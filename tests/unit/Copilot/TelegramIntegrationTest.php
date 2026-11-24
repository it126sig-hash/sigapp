<?php

use App\Controllers\Api\Copilot\ChatController;
use App\Controllers\Api\Copilot\TelegramLinkController;
use App\Controllers\Api\Public\CopilotTelegramController;
use App\Repositories\AuditTrail\HistoryRepository;
use App\Repositories\Copilot\MessageRepository;
use App\Repositories\Copilot\SettingRepository;
use App\Repositories\Copilot\TelegramLinkRepository;
use App\Repositories\Proyek\ProyekRepository;
use App\Services\AuditTrail\HistoryService;
use App\Services\Copilot\CopilotAuthorizer;
use App\Services\Copilot\CopilotOrchestrator;
use App\Services\Copilot\LlmProviderFactory;
use App\Services\Copilot\LlmProviderInterface;
use App\Services\Copilot\LlmResponse;
use App\Services\Copilot\PiiGuard;
use App\Services\Copilot\TelegramService;
use App\Services\Copilot\ToolRegistry;
use App\Services\Copilot\Tools\BookingPerBulanTool;
use App\Services\Copilot\Tools\JatuhTempoCicilanTool;
use App\Services\Settings\MenuAccessService;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\Test\CIUnitTestCase;
use Config\App;
use Config\Copilot as CopilotConfig;
use Config\Database;

final class TelegramIntegrationTest extends CIUnitTestCase
{
    public function testPairingFlowLinkExpiredAndUnlink(): void
    {
        $db = $this->connectLiveDefaultDb();
        $userId1 = 999811;
        $userId2 = 999812;
        $telegramUserId = 881000111;
        $chatId = 771000111;

        try {
            $this->cleanupTestArtifacts($db, [$userId1, $userId2]);

            $linkRepo = new TelegramLinkRepository($db);
            $messageRepo = new MessageRepository($db);
            $config = new CopilotConfig();

            $menuAccess = $this->createMock(MenuAccessService::class);
            $menuAccess->method('canAccessUrl')->willReturn(true);
            $menuAccess->method('getUserGroupIds')->willReturn([1]);

            $proyekRepo = $this->createMock(ProyekRepository::class);
            $proyekRepo->method('getAccessibleForUser')->willReturn([]);

            $authorizer = new CopilotAuthorizer($menuAccess, $proyekRepo, $config);
            $toolRegistry = new ToolRegistry($authorizer, new PiiGuard(), [
                new JatuhTempoCicilanTool($authorizer, null, new PiiGuard(), $config),
                new BookingPerBulanTool($authorizer, null, new PiiGuard()),
            ]);

            $orchestrator = $this->buildOrchestrator($db, $authorizer, $toolRegistry, $messageRepo, $config);

            $sentTelegramCalls = [];
            $transport = $this->makeFakeTransport($sentTelegramCalls);

            $telegramService = new TelegramService(
                linkRepository: $linkRepo,
                messageRepository: $messageRepo,
                orchestrator: $orchestrator,
                authorizer: $authorizer,
                toolRegistry: $toolRegistry,
                config: $config,
                httpTransport: $transport,
                botToken: 'test-bot-token',
                webhookSecret: 'secret-123',
                hashSecret: 'unit-test-hmac-secret'
            );

            // 1. Create pairing code via TelegramLinkController::create()
            $linkCtrl = $this->makeLinkController($telegramService, $userId1, 'POST');
            $createRes = $linkCtrl->create();
            $this->assertSame(201, $createRes->getStatusCode());
            $createBody = json_decode((string) $createRes->getBody(), true);
            $this->assertTrue($createBody['success']);
            $this->assertMatchesRegularExpression('/^\d{6}$/', $createBody['data']['code']);
            $this->assertSame(10, $createBody['data']['expires_in_minutes']);

            $code = $createBody['data']['code'];

            // Meta reflects pending_expires_at while not yet linked
            $chatCtrl = $this->makeChatController($orchestrator, $userId1, 'GET');
            $metaBefore = json_decode((string) $chatCtrl->meta()->getBody(), true);
            $this->assertFalse($metaBefore['data']['telegram']['linked']);
            $this->assertNotNull($metaBefore['data']['telegram']['pending_expires_at']);

            // 2. /link <KODE> succeeds
            $linkResult = $telegramService->handleWebhookUpdate([
                'update_id' => 1001,
                'message'   => [
                    'message_id' => 1,
                    'from'       => ['id' => $telegramUserId, 'username' => 'tester_sigapp'],
                    'chat'       => ['id' => $chatId, 'type' => 'private'],
                    'text'       => '/link@SigappBot ' . $code,
                ],
            ]);

            $this->assertSame('linked', $linkResult['status']);
            $this->assertSame($userId1, $linkResult['user_id']);
            $this->assertNotEmpty($sentTelegramCalls);
            $lastSent = $sentTelegramCalls[count($sentTelegramCalls) - 1];
            $this->assertSame($chatId, $lastSent['body']['chat_id']);
            $this->assertStringContainsString('berhasil ditautkan', $lastSent['body']['text']);

            $metaAfter = json_decode((string) $chatCtrl->meta()->getBody(), true);
            $this->assertTrue($metaAfter['data']['telegram']['linked']);
            $this->assertSame('tester_sigapp', $metaAfter['data']['telegram']['telegram_username']);
            $this->assertNull($metaAfter['data']['telegram']['pending_expires_at']);

            // 3. Re-binding same telegram_user_id to userId2 succeeds without unique constraint error
            $code2 = $telegramService->generateLinkCode($userId2)['code'];
            $rebindResult = $telegramService->handleWebhookUpdate([
                'update_id' => 1002,
                'message'   => [
                    'message_id' => 2,
                    'from'       => ['id' => $telegramUserId, 'username' => 'tester_sigapp_2'],
                    'chat'       => ['id' => $chatId, 'type' => 'private'],
                    'text'       => '/link ' . $code2,
                ],
            ]);
            $this->assertSame('linked', $rebindResult['status']);
            $this->assertSame($userId2, $rebindResult['user_id']);
            $this->assertNull($linkRepo->findByUserId($userId1)['telegram_user_id']);

            // 4. Expired pairing code is rejected
            $expiredCode = '654321';
            $linkRepo->savePairingCode(
                $userId1,
                $telegramService->hashCode($expiredCode),
                date('Y-m-d H:i:s', time() - 120)
            );
            $expiredResult = $telegramService->handleWebhookUpdate([
                'update_id' => 1003,
                'message'   => [
                    'message_id' => 3,
                    'from'       => ['id' => $telegramUserId + 1, 'username' => 'other_user'],
                    'chat'       => ['id' => $chatId + 1, 'type' => 'private'],
                    'text'       => '/link ' . $expiredCode,
                ],
            ]);
            $this->assertSame('link_invalid', $expiredResult['status']);
            $this->assertStringContainsString('kedaluwarsa', $sentTelegramCalls[count($sentTelegramCalls) - 1]['body']['text']);

            // 5. /unlink command unbinds userId2
            $unlinkCmdResult = $telegramService->handleWebhookUpdate([
                'update_id' => 1004,
                'message'   => [
                    'message_id' => 4,
                    'from'       => ['id' => $telegramUserId, 'username' => 'tester_sigapp_2'],
                    'chat'       => ['id' => $chatId, 'type' => 'private'],
                    'text'       => '/unlink',
                ],
            ]);
            $this->assertSame('unlinked_command', $unlinkCmdResult['status']);
            $this->assertNull($linkRepo->findVerifiedByTelegramUserId($telegramUserId));

            // 6. DELETE /api/copilot/telegram/link returns 200 Full JSON
            $deleteCtrl = $this->makeLinkController($telegramService, $userId1, 'DELETE');
            $delRes = $deleteCtrl->delete();
            $this->assertSame(200, $delRes->getStatusCode());
            $delBody = json_decode((string) $delRes->getBody(), true);
            $this->assertTrue($delBody['success']);
            $this->assertFalse($delBody['data']['linked']);
        } finally {
            $this->cleanupTestArtifacts($db, [$userId1, $userId2]);
            $db->close();
        }
    }

    public function testWebhookSecurityGroupGuardUnlinkedDedupAndChatReply(): void
    {
        $db = $this->connectLiveDefaultDb();
        $userId = 999813;
        $telegramUserId = 881000222;
        $chatId = 771000222;

        try {
            $this->cleanupTestArtifacts($db, [$userId]);

            $linkRepo = new TelegramLinkRepository($db);
            $messageRepo = new MessageRepository($db);
            $config = new CopilotConfig();

            $menuAccess = $this->createMock(MenuAccessService::class);
            $menuAccess->method('canAccessUrl')->willReturn(true);
            $menuAccess->method('getUserGroupIds')->willReturn([4]);

            $proyekRepo = $this->createMock(ProyekRepository::class);
            $proyekRepo->method('getAccessibleForUser')->willReturn([]);

            $authorizer = new CopilotAuthorizer($menuAccess, $proyekRepo, $config);
            $toolRegistry = new ToolRegistry($authorizer, new PiiGuard(), []);

            $llmCallCount = 0;
            $longReply = str_repeat("Ringkasan proyek SIGAPP baris data.\n", 140); // > 4000 chars
            $fakeProvider = new class($llmCallCount, $longReply) implements LlmProviderInterface {
                private int $count;

                public function __construct(int &$countRef, private readonly string $replyText)
                {
                    $this->count = &$countRef;
                }

                public function chat(array $messages, array $tools = [], array $options = []): LlmResponse
                {
                    $this->count++;

                    return new LlmResponse(text: $this->replyText);
                }

                public function getProviderName(): string
                {
                    return 'gemini';
                }

                public function getModelName(): string
                {
                    return 'gemini-flash-latest';
                }
            };

            $orchestrator = $this->buildOrchestrator($db, $authorizer, $toolRegistry, $messageRepo, $config, $fakeProvider);

            $sentTelegramCalls = [];
            $transport = $this->makeFakeTransport($sentTelegramCalls);

            // 1. Empty webhook secret on server -> 503 JSON
            $unconfiguredService = new TelegramService(
                linkRepository: $linkRepo,
                messageRepository: $messageRepo,
                orchestrator: $orchestrator,
                authorizer: $authorizer,
                toolRegistry: $toolRegistry,
                config: $config,
                httpTransport: $transport,
                botToken: 'test-bot-token',
                webhookSecret: '',
                hashSecret: 'unit-test-hmac-secret'
            );
            $ctrl503 = $this->makeWebhookController($unconfiguredService, ['X-Telegram-Bot-Api-Secret-Token' => 'any'], ['update_id' => 1]);
            $res503 = $ctrl503->webhook();
            $this->assertSame(503, $res503->getStatusCode());
            $this->assertFalse(json_decode((string) $res503->getBody(), true)['success']);

            // Configure valid secret
            $telegramService = new TelegramService(
                linkRepository: $linkRepo,
                messageRepository: $messageRepo,
                orchestrator: $orchestrator,
                authorizer: $authorizer,
                toolRegistry: $toolRegistry,
                config: $config,
                httpTransport: $transport,
                botToken: 'test-bot-token',
                webhookSecret: 'super-secret-token',
                hashSecret: 'unit-test-hmac-secret'
            );

            // 2. Wrong / missing header -> 403 JSON
            $ctrlMissing = $this->makeWebhookController($telegramService, [], ['update_id' => 2]);
            $res403Missing = $ctrlMissing->webhook();
            $this->assertSame(403, $res403Missing->getStatusCode());
            $this->assertFalse(json_decode((string) $res403Missing->getBody(), true)['success']);

            $ctrlWrong = $this->makeWebhookController($telegramService, ['X-Telegram-Bot-Api-Secret-Token' => 'wrong'], ['update_id' => 2]);
            $res403Wrong = $ctrlWrong->webhook();
            $this->assertSame(403, $res403Wrong->getStatusCode());

            // 3. Group update ignored & orchestrator not called
            $ctrlGroup = $this->makeWebhookController(
                $telegramService,
                ['X-Telegram-Bot-Api-Secret-Token' => 'super-secret-token'],
                [
                    'update_id' => 2001,
                    'message'   => [
                        'message_id' => 10,
                        'from'       => ['id' => $telegramUserId],
                        'chat'       => ['id' => -999111, 'type' => 'group'],
                        'text'       => 'Halo bot di grup',
                    ],
                ]
            );
            $resGroup = $ctrlGroup->webhook();
            $this->assertSame(200, $resGroup->getStatusCode());
            $groupBody = json_decode((string) $resGroup->getBody(), true);
            $this->assertTrue($groupBody['success']);
            $this->assertSame('ignored_non_private', $groupBody['data']['status']);
            $this->assertSame(0, $llmCallCount);
            $this->assertCount(0, $sentTelegramCalls);

            // 4. Unlinked user does NOT call CopilotOrchestrator
            $ctrlUnlinked = $this->makeWebhookController(
                $telegramService,
                ['X-Telegram-Bot-Api-Secret-Token' => 'super-secret-token'],
                [
                    'update_id' => 2002,
                    'message'   => [
                        'message_id' => 11,
                        'from'       => ['id' => $telegramUserId, 'username' => 'unlinked_user'],
                        'chat'       => ['id' => $chatId, 'type' => 'private'],
                        'text'       => 'Berapa booking bulan ini?',
                    ],
                ]
            );
            $resUnlinked = $ctrlUnlinked->webhook();
            $this->assertSame(200, $resUnlinked->getStatusCode());
            $unlinkedBody = json_decode((string) $resUnlinked->getBody(), true);
            $this->assertSame('unlinked', $unlinkedBody['data']['status']);
            $this->assertSame(0, $llmCallCount);
            $this->assertCount(1, $sentTelegramCalls);
            $this->assertStringContainsString('/link KODE', $sentTelegramCalls[0]['body']['text']);

            // Pair user now
            $code = $telegramService->generateLinkCode($userId)['code'];
            $linkRepo->verifyAndBindByCodeHash(
                $telegramService->hashCode($code),
                $telegramUserId,
                $chatId,
                'linked_user',
                date('Y-m-d H:i:s')
            );

            // 5. Linked user message invokes orchestrator & chunks >4000 char reply
            $sentTelegramCalls = [];
            $ctrlLinked = $this->makeWebhookController(
                $telegramService,
                ['X-Telegram-Bot-Api-Secret-Token' => 'super-secret-token'],
                [
                    'update_id' => 2003,
                    'message'   => [
                        'message_id' => 12,
                        'from'       => ['id' => $telegramUserId, 'username' => 'linked_user'],
                        'chat'       => ['id' => $chatId, 'type' => 'private'],
                        'text'       => 'Berapa booking tahun 2026?',
                    ],
                ]
            );
            $resLinked = $ctrlLinked->webhook();
            $this->assertSame(200, $resLinked->getStatusCode());
            $linkedBody = json_decode((string) $resLinked->getBody(), true);
            $this->assertTrue($linkedBody['success']);
            $this->assertSame('replied', $linkedBody['data']['status']);
            $this->assertSame(1, $llmCallCount);

            $sendMessageCalls = array_values(array_filter(
                $sentTelegramCalls,
                static fn (array $c): bool => str_ends_with($c['url'], '/sendMessage')
            ));
            $this->assertGreaterThanOrEqual(2, count($sendMessageCalls));
            foreach ($sendMessageCalls as $msgCall) {
                $this->assertSame($chatId, $msgCall['body']['chat_id']);
                $this->assertLessThanOrEqual(TelegramService::TELEGRAM_MAX_MESSAGE_LENGTH, mb_strlen($msgCall['body']['text'], 'UTF-8'));
            }

            // 6. Duplicate telegram_update_id is ignored without calling orchestrator again
            $sentTelegramCalls = [];
            $ctrlDup = $this->makeWebhookController(
                $telegramService,
                ['X-Telegram-Bot-Api-Secret-Token' => 'super-secret-token'],
                [
                    'update_id' => 2003,
                    'message'   => [
                        'message_id' => 12,
                        'from'       => ['id' => $telegramUserId, 'username' => 'linked_user'],
                        'chat'       => ['id' => $chatId, 'type' => 'private'],
                        'text'       => 'Berapa booking tahun 2026?',
                    ],
                ]
            );
            $resDup = $ctrlDup->webhook();
            $this->assertSame(200, $resDup->getStatusCode());
            $dupBody = json_decode((string) $resDup->getBody(), true);
            $this->assertSame('duplicate_ignored', $dupBody['data']['status']);
            $this->assertSame(1, $llmCallCount);
            $this->assertCount(0, $sentTelegramCalls);
        } finally {
            $this->cleanupTestArtifacts($db, [$userId]);
            $db->close();
        }
    }

    public function testPruneOlderThanDaysRemovesOnlyExpiredMessages(): void
    {
        $db = $this->connectLiveDefaultDb();
        $userId = 999814;

        try {
            $this->cleanupTestArtifacts($db, [$userId]);
            $messageRepo = new MessageRepository($db);

            // Insert 1 message older than 90 days and 1 recent message
            $db->table('copilot_messages')->insert([
                'user_id'    => $userId,
                'channel'    => 'telegram',
                'role'       => 'user',
                'content'    => 'Pesan lama 120 hari',
                'created_at' => date('Y-m-d H:i:s', time() - (120 * 86400)),
            ]);
            $db->table('copilot_messages')->insert([
                'user_id'    => $userId,
                'channel'    => 'telegram',
                'role'       => 'user',
                'content'    => 'Pesan baru 5 hari',
                'created_at' => date('Y-m-d H:i:s', time() - (5 * 86400)),
            ]);

            $deleted = $messageRepo->pruneOlderThanDays(90);
            $this->assertGreaterThanOrEqual(1, $deleted);

            $remaining = $db->table('copilot_messages')
                ->where('user_id', $userId)
                ->get()
                ->getResultArray();

            $this->assertCount(1, $remaining);
            $this->assertSame('Pesan baru 5 hari', $remaining[0]['content']);
        } finally {
            $this->cleanupTestArtifacts($db, [$userId]);
            $db->close();
        }
    }

    /**
     * @param array<int, array<string, mixed>> $recordedCalls
     */
    private function makeFakeTransport(array &$recordedCalls): callable
    {
        return static function (string $method, string $url, array $headers, array $body, int $timeout) use (&$recordedCalls): array {
            $recordedCalls[] = compact('method', 'url', 'headers', 'body', 'timeout');

            return [
                'status' => 200,
                'body'   => json_encode(['ok' => true, 'result' => true]),
            ];
        };
    }

    private function buildOrchestrator(
        BaseConnection $db,
        CopilotAuthorizer $authorizer,
        ToolRegistry $toolRegistry,
        MessageRepository $messageRepo,
        CopilotConfig $config,
        ?LlmProviderInterface $provider = null
    ): CopilotOrchestrator {
        $settingRepo = new SettingRepository($db);
        $piiGuard = new PiiGuard();
        $historyService = new HistoryService(new HistoryRepository($db));

        $defaultProvider = $provider ?? new class implements LlmProviderInterface {
            public function chat(array $messages, array $tools = [], array $options = []): LlmResponse
            {
                return new LlmResponse(text: 'OK');
            }

            public function getProviderName(): string
            {
                return 'gemini';
            }

            public function getModelName(): string
            {
                return 'gemini-flash-latest';
            }
        };

        return new CopilotOrchestrator(
            providerFactory: new LlmProviderFactory($config, $settingRepo),
            toolRegistry: $toolRegistry,
            authorizer: $authorizer,
            messageRepository: $messageRepo,
            piiGuard: $piiGuard,
            historyService: $historyService,
            config: $config,
            provider: $defaultProvider,
            settingRepository: $settingRepo,
            db: $db
        );
    }

    private function makeLinkController(TelegramService $telegramService, int $userId, string $method): TelegramLinkController
    {
        $controller = new class($telegramService, $userId) extends TelegramLinkController {
            public function __construct(TelegramService $telegramService, private readonly int $uid)
            {
                parent::__construct($telegramService);
            }

            protected function resolveUserId(): int
            {
                return $this->uid;
            }
        };

        $request = new IncomingRequest(new App(), new URI('http://localhost/api/copilot/telegram/link'), null, new UserAgent());
        $request->setMethod($method);
        $controller->initController($request, new Response(new App()), service('logger'));

        return $controller;
    }

    private function makeChatController(CopilotOrchestrator $orchestrator, int $userId, string $method): ChatController
    {
        $controller = new class($orchestrator, $userId) extends ChatController {
            public function __construct(CopilotOrchestrator $orchestrator, private readonly int $uid)
            {
                parent::__construct($orchestrator);
            }

            protected function resolveUserId(): int
            {
                return $this->uid;
            }
        };

        $request = new IncomingRequest(new App(), new URI('http://localhost/api/copilot/meta'), null, new UserAgent());
        $request->setMethod($method);
        $controller->initController($request, new Response(new App()), service('logger'));

        return $controller;
    }

    /**
     * @param array<string, string> $headers
     * @param array<string, mixed> $jsonBody
     */
    private function makeWebhookController(TelegramService $telegramService, array $headers, array $jsonBody): CopilotTelegramController
    {
        $controller = new CopilotTelegramController($telegramService);
        $request = new IncomingRequest(new App(), new URI('http://localhost/api/public/copilot/telegram/webhook'), null, new UserAgent());
        $request->setMethod('POST');
        $request->setHeader('Content-Type', 'application/json');
        foreach ($headers as $name => $val) {
            $request->setHeader($name, $val);
        }
        $request->setBody((string) json_encode($jsonBody));
        $controller->initController($request, new Response(new App()), service('logger'));

        return $controller;
    }

    private function connectLiveDefaultDb(): BaseConnection
    {
        try {
            $db = Database::connect('default', false);
            $db->connect();

            return $db;
        } catch (Throwable $e) {
            $this->markTestSkipped('Local MySQL database is not accessible: ' . $e->getMessage());
        }
    }

    /**
     * @param int[] $userIds
     */
    private function cleanupTestArtifacts(BaseConnection $db, array $userIds): void
    {
        if ($userIds === []) {
            return;
        }

        if ($db->tableExists('copilot_telegram_links')) {
            $db->table('copilot_telegram_links')->whereIn('user_id', $userIds)->delete();
        }
        if ($db->tableExists('copilot_messages')) {
            $db->table('copilot_messages')->whereIn('user_id', $userIds)->delete();
        }
        if ($db->tableExists('history_log')) {
            $db->table('history_log')->where('module', 'copilot')->whereIn('add_by', $userIds)->delete();
        }
    }
}

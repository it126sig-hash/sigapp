<?php

use App\Controllers\Api\Copilot\ChatController;
use App\Controllers\Api\Copilot\SettingController;
use App\Exceptions\ServiceException;
use App\Repositories\AuditTrail\HistoryRepository;
use App\Repositories\Copilot\MessageRepository;
use App\Repositories\Copilot\SettingRepository;
use App\Repositories\Mkdt\BookingAkadReportRepository;
use App\Repositories\Proyek\ProyekRepository;
use App\Services\AuditTrail\HistoryService;
use App\Services\Copilot\CopilotAuthorizer;
use App\Services\Copilot\CopilotOrchestrator;
use App\Services\Copilot\LlmProviderException;
use App\Services\Copilot\LlmProviderFactory;
use App\Services\Copilot\LlmProviderInterface;
use App\Services\Copilot\LlmResponse;
use App\Services\Copilot\PiiGuard;
use App\Services\Copilot\ToolRegistry;
use App\Services\Copilot\Tools\BookingPerBulanTool;
use App\Services\Copilot\Tools\JatuhTempoCicilanTool;
use App\Services\Copilot\Tools\KonversiBookingAkadTool;
use App\Services\Mkdt\BookingAkadReportService;
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

final class OrchestratorAndApiTest extends CIUnitTestCase
{
    public function testOrchestratorMultiTurnToolCallSavesMessagesAndAuditLog(): void
    {
        $db = $this->connectLiveDefaultDb();
        $testUserId = 999801;

        try {
            $this->cleanupTestArtifacts($db, $testUserId);

            $projectRow = $db->table('proyek')->select('id_proyek, nama_proyek')->get()->getRow();
            $this->assertNotNull($projectRow, 'Database must have at least one project.');
            $idProyek = (int) $projectRow->id_proyek;
            $namaProyek = (string) $projectRow->nama_proyek;

            $menuAccess = $this->createMock(MenuAccessService::class);
            $menuAccess->method('canAccessUrl')->willReturn(true);
            $menuAccess->method('getUserGroupIds')->willReturn([4]); // MKDT

            $proyekRepo = $this->createMock(ProyekRepository::class);
            $proyekRepo->method('getAccessibleForUser')->willReturn([
                (object) ['id_proyek' => $idProyek, 'nama_proyek' => $namaProyek],
            ]);

            $config = new CopilotConfig();
            $authorizer = new CopilotAuthorizer($menuAccess, $proyekRepo, $config);
            $piiGuard = new PiiGuard();
            $reportService = new BookingAkadReportService(new BookingAkadReportRepository($db));
            $toolRegistry = new ToolRegistry($authorizer, $piiGuard, [
                new BookingPerBulanTool($authorizer, $reportService, $piiGuard),
                new KonversiBookingAkadTool($authorizer, $reportService, $piiGuard),
            ]);

            $messageRepo = new MessageRepository($db);
            $settingRepo = new SettingRepository($db);
            $historyService = new HistoryService(new HistoryRepository($db));

            $recordedCalls = [];
            $fakeProvider = new class($recordedCalls, $namaProyek) implements LlmProviderInterface {
                /** @var array<int, array<string, mixed>> */
                private array $calls;
                private int $step = 0;

                public function __construct(array &$callsRef, private readonly string $proyekName)
                {
                    $this->calls = &$callsRef;
                }

                public function chat(array $messages, array $tools = [], array $options = []): LlmResponse
                {
                    $this->calls[] = compact('messages', 'tools', 'options');
                    $this->step++;

                    if ($this->step === 1) {
                        return new LlmResponse(
                            text: '',
                            toolCalls: [[
                                'id'        => 'call_booking_1',
                                'name'      => 'get_booking_per_bulan',
                                'arguments' => ['proyek' => $this->proyekName, 'tahun' => 2026],
                            ]],
                            rawMessage: [
                                'role'  => 'model',
                                'parts' => [[
                                    'functionCall' => [
                                        'name' => 'get_booking_per_bulan',
                                        'args' => ['proyek' => $this->proyekName, 'tahun' => 2026],
                                    ],
                                ]],
                            ]
                        );
                    }

                    return new LlmResponse(
                        text: "Total booking tahun 2026 pada proyek {$this->proyekName} berhasil diambil dari data resmi."
                    );
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

            $orchestrator = new CopilotOrchestrator(
                providerFactory: new LlmProviderFactory($config, $settingRepo),
                toolRegistry: $toolRegistry,
                authorizer: $authorizer,
                messageRepository: $messageRepo,
                piiGuard: $piiGuard,
                historyService: $historyService,
                config: $config,
                provider: $fakeProvider,
                settingRepository: $settingRepo,
                db: $db
            );

            $result = $orchestrator->handleUserMessage($testUserId, 'Berapa booking tahun 2026?', 'web');

            $this->assertSame('gemini', $result['provider']);
            $this->assertSame('gemini-flash-latest', $result['model']);
            $this->assertSame(['get_booking_per_bulan'], $result['tools_used']);
            $this->assertStringContainsString($namaProyek, $result['reply']);

            // Verify 2 LLM turns happened and system prompt + tool response were passed cleanly
            $this->assertCount(2, $recordedCalls);
            $this->assertSame('system', $recordedCalls[0]['messages'][0]['role']);
            $this->assertArrayHasKey('systemInstruction', $recordedCalls[0]['options']);
            $this->assertStringContainsString('TIDAK TERSEDIA', $recordedCalls[0]['options']['systemInstruction']);
            $this->assertSame('tool', $recordedCalls[1]['messages'][count($recordedCalls[1]['messages']) - 1]['role']);
            $this->assertSame('get_booking_per_bulan', $recordedCalls[1]['messages'][count($recordedCalls[1]['messages']) - 1]['name']);

            // Verify copilot_messages persisted user + assistant turns in chronological order
            $history = $messageRepo->getRecentHistory($testUserId, 'web', 10);
            $this->assertCount(2, $history);
            $this->assertSame('user', $history[0]['role']);
            $this->assertSame('Berapa booking tahun 2026?', $history[0]['content']);
            $this->assertSame('assistant', $history[1]['role']);
            $this->assertSame($result['reply'], $history[1]['content']);

            // Verify history_log audit entry recorded without reply content
            $auditRows = $db->table('history_log')
                ->where('module', 'copilot')
                ->where('add_by', $testUserId)
                ->get()
                ->getResultArray();

            $this->assertCount(1, $auditRows);
            $this->assertSame('tool_call', $auditRows[0]['action']);
            $meta = json_decode((string) $auditRows[0]['metadata'], true);
            $this->assertSame('web', $meta['channel']);
            $this->assertSame('get_booking_per_bulan', $meta['tool']);
            $this->assertTrue($meta['ok']);
            $this->assertStringNotContainsString($result['reply'], (string) $auditRows[0]['summary']);

            // Verify HistoryService module label includes copilot
            $labels = $historyService->moduleLabels();
            $this->assertSame('AI Copilot', $labels['copilot'] ?? null);
        } finally {
            $this->cleanupTestArtifacts($db, $testUserId);
            $db->close();
        }
    }

    public function testOrchestratorRedactsPiiAndEnforcesAccessValidationAndRateLimit(): void
    {
        $db = $this->connectLiveDefaultDb();
        $testUserId = 999802;
        $deniedUserId = 999803;

        try {
            $this->cleanupTestArtifacts($db, $testUserId);

            $menuAccess = $this->createMock(MenuAccessService::class);
            $menuAccess->method('canAccessUrl')->willReturnCallback(
                static fn (int $uid, string $url): bool => $uid === $testUserId && $url === 'copilot'
            );
            $menuAccess->method('getUserGroupIds')->willReturn([1]);

            $proyekRepo = $this->createMock(ProyekRepository::class);
            $proyekRepo->method('getAccessibleForUser')->willReturn([]);

            $config = new CopilotConfig();
            $config->rateLimitMaxMessages = 2;
            $config->rateLimitWindowMinutes = 10;

            $authorizer = new CopilotAuthorizer($menuAccess, $proyekRepo, $config);
            $piiGuard = new PiiGuard();
            $messageRepo = new MessageRepository($db);
            $settingRepo = new SettingRepository($db);
            $historyService = new HistoryService(new HistoryRepository($db));

            $fakeProvider = new class implements LlmProviderInterface {
                public function chat(array $messages, array $tools = [], array $options = []): LlmResponse
                {
                    return new LlmResponse(
                        text: 'Data Anda diterima (ref 3201012345678902 dan NPWP 12.345.678.9-012.345).'
                    );
                }

                public function getProviderName(): string
                {
                    return 'openai';
                }

                public function getModelName(): string
                {
                    return 'gpt-4o-mini';
                }
            };

            $orchestrator = new CopilotOrchestrator(
                providerFactory: new LlmProviderFactory($config, $settingRepo),
                toolRegistry: new ToolRegistry($authorizer, $piiGuard, []),
                authorizer: $authorizer,
                messageRepository: $messageRepo,
                piiGuard: $piiGuard,
                historyService: $historyService,
                config: $config,
                provider: $fakeProvider,
                settingRepository: $settingRepo,
                db: $db
            );

            // 1. Unauthorized user rejected with 403
            try {
                $orchestrator->handleUserMessage($deniedUserId, 'Halo');
                $this->fail('Expected 403 ServiceException for unauthorized user.');
            } catch (ServiceException $e) {
                $this->assertSame(403, $e->getStatusCode());
            }

            // 2. Empty message rejected with 422
            try {
                $orchestrator->handleUserMessage($testUserId, '   ');
                $this->fail('Expected 422 ServiceException for empty message.');
            } catch (ServiceException $e) {
                $this->assertSame(422, $e->getStatusCode());
            }

            // 3. Too long message (> 2000 chars) rejected with 422
            try {
                $orchestrator->handleUserMessage($testUserId, str_repeat('a', 2001));
                $this->fail('Expected 422 ServiceException for message > 2000 chars.');
            } catch (ServiceException $e) {
                $this->assertSame(422, $e->getStatusCode());
            }

            // 4. PII redaction on prompt and final reply
            $res1 = $orchestrator->handleUserMessage(
                $testUserId,
                'Cek NIK 3201012345678901 dan NPWP 98.765.432.1-098.765 saya',
                'web',
                555001
            );

            $this->assertStringNotContainsString('3201012345678902', $res1['reply']);
            $this->assertStringNotContainsString('12.345.678.9-012.345', $res1['reply']);
            $this->assertStringContainsString('[REDACTED_ID]', $res1['reply']);
            $this->assertStringContainsString('[REDACTED_NPWP]', $res1['reply']);

            $saved = $messageRepo->getRecentHistory($testUserId, 'web', 5);
            $this->assertStringNotContainsString('3201012345678901', $saved[0]['content']);
            $this->assertStringNotContainsString('98.765.432.1-098.765', $saved[0]['content']);
            $this->assertTrue($messageRepo->hasTelegramUpdateId(555001));

            // 5. Second message allowed, third hits rate limit (max 2)
            $orchestrator->handleUserMessage($testUserId, 'Pesan kedua');

            try {
                $orchestrator->handleUserMessage($testUserId, 'Pesan ketiga kena rate limit');
                $this->fail('Expected 429 ServiceException when rate limit exceeded.');
            } catch (ServiceException $e) {
                $this->assertSame(429, $e->getStatusCode());
            }
        } finally {
            $this->cleanupTestArtifacts($db, $testUserId);
            $db->close();
        }
    }

    public function testApiControllersReturnFullJsonAndEnforceAdminSettings(): void
    {
        $db = $this->connectLiveDefaultDb();
        $adminUserId = 999804;
        $nonAdminUserId = 999805;

        $settingRepo = new SettingRepository($db);
        $originalSettings = $settingRepo->getAll();

        try {
            $this->cleanupTestArtifacts($db, $adminUserId);
            $this->cleanupTestArtifacts($db, $nonAdminUserId);

            $menuAccess = $this->createMock(MenuAccessService::class);
            $menuAccess->method('canAccessUrl')->willReturn(true);
            $menuAccess->method('getUserGroupIds')->willReturnCallback(
                static fn (int $uid): array => $uid === $adminUserId ? [1] : [3]
            );

            $proyekRepo = $this->createMock(ProyekRepository::class);
            $proyekRepo->method('getAccessibleForUser')->willReturn([
                (object) ['id_proyek' => 13, 'nama_proyek' => 'SANGGAR INDAH PALASTRI'],
            ]);

            $config = new CopilotConfig();
            $authorizer = new CopilotAuthorizer($menuAccess, $proyekRepo, $config);
            $piiGuard = new PiiGuard();
            $toolRegistry = new ToolRegistry($authorizer, $piiGuard, [
                new JatuhTempoCicilanTool($authorizer, null, $piiGuard, $config),
                new BookingPerBulanTool($authorizer, null, $piiGuard),
            ]);
            $messageRepo = new MessageRepository($db);
            $historyService = new HistoryService(new HistoryRepository($db));

            $failingProvider = new class implements LlmProviderInterface {
                public function chat(array $messages, array $tools = [], array $options = []): LlmResponse
                {
                    throw new LlmProviderException('Upstream timeout', 502);
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

            $orchestrator = new CopilotOrchestrator(
                providerFactory: new LlmProviderFactory($config, $settingRepo),
                toolRegistry: $toolRegistry,
                authorizer: $authorizer,
                messageRepository: $messageRepo,
                piiGuard: $piiGuard,
                historyService: $historyService,
                config: $config,
                provider: $failingProvider,
                settingRepository: $settingRepo,
                db: $db
            );

            // 1. ChatController::meta() never leaks API keys
            $chatCtrl = $this->makeChatController($orchestrator, $adminUserId, 'GET');
            $metaRes = $chatCtrl->meta();
            $this->assertSame(200, $metaRes->getStatusCode());
            $metaBody = json_decode((string) $metaRes->getBody(), true);
            $this->assertTrue($metaBody['success']);
            $this->assertTrue($metaBody['data']['can_access']);
            $this->assertTrue($metaBody['data']['is_admin']);
            $this->assertArrayHasKey('provider_config', $metaBody['data']);
            $this->assertArrayHasKey('telegram', $metaBody['data']);
            $this->assertArrayHasKey('policy', $metaBody['data']);
            foreach ($metaBody['data']['provider_config']['available_providers'] as $item) {
                $this->assertArrayHasKey('has_api_key', $item);
                $this->assertIsBool($item['has_api_key']);
                $this->assertArrayNotHasKey('api_key', $item);
                $this->assertArrayNotHasKey('apiKey', $item);
            }

            // 2. ChatController::chat() maps LlmProviderException -> 502 Full JSON
            $chatPostCtrl = $this->makeChatController($orchestrator, $adminUserId, 'POST', ['message' => 'Halo']);
            $chatErrRes = $chatPostCtrl->chat();
            $this->assertSame(502, $chatErrRes->getStatusCode());
            $chatErrBody = json_decode((string) $chatErrRes->getBody(), true);
            $this->assertFalse($chatErrBody['success']);
            $this->assertSame('Upstream timeout', $chatErrBody['messages']);

            // 3. SettingController denies non-Admin with 403 JSON
            $nonAdminSettingCtrl = $this->makeSettingController($orchestrator, $nonAdminUserId, 'GET');
            $forbiddenRes = $nonAdminSettingCtrl->index();
            $this->assertSame(403, $forbiddenRes->getStatusCode());
            $forbiddenBody = json_decode((string) $forbiddenRes->getBody(), true);
            $this->assertFalse($forbiddenBody['success']);

            // 4. SettingController validates invalid provider & base_url (422 JSON)
            $invalidCtrl = $this->makeSettingController($orchestrator, $adminUserId, 'POST', [
                'provider' => 'unknown_llm',
                'model'    => '',
                'base_url' => 'ftp://invalid-scheme.local',
            ]);
            $invalidRes = $invalidCtrl->update();
            $this->assertSame(422, $invalidRes->getStatusCode());
            $invalidBody = json_decode((string) $invalidRes->getBody(), true);
            $this->assertFalse($invalidBody['success']);
            $this->assertArrayHasKey('provider', $invalidBody['errors']);
            $this->assertArrayHasKey('model', $invalidBody['errors']);
            $this->assertArrayHasKey('base_url', $invalidBody['errors']);

            // 5. SettingController persists valid Admin update to SettingRepository
            $validCtrl = $this->makeSettingController($orchestrator, $adminUserId, 'POST', [
                'provider' => 'openai',
                'model'    => 'gpt-4o-mini',
                'base_url' => 'https://api.openai.com/v1/',
            ]);
            $okRes = $validCtrl->update();
            $this->assertSame(200, $okRes->getStatusCode());
            $okBody = json_decode((string) $okRes->getBody(), true);
            $this->assertTrue($okBody['success']);
            $this->assertSame('openai', $okBody['data']['provider']);
            $this->assertSame('gpt-4o-mini', $okBody['data']['model']);
            $this->assertSame('https://api.openai.com/v1', $okBody['data']['base_url']);

            $persisted = $settingRepo->getAll();
            $this->assertSame('openai', $persisted['provider']);
            $this->assertSame('gpt-4o-mini', $persisted['model']);
            $this->assertSame('https://api.openai.com/v1', $persisted['base_url']);
        } finally {
            $this->restoreSettings($db, $originalSettings);
            $this->cleanupTestArtifacts($db, $adminUserId);
            $this->cleanupTestArtifacts($db, $nonAdminUserId);
            $db->close();
        }
    }

    private function makeChatController(
        CopilotOrchestrator $orchestrator,
        int $userId,
        string $method,
        array $jsonBody = []
    ): ChatController {
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

        $request = $this->buildJsonRequest($method, 'api/copilot/test', $jsonBody);
        $controller->initController($request, new Response(new App()), service('logger'));

        return $controller;
    }

    private function makeSettingController(
        CopilotOrchestrator $orchestrator,
        int $userId,
        string $method,
        array $jsonBody = []
    ): SettingController {
        $controller = new class($orchestrator, $userId) extends SettingController {
            public function __construct(CopilotOrchestrator $orchestrator, private readonly int $uid)
            {
                parent::__construct($orchestrator);
            }

            protected function resolveUserId(): int
            {
                return $this->uid;
            }
        };

        $request = $this->buildJsonRequest($method, 'api/copilot/settings', $jsonBody);
        $controller->initController($request, new Response(new App()), service('logger'));

        return $controller;
    }

    private function buildJsonRequest(string $method, string $path, array $body = []): IncomingRequest
    {
        $request = new IncomingRequest(new App(), new URI('http://localhost/' . $path), null, new UserAgent());
        $request->setMethod($method);
        if ($body !== []) {
            $request->setHeader('Content-Type', 'application/json');
            $request->setBody((string) json_encode($body));
        }

        return $request;
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

    private function cleanupTestArtifacts(BaseConnection $db, int $userId): void
    {
        if ($db->tableExists('copilot_messages')) {
            $db->table('copilot_messages')->where('user_id', $userId)->delete();
        }
        if ($db->tableExists('history_log')) {
            $db->table('history_log')->where('module', 'copilot')->where('add_by', $userId)->delete();
        }
    }

    /**
     * @param array<string, ?string> $originalSettings
     */
    private function restoreSettings(BaseConnection $db, array $originalSettings): void
    {
        if (! $db->tableExists('copilot_settings')) {
            return;
        }

        $db->table('copilot_settings')->whereIn('setting_key', SettingRepository::ALLOWED_KEYS)->delete();
        foreach ($originalSettings as $key => $val) {
            $db->table('copilot_settings')->insert([
                'setting_key'   => $key,
                'setting_value' => $val,
                'updated_at'    => date('Y-m-d H:i:s'),
            ]);
        }
    }
}

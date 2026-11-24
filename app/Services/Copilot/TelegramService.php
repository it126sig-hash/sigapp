<?php

namespace App\Services\Copilot;

use App\Exceptions\ServiceException;
use App\Repositories\Copilot\MessageRepository;
use App\Repositories\Copilot\TelegramLinkRepository;
use Config\Copilot as CopilotConfig;
use RuntimeException;

class TelegramService
{
    public const PAIRING_TTL_MINUTES = 10;
    public const TELEGRAM_MAX_MESSAGE_LENGTH = 4000;
    private const TELEGRAM_API_BASE = 'https://api.telegram.org';

    private TelegramLinkRepository $linkRepository;
    private MessageRepository $messageRepository;
    private CopilotOrchestrator $orchestrator;
    private CopilotAuthorizer $authorizer;
    private ToolRegistry $toolRegistry;
    private CopilotConfig $config;

    /**
     * @var callable(string, string, array, array, int): array{status: int, body: string|array}
     */
    private $httpTransport;

    private string $botToken;
    private string $webhookSecret;
    private string $hashSecret;

    /**
     * @param ?callable(string, string, array, array, int): array{status: int, body: string|array} $httpTransport
     */
    public function __construct(
        ?TelegramLinkRepository $linkRepository = null,
        ?MessageRepository $messageRepository = null,
        ?CopilotOrchestrator $orchestrator = null,
        ?CopilotAuthorizer $authorizer = null,
        ?ToolRegistry $toolRegistry = null,
        ?CopilotConfig $config = null,
        ?callable $httpTransport = null,
        ?string $botToken = null,
        ?string $webhookSecret = null,
        ?string $hashSecret = null,
    ) {
        $this->config = $config ?? config('Copilot');
        $this->linkRepository = $linkRepository ?? new TelegramLinkRepository();
        $this->messageRepository = $messageRepository ?? new MessageRepository();
        $this->authorizer = $authorizer ?? new CopilotAuthorizer(null, null, $this->config);
        $this->toolRegistry = $toolRegistry ?? new ToolRegistry($this->authorizer);
        $this->orchestrator = $orchestrator ?? new CopilotOrchestrator(
            toolRegistry: $this->toolRegistry,
            authorizer: $this->authorizer,
            messageRepository: $this->messageRepository,
            config: $this->config
        );
        $this->httpTransport = $httpTransport ?? [$this, 'defaultTransport'];

        $this->botToken = trim((string) ($botToken ?? env($this->config->telegramBotTokenEnv, '')));
        $this->webhookSecret = trim((string) ($webhookSecret ?? env($this->config->telegramWebhookSecretEnv, '')));

        $resolvedHashSecret = trim((string) ($hashSecret ?? env('encryption.key', 'sigapp-copilot')));
        $this->hashSecret = $resolvedHashSecret !== '' ? $resolvedHashSecret : 'sigapp-copilot';
    }

    public function getBotToken(): string
    {
        return $this->botToken;
    }

    public function getWebhookSecret(): string
    {
        return $this->webhookSecret;
    }

    /**
     * Buat kode pairing 6 digit (berlaku 10 menit) dan simpan hash-nya untuk user login.
     *
     * @return array{code: string, expires_at: string, expires_in_minutes: int}
     */
    public function generateLinkCode(int $userId): array
    {
        if (! $this->authorizer->canAccessCopilot($userId)) {
            throw new ServiceException('Anda tidak memiliki akses ke fitur AI Copilot.', 403);
        }

        $code = (string) random_int(100000, 999999);
        $expiresAt = date('Y-m-d H:i:s', time() + (self::PAIRING_TTL_MINUTES * 60));

        $this->linkRepository->savePairingCode($userId, $this->hashCode($code), $expiresAt);

        return [
            'code'               => $code,
            'expires_at'         => $expiresAt,
            'expires_in_minutes' => self::PAIRING_TTL_MINUTES,
        ];
    }

    /**
     * Putuskan tautan Telegram untuk user login.
     */
    public function unlink(int $userId): void
    {
        if (! $this->authorizer->canAccessCopilot($userId)) {
            throw new ServiceException('Anda tidak memiliki akses ke fitur AI Copilot.', 403);
        }

        $this->linkRepository->unlinkByUserId($userId);
    }

    /**
     * Proses satu object Update dari webhook atau long-polling Telegram.
     *
     * @param array<string, mixed> $update
     * @return array<string, mixed>
     */
    public function handleWebhookUpdate(array $update): array
    {
        $updateId = isset($update['update_id']) && is_numeric($update['update_id'])
            ? (int) $update['update_id']
            : 0;

        $message = $update['message'] ?? null;
        if (! is_array($message) || ! isset($message['text']) || trim((string) $message['text']) === '') {
            return ['status' => 'ignored_non_text'];
        }

        if ($updateId > 0 && $this->messageRepository->hasTelegramUpdateId($updateId)) {
            return ['status' => 'duplicate_ignored'];
        }

        $chatType = strtolower(trim((string) ($message['chat']['type'] ?? '')));
        if ($chatType !== 'private') {
            return ['status' => 'ignored_non_private'];
        }

        $telegramUserId = isset($message['from']['id']) && is_numeric($message['from']['id'])
            ? (int) $message['from']['id']
            : 0;
        $chatId = isset($message['chat']['id']) && is_numeric($message['chat']['id'])
            ? (int) $message['chat']['id']
            : 0;
        $username = isset($message['from']['username']) && is_string($message['from']['username'])
            ? trim($message['from']['username'])
            : null;
        $text = trim((string) $message['text']);

        if ($telegramUserId <= 0 || $chatId === 0) {
            return ['status' => 'ignored_invalid_sender'];
        }

        if (preg_match('/^\/([a-zA-Z0-9_]+)(?:@[a-zA-Z0-9_]+)?(?:\s+(.*))?$/s', $text, $matches)) {
            $command = strtolower($matches[1]);
            $arg = trim((string) ($matches[2] ?? ''));

            return $this->handleCommand($command, $arg, $telegramUserId, $chatId, $username);
        }

        $link = $this->linkRepository->findVerifiedByTelegramUserId($telegramUserId);
        if ($link === null) {
            $this->safeSendMessage(
                $chatId,
                "Akun Telegram Anda belum tertaut dengan akun SIGAPP.\n\n"
                . "Silakan buka menu AI Copilot di SIGAPP, klik tombol \"Hubungkan Telegram\", "
                . "lalu kirim perintah:\n/link KODE"
            );

            return ['status' => 'unlinked'];
        }

        $userId = (int) $link['user_id'];
        $this->sendChatAction($chatId, 'typing');

        try {
            $result = $this->orchestrator->handleUserMessage(
                $userId,
                $text,
                'telegram',
                $updateId > 0 ? $updateId : null
            );
        } catch (ServiceException $e) {
            $this->safeSendMessage($chatId, $e->getMessage());

            return [
                'status' => 'error_service',
                'code'   => $e->getStatusCode(),
            ];
        } catch (LlmProviderException $e) {
            log_message('warning', '[Copilot\TelegramService] LLM error: {message}', ['message' => $e->getMessage()]);
            $this->safeSendMessage(
                $chatId,
                'Maaf, layanan AI sedang mengalami gangguan atau timeout. Silakan coba beberapa saat lagi.'
            );

            return ['status' => 'error_llm'];
        } catch (\Throwable $e) {
            log_message('error', '[Copilot\TelegramService] Unexpected error: {message}', ['message' => $e->getMessage()]);
            $this->safeSendMessage(
                $chatId,
                'Maaf, terjadi kendala internal saat memproses pertanyaan Anda.'
            );

            return ['status' => 'error_internal'];
        }

        $chunks = $this->splitMessageChunks((string) ($result['reply'] ?? ''), self::TELEGRAM_MAX_MESSAGE_LENGTH);
        foreach ($chunks as $chunk) {
            $this->safeSendMessage($chatId, $chunk);
        }

        return [
            'status'     => 'replied',
            'user_id'    => $userId,
            'tools_used' => $result['tools_used'] ?? [],
        ];
    }

    /**
     * Daftarkan URL webhook Telegram beserta secret token header.
     *
     * @return array<string, mixed>
     */
    public function setWebhook(string $url, string $secretToken): array
    {
        $cleanUrl = trim($url);
        if ($cleanUrl === '' || filter_var($cleanUrl, FILTER_VALIDATE_URL) === false) {
            throw new RuntimeException('URL webhook Telegram tidak valid.');
        }
        if (trim($secretToken) === '') {
            throw new RuntimeException('Secret token webhook Telegram wajib diisi.');
        }

        return $this->callTelegramApi('setWebhook', [
            'url'             => $cleanUrl,
            'secret_token'    => trim($secretToken),
            'allowed_updates' => ['message'],
        ]);
    }

    /**
     * Hapus konfigurasi webhook Telegram.
     *
     * @return array<string, mixed>
     */
    public function deleteWebhook(bool $dropPending = false): array
    {
        return $this->callTelegramApi('deleteWebhook', [
            'drop_pending_updates' => $dropPending,
        ]);
    }

    /**
     * Ambil daftar update via long-polling (untuk development lokal).
     *
     * @return array<int, array<string, mixed>>
     */
    public function getUpdates(int $offset = 0, int $timeout = 20): array
    {
        $safeTimeout = max(0, min(50, $timeout));
        $response = $this->callTelegramApi('getUpdates', [
            'offset'          => $offset,
            'timeout'         => $safeTimeout,
            'allowed_updates' => ['message'],
        ], max(10, $safeTimeout + 10));

        $result = $response['result'] ?? [];

        return is_array($result) ? $result : [];
    }

    /**
     * Kirim pesan teks biasa ke `chat_id` Telegram.
     *
     * @return array<string, mixed>
     */
    public function sendMessage(int $chatId, string $text): array
    {
        return $this->callTelegramApi('sendMessage', [
            'chat_id' => $chatId,
            'text'    => $text,
        ]);
    }

    /**
     * Kirim indikator aksi (mis. `typing`) ke Telegram secara best-effort.
     */
    public function sendChatAction(int $chatId, string $action = 'typing'): void
    {
        if ($this->botToken === '') {
            return;
        }

        try {
            $this->callTelegramApi('sendChatAction', [
                'chat_id' => $chatId,
                'action'  => $action,
            ], 5);
        } catch (\Throwable) {
            // Best-effort: kegagalan typing indicator tidak boleh menggagalkan balasan utama.
        }
    }

    /**
     * Pecah pesan panjang menjadi potongan <= $maxLen karakter UTF-8.
     *
     * @return string[]
     */
    public function splitMessageChunks(string $text, int $maxLen = self::TELEGRAM_MAX_MESSAGE_LENGTH): array
    {
        $clean = trim($text);
        if ($clean === '') {
            return [];
        }

        $maxLen = max(100, $maxLen);
        if (mb_strlen($clean, 'UTF-8') <= $maxLen) {
            return [$clean];
        }

        $chunks = [];
        $remaining = $clean;

        while ($remaining !== '') {
            if (mb_strlen($remaining, 'UTF-8') <= $maxLen) {
                $chunks[] = $remaining;
                break;
            }

            $slice = mb_substr($remaining, 0, $maxLen, 'UTF-8');
            $splitPos = mb_strrpos($slice, "\n", 0, 'UTF-8');
            if ($splitPos === false || $splitPos < (int) ($maxLen * 0.5)) {
                $spacePos = mb_strrpos($slice, ' ', 0, 'UTF-8');
                if ($spacePos !== false && $spacePos >= (int) ($maxLen * 0.5)) {
                    $splitPos = $spacePos;
                } else {
                    $splitPos = $maxLen;
                }
            }

            $chunk = rtrim(mb_substr($remaining, 0, $splitPos, 'UTF-8'));
            if ($chunk === '') {
                $chunk = mb_substr($remaining, 0, $maxLen, 'UTF-8');
                $splitPos = $maxLen;
            }

            $chunks[] = $chunk;
            $remaining = ltrim(mb_substr($remaining, $splitPos, null, 'UTF-8'));
        }

        return $chunks;
    }

    public function hashCode(string $code): string
    {
        return hash_hmac('sha256', trim($code), $this->hashSecret);
    }

    /**
     * @return array<string, mixed>
     */
    private function handleCommand(
        string $command,
        string $arg,
        int $telegramUserId,
        int $chatId,
        ?string $username
    ): array {
        if ($command === 'start') {
            $this->safeSendMessage(
                $chatId,
                "Selamat datang di AI Copilot SIGAPP.\n\n"
                . "Untuk menautkan akun SIGAPP Anda:\n"
                . "1. Buka tab AI Copilot di aplikasi web SIGAPP.\n"
                . "2. Klik tombol \"Hubungkan Telegram\" untuk membuat kode 6 digit.\n"
                . "3. Kirim perintah /link KODE di chat ini.\n\n"
                . "Ketik /help untuk melihat contoh pertanyaan."
            );

            return ['status' => 'command_start'];
        }

        if ($command === 'link') {
            if (! preg_match('/^\d{6}$/', $arg)) {
                $this->safeSendMessage(
                    $chatId,
                    "Format kode tidak valid.\nKirim perintah /link diikuti 6 digit angka dari tab AI Copilot SIGAPP.\nContoh: /link 123456"
                );

                return ['status' => 'link_invalid'];
            }

            $now = date('Y-m-d H:i:s');
            $bound = $this->linkRepository->verifyAndBindByCodeHash(
                $this->hashCode($arg),
                $telegramUserId,
                $chatId,
                $username,
                $now
            );

            if ($bound === null) {
                $this->safeSendMessage(
                    $chatId,
                    'Kode pairing tidak valid atau sudah kedaluwarsa. Silakan buat kode baru dari tab AI Copilot di SIGAPP.'
                );

                return ['status' => 'link_invalid'];
            }

            $userId = (int) $bound['user_id'];
            $displayName = $bound['sigapp_name'] ?? $bound['sigapp_username'] ?? ('User #' . $userId);
            $allowedTools = $this->toolRegistry->getAllowedToolNamesForUser($userId);
            $capabilitiesText = $this->formatAllowedCapabilities($allowedTools);

            $this->safeSendMessage(
                $chatId,
                "Akun Telegram berhasil ditautkan ke akun SIGAPP: {$displayName}.\n\n"
                . "Kemampuan AI Copilot sesuai peran Anda:\n{$capabilitiesText}\n\n"
                . "Silakan langsung ketik pertanyaan Anda, atau ketik /help untuk contoh pertanyaan."
            );

            return [
                'status'  => 'linked',
                'user_id' => $userId,
            ];
        }

        if ($command === 'unlink') {
            $link = $this->linkRepository->findVerifiedByTelegramUserId($telegramUserId);
            if ($link === null) {
                $this->safeSendMessage($chatId, 'Akun Telegram ini belum tertaut ke akun SIGAPP mana pun.');

                return ['status' => 'unlink_not_linked'];
            }

            $userId = (int) $link['user_id'];
            $this->linkRepository->unlinkByUserId($userId);
            $this->safeSendMessage(
                $chatId,
                'Tautan akun SIGAPP dengan Telegram berhasil diputuskan. Anda dapat menautkannya kembali kapan saja lewat tab AI Copilot.'
            );

            return [
                'status'  => 'unlinked_command',
                'user_id' => $userId,
            ];
        }

        if ($command === 'help') {
            $this->safeSendMessage(
                $chatId,
                "Panduan AI Copilot SIGAPP:\n"
                . "- Contoh pertanyaan jatuh tempo cicilan: \"Tagihan cicilan jatuh tempo 7 hari ke depan\"\n"
                . "- Contoh pertanyaan booking bulanan: \"Rekap booking per bulan tahun 2026\"\n"
                . "- Contoh pertanyaan konversi akad: \"Berapa konversi booking ke akad tahun ini?\"\n\n"
                . "Perintah tersedia:\n"
                . "/link KODE - Tautkan akun SIGAPP\n"
                . "/unlink - Putuskan tautan akun\n"
                . "/help - Tampilkan bantuan ini"
            );

            return ['status' => 'command_help'];
        }

        $this->safeSendMessage(
            $chatId,
            'Perintah tidak dikenali. Ketik /help untuk melihat daftar perintah dan contoh pertanyaan.'
        );

        return ['status' => 'command_unknown'];
    }

    /**
     * @param string[] $allowedTools
     */
    private function formatAllowedCapabilities(array $allowedTools): string
    {
        if ($allowedTools === []) {
            return '- Belum ada tool operasional yang aktif untuk akun ini.';
        }

        $labels = [
            'get_jatuh_tempo_cicilan'   => '- Cek tagihan cicilan terlambat & akan jatuh tempo',
            'get_booking_per_bulan'     => '- Rekap jumlah booking per bulan per proyek',
            'get_konversi_booking_akad' => '- Rasio konversi booking menjadi akad',
        ];

        $lines = [];
        foreach ($allowedTools as $toolName) {
            $lines[] = $labels[$toolName] ?? ('- ' . $toolName);
        }

        return implode("\n", $lines);
    }

    private function safeSendMessage(int $chatId, string $text): void
    {
        if ($this->botToken === '' || trim($text) === '') {
            return;
        }

        try {
            $this->sendMessage($chatId, $text);
        } catch (\Throwable $e) {
            log_message('warning', '[Copilot\TelegramService::safeSendMessage] {message}', ['message' => $e->getMessage()]);
        }
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function callTelegramApi(string $method, array $payload = [], int $timeout = 15): array
    {
        if ($this->botToken === '') {
            throw new RuntimeException('TELEGRAM_BOT_TOKEN belum dikonfigurasi.');
        }

        $url = self::TELEGRAM_API_BASE . '/bot' . $this->botToken . '/' . $method;
        $response = ($this->httpTransport)(
            'POST',
            $url,
            ['Content-Type' => 'application/json'],
            $payload,
            $timeout
        );

        $status = (int) ($response['status'] ?? 0);
        $rawBody = $response['body'] ?? '';
        $decoded = is_array($rawBody) ? $rawBody : json_decode((string) $rawBody, true);

        if ($status >= 400 || ! is_array($decoded) || empty($decoded['ok'])) {
            $desc = is_array($decoded)
                ? (string) ($decoded['description'] ?? "HTTP {$status}")
                : "HTTP {$status} dengan respons tidak valid";

            throw new RuntimeException("Telegram API {$method} gagal: {$desc}");
        }

        return $decoded;
    }

    /**
     * @param array<string, string> $headers
     * @param array<string, mixed> $body
     * @return array{status: int, body: string}
     */
    private function defaultTransport(string $method, string $url, array $headers, array $body, int $timeout): array
    {
        $client = \Config\Services::curlrequest([
            'timeout'     => max(5, $timeout),
            'http_errors' => false,
        ]);

        $options = ['headers' => $headers];
        if (strtoupper($method) !== 'GET' && $body !== []) {
            $options['json'] = $body;
        }

        $response = $client->request($method, $url, $options);

        return [
            'status' => (int) $response->getStatusCode(),
            'body'   => (string) $response->getBody(),
        ];
    }
}

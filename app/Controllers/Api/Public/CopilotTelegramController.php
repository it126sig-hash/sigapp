<?php

namespace App\Controllers\Api\Public;

use App\Controllers\Api\BaseApiController;
use App\Services\Copilot\TelegramService;
use CodeIgniter\HTTP\ResponseInterface;

class CopilotTelegramController extends BaseApiController
{
    private TelegramService $telegramService;

    public function __construct(?TelegramService $telegramService = null)
    {
        $this->telegramService = $telegramService ?? new TelegramService();
    }

    /**
     * POST /api/public/copilot/telegram/webhook
     */
    public function webhook(): ResponseInterface
    {
        $serverSecret = $this->telegramService->getWebhookSecret();
        if ($serverSecret === '') {
            return $this->error('Webhook Telegram belum dikonfigurasi pada server.', 503);
        }

        $providedSecret = trim((string) $this->request->getHeaderLine('X-Telegram-Bot-Api-Secret-Token'));
        if ($providedSecret === '' || ! hash_equals($serverSecret, $providedSecret)) {
            return $this->error('Secret token webhook tidak valid.', 403);
        }

        $update = $this->parseUpdatePayload();
        if ($update === null) {
            return $this->success(['status' => 'ignored_non_text'], 'Payload webhook diabaikan.');
        }

        try {
            $result = $this->telegramService->handleWebhookUpdate($update);

            return $this->success(['status' => (string) ($result['status'] ?? 'ok')], 'Webhook Telegram diproses.');
        } catch (\Throwable $e) {
            log_message('error', '[Api\Public\CopilotTelegramController::webhook] {message}', ['message' => $e->getMessage()]);

            return $this->success(['status' => 'error_handled'], 'Webhook Telegram diproses.');
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    private function parseUpdatePayload(): ?array
    {
        try {
            $decoded = $this->request->getJSON(true);
            if (is_array($decoded)) {
                return $decoded;
            }
        } catch (\Throwable) {
            $raw = json_decode((string) $this->request->getBody(), true);
            if (is_array($raw)) {
                return $raw;
            }
        }

        return null;
    }
}

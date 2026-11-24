<?php

namespace App\Controllers\Api\Copilot;

use App\Controllers\Api\BaseApiController;
use App\Exceptions\ServiceException;
use App\Services\Copilot\CopilotOrchestrator;
use App\Services\Copilot\LlmProviderException;
use CodeIgniter\HTTP\ResponseInterface;

class ChatController extends BaseApiController
{
    private CopilotOrchestrator $orchestrator;

    public function __construct(?CopilotOrchestrator $orchestrator = null)
    {
        if (! function_exists('user_id')) {
            helper('auth');
        }

        $this->orchestrator = $orchestrator ?? new CopilotOrchestrator();
    }

    /**
     * GET /api/copilot/meta
     */
    public function meta(): ResponseInterface
    {
        $userId = $this->resolveUserId();
        if ($userId <= 0) {
            return $this->error('Sesi telah berakhir, silakan login kembali.', 401);
        }

        try {
            $data = $this->orchestrator->getMetaForUser($userId);

            return $this->success($data, 'Metadata AI Copilot berhasil dimuat.');
        } catch (\Throwable $e) {
            log_message('error', '[Api\Copilot\ChatController::meta] {message}', ['message' => $e->getMessage()]);

            return $this->error('Gagal memuat metadata AI Copilot.', 500);
        }
    }

    /**
     * GET /api/copilot/history
     */
    public function history(): ResponseInterface
    {
        $userId = $this->resolveUserId();
        if ($userId <= 0) {
            return $this->error('Sesi telah berakhir, silakan login kembali.', 401);
        }

        $input = $this->readRequestData();
        $limit = isset($input['limit']) && is_numeric($input['limit']) ? (int) $input['limit'] : 30;
        $limit = max(1, min(100, $limit));

        try {
            $rows = $this->orchestrator->getChatHistory($userId, 'web', $limit);
            $messages = array_map(static fn (array $row): array => [
                'id'         => (int) $row['id'],
                'role'       => (string) $row['role'],
                'content'    => (string) $row['content'],
                'metadata'   => $row['metadata'] ?? null,
                'created_at' => (string) $row['created_at'],
            ], $rows);

            return $this->success([
                'messages' => $messages,
                'limit'    => $limit,
            ], 'Riwayat percakapan berhasil dimuat.');
        } catch (ServiceException $e) {
            return $this->error($e->getMessage(), $e->getStatusCode(), $e->getErrors() !== [] ? $e->getErrors() : null);
        } catch (\Throwable $e) {
            log_message('error', '[Api\Copilot\ChatController::history] {message}', ['message' => $e->getMessage()]);

            return $this->error('Gagal memuat riwayat percakapan AI Copilot.', 500);
        }
    }

    /**
     * POST /api/copilot/chat
     */
    public function chat(): ResponseInterface
    {
        $userId = $this->resolveUserId();
        if ($userId <= 0) {
            return $this->error('Sesi telah berakhir, silakan login kembali.', 401);
        }

        $input = $this->readRequestData();
        $message = is_string($input['message'] ?? null) ? $input['message'] : '';

        try {
            $result = $this->orchestrator->handleUserMessage($userId, $message, 'web');

            return $this->success($result, 'Pesan berhasil diproses.');
        } catch (ServiceException $e) {
            return $this->error($e->getMessage(), $e->getStatusCode(), $e->getErrors() !== [] ? $e->getErrors() : null);
        } catch (LlmProviderException $e) {
            log_message('warning', '[Api\Copilot\ChatController::chat] LLM error: {message}', ['message' => $e->getMessage()]);

            return $this->error($e->getMessage(), 502);
        } catch (\Throwable $e) {
            log_message('error', '[Api\Copilot\ChatController::chat] {message}', ['message' => $e->getMessage()]);

            return $this->error('Terjadi kesalahan internal saat memproses pesan Copilot.', 500);
        }
    }

    protected function resolveUserId(): int
    {
        return (int) (user_id() ?? 0);
    }
}

<?php

namespace App\Controllers\Api\Copilot;

use App\Controllers\Api\BaseApiController;
use App\Exceptions\ServiceException;
use App\Services\Copilot\TelegramService;
use CodeIgniter\HTTP\ResponseInterface;

class TelegramLinkController extends BaseApiController
{
    private TelegramService $telegramService;

    public function __construct(?TelegramService $telegramService = null)
    {
        if (! function_exists('user_id')) {
            helper('auth');
        }

        $this->telegramService = $telegramService ?? new TelegramService();
    }

    /**
     * POST /api/copilot/telegram/link
     */
    public function create(): ResponseInterface
    {
        $userId = $this->resolveUserId();
        if ($userId <= 0) {
            return $this->error('Sesi telah berakhir, silakan login kembali.', 401);
        }

        try {
            $data = $this->telegramService->generateLinkCode($userId);

            return $this->successCreated($data, 'Kode pairing Telegram berhasil dibuat.');
        } catch (ServiceException $e) {
            return $this->error($e->getMessage(), $e->getStatusCode(), $e->getErrors() !== [] ? $e->getErrors() : null);
        } catch (\Throwable $e) {
            log_message('error', '[Api\Copilot\TelegramLinkController::create] {message}', ['message' => $e->getMessage()]);

            return $this->error('Gagal membuat kode pairing Telegram.', 500);
        }
    }

    /**
     * DELETE /api/copilot/telegram/link
     */
    public function delete($id = null): ResponseInterface
    {
        $userId = $this->resolveUserId();
        if ($userId <= 0) {
            return $this->error('Sesi telah berakhir, silakan login kembali.', 401);
        }

        try {
            $this->telegramService->unlink($userId);

            return $this->success(['linked' => false], 'Tautan Telegram berhasil diputuskan.');
        } catch (ServiceException $e) {
            return $this->error($e->getMessage(), $e->getStatusCode(), $e->getErrors() !== [] ? $e->getErrors() : null);
        } catch (\Throwable $e) {
            log_message('error', '[Api\Copilot\TelegramLinkController::delete] {message}', ['message' => $e->getMessage()]);

            return $this->error('Gagal memutuskan tautan Telegram.', 500);
        }
    }

    protected function resolveUserId(): int
    {
        return (int) (user_id() ?? 0);
    }
}

<?php

namespace App\Controllers\Api\Copilot;

use App\Controllers\Api\BaseApiController;
use App\Exceptions\ServiceException;
use App\Services\Copilot\CopilotOrchestrator;
use CodeIgniter\HTTP\ResponseInterface;

class SettingController extends BaseApiController
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
     * GET /api/copilot/settings
     */
    public function index(): ResponseInterface
    {
        $userId = $this->resolveUserId();
        if ($userId <= 0) {
            return $this->error('Sesi telah berakhir, silakan login kembali.', 401);
        }

        try {
            $data = $this->orchestrator->getSettingsForAdmin($userId);

            return $this->success($data, 'Pengaturan AI Copilot berhasil dimuat.');
        } catch (ServiceException $e) {
            return $this->error($e->getMessage(), $e->getStatusCode(), $e->getErrors() !== [] ? $e->getErrors() : null);
        } catch (\Throwable $e) {
            log_message('error', '[Api\Copilot\SettingController::index] {message}', ['message' => $e->getMessage()]);

            return $this->error('Gagal memuat pengaturan AI Copilot.', 500);
        }
    }

    /**
     * POST /api/copilot/settings
     */
    public function update($id = null): ResponseInterface
    {
        $userId = $this->resolveUserId();
        if ($userId <= 0) {
            return $this->error('Sesi telah berakhir, silakan login kembali.', 401);
        }

        $input = $this->readRequestData();
        $provider = is_string($input['provider'] ?? null) ? $input['provider'] : '';
        $model = is_string($input['model'] ?? null) ? $input['model'] : '';
        $baseUrl = isset($input['base_url']) && is_string($input['base_url']) ? $input['base_url'] : null;

        try {
            $data = $this->orchestrator->updateProviderSettings($userId, $provider, $model, $baseUrl);

            return $this->success($data, 'Pengaturan AI Copilot berhasil disimpan.');
        } catch (ServiceException $e) {
            return $this->error($e->getMessage(), $e->getStatusCode(), $e->getErrors() !== [] ? $e->getErrors() : null);
        } catch (\Throwable $e) {
            log_message('error', '[Api\Copilot\SettingController::update] {message}', ['message' => $e->getMessage()]);

            return $this->error('Gagal menyimpan pengaturan AI Copilot.', 500);
        }
    }

    protected function resolveUserId(): int
    {
        return (int) (user_id() ?? 0);
    }
}

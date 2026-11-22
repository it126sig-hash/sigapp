<?php

namespace App\Controllers\Api\SalesExternal;

use App\Controllers\Api\BaseApiController;
use App\Services\SalesExternal\FeeSalesSettingService;
use CodeIgniter\HTTP\ResponseInterface;

class FeeSalesSettingController extends BaseApiController
{
    protected $helpers = ['auth'];

    protected FeeSalesSettingService $feeSalesSettingService;

    public function __construct(?FeeSalesSettingService $feeSalesSettingService = null)
    {
        $this->feeSalesSettingService = $feeSalesSettingService ?? new FeeSalesSettingService();
    }

    /**
     * Mengambil daftar hak akses per role
     */
    public function listPermissions(): ResponseInterface
    {
        try {
            $data = $this->feeSalesSettingService->listPermissions();

            return $this->jsonResponse(true, 'Data hak akses berhasil dimuat.', 200, $data);
        } catch (\Throwable $e) {
            log_message('error', 'Error listPermissions fee sales: ' . $e->getMessage());

            return $this->jsonResponse(false, 'Gagal mengambil data hak akses: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Menyimpan matriks hak akses seluruh role
     */
    public function savePermissions(): ResponseInterface
    {
        try {
            if (! function_exists('user_id')) {
                helper('auth');
            }

            $input          = $this->readRequestData();
            $rawPermissions = $input['permissions'] ?? null;

            if ($rawPermissions === null) {
                try {
                    $json           = $this->request->getJSON(true);
                    $rawPermissions = is_array($json) ? ($json['permissions'] ?? null) : null;
                } catch (\Throwable) {
                    $rawPermissions = null;
                }
            }

            if (is_string($rawPermissions)) {
                $permissions = json_decode($rawPermissions, true);
            } else {
                $permissions = $rawPermissions;
            }

            if (! is_array($permissions)) {
                return $this->jsonResponse(false, 'Format data permissions tidak valid', 400);
            }

            $saved = $this->feeSalesSettingService->savePermissions($permissions, (int) user_id());

            if (! $saved) {
                return $this->jsonResponse(false, 'Gagal menyimpan perubahan hak akses ke database', 500);
            }

            return $this->jsonResponse(true, 'Hak akses berhasil disimpan', 200);
        } catch (\Throwable $e) {
            log_message('error', 'Error savePermissions fee sales: ' . $e->getMessage());

            return $this->jsonResponse(false, 'Terjadi kesalahan sistem: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Envelope JSON konsisten yang menyertakan `message` (singular untuk view legacy)
     * serta `messages` (plural standar BaseApiController) beserta token CSRF terbaru.
     */
    protected function jsonResponse(bool $success, string $message, int $code = 200, mixed $data = null): ResponseInterface
    {
        $body = [
            'success'  => $success,
            'message'  => $message,
            'messages' => $message,
        ];

        if ($data !== null) {
            $body['data'] = $data;
        }

        $body['token'] = csrf_hash();

        return $this->respond($body, $code);
    }
}

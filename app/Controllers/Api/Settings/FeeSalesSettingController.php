<?php

namespace App\Controllers\Api\Settings;

use App\Controllers\BaseController;
use App\Services\Settings\FeeSalesSettingService;

class FeeSalesSettingController extends BaseController
{
    protected FeeSalesSettingService $feeSalesSettingService;

    public function __construct()
    {
        $this->feeSalesSettingService = new FeeSalesSettingService();
    }

    /**
     * Mengambil daftar hak akses per role
     */
    public function listPermissions()
    {
        try {
            $data = $this->feeSalesSettingService->listPermissions();

            return $this->response->setJSON([
                'success' => true,
                'data'    => $data,
                'token'   => csrf_hash(),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Error listPermissions fee sales: ' . $e->getMessage());

            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Gagal mengambil data hak akses: ' . $e->getMessage(),
                'token'   => csrf_hash(),
            ]);
        }
    }

    /**
     * Menyimpan matriks hak akses seluruh role
     */
    public function savePermissions()
    {
        try {
            $rawPermissions = $this->request->getPost('permissions');
            if (empty($rawPermissions)) {
                $json = $this->request->getJSON(true);
                $rawPermissions = $json['permissions'] ?? null;
            }

            if (is_string($rawPermissions)) {
                $permissions = json_decode($rawPermissions, true);
            } else {
                $permissions = $rawPermissions;
            }

            if (! is_array($permissions)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => 'Format data permissions tidak valid',
                    'token'   => csrf_hash(),
                ]);
            }

            $saved = $this->feeSalesSettingService->savePermissions($permissions, (int) user_id());

            if (! $saved) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => 'Gagal menyimpan perubahan hak akses ke database',
                    'token'   => csrf_hash(),
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Hak akses berhasil disimpan',
                'token'   => csrf_hash(),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Error savePermissions fee sales: ' . $e->getMessage());

            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage(),
                'token'   => csrf_hash(),
            ]);
        }
    }
}

<?php

namespace App\Controllers\Api\Settings;

use App\Controllers\Api\BaseApiController;
use App\Services\Settings\SettingDataService;
use CodeIgniter\Exceptions\PageNotFoundException;

class SettingDataController extends BaseApiController
{
    private const MENU_URL = 'setting-data';

    protected SettingDataService $service;

    public function __construct()
    {
        $this->service = new SettingDataService();
    }

    public function meta()
    {
        if ($denied = $this->ensureMenuAccess(self::MENU_URL)) {
            return $denied;
        }

        return $this->success([
            'tables'      => $this->service->getMetaTables(),
            'permissions' => [
                'can_manage' => true,
                'can_add'    => true,
                'can_edit'   => true,
                'can_delete' => true,
            ],
        ], 'Metadata setting data berhasil dimuat');
    }

    public function list(string $key)
    {
        if ($denied = $this->ensureMenuAccess(self::MENU_URL)) {
            return $denied;
        }

        try {
            if ($this->isRawFormat()) {
                $params = $this->readRequestData();
                $params['format'] = 'raw';

                return $this->success(
                    $this->service->list($key, $params),
                    'Data setting berhasil dimuat'
                );
            }

            return $this->respond($this->service->list($key));
        } catch (PageNotFoundException $e) {
            return $this->error($e->getMessage(), 404);
        }
    }

    public function get(string $key)
    {
        if ($denied = $this->ensureMenuAccess(self::MENU_URL)) {
            return $denied;
        }

        try {
            $input = $this->readRequestData();
            $config = $this->service->getConfig($key);
            $id = (int) ($input['id'] ?? $input[$config['primary_key']] ?? 0);
            $raw = $this->isRawFormat();
            $row = $this->service->get($key, $id, $raw);

            if (!$row) {
                if ($raw) {
                    return $this->error('Data tidak ditemukan', 404);
                }

                return $this->respond([
                    'success'  => false,
                    'token'    => csrf_hash(),
                    'messages' => 'Data tidak ditemukan',
                ]);
            }

            return $raw ? $this->success($row) : $this->respond($row);
        } catch (PageNotFoundException $e) {
            return $this->error($e->getMessage(), 404);
        }
    }

    public function save(string $key)
    {
        if ($denied = $this->ensureMenuAccess(self::MENU_URL)) {
            return $denied;
        }

        try {
            $userId = (int) (user_id() ?? 0);
            $raw = $this->isRawFormat();
            $result = $this->service->save($key, $this->readRequestData(), $userId, $raw);

            return $this->respondResult($result, $raw);
        } catch (PageNotFoundException $e) {
            return $this->error($e->getMessage(), 404);
        }
    }

    public function delete($key = null)
    {
        if ($denied = $this->ensureMenuAccess(self::MENU_URL)) {
            return $denied;
        }

        try {
            $key = (string) ($key ?? '');
            $userId = (int) (user_id() ?? 0);
            $input = $this->readRequestData();
            $config = $this->service->getConfig($key);
            $id = (int) ($input['id'] ?? $input[$config['primary_key']] ?? 0);
            $raw = $this->isRawFormat();
            $result = $this->service->delete($key, $id, $userId, $raw);

            return $this->respondResult($result, $raw);
        } catch (PageNotFoundException $e) {
            return $this->error($e->getMessage(), 404);
        }
    }

    private function respondResult(array $result, bool $raw)
    {
        if (!$raw) {
            return $this->respond($result);
        }

        if (!($result['success'] ?? false)) {
            return $this->error(
                (string) ($result['messages'] ?? 'Permintaan gagal'),
                (int) ($result['status'] ?? 422),
                $result['errors'] ?? null
            );
        }

        if (!empty($result['created'])) {
            return $this->successCreated($result['data'] ?? null, (string) ($result['messages'] ?? 'Created'));
        }

        return $this->success($result['data'] ?? null, (string) ($result['messages'] ?? 'Success'));
    }
}

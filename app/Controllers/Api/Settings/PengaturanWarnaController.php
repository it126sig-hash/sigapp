<?php

namespace App\Controllers\Api\Settings;

use App\Controllers\Api\BaseApiController;
use App\Services\Settings\PengaturanWarnaService;

class PengaturanWarnaController extends BaseApiController
{
    private const MENU_URL = 'configcolor';

    protected PengaturanWarnaService $service;

    public function __construct()
    {
        $this->service = new PengaturanWarnaService();
    }

    public function meta()
    {
        if ($denied = $this->ensureMenuAccess(self::MENU_URL)) {
            return $denied;
        }

        return $this->success([
            'permissions' => [
                'can_manage' => true,
                'can_add'    => true,
                'can_edit'   => true,
                'can_remove' => true,
            ],
        ], 'Metadata pengaturan warna berhasil dimuat');
    }

    public function getAll()
    {
        if ($denied = $this->ensureMenuAccess(self::MENU_URL)) {
            return $denied;
        }

        if ($this->isRawFormat()) {
            $params = $this->readRequestData();
            $params['format'] = 'raw';

            return $this->success(
                $this->service->getAll($params),
                'Data pengaturan warna berhasil dimuat'
            );
        }

        return $this->respond($this->service->getAll());
    }

    public function getOne()
    {
        if ($denied = $this->ensureMenuAccess(self::MENU_URL)) {
            return $denied;
        }

        $input = $this->readRequestData();
        $configName = trim((string) ($input['config_name'] ?? $input['configName'] ?? ''));
        $raw = $this->isRawFormat();
        $data = $this->service->getOne($configName, $raw);

        if (!$data) {
            if ($raw) {
                return $this->error('Data tidak ditemukan', 404);
            }

            return $this->respond([
                'success'  => false,
                'token'    => csrf_hash(),
                'messages' => 'Data tidak ditemukan',
            ]);
        }

        return $raw ? $this->success($data) : $this->respond($data);
    }

    public function add()
    {
        if ($denied = $this->ensureMenuAccess(self::MENU_URL)) {
            return $denied;
        }

        $userName = user() ? user()->username : null;
        $raw = $this->isRawFormat();
        $result = $this->service->add($this->readRequestData(), $userName, $raw);

        return $this->respondResult($result, $raw);
    }

    public function edit($id = null)
    {
        if ($denied = $this->ensureMenuAccess(self::MENU_URL)) {
            return $denied;
        }

        $input = $this->readRequestData();
        if ($id !== null && empty($input['configName']) && empty($input['config_name'])) {
            $input['configName'] = (string) $id;
        }

        $userName = user() ? user()->username : null;
        $raw = $this->isRawFormat();
        $result = $this->service->edit($input, $userName, $raw);

        return $this->respondResult($result, $raw);
    }

    public function remove()
    {
        if ($denied = $this->ensureMenuAccess(self::MENU_URL)) {
            return $denied;
        }

        $input = $this->readRequestData();
        $configName = trim((string) ($input['config_name'] ?? $input['configName'] ?? ''));
        $raw = $this->isRawFormat();
        $result = $this->service->remove($configName, $raw);

        return $this->respondResult($result, $raw);
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

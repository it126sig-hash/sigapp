<?php

namespace App\Controllers\Api\Settings;

use App\Controllers\Api\BaseApiController;
use App\Services\Settings\SettingDataService;

class SettingDataController extends BaseApiController
{
    protected SettingDataService $service;

    public function __construct()
    {
        $this->service = new SettingDataService();
    }

    public function list(string $key)
    {
        return $this->respond($this->service->list($key));
    }

    public function get(string $key)
    {
        $id = (int) $this->request->getPost('id');
        $row = $this->service->get($key, $id);

        if (!$row) {
            return $this->respond([
                'success'  => false,
                'token'    => csrf_hash(),
                'messages' => 'Data tidak ditemukan',
            ]);
        }

        return $this->respond($row);
    }

    public function save(string $key)
    {
        $userId = (int) (user_id() ?? 0);
        $result = $this->service->save($key, (array) $this->request->getPost(), $userId);

        return $this->respond($result);
    }

    public function delete($key = null)
    {
        $key = (string) ($key ?? '');
        $userId = (int) (user_id() ?? 0);
        $id = (int) $this->request->getPost('id');
        $result = $this->service->delete($key, $id, $userId);

        return $this->respond($result);
    }
}

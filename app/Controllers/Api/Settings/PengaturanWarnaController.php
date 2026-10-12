<?php

namespace App\Controllers\Api\Settings;

use App\Controllers\Api\BaseApiController;
use App\Services\Settings\PengaturanWarnaService;

class PengaturanWarnaController extends BaseApiController
{
    protected PengaturanWarnaService $service;

    public function __construct()
    {
        $this->service = new PengaturanWarnaService();
    }

    public function getAll()
    {
        return $this->respond($this->service->getAll());
    }

    public function getOne()
    {
        $configName = (string) $this->request->getPost('config_name');
        $data = $this->service->getOne($configName);

        if (!$data) {
            return $this->respond([
                'success'  => false,
                'token'    => csrf_hash(),
                'messages' => 'Data tidak ditemukan',
            ]);
        }

        return $this->respond($data);
    }

    public function add()
    {
        $userName = user() ? user()->username : null;
        $result = $this->service->add((array) $this->request->getPost(), $userName);

        return $this->respond($result);
    }

    public function edit($id = null)
    {
        $userName = user() ? user()->username : null;
        $result = $this->service->edit((array) $this->request->getPost(), $userName);

        return $this->respond($result);
    }

    public function remove()
    {
        $configName = (string) $this->request->getPost('config_name');
        $result = $this->service->remove($configName);

        return $this->respond($result);
    }
}

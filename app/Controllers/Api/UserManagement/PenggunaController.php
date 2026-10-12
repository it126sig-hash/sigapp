<?php

namespace App\Controllers\Api\UserManagement;

use App\Controllers\Api\BaseApiController;
use App\Services\UserManagement\PenggunaService;

class PenggunaController extends BaseApiController
{
    protected PenggunaService $penggunaService;

    public function __construct()
    {
        $this->penggunaService = new PenggunaService();
    }

    public function getAll()
    {
        return $this->respond($this->penggunaService->getDatatable());
    }

    public function getOne()
    {
        $id = (int) $this->request->getPost('id_user');
        if ($id <= 0) {
            return $this->respond([
                'success'  => false,
                'messages' => 'ID pengguna tidak valid',
                'token'    => csrf_hash(),
            ], 400);
        }

        $user = $this->penggunaService->getOne($id);
        if (!$user) {
            return $this->respond([
                'success'  => false,
                'messages' => 'Pengguna tidak ditemukan',
                'token'    => csrf_hash(),
            ], 404);
        }

        return $this->respond($user);
    }

    public function add()
    {
        $result = $this->penggunaService->addUser((array) $this->request->getPost());
        return $this->respond($result);
    }

    public function edit($id = null)
    {
        $result = $this->penggunaService->editUser((array) $this->request->getPost());
        return $this->respond($result);
    }

    public function edit_user()
    {
        return $this->edit();
    }

    public function remove()
    {
        $id     = (int) $this->request->getPost('id');
        $active = (int) $this->request->getPost('active');

        $result = $this->penggunaService->toggleStatus($id, $active);
        return $this->respond($result);
    }
}

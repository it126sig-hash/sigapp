<?php

namespace App\Controllers;

use App\Services\UserManagement\PenggunaService;

/**
 * Backward compatibility shim.
 * Canonical implementation located in:
 * - Web: App\Controllers\Web\UserManagement\PenggunaController
 * - Api: App\Controllers\Api\UserManagement\PenggunaController
 * - Service: App\Services\UserManagement\PenggunaService
 */
class Pengguna extends BaseController
{
    protected PenggunaService $penggunaService;

    public function __construct()
    {
        $this->penggunaService = new PenggunaService();
    }

    public function index()
    {
        return $this->setuser();
    }

    public function setuser()
    {
        $data['content'] = 'user/setuser';
        $data['data']    = $this->penggunaService->getSetuserData();

        return view('template', $data);
    }

    public function getAll()
    {
        return $this->response->setJSON($this->penggunaService->getDatatable());
    }

    public function getOne()
    {
        $id = (int) $this->request->getPost('id_user');
        if ($id <= 0) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }

        $user = $this->penggunaService->getOne($id);
        if (!$user) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }

        return $this->response->setJSON($user);
    }

    public function add()
    {
        $result = $this->penggunaService->addUser((array) $this->request->getPost());
        return $this->response->setJSON($result);
    }

    public function edit_user()
    {
        $result = $this->penggunaService->editUser((array) $this->request->getPost());
        return $this->response->setJSON($result);
    }

    public function remove()
    {
        $id     = (int) $this->request->getPost('id');
        $active = (int) $this->request->getPost('active');

        $result = $this->penggunaService->toggleStatus($id, $active);
        return $this->response->setJSON($result);
    }

    public function changeGroup($userId, $groupId)
    {
        $repo = new \App\Repositories\UserManagement\PenggunaRepository();
        $repo->syncGroup((int) $userId, (int) $groupId);
    }

    public function chnagePermission($userId, $permissionId)
    {
        $repo = new \App\Repositories\UserManagement\PenggunaRepository();
        $repo->syncPermission((int) $userId, (int) $permissionId);
    }
}

<?php

namespace App\Controllers;

use App\Services\UserManagement\KaryawanService;

/**
 * Backward compatibility shim.
 * Canonical implementation located in:
 * - Web: App\Controllers\Web\UserManagement\KaryawanController
 * - Api: App\Controllers\Api\UserManagement\KaryawanController
 * - Service: App\Services\UserManagement\KaryawanService
 */
class Karyawan extends BaseController
{
    protected KaryawanService $karyawanService;

    public function __construct()
    {
        $this->karyawanService = new KaryawanService();
    }

    public function index()
    {
        $data['content'] = 'master/karyawan';
        $data['data']    = $this->karyawanService->getIndexData();

        return view('template', $data);
    }

    public function getAll()
    {
        return $this->response->setJSON($this->karyawanService->getDatatable());
    }

    public function getOne()
    {
        $nik = trim((string) $this->request->getPost('nik'));
        if ($nik === '') {
            throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }

        $karyawan = $this->karyawanService->getOne($nik);
        if (!$karyawan) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }

        return $this->response->setJSON($karyawan);
    }

    public function add()
    {
        $result = $this->karyawanService->addKaryawan((array) $this->request->getPost());
        return $this->response->setJSON($result);
    }

    public function edit()
    {
        $result = $this->karyawanService->editKaryawan((array) $this->request->getPost());
        return $this->response->setJSON($result);
    }

    public function remove()
    {
        $nik    = trim((string) $this->request->getPost('nik'));
        $status = (int) $this->request->getPost('status');

        $result = $this->karyawanService->toggleStatus($nik, $status);
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

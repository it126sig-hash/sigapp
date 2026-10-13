<?php

namespace App\Controllers\Api\UserManagement;

use App\Controllers\Api\BaseApiController;
use App\Services\UserManagement\KaryawanService;

class KaryawanController extends BaseApiController
{
    protected KaryawanService $karyawanService;

    public function __construct()
    {
        $this->karyawanService = new KaryawanService();
    }

    public function getAll()
    {
        return $this->respond($this->karyawanService->getDatatable());
    }

    public function getOne()
    {
        $nik = trim((string) $this->request->getPost('nik'));
        if ($nik === '') {
            return $this->respond([
                'success'  => false,
                'messages' => 'NIK tidak valid',
                'token'    => csrf_hash(),
            ], 400);
        }

        $karyawan = $this->karyawanService->getOne($nik);
        if (!$karyawan) {
            return $this->respond([
                'success'  => false,
                'messages' => 'Data karyawan tidak ditemukan',
                'token'    => csrf_hash(),
            ], 404);
        }

        return $this->respond($karyawan);
    }

    public function add()
    {
        $result = $this->karyawanService->addKaryawan((array) $this->request->getPost());
        return $this->respond($result);
    }

    public function edit($id = null)
    {
        $result = $this->karyawanService->editKaryawan((array) $this->request->getPost());
        return $this->respond($result);
    }

    public function remove()
    {
        $nik    = trim((string) $this->request->getPost('nik'));
        $status = (int) $this->request->getPost('status');

        $result = $this->karyawanService->toggleStatus($nik, $status);
        return $this->respond($result);
    }
}

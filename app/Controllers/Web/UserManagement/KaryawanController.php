<?php

namespace App\Controllers\Web\UserManagement;

use App\Controllers\BaseController;
use App\Services\UserManagement\KaryawanService;

class KaryawanController extends BaseController
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
}

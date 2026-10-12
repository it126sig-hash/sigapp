<?php

namespace App\Controllers\Web\UserManagement;

use App\Controllers\BaseController;
use App\Services\UserManagement\PenggunaService;

class PenggunaController extends BaseController
{
    protected PenggunaService $penggunaService;

    public function __construct()
    {
        $this->penggunaService = new PenggunaService();
    }

    public function index()
    {
        $data['content'] = 'user/setuser';
        $data['data']    = $this->penggunaService->getSetuserData();

        return view('template', $data);
    }
}

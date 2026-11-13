<?php

namespace App\Controllers\Web\Keuangan;

use App\Controllers\BaseController;
use App\Services\Keuangan\BankService;

class BankController extends BaseController
{
    protected BankService $bankService;

    public function __construct()
    {
        $this->bankService = new BankService();
    }

    public function index()
    {
        $data = [
            'title' => 'List Bank',
            'banks' => $this->bankService->getList(),
            'content' => 'master/listbank',
        ];

        return view('template', $data);
    }
}

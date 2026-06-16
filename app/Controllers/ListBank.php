<?php

namespace App\Controllers;

use App\Models\ListBankModel;

class ListBank extends BaseController
{
    protected $listBankModel;

    public function __construct()
    {
        $this->listBankModel = new ListBankModel();
    }

    public function index()
    {
        $data = [
            'title' => 'List Bank',
            'banks' => $this->listBankModel->getAllBanks()
        ];

        $data['content'] = 'master/listbank';
        return view('template', $data);
    }
}

<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Models\ProyekModel;

class SalesExternalController extends BaseController
{
    public function index()
    {
        $proyekModel = new ProyekModel();
        $data['content'] = 'sales/master_sales';
        $data['data'] = [
            'controller' => 'SalesExternal',
            'title'      => 'Master Sales External',
            'proyekList' => $proyekModel->findAll(),
        ];

        return view('template', $data);
    }

    public function feeSales()
    {
        $proyekModel = new ProyekModel();
        $data['content'] = 'sales/fee_sales';
        $data['data'] = [
            'controller' => 'SalesExternal',
            'title'      => 'Fee Sales External',
            'proyekList' => $proyekModel->findAll(),
        ];

        return view('template', $data);
    }
}

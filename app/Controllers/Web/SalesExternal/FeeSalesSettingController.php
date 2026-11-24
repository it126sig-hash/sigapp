<?php

namespace App\Controllers\Web\SalesExternal;

use App\Controllers\BaseController;

class FeeSalesSettingController extends BaseController
{
    public function index()
    {
        $data = [
            'content' => 'setting/fee_sales_access',
            'data'    => [
                'title'      => 'Setting Akses Fee Sales',
                'controller' => 'setting-fee-sales',
            ],
        ];

        return view('template', $data);
    }
}

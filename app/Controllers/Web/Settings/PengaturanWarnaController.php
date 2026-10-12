<?php

namespace App\Controllers\Web\Settings;

use App\Controllers\BaseController;

class PengaturanWarnaController extends BaseController
{
    public function index()
    {
        $data = [
            'controller' => 'pengaturanWarna',
            'title'      => 'Pengaturan Warna',
            'content'    => 'master/pengaturanWarna',
        ];

        return view('template', $data);
    }
}

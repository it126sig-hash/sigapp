<?php

namespace App\Controllers\Web\Proyek;

use App\Controllers\BaseController;

class ProyekController extends BaseController
{
    public function index()
    {
        $data['content']            = 'master/proyek';
        $data['data']['controller'] = 'proyek';
        $data['data']['title']      = 'proyek';

        return view('template', $data);
    }
}

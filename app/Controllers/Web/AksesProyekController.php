<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;

class AksesProyekController extends BaseController
{
    public function index()
    {
        $data = [
            'controller' => 'AksesProyek',
            'title'      => 'Akses Proyek',
            'content'    => 'master/aksesproyek',
        ];

        return view('template', $data);
    }
}

<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;

class TiketMasalahController extends BaseController
{
    public function index()
    {
        return view('template', [
            'content' => 'tiket_masalah/index',
            'data' => [
                'title' => 'Tiket Masalah',
            ]
        ]);
    }
}

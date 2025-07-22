<?php

namespace App\Controllers;

class Proyek extends BaseController
{
    public function index()
    {
        $data['content'] = 'proyek/proyek';
        return view('template',$data);
    }
}

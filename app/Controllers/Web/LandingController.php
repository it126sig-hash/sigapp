<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;

class LandingController extends BaseController
{
    public function index(): string
    {
        return view('public/landing', [
            'pageTitle' => 'SIGAPP - Sistem Internal Sanggar Indah Group'
        ]);
    }
}

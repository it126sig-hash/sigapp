<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Modal extends BaseController
{
    public function index()
    {
        $data['url'] = $this->request->getGet('url');
        $path = 'siteplan/modal/' . $data['url'] . '.php';
        if (!file_exists(APPPATH . 'Views/' . $path)) {
            return view('errors/html/error_404');
        }
        return view($path);
    }
}
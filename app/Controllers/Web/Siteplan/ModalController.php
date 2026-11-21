<?php

namespace App\Controllers\Web\Siteplan;

use App\Controllers\BaseController;

class ModalController extends BaseController
{
    public function index()
    {
        $url  = trim((string) $this->request->getGet('url'));
        $path = 'siteplan/modal/' . $url . '.php';

        if ($url === '' || str_contains($url, '..') || ! is_file(APPPATH . 'Views/' . $path)) {
            return view('errors/html/error_404');
        }

        return view($path);
    }
}

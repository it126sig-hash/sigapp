<?php

namespace App\Controllers\Web\Legal;

use App\Controllers\BaseController;

class LegalController extends BaseController
{
    public function index()
    {
        return $this->list_legalitas();
    }

    public function list_legalitas()
    {
        $data['content']            = 'legal/list-legalitas';
        $data['data']['controller'] = 'Legal';
        $data['data']['title']      = 'List Legalitas';

        return view('template', $data);
    }
}

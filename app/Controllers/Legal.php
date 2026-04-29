<?php

namespace App\Controllers;

class Legal extends BaseController
{
    public function list_legalitas()
    {
        $data['content'] = 'legal/list-legalitas';
        $data['data']['controller'] = 'Legal';
        $data['data']['title'] = 'List Legalitas';

        return view('template', $data);
    }
}

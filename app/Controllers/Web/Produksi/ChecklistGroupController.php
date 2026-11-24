<?php

namespace App\Controllers\Web\Produksi;

use App\Controllers\BaseController;

class ChecklistGroupController extends BaseController
{
    public function index()
    {
        $data['content']            = 'checklist/checklistGroup';
        $data['data']['controller'] = 'checklistGroup';
        $data['data']['title']      = 'Checklist Ruang';

        return view('template', $data);
    }
}

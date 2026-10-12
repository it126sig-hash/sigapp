<?php

namespace App\Controllers\Web\Proyek;

use App\Controllers\BaseController;
use App\Models\ProyekModel;

class TipeController extends BaseController
{
    protected ProyekModel $proyekModel;

    public function __construct()
    {
        $this->proyekModel = new ProyekModel();
    }

    public function index()
    {
        $data['data']['proyek'] = $this->proyekModel
            ->select('id_proyek, nama_proyek')
            ->findAll();

        $data['content']            = 'master/tipe';
        $data['data']['controller'] = 'tipe';
        $data['data']['title']      = 'Tipe';

        return view('template', $data);
    }
}

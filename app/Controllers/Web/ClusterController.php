<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Models\ProyekModel;

class ClusterController extends BaseController
{
    protected ProyekModel $proyekModel;

    public function __construct()
    {
        $this->proyekModel = new ProyekModel();
    }

    public function index()
    {
        $data['data']['proyek']     = $this->proyekModel->select('id_proyek, nama_proyek')->findAll();
        $data['content']            = 'master/cluster';
        $data['data']['controller'] = 'cluster';
        $data['data']['title']      = 'Cluster';

        return view('template', $data);
    }
}

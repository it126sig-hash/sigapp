<?php

namespace App\Controllers;

use App\Repositories\KavlingRepository;

use App\Controllers\Notif;

class Kavling extends BaseController
{
    protected $db;
    protected $kavlingRepo;

    public function __construct()
    {
        $this->kavlingRepo = new KavlingRepository();
        $this->db = db_connect();
    }

    public function getList()
    {
        $id_proyek = $this->request->getPost('id_proyek') ?? "";
        $search = $this->request->getPost('search') ?? "";
        $limit = $this->request->getPost('limit') ?? null;
        $is_cashout_subkon = $this->request->getPost('is_cashout_subkon') ?? 0;
        $id_cluster = $this->request->getPost('id_cluster') ?? null;
        $id_jalan = $this->request->getPost('id_jalan') ?? null;

        $kavling = $this->kavlingRepo->getKavlingList($id_proyek, $search, $limit, $is_cashout_subkon, $id_cluster, $id_jalan);
        return $this->response->setJSON($kavling);
    }
}

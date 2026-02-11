<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use App\Models\SubkonModel;


class Subkon extends BaseController
{
    protected $db;
    protected $modelSubkon;

    use ResponseTrait;

    public function __construct()
    {
        $this->modelSubkon = new SubkonModel();

        $this->db = db_connect();
    }

    function getList()
    {
        $search = trim((string) $this->request->getVar('search'));

        $data = $this->modelSubkon->like('nama_subkon', $search)->findAll();
        return $this->response->setJSON($data);
    }
}

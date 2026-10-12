<?php

namespace App\Controllers\Api\Proyek;

use App\Controllers\Api\BaseApiController;
use App\Models\KavlingModel;
use App\Services\Proyek\TipeService;
use CodeIgniter\Exceptions\PageNotFoundException;

class TipeController extends BaseApiController
{
    protected TipeService $tipeService;

    public function __construct()
    {
        $this->tipeService = new TipeService();
    }

    public function getAll()
    {
        $params              = (array) $this->request->getVar();
        $params['id_proyek'] = resolve_active_proyek_id($params['id_proyek'] ?? null);

        return $this->respond([
            'data'  => $this->tipeService->getAll($params),
            'token' => csrf_hash(),
        ]);
    }

    public function getDataTables()
    {
        $params              = (array) $this->request->getVar();
        $params['id_proyek'] = resolve_active_proyek_id($params['id_proyek'] ?? null);

        $result = $this->tipeService->getDataTables($params);
        $result['token'] = csrf_hash();

        return $this->respond($result);
    }

    public function getOne()
    {
        $id = (int) $this->request->getPost('id_tipe');

        if ($id <= 0) {
            throw new PageNotFoundException();
        }

        $data = $this->tipeService->getOne($id);
        if (!$data) {
            throw new PageNotFoundException();
        }

        $data->token = csrf_hash();

        return $this->respond($data);
    }

    public function add()
    {
        $result = $this->tipeService->add($this->request);
        $result['token'] = csrf_hash();

        return $this->respond($result);
    }

    public function edit($id = null)
    {
        $result = $this->tipeService->edit($this->request);
        $result['token'] = csrf_hash();

        return $this->respond($result);
    }

    public function remove()
    {
        $id = (int) $this->request->getPost('id_tipe');
        $result = $this->tipeService->remove($id);
        $result['token'] = csrf_hash();

        return $this->respond($result);
    }

    public function getListKavling()
    {
        $id           = (int) $this->request->getPost('id_tipe');
        $kavlingModel = new KavlingModel();
        $list         = $kavlingModel->select('kavling.id_kavling, kavling.no_kavling, kavling.status_kavling, jalan.nama_jalan')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan', 'left')
            ->where('kavling.id_tipe', $id)
            ->findAll();

        return $this->respond([
            'success' => true,
            'data'    => $list,
            'token'   => csrf_hash(),
        ]);
    }
}

<?php

namespace App\Controllers\Api\Proyek;

use App\Controllers\Api\BaseApiController;
use App\Services\Proyek\JalanService;
use CodeIgniter\Exceptions\PageNotFoundException;

class JalanController extends BaseApiController
{
    protected JalanService $jalanService;

    public function __construct()
    {
        $this->jalanService = new JalanService();
    }

    public function getAll()
    {
        $params              = (array) $this->request->getVar();
        $params['id_proyek'] = resolve_active_proyek_id($params['id_proyek'] ?? null);

        return $this->respond([
            'data'  => $this->jalanService->getAll($params),
            'token' => csrf_hash(),
        ]);
    }

    public function getDatatable()
    {
        $params              = (array) $this->request->getVar();
        $params['id_proyek'] = resolve_active_proyek_id($params['id_proyek'] ?? null);

        $result = $this->jalanService->getDataTables($params);
        $result['token'] = csrf_hash();

        return $this->respond($result);
    }

    public function getOne()
    {
        $id = (int) $this->request->getPost('id_jalan');

        if ($id <= 0) {
            throw new PageNotFoundException();
        }

        $data = $this->jalanService->getOne($id);
        if (!$data) {
            throw new PageNotFoundException();
        }

        $data->token = csrf_hash();

        return $this->respond($data);
    }

    public function add()
    {
        $result = $this->jalanService->add($this->request);
        $result['token'] = csrf_hash();

        return $this->respond($result);
    }

    public function edit($id = null)
    {
        $result = $this->jalanService->edit($this->request);
        $result['token'] = csrf_hash();

        return $this->respond($result);
    }

    public function remove()
    {
        $id = (int) $this->request->getPost('id_jalan');

        if ($id <= 0) {
            throw new PageNotFoundException();
        }

        $result = $this->jalanService->remove($id);
        $result['token'] = csrf_hash();

        return $this->respond($result);
    }
}

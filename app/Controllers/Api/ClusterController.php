<?php

namespace App\Controllers\Api;

use App\Services\ClusterService;
use CodeIgniter\Exceptions\PageNotFoundException;

class ClusterController extends BaseApiController
{
    protected ClusterService $clusterService;

    public function __construct()
    {
        $this->clusterService = new ClusterService();
    }

    public function getDataTable()
    {
        $result = $this->clusterService->getDataTables((array) $this->request->getVar());
        $result['token'] = csrf_hash();

        return $this->respond($result);
    }

    public function getAll()
    {
        $result = $this->clusterService->getAll((array) $this->request->getVar());

        return $this->respond([
            'data'  => $result,
            'token' => csrf_hash(),
        ]);
    }

    public function getOne()
    {
        $id = (int) $this->request->getPost('id_cluster');

        if ($id <= 0) {
            throw new PageNotFoundException();
        }

        $data = $this->clusterService->getOne($id);
        if (!$data) {
            throw new PageNotFoundException();
        }

        $data->token = csrf_hash();

        return $this->respond($data);
    }

    public function add()
    {
        $result = $this->clusterService->add((array) $this->request->getPost());
        $result['token'] = csrf_hash();

        return $this->respond($result);
    }

    public function edit($id = null)
    {
        $postData = (array) $this->request->getPost();
        if ($id !== null && empty($postData['idCluster'])) {
            $postData['idCluster'] = $id;
        }
        $result = $this->clusterService->edit($postData);
        $result['token'] = csrf_hash();

        return $this->respond($result);
    }

    public function remove()
    {
        $id = (int) $this->request->getPost('id_cluster');
        if ($id <= 0) {
            throw new PageNotFoundException();
        }

        $result = $this->clusterService->remove($id);
        $result['token'] = csrf_hash();

        return $this->respond($result);
    }
}

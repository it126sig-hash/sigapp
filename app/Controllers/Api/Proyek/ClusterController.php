<?php

namespace App\Controllers\Api\Proyek;

use App\Controllers\Api\BaseApiController;
use App\Services\Proyek\ClusterService;

class ClusterController extends BaseApiController
{
    protected ClusterService $clusterService;

    public function __construct()
    {
        $this->clusterService = new ClusterService();
    }

    public function getDataTable()
    {
        $params = (array) $this->request->getVar();
        $params['format'] = $params['format'] ?? 'raw';
        $result = $this->clusterService->getDataTables($params);

        return $this->success($result, 'Data cluster berhasil dimuat');
    }

    public function getAll()
    {
        $params = (array) $this->request->getVar();
        $params['format'] = $params['format'] ?? 'raw';
        $result = $this->clusterService->getAll($params);

        return $this->success($result, 'Data cluster berhasil dimuat');
    }

    public function getOne($id = null)
    {
        $id = (int) ($id ?: ($this->request->getVar('id_cluster') ?: $this->request->getVar('id')));

        if ($id <= 0) {
            return $this->error('ID cluster tidak valid', 400);
        }

        $data = $this->clusterService->getOne($id);
        if (!$data) {
            return $this->error('Cluster tidak ditemukan', 404);
        }

        return $this->success($data);
    }

    public function add()
    {
        $result = $this->clusterService->add((array) $this->request->getPost());

        if (!($result['success'] ?? false)) {
            return $this->error($result['messages'] ?? 'Gagal menambahkan cluster', 400);
        }

        return $this->successCreated($result, $result['messages'] ?? 'Cluster berhasil ditambahkan');
    }

    public function edit($id = null)
    {
        $postData = (array) $this->request->getPost();
        if ($id !== null && empty($postData['idCluster'])) {
            $postData['idCluster'] = $id;
        }
        $result = $this->clusterService->edit($postData);

        if (!($result['success'] ?? false)) {
            return $this->error($result['messages'] ?? 'Gagal memperbarui cluster', 400);
        }

        return $this->success($result, $result['messages'] ?? 'Cluster berhasil diperbarui');
    }

    public function remove()
    {
        $id = (int) $this->request->getPost('id_cluster');
        if ($id <= 0) {
            return $this->error('ID cluster tidak valid', 400);
        }

        $result = $this->clusterService->remove($id);

        if (!($result['success'] ?? false)) {
            return $this->error($result['messages'] ?? 'Gagal menghapus cluster', 400);
        }

        return $this->success($result, $result['messages'] ?? 'Cluster berhasil dihapus');
    }
}

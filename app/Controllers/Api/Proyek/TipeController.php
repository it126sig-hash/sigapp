<?php

namespace App\Controllers\Api\Proyek;

use App\Controllers\Api\BaseApiController;
use App\Services\Proyek\TipeService;

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
        $params['format']    = $params['format'] ?? 'raw';

        return $this->success($this->tipeService->getAll($params), 'Data tipe berhasil dimuat');
    }

    public function getDataTables()
    {
        $params              = (array) $this->request->getVar();
        $params['id_proyek'] = resolve_active_proyek_id($params['id_proyek'] ?? null);
        $params['format']    = $params['format'] ?? 'raw';

        $result = $this->tipeService->getDataTables($params);

        return $this->success($result, 'Data tipe datatables berhasil dimuat');
    }

    public function getOne($id = null)
    {
        $id = (int) ($id ?: ($this->request->getVar('id_tipe') ?: $this->request->getVar('id')));

        if ($id <= 0) {
            return $this->error('ID tipe tidak valid', 400);
        }

        $data = $this->tipeService->getOne($id);
        if (!$data) {
            return $this->error('Tipe tidak ditemukan', 404);
        }

        return $this->success($data);
    }

    protected function getRequestData(): array
    {
        $json = $this->request->getJSON(true);
        if (is_array($json) && !empty($json)) {
            return $json;
        }

        return (array) $this->request->getPost();
    }

    public function add()
    {
        $result = $this->tipeService->add($this->request);

        if (!($result['success'] ?? false)) {
            return $this->error($result['messages'] ?? 'Gagal menambahkan tipe', 400);
        }

        return $this->successCreated($result, $result['messages'] ?? 'Tipe berhasil ditambahkan');
    }

    public function edit($id = null)
    {
        if ($id !== null && !$this->request->getPost('idTipe') && !$this->request->getPost('id_tipe')) {
            $_POST['id_tipe'] = (int) $id;
            $_POST['idTipe'] = (int) $id;
        }

        $result = $this->tipeService->edit($this->request);

        if (!($result['success'] ?? false)) {
            return $this->error($result['messages'] ?? 'Gagal memperbarui tipe', 400);
        }

        return $this->success($result, $result['messages'] ?? 'Tipe berhasil diperbarui');
    }

    public function remove($id = null)
    {
        $payload = $this->getRequestData();
        $id = (int) ($id ?: ($payload['id_tipe'] ?? ($payload['id'] ?? ($this->request->getPost('id_tipe') ?: $this->request->getVar('id_tipe')))));
        if ($id <= 0) {
            return $this->error('ID tipe tidak valid', 400);
        }

        $result = $this->tipeService->remove($id);

        if (!($result['success'] ?? false)) {
            return $this->error($result['messages'] ?? 'Gagal menghapus tipe', 400);
        }

        return $this->success($result, $result['messages'] ?? 'Tipe berhasil dihapus');
    }

    public function getListKavling($id = null)
    {
        $id = (int) ($id ?: ($this->request->getVar('id_tipe') ?: $this->request->getVar('id')));
        if ($id <= 0) {
            return $this->error('ID tipe tidak valid', 400);
        }

        $list = $this->tipeService->getListKavling($id);

        return $this->success($list, 'Daftar kavling berhasil dimuat');
    }
}

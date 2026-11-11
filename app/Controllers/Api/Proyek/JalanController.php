<?php

namespace App\Controllers\Api\Proyek;

use App\Controllers\Api\BaseApiController;
use App\Services\Proyek\JalanService;

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
        $params['format']    = $params['format'] ?? 'raw';

        return $this->success($this->jalanService->getAll($params), 'Data jalan berhasil dimuat');
    }

    public function getDatatable()
    {
        $params              = (array) $this->request->getVar();
        $params['id_proyek'] = resolve_active_proyek_id($params['id_proyek'] ?? null);
        $params['format']    = $params['format'] ?? 'raw';

        $result = $this->jalanService->getDataTables($params);

        return $this->success($result, 'Data jalan datatables berhasil dimuat');
    }

    public function getOne($id = null)
    {
        $id = (int) ($id ?: ($this->request->getVar('id_jalan') ?: $this->request->getVar('id')));

        if ($id <= 0) {
            return $this->error('ID jalan tidak valid', 400);
        }

        $data = $this->jalanService->getOne($id);
        if (!$data) {
            return $this->error('Jalan tidak ditemukan', 404);
        }

        return $this->success($data);
    }

    public function add()
    {
        $result = $this->jalanService->add($this->request);

        if (!($result['success'] ?? false)) {
            return $this->error($result['messages'] ?? 'Gagal menambahkan jalan', 400);
        }

        return $this->successCreated($result, $result['messages'] ?? 'Jalan berhasil ditambahkan');
    }

    public function edit($id = null)
    {
        $result = $this->jalanService->edit($this->request);

        if (!($result['success'] ?? false)) {
            return $this->error($result['messages'] ?? 'Gagal memperbarui jalan', 400);
        }

        return $this->success($result, $result['messages'] ?? 'Jalan berhasil diperbarui');
    }

    public function remove()
    {
        $id = (int) $this->request->getPost('id_jalan');

        if ($id <= 0) {
            return $this->error('ID jalan tidak valid', 400);
        }

        $result = $this->jalanService->remove($id);

        if (!($result['success'] ?? false)) {
            return $this->error($result['messages'] ?? 'Gagal menghapus jalan', 400);
        }

        return $this->success($result, $result['messages'] ?? 'Jalan berhasil dihapus');
    }
}

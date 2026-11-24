<?php

namespace App\Controllers\Api\TiketMasalah;

use App\Controllers\Api\BaseApiController;
use App\Services\TiketMasalah\TiketMasalahService;

class TiketMasalahController extends BaseApiController
{
    protected $helpers = ['auth', 'active_proyek'];

    protected $service;

    public function __construct()
    {
        helper(['auth', 'active_proyek']);
        $this->service = new TiketMasalahService();
    }

    public function list()
    {
        $refType = $this->request->getPost('ref_type');
        $refId = (int) $this->request->getPost('ref_id');

        if (!$refType || !$refId) {
            return $this->fail('Parameter tidak lengkap', 400);
        }

        $list = $this->service->getListByRef($refType, $refId);
        return $this->respond(['success' => true, 'data' => $list]);
    }

    public function detail()
    {
        $idTiket = (int) $this->request->getPost('id_tiket_masalah');
        if (!$idTiket) {
            return $this->fail('ID Tiket diperlukan', 400);
        }

        $detail = $this->service->getDetail($idTiket);
        if (!$detail) {
            return $this->failNotFound('Tiket tidak ditemukan');
        }

        return $this->respond(['success' => true, 'data' => $detail]);
    }

    public function progress()
    {
        $idTiket = (int) $this->request->getPost('id_tiket_masalah');
        $limit = (int) $this->request->getPost('limit') ?: 10;
        $offset = (int) $this->request->getPost('offset') ?: 0;

        if (!$idTiket) {
            return $this->fail('ID Tiket diperlukan', 400);
        }

        $progress = $this->service->getProgress($idTiket, $limit, $offset);
        return $this->respond(['success' => true, 'data' => $progress]);
    }

    public function store()
    {
        $rules = [
            'ref_type' => 'required|in_list[kavling,others]',
            'ref_id' => 'required|numeric',
            'id_proyek' => 'required|numeric',
            'tanggal_masalah' => 'required|valid_date[Y-m-d]',
            'tanggal_kunjungan' => 'permit_empty|valid_date[Y-m-d]',
            'keterangan' => 'required',
            'prioritas' => 'required|in_list[urgent,medium,normal,low,laporan]',
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $data = $this->request->getPost();
        $files = $this->request->getFiles();

        $result = $this->service->createTiket($data, $files['foto'] ?? []);

        if ($result['success']) {
            return $this->respondCreated(['success' => true, 'message' => 'Tiket berhasil dibuat', 'id' => $result['id']]);
        }
        return $this->fail('Gagal membuat tiket');
    }

    public function update($id = null)
    {
        $rules = [
            'id_tiket_masalah' => 'required|numeric',
            'ref_type' => 'required|in_list[kavling,others]',
            'ref_id' => 'required|numeric',
            'id_proyek' => 'required|numeric',
            'tanggal_masalah' => 'required|valid_date[Y-m-d]',
            'tanggal_kunjungan' => 'permit_empty|valid_date[Y-m-d]',
            'keterangan' => 'required',
            'prioritas' => 'required|in_list[urgent,medium,normal,low,laporan]',
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $idTiket = (int) ($this->request->getPost('id_tiket_masalah') ?? $id ?? 0);
        $data = $this->request->getPost();
        $files = $this->request->getFiles();

        $result = $this->service->updateTiket($idTiket, $data, $files['foto'] ?? []);

        if ($result['success']) {
            return $this->respond(['success' => true, 'message' => 'Tiket berhasil diperbarui', 'id' => $idTiket]);
        }
        return $this->fail($result['message'] ?? 'Gagal memperbarui tiket');
    }

    public function addProgress()
    {
        $rules = [
            'id_tiket_masalah' => 'required|numeric',
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $idTiket = (int) $this->request->getPost('id_tiket_masalah');
        $data = $this->request->getPost();
        $files = $this->request->getFiles();

        $result = $this->service->addProgress($idTiket, $data, $files['foto'] ?? []);

        if ($result['success']) {
            return $this->respond(['success' => true, 'message' => 'Progress berhasil ditambahkan']);
        }
        return $this->fail($result['message'] ?? 'Gagal menambahkan progress');
    }

    public function togglePin()
    {
        $rules = [
            'id_progress' => 'required|numeric',
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $idProgress = (int) $this->request->getPost('id_progress');
        $result = $this->service->togglePinProgress($idProgress);

        if ($result['success']) {
            return $this->respond([
                'success' => true,
                'message' => $result['is_pinned'] ? 'Progress berhasil dipin' : 'Progress berhasil di-unpin',
                'is_pinned' => $result['is_pinned']
            ]);
        }
        return $this->fail($result['message'] ?? 'Gagal mengubah pin progress');
    }

    public function refInfo()
    {
        $refType = $this->request->getPost('ref_type');
        $refId = (int) $this->request->getPost('ref_id');

        if (!$refType || !$refId) {
            return $this->fail('Parameter tidak lengkap', 400);
        }

        $info = $this->service->getRefInfo($refType, $refId);
        if (!$info) {
            return $this->failNotFound('Data tidak ditemukan');
        }

        return $this->respond(['success' => true, 'data' => $info]);
    }

    public function users()
    {
        $creatorsOnly = (bool) $this->request->getGet('creators_only');
        $users = $creatorsOnly ? $this->service->getCreatorList() : $this->service->getUserList();
        return $this->respond(['success' => true, 'data' => $users]);
    }

    public function creators()
    {
        $creators = $this->service->getCreatorList();
        return $this->respond(['success' => true, 'data' => $creators]);
    }

    public function divisions()
    {
        $divisions = $this->service->getDivisionList();
        return $this->respond(['success' => true, 'data' => $divisions]);
    }

    public function datatable()
    {
        $params = $this->request->getPost();
        $result = $this->service->getDatatableData($params);
        return $this->respond($result);
    }

    public function createOthersArea()
    {
        $params = $this->request->getPost();
        $result = $this->service->createOthersArea($params, (int) user_id());

        if (!$result['success']) {
            return $this->fail($result['message'], $result['code'] ?? 400);
        }

        return $this->respond(['success' => true, 'data' => ['id' => $result['id']]]);
    }
}

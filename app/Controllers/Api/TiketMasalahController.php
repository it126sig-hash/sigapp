<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\TiketMasalahService;
use CodeIgniter\API\ResponseTrait;

class TiketMasalahController extends BaseController
{
    use ResponseTrait;

    protected $service;

    public function __construct()
    {
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
            'keterangan' => 'required',
            'prioritas' => 'required|in_list[urgent,medium,normal,low]',
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
        $users = $this->service->getUserList();
        return $this->respond(['success' => true, 'data' => $users]);
    }
}

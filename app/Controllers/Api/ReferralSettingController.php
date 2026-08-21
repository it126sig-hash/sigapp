<?php

namespace App\Controllers\Api;

use CodeIgniter\HTTP\ResponseInterface;
use App\Models\ReferralBonusStageModel;

class ReferralSettingController extends BaseApiController
{
    protected $model;

    public function __construct()
    {
        $this->model = new ReferralBonusStageModel();
    }

    public function listStages(): ResponseInterface
    {
        $idProyek = $this->request->getPost('id_proyek') ?: active_proyek_id();
        $stages = $this->model->where('id_proyek', $idProyek)
                              ->orderBy('urutan', 'asc')
                              ->findAll();
                              
        return $this->success($stages);
    }

    public function saveStage(): ResponseInterface
    {
        $idProyek = $this->request->getPost('id_proyek') ?: active_proyek_id();
        $id = $this->request->getPost('id');
        
        $data = [
            'id_proyek' => $idProyek,
            'nama_tahapan' => $this->request->getPost('nama_tahapan'),
            'trigger_status_mkdt' => $this->request->getPost('trigger_status_mkdt'),
            'nominal_default' => str_replace(',', '', $this->request->getPost('nominal_default')),
            'urutan' => $this->request->getPost('urutan'),
            'is_active' => $this->request->getPost('is_active')
        ];

        if ($id) {
            $this->model->update($id, $data);
        } else {
            $this->model->insert($data);
        }

        return $this->success(['message' => 'Tahapan berhasil disimpan']);
    }

    public function deleteStage(): ResponseInterface
    {
        $id = $this->request->getPost('id');
        $this->model->delete($id);
        return $this->success(['message' => 'Tahapan berhasil dihapus']);
    }
}

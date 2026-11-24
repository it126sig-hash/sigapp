<?php

namespace App\Controllers\Api\MGM;

use App\Controllers\Api\BaseApiController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Services\MGM\ReferralService;

class ReferralSettingController extends BaseApiController
{
    protected ReferralService $service;

    public function __construct()
    {
        $this->service = new ReferralService();
    }

    public function listStages(): ResponseInterface
    {
        $idProyek = (int) ($this->request->getPost('id_proyek') ?: active_proyek_id());
        $stages   = $this->service->getStages($idProyek);

        return $this->success($stages);
    }

    public function saveStage(): ResponseInterface
    {
        $idProyek = (int) ($this->request->getPost('id_proyek') ?: active_proyek_id());
        $id       = $this->request->getPost('id') ? (int) $this->request->getPost('id') : null;

        $payload = [
            'id_proyek'           => $idProyek,
            'nama_tahapan'        => $this->request->getPost('nama_tahapan'),
            'trigger_status_mkdt' => $this->request->getPost('trigger_status_mkdt'),
            'nominal_default'     => $this->request->getPost('nominal_default'),
            'urutan'              => $this->request->getPost('urutan'),
            'is_active'           => $this->request->getPost('is_active'),
        ];

        $res = $this->service->saveStage($payload, $id);
        if (!$res['success']) {
            return $this->failValidationErrors($res['message']);
        }

        return $this->success(['message' => $res['message']]);
    }

    public function deleteStage(): ResponseInterface
    {
        $id  = (int) $this->request->getPost('id');
        $res = $this->service->deleteStage($id);
        if (!$res['success']) {
            return $this->failValidationErrors($res['message']);
        }

        return $this->success(['message' => $res['message']]);
    }
}

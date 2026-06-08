<?php

namespace App\Controllers\Api;

use App\Services\TargetSiteplanService;

class TargetController extends BaseApiController
{
    protected $targetService;
    protected $validation;

    public function __construct()
    {
        $this->targetService = new TargetSiteplanService();
        $this->validation = \Config\Services::validation();
    }

    public function list()
    {
        $idProyek = (int) $this->request->getVar('id_proyek');
        if ($idProyek <= 0) {
            return $this->error('Proyek harus diisi');
        }

        return $this->success($this->targetService->listByProject($idProyek));
    }

    public function detail()
    {
        $idTarget = (int) $this->request->getVar('id_target');
        if ($idTarget <= 0) {
            return $this->error('Target harus diisi');
        }

        try {
            return $this->success($this->targetService->get($idTarget));
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function save()
    {
        $this->validation->setRules([
            'id_proyek' => [
                'label' => 'Proyek',
                'rules' => 'required|integer',
            ],
            'tahun_target' => [
                'label' => 'Tahun Target',
                'rules' => 'required|integer|greater_than_equal_to[2000]|less_than_equal_to[2100]',
            ],
            'deskripsi' => [
                'label' => 'Deskripsi',
                'rules' => 'permit_empty',
            ],
        ]);

        if ($this->validation->run($this->request->getPost()) == false) {
            return $this->error($this->validation->listErrors());
        }

        try {
            $result = $this->targetService->save($this->request->getPost());
            return $this->success($result, 'Target berhasil disimpan');
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function history()
    {
        $idTarget = (int) $this->request->getVar('id_target');
        if ($idTarget <= 0) {
            return $this->error('Target harus diisi');
        }

        return $this->success($this->targetService->history($idTarget));
    }
}

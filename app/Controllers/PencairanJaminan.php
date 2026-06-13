<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\RiwayatPencairanJaminanModel;
use App\Services\DanaJaminanService;
use App\Services\FileAccessService;

class PencairanJaminan extends BaseController
{
    protected $model;
    protected $fileAccessService;
    protected $danaJaminanService;
    public function __construct()
    {
        $this->model = new RiwayatPencairanJaminanModel();
        $this->fileAccessService = new FileAccessService();
        $this->danaJaminanService = new DanaJaminanService();
        helper(['form']);
    }

    public function index()
    {
        return view('pencairan_jaminan/index');
    }

    public function list($id_kavling)
    {
        return $this->response->setJSON(
            $this->danaJaminanService->listPengajuan((int) $id_kavling)
        );
    }

    public function store()
    {
        return $this->response->setJSON(
            $this->danaJaminanService->storePengajuan($this->request, user_id())
        );
    }

    public function toggleStatus($id)
    {
        return $this->response->setJSON($this->danaJaminanService->rejectLegacyToggle());
    }

    public function cairkan($id)
    {
        return $this->response->setJSON(
            $this->danaJaminanService->cairkanPengajuan((int) $id, $this->request, user_id())
        );
    }

    public function history($id_kavling)
    {
        return $this->response->setJSON(
            $this->danaJaminanService->getHistory((int) $id_kavling)
        );
    }

    public function download($id)
    {
        $row = $this->model->find($id);
        if (!$row) {
            return $this->response->setBody('Lampiran tidak ditemukan')->setStatusCode(404);
        }

        return redirect()->to(site_url('files/pencairan_jaminan/' . $id . '?download=1'));
    }
}

<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\PencairanAkadService;

class PencairanAkad extends BaseController
{
    protected PencairanAkadService $service;

    public function __construct()
    {
        $this->service = new PencairanAkadService();
    }

    public function get()
    {
        return $this->response->setJSON(
            $this->service->getData(
                (int) $this->request->getVar('id_mkdt'),
                (int) $this->request->getVar('id_kavling')
            )
        );
    }

    public function saveRetensi()
    {
        return $this->response->setJSON(
            $this->service->saveRetensi($this->request->getPost(), (int) user_id())
        );
    }

    public function saveTenor()
    {
        return $this->response->setJSON(
            $this->service->saveTenor($this->request->getPost(), (int) user_id())
        );
    }

    public function storePengajuan()
    {
        return $this->response->setJSON(
            $this->service->storePengajuan($this->request, (int) user_id())
        );
    }

    public function cairkan()
    {
        return $this->response->setJSON(
            $this->service->cairkan($this->request, (int) user_id())
        );
    }

    public function void()
    {
        return $this->response->setJSON(
            $this->service->void(
                (int) $this->request->getPost('id_pengajuan'),
                (string) $this->request->getPost('reason'),
                (int) user_id()
            )
        );
    }

    public function history($id_kavling)
    {
        return $this->response->setJSON(
            $this->service->getHistory((int) $id_kavling)
        );
    }
}

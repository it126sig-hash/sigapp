<?php

namespace App\Controllers;

use App\Models\ProyekModel;
use App\Services\TipeService;
use CodeIgniter\Exceptions\PageNotFoundException;

class Tipe extends BaseController
{
    protected ProyekModel $proyekModel;
    protected TipeService $tipeService;

    public function __construct()
    {
        $this->proyekModel = new ProyekModel();
        $this->tipeService = new TipeService();
    }

    public function index()
    {
        $data['data']['proyek'] = $this->proyekModel
            ->select('id_proyek, nama_proyek')
            ->findAll();

        $data['content'] = 'master/tipe';
        $data['data']['controller'] = 'tipe';
        $data['data']['title'] = 'Tipe';

        return view('template', $data);
    }

    public function getAll()
    {
        return $this->json([
            'data' => $this->tipeService->getAll((array) $this->request->getVar()),
        ]);
    }

    public function getDataTables()
    {
        return $this->json($this->tipeService->getDataTables((array) $this->request->getVar()));
    }

    public function getOne()
    {
        $id = (int) $this->request->getPost('id_tipe');

        if ($id <= 0) {
            throw new PageNotFoundException();
        }

        $data = $this->tipeService->getOne($id);
        if (!$data) {
            throw new PageNotFoundException();
        }

        $data->token = csrf_hash();

        return $this->response->setJSON($data);
    }

    public function add()
    {
        return $this->json($this->tipeService->add($this->request));
    }

    public function edit()
    {
        return $this->json($this->tipeService->edit($this->request));
    }

    public function remove()
    {
        return $this->json($this->tipeService->remove((int) $this->request->getPost('id_tipe')));
    }

    private function json(array $payload)
    {
        $payload['token'] = $payload['token'] ?? csrf_hash();

        return $this->response->setJSON($payload);
    }
}

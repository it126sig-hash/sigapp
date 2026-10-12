<?php

namespace App\Controllers\Api\Proyek;

use App\Controllers\Api\BaseApiController;
use App\Services\Proyek\ProyekService;
use CodeIgniter\Exceptions\PageNotFoundException;

class AksesProyekController extends BaseApiController
{
    protected ProyekService $proyekService;

    public function __construct()
    {
        $this->proyekService = new ProyekService();
    }

    public function getAll()
    {
        $data = $this->proyekService->getAksesList();

        return $this->respond([
            'data'  => $data,
            'token' => csrf_hash(),
        ]);
    }

    public function getOne()
    {
        $id = (int) $this->request->getPost('id_proyek');

        if ($id <= 0) {
            throw new PageNotFoundException();
        }

        $data = $this->proyekService->getAksesOne($id);
        if (!$data) {
            throw new PageNotFoundException();
        }

        $data->token = csrf_hash();

        return $this->respond($data);
    }

    public function getAkses()
    {
        $search = (string) ($this->request->getPost('search') ?? '');
        $data   = $this->proyekService->searchAksesUsers($search);

        return $this->respond([
            'data'  => $data,
            'token' => csrf_hash(),
        ]);
    }

    public function edit($id = null)
    {
        $postData = (array) $this->request->getPost();
        if ($id !== null && empty($postData['id_proyek'])) {
            $postData['id_proyek'] = $id;
        }
        $result = $this->proyekService->updateAkses($postData);
        $result['token'] = csrf_hash();

        return $this->respond($result);
    }
}

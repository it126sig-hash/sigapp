<?php

namespace App\Controllers\Api\Proyek;

use App\Controllers\Api\BaseApiController;
use App\Services\Proyek\ProyekService;

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

        return $this->success($data, 'Data akses proyek berhasil dimuat');
    }

    public function getOne($id = null)
    {
        $id = (int) ($id ?: ($this->request->getVar('id_proyek') ?: $this->request->getVar('id')));

        if ($id <= 0) {
            return $this->error('ID proyek tidak valid', 400);
        }

        $data = $this->proyekService->getAksesOne($id);
        if (!$data) {
            return $this->error('Data akses proyek tidak ditemukan', 404);
        }

        return $this->success($data);
    }

    public function getAkses()
    {
        $search = (string) ($this->request->getPost('search') ?? '');
        $data   = $this->proyekService->searchAksesUsers($search);

        return $this->success($data, 'Pencarian pengguna berhasil dimuat');
    }

    public function edit($id = null)
    {
        $postData = (array) $this->request->getPost();
        if ($id !== null && empty($postData['id_proyek'])) {
            $postData['id_proyek'] = $id;
        }
        $result = $this->proyekService->updateAkses($postData);

        if (!($result['success'] ?? false)) {
            return $this->error($result['messages'] ?? 'Gagal memperbarui akses proyek', 400);
        }

        return $this->success($result, $result['messages'] ?? 'Akses proyek berhasil diperbarui');
    }
}

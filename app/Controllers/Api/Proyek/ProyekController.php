<?php

namespace App\Controllers\Api\Proyek;

use App\Controllers\Api\BaseApiController;
use App\Services\Proyek\ActiveProyekService;
use App\Services\Proyek\ProyekService;

class ProyekController extends BaseApiController
{
    protected ProyekService $proyekService;
    protected ActiveProyekService $activeProyekService;

    public function __construct()
    {
        $this->proyekService       = new ProyekService();
        $this->activeProyekService = new ActiveProyekService();
    }

    public function getAll()
    {
        $data = $this->proyekService->getAll((array) $this->request->getVar());

        return $this->success($data, 'Data proyek berhasil dimuat');
    }

    public function getOne($id = null)
    {
        $id = (int) ($id ?: ($this->request->getVar('id_proyek') ?: $this->request->getVar('id')));

        if ($id <= 0) {
            return $this->error('ID proyek tidak valid', 400);
        }

        $data = $this->proyekService->getOne($id);
        if (!$data) {
            return $this->error('Proyek tidak ditemukan', 404);
        }

        return $this->success($data);
    }

    public function add()
    {
        $result = $this->proyekService->add($this->request);

        if (!($result['success'] ?? false)) {
            return $this->error($result['messages'] ?? 'Gagal menambahkan proyek', 400);
        }

        return $this->successCreated($result, $result['messages'] ?? 'Proyek berhasil ditambahkan');
    }

    public function edit($id = null)
    {
        $result = $this->proyekService->edit($this->request);

        if (!($result['success'] ?? false)) {
            return $this->error($result['messages'] ?? 'Gagal memperbarui proyek', 400);
        }

        return $this->success($result, $result['messages'] ?? 'Proyek berhasil diperbarui');
    }

    public function getSiteplanList($id = null)
    {
        $idProyek = (int) ($id ?: $this->request->getVar('idProyek'));

        return $this->success($this->proyekService->getSiteplanList($idProyek));
    }

    public function remove()
    {
        $id = (int) $this->request->getPost('id_proyek');
        if ($id <= 0) {
            return $this->error('ID proyek tidak valid', 400);
        }

        $result = $this->proyekService->remove($id);

        if (!($result['success'] ?? false)) {
            return $this->error($result['messages'] ?? 'Gagal menghapus proyek', 400);
        }

        return $this->success($result, $result['messages'] ?? 'Proyek berhasil dihapus');
    }

    public function getAccessible()
    {
        $list     = $this->activeProyekService->getAccessibleList((int) user_id());
        $activeId = $this->activeProyekService->getActiveId();

        return $this->success([
            'active_id' => $activeId,
            'items'     => array_map(static function ($row) {
                return [
                    'id_proyek'       => (int) $row->id_proyek,
                    'nama_proyek'     => $row->nama_proyek,
                    'logo_access_url' => $row->logo_access_url ?? '',
                ];
            }, $list),
        ], 'Daftar proyek yang dapat diakses berhasil dimuat');
    }

    public function setActive()
    {
        $idProyek = (int) $this->request->getPost('id_proyek');
        $result   = $this->activeProyekService->setActive($idProyek);

        if (!($result['success'] ?? false)) {
            return $this->error($result['message'] ?? 'Gagal mengatur proyek aktif', 400);
        }

        return $this->success($result, $result['message'] ?? 'Proyek aktif berhasil diubah');
    }
}

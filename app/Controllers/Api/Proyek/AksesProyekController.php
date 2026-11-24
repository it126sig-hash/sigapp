<?php

namespace App\Controllers\Api\Proyek;

use App\Controllers\Api\BaseApiController;
use App\Services\Proyek\ProyekService;

class AksesProyekController extends BaseApiController
{
    private const MENU_URLS = ['aksesproyek', 'master/aksesproyek'];

    protected ProyekService $proyekService;

    public function __construct()
    {
        $this->proyekService = new ProyekService();
    }

    public function meta()
    {
        if ($denied = $this->ensureMenuAccess(self::MENU_URLS)) {
            return $denied;
        }

        return $this->success(
            $this->proyekService->getAksesMeta(),
            'Metadata akses proyek berhasil dimuat'
        );
    }

    public function getAll()
    {
        if ($denied = $this->ensureMenuAccess(self::MENU_URLS)) {
            return $denied;
        }

        if ($this->isRawFormat()) {
            $input   = $this->readRequestData();
            $search  = trim((string) ($input['search'] ?? ''));
            $page    = max(1, (int) ($input['page'] ?? 1));
            $perPage = max(1, min(100, (int) ($input['per_page'] ?? 25)));

            $data = $this->proyekService->getAksesList([
                'format'   => 'raw',
                'search'   => $search,
                'page'     => $page,
                'per_page' => $perPage,
            ]);

            return $this->success($data, 'Data akses proyek berhasil dimuat');
        }

        $data = $this->proyekService->getAksesList();

        return $this->success($data, 'Data akses proyek berhasil dimuat');
    }

    public function getOne($id = null)
    {
        if ($denied = $this->ensureMenuAccess(self::MENU_URLS)) {
            return $denied;
        }

        $input = $this->readRequestData();
        $id    = (int) ($id ?: ($input['id_proyek'] ?? $input['idProyek'] ?? $input['id'] ?? 0));

        if ($id <= 0) {
            return $this->error('ID proyek tidak valid', 400);
        }

        $data = $this->proyekService->getAksesOne($id, $this->isRawFormat());
        if (!$data) {
            return $this->error('Data akses proyek tidak ditemukan', 404);
        }

        return $this->success($data);
    }

    public function getAkses()
    {
        if ($denied = $this->ensureMenuAccess(self::MENU_URLS)) {
            return $denied;
        }

        $input  = $this->readRequestData();
        $search = (string) ($input['search'] ?? '');
        $data   = $this->proyekService->searchAksesUsers($search, $this->isRawFormat());

        return $this->success($data, 'Pencarian pengguna berhasil dimuat');
    }

    public function edit($id = null)
    {
        if ($denied = $this->ensureMenuAccess(self::MENU_URLS)) {
            return $denied;
        }

        $postData = $this->readRequestData();
        if ($id !== null && empty($postData['id_proyek']) && empty($postData['idProyek'])) {
            $postData['id_proyek'] = $id;
        }

        $result = $this->proyekService->updateAkses($postData);

        if (!($result['success'] ?? false)) {
            $statusCode = (int) ($result['status_code'] ?? 422);

            return $this->error(
                (string) ($result['messages'] ?? 'Gagal memperbarui akses proyek'),
                $statusCode,
                $result['errors'] ?? null
            );
        }

        return $this->success(
            $result['data'] ?? $result,
            (string) ($result['messages'] ?? 'Akses proyek berhasil diperbarui')
        );
    }
}

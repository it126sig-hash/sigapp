<?php

namespace App\Controllers\Api\Mkdt;

use App\Controllers\Api\BaseApiController;
use App\Services\Mkdt\PosisiKonsumenService;
use CodeIgniter\HTTP\ResponseInterface;

class PosisiKonsumenController extends BaseApiController
{
    protected PosisiKonsumenService $posisiKonsumenService;

    public function __construct(?PosisiKonsumenService $posisiKonsumenService = null)
    {
        $this->posisiKonsumenService = $posisiKonsumenService ?? new PosisiKonsumenService();
    }

    public function meta(): ResponseInterface
    {
        if (!function_exists('user_id')) {
            helper('auth');
        }

        $userId     = (int) (function_exists('user_id') ? (user_id() ?? 0) : 0);
        $userGroups = [];
        if (function_exists('user') && user()) {
            $userGroups = array_map('strval', array_keys(user()->getRoles()));
        }

        $status    = $this->request->getVar('status');
        $indexData = $this->posisiKonsumenService->getIndexData(
            $status !== null ? (string) $status : null,
            $userId,
            $userGroups
        );

        return $this->success($indexData['meta'], 'Metadata posisi konsumen berhasil diambil');
    }

    public function getDataTables($status = null): ResponseInterface
    {
        $isRaw = $this->isRawRequest();

        if ($isRaw) {
            $params = $this->readRequestData();
            if (!isset($params['search'])) {
                $params['search'] = ['value' => '', 'regex' => false];
            }

            $result = $this->posisiKonsumenService->getRawDataTables(
                $params,
                $status !== null ? (string) $status : null
            );

            return $this->success($result, 'Data posisi konsumen berhasil diambil');
        }

        return $this->posisiKonsumenService->getDataTable($this->request, $status);
    }

    public function getDataTablesAkad(): ResponseInterface
    {
        return $this->getDataTables('Akad');
    }

    public function getDataTablesBatal(): ResponseInterface
    {
        $isRaw = $this->isRawRequest();

        if ($isRaw) {
            $params = $this->readRequestData();
            if (!isset($params['search'])) {
                $params['search'] = ['value' => '', 'regex' => false];
            }

            $result = $this->posisiKonsumenService->getRawDataTablesBatal($params);

            return $this->success($result, 'Data posisi konsumen batal berhasil diambil');
        }

        return $this->posisiKonsumenService->getDataTablesBatal($this->request);
    }

    public function exportPoskon($tipe = 'xlsx', $status = 'aktif'): ResponseInterface
    {
        if (!function_exists('user_id')) {
            helper('auth');
        }

        $isRaw   = $this->isRawRequest();
        $payload = $this->readRequestData();
        $filters = [
            'id_proyek'  => $payload['id_proyek'] ?? $this->request->getVar('id_proyek'),
            'id_cluster' => $payload['id_cluster'] ?? $this->request->getVar('id_cluster'),
            'id_jalan'   => $payload['id_jalan'] ?? $this->request->getVar('id_jalan'),
        ];

        $userId = (int) (function_exists('user_id') ? (user_id() ?? 0) : 0);
        $result = $this->posisiKonsumenService->exportPoskon(
            (string) $tipe,
            (string) $status,
            $filters,
            $userId
        );

        if ($isRaw) {
            if (($result['status'] ?? null) === 'error' || empty($result['status'])) {
                $message = (string) ($result['message'] ?? 'Export posisi konsumen gagal');
                return $this->error($message, 422, ['id_proyek' => $message]);
            }

            return $this->success($result, (string) ($result['message'] ?? 'File berhasil disimpan di server'));
        }

        if (($result['status'] ?? null) === 'error') {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => $result['message'] ?? 'Tidak ada proyek yang dipilih',
            ]);
        }

        return $this->response->setJSON($result);
    }

    public function getRiwayatExport($status = 'Aktif'): ResponseInterface
    {
        $isRaw    = $this->isRawRequest();
        $payload  = $this->readRequestData();
        $idProyek = $payload['id_proyek'] ?? $this->request->getVar('id_proyek');
        $rows     = $this->posisiKonsumenService->getRiwayatExport($idProyek, (string) $status);

        if ($isRaw) {
            return $this->success($rows, 'Riwayat export posisi konsumen berhasil diambil');
        }

        return $this->response->setJSON($rows);
    }

    protected function isRawRequest(): bool
    {
        // TODO(full-json): switch default to raw JSON once legacy DataTables views migrate
        return $this->request->getVar('format') === 'raw';
    }
}

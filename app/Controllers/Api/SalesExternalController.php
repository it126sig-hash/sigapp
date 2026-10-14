<?php

namespace App\Controllers\Api;

use App\Repositories\SalesExternalRepository;
use App\Services\SalesExternalService;
use CodeIgniter\HTTP\ResponseInterface;

class SalesExternalController extends BaseApiController
{
    protected SalesExternalService $service;
    protected SalesExternalRepository $repo;

    public function __construct()
    {
        $this->service = new SalesExternalService();
        $this->repo    = new SalesExternalRepository();
    }

    /**
     * Server-side DataTables list master sales
     */
    public function listMasterSales(): ResponseInterface
    {
        $params = [
            'start'  => (int) ($this->request->getVar('start') ?? 0),
            'length' => (int) ($this->request->getVar('length') ?? 10),
            'search' => trim((string) ($this->request->getVar('search')['value'] ?? $this->request->getVar('search') ?? '')),
            'status' => $this->request->getVar('status') ?? 'all',
        ];

        $result = $this->repo->getMasterSalesList($params);

        return $this->response->setJSON([
            'draw'            => (int) ($this->request->getVar('draw') ?? 1),
            'recordsTotal'    => $result['total'],
            'recordsFiltered' => $result['total'],
            'data'            => $result['data'],
            'token'           => csrf_hash(),
        ]);
    }

    /**
     * Ambil 1 record master sales
     */
    public function getSales(int $id): ResponseInterface
    {
        $sales = $this->repo->getSalesById($id);
        if (! $sales) {
            return $this->error('Data sales tidak ditemukan.', 404);
        }
        return $this->success($sales);
    }

    /**
     * Simpan / Perbarui Master Sales External
     */
    public function saveMasterSales(): ResponseInterface
    {
        $data = $this->request->getPost();
        $filePks = $this->request->getFile('lampiran_pks');

        try {
            $result = $this->service->saveMasterSales($data, (int) user_id(), $filePks);
            return $this->success($result, $result['message']);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    /**
     * Ambil riwayat perubahan tarif & PKS sales
     */
    public function getSalesHistory(int $id): ResponseInterface
    {
        $history = $this->repo->getSalesHistory($id);
        return $this->success($history);
    }

    /**
     * List sales aktif untuk dropdown select2 pada form transaksi
     */
    public function getActiveSalesList(): ResponseInterface
    {
        $list = $this->repo->getActiveSales();
        return $this->success($list);
    }

    /**
     * Server-side DataTables tracking fee sales
     */
    public function listFeeSales(): ResponseInterface
    {
        $params = [
            'start'      => (int) ($this->request->getVar('start') ?? 0),
            'length'     => (int) ($this->request->getVar('length') ?? 10),
            'search'     => trim((string) ($this->request->getVar('search')['value'] ?? $this->request->getVar('search') ?? '')),
            'id_proyek'  => $this->request->getVar('id_proyek'),
            'status_fee' => $this->request->getVar('status_fee') ?? 'all',
            'is_ajb'     => $this->request->getVar('is_ajb'),
        ];

        $result = $this->repo->getFeeSalesList($params);

        return $this->response->setJSON([
            'draw'            => (int) ($this->request->getVar('draw') ?? 1),
            'recordsTotal'    => $result['total'],
            'recordsFiltered' => $result['total'],
            'data'            => $result['data'],
            'token'           => csrf_hash(),
        ]);
    }

    /**
     * Ambil detail kalkulasi dan termin fee sales per MKDT
     */
    public function getFeeDetail(int $idMkdt): ResponseInterface
    {
        $detail = $this->repo->getFeeDetailByMkdt($idMkdt);
        if (! $detail) {
            return $this->error('Data transaksi akad tidak ditemukan.', 404);
        }
        return $this->success($detail);
    }

    /**
     * Simpan / Perbarui kalkulasi fee sales dan skema termin
     */
    public function saveFeeHeader(): ResponseInterface
    {
        $data = $this->request->getPost();

        try {
            $result = $this->service->saveFeeHeader($data, (int) user_id());
            return $this->success($result, $result['message']);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    /**
     * Pengajuan SPP untuk termin tertentu
     */
    public function submitPengajuanSpp(): ResponseInterface
    {
        $idTermin = (int) ($this->request->getPost('id_termin') ?? 0);
        $data     = $this->request->getPost();
        $fileSpp  = $this->request->getFile('lampiran_spp');

        try {
            $result = $this->service->submitPengajuanSpp($idTermin, $data, (int) user_id(), $fileSpp);
            return $this->success($result, $result['message']);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    /**
     * Konfirmasi realisasi pencairan dana termin
     */
    public function konfirmasiCair(): ResponseInterface
    {
        $idTermin  = (int) ($this->request->getPost('id_termin') ?? 0);
        $data      = $this->request->getPost();
        $fileBukti = $this->request->getFile('lampiran_bukti_cair');

        try {
            $result = $this->service->confirmPencairan($idTermin, $data, (int) user_id(), $fileBukti);
            return $this->success($result, $result['message']);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage(), 422);
        }
    }
}

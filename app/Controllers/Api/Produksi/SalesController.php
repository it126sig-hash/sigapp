<?php

namespace App\Controllers\Api\Produksi;

use App\Controllers\Api\BaseApiController;
use App\Services\Produksi\ProduksiService;
use CodeIgniter\HTTP\ResponseInterface;

class SalesController extends BaseApiController
{
    protected ProduksiService $produksiService;

    public function __construct(?ProduksiService $produksiService = null)
    {
        $this->produksiService = $produksiService ?? new ProduksiService();
    }

    public function get_data_by_id(): ResponseInterface
    {
        $idKavling = (int) ($this->request->getVar('id_kavling') ?? 0);
        $result = $this->produksiService->getChecklistSalesDetail($idKavling);
        $result['token'] = csrf_hash();

        return $this->response->setJSON($result);
    }

    public function save_checklist(): ResponseInterface
    {
        $idKavling  = (int) ($this->request->getVar('id_kavling') ?? 0);
        $isChecked  = $this->request->getVar('is_checked');
        $postVars   = (array) $this->request->getVar();
        $userId     = (int) user_id();

        $result = $this->produksiService->saveChecklistSales($idKavling, $isChecked, $postVars, $userId);
        $result['token'] = csrf_hash();

        return $this->response->setJSON($result);
    }

    public function get_data_komplain_by_id(): ResponseInterface
    {
        $idKavling = (int) ($this->request->getVar('id_kavling') ?? 0);
        $result = $this->produksiService->getKomplainDetail($idKavling);
        $result['token'] = csrf_hash();

        return $this->response->setJSON($result);
    }

    public function batalkan_komplain(): ResponseInterface
    {
        $idKavling   = (int) ($this->request->getVar('id_kavling') ?? 0);
        $idKomplain  = (int) ($this->request->getVar('id_komplain') ?? 0);

        $result = $this->produksiService->batalkanKomplain($idKavling, $idKomplain ?: null);
        $result['token'] = csrf_hash();

        return $this->response->setJSON($result);
    }

    public function save_komplain_sales(): ResponseInterface
    {
        $data          = (array) $this->request->getVar();
        $uploadedFiles = (array) $this->request->getFiles();
        $userId        = (int) user_id();

        $result = $this->produksiService->saveKomplainSales($data, $uploadedFiles, $userId);
        $result['token'] = csrf_hash();

        return $this->response->setJSON($result);
    }

    public function get_data_serah_terima_by_id(): ResponseInterface
    {
        $idKavling = (int) ($this->request->getVar('id_kavling') ?? 0);
        $result = $this->produksiService->getSerahTerimaDetail($idKavling);
        $result['token'] = csrf_hash();

        return $this->response->setJSON($result);
    }

    public function save_serah_terima(): ResponseInterface
    {
        $data   = (array) $this->request->getVar();
        $userId = (int) user_id();

        $result = $this->produksiService->saveSerahTerima($data, $userId);
        $result['token'] = csrf_hash();

        return $this->response->setJSON($result);
    }
}

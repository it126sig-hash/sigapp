<?php

namespace App\Controllers\Api;

use CodeIgniter\HTTP\ResponseInterface;
use App\Services\ReferralService;
use App\Repositories\ReferralRepository;

class ReferralController extends BaseApiController
{
    protected $service;
    protected $repo;

    public function __construct()
    {
        $this->service = new ReferralService();
        $this->repo = new ReferralRepository();
    }

    public function list(): ResponseInterface
    {
        $idProyek = $this->request->getPost('id_proyek') ?: active_proyek_id();
        $idCluster = $this->request->getPost('id_cluster');

        $filters = [];
        if ($idCluster) $filters['id_cluster'] = $idCluster;

        $data = $this->repo->getListMGM($idProyek, $filters);
        return $this->success($data);
    }

    public function subRows(): ResponseInterface
    {
        $idProyek = $this->request->getPost('id_proyek') ?: active_proyek_id();
        $idKonsumenReferrer = $this->request->getPost('id_konsumen_referrer');

        if (!$idKonsumenReferrer) return $this->failValidationErrors('ID Referrer diperlukan');

        $data = $this->repo->getSubRowsByReferrer($idKonsumenReferrer, $idProyek);
        return $this->success($data);
    }

    public function confirmBonus(): ResponseInterface
    {
        $idBonus = $this->request->getPost('id_bonus');
        $nominal = $this->request->getPost('nominal_bonus');
        
        $nominalOverride = null;
        if ($nominal !== null && $nominal !== '') {
             $nominalOverride = (float) str_replace(',', '', $nominal);
        }

        $res = $this->service->confirmBonus($idBonus, $nominalOverride);
        if (!$res['success']) return $this->failValidationErrors($res['message']);
        
        return $this->success(['message' => 'Bonus berhasil dikonfirmasi']);
    }

    public function payByPromosi(): ResponseInterface
    {
        $idBonus = $this->request->getPost('id_bonus');
        $file = $this->request->getFile('bukti_bayar');

        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return $this->failValidationErrors('Bukti bayar diperlukan');
        }

        $storageService = new \App\Services\StorageService();
        $path = $storageService->store($file, 'uploads/promosi/mgm/' . date('Ymd'));

        $res = $this->service->payByPromosi($idBonus, $path);
        if (!$res['success']) return $this->failValidationErrors($res['message']);
        
        return $this->success(['message' => 'Pembayaran berhasil dicatat']);
    }

    public function submitKeuangan(): ResponseInterface
    {
        $idBonus = $this->request->getPost('id_bonus');
        $file = $this->request->getFile('bukti_bayar');

        $path = null;
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $storageService = new \App\Services\StorageService();
            $path = $storageService->store($file, 'uploads/keuangan/mgm/' . date('Ymd'));
        }

        $res = $this->service->submitToKeuangan($idBonus, $path);
        if (!$res['success']) return $this->failValidationErrors($res['message']);
        
        return $this->success(['message' => 'Berhasil diajukan ke Keuangan']);
    }

    public function cancelBonus(): ResponseInterface
    {
        $idBonus = $this->request->getPost('id_bonus');
        $keterangan = $this->request->getPost('keterangan');

        $res = $this->service->cancelBonus($idBonus, $keterangan);
        if (!$res['success']) return $this->failValidationErrors($res['message']);
        
        return $this->success(['message' => 'Bonus berhasil dibatalkan']);
    }
    
    public function updateKeterangan(): ResponseInterface
    {
         $idBonus = $this->request->getPost('id_bonus');
         $keterangan = $this->request->getPost('keterangan');
         $res = $this->service->updateKeterangan($idBonus, $keterangan);
         if (!$res['success']) return $this->failValidationErrors($res['message']);
         return $this->success(['message' => 'Keterangan berhasil diupdate']);
    }

    public function searchOptions(): ResponseInterface
    {
        $search = $this->request->getPost('q');
        $idProyek = $this->request->getPost('id_proyek') ?: active_proyek_id();

        $data = $this->repo->searchReferrerOptions((string)$search, (int)$idProyek);
        
        $results = [];
        foreach ($data as $row) {
            $results[] = [
                'id' => $row->kode_referal,
                'text' => $row->kode_referal . ' - ' . $row->nama_konsumen
            ];
        }
        
        return $this->response->setJSON(['results' => $results]);
    }
}

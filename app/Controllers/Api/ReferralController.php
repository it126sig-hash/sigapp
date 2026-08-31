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

        $data = $this->service->getSubRowsByReferrer((int) $idKonsumenReferrer, (int) $idProyek);
        return $this->success($data);
    }

    public function confirmBonus(): ResponseInterface
    {
        if (!$this->canPromosi()) {
            return $this->failForbidden('Akses hanya untuk Sales & Promotion');
        }

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
        if (!$this->canPromosi()) {
            return $this->failForbidden('Akses hanya untuk Sales & Promotion');
        }

        $idBonus = $this->request->getPost('id_bonus');
        $file = $this->request->getFile('bukti_bayar');

        if (!$this->isValidMgmFile($file)) {
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
        if (!$this->canPromosi()) {
            return $this->failForbidden('Akses hanya untuk Sales & Promotion');
        }

        $idBonus = $this->request->getPost('id_bonus');
        $nominal = $this->cleanMoney($this->request->getPost('nominal_pengajuan') ?? $this->request->getPost('nominal'));
        $tanggalSpp = trim((string) $this->request->getPost('tanggal_spp'));
        $file = $this->request->getFile('bukti_bayar');

        $path = null;
        if ($file && $file->getError() !== UPLOAD_ERR_NO_FILE) {
            if (!$this->isValidMgmFile($file)) {
                return $this->failValidationErrors('Lampiran SPP harus berupa foto atau PDF yang valid');
            }
            $storageService = new \App\Services\StorageService();
            $path = $storageService->store($file, 'uploads/keuangan/mgm/' . date('Ymd'));
        }

        if (!$path) {
            return $this->failValidationErrors('Lampiran SPP wajib diisi');
        }

        $res = $this->service->submitToKeuangan((int) $idBonus, $nominal, $tanggalSpp, $path);
        if (!$res['success']) return $this->failValidationErrors($res['message']);
        
        return $this->success(['message' => 'Berhasil diajukan ke Keuangan']);
    }

    public function markCairKeuangan(): ResponseInterface
    {
        if (!$this->canKeuangan()) {
            return $this->failForbidden('Akses hanya untuk Keuangan');
        }

        $idBonus = (int) $this->request->getPost('id_bonus');
        $tanggalCair = trim((string) $this->request->getPost('tanggal_cair_keuangan'));
        $nominalCair = $this->cleanMoney($this->request->getPost('nominal_cair_keuangan') ?? $this->request->getPost('nominal'));
        $file = $this->request->getFile('bukti_bayar');

        if (!$this->isValidMgmFile($file)) {
            return $this->failValidationErrors('Bukti transfer ke Promosi wajib berupa foto atau PDF yang valid');
        }

        $storageService = new \App\Services\StorageService();
        $path = $storageService->store($file, 'uploads/keuangan/mgm/transfer/' . date('Ymd'));

        $res = $this->service->markCairKeuangan($idBonus, $tanggalCair, $nominalCair, $path);
        if (!$res['success']) return $this->failValidationErrors($res['message']);

        return $this->success(['message' => 'Pencairan Keuangan berhasil dicatat']);
    }

    public function cancelBonus(): ResponseInterface
    {
        if (!$this->canPromosi()) {
            return $this->failForbidden('Akses hanya untuk Sales & Promotion');
        }

        $idBonus = $this->request->getPost('id_bonus');
        $keterangan = $this->request->getPost('keterangan');

        $res = $this->service->cancelBonus($idBonus, $keterangan);
        if (!$res['success']) return $this->failValidationErrors($res['message']);
        
        return $this->success(['message' => 'Bonus berhasil dibatalkan']);
    }
    
    public function updateKeterangan(): ResponseInterface
    {
         if (!$this->canPromosi()) {
             return $this->failForbidden('Akses hanya untuk Sales & Promotion');
         }

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

    private function canPromosi(): bool
    {
        return in_groups(['1', '8']);
    }

    private function canKeuangan(): bool
    {
        return in_groups(['1', '3']);
    }

    private function cleanMoney($value): ?float
    {
        $digits = preg_replace('/[^\d-]/', '', (string) $value);
        if ($digits === '' || $digits === '-') {
            return null;
        }

        return (float) $digits;
    }

    private function isValidMgmFile($file): bool
    {
        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return false;
        }

        $extension = strtolower((string) $file->getClientExtension());
        return in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'pdf'], true);
    }
}

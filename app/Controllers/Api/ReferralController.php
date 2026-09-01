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

        $filters = $this->mgmFilters();
        if ($idCluster) $filters['id_cluster'] = $idCluster;

        $data = $this->repo->getListMGM($idProyek, $filters);
        return $this->success($data);
    }

    public function subRows(): ResponseInterface
    {
        $idProyek = $this->request->getPost('id_proyek') ?: active_proyek_id();
        $idKonsumenReferrer = $this->request->getPost('id_konsumen_referrer');

        if (!$idKonsumenReferrer) return $this->failValidationErrors('ID Referrer diperlukan');

        $data = $this->service->getSubRowsByReferrer((int) $idKonsumenReferrer, (int) $idProyek, $this->mgmFilters());
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
        $tanggalPembayaran = trim((string) $this->request->getPost('tanggal_pembayaran'));
        $namaPenerima = trim((string) $this->request->getPost('nama_penerima'));
        $noRekening = trim((string) ($this->request->getPost('no_rekening_penerima') ?? $this->request->getPost('no_rekening')));
        $bankPenerima = trim((string) ($this->request->getPost('bank_penerima') ?? $this->request->getPost('bank')));
        $keterangan = $this->request->getPost('keterangan');
        $file = $this->request->getFile('bukti_bayar');

        if (!$this->isValidMgmFile($file)) {
            return $this->failValidationErrors('Bukti bayar wajib berupa foto atau PDF yang valid');
        }

        $storageService = new \App\Services\StorageService();
        $path = $storageService->store($file, 'uploads/promosi/mgm/' . date('Ymd'));

        $res = $this->service->payByPromosi((int) $idBonus, $path, $tanggalPembayaran, $namaPenerima, $noRekening, $bankPenerima, $keterangan);
        if (!$res['success']) return $this->failValidationErrors($res['message']);
        
        return $this->success(['message' => 'Pembayaran berhasil dicatat']);
    }

    public function updateNominal(): ResponseInterface
    {
        if (!$this->canPromosi()) {
            return $this->failForbidden('Akses hanya untuk Sales & Promotion');
        }

        $idBonus = (int) $this->request->getPost('id_bonus');
        $nominal = $this->cleanMoney($this->request->getPost('nominal_bonus') ?? $this->request->getPost('nominal'));
        $keterangan = $this->request->getPost('keterangan');

        if ($nominal === null) {
            return $this->failValidationErrors('Nominal bonus wajib diisi');
        }

        $res = $this->service->updateNominalBonus($idBonus, $nominal, $keterangan);
        if (!$res['success']) return $this->failValidationErrors($res['message']);

        return $this->success(['message' => 'Nominal bonus berhasil diperbarui']);
    }

    public function submitKeuangan(): ResponseInterface
    {
        if (!$this->canPromosi()) {
            return $this->failForbidden('Akses hanya untuk Sales & Promotion');
        }

        $idBonus = $this->request->getPost('id_bonus');
        $nominal = $this->cleanMoney($this->request->getPost('nominal_pengajuan') ?? $this->request->getPost('nominal'));
        $tanggalSpp = trim((string) $this->request->getPost('tanggal_spp'));
        $keterangan = $this->request->getPost('keterangan');
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

        $res = $this->service->submitToKeuangan((int) $idBonus, $nominal, $tanggalSpp, $path, $keterangan);
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
        $namaPenerima = trim((string) $this->request->getPost('nama_penerima'));
        $noRekening = trim((string) ($this->request->getPost('no_rekening') ?? $this->request->getPost('no_rekening_penerima')));
        $bankPencairan = trim((string) ($this->request->getPost('bank_pencairan') ?? $this->request->getPost('bank_penerima')));
        $keterangan = $this->request->getPost('keterangan');
        $file = $this->request->getFile('bukti_bayar');

        if (!$this->isValidMgmFile($file)) {
            return $this->failValidationErrors('Bukti transfer ke Promosi wajib berupa foto atau PDF yang valid');
        }

        $storageService = new \App\Services\StorageService();
        $path = $storageService->store($file, 'uploads/keuangan/mgm/transfer/' . date('Ymd'));

        $res = $this->service->markCairKeuangan($idBonus, $tanggalCair, $nominalCair, $path, $namaPenerima, $noRekening, $bankPencairan, $keterangan);
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

    private function canMgmSearch(): bool
    {
        return $this->canPromosi() || $this->canKeuangan();
    }

    private function mgmFilters(): array
    {
        $allowedStatuses = ['booking', 'akad', 'cair_bonus_booking', 'cair_bonus_akad'];
        $status = trim((string) $this->request->getPost('filter_status'));

        $filters = [
            'kode_referal' => strtoupper(trim((string) $this->request->getPost('kode_referal'))),
            'filter_status' => in_array($status, $allowedStatuses, true) ? $status : '',
            'tanggal_mulai' => $this->cleanDate($this->request->getPost('tanggal_mulai')),
            'tanggal_selesai' => $this->cleanDate($this->request->getPost('tanggal_selesai')),
        ];

        if ($filters['tanggal_mulai'] && $filters['tanggal_selesai'] && $filters['tanggal_mulai'] > $filters['tanggal_selesai']) {
            [$filters['tanggal_mulai'], $filters['tanggal_selesai']] = [$filters['tanggal_selesai'], $filters['tanggal_mulai']];
        }

        return array_filter($filters, static fn ($value) => $value !== '' && $value !== null);
    }

    private function cleanDate($value): ?string
    {
        $value = trim((string) $value);
        if ($value === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }

        return $value;
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
        $mime = strtolower((string) $file->getMimeType());

        return in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'pdf'], true)
            && (str_starts_with($mime, 'image/') || $mime === 'application/pdf');
    }
}

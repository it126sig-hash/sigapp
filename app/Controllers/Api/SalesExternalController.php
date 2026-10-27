<?php

namespace App\Controllers\Api;

use App\Repositories\SalesExternalRepository;
use App\Services\SalesExternalService;
use App\Services\Settings\FeeSalesSettingService;
use CodeIgniter\HTTP\ResponseInterface;

class SalesExternalController extends BaseApiController
{
    protected SalesExternalService $service;
    protected SalesExternalRepository $repo;
    protected SalesExternalRepository $salesRepo;
    protected ?array $userPermissions = null;

    public function __construct()
    {
        $this->service   = new SalesExternalService();
        $this->repo      = new SalesExternalRepository();
        $this->salesRepo = $this->repo;
    }

    /**
     * Helper resolusi hak akses user saat ini
     */
    protected function getPermissions(): array
    {
        if ($this->userPermissions === null) {
            $settingService = new FeeSalesSettingService();
            $this->userPermissions = $settingService->getUserPermissions((int) user_id());
        }

        return $this->userPermissions;
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
        if (empty($this->getPermissions()['can_view'])) {
            return $this->error('Anda tidak memiliki izin untuk melihat data fee sales.', 403);
        }

        $idProyek = $this->request->getVar('id_proyek') ?: session()->get('id_proyek');

        $params = [
            'start'      => (int) ($this->request->getVar('start') ?? 0),
            'length'     => (int) ($this->request->getVar('length') ?? 10),
            'search'     => trim((string) ($this->request->getVar('search')['value'] ?? $this->request->getVar('search') ?? '')),
            'id_proyek'  => $idProyek ? (int) $idProyek : null,
            'id_sales'   => $this->request->getVar('id_sales'),
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
        if (empty($this->getPermissions()['can_view'])) {
            return $this->error('Anda tidak memiliki izin untuk melihat detail fee sales.', 403);
        }

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
        $perms = $this->getPermissions();
        $isAllowed = ! empty($perms['can_manage']);

        if (! $isAllowed && (! empty($perms['can_sign_mkdt']) || ! empty($perms['can_sign_keuangan']))) {
            $idMkdt = (int) ($this->request->getPost('id_mkdt') ?? 0);
            if ($idMkdt <= 0 && ! empty($this->request->getPost('id_fee_sales'))) {
                $feeRow = \Config\Database::connect()->table('fee_sales')
                    ->select('id_mkdt')
                    ->where('id', (int) $this->request->getPost('id_fee_sales'))
                    ->get()
                    ->getRow();
                if ($feeRow) {
                    $idMkdt = (int) $feeRow->id_mkdt;
                }
            }

            $hasCair = $idMkdt > 0 ? $this->salesRepo->hasCairTerminByMkdt($idMkdt) : false;
            if (! $hasCair) {
                $isAllowed = true;
            }
        }

        if (! $isAllowed) {
            return $this->error('Anda tidak memiliki izin untuk mengelola atau menyimpan fee sales.', 403);
        }

        $data = $this->request->getPost();

        try {
            $result = $this->service->saveFeeHeader($data, (int) user_id());
            return $this->success($result, $result['message']);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    /**
     * Hapus satu entri fee sales (hanya jika belum diajukan/cair)
     */
    public function deleteFeeEntry(int $idFeeSales): ResponseInterface
    {
        if (empty($this->getPermissions()['can_manage'])) {
            return $this->error('Anda tidak memiliki izin untuk menghapus data fee sales.', 403);
        }

        try {
            $result = $this->service->deleteFeeEntry($idFeeSales, (int) user_id());
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
        if (empty($this->getPermissions()['can_submit_spp'])) {
            return $this->error('Anda tidak memiliki izin untuk membuat pengajuan pencairan.', 403);
        }

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
        if (empty($this->getPermissions()['can_cairkan'])) {
            return $this->error('Anda tidak memiliki izin untuk mencairkan dana termin fee sales.', 403);
        }

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

    /**
     * Edit pengajuan pencairan termin dengan pencatatan alasan
     */
    public function editPengajuan(): ResponseInterface
    {
        if (empty($this->getPermissions()['can_submit_spp']) && empty($this->getPermissions()['can_manage'])) {
            return $this->error('Anda tidak memiliki izin untuk mengubah pengajuan pencairan.', 403);
        }

        $idTermin = (int) ($this->request->getPost('id_termin') ?? 0);
        $data     = $this->request->getPost();
        $fileSpp  = $this->request->getFile('lampiran_spp');

        try {
            $result = $this->service->updatePengajuan($idTermin, $data, (int) user_id(), $fileSpp);
            return $this->success($result, $result['message']);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    /**
     * Edit data pencairan dana termin dengan pencatatan alasan
     */
    public function editCair(): ResponseInterface
    {
        if (empty($this->getPermissions()['can_cairkan'])) {
            return $this->error('Anda tidak memiliki izin untuk mengubah data pencairan.', 403);
        }

        $idTermin  = (int) ($this->request->getPost('id_termin') ?? 0);
        $data      = $this->request->getPost();
        $fileBukti = $this->request->getFile('lampiran_bukti_cair');

        try {
            $result = $this->service->updatePencairan($idTermin, $data, (int) user_id(), $fileBukti);
            return $this->success($result, $result['message']);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    /**
     * Persiapan data kavling untuk modal pengajuan kelompok
     */
    public function preparePengajuanGroup(): ResponseInterface
    {
        if (empty($this->getPermissions()['can_submit_spp'])) {
            return $this->error('Anda tidak memiliki izin untuk membuat pengajuan pencairan.', 403);
        }

        $mkdtIds = $this->request->getPost('mkdt_ids');
        if (! is_array($mkdtIds)) {
            $mkdtIds = explode(',', (string) $mkdtIds);
        }

        $list = $this->repo->getFeeDetailsByMkdtIds($mkdtIds);
        return $this->success($list);
    }

    /**
     * Simpan Pengajuan Pencairan Kelompok
     */
    public function createPengajuanGroup(): ResponseInterface
    {
        if (empty($this->getPermissions()['can_submit_spp'])) {
            return $this->error('Anda tidak memiliki izin untuk membuat pengajuan pencairan kelompok.', 403);
        }

        $data = $this->request->getPost();
        $fileSpp = $this->request->getFile('lampiran_spp');

        try {
            $result = $this->service->createPengajuanGroup($data, (int) user_id(), $fileSpp);
            return $this->success($result, $result['message']);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    /**
     * Server-side DataTables list grup pengajuan
     */
    public function listPengajuanGroup(): ResponseInterface
    {
        if (empty($this->getPermissions()['can_view'])) {
            return $this->error('Anda tidak memiliki izin untuk melihat daftar pengajuan fee sales.', 403);
        }

        $idProyek = $this->request->getVar('id_proyek') ?: session()->get('id_proyek');

        $params = [
            'start'     => (int) ($this->request->getVar('start') ?? 0),
            'length'    => (int) ($this->request->getVar('length') ?? 10),
            'search'    => trim((string) ($this->request->getVar('search')['value'] ?? $this->request->getVar('search') ?? '')),
            'id_proyek' => $idProyek ? (int) $idProyek : null,
            'status'    => $this->request->getVar('status') ?? 'all',
        ];

        $result = $this->repo->getPengajuanGroupList($params);

        return $this->response->setJSON([
            'draw'            => (int) ($this->request->getVar('draw') ?? 1),
            'recordsTotal'    => $result['total'],
            'recordsFiltered' => $result['total'],
            'data'            => $result['data'],
            'token'           => csrf_hash(),
        ]);
    }

    /**
     * Ambil detail grup pengajuan
     */
    public function getPengajuanGroupDetail(int $id): ResponseInterface
    {
        if (empty($this->getPermissions()['can_view'])) {
            return $this->error('Anda tidak memiliki izin untuk melihat detail pengajuan fee sales.', 403);
        }

        $detail = $this->repo->getPengajuanGroupDetail($id);
        if (! $detail) {
            return $this->error('Grup pengajuan tidak ditemukan.', 404);
        }
        return $this->success($detail);
    }

    /**
     * Konfirmasi pencairan massal dari dalam grup pengajuan
     */
    public function konfirmasiCairGroup(): ResponseInterface
    {
        if (empty($this->getPermissions()['can_cairkan'])) {
            return $this->error('Anda tidak memiliki izin untuk mencairkan dana kelompok fee sales.', 403);
        }

        $idPengajuan = (int) ($this->request->getPost('id_pengajuan') ?? 0);
        $data        = $this->request->getPost();
        $fileBukti   = $this->request->getFile('lampiran_bukti_cair');

        try {
            $result = $this->service->confirmPencairanGroup($idPengajuan, $data, (int) user_id(), $fileBukti);
            return $this->success($result, $result['message']);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    /**
     * Membatalkan (Void) Pengajuan Pencairan
     */
    public function voidPengajuanGroup(): ResponseInterface
    {
        $perms = $this->getPermissions();
        if (empty($perms['can_submit_spp']) && empty($perms['can_manage'])) {
            return $this->error('Anda tidak memiliki izin untuk membatalkan pengajuan pencairan.', 403);
        }

        $idPengajuan = (int) ($this->request->getPost('id_pengajuan') ?? 0);
        $alasan      = trim((string) ($this->request->getPost('alasan') ?? ''));

        if ($idPengajuan <= 0) {
            return $this->error('ID pengajuan tidak valid.', 422);
        }

        try {
            $result = $this->service->voidPengajuanGroup($idPengajuan, $alasan, (int) user_id());
            return $this->success($result, $result['message']);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    /**
     * Tanda tangani pengajuan pencairan oleh MKDT atau Keuangan
     */
    public function signPengajuanGroup(): ResponseInterface
    {
        $perms = $this->getPermissions();
        $role = strtolower(trim((string) ($this->request->getPost('role') ?? '')));

        if ($role === 'mkdt' && empty($perms['can_sign_mkdt'])) {
            return $this->error('Anda tidak memiliki hak akses tanda tangan divisi MKDT.', 403);
        }
        if ($role === 'keuangan' && empty($perms['can_sign_keuangan'])) {
            return $this->error('Anda tidak memiliki hak akses tanda tangan divisi Keuangan.', 403);
        }
        if (! in_array($role, ['mkdt', 'keuangan'], true)) {
            return $this->error('Role tanda tangan tidak valid.', 422);
        }

        $idPengajuan = (int) ($this->request->getPost('id_pengajuan') ?? 0);
        $data        = $this->request->getPost();

        if ($idPengajuan <= 0) {
            return $this->error('ID pengajuan tidak valid.', 422);
        }

        try {
            $result = $this->service->signPengajuanGroup($idPengajuan, $role, $data, (int) user_id());
            return $this->success($result, $result['message']);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    /**
     * Tolak Pengajuan Pencairan oleh MKDT atau Keuangan disertai saran revisi harga jual net AJB
     */
    public function rejectPengajuanGroup(): ResponseInterface
    {
        $perms = $this->getPermissions();
        $role = strtolower(trim((string) ($this->request->getPost('role') ?? '')));

        if ($role === 'mkdt' && empty($perms['can_sign_mkdt'])) {
            return $this->error('Anda tidak memiliki hak akses penolakan divisi MKDT.', 403);
        }
        if ($role === 'keuangan' && empty($perms['can_sign_keuangan'])) {
            return $this->error('Anda tidak memiliki hak akses penolakan divisi Keuangan.', 403);
        }
        if (! in_array($role, ['mkdt', 'keuangan'], true)) {
            return $this->error('Role penolakan tidak valid.', 422);
        }

        $idPengajuan = (int) ($this->request->getPost('id_pengajuan') ?? 0);
        $data        = $this->request->getPost();

        if ($idPengajuan <= 0) {
            return $this->error('ID pengajuan tidak valid.', 422);
        }

        try {
            $result = $this->service->rejectPengajuanGroup($idPengajuan, $role, $data, (int) user_id());
            return $this->success($result, $result['message']);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    /**
     * Hitung ulang nominal pencairan tiap termin berdasarkan Harga Jual Net AJB baru
     */
    public function recalculateTermin(): ResponseInterface
    {
        $perms = $this->getPermissions();
        $isAllowed = ! empty($perms['can_manage'])
            || ! empty($perms['can_submit_spp'])
            || ! empty($perms['can_sign_mkdt'])
            || ! empty($perms['can_sign_keuangan']);

        if (! $isAllowed) {
            $idPengajuan = (int) ($this->request->getVar('id_pengajuan') ?? 0);
            if ($idPengajuan > 0) {
                $group = $this->salesRepo->getPengajuanGroupDetail($idPengajuan);
                if ($group && (int) ($group->created_by ?? 0) === (int) user_id()) {
                    $isAllowed = true;
                }
            }
        }

        if (! $isAllowed) {
            return $this->error('Anda tidak memiliki izin untuk memperbarui nominal pencairan termin.', 403);
        }

        $data = $this->request->getPost();

        try {
            $result = $this->service->recalculateTerminNominals($data, (int) user_id());
            return $this->success($result, $result['message']);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage(), 422);
        }
    }
}

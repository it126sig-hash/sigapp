<?php

namespace App\Services\Produksi;

use App\Repositories\Produksi\ProduksiRepository;
use App\Services\FileAccessService;
use App\Services\FinanceLedgerService;
use App\Services\KonsumenService;
use App\Services\NotifikasiService;
use CodeIgniter\HTTP\Files\UploadedFile;
use RuntimeException;

class ProduksiService
{
    protected ProduksiRepository   $repo;
    protected ProduksiFileService  $fileService;
    protected NotifikasiService    $notifService;
    protected FileAccessService    $fileAccessService;
    protected FinanceLedgerService $ledgerService;
    protected KonsumenService      $konsumenService;
    protected $db;

    public function __construct(
        ?ProduksiRepository   $repo = null,
        ?ProduksiFileService  $fileService = null,
        ?NotifikasiService    $notifService = null,
        ?FileAccessService    $fileAccessService = null,
        ?FinanceLedgerService $ledgerService = null,
        ?KonsumenService      $konsumenService = null
    ) {
        $this->repo              = $repo ?? new ProduksiRepository();
        $this->fileService       = $fileService ?? new ProduksiFileService();
        $this->notifService      = $notifService ?? new NotifikasiService();
        $this->fileAccessService = $fileAccessService ?? new FileAccessService();
        $this->ledgerService     = $ledgerService ?? new FinanceLedgerService();
        $this->konsumenService   = $konsumenService ?? new KonsumenService();
        $this->db                = \Config\Database::connect();
    }

    public function cleanNumeric(mixed $value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }
        if (!is_string($value)) {
            return 0.0;
        }
        $cleaned = preg_replace('/[^\d.,-]/', '', $value);
        if (strpos($cleaned, ',') !== false && strpos($cleaned, '.') !== false) {
            if (strrpos($cleaned, ',') > strrpos($cleaned, '.')) {
                $cleaned = str_replace('.', '', $cleaned);
                $cleaned = str_replace(',', '.', $cleaned);
            } else {
                $cleaned = str_replace(',', '', $cleaned);
            }
        } else {
            $cleaned = str_replace(',', '', $cleaned);
        }
        return (float) $cleaned;
    }

    public function save(array $var, array $uploadedFiles, int $userId): array
    {
        $idKavling   = (int) ($var['id_kavling'] ?? 0);
        $idProduksi  = (int) ($var['id_produksi'] ?? 0);
        $oldProduksi = $idProduksi > 0 ? (array) ($this->repo->getProduksiRaw($idProduksi) ?? []) : [];

        $fotoRows = $this->fileService->uploadFotoGroups($uploadedFiles, $var, $idKavling);
        $f = $this->buildProduksiData($var, $userId);
        $historyPayload = $this->buildHistoryPayload($oldProduksi, $f, $var, $fotoRows);

        $notif    = 'Melakukan perubahan pada progres pembangunan = ' . ($var['progres_bangunan'] ?? '') . '%';
        $sumurbor = [
            'sumurbor'            => $var['sumurbor'] ?? null,
            'sumurbor_keterangan' => $var['sumurbor_keterangan'] ?? null,
            'sumurbor_tanggal'    => $var['sumurbor_tanggal'] ?? null,
            'sumurbor_oleh'       => $userId,
        ];

        $this->db->transStart();

        if (!empty($fotoRows)) {
            $this->repo->insertFiles($fotoRows);
        }

        $this->processChecklists($idKavling, $var, $userId);

        if (empty($f['id_produksi'])) {
            $f['add_by']     = $userId;
            $f['edit_by']    = $userId;
            $f['created_at'] = date('Y-m-d H:i:s');

            $idProduksi = $this->repo->insertProduksi($f);
            $this->repo->updateKavling($idKavling, array_merge($sumurbor, [
                'id_produksi'      => $idProduksi,
                'sumurbor_updated' => date('Y-m-d H:i:s'),
            ]));
        } else {
            $f['edit_by']    = $userId;
            $f['updated_at'] = date('Y-m-d H:i:s');

            $this->repo->updateProduksi((int) $f['id_produksi'], $f);
            $this->repo->updateKavling($idKavling, $sumurbor);
        }

        if ($this->repo->hasProduksiChangeHistoryTable() && $this->shouldWriteHistory($historyPayload)) {
            $this->repo->insertProduksiChangeHistory([
                'id_kavling'  => $idKavling,
                'id_produksi' => $idProduksi ?: null,
                'action'      => empty($oldProduksi) ? 'create' : 'update',
                'summary'     => $historyPayload['summary'],
                'old_data'    => json_encode($historyPayload['old_data']),
                'new_data'    => json_encode($historyPayload['new_data']),
                'files'       => json_encode($historyPayload['files']),
                'add_by'      => $userId,
                'created_at'  => date('Y-m-d H:i:s'),
            ]);
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return ['success' => false, 'messages' => 'Terjadi Kesalahan'];
        }

        $this->notifService->tambah_notif(
            '7;4;9',
            $notif,
            $userId,
            $idKavling,
            '',
            \App\Enums\NotificationEvent::PROGRESS_PRODUKSI,
            null,
            "siteplan/view?id_kavling=" . $idKavling
        );

        return ['success' => true, 'messages' => 'Successfully updated'];
    }

    public function saveMobileUpload(int $idKavling, ?int $idProduksi, array $files, array $vars, int $userId): array
    {
        if ($idKavling <= 0) {
            return ['success' => false, 'messages' => 'Kavling wajib dipilih'];
        }

        if (!$idProduksi || $idProduksi <= 0) {
            $kavling = $this->repo->getKavlingProduksiById($idKavling);
            $idProduksi = (int) ($kavling->id_produksi ?? 0);
        }

        try {
            $fotoRows = $this->fileService->uploadFotoGroups($files, $vars, $idKavling);
        } catch (\Throwable $e) {
            log_message('error', 'Gagal upload foto produksi mobile: ' . $e->getMessage());
            return ['success' => false, 'messages' => 'Gagal menyiapkan file upload'];
        }

        if (empty($fotoRows)) {
            return ['success' => false, 'messages' => 'Pilih minimal 1 foto untuk diunggah'];
        }

        $this->db->transStart();

        $this->repo->insertFiles($fotoRows);

        if ($this->repo->hasProduksiChangeHistoryTable()) {
            $this->repo->insertProduksiChangeHistory([
                'id_kavling'  => $idKavling,
                'id_produksi' => $idProduksi ?: null,
                'action'      => 'upload_mobile',
                'summary'     => count($fotoRows) . ' file/foto diunggah dari mode mobile',
                'old_data'    => json_encode([]),
                'new_data'    => json_encode([]),
                'files'       => json_encode(array_map(static function (array $row) {
                    return [
                        'kategori'        => $row['kategori'] ?? null,
                        'file_name'       => $row['file_name'] ?? null,
                        'tgl_capture'     => $row['tgl_capture'] ?? null,
                        'file_keterangan' => $row['file_keterangan'] ?? null,
                        'foto_lat'        => $row['foto_lat'] ?? null,
                        'foto_lng'        => $row['foto_lng'] ?? null,
                    ];
                }, $fotoRows)),
                'add_by'      => $userId,
                'created_at'  => date('Y-m-d H:i:s'),
            ]);
        }

        $this->db->transComplete();
        $success = $this->db->transStatus() !== false;

        $returnedFiles = $success ? $this->repo->getFilesByKavling($idKavling) : [];
        $returnedFiles = $this->fileAccessService->addAccessUrlsToRows($returnedFiles, 'file_produksi');

        return [
            'success'  => $success,
            'messages' => $success ? 'Foto berhasil diunggah' : 'Gagal menyimpan foto',
            'files'    => $returnedFiles,
        ];
    }

    public function saveBayarProduksi(int $idKavling, int $idItemProduksi, mixed $nominal, string $tanggalBayar, string $keterangan, int $userId): array
    {
        $cleanNominal = $this->cleanNumeric($nominal);

        if ($idKavling <= 0 || $idItemProduksi <= 0 || trim($tanggalBayar) === '' || $cleanNominal <= 0) {
            return ['success' => false, 'messages' => 'data tidak lengkap'];
        }

        try {
            $this->db->transBegin();

            $idBayarProduksi = $this->repo->insertBayarSingle([
                'id_kavling'       => $idKavling,
                'id_item_produksi' => $idItemProduksi,
                'nominal'          => $cleanNominal,
                'keterangan'       => $keterangan,
                'tanggal_bayar'    => trim($tanggalBayar),
                'add_by'           => $userId,
            ]);

            if (!$idBayarProduksi) {
                throw new RuntimeException('data gagal disimpan');
            }

            $this->ledgerService->recordExpenseFromBayarProduksi((int) $idBayarProduksi, $userId);

            if ($this->db->transStatus() === false) {
                throw new RuntimeException('transaksi gagal');
            }

            $this->db->transCommit();
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', 'ProduksiService::saveBayarProduksi error: ' . $e->getMessage());

            return ['success' => false, 'messages' => 'data gagal disimpan'];
        }

        return [
            'success'    => true,
            'messages'   => 'data berhasil disimpan',
            'id_kavling' => $idKavling,
        ];
    }

    public function deleteBayarProduksi(int $id, int $userId): array
    {
        if ($id <= 0) {
            return ['success' => false, 'messages' => 'data tidak ditemukan'];
        }

        try {
            $this->db->transBegin();

            $idKavling = $this->repo->deleteBayar($id);
            if ($idKavling === null) {
                throw new RuntimeException('data gagal dihapus');
            }

            $this->ledgerService->voidByBayarProduksi($id, $userId);

            if ($this->db->transStatus() === false) {
                throw new RuntimeException('transaksi gagal');
            }

            $this->db->transCommit();
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', 'ProduksiService::deleteBayarProduksi error: ' . $e->getMessage());

            return ['success' => false, 'messages' => 'data gagal dihapus'];
        }

        return [
            'success'    => true,
            'messages'   => 'data berhasil dihapus',
            'id_kavling' => $idKavling,
        ];
    }

    public function getBayarProduksiData(int $idKavling): array
    {
        if ($idKavling <= 0) {
            return ['success' => false, 'messages' => 'data tidak ditemukan'];
        }

        return [
            'success'             => true,
            'id_kavling'          => $idKavling,
            'konsumen'            => $this->konsumenService->getByIDKavling($idKavling),
            'riwayat_bayar'       => $this->repo->getRiwayatBayarByKavling($idKavling),
            'list_bayar_produksi' => $this->repo->getBayarList($idKavling),
        ];
    }

    public function getDetailByKavling(int $idKavling, ?int $idProduksi): array
    {
        $files = [];
        if ($idKavling > 0) {
            $files = $this->repo->getFilesByKavling($idKavling);
            $files = $this->fileAccessService->addAccessUrlsToRows($files, 'file_produksi');
        }

        if ($idProduksi && $idProduksi > 0) {
            $prod = $this->repo->getByIdWithUsers($idProduksi);
            if ($prod) {
                $prod->cl = $this->repo->getChecklistWorkForProduksi($idKavling);
                $prod->files = $files;
                return (array) $prod;
            }
        }

        return ['files' => $files];
    }

    public function getHistory(int $idKavling, int $limit = 10, int $offset = 0): array
    {
        $limit = max(1, min(50, $limit));
        $offset = max(0, $offset);

        $total = $this->repo->countProduksiChangeHistory($idKavling);
        $history = $this->repo->getProduksiChangeHistory($idKavling, $limit, $offset);

        foreach ($history as $item) {
            $item->old_data = json_decode($item->old_data ?? '{}', true) ?: [];
            $item->new_data = json_decode($item->new_data ?? '{}', true) ?: [];
            $item->files    = json_decode($item->files ?? '[]', true) ?: [];
        }

        return [
            'history'             => $history,
            'history_total'       => $total,
            'history_limit'       => $limit,
            'history_offset'      => $offset,
            'history_next_offset' => $offset + count($history),
            'history_has_more'    => ($offset + count($history)) < $total,
        ];
    }

    public function getKomplainDetail(int $idKavling): array
    {
        if ($idKavling <= 0) {
            return [];
        }

        $komplain = $this->repo->getKomplainWithUsers($idKavling);
        if ($komplain) {
            $komplain->upload_komplain_sales_urls = $this->fileAccessService->pathUrlsFromDelimitedString(
                $komplain->upload_komplain_sales,
                'komplain_sales'
            );
            $komplain->upload_komplain_produksi_urls = $this->fileAccessService->pathUrlsFromDelimitedString(
                $komplain->upload_komplain_produksi,
                'komplain_produksi'
            );
        }

        return ['komplain' => $komplain];
    }

    public function saveKomplainProduksi(array $data, array $uploadedFiles, int $userId): array
    {
        $terimaKomplain    = (int) ($data['terima_komplain'] ?? 0);
        $isSelesaiProduksi = (int) ($data['is_selesai_produksi'] ?? 0);
        $idKavling         = (int) ($data['id_kavling'] ?? 0);
        $idKomplain        = (int) ($data['id_komplain'] ?? 0);

        if ($terimaKomplain !== 1) {
            return ['success' => true, 'messages' => ''];
        }

        // Verify komplain ownership
        $owner = $this->repo->getKavlingOwnerKomplain($idKavling, $idKomplain);
        if (!$owner) {
            return ['success' => false, 'messages' => 'Data komplain tidak ditemukan'];
        }

        $f = [
            'keterangan_ditangani' => $data['keterangan_ditangani'] ?? '',
            'id_komplain'          => $idKomplain,
            'edit_by'              => $userId,
        ];

        if ($isSelesaiProduksi !== 1) {
            if (trim($f['keterangan_ditangani']) === '') {
                return ['success' => false, 'messages' => 'Keterangan harus diisi'];
            }

            $f['ditangani_oleh'] = $userId;
            $f['ditangani_tgl']  = date('Y-m-d');

            $this->db->transStart();
            $this->repo->updateKomplain($idKomplain, $f);
            $this->repo->updateKavling($idKavling, ['status_komplain' => 2]);
            $this->db->transComplete();

            $success = $this->db->transStatus() !== false;
            return [
                'success'  => $success,
                'messages' => $success ? 'Successfully updated' : 'Terjadi kesalahan',
            ];
        }

        $f['selesai_keterangan_produksi'] = trim((string) ($data['selesai_keterangan_produksi'] ?? ''));
        $f['is_selesai_produksi']         = 1;

        if ($f['selesai_keterangan_produksi'] === '') {
            return ['success' => false, 'messages' => 'Keterangan harus diisi'];
        }

        $imgFiles = $uploadedFiles['upload_komplain_produksi'] ?? [];
        if (!is_array($imgFiles)) {
            $imgFiles = [$imgFiles];
        }

        $uploadPaths = '';
        $lok = 'uploads/komplain_produksi/' . date('Ymd') . '/';
        foreach ($imgFiles as $img) {
            if ($img instanceof UploadedFile && $img->isValid() && !$img->hasMoved()) {
                $name = $img->getRandomName();
                $uploadPaths .= $lok . $name . ';';
                $this->fileAccessService->storeAs($img, $lok, $name);
            }
        }

        if ($uploadPaths === '') {
            return ['success' => false, 'messages' => 'File bukti selesai komplain harus diunggah'];
        }

        $f['upload_komplain_produksi'] = $uploadPaths;
        $f['selesai_oleh_produksi']    = $userId;
        $f['selesai_tgl_produksi']     = date('Y-m-d');

        $this->db->transStart();
        $this->repo->updateKomplain($idKomplain, $f);
        $this->repo->updateKavling($idKavling, ['status_komplain' => 3]);
        $this->db->transComplete();

        $success = $this->db->transStatus() !== false;
        return [
            'success'  => $success,
            'messages' => $success ? 'Successfully updated' : 'Terjadi kesalahan',
        ];
    }

    public function saveKomplainSales(array $data, array $uploadedFiles, int $userId): array
    {
        $idKavling       = (int) ($data['id_kavling'] ?? 0);
        $isSelesaiSales  = (int) ($data['is_selesai_sales'] ?? 0);
        $idKomplain      = (int) ($data['id_komplain'] ?? 0);

        $f = [
            'id_komplain' => $idKomplain,
            'edit_by'     => $userId,
        ];

        if ($isSelesaiSales === 1) {
            $f['selesai_keterangan_sales'] = trim((string) ($data['selesai_keterangan_sales'] ?? ''));
            if ($f['selesai_keterangan_sales'] === '') {
                return ['success' => false, 'messages' => 'Keterangan harus diisi'];
            }

            $f['is_selesai_sales']    = 1;
            $f['selesai_oleh_sales']  = $userId;
            $f['selesai_tgl_sales']   = date('Y-m-d');

            $this->db->transStart();
            $this->repo->updateKomplain($idKomplain, $f);
            $this->repo->updateKavling($idKavling, ['status_komplain' => 4]);
            $this->db->transComplete();

            $success = $this->db->transStatus() !== false;
            return [
                'success'  => $success,
                'messages' => $success ? 'Successfully updated' : 'Terjadi kesalahan',
            ];
        }

        $f['keterangan_komplain'] = trim((string) ($data['keterangan_komplain'] ?? ''));
        if ($f['keterangan_komplain'] === '') {
            return ['success' => false, 'messages' => 'Keterangan harus diisi'];
        }

        $imgFiles = $uploadedFiles['upload_komplain_sales'] ?? [];
        if (!is_array($imgFiles)) {
            $imgFiles = [$imgFiles];
        }

        $uploadPaths = '';
        $lok = 'uploads/komplain_sales/' . date('Ymd') . '/';
        foreach ($imgFiles as $img) {
            if ($img instanceof UploadedFile && $img->isValid() && !$img->hasMoved()) {
                $name = $img->getRandomName();
                $uploadPaths .= $lok . $name . ';';
                $this->fileAccessService->storeAs($img, $lok, $name);
            }
        }

        if ($idKomplain <= 0) {
            if ($uploadPaths === '') {
                return ['success' => false, 'messages' => 'File komplain harus diunggah'];
            }

            $f['komplain_oleh']          = $userId;
            $f['komplain_tgl']           = date('Y-m-d');
            $f['add_by']                 = $userId;
            $f['upload_komplain_sales']  = $uploadPaths;

            $this->db->transStart();
            $insertedId = $this->repo->insertKomplain($f);
            $this->repo->updateKavling($idKavling, [
                'id_komplain'     => $insertedId,
                'status_komplain' => 1,
            ]);
            $this->db->transComplete();

            $success = $this->db->transStatus() !== false;
            return [
                'success'  => $success,
                'messages' => $success ? 'Successfully updated' : 'Terjadi kesalahan',
            ];
        }

        if ($uploadPaths !== '') {
            $f['upload_komplain_sales'] = $uploadPaths;
        }

        $this->db->transStart();
        $this->repo->updateKomplain($idKomplain, $f);
        $this->repo->updateKavling($idKavling, ['status_komplain' => 1]);
        $this->db->transComplete();

        $success = $this->db->transStatus() !== false;
        return [
            'success'  => $success,
            'messages' => $success ? 'Successfully updated' : 'Terjadi kesalahan',
        ];
    }

    public function batalkanKomplain(int $idKavling, ?int $idKomplain): array
    {
        if ($idKavling <= 0) {
            return ['success' => false, 'messages' => 'terjadi kesalahan'];
        }

        $this->db->transStart();
        $this->repo->updateKavling($idKavling, ['id_komplain' => null]);
        if ($idKomplain && $idKomplain > 0) {
            $this->repo->deleteKomplain($idKomplain);
        }
        $this->db->transComplete();

        $success = $this->db->transStatus() !== false;
        return [
            'success'  => $success,
            'messages' => $success ? 'Komplain berhasil dibatalkan' : 'terjadi kesalahan',
        ];
    }

    public function getSerahTerimaDetail(int $idKavling): array
    {
        if ($idKavling <= 0) {
            return [];
        }

        return ['serah_terima' => $this->repo->getSerahTerimaByKavling($idKavling)];
    }

    public function saveSerahTerima(array $data, int $userId): array
    {
        $idKavling = (int) ($data['id_kavling'] ?? 0);
        $idSt      = (int) ($data['id_serah_terima'] ?? 0);

        $f = [
            'is_serah_terima'        => !empty($data['is_serah_terima']) ? 1 : 0,
            'serah_terima_oleh'      => trim((string) ($data['serah_terima_oleh'] ?? '')),
            'serah_terima_ke'        => trim((string) ($data['serah_terima_ke'] ?? '')),
            'serah_terima_tgl'       => trim((string) ($data['serah_terima_tgl'] ?? '')),
            'serah_terima_keterangan'=> (string) ($data['serah_terima_keterangan'] ?? ''),
            'edit_by'                => $userId,
        ];

        if ($f['serah_terima_oleh'] === '' || $f['serah_terima_ke'] === '' || $f['serah_terima_tgl'] === '') {
            return ['success' => false, 'messages' => 'Data serah terima tidak lengkap'];
        }

        $this->db->transStart();

        if ($idSt <= 0) {
            $f['add_by'] = $userId;
            $newStId = $this->repo->insertSerahTerima($f);
            $this->repo->updateKavling($idKavling, [
                'is_serah_terima' => $f['is_serah_terima'],
                'id_serah_terima' => $newStId,
            ]);
        } else {
            $this->repo->updateSerahTerima($idSt, $f);
            $this->repo->updateKavling($idKavling, [
                'is_serah_terima' => $f['is_serah_terima'],
                'id_serah_terima' => $idSt,
            ]);
        }

        $this->db->transComplete();
        $success = $this->db->transStatus() !== false;

        return [
            'success'  => $success,
            'messages' => $success ? 'Successfully updated' : 'Terjadi kesalahan',
        ];
    }

    public function getChecklistSalesDetail(int $idKavling): array
    {
        if ($idKavling <= 0) {
            return [];
        }

        return [
            'kav' => $this->repo->getKavlingCheckStatus($idKavling),
            'cl'  => $this->repo->getChecklistWorkWithUsers($idKavling),
        ];
    }

    public function saveChecklistSales(int $idKavling, mixed $isChecked, array $postVars, int $userId): array
    {
        if ($idKavling <= 0) {
            return ['success' => false, 'messages' => 'Kavling tidak valid'];
        }

        $sub = $this->repo->getAllSubitemIds();
        $existingCw = $this->repo->getChecklistWorkByKavling($idKavling);
        $existingMap = [];
        foreach ($existingCw as $row) {
            $existingMap[$row->id_subitem] = true;
        }

        $this->db->transStart();

        foreach ($sub as $s) {
            $hct = isset($postVars['hasil_cek_t_s'][$s->id_subitem]) ? 1 : 0;
            $hcf = isset($postVars['hasil_cek_f_s'][$s->id_subitem]) ? 1 : 0;
            $hcv = isset($postVars['hasil_cek_v_s'][$s->id_subitem]) ? 1 : 0;
            $kcp = $postVars['keterangan_cek_sales'][$s->id_subitem] ?? null;

            if ($hct !== 0 || $hcf !== 0 || $hcv !== 0 || $kcp !== null) {
                $f2 = [
                    'sales_cek'            => $userId,
                    'sales_cek_tgl'        => date('Y-m-d'),
                    'keterangan_cek_sales' => $kcp,
                    'hasil_cek_t_s'        => $hct,
                    'hasil_cek_f_s'        => $hcf,
                    'hasil_cek_v_s'        => $hcv,
                ];

                $this->repo->upsertChecklistWork($idKavling, (int) $s->id_subitem, $f2);
            }
        }

        $this->repo->updateKavling($idKavling, ['is_checked' => $isChecked]);

        $this->db->transComplete();
        $success = $this->db->transStatus() !== false;

        return [
            'success'  => $success,
            'messages' => $success ? 'Successfully updated' : 'Terjadi Kesalahan',
        ];
    }

    public function deleteFoto(int $id, int $userId): array
    {
        $file = $this->repo->getFileById($id);
        if (!$file) {
            return ['success' => false, 'messages' => 'File tidak ditemukan'];
        }

        if ($this->fileService->moveFotoToTrash($file)) {
            $deleted = $this->repo->deleteFileById($id);
            if ($deleted && $this->repo->hasProduksiChangeHistoryTable()) {
                $this->repo->insertProduksiChangeHistory([
                    'id_kavling'  => (int) $file->id_kavling,
                    'id_produksi' => null,
                    'action'      => 'delete_file',
                    'summary'     => 'File/foto produksi dihapus',
                    'old_data'    => null,
                    'new_data'    => null,
                    'files'       => json_encode([[
                        'kategori'        => $file->kategori ?? null,
                        'file_name'       => $file->file_name ?? null,
                        'file_keterangan' => $file->file_keterangan ?? null,
                    ]]),
                    'add_by'      => $userId,
                    'created_at'  => date('Y-m-d H:i:s'),
                ]);
            }
            return [
                'success'  => $deleted,
                'messages' => $deleted ? 'File berhasil dipindahkan ke folder trash' : 'Terjadi kesalahan saat menghapus data file dari database',
            ];
        }

        return ['success' => false, 'messages' => 'Terjadi kesalahan saat memindahkan file ke folder trash'];
    }

    public function addJalan(array $postVars, int $userId): array
    {
        $idJalan = (int) ($postVars['id_jalan'] ?? 0);
        $points  = trim((string) ($postVars['points'] ?? ''));
        $pointList = array_filter(array_map('trim', explode(',', $points)), static fn ($p) => $p !== '');

        if ($idJalan <= 0) {
            return ['success' => false, 'messages' => 'Blok/Jalan harus diisi'];
        }

        if (count($pointList) < 6 || count($pointList) % 2 !== 0) {
            return ['success' => false, 'messages' => 'Seleksi manual minimal 3 titik'];
        }

        $progres = $postVars['f_progres_jalan'] ?? null;
        $progres = ($progres === null || $progres === '') ? 0 : max(0, min(100, (int) $progres));

        $now = date('Y-m-d H:i:s');
        $fields = [
            'id_jalan'             => $idJalan,
            'tipe'                 => 'jalan',
            'scope'                => 'produksi',
            'points'               => implode(',', $pointList),
            'progres'              => $progres,
            'produksi_luas'        => $postVars['f_produksi_luas'] ?? null,
            'produksi_keterangan'  => $postVars['f_produksi_keterangan'] ?? null,
            'produksi_add_by'      => $userId,
            'produksi_created_at'  => $now,
            'produksi_edit_by'     => $userId,
            'produksi_updated_at'  => $now,
        ];

        $success = $this->repo->createOther($fields);

        return [
            'success'  => $success,
            'messages' => $success ? 'Jalan produksi berhasil ditambahkan' : 'Jalan produksi gagal ditambahkan',
        ];
    }

    public function editOthers(int $id, array $postVars, array $uploadedFiles, int $userId): array
    {
        $other = $this->repo->getOtherById($id);
        if (!$other) {
            return ['success' => false, 'messages' => 'Data jalan tidak ditemukan'];
        }

        $progres = $postVars['f_progres_jalan'] ?? null;
        $progres = ($progres === null || $progres === '') ? 0 : max(0, min(100, (int) $progres));
        $now     = date('Y-m-d H:i:s');
        $foto    = '';
        $isJalan = ($other->tipe ?? '') === 'jalan';

        if ($isJalan && !$this->repo->hasJalanProgressHistoryTable()) {
            return ['success' => false, 'messages' => 'Tabel history progres jalan belum tersedia. Jalankan migration terlebih dahulu.'];
        }

        if ($isJalan) {
            $files = $uploadedFiles['produksi_jalan_foto'] ?? [];
            if (!is_array($files)) {
                $files = [$files];
            }

            $paths = [];
            $allowedMime = ['image/jpg', 'image/jpeg', 'image/gif', 'image/png', 'image/webp'];

            foreach ($files as $img) {
                if (!$img instanceof UploadedFile || $img->getError() === UPLOAD_ERR_NO_FILE) {
                    continue;
                }
                if (!$img->isValid()) {
                    return ['success' => false, 'messages' => 'Foto kondisi jalan tidak valid'];
                }
                if (!in_array($img->getMimeType(), $allowedMime, true)) {
                    return ['success' => false, 'messages' => 'Foto kondisi jalan harus berupa gambar'];
                }
                if ($img->getSize() > 12000 * 1024) {
                    return ['success' => false, 'messages' => 'Ukuran foto kondisi jalan maksimal 12MB'];
                }
                $paths[] = $this->fileAccessService->storeAs(
                    $img,
                    'uploads/produksi_jalan_progress/' . date('Ymd') . '/',
                    $img->getRandomName()
                );
            }

            $foto = implode(';', $paths);
            if ($foto !== '') {
                $foto .= ';';
            }
        }

        $fields = [
            'progres'             => $progres,
            'produksi_luas'       => $postVars['f_produksi_luas'] ?? null,
            'produksi_keterangan' => $postVars['f_produksi_keterangan'] ?? null,
            'produksi_edit_by'    => $userId,
            'produksi_updated_at' => $now,
        ];

        $points = $postVars['points'] ?? null;
        if ($points !== null && $points !== '') {
            $fields['points'] = $points;
        }

        $this->db->transStart();
        $this->repo->updateOthers($id, $fields);

        if ($isJalan) {
            $this->repo->insertJalanProgressHistory([
                'id_others'       => $id,
                'progres'         => $progres,
                'produksi_luas'   => $fields['produksi_luas'],
                'keterangan'      => $fields['produksi_keterangan'],
                'foto'            => $foto,
                'add_by'          => $userId,
                'created_at'      => $now,
            ]);
        }

        $this->db->transComplete();
        $success = $this->db->transStatus() !== false;

        return [
            'success'  => $success,
            'messages' => $success ? 'Data berhasil diperbaharui' : 'Data gagal diperbaharui!',
        ];
    }

    public function saveSlf(array $postVars, int $userId): bool
    {
        $idKavling = is_array($postVars['id_kavling'] ?? null)
            ? implode(',', $postVars['id_kavling'])
            : (string) ($postVars['id_kavling'] ?? '');

        $data = [
            'no_slf'                       => $postVars['no_slf'] ?? null,
            'tanggal_slf'                  => $postVars['tanggal_slf'] ?? null,
            'penanggungjawab'              => $postVars['penanggungjawab'] ?? null,
            'id_proyek'                    => $postVars['id_proyek'] ?? null,
            'fungsi_utama'                 => $postVars['fungsi_utama'] ?? null,
            'fungsi_tambahan'              => $postVars['fungsi_tambahan'] ?? null,
            'jenis_bangunan'               => $postVars['jenis_bangunan'] ?? null,
            'nama_bangunan'                => $postVars['nama_bangunan'] ?? null,
            'nomor_pendaftaran_bangunan'   => $postVars['nomor_pendaftaran_bangunan'] ?? null,
            'penerbitan_slf_no'            => $postVars['penerbitan_slf_no'] ?? null,
            'penerbitan_slf_tgl'           => $postVars['penerbitan_slf_tgl'] ?? null,
            'perpanjangan_slf_ke'          => $postVars['perpanjangan_slf_ke'] ?? null,
            'persyaratan_administrasi'     => $postVars['persyaratan_administrasi'] ?? null,
            'persyaratan_fungsi_bangunan'  => $postVars['persyaratan_fungsi_bangunan'] ?? null,
            'persyaratan_peruntukan'       => $postVars['persyaratan_peruntukan'] ?? null,
            'persyaratan_tata_bangunan'    => $postVars['persyaratan_tata_bangunan'] ?? null,
            'persyaratan_kelaikan'         => $postVars['persyaratan_kelaikan'] ?? null,
            'id_kavling'                   => $idKavling,
            'created_at'                   => date('Y-m-d H:i:s'),
            'add_by'                       => $userId,
        ];

        return $this->repo->insertSlf($data);
    }

    public function deleteSlf(int $id): bool
    {
        return $this->repo->deleteSlfById($id);
    }

    protected function buildProduksiData(array $var, int $userId): array
    {
        $f = [];

        foreach ([
            'id_produksi', 'st_0', 'st_25', 'st_50', 'st_75', 'st_100',
            'bp', 'slo', 'lpa', 'lpa_tanggal', 'st_jalan', 'st_saluran', 'st_air',
            'air_jenis', 'listrik_jenis', 'progres_bangunan',
            'listrik_pln', 'listrik_disediakan_no', 'listrik_disediakan_tanggal',
            'air_deskripsi_unit', 'air_pdam_no',
        ] as $field) {
            $f[$field] = $var[$field] ?? null;
        }

        $f['keterangan'] = $var['produksi_keterangan'] ?? null;

        $tglBangun     = $var['tanggal_pembangunan'] ?? '';
        $tglBangunOld  = $var['tanggal_pembangunan_old'] ?? '';
        $tglRencana    = $var['tanggal_rencana_selesai_pembangunan'] ?? '';
        $tglRencanaOld = $var['tanggal_rencana_selesai_pembangunan_old'] ?? '';
        $tglSelesai    = $var['tanggal_selesai_pembangunan'] ?? '';
        $tglSelesaiOld = $var['tanggal_selesai_pembangunan_old'] ?? '';

        if (($tglBangun && $tglBangunOld === '') || ($tglRencana && $tglRencanaOld === '')) {
            $f['tanggal_pembangunan_oleh'] = $userId;
            $f['tanggal_pembangunan_pada'] = date('Y-m-d H:i:s');
        } elseif (($tglBangunOld !== '' && $tglBangun !== $tglBangunOld) || ($tglRencanaOld !== '' && $tglRencana !== $tglRencanaOld)) {
            $f['tanggal_pembangunan_diubah_oleh'] = $userId;
            $f['tanggal_pembangunan_diubah_pada'] = date('Y-m-d H:i:s');
        }

        if ($tglSelesai && $tglSelesaiOld === '') {
            $f['tanggal_selesai_pembangunan_oleh'] = $userId;
            $f['tanggal_selesai_pembangunan_pada'] = date('Y-m-d H:i:s');
        } elseif ($tglSelesaiOld !== '' && $tglSelesai !== $tglSelesaiOld) {
            $f['tanggal_selesai_pembangunan_diubah_oleh'] = $userId;
            $f['tanggal_selesai_pembangunan_diubah_pada'] = date('Y-m-d H:i:s');
        }

        if ($tglBangun)  $f['tanggal_pembangunan'] = $tglBangun;
        if ($tglSelesai) $f['tanggal_selesai_pembangunan'] = $tglSelesai ?: null;
        if ($tglRencana) $f['tanggal_rencana_selesai_pembangunan'] = $tglRencana;

        return $f;
    }

    protected function processChecklists(int $idKavling, array $var, int $userId): void
    {
        $subitems = $this->repo->getAllSubitemIds();

        foreach ($subitems as $s) {
            $hct = isset($var['hasil_cek_t'][$s->id_subitem]) ? 1 : 0;
            $hcf = isset($var['hasil_cek_f'][$s->id_subitem]) ? 1 : 0;
            $hcv = isset($var['hasil_cek_v'][$s->id_subitem]) ? 1 : 0;

            if ($hct === 0 && $hcf === 0 && $hcv === 0) {
                continue;
            }

            $row = [
                'produksi_cek'            => $userId,
                'produksi_cek_tgl'        => date('Y-m-d'),
                'keterangan_cek_produksi' => $var['keterangan_cek_produksi'][$s->id_subitem] ?? null,
                'hasil_cek_t'             => $hct,
                'hasil_cek_f'             => $hcf,
                'hasil_cek_v'             => $hcv,
            ];

            $this->repo->upsertChecklistWork($idKavling, (int) $s->id_subitem, $row);
        }
    }

    protected function buildHistoryPayload(array $oldProduksi, array $newProduksi, array $var, array $fotoRows): array
    {
        $watchedFields = [
            'progres_bangunan',
            'st_0',
            'st_25',
            'st_50',
            'st_75',
            'st_100',
            'slo',
            'bp',
            'lpa',
            'lpa_tanggal',
            'st_jalan',
            'st_saluran',
            'st_air',
            'air_jenis',
            'listrik_jenis',
            'listrik_pln',
            'listrik_disediakan_no',
            'listrik_disediakan_tanggal',
            'air_deskripsi_unit',
            'air_pdam_no',
            'keterangan',
            'tanggal_pembangunan',
            'tanggal_rencana_selesai_pembangunan',
            'tanggal_selesai_pembangunan',
        ];

        $oldData = [];
        $newData = [];
        foreach ($watchedFields as $field) {
            $oldValue = $oldProduksi[$field] ?? null;
            $newValue = $newProduksi[$field] ?? null;
            if ((string) $oldValue !== (string) $newValue) {
                $oldData[$field] = $oldValue;
                $newData[$field] = $newValue;
            }
        }

        $fileSummary = array_map(static function (array $row) {
            return [
                'kategori'        => $row['kategori'] ?? null,
                'file_name'       => $row['file_name'] ?? null,
                'tgl_capture'     => $row['tgl_capture'] ?? null,
                'file_keterangan' => $row['file_keterangan'] ?? null,
                'foto_lat'        => $row['foto_lat'] ?? null,
                'foto_lng'        => $row['foto_lng'] ?? null,
            ];
        }, $fotoRows);

        $summaryParts = [];
        if (!empty($newData)) {
            $summaryParts[] = count($newData) . ' field diperbarui';
        }
        if (!empty($fotoRows)) {
            $summaryParts[] = count($fotoRows) . ' file/foto diunggah';
        }

        return [
            'summary'  => !empty($summaryParts) ? implode(', ', $summaryParts) : 'Data produksi disimpan',
            'old_data' => $oldData,
            'new_data' => $newData,
            'files'    => $fileSummary,
        ];
    }

    protected function shouldWriteHistory(array $payload): bool
    {
        return !empty($payload['old_data']) || !empty($payload['new_data']) || !empty($payload['files']);
    }
}

<?php

namespace App\Services\SalesExternal;

use App\Enums\NotificationEvent;
use App\Models\FeeSalesModel;
use App\Models\FeeSalesPengajuanItemModel;
use App\Models\FeeSalesPengajuanModel;
use App\Models\FeeSalesTerminModel;
use App\Models\SalesHistoryModel;
use App\Models\SalesModel;
use App\Models\UserSignatureProfileModel;
use App\Repositories\SalesExternal\SalesExternalRepository;
use App\Services\Bpb\BpbFileService;
use App\Services\HistoryService;
use App\Services\Mkdt\MkdtPricingService;
use App\Services\NotifikasiService;
use App\Services\StorageService;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\Files\UploadedFile;
use Config\Database;
use Myth\Auth\Password;
use RuntimeException;

class SalesExternalService
{
    protected BaseConnection $db;
    protected SalesModel $salesModel;
    protected SalesHistoryModel $salesHistoryModel;
    protected FeeSalesModel $feeSalesModel;
    protected FeeSalesTerminModel $feeSalesTerminModel;
    protected FeeSalesPengajuanModel $pengajuanModel;
    protected FeeSalesPengajuanItemModel $pengajuanItemModel;
    protected SalesExternalRepository $salesRepo;
    protected MkdtPricingService $mkdtPricingService;
    protected StorageService $storageService;
    protected HistoryService $historyService;
    protected NotifikasiService $notifikasiService;

    public function __construct(?NotifikasiService $notifikasiService = null)
    {
        $this->db                  = Database::connect();
        $this->salesModel          = new SalesModel();
        $this->salesHistoryModel   = new SalesHistoryModel();
        $this->feeSalesModel       = new FeeSalesModel();
        $this->feeSalesTerminModel = new FeeSalesTerminModel();
        $this->pengajuanModel      = new FeeSalesPengajuanModel();
        $this->pengajuanItemModel  = new FeeSalesPengajuanItemModel();
        $this->salesRepo           = new SalesExternalRepository($this->db);
        $this->mkdtPricingService  = new MkdtPricingService($this->db);
        $this->storageService      = new StorageService();
        $this->historyService      = new HistoryService();
        $this->notifikasiService   = $notifikasiService ?? new NotifikasiService();
    }

    /**
     * Simpan / Perbarui data Master Sales External beserta riwayat perubahannya
     */
    public function saveMasterSales(array $data, ?int $userId, ?UploadedFile $filePks = null): array
    {
        $id = ! empty($data['id']) ? (int) $data['id'] : null;

        $saveData = [
            'nama'            => trim($data['nama'] ?? ''),
            'kontak'          => trim($data['kontak'] ?? ''),
            'bank'            => trim($data['bank'] ?? ''),
            'no_rek'          => trim($data['no_rek'] ?? ''),
            'proyek'          => ! empty($data['proyek']) ? (is_array($data['proyek']) ? json_encode($data['proyek']) : $data['proyek']) : null,
            'fee_subsidi'     => $this->cleanNumeric($data['fee_subsidi'] ?? 0),
            'fee_komersil'    => $this->cleanNumeric($data['fee_komersil'] ?? 0),
            'tanggal_pks'     => ! empty($data['tanggal_pks']) ? $data['tanggal_pks'] : null,
            'tanggal_exp_pks' => ! empty($data['tanggal_exp_pks']) ? $data['tanggal_exp_pks'] : null,
            'status'          => in_array($data['status'] ?? '', ['active', 'inactive'], true) ? $data['status'] : 'active',
            'has_pks'         => isset($data['has_pks']) ? (int) $data['has_pks'] : 0,
            'catatan'         => trim($data['catatan'] ?? ''),
        ];

        if ($saveData['nama'] === '') {
            throw new RuntimeException('Nama sales wajib diisi.');
        }

        $this->db->transStart();
        try {
            $oldSales = $id ? $this->salesRepo->getSalesById($id) : null;

            if ($filePks && $filePks->isValid() && ! $filePks->hasMoved()) {
                $storedPath = $this->storageService->store($filePks, 'uploads/sales/pks/' . date('Ymd'));
                $saveData['lampiran_pks'] = $storedPath;
            }

            if ($id) {
                $saveData['updated_by'] = $userId;
                $this->salesModel->update($id, $saveData);

                // Cek perubahan tarif fee atau tanggal PKS untuk dicatat di sales_history
                $isFeeChanged = (
                    (float) ($oldSales->fee_subsidi ?? 0) !== (float) $saveData['fee_subsidi'] ||
                    (float) ($oldSales->fee_komersil ?? 0) !== (float) $saveData['fee_komersil'] ||
                    ($oldSales->tanggal_pks ?? null) !== $saveData['tanggal_pks'] ||
                    ($oldSales->tanggal_exp_pks ?? null) !== $saveData['tanggal_exp_pks'] ||
                    ! empty($saveData['lampiran_pks'])
                );

                if ($isFeeChanged) {
                    $this->salesHistoryModel->insert([
                        'id_sales'             => $id,
                        'fee_subsidi_lama'     => $oldSales->fee_subsidi ?? null,
                        'fee_subsidi_baru'     => $saveData['fee_subsidi'],
                        'fee_komersil_lama'    => $oldSales->fee_komersil ?? null,
                        'fee_komersil_baru'    => $saveData['fee_komersil'],
                        'tanggal_pks_lama'     => $oldSales->tanggal_pks ?? null,
                        'tanggal_pks_baru'     => $saveData['tanggal_pks'],
                        'tanggal_exp_pks_lama' => $oldSales->tanggal_exp_pks ?? null,
                        'tanggal_exp_pks_baru' => $saveData['tanggal_exp_pks'],
                        'lampiran_pks_lama'    => $oldSales->lampiran_pks ?? null,
                        'lampiran_pks_baru'    => $saveData['lampiran_pks'] ?? ($oldSales->lampiran_pks ?? null),
                        'catatan'              => 'Pembaruan data PKS / tarif komisi',
                        'created_by'           => $userId,
                        'created_at'           => date('Y-m-d H:i:s'),
                    ]);
                }
                $salesId = $id;
            } else {
                $saveData['created_by'] = $userId;
                $saveData['updated_by'] = $userId;
                $salesId = $this->salesModel->insert($saveData);

                // Catat log histori perdana
                $this->salesHistoryModel->insert([
                    'id_sales'          => $salesId,
                    'fee_subsidi_baru'  => $saveData['fee_subsidi'],
                    'fee_komersil_baru' => $saveData['fee_komersil'],
                    'tanggal_pks_baru'  => $saveData['tanggal_pks'],
                    'lampiran_pks_baru' => $saveData['lampiran_pks'] ?? null,
                    'catatan'           => 'Pendaftaran Master Sales baru',
                    'created_by'        => $userId,
                    'created_at'        => date('Y-m-d H:i:s'),
                ]);
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new RuntimeException('Gagal menyimpan data master sales ke database.');
            }

            return [
                'success' => true,
                'id'      => $salesId,
                'message' => 'Data master sales berhasil disimpan.',
            ];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    /**
     * Simpan / Perbarui Header Fee Sales dan Bangkitkan Termin Pencairan
     */
    public function saveFeeHeader(array $data, int|bool|null $userId = null, bool $sendNotification = true): array
    {
        if (is_bool($userId)) {
            $generateTermin = $userId;
            $userId         = function_exists('user_id') ? (int) user_id() : 0;
        } else {
            $generateTermin = true;
        }

        $idMkdt = (int) ($data['id_mkdt'] ?? 0);
        if ($idMkdt <= 0) {
            throw new RuntimeException('ID MKDT tidak valid.');
        }

        $detail = $this->salesRepo->getFeeDetailByMkdt($idMkdt);
        if (! $detail) {
            throw new RuntimeException('Data transaksi akad tidak ditemukan.');
        }

        $rawHargaAjbInput = $this->cleanNumeric($data['harga_net_ajb'] ?? 0);
        $oldHargaNetAjb   = (float) ($detail->harga_net_ajb ?? 0);
        $hargaAjb         = $rawHargaAjbInput;
        if ($hargaAjb <= 0) {
            // Fallback ke harga_jual_net jika harga AJB belum diisi
            $hargaAjb = (float) ($detail->harga_net_ajb > 0 ? $detail->harga_net_ajb : $detail->harga_jual_net);
        }

        // Kunci nominal harga jual net AJB jika sudah ada termin yang cair
        $hasCair = $this->salesRepo->hasCairTerminByMkdt($idMkdt);
        if ($hasCair) {
            $savedPrice = (float) ($detail->harga_net_ajb > 0 ? $detail->harga_net_ajb : $detail->harga_jual_net);
            if ($rawHargaAjbInput > 0 && abs($rawHargaAjbInput - $savedPrice) > 0.01) {
                throw new RuntimeException('Harga Jual Net AJB tidak dapat diubah karena sudah ada pengajuan/termin yang cair.');
            }
            if ($savedPrice > 0) {
                $hargaAjb = $savedPrice;
            }
        }

        $isHargaChanged = ($rawHargaAjbInput > 0 && abs($hargaAjb - $oldHargaNetAjb) > 0.01);

        $idFeeSales = ! empty($data['id_fee_sales']) ? (int) $data['id_fee_sales'] : 0;
        $idSales    = ! empty($data['id_sales']) ? (int) $data['id_sales'] : 0;
        $salesNama  = trim($data['sales_nama'] ?? '');

        // Jika nama sales kosong tapi ada id_sales, ambil nama dari master sales
        if ($salesNama === '' && $idSales > 0) {
            $salesRow = $this->salesRepo->getSalesById($idSales);
            if ($salesRow) {
                $salesNama = $salesRow->nama;
            }
        }

        // Fallback nama sales dari data konsumen jika benar-benar kosong
        if ($salesNama === '') {
            $salesNama = trim($detail->master_sales_nama ?? $detail->sales_konsumen ?? '');
        }

        if ($salesNama === '') {
            throw new RuntimeException('Nama sales penerima fee wajib diisi.');
        }

        // Resolusi id_sales: jika belum ada id_sales atau diketik nama baru,
        // cek apakah sudah ada di master sales. Jika belum ada, auto-insert dengan has_pks=0 dan default komisi 1.00%
        if ($idSales <= 0) {
            $existingMaster = $this->salesRepo->findActiveSalesByNama($salesNama);

            if ($existingMaster) {
                $idSales = (int) $existingMaster->id;
            } else {
                $idSales = $this->salesModel->insert([
                    'nama'         => $salesNama,
                    'has_pks'      => 0, // non-PKS
                    'fee_subsidi'  => 1.00,
                    'fee_komersil' => 1.00,
                    'status'       => 'active',
                    'catatan'      => 'Didaftarkan otomatis saat penambahan fee sales kavling',
                    'created_by'   => $userId,
                    'updated_by'   => $userId,
                ]);
            }
        }

        $persenFee = $this->cleanNumeric($data['persentase_fee'] ?? 0);
        if ($persenFee <= 0) {
            $masterSales = $this->salesRepo->getSalesById($idSales);
            if ($masterSales) {
                $persenFee = (float) ((int) $detail->is_subsidi === 1 ? ($masterSales->fee_subsidi ?? 1.50) : ($masterSales->fee_komersil ?? 2.00));
            } else {
                $persenFee = (float) ((int) $detail->is_subsidi === 1 ? 1.50 : 2.00);
            }
        }

        $isManual = ! empty($data['total_fee_manual']) && (int) $data['total_fee_manual'] === 1;
        if ($isManual && isset($data['total_fee'])) {
            $totalFee = $this->cleanNumeric($data['total_fee']);
        } else {
            $totalFee = round(($persenFee / 100) * $hargaAjb, 2);
        }

        $defaultSkema = (int) ($detail->akad_indent ?? 0) === 1 ? '100' : '30_60_10';
        $skemaTermin  = ! empty($data['skema_termin']) && in_array($data['skema_termin'], ['100', '30_60_10', 'custom'], true)
            ? $data['skema_termin']
            : $defaultSkema;

        $salesKet = trim($data['sales_keterangan'] ?? '');
        $totalGeneratedTerminCount = 0;

        $this->db->transStart();
        try {
            // Update harga_net_ajb pada tabel mkdt melalui kontrak MKDT (seragam untuk unit kavling ini)
            $this->mkdtPricingService->updateHargaNetAjb($idMkdt, $hargaAjb);

            // Cek entri fee_sales yang ditargetkan
            $existingFee = $idFeeSales > 0
                ? $this->salesRepo->getFeeSalesByIdAndMkdt($idFeeSales, $idMkdt)
                : $this->salesRepo->getFeeSalesByMkdtAndSales($idMkdt, $idSales);

            $headerData = [
                'id_mkdt'          => $idMkdt,
                'id_sales'         => $idSales,
                'sales_nama'       => $salesNama,
                'sales_keterangan' => $salesKet,
                'harga_net_ajb'    => $hargaAjb,
                'persentase_fee'   => $persenFee,
                'total_fee'        => $totalFee,
                'skema_termin'     => $skemaTermin,
                'status'           => $existingFee ? $existingFee->status : 'draft',
                'catatan'          => trim($data['catatan'] ?? ($existingFee->catatan ?? '')),
                'updated_by'       => $userId,
            ];

            if ($existingFee) {
                $idFeeSales = (int) $existingFee->id;

                // Kunci konfigurasi jika ada termin yang sudah diajukan atau sudah cair
                $activeCount = $this->salesRepo->countActiveTerminByFeeSalesId($idFeeSales);

                if ($activeCount > 0) {
                    throw new RuntimeException("Konfigurasi fee untuk sales {$salesNama} tidak dapat diubah karena termin sudah dalam proses pengajuan atau sudah dicairkan.");
                }

                $this->feeSalesModel->update($idFeeSales, $headerData);
            } else {
                $headerData['created_by'] = $userId;
                $idFeeSales = $this->feeSalesModel->insert($headerData);
            }

            if ($generateTermin) {
                // Hapus termin lama yang belum diajukan
                $this->salesRepo->deleteBelumDiajukanTerminByFeeSalesId($idFeeSales);

                // Buat termin baru sesuai skema
                if ($skemaTermin === '100') {
                    $this->feeSalesTerminModel->insert([
                        'id_fee_sales' => $idFeeSales,
                        'termin_ke'    => 1,
                        'persen'       => 100.00,
                        'nominal'      => $totalFee,
                        'status'       => 'belum_diajukan',
                        'created_by'   => $userId,
                        'updated_by'   => $userId,
                    ]);
                    $totalGeneratedTerminCount = 1;
                } elseif ($skemaTermin === '30_60_10') {
                    $nom1 = round(0.30 * $totalFee, 2);
                    $nom2 = round(0.60 * $totalFee, 2);
                    $nom3 = round($totalFee - $nom1 - $nom2, 2);

                    $terminRows = [
                        ['id_fee_sales' => $idFeeSales, 'termin_ke' => 1, 'persen' => 30.00, 'nominal' => $nom1, 'status' => 'belum_diajukan', 'created_by' => $userId, 'updated_by' => $userId],
                        ['id_fee_sales' => $idFeeSales, 'termin_ke' => 2, 'persen' => 60.00, 'nominal' => $nom2, 'status' => 'belum_diajukan', 'created_by' => $userId, 'updated_by' => $userId],
                        ['id_fee_sales' => $idFeeSales, 'termin_ke' => 3, 'persen' => 10.00, 'nominal' => $nom3, 'status' => 'belum_diajukan', 'created_by' => $userId, 'updated_by' => $userId],
                    ];
                    $this->feeSalesTerminModel->insertBatch($terminRows);
                    $totalGeneratedTerminCount = 3;
                }
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new RuntimeException('Gagal memperbarui kalkulasi fee sales.');
            }

            if ($sendNotification) {
                $unitLabel  = $this->formatKavlingLabel($detail);
                $idProyek   = (int) ($detail->id_proyek ?? 0);
                $idKavling  = (int) ($detail->id_kavling ?? 0);
                $idKonsumen = (int) ($detail->id_konsumen ?? 0);
                $actionUrl  = 'fee-sales?open_mkdt=' . $idMkdt;

                if ($isHargaChanged) {
                    $formattedHarga = number_format($hargaAjb, 0, ',', '.');
                    $this->sendFeeSalesNotification(
                        NotificationEvent::FEE_SALES_HARGA_NET_UPDATED,
                        'Update Harga Net AJB Fee Sales',
                        "Harga Net AJB untuk {$unitLabel} diperbarui menjadi Rp {$formattedHarga}.",
                        $idProyek,
                        $idKavling,
                        $idKonsumen,
                        $actionUrl,
                        (int) $idFeeSales,
                        $userId
                    );
                }

                if ($generateTermin && $totalGeneratedTerminCount > 0) {
                    $this->sendFeeSalesNotification(
                        NotificationEvent::FEE_SALES_TERMIN_GENERATED,
                        'Generate Termin Fee Sales',
                        "Berhasil men-generate {$totalGeneratedTerminCount} termin fee sales ({$salesNama}) untuk {$unitLabel}.",
                        $idProyek,
                        $idKavling,
                        $idKonsumen,
                        $actionUrl,
                        (int) $idFeeSales,
                        $userId
                    );
                }
            }

            return [
                'success'      => true,
                'id_fee_sales' => $idFeeSales,
                'sales_nama'   => $salesNama,
                'total_fee'    => $totalFee,
                'message'      => "Kalkulasi fee sales ({$salesNama}) dan termin berhasil disimpan.",
            ];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    /**
     * Hapus satu entri fee sales beserta seluruh terminnya (hanya jika belum diajukan/cair)
     */
    public function deleteFeeEntry(int $idFeeSales, ?int $userId): array
    {
        $fee = $this->salesRepo->getFeeSalesById($idFeeSales);
        if (! $fee) {
            throw new RuntimeException('Data fee sales tidak ditemukan.');
        }

        // Cek apakah ada termin yang sudah diajukan atau sudah cair
        $activeCount = $this->salesRepo->countActiveTerminByFeeSalesId($idFeeSales);

        if ($activeCount > 0) {
            throw new RuntimeException('Fee sales ini tidak dapat dihapus karena termin sudah dalam proses pengajuan atau sudah dicairkan.');
        }

        $this->db->transStart();
        try {
            // Hapus termin yang belum diajukan
            $this->salesRepo->deleteTerminByFeeSalesId($idFeeSales);

            // Hapus header fee_sales
            $this->salesRepo->deleteFeeSalesById($idFeeSales);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new RuntimeException('Gagal menghapus entri fee sales.');
            }

            return [
                'success' => true,
                'message' => 'Entri fee sales ' . $fee->sales_nama . ' berhasil dihapus.',
            ];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    /**
     * Pengajuan SPP untuk satu termin fee sales
     */
    public function submitPengajuanSpp(int $idTermin, array $data, ?int $userId, ?UploadedFile $fileSpp = null): array
    {
        $termin = $this->salesRepo->getTerminById($idTermin);
        if (! $termin) {
            throw new RuntimeException('Data termin tidak ditemukan.');
        }

        if ($termin->status === 'sudah_cair') {
            throw new RuntimeException('Termin yang sudah cair tidak dapat diedit pengajuannya.');
        }

        $tglSpp   = ! empty($data['tanggal_pengajuan_spp']) ? $data['tanggal_pengajuan_spp'] : date('Y-m-d');
        $nomorSpp = trim($data['nomor_spp'] ?? '');
        $ket      = trim($data['keterangan'] ?? '');

        $this->db->transStart();
        try {
            $updateData = [
                'status'                => 'pengajuan_spp',
                'nomor_spp'            => $nomorSpp,
                'tanggal_pengajuan_spp' => $tglSpp,
                'keterangan'           => $ket,
                'updated_by'           => $userId,
            ];

            if ($fileSpp && $fileSpp->isValid() && ! $fileSpp->hasMoved()) {
                $storedPath = $this->storageService->store($fileSpp, 'uploads/sales/spp/' . date('Ymd'));
                $updateData['lampiran_spp'] = $storedPath;
            }

            $this->feeSalesTerminModel->update($idTermin, $updateData);

            // Update status parent fee_sales
            $this->salesRepo->markFeeSalesProsesTerminIfNotSelesai((int) $termin->id_fee_sales, $userId);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new RuntimeException('Gagal menyimpan pengajuan SPP termin.');
            }

            $feeRow           = $this->salesRepo->getFeeSalesById((int) $termin->id_fee_sales);
            $idMkdt           = (int) ($feeRow->id_mkdt ?? 0);
            $detail           = $idMkdt > 0 ? $this->salesRepo->getFeeDetailByMkdt($idMkdt) : null;
            $unitLabel        = $this->formatKavlingLabel($detail);
            $formattedNominal = number_format((float) ($termin->nominal ?? 0), 0, ',', '.');
            $noSppLabel       = $nomorSpp !== '' ? " ({$nomorSpp})" : '';

            $this->sendFeeSalesNotification(
                NotificationEvent::FEE_SALES_PENGAJUAN_CREATED,
                'Pengajuan Fee Sales Baru',
                "Pengajuan SPP Termin {$termin->termin_ke}{$noSppLabel} untuk {$unitLabel} sebesar Rp {$formattedNominal} telah diajukan.",
                (int) ($detail->id_proyek ?? 0),
                (int) ($detail->id_kavling ?? 0),
                (int) ($detail->id_konsumen ?? 0),
                $idMkdt > 0 ? ('fee-sales?open_mkdt=' . $idMkdt) : 'fee-sales',
                (int) $idTermin,
                $userId
            );

            return [
                'success' => true,
                'message' => 'Pengajuan SPP Termin ke-' . $termin->termin_ke . ' berhasil dicatat.',
            ];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    /**
     * Konfirmasi Pencairan Dana untuk satu termin fee sales
     */
    public function confirmPencairan(int $idTermin, array $data, ?int $userId, ?UploadedFile $fileBukti = null): array
    {
        $termin = $this->salesRepo->getTerminById($idTermin);
        if (! $termin) {
            throw new RuntimeException('Data termin tidak ditemukan.');
        }

        $tglCair = ! empty($data['tanggal_cair']) ? $data['tanggal_cair'] : date('Y-m-d');
        $noCek   = trim($data['no_cek'] ?? '');
        $ket     = trim($data['keterangan'] ?? '');

        $this->db->transStart();
        try {
            $updateData = [
                'status'       => 'sudah_cair',
                'tanggal_cair' => $tglCair,
                'no_cek'       => $noCek ?: $termin->no_cek,
                'keterangan'   => $ket ?: $termin->keterangan,
                'updated_by'   => $userId,
            ];

            if ($fileBukti && $fileBukti->isValid() && ! $fileBukti->hasMoved()) {
                $storedPath = $this->storageService->store($fileBukti, 'uploads/sales/bukti_cair/' . date('Ymd'));
                $updateData['lampiran_bukti_cair'] = $storedPath;
            }

            $this->feeSalesTerminModel->update($idTermin, $updateData);

            // Cek apakah semua termin pada fee_sales ini sudah cair
            $this->syncFeeSalesStatusFromTermins((int) $termin->id_fee_sales, $userId);

            // Sinkronkan status pada tabel fee_sales_pengajuan jika termin ini masuk grup pengajuan
            $this->syncPengajuanStatusByTerminId($idTermin, $userId);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new RuntimeException('Gagal mengonfirmasi pencairan fee termin.');
            }

            $feeRow        = $this->salesRepo->getFeeSalesById((int) $termin->id_fee_sales);
            $idMkdt        = (int) ($feeRow->id_mkdt ?? 0);
            $detail        = $idMkdt > 0 ? $this->salesRepo->getFeeDetailByMkdt($idMkdt) : null;
            $pengajuanLink = $this->salesRepo->getLatestNonVoidPengajuanByTerminId($idTermin);

            $actionUrl = $pengajuanLink
                ? ('fee-sales/pengajuan/' . ($pengajuanLink->share_token ?: $pengajuanLink->id))
                : ($idMkdt > 0 ? ('fee-sales?open_mkdt=' . $idMkdt) : 'fee-sales');
            $unitLabel        = $this->formatKavlingLabel($detail);
            $formattedNominal = number_format((float) ($termin->nominal ?? 0), 0, ',', '.');

            $this->sendFeeSalesNotification(
                NotificationEvent::FEE_SALES_PENCAIRAN,
                'Pencairan Fee Sales',
                "Pencairan fee sales Termin {$termin->termin_ke} untuk {$unitLabel} sebesar Rp {$formattedNominal} telah dikonfirmasi.",
                (int) ($detail->id_proyek ?? 0),
                (int) ($detail->id_kavling ?? 0),
                (int) ($detail->id_konsumen ?? 0),
                $actionUrl,
                (int) $idTermin,
                $userId
            );

            return [
                'success' => true,
                'message' => 'Pencairan Termin ke-' . $termin->termin_ke . ' berhasil dikonfirmasi.',
            ];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    /**
     * Edit Pengajuan Pencairan untuk satu termin fee sales dengan pencatatan alasan perubahan
     */
    public function updatePengajuan(int $idTermin, array $data, ?int $userId, ?UploadedFile $fileSpp = null): array
    {
        $termin = $this->salesRepo->getTerminById($idTermin);
        if (! $termin) {
            throw new RuntimeException('Data termin tidak ditemukan.');
        }

        if ($termin->status !== 'pengajuan_spp') {
            throw new RuntimeException('Hanya pengajuan dengan status sedang diajukan yang dapat diedit.');
        }

        $alasan = trim($data['alasan_perubahan'] ?? '');
        if ($alasan === '') {
            throw new RuntimeException('Alasan perubahan pengajuan wajib diisi.');
        }

        $tglSpp   = ! empty($data['tanggal_pengajuan_spp']) ? $data['tanggal_pengajuan_spp'] : ($termin->tanggal_pengajuan_spp ?: date('Y-m-d'));
        $nomorSpp = trim($data['nomor_spp'] ?? ($termin->nomor_spp ?? ''));
        $ket      = trim($data['keterangan'] ?? ($termin->keterangan ?? ''));

        $this->db->transStart();
        try {
            $updateData = [
                'nomor_spp'            => $nomorSpp,
                'tanggal_pengajuan_spp' => $tglSpp,
                'keterangan'           => $ket,
                'updated_by'           => $userId,
            ];

            if ($fileSpp && $fileSpp->isValid() && ! $fileSpp->hasMoved()) {
                $storedPath = $this->storageService->store($fileSpp, 'uploads/sales/spp/' . date('Ymd'));
                $updateData['lampiran_spp'] = $storedPath;
            }

            $this->feeSalesTerminModel->update($idTermin, $updateData);

            // Ambil konteks kavling / proyek untuk audit log
            $feeHeader = $this->salesRepo->getFeeSalesContextForAudit((int) $termin->id_fee_sales);

            $this->historyService->log('fee_sales', [
                'reference_type' => 'fee_sales_termin',
                'reference_id'   => $idTermin,
                'id_kavling'     => (int) ($feeHeader->id_kavling ?? 0),
                'id_proyek'      => (int) ($feeHeader->id_proyek ?? 0),
                'action'         => 'edit_pengajuan',
                'summary'        => 'Edit Pengajuan Termin ' . $termin->termin_ke . '. Alasan: ' . $alasan,
                'old_data'       => (array) $termin,
                'new_data'       => array_merge((array) $termin, $updateData),
                'metadata'       => ['alasan' => $alasan],
                'add_by'         => $userId,
            ]);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new RuntimeException('Gagal menyimpan perubahan pengajuan.');
            }

            return [
                'success' => true,
                'message' => 'Perubahan pengajuan pencairan Termin ke-' . $termin->termin_ke . ' berhasil disimpan.',
            ];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    /**
     * Edit Realisasi Pencairan Dana untuk satu termin fee sales dengan pencatatan alasan perubahan
     */
    public function updatePencairan(int $idTermin, array $data, ?int $userId, ?UploadedFile $fileBukti = null): array
    {
        $termin = $this->salesRepo->getTerminById($idTermin);
        if (! $termin) {
            throw new RuntimeException('Data termin tidak ditemukan.');
        }

        if ($termin->status !== 'sudah_cair') {
            throw new RuntimeException('Hanya termin yang sudah cair yang dapat diedit data pencairannya.');
        }

        $alasan = trim($data['alasan_perubahan'] ?? '');
        if ($alasan === '') {
            throw new RuntimeException('Alasan perubahan pencairan wajib diisi.');
        }

        $tglCair = ! empty($data['tanggal_cair']) ? $data['tanggal_cair'] : ($termin->tanggal_cair ?: date('Y-m-d'));
        $noCek   = trim($data['no_cek'] ?? ($termin->no_cek ?? ''));
        $ket     = trim($data['keterangan'] ?? ($termin->keterangan ?? ''));

        $this->db->transStart();
        try {
            $updateData = [
                'tanggal_cair' => $tglCair,
                'no_cek'       => $noCek,
                'keterangan'   => $ket,
                'updated_by'   => $userId,
            ];

            if ($fileBukti && $fileBukti->isValid() && ! $fileBukti->hasMoved()) {
                $storedPath = $this->storageService->store($fileBukti, 'uploads/sales/bukti_cair/' . date('Ymd'));
                $updateData['lampiran_bukti_cair'] = $storedPath;
            }

            $this->feeSalesTerminModel->update($idTermin, $updateData);

            // Ambil konteks kavling / proyek untuk audit log
            $feeHeader = $this->salesRepo->getFeeSalesContextForAudit((int) $termin->id_fee_sales);

            $this->historyService->log('fee_sales', [
                'reference_type' => 'fee_sales_termin',
                'reference_id'   => $idTermin,
                'id_kavling'     => (int) ($feeHeader->id_kavling ?? 0),
                'id_proyek'      => (int) ($feeHeader->id_proyek ?? 0),
                'action'         => 'edit_pencairan',
                'summary'        => 'Edit Pencairan Dana Termin ' . $termin->termin_ke . '. Alasan: ' . $alasan,
                'old_data'       => (array) $termin,
                'new_data'       => array_merge((array) $termin, $updateData),
                'metadata'       => ['alasan' => $alasan],
                'add_by'         => $userId,
            ]);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new RuntimeException('Gagal menyimpan perubahan pencairan dana.');
            }

            return [
                'success' => true,
                'message' => 'Perubahan pencairan dana Termin ke-' . $termin->termin_ke . ' berhasil disimpan.',
            ];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    /**
     * Simpan file tanda tangan pengajuan (metode canvas base64 atau salin dari profil)
     */
    protected function storePengajuanSignature(int $idPengajuan, int $userId, string $method, string $canvasData): string
    {
        $fileService = new BpbFileService();
        $targetDir   = "fee_sales/pengajuan/{$idPengajuan}/signatures";

        if ($method === 'profile') {
            $profile = (new UserSignatureProfileModel())->where('user_id', $userId)->first();
            if (! $profile || empty($profile['signature_path'])) {
                throw new RuntimeException('Tanda tangan profil belum tersedia. Silakan gambar tanda tangan atau atur di profil.');
            }
            return $fileService->copyPrivate((string) $profile['signature_path'], $targetDir);
        }

        if ($method !== 'canvas') {
            throw new RuntimeException('Metode tanda tangan tidak valid.');
        }

        return $fileService->storeCanvas($canvasData, $targetDir);
    }

    /**
     * Buat Pengajuan Pencairan (Batch Submission)
     */
    public function createPengajuanGroup(array $data, ?int $userId, ?UploadedFile $fileSpp = null): array
    {
        $idProyek   = ! empty($data['id_proyek']) ? (int) $data['id_proyek'] : null;
        $tglSales   = trim((string) ($data['tanggal_pengajuan_sales'] ?? ''));
        if ($tglSales === '') {
            throw new RuntimeException('Tanggal pengajuan dari sales wajib diisi.');
        }
        $tglPeng    = trim((string) ($data['tanggal_pengajuan'] ?? ''));
        if ($tglPeng === '') {
            throw new RuntimeException('Tanggal pengajuan pencairan wajib diisi.');
        }

        $signMethod = (string) ($data['signature_method'] ?? 'canvas');
        $signData   = (string) ($data['signature_data'] ?? '');
        if ($signMethod === 'canvas' && trim($signData) === '') {
            throw new RuntimeException('Tanda tangan pengajuan wajib diisi.');
        }

        $noPeng     = trim($data['nomor_pengajuan'] ?? '');
        $catatan    = trim($data['catatan'] ?? '');
        $itemsInput = $data['items'] ?? [];

        if (empty($itemsInput) || ! is_array($itemsInput)) {
            throw new RuntimeException('Pilih setidaknya 1 kavling untuk diajukan.');
        }

        $kodeKeu = '';
        if (! $idProyek && ! empty($itemsInput[0]['id_mkdt'])) {
            $firstMkdt = (int) $itemsInput[0]['id_mkdt'];
            $proyekRow = $this->salesRepo->getProyekContextByMkdtId($firstMkdt);
            if ($proyekRow) {
                $idProyek = (int) $proyekRow->id_proyek;
                $kodeKeu  = strtoupper(trim((string) ($proyekRow->kode_keuangan ?? '')));
            }
        }

        if (empty($kodeKeu) && (int) $idProyek > 0) {
            $kodeKeu = strtoupper((string) ($this->salesRepo->getProyekKodeKeuangan((int) $idProyek) ?? ''));
        }
        if (empty($kodeKeu)) {
            $kodeKeu = 'GEN';
        }

        $rand4 = sprintf('%04X', random_int(0, 0xFFFF));
        $finalNoPeng = $noPeng !== ''
            ? $noPeng
            : ('PGJ/FEE/' . $kodeKeu . '/' . date('Ymd', strtotime($tglPeng)) . '/' . $rand4);

        $this->db->transStart();
        try {
            $lampiranSppPath = null;
            if ($fileSpp && $fileSpp->isValid() && ! $fileSpp->hasMoved()) {
                $lampiranSppPath = $this->storageService->store($fileSpp, 'uploads/sales/spp/' . date('Ymd'));
            }

            $shareToken = bin2hex(random_bytes(16));
            $idPengajuan = $this->pengajuanModel->insert([
                'id_proyek'               => $idProyek,
                'share_token'             => $shareToken,
                'nomor_pengajuan'         => $finalNoPeng,
                'tanggal_pengajuan'       => $tglPeng,
                'tanggal_pengajuan_sales' => $tglSales,
                'catatan'                 => $catatan,
                'lampiran_spp'            => $lampiranSppPath,
                'status'            => 'diajukan',
                'created_by'        => $userId,
                'updated_by'        => $userId,
            ]);

            $sigPath  = $this->storePengajuanSignature((int) $idPengajuan, (int) $userId, $signMethod, $signData);
            $userSnap = $this->salesRepo->userSnapshot((int) $userId);
            $this->pengajuanModel->update($idPengajuan, [
                'ttd_pembuat_by'     => $userId,
                'ttd_pembuat_nama'   => $userSnap['name'] ?? ('User #' . $userId),
                'ttd_pembuat_path'   => $sigPath,
                'ttd_pembuat_method' => $signMethod,
                'ttd_pembuat_at'     => date('Y-m-d H:i:s'),
            ]);

            $processedTerminIds    = [];
            $firstDetail           = null;
            $totalPengajuanNominal = 0.0;
            foreach ($itemsInput as $item) {
                $idMkdt = (int) ($item['id_mkdt'] ?? 0);
                if ($idMkdt <= 0) {
                    continue;
                }

                $idTermin     = (int) ($item['id_termin'] ?? 0);
                $idFeeSales   = (int) ($item['id_fee_sales'] ?? 0);
                $terminTarget = (int) ($item['termin_ke'] ?? 1); // 1, 2, 3

                // Cek detail fee_sales
                $detail = $this->salesRepo->getFeeDetailByMkdt($idMkdt);
                if (! $detail) {
                    continue;
                }

                // Jika belum ada fee_sales sama sekali, generate otomatis tanpa mengirim notifikasi terpisah
                if (empty($detail->fee_list) && empty($detail->id_fee_sales)) {
                    $autoSkema = ($terminTarget === 1 && ! empty($item['skema_100'])) ? '100' : '30_60_10';
                    $this->saveFeeHeader([
                        'id_mkdt'        => $idMkdt,
                        'skema_termin'   => $autoSkema,
                        'harga_net_ajb'  => $detail->harga_net_ajb > 0 ? $detail->harga_net_ajb : $detail->harga_jual_net,
                    ], $userId, false);

                    $detail = $this->salesRepo->getFeeDetailByMkdt($idMkdt);
                }

                // Cari termin yang ditargetkan
                $matchedTermin    = null;
                $targetFeeSalesId = $idFeeSales;

                if ($idTermin > 0) {
                    $matchedTermin = $this->salesRepo->getTerminById($idTermin);
                    if ($matchedTermin) {
                        $targetFeeSalesId = (int) $matchedTermin->id_fee_sales;
                    }
                }

                if (! $matchedTermin && ! empty($detail->fee_list)) {
                    foreach ($detail->fee_list as $feeEntry) {
                        if ($idFeeSales > 0 && (int) $feeEntry->id !== $idFeeSales) {
                            continue;
                        }
                        if (! empty($feeEntry->termin)) {
                            foreach ($feeEntry->termin as $t) {
                                if ((int) $t->termin_ke === $terminTarget) {
                                    $matchedTermin    = $t;
                                    $targetFeeSalesId = (int) $feeEntry->id;
                                    break 2;
                                }
                            }
                        }
                    }
                }

                // Fallback pencarian pada single fee
                if (! $matchedTermin && ! empty($detail->termin)) {
                    foreach ($detail->termin as $t) {
                        if ((int) $t->termin_ke === $terminTarget) {
                            $matchedTermin    = $t;
                            $targetFeeSalesId = (int) $detail->id_fee_sales;
                            break;
                        }
                    }
                }

                if (! $matchedTermin) {
                    throw new RuntimeException("Termin ke-{$terminTarget} untuk unit kavling {$detail->no_kavling} ({$detail->nama_konsumen}) tidak ditemukan.");
                }

                if (in_array((int) $matchedTermin->id, $processedTerminIds, true)) {
                    continue; // Lewati jika termin yang sama sudah diproses
                }
                $processedTerminIds[] = (int) $matchedTermin->id;
                if ($firstDetail === null) {
                    $firstDetail = $detail;
                }
                $totalPengajuanNominal += (float) ($matchedTermin->nominal ?? 0);

                if ($matchedTermin->status === 'sudah_cair') {
                    throw new RuntimeException("Termin ke-{$terminTarget} untuk unit kavling {$detail->no_kavling} sudah cair.");
                }

                if ($matchedTermin->status === 'pengajuan_spp') {
                    throw new RuntimeException("Termin ke-{$terminTarget} untuk unit kavling {$detail->no_kavling} sedang dalam proses pengajuan.");
                }

                if ($matchedTermin->status !== 'belum_diajukan') {
                    throw new RuntimeException("Termin ke-{$terminTarget} untuk unit kavling {$detail->no_kavling} tidak dapat diajukan (status: {$matchedTermin->status}).");
                }

                // Update termin menjadi pengajuan_spp jika belum
                $terminUpdate = [
                    'status'                => 'pengajuan_spp',
                    'tanggal_pengajuan_spp' => $tglPeng,
                    'nomor_spp'            => $finalNoPeng,
                    'updated_by'           => $userId,
                ];
                if ($lampiranSppPath && empty($matchedTermin->lampiran_spp)) {
                    $terminUpdate['lampiran_spp'] = $lampiranSppPath;
                }
                $this->feeSalesTerminModel->update($matchedTermin->id, $terminUpdate);

                // Update parent fee_sales status
                $this->salesRepo->markFeeSalesProsesTerminIfNotSelesai($targetFeeSalesId, $userId);

                // Simpan item pengajuan pivot
                $this->pengajuanItemModel->insert([
                    'id_pengajuan' => $idPengajuan,
                    'id_fee_sales' => $targetFeeSalesId,
                    'id_termin'    => $matchedTermin->id,
                    'termin_ke'    => $matchedTermin->termin_ke,
                    'nominal'      => $matchedTermin->nominal, // dari DB, bukan frontend
                    'created_at'   => date('Y-m-d H:i:s'),
                ]);
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new RuntimeException('Gagal membuat pengajuan pencairan.');
            }

            $notifProyek    = (int) ($idProyek ?: ($firstDetail->id_proyek ?? 0));
            $notifKavling   = (int) ($firstDetail->id_kavling ?? 0);
            $notifKonsumen  = (int) ($firstDetail->id_konsumen ?? 0);
            $itemCount      = count($processedTerminIds);
            $formattedTotal = number_format($totalPengajuanNominal, 0, ',', '.');

            $this->sendFeeSalesNotification(
                NotificationEvent::FEE_SALES_PENGAJUAN_CREATED,
                'Pengajuan Fee Sales Baru',
                "Pengajuan fee sales {$finalNoPeng} ({$itemCount} item, total Rp {$formattedTotal}) telah dibuat.",
                $notifProyek,
                $notifKavling,
                $notifKonsumen,
                'fee-sales/pengajuan/' . ($shareToken ?: $idPengajuan),
                (int) $idPengajuan,
                $userId
            );

            return [
                'success'      => true,
                'id_pengajuan' => $idPengajuan,
                'share_token'  => $shareToken,
                'message'      => 'Pengajuan Pencairan berhasil dibuat.',
            ];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    /**
     * Membatalkan (Void) Pengajuan Pencairan
     * Hanya dapat dilakukan sebelum ada termin yang cair
     */
    public function voidPengajuanGroup(int $idPengajuan, string $alasan, int $userId): array
    {
        $alasan = trim($alasan);
        if ($alasan === '') {
            throw new RuntimeException('Alasan void wajib diisi.');
        }

        $group = $this->salesRepo->getPengajuanGroupDetail($idPengajuan);
        if (! $group) {
            throw new RuntimeException('Pengajuan tidak ditemukan.');
        }

        if ($group->status === 'void') {
            throw new RuntimeException('Pengajuan ini sudah di-void sebelumnya.');
        }

        // Cek apakah ada termin yang sudah cair
        if (! empty($group->items)) {
            foreach ($group->items as $item) {
                if ($item->termin_status === 'sudah_cair') {
                    throw new RuntimeException('Pengajuan tidak dapat di-void karena sudah ada item termin yang cair.');
                }
            }
        }

        $this->db->transStart();
        try {
            // Update status pengajuan jadi void
            $this->pengajuanModel->update($idPengajuan, [
                'status'      => 'void',
                'voided_by'   => $userId,
                'voided_at'   => date('Y-m-d H:i:s'),
                'void_reason' => $alasan,
                'updated_by'  => $userId,
            ]);

            // Kembalikan seluruh termin terkait menjadi belum_diajukan
            $affectedFeeSalesIds = [];
            if (! empty($group->items)) {
                foreach ($group->items as $item) {
                    $this->feeSalesTerminModel->update($item->id_termin, [
                        'status'                => 'belum_diajukan',
                        'nomor_spp'            => null,
                        'tanggal_pengajuan_spp' => null,
                        'lampiran_spp'          => null,
                        'updated_by'           => $userId,
                    ]);
                    $affectedFeeSalesIds[] = (int) $item->id_fee_sales;
                }
            }

            // Hitung ulang status header fee_sales
            $affectedFeeSalesIds = array_unique(array_filter($affectedFeeSalesIds));
            foreach ($affectedFeeSalesIds as $fsId) {
                $totalActive = $this->salesRepo->countActiveTerminByFeeSalesId((int) $fsId);

                $fsStatus = $totalActive > 0 ? 'proses_termin' : 'draft';
                $this->salesRepo->updateFeeSalesStatus((int) $fsId, $fsStatus, $userId);
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new RuntimeException('Gagal memproses void pengajuan.');
            }

            return [
                'success' => true,
                'message' => 'Pengajuan berhasil dibatalkan (void). Item termin telah dikembalikan ke status belum diajukan.',
            ];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    /**
     * Tanda tangani pengajuan pencairan oleh MKDT atau Keuangan
     */
    public function signPengajuanGroup(int $idPengajuan, string $role, array $data, int $userId): array
    {
        $role = strtolower(trim($role));
        if (! in_array($role, ['mkdt', 'keuangan'], true)) {
            throw new RuntimeException('Role penanda tangan tidak valid.');
        }

        $group = $this->salesRepo->getPengajuanGroupDetail($idPengajuan);
        if (! $group) {
            throw new RuntimeException('Pengajuan tidak ditemukan.');
        }

        if ($group->status === 'void') {
            throw new RuntimeException('Pengajuan yang sudah di-void tidak dapat ditandatangani.');
        }

        // Verifikasi password akun saat ini
        $user = $this->salesRepo->userSnapshot($userId);
        $password = (string) ($data['password'] ?? '');
        if ($password === '' || empty($user['password_hash']) || ! Password::verify($password, (string) $user['password_hash'])) {
            throw new RuntimeException('Password akun tidak sesuai.');
        }

        $signMethod = (string) ($data['signature_method'] ?? 'canvas');
        $signData   = (string) ($data['signature_data'] ?? '');
        $sigPath    = $this->storePengajuanSignature($idPengajuan, $userId, $signMethod, $signData);
        $signerName = $user['name'] ?: ('User #' . $userId);

        $now = date('Y-m-d H:i:s');
        $updateFields = [
            "ttd_{$role}_by"     => $userId,
            "ttd_{$role}_nama"   => $signerName,
            "ttd_{$role}_path"   => $sigPath,
            "ttd_{$role}_method" => $signMethod,
            "ttd_{$role}_at"     => $now,
            'updated_by'         => $userId,
        ];
        $this->pengajuanModel->update($idPengajuan, $updateFields);

        $firstItem     = $group->items[0] ?? null;
        $notifProyek   = (int) ($group->id_proyek ?: ($firstItem->item_id_proyek ?? 0));
        $notifKavling  = (int) ($firstItem->id_kavling ?? 0);
        $notifKonsumen = (int) ($firstItem->id_konsumen ?? 0);
        $roleLabel     = strtoupper($role);

        $this->sendFeeSalesNotification(
            NotificationEvent::FEE_SALES_TTD_SIGNED,
            "TTD {$roleLabel} Pengajuan Fee Sales",
            "Pengajuan fee sales {$group->nomor_pengajuan} telah ditandatangani oleh {$signerName} ({$roleLabel}).",
            $notifProyek,
            $notifKavling,
            $notifKonsumen,
            'fee-sales/pengajuan/' . ($group->share_token ?: $idPengajuan),
            $idPengajuan,
            $userId
        );

        return [
            'success'   => true,
            'message'   => 'Tanda tangan berhasil disimpan.',
            'signer'    => $signerName,
            'role'      => $role,
            'signed_at' => $now,
        ];
    }

    /**
     * Tolak Pengajuan Pencairan oleh MKDT atau Keuangan disertai saran revisi harga jual net AJB
     */
    public function rejectPengajuanGroup(int $idPengajuan, string $role, array $data, int $userId): array
    {
        $role = strtolower(trim($role));
        if (! in_array($role, ['mkdt', 'keuangan'], true)) {
            throw new RuntimeException('Role penolak tidak valid.');
        }

        $group = $this->salesRepo->getPengajuanGroupDetail($idPengajuan);
        if (! $group) {
            throw new RuntimeException('Pengajuan tidak ditemukan.');
        }

        if ($group->status === 'void' || $group->status === 'selesai') {
            throw new RuntimeException("Pengajuan berstatus '{$group->status}' tidak dapat ditolak.");
        }

        if (! empty($group->ttd_mkdt_by) || ! empty($group->ttd_keuangan_by)) {
            throw new \RuntimeException('Pengajuan tidak dapat ditolak karena sudah ditandatangani oleh salah satu pihak (MKDT atau Keuangan).');
        }

        // Cek apakah ada termin yang sudah cair
        if (! empty($group->items)) {
            foreach ($group->items as $item) {
                if ($item->termin_status === 'sudah_cair') {
                    throw new RuntimeException('Pengajuan tidak dapat ditolak karena sudah ada item termin yang cair.');
                }
            }
        }

        $alasan = trim((string) ($data['alasan'] ?? $data['keterangan'] ?? $data['reject_reason'] ?? ''));
        if ($alasan === '') {
            throw new RuntimeException('Keterangan / alasan penolakan wajib diisi.');
        }

        $suggestedPrices = ! empty($data['suggested_prices']) && is_array($data['suggested_prices'])
            ? $data['suggested_prices']
            : (! empty($data['unit_prices']) && is_array($data['unit_prices']) ? $data['unit_prices'] : []);

        $globalSuggested = ! empty($data['suggested_harga_net_ajb']) ? $this->cleanNumeric($data['suggested_harga_net_ajb']) : 0.0;
        $firstSuggested  = $globalSuggested > 0 ? $globalSuggested : null;

        $this->db->transStart();
        try {
            if (! empty($group->items)) {
                foreach ($group->items as $item) {
                    $itemSuggested = null;
                    $idMkdt = (int) $item->id_mkdt;
                    $idItem = (int) $item->id_pengajuan_item;

                    if (isset($suggestedPrices[$idMkdt])) {
                        $itemSuggested = $this->cleanNumeric($suggestedPrices[$idMkdt]);
                    } elseif (isset($suggestedPrices[$idItem])) {
                        $itemSuggested = $this->cleanNumeric($suggestedPrices[$idItem]);
                    } elseif ($globalSuggested > 0) {
                        $itemSuggested = $globalSuggested;
                    }

                    if ($itemSuggested !== null && $itemSuggested > 0) {
                        if ($firstSuggested === null) {
                            $firstSuggested = $itemSuggested;
                        }
                        $this->pengajuanItemModel->update($idItem, [
                            'suggested_harga_net_ajb' => $itemSuggested,
                        ]);
                    }
                }
            }

            $now = date('Y-m-d H:i:s');
            $this->pengajuanModel->update($idPengajuan, [
                'status'                  => 'ditolak',
                'rejected_by'             => $userId,
                'rejected_role'           => $role,
                'rejected_at'             => $now,
                'reject_reason'           => $alasan,
                'suggested_harga_net_ajb' => $firstSuggested,
                // Reset tanda tangan MKDT dan Keuangan agar di-review dan di-TTD ulang setelah revisi
                'ttd_mkdt_by'             => null,
                'ttd_mkdt_nama'           => null,
                'ttd_mkdt_path'           => null,
                'ttd_mkdt_method'         => null,
                'ttd_mkdt_at'             => null,
                'ttd_keuangan_by'         => null,
                'ttd_keuangan_nama'       => null,
                'ttd_keuangan_path'       => null,
                'ttd_keuangan_method'     => null,
                'ttd_keuangan_at'         => null,
                'updated_by'              => $userId,
            ]);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new RuntimeException('Gagal memproses penolakan pengajuan.');
            }

            $firstItem     = $group->items[0] ?? null;
            $notifProyek   = (int) ($group->id_proyek ?: ($firstItem->item_id_proyek ?? 0));
            $notifKavling  = (int) ($firstItem->id_kavling ?? 0);
            $notifKonsumen = (int) ($firstItem->id_konsumen ?? 0);
            $roleLabel     = strtoupper($role);
            $actionUrl     = 'fee-sales/pengajuan/' . ($group->share_token ?: $idPengajuan);
            $notifText     = "Pengajuan fee sales {$group->nomor_pengajuan} ditolak oleh {$roleLabel}. Catatan: {$alasan}";

            if (! empty($group->created_by)) {
                try {
                    $this->notifikasiService->tambah_notif_user(
                        (int) $group->created_by,
                        $notifText,
                        $userId,
                        $notifKavling > 0 ? $notifKavling : null,
                        $notifKonsumen > 0 ? $notifKonsumen : null,
                        NotificationEvent::FEE_SALES_PENGAJUAN_REJECTED,
                        $notifProyek > 0 ? $notifProyek : null,
                        $actionUrl,
                        NotificationEvent::FEE_SALES_PENGAJUAN_REJECTED
                    );
                } catch (\Throwable $e) {
                    log_message('error', '[SalesExternalService] Gagal kirim notif personal reject: ' . $e->getMessage());
                }
            }

            $this->sendFeeSalesNotification(
                NotificationEvent::FEE_SALES_PENGAJUAN_REJECTED,
                "Penolakan Pengajuan Fee Sales ({$roleLabel})",
                $notifText,
                $notifProyek,
                $notifKavling,
                $notifKonsumen,
                $actionUrl,
                $idPengajuan,
                $userId
            );

            return [
                'success' => true,
                'message' => 'Pengajuan fee sales berhasil ditolak dan catatan saran revisi telah dikirimkan ke pembuat pengajuan.',
                'status'  => 'ditolak',
            ];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    /**
     * Hitung ulang nominal seluruh termin dan item pengajuan aktif untuk unit MKDT
     */
    private function applyPriceRecalculationForMkdt(int $idMkdt, float $newHargaAjb, ?int $userId = null): void
    {
        $currentAjb     = $this->salesRepo->getMkdtHargaNetAjb($idMkdt);
        $isPriceChanged = abs($currentAjb - $newHargaAjb) > 0.01;
        if ($this->salesRepo->hasCairTerminByMkdt($idMkdt)) {
            if ($isPriceChanged) {
                throw new RuntimeException('Tidak dapat mengubah Harga Jual Net AJB karena sudah ada pengajuan/termin yang cair pada unit kavling ini.');
            }
            // Jika harga sama sekali tidak berubah, skip / no-op
            return;
        }

        if ($newHargaAjb <= 0) {
            throw new RuntimeException('Nominal Harga Jual Net AJB harus lebih dari 0.');
        }

        // 1. Update tabel mkdt melalui kontrak MKDT
        $this->mkdtPricingService->updateHargaNetAjb($idMkdt, $newHargaAjb);

        // 2. Ambil seluruh entri fee_sales untuk unit ini
        $fees = $this->salesRepo->getFeeSalesListByMkdt($idMkdt);

        foreach ($fees as $fee) {
            $idFeeSales = (int) $fee->id;
            $persenFee  = (float) $fee->persentase_fee;
            $totalFee   = round(($persenFee / 100) * $newHargaAjb, 2);

            // Update header fee_sales
            $this->salesRepo->updateFeeSalesPricing($idFeeSales, $newHargaAjb, $totalFee, $userId);

            // Ambil semua termin untuk fee_sales ini urut termin_ke ASC
            $termins = $this->salesRepo->getTerminListByFeeSalesId($idFeeSales);

            if (empty($termins)) {
                continue;
            }

            $countTermin  = count($termins);
            $runningTotal = 0.0;

            foreach ($termins as $idx => $t) {
                $isLast = ($idx === $countTermin - 1);
                $persen = (float) $t->persen;

                if ($isLast) {
                    $newNominal = round($totalFee - $runningTotal, 2);
                } else {
                    $newNominal = round(($persen / 100) * $totalFee, 2);
                    $runningTotal += $newNominal;
                }

                // Update in-place fee_sales_termin
                $this->salesRepo->updateTerminNominal((int) $t->id, $newNominal, $userId);

                // Update nominal di fee_sales_pengajuan_item pada pengajuan aktif (status NOT IN ('void', 'selesai'))
                // sekaligus bersihkan suggested_harga_net_ajb karena sudah diterapkan
                $this->salesRepo->syncActivePengajuanItemNominalByTerminId((int) $t->id, $newNominal);
            }
        }
    }

    /**
     * Memperbarui nominal pencairan tiap termin berdasarkan Harga Jual Net AJB baru
     * Mendukung pemanggilan dari Modal Detail Pengajuan (multi-unit) maupun Modal Detail Fee Kavling (single unit)
     */
    public function recalculateTerminNominals(array $data, int $userId): array
    {
        $idPengajuan = ! empty($data['id_pengajuan']) ? (int) $data['id_pengajuan'] : 0;
        $idMkdt      = ! empty($data['id_mkdt']) ? (int) $data['id_mkdt'] : 0;

        if ($idPengajuan <= 0 && $idMkdt <= 0) {
            throw new RuntimeException('ID Pengajuan atau ID MKDT wajib diberikan.');
        }

        $this->db->transStart();
        try {
            $updatedUnits = 0;

            if ($idPengajuan > 0) {
                $group = $this->salesRepo->getPengajuanGroupDetail($idPengajuan);
                if (! $group) {
                    throw new RuntimeException('Data pengajuan tidak ditemukan.');
                }

                if ($group->status === 'void' || $group->status === 'selesai') {
                    throw new RuntimeException("Pengajuan berstatus '{$group->status}' tidak dapat diperbarui.");
                }

                $unitPricesInput = ! empty($data['unit_prices']) && is_array($data['unit_prices']) ? $data['unit_prices'] : [];
                $globalPrice     = ! empty($data['harga_net_ajb']) ? $this->cleanNumeric($data['harga_net_ajb']) : 0.0;

                $unitsToUpdate = [];
                foreach ($group->items as $item) {
                    $uId = (int) $item->id_mkdt;
                    if ($uId <= 0 || isset($unitsToUpdate[$uId])) {
                        continue;
                    }

                    $newPrice = 0.0;
                    if (isset($unitPricesInput[$uId])) {
                        $newPrice = $this->cleanNumeric($unitPricesInput[$uId]);
                    } elseif ($globalPrice > 0) {
                        $newPrice = $globalPrice;
                    } elseif (! empty($item->suggested_harga_net_ajb) && (float) $item->suggested_harga_net_ajb > 0) {
                        $newPrice = (float) $item->suggested_harga_net_ajb;
                    } elseif (! empty($group->suggested_harga_net_ajb) && (float) $group->suggested_harga_net_ajb > 0) {
                        $newPrice = (float) $group->suggested_harga_net_ajb;
                    } else {
                        $newPrice = (float) ($item->fee_harga_net_ajb ?: $item->harga_net_ajb);
                    }

                    $unitsToUpdate[$uId] = $newPrice;
                }

                foreach ($unitsToUpdate as $uId => $nPrice) {
                    if ($nPrice > 0) {
                        $this->applyPriceRecalculationForMkdt($uId, $nPrice, $userId);
                        $updatedUnits++;
                    }
                }

                // Jika pengajuan berstatus 'ditolak', kembalikan ke status 'diajukan' dan bersihkan rejection state
                if ($group->status === 'ditolak') {
                    $this->pengajuanModel->update($idPengajuan, [
                        'status'                  => 'diajukan',
                        'rejected_by'             => null,
                        'rejected_role'           => null,
                        'rejected_at'             => null,
                        'reject_reason'           => null,
                        'suggested_harga_net_ajb' => null,
                        'updated_by'              => $userId,
                    ]);
                }
            } else {
                // Konteks single unit dari modal kavling
                $newHarga = $this->cleanNumeric($data['harga_net_ajb'] ?? 0);
                if ($newHarga <= 0) {
                    throw new RuntimeException('Harga Jual Net AJB harus lebih dari 0.');
                }

                $this->applyPriceRecalculationForMkdt($idMkdt, $newHarga, $userId);
                $updatedUnits++;

                // Periksa pengajuan berstatus 'ditolak' terkait unit ini
                $rejectedPengajuans = $this->salesRepo->getRejectedPengajuanByMkdt($idMkdt);

                foreach ($rejectedPengajuans as $rp) {
                    $pId = (int) $rp->id_pengajuan;
                    $remainingSuggested = $this->salesRepo->countRemainingSuggestedItemsByPengajuanId($pId);

                    if ($remainingSuggested === 0) {
                        $this->pengajuanModel->update($pId, [
                            'status'                  => 'diajukan',
                            'rejected_by'             => null,
                            'rejected_role'           => null,
                            'rejected_at'             => null,
                            'reject_reason'           => null,
                            'suggested_harga_net_ajb' => null,
                            'updated_by'              => $userId,
                        ]);
                    }
                }
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new RuntimeException('Gagal memperbaharui nominal pencairan termin.');
            }

            return [
                'success'       => true,
                'message'       => 'Nominal termin pencairan berhasil diperbarui berdasarkan Harga Jual Net AJB terbaru.',
                'units_updated' => $updatedUnits,
            ];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    /**
     * Konfirmasi Pencairan Massal untuk seluruh atau subset item dalam satu Grup Pengajuan
     */
    public function confirmPencairanGroup(int $idPengajuan, array $data, ?int $userId, ?UploadedFile $fileBukti = null): array
    {
        $group = $this->salesRepo->getPengajuanGroupDetail($idPengajuan);
        if (! $group) {
            throw new RuntimeException('Pengajuan tidak ditemukan.');
        }

        if ($group->status === 'void') {
            throw new RuntimeException('Pengajuan yang sudah di-void tidak dapat dicairkan.');
        }

        $tglCair = ! empty($data['tanggal_cair']) ? $data['tanggal_cair'] : date('Y-m-d');
        $noCek   = trim($data['no_cek'] ?? '');
        $ket     = trim($data['keterangan'] ?? '');
        $selectedTerminIds = ! empty($data['id_termin']) && is_array($data['id_termin'])
            ? array_map('intval', $data['id_termin'])
            : [];

        $this->db->transStart();
        try {
            $lampiranBuktiPath = null;
            if ($fileBukti && $fileBukti->isValid() && ! $fileBukti->hasMoved()) {
                $lampiranBuktiPath = $this->storageService->store($fileBukti, 'uploads/sales/bukti_cair/' . date('Ymd'));
            }

            $affectedCount    = 0;
            $totalCairNominal = 0.0;
            $firstCairItem    = null;
            foreach ($group->items as $item) {
                if (! empty($selectedTerminIds) && ! in_array((int) $item->id_termin, $selectedTerminIds, true)) {
                    continue;
                }

                if ($item->termin_status === 'sudah_cair') {
                    continue;
                }

                $updateData = [
                    'status'       => 'sudah_cair',
                    'tanggal_cair' => $tglCair,
                    'no_cek'       => $noCek ?: $item->termin_no_cek,
                    'keterangan'   => $ket ?: $item->termin_keterangan,
                    'updated_by'   => $userId,
                ];
                if ($lampiranBuktiPath) {
                    $updateData['lampiran_bukti_cair'] = $lampiranBuktiPath;
                }

                $this->feeSalesTerminModel->update($item->id_termin, $updateData);

                // Cek status parent fee_sales
                $this->syncFeeSalesStatusFromTermins((int) $item->id_fee_sales, $userId);

                if ($firstCairItem === null) {
                    $firstCairItem = $item;
                }
                $totalCairNominal += (float) ($item->nominal ?? $item->termin_nominal ?? 0);
                $affectedCount++;
            }

            // Update status grup pengajuan
            $this->syncPengajuanStatus($idPengajuan, $userId);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new RuntimeException('Gagal memproses pencairan pengajuan.');
            }

            if ($affectedCount > 0) {
                $refItem       = $firstCairItem ?? ($group->items[0] ?? null);
                $notifProyek   = (int) ($group->id_proyek ?: ($refItem->item_id_proyek ?? 0));
                $notifKavling  = (int) ($refItem->id_kavling ?? 0);
                $notifKonsumen = (int) ($refItem->id_konsumen ?? 0);
                $formattedNom  = number_format($totalCairNominal, 0, ',', '.');

                $this->sendFeeSalesNotification(
                    NotificationEvent::FEE_SALES_PENCAIRAN,
                    'Pencairan Fee Sales',
                    "Pencairan fee sales {$group->nomor_pengajuan} ({$affectedCount} item, Rp {$formattedNom}) telah dikonfirmasi.",
                    $notifProyek,
                    $notifKavling,
                    $notifKonsumen,
                    'fee-sales/pengajuan/' . ($group->share_token ?: $idPengajuan),
                    $idPengajuan,
                    $userId
                );
            }

            return [
                'success' => true,
                'message' => "Berhasil mengonfirmasi pencairan untuk {$affectedCount} item termin.",
            ];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    /**
     * Ambil detail lengkap grup pengajuan berdasarkan ID pengajuan
     */
    public function getPengajuanGroupDetail(int $idPengajuan): ?object
    {
        return $this->salesRepo->getPengajuanGroupDetail($idPengajuan);
    }

    /**
     * Ambil detail lengkap grup pengajuan berdasarkan share_token atau ID
     */
    public function getPengajuanGroupByToken(string $tokenOrId): ?object
    {
        return $this->salesRepo->getPengajuanGroupByToken($tokenOrId);
    }

    private function syncFeeSalesStatusFromTermins(int $idFeeSales, ?int $userId): void
    {
        $totalTermin  = $this->salesRepo->countTerminByFeeSalesId($idFeeSales);
        $cairTermin   = $this->salesRepo->countCairTerminByFeeSalesId($idFeeSales);
        $parentStatus = ($totalTermin > 0 && $totalTermin === $cairTermin) ? 'selesai' : 'proses_termin';

        $this->salesRepo->updateFeeSalesStatus($idFeeSales, $parentStatus, $userId);
    }

    /**
     * Sinkronkan status pada tabel fee_sales_pengajuan berdasarkan id_pengajuan
     */
    protected function syncPengajuanStatus(int $idPengajuan, ?int $userId): void
    {
        if ($this->salesRepo->getPengajuanStatusById($idPengajuan) === 'void') {
            return;
        }

        $items = $this->salesRepo->getPengajuanItemStatuses($idPengajuan);

        if (empty($items)) {
            return;
        }

        $total = count($items);
        $cair  = 0;
        foreach ($items as $it) {
            if ($it->status === 'sudah_cair') {
                $cair++;
            }
        }

        $newStatus = 'diajukan';
        if ($cair === $total && $total > 0) {
            $newStatus = 'selesai';
        } elseif ($cair > 0) {
            $newStatus = 'cair_sebagian';
        }

        $this->pengajuanModel->update($idPengajuan, [
            'status'     => $newStatus,
            'updated_by' => $userId,
        ]);
    }

    /**
     * Sinkronkan status pada tabel fee_sales_pengajuan berdasarkan id_termin
     */
    protected function syncPengajuanStatusByTerminId(int $idTermin, ?int $userId): void
    {
        $links = $this->salesRepo->getPengajuanLinksByTerminId($idTermin);

        foreach ($links as $link) {
            $this->syncPengajuanStatus((int) $link->id_pengajuan, $userId);
        }
    }

    private function formatKavlingLabel(?object $detail): string
    {
        if (! $detail) {
            return 'unit kavling';
        }
        $parts = [];
        if (! empty($detail->no_kavling)) {
            $parts[] = 'Kavling ' . trim((string) $detail->no_kavling);
        }
        if (! empty($detail->nama_konsumen)) {
            $parts[] = '(' . trim((string) $detail->nama_konsumen) . ')';
        }
        if (! empty($detail->nama_proyek)) {
            $parts[] = '- ' . trim((string) $detail->nama_proyek);
        }

        return ! empty($parts) ? implode(' ', $parts) : 'unit kavling';
    }

    private function sendFeeSalesNotification(
        string $eventCode,
        string $title,
        string $isi,
        int $idProyek,
        int $idKavling,
        int $idKonsumen,
        string $actionUrl,
        ?int $referenceId = null,
        ?int $actorId = null
    ): void {
        try {
            $resolvedActorId = $actorId ?? (function_exists('user_id') && user_id() ? (int) user_id() : 0);
            $this->notifikasiService->tambah_notif(
                '3;4',
                $isi,
                $resolvedActorId,
                $idKavling > 0 ? $idKavling : null,
                $idKonsumen > 0 ? $idKonsumen : null,
                $eventCode,
                $idProyek > 0 ? $idProyek : null,
                $actionUrl,
                $eventCode
            );
        } catch (\Throwable $e) {
            log_message('error', '[SalesExternalService] Gagal mengirim notifikasi ' . $eventCode . ': ' . $e->getMessage());
        }
    }

    private function cleanNumeric(mixed $value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }
        if (is_numeric($value)) {
            return (float) $value;
        }
        $val = trim((string) $value);
        if (str_contains($val, '.') && str_contains($val, ',')) {
            if (strrpos($val, ',') > strrpos($val, '.')) {
                $val = str_replace('.', '', $val);
                $val = str_replace(',', '.', $val);
            } else {
                $val = str_replace(',', '', $val);
            }
        } elseif (substr_count($val, '.') > 1) {
            $val = str_replace('.', '', $val);
        } elseif (substr_count($val, ',') > 1) {
            $val = str_replace(',', '', $val);
        } elseif (preg_match('/^\d{1,3}\.\d{3}$/', $val)) {
            $val = str_replace('.', '', $val);
        } else {
            $val = str_replace(',', '.', $val);
        }

        return (float) preg_replace('/[^\d.\-]/', '', $val);
    }
}

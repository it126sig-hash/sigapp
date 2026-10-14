<?php

namespace App\Services;

use App\Models\FeeSalesModel;
use App\Models\FeeSalesTerminModel;
use App\Models\SalesHistoryModel;
use App\Models\SalesModel;
use App\Repositories\SalesExternalRepository;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\Files\UploadedFile;
use Config\Database;
use RuntimeException;

class SalesExternalService
{
    protected BaseConnection $db;
    protected SalesModel $salesModel;
    protected SalesHistoryModel $salesHistoryModel;
    protected FeeSalesModel $feeSalesModel;
    protected FeeSalesTerminModel $feeSalesTerminModel;
    protected SalesExternalRepository $salesRepo;
    protected StorageService $storageService;

    public function __construct()
    {
        $this->db                  = Database::connect();
        $this->salesModel          = new SalesModel();
        $this->salesHistoryModel   = new SalesHistoryModel();
        $this->feeSalesModel       = new FeeSalesModel();
        $this->feeSalesTerminModel = new FeeSalesTerminModel();
        $this->salesRepo           = new SalesExternalRepository();
        $this->storageService      = new StorageService();
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
    public function saveFeeHeader(array $data, ?int $userId): array
    {
        $idMkdt = (int) ($data['id_mkdt'] ?? 0);
        if ($idMkdt <= 0) {
            throw new RuntimeException('ID MKDT tidak valid.');
        }

        $detail = $this->salesRepo->getFeeDetailByMkdt($idMkdt);
        if (! $detail) {
            throw new RuntimeException('Data transaksi akad tidak ditemukan.');
        }

        $hargaAjb = $this->cleanNumeric($data['harga_net_ajb'] ?? 0);
        if ($hargaAjb <= 0) {
            // Fallback ke harga_jual_net jika harga AJB belum diisi
            $hargaAjb = (float) ($detail->harga_net_ajb > 0 ? $detail->harga_net_ajb : $detail->harga_jual_net);
        }

        $persenFee = $this->cleanNumeric($data['persentase_fee'] ?? 0);
        if ($persenFee <= 0) {
            // Ambil dari master sales jika ada
            if ((int) $detail->is_subsidi === 1) {
                $persenFee = (float) ($detail->sales_fee_subsidi ?? 1.50);
            } else {
                $persenFee = (float) ($detail->sales_fee_komersil ?? 2.00);
            }
        }

        $isManual = ! empty($data['total_fee_manual']) && (int) $data['total_fee_manual'] === 1;
        if ($isManual && isset($data['total_fee'])) {
            $totalFee = $this->cleanNumeric($data['total_fee']);
        } else {
            $totalFee = round(($persenFee / 100) * $hargaAjb, 2);
        }

        $skemaTermin = in_array($data['skema_termin'] ?? '', ['100', '30_60_10', 'custom'], true)
            ? $data['skema_termin']
            : '100';

        $salesNama = trim($data['sales_nama'] ?? ($detail->master_sales_nama ?? $detail->sales_konsumen ?? ''));
        $salesKet  = trim($data['sales_keterangan'] ?? ($detail->keterangan_sales ?? ''));

        $this->db->transStart();
        try {
            // Update harga_net_ajb pada tabel mkdt
            $this->db->table('mkdt')
                ->where('id_mkdt', $idMkdt)
                ->update(['harga_net_ajb' => $hargaAjb]);

            // Cek apakah fee_sales sudah ada
            $existingFee = $this->db->table('fee_sales')->where('id_mkdt', $idMkdt)->get()->getRow();

            $headerData = [
                'id_mkdt'          => $idMkdt,
                'id_sales'         => ! empty($detail->id_sales) ? (int) $detail->id_sales : null,
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
                $this->feeSalesModel->update($idFeeSales, $headerData);
            } else {
                $headerData['created_by'] = $userId;
                $idFeeSales = $this->feeSalesModel->insert($headerData);
            }

            // Generate ulang termin jika belum ada termin yang cair
            $cairCount = (int) $this->db->table('fee_sales_termin')
                ->where('id_fee_sales', $idFeeSales)
                ->where('status', 'sudah_cair')
                ->countAllResults();

            if ($cairCount === 0) {
                // Hapus termin lama yang belum cair
                $this->db->table('fee_sales_termin')->where('id_fee_sales', $idFeeSales)->delete();

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
                }
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new RuntimeException('Gagal memperbarui kalkulasi fee sales.');
            }

            return [
                'success'      => true,
                'id_fee_sales' => $idFeeSales,
                'total_fee'    => $totalFee,
                'message'      => 'Kalkulasi Fee Sales dan termin berhasil disimpan.',
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
        $termin = $this->db->table('fee_sales_termin')->where('id', $idTermin)->get()->getRow();
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
            $this->db->table('fee_sales')
                ->where('id', $termin->id_fee_sales)
                ->where('status !=', 'selesai')
                ->update(['status' => 'proses_termin', 'updated_by' => $userId]);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new RuntimeException('Gagal menyimpan pengajuan SPP termin.');
            }

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
        $termin = $this->db->table('fee_sales_termin')->where('id', $idTermin)->get()->getRow();
        if (! $termin) {
            throw new RuntimeException('Data termin tidak ditemukan.');
        }

        $tglCair = ! empty($data['tanggal_cair']) ? $data['tanggal_cair'] : date('Y-m-d');
        $ket     = trim($data['keterangan'] ?? '');

        $this->db->transStart();
        try {
            $updateData = [
                'status'       => 'sudah_cair',
                'tanggal_cair' => $tglCair,
                'keterangan'   => $ket ?: $termin->keterangan,
                'updated_by'   => $userId,
            ];

            if ($fileBukti && $fileBukti->isValid() && ! $fileBukti->hasMoved()) {
                $storedPath = $this->storageService->store($fileBukti, 'uploads/sales/bukti_cair/' . date('Ymd'));
                $updateData['lampiran_bukti_cair'] = $storedPath;
            }

            $this->feeSalesTerminModel->update($idTermin, $updateData);

            // Cek apakah semua termin pada fee_sales ini sudah cair
            $totalTermin = (int) $this->db->table('fee_sales_termin')
                ->where('id_fee_sales', $termin->id_fee_sales)
                ->countAllResults();

            $cairTermin = (int) $this->db->table('fee_sales_termin')
                ->where('id_fee_sales', $termin->id_fee_sales)
                ->where('status', 'sudah_cair')
                ->countAllResults();

            $newParentStatus = ($totalTermin > 0 && $totalTermin === $cairTermin) ? 'selesai' : 'proses_termin';

            $this->db->table('fee_sales')
                ->where('id', $termin->id_fee_sales)
                ->update(['status' => $newParentStatus, 'updated_by' => $userId]);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new RuntimeException('Gagal mengonfirmasi pencairan fee termin.');
            }

            return [
                'success' => true,
                'message' => 'Pencairan Termin ke-' . $termin->termin_ke . ' berhasil dikonfirmasi.',
            ];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
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

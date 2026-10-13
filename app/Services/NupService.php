<?php

namespace App\Services;

use App\Exceptions\ServiceException;
use App\Models\KonsumenModel;
use App\Models\MkdtModel;
use App\Repositories\NupRepository;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\Files\UploadedFile;

class NupService
{
    private BaseConnection $db;
    private NupRepository $nupRepo;
    private MkdtModel $mkdtModel;
    private KonsumenModel $konsumenModel;
    private KonsumenService $konsumenService;
    private StorageService $storageService;
    private ReferralService $referralService;
    private FileAccessService $fileAccessService;
    private BookingPaymentService $bookingPaymentService;
    private MkdtHistoryService $mkdtHistoryService;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db                    = $db ?? \Config\Database::connect();
        $this->nupRepo               = new NupRepository($this->db);
        $this->mkdtModel             = new MkdtModel($this->db);
        $this->konsumenModel         = new KonsumenModel($this->db);
        $this->konsumenService       = new KonsumenService();
        $this->storageService        = new StorageService();
        $this->referralService       = new ReferralService();
        $this->fileAccessService     = new FileAccessService();
        $this->bookingPaymentService = new BookingPaymentService($this->db);
        $this->mkdtHistoryService    = new MkdtHistoryService();
    }

    public function getListByProyek(int $idProyek, ?string $search = null): array
    {
        if ($idProyek <= 0) {
            return [];
        }

        $rows = $this->nupRepo->getNupListByProyek($idProyek, $search);
        foreach ($rows as &$row) {
            $idMkdt     = (int) ($row['id_mkdt'] ?? 0);
            $idKonsumen = (int) ($row['id_konsumen'] ?? 0);

            $row['file_ktp_url']       = (! empty($row['file_ktp']) && $idKonsumen > 0) ? $this->fileAccessService->accessUrl('konsumen_ktp', $idKonsumen) : null;
            $row['file_npwp_url']      = (! empty($row['file_npwp']) && $idKonsumen > 0) ? $this->fileAccessService->accessUrl('konsumen_npwp', $idKonsumen) : null;
            $row['file_data_diri_url'] = (! empty($row['file_data_diri']) && $idKonsumen > 0) ? $this->fileAccessService->accessUrl('konsumen_data', $idKonsumen) : null;
            $row['file_nup_url']       = (! empty($row['file_nup']) && $idMkdt > 0) ? $this->fileAccessService->accessUrl('mkdt_file_nup', $idMkdt) : null;

            $lastJalan = trim((string) ($row['last_nama_jalan'] ?? ''));
            $lastNo    = trim((string) ($row['last_no_kavling'] ?? ''));
            $row['last_kavling_label'] = ($lastJalan !== '' || $lastNo !== '')
                ? trim($lastJalan . ' No. ' . ($lastNo !== '' ? $lastNo : '-'))
                : null;
        }
        unset($row);

        return $rows;
    }

    public function getDetail(int $idMkdt): array
    {
        $detail = $this->nupRepo->getNupDetail($idMkdt, false);
        if (! $detail) {
            throw new ServiceException('Data NUP tidak ditemukan.', 404);
        }

        $idKonsumen = (int) ($detail['id_konsumen'] ?? 0);
        $detail['file_ktp_url']       = (! empty($detail['file_ktp']) && $idKonsumen > 0) ? $this->fileAccessService->accessUrl('konsumen_ktp', $idKonsumen) : null;
        $detail['file_npwp_url']      = (! empty($detail['file_npwp']) && $idKonsumen > 0) ? $this->fileAccessService->accessUrl('konsumen_npwp', $idKonsumen) : null;
        $detail['file_data_diri_url'] = (! empty($detail['file_data_diri']) && $idKonsumen > 0) ? $this->fileAccessService->accessUrl('konsumen_data', $idKonsumen) : null;
        $detail['file_nup_url']       = (! empty($detail['file_nup']) && $idMkdt > 0) ? $this->fileAccessService->accessUrl('mkdt_file_nup', $idMkdt) : null;

        $lastJalan = trim((string) ($detail['last_nama_jalan'] ?? ''));
        $lastNo    = trim((string) ($detail['last_no_kavling'] ?? ''));
        $detail['last_kavling_label'] = ($lastJalan !== '' || $lastNo !== '')
            ? trim($lastJalan . ' No. ' . ($lastNo !== '' ? $lastNo : '-'))
            : null;

        $detail['booking_state']   = $this->bookingPaymentService->getBooking($idMkdt);
        $detail['kavling_history'] = $this->getFormattedKavlingHistory($idMkdt, 0);
        $detail['no_nup']          = ! empty($detail['no_nup']) ? (string) $detail['no_nup'] : '';

        return $detail;
    }

    public function saveNup(array $post, array $files, int $actorId): array
    {
        $idMkdt       = (int) ($post['id_mkdt'] ?? 0);
        $idProyek     = (int) ($post['id_proyek'] ?? 0);
        $noNup        = trim((string) ($post['no_nup'] ?? ''));
        $namaKonsumen = trim((string) ($post['nama_konsumen'] ?? ''));
        $bookingTgl   = trim((string) ($post['booking_tgl'] ?? ''));
        $bookingFee   = $this->cleanNumeric($post['booking_fee'] ?? 0);
        $catatanNup   = trim((string) ($post['catatan_nup'] ?? ''));

        if ($idProyek <= 0) {
            throw new ServiceException('Proyek wajib dipilih terlebih dahulu.', 422);
        }
        if ($noNup === '') {
            throw new ServiceException('Nomor Urut Pemesanan (No NUP) wajib diisi manual.', 422);
        }
        if ($this->nupRepo->isNoNupExists($noNup, $idProyek, $idMkdt)) {
            throw new ServiceException("Nomor NUP '{$noNup}' sudah digunakan pada proyek ini. Harap gunakan nomor NUP yang berbeda.", 422);
        }
        if ($namaKonsumen === '') {
            throw new ServiceException('Nama konsumen wajib diisi.', 422);
        }
        if ($bookingTgl === '' || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $bookingTgl)) {
            throw new ServiceException('Tanggal booking wajib diisi dengan format yang benar.', 422);
        }
        if ($bookingFee <= 0) {
            throw new ServiceException('Nominal Booking Fee pada NUP wajib lebih dari Rp 0.', 422);
        }
        if ($catatanNup === '') {
            throw new ServiceException('Catatan NUP wajib diisi.', 422);
        }

        $now = date('Y-m-d H:i:s');

        $this->db->transStart();

        try {
            $existingMkdt = null;
            $idKonsumen   = (int) ($post['id_konsumen'] ?? 0);

            if ($idMkdt > 0) {
                $existingMkdt = $this->nupRepo->getNupDetail($idMkdt, true);
                if (! $existingMkdt) {
                    throw new ServiceException('Data NUP yang akan diubah tidak ditemukan.', 404);
                }
                if (($existingMkdt['status_mkdt'] ?? '') !== 'NUP') {
                    throw new ServiceException('Data ini sudah bukan berstatus NUP.', 422);
                }
                $idKonsumen = (int) ($existingMkdt['id_konsumen'] ?? $idKonsumen);

                $bookingState = $this->bookingPaymentService->getBooking($idMkdt);
                if (! empty($bookingState['is_verified'])) {
                    $bookingFee = (float) ($existingMkdt['booking_fee'] ?? $bookingFee);
                    $bookingTgl = (string) ($existingMkdt['booking_tgl'] ?? $bookingTgl);
                }
            }

            $fileFields = [];
            $konsumenUploadMap = [
                'file_npwp'      => 'uploads/konsumen/n/' . date('Ymd'),
                'file_ktp'       => 'uploads/konsumen/k/' . date('Ymd'),
                'file_data_diri' => 'uploads/konsumen/d/' . date('Ymd'),
            ];
            foreach ($konsumenUploadMap as $field => $dir) {
                $file = $files[$field] ?? null;
                if ($file instanceof UploadedFile && $file->isValid() && ! $file->hasMoved()) {
                    $fileFields[$field] = $this->storageService->store($file, $dir);
                }
            }

            $fileNupPath = $existingMkdt['file_nup'] ?? null;
            $fileNup     = $files['file_nup'] ?? null;
            if ($fileNup instanceof UploadedFile && $fileNup->isValid() && ! $fileNup->hasMoved()) {
                $fileNupPath = $this->storageService->store($fileNup, 'uploads/mkdt/nup/' . date('Ymd'));
            }

            $konsData = [
                'no_spptb'                  => trim((string) ($post['no_spptb'] ?? '')),
                'nama_konsumen'             => $namaKonsumen,
                'npwp'                      => trim((string) ($post['npwp'] ?? $post['npwp_konsumen'] ?? '')),
                'nik'                       => trim((string) ($post['nik'] ?? $post['nik_konsumen'] ?? '')),
                'alamat_konsumen'           => trim((string) ($post['alamat_konsumen'] ?? '')),
                'hp_konsumen'               => trim((string) ($post['hp_konsumen'] ?? '')),
                'email_konsumen'            => trim((string) ($post['email_konsumen'] ?? '')),
                'nama_instansi'             => trim((string) ($post['nama_instansi'] ?? '')),
                'alamat_instansi'           => trim((string) ($post['alamat_instansi'] ?? '')),
                'tel_instansi'              => trim((string) ($post['tel_instansi'] ?? '')),
                'email_instansi'            => trim((string) ($post['email_instansi'] ?? '')),
                'alamat_surat'              => trim((string) ($post['alamat_surat'] ?? '')),
                'pekerjaan'                 => trim((string) ($post['pekerjaan'] ?? 'Pegawai')),
                'lama_bekerja'              => trim((string) ($post['lama_bekerja'] ?? '')),
                'bidang_pekerjaan'          => trim((string) ($post['bidang_pekerjaan'] ?? '')),
                'status_pernikahan'         => trim((string) ($post['status_pernikahan'] ?? 'Belum Menikah')),
                'nama_pasangan'             => trim((string) ($post['nama_pasangan'] ?? '')),
                'nik_pasangan'              => trim((string) ($post['nik_pasangan'] ?? '')),
                'hp_pasangan'               => trim((string) ($post['hp_pasangan'] ?? '')),
                'status_pekerjaan_pasangan' => trim((string) ($post['status_pekerjaan_pasangan'] ?? 'Tidak Bekerja')),
                'instansi_pasangan'         => trim((string) ($post['instansi_pasangan'] ?? '')),
                'sales'                     => trim((string) ($post['sales'] ?? '')),
                'id_kavling'                => null,
                'status_konsumen'           => 'NUP',
                'status'                    => 'Normal',
                'keterangan'                => $catatanNup,
                'edit_by'                   => $actorId,
            ];
            if ($idKonsumen <= 0) {
                $konsData['add_by']  = $actorId;
                $konsData['uniq_id'] = uniqid('', true);
            }
            foreach ($fileFields as $kField => $vPath) {
                $konsData[$kField] = $vPath;
            }

            $savedKonsumenId = $this->konsumenService->upsert($idKonsumen > 0 ? $idKonsumen : null, $konsData);
            if (! $savedKonsumenId) {
                throw new ServiceException('Gagal menyimpan data identitas konsumen NUP.', 500);
            }
            $this->referralService->generateKodeReferal((int) $savedKonsumenId);

            if ($idMkdt > 0) {
                $updateMkdt = [
                    'id_konsumen' => (int) $savedKonsumenId,
                    'id_proyek'   => $idProyek,
                    'no_nup'      => $noNup,
                    'booking_fee' => $bookingFee,
                    'booking_tgl' => $bookingTgl,
                    'catatan_nup' => $catatanNup,
                    'file_nup'    => $fileNupPath,
                    'edit_by'     => $actorId,
                ];
                if (empty($existingMkdt['keterangan'])) {
                    $updateMkdt['keterangan'] = $catatanNup;
                }
                if (! $this->mkdtModel->update($idMkdt, $updateMkdt)) {
                    throw new ServiceException('Gagal memperbarui data NUP.', 500);
                }
            } else {
                $insertMkdt = [
                    'id_konsumen'       => (int) $savedKonsumenId,
                    'id_proyek'         => $idProyek,
                    'no_nup'            => $noNup,
                    'id_kavling'        => null,
                    'status_mkdt'       => 'NUP',
                    'is_batal'          => 0,
                    'booking_fee'       => $bookingFee,
                    'booking_tgl'       => $bookingTgl,
                    'catatan_nup'       => $catatanNup,
                    'keterangan'        => $catatanNup,
                    'file_nup'          => $fileNupPath,
                    'nup_source'        => 'BARU',
                    'nup_at'            => $now,
                    'nup_by'            => $actorId,
                    'id_bank'           => 0,
                    'dajam_selesai'     => 0,
                    'harga_penambahan'  => 0,
                    'harga_harus_bayar' => 0,
                    'uniq_id'           => uniqid('', true),
                    'add_by'            => $actorId,
                    'edit_by'           => $actorId,
                ];
                if (! $this->mkdtModel->insert($insertMkdt)) {
                    throw new ServiceException('Gagal menyimpan transaksi NUP.', 500);
                }
                $idMkdt = (int) $this->mkdtModel->getInsertID();
            }

            $bookingResult = $this->bookingPaymentService->synchronize($idMkdt, $actorId);

            if (! $existingMkdt) {
                $this->nupRepo->insertKavlingHistory([
                    'id_mkdt'                      => $idMkdt,
                    'id_konsumen'                  => (int) $savedKonsumenId,
                    'id_proyek'                    => $idProyek,
                    'action_type'                  => 'NUP_BARU',
                    'from_id_kavling'              => null,
                    'to_id_kavling'                => null,
                    'from_kavling_label'           => '-',
                    'to_kavling_label'             => 'NUP (Belum Pilih Kavling)',
                    'status_sebelum'               => '-',
                    'status_sesudah'               => 'NUP',
                    'nominal_booking_snapshot'     => $bookingFee,
                    'nominal_sudah_bayar_snapshot' => $bookingFee,
                    'catatan'                      => $catatanNup,
                    'file_lampiran'                => $fileNupPath,
                    'created_by'                   => $actorId,
                    'created_at'                   => $now,
                ]);
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new ServiceException('Transaksi database gagal saat menyimpan NUP.', 500);
            }

            return [
                'id_mkdt'     => $idMkdt,
                'id_konsumen' => (int) $savedKonsumenId,
                'booking'     => $bookingResult,
            ];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            if ($e instanceof ServiceException) {
                throw $e;
            }
            throw new ServiceException($e->getMessage(), 500, [], $e);
        }
    }

    /**
     * Menarik konsumen aktif dari kavling kembali menjadi status NUP.
     * Semua data konsumen, transaksi, dan pembayaran tetap tersimpan pada id_mkdt yang sama,
     * sedangkan kavling di siteplan menjadi kosong (id_mkdt = NULL).
     */
    public function pullToNup(array $post, ?UploadedFile $fileLampiran, int $actorId): array
    {
        $idMkdt    = (int) ($post['id_mkdt'] ?? 0);
        $idKavling = (int) ($post['id_kavling'] ?? 0);
        $catatan   = trim((string) ($post['catatan_nup'] ?? ''));

        if ($idMkdt <= 0 || $idKavling <= 0) {
            throw new ServiceException('Parameter transaksi dan kavling tidak valid.', 422);
        }
        if ($catatan === '') {
            throw new ServiceException('Catatan / alasan penarikan konsumen ke NUP wajib diisi.', 422);
        }

        $now = date('Y-m-d H:i:s');

        $this->db->transStart();

        try {
            $kavling = $this->nupRepo->getKavlingRow($idKavling, true);
            if (! $kavling) {
                throw new ServiceException('Data kavling tidak ditemukan.', 404);
            }

            $mkdt = $this->nupRepo->getNupDetail($idMkdt, true);
            if (! $mkdt) {
                throw new ServiceException('Data transaksi konsumen tidak ditemukan.', 404);
            }

            if ((int) ($kavling['id_mkdt'] ?? 0) !== $idMkdt && (int) ($mkdt['id_kavling'] ?? 0) !== $idKavling) {
                throw new ServiceException('Konsumen sudah tidak terdaftar aktif pada kavling tersebut.', 422);
            }

            $oldStatus = trim((string) ($mkdt['status_mkdt'] ?? 'Booking'));
            if (strtolower($oldStatus) === 'batal' || (int) ($mkdt['is_batal'] ?? 0) === 1) {
                throw new ServiceException('Transaksi yang sudah dibatalkan tidak dapat ditarik ke NUP.', 422);
            }
            if (strtolower($oldStatus) === 'nup') {
                throw new ServiceException('Konsumen ini sudah berada dalam status NUP.', 422);
            }

            $idProyek   = (int) ($kavling['id_proyek'] ?? $mkdt['id_proyek'] ?? 0);
            $idKonsumen = (int) ($mkdt['id_konsumen'] ?? 0);
            $fromLabel  = $this->nupRepo->getKavlingLabel($idKavling) ?? ('Kavling #' . $idKavling);

            $filePath = null;
            if ($fileLampiran instanceof UploadedFile && $fileLampiran->isValid() && ! $fileLampiran->hasMoved()) {
                $filePath = $this->storageService->store($fileLampiran, 'uploads/mkdt/nup/' . date('Ymd'));
            }

            $totalBooking    = (float) ($mkdt['total_booking'] ?? $mkdt['booking_fee'] ?? 0);
            $totalSudahBayar = (float) ($mkdt['total_sudah_bayar'] ?? $totalBooking);

            // 1. Kosongkan kavling di siteplan (id_mkdt = NULL)
            $this->db->table('kavling')
                ->where('id_kavling', $idKavling)
                ->update([
                    'id_mkdt'    => null,
                    'edit_by'    => $actorId,
                    'updated_at' => $now,
                ]);

            // 2. Update status konsumen menjadi NUP (id_kavling = NULL)
            if ($idKonsumen > 0) {
                $this->konsumenModel->update($idKonsumen, [
                    'id_kavling'      => null,
                    'status_konsumen' => 'NUP',
                    'edit_by'         => $actorId,
                ]);
            }

            $noNup = trim((string) ($post['no_nup'] ?? ''));
            if ($noNup === '') {
                throw new ServiceException('Nomor Urut Pemesanan (No NUP) wajib diisi manual saat menarik konsumen ke NUP.', 422);
            }
            if ($this->nupRepo->isNoNupExists($noNup, $idProyek, $idMkdt)) {
                throw new ServiceException("Nomor NUP '{$noNup}' sudah digunakan pada proyek ini. Harap gunakan nomor NUP yang berbeda.", 422);
            }

            // 3. Update mkdt ke status NUP dengan tetap menyimpan seluruh data biaya & pembayaran
            $updateMkdt = [
                'no_nup'          => $noNup,
                'status_mkdt'     => 'NUP',
                'id_kavling'      => null,
                'id_proyek'       => $idProyek > 0 ? $idProyek : ($mkdt['id_proyek'] ?? null),
                'last_id_kavling' => $idKavling,
                'nup_source'      => 'TARIKAN',
                'catatan_nup'     => $catatan,
                'nup_at'          => $now,
                'nup_by'          => $actorId,
                'edit_by'         => $actorId,
            ];
            if ($filePath !== null) {
                $updateMkdt['file_nup'] = $filePath;
            }
            if (! $this->mkdtModel->update($idMkdt, $updateMkdt)) {
                throw new ServiceException('Gagal mengubah status transaksi menjadi NUP.', 500);
            }

            // 4. Lepas id_kavling pada finance_ledger untuk id_mkdt ini agar tidak mengikat kavling kosong
            if ($this->db->tableExists('finance_ledger')) {
                $this->db->table('finance_ledger')
                    ->where('id_mkdt', $idMkdt)
                    ->update([
                        'id_kavling' => null,
                        'edit_by'    => $actorId,
                        'updated_at' => $now,
                    ]);
            }

            // 5. Catat riwayat kavling & NUP
            $historyId = $this->nupRepo->insertKavlingHistory([
                'id_mkdt'                      => $idMkdt,
                'id_konsumen'                  => $idKonsumen > 0 ? $idKonsumen : null,
                'id_proyek'                    => $idProyek > 0 ? $idProyek : null,
                'action_type'                  => 'TARIK_KE_NUP',
                'from_id_kavling'              => $idKavling,
                'to_id_kavling'                => null,
                'from_kavling_label'           => $fromLabel,
                'to_kavling_label'             => 'NUP (Belum Pilih Kavling)',
                'status_sebelum'               => $oldStatus,
                'status_sesudah'               => 'NUP',
                'nominal_booking_snapshot'     => $totalBooking,
                'nominal_sudah_bayar_snapshot' => $totalSudahBayar,
                'catatan'                      => $catatan,
                'file_lampiran'                => $filePath,
                'created_by'                   => $actorId,
                'created_at'                   => $now,
            ]);

            // 6. Catat juga pada history_logs kavling
            $namaKonsumen = $mkdt['nama_konsumen'] ?? '-';
            $this->mkdtHistoryService->log(
                $idKavling,
                $idMkdt,
                MkdtHistoryService::ACTION_TARIK_KE_NUP,
                "Konsumen {$namaKonsumen} ditarik dari {$fromLabel} ke daftar NUP. Alasan: {$catatan}",
                [
                    'id_mkdt'          => $idMkdt,
                    'id_konsumen'      => $idKonsumen,
                    'nama_konsumen'    => $namaKonsumen,
                    'status_mkdt'      => $oldStatus,
                    'kavling'          => $fromLabel,
                    'sudah_bayar'      => $totalSudahBayar,
                ],
                [
                    'id_mkdt'          => $idMkdt,
                    'status_mkdt'      => 'NUP',
                    'kavling'          => 'Kosong (Ditarik ke NUP)',
                    'catatan_nup'      => $catatan,
                    'history_id'       => $historyId,
                ],
                $actorId
            );

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new ServiceException('Transaksi database gagal saat menarik konsumen ke NUP.', 500);
            }

            return [
                'id_mkdt'            => $idMkdt,
                'id_konsumen'        => $idKonsumen,
                'from_id_kavling'    => $idKavling,
                'from_kavling_label' => $fromLabel,
                'history_id'         => $historyId,
            ];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            if ($e instanceof ServiceException) {
                throw $e;
            }
            throw new ServiceException($e->getMessage(), 500, [], $e);
        }
    }

    public function getKavlingOptions(
        int $idProyek,
        ?string $search = null,
        ?int $currentIdKavling = null,
        ?int $lastIdKavling = null,
        ?string $filterHargaJual = null
    ): array {
        if ($idProyek <= 0) {
            return [];
        }

        return $this->nupRepo->getKavlingOptionsForTransfer(
            $idProyek,
            $search,
            $currentIdKavling,
            $lastIdKavling,
            $filterHargaJual
        );
    }

    public function getFormattedKavlingHistory(int $idMkdt = 0, int $idKavling = 0): array
    {
        $rows = $this->nupRepo->getKavlingHistory($idMkdt, $idKavling);
        foreach ($rows as &$row) {
            $histId = (int) ($row['id'] ?? 0);
            $row['file_lampiran_url'] = (! empty($row['file_lampiran']) && $histId > 0)
                ? $this->fileAccessService->accessUrl('mkdt_kavling_history', $histId)
                : null;

            $actionMap = [
                'NUP_BARU'       => 'Input NUP Baru',
                'AKTIFKAN_NUP'   => 'Aktivasi NUP ke Kavling',
                'TARIK_KE_NUP'   => 'Penarikan Konsumen ke NUP',
                'PINDAH_KAVLING' => 'Pindah Kavling',
            ];
            $row['action_label'] = $actionMap[$row['action_type'] ?? ''] ?? ($row['action_type'] ?? '-');
        }
        unset($row);

        return $rows;
    }

    private function cleanNumeric(mixed $value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }
        return (float) preg_replace('/[^\d.\-]/', '', str_replace(',', '', (string) $value));
    }
}

<?php

namespace App\Services\Mkdt;

use App\Enums\NotificationEvent;
use App\Repositories\Mkdt\BatalBookingRepository;
use App\Services\FileAccessService;
use App\Services\Notifikasi\NotifikasiService;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\Files\UploadedFile;

class BatalBookingService
{
    private BatalBookingRepository $batalRepo;
    private FileAccessService $fileAccessService;
    private MkdtHistoryService $mkdtHistoryService;
    private NotifikasiService $notifikasiService;
    private ?BaseConnection $db;

    public function __construct(
        ?BatalBookingRepository $batalRepo = null,
        ?FileAccessService $fileAccessService = null,
        ?MkdtHistoryService $mkdtHistoryService = null,
        ?NotifikasiService $notifikasiService = null,
        ?BaseConnection $db = null
    ) {
        $this->batalRepo          = $batalRepo ?? new BatalBookingRepository();
        $this->fileAccessService  = $fileAccessService ?? new FileAccessService();
        $this->mkdtHistoryService = $mkdtHistoryService ?? new MkdtHistoryService();
        $this->notifikasiService  = $notifikasiService ?? new NotifikasiService();
        $this->db                 = $db;
    }

    protected function getDb(): BaseConnection
    {
        if ($this->db === null) {
            $this->db = \Config\Database::connect();
        }

        return $this->db;
    }

    /**
     * Sanitasi nilai numerik dari DB atau input agar aman dari pemisah ribuan.
     */
    public function cleanNumeric(mixed $value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        $clean = str_replace(',', '', trim((string) $value));
        $clean = preg_replace('/[^\d.\-]/', '', $clean) ?? '';
        if ($clean === '') {
            return 0.0;
        }

        if (substr_count($clean, '.') > 1) {
            $clean = str_replace('.', '', $clean);
        }

        return is_numeric($clean) ? (float) $clean : 0.0;
    }

    /**
     * Ambil detail booking untuk modal/form Pembatalan Booking, menghitung ulang total biaya & sudah bayar dari DB.
     *
     * @return array{
     *     found: bool,
     *     data: ?object,
     *     sudah_bayar: array{uang_muka: float, biaya_biaya: float},
     *     total_biaya: array{uang_muka: float, biaya_biaya: float}
     * }
     */
    public function getDetail(int $idMkdt): array
    {
        $emptySummary = [
            'uang_muka'   => 0.0,
            'biaya_biaya' => 0.0,
        ];

        if ($idMkdt <= 0) {
            return [
                'found'       => false,
                'data'        => null,
                'sudah_bayar' => $emptySummary,
                'total_biaya' => $emptySummary,
            ];
        }

        $row = $this->batalRepo->getMkdtDetailForBatal($idMkdt);
        if (!$row) {
            return [
                'found'       => false,
                'data'        => null,
                'sudah_bayar' => $emptySummary,
                'total_biaya' => $emptySummary,
            ];
        }

        $totalUm = $this->cleanNumeric($row->harga_uang_muka ?? 0)
            + $this->cleanNumeric($row->harga_administrasi ?? 0)
            - $this->cleanNumeric($row->harga_penambahan_um ?? 0)
            - $this->cleanNumeric($row->harga_diskon_uang_muka ?? 0);

        $totalBb = $this->cleanNumeric($row->harga_ppn ?? 0)
            + $this->cleanNumeric($row->harga_bphtb ?? 0)
            + $this->cleanNumeric($row->harga_biaya_proses ?? 0);

        $logs = $this->batalRepo->getPembayaranLogsByMkdt((int) $row->id_mkdt);
        $sbUm = 0.0;
        $sbBb = 0.0;

        foreach ($logs as $log) {
            $paymentType = (string) ($log->payment_type ?? '');
            $nominal     = $this->cleanNumeric($log->nominal ?? 0);

            if ($paymentType === 'UangMuka') {
                $sbUm += $nominal;
            } elseif ($paymentType === 'BiayaBiaya') {
                $sbBb += $nominal;
            }
        }

        $row->surat_batal_access_url = !empty($row->surat_batal)
            ? $this->fileAccessService->accessUrl('mkdt_surat_batal', (int) $row->id_mkdt)
            : null;

        return [
            'found' => true,
            'data'  => $row,
            'sudah_bayar' => [
                'uang_muka'   => $sbUm,
                'biaya_biaya' => $sbBb,
            ],
            'total_biaya' => [
                'uang_muka'   => $totalUm,
                'biaya_biaya' => $totalBb,
            ],
        ];
    }

    /**
     * Batalkan booking MKDT: simpan file surat batal SEBELUM update DB, catat audit trail, dan kirim notifikasi.
     *
     * @param array<string, mixed>     $input
     * @param array<int, int|string>   $userGroupIds
     * @return array{success: bool, not_found: bool, messages: string, data?: array<string, mixed>, errors?: array<string, string>}
     */
    public function cancelBooking(array $input, ?UploadedFile $suratBatalFile, int $actorId, array $userGroupIds): array
    {
        $idMkdt      = (int) ($input['batal-id_mkdt'] ?? $input['id_mkdt'] ?? 0);
        $idKavling   = (int) ($input['batal-id_kavling'] ?? $input['id_kavling'] ?? 0);
        $idKonsumen  = (int) ($input['batal-id_konsumen'] ?? $input['id_konsumen'] ?? 0);
        $keterangan  = trim((string) ($input['batal-keterangan_batal'] ?? $input['keterangan_batal'] ?? ''));
        $perluRefund = (int) (($input['batal-perlu_refund'] ?? $input['perlu_refund'] ?? 0) == 1);

        $errors = [];
        if ($idMkdt <= 0) {
            $errors['id_mkdt'] = 'ID MKDT wajib diisi';
        }
        if ($idKavling <= 0) {
            $errors['id_kavling'] = 'ID Kavling wajib diisi';
        }
        if (!empty($errors)) {
            return [
                'success'   => false,
                'not_found' => false,
                'messages'  => 'Data pembatalan booking tidak valid',
                'errors'    => $errors,
            ];
        }

        $oldMkdt = $this->batalRepo->getMkdtRawById($idMkdt);
        if (!$oldMkdt) {
            return [
                'success'   => false,
                'not_found' => true,
                'messages'  => 'Data booking MKDT tidak ditemukan',
                'errors'    => ['id_mkdt' => 'Data booking MKDT tidak ditemukan'],
            ];
        }

        if ($idKonsumen <= 0) {
            $idKonsumen = (int) ($oldMkdt->id_konsumen ?? 0);
        }

        $now        = date('Y-m-d H:i:s');
        $isKeuangan = in_array(3, array_map('intval', $userGroupIds), true);

        $updateData = [
            'keterangan_batal' => $keterangan,
            'perlu_refund'     => $perluRefund,
            'is_batal'         => 1,
            'status_mkdt'      => 'Batal',
        ];

        if ($isKeuangan) {
            $updateData['keuangan_batal_oleh'] = $actorId;
            $updateData['keuangan_batal_tgl']  = $now;
        } else {
            $updateData['mkdt_batal_oleh'] = $actorId;
            $updateData['mkdt_batal_tgl']  = $now;
        }

        if ($suratBatalFile instanceof UploadedFile && $suratBatalFile->isValid() && !$suratBatalFile->hasMoved() && $suratBatalFile->getSize() > 0) {
            $name                      = $suratBatalFile->getRandomName();
            $lok                       = 'uploads/batal/' . date('Ymd') . '/';
            $storedPath                = $this->fileAccessService->storeAs($suratBatalFile, $lok, $name);
            $updateData['surat_batal'] = $storedPath;
        }

        $db = $this->getDb();
        $db->transStart();
        $updated = $this->batalRepo->updateMkdtBatal($idMkdt, $updateData);
        $db->transComplete();

        if (!$updated || $db->transStatus() === false) {
            return [
                'success'   => false,
                'not_found' => false,
                'messages'  => 'Terjadi kesalahan saat menyimpan pembatalan booking',
            ];
        }

        $this->mkdtHistoryService->log(
            $idKavling,
            $idMkdt,
            MkdtHistoryService::ACTION_BATAL_BOOKING,
            $this->mkdtHistoryService->buildBatalSummary($updateData),
            (array) $oldMkdt,
            $updateData,
            $actorId
        );

        $idProyek = $this->batalRepo->getProyekIdByKavling($idKavling);
        $this->notifikasiService->tambah_notif(
            '3;4;9',
            'Membatalkan booking',
            $actorId,
            $idKavling,
            $idKonsumen > 0 ? $idKonsumen : null,
            NotificationEvent::BATAL_BOOKING,
            $idProyek,
            "siteplan/view?id_kavling={$idKavling}&tab=konsumen"
        );

        $suratBatalPath = $updateData['surat_batal'] ?? ($oldMkdt->surat_batal ?? null);
        $resultData     = array_merge((array) $oldMkdt, $updateData, [
            'id_mkdt'                => $idMkdt,
            'id_kavling'             => $idKavling,
            'id_konsumen'            => $idKonsumen > 0 ? $idKonsumen : null,
            'surat_batal_access_url' => !empty($suratBatalPath)
                ? $this->fileAccessService->accessUrl('mkdt_surat_batal', $idMkdt)
                : null,
        ]);

        return [
            'success'   => true,
            'not_found' => false,
            'messages'  => 'Pembatalan booking berhasil disimpan',
            'data'      => $resultData,
        ];
    }
}

<?php

namespace App\Services\Keuangan;

use App\Enums\NotificationEvent;
use App\Repositories\Keuangan\DireksiRepository;
use App\Services\Bpb\ProfileSignatureService;
use App\Services\FileAccessService;
use App\Services\Notifikasi\NotifikasiService;
use App\Services\Proyek\ActiveProyekService;
use CodeIgniter\Database\BaseConnection;
use Myth\Auth\Password;
use PhpOffice\PhpSpreadsheet\Reader\Html as HtmlReader;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class DireksiService
{
    protected DireksiRepository $repo;
    protected FileAccessService $fileAccessService;
    protected ProfileSignatureService $profileSignatureService;
    protected NotifikasiService $notif;
    protected ActiveProyekService $activeProyekService;
    protected BaseConnection $db;

    public function __construct(
        ?DireksiRepository $repo = null,
        ?FileAccessService $fileAccessService = null,
        ?ProfileSignatureService $profileSignatureService = null,
        ?NotifikasiService $notif = null,
        ?ActiveProyekService $activeProyekService = null,
        ?BaseConnection $db = null
    ) {
        $this->repo = $repo ?? new DireksiRepository();
        $this->fileAccessService = $fileAccessService ?? new FileAccessService();
        $this->profileSignatureService = $profileSignatureService ?? new ProfileSignatureService();
        $this->notif = $notif ?? new NotifikasiService();
        $this->activeProyekService = $activeProyekService ?? new ActiveProyekService();
        $this->db = $db ?? db_connect();
    }

    public function getMetaPayload(?int $activeProjectId = null): array
    {
        $activeProyek = $this->activeProyekService->resolveAndGet($activeProjectId);
        $canApprove = ! function_exists('in_groups') || in_groups(['1', '9']);

        return [
            'active_project' => $activeProyek ? [
                'id'   => (int) $activeProyek->id_proyek,
                'name' => (string) ($activeProyek->nama_proyek ?? ''),
            ] : null,
            'signature_methods' => ['profile', 'canvas'],
            'approval_statuses' => [
                ['code' => 0, 'label' => 'Pending'],
                ['code' => 1, 'label' => 'Disetujui'],
                ['code' => 2, 'label' => 'Ditolak'],
            ],
            'permissions' => [
                'can_view'     => true,
                'can_diskresi' => $canApprove,
                'can_sign'     => $canApprove,
                'can_reject'   => $canApprove,
            ],
        ];
    }

    public function getDiskresiData(int $idKavling, ?int $actorId = null): array
    {
        if ($idKavling <= 0) {
            return [
                'data'        => null,
                'harga_akhir' => [],
            ];
        }

        $bundle = $this->repo->getDiskresiByKavling($idKavling);
        $uid = $actorId ?: (function_exists('user_id') && user_id() ? (int) user_id() : 0);

        if (! empty($bundle['harga_akhir'])) {
            foreach ($bundle['harga_akhir'] as $row) {
                if (! empty($row->id_filehj)) {
                    $fileId = (int) $row->id_filehj;
                    if ($uid > 0) {
                        $row->access_url = $this->fileAccessService->signedUrl('file_hargajual', $fileId, 3600, null, false, $uid);
                        $row->download_url = $this->fileAccessService->signedUrl('file_hargajual', $fileId, 3600, null, true, $uid);
                    } else {
                        $row->access_url = $this->fileAccessService->accessUrl('file_hargajual', $fileId);
                        $row->download_url = $this->fileAccessService->accessUrl('file_hargajual', $fileId, true);
                    }
                }
            }
        }

        return $bundle;
    }

    public function saveDiskresi(int $idKavling, array $postData, int $actorId): array
    {
        if ($idKavling <= 0) {
            return [
                'success'  => false,
                'messages' => 'ID kavling tidak valid.',
            ];
        }

        $harga = $this->cleanNumeric($postData['dir-diskresi_harga'] ?? 0);
        $memo = (string) ($postData['dir-diskresi_memo'] ?? '');

        $payload = [
            'diskresi_harga' => $harga,
            'diskresi_memo'  => $memo,
            'diskresi_at'    => date('Y-m-d H:i:s'),
            'diskresi_oleh'  => $actorId,
        ];

        $updated = $this->repo->updateDiskresiKavling($idKavling, $payload);

        if ($updated) {
            $notif = 'Diskresi Harga Jual : Rp. ' . number_format($harga, 0, ',', '.') . ' - ' . $memo;
            $this->notif->tambah_notif(
                '3;4;9',
                $notif,
                $actorId,
                $idKavling,
                null,
                NotificationEvent::DISKRESI_HARGA,
                null,
                'siteplan/view?id_kavling=' . $idKavling . '&tab=konsumen'
            );

            return [
                'success'  => true,
                'messages' => 'Berhasil melakukan perubahan data',
                'data'     => [
                    'id_kavling'     => $idKavling,
                    'diskresi_harga' => $harga,
                    'diskresi_memo'  => $memo,
                    'diskresi_at'    => $payload['diskresi_at'],
                    'diskresi_oleh'  => $actorId,
                ],
            ];
        }

        return [
            'success'  => false,
            'messages' => 'Terjadi kesaahan saat melakukan perubahan data',
        ];
    }

    public function exportXlsx(string $tableHtml): array
    {
        $firstHtmlString = '<table>' . $tableHtml . '</table>';

        $reader = new HtmlReader();
        $spreadsheet = $reader->loadFromString($firstHtmlString);
        $reader->setSheetIndex(1);

        ob_start();
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        $xlsData = (string) ob_get_contents();
        ob_end_clean();

        return [
            'status' => true,
            'file'   => 'data:application/vnd.ms-excel;base64,' . base64_encode($xlsData),
        ];
    }

    public function getPendingTagihan(): array
    {
        $list = $this->repo->getPendingTagihan();

        return $this->attachTotalNominal($list);
    }

    public function getHistoryTagihan(): array
    {
        $list = $this->repo->getHistoryTagihan();

        return $this->attachTotalNominal($list);
    }

    public function getMySignature(int $userId): array
    {
        return $this->profileSignatureService->get($userId);
    }

    public function signTagihan(
        string $noInv,
        string $password,
        string $signatureMethod,
        string $signatureData,
        int $userId
    ): array {
        if ($noInv === '') {
            return ['success' => false, 'message' => 'ID Surat tidak ditemukan.'];
        }

        if ($password === '') {
            return ['success' => false, 'message' => 'Password wajib diisi untuk menandatangani surat.'];
        }

        $user = $this->repo->findUserById($userId);
        if (! $user || ! Password::verify($password, (string) $user->password_hash)) {
            return ['success' => false, 'message' => 'Password yang Anda masukkan salah.'];
        }

        if ($signatureMethod === 'canvas') {
            if ($signatureData === '' || ! str_starts_with($signatureData, 'data:image/')) {
                return ['success' => false, 'message' => 'Silakan buat tanda tangan pada kanvas terlebih dahulu.'];
            }

            try {
                $this->profileSignatureService->save($userId, $password, $signatureData);
            } catch (\Throwable $e) {
                return ['success' => false, 'message' => 'Gagal menyimpan tanda tangan kanvas: ' . $e->getMessage()];
            }
        } else {
            $profile = $this->profileSignatureService->get($userId);
            if (empty($profile['has_signature'])) {
                return ['success' => false, 'message' => 'Anda belum memiliki tanda tangan di profil. Silakan pilih opsi Gambar di Kanvas.'];
            }
        }

        $signedAt = date('Y-m-d H:i:s');
        $updated = $this->repo->updateInvoiceSignature($noInv, [
            'is_signed_direktur' => 1,
            'signed_at'          => $signedAt,
            'signed_by'          => $userId,
        ]);

        if ($updated) {
            return [
                'success' => true,
                'message' => 'Surat tagihan berhasil ditandatangani.',
                'data'    => [
                    'no_inv'    => $noInv,
                    'signed_at' => $signedAt,
                    'signed_by' => $userId,
                ],
            ];
        }

        return ['success' => false, 'message' => 'Gagal menandatangani surat.'];
    }

    public function rejectTagihan(string $noInv, string $alasan, string $password, int $userId): array
    {
        $alasan = trim($alasan);

        if ($noInv === '') {
            return ['success' => false, 'message' => 'ID Surat tidak ditemukan.'];
        }

        if ($alasan === '') {
            return ['success' => false, 'message' => 'Alasan penolakan wajib diisi.'];
        }

        if ($password === '') {
            return ['success' => false, 'message' => 'Password wajib diisi untuk konfirmasi penolakan.'];
        }

        $user = $this->repo->findUserById($userId);
        if (! $user || ! Password::verify($password, (string) $user->password_hash)) {
            return ['success' => false, 'message' => 'Password yang Anda masukkan salah.'];
        }

        $now = date('Y-m-d H:i:s');
        $this->db->transBegin();

        try {
            $update = $this->repo->updateInvoiceSignature($noInv, [
                'is_signed_direktur' => 2,
                'signed_at'          => $now,
                'signed_by'          => $userId,
                'keterangan_status'  => $alasan,
            ]);

            if (! $update) {
                $this->db->transRollback();

                return ['success' => false, 'message' => 'Gagal menolak surat tagihan.'];
            }

            $this->repo->insertInvoiceStatusLog([
                'no_inv'     => $noInv,
                'status'     => 'ditolak',
                'tanggal'    => date('Y-m-d'),
                'keterangan' => 'Tanda tangan ditolak oleh Direksi: ' . $alasan,
                'add_by'     => $userId,
                'date_add'   => $now,
            ]);

            if ($this->db->transStatus() === false) {
                $this->db->transRollback();

                return ['success' => false, 'message' => 'Gagal menolak surat tagihan.'];
            }

            $this->db->transCommit();
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', '[DireksiService::rejectTagihan] ' . $e->getMessage());

            return ['success' => false, 'message' => 'Gagal menolak surat tagihan.'];
        }

        try {
            $inv = $this->repo->findInvoiceByNoInv($noInv);
            if ($inv && ! empty($inv->add_by)) {
                $msg = "Tanda tangan surat tagihan {$inv->nomor_surat} ditolak oleh Direksi. Alasan: {$alasan}";
                $this->notif->tambah_notif(
                    '3',
                    $msg,
                    $userId,
                    $inv->id_kavling ?? null,
                    null,
                    null,
                    null,
                    'tagihan/list'
                );
            }
        } catch (\Throwable) {
            // Ignore notif error
        }

        return [
            'success' => true,
            'message' => 'Surat tagihan berhasil ditolak.',
            'data'    => [
                'no_inv'             => $noInv,
                'is_signed_direktur' => 2,
                'signed_at'          => $now,
                'signed_by'          => $userId,
                'keterangan_status'  => $alasan,
            ],
        ];
    }

    protected function attachTotalNominal(array $list): array
    {
        foreach ($list as $item) {
            $totalNominal = 0.0;
            if (! empty($item->tagihan)) {
                $raw = $item->tagihan;
                if (is_string($raw)) {
                    $raw = json_decode(str_replace('&quot;', '"', $raw), true);
                }
                if (is_array($raw)) {
                    foreach ($raw as $t) {
                        $totalNominal += (float) ($t['nominal'] ?? 0);
                    }
                }
            }
            $item->total_nominal = $totalNominal;
        }

        return $list;
    }

    protected function cleanNumeric(mixed $value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        $cleaned = str_replace(['.', ','], '', (string) $value);

        return (float) $cleaned;
    }
}

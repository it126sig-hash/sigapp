<?php

namespace App\Services\Transaksi;

use App\Data\NotificationAudience;
use App\Data\NotificationData;
use App\Models\KavlingRequestModel;
use App\Services\NotifikasiService;
use CodeIgniter\Database\BaseConnection;
use RuntimeException;

class KavlingRequestService
{
    protected BaseConnection $db;
    protected KavlingRequestModel $requestModel;
    protected NotifikasiService $notifikasiService;

    public function __construct(
        ?BaseConnection $db = null,
        ?KavlingRequestModel $requestModel = null,
        ?NotifikasiService $notifikasiService = null
    ) {
        $this->db = $db ?? db_connect();
        $this->requestModel = $requestModel ?? new KavlingRequestModel($this->db);
        $this->notifikasiService = $notifikasiService ?? new NotifikasiService();
    }

    /**
     * Submit request tambah atau ubah kavling dari user non-planning (mendukung single maupun batch ubah_tipe)
     */
    public function submitRequest(array $data, int $userId): array
    {
        $idProyek = (int) ($data['id_proyek'] ?? 0);
        if ($idProyek <= 0) {
            throw new RuntimeException('Proyek tidak valid.');
        }

        $jenisRequest = $data['jenis_request'] ?? 'tambah_baru';
        if (! in_array($jenisRequest, ['tambah_baru', 'ubah_tipe'], true)) {
            throw new RuntimeException('Jenis request tidak valid.');
        }

        if (array_key_exists('items', $data) || array_key_exists('id_kavlings', $data)) {
            return $this->submitBatchUbahTipe($idProyek, $jenisRequest, $data, $userId);
        }

        $idKavling  = ! empty($data['id_kavling']) ? (int) $data['id_kavling'] : null;
        $idCluster  = ! empty($data['id_cluster']) ? (int) $data['id_cluster'] : null;
        $idJalan    = ! empty($data['id_jalan']) ? (int) $data['id_jalan'] : null;
        $idTipe     = ! empty($data['id_tipe']) ? (int) $data['id_tipe'] : null;
        $tipeManual = trim((string) ($data['tipe_manual'] ?? ''));
        if (mb_strlen($tipeManual) > 100) {
            throw new RuntimeException('Tipe rumah manual maksimal 100 karakter.');
        }
        $keterangan = trim((string) ($data['keterangan'] ?? ''));

        if ($idTipe === null && $tipeManual === '') {
            throw new RuntimeException('Tipe rumah wajib diisi.');
        }

        if ($jenisRequest === 'ubah_tipe' && empty($idKavling)) {
            throw new RuntimeException('Kavling existing harus dipilih untuk request perubahan tipe.');
        }

        // Jika cluster dan/atau jalan belum ada/tidak dipilih, keterangan wajib diisi untuk catatan manual
        if ($jenisRequest === 'tambah_baru' && (empty($idCluster) || empty($idJalan)) && empty($keterangan)) {
            throw new RuntimeException('Keterangan wajib diisi apabila Cluster dan/atau Jalan belum dipilih.');
        }

        if ($jenisRequest === 'ubah_tipe' && $idKavling !== null) {
            $existingPending = $this->findPendingUbahTipeMap($idProyek, [$idKavling]);
            if (isset($existingPending[$idKavling])) {
                throw new RuntimeException('Kavling ini sudah memiliki pengajuan ubah tipe yang berstatus pending untuk tim Planning.');
            }
        }

        $this->db->transStart();

        $insertData = [
            'id_proyek'     => $idProyek,
            'jenis_request' => $jenisRequest,
            'id_kavling'    => $idKavling,
            'id_cluster'    => $idCluster,
            'id_jalan'      => $idJalan,
            'id_tipe'       => $idTipe,
            'tipe_manual'   => $tipeManual !== '' ? $tipeManual : null,
            'keterangan'    => $keterangan,
            'status'        => 'pending',
            'created_by'    => $userId,
        ];

        $requestId = $this->requestModel->insert($insertData);
        if (! $requestId) {
            $this->db->transRollback();
            throw new RuntimeException('Gagal menyimpan pengajuan request kavling.');
        }

        [$namaProyek, $pengaju] = $this->resolveProyekAndUserLabels($idProyek, $userId);

        // Siapkan pesan notifikasi
        $pesanNotif = '';
        $actionUrl = 'siteplan/view';

        if ($jenisRequest === 'ubah_tipe') {
            $pesanNotif = $this->buildSingleUbahTipeNotifMessage(
                (int) $idKavling,
                $idTipe,
                $tipeManual,
                $keterangan,
                $pengaju,
                $namaProyek
            );
            $actionUrl = "siteplan/view?id_kavling={$idKavling}";
        } else {
            $clusterRow = $idCluster ? $this->db->table('cluster')->select('nama_cluster')->where('id_cluster', $idCluster)->get()->getRow() : null;
            $jalanRow   = $idJalan ? $this->db->table('jalan')->select('nama_jalan')->where('id_jalan', $idJalan)->get()->getRow() : null;

            $lokasiParts = [];
            if ($clusterRow) {
                $lokasiParts[] = "Cluster: " . $clusterRow->nama_cluster;
            }
            if ($jalanRow) {
                $lokasiParts[] = "Jalan: " . $jalanRow->nama_jalan;
            }

            $lokasiText = ! empty($lokasiParts) ? implode(', ', $lokasiParts) : 'Cluster/Jalan Manual';

            $tipeRow = $idTipe ? $this->db->table('tipe')->select('no_tipe_rumah, tipe_rumah')->where('id_tipe', $idTipe)->get()->getRow() : null;
            $tipeParts = [];
            if ($tipeRow) {
                $tipeParts[] = "{$tipeRow->no_tipe_rumah} ({$tipeRow->tipe_rumah})";
            }
            if ($tipeManual !== '') {
                $tipeParts[] = $tipeManual;
            }
            $tipeText = ! empty($tipeParts) ? ' Tipe: ' . implode(' / ', $tipeParts) . '.' : '';

            $pesanNotif = "[Request Siteplan] {$pengaju} mengajukan penambahan kavling baru ({$lokasiText}).{$tipeText} di {$namaProyek}." .
                ($keterangan !== '' ? " Catatan: {$keterangan}" : '');
        }

        // Kirim notifikasi in-app dan email ke Tim Planning (Group 6)
        $notifData = new NotificationData(
            message: $pesanNotif,
            actorUserId: $userId,
            idKavling: $idKavling,
            idKonsumen: null,
            type: \App\Enums\NotificationEvent::REQUEST_KAVLING,
            idProyek: $idProyek,
            actionUrl: $actionUrl,
            eventType: \App\Enums\NotificationEvent::REQUEST_KAVLING
        );

        $audience = NotificationAudience::forGroups([6]); // Group 6 = Planning
        $this->notifikasiService->create($notifData, $audience);

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            throw new RuntimeException('Gagal memproses transaksi request kavling.');
        }

        return [
            'id_request' => $requestId,
            'message'    => 'Pengajuan request berhasil dikirim ke tim Planning.',
        ];
    }

    private function submitBatchUbahTipe(int $idProyek, string $jenisRequest, array $data, int $userId): array
    {
        if ($jenisRequest !== 'ubah_tipe') {
            throw new RuntimeException('Pengiriman batch hanya didukung untuk jenis request ubah_tipe.');
        }

        $items = $this->normalizeBatchItems($data);
        if ($items === []) {
            throw new RuntimeException('Minimal 1 kavling harus dipilih untuk pengajuan perubahan tipe.');
        }

        $kavlingIds = array_map(static fn (array $item): int => $item['id_kavling'], $items);
        $existingPendingMap = $this->findPendingUbahTipeMap($idProyek, $kavlingIds);

        $toInsert = [];
        $skippedPending = [];

        foreach ($items as $item) {
            $kId = $item['id_kavling'];
            if (isset($existingPendingMap[$kId])) {
                $skippedPending[] = [
                    'id_kavling' => $kId,
                    'id_request' => $existingPendingMap[$kId],
                    'reason'     => 'Kavling sudah memiliki pengajuan ubah tipe yang berstatus pending.',
                ];
                continue;
            }
            $toInsert[] = $item;
        }

        if ($toInsert === []) {
            throw new RuntimeException('Semua kavling yang dipilih sudah memiliki pengajuan ubah tipe yang berstatus pending.');
        }

        $this->db->transStart();

        $createdIds = [];
        foreach ($toInsert as $item) {
            $requestId = $this->requestModel->insert([
                'id_proyek'     => $idProyek,
                'jenis_request' => 'ubah_tipe',
                'id_kavling'    => $item['id_kavling'],
                'id_cluster'    => null,
                'id_jalan'      => null,
                'id_tipe'       => $item['id_tipe'],
                'tipe_manual'   => $item['tipe_manual'] !== '' ? $item['tipe_manual'] : null,
                'keterangan'    => $item['keterangan'],
                'status'        => 'pending',
                'created_by'    => $userId,
            ]);

            if (! $requestId) {
                $this->db->transRollback();
                throw new RuntimeException('Gagal menyimpan pengajuan request kavling.');
            }

            $createdIds[] = (int) $requestId;
        }

        [$namaProyek, $pengaju] = $this->resolveProyekAndUserLabels($idProyek, $userId);

        if (count($toInsert) === 1) {
            $single = $toInsert[0];
            $pesanNotif = $this->buildSingleUbahTipeNotifMessage(
                $single['id_kavling'],
                $single['id_tipe'],
                $single['tipe_manual'],
                $single['keterangan'],
                $pengaju,
                $namaProyek
            );
            $notifKavlingId = $single['id_kavling'];
            $actionUrl = 'siteplan/view?id_kavling=' . $single['id_kavling'];
        } else {
            $insertedKavlingIds = array_map(static fn (array $item): int => $item['id_kavling'], $toInsert);
            $kavlingRows = $this->db->table('kavling')
                ->select('kavling.id_kavling, kavling.no_kavling, jalan.nama_jalan')
                ->join('jalan', 'jalan.id_jalan = kavling.id_jalan', 'left')
                ->whereIn('kavling.id_kavling', $insertedKavlingIds)
                ->get()
                ->getResult();

            $labels = [];
            foreach ($kavlingRows as $kRow) {
                $jalan = trim((string) ($kRow->nama_jalan ?? ''));
                $noKav = trim((string) ($kRow->no_kavling ?? ''));
                $labels[] = $jalan !== '' ? ($jalan . ' No. ' . $noKav) : ($noKav !== '' ? $noKav : ('#' . $kRow->id_kavling));
            }

            $previewLabels = array_slice($labels, 0, 5);
            $previewText = implode(', ', $previewLabels);
            if (count($labels) > 5) {
                $previewText .= ', +' . (count($labels) - 5) . ' lainnya';
            }

            $count = count($toInsert);
            $pesanNotif = "[Request Siteplan] {$pengaju} mengajukan perubahan tipe untuk {$count} kavling" .
                ($previewText !== '' ? " ({$previewText})" : '') .
                " di {$namaProyek}.";
            $notifKavlingId = null;
            $actionUrl = 'siteplan/view';
        }

        $notifData = new NotificationData(
            message: $pesanNotif,
            actorUserId: $userId,
            idKavling: $notifKavlingId,
            idKonsumen: null,
            type: \App\Enums\NotificationEvent::REQUEST_KAVLING,
            idProyek: $idProyek,
            actionUrl: $actionUrl,
            eventType: \App\Enums\NotificationEvent::REQUEST_KAVLING
        );

        $audience = NotificationAudience::forGroups([6]); // Group 6 = Planning
        $this->notifikasiService->create($notifData, $audience);

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            throw new RuntimeException('Gagal memproses transaksi batch request kavling.');
        }

        $submittedCount = count($createdIds);
        $skippedCount = count($skippedPending);
        $message = $submittedCount === 1
            ? 'Pengajuan request berhasil dikirim ke tim Planning.'
            : ($submittedCount . ' pengajuan ubah tipe berhasil dikirim ke tim Planning.');
        if ($skippedCount > 0) {
            $message .= ' (' . $skippedCount . ' kavling dilewati karena masih berstatus pending.)';
        }

        return [
            'id_request'      => $createdIds[0],
            'created_ids'     => $createdIds,
            'submitted_count' => $submittedCount,
            'skipped_count'   => $skippedCount,
            'skipped_pending' => $skippedPending,
            'message'         => $message,
        ];
    }

    /**
     * @return array<int, array{id_kavling: int, id_tipe: ?int, tipe_manual: string, keterangan: string}>
     */
    private function normalizeBatchItems(array $data): array
    {
        $topIdTipe = ! empty($data['id_tipe']) ? (int) $data['id_tipe'] : null;
        $topTipeManual = trim((string) ($data['tipe_manual'] ?? ''));
        $topKeterangan = trim((string) ($data['keterangan'] ?? ''));

        $rawItems = [];
        if (isset($data['items']) && is_array($data['items'])) {
            $rawItems = $data['items'];
        } elseif (isset($data['id_kavlings'])) {
            $rawKavlings = $data['id_kavlings'];
            if (is_string($rawKavlings)) {
                $rawKavlings = preg_split('/[;,]+/', $rawKavlings) ?: [];
            }

            if (is_array($rawKavlings)) {
                $tipeByKavling = is_array($data['tipe_by_kavling'] ?? null)
                    ? $data['tipe_by_kavling']
                    : (is_array($data['target_tipes'] ?? null) ? $data['target_tipes'] : []);

                foreach ($rawKavlings as $idx => $entry) {
                    if (is_array($entry) || is_object($entry)) {
                        $rawItems[] = (array) $entry;
                        continue;
                    }

                    $kId = (int) trim((string) $entry);
                    if ($kId <= 0) {
                        continue;
                    }

                    $mapped = is_array($tipeByKavling[$kId] ?? null)
                        ? $tipeByKavling[$kId]
                        : (is_array($tipeByKavling[(string) $kId] ?? null) ? $tipeByKavling[(string) $kId] : []);

                    $rawItems[] = [
                        'id_kavling'  => $kId,
                        'id_tipe'     => $mapped['id_tipe'] ?? ($data['id_tipes'][$idx] ?? null),
                        'tipe_manual' => $mapped['tipe_manual'] ?? ($data['tipe_manuals'][$idx] ?? null),
                        'keterangan'  => $mapped['keterangan'] ?? ($data['keterangans'][$idx] ?? null),
                    ];
                }
            }
        }

        $normalized = [];
        $seen = [];

        foreach ($rawItems as $rawItem) {
            $item = is_object($rawItem) ? (array) $rawItem : $rawItem;
            if (! is_array($item)) {
                continue;
            }

            $kId = (int) ($item['id_kavling'] ?? 0);
            if ($kId <= 0) {
                throw new RuntimeException('Setiap item pengajuan harus memiliki id_kavling yang valid.');
            }

            if (isset($seen[$kId])) {
                continue;
            }
            $seen[$kId] = true;

            $itemIdTipe = ! empty($item['id_tipe']) ? (int) $item['id_tipe'] : null;
            $itemTipeManual = trim((string) ($item['tipe_manual'] ?? ''));

            if ($itemIdTipe === null && $itemTipeManual === '') {
                $itemIdTipe = $topIdTipe;
                $itemTipeManual = $topTipeManual;
            }

            if ($itemIdTipe === null && $itemTipeManual === '') {
                throw new RuntimeException('Tipe rumah wajib diisi untuk setiap kavling yang diajukan.');
            }

            if (mb_strlen($itemTipeManual) > 100) {
                throw new RuntimeException('Tipe rumah manual maksimal 100 karakter.');
            }

            $itemKeterangan = array_key_exists('keterangan', $item) && $item['keterangan'] !== null
                ? trim((string) $item['keterangan'])
                : $topKeterangan;

            $normalized[] = [
                'id_kavling'  => $kId,
                'id_tipe'     => $itemIdTipe,
                'tipe_manual' => $itemTipeManual,
                'keterangan'  => $itemKeterangan,
            ];
        }

        return $normalized;
    }

    /**
     * @param int[] $idKavlings
     * @return array<int, int> map id_kavling => id_request
     */
    private function findPendingUbahTipeMap(int $idProyek, array $idKavlings): array
    {
        $ids = array_values(array_unique(array_filter(
            array_map('intval', $idKavlings),
            static fn (int $id): bool => $id > 0
        )));

        if ($idProyek <= 0 || $ids === []) {
            return [];
        }

        $rows = $this->db->table('kavling_request')
            ->select('id_request, id_kavling')
            ->where('id_proyek', $idProyek)
            ->where('jenis_request', 'ubah_tipe')
            ->where('status', 'pending')
            ->whereIn('id_kavling', $ids)
            ->orderBy('id_request', 'desc')
            ->get()
            ->getResult();

        $map = [];
        foreach ($rows as $row) {
            $kId = (int) ($row->id_kavling ?? 0);
            if ($kId > 0 && ! isset($map[$kId])) {
                $map[$kId] = (int) $row->id_request;
            }
        }

        return $map;
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function resolveProyekAndUserLabels(int $idProyek, int $userId): array
    {
        $proyek = $this->db->table('proyek')->select('nama_proyek')->where('id_proyek', $idProyek)->get()->getRow();
        $namaProyek = $proyek->nama_proyek ?? ('Proyek #' . $idProyek);

        $userRow = $this->db->table('users')->select('name, username')->where('id', $userId)->get()->getRow();
        $pengaju = $userRow->name ?? ($userRow->username ?? ('User #' . $userId));

        return [$namaProyek, $pengaju];
    }

    private function buildSingleUbahTipeNotifMessage(
        int $idKavling,
        ?int $idTipe,
        string $tipeManual,
        string $keterangan,
        string $pengaju,
        string $namaProyek
    ): string {
        $kavlingRow = $this->db->table('kavling')
            ->select('kavling.no_kavling, jalan.nama_jalan')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan', 'left')
            ->where('kavling.id_kavling', $idKavling)
            ->get()
            ->getRow();

        $noKav = $kavlingRow ? ($kavlingRow->nama_jalan . ' No. ' . $kavlingRow->no_kavling) : ('ID #' . $idKavling);

        $tipeRow  = $idTipe ? $this->db->table('tipe')->select('no_tipe_rumah, tipe_rumah')->where('id_tipe', $idTipe)->get()->getRow() : null;
        $namaTipe = $tipeManual !== ''
            ? $tipeManual
            : ($tipeRow ? ($tipeRow->no_tipe_rumah . ' (' . $tipeRow->tipe_rumah . ')') : 'Tipe Baru');

        return "[Request Siteplan] {$pengaju} mengajukan perubahan tipe kavling {$noKav} menjadi {$namaTipe} di {$namaProyek}." .
            ($keterangan !== '' ? " Catatan: {$keterangan}" : '');
    }

    /**
     * Mengambil daftar request kavling berdasarkan proyek aktif dan filter status
     */
    public function getList(int $idProyek, ?string $status = null, ?int $page = null, ?int $perPage = null): array
    {
        $builder = $this->db->table('kavling_request')
            ->select('
                kavling_request.*,
                users.name as pengaju_name,
                users.username as pengaju_username,
                cluster.nama_cluster,
                jalan.nama_jalan,
                tipe.no_tipe_rumah,
                tipe.tipe_rumah,
                kavling.no_kavling as existing_no_kavling,
                kavling_jalan.nama_jalan as existing_nama_jalan
            ')
            ->select("COALESCE(NULLIF(kavling_request.tipe_manual, ''), NULLIF(CONCAT_WS(' - ', tipe.no_tipe_rumah, tipe.tipe_rumah), '')) AS tipe_display", false)
            ->join('users', 'users.id = kavling_request.created_by', 'left')
            ->join('cluster', 'cluster.id_cluster = kavling_request.id_cluster', 'left')
            ->join('jalan', 'jalan.id_jalan = kavling_request.id_jalan', 'left')
            ->join('tipe', 'tipe.id_tipe = kavling_request.id_tipe', 'left')
            ->join('kavling', 'kavling.id_kavling = kavling_request.id_kavling', 'left')
            ->join('jalan as kavling_jalan', 'kavling_jalan.id_jalan = kavling.id_jalan', 'left')
            ->where('kavling_request.id_proyek', $idProyek);

        if (!empty($status) && in_array($status, ['pending', 'approved', 'rejected'])) {
            $builder->where('kavling_request.status', $status);
        }

        $builder->orderBy('kavling_request.created_at', 'DESC');

        if ($perPage !== null && $perPage > 0) {
            $safePage = max(1, (int) ($page ?? 1));
            $offset   = ($safePage - 1) * $perPage;
            $builder->limit($perPage, $offset);
        }

        return $builder->get()->getResult();
    }

    /**
     * Menghitung jumlah request kavling per status untuk proyek tertentu.
     *
     * @return array{all: int, pending: int, approved: int, rejected: int}
     */
    public function getStatusCounts(int $idProyek): array
    {
        if ($idProyek <= 0) {
            return [
                'all'      => 0,
                'pending'  => 0,
                'approved' => 0,
                'rejected' => 0,
            ];
        }

        $row = $this->db->table('kavling_request')
            ->select("
                COUNT(*) AS total_count,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) AS pending_count,
                COUNT(CASE WHEN status = 'approved' THEN 1 END) AS approved_count,
                COUNT(CASE WHEN status = 'rejected' THEN 1 END) AS rejected_count
            ", false)
            ->where('id_proyek', $idProyek)
            ->get()
            ->getRow();

        return [
            'all'      => (int) ($row->total_count ?? 0),
            'pending'  => (int) ($row->pending_count ?? 0),
            'approved' => (int) ($row->approved_count ?? 0),
            'rejected' => (int) ($row->rejected_count ?? 0),
        ];
    }

    /**
     * Update status request kavling (contoh: approved/selesai atau dikembalikan ke pending/rejected)
     */
    public function updateStatus(int $idRequest, string $newStatus, int $userId, ?string $alasanTolak = null): array
    {
        if (!in_array($newStatus, ['pending', 'approved', 'rejected'])) {
            throw new RuntimeException('Status tidak valid.');
        }

        $request = $this->requestModel->find($idRequest);
        if (!$request) {
            throw new RuntimeException('Data request tidak ditemukan.');
        }

        $cleanAlasan  = $alasanTolak !== null ? trim($alasanTolak) : null;
        $storedAlasan = $newStatus === 'rejected'
            ? ($cleanAlasan !== '' ? $cleanAlasan : null)
            : null;

        $now = date('Y-m-d H:i:s');
        $updated = $this->requestModel->update($idRequest, [
            'status'       => $newStatus,
            'alasan_tolak' => $storedAlasan,
            'updated_at'   => $now,
        ]);

        if (!$updated) {
            throw new RuntimeException('Gagal mengubah status request.');
        }

        $statusLabels = [
            'pending'  => 'Menunggu (Pending)',
            'approved' => 'Selesai (Disetujui)',
            'rejected' => 'Ditolak',
        ];

        return [
            'id_request'   => $idRequest,
            'status'       => $newStatus,
            'alasan_tolak' => $storedAlasan,
            'label'        => $statusLabels[$newStatus] ?? $newStatus,
            'message'      => "Status request berhasil diubah menjadi: " . ($statusLabels[$newStatus] ?? $newStatus),
        ];
    }
}

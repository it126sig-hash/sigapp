<?php

namespace App\Services\Transaksi;

use App\Data\NotificationAudience;
use App\Data\NotificationData;
use App\Enums\NotificationEvent;
use App\Models\KavlingRequestModel;
use App\Repositories\Transaksi\KavlingRequestRepository;
use App\Services\AuditTrail\HistoryService;
use App\Services\FileAccessService;
use App\Services\NotifikasiService;
use CodeIgniter\Database\BaseConnection;
use RuntimeException;

class KavlingRequestService
{
    protected BaseConnection $db;
    protected KavlingRequestModel $requestModel;
    protected NotifikasiService $notifikasiService;
    protected KavlingRequestRepository $requestRepo;
    protected FileAccessService $fileAccessService;
    protected HistoryService $historyService;

    public function __construct(
        ?BaseConnection $db = null,
        ?KavlingRequestModel $requestModel = null,
        ?NotifikasiService $notifikasiService = null,
        ?KavlingRequestRepository $requestRepo = null,
        ?FileAccessService $fileAccessService = null,
        ?HistoryService $historyService = null
    ) {
        $this->db                = $db ?? db_connect();
        $this->requestModel      = $requestModel ?? new KavlingRequestModel($this->db);
        $this->notifikasiService = $notifikasiService ?? new NotifikasiService();
        $this->requestRepo       = $requestRepo ?? new KavlingRequestRepository($this->db);
        $this->fileAccessService = $fileAccessService ?? new FileAccessService();
        $this->historyService    = $historyService ?? new HistoryService();
    }

    public function isKavlingBlockedByAkad(object $row): bool
    {
        if (empty($row->id_mkdt)) {
            return false;
        }

        if ((int) ($row->is_batal ?? 0) !== 0) {
            return false;
        }

        $statusMkdt = trim((string) ($row->status_mkdt ?? ''));
        if (strcasecmp($statusMkdt, 'Batal') === 0) {
            return false;
        }

        return strcasecmp($statusMkdt, 'Akad') === 0
            || (int) ($row->akad ?? 0) === 1
            || (int) ($row->akad_indent ?? 0) === 1;
    }

    /**
     * Submit request tambah baru atau ubah tipe kavling (mendukung multi-jalan, multi-kavling, single legacy, dan batch target.js)
     */
    public function submitRequest(array $data, int $userId): array
    {
        $idProyek = (int) ($data['id_proyek'] ?? 0);
        if ($idProyek <= 0) {
            throw new RuntimeException('Proyek tidak valid.');
        }

        $jenisRequest = (string) ($data['jenis_request'] ?? 'tambah_baru');
        if (! in_array($jenisRequest, ['tambah_baru', 'ubah_tipe'], true)) {
            throw new RuntimeException('Jenis request tidak valid.');
        }

        if (array_key_exists('items', $data)) {
            return $this->submitBatchItemsUbahTipe($idProyek, $jenisRequest, $data, $userId);
        }

        if ($jenisRequest === 'tambah_baru') {
            return $this->submitTambahBaru($idProyek, $data, $userId);
        }

        if (array_key_exists('id_kavlings', $data)) {
            return $this->submitMultiUbahTipe($idProyek, $data, $userId);
        }

        return $this->submitLegacySingleUbahTipe($idProyek, $data, $userId);
    }

    private function submitTambahBaru(int $idProyek, array $data, int $userId): array
    {
        $idCluster  = ! empty($data['id_cluster']) ? (int) $data['id_cluster'] : null;
        $idJalans   = $this->normalizeIntList($data['id_jalans'] ?? ($data['id_jalan'] ?? []));
        $idTipe     = ! empty($data['id_tipe']) ? (int) $data['id_tipe'] : null;
        $tipeManual = trim((string) ($data['tipe_manual'] ?? ''));

        if (mb_strlen($tipeManual) > 100) {
            throw new RuntimeException('Tipe rumah manual maksimal 100 karakter.');
        }

        $keterangan = trim((string) ($data['keterangan'] ?? ''));
        if ($keterangan === '') {
            throw new RuntimeException('Keterangan pengajuan wajib diisi.');
        }

        $jalanRows = [];
        if ($idJalans !== []) {
            $jalanRows = $this->requestRepo->getJalanRowsByProyek($idProyek, $idJalans);
            if (count($jalanRows) !== count($idJalans)) {
                throw new RuntimeException('Sebagian jalan yang dipilih tidak valid atau bukan milik proyek ini.');
            }
        }

        $tipeRow = null;
        if ($idTipe !== null) {
            $tipeRow = $this->requestRepo->findTipeByIdAndProyek($idTipe, $idProyek);
            if (! $tipeRow) {
                throw new RuntimeException('Tipe rumah tidak ditemukan pada proyek ini.');
            }
        }

        $this->db->transStart();

        $insertData = [
            'id_proyek'     => $idProyek,
            'jenis_request' => 'tambah_baru',
            'id_kavling'    => null,
            'id_cluster'    => $idCluster,
            'id_jalan'      => count($idJalans) === 1 ? $idJalans[0] : null,
            'id_tipe'       => $idTipe,
            'tipe_manual'   => $tipeManual !== '' ? $tipeManual : null,
            'keterangan'    => $keterangan,
            'status'        => 'pending',
            'created_by'    => $userId,
        ];

        $inserted = $this->requestModel->insert($insertData);
        $requestId = (int) $this->requestModel->getInsertID();
        if (! $inserted || $requestId <= 0) {
            $this->db->transRollback();
            throw new RuntimeException('Gagal menyimpan pengajuan request kavling.');
        }

        if ($idJalans !== []) {
            $this->requestRepo->insertJalans($requestId, $idJalans);
        }

        [$namaProyek, $pengaju] = $this->resolveProyekAndUserLabels($idProyek, $userId);

        if ($jalanRows !== []) {
            $jalanLabels = array_map(static fn (object $j): string => (string) $j->nama_jalan, $jalanRows);
            $preview     = implode(', ', array_slice($jalanLabels, 0, 5));
            if (count($jalanLabels) > 5) {
                $preview .= ', +' . (count($jalanLabels) - 5) . ' lainnya';
            }
            $lokasiText = 'Jalan: ' . $preview;
        } elseif ($idCluster !== null) {
            $clusterName = $this->requestRepo->getClusterName($idCluster);
            $lokasiText  = $clusterName ? ('Cluster: ' . $clusterName) : 'Cluster/Jalan belum ditentukan';
        } else {
            $lokasiText = 'Cluster/Jalan belum ditentukan';
        }

        $tipeParts = [];
        if ($tipeRow) {
            $tipeParts[] = $this->formatTipeLabel($tipeRow->no_tipe_rumah ?? null, $tipeRow->tipe_rumah ?? null) ?? '';
        }
        if ($tipeManual !== '') {
            $tipeParts[] = $tipeManual;
        }
        $tipeParts = array_values(array_filter($tipeParts, static fn (string $v): bool => $v !== ''));
        $tipeText  = $tipeParts !== [] ? implode(' / ', $tipeParts) : 'Belum ditentukan / Tipe Baru';

        $pesanNotif = "[Request Siteplan] {$pengaju} mengajukan penambahan kavling baru ({$lokasiText}). Tipe: {$tipeText} di {$namaProyek}. Catatan: {$keterangan}";

        $notifData = new NotificationData(
            message: $pesanNotif,
            actorUserId: $userId,
            idKavling: null,
            idKonsumen: null,
            type: NotificationEvent::REQUEST_KAVLING,
            idProyek: $idProyek,
            actionUrl: 'siteplan/view',
            eventType: NotificationEvent::REQUEST_KAVLING
        );

        $this->notifikasiService->create($notifData, NotificationAudience::forGroups([6]));

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            throw new RuntimeException('Gagal memproses transaksi request kavling.');
        }

        return [
            'id_request'  => $requestId,
            'jalan_count' => count($idJalans),
            'message'     => 'Pengajuan request kavling baru berhasil dikirim ke tim Planning.',
        ];
    }

    private function submitMultiUbahTipe(int $idProyek, array $data, int $userId): array
    {
        $idKavlings = $this->normalizeIntList($data['id_kavlings']);
        if ($idKavlings === []) {
            throw new RuntimeException('Minimal 1 kavling harus dipilih untuk pengajuan perubahan tipe.');
        }

        $keterangan = trim((string) ($data['keterangan'] ?? ''));
        if ($keterangan === '') {
            throw new RuntimeException('Keterangan pengajuan wajib diisi.');
        }

        $idTipe     = ! empty($data['id_tipe']) ? (int) $data['id_tipe'] : null;
        $tipeManual = trim((string) ($data['tipe_manual'] ?? ''));
        if (mb_strlen($tipeManual) > 100) {
            throw new RuntimeException('Tipe rumah manual maksimal 100 karakter.');
        }

        $tipeRow = null;
        if ($idTipe !== null) {
            $tipeRow = $this->requestRepo->findTipeByIdAndProyek($idTipe, $idProyek);
            if (! $tipeRow) {
                throw new RuntimeException('Tipe rumah tidak ditemukan pada proyek ini.');
            }
        }

        $eligibilityMap = $this->requestRepo->getKavlingEligibilityRows($idProyek, $idKavlings);
        if (count($eligibilityMap) !== count($idKavlings)) {
            throw new RuntimeException('Sebagian kavling yang dipilih tidak valid atau bukan milik proyek ini.');
        }

        $blockedLabels = [];
        foreach ($idKavlings as $kId) {
            $row = $eligibilityMap[$kId];
            if ($this->isKavlingBlockedByAkad($row)) {
                $statusLabel = (int) ($row->akad_indent ?? 0) === 1 ? 'Akad Indent' : 'Akad';
                $blockedLabels[] = trim((string) $row->nama_jalan) . ' No. ' . trim((string) $row->no_kavling) . " ({$statusLabel})";
            }
        }

        if ($blockedLabels !== []) {
            throw new RuntimeException(
                'Kavling berikut tidak dapat diajukan ubah tipe karena berstatus Akad / Akad Indent: ' . implode(', ', $blockedLabels) . '.'
            );
        }

        $pendingMap = $this->requestRepo->findPendingUbahTipeMap($idProyek, $idKavlings);
        if ($pendingMap !== []) {
            $pendingLabels = [];
            foreach (array_keys($pendingMap) as $kId) {
                $row             = $eligibilityMap[$kId] ?? null;
                $pendingLabels[] = $row
                    ? (trim((string) $row->nama_jalan) . ' No. ' . trim((string) $row->no_kavling))
                    : ('ID #' . $kId);
            }

            throw new RuntimeException(
                'Kavling berikut masih memiliki pengajuan ubah tipe yang berstatus pending: ' . implode(', ', $pendingLabels) . '.'
            );
        }

        $this->db->transStart();

        $insertData = [
            'id_proyek'     => $idProyek,
            'jenis_request' => 'ubah_tipe',
            'id_kavling'    => count($idKavlings) === 1 ? $idKavlings[0] : null,
            'id_cluster'    => null,
            'id_jalan'      => null,
            'id_tipe'       => $idTipe,
            'tipe_manual'   => $tipeManual !== '' ? $tipeManual : null,
            'keterangan'    => $keterangan,
            'status'        => 'pending',
            'created_by'    => $userId,
        ];

        $inserted  = $this->requestModel->insert($insertData);
        $requestId = (int) $this->requestModel->getInsertID();
        if (! $inserted || $requestId <= 0) {
            $this->db->transRollback();
            throw new RuntimeException('Gagal menyimpan pengajuan request ubah tipe.');
        }

        $childRows = [];
        $kavLabels = [];
        foreach ($idKavlings as $kId) {
            $row         = $eligibilityMap[$kId];
            $childRows[] = [
                'id_kavling'  => $kId,
                'old_id_tipe' => ! empty($row->id_tipe) ? (int) $row->id_tipe : null,
            ];
            $kavLabels[] = trim((string) $row->nama_jalan) . ' No. ' . trim((string) $row->no_kavling);
        }

        $this->requestRepo->insertKavlings($requestId, $childRows);

        [$namaProyek, $pengaju] = $this->resolveProyekAndUserLabels($idProyek, $userId);
        $previewText = implode(', ', array_slice($kavLabels, 0, 5));
        if (count($kavLabels) > 5) {
            $previewText .= ', +' . (count($kavLabels) - 5) . ' lainnya';
        }

        $tipeText = $tipeRow
            ? ($this->formatTipeLabel($tipeRow->no_tipe_rumah ?? null, $tipeRow->tipe_rumah ?? null) ?? 'Tipe Dipilih')
            : ($tipeManual !== '' ? $tipeManual : 'Belum ditentukan / Tipe Baru');

        $countKav   = count($idKavlings);
        $pesanNotif = "[Request Siteplan] {$pengaju} mengajukan perubahan tipe {$countKav} kavling ({$previewText}) menjadi {$tipeText} di {$namaProyek}. Catatan: {$keterangan}";

        $notifKavlingId = $countKav === 1 ? $idKavlings[0] : null;
        $actionUrl      = $countKav === 1 ? ('siteplan/view?id_kavling=' . $idKavlings[0]) : 'siteplan/view';

        $notifData = new NotificationData(
            message: $pesanNotif,
            actorUserId: $userId,
            idKavling: $notifKavlingId,
            idKonsumen: null,
            type: NotificationEvent::REQUEST_KAVLING,
            idProyek: $idProyek,
            actionUrl: $actionUrl,
            eventType: NotificationEvent::REQUEST_KAVLING
        );

        $this->notifikasiService->create($notifData, NotificationAudience::forGroups([6]));

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            throw new RuntimeException('Gagal memproses transaksi request ubah tipe.');
        }

        return [
            'id_request'    => $requestId,
            'kavling_count' => $countKav,
            'message'       => 'Pengajuan ubah tipe berhasil dikirim ke tim Planning.',
        ];
    }

    private function submitLegacySingleUbahTipe(int $idProyek, array $data, int $userId): array
    {
        $idKavling  = ! empty($data['id_kavling']) ? (int) $data['id_kavling'] : null;
        $idTipe     = ! empty($data['id_tipe']) ? (int) $data['id_tipe'] : null;
        $tipeManual = trim((string) ($data['tipe_manual'] ?? ''));
        $keterangan = trim((string) ($data['keterangan'] ?? ''));

        if (mb_strlen($tipeManual) > 100) {
            throw new RuntimeException('Tipe rumah manual maksimal 100 karakter.');
        }

        if ($idTipe === null && $tipeManual === '') {
            throw new RuntimeException('Tipe rumah wajib diisi.');
        }

        if (empty($idKavling)) {
            throw new RuntimeException('Kavling existing harus dipilih untuk request perubahan tipe.');
        }

        $existingPending = $this->requestRepo->findPendingUbahTipeMap($idProyek, [$idKavling]);
        if (isset($existingPending[$idKavling])) {
            throw new RuntimeException('Kavling ini sudah memiliki pengajuan ubah tipe yang berstatus pending untuk tim Planning.');
        }

        $eligibilityMap = $this->requestRepo->getKavlingEligibilityRows($idProyek, [$idKavling]);
        if (isset($eligibilityMap[$idKavling]) && $this->isKavlingBlockedByAkad($eligibilityMap[$idKavling])) {
            throw new RuntimeException('Kavling yang berstatus Akad / Akad Indent tidak dapat diajukan perubahan tipe.');
        }

        $this->db->transStart();

        $insertData = [
            'id_proyek'     => $idProyek,
            'jenis_request' => 'ubah_tipe',
            'id_kavling'    => $idKavling,
            'id_cluster'    => null,
            'id_jalan'      => null,
            'id_tipe'       => $idTipe,
            'tipe_manual'   => $tipeManual !== '' ? $tipeManual : null,
            'keterangan'    => $keterangan,
            'status'        => 'pending',
            'created_by'    => $userId,
        ];

        $inserted  = $this->requestModel->insert($insertData);
        $requestId = (int) $this->requestModel->getInsertID();
        if (! $inserted || $requestId <= 0) {
            $this->db->transRollback();
            throw new RuntimeException('Gagal menyimpan pengajuan request kavling.');
        }

        $oldIdTipe = isset($eligibilityMap[$idKavling]) && ! empty($eligibilityMap[$idKavling]->id_tipe)
            ? (int) $eligibilityMap[$idKavling]->id_tipe
            : null;
        $this->requestRepo->insertKavlings($requestId, [[
            'id_kavling'  => $idKavling,
            'old_id_tipe' => $oldIdTipe,
        ]]);

        [$namaProyek, $pengaju] = $this->resolveProyekAndUserLabels($idProyek, $userId);
        $pesanNotif = $this->buildSingleUbahTipeNotifMessage(
            $idProyek,
            $idKavling,
            $idTipe,
            $tipeManual,
            $keterangan,
            $pengaju,
            $namaProyek
        );

        $notifData = new NotificationData(
            message: $pesanNotif,
            actorUserId: $userId,
            idKavling: $idKavling,
            idKonsumen: null,
            type: NotificationEvent::REQUEST_KAVLING,
            idProyek: $idProyek,
            actionUrl: "siteplan/view?id_kavling={$idKavling}",
            eventType: NotificationEvent::REQUEST_KAVLING
        );

        $this->notifikasiService->create($notifData, NotificationAudience::forGroups([6]));

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            throw new RuntimeException('Gagal memproses transaksi request kavling.');
        }

        return [
            'id_request' => $requestId,
            'message'    => 'Pengajuan request berhasil dikirim ke tim Planning.',
        ];
    }

    private function submitBatchItemsUbahTipe(int $idProyek, string $jenisRequest, array $data, int $userId): array
    {
        if ($jenisRequest !== 'ubah_tipe') {
            throw new RuntimeException('Pengiriman batch hanya didukung untuk jenis request ubah_tipe.');
        }

        $items = $this->normalizeBatchItems($data);
        if ($items === []) {
            throw new RuntimeException('Minimal 1 kavling harus dipilih untuk pengajuan perubahan tipe.');
        }

        $kavlingIds     = array_map(static fn (array $item): int => $item['id_kavling'], $items);
        $eligibilityMap = $this->requestRepo->getKavlingEligibilityRows($idProyek, $kavlingIds);

        $blockedLabels = [];
        foreach ($kavlingIds as $kId) {
            if (isset($eligibilityMap[$kId]) && $this->isKavlingBlockedByAkad($eligibilityMap[$kId])) {
                $row             = $eligibilityMap[$kId];
                $blockedLabels[] = trim((string) $row->nama_jalan) . ' No. ' . trim((string) $row->no_kavling);
            }
        }
        if ($blockedLabels !== []) {
            throw new RuntimeException(
                'Kavling berikut tidak dapat diajukan ubah tipe karena berstatus Akad / Akad Indent: ' . implode(', ', $blockedLabels) . '.'
            );
        }

        $existingPendingMap = $this->requestRepo->findPendingUbahTipeMap($idProyek, $kavlingIds);

        $toInsert       = [];
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

        $tipeCache = [];
        $groups    = [];

        foreach ($toInsert as $item) {
            if ($item['id_tipe'] !== null) {
                $idTipe = (int) $item['id_tipe'];
                if (! array_key_exists($idTipe, $tipeCache)) {
                    $tipeRow = $this->requestRepo->findTipeByIdAndProyek($idTipe, $idProyek);
                    if (! $tipeRow) {
                        throw new RuntimeException('Tipe rumah tidak ditemukan pada proyek ini.');
                    }
                    $tipeCache[$idTipe] = $tipeRow;
                }
                $tipeRow   = $tipeCache[$idTipe];
                $groupKey  = 'master:' . $idTipe;
                $groupTipe = $idTipe;
                $manualVal = null;
                $tipeLabel = $this->formatTipeLabel($tipeRow->no_tipe_rumah ?? null, $tipeRow->tipe_rumah ?? null)
                    ?? ('Tipe #' . $idTipe);
            } else {
                $manualText = trim((string) $item['tipe_manual']);
                $lowerKey   = function_exists('mb_strtolower') ? mb_strtolower($manualText) : strtolower($manualText);
                $groupKey   = 'manual:' . $lowerKey;
                $groupTipe  = null;
                $manualVal  = $manualText;
                $tipeLabel  = $manualText;
            }

            if (! isset($groups[$groupKey])) {
                $groups[$groupKey] = [
                    'id_tipe'     => $groupTipe,
                    'tipe_manual' => $manualVal,
                    'tipe_label'  => $tipeLabel,
                    'items'       => [],
                ];
            }

            $groups[$groupKey]['items'][] = $item;
        }

        $this->db->transStart();

        $createdIds     = [];
        $groupSummaries = [];

        foreach ($groups as $group) {
            $groupKavlingIds = array_map(
                static fn (array $item): int => $item['id_kavling'],
                $group['items']
            );

            $keterangans = [];
            $seenKet     = [];
            foreach ($group['items'] as $gItem) {
                $ket = trim((string) ($gItem['keterangan'] ?? ''));
                if ($ket !== '' && ! isset($seenKet[$ket])) {
                    $seenKet[$ket] = true;
                    $keterangans[] = $ket;
                }
            }
            $groupKeterangan = $keterangans !== [] ? implode('; ', $keterangans) : '';

            $inserted = $this->requestModel->insert([
                'id_proyek'     => $idProyek,
                'jenis_request' => 'ubah_tipe',
                'id_kavling'    => count($groupKavlingIds) === 1 ? $groupKavlingIds[0] : null,
                'id_cluster'    => null,
                'id_jalan'      => null,
                'id_tipe'       => $group['id_tipe'],
                'tipe_manual'   => $group['tipe_manual'],
                'keterangan'    => $groupKeterangan,
                'status'        => 'pending',
                'created_by'    => $userId,
            ]);

            $requestId = (int) $this->requestModel->getInsertID();
            if (! $inserted || $requestId <= 0) {
                $this->db->transRollback();
                throw new RuntimeException('Gagal menyimpan pengajuan request kavling.');
            }

            $childRows = [];
            foreach ($groupKavlingIds as $kId) {
                $oldIdTipe = isset($eligibilityMap[$kId]) && ! empty($eligibilityMap[$kId]->id_tipe)
                    ? (int) $eligibilityMap[$kId]->id_tipe
                    : null;
                $childRows[] = [
                    'id_kavling'  => $kId,
                    'old_id_tipe' => $oldIdTipe,
                ];
            }

            $this->requestRepo->insertKavlings($requestId, $childRows);

            $createdIds[]     = $requestId;
            $groupSummaries[] = [
                'id_request'    => $requestId,
                'id_tipe'       => $group['id_tipe'],
                'tipe_manual'   => $group['tipe_manual'],
                'tipe_label'    => $group['tipe_label'],
                'kavling_count' => count($groupKavlingIds),
                'id_kavlings'   => $groupKavlingIds,
            ];
        }

        [$namaProyek, $pengaju] = $this->resolveProyekAndUserLabels($idProyek, $userId);
        $requestCount = count($createdIds);
        $kavlingCount = count($toInsert);

        if ($kavlingCount === 1) {
            $single     = $toInsert[0];
            $pesanNotif = $this->buildSingleUbahTipeNotifMessage(
                $idProyek,
                $single['id_kavling'],
                $single['id_tipe'],
                $single['tipe_manual'],
                $single['keterangan'],
                $pengaju,
                $namaProyek
            );
            $notifKavlingId = $single['id_kavling'];
            $actionUrl      = 'siteplan/view?id_kavling=' . $single['id_kavling'];
        } else {
            $breakdownParts = [];
            foreach (array_slice($groupSummaries, 0, 5) as $summaryItem) {
                $breakdownParts[] = $summaryItem['tipe_label'] . ': ' . $summaryItem['kavling_count'] . ' kavling';
            }
            if (count($groupSummaries) > 5) {
                $breakdownParts[] = '+' . (count($groupSummaries) - 5) . ' tipe lainnya';
            }
            $tipeBreakdown  = implode(', ', $breakdownParts);
            $pesanNotif     = "[Request Siteplan] {$pengaju} mengajukan {$requestCount} request perubahan tipe untuk {$kavlingCount} kavling ({$tipeBreakdown}) di {$namaProyek}.";
            $notifKavlingId = null;
            $actionUrl      = 'siteplan/view';
        }

        $notifData = new NotificationData(
            message: $pesanNotif,
            actorUserId: $userId,
            idKavling: $notifKavlingId,
            idKonsumen: null,
            type: NotificationEvent::REQUEST_KAVLING,
            idProyek: $idProyek,
            actionUrl: $actionUrl,
            eventType: NotificationEvent::REQUEST_KAVLING
        );

        $this->notifikasiService->create($notifData, NotificationAudience::forGroups([6]));

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            throw new RuntimeException('Gagal memproses transaksi batch request kavling.');
        }

        $skippedCount = count($skippedPending);
        if ($requestCount === 1 && $kavlingCount === 1) {
            $message = 'Pengajuan request berhasil dikirim ke tim Planning.';
        } elseif ($requestCount === 1 && $kavlingCount > 1) {
            $message = "1 request ubah tipe untuk {$kavlingCount} kavling berhasil dikirim ke tim Planning.";
        } else {
            $message = "{$requestCount} request ubah tipe untuk {$kavlingCount} kavling berhasil dikirim ke tim Planning.";
        }

        if ($skippedCount > 0) {
            $message .= ' (' . $skippedCount . ' kavling dilewati karena masih berstatus pending.)';
        }

        return [
            'id_request'      => $createdIds[0],
            'created_ids'     => $createdIds,
            'submitted_count' => $requestCount,
            'request_count'   => $requestCount,
            'kavling_count'   => $kavlingCount,
            'groups'          => $groupSummaries,
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
        $topIdTipe     = ! empty($data['id_tipe']) ? (int) $data['id_tipe'] : null;
        $topTipeManual = trim((string) ($data['tipe_manual'] ?? ''));
        $topKeterangan = trim((string) ($data['keterangan'] ?? ''));

        $rawItems = is_array($data['items'] ?? null) ? $data['items'] : [];

        $normalized = [];
        $seen       = [];

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

            $itemIdTipe     = ! empty($item['id_tipe']) ? (int) $item['id_tipe'] : null;
            $itemTipeManual = trim((string) ($item['tipe_manual'] ?? ''));

            if ($itemIdTipe === null && $itemTipeManual === '') {
                $itemIdTipe     = $topIdTipe;
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
     * Mengambil daftar request kavling dalam format DTO bersih (Full JSON RAW_JSON_READY)
     */
    public function getList(
        int $idProyek,
        ?string $status = null,
        ?int $page = null,
        ?int $perPage = null,
        ?string $jenisRequest = null
    ): array {
        $rows = $this->requestRepo->getList($idProyek, $jenisRequest, $status, $page, $perPage);

        return array_map(fn (object $row): array => $this->formatRequestListRow($row), $rows);
    }

    /**
     * @return array{all: int, pending: int, tambah_baru_pending: int, ubah_tipe_pending: int, approved: int, rejected: int, cancelled: int, history: int}
     */
    public function getStatusCounts(int $idProyek): array
    {
        return $this->requestRepo->getStatusCounts($idProyek);
    }

    public function countFiltered(int $idProyek, ?string $jenisRequest = null, ?string $status = null): int
    {
        return $this->requestRepo->countFiltered($idProyek, $jenisRequest, $status);
    }

    /**
     * Update status request kavling oleh tim Planning / Admin dan kirim notifikasi balik ke pengaju
     */
    public function updateStatus(int $idRequest, string $newStatus, int $userId, ?string $catatanPlanning = null): array
    {
        if (! in_array($newStatus, ['pending', 'approved', 'rejected'], true)) {
            throw new RuntimeException('Status tidak valid.');
        }

        $request = $this->requestModel->find($idRequest);
        if (! $request) {
            throw new RuntimeException('Data request tidak ditemukan.');
        }

        if (strtolower((string) ($request->status ?? '')) === 'cancelled') {
            throw new RuntimeException('Pengajuan ini sudah dibatalkan dan tidak dapat diproses.');
        }

        $cleanNote = $catatanPlanning !== null ? trim($catatanPlanning) : null;
        if ($cleanNote === '') {
            $cleanNote = null;
        }

        if ($newStatus === 'rejected' && $cleanNote === null) {
            throw new RuntimeException('Catatan/alasan penolakan wajib diisi saat menolak request.');
        }

        $now         = date('Y-m-d H:i:s');
        $processedBy = ($newStatus === 'pending') ? null : ($userId > 0 ? $userId : null);
        $processedAt = ($newStatus === 'pending') ? null : $now;
        $storedNote  = ($newStatus === 'pending') ? null : $cleanNote;
        $storedAlasan = $newStatus === 'rejected' ? $cleanNote : null;

        $this->db->transStart();

        $updated = $this->requestModel->update($idRequest, [
            'status'           => $newStatus,
            'catatan_planning' => $storedNote,
            'alasan_tolak'     => $storedAlasan,
            'processed_by'     => $processedBy,
            'processed_at'     => $processedAt,
            'updated_at'       => $now,
        ]);

        if (! $updated) {
            $this->db->transRollback();
            throw new RuntimeException('Gagal mengubah status request.');
        }

        if (in_array($newStatus, ['approved', 'rejected'], true)) {
            $this->sendStatusFeedbackNotification($request, $newStatus, $userId, $storedNote);
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            throw new RuntimeException('Gagal memproses transaksi perubahan status request.');
        }

        $statusLabels = [
            'pending'  => 'Menunggu (Pending)',
            'approved' => 'Selesai (Disetujui)',
            'rejected' => 'Ditolak',
        ];

        return [
            'id_request'       => $idRequest,
            'status'           => $newStatus,
            'catatan_planning' => $storedNote,
            'alasan_tolak'     => $storedAlasan,
            'processed_by'     => $processedBy,
            'processed_at'     => $processedAt,
            'label'            => $statusLabels[$newStatus] ?? $newStatus,
            'message'          => 'Status request berhasil diubah menjadi: ' . ($statusLabels[$newStatus] ?? $newStatus),
        ];
    }

    /**
     * Terapkan perubahan tipe kavling secara langsung dari modal Planning + tandai request approved + catat audit trail
     */
    public function applyUbahTipeKavling(int $idRequest, int $idTipe, int $userId, ?string $catatanPlanning = null): array
    {
        $request = $this->requestModel->find($idRequest);
        if (! $request) {
            throw new RuntimeException('Data request tidak ditemukan.');
        }

        if ((string) $request->jenis_request !== 'ubah_tipe') {
            throw new RuntimeException('Aksi terapkan ubah tipe hanya berlaku untuk pengajuan ubah_tipe.');
        }

        if ((string) $request->status !== 'pending') {
            throw new RuntimeException('Pengajuan ini sudah diproses dan tidak berstatus pending.');
        }

        $idProyek = (int) $request->id_proyek;
        $tipeRow  = $this->requestRepo->findTipeByIdAndProyek($idTipe, $idProyek);
        if (! $tipeRow) {
            throw new RuntimeException('Tipe rumah tidak ditemukan pada proyek ini.');
        }

        $kavlingRows = $this->requestRepo->getKavlingsByRequestId($idRequest);
        if ($kavlingRows === []) {
            throw new RuntimeException('Daftar kavling pada pengajuan ini tidak ditemukan.');
        }

        $idKavlings = array_values(array_unique(array_filter(
            array_map(static fn (object $r): int => (int) $r->id_kavling, $kavlingRows),
            static fn (int $id): bool => $id > 0
        )));

        if ($idKavlings === []) {
            throw new RuntimeException('Daftar kavling pada pengajuan ini tidak valid.');
        }

        $eligibilityMap = $this->requestRepo->getKavlingEligibilityRows($idProyek, $idKavlings);
        if (count($eligibilityMap) !== count($idKavlings)) {
            throw new RuntimeException('Sebagian kavling pada pengajuan ini tidak ditemukan pada proyek aktif.');
        }

        $blockedLabels          = [];
        $activeKonsumenWarnings = [];
        $oldSnapshots           = [];

        foreach ($idKavlings as $kId) {
            $elig = $eligibilityMap[$kId];
            $label = trim((string) $elig->nama_jalan) . ' No. ' . trim((string) $elig->no_kavling);

            if ($this->isKavlingBlockedByAkad($elig)) {
                $statusLabel     = (int) ($elig->akad_indent ?? 0) === 1 ? 'Akad Indent' : 'Akad';
                $blockedLabels[] = "{$label} ({$statusLabel})";
            }

            if (! empty($elig->id_mkdt) && (int) ($elig->is_batal ?? 0) === 0 && strcasecmp(trim((string) ($elig->status_mkdt ?? '')), 'Batal') !== 0) {
                $activeKonsumenWarnings[] = [
                    'id_kavling'    => $kId,
                    'label'         => $label,
                    'nama_konsumen' => $elig->nama_konsumen ?? null,
                    'status_mkdt'   => $elig->status_mkdt ?? null,
                ];
            }

            $oldSnapshots[] = [
                'id_kavling' => $kId,
                'no_kavling' => $elig->no_kavling ?? null,
                'nama_jalan' => $elig->nama_jalan ?? null,
                'id_tipe'    => ! empty($elig->id_tipe) ? (int) $elig->id_tipe : null,
            ];
        }

        if ($blockedLabels !== []) {
            throw new RuntimeException(
                'Kavling berikut tidak dapat diubah tipenya karena sudah berstatus Akad / Akad Indent: ' . implode(', ', $blockedLabels) . '.'
            );
        }

        $cleanNote        = ($catatanPlanning !== null && trim($catatanPlanning) !== '') ? trim($catatanPlanning) : null;
        $now              = date('Y-m-d H:i:s');
        $appliedTipeLabel = $this->formatTipeLabel($tipeRow->no_tipe_rumah ?? null, $tipeRow->tipe_rumah ?? null) ?? ('Tipe #' . $idTipe);
        $processedBy      = $userId > 0 ? $userId : null;

        $this->db->transStart();

        // Hanya ubah kavling.id_tipe, tanpa menyentuh harga_akhir ataupun mkdt
        if (! $this->requestRepo->updateKavlingsTipe($idKavlings, $idTipe, $userId, $now)) {
            $this->db->transRollback();
            throw new RuntimeException('Gagal memperbarui tipe rumah pada tabel kavling.');
        }

        $this->historyService->log('siteplan', [
            'reference_type' => 'kavling_request',
            'reference_id'   => $idRequest,
            'id_proyek'      => $idProyek,
            'id_kavling'     => count($idKavlings) === 1 ? $idKavlings[0] : null,
            'action'         => 'request_ubah_tipe_kavling',
            'summary'        => 'Penerapan ubah tipe kavling dari request #' . $idRequest . ' menjadi ' . $appliedTipeLabel . ' (' . count($idKavlings) . ' kavling)',
            'old_data'       => $oldSnapshots,
            'new_data'       => [
                'id_request'       => $idRequest,
                'id_tipe'          => $idTipe,
                'tipe_label'       => $appliedTipeLabel,
                'id_kavlings'      => $idKavlings,
                'catatan_planning' => $cleanNote,
            ],
            'metadata'       => [
                'id_request'       => $idRequest,
                'batch_ids'        => $idKavlings,
                'applied_id_tipe'  => $idTipe,
                'catatan_planning' => $cleanNote,
            ],
            'add_by'         => $processedBy,
        ]);

        $updated = $this->requestModel->update($idRequest, [
            'status'           => 'approved',
            'applied_id_tipe'  => $idTipe,
            'catatan_planning' => $cleanNote,
            'alasan_tolak'     => null,
            'processed_by'     => $processedBy,
            'processed_at'     => $now,
            'updated_at'       => $now,
        ]);

        if (! $updated) {
            $this->db->transRollback();
            throw new RuntimeException('Gagal memperbarui status pengajuan request kavling.');
        }

        $this->sendStatusFeedbackNotification(
            $request,
            'approved',
            $userId,
            $cleanNote,
            "Tipe diterapkan menjadi {$appliedTipeLabel} untuk " . count($idKavlings) . " kavling",
            count($idKavlings) === 1 ? $idKavlings[0] : null
        );

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            throw new RuntimeException('Gagal memproses transaksi penerapan ubah tipe kavling.');
        }

        return [
            'id_request'               => $idRequest,
            'status'                   => 'approved',
            'applied_id_tipe'          => $idTipe,
            'applied_tipe_label'       => $appliedTipeLabel,
            'catatan_planning'         => $cleanNote,
            'processed_by'             => $processedBy,
            'processed_at'             => $now,
            'affected_kavling_ids'     => $idKavlings,
            'affected_kavling_count'   => count($idKavlings),
            'active_konsumen_warnings' => $activeKonsumenWarnings,
            'message'                  => count($idKavlings) . ' kavling berhasil diubah tipenya menjadi ' . $appliedTipeLabel . '.',
        ];
    }

    /**
     * Batalkan satu atau beberapa request kavling berstatus pending (Admin atau pemilik pengajuan MKDT)
     *
     * @param int[] $idRequests
     */
    public function cancelRequests(int $idProyek, array $idRequests, int $userId, bool $isAdmin, ?string $alasanBatal = null): array
    {
        if ($idProyek <= 0) {
            throw new RuntimeException('Proyek tidak valid.');
        }

        if ($userId <= 0) {
            throw new RuntimeException('User tidak valid.');
        }

        $ids = $this->normalizeIntList($idRequests);
        if ($ids === []) {
            throw new RuntimeException('Minimal 1 request harus dipilih untuk dibatalkan.');
        }

        $cleanAlasan = $alasanBatal !== null ? trim($alasanBatal) : null;
        if ($cleanAlasan === '') {
            $cleanAlasan = null;
        }
        if ($cleanAlasan !== null && mb_strlen($cleanAlasan) > 1000) {
            throw new RuntimeException('Alasan pembatalan maksimal 1000 karakter.');
        }

        $existingRows = $this->requestRepo->findRequestsByIdsAndProyek($idProyek, $ids);
        $byId         = [];
        foreach ($existingRows as $row) {
            $byId[(int) $row->id_request] = $row;
        }

        $eligibleIds  = [];
        $eligibleRows = [];
        $skipped      = [];

        foreach ($ids as $reqId) {
            if (! isset($byId[$reqId])) {
                $skipped[] = [
                    'id_request' => $reqId,
                    'reason'     => 'Request tidak ditemukan pada proyek ini.',
                ];
                continue;
            }

            $row = $byId[$reqId];
            if (strtolower((string) ($row->status ?? '')) !== 'pending') {
                $skipped[] = [
                    'id_request' => $reqId,
                    'reason'     => 'Request sudah diproses atau dibatalkan sebelumnya.',
                ];
                continue;
            }

            if (! $isAdmin && (int) ($row->created_by ?? 0) !== $userId) {
                $skipped[] = [
                    'id_request' => $reqId,
                    'reason'     => 'Anda hanya dapat membatalkan pengajuan milik sendiri.',
                ];
                continue;
            }

            $eligibleIds[]  = $reqId;
            $eligibleRows[] = $row;
        }

        if ($eligibleIds === []) {
            throw new RuntimeException($skipped[0]['reason'] ?? 'Tidak ada request yang memenuhi syarat untuk dibatalkan.');
        }

        $now = date('Y-m-d H:i:s');

        $this->db->transStart();

        $affected = $this->requestRepo->markRequestsCancelled($eligibleIds, $userId, $cleanAlasan, $now);
        if ($affected <= 0) {
            $this->db->transRollback();
            throw new RuntimeException('Gagal membatalkan request kavling.');
        }

        [$namaProyek, $pembatal] = $this->resolveProyekAndUserLabels($idProyek, $userId);
        $tambahCount             = 0;
        $ubahCount               = 0;
        $kavlingCount            = 0;

        foreach ($eligibleRows as $row) {
            if ((string) ($row->jenis_request ?? '') === 'ubah_tipe') {
                $ubahCount++;
                $rowKavCount   = ! empty($row->kavlings) && is_array($row->kavlings)
                    ? count($row->kavlings)
                    : (! empty($row->id_kavling) ? 1 : 0);
                $kavlingCount += $rowKavCount;
            } else {
                $tambahCount++;
            }
        }

        $jenisParts = [];
        if ($tambahCount > 0) {
            $jenisParts[] = "{$tambahCount} Tambah Kavling";
        }
        if ($ubahCount > 0) {
            $jenisParts[] = $kavlingCount > 0
                ? "{$ubahCount} Ubah Kavling ({$kavlingCount} kavling)"
                : "{$ubahCount} Ubah Kavling";
        }
        $jenisSummary = implode(', ', $jenisParts);

        $cancelledCount = count($eligibleIds);
        $pesanNotif     = $cancelledCount === 1
            ? "[Request Siteplan] {$pembatal} membatalkan 1 request kavling (#{$eligibleIds[0]}: {$jenisSummary}) di {$namaProyek}."
            : "[Request Siteplan] {$pembatal} membatalkan {$cancelledCount} request kavling ({$jenisSummary}) di {$namaProyek}.";

        if ($cleanAlasan !== null) {
            $pesanNotif .= " Alasan: {$cleanAlasan}";
        }

        $notifData = new NotificationData(
            message: $pesanNotif,
            actorUserId: $userId,
            idKavling: null,
            idKonsumen: null,
            type: NotificationEvent::REQUEST_KAVLING,
            idProyek: $idProyek,
            actionUrl: 'siteplan/view',
            eventType: NotificationEvent::REQUEST_KAVLING
        );

        $this->notifikasiService->create($notifData, NotificationAudience::forGroups([6]));

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            throw new RuntimeException('Gagal memproses transaksi pembatalan request kavling.');
        }

        $counts       = $this->getStatusCounts($idProyek);
        $skippedCount = count($skipped);
        $message      = $cancelledCount === 1
            ? '1 request kavling berhasil dibatalkan.'
            : "{$cancelledCount} request kavling berhasil dibatalkan.";

        if ($skippedCount > 0) {
            $message .= " ({$skippedCount} dilewati.)";
        }

        return [
            'cancelled_ids'   => $eligibleIds,
            'cancelled_count' => $cancelledCount,
            'skipped_count'   => $skippedCount,
            'skipped'         => $skipped,
            'counts'          => $counts,
            'message'         => $message,
        ];
    }

    private function sendStatusFeedbackNotification(
        object $request,
        string $newStatus,
        int $userId,
        ?string $catatanPlanning = null,
        ?string $extraInfo = null,
        ?int $idKavling = null
    ): void {
        $idProyek  = (int) ($request->id_proyek ?? 0);
        $idRequest = (int) ($request->id_request ?? 0);
        $createdBy = (int) ($request->created_by ?? 0);

        $namaProyek = $this->requestRepo->getProyekName($idProyek);
        $pemroses   = $this->requestRepo->getUserDisplayName($userId);
        $jenisLabel = ((string) ($request->jenis_request ?? '')) === 'ubah_tipe'
            ? 'ubah tipe kavling'
            : 'penambahan kavling baru';
        $statusText = $newStatus === 'approved' ? 'disetujui / selesai dikerjakan' : 'ditolak';

        $pesan = "[Request Siteplan] Pengajuan {$jenisLabel} (#{$idRequest}) di {$namaProyek} telah {$statusText} oleh {$pemroses}.";
        if ($extraInfo !== null && $extraInfo !== '') {
            $pesan .= " {$extraInfo}.";
        }
        if ($catatanPlanning !== null && $catatanPlanning !== '') {
            $pesan .= " Catatan Planning: {$catatanPlanning}";
        }

        $targetKavlingId = $idKavling ?? (! empty($request->id_kavling) ? (int) $request->id_kavling : null);
        $actionUrl       = $targetKavlingId ? ('siteplan/view?id_kavling=' . $targetKavlingId) : 'siteplan/view';

        $audience = $createdBy > 0
            ? NotificationAudience::forUsers([$createdBy])
            : NotificationAudience::forGroups([4]);

        $notifData = new NotificationData(
            message: $pesan,
            actorUserId: $userId,
            idKavling: $targetKavlingId,
            idKonsumen: null,
            type: NotificationEvent::REQUEST_KAVLING,
            idProyek: $idProyek > 0 ? $idProyek : null,
            actionUrl: $actionUrl,
            eventType: NotificationEvent::REQUEST_KAVLING
        );

        $this->notifikasiService->create($notifData, $audience);
    }

    private function formatRequestListRow(object $row): array
    {
        $requestedTipe = $this->formatTipeDto($row, 'req');
        $appliedTipe   = $this->formatTipeDto($row, 'app');

        $jalans = [];
        foreach (($row->jalans ?? []) as $j) {
            $jalans[] = [
                'id_jalan'     => (int) $j->id_jalan,
                'nama_jalan'   => (string) $j->nama_jalan,
                'nama_cluster' => $j->nama_cluster ?? null,
            ];
        }

        $kavlings = [];
        foreach (($row->kavlings ?? []) as $k) {
            $hasActiveKonsumen = ! empty($k->id_mkdt)
                && (int) ($k->is_batal ?? 0) === 0
                && strcasecmp(trim((string) ($k->status_mkdt ?? '')), 'Batal') !== 0;

            $kavlings[] = [
                'id_kavling'          => (int) $k->id_kavling,
                'no_kavling'          => (string) ($k->no_kavling ?? ''),
                'nama_jalan'          => (string) ($k->nama_jalan ?? ''),
                'old_id_tipe'         => ! empty($k->old_id_tipe) ? (int) $k->old_id_tipe : null,
                'old_tipe_label'      => $this->formatTipeLabel($k->old_no_tipe_rumah ?? null, $k->old_tipe_rumah ?? null),
                'current_id_tipe'     => ! empty($k->current_id_tipe) ? (int) $k->current_id_tipe : null,
                'current_tipe_label'  => $this->formatTipeLabel($k->current_no_tipe_rumah ?? null, $k->current_tipe_rumah ?? null),
                'has_active_konsumen' => $hasActiveKonsumen,
                'nama_konsumen'       => $hasActiveKonsumen ? ($k->nama_konsumen ?: null) : null,
                'status_mkdt'         => $hasActiveKonsumen ? ($k->status_mkdt ?: null) : null,
            ];
        }

        $tipeManual  = isset($row->tipe_manual) && trim((string) $row->tipe_manual) !== '' ? trim((string) $row->tipe_manual) : null;
        $tipeDisplay = $tipeManual
            ?? ($requestedTipe ? $this->formatTipeLabel($requestedTipe['no_tipe_rumah'], $requestedTipe['tipe_rumah']) : null);

        $pengajuName     = isset($row->pengaju_name) && trim((string) $row->pengaju_name) !== ''
            ? trim((string) $row->pengaju_name)
            : ($row->pengaju_username ?? null);
        $processedByName = isset($row->processed_by_name) && trim((string) $row->processed_by_name) !== ''
            ? trim((string) $row->processed_by_name)
            : ($row->processed_by_username ?? null);

        return [
            'id_request'          => (int) $row->id_request,
            'id_proyek'           => (int) $row->id_proyek,
            'jenis_request'       => (string) $row->jenis_request,
            'status'              => (string) $row->status,
            'id_kavling'          => ! empty($row->id_kavling) ? (int) $row->id_kavling : null,
            'id_cluster'          => ! empty($row->id_cluster) ? (int) $row->id_cluster : null,
            'id_jalan'            => ! empty($row->id_jalan) ? (int) $row->id_jalan : null,
            'id_tipe'             => ! empty($row->id_tipe) ? (int) $row->id_tipe : null,
            'applied_id_tipe'     => ! empty($row->applied_id_tipe) ? (int) $row->applied_id_tipe : null,
            'tipe_manual'         => $tipeManual,
            'tipe_display'        => $tipeDisplay,
            'nama_cluster'        => $row->nama_cluster ?? null,
            'nama_jalan'          => $row->nama_jalan ?? null,
            'no_tipe_rumah'       => $requestedTipe['no_tipe_rumah'] ?? null,
            'tipe_rumah'          => $requestedTipe['tipe_rumah'] ?? null,
            'existing_no_kavling' => $row->existing_no_kavling ?? null,
            'existing_nama_jalan' => $row->existing_nama_jalan ?? null,
            'requested_tipe'      => $requestedTipe,
            'applied_tipe'        => $appliedTipe,
            'jalans'              => $jalans,
            'kavlings'            => $kavlings,
            'keterangan'          => (string) ($row->keterangan ?? ''),
            'catatan_planning'    => $row->catatan_planning ?? null,
            'alasan_tolak'        => $row->alasan_tolak ?? null,
            'alasan_batal'        => $row->alasan_batal ?? null,
            'created_by'          => ! empty($row->created_by) ? (int) $row->created_by : null,
            'pengaju_name'        => $pengajuName,
            'pengaju_username'    => $row->pengaju_username ?? null,
            'processed_by'        => ! empty($row->processed_by) ? (int) $row->processed_by : null,
            'processed_by_name'   => $processedByName,
            'processed_at'        => $row->processed_at ?? null,
            'created_at'          => $row->created_at ?? null,
            'updated_at'          => $row->updated_at ?? null,
        ];
    }

    private function formatTipeDto(object $row, string $prefix): ?array
    {
        $idTipe = (int) ($row->{$prefix . '_id_tipe'} ?? 0);
        if ($idTipe <= 0) {
            return null;
        }

        $idGambarTipe  = ! empty($row->{$prefix . '_id_gambar_tipe'}) ? (int) $row->{$prefix . '_id_gambar_tipe'} : null;
        $idGambarDenah = ! empty($row->{$prefix . '_id_gambar_denah'}) ? (int) $row->{$prefix . '_id_gambar_denah'} : null;
        $idGambarKerja = ! empty($row->{$prefix . '_id_gambar_kerja'}) ? (int) $row->{$prefix . '_id_gambar_kerja'} : null;

        return [
            'id_tipe'                    => $idTipe,
            'no_tipe_rumah'              => $row->{$prefix . '_no_tipe_rumah'} ?? null,
            'tipe_rumah'                 => $row->{$prefix . '_tipe_rumah'} ?? null,
            'lb'                         => $row->{$prefix . '_lb'} !== null ? (float) $row->{$prefix . '_lb'} : null,
            'lt'                         => $row->{$prefix . '_lt'} !== null ? (float) $row->{$prefix . '_lt'} : null,
            'jumlah_kamar_tidur'         => (int) ($row->{$prefix . '_jumlah_kamar_tidur'} ?? 0),
            'jumlah_kamar_mandi'         => (int) ($row->{$prefix . '_jumlah_kamar_mandi'} ?? 0),
            'spesifikasi_teknis_atap'    => $row->{$prefix . '_spesifikasi_teknis_atap'} ?? null,
            'spesifikasi_teknis_dinding' => $row->{$prefix . '_spesifikasi_teknis_dinding'} ?? null,
            'spesifikasi_teknis_lantai'  => $row->{$prefix . '_spesifikasi_teknis_lantai'} ?? null,
            'spesifikasi_teknis_pondasi' => $row->{$prefix . '_spesifikasi_teknis_pondasi'} ?? null,
            'is_subsidi'                 => (int) ($row->{$prefix . '_is_subsidi'} ?? 0),
            'id_gambar_tipe'             => $idGambarTipe,
            'id_gambar_denah'            => $idGambarDenah,
            'id_gambar_kerja'            => $idGambarKerja,
            'gambar_tipe_access_url'     => $idGambarTipe ? $this->fileAccessService->accessUrl('gambar_kerja', $idGambarTipe) : null,
            'gambar_denah_access_url'    => $idGambarDenah ? $this->fileAccessService->accessUrl('gambar_kerja', $idGambarDenah) : null,
            'gambar_kerja_access_url'    => $idGambarKerja ? $this->fileAccessService->accessUrl('gambar_kerja', $idGambarKerja) : null,
        ];
    }

    private function formatTipeLabel(?string $noTipe, ?string $namaTipe): ?string
    {
        $no   = trim((string) ($noTipe ?? ''));
        $nama = trim((string) ($namaTipe ?? ''));

        if ($no !== '' && $nama !== '') {
            return "{$no} ({$nama})";
        }

        if ($no !== '' || $nama !== '') {
            return $no !== '' ? $no : $nama;
        }

        return null;
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function resolveProyekAndUserLabels(int $idProyek, int $userId): array
    {
        return [
            $this->requestRepo->getProyekName($idProyek),
            $this->requestRepo->getUserDisplayName($userId),
        ];
    }

    private function buildSingleUbahTipeNotifMessage(
        int $idProyek,
        int $idKavling,
        ?int $idTipe,
        string $tipeManual,
        string $keterangan,
        string $pengaju,
        string $namaProyek
    ): string {
        $labels = $this->requestRepo->getKavlingLabels([$idKavling]);
        $noKav  = $labels[$idKavling] ?? ('ID #' . $idKavling);

        $tipeRow  = $idTipe ? $this->requestRepo->findTipeByIdAndProyek($idTipe, $idProyek) : null;
        $namaTipe = $tipeManual !== ''
            ? $tipeManual
            : ($tipeRow ? ($this->formatTipeLabel($tipeRow->no_tipe_rumah ?? null, $tipeRow->tipe_rumah ?? null) ?? 'Tipe Baru') : 'Tipe Baru');

        return "[Request Siteplan] {$pengaju} mengajukan perubahan tipe kavling {$noKav} menjadi {$namaTipe} di {$namaProyek}." .
            ($keterangan !== '' ? " Catatan: {$keterangan}" : '');
    }

    /**
     * @param mixed $raw
     * @return int[]
     */
    private function normalizeIntList(mixed $raw): array
    {
        if ($raw === null || $raw === '') {
            return [];
        }

        $items = is_array($raw) ? $raw : (preg_split('/[;,]+/', (string) $raw) ?: []);
        $ids   = [];

        foreach ($items as $entry) {
            $id = (int) trim((string) $entry);
            if ($id > 0) {
                $ids[$id] = $id;
            }
        }

        return array_values($ids);
    }
}

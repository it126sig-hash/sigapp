<?php

namespace App\Repositories\Siteplan;

use App\Models\TargetSiteplanRequestModel;
use App\Repositories\AuditTrail\HistoryRepository;
use App\Services\HistoryService;
use CodeIgniter\Database\BaseConnection;

class TargetSiteplanRepository
{
    protected BaseConnection $db;
    protected HistoryService $historyService;
    protected HistoryRepository $historyRepository;
    protected TargetSiteplanRequestModel $requestModel;

    public function __construct(
        ?BaseConnection $db = null,
        ?HistoryService $historyService = null,
        ?HistoryRepository $historyRepository = null,
        ?TargetSiteplanRequestModel $requestModel = null
    ) {
        $this->db = $db ?? \Config\Database::connect();
        $this->historyService = $historyService ?? new HistoryService();
        $this->historyRepository = $historyRepository ?? new HistoryRepository();
        $this->requestModel = $requestModel ?? new TargetSiteplanRequestModel($this->db);
    }

    public function getByProject(int $idProyek): array
    {
        return $this->db->table('target_siteplan t')
            ->select('
                t.*,
                add_user.username as add_by_username,
                edit_user.username as edit_by_username,
                COUNT(tk.id_kavling) as jumlah_kavling
            ')
            ->join('target_siteplan_kavling tk', 'tk.id_target = t.id_target', 'left')
            ->join('users add_user', 'add_user.id = t.add_by', 'left')
            ->join('users edit_user', 'edit_user.id = t.edit_by', 'left')
            ->where('t.id_proyek', $idProyek)
            ->where('t.deleted_at', null)
            ->groupBy('t.id_target')
            ->orderBy('t.tahun_target', 'desc')
            ->orderBy('t.updated_at', 'desc')
            ->get()
            ->getResult();
    }

    public function getById(int $idTarget): ?object
    {
        return $this->db->table('target_siteplan t')
            ->select('t.*, add_user.username as add_by_username, edit_user.username as edit_by_username')
            ->join('users add_user', 'add_user.id = t.add_by', 'left')
            ->join('users edit_user', 'edit_user.id = t.edit_by', 'left')
            ->where('t.id_target', $idTarget)
            ->where('t.deleted_at', null)
            ->get()
            ->getRow();
    }

    public function findActiveByProjectAndYear(int $idProyek, int $tahunTarget): ?object
    {
        if ($idProyek <= 0 || $tahunTarget <= 0) {
            return null;
        }

        return $this->db->table('target_siteplan t')
            ->select('t.*, add_user.username as add_by_username, edit_user.username as edit_by_username')
            ->join('users add_user', 'add_user.id = t.add_by', 'left')
            ->join('users edit_user', 'edit_user.id = t.edit_by', 'left')
            ->where('t.id_proyek', $idProyek)
            ->where('t.tahun_target', $tahunTarget)
            ->where('t.status', 1)
            ->where('t.deleted_at', null)
            ->orderBy('t.id_target', 'desc')
            ->get()
            ->getRow();
    }

    public function createTargetHeader(array $fields): int
    {
        $this->db->table('target_siteplan')->insert($fields);

        return (int) $this->db->insertID();
    }

    public function updateTargetHeader(int $idTarget, array $fields): bool
    {
        if ($idTarget <= 0) {
            return false;
        }

        return (bool) $this->db->table('target_siteplan')
            ->where('id_target', $idTarget)
            ->update($fields);
    }

    public function getKavlings(int $idTarget): array
    {
        $rows = $this->db->table('target_siteplan_kavling tk')
            ->select('
                tk.id_target_kavling,
                tk.id_target,
                tk.id_kavling,
                tk.id_tipe,
                tk.tipe_manual,
                target_tipe.no_tipe_rumah as target_no_tipe_rumah,
                target_tipe.tipe_rumah as target_tipe_rumah,
                target_tipe.is_active as target_tipe_is_active,
                k.no_kavling,
                k.id_tipe as kavling_id_tipe,
                k.id_mkdt,
                j.nama_jalan,
                t.no_tipe_rumah,
                t.tipe_rumah,
                t.no_tipe_rumah as kavling_no_tipe_rumah,
                t.tipe_rumah as kavling_tipe_rumah,
                m.status_mkdt,
                m.is_batal
            ')
            ->join('kavling k', 'k.id_kavling = tk.id_kavling')
            ->join('jalan j', 'j.id_jalan = k.id_jalan')
            ->join('tipe t', 't.id_tipe = k.id_tipe', 'left')
            ->join('tipe target_tipe', 'target_tipe.id_tipe = tk.id_tipe', 'left')
            ->join('mkdt m', 'm.id_mkdt = k.id_mkdt', 'left')
            ->where('tk.id_target', $idTarget)
            ->orderBy('j.nama_jalan', 'asc')
            ->orderBy('ABS(k.no_kavling)', 'asc')
            ->get()
            ->getResult();

        $planningRequests = $this->getLatestPlanningChangeRequests(
            array_map(static fn (object $row): int => (int) $row->id_kavling, $rows)
        );

        foreach ($rows as $row) {
            $idTipe = isset($row->id_tipe) && (int) $row->id_tipe > 0 ? (int) $row->id_tipe : null;
            $tipeManual = isset($row->tipe_manual) && trim((string) $row->tipe_manual) !== ''
                ? trim((string) $row->tipe_manual)
                : null;

            $row->id_target_kavling = (int) $row->id_target_kavling;
            $row->id_target = (int) $row->id_target;
            $row->id_kavling = (int) $row->id_kavling;
            $row->id_tipe = $idTipe;
            $row->tipe_manual = $tipeManual;
            $row->target_tipe_is_active = isset($row->target_tipe_is_active) && $row->target_tipe_is_active !== null && $row->target_tipe_is_active !== ''
                ? (int) $row->target_tipe_is_active
                : null;
            $row->kavling_id_tipe = isset($row->kavling_id_tipe) && (int) $row->kavling_id_tipe > 0
                ? (int) $row->kavling_id_tipe
                : null;
            $row->id_mkdt = isset($row->id_mkdt) && (int) $row->id_mkdt > 0
                ? (int) $row->id_mkdt
                : null;
            $row->is_batal = isset($row->is_batal) && $row->is_batal !== null && $row->is_batal !== ''
                ? (int) $row->is_batal
                : 0;
            $row->tipe_label = $this->resolveTipeLabel(
                $tipeManual,
                $row->target_no_tipe_rumah ?? null,
                $row->target_tipe_rumah ?? null
            );

            $planningMeta = $planningRequests[$row->id_kavling] ?? null;
            $row->pending_planning_request_id = $planningMeta['pending_planning_request_id'] ?? null;
            $row->planning_request_status = $planningMeta['planning_request_status'] ?? null;
            $row->planning_request_created_at = $planningMeta['planning_request_created_at'] ?? null;
        }

        return $rows;
    }

    public function findTargetKavlingRow(int $idTarget, int $idKavling): ?object
    {
        if ($idTarget <= 0 || $idKavling <= 0) {
            return null;
        }

        return $this->db->table('target_siteplan_kavling')
            ->where('id_target', $idTarget)
            ->where('id_kavling', $idKavling)
            ->get()
            ->getRow();
    }

    public function findTargetKavlingById(int $idTargetKavling): ?object
    {
        if ($idTargetKavling <= 0) {
            return null;
        }

        return $this->db->table('target_siteplan_kavling')
            ->where('id_target_kavling', $idTargetKavling)
            ->get()
            ->getRow();
    }

    public function findTargetKavlingsByIds(array $idTargetKavlings): array
    {
        $ids = array_values(array_unique(array_filter(
            array_map('intval', $idTargetKavlings),
            static fn (int $id): bool => $id > 0
        )));

        if ($ids === []) {
            return [];
        }

        $rows = $this->db->table('target_siteplan_kavling tk')
            ->select('
                tk.id_target_kavling,
                tk.id_target,
                tk.id_kavling,
                tk.id_tipe,
                tk.tipe_manual,
                ts.id_proyek,
                ts.tahun_target,
                ts.status as target_status,
                target_tipe.no_tipe_rumah as target_no_tipe_rumah,
                target_tipe.tipe_rumah as target_tipe_rumah,
                target_tipe.is_active as target_tipe_is_active,
                k.no_kavling,
                k.id_tipe as kavling_id_tipe,
                k.id_mkdt,
                j.nama_jalan,
                t.no_tipe_rumah as kavling_no_tipe_rumah,
                t.tipe_rumah as kavling_tipe_rumah,
                m.status_mkdt,
                m.is_batal
            ')
            ->join('target_siteplan ts', 'ts.id_target = tk.id_target')
            ->join('kavling k', 'k.id_kavling = tk.id_kavling')
            ->join('jalan j', 'j.id_jalan = k.id_jalan')
            ->join('tipe t', 't.id_tipe = k.id_tipe', 'left')
            ->join('tipe target_tipe', 'target_tipe.id_tipe = tk.id_tipe', 'left')
            ->join('mkdt m', 'm.id_mkdt = k.id_mkdt', 'left')
            ->where('ts.deleted_at', null)
            ->whereIn('tk.id_target_kavling', $ids)
            ->orderBy('tk.id_target_kavling', 'asc')
            ->get()
            ->getResult();

        foreach ($rows as $row) {
            $row->id_target_kavling = (int) $row->id_target_kavling;
            $row->id_target = (int) $row->id_target;
            $row->id_kavling = (int) $row->id_kavling;
            $row->id_proyek = (int) $row->id_proyek;
            $row->tahun_target = (int) $row->tahun_target;
            $row->id_tipe = isset($row->id_tipe) && (int) $row->id_tipe > 0 ? (int) $row->id_tipe : null;
            $row->tipe_manual = isset($row->tipe_manual) && trim((string) $row->tipe_manual) !== ''
                ? trim((string) $row->tipe_manual)
                : null;
            $row->kavling_id_tipe = isset($row->kavling_id_tipe) && (int) $row->kavling_id_tipe > 0
                ? (int) $row->kavling_id_tipe
                : null;
            $row->id_mkdt = isset($row->id_mkdt) && (int) $row->id_mkdt > 0
                ? (int) $row->id_mkdt
                : null;
            $row->is_batal = isset($row->is_batal) && $row->is_batal !== null && $row->is_batal !== ''
                ? (int) $row->is_batal
                : 0;
            $row->tipe_label = $this->resolveTipeLabel(
                $row->tipe_manual,
                $row->target_no_tipe_rumah ?? null,
                $row->target_tipe_rumah ?? null
            );
        }

        return $rows;
    }

    public function findTargetKavlingsByTargetAndKavlingIds(int $idTarget, array $idKavlings): array
    {
        $ids = array_values(array_unique(array_filter(
            array_map('intval', $idKavlings),
            static fn (int $id): bool => $id > 0
        )));

        if ($idTarget <= 0 || $ids === []) {
            return [];
        }

        $matched = $this->db->table('target_siteplan_kavling')
            ->select('id_target_kavling')
            ->where('id_target', $idTarget)
            ->whereIn('id_kavling', $ids)
            ->get()
            ->getResult();

        $targetKavlingIds = array_map(
            static fn (object $row): int => (int) $row->id_target_kavling,
            $matched
        );

        return $this->findTargetKavlingsByIds($targetKavlingIds);
    }

    private function getLatestPlanningChangeRequests(array $idKavlings): array
    {
        $ids = array_values(array_unique(array_filter(
            array_map('intval', $idKavlings),
            static fn (int $id): bool => $id > 0
        )));

        if ($ids === [] || ! $this->db->tableExists('kavling_request')) {
            return [];
        }

        $reqRows = $this->db->table('kavling_request')
            ->select('id_request, id_kavling, status, created_at')
            ->whereIn('id_kavling', $ids)
            ->where('jenis_request', 'ubah_tipe')
            ->orderBy('id_request', 'desc')
            ->get()
            ->getResult();

        $latestByKavling = [];
        $pendingByKavling = [];

        foreach ($reqRows as $reqRow) {
            $kId = (int) ($reqRow->id_kavling ?? 0);
            if ($kId <= 0) {
                continue;
            }

            if (! isset($latestByKavling[$kId])) {
                $latestByKavling[$kId] = $reqRow;
            }

            if (strtolower(trim((string) ($reqRow->status ?? ''))) === 'pending' && ! isset($pendingByKavling[$kId])) {
                $pendingByKavling[$kId] = $reqRow;
            }
        }

        $result = [];
        foreach ($ids as $kId) {
            $pendingReq = $pendingByKavling[$kId] ?? null;
            $latestReq = $pendingReq ?? ($latestByKavling[$kId] ?? null);

            if (! $latestReq) {
                continue;
            }

            $result[$kId] = [
                'pending_planning_request_id' => $pendingReq ? (int) $pendingReq->id_request : null,
                'planning_request_status'     => strtolower(trim((string) ($latestReq->status ?? ''))),
                'planning_request_created_at' => $latestReq->created_at ?? null,
            ];
        }

        return $result;
    }

    public function insertTargetKavling(array $fields): int
    {
        $this->db->table('target_siteplan_kavling')->insert($fields);

        return (int) $this->db->insertID();
    }

    public function updateTargetKavling(int $idTargetKavling, array $fields): bool
    {
        if ($idTargetKavling <= 0) {
            return false;
        }

        return (bool) $this->db->table('target_siteplan_kavling')
            ->where('id_target_kavling', $idTargetKavling)
            ->update($fields);
    }

    public function deleteTargetKavling(int $idTargetKavling): bool
    {
        if ($idTargetKavling <= 0) {
            return false;
        }

        return (bool) $this->db->table('target_siteplan_kavling')
            ->where('id_target_kavling', $idTargetKavling)
            ->delete();
    }

    public function findKavlingsWithActiveConsumer(array $idKavlings): array
    {
        $ids = array_values(array_unique(array_filter(
            array_map('intval', $idKavlings),
            static fn (int $id): bool => $id > 0
        )));

        if ($ids === []) {
            return [];
        }

        return $this->db->table('kavling k')
            ->select('k.id_kavling, k.no_kavling, k.id_mkdt, m.status_mkdt, m.is_batal')
            ->join('mkdt m', 'm.id_mkdt = k.id_mkdt', 'left')
            ->whereIn('k.id_kavling', $ids)
            ->where('k.id_mkdt >', 0)
            ->where('COALESCE(m.is_batal, 0) = 0', null, false)
            ->where("LOWER(TRIM(COALESCE(m.status_mkdt, ''))) NOT IN ('', 'batal', 'nup')", null, false)
            ->get()
            ->getResult();
    }

    public function findTipeForProject(int $idTipe, int $idProyek): ?object
    {
        if ($idTipe <= 0 || $idProyek <= 0) {
            return null;
        }

        return $this->db->table('tipe')
            ->select('id_tipe, id_proyek, no_tipe_rumah, tipe_rumah, is_subsidi, is_active')
            ->where('id_tipe', $idTipe)
            ->where('id_proyek', $idProyek)
            ->get()
            ->getRow();
    }

    public function countKavlingsInProject(int $idProyek, array $idKavlings): int
    {
        if ($idProyek <= 0 || $idKavlings === []) {
            return 0;
        }

        return (int) $this->db->table('kavling k')
            ->join('jalan j', 'j.id_jalan = k.id_jalan')
            ->join('cluster c', 'c.id_cluster = j.id_cluster')
            ->where('c.id_proyek', $idProyek)
            ->whereIn('k.id_kavling', $idKavlings)
            ->countAllResults();
    }

    public function countTargetHistory(int $idTarget): int
    {
        if ($idTarget <= 0) {
            return 0;
        }

        return $this->historyRepository->count([
            'module'         => 'target_siteplan',
            'reference_type' => 'target_siteplan',
            'reference_id'   => $idTarget,
        ]);
    }

    public function getHistory(int $idTarget): array
    {
        $result = $this->historyService->getList([
            'module'         => 'target_siteplan',
            'reference_type' => 'target_siteplan',
            'reference_id'   => $idTarget,
        ], 1000, 0);

        $dataRows = $result['data'] ?? [];
        $totalRows = count($dataRows);
        $rows = [];

        foreach ($dataRows as $index => $row) {
            $newData = is_array($row['new_data'] ?? null) ? $row['new_data'] : [];
            $oldData = is_array($row['old_data'] ?? null) ? $row['old_data'] : [];
            $metadata = is_array($row['metadata'] ?? null) ? $row['metadata'] : [];

            $version = isset($metadata['version']) && (int) $metadata['version'] > 0
                ? (int) $metadata['version']
                : max(1, $totalRows - $index);

            $beforeSnapshot = null;
            if (array_key_exists('before', $newData) && is_array($newData['before'])) {
                $beforeSnapshot = $newData['before'];
            } elseif ($oldData !== []) {
                $beforeSnapshot = $oldData;
            }

            $afterSnapshot = null;
            if (array_key_exists('after', $newData) && is_array($newData['after'])) {
                $afterSnapshot = $newData['after'];
            } elseif (! array_key_exists('before', $newData) && ! array_key_exists('after', $newData) && $newData !== []) {
                $afterSnapshot = $newData;
            }

            $snapshotPayload = (array_key_exists('before', $newData) || array_key_exists('after', $newData))
                ? $newData
                : ['before' => $beforeSnapshot, 'after' => $afterSnapshot];

            $diffSummary = isset($metadata['diff_summary']) && is_array($metadata['diff_summary'])
                ? $metadata['diff_summary']
                : null;

            $requestedByUsername = isset($metadata['requested_by_username']) && trim((string) $metadata['requested_by_username']) !== ''
                ? (string) $metadata['requested_by_username']
                : null;

            $approvedByUsername = isset($metadata['approved_by_username']) && trim((string) $metadata['approved_by_username']) !== ''
                ? (string) $metadata['approved_by_username']
                : null;

            $idRequest = isset($metadata['id_request']) && (int) $metadata['id_request'] > 0
                ? (int) $metadata['id_request']
                : null;

            $catatanRequest = isset($metadata['catatan_request']) && trim((string) $metadata['catatan_request']) !== ''
                ? trim((string) $metadata['catatan_request'])
                : null;

            $catatanApproval = isset($metadata['catatan_approval']) && trim((string) $metadata['catatan_approval']) !== ''
                ? trim((string) $metadata['catatan_approval'])
                : null;

            $rows[] = (object) [
                'id_target_history'     => $row['id'],
                'id_target'             => $row['reference_id'],
                'id_request'            => $idRequest,
                'aksi'                  => $row['action'],
                'action_label'          => $row['action_label'] ?? null,
                'deskripsi'             => $row['summary'],
                'version'               => $version,
                'version_label'         => 'v' . $version,
                'before_snapshot'       => $beforeSnapshot,
                'after_snapshot'        => $afterSnapshot,
                'diff_summary'          => $diffSummary,
                'requested_by_username' => $requestedByUsername,
                'approved_by_username'  => $approvedByUsername,
                'catatan_request'       => $catatanRequest,
                'catatan_approval'      => $catatanApproval,
                'snapshot'              => json_encode($snapshotPayload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                'add_by'                => $row['add_by'],
                'created_at'            => $row['created_at'],
                'add_by_username'       => $row['add_by_username'] ?? null,
            ];
        }

        return $rows;
    }

    public function getAvailableYears(int $idProyek): array
    {
        if ($idProyek <= 0) {
            return [];
        }

        $rows = $this->db->table('target_siteplan')
            ->select('tahun_target')
            ->distinct()
            ->where('id_proyek', $idProyek)
            ->where('status', 1)
            ->where('deleted_at', null)
            ->orderBy('tahun_target', 'desc')
            ->get()
            ->getResult();

        $years = [];
        foreach ($rows as $row) {
            $year = (int) ($row->tahun_target ?? 0);
            if ($year > 0) {
                $years[] = $year;
            }
        }

        return array_values(array_unique($years));
    }

    public function getKavlingTargetMap(int $idProyek, ?int $tahunTarget = null): array
    {
        if ($idProyek <= 0) {
            return [];
        }

        $builder = $this->db->table('target_siteplan_kavling tk')
            ->select('
                tk.id_kavling,
                tk.id_tipe,
                tk.tipe_manual,
                target_tipe.tipe_rumah as target_tipe_rumah,
                target_tipe.no_tipe_rumah as target_no_tipe_rumah,
                target_tipe.is_subsidi as target_is_subsidi,
                t.id_target,
                t.tahun_target,
                t.deskripsi
            ')
            ->join('target_siteplan t', 't.id_target = tk.id_target')
            ->join('tipe target_tipe', 'target_tipe.id_tipe = tk.id_tipe', 'left')
            ->where('t.id_proyek', $idProyek)
            ->where('t.status', 1)
            ->where('t.deleted_at', null);

        if ($tahunTarget !== null && $tahunTarget > 0) {
            $builder->where('t.tahun_target', $tahunTarget);
        }

        $rows = $builder
            ->orderBy('t.tahun_target', 'asc')
            ->get()
            ->getResult();

        $map = [];
        foreach ($rows as $row) {
            $idKavling = (int) $row->id_kavling;
            if (! isset($map[$idKavling])) {
                $map[$idKavling] = [
                    'id_target'         => [],
                    'tahun_target'      => [],
                    'deskripsi'         => [],
                    'id_tipe'           => null,
                    'tipe_manual'       => null,
                    'tipe_label'        => null,
                    'target_is_subsidi' => null,
                ];
            }

            $map[$idKavling]['id_target'][] = (int) $row->id_target;
            $map[$idKavling]['tahun_target'][] = (int) $row->tahun_target;
            if ((string) ($row->deskripsi ?? '') !== '') {
                $map[$idKavling]['deskripsi'][] = $row->deskripsi;
            }

            $idTipe = isset($row->id_tipe) && (int) $row->id_tipe > 0 ? (int) $row->id_tipe : null;
            $tipeManual = isset($row->tipe_manual) && trim((string) $row->tipe_manual) !== ''
                ? trim((string) $row->tipe_manual)
                : null;
            $tipeLabel = $this->resolveTipeLabel(
                $tipeManual,
                $row->target_no_tipe_rumah ?? null,
                $row->target_tipe_rumah ?? null
            );
            $targetIsSubsidi = isset($row->target_is_subsidi) && $row->target_is_subsidi !== null && $row->target_is_subsidi !== ''
                ? (int) $row->target_is_subsidi
                : null;

            if ($idTipe !== null || $tipeManual !== null || $tipeLabel !== null) {
                $map[$idKavling]['id_tipe'] = $idTipe;
                $map[$idKavling]['tipe_manual'] = $tipeManual;
                $map[$idKavling]['tipe_label'] = $tipeLabel;
                $map[$idKavling]['target_is_subsidi'] = $targetIsSubsidi;
            }
        }

        foreach ($map as $idKavling => $item) {
            $map[$idKavling] = [
                'id_target'         => implode(',', array_unique($item['id_target'])),
                'tahun_target'      => implode(', ', array_unique($item['tahun_target'])),
                'deskripsi'         => implode(' | ', array_unique($item['deskripsi'])),
                'id_tipe'           => $item['id_tipe'],
                'tipe_manual'       => $item['tipe_manual'],
                'tipe_label'        => $item['tipe_label'],
                'target_is_subsidi' => $item['target_is_subsidi'],
            ];
        }

        return $map;
    }

    public function findPendingRequest(int $idTarget, int $idKavling): ?object
    {
        if ($idTarget <= 0 || $idKavling <= 0) {
            return null;
        }

        $row = $this->requestModel
            ->where('id_target', $idTarget)
            ->where('id_kavling', $idKavling)
            ->where('status', 'pending')
            ->orderBy('id_request', 'desc')
            ->first();

        return is_object($row) ? $row : null;
    }

    public function findRequestById(int $idRequest): ?object
    {
        if ($idRequest <= 0) {
            return null;
        }

        $row = $this->requestModel->find($idRequest);

        return is_object($row) ? $row : null;
    }

    public function insertRequest(array $data): int
    {
        $this->requestModel->insert($data, true);

        return (int) $this->requestModel->getInsertID();
    }

    public function updateRequest(int $idRequest, array $data): bool
    {
        if ($idRequest <= 0 || $data === []) {
            return false;
        }

        return (bool) $this->requestModel->update($idRequest, $data);
    }

    public function getRequests(int $idProyek, ?int $idTarget = null, ?int $tahunTarget = null, ?string $status = null): array
    {
        if ($idProyek <= 0) {
            return [];
        }

        $builder = $this->db->table('target_siteplan_request r')
            ->select('
                r.id_request,
                r.id_target,
                r.id_kavling,
                r.old_id_tipe,
                r.old_tipe_manual,
                r.new_id_tipe,
                r.new_tipe_manual,
                r.catatan_request,
                r.status,
                r.request_by,
                r.request_at,
                r.approve_by,
                r.approve_at,
                r.catatan_approval,
                t.id_proyek,
                t.tahun_target,
                t.deskripsi as target_deskripsi,
                k.no_kavling,
                k.id_tipe as kavling_id_tipe,
                k.id_mkdt,
                j.nama_jalan,
                c.nama_cluster,
                old_t.no_tipe_rumah as old_no_tipe_rumah,
                old_t.tipe_rumah as old_tipe_rumah,
                new_t.no_tipe_rumah as new_no_tipe_rumah,
                new_t.tipe_rumah as new_tipe_rumah,
                new_t.is_active as new_tipe_is_active,
                master_t.no_tipe_rumah as master_no_tipe_rumah,
                master_t.tipe_rumah as master_tipe_rumah,
                req_u.username as request_by_username,
                app_u.username as approve_by_username
            ')
            ->join('target_siteplan t', 't.id_target = r.id_target')
            ->join('kavling k', 'k.id_kavling = r.id_kavling')
            ->join('jalan j', 'j.id_jalan = k.id_jalan')
            ->join('cluster c', 'c.id_cluster = j.id_cluster')
            ->join('tipe old_t', 'old_t.id_tipe = r.old_id_tipe', 'left')
            ->join('tipe new_t', 'new_t.id_tipe = r.new_id_tipe', 'left')
            ->join('tipe master_t', 'master_t.id_tipe = k.id_tipe', 'left')
            ->join('users req_u', 'req_u.id = r.request_by', 'left')
            ->join('users app_u', 'app_u.id = r.approve_by', 'left')
            ->where('t.id_proyek', $idProyek)
            ->where('t.deleted_at', null);

        if ($idTarget !== null && $idTarget > 0) {
            $builder->where('r.id_target', $idTarget);
        }

        if ($tahunTarget !== null && $tahunTarget > 0) {
            $builder->where('t.tahun_target', $tahunTarget);
        }

        if ($status !== null && trim($status) !== '' && strtolower(trim($status)) !== 'all') {
            $builder->where('r.status', strtolower(trim($status)));
        }

        $rows = $builder
            ->orderBy("CASE WHEN r.status = 'pending' THEN 0 ELSE 1 END", 'asc', false)
            ->orderBy('r.id_request', 'desc')
            ->get()
            ->getResult();

        foreach ($rows as $row) {
            $oldIdTipe = isset($row->old_id_tipe) && (int) $row->old_id_tipe > 0 ? (int) $row->old_id_tipe : null;
            $oldTipeManual = isset($row->old_tipe_manual) && trim((string) $row->old_tipe_manual) !== ''
                ? trim((string) $row->old_tipe_manual)
                : null;
            $newIdTipe = isset($row->new_id_tipe) && (int) $row->new_id_tipe > 0 ? (int) $row->new_id_tipe : null;
            $newTipeManual = isset($row->new_tipe_manual) && trim((string) $row->new_tipe_manual) !== ''
                ? trim((string) $row->new_tipe_manual)
                : null;

            $row->id_request = (int) $row->id_request;
            $row->id_target = (int) $row->id_target;
            $row->id_kavling = (int) $row->id_kavling;
            $row->id_proyek = (int) $row->id_proyek;
            $row->tahun_target = (int) $row->tahun_target;
            $row->old_id_tipe = $oldIdTipe;
            $row->old_tipe_manual = $oldTipeManual;
            $row->new_id_tipe = $newIdTipe;
            $row->new_tipe_manual = $newTipeManual;
            $row->kavling_id_tipe = isset($row->kavling_id_tipe) && (int) $row->kavling_id_tipe > 0
                ? (int) $row->kavling_id_tipe
                : null;
            $row->id_mkdt = isset($row->id_mkdt) && (int) $row->id_mkdt > 0 ? (int) $row->id_mkdt : null;
            $row->request_by = isset($row->request_by) && (int) $row->request_by > 0 ? (int) $row->request_by : null;
            $row->approve_by = isset($row->approve_by) && (int) $row->approve_by > 0 ? (int) $row->approve_by : null;
            $row->new_tipe_is_active = isset($row->new_tipe_is_active) && $row->new_tipe_is_active !== null && $row->new_tipe_is_active !== ''
                ? (int) $row->new_tipe_is_active
                : null;
            $row->old_tipe_label = $this->resolveTipeLabel(
                $oldTipeManual,
                $row->old_no_tipe_rumah ?? null,
                $row->old_tipe_rumah ?? null
            );
            $row->new_tipe_label = $this->resolveTipeLabel(
                $newTipeManual,
                $row->new_no_tipe_rumah ?? null,
                $row->new_tipe_rumah ?? null
            );
            $row->master_tipe_label = $this->resolveTipeLabel(
                null,
                $row->master_no_tipe_rumah ?? null,
                $row->master_tipe_rumah ?? null
            );
        }

        return $rows;
    }

    public function getRequestCounts(int $idProyek, ?int $idTarget = null, ?int $tahunTarget = null): array
    {
        $counts = [
            'all'      => 0,
            'pending'  => 0,
            'approved' => 0,
            'rejected' => 0,
        ];

        if ($idProyek <= 0) {
            return $counts;
        }

        $builder = $this->db->table('target_siteplan_request r')
            ->select('r.status, COUNT(*) as total')
            ->join('target_siteplan t', 't.id_target = r.id_target')
            ->where('t.id_proyek', $idProyek)
            ->where('t.deleted_at', null);

        if ($idTarget !== null && $idTarget > 0) {
            $builder->where('r.id_target', $idTarget);
        }

        if ($tahunTarget !== null && $tahunTarget > 0) {
            $builder->where('t.tahun_target', $tahunTarget);
        }

        $rows = $builder
            ->groupBy('r.status')
            ->get()
            ->getResult();

        foreach ($rows as $row) {
            $status = strtolower(trim((string) ($row->status ?? '')));
            $total = (int) ($row->total ?? 0);
            $counts['all'] += $total;

            if (array_key_exists($status, $counts)) {
                $counts[$status] += $total;
            }
        }

        return $counts;
    }

    private function resolveTipeLabel(?string $tipeManual, ?string $noTipeRumah, ?string $tipeRumah): ?string
    {
        if ($tipeManual !== null && $tipeManual !== '') {
            return $tipeManual;
        }

        $noTipe = trim((string) ($noTipeRumah ?? ''));
        $namaTipe = trim((string) ($tipeRumah ?? ''));

        if ($noTipe !== '' && $namaTipe !== '') {
            return $noTipe . ' / ' . $namaTipe;
        }

        if ($namaTipe !== '') {
            return $namaTipe;
        }

        if ($noTipe !== '') {
            return $noTipe;
        }

        return null;
    }
}

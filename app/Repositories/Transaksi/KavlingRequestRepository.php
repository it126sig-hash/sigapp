<?php

namespace App\Repositories\Transaksi;

use CodeIgniter\Database\BaseConnection;

class KavlingRequestRepository
{
    protected BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
    }

    /**
     * @param int[] $idJalans
     */
    public function insertJalans(int $idRequest, array $idJalans): void
    {
        if ($idRequest <= 0 || ! $this->db->tableExists('kavling_request_jalan')) {
            return;
        }

        $ids = $this->normalizeIds($idJalans);
        if ($ids === []) {
            return;
        }

        $now  = date('Y-m-d H:i:s');
        $rows = [];
        foreach ($ids as $idJalan) {
            $rows[] = [
                'id_request' => $idRequest,
                'id_jalan'   => $idJalan,
                'created_at' => $now,
            ];
        }

        $this->db->table('kavling_request_jalan')->insertBatch($rows);
    }

    /**
     * @param array<int, array{id_kavling: int, old_id_tipe?: ?int}> $kavlingRows
     */
    public function insertKavlings(int $idRequest, array $kavlingRows): void
    {
        if ($idRequest <= 0 || ! $this->db->tableExists('kavling_request_kavling')) {
            return;
        }

        $now  = date('Y-m-d H:i:s');
        $rows = [];
        $seen = [];

        foreach ($kavlingRows as $entry) {
            $kId = (int) ($entry['id_kavling'] ?? 0);
            if ($kId <= 0 || isset($seen[$kId])) {
                continue;
            }
            $seen[$kId] = true;

            $oldIdTipe = isset($entry['old_id_tipe']) && (int) $entry['old_id_tipe'] > 0
                ? (int) $entry['old_id_tipe']
                : null;

            $rows[] = [
                'id_request'  => $idRequest,
                'id_kavling'  => $kId,
                'old_id_tipe' => $oldIdTipe,
                'created_at'  => $now,
            ];
        }

        if ($rows !== []) {
            $this->db->table('kavling_request_kavling')->insertBatch($rows);
        }
    }

    /**
     * @param int[] $idKavlings
     * @return array<int, int> map id_kavling => id_request
     */
    public function findPendingUbahTipeMap(int $idProyek, array $idKavlings): array
    {
        $ids = $this->normalizeIds($idKavlings);
        if ($idProyek <= 0 || $ids === [] || ! $this->db->tableExists('kavling_request')) {
            return [];
        }

        $map = [];

        if ($this->db->tableExists('kavling_request_kavling')) {
            $childRows = $this->db->table('kavling_request_kavling krk')
                ->select('kr.id_request, krk.id_kavling')
                ->join('kavling_request kr', 'kr.id_request = krk.id_request')
                ->where('kr.id_proyek', $idProyek)
                ->where('kr.jenis_request', 'ubah_tipe')
                ->where('kr.status', 'pending')
                ->whereIn('krk.id_kavling', $ids)
                ->orderBy('kr.id_request', 'desc')
                ->get()
                ->getResult();

            foreach ($childRows as $row) {
                $kId   = (int) ($row->id_kavling ?? 0);
                $reqId = (int) ($row->id_request ?? 0);
                if ($kId > 0 && $reqId > 0 && (! isset($map[$kId]) || $reqId > $map[$kId])) {
                    $map[$kId] = $reqId;
                }
            }
        }

        $legacyRows = $this->db->table('kavling_request')
            ->select('id_request, id_kavling')
            ->where('id_proyek', $idProyek)
            ->where('jenis_request', 'ubah_tipe')
            ->where('status', 'pending')
            ->whereIn('id_kavling', $ids)
            ->orderBy('id_request', 'desc')
            ->get()
            ->getResult();

        foreach ($legacyRows as $row) {
            $kId   = (int) ($row->id_kavling ?? 0);
            $reqId = (int) ($row->id_request ?? 0);
            if ($kId > 0 && $reqId > 0 && (! isset($map[$kId]) || $reqId > $map[$kId])) {
                $map[$kId] = $reqId;
            }
        }

        return $map;
    }

    /**
     * @param int[] $idKavlings
     * @return array<int, object> map id_kavling => eligibility row
     */
    public function getKavlingEligibilityRows(int $idProyek, array $idKavlings): array
    {
        $ids = $this->normalizeIds($idKavlings);
        if ($idProyek <= 0 || $ids === [] || ! $this->db->tableExists('kavling')) {
            return [];
        }

        $hasTipe         = $this->db->tableExists('tipe');
        $hasMkdt         = $this->db->tableExists('mkdt');
        $hasAkad         = $hasMkdt && $this->db->fieldExists('akad', 'mkdt');
        $hasAkadIndent   = $hasMkdt && $this->db->fieldExists('akad_indent', 'mkdt');
        $hasIdKonsumen   = $hasMkdt && $this->db->fieldExists('id_konsumen', 'mkdt');
        $hasKonsumen     = $hasIdKonsumen && $this->db->tableExists('konsumen');

        $builder = $this->db->table('kavling k')
            ->select('k.id_kavling, k.id_tipe, k.no_kavling, j.nama_jalan, c.nama_cluster, k.id_mkdt')
            ->join('jalan j', 'j.id_jalan = k.id_jalan')
            ->join('cluster c', 'c.id_cluster = j.id_cluster')
            ->where('c.id_proyek', $idProyek)
            ->whereIn('k.id_kavling', $ids);

        if ($hasTipe) {
            $builder->select('t.no_tipe_rumah, t.tipe_rumah')
                ->join('tipe t', 't.id_tipe = k.id_tipe', 'left');
        } else {
            $builder->select('NULL as no_tipe_rumah, NULL as tipe_rumah', false);
        }

        if ($hasMkdt) {
            $builder->select('m.is_batal, m.status_mkdt')
                ->select($hasAkad ? 'm.akad' : '0 as akad', $hasAkad)
                ->select($hasAkadIndent ? 'm.akad_indent' : '0 as akad_indent', $hasAkadIndent)
                ->join('mkdt m', 'm.id_mkdt = k.id_mkdt', 'left');
        } else {
            $builder->select('0 as is_batal, NULL as status_mkdt, 0 as akad, 0 as akad_indent', false);
        }

        if ($hasKonsumen) {
            $builder->select('kon.nama_konsumen')
                ->join('konsumen kon', 'kon.id_konsumen = m.id_konsumen', 'left');
        } else {
            $builder->select('NULL as nama_konsumen', false);
        }

        $rows = $builder->get()->getResult();
        $map  = [];
        foreach ($rows as $row) {
            $map[(int) $row->id_kavling] = $row;
        }

        return $map;
    }

    /**
     * @param int[] $idJalans
     * @return object[]
     */
    public function getJalanRowsByProyek(int $idProyek, array $idJalans): array
    {
        $ids = $this->normalizeIds($idJalans);
        if ($idProyek <= 0 || $ids === [] || ! $this->db->tableExists('jalan')) {
            return [];
        }

        return $this->db->table('jalan j')
            ->select('j.id_jalan, j.id_cluster, j.nama_jalan, c.nama_cluster')
            ->join('cluster c', 'c.id_cluster = j.id_cluster')
            ->where('c.id_proyek', $idProyek)
            ->whereIn('j.id_jalan', $ids)
            ->orderBy('j.nama_jalan', 'asc')
            ->get()
            ->getResult();
    }

    public function findTipeByIdAndProyek(int $idTipe, int $idProyek): ?object
    {
        if ($idTipe <= 0 || $idProyek <= 0 || ! $this->db->tableExists('tipe')) {
            return null;
        }

        $row = $this->db->table('tipe')
            ->where('id_tipe', $idTipe)
            ->where('id_proyek', $idProyek)
            ->get()
            ->getRow();

        return is_object($row) ? $row : null;
    }

    /**
     * @return object[]
     */
    public function getList(
        int $idProyek,
        ?string $jenisRequest = null,
        ?string $status = null,
        ?int $page = null,
        ?int $perPage = null
    ): array {
        if ($idProyek <= 0 || ! $this->db->tableExists('kavling_request')) {
            return [];
        }

        $hasAppliedCol    = $this->db->fieldExists('applied_id_tipe', 'kavling_request');
        $hasAlasanBatal   = $this->db->fieldExists('alasan_batal', 'kavling_request');
        $hasCatatanCol    = $this->db->fieldExists('catatan_planning', 'kavling_request');
        $hasProcessedBy   = $this->db->fieldExists('processed_by', 'kavling_request');
        $hasProcessedAt   = $this->db->fieldExists('processed_at', 'kavling_request');
        $hasUserName      = $this->db->tableExists('users') && $this->db->fieldExists('name', 'users');
        $hasTipeSpecCols  = $this->db->tableExists('tipe') && $this->db->fieldExists('lb', 'tipe');
        $hasTipeMediaCols = $this->db->tableExists('tipe') && $this->db->fieldExists('id_gambar_tipe', 'tipe');

        $builder = $this->db->table('kavling_request kr')
            ->select('kr.*')
            ->select($hasUserName ? 'pengaju.name as pengaju_name' : 'NULL as pengaju_name', $hasUserName)
            ->select('pengaju.username as pengaju_username')
            ->select('c.nama_cluster, j.nama_jalan')
            ->select('k.no_kavling as existing_no_kavling, kj.nama_jalan as existing_nama_jalan')
            ->join('users pengaju', 'pengaju.id = kr.created_by', 'left')
            ->join('cluster c', 'c.id_cluster = kr.id_cluster', 'left')
            ->join('jalan j', 'j.id_jalan = kr.id_jalan', 'left')
            ->join('kavling k', 'k.id_kavling = kr.id_kavling', 'left')
            ->join('jalan kj', 'kj.id_jalan = k.id_jalan', 'left');

        if ($hasProcessedBy) {
            $builder->select($hasUserName ? 'pemroses.name as processed_by_name' : 'NULL as processed_by_name', $hasUserName)
                ->select('pemroses.username as processed_by_username')
                ->join('users pemroses', 'pemroses.id = kr.processed_by', 'left');
        } else {
            $builder->select('NULL as processed_by_name, NULL as processed_by_username', false);
        }

        if (! $hasAppliedCol) {
            $builder->select('NULL as applied_id_tipe', false);
        }
        if (! $hasAlasanBatal) {
            $builder->select('NULL as alasan_batal', false);
        }
        if (! $hasCatatanCol) {
            $builder->select('NULL as catatan_planning', false);
        }
        if (! $hasProcessedAt) {
            $builder->select('NULL as processed_at', false);
        }

        $this->selectTipeAliasFields($builder, 'req_tipe', 'req', $hasTipeSpecCols, $hasTipeMediaCols);
        $builder->join('tipe req_tipe', 'req_tipe.id_tipe = kr.id_tipe', 'left');

        if ($hasAppliedCol) {
            $this->selectTipeAliasFields($builder, 'app_tipe', 'app', $hasTipeSpecCols, $hasTipeMediaCols);
            $builder->join('tipe app_tipe', 'app_tipe.id_tipe = kr.applied_id_tipe', 'left');
        }

        $builder->where('kr.id_proyek', $idProyek);
        $this->applyListFilters($builder, 'kr', $jenisRequest, $status);

        $builder->orderBy('kr.created_at', 'DESC')
            ->orderBy('kr.id_request', 'DESC');

        if ($perPage !== null && $perPage > 0) {
            $safePage = max(1, (int) ($page ?? 1));
            $offset   = ($safePage - 1) * $perPage;
            $builder->limit($perPage, $offset);
        }

        $rows = $builder->get()->getResult();
        if ($rows === []) {
            return [];
        }

        $requestIds = array_map(static fn (object $r): int => (int) $r->id_request, $rows);
        $jalansMap  = $this->loadJalansForRequests($requestIds, $rows);
        $kavlingMap = $this->loadKavlingsForRequests($requestIds, $rows);

        foreach ($rows as $row) {
            $reqId         = (int) $row->id_request;
            $row->jalans   = $jalansMap[$reqId] ?? [];
            $row->kavlings = $kavlingMap[$reqId] ?? [];
        }

        return $rows;
    }

    /**
     * @return array{all: int, pending: int, tambah_baru_pending: int, ubah_tipe_pending: int, approved: int, rejected: int, cancelled: int, history: int}
     */
    public function getStatusCounts(int $idProyek): array
    {
        if ($idProyek <= 0 || ! $this->db->tableExists('kavling_request')) {
            return [
                'all'                 => 0,
                'pending'             => 0,
                'tambah_baru_pending' => 0,
                'ubah_tipe_pending'   => 0,
                'approved'            => 0,
                'rejected'            => 0,
                'cancelled'           => 0,
                'history'             => 0,
            ];
        }

        $row = $this->db->table('kavling_request')
            ->select("
                COUNT(*) AS total_count,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) AS pending_count,
                COUNT(CASE WHEN status = 'pending' AND jenis_request = 'tambah_baru' THEN 1 END) AS tambah_baru_pending_count,
                COUNT(CASE WHEN status = 'pending' AND jenis_request = 'ubah_tipe' THEN 1 END) AS ubah_tipe_pending_count,
                COUNT(CASE WHEN status = 'approved' THEN 1 END) AS approved_count,
                COUNT(CASE WHEN status = 'rejected' THEN 1 END) AS rejected_count,
                COUNT(CASE WHEN status = 'cancelled' THEN 1 END) AS cancelled_count
            ", false)
            ->where('id_proyek', $idProyek)
            ->get()
            ->getRow();

        $approved  = (int) ($row->approved_count ?? 0);
        $rejected  = (int) ($row->rejected_count ?? 0);
        $cancelled = (int) ($row->cancelled_count ?? 0);

        return [
            'all'                 => (int) ($row->total_count ?? 0),
            'pending'             => (int) ($row->pending_count ?? 0),
            'tambah_baru_pending' => (int) ($row->tambah_baru_pending_count ?? 0),
            'ubah_tipe_pending'   => (int) ($row->ubah_tipe_pending_count ?? 0),
            'approved'            => $approved,
            'rejected'            => $rejected,
            'cancelled'           => $cancelled,
            'history'             => $approved + $rejected + $cancelled,
        ];
    }

    public function countFiltered(int $idProyek, ?string $jenisRequest = null, ?string $status = null): int
    {
        if ($idProyek <= 0 || ! $this->db->tableExists('kavling_request')) {
            return 0;
        }

        $builder = $this->db->table('kavling_request')
            ->where('id_proyek', $idProyek);

        $this->applyListFilters($builder, 'kavling_request', $jenisRequest, $status);

        return (int) $builder->countAllResults();
    }

    /**
     * @param int[] $idRequests
     * @return object[]
     */
    public function findRequestsByIdsAndProyek(int $idProyek, array $idRequests): array
    {
        $ids = $this->normalizeIds($idRequests);
        if ($idProyek <= 0 || $ids === [] || ! $this->db->tableExists('kavling_request')) {
            return [];
        }

        $rows = $this->db->table('kavling_request')
            ->where('id_proyek', $idProyek)
            ->whereIn('id_request', $ids)
            ->orderBy('id_request', 'ASC')
            ->get()
            ->getResult();

        if ($rows === []) {
            return [];
        }

        $foundIds = array_map(static fn (object $r): int => (int) $r->id_request, $rows);
        if ($this->db->tableExists('kavling') && $this->db->tableExists('jalan') && $this->db->tableExists('tipe')) {
            $kavlingMap = $this->loadKavlingsForRequests($foundIds, $rows);
            foreach ($rows as $row) {
                $row->kavlings = $kavlingMap[(int) $row->id_request] ?? [];
            }
        } else {
            foreach ($rows as $row) {
                $row->kavlings = [];
            }
        }

        return $rows;
    }

    /**
     * @param int[] $idRequests
     */
    public function markRequestsCancelled(array $idRequests, int $userId, ?string $alasanBatal, string $now): int
    {
        $ids = $this->normalizeIds($idRequests);
        if ($ids === [] || ! $this->db->tableExists('kavling_request')) {
            return 0;
        }

        $fields = [
            'status' => 'cancelled',
        ];

        if ($this->db->fieldExists('alasan_batal', 'kavling_request')) {
            $fields['alasan_batal'] = $alasanBatal;
        }
        if ($this->db->fieldExists('processed_by', 'kavling_request')) {
            $fields['processed_by'] = $userId > 0 ? $userId : null;
        }
        if ($this->db->fieldExists('processed_at', 'kavling_request')) {
            $fields['processed_at'] = $now;
        }
        if ($this->db->fieldExists('updated_at', 'kavling_request')) {
            $fields['updated_at'] = $now;
        }

        $this->db->table('kavling_request')
            ->whereIn('id_request', $ids)
            ->where('status', 'pending')
            ->update($fields);

        return (int) $this->db->affectedRows();
    }

    /**
     * @return object[]
     */
    public function getKavlingsByRequestId(int $idRequest): array
    {
        if ($idRequest <= 0 || ! $this->db->tableExists('kavling')) {
            return [];
        }

        $rows = [];
        if ($this->db->tableExists('kavling_request_kavling')) {
            $rows = $this->db->table('kavling_request_kavling krk')
                ->select('
                    krk.id_kavling,
                    krk.old_id_tipe,
                    k.no_kavling,
                    k.id_tipe as current_id_tipe,
                    k.id_mkdt,
                    j.nama_jalan,
                    old_t.no_tipe_rumah as old_no_tipe_rumah,
                    old_t.tipe_rumah as old_tipe_rumah,
                    cur_t.no_tipe_rumah as current_no_tipe_rumah,
                    cur_t.tipe_rumah as current_tipe_rumah
                ')
                ->join('kavling k', 'k.id_kavling = krk.id_kavling')
                ->join('jalan j', 'j.id_jalan = k.id_jalan', 'left')
                ->join('tipe old_t', 'old_t.id_tipe = krk.old_id_tipe', 'left')
                ->join('tipe cur_t', 'cur_t.id_tipe = k.id_tipe', 'left')
                ->where('krk.id_request', $idRequest)
                ->orderBy('j.nama_jalan', 'ASC')
                ->orderBy('ABS(k.no_kavling)', 'ASC')
                ->orderBy('k.no_kavling', 'ASC')
                ->get()
                ->getResult();
        }

        if ($rows !== []) {
            return $rows;
        }

        $header = $this->db->table('kavling_request')
            ->select('id_kavling')
            ->where('id_request', $idRequest)
            ->get()
            ->getRow();

        $legacyKavlingId = (int) ($header->id_kavling ?? 0);
        if ($legacyKavlingId <= 0) {
            return [];
        }

        return $this->db->table('kavling k')
            ->select('
                k.id_kavling,
                NULL as old_id_tipe,
                k.no_kavling,
                k.id_tipe as current_id_tipe,
                k.id_mkdt,
                j.nama_jalan,
                NULL as old_no_tipe_rumah,
                NULL as old_tipe_rumah,
                cur_t.no_tipe_rumah as current_no_tipe_rumah,
                cur_t.tipe_rumah as current_tipe_rumah
            ', false)
            ->join('jalan j', 'j.id_jalan = k.id_jalan', 'left')
            ->join('tipe cur_t', 'cur_t.id_tipe = k.id_tipe', 'left')
            ->where('k.id_kavling', $legacyKavlingId)
            ->get()
            ->getResult();
    }

    /**
     * @param int[] $idKavlings
     */
    public function updateKavlingsTipe(array $idKavlings, int $idTipe, int $userId, string $now): bool
    {
        $ids = $this->normalizeIds($idKavlings);
        if ($ids === [] || $idTipe <= 0) {
            return false;
        }

        $fields = [
            'id_tipe' => $idTipe,
        ];

        if ($this->db->fieldExists('edit_by', 'kavling')) {
            $fields['edit_by'] = $userId > 0 ? (string) $userId : null;
        }

        if ($this->db->fieldExists('updated_at', 'kavling')) {
            $fields['updated_at'] = $now;
        }

        return (bool) $this->db->table('kavling')
            ->whereIn('id_kavling', $ids)
            ->update($fields);
    }

    public function getProyekName(int $idProyek): string
    {
        if ($idProyek <= 0 || ! $this->db->tableExists('proyek')) {
            return 'Proyek #' . $idProyek;
        }

        $row = $this->db->table('proyek')
            ->select('nama_proyek')
            ->where('id_proyek', $idProyek)
            ->get()
            ->getRow();

        return $row->nama_proyek ?? ('Proyek #' . $idProyek);
    }

    public function getUserDisplayName(int $userId): string
    {
        if ($userId <= 0 || ! $this->db->tableExists('users')) {
            return 'User #' . $userId;
        }

        $hasName = $this->db->fieldExists('name', 'users');
        $builder = $this->db->table('users')
            ->select($hasName ? 'name, username' : 'username')
            ->where('id', $userId);

        $row = $builder->get()->getRow();
        if (! $row) {
            return 'User #' . $userId;
        }

        $name = $hasName ? trim((string) ($row->name ?? '')) : '';

        return $name !== '' ? $name : ((string) ($row->username ?? ('User #' . $userId)));
    }

    public function getClusterName(int $idCluster): ?string
    {
        if ($idCluster <= 0 || ! $this->db->tableExists('cluster')) {
            return null;
        }

        $row = $this->db->table('cluster')
            ->select('nama_cluster')
            ->where('id_cluster', $idCluster)
            ->get()
            ->getRow();

        return $row->nama_cluster ?? null;
    }

    public function getJalanName(int $idJalan): ?string
    {
        if ($idJalan <= 0 || ! $this->db->tableExists('jalan')) {
            return null;
        }

        $row = $this->db->table('jalan')
            ->select('nama_jalan')
            ->where('id_jalan', $idJalan)
            ->get()
            ->getRow();

        return $row->nama_jalan ?? null;
    }

    /**
     * @param int[] $idKavlings
     * @return array<int, string> map id_kavling => label
     */
    public function getKavlingLabels(array $idKavlings): array
    {
        $ids = $this->normalizeIds($idKavlings);
        if ($ids === [] || ! $this->db->tableExists('kavling')) {
            return [];
        }

        $rows = $this->db->table('kavling k')
            ->select('k.id_kavling, k.no_kavling, j.nama_jalan')
            ->join('jalan j', 'j.id_jalan = k.id_jalan', 'left')
            ->whereIn('k.id_kavling', $ids)
            ->orderBy('j.nama_jalan', 'ASC')
            ->orderBy('ABS(k.no_kavling)', 'ASC')
            ->orderBy('k.no_kavling', 'ASC')
            ->get()
            ->getResult();

        $labels = [];
        foreach ($rows as $row) {
            $jalan = trim((string) ($row->nama_jalan ?? ''));
            $noKav = trim((string) ($row->no_kavling ?? ''));
            $labels[(int) $row->id_kavling] = $jalan !== ''
                ? ($jalan . ' No. ' . $noKav)
                : ($noKav !== '' ? $noKav : ('#' . $row->id_kavling));
        }

        return $labels;
    }

    private function applyListFilters($builder, string $tableAlias, ?string $jenisRequest, ?string $status): void
    {
        if ($jenisRequest !== null && in_array($jenisRequest, ['tambah_baru', 'ubah_tipe'], true)) {
            $builder->where("{$tableAlias}.jenis_request", $jenisRequest);
        }

        if ($status !== null && $status !== '') {
            if (in_array($status, ['pending', 'approved', 'rejected', 'cancelled'], true)) {
                $builder->where("{$tableAlias}.status", $status);
            } elseif ($status === 'history') {
                $builder->whereIn("{$tableAlias}.status", ['approved', 'rejected', 'cancelled']);
            }
        }
    }

    private function selectTipeAliasFields(
        $builder,
        string $tableAlias,
        string $colPrefix,
        bool $hasSpecCols,
        bool $hasMediaCols
    ): void {
        $builder->select("
            {$tableAlias}.id_tipe as {$colPrefix}_id_tipe,
            {$tableAlias}.no_tipe_rumah as {$colPrefix}_no_tipe_rumah,
            {$tableAlias}.tipe_rumah as {$colPrefix}_tipe_rumah,
            {$tableAlias}.is_subsidi as {$colPrefix}_is_subsidi
        ");

        if ($hasSpecCols) {
            $builder->select("
                {$tableAlias}.lb as {$colPrefix}_lb,
                {$tableAlias}.lt as {$colPrefix}_lt,
                {$tableAlias}.jumlah_kamar_tidur as {$colPrefix}_jumlah_kamar_tidur,
                {$tableAlias}.jumlah_kamar_mandi as {$colPrefix}_jumlah_kamar_mandi,
                {$tableAlias}.spesifikasi_teknis_atap as {$colPrefix}_spesifikasi_teknis_atap,
                {$tableAlias}.spesifikasi_teknis_dinding as {$colPrefix}_spesifikasi_teknis_dinding,
                {$tableAlias}.spesifikasi_teknis_lantai as {$colPrefix}_spesifikasi_teknis_lantai,
                {$tableAlias}.spesifikasi_teknis_pondasi as {$colPrefix}_spesifikasi_teknis_pondasi
            ");
        } else {
            $builder->select("
                NULL as {$colPrefix}_lb,
                NULL as {$colPrefix}_lt,
                0 as {$colPrefix}_jumlah_kamar_tidur,
                0 as {$colPrefix}_jumlah_kamar_mandi,
                NULL as {$colPrefix}_spesifikasi_teknis_atap,
                NULL as {$colPrefix}_spesifikasi_teknis_dinding,
                NULL as {$colPrefix}_spesifikasi_teknis_lantai,
                NULL as {$colPrefix}_spesifikasi_teknis_pondasi
            ", false);
        }

        if ($hasMediaCols) {
            $builder->select("
                {$tableAlias}.id_gambar_tipe as {$colPrefix}_id_gambar_tipe,
                {$tableAlias}.id_gambar_denah as {$colPrefix}_id_gambar_denah,
                {$tableAlias}.id_gambar_kerja as {$colPrefix}_id_gambar_kerja
            ");
        } else {
            $builder->select("
                NULL as {$colPrefix}_id_gambar_tipe,
                NULL as {$colPrefix}_id_gambar_denah,
                NULL as {$colPrefix}_id_gambar_kerja
            ", false);
        }
    }

    /**
     * @param int[]    $requestIds
     * @param object[] $headerRows
     * @return array<int, object[]>
     */
    private function loadJalansForRequests(array $requestIds, array $headerRows): array
    {
        $map = [];

        if ($requestIds !== [] && $this->db->tableExists('kavling_request_jalan')) {
            $rows = $this->db->table('kavling_request_jalan krj')
                ->select('krj.id_request, krj.id_jalan, j.nama_jalan, c.nama_cluster')
                ->join('jalan j', 'j.id_jalan = krj.id_jalan')
                ->join('cluster c', 'c.id_cluster = j.id_cluster', 'left')
                ->whereIn('krj.id_request', $requestIds)
                ->orderBy('krj.id', 'ASC')
                ->get()
                ->getResult();

            foreach ($rows as $row) {
                $reqId         = (int) $row->id_request;
                $map[$reqId][] = (object) [
                    'id_jalan'     => (int) $row->id_jalan,
                    'nama_jalan'   => (string) $row->nama_jalan,
                    'nama_cluster' => $row->nama_cluster ?? null,
                ];
            }
        }

        foreach ($headerRows as $header) {
            $reqId = (int) $header->id_request;
            if (! empty($map[$reqId]) || empty($header->id_jalan)) {
                continue;
            }

            $map[$reqId] = [
                (object) [
                    'id_jalan'     => (int) $header->id_jalan,
                    'nama_jalan'   => (string) ($header->nama_jalan ?? ''),
                    'nama_cluster' => $header->nama_cluster ?? null,
                ],
            ];
        }

        return $map;
    }

    /**
     * @param int[]    $requestIds
     * @param object[] $headerRows
     * @return array<int, object[]>
     */
    private function loadKavlingsForRequests(array $requestIds, array $headerRows): array
    {
        $map           = [];
        $hasMkdt       = $this->db->tableExists('mkdt');
        $hasIdKonsumen = $hasMkdt && $this->db->fieldExists('id_konsumen', 'mkdt');
        $hasKonsumen   = $hasIdKonsumen && $this->db->tableExists('konsumen');

        if ($requestIds !== [] && $this->db->tableExists('kavling_request_kavling') && $this->db->tableExists('kavling')) {
            $builder = $this->db->table('kavling_request_kavling krk')
                ->select('
                    krk.id_request,
                    krk.id_kavling,
                    krk.old_id_tipe,
                    k.no_kavling,
                    k.id_tipe as current_id_tipe,
                    k.id_mkdt,
                    j.nama_jalan,
                    old_t.no_tipe_rumah as old_no_tipe_rumah,
                    old_t.tipe_rumah as old_tipe_rumah,
                    cur_t.no_tipe_rumah as current_no_tipe_rumah,
                    cur_t.tipe_rumah as current_tipe_rumah
                ')
                ->join('kavling k', 'k.id_kavling = krk.id_kavling')
                ->join('jalan j', 'j.id_jalan = k.id_jalan', 'left')
                ->join('tipe old_t', 'old_t.id_tipe = krk.old_id_tipe', 'left')
                ->join('tipe cur_t', 'cur_t.id_tipe = k.id_tipe', 'left');

            if ($hasMkdt) {
                $builder->select('m.status_mkdt, m.is_batal')
                    ->join('mkdt m', 'm.id_mkdt = k.id_mkdt', 'left');
            } else {
                $builder->select('NULL as status_mkdt, 0 as is_batal', false);
            }

            if ($hasKonsumen) {
                $builder->select('kon.nama_konsumen')
                    ->join('konsumen kon', 'kon.id_konsumen = m.id_konsumen', 'left');
            } else {
                $builder->select('NULL as nama_konsumen', false);
            }

            $rows = $builder
                ->whereIn('krk.id_request', $requestIds)
                ->orderBy('j.nama_jalan', 'ASC')
                ->orderBy('ABS(k.no_kavling)', 'ASC')
                ->orderBy('k.no_kavling', 'ASC')
                ->get()
                ->getResult();

            foreach ($rows as $row) {
                $reqId         = (int) $row->id_request;
                $map[$reqId][] = $row;
            }
        }

        // Batch fallback untuk data legacy dengan kavling_request.id_kavling
        $fallbackKavlingIds = [];
        foreach ($headerRows as $header) {
            $reqId = (int) $header->id_request;
            if (empty($map[$reqId]) && ! empty($header->id_kavling)) {
                $fallbackKavlingIds[] = (int) $header->id_kavling;
            }
        }

        $fallbackKavlingIds = $this->normalizeIds($fallbackKavlingIds);
        if ($fallbackKavlingIds === [] || ! $this->db->tableExists('kavling')) {
            return $map;
        }

        $fbBuilder = $this->db->table('kavling k')
            ->select('
                k.id_kavling,
                NULL as old_id_tipe,
                k.no_kavling,
                k.id_tipe as current_id_tipe,
                k.id_mkdt,
                j.nama_jalan,
                NULL as old_no_tipe_rumah,
                NULL as old_tipe_rumah,
                cur_t.no_tipe_rumah as current_no_tipe_rumah,
                cur_t.tipe_rumah as current_tipe_rumah
            ', false)
            ->join('jalan j', 'j.id_jalan = k.id_jalan', 'left')
            ->join('tipe cur_t', 'cur_t.id_tipe = k.id_tipe', 'left')
            ->whereIn('k.id_kavling', $fallbackKavlingIds);

        if ($hasMkdt) {
            $fbBuilder->select('m.status_mkdt, m.is_batal')
                ->join('mkdt m', 'm.id_mkdt = k.id_mkdt', 'left');
        } else {
            $fbBuilder->select('NULL as status_mkdt, 0 as is_batal', false);
        }

        if ($hasKonsumen) {
            $fbBuilder->select('kon.nama_konsumen')
                ->join('konsumen kon', 'kon.id_konsumen = m.id_konsumen', 'left');
        } else {
            $fbBuilder->select('NULL as nama_konsumen', false);
        }

        $fbById = [];
        foreach ($fbBuilder->get()->getResult() as $fbRow) {
            $fbById[(int) $fbRow->id_kavling] = $fbRow;
        }

        foreach ($headerRows as $header) {
            $reqId = (int) $header->id_request;
            if (empty($map[$reqId]) && ! empty($header->id_kavling)) {
                $kId = (int) $header->id_kavling;
                if (isset($fbById[$kId])) {
                    $row             = clone $fbById[$kId];
                    $row->id_request = $reqId;
                    $map[$reqId]     = [$row];
                }
            }
        }

        return $map;
    }

    /**
     * @param array<int|string> $values
     * @return int[]
     */
    private function normalizeIds(array $values): array
    {
        $ids = [];
        foreach ($values as $value) {
            $id = (int) $value;
            if ($id > 0) {
                $ids[$id] = $id;
            }
        }

        return array_values($ids);
    }
}

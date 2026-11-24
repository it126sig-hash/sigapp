<?php

namespace App\Repositories\Permohonan;

use CodeIgniter\Database\BaseConnection;

class PermohonanRepository
{
    public const MODULES = [
        'bpb',
        'fee_sales',
        'kavling_request',
        'tagihan_direksi',
        'cashout_subkon',
        'mgm',
        'tiket_masalah',
    ];

    protected BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
    }

    /**
     * @return int[]
     */
    public function getUserGroupIds(int $userId): array
    {
        if ($userId <= 0) {
            return [];
        }

        $rows = $this->db->table('auth_groups_users')
            ->select('group_id')
            ->where('user_id', $userId)
            ->get()
            ->getResultArray();

        return array_values(array_unique(array_map(
            static fn (array $r): int => (int) $r['group_id'],
            $rows
        )));
    }

    /**
     * @return array<int, array{id_proyek: int, nama_proyek: string, kode_keuangan: ?string}>
     */
    public function getAccessibleProjects(int $userId, bool $isAdmin): array
    {
        $rows = $this->db->table('proyek')
            ->select('id_proyek, nama_proyek, kode_keuangan, id_users')
            ->where('deleted_at IS NULL', null, false)
            ->orderBy('order_by', 'ASC')
            ->orderBy('nama_proyek', 'ASC')
            ->get()
            ->getResultArray();

        $userIdStr = (string) $userId;
        $projects  = [];

        foreach ($rows as $row) {
            if (! $isAdmin) {
                $allowed = array_filter(array_map('trim', explode(',', (string) ($row['id_users'] ?? ''))));
                if (! in_array($userIdStr, $allowed, true)) {
                    continue;
                }
            }

            $projects[] = [
                'id_proyek'     => (int) $row['id_proyek'],
                'nama_proyek'   => (string) $row['nama_proyek'],
                'kode_keuangan' => ! empty($row['kode_keuangan']) ? (string) $row['kode_keuangan'] : null,
            ];
        }

        return $projects;
    }

    /**
     * @param int[] $userIds
     * @return array<int, array{nama: string, divisi: string}>
     */
    public function getUserSnapshots(array $userIds): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $userIds), static fn (int $id): bool => $id > 0)));
        if ($ids === []) {
            return [];
        }

        $rows = $this->db->table('users u')
            ->select('u.id, u.username, u.name, k.nama_karyawan, d.divisi')
            ->join('karyawan k', 'k.id_user = u.id', 'left')
            ->join('divisi d', 'd.id_divisi = k.id_divisi', 'left')
            ->whereIn('u.id', $ids)
            ->get()
            ->getResultArray();

        $groupRows = $this->db->table('auth_groups_users agu')
            ->select('agu.user_id, ag.name AS group_name')
            ->join('auth_groups ag', 'ag.id = agu.group_id', 'left')
            ->whereIn('agu.user_id', $ids)
            ->orderBy('agu.group_id', 'ASC')
            ->get()
            ->getResultArray();

        $groupMap = [];
        foreach ($groupRows as $gr) {
            $uid = (int) $gr['user_id'];
            if (! isset($groupMap[$uid]) && ! empty($gr['group_name'])) {
                $groupMap[$uid] = (string) $gr['group_name'];
            }
        }

        $map = [];
        foreach ($rows as $row) {
            $uid  = (int) $row['id'];
            $nama = trim((string) ($row['nama_karyawan'] ?? ''));
            if ($nama === '') {
                $nama = trim((string) ($row['name'] ?? ''));
            }
            if ($nama === '') {
                $nama = trim((string) ($row['username'] ?? ('User #' . $uid)));
            }

            $divisi = trim((string) ($row['divisi'] ?? ''));
            if ($divisi === '') {
                $divisi = $groupMap[$uid] ?? '-';
            }

            $map[$uid] = [
                'nama'   => $nama,
                'divisi' => $divisi,
            ];
        }

        return $map;
    }

    /**
     * @return array<string, array<string, int>>
     */
    public function getSummaryCounts(array $context, ?int $idProyek = null): array
    {
        $result = [];

        foreach (['assigned_to_me', 'my_requests', 'department_monitoring'] as $scope) {
            $counts = ['all' => 0];
            foreach (self::MODULES as $module) {
                $counts[$module] = 0;
            }

            $unionParts = [];
            $bindings   = [];

            foreach (self::MODULES as $module) {
                $branch = $this->buildModuleBranch($module, $scope, $context, $idProyek, null, null);
                if ($branch === null) {
                    continue;
                }
                $unionParts[] = $branch['sql'];
                foreach ($branch['bindings'] as $b) {
                    $bindings[] = $b;
                }
            }

            if ($unionParts !== []) {
                $unionSql = implode("\nUNION ALL\n", $unionParts);
                $sql      = "SELECT module, COUNT(*) AS total FROM (\n{$unionSql}\n) summary_idx GROUP BY module";
                $rows     = $this->db->query($sql, $bindings)->getResultArray();

                $sum = 0;
                foreach ($rows as $row) {
                    $mod = (string) $row['module'];
                    $cnt = (int) $row['total'];
                    if (array_key_exists($mod, $counts)) {
                        $counts[$mod] = $cnt;
                        $sum += $cnt;
                    }
                }
                $counts['all'] = $sum;
            }

            $result[$scope] = $counts;
        }

        return $result;
    }

    /**
     * @return array{total: int, rows: array<int, array{module: string, item_id: string, sort_at: string}>}
     */
    public function getPaginatedIndex(array $context, array $filters): array
    {
        $scope    = (string) ($filters['scope'] ?? 'assigned_to_me');
        $module   = (string) ($filters['module'] ?? 'all');
        $status   = isset($filters['status']) ? trim((string) $filters['status']) : null;
        $idProyek = isset($filters['id_proyek']) && (int) $filters['id_proyek'] > 0 ? (int) $filters['id_proyek'] : null;
        $search   = isset($filters['search']) ? trim((string) $filters['search']) : null;
        $page     = max(1, (int) ($filters['page'] ?? 1));
        $perPage  = min(100, max(1, (int) ($filters['per_page'] ?? 20)));
        $offset   = ($page - 1) * $perPage;

        $targetModules = ($module !== '' && $module !== 'all' && in_array($module, self::MODULES, true))
            ? [$module]
            : self::MODULES;

        $unionParts = [];
        $bindings   = [];

        foreach ($targetModules as $mod) {
            $branch = $this->buildModuleBranch($mod, $scope, $context, $idProyek, $status, $search);
            if ($branch === null) {
                continue;
            }
            $unionParts[] = $branch['sql'];
            foreach ($branch['bindings'] as $b) {
                $bindings[] = $b;
            }
        }

        if ($unionParts === []) {
            return ['total' => 0, 'rows' => []];
        }

        $unionSql = implode("\nUNION ALL\n", $unionParts);
        $countSql = "SELECT COUNT(*) AS total FROM (\n{$unionSql}\n) perm_idx";
        $totalRow = $this->db->query($countSql, $bindings)->getRowArray();
        $total    = (int) ($totalRow['total'] ?? 0);

        if ($total === 0) {
            return ['total' => 0, 'rows' => []];
        }

        $listSql       = "SELECT module, item_id, sort_at FROM (\n{$unionSql}\n) perm_idx ORDER BY sort_at DESC, item_id DESC LIMIT ? OFFSET ?";
        $listBindings  = array_merge($bindings, [$perPage, $offset]);
        $rows          = $this->db->query($listSql, $listBindings)->getResultArray();

        return [
            'total' => $total,
            'rows'  => $rows,
        ];
    }

    /**
     * @param array<int, array{module: string, item_id: string, sort_at: string}> $indexRows
     * @return array<string, array<string, mixed>>
     */
    public function hydrateRows(array $indexRows, int $userId): array
    {
        $idsByModule = [];
        foreach ($indexRows as $idx) {
            $mod = (string) $idx['module'];
            $id  = (string) $idx['item_id'];
            $idsByModule[$mod][] = $id;
        }

        $hydrated = [];

        if (! empty($idsByModule['bpb'])) {
            foreach ($this->hydrateBpb($idsByModule['bpb']) as $id => $row) {
                $hydrated['bpb:' . $id] = $row;
            }
        }
        if (! empty($idsByModule['fee_sales'])) {
            foreach ($this->hydrateFeeSales($idsByModule['fee_sales']) as $id => $row) {
                $hydrated['fee_sales:' . $id] = $row;
            }
        }
        if (! empty($idsByModule['kavling_request'])) {
            foreach ($this->hydrateKavlingRequest($idsByModule['kavling_request']) as $id => $row) {
                $hydrated['kavling_request:' . $id] = $row;
            }
        }
        if (! empty($idsByModule['tagihan_direksi'])) {
            foreach ($this->hydrateTagihanDireksi($idsByModule['tagihan_direksi']) as $id => $row) {
                $hydrated['tagihan_direksi:' . $id] = $row;
            }
        }
        if (! empty($idsByModule['cashout_subkon'])) {
            foreach ($this->hydrateCashoutSubkon($idsByModule['cashout_subkon']) as $id => $row) {
                $hydrated['cashout_subkon:' . $id] = $row;
            }
        }
        if (! empty($idsByModule['mgm'])) {
            foreach ($this->hydrateMgm($idsByModule['mgm']) as $id => $row) {
                $hydrated['mgm:' . $id] = $row;
            }
        }
        if (! empty($idsByModule['tiket_masalah'])) {
            foreach ($this->hydrateTiketMasalah($idsByModule['tiket_masalah'], $userId) as $id => $row) {
                $hydrated['tiket_masalah:' . $id] = $row;
            }
        }

        return $hydrated;
    }

    /**
     * @return array{sql: string, bindings: array<int, mixed>}|null
     */
    private function buildModuleBranch(
        string $module,
        string $scope,
        array $context,
        ?int $idProyek,
        ?string $status,
        ?string $search
    ): ?array {
        return match ($module) {
            'bpb'             => $this->branchBpb($scope, $context, $idProyek, $status, $search),
            'fee_sales'       => $this->branchFeeSales($scope, $context, $idProyek, $status, $search),
            'kavling_request' => $this->branchKavlingRequest($scope, $context, $idProyek, $status, $search),
            'tagihan_direksi' => $this->branchTagihanDireksi($scope, $context, $idProyek, $status, $search),
            'cashout_subkon'  => $this->branchCashoutSubkon($scope, $context, $idProyek, $status, $search),
            'mgm'             => $this->branchMgm($scope, $context, $idProyek, $status, $search),
            'tiket_masalah'   => $this->branchTiketMasalah($scope, $context, $idProyek, $status, $search),
            default           => null,
        };
    }

    /**
     * @return array{sql: string, bindings: array<int, mixed>}|null
     */
    private function branchBpb(string $scope, array $context, ?int $idProyek, ?string $status, ?string $search): ?array
    {
        // BPB tidak memiliki relasi id_proyek; bila filter proyek spesifik aktif, lewati
        if ($idProyek !== null && $idProyek > 0) {
            return null;
        }

        $userId = (int) $context['user_id'];
        $where  = [];
        $binds  = [];

        if ($scope === 'assigned_to_me') {
            $assignedParts = [
                "(b.status = 'waiting_cc' AND b.cc_user_id = ?)",
                "(b.status = 'waiting_approver' AND b.approver_user_id = ?)",
            ];
            $binds[] = $userId;
            $binds[] = $userId;

            if (! empty($context['is_umum'])) {
                $assignedParts[] = "b.status IN ('approved', 'processed', 'disbursed', 'pending')";
            }

            $where[] = '(' . implode(' OR ', $assignedParts) . ')';
        } elseif ($scope === 'my_requests') {
            $where[] = 'b.applicant_user_id = ?';
            $binds[] = $userId;
        } else {
            // department_monitoring
            if (empty($context['is_admin']) && empty($context['is_direksi']) && empty($context['is_umum'])) {
                $where[] = '(b.applicant_user_id = ? OR b.cc_user_id = ? OR b.approver_user_id = ?)';
                $binds[] = $userId;
                $binds[] = $userId;
                $binds[] = $userId;
            }
            $where[] = "(b.status != 'draft' OR b.applicant_user_id = ?)";
            $binds[] = $userId;
        }

        $statusCond = $this->statusCondition('bpb', 'b.status', $status);
        if ($statusCond === false) {
            return null;
        }
        if ($statusCond !== null) {
            $where[] = $statusCond['sql'];
            array_push($binds, ...$statusCond['bindings']);
        }

        if ($search !== null && $search !== '') {
            $like    = '%' . $search . '%';
            $where[] = "(b.nomor LIKE ? OR b.applicant_name LIKE ? OR b.applicant_department LIKE ? OR EXISTS (SELECT 1 FROM bpb_items si WHERE si.bpb_id = b.id AND si.nama_barang LIKE ?))";
            array_push($binds, $like, $like, $like, $like);
        }

        $whereSql = $where !== [] ? 'WHERE ' . implode(' AND ', $where) : '';
        $sql      = "SELECT CAST('bpb' AS CHAR(32)) COLLATE utf8mb4_general_ci AS module,
                            CAST(b.id AS CHAR(255)) COLLATE utf8mb4_general_ci AS item_id,
                            CAST(COALESCE(b.submitted_at, b.created_at, '1970-01-01 00:00:00') AS DATETIME) AS sort_at
                     FROM bpb_requests b
                     {$whereSql}";

        return ['sql' => $sql, 'bindings' => $binds];
    }

    /**
     * @return array{sql: string, bindings: array<int, mixed>}|null
     */
    private function branchFeeSales(string $scope, array $context, ?int $idProyek, ?string $status, ?string $search): ?array
    {
        $userId        = (int) $context['user_id'];
        $isAdmin       = ! empty($context['is_admin']);
        $canSignMkdt   = ! empty($context['can_sign_mkdt']);
        $canSignKeu    = ! empty($context['can_sign_keuangan']);
        $canCairkan    = ! empty($context['can_cairkan_fee_sales']);
        $canView       = ! empty($context['can_view_fee_sales']);
        $accessibleIds = $context['accessible_project_ids'] ?? [];

        $where = [];
        $binds = [];

        if ($scope === 'assigned_to_me') {
            $roleParts = [];
            if ($canSignMkdt) {
                $roleParts[] = 'fsp.ttd_mkdt_by IS NULL';
            }
            if ($canSignKeu) {
                $roleParts[] = 'fsp.ttd_keuangan_by IS NULL';
            }
            if ($canCairkan) {
                $roleParts[] = '(fsp.ttd_mkdt_by IS NOT NULL AND fsp.ttd_keuangan_by IS NOT NULL)';
            }

            if ($roleParts === []) {
                return null;
            }

            $where[] = "fsp.status = 'diajukan'";
            $where[] = '(' . implode(' OR ', $roleParts) . ')';
        } elseif ($scope === 'my_requests') {
            $where[] = 'fsp.created_by = ?';
            $binds[] = $userId;
        } else {
            if (
                ! $isAdmin
                && empty($context['is_direksi'])
                && empty($context['is_mkdt'])
                && empty($context['is_keuangan'])
                && empty($context['is_promosi'])
                && ! $canView
                && ! $canSignMkdt
                && ! $canSignKeu
                && ! $canCairkan
            ) {
                return null;
            }
        }

        if ($scope !== 'my_requests' && ! $isAdmin) {
            if ($accessibleIds === []) {
                return null;
            }
            $placeholders = implode(',', array_fill(0, count($accessibleIds), '?'));
            $where[]      = "fsp.id_proyek IN ({$placeholders})";
            array_push($binds, ...$accessibleIds);
        }

        if ($idProyek !== null && $idProyek > 0) {
            $where[] = 'fsp.id_proyek = ?';
            $binds[] = $idProyek;
        }

        $statusCond = $this->statusCondition('fee_sales', 'fsp.status', $status);
        if ($statusCond === false) {
            return null;
        }
        if ($statusCond !== null) {
            $where[] = $statusCond['sql'];
            array_push($binds, ...$statusCond['bindings']);
        }

        $joins = '';
        if ($search !== null && $search !== '') {
            $joins   = 'LEFT JOIN proyek p ON p.id_proyek = fsp.id_proyek';
            $like    = '%' . $search . '%';
            $where[] = "(fsp.nomor_pengajuan LIKE ? OR fsp.catatan LIKE ? OR fsp.ttd_pembuat_nama LIKE ? OR p.nama_proyek LIKE ? OR EXISTS (SELECT 1 FROM fee_sales_pengajuan_item pis JOIN fee_sales fss ON fss.id = pis.id_fee_sales WHERE pis.id_pengajuan = fsp.id AND fss.sales_nama LIKE ?))";
            array_push($binds, $like, $like, $like, $like, $like);
        }

        $whereSql = $where !== [] ? 'WHERE ' . implode(' AND ', $where) : '';
        $sql      = "SELECT CAST('fee_sales' AS CHAR(32)) COLLATE utf8mb4_general_ci AS module,
                            CAST(fsp.id AS CHAR(255)) COLLATE utf8mb4_general_ci AS item_id,
                            CAST(COALESCE(fsp.created_at, CONCAT(fsp.tanggal_pengajuan, ' 00:00:00'), '1970-01-01 00:00:00') AS DATETIME) AS sort_at
                     FROM fee_sales_pengajuan fsp
                     {$joins}
                     {$whereSql}";

        return ['sql' => $sql, 'bindings' => $binds];
    }

    /**
     * @return array{sql: string, bindings: array<int, mixed>}|null
     */
    private function branchKavlingRequest(string $scope, array $context, ?int $idProyek, ?string $status, ?string $search): ?array
    {
        $userId        = (int) $context['user_id'];
        $isAdmin       = ! empty($context['is_admin']);
        $isPlanning    = ! empty($context['is_planning']);
        $accessibleIds = $context['accessible_project_ids'] ?? [];

        $where = [];
        $binds = [];

        if ($scope === 'assigned_to_me') {
            if (! $isAdmin && ! $isPlanning) {
                return null;
            }
            $where[] = "kr.status = 'pending'";
        } elseif ($scope === 'my_requests') {
            $where[] = 'kr.created_by = ?';
            $binds[] = $userId;
        } else {
            if (
                ! $isAdmin
                && empty($context['is_direksi'])
                && ! $isPlanning
                && empty($context['is_mkdt'])
                && empty($context['is_produksi'])
                && empty($context['is_legal'])
            ) {
                return null;
            }
        }

        if ($scope !== 'my_requests' && ! $isAdmin) {
            if ($accessibleIds === []) {
                return null;
            }
            $placeholders = implode(',', array_fill(0, count($accessibleIds), '?'));
            $where[]      = "kr.id_proyek IN ({$placeholders})";
            array_push($binds, ...$accessibleIds);
        }

        if ($idProyek !== null && $idProyek > 0) {
            $where[] = 'kr.id_proyek = ?';
            $binds[] = $idProyek;
        }

        $statusCond = $this->statusCondition('kavling_request', 'kr.status', $status);
        if ($statusCond === false) {
            return null;
        }
        if ($statusCond !== null) {
            $where[] = $statusCond['sql'];
            array_push($binds, ...$statusCond['bindings']);
        }

        $joins = '';
        if ($search !== null && $search !== '') {
            $joins   = 'LEFT JOIN proyek p ON p.id_proyek = kr.id_proyek';
            $like    = '%' . $search . '%';
            $where[] = "(kr.keterangan LIKE ? OR kr.tipe_manual LIKE ? OR p.nama_proyek LIKE ? OR EXISTS (SELECT 1 FROM kavling kv JOIN jalan jl ON jl.id_jalan = kv.id_jalan WHERE kv.id_kavling = kr.id_kavling AND (kv.no_kavling LIKE ? OR jl.nama_jalan LIKE ?)) OR EXISTS (SELECT 1 FROM kavling_request_kavling krks JOIN kavling kvs ON kvs.id_kavling = krks.id_kavling JOIN jalan jls ON jls.id_jalan = kvs.id_jalan WHERE krks.id_request = kr.id_request AND (kvs.no_kavling LIKE ? OR jls.nama_jalan LIKE ?)))";
            array_push($binds, $like, $like, $like, $like, $like, $like, $like);
        }

        $whereSql = $where !== [] ? 'WHERE ' . implode(' AND ', $where) : '';
        $sql      = "SELECT CAST('kavling_request' AS CHAR(32)) COLLATE utf8mb4_general_ci AS module,
                            CAST(kr.id_request AS CHAR(255)) COLLATE utf8mb4_general_ci AS item_id,
                            CAST(COALESCE(kr.created_at, '1970-01-01 00:00:00') AS DATETIME) AS sort_at
                     FROM kavling_request kr
                     {$joins}
                     {$whereSql}";

        return ['sql' => $sql, 'bindings' => $binds];
    }

    /**
     * @return array{sql: string, bindings: array<int, mixed>}|null
     */
    private function branchTagihanDireksi(string $scope, array $context, ?int $idProyek, ?string $status, ?string $search): ?array
    {
        $userId        = (int) $context['user_id'];
        $isAdmin       = ! empty($context['is_admin']);
        $isDireksi     = ! empty($context['is_direksi']);
        $accessibleIds = $context['accessible_project_ids'] ?? [];

        $where = ["il.nomor_surat IS NOT NULL", "il.nomor_surat != ''", "il.no_inv != ''"];
        $binds = [];

        if ($scope === 'assigned_to_me') {
            if (! $isAdmin && ! $isDireksi) {
                return null;
            }
            $where[] = 'il.is_signed_direktur = 0';
        } elseif ($scope === 'my_requests') {
            $where[] = 'il.add_by = ?';
            $binds[] = (string) $userId;
        } else {
            if (! $isAdmin && ! $isDireksi && empty($context['is_keuangan'])) {
                return null;
            }
        }

        if ($scope !== 'my_requests' && ! $isAdmin) {
            if ($accessibleIds === []) {
                return null;
            }
            $placeholders = implode(',', array_fill(0, count($accessibleIds), '?'));
            $where[]      = "cl.id_proyek IN ({$placeholders})";
            array_push($binds, ...$accessibleIds);
        }

        if ($idProyek !== null && $idProyek > 0) {
            $where[] = 'cl.id_proyek = ?';
            $binds[] = $idProyek;
        }

        $statusCond = $this->statusCondition('tagihan_direksi', 'il.is_signed_direktur', $status);
        if ($statusCond === false) {
            return null;
        }
        if ($statusCond !== null) {
            $where[] = $statusCond['sql'];
            array_push($binds, ...$statusCond['bindings']);
        }

        $joins = "LEFT JOIN kavling k ON k.id_kavling = il.id_kavling
                  LEFT JOIN jalan j ON j.id_jalan = k.id_jalan
                  LEFT JOIN cluster cl ON cl.id_cluster = j.id_cluster";

        if ($search !== null && $search !== '') {
            $joins   .= "\nLEFT JOIN konsumen kon ON kon.id_konsumen = il.id_konsumen\nLEFT JOIN proyek p ON p.id_proyek = cl.id_proyek";
            $like    = '%' . $search . '%';
            $where[] = "(il.no_inv LIKE ? OR il.nomor_surat LIKE ? OR kon.nama_konsumen LIKE ? OR p.nama_proyek LIKE ? OR j.nama_jalan LIKE ? OR k.no_kavling LIKE ?)";
            array_push($binds, $like, $like, $like, $like, $like, $like);
        }

        $whereSql = 'WHERE ' . implode(' AND ', $where);
        $sql      = "SELECT CAST('tagihan_direksi' AS CHAR(32)) COLLATE utf8mb4_general_ci AS module,
                            CAST(il.no_inv AS CHAR(255)) COLLATE utf8mb4_general_ci AS item_id,
                            CAST(COALESCE(il.date_add, '1970-01-01 00:00:00') AS DATETIME) AS sort_at
                     FROM invoice_log il
                     {$joins}
                     {$whereSql}";

        return ['sql' => $sql, 'bindings' => $binds];
    }

    /**
     * @return array{sql: string, bindings: array<int, mixed>}|null
     */
    private function branchCashoutSubkon(string $scope, array $context, ?int $idProyek, ?string $status, ?string $search): ?array
    {
        $userId        = (int) $context['user_id'];
        $isAdmin       = ! empty($context['is_admin']);
        $isKeuangan    = ! empty($context['is_keuangan']);
        $isProduksi    = ! empty($context['is_produksi']);
        $accessibleIds = $context['accessible_project_ids'] ?? [];

        $where = [];
        $binds = [];

        if ($scope === 'assigned_to_me') {
            if ($isAdmin) {
                $where[] = 'csd.status IN (0, 1, 2, 3)';
            } elseif ($isKeuangan && $isProduksi) {
                $where[] = 'csd.status IN (0, 1, 2, 3)';
            } elseif ($isKeuangan) {
                $where[] = 'csd.status IN (0, 2, 3)';
            } elseif ($isProduksi) {
                $where[] = 'csd.status = 1';
            } else {
                return null;
            }
            $where[] = '(csd.is_paid IS NULL OR csd.is_paid = 0)';
        } elseif ($scope === 'my_requests') {
            $where[] = '(cs.add_by = ? OR csd.spp_add_by = ?)';
            $binds[] = $userId;
            $binds[] = $userId;
        } else {
            if (! $isAdmin && empty($context['is_direksi']) && ! $isKeuangan && ! $isProduksi) {
                return null;
            }
        }

        if ($scope !== 'my_requests' && ! $isAdmin) {
            if ($accessibleIds === []) {
                return null;
            }
            $placeholders = implode(',', array_fill(0, count($accessibleIds), '?'));
            $where[]      = "EXISTS (SELECT 1 FROM cashout_subkon_kavling cskp JOIN kavling kp ON kp.id_kavling = cskp.id_kavling JOIN jalan jp ON jp.id_jalan = kp.id_jalan JOIN cluster clp ON clp.id_cluster = jp.id_cluster WHERE cskp.id_cashout_subkon = cs.id_cashout_subkon AND clp.id_proyek IN ({$placeholders}))";
            array_push($binds, ...$accessibleIds);
        }

        if ($idProyek !== null && $idProyek > 0) {
            $where[] = 'EXISTS (SELECT 1 FROM cashout_subkon_kavling cskf JOIN kavling kf ON kf.id_kavling = cskf.id_kavling JOIN jalan jf ON jf.id_jalan = kf.id_jalan JOIN cluster clf ON clf.id_cluster = jf.id_cluster WHERE cskf.id_cashout_subkon = cs.id_cashout_subkon AND clf.id_proyek = ?)';
            $binds[] = $idProyek;
        }

        $statusCond = $this->statusCondition('cashout_subkon', 'csd.status', $status);
        if ($statusCond === false) {
            return null;
        }
        if ($statusCond !== null) {
            $where[] = $statusCond['sql'];
            array_push($binds, ...$statusCond['bindings']);
        }

        $joins = '';
        if ($search !== null && $search !== '') {
            $joins   = 'LEFT JOIN subkon s ON s.id = cs.id_subkon';
            $like    = '%' . $search . '%';
            $where[] = "(cs.nomor_surat LIKE ? OR s.nama_subkon LIKE ? OR csd.berita_acara LIKE ? OR csd.spp_no LIKE ? OR EXISTS (SELECT 1 FROM cashout_subkon_kavling csks JOIN kavling ks ON ks.id_kavling = csks.id_kavling JOIN jalan js ON js.id_jalan = ks.id_jalan JOIN cluster cls ON cls.id_cluster = js.id_cluster JOIN proyek ps ON ps.id_proyek = cls.id_proyek WHERE csks.id_cashout_subkon = cs.id_cashout_subkon AND (ks.no_kavling LIKE ? OR js.nama_jalan LIKE ? OR ps.nama_proyek LIKE ?)))";
            array_push($binds, $like, $like, $like, $like, $like, $like, $like);
        }

        $whereSql = $where !== [] ? 'WHERE ' . implode(' AND ', $where) : '';
        $sql      = "SELECT CAST('cashout_subkon' AS CHAR(32)) COLLATE utf8mb4_general_ci AS module,
                            CAST(csd.id_cashout_subkon_detail AS CHAR(255)) COLLATE utf8mb4_general_ci AS item_id,
                            CAST(COALESCE(csd.pengajuan_cair_created_at, csd.spp_created_at, cs.created_at, '1970-01-01 00:00:00') AS DATETIME) AS sort_at
                     FROM cashout_subkon_detail csd
                     JOIN cashout_subkon cs ON cs.id_cashout_subkon = csd.id_cashout_subkon
                     {$joins}
                     {$whereSql}";

        return ['sql' => $sql, 'bindings' => $binds];
    }

    /**
     * @return array{sql: string, bindings: array<int, mixed>}|null
     */
    private function branchMgm(string $scope, array $context, ?int $idProyek, ?string $status, ?string $search): ?array
    {
        $userId        = (int) $context['user_id'];
        $isAdmin       = ! empty($context['is_admin']);
        $isPromosi     = ! empty($context['is_promosi']);
        $isKeuangan    = ! empty($context['is_keuangan']);
        $accessibleIds = $context['accessible_project_ids'] ?? [];

        $where = ["COALESCE(r.status, 'active') = 'active'"];
        $binds = [];

        if ($scope === 'assigned_to_me') {
            $parts = [];
            if ($isAdmin || $isPromosi) {
                $parts[] = "rb.status IN ('eligible', 'dikonfirmasi')";
                $parts[] = "(rb.status = 'dibayar_promosi' AND rb.cair_keuangan_at IS NULL)";
                $parts[] = "(rb.status = 'cair' AND COALESCE(rb.paid_by_promosi, 0) = 0)";
            }
            if ($isAdmin || $isKeuangan) {
                $parts[] = "rb.status = 'diajukan_keuangan'";
            }
            if ($parts === []) {
                return null;
            }
            $where[] = '(' . implode(' OR ', $parts) . ')';
        } elseif ($scope === 'my_requests') {
            $where[] = '(rb.submitted_keuangan_by = ? OR rb.confirmed_by = ? OR rb.add_by = ?)';
            $binds[] = $userId;
            $binds[] = $userId;
            $binds[] = $userId;
        } else {
            if (! $isAdmin && empty($context['is_direksi']) && ! $isPromosi && ! $isKeuangan) {
                return null;
            }
        }

        if ($scope !== 'my_requests' && ! $isAdmin) {
            if ($accessibleIds === []) {
                return null;
            }
            $placeholders = implode(',', array_fill(0, count($accessibleIds), '?'));
            $where[]      = "r.id_proyek IN ({$placeholders})";
            array_push($binds, ...$accessibleIds);
        }

        if ($idProyek !== null && $idProyek > 0) {
            $where[] = 'r.id_proyek = ?';
            $binds[] = $idProyek;
        }

        $statusCond = $this->statusCondition('mgm', 'rb.status', $status);
        if ($statusCond === false) {
            return null;
        }
        if ($statusCond !== null) {
            $where[] = $statusCond['sql'];
            array_push($binds, ...$statusCond['bindings']);
        }

        $joins = '';
        if ($search !== null && $search !== '') {
            $joins = "LEFT JOIN referral_bonus_stages st ON st.id = rb.id_stage
                      LEFT JOIN proyek p ON p.id_proyek = r.id_proyek
                      LEFT JOIN konsumen k_ref ON k_ref.id_konsumen = r.id_konsumen_referrer
                      LEFT JOIN mkdt mk ON mk.id_mkdt = r.id_mkdt_referred
                      LEFT JOIN konsumen k_new ON k_new.id_konsumen = mk.id_konsumen";
            $like    = '%' . $search . '%';
            $where[] = "(st.nama_tahapan LIKE ? OR k_ref.nama_konsumen LIKE ? OR k_new.nama_konsumen LIKE ? OR p.nama_proyek LIKE ? OR rb.keterangan LIKE ?)";
            array_push($binds, $like, $like, $like, $like, $like);
        }

        $whereSql = 'WHERE ' . implode(' AND ', $where);
        $sql      = "SELECT CAST('mgm' AS CHAR(32)) COLLATE utf8mb4_general_ci AS module,
                            CAST(rb.id AS CHAR(255)) COLLATE utf8mb4_general_ci AS item_id,
                            CAST(COALESCE(rb.submitted_keuangan_at, rb.paid_promosi_at, rb.confirmed_at, rb.eligible_at, rb.created_at, '1970-01-01 00:00:00') AS DATETIME) AS sort_at
                     FROM referral_bonuses rb
                     JOIN referrals r ON r.id = rb.id_referral
                     {$joins}
                     {$whereSql}";

        return ['sql' => $sql, 'bindings' => $binds];
    }

    /**
     * @return array{sql: string, bindings: array<int, mixed>}|null
     */
    private function branchTiketMasalah(string $scope, array $context, ?int $idProyek, ?string $status, ?string $search): ?array
    {
        $userId        = (int) $context['user_id'];
        $isAdmin       = ! empty($context['is_admin']);
        $accessibleIds = $context['accessible_project_ids'] ?? [];

        $where = [];
        $binds = [];

        if ($scope === 'assigned_to_me') {
            $where[] = "tm.status IN ('dibuat', 'dalam_proses')";
            $where[] = '(tm.pic_user_id = ? OR EXISTS (SELECT 1 FROM tiket_masalah_user tmu WHERE tmu.id_tiket_masalah = tm.id AND tmu.user_id = ?))';
            $binds[] = $userId;
            $binds[] = $userId;
        } elseif ($scope === 'my_requests') {
            $where[] = 'tm.pic_user_id = ?';
            $binds[] = $userId;
        } else {
            $where[] = "(tm.status != 'draft' OR tm.pic_user_id = ?)";
            $binds[] = $userId;
        }

        if ($scope !== 'my_requests' && ! $isAdmin) {
            if ($accessibleIds === []) {
                if ($scope === 'department_monitoring') {
                    return null;
                }
            } else {
                $placeholders = implode(',', array_fill(0, count($accessibleIds), '?'));
                $where[]      = "tm.id_proyek IN ({$placeholders})";
                array_push($binds, ...$accessibleIds);
            }
        }

        if ($idProyek !== null && $idProyek > 0) {
            $where[] = 'tm.id_proyek = ?';
            $binds[] = $idProyek;
        }

        $statusCond = $this->statusCondition('tiket_masalah', 'tm.status', $status);
        if ($statusCond === false) {
            return null;
        }
        if ($statusCond !== null) {
            $where[] = $statusCond['sql'];
            array_push($binds, ...$statusCond['bindings']);
        }

        $joins = '';
        if ($search !== null && $search !== '') {
            $joins = "LEFT JOIN proyek p ON p.id_proyek = tm.id_proyek
                      LEFT JOIN kavling k ON k.id_kavling = tm.ref_id AND tm.ref_type = 'kavling'
                      LEFT JOIN jalan jk ON jk.id_jalan = k.id_jalan
                      LEFT JOIN others o ON o.id = tm.ref_id AND tm.ref_type = 'others'
                      LEFT JOIN jalan jo ON jo.id_jalan = o.id_jalan";
            $like    = '%' . $search . '%';
            $where[] = "(tm.keterangan LIKE ? OR tm.prioritas LIKE ? OR p.nama_proyek LIKE ? OR k.no_kavling LIKE ? OR jk.nama_jalan LIKE ? OR o.nama LIKE ? OR jo.nama_jalan LIKE ?)";
            array_push($binds, $like, $like, $like, $like, $like, $like, $like);
        }

        $whereSql = $where !== [] ? 'WHERE ' . implode(' AND ', $where) : '';
        $sql      = "SELECT CAST('tiket_masalah' AS CHAR(32)) COLLATE utf8mb4_general_ci AS module,
                            CAST(tm.id AS CHAR(255)) COLLATE utf8mb4_general_ci AS item_id,
                            CAST(COALESCE(tm.created_at, '1970-01-01 00:00:00') AS DATETIME) AS sort_at
                     FROM tiket_masalah tm
                     {$joins}
                     {$whereSql}";

        return ['sql' => $sql, 'bindings' => $binds];
    }

    /**
     * @return array{sql: string, bindings: array<int, mixed>}|null|false
     *         false artinya kombinasi modul+status mustahil menghasilkan baris.
     */
    private function statusCondition(string $module, string $column, ?string $status): array|null|false
    {
        if ($status === null || $status === '' || $status === 'all') {
            return null;
        }

        $normalized = strtolower(trim($status));

        if ($normalized === 'active') {
            return match ($module) {
                'bpb'             => ['sql' => "{$column} IN ('draft','waiting_cc','waiting_approver','approved','processed','disbursed','pending')", 'bindings' => []],
                'fee_sales'       => ['sql' => "{$column} = 'diajukan'", 'bindings' => []],
                'kavling_request' => ['sql' => "{$column} = 'pending'", 'bindings' => []],
                'tagihan_direksi' => ['sql' => "{$column} = 0", 'bindings' => []],
                'cashout_subkon'  => ['sql' => "{$column} IN (0, 1, 2, 3)", 'bindings' => []],
                'mgm'             => ['sql' => "{$column} IN ('eligible','dikonfirmasi','diajukan_keuangan','dibayar_promosi','cair')", 'bindings' => []],
                'tiket_masalah'   => ['sql' => "{$column} IN ('draft','dibuat','dalam_proses')", 'bindings' => []],
                default           => null,
            };
        }

        if ($normalized === 'pending') {
            return match ($module) {
                'bpb'             => ['sql' => "{$column} IN ('waiting_cc','waiting_approver','pending')", 'bindings' => []],
                'fee_sales'       => ['sql' => "{$column} = 'diajukan'", 'bindings' => []],
                'kavling_request' => ['sql' => "{$column} = 'pending'", 'bindings' => []],
                'tagihan_direksi' => ['sql' => "{$column} = 0", 'bindings' => []],
                'cashout_subkon'  => ['sql' => "{$column} IN (0, 1, 2, 3)", 'bindings' => []],
                'mgm'             => ['sql' => "{$column} IN ('eligible','dikonfirmasi','diajukan_keuangan','dibayar_promosi','cair')", 'bindings' => []],
                'tiket_masalah'   => ['sql' => "{$column} IN ('dibuat','dalam_proses')", 'bindings' => []],
                default           => null,
            };
        }

        if ($normalized === 'in_progress') {
            return match ($module) {
                'bpb'             => ['sql' => "{$column} IN ('approved','processed','disbursed','pending')", 'bindings' => []],
                'fee_sales'       => ['sql' => "{$column} = 'diajukan' AND (fsp.ttd_mkdt_by IS NOT NULL OR fsp.ttd_keuangan_by IS NOT NULL)", 'bindings' => []],
                'kavling_request' => false,
                'tagihan_direksi' => false,
                'cashout_subkon'  => ['sql' => "{$column} IN (1, 2, 3)", 'bindings' => []],
                'mgm'             => ['sql' => "{$column} IN ('dikonfirmasi','diajukan_keuangan','dibayar_promosi','cair')", 'bindings' => []],
                'tiket_masalah'   => ['sql' => "{$column} = 'dalam_proses'", 'bindings' => []],
                default           => null,
            };
        }

        if ($normalized === 'completed') {
            return match ($module) {
                'bpb'             => ['sql' => "{$column} = 'purchased'", 'bindings' => []],
                'fee_sales'       => ['sql' => "{$column} = 'selesai'", 'bindings' => []],
                'kavling_request' => ['sql' => "{$column} = 'approved'", 'bindings' => []],
                'tagihan_direksi' => ['sql' => "{$column} = 1", 'bindings' => []],
                'cashout_subkon'  => ['sql' => "({$column} = 4 OR csd.is_paid = 1)", 'bindings' => []],
                'mgm'             => ['sql' => "{$column} = 'selesai'", 'bindings' => []],
                'tiket_masalah'   => ['sql' => "{$column} = 'selesai'", 'bindings' => []],
                default           => null,
            };
        }

        if ($normalized === 'rejected') {
            return match ($module) {
                'bpb'             => ['sql' => "{$column} IN ('rejected','cancelled')", 'bindings' => []],
                'fee_sales'       => ['sql' => "{$column} IN ('ditolak','void')", 'bindings' => []],
                'kavling_request' => ['sql' => "{$column} IN ('rejected','cancelled')", 'bindings' => []],
                'tagihan_direksi' => ['sql' => "{$column} = 2", 'bindings' => []],
                'cashout_subkon'  => false,
                'mgm'             => ['sql' => "{$column} = 'batal'", 'bindings' => []],
                'tiket_masalah'   => ['sql' => "{$column} = 'batal'", 'bindings' => []],
                default           => null,
            };
        }

        // Exact raw status fallback
        if ($module === 'tagihan_direksi' || $module === 'cashout_subkon') {
            if (! is_numeric($normalized)) {
                return false;
            }
            return ['sql' => "{$column} = ?", 'bindings' => [(int) $normalized]];
        }

        return ['sql' => "{$column} = ?", 'bindings' => [$normalized]];
    }

    /**
     * @param string[] $ids
     * @return array<string, array<string, mixed>>
     */
    private function hydrateBpb(array $ids): array
    {
        $intIds = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if ($intIds === []) {
            return [];
        }

        $rows = $this->db->table('bpb_requests b')
            ->select("b.id, b.nomor, b.status, b.applicant_user_id, b.applicant_name, b.applicant_department, b.cc_user_id, b.approver_user_id, b.share_token, b.actual_amount, b.submitted_at, b.created_at,
                      (SELECT i.nama_barang FROM bpb_items i WHERE i.bpb_id = b.id ORDER BY i.item_order ASC, i.id ASC LIMIT 1) AS first_item,
                      (SELECT COUNT(*) FROM bpb_items ic WHERE ic.bpb_id = b.id) AS item_count", false)
            ->whereIn('b.id', $intIds)
            ->get()
            ->getResultArray();

        $map = [];
        foreach ($rows as $row) {
            $map[(string) $row['id']] = $row;
        }
        return $map;
    }

    /**
     * @param string[] $ids
     * @return array<string, array<string, mixed>>
     */
    private function hydrateFeeSales(array $ids): array
    {
        $intIds = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if ($intIds === []) {
            return [];
        }

        $rows = $this->db->table('fee_sales_pengajuan fsp')
            ->select("fsp.id, fsp.id_proyek, fsp.share_token, fsp.nomor_pengajuan, fsp.tanggal_pengajuan, fsp.tanggal_pengajuan_sales, fsp.catatan, fsp.status, fsp.created_by, fsp.ttd_pembuat_nama, fsp.ttd_mkdt_by, fsp.ttd_keuangan_by, fsp.created_at,
                      p.nama_proyek,
                      COALESCE(stat.total_unit, 0) AS total_unit,
                      COALESCE(stat.total_item, 0) AS total_item,
                      COALESCE(stat.total_nominal, 0) AS total_nominal,
                      stat.sales_names,
                      stat.first_id_kavling", false)
            ->join('proyek p', 'p.id_proyek = fsp.id_proyek', 'left')
            ->join("(SELECT pi.id_pengajuan,
                            COUNT(DISTINCT fs.id_mkdt) AS total_unit,
                            COUNT(pi.id) AS total_item,
                            COALESCE(SUM(pi.nominal), 0) AS total_nominal,
                            GROUP_CONCAT(DISTINCT COALESCE(fs.sales_nama, 'Sales') SEPARATOR ', ') AS sales_names,
                            MIN(m.id_kavling) AS first_id_kavling
                     FROM fee_sales_pengajuan_item pi
                     JOIN fee_sales fs ON fs.id = pi.id_fee_sales
                     LEFT JOIN mkdt m ON m.id_mkdt = fs.id_mkdt
                     GROUP BY pi.id_pengajuan) stat", 'stat.id_pengajuan = fsp.id', 'left', false)
            ->whereIn('fsp.id', $intIds)
            ->get()
            ->getResultArray();

        $map = [];
        foreach ($rows as $row) {
            $map[(string) $row['id']] = $row;
        }
        return $map;
    }

    /**
     * @param string[] $ids
     * @return array<string, array<string, mixed>>
     */
    private function hydrateKavlingRequest(array $ids): array
    {
        $intIds = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if ($intIds === []) {
            return [];
        }

        $rows = $this->db->table('kavling_request kr')
            ->select("kr.id_request, kr.id_proyek, kr.jenis_request, kr.id_kavling, kr.id_cluster, kr.id_jalan, kr.id_tipe, kr.tipe_manual, kr.keterangan, kr.status, kr.created_by, kr.created_at,
                      p.nama_proyek, c.nama_cluster, j.nama_jalan, k.no_kavling, kj.nama_jalan AS kavling_nama_jalan,
                      t.no_tipe_rumah, t.tipe_rumah,
                      (SELECT COUNT(*) FROM kavling_request_kavling krk WHERE krk.id_request = kr.id_request) AS multi_kavling_count,
                      (SELECT MIN(krk_min.id_kavling) FROM kavling_request_kavling krk_min WHERE krk_min.id_request = kr.id_request) AS first_multi_id_kavling,
                      (SELECT GROUP_CONCAT(CONCAT(jk2.nama_jalan, ' No. ', kv2.no_kavling) ORDER BY jk2.nama_jalan, kv2.no_kavling SEPARATOR ', ')
                       FROM kavling_request_kavling krk2
                       JOIN kavling kv2 ON kv2.id_kavling = krk2.id_kavling
                       JOIN jalan jk2 ON jk2.id_jalan = kv2.id_jalan
                       WHERE krk2.id_request = kr.id_request) AS multi_kavling_labels,
                      (SELECT GROUP_CONCAT(j2.nama_jalan ORDER BY j2.nama_jalan SEPARATOR ', ')
                       FROM kavling_request_jalan krj2
                       JOIN jalan j2 ON j2.id_jalan = krj2.id_jalan
                       WHERE krj2.id_request = kr.id_request) AS multi_jalan_labels", false)
            ->join('proyek p', 'p.id_proyek = kr.id_proyek', 'left')
            ->join('cluster c', 'c.id_cluster = kr.id_cluster', 'left')
            ->join('jalan j', 'j.id_jalan = kr.id_jalan', 'left')
            ->join('kavling k', 'k.id_kavling = kr.id_kavling', 'left')
            ->join('jalan kj', 'kj.id_jalan = k.id_jalan', 'left')
            ->join('tipe t', 't.id_tipe = kr.id_tipe', 'left')
            ->whereIn('kr.id_request', $intIds)
            ->get()
            ->getResultArray();

        $map = [];
        foreach ($rows as $row) {
            $map[(string) $row['id_request']] = $row;
        }
        return $map;
    }

    /**
     * @param string[] $ids
     * @return array<string, array<string, mixed>>
     */
    private function hydrateTagihanDireksi(array $ids): array
    {
        $strIds = array_values(array_unique(array_filter(array_map('strval', $ids), static fn (string $v): bool => $v !== '')));
        if ($strIds === []) {
            return [];
        }

        $rows = $this->db->table('invoice_log il')
            ->select('il.no_inv, il.id_konsumen, il.id_kavling, il.tanggal_invoice, il.tanggal_jatuh_tempo, il.catatan, il.tagihan, il.date_add, il.add_by, il.nomor_surat, il.is_signed_direktur,
                      kon.nama_konsumen, k.no_kavling, j.nama_jalan, p.id_proyek, p.nama_proyek')
            ->join('konsumen kon', 'kon.id_konsumen = il.id_konsumen', 'left')
            ->join('kavling k', 'k.id_kavling = il.id_kavling', 'left')
            ->join('jalan j', 'j.id_jalan = k.id_jalan', 'left')
            ->join('cluster cl', 'cl.id_cluster = j.id_cluster', 'left')
            ->join('proyek p', 'p.id_proyek = cl.id_proyek', 'left')
            ->whereIn('il.no_inv', $strIds)
            ->get()
            ->getResultArray();

        $map = [];
        foreach ($rows as $row) {
            $map[(string) $row['no_inv']] = $row;
        }
        return $map;
    }

    /**
     * @param string[] $ids
     * @return array<string, array<string, mixed>>
     */
    private function hydrateCashoutSubkon(array $ids): array
    {
        $intIds = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if ($intIds === []) {
            return [];
        }

        $rows = $this->db->table('cashout_subkon_detail csd')
            ->select("csd.id_cashout_subkon_detail, csd.id_cashout_subkon, csd.berita_acara, csd.persentase, csd.nominal, csd.tanggal_jatuh_tempo, csd.status, csd.spp_no, csd.spp_add_by, csd.spp_created_at, csd.pengajuan_cair_created_at, csd.is_paid,
                      cs.nomor_surat, cs.tanggal_surat, cs.total_nominal, cs.add_by, cs.created_at AS spk_created_at,
                      s.nama_subkon,
                      csk_agg.id_proyek, csk_agg.nama_proyek, csk_agg.first_id_kavling, csk_agg.id_kavlings, csk_agg.kavling_options, csk_agg.lokasi_kavling", false)
            ->join('cashout_subkon cs', 'cs.id_cashout_subkon = csd.id_cashout_subkon')
            ->join('subkon s', 's.id = cs.id_subkon', 'left')
            ->join("(SELECT csk.id_cashout_subkon,
                            MIN(p.id_proyek) AS id_proyek,
                            GROUP_CONCAT(DISTINCT p.nama_proyek ORDER BY p.nama_proyek SEPARATOR ', ') AS nama_proyek,
                            MIN(k.id_kavling) AS first_id_kavling,
                            GROUP_CONCAT(DISTINCT k.id_kavling ORDER BY j.nama_jalan, ABS(k.no_kavling), k.no_kavling SEPARATOR ',') AS id_kavlings,
                            GROUP_CONCAT(DISTINCT CONCAT(k.id_kavling, '|', j.nama_jalan, '|', k.no_kavling) ORDER BY j.nama_jalan, ABS(k.no_kavling), k.no_kavling SEPARATOR ',') AS kavling_options,
                            GROUP_CONCAT(DISTINCT CONCAT(j.nama_jalan, ' No. ', k.no_kavling) ORDER BY j.nama_jalan, ABS(k.no_kavling), k.no_kavling SEPARATOR ', ') AS lokasi_kavling
                     FROM cashout_subkon_kavling csk
                     JOIN kavling k ON k.id_kavling = csk.id_kavling
                     JOIN jalan j ON j.id_jalan = k.id_jalan
                     JOIN cluster cl ON cl.id_cluster = j.id_cluster
                     JOIN proyek p ON p.id_proyek = cl.id_proyek
                     GROUP BY csk.id_cashout_subkon) csk_agg", 'csk_agg.id_cashout_subkon = cs.id_cashout_subkon', 'left', false)
            ->whereIn('csd.id_cashout_subkon_detail', $intIds)
            ->get()
            ->getResultArray();

        $map = [];
        foreach ($rows as $row) {
            $map[(string) $row['id_cashout_subkon_detail']] = $row;
        }
        return $map;
    }

    /**
     * @param string[] $ids
     * @return array<string, array<string, mixed>>
     */
    private function hydrateMgm(array $ids): array
    {
        $intIds = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if ($intIds === []) {
            return [];
        }

        $rows = $this->db->table('referral_bonuses rb')
            ->select("rb.id, rb.id_referral, rb.id_stage, rb.nominal_bonus, rb.status, rb.eligible_at, rb.confirmed_by, rb.confirmed_at, rb.paid_by_promosi, rb.paid_promosi_at, rb.submitted_keuangan_by, rb.submitted_keuangan_at, rb.nominal_pengajuan_keuangan, rb.nominal_cair_keuangan, rb.cair_keuangan_at, rb.keterangan, rb.add_by, rb.created_at,
                      r.id_proyek, p.nama_proyek, st.nama_tahapan,
                      k_ref.nama_konsumen AS referrer_nama, k_new.nama_konsumen AS referred_nama,
                      kv_agg.id_kavling, kv_agg.no_kavling, jl.nama_jalan", false)
            ->join('referrals r', 'r.id = rb.id_referral')
            ->join('referral_bonus_stages st', 'st.id = rb.id_stage', 'left')
            ->join('proyek p', 'p.id_proyek = r.id_proyek', 'left')
            ->join('konsumen k_ref', 'k_ref.id_konsumen = r.id_konsumen_referrer', 'left')
            ->join('mkdt mk', 'mk.id_mkdt = r.id_mkdt_referred', 'left')
            ->join('konsumen k_new', 'k_new.id_konsumen = mk.id_konsumen', 'left')
            ->join('(SELECT id_mkdt, MIN(id_kavling) AS id_kavling, MIN(id_jalan) AS id_jalan, GROUP_CONCAT(DISTINCT no_kavling SEPARATOR ", ") AS no_kavling FROM kavling GROUP BY id_mkdt) kv_agg', 'kv_agg.id_mkdt = mk.id_mkdt', 'left', false)
            ->join('jalan jl', 'jl.id_jalan = kv_agg.id_jalan', 'left')
            ->whereIn('rb.id', $intIds)
            ->get()
            ->getResultArray();

        $map = [];
        foreach ($rows as $row) {
            $map[(string) $row['id']] = $row;
        }
        return $map;
    }

    /**
     * @param string[] $ids
     * @return array<string, array<string, mixed>>
     */
    private function hydrateTiketMasalah(array $ids, int $userId): array
    {
        $intIds = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if ($intIds === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($intIds), '?'));
        $sql = "SELECT tm.id, tm.ref_type, tm.ref_id, tm.id_proyek, tm.tanggal_masalah, tm.keterangan, tm.prioritas, tm.status, tm.pic_user_id, tm.created_at,
                       p.nama_proyek,
                       k.no_kavling, jk.nama_jalan AS kavling_jalan,
                       o.nama AS others_nama, jo.nama_jalan AS others_jalan,
                       EXISTS (SELECT 1 FROM tiket_masalah_user tmu WHERE tmu.id_tiket_masalah = tm.id AND tmu.user_id = ?) AS is_assigned_to_me,
                       (SELECT GROUP_CONCAT(u2.username SEPARATOR ', ') FROM tiket_masalah_user tmu2 JOIN users u2 ON u2.id = tmu2.user_id WHERE tmu2.id_tiket_masalah = tm.id) AS assigned_users_list
                FROM tiket_masalah tm
                LEFT JOIN proyek p ON p.id_proyek = tm.id_proyek
                LEFT JOIN kavling k ON k.id_kavling = tm.ref_id AND tm.ref_type = 'kavling'
                LEFT JOIN jalan jk ON jk.id_jalan = k.id_jalan
                LEFT JOIN others o ON o.id = tm.ref_id AND tm.ref_type = 'others'
                LEFT JOIN jalan jo ON jo.id_jalan = o.id_jalan
                WHERE tm.id IN ({$placeholders})";

        $rows = $this->db->query($sql, array_merge([$userId], $intIds))->getResultArray();

        $map = [];
        foreach ($rows as $row) {
            $map[(string) $row['id']] = $row;
        }
        return $map;
    }
}

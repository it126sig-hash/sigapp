<?php

namespace App\Repositories\AuditTrail;

use Config\Database;

class HistoryRepository
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function hasTable(): bool
    {
        return $this->db->tableExists('history_log');
    }

    public function insert(array $fields): bool
    {
        if (! $this->hasTable()) {
            return false;
        }

        return (bool) $this->db->table('history_log')->insert($fields);
    }

    public function getList(array $filters, int $limit, int $offset): array
    {
        if (! $this->hasTable()) {
            return [];
        }

        $builder = $this->baseListQuery();
        $this->applyFilters($builder, $filters);

        return $builder
            ->orderBy('h.created_at', 'DESC')
            ->orderBy('h.id', 'DESC')
            ->limit($limit, $offset)
            ->get()
            ->getResultArray();
    }

    public function count(array $filters): int
    {
        if (! $this->hasTable()) {
            return 0;
        }

        $builder = $this->db->table('history_log h');
        $this->applyFilters($builder, $filters);

        return (int) $builder->countAllResults();
    }

    public function getByKavling(int $idKavling, array $modules, int $limit, int $offset): array
    {
        if (! $this->hasTable()) {
            return [];
        }

        $builder = $this->baseListQuery();
        $this->applyModuleFilter($builder, $modules);
        $this->applyKavlingFilter($builder, $idKavling);

        return $builder
            ->orderBy('h.created_at', 'DESC')
            ->orderBy('h.id', 'DESC')
            ->limit($limit, $offset)
            ->get()
            ->getResultArray();
    }

    public function countByKavling(int $idKavling, array $modules): int
    {
        if (! $this->hasTable()) {
            return 0;
        }

        $builder = $this->db->table('history_log h');
        $this->applyModuleFilter($builder, $modules);
        $this->applyKavlingFilter($builder, $idKavling);

        return (int) $builder->countAllResults();
    }

    public function getByReferenceAction(string $module, int $referenceId, string $action): array
    {
        if (! $this->hasTable()) {
            return [];
        }

        return $this->baseListQuery()
            ->where('h.module', $module)
            ->where('h.reference_id', $referenceId)
            ->where('h.action', $action)
            ->orderBy('h.created_at', 'ASC')
            ->orderBy('h.id', 'ASC')
            ->get()->getResultArray();
    }

    public function getProyekIdByKavling(int $idKavling): ?int
    {
        $row = $this->db->table('kavling k')
            ->select('c.id_proyek')
            ->join('jalan j', 'j.id_jalan = k.id_jalan', 'left')
            ->join('cluster c', 'c.id_cluster = j.id_cluster', 'left')
            ->where('k.id_kavling', $idKavling)
            ->get()
            ->getRow();

        return $row && $row->id_proyek ? (int) $row->id_proyek : null;
    }

    public function getTargetSiteplanProyekId(int $referenceId): ?int
    {
        $row = $this->db->table('target_siteplan')
            ->select('id_proyek')
            ->where('id_target', $referenceId)
            ->get()
            ->getRow();

        return $row && $row->id_proyek ? (int) $row->id_proyek : null;
    }

    public function getCashoutProyekIds(int $idCashoutSubkon): array
    {
        $rows = $this->db->table('cashout_subkon_kavling csk')
            ->select('DISTINCT c.id_proyek', false)
            ->join('kavling k', 'k.id_kavling = csk.id_kavling', 'left')
            ->join('jalan j', 'j.id_jalan = k.id_jalan', 'left')
            ->join('cluster c', 'c.id_cluster = j.id_cluster', 'left')
            ->where('csk.id_cashout_subkon', $idCashoutSubkon)
            ->where('c.id_proyek IS NOT NULL', null, false)
            ->orderBy('c.id_proyek', 'ASC')
            ->get()
            ->getResult();

        return array_values(array_map(static fn ($row) => (int) $row->id_proyek, $rows));
    }

    private function baseListQuery()
    {
        return $this->db->table('history_log h')
            ->select('h.*, users.username, users.username as add_by_username')
            ->join('users', 'users.id = h.add_by', 'left');
    }

    private function applyFilters($builder, array $filters): void
    {
        if (! empty($filters['module'])) {
            $this->applyModuleFilter($builder, $filters['module']);
        }

        foreach (['id_proyek', 'id_kavling', 'reference_type', 'reference_id', 'action'] as $key) {
            if (($filters[$key] ?? '') !== '' && $filters[$key] !== null) {
                $builder->where('h.' . $key, $filters[$key]);
            }
        }

        if (! empty($filters['date_from'])) {
            $builder->where('DATE(h.created_at) >=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $builder->where('DATE(h.created_at) <=', $filters['date_to']);
        }
    }

    private function applyModuleFilter($builder, $modules): void
    {
        if (is_array($modules)) {
            $modules = array_values(array_filter(array_map('strval', $modules)));
            if ($modules) {
                $builder->whereIn('h.module', $modules);
            }
            return;
        }

        $module = trim((string) $modules);
        if ($module !== '') {
            $builder->where('h.module', $module);
        }
    }

    private function applyKavlingFilter($builder, int $idKavling): void
    {
        $subQuery = $this->db->table('cashout_subkon_kavling csk')
            ->select('csk.id_cashout_subkon')
            ->where('csk.id_kavling', $idKavling)
            ->getCompiledSelect();

        $builder->groupStart()
            ->where('h.id_kavling', $idKavling)
            ->orGroupStart()
                ->where('h.module', 'cashout_subkon')
                ->where('h.reference_type', 'cashout_subkon')
                ->where("h.reference_id IN ({$subQuery})", null, false)
            ->groupEnd()
        ->groupEnd();
    }
}

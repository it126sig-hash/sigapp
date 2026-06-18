<?php

namespace App\Repositories;

class HistoryRepository
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
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

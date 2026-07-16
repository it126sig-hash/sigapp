<?php

namespace App\Services;

use App\Repositories\HistoryRepository;

class HistoryService
{
    public const MODULE_LABELS = [
        'mkdt' => 'MKDT / Kavling',
        'produksi' => 'Produksi',
        'dana_jaminan' => 'Dana Jaminan',
        'pencairan_akad' => 'Pencairan Akad',
        'target_siteplan' => 'Target Siteplan',
        'cashout_subkon' => 'Cashout Subkon',
    ];

    protected $db;
    protected HistoryRepository $repo;

    public function __construct(?HistoryRepository $repo = null)
    {
        $this->db = \Config\Database::connect();
        $this->repo = $repo ?? new HistoryRepository();
    }

    public function hasTable(): bool
    {
        return $this->repo->hasTable();
    }

    public function log(string $module, array $fields): bool
    {
        if (! $this->repo->hasTable()) {
            return false;
        }

        $fields['module'] = $module;
        $fields['action'] = (string) ($fields['action'] ?? 'update');
        $fields['created_at'] = $fields['created_at'] ?? date('Y-m-d H:i:s');
        $fields['add_by'] = $fields['add_by'] ?? (function_exists('user_id') ? user_id() : null);

        foreach (['old_data', 'new_data', 'metadata'] as $key) {
            $fields[$key] = $this->encodeJsonValue($fields[$key] ?? null);
        }

        if (empty($fields['id_proyek'])) {
            $fields['id_proyek'] = $this->resolveProyekId($module, $fields);
        }

        if ($module === 'cashout_subkon') {
            $fields = $this->enrichCashoutMetadata($fields);
        }

        return $this->repo->insert($fields);
    }

    public function getList(array $filters, int $limit, int $offset): array
    {
        $limit = max(1, min(100, $limit));
        $offset = max(0, $offset);
        $rows = $this->repo->getList($filters, $limit, $offset);

        return [
            'data' => $this->decodeRows($rows),
            'total' => $this->repo->count($filters),
            'limit' => $limit,
            'offset' => $offset,
        ];
    }

    public function getByKavling(int $idKavling, array $modules, int $limit, int $offset): array
    {
        $limit = max(1, min(50, $limit));
        $offset = max(0, $offset);
        $rows = $this->decodeRows($this->repo->getByKavling($idKavling, $modules, $limit, $offset));
        $total = $this->repo->countByKavling($idKavling, $modules);

        return [
            'history' => array_map(static fn (array $row) => (object) $row, $rows),
            'history_total' => $total,
            'history_limit' => $limit,
            'history_offset' => $offset,
            'history_next_offset' => $offset + count($rows),
            'history_has_more' => ($offset + count($rows)) < $total,
        ];
    }

    public function moduleLabels(): array
    {
        return self::MODULE_LABELS;
    }

    private function decodeRows(array $rows): array
    {
        foreach ($rows as &$row) {
            $row['module_label'] = self::MODULE_LABELS[$row['module']] ?? $row['module'];
            $row['old_data'] = $this->decodeJsonValue($row['old_data'] ?? null);
            $row['new_data'] = $this->decodeJsonValue($row['new_data'] ?? null);
            $row['metadata'] = $this->decodeJsonValue($row['metadata'] ?? null);
        }

        return $rows;
    }

    private function encodeJsonValue($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_string($value)) {
            return $value;
        }

        return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    private function decodeJsonValue(?string $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function resolveProyekId(string $module, array $fields): ?int
    {
        $idKavling = (int) ($fields['id_kavling'] ?? 0);
        if ($idKavling > 0) {
            return $this->getProyekIdByKavling($idKavling);
        }

        $referenceId = (int) ($fields['reference_id'] ?? 0);
        if ($referenceId <= 0) {
            return null;
        }

        if ($module === 'target_siteplan') {
            $row = $this->db->table('target_siteplan')
                ->select('id_proyek')
                ->where('id_target', $referenceId)
                ->get()
                ->getRow();
            return $row ? (int) $row->id_proyek : null;
        }

        if ($module === 'cashout_subkon') {
            $ids = $this->getCashoutProyekIds($referenceId);
            return $ids[0] ?? null;
        }

        return null;
    }

    private function getProyekIdByKavling(int $idKavling): ?int
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

    private function enrichCashoutMetadata(array $fields): array
    {
        $referenceId = (int) ($fields['reference_id'] ?? 0);
        if ($referenceId <= 0) {
            return $fields;
        }

        $metadata = $this->decodeJsonValue($fields['metadata'] ?? null);
        $projectIds = $this->getCashoutProyekIds($referenceId);
        if ($projectIds) {
            $metadata['id_proyek_list'] = $projectIds;
            $fields['id_proyek'] = $fields['id_proyek'] ?? $projectIds[0];
        }
        $fields['metadata'] = $this->encodeJsonValue($metadata);

        return $fields;
    }

    private function getCashoutProyekIds(int $idCashoutSubkon): array
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
}

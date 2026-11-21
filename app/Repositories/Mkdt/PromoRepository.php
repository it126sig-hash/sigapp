<?php

namespace App\Repositories\Mkdt;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\BaseConnection;

class PromoRepository
{
    protected BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
    }

    /**
     * Ambil daftar proyek untuk dropdown/filter Promo.
     *
     * @return array<int, object>
     */
    public function getProyekList(): array
    {
        return $this->db->table('proyek')
            ->select('id_proyek, nama_proyek')
            ->get()
            ->getResult();
    }

    /**
     * Query builder DataTables server-side khusus domain Promo.
     *
     * @param array<string, mixed> $params
     * @return array{recordsTotal: int, recordsFiltered: int, rows: array<int, object>}
     */
    public function getDataTables(array $params): array
    {
        $idProyek = isset($params['id_proyek']) && $params['id_proyek'] !== '' && $params['id_proyek'] !== null
            ? (int) $params['id_proyek']
            : null;
        if ($idProyek !== null && $idProyek <= 0) {
            $idProyek = null;
        }

        $search = '';
        if (isset($params['search']) && is_array($params['search'])) {
            $search = trim((string) ($params['search']['value'] ?? ''));
        } elseif (isset($params['search'])) {
            $search = trim((string) $params['search']);
        }

        $start  = max(0, (int) ($params['start'] ?? 0));
        $length = isset($params['length']) ? (int) $params['length'] : 10;

        $totalBuilder = $this->db->table('promo')
            ->join('proyek', 'proyek.id_proyek = promo.id_proyek');
        $recordsTotal = (int) $totalBuilder->countAllResults();

        $filteredBuilder = $this->db->table('promo')
            ->join('proyek', 'proyek.id_proyek = promo.id_proyek');
        $this->applyDataTableFilters($filteredBuilder, $idProyek, $search);
        $recordsFiltered = (int) $filteredBuilder->countAllResults();

        $dataBuilder = $this->basePromoBuilder();
        $this->applyDataTableFilters($dataBuilder, $idProyek, $search);

        if ($length > 0) {
            $dataBuilder->limit($length, $start);
        } elseif ($start > 0) {
            $dataBuilder->offset($start);
        }

        $rows = $dataBuilder->get()->getResult();

        return [
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'rows'            => $rows,
        ];
    }

    /**
     * Ambil seluruh data promo (perbaikan bug legacy ClusterModel).
     *
     * @return array<int, object>
     */
    public function getAll(?int $idProyek = null, string $search = ''): array
    {
        $builder = $this->basePromoBuilder();
        $this->applyDataTableFilters(
            $builder,
            ($idProyek !== null && $idProyek > 0) ? $idProyek : null,
            trim($search)
        );

        return $builder->get()->getResult();
    }

    /**
     * Ambil detail satu promo berdasarkan ID.
     */
    public function getById(int $id): ?object
    {
        if ($id <= 0) {
            return null;
        }

        $row = $this->db->table('promo')
            ->select('
                promo.*,
                proyek.nama_proyek,
                a.username as uadd_by,
                c.username as uedit_by
            ')
            ->join('proyek', 'proyek.id_proyek = promo.id_proyek', 'left')
            ->join('users as a', 'a.id = promo.add_by', 'left')
            ->join('users as c', 'c.id = promo.edit_by', 'left')
            ->where('promo.id', $id)
            ->get()
            ->getRow(0);

        return $row ?: null;
    }

    /**
     * Tambah data promo baru.
     *
     * @param array<string, mixed> $fields
     */
    public function insert(array $fields): int|false
    {
        $ok = $this->db->table('promo')->insert($fields);
        if (!$ok) {
            return false;
        }

        return (int) $this->db->insertID();
    }

    /**
     * Perbarui data promo berdasarkan ID.
     *
     * @param array<string, mixed> $fields
     */
    public function updateById(int $id, array $fields): bool
    {
        if ($id <= 0) {
            return false;
        }

        return (bool) $this->db->table('promo')
            ->where('id', $id)
            ->update($fields);
    }

    /**
     * Soft-deactivate promo (status = 0).
     */
    public function deactivate(int $id, int $actorId, string $timestamp): bool
    {
        if ($id <= 0) {
            return false;
        }

        return (bool) $this->db->table('promo')
            ->where('id', $id)
            ->update([
                'status'     => 0,
                'edit_by'    => $actorId,
                'updated_at' => $timestamp,
            ]);
    }

    protected function basePromoBuilder(): BaseBuilder
    {
        return $this->db->table('promo')
            ->select('
                promo.*,
                proyek.nama_proyek,
                a.username as uadd_by,
                c.username as uedit_by
            ')
            ->join('proyek', 'proyek.id_proyek = promo.id_proyek')
            ->join('users as a', 'a.id = promo.add_by', 'left')
            ->join('users as c', 'c.id = promo.edit_by', 'left');
    }

    protected function applyDataTableFilters(BaseBuilder $builder, ?int $idProyek, string $search): void
    {
        if ($idProyek !== null) {
            $builder->where('promo.id_proyek', $idProyek);
        }

        if ($search !== '') {
            $builder->groupStart()
                ->like('promo.nama_promo', $search)
                ->groupEnd();
        }
    }
}

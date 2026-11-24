<?php

namespace App\Repositories\Mkdt;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\BaseConnection;

class StockRepository
{
    protected BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
    }

    /**
     * Query builder DataTables server-side untuk daftar kavling stock (progres 100% dan belum terjual / batal).
     *
     * @param array<string, mixed> $params
     * @return array{recordsTotal: int, recordsFiltered: int, rows: array<int, object>}
     */
    public function getStockListDataTables(array $params): array
    {
        $totalBuilder = $this->baseStockBuilder();
        $this->applyMandatoryAndScopeFilters($totalBuilder, $params);
        $recordsTotal = (int) $totalBuilder->countAllResults();

        $filteredBuilder = $this->baseStockBuilder();
        $this->applyMandatoryAndScopeFilters($filteredBuilder, $params);
        $this->applyOptionalFilters($filteredBuilder, $params);
        $recordsFiltered = (int) $filteredBuilder->countAllResults();

        $dataBuilder = $this->baseStockBuilder();
        $dataBuilder->select('
            kavling.*,
            mkdt.status_mkdt,
            produksi.progres_bangunan,
            produksi.tanggal_pembangunan,
            produksi.tanggal_selesai_pembangunan,
            jalan.id_jalan,
            jalan.nama_jalan,
            cluster.id_cluster,
            cluster.nama_cluster,
            proyek.id_proyek,
            proyek.nama_proyek,
            tipe.tipe_rumah,
            tipe.no_tipe_rumah,
            konsumen.nama_konsumen,
            konsumen.hp_konsumen,
            konsumen.keterangan as keterangan_batal,
            a.username as uadd_by,
            b.username as uedit_by
        ');

        $this->applyMandatoryAndScopeFilters($dataBuilder, $params);
        $this->applyOptionalFilters($dataBuilder, $params);

        $start  = max(0, (int) ($params['start'] ?? 0));
        $length = isset($params['length']) ? (int) $params['length'] : 10;

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

    private function baseStockBuilder(): BaseBuilder
    {
        return $this->db->table('kavling')
            ->join('tipe', 'tipe.id_tipe = kavling.id_tipe')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
            ->join('cluster', 'jalan.id_cluster = cluster.id_cluster')
            ->join('proyek', 'proyek.id_proyek = cluster.id_proyek')
            ->join('produksi', 'kavling.id_produksi = produksi.id_produksi')
            ->join('mkdt', 'kavling.id_mkdt = mkdt.id_mkdt', 'left')
            ->join('konsumen', 'konsumen.id_konsumen = mkdt.id_konsumen', 'left')
            ->join('users a', 'produksi.add_by = a.id', 'left')
            ->join('users b', 'produksi.edit_by = b.id', 'left');
    }

    /**
     * Terapkan filter wajib stock dan scope lokasi (mutually exclusive: jalan -> cluster -> proyek).
     *
     * @param array<string, mixed> $params
     */
    private function applyMandatoryAndScopeFilters(BaseBuilder $builder, array $params): void
    {
        if (!empty($params['id_jalan'])) {
            $builder->where('jalan.id_jalan', (int) $params['id_jalan']);
        } elseif (!empty($params['id_cluster'])) {
            $builder->where('cluster.id_cluster', (int) $params['id_cluster']);
        } else {
            $builder->where('proyek.id_proyek', (int) ($params['id_proyek'] ?? 0));
        }

        $builder->where('produksi.progres_bangunan', 100);
        $builder->groupStart()
            ->where('mkdt.status_mkdt', 'Batal')
            ->orWhere('mkdt.status_mkdt IS NULL', null, false)
            ->groupEnd();
    }

    /**
     * Terapkan filter opsional (rentang tanggal pembangunan/selesai dan pencarian).
     *
     * @param array<string, mixed> $params
     */
    private function applyOptionalFilters(BaseBuilder $builder, array $params): void
    {
        $this->applyDateOrRangeFilter($builder, 'produksi.tanggal_pembangunan', $params['tgl_pembangunan'] ?? null);
        $this->applyDateOrRangeFilter($builder, 'produksi.tanggal_selesai_pembangunan', $params['tgl_selesai'] ?? null);

        $search = '';
        if (isset($params['search']) && is_array($params['search'])) {
            $search = trim((string) ($params['search']['value'] ?? ''));
        } elseif (isset($params['search'])) {
            $search = trim((string) $params['search']);
        }

        if ($search !== '') {
            $builder->groupStart()
                ->like('konsumen.nama_konsumen', $search)
                ->orLike('jalan.nama_jalan', $search)
                ->orLike('kavling.no_kavling', $search)
                ->groupEnd();
        }
    }

    private function applyDateOrRangeFilter(BaseBuilder $builder, string $column, mixed $rawValue): void
    {
        $value = trim((string) ($rawValue ?? ''));
        if ($value === '') {
            return;
        }

        $dates = explode(' to ', $value);
        if (count($dates) === 2) {
            $start = trim($dates[0]);
            $end   = trim($dates[1]);
            if ($start !== '') {
                $builder->where($column . ' >=', $start);
            }
            if ($end !== '') {
                $builder->where($column . ' <=', $end);
            }
            return;
        }

        $builder->where($column, trim($dates[0]));
    }
}

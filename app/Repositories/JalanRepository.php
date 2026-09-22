<?php

namespace App\Repositories;

class JalanRepository
{
    protected $db;

    public function __construct()
    {
        $this->db = db_connect();
    }

    public function getClusters(): array
    {
        return $this->db->table('cluster')
            ->select('nama_proyek, id_cluster, nama_cluster')
            ->join('proyek', 'proyek.id_proyek = cluster.id_proyek')
            ->orderBy('nama_proyek', 'asc')
            ->get()
            ->getResult();
    }

    public function getAll(array $params): array
    {
        $builder = $this->baseQuery();
        $search = $params['search'] ?? '';
        $clusterIds = $this->normalizeIds($params['id_cluster'] ?? null);

        if ($search !== '') {
            $builder->like('jalan.nama_jalan', $search);
        }

        if (!empty($params['id_proyek'])) {
            $builder->where('cluster.id_proyek', $params['id_proyek']);
        }

        if ($clusterIds !== []) {
            $builder->whereIn('cluster.id_cluster', $clusterIds);
        }

        return $builder->get()->getResult();
    }

    public function getDataTables(array $params): array
    {
        $search = $params['search']['value'] ?? '';
        $idCluster = $params['id_cluster'] ?? null;
        $idProyek = $params['id_proyek'] ?? null;

        $recordsTotal = $this->countAll();
        $recordsFiltered = $this->countDataTables($search, $idCluster, $idProyek);
        $builder = $this->dataTablesBaseQuery($search, $idCluster, $idProyek);

        if (isset($params['start'], $params['length']) && (int) $params['length'] > 0) {
            $builder->limit((int) $params['length'], (int) $params['start']);
        }

        return [
            'draw' => $params['draw'] ?? null,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'rows' => $builder->get()->getResult(),
            'start' => (int) ($params['start'] ?? 0),
        ];
    }

    private function countAll(): int
    {
        return (int) $this->db->table('jalan')
            ->select('count(id_jalan) as count')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster')
            ->join('proyek', 'proyek.id_proyek = cluster.id_proyek')
            ->get()
            ->getRow()
            ->count;
    }

    private function countDataTables(string $search = '', $idCluster = null, $idProyek = null): int
    {
        return count($this->dataTablesBaseQuery($search, $idCluster, $idProyek)->get()->getResult());
    }

    private function dataTablesBaseQuery(string $search = '', $idCluster = null, $idProyek = null)
    {
        $builder = $this->baseQuery();

        $clusterIds = $this->normalizeIds($idCluster);
        if (!empty($idProyek)) {
            $builder->where('proyek.id_proyek', $idProyek);
        }

        if ($clusterIds !== []) {
            $builder->whereIn('cluster.id_cluster', $clusterIds);
        }

        if ($search !== '') {
            $builder->like('jalan.nama_jalan', $search);
        }

        return $builder;
    }

    private function normalizeIds($value): array
    {
        $values = is_array($value) ? $value : (($value === null || $value === '') ? [] : [$value]);
        $ids = [];

        foreach ($values as $item) {
            $id = filter_var($item, FILTER_VALIDATE_INT);
            if ($id !== false && $id > 0) {
                $ids[(int) $id] = (int) $id;
            }
        }

        return array_values($ids);
    }

    private function baseQuery()
    {
        return $this->db->table('jalan')
            ->select("id_jalan, jalan.id_cluster, nama_cluster, nama_proyek, nama_jalan,
                (SELECT COUNT(kavling.id_kavling) FROM kavling WHERE kavling.id_jalan = jalan.id_jalan) as total_kavling,
                (SELECT COUNT(kavling.id_kavling) FROM kavling JOIN mkdt ON mkdt.id_mkdt = kavling.id_mkdt WHERE kavling.id_jalan = jalan.id_jalan AND mkdt.status_mkdt = 'Akad' AND mkdt.is_batal = 0) as total_akad,
                (SELECT COUNT(kavling.id_kavling) FROM kavling JOIN mkdt ON mkdt.id_mkdt = kavling.id_mkdt WHERE kavling.id_jalan = jalan.id_jalan AND mkdt.status_mkdt = 'Booking' AND mkdt.is_batal = 0) as total_booking,
                (SELECT COUNT(kavling.id_kavling) FROM kavling LEFT JOIN mkdt ON mkdt.id_mkdt = kavling.id_mkdt WHERE kavling.id_jalan = jalan.id_jalan AND (kavling.id_mkdt IS NULL OR mkdt.status_mkdt = 'Batal' OR mkdt.is_batal = 1)) as total_belum_terjual
            ")
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster')
            ->join('proyek', 'proyek.id_proyek = cluster.id_proyek');
    }
}

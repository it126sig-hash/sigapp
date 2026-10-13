<?php

namespace App\Repositories\Proyek;

class ClusterRepository
{
    protected $db;

    public function __construct()
    {
        $this->db = db_connect();
    }

    public function getDataTables(array $params): array
    {
        $search = $params['search']['value'] ?? '';
        $idProyek = $params['id_proyek'] ?? null;

        $builder = $this->db->table('cluster')
            ->select('id_cluster, cluster.id_proyek, nama_proyek, nama_cluster, is_active, 
                (SELECT COUNT(kavling.id_kavling) 
                 FROM kavling 
                 JOIN jalan ON jalan.id_jalan = kavling.id_jalan 
                 JOIN produksi ON produksi.id_produksi = kavling.id_produksi 
                 WHERE jalan.id_cluster = cluster.id_cluster 
                 AND produksi.progres_bangunan = 100) as jumlah_bangun,
                (SELECT COUNT(kavling.id_kavling) 
                 FROM kavling 
                 JOIN jalan ON jalan.id_jalan = kavling.id_jalan 
                 JOIN mkdt ON mkdt.id_mkdt = kavling.id_mkdt 
                 WHERE jalan.id_cluster = cluster.id_cluster 
                 AND mkdt.status_mkdt = \'Akad\' 
                 AND mkdt.is_batal = 0) as total_akad,
                (SELECT COUNT(kavling.id_kavling) 
                 FROM kavling 
                 JOIN jalan ON jalan.id_jalan = kavling.id_jalan 
                 JOIN mkdt ON mkdt.id_mkdt = kavling.id_mkdt 
                 WHERE jalan.id_cluster = cluster.id_cluster 
                 AND mkdt.status_mkdt = \'Booking\' 
                 AND mkdt.is_batal = 0) as total_booking,
                (SELECT COUNT(kavling.id_kavling) 
                 FROM kavling 
                 JOIN jalan ON jalan.id_jalan = kavling.id_jalan 
                 LEFT JOIN mkdt ON mkdt.id_mkdt = kavling.id_mkdt 
                 WHERE jalan.id_cluster = cluster.id_cluster 
                 AND (kavling.id_mkdt IS NULL OR mkdt.status_mkdt = \'Batal\' OR mkdt.is_batal = 1)) as total_belum_terjual')
            ->join('proyek', 'proyek.id_proyek = cluster.id_proyek');

        if (!empty($idProyek)) {
            $builder->where('cluster.id_proyek', $idProyek);
        }

        if ($search !== '') {
            $builder->like('cluster.nama_cluster', $search);
        }

        // Count filtered
        $countFilteredBuilder = $this->db->table('cluster')
            ->select('id_cluster')
            ->join('proyek', 'proyek.id_proyek = cluster.id_proyek');

        if (!empty($idProyek)) {
            $countFilteredBuilder->where('cluster.id_proyek', $idProyek);
        }

        if ($search !== '') {
            $countFilteredBuilder->like('cluster.nama_cluster', $search);
        }

        $recordsFiltered = $countFilteredBuilder->countAllResults();

        // Count total
        $countTotalBuilder = $this->db->table('cluster')
            ->join('proyek', 'proyek.id_proyek = cluster.id_proyek');

        if (!empty($idProyek)) {
            $countTotalBuilder->where('cluster.id_proyek', $idProyek);
        }

        $recordsTotal = $countTotalBuilder->countAllResults();

        if (isset($params['start'], $params['length']) && (int) $params['length'] > 0) {
            $builder->offset((int) $params['start'])->limit((int) $params['length']);
        }

        return [
            'draw'            => $params['draw'] ?? null,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'rows'            => $builder->get()->getResult(),
        ];
    }

    public function getAll(array $params): array
    {
        $idProyek = $params['id_proyek'] ?? null;
        $search   = $params['search'] ?? '';

        $builder = $this->db->table('cluster')
            ->select('id_cluster, cluster.id_proyek, nama_proyek, nama_cluster, is_active')
            ->join('proyek', 'proyek.id_proyek = cluster.id_proyek');

        if (!empty($idProyek)) {
            $builder->where('cluster.id_proyek', $idProyek);
        }

        if ($search !== '') {
            $builder->like('cluster.nama_cluster', $search);
        }

        return $builder->get()->getResult();
    }

    public function getById(int $idCluster): ?object
    {
        return $this->db->table('cluster')
            ->where('id_cluster', $idCluster)
            ->get()
            ->getRow();
    }

    public function countJalan(int $idCluster): int
    {
        return $this->db->table('jalan')
            ->where('id_cluster', $idCluster)
            ->countAllResults();
    }

    public function countKavling(int $idCluster): int
    {
        return $this->db->table('kavling')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
            ->where('jalan.id_cluster', $idCluster)
            ->countAllResults();
    }
}

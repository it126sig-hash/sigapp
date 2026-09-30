<?php

namespace App\Repositories;

use CodeIgniter\Database\BaseConnection;

class OthersRepository
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
    }

    public function getAll(?int $id, int $idProyek, array $clusterIds, array $filters = []): array
    {
        $kategoriList = $filters['kategori'] ?? [];
        $builder = $this->db->table('others')
            ->select('others.*, a.username as planning_add, b.username as produksi_add,
                c.username as legal_add, d.username as planning_edit, e.username as produksi_edit,
                f.username as legal_edit, jalan.nama_jalan, cluster.id_cluster, cluster.nama_cluster')
            ->join('jalan', 'jalan.id_jalan = others.id_jalan', 'left')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster', 'left')
            ->join('proyek', 'proyek.id_proyek = cluster.id_proyek', 'left')
            ->join('users as a', 'a.id = others.planning_add_by', 'left')
            ->join('users as b', 'b.id = others.produksi_add_by', 'left')
            ->join('users as c', 'c.id = others.legal_add_by', 'left')
            ->join('users as d', 'd.id = others.planning_edit_by', 'left')
            ->join('users as e', 'e.id = others.produksi_edit_by', 'left')
            ->join('users as f', 'f.id = others.legal_edit_by', 'left');

        if ($id !== null) {
            return $builder->where('others.id', $id)->get()->getResult();
        }

        if ($idProyek <= 0) {
            return [];
        }

        $builder->where('cluster.id_proyek', $idProyek);
        if ($clusterIds !== []) {
            $builder->whereIn('cluster.id_cluster', $clusterIds);
        }

        if ($this->db->fieldExists('scope', 'others')) {
            $allowedScopes = ['siteplan', 'produksi'];
            if (in_array('Masalah', $kategoriList, true)) {
                $allowedScopes[] = 'masalah';
            }
            $builder->groupStart()
                ->whereIn('others.scope', $allowedScopes)
                ->orWhere('others.scope IS NULL', null, false)
                ->groupEnd();
        }

        if (in_array('Masalah', $kategoriList, true)) {
            [$statusSql, $dateSql] = $this->problemSql($filters);
            $builder->where("EXISTS (
                SELECT 1 FROM tiket_masalah tm
                WHERE tm.ref_type = 'others' AND tm.ref_id = others.id
                $statusSql
                $dateSql
            )", null, false);
            $builder->select("(SELECT tm.prioritas FROM tiket_masalah tm
                WHERE tm.ref_type = 'others' AND tm.ref_id = others.id
                $statusSql $dateSql
                ORDER BY tm.created_at DESC LIMIT 1) as prioritas_masalah", false);
        }

        return $builder->get()->getResult();
    }

    public function hasProgressHistory(): bool
    {
        return $this->db->tableExists('produksi_jalan_progress_history');
    }

    public function countProgressHistory(int $idOthers): int
    {
        return $this->db->table('produksi_jalan_progress_history')
            ->where('id_others', $idOthers)
            ->countAllResults();
    }

    public function getProgressHistory(int $idOthers, int $limit, int $offset): array
    {
        return $this->db->table('produksi_jalan_progress_history h')
            ->select('h.*, users.username')
            ->join('users', 'users.id = h.add_by', 'left')
            ->where('h.id_others', $idOthers)
            ->orderBy('h.created_at', 'DESC')
            ->limit($limit, $offset)
            ->get()
            ->getResult();
    }

    private function problemSql(array $filters): array
    {
        $status = $filters['status_masalah'] ?? null;
        $statusSql = $status
            ? 'AND tm.status = ' . $this->db->escape($status)
            : "AND tm.status NOT IN ('selesai', 'batal')";

        $dateSql = '';
        $start = $filters['periode_mulai'] ?? null;
        $end = $filters['periode_selesai'] ?? null;
        if ($start && $end) {
            $column = ($filters['periode_masalah_jenis'] ?? null) === 'tgl_selesai' ? 'tm.tgl_selesai' : 'tm.created_at';
            $dateSql = 'AND DATE(' . $column . ') >= ' . $this->db->escape($start)
                . ' AND DATE(' . $column . ') <= ' . $this->db->escape($end);
        }

        return [$statusSql, $dateSql];
    }
}

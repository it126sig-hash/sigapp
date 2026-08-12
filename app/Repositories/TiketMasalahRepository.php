<?php

namespace App\Repositories;

use CodeIgniter\Database\BaseConnection;

class TiketMasalahRepository
{
    protected BaseConnection $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function getListWithSummary(string $refType, int $refId): array
    {
        return $this->db->table('tiket_masalah tm')
            ->select('
                tm.*,
                u.username as pic_username,
                (SELECT COUNT(*) FROM tiket_masalah_foto tmf WHERE tmf.id_tiket_masalah = tm.id) as foto_count,
                (SELECT file_path FROM tiket_masalah_foto tmf WHERE tmf.id_tiket_masalah = tm.id ORDER BY tmf.id ASC LIMIT 1) as foto_path_1,
                (SELECT keterangan FROM tiket_masalah_progress tmp WHERE tmp.id_tiket_masalah = tm.id ORDER BY tmp.created_at DESC LIMIT 1) as last_progress_keterangan,
                (SELECT created_at FROM tiket_masalah_progress tmp WHERE tmp.id_tiket_masalah = tm.id ORDER BY tmp.created_at DESC LIMIT 1) as last_progress_date,
                (SELECT GROUP_CONCAT(u2.username SEPARATOR \', \') FROM tiket_masalah_user tmu JOIN users u2 ON u2.id = tmu.user_id WHERE tmu.id_tiket_masalah = tm.id) as assigned_users_list
            ')
            ->join('users u', 'u.id = tm.pic_user_id', 'left')
            ->where('tm.ref_type', $refType)
            ->where('tm.ref_id', $refId)
            ->orderBy('tm.created_at', 'DESC')
            ->get()
            ->getResult();
    }

    public function getDetailFull(int $idTiket): ?object
    {
        $tiket = $this->db->table('tiket_masalah tm')
            ->select('tm.*, u.username as pic_username')
            ->join('users u', 'u.id = tm.pic_user_id', 'left')
            ->where('tm.id', $idTiket)
            ->get()
            ->getRow();

        if (!$tiket) {
            return null;
        }

        $tiket->foto = $this->db->table('tiket_masalah_foto')
            ->where('id_tiket_masalah', $idTiket)
            ->get()
            ->getResult();

        $tiket->assigned_users = $this->db->table('tiket_masalah_user tmu')
            ->select('u.id, u.username')
            ->join('users u', 'u.id = tmu.user_id')
            ->where('tmu.id_tiket_masalah', $idTiket)
            ->get()
            ->getResult();

        return $tiket;
    }

    public function getProgressPaginated(int $idTiket, int $limit, int $offset): array
    {
        return $this->db->table('tiket_masalah_progress tmp')
            ->select('tmp.*, u.username as user_username')
            ->join('users u', 'u.id = tmp.user_id', 'left')
            ->where('tmp.id_tiket_masalah', $idTiket)
            ->orderBy('tmp.created_at', 'ASC')
            ->limit($limit, $offset)
            ->get()
            ->getResult();
    }

    private function buildDatatablesQuery(array $params)
    {
        $builder = $this->db->table('tiket_masalah tm')
            ->select('
                tm.id, tm.ref_type, tm.ref_id, tm.id_proyek, tm.tanggal_masalah, 
                tm.keterangan, tm.prioritas, tm.status, tm.created_at,
                u.username as pic_username,
                IF(tm.ref_type = "kavling", CONCAT(jk.nama_jalan, " - ", k.no_kavling), CONCAT(jo.nama_jalan, " - ", o.nama)) as lokasi,
                (SELECT keterangan FROM tiket_masalah_progress tmp WHERE tmp.id_tiket_masalah = tm.id ORDER BY tmp.created_at DESC LIMIT 1) as last_progress_keterangan,
                (SELECT created_at FROM tiket_masalah_progress tmp WHERE tmp.id_tiket_masalah = tm.id ORDER BY tmp.created_at DESC LIMIT 1) as last_progress_date,
                (SELECT GROUP_CONCAT(u2.username SEPARATOR \', \') FROM tiket_masalah_user tmu JOIN users u2 ON u2.id = tmu.user_id WHERE tmu.id_tiket_masalah = tm.id) as assigned_users_list
            ')
            ->join('users u', 'u.id = tm.pic_user_id', 'left')
            ->join('kavling k', 'k.id_kavling = tm.ref_id AND tm.ref_type = "kavling"', 'left')
            ->join('jalan jk', 'jk.id_jalan = k.id_jalan', 'left')
            ->join('others o', 'o.id = tm.ref_id AND tm.ref_type = "others"', 'left')
            ->join('jalan jo', 'jo.id_jalan = o.id_jalan', 'left');

        if (!empty($params['search'])) {
            $search = $params['search'];
            $builder->groupStart()
                ->like('tm.keterangan', $search)
                ->orLike('k.no_kavling', $search)
                ->orLike('jk.nama_jalan', $search)
                ->orLike('o.nama', $search)
                ->orLike('jo.nama_jalan', $search)
                ->orLike('u.username', $search)
                ->orLike('tm.prioritas', $search)
                ->groupEnd();
        }

        if (!empty($params['filter_status'])) {
            if ($params['filter_status'] === 'active') {
                $builder->whereNotIn('tm.status', ['selesai', 'batal']);
            } else {
                $builder->where('tm.status', $params['filter_status']);
            }
        }

        if (!empty($params['filter_prioritas'])) {
            $builder->where('tm.prioritas', $params['filter_prioritas']);
        }

        if (!empty($params['filter_proyek'])) {
            $builder->where('tm.id_proyek', $params['filter_proyek']);
        }

        if (!empty($params['order_by']) && !empty($params['order_dir'])) {
            $builder->orderBy($params['order_by'], $params['order_dir']);
        } else {
            $builder->orderBy('tm.created_at', 'DESC');
        }

        return $builder;
    }

    public function getDatatables(array $params): array
    {
        $builder = $this->buildDatatablesQuery($params);
        if (isset($params['limit']) && isset($params['offset'])) {
            $builder->limit($params['limit'], $params['offset']);
        }
        return $builder->get()->getResult();
    }

    public function countDatatables(array $params): int
    {
        return $this->buildDatatablesQuery($params)->countAllResults(false);
    }

    public function countAll(): int
    {
        return $this->db->table('tiket_masalah')->countAllResults();
    }
}

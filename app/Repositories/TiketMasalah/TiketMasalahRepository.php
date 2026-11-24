<?php

namespace App\Repositories\TiketMasalah;

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
            ->orderBy('tmp.is_pinned', 'DESC')
            ->orderBy('tmp.created_at', 'DESC')
            ->limit($limit, $offset)
            ->get()
            ->getResult();
    }

    private function buildDatatablesQuery(array $params)
    {
        $builder = $this->db->table('tiket_masalah tm')
            ->select('
                tm.id, tm.ref_type, tm.ref_id, tm.id_proyek, tm.tanggal_masalah,
                COALESCE(jk.id_cluster, jo.id_cluster) as target_cluster_id,
                tm.keterangan, tm.prioritas, tm.status, tm.created_at,
                u.username as pic_username,
                IF(tm.ref_type = "kavling", CONCAT_WS(" - ", jk.nama_jalan, k.no_kavling), CONCAT_WS(" - ", jo.nama_jalan, o.nama)) as lokasi,
                (SELECT keterangan FROM tiket_masalah_progress tmp WHERE tmp.id_tiket_masalah = tm.id ORDER BY tmp.created_at DESC LIMIT 1) as last_progress_keterangan,
                (SELECT created_at FROM tiket_masalah_progress tmp WHERE tmp.id_tiket_masalah = tm.id ORDER BY tmp.created_at DESC LIMIT 1) as last_progress_date,
                (SELECT GROUP_CONCAT(u2.username SEPARATOR \', \') FROM tiket_masalah_user tmu JOIN users u2 ON u2.id = tmu.user_id WHERE tmu.id_tiket_masalah = tm.id) as assigned_users_list,
                (SELECT GROUP_CONCAT(tmf.file_path ORDER BY tmf.id ASC SEPARATOR \'||\') FROM tiket_masalah_foto tmf WHERE tmf.id_tiket_masalah = tm.id) as foto_paths
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

        if (!empty($params['filter_pembuat'])) {
            $builder->where('tm.pic_user_id', $params['filter_pembuat']);
        }

        if (!empty($params['filter_divisi'])) {
            $builder->join('auth_groups_users agu', 'agu.user_id = tm.pic_user_id', 'left')
                    ->where('agu.group_id', $params['filter_divisi']);
        }

        if (!empty($params['filter_periode'])) {
            $dates = explode(' to ', $params['filter_periode']);
            if (count($dates) == 2) {
                $builder->where('DATE(tm.tanggal_masalah) >=', trim($dates[0]))
                        ->where('DATE(tm.tanggal_masalah) <=', trim($dates[1]));
            } else {
                $builder->where('DATE(tm.tanggal_masalah)', trim($dates[0]));
            }
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

    public function insertOthersArea(array $fields): int
    {
        $this->db->table('others')->insert($fields);
        return (int) $this->db->insertID();
    }

    public function getUserPrimaryGroupId(int $userId): ?int
    {
        $row = $this->db->table('auth_groups_users')
            ->select('group_id')
            ->where('user_id', $userId)
            ->get()
            ->getRow();

        return $row ? (int) $row->group_id : null;
    }

    public function getRefInfo(string $refType, int $refId): ?object
    {
        if ($refType === 'kavling') {
            return $this->db->table('kavling k')
                ->select("
                    k.id_kavling as id,
                    k.no_kavling,
                    'kavling' as tipe,
                    j.nama_jalan,
                    cl.nama_cluster,
                    p.nama_proyek,
                    p.id_proyek,
                    COALESCE(pr.progres_bangunan, 0) as progres_bangunan,
                    t.tipe_rumah,
                    t.lb,
                    t.lt,
                    t.keterangan as tipe_keterangan
                ")
                ->join('jalan j', 'j.id_jalan = k.id_jalan')
                ->join('cluster cl', 'cl.id_cluster = j.id_cluster')
                ->join('proyek p', 'p.id_proyek = cl.id_proyek')
                ->join('produksi pr', 'pr.id_produksi = k.id_produksi', 'left')
                ->join('tipe t', 't.id_tipe = k.id_tipe', 'left')
                ->where('k.id_kavling', $refId)
                ->get()
                ->getRow();
        } else {
            return $this->db->table('others o')
                ->select("
                    o.id,
                    o.nama as no_kavling,
                    o.tipe,
                    j.nama_jalan,
                    cl.nama_cluster,
                    p.nama_proyek,
                    p.id_proyek,
                    COALESCE(o.progres, 0) as progres_bangunan
                ")
                ->join('jalan j', 'j.id_jalan = o.id_jalan', 'left')
                ->join('cluster cl', 'cl.id_cluster = j.id_cluster', 'left')
                ->join('proyek p', 'p.id_proyek = cl.id_proyek', 'left')
                ->where('o.id', $refId)
                ->get()
                ->getRow();
        }
    }

    public function getActiveUsers(): array
    {
        return $this->db->table('users')
            ->select('id, username, name')
            ->where('active', 1)
            ->where('deleted_at IS NULL', null, false)
            ->orderBy('username', 'ASC')
            ->get()
            ->getResult();
    }

    public function getCreators(): array
    {
        return $this->db->table('users u')
            ->select('u.id, u.username, u.name')
            ->join('tiket_masalah tm', 'tm.pic_user_id = u.id')
            ->where('u.active', 1)
            ->where('u.deleted_at IS NULL', null, false)
            ->groupBy('u.id')
            ->orderBy('u.username', 'ASC')
            ->get()
            ->getResult();
    }

    public function getDivisions(): array
    {
        return $this->db->table('auth_groups')
            ->select('id, name')
            ->orderBy('name', 'ASC')
            ->get()
            ->getResult();
    }
}

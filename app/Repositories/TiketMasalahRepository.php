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
                (SELECT COUNT(*) FROM tiket_masalah_foto tmf WHERE tmf.id_tiket_masalah = tm.id) as foto_count
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
}

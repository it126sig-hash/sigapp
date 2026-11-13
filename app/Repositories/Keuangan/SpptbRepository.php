<?php

namespace App\Repositories\Keuangan;

use CodeIgniter\Database\BaseConnection;

class SpptbRepository
{
    public function __construct(private BaseConnection $db) {}

    public function getLatestByMkdtId(int $idMkdt, int $limit = 3): array
    {
        return $this->db->table('file_spptb')
            ->select(['file_spptb.id', 'lokasi', 'file_spptb.created_at', 'users.username'])
            ->join('users', 'users.id = file_spptb.add_by')
            ->where('file_spptb.id_mkdt', $idMkdt)
            ->orderBy('file_spptb.created_at', 'DESC')
            ->limit($limit)
            ->get()
            ->getResult();
    }

    public function insertFile(int $idMkdt, string $lokasi, int $actorId): int
    {
        $this->db->table('file_spptb')->insert([
            'id_mkdt'    => $idMkdt,
            'lokasi'     => $lokasi,
            'created_at' => date('Y-m-d H:i:s'),
            'add_by'     => $actorId,
        ]);

        return (int) $this->db->insertID();
    }
}

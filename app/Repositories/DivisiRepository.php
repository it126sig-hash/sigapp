<?php

namespace App\Repositories;

use CodeIgniter\Database\ConnectionInterface;

/**
 * DivisiRepository
 *
 * Repository for Divisi master data.
 */
class DivisiRepository
{
    private ConnectionInterface $db;
    private string $table;
    private string $primaryKey;

    public function __construct(?ConnectionInterface $db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
        $this->table = 'divisi';
        $this->primaryKey = 'id_divisi';
    }

    public function findAll(int $limit = null, int $offset = 0): array
    {
        $builder = $this->db->table($this->table);
        if ($limit !== null) {
            $builder->limit($limit, $offset);
        }
        return $builder->get()->getResult();
    }

    public function findById($id): ?object
    {
        return $this->db->table($this->table)
            ->where($this->primaryKey, $id)
            ->get()
            ->getRow();
    }
}

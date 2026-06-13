<?php

namespace App\Repositories;

class MkdtHistoryRepository
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function hasTable(): bool
    {
        return $this->db->tableExists('mkdt_change_history');
    }

    public function insert(array $fields): bool
    {
        if (!$this->hasTable()) {
            return false;
        }

        return (bool) $this->db->table('mkdt_change_history')->insert($fields);
    }

    public function countByKavling(int $idKavling): int
    {
        if (!$this->hasTable()) {
            return 0;
        }

        return $this->db->table('mkdt_change_history')
            ->where('id_kavling', $idKavling)
            ->countAllResults();
    }

    public function getByKavling(int $idKavling, int $limit, int $offset): array
    {
        if (!$this->hasTable()) {
            return [];
        }

        return $this->db->table('mkdt_change_history h')
            ->select('h.*, users.username')
            ->join('users', 'users.id = h.add_by', 'left')
            ->where('h.id_kavling', $idKavling)
            ->orderBy('h.created_at', 'DESC')
            ->orderBy('h.id', 'DESC')
            ->limit($limit, $offset)
            ->get()->getResult();
    }
}

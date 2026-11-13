<?php

namespace App\Repositories\Mkdt;

use CodeIgniter\Database\BaseConnection;

class StandingInstructionRepository
{
    protected BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
    }

    /**
     * Ambil daftar Standing Instruction per kavling (master list_si LEFT JOIN si).
     *
     * @return array<int, object>
     */
    public function getByKavling(int $idKavling): array
    {
        return $this->db->table('list_si')
            ->select('list_si.nama, list_si.id as id_list_si_ori, si.*, a.username as uadd_by, b.username as uedit_by')
            ->join('si', 'si.id_list_si = list_si.id AND si.id_kavling = ' . $this->db->escape($idKavling), 'left')
            ->join('users a', 'a.id = si.add_by', 'left')
            ->join('users b', 'b.id = si.edit_by', 'left')
            ->get()
            ->getResult();
    }

    public function getSiListIdBySiId(int $idSi): ?int
    {
        $row = $this->db->table('si')
            ->select('id_list_si')
            ->where('id', $idSi)
            ->get()
            ->getRow();

        if (!$row || !isset($row->id_list_si)) {
            return null;
        }

        $idListSi = (int) $row->id_list_si;

        return $idListSi > 0 ? $idListSi : null;
    }

    public function getListSiNameById(int $idListSi): ?string
    {
        $row = $this->db->table('list_si')
            ->select('nama')
            ->where('id', $idListSi)
            ->get()
            ->getRow();

        if (!$row || !isset($row->nama)) {
            return null;
        }

        return (string) $row->nama;
    }

    public function getKavlingMkdtId(int $idKavling): ?int
    {
        $row = $this->db->table('kavling')
            ->select('id_mkdt')
            ->where('id_kavling', $idKavling)
            ->get()
            ->getRow();

        $idMkdt = $row ? (int) ($row->id_mkdt ?? 0) : 0;

        return $idMkdt > 0 ? $idMkdt : null;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function insertSi(array $data): int|false
    {
        $inserted = $this->db->table('si')->insert($data);
        if (!$inserted) {
            return false;
        }

        return (int) $this->db->insertID();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateSi(int $idSi, array $data): bool
    {
        return (bool) $this->db->table('si')
            ->where('id', $idSi)
            ->update($data);
    }
}

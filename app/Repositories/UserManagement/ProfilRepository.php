<?php

namespace App\Repositories\UserManagement;

use CodeIgniter\Database\BaseConnection;

class ProfilRepository
{
    protected BaseConnection $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * Mengambil row user berdasarkan ID.
     */
    public function getUserRow(int $userId): ?object
    {
        return $this->db->table('users')->where('id', $userId)->get()->getRow();
    }

    /**
     * Mengambil nama departemen/grup pengguna.
     */
    public function getDepartmentName(int $userId): string
    {
        $groupRow = $this->db->table('auth_groups_users')
            ->select('auth_groups.description, auth_groups.name')
            ->join('auth_groups', 'auth_groups.id = auth_groups_users.group_id')
            ->where('auth_groups_users.user_id', $userId)
            ->get()->getRow();

        if ($groupRow) {
            return $groupRow->description ?: strtoupper($groupRow->name);
        }

        return 'Belum Ada Departemen';
    }

    /**
     * Update data tabel users.
     */
    public function updateUser(int $userId, array $fields): bool
    {
        return $this->db->table('users')->where('id', $userId)->update($fields);
    }

    /**
     * Sinkronisasi nama karyawan jika ditautkan ke user.
     */
    public function syncKaryawanName(int $userId, string $name): bool
    {
        return $this->db->table('karyawan')->where('id_user', $userId)->update(['nama_karyawan' => $name]);
    }
}

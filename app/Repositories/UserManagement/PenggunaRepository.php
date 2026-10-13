<?php

namespace App\Repositories\UserManagement;

use App\Models\UserModel;
use CodeIgniter\Database\BaseConnection;
use Myth\Auth\Authorization\GroupModel;
use Myth\Auth\Authorization\PermissionModel;

class PenggunaRepository
{
    protected BaseConnection $db;
    protected UserModel $userModel;
    protected GroupModel $groupModel;
    protected PermissionModel $permissionModel;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->userModel = new UserModel();
        $this->groupModel = new GroupModel();
        $this->permissionModel = new PermissionModel();
    }

    /**
     * Mengambil seluruh user terhubung dengan karyawan, divisi, dan level.
     */
    public function getAll(): array
    {
        return $this->userModel
            ->select('users.*, karyawan.nik, karyawan.nama_karyawan, karyawan.status, auth_groups.name as divisi, auth_permissions.name as level')
            ->join('karyawan', 'karyawan.id_user = users.id')
            ->join('auth_groups', 'auth_groups.id = karyawan.id_divisi', 'left')
            ->join('auth_permissions', 'auth_permissions.id = karyawan.id_level', 'left')
            ->findAll();
    }

    /**
     * Mengambil data single user dengan join data karyawan & auth.
     */
    public function findById(int $id): ?object
    {
        return $this->userModel
            ->select('users.*, karyawan.nik, karyawan.nama_karyawan, karyawan.status, auth_groups.name as divisi, auth_permissions.name as level')
            ->join('karyawan', 'karyawan.id_user = users.id', 'left')
            ->join('auth_groups', 'auth_groups.id = karyawan.id_divisi', 'left')
            ->join('auth_permissions', 'auth_permissions.id = karyawan.id_level', 'left')
            ->where('users.id', $id)
            ->first();
    }

    /**
     * Mengambil entitas user murni berdasarkan ID.
     */
    public function findRawUser(int $id): ?object
    {
        return $this->userModel->where('id', $id)->first();
    }

    /**
     * Insert user baru.
     */
    public function insertUser(array $data): int
    {
        $this->userModel->insert($data);
        return (int) $this->userModel->getInsertID();
    }

    /**
     * Update data user.
     */
    public function updateUser(int $id, array $data): bool
    {
        return (bool) $this->userModel->update($id, $data);
    }

    /**
     * Sinkronisasi grup divisi user.
     */
    public function syncGroup(int $userId, int $groupId): void
    {
        $this->groupModel->removeUserFromAllGroups($userId);
        if ($groupId > 0) {
            $this->groupModel->addUserToGroup($userId, $groupId);
        }
    }

    /**
     * Sinkronisasi level permission user.
     */
    public function syncPermission(int $userId, int $permissionId): void
    {
        if ($permissionId > 0) {
            $this->permissionModel->removePermissionFromUser($permissionId, $userId);
            $this->permissionModel->addPermissionToUser($permissionId, $userId);
        }
    }

    /**
     * Update active status.
     */
    public function toggleActive(int $id, int $newActiveStatus): bool
    {
        return (bool) $this->userModel->update($id, ['active' => $newActiveStatus]);
    }
}

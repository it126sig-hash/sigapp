<?php

namespace App\Repositories\UserManagement;

use App\Models\KaryawanModel;
use Myth\Auth\Authorization\GroupModel;
use Myth\Auth\Authorization\PermissionModel;

class KaryawanRepository
{
    protected KaryawanModel $karyawanModel;
    protected GroupModel $groupModel;
    protected PermissionModel $permissionModel;

    public function __construct()
    {
        $this->karyawanModel = new KaryawanModel();
        $this->groupModel = new GroupModel();
        $this->permissionModel = new PermissionModel();
    }

    /**
     * Mengambil seluruh karyawan bergabung dengan auth groups dan permissions.
     */
    public function getAll(): array
    {
        return $this->karyawanModel
            ->select('nik, nama_karyawan, karyawan.id_divisi, karyawan.id_level, karyawan.id_user, status, auth_groups.name as divisi, auth_permissions.name as level')
            ->join('auth_groups', 'auth_groups.id = karyawan.id_divisi', 'left')
            ->join('auth_permissions', 'auth_permissions.id = karyawan.id_level', 'left')
            ->findAll();
    }

    /**
     * Mengambil karyawan yang belum ditautkan ke akun user.
     */
    public function getUnlinkedKaryawan(): array
    {
        return $this->karyawanModel
            ->where('id_user', null)
            ->orWhere('id_user', '')
            ->orWhere('id_user', 0)
            ->findAll();
    }

    /**
     * Mencari karyawan berdasarkan NIK.
     */
    public function findByNik(string $nik): ?object
    {
        return $this->karyawanModel->where('nik', $nik)->first();
    }

    /**
     * Mencari karyawan berdasarkan id_user.
     */
    public function findByUserId(int $userId): ?object
    {
        return $this->karyawanModel->where('id_user', $userId)->first();
    }

    /**
     * Insert karyawan baru.
     */
    public function insert(array $data): bool
    {
        return (bool) $this->karyawanModel->insert($data);
    }

    /**
     * Update karyawan berdasarkan NIK.
     */
    public function update(string $nik, array $data): bool
    {
        return (bool) $this->karyawanModel->update($nik, $data);
    }

    /**
     * Tautkan user ID ke karyawan.
     */
    public function linkUser(string $nik, int $userId): bool
    {
        return (bool) $this->karyawanModel->update($nik, ['id_user' => $userId]);
    }

    /**
     * Toggle status aktif karyawan.
     */
    public function toggleStatus(string $nik, int $newStatus): bool
    {
        return (bool) $this->karyawanModel->update($nik, ['status' => $newStatus]);
    }

    /**
     * Mengambil daftar seluruh grup divisi (auth_groups).
     */
    public function getDivisiList(): array
    {
        return $this->groupModel->findAll();
    }

    /**
     * Mengambil daftar seluruh level hak akses (auth_permissions).
     */
    public function getLevelList(): array
    {
        return $this->permissionModel->findAll();
    }
}

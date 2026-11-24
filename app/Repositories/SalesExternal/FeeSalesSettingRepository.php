<?php

namespace App\Repositories\SalesExternal;

use CodeIgniter\Database\BaseConnection;
use Config\Database;

class FeeSalesSettingRepository
{
    protected BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? Database::connect();
    }

    /**
     * Ambil seluruh peran beserta konfigurasi hak akses fee sales
     */
    public function listPermissions(): array
    {
        return $this->db->table('auth_groups g')
            ->select('g.id AS id_group, g.name AS group_name, g.description AS group_description,
                      COALESCE(s.can_view, 0) AS can_view,
                      COALESCE(s.can_manage, 0) AS can_manage,
                      COALESCE(s.can_submit_spp, 0) AS can_submit_spp,
                      COALESCE(s.can_cairkan, 0) AS can_cairkan,
                      s.updated_at, s.updated_by')
            ->join('fee_sales_setting_akses s', 's.id_group = g.id', 'left')
            ->orderBy('g.id', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Periksa apakah konfigurasi hak akses untuk group sudah ada
     */
    public function existsByGroupId(int $idGroup): bool
    {
        return $this->db->table('fee_sales_setting_akses')
            ->where('id_group', $idGroup)
            ->countAllResults() > 0;
    }

    /**
     * Insert atau update konfigurasi hak akses fee sales berdasarkan id_group
     */
    public function upsertGroupPermission(int $idGroup, array $payload): bool
    {
        if ($this->existsByGroupId($idGroup)) {
            return (bool) $this->db->table('fee_sales_setting_akses')
                ->where('id_group', $idGroup)
                ->update($payload);
        }

        $payload['id_group'] = $idGroup;

        return (bool) $this->db->table('fee_sales_setting_akses')->insert($payload);
    }

    /**
     * Ambil agregasi nilai tertinggi (MAX) hak akses fee sales untuk daftar group_id
     */
    public function getAggregatedPermissionsByGroupIds(array $groupIds): ?object
    {
        $groupIds = array_values(array_unique(array_filter(
            array_map('intval', $groupIds),
            static fn (int $id): bool => $id > 0
        )));

        if (empty($groupIds)) {
            return null;
        }

        return $this->db->table('fee_sales_setting_akses')
            ->select('MAX(can_view) AS can_view, MAX(can_manage) AS can_manage, MAX(can_submit_spp) AS can_submit_spp, MAX(can_cairkan) AS can_cairkan')
            ->whereIn('id_group', $groupIds)
            ->get()
            ->getRow();
    }
}

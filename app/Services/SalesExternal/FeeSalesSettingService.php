<?php

namespace App\Services\SalesExternal;

use CodeIgniter\Database\BaseConnection;

class FeeSalesSettingService
{
    protected BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
    }

    /**
     * Ambil daftar hak akses seluruh peran
     */
    public function listPermissions(): array
    {
        $rows = $this->db->table('auth_groups g')
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

        return array_map(static function (array $r): array {
            return [
                'id_group'          => (int) $r['id_group'],
                'group_name'        => (string) $r['group_name'],
                'group_description' => (string) ($r['group_description'] ?? ''),
                'can_view'          => (int) $r['can_view'],
                'can_manage'        => (int) $r['can_manage'],
                'can_submit_spp'    => (int) $r['can_submit_spp'],
                'can_cairkan'       => (int) $r['can_cairkan'],
                'updated_at'        => $r['updated_at'],
                'updated_by'        => $r['updated_by'] !== null ? (int) $r['updated_by'] : null,
            ];
        }, $rows);
    }

    /**
     * Simpan pembaruan hak akses per role
     */
    public function savePermissions(array $permissions, ?int $userId = null): bool
    {
        if (empty($permissions)) {
            return false;
        }

        $now = date('Y-m-d H:i:s');
        $feeSalesMenu = $this->db->table('menus')->where('url', 'fee-sales')->get()->getRow();
        $feeMenuId = $feeSalesMenu ? (int) $feeSalesMenu->id : null;

        $this->db->transStart();

        foreach ($permissions as $item) {
            $idGroup = (int) ($item['id_group'] ?? 0);
            if ($idGroup <= 0) {
                continue;
            }

            // Role Admin (id 1) selalu dipastikan full access
            if ($idGroup === 1) {
                $canView = 1;
                $canManage = 1;
                $canSubmitSpp = 1;
                $canCairkan = 1;
            } else {
                $canView = !empty($item['can_view']) ? 1 : 0;
                $canManage = !empty($item['can_manage']) ? 1 : 0;
                $canSubmitSpp = !empty($item['can_submit_spp']) ? 1 : 0;
                $canCairkan = !empty($item['can_cairkan']) ? 1 : 0;
            }

            $exists = $this->db->table('fee_sales_setting_akses')
                ->where('id_group', $idGroup)
                ->countAllResults();

            $payload = [
                'can_view'       => $canView,
                'can_manage'     => $canManage,
                'can_submit_spp' => $canSubmitSpp,
                'can_cairkan'    => $canCairkan,
                'updated_at'     => $now,
                'updated_by'     => $userId,
            ];

            if ($exists > 0) {
                $this->db->table('fee_sales_setting_akses')
                    ->where('id_group', $idGroup)
                    ->update($payload);
            } else {
                $payload['id_group'] = $idGroup;
                $this->db->table('fee_sales_setting_akses')->insert($payload);
            }

            // Sinkronkan menu_roles untuk menu fee-sales jika menu ada
            if ($feeMenuId) {
                if ($canView === 1) {
                    $menuRoleExists = $this->db->table('menu_roles')
                        ->where('id_groups', $idGroup)
                        ->where('id_menu', $feeMenuId)
                        ->countAllResults();

                    if ($menuRoleExists === 0) {
                        $this->db->table('menu_roles')->insert([
                            'id_groups' => $idGroup,
                            'id_menu'   => $feeMenuId,
                        ]);
                    }
                } else {
                    // Jangan hapus menu role admin
                    if ($idGroup !== 1) {
                        $this->db->table('menu_roles')
                            ->where('id_groups', $idGroup)
                            ->where('id_menu', $feeMenuId)
                            ->delete();
                    }
                }
            }
        }

        $this->db->transComplete();

        return $this->db->transStatus() !== false;
    }

    /**
     * Resolusi hak akses user saat ini berdasarkan relasi auth_groups_users dan fee_sales_setting_akses.
     * Admin (id_group 1) selalu mendapatkan hak penuh (all 1).
     * Role lain mengambil nilai agregat tertinggi (MAX) dari seluruh grup yang dimiliki.
     */
    public function getUserPermissions(?int $userId = null): array
    {
        if ($userId === null) {
            $userId = (int) user_id();
        }

        if ($userId <= 0) {
            return [
                'can_view'          => 0,
                'can_manage'        => 0,
                'can_submit_spp'    => 0,
                'can_cairkan'       => 0,
                'can_sign_mkdt'     => 0,
                'can_sign_keuangan' => 0,
            ];
        }

        $groupRows = $this->db->table('auth_groups_users')
            ->select('group_id')
            ->where('user_id', $userId)
            ->get()
            ->getResultArray();

        $groupIds = array_map(static fn($r) => (int) $r['group_id'], $groupRows);

        if (empty($groupIds)) {
            return [
                'can_view'          => 0,
                'can_manage'        => 0,
                'can_submit_spp'    => 0,
                'can_cairkan'       => 0,
                'can_sign_mkdt'     => 0,
                'can_sign_keuangan' => 0,
            ];
        }

        $canSignMkdt     = (in_array(1, $groupIds, true) || in_array(4, $groupIds, true)) ? 1 : 0;
        $canSignKeuangan = (in_array(1, $groupIds, true) || in_array(3, $groupIds, true)) ? 1 : 0;

        // Jika user memiliki role Admin (id 1), beri akses penuh
        if (in_array(1, $groupIds, true)) {
            return [
                'can_view'          => 1,
                'can_manage'        => 1,
                'can_submit_spp'    => 1,
                'can_cairkan'       => 1,
                'can_sign_mkdt'     => 1,
                'can_sign_keuangan' => 1,
            ];
        }

        $row = $this->db->table('fee_sales_setting_akses')
            ->select('MAX(can_view) AS can_view, MAX(can_manage) AS can_manage, MAX(can_submit_spp) AS can_submit_spp, MAX(can_cairkan) AS can_cairkan')
            ->whereIn('id_group', $groupIds)
            ->get()
            ->getRowArray();

        $canView = (int) ($row['can_view'] ?? 0);
        if ($canSignMkdt || $canSignKeuangan) {
            $canView = 1;
        }

        return [
            'can_view'          => $canView,
            'can_manage'        => (int) ($row['can_manage'] ?? 0),
            'can_submit_spp'    => (int) ($row['can_submit_spp'] ?? 0),
            'can_cairkan'       => (int) ($row['can_cairkan'] ?? 0),
            'can_sign_mkdt'     => $canSignMkdt,
            'can_sign_keuangan' => $canSignKeuangan,
        ];
    }
}

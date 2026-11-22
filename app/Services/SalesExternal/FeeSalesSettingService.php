<?php

namespace App\Services\SalesExternal;

use App\Repositories\SalesExternal\FeeSalesSettingRepository;
use App\Services\Settings\MenuAccessService;
use CodeIgniter\Database\BaseConnection;
use Config\Database;

class FeeSalesSettingService
{
    protected BaseConnection $db;
    protected FeeSalesSettingRepository $repo;
    protected MenuAccessService $menuAccessService;

    public function __construct(
        ?BaseConnection $db = null,
        ?FeeSalesSettingRepository $repo = null,
        ?MenuAccessService $menuAccessService = null
    ) {
        $this->db                = $db ?? Database::connect();
        $this->repo              = $repo ?? new FeeSalesSettingRepository($this->db);
        $this->menuAccessService = $menuAccessService ?? new MenuAccessService();
    }

    /**
     * Ambil daftar hak akses seluruh peran
     */
    public function listPermissions(): array
    {
        $rows = $this->repo->listPermissions();

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

        $now       = date('Y-m-d H:i:s');
        $feeMenuId = null;
        foreach ($this->menuAccessService->getActiveMenus() as $menu) {
            if (ltrim(trim((string) ($menu->url ?? '')), '/') === 'fee-sales') {
                $feeMenuId = (int) $menu->id;
                break;
            }
        }

        $this->db->transStart();

        foreach ($permissions as $item) {
            if (! is_array($item)) {
                continue;
            }

            $idGroup = (int) ($item['id_group'] ?? 0);
            if ($idGroup <= 0) {
                continue;
            }

            // Role Admin (id 1) selalu dipastikan full access
            if ($idGroup === 1) {
                $canView      = 1;
                $canManage    = 1;
                $canSubmitSpp = 1;
                $canCairkan   = 1;
            } else {
                $canView      = ! empty($item['can_view']) ? 1 : 0;
                $canManage    = ! empty($item['can_manage']) ? 1 : 0;
                $canSubmitSpp = ! empty($item['can_submit_spp']) ? 1 : 0;
                $canCairkan   = ! empty($item['can_cairkan']) ? 1 : 0;
            }

            $payload = [
                'can_view'       => $canView,
                'can_manage'     => $canManage,
                'can_submit_spp' => $canSubmitSpp,
                'can_cairkan'    => $canCairkan,
                'updated_at'     => $now,
                'updated_by'     => $userId,
            ];

            $this->repo->upsertGroupPermission($idGroup, $payload);

            // Sinkronkan menu_roles untuk menu fee-sales jika menu aktif tersedia
            if ($feeMenuId !== null && $feeMenuId > 0) {
                $currentMenuIds = array_values(array_unique(array_map(
                    'intval',
                    $this->menuAccessService->getGroupMenuIds([$idGroup])
                )));
                $newMenuIds = $currentMenuIds;

                if ($canView === 1) {
                    if (! in_array($feeMenuId, $newMenuIds, true)) {
                        $newMenuIds[] = $feeMenuId;
                    }
                } elseif ($idGroup !== 1) {
                    // Jangan hapus menu role admin
                    $newMenuIds = array_values(array_filter(
                        $newMenuIds,
                        static fn (int $menuId): bool => $menuId !== $feeMenuId
                    ));
                }

                $sortedCurrent = $currentMenuIds;
                $sortedNew     = $newMenuIds;
                sort($sortedCurrent, SORT_NUMERIC);
                sort($sortedNew, SORT_NUMERIC);

                if ($sortedNew !== $sortedCurrent) {
                    $this->menuAccessService->syncGroupMenus($idGroup, $newMenuIds);
                }
            }
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return false;
        }

        return true;
    }

    /**
     * Resolusi hak akses user saat ini berdasarkan relasi auth_groups_users dan fee_sales_setting_akses.
     * Admin (id_group 1) selalu mendapatkan hak penuh (all 1).
     * Role lain mengambil nilai agregat tertinggi (MAX) dari seluruh grup yang dimiliki.
     */
    public function getUserPermissions(?int $userId = null): array
    {
        if ($userId === null) {
            if (! function_exists('user_id')) {
                helper('auth');
            }
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

        $groupIds = $this->menuAccessService->getUserGroupIds($userId);

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

        $row = $this->repo->getAggregatedPermissionsByGroupIds($groupIds);

        $canView = (int) ($row->can_view ?? 0);
        if ($canSignMkdt || $canSignKeuangan) {
            $canView = 1;
        }

        return [
            'can_view'          => $canView,
            'can_manage'        => (int) ($row->can_manage ?? 0),
            'can_submit_spp'    => (int) ($row->can_submit_spp ?? 0),
            'can_cairkan'       => (int) ($row->can_cairkan ?? 0),
            'can_sign_mkdt'     => $canSignMkdt,
            'can_sign_keuangan' => $canSignKeuangan,
        ];
    }
}

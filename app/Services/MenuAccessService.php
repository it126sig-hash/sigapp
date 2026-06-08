<?php

namespace App\Services;

class MenuAccessService
{
    protected $db;

    public function __construct()
    {
        $this->db = db_connect();
    }

    public function getEffectiveMenuIds(int $userId): array
    {
        $menus = $this->getActiveMenus();
        $activeIds = array_fill_keys(array_column($menus, 'id'), true);
        $groupIds = $this->getUserGroupIds($userId);

        if (in_array(1, $groupIds, true)) {
            return array_keys($activeIds);
        }

        $baseIds = $this->getGroupMenuIds($groupIds);
        $overrides = $this->getUserOverrides($userId);
        $visible = array_fill_keys(array_merge($baseIds, $overrides['allow']), true);

        foreach (array_keys($visible) as $id) {
            if (!isset($activeIds[$id])) {
                unset($visible[$id]);
            }
        }

        $denyIds = $this->expandWithDescendants($overrides['deny']);
        foreach ($denyIds as $id) {
            unset($visible[$id]);
        }

        $visible = $this->includeAncestors(array_keys($visible), $menus, $denyIds);
        sort($visible, SORT_NUMERIC);

        return $visible;
    }

    public function getMenuTreeForUser(int $userId): array
    {
        $menus = $this->getActiveMenus();
        $allowed = array_fill_keys($this->getEffectiveMenuIds($userId), true);
        $nodes = [];
        $tree = [];

        foreach ($menus as $menu) {
            if (!isset($allowed[(int) $menu->id])) {
                continue;
            }

            $node = clone $menu;
            $node->children = [];
            $nodes[(int) $node->id] = $node;
        }

        foreach ($nodes as $id => $node) {
            $parentId = (int) $node->parent_id;
            if ($parentId > 0 && isset($nodes[$parentId])) {
                $nodes[$parentId]->children[] = $node;
            } else {
                $tree[] = $node;
            }
        }

        return $tree;
    }

    public function syncGroupMenus(int $groupId, array $menuIds): void
    {
        $menuIds = $this->filterValidMenuIds($menuIds);

        $this->db->transStart();
        $this->db->table('menu_roles')->where('id_groups', $groupId)->delete();
        foreach ($menuIds as $menuId) {
            $this->db->table('menu_roles')->insert([
                'id_groups' => $groupId,
                'id_menu' => $menuId,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
        $this->db->transComplete();
    }

    public function syncUserOverrides(int $userId, array $allowIds, array $denyIds): void
    {
        $allowIds = $this->filterValidMenuIds($allowIds);
        $denyIds = $this->filterValidMenuIds($denyIds);
        $denyLookup = array_fill_keys($denyIds, true);
        $allowIds = array_values(array_filter($allowIds, static fn ($id) => !isset($denyLookup[$id])));

        $this->db->transStart();
        $this->db->table('menu_user_access')->where('id_user', $userId)->delete();
        foreach ($allowIds as $menuId) {
            $this->insertUserOverride($userId, $menuId, 'allow');
        }
        foreach ($denyIds as $menuId) {
            $this->insertUserOverride($userId, $menuId, 'deny');
        }
        $this->db->transComplete();
    }

    public function getGroupMenuIds(array $groupIds): array
    {
        $groupIds = array_values(array_unique(array_map('intval', $groupIds)));
        if (!$groupIds) {
            return [];
        }

        $rows = $this->db->table('menu_roles')
            ->select('id_menu')
            ->whereIn('id_groups', $groupIds)
            ->get()
            ->getResult();

        return array_values(array_unique(array_map(static fn ($row) => (int) $row->id_menu, $rows)));
    }

    public function getUserOverrides(int $userId): array
    {
        $rows = $this->db->table('menu_user_access')
            ->select('id_menu, access_type')
            ->where('id_user', $userId)
            ->get()
            ->getResult();

        $result = ['allow' => [], 'deny' => []];
        foreach ($rows as $row) {
            if ($row->access_type === 'allow' || $row->access_type === 'deny') {
                $result[$row->access_type][] = (int) $row->id_menu;
            }
        }

        return $result;
    }

    public function getActiveMenus(): array
    {
        return $this->db->table('menus')
            ->where('is_active', 1)
            ->orderBy('parent_id', 'ASC')
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResult();
    }

    public function getUserGroupIds(int $userId): array
    {
        $rows = $this->db->table('auth_groups_users')
            ->select('group_id')
            ->where('user_id', $userId)
            ->get()
            ->getResult();

        return array_values(array_unique(array_map(static fn ($row) => (int) $row->group_id, $rows)));
    }

    private function insertUserOverride(int $userId, int $menuId, string $accessType): void
    {
        $this->db->table('menu_user_access')->insert([
            'id_user' => $userId,
            'id_menu' => $menuId,
            'access_type' => $accessType,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function filterValidMenuIds(array $menuIds): array
    {
        $menuIds = array_values(array_unique(array_filter(array_map('intval', $menuIds), static fn ($id) => $id > 0)));
        if (!$menuIds) {
            return [];
        }

        $rows = $this->db->table('menus')
            ->select('id')
            ->whereIn('id', $menuIds)
            ->get()
            ->getResult();

        return array_values(array_unique(array_map(static fn ($row) => (int) $row->id, $rows)));
    }

    private function expandWithDescendants(array $menuIds): array
    {
        $menuIds = array_values(array_unique(array_map('intval', $menuIds)));
        if (!$menuIds) {
            return [];
        }

        $allMenus = $this->db->table('menus')->select('id, parent_id')->get()->getResult();
        $childrenByParent = [];
        foreach ($allMenus as $menu) {
            $childrenByParent[(int) $menu->parent_id][] = (int) $menu->id;
        }

        $expanded = array_fill_keys($menuIds, true);
        $queue = $menuIds;
        while ($queue) {
            $current = array_shift($queue);
            foreach ($childrenByParent[$current] ?? [] as $childId) {
                if (!isset($expanded[$childId])) {
                    $expanded[$childId] = true;
                    $queue[] = $childId;
                }
            }
        }

        return array_map('intval', array_keys($expanded));
    }

    private function includeAncestors(array $menuIds, array $menus, array $denyIds): array
    {
        $visible = array_fill_keys(array_map('intval', $menuIds), true);
        $deny = array_fill_keys(array_map('intval', $denyIds), true);
        $menusById = [];

        foreach ($menus as $menu) {
            $menusById[(int) $menu->id] = $menu;
        }

        foreach ($menuIds as $menuId) {
            $current = $menusById[(int) $menuId] ?? null;
            while ($current && (int) $current->parent_id > 0) {
                $parentId = (int) $current->parent_id;
                if (isset($deny[$parentId]) || !isset($menusById[$parentId])) {
                    break;
                }
                $visible[$parentId] = true;
                $current = $menusById[$parentId];
            }
        }

        return array_map('intval', array_keys($visible));
    }
}

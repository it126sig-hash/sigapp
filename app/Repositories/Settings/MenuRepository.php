<?php

namespace App\Repositories\Settings;

class MenuRepository
{
    protected $db;

    public function __construct()
    {
        $this->db = db_connect();
    }

    public function getGroups(): array
    {
        return $this->db->table('auth_groups')
            ->select('id, name, description')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResult();
    }

    public function getUsers(): array
    {
        return $this->db->table('users')
            ->select('users.id, users.username, karyawan.nama_karyawan, auth_groups.name AS group_name')
            ->join('karyawan', 'karyawan.id_user = users.id', 'left')
            ->join('auth_groups_users', 'auth_groups_users.user_id = users.id', 'left')
            ->join('auth_groups', 'auth_groups.id = auth_groups_users.group_id', 'left')
            ->where('users.active', 1)
            ->orderBy('karyawan.nama_karyawan', 'ASC')
            ->orderBy('users.username', 'ASC')
            ->get()
            ->getResult();
    }

    public function getParentOptions(): array
    {
        $options = [(object) ['id' => 0, 'name' => 'Root']];
        $menus = $this->db->table('menus')
            ->select('id, name')
            ->where('parent_id', 0)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResult();

        foreach ($menus as $menu) {
            $options[] = $menu;
        }

        return $options;
    }

    public function getFlatMenus(bool $activeOnly = false): array
    {
        $builder = $this->db->table('menus')
            ->select('id, name, url, icon, slug, parent_id, is_active, sort_order')
            ->orderBy('parent_id', 'ASC')
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC');

        if ($activeOnly) {
            $builder->where('is_active', 1);
        }

        $menus = $builder->get()->getResult();
        $children = [];
        foreach ($menus as $menu) {
            $children[(int) $menu->parent_id][] = $menu;
        }

        $flat = [];
        $walk = function (int $parentId, int $depth) use (&$walk, &$flat, $children) {
            foreach ($children[$parentId] ?? [] as $menu) {
                $menu->depth = $depth;
                $flat[] = $menu;
                $walk((int) $menu->id, $depth + 1);
            }
        };
        $walk(0, 0);

        return $flat;
    }

    public function findMenuById(int $id): ?object
    {
        return $this->db->table('menus')
            ->where('id', $id)
            ->get()
            ->getRow();
    }

    public function insertMenu(array $data): int
    {
        $this->db->table('menus')->insert($data);
        return (int) $this->db->insertID();
    }

    public function updateMenu(int $id, array $data): bool
    {
        return $this->db->table('menus')->where('id', $id)->update($data);
    }

    public function groupExists(int $groupId): bool
    {
        return $groupId > 0 && $this->db->table('auth_groups')->where('id', $groupId)->countAllResults() > 0;
    }

    public function userExists(int $userId): bool
    {
        return $userId > 0 && $this->db->table('users')->where('id', $userId)->countAllResults() > 0;
    }

    public function isDescendant(int $menuId, int $parentId): bool
    {
        if ($parentId <= 0) {
            return false;
        }

        $current = $this->findMenuById($parentId);
        while ($current) {
            if ((int) $current->parent_id === $menuId) {
                return true;
            }

            if ((int) $current->parent_id <= 0) {
                return false;
            }

            $current = $this->findMenuById((int) $current->parent_id);
        }

        return false;
    }

    public function checkDuplicateSiteplanItemKey(string $itemKey, int $excludeId = 0): bool
    {
        $builder = $this->db->table('siteplan_menu_items')
            ->select('id')
            ->where('item_key', $itemKey);

        if ($excludeId > 0) {
            $builder->where('id !=', $excludeId);
        }

        return $builder->get()->getRow() !== null;
    }
}

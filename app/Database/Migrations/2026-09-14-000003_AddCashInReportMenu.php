<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCashInReportMenu extends Migration
{
    private const PARENT_SLUG = 'laporan';
    private const CHILD_URL = 'laporan/cash-in';
    private const ADMIN_GROUP_ID = 1;

    public function up()
    {
        if (! $this->db->tableExists('menus')) {
            return;
        }

        $parentId = $this->ensureParentMenu();
        $childId = $this->ensureChildMenu($parentId);

        $this->ensureAdminAccess($parentId);
        $this->ensureAdminAccess($childId);
    }

    public function down()
    {
        if (! $this->db->tableExists('menus')) {
            return;
        }

        $child = $this->db->table('menus')
            ->select('id, parent_id')
            ->where('url', self::CHILD_URL)
            ->get()
            ->getRow();

        if ($child) {
            $this->deleteAccessRows((int) $child->id);
            $this->db->table('menus')->where('id', (int) $child->id)->delete();
        }

        $parent = $this->db->table('menus')
            ->select('id')
            ->where('slug', self::PARENT_SLUG)
            ->where('parent_id', 0)
            ->where('url', '#')
            ->get()
            ->getRow();

        if (! $parent) {
            return;
        }

        $hasChildren = $this->db->table('menus')
            ->where('parent_id', (int) $parent->id)
            ->countAllResults() > 0;

        if (! $hasChildren) {
            $this->deleteAccessRows((int) $parent->id);
            $this->db->table('menus')->where('id', (int) $parent->id)->delete();
        }
    }

    private function ensureParentMenu(): int
    {
        $parent = $this->db->table('menus')
            ->select('id')
            ->groupStart()
                ->where('slug', self::PARENT_SLUG)
                ->orGroupStart()
                    ->where('name', 'Laporan')
                    ->where('parent_id', 0)
                ->groupEnd()
            ->groupEnd()
            ->get()
            ->getRow();

        if ($parent) {
            return (int) $parent->id;
        }

        $maxOrder = $this->db->table('menus')
            ->selectMax('sort_order')
            ->where('parent_id', 0)
            ->get()
            ->getRow();

        $now = date('Y-m-d H:i:s');
        $this->db->table('menus')->insert([
            'name' => 'Laporan',
            'url' => '#',
            'icon' => 'bar-chart-2',
            'slug' => self::PARENT_SLUG,
            'parent_id' => 0,
            'is_active' => 1,
            'sort_order' => ((int) ($maxOrder->sort_order ?? 0)) + 1,
            'date_add' => $now,
            'date_edit' => $now,
        ]);

        return (int) $this->db->insertID();
    }

    private function ensureChildMenu(int $parentId): int
    {
        $child = $this->db->table('menus')
            ->select('id')
            ->where('url', self::CHILD_URL)
            ->get()
            ->getRow();

        if ($child) {
            return (int) $child->id;
        }

        $maxOrder = $this->db->table('menus')
            ->selectMax('sort_order')
            ->where('parent_id', $parentId)
            ->get()
            ->getRow();

        $now = date('Y-m-d H:i:s');
        $this->db->table('menus')->insert([
            'name' => 'Cash In',
            'url' => self::CHILD_URL,
            'icon' => 'circle',
            'slug' => 'laporan-cash-in',
            'parent_id' => $parentId,
            'is_active' => 1,
            'sort_order' => ((int) ($maxOrder->sort_order ?? 0)) + 1,
            'date_add' => $now,
            'date_edit' => $now,
        ]);

        return (int) $this->db->insertID();
    }

    private function ensureAdminAccess(int $menuId): void
    {
        if ($menuId <= 0 || ! $this->db->tableExists('menu_roles')) {
            return;
        }

        $exists = $this->db->table('menu_roles')
            ->where('id_groups', self::ADMIN_GROUP_ID)
            ->where('id_menu', $menuId)
            ->countAllResults() > 0;

        if ($exists) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $this->db->table('menu_roles')->insert([
            'id_groups' => self::ADMIN_GROUP_ID,
            'id_menu' => $menuId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function deleteAccessRows(int $menuId): void
    {
        if ($this->db->tableExists('menu_roles')) {
            $this->db->table('menu_roles')->where('id_menu', $menuId)->delete();
        }

        if ($this->db->tableExists('menu_user_access')) {
            $this->db->table('menu_user_access')->where('id_menu', $menuId)->delete();
        }
    }
}

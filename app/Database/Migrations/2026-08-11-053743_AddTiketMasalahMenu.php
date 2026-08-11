<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTiketMasalahMenu extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('menus')) {
            return;
        }

        $menu = $this->db->table('menus')
            ->where('url', 'tiket-masalah')
            ->get()
            ->getRow();

        if (! $menu) {
            $keuanganMenu = $this->db->table('menus')->like('name', 'keuangan')->get()->getRow();
            $sortOrder = 100; // default safe fallback
            if ($keuanganMenu) {
                $sortOrder = (int)$keuanganMenu->sort_order + 1;
                // Shift existing menus down
                $this->db->query("UPDATE menus SET sort_order = sort_order + 1 WHERE sort_order >= ?", [$sortOrder]);
            } else {
                $maxOrder = $this->db->table('menus')->selectMax('sort_order')->get()->getRow();
                $sortOrder = ((int) ($maxOrder->sort_order ?? 0)) + 1;
            }

            $now = date('Y-m-d H:i:s');
            $this->db->table('menus')->insert([
                'name' => 'Tiket Masalah',
                'url' => 'tiket-masalah',
                'icon' => 'alert-circle',
                'slug' => 'tiket-masalah',
                'parent_id' => 0, // Menu Utama
                'is_active' => 1,
                'sort_order' => $sortOrder,
                'date_add' => $now,
                'date_edit' => $now,
            ]);
            $menuId = (int) $this->db->insertID();
        } else {
            $menuId = (int) $menu->id;
        }

        // Berikan akses ke semua role
        $groups = $this->db->table('auth_groups')->get()->getResult();
        foreach ($groups as $group) {
            $this->ensureRole($menuId, (int) $group->id);
        }
    }

    public function down()
    {
        if (! $this->db->tableExists('menus')) {
            return;
        }

        $menu = $this->db->table('menus')
            ->where('url', 'tiket-masalah')
            ->get()
            ->getRow();

        if (! $menu) {
            return;
        }

        if ($this->db->tableExists('menu_roles')) {
            $this->db->table('menu_roles')->where('id_menu', (int) $menu->id)->delete();
        }

        $this->db->table('menus')->where('id', (int) $menu->id)->delete();
    }

    private function ensureRole(int $menuId, int $groupId): void
    {
        if (! $this->db->tableExists('menu_roles')) {
            return;
        }

        $exists = $this->db->table('menu_roles')
            ->where('id_menu', $menuId)
            ->where('id_groups', $groupId)
            ->countAllResults();

        if ($exists > 0) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $this->db->table('menu_roles')->insert([
            'id_groups' => $groupId,
            'id_menu' => $menuId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}

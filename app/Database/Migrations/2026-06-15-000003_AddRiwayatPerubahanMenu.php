<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRiwayatPerubahanMenu extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('menus')) {
            return;
        }

        $menu = $this->db->table('menus')
            ->where('url', 'riwayat-perubahan')
            ->get()
            ->getRow();

        if (! $menu) {
            $maxOrder = $this->db->table('menus')->selectMax('sort_order')->get()->getRow();
            $now = date('Y-m-d H:i:s');
            $this->db->table('menus')->insert([
                'name' => 'Riwayat Perubahan',
                'url' => 'riwayat-perubahan',
                'icon' => 'clock',
                'slug' => 'riwayat-perubahan',
                'parent_id' => 0,
                'is_active' => 1,
                'sort_order' => ((int) ($maxOrder->sort_order ?? 0)) + 1,
                'date_add' => $now,
                'date_edit' => $now,
            ]);
            $menuId = (int) $this->db->insertID();
        } else {
            $menuId = (int) $menu->id;
        }

        $this->ensureRole($menuId, 1);
        $this->ensureRole($menuId, 9);
    }

    public function down()
    {
        if (! $this->db->tableExists('menus')) {
            return;
        }

        $menu = $this->db->table('menus')
            ->where('url', 'riwayat-perubahan')
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

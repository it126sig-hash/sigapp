<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tambah submenu "Hasil Akad" di bawah Keuangan (parent_id 20), mengarah ke
 * halaman list baru keuangan/hasil-akad/list. Role diberikan ke grup yang sama
 * dengan "Belum Lunas" (menu id 21): Admin, Umum, Keuangan, MKDT, Produksi, Sales, Direksi.
 */
class AddHasilAkadListMenu extends Migration
{
    private const URL = 'keuangan/hasil-akad/list';
    private const GROUPS = [1, 2, 3, 4, 7, 8, 9];

    public function up()
    {
        if (! $this->db->tableExists('menus')) {
            return;
        }

        $existing = $this->db->table('menus')->where('url', self::URL)->get()->getRow();
        if ($existing) {
            $menuId = (int) $existing->id;
        } else {
            $maxOrder = $this->db->table('menus')->selectMax('sort_order')->get()->getRow();
            $now = date('Y-m-d H:i:s');

            $this->db->table('menus')->insert([
                'name' => 'Hasil Akad',
                'url' => self::URL,
                'icon' => 'circle',
                'slug' => 'hasil-akad-list',
                'parent_id' => 20,
                'is_active' => 1,
                'sort_order' => ((int) ($maxOrder->sort_order ?? 0)) + 1,
                'date_add' => $now,
                'date_edit' => $now,
            ]);
            $menuId = (int) $this->db->insertID();
        }

        if (! $this->db->tableExists('menu_roles')) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        foreach (self::GROUPS as $groupId) {
            $exists = $this->db->table('menu_roles')
                ->where('id_groups', $groupId)
                ->where('id_menu', $menuId)
                ->countAllResults();

            if ($exists > 0) {
                continue;
            }

            $this->db->table('menu_roles')->insert([
                'id_groups' => $groupId,
                'id_menu' => $menuId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down()
    {
        if (! $this->db->tableExists('menus')) {
            return;
        }

        $menu = $this->db->table('menus')->where('url', self::URL)->get()->getRow();
        if (! $menu) {
            return;
        }

        if ($this->db->tableExists('menu_roles')) {
            $this->db->table('menu_roles')->where('id_menu', (int) $menu->id)->delete();
        }

        $this->db->table('menus')->where('id', (int) $menu->id)->delete();
    }
}

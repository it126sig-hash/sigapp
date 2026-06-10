<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSettingDataCrudSupport extends Migration
{
    public function up()
    {
        $this->ensureSoftDeleteColumns();
        $this->ensureSettingDataMenu();
    }

    public function down()
    {
        if ($this->db->tableExists('menus')) {
            $menu = $this->db->table('menus')->select('id')->where('url', 'setting-data')->get()->getRow();
            if ($menu) {
                if ($this->db->tableExists('menu_roles')) {
                    $this->db->table('menu_roles')->where('id_menu', (int) $menu->id)->delete();
                }
                if ($this->db->tableExists('menu_user_access')) {
                    $this->db->table('menu_user_access')->where('id_menu', (int) $menu->id)->delete();
                }
                $this->db->table('menus')->where('id', (int) $menu->id)->delete();
            }
        }
    }

    private function ensureSoftDeleteColumns(): void
    {
        foreach ($this->targetTables() as $table) {
            if (!$this->db->tableExists($table)) {
                continue;
            }

            $fields = $this->db->getFieldNames($table);
            if (in_array('deleted_at', $fields, true)) {
                continue;
            }

            $this->forge->addColumn($table, [
                'deleted_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
        }
    }

    private function ensureSettingDataMenu(): void
    {
        if (!$this->db->tableExists('menus')) {
            return;
        }

        $menu = $this->db->table('menus')->select('id')->where('url', 'setting-data')->get()->getRow();
        if (!$menu) {
            $parentId = $this->getDataMasterMenuId();
            $maxOrder = $this->db->table('menus')->selectMax('sort_order')->get()->getRow();
            $this->db->table('menus')->insert([
                'name' => 'Setting Data',
                'url' => 'setting-data',
                'icon' => 'settings',
                'slug' => 'setting-data',
                'parent_id' => $parentId,
                'is_active' => 1,
                'sort_order' => ((int) ($maxOrder->sort_order ?? 0)) + 1,
                'date_add' => date('Y-m-d H:i:s'),
                'date_edit' => date('Y-m-d H:i:s'),
            ]);
            $menuId = (int) $this->db->insertID();
        } else {
            $menuId = (int) $menu->id;
        }

        $this->ensureAdminMenuRole($menuId);
    }

    private function getDataMasterMenuId(): int
    {
        $menu = $this->db->table('menus')
            ->select('id')
            ->where('name', 'Data Master')
            ->get()
            ->getRow();

        return $menu ? (int) $menu->id : 0;
    }

    private function ensureAdminMenuRole(int $menuId): void
    {
        if ($menuId <= 0 || !$this->db->tableExists('menu_roles')) {
            return;
        }

        $exists = $this->db->table('menu_roles')
            ->where('id_groups', 1)
            ->where('id_menu', $menuId)
            ->countAllResults();

        if ($exists > 0) {
            return;
        }

        $this->db->table('menu_roles')->insert([
            'id_groups' => 1,
            'id_menu' => $menuId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function targetTables(): array
    {
        return [
            'list_bank',
            'list_bayar_produksi',
            'list_cashout',
            'list_dajam',
            'keuangan_item_list',
        ];
    }
}

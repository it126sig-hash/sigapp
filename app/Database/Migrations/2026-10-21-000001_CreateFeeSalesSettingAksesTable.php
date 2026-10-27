<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFeeSalesSettingAksesTable extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // 1. Buat tabel fee_sales_setting_akses
        if (! $this->db->tableExists('fee_sales_setting_akses')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'auto_increment' => true,
                ],
                'id_group' => [
                    'type'     => 'INT',
                    'null'     => false,
                    'unique'   => true,
                ],
                'can_view' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 1,
                    'null'       => false,
                ],
                'can_manage' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 0,
                    'null'       => false,
                ],
                'can_submit_spp' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 0,
                    'null'       => false,
                ],
                'can_cairkan' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 0,
                    'null'       => false,
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updated_by' => [
                    'type' => 'INT',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('fee_sales_setting_akses', true, [
                'ENGINE' => 'InnoDB',
                'DEFAULT CHARSET' => 'utf8mb4',
            ]);
        }

        // 2. Seed data permission per grup
        $groups = $db->table('auth_groups')->get()->getResultArray();
        $now = date('Y-m-d H:i:s');
        foreach ($groups as $group) {
            $groupId = (int) $group['id'];
            $exists = $db->table('fee_sales_setting_akses')->where('id_group', $groupId)->countAllResults();
            if ($exists === 0) {
                $canView = 0;
                $canManage = 0;
                $canSubmitSpp = 0;
                $canCairkan = 0;

                if ($groupId === 1) { // Admin
                    $canView = 1;
                    $canManage = 1;
                    $canSubmitSpp = 1;
                    $canCairkan = 1;
                } elseif ($groupId === 2) { // Umum
                    $canView = 1;
                } elseif ($groupId === 3) { // Keuangan
                    $canView = 1;
                    $canCairkan = 1;
                } elseif ($groupId === 4) { // MKDT
                    $canView = 1;
                    $canManage = 1;
                    $canSubmitSpp = 1;
                }

                $db->table('fee_sales_setting_akses')->insert([
                    'id_group'       => $groupId,
                    'can_view'       => $canView,
                    'can_manage'     => $canManage,
                    'can_submit_spp' => $canSubmitSpp,
                    'can_cairkan'    => $canCairkan,
                    'updated_at'     => $now,
                    'updated_by'     => 1,
                ]);
            }
        }

        // 3. Daftarkan menu di menus
        $existingMenu = $db->table('menus')->where('url', 'setting-fee-sales')->get()->getRow();
        if (! $existingMenu) {
            $db->table('menus')->insert([
                'name'       => 'Setting Akses Fee Sales',
                'url'        => 'setting-fee-sales',
                'icon'       => 'shield',
                'slug'       => 'setting-fee-sales',
                'parent_id'  => 2,
                'is_active'  => 1,
                'sort_order' => 36,
            ]);
            $menuId = $db->insertID();

            // Menu role untuk Admin (id_groups = 1)
            $db->table('menu_roles')->insert([
                'id_groups' => 1,
                'id_menu'   => $menuId,
            ]);
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();

        $menu = $db->table('menus')->where('url', 'setting-fee-sales')->get()->getRow();
        if ($menu) {
            $db->table('menu_roles')->where('id_menu', $menu->id)->delete();
            $db->table('menus')->where('id', $menu->id)->delete();
        }

        $this->forge->dropTable('fee_sales_setting_akses', true);
    }
}

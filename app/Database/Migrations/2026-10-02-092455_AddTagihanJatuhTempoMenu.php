<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTagihanJatuhTempoMenu extends Migration
{
    public function up()
    {
        // Add to menus table
        $data = [
            'name'       => 'Tagihan Jatuh Tempo',
            'url'        => 'tagihan/jatuh-tempo',
            'icon'       => 'fas fa-exclamation-triangle', // or another icon
            'slug'       => 'tagihan-jatuh-tempo',
            'parent_id'  => 20, // Keuangan
            'is_active'  => 1,
            'sort_order' => 2, // Belum Lunas is likely 1
            'date_add'   => date('Y-m-d H:i:s'),
        ];
        $this->db->table('menus')->insert($data);
        $menuId = $this->db->insertID();

        // Add to menu_roles
        // Groups: 1, 2, 3, 4, 7, 8, 9
        $roles = [1, 2, 3, 4, 7, 8, 9];
        $roleData = [];
        foreach ($roles as $role) {
            $roleData[] = [
                'id_groups'  => $role,
                'id_menu'    => $menuId,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
        }
        $this->db->table('menu_roles')->insertBatch($roleData);
    }

    public function down()
    {
        // Retrieve the menu
        $menu = $this->db->table('menus')->where('url', 'tagihan/jatuh-tempo')->get()->getRow();
        
        if ($menu) {
            $this->db->table('menu_roles')->where('id_menu', $menu->id)->delete();
            $this->db->table('menus')->where('id', $menu->id)->delete();
        }
    }
}

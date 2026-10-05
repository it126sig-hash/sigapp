<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDireksiTagihanMenu extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        
        // Cek jika menu Direksi sudah ada (sebagai parent)
        $parentMenu = $db->table('menus')->where('name', 'Direksi')->get()->getRow();
        $parentId = 0;
        
        if (!$parentMenu) {
            $db->table('menus')->insert([
                'name'       => 'Direksi',
                'url'        => '#',
                'icon'       => 'briefcase',
                'parent_id'  => 0,
                'is_active'  => 1,
                'sort_order' => 50,
            ]);
            $parentId = $db->insertID();
            
            // Beri akses ke Admin (1) dan Direksi (9)
            $db->table('menu_roles')->insertBatch([
                ['id_groups' => 1, 'id_menu' => $parentId],
                ['id_groups' => 9, 'id_menu' => $parentId],
            ]);
        } else {
            $parentId = $parentMenu->id;
        }

        // Cek jika menu Surat Tagihan sudah ada
        $childMenu = $db->table('menus')->where('url', 'direksi/tagihan')->get()->getRow();
        if (!$childMenu) {
            $db->table('menus')->insert([
                'name'       => 'Persetujuan Tagihan',
                'url'        => 'direksi/tagihan',
                'icon'       => 'circle',
                'parent_id'  => $parentId,
                'is_active'  => 1,
                'sort_order' => 1,
            ]);
            $childId = $db->insertID();
            
            // Beri akses
            $db->table('menu_roles')->insertBatch([
                ['id_groups' => 1, 'id_menu' => $childId],
                ['id_groups' => 9, 'id_menu' => $childId],
            ]);
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();
        $childMenu = $db->table('menus')->where('url', 'direksi/tagihan')->get()->getRow();
        if ($childMenu) {
            $db->table('menu_roles')->where('id_menu', $childMenu->id)->delete();
            $db->table('menus')->where('id', $childMenu->id)->delete();
        }
    }
}

<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPlanningDetailSiteplanMenu extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('siteplan_menu_items') || !$this->db->tableExists('siteplan_menu_roles')) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $row = [
            'id_group'    => 6,
            'item_key'    => 'planning_lihat_detail',
            'label'       => 'Lihat Detail',
            'group_label' => 'Data',
            'onclick'     => 'lihat_detail()',
            'icon'        => 'fas fa-eye',
            'btn_class'   => 'btn-info',
            'sort_order'  => 25,
            'is_active'   => 1,
            'extra_id'    => '',
            'extra_class' => '',
            'updated_at'  => $now,
        ];

        $existing = $this->db->table('siteplan_menu_items')
            ->select('id')
            ->where('item_key', 'planning_lihat_detail')
            ->get()
            ->getRow();

        if ($existing) {
            $this->db->table('siteplan_menu_items')
                ->where('id', (int) $existing->id)
                ->update($row);
            $itemId = (int) $existing->id;
        } else {
            $row['created_at'] = $now;
            $this->db->table('siteplan_menu_items')->insert($row);
            $itemId = (int) $this->db->insertID();
        }

        $roleExists = $this->db->table('siteplan_menu_roles')
            ->where('id_group', 6)
            ->where('id_siteplan_menu_item', $itemId)
            ->countAllResults();

        if ($roleExists == 0) {
            $this->db->table('siteplan_menu_roles')->insert([
                'id_group'              => 6,
                'id_siteplan_menu_item' => $itemId,
                'created_at'            => $now,
                'updated_at'            => $now,
            ]);
        }
    }

    public function down()
    {
        if (!$this->db->tableExists('siteplan_menu_items') || !$this->db->tableExists('siteplan_menu_roles')) {
            return;
        }

        $item = $this->db->table('siteplan_menu_items')
            ->select('id')
            ->where('item_key', 'planning_lihat_detail')
            ->get()
            ->getRow();

        if (!$item) {
            return;
        }

        $this->db->table('siteplan_menu_roles')
            ->where('id_siteplan_menu_item', (int) $item->id)
            ->delete();
        $this->db->table('siteplan_menu_items')
            ->where('id', (int) $item->id)
            ->delete();
    }
}

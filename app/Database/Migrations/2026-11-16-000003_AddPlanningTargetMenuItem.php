<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPlanningTargetMenuItem extends Migration
{
    private const ITEM_KEY = 'planning_target_request';

    public function up()
    {
        if (! $this->db->tableExists('siteplan_menu_items') || ! $this->db->tableExists('siteplan_menu_roles')) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $row = [
            'id_group'    => 6,
            'item_key'    => self::ITEM_KEY,
            'label'       => 'Target Siteplan',
            'group_label' => 'Target',
            'onclick'     => 'open_target_request()',
            'icon'        => 'fas fa-bullseye',
            'btn_class'   => 'btn-warning',
            'sort_order'  => 85,
            'is_active'   => 1,
            'extra_id'    => 'planning-target-request-btn',
            'extra_class' => '',
            'updated_at'  => $now,
        ];

        $existing = $this->db->table('siteplan_menu_items')
            ->select('id')
            ->where('item_key', self::ITEM_KEY)
            ->get()
            ->getRow();

        if ($existing) {
            $itemId = (int) $existing->id;
            $this->db->table('siteplan_menu_items')
                ->where('id', $itemId)
                ->update($row);
        } else {
            $row['created_at'] = $now;
            $this->db->table('siteplan_menu_items')->insert($row);
            $itemId = (int) $this->db->insertID();
        }

        foreach ([6, 1] as $groupId) {
            $roleExists = $this->db->table('siteplan_menu_roles')
                ->where('id_group', $groupId)
                ->where('id_siteplan_menu_item', $itemId)
                ->countAllResults();

            if ($roleExists === 0) {
                $this->db->table('siteplan_menu_roles')->insert([
                    'id_group'              => $groupId,
                    'id_siteplan_menu_item' => $itemId,
                    'created_at'            => $now,
                    'updated_at'            => $now,
                ]);
            }
        }
    }

    public function down()
    {
        if (! $this->db->tableExists('siteplan_menu_items') || ! $this->db->tableExists('siteplan_menu_roles')) {
            return;
        }

        $item = $this->db->table('siteplan_menu_items')
            ->select('id')
            ->where('item_key', self::ITEM_KEY)
            ->get()
            ->getRow();

        if (! $item) {
            return;
        }

        $itemId = (int) $item->id;

        $this->db->table('siteplan_menu_roles')
            ->where('id_siteplan_menu_item', $itemId)
            ->delete();

        $this->db->table('siteplan_menu_items')
            ->where('id', $itemId)
            ->delete();
    }
}

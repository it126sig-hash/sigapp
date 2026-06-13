<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateCashoutSubkonMenuAction extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('siteplan_menu_items')) {
            return;
        }

        $this->db->table('siteplan_menu_items')
            ->where('item_key', 'keuangan_cash_out_subkon')
            ->update(['onclick' => 'openCOSubkon()']);
    }

    public function down()
    {
        if (!$this->db->tableExists('siteplan_menu_items')) {
            return;
        }

        $this->db->table('siteplan_menu_items')
            ->where('item_key', 'keuangan_cash_out_subkon')
            ->update(['onclick' => 'openCOSubkon(1)']);
    }
}

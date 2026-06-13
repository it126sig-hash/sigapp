<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCashoutSubkonDetailAllocationTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('cashout_subkon_detail_allocation')) {
            return;
        }

        $this->forge->addField([
            'id_cashout_subkon_detail_allocation' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_cashout_subkon_detail' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'id_cashout_subkon' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'id_kavling' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'nominal' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
            ],
            'allocation_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'auto_equal',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'add_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'edit_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
        ]);

        $this->forge->addKey('id_cashout_subkon_detail_allocation', true);
        $this->forge->addKey('id_cashout_subkon_detail');
        $this->forge->addKey('id_cashout_subkon');
        $this->forge->addKey('id_kavling');
        $this->forge->addUniqueKey(['id_cashout_subkon_detail', 'id_kavling'], 'cashout_subkon_detail_allocation_unique');
        $this->forge->createTable('cashout_subkon_detail_allocation', true);
    }

    public function down()
    {
        $this->forge->dropTable('cashout_subkon_detail_allocation', true);
    }
}

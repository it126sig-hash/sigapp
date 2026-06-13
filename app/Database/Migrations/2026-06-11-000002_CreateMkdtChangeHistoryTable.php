<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMkdtChangeHistoryTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('mkdt_change_history')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_kavling' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'id_mkdt' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'action' => [
                'type'       => 'VARCHAR',
                'constraint' => 80,
            ],
            'summary' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'old_data' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'new_data' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'add_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('id_kavling');
        $this->forge->addKey('id_mkdt');
        $this->forge->addKey('action');
        $this->forge->addKey('add_by');
        $this->forge->createTable('mkdt_change_history', true);
    }

    public function down()
    {
        if ($this->db->tableExists('mkdt_change_history')) {
            $this->forge->dropTable('mkdt_change_history', true);
        }
    }
}

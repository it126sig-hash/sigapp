<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProduksiJalanProgressHistoryTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('produksi_jalan_progress_history')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_others' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'progres' => [
                'type'       => 'INT',
                'constraint' => 3,
                'default'    => 0,
            ],
            'produksi_luas' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'keterangan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'foto' => [
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
        $this->forge->addKey('id_others');
        $this->forge->addKey('add_by');
        $this->forge->createTable('produksi_jalan_progress_history', true);
    }

    public function down()
    {
        $this->forge->dropTable('produksi_jalan_progress_history', true);
    }
}

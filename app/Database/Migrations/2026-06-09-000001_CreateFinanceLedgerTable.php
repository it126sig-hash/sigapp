<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFinanceLedgerTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'direction' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'source_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'source_id' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'source_detail_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'id_mkdt' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'id_kavling' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'nominal' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
            ],
            'tanggal_transaksi' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'label' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'keterangan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'active',
            ],
            'is_deleted' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'add_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'edit_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['direction', 'tanggal_transaksi']);
        $this->forge->addKey('id_mkdt');
        $this->forge->addKey('id_kavling');
        $this->forge->addUniqueKey(['source_type', 'source_id'], 'finance_ledger_source_unique');
        $this->forge->createTable('finance_ledger', true);
    }

    public function down()
    {
        $this->forge->dropTable('finance_ledger', true);
    }
}

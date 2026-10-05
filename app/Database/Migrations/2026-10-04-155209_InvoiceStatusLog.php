<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class InvoiceStatusLog extends Migration
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
            'no_inv' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
            ],
            'tanggal' => [
                'type' => 'DATE',
            ],
            'keterangan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'add_by' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'date_add' => [
                'type' => 'DATETIME',
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('no_inv');
        $this->forge->createTable('invoice_status_log');
    }

    public function down()
    {
        $this->forge->dropTable('invoice_status_log');
    }
}

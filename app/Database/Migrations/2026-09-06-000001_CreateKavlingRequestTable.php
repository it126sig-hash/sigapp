<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateKavlingRequestTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_request' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_proyek' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'jenis_request' => [
                'type'       => 'ENUM',
                'constraint' => ['tambah_baru', 'ubah_tipe'],
                'default'    => 'tambah_baru',
            ],
            'id_kavling' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
            ],
            'id_cluster' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
            ],
            'id_jalan' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
            ],
            'id_tipe' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
            ],
            'keterangan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'approved', 'rejected'],
                'default'    => 'pending',
            ],
            'created_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id_request', true);
        $this->forge->addKey('id_proyek');
        $this->forge->addKey('id_kavling');
        $this->forge->addKey('status');
        $this->forge->addKey('created_by');

        $this->forge->createTable('kavling_request', true);
    }

    public function down()
    {
        $this->forge->dropTable('kavling_request', true);
    }
}

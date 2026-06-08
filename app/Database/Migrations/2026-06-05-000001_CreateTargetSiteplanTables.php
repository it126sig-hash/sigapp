<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTargetSiteplanTables extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_target' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_proyek' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'tahun_target' => [
                'type'       => 'INT',
                'constraint' => 4,
            ],
            'deskripsi' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'add_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'edit_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id_target', true);
        $this->forge->addKey(['id_proyek', 'tahun_target']);
        $this->forge->createTable('target_siteplan', true);

        $this->forge->addField([
            'id_target_kavling' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_target' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'id_kavling' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id_target_kavling', true);
        $this->forge->addKey(['id_target', 'id_kavling']);
        $this->forge->createTable('target_siteplan_kavling', true);

        $this->forge->addField([
            'id_target_history' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_target' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'aksi' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'deskripsi' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'snapshot' => [
                'type' => 'LONGTEXT',
                'null' => true,
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
        ]);
        $this->forge->addKey('id_target_history', true);
        $this->forge->addKey('id_target');
        $this->forge->createTable('target_siteplan_history', true);
    }

    public function down()
    {
        $this->forge->dropTable('target_siteplan_history', true);
        $this->forge->dropTable('target_siteplan_kavling', true);
        $this->forge->dropTable('target_siteplan', true);
    }
}

<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDanaJaminanModuleTables extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('id_mkdt', 'riwayat_pencairan_jaminan')) {
            $this->forge->addColumn('riwayat_pencairan_jaminan', [
                'id_mkdt' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'null'       => true,
                    'after'      => 'id_kavling',
                ],
            ]);
        }

        if ($this->db->fieldExists('surat_path', 'riwayat_pencairan_jaminan')) {
            $this->forge->modifyColumn('riwayat_pencairan_jaminan', [
                'surat_path' => [
                    'name' => 'surat_path',
                    'type' => 'TEXT',
                    'null' => true,
                ],
            ]);
        }

        if (! $this->db->tableExists('riwayat_pencairan_jaminan_detail')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'id_pengajuan' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'id_dana_akad' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'null'       => true,
                ],
                'id_list_dajam' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'null'       => true,
                ],
                'nominal_pengajuan' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'default'    => 0,
                ],
                'nominal_cair' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'null'       => true,
                ],
                'tanggal_cair' => [
                    'type' => 'DATE',
                    'null' => true,
                ],
                'keterangan_cair' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'status_cair' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 0,
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
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('id_pengajuan');
            $this->forge->addKey('id_dana_akad');
            $this->forge->createTable('riwayat_pencairan_jaminan_detail', true);
        }

        if (! $this->db->tableExists('dana_jaminan_history')) {
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
                    'null'       => true,
                ],
                'id_mkdt' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'null'       => true,
                ],
                'id_dana_akad' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'null'       => true,
                ],
                'id_pengajuan' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'null'       => true,
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
            $this->forge->addKey('id', true);
            $this->forge->addKey('id_kavling');
            $this->forge->addKey('id_mkdt');
            $this->forge->addKey('id_pengajuan');
            $this->forge->createTable('dana_jaminan_history', true);
        }
    }

    public function down()
    {
        $this->forge->dropTable('dana_jaminan_history', true);
        $this->forge->dropTable('riwayat_pencairan_jaminan_detail', true);

        if ($this->db->fieldExists('id_mkdt', 'riwayat_pencairan_jaminan')) {
            $this->forge->dropColumn('riwayat_pencairan_jaminan', 'id_mkdt');
        }
    }
}

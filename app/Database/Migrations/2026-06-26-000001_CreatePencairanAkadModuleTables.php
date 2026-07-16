<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePencairanAkadModuleTables extends Migration
{
    public function up()
    {
        $this->createPlanTable();
        $this->createItemTable();
        $this->createPengajuanTable();
        $this->createPengajuanDetailTable();
        $this->createPaymentTable();
        $this->createPaymentDetailTable();
    }

    public function down()
    {
        $this->forge->dropTable('pencairan_akad_payment_detail', true);
        $this->forge->dropTable('pencairan_akad_payment', true);
        $this->forge->dropTable('pencairan_akad_pengajuan_detail', true);
        $this->forge->dropTable('pencairan_akad_pengajuan', true);
        $this->forge->dropTable('pencairan_akad_item', true);
        $this->forge->dropTable('pencairan_akad_plan', true);
    }

    private function createPlanTable(): void
    {
        if ($this->db->tableExists('pencairan_akad_plan')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_mkdt' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'id_kavling' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'harga_kpr_acc' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
            ],
            'total_retensi' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
            ],
            'total_hasil_akad' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'draft',
            ],
            'legacy_source_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'legacy_source_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
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
        $this->forge->addUniqueKey('id_mkdt', 'uniq_pencairan_akad_plan_mkdt');
        $this->forge->addKey('id_kavling', false, false, 'idx_pencairan_akad_plan_kavling');
        $this->forge->createTable('pencairan_akad_plan', true);
    }

    private function createItemTable(): void
    {
        if ($this->db->tableExists('pencairan_akad_item')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_plan' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'jenis' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'comment'    => 'retensi|tenor',
            ],
            'id_list_dajam' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'urutan_tenor' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'nominal' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
            ],
            'catatan' => [
                'type' => 'TEXT',
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
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('id_plan', false, false, 'idx_pencairan_akad_item_plan');
        $this->forge->addKey('id_list_dajam', false, false, 'idx_pencairan_akad_item_dajam');
        $this->forge->createTable('pencairan_akad_item', true);
    }

    private function createPengajuanTable(): void
    {
        if ($this->db->tableExists('pencairan_akad_pengajuan')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_plan' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'tanggal_pengajuan' => [
                'type' => 'DATE',
            ],
            'tanggal_rencana_cair' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'catatan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'lampiran_surat' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'total_pengajuan' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
            ],
            'total_cair' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'active',
                'comment'    => 'active|partial|paid|void',
            ],
            'void_reason' => [
                'type' => 'TEXT',
                'null' => true,
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
        $this->forge->addKey('id_plan', false, false, 'idx_pencairan_akad_pengajuan_plan');
        $this->forge->addKey('status', false, false, 'idx_pencairan_akad_pengajuan_status');
        $this->forge->addKey('tanggal_rencana_cair', false, false, 'idx_pencairan_akad_pengajuan_rencana');
        $this->forge->createTable('pencairan_akad_pengajuan', true);
    }

    private function createPengajuanDetailTable(): void
    {
        if ($this->db->tableExists('pencairan_akad_pengajuan_detail')) {
            return;
        }

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
            'id_item' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'nominal_pengajuan' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
            ],
            'nominal_cair' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
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
        $this->forge->addKey('id_pengajuan', false, false, 'idx_pencairan_akad_pengajuan_detail_pengajuan');
        $this->forge->addKey('id_item', false, false, 'idx_pencairan_akad_pengajuan_detail_item');
        $this->forge->createTable('pencairan_akad_pengajuan_detail', true);
    }

    private function createPaymentTable(): void
    {
        if ($this->db->tableExists('pencairan_akad_payment')) {
            return;
        }

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
            'tanggal_cair' => [
                'type' => 'DATE',
            ],
            'catatan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'total_cair' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
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
        $this->forge->addKey('id_pengajuan', false, false, 'idx_pencairan_akad_payment_pengajuan');
        $this->forge->createTable('pencairan_akad_payment', true);
    }

    private function createPaymentDetailTable(): void
    {
        if ($this->db->tableExists('pencairan_akad_payment_detail')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_payment' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'id_pengajuan_detail' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'nominal_cair' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('id_payment', false, false, 'idx_pencairan_akad_payment_detail_payment');
        $this->forge->addKey('id_pengajuan_detail', false, false, 'idx_pencairan_akad_payment_detail_pengajuan_detail');
        $this->forge->createTable('pencairan_akad_payment_detail', true);
    }
}

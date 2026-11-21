<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFeeSalesPengajuanTables extends Migration
{
    public function up()
    {
        // 1. Tambah kolom no_cek pada fee_sales_termin jika belum ada
        if ($this->db->tableExists('fee_sales_termin')) {
            if (! $this->db->fieldExists('no_cek', 'fee_sales_termin')) {
                $this->forge->addColumn('fee_sales_termin', [
                    'no_cek' => [
                        'type'       => 'VARCHAR',
                        'constraint' => 100,
                        'null'       => true,
                        'after'      => 'tanggal_cair',
                    ],
                ]);
            }
        }

        // 2. Tabel Header Pengajuan Kelompok Fee Sales
        if (! $this->db->tableExists('fee_sales_pengajuan')) {
            $this->forge->addField([
                'id'                => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'id_proyek'         => ['type' => 'INT', 'constraint' => 11, 'null' => true],
                'nomor_pengajuan'   => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
                'tanggal_pengajuan' => ['type' => 'DATE'],
                'catatan'           => ['type' => 'TEXT', 'null' => true],
                'lampiran_spp'      => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'status'            => ['type' => 'ENUM', 'constraint' => ['diajukan', 'cair_sebagian', 'selesai'], 'default' => 'diajukan'],
                'created_by'        => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'updated_by'        => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'created_at'        => ['type' => 'DATETIME', 'null' => true],
                'updated_at'        => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('id_proyek');
            $this->forge->addKey('tanggal_pengajuan');
            $this->forge->createTable('fee_sales_pengajuan', true);
        }

        // 3. Tabel Detail Item Pengajuan Kelompok
        if (! $this->db->tableExists('fee_sales_pengajuan_item')) {
            $this->forge->addField([
                'id'           => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'id_pengajuan' => ['type' => 'INT', 'unsigned' => true],
                'id_fee_sales' => ['type' => 'INT', 'unsigned' => true],
                'id_termin'    => ['type' => 'INT', 'unsigned' => true],
                'termin_ke'    => ['type' => 'TINYINT', 'unsigned' => true],
                'nominal'      => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0.00],
                'created_at'   => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('id_pengajuan');
            $this->forge->addKey('id_termin');
            $this->forge->addForeignKey('id_pengajuan', 'fee_sales_pengajuan', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('fee_sales_pengajuan_item', true);
        }
    }

    public function down()
    {
        $this->forge->dropTable('fee_sales_pengajuan_item', true);
        $this->forge->dropTable('fee_sales_pengajuan', true);

        if ($this->db->tableExists('fee_sales_termin') && $this->db->fieldExists('no_cek', 'fee_sales_termin')) {
            $this->forge->dropColumn('fee_sales_termin', 'no_cek');
        }
    }
}

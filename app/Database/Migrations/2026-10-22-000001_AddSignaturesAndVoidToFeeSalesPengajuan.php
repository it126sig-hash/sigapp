<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSignaturesAndVoidToFeeSalesPengajuan extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('fee_sales_pengajuan')) {
            return;
        }

        // 1. Ubah enum status untuk menyertakan 'void'
        $this->db->query("ALTER TABLE fee_sales_pengajuan MODIFY COLUMN status ENUM('diajukan', 'cair_sebagian', 'selesai', 'void') NOT NULL DEFAULT 'diajukan'");

        // 2. Tambah kolom-kolom baru jika belum ada
        $fieldsToAdd = [];

        if (! $this->db->fieldExists('share_token', 'fee_sales_pengajuan')) {
            $fieldsToAdd['share_token'] = [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
                'after'      => 'id_proyek',
            ];
        }

        if (! $this->db->fieldExists('ttd_pembuat_by', 'fee_sales_pengajuan')) {
            $fieldsToAdd['ttd_pembuat_by'] = [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ];
            $fieldsToAdd['ttd_pembuat_nama'] = [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ];
            $fieldsToAdd['ttd_pembuat_path'] = [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ];
            $fieldsToAdd['ttd_pembuat_method'] = [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ];
            $fieldsToAdd['ttd_pembuat_at'] = [
                'type' => 'DATETIME',
                'null' => true,
            ];
        }

        if (! $this->db->fieldExists('ttd_mkdt_by', 'fee_sales_pengajuan')) {
            $fieldsToAdd['ttd_mkdt_by'] = [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ];
            $fieldsToAdd['ttd_mkdt_nama'] = [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ];
            $fieldsToAdd['ttd_mkdt_path'] = [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ];
            $fieldsToAdd['ttd_mkdt_method'] = [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ];
            $fieldsToAdd['ttd_mkdt_at'] = [
                'type' => 'DATETIME',
                'null' => true,
            ];
        }

        if (! $this->db->fieldExists('ttd_keuangan_by', 'fee_sales_pengajuan')) {
            $fieldsToAdd['ttd_keuangan_by'] = [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ];
            $fieldsToAdd['ttd_keuangan_nama'] = [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ];
            $fieldsToAdd['ttd_keuangan_path'] = [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ];
            $fieldsToAdd['ttd_keuangan_method'] = [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ];
            $fieldsToAdd['ttd_keuangan_at'] = [
                'type' => 'DATETIME',
                'null' => true,
            ];
        }

        if (! $this->db->fieldExists('voided_by', 'fee_sales_pengajuan')) {
            $fieldsToAdd['voided_by'] = [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ];
            $fieldsToAdd['voided_at'] = [
                'type' => 'DATETIME',
                'null' => true,
            ];
            $fieldsToAdd['void_reason'] = [
                'type' => 'TEXT',
                'null' => true,
            ];
        }

        if (! empty($fieldsToAdd)) {
            $this->forge->addColumn('fee_sales_pengajuan', $fieldsToAdd);
        }

        // Backfill share_token untuk data existing yang belum punya token
        $rows = $this->db->table('fee_sales_pengajuan')
            ->select('id')
            ->where('share_token IS NULL OR share_token = ""', null, false)
            ->get()
            ->getResult();

        foreach ($rows as $row) {
            $token = bin2hex(random_bytes(16));
            $this->db->table('fee_sales_pengajuan')
                ->where('id', $row->id)
                ->update(['share_token' => $token]);
        }
    }

    public function down()
    {
        if (! $this->db->tableExists('fee_sales_pengajuan')) {
            return;
        }

        $colsToDrop = [
            'share_token',
            'ttd_pembuat_by', 'ttd_pembuat_nama', 'ttd_pembuat_path', 'ttd_pembuat_method', 'ttd_pembuat_at',
            'ttd_mkdt_by', 'ttd_mkdt_nama', 'ttd_mkdt_path', 'ttd_mkdt_method', 'ttd_mkdt_at',
            'ttd_keuangan_by', 'ttd_keuangan_nama', 'ttd_keuangan_path', 'ttd_keuangan_method', 'ttd_keuangan_at',
            'voided_by', 'voided_at', 'void_reason',
        ];

        foreach ($colsToDrop as $col) {
            if ($this->db->fieldExists($col, 'fee_sales_pengajuan')) {
                $this->forge->dropColumn('fee_sales_pengajuan', $col);
            }
        }

        $this->db->query("ALTER TABLE fee_sales_pengajuan MODIFY COLUMN status ENUM('diajukan', 'cair_sebagian', 'selesai') NOT NULL DEFAULT 'diajukan'");
    }
}

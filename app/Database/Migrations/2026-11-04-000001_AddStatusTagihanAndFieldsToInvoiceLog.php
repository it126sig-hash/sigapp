<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddStatusTagihanAndFieldsToInvoiceLog extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('invoice_log')) {
            return;
        }

        $fieldsToAdd = [];

        if (! $this->db->fieldExists('status_tagihan', 'invoice_log')) {
            $fieldsToAdd['status_tagihan'] = [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'draft',
                'null'       => false,
                'after'      => 'terms',
            ];
        }

        if (! $this->db->fieldExists('tanggal_ubah_status', 'invoice_log')) {
            $fieldsToAdd['tanggal_ubah_status'] = [
                'type'  => 'DATE',
                'null'  => true,
                'after' => 'status_tagihan',
            ];
        }

        if (! $this->db->fieldExists('keterangan_status', 'invoice_log')) {
            $fieldsToAdd['keterangan_status'] = [
                'type'  => 'TEXT',
                'null'  => true,
                'after' => 'tanggal_ubah_status',
            ];
        }

        if (! $this->db->fieldExists('tanggal_kirim', 'invoice_log')) {
            $fieldsToAdd['tanggal_kirim'] = [
                'type'  => 'DATETIME',
                'null'  => true,
                'after' => 'keterangan_status',
            ];
        }

        if (! empty($fieldsToAdd)) {
            $this->forge->addColumn('invoice_log', $fieldsToAdd);
        }

        $this->db->resetDataCache();

        // Backfill data existing jika status_tagihan IS NULL OR status_tagihan = ''
        $this->db->table('invoice_log')
            ->where('status_tagihan IS NULL OR status_tagihan = \'\'', null, false)
            ->update(['status_tagihan' => 'draft']);

        // Tambahkan index idx_invoice_log_status_tagihan pada status_tagihan jika belum ada
        if (! $this->indexExists('invoice_log', 'idx_invoice_log_status_tagihan')) {
            $this->db->query("ALTER TABLE `invoice_log` ADD INDEX `idx_invoice_log_status_tagihan` (`status_tagihan`)");
        }
    }

    public function down()
    {
        if (! $this->db->tableExists('invoice_log')) {
            return;
        }

        // Drop index idx_invoice_log_status_tagihan jika ada
        if ($this->indexExists('invoice_log', 'idx_invoice_log_status_tagihan')) {
            $this->db->query("ALTER TABLE `invoice_log` DROP INDEX `idx_invoice_log_status_tagihan`");
        }

        // Drop kolom status_tagihan, tanggal_ubah_status, keterangan_status, tanggal_kirim jika ada
        $colsToDrop = [
            'tanggal_kirim',
            'keterangan_status',
            'tanggal_ubah_status',
            'status_tagihan',
        ];

        foreach ($colsToDrop as $col) {
            if ($this->db->fieldExists($col, 'invoice_log')) {
                $this->forge->dropColumn('invoice_log', $col);
            }
        }

        $this->db->resetDataCache();
    }

    private function indexExists(string $table, string $index): bool
    {
        $rows = $this->db->query("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$index])->getResult();

        return count($rows) > 0;
    }
}

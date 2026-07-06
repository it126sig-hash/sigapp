<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSiteplanPerformanceIndexes extends Migration
{
    private array $indexes = [
        ['cluster', 'idx_cluster_id_proyek', ['id_proyek']],
        ['jalan', 'idx_jalan_id_cluster', ['id_cluster']],
        ['kavling', 'idx_kavling_id_jalan', ['id_jalan']],
        ['kavling', 'idx_kavling_id_mkdt', ['id_mkdt']],
        ['kavling', 'idx_kavling_id_tipe', ['id_tipe']],
        ['kavling', 'idx_kavling_harga_akhir', ['harga_akhir']],
        ['kavling', 'idx_kavling_id_legal', ['id_legal']],
        ['kavling', 'idx_kavling_id_pajak', ['id_pajak']],
        ['kavling', 'idx_kavling_id_produksi', ['id_produksi']],
        ['mkdt', 'idx_mkdt_id_kavling', ['id_kavling']],
        ['mkdt', 'idx_mkdt_id_konsumen', ['id_konsumen']],
        ['mkdt', 'idx_mkdt_sp3k_urgent', ['sp3k_tgl_exp', 'akad_tgl', 'is_batal']],
        ['mkdt', 'idx_mkdt_rencana_akad_urgent', ['rencana_akad_tgl', 'akad_tgl', 'is_batal']],
        ['keuangan', 'idx_keuangan_due_status_mkdt', ['sudah_dibayar', 'jatuh_tempo_tgl', 'id_mkdt']],
        ['others', 'idx_others_jalan_scope', ['id_jalan', 'scope']],
        ['cashout_subkon_detail', 'idx_csd_subkon_status_paid_due', ['id_cashout_subkon', 'status', 'is_paid', 'tanggal_jatuh_tempo']],
        ['cashout_subkon_kavling', 'idx_csk_subkon_kavling', ['id_cashout_subkon', 'id_kavling']],
        ['notification', 'idx_notification_read_kavling_created', ['is_read', 'id_kavling', 'created_at']],
    ];

    public function up()
    {
        foreach ($this->indexes as [$table, $index, $columns]) {
            $this->addIndexIfMissing($table, $index, $columns);
        }
    }

    public function down()
    {
        foreach (array_reverse($this->indexes) as [$table, $index]) {
            $this->dropIndexIfExists($table, $index);
        }
    }

    private function addIndexIfMissing(string $table, string $index, array $columns): void
    {
        if (!$this->tableHasColumns($table, $columns) || $this->indexExists($table, $index)) {
            return;
        }

        $columnList = implode('`, `', $columns);
        $this->db->query("ALTER TABLE `{$table}` ADD INDEX `{$index}` (`{$columnList}`)");
    }

    private function dropIndexIfExists(string $table, string $index): void
    {
        if (!$this->db->tableExists($table) || !$this->indexExists($table, $index)) {
            return;
        }

        $this->db->query("ALTER TABLE `{$table}` DROP INDEX `{$index}`");
    }

    private function tableHasColumns(string $table, array $columns): bool
    {
        if (!$this->db->tableExists($table)) {
            return false;
        }

        $fields = $this->db->getFieldNames($table);
        foreach ($columns as $column) {
            if (!in_array($column, $fields, true)) {
                return false;
            }
        }

        return true;
    }

    private function indexExists(string $table, string $index): bool
    {
        $rows = $this->db->query("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$index])->getResult();

        return count($rows) > 0;
    }
}

<?php

namespace App\Database\Migrations;

use App\Services\MkdtSettlementService;
use CodeIgniter\Database\Migration;

class SynchronizeMkdtSettlementStatus extends Migration
{
    public function up()
    {
        foreach (['mkdt', 'keuangan', 'log_pembayaran', 'log_pembayaran_detail', 'keuangan_item_list'] as $table) {
            if (! $this->db->tableExists($table)) {
                return;
            }
        }

        if (
            ! $this->db->fieldExists('is_lunas', 'mkdt')
            || ! $this->db->fieldExists('is_void', 'keuangan')
            || ! $this->db->fieldExists('booking_is_installment', 'log_pembayaran_detail')
        ) {
            return;
        }

        $this->addIndexIfMissing('log_pembayaran', 'idx_log_pembayaran_mkdt_deleted', ['id_mkdt', 'is_deleted']);
        $this->addIndexIfMissing('keuangan', 'idx_keuangan_mkdt_void', ['id_mkdt', 'is_void']);

        $ids = $this->db->table('mkdt')
            ->select('id_mkdt')
            ->orderBy('id_mkdt', 'ASC')
            ->get()
            ->getResultArray();
        $settlement = new MkdtSettlementService($this->db);

        $this->db->transException(true)->transBegin();
        try {
            foreach ($ids as $row) {
                $settlement->synchronize((int) $row['id_mkdt']);
            }

            $this->db->transCommit();
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    public function down()
    {
        // No-op: is_lunas adalah derived data dan nilai lama tidak dapat
        // dipulihkan secara aman karena sebagian sudah tidak konsisten.
    }

    private function addIndexIfMissing(string $table, string $index, array $columns): void
    {
        if (isset($this->db->getIndexData($table)[$index])) {
            return;
        }

        $columnList = implode(', ', array_map(
            fn (string $column): string => $this->db->escapeIdentifiers($column),
            $columns
        ));
        $tableName = $this->db->escapeIdentifiers($this->db->prefixTable($table));
        $indexName = $this->db->escapeIdentifiers($index);

        if ($this->db->DBDriver === 'SQLite3') {
            $this->db->query("CREATE INDEX {$indexName} ON {$tableName} ({$columnList})");
        } else {
            $this->db->query("ALTER TABLE {$tableName} ADD INDEX {$indexName} ({$columnList})");
        }

        $this->db->resetDataCache();
    }
}

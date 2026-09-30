<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ReconcileMkdt354Sbum extends Migration
{
    private const ID_MKDT = 354;

    private const EXPECTED = [
        'harga_uang_muka' => 6_500_000,
        'harga_diskon_uang_muka' => 0,
        'harga_sbum' => 0,
        'harga_biaya_proses' => 0,
        'harga_penambahan_um' => 159_500_000,
        'harga_kpr_acc' => 0,
    ];

    private const CHANGES = [
        'harga_sbum' => 4_000_000,
        'harga_biaya_proses' => 3_000_000,
        'harga_penambahan_um' => 0,
    ];

    public function up()
    {
        if (! $this->db->tableExists('mkdt')) {
            return;
        }

        foreach (array_unique(array_merge(array_keys(self::EXPECTED), array_keys(self::CHANGES))) as $field) {
            if (! $this->db->fieldExists($field, 'mkdt')) {
                return;
            }
        }

        $row = $this->db->table('mkdt')
            ->where('id_mkdt', self::ID_MKDT)
            ->get()
            ->getRowArray();

        // Database baru/test dapat tidak memiliki transaksi historis ini.
        if (! $row) {
            return;
        }

        if ($this->isApplied($row)) {
            $this->ensureHistory($row, $row);
            return;
        }

        $this->assertSafeBaseline($row);

        $this->db->transException(true)->transBegin();
        try {
            $before = $row;
            $changes = self::CHANGES;
            if ($this->db->fieldExists('updated_at', 'mkdt')) {
                $changes['updated_at'] = date('Y-m-d H:i:s');
            }

            if (! $this->db->table('mkdt')->where('id_mkdt', self::ID_MKDT)->update($changes)) {
                throw new \RuntimeException('Gagal merekonsiliasi SBUM MKDT 354.');
            }

            $after = array_merge($row, $changes);
            $this->ensureHistory($before, $after);
            $this->db->transCommit();
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    public function down()
    {
        // No-op: keputusan bahwa selisih UM Rp4 juta adalah SBUM merupakan
        // koreksi bisnis. Mengembalikan nilai lama akan memunculkan anomali lagi.
    }

    private function isApplied(array $row): bool
    {
        foreach (self::CHANGES as $field => $value) {
            if (round((float) ($row[$field] ?? 0), 2) !== round((float) $value, 2)) {
                return false;
            }
        }

        return true;
    }

    private function assertSafeBaseline(array $row): void
    {
        foreach (self::EXPECTED as $field => $expected) {
            $current = (float) ($row[$field] ?? 0);

            // Koreksi langsung Biaya Proses/Turun KPR dapat sudah diterapkan
            // lebih dahulu melalui manifest rekonsiliasi.
            if (array_key_exists($field, self::CHANGES)) {
                $target = (float) self::CHANGES[$field];
                if (round($current, 2) === round($target, 2)) {
                    continue;
                }
            }

            if (round($current, 2) !== round((float) $expected, 2)) {
                throw new \RuntimeException(
                    "MKDT 354 berubah pada {$field}; migration SBUM dihentikan agar tidak menimpa data baru."
                );
            }
        }
    }

    private function ensureHistory(array $before, array $after): void
    {
        if (! $this->db->tableExists('history_log')) {
            return;
        }

        $action = 'reconcile_financial_breakdown';
        $summary = 'MKDT 354: selisih UM Rp4.000.000 ditetapkan sebagai SBUM; Biaya Proses dan Turun KPR direkonsiliasi.';
        $exists = $this->db->table('history_log')
            ->where('module', 'mkdt')
            ->where('reference_id', self::ID_MKDT)
            ->where('action', $action)
            ->where('summary', $summary)
            ->countAllResults() > 0;

        if ($exists) {
            return;
        }

        $oldData = array_intersect_key($before, self::CHANGES);
        $newData = array_intersect_key($after, self::CHANGES);
        $this->db->table('history_log')->insert([
            'module' => 'mkdt',
            'reference_type' => 'mkdt',
            'reference_id' => self::ID_MKDT,
            'id_kavling' => !empty($after['id_kavling']) ? (int) $after['id_kavling'] : null,
            'action' => $action,
            'summary' => $summary,
            'old_data' => json_encode($oldData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'new_data' => json_encode($newData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'metadata' => json_encode([
                'migration' => '2026-09-25-000001_ReconcileMkdt354Sbum',
                'business_decision' => 'Selisih UM Rp4.000.000 adalah SBUM.',
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'add_by' => null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}

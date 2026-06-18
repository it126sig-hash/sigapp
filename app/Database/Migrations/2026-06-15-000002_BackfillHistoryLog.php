<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class BackfillHistoryLog extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('history_log')) {
            return;
        }

        $this->backfillMkdt();
        $this->backfillProduksi();
        $this->backfillDanaJaminan();
        $this->backfillTargetSiteplan();
        $this->backfillCashoutSubkon();
    }

    public function down()
    {
        if (! $this->db->tableExists('history_log')) {
            return;
        }

        $this->db->table('history_log')
            ->whereIn('legacy_table', [
                'mkdt_change_history',
                'produksi_change_history',
                'dana_jaminan_history',
                'target_siteplan_history',
                'cashout_subkon_history',
            ])
            ->delete();
    }

    private function backfillMkdt(): void
    {
        if (! $this->db->tableExists('mkdt_change_history')) {
            return;
        }

        $this->db->query("
            INSERT INTO history_log
                (module, reference_type, reference_id, id_kavling, id_proyek, action, summary, old_data, new_data, metadata, legacy_table, legacy_id, add_by, created_at)
            SELECT
                'mkdt',
                'mkdt',
                h.id_mkdt,
                h.id_kavling,
                c.id_proyek,
                h.action,
                h.summary,
                h.old_data,
                h.new_data,
                NULL,
                'mkdt_change_history',
                h.id,
                h.add_by,
                h.created_at
            FROM mkdt_change_history h
            LEFT JOIN kavling k ON k.id_kavling = h.id_kavling
            LEFT JOIN jalan j ON j.id_jalan = k.id_jalan
            LEFT JOIN cluster c ON c.id_cluster = j.id_cluster
            WHERE NOT EXISTS (
                SELECT 1 FROM history_log hl
                WHERE hl.legacy_table = 'mkdt_change_history' AND hl.legacy_id = h.id
            )
        ");
    }

    private function backfillProduksi(): void
    {
        if (! $this->db->tableExists('produksi_change_history')) {
            return;
        }

        $this->db->query("
            INSERT INTO history_log
                (module, reference_type, reference_id, id_kavling, id_proyek, action, summary, old_data, new_data, metadata, legacy_table, legacy_id, add_by, created_at)
            SELECT
                'produksi',
                'produksi',
                h.id_produksi,
                h.id_kavling,
                c.id_proyek,
                h.action,
                h.summary,
                h.old_data,
                h.new_data,
                JSON_OBJECT('files', h.files),
                'produksi_change_history',
                h.id,
                h.add_by,
                h.created_at
            FROM produksi_change_history h
            LEFT JOIN kavling k ON k.id_kavling = h.id_kavling
            LEFT JOIN jalan j ON j.id_jalan = k.id_jalan
            LEFT JOIN cluster c ON c.id_cluster = j.id_cluster
            WHERE NOT EXISTS (
                SELECT 1 FROM history_log hl
                WHERE hl.legacy_table = 'produksi_change_history' AND hl.legacy_id = h.id
            )
        ");
    }

    private function backfillDanaJaminan(): void
    {
        if (! $this->db->tableExists('dana_jaminan_history')) {
            return;
        }

        $this->db->query("
            INSERT INTO history_log
                (module, reference_type, reference_id, id_kavling, id_proyek, action, summary, old_data, new_data, metadata, legacy_table, legacy_id, add_by, created_at)
            SELECT
                'dana_jaminan',
                CASE WHEN h.id_pengajuan IS NOT NULL THEN 'pengajuan_jaminan' ELSE 'dana_akad' END,
                COALESCE(h.id_pengajuan, h.id_dana_akad),
                h.id_kavling,
                c.id_proyek,
                h.aksi,
                h.deskripsi,
                NULL,
                h.snapshot,
                JSON_OBJECT('id_mkdt', h.id_mkdt, 'id_dana_akad', h.id_dana_akad, 'id_pengajuan', h.id_pengajuan),
                'dana_jaminan_history',
                h.id,
                h.add_by,
                h.created_at
            FROM dana_jaminan_history h
            LEFT JOIN kavling k ON k.id_kavling = h.id_kavling
            LEFT JOIN jalan j ON j.id_jalan = k.id_jalan
            LEFT JOIN cluster c ON c.id_cluster = j.id_cluster
            WHERE NOT EXISTS (
                SELECT 1 FROM history_log hl
                WHERE hl.legacy_table = 'dana_jaminan_history' AND hl.legacy_id = h.id
            )
        ");
    }

    private function backfillTargetSiteplan(): void
    {
        if (! $this->db->tableExists('target_siteplan_history')) {
            return;
        }

        $this->db->query("
            INSERT INTO history_log
                (module, reference_type, reference_id, id_kavling, id_proyek, action, summary, old_data, new_data, metadata, legacy_table, legacy_id, add_by, created_at)
            SELECT
                'target_siteplan',
                'target_siteplan',
                h.id_target,
                NULL,
                t.id_proyek,
                h.aksi,
                h.deskripsi,
                NULL,
                h.snapshot,
                NULL,
                'target_siteplan_history',
                h.id_target_history,
                h.add_by,
                h.created_at
            FROM target_siteplan_history h
            LEFT JOIN target_siteplan t ON t.id_target = h.id_target
            WHERE NOT EXISTS (
                SELECT 1 FROM history_log hl
                WHERE hl.legacy_table = 'target_siteplan_history' AND hl.legacy_id = h.id_target_history
            )
        ");
    }

    private function backfillCashoutSubkon(): void
    {
        if (! $this->db->tableExists('cashout_subkon_history')) {
            return;
        }

        $this->db->query("
            INSERT INTO history_log
                (module, reference_type, reference_id, id_kavling, id_proyek, action, summary, old_data, new_data, metadata, legacy_table, legacy_id, add_by, created_at)
            SELECT
                'cashout_subkon',
                'cashout_subkon',
                h.id_cashout_subkon,
                NULL,
                cp.id_proyek,
                CASE h.status
                    WHEN 0 THEN 'update_spk'
                    WHEN 1 THEN 'turun_jatuh_tempo'
                    WHEN 2 THEN 'pengajuan_spp'
                    WHEN 3 THEN 'pengajuan_pencairan'
                    WHEN 4 THEN 'pembayaran'
                    ELSE 'status_update'
                END,
                h.keterangan,
                NULL,
                NULL,
                JSON_OBJECT(
                    'status', h.status,
                    'id_proyek_list', cp.id_proyek_list
                ),
                'cashout_subkon_history',
                h.id_cashout_subkon_history,
                h.add_by,
                h.created_at
            FROM cashout_subkon_history h
            LEFT JOIN (
                SELECT
                    csk.id_cashout_subkon,
                    MIN(c.id_proyek) AS id_proyek,
                    GROUP_CONCAT(DISTINCT c.id_proyek ORDER BY c.id_proyek SEPARATOR ',') AS id_proyek_list
                FROM cashout_subkon_kavling csk
                LEFT JOIN kavling k ON k.id_kavling = csk.id_kavling
                LEFT JOIN jalan j ON j.id_jalan = k.id_jalan
                LEFT JOIN cluster c ON c.id_cluster = j.id_cluster
                GROUP BY csk.id_cashout_subkon
            ) cp ON cp.id_cashout_subkon = h.id_cashout_subkon
            WHERE NOT EXISTS (
                SELECT 1 FROM history_log hl
                WHERE hl.legacy_table = 'cashout_subkon_history' AND hl.legacy_id = h.id_cashout_subkon_history
            )
        ");
    }
}

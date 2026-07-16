<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Backfill data_akad/riwayat_pencairan_jaminan(_detail) dan bank_kpr_disbursement
 * ke modul Pencairan Akad, lalu nonaktifkan menu lama. Ledger lama (source_type
 * dana_jaminan/bank_kpr_disbursement) TIDAK disentuh - tetap jadi record cash asli;
 * baris baru di pencairan_akad_* hanya untuk reporting/piutang ke depan.
 */
class BackfillPencairanAkadModule extends Migration
{
    public function up()
    {
        $this->backfill();
        $this->updateMenu();
    }

    public function down()
    {
        $planIds = array_map(
            static fn ($row) => (int) $row->id,
            $this->db->table('pencairan_akad_plan')->select('id')->where('legacy_source_type', 'mkdt')->get()->getResult()
        );
        if (empty($planIds)) {
            return;
        }

        $itemIds = array_map(
            static fn ($row) => (int) $row->id,
            $this->db->table('pencairan_akad_item')->select('id')->whereIn('id_plan', $planIds)->get()->getResult()
        );
        $pengajuanIds = array_map(
            static fn ($row) => (int) $row->id,
            $this->db->table('pencairan_akad_pengajuan')->select('id')->whereIn('id_plan', $planIds)->get()->getResult()
        );

        if ($pengajuanIds) {
            $paymentIds = array_map(
                static fn ($row) => (int) $row->id,
                $this->db->table('pencairan_akad_payment')->select('id')->whereIn('id_pengajuan', $pengajuanIds)->get()->getResult()
            );
            if ($paymentIds) {
                $this->db->table('pencairan_akad_payment_detail')->whereIn('id_payment', $paymentIds)->delete();
            }
            $this->db->table('pencairan_akad_payment')->whereIn('id_pengajuan', $pengajuanIds)->delete();
            $this->db->table('pencairan_akad_pengajuan_detail')->whereIn('id_pengajuan', $pengajuanIds)->delete();
            $this->db->table('pencairan_akad_pengajuan')->whereIn('id', $pengajuanIds)->delete();
        }
        if ($itemIds) {
            $this->db->table('pencairan_akad_item')->whereIn('id', $itemIds)->delete();
        }
        $this->db->table('pencairan_akad_plan')->whereIn('id', $planIds)->delete();
    }

    private function backfill(): void
    {
        $mkdtRows = $this->db->table('mkdt')
            ->select('id_mkdt, id_konsumen, status_mkdt, harga_kpr_acc')
            ->where('status_mkdt', 'Akad')
            ->get()
            ->getResult();

        foreach ($mkdtRows as $mkdt) {
            $idMkdt = (int) $mkdt->id_mkdt;

            $existingPlan = $this->db->table('pencairan_akad_plan')->where('id_mkdt', $idMkdt)->get()->getRow();
            if ($existingPlan) {
                continue;
            }

            $kavling = $this->db->table('kavling')->select('id_kavling')->where('id_mkdt', $idMkdt)->get()->getRow();
            $idKavling = $kavling ? (int) $kavling->id_kavling : 0;
            if ($idKavling <= 0) {
                continue;
            }

            $danaAkadRows = $this->db->table('dana_akad')->where('id_kavling', $idKavling)->get()->getResult();
            $disbursementRows = $this->db->tableExists('bank_kpr_disbursement')
                ? $this->db->table('bank_kpr_disbursement')
                    ->where('id_mkdt', $idMkdt)
                    ->where('deleted_at', null)
                    ->where('status !=', 'void')
                    ->get()
                    ->getResult()
                : [];

            if (empty($danaAkadRows) && empty($disbursementRows)) {
                continue;
            }

            $totalRetensi = 0;
            foreach ($danaAkadRows as $row) {
                $totalRetensi += (float) $row->nominal;
            }
            $accKpr = (float) $mkdt->harga_kpr_acc;

            $this->db->table('pencairan_akad_plan')->insert([
                'id_mkdt' => $idMkdt,
                'id_kavling' => $idKavling,
                'harga_kpr_acc' => $accKpr,
                'total_retensi' => $totalRetensi,
                'total_hasil_akad' => max(0, $accKpr - $totalRetensi),
                'status' => 'backfill',
                'legacy_source_type' => 'mkdt',
                'legacy_source_id' => $idMkdt,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            $idPlan = (int) $this->db->insertID();

            foreach ($danaAkadRows as $row) {
                $this->db->table('pencairan_akad_item')->insert([
                    'id_plan' => $idPlan,
                    'jenis' => 'retensi',
                    'id_list_dajam' => (int) $row->id_list_dajam,
                    'nominal' => (float) $row->nominal,
                    'catatan' => $row->keterangan,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                $idItem = (int) $this->db->insertID();

                if ((int) $row->sudah_cair === 1) {
                    $this->backfillPaidPengajuan(
                        $idPlan,
                        $idItem,
                        (float) ($row->nominal_cair ?: $row->nominal),
                        $row->tgl_cair,
                        $row->keterangan,
                        'riwayat_pencairan_jaminan'
                    );
                }
            }

            $urutanTenor = 1;
            foreach ($disbursementRows as $row) {
                $nominal = (float) $row->nominal_plafon ?: (float) $row->nominal_cair;
                if ($nominal <= 0) {
                    continue;
                }

                $this->db->table('pencairan_akad_item')->insert([
                    'id_plan' => $idPlan,
                    'jenis' => 'tenor',
                    'urutan_tenor' => $urutanTenor++,
                    'nominal' => $nominal,
                    'catatan' => $row->keterangan,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                $idItem = (int) $this->db->insertID();

                if ($row->status === 'cair') {
                    $this->backfillPaidPengajuan(
                        $idPlan,
                        $idItem,
                        (float) $row->nominal_cair,
                        $row->tanggal_cair,
                        $row->keterangan,
                        'bank_kpr_disbursement'
                    );
                }
            }
        }
    }

    private function backfillPaidPengajuan(int $idPlan, int $idItem, float $nominal, ?string $tanggalCair, ?string $catatan, string $legacySource): void
    {
        if ($nominal <= 0) {
            return;
        }
        $tanggalCair = $tanggalCair ?: date('Y-m-d');

        $this->db->table('pencairan_akad_pengajuan')->insert([
            'id_plan' => $idPlan,
            'tanggal_pengajuan' => $tanggalCair,
            'tanggal_rencana_cair' => $tanggalCair,
            'catatan' => 'Backfill dari ' . $legacySource,
            'total_pengajuan' => $nominal,
            'total_cair' => $nominal,
            'status' => 'paid',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $idPengajuan = (int) $this->db->insertID();

        $this->db->table('pencairan_akad_pengajuan_detail')->insert([
            'id_pengajuan' => $idPengajuan,
            'id_item' => $idItem,
            'nominal_pengajuan' => $nominal,
            'nominal_cair' => $nominal,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $idDetail = (int) $this->db->insertID();

        $this->db->table('pencairan_akad_payment')->insert([
            'id_pengajuan' => $idPengajuan,
            'tanggal_cair' => $tanggalCair,
            'catatan' => $catatan,
            'total_cair' => $nominal,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $idPayment = (int) $this->db->insertID();

        $this->db->table('pencairan_akad_payment_detail')->insert([
            'id_payment' => $idPayment,
            'id_pengajuan_detail' => $idDetail,
            'nominal_cair' => $nominal,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function updateMenu(): void
    {
        if (! $this->db->tableExists('siteplan_menu_items') || ! $this->db->tableExists('siteplan_menu_roles')) {
            return;
        }

        $now = date('Y-m-d H:i:s');

        foreach (['keuangan_dana_jaminan', 'keuangan_pencairan_bank'] as $oldKey) {
            $this->db->table('siteplan_menu_items')->where('item_key', $oldKey)->update([
                'is_active' => 0,
                'updated_at' => $now,
            ]);
        }

        $payload = [
            'id_group'    => 3,
            'item_key'    => 'keuangan_pencairan_akad',
            'label'       => 'Pencairan Akad',
            'group_label' => 'Pembayaran',
            'onclick'     => 'pencairan_akad()',
            'icon'        => 'fas fa-hand-holding-usd',
            'btn_class'   => 'btn-primary',
            'sort_order'  => 50,
            'is_active'   => 1,
            'extra_id'    => 'pencairan_akad-btn',
            'extra_class' => '',
            'updated_at'  => $now,
        ];

        $existing = $this->db->table('siteplan_menu_items')
            ->select('id')
            ->where('item_key', 'keuangan_pencairan_akad')
            ->get()
            ->getRow();

        if ($existing) {
            $this->db->table('siteplan_menu_items')->where('id', (int) $existing->id)->update($payload);
            $itemId = (int) $existing->id;
        } else {
            $payload['created_at'] = $now;
            $this->db->table('siteplan_menu_items')->insert($payload);
            $itemId = (int) $this->db->insertID();
        }

        foreach ([1, 3] as $roleId) {
            $exists = $this->db->table('siteplan_menu_roles')
                ->where('id_group', $roleId)
                ->where('id_siteplan_menu_item', $itemId)
                ->countAllResults();

            if ($exists > 0) {
                continue;
            }

            $this->db->table('siteplan_menu_roles')->insert([
                'id_group'              => $roleId,
                'id_siteplan_menu_item' => $itemId,
                'created_at'            => $now,
                'updated_at'            => $now,
            ]);
        }
    }
}

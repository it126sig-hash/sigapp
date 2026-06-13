<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSiteplanMenuTables extends Migration
{
    public function up()
    {
        $this->createItemsTable();
        $this->createRolesTable();
        $this->seedDefaultItems();
    }

    public function down()
    {
        $this->forge->dropTable('siteplan_menu_roles', true);
        $this->forge->dropTable('siteplan_menu_items', true);
    }

    private function createItemsTable(): void
    {
        if ($this->db->tableExists('siteplan_menu_items')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'item_key' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
            ],
            'id_group' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'label' => [
                'type'       => 'VARCHAR',
                'constraint' => 160,
            ],
            'group_label' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
                'null'       => true,
            ],
            'onclick' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'icon' => [
                'type'       => 'VARCHAR',
                'constraint' => 160,
                'null'       => true,
            ],
            'btn_class' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
                'default'    => 'btn-primary',
            ],
            'sort_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'extra_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
                'null'       => true,
            ],
            'extra_class' => [
                'type'       => 'VARCHAR',
                'constraint' => 160,
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
        $this->forge->addUniqueKey('item_key', 'uq_siteplan_menu_item_key');
        $this->forge->addKey(['id_group', 'is_active'], false, false, 'idx_siteplan_menu_group_active');
        $this->forge->createTable('siteplan_menu_items', true);
    }

    private function createRolesTable(): void
    {
        if ($this->db->tableExists('siteplan_menu_roles')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_group' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'id_siteplan_menu_item' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
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
        $this->forge->addKey('id_group', false, false, 'idx_siteplan_menu_roles_group');
        $this->forge->addUniqueKey(['id_group', 'id_siteplan_menu_item'], 'uq_siteplan_menu_role_item');
        $this->forge->createTable('siteplan_menu_roles', true);
    }

    private function seedDefaultItems(): void
    {
        if (!$this->db->tableExists('siteplan_menu_items') || !$this->db->tableExists('siteplan_menu_roles')) {
            return;
        }

        $items = [
            [6, 'planning_add_kavling', 'Data', 'Tambah Data', 'tambah_kavling()', 'fas fa-plus', 'btn-primary', 10, 'add_kavling', '', [6]],
            [6, 'planning_edit_kavling_batch', 'Data', 'Ubah Kavling', 'edit_kavling_batch()', 'fas fa-edit', 'btn-primary', 20, 'edit_kavling_batch', '', [6]],
            [6, 'planning_manual_selection', 'Seleksi Manual', 'Manual Seleksi', '', '', '', 30, '', '', [6]],
            [6, 'planning_selection_done', 'Seleksi Manual', 'Selesai', 'selesai_selection(1)', 'fas fa-check', 'btn-success', 40, 'selesai_pindah_btn', '', [6]],
            [6, 'planning_selection_cancel', 'Seleksi Manual', 'Batal', 'selesai_selection(0)', 'fas fa-times', 'btn-danger', 50, 'batal_pindah_btn', '', [6]],
            [6, 'planning_undo_manual_selection', 'Seleksi Manual', 'Undo Titik', 'undo_manual_selection()', 'fas fa-undo', 'btn-outline-warning', 60, 'planning_undo_manual_selection', '', [6]],
            [6, 'planning_toggle_legend', 'Lainnya', 'Cek Legenda', '', 'fas fa-palette', 'btn-info', 70, 'planning_toggle_btn', '', [6]],
            [6, 'planning_hapus_seleksi', 'Lainnya', 'Hapus Seleksi', 'hapus_seleksi()', 'fas fa-eraser', 'btn-outline-warning', 80, '', '', [6]],

            [3, 'keuangan_bayar_tagihan', 'Pembayaran', 'Bayar Tagihan', 'isi_data()', 'fas fa-file-invoice-dollar', 'btn-primary', 10, 'bayar_tagihan-btn', '', [3]],
            [3, 'keuangan_lihat_detail', 'Pembayaran', 'Lihat Detail', 'lihat_detail()', 'fas fa-eye', 'btn-info', 20, '', '', [3]],
            [3, 'keuangan_cash_out', 'Pembayaran', 'Cash Out', 'isi_cashout()', 'fas fa-money-bill-wave', 'btn-primary', 30, 'bayar_sumurbor-btn', '', [3]],
            [3, 'keuangan_cash_out_subkon', 'Pembayaran', 'Cash Out Subkon', 'openCOSubkon()', 'fas fa-hand-holding-usd', 'btn-primary', 40, 'bayar_sumurbor-btn', '', [3]],
            [3, 'keuangan_dana_jaminan', 'Pembayaran', 'Dana Jaminan', 'dana_akad()', 'fas fa-piggy-bank', 'btn-primary', 50, 'dana_akad-btn', '', [3]],
            [3, 'keuangan_print_tagihan', 'Laporan', 'Print Tagihan', 'print_tagihan()', 'fas fa-print', 'btn-info', 60, 'print_tagihan-btn', '', [3]],
            [3, 'keuangan_list_jatuh_tempo', 'Laporan', 'List Jatuh Tempo', 'cek_jatuh_tempo(true)', 'fas fa-calendar-times', 'btn-info', 70, '', '', [3]],
            [3, 'keuangan_batal_booking', 'Lainnya', 'Batal Booking', 'terima_batal()', 'fas fa-ban', 'btn-outline-danger', 80, '', '', [3]],
            [3, 'keuangan_hapus_seleksi', 'Lainnya', 'Hapus Seleksi', 'hapus_seleksi()', 'fas fa-eraser', 'btn-outline-warning', 90, '', '', [3]],

            [4, 'mkdt_ubah_status_kavling', 'Data Konsumen', 'Ubah Status Kavling', 'isi_data()', 'fas fa-exchange-alt', 'btn-primary', 10, 'edit_kavling_batch', '', [4]],
            [4, 'mkdt_lihat_detail', 'Data Konsumen', 'Lihat Detail', 'lihat_detail()', 'fas fa-eye', 'btn-info', 20, '', '', [4]],
            [4, 'mkdt_isi_data_konsumen', 'Data Konsumen', 'Isi Data Konsumen', 'isi_data_konsumen()', 'fas fa-user-edit', 'btn-primary', 30, '', '', [4]],
            [4, 'mkdt_set_harga', 'Data Konsumen', 'Set Harga', 'open_set_harga()', 'fas fa-tag', 'btn-primary', 40, '', '', [4]],
            [4, 'mkdt_turun_pembangunan', 'Data Konsumen', 'Turun Pembangunan', 'open_set_turun_pembangunan()', 'fas fa-hard-hat', 'btn-primary', 50, '', '', [4]],
            [4, 'mkdt_standing_instruction', 'Dokumen', 'Standing Instruction', 'isi_si()', 'fas fa-file-signature', 'btn-info', 60, '', '', [4]],
            [4, 'mkdt_buat_nominatif', 'Dokumen', 'Buat Nominatif', 'buat_nominatif()', 'fas fa-file-alt', 'btn-info', 70, '', '', [4]],
            [4, 'mkdt_batal_booking', 'Lainnya', 'Batal Booking', 'ajukan_batal()', 'fas fa-ban', 'btn-outline-danger', 80, '', '', [4]],
            [4, 'mkdt_hapus_seleksi', 'Lainnya', 'Hapus Seleksi', 'hapus_seleksi()', 'fas fa-eraser', 'btn-outline-warning', 90, '', '', [4]],

            [7, 'produksi_isi_ubah_data', 'Data', 'Isi/ubah Data', 'isi_data()', 'fas fa-edit', 'btn-primary', 10, '', 'btn-prod', [7]],
            [7, 'produksi_lihat_detail', 'Data', 'Lihat Detail', 'lihat_detail()', 'fas fa-eye', 'btn-info', 20, '', '', [7]],
            [7, 'produksi_pembayaran', 'Data', 'Pembayaran', 'isi_pembayaran()', 'fas fa-money-check-alt', 'btn-primary', 30, '', 'btn-prod', [7]],
            [7, 'produksi_cash_out_subkon', 'Data', 'Cash Out Subkon', 'openCOSubkon()', 'fas fa-hand-holding-usd', 'btn-primary', 40, 'btn-cashout_subkon-pr', '', [7]],
            [7, 'produksi_slf', 'Pengawasan', 'SLF', 'buat_slf()', 'fas fa-certificate', 'btn-info', 50, '', 'btn-prod', [7]],
            [7, 'produksi_belum_selesai', 'Pengawasan', 'Bangunan Belum Selesai', 'cek_tanggal_pembangunan(true)', 'fas fa-exclamation-triangle', 'btn-info', 60, '', '', [7]],
            [7, 'produksi_komplain', 'Pengawasan', 'Komplain', 'open_komplain_produksi()', 'fas fa-bullhorn', 'btn-outline-warning', 70, '', 'btn-prod', [7]],
            [7, 'produksi_tambah_jalan_state', 'Tambah Jalan', 'State Tambah Jalan', '', '', '', 80, '', '', [7]],
            [7, 'produksi_tambah_jalan', 'Tambah Jalan', 'Tambah Jalan', 'start_tambah_jalan_produksi()', 'fas fa-road', 'btn-primary', 90, 'produksi_add_jalan', 'btn-prod', [7]],
            [7, 'produksi_tambah_jalan_ok', 'Tambah Jalan', 'OK', 'tambah_jalan_produksi()', 'fas fa-check', 'btn-success', 100, 'produksi_add_jalan_ok', 'btn-prod d-none', [7]],
            [7, 'produksi_tambah_jalan_undo', 'Tambah Jalan', 'Undo Titik', 'undo_manual_selection()', 'fas fa-undo', 'btn-outline-warning', 110, 'produksi_add_jalan_undo', 'btn-prod d-none', [7]],
            [7, 'produksi_tambah_jalan_batal', 'Tambah Jalan', 'Batal', 'cancel_tambah_jalan_produksi()', 'fas fa-times', 'btn-outline-secondary', 120, 'produksi_add_jalan_batal', 'btn-prod d-none', [7]],
            [7, 'produksi_tambah_jalan_hint', 'Tambah Jalan', 'Tandai jalan yang akan dibuat', '', '', '', 130, 'produksi_add_jalan_hint', '', [7]],
            [7, 'produksi_hapus_seleksi', 'Lainnya', 'Hapus Seleksi', 'hapus_seleksi()', 'fas fa-eraser', 'btn-outline-warning', 140, '', '', [7]],

            [8, 'sales_checklist', '', 'Checklist', 'open_checklist_sales()', 'fas fa-clipboard-check', 'btn-primary', 10, '', '', [8]],
            [8, 'sales_lihat_detail', '', 'Lihat Detail', 'lihat_detail()', 'fas fa-eye', 'btn-info', 20, '', '', [8]],
            [8, 'sales_serah_terima', '', 'Serah Terima', 'open_serah_terima()', 'fas fa-handshake', 'btn-primary', 30, '', '', [8]],
            [8, 'sales_komplain', '', 'Komplain', 'open_komplain_sales()', 'fas fa-bullhorn', 'btn-outline-warning', 40, '', '', [8]],
            [8, 'sales_hapus_seleksi', '', 'Hapus Seleksi', 'hapus_seleksi()', 'fas fa-eraser', 'btn-outline-warning', 50, '', '', [8]],

            [9, 'direksi_diskresi_hargajual', '', 'Diskresi HargaJual', 'open_diskresi()', 'fas fa-balance-scale', 'btn-primary', 10, '', '', [9]],
            [9, 'direksi_lihat_detail', '', 'Lihat Detail', 'lihat_detail()', 'fas fa-eye', 'btn-info', 20, '', '', [9]],
            [9, 'direksi_hapus_seleksi', '', 'Hapus Seleksi', 'hapus_seleksi()', 'fas fa-eraser', 'btn-outline-warning', 30, '', '', [9]],

            [11, 'target_set_target', '', 'Set Target', 'open_target_siteplan()', 'fas fa-bullseye', 'btn-primary', 10, '', '', [11]],
            [11, 'target_lihat_detail', '', 'Lihat Detail', 'lihat_detail()', 'fas fa-eye', 'btn-info', 20, '', '', [11]],
            [11, 'target_histori', '', 'Histori Target', 'open_target_history()', 'fas fa-history', 'btn-info', 30, '', '', [11]],
            [11, 'target_hapus_seleksi', '', 'Hapus Seleksi', 'hapus_seleksi()', 'fas fa-eraser', 'btn-outline-warning', 40, '', '', [11]],

            [0, 'others_isi_ubah_data', '', 'Isi/ubah Data', 'isi_data()', 'fas fa-edit', 'btn-primary', 10, 'edit_kavling_batch', '', [0]],
            [0, 'others_lihat_detail', '', 'Lihat Detail', 'lihat_detail()', 'fas fa-eye', 'btn-info', 20, '', '', [0]],
            [0, 'others_hapus_seleksi', '', 'Hapus Seleksi', 'hapus_seleksi()', 'fas fa-eraser', 'btn-outline-warning', 30, '', '', [0]],
        ];

        foreach ($items as $item) {
            $this->upsertItem($item);
        }
    }

    private function upsertItem(array $item): void
    {
        [$groupId, $key, $groupLabel, $label, $onclick, $icon, $btnClass, $sortOrder, $extraId, $extraClass, $roles] = $item;
        $now = date('Y-m-d H:i:s');
        $row = [
            'id_group'    => $groupId,
            'item_key'    => $key,
            'label'       => $label,
            'group_label' => $groupLabel,
            'onclick'     => $onclick,
            'icon'        => $icon,
            'btn_class'   => $btnClass ?: 'btn-primary',
            'sort_order'  => $sortOrder,
            'is_active'   => 1,
            'extra_id'    => $extraId,
            'extra_class' => $extraClass,
            'updated_at'  => $now,
        ];

        $existing = $this->db->table('siteplan_menu_items')->select('id')->where('item_key', $key)->get()->getRow();
        if ($existing) {
            $this->db->table('siteplan_menu_items')->where('id', (int) $existing->id)->update($row);
            $itemId = (int) $existing->id;
        } else {
            $row['created_at'] = $now;
            $this->db->table('siteplan_menu_items')->insert($row);
            $itemId = (int) $this->db->insertID();
        }

        foreach ($roles as $roleId) {
            $exists = $this->db->table('siteplan_menu_roles')
                ->where('id_group', (int) $roleId)
                ->where('id_siteplan_menu_item', $itemId)
                ->countAllResults();
            if ($exists > 0) {
                continue;
            }

            $this->db->table('siteplan_menu_roles')->insert([
                'id_group'               => (int) $roleId,
                'id_siteplan_menu_item'  => $itemId,
                'created_at'             => $now,
                'updated_at'             => $now,
            ]);
        }
    }
}

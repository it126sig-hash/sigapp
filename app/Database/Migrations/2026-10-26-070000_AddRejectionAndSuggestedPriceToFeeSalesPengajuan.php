<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRejectionAndSuggestedPriceToFeeSalesPengajuan extends Migration
{
    public function up()
    {
        // 1. Modifikasi enum status fee_sales_pengajuan untuk menyertakan 'ditolak'
        if ($this->db->tableExists('fee_sales_pengajuan')) {
            $this->db->query("ALTER TABLE fee_sales_pengajuan MODIFY COLUMN status ENUM('diajukan', 'cair_sebagian', 'selesai', 'void', 'ditolak') NOT NULL DEFAULT 'diajukan'");

            $fieldsFsp = [];
            if (! $this->db->fieldExists('rejected_by', 'fee_sales_pengajuan')) {
                $fieldsFsp['rejected_by'] = [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'void_reason',
                ];
            }
            if (! $this->db->fieldExists('rejected_role', 'fee_sales_pengajuan')) {
                $fieldsFsp['rejected_role'] = [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'null'       => true,
                    'after'      => 'rejected_by',
                ];
            }
            if (! $this->db->fieldExists('rejected_at', 'fee_sales_pengajuan')) {
                $fieldsFsp['rejected_at'] = [
                    'type'  => 'DATETIME',
                    'null'  => true,
                    'after' => 'rejected_role',
                ];
            }
            if (! $this->db->fieldExists('reject_reason', 'fee_sales_pengajuan')) {
                $fieldsFsp['reject_reason'] = [
                    'type'  => 'TEXT',
                    'null'  => true,
                    'after' => 'rejected_at',
                ];
            }
            if (! $this->db->fieldExists('suggested_harga_net_ajb', 'fee_sales_pengajuan')) {
                $fieldsFsp['suggested_harga_net_ajb'] = [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'null'       => true,
                    'after'      => 'reject_reason',
                ];
            }

            if (! empty($fieldsFsp)) {
                $this->forge->addColumn('fee_sales_pengajuan', $fieldsFsp);
            }
        }

        // 2. Tambah kolom suggested_harga_net_ajb pada fee_sales_pengajuan_item
        if ($this->db->tableExists('fee_sales_pengajuan_item')) {
            if (! $this->db->fieldExists('suggested_harga_net_ajb', 'fee_sales_pengajuan_item')) {
                $this->forge->addColumn('fee_sales_pengajuan_item', [
                    'suggested_harga_net_ajb' => [
                        'type'       => 'DECIMAL',
                        'constraint' => '15,2',
                        'null'       => true,
                        'after'      => 'nominal',
                    ],
                ]);
            }
        }

        // 3. Daftarkan event fee_sales_pengajuan_rejected pada notification_event_types
        if ($this->db->tableExists('notification_event_types')) {
            $exists = $this->db->table('notification_event_types')
                ->where('event_type', 'fee_sales_pengajuan_rejected')
                ->countAllResults();

            if (! $exists) {
                $now = date('Y-m-d H:i:s');
                $this->db->table('notification_event_types')->insert([
                    'event_type'       => 'fee_sales_pengajuan_rejected',
                    'category'         => 'Fee Sales',
                    'label'            => 'Penolakan Pengajuan Fee Sales',
                    'description'      => 'Notifikasi saat pengajuan fee sales ditolak oleh pihak MKDT atau Keuangan dengan saran revisi harga.',
                    'default_in_app'   => 1,
                    'default_email'    => 0,
                    'default_web_push' => 0,
                    'is_mandatory'     => 1,
                    'relevant_groups'  => '3;4',
                    'sort_order'       => 105,
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ]);
            }
        }
    }

    public function down()
    {
        if ($this->db->tableExists('fee_sales_pengajuan')) {
            $colsFsp = ['rejected_by', 'rejected_role', 'rejected_at', 'reject_reason', 'suggested_harga_net_ajb'];
            foreach ($colsFsp as $col) {
                if ($this->db->fieldExists($col, 'fee_sales_pengajuan')) {
                    $this->forge->dropColumn('fee_sales_pengajuan', $col);
                }
            }

            $this->db->query("ALTER TABLE fee_sales_pengajuan MODIFY COLUMN status ENUM('diajukan', 'cair_sebagian', 'selesai', 'void') NOT NULL DEFAULT 'diajukan'");
        }

        if ($this->db->tableExists('fee_sales_pengajuan_item')) {
            if ($this->db->fieldExists('suggested_harga_net_ajb', 'fee_sales_pengajuan_item')) {
                $this->forge->dropColumn('fee_sales_pengajuan_item', 'suggested_harga_net_ajb');
            }
        }

        if ($this->db->tableExists('notification_event_types')) {
            $this->db->table('notification_event_types')
                ->where('event_type', 'fee_sales_pengajuan_rejected')
                ->delete();
        }
    }
}

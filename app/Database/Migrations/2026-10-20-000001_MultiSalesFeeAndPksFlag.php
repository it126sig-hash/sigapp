<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MultiSalesFeeAndPksFlag extends Migration
{
    public function up()
    {
        // 1. Tambah kolom has_pks pada tabel sales
        if ($this->db->tableExists('sales')) {
            if (! $this->db->fieldExists('has_pks', 'sales')) {
                $this->forge->addColumn('sales', [
                    'has_pks' => [
                        'type'       => 'TINYINT',
                        'constraint' => 1,
                        'default'    => 0,
                        'null'       => false,
                        'after'      => 'status',
                        'comment'    => '1=Sales dengan PKS (kontrak), 0=tanpa PKS',
                    ],
                ]);
            }
        }

        // 2. Ubah UNIQUE KEY id_mkdt pada fee_sales menjadi regular index
        if ($this->db->tableExists('fee_sales')) {
            // Ambil semua index di fee_sales
            $indexes = $this->db->query("SHOW INDEX FROM fee_sales WHERE Key_name = 'id_mkdt'")->getResultArray();
            if (! empty($indexes)) {
                // Drop index unique id_mkdt
                $this->db->query("ALTER TABLE fee_sales DROP INDEX id_mkdt");
            }

            // Cek apakah index baru sudah ada
            $newIdx = $this->db->query("SHOW INDEX FROM fee_sales WHERE Key_name = 'idx_id_mkdt'")->getResultArray();
            if (empty($newIdx)) {
                $this->db->query("ALTER TABLE fee_sales ADD INDEX idx_id_mkdt (id_mkdt)");
            }
        }
    }

    public function down()
    {
        if ($this->db->tableExists('sales') && $this->db->fieldExists('has_pks', 'sales')) {
            $this->forge->dropColumn('sales', 'has_pks');
        }

        if ($this->db->tableExists('fee_sales')) {
            $newIdx = $this->db->query("SHOW INDEX FROM fee_sales WHERE Key_name = 'idx_id_mkdt'")->getResultArray();
            if (! empty($newIdx)) {
                $this->db->query("ALTER TABLE fee_sales DROP INDEX idx_id_mkdt");
            }
            $origIdx = $this->db->query("SHOW INDEX FROM fee_sales WHERE Key_name = 'id_mkdt'")->getResultArray();
            if (empty($origIdx)) {
                $this->db->query("ALTER TABLE fee_sales ADD UNIQUE KEY id_mkdt (id_mkdt)");
            }
        }
    }
}

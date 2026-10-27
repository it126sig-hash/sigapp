<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SeedMasterSalesAndBackfillKonsumen extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        if (! $this->db->tableExists('sales') || ! $this->db->tableExists('konsumen')) {
            return;
        }

        $now = date('Y-m-d H:i:s');

        $initialSales = [
            ['nama' => 'Yenny', 'kontak' => '081234567890', 'bank' => 'BCA', 'no_rek' => '-', 'fee_subsidi' => 1.50, 'fee_komersil' => 2.00, 'tanggal_pks' => '2024-01-01', 'status' => 'active'],
            ['nama' => 'Ani', 'kontak' => '081234567891', 'bank' => 'BCA', 'no_rek' => '-', 'fee_subsidi' => 1.50, 'fee_komersil' => 2.00, 'tanggal_pks' => '2024-01-01', 'status' => 'active'],
            ['nama' => 'Yuni', 'kontak' => '-', 'bank' => '-', 'no_rek' => '-', 'fee_subsidi' => 1.50, 'fee_komersil' => 2.00, 'tanggal_pks' => '2024-01-01', 'status' => 'active'],
            ['nama' => 'Winda', 'kontak' => '-', 'bank' => '-', 'no_rek' => '-', 'fee_subsidi' => 1.50, 'fee_komersil' => 2.00, 'tanggal_pks' => '2024-01-01', 'status' => 'active'],
            ['nama' => 'Nuri', 'kontak' => '-', 'bank' => '-', 'no_rek' => '-', 'fee_subsidi' => 1.50, 'fee_komersil' => 2.00, 'tanggal_pks' => '2024-01-01', 'status' => 'active'],
            ['nama' => 'Erlin', 'kontak' => '-', 'bank' => '-', 'no_rek' => '-', 'fee_subsidi' => 1.50, 'fee_komersil' => 2.00, 'tanggal_pks' => '2024-01-01', 'status' => 'active'],
        ];

        $salesMap = [];
        foreach ($initialSales as $s) {
            $existing = $db->table('sales')->where('LOWER(TRIM(nama))', strtolower(trim($s['nama'])))->get()->getRow();
            if ($existing) {
                $salesMap[strtolower(trim($s['nama']))] = $existing->id;
            } else {
                $db->table('sales')->insert(array_merge($s, [
                    'created_at' => $now,
                    'updated_at' => $now,
                ]));
                $newId = $db->insertID();
                $salesMap[strtolower(trim($s['nama']))] = $newId;

                // Log initial history if table exists
                if ($this->db->tableExists('sales_history')) {
                    $db->table('sales_history')->insert([
                        'id_sales'          => $newId,
                        'fee_subsidi_baru'  => $s['fee_subsidi'],
                        'fee_komersil_baru' => $s['fee_komersil'],
                        'tanggal_pks_baru'  => $s['tanggal_pks'],
                        'catatan'           => 'Inisialisasi Master Data Sales External dari Migration',
                        'created_at'        => $now,
                    ]);
                }
            }
        }

        // Fetch all active sales to ensure full mapping for fallback joins
        $allSales = $db->table('sales')->get()->getResult();
        foreach ($allSales as $sls) {
            $salesMap[strtolower(trim($sls->nama))] = $sls->id;
        }

        $idYenny = $salesMap['yenny'] ?? null;
        $idAni   = $salesMap['ani'] ?? null;
        $idYuni  = $salesMap['yuni'] ?? null;
        $idWinda = $salesMap['winda'] ?? null;
        $idNuri  = $salesMap['nuri'] ?? null;
        $idErlin = $salesMap['erlin'] ?? null;

        // 1. Backfill Yenny / Devoy
        if ($idYenny) {
            $db->query("
                UPDATE konsumen 
                SET sales = 'Yenny', keterangan_sales = 'DEVOY', id_sales = {$idYenny}
                WHERE LOWER(TRIM(sales)) IN ('yenny/devoy', 'yenny / devoy', 'devoy / yenny', 'devoy')
            ");

            // 2. Backfill Yenny / Diaz
            $db->query("
                UPDATE konsumen 
                SET sales = 'Yenny', keterangan_sales = 'DIAZ', id_sales = {$idYenny}
                WHERE LOWER(TRIM(sales)) IN ('yenny/diaz', 'yenny / diaz', 'yenny diaz')
            ");

            // 3. Backfill Yenny polos & Yunny
            $db->query("
                UPDATE konsumen 
                SET sales = 'Yenny', id_sales = {$idYenny}
                WHERE (LOWER(TRIM(sales)) IN ('yenny', 'yunny') OR sales LIKE 'Yenny %') AND (keterangan_sales IS NULL OR keterangan_sales = '')
            ");
        }

        // 4. Backfill Ani
        if ($idAni) {
            $db->query("
                UPDATE konsumen 
                SET sales = 'Ani', id_sales = {$idAni}
                WHERE LOWER(TRIM(sales)) = 'ani'
            ");
        }

        // 5. Backfill Yuni
        if ($idYuni) {
            $db->query("
                UPDATE konsumen 
                SET sales = 'Yuni', id_sales = {$idYuni}
                WHERE LOWER(TRIM(sales)) = 'yuni'
            ");
        }

        // 6. Backfill Winda
        if ($idWinda) {
            $db->query("
                UPDATE konsumen 
                SET sales = 'Winda', id_sales = {$idWinda}
                WHERE LOWER(TRIM(sales)) = 'winda'
            ");
        }

        // 7. Backfill Nuri
        if ($idNuri) {
            $db->query("
                UPDATE konsumen 
                SET sales = 'Nuri', id_sales = {$idNuri}
                WHERE LOWER(TRIM(sales)) = 'nuri'
            ");
        }

        // 8. Backfill Erlin
        if ($idErlin) {
            $db->query("
                UPDATE konsumen 
                SET sales = 'Erlin', keterangan_sales = 'YUSUP', id_sales = {$idErlin}
                WHERE LOWER(TRIM(sales)) = 'erlin/yusup'
            ");
            $db->query("
                UPDATE konsumen 
                SET sales = 'Erlin', id_sales = {$idErlin}
                WHERE LOWER(TRIM(sales)) = 'erlin'
            ");
        }

        // 9. Generic matching fallback: hubungkan sales yang belum ber-id_sales jika nama persis sama
        $db->query("
            UPDATE konsumen k
            INNER JOIN sales s ON LOWER(TRIM(k.sales)) = LOWER(TRIM(s.nama))
            SET k.id_sales = s.id, k.sales = s.nama
            WHERE k.id_sales IS NULL AND k.sales IS NOT NULL AND TRIM(k.sales) != ''
        ");
    }

    public function down()
    {
        // Data seeding dan backfill tidak di-rollback agar data transaksi dan relasi tetap utuh
    }
}

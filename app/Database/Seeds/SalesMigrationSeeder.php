<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SalesMigrationSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();
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
            $existing = $db->table('sales')->where('LOWER(nama)', strtolower($s['nama']))->get()->getRow();
            if ($existing) {
                $salesMap[strtolower($s['nama'])] = $existing->id;
            } else {
                $db->table('sales')->insert(array_merge($s, [
                    'created_at' => $now,
                    'updated_at' => $now,
                ]));
                $newId = $db->insertID();
                $salesMap[strtolower($s['nama'])] = $newId;

                // Log initial history
                $db->table('sales_history')->insert([
                    'id_sales'          => $newId,
                    'fee_subsidi_baru'  => $s['fee_subsidi'],
                    'fee_komersil_baru' => $s['fee_komersil'],
                    'tanggal_pks_baru'  => $s['tanggal_pks'],
                    'catatan'           => 'Inisialisasi Master Data Sales External dari Seeder',
                    'created_at'        => $now,
                ]);
            }
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
                WHERE LOWER(sales) IN ('yenny/devoy', 'yenny / devoy', 'devoy / yenny', 'devoy')
            ");

            // 2. Backfill Yenny / Diaz
            $db->query("
                UPDATE konsumen 
                SET sales = 'Yenny', keterangan_sales = 'DIAZ', id_sales = {$idYenny}
                WHERE LOWER(sales) IN ('yenny/diaz', 'yenny / diaz', 'yenny diaz')
            ");

            // 3. Backfill Yenny polos & Yunny
            $db->query("
                UPDATE konsumen 
                SET sales = 'Yenny', id_sales = {$idYenny}
                WHERE (LOWER(sales) IN ('yenny', 'yunny') OR sales LIKE 'Yenny %') AND (keterangan_sales IS NULL OR keterangan_sales = '')
            ");
        }

        // 4. Backfill Ani
        if ($idAni) {
            $db->query("
                UPDATE konsumen 
                SET sales = 'Ani', id_sales = {$idAni}
                WHERE LOWER(sales) = 'ani'
            ");
        }

        // 5. Backfill Yuni
        if ($idYuni) {
            $db->query("
                UPDATE konsumen 
                SET sales = 'Yuni', id_sales = {$idYuni}
                WHERE LOWER(sales) = 'yuni'
            ");
        }

        // 6. Backfill Winda
        if ($idWinda) {
            $db->query("
                UPDATE konsumen 
                SET sales = 'Winda', id_sales = {$idWinda}
                WHERE LOWER(sales) = 'winda'
            ");
        }

        // 7. Backfill Nuri
        if ($idNuri) {
            $db->query("
                UPDATE konsumen 
                SET sales = 'Nuri', id_sales = {$idNuri}
                WHERE LOWER(sales) = 'nuri'
            ");
        }

        // 8. Backfill Erlin
        if ($idErlin) {
            $db->query("
                UPDATE konsumen 
                SET sales = 'Erlin', keterangan_sales = 'YUSUP', id_sales = {$idErlin}
                WHERE LOWER(sales) = 'erlin/yusup'
            ");
            $db->query("
                UPDATE konsumen 
                SET sales = 'Erlin', id_sales = {$idErlin}
                WHERE LOWER(sales) = 'erlin'
            ");
        }
    }
}

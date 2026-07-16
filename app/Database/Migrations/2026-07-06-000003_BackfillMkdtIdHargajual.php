<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Backfill mkdt.id_hargajual dari kavling.harga_akhir untuk transaksi lama
 * yang belum punya id_hargajual (dibutuhkan agar join hargajual/tipe di
 * SPPTB print bisa resolve "Tipe" dari price list).
 */
class BackfillMkdtIdHargajual extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('mkdt') || ! $this->db->tableExists('kavling')) {
            return;
        }

        $this->db->query("
            UPDATE mkdt m
            INNER JOIN kavling k ON k.id_mkdt = m.id_mkdt
            SET m.id_hargajual = k.harga_akhir
            WHERE m.id_hargajual IS NULL
              AND k.harga_akhir IS NOT NULL
        ");
    }

    public function down()
    {
        // No-op: tidak ada penanda baris mana yang di-backfill vs yang memang
        // sudah diisi manual setelahnya, jadi set balik ke NULL berisiko
        // menghapus data yang valid. Backfill ini idempotent, aman dijalankan ulang.
    }
}

<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTanggalKunjunganToTiketMasalah extends Migration
{
    public function up()
    {
        // Add tanggal_kunjungan column if not exists
        if (!$this->db->fieldExists('tanggal_kunjungan', 'tiket_masalah')) {
            $this->forge->addColumn('tiket_masalah', [
                'tanggal_kunjungan' => [
                    'type'  => 'DATE',
                    'null'  => true,
                    'after' => 'tanggal_masalah'
                ],
            ]);
        }

        // Modify prioritas ENUM to include 'laporan'
        $this->db->query("ALTER TABLE tiket_masalah MODIFY COLUMN prioritas ENUM('urgent', 'medium', 'normal', 'low', 'laporan') DEFAULT 'normal'");
    }

    public function down()
    {
        // Drop tanggal_kunjungan column
        $this->forge->dropColumn('tiket_masalah', 'tanggal_kunjungan');

        // Revert prioritas ENUM (note: will fail if there are existing rows with 'laporan' priority)
        $this->db->query("ALTER TABLE tiket_masalah MODIFY COLUMN prioritas ENUM('urgent', 'medium', 'normal', 'low') DEFAULT 'normal'");
    }
}

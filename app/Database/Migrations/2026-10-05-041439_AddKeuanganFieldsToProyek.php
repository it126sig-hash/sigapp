<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddKeuanganFieldsToProyek extends Migration
{
    public function up()
    {
        // 1. Tambah kolom di table proyek
        $this->forge->addColumn('proyek', [
            'kode_keuangan' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'no_telepon'    => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'direktur_id'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
        ]);

        // 2. Isi data default untuk proyek yang sudah ada
        $db = \Config\Database::connect();
        $proyekList = [
            'SANGGAR INDAH PALASTRI' => 'SIP',
            'SANGGAR INDAH PARAHYANGAN' => 'SDP',
            'ALAM SANGGAR INDAH' => 'ASI',
            'ALAM SANGGAR INDAH Ext' => 'ASI',
            'BUMI KARYA INDAH' => 'BKI'
        ];

        foreach ($proyekList as $nama => $kode) {
            $db->table('proyek')
               ->where('nama_proyek', $nama)
               ->update([
                   'kode_keuangan' => $kode,
                   'no_telepon' => '082133557009'
               ]);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('proyek', ['kode_keuangan', 'no_telepon', 'direktur_id']);
    }
}

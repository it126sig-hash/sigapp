<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SyncUsersNameFromKaryawan extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('users') && $this->db->tableExists('karyawan')) {
            $this->db->query("
                UPDATE users
                JOIN karyawan ON karyawan.id_user = users.id
                SET users.name = karyawan.nama_karyawan
                WHERE karyawan.nama_karyawan IS NOT NULL AND karyawan.nama_karyawan != ''
            ");
        }
    }

    public function down()
    {
        // No revert action needed for data sync
    }
}

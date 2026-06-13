<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSuratPathToRiwayatPencairanJaminan extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('surat_path', 'riwayat_pencairan_jaminan')) {
            $this->forge->addColumn('riwayat_pencairan_jaminan', [
                'surat_path' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'after' => 'keterangan',
                ],
            ]);
        }

        if ($this->db->fieldExists('file_path', 'riwayat_pencairan_jaminan')) {
            $this->db->query(
                "UPDATE riwayat_pencairan_jaminan
                 SET surat_path = file_path
                 WHERE (surat_path IS NULL OR surat_path = '')
                 AND file_path IS NOT NULL
                 AND file_path != ''"
            );
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('surat_path', 'riwayat_pencairan_jaminan')) {
            $this->forge->dropColumn('riwayat_pencairan_jaminan', 'surat_path');
        }
    }
}

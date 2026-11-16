<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTipeManualAndAlasanTolakToKavlingRequest extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('kavling_request')) {
            return;
        }

        // Pastikan id_tipe nullable agar request dengan tipe_manual dapat disimpan
        $this->forge->modifyColumn('kavling_request', [
            'id_tipe' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
            ],
        ]);

        if (! $this->db->fieldExists('tipe_manual', 'kavling_request')) {
            $this->forge->addColumn('kavling_request', [
                'tipe_manual' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                    'default'    => null,
                    'after'      => 'id_tipe',
                ],
            ]);
        }

        if (! $this->db->fieldExists('alasan_tolak', 'kavling_request')) {
            $this->forge->addColumn('kavling_request', [
                'alasan_tolak' => [
                    'type'    => 'TEXT',
                    'null'    => true,
                    'default' => null,
                    'after'   => 'status',
                ],
            ]);
        }
    }

    public function down()
    {
        if (! $this->db->tableExists('kavling_request')) {
            return;
        }

        if ($this->db->fieldExists('alasan_tolak', 'kavling_request')) {
            $this->forge->dropColumn('kavling_request', 'alasan_tolak');
        }

        if ($this->db->fieldExists('tipe_manual', 'kavling_request')) {
            $this->forge->dropColumn('kavling_request', 'tipe_manual');
        }
    }
}

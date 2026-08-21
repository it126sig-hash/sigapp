<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPinToTiketMasalahProgress extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tiket_masalah_progress', [
            'is_pin_requested' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'null' => false,
                'after' => 'status_sesudah'
            ],
            'is_pinned' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'null' => false,
                'after' => 'is_pin_requested'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tiket_masalah_progress', ['is_pin_requested', 'is_pinned']);
    }
}

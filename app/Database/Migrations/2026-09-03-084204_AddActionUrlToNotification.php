<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddActionUrlToNotification extends Migration
{
    public function up()
    {
        $this->forge->addColumn('notification', [
            'action_url' => [
                'type' => 'VARCHAR',
                'constraint' => '500',
                'null' => true,
                'default' => null,
                'after' => 'id_proyek'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('notification', 'action_url');
    }
}

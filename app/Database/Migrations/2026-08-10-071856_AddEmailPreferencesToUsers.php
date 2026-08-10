<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEmailPreferencesToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'email_notif_enabled' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'after'      => 'email',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'email_notif_enabled');
    }
}

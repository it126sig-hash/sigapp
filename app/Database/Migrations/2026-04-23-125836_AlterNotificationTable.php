<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterNotificationTable extends Migration
{
    public function up()
    {
        $fields = [
            'type' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
                'after'      => 'group_target'
            ],
            'is_read' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'after'      => 'type'
            ]
        ];
        $this->forge->addColumn('notification', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('notification', ['type', 'is_read']);
    }
}

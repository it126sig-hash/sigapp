<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNotificationEventTypesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'event_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'category' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'label' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'description' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'default_in_app' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'unsigned'   => true,
                'default'    => 1,
            ],
            'default_email' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'unsigned'   => true,
                'default'    => 1,
            ],
            'default_web_push' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'unsigned'   => true,
                'default'    => 1,
            ],
            'is_mandatory' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'unsigned'   => true,
                'default'    => 0,
            ],
            'relevant_groups' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'Semicolon-separated group IDs, NULL = all groups',
            ],
            'sort_order' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'default'    => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('event_type', true);
        $this->forge->createTable('notification_event_types', true);
    }

    public function down()
    {
        $this->forge->dropTable('notification_event_types', true);
    }
}

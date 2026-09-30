<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserNotificationPreferencesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'event_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'in_app' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'email' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'web_push' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'is_locked' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'comment'    => 'If 1, admin locked this preference and user cannot change it',
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

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['user_id', 'event_type'], 'uniq_user_event');
        $this->forge->addKey('user_id', false, false, 'idx_user_id');
        $this->forge->addForeignKey('event_type', 'notification_event_types', 'event_type', 'CASCADE', 'CASCADE');

        $this->forge->createTable('user_notification_preferences', true);
    }

    public function down()
    {
        $this->forge->dropTable('user_notification_preferences', true);
    }
}

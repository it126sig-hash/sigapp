<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNotificationEmailQueueTable extends Migration
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
            'notification_id' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'target_group' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
            ],
            'target_user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'actor_user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'sent', 'failed'],
                'default'    => 'pending',
            ],
            'batch_id' => [
                'type'       => 'VARCHAR',
                'constraint' => '36',
                'null'       => true,
            ],
            'processed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['status', 'created_at']);
        $this->forge->addKey('batch_id');
        $this->forge->createTable('notification_email_queue', true);
    }

    public function down()
    {
        $this->forge->dropTable('notification_email_queue', true);
    }
}

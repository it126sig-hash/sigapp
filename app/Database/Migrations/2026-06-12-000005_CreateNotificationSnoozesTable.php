<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNotificationSnoozesTable extends Migration
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
            'item_key' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'item_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'id_proyek' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'snoozed_until' => [
                'type' => 'DATETIME',
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
        $this->forge->addKey(['user_id', 'id_proyek']);
        $this->forge->addKey('snoozed_until');
        $this->forge->addUniqueKey(['user_id', 'id_proyek', 'item_key'], 'notification_snoozes_user_project_item_unique');
        $this->forge->createTable('notification_snoozes', true);
    }

    public function down()
    {
        $this->forge->dropTable('notification_snoozes', true);
    }
}

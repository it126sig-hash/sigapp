<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCopilotTables extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('copilot_settings')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 10,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'setting_key' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 64,
                ],
                'setting_value' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'updated_by' => [
                    'type'       => 'INT',
                    'constraint' => 10,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey('setting_key');
            $this->forge->createTable('copilot_settings', true);
        }

        if (! $this->db->tableExists('copilot_telegram_links')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 10,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'user_id' => [
                    'type'       => 'INT',
                    'constraint' => 10,
                    'unsigned'   => true,
                ],
                'telegram_user_id' => [
                    'type'       => 'BIGINT',
                    'constraint' => 20,
                    'null'       => true,
                ],
                'chat_id' => [
                    'type'       => 'BIGINT',
                    'constraint' => 20,
                    'null'       => true,
                ],
                'telegram_username' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                ],
                'code_hash' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 128,
                    'null'       => true,
                ],
                'code_expires_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'verified_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
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
            $this->forge->addUniqueKey('user_id');
            $this->forge->addUniqueKey('telegram_user_id');
            $this->forge->createTable('copilot_telegram_links', true);
        }

        if (! $this->db->tableExists('copilot_messages')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'BIGINT',
                    'constraint'     => 20,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'user_id' => [
                    'type'       => 'INT',
                    'constraint' => 10,
                    'unsigned'   => true,
                ],
                'channel' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                ],
                'role' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                ],
                'content' => [
                    'type' => 'MEDIUMTEXT',
                ],
                'telegram_update_id' => [
                    'type'       => 'BIGINT',
                    'constraint' => 20,
                    'null'       => true,
                ],
                'metadata_json' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey('telegram_update_id');
            $this->forge->addKey(['user_id', 'channel', 'created_at']);
            $this->forge->addKey('created_at');
            $this->forge->createTable('copilot_messages', true);
        }
    }

    public function down()
    {
        $this->forge->dropTable('copilot_messages', true);
        $this->forge->dropTable('copilot_telegram_links', true);
        $this->forge->dropTable('copilot_settings', true);
    }
}

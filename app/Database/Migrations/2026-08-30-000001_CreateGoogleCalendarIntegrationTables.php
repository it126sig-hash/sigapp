<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateGoogleCalendarIntegrationTables extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('google_calendar_connections')) {
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
                'google_email' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'calendar_id' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'default'    => 'primary',
                ],
                'access_token_enc' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'refresh_token_enc' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'token_expires_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'connected_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'disconnected_at' => [
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
            $this->forge->addKey('user_id', false, true, 'uniq_google_calendar_connections_user');
            $this->forge->createTable('google_calendar_connections', true);
        }

        if (! $this->db->tableExists('google_calendar_event_syncs')) {
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
                'id_tiket_masalah' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'notification_email_queue_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'google_event_id' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'event_start_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'status' => [
                    'type'       => 'ENUM',
                    'constraint' => ['created', 'skipped', 'failed'],
                    'default'    => 'created',
                ],
                'error_message' => [
                    'type' => 'TEXT',
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
            $this->forge->addKey(['user_id', 'id_tiket_masalah'], false, true, 'uniq_google_calendar_ticket_user');
            $this->forge->addKey(['status', 'created_at']);
            $this->forge->createTable('google_calendar_event_syncs', true);
        }
    }

    public function down()
    {
        $this->forge->dropTable('google_calendar_event_syncs', true);
        $this->forge->dropTable('google_calendar_connections', true);
    }
}

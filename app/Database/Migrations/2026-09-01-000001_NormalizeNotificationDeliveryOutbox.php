<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class NormalizeNotificationDeliveryOutbox extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('id_proyek', 'notification')) {
            $this->forge->addColumn('notification', [
                'id_proyek' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => true,
                    'after' => 'id_konsumen',
                ],
            ]);
        }

        $this->createRecipientsTable();
        $this->createDeliveriesTable();
        $this->upgradePushSubscriptions();
        $this->backfillRecipients();
    }

    public function down()
    {
        $this->forge->dropTable('notification_deliveries', true);
        $this->forge->dropTable('notification_recipients', true);

        if ($this->db->tableExists('push_subscriptions')) {
            if ($this->indexExists('push_subscriptions', 'idx_push_endpoint_hash')) {
                $this->db->query('ALTER TABLE push_subscriptions DROP INDEX idx_push_endpoint_hash');
            }

            foreach (['endpoint_hash', 'last_seen_at', 'disabled_at', 'failure_count'] as $field) {
                if ($this->db->fieldExists($field, 'push_subscriptions')) {
                    $this->forge->dropColumn('push_subscriptions', $field);
                }
            }
        }
    }

    private function createRecipientsTable(): void
    {
        if ($this->db->tableExists('notification_recipients')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'notification_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'read_at' => [
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
        $this->forge->addKey(['notification_id', 'user_id']);
        $this->forge->addKey(['user_id', 'read_at', 'notification_id'], false, false, 'idx_notif_recip_user_read');
        $this->forge->createTable('notification_recipients', true);

        if (! $this->indexExists('notification_recipients', 'uniq_notif_recip_user')) {
            $this->db->query('ALTER TABLE notification_recipients ADD UNIQUE KEY uniq_notif_recip_user (notification_id, user_id)');
        }
    }

    private function createDeliveriesTable(): void
    {
        if ($this->db->tableExists('notification_deliveries')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'notification_recipient_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'notification_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'channel' => [
                'type' => 'ENUM',
                'constraint' => ['web_push', 'email'],
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['pending', 'processing', 'sent', 'failed', 'skipped'],
                'default' => 'pending',
            ],
            'attempts' => [
                'type' => 'TINYINT',
                'constraint' => 3,
                'unsigned' => true,
                'default' => 0,
            ],
            'available_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'processed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'claimed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'claim_token' => [
                'type' => 'VARCHAR',
                'constraint' => 64,
                'null' => true,
            ],
            'last_error' => [
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
        $this->forge->addKey(['channel', 'status', 'available_at', 'id'], false, false, 'idx_notif_delivery_queue');
        $this->forge->addKey(['notification_id', 'user_id'], false, false, 'idx_notif_delivery_notif_user');
        $this->forge->createTable('notification_deliveries', true);

        if (! $this->indexExists('notification_deliveries', 'uniq_notif_delivery_recipient_channel')) {
            $this->db->query('ALTER TABLE notification_deliveries ADD UNIQUE KEY uniq_notif_delivery_recipient_channel (notification_recipient_id, channel)');
        }
    }

    private function upgradePushSubscriptions(): void
    {
        if (! $this->db->tableExists('push_subscriptions')) {
            return;
        }

        $fields = [];
        if (! $this->db->fieldExists('endpoint_hash', 'push_subscriptions')) {
            $fields['endpoint_hash'] = [
                'type' => 'CHAR',
                'constraint' => 64,
                'null' => true,
                'after' => 'endpoint',
            ];
        }
        if (! $this->db->fieldExists('last_seen_at', 'push_subscriptions')) {
            $fields['last_seen_at'] = [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'user_agent',
            ];
        }
        if (! $this->db->fieldExists('disabled_at', 'push_subscriptions')) {
            $fields['disabled_at'] = [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'last_seen_at',
            ];
        }
        if (! $this->db->fieldExists('failure_count', 'push_subscriptions')) {
            $fields['failure_count'] = [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 0,
                'after' => 'disabled_at',
            ];
        }

        if ($fields !== []) {
            $this->forge->addColumn('push_subscriptions', $fields);
        }

        $this->db->query("UPDATE push_subscriptions SET endpoint_hash = SHA2(endpoint, 256), last_seen_at = COALESCE(last_seen_at, updated_at, created_at, NOW()) WHERE endpoint_hash IS NULL AND endpoint IS NOT NULL AND endpoint <> ''");
        $this->db->query("UPDATE push_subscriptions ps JOIN (SELECT endpoint, MAX(id) AS keep_id FROM push_subscriptions WHERE endpoint IS NOT NULL AND endpoint <> '' GROUP BY endpoint HAVING COUNT(*) > 1) dup ON dup.endpoint = ps.endpoint SET ps.disabled_at = COALESCE(ps.disabled_at, NOW()), ps.endpoint_hash = NULL, ps.updated_at = NOW() WHERE ps.id <> dup.keep_id");

        if (! $this->indexExists('push_subscriptions', 'idx_push_endpoint_hash')) {
            $this->db->query('ALTER TABLE push_subscriptions ADD UNIQUE KEY idx_push_endpoint_hash (endpoint_hash)');
        }
    }

    private function backfillRecipients(): void
    {
        if (! $this->db->tableExists('notification_recipients')) {
            return;
        }

        $readAt = "CASE WHEN COALESCE(n.is_read, 0) = 1 THEN COALESCE(n.created_at, NOW()) ELSE NULL END";
        $createdAt = 'COALESCE(n.created_at, NOW())';

        $this->db->query("INSERT IGNORE INTO notification_recipients (notification_id, user_id, read_at, created_at, updated_at) SELECT n.id, n.add_by, {$readAt}, {$createdAt}, NOW() FROM notification n JOIN users u ON u.id = n.add_by AND u.active = 1 AND u.deleted_at IS NULL WHERE n.add_by IS NOT NULL AND n.add_by > 0");
        $this->db->query("INSERT IGNORE INTO notification_recipients (notification_id, user_id, read_at, created_at, updated_at) SELECT n.id, n.user_id, {$readAt}, {$createdAt}, NOW() FROM notification n JOIN users u ON u.id = n.user_id AND u.active = 1 AND u.deleted_at IS NULL WHERE n.user_id IS NOT NULL AND n.user_id > 0");
        $this->db->query("INSERT IGNORE INTO notification_recipients (notification_id, user_id, read_at, created_at, updated_at) SELECT n.id, agu.user_id, {$readAt}, {$createdAt}, NOW() FROM notification n JOIN auth_groups_users agu ON FIND_IN_SET(agu.group_id, REPLACE(n.group_target, ';', ',')) > 0 JOIN users u ON u.id = agu.user_id AND u.active = 1 AND u.deleted_at IS NULL WHERE n.group_target IS NOT NULL AND n.group_target <> '' AND n.group_target <> '0'");
        $this->db->query("INSERT IGNORE INTO notification_recipients (notification_id, user_id, read_at, created_at, updated_at) SELECT n.id, u.id, {$readAt}, {$createdAt}, NOW() FROM notification n JOIN users u ON u.active = 1 AND u.deleted_at IS NULL WHERE n.group_target = '0'");
        $this->db->query("INSERT IGNORE INTO notification_recipients (notification_id, user_id, read_at, created_at, updated_at) SELECT n.id, agu.user_id, {$readAt}, {$createdAt}, NOW() FROM notification n JOIN auth_groups_users agu ON agu.group_id = 1 JOIN users u ON u.id = agu.user_id AND u.active = 1 AND u.deleted_at IS NULL");
    }

    private function indexExists(string $table, string $indexName): bool
    {
        return $this->db->query("SHOW INDEX FROM {$this->db->protectIdentifiers($table)} WHERE Key_name = ?", [$indexName])
            ->getNumRows() > 0;
    }
}

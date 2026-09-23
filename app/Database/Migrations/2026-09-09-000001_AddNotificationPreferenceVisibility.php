<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNotificationPreferenceVisibility extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('notification_recipients') && ! $this->db->fieldExists('in_app_visible', 'notification_recipients')) {
            $this->forge->addColumn('notification_recipients', [
                'in_app_visible' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'unsigned' => true,
                    'default' => 1,
                    'after' => 'user_id',
                ],
            ]);
        }

        if ($this->db->tableExists('notification_recipients') && ! $this->indexExists('notification_recipients', 'idx_notif_recip_user_visible_read')) {
            $this->db->query('ALTER TABLE notification_recipients ADD INDEX idx_notif_recip_user_visible_read (user_id, in_app_visible, read_at, notification_id)');
        }

        if ($this->db->tableExists('notification_deliveries')) {
            $this->db->query("ALTER TABLE notification_deliveries MODIFY status ENUM('pending','processing','sent','failed','skipped','preference_blocked') NOT NULL DEFAULT 'pending'");
        }
    }

    public function down()
    {
        if ($this->db->tableExists('notification_deliveries')) {
            $this->db->query("UPDATE notification_deliveries SET status = 'skipped' WHERE status = 'preference_blocked'");
            $this->db->query("ALTER TABLE notification_deliveries MODIFY status ENUM('pending','processing','sent','failed','skipped') NOT NULL DEFAULT 'pending'");
        }

        if ($this->db->tableExists('notification_recipients')) {
            if ($this->indexExists('notification_recipients', 'idx_notif_recip_user_visible_read')) {
                $this->db->query('ALTER TABLE notification_recipients DROP INDEX idx_notif_recip_user_visible_read');
            }

            if ($this->db->fieldExists('in_app_visible', 'notification_recipients')) {
                $this->forge->dropColumn('notification_recipients', 'in_app_visible');
            }
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        return $this->db->query("SHOW INDEX FROM {$this->db->protectIdentifiers($table)} WHERE Key_name = ?", [$indexName])
            ->getNumRows() > 0;
    }
}

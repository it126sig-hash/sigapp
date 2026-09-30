<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSkippedStatusToNotificationEmailQueue extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('notification_email_queue') || ! $this->db->fieldExists('status', 'notification_email_queue')) {
            return;
        }

        $this->forge->modifyColumn('notification_email_queue', [
            'status' => [
                'name' => 'status',
                'type' => 'ENUM',
                'constraint' => ['pending', 'sent', 'failed', 'skipped'],
                'default' => 'pending',
            ],
        ]);
    }

    public function down()
    {
        if (! $this->db->tableExists('notification_email_queue') || ! $this->db->fieldExists('status', 'notification_email_queue')) {
            return;
        }

        $this->db->table('notification_email_queue')
            ->where('status', 'skipped')
            ->update(['status' => 'sent']);

        $this->forge->modifyColumn('notification_email_queue', [
            'status' => [
                'name' => 'status',
                'type' => 'ENUM',
                'constraint' => ['pending', 'sent', 'failed'],
                'default' => 'pending',
            ],
        ]);
    }
}

<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\Database;

class SyncNotificationEventTypesFromSeeder extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('notification_event_types')) {
            return;
        }

        Database::seeder()->call('NotificationEventTypeSeeder');
    }

    public function down()
    {
        // Registry event tidak dihapus saat rollback agar preferensi user tetap aman.
    }
}

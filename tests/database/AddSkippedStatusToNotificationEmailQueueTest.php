<?php

require_once APPPATH . 'Database/Migrations/2026-09-02-000001_AddSkippedStatusToNotificationEmailQueue.php';

use App\Database\Migrations\AddSkippedStatusToNotificationEmailQueue;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

final class AddSkippedStatusToNotificationEmailQueueTest extends CIUnitTestCase
{
    private BaseConnection $testDb;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testDb = Database::connect([
            'DBDriver' => 'SQLite3',
            'database' => ':memory:',
            'DBPrefix' => '',
            'DBDebug' => true,
            'foreignKeys' => false,
        ], false);
        $this->testDb->query('CREATE TABLE notification_email_queue (id INTEGER PRIMARY KEY, status TEXT NOT NULL DEFAULT "pending")');
    }

    protected function tearDown(): void
    {
        $this->testDb->close();
        parent::tearDown();
    }

    public function testMigrationSupportsSkippedAndMapsItToSentOnRollback(): void
    {
        $migration = new AddSkippedStatusToNotificationEmailQueue(Database::forge($this->testDb));

        $migration->up();
        $this->testDb->table('notification_email_queue')->insert(['id' => 1, 'status' => 'skipped']);
        $this->assertSame('skipped', $this->queueStatus(1));

        $migration->down();
        $this->assertSame('sent', $this->queueStatus(1));
    }

    private function queueStatus(int $id): string
    {
        return (string) $this->testDb->table('notification_email_queue')
            ->select('status')
            ->where('id', $id)
            ->get()
            ->getRow()
            ->status;
    }
}

<?php

use App\Services\EmailDigestService;
use App\Services\GoogleCalendarService;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Email\Email;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;
use Config\Email as EmailConfig;

final class EmailDigestServiceTest extends CIUnitTestCase
{
    private BaseConnection $testDb;
    private TestableEmailDigestService $service;

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
        $this->createTables();

        $calendar = $this->getMockBuilder(GoogleCalendarService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->service = new TestableEmailDigestService(
            $this->testDb,
            new Email(new EmailConfig()),
            $calendar
        );

        $this->insertUser(1, 'actor@example.test', 'actor');
        $this->insertUser(2, 'recipient@example.test', 'recipient');
        $this->testDb->table('auth_groups')->insert(['id' => 3, 'name' => 'QA', 'description' => 'Pengujian']);
        $this->testDb->table('auth_groups_users')->insert(['user_id' => 1, 'group_id' => 3]);
    }

    protected function tearDown(): void
    {
        $this->testDb->close();
        parent::tearDown();
    }

    public function testDeliveryDigestSendsOnlyUnreadItems(): void
    {
        $this->insertNotification(101, 'Status &amp; <strong>aman</strong>', 0);
        $this->insertNotification(102, 'Sudah dibaca', 0);
        $this->insertRecipient(201, 101, 2, null);
        $this->insertRecipient(202, 102, 2, '2026-09-02 09:00:00');
        $this->insertDelivery(301, 201, 101, 2, 'pending');
        $this->insertDelivery(302, 202, 102, 2, 'pending');

        $stats = $this->service->processQueue();

        $this->assertSame(1, $stats['emails_sent']);
        $this->assertSame(1, $stats['queues_sent']);
        $this->assertSame(1, $stats['queues_skipped']);
        $this->assertSame([101], $this->service->sentNotificationIds);
        $this->assertSame(['Status & aman'], $this->service->sentNotificationTexts);
        $this->assertSame([101], $this->service->calendarNotificationIds);
        $this->assertSame('sent', $this->deliveryStatus(301));
        $this->assertSame('skipped', $this->deliveryStatus(302));
    }

    public function testFailedDeliveryIsRetriedWithoutCalendarSync(): void
    {
        $this->service->sendResult = false;
        $this->insertNotification(103, 'Akan gagal', 0);
        $this->insertRecipient(203, 103, 2, null);
        $this->insertDelivery(303, 203, 103, 2, 'pending');

        $stats = $this->service->processQueue();
        $delivery = $this->testDb->table('notification_deliveries')->where('id', 303)->get()->getRow();

        $this->assertSame(1, $stats['emails_failed']);
        $this->assertSame(1, $stats['queues_failed']);
        $this->assertSame('failed', $delivery->status);
        $this->assertSame(1, (int) $delivery->attempts);
        $this->assertSame([], $this->service->calendarNotificationIds);
    }

    public function testLegacyFallbackSkipsReadAndOutboxCoveredRows(): void
    {
        $this->insertNotification(201, 'Sudah punya outbox', 0);
        $this->insertNotification(202, 'Legacy <strong>belum dibaca</strong>', 0);
        $this->insertNotification(203, 'Legacy sudah dibaca', 0);
        $this->insertRecipient(401, 201, 2, null);
        $this->insertRecipient(402, 202, 2, null);
        $this->insertRecipient(403, 203, 2, '2026-09-02 09:00:00');
        $this->insertDelivery(501, 401, 201, 2, 'sent');
        $this->insertLegacyQueue(601, 201, 2);
        $this->insertLegacyQueue(602, 202, 2);
        $this->insertLegacyQueue(603, 203, 2);

        $stats = $this->service->processQueue();

        $this->assertSame(1, $stats['emails_sent']);
        $this->assertSame(1, $stats['queues_sent']);
        $this->assertSame(2, $stats['queues_skipped']);
        $this->assertSame([202], $this->service->sentNotificationIds);
        $this->assertSame(['Legacy belum dibaca'], $this->service->sentNotificationTexts);
        $this->assertSame('skipped', $this->legacyStatus(601));
        $this->assertSame('sent', $this->legacyStatus(602));
        $this->assertSame('skipped', $this->legacyStatus(603));
    }

    public function testLegacyFallbackUsesNotificationReadFlagWithoutRecipientTables(): void
    {
        $this->testDb->query('DROP TABLE notification_deliveries');
        $this->testDb->query('DROP TABLE notification_recipients');
        $this->insertNotification(301, 'Legacy unread', 0);
        $this->insertNotification(302, 'Legacy read', 1);
        $this->insertLegacyQueue(701, 301, 2);
        $this->insertLegacyQueue(702, 302, 2);

        $stats = $this->service->processQueue();

        $this->assertSame(1, $stats['emails_sent']);
        $this->assertSame([301], $this->service->sentNotificationIds);
        $this->assertSame('sent', $this->legacyStatus(701));
        $this->assertSame('skipped', $this->legacyStatus(702));
    }

    private function createTables(): void
    {
        $queries = [
            'CREATE TABLE users (id INTEGER PRIMARY KEY, email TEXT, username TEXT, name TEXT, email_notif_enabled INTEGER, active INTEGER, deleted_at TEXT)',
            'CREATE TABLE auth_groups (id INTEGER PRIMARY KEY, name TEXT, description TEXT)',
            'CREATE TABLE auth_groups_users (user_id INTEGER, group_id INTEGER)',
            'CREATE TABLE proyek (id_proyek INTEGER PRIMARY KEY, nama_proyek TEXT)',
            'CREATE TABLE kavling (id_kavling INTEGER PRIMARY KEY, no_kavling TEXT)',
            'CREATE TABLE notification (id INTEGER PRIMARY KEY, notif TEXT, type TEXT, is_read INTEGER, created_at TEXT, add_by INTEGER, id_proyek INTEGER, id_kavling INTEGER)',
            'CREATE TABLE notification_recipients (id INTEGER PRIMARY KEY, notification_id INTEGER, user_id INTEGER, read_at TEXT)',
            'CREATE TABLE notification_deliveries (id INTEGER PRIMARY KEY, notification_recipient_id INTEGER, notification_id INTEGER, user_id INTEGER, channel TEXT, status TEXT, attempts INTEGER, available_at TEXT, processed_at TEXT, last_error TEXT, updated_at TEXT)',
            'CREATE TABLE notification_email_queue (id INTEGER PRIMARY KEY, notification_id INTEGER, target_group TEXT, target_user_id INTEGER, actor_user_id INTEGER, status TEXT, batch_id TEXT, processed_at TEXT, created_at TEXT)',
        ];

        foreach ($queries as $query) {
            $this->testDb->query($query);
        }
    }

    private function insertUser(int $id, string $email, string $username): void
    {
        $this->testDb->table('users')->insert([
            'id' => $id,
            'email' => $email,
            'username' => $username,
            'name' => ucfirst($username),
            'email_notif_enabled' => 1,
            'active' => 1,
            'deleted_at' => null,
        ]);
    }

    private function insertNotification(int $id, string $message, int $isRead): void
    {
        $this->testDb->table('notification')->insert([
            'id' => $id,
            'notif' => $message,
            'type' => null,
            'is_read' => $isRead,
            'created_at' => '2026-09-02 10:00:00',
            'add_by' => 1,
            'id_proyek' => null,
            'id_kavling' => null,
        ]);
    }

    private function insertRecipient(int $id, int $notificationId, int $userId, ?string $readAt): void
    {
        $this->testDb->table('notification_recipients')->insert([
            'id' => $id,
            'notification_id' => $notificationId,
            'user_id' => $userId,
            'read_at' => $readAt,
        ]);
    }

    private function insertDelivery(int $id, int $recipientId, int $notificationId, int $userId, string $status): void
    {
        $this->testDb->table('notification_deliveries')->insert([
            'id' => $id,
            'notification_recipient_id' => $recipientId,
            'notification_id' => $notificationId,
            'user_id' => $userId,
            'channel' => 'email',
            'status' => $status,
            'attempts' => 0,
            'available_at' => '2026-09-02 00:00:00',
            'processed_at' => null,
            'last_error' => null,
            'updated_at' => '2026-09-02 00:00:00',
        ]);
    }

    private function insertLegacyQueue(int $id, int $notificationId, int $userId): void
    {
        $this->testDb->table('notification_email_queue')->insert([
            'id' => $id,
            'notification_id' => $notificationId,
            'target_group' => null,
            'target_user_id' => $userId,
            'actor_user_id' => 1,
            'status' => 'pending',
            'batch_id' => null,
            'processed_at' => null,
            'created_at' => '2026-09-02 10:00:00',
        ]);
    }

    private function deliveryStatus(int $id): string
    {
        return (string) $this->testDb->table('notification_deliveries')->select('status')->where('id', $id)->get()->getRow()->status;
    }

    private function legacyStatus(int $id): string
    {
        return (string) $this->testDb->table('notification_email_queue')->select('status')->where('id', $id)->get()->getRow()->status;
    }
}

final class TestableEmailDigestService extends EmailDigestService
{
    public bool $sendResult = true;
    public array $sentNotificationIds = [];
    public array $sentNotificationTexts = [];
    public array $calendarNotificationIds = [];

    protected function sendDigestEmail($user, $items): bool
    {
        foreach ($items as $projectItems) {
            foreach ($projectItems as $item) {
                $this->sentNotificationIds[] = (int) $item->notification_id;
                $this->sentNotificationTexts[] = (string) $item->notif_text;
            }
        }

        return $this->sendResult;
    }

    protected function syncGoogleCalendarForUser($user, array $items, string $sentAt): array
    {
        foreach ($items as $item) {
            $this->calendarNotificationIds[] = (int) $item->notification_id;
        }

        return ['created' => count($items), 'skipped' => 0, 'failed' => 0];
    }
}

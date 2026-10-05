<?php

require_once APPPATH . 'Database/Migrations/2026-09-30-000001_CreateAuthDeviceSessions.php';

use App\Database\Migrations\CreateAuthDeviceSessions;
use App\Repositories\DeviceSessionRepository;
use App\Services\DeviceManagementException;
use App\Services\DeviceManagementService;
use App\Services\DeviceSessionService;
use App\Services\UserAgentDeviceParser;
use App\Models\PushSubscriptionModel;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;
use Myth\Auth\Password;

final class DeviceManagementServiceTest extends CIUnitTestCase
{
    private BaseConnection $testDb;
    private DeviceSessionService $deviceSessions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testDb = Database::connect([
            'DBDriver' => 'SQLite3',
            'database' => ':memory:',
            'DBPrefix' => '',
            'DBDebug' => true,
            'foreignKeys' => true,
        ], false);
        $this->createAuthTables();
        $this->createPushSubscriptionsTable();
        (new CreateAuthDeviceSessions(Database::forge($this->testDb)))->up();
        $this->testDb->table('users')->insert(['id' => 10, 'password_hash' => Password::hash('correct-password')]);
        $this->testDb->table('users')->insert(['id' => 20, 'password_hash' => Password::hash('other-password')]);
        $this->testDb->table('users')->insert(['id' => 30, 'password_hash' => Password::hash('admin-password')]);

        $now = new DateTimeImmutable('2026-09-30 10:00:00');
        $this->deviceSessions = new DeviceSessionService(
            new DeviceSessionRepository($this->testDb),
            new UserAgentDeviceParser(),
            static fn (): DateTimeImmutable => $now,
        );
    }

    protected function tearDown(): void
    {
        $this->testDb->close();
        parent::tearDown();
    }

    public function testSelfRevokeRequiresPasswordAndNeverLeaksCrossUserDevice(): void
    {
        $own = $this->register(10, 'own-token', 'own-selector');
        $other = $this->register(20, 'other-token', 'other-selector');
        $service = $this->managementService(false);

        try {
            $service->revokeOwn(10, $own['id'], 'wrong-password', 'own-token');
            $this->fail('Password salah harus ditolak.');
        } catch (DeviceManagementException $e) {
            $this->assertSame(401, $e->statusCode());
        }
        $this->assertNotNull($this->deviceSessions->resolveActive(10, 'own-token'));

        try {
            $service->revokeOwn(10, $other['id'], 'correct-password', 'own-token');
            $this->fail('Perangkat milik user lain harus tampak tidak ditemukan.');
        } catch (DeviceManagementException $e) {
            $this->assertSame(404, $e->statusCode());
        }
        $this->assertNotNull($this->deviceSessions->resolveActive(20, 'other-token'));

        $result = $service->revokeOwn(10, $own['id'], 'correct-password', 'own-token');
        $this->assertTrue($result['current_device_revoked']);
        $this->assertNull($this->deviceSessions->resolveActive(10, 'own-token'));
    }

    public function testSelfListAndRevokeOthersRequireAndPreserveActiveCurrentDevice(): void
    {
        $current = $this->register(10, 'current-token', 'current-selector');
        $this->register(10, 'other-token', 'other-selector');
        $service = $this->managementService(false);

        $devices = $service->listOwn(10, 'current-token');
        $this->assertCount(2, $devices);
        $this->assertSame($current['id'], array_values(array_filter($devices, static fn (array $row): bool => $row['is_current']))[0]['id']);

        $this->assertSame(1, $service->revokeOthers(10, 'current-token', 'correct-password'));
        $this->assertNotNull($this->deviceSessions->resolveActive(10, 'current-token'));
        $this->assertNull($this->deviceSessions->resolveActive(10, 'other-token'));
    }

    public function testAdminPermissionReasonOwnershipAndRevokeAllAreEnforced(): void
    {
        $first = $this->register(10, 'target-a', 'selector-a');
        $this->register(10, 'target-b', 'selector-b');
        $foreign = $this->register(20, 'foreign', 'selector-foreign');

        try {
            $this->managementService(false)->listForAdmin(30, 10);
            $this->fail('Admin tanpa permission harus ditolak.');
        } catch (DeviceManagementException $e) {
            $this->assertSame(403, $e->statusCode());
        }

        $service = $this->managementService(true);
        try {
            $service->revokeForAdmin(30, 10, $first['id'], '   ');
            $this->fail('Alasan audit wajib diisi.');
        } catch (DeviceManagementException $e) {
            $this->assertSame(422, $e->statusCode());
        }

        try {
            $service->revokeForAdmin(30, 10, $foreign['id'], 'security incident');
            $this->fail('ID perangkat lintas user harus tampak tidak ditemukan.');
        } catch (DeviceManagementException $e) {
            $this->assertSame(404, $e->statusCode());
        }

        $this->assertTrue($service->revokeForAdmin(30, 10, $first['id'], 'security incident'));
        $revoked = $this->testDb->table('auth_device_sessions')->where('id', $first['id'])->get()->getRowArray();
        $this->assertSame(30, (int) $revoked['revoked_by']);
        $this->assertSame('security incident', $revoked['revoke_reason']);
        $this->assertSame(1, $service->revokeAllForAdmin(30, 10, 'account compromised'));
        $this->assertSame(0, $this->testDb->table('auth_device_sessions')->where(['user_id' => 10, 'revoked_at' => null])->countAllResults());
        $this->assertNotNull($this->deviceSessions->resolveActive(20, 'foreign'));
    }

    public function testRevokingDeviceDisablesItsPushSubscription(): void
    {
        $this->assertTrue($this->testDb->fieldExists('device_session_id', 'push_subscriptions'));
        $device = $this->register(10, 'push-device-token', 'push-device-selector');
        $this->testDb->table('push_subscriptions')->insert([
            'user_id' => 10,
            'device_session_id' => $device['id'],
            'endpoint' => 'https://push.example/device',
            'disabled_at' => null,
        ]);

        $this->deviceSessions->revokeOne(10, (int) $device['id'], 10, 'security test');

        $subscription = $this->testDb->table('push_subscriptions')->get()->getRowArray();
        $this->assertNotNull($subscription['disabled_at']);
    }

    public function testPushDeliveryOnlyReturnsSubscriptionsForActiveLinkedDevices(): void
    {
        $active = $this->register(10, 'push-active-token', 'push-active-selector');
        $revoked = $this->register(10, 'push-revoked-token', 'push-revoked-selector');
        $this->deviceSessions->revokeOne(10, (int) $revoked['id'], 10, 'test');

        foreach ([
            ['endpoint' => 'https://push.example/active', 'device_session_id' => $active['id']],
            ['endpoint' => 'https://push.example/revoked', 'device_session_id' => $revoked['id']],
            ['endpoint' => 'https://push.example/orphan', 'device_session_id' => null],
        ] as $subscription) {
            $this->testDb->table('push_subscriptions')->insert([
                'user_id' => 10,
                'endpoint' => $subscription['endpoint'],
                'device_session_id' => $subscription['device_session_id'],
                'disabled_at' => null,
            ]);
        }

        $subscriptions = (new PushSubscriptionModel($this->testDb))->activeForUser(10);

        $this->assertCount(1, $subscriptions);
        $this->assertSame('https://push.example/active', $subscriptions[0]->endpoint);
    }

    private function managementService(bool $allowed): DeviceManagementService
    {
        return new DeviceManagementService(
            $this->deviceSessions,
            $this->testDb,
            static fn (int $userId, string $permission): bool => $allowed && $userId === 30 && $permission === 'auth.devices.manage',
        );
    }

    private function register(int $userId, string $token, string $selector): array
    {
        return $this->deviceSessions->registerOrRotate(
            $userId,
            $token,
            $selector,
            'Mozilla/5.0 (Windows NT 10.0) Firefox/120.0',
            '192.0.2.1',
        );
    }

    private function createAuthTables(): void
    {
        $this->testDb->query('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, password_hash TEXT NOT NULL)');
        $this->testDb->query('CREATE TABLE auth_groups (id INTEGER PRIMARY KEY, name TEXT NOT NULL)');
        $this->testDb->query('CREATE TABLE auth_permissions (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL, description TEXT NOT NULL)');
        $this->testDb->query('CREATE TABLE auth_groups_permissions (group_id INTEGER NOT NULL, permission_id INTEGER NOT NULL, PRIMARY KEY (group_id, permission_id))');
        $this->testDb->query('CREATE TABLE auth_users_permissions (user_id INTEGER NOT NULL, permission_id INTEGER NOT NULL, PRIMARY KEY (user_id, permission_id))');
        $this->testDb->query('CREATE TABLE auth_tokens (id INTEGER PRIMARY KEY AUTOINCREMENT, selector TEXT NOT NULL, hashedValidator TEXT NOT NULL, user_id INTEGER NOT NULL, expires TEXT NOT NULL)');
        $this->testDb->table('auth_groups')->insert(['id' => 1, 'name' => 'Admin']);
    }

    private function createPushSubscriptionsTable(): void
    {
        $this->testDb->query('CREATE TABLE push_subscriptions (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL, endpoint TEXT NOT NULL, disabled_at TEXT NULL)');
    }
}

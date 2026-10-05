<?php

require_once APPPATH . 'Database/Migrations/2026-09-30-000001_CreateAuthDeviceSessions.php';

use App\Database\Migrations\CreateAuthDeviceSessions;
use App\Repositories\DeviceSessionRepository;
use App\Services\DeviceSessionService;
use App\Services\UserAgentDeviceParser;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

final class DeviceSessionServiceTest extends CIUnitTestCase
{
    private BaseConnection $testDb;
    private DateTimeImmutable $now;
    private DeviceSessionService $service;

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
        (new CreateAuthDeviceSessions(Database::forge($this->testDb)))->up();
        $this->testDb->table('users')->insert(['id' => 10]);
        $this->testDb->table('users')->insert(['id' => 20]);

        $this->now = new DateTimeImmutable('2026-09-30 10:00:00');
        $this->service = new DeviceSessionService(
            new DeviceSessionRepository($this->testDb),
            new UserAgentDeviceParser(),
            fn (): DateTimeImmutable => $this->now,
        );
    }

    protected function tearDown(): void
    {
        $this->testDb->close();
        parent::tearDown();
    }

    public function testRegistersAndRotatesOnlyHashedTokensWithRollingExpiry(): void
    {
        $device = $this->service->registerOrRotate(
            10,
            'raw-device-token-one',
            'selector-one',
            'Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 Chrome/130.0 Safari/537.36',
            '192.0.2.10',
        );

        $stored = $this->testDb->table('auth_device_sessions')->where('id', $device['id'])->get()->getRowArray();
        $this->assertSame(hash('sha256', 'raw-device-token-one'), $stored['token_hash']);
        $this->assertStringNotContainsString('raw-device-token-one', json_encode($stored, JSON_THROW_ON_ERROR));
        $this->assertArrayNotHasKey('token_hash', $device);
        $this->assertArrayNotHasKey('remember_selector', $device);
        $this->assertSame('Chrome di Windows', $device['device_label']);
        $this->assertSame('2027-09-30 10:00:00', $stored['expires_at']);

        $this->now = $this->now->modify('+1 day');
        $rotated = $this->service->registerOrRotate(
            10,
            'raw-device-token-two',
            'selector-two',
            'Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 Chrome/130.0 Safari/537.36',
            '192.0.2.11',
            'raw-device-token-one',
        );

        $this->assertSame($device['id'], $rotated['id']);
        $this->assertArrayNotHasKey('token_hash', $rotated);
        $this->assertArrayNotHasKey('remember_selector', $rotated);
        $this->assertSame(1, $this->testDb->table('auth_device_sessions')->countAllResults());
        $rotatedStored = $this->testDb->table('auth_device_sessions')->where('id', $device['id'])->get()->getRowArray();
        $this->assertSame('2026-09-30 10:00:00', $rotatedStored['created_at']);
        $this->assertSame('2027-10-01 10:00:00', $rotatedStored['expires_at']);
        $this->assertNull($this->service->resolveActive(10, 'raw-device-token-one'));
        $resolved = $this->service->resolveActive(10, 'raw-device-token-two');
        $this->assertSame($device['id'], $resolved['id']);
        $this->assertArrayNotHasKey('token_hash', $resolved);
        $this->assertArrayNotHasKey('remember_selector', $resolved);
        $this->assertNull($this->service->resolveActive(20, 'raw-device-token-two'));
    }

    public function testListsActiveDevicesAndMarksOnlyCurrentWithoutLeakingHashes(): void
    {
        $first = $this->register('token-a', 'selector-a', 'Firefox/120.0 (Linux x86_64)');
        $this->register('token-b', 'selector-b', 'unknown-agent');

        $devices = $this->service->listActive(10, 'token-a');

        $this->assertCount(2, $devices);
        $current = array_values(array_filter($devices, static fn (array $row): bool => $row['is_current']))[0];
        $this->assertSame($first['id'], $current['id']);
        $this->assertSame('Perangkat tidak dikenal', $devices[0]['device_label'] === 'Perangkat tidak dikenal' ? $devices[0]['device_label'] : $devices[1]['device_label']);
        foreach ($devices as $device) {
            $this->assertArrayNotHasKey('token_hash', $device);
            $this->assertArrayNotHasKey('remember_selector', $device);
        }
    }

    public function testRotationDeletesOnlyPreviousRememberSelectorForSameUser(): void
    {
        $this->register('rotation-old-token', 'rotation-old-selector', 'Mozilla/5.0 Firefox/120.0');
        $this->insertRememberToken(10, 'rotation-old-selector');
        $this->insertRememberToken(10, 'rotation-new-selector');
        $this->insertRememberToken(20, 'rotation-old-selector');

        $this->service->registerOrRotate(
            10,
            'rotation-new-token',
            'rotation-new-selector',
            'Mozilla/5.0 Firefox/120.0',
            '192.0.2.2',
            'rotation-old-token',
        );

        $this->assertSame(0, $this->testDb->table('auth_tokens')->where([
            'user_id' => 10,
            'selector' => 'rotation-old-selector',
        ])->countAllResults());
        $this->assertSame(1, $this->testDb->table('auth_tokens')->where([
            'user_id' => 10,
            'selector' => 'rotation-new-selector',
        ])->countAllResults());
        $this->assertSame(1, $this->testDb->table('auth_tokens')->where([
            'user_id' => 20,
            'selector' => 'rotation-old-selector',
        ])->countAllResults());
    }

    public function testRevokedPreviousDeviceCannotBeRotatedAndNewSelectorIsCleanedUp(): void
    {
        $device = $this->register('race-old-token', 'race-old-selector', 'Mozilla/5.0 Firefox/120.0');
        $this->insertRememberToken(10, 'race-old-selector');
        $this->insertRememberToken(10, 'race-new-selector');
        $this->assertTrue($this->service->revokeOne(10, $device['id'], 10, 'revoke menang'));

        $rejected = false;
        try {
            $this->service->registerOrRotate(
                10,
                'race-new-token',
                'race-new-selector',
                'Mozilla/5.0 Firefox/120.0',
                '192.0.2.3',
                'race-old-token',
            );
        } catch (RuntimeException $e) {
            $rejected = true;
            $this->assertSame('Sesi perangkat sebelumnya sudah dicabut.', $e->getMessage());
        }

        $this->assertTrue($rejected);
        $this->assertSame(0, $this->testDb->table('auth_tokens')->where([
            'user_id' => 10,
            'selector' => 'race-new-selector',
        ])->countAllResults());
        $this->assertSame(1, $this->testDb->table('auth_device_sessions')->countAllResults());
    }

    public function testTouchesAtMostOncePerFiveMinutesAndExtendsExpiry(): void
    {
        $device = $this->register('touch-token', 'touch-selector', 'Mozilla/5.0 Firefox/120.0');

        $this->assertFalse($this->service->touchLastSeen(10, $device['id']));
        $this->now = $this->now->modify('+6 minutes');
        $this->assertTrue($this->service->touchLastSeen(10, $device['id']));

        $stored = $this->testDb->table('auth_device_sessions')->where('id', $device['id'])->get()->getRowArray();
        $this->assertSame('2026-09-30 10:06:00', $stored['last_seen_at']);
        $this->assertSame('2027-09-30 10:06:00', $stored['expires_at']);
        $this->assertFalse($this->service->touchLastSeen(20, $device['id']));
    }

    public function testRevokesOneIdempotentlyWithAuditAndDeletesOnlyItsRememberSelector(): void
    {
        $first = $this->register('revoke-a', 'selector-a', 'Mozilla/5.0 Firefox/120.0');
        $second = $this->register('revoke-b', 'selector-b', 'Mozilla/5.0 Firefox/120.0');
        $this->insertRememberToken(10, 'selector-a');
        $this->insertRememberToken(10, 'selector-b');
        $this->insertRememberToken(20, 'selector-a');

        $this->assertTrue($this->service->revokeOne(10, $first['id'], 10, 'logout pengguna'));
        $this->assertFalse($this->service->revokeOne(10, $first['id'], 10, 'alasan kedua'));

        $revoked = $this->testDb->table('auth_device_sessions')->where('id', $first['id'])->get()->getRowArray();
        $this->assertSame(10, (int) $revoked['revoked_by']);
        $this->assertSame('logout pengguna', $revoked['revoke_reason']);
        $this->assertSame(0, $this->testDb->table('auth_tokens')->where(['user_id' => 10, 'selector' => 'selector-a'])->countAllResults());
        $this->assertSame(1, $this->testDb->table('auth_tokens')->where(['user_id' => 10, 'selector' => 'selector-b'])->countAllResults());
        $this->assertSame(1, $this->testDb->table('auth_tokens')->where(['user_id' => 20, 'selector' => 'selector-a'])->countAllResults());
        $this->assertNotNull($this->service->resolveActive(10, 'revoke-b'));
        $this->assertSame($second['id'], $this->service->resolveActive(10, 'revoke-b')['id']);
    }

    public function testRevokesAllOtherDevicesButKeepsCurrentAndItsRememberToken(): void
    {
        $current = $this->register('current-token', 'current-selector', 'Mozilla/5.0 Firefox/120.0');
        $other = $this->register('other-token', 'other-selector', 'Mozilla/5.0 Firefox/120.0');
        $this->insertRememberToken(10, 'current-selector');
        $this->insertRememberToken(10, 'other-selector');

        $this->assertSame(1, $this->service->revokeOthers(10, 'current-token', 10, 'logout perangkat lain'));
        $this->assertNotNull($this->service->resolveActive(10, 'current-token'));
        $this->assertNull($this->service->resolveActive(10, 'other-token'));
        $this->assertSame(1, $this->testDb->table('auth_tokens')->where(['user_id' => 10, 'selector' => 'current-selector'])->countAllResults());
        $this->assertSame(0, $this->testDb->table('auth_tokens')->where(['user_id' => 10, 'selector' => 'other-selector'])->countAllResults());
        $this->assertSame($current['id'], $this->service->listActive(10, 'current-token')[0]['id']);
        $this->assertNotSame($current['id'], $other['id']);
    }

    public function testRepositoryRefusesToRevokeOthersWhenCurrentDeviceIsNoLongerActive(): void
    {
        $this->register('first-token', 'first-selector', 'Mozilla/5.0 Firefox/120.0');
        $this->register('second-token', 'second-selector', 'Mozilla/5.0 Firefox/120.0');
        $repository = new DeviceSessionRepository($this->testDb);

        try {
            $repository->revokeOthers(10, 999, 10, 'logout perangkat lain', '2026-09-30 10:00:00');
            $this->fail('Revoke perangkat lain seharusnya ditolak tanpa perangkat current yang aktif.');
        } catch (RuntimeException $e) {
            $this->assertSame('Sesi perangkat saat ini tidak aktif.', $e->getMessage());
        }

        $this->assertSame(2, $this->testDb->table('auth_device_sessions')->where('revoked_at', null)->countAllResults());
    }

    public function testResolvesRememberedDeviceOnlyWhenTokenAndSelectorMatch(): void
    {
        $device = $this->register('remember-device-token', 'remember-selector', 'Mozilla/5.0 Firefox/120.0');

        $resolved = $this->service->resolveActiveForRemember('remember-device-token', 'remember-selector');

        $this->assertSame($device['id'], $resolved['id']);
        $this->assertSame(10, (int) $resolved['user_id']);
        $this->assertNull($this->service->resolveActiveForRemember('remember-device-token', 'wrong-selector'));
        $this->assertNull($this->service->resolveActiveForRemember('wrong-device-token', 'remember-selector'));
    }

    public function testDeletesOnlyIncomingRememberSelectorWithOptionalUserScope(): void
    {
        $this->insertRememberToken(10, 'legacy-selector');
        $this->insertRememberToken(20, 'other-selector');

        $this->assertSame(1, $this->service->deleteRememberSelector('legacy-selector', 10));
        $this->assertSame(0, $this->testDb->table('auth_tokens')->where(['user_id' => 10, 'selector' => 'legacy-selector'])->countAllResults());
        $this->assertSame(1, $this->testDb->table('auth_tokens')->where(['user_id' => 20, 'selector' => 'other-selector'])->countAllResults());
        $this->assertSame(0, $this->service->deleteRememberSelector('missing-selector'));
    }

    public function testAdminCanListAndRevokeAllTargetDevicesTransactionally(): void
    {
        $this->register('admin-target-a', 'admin-selector-a', 'Mozilla/5.0 Firefox/120.0');
        $this->register('admin-target-b', 'admin-selector-b', 'Mozilla/5.0 Firefox/120.0');
        $this->insertRememberToken(10, 'admin-selector-a');
        $this->insertRememberToken(10, 'admin-selector-b');
        $this->insertRememberToken(20, 'unrelated-selector');

        $devices = $this->service->listActiveForUser(10);
        $this->assertCount(2, $devices);
        foreach ($devices as $device) {
            $this->assertArrayNotHasKey('token_hash', $device);
            $this->assertArrayNotHasKey('remember_selector', $device);
        }

        $this->assertSame(2, $this->service->revokeAll(10, 20, 'admin security review'));
        $this->assertSame(0, $this->testDb->table('auth_device_sessions')->where(['user_id' => 10, 'revoked_at' => null])->countAllResults());
        $this->assertSame(0, $this->testDb->table('auth_tokens')->where('user_id', 10)->countAllResults());
        $this->assertSame(1, $this->testDb->table('auth_tokens')->where('user_id', 20)->countAllResults());
        $this->assertSame(0, $this->service->revokeAll(10, 20, 'second request'));
    }

    private function register(string $rawToken, string $selector, string $userAgent): array
    {
        return $this->service->registerOrRotate(10, $rawToken, $selector, $userAgent, '192.0.2.1');
    }

    private function insertRememberToken(int $userId, string $selector): void
    {
        $this->testDb->table('auth_tokens')->insert([
            'selector' => $selector,
            'hashedValidator' => hash('sha256', 'validator'),
            'user_id' => $userId,
            'expires' => '2027-09-30 10:00:00',
        ]);
    }

    private function createAuthTables(): void
    {
        $this->testDb->query('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT)');
        $this->testDb->query('CREATE TABLE auth_groups (id INTEGER PRIMARY KEY, name TEXT NOT NULL)');
        $this->testDb->query('CREATE TABLE auth_permissions (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL, description TEXT NOT NULL)');
        $this->testDb->query('CREATE TABLE auth_groups_permissions (group_id INTEGER NOT NULL, permission_id INTEGER NOT NULL, PRIMARY KEY (group_id, permission_id))');
        $this->testDb->query('CREATE TABLE auth_users_permissions (user_id INTEGER NOT NULL, permission_id INTEGER NOT NULL, PRIMARY KEY (user_id, permission_id))');
        $this->testDb->query('CREATE TABLE auth_tokens (id INTEGER PRIMARY KEY AUTOINCREMENT, selector TEXT NOT NULL, hashedValidator TEXT NOT NULL, user_id INTEGER NOT NULL, expires TEXT NOT NULL)');
        $this->testDb->table('auth_groups')->insert(['id' => 1, 'name' => 'Admin']);
    }
}

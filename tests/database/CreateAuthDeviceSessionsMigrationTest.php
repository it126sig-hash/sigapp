<?php

require_once APPPATH . 'Database/Migrations/2026-09-30-000001_CreateAuthDeviceSessions.php';

use App\Database\Migrations\CreateAuthDeviceSessions;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

final class CreateAuthDeviceSessionsMigrationTest extends CIUnitTestCase
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
            'foreignKeys' => true,
        ], false);

        $this->testDb->query('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT)');
        $this->testDb->query('CREATE TABLE auth_groups (id INTEGER PRIMARY KEY, name TEXT NOT NULL)');
        $this->testDb->query('CREATE TABLE auth_permissions (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL, description TEXT NOT NULL)');
        $this->testDb->query('CREATE TABLE auth_groups_permissions (group_id INTEGER NOT NULL, permission_id INTEGER NOT NULL, PRIMARY KEY (group_id, permission_id))');
        $this->testDb->query('CREATE TABLE auth_users_permissions (user_id INTEGER NOT NULL, permission_id INTEGER NOT NULL, PRIMARY KEY (user_id, permission_id))');
        $this->testDb->query('CREATE TABLE push_subscriptions (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL, endpoint TEXT NOT NULL, disabled_at TEXT NULL)');
        $this->testDb->table('auth_groups')->insert(['id' => 1, 'name' => 'Admin']);
    }

    protected function tearDown(): void
    {
        $this->testDb->close();
        parent::tearDown();
    }

    public function testCreatesDeviceRegistryAndSeedsAdminPermissionIdempotently(): void
    {
        $migration = new CreateAuthDeviceSessions(Database::forge($this->testDb));

        $migration->up();
        $migration->up();

        $this->assertTrue($this->testDb->tableExists('auth_device_sessions'));
        $this->assertEqualsCanonicalizing([
            'id', 'user_id', 'token_hash', 'remember_selector', 'user_agent', 'device_label',
            'browser', 'platform', 'ip_address', 'created_at', 'last_seen_at', 'expires_at',
            'revoked_at', 'revoked_by', 'revoke_reason',
        ], $this->testDb->getFieldNames('auth_device_sessions'));

        $permission = $this->testDb->table('auth_permissions')->where('name', 'auth.devices.manage')->get()->getRowArray();
        $this->assertNotNull($permission);
        $this->assertSame(1, $this->testDb->table('auth_permissions')->where('name', 'auth.devices.manage')->countAllResults());
        $this->assertSame(1, $this->testDb->table('auth_groups_permissions')->where([
            'group_id' => 1,
            'permission_id' => $permission['id'],
        ])->countAllResults());

        $indexes = array_column($this->testDb->query("PRAGMA index_list('auth_device_sessions')")->getResultArray(), 'name');
        $this->assertContains('uq_auth_device_sessions_token_hash', $indexes);
        $this->assertContains('idx_auth_device_sessions_selector', $indexes);
        $this->assertContains('idx_auth_device_sessions_user_active', $indexes);
        $this->assertTrue($this->testDb->fieldExists('device_session_id', 'push_subscriptions'));
        $pushIndexes = array_column($this->testDb->query("PRAGMA index_list('push_subscriptions')")->getResultArray(), 'name');
        $this->assertContains('idx_push_subscriptions_device_session', $pushIndexes);

        $this->testDb->table('users')->insert(['id' => 10]);
        $this->testDb->table('users')->insert(['id' => 20]);
        $this->testDb->table('auth_device_sessions')->insert([
            'user_id' => 10,
            'token_hash' => hash('sha256', 'migration-fk-token'),
            'device_label' => 'Firefox di Linux',
            'created_at' => '2026-09-30 10:00:00',
            'last_seen_at' => '2026-09-30 10:00:00',
            'expires_at' => '2027-09-30 10:00:00',
            'revoked_at' => '2026-09-30 11:00:00',
            'revoked_by' => 20,
        ]);
        $deviceId = (int) $this->testDb->insertID();
        $this->testDb->table('users')->where('id', 20)->delete();
        $device = $this->testDb->table('auth_device_sessions')->where('id', $deviceId)->get()->getRowArray();
        $this->assertNotNull($device);
        $this->assertNull($device['revoked_by']);

        $migration->down();

        $this->assertFalse($this->testDb->tableExists('auth_device_sessions'));
        $this->assertFalse($this->testDb->fieldExists('device_session_id', 'push_subscriptions'));
        $this->assertSame(0, $this->testDb->table('auth_permissions')->where('name', 'auth.devices.manage')->countAllResults());
    }

    public function testRollbackPreservesPreexistingPermissionAndGrant(): void
    {
        $this->testDb->table('auth_permissions')->insert([
            'name' => 'auth.devices.manage',
            'description' => 'Existing permission',
        ]);
        $permissionId = (int) $this->testDb->insertID();
        $this->testDb->table('auth_groups_permissions')->insert(['group_id' => 1, 'permission_id' => $permissionId]);

        $migration = new CreateAuthDeviceSessions(Database::forge($this->testDb));
        $migration->up();
        $migration->down();

        $this->assertSame(1, $this->testDb->table('auth_permissions')->where('id', $permissionId)->countAllResults());
        $this->assertSame(1, $this->testDb->table('auth_groups_permissions')->where([
            'group_id' => 1,
            'permission_id' => $permissionId,
        ])->countAllResults());
    }

    public function testPartialSeedFailureRollsBackOwnershipAndAllowsCleanRerunAndDown(): void
    {
        $this->testDb->query(<<<'SQL'
            CREATE TRIGGER fail_device_permission_grant
            BEFORE INSERT ON auth_groups_permissions
            WHEN NEW.group_id = 1
            BEGIN
                SELECT RAISE(FAIL, 'simulated device permission grant failure');
            END
            SQL);

        $failed = false;
        try {
            (new CreateAuthDeviceSessions(Database::forge($this->testDb)))->up();
        } catch (Throwable) {
            $failed = true;
        }

        $this->assertTrue($failed);
        $this->assertSame(0, $this->testDb->table('auth_permissions')->where('name', 'auth.devices.manage')->countAllResults());
        $this->assertSame(0, $this->testDb->table('auth_device_session_migration_state')->countAllResults());

        $this->testDb->query('DROP TRIGGER fail_device_permission_grant');
        $migration = new CreateAuthDeviceSessions(Database::forge($this->testDb));
        $migration->up();
        $migration->down();

        $this->assertSame(0, $this->testDb->table('auth_permissions')->where('name', 'auth.devices.manage')->countAllResults());
        $this->assertFalse($this->testDb->tableExists('auth_device_sessions'));
        $this->assertFalse($this->testDb->tableExists('auth_device_session_migration_state'));
    }
}

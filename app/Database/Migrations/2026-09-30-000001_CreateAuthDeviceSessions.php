<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;
use Throwable;

class CreateAuthDeviceSessions extends Migration
{
    private const PERMISSION = 'auth.devices.manage';
    private const ADMIN_GROUP_ID = 1;
    private const STATE_TABLE = 'auth_device_session_migration_state';

    public function up()
    {
        $this->createDeviceSessionsTable();
        $this->createStateTable();
        $this->addPushDeviceLink();

        $this->transactional(function (): void {
            if ($this->db->table(self::STATE_TABLE)->where('id', 1)->countAllResults() === 0) {
                $this->assertWrite($this->db->table(self::STATE_TABLE)->insert([
                    'id' => 1,
                    'permission_created' => 0,
                    'admin_grant_created' => 0,
                ]), 'Gagal menyimpan status kepemilikan migration perangkat.');
            }

            $permission = $this->db->table('auth_permissions')
                ->select('id')
                ->where('name', self::PERMISSION)
                ->get()
                ->getRowArray();

            $permissionCreated = false;
            if (! $permission) {
                $this->assertWrite($this->db->table('auth_permissions')->insert([
                    'name' => self::PERMISSION,
                    'description' => 'Kelola sesi login perangkat pengguna',
                ]), 'Gagal membuat permission perangkat.');
                $permission = ['id' => (int) $this->db->insertID()];
                $permissionCreated = true;
            }

            $permissionId = (int) $permission['id'];
            $grantExists = $this->db->table('auth_groups_permissions')->where([
                'group_id' => self::ADMIN_GROUP_ID,
                'permission_id' => $permissionId,
            ])->countAllResults() > 0;

            $grantCreated = false;
            if (! $grantExists) {
                $this->assertWrite($this->db->table('auth_groups_permissions')->insert([
                    'group_id' => self::ADMIN_GROUP_ID,
                    'permission_id' => $permissionId,
                ]), 'Gagal memberikan permission perangkat kepada admin.');
                $grantCreated = true;
            }

            $state = $this->db->table(self::STATE_TABLE)->where('id', 1)->get()->getRowArray();
            if (! $state) {
                throw new RuntimeException('Status kepemilikan migration perangkat tidak ditemukan.');
            }
            $this->assertWrite($this->db->table(self::STATE_TABLE)->where('id', 1)->update([
                'permission_created' => (int) ((bool) $state['permission_created'] || $permissionCreated),
                'admin_grant_created' => (int) ((bool) $state['admin_grant_created'] || $grantCreated),
            ]), 'Gagal memperbarui status kepemilikan migration perangkat.');
        });
    }

    public function down()
    {
        $state = $this->db->tableExists(self::STATE_TABLE)
            ? $this->db->table(self::STATE_TABLE)->where('id', 1)->get()->getRowArray()
            : null;

        $permission = $this->db->tableExists('auth_permissions')
            ? $this->db->table('auth_permissions')->select('id')->where('name', self::PERMISSION)->get()->getRowArray()
            : null;

        if ($state && $permission) {
            $this->transactional(function () use ($state, $permission): void {
                $permissionId = (int) $permission['id'];
                if ((bool) $state['admin_grant_created'] && $this->db->tableExists('auth_groups_permissions')) {
                    $this->assertWrite($this->db->table('auth_groups_permissions')->where([
                        'group_id' => self::ADMIN_GROUP_ID,
                        'permission_id' => $permissionId,
                    ])->delete(), 'Gagal menghapus grant permission perangkat.');
                }

                if ((bool) $state['permission_created'] && ! $this->permissionHasAssignments($permissionId)) {
                    $this->assertWrite(
                        $this->db->table('auth_permissions')->where('id', $permissionId)->delete(),
                        'Gagal menghapus permission perangkat.',
                    );
                }
            });
        }

        $this->removePushDeviceLink();
        $this->forge->dropTable('auth_device_sessions', true);
        $this->forge->dropTable(self::STATE_TABLE, true);
    }

    private function addPushDeviceLink(): void
    {
        if (! $this->db->tableExists('push_subscriptions')
            || $this->db->fieldExists('device_session_id', 'push_subscriptions')) {
            return;
        }

        $this->forge->addColumn('push_subscriptions', [
            'device_session_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
        ]);
        $this->forge->addKey('device_session_id', false, false, 'idx_push_subscriptions_device_session');
        $this->forge->processIndexes('push_subscriptions');
        $this->db->resetDataCache();
    }

    private function removePushDeviceLink(): void
    {
        if (! $this->db->tableExists('push_subscriptions')
            || ! $this->db->fieldExists('device_session_id', 'push_subscriptions')
            || ! isset($this->db->getIndexData('push_subscriptions')['idx_push_subscriptions_device_session'])) {
            return;
        }

        $this->forge->dropKey('push_subscriptions', 'idx_push_subscriptions_device_session');
        $this->forge->dropColumn('push_subscriptions', 'device_session_id');
        $this->db->resetDataCache();
    }

    private function createDeviceSessionsTable(): void
    {
        if ($this->db->tableExists('auth_device_sessions')) {
            return;
        }

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'token_hash' => ['type' => 'CHAR', 'constraint' => 64],
            'remember_selector' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'user_agent' => ['type' => 'VARCHAR', 'constraint' => 512, 'null' => true],
            'device_label' => ['type' => 'VARCHAR', 'constraint' => 255],
            'browser' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'platform' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'ip_address' => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'created_at' => ['type' => 'DATETIME'],
            'last_seen_at' => ['type' => 'DATETIME'],
            'expires_at' => ['type' => 'DATETIME'],
            'revoked_at' => ['type' => 'DATETIME', 'null' => true],
            'revoked_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'revoke_reason' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('token_hash', 'uq_auth_device_sessions_token_hash');
        $this->forge->addKey('remember_selector', false, false, 'idx_auth_device_sessions_selector');
        $this->forge->addKey(['user_id', 'revoked_at', 'expires_at'], false, false, 'idx_auth_device_sessions_user_active');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('revoked_by', 'users', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable(
            'auth_device_sessions',
            true,
            $this->db->DBDriver === 'MySQLi' ? ['ENGINE' => 'InnoDB'] : [],
        );
    }

    private function createStateTable(): void
    {
        if ($this->db->tableExists(self::STATE_TABLE)) {
            return;
        }

        $this->forge->addField([
            'id' => ['type' => 'TINYINT', 'unsigned' => true],
            'permission_created' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'admin_grant_created' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable(
            self::STATE_TABLE,
            true,
            $this->db->DBDriver === 'MySQLi' ? ['ENGINE' => 'InnoDB'] : [],
        );
    }

    private function permissionHasAssignments(int $permissionId): bool
    {
        foreach (['auth_groups_permissions', 'auth_users_permissions'] as $table) {
            if ($this->db->tableExists($table)
                && $this->db->table($table)->where('permission_id', $permissionId)->countAllResults() > 0) {
                return true;
            }
        }

        return false;
    }

    private function transactional(callable $operation): void
    {
        if (! $this->db->transBegin()) {
            throw new RuntimeException('Gagal memulai transaksi migration perangkat.');
        }

        try {
            $operation();
            if (! $this->db->transCommit()) {
                throw new RuntimeException('Gagal commit transaksi migration perangkat.');
            }
        } catch (Throwable $e) {
            if (! $this->db->transRollback()) {
                throw new RuntimeException('Gagal rollback transaksi migration perangkat.', 0, $e);
            }

            throw $e;
        }
    }

    private function assertWrite(bool $written, string $message): void
    {
        if (! $written) {
            throw new RuntimeException($message);
        }
    }
}

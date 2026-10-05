<?php

namespace App\Repositories;

use App\Models\DeviceSessionModel;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\BaseBuilder;
use RuntimeException;
use Throwable;

class DeviceSessionRepository
{
    private BaseConnection $db;
    private DeviceSessionModel $model;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
        $this->model = new DeviceSessionModel($this->db);
    }

    public function registerOrRotate(array $data, ?string $previousTokenHash): array
    {
        $this->beginWriteTransaction();
        $transactionOpen = true;
        $rejection = null;
        $row = null;

        try {
            $sameToken = $this->rowForUpdate(
                $this->db->table('auth_device_sessions')->where('token_hash', $data['token_hash']),
            );

            if ($sameToken && (int) $sameToken['user_id'] !== (int) $data['user_id']) {
                throw new RuntimeException('Token perangkat tidak dapat digunakan.');
            }
            if ($sameToken && $sameToken['revoked_at'] !== null) {
                $this->deleteRememberSelectors((int) $data['user_id'], [$data['remember_selector']]);
                $this->commitWriteTransaction();
                $transactionOpen = false;
                $rejection = 'Token perangkat sudah dicabut.';
            }

            $existing = $sameToken;
            if ($rejection === null && ! $existing && $previousTokenHash !== null) {
                $existing = $this->rowForUpdate(
                    $this->db->table('auth_device_sessions')
                        ->where('user_id', $data['user_id'])
                        ->where('token_hash', $previousTokenHash),
                );
                if ($existing && $existing['revoked_at'] !== null) {
                    $this->deleteRememberSelectors((int) $data['user_id'], [$data['remember_selector']]);
                    $this->commitWriteTransaction();
                    $transactionOpen = false;
                    $rejection = 'Sesi perangkat sebelumnya sudah dicabut.';
                }
            }

            if ($rejection === null && $existing) {
                $id = (int) $existing['id'];
                $oldSelector = $existing['remember_selector'];
                unset($data['created_at']);
                $updated = $this->db->table('auth_device_sessions')
                    ->where('id', $id)
                    ->where('user_id', $data['user_id'])
                    ->where('revoked_at', null)
                    ->update($data);
                if (! $updated) {
                    throw new RuntimeException('Gagal memperbarui sesi perangkat.');
                }
                if ($previousTokenHash !== null && ! $sameToken && $this->db->affectedRows() !== 1) {
                    throw new RuntimeException('Sesi perangkat berubah sebelum rotasi selesai.');
                }
                if ($oldSelector !== $data['remember_selector']) {
                    $this->deleteRememberSelectors((int) $data['user_id'], [$oldSelector]);
                }
            } elseif ($rejection === null) {
                $id = (int) $this->model->insert($data, true);
                if ($id <= 0) {
                    throw new RuntimeException('Gagal mendaftarkan sesi perangkat.');
                }
            }

            if ($rejection === null) {
                $row = $this->model->find($id);
                if (! $row
                    || (int) $row['user_id'] !== (int) $data['user_id']
                    || ! hash_equals((string) $data['token_hash'], (string) $row['token_hash'])
                    || $row['revoked_at'] !== null) {
                    throw new RuntimeException('Sesi perangkat gagal disimpan.');
                }

                $this->commitWriteTransaction();
                $transactionOpen = false;
            }
        } catch (Throwable $e) {
            if ($transactionOpen) {
                $this->rollbackWriteTransaction($e);
            }
            throw $e;
        }

        if ($rejection !== null) {
            throw new RuntimeException($rejection);
        }

        return $row;
    }

    public function findActiveByTokenHash(int $userId, string $tokenHash, string $now): ?array
    {
        $row = $this->db->table('auth_device_sessions')
            ->where('user_id', $userId)
            ->where('token_hash', $tokenHash)
            ->where('revoked_at', null)
            ->where('expires_at >', $now)
            ->get()
            ->getRowArray();

        return $row ?: null;
    }

    public function findActiveByTokenHashAndSelector(string $tokenHash, string $selector, string $now): ?array
    {
        $row = $this->db->table('auth_device_sessions')
            ->where('token_hash', $tokenHash)
            ->where('remember_selector', $selector)
            ->where('revoked_at', null)
            ->where('expires_at >', $now)
            ->get()
            ->getRowArray();

        return $row ?: null;
    }

    public function listActive(int $userId, string $now): array
    {
        return $this->db->table('auth_device_sessions')
            ->where('user_id', $userId)
            ->where('revoked_at', null)
            ->where('expires_at >', $now)
            ->orderBy('last_seen_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function touchIfStale(int $userId, int $deviceId, string $threshold, string $now, string $expiresAt): bool
    {
        $this->db->table('auth_device_sessions')
            ->where('id', $deviceId)
            ->where('user_id', $userId)
            ->where('revoked_at', null)
            ->where('expires_at >', $now)
            ->where('last_seen_at <=', $threshold)
            ->update([
                'last_seen_at' => $now,
                'expires_at' => $expiresAt,
            ]);

        return $this->db->affectedRows() === 1;
    }

    public function revokeOne(int $userId, int $deviceId, int $revokedBy, ?string $reason, string $now): bool
    {
        $this->beginWriteTransaction();
        $transactionOpen = true;

        try {
            $device = $this->rowForUpdate(
                $this->db->table('auth_device_sessions')
                    ->select('id, remember_selector')
                    ->where('id', $deviceId)
                    ->where('user_id', $userId)
                    ->where('revoked_at', null),
            );

            if (! $device) {
                $this->rollbackWriteTransaction();
                $transactionOpen = false;

                return false;
            }

            $updated = $this->db->table('auth_device_sessions')
                ->where('id', $deviceId)
                ->where('user_id', $userId)
                ->where('revoked_at', null)
                ->update([
                    'revoked_at' => $now,
                    'revoked_by' => $revokedBy,
                    'revoke_reason' => $reason,
                ]);

            if (! $updated || $this->db->affectedRows() !== 1) {
                throw new RuntimeException('Sesi perangkat berubah sebelum pencabutan selesai.');
            }

            $this->deleteRememberSelectors($userId, [$device['remember_selector']]);
            $this->disablePushSubscriptions([(int) $deviceId], $now);
            $this->commitWriteTransaction();
            $transactionOpen = false;

            return true;
        } catch (Throwable $e) {
            if ($transactionOpen) {
                $this->rollbackWriteTransaction($e);
            }
            throw $e;
        }
    }

    public function revokeOthers(int $userId, int $currentDeviceId, int $revokedBy, ?string $reason, string $now): int
    {
        $this->beginWriteTransaction();
        $transactionOpen = true;

        try {
            $current = $this->rowForUpdate(
                $this->db->table('auth_device_sessions')
                    ->select('id')
                    ->where('id', $currentDeviceId)
                    ->where('user_id', $userId)
                    ->where('revoked_at', null)
                    ->where('expires_at >', $now),
            );
            if (! $current) {
                throw new RuntimeException('Sesi perangkat saat ini tidak aktif.');
            }

            $devices = $this->rowsForUpdate(
                $this->db->table('auth_device_sessions')
                    ->select('id, remember_selector')
                    ->where('user_id', $userId)
                    ->where('id !=', $currentDeviceId)
                    ->where('revoked_at', null),
            );

            if ($devices === []) {
                $this->commitWriteTransaction();
                $transactionOpen = false;

                return 0;
            }

            $ids = array_map(static fn (array $row): int => (int) $row['id'], $devices);
            $updated = $this->db->table('auth_device_sessions')
                ->where('user_id', $userId)
                ->where('revoked_at', null)
                ->whereIn('id', $ids)
                ->update([
                    'revoked_at' => $now,
                    'revoked_by' => $revokedBy,
                    'revoke_reason' => $reason,
                ]);
            $count = $this->db->affectedRows();
            if (! $updated || $count !== count($ids)) {
                throw new RuntimeException('Daftar perangkat berubah sebelum pencabutan selesai.');
            }

            $this->deleteRememberSelectors($userId, array_column($devices, 'remember_selector'));
            $this->disablePushSubscriptions($ids, $now);
            $this->commitWriteTransaction();
            $transactionOpen = false;

            return $count;
        } catch (Throwable $e) {
            if ($transactionOpen) {
                $this->rollbackWriteTransaction($e);
            }
            throw $e;
        }
    }

    public function revokeAll(int $userId, int $revokedBy, ?string $reason, string $now): int
    {
        $this->beginWriteTransaction();
        $transactionOpen = true;

        try {
            $devices = $this->rowsForUpdate(
                $this->db->table('auth_device_sessions')
                    ->select('id, remember_selector')
                    ->where('user_id', $userId)
                    ->where('revoked_at', null),
            );

            if ($devices === []) {
                $this->commitWriteTransaction();

                return 0;
            }

            $ids = array_map(static fn (array $row): int => (int) $row['id'], $devices);
            $updated = $this->db->table('auth_device_sessions')
                ->where('user_id', $userId)
                ->where('revoked_at', null)
                ->whereIn('id', $ids)
                ->update([
                    'revoked_at' => $now,
                    'revoked_by' => $revokedBy,
                    'revoke_reason' => $reason,
                ]);
            $count = $this->db->affectedRows();
            if (! $updated || $count !== count($ids)) {
                throw new RuntimeException('Daftar perangkat berubah sebelum pencabutan selesai.');
            }

            $this->deleteRememberSelectors($userId, array_column($devices, 'remember_selector'));
            $this->disablePushSubscriptions($ids, $now);
            $this->commitWriteTransaction();
            $transactionOpen = false;

            return $count;
        } catch (Throwable $e) {
            if ($transactionOpen) {
                $this->rollbackWriteTransaction($e);
            }
            throw $e;
        }
    }

    public function deleteRememberSelector(string $selector, ?int $userId = null): int
    {
        if ($selector === '' || ! $this->db->tableExists('auth_tokens')) {
            return 0;
        }

        $builder = $this->db->table('auth_tokens')->where('selector', $selector);
        if ($userId !== null) {
            $builder->where('user_id', $userId);
        }

        if (! $builder->delete()) {
            throw new RuntimeException('Gagal menghapus remember token perangkat.');
        }

        return $this->db->affectedRows();
    }

    private function deleteRememberSelectors(int $userId, array $selectors): void
    {
        $selectors = array_values(array_unique(array_filter(
            $selectors,
            static fn ($selector): bool => is_string($selector) && $selector !== '',
        )));

        if ($selectors === [] || ! $this->db->tableExists('auth_tokens')) {
            return;
        }

        $deleted = $this->db->table('auth_tokens')
            ->where('user_id', $userId)
            ->whereIn('selector', $selectors)
            ->delete();
        if (! $deleted) {
            throw new RuntimeException('Gagal menghapus remember token perangkat.');
        }
    }

    private function disablePushSubscriptions(array $deviceIds, string $now): void
    {
        if ($deviceIds === [] || ! $this->db->tableExists('push_subscriptions')
            || ! $this->db->fieldExists('device_session_id', 'push_subscriptions')
            || ! $this->db->fieldExists('disabled_at', 'push_subscriptions')) {
            return;
        }

        $updated = $this->db->table('push_subscriptions')
            ->whereIn('device_session_id', $deviceIds)
            ->where('disabled_at', null)
            ->update(['disabled_at' => $now]);
        if (! $updated) {
            throw new RuntimeException('Gagal menonaktifkan push perangkat yang dicabut.');
        }
    }

    private function beginWriteTransaction(): void
    {
        if (! $this->db->transBegin()) {
            throw new RuntimeException('Gagal memulai transaksi sesi perangkat.');
        }

        if ($this->db->DBDriver === 'SQLite3'
            && ! $this->db->simpleQuery('UPDATE auth_device_sessions SET id = id WHERE 1 = 0')) {
            if (! $this->db->transRollback()) {
                throw new RuntimeException('Gagal rollback transaksi penguncian sesi perangkat.');
            }
            throw new RuntimeException('Gagal mengunci transaksi sesi perangkat.');
        }
    }

    private function commitWriteTransaction(): void
    {
        if (! $this->db->transCommit()) {
            throw new RuntimeException('Gagal commit transaksi sesi perangkat.');
        }
    }

    private function rollbackWriteTransaction(?Throwable $cause = null): void
    {
        if (! $this->db->transRollback()) {
            throw new RuntimeException('Gagal rollback transaksi sesi perangkat.', 0, $cause);
        }
    }

    private function rowForUpdate(BaseBuilder $builder): ?array
    {
        if (in_array($this->db->DBDriver, ['MySQLi', 'Postgre'], true)) {
            $row = $this->db->query($builder->getCompiledSelect() . ' FOR UPDATE')->getRowArray();
        } else {
            $row = $builder->get()->getRowArray();
        }

        return $row ?: null;
    }

    private function rowsForUpdate(BaseBuilder $builder): array
    {
        if (in_array($this->db->DBDriver, ['MySQLi', 'Postgre'], true)) {
            return $this->db->query($builder->getCompiledSelect() . ' FOR UPDATE')->getResultArray();
        }

        return $builder->get()->getResultArray();
    }
}

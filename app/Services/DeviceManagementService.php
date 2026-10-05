<?php

namespace App\Services;

use Closure;
use CodeIgniter\Database\BaseConnection;
use Myth\Auth\Password;

class DeviceManagementService
{
    private const MANAGE_PERMISSION = 'auth.devices.manage';

    private DeviceSessionService $deviceSessions;
    private BaseConnection $db;
    private Closure $permissionChecker;

    public function __construct(
        ?DeviceSessionService $deviceSessions = null,
        ?BaseConnection $db = null,
        ?callable $permissionChecker = null,
    ) {
        $this->deviceSessions = $deviceSessions ?? new DeviceSessionService();
        $this->db = $db ?? db_connect();
        $this->permissionChecker = $permissionChecker !== null
            ? Closure::fromCallable($permissionChecker)
            : static fn (int $userId, string $permission): bool => service('authorization')->hasPermission($permission, $userId) === true;
    }

    public function listOwn(int $userId, string $currentRawToken): array
    {
        $this->requireActiveCurrent($userId, $currentRawToken);

        return $this->deviceSessions->listActive($userId, $currentRawToken);
    }

    public function revokeOwn(int $userId, int $deviceId, string $password, string $currentRawToken): array
    {
        $this->verifyPassword($userId, $password);
        $current = $this->requireActiveCurrent($userId, $currentRawToken);

        if (! $this->deviceSessions->revokeOne($userId, $deviceId, $userId, 'Dicabut oleh pengguna')) {
            throw new DeviceManagementException('Perangkat tidak ditemukan.', 404);
        }

        return ['current_device_revoked' => (int) $current['id'] === $deviceId];
    }

    public function revokeOthers(int $userId, string $currentRawToken, string $password): int
    {
        $this->verifyPassword($userId, $password);
        $this->requireActiveCurrent($userId, $currentRawToken);

        return $this->deviceSessions->revokeOthers(
            $userId,
            $currentRawToken,
            $userId,
            'Dicabut oleh pengguna dari perangkat aktif',
        );
    }

    public function listForAdmin(int $adminUserId, int $targetUserId): array
    {
        $this->requireAdmin($adminUserId);
        $this->requireUser($targetUserId);

        return $this->deviceSessions->listActiveForUser($targetUserId);
    }

    public function revokeForAdmin(
        int $adminUserId,
        int $targetUserId,
        int $deviceId,
        string $reason,
    ): bool {
        $this->requireAdmin($adminUserId);
        $reason = $this->requireReason($reason);
        $this->requireUser($targetUserId);

        if (! $this->deviceSessions->revokeOne($targetUserId, $deviceId, $adminUserId, $reason)) {
            throw new DeviceManagementException('Perangkat tidak ditemukan.', 404);
        }

        return true;
    }

    public function revokeAllForAdmin(int $adminUserId, int $targetUserId, string $reason): int
    {
        $this->requireAdmin($adminUserId);
        $reason = $this->requireReason($reason);
        $this->requireUser($targetUserId);

        return $this->deviceSessions->revokeAll($targetUserId, $adminUserId, $reason);
    }

    private function requireActiveCurrent(int $userId, string $rawToken): array
    {
        if ($rawToken === '') {
            throw new DeviceManagementException('Sesi perangkat tidak valid.', 401);
        }

        $device = $this->deviceSessions->resolveActive($userId, $rawToken);
        if (! $device) {
            throw new DeviceManagementException('Sesi perangkat tidak aktif.', 401);
        }

        return $device;
    }

    private function verifyPassword(int $userId, string $password): void
    {
        $user = $this->db->table('users')
            ->select('password_hash')
            ->where('id', $userId)
            ->get()
            ->getRowArray();

        if (! $user || $password === '' || ! Password::verify($password, (string) $user['password_hash'])) {
            throw new DeviceManagementException('Password tidak valid.', 401);
        }
    }

    private function requireAdmin(int $adminUserId): void
    {
        if ($adminUserId <= 0 || ! ($this->permissionChecker)($adminUserId, self::MANAGE_PERMISSION)) {
            throw new DeviceManagementException('Anda tidak memiliki izin mengelola perangkat.', 403);
        }
    }

    private function requireUser(int $userId): void
    {
        if ($userId <= 0 || $this->db->table('users')->where('id', $userId)->countAllResults() !== 1) {
            throw new DeviceManagementException('Pengguna tidak ditemukan.', 404);
        }
    }

    private function requireReason(string $reason): string
    {
        $reason = trim($reason);
        if ($reason === '') {
            throw new DeviceManagementException('Alasan pencabutan wajib diisi.', 422);
        }
        if (strlen($reason) > 255) {
            throw new DeviceManagementException('Alasan pencabutan maksimal 255 karakter.', 422);
        }

        return $reason;
    }
}

<?php

namespace App\Services;

use App\Repositories\DeviceSessionRepository;
use Closure;
use DateTimeImmutable;
use InvalidArgumentException;
use RuntimeException;

class DeviceSessionService
{
    private const LIFETIME_DAYS = 365;
    private const TOUCH_INTERVAL_MINUTES = 5;

    private DeviceSessionRepository $repository;
    private UserAgentDeviceParser $parser;
    private Closure $clock;

    public function __construct(
        ?DeviceSessionRepository $repository = null,
        ?UserAgentDeviceParser $parser = null,
        ?callable $clock = null,
    ) {
        $this->repository = $repository ?? new DeviceSessionRepository();
        $this->parser = $parser ?? new UserAgentDeviceParser();
        $this->clock = $clock !== null
            ? Closure::fromCallable($clock)
            : static fn (): DateTimeImmutable => new DateTimeImmutable();
    }

    public function registerOrRotate(
        int $userId,
        string $rawToken,
        ?string $rememberSelector,
        ?string $userAgent,
        ?string $ipAddress,
        ?string $previousRawToken = null,
    ): array {
        $this->assertUserId($userId);
        $tokenHash = $this->hashToken($rawToken);
        $now = $this->now();
        $device = $this->parser->parse($userAgent);

        $row = $this->repository->registerOrRotate([
            'user_id' => $userId,
            'token_hash' => $tokenHash,
            'remember_selector' => $this->nullableLimited($rememberSelector, 255),
            'user_agent' => $this->nullableLimited($userAgent, 512),
            'device_label' => $device['device_label'],
            'browser' => $device['browser'],
            'platform' => $device['platform'],
            'ip_address' => $this->nullableLimited($ipAddress, 45),
            'created_at' => $now->format('Y-m-d H:i:s'),
            'last_seen_at' => $now->format('Y-m-d H:i:s'),
            'expires_at' => $this->expiresAt($now),
            'revoked_at' => null,
            'revoked_by' => null,
            'revoke_reason' => null,
        ], $previousRawToken !== null && $previousRawToken !== '' ? $this->hashToken($previousRawToken) : null);

        return $this->publicDevice($row);
    }

    public function resolveActive(int $userId, string $rawToken): ?array
    {
        $this->assertUserId($userId);
        $row = $this->repository->findActiveByTokenHash(
            $userId,
            $this->hashToken($rawToken),
            $this->now()->format('Y-m-d H:i:s'),
        );

        return $row ? $this->publicDevice($row) : null;
    }

    public function resolveActiveForRemember(string $rawToken, string $rememberSelector): ?array
    {
        $selector = $this->nullableLimited($rememberSelector, 255);
        if ($selector === null) {
            return null;
        }

        $row = $this->repository->findActiveByTokenHashAndSelector(
            $this->hashToken($rawToken),
            $selector,
            $this->now()->format('Y-m-d H:i:s'),
        );

        return $row ? $this->publicDevice($row) : null;
    }

    public function listActive(int $userId, string $currentRawToken): array
    {
        $this->assertUserId($userId);
        $currentHash = $this->hashToken($currentRawToken);
        $rows = $this->repository->listActive($userId, $this->now()->format('Y-m-d H:i:s'));

        return array_map(function (array $row) use ($currentHash): array {
            $isCurrent = hash_equals((string) $row['token_hash'], $currentHash);
            $row = $this->publicDevice($row);
            $row['is_current'] = $isCurrent;

            return $row;
        }, $rows);
    }

    public function listActiveForUser(int $userId): array
    {
        $this->assertUserId($userId);

        return array_map(
            fn (array $row): array => $this->publicDevice($row),
            $this->repository->listActive($userId, $this->now()->format('Y-m-d H:i:s')),
        );
    }

    public function touchLastSeen(int $userId, int $deviceId): bool
    {
        $this->assertUserId($userId);
        $this->assertDeviceId($deviceId);
        $now = $this->now();

        return $this->repository->touchIfStale(
            $userId,
            $deviceId,
            $now->modify('-' . self::TOUCH_INTERVAL_MINUTES . ' minutes')->format('Y-m-d H:i:s'),
            $now->format('Y-m-d H:i:s'),
            $this->expiresAt($now),
        );
    }

    public function revokeOne(int $userId, int $deviceId, int $revokedBy, ?string $reason = null): bool
    {
        $this->assertUserId($userId);
        $this->assertUserId($revokedBy);
        $this->assertDeviceId($deviceId);

        return $this->repository->revokeOne(
            $userId,
            $deviceId,
            $revokedBy,
            $this->nullableLimited($reason, 255),
            $this->now()->format('Y-m-d H:i:s'),
        );
    }

    public function revokeOthers(
        int $userId,
        string $currentRawToken,
        int $revokedBy,
        ?string $reason = null,
    ): int {
        $this->assertUserId($userId);
        $this->assertUserId($revokedBy);
        $current = $this->repository->findActiveByTokenHash(
            $userId,
            $this->hashToken($currentRawToken),
            $this->now()->format('Y-m-d H:i:s'),
        );

        if (! $current) {
            throw new RuntimeException('Sesi perangkat saat ini tidak aktif.');
        }

        return $this->repository->revokeOthers(
            $userId,
            (int) $current['id'],
            $revokedBy,
            $this->nullableLimited($reason, 255),
            $this->now()->format('Y-m-d H:i:s'),
        );
    }

    public function revokeAll(int $userId, int $revokedBy, ?string $reason = null): int
    {
        $this->assertUserId($userId);
        $this->assertUserId($revokedBy);

        return $this->repository->revokeAll(
            $userId,
            $revokedBy,
            $this->nullableLimited($reason, 255),
            $this->now()->format('Y-m-d H:i:s'),
        );
    }

    public function deleteRememberSelector(string $selector, ?int $userId = null): int
    {
        $selector = $this->nullableLimited($selector, 255);
        if ($selector === null) {
            return 0;
        }
        if ($userId !== null) {
            $this->assertUserId($userId);
        }

        return $this->repository->deleteRememberSelector($selector, $userId);
    }

    private function hashToken(string $rawToken): string
    {
        if ($rawToken === '') {
            throw new InvalidArgumentException('Token perangkat wajib diisi.');
        }

        return hash('sha256', $rawToken);
    }

    private function publicDevice(array $row): array
    {
        unset($row['token_hash'], $row['remember_selector']);

        return $row;
    }

    private function expiresAt(DateTimeImmutable $now): string
    {
        return $now->modify('+' . self::LIFETIME_DAYS . ' days')->format('Y-m-d H:i:s');
    }

    private function now(): DateTimeImmutable
    {
        $now = ($this->clock)();
        if (! $now instanceof DateTimeImmutable) {
            throw new RuntimeException('Clock perangkat harus mengembalikan DateTimeImmutable.');
        }

        return $now;
    }

    private function nullableLimited(?string $value, int $limit): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        if (strlen($value) > $limit) {
            throw new InvalidArgumentException("Nilai melebihi batas {$limit} karakter.");
        }

        return $value;
    }

    private function assertUserId(int $userId): void
    {
        if ($userId <= 0) {
            throw new InvalidArgumentException('ID pengguna tidak valid.');
        }
    }

    private function assertDeviceId(int $deviceId): void
    {
        if ($deviceId <= 0) {
            throw new InvalidArgumentException('ID perangkat tidak valid.');
        }
    }
}

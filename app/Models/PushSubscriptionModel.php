<?php

namespace App\Models;

use CodeIgniter\Model;

class PushSubscriptionModel extends Model
{
    protected $table            = 'push_subscriptions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'provider',
        'endpoint',
        'endpoint_hash',
        'fcm_token',
        'platform',
        'p256dh_key',
        'auth_token',
        'user_agent',
        'device_session_id',
        'last_seen_at',
        'disabled_at',
        'failure_count',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function saveSubscription(int $userId, array $subscriptionData, ?string $userAgent = null, ?int $deviceSessionId = null): bool
    {
        $provider = strtolower(trim((string) ($subscriptionData['provider'] ?? 'web_push')));
        if (! in_array($provider, ['web_push', 'fcm'], true)) {
            return false;
        }

        if ($provider === 'fcm') {
            if (! $this->hasProviderColumn() || ! $this->hasFcmTokenColumn()) {
                return false;
            }

            $fcmToken = trim((string) ($subscriptionData['fcm_token'] ?? ''));
            if ($fcmToken === '') {
                return false;
            }

            $platform = strtolower(trim((string) ($subscriptionData['platform'] ?? 'android')));
            if (! in_array($platform, ['android', 'ios', 'web'], true)) {
                $platform = 'android';
            }

            $endpoint = 'fcm://' . $fcmToken;
            $p256dh = null;
            $auth = null;
        } else {
            $endpoint = trim((string) ($subscriptionData['endpoint'] ?? ''));
            $keys = $subscriptionData['keys'] ?? [];
            $p256dh = trim((string) (is_array($keys) ? ($keys['p256dh'] ?? '') : ''));
            $auth = trim((string) (is_array($keys) ? ($keys['auth'] ?? '') : ''));

            if ($endpoint === '' || $p256dh === '' || $auth === '') {
                return false;
            }

            $fcmToken = null;
            $platform = strtolower(trim((string) ($subscriptionData['platform'] ?? 'web')));
            if (! in_array($platform, ['web', 'android', 'ios'], true)) {
                $platform = 'web';
            }
        }

        if (! $this->hasEndpointHashColumn()) {
            return $this->saveLegacySubscription($userId, $endpoint, (string) $p256dh, (string) $auth, $userAgent, $deviceSessionId);
        }

        $hash = $this->endpointHash($endpoint);
        $existing = $this->where('endpoint_hash', $hash)->first();
        if (! $existing) {
            $existing = $this->where('endpoint', $endpoint)
                ->where('disabled_at IS NULL', null, false)
                ->first();
        }

        $data = [
            'user_id' => $userId,
            'endpoint' => $endpoint,
            'endpoint_hash' => $hash,
            'p256dh_key' => $p256dh,
            'auth_token' => $auth,
            'user_agent' => $userAgent,
            'last_seen_at' => date('Y-m-d H:i:s'),
            'disabled_at' => null,
            'failure_count' => 0,
        ];
        if ($this->hasProviderColumn()) {
            $data['provider'] = $provider;
        }
        if ($this->hasFcmTokenColumn()) {
            $data['fcm_token'] = $fcmToken;
        }
        if ($this->hasPlatformColumn()) {
            $data['platform'] = $platform;
        }
        if ($this->hasDeviceSessionColumn()) {
            $data['device_session_id'] = $deviceSessionId;
        }

        if ($existing) {
            $data['id'] = (int) $existing->id;
        }

        return (bool) $this->save($data);
    }

    public function deleteSubscription(int $userId, string $endpointOrToken): bool
    {
        $value = trim($endpointOrToken);
        if ($value === '') {
            return false;
        }

        if (! $this->hasEndpointHashColumn()) {
            $builder = $this->where('user_id', $userId)
                ->groupStart()
                    ->where('endpoint', $value)
                    ->orWhere('endpoint', 'fcm://' . $value)
                ->groupEnd();

            return (bool) $builder->delete();
        }

        $hashes = [$this->endpointHash($value)];
        $isUri = str_starts_with($value, 'http://') || str_starts_with($value, 'https://') || str_starts_with($value, 'fcm://');
        if (! $isUri) {
            $hashes[] = $this->endpointHash('fcm://' . $value);
        }

        $rawToken = str_starts_with($value, 'fcm://') ? substr($value, 6) : $value;

        $builder = $this->where('user_id', $userId)
            ->groupStart()
                ->whereIn('endpoint_hash', $hashes);

        if ($this->hasFcmTokenColumn() && $rawToken !== '') {
            $builder->orWhere('fcm_token', $rawToken);
        }

        $builder->groupEnd();

        return (bool) $builder->set([
            'disabled_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ])->update();
    }

    public function activeForUser(int $userId, ?string $provider = null): array
    {
        if ($this->hasDeviceSessionColumn()) {
            $now = date('Y-m-d H:i:s');
            $idleCutoff = date('Y-m-d H:i:s', strtotime('-30 days'));

            $builder = $this->db->table('push_subscriptions')
                ->select('push_subscriptions.*')
                ->join('auth_device_sessions', 'auth_device_sessions.id = push_subscriptions.device_session_id')
                ->where('push_subscriptions.user_id', $userId)
                ->where('auth_device_sessions.user_id', $userId)
                ->where('auth_device_sessions.revoked_at', null)
                ->where('auth_device_sessions.expires_at >', $now)
                ->groupStart()
                    ->where('auth_device_sessions.last_seen_at >=', $idleCutoff)
                    ->orGroupStart()
                        ->where('auth_device_sessions.last_seen_at IS NULL', null, false)
                        ->where('auth_device_sessions.created_at >=', $idleCutoff)
                    ->groupEnd()
                ->groupEnd();
            if ($this->hasDisabledAtColumn()) {
                $builder->where('push_subscriptions.disabled_at', null);
            }
            if ($provider !== null && $this->hasProviderColumn()) {
                $builder->where('push_subscriptions.provider', $provider);
            }

            return $builder->get()->getResult();
        }

        $query = $this->where('user_id', $userId);
        if ($this->hasDisabledAtColumn()) {
            $query->where('disabled_at IS NULL', null, false);
        }
        if ($provider !== null && $this->hasProviderColumn()) {
            $query->where('provider', $provider);
        }
        return $query->findAll();
    }

    public function disableEndpoint(string $endpoint): void
    {
        if (! $this->hasEndpointHashColumn() || ! $this->hasDisabledAtColumn()) {
            $this->where('endpoint', $endpoint)->delete();
            return;
        }

        $this->where('endpoint_hash', $this->endpointHash($endpoint))
            ->set([
                'disabled_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ])
            ->update();
    }

    public function recordFailure(string $endpoint): void
    {
        if (! $this->hasEndpointHashColumn() || ! $this->hasFailureCountColumn()) {
            return;
        }

        $row = $this->where('endpoint_hash', $this->endpointHash($endpoint))->first();
        if (! $row) {
            return;
        }

        $this->update((int) $row->id, [
            'failure_count' => ((int) ($row->failure_count ?? 0)) + 1,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function resetFailure(string $endpoint): void
    {
        if (! $this->hasEndpointHashColumn() || ! $this->hasFailureCountColumn()) {
            return;
        }

        $this->where('endpoint_hash', $this->endpointHash($endpoint))
            ->set([
                'failure_count' => 0,
                'last_seen_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ])
            ->update();
    }

    public function endpointHash(string $endpoint): string
    {
        return hash('sha256', $endpoint);
    }

    private function saveLegacySubscription(int $userId, string $endpoint, string $p256dh, string $auth, ?string $userAgent, ?int $deviceSessionId): bool
    {
        $existing = $this->where('user_id', $userId)
            ->where('endpoint', $endpoint)
            ->first();

        $data = [
            'user_id' => $userId,
            'endpoint' => $endpoint,
            'p256dh_key' => $p256dh,
            'auth_token' => $auth,
            'user_agent' => $userAgent,
        ];
        if ($this->hasDeviceSessionColumn()) {
            $data['device_session_id'] = $deviceSessionId;
        }

        if ($existing) {
            $data['id'] = (int) $existing->id;
        }

        return (bool) $this->save($data);
    }

    private function hasEndpointHashColumn(): bool
    {
        return $this->db->fieldExists('endpoint_hash', $this->table);
    }

    private function hasDisabledAtColumn(): bool
    {
        return $this->db->fieldExists('disabled_at', $this->table);
    }

    private function hasFailureCountColumn(): bool
    {
        return $this->db->fieldExists('failure_count', $this->table);
    }

    private function hasDeviceSessionColumn(): bool
    {
        return $this->db->fieldExists('device_session_id', $this->table);
    }

    private function hasProviderColumn(): bool
    {
        return $this->db->fieldExists('provider', $this->table);
    }

    private function hasFcmTokenColumn(): bool
    {
        return $this->db->fieldExists('fcm_token', $this->table);
    }

    private function hasPlatformColumn(): bool
    {
        return $this->db->fieldExists('platform', $this->table);
    }
}

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
        'endpoint',
        'endpoint_hash',
        'p256dh_key',
        'auth_token',
        'user_agent',
        'last_seen_at',
        'disabled_at',
        'failure_count',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function saveSubscription(int $userId, array $subscriptionData, ?string $userAgent = null): bool
    {
        $endpoint = trim((string) ($subscriptionData['endpoint'] ?? ''));
        $keys = $subscriptionData['keys'] ?? [];
        $p256dh = trim((string) ($keys['p256dh'] ?? ''));
        $auth = trim((string) ($keys['auth'] ?? ''));

        if ($endpoint === '' || $p256dh === '' || $auth === '') {
            return false;
        }

        if (! $this->hasEndpointHashColumn()) {
            return $this->saveLegacySubscription($userId, $endpoint, $p256dh, $auth, $userAgent);
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

        if ($existing) {
            $data['id'] = (int) $existing->id;
        }

        return (bool) $this->save($data);
    }

    public function deleteSubscription(int $userId, string $endpoint): bool
    {
        $endpoint = trim($endpoint);
        if ($endpoint === '') {
            return false;
        }

        if (! $this->hasEndpointHashColumn()) {
            return (bool) $this->where('user_id', $userId)
                ->where('endpoint', $endpoint)
                ->delete();
        }

        return (bool) $this->where('user_id', $userId)
            ->where('endpoint_hash', $this->endpointHash($endpoint))
            ->set([
                'disabled_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ])
            ->update();
    }

    public function activeForUser(int $userId): array
    {
        $query = $this->where('user_id', $userId);
        if ($this->hasDisabledAtColumn()) {
            $query->where('disabled_at IS NULL', null, false);
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

    private function saveLegacySubscription(int $userId, string $endpoint, string $p256dh, string $auth, ?string $userAgent): bool
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
}

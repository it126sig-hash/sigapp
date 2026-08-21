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
    protected $allowedFields    = ['user_id', 'endpoint', 'p256dh_key', 'auth_token', 'user_agent'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    
    public function saveSubscription(int $userId, array $subscriptionData, ?string $userAgent = null)
    {
        $endpoint = $subscriptionData['endpoint'] ?? '';
        $keys = $subscriptionData['keys'] ?? [];
        $p256dh = $keys['p256dh'] ?? '';
        $auth = $keys['auth'] ?? '';

        if (empty($endpoint) || empty($p256dh) || empty($auth)) {
            return false;
        }

        $existing = $this->where('user_id', $userId)
                         ->where('endpoint', $endpoint)
                         ->first();

        $data = [
            'user_id'    => $userId,
            'endpoint'   => $endpoint,
            'p256dh_key' => $p256dh,
            'auth_token' => $auth,
            'user_agent' => $userAgent
        ];

        if ($existing) {
            $data['id'] = $existing->id;
        }

        return $this->save($data);
    }

    public function deleteSubscription(int $userId, string $endpoint)
    {
        return $this->where('user_id', $userId)
                    ->where('endpoint', $endpoint)
                    ->delete();
    }
}

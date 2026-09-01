<?php

namespace App\Services;

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;
use App\Models\PushSubscriptionModel;

class WebPushService
{
    protected $webPush;
    protected $pushModel;
    protected $db;

    public function __construct()
    {
        $this->pushModel = new PushSubscriptionModel();
        $this->db = db_connect();

        $auth = [
            'VAPID' => [
                'subject' => getenv('VAPID_SUBJECT') ?: 'mailto:admin@sigapp.dev',
                'publicKey' => getenv('VAPID_PUBLIC_KEY') ?: '',
                'privateKey' => getenv('VAPID_PRIVATE_KEY') ?: '',
            ],
        ];

        try {
            $this->webPush = new WebPush($auth);
        } catch (\Exception $e) {
            log_message('error', 'WebPush init error: ' . $e->getMessage());
            $this->webPush = null;
        }
    }

    public function subscribe(int $userId, array $subscriptionData, ?string $userAgent = null)
    {
        return $this->pushModel->saveSubscription($userId, $subscriptionData, $userAgent);
    }

    public function unsubscribe(int $userId, string $endpoint)
    {
        return $this->pushModel->deleteSubscription($userId, $endpoint);
    }

    /**
     * Send push notification to all devices of a specific user
     */
    public function sendToUser(int $userId, string $title, string $body, string $url = '/')
    {
        if (!$this->webPush) return false;

        $subscriptions = $this->pushModel->where('user_id', $userId)->findAll();
        if (empty($subscriptions)) return false;

        $payload = json_encode([
            'title' => $title,
            'body'  => $body,
            'url'   => $url
        ]);

        foreach ($subscriptions as $sub) {
            $subscription = Subscription::create([
                'endpoint' => $sub->endpoint,
                'publicKey' => $sub->p256dh_key,
                'authToken' => $sub->auth_token,
            ]);

            $this->webPush->queueNotification($subscription, $payload);
        }

        $successCount = 0;
        foreach ($this->webPush->flush() as $report) {
            $endpoint = $report->getRequest()->getUri()->__toString();
            if ($report->isSuccess()) {
                $successCount++;
            } else {
                log_message('error', "Push send failed to endpoint {$endpoint}: {$report->getReason()}");
                if ($report->isSubscriptionExpired()) {
                    $this->pushModel->where('endpoint', $endpoint)->delete();
                }
            }
        }

        return $successCount > 0;
    }

    /**
     * Send push notification to a group/department, excluding the actor
     */
    public function sendToGroup(string $groupId, string $title, string $body, string $url = '/', int $excludeUserId = 0)
    {
        if (!$this->webPush || $groupId === '') return false;

        if ($groupId === '0') {
            $builder = $this->db->table('users')
                ->select('id as user_id')
                ->where('active', 1)
                ->where('deleted_at', null);
            
            if ($excludeUserId > 0) {
                $builder->where('id !=', $excludeUserId);
            }
        } else {
            $groups = explode(';', $groupId);
            $builder = $this->db->table('auth_groups_users')
                ->select('user_id')
                ->whereIn('group_id', $groups);
                
            if ($excludeUserId > 0) {
                $builder->where('user_id !=', $excludeUserId);
            }
        }
        
        $users = $builder->get()->getResult();
        
        $successCount = 0;
        $sentUsers = [];
        
        foreach ($users as $u) {
            $uid = (int)$u->user_id;
            
            // Deduplicate to avoid sending multiple push notifications to the same user
            if (in_array($uid, $sentUsers)) {
                continue;
            }
            
            if ($this->sendToUser($uid, $title, $body, $url)) {
                $successCount++;
            }
            
            $sentUsers[] = $uid;
        }
        
        return $successCount > 0;
    }
}

<?php

namespace App\Services;

use App\Models\PushSubscriptionModel;
use GuzzleHttp\Client;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\VAPID;
use Minishlink\WebPush\WebPush;
use Throwable;

class WebPushService
{
    protected ?WebPush $webPush = null;
    protected PushSubscriptionModel $pushModel;
    protected $db;
    protected array $auth;
    protected ?string $initError = null;

    public function __construct()
    {
        $this->pushModel = new PushSubscriptionModel();
        $this->db = db_connect();
        $this->auth = [
            'VAPID' => [
                'subject' => getenv('VAPID_SUBJECT') ?: 'mailto:admin@sigapp.dev',
                'publicKey' => getenv('VAPID_PUBLIC_KEY') ?: '',
                'privateKey' => getenv('VAPID_PRIVATE_KEY') ?: '',
            ],
        ];

        try {
            $this->validateVapid();
            $client = new Client([
                'timeout' => 15,
                'connect_timeout' => 5,
            ]);
            $this->webPush = new WebPush($this->auth, [
                'TTL' => 3600,
                'urgency' => 'normal',
                'batchSize' => 50,
            ], $client);
        } catch (Throwable $e) {
            $this->initError = $e->getMessage();
            log_message('error', 'WebPush init error: ' . $this->initError);
            $this->webPush = null;
        }
    }

    public function isReady(): bool
    {
        return $this->webPush !== null;
    }

    public function initError(): ?string
    {
        return $this->initError;
    }

    public function validateVapid(): void
    {
        VAPID::validate($this->auth['VAPID']);
    }

    public function subscribe(int $userId, array $subscriptionData, ?string $userAgent = null): bool
    {
        return $this->pushModel->saveSubscription($userId, $subscriptionData, $userAgent);
    }

    public function unsubscribe(int $userId, string $endpoint): bool
    {
        return $this->pushModel->deleteSubscription($userId, $endpoint);
    }

    public function activeSubscriptionCount(int $userId): int
    {
        return count($this->pushModel->activeForUser($userId));
    }

    public function sendToUser(int $userId, string $title, string $body, string $url = '/'): bool
    {
        $result = $this->sendToSubscriptions($this->pushModel->activeForUser($userId), $title, $body, $url);

        return $result['success'] > 0;
    }

    public function sendToGroup(string $groupId, string $title, string $body, string $url = '/', int $excludeUserId = 0): bool
    {
        $userIds = $this->resolveGroupUserIds($groupId, $excludeUserId);
        $successCount = 0;

        foreach ($userIds as $userId) {
            if ($this->sendToUser($userId, $title, $body, $url)) {
                $successCount++;
            }
        }

        return $successCount > 0;
    }

    public function sendToSubscriptions(array $subscriptions, string $title, string $body, string $url = '/'): array
    {
        $result = [
            'success' => 0,
            'failed' => 0,
            'expired' => 0,
            'retryable' => 0,
            'errors' => [],
        ];

        if (! $this->webPush) {
            $result['failed'] = count($subscriptions);
            $result['retryable'] = count($subscriptions);
            $result['errors'][] = $this->initError ?: 'WebPush tidak siap.';
            return $result;
        }

        if ($subscriptions === []) {
            return $result;
        }

        $payload = json_encode([
            'title' => $title,
            'body' => $body,
            'url' => $url,
            'tag' => 'sigapp-notif-' . md5($body . $url),
        ]);

        foreach ($subscriptions as $sub) {
            try {
                $subscription = Subscription::create([
                    'endpoint' => $sub->endpoint,
                    'publicKey' => $sub->p256dh_key,
                    'authToken' => $sub->auth_token,
                ]);
                $this->webPush->queueNotification($subscription, $payload);
            } catch (Throwable $e) {
                $result['failed']++;
                $result['errors'][] = $e->getMessage();
                $this->pushModel->recordFailure((string) ($sub->endpoint ?? ''));
            }
        }

        foreach ($this->webPush->flush() as $report) {
            $endpoint = $report->getEndpoint();
            if ($report->isSuccess()) {
                $result['success']++;
                $this->pushModel->resetFailure($endpoint);
                continue;
            }

            $result['failed']++;
            $reason = $report->getReason();
            $response = $report->getResponse();
            $statusCode = $response ? $response->getStatusCode() : 0;
            $result['errors'][] = $statusCode > 0 ? "{$statusCode}: {$reason}" : $reason;

            if ($report->isSubscriptionExpired()) {
                $result['expired']++;
                $this->pushModel->disableEndpoint($endpoint);
                continue;
            }

            if ($statusCode === 0 || $statusCode === 429 || $statusCode >= 500) {
                $result['retryable']++;
            }

            $this->pushModel->recordFailure($endpoint);
            log_message('error', "Push send failed to endpoint {$endpoint}: {$reason}");
        }

        return $result;
    }

    public function buildActorPayload(string $message, ?string $actorName = null, ?string $actorUsername = null, ?string $department = null): array
    {
        $title = trim((string) ($actorName ?: $actorUsername ?: 'SIGAPP'));
        $department = trim((string) ($department ?: 'Umum'));
        $bodyMessage = $this->limitText($this->plainText($message), 180);

        return [
            'title' => $title !== '' ? $title : 'SIGAPP',
            'body' => '[' . ($department !== '' ? $department : 'Umum') . '] ' . ($bodyMessage !== '' ? $bodyMessage : 'Ada notifikasi baru'),
        ];
    }

    private function plainText(string $value): string
    {
        $decoded = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $decoded = preg_replace('/<\s*(br|\/p|\/div|\/li)\s*\/?>/i', ' ', $decoded);
        $text = trim(strip_tags($decoded));
        $text = preg_replace('/\s+/u', ' ', $text);

        return $text ?: 'Ada notifikasi baru';
    }

    private function limitText(string $value, int $limit): string
    {
        if ($limit <= 0) {
            return $value;
        }

        if (function_exists('mb_strlen') && function_exists('mb_substr')) {
            return mb_strlen($value) > $limit ? rtrim(mb_substr($value, 0, $limit - 1)) . '…' : $value;
        }

        return strlen($value) > $limit ? rtrim(substr($value, 0, $limit - 3)) . '...' : $value;
    }

    protected function resolveGroupUserIds(string $groupId, int $excludeUserId = 0): array
    {
        if ($groupId === '') {
            return [];
        }

        if ($groupId === '0') {
            $builder = $this->db->table('users')
                ->select('id as user_id')
                ->where('active', 1)
                ->where('deleted_at IS NULL', null, false);
        } else {
            $groups = array_values(array_filter(array_map('intval', explode(';', $groupId))));
            if ($groups === []) {
                return [];
            }

            $builder = $this->db->table('auth_groups_users')
                ->select('user_id')
                ->whereIn('group_id', $groups);
        }

        if ($excludeUserId > 0) {
            $builder->where($groupId === '0' ? 'id !=' : 'user_id !=', $excludeUserId);
        }

        $users = [];
        foreach ($builder->get()->getResult() as $row) {
            $users[(int) $row->user_id] = (int) $row->user_id;
        }

        return array_values($users);
    }
}

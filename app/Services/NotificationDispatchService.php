<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

class NotificationDispatchService
{
    private const MAX_ATTEMPTS = 5;

    private BaseConnection $db;
    private WebPushService $webPushService;

    public function __construct(?BaseConnection $db = null, ?WebPushService $webPushService = null)
    {
        $this->db = $db ?? db_connect();
        $this->webPushService = $webPushService ?? new WebPushService();
    }

    public function dispatch(string $channel = 'web_push', int $limit = 100): array
    {
        if (! $this->db->tableExists('notification_deliveries')) {
            return [
                'claimed' => 0,
                'sent' => 0,
                'failed' => 0,
                'skipped' => 0,
                'retrying' => 0,
                'message' => 'Tabel notification_deliveries belum tersedia.',
            ];
        }

        if ($channel !== 'web_push') {
            return [
                'claimed' => 0,
                'sent' => 0,
                'failed' => 0,
                'skipped' => 0,
                'retrying' => 0,
                'message' => 'Channel belum didukung oleh dispatcher ini.',
            ];
        }

        $deliveries = $this->claim($channel, max(1, $limit));
        $stats = [
            'claimed' => count($deliveries),
            'sent' => 0,
            'failed' => 0,
            'skipped' => 0,
            'retrying' => 0,
            'message' => '',
        ];

        foreach ($deliveries as $delivery) {
            $result = $this->dispatchWebPush($delivery);
            $stats[$result]++;
        }

        return $stats;
    }

    private function claim(string $channel, int $limit): array
    {
        $now = date('Y-m-d H:i:s');
        $claimToken = $this->claimToken();

        $rows = $this->db->table('notification_deliveries')
            ->select('id')
            ->where('channel', $channel)
            ->whereIn('status', ['pending', 'failed'])
            ->where('available_at <=', $now)
            ->where('attempts <', self::MAX_ATTEMPTS)
            ->orderBy('available_at', 'asc')
            ->orderBy('id', 'asc')
            ->limit($limit)
            ->get()
            ->getResult();

        $ids = array_map(static fn ($row) => (int) $row->id, $rows);
        if ($ids === []) {
            return [];
        }

        $this->db->table('notification_deliveries')
            ->whereIn('id', $ids)
            ->whereIn('status', ['pending', 'failed'])
            ->set([
                'status' => 'processing',
                'claim_token' => $claimToken,
                'claimed_at' => $now,
                'updated_at' => $now,
            ])
            ->update();

        return $this->db->table('notification_deliveries d')
            ->select('d.*, n.notif, n.type, n.id_kavling')
            ->join('notification n', 'n.id = d.notification_id')
            ->where('d.claim_token', $claimToken)
            ->orderBy('d.id', 'asc')
            ->get()
            ->getResult();
    }

    private function dispatchWebPush(object $delivery): string
    {
        $subscriptions = $this->db->table('push_subscriptions')
            ->where('user_id', (int) $delivery->user_id)
            ->where('disabled_at IS NULL', null, false)
            ->get()
            ->getResult();

        if ($subscriptions === []) {
            $this->finishDelivery((int) $delivery->id, 'skipped', (int) $delivery->attempts, 'Tidak ada subscription push aktif.');
            return 'skipped';
        }

        $send = $this->webPushService->sendToSubscriptions(
            $subscriptions,
            'SIGAPP',
            (string) $delivery->notif,
            $this->urlForNotification($delivery)
        );

        if (($send['success'] ?? 0) > 0) {
            $this->finishDelivery((int) $delivery->id, 'sent', (int) $delivery->attempts, null);
            return 'sent';
        }

        $error = implode('; ', array_slice($send['errors'] ?? [], 0, 3));
        if ($error === '') {
            $error = 'Push gagal tanpa detail response.';
        }

        if (($send['retryable'] ?? 0) > 0 && ((int) $delivery->attempts + 1) < self::MAX_ATTEMPTS) {
            $this->retryDelivery((int) $delivery->id, (int) $delivery->attempts, $error);
            return 'retrying';
        }

        $this->finishDelivery((int) $delivery->id, 'failed', (int) $delivery->attempts, $error);
        return 'failed';
    }

    private function retryDelivery(int $deliveryId, int $currentAttempts, string $error): void
    {
        $attempts = $currentAttempts + 1;
        $this->db->table('notification_deliveries')
            ->where('id', $deliveryId)
            ->update([
                'status' => 'failed',
                'attempts' => $attempts,
                'available_at' => date('Y-m-d H:i:s', time() + ($this->retryDelayMinutes($attempts) * 60)),
                'processed_at' => null,
                'claim_token' => null,
                'claimed_at' => null,
                'last_error' => $error,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
    }

    private function finishDelivery(int $deliveryId, string $status, int $currentAttempts, ?string $error): void
    {
        $this->db->table('notification_deliveries')
            ->where('id', $deliveryId)
            ->update([
                'status' => $status,
                'attempts' => $status === 'sent' || $status === 'skipped' ? $currentAttempts : $currentAttempts + 1,
                'processed_at' => date('Y-m-d H:i:s'),
                'claim_token' => null,
                'claimed_at' => null,
                'last_error' => $error,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
    }

    private function retryDelayMinutes(int $attempts): int
    {
        return match ($attempts) {
            1 => 1,
            2 => 5,
            3 => 15,
            default => 60,
        };
    }

    private function urlForNotification(object $delivery): string
    {
        if (! empty($delivery->id_kavling)) {
            return site_url('siteplan/view') . '?id_kavling=' . rawurlencode((string) $delivery->id_kavling);
        }

        return site_url('/');
    }

    private function claimToken(): string
    {
        return bin2hex(random_bytes(16));
    }
}

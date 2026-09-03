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

        $actorDepartment = $this->db->table('auth_groups_users agu')
            ->select('agu.user_id, MIN(ag.name) as department_name', false)
            ->join('auth_groups ag', 'ag.id = agu.group_id')
            ->groupBy('agu.user_id')
            ->getCompiledSelect();

        return $this->db->table('notification_deliveries d')
            ->select('d.*, n.notif, n.type, n.id_kavling, n.id_proyek')
            ->select('actor.name as actor_name, actor.username as actor_username')
            ->select('actor_department.department_name as actor_department')
            ->join('notification n', 'n.id = d.notification_id')
            ->join('users actor', 'actor.id = n.add_by', 'left')
            ->join("({$actorDepartment}) actor_department", 'actor_department.user_id = n.add_by', 'left')
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

        $idProyek = (int) ($delivery->id_proyek ?? 0);
        $namaProyek = $idProyek > 0 ? $this->getNamaProyek($idProyek) : null;
        
        if (! $namaProyek && ! empty($delivery->id_kavling)) {
            $idProyek = $this->resolveProyekIdFromKavling($delivery->id_kavling);
            if ($idProyek > 0) {
                $namaProyek = $this->getNamaProyek($idProyek);
            }
        }

        $payload = $this->webPushService->buildActorPayload(
            (string) $delivery->notif,
            $delivery->actor_name ?? null,
            $delivery->actor_username ?? null,
            $delivery->actor_department ?? null,
            $namaProyek
        );

        $icon = $idProyek > 0 ? site_url('notif/icon/' . $idProyek) : null;
        $url = site_url('notif/open/' . $delivery->notification_id);

        $send = $this->webPushService->sendToSubscriptions(
            $subscriptions,
            $payload['title'],
            $payload['body'],
            $url,
            $icon,
            (int) $delivery->notification_id
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

    private function getNamaProyek(int $idProyek): ?string
    {
        if ($idProyek <= 0) return null;
        $proyek = $this->db->table('proyek')->select('nama_proyek')->where('id_proyek', $idProyek)->get()->getRow();
        return $proyek ? $proyek->nama_proyek : null;
    }

    private function resolveProyekIdFromKavling($idKavling): int
    {
        $kavling = $this->db->table('kavling')
            ->select('cluster.id_proyek')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan', 'left')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster', 'left')
            ->where('kavling.id_kavling', $idKavling)
            ->get()
            ->getRow();
        
        return $kavling && $kavling->id_proyek ? (int) $kavling->id_proyek : 0;
    }
}

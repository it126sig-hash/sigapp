<?php

namespace App\Controllers\Api;

use App\Services\WebPushService;
use CodeIgniter\HTTP\ResponseInterface;

class NotifPushController extends BaseApiController
{
    protected WebPushService $webPushService;

    public function __construct()
    {
        $this->webPushService = new WebPushService();
    }

    public function status(): ResponseInterface
    {
        $userId = (int) user_id();

        return $this->success([
            'ready' => $this->webPushService->isReady(),
            'has_vapid_public_key' => (bool) getenv('VAPID_PUBLIC_KEY'),
            'active_subscriptions' => $this->webPushService->activeSubscriptionCount($userId),
        ]);
    }

    public function subscribe(): ResponseInterface
    {
        $json = $this->request->getJSON(true);
        if (! $this->validSubscriptionPayload($json)) {
            return $this->error('Data subscription tidak valid.', 422);
        }

        $userAgent = $this->request->getUserAgent()->getAgentString();
        $saved = $this->webPushService->subscribe((int) user_id(), $json, $userAgent);
        if (! $saved) {
            return $this->error('Gagal menyimpan subscription.', 500);
        }

        return $this->success([
            'active_subscriptions' => $this->webPushService->activeSubscriptionCount((int) user_id()),
        ], 'Subscription push aktif.');
    }

    public function unsubscribe(): ResponseInterface
    {
        $json = $this->request->getJSON(true);
        $endpoint = is_array($json) ? trim((string) ($json['endpoint'] ?? '')) : '';
        if ($endpoint === '') {
            return $this->error('Endpoint subscription tidak valid.', 422);
        }

        $this->webPushService->unsubscribe((int) user_id(), $endpoint);

        return $this->success(null, 'Subscription push dinonaktifkan.');
    }

    public function test(): ResponseInterface
    {
        if (! $this->allowPushTest()) {
            return $this->error('Terlalu sering mengirim test push. Coba lagi sebentar.', 429);
        }

        if (! $this->webPushService->isReady()) {
            return $this->error('Web Push belum siap. Periksa VAPID dan PSR-18 HTTP client.', 503);
        }

        $userId = (int) user_id();
        $idProyek = (int) session()->get('id_proyek');
        if ($idProyek <= 0) {
            $idProyek = (int) $this->request->getGet('id_proyek');
        }
        
        $namaProyek = null;
        if ($idProyek > 0) {
            $proyek = db_connect()->table('proyek')->select('nama_proyek')->where('id_proyek', $idProyek)->get()->getRow();
            $namaProyek = $proyek ? $proyek->nama_proyek : null;
        }
        
        $actor = $this->currentActorContext($userId);
        $payload = $this->webPushService->buildActorPayload(
            'Test push notification berhasil dikirim.',
            $actor['name'] ?? null,
            $actor['username'] ?? null,
            $actor['department'] ?? null,
            $namaProyek
        );
        $icon = $idProyek > 0 ? site_url('notif/icon/' . $idProyek) : null;
        
        $subs = (new \App\Models\PushSubscriptionModel())->activeForUser($userId);
        $res = $this->webPushService->sendToSubscriptions($subs, $payload['title'], $payload['body'], site_url('/'), $icon, null);
        
        if ($res['success'] == 0) {
            return $this->error('Pengiriman gagal. Errors: ' . implode(', ', $res['errors']), 422);
        }

        return $this->success($res, 'Test push dikirim.');
    }

    private function validSubscriptionPayload($payload): bool
    {
        if (! is_array($payload)) {
            return false;
        }

        $endpoint = trim((string) ($payload['endpoint'] ?? ''));
        $keys = $payload['keys'] ?? [];

        return $endpoint !== ''
            && is_array($keys)
            && trim((string) ($keys['p256dh'] ?? '')) !== ''
            && trim((string) ($keys['auth'] ?? '')) !== '';
    }

    private function allowPushTest(): bool
    {
        $cache = cache();
        $key = 'notif_push_test_' . (int) user_id();
        $count = (int) ($cache->get($key) ?? 0);
        if ($count >= 5) {
            return false;
        }

        $cache->save($key, $count + 1, 60);

        return true;
    }

    private function currentActorContext(int $userId): array
    {
        $db = db_connect();
        $departmentSubquery = $db->table('auth_groups_users agu')
            ->select('agu.user_id, MIN(ag.name) as department', false)
            ->join('auth_groups ag', 'ag.id = agu.group_id')
            ->groupBy('agu.user_id')
            ->getCompiledSelect();

        $row = $db->table('users')
            ->select('users.name, users.username, actor_department.department')
            ->join("({$departmentSubquery}) actor_department", 'actor_department.user_id = users.id', 'left')
            ->where('users.id', $userId)
            ->get()
            ->getRowArray();

        return $row ?: [];
    }
}

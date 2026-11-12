<?php

namespace App\Controllers\Api\Notifikasi;

use App\Controllers\Api\BaseApiController;
use App\Repositories\Notifikasi\NotificationRepository;
use App\Services\Notifikasi\WebPushService;
use CodeIgniter\HTTP\ResponseInterface;

class NotifPushController extends BaseApiController
{
    protected WebPushService $webPushService;
    protected NotificationRepository $notificationRepository;

    public function __construct(
        ?WebPushService $webPushService = null,
        ?NotificationRepository $notificationRepository = null
    ) {
        $this->webPushService = $webPushService ?? new WebPushService();
        $this->notificationRepository = $notificationRepository ?? new NotificationRepository();
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
        $deviceSessionId = (int) session()->get('sigapp_device_id');
        $saved = $this->webPushService->subscribe((int) user_id(), $json, $userAgent, $deviceSessionId > 0 ? $deviceSessionId : null);
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

        $namaProyek = $idProyek > 0 ? $this->notificationRepository->getProyekName($idProyek) : null;

        $actor = $this->notificationRepository->getActorDepartmentContext($userId);
        $payload = $this->webPushService->buildActorPayload(
            'Test push notification berhasil dikirim.',
            $actor['name'] ?? null,
            $actor['username'] ?? null,
            $actor['department'] ?? null,
            $namaProyek
        );
        $icon = $idProyek > 0 ? site_url('notif/icon/' . $idProyek) : null;

        $subs = $this->webPushService->getActiveSubscriptionsForUser($userId);
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
}

<?php

namespace App\Controllers\Api\Notifikasi;

use App\Controllers\Api\BaseApiController;
use App\Repositories\Notifikasi\NotificationRepository;
use App\Services\DeviceSessionService;
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
        $json = $this->readJsonOrPostPayload();
        if (! $this->validSubscriptionPayload($json)) {
            return $this->error('Data subscription tidak valid.', 422);
        }

        $userId = (int) user_id();
        $userAgent = $this->request->getUserAgent()->getAgentString();
        $deviceSessionId = $this->resolveDeviceSessionId($userId);
        $saved = $this->webPushService->subscribe($userId, $json, $userAgent, $deviceSessionId);
        if (! $saved) {
            return $this->error('Gagal menyimpan subscription.', 500);
        }

        $provider = strtolower(trim((string) ($json['provider'] ?? 'web_push')));

        return $this->success([
            'active_subscriptions' => $this->webPushService->activeSubscriptionCount($userId),
            'provider' => $provider,
        ], 'Subscription push aktif.');
    }

    public function unsubscribe(): ResponseInterface
    {
        $json = $this->readJsonOrPostPayload();
        $endpoint = is_array($json) ? trim((string) ($json['endpoint'] ?? '')) : '';
        $fcmToken = is_array($json) ? trim((string) ($json['fcm_token'] ?? '')) : '';
        $target = $endpoint !== '' ? $endpoint : $fcmToken;

        if ($target === '') {
            return $this->error('Endpoint atau FCM token subscription tidak valid.', 422);
        }

        $this->webPushService->unsubscribe((int) user_id(), $target);

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

        $subs = array_values(array_filter(
            $this->webPushService->getActiveSubscriptionsForUser($userId),
            static fn ($sub): bool => ($sub->provider ?? 'web_push') === 'web_push'
        ));
        $res = $this->webPushService->sendToSubscriptions($subs, $payload['title'], $payload['body'], site_url('/'), $icon, null);

        if ($res['success'] == 0) {
            return $this->error('Pengiriman gagal. Errors: ' . implode(', ', $res['errors']), 422);
        }

        return $this->success($res, 'Test push dikirim.');
    }

    private function readJsonOrPostPayload(): mixed
    {
        try {
            $json = $this->request->getJSON(true);
            if (is_array($json)) {
                return $json;
            }
        } catch (\Throwable) {
            // fallback below
        }

        $post = $this->request->getPost();
        return is_array($post) && $post !== [] ? $post : null;
    }

    private function resolveDeviceSessionId(int $userId): ?int
    {
        $sessionDeviceId = (int) session()->get('sigapp_device_id');
        if ($sessionDeviceId > 0) {
            return $sessionDeviceId;
        }

        if ($userId <= 0) {
            return null;
        }

        $rawToken = trim((string) $this->request->getCookie(DeviceSessionService::SPA_TOKEN_COOKIE));
        if ($rawToken === '') {
            $authHeader = $this->request->header('Authorization')?->getValue()
                ?? $this->request->getServer('HTTP_AUTHORIZATION')
                ?? $this->request->getServer('REDIRECT_HTTP_AUTHORIZATION')
                ?? '';
            if (preg_match('/^Bearer\s+(\S+)/i', trim((string) $authHeader), $matches)) {
                $rawToken = trim((string) $matches[1]);
            }
        }

        if ($rawToken === '') {
            return null;
        }

        $device = (new DeviceSessionService())->resolveActive($userId, $rawToken);
        $resolvedId = (int) ($device['id'] ?? 0);

        return $resolvedId > 0 ? $resolvedId : null;
    }

    private function validSubscriptionPayload($payload): bool
    {
        if (! is_array($payload)) {
            return false;
        }

        $provider = strtolower(trim((string) ($payload['provider'] ?? 'web_push')));
        if ($provider === 'fcm') {
            $fcmToken = trim((string) ($payload['fcm_token'] ?? ''));
            if ($fcmToken === '') {
                return false;
            }

            if (isset($payload['platform'])) {
                $platform = strtolower(trim((string) $payload['platform']));
                if (! in_array($platform, ['android', 'ios', 'web'], true)) {
                    return false;
                }
            }

            return true;
        }

        if ($provider !== 'web_push') {
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

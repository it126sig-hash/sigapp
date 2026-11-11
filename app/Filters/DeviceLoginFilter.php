<?php

namespace App\Filters;

use App\Services\DeviceSessionService;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Session\SessionInterface;
use Config\App;
use WeakMap;

class DeviceLoginFilter implements FilterInterface
{
    private const DEVICE_COOKIE = 'sigapp_device';
    private const SPA_TOKEN_COOKIE = 'sigapp_spa_token';

    private static ?WeakMap $processedRequests = null;
    private DeviceSessionService $deviceSessions;
    private object $auth;
    private SessionInterface $session;
    private ResponseInterface $response;

    public function __construct(
        ?DeviceSessionService $deviceSessions = null,
        ?object $auth = null,
        ?SessionInterface $session = null,
        ?ResponseInterface $response = null,
    ) {
        $this->deviceSessions = $deviceSessions ?? new DeviceSessionService();
        $this->auth = $auth ?? service('authentication');
        $this->session = $session ?? service('session');
        $this->response = $response ?? service('response');

        if (! function_exists('logged_in')) {
            helper('auth');
        }

        self::$processedRequests ??= new WeakMap();
    }

    public function before(RequestInterface $request, $arguments = null)
    {
        if (strtoupper($request->getMethod()) === 'OPTIONS') {
            return $this->response->setStatusCode(200);
        }

        if ($this->isReservedAuthRoute($request)) {
            return null;
        }
        if (isset(self::$processedRequests[$request])) {
            return null;
        }
        self::$processedRequests[$request] = true;

        $authHeader = $request->header('Authorization')?->getValue()
            ?? $request->getServer('HTTP_AUTHORIZATION')
            ?? $request->getServer('REDIRECT_HTTP_AUTHORIZATION')
            ?? '';

        $spaCookieToken = trim((string) $request->getCookie(self::SPA_TOKEN_COOKIE));

        $rawBearerToken = null;
        if (preg_match('/^Bearer\s+(\S+)/i', trim((string) $authHeader), $matches)) {
            $rawBearerToken = $matches[1];
        } elseif ($spaCookieToken !== '') {
            $rawBearerToken = $spaCookieToken;
        } elseif (!empty($request->getGet('token'))) {
            $rawBearerToken = trim((string) $request->getGet('token'));
        } elseif (!empty($request->getGet('access_token'))) {
            $rawBearerToken = trim((string) $request->getGet('access_token'));
        }

        if ($rawBearerToken) {
            $device = $this->deviceSessions->resolveActiveByToken($rawBearerToken);
            if ($device && $this->deviceSessions->verifyDeviceBinding($device, (string) $request->getUserAgent())) {
                $userId = (int) ($device['user_id'] ?? 0);
                if ($userId > 0) {
                    $userModel = model(\App\Models\UserModel::class);
                    $user = $userModel->find($userId);
                    if ($user && (! method_exists($user, 'isBanned') || ! $user->isBanned())) {
                        $this->session->set('logged_in', $userId);
                        if (method_exists($this->auth, 'login')) {
                            $this->auth->login($user);
                        }
                        $this->accept($userId, $device);

                        return null;
                    }
                }
            }

            return $this->reject($request, null, null);
        }

        $userId = (int) ($this->session->get('logged_in') ?? 0);
        $rawDeviceToken = trim((string) $request->getCookie(self::DEVICE_COOKIE));
        $rememberSelector = $this->rememberSelector((string) $request->getCookie('remember'));

        if ($userId > 0) {
            $device = $this->resolveSessionDevice($userId, $rawDeviceToken, $rememberSelector);
            if (! $device || ! $this->auth->check() || (int) ($this->auth->user()->id ?? 0) !== $userId) {
                return $this->reject($request, $rememberSelector, $userId);
            }

            $this->accept($userId, $device);

            return null;
        }

        if ($rawDeviceToken !== '' && $rememberSelector !== null) {
            $device = $this->deviceSessions->resolveActiveForRemember($rawDeviceToken, $rememberSelector);
            if ($device && $this->auth->check()) {
                $authenticatedUserId = (int) ($this->auth->user()->id ?? 0);
                if ($authenticatedUserId > 0 && $authenticatedUserId === (int) $device['user_id']) {
                    $this->accept($authenticatedUserId, $device);

                    return null;
                }
            }
        }

        return $this->reject($request, $rememberSelector, null);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }

    private function resolveSessionDevice(int $userId, string $rawToken, ?string $selector): ?array
    {
        if ($rawToken === '') {
            return null;
        }
        if ($selector !== null) {
            $device = $this->deviceSessions->resolveActiveForRemember($rawToken, $selector);

            return $device && (int) $device['user_id'] === $userId ? $device : null;
        }

        return $this->deviceSessions->resolveActive($userId, $rawToken);
    }

    private function accept(int $userId, array $device): void
    {
        $deviceId = (int) $device['id'];
        $this->session->set('sigapp_device_id', $deviceId);
        $this->deviceSessions->touchLastSeen($userId, $deviceId);
    }

    private function reject(
        RequestInterface $request,
        ?string $rememberSelector,
        ?int $userId,
    ): ResponseInterface {
        if ($rememberSelector !== null) {
            $this->deviceSessions->deleteRememberSelector($rememberSelector, $userId);
        }

        $this->session->remove(['logged_in', 'sigapp_device_id']);
        $this->session->regenerate(true);
        $this->expireAuthCookies();

        $path = trim($request->getUri()->getPath(), '/');
        $isApi = str_starts_with($path, 'api/') || str_contains($path, '/api/');
        $isFiles = str_starts_with($path, 'files/') || str_contains($path, '/files/');
        $acceptHeader = $request->header('Accept')?->getValue() ?? '';
        $isJson = str_contains($acceptHeader, 'application/json');
        $isAjax = $request->isAJAX();

        if ($isApi || $isFiles || $isJson || $isAjax) {
            return $this->response->setStatusCode(401)->setJSON([
                'success' => false,
                'messages' => 'Sesi tidak valid atau telah kadaluarsa. Silakan login kembali.',
                'data' => null,
            ]);
        }

        $this->session->set('redirect_url', (string) $request->getUri());

        return $this->response->redirect(site_url(config('Auth')->reservedRoutes['login']));
    }

    private function expireAuthCookies(): void
    {
        $app = config(App::class);
        $this->response->deleteCookie('remember', $app->cookieDomain, $app->cookiePath, $app->cookiePrefix);
        $this->response->deleteCookie(self::DEVICE_COOKIE, $app->cookieDomain, $app->cookiePath, $app->cookiePrefix);
        $this->response->deleteCookie(self::SPA_TOKEN_COOKIE, $app->cookieDomain, $app->cookiePath, $app->cookiePrefix);
    }

    private function rememberSelector(string $rememberCookie): ?string
    {
        if ($rememberCookie === '' || ! str_contains($rememberCookie, ':')) {
            return null;
        }

        [$selector, $validator] = explode(':', $rememberCookie, 2);

        return $selector !== '' && $validator !== '' ? $selector : null;
    }

    private function isReservedAuthRoute(RequestInterface $request): bool
    {
        $path = trim($request->getUri()->getPath(), '/');
        if ($path === 'api/auth/login' || str_ends_with($path, 'api/auth/login')) {
            return true;
        }

        foreach (config('Auth')->reservedRoutes as $route) {
            $r = trim((string) $route, '/');
            if ($path === $r || str_ends_with($path, '/' . $r)) {
                return true;
            }
        }

        return false;
    }
}

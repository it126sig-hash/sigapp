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
        if ($this->isReservedAuthRoute($request)) {
            return null;
        }
        if (isset(self::$processedRequests[$request])) {
            return null;
        }
        self::$processedRequests[$request] = true;

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
        $this->session->set('redirect_url', (string) $request->getUri());
        $this->expireAuthCookies();

        return $this->response->redirect(site_url(config('Auth')->reservedRoutes['login']));
    }

    private function expireAuthCookies(): void
    {
        $app = config(App::class);
        $this->response->deleteCookie('remember', $app->cookieDomain, $app->cookiePath, $app->cookiePrefix);
        $this->response->deleteCookie(self::DEVICE_COOKIE, $app->cookieDomain, $app->cookiePath, $app->cookiePrefix);
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
        foreach (config('Auth')->reservedRoutes as $route) {
            if ($path === trim((string) $route, '/')) {
                return true;
            }
        }

        return false;
    }
}

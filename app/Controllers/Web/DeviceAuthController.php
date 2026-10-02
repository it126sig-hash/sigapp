<?php

namespace App\Controllers\Web;

use App\Services\DeviceSessionService;
use CodeIgniter\Cookie\Cookie;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Session\SessionInterface;
use Config\App;
use Myth\Auth\Controllers\AuthController;
use Throwable;

class DeviceAuthController extends AuthController
{
    private const DEVICE_COOKIE = 'sigapp_device';

    private DeviceSessionService $deviceSessions;

    public function __construct(
        ?DeviceSessionService $deviceSessions = null,
        ?object $auth = null,
        ?SessionInterface $session = null,
    ) {
        parent::__construct();
        $this->deviceSessions = $deviceSessions ?? new DeviceSessionService();
        $this->auth = $auth ?? $this->auth;
        $this->session = $session ?? $this->session;
    }

    public function attemptLogin()
    {
        $rules = [
            'login' => 'required',
            'password' => 'required',
        ];
        if ($this->config->validFields === ['email']) {
            $rules['login'] .= '|valid_email';
        }

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $login = (string) $this->request->getPost('login');
        $password = (string) $this->request->getPost('password');
        $remember = (bool) $this->request->getPost('remember');
        $type = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (! $this->auth->attempt([$type => $login, 'password' => $password], $remember)) {
            return redirect()->back()->withInput()->with('error', $this->auth->error() ?? lang('Auth.badAttempt'));
        }

        $response = service('response');
        $rememberSelector = $this->rememberSelector((string) ($response->getCookie('remember')?->getValue() ?? ''));
        $rawDeviceToken = bin2hex(random_bytes(32));
        $previousRawToken = trim((string) $this->request->getCookie(self::DEVICE_COOKIE));

        try {
            $device = $this->deviceSessions->registerOrRotate(
                (int) $this->auth->user()->id,
                $rawDeviceToken,
                $rememberSelector,
                (string) $this->request->getUserAgent(),
                $this->request->getIPAddress(),
                $previousRawToken !== '' ? $previousRawToken : null,
            );
        } catch (Throwable $e) {
            if ($rememberSelector !== null) {
                $this->deviceSessions->deleteRememberSelector($rememberSelector, (int) $this->auth->user()->id);
            }
            $this->clearSession();
            $this->expireAuthCookies();

            return redirect()->back()->withInput()->with('error', 'Sesi perangkat gagal didaftarkan. Silakan login kembali.')->withCookies();
        }

        $this->session->set('sigapp_device_id', (int) $device['id']);
        $this->setDeviceCookie($rawDeviceToken);

        if ($this->auth->user()->force_pass_reset === true) {
            $redirect = redirect()->to(route_to('reset-password') . '?token=' . $this->auth->user()->reset_hash)->withCookies();
            $this->setDeviceCookie($rawDeviceToken, $redirect);

            return $redirect;
        }

        $redirectURL = session('redirect_url') ?? site_url('/');
        unset($_SESSION['redirect_url']);

        $redirect = redirect()->to($redirectURL)->withCookies()->with('message', lang('Auth.loginSuccess'));
        $this->setDeviceCookie($rawDeviceToken, $redirect);

        return $redirect;
    }

    public function logout()
    {
        $userId = (int) ($this->session->get('logged_in') ?? 0);
        $rawDeviceToken = trim((string) $this->request->getCookie(self::DEVICE_COOKIE));
        $rememberSelector = $this->rememberSelector((string) $this->request->getCookie('remember'));

        try {
            $device = null;
            if ($userId > 0 && $rawDeviceToken !== '') {
                $device = $this->deviceSessions->resolveActive($userId, $rawDeviceToken);
            } elseif ($rawDeviceToken !== '' && $rememberSelector !== null) {
                $device = $this->deviceSessions->resolveActiveForRemember($rawDeviceToken, $rememberSelector);
                $userId = (int) ($device['user_id'] ?? 0);
            }

            if ($device && $userId > 0) {
                $this->deviceSessions->revokeOne($userId, (int) $device['id'], $userId, 'Logout pengguna');
            } elseif ($rememberSelector !== null) {
                $this->deviceSessions->deleteRememberSelector($rememberSelector, $userId > 0 ? $userId : null);
            }
        } finally {
            $this->clearSession();
            $this->expireAuthCookies();
        }

        return redirect()->to(site_url('/'))->withCookies();
    }

    private function setDeviceCookie(string $rawToken, ?ResponseInterface $response = null): void
    {
        $app = config(App::class);
        ($response ?? service('response'))->setCookie(new Cookie(self::DEVICE_COOKIE, $rawToken, [
            'expires' => time() + (int) $this->config->rememberLength,
            'domain' => $app->cookieDomain,
            'path' => $app->cookiePath,
            'prefix' => $app->cookiePrefix,
            'secure' => $app->cookieSecure || $this->request->isSecure(),
            'httponly' => true,
            'samesite' => Cookie::SAMESITE_LAX,
            'raw' => true,
        ]));
    }

    private function expireAuthCookies(): void
    {
        $app = config(App::class);
        $response = service('response');
        $response->deleteCookie('remember', $app->cookieDomain, $app->cookiePath, $app->cookiePrefix);
        $response->deleteCookie(self::DEVICE_COOKIE, $app->cookieDomain, $app->cookiePath, $app->cookiePrefix);
    }

    private function clearSession(): void
    {
        $keys = array_keys((array) $this->session->get());
        if ($keys !== []) {
            $this->session->remove($keys);
        }
        $this->session->regenerate(true);
    }

    private function rememberSelector(string $rememberCookie): ?string
    {
        if ($rememberCookie === '' || ! str_contains($rememberCookie, ':')) {
            return null;
        }

        [$selector, $validator] = explode(':', $rememberCookie, 2);

        return $selector !== '' && $validator !== '' ? $selector : null;
    }
}

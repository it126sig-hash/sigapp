<?php

namespace App\Controllers\Api\Auth;

use App\Controllers\Api\BaseApiController;
use App\Services\DeviceSessionService;
use App\Services\FileAccessService;
use App\Services\Proyek\ActiveProyekService;
use App\Services\Settings\MenuAccessService;
use CodeIgniter\Cookie\Cookie;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\ResponseInterface;
use Config\App;
use Myth\Auth\Entities\User;
use Throwable;

class AuthController extends BaseApiController
{
    private const SPA_TOKEN_COOKIE = 'sigapp_spa_token';

    private DeviceSessionService $deviceSessions;
    private ActiveProyekService $activeProyekService;
    private MenuAccessService $menuAccessService;
    private FileAccessService $fileAccessService;
    private BaseConnection $db;

    public function __construct(
        ?DeviceSessionService $deviceSessions = null,
        ?ActiveProyekService $activeProyekService = null,
        ?MenuAccessService $menuAccessService = null,
        ?FileAccessService $fileAccessService = null,
        ?BaseConnection $db = null,
    ) {
        $this->deviceSessions = $deviceSessions ?? new DeviceSessionService();
        $this->activeProyekService = $activeProyekService ?? new ActiveProyekService();
        $this->menuAccessService = $menuAccessService ?? new MenuAccessService();
        $this->fileAccessService = $fileAccessService ?? new FileAccessService();
        $this->db = $db ?? db_connect();

        if (! function_exists('logged_in')) {
            helper('auth');
        }
    }

    public function login(): ResponseInterface
    {
        $json = $this->request->getJSON(true);
        $login = trim((string) ($json['login'] ?? $this->request->getPost('login') ?? ''));
        $password = (string) ($json['password'] ?? $this->request->getPost('password') ?? '');
        $deviceName = trim((string) ($json['device_name'] ?? $this->request->getPost('device_name') ?? ''));

        if ($login === '' || $password === '') {
            return $this->error('Username dan password wajib diisi.', 400);
        }

        $type = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $auth = service('authentication');
        $user = $auth->validate([$type => $login, 'password' => $password], true);

        if (! $user instanceof User) {
            return $this->error('Username atau password salah.', 401);
        }

        if (method_exists($user, 'isBanned') && $user->isBanned()) {
            return $this->error(lang('Auth.userIsBanned') ?? 'Akun telah diblokir.', 403);
        }

        if (method_exists($user, 'isActivated') && ! $user->isActivated()) {
            return $this->error(lang('Auth.notActivated') ?? 'Akun belum aktif.', 403);
        }

        $userId = (int) $user->id;
        $rawDeviceToken = bin2hex(random_bytes(32));
        $httpUserAgent = trim((string) $this->request->getUserAgent());
        $userAgent = $httpUserAgent !== '' ? $httpUserAgent : $deviceName;
        $ipAddress = (string) $this->request->getIPAddress();
        $candidateToken = $this->extractRawToken();
        $previousRawToken = ($candidateToken !== null && $this->deviceSessions->resolveActive($userId, $candidateToken) !== null)
            ? $candidateToken
            : null;

        try {
            $device = $this->deviceSessions->registerOrRotate(
                $userId,
                $rawDeviceToken,
                null,
                $userAgent,
                $ipAddress,
                $previousRawToken,
            );
        } catch (Throwable $e) {
            return $this->error('Gagal mendaftarkan sesi perangkat: ' . $e->getMessage(), 500);
        }

        $this->setSpaCookie($rawDeviceToken);

        $clientPlatform = strtolower(trim((string) ($this->request->header('X-Client-Platform')?->getValue() ?? '')));
        $isWebSpa = ($clientPlatform === 'web-spa');

        $userData = $this->db->table('users')
            ->select('users.id, users.username, users.email, users.name, users.profile_photo, karyawan.nama_karyawan')
            ->join('karyawan', 'karyawan.id_user = users.id', 'left')
            ->where('users.id', $userId)
            ->get()
            ->getRow();

        $fullname = $userData?->nama_karyawan ?: ($userData?->name ?: $userData?->username ?? $user->username);
        $avatarUrl = trim((string) ($userData?->profile_photo ?? '')) !== ''
            ? $this->fileAccessService->thumbnailUrl('profile_photo', $userId)
            : null;

        return $this->success([
            'access_token' => $isWebSpa ? null : $rawDeviceToken,
            'token_type'   => $isWebSpa ? 'HttpOnly-Cookie' : 'Bearer',
            'expires_at'   => $device['expires_at'] ?? date('Y-m-d H:i:s', strtotime('+365 days')),
            'device'       => [
                'id'           => (int) $device['id'],
                'device_label' => (string) ($device['device_label'] ?? ''),
                'browser'      => (string) ($device['browser'] ?? ''),
                'platform'     => (string) ($device['platform'] ?? ''),
                'last_seen_at' => (string) ($device['last_seen_at'] ?? date('Y-m-d H:i:s')),
            ],
            'user'         => [
                'id'         => $userId,
                'username'   => (string) $user->username,
                'fullname'   => (string) $fullname,
                'email'      => (string) $user->email,
                'avatar_url' => $avatarUrl,
            ],
        ], 'Login berhasil.');
    }

    public function logout(): ResponseInterface
    {
        $rawToken = $this->extractRawToken();
        if ($rawToken !== null && $rawToken !== '') {
            $this->deviceSessions->revokeByToken($rawToken, 'user_logout');
        }

        session()->destroy();
        $this->expireSpaCookie();

        return $this->success(null, 'Logout berhasil.');
    }

    public function me(): ResponseInterface
    {
        $userId = (int) (session('logged_in') ?? 0);
        if ($userId <= 0) {
            $rawToken = $this->extractRawToken();
            if ($rawToken !== null && $rawToken !== '') {
                $device = $this->deviceSessions->resolveActiveByToken($rawToken);
                $userId = (int) ($device['user_id'] ?? 0);
            }
        }

        if ($userId <= 0) {
            return $this->error('Sesi tidak valid atau telah kadaluarsa.', 401);
        }

        $userData = $this->db->table('users')
            ->select('users.id, users.username, users.email, users.name, users.profile_photo, karyawan.nama_karyawan')
            ->join('karyawan', 'karyawan.id_user = users.id', 'left')
            ->where('users.id', $userId)
            ->get()
            ->getRow();

        if (! $userData) {
            return $this->error('Data pengguna tidak ditemukan.', 404);
        }

        $fullname = $userData->nama_karyawan ?: ($userData->name ?: $userData->username);
        $avatarUrl = trim((string) ($userData->profile_photo ?? '')) !== ''
            ? $this->fileAccessService->thumbnailUrl('profile_photo', $userId)
            : null;

        $roleRows = $this->db->table('auth_groups_users')
            ->select('auth_groups.id, auth_groups.name, auth_groups.description')
            ->join('auth_groups', 'auth_groups.id = auth_groups_users.group_id')
            ->where('auth_groups_users.user_id', $userId)
            ->get()
            ->getResultArray();

        $roles = array_map(static fn (array $r): array => [
            'id'          => (int) $r['id'],
            'name'        => (string) $r['name'],
            'description' => (string) ($r['description'] ?? ''),
        ], $roleRows);

        $headerProjectId = $this->request->header('X-Project-Id')?->getValue();
        if ($headerProjectId !== null && is_numeric($headerProjectId) && (int) $headerProjectId > 0) {
            $this->activeProyekService->setActive((int) $headerProjectId);
        }

        $activeProyekObj = $this->activeProyekService->getActive();
        if (! $activeProyekObj) {
            $activeProyekObj = $this->activeProyekService->resolveAndGet();
        }

        $activeProjectData = null;
        if ($activeProyekObj) {
            $activeProjectData = [
                'id_proyek'     => (int) $activeProyekObj->id_proyek,
                'nama_proyek'   => (string) $activeProyekObj->nama_proyek,
                'lokasi_proyek' => (string) ($activeProyekObj->kota ?? $activeProyekObj->alamat_proyek ?? ''),
                'logo_url'      => (string) ($activeProyekObj->logo_thumbnail_url ?? $activeProyekObj->logo_access_url ?? $this->fileAccessService->thumbnailUrl('proyek_logo', (int) $activeProyekObj->id_proyek)),
            ];
        }

        $accessibleList = $this->activeProyekService->getAccessibleList($userId);
        $accessibleProjects = array_map(fn (object $p): array => [
            'id_proyek'     => (int) $p->id_proyek,
            'nama_proyek'   => (string) $p->nama_proyek,
            'lokasi_proyek' => (string) ($p->kota ?? $p->alamat_proyek ?? ''),
            'logo_url'      => (string) ($p->logo_thumbnail_url ?? $p->logo_access_url ?? $this->fileAccessService->thumbnailUrl('proyek_logo', (int) $p->id_proyek)),
        ], $accessibleList);

        $menuTree = $this->menuAccessService->getMenuTreeForUser($userId);

        $vapidKey = (string) (getenv('VAPID_PUBLIC_KEY') ?: (env('VAPID_PUBLIC_KEY') ?? ''));

        return $this->success([
            'user' => [
                'id'         => $userId,
                'username'   => (string) $userData->username,
                'fullname'   => (string) $fullname,
                'email'      => (string) $userData->email,
                'avatar_url' => $avatarUrl,
            ],
            'roles'               => $roles,
            'active_project'      => $activeProjectData,
            'accessible_projects' => $accessibleProjects,
            'menus'               => $menuTree,
            'push_config'         => [
                'vapid_public_key' => $vapidKey,
            ],
        ], 'Bootstrap data loaded.');
    }

    private function extractRawToken(): ?string
    {
        $authHeader = $this->request->header('Authorization')?->getValue()
            ?? $this->request->getServer('HTTP_AUTHORIZATION')
            ?? $this->request->getServer('REDIRECT_HTTP_AUTHORIZATION')
            ?? '';

        if (preg_match('/^Bearer\s+(\S+)/i', trim((string) $authHeader), $matches)) {
            return $matches[1];
        }

        $cookieToken = trim((string) $this->request->getCookie(self::SPA_TOKEN_COOKIE));
        if ($cookieToken !== '') {
            return $cookieToken;
        }

        return null;
    }

    private function setSpaCookie(string $rawDeviceToken): void
    {
        $app = config(App::class);
        $this->response->setCookie(new Cookie(
            self::SPA_TOKEN_COOKIE,
            $rawDeviceToken,
            [
                'expires'  => time() + (365 * 86400),
                'path'     => $app->cookiePath ?: '/',
                'domain'   => $app->cookieDomain ?? '',
                'prefix'   => $app->cookiePrefix ?? '',
                'secure'   => (bool) ($app->cookieSecure || $this->request->isSecure()),
                'httponly' => true,
                'samesite' => Cookie::SAMESITE_LAX,
                'raw'      => true,
            ],
        ));
    }

    private function expireSpaCookie(): void
    {
        $app = config(App::class);
        $this->response->setCookie(new Cookie(
            self::SPA_TOKEN_COOKIE,
            '',
            [
                'expires'  => time() - 3600,
                'path'     => $app->cookiePath ?: '/',
                'domain'   => $app->cookieDomain ?? '',
                'prefix'   => $app->cookiePrefix ?? '',
                'secure'   => (bool) ($app->cookieSecure || $this->request->isSecure()),
                'httponly' => true,
                'samesite' => Cookie::SAMESITE_LAX,
                'raw'      => true,
            ],
        ));
    }
}

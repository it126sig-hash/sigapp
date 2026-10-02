<?php

require_once APPPATH . 'Database/Migrations/2026-09-30-000001_CreateAuthDeviceSessions.php';

use App\Controllers\Web\DeviceAuthController;
use App\Database\Migrations\CreateAuthDeviceSessions;
use App\Repositories\DeviceSessionRepository;
use App\Services\DeviceSessionService;
use App\Services\UserAgentDeviceParser;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\Session\SessionInterface;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;
use Config\Services;
use Myth\Auth\Authentication\AuthenticatorInterface;
use Myth\Auth\Entities\User;

final class DeviceAuthControllerTest extends CIUnitTestCase
{
    private BaseConnection $testDb;
    private DeviceSessionService $deviceSessions;
    private SessionInterface $testSession;
    private ResponseInterface $testResponse;

    protected function setUp(): void
    {
        parent::setUp();

        $_SESSION = [];
        $_COOKIE = [];
        Services::resetSingle('response');
        Services::resetSingle('redirectresponse');
        $this->testSession = Services::session();
        $this->testResponse = Services::response(config('App'), false);
        Services::injectMock('response', $this->testResponse);

        $this->testDb = Database::connect([
            'DBDriver' => 'SQLite3',
            'database' => ':memory:',
            'DBPrefix' => '',
            'DBDebug' => true,
            'foreignKeys' => true,
        ], false);
        $this->createAuthTables();
        (new CreateAuthDeviceSessions(Database::forge($this->testDb)))->up();
        $this->testDb->table('users')->insert(['id' => 10]);
        $this->testDb->table('users')->insert(['id' => 20]);

        $now = new DateTimeImmutable('2026-09-30 10:00:00');
        $this->deviceSessions = new DeviceSessionService(
            new DeviceSessionRepository($this->testDb),
            new UserAgentDeviceParser(),
            static fn (): DateTimeImmutable => $now,
        );
    }

    protected function tearDown(): void
    {
        Services::resetSingle('response');
        Services::resetSingle('redirectresponse');
        $_SESSION = [];
        $_COOKIE = [];
        $this->testDb->close();
        parent::tearDown();
    }

    public function testSuccessfulLoginRegistersHashedDeviceAndSecureCookie(): void
    {
        $auth = new TestControllerAuthenticator(true, 10, $this->testSession, $this->testResponse, 'login-selector');
        $controller = $this->controller($auth, $this->request('login', [
            'login' => 'user@example.com',
            'password' => 'secret',
            'remember' => '1',
        ], [], '203.0.113.8'));

        $result = $controller->attemptLogin();

        $this->assertSame(1, $auth->attemptCalls);
        $this->assertNull($this->testSession->getFlashdata('error'));
        $stored = $this->testDb->table('auth_device_sessions')->get()->getRowArray();
        $this->assertNotNull($stored);
        $deviceCookie = $result->getCookie('sigapp_device');
        $this->assertNotNull($deviceCookie);
        $this->assertTrue($deviceCookie->isSecure());
        $this->assertTrue($deviceCookie->isHTTPOnly());
        $this->assertSame('Lax', $deviceCookie->getSameSite());
        $this->assertSame(config('App')->cookieDomain, $deviceCookie->getDomain());
        $this->assertSame(config('App')->cookiePath, $deviceCookie->getPath());

        $this->assertSame(hash('sha256', $deviceCookie->getValue()), $stored['token_hash']);
        $this->assertSame('login-selector', $stored['remember_selector']);
        $this->assertSame('203.0.113.8', $stored['ip_address']);
        $this->assertStringNotContainsString($deviceCookie->getValue(), json_encode($stored, JSON_THROW_ON_ERROR));
        $this->assertSame((int) $stored['id'], $this->testSession->get('sigapp_device_id'));
    }

    public function testHttpLoginUsesNonSecureDeviceCookieWhenAppDoesNotRequireSecureCookies(): void
    {
        $auth = new TestControllerAuthenticator(true, 10, $this->testSession, $this->testResponse, null);
        $controller = $this->controller($auth, $this->request('login', [
            'login' => 'user@example.com',
            'password' => 'secret',
        ], [], '127.0.0.1', false));

        $result = $controller->attemptLogin();

        $this->assertNotNull($result->getCookie('sigapp_device'));
        $this->assertFalse($result->getCookie('sigapp_device')->isSecure());
    }

    public function testFailedLoginDoesNotCreateDeviceOrCookie(): void
    {
        $auth = new TestControllerAuthenticator(false, 10, $this->testSession, $this->testResponse, null);
        $controller = $this->controller($auth, $this->request('login', [
            'login' => 'user@example.com',
            'password' => 'wrong',
            'remember' => '1',
        ]));

        $result = $controller->attemptLogin();

        $this->assertSame(0, $this->testDb->table('auth_device_sessions')->countAllResults());
        $this->assertNull($result->getCookie('sigapp_device'));
    }

    public function testLogoutRevokesOnlyCurrentDeviceAndNeverCallsMythLogout(): void
    {
        $current = $this->register(10, 'current-device', 'current-selector');
        $other = $this->register(10, 'other-device', 'other-selector');
        $this->insertRememberToken(10, 'current-selector');
        $this->insertRememberToken(10, 'other-selector');
        $this->testSession->set(['logged_in' => 10, 'sigapp_device_id' => $current['id'], 'unrelated' => 'value']);
        $auth = new TestControllerAuthenticator(true, 10, $this->testSession, $this->testResponse, null);
        $controller = $this->controller($auth, $this->request('logout', [], [
            'sigapp_device' => 'current-device',
            'remember' => 'current-selector:validator',
        ]));

        $result = $controller->logout();

        $this->assertNull($this->deviceSessions->resolveActive(10, 'current-device'));
        $this->assertSame($other['id'], $this->deviceSessions->resolveActive(10, 'other-device')['id']);
        $this->assertSame(0, $this->testDb->table('auth_tokens')->where('selector', 'current-selector')->countAllResults());
        $this->assertSame(1, $this->testDb->table('auth_tokens')->where('selector', 'other-selector')->countAllResults());
        $this->assertSame(0, $auth->logoutCalls);
        $this->assertNull($this->testSession->get('logged_in'));
        $this->assertNull($this->testSession->get('unrelated'));
        $this->assertTrue($result->getCookie('remember')->isExpired());
        $this->assertTrue($result->getCookie('sigapp_device')->isExpired());
    }

    public function testLegacyLogoutDeletesOnlyIncomingRememberSelector(): void
    {
        $this->insertRememberToken(10, 'legacy-selector');
        $this->insertRememberToken(10, 'other-selector');
        $this->testSession->set('logged_in', 10);
        $auth = new TestControllerAuthenticator(true, 10, $this->testSession, $this->testResponse, null);
        $controller = $this->controller($auth, $this->request('logout', [], [
            'remember' => 'legacy-selector:validator',
        ]));

        $controller->logout();

        $this->assertSame(0, $this->testDb->table('auth_tokens')->where('selector', 'legacy-selector')->countAllResults());
        $this->assertSame(1, $this->testDb->table('auth_tokens')->where('selector', 'other-selector')->countAllResults());
        $this->assertSame(0, $auth->logoutCalls);
    }

    private function controller(TestControllerAuthenticator $auth, IncomingRequest $request): DeviceAuthController
    {
        $controller = new DeviceAuthController($this->deviceSessions, $auth, $this->testSession);
        $controller->initController($request, $this->testResponse, service('logger'));

        return $controller;
    }

    private function request(
        string $path,
        array $post = [],
        array $cookies = [],
        string $ipAddress = '127.0.0.1',
        bool $secure = true,
    ): IncomingRequest {
        $_COOKIE = $cookies;
        $_SERVER['REMOTE_ADDR'] = $ipAddress;
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 10.0) Chrome/130.0 Safari/537.36';
        $_SERVER['HTTPS'] = $secure ? 'on' : 'off';
        $scheme = $secure ? 'https' : 'http';
        $request = new IncomingRequest(config('App'), new URI($scheme . '://example.com/' . $path), null, new UserAgent());
        $request->setMethod($post !== [] ? 'POST' : 'GET');
        $request->setGlobal('post', $post);
        $request->setGlobal('request', $post);

        return $request;
    }

    private function register(int $userId, string $token, string $selector): array
    {
        return $this->deviceSessions->registerOrRotate($userId, $token, $selector, 'Mozilla/5.0 Firefox/120.0', '192.0.2.1');
    }

    private function insertRememberToken(int $userId, string $selector): void
    {
        $this->testDb->table('auth_tokens')->insert([
            'selector' => $selector,
            'hashedValidator' => hash('sha256', 'validator'),
            'user_id' => $userId,
            'expires' => '2027-09-30 10:00:00',
        ]);
    }

    private function createAuthTables(): void
    {
        $this->testDb->query('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT)');
        $this->testDb->query('CREATE TABLE auth_groups (id INTEGER PRIMARY KEY, name TEXT NOT NULL)');
        $this->testDb->query('CREATE TABLE auth_permissions (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL, description TEXT NOT NULL)');
        $this->testDb->query('CREATE TABLE auth_groups_permissions (group_id INTEGER NOT NULL, permission_id INTEGER NOT NULL, PRIMARY KEY (group_id, permission_id))');
        $this->testDb->query('CREATE TABLE auth_users_permissions (user_id INTEGER NOT NULL, permission_id INTEGER NOT NULL, PRIMARY KEY (user_id, permission_id))');
        $this->testDb->query('CREATE TABLE auth_tokens (id INTEGER PRIMARY KEY AUTOINCREMENT, selector TEXT NOT NULL, hashedValidator TEXT NOT NULL, user_id INTEGER NOT NULL, expires TEXT NOT NULL)');
        $this->testDb->table('auth_groups')->insert(['id' => 1, 'name' => 'Admin']);
    }
}

final class TestControllerAuthenticator implements AuthenticatorInterface
{
    public int $attemptCalls = 0;
    public int $logoutCalls = 0;
    private User $currentUser;

    public function __construct(
        private readonly bool $attemptResult,
        int $userId,
        private readonly SessionInterface $session,
        private readonly ResponseInterface $response,
        private readonly ?string $rememberSelector,
    ) {
        $this->currentUser = new User(['id' => $userId, 'force_pass_reset' => false]);
    }

    public function attempt(array $credentials, ?bool $remember = null): bool
    {
        $this->attemptCalls++;
        if (! $this->attemptResult) {
            return false;
        }

        $this->session->set('logged_in', $this->currentUser->id);
        if ($remember && $this->rememberSelector !== null) {
            $this->response->setCookie('remember', $this->rememberSelector . ':validator', 365 * DAY, '', '/', '', true, true, 'Lax');
        }

        return true;
    }

    public function check(): bool
    {
        return $this->session->get('logged_in') !== null;
    }

    public function validate(array $credentials, bool $returnUser = false)
    {
        return false;
    }

    public function user(): User
    {
        return $this->currentUser;
    }

    public function error(): string
    {
        return 'Bad attempt';
    }

    public function logout(): void
    {
        $this->logoutCalls++;
    }
}

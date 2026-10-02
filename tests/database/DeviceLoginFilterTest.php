<?php

require_once APPPATH . 'Database/Migrations/2026-09-30-000001_CreateAuthDeviceSessions.php';

use App\Database\Migrations\CreateAuthDeviceSessions;
use App\Filters\DeviceLoginFilter;
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
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;

final class DeviceLoginFilterTest extends CIUnitTestCase
{
    private BaseConnection $testDb;
    private DeviceSessionService $deviceSessions;
    private DateTimeImmutable $now;
    private SessionInterface $testSession;
    private ResponseInterface $testResponse;

    protected function setUp(): void
    {
        parent::setUp();

        $_SESSION = [];
        $_COOKIE = [];
        Services::resetSingle('response');
        $this->testSession = Services::session();
        $this->testResponse = Services::response(config('App'), false);
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
        $this->now = new DateTimeImmutable('2026-09-30 10:00:00');
        $this->deviceSessions = new DeviceSessionService(
            new DeviceSessionRepository($this->testDb),
            new UserAgentDeviceParser(),
            fn (): DateTimeImmutable => $this->now,
        );
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        $_COOKIE = [];
        $this->testDb->close();
        parent::tearDown();
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testFilterLoadsAuthHelperBeforeApiControllerRuns(): void
    {
        $this->assertFalse(function_exists('user_id'));

        $this->filter(new TestDeviceAuthenticator(false, 10));

        $this->assertTrue(function_exists('user_id'));
    }

    public function testLegacySessionFailsClosedAndDeletesOnlyIncomingRememberSelector(): void
    {
        $this->testSession->set('logged_in', 10);
        $this->insertRememberToken(10, 'legacy-selector');
        $this->insertRememberToken(10, 'other-selector');
        $request = $this->request(['remember' => 'legacy-selector:validator']);
        $auth = new TestDeviceAuthenticator(true, 10);

        $result = $this->filter($auth)->before($request);

        $this->assertNotNull($result);
        $this->assertStringEndsWith('/login', $result->getHeaderLine('Location'));
        $this->assertNull($this->testSession->get('logged_in'));
        $this->assertSame(0, $auth->checkCalls);
        $this->assertSame(0, $this->testDb->table('auth_tokens')->where('selector', 'legacy-selector')->countAllResults());
        $this->assertSame(1, $this->testDb->table('auth_tokens')->where('selector', 'other-selector')->countAllResults());
        $this->assertTrue($this->testResponse->getCookie('remember')->isExpired());
        $this->assertTrue($this->testResponse->getCookie('sigapp_device')->isExpired());
    }

    public function testValidSessionDeviceIsAcceptedTouchedAndDuplicateFilterIsIdempotent(): void
    {
        $device = $this->deviceSessions->registerOrRotate(10, 'valid-device', 'valid-selector', 'Mozilla/5.0 Firefox/120.0', '192.0.2.1');
        $this->now = $this->now->modify('+6 minutes');
        $this->testSession->set('logged_in', 10);
        $request = $this->request(['sigapp_device' => 'valid-device']);
        $auth = new TestDeviceAuthenticator(true, 10);
        $filter = $this->filter($auth);

        $this->assertNull($filter->before($request));
        $this->assertNull($filter->before($request));

        $this->assertSame(1, $auth->checkCalls);
        $this->assertSame($device['id'], $this->testSession->get('sigapp_device_id'));
        $stored = $this->testDb->table('auth_device_sessions')->where('id', $device['id'])->get()->getRowArray();
        $this->assertSame('2026-09-30 10:06:00', $stored['last_seen_at']);
    }

    public function testRevokedDeviceRedirectsOnNextRequestWithoutCallingRememberAuthentication(): void
    {
        $device = $this->deviceSessions->registerOrRotate(10, 'revoked-device', 'revoked-selector', 'Mozilla/5.0 Firefox/120.0', '192.0.2.1');
        $this->insertRememberToken(10, 'revoked-selector');
        $this->deviceSessions->revokeOne(10, $device['id'], 10, 'revoked remotely');
        $this->testSession->set('logged_in', 10);
        $request = $this->request([
            'sigapp_device' => 'revoked-device',
            'remember' => 'revoked-selector:validator',
        ]);
        $auth = new TestDeviceAuthenticator(true, 10);

        $result = $this->filter($auth)->before($request);

        $this->assertStringEndsWith('/login', $result->getHeaderLine('Location'));
        $this->assertSame(0, $auth->checkCalls);
        $this->assertNull($this->testSession->get('logged_in'));
    }

    public function testRememberLoginRequiresMatchingActiveDeviceAndAuthenticatedUser(): void
    {
        $device = $this->deviceSessions->registerOrRotate(10, 'remember-device', 'remember-selector', 'Mozilla/5.0 Firefox/120.0', '192.0.2.1');
        $request = $this->request([
            'sigapp_device' => 'remember-device',
            'remember' => 'remember-selector:validator',
        ]);
        $auth = new TestDeviceAuthenticator(true, 10);

        $this->assertNull($this->filter($auth)->before($request));
        $this->assertSame(1, $auth->checkCalls);
        $this->assertSame($device['id'], $this->testSession->get('sigapp_device_id'));

        $mismatchRequest = $this->request([
            'sigapp_device' => 'remember-device',
            'remember' => 'wrong-selector:validator',
        ]);
        $mismatchAuth = new TestDeviceAuthenticator(true, 10);
        $result = $this->filter($mismatchAuth)->before($mismatchRequest);
        $this->assertStringEndsWith('/login', $result->getHeaderLine('Location'));
        $this->assertSame(0, $mismatchAuth->checkCalls);
    }

    private function filter(TestDeviceAuthenticator $auth): DeviceLoginFilter
    {
        return new DeviceLoginFilter($this->deviceSessions, $auth, $this->testSession, $this->testResponse);
    }

    private function request(array $cookies): IncomingRequest
    {
        $_COOKIE = $cookies;

        return new IncomingRequest(config('App'), new URI('https://example.com/protected'), null, new UserAgent());
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

final class TestDeviceAuthenticator implements AuthenticatorInterface
{
    public int $checkCalls = 0;
    private User $currentUser;

    public function __construct(private readonly bool $checkResult, int $userId)
    {
        $this->currentUser = new User(['id' => $userId]);
    }

    public function attempt(array $credentials, ?bool $remember = null): bool
    {
        return false;
    }

    public function check(): bool
    {
        $this->checkCalls++;

        return $this->checkResult;
    }

    public function validate(array $credentials, bool $returnUser = false)
    {
        return false;
    }

    public function user(): User
    {
        return $this->currentUser;
    }
}

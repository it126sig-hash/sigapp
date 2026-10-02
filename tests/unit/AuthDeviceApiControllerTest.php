<?php

use App\Controllers\Api\AdminAuthDeviceController;
use App\Controllers\Api\AuthDeviceController;
use App\Services\DeviceManagementException;
use App\Services\DeviceManagementService;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\Session\SessionInterface;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

final class AuthDeviceApiControllerTest extends CIUnitTestCase
{
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
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        $_COOKIE = [];
        parent::tearDown();
    }

    public function testSelfEndpointsReturnConsistentUnauthorizedAndSuccessResponses(): void
    {
        $service = new TestDeviceManagementService();
        $controller = $this->selfController($service, $this->request('api/auth/devices'));
        $unauthorized = $controller->index();
        $this->assertSame(401, $unauthorized->getStatusCode());
        $unauthorizedBody = $this->json($unauthorized);
        $this->assertSame('error', $unauthorizedBody['status']);
        $this->assertSame('Sesi login tidak valid.', $unauthorizedBody['message']);
        $this->assertArrayHasKey('token', $unauthorizedBody);

        $this->testSession->set('logged_in', 10);
        $controller = $this->selfController($service, $this->request('api/auth/devices', [], ['sigapp_device' => 'current-token']));
        $success = $controller->index();
        $this->assertSame(200, $success->getStatusCode());
        $this->assertSame('success', $this->json($success)['status']);
        $this->assertSame([['id' => 7, 'is_current' => true]], $this->json($success)['data']);
    }

    public function testSelfRevokeMapsPasswordFailureAndCurrentDeviceReloginSignal(): void
    {
        $this->testSession->set('logged_in', 10);
        $service = new TestDeviceManagementService();
        $service->exception = new DeviceManagementException('Password tidak valid.', 401);
        $controller = $this->selfController($service, $this->request('api/auth/devices/7/revoke', ['password' => 'wrong'], ['sigapp_device' => 'current-token']));
        $failed = $controller->revoke(7);
        $this->assertSame(401, $failed->getStatusCode());
        $this->assertSame('error', $this->json($failed)['status']);

        $service->exception = null;
        $service->currentRevoked = true;
        $controller = $this->selfController($service, $this->request('api/auth/devices/7/revoke', ['password' => 'correct'], ['sigapp_device' => 'current-token']));
        $success = $controller->revoke(7);
        $this->assertTrue($this->json($success)['data']['reauthenticate']);
        $this->assertNull($this->testSession->get('logged_in'));
    }

    public function testAdminEndpointsMapPermissionReasonAndSuccess(): void
    {
        $this->testSession->set('logged_in', 30);
        $service = new TestDeviceManagementService();
        $service->exception = new DeviceManagementException('Anda tidak memiliki izin mengelola perangkat.', 403);
        $controller = $this->adminController($service, $this->request('api/admin/users/10/devices'));
        $this->assertSame(403, $controller->index(10)->getStatusCode());

        $service->exception = new DeviceManagementException('Alasan pencabutan wajib diisi.', 422);
        $controller = $this->adminController($service, $this->request('api/admin/users/10/devices/7/revoke', ['reason' => '']));
        $this->assertSame(422, $controller->revoke(10, 7)->getStatusCode());

        $service->exception = null;
        $controller = $this->adminController($service, $this->request('api/admin/users/10/devices/revoke-all', ['reason' => 'incident']));
        $response = $controller->revokeAll(10);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(2, $this->json($response)['data']['revoked_count']);
    }

    private function selfController(TestDeviceManagementService $service, IncomingRequest $request): AuthDeviceController
    {
        $controller = new AuthDeviceController($service, $this->testSession);
        $controller->initController($request, $this->testResponse, service('logger'));

        return $controller;
    }

    private function adminController(TestDeviceManagementService $service, IncomingRequest $request): AdminAuthDeviceController
    {
        $controller = new AdminAuthDeviceController($service, $this->testSession);
        $controller->initController($request, $this->testResponse, service('logger'));

        return $controller;
    }

    private function request(string $path, array $post = [], array $cookies = []): IncomingRequest
    {
        $_COOKIE = $cookies;
        $request = new IncomingRequest(config('App'), new URI('https://example.com/' . $path), null, new UserAgent());
        $request->setMethod($post !== [] ? 'POST' : 'GET');
        $request->setGlobal('post', $post);
        $request->setGlobal('request', $post);

        return $request;
    }

    private function json(ResponseInterface $response): array
    {
        return json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
    }
}

final class TestDeviceManagementService extends DeviceManagementService
{
    public ?DeviceManagementException $exception = null;
    public bool $currentRevoked = false;

    public function __construct()
    {
    }

    public function listOwn(int $userId, string $currentRawToken): array
    {
        $this->throwIfNeeded();

        return [['id' => 7, 'is_current' => true]];
    }

    public function revokeOwn(int $userId, int $deviceId, string $password, string $currentRawToken): array
    {
        $this->throwIfNeeded();

        return ['current_device_revoked' => $this->currentRevoked];
    }

    public function revokeOthers(int $userId, string $currentRawToken, string $password): int
    {
        $this->throwIfNeeded();

        return 2;
    }

    public function listForAdmin(int $adminUserId, int $targetUserId): array
    {
        $this->throwIfNeeded();

        return [['id' => 7]];
    }

    public function revokeForAdmin(int $adminUserId, int $targetUserId, int $deviceId, string $reason): bool
    {
        $this->throwIfNeeded();

        return true;
    }

    public function revokeAllForAdmin(int $adminUserId, int $targetUserId, string $reason): int
    {
        $this->throwIfNeeded();

        return 2;
    }

    private function throwIfNeeded(): void
    {
        if ($this->exception) {
            throw $this->exception;
        }
    }
}

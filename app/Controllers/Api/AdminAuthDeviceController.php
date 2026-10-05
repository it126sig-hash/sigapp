<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\DeviceManagementException;
use App\Services\DeviceManagementService;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Session\SessionInterface;

class AdminAuthDeviceController extends BaseController
{
    private DeviceManagementService $devices;
    private SessionInterface $authSession;

    public function __construct(?DeviceManagementService $devices = null, ?SessionInterface $session = null)
    {
        $this->devices = $devices ?? new DeviceManagementService();
        $this->authSession = $session ?? service('session');
    }

    public function index(int $userId): ResponseInterface
    {
        return $this->run(fn () => $this->devices->listForAdmin(
            (int) ($this->authSession->get('logged_in') ?? 0),
            $userId,
        ));
    }

    public function revoke(int $userId, int $deviceId): ResponseInterface
    {
        return $this->run(function () use ($userId, $deviceId): array {
            $this->devices->revokeForAdmin(
                (int) ($this->authSession->get('logged_in') ?? 0),
                $userId,
                $deviceId,
                (string) $this->request->getPost('reason'),
            );

            return ['revoked' => true];
        });
    }

    public function revokeAll(int $userId): ResponseInterface
    {
        return $this->run(fn () => [
            'revoked_count' => $this->devices->revokeAllForAdmin(
                (int) ($this->authSession->get('logged_in') ?? 0),
                $userId,
                (string) $this->request->getPost('reason'),
            ),
        ]);
    }

    private function run(callable $callback): ResponseInterface
    {
        try {
            return $this->jsonResponse([
                'status' => 'success',
                'message' => 'Permintaan berhasil.',
                'data' => $callback(),
            ]);
        } catch (DeviceManagementException $e) {
            return $this->jsonResponse([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], $e->statusCode());
        }
    }

    private function jsonResponse(array $body, int $status = 200): ResponseInterface
    {
        $body['token'] = csrf_hash();
        return $this->response->setJSON($body)->setStatusCode($status);
    }
}

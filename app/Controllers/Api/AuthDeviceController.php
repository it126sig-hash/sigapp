<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\DeviceManagementException;
use App\Services\DeviceManagementService;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Session\SessionInterface;

class AuthDeviceController extends BaseController
{
    private DeviceManagementService $devices;
    private SessionInterface $authSession;

    public function __construct(?DeviceManagementService $devices = null, ?SessionInterface $session = null)
    {
        $this->devices = $devices ?? new DeviceManagementService();
        $this->authSession = $session ?? service('session');
    }

    public function index(): ResponseInterface
    {
        if ((int) ($this->authSession->get('logged_in') ?? 0) <= 0) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Sesi login tidak valid.'], 401);
        }

        return $this->run(fn () => $this->devices->listOwn(
            (int) ($this->authSession->get('logged_in') ?? 0),
            $this->resolveCurrentToken(),
        ));
    }

    public function revoke(int $deviceId): ResponseInterface
    {
        $result = $this->run(fn () => $this->devices->revokeOwn(
            (int) ($this->authSession->get('logged_in') ?? 0),
            $deviceId,
            $this->resolvePassword(),
            $this->resolveCurrentToken(),
        ));

        if ($result->getStatusCode() < 400) {
            $body = json_decode((string) $result->getBody(), true);
            if (! empty($body['data']['current_device_revoked'])) {
                $this->authSession->remove(['logged_in', 'sigapp_device_id']);
                $result->deleteCookie('remember')->deleteCookie('sigapp_device')->deleteCookie('sigapp_spa_token');
                $body['data']['reauthenticate'] = true;
                $result->setJSON($body);
            }
        }

        return $result;
    }

    public function revokeOthers(): ResponseInterface
    {
        return $this->run(fn () => [
            'revoked_count' => $this->devices->revokeOthers(
                (int) ($this->authSession->get('logged_in') ?? 0),
                $this->resolveCurrentToken(),
                $this->resolvePassword(),
            ),
        ]);
    }

    private function resolveCurrentToken(): string
    {
        $authHeader = $this->request->header('Authorization')?->getValue()
            ?? $this->request->getServer('HTTP_AUTHORIZATION')
            ?? $this->request->getServer('REDIRECT_HTTP_AUTHORIZATION')
            ?? '';

        if (preg_match('/^Bearer\s+(\S+)/i', trim((string) $authHeader), $matches)) {
            return $matches[1];
        }

        $spaToken = trim((string) $this->request->getCookie('sigapp_spa_token'));
        if ($spaToken !== '') {
            return $spaToken;
        }

        return trim((string) $this->request->getCookie('sigapp_device'));
    }

    private function resolvePassword(): string
    {
        $postPassword = $this->request->getPost('password');
        if ($postPassword !== null) {
            return (string) $postPassword;
        }

        $contentType = (string) ($this->request->header('Content-Type')?->getValue() ?? '');
        if (stripos($contentType, 'application/json') !== false) {
            $json = $this->request->getJSON(true);

            return (string) ($json['password'] ?? '');
        }

        return '';
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

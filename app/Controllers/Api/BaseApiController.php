<?php

namespace App\Controllers\Api;

use App\Services\Settings\MenuAccessService;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

class BaseApiController extends ResourceController
{
    protected $format = 'json';

    protected function success(mixed $data = null, string $message = 'Success', int $code = 200)
    {
        $body = ['success' => true, 'messages' => $message]; // menyesuaikan kebiasaan success dan messages dari ajax frontend saat ini
        if ($data !== null) $body['data'] = $data;
        $body['token'] = csrf_hash();
        return $this->respond($body, $code);
    }

    protected function successCreated(mixed $data, string $message = 'Created')
    {
        return $this->respond([
            'success' => true,
            'messages' => $message,
            'data'    => $data,
            'token'   => csrf_hash()
        ], 201);
    }

    protected function error(string $message, int $code = 400, mixed $errors = null)
    {
        $body = ['success' => false, 'messages' => $message];
        if ($errors !== null) $body['errors'] = $errors;
        $body['token'] = csrf_hash();
        return $this->respond($body, $code);
    }

    /**
     * Gabungkan query GET, POST form, dan JSON body secara aman tanpa melempar error
     * jika body kosong atau bukan JSON.
     */
    protected function readRequestData(): array
    {
        $query = $this->request->getGet();
        $query = is_array($query) ? $query : [];

        $post = $this->request->getPost();
        $post = is_array($post) ? $post : [];

        $json = [];
        $contentType = strtolower((string) $this->request->getHeaderLine('Content-Type'));
        if (str_contains($contentType, 'application/json')) {
            try {
                $decoded = $this->request->getJSON(true);
                if (is_array($decoded)) {
                    $json = $decoded;
                }
            } catch (\Throwable) {
                $decoded = json_decode((string) $this->request->getBody(), true);
                if (is_array($decoded)) {
                    $json = $decoded;
                }
            }
        }

        return array_merge($query, $post, $json);
    }

    protected function isRawFormat(): bool
    {
        return $this->request->getGet('format') === 'raw';
    }

    /**
     * Verifikasi otorisasi menu berdasarkan URL pada tabel `menus`.
     * Admin (group_id = 1) selalu diizinkan.
     */
    protected function ensureMenuAccess(string|array $menuUrls): ?ResponseInterface
    {
        if (! function_exists('user_id')) {
            helper('auth');
        }

        $userId = (int) (user_id() ?? 0);
        if ($userId <= 0) {
            return $this->error('Anda tidak memiliki akses ke fitur ini', 403);
        }

        $menuAccess = new MenuAccessService();
        if (in_array(1, $menuAccess->getUserGroupIds($userId), true)) {
            return null;
        }

        foreach ((array) $menuUrls as $url) {
            if ($menuAccess->canAccessUrl($userId, (string) $url)) {
                return null;
            }
        }

        return $this->error('Anda tidak memiliki akses ke fitur ini', 403);
    }
}

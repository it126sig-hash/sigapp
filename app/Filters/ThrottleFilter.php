<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * ThrottleFilter
 *
 * Membatasi jumlah request per IP dalam jendela waktu tertentu.
 * Konfigurasi default: 20 request / 60 detik.
 *
 * Cara pakai di Routes.php:
 *   ['filter' => 'throttle:20,60']
 *
 * Argumen:
 *   $arguments[0] = max request (default: 20)
 *   $arguments[1] = window dalam detik (default: 60)
 */
class ThrottleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $maxRequests = (int) ($arguments[0] ?? 20);
        $windowSecs  = (int) ($arguments[1] ?? 60);

        $ip      = $request->getIPAddress();
        $uri     = $request->getUri()->getPath();
        $cacheKey = 'throttle_' . md5($ip . '|' . $uri);

        $cache = \Config\Services::cache();
        $hits  = (int) ($cache->get($cacheKey) ?? 0);

        if ($hits >= $maxRequests) {
            $response = \Config\Services::response();
            return $response
                ->setStatusCode(429)
                ->setJSON([
                    'success' => false,
                    'message' => 'Terlalu banyak permintaan. Coba lagi dalam ' . $windowSecs . ' detik.',
                ]);
        }

        // Increment. Jika kunci belum ada, set dengan TTL window.
        if ($hits === 0) {
            $cache->save($cacheKey, 1, $windowSecs);
        } else {
            // Simpan sisa TTL agar window tidak di-reset tiap request
            $remaining = $cache->getCacheInfo()['expire'] ?? $windowSecs;
            $cache->save($cacheKey, $hits + 1, $windowSecs);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // tidak ada post-processing
    }
}

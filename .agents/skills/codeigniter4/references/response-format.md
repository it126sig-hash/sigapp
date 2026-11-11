# Standar Format Full JSON Response API (`BaseApiController` & `RAW_JSON_READY`)

> **Aturan Mutlak Full JSON (`/api/*`):**
> 1. Seluruh endpoint di `App\Controllers\Api\<Modul>\*Controller` **WAJIB** mewarisi `App\Controllers\Api\BaseApiController` dan mengembalikan **Full JSON Murni (`RAW_JSON_READY`)** baik saat sukses (`200/201`) maupun error (`400/401/403/404/422/500`).
> 2. **DILARANG KERAS** mengembalikan halaman HTML, melakukan HTTP redirect `302` ke `/login`, atau melempar HTML exception pada rute `/api/*`.
> 3. **DILARANG KERAS** menyisipkan potongan string HTML (`<button onclick="...">`, `<span class="badge">`) di dalam response JSON API baru. Jika memodifikasi endpoint legacy yang masih dipakai oleh jQuery DataTables di view CI4, dukung query `?format=raw` agar mengembalikan objek data JSON murni untuk klien modern (`sigapp-vue` / Capacitor) sekaligus menjaga kompatibilitas tampilan lama (Zero-Downtime).

## BaseController untuk API (`app/Controllers/Api/BaseApiController.php`)

```php
<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class BaseApiController extends ResourceController
{
    protected $format = 'json';

    protected function success(mixed $data = null, string $message = 'Success', int $code = 200)
    {
        $body = ['success' => true, 'messages' => $message];
        if ($data !== null) $body['data'] = $data;
        $body['token'] = csrf_hash();
        return $this->respond($body, $code);
    }

    protected function successCreated(mixed $data, string $message = 'Created')
    {
        return $this->respond([
            'success'  => true,
            'messages' => $message,
            'data'     => $data,
            'token'    => csrf_hash(),
        ], 201);
    }

    protected function error(string $message, int $code = 400, mixed $errors = null)
    {
        $body = ['success' => false, 'messages' => $message];
        if ($errors !== null) $body['errors'] = $errors;
        $body['token'] = csrf_hash();
        return $this->respond($body, $code);
    }
}
```

## Contoh Response Full JSON

```json
// GET /api/cluster?format=raw (200 OK - RAW_JSON_READY)
{
    "success": true,
    "messages": "Success",
    "data": {
        "items": [
            {
                "id_cluster": 12,
                "nama_cluster": "Cluster Aster",
                "status": "aktif",
                "permissions": { "can_edit": true, "can_delete": false }
            }
        ],
        "meta": { "page": 1, "per_page": 25, "total": 1 }
    },
    "token": "a1b2c3..."
}

// POST /api/cluster (400 / 422 Validasi Gagal - Tetap Full JSON)
{
    "success": false,
    "messages": "Validasi gagal.",
    "errors": {
        "nama_cluster": "Nama cluster wajib diisi."
    },
    "token": "a1b2c3..."
}

// GET /api/cluster/999 (404 Not Found - Tetap Full JSON, Dilarang HTML PageNotFoundException)
{
    "success": false,
    "messages": "Data tidak ditemukan.",
    "token": "a1b2c3..."
}
```

## Global Exception Handler

Tambahkan di `app/Config/Exceptions.php` agar semua exception otomatis ter-handle:

```php
public function handler(int $statusCode, Throwable $exception): void
{
    if ($this->request instanceof IncomingRequest) {
        $response = service('response');
        $response->setContentType('application/json');
        $response->setStatusCode($statusCode);
        $response->setJSON([
            'status'  => false,
            'message' => $exception->getMessage(),
        ]);
        $response->send();
        exit;
    }

    parent::handler($statusCode, $exception);
}
```

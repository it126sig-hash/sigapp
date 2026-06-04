# Standar Format JSON Response API

## BaseController untuk API

Buat `app/Controllers/Api/BaseApiController.php`:

```php
<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class BaseApiController extends ResourceController
{
    protected $format = 'json';

    /**
     * Response sukses dengan data.
     */
    protected function success(mixed $data, string $message = 'Success', int $code = 200)
    {
        return $this->respond([
            'status'  => true,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    /**
     * Response sukses tanpa data (untuk delete, dsb.).
     */
    protected function successMessage(string $message, int $code = 200)
    {
        return $this->respond([
            'status'  => true,
            'message' => $message,
        ], $code);
    }

    /**
     * Response error.
     */
    protected function error(string $message, int $code = 400, mixed $errors = null)
    {
        $body = [
            'status'  => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $body['errors'] = $errors;
        }

        return $this->respond($body, $code);
    }
}
```

## Contoh Response

```json
// GET /api/users
{
    "status": true,
    "message": "Success",
    "data": [
        { "id": 1, "name": "Budi", "email": "budi@example.com" }
    ]
}

// POST /api/users (validasi gagal)
{
    "status": false,
    "message": "Validation failed",
    "errors": {
        "email": "Email already exists"
    }
}

// GET /api/users/99 (not found)
{
    "status": false,
    "message": "User with id 99 not found"
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

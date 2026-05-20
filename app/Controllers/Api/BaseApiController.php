<?php

namespace App\Controllers\Api;

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
}

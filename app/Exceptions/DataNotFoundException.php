<?php

namespace App\Exceptions;

use Exception;

class DataNotFoundException extends Exception
{
    public function __construct(string $message = "Data tidak ditemukan", int $code = 404, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

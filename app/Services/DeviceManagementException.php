<?php

namespace App\Services;

use RuntimeException;

class DeviceManagementException extends RuntimeException
{
    public function __construct(string $message, private readonly int $httpStatus)
    {
        parent::__construct($message);
    }

    public function statusCode(): int
    {
        return $this->httpStatus;
    }
}

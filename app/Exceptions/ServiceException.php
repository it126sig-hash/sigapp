<?php

namespace App\Exceptions;

use RuntimeException;
use Throwable;

class ServiceException extends RuntimeException
{
    private array $errors;

    public function __construct(string $message = '', int $code = 400, array $errors = [], ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->errors = $errors;
    }

    public function getStatusCode(): int
    {
        $code = (int) $this->getCode();
        return ($code >= 400 && $code < 600) ? $code : 400;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}

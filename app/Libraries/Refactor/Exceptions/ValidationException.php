<?php

namespace App\Libraries\Refactor\Exceptions;

/**
 * Validation Exception
 * 
 * Exception thrown during validation operations.
 * Includes invalid module name, invalid options, and prerequisite errors.
 * 
 * @package App\Libraries\Refactor\Exceptions
 */
class ValidationException extends RefactorException
{
    /**
     * Error codes for validation errors (5xxx)
     */
    public const ERROR_MODULE_NOT_FOUND = 5001;
    public const ERROR_INVALID_OPTIONS = 5002;
    public const ERROR_PREREQUISITE_MISSING = 5003;
    public const ERROR_INVALID_INPUT = 5004;

    /**
     * Create a new ValidationException instance
     * 
     * @param string $message Error message
     * @param int $code Error code
     * @param string $severity Error severity
     * @param \Throwable|null $previous Previous exception
     */
    public function __construct(
        string $message = "",
        int $code = 0,
        string $severity = self::SEVERITY_ERROR,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, self::CATEGORY_VALIDATION, $severity, $previous);
    }

    /**
     * Create exception for module not found error
     * 
     * @param string $moduleName
     * @return self
     */
    public static function moduleNotFound(string $moduleName): self
    {
        return (new self(
            "Module '{$moduleName}' not found in inventory",
            self::ERROR_MODULE_NOT_FOUND,
            self::SEVERITY_ERROR
        ))->setContext(['module' => $moduleName]);
    }

    /**
     * Create exception for invalid options error
     * 
     * @param string $reason
     * @return self
     */
    public static function invalidOptions(string $reason): self
    {
        return new self(
            "Invalid refactoring options: {$reason}",
            self::ERROR_INVALID_OPTIONS,
            self::SEVERITY_ERROR
        );
    }

    /**
     * Create exception for prerequisite missing error
     * 
     * @param string $prerequisite
     * @param string $action
     * @return self
     */
    public static function prerequisiteMissing(string $prerequisite, string $action): self
    {
        return new self(
            "Cannot {$action}: prerequisite '{$prerequisite}' is missing",
            self::ERROR_PREREQUISITE_MISSING,
            self::SEVERITY_ERROR
        );
    }

    /**
     * Create exception for invalid input error
     * 
     * @param string $field Field name
     * @param string $reason Validation failure reason
     * @return self
     */
    public static function invalidInput(string $field, string $reason): self
    {
        return (new self(
            "Invalid input for '{$field}': {$reason}",
            self::ERROR_INVALID_INPUT,
            self::SEVERITY_ERROR
        ))->setContext(['field' => $field]);
    }
}

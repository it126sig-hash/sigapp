<?php

namespace App\Libraries\Refactor\Exceptions;

/**
 * Discovery Exception
 * 
 * Exception thrown during module discovery operations.
 * Includes file system errors, parse errors, and configuration errors.
 * 
 * @package App\Libraries\Refactor\Exceptions
 */
class DiscoveryException extends RefactorException
{
    /**
     * Error codes for discovery errors (1xxx)
     */
    public const ERROR_FILE_NOT_FOUND = 1001;
    public const ERROR_PERMISSION_DENIED = 1002;
    public const ERROR_PARSE_FAILED = 1003;
    public const ERROR_INVALID_CONFIGURATION = 1004;
    public const ERROR_DIRECTORY_NOT_FOUND = 1005;

    /**
     * Create a new DiscoveryException instance
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
        parent::__construct($message, $code, self::CATEGORY_DISCOVERY, $severity, $previous);
    }

    /**
     * Create exception for file not found error
     * 
     * @param string $filePath
     * @return self
     */
    public static function fileNotFound(string $filePath): self
    {
        return (new self(
            "File not found: {$filePath}",
            self::ERROR_FILE_NOT_FOUND,
            self::SEVERITY_ERROR
        ))->setFilePath($filePath);
    }

    /**
     * Create exception for permission denied error
     * 
     * @param string $filePath
     * @return self
     */
    public static function permissionDenied(string $filePath): self
    {
        return (new self(
            "Permission denied: {$filePath}",
            self::ERROR_PERMISSION_DENIED,
            self::SEVERITY_ERROR
        ))->setFilePath($filePath);
    }

    /**
     * Create exception for parse failed error
     * 
     * @param string $filePath
     * @param string $reason
     * @return self
     */
    public static function parseFailed(string $filePath, string $reason = ""): self
    {
        $message = "Failed to parse file: {$filePath}";
        if ($reason) {
            $message .= " - {$reason}";
        }

        return (new self(
            $message,
            self::ERROR_PARSE_FAILED,
            self::SEVERITY_WARNING
        ))->setFilePath($filePath);
    }

    /**
     * Create exception for directory not found error
     * 
     * @param string $directoryPath
     * @return self
     */
    public static function directoryNotFound(string $directoryPath): self
    {
        return (new self(
            "Directory not found: {$directoryPath}",
            self::ERROR_DIRECTORY_NOT_FOUND,
            self::SEVERITY_ERROR
        ))->setFilePath($directoryPath);
    }

    /**
     * Create exception for invalid configuration error
     * 
     * @param string $message
     * @return self
     */
    public static function invalidConfiguration(string $message): self
    {
        return new self(
            "Invalid configuration: {$message}",
            self::ERROR_INVALID_CONFIGURATION,
            self::SEVERITY_ERROR
        );
    }
}

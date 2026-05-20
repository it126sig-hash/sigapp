<?php

namespace App\Libraries\Refactor\Exceptions;

/**
 * Refactor Execution Exception
 * 
 * Exception thrown during refactoring execution operations.
 * Includes backup errors, code generation errors, file write errors, and test failures.
 * 
 * @package App\Libraries\Refactor\Exceptions
 */
class RefactorExecutionException extends RefactorException
{
    /**
     * Error codes for refactoring errors (4xxx)
     */
    public const ERROR_BACKUP_FAILED = 4001;
    public const ERROR_CODE_GEN_FAILED = 4002;
    public const ERROR_FILE_WRITE_FAILED = 4003;
    public const ERROR_TEST_FAILED = 4004;
    public const ERROR_ROLLBACK_FAILED = 4005;
    public const ERROR_STEP_FAILED = 4006;

    /**
     * Create a new RefactorExecutionException instance
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
        parent::__construct($message, $code, self::CATEGORY_REFACTOR, $severity, $previous);
    }

    /**
     * Create exception for backup failed error
     * 
     * @param string $reason
     * @return self
     */
    public static function backupFailed(string $reason): self
    {
        return new self(
            "Failed to create backup: {$reason}",
            self::ERROR_BACKUP_FAILED,
            self::SEVERITY_CRITICAL
        );
    }

    /**
     * Create exception for code generation failed error
     * 
     * @param string $component Component being generated (e.g., "Repository", "Service")
     * @param string $reason Failure reason
     * @return self
     */
    public static function codeGenerationFailed(string $component, string $reason): self
    {
        return (new self(
            "Failed to generate {$component}: {$reason}",
            self::ERROR_CODE_GEN_FAILED,
            self::SEVERITY_ERROR
        ))->setContext(['component' => $component]);
    }

    /**
     * Create exception for file write failed error
     * 
     * @param string $filePath
     * @param string $reason
     * @return self
     */
    public static function fileWriteFailed(string $filePath, string $reason): self
    {
        return (new self(
            "Failed to write file '{$filePath}': {$reason}",
            self::ERROR_FILE_WRITE_FAILED,
            self::SEVERITY_ERROR
        ))->setFilePath($filePath);
    }

    /**
     * Create exception for test failed error
     * 
     * @param string $testOutput Test output
     * @return self
     */
    public static function testFailed(string $testOutput): self
    {
        return (new self(
            "Tests failed after refactoring",
            self::ERROR_TEST_FAILED,
            self::SEVERITY_ERROR
        ))->setContext(['testOutput' => $testOutput]);
    }

    /**
     * Create exception for rollback failed error
     * 
     * @param string $backupId
     * @param string $reason
     * @return self
     */
    public static function rollbackFailed(string $backupId, string $reason): self
    {
        return (new self(
            "Failed to rollback to backup '{$backupId}': {$reason}",
            self::ERROR_ROLLBACK_FAILED,
            self::SEVERITY_CRITICAL
        ))->setContext(['backupId' => $backupId]);
    }

    /**
     * Create exception for refactoring step failed error
     * 
     * @param string $step Step name
     * @param string $reason Failure reason
     * @return self
     */
    public static function stepFailed(string $step, string $reason): self
    {
        return (new self(
            "Refactoring step '{$step}' failed: {$reason}",
            self::ERROR_STEP_FAILED,
            self::SEVERITY_ERROR
        ))->setContext(['step' => $step]);
    }
}

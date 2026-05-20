<?php

namespace App\Libraries\Refactor\Exceptions;

/**
 * Security Exception
 * 
 * Exception thrown during security scanning operations.
 * Includes rule loading errors and pattern matching errors.
 * 
 * @package App\Libraries\Refactor\Exceptions
 */
class SecurityException extends RefactorException
{
    /**
     * Error codes for security errors (3xxx)
     */
    public const ERROR_RULE_LOAD_FAILED = 3001;
    public const ERROR_PATTERN_INVALID = 3002;
    public const ERROR_SCAN_FAILED = 3003;

    /**
     * Create a new SecurityException instance
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
        parent::__construct($message, $code, self::CATEGORY_SECURITY, $severity, $previous);
    }

    /**
     * Create exception for rule load failed error
     * 
     * @param string $ruleName
     * @param string $reason
     * @return self
     */
    public static function ruleLoadFailed(string $ruleName, string $reason = ""): self
    {
        $message = "Failed to load security rule: {$ruleName}";
        if ($reason) {
            $message .= " - {$reason}";
        }

        return (new self(
            $message,
            self::ERROR_RULE_LOAD_FAILED,
            self::SEVERITY_WARNING
        ))->setContext(['rule' => $ruleName]);
    }

    /**
     * Create exception for invalid pattern error
     * 
     * @param string $pattern
     * @param string $reason
     * @return self
     */
    public static function patternInvalid(string $pattern, string $reason = ""): self
    {
        $message = "Invalid security pattern: {$pattern}";
        if ($reason) {
            $message .= " - {$reason}";
        }

        return (new self(
            $message,
            self::ERROR_PATTERN_INVALID,
            self::SEVERITY_WARNING
        ))->setContext(['pattern' => $pattern]);
    }

    /**
     * Create exception for scan failed error
     * 
     * @param string $module Module name
     * @param string $reason Failure reason
     * @return self
     */
    public static function scanFailed(string $module, string $reason): self
    {
        return (new self(
            "Failed to scan module '{$module}': {$reason}",
            self::ERROR_SCAN_FAILED,
            self::SEVERITY_ERROR
        ))->setContext(['module' => $module]);
    }
}

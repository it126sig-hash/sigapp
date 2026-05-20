<?php

namespace App\Libraries\Refactor\Exceptions;

/**
 * Analysis Exception
 * 
 * Exception thrown during dependency analysis and impact analysis operations.
 * Includes circular dependency errors, missing dependency errors, and invalid graph errors.
 * 
 * @package App\Libraries\Refactor\Exceptions
 */
class AnalysisException extends RefactorException
{
    /**
     * Error codes for analysis errors (2xxx)
     */
    public const ERROR_CIRCULAR_DEPENDENCY = 2001;
    public const ERROR_MISSING_DEPENDENCY = 2002;
    public const ERROR_INVALID_GRAPH = 2003;
    public const ERROR_ANALYSIS_FAILED = 2004;

    /**
     * Create a new AnalysisException instance
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
        parent::__construct($message, $code, self::CATEGORY_ANALYSIS, $severity, $previous);
    }

    /**
     * Create exception for circular dependency error
     * 
     * @param array $chain Circular dependency chain
     * @return self
     */
    public static function circularDependency(array $chain): self
    {
        $chainStr = implode(' -> ', $chain);
        return (new self(
            "Circular dependency detected: {$chainStr}",
            self::ERROR_CIRCULAR_DEPENDENCY,
            self::SEVERITY_WARNING
        ))->setContext(['chain' => $chain]);
    }

    /**
     * Create exception for missing dependency error
     * 
     * @param string $module Module name
     * @param string $dependency Missing dependency name
     * @return self
     */
    public static function missingDependency(string $module, string $dependency): self
    {
        return (new self(
            "Module '{$module}' depends on '{$dependency}' which was not found in inventory",
            self::ERROR_MISSING_DEPENDENCY,
            self::SEVERITY_WARNING
        ))->setContext(['module' => $module, 'dependency' => $dependency]);
    }

    /**
     * Create exception for invalid graph error
     * 
     * @param string $reason
     * @return self
     */
    public static function invalidGraph(string $reason): self
    {
        return new self(
            "Invalid dependency graph: {$reason}",
            self::ERROR_INVALID_GRAPH,
            self::SEVERITY_ERROR
        );
    }

    /**
     * Create exception for analysis failed error
     * 
     * @param string $module Module name
     * @param string $reason Failure reason
     * @return self
     */
    public static function analysisFailed(string $module, string $reason): self
    {
        return (new self(
            "Failed to analyze module '{$module}': {$reason}",
            self::ERROR_ANALYSIS_FAILED,
            self::SEVERITY_ERROR
        ))->setContext(['module' => $module]);
    }
}

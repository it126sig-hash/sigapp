<?php

namespace App\Libraries\Refactor\Exceptions;

use Exception;

/**
 * Base Refactor Exception
 * 
 * Base exception class for all refactoring system exceptions.
 * Provides common functionality for error handling and reporting.
 * 
 * @package App\Libraries\Refactor\Exceptions
 */
class RefactorException extends Exception
{
    /**
     * Error category
     * 
     * @var string
     */
    protected string $category;

    /**
     * Error severity
     * 
     * @var string
     */
    protected string $severity;

    /**
     * Related file path (if applicable)
     * 
     * @var string|null
     */
    protected ?string $filePath = null;

    /**
     * Related line number (if applicable)
     * 
     * @var int|null
     */
    protected ?int $lineNumber = null;

    /**
     * Additional context data
     * 
     * @var array|null
     */
    protected ?array $context = null;

    /**
     * Error categories
     */
    public const CATEGORY_DISCOVERY = 'DISCOVERY';
    public const CATEGORY_ANALYSIS = 'ANALYSIS';
    public const CATEGORY_SECURITY = 'SECURITY';
    public const CATEGORY_REFACTOR = 'REFACTOR';
    public const CATEGORY_VALIDATION = 'VALIDATION';

    /**
     * Error severities
     */
    public const SEVERITY_CRITICAL = 'CRITICAL';
    public const SEVERITY_ERROR = 'ERROR';
    public const SEVERITY_WARNING = 'WARNING';
    public const SEVERITY_INFO = 'INFO';

    /**
     * Create a new RefactorException instance
     * 
     * @param string $message Error message
     * @param int $code Error code
     * @param string $category Error category
     * @param string $severity Error severity
     * @param \Throwable|null $previous Previous exception
     */
    public function __construct(
        string $message = "",
        int $code = 0,
        string $category = self::CATEGORY_REFACTOR,
        string $severity = self::SEVERITY_ERROR,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->category = $category;
        $this->severity = $severity;
    }

    /**
     * Set the related file path
     * 
     * @param string $filePath
     * @return self
     */
    public function setFilePath(string $filePath): self
    {
        $this->filePath = $filePath;
        return $this;
    }

    /**
     * Set the related line number
     * 
     * @param int $lineNumber
     * @return self
     */
    public function setLineNumber(int $lineNumber): self
    {
        $this->lineNumber = $lineNumber;
        return $this;
    }

    /**
     * Set additional context data
     * 
     * @param array $context
     * @return self
     */
    public function setContext(array $context): self
    {
        $this->context = $context;
        return $this;
    }

    /**
     * Get error category
     * 
     * @return string
     */
    public function getCategory(): string
    {
        return $this->category;
    }

    /**
     * Get error severity
     * 
     * @return string
     */
    public function getSeverity(): string
    {
        return $this->severity;
    }

    /**
     * Get related file path
     * 
     * @return string|null
     */
    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    /**
     * Get related line number
     * 
     * @return int|null
     */
    public function getLineNumber(): ?int
    {
        return $this->lineNumber;
    }

    /**
     * Get additional context data
     * 
     * @return array|null
     */
    public function getContext(): ?array
    {
        return $this->context;
    }

    /**
     * Check if this is a critical error
     * 
     * @return bool
     */
    public function isCritical(): bool
    {
        return $this->severity === self::SEVERITY_CRITICAL;
    }

    /**
     * Convert exception to array
     * 
     * @return array
     */
    public function toArray(): array
    {
        return [
            'code' => $this->getCode(),
            'message' => $this->getMessage(),
            'category' => $this->category,
            'severity' => $this->severity,
            'filePath' => $this->filePath,
            'lineNumber' => $this->lineNumber,
            'context' => $this->context,
            'file' => $this->getFile(),
            'line' => $this->getLine(),
        ];
    }
}

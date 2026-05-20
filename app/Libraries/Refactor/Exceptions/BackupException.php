<?php

namespace App\Libraries\Refactor\Exceptions;

/**
 * Backup Exception
 * 
 * Exception thrown during backup and restore operations.
 * 
 * @package App\Libraries\Refactor\Exceptions
 */
class BackupException extends RefactorExecutionException
{
    /**
     * Create a new BackupException instance
     * 
     * @param string $message Error message
     * @param int $code Error code (defaults to ERROR_BACKUP_FAILED)
     * @param \Throwable|null $previous Previous exception
     */
    public function __construct(
        string $message = "",
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        $code = $code === 0 ? self::ERROR_BACKUP_FAILED : $code;
        parent::__construct($message, $code, self::SEVERITY_CRITICAL, $previous);
    }

    /**
     * Create exception for backup creation failure
     * 
     * @param string $reason Failure reason
     * @return self
     */
    public static function creationFailed(string $reason): self
    {
        return new self("Failed to create backup: {$reason}");
    }

    /**
     * Create exception for backup restore failure
     * 
     * @param string $backupId Backup identifier
     * @param string $reason Failure reason
     * @return self
     */
    public static function restoreFailed(string $backupId, string $reason): self
    {
        return (new self(
            "Failed to restore backup '{$backupId}': {$reason}",
            self::ERROR_ROLLBACK_FAILED
        ))->setContext(['backupId' => $backupId]);
    }

    /**
     * Create exception for backup not found
     * 
     * @param string $backupId Backup identifier
     * @return self
     */
    public static function notFound(string $backupId): self
    {
        return (new self(
            "Backup not found: {$backupId}",
            self::ERROR_BACKUP_FAILED
        ))->setContext(['backupId' => $backupId]);
    }

    /**
     * Create exception for backup deletion failure
     * 
     * @param string $backupId Backup identifier
     * @param string $reason Failure reason
     * @return self
     */
    public static function deletionFailed(string $backupId, string $reason): self
    {
        return (new self(
            "Failed to delete backup '{$backupId}': {$reason}",
            self::ERROR_BACKUP_FAILED
        ))->setContext(['backupId' => $backupId]);
    }

    /**
     * Create exception for checksum mismatch
     * 
     * @param string $filePath File path
     * @param string $expected Expected checksum
     * @param string $actual Actual checksum
     * @return self
     */
    public static function checksumMismatch(string $filePath, string $expected, string $actual): self
    {
        return (new self(
            "Checksum mismatch for file '{$filePath}'. Expected: {$expected}, Actual: {$actual}",
            self::ERROR_BACKUP_FAILED
        ))->setFilePath($filePath)->setContext([
            'expectedChecksum' => $expected,
            'actualChecksum' => $actual,
        ]);
    }
}

<?php

namespace App\Libraries\Refactor\Models;

use DateTime;

/**
 * Refactor Result Data Model
 * 
 * Contains the results of a refactoring operation.
 * 
 * @package App\Libraries\Refactor\Models
 */
class RefactorResult
{
    /**
     * Whether the refactoring was successful
     */
    public bool $success;

    /**
     * Array of file paths that were created
     * 
     * @var string[]
     */
    public array $filesCreated = [];

    /**
     * Array of file paths that were modified
     * 
     * @var string[]
     */
    public array $filesModified = [];

    /**
     * Array of refactoring steps that were completed
     * 
     * @var string[]
     */
    public array $stepsCompleted = [];

    /**
     * Backup ID for rollback (if backup was created)
     */
    public ?string $backupId = null;

    /**
     * Error message if refactoring failed
     */
    public ?string $errorMessage = null;

    /**
     * Timestamp when refactoring completed
     */
    public DateTime $completedAt;

    /**
     * Create a new RefactorResult instance
     * 
     * @param bool $success Whether refactoring was successful
     */
    public function __construct(bool $success)
    {
        $this->success = $success;
        $this->completedAt = new DateTime();
    }

    /**
     * Create a successful result
     * 
     * @return self
     */
    public static function success(): self
    {
        return new self(true);
    }

    /**
     * Create a failed result with error message
     * 
     * @param string $errorMessage Error message
     * @return self
     */
    public static function failure(string $errorMessage): self
    {
        $result = new self(false);
        $result->errorMessage = $errorMessage;

        return $result;
    }

    /**
     * Add a created file to the result
     * 
     * @param string $filePath File path
     * @return void
     */
    public function addCreatedFile(string $filePath): void
    {
        $this->filesCreated[] = $filePath;
    }

    /**
     * Add a modified file to the result
     * 
     * @param string $filePath File path
     * @return void
     */
    public function addModifiedFile(string $filePath): void
    {
        $this->filesModified[] = $filePath;
    }

    /**
     * Add a completed step to the result
     * 
     * @param string $step Step description
     * @return void
     */
    public function addCompletedStep(string $step): void
    {
        $this->stepsCompleted[] = $step;
    }

    /**
     * Get total count of files affected
     * 
     * @return int
     */
    public function getTotalFilesAffected(): int
    {
        return count($this->filesCreated) + count($this->filesModified);
    }

    /**
     * Convert result to markdown report
     * 
     * @return string
     */
    public function toMarkdown(): string
    {
        $lines = [];
        $lines[] = '# Refactoring Result';
        $lines[] = '';
        $lines[] = '**Status:** ' . ($this->success ? '✅ Success' : '❌ Failed');
        $lines[] = '**Completed At:** ' . $this->completedAt->format('Y-m-d H:i:s');
        $lines[] = '';

        if (!$this->success && $this->errorMessage) {
            $lines[] = '## Error';
            $lines[] = '';
            $lines[] = $this->errorMessage;
            $lines[] = '';
        }

        if ($this->backupId) {
            $lines[] = '**Backup ID:** ' . $this->backupId;
            $lines[] = '';
        }

        if (!empty($this->stepsCompleted)) {
            $lines[] = '## Steps Completed';
            $lines[] = '';
            foreach ($this->stepsCompleted as $step) {
                $lines[] = '- ' . $step;
            }
            $lines[] = '';
        }

        if (!empty($this->filesCreated)) {
            $lines[] = '## Files Created (' . count($this->filesCreated) . ')';
            $lines[] = '';
            foreach ($this->filesCreated as $file) {
                $lines[] = '- ' . $file;
            }
            $lines[] = '';
        }

        if (!empty($this->filesModified)) {
            $lines[] = '## Files Modified (' . count($this->filesModified) . ')';
            $lines[] = '';
            foreach ($this->filesModified as $file) {
                $lines[] = '- ' . $file;
            }
            $lines[] = '';
        }

        $lines[] = '**Total Files Affected:** ' . $this->getTotalFilesAffected();

        return implode("\n", $lines);
    }

    /**
     * Convert to array representation
     * 
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'filesCreated' => $this->filesCreated,
            'filesModified' => $this->filesModified,
            'stepsCompleted' => $this->stepsCompleted,
            'backupId' => $this->backupId,
            'errorMessage' => $this->errorMessage,
            'completedAt' => $this->completedAt->format('c'),
        ];
    }
}

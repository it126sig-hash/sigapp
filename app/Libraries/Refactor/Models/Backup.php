<?php

namespace App\Libraries\Refactor\Models;

use DateTime;
use JsonException;

/**
 * Backup Metadata Model
 * 
 * Contains metadata about a backup created before refactoring operations.
 * 
 * @package App\Libraries\Refactor\Models
 */
class Backup
{
    /**
     * Unique backup identifier (timestamp-based)
     */
    public string $id;

    /**
     * Module name that was backed up
     */
    public string $moduleName;

    /**
     * Array of file paths that were backed up
     * 
     * @var string[]
     */
    public array $files = [];

    /**
     * Timestamp when backup was created
     */
    public DateTime $createdAt;

    /**
     * Optional description of the backup
     */
    public ?string $description = null;

    /**
     * File checksums for integrity verification
     * 
     * @var array<string, string> Map of file path to MD5 checksum
     */
    public array $checksums = [];

    /**
     * Create a new Backup instance
     * 
     * @param string $id Backup identifier
     * @param string $moduleName Module name
     * @param array<int, string> $files Array of file paths
     * @param DateTime|null $createdAt Creation timestamp (defaults to now)
     */
    public function __construct(
        string $id,
        string $moduleName,
        array $files = [],
        ?DateTime $createdAt = null
    ) {
        $this->id = $id;
        $this->moduleName = $moduleName;
        $this->files = $files;
        $this->createdAt = $createdAt ?? new DateTime();
    }

    /**
     * Add a file to the backup
     * 
     * @param string $filePath File path
     * @param string|null $checksum Optional MD5 checksum
     * @return void
     */
    public function addFile(string $filePath, ?string $checksum = null): void
    {
        if (!in_array($filePath, $this->files, true)) {
            $this->files[] = $filePath;
        }

        if ($checksum !== null) {
            $this->checksums[$filePath] = $checksum;
        }
    }

    /**
     * Get checksum for a specific file
     * 
     * @param string $filePath File path
     * @return string|null Checksum or null if not found
     */
    public function getChecksum(string $filePath): ?string
    {
        return $this->checksums[$filePath] ?? null;
    }

    /**
     * Get total number of files in backup
     * 
     * @return int
     */
    public function getFileCount(): int
    {
        return count($this->files);
    }

    /**
     * Check if backup contains a specific file
     * 
     * @param string $filePath File path
     * @return bool
     */
    public function hasFile(string $filePath): bool
    {
        return in_array($filePath, $this->files, true);
    }

    /**
     * Convert to array representation
     * 
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'moduleName' => $this->moduleName,
            'files' => $this->files,
            'createdAt' => $this->createdAt->format('c'),
            'description' => $this->description,
            'checksums' => $this->checksums,
        ];
    }

    /**
     * Convert to JSON string
     * 
     * @return string
     * @throws JsonException
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
    }

    /**
     * Create instance from array
     * 
     * @param array<string, mixed> $data Array data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $backup = new self(
            id: $data['id'],
            moduleName: $data['moduleName'],
            files: $data['files'] ?? [],
            createdAt: new DateTime($data['createdAt'])
        );

        $backup->description = $data['description'] ?? null;
        $backup->checksums = $data['checksums'] ?? [];

        return $backup;
    }

    /**
     * Create instance from JSON string
     * 
     * @param string $json JSON string
     * @return self
     * @throws JsonException
     */
    public static function fromJson(string $json): self
    {
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        return self::fromArray($data);
    }
}

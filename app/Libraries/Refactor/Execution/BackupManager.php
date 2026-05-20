<?php

namespace App\Libraries\Refactor\Execution;

use App\Libraries\Refactor\Exceptions\BackupException;
use App\Libraries\Refactor\Models\Backup;
use DateTime;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Backup Manager
 * 
 * Manages backup and restore operations for refactoring safety.
 * Creates timestamped backups of files before modifications and provides
 * rollback capabilities if refactoring fails.
 * 
 * @package App\Libraries\Refactor\Execution
 */
class BackupManager
{
    /**
     * Base directory for storing backups
     */
    private string $backupDir;

    /**
     * Create a new BackupManager instance
     * 
     * @param string|null $backupDir Custom backup directory (defaults to writable/refactor/backups)
     */
    public function __construct(?string $backupDir = null)
    {
        $this->backupDir = $backupDir ?? WRITEPATH . 'refactor/backups';
        $this->ensureBackupDirectoryExists();
    }

    /**
     * Create a backup of specified files
     * 
     * @param array<int, string> $files Array of file paths to backup
     * @param string $moduleName Module name for metadata
     * @param string|null $description Optional backup description
     * @return string Backup ID
     * @throws BackupException If backup creation fails
     */
    public function createBackup(array $files, string $moduleName, ?string $description = null): string
    {
        // Generate unique backup ID based on timestamp
        $backupId = 'backup_' . date('YmdHis') . '_' . uniqid();
        $backupPath = $this->getBackupPath($backupId);

        try {
            // Create backup directory
            if (!mkdir($backupPath, 0755, true) && !is_dir($backupPath)) {
                throw new BackupException("Failed to create backup directory: {$backupPath}");
            }

            // Create backup metadata
            $backup = new Backup($backupId, $moduleName, [], new DateTime());
            $backup->description = $description;

            // Copy each file to backup directory
            foreach ($files as $filePath) {
                if (!file_exists($filePath)) {
                    // Skip non-existent files (they might be new files to be created)
                    continue;
                }

                // Calculate relative path from project root
                $relativePath = $this->getRelativePath($filePath);
                $backupFilePath = $backupPath . DIRECTORY_SEPARATOR . $relativePath;

                // Create subdirectories if needed
                $backupFileDir = dirname($backupFilePath);
                if (!is_dir($backupFileDir) && !mkdir($backupFileDir, 0755, true) && !is_dir($backupFileDir)) {
                    throw new BackupException("Failed to create backup subdirectory: {$backupFileDir}");
                }

                // Copy file
                if (!copy($filePath, $backupFilePath)) {
                    throw new BackupException("Failed to backup file: {$filePath}");
                }

                // Calculate checksum for integrity verification
                $checksum = md5_file($filePath);
                if ($checksum === false) {
                    throw new BackupException("Failed to calculate checksum for file: {$filePath}");
                }

                // Add to backup metadata
                $backup->addFile($filePath, $checksum);
            }

            // Save backup metadata
            $this->saveBackupMetadata($backup);

            return $backupId;
        } catch (\Exception $e) {
            // Clean up partial backup on failure
            if (is_dir($backupPath)) {
                $this->deleteDirectory($backupPath);
            }

            throw new BackupException(
                "Backup creation failed: {$e->getMessage()}",
                0,
                $e
            );
        }
    }

    /**
     * Restore files from a backup
     * 
     * @param string $backupId Backup identifier
     * @return Backup Restored backup metadata
     * @throws BackupException If restore fails
     */
    public function restoreBackup(string $backupId): Backup
    {
        $backupPath = $this->getBackupPath($backupId);

        if (!is_dir($backupPath)) {
            throw new BackupException("Backup not found: {$backupId}");
        }

        // Load backup metadata
        $backup = $this->loadBackupMetadata($backupId);

        try {
            // Restore each file
            foreach ($backup->files as $originalFilePath) {
                $relativePath = $this->getRelativePath($originalFilePath);
                $backupFilePath = $backupPath . DIRECTORY_SEPARATOR . $relativePath;

                if (!file_exists($backupFilePath)) {
                    throw new BackupException("Backup file not found: {$backupFilePath}");
                }

                // Create parent directory if needed
                $originalFileDir = dirname($originalFilePath);
                if (!is_dir($originalFileDir) && !mkdir($originalFileDir, 0755, true) && !is_dir($originalFileDir)) {
                    throw new BackupException("Failed to create directory: {$originalFileDir}");
                }

                // Restore file
                if (!copy($backupFilePath, $originalFilePath)) {
                    throw new BackupException("Failed to restore file: {$originalFilePath}");
                }

                // Verify checksum if available
                $expectedChecksum = $backup->getChecksum($originalFilePath);
                if ($expectedChecksum !== null) {
                    $actualChecksum = md5_file($backupFilePath);
                    if ($actualChecksum !== $expectedChecksum) {
                        throw new BackupException(
                            "Checksum mismatch for file: {$originalFilePath}. Backup may be corrupted."
                        );
                    }
                }
            }

            return $backup;
        } catch (\Exception $e) {
            throw new BackupException(
                "Backup restore failed: {$e->getMessage()}",
                0,
                $e
            );
        }
    }

    /**
     * List all available backups
     * 
     * @return array<int, Backup> Array of backup metadata
     */
    public function listBackups(): array
    {
        $backups = [];

        if (!is_dir($this->backupDir)) {
            return $backups;
        }

        $directories = new \DirectoryIterator($this->backupDir);

        foreach ($directories as $dir) {
            if ($dir->isDot() || !$dir->isDir()) {
                continue;
            }

            $backupId = $dir->getFilename();

            try {
                $backup = $this->loadBackupMetadata($backupId);
                $backups[] = $backup;
            } catch (BackupException $e) {
                // Skip backups with invalid metadata
                continue;
            }
        }

        // Sort by creation date (newest first)
        usort($backups, fn($a, $b) => $b->createdAt <=> $a->createdAt);

        return $backups;
    }

    /**
     * Delete a backup
     * 
     * @param string $backupId Backup identifier
     * @return void
     * @throws BackupException If deletion fails
     */
    public function deleteBackup(string $backupId): void
    {
        $backupPath = $this->getBackupPath($backupId);

        if (!is_dir($backupPath)) {
            throw new BackupException("Backup not found: {$backupId}");
        }

        try {
            $this->deleteDirectory($backupPath);
        } catch (\Exception $e) {
            throw new BackupException(
                "Failed to delete backup: {$e->getMessage()}",
                0,
                $e
            );
        }
    }

    /**
     * Get backup by ID
     * 
     * @param string $backupId Backup identifier
     * @return Backup Backup metadata
     * @throws BackupException If backup not found
     */
    public function getBackup(string $backupId): Backup
    {
        return $this->loadBackupMetadata($backupId);
    }

    /**
     * Check if a backup exists
     * 
     * @param string $backupId Backup identifier
     * @return bool
     */
    public function backupExists(string $backupId): bool
    {
        return is_dir($this->getBackupPath($backupId));
    }

    /**
     * Get the full path to a backup directory
     * 
     * @param string $backupId Backup identifier
     * @return string
     */
    private function getBackupPath(string $backupId): string
    {
        return $this->backupDir . DIRECTORY_SEPARATOR . $backupId;
    }

    /**
     * Get the path to backup metadata file
     * 
     * @param string $backupId Backup identifier
     * @return string
     */
    private function getMetadataPath(string $backupId): string
    {
        return $this->getBackupPath($backupId) . DIRECTORY_SEPARATOR . 'metadata.json';
    }

    /**
     * Save backup metadata to JSON file
     * 
     * @param Backup $backup Backup metadata
     * @return void
     * @throws BackupException If save fails
     */
    private function saveBackupMetadata(Backup $backup): void
    {
        $metadataPath = $this->getMetadataPath($backup->id);

        try {
            $json = $backup->toJson();
            if (file_put_contents($metadataPath, $json) === false) {
                throw new BackupException("Failed to write metadata file: {$metadataPath}");
            }
        } catch (\JsonException $e) {
            throw new BackupException(
                "Failed to serialize backup metadata: {$e->getMessage()}",
                0,
                $e
            );
        }
    }

    /**
     * Load backup metadata from JSON file
     * 
     * @param string $backupId Backup identifier
     * @return Backup Backup metadata
     * @throws BackupException If load fails
     */
    private function loadBackupMetadata(string $backupId): Backup
    {
        $metadataPath = $this->getMetadataPath($backupId);

        if (!file_exists($metadataPath)) {
            throw new BackupException("Backup metadata not found: {$backupId}");
        }

        try {
            $json = file_get_contents($metadataPath);
            if ($json === false) {
                throw new BackupException("Failed to read metadata file: {$metadataPath}");
            }

            return Backup::fromJson($json);
        } catch (\JsonException $e) {
            throw new BackupException(
                "Failed to parse backup metadata: {$e->getMessage()}",
                0,
                $e
            );
        }
    }

    /**
     * Get relative path from project root
     * 
     * @param string $absolutePath Absolute file path
     * @return string Relative path
     */
    private function getRelativePath(string $absolutePath): string
    {
        $rootPath = ROOTPATH;

        // Normalize paths
        $absolutePath = str_replace('\\', '/', realpath($absolutePath) ?: $absolutePath);
        $rootPath = str_replace('\\', '/', realpath($rootPath) ?: $rootPath);

        // Remove root path prefix
        if (str_starts_with($absolutePath, $rootPath)) {
            return ltrim(substr($absolutePath, strlen($rootPath)), '/');
        }

        // If not under root path, use basename
        return basename($absolutePath);
    }

    /**
     * Ensure backup directory exists
     * 
     * @return void
     * @throws BackupException If directory creation fails
     */
    private function ensureBackupDirectoryExists(): void
    {
        if (!is_dir($this->backupDir)) {
            if (!mkdir($this->backupDir, 0755, true) && !is_dir($this->backupDir)) {
                throw new BackupException("Failed to create backup directory: {$this->backupDir}");
            }
        }
    }

    /**
     * Recursively delete a directory
     * 
     * @param string $dir Directory path
     * @return void
     */
    private function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isDir()) {
                rmdir($file->getPathname());
            } else {
                unlink($file->getPathname());
            }
        }

        rmdir($dir);
    }
}

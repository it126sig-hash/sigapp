<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Libraries\Refactor\Execution\BackupManager;

/**
 * CLI Command: Backup Management
 *
 * Lists, restores, and deletes backups created during refactoring.
 *
 * Usage:
 *   php spark refactor:backup              - List all backups
 *   php spark refactor:backup --restore ID - Restore a backup
 *   php spark refactor:backup --delete ID  - Delete a backup
 */
class RefactorBackup extends BaseCommand
{
    /**
     * Command group
     */
    protected $group = 'Refactor';

    /**
     * Command name
     */
    protected $name = 'refactor:backup';

    /**
     * Command description
     */
    protected $description = 'Manage refactoring backups (list, restore, delete)';

    /**
     * Command usage
     */
    protected $usage = 'refactor:backup [options]';

    /**
     * Command options
     */
    protected $options = [
        '--restore' => 'Restore a backup by ID',
        '--delete'  => 'Delete a backup by ID',
    ];

    /**
     * Execute the command
     */
    public function run(array $params)
    {
        CLI::write('=== Backup Management ===', 'cyan');
        CLI::newLine();

        try {
            $backupManager = new BackupManager();

            $restoreId = CLI::getOption('restore');
            $deleteId = CLI::getOption('delete');

            if ($restoreId) {
                $this->restoreBackup($backupManager, $restoreId);
            } elseif ($deleteId) {
                $this->deleteBackup($backupManager, $deleteId);
            } else {
                $this->listBackups($backupManager);
            }

        } catch (\Exception $e) {
            CLI::error('Backup operation failed: ' . $e->getMessage());
            return;
        }
    }

    /**
     * List all available backups
     */
    private function listBackups(BackupManager $manager): void
    {
        $backups = $manager->listBackups();

        if (empty($backups)) {
            CLI::write('No backups found.', 'yellow');
            return;
        }

        CLI::write("Found " . count($backups) . " backup(s):", 'yellow');
        CLI::newLine();

        foreach ($backups as $backup) {
            $createdAt = $backup->createdAt->format('Y-m-d H:i:s');
            $fileCount = $backup->getFileCount();
            $description = $backup->description ?? 'No description';

            CLI::write("  ID: {$backup->id}", 'green');
            CLI::write("    Module:      {$backup->moduleName}", 'white');
            CLI::write("    Created:     {$createdAt}", 'white');
            CLI::write("    Files:       {$fileCount}", 'white');
            CLI::write("    Description: {$description}", 'white');
            CLI::newLine();
        }

        CLI::write('Usage:', 'yellow');
        CLI::write('  Restore: php spark refactor:backup --restore <backupId>', 'white');
        CLI::write('  Delete:  php spark refactor:backup --delete <backupId>', 'white');
    }

    /**
     * Restore a backup by ID
     */
    private function restoreBackup(BackupManager $manager, string $backupId): void
    {
        if (!$manager->backupExists($backupId)) {
            CLI::error("Backup '{$backupId}' not found.");
            return;
        }

        $backup = $manager->getBackup($backupId);
        CLI::write("Restoring backup: {$backupId}", 'white');
        CLI::write("  Module: {$backup->moduleName}", 'white');
        CLI::write("  Files:  {$backup->getFileCount()}", 'white');
        CLI::newLine();

        $confirm = CLI::prompt('Are you sure you want to restore this backup? This will overwrite current files.', ['y', 'n']);
        if ($confirm !== 'y') {
            CLI::write('Restore cancelled.', 'yellow');
            return;
        }

        $manager->restoreBackup($backupId);
        CLI::write('Backup restored successfully!', 'green');
    }

    /**
     * Delete a backup by ID
     */
    private function deleteBackup(BackupManager $manager, string $backupId): void
    {
        if (!$manager->backupExists($backupId)) {
            CLI::error("Backup '{$backupId}' not found.");
            return;
        }

        $backup = $manager->getBackup($backupId);
        CLI::write("Deleting backup: {$backupId}", 'white');
        CLI::write("  Module: {$backup->moduleName}", 'white');
        CLI::write("  Created: {$backup->createdAt->format('Y-m-d H:i:s')}", 'white');
        CLI::newLine();

        $confirm = CLI::prompt('Are you sure you want to delete this backup? This cannot be undone.', ['y', 'n']);
        if ($confirm !== 'y') {
            CLI::write('Delete cancelled.', 'yellow');
            return;
        }

        $manager->deleteBackup($backupId);
        CLI::write('Backup deleted successfully!', 'green');
    }
}

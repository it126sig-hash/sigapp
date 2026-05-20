<?php

namespace Tests\Libraries\Refactor\Execution;

use App\Libraries\Refactor\Exceptions\BackupException;
use App\Libraries\Refactor\Execution\BackupManager;
use App\Libraries\Refactor\Models\Backup;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * BackupManager Test
 * 
 * Tests the BackupManager class to ensure it correctly creates, restores,
 * lists, and deletes backups with proper metadata and integrity verification.
 */
class BackupManagerTest extends CIUnitTestCase
{
    private BackupManager $backupManager;
    private string $testBackupDir;
    private string $testFilesDir;

    protected function setUp(): void
    {
        parent::setUp();

        // Create temporary directories for testing
        $this->testBackupDir = WRITEPATH . 'tests/backups_' . uniqid();
        $this->testFilesDir = WRITEPATH . 'tests/files_' . uniqid();

        mkdir($this->testBackupDir, 0755, true);
        mkdir($this->testFilesDir, 0755, true);

        $this->backupManager = new BackupManager($this->testBackupDir);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Clean up test directories
        $this->deleteDirectory($this->testBackupDir);
        $this->deleteDirectory($this->testFilesDir);
    }

    /**
     * Test creating a backup with single file
     */
    public function testCreateBackupWithSingleFile(): void
    {
        // Create a test file
        $testFile = $this->testFilesDir . '/test.php';
        file_put_contents($testFile, '<?php echo "test";');

        // Create backup
        $backupId = $this->backupManager->createBackup(
            [$testFile],
            'TestModule',
            'Test backup'
        );

        $this->assertIsString($backupId);
        $this->assertStringStartsWith('backup_', $backupId);
        $this->assertTrue($this->backupManager->backupExists($backupId));

        // Verify backup metadata
        $backup = $this->backupManager->getBackup($backupId);
        $this->assertInstanceOf(Backup::class, $backup);
        $this->assertSame('TestModule', $backup->moduleName);
        $this->assertSame('Test backup', $backup->description);
        $this->assertCount(1, $backup->files);
        $this->assertTrue($backup->hasFile($testFile));
        $this->assertNotNull($backup->getChecksum($testFile));
    }

    /**
     * Test creating a backup with multiple files
     */
    public function testCreateBackupWithMultipleFiles(): void
    {
        // Create test files
        $testFile1 = $this->testFilesDir . '/file1.php';
        $testFile2 = $this->testFilesDir . '/file2.php';
        $testFile3 = $this->testFilesDir . '/subdir/file3.php';

        mkdir(dirname($testFile3), 0755, true);

        file_put_contents($testFile1, '<?php echo "file1";');
        file_put_contents($testFile2, '<?php echo "file2";');
        file_put_contents($testFile3, '<?php echo "file3";');

        // Create backup
        $backupId = $this->backupManager->createBackup(
            [$testFile1, $testFile2, $testFile3],
            'MultiFileModule'
        );

        $this->assertTrue($this->backupManager->backupExists($backupId));

        // Verify backup metadata
        $backup = $this->backupManager->getBackup($backupId);
        $this->assertCount(3, $backup->files);
        $this->assertTrue($backup->hasFile($testFile1));
        $this->assertTrue($backup->hasFile($testFile2));
        $this->assertTrue($backup->hasFile($testFile3));
    }

    /**
     * Test creating backup skips non-existent files
     */
    public function testCreateBackupSkipsNonExistentFiles(): void
    {
        $existingFile = $this->testFilesDir . '/existing.php';
        $nonExistentFile = $this->testFilesDir . '/nonexistent.php';

        file_put_contents($existingFile, '<?php echo "exists";');

        // Create backup with mix of existing and non-existing files
        $backupId = $this->backupManager->createBackup(
            [$existingFile, $nonExistentFile],
            'TestModule'
        );

        // Should only backup the existing file
        $backup = $this->backupManager->getBackup($backupId);
        $this->assertCount(1, $backup->files);
        $this->assertTrue($backup->hasFile($existingFile));
        $this->assertFalse($backup->hasFile($nonExistentFile));
    }

    /**
     * Test restoring a backup
     */
    public function testRestoreBackup(): void
    {
        // Create test file with original content
        $testFile = $this->testFilesDir . '/restore_test.php';
        $originalContent = '<?php echo "original";';
        file_put_contents($testFile, $originalContent);

        // Create backup
        $backupId = $this->backupManager->createBackup([$testFile], 'TestModule');

        // Modify the file
        $modifiedContent = '<?php echo "modified";';
        file_put_contents($testFile, $modifiedContent);
        $this->assertSame($modifiedContent, file_get_contents($testFile));

        // Restore backup
        $backup = $this->backupManager->restoreBackup($backupId);

        // Verify file was restored
        $this->assertSame($originalContent, file_get_contents($testFile));
        $this->assertInstanceOf(Backup::class, $backup);
        $this->assertSame('TestModule', $backup->moduleName);
    }

    /**
     * Test restoring backup with multiple files
     */
    public function testRestoreBackupWithMultipleFiles(): void
    {
        // Create test files
        $testFile1 = $this->testFilesDir . '/file1.php';
        $testFile2 = $this->testFilesDir . '/file2.php';

        file_put_contents($testFile1, 'original1');
        file_put_contents($testFile2, 'original2');

        // Create backup
        $backupId = $this->backupManager->createBackup(
            [$testFile1, $testFile2],
            'TestModule'
        );

        // Modify files
        file_put_contents($testFile1, 'modified1');
        file_put_contents($testFile2, 'modified2');

        // Restore backup
        $this->backupManager->restoreBackup($backupId);

        // Verify files were restored
        $this->assertSame('original1', file_get_contents($testFile1));
        $this->assertSame('original2', file_get_contents($testFile2));
    }

    /**
     * Test restoring backup throws exception for non-existent backup
     */
    public function testRestoreBackupThrowsExceptionForNonExistentBackup(): void
    {
        $this->expectException(BackupException::class);
        $this->expectExceptionMessage('Backup not found');

        $this->backupManager->restoreBackup('nonexistent_backup_id');
    }

    /**
     * Test listing backups
     */
    public function testListBackups(): void
    {
        // Initially no backups
        $this->assertEmpty($this->backupManager->listBackups());

        // Create test files
        $testFile1 = $this->testFilesDir . '/file1.php';
        $testFile2 = $this->testFilesDir . '/file2.php';

        file_put_contents($testFile1, 'content1');
        file_put_contents($testFile2, 'content2');

        // Create multiple backups
        $backupId1 = $this->backupManager->createBackup([$testFile1], 'Module1', 'First backup');
        sleep(1); // Ensure different timestamps
        $backupId2 = $this->backupManager->createBackup([$testFile2], 'Module2', 'Second backup');

        // List backups
        $backups = $this->backupManager->listBackups();

        $this->assertCount(2, $backups);
        $this->assertContainsOnlyInstancesOf(Backup::class, $backups);

        // Verify backups are sorted by creation date (newest first)
        $this->assertSame($backupId2, $backups[0]->id);
        $this->assertSame($backupId1, $backups[1]->id);

        // Verify metadata
        $this->assertSame('Module2', $backups[0]->moduleName);
        $this->assertSame('Second backup', $backups[0]->description);
        $this->assertSame('Module1', $backups[1]->moduleName);
        $this->assertSame('First backup', $backups[1]->description);
    }

    /**
     * Test deleting a backup
     */
    public function testDeleteBackup(): void
    {
        // Create test file and backup
        $testFile = $this->testFilesDir . '/delete_test.php';
        file_put_contents($testFile, 'content');

        $backupId = $this->backupManager->createBackup([$testFile], 'TestModule');

        // Verify backup exists
        $this->assertTrue($this->backupManager->backupExists($backupId));
        $this->assertCount(1, $this->backupManager->listBackups());

        // Delete backup
        $this->backupManager->deleteBackup($backupId);

        // Verify backup was deleted
        $this->assertFalse($this->backupManager->backupExists($backupId));
        $this->assertEmpty($this->backupManager->listBackups());
    }

    /**
     * Test deleting non-existent backup throws exception
     */
    public function testDeleteNonExistentBackupThrowsException(): void
    {
        $this->expectException(BackupException::class);
        $this->expectExceptionMessage('Backup not found');

        $this->backupManager->deleteBackup('nonexistent_backup_id');
    }

    /**
     * Test getting backup by ID
     */
    public function testGetBackup(): void
    {
        $testFile = $this->testFilesDir . '/test.php';
        file_put_contents($testFile, 'content');

        $backupId = $this->backupManager->createBackup(
            [$testFile],
            'TestModule',
            'Test description'
        );

        $backup = $this->backupManager->getBackup($backupId);

        $this->assertInstanceOf(Backup::class, $backup);
        $this->assertSame($backupId, $backup->id);
        $this->assertSame('TestModule', $backup->moduleName);
        $this->assertSame('Test description', $backup->description);
        $this->assertCount(1, $backup->files);
    }

    /**
     * Test getting non-existent backup throws exception
     */
    public function testGetNonExistentBackupThrowsException(): void
    {
        $this->expectException(BackupException::class);
        $this->expectExceptionMessage('Backup metadata not found');

        $this->backupManager->getBackup('nonexistent_backup_id');
    }

    /**
     * Test backup exists check
     */
    public function testBackupExists(): void
    {
        $testFile = $this->testFilesDir . '/test.php';
        file_put_contents($testFile, 'content');

        $backupId = $this->backupManager->createBackup([$testFile], 'TestModule');

        $this->assertTrue($this->backupManager->backupExists($backupId));
        $this->assertFalse($this->backupManager->backupExists('nonexistent_id'));
    }

    /**
     * Test backup includes checksums for integrity verification
     */
    public function testBackupIncludesChecksums(): void
    {
        $testFile = $this->testFilesDir . '/checksum_test.php';
        $content = '<?php echo "test content";';
        file_put_contents($testFile, $content);

        $expectedChecksum = md5_file($testFile);

        $backupId = $this->backupManager->createBackup([$testFile], 'TestModule');
        $backup = $this->backupManager->getBackup($backupId);

        $actualChecksum = $backup->getChecksum($testFile);

        $this->assertNotNull($actualChecksum);
        $this->assertSame($expectedChecksum, $actualChecksum);
    }

    /**
     * Test backup creation with empty file list
     */
    public function testCreateBackupWithEmptyFileList(): void
    {
        $backupId = $this->backupManager->createBackup([], 'EmptyModule');

        $this->assertTrue($this->backupManager->backupExists($backupId));

        $backup = $this->backupManager->getBackup($backupId);
        $this->assertEmpty($backup->files);
        $this->assertSame('EmptyModule', $backup->moduleName);
    }

    /**
     * Test backup preserves directory structure
     */
    public function testBackupPreservesDirectoryStructure(): void
    {
        // Create nested directory structure
        $subdir = $this->testFilesDir . '/level1/level2';
        mkdir($subdir, 0755, true);

        $testFile = $subdir . '/nested.php';
        file_put_contents($testFile, 'nested content');

        // Create backup
        $backupId = $this->backupManager->createBackup([$testFile], 'TestModule');

        // Delete original file
        unlink($testFile);
        $this->assertFileDoesNotExist($testFile);

        // Restore backup
        $this->backupManager->restoreBackup($backupId);

        // Verify file was restored with correct directory structure
        $this->assertFileExists($testFile);
        $this->assertSame('nested content', file_get_contents($testFile));
    }

    /**
     * Test backup ID format
     */
    public function testBackupIdFormat(): void
    {
        $testFile = $this->testFilesDir . '/test.php';
        file_put_contents($testFile, 'content');

        $backupId = $this->backupManager->createBackup([$testFile], 'TestModule');

        // Verify backup ID format: backup_YmdHis_uniqueid
        $this->assertMatchesRegularExpression('/^backup_\d{14}_[a-f0-9]+$/', $backupId);
    }

    /**
     * Test backup metadata includes creation timestamp
     */
    public function testBackupMetadataIncludesTimestamp(): void
    {
        $testFile = $this->testFilesDir . '/test.php';
        file_put_contents($testFile, 'content');

        $beforeBackup = new \DateTime();
        $backupId = $this->backupManager->createBackup([$testFile], 'TestModule');
        $afterBackup = new \DateTime();

        $backup = $this->backupManager->getBackup($backupId);

        $this->assertInstanceOf(\DateTime::class, $backup->createdAt);
        // Use timestamp comparison with tolerance for better reliability
        $this->assertGreaterThanOrEqual(
            $beforeBackup->getTimestamp() - 1,
            $backup->createdAt->getTimestamp()
        );
        $this->assertLessThanOrEqual(
            $afterBackup->getTimestamp() + 1,
            $backup->createdAt->getTimestamp()
        );
    }

    /**
     * Helper method to recursively delete a directory
     */
    private function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            $path = $dir . '/' . $file;

            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($dir);
    }
}

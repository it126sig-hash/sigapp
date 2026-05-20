<?php

namespace Tests\Libraries\Refactor\Models;

use App\Libraries\Refactor\Models\Backup;
use CodeIgniter\Test\CIUnitTestCase;
use DateTime;
use JsonException;

/**
 * Backup Model Test
 * 
 * Tests the Backup data model to ensure it correctly stores
 * and manages backup metadata.
 */
class BackupTest extends CIUnitTestCase
{
    /**
     * Test creating a Backup instance
     */
    public function testCreateBackup(): void
    {
        $createdAt = new DateTime('2024-01-15 10:30:00');
        $files = ['/path/to/file1.php', '/path/to/file2.php'];

        $backup = new Backup('backup_123', 'TestModule', $files, $createdAt);

        $this->assertSame('backup_123', $backup->id);
        $this->assertSame('TestModule', $backup->moduleName);
        $this->assertSame($files, $backup->files);
        $this->assertSame($createdAt, $backup->createdAt);
        $this->assertNull($backup->description);
        $this->assertEmpty($backup->checksums);
    }

    /**
     * Test creating Backup with default timestamp
     */
    public function testCreateBackupWithDefaultTimestamp(): void
    {
        $before = new DateTime();
        $backup = new Backup('backup_123', 'TestModule');
        $after = new DateTime();

        $this->assertInstanceOf(DateTime::class, $backup->createdAt);
        $this->assertGreaterThanOrEqual($before, $backup->createdAt);
        $this->assertLessThanOrEqual($after, $backup->createdAt);
    }

    /**
     * Test adding a file to backup
     */
    public function testAddFile(): void
    {
        $backup = new Backup('backup_123', 'TestModule');

        $this->assertEmpty($backup->files);

        $backup->addFile('/path/to/file.php');

        $this->assertCount(1, $backup->files);
        $this->assertContains('/path/to/file.php', $backup->files);
    }

    /**
     * Test adding file with checksum
     */
    public function testAddFileWithChecksum(): void
    {
        $backup = new Backup('backup_123', 'TestModule');

        $backup->addFile('/path/to/file.php', 'abc123def456');

        $this->assertTrue($backup->hasFile('/path/to/file.php'));
        $this->assertSame('abc123def456', $backup->getChecksum('/path/to/file.php'));
    }

    /**
     * Test adding duplicate file does not create duplicates
     */
    public function testAddDuplicateFileDoesNotCreateDuplicates(): void
    {
        $backup = new Backup('backup_123', 'TestModule');

        $backup->addFile('/path/to/file.php');
        $backup->addFile('/path/to/file.php');
        $backup->addFile('/path/to/file.php');

        $this->assertCount(1, $backup->files);
    }

    /**
     * Test getting checksum for non-existent file returns null
     */
    public function testGetChecksumForNonExistentFileReturnsNull(): void
    {
        $backup = new Backup('backup_123', 'TestModule');

        $this->assertNull($backup->getChecksum('/nonexistent/file.php'));
    }

    /**
     * Test getting file count
     */
    public function testGetFileCount(): void
    {
        $backup = new Backup('backup_123', 'TestModule');

        $this->assertSame(0, $backup->getFileCount());

        $backup->addFile('/path/to/file1.php');
        $this->assertSame(1, $backup->getFileCount());

        $backup->addFile('/path/to/file2.php');
        $this->assertSame(2, $backup->getFileCount());
    }

    /**
     * Test hasFile method
     */
    public function testHasFile(): void
    {
        $backup = new Backup('backup_123', 'TestModule');

        $this->assertFalse($backup->hasFile('/path/to/file.php'));

        $backup->addFile('/path/to/file.php');

        $this->assertTrue($backup->hasFile('/path/to/file.php'));
        $this->assertFalse($backup->hasFile('/other/file.php'));
    }

    /**
     * Test setting description
     */
    public function testSetDescription(): void
    {
        $backup = new Backup('backup_123', 'TestModule');

        $this->assertNull($backup->description);

        $backup->description = 'Test backup description';

        $this->assertSame('Test backup description', $backup->description);
    }

    /**
     * Test toArray returns correct structure
     */
    public function testToArrayReturnsCorrectStructure(): void
    {
        $createdAt = new DateTime('2024-01-15 10:30:00');
        $backup = new Backup('backup_123', 'TestModule', ['/file1.php'], $createdAt);
        $backup->description = 'Test description';
        $backup->addFile('/file1.php', 'checksum123');

        $array = $backup->toArray();

        $this->assertIsArray($array);
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('moduleName', $array);
        $this->assertArrayHasKey('files', $array);
        $this->assertArrayHasKey('createdAt', $array);
        $this->assertArrayHasKey('description', $array);
        $this->assertArrayHasKey('checksums', $array);

        $this->assertSame('backup_123', $array['id']);
        $this->assertSame('TestModule', $array['moduleName']);
        $this->assertSame(['/file1.php'], $array['files']);
        // Verify ISO 8601 format instead of exact match (timezone may vary)
        $this->assertMatchesRegularExpression('/^2024-01-15T10:30:00[+-]\d{2}:\d{2}$/', $array['createdAt']);
        $this->assertSame('Test description', $array['description']);
        $this->assertSame(['checksum123'], array_values($array['checksums']));
    }

    /**
     * Test toJson returns valid JSON
     */
    public function testToJsonReturnsValidJson(): void
    {
        $backup = new Backup('backup_123', 'TestModule');
        $backup->addFile('/file1.php', 'checksum123');

        $json = $backup->toJson();

        $this->assertIsString($json);
        $this->assertJson($json);

        $decoded = json_decode($json, true);
        $this->assertSame('backup_123', $decoded['id']);
        $this->assertSame('TestModule', $decoded['moduleName']);
    }

    /**
     * Test fromArray creates correct instance
     */
    public function testFromArrayCreatesCorrectInstance(): void
    {
        $data = [
            'id' => 'backup_456',
            'moduleName' => 'TestModule',
            'files' => ['/file1.php', '/file2.php'],
            'createdAt' => '2024-01-15T10:30:00+00:00',
            'description' => 'Test backup',
            'checksums' => [
                '/file1.php' => 'checksum1',
                '/file2.php' => 'checksum2',
            ],
        ];

        $backup = Backup::fromArray($data);

        $this->assertInstanceOf(Backup::class, $backup);
        $this->assertSame('backup_456', $backup->id);
        $this->assertSame('TestModule', $backup->moduleName);
        $this->assertCount(2, $backup->files);
        $this->assertSame('Test backup', $backup->description);
        $this->assertSame('checksum1', $backup->getChecksum('/file1.php'));
        $this->assertSame('checksum2', $backup->getChecksum('/file2.php'));
    }

    /**
     * Test fromArray handles missing optional fields
     */
    public function testFromArrayHandlesMissingOptionalFields(): void
    {
        $data = [
            'id' => 'backup_789',
            'moduleName' => 'TestModule',
            'createdAt' => '2024-01-15T10:30:00+00:00',
        ];

        $backup = Backup::fromArray($data);

        $this->assertEmpty($backup->files);
        $this->assertNull($backup->description);
        $this->assertEmpty($backup->checksums);
    }

    /**
     * Test fromJson creates correct instance
     */
    public function testFromJsonCreatesCorrectInstance(): void
    {
        $json = json_encode([
            'id' => 'backup_999',
            'moduleName' => 'JsonModule',
            'files' => ['/json/file.php'],
            'createdAt' => '2024-01-15T12:00:00+00:00',
            'description' => 'JSON backup',
            'checksums' => ['/json/file.php' => 'jsonchecksum'],
        ]);

        $backup = Backup::fromJson($json);

        $this->assertInstanceOf(Backup::class, $backup);
        $this->assertSame('backup_999', $backup->id);
        $this->assertSame('JsonModule', $backup->moduleName);
        $this->assertSame('JSON backup', $backup->description);
        $this->assertSame('jsonchecksum', $backup->getChecksum('/json/file.php'));
    }

    /**
     * Test fromJson throws exception for invalid JSON
     */
    public function testFromJsonThrowsExceptionForInvalidJson(): void
    {
        $this->expectException(JsonException::class);

        Backup::fromJson('invalid json {');
    }

    /**
     * Test round-trip serialization (toJson -> fromJson)
     */
    public function testRoundTripSerialization(): void
    {
        $original = new Backup('backup_roundtrip', 'RoundTripModule');
        $original->description = 'Round trip test';
        $original->addFile('/file1.php', 'checksum1');
        $original->addFile('/file2.php', 'checksum2');

        $json = $original->toJson();
        $restored = Backup::fromJson($json);

        $this->assertSame($original->id, $restored->id);
        $this->assertSame($original->moduleName, $restored->moduleName);
        $this->assertSame($original->description, $restored->description);
        $this->assertSame($original->files, $restored->files);
        $this->assertSame($original->checksums, $restored->checksums);
        // Compare timestamps with tolerance for microsecond precision loss
        $this->assertEqualsWithDelta(
            $original->createdAt->getTimestamp(),
            $restored->createdAt->getTimestamp(),
            1,
            'Timestamps should be equal within 1 second'
        );
    }

    /**
     * Test backup with multiple checksums
     */
    public function testBackupWithMultipleChecksums(): void
    {
        $backup = new Backup('backup_multi', 'MultiModule');

        $backup->addFile('/file1.php', 'checksum1');
        $backup->addFile('/file2.php', 'checksum2');
        $backup->addFile('/file3.php', 'checksum3');

        $this->assertCount(3, $backup->files);
        $this->assertCount(3, $backup->checksums);

        $this->assertSame('checksum1', $backup->getChecksum('/file1.php'));
        $this->assertSame('checksum2', $backup->getChecksum('/file2.php'));
        $this->assertSame('checksum3', $backup->getChecksum('/file3.php'));
    }

    /**
     * Test backup with files but no checksums
     */
    public function testBackupWithFilesButNoChecksums(): void
    {
        $backup = new Backup('backup_nochecksum', 'NoChecksumModule');

        $backup->addFile('/file1.php');
        $backup->addFile('/file2.php');

        $this->assertCount(2, $backup->files);
        $this->assertEmpty($backup->checksums);
        $this->assertNull($backup->getChecksum('/file1.php'));
        $this->assertNull($backup->getChecksum('/file2.php'));
    }

    /**
     * Test backup created at timestamp format
     */
    public function testBackupCreatedAtTimestampFormat(): void
    {
        $backup = new Backup('backup_timestamp', 'TimestampModule');

        $array = $backup->toArray();

        // Verify ISO 8601 format
        $this->assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2}$/',
            $array['createdAt']
        );
    }
}

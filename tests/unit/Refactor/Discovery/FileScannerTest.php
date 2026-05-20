<?php

namespace Tests\Unit\Refactor\Discovery;

use App\Libraries\Refactor\Discovery\FileScanner;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * FileScanner Unit Tests
 * 
 * Tests for the FileScanner utility that recursively scans directories for PHP files.
 * 
 * @package Tests\Unit\Refactor\Discovery
 */
class FileScannerTest extends CIUnitTestCase
{
    private FileScanner $scanner;
    private string $testDataDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->scanner = new FileScanner();
        $this->testDataDir = APPPATH . '../tests/_support/Refactor/TestData';
        
        // Create test data directory structure if it doesn't exist
        $this->createTestDataStructure();
        
        // Remove 'tests' from exclude dirs for testing purposes
        $this->scanner->setExcludeDirs(['vendor', 'writable', 'public', '.git', '.idea', 'node_modules']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        // Clean up test data
        $this->cleanupTestDataStructure();
    }

    /**
     * Test scanning a directory with various file structures
     */
    public function testScanFindsAllPhpFiles(): void
    {
        // Verify test data directory exists
        $this->assertDirectoryExists($this->testDataDir, 'Test data directory should exist');
        
        $files = $this->scanner->scan($this->testDataDir);
        
        $this->assertIsArray($files);
        $this->assertNotEmpty($files, 'Should find PHP files in test data directory: ' . $this->testDataDir);
        
        // All returned files should be PHP files
        foreach ($files as $file) {
            $this->assertStringEndsWith('.php', $file);
            $this->assertFileExists($file);
        }
    }

    /**
     * Test scanning with controller filter
     */
    public function testScanWithControllerFilter(): void
    {
        $this->scanner->setFilters([FileScanner::FILTER_CONTROLLERS]);
        $files = $this->scanner->scan($this->testDataDir);
        
        foreach ($files as $file) {
            $this->assertStringContainsString('Controllers', $file);
        }
    }

    /**
     * Test scanning with model filter
     */
    public function testScanWithModelFilter(): void
    {
        $this->scanner->setFilters([FileScanner::FILTER_MODELS]);
        $files = $this->scanner->scan($this->testDataDir);
        
        foreach ($files as $file) {
            $this->assertStringContainsString('Models', $file);
        }
    }

    /**
     * Test scanning with service filter
     */
    public function testScanWithServiceFilter(): void
    {
        $this->scanner->setFilters([FileScanner::FILTER_SERVICES]);
        $files = $this->scanner->scan($this->testDataDir);
        
        foreach ($files as $file) {
            $this->assertStringContainsString('Services', $file);
        }
    }

    /**
     * Test scanning with repository filter
     */
    public function testScanWithRepositoryFilter(): void
    {
        $this->scanner->setFilters([FileScanner::FILTER_REPOSITORIES]);
        $files = $this->scanner->scan($this->testDataDir);
        
        foreach ($files as $file) {
            $this->assertStringContainsString('Repositories', $file);
        }
    }

    /**
     * Test scanning with multiple filters
     */
    public function testScanWithMultipleFilters(): void
    {
        $this->scanner->setFilters([
            FileScanner::FILTER_CONTROLLERS,
            FileScanner::FILTER_MODELS
        ]);
        
        $files = $this->scanner->scan($this->testDataDir);
        
        foreach ($files as $file) {
            $hasController = str_contains($file, 'Controllers');
            $hasModel = str_contains($file, 'Models');
            $this->assertTrue($hasController || $hasModel);
        }
    }

    /**
     * Test scanning excludes vendor directory
     */
    public function testScanExcludesVendorDirectory(): void
    {
        // Scan from project root
        $files = $this->scanner->scan(ROOTPATH);
        
        foreach ($files as $file) {
            $this->assertStringNotContainsString('/vendor/', str_replace('\\', '/', $file));
        }
    }

    /**
     * Test scanning excludes tests directory
     */
    public function testScanExcludesTestsDirectory(): void
    {
        // Create a new scanner with default exclude dirs (including tests)
        $scanner = new FileScanner();
        
        // Scan from project root
        $files = $scanner->scan(ROOTPATH);
        
        foreach ($files as $file) {
            $this->assertStringNotContainsString('/tests/', str_replace('\\', '/', $file));
        }
    }

    /**
     * Test scanning non-existent directory throws exception
     */
    public function testScanNonExistentDirectoryThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Directory not found');
        
        $this->scanner->scan('/non/existent/directory');
    }

    /**
     * Test scanning multiple directories
     */
    public function testScanMultipleDirectories(): void
    {
        $directories = [
            APPPATH . 'Controllers',
            APPPATH . 'Models',
        ];
        
        $files = $this->scanner->scanMultiple($directories);
        
        $this->assertIsArray($files);
        $this->assertNotEmpty($files);
        
        // Should contain files from both directories
        $hasControllers = false;
        $hasModels = false;
        
        foreach ($files as $file) {
            if (str_contains($file, 'Controllers')) {
                $hasControllers = true;
            }
            if (str_contains($file, 'Models')) {
                $hasModels = true;
            }
        }
        
        $this->assertTrue($hasControllers);
        $this->assertTrue($hasModels);
    }

    /**
     * Test scanning multiple directories with non-existent directory
     */
    public function testScanMultipleDirectoriesSkipsNonExistent(): void
    {
        $directories = [
            APPPATH . 'Controllers',
            '/non/existent/directory',
            APPPATH . 'Models',
        ];
        
        // Should not throw exception, just skip non-existent
        $files = $this->scanner->scanMultiple($directories);
        
        $this->assertIsArray($files);
        $this->assertNotEmpty($files);
    }

    /**
     * Test count method
     */
    public function testCountReturnsCorrectNumber(): void
    {
        $count = $this->scanner->count($this->testDataDir);
        $files = $this->scanner->scan($this->testDataDir);
        
        $this->assertEquals(count($files), $count);
    }

    /**
     * Test hasPhpFiles method
     */
    public function testHasPhpFilesReturnsTrueForDirectoryWithPhpFiles(): void
    {
        $result = $this->scanner->hasPhpFiles(APPPATH . 'Controllers');
        $this->assertTrue($result);
    }

    /**
     * Test addFilter method
     */
    public function testAddFilterAddsFilter(): void
    {
        $this->scanner->addFilter(FileScanner::FILTER_CONTROLLERS);
        $this->scanner->addFilter(FileScanner::FILTER_MODELS);
        
        $files = $this->scanner->scan($this->testDataDir);
        
        foreach ($files as $file) {
            $hasController = str_contains($file, 'Controllers');
            $hasModel = str_contains($file, 'Models');
            $this->assertTrue($hasController || $hasModel);
        }
    }

    /**
     * Test addFilter doesn't add duplicate filters
     */
    public function testAddFilterDoesNotAddDuplicates(): void
    {
        $this->scanner->addFilter(FileScanner::FILTER_CONTROLLERS);
        $this->scanner->addFilter(FileScanner::FILTER_CONTROLLERS);
        
        // Should work without issues
        $files = $this->scanner->scan($this->testDataDir);
        $this->assertIsArray($files);
    }

    /**
     * Test setExcludeDirs method
     */
    public function testSetExcludeDirsExcludesSpecifiedDirectories(): void
    {
        $this->scanner->setExcludeDirs(['Controllers']);
        $files = $this->scanner->scan($this->testDataDir);
        
        foreach ($files as $file) {
            $this->assertStringNotContainsString('/Controllers/', str_replace('\\', '/', $file));
        }
    }

    /**
     * Create test data directory structure
     */
    private function createTestDataStructure(): void
    {
        // Clean up first if exists
        if (is_dir($this->testDataDir)) {
            $this->deleteDirectory($this->testDataDir);
        }

        // Create base directory
        mkdir($this->testDataDir, 0777, true);

        // Create Controllers directory
        $controllersDir = $this->testDataDir . '/Controllers';
        mkdir($controllersDir, 0777, true);
        file_put_contents($controllersDir . '/TestController.php', '<?php class TestController {}');

        // Create Models directory
        $modelsDir = $this->testDataDir . '/Models';
        mkdir($modelsDir, 0777, true);
        file_put_contents($modelsDir . '/TestModel.php', '<?php class TestModel {}');

        // Create Services directory
        $servicesDir = $this->testDataDir . '/Services';
        mkdir($servicesDir, 0777, true);
        file_put_contents($servicesDir . '/TestService.php', '<?php class TestService {}');

        // Create Repositories directory
        $repositoriesDir = $this->testDataDir . '/Repositories';
        mkdir($repositoriesDir, 0777, true);
        file_put_contents($repositoriesDir . '/TestRepository.php', '<?php class TestRepository {}');
    }

    /**
     * Clean up test data directory structure
     */
    private function cleanupTestDataStructure(): void
    {
        if (is_dir($this->testDataDir)) {
            $this->deleteDirectory($this->testDataDir);
        }
    }

    /**
     * Recursively delete directory
     */
    private function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
}

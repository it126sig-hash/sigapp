<?php

namespace Tests\Libraries\Refactor\Integration;

use App\Libraries\Refactor\Discovery\CodeParser;
use App\Libraries\Refactor\Discovery\FileScanner;
use App\Libraries\Refactor\Discovery\ModuleDiscovery;
use App\Libraries\Refactor\Exceptions\BackupException;
use App\Libraries\Refactor\Exceptions\RefactorException;
use App\Libraries\Refactor\Execution\BackupManager;
use App\Libraries\Refactor\Execution\ControllerRefactorer;
use App\Libraries\Refactor\Execution\ControllerSplitter;
use App\Libraries\Refactor\Execution\RefactorEngine;
use App\Libraries\Refactor\Execution\SecurityFixer;
use App\Libraries\Refactor\Generation\RepositoryGenerator;
use App\Libraries\Refactor\Generation\ServiceGenerator;
use App\Libraries\Refactor\Models\Module;
use App\Libraries\Refactor\Models\ModuleInventory;
use App\Libraries\Refactor\Models\RefactorOptions;
use App\Libraries\Refactor\Models\RefactorResult;
use App\Libraries\Refactor\Models\SplitResult;
use App\Libraries\Refactor\Security\SecurityScanner;
use CodeIgniter\Test\CIUnitTestCase;
use Psr\Log\LoggerInterface;

/**
 * Integration Test: Error Handling and Rollback Scenarios
 *
 * Tests error handling, rollback on failure, missing files,
 * and malformed PHP code handling.
 */
class ErrorHandlingTest extends CIUnitTestCase
{
    private string $testDir;
    private string $backupDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testDir = WRITEPATH . 'tests/integration_errors_' . uniqid();
        $this->backupDir = WRITEPATH . 'tests/integration_backups_' . uniqid();

        mkdir($this->testDir, 0755, true);
        mkdir($this->backupDir, 0755, true);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->deleteDirectory($this->testDir);
        $this->deleteDirectory($this->backupDir);
    }

    /**
     * Test rollback on code generation failure
     */
    public function testRollbackOnCodeGenerationFailure(): void
    {
        // Create a real file to backup
        $controllerPath = $this->createTestFile('TestController.php', '<?php class TestController {}');
        $modelPath = $this->createTestFile('TestModel.php', '<?php class TestModel {}');

        $originalContent = file_get_contents($controllerPath);

        // Use real BackupManager
        $backupManager = new BackupManager($this->backupDir);

        // Mock the repository generator to throw an exception
        $repositoryGenerator = $this->createMock(RepositoryGenerator::class);
        $repositoryGenerator->method('generate')
            ->willThrowException(new \RuntimeException('Code generation failed: template error'));

        $serviceGenerator = $this->createMock(ServiceGenerator::class);
        $controllerRefactorer = $this->createMock(ControllerRefactorer::class);
        $securityFixer = $this->createMock(SecurityFixer::class);
        $controllerSplitter = $this->createMock(ControllerSplitter::class);

        // Set up module discovery to return our test module
        $moduleDiscovery = $this->createMock(ModuleDiscovery::class);
        $module = new Module('Test', $controllerPath);
        $module->modelPaths = [$modelPath];
        $inventory = new ModuleInventory();
        $inventory->addModule($module);
        $moduleDiscovery->method('discover')->willReturn($inventory);

        $engine = new RefactorEngine(
            $backupManager,
            $repositoryGenerator,
            $serviceGenerator,
            $controllerRefactorer,
            $securityFixer,
            $controllerSplitter,
            $moduleDiscovery,
            null
        );

        $options = RefactorOptions::all();
        $options->runTests = false;
        $options->createGitCommits = false;

        $result = $engine->refactor('Test', $options);

        // Verify refactoring failed
        $this->assertFalse($result->success);
        $this->assertStringContainsString('Code generation failed', $result->errorMessage);

        // Verify original file is intact (rollback restored it)
        $this->assertFileExists($controllerPath);
        $this->assertEquals($originalContent, file_get_contents($controllerPath));
    }

    /**
     * Test handling of missing files gracefully
     */
    public function testHandlingMissingFilesGracefully(): void
    {
        // Create a fixture directory with only a controller (no model file)
        $controllersDir = $this->testDir . '/Controllers';
        $modelsDir = $this->testDir . '/Models';
        mkdir($controllersDir, 0755, true);
        mkdir($modelsDir, 0755, true);

        $controllerCode = <<<'PHP'
<?php

namespace App\Controllers;

use App\Models\NonExistentModel;

class GhostController extends \CodeIgniter\Controller
{
    protected $model;

    public function __construct()
    {
        $this->model = new NonExistentModel();
    }

    public function index()
    {
        return view('ghost/index');
    }
}
PHP;

        file_put_contents($controllersDir . '/GhostController.php', $controllerCode);

        // Discovery should handle missing model files gracefully
        $fileScanner = new FileScanner();
        $fileScanner->setExcludeDirs([]); // Don't exclude writable/ for tests
        $codeParser = new CodeParser();
        $discovery = new ModuleDiscovery($this->testDir, $fileScanner, $codeParser);

        // Should not throw - handles missing files gracefully
        $inventory = $discovery->discover();
        $this->assertInstanceOf(ModuleInventory::class, $inventory);
        $this->assertGreaterThan(0, $inventory->getModuleCount());
    }

    /**
     * Test handling of malformed PHP code
     */
    public function testHandlingMalformedPhpCode(): void
    {
        $controllersDir = $this->testDir . '/Controllers';
        mkdir($controllersDir, 0755, true);

        // Create a file with malformed PHP (missing closing brace)
        $malformedCode = <<<'PHP'
<?php

namespace App\Controllers;

class BrokenController extends \CodeIgniter\Controller
{
    public function index()
    {
        // Missing closing braces intentionally
        $data = ['key' => 'value';
    
PHP;

        file_put_contents($controllersDir . '/BrokenController.php', $malformedCode);

        // Discovery should handle malformed code without crashing
        $fileScanner = new FileScanner();
        $fileScanner->setExcludeDirs([]); // Don't exclude writable/ for tests
        $codeParser = new CodeParser();
        $discovery = new ModuleDiscovery($this->testDir, $fileScanner, $codeParser);

        // Should not throw an exception - handles gracefully
        $inventory = $discovery->discover();
        $this->assertInstanceOf(ModuleInventory::class, $inventory);

        // Security scanner should also handle malformed code
        $scanner = new SecurityScanner();
        if ($inventory->getModuleCount() > 0) {
            $modules = $inventory->modules;
            $module = reset($modules);
            // Should not crash on malformed code
            $report = $scanner->scanModule($module);
            $this->assertInstanceOf(\App\Libraries\Refactor\Models\SecurityReport::class, $report);
        } else {
            // If malformed code can't be parsed, that's also acceptable behavior
            $this->assertEquals(0, $inventory->getModuleCount());
        }
    }

    /**
     * Test BackupManager creates and restores correctly
     */
    public function testBackupManagerCreatesAndRestoresCorrectly(): void
    {
        $backupManager = new BackupManager($this->backupDir);

        // Create test files
        $file1 = $this->createTestFile('controller.php', '<?php class OriginalController { public function index() { return "original"; } }');
        $file2 = $this->createTestFile('model.php', '<?php class OriginalModel { protected $table = "test"; }');

        $originalContent1 = file_get_contents($file1);
        $originalContent2 = file_get_contents($file2);

        // Create backup
        $backupId = $backupManager->createBackup([$file1, $file2], 'IntegrationTest', 'Integration test backup');

        $this->assertNotEmpty($backupId);
        $this->assertTrue($backupManager->backupExists($backupId));

        // Modify files
        file_put_contents($file1, '<?php class ModifiedController {}');
        file_put_contents($file2, '<?php class ModifiedModel {}');

        // Verify files were modified
        $this->assertNotEquals($originalContent1, file_get_contents($file1));
        $this->assertNotEquals($originalContent2, file_get_contents($file2));

        // Restore backup
        $backup = $backupManager->restoreBackup($backupId);

        // Verify files were restored to original content
        $this->assertEquals($originalContent1, file_get_contents($file1));
        $this->assertEquals($originalContent2, file_get_contents($file2));

        // Verify backup metadata
        $this->assertEquals('IntegrationTest', $backup->moduleName);
        $this->assertEquals('Integration test backup', $backup->description);
    }

    /**
     * Test RefactorEngine handles exceptions and rolls back
     */
    public function testRefactorEngineHandlesExceptionsAndRollsBack(): void
    {
        $controllerPath = $this->createTestFile('ExcController.php', '<?php class ExcController { public function index() { return "hello"; } }');
        $modelPath = $this->createTestFile('ExcModel.php', '<?php class ExcModel { protected $table = "exc"; }');

        $originalControllerContent = file_get_contents($controllerPath);

        // Use real BackupManager
        $backupManager = new BackupManager($this->backupDir);

        // Repository generator succeeds
        $repositoryGenerator = $this->createMock(RepositoryGenerator::class);
        $repositoryGenerator->method('generate')->willReturn('<?php class ExcRepository {}');

        // Service generator throws exception
        $serviceGenerator = $this->createMock(ServiceGenerator::class);
        $serviceGenerator->method('generate')
            ->willThrowException(new \RuntimeException('Service generation exploded'));

        $controllerRefactorer = $this->createMock(ControllerRefactorer::class);
        $securityFixer = $this->createMock(SecurityFixer::class);
        $controllerSplitter = $this->createMock(ControllerSplitter::class);

        // Module discovery
        $moduleDiscovery = $this->createMock(ModuleDiscovery::class);
        $module = new Module('Exc', $controllerPath);
        $module->modelPaths = [$modelPath];
        $inventory = new ModuleInventory();
        $inventory->addModule($module);
        $moduleDiscovery->method('discover')->willReturn($inventory);

        $engine = new RefactorEngine(
            $backupManager,
            $repositoryGenerator,
            $serviceGenerator,
            $controllerRefactorer,
            $securityFixer,
            $controllerSplitter,
            $moduleDiscovery,
            null
        );

        $options = RefactorOptions::all();
        $options->runTests = false;
        $options->createGitCommits = false;

        $result = $engine->refactor('Exc', $options);

        // Verify failure
        $this->assertFalse($result->success);
        $this->assertStringContainsString('Service generation exploded', $result->errorMessage);
        $this->assertNotNull($result->backupId);

        // Verify original file is intact after rollback
        $this->assertFileExists($controllerPath);
        $this->assertEquals($originalControllerContent, file_get_contents($controllerPath));
    }

    /**
     * Test handling of non-existent module in RefactorEngine
     */
    public function testRefactorEngineHandlesNonExistentModule(): void
    {
        $backupManager = new BackupManager($this->backupDir);
        $repositoryGenerator = $this->createMock(RepositoryGenerator::class);
        $serviceGenerator = $this->createMock(ServiceGenerator::class);
        $controllerRefactorer = $this->createMock(ControllerRefactorer::class);
        $securityFixer = $this->createMock(SecurityFixer::class);
        $controllerSplitter = $this->createMock(ControllerSplitter::class);

        $moduleDiscovery = $this->createMock(ModuleDiscovery::class);
        $inventory = new ModuleInventory();
        $moduleDiscovery->method('discover')->willReturn($inventory);

        $engine = new RefactorEngine(
            $backupManager,
            $repositoryGenerator,
            $serviceGenerator,
            $controllerRefactorer,
            $securityFixer,
            $controllerSplitter,
            $moduleDiscovery,
            null
        );

        $options = RefactorOptions::all();
        $result = $engine->refactor('NonExistentModule', $options);

        $this->assertFalse($result->success);
        $this->assertStringContainsString('Module not found', $result->errorMessage);
    }

    /**
     * Test BackupManager handles non-existent backup restore gracefully
     */
    public function testBackupManagerHandlesNonExistentRestore(): void
    {
        $backupManager = new BackupManager($this->backupDir);

        $this->expectException(BackupException::class);
        $backupManager->restoreBackup('nonexistent_backup_12345');
    }

    /**
     * Test discovery handles empty directories
     */
    public function testDiscoveryHandlesEmptyDirectories(): void
    {
        // Create empty Controllers and Models directories
        $controllersDir = $this->testDir . '/Controllers';
        $modelsDir = $this->testDir . '/Models';
        mkdir($controllersDir, 0755, true);
        mkdir($modelsDir, 0755, true);

        $fileScanner = new FileScanner();
        $fileScanner->setExcludeDirs([]); // Don't exclude writable/ for tests
        $codeParser = new CodeParser();
        $discovery = new ModuleDiscovery($this->testDir, $fileScanner, $codeParser);

        $inventory = $discovery->discover();

        $this->assertInstanceOf(ModuleInventory::class, $inventory);
        $this->assertEquals(0, $inventory->getModuleCount());
    }

    // ========================================================================
    // Helper Methods
    // ========================================================================

    /**
     * Create a test file in the test directory
     */
    private function createTestFile(string $filename, string $content): string
    {
        $path = $this->testDir . DIRECTORY_SEPARATOR . $filename;
        file_put_contents($path, $content);
        return $path;
    }

    /**
     * Recursively delete a directory
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

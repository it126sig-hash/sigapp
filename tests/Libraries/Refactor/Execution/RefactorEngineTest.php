<?php

namespace Tests\Libraries\Refactor\Execution;

use App\Libraries\Refactor\Discovery\ModuleDiscovery;
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
use CodeIgniter\Test\CIUnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

/**
 * RefactorEngine Test
 *
 * Tests the RefactorEngine orchestration class to ensure it correctly
 * coordinates backup, step execution, error handling, and rollback.
 */
class RefactorEngineTest extends CIUnitTestCase
{
    private RefactorEngine $engine;

    /** @var BackupManager&MockObject */
    private $backupManager;

    /** @var RepositoryGenerator&MockObject */
    private $repositoryGenerator;

    /** @var ServiceGenerator&MockObject */
    private $serviceGenerator;

    /** @var ControllerRefactorer&MockObject */
    private $controllerRefactorer;

    /** @var SecurityFixer&MockObject */
    private $securityFixer;

    /** @var ControllerSplitter&MockObject */
    private $controllerSplitter;

    /** @var ModuleDiscovery&MockObject */
    private $moduleDiscovery;

    /** @var LoggerInterface&MockObject */
    private $logger;

    private string $testDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testDir = WRITEPATH . 'tests/refactor_engine_' . uniqid();
        mkdir($this->testDir, 0755, true);

        // Create mocks
        $this->backupManager = $this->createMock(BackupManager::class);
        $this->repositoryGenerator = $this->createMock(RepositoryGenerator::class);
        $this->serviceGenerator = $this->createMock(ServiceGenerator::class);
        $this->controllerRefactorer = $this->createMock(ControllerRefactorer::class);
        $this->securityFixer = $this->createMock(SecurityFixer::class);
        $this->controllerSplitter = $this->createMock(ControllerSplitter::class);
        $this->moduleDiscovery = $this->createMock(ModuleDiscovery::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->engine = new RefactorEngine(
            $this->backupManager,
            $this->repositoryGenerator,
            $this->serviceGenerator,
            $this->controllerRefactorer,
            $this->securityFixer,
            $this->controllerSplitter,
            $this->moduleDiscovery,
            $this->logger
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->deleteDirectory($this->testDir);
    }

    /**
     * Test successful refactoring with all options enabled
     */
    public function testRefactorSuccessWithAllOptions(): void
    {
        $controllerPath = $this->createTestFile('TestController.php', '<?php class TestController {}');
        $modelPath = $this->createTestFile('TestModel.php', '<?php class TestModel {}');

        $module = new Module('Test', $controllerPath);
        $module->modelPaths = [$modelPath];

        $inventory = new ModuleInventory();
        $inventory->addModule($module);

        $this->moduleDiscovery->method('discover')->willReturn($inventory);

        $this->backupManager->method('createBackup')->willReturn('backup_123');

        $this->repositoryGenerator->method('generate')->willReturn('<?php class TestRepository {}');

        $this->serviceGenerator->method('generate')->willReturn('<?php class TestService {}');

        $controllerResult = RefactorResult::success();
        $controllerResult->filesModified = [$controllerPath];
        $this->controllerRefactorer->method('refactor')->willReturn($controllerResult);

        // SecurityFixer methods return the same code (no changes)
        $this->securityFixer->method('addInputValidation')->willReturnArgument(0);
        $this->securityFixer->method('addOutputEscaping')->willReturnArgument(0);
        $this->securityFixer->method('addCSRFProtection')->willReturnArgument(0);
        $this->securityFixer->method('addAuthenticationCheck')->willReturnArgument(0);
        $this->securityFixer->method('replaceRawQueryWithQueryBuilder')->willReturnArgument(0);

        $splitResult = new SplitResult(['wasSplit' => false]);
        $this->controllerSplitter->method('split')->willReturn($splitResult);

        $options = RefactorOptions::all();
        $options->runTests = false;

        $result = $this->engine->refactor('Test', $options);

        $this->assertTrue($result->success);
        $this->assertSame('backup_123', $result->backupId);
        $this->assertContains('Repository Generation', $result->stepsCompleted);
        $this->assertContains('Service Generation', $result->stepsCompleted);
        $this->assertContains('Controller Refactoring', $result->stepsCompleted);
        $this->assertContains('Security Fixes', $result->stepsCompleted);
        $this->assertContains('Web/API Splitting', $result->stepsCompleted);
    }

    /**
     * Test refactoring with minimal options
     */
    public function testRefactorWithMinimalOptions(): void
    {
        $controllerPath = $this->createTestFile('MinController.php', '<?php class MinController {}');

        $module = new Module('Min', $controllerPath);

        $inventory = new ModuleInventory();
        $inventory->addModule($module);

        $this->moduleDiscovery->method('discover')->willReturn($inventory);
        $this->backupManager->method('createBackup')->willReturn('backup_min');

        $this->repositoryGenerator->method('generate')->willReturn('<?php class MinRepository {}');
        $this->serviceGenerator->method('generate')->willReturn('<?php class MinService {}');

        $controllerResult = RefactorResult::success();
        $controllerResult->filesModified = [$controllerPath];
        $this->controllerRefactorer->method('refactor')->willReturn($controllerResult);

        $options = RefactorOptions::minimal();

        $result = $this->engine->refactor('Min', $options);

        $this->assertTrue($result->success);
        $this->assertContains('Repository Generation', $result->stepsCompleted);
        $this->assertContains('Service Generation', $result->stepsCompleted);
        $this->assertContains('Controller Refactoring', $result->stepsCompleted);
        // Security and Web/API splitting should NOT be in completed steps
        $this->assertNotContains('Security Fixes', $result->stepsCompleted);
        $this->assertNotContains('Web/API Splitting', $result->stepsCompleted);
    }

    /**
     * Test refactoring returns failure when module not found
     */
    public function testRefactorReturnsFailureWhenModuleNotFound(): void
    {
        $inventory = new ModuleInventory();
        $this->moduleDiscovery->method('discover')->willReturn($inventory);

        $options = RefactorOptions::all();

        $result = $this->engine->refactor('NonExistent', $options);

        $this->assertFalse($result->success);
        $this->assertStringContainsString('Module not found', $result->errorMessage);
    }

    /**
     * Test rollback is triggered on step failure
     */
    public function testRollbackOnStepFailure(): void
    {
        $controllerPath = $this->createTestFile('FailController.php', '<?php class FailController {}');
        $modelPath = $this->createTestFile('FailModel.php', '<?php class FailModel {}');

        $module = new Module('Fail', $controllerPath);
        $module->modelPaths = [$modelPath];

        $inventory = new ModuleInventory();
        $inventory->addModule($module);

        $this->moduleDiscovery->method('discover')->willReturn($inventory);
        $this->backupManager->method('createBackup')->willReturn('backup_fail');

        // Repository generation throws an exception
        $this->repositoryGenerator->method('generate')
            ->willThrowException(new \RuntimeException('Generation failed'));

        // Expect rollback to be called
        $this->backupManager->expects($this->once())
            ->method('restoreBackup')
            ->with('backup_fail');

        $options = RefactorOptions::all();

        $result = $this->engine->refactor('Fail', $options);

        $this->assertFalse($result->success);
        $this->assertStringContainsString('Generation failed', $result->errorMessage);
        $this->assertSame('backup_fail', $result->backupId);
    }

    /**
     * Test executeStep records completed steps
     */
    public function testExecuteStepRecordsCompletedSteps(): void
    {
        // Use reflection to set currentResult
        $reflection = new \ReflectionClass($this->engine);
        $prop = $reflection->getProperty('currentResult');
        $prop->setAccessible(true);
        $prop->setValue($this->engine, RefactorResult::success());

        $this->engine->executeStep('Test Step', function () {
            return ['created' => ['/path/to/file.php'], 'modified' => []];
        });

        $result = $prop->getValue($this->engine);
        $this->assertContains('Test Step', $result->stepsCompleted);
        $this->assertContains('/path/to/file.php', $result->filesCreated);
    }

    /**
     * Test executeStep throws RefactorException on failure
     */
    public function testExecuteStepThrowsOnFailure(): void
    {
        $reflection = new \ReflectionClass($this->engine);
        $prop = $reflection->getProperty('currentResult');
        $prop->setAccessible(true);
        $prop->setValue($this->engine, RefactorResult::success());

        $this->expectException(RefactorException::class);
        $this->expectExceptionMessage("Step 'Failing Step' failed: Something went wrong");

        $this->engine->executeStep('Failing Step', function () {
            throw new \RuntimeException('Something went wrong');
        });
    }

    /**
     * Test createBackup delegates to BackupManager
     */
    public function testCreateBackupDelegatesToBackupManager(): void
    {
        $files = ['/path/to/file1.php', '/path/to/file2.php'];

        $this->backupManager->expects($this->once())
            ->method('createBackup')
            ->with($files, 'refactor_engine', 'Pre-refactoring backup')
            ->willReturn('backup_abc');

        $backupId = $this->engine->createBackup($files);

        $this->assertSame('backup_abc', $backupId);
    }

    /**
     * Test createBackup throws RefactorException on failure
     */
    public function testCreateBackupThrowsOnFailure(): void
    {
        $this->backupManager->method('createBackup')
            ->willThrowException(new \RuntimeException('Disk full'));

        $this->expectException(RefactorException::class);
        $this->expectExceptionMessage('Failed to create backup: Disk full');

        $this->engine->createBackup(['/some/file.php']);
    }

    /**
     * Test rollback delegates to BackupManager
     */
    public function testRollbackDelegatesToBackupManager(): void
    {
        $this->backupManager->expects($this->once())
            ->method('restoreBackup')
            ->with('backup_xyz');

        $this->engine->rollback('backup_xyz');
    }

    /**
     * Test rollback does not throw on failure
     */
    public function testRollbackDoesNotThrowOnFailure(): void
    {
        $this->backupManager->method('restoreBackup')
            ->willThrowException(new \RuntimeException('Restore failed'));

        // Should not throw - rollback failure is logged but not propagated
        $this->engine->rollback('backup_broken');

        // If we reach here, the test passes (no exception thrown)
        $this->assertTrue(true);
    }

    /**
     * Test logger receives messages during refactoring
     */
    public function testLoggerReceivesMessages(): void
    {
        $controllerPath = $this->createTestFile('LogController.php', '<?php class LogController {}');

        $module = new Module('Log', $controllerPath);

        $inventory = new ModuleInventory();
        $inventory->addModule($module);

        $this->moduleDiscovery->method('discover')->willReturn($inventory);
        $this->backupManager->method('createBackup')->willReturn('backup_log');

        $this->repositoryGenerator->method('generate')->willReturn('');
        $this->serviceGenerator->method('generate')->willReturn('');

        $controllerResult = RefactorResult::success();
        $this->controllerRefactorer->method('refactor')->willReturn($controllerResult);

        $this->securityFixer->method('addInputValidation')->willReturnArgument(0);
        $this->securityFixer->method('addOutputEscaping')->willReturnArgument(0);
        $this->securityFixer->method('addCSRFProtection')->willReturnArgument(0);
        $this->securityFixer->method('addAuthenticationCheck')->willReturnArgument(0);
        $this->securityFixer->method('replaceRawQueryWithQueryBuilder')->willReturnArgument(0);

        $splitResult = new SplitResult(['wasSplit' => false]);
        $this->controllerSplitter->method('split')->willReturn($splitResult);

        // Expect logger to be called multiple times
        $this->logger->expects($this->atLeast(3))
            ->method('log');

        $options = RefactorOptions::all();
        $options->runTests = false;

        $this->engine->refactor('Log', $options);
    }

    /**
     * Test partial completion is recorded on failure
     */
    public function testPartialCompletionRecordedOnFailure(): void
    {
        $controllerPath = $this->createTestFile('PartialController.php', '<?php class PartialController {}');
        $modelPath = $this->createTestFile('PartialModel.php', '<?php class PartialModel {}');

        $module = new Module('Partial', $controllerPath);
        $module->modelPaths = [$modelPath];

        $inventory = new ModuleInventory();
        $inventory->addModule($module);

        $this->moduleDiscovery->method('discover')->willReturn($inventory);
        $this->backupManager->method('createBackup')->willReturn('backup_partial');

        // Repository generation succeeds
        $this->repositoryGenerator->method('generate')->willReturn('<?php class PartialRepository {}');

        // Service generation fails
        $this->serviceGenerator->method('generate')
            ->willThrowException(new \RuntimeException('Service generation failed'));

        $backupMock = $this->createMock(\App\Libraries\Refactor\Models\Backup::class);
        $this->backupManager->method('restoreBackup')->willReturn($backupMock);

        $options = RefactorOptions::all();
        $options->runTests = false;

        $result = $this->engine->refactor('Partial', $options);

        $this->assertFalse($result->success);
        // Repository Generation should be in completed steps (it succeeded before failure)
        $this->assertContains('Repository Generation', $result->stepsCompleted);
        // Service Generation should NOT be in completed steps
        $this->assertNotContains('Service Generation', $result->stepsCompleted);
    }

    /**
     * Test security-only options skip other steps
     */
    public function testSecurityOnlyOptionsSkipOtherSteps(): void
    {
        $controllerPath = $this->createTestFile('SecController.php', '<?php class SecController {}');

        $module = new Module('Sec', $controllerPath);

        $inventory = new ModuleInventory();
        $inventory->addModule($module);

        $this->moduleDiscovery->method('discover')->willReturn($inventory);
        $this->backupManager->method('createBackup')->willReturn('backup_sec');

        // SecurityFixer methods return the same code (no changes)
        $this->securityFixer->method('addInputValidation')->willReturnArgument(0);
        $this->securityFixer->method('addOutputEscaping')->willReturnArgument(0);
        $this->securityFixer->method('addCSRFProtection')->willReturnArgument(0);
        $this->securityFixer->method('addAuthenticationCheck')->willReturnArgument(0);
        $this->securityFixer->method('replaceRawQueryWithQueryBuilder')->willReturnArgument(0);

        $options = RefactorOptions::securityOnly();

        $result = $this->engine->refactor('Sec', $options);

        $this->assertTrue($result->success);
        $this->assertContains('Security Fixes', $result->stepsCompleted);
        $this->assertNotContains('Repository Generation', $result->stepsCompleted);
        $this->assertNotContains('Service Generation', $result->stepsCompleted);
        $this->assertNotContains('Controller Refactoring', $result->stepsCompleted);
        $this->assertNotContains('Web/API Splitting', $result->stepsCompleted);
    }

    /**
     * Test engine works without logger (null logger)
     */
    public function testEngineWorksWithoutLogger(): void
    {
        $engineNoLogger = new RefactorEngine(
            $this->backupManager,
            $this->repositoryGenerator,
            $this->serviceGenerator,
            $this->controllerRefactorer,
            $this->securityFixer,
            $this->controllerSplitter,
            $this->moduleDiscovery,
            null
        );

        $inventory = new ModuleInventory();
        $this->moduleDiscovery->method('discover')->willReturn($inventory);

        $options = RefactorOptions::all();
        $result = $engineNoLogger->refactor('NonExistent', $options);

        $this->assertFalse($result->success);
        $this->assertStringContainsString('Module not found', $result->errorMessage);
    }

    /**
     * Create a test file in the test directory
     *
     * @param string $filename File name
     * @param string $content File content
     * @return string Full path to created file
     */
    private function createTestFile(string $filename, string $content): string
    {
        $path = $this->testDir . DIRECTORY_SEPARATOR . $filename;
        file_put_contents($path, $content);
        return $path;
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

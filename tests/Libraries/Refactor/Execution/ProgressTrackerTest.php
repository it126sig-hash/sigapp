<?php

namespace Tests\Libraries\Refactor\Execution;

use App\Libraries\Refactor\Execution\ProgressTracker;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * ProgressTracker Test
 *
 * Tests the ProgressTracker class to ensure it correctly tracks module
 * refactoring progress, calculates overall progress, and generates
 * filtered progress reports.
 */
class ProgressTrackerTest extends CIUnitTestCase
{
    private ProgressTracker $tracker;
    private string $testProgressFile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testProgressFile = WRITEPATH . 'tests/progress_' . uniqid() . '.json';
        $dir = dirname($this->testProgressFile);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $this->tracker = new ProgressTracker($this->testProgressFile);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if (file_exists($this->testProgressFile)) {
            unlink($this->testProgressFile);
        }
    }

    /**
     * Test that a new tracker starts with no modules
     */
    public function testNewTrackerHasNoModules(): void
    {
        $this->assertEmpty($this->tracker->getTrackedModules());
        $this->assertSame(0.0, $this->tracker->calculateOverallProgress());
    }

    /**
     * Test recording an audit creates the module entry with AUDITED status
     */
    public function testRecordAuditSetsAuditedStatus(): void
    {
        $this->tracker->recordAudit('Transaksi');

        $this->assertSame(ProgressTracker::STATUS_AUDITED, $this->tracker->getModuleStatus('Transaksi'));
        $this->assertTrue($this->tracker->isTracked('Transaksi'));

        $data = $this->tracker->getModuleData('Transaksi');
        $this->assertNotNull($data['auditedAt']);
    }

    /**
     * Test recording a successful refactor sets COMPLETED status
     */
    public function testRecordRefactorSuccessSetsCompletedStatus(): void
    {
        $this->tracker->recordAudit('Keuangan');
        $this->tracker->recordRefactor('Keuangan', true);

        $this->assertSame(ProgressTracker::STATUS_COMPLETED, $this->tracker->getModuleStatus('Keuangan'));

        $data = $this->tracker->getModuleData('Keuangan');
        $this->assertNotNull($data['refactoredAt']);
    }

    /**
     * Test recording a failed refactor sets FAILED status
     */
    public function testRecordRefactorFailureSetsFailedStatus(): void
    {
        $this->tracker->recordAudit('Kavling');
        $this->tracker->recordRefactor('Kavling', false);

        $this->assertSame(ProgressTracker::STATUS_FAILED, $this->tracker->getModuleStatus('Kavling'));

        $data = $this->tracker->getModuleData('Kavling');
        $this->assertNotNull($data['failedAt']);
    }

    /**
     * Test calculating overall progress with mixed statuses
     */
    public function testCalculateOverallProgress(): void
    {
        $this->tracker->recordAudit('ModuleA');
        $this->tracker->recordRefactor('ModuleA', true);

        $this->tracker->recordAudit('ModuleB');

        $this->tracker->recordAudit('ModuleC');
        $this->tracker->recordRefactor('ModuleC', true);

        $this->tracker->recordAudit('ModuleD');
        $this->tracker->recordRefactor('ModuleD', false);

        // 2 out of 4 completed = 50%
        $this->assertSame(50.0, $this->tracker->calculateOverallProgress());
    }

    /**
     * Test progress is 0 when no modules are tracked
     */
    public function testCalculateOverallProgressWithNoModules(): void
    {
        $this->assertSame(0.0, $this->tracker->calculateOverallProgress());
    }

    /**
     * Test progress is 100 when all modules are completed
     */
    public function testCalculateOverallProgressAllCompleted(): void
    {
        $this->tracker->recordRefactor('ModuleA', true);
        $this->tracker->recordRefactor('ModuleB', true);

        $this->assertSame(100.0, $this->tracker->calculateOverallProgress());
    }

    /**
     * Test generating a progress report without filters
     */
    public function testGenerateProgressReportNoFilters(): void
    {
        $this->tracker->recordAudit('ModuleA');
        $this->tracker->recordRefactor('ModuleA', true);
        $this->tracker->recordAudit('ModuleB');

        $report = $this->tracker->generateProgressReport();

        $this->assertSame(50.0, $report['overallProgress']);
        $this->assertSame(2, $report['totalModules']);
        $this->assertSame(1, $report['completedModules']);
        $this->assertSame(1, $report['auditedModules']);
        $this->assertSame(0, $report['failedModules']);
        $this->assertSame(0, $report['notStartedModules']);
        $this->assertSame(0, $report['inProgressModules']);
        $this->assertArrayHasKey('generatedAt', $report);
        $this->assertCount(2, $report['modules']);
    }

    /**
     * Test generating a progress report with status filter
     */
    public function testGenerateProgressReportWithStatusFilter(): void
    {
        $this->tracker->recordRefactor('ModuleA', true);
        $this->tracker->recordAudit('ModuleB');
        $this->tracker->recordRefactor('ModuleC', true);

        $report = $this->tracker->generateProgressReport(['status' => ProgressTracker::STATUS_COMPLETED]);

        // Only completed modules in the filtered list
        $this->assertCount(2, $report['modules']);
        $this->assertArrayHasKey('ModuleA', $report['modules']);
        $this->assertArrayHasKey('ModuleC', $report['modules']);
        $this->assertArrayNotHasKey('ModuleB', $report['modules']);

        // Overall stats still reflect all modules
        $this->assertSame(3, $report['totalModules']);
    }

    /**
     * Test getModuleStatus returns NOT_STARTED for unknown modules
     */
    public function testGetModuleStatusReturnsNotStartedForUnknown(): void
    {
        $this->assertSame(ProgressTracker::STATUS_NOT_STARTED, $this->tracker->getModuleStatus('NonExistent'));
    }

    /**
     * Test getModulesByStatus returns correct modules
     */
    public function testGetModulesByStatus(): void
    {
        $this->tracker->recordRefactor('ModuleA', true);
        $this->tracker->recordRefactor('ModuleB', true);
        $this->tracker->recordAudit('ModuleC');
        $this->tracker->recordRefactor('ModuleD', false);

        $completed = $this->tracker->getModulesByStatus(ProgressTracker::STATUS_COMPLETED);
        $this->assertCount(2, $completed);
        $this->assertContains('ModuleA', $completed);
        $this->assertContains('ModuleB', $completed);

        $audited = $this->tracker->getModulesByStatus(ProgressTracker::STATUS_AUDITED);
        $this->assertCount(1, $audited);
        $this->assertContains('ModuleC', $audited);

        $failed = $this->tracker->getModulesByStatus(ProgressTracker::STATUS_FAILED);
        $this->assertCount(1, $failed);
        $this->assertContains('ModuleD', $failed);
    }

    /**
     * Test resetting a module's status
     */
    public function testResetModuleStatus(): void
    {
        $this->tracker->recordAudit('ModuleA');
        $this->tracker->recordRefactor('ModuleA', true);

        $this->assertSame(ProgressTracker::STATUS_COMPLETED, $this->tracker->getModuleStatus('ModuleA'));

        $this->tracker->reset('ModuleA');

        $this->assertSame(ProgressTracker::STATUS_NOT_STARTED, $this->tracker->getModuleStatus('ModuleA'));

        $data = $this->tracker->getModuleData('ModuleA');
        $this->assertNull($data['auditedAt']);
        $this->assertNull($data['refactoredAt']);
        $this->assertSame(0, $data['vulnerabilitiesFixed']);
    }

    /**
     * Test resetting a non-existent module does nothing
     */
    public function testResetNonExistentModuleDoesNothing(): void
    {
        $this->tracker->reset('NonExistent');

        $this->assertFalse($this->tracker->isTracked('NonExistent'));
    }

    /**
     * Test marking a module as in progress
     */
    public function testMarkInProgress(): void
    {
        $this->tracker->recordAudit('ModuleA');
        $this->tracker->markInProgress('ModuleA');

        $this->assertSame(ProgressTracker::STATUS_IN_PROGRESS, $this->tracker->getModuleStatus('ModuleA'));
    }

    /**
     * Test recording vulnerabilities fixed
     */
    public function testRecordVulnerabilitiesFixed(): void
    {
        $this->tracker->recordAudit('ModuleA');
        $this->tracker->recordVulnerabilitiesFixed('ModuleA', 5);

        $data = $this->tracker->getModuleData('ModuleA');
        $this->assertSame(5, $data['vulnerabilitiesFixed']);
    }

    /**
     * Test recording backup ID
     */
    public function testRecordBackupId(): void
    {
        $this->tracker->recordAudit('ModuleA');
        $this->tracker->recordBackupId('ModuleA', 'backup_20240115_112000');

        $data = $this->tracker->getModuleData('ModuleA');
        $this->assertSame('backup_20240115_112000', $data['backupId']);
    }

    /**
     * Test data persistence - data survives reload
     */
    public function testDataPersistence(): void
    {
        $this->tracker->recordAudit('ModuleA');
        $this->tracker->recordRefactor('ModuleA', true);
        $this->tracker->recordAudit('ModuleB');

        // Create a new tracker instance pointing to the same file
        $newTracker = new ProgressTracker($this->testProgressFile);

        $this->assertSame(ProgressTracker::STATUS_COMPLETED, $newTracker->getModuleStatus('ModuleA'));
        $this->assertSame(ProgressTracker::STATUS_AUDITED, $newTracker->getModuleStatus('ModuleB'));
        $this->assertSame(50.0, $newTracker->calculateOverallProgress());
    }

    /**
     * Test that progress file is created automatically
     */
    public function testProgressFileCreatedAutomatically(): void
    {
        $this->tracker->recordAudit('TestModule');

        $this->assertFileExists($this->testProgressFile);

        $content = file_get_contents($this->testProgressFile);
        $decoded = json_decode($content, true);

        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('modules', $decoded);
        $this->assertArrayHasKey('TestModule', $decoded['modules']);
    }

    /**
     * Test handling corrupted JSON file gracefully
     */
    public function testHandlesCorruptedJsonFile(): void
    {
        // Write invalid JSON to the file
        file_put_contents($this->testProgressFile, 'not valid json {{{');

        // Should not throw, should initialize with empty data
        $tracker = new ProgressTracker($this->testProgressFile);

        $this->assertEmpty($tracker->getTrackedModules());
        $this->assertSame(0.0, $tracker->calculateOverallProgress());
    }

    /**
     * Test getModuleData returns null for untracked module
     */
    public function testGetModuleDataReturnsNullForUntracked(): void
    {
        $this->assertNull($this->tracker->getModuleData('NonExistent'));
    }

    /**
     * Test getTrackedModules returns all module names
     */
    public function testGetTrackedModules(): void
    {
        $this->tracker->recordAudit('Alpha');
        $this->tracker->recordAudit('Beta');
        $this->tracker->recordAudit('Gamma');

        $modules = $this->tracker->getTrackedModules();

        $this->assertCount(3, $modules);
        $this->assertContains('Alpha', $modules);
        $this->assertContains('Beta', $modules);
        $this->assertContains('Gamma', $modules);
    }
}

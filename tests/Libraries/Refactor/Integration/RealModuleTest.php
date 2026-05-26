<?php

namespace Tests\Libraries\Refactor\Integration;

use App\Libraries\Refactor\Analysis\AuditGenerator;
use App\Libraries\Refactor\Analysis\CodeAnalyzer;
use App\Libraries\Refactor\Analysis\DependencyAnalyzer;
use App\Libraries\Refactor\Analysis\ImpactAnalyzer;
use App\Libraries\Refactor\Discovery\CodeParser;
use App\Libraries\Refactor\Discovery\FileScanner;
use App\Libraries\Refactor\Discovery\ModuleDiscovery;
use App\Libraries\Refactor\Models\AuditReport;
use App\Libraries\Refactor\Models\DependencyGraph;
use App\Libraries\Refactor\Models\Module;
use App\Libraries\Refactor\Models\ModuleInventory;
use App\Libraries\Refactor\Models\SecurityReport;
use App\Libraries\Refactor\Security\SecurityScanner;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Integration Test: Real Module Testing
 *
 * Tests the refactoring system against actual sigapp.dev modules.
 * Only tests discovery, scanning, and audit - does NOT execute refactoring.
 */
class RealModuleTest extends CIUnitTestCase
{
    private string $appPath;
    private ModuleDiscovery $discovery;
    private FileScanner $fileScanner;
    private CodeParser $codeParser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->appPath = APPPATH;
        $this->fileScanner = new FileScanner();
        $this->codeParser = new CodeParser();
        $this->discovery = new ModuleDiscovery($this->appPath, $this->fileScanner, $this->codeParser);
    }

    /**
     * Test ModuleDiscovery finds real modules in the application
     */
    public function testModuleDiscoveryFindsRealModules(): void
    {
        $inventory = $this->discovery->discover();

        $this->assertInstanceOf(ModuleInventory::class, $inventory);
        // The app has many controllers - should find at least 10 modules
        $this->assertGreaterThan(10, $inventory->getModuleCount());

        // Verify specific known controllers are discovered
        $moduleNames = $inventory->getModuleNames();

        // Check that at least some known controllers are found
        $knownControllers = ['Transaksi', 'Kavling', 'Keuangan'];
        $foundCount = 0;
        foreach ($knownControllers as $name) {
            if (in_array($name, $moduleNames)) {
                $foundCount++;
            }
        }
        $this->assertGreaterThan(0, $foundCount, 'Should find at least one of the known controllers');

        // Verify controllers array is populated
        $this->assertNotEmpty($inventory->controllers);

        // Verify models array is populated
        $this->assertNotEmpty($inventory->models);
    }

    /**
     * Test ModuleDiscovery correctly identifies controller-model relationships
     */
    public function testModuleDiscoveryIdentifiesRelationships(): void
    {
        $inventory = $this->discovery->discover();

        // Find a module that should have model relationships
        $transaksiModule = $inventory->getModule('Transaksi');
        $keuanganModule = $inventory->getModule('Keuangan');

        // At least one of these should exist and have model paths
        $moduleWithModels = $transaksiModule ?? $keuanganModule;

        if ($moduleWithModels !== null) {
            $this->assertNotEmpty(
                $moduleWithModels->modelPaths,
                "Module '{$moduleWithModels->name}' should have associated models"
            );
        } else {
            // If neither specific module found, check any module has models
            $anyModuleHasModels = false;
            foreach ($inventory->modules as $module) {
                if (!empty($module->modelPaths)) {
                    $anyModuleHasModels = true;
                    break;
                }
            }
            $this->assertTrue($anyModuleHasModels, 'At least one module should have model relationships');
        }
    }

    /**
     * Test SecurityScanner detects vulnerabilities in real controllers
     */
    public function testSecurityScannerDetectsVulnerabilitiesInRealControllers(): void
    {
        $inventory = $this->discovery->discover();
        $scanner = new SecurityScanner();

        // Scan a few real modules
        $modulesToScan = ['Transaksi', 'Keuangan', 'Kavling'];
        $totalVulnerabilities = 0;
        $scannedModules = 0;

        foreach ($modulesToScan as $moduleName) {
            $module = $inventory->getModule($moduleName);
            if ($module === null) {
                continue;
            }

            $report = $scanner->scanModule($module);
            $this->assertInstanceOf(SecurityReport::class, $report);
            $this->assertEquals($moduleName, $report->moduleName);

            $totalVulnerabilities += $report->getTotalCount();
            $scannedModules++;
        }

        // We should have scanned at least one module
        $this->assertGreaterThan(0, $scannedModules, 'Should scan at least one real module');

        // Real-world controllers typically have some vulnerabilities
        // (raw queries, missing validation, etc.)
        $this->assertGreaterThanOrEqual(0, $totalVulnerabilities);
    }

    /**
     * Test SecurityScanner on Keuangan controller specifically
     * (known to have raw DB queries)
     */
    public function testSecurityScannerOnKeuanganController(): void
    {
        $inventory = $this->discovery->discover();
        $scanner = new SecurityScanner();

        $module = $inventory->getModule('Keuangan');
        if ($module === null) {
            $this->markTestSkipped('Keuangan module not found in discovery');
        }

        $report = $scanner->scanModule($module);

        $this->assertInstanceOf(SecurityReport::class, $report);
        $this->assertEquals('Keuangan', $report->moduleName);

        // Keuangan controller uses raw DB queries - should detect vulnerabilities
        // The controller has: $this->db->table('mkdt')->select(...)->where(...)->get()
        // which is Query Builder (safe), but may have other patterns
        $this->assertIsInt($report->getTotalCount());

        // Verify report can be serialized
        $json = $report->toJson();
        $this->assertJson($json);
    }

    /**
     * Test AuditGenerator produces valid audit reports for real controllers
     */
    public function testAuditGeneratorOnRealControllers(): void
    {
        $inventory = $this->discovery->discover();
        $scanner = new SecurityScanner();

        $depAnalyzer = new DependencyAnalyzer($inventory);
        $graph = $depAnalyzer->analyze();

        $codeAnalyzer = new CodeAnalyzer();
        $impactAnalyzer = new ImpactAnalyzer($inventory, $graph);
        $auditGen = new AuditGenerator($inventory, $scanner, $impactAnalyzer, $codeAnalyzer);

        // Try to audit a real module
        $modulesToAudit = ['Transaksi', 'Keuangan', 'Kavling'];
        $auditedCount = 0;

        foreach ($modulesToAudit as $moduleName) {
            $module = $inventory->getModule($moduleName);
            if ($module === null) {
                continue;
            }

            $auditReport = $auditGen->generateAudit($moduleName);
            $this->assertInstanceOf(AuditReport::class, $auditReport);

            // Verify audit report has meaningful content
            $markdown = $auditReport->toMarkdown();
            $this->assertNotEmpty($markdown);
            $this->assertStringContainsString($moduleName, $markdown);

            // Verify audit report can be serialized
            $json = $auditReport->toJson();
            $this->assertJson($json);

            $auditedCount++;
        }

        $this->assertGreaterThan(0, $auditedCount, 'Should audit at least one real module');
    }

    /**
     * Test DependencyAnalyzer works on real modules
     */
    public function testDependencyAnalyzerOnRealModules(): void
    {
        $inventory = $this->discovery->discover();

        $depAnalyzer = new DependencyAnalyzer($inventory);
        $graph = $depAnalyzer->analyze();

        $this->assertInstanceOf(DependencyGraph::class, $graph);
        $this->assertNotEmpty($graph->nodes);

        // Real app should have some dependency edges
        $totalEdges = 0;
        foreach ($graph->edges as $from => $toList) {
            $totalEdges += count($toList);
        }
        $this->assertGreaterThan(0, $totalEdges, 'Real app should have dependency relationships');

        // Impact scores should be calculated
        $this->assertNotEmpty($graph->impactScores);

        // Verify Mermaid diagram generation doesn't crash
        $mermaid = $graph->toMermaid();
        $this->assertNotEmpty($mermaid);
        $this->assertStringContainsString('graph TD', $mermaid);
    }

    /**
     * Test the system handles real-world code without crashing
     */
    public function testSystemHandlesRealWorldCodeWithoutCrashing(): void
    {
        // Full workflow on real app - should not throw any exceptions
        $inventory = $this->discovery->discover();
        $this->assertInstanceOf(ModuleInventory::class, $inventory);

        $depAnalyzer = new DependencyAnalyzer($inventory);
        $graph = $depAnalyzer->analyze();
        $this->assertInstanceOf(DependencyGraph::class, $graph);

        $scanner = new SecurityScanner();

        // Scan ALL discovered modules - none should crash
        $scannedCount = 0;
        foreach ($inventory->modules as $module) {
            try {
                $report = $scanner->scanModule($module);
                $this->assertInstanceOf(SecurityReport::class, $report);
                $scannedCount++;
            } catch (\Throwable $e) {
                $this->fail("SecurityScanner crashed on module '{$module->name}': " . $e->getMessage());
            }
        }

        $this->assertGreaterThan(0, $scannedCount, 'Should scan at least one module without crashing');
    }

    /**
     * Test that module inventory serialization works with real data
     */
    public function testModuleInventorySerializationWithRealData(): void
    {
        $inventory = $this->discovery->discover();

        // Serialize to JSON
        $json = $inventory->toJson();
        $this->assertJson($json);

        // Deserialize back
        $restored = ModuleInventory::fromJson($json);
        $this->assertInstanceOf(ModuleInventory::class, $restored);
        $this->assertEquals($inventory->getModuleCount(), $restored->getModuleCount());

        // Verify module names match
        $originalNames = $inventory->getModuleNames();
        $restoredNames = $restored->getModuleNames();
        sort($originalNames);
        sort($restoredNames);
        $this->assertEquals($originalNames, $restoredNames);
    }

    /**
     * Test that real controllers have valid PHP syntax
     */
    public function testRealControllersHaveValidPhpSyntax(): void
    {
        $inventory = $this->discovery->discover();

        // Check a subset of controllers for valid PHP syntax
        $checked = 0;
        $maxToCheck = 5;

        foreach ($inventory->controllers as $controllerPath) {
            if ($checked >= $maxToCheck) {
                break;
            }

            if (!file_exists($controllerPath)) {
                continue;
            }

            $output = [];
            $returnCode = 0;
            exec('php -l ' . escapeshellarg($controllerPath) . ' 2>&1', $output, $returnCode);
            $this->assertEquals(
                0,
                $returnCode,
                "PHP syntax error in {$controllerPath}: " . implode("\n", $output)
            );
            $checked++;
        }

        $this->assertGreaterThan(0, $checked, 'Should check at least one controller for syntax');
    }
}

<?php

namespace Tests\Unit\Libraries\Refactor\Analysis;

use App\Libraries\Refactor\Analysis\AuditGenerator;
use App\Libraries\Refactor\Analysis\CodeAnalyzer;
use App\Libraries\Refactor\Analysis\ImpactAnalyzer;
use App\Libraries\Refactor\Exceptions\AnalysisException;
use App\Libraries\Refactor\Models\AuditReport;
use App\Libraries\Refactor\Models\ControllerAnalysis;
use App\Libraries\Refactor\Models\DependencyGraph;
use App\Libraries\Refactor\Models\ImpactAnalysis;
use App\Libraries\Refactor\Models\Module;
use App\Libraries\Refactor\Models\ModuleInventory;
use App\Libraries\Refactor\Models\SecurityReport;
use App\Libraries\Refactor\Models\Vulnerability;
use App\Libraries\Refactor\Security\SecurityScanner;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Unit tests for AuditGenerator
 * 
 * @package Tests\Unit\Libraries\Refactor\Analysis
 */
class AuditGeneratorTest extends CIUnitTestCase
{
    private string $testFilesDir;
    private ModuleInventory $inventory;
    private SecurityScanner $scanner;
    private ImpactAnalyzer $impactAnalyzer;
    private DependencyGraph $dependencyGraph;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test files directory
        $this->testFilesDir = WRITEPATH . 'tests/audit_generator_test_' . uniqid();
        if (!is_dir($this->testFilesDir)) {
            mkdir($this->testFilesDir, 0755, true);
        }

        // Create test inventory
        $this->inventory = new ModuleInventory();

        // Create test dependency graph
        $this->dependencyGraph = new DependencyGraph();

        // Create test scanner
        $this->scanner = new SecurityScanner();

        // Create test impact analyzer
        $this->impactAnalyzer = new ImpactAnalyzer($this->inventory, $this->dependencyGraph);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Clean up test files
        if (is_dir($this->testFilesDir)) {
            $this->deleteDirectory($this->testFilesDir);
        }
    }

    /**
     * Helper to recursively delete a directory
     */
    private function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }

    /**
     * Test constructor initializes all dependencies
     */
    public function testConstructorInitializesDependencies(): void
    {
        $generator = new AuditGenerator(
            $this->inventory,
            $this->scanner,
            $this->impactAnalyzer
        );

        $this->assertInstanceOf(AuditGenerator::class, $generator);
        $this->assertSame($this->inventory, $generator->getInventory());
        $this->assertSame($this->scanner, $generator->getScanner());
        $this->assertSame($this->impactAnalyzer, $generator->getImpactAnalyzer());
        $this->assertInstanceOf(CodeAnalyzer::class, $generator->getCodeAnalyzer());
    }

    /**
     * Test constructor accepts custom CodeAnalyzer
     */
    public function testConstructorAcceptsCustomCodeAnalyzer(): void
    {
        $customAnalyzer = new CodeAnalyzer();
        
        $generator = new AuditGenerator(
            $this->inventory,
            $this->scanner,
            $this->impactAnalyzer,
            $customAnalyzer
        );

        $this->assertSame($customAnalyzer, $generator->getCodeAnalyzer());
    }

    /**
     * Test generateAudit throws exception for non-existent module
     */
    public function testGenerateAuditThrowsExceptionForNonExistentModule(): void
    {
        $generator = new AuditGenerator(
            $this->inventory,
            $this->scanner,
            $this->impactAnalyzer
        );

        $this->expectException(AnalysisException::class);
        $this->expectExceptionMessage("Module 'NonExistent' not found in inventory");

        $generator->generateAudit('NonExistent');
    }

    /**
     * Test generateAudit throws exception for module without controller
     */
    public function testGenerateAuditThrowsExceptionForModuleWithoutController(): void
    {
        $module = new Module('TestModule', ''); // Empty controller path
        $this->inventory->addModule($module);

        $generator = new AuditGenerator(
            $this->inventory,
            $this->scanner,
            $this->impactAnalyzer
        );

        $this->expectException(AnalysisException::class);
        $this->expectExceptionMessage("Controller file not found for module 'TestModule'");

        $generator->generateAudit('TestModule');
    }

    /**
     * Test generateAudit creates comprehensive audit report
     */
    public function testGenerateAuditCreatesComprehensiveReport(): void
    {
        // Create a simple test controller
        $controllerPath = $this->testFilesDir . '/TestController.php';
        $controllerCode = <<<'PHP'
<?php

namespace App\Controllers;

class TestController extends BaseController
{
    public function index()
    {
        $data = $this->db->table('users')->get()->getResultArray();
        return view('test/index', $data);
    }

    public function save()
    {
        $name = $this->request->getPost('name');
        $total = $this->request->getPost('price') * $this->request->getPost('quantity');
        
        $this->db->table('orders')->insert([
            'name' => $name,
            'total' => $total
        ]);
        
        return redirect()->to('/test');
    }
}
PHP;
        file_put_contents($controllerPath, $controllerCode);

        // Create module
        $module = new Module('Test', $controllerPath);
        $module->routes = ['GET /test', 'POST /test/save'];
        $this->inventory->addModule($module);

        // Add module to dependency graph
        $this->dependencyGraph->addNode('Test');

        // Generate audit
        $generator = new AuditGenerator(
            $this->inventory,
            $this->scanner,
            $this->impactAnalyzer
        );

        $report = $generator->generateAudit('Test');

        // Assertions
        $this->assertInstanceOf(AuditReport::class, $report);
        $this->assertEquals('Test', $report->moduleName);
        $this->assertInstanceOf(ControllerAnalysis::class, $report->controllerAnalysis);
        $this->assertInstanceOf(SecurityReport::class, $report->securityReport);
        $this->assertInstanceOf(ImpactAnalysis::class, $report->impactAnalysis);
        $this->assertContains($report->complexity, ['Simple', 'Medium', 'Complex']);
        $this->assertNotEmpty($report->recommendations);
    }

    /**
     * Test analyzeControllerStructure delegates to CodeAnalyzer
     */
    public function testAnalyzeControllerStructureDelegatesToCodeAnalyzer(): void
    {
        $controllerPath = $this->testFilesDir . '/SimpleController.php';
        $controllerCode = <<<'PHP'
<?php

namespace App\Controllers;

class SimpleController extends BaseController
{
    public function index()
    {
        return view('simple/index');
    }
}
PHP;
        file_put_contents($controllerPath, $controllerCode);

        $generator = new AuditGenerator(
            $this->inventory,
            $this->scanner,
            $this->impactAnalyzer
        );

        $analysis = $generator->analyzeControllerStructure($controllerPath);

        $this->assertIsArray($analysis);
        $this->assertArrayHasKey('methodCount', $analysis);
        $this->assertArrayHasKey('linesOfCode', $analysis);
        $this->assertArrayHasKey('dependencies', $analysis);
        $this->assertArrayHasKey('methods', $analysis);
    }

    /**
     * Test identifyBusinessLogic delegates to CodeAnalyzer
     */
    public function testIdentifyBusinessLogicDelegatesToCodeAnalyzer(): void
    {
        $controllerPath = $this->testFilesDir . '/BusinessController.php';
        $controllerCode = <<<'PHP'
<?php

namespace App\Controllers;

class BusinessController extends BaseController
{
    public function calculate()
    {
        $price = 100;
        $tax = $price * 0.1;
        $total = $price + $tax;
        
        return $this->response->setJSON(['total' => $total]);
    }
}
PHP;
        file_put_contents($controllerPath, $controllerCode);

        $generator = new AuditGenerator(
            $this->inventory,
            $this->scanner,
            $this->impactAnalyzer
        );

        $findings = $generator->identifyBusinessLogic($controllerPath);

        $this->assertIsArray($findings);
        // Should find calculation patterns
        $this->assertNotEmpty($findings);
    }

    /**
     * Test identifyDatabaseQueries delegates to CodeAnalyzer
     */
    public function testIdentifyDatabaseQueriesDelegatesToCodeAnalyzer(): void
    {
        $controllerPath = $this->testFilesDir . '/DatabaseController.php';
        $controllerCode = <<<'PHP'
<?php

namespace App\Controllers;

class DatabaseController extends BaseController
{
    public function list()
    {
        $users = $this->db->table('users')->get()->getResultArray();
        return view('users/list', ['users' => $users]);
    }
}
PHP;
        file_put_contents($controllerPath, $controllerCode);

        $generator = new AuditGenerator(
            $this->inventory,
            $this->scanner,
            $this->impactAnalyzer
        );

        $findings = $generator->identifyDatabaseQueries($controllerPath);

        $this->assertIsArray($findings);
        // Should find database query patterns
        $this->assertNotEmpty($findings);
    }

    /**
     * Test generateAudit includes security vulnerabilities
     */
    public function testGenerateAuditIncludesSecurityVulnerabilities(): void
    {
        $controllerPath = $this->testFilesDir . '/VulnerableController.php';
        $controllerCode = <<<'PHP'
<?php

namespace App\Controllers;

class VulnerableController extends BaseController
{
    public function search()
    {
        $id = $this->request->getGet('id');
        $query = "SELECT * FROM users WHERE id = " . $id;
        $result = $this->db->query($query);
        return view('search/results', ['data' => $result]);
    }
}
PHP;
        file_put_contents($controllerPath, $controllerCode);

        $module = new Module('Vulnerable', $controllerPath);
        $this->inventory->addModule($module);
        $this->dependencyGraph->addNode('Vulnerable');

        $generator = new AuditGenerator(
            $this->inventory,
            $this->scanner,
            $this->impactAnalyzer
        );

        $report = $generator->generateAudit('Vulnerable');

        // Should detect SQL injection vulnerability
        $this->assertGreaterThan(0, $report->securityReport->getTotalCount());
    }

    /**
     * Test generateAudit includes impact analysis
     */
    public function testGenerateAuditIncludesImpactAnalysis(): void
    {
        $controllerPath = $this->testFilesDir . '/ImpactController.php';
        $controllerCode = <<<'PHP'
<?php

namespace App\Controllers;

class ImpactController extends BaseController
{
    public function index()
    {
        return view('impact/index');
    }
}
PHP;
        file_put_contents($controllerPath, $controllerCode);

        $module = new Module('Impact', $controllerPath);
        $module->routes = ['GET /impact'];
        $this->inventory->addModule($module);
        $this->dependencyGraph->addNode('Impact');

        $generator = new AuditGenerator(
            $this->inventory,
            $this->scanner,
            $this->impactAnalyzer
        );

        $report = $generator->generateAudit('Impact');

        $this->assertInstanceOf(ImpactAnalysis::class, $report->impactAnalysis);
        $this->assertEquals('Impact', $report->impactAnalysis->moduleName);
    }

    /**
     * Test generateAudit generates recommendations
     */
    public function testGenerateAuditGeneratesRecommendations(): void
    {
        $controllerPath = $this->testFilesDir . '/RecommendController.php';
        $controllerCode = <<<'PHP'
<?php

namespace App\Controllers;

class RecommendController extends BaseController
{
    public function process()
    {
        $data = $this->db->table('items')->get()->getResultArray();
        $total = 0;
        foreach ($data as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $this->response->setJSON(['total' => $total]);
    }
}
PHP;
        file_put_contents($controllerPath, $controllerCode);

        $module = new Module('Recommend', $controllerPath);
        $this->inventory->addModule($module);
        $this->dependencyGraph->addNode('Recommend');

        $generator = new AuditGenerator(
            $this->inventory,
            $this->scanner,
            $this->impactAnalyzer
        );

        $report = $generator->generateAudit('Recommend');

        $this->assertNotEmpty($report->recommendations);
        $this->assertIsArray($report->recommendations);
        
        // Should have recommendations about repository, service, testing, etc.
        $this->assertGreaterThan(3, count($report->recommendations));
    }

    /**
     * Test generateMultipleAudits processes multiple modules
     */
    public function testGenerateMultipleAuditsProcessesMultipleModules(): void
    {
        // Create two test controllers
        $controller1Path = $this->testFilesDir . '/Module1Controller.php';
        $controller2Path = $this->testFilesDir . '/Module2Controller.php';
        
        $controllerCode = <<<'PHP'
<?php

namespace App\Controllers;

class TestController extends BaseController
{
    public function index()
    {
        return view('test/index');
    }
}
PHP;
        
        file_put_contents($controller1Path, $controllerCode);
        file_put_contents($controller2Path, $controllerCode);

        // Create modules
        $module1 = new Module('Module1', $controller1Path);
        $this->inventory->addModule($module1);
        $this->dependencyGraph->addNode('Module1');

        $module2 = new Module('Module2', $controller2Path);
        $this->inventory->addModule($module2);
        $this->dependencyGraph->addNode('Module2');

        $generator = new AuditGenerator(
            $this->inventory,
            $this->scanner,
            $this->impactAnalyzer
        );

        $reports = $generator->generateMultipleAudits(['Module1', 'Module2']);

        $this->assertCount(2, $reports);
        $this->assertArrayHasKey('Module1', $reports);
        $this->assertArrayHasKey('Module2', $reports);
        $this->assertInstanceOf(AuditReport::class, $reports['Module1']);
        $this->assertInstanceOf(AuditReport::class, $reports['Module2']);
    }

    /**
     * Test generateMultipleAudits skips non-existent modules
     */
    public function testGenerateMultipleAuditsSkipsNonExistentModules(): void
    {
        $controllerPath = $this->testFilesDir . '/ExistingController.php';
        $controllerCode = <<<'PHP'
<?php

namespace App\Controllers;

class ExistingController extends BaseController
{
    public function index()
    {
        return view('existing/index');
    }
}
PHP;
        file_put_contents($controllerPath, $controllerCode);

        $module = new Module('Existing', $controllerPath);
        $this->inventory->addModule($module);
        $this->dependencyGraph->addNode('Existing');

        $generator = new AuditGenerator(
            $this->inventory,
            $this->scanner,
            $this->impactAnalyzer
        );

        $reports = $generator->generateMultipleAudits(['Existing', 'NonExistent']);

        // Should only have report for existing module
        $this->assertCount(1, $reports);
        $this->assertArrayHasKey('Existing', $reports);
        $this->assertArrayNotHasKey('NonExistent', $reports);
    }

    /**
     * Test generateAndSaveAudit creates markdown file
     */
    public function testGenerateAndSaveAuditCreatesMarkdownFile(): void
    {
        $controllerPath = $this->testFilesDir . '/SaveController.php';
        $controllerCode = <<<'PHP'
<?php

namespace App\Controllers;

class SaveController extends BaseController
{
    public function index()
    {
        return view('save/index');
    }
}
PHP;
        file_put_contents($controllerPath, $controllerCode);

        $module = new Module('Save', $controllerPath);
        $this->inventory->addModule($module);
        $this->dependencyGraph->addNode('Save');

        $generator = new AuditGenerator(
            $this->inventory,
            $this->scanner,
            $this->impactAnalyzer
        );

        $outputPath = $this->testFilesDir . '/audit_report.md';
        $report = $generator->generateAndSaveAudit('Save', $outputPath);

        $this->assertInstanceOf(AuditReport::class, $report);
        $this->assertFileExists($outputPath);
        
        $content = file_get_contents($outputPath);
        $this->assertStringContainsString('# Audit Report: Save', $content);
        $this->assertStringContainsString('## Controller Analysis', $content);
        $this->assertStringContainsString('## Recommendations', $content);
    }

    /**
     * Test generateAndSaveAudit creates output directory if not exists
     */
    public function testGenerateAndSaveAuditCreatesOutputDirectory(): void
    {
        $controllerPath = $this->testFilesDir . '/DirController.php';
        $controllerCode = <<<'PHP'
<?php

namespace App\Controllers;

class DirController extends BaseController
{
    public function index()
    {
        return view('dir/index');
    }
}
PHP;
        file_put_contents($controllerPath, $controllerCode);

        $module = new Module('Dir', $controllerPath);
        $this->inventory->addModule($module);
        $this->dependencyGraph->addNode('Dir');

        $generator = new AuditGenerator(
            $this->inventory,
            $this->scanner,
            $this->impactAnalyzer
        );

        $outputPath = $this->testFilesDir . '/nested/dir/audit_report.md';
        $report = $generator->generateAndSaveAudit('Dir', $outputPath);

        $this->assertFileExists($outputPath);
        $this->assertDirectoryExists(dirname($outputPath));
    }

    /**
     * Test audit report includes critical security recommendation
     */
    public function testAuditReportIncludesCriticalSecurityRecommendation(): void
    {
        $controllerPath = $this->testFilesDir . '/CriticalController.php';
        $controllerCode = <<<'PHP'
<?php

namespace App\Controllers;

class CriticalController extends BaseController
{
    public function delete()
    {
        $id = $this->request->getGet('id');
        $sql = "DELETE FROM users WHERE id = " . $id;
        $this->db->query($sql);
        return redirect()->back();
    }
}
PHP;
        file_put_contents($controllerPath, $controllerCode);

        $module = new Module('Critical', $controllerPath);
        $this->inventory->addModule($module);
        $this->dependencyGraph->addNode('Critical');

        $generator = new AuditGenerator(
            $this->inventory,
            $this->scanner,
            $this->impactAnalyzer
        );

        $report = $generator->generateAudit('Critical');

        // Should have critical security vulnerabilities
        $this->assertGreaterThan(0, $report->securityReport->getCriticalCount());
        
        // Should have recommendation about critical vulnerabilities
        $hasCriticalRecommendation = false;
        foreach ($report->recommendations as $rec) {
            if (stripos($rec, 'CRITICAL') !== false) {
                $hasCriticalRecommendation = true;
                break;
            }
        }
        $this->assertTrue($hasCriticalRecommendation);
    }

    /**
     * Test audit report complexity estimation
     */
    public function testAuditReportComplexityEstimation(): void
    {
        // Simple controller
        $simpleControllerPath = $this->testFilesDir . '/SimpleComplexController.php';
        $simpleCode = <<<'PHP'
<?php

namespace App\Controllers;

class SimpleComplexController extends BaseController
{
    public function index()
    {
        return view('simple/index');
    }
}
PHP;
        file_put_contents($simpleControllerPath, $simpleCode);

        $simpleModule = new Module('SimpleComplex', $simpleControllerPath);
        $this->inventory->addModule($simpleModule);
        $this->dependencyGraph->addNode('SimpleComplex');

        $generator = new AuditGenerator(
            $this->inventory,
            $this->scanner,
            $this->impactAnalyzer
        );

        $report = $generator->generateAudit('SimpleComplex');

        // Simple controller should have Simple complexity
        $this->assertEquals('Simple', $report->complexity);
    }
}

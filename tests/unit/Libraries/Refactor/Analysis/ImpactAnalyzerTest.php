<?php

namespace Tests\Unit\Libraries\Refactor\Analysis;

use App\Libraries\Refactor\Analysis\ImpactAnalyzer;
use App\Libraries\Refactor\Models\DependencyGraph;
use App\Libraries\Refactor\Models\ImpactAnalysis;
use App\Libraries\Refactor\Models\Module;
use App\Libraries\Refactor\Models\ModuleInventory;
use App\Libraries\Refactor\Models\SecurityReport;
use App\Libraries\Refactor\Models\Vulnerability;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Unit tests for ImpactAnalyzer
 * 
 * Tests the impact analysis functionality including:
 * - Dependency analysis
 * - Route and view identification
 * - File change estimation
 * - Risk assessment calculation
 * - Warning generation
 * 
 * @package Tests\Unit\Libraries\Refactor\Analysis
 */
class ImpactAnalyzerTest extends CIUnitTestCase
{
    private ModuleInventory $inventory;
    private DependencyGraph $graph;
    private ImpactAnalyzer $analyzer;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test inventory
        $this->inventory = new ModuleInventory();
        
        // Create test dependency graph
        $this->graph = new DependencyGraph();
        
        // Create analyzer
        $this->analyzer = new ImpactAnalyzer($this->inventory, $this->graph);
    }

    /**
     * Test analyzing a leaf module (no dependents)
     */
    public function testAnalyzeLeafModule(): void
    {
        // Create a simple module with no dependents
        $module = new Module('TestModule', APPPATH . 'Controllers/TestModule.php');
        $module->routes = ['GET /test', 'POST /test/save'];
        $module->methods = ['index', 'save'];
        
        $this->inventory->addModule($module);
        $this->graph->addNode('TestModule');
        $this->graph->setImpactScore('TestModule', 0);

        $analysis = $this->analyzer->analyze('TestModule');

        $this->assertInstanceOf(ImpactAnalysis::class, $analysis);
        $this->assertEquals('TestModule', $analysis->moduleName);
        $this->assertCount(0, $analysis->dependentModules);
        $this->assertEquals(0, $analysis->impactScore);
        $this->assertEquals(ImpactAnalysis::RISK_LOW, $analysis->riskLevel);
        $this->assertCount(2, $analysis->affectedRoutes);
    }

    /**
     * Test analyzing a module with dependents
     */
    public function testAnalyzeModuleWithDependents(): void
    {
        // Create a core module that other modules depend on
        $coreModule = new Module('CoreModule', APPPATH . 'Controllers/CoreModule.php');
        $coreModule->routes = ['GET /core'];
        
        $dependent1 = new Module('Dependent1', APPPATH . 'Controllers/Dependent1.php');
        $dependent2 = new Module('Dependent2', APPPATH . 'Controllers/Dependent2.php');
        
        $this->inventory->addModule($coreModule);
        $this->inventory->addModule($dependent1);
        $this->inventory->addModule($dependent2);
        
        // Build dependency graph: Dependent1 -> CoreModule, Dependent2 -> CoreModule
        $this->graph->addEdge('Dependent1', 'CoreModule');
        $this->graph->addEdge('Dependent2', 'CoreModule');
        $this->graph->setImpactScore('CoreModule', 2);

        $analysis = $this->analyzer->analyze('CoreModule');

        $this->assertCount(2, $analysis->dependentModules);
        $this->assertContains('Dependent1', $analysis->dependentModules);
        $this->assertContains('Dependent2', $analysis->dependentModules);
        $this->assertEquals(2, $analysis->impactScore);
        $this->assertTrue($analysis->hasDependents());
    }

    /**
     * Test file change estimation
     */
    public function testEstimateFileChanges(): void
    {
        // Module without service or repository
        $module = new Module('NewModule', APPPATH . 'Controllers/NewModule.php');
        $module->modelPaths = [
            APPPATH . 'Models/NewModuleModel.php',
            APPPATH . 'Models/RelatedModel.php'
        ];
        $module->methods = ['index', 'create', 'update', 'delete'];
        $module->routes = ['GET /new', 'POST /new/save'];
        
        $this->inventory->addModule($module);
        $this->graph->addNode('NewModule');
        $this->graph->setImpactScore('NewModule', 0);

        $analysis = $this->analyzer->analyze('NewModule');

        // Should create: Repository, Service, Validation, API Controller
        $this->assertGreaterThan(0, $analysis->filesWillBeCreated);
        
        // Should modify: Controller + 2 Models
        $this->assertGreaterThanOrEqual(3, $analysis->filesWillBeModified);
        
        $this->assertGreaterThan(0, $analysis->getTotalFilesAffected());
    }

    /**
     * Test risk assessment for low risk module
     */
    public function testRiskAssessmentLowRisk(): void
    {
        $module = new Module('LowRiskModule', APPPATH . 'Controllers/LowRiskModule.php');
        $module->routes = ['GET /low'];
        $module->modelPaths = [APPPATH . 'Models/LowRiskModel.php'];
        
        $this->inventory->addModule($module);
        $this->graph->addNode('LowRiskModule');
        $this->graph->setImpactScore('LowRiskModule', 0);

        $analysis = $this->analyzer->analyze('LowRiskModule');

        $this->assertEquals(ImpactAnalysis::RISK_LOW, $analysis->riskLevel);
        $this->assertTrue($analysis->isLowRisk());
        $this->assertFalse($analysis->isMediumRisk());
        $this->assertFalse($analysis->isHighRisk());
    }

    /**
     * Test risk assessment for medium risk module
     */
    public function testRiskAssessmentMediumRisk(): void
    {
        $module = new Module('MediumRiskModule', APPPATH . 'Controllers/MediumRiskModule.php');
        $module->routes = ['GET /medium', 'POST /medium/save', 'PUT /medium/update', 'DELETE /medium/delete'];
        $module->modelPaths = [
            APPPATH . 'Models/Model1.php',
            APPPATH . 'Models/Model2.php',
            APPPATH . 'Models/Model3.php'
        ];
        
        $this->inventory->addModule($module);
        
        // Add 2 dependents
        $dep1 = new Module('Dep1', APPPATH . 'Controllers/Dep1.php');
        $dep2 = new Module('Dep2', APPPATH . 'Controllers/Dep2.php');
        $this->inventory->addModule($dep1);
        $this->inventory->addModule($dep2);
        
        $this->graph->addEdge('Dep1', 'MediumRiskModule');
        $this->graph->addEdge('Dep2', 'MediumRiskModule');
        $this->graph->setImpactScore('MediumRiskModule', 2);

        $analysis = $this->analyzer->analyze('MediumRiskModule');

        $this->assertEquals(ImpactAnalysis::RISK_MEDIUM, $analysis->riskLevel);
        $this->assertTrue($analysis->isMediumRisk());
    }

    /**
     * Test risk assessment for high risk module
     */
    public function testRiskAssessmentHighRisk(): void
    {
        $module = new Module('HighRiskModule', APPPATH . 'Controllers/HighRiskModule.php');
        
        // Many routes
        for ($i = 1; $i <= 10; $i++) {
            $module->routes[] = "GET /high/route{$i}";
        }
        
        // Many models
        for ($i = 1; $i <= 8; $i++) {
            $module->modelPaths[] = APPPATH . "Models/Model{$i}.php";
        }
        
        $this->inventory->addModule($module);
        
        // Add many dependents
        for ($i = 1; $i <= 6; $i++) {
            $dep = new Module("Dependent{$i}", APPPATH . "Controllers/Dependent{$i}.php");
            $this->inventory->addModule($dep);
            $this->graph->addEdge("Dependent{$i}", 'HighRiskModule');
        }
        
        $this->graph->setImpactScore('HighRiskModule', 6);

        $analysis = $this->analyzer->analyze('HighRiskModule');

        $this->assertEquals(ImpactAnalysis::RISK_HIGH, $analysis->riskLevel);
        $this->assertTrue($analysis->isHighRisk());
    }

    /**
     * Test security vulnerability analysis
     */
    public function testSecurityVulnerabilityAnalysis(): void
    {
        $module = new Module('VulnerableModule', APPPATH . 'Controllers/VulnerableModule.php');
        $module->routes = ['GET /vulnerable', 'POST /vulnerable/save'];
        $this->inventory->addModule($module);
        $this->graph->addNode('VulnerableModule');
        $this->graph->setImpactScore('VulnerableModule', 0);

        // Create security report with vulnerabilities
        $securityReport = new SecurityReport('VulnerableModule');
        
        $criticalVuln = new Vulnerability(
            Vulnerability::TYPE_SQL_INJECTION,
            Vulnerability::SEVERITY_CRITICAL,
            APPPATH . 'Controllers/VulnerableModule.php',
            45,
            'SQL injection vulnerability',
            'Use Query Builder with parameter binding'
        );
        
        $highVuln = new Vulnerability(
            Vulnerability::TYPE_XSS,
            Vulnerability::SEVERITY_HIGH,
            APPPATH . 'Controllers/VulnerableModule.php',
            78,
            'XSS vulnerability',
            'Use esc() helper for output escaping'
        );
        
        $securityReport->addVulnerability($criticalVuln);
        $securityReport->addVulnerability($highVuln);

        $this->analyzer->setSecurityReport($securityReport);
        $analysis = $this->analyzer->analyze('VulnerableModule');

        $this->assertEquals(2, $analysis->vulnerabilityCount);
        $this->assertEquals(1, $analysis->criticalVulnerabilityCount);
        
        // Should have warning about critical vulnerabilities
        $hasCriticalWarning = false;
        foreach ($analysis->warnings as $warning) {
            if (stripos($warning, 'critical') !== false) {
                $hasCriticalWarning = true;
                break;
            }
        }
        $this->assertTrue($hasCriticalWarning, 'Should have warning about critical vulnerabilities');
    }

    /**
     * Test warning generation for dependent modules
     */
    public function testWarningGenerationForDependents(): void
    {
        $module = new Module('ModuleWithDeps', APPPATH . 'Controllers/ModuleWithDeps.php');
        $this->inventory->addModule($module);
        
        // Add 3 dependents
        for ($i = 1; $i <= 3; $i++) {
            $dep = new Module("Dep{$i}", APPPATH . "Controllers/Dep{$i}.php");
            $this->inventory->addModule($dep);
            $this->graph->addEdge("Dep{$i}", 'ModuleWithDeps');
        }
        
        $this->graph->setImpactScore('ModuleWithDeps', 3);

        $analysis = $this->analyzer->analyze('ModuleWithDeps');

        $this->assertGreaterThan(0, count($analysis->warnings));
        
        // Should have warning about dependent modules
        $hasDepWarning = false;
        foreach ($analysis->warnings as $warning) {
            if (stripos($warning, 'dependent') !== false) {
                $hasDepWarning = true;
                break;
            }
        }
        $this->assertTrue($hasDepWarning);
    }

    /**
     * Test warning generation for many routes
     */
    public function testWarningGenerationForManyRoutes(): void
    {
        $module = new Module('ManyRoutesModule', APPPATH . 'Controllers/ManyRoutesModule.php');
        
        // Add many routes
        for ($i = 1; $i <= 8; $i++) {
            $module->routes[] = "GET /route{$i}";
        }
        
        $this->inventory->addModule($module);
        $this->graph->addNode('ManyRoutesModule');
        $this->graph->setImpactScore('ManyRoutesModule', 0);

        $analysis = $this->analyzer->analyze('ManyRoutesModule');

        // Should have warning about many routes
        $hasRouteWarning = false;
        foreach ($analysis->warnings as $warning) {
            if (stripos($warning, 'routes') !== false) {
                $hasRouteWarning = true;
                break;
            }
        }
        $this->assertTrue($hasRouteWarning);
    }

    /**
     * Test warning generation for leaf modules
     */
    public function testWarningGenerationForLeafModule(): void
    {
        $module = new Module('LeafModule', APPPATH . 'Controllers/LeafModule.php');
        $this->inventory->addModule($module);
        $this->graph->addNode('LeafModule');
        $this->graph->setImpactScore('LeafModule', 0);

        $analysis = $this->analyzer->analyze('LeafModule');

        // Should have warning about being a safe starting point
        $hasLeafWarning = false;
        foreach ($analysis->warnings as $warning) {
            if (stripos($warning, 'leaf') !== false || stripos($warning, 'safe starting point') !== false) {
                $hasLeafWarning = true;
                break;
            }
        }
        $this->assertTrue($hasLeafWarning);
    }

    /**
     * Test analyzing multiple modules
     */
    public function testAnalyzeMultiple(): void
    {
        $module1 = new Module('Module1', APPPATH . 'Controllers/Module1.php');
        $module2 = new Module('Module2', APPPATH . 'Controllers/Module2.php');
        $module3 = new Module('Module3', APPPATH . 'Controllers/Module3.php');
        
        $this->inventory->addModule($module1);
        $this->inventory->addModule($module2);
        $this->inventory->addModule($module3);
        
        $this->graph->addNode('Module1');
        $this->graph->addNode('Module2');
        $this->graph->addNode('Module3');
        
        $this->graph->setImpactScore('Module1', 0);
        $this->graph->setImpactScore('Module2', 1);
        $this->graph->setImpactScore('Module3', 2);

        $analyses = $this->analyzer->analyzeMultiple(['Module1', 'Module2', 'Module3']);

        $this->assertCount(3, $analyses);
        $this->assertArrayHasKey('Module1', $analyses);
        $this->assertArrayHasKey('Module2', $analyses);
        $this->assertArrayHasKey('Module3', $analyses);
        
        $this->assertInstanceOf(ImpactAnalysis::class, $analyses['Module1']);
        $this->assertInstanceOf(ImpactAnalysis::class, $analyses['Module2']);
        $this->assertInstanceOf(ImpactAnalysis::class, $analyses['Module3']);
    }

    /**
     * Test analyzing non-existent module throws exception
     */
    public function testAnalyzeNonExistentModuleThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Module 'NonExistent' not found");

        $this->analyzer->analyze('NonExistent');
    }

    /**
     * Test analyze with invalid argument type throws exception
     */
    public function testAnalyzeWithInvalidArgumentThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Module name must be a string');

        $this->analyzer->analyze(123);
    }

    /**
     * Test getting modules sorted by impact
     */
    public function testGetModulesByImpact(): void
    {
        $module1 = new Module('HighImpact', APPPATH . 'Controllers/HighImpact.php');
        $module2 = new Module('MediumImpact', APPPATH . 'Controllers/MediumImpact.php');
        $module3 = new Module('LowImpact', APPPATH . 'Controllers/LowImpact.php');
        
        $this->inventory->addModule($module1);
        $this->inventory->addModule($module2);
        $this->inventory->addModule($module3);
        
        $this->graph->addNode('HighImpact');
        $this->graph->addNode('MediumImpact');
        $this->graph->addNode('LowImpact');
        
        $this->graph->setImpactScore('HighImpact', 5);
        $this->graph->setImpactScore('MediumImpact', 2);
        $this->graph->setImpactScore('LowImpact', 0);

        $sortedModules = $this->analyzer->getModulesByImpact();

        $this->assertCount(3, $sortedModules);
        $this->assertEquals('LowImpact', $sortedModules[0]);
        $this->assertEquals('MediumImpact', $sortedModules[1]);
        $this->assertEquals('HighImpact', $sortedModules[2]);
    }

    /**
     * Test ImpactAnalysis model methods
     */
    public function testImpactAnalysisModel(): void
    {
        $analysis = new ImpactAnalysis('TestModule');
        
        $this->assertEquals('TestModule', $analysis->moduleName);
        $this->assertEquals(ImpactAnalysis::RISK_LOW, $analysis->riskLevel);
        $this->assertFalse($analysis->hasDependents());
        
        $analysis->addDependentModule('Dep1');
        $analysis->addDependentModule('Dep2');
        $this->assertTrue($analysis->hasDependents());
        $this->assertCount(2, $analysis->dependentModules);
        
        $analysis->addAffectedRoute('GET /test');
        $this->assertCount(1, $analysis->affectedRoutes);
        
        $analysis->addAffectedView(APPPATH . 'Views/test.php');
        $this->assertCount(1, $analysis->affectedViews);
        
        $analysis->addWarning('Test warning');
        $this->assertCount(1, $analysis->warnings);
        
        $analysis->filesWillBeCreated = 3;
        $analysis->filesWillBeModified = 5;
        $this->assertEquals(8, $analysis->getTotalFilesAffected());
        
        $analysis->riskLevel = ImpactAnalysis::RISK_HIGH;
        $this->assertTrue($analysis->isHighRisk());
        $this->assertFalse($analysis->isLowRisk());
    }

    /**
     * Test ImpactAnalysis JSON serialization
     */
    public function testImpactAnalysisJsonSerialization(): void
    {
        $analysis = new ImpactAnalysis('TestModule');
        $analysis->addDependentModule('Dep1');
        $analysis->addAffectedRoute('GET /test');
        $analysis->filesWillBeCreated = 2;
        $analysis->filesWillBeModified = 3;
        $analysis->riskLevel = ImpactAnalysis::RISK_MEDIUM;

        $json = $analysis->toJson();
        $this->assertIsString($json);
        
        $decoded = json_decode($json, true);
        $this->assertEquals('TestModule', $decoded['moduleName']);
        $this->assertCount(1, $decoded['dependentModules']);
        $this->assertEquals(ImpactAnalysis::RISK_MEDIUM, $decoded['riskLevel']);
        
        $restored = ImpactAnalysis::fromJson($json);
        $this->assertEquals($analysis->moduleName, $restored->moduleName);
        $this->assertEquals($analysis->riskLevel, $restored->riskLevel);
        $this->assertCount(1, $restored->dependentModules);
    }
}

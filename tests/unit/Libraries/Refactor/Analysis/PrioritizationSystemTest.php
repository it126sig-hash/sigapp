<?php

namespace Tests\Unit\Libraries\Refactor\Analysis;

use App\Libraries\Refactor\Analysis\PrioritizationSystem;
use App\Libraries\Refactor\Models\DependencyGraph;
use App\Libraries\Refactor\Models\Module;
use App\Libraries\Refactor\Models\ModuleInventory;
use App\Libraries\Refactor\Models\PriorityScore;
use App\Libraries\Refactor\Models\SecurityReport;
use App\Libraries\Refactor\Models\Vulnerability;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Unit tests for PrioritizationSystem
 * 
 * Tests the module prioritization functionality including:
 * - Identifying leaf modules (safe starting points)
 * - Identifying core modules (high-risk targets)
 * - Calculating priority scores
 * - Applying manual overrides
 * - Generating recommended refactoring order
 * 
 * @package Tests\Unit\Libraries\Refactor\Analysis
 */
class PrioritizationSystemTest extends CIUnitTestCase
{
    private ModuleInventory $inventory;
    private DependencyGraph $graph;
    private PrioritizationSystem $prioritizer;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test inventory
        $this->inventory = new ModuleInventory();
        
        // Create test dependency graph
        $this->graph = new DependencyGraph();
        
        // Create prioritization system
        $this->prioritizer = new PrioritizationSystem($this->inventory, $this->graph);
    }

    /**
     * Test identifying leaf modules (no dependents)
     */
    public function testIdentifyLeafModules(): void
    {
        // Create modules with different impact scores
        $leaf1 = new Module('LeafModule1', APPPATH . 'Controllers/LeafModule1.php');
        $leaf2 = new Module('LeafModule2', APPPATH . 'Controllers/LeafModule2.php');
        $core = new Module('CoreModule', APPPATH . 'Controllers/CoreModule.php');
        
        $this->inventory->addModule($leaf1);
        $this->inventory->addModule($leaf2);
        $this->inventory->addModule($core);
        
        // Set impact scores: leaf modules have 0, core has 3
        $this->graph->addNode('LeafModule1');
        $this->graph->addNode('LeafModule2');
        $this->graph->addNode('CoreModule');
        
        $this->graph->setImpactScore('LeafModule1', 0);
        $this->graph->setImpactScore('LeafModule2', 0);
        $this->graph->setImpactScore('CoreModule', 3);

        $leafModules = $this->prioritizer->identifyLeafModules();

        $this->assertCount(2, $leafModules);
        $this->assertContains('LeafModule1', $leafModules);
        $this->assertContains('LeafModule2', $leafModules);
        $this->assertNotContains('CoreModule', $leafModules);
    }

    /**
     * Test identifying core modules (many dependents)
     */
    public function testIdentifyCoreModules(): void
    {
        // Create modules with different impact scores
        $leaf = new Module('LeafModule', APPPATH . 'Controllers/LeafModule.php');
        $intermediate = new Module('IntermediateModule', APPPATH . 'Controllers/IntermediateModule.php');
        $core1 = new Module('CoreModule1', APPPATH . 'Controllers/CoreModule1.php');
        $core2 = new Module('CoreModule2', APPPATH . 'Controllers/CoreModule2.php');
        
        $this->inventory->addModule($leaf);
        $this->inventory->addModule($intermediate);
        $this->inventory->addModule($core1);
        $this->inventory->addModule($core2);
        
        // Set impact scores: core modules have >= 3 dependents
        $this->graph->addNode('LeafModule');
        $this->graph->addNode('IntermediateModule');
        $this->graph->addNode('CoreModule1');
        $this->graph->addNode('CoreModule2');
        
        $this->graph->setImpactScore('LeafModule', 0);
        $this->graph->setImpactScore('IntermediateModule', 2);
        $this->graph->setImpactScore('CoreModule1', 3);
        $this->graph->setImpactScore('CoreModule2', 5);

        $coreModules = $this->prioritizer->identifyCoreModules();

        $this->assertCount(2, $coreModules);
        $this->assertContains('CoreModule1', $coreModules);
        $this->assertContains('CoreModule2', $coreModules);
        $this->assertNotContains('LeafModule', $coreModules);
        $this->assertNotContains('IntermediateModule', $coreModules);
    }

    /**
     * Test calculating priority score for leaf module
     */
    public function testCalculatePriorityScoreForLeafModule(): void
    {
        $module = new Module('LeafModule', APPPATH . 'Controllers/LeafModule.php');
        $this->inventory->addModule($module);
        
        $this->graph->addNode('LeafModule');
        $this->graph->setImpactScore('LeafModule', 0);

        $score = $this->prioritizer->calculatePriorityScore('LeafModule');

        // Leaf module with no dependencies and no vulnerabilities should have score of 0
        $this->assertEquals(0.0, $score);
    }

    /**
     * Test calculating priority score with critical vulnerabilities
     */
    public function testCalculatePriorityScoreWithCriticalVulnerabilities(): void
    {
        $module = new Module('VulnerableModule', APPPATH . 'Controllers/VulnerableModule.php');
        $this->inventory->addModule($module);
        
        $this->graph->addNode('VulnerableModule');
        $this->graph->setImpactScore('VulnerableModule', 2);

        // Add security report with 2 critical vulnerabilities
        $securityReport = new SecurityReport('VulnerableModule');
        $securityReport->addVulnerability(new Vulnerability(
            Vulnerability::TYPE_SQL_INJECTION,
            Vulnerability::SEVERITY_CRITICAL,
            APPPATH . 'Controllers/VulnerableModule.php',
            45,
            'SQL injection',
            'Use Query Builder'
        ));
        $securityReport->addVulnerability(new Vulnerability(
            Vulnerability::TYPE_XSS,
            Vulnerability::SEVERITY_CRITICAL,
            APPPATH . 'Controllers/VulnerableModule.php',
            78,
            'XSS vulnerability',
            'Use esc() helper'
        ));

        $this->prioritizer->addSecurityReport($securityReport);

        $score = $this->prioritizer->calculatePriorityScore('VulnerableModule');

        // Score = 2 (impact) - (2 * 2) (critical vulns) = -2
        // Critical vulnerabilities should reduce score (increase priority)
        $this->assertLessThan(2.0, $score);
    }

    /**
     * Test calculating priority score with dependency depth
     */
    public function testCalculatePriorityScoreWithDependencyDepth(): void
    {
        // Create a chain: ModuleA -> ModuleB -> ModuleC
        // ModuleA depends on ModuleB, which depends on ModuleC
        $moduleA = new Module('ModuleA', APPPATH . 'Controllers/ModuleA.php');
        $moduleB = new Module('ModuleB', APPPATH . 'Controllers/ModuleB.php');
        $moduleC = new Module('ModuleC', APPPATH . 'Controllers/ModuleC.php');
        
        $this->inventory->addModule($moduleA);
        $this->inventory->addModule($moduleB);
        $this->inventory->addModule($moduleC);
        
        $this->graph->addEdge('ModuleA', 'ModuleB');
        $this->graph->addEdge('ModuleB', 'ModuleC');
        
        // All have same impact score to isolate depth effect
        $this->graph->setImpactScore('ModuleA', 0);
        $this->graph->setImpactScore('ModuleB', 0);
        $this->graph->setImpactScore('ModuleC', 0);

        $scoreA = $this->prioritizer->calculatePriorityScore('ModuleA');
        $scoreB = $this->prioritizer->calculatePriorityScore('ModuleB');
        $scoreC = $this->prioritizer->calculatePriorityScore('ModuleC');

        // ModuleA has depth 2 (depends on B which depends on C)
        // ModuleB has depth 1 (depends on C)
        // ModuleC has depth 0 (no dependencies)
        // Higher depth = higher score (lower priority)
        $this->assertGreaterThan($scoreC, $scoreB);
        $this->assertGreaterThan($scoreB, $scoreA);
    }

    /**
     * Test applying manual priority override
     */
    public function testApplyManualOverride(): void
    {
        $module = new Module('TestModule', APPPATH . 'Controllers/TestModule.php');
        $this->inventory->addModule($module);
        
        $this->graph->addNode('TestModule');
        $this->graph->setImpactScore('TestModule', 5);

        // Apply manual override
        $this->prioritizer->applyManualOverride('TestModule', 1);

        $priorityScore = $this->prioritizer->getPriorityScore('TestModule');

        $this->assertNotNull($priorityScore);
        $this->assertEquals(1, $priorityScore->manualPriority);
        $this->assertEquals(1, $priorityScore->getEffectivePriority());
    }

    /**
     * Test applying manual override with invalid priority throws exception
     */
    public function testApplyManualOverrideWithInvalidPriorityThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Priority must be at least 1');

        $this->prioritizer->applyManualOverride('TestModule', 0);
    }

    /**
     * Test prioritize returns modules in correct order
     */
    public function testPrioritizeReturnsModulesInCorrectOrder(): void
    {
        // Create modules with different characteristics
        $leaf = new Module('LeafModule', APPPATH . 'Controllers/LeafModule.php');
        $vulnerable = new Module('VulnerableModule', APPPATH . 'Controllers/VulnerableModule.php');
        $core = new Module('CoreModule', APPPATH . 'Controllers/CoreModule.php');
        
        $this->inventory->addModule($leaf);
        $this->inventory->addModule($vulnerable);
        $this->inventory->addModule($core);
        
        $this->graph->addNode('LeafModule');
        $this->graph->addNode('VulnerableModule');
        $this->graph->addNode('CoreModule');
        
        $this->graph->setImpactScore('LeafModule', 0);
        $this->graph->setImpactScore('VulnerableModule', 1);
        $this->graph->setImpactScore('CoreModule', 5);

        // Add critical vulnerability to VulnerableModule
        $securityReport = new SecurityReport('VulnerableModule');
        $securityReport->addVulnerability(new Vulnerability(
            Vulnerability::TYPE_SQL_INJECTION,
            Vulnerability::SEVERITY_CRITICAL,
            APPPATH . 'Controllers/VulnerableModule.php',
            45,
            'SQL injection',
            'Use Query Builder'
        ));
        $this->prioritizer->addSecurityReport($securityReport);

        $prioritizedScores = $this->prioritizer->prioritize();

        $this->assertCount(3, $prioritizedScores);
        $this->assertInstanceOf(PriorityScore::class, $prioritizedScores[0]);
        
        // VulnerableModule should be first (has critical vuln, reducing its score)
        $this->assertEquals('VulnerableModule', $prioritizedScores[0]->module);
        
        // LeafModule should be second (no dependents, no vulns)
        $this->assertEquals('LeafModule', $prioritizedScores[1]->module);
        
        // CoreModule should be last (many dependents, high risk)
        $this->assertEquals('CoreModule', $prioritizedScores[2]->module);
    }

    /**
     * Test prioritize with manual overrides takes precedence
     */
    public function testPrioritizeWithManualOverridesTakesPrecedence(): void
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
        $this->graph->setImpactScore('Module2', 2);
        $this->graph->setImpactScore('Module3', 5);

        // Apply manual override to make Module3 highest priority
        $this->prioritizer->applyManualOverride('Module3', 1);

        $prioritizedScores = $this->prioritizer->prioritize();

        // Module3 should be first due to manual override
        $this->assertEquals('Module3', $prioritizedScores[0]->module);
        $this->assertEquals(1, $prioritizedScores[0]->manualPriority);
    }

    /**
     * Test getRecommendedOrder returns module names only
     */
    public function testGetRecommendedOrderReturnsModuleNamesOnly(): void
    {
        $module1 = new Module('Module1', APPPATH . 'Controllers/Module1.php');
        $module2 = new Module('Module2', APPPATH . 'Controllers/Module2.php');
        
        $this->inventory->addModule($module1);
        $this->inventory->addModule($module2);
        
        $this->graph->addNode('Module1');
        $this->graph->addNode('Module2');
        
        $this->graph->setImpactScore('Module1', 0);
        $this->graph->setImpactScore('Module2', 3);

        $order = $this->prioritizer->getRecommendedOrder();

        $this->assertCount(2, $order);
        $this->assertIsString($order[0]);
        $this->assertIsString($order[1]);
        $this->assertEquals('Module1', $order[0]);
        $this->assertEquals('Module2', $order[1]);
    }

    /**
     * Test getPriorityScore returns correct score for module
     */
    public function testGetPriorityScoreReturnsCorrectScore(): void
    {
        $module = new Module('TestModule', APPPATH . 'Controllers/TestModule.php');
        $this->inventory->addModule($module);
        
        $this->graph->addNode('TestModule');
        $this->graph->setImpactScore('TestModule', 2);

        $priorityScore = $this->prioritizer->getPriorityScore('TestModule');

        $this->assertInstanceOf(PriorityScore::class, $priorityScore);
        $this->assertEquals('TestModule', $priorityScore->module);
        $this->assertEquals(2, $priorityScore->impactScore);
        $this->assertEquals(PriorityScore::CATEGORY_INTERMEDIATE, $priorityScore->category);
    }

    /**
     * Test getPriorityScore for non-existent module returns null
     */
    public function testGetPriorityScoreForNonExistentModuleReturnsNull(): void
    {
        $priorityScore = $this->prioritizer->getPriorityScore('NonExistent');

        $this->assertNull($priorityScore);
    }

    /**
     * Test getAllPriorityScores returns all scores
     */
    public function testGetAllPriorityScoresReturnsAllScores(): void
    {
        $module1 = new Module('Module1', APPPATH . 'Controllers/Module1.php');
        $module2 = new Module('Module2', APPPATH . 'Controllers/Module2.php');
        
        $this->inventory->addModule($module1);
        $this->inventory->addModule($module2);
        
        $this->graph->addNode('Module1');
        $this->graph->addNode('Module2');
        
        $this->graph->setImpactScore('Module1', 0);
        $this->graph->setImpactScore('Module2', 3);

        $allScores = $this->prioritizer->getAllPriorityScores();

        $this->assertCount(2, $allScores);
        $this->assertArrayHasKey('Module1', $allScores);
        $this->assertArrayHasKey('Module2', $allScores);
        $this->assertInstanceOf(PriorityScore::class, $allScores['Module1']);
        $this->assertInstanceOf(PriorityScore::class, $allScores['Module2']);
    }

    /**
     * Test PriorityScore category classification for leaf module
     */
    public function testPriorityScoreCategoryForLeafModule(): void
    {
        $module = new Module('LeafModule', APPPATH . 'Controllers/LeafModule.php');
        $this->inventory->addModule($module);
        
        $this->graph->addNode('LeafModule');
        $this->graph->setImpactScore('LeafModule', 0);

        $priorityScore = $this->prioritizer->getPriorityScore('LeafModule');

        $this->assertEquals(PriorityScore::CATEGORY_LEAF, $priorityScore->category);
        $this->assertTrue($priorityScore->isLeaf());
        $this->assertFalse($priorityScore->isCore());
    }

    /**
     * Test PriorityScore category classification for core module
     */
    public function testPriorityScoreCategoryForCoreModule(): void
    {
        $module = new Module('CoreModule', APPPATH . 'Controllers/CoreModule.php');
        $this->inventory->addModule($module);
        
        $this->graph->addNode('CoreModule');
        $this->graph->setImpactScore('CoreModule', 5);

        $priorityScore = $this->prioritizer->getPriorityScore('CoreModule');

        $this->assertEquals(PriorityScore::CATEGORY_CORE, $priorityScore->category);
        $this->assertTrue($priorityScore->isCore());
        $this->assertFalse($priorityScore->isLeaf());
    }

    /**
     * Test PriorityScore category classification for intermediate module
     */
    public function testPriorityScoreCategoryForIntermediateModule(): void
    {
        $module = new Module('IntermediateModule', APPPATH . 'Controllers/IntermediateModule.php');
        $this->inventory->addModule($module);
        
        $this->graph->addNode('IntermediateModule');
        $this->graph->setImpactScore('IntermediateModule', 2);

        $priorityScore = $this->prioritizer->getPriorityScore('IntermediateModule');

        $this->assertEquals(PriorityScore::CATEGORY_INTERMEDIATE, $priorityScore->category);
        $this->assertFalse($priorityScore->isLeaf());
        $this->assertFalse($priorityScore->isCore());
    }

    /**
     * Test prioritization with equal scores uses critical vulnerabilities as tiebreaker
     */
    public function testPrioritizationWithEqualScoresUsesCriticalVulnerabilitiesAsTiebreaker(): void
    {
        $module1 = new Module('Module1', APPPATH . 'Controllers/Module1.php');
        $module2 = new Module('Module2', APPPATH . 'Controllers/Module2.php');
        
        $this->inventory->addModule($module1);
        $this->inventory->addModule($module2);
        
        $this->graph->addNode('Module1');
        $this->graph->addNode('Module2');
        
        // Both have same impact score
        $this->graph->setImpactScore('Module1', 1);
        $this->graph->setImpactScore('Module2', 1);

        // Module2 has critical vulnerability
        $securityReport = new SecurityReport('Module2');
        $securityReport->addVulnerability(new Vulnerability(
            Vulnerability::TYPE_SQL_INJECTION,
            Vulnerability::SEVERITY_CRITICAL,
            APPPATH . 'Controllers/Module2.php',
            45,
            'SQL injection',
            'Use Query Builder'
        ));
        $this->prioritizer->addSecurityReport($securityReport);

        $prioritizedScores = $this->prioritizer->prioritize();

        // Module2 should be first due to critical vulnerability
        $this->assertEquals('Module2', $prioritizedScores[0]->module);
        $this->assertEquals('Module1', $prioritizedScores[1]->module);
    }

    /**
     * Test circular dependency handling in depth calculation
     */
    public function testCircularDependencyHandlingInDepthCalculation(): void
    {
        // Create circular dependency: A -> B -> C -> A
        $moduleA = new Module('ModuleA', APPPATH . 'Controllers/ModuleA.php');
        $moduleB = new Module('ModuleB', APPPATH . 'Controllers/ModuleB.php');
        $moduleC = new Module('ModuleC', APPPATH . 'Controllers/ModuleC.php');
        
        $this->inventory->addModule($moduleA);
        $this->inventory->addModule($moduleB);
        $this->inventory->addModule($moduleC);
        
        $this->graph->addEdge('ModuleA', 'ModuleB');
        $this->graph->addEdge('ModuleB', 'ModuleC');
        $this->graph->addEdge('ModuleC', 'ModuleA');
        
        $this->graph->setImpactScore('ModuleA', 1);
        $this->graph->setImpactScore('ModuleB', 1);
        $this->graph->setImpactScore('ModuleC', 1);

        // Should not cause infinite recursion
        $score = $this->prioritizer->calculatePriorityScore('ModuleA');

        $this->assertIsFloat($score);
    }

    /**
     * Test empty inventory returns empty results
     */
    public function testEmptyInventoryReturnsEmptyResults(): void
    {
        $leafModules = $this->prioritizer->identifyLeafModules();
        $coreModules = $this->prioritizer->identifyCoreModules();
        $prioritizedScores = $this->prioritizer->prioritize();
        $recommendedOrder = $this->prioritizer->getRecommendedOrder();

        $this->assertCount(0, $leafModules);
        $this->assertCount(0, $coreModules);
        $this->assertCount(0, $prioritizedScores);
        $this->assertCount(0, $recommendedOrder);
    }

    /**
     * Test complex prioritization scenario
     */
    public function testComplexPrioritizationScenario(): void
    {
        // Create a complex module structure
        $leaf1 = new Module('Leaf1', APPPATH . 'Controllers/Leaf1.php');
        $leaf2 = new Module('Leaf2', APPPATH . 'Controllers/Leaf2.php');
        $intermediate = new Module('Intermediate', APPPATH . 'Controllers/Intermediate.php');
        $core = new Module('Core', APPPATH . 'Controllers/Core.php');
        $vulnerable = new Module('Vulnerable', APPPATH . 'Controllers/Vulnerable.php');
        
        $this->inventory->addModule($leaf1);
        $this->inventory->addModule($leaf2);
        $this->inventory->addModule($intermediate);
        $this->inventory->addModule($core);
        $this->inventory->addModule($vulnerable);
        
        // Build dependency graph
        $this->graph->addEdge('Leaf1', 'Intermediate');
        $this->graph->addEdge('Leaf2', 'Intermediate');
        $this->graph->addEdge('Intermediate', 'Core');
        $this->graph->addEdge('Vulnerable', 'Core');
        
        $this->graph->setImpactScore('Leaf1', 0);
        $this->graph->setImpactScore('Leaf2', 0);
        $this->graph->setImpactScore('Intermediate', 2);
        $this->graph->setImpactScore('Core', 3);
        $this->graph->setImpactScore('Vulnerable', 0);

        // Add critical vulnerabilities to Vulnerable module
        $securityReport = new SecurityReport('Vulnerable');
        $securityReport->addVulnerability(new Vulnerability(
            Vulnerability::TYPE_SQL_INJECTION,
            Vulnerability::SEVERITY_CRITICAL,
            APPPATH . 'Controllers/Vulnerable.php',
            45,
            'SQL injection',
            'Use Query Builder'
        ));
        $securityReport->addVulnerability(new Vulnerability(
            Vulnerability::TYPE_XSS,
            Vulnerability::SEVERITY_CRITICAL,
            APPPATH . 'Controllers/Vulnerable.php',
            78,
            'XSS vulnerability',
            'Use esc() helper'
        ));
        $this->prioritizer->addSecurityReport($securityReport);

        // Apply manual override to Core (business critical)
        $this->prioritizer->applyManualOverride('Core', 1);

        $prioritizedScores = $this->prioritizer->prioritize();

        // Expected order:
        // 1. Core (manual override = 1, takes absolute precedence)
        // 2. Vulnerable (has critical vulns, reducing score significantly)
        // 3. Leaf1 or Leaf2 (both have score 0, no vulns, shallow depth)
        // 4. Leaf2 or Leaf1
        // 5. Intermediate (has dependents, deeper in tree)

        $this->assertCount(5, $prioritizedScores);
        $this->assertEquals('Core', $prioritizedScores[0]->module);
        $this->assertEquals('Vulnerable', $prioritizedScores[1]->module);
        
        // Last should be Intermediate (highest calculated score)
        $this->assertEquals('Intermediate', $prioritizedScores[4]->module);
    }
}

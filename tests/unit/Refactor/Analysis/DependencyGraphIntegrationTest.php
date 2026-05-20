<?php

namespace Tests\Unit\Refactor\Analysis;

use App\Libraries\Refactor\Models\DependencyGraph;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Integration tests for DependencyGraph with DependencyAnalyzer
 * 
 * Verifies that DependencyGraph works correctly when used by DependencyAnalyzer
 * to build and query dependency relationships.
 * 
 * @package Tests\Unit\Refactor\Analysis
 */
class DependencyGraphIntegrationTest extends CIUnitTestCase
{
    /**
     * Test building a realistic dependency graph
     * 
     * Simulates how DependencyAnalyzer would build a graph for a real application
     */
    public function testBuildingRealisticDependencyGraph(): void
    {
        $graph = new DependencyGraph();

        // Simulate DependencyAnalyzer adding modules and dependencies
        // Based on a typical CodeIgniter 4 application structure
        
        // Add all modules as nodes first
        $modules = ['Transaksi', 'Keuangan', 'Kavling', 'Konsumen', 'Karyawan', 'Proyek'];
        foreach ($modules as $module) {
            $graph->addNode($module);
        }

        // Add dependency edges (module A depends on module B)
        $graph->addEdge('Transaksi', 'Keuangan');  // Transaksi uses Keuangan
        $graph->addEdge('Transaksi', 'Kavling');   // Transaksi uses Kavling
        $graph->addEdge('Transaksi', 'Konsumen');  // Transaksi uses Konsumen
        $graph->addEdge('Keuangan', 'Konsumen');   // Keuangan uses Konsumen
        $graph->addEdge('Kavling', 'Proyek');      // Kavling uses Proyek
        $graph->addEdge('Proyek', 'Karyawan');     // Proyek uses Karyawan

        // Calculate impact scores (simulating DependencyAnalyzer.calculateImpactScores)
        foreach ($modules as $module) {
            $dependents = $graph->getDependents($module);
            $impactScore = count($dependents);
            $graph->setImpactScore($module, $impactScore);
        }

        // Verify the graph structure
        $this->assertCount(6, $graph->nodes);

        // Verify dependencies
        $this->assertCount(3, $graph->getDependencies('Transaksi'));
        $this->assertCount(1, $graph->getDependencies('Keuangan'));
        $this->assertCount(1, $graph->getDependencies('Kavling'));
        $this->assertCount(0, $graph->getDependencies('Konsumen')); // Leaf module

        // Verify dependents
        $this->assertCount(0, $graph->getDependents('Transaksi')); // No one depends on Transaksi
        $this->assertCount(2, $graph->getDependents('Konsumen')); // Transaksi and Keuangan depend on Konsumen
        $this->assertCount(1, $graph->getDependents('Proyek')); // Kavling depends on Proyek

        // Verify impact scores
        $this->assertEquals(0, $graph->getImpactScore('Transaksi')); // Leaf module (safe to refactor)
        $this->assertEquals(2, $graph->getImpactScore('Konsumen')); // Core module (high risk)
        $this->assertEquals(1, $graph->getImpactScore('Keuangan')); // Intermediate module
    }

    /**
     * Test identifying leaf modules (safe refactoring targets)
     */
    public function testIdentifyingLeafModules(): void
    {
        $graph = new DependencyGraph();

        // Build graph
        $graph->addEdge('Transaksi', 'Keuangan');
        $graph->addEdge('Transaksi', 'Kavling');
        $graph->addEdge('Keuangan', 'Konsumen');

        // Calculate impact scores
        foreach ($graph->nodes as $module) {
            $dependents = $graph->getDependents($module);
            $graph->setImpactScore($module, count($dependents));
        }

        // Identify leaf modules (impact score = 0)
        $leafModules = [];
        foreach ($graph->nodes as $module) {
            if ($graph->getImpactScore($module) === 0) {
                $leafModules[] = $module;
            }
        }

        // Transaksi is a leaf module (no one depends on it)
        $this->assertContains('Transaksi', $leafModules);
        $this->assertCount(1, $leafModules);
    }

    /**
     * Test identifying core modules (high-risk refactoring targets)
     */
    public function testIdentifyingCoreModules(): void
    {
        $graph = new DependencyGraph();

        // Build graph where Konsumen is a core module
        $graph->addEdge('Transaksi', 'Konsumen');
        $graph->addEdge('Keuangan', 'Konsumen');
        $graph->addEdge('Kavling', 'Konsumen');
        $graph->addEdge('Proyek', 'Konsumen');

        // Calculate impact scores
        foreach ($graph->nodes as $module) {
            $dependents = $graph->getDependents($module);
            $graph->setImpactScore($module, count($dependents));
        }

        // Konsumen has 4 dependents (high impact)
        $this->assertEquals(4, $graph->getImpactScore('Konsumen'));

        // All other modules have 0 dependents
        $this->assertEquals(0, $graph->getImpactScore('Transaksi'));
        $this->assertEquals(0, $graph->getImpactScore('Keuangan'));
        $this->assertEquals(0, $graph->getImpactScore('Kavling'));
        $this->assertEquals(0, $graph->getImpactScore('Proyek'));
    }

    /**
     * Test generating Mermaid diagram for visualization
     */
    public function testGeneratingMermaidDiagramForVisualization(): void
    {
        $graph = new DependencyGraph();

        // Build a simple graph
        $graph->addEdge('Transaksi', 'Keuangan');
        $graph->addEdge('Transaksi', 'Kavling');
        $graph->addEdge('Keuangan', 'Konsumen');

        $mermaid = $graph->toMermaid();

        // Verify Mermaid syntax
        $this->assertStringContainsString('graph TD', $mermaid);
        $this->assertStringContainsString('Transaksi --> Keuangan', $mermaid);
        $this->assertStringContainsString('Transaksi --> Kavling', $mermaid);
        $this->assertStringContainsString('Keuangan --> Konsumen', $mermaid);

        // Verify it's valid Mermaid syntax (can be rendered)
        $lines = explode("\n", $mermaid);
        $this->assertEquals('graph TD', $lines[0]);
        $this->assertGreaterThan(1, count($lines));
    }

    /**
     * Test persisting and loading graph from JSON
     */
    public function testPersistingAndLoadingGraphFromJson(): void
    {
        $graph = new DependencyGraph();

        // Build graph
        $graph->addEdge('Transaksi', 'Keuangan');
        $graph->addEdge('Transaksi', 'Kavling');
        $graph->addEdge('Keuangan', 'Konsumen');

        // Calculate impact scores
        foreach ($graph->nodes as $module) {
            $dependents = $graph->getDependents($module);
            $graph->setImpactScore($module, count($dependents));
        }

        // Add circular dependency tracking
        $graph->circular = [['ModuleA', 'ModuleB', 'ModuleA']];

        // Serialize to JSON
        $json = $graph->toJson();

        // Verify JSON is valid
        $this->assertJson($json);

        // Deserialize from JSON
        $loadedGraph = DependencyGraph::fromJson($json);

        // Verify all data is preserved
        $this->assertEquals($graph->nodes, $loadedGraph->nodes);
        $this->assertEquals($graph->edges, $loadedGraph->edges);
        $this->assertEquals($graph->impactScores, $loadedGraph->impactScores);
        $this->assertEquals($graph->circular, $loadedGraph->circular);

        // Verify queries work on loaded graph
        $this->assertEquals(
            $graph->getDependencies('Transaksi'),
            $loadedGraph->getDependencies('Transaksi')
        );
        $this->assertEquals(
            $graph->getDependents('Konsumen'),
            $loadedGraph->getDependents('Konsumen')
        );
        $this->assertEquals(
            $graph->getImpactScore('Keuangan'),
            $loadedGraph->getImpactScore('Keuangan')
        );
    }

    /**
     * Test calculating refactoring order based on impact scores
     */
    public function testCalculatingRefactoringOrderBasedOnImpactScores(): void
    {
        $graph = new DependencyGraph();

        // Build graph
        $graph->addEdge('Transaksi', 'Keuangan');
        $graph->addEdge('Transaksi', 'Kavling');
        $graph->addEdge('Keuangan', 'Konsumen');
        $graph->addEdge('Kavling', 'Konsumen');

        // Calculate impact scores
        foreach ($graph->nodes as $module) {
            $dependents = $graph->getDependents($module);
            $graph->setImpactScore($module, count($dependents));
        }

        // Sort modules by impact score (ascending - lowest risk first)
        $moduleScores = [];
        foreach ($graph->nodes as $module) {
            $moduleScores[$module] = $graph->getImpactScore($module);
        }
        asort($moduleScores);

        $refactoringOrder = array_keys($moduleScores);

        // Verify order: Transaksi (0) should be first, Konsumen (2) should be last
        $this->assertEquals('Transaksi', $refactoringOrder[0]);
        $this->assertEquals('Konsumen', $refactoringOrder[count($refactoringOrder) - 1]);

        // Verify Keuangan and Kavling are in the middle (both have score 1)
        $middleModules = array_slice($refactoringOrder, 1, 2);
        $this->assertContains('Keuangan', $middleModules);
        $this->assertContains('Kavling', $middleModules);
    }

    /**
     * Test handling circular dependencies
     */
    public function testHandlingCircularDependencies(): void
    {
        $graph = new DependencyGraph();

        // Build graph with circular dependency
        $graph->addEdge('ModuleA', 'ModuleB');
        $graph->addEdge('ModuleB', 'ModuleC');
        $graph->addEdge('ModuleC', 'ModuleA'); // Creates cycle

        // Manually set circular dependencies (normally done by DependencyAnalyzer)
        $graph->circular = [
            ['ModuleA', 'ModuleB', 'ModuleC', 'ModuleA']
        ];

        // Verify circular dependency is tracked
        $this->assertCount(1, $graph->circular);
        $this->assertEquals(['ModuleA', 'ModuleB', 'ModuleC', 'ModuleA'], $graph->circular[0]);

        // Verify graph structure is still valid
        $this->assertCount(3, $graph->nodes);
        $this->assertCount(1, $graph->getDependencies('ModuleA'));
        $this->assertCount(1, $graph->getDependencies('ModuleB'));
        $this->assertCount(1, $graph->getDependencies('ModuleC'));
    }

    /**
     * Test impact analysis for refactoring decision
     */
    public function testImpactAnalysisForRefactoringDecision(): void
    {
        $graph = new DependencyGraph();

        // Build realistic graph
        $graph->addEdge('Transaksi', 'Keuangan');
        $graph->addEdge('Transaksi', 'Kavling');
        $graph->addEdge('Transaksi', 'Konsumen');
        $graph->addEdge('CashOut', 'Keuangan');
        $graph->addEdge('CashOut', 'Konsumen');
        $graph->addEdge('Keuangan', 'Konsumen');

        // Calculate impact scores
        foreach ($graph->nodes as $module) {
            $dependents = $graph->getDependents($module);
            $graph->setImpactScore($module, count($dependents));
        }

        // Analyze impact of refactoring Konsumen
        $konsumenDependents = $graph->getDependents('Konsumen');
        $konsumenImpact = $graph->getImpactScore('Konsumen');

        // Konsumen is used by 3 modules (high impact)
        $this->assertEquals(3, $konsumenImpact);
        $this->assertCount(3, $konsumenDependents);
        $this->assertContains('Transaksi', $konsumenDependents);
        $this->assertContains('CashOut', $konsumenDependents);
        $this->assertContains('Keuangan', $konsumenDependents);

        // Analyze impact of refactoring Transaksi
        $transaksiDependents = $graph->getDependents('Transaksi');
        $transaksiImpact = $graph->getImpactScore('Transaksi');

        // Transaksi has no dependents (safe to refactor)
        $this->assertEquals(0, $transaksiImpact);
        $this->assertEmpty($transaksiDependents);

        // Decision: Refactor Transaksi first (low risk), Konsumen last (high risk)
        $this->assertLessThan($konsumenImpact, $transaksiImpact);
    }
}

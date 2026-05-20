<?php

namespace Tests\Unit\Refactor\Analysis;

use App\Libraries\Refactor\Models\DependencyGraph;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Unit tests for DependencyGraph data model
 * 
 * Tests the DependencyGraph class functionality including:
 * - Node and edge management
 * - Dependency queries (getDependencies, getDependents)
 * - Impact score management
 * - Mermaid diagram generation
 * - JSON serialization/deserialization
 * 
 * @package Tests\Unit\Refactor\Analysis
 */
class DependencyGraphTest extends CIUnitTestCase
{
    private DependencyGraph $graph;

    protected function setUp(): void
    {
        parent::setUp();
        $this->graph = new DependencyGraph();
    }

    /**
     * Test adding nodes to the graph
     */
    public function testAddNode(): void
    {
        $this->graph->addNode('ModuleA');
        $this->graph->addNode('ModuleB');

        $this->assertCount(2, $this->graph->nodes);
        $this->assertContains('ModuleA', $this->graph->nodes);
        $this->assertContains('ModuleB', $this->graph->nodes);
    }

    /**
     * Test that duplicate nodes are not added
     */
    public function testAddNodePreventsDuplicates(): void
    {
        $this->graph->addNode('ModuleA');
        $this->graph->addNode('ModuleA');
        $this->graph->addNode('ModuleA');

        $this->assertCount(1, $this->graph->nodes);
        $this->assertContains('ModuleA', $this->graph->nodes);
    }

    /**
     * Test adding edges to the graph
     */
    public function testAddEdge(): void
    {
        $this->graph->addEdge('ModuleA', 'ModuleB');

        $this->assertCount(2, $this->graph->nodes);
        $this->assertArrayHasKey('ModuleA', $this->graph->edges);
        $this->assertContains('ModuleB', $this->graph->edges['ModuleA']);
    }

    /**
     * Test that adding an edge automatically adds nodes
     */
    public function testAddEdgeAutomaticallyAddsNodes(): void
    {
        $this->graph->addEdge('ModuleA', 'ModuleB');

        $this->assertContains('ModuleA', $this->graph->nodes);
        $this->assertContains('ModuleB', $this->graph->nodes);
    }

    /**
     * Test that duplicate edges are not added
     */
    public function testAddEdgePreventsDuplicates(): void
    {
        $this->graph->addEdge('ModuleA', 'ModuleB');
        $this->graph->addEdge('ModuleA', 'ModuleB');
        $this->graph->addEdge('ModuleA', 'ModuleB');

        $this->assertCount(1, $this->graph->edges['ModuleA']);
        $this->assertContains('ModuleB', $this->graph->edges['ModuleA']);
    }

    /**
     * Test adding multiple edges from the same module
     */
    public function testAddMultipleEdgesFromSameModule(): void
    {
        $this->graph->addEdge('ModuleA', 'ModuleB');
        $this->graph->addEdge('ModuleA', 'ModuleC');
        $this->graph->addEdge('ModuleA', 'ModuleD');

        $this->assertCount(3, $this->graph->edges['ModuleA']);
        $this->assertContains('ModuleB', $this->graph->edges['ModuleA']);
        $this->assertContains('ModuleC', $this->graph->edges['ModuleA']);
        $this->assertContains('ModuleD', $this->graph->edges['ModuleA']);
    }

    /**
     * Test getDependencies returns correct dependencies
     */
    public function testGetDependencies(): void
    {
        $this->graph->addEdge('ModuleA', 'ModuleB');
        $this->graph->addEdge('ModuleA', 'ModuleC');

        $dependencies = $this->graph->getDependencies('ModuleA');

        $this->assertCount(2, $dependencies);
        $this->assertContains('ModuleB', $dependencies);
        $this->assertContains('ModuleC', $dependencies);
    }

    /**
     * Test getDependencies returns empty array for module with no dependencies
     */
    public function testGetDependenciesReturnsEmptyArrayForNoDependencies(): void
    {
        $this->graph->addNode('ModuleA');

        $dependencies = $this->graph->getDependencies('ModuleA');

        $this->assertIsArray($dependencies);
        $this->assertEmpty($dependencies);
    }

    /**
     * Test getDependencies returns empty array for non-existent module
     */
    public function testGetDependenciesReturnsEmptyArrayForNonExistentModule(): void
    {
        $dependencies = $this->graph->getDependencies('NonExistent');

        $this->assertIsArray($dependencies);
        $this->assertEmpty($dependencies);
    }

    /**
     * Test getDependents returns correct dependents
     */
    public function testGetDependents(): void
    {
        // ModuleA depends on ModuleC
        // ModuleB depends on ModuleC
        // So ModuleC has dependents: ModuleA and ModuleB
        $this->graph->addEdge('ModuleA', 'ModuleC');
        $this->graph->addEdge('ModuleB', 'ModuleC');

        $dependents = $this->graph->getDependents('ModuleC');

        $this->assertCount(2, $dependents);
        $this->assertContains('ModuleA', $dependents);
        $this->assertContains('ModuleB', $dependents);
    }

    /**
     * Test getDependents returns empty array for module with no dependents
     */
    public function testGetDependentsReturnsEmptyArrayForNoDependents(): void
    {
        $this->graph->addNode('ModuleA');

        $dependents = $this->graph->getDependents('ModuleA');

        $this->assertIsArray($dependents);
        $this->assertEmpty($dependents);
    }

    /**
     * Test getDependents returns empty array for non-existent module
     */
    public function testGetDependentsReturnsEmptyArrayForNonExistentModule(): void
    {
        $dependents = $this->graph->getDependents('NonExistent');

        $this->assertIsArray($dependents);
        $this->assertEmpty($dependents);
    }

    /**
     * Test complex dependency graph
     */
    public function testComplexDependencyGraph(): void
    {
        // Build a complex graph:
        // Transaksi -> Keuangan
        // Transaksi -> Kavling
        // Keuangan -> Konsumen
        // Kavling -> Konsumen
        $this->graph->addEdge('Transaksi', 'Keuangan');
        $this->graph->addEdge('Transaksi', 'Kavling');
        $this->graph->addEdge('Keuangan', 'Konsumen');
        $this->graph->addEdge('Kavling', 'Konsumen');

        // Test Transaksi dependencies
        $transaksiDeps = $this->graph->getDependencies('Transaksi');
        $this->assertCount(2, $transaksiDeps);
        $this->assertContains('Keuangan', $transaksiDeps);
        $this->assertContains('Kavling', $transaksiDeps);

        // Test Konsumen dependents
        $konsumenDependents = $this->graph->getDependents('Konsumen');
        $this->assertCount(2, $konsumenDependents);
        $this->assertContains('Keuangan', $konsumenDependents);
        $this->assertContains('Kavling', $konsumenDependents);

        // Test Keuangan
        $keuanganDeps = $this->graph->getDependencies('Keuangan');
        $this->assertCount(1, $keuanganDeps);
        $this->assertContains('Konsumen', $keuanganDeps);

        $keuanganDependents = $this->graph->getDependents('Keuangan');
        $this->assertCount(1, $keuanganDependents);
        $this->assertContains('Transaksi', $keuanganDependents);
    }

    /**
     * Test setting and getting impact scores
     */
    public function testSetAndGetImpactScore(): void
    {
        $this->graph->addNode('ModuleA');
        $this->graph->setImpactScore('ModuleA', 5);

        $score = $this->graph->getImpactScore('ModuleA');

        $this->assertEquals(5, $score);
    }

    /**
     * Test getImpactScore returns 0 for module without score
     */
    public function testGetImpactScoreReturnsZeroForModuleWithoutScore(): void
    {
        $this->graph->addNode('ModuleA');

        $score = $this->graph->getImpactScore('ModuleA');

        $this->assertEquals(0, $score);
    }

    /**
     * Test getImpactScore returns 0 for non-existent module
     */
    public function testGetImpactScoreReturnsZeroForNonExistentModule(): void
    {
        $score = $this->graph->getImpactScore('NonExistent');

        $this->assertEquals(0, $score);
    }

    /**
     * Test toMermaid generates correct diagram for simple graph
     */
    public function testToMermaidSimpleGraph(): void
    {
        $this->graph->addEdge('ModuleA', 'ModuleB');
        $this->graph->addEdge('ModuleB', 'ModuleC');

        $mermaid = $this->graph->toMermaid();

        $this->assertStringContainsString('graph TD', $mermaid);
        $this->assertStringContainsString('ModuleA --> ModuleB', $mermaid);
        $this->assertStringContainsString('ModuleB --> ModuleC', $mermaid);
    }

    /**
     * Test toMermaid includes isolated nodes
     */
    public function testToMermaidIncludesIsolatedNodes(): void
    {
        $this->graph->addNode('IsolatedModule');
        $this->graph->addEdge('ModuleA', 'ModuleB');

        $mermaid = $this->graph->toMermaid();

        $this->assertStringContainsString('graph TD', $mermaid);
        $this->assertStringContainsString('ModuleA --> ModuleB', $mermaid);
        $this->assertStringContainsString('IsolatedModule', $mermaid);
    }

    /**
     * Test toMermaid for complex graph
     */
    public function testToMermaidComplexGraph(): void
    {
        $this->graph->addEdge('Transaksi', 'Keuangan');
        $this->graph->addEdge('Transaksi', 'Kavling');
        $this->graph->addEdge('Keuangan', 'Konsumen');

        $mermaid = $this->graph->toMermaid();

        $this->assertStringContainsString('graph TD', $mermaid);
        $this->assertStringContainsString('Transaksi --> Keuangan', $mermaid);
        $this->assertStringContainsString('Transaksi --> Kavling', $mermaid);
        $this->assertStringContainsString('Keuangan --> Konsumen', $mermaid);
    }

    /**
     * Test toJson serialization
     */
    public function testToJson(): void
    {
        $this->graph->addEdge('ModuleA', 'ModuleB');
        $this->graph->addEdge('ModuleA', 'ModuleC');
        $this->graph->setImpactScore('ModuleA', 0);
        $this->graph->setImpactScore('ModuleB', 1);
        $this->graph->setImpactScore('ModuleC', 1);
        $this->graph->circular = [['ModuleX', 'ModuleY', 'ModuleX']];

        $json = $this->graph->toJson();
        $data = json_decode($json, true);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('nodes', $data);
        $this->assertArrayHasKey('edges', $data);
        $this->assertArrayHasKey('impactScores', $data);
        $this->assertArrayHasKey('circular', $data);

        $this->assertCount(3, $data['nodes']);
        $this->assertContains('ModuleA', $data['nodes']);
        $this->assertContains('ModuleB', $data['nodes']);
        $this->assertContains('ModuleC', $data['nodes']);

        $this->assertArrayHasKey('ModuleA', $data['edges']);
        $this->assertCount(2, $data['edges']['ModuleA']);
        $this->assertContains('ModuleB', $data['edges']['ModuleA']);
        $this->assertContains('ModuleC', $data['edges']['ModuleA']);

        $this->assertEquals(0, $data['impactScores']['ModuleA']);
        $this->assertEquals(1, $data['impactScores']['ModuleB']);
        $this->assertEquals(1, $data['impactScores']['ModuleC']);

        $this->assertCount(1, $data['circular']);
        $this->assertEquals(['ModuleX', 'ModuleY', 'ModuleX'], $data['circular'][0]);
    }

    /**
     * Test fromJson deserialization
     */
    public function testFromJson(): void
    {
        $json = json_encode([
            'nodes' => ['ModuleA', 'ModuleB', 'ModuleC'],
            'edges' => [
                'ModuleA' => ['ModuleB', 'ModuleC'],
                'ModuleB' => ['ModuleC']
            ],
            'impactScores' => [
                'ModuleA' => 0,
                'ModuleB' => 1,
                'ModuleC' => 2
            ],
            'circular' => [
                ['ModuleX', 'ModuleY', 'ModuleX']
            ]
        ]);

        $graph = DependencyGraph::fromJson($json);

        $this->assertCount(3, $graph->nodes);
        $this->assertContains('ModuleA', $graph->nodes);
        $this->assertContains('ModuleB', $graph->nodes);
        $this->assertContains('ModuleC', $graph->nodes);

        $this->assertArrayHasKey('ModuleA', $graph->edges);
        $this->assertCount(2, $graph->edges['ModuleA']);
        $this->assertContains('ModuleB', $graph->edges['ModuleA']);
        $this->assertContains('ModuleC', $graph->edges['ModuleA']);

        $this->assertEquals(0, $graph->getImpactScore('ModuleA'));
        $this->assertEquals(1, $graph->getImpactScore('ModuleB'));
        $this->assertEquals(2, $graph->getImpactScore('ModuleC'));

        $this->assertCount(1, $graph->circular);
        $this->assertEquals(['ModuleX', 'ModuleY', 'ModuleX'], $graph->circular[0]);
    }

    /**
     * Test JSON round-trip (serialize then deserialize)
     */
    public function testJsonRoundTrip(): void
    {
        // Build original graph
        $this->graph->addEdge('Transaksi', 'Keuangan');
        $this->graph->addEdge('Transaksi', 'Kavling');
        $this->graph->addEdge('Keuangan', 'Konsumen');
        $this->graph->setImpactScore('Transaksi', 0);
        $this->graph->setImpactScore('Keuangan', 1);
        $this->graph->setImpactScore('Kavling', 1);
        $this->graph->setImpactScore('Konsumen', 2);
        $this->graph->circular = [];

        // Serialize to JSON
        $json = $this->graph->toJson();

        // Deserialize from JSON
        $restoredGraph = DependencyGraph::fromJson($json);

        // Verify all data is preserved
        $this->assertEquals($this->graph->nodes, $restoredGraph->nodes);
        $this->assertEquals($this->graph->edges, $restoredGraph->edges);
        $this->assertEquals($this->graph->impactScores, $restoredGraph->impactScores);
        $this->assertEquals($this->graph->circular, $restoredGraph->circular);
    }

    /**
     * Test fromJson handles empty graph
     */
    public function testFromJsonHandlesEmptyGraph(): void
    {
        $json = json_encode([
            'nodes' => [],
            'edges' => [],
            'impactScores' => [],
            'circular' => []
        ]);

        $graph = DependencyGraph::fromJson($json);

        $this->assertEmpty($graph->nodes);
        $this->assertEmpty($graph->edges);
        $this->assertEmpty($graph->impactScores);
        $this->assertEmpty($graph->circular);
    }

    /**
     * Test fromJson handles missing fields gracefully
     */
    public function testFromJsonHandlesMissingFields(): void
    {
        $json = json_encode([
            'nodes' => ['ModuleA']
        ]);

        $graph = DependencyGraph::fromJson($json);

        $this->assertCount(1, $graph->nodes);
        $this->assertEmpty($graph->edges);
        $this->assertEmpty($graph->impactScores);
        $this->assertEmpty($graph->circular);
    }

    /**
     * Test circular dependencies tracking
     */
    public function testCircularDependenciesTracking(): void
    {
        $this->graph->circular = [
            ['ModuleA', 'ModuleB', 'ModuleC', 'ModuleA'],
            ['ModuleX', 'ModuleY', 'ModuleX']
        ];

        $this->assertCount(2, $this->graph->circular);
        $this->assertEquals(['ModuleA', 'ModuleB', 'ModuleC', 'ModuleA'], $this->graph->circular[0]);
        $this->assertEquals(['ModuleX', 'ModuleY', 'ModuleX'], $this->graph->circular[1]);
    }

    /**
     * Test impact scores for leaf modules (no dependents)
     */
    public function testImpactScoresForLeafModules(): void
    {
        // Build graph where Transaksi is a leaf (no one depends on it)
        $this->graph->addEdge('Transaksi', 'Keuangan');
        $this->graph->addEdge('Transaksi', 'Kavling');
        $this->graph->setImpactScore('Transaksi', 0);
        $this->graph->setImpactScore('Keuangan', 1);
        $this->graph->setImpactScore('Kavling', 1);

        $this->assertEquals(0, $this->graph->getImpactScore('Transaksi'));
        $this->assertEquals(1, $this->graph->getImpactScore('Keuangan'));
        $this->assertEquals(1, $this->graph->getImpactScore('Kavling'));
    }

    /**
     * Test impact scores for core modules (many dependents)
     */
    public function testImpactScoresForCoreModules(): void
    {
        // Build graph where Konsumen is a core module (many depend on it)
        $this->graph->addEdge('Transaksi', 'Konsumen');
        $this->graph->addEdge('Keuangan', 'Konsumen');
        $this->graph->addEdge('Kavling', 'Konsumen');
        $this->graph->setImpactScore('Konsumen', 3);
        $this->graph->setImpactScore('Transaksi', 0);
        $this->graph->setImpactScore('Keuangan', 0);
        $this->graph->setImpactScore('Kavling', 0);

        $this->assertEquals(3, $this->graph->getImpactScore('Konsumen'));
        $this->assertEquals(0, $this->graph->getImpactScore('Transaksi'));
        $this->assertEquals(0, $this->graph->getImpactScore('Keuangan'));
        $this->assertEquals(0, $this->graph->getImpactScore('Kavling'));
    }
}

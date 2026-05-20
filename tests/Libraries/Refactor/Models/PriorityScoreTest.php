<?php

namespace Tests\Libraries\Refactor\Models;

use App\Libraries\Refactor\Models\PriorityScore;
use CodeIgniter\Test\CIUnitTestCase;
use JsonException;

/**
 * PriorityScore Model Test
 * 
 * Tests the PriorityScore data model to ensure it correctly stores
 * and manages priority scoring information for modules.
 */
class PriorityScoreTest extends CIUnitTestCase
{
    /**
     * Test creating a PriorityScore instance with all properties
     */
    public function testCreatePriorityScore(): void
    {
        $score = new PriorityScore(
            module: 'TestModule',
            score: 10.5,
            impactScore: 3,
            dependencyDepth: 2,
            criticalVulnerabilities: 1,
            category: PriorityScore::CATEGORY_LEAF
        );

        $this->assertSame('TestModule', $score->module);
        $this->assertSame(10.5, $score->score);
        $this->assertSame(3, $score->impactScore);
        $this->assertSame(2, $score->dependencyDepth);
        $this->assertSame(1, $score->criticalVulnerabilities);
        $this->assertSame(PriorityScore::CATEGORY_LEAF, $score->category);
        $this->assertNull($score->manualPriority);
    }

    /**
     * Test isLeaf() method returns true for LEAF category
     */
    public function testIsLeafReturnsTrueForLeafCategory(): void
    {
        $score = new PriorityScore(
            module: 'LeafModule',
            score: 5.0,
            impactScore: 0,
            dependencyDepth: 1,
            criticalVulnerabilities: 0,
            category: PriorityScore::CATEGORY_LEAF
        );

        $this->assertTrue($score->isLeaf());
        $this->assertFalse($score->isCore());
    }

    /**
     * Test isCore() method returns true for CORE category
     */
    public function testIsCoreReturnsTrueForCoreCategory(): void
    {
        $score = new PriorityScore(
            module: 'CoreModule',
            score: 100.0,
            impactScore: 15,
            dependencyDepth: 5,
            criticalVulnerabilities: 2,
            category: PriorityScore::CATEGORY_CORE
        );

        $this->assertTrue($score->isCore());
        $this->assertFalse($score->isLeaf());
    }

    /**
     * Test INTERMEDIATE category
     */
    public function testIntermediateCategory(): void
    {
        $score = new PriorityScore(
            module: 'IntermediateModule',
            score: 50.0,
            impactScore: 5,
            dependencyDepth: 3,
            criticalVulnerabilities: 1,
            category: PriorityScore::CATEGORY_INTERMEDIATE
        );

        $this->assertFalse($score->isLeaf());
        $this->assertFalse($score->isCore());
        $this->assertSame(PriorityScore::CATEGORY_INTERMEDIATE, $score->category);
    }

    /**
     * Test getEffectivePriority() returns score when no manual override
     */
    public function testGetEffectivePriorityReturnsScoreWithoutOverride(): void
    {
        $score = new PriorityScore(
            module: 'TestModule',
            score: 25.5,
            impactScore: 2,
            dependencyDepth: 1,
            criticalVulnerabilities: 0,
            category: PriorityScore::CATEGORY_INTERMEDIATE
        );

        $this->assertSame(25.5, $score->getEffectivePriority());
    }

    /**
     * Test getEffectivePriority() returns manual priority when set
     */
    public function testGetEffectivePriorityReturnsManualOverride(): void
    {
        $score = new PriorityScore(
            module: 'TestModule',
            score: 25.5,
            impactScore: 2,
            dependencyDepth: 1,
            criticalVulnerabilities: 0,
            category: PriorityScore::CATEGORY_INTERMEDIATE
        );

        $score->setManualPriority(1);

        $this->assertSame(1, $score->getEffectivePriority());
        $this->assertSame(25.5, $score->score); // Original score unchanged
    }

    /**
     * Test setManualPriority() sets the manual priority
     */
    public function testSetManualPriority(): void
    {
        $score = new PriorityScore(
            module: 'TestModule',
            score: 10.0,
            impactScore: 1,
            dependencyDepth: 1,
            criticalVulnerabilities: 0,
            category: PriorityScore::CATEGORY_LEAF
        );

        $this->assertNull($score->manualPriority);
        $this->assertFalse($score->hasManualOverride());

        $score->setManualPriority(5);

        $this->assertSame(5, $score->manualPriority);
        $this->assertTrue($score->hasManualOverride());
    }

    /**
     * Test setManualPriority() can clear the override with null
     */
    public function testSetManualPriorityCanClearOverride(): void
    {
        $score = new PriorityScore(
            module: 'TestModule',
            score: 10.0,
            impactScore: 1,
            dependencyDepth: 1,
            criticalVulnerabilities: 0,
            category: PriorityScore::CATEGORY_LEAF
        );

        $score->setManualPriority(5);
        $this->assertTrue($score->hasManualOverride());

        $score->setManualPriority(null);
        $this->assertFalse($score->hasManualOverride());
        $this->assertNull($score->manualPriority);
    }

    /**
     * Test hasManualOverride() returns correct boolean
     */
    public function testHasManualOverride(): void
    {
        $score = new PriorityScore(
            module: 'TestModule',
            score: 10.0,
            impactScore: 1,
            dependencyDepth: 1,
            criticalVulnerabilities: 0,
            category: PriorityScore::CATEGORY_LEAF
        );

        $this->assertFalse($score->hasManualOverride());

        $score->setManualPriority(10);
        $this->assertTrue($score->hasManualOverride());

        $score->setManualPriority(null);
        $this->assertFalse($score->hasManualOverride());
    }

    /**
     * Test toArray() returns correct array structure
     */
    public function testToArrayReturnsCorrectStructure(): void
    {
        $score = new PriorityScore(
            module: 'TestModule',
            score: 15.5,
            impactScore: 3,
            dependencyDepth: 2,
            criticalVulnerabilities: 1,
            category: PriorityScore::CATEGORY_INTERMEDIATE
        );

        $array = $score->toArray();

        $this->assertIsArray($array);
        $this->assertArrayHasKey('module', $array);
        $this->assertArrayHasKey('score', $array);
        $this->assertArrayHasKey('impactScore', $array);
        $this->assertArrayHasKey('dependencyDepth', $array);
        $this->assertArrayHasKey('criticalVulnerabilities', $array);
        $this->assertArrayHasKey('category', $array);
        $this->assertArrayHasKey('manualPriority', $array);

        $this->assertSame('TestModule', $array['module']);
        $this->assertSame(15.5, $array['score']);
        $this->assertSame(3, $array['impactScore']);
        $this->assertSame(2, $array['dependencyDepth']);
        $this->assertSame(1, $array['criticalVulnerabilities']);
        $this->assertSame(PriorityScore::CATEGORY_INTERMEDIATE, $array['category']);
        $this->assertNull($array['manualPriority']);
    }

    /**
     * Test toArray() includes manual priority when set
     */
    public function testToArrayIncludesManualPriority(): void
    {
        $score = new PriorityScore(
            module: 'TestModule',
            score: 15.5,
            impactScore: 3,
            dependencyDepth: 2,
            criticalVulnerabilities: 1,
            category: PriorityScore::CATEGORY_INTERMEDIATE
        );

        $score->setManualPriority(1);
        $array = $score->toArray();

        $this->assertSame(1, $array['manualPriority']);
    }

    /**
     * Test toJson() returns valid JSON string
     */
    public function testToJsonReturnsValidJson(): void
    {
        $score = new PriorityScore(
            module: 'TestModule',
            score: 20.5,
            impactScore: 4,
            dependencyDepth: 3,
            criticalVulnerabilities: 2,
            category: PriorityScore::CATEGORY_CORE
        );

        $json = $score->toJson();

        $this->assertIsString($json);
        $this->assertJson($json);

        $decoded = json_decode($json, true);
        $this->assertSame('TestModule', $decoded['module']);
        $this->assertSame(20.5, $decoded['score']);
        $this->assertSame(4, $decoded['impactScore']);
        $this->assertSame(3, $decoded['dependencyDepth']);
        $this->assertSame(2, $decoded['criticalVulnerabilities']);
        $this->assertSame(PriorityScore::CATEGORY_CORE, $decoded['category']);
        $this->assertNull($decoded['manualPriority']);
    }

    /**
     * Test fromArray() creates correct instance
     */
    public function testFromArrayCreatesCorrectInstance(): void
    {
        $data = [
            'module' => 'TestModule',
            'score' => 30.5,
            'impactScore' => 5,
            'dependencyDepth' => 4,
            'criticalVulnerabilities' => 3,
            'category' => PriorityScore::CATEGORY_CORE,
            'manualPriority' => null,
        ];

        $score = PriorityScore::fromArray($data);

        $this->assertInstanceOf(PriorityScore::class, $score);
        $this->assertSame('TestModule', $score->module);
        $this->assertSame(30.5, $score->score);
        $this->assertSame(5, $score->impactScore);
        $this->assertSame(4, $score->dependencyDepth);
        $this->assertSame(3, $score->criticalVulnerabilities);
        $this->assertSame(PriorityScore::CATEGORY_CORE, $score->category);
        $this->assertNull($score->manualPriority);
    }

    /**
     * Test fromArray() handles manual priority
     */
    public function testFromArrayHandlesManualPriority(): void
    {
        $data = [
            'module' => 'TestModule',
            'score' => 30.5,
            'impactScore' => 5,
            'dependencyDepth' => 4,
            'criticalVulnerabilities' => 3,
            'category' => PriorityScore::CATEGORY_CORE,
            'manualPriority' => 2,
        ];

        $score = PriorityScore::fromArray($data);

        $this->assertSame(2, $score->manualPriority);
        $this->assertTrue($score->hasManualOverride());
    }

    /**
     * Test fromArray() handles missing manualPriority key
     */
    public function testFromArrayHandlesMissingManualPriority(): void
    {
        $data = [
            'module' => 'TestModule',
            'score' => 30.5,
            'impactScore' => 5,
            'dependencyDepth' => 4,
            'criticalVulnerabilities' => 3,
            'category' => PriorityScore::CATEGORY_CORE,
        ];

        $score = PriorityScore::fromArray($data);

        $this->assertNull($score->manualPriority);
        $this->assertFalse($score->hasManualOverride());
    }

    /**
     * Test fromJson() creates correct instance
     */
    public function testFromJsonCreatesCorrectInstance(): void
    {
        $json = json_encode([
            'module' => 'TestModule',
            'score' => 40.0,
            'impactScore' => 6,
            'dependencyDepth' => 5,
            'criticalVulnerabilities' => 4,
            'category' => PriorityScore::CATEGORY_INTERMEDIATE,
            'manualPriority' => 3,
        ]);

        $score = PriorityScore::fromJson($json);

        $this->assertInstanceOf(PriorityScore::class, $score);
        $this->assertSame('TestModule', $score->module);
        $this->assertSame(40.0, $score->score);
        $this->assertSame(6, $score->impactScore);
        $this->assertSame(5, $score->dependencyDepth);
        $this->assertSame(4, $score->criticalVulnerabilities);
        $this->assertSame(PriorityScore::CATEGORY_INTERMEDIATE, $score->category);
        $this->assertSame(3, $score->manualPriority);
    }

    /**
     * Test fromJson() throws exception for invalid JSON
     */
    public function testFromJsonThrowsExceptionForInvalidJson(): void
    {
        $this->expectException(JsonException::class);

        PriorityScore::fromJson('invalid json {');
    }

    /**
     * Test round-trip serialization (toJson -> fromJson)
     */
    public function testRoundTripSerialization(): void
    {
        $original = new PriorityScore(
            module: 'TestModule',
            score: 50.5,
            impactScore: 7,
            dependencyDepth: 6,
            criticalVulnerabilities: 5,
            category: PriorityScore::CATEGORY_LEAF
        );

        $original->setManualPriority(10);

        $json = $original->toJson();
        $restored = PriorityScore::fromJson($json);

        $this->assertSame($original->module, $restored->module);
        $this->assertSame($original->score, $restored->score);
        $this->assertSame($original->impactScore, $restored->impactScore);
        $this->assertSame($original->dependencyDepth, $restored->dependencyDepth);
        $this->assertSame($original->criticalVulnerabilities, $restored->criticalVulnerabilities);
        $this->assertSame($original->category, $restored->category);
        $this->assertSame($original->manualPriority, $restored->manualPriority);
    }

    /**
     * Test category constants are defined correctly
     */
    public function testCategoryConstantsAreDefined(): void
    {
        $this->assertSame('LEAF', PriorityScore::CATEGORY_LEAF);
        $this->assertSame('CORE', PriorityScore::CATEGORY_CORE);
        $this->assertSame('INTERMEDIATE', PriorityScore::CATEGORY_INTERMEDIATE);
    }

    /**
     * Test sorting multiple PriorityScore objects by effective priority
     */
    public function testSortingByEffectivePriority(): void
    {
        $scores = [
            new PriorityScore('Module1', 30.0, 3, 2, 1, PriorityScore::CATEGORY_INTERMEDIATE),
            new PriorityScore('Module2', 10.0, 1, 1, 0, PriorityScore::CATEGORY_LEAF),
            new PriorityScore('Module3', 50.0, 5, 3, 2, PriorityScore::CATEGORY_CORE),
        ];

        // Set manual priority on Module3 to make it highest priority
        $scores[2]->setManualPriority(1);

        // Sort by effective priority (ascending)
        usort($scores, fn($a, $b) => $a->getEffectivePriority() <=> $b->getEffectivePriority());

        $this->assertSame('Module3', $scores[0]->module); // Manual priority 1
        $this->assertSame('Module2', $scores[1]->module); // Score 10.0
        $this->assertSame('Module1', $scores[2]->module); // Score 30.0
    }
}

<?php

namespace Tests\Libraries\Refactor\Analysis;

use App\Libraries\Refactor\Analysis\CodeAnalyzer;
use App\Libraries\Refactor\Exceptions\AnalysisException;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * CodeAnalyzerTest
 * 
 * Tests for the CodeAnalyzer class which analyzes controller structure,
 * identifies business logic, identifies database queries, and estimates
 * refactoring complexity.
 */
class CodeAnalyzerTest extends CIUnitTestCase
{
    private CodeAnalyzer $analyzer;
    private string $fixturesPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->analyzer = new CodeAnalyzer();
        $this->fixturesPath = TESTPATH . '_support/fixtures/controllers/';
    }

    /**
     * Test analyzing controller structure with simple controller
     */
    public function testAnalyzeControllerStructureSimple(): void
    {
        $filePath = $this->fixturesPath . 'SimpleController.php';
        $result = $this->analyzer->analyzeControllerStructure($filePath);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('methodCount', $result);
        $this->assertArrayHasKey('linesOfCode', $result);
        $this->assertArrayHasKey('dependencies', $result);
        $this->assertArrayHasKey('methods', $result);

        // Simple controller should have 2 public methods (index, show)
        $this->assertGreaterThanOrEqual(2, $result['methodCount']);
        
        // Should have some lines of code
        $this->assertGreaterThan(0, $result['linesOfCode']);
        
        // Should have dependencies array
        $this->assertIsArray($result['dependencies']);
        
        // Should have methods array
        $this->assertIsArray($result['methods']);
    }

    /**
     * Test analyzing controller structure with complex controller
     */
    public function testAnalyzeControllerStructureComplex(): void
    {
        $filePath = $this->fixturesPath . 'ComplexController.php';
        $result = $this->analyzer->analyzeControllerStructure($filePath);

        $this->assertIsArray($result);
        
        // Complex controller should have more methods
        $this->assertGreaterThanOrEqual(3, $result['methodCount']);
        
        // Should have more lines of code
        $this->assertGreaterThan(50, $result['linesOfCode']);
        
        // Should have multiple dependencies
        $this->assertGreaterThan(1, count($result['dependencies']));
    }

    /**
     * Test analyzing controller structure with non-existent file
     */
    public function testAnalyzeControllerStructureFileNotFound(): void
    {
        $this->expectException(AnalysisException::class);
        $this->expectExceptionMessage('File not found');
        
        $this->analyzer->analyzeControllerStructure('/non/existent/file.php');
    }

    /**
     * Test identifying business logic in simple controller
     */
    public function testIdentifyBusinessLogicSimple(): void
    {
        $filePath = $this->fixturesPath . 'SimpleController.php';
        $result = $this->analyzer->identifyBusinessLogic($filePath);

        $this->assertIsArray($result);
        
        // Simple controller should have minimal or no business logic
        $this->assertLessThan(5, count($result));
    }

    /**
     * Test identifying business logic in complex controller
     */
    public function testIdentifyBusinessLogicComplex(): void
    {
        $filePath = $this->fixturesPath . 'ComplexController.php';
        $result = $this->analyzer->identifyBusinessLogic($filePath);

        $this->assertIsArray($result);
        
        // Complex controller should have multiple business logic findings
        $this->assertGreaterThan(0, count($result));
        
        // Check that findings have required structure
        foreach ($result as $finding) {
            $this->assertArrayHasKey('type', $finding);
            $this->assertArrayHasKey('description', $finding);
            $this->assertArrayHasKey('line', $finding);
            $this->assertArrayHasKey('method', $finding);
            $this->assertArrayHasKey('snippet', $finding);
            $this->assertArrayHasKey('severity', $finding);
        }
        
        // Should find calculations
        $calculations = array_filter($result, fn($f) => $f['type'] === 'CALCULATION');
        $this->assertGreaterThan(0, count($calculations));
        
        // Should find complex conditionals
        $conditionals = array_filter($result, fn($f) => $f['type'] === 'COMPLEX_CONDITIONAL');
        $this->assertGreaterThan(0, count($conditionals));
        
        // Should find data transformations
        $transformations = array_filter($result, fn($f) => $f['type'] === 'DATA_TRANSFORMATION');
        $this->assertGreaterThan(0, count($transformations));
        
        // Should find validation logic
        $validations = array_filter($result, fn($f) => $f['type'] === 'VALIDATION');
        $this->assertGreaterThan(0, count($validations));
        
        // Should find data processing loops
        $loops = array_filter($result, fn($f) => $f['type'] === 'DATA_PROCESSING_LOOP');
        $this->assertGreaterThan(0, count($loops));
    }

    /**
     * Test identifying business logic with non-existent file
     */
    public function testIdentifyBusinessLogicFileNotFound(): void
    {
        $this->expectException(AnalysisException::class);
        $this->expectExceptionMessage('File not found');
        
        $this->analyzer->identifyBusinessLogic('/non/existent/file.php');
    }

    /**
     * Test identifying database queries in simple controller
     */
    public function testIdentifyDatabaseQueriesSimple(): void
    {
        $filePath = $this->fixturesPath . 'SimpleController.php';
        $result = $this->analyzer->identifyDatabaseQueries($filePath);

        $this->assertIsArray($result);
        
        // Simple controller uses model methods
        $this->assertGreaterThan(0, count($result));
        
        // Should find model query methods
        $modelQueries = array_filter($result, fn($f) => $f['type'] === 'MODEL_QUERY');
        $this->assertGreaterThan(0, count($modelQueries));
    }

    /**
     * Test identifying database queries in complex controller
     */
    public function testIdentifyDatabaseQueriesComplex(): void
    {
        $filePath = $this->fixturesPath . 'ComplexController.php';
        $result = $this->analyzer->identifyDatabaseQueries($filePath);

        $this->assertIsArray($result);
        
        // Complex controller should have multiple database query findings
        $this->assertGreaterThan(0, count($result));
        
        // Check that findings have required structure
        foreach ($result as $finding) {
            $this->assertArrayHasKey('type', $finding);
            $this->assertArrayHasKey('description', $finding);
            $this->assertArrayHasKey('line', $finding);
            $this->assertArrayHasKey('method', $finding);
            $this->assertArrayHasKey('snippet', $finding);
            $this->assertArrayHasKey('severity', $finding);
        }
        
        // Should find Query Builder usage
        $queryBuilder = array_filter($result, fn($f) => $f['type'] === 'QUERY_BUILDER');
        $this->assertGreaterThan(0, count($queryBuilder));
        
        // Should find raw queries
        $rawQueries = array_filter($result, fn($f) => $f['type'] === 'RAW_QUERY');
        $this->assertGreaterThan(0, count($rawQueries));
        
        // Should find model query methods
        $modelQueries = array_filter($result, fn($f) => $f['type'] === 'MODEL_QUERY');
        $this->assertGreaterThan(0, count($modelQueries));
        
        // Should find database connections
        $dbConnections = array_filter($result, fn($f) => $f['type'] === 'DATABASE_CONNECTION');
        $this->assertGreaterThan(0, count($dbConnections));
    }

    /**
     * Test identifying database queries with non-existent file
     */
    public function testIdentifyDatabaseQueriesFileNotFound(): void
    {
        $this->expectException(AnalysisException::class);
        $this->expectExceptionMessage('File not found');
        
        $this->analyzer->identifyDatabaseQueries('/non/existent/file.php');
    }

    /**
     * Test estimating complexity for simple controller
     */
    public function testEstimateComplexitySimple(): void
    {
        $filePath = $this->fixturesPath . 'SimpleController.php';
        $result = $this->analyzer->estimateComplexity($filePath);

        $this->assertIsString($result);
        $this->assertContains($result, ['SIMPLE', 'MEDIUM', 'COMPLEX']);
        
        // Simple controller should be SIMPLE or MEDIUM
        $this->assertContains($result, ['SIMPLE', 'MEDIUM']);
    }

    /**
     * Test estimating complexity for complex controller
     */
    public function testEstimateComplexityComplex(): void
    {
        $filePath = $this->fixturesPath . 'ComplexController.php';
        $result = $this->analyzer->estimateComplexity($filePath);

        $this->assertIsString($result);
        $this->assertContains($result, ['SIMPLE', 'MEDIUM', 'COMPLEX']);
        
        // Complex controller should be MEDIUM or COMPLEX
        $this->assertContains($result, ['MEDIUM', 'COMPLEX']);
    }

    /**
     * Test estimating complexity with non-existent file
     */
    public function testEstimateComplexityFileNotFound(): void
    {
        $this->expectException(AnalysisException::class);
        
        $this->analyzer->estimateComplexity('/non/existent/file.php');
    }

    /**
     * Test that method analysis includes method details
     */
    public function testMethodAnalysisIncludesDetails(): void
    {
        $filePath = $this->fixturesPath . 'SimpleController.php';
        $result = $this->analyzer->analyzeControllerStructure($filePath);

        $this->assertIsArray($result['methods']);
        
        if (count($result['methods']) > 0) {
            $method = $result['methods'][0];
            
            $this->assertArrayHasKey('name', $method);
            $this->assertArrayHasKey('lineCount', $method);
            $this->assertArrayHasKey('startLine', $method);
            $this->assertArrayHasKey('endLine', $method);
            
            $this->assertIsString($method['name']);
            $this->assertIsInt($method['lineCount']);
            $this->assertIsInt($method['startLine']);
            $this->assertIsInt($method['endLine']);
            
            $this->assertGreaterThan(0, $method['lineCount']);
            $this->assertGreaterThan(0, $method['startLine']);
            $this->assertGreaterThanOrEqual($method['startLine'], $method['endLine']);
        }
    }

    /**
     * Test that severity levels are assigned correctly
     */
    public function testSeverityLevelsAreAssigned(): void
    {
        $filePath = $this->fixturesPath . 'ComplexController.php';
        
        // Test business logic severity
        $businessLogic = $this->analyzer->identifyBusinessLogic($filePath);
        foreach ($businessLogic as $finding) {
            $this->assertContains($finding['severity'], ['LOW', 'MEDIUM', 'HIGH', 'CRITICAL']);
        }
        
        // Test database query severity
        $queries = $this->analyzer->identifyDatabaseQueries($filePath);
        foreach ($queries as $finding) {
            $this->assertContains($finding['severity'], ['LOW', 'MEDIUM', 'HIGH', 'CRITICAL']);
        }
        
        // Raw queries should be CRITICAL
        $rawQueries = array_filter($queries, fn($f) => $f['type'] === 'RAW_QUERY');
        foreach ($rawQueries as $finding) {
            $this->assertEquals('CRITICAL', $finding['severity']);
        }
    }

    /**
     * Test that line numbers are accurate
     */
    public function testLineNumbersAreAccurate(): void
    {
        $filePath = $this->fixturesPath . 'ComplexController.php';
        
        $businessLogic = $this->analyzer->identifyBusinessLogic($filePath);
        
        foreach ($businessLogic as $finding) {
            $this->assertIsInt($finding['line']);
            $this->assertGreaterThan(0, $finding['line']);
        }
    }

    /**
     * Test that method names are identified correctly
     */
    public function testMethodNamesAreIdentified(): void
    {
        $filePath = $this->fixturesPath . 'ComplexController.php';
        
        $businessLogic = $this->analyzer->identifyBusinessLogic($filePath);
        
        foreach ($businessLogic as $finding) {
            $this->assertIsString($finding['method']);
            $this->assertNotEmpty($finding['method']);
        }
    }

    /**
     * Test that code snippets are captured
     */
    public function testCodeSnippetsAreCaptured(): void
    {
        $filePath = $this->fixturesPath . 'ComplexController.php';
        
        $businessLogic = $this->analyzer->identifyBusinessLogic($filePath);
        
        foreach ($businessLogic as $finding) {
            $this->assertIsString($finding['snippet']);
            $this->assertNotEmpty($finding['snippet']);
        }
    }

    /**
     * Test that dependencies are extracted correctly
     */
    public function testDependenciesAreExtracted(): void
    {
        $filePath = $this->fixturesPath . 'ComplexController.php';
        $result = $this->analyzer->analyzeControllerStructure($filePath);

        $this->assertIsArray($result['dependencies']);
        $this->assertGreaterThan(0, count($result['dependencies']));
        
        // All dependencies should be strings
        foreach ($result['dependencies'] as $dep) {
            $this->assertIsString($dep);
        }
    }

    /**
     * Test that lines of code count excludes comments and blank lines
     */
    public function testLinesOfCodeExcludesCommentsAndBlankLines(): void
    {
        $filePath = $this->fixturesPath . 'SimpleController.php';
        $result = $this->analyzer->analyzeControllerStructure($filePath);

        // Get actual file line count
        $actualLines = count(file($filePath));
        
        // LOC should be less than actual lines (due to comments and blank lines)
        $this->assertLessThan($actualLines, $result['linesOfCode']);
        
        // But should still be greater than 0
        $this->assertGreaterThan(0, $result['linesOfCode']);
    }
}

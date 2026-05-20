<?php

namespace Tests\Unit\Refactor\Discovery;

use App\Libraries\Refactor\Discovery\FileScanner;
use App\Libraries\Refactor\Discovery\CodeParser;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Integration Test for FileScanner and CodeParser
 * 
 * Tests the integration between FileScanner and CodeParser utilities.
 * 
 * @package Tests\Unit\Refactor\Discovery
 */
class IntegrationTest extends CIUnitTestCase
{
    private FileScanner $scanner;
    private CodeParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->scanner = new FileScanner();
        $this->parser = new CodeParser();
    }

    /**
     * Test scanning and parsing controllers
     */
    public function testScanAndParseControllers(): void
    {
        // Scan for controller files
        $this->scanner->setFilters([FileScanner::FILTER_CONTROLLERS]);
        $controllerFiles = $this->scanner->scan(APPPATH . 'Controllers');
        
        $this->assertNotEmpty($controllerFiles, 'Should find controller files');
        
        // Parse the first controller file
        $firstController = $controllerFiles[0];
        $classInfo = $this->parser->parseClassInfo($firstController);
        
        $this->assertIsArray($classInfo);
        $this->assertArrayHasKey('className', $classInfo);
        $this->assertArrayHasKey('namespace', $classInfo);
        $this->assertArrayHasKey('methods', $classInfo);
    }

    /**
     * Test scanning and parsing models
     */
    public function testScanAndParseModels(): void
    {
        // Scan for model files
        $this->scanner->setFilters([FileScanner::FILTER_MODELS]);
        $modelFiles = $this->scanner->scan(APPPATH . 'Models');
        
        $this->assertNotEmpty($modelFiles, 'Should find model files');
        
        // Parse the first model file
        $firstModel = $modelFiles[0];
        $classInfo = $this->parser->parseClassInfo($firstModel);
        
        $this->assertIsArray($classInfo);
        $this->assertArrayHasKey('className', $classInfo);
    }

    /**
     * Test finding dependencies in controllers
     */
    public function testFindDependenciesInControllers(): void
    {
        // Scan for controller files
        $this->scanner->setFilters([FileScanner::FILTER_CONTROLLERS]);
        $controllerFiles = $this->scanner->scan(APPPATH . 'Controllers');
        
        $this->assertNotEmpty($controllerFiles);
        
        // Parse dependencies from first controller
        $firstController = $controllerFiles[0];
        
        // Extract use statements (imports)
        $uses = $this->parser->extractUseStatements($firstController);
        $this->assertIsArray($uses);
        
        // Extract method calls
        $methodCalls = $this->parser->extractMethodCalls($firstController);
        $this->assertIsArray($methodCalls);
        
        // Extract model instantiations
        $modelInstantiations = $this->parser->extractModelInstantiations($firstController);
        $this->assertIsArray($modelInstantiations);
    }

    /**
     * Test scanning multiple directories and parsing results
     */
    public function testScanMultipleDirectoriesAndParse(): void
    {
        $directories = [
            APPPATH . 'Controllers',
            APPPATH . 'Models',
            APPPATH . 'Services',
        ];
        
        $files = $this->scanner->scanMultiple($directories);
        
        $this->assertNotEmpty($files);
        
        // Parse a sample of files
        $sampleSize = min(5, count($files));
        $parsedCount = 0;
        
        for ($i = 0; $i < $sampleSize; $i++) {
            $ast = $this->parser->parse($files[$i]);
            if ($ast !== null) {
                $parsedCount++;
            }
        }
        
        $this->assertGreaterThan(0, $parsedCount, 'Should successfully parse at least one file');
    }

    /**
     * Test finding raw SQL queries in controllers
     */
    public function testFindRawSqlQueriesInControllers(): void
    {
        // Scan for controller files
        $this->scanner->setFilters([FileScanner::FILTER_CONTROLLERS]);
        $controllerFiles = $this->scanner->scan(APPPATH . 'Controllers');
        
        $this->assertNotEmpty($controllerFiles);
        
        $totalQueries = 0;
        
        // Check first few controllers for raw SQL
        $sampleSize = min(10, count($controllerFiles));
        for ($i = 0; $i < $sampleSize; $i++) {
            $queries = $this->parser->findRawSqlQueries($controllerFiles[$i]);
            $totalQueries += count($queries);
        }
        
        // Just verify the method works, don't assert on count
        // (some controllers may not have raw SQL)
        $this->assertIsInt($totalQueries);
    }

    /**
     * Test extracting constructor dependencies from services
     */
    public function testExtractConstructorDependenciesFromServices(): void
    {
        // Scan for service files
        $this->scanner->setFilters([FileScanner::FILTER_SERVICES]);
        $serviceFiles = $this->scanner->scan(APPPATH . 'Services');
        
        if (empty($serviceFiles)) {
            $this->markTestSkipped('No service files found');
        }
        
        // Parse dependencies from first service
        $firstService = $serviceFiles[0];
        $dependencies = $this->parser->extractConstructorDependencies($firstService);
        
        $this->assertIsArray($dependencies);
        // Services typically have dependencies injected via constructor
    }

    /**
     * Test complete workflow: scan, filter, parse, analyze
     */
    public function testCompleteWorkflow(): void
    {
        // Step 1: Scan for all PHP files in app directory
        $allFiles = $this->scanner->scan(APPPATH);
        $this->assertNotEmpty($allFiles);
        
        // Step 2: Filter for controllers only
        $this->scanner->setFilters([FileScanner::FILTER_CONTROLLERS]);
        $controllers = $this->scanner->scan(APPPATH . 'Controllers');
        $this->assertNotEmpty($controllers);
        
        // Step 3: Parse first controller
        $firstController = $controllers[0];
        $classInfo = $this->parser->parseClassInfo($firstController);
        
        // Step 4: Verify we got useful information
        $this->assertNotNull($classInfo['className']);
        $this->assertIsArray($classInfo['methods']);
        $this->assertIsArray($classInfo['uses']);
        
        // Step 5: Extract dependencies
        $uses = $this->parser->extractUseStatements($firstController);
        $methodCalls = $this->parser->extractMethodCalls($firstController);
        
        // Verify we can extract dependency information
        $this->assertIsArray($uses);
        $this->assertIsArray($methodCalls);
    }
}

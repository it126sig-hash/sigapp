<?php

namespace Tests\Unit\Refactor\Discovery;

use App\Libraries\Refactor\Discovery\CodeParser;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * CodeParser Unit Tests
 * 
 * Tests for the CodeParser utility that uses nikic/php-parser for AST parsing.
 * 
 * @package Tests\Unit\Refactor\Discovery
 */
class CodeParserTest extends CIUnitTestCase
{
    private CodeParser $parser;
    private string $testFilesDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new CodeParser();
        $this->testFilesDir = APPPATH . '../tests/_support/Refactor/TestFiles';
        
        // Create test files directory
        if (!is_dir($this->testFilesDir)) {
            mkdir($this->testFilesDir, 0777, true);
        }
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
     * Test parsing valid PHP code
     */
    public function testParseValidPhpCode(): void
    {
        $code = '<?php class TestClass {}';
        $ast = $this->parser->parse($code);
        
        $this->assertIsArray($ast);
        $this->assertNotEmpty($ast);
        $this->assertTrue($this->parser->wasLastParseSuccessful());
    }

    /**
     * Test parsing invalid PHP code
     */
    public function testParseInvalidPhpCode(): void
    {
        $code = '<?php class TestClass {';  // Missing closing brace
        $ast = $this->parser->parse($code);
        
        $this->assertNull($ast);
        $this->assertFalse($this->parser->wasLastParseSuccessful());
        
        $metadata = $this->parser->getLastParseMetadata();
        $this->assertArrayHasKey('error', $metadata);
        $this->assertNotNull($metadata['error']);
    }

    /**
     * Test parsing PHP file
     */
    public function testParsePhpFile(): void
    {
        $filePath = $this->testFilesDir . '/TestClass.php';
        file_put_contents($filePath, '<?php class TestClass {}');
        
        $ast = $this->parser->parse($filePath);
        
        $this->assertIsArray($ast);
        $this->assertNotEmpty($ast);
    }

    /**
     * Test parseClassInfo extracts namespace
     */
    public function testParseClassInfoExtractsNamespace(): void
    {
        $code = '<?php namespace App\Controllers; class TestController {}';
        $filePath = $this->testFilesDir . '/TestController.php';
        file_put_contents($filePath, $code);
        
        $info = $this->parser->parseClassInfo($filePath);
        
        $this->assertIsArray($info);
        $this->assertEquals('App\Controllers', $info['namespace']);
        $this->assertEquals('TestController', $info['className']);
    }

    /**
     * Test parseClassInfo extracts class extends
     */
    public function testParseClassInfoExtractsExtends(): void
    {
        $code = '<?php namespace App\Controllers; class TestController extends BaseController {}';
        $filePath = $this->testFilesDir . '/TestController.php';
        file_put_contents($filePath, $code);
        
        $info = $this->parser->parseClassInfo($filePath);
        
        $this->assertEquals('BaseController', $info['extends']);
    }

    /**
     * Test parseClassInfo extracts implements
     */
    public function testParseClassInfoExtractsImplements(): void
    {
        $code = '<?php interface TestInterface {} class TestClass implements TestInterface {}';
        $filePath = $this->testFilesDir . '/TestClass.php';
        file_put_contents($filePath, $code);
        
        $info = $this->parser->parseClassInfo($filePath);
        
        $this->assertContains('TestInterface', $info['implements']);
    }

    /**
     * Test parseClassInfo extracts methods
     */
    public function testParseClassInfoExtractsMethods(): void
    {
        $code = '<?php class TestClass { public function index() {} public function show() {} }';
        $filePath = $this->testFilesDir . '/TestClass.php';
        file_put_contents($filePath, $code);
        
        $info = $this->parser->parseClassInfo($filePath);
        
        $this->assertContains('index', $info['methods']);
        $this->assertContains('show', $info['methods']);
    }

    /**
     * Test parseClassInfo extracts properties
     */
    public function testParseClassInfoExtractsProperties(): void
    {
        $code = '<?php class TestClass { private $name; protected $age; }';
        $filePath = $this->testFilesDir . '/TestClass.php';
        file_put_contents($filePath, $code);
        
        $info = $this->parser->parseClassInfo($filePath);
        
        $this->assertContains('name', $info['properties']);
        $this->assertContains('age', $info['properties']);
    }

    /**
     * Test parseClassInfo extracts use statements
     */
    public function testParseClassInfoExtractsUseStatements(): void
    {
        $code = '<?php use App\Models\UserModel; use App\Services\AuthService; class TestClass {}';
        $filePath = $this->testFilesDir . '/TestClass.php';
        file_put_contents($filePath, $code);
        
        $info = $this->parser->parseClassInfo($filePath);
        
        $this->assertContains('App\Models\UserModel', $info['uses']);
        $this->assertContains('App\Services\AuthService', $info['uses']);
    }

    /**
     * Test extractMethodCalls finds method calls
     */
    public function testExtractMethodCallsFindsMethodCalls(): void
    {
        $code = '<?php class TestClass { public function test() { $this->someMethod(); } }';
        
        $calls = $this->parser->extractMethodCalls($code);
        
        $this->assertIsArray($calls);
        $this->assertNotEmpty($calls);
        $this->assertEquals('someMethod', $calls[0]['method']);
    }

    /**
     * Test extractMethodCalls finds static method calls
     */
    public function testExtractMethodCallsFindsStaticCalls(): void
    {
        $code = '<?php class TestClass { public function test() { SomeClass::staticMethod(); } }';
        
        $calls = $this->parser->extractMethodCalls($code);
        
        $this->assertIsArray($calls);
        $this->assertNotEmpty($calls);
        $this->assertEquals('SomeClass', $calls[0]['class']);
        $this->assertEquals('staticMethod', $calls[0]['method']);
    }

    /**
     * Test extractModelInstantiations finds model instantiations
     */
    public function testExtractModelInstantiationsFindsModels(): void
    {
        $code = '<?php class TestClass { public function test() { $model = new UserModel(); } }';
        
        $instantiations = $this->parser->extractModelInstantiations($code);
        
        $this->assertIsArray($instantiations);
        $this->assertNotEmpty($instantiations);
        $this->assertEquals('UserModel', $instantiations[0]['class']);
    }

    /**
     * Test extractModelInstantiations filters non-model classes
     */
    public function testExtractModelInstantiationsFiltersNonModels(): void
    {
        $code = '<?php class TestClass { public function test() { $obj = new SomeClass(); } }';
        
        $instantiations = $this->parser->extractModelInstantiations($code);
        
        $this->assertIsArray($instantiations);
        $this->assertEmpty($instantiations);
    }

    /**
     * Test extractUseStatements extracts imports
     */
    public function testExtractUseStatementsExtractsImports(): void
    {
        $code = '<?php use App\Models\UserModel; use App\Services\AuthService; class TestClass {}';
        
        $uses = $this->parser->extractUseStatements($code);
        
        $this->assertIsArray($uses);
        $this->assertContains('App\Models\UserModel', $uses);
        $this->assertContains('App\Services\AuthService', $uses);
    }

    /**
     * Test findRawSqlQueries detects SELECT queries
     */
    public function testFindRawSqlQueriesDetectsSelectQueries(): void
    {
        $code = '<?php class TestClass { public function test() { $sql = "SELECT * FROM users"; } }';
        
        $queries = $this->parser->findRawSqlQueries($code);
        
        $this->assertIsArray($queries);
        $this->assertNotEmpty($queries);
        $this->assertStringContainsString('SELECT', $queries[0]['query']);
    }

    /**
     * Test findRawSqlQueries detects INSERT queries
     */
    public function testFindRawSqlQueriesDetectsInsertQueries(): void
    {
        $code = '<?php class TestClass { public function test() { $sql = "INSERT INTO users VALUES (1, \'test\')"; } }';
        
        $queries = $this->parser->findRawSqlQueries($code);
        
        $this->assertIsArray($queries);
        $this->assertNotEmpty($queries);
        $this->assertStringContainsString('INSERT', $queries[0]['query']);
    }

    /**
     * Test findRawSqlQueries detects UPDATE queries
     */
    public function testFindRawSqlQueriesDetectsUpdateQueries(): void
    {
        $code = '<?php class TestClass { public function test() { $sql = "UPDATE users SET name = \'test\'"; } }';
        
        $queries = $this->parser->findRawSqlQueries($code);
        
        $this->assertIsArray($queries);
        $this->assertNotEmpty($queries);
        $this->assertStringContainsString('UPDATE', $queries[0]['query']);
    }

    /**
     * Test findRawSqlQueries detects DELETE queries
     */
    public function testFindRawSqlQueriesDetectsDeleteQueries(): void
    {
        $code = '<?php class TestClass { public function test() { $sql = "DELETE FROM users WHERE id = 1"; } }';
        
        $queries = $this->parser->findRawSqlQueries($code);
        
        $this->assertIsArray($queries);
        $this->assertNotEmpty($queries);
        $this->assertStringContainsString('DELETE', $queries[0]['query']);
    }

    /**
     * Test extractConstructorDependencies extracts typed parameters
     */
    public function testExtractConstructorDependenciesExtractsTypedParameters(): void
    {
        $code = '<?php class TestClass { public function __construct(UserModel $userModel, AuthService $authService) {} }';
        
        $dependencies = $this->parser->extractConstructorDependencies($code);
        
        $this->assertIsArray($dependencies);
        $this->assertCount(2, $dependencies);
        $this->assertEquals('UserModel', $dependencies[0]['type']);
        $this->assertEquals('userModel', $dependencies[0]['name']);
        $this->assertEquals('AuthService', $dependencies[1]['type']);
        $this->assertEquals('authService', $dependencies[1]['name']);
    }

    /**
     * Test extractConstructorDependencies handles untyped parameters
     */
    public function testExtractConstructorDependenciesHandlesUntypedParameters(): void
    {
        $code = '<?php class TestClass { public function __construct($param1, $param2) {} }';
        
        $dependencies = $this->parser->extractConstructorDependencies($code);
        
        $this->assertIsArray($dependencies);
        $this->assertCount(2, $dependencies);
        $this->assertNull($dependencies[0]['type']);
        $this->assertEquals('param1', $dependencies[0]['name']);
    }

    /**
     * Test parsing malformed code returns null
     */
    public function testParseMalformedCodeReturnsNull(): void
    {
        $code = '<?php class TestClass { public function test() {';  // Unclosed method
        
        $ast = $this->parser->parse($code);
        
        $this->assertNull($ast);
        $this->assertFalse($this->parser->wasLastParseSuccessful());
    }

    /**
     * Test getLastParseMetadata returns metadata
     */
    public function testGetLastParseMetadataReturnsMetadata(): void
    {
        $code = '<?php class TestClass {}';
        $this->parser->parse($code);
        
        $metadata = $this->parser->getLastParseMetadata();
        
        $this->assertIsArray($metadata);
        $this->assertArrayHasKey('success', $metadata);
        $this->assertArrayHasKey('error', $metadata);
        $this->assertTrue($metadata['success']);
    }

    /**
     * Test parsing non-existent file returns null
     */
    public function testParseNonExistentFileReturnsNull(): void
    {
        $ast = $this->parser->parse('/non/existent/file.php');
        
        $this->assertNull($ast);
    }

    /**
     * Test parseClassInfo with complex class structure
     */
    public function testParseClassInfoWithComplexStructure(): void
    {
        $code = '<?php
namespace App\Controllers;

use App\Models\UserModel;
use App\Services\AuthService;

class UserController extends BaseController
{
    private $userModel;
    protected $authService;

    public function __construct(UserModel $userModel, AuthService $authService)
    {
        $this->userModel = $userModel;
        $this->authService = $authService;
    }

    public function index()
    {
        return view("users/index");
    }

    public function show($id)
    {
        $user = $this->userModel->find($id);
        return view("users/show", ["user" => $user]);
    }
}';
        
        $filePath = $this->testFilesDir . '/UserController.php';
        file_put_contents($filePath, $code);
        
        $info = $this->parser->parseClassInfo($filePath);
        
        $this->assertEquals('App\Controllers', $info['namespace']);
        $this->assertEquals('UserController', $info['className']);
        $this->assertEquals('BaseController', $info['extends']);
        $this->assertContains('App\Models\UserModel', $info['uses']);
        $this->assertContains('App\Services\AuthService', $info['uses']);
        $this->assertContains('index', $info['methods']);
        $this->assertContains('show', $info['methods']);
        $this->assertContains('userModel', $info['properties']);
        $this->assertContains('authService', $info['properties']);
    }

    /**
     * Recursively delete directory
     */
    private function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
}

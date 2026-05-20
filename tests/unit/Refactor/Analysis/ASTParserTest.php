<?php

namespace Tests\Unit\Refactor\Analysis;

use App\Libraries\Refactor\Analysis\ASTParser;
use App\Libraries\Refactor\Discovery\CodeParser;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * ASTParser Unit Tests
 * 
 * Tests for the ASTParser wrapper class that provides focused interface
 * for dependency extraction from PHP code.
 * 
 * @package Tests\Unit\Refactor\Analysis
 */
class ASTParserTest extends CIUnitTestCase
{
    private ASTParser $parser;
    private string $testFilesDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new ASTParser();
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
     * Test ASTParser can be instantiated
     */
    public function testCanBeInstantiated(): void
    {
        $parser = new ASTParser();
        
        $this->assertInstanceOf(ASTParser::class, $parser);
    }

    /**
     * Test ASTParser can be instantiated with custom CodeParser
     */
    public function testCanBeInstantiatedWithCustomCodeParser(): void
    {
        $codeParser = new CodeParser();
        $parser = new ASTParser($codeParser);
        
        $this->assertInstanceOf(ASTParser::class, $parser);
        $this->assertSame($codeParser, $parser->getCodeParser());
    }

    /**
     * Test extractUseStatements extracts use statements from file
     */
    public function testExtractUseStatementsExtractsFromFile(): void
    {
        $code = '<?php
namespace App\Controllers;

use App\Models\UserModel;
use App\Services\AuthService;
use App\Repositories\UserRepository;

class TestController {}';
        
        $filePath = $this->testFilesDir . '/TestController.php';
        file_put_contents($filePath, $code);
        
        $uses = $this->parser->extractUseStatements($filePath);
        
        $this->assertIsArray($uses);
        $this->assertCount(3, $uses);
        $this->assertContains('App\Models\UserModel', $uses);
        $this->assertContains('App\Services\AuthService', $uses);
        $this->assertContains('App\Repositories\UserRepository', $uses);
    }

    /**
     * Test extractUseStatements returns empty array for file with no use statements
     */
    public function testExtractUseStatementsReturnsEmptyArrayForNoUses(): void
    {
        $code = '<?php
namespace App\Controllers;

class TestController {}';
        
        $filePath = $this->testFilesDir . '/TestController.php';
        file_put_contents($filePath, $code);
        
        $uses = $this->parser->extractUseStatements($filePath);
        
        $this->assertIsArray($uses);
        $this->assertEmpty($uses);
    }

    /**
     * Test extractClassInstantiations extracts model instantiations
     */
    public function testExtractClassInstantiationsExtractsModels(): void
    {
        $code = '<?php
namespace App\Controllers;

class TestController
{
    public function index()
    {
        $userModel = new UserModel();
        $postModel = new PostModel();
        return view("test");
    }
}';
        
        $filePath = $this->testFilesDir . '/TestController.php';
        file_put_contents($filePath, $code);
        
        $instantiations = $this->parser->extractClassInstantiations($filePath);
        
        $this->assertIsArray($instantiations);
        $this->assertCount(2, $instantiations);
        $this->assertEquals('UserModel', $instantiations[0]['class']);
        $this->assertEquals('PostModel', $instantiations[1]['class']);
        $this->assertArrayHasKey('line', $instantiations[0]);
    }

    /**
     * Test extractClassInstantiations filters non-model classes
     */
    public function testExtractClassInstantiationsFiltersNonModels(): void
    {
        $code = '<?php
namespace App\Controllers;

class TestController
{
    public function index()
    {
        $service = new UserService();
        $helper = new StringHelper();
        return view("test");
    }
}';
        
        $filePath = $this->testFilesDir . '/TestController.php';
        file_put_contents($filePath, $code);
        
        $instantiations = $this->parser->extractClassInstantiations($filePath);
        
        $this->assertIsArray($instantiations);
        $this->assertEmpty($instantiations);
    }

    /**
     * Test extractMethodCalls extracts instance method calls
     */
    public function testExtractMethodCallsExtractsInstanceMethods(): void
    {
        $code = '<?php
namespace App\Controllers;

class TestController
{
    public function index()
    {
        $user = $this->userModel->find(1);
        $posts = $this->postModel->where("user_id", 1)->findAll();
        return view("test");
    }
}';
        
        $filePath = $this->testFilesDir . '/TestController.php';
        file_put_contents($filePath, $code);
        
        $calls = $this->parser->extractMethodCalls($filePath);
        
        $this->assertIsArray($calls);
        $this->assertNotEmpty($calls);
        
        // Check for find method call
        $findCall = array_filter($calls, fn($call) => $call['method'] === 'find');
        $this->assertNotEmpty($findCall);
        
        // Check for where method call
        $whereCall = array_filter($calls, fn($call) => $call['method'] === 'where');
        $this->assertNotEmpty($whereCall);
        
        // Check for findAll method call
        $findAllCall = array_filter($calls, fn($call) => $call['method'] === 'findAll');
        $this->assertNotEmpty($findAllCall);
    }

    /**
     * Test extractMethodCalls extracts static method calls
     */
    public function testExtractMethodCallsExtractsStaticMethods(): void
    {
        $code = '<?php
namespace App\Controllers;

class TestController
{
    public function index()
    {
        $users = DB::table("users")->get();
        $config = Config::get("app.name");
        return view("test");
    }
}';
        
        $filePath = $this->testFilesDir . '/TestController.php';
        file_put_contents($filePath, $code);
        
        $calls = $this->parser->extractMethodCalls($filePath);
        
        $this->assertIsArray($calls);
        $this->assertNotEmpty($calls);
        
        // Check for DB::table call
        $tableCall = array_filter($calls, fn($call) => 
            $call['class'] === 'DB' && $call['method'] === 'table'
        );
        $this->assertNotEmpty($tableCall);
        
        // Check for Config::get call
        $getCall = array_filter($calls, fn($call) => 
            $call['class'] === 'Config' && $call['method'] === 'get'
        );
        $this->assertNotEmpty($getCall);
    }

    /**
     * Test extractAllDependencies returns comprehensive dependency info
     */
    public function testExtractAllDependenciesReturnsComprehensiveInfo(): void
    {
        $code = '<?php
namespace App\Controllers;

use App\Models\UserModel;
use App\Services\AuthService;

class TestController
{
    public function index()
    {
        $userModel = new UserModel();
        $user = $userModel->find(1);
        $config = Config::get("app.name");
        return view("test");
    }
}';
        
        $filePath = $this->testFilesDir . '/TestController.php';
        file_put_contents($filePath, $code);
        
        $dependencies = $this->parser->extractAllDependencies($filePath);
        
        $this->assertIsArray($dependencies);
        $this->assertArrayHasKey('uses', $dependencies);
        $this->assertArrayHasKey('instantiations', $dependencies);
        $this->assertArrayHasKey('methodCalls', $dependencies);
        
        // Check uses
        $this->assertContains('App\Models\UserModel', $dependencies['uses']);
        $this->assertContains('App\Services\AuthService', $dependencies['uses']);
        
        // Check instantiations
        $this->assertNotEmpty($dependencies['instantiations']);
        $this->assertEquals('UserModel', $dependencies['instantiations'][0]['class']);
        
        // Check method calls
        $this->assertNotEmpty($dependencies['methodCalls']);
    }

    /**
     * Test extractConstructorDependencies extracts typed constructor parameters
     */
    public function testExtractConstructorDependenciesExtractsTypedParameters(): void
    {
        $code = '<?php
namespace App\Services;

use App\Repositories\UserRepository;
use App\Services\EmailService;

class UserService
{
    public function __construct(
        private UserRepository $userRepository,
        private EmailService $emailService
    ) {}
}';
        
        $filePath = $this->testFilesDir . '/UserService.php';
        file_put_contents($filePath, $code);
        
        $dependencies = $this->parser->extractConstructorDependencies($filePath);
        
        $this->assertIsArray($dependencies);
        $this->assertCount(2, $dependencies);
        
        $this->assertEquals('UserRepository', $dependencies[0]['type']);
        $this->assertEquals('userRepository', $dependencies[0]['name']);
        
        $this->assertEquals('EmailService', $dependencies[1]['type']);
        $this->assertEquals('emailService', $dependencies[1]['name']);
    }

    /**
     * Test extractConstructorDependencies handles untyped parameters
     */
    public function testExtractConstructorDependenciesHandlesUntypedParameters(): void
    {
        $code = '<?php
namespace App\Services;

class UserService
{
    public function __construct($config, $logger)
    {
    }
}';
        
        $filePath = $this->testFilesDir . '/UserService.php';
        file_put_contents($filePath, $code);
        
        $dependencies = $this->parser->extractConstructorDependencies($filePath);
        
        $this->assertIsArray($dependencies);
        $this->assertCount(2, $dependencies);
        
        $this->assertNull($dependencies[0]['type']);
        $this->assertEquals('config', $dependencies[0]['name']);
        
        $this->assertNull($dependencies[1]['type']);
        $this->assertEquals('logger', $dependencies[1]['name']);
    }

    /**
     * Test parseClassInfo extracts comprehensive class information
     */
    public function testParseClassInfoExtractsComprehensiveInfo(): void
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
        return view("users/show");
    }
}';
        
        $filePath = $this->testFilesDir . '/UserController.php';
        file_put_contents($filePath, $code);
        
        $info = $this->parser->parseClassInfo($filePath);
        
        $this->assertIsArray($info);
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
     * Test parseClassInfo returns null for invalid file
     */
    public function testParseClassInfoReturnsNullForInvalidFile(): void
    {
        $info = $this->parser->parseClassInfo('/non/existent/file.php');
        
        $this->assertNull($info);
    }

    /**
     * Test wasLastParseSuccessful returns correct status
     */
    public function testWasLastParseSuccessfulReturnsCorrectStatus(): void
    {
        $code = '<?php class TestClass {}';
        $filePath = $this->testFilesDir . '/TestClass.php';
        file_put_contents($filePath, $code);
        
        $this->parser->parseClassInfo($filePath);
        
        $this->assertTrue($this->parser->wasLastParseSuccessful());
    }

    /**
     * Test wasLastParseSuccessful returns false for parse error
     */
    public function testWasLastParseSuccessfulReturnsFalseForError(): void
    {
        $code = '<?php class TestClass {';  // Malformed code
        $filePath = $this->testFilesDir . '/TestClass.php';
        file_put_contents($filePath, $code);
        
        $this->parser->parseClassInfo($filePath);
        
        $this->assertFalse($this->parser->wasLastParseSuccessful());
    }

    /**
     * Test getLastParseMetadata returns metadata
     */
    public function testGetLastParseMetadataReturnsMetadata(): void
    {
        $code = '<?php class TestClass {}';
        $filePath = $this->testFilesDir . '/TestClass.php';
        file_put_contents($filePath, $code);
        
        $this->parser->parseClassInfo($filePath);
        $metadata = $this->parser->getLastParseMetadata();
        
        $this->assertIsArray($metadata);
        $this->assertArrayHasKey('success', $metadata);
        $this->assertTrue($metadata['success']);
    }

    /**
     * Test getCodeParser returns underlying CodeParser instance
     */
    public function testGetCodeParserReturnsCodeParserInstance(): void
    {
        $codeParser = $this->parser->getCodeParser();
        
        $this->assertInstanceOf(CodeParser::class, $codeParser);
    }

    /**
     * Test extracting dependencies from complex controller
     */
    public function testExtractDependenciesFromComplexController(): void
    {
        $code = '<?php
namespace App\Controllers;

use App\Models\UserModel;
use App\Models\PostModel;
use App\Services\AuthService;
use App\Services\EmailService;

class UserController extends BaseController
{
    private $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function index()
    {
        $userModel = new UserModel();
        $users = $userModel->findAll();
        
        $postModel = new PostModel();
        $posts = $postModel->where("status", "published")->findAll();
        
        $config = Config::get("app.name");
        
        return view("users/index", ["users" => $users, "posts" => $posts]);
    }

    public function create()
    {
        $email = $this->authService->getCurrentUserEmail();
        EmailService::send($email, "Welcome");
        
        return redirect()->to("/users");
    }
}';
        
        $filePath = $this->testFilesDir . '/UserController.php';
        file_put_contents($filePath, $code);
        
        $dependencies = $this->parser->extractAllDependencies($filePath);
        
        // Verify use statements
        $this->assertContains('App\Models\UserModel', $dependencies['uses']);
        $this->assertContains('App\Models\PostModel', $dependencies['uses']);
        $this->assertContains('App\Services\AuthService', $dependencies['uses']);
        $this->assertContains('App\Services\EmailService', $dependencies['uses']);
        
        // Verify instantiations
        $this->assertCount(2, $dependencies['instantiations']);
        $modelClasses = array_column($dependencies['instantiations'], 'class');
        $this->assertContains('UserModel', $modelClasses);
        $this->assertContains('PostModel', $modelClasses);
        
        // Verify method calls
        $this->assertNotEmpty($dependencies['methodCalls']);
        $methodNames = array_column($dependencies['methodCalls'], 'method');
        $this->assertContains('findAll', $methodNames);
        $this->assertContains('where', $methodNames);
    }

    /**
     * Test extracting dependencies from service class
     */
    public function testExtractDependenciesFromServiceClass(): void
    {
        $code = '<?php
namespace App\Services;

use App\Repositories\UserRepository;
use App\Repositories\PostRepository;
use App\Services\EmailService;

class UserService
{
    public function __construct(
        private UserRepository $userRepository,
        private PostRepository $postRepository,
        private EmailService $emailService
    ) {}

    public function createUser(array $data): bool
    {
        $user = $this->userRepository->create($data);
        $this->emailService->sendWelcomeEmail($user);
        
        return true;
    }

    public function getUserPosts(int $userId): array
    {
        return $this->postRepository->findByUserId($userId);
    }
}';
        
        $filePath = $this->testFilesDir . '/UserService.php';
        file_put_contents($filePath, $code);
        
        // Test constructor dependencies
        $constructorDeps = $this->parser->extractConstructorDependencies($filePath);
        $this->assertCount(3, $constructorDeps);
        $this->assertEquals('UserRepository', $constructorDeps[0]['type']);
        $this->assertEquals('PostRepository', $constructorDeps[1]['type']);
        $this->assertEquals('EmailService', $constructorDeps[2]['type']);
        
        // Test use statements
        $uses = $this->parser->extractUseStatements($filePath);
        $this->assertContains('App\Repositories\UserRepository', $uses);
        $this->assertContains('App\Repositories\PostRepository', $uses);
        $this->assertContains('App\Services\EmailService', $uses);
        
        // Test method calls
        $calls = $this->parser->extractMethodCalls($filePath);
        $methodNames = array_column($calls, 'method');
        $this->assertContains('create', $methodNames);
        $this->assertContains('sendWelcomeEmail', $methodNames);
        $this->assertContains('findByUserId', $methodNames);
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

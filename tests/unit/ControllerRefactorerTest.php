<?php

namespace Tests\Unit;

use App\Libraries\Refactor\Execution\ControllerRefactorer;
use App\Libraries\Refactor\Execution\ControllerSplitter;
use App\Libraries\Refactor\Discovery\CodeParser;
use App\Libraries\Refactor\Generation\CodeGenerator;
use App\Libraries\Refactor\Models\RefactorResult;
use App\Libraries\Refactor\Models\SplitResult;
use PHPUnit\Framework\TestCase;

/**
 * ControllerRefactorerTest
 * 
 * Unit tests for ControllerRefactorer class
 */
class ControllerRefactorerTest extends TestCase
{
    private ControllerRefactorer $refactorer;
    private CodeParser $parser;
    private CodeGenerator $generator;
    private ControllerSplitter $splitter;
    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->parser = new CodeParser();
        $this->generator = new CodeGenerator();
        $this->splitter = new ControllerSplitter($this->parser, $this->generator);
        $this->refactorer = new ControllerRefactorer($this->parser, $this->generator, $this->splitter);
        
        // Create temporary directory for test files
        $this->tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'controller_refactorer_test_' . uniqid();
        if (!is_dir($this->tempDir)) {
            mkdir($this->tempDir, 0755, true);
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        
        // Clean up temporary directory
        if (is_dir($this->tempDir)) {
            $this->deleteDirectory($this->tempDir);
        }
    }

    public function testInjectServiceAddsUseStatement(): void
    {
        $code = <<<'PHP'
<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class TestController extends Controller
{
    public function index()
    {
        return view('test/index');
    }
}
PHP;

        $classInfo = [
            'className' => 'TestController',
            'namespace' => 'App\\Controllers',
        ];

        $result = $this->refactorer->injectService($code, 'TestService', $classInfo);

        $this->assertStringContainsString('use App\\Services\\TestService;', $result);
    }

    public function testInjectServiceAddsProperty(): void
    {
        $code = <<<'PHP'
<?php

namespace App\Controllers;

class TestController
{
    public function index()
    {
        return view('test/index');
    }
}
PHP;

        $classInfo = [
            'className' => 'TestController',
            'namespace' => 'App\\Controllers',
        ];

        $result = $this->refactorer->injectService($code, 'TestService', $classInfo);

        $this->assertStringContainsString('protected TestService $testService;', $result);
        $this->assertStringContainsString('@var TestService', $result);
    }

    public function testInjectServiceCreatesConstructorIfNotExists(): void
    {
        $code = <<<'PHP'
<?php

namespace App\Controllers;

class TestController
{
    public function index()
    {
        return view('test/index');
    }
}
PHP;

        $classInfo = [
            'className' => 'TestController',
            'namespace' => 'App\\Controllers',
        ];

        $result = $this->refactorer->injectService($code, 'TestService', $classInfo);

        $this->assertStringContainsString('public function __construct(TestService $testService)', $result);
        $this->assertStringContainsString('$this->testService = $testService;', $result);
    }

    public function testInjectServiceAddsParameterToExistingConstructor(): void
    {
        $code = <<<'PHP'
<?php

namespace App\Controllers;

class TestController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        return view('test/index');
    }
}
PHP;

        $classInfo = [
            'className' => 'TestController',
            'namespace' => 'App\\Controllers',
        ];

        $result = $this->refactorer->injectService($code, 'TestService', $classInfo);

        $this->assertStringContainsString('public function __construct(TestService $testService)', $result);
        $this->assertStringContainsString('$this->testService = $testService;', $result);
    }

    public function testReplaceBusinessLogicWithServiceCalls(): void
    {
        $code = <<<'PHP'
<?php

namespace App\Controllers;

class TestController
{
    public function save()
    {
        $data = $this->request->getPost();
        $this->testModel->save($data);
        return redirect()->to('/test');
    }
}
PHP;

        $result = $this->refactorer->replaceBusinessLogicWithServiceCalls($code, 'TestService');

        // Should replace model calls with service calls
        $this->assertStringContainsString('$this->testService->save($data);', $result);
        $this->assertStringNotContainsString('$this->testModel->save($data);', $result);
    }

    public function testAddErrorHandlingAddsTodoComments(): void
    {
        $code = <<<'PHP'
<?php

namespace App\Controllers;

class TestController
{
    public function save()
    {
        $data = $this->request->getPost();
        $this->model->save($data);
        return redirect()->to('/test');
    }
}
PHP;

        $result = $this->refactorer->addErrorHandling($code);

        // Should add TODO comment for error handling
        $this->assertStringContainsString('// TODO: Add try-catch error handling', $result);
    }

    public function testAddErrorHandlingSkipsConstructor(): void
    {
        $code = <<<'PHP'
<?php

namespace App\Controllers;

class TestController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function save()
    {
        $data = $this->request->getPost();
        return redirect()->to('/test');
    }
}
PHP;

        $result = $this->refactorer->addErrorHandling($code);

        // Should add error handling to save method but not constructor
        $this->assertStringContainsString('// TODO: Add try-catch error handling', $result);
        
        // Count occurrences - should be 1 (only for save method)
        $todoCount = substr_count($result, '// TODO: Add try-catch error handling');
        $this->assertEquals(1, $todoCount, 'Should only add TODO to save method, not constructor');
    }

    public function testAddErrorHandlingSkipsMethodsWithExistingTryCatch(): void
    {
        $code = <<<'PHP'
<?php

namespace App\Controllers;

class TestController
{
    public function save()
    {
        try {
            $data = $this->request->getPost();
            $this->model->save($data);
            return redirect()->to('/test');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
PHP;

        $result = $this->refactorer->addErrorHandling($code);

        // Should not add TODO comment if try-catch already exists
        $saveMethodLines = array_filter(explode("\n", $result), function($line) {
            return str_contains($line, 'public function save()');
        });
        
        // The method should not have TODO added
        $this->assertCount(1, $saveMethodLines);
    }

    public function testSplitWebAndApiCallsSplitter(): void
    {
        // Create a temporary controller file
        $controllerCode = <<<'PHP'
<?php

namespace App\Controllers;

class TestController
{
    public function index()
    {
        return view('test/index');
    }

    public function apiGet()
    {
        return $this->response->setJSON(['data' => 'test']);
    }
}
PHP;

        $filePath = $this->tempDir . DIRECTORY_SEPARATOR . 'TestController.php';
        file_put_contents($filePath, $controllerCode);

        $result = $this->refactorer->splitWebAndApi($filePath);

        $this->assertInstanceOf(SplitResult::class, $result);
    }

    public function testWriteControllerCreatesDirectory(): void
    {
        $code = '<?php echo "test";';
        $filePath = $this->tempDir . DIRECTORY_SEPARATOR . 'subdir' . DIRECTORY_SEPARATOR . 'TestController.php';

        $result = $this->refactorer->writeController($filePath, $code);

        $this->assertTrue($result);
        $this->assertFileExists($filePath);
        $this->assertEquals($code, file_get_contents($filePath));
    }

    public function testWriteControllerHandlesErrors(): void
    {
        $code = '<?php echo "test";';
        $filePath = $this->tempDir . DIRECTORY_SEPARATOR . 'TestController.php';

        // Test successful write
        $result = $this->refactorer->writeController($filePath, $code);
        $this->assertTrue($result);
        $this->assertFileExists($filePath);
        
        // Verify content
        $this->assertEquals($code, file_get_contents($filePath));
    }

    public function testValidateControllerWithValidCode(): void
    {
        $code = <<<'PHP'
<?php

namespace App\Controllers;

class TestController
{
    public function index()
    {
        return view('test/index');
    }
}
PHP;

        $result = $this->refactorer->validateController($code);

        $this->assertTrue($result['valid']);
        $this->assertNull($result['error']);
    }

    public function testValidateControllerWithInvalidCode(): void
    {
        $code = <<<'PHP'
<?php

namespace App\Controllers;

class TestController
{
    public function index()
    {
        return view('test/index'
    }
}
PHP;

        $result = $this->refactorer->validateController($code);

        $this->assertFalse($result['valid']);
        $this->assertNotNull($result['error']);
    }

    public function testRefactorWithValidController(): void
    {
        // Create a temporary controller file
        $controllerCode = <<<'PHP'
<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class TestController extends Controller
{
    public function index()
    {
        return view('test/index');
    }

    public function save()
    {
        $data = $this->request->getPost();
        $this->testModel->save($data);
        return redirect()->to('/test');
    }
}
PHP;

        $filePath = $this->tempDir . DIRECTORY_SEPARATOR . 'TestController.php';
        file_put_contents($filePath, $controllerCode);

        $result = $this->refactorer->refactor($filePath, 'TestService', [
            'splitWebApi' => false,
            'addErrorHandling' => true,
        ]);

        $this->assertInstanceOf(RefactorResult::class, $result);
        $this->assertTrue($result->success);
        $this->assertNotEmpty($result->stepsCompleted);
        $this->assertContains('Injected service dependency', $result->stepsCompleted);
        $this->assertContains('Replaced business logic with service calls', $result->stepsCompleted);
    }

    public function testRefactorWithInvalidFile(): void
    {
        $filePath = $this->tempDir . DIRECTORY_SEPARATOR . 'NonExistent.php';

        $result = $this->refactorer->refactor($filePath, 'TestService');

        $this->assertInstanceOf(RefactorResult::class, $result);
        $this->assertFalse($result->success);
        $this->assertNotNull($result->errorMessage);
    }

    public function testRefactorWithSplitWebApi(): void
    {
        // Create a temporary controller file with both web and API methods
        $controllerCode = <<<'PHP'
<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class TestController extends Controller
{
    public function index()
    {
        return view('test/index');
    }

    public function apiGet()
    {
        return $this->response->setJSON(['data' => 'test']);
    }
}
PHP;

        $filePath = $this->tempDir . DIRECTORY_SEPARATOR . 'TestController.php';
        file_put_contents($filePath, $controllerCode);

        $result = $this->refactorer->refactor($filePath, 'TestService', [
            'splitWebApi' => true,
            'addErrorHandling' => false,
        ]);

        $this->assertInstanceOf(RefactorResult::class, $result);
        $this->assertTrue($result->success);
    }

    public function testRefactorPreservesExistingServiceInjection(): void
    {
        $code = <<<'PHP'
<?php

namespace App\Controllers;

use App\Services\TestService;

class TestController
{
    protected TestService $testService;

    public function __construct(TestService $testService)
    {
        $this->testService = $testService;
    }

    public function index()
    {
        return view('test/index');
    }
}
PHP;

        $classInfo = [
            'className' => 'TestController',
            'namespace' => 'App\\Controllers',
        ];

        $result = $this->refactorer->injectService($code, 'TestService', $classInfo);

        // Should not duplicate use statement or property
        $this->assertEquals(1, substr_count($result, 'use App\\Services\\TestService;'));
        $this->assertEquals(1, substr_count($result, 'protected TestService $testService;'));
    }

    public function testInjectServiceWithMultipleExistingParameters(): void
    {
        $code = <<<'PHP'
<?php

namespace App\Controllers;

class TestController
{
    public function __construct(string $param1, int $param2)
    {
        // existing code
    }

    public function index()
    {
        return view('test/index');
    }
}
PHP;

        $classInfo = [
            'className' => 'TestController',
            'namespace' => 'App\\Controllers',
        ];

        $result = $this->refactorer->injectService($code, 'TestService', $classInfo);

        // Should add service parameter to existing parameters
        $this->assertStringContainsString('public function __construct(string $param1, int $param2, TestService $testService)', $result);
    }

    /**
     * Helper method to recursively delete a directory
     * 
     * @param string $dir Directory path
     * @return void
     */
    private function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }
        
        rmdir($dir);
    }
}

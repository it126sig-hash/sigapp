<?php

namespace Tests\Unit\Libraries\Refactor\Execution;

use App\Libraries\Refactor\Execution\ControllerSplitter;
use App\Libraries\Refactor\Models\SplitResult;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * ControllerSplitterTest
 * 
 * Unit tests for ControllerSplitter class
 * 
 * @package Tests\Unit\Libraries\Refactor\Execution
 */
class ControllerSplitterTest extends CIUnitTestCase
{
    private ControllerSplitter $splitter;
    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->splitter = new ControllerSplitter();
        $this->tempDir = sys_get_temp_dir() . '/controller_splitter_test_' . uniqid();
        mkdir($this->tempDir, 0777, true);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        
        // Clean up temp directory
        if (is_dir($this->tempDir)) {
            $files = glob($this->tempDir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            rmdir($this->tempDir);
        }
    }

    public function testIdentifyWebMethodsWithViewRendering()
    {
        $methods = [
            'index' => [
                'code' => "return view('home/index', \$data);",
                'visibility' => 'public',
            ],
            'dashboard' => [
                'code' => "\$data['content'] = 'dashboard';\nreturn view('template', \$data);",
                'visibility' => 'public',
            ],
        ];

        $webMethods = $this->splitter->identifyWebMethods($methods);

        $this->assertCount(2, $webMethods);
        $this->assertArrayHasKey('index', $webMethods);
        $this->assertArrayHasKey('dashboard', $webMethods);
    }

    public function testIdentifyApiMethodsWithJsonResponse()
    {
        $methods = [
            'getData' => [
                'code' => "\$data = ['result' => 'success'];\nreturn \$this->response->setJSON(\$data);",
                'visibility' => 'public',
            ],
            'getList' => [
                'code' => "return \$this->respond(\$data, 200);",
                'visibility' => 'public',
            ],
        ];

        $apiMethods = $this->splitter->identifyApiMethods($methods);

        $this->assertCount(2, $apiMethods);
        $this->assertArrayHasKey('getData', $apiMethods);
        $this->assertArrayHasKey('getList', $apiMethods);
    }

    public function testSplitMixedController()
    {
        // Create a sample mixed controller
        $controllerCode = <<<'PHP'
<?php

namespace App\Controllers;

use App\Models\UserModel;

class User extends BaseController
{
    public function index()
    {
        $data['users'] = [];
        return view('user/index', $data);
    }

    public function getUsers()
    {
        $model = new UserModel();
        $users = $model->findAll();
        return $this->response->setJSON($users);
    }

    public function create()
    {
        return view('user/create');
    }

    public function store()
    {
        $data = $this->request->getPost();
        return $this->response->setJSON(['success' => true]);
    }
}
PHP;

        $filePath = $this->tempDir . '/User.php';
        file_put_contents($filePath, $controllerCode);

        $result = $this->splitter->split($filePath);

        $this->assertInstanceOf(SplitResult::class, $result);
        $this->assertTrue($result->wasSplit);
        $this->assertCount(2, $result->webMethods);
        $this->assertCount(2, $result->apiMethods);
        $this->assertContains('index', $result->webMethods);
        $this->assertContains('create', $result->webMethods);
        $this->assertContains('getUsers', $result->apiMethods);
        $this->assertContains('store', $result->apiMethods);
    }

    public function testGenerateWebController()
    {
        $methods = [
            'index' => [
                'code' => "return view('home/index');",
                'visibility' => 'public',
                'params' => [],
                'return' => null,
            ],
        ];

        $code = $this->splitter->generateWebController(
            'Home',
            'App\\Controllers',
            [],
            $methods
        );

        $this->assertStringContainsString('namespace App\\Controllers;', $code);
        $this->assertStringContainsString('class Home extends BaseController', $code);
        $this->assertStringContainsString('public function index()', $code);
        $this->assertStringContainsString("return view('home/index');", $code);
    }

    public function testGenerateApiController()
    {
        $methods = [
            'getData' => [
                'code' => "return \$this->response->setJSON(['data' => 'test']);",
                'visibility' => 'public',
                'params' => [],
                'return' => null,
            ],
        ];

        $code = $this->splitter->generateApiController(
            'Home',
            'App\\Controllers',
            [],
            $methods
        );

        $this->assertStringContainsString('namespace App\\Controllers\\Api;', $code);
        $this->assertStringContainsString('use App\\Controllers\\Api\\BaseApiController;', $code);
        $this->assertStringContainsString('class HomeController extends BaseApiController', $code);
        $this->assertStringContainsString('public function getData()', $code);
    }

    public function testSplitWebOnlyController()
    {
        $controllerCode = <<<'PHP'
<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        return view('home/index');
    }

    public function about()
    {
        return view('home/about');
    }
}
PHP;

        $filePath = $this->tempDir . '/Home.php';
        file_put_contents($filePath, $controllerCode);

        $result = $this->splitter->split($filePath);

        $this->assertInstanceOf(SplitResult::class, $result);
        $this->assertFalse($result->wasSplit);
        $this->assertCount(2, $result->webMethods);
        $this->assertCount(0, $result->apiMethods);
        $this->assertTrue($result->hasWebController());
        $this->assertFalse($result->hasApiController());
    }

    public function testSplitApiOnlyController()
    {
        $controllerCode = <<<'PHP'
<?php

namespace App\Controllers\Api;

use App\Controllers\Api\BaseApiController;

class User extends BaseApiController
{
    public function index()
    {
        return $this->respond(['users' => []]);
    }

    public function show($id)
    {
        return $this->respond(['user' => ['id' => $id]]);
    }
}
PHP;

        $filePath = $this->tempDir . '/User.php';
        file_put_contents($filePath, $controllerCode);

        $result = $this->splitter->split($filePath);

        $this->assertInstanceOf(SplitResult::class, $result);
        $this->assertFalse($result->wasSplit);
        $this->assertCount(0, $result->webMethods);
        $this->assertCount(2, $result->apiMethods);
        $this->assertFalse($result->hasWebController());
        $this->assertTrue($result->hasApiController());
    }

    public function testSplitResultSummary()
    {
        $result = new SplitResult([
            'originalClassName' => 'TestController',
            'wasSplit' => true,
            'webMethods' => ['index', 'create'],
            'apiMethods' => ['getData', 'store'],
        ]);

        $summary = $result->getSummary();

        $this->assertEquals('TestController', $summary['originalClass']);
        $this->assertTrue($summary['wasSplit']);
        $this->assertEquals(2, $summary['webMethodCount']);
        $this->assertEquals(2, $summary['apiMethodCount']);
        $this->assertContains('index', $summary['webMethods']);
        $this->assertContains('getData', $summary['apiMethods']);
    }

    public function testHandleInvalidFilePath()
    {
        $result = $this->splitter->split('/nonexistent/file.php');

        $this->assertInstanceOf(SplitResult::class, $result);
        $this->assertFalse($result->wasSplit);
        $this->assertCount(0, $result->webMethods);
        $this->assertCount(0, $result->apiMethods);
    }

    public function testIdentifyMethodsWithMultiplePatterns()
    {
        $methods = [
            'webMethod1' => [
                'code' => "echo view('test');",
                'visibility' => 'public',
            ],
            'webMethod2' => [
                'code' => "\$this->response->setBody('<html></html>');\nreturn \$this->response;",
                'visibility' => 'public',
            ],
            'apiMethod1' => [
                'code' => "return \$this->success(\$data);",
                'visibility' => 'public',
            ],
            'apiMethod2' => [
                'code' => "return \$this->error('Failed');",
                'visibility' => 'public',
            ],
            'apiMethod3' => [
                'code' => "\$json = json_encode(\$data);\nreturn \$json;",
                'visibility' => 'public',
            ],
        ];

        $webMethods = $this->splitter->identifyWebMethods($methods);
        $apiMethods = $this->splitter->identifyApiMethods($methods);

        $this->assertCount(2, $webMethods);
        $this->assertCount(3, $apiMethods);
    }

    public function testGeneratedCodeHasProperStructure()
    {
        $methods = [
            'testMethod' => [
                'code' => "\$data = ['test' => 'value'];\nreturn view('test', \$data);",
                'visibility' => 'public',
                'params' => [
                    ['name' => 'id', 'type' => 'int'],
                ],
                'return' => 'string',
            ],
        ];

        $code = $this->splitter->generateWebController(
            'Test',
            'App\\Controllers',
            ['App\\Models\\TestModel'],
            $methods
        );

        // Check for proper PHP structure
        $this->assertStringStartsWith('<?php', $code);
        $this->assertStringContainsString('namespace App\\Controllers;', $code);
        $this->assertStringContainsString('use App\\Models\\TestModel;', $code);
        $this->assertStringContainsString('class Test extends BaseController', $code);
        $this->assertStringContainsString('public function testMethod(int $id): string', $code);
        
        // Validate PHP syntax
        $tempFile = $this->tempDir . '/generated_test.php';
        file_put_contents($tempFile, $code);
        
        $output = [];
        $returnCode = 0;
        exec("php -l " . escapeshellarg($tempFile) . " 2>&1", $output, $returnCode);
        
        $this->assertEquals(0, $returnCode, 'Generated code should have valid PHP syntax: ' . implode("\n", $output));
    }
}

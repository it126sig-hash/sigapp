<?php

namespace Tests\Unit;

use App\Libraries\Refactor\Generation\ServiceGenerator;
use App\Libraries\Refactor\Generation\CodeGenerator;
use App\Libraries\Refactor\Generation\ValidationExtractor;
use PHPUnit\Framework\TestCase;

/**
 * ServiceGeneratorTest
 * 
 * Unit tests for ServiceGenerator class
 */
class ServiceGeneratorTest extends TestCase
{
    private ServiceGenerator $generator;
    private CodeGenerator $codeGenerator;
    private ValidationExtractor $validationExtractor;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->codeGenerator = new CodeGenerator();
        $this->validationExtractor = new ValidationExtractor($this->codeGenerator);
        $this->generator = new ServiceGenerator($this->codeGenerator, $this->validationExtractor);
    }

    public function testGenerateServiceNameFromController(): void
    {
        $reflection = new \ReflectionClass($this->generator);
        $method = $reflection->getMethod('generateServiceName');
        $method->setAccessible(true);

        $this->assertEquals('TransaksiService', $method->invoke($this->generator, 'Transaksi'));
        $this->assertEquals('KavlingService', $method->invoke($this->generator, 'KavlingController'));
        $this->assertEquals('UserService', $method->invoke($this->generator, 'UserController'));
    }

    public function testDetectRepositoriesFromControllerCode(): void
    {
        $controllerCode = <<<'PHP'
<?php
namespace App\Controllers;

use App\Repositories\KavlingRepository;
use App\Repositories\TransaksiRepository;

class TransaksiController
{
    protected $kavlingRepo;
    protected $transaksiRepo;
    
    public function __construct()
    {
        $this->kavlingRepo = new KavlingRepository();
        $this->transaksiRepo = new TransaksiRepository();
    }
}
PHP;

        $repositories = $this->generator->detectRepositories($controllerCode);

        $this->assertContains('KavlingRepository', $repositories);
        $this->assertContains('TransaksiRepository', $repositories);
    }

    public function testDetectRepositoriesFromPropertyDeclarations(): void
    {
        $controllerCode = <<<'PHP'
<?php
namespace App\Controllers;

class TransaksiController
{
    /**
     * @var KavlingRepository
     */
    protected $kavlingRepo;
    
    /**
     * @var TransaksiRepository
     */
    protected $transaksiRepo;
}
PHP;

        $repositories = $this->generator->detectRepositories($controllerCode);

        $this->assertContains('KavlingRepository', $repositories);
        $this->assertContains('TransaksiRepository', $repositories);
    }

    public function testExtractBusinessLogicFromController(): void
    {
        $controllerCode = <<<'PHP'
<?php
namespace App\Controllers;

class TransaksiController
{
    public function __construct()
    {
        // Constructor should be skipped
    }
    
    public function index()
    {
        // Simple view method - no business logic
        return view('transaksi/index');
    }
    
    public function save()
    {
        // Has business logic - database insert
        $this->db->table('transaksi')->insert($data);
        return redirect()->to('/transaksi');
    }
    
    public function update($id)
    {
        // Has business logic - database update
        $this->transaksiModel->update($id, $data);
        return redirect()->to('/transaksi');
    }
}
PHP;

        $businessLogic = $this->generator->extractBusinessLogic($controllerCode);

        // Should extract save and update methods (not constructor or index)
        $this->assertGreaterThanOrEqual(2, count($businessLogic));
        
        $methodNames = array_column($businessLogic, 'name');
        $this->assertContains('save', $methodNames);
        $this->assertContains('update', $methodNames);
        $this->assertNotContains('__construct', $methodNames);
    }

    public function testGenerateServiceWithRepositories(): void
    {
        $repositories = ['KavlingRepository', 'TransaksiRepository'];
        
        $serviceCode = $this->generator->generateFromExtractedLogic(
            'Transaksi',
            [],
            $repositories
        );

        // Check namespace
        $this->assertStringContainsString('namespace App\\Services;', $serviceCode);
        
        // Check class name
        $this->assertStringContainsString('class TransaksiService', $serviceCode);
        
        // Check use statements
        $this->assertStringContainsString('use App\\Repositories\\KavlingRepository;', $serviceCode);
        $this->assertStringContainsString('use App\\Repositories\\TransaksiRepository;', $serviceCode);
        
        // Check properties
        $this->assertStringContainsString('protected BaseConnection $db;', $serviceCode);
        $this->assertStringContainsString('protected KavlingRepository $kavlingRepo;', $serviceCode);
        $this->assertStringContainsString('protected TransaksiRepository $transaksiRepo;', $serviceCode);
        
        // Check constructor
        $this->assertStringContainsString('public function __construct(', $serviceCode);
        $this->assertStringContainsString('BaseConnection $db', $serviceCode);
        $this->assertStringContainsString('KavlingRepository $kavlingRepo', $serviceCode);
        $this->assertStringContainsString('TransaksiRepository $transaksiRepo', $serviceCode);
    }

    public function testGenerateServiceMethodWithTransaction(): void
    {
        $logic = [
            'name' => 'saveTransaction',
            'method' => null,
            'hasTransaction' => true,
            'hasValidation' => false,
        ];

        $method = $this->generator->generateServiceMethod($logic);

        $this->assertEquals('saveTransaction', $method['name']);
        $this->assertEquals('public', $method['visibility']);
        $this->assertEquals('array', $method['return']);
        
        // Check transaction management in body
        $this->assertStringContainsString('$this->db->transStart()', $method['body']);
        $this->assertStringContainsString('$this->db->transComplete()', $method['body']);
        $this->assertStringContainsString('$this->db->transRollback()', $method['body']);
        $this->assertStringContainsString('try {', $method['body']);
        $this->assertStringContainsString('} catch (\\Throwable $e) {', $method['body']);
    }

    public function testGenerateServiceMethodWithValidation(): void
    {
        $logic = [
            'name' => 'validateAndSave',
            'method' => null,
            'hasTransaction' => false,
            'hasValidation' => true,
        ];

        $method = $this->generator->generateServiceMethod($logic);

        // Check validation comment in body
        $this->assertStringContainsString('// TODO: Add validation logic here', $method['body']);
        $this->assertStringContainsString('// Use validation rules extracted from controller', $method['body']);
    }

    public function testGenerateServiceMethodWithoutTransaction(): void
    {
        $logic = [
            'name' => 'getData',
            'method' => null,
            'hasTransaction' => false,
            'hasValidation' => false,
        ];

        $method = $this->generator->generateServiceMethod($logic);

        // Should have try-catch but no transaction management
        $this->assertStringContainsString('try {', $method['body']);
        $this->assertStringContainsString('} catch (\\Throwable $e) {', $method['body']);
        $this->assertStringNotContainsString('transStart', $method['body']);
        $this->assertStringNotContainsString('transComplete', $method['body']);
    }

    public function testAddTransactionManagementToMethodBody(): void
    {
        $originalBody = <<<'PHP'
$data = ['name' => 'Test'];
$this->repository->save($data);
return true;
PHP;

        $wrappedBody = $this->generator->addTransactionManagement($originalBody);

        $this->assertStringContainsString('$this->db->transStart()', $wrappedBody);
        $this->assertStringContainsString('$this->db->transComplete()', $wrappedBody);
        $this->assertStringContainsString('$this->db->transRollback()', $wrappedBody);
        $this->assertStringContainsString('$data = [\'name\' => \'Test\'];', $wrappedBody);
        $this->assertStringContainsString('$this->repository->save($data);', $wrappedBody);
    }

    public function testAddTransactionManagementSkipsIfAlreadyPresent(): void
    {
        $bodyWithTransaction = <<<'PHP'
$this->db->transStart();
$this->repository->save($data);
$this->db->transComplete();
PHP;

        $result = $this->generator->addTransactionManagement($bodyWithTransaction);

        // Should return unchanged
        $this->assertEquals($bodyWithTransaction, $result);
    }

    public function testGenerateResultObjectMethod(): void
    {
        $resultMethod = $this->generator->generateResultObject();

        $this->assertStringContainsString('protected function generateResultObject(', $resultMethod);
        $this->assertStringContainsString('bool $success', $resultMethod);
        $this->assertStringContainsString('string $message', $resultMethod);
        $this->assertStringContainsString('mixed $data = null', $resultMethod);
        $this->assertStringContainsString('return [', $resultMethod);
        $this->assertStringContainsString('\'success\' => $success,', $resultMethod);
        $this->assertStringContainsString('\'message\' => $message,', $resultMethod);
        $this->assertStringContainsString('\'data\' => $data,', $resultMethod);
    }

    public function testRepositoryToPropertyName(): void
    {
        $reflection = new \ReflectionClass($this->generator);
        $method = $reflection->getMethod('repositoryToPropertyName');
        $method->setAccessible(true);

        $this->assertEquals('kavlingRepo', $method->invoke($this->generator, 'KavlingRepository'));
        $this->assertEquals('transaksiRepo', $method->invoke($this->generator, 'TransaksiRepository'));
        $this->assertEquals('userRepo', $method->invoke($this->generator, 'UserRepository'));
    }

    public function testGenerateFromControllerWithFullCode(): void
    {
        $controllerCode = <<<'PHP'
<?php
namespace App\Controllers;

use App\Repositories\TransaksiRepository;

class TransaksiController extends BaseController
{
    protected $transaksiRepo;
    
    public function __construct()
    {
        $this->transaksiRepo = new TransaksiRepository();
    }
    
    public function save()
    {
        $data = $this->request->getPost();
        $this->db->transStart();
        $this->transaksiRepo->insert($data);
        $this->db->transComplete();
        
        return redirect()->to('/transaksi');
    }
}
PHP;

        $serviceCode = $this->generator->generateFromController('Transaksi', $controllerCode);

        // Verify generated service structure
        $this->assertStringContainsString('namespace App\\Services;', $serviceCode);
        $this->assertStringContainsString('class TransaksiService', $serviceCode);
        $this->assertStringContainsString('use App\\Repositories\\TransaksiRepository;', $serviceCode);
        $this->assertStringContainsString('protected TransaksiRepository $transaksiRepo;', $serviceCode);
    }

    public function testGeneratedCodeIsValidPHP(): void
    {
        $repositories = ['KavlingRepository'];
        
        $serviceCode = $this->generator->generateFromExtractedLogic(
            'Kavling',
            [],
            $repositories
        );

        // Validate syntax using CodeGenerator
        $validation = $this->codeGenerator->validateSyntax($serviceCode);
        
        $this->assertTrue($validation['valid'], 'Generated code should be valid PHP: ' . ($validation['error'] ?? ''));
    }

    public function testGenerateServiceWithMultipleMethods(): void
    {
        $businessLogic = [
            [
                'name' => 'create',
                'method' => null,
                'hasTransaction' => true,
                'hasValidation' => true,
            ],
            [
                'name' => 'update',
                'method' => null,
                'hasTransaction' => true,
                'hasValidation' => true,
            ],
            [
                'name' => 'delete',
                'method' => null,
                'hasTransaction' => true,
                'hasValidation' => false,
            ],
        ];

        $serviceCode = $this->generator->generateFromExtractedLogic(
            'Transaksi',
            $businessLogic,
            ['TransaksiRepository']
        );

        // Check all methods are present
        $this->assertStringContainsString('public function create(', $serviceCode);
        $this->assertStringContainsString('public function update(', $serviceCode);
        $this->assertStringContainsString('public function delete(', $serviceCode);
    }

    public function testGenerateServiceWithNoRepositories(): void
    {
        $serviceCode = $this->generator->generateFromExtractedLogic(
            'Simple',
            [],
            []
        );

        // Should still generate valid service with just database connection
        $this->assertStringContainsString('namespace App\\Services;', $serviceCode);
        $this->assertStringContainsString('class SimpleService', $serviceCode);
        $this->assertStringContainsString('protected BaseConnection $db;', $serviceCode);
        $this->assertStringContainsString('public function __construct(', $serviceCode);
    }
}

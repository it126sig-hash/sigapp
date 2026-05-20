<?php

namespace Tests\Libraries\Refactor\Integration;

use App\Libraries\Refactor\Analysis\AuditGenerator;
use App\Libraries\Refactor\Analysis\CodeAnalyzer;
use App\Libraries\Refactor\Analysis\DependencyAnalyzer;
use App\Libraries\Refactor\Analysis\ImpactAnalyzer;
use App\Libraries\Refactor\Analysis\PrioritizationSystem;
use App\Libraries\Refactor\Discovery\CodeParser;
use App\Libraries\Refactor\Discovery\FileScanner;
use App\Libraries\Refactor\Discovery\ModuleDiscovery;
use App\Libraries\Refactor\Models\AuditReport;
use App\Libraries\Refactor\Models\DependencyGraph;
use App\Libraries\Refactor\Models\Module;
use App\Libraries\Refactor\Models\ModuleInventory;
use App\Libraries\Refactor\Models\SecurityReport;
use App\Libraries\Refactor\Security\SecurityScanner;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Integration Test: Complete Workflow
 *
 * Tests the complete refactoring workflow on sample modules:
 * discover → analyze → prioritize → scan → audit
 */
class WorkflowTest extends CIUnitTestCase
{
    private string $fixtureDir;
    private string $controllersDir;
    private string $modelsDir;

    protected function setUp(): void
    {
        parent::setUp();

        // Create fixture directories for test modules
        $this->fixtureDir = WRITEPATH . 'tests/integration_workflow_' . uniqid();
        $this->controllersDir = $this->fixtureDir . '/Controllers';
        $this->modelsDir = $this->fixtureDir . '/Models';

        mkdir($this->controllersDir, 0755, true);
        mkdir($this->modelsDir, 0755, true);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->deleteDirectory($this->fixtureDir);
    }

    /**
     * Test complete workflow with a simple module (single controller, single model)
     */
    public function testCompleteWorkflowWithSimpleModule(): void
    {
        // Step 1: Create simple test module fixture
        $this->createSimpleModuleFixture();

        // Step 2: Discover modules
        $discovery = $this->createDiscovery();
        $inventory = $discovery->discover();

        $this->assertInstanceOf(ModuleInventory::class, $inventory);
        $this->assertGreaterThan(0, $inventory->getModuleCount());

        // Step 3: Analyze dependencies
        $depAnalyzer = new DependencyAnalyzer($inventory);
        $graph = $depAnalyzer->analyze();

        $this->assertInstanceOf(DependencyGraph::class, $graph);
        $this->assertNotEmpty($graph->nodes);

        // Step 4: Prioritize modules
        $prioritizer = new PrioritizationSystem($inventory, $graph);
        $prioritized = $prioritizer->prioritize();

        $this->assertIsArray($prioritized);
        $this->assertNotEmpty($prioritized);

        // Step 5: Security scan
        $scanner = new SecurityScanner();
        $modules = $inventory->modules;
        $firstModule = reset($modules);
        $this->assertInstanceOf(Module::class, $firstModule);

        $secReport = $scanner->scanModule($firstModule);
        $this->assertInstanceOf(SecurityReport::class, $secReport);

        // Step 6: Generate audit
        $codeAnalyzer = new CodeAnalyzer();
        $impactAnalyzer = new ImpactAnalyzer($inventory, $graph);
        $auditGen = new AuditGenerator($inventory, $scanner, $impactAnalyzer, $codeAnalyzer);

        $auditReport = $auditGen->generateAudit($firstModule->name);
        $this->assertInstanceOf(AuditReport::class, $auditReport);

        // Verify audit report has content
        $markdown = $auditReport->toMarkdown();
        $this->assertNotEmpty($markdown);
        $this->assertStringContainsString($firstModule->name, $markdown);
    }

    /**
     * Test complete workflow with a complex module (multiple models, dependencies, security issues)
     */
    public function testCompleteWorkflowWithComplexModule(): void
    {
        // Step 1: Create complex test module fixture
        $this->createComplexModuleFixture();

        // Step 2: Discover modules
        $discovery = $this->createDiscovery();
        $inventory = $discovery->discover();

        $this->assertInstanceOf(ModuleInventory::class, $inventory);
        $this->assertGreaterThan(0, $inventory->getModuleCount());

        // Step 3: Analyze dependencies
        $depAnalyzer = new DependencyAnalyzer($inventory);
        $graph = $depAnalyzer->analyze();

        $this->assertInstanceOf(DependencyGraph::class, $graph);
        // Complex module should have dependency edges
        $this->assertNotEmpty($graph->nodes);

        // Step 4: Prioritize
        $prioritizer = new PrioritizationSystem($inventory, $graph);
        $prioritized = $prioritizer->prioritize();
        $this->assertNotEmpty($prioritized);

        // Step 5: Security scan - should find vulnerabilities in complex module
        $scanner = new SecurityScanner();
        $modules = $inventory->modules;

        // Find the complex controller module
        $complexModule = null;
        foreach ($modules as $module) {
            if (str_contains($module->name, 'Order') || str_contains($module->name, 'Complex')) {
                $complexModule = $module;
                break;
            }
        }

        if ($complexModule === null) {
            $complexModule = reset($modules);
        }

        $this->assertNotFalse($complexModule, 'Should have at least one module');

        $secReport = $scanner->scanModule($complexModule);
        $this->assertInstanceOf(SecurityReport::class, $secReport);
        // Complex module has SQL injection and missing validation - should find vulnerabilities
        $this->assertGreaterThan(0, $secReport->getTotalCount());

        // Step 6: Generate audit
        $codeAnalyzer = new CodeAnalyzer();
        $impactAnalyzer = new ImpactAnalyzer($inventory, $graph);
        $auditGen = new AuditGenerator($inventory, $scanner, $impactAnalyzer, $codeAnalyzer);

        $auditReport = $auditGen->generateAudit($complexModule->name);
        $this->assertInstanceOf(AuditReport::class, $auditReport);

        // Verify audit report identifies issues
        $markdown = $auditReport->toMarkdown();
        $this->assertNotEmpty($markdown);
    }

    /**
     * Test that generated code passes PHP syntax validation
     */
    public function testGeneratedCodeQualityValidation(): void
    {
        // Create a simple fixture
        $this->createSimpleModuleFixture();

        // Discover and audit
        $discovery = $this->createDiscovery();
        $inventory = $discovery->discover();

        $this->assertGreaterThan(0, $inventory->getModuleCount());

        // Verify all fixture files are valid PHP
        foreach ($inventory->controllers as $controllerPath) {
            $this->assertValidPhpSyntax($controllerPath);
        }

        foreach ($inventory->models as $modelPath) {
            $this->assertValidPhpSyntax($modelPath);
        }
    }

    /**
     * Test that generated files exist in expected locations after discovery
     */
    public function testDiscoveredFilesExistInExpectedLocations(): void
    {
        $this->createSimpleModuleFixture();
        $this->createComplexModuleFixture();

        $discovery = $this->createDiscovery();
        $inventory = $discovery->discover();

        // Verify all discovered controller files exist
        foreach ($inventory->controllers as $path) {
            $this->assertFileExists($path, "Controller file should exist: {$path}");
        }

        // Verify all discovered model files exist
        foreach ($inventory->models as $path) {
            $this->assertFileExists($path, "Model file should exist: {$path}");
        }

        // Verify module inventory can be serialized and deserialized
        $json = $inventory->toJson();
        $this->assertJson($json);

        $restored = ModuleInventory::fromJson($json);
        $this->assertEquals($inventory->getModuleCount(), $restored->getModuleCount());
    }

    /**
     * Test dependency graph serialization roundtrip
     */
    public function testDependencyGraphSerializationRoundtrip(): void
    {
        $this->createComplexModuleFixture();

        $discovery = $this->createDiscovery();
        $inventory = $discovery->discover();

        $depAnalyzer = new DependencyAnalyzer($inventory);
        $graph = $depAnalyzer->analyze();

        // Serialize and deserialize
        $json = $graph->toJson();
        $this->assertJson($json);

        $restored = DependencyGraph::fromJson($json);
        $this->assertEquals($graph->nodes, $restored->nodes);
        $this->assertEquals($graph->edges, $restored->edges);
        $this->assertEquals($graph->impactScores, $restored->impactScores);
    }

    /**
     * Test security report serialization roundtrip
     */
    public function testSecurityReportSerializationRoundtrip(): void
    {
        $this->createComplexModuleFixture();

        $discovery = $this->createDiscovery();
        $inventory = $discovery->discover();

        $this->assertGreaterThan(0, $inventory->getModuleCount());

        $scanner = new SecurityScanner();
        $modules = $inventory->modules;
        $firstModule = reset($modules);
        $this->assertNotFalse($firstModule, 'Should have at least one module');

        $report = $scanner->scanModule($firstModule);

        // Serialize and deserialize
        $json = $report->toJson();
        $this->assertJson($json);

        $restored = SecurityReport::fromJson($json);
        $this->assertEquals($report->moduleName, $restored->moduleName);
        $this->assertEquals($report->getTotalCount(), $restored->getTotalCount());
    }

    // ========================================================================
    // Fixture Creation Helpers
    // ========================================================================

    /**
     * Create a ModuleDiscovery instance configured for the fixture directory
     * (with no excluded directories so writable/ path works)
     */
    private function createDiscovery(): ModuleDiscovery
    {
        $fileScanner = new FileScanner();
        $fileScanner->setExcludeDirs([]); // Don't exclude any dirs for test fixtures
        $codeParser = new CodeParser();
        return new ModuleDiscovery($this->fixtureDir, $fileScanner, $codeParser);
    }

    /**
     * Create a simple test module fixture (single controller with model)
     */
    private function createSimpleModuleFixture(): void
    {
        // Simple controller - class name ends with "Controller" for discovery
        $controllerCode = <<<'PHP'
<?php

namespace App\Controllers;

use App\Models\ProductModel;

class ProductController extends \CodeIgniter\Controller
{
    protected $productModel;

    public function __construct()
    {
        $this->productModel = new ProductModel();
    }

    public function index()
    {
        $products = $this->productModel->findAll();
        return view('product/index', ['products' => $products]);
    }

    public function show($id)
    {
        $product = $this->productModel->find($id);
        if (!$product) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Product not found');
        }
        return view('product/show', ['product' => $product]);
    }

    public function create()
    {
        return view('product/create');
    }

    public function store()
    {
        $data = $this->request->getPost();
        $this->productModel->insert($data);
        return redirect()->to('/product');
    }
}
PHP;

        // Simple model
        $modelCode = <<<'PHP'
<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table = 'products';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name', 'price', 'description', 'stock'];
    protected $returnType = 'array';
}
PHP;

        file_put_contents($this->controllersDir . '/ProductController.php', $controllerCode);
        file_put_contents($this->modelsDir . '/ProductModel.php', $modelCode);
    }

    /**
     * Create a complex test module fixture (multiple models, dependencies, security issues)
     */
    private function createComplexModuleFixture(): void
    {
        // Complex controller with security issues - class name ends with "Controller"
        $controllerCode = <<<'PHP'
<?php

namespace App\Controllers;

use App\Models\OrderModel;
use App\Models\CustomerModel;
use App\Models\PaymentModel;

class OrderController extends \CodeIgniter\Controller
{
    protected $orderModel;
    protected $customerModel;
    protected $paymentModel;
    protected $db;

    public function __construct()
    {
        $this->orderModel = new OrderModel();
        $this->customerModel = new CustomerModel();
        $this->paymentModel = new PaymentModel();
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        $orders = $this->orderModel->findAll();
        return view('order/index', ['orders' => $orders]);
    }

    public function show($id)
    {
        // SQL Injection vulnerability - raw query with user input
        $query = "SELECT * FROM orders WHERE id = " . $id;
        $order = $this->db->query($query)->getRowArray();

        $customer = $this->customerModel->find($order['customer_id']);
        return view('order/show', ['order' => $order, 'customer' => $customer]);
    }

    public function processPayment($orderId)
    {
        // Missing input validation
        $amount = $this->request->getPost('amount');
        $method = $this->request->getPost('method');

        // Business logic in controller
        $order = $this->orderModel->find($orderId);
        $tax = $amount * 0.11;
        $total = $amount + $tax;

        if ($total > $order['max_amount']) {
            return redirect()->back()->with('error', 'Amount exceeds limit');
        }

        // Direct database manipulation
        $this->db->table('payments')->insert([
            'order_id' => $orderId,
            'amount' => $total,
            'method' => $method,
            'status' => 'pending',
        ]);

        // Update order status
        $this->orderModel->update($orderId, ['status' => 'paid']);

        return redirect()->to('/order/' . $orderId);
    }

    public function search()
    {
        // Another SQL injection vulnerability
        $keyword = $this->request->getGet('q');
        $results = $this->db->query("SELECT * FROM orders WHERE description LIKE '%" . $keyword . "%'")->getResultArray();
        return $this->response->setJSON($results);
    }

    public function bulkDelete()
    {
        // Missing CSRF and authorization check
        $ids = $this->request->getPost('ids');
        foreach ($ids as $id) {
            $this->orderModel->delete($id);
        }
        return $this->response->setJSON(['success' => true]);
    }

    public function uploadInvoice($orderId)
    {
        // Insecure file upload - no validation
        $file = $this->request->getFile('invoice');
        $file->move(WRITEPATH . 'uploads/invoices/');

        $this->orderModel->update($orderId, [
            'invoice_path' => $file->getName(),
        ]);

        return redirect()->back();
    }
}
PHP;

        // Order model
        $orderModelCode = <<<'PHP'
<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderModel extends Model
{
    protected $table = 'orders';
    protected $primaryKey = 'id';
    protected $allowedFields = ['customer_id', 'description', 'amount', 'max_amount', 'status', 'invoice_path'];
    protected $returnType = 'array';
}
PHP;

        // Customer model
        $customerModelCode = <<<'PHP'
<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomerModel extends Model
{
    protected $table = 'customers';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name', 'email', 'phone', 'address'];
    protected $returnType = 'array';
}
PHP;

        // Payment model
        $paymentModelCode = <<<'PHP'
<?php

namespace App\Models;

use CodeIgniter\Model;

class PaymentModel extends Model
{
    protected $table = 'payments';
    protected $primaryKey = 'id';
    protected $allowedFields = ['order_id', 'amount', 'method', 'status'];
    protected $returnType = 'array';
}
PHP;

        file_put_contents($this->controllersDir . '/OrderController.php', $controllerCode);
        file_put_contents($this->modelsDir . '/OrderModel.php', $orderModelCode);
        file_put_contents($this->modelsDir . '/CustomerModel.php', $customerModelCode);
        file_put_contents($this->modelsDir . '/PaymentModel.php', $paymentModelCode);
    }

    /**
     * Assert that a PHP file has valid syntax
     */
    private function assertValidPhpSyntax(string $filePath): void
    {
        $this->assertFileExists($filePath);
        $output = [];
        $returnCode = 0;
        exec('php -l ' . escapeshellarg($filePath) . ' 2>&1', $output, $returnCode);
        $this->assertEquals(0, $returnCode, "PHP syntax error in {$filePath}: " . implode("\n", $output));
    }

    /**
     * Recursively delete a directory
     */
    private function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }
}

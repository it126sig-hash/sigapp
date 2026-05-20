<?php

namespace Tests\Unit\Refactor\Analysis;

use App\Libraries\Refactor\Analysis\ASTParser;
use App\Libraries\Refactor\Analysis\DependencyAnalyzer;
use App\Libraries\Refactor\Models\DependencyGraph;
use App\Libraries\Refactor\Models\Module;
use App\Libraries\Refactor\Models\ModuleInventory;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * DependencyAnalyzer Unit Tests
 * 
 * Tests for the DependencyAnalyzer class that builds dependency graphs
 * and calculates impact scores for modules.
 * 
 * @package Tests\Unit\Refactor\Analysis
 */
class DependencyAnalyzerTest extends CIUnitTestCase
{
    private string $testFilesDir;
    private ModuleInventory $inventory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testFilesDir = APPPATH . '../tests/_support/Refactor/DependencyAnalyzerTest';
        
        // Create test files directory
        if (!is_dir($this->testFilesDir)) {
            mkdir($this->testFilesDir, 0777, true);
        }

        // Create a sample inventory
        $this->inventory = new ModuleInventory();
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
     * Test DependencyAnalyzer can be instantiated
     */
    public function testCanBeInstantiated(): void
    {
        $analyzer = new DependencyAnalyzer($this->inventory);
        
        $this->assertInstanceOf(DependencyAnalyzer::class, $analyzer);
    }

    /**
     * Test DependencyAnalyzer can be instantiated with custom ASTParser
     */
    public function testCanBeInstantiatedWithCustomASTParser(): void
    {
        $astParser = new ASTParser();
        $analyzer = new DependencyAnalyzer($this->inventory, $astParser);
        
        $this->assertInstanceOf(DependencyAnalyzer::class, $analyzer);
    }

    /**
     * Test parseControllerDependencies extracts dependencies from controller
     */
    public function testParseControllerDependenciesExtractsDependencies(): void
    {
        // Create test controller file
        $controllerCode = '<?php
namespace App\Controllers;

use App\Models\UserModel;
use App\Models\TransaksiModel;
use App\Services\AuthService;

class UserController extends BaseController
{
    private $userModel;
    
    public function __construct()
    {
        $this->userModel = new UserModel();
    }
    
    public function index()
    {
        $transaksi = new TransaksiModel();
        return view("user/index");
    }
}';
        
        $controllerPath = $this->testFilesDir . '/UserController.php';
        file_put_contents($controllerPath, $controllerCode);
        
        // Add modules to inventory
        $userModule = new Module('User', $controllerPath);
        $transaksiModule = new Module('Transaksi', $this->testFilesDir . '/TransaksiController.php');
        $authModule = new Module('Auth', $this->testFilesDir . '/AuthController.php');
        
        $this->inventory->addModule($userModule);
        $this->inventory->addModule($transaksiModule);
        $this->inventory->addModule($authModule);
        
        $analyzer = new DependencyAnalyzer($this->inventory);
        $dependencies = $analyzer->parseControllerDependencies($controllerPath);
        
        $this->assertIsArray($dependencies);
        $this->assertContains('User', $dependencies);
        $this->assertContains('Transaksi', $dependencies);
    }

    /**
     * Test parseControllerDependencies returns empty array for non-existent file
     */
    public function testParseControllerDependenciesReturnsEmptyForNonExistentFile(): void
    {
        $analyzer = new DependencyAnalyzer($this->inventory);
        $dependencies = $analyzer->parseControllerDependencies('/non/existent/file.php');
        
        $this->assertIsArray($dependencies);
        $this->assertEmpty($dependencies);
    }

    /**
     * Test parseModelDependencies extracts dependencies from model
     */
    public function testParseModelDependenciesExtractsDependencies(): void
    {
        // Create test model file
        $modelCode = '<?php
namespace App\Models;

use App\Models\KeuanganModel;
use App\Models\KavlingModel;

class TransaksiModel extends BaseModel
{
    public function getWithKeuangan($id)
    {
        $keuangan = new KeuanganModel();
        return $keuangan->find($id);
    }
    
    public function getKavling()
    {
        $kavling = new KavlingModel();
        return $kavling->findAll();
    }
}';
        
        $modelPath = $this->testFilesDir . '/TransaksiModel.php';
        file_put_contents($modelPath, $modelCode);
        
        // Add modules to inventory
        $transaksiModule = new Module('Transaksi', $this->testFilesDir . '/TransaksiController.php');
        $keuanganModule = new Module('Keuangan', $this->testFilesDir . '/KeuanganController.php');
        $kavlingModule = new Module('Kavling', $this->testFilesDir . '/KavlingController.php');
        
        $this->inventory->addModule($transaksiModule);
        $this->inventory->addModule($keuanganModule);
        $this->inventory->addModule($kavlingModule);
        
        $analyzer = new DependencyAnalyzer($this->inventory);
        $dependencies = $analyzer->parseModelDependencies($modelPath);
        
        $this->assertIsArray($dependencies);
        $this->assertContains('Keuangan', $dependencies);
        $this->assertContains('Kavling', $dependencies);
    }

    /**
     * Test parseModelDependencies returns empty array for non-existent file
     */
    public function testParseModelDependenciesReturnsEmptyForNonExistentFile(): void
    {
        $analyzer = new DependencyAnalyzer($this->inventory);
        $dependencies = $analyzer->parseModelDependencies('/non/existent/file.php');
        
        $this->assertIsArray($dependencies);
        $this->assertEmpty($dependencies);
    }

    /**
     * Test analyze builds complete dependency graph
     */
    public function testAnalyzeBuildsCompleteDependencyGraph(): void
    {
        // Create test files
        $userControllerCode = '<?php
namespace App\Controllers;
use App\Models\UserModel;
class UserController {}';
        
        $transaksiControllerCode = '<?php
namespace App\Controllers;
use App\Models\TransaksiModel;
use App\Models\UserModel;
class TransaksiController {}';
        
        $userControllerPath = $this->testFilesDir . '/UserController.php';
        $transaksiControllerPath = $this->testFilesDir . '/TransaksiController.php';
        
        file_put_contents($userControllerPath, $userControllerCode);
        file_put_contents($transaksiControllerPath, $transaksiControllerCode);
        
        // Add modules to inventory
        $userModule = new Module('User', $userControllerPath);
        $transaksiModule = new Module('Transaksi', $transaksiControllerPath);
        
        $this->inventory->addModule($userModule);
        $this->inventory->addModule($transaksiModule);
        
        $analyzer = new DependencyAnalyzer($this->inventory);
        $graph = $analyzer->analyze();
        
        $this->assertInstanceOf(DependencyGraph::class, $graph);
        $this->assertCount(2, $graph->nodes);
        $this->assertContains('User', $graph->nodes);
        $this->assertContains('Transaksi', $graph->nodes);
    }

    /**
     * Test calculateImpactScores calculates correct scores
     */
    public function testCalculateImpactScoresCalculatesCorrectly(): void
    {
        // Create a simple dependency graph
        $graph = new DependencyGraph();
        $graph->addNode('User');
        $graph->addNode('Transaksi');
        $graph->addNode('Keuangan');
        
        // Transaksi depends on User
        // Keuangan depends on User
        // So User has impact score of 2
        $graph->addEdge('Transaksi', 'User');
        $graph->addEdge('Keuangan', 'User');
        
        $analyzer = new DependencyAnalyzer($this->inventory);
        $impactScores = $analyzer->calculateImpactScores($graph);
        
        $this->assertIsArray($impactScores);
        $this->assertEquals(2, $impactScores['User']); // 2 modules depend on User
        $this->assertEquals(0, $impactScores['Transaksi']); // No modules depend on Transaksi
        $this->assertEquals(0, $impactScores['Keuangan']); // No modules depend on Keuangan
    }

    /**
     * Test detectCircularDependencies detects simple cycle
     */
    public function testDetectCircularDependenciesDetectsSimpleCycle(): void
    {
        // Create a circular dependency: A -> B -> C -> A
        $graph = new DependencyGraph();
        $graph->addNode('A');
        $graph->addNode('B');
        $graph->addNode('C');
        
        $graph->addEdge('A', 'B');
        $graph->addEdge('B', 'C');
        $graph->addEdge('C', 'A');
        
        $analyzer = new DependencyAnalyzer($this->inventory);
        $circular = $analyzer->detectCircularDependencies($graph);
        
        $this->assertIsArray($circular);
        $this->assertNotEmpty($circular);
        $this->assertCount(1, $circular);
        
        // Check that the cycle contains all three nodes
        $cycle = $circular[0];
        $this->assertContains('A', $cycle);
        $this->assertContains('B', $cycle);
        $this->assertContains('C', $cycle);
    }

    /**
     * Test detectCircularDependencies returns empty for acyclic graph
     */
    public function testDetectCircularDependenciesReturnsEmptyForAcyclicGraph(): void
    {
        // Create an acyclic graph: A -> B -> C
        $graph = new DependencyGraph();
        $graph->addNode('A');
        $graph->addNode('B');
        $graph->addNode('C');
        
        $graph->addEdge('A', 'B');
        $graph->addEdge('B', 'C');
        
        $analyzer = new DependencyAnalyzer($this->inventory);
        $circular = $analyzer->detectCircularDependencies($graph);
        
        $this->assertIsArray($circular);
        $this->assertEmpty($circular);
    }

    /**
     * Test detectCircularDependencies detects self-loop
     */
    public function testDetectCircularDependenciesDetectsSelfLoop(): void
    {
        // Create a self-loop: A -> A
        $graph = new DependencyGraph();
        $graph->addNode('A');
        $graph->addEdge('A', 'A');
        
        $analyzer = new DependencyAnalyzer($this->inventory);
        $circular = $analyzer->detectCircularDependencies($graph);
        
        $this->assertIsArray($circular);
        $this->assertNotEmpty($circular);
    }

    /**
     * Test analyze sets impact scores in graph
     */
    public function testAnalyzeSetsImpactScoresInGraph(): void
    {
        // Create test files
        $userControllerCode = '<?php
namespace App\Controllers;
use App\Models\UserModel;
class UserController {}';
        
        $transaksiControllerCode = '<?php
namespace App\Controllers;
use App\Models\UserModel;
use App\Models\TransaksiModel;
class TransaksiController {}';
        
        $keuanganControllerCode = '<?php
namespace App\Controllers;
use App\Models\KeuanganModel;
class KeuanganController {}';
        
        $userControllerPath = $this->testFilesDir . '/UserController.php';
        $transaksiControllerPath = $this->testFilesDir . '/TransaksiController.php';
        $keuanganControllerPath = $this->testFilesDir . '/KeuanganController.php';
        
        file_put_contents($userControllerPath, $userControllerCode);
        file_put_contents($transaksiControllerPath, $transaksiControllerCode);
        file_put_contents($keuanganControllerPath, $keuanganControllerCode);
        
        // Add modules to inventory
        $userModule = new Module('User', $userControllerPath);
        $transaksiModule = new Module('Transaksi', $transaksiControllerPath);
        $keuanganModule = new Module('Keuangan', $keuanganControllerPath);
        
        $this->inventory->addModule($userModule);
        $this->inventory->addModule($transaksiModule);
        $this->inventory->addModule($keuanganModule);
        
        $analyzer = new DependencyAnalyzer($this->inventory);
        $graph = $analyzer->analyze();
        
        // User should have impact score of 2 (both User itself and Transaksi depend on it)
        $this->assertEquals(2, $graph->getImpactScore('User'));
        // Transaksi should have impact score of 1 (Transaksi itself depends on it)
        $this->assertEquals(1, $graph->getImpactScore('Transaksi'));
        // Keuangan should have impact score of 1 (Keuangan itself depends on it)
        $this->assertEquals(1, $graph->getImpactScore('Keuangan'));
    }

    /**
     * Helper method to recursively delete a directory
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

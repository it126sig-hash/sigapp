<?php

namespace Tests\Unit\Refactor\Discovery;

use App\Libraries\Refactor\Discovery\ModuleDiscovery;
use App\Libraries\Refactor\Discovery\FileScanner;
use App\Libraries\Refactor\Discovery\CodeParser;
use App\Libraries\Refactor\Models\ModuleInventory;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * ModuleDiscovery Unit Tests
 * 
 * Tests for the ModuleDiscovery component that scans and identifies modules
 * in the CodeIgniter 4 application.
 * 
 * Requirements: 1.1, 1.2, 1.3, 1.5
 * 
 * @package Tests\Unit\Refactor\Discovery
 */
class ModuleDiscoveryTest extends CIUnitTestCase
{
    private ModuleDiscovery $discovery;
    private FileScanner $scanner;
    private CodeParser $parser;
    private string $testAppPath;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->scanner = new FileScanner();
        // Remove 'tests' from exclude dirs for testing purposes
        $this->scanner->setExcludeDirs(['vendor', 'writable', 'public', '.git', '.idea', 'node_modules']);
        
        $this->parser = new CodeParser();
        $this->testAppPath = APPPATH . '../tests/_support/Refactor/TestApp';
        
        // Create test application structure
        $this->createTestAppStructure();
        
        $this->discovery = new ModuleDiscovery(
            $this->testAppPath,
            $this->scanner,
            $this->parser
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        
        // Clean up test application structure
        $this->cleanupTestAppStructure();
    }

    /**
     * Test scanning controllers directory
     * 
     * Requirement 1.1: THE Module_Discovery_System SHALL scan the app/Controllers 
     * directory and identify all controller files
     */
    public function testScanControllersFindsAllControllerFiles(): void
    {
        $controllers = $this->discovery->scanControllers();
        
        $this->assertIsArray($controllers);
        $this->assertNotEmpty($controllers, 'Should find controller files');
        
        // Verify all returned files are controller files
        foreach ($controllers as $file) {
            $this->assertStringEndsWith('.php', $file);
            $this->assertStringContainsString('Controllers', $file);
            $this->assertFileExists($file);
        }
        
        // Verify specific test controllers are found
        $controllerNames = array_map(fn($path) => basename($path), $controllers);
        $this->assertContains('UserController.php', $controllerNames);
        $this->assertContains('ProductController.php', $controllerNames);
    }

    /**
     * Test scanning models directory
     * 
     * Requirement 1.2: THE Module_Discovery_System SHALL scan the app/Models 
     * directory and identify all model files
     */
    public function testScanModelsFindsAllModelFiles(): void
    {
        $models = $this->discovery->scanModels();
        
        $this->assertIsArray($models);
        $this->assertNotEmpty($models, 'Should find model files');
        
        // Verify all returned files are model files
        foreach ($models as $file) {
            $this->assertStringEndsWith('.php', $file);
            $this->assertStringContainsString('Models', $file);
            $this->assertFileExists($file);
        }
        
        // Verify specific test models are found
        $modelNames = array_map(fn($path) => basename($path), $models);
        $this->assertContains('UserModel.php', $modelNames);
        $this->assertContains('ProductModel.php', $modelNames);
    }

    /**
     * Test handling missing controllers directory gracefully
     * 
     * Requirement 1.1: Should handle missing directories without throwing exceptions
     */
    public function testScanControllersHandlesMissingDirectoryGracefully(): void
    {
        // Create discovery with non-existent path
        $discovery = new ModuleDiscovery(
            '/non/existent/path',
            $this->scanner,
            $this->parser
        );
        
        // Should return empty array, not throw exception
        $controllers = $discovery->scanControllers();
        
        $this->assertIsArray($controllers);
        $this->assertEmpty($controllers);
    }

    /**
     * Test handling missing models directory gracefully
     * 
     * Requirement 1.2: Should handle missing directories without throwing exceptions
     */
    public function testScanModelsHandlesMissingDirectoryGracefully(): void
    {
        // Create discovery with non-existent path
        $discovery = new ModuleDiscovery(
            '/non/existent/path',
            $this->scanner,
            $this->parser
        );
        
        // Should return empty array, not throw exception
        $models = $discovery->scanModels();
        
        $this->assertIsArray($models);
        $this->assertEmpty($models);
    }

    /**
     * Test detecting existing services
     * 
     * Requirement 1.5: THE Module_Discovery_System SHALL detect existing Service 
     * and Repository classes if they already exist
     */
    public function testScanServicesDetectsExistingServices(): void
    {
        $services = $this->discovery->scanServices();
        
        $this->assertIsArray($services);
        $this->assertNotEmpty($services, 'Should find service files');
        
        // Verify all returned files are service files
        foreach ($services as $file) {
            $this->assertStringEndsWith('.php', $file);
            $this->assertStringContainsString('Services', $file);
            $this->assertFileExists($file);
        }
        
        // Verify specific test service is found
        $serviceNames = array_map(fn($path) => basename($path), $services);
        $this->assertContains('UserService.php', $serviceNames);
    }

    /**
     * Test detecting existing repositories
     * 
     * Requirement 1.5: THE Module_Discovery_System SHALL detect existing Service 
     * and Repository classes if they already exist
     */
    public function testScanRepositoriesDetectsExistingRepositories(): void
    {
        $repositories = $this->discovery->scanRepositories();
        
        $this->assertIsArray($repositories);
        $this->assertNotEmpty($repositories, 'Should find repository files');
        
        // Verify all returned files are repository files
        foreach ($repositories as $file) {
            $this->assertStringEndsWith('.php', $file);
            $this->assertStringContainsString('Repositories', $file);
            $this->assertFileExists($file);
        }
        
        // Verify specific test repository is found
        $repositoryNames = array_map(fn($path) => basename($path), $repositories);
        $this->assertContains('UserRepository.php', $repositoryNames);
    }

    /**
     * Test handling missing services directory gracefully
     * 
     * Requirement 1.5: Should handle missing directories without throwing exceptions
     */
    public function testScanServicesHandlesMissingDirectoryGracefully(): void
    {
        // Create discovery with path that has no Services directory
        $discovery = new ModuleDiscovery(
            '/non/existent/path',
            $this->scanner,
            $this->parser
        );
        
        // Should return empty array, not throw exception
        $services = $discovery->scanServices();
        
        $this->assertIsArray($services);
        $this->assertEmpty($services);
    }

    /**
     * Test handling missing repositories directory gracefully
     * 
     * Requirement 1.5: Should handle missing directories without throwing exceptions
     */
    public function testScanRepositoriesHandlesMissingDirectoryGracefully(): void
    {
        // Create discovery with path that has no Repositories directory
        $discovery = new ModuleDiscovery(
            '/non/existent/path',
            $this->scanner,
            $this->parser
        );
        
        // Should return empty array, not throw exception
        $repositories = $discovery->scanRepositories();
        
        $this->assertIsArray($repositories);
        $this->assertEmpty($repositories);
    }

    /**
     * Test identifying relationships between controllers and models
     * 
     * Requirement 1.3: THE Module_Discovery_System SHALL identify relationships 
     * between controllers and models based on code analysis
     */
    public function testIdentifyRelationshipsLinksControllersWithModels(): void
    {
        $inventory = $this->discovery->discover();
        
        $this->assertInstanceOf(ModuleInventory::class, $inventory);
        $this->assertNotEmpty($inventory->modules);
        
        // Get the User module
        $userModule = $inventory->getModule('User');
        $this->assertNotNull($userModule, 'User module should exist');
        
        // Verify controller path is set
        $this->assertNotEmpty($userModule->controllerPath);
        $this->assertStringContainsString('UserController.php', $userModule->controllerPath);
        
        // Verify related models are identified
        $this->assertNotEmpty($userModule->modelPaths, 'User module should have related models');
        $this->assertCount(1, $userModule->modelPaths);
        
        $modelPath = $userModule->modelPaths[0];
        $this->assertStringContainsString('UserModel.php', $modelPath);
    }

    /**
     * Test identifying relationships with services
     * 
     * Requirement 1.3: Should identify relationships with existing services
     */
    public function testIdentifyRelationshipsLinksControllersWithServices(): void
    {
        $inventory = $this->discovery->discover();
        
        // Get the User module
        $userModule = $inventory->getModule('User');
        $this->assertNotNull($userModule);
        
        // Verify related service is identified
        $this->assertNotNull($userModule->servicePath, 'User module should have related service');
        $this->assertStringContainsString('UserService.php', $userModule->servicePath);
    }

    /**
     * Test identifying relationships with repositories
     * 
     * Requirement 1.3: Should identify relationships with existing repositories
     */
    public function testIdentifyRelationshipsLinksControllersWithRepositories(): void
    {
        $inventory = $this->discovery->discover();
        
        // Get the User module
        $userModule = $inventory->getModule('User');
        $this->assertNotNull($userModule);
        
        // Verify related repository is identified
        $this->assertNotNull($userModule->repositoryPath, 'User module should have related repository');
        $this->assertStringContainsString('UserRepository.php', $userModule->repositoryPath);
    }

    /**
     * Test complete discovery workflow
     * 
     * Requirements: 1.1, 1.2, 1.3, 1.5
     */
    public function testDiscoverReturnsCompleteInventory(): void
    {
        $inventory = $this->discovery->discover();
        
        $this->assertInstanceOf(ModuleInventory::class, $inventory);
        
        // Verify controllers were scanned
        $this->assertNotEmpty($inventory->controllers);
        $this->assertGreaterThanOrEqual(2, count($inventory->controllers));
        
        // Verify models were scanned
        $this->assertNotEmpty($inventory->models);
        $this->assertGreaterThanOrEqual(2, count($inventory->models));
        
        // Verify services were scanned
        $this->assertNotEmpty($inventory->services);
        $this->assertGreaterThanOrEqual(1, count($inventory->services));
        
        // Verify repositories were scanned
        $this->assertNotEmpty($inventory->repositories);
        $this->assertGreaterThanOrEqual(1, count($inventory->repositories));
        
        // Verify modules were created
        $this->assertNotEmpty($inventory->modules);
        $this->assertGreaterThanOrEqual(2, count($inventory->modules));
        
        // Verify discoveredAt timestamp is set
        $this->assertInstanceOf(\DateTime::class, $inventory->discoveredAt);
    }

    /**
     * Test module extraction includes methods
     * 
     * Requirement 1.4: Should generate complete module inventory with metadata
     */
    public function testDiscoveryExtractsModuleMethods(): void
    {
        $inventory = $this->discovery->discover();
        
        $userModule = $inventory->getModule('User');
        $this->assertNotNull($userModule);
        
        // Verify methods are extracted
        $this->assertNotEmpty($userModule->methods);
        $this->assertContains('index', $userModule->methods);
        $this->assertContains('show', $userModule->methods);
    }

    /**
     * Test module extraction includes routes
     * 
     * Requirement 1.4: Should generate complete module inventory with metadata
     */
    public function testDiscoveryExtractsModuleRoutes(): void
    {
        $inventory = $this->discovery->discover();
        
        $userModule = $inventory->getModule('User');
        $this->assertNotNull($userModule);
        
        // Verify routes are extracted
        $this->assertNotEmpty($userModule->routes);
        
        // Check for expected route patterns
        $routeStrings = implode(' ', $userModule->routes);
        $this->assertStringContainsString('user', strtolower($routeStrings));
    }

    /**
     * Test handling controllers without related models
     */
    public function testIdentifyRelationshipsHandlesControllersWithoutModels(): void
    {
        $inventory = $this->discovery->discover();
        
        // Product controller doesn't use ProductModel in our test setup
        $productModule = $inventory->getModule('Product');
        $this->assertNotNull($productModule);
        
        // Should have empty model paths array, not null
        $this->assertIsArray($productModule->modelPaths);
    }

    /**
     * Test module names are extracted correctly
     */
    public function testModuleNamesAreExtractedCorrectly(): void
    {
        $inventory = $this->discovery->discover();
        
        $moduleNames = $inventory->getModuleNames();
        
        $this->assertContains('User', $moduleNames);
        $this->assertContains('Product', $moduleNames);
    }

    /**
     * Create test application structure
     */
    private function createTestAppStructure(): void
    {
        // Clean up first if exists
        if (is_dir($this->testAppPath)) {
            $this->deleteDirectory($this->testAppPath);
        }

        // Create base directory
        mkdir($this->testAppPath, 0777, true);

        // Create Controllers directory with test controllers
        $controllersDir = $this->testAppPath . '/Controllers';
        mkdir($controllersDir, 0777, true);
        
        // UserController with UserModel dependency
        file_put_contents($controllersDir . '/UserController.php', '<?php
namespace App\Controllers;

use App\Models\UserModel;

class UserController extends BaseController
{
    public function index()
    {
        $model = new UserModel();
        return view("users/index");
    }

    public function show($id)
    {
        $model = new UserModel();
        return view("users/show");
    }
}');

        // ProductController without model dependency
        file_put_contents($controllersDir . '/ProductController.php', '<?php
namespace App\Controllers;

class ProductController extends BaseController
{
    public function index()
    {
        return view("products/index");
    }

    public function create()
    {
        return view("products/create");
    }
}');

        // Create Models directory with test models
        $modelsDir = $this->testAppPath . '/Models';
        mkdir($modelsDir, 0777, true);
        
        file_put_contents($modelsDir . '/UserModel.php', '<?php
namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = "users";
}');

        file_put_contents($modelsDir . '/ProductModel.php', '<?php
namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table = "products";
}');

        // Create Services directory with test service
        $servicesDir = $this->testAppPath . '/Services';
        mkdir($servicesDir, 0777, true);
        
        file_put_contents($servicesDir . '/UserService.php', '<?php
namespace App\Services;

class UserService
{
    public function getUsers()
    {
        return [];
    }
}');

        // Create Repositories directory with test repository
        $repositoriesDir = $this->testAppPath . '/Repositories';
        mkdir($repositoriesDir, 0777, true);
        
        file_put_contents($repositoriesDir . '/UserRepository.php', '<?php
namespace App\Repositories;

class UserRepository
{
    public function findAll()
    {
        return [];
    }
}');
    }

    /**
     * Clean up test application structure
     */
    private function cleanupTestAppStructure(): void
    {
        if (is_dir($this->testAppPath)) {
            $this->deleteDirectory($this->testAppPath);
        }
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

<?php

namespace App\Libraries\Refactor\Discovery;

use App\Libraries\Refactor\Models\Module;
use App\Libraries\Refactor\Models\ModuleInventory;

/**
 * ModuleDiscovery
 * 
 * Discovers all modules in the CodeIgniter 4 application by scanning controllers,
 * models, services, and repositories, then identifying relationships between them.
 * 
 * Requirements: 1.1, 1.2, 1.3, 1.4, 1.5
 * 
 * @package App\Libraries\Refactor\Discovery
 */
class ModuleDiscovery
{
    /**
     * @var string Application root path
     */
    private string $appPath;

    /**
     * @var FileScanner File scanner utility
     */
    private FileScanner $fileScanner;

    /**
     * @var CodeParser Code parser utility
     */
    private CodeParser $codeParser;

    /**
     * Constructor
     * 
     * @param string $appPath Application root path (typically APPPATH)
     * @param FileScanner $fileScanner File scanner utility
     * @param CodeParser $codeParser Code parser utility
     */
    public function __construct(
        string $appPath,
        FileScanner $fileScanner,
        CodeParser $codeParser
    ) {
        $this->appPath = rtrim($appPath, '/\\');
        $this->fileScanner = $fileScanner;
        $this->codeParser = $codeParser;
    }

    /**
     * Discover all modules in the application
     * 
     * @return ModuleInventory Complete module inventory
     */
    public function discover(): ModuleInventory
    {
        $inventory = new ModuleInventory();

        // Scan for all component types
        $inventory->controllers = $this->scanControllers();
        $inventory->models = $this->scanModels();
        $inventory->services = $this->scanServices();
        $inventory->repositories = $this->scanRepositories();

        // Build modules from controllers and identify relationships
        $modules = $this->identifyRelationships($inventory);

        // Add modules to inventory
        foreach ($modules as $module) {
            $inventory->addModule($module);
        }

        return $inventory;
    }

    /**
     * Scan for all controller files
     * 
     * Requirement 1.1: THE Module_Discovery_System SHALL scan the app/Controllers 
     * directory and identify all controller files
     * 
     * @return array<string> Array of controller file paths
     */
    public function scanControllers(): array
    {
        $controllersPath = $this->appPath . DIRECTORY_SEPARATOR . 'Controllers';

        if (!is_dir($controllersPath)) {
            return [];
        }

        try {
            $this->fileScanner->setFilters([FileScanner::FILTER_CONTROLLERS]);
            $files = $this->fileScanner->scan($controllersPath);
            
            // Filter to ensure we only get actual controller files
            return array_filter($files, function($file) {
                $classInfo = $this->codeParser->parseClassInfo($file);
                if ($classInfo === null || $classInfo['className'] === null) {
                    return false;
                }
                
                // Check if it's a controller (ends with Controller or extends BaseController)
                return str_ends_with($classInfo['className'], 'Controller') || 
                       $classInfo['extends'] === 'BaseController' ||
                       str_contains($classInfo['extends'] ?? '', 'Controller');
            });
        } catch (\Exception $e) {
            // Log error and return empty array for graceful degradation
            error_log("Error scanning controllers: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Scan for all model files
     * 
     * Requirement 1.2: THE Module_Discovery_System SHALL scan the app/Models 
     * directory and identify all model files
     * 
     * @return array<string> Array of model file paths
     */
    public function scanModels(): array
    {
        $modelsPath = $this->appPath . DIRECTORY_SEPARATOR . 'Models';

        if (!is_dir($modelsPath)) {
            return [];
        }

        try {
            $this->fileScanner->setFilters([FileScanner::FILTER_MODELS]);
            $files = $this->fileScanner->scan($modelsPath);
            
            // Filter to ensure we only get actual model files
            return array_filter($files, function($file) {
                $classInfo = $this->codeParser->parseClassInfo($file);
                if ($classInfo === null || $classInfo['className'] === null) {
                    return false;
                }
                
                // Check if it's a model (ends with Model or extends Model/BaseModel)
                return str_ends_with($classInfo['className'], 'Model') || 
                       $classInfo['extends'] === 'Model' ||
                       $classInfo['extends'] === 'BaseModel' ||
                       str_contains($classInfo['extends'] ?? '', 'Model');
            });
        } catch (\Exception $e) {
            // Log error and return empty array for graceful degradation
            error_log("Error scanning models: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Scan for existing service classes
     * 
     * Requirement 1.5: THE Module_Discovery_System SHALL detect existing Service 
     * and Repository classes if they already exist
     * 
     * @return array<string> Array of service file paths
     */
    public function scanServices(): array
    {
        $servicesPath = $this->appPath . DIRECTORY_SEPARATOR . 'Services';

        if (!is_dir($servicesPath)) {
            return [];
        }

        try {
            $this->fileScanner->setFilters([FileScanner::FILTER_SERVICES]);
            $files = $this->fileScanner->scan($servicesPath);
            
            // Filter to ensure we only get actual service files
            return array_filter($files, function($file) {
                $classInfo = $this->codeParser->parseClassInfo($file);
                if ($classInfo === null || $classInfo['className'] === null) {
                    return false;
                }
                
                // Check if it's a service (ends with Service or in Services directory)
                return str_ends_with($classInfo['className'], 'Service') || 
                       str_contains($file, DIRECTORY_SEPARATOR . 'Services' . DIRECTORY_SEPARATOR);
            });
        } catch (\Exception $e) {
            // Log error and return empty array for graceful degradation
            error_log("Error scanning services: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Scan for existing repository classes
     * 
     * Requirement 1.5: THE Module_Discovery_System SHALL detect existing Service 
     * and Repository classes if they already exist
     * 
     * @return array<string> Array of repository file paths
     */
    public function scanRepositories(): array
    {
        $repositoriesPath = $this->appPath . DIRECTORY_SEPARATOR . 'Repositories';

        if (!is_dir($repositoriesPath)) {
            return [];
        }

        try {
            $this->fileScanner->setFilters([FileScanner::FILTER_REPOSITORIES]);
            $files = $this->fileScanner->scan($repositoriesPath);
            
            // Filter to ensure we only get actual repository files
            return array_filter($files, function($file) {
                $classInfo = $this->codeParser->parseClassInfo($file);
                if ($classInfo === null || $classInfo['className'] === null) {
                    return false;
                }
                
                // Check if it's a repository (ends with Repository or in Repositories directory)
                return str_ends_with($classInfo['className'], 'Repository') || 
                       str_contains($file, DIRECTORY_SEPARATOR . 'Repositories' . DIRECTORY_SEPARATOR);
            });
        } catch (\Exception $e) {
            // Log error and return empty array for graceful degradation
            error_log("Error scanning repositories: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Identify relationships between controllers and models
     * 
     * Requirements:
     * - 1.3: THE Module_Discovery_System SHALL identify relationships between 
     *        controllers and models based on code analysis
     * - 1.4: THE Module_Discovery_System SHALL generate a complete module inventory 
     *        with file paths and basic metadata
     * 
     * @param ModuleInventory $inventory Inventory with scanned files
     * @return array<Module> Array of Module objects
     */
    public function identifyRelationships(ModuleInventory $inventory): array
    {
        $modules = [];

        // Build a lookup map for models, services, and repositories by class name
        $modelMap = $this->buildClassNameMap($inventory->models);
        $serviceMap = $this->buildClassNameMap($inventory->services);
        $repositoryMap = $this->buildClassNameMap($inventory->repositories);

        // Process each controller
        foreach ($inventory->controllers as $controllerPath) {
            $classInfo = $this->codeParser->parseClassInfo($controllerPath);

            if ($classInfo === null || $classInfo['className'] === null) {
                // Skip files that couldn't be parsed or don't contain a class
                continue;
            }

            $moduleName = $this->extractModuleName($classInfo['className']);
            $module = new Module($moduleName, $controllerPath);

            // Extract public methods (excluding inherited BaseController methods)
            $module->methods = $this->filterPublicMethods($classInfo['methods']);

            // Extract routes from controller methods (basic route detection)
            $module->routes = $this->extractRoutes($controllerPath, $module->methods);

            // Identify related models from use statements and instantiations
            $module->modelPaths = $this->identifyRelatedModels(
                $controllerPath,
                $classInfo['uses'],
                $modelMap
            );

            // Identify related service if exists
            $module->servicePath = $this->findRelatedComponent(
                $moduleName,
                $serviceMap,
                'Service'
            );

            // Identify related repository if exists
            $module->repositoryPath = $this->findRelatedComponent(
                $moduleName,
                $repositoryMap,
                'Repository'
            );

            $modules[] = $module;
        }

        return $modules;
    }

    /**
     * Build a map of class names to file paths
     * 
     * @param array<string> $filePaths Array of file paths
     * @return array<string, string> Map of class name => file path
     */
    private function buildClassNameMap(array $filePaths): array
    {
        $map = [];

        foreach ($filePaths as $filePath) {
            $classInfo = $this->codeParser->parseClassInfo($filePath);

            if ($classInfo !== null && $classInfo['className'] !== null) {
                $map[$classInfo['className']] = $filePath;
            }
        }

        return $map;
    }

    /**
     * Extract module name from controller class name
     * 
     * Examples:
     * - "TransaksiController" => "Transaksi"
     * - "Transaksi" => "Transaksi"
     * - "KeuanganController" => "Keuangan"
     * 
     * @param string $className Controller class name
     * @return string Module name
     */
    private function extractModuleName(string $className): string
    {
        // Remove "Controller" suffix if present
        if (str_ends_with($className, 'Controller')) {
            return substr($className, 0, -10);
        }

        return $className;
    }

    /**
     * Filter public methods (exclude magic methods and common base methods)
     * 
     * @param array<string> $methods All methods from class
     * @return array<string> Filtered public methods
     */
    private function filterPublicMethods(array $methods): array
    {
        // Common base controller methods to exclude
        $excludeMethods = [
            '__construct',
            '__destruct',
            '__call',
            '__get',
            '__set',
            'initController',
            'getResponse',
            'getRequest',
            'setValidator',
            'validate',
        ];

        return array_values(array_filter($methods, function ($method) use ($excludeMethods) {
            // Exclude magic methods (starting with __)
            if (str_starts_with($method, '__')) {
                return false;
            }

            // Exclude common base methods
            if (in_array($method, $excludeMethods, true)) {
                return false;
            }

            return true;
        }));
    }

    /**
     * Identify related models for a controller
     * 
     * @param string $controllerPath Path to controller file
     * @param array<string> $useStatements Use statements from controller
     * @param array<string, string> $modelMap Map of model class names to paths
     * @return array<string> Array of related model file paths
     */
    private function identifyRelatedModels(
        string $controllerPath,
        array $useStatements,
        array $modelMap
    ): array {
        $relatedModels = [];

        // Check use statements for model imports
        foreach ($useStatements as $use) {
            $className = $this->extractClassNameFromFQN($use);

            if (str_ends_with($className, 'Model') && isset($modelMap[$className])) {
                $relatedModels[] = $modelMap[$className];
            }
        }

        // Also check for model instantiations in the code
        $instantiations = $this->codeParser->extractModelInstantiations($controllerPath);

        foreach ($instantiations as $instantiation) {
            $className = $instantiation['class'];

            if (isset($modelMap[$className]) && !in_array($modelMap[$className], $relatedModels, true)) {
                $relatedModels[] = $modelMap[$className];
            }
        }

        return array_unique($relatedModels);
    }

    /**
     * Find related service or repository for a module
     * 
     * @param string $moduleName Module name (e.g., "Transaksi")
     * @param array<string, string> $componentMap Map of class names to paths
     * @param string $suffix Component suffix (e.g., "Service" or "Repository")
     * @return string|null Path to related component or null if not found
     */
    private function findRelatedComponent(
        string $moduleName,
        array $componentMap,
        string $suffix
    ): ?string {
        $expectedClassName = $moduleName . $suffix;

        return $componentMap[$expectedClassName] ?? null;
    }

    /**
     * Extract routes from controller methods
     * 
     * This method performs basic route detection by analyzing controller methods
     * and generating likely route patterns based on CodeIgniter 4 conventions.
     * 
     * @param string $controllerPath Path to controller file
     * @param array<string> $methods Public methods in the controller
     * @return array<string> Array of likely route patterns
     */
    private function extractRoutes(string $controllerPath, array $methods): array
    {
        $routes = [];
        
        // Extract controller name from file path
        $fileName = basename($controllerPath, '.php');
        $controllerName = $this->extractModuleName($fileName);
        
        // Convert to lowercase for URL segments
        $urlSegment = strtolower($controllerName);
        
        // Map common method names to HTTP methods and routes
        $routeMap = [
            'index' => "GET /{$urlSegment}",
            'show' => "GET /{$urlSegment}/(:segment)",
            'create' => "GET /{$urlSegment}/create",
            'store' => "POST /{$urlSegment}",
            'edit' => "GET /{$urlSegment}/(:segment)/edit",
            'update' => "PUT /{$urlSegment}/(:segment)",
            'delete' => "DELETE /{$urlSegment}/(:segment)",
            'destroy' => "DELETE /{$urlSegment}/(:segment)",
            'list' => "GET /{$urlSegment}/list",
            'view' => "GET /{$urlSegment}/view/(:segment)",
            'add' => "GET /{$urlSegment}/add",
            'save' => "POST /{$urlSegment}/save",
            'remove' => "POST /{$urlSegment}/remove/(:segment)",
        ];
        
        // Generate routes for known methods
        foreach ($methods as $method) {
            if (isset($routeMap[$method])) {
                $routes[] = $routeMap[$method];
            } else {
                // For unknown methods, generate a generic GET route
                $routes[] = "GET /{$urlSegment}/{$method}";
            }
        }
        
        // Try to detect additional routes from the actual controller code
        $additionalRoutes = $this->detectRoutesFromCode($controllerPath, $urlSegment);
        $routes = array_merge($routes, $additionalRoutes);
        
        return array_unique($routes);
    }
    
    /**
     * Detect additional routes by analyzing controller code
     * 
     * @param string $controllerPath Path to controller file
     * @param string $urlSegment URL segment for the controller
     * @return array<string> Array of detected routes
     */
    private function detectRoutesFromCode(string $controllerPath, string $urlSegment): array
    {
        $routes = [];
        
        try {
            $code = file_get_contents($controllerPath);
            if ($code === false) {
                return $routes;
            }
            
            // Look for form actions that might indicate POST routes
            if (preg_match_all('/form_open\([\'"]([^\'"]+)[\'"]/', $code, $matches)) {
                foreach ($matches[1] as $action) {
                    if (str_contains($action, $urlSegment)) {
                        $routes[] = "POST {$action}";
                    }
                }
            }
            
            // Look for redirect patterns that might indicate routes
            if (preg_match_all('/redirect\(\)->to\([\'"]([^\'"]+)[\'"]/', $code, $matches)) {
                foreach ($matches[1] as $redirect) {
                    if (str_contains($redirect, $urlSegment)) {
                        $routes[] = "GET {$redirect}";
                    }
                }
            }
            
            // Look for AJAX endpoints (methods that return JSON)
            if (preg_match_all('/return\s+\$this->response->setJSON/', $code, $matches)) {
                // If controller has JSON responses, it likely has API endpoints
                $routes[] = "POST /{$urlSegment}/api";
                $routes[] = "GET /{$urlSegment}/api";
            }
            
        } catch (\Exception $e) {
            // Silently ignore errors in route detection
            error_log("Error detecting routes from code: " . $e->getMessage());
        }
        
        return $routes;
    }

    /**
     * Extract class name from fully qualified name
     * 
     * Example: "App\Models\TransaksiModel" => "TransaksiModel"
     * 
     * @param string $fqn Fully qualified class name
     * @return string Class name without namespace
     */
    private function extractClassNameFromFQN(string $fqn): string
    {
        $parts = explode('\\', $fqn);
        return end($parts);
    }
}

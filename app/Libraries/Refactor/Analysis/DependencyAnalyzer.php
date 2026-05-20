<?php

namespace App\Libraries\Refactor\Analysis;

use App\Libraries\Refactor\Models\DependencyGraph;
use App\Libraries\Refactor\Models\ModuleInventory;

/**
 * Dependency Analyzer
 * 
 * Analyzes dependencies between modules by parsing controller and model code
 * to build a dependency graph. Calculates impact scores and detects circular
 * dependencies to help prioritize refactoring work.
 * 
 * @package App\Libraries\Refactor\Analysis
 */
class DependencyAnalyzer
{
    /**
     * @var ModuleInventory Module inventory containing all discovered modules
     */
    private ModuleInventory $inventory;

    /**
     * @var ASTParser AST parser for extracting dependencies from code
     */
    private ASTParser $astParser;

    /**
     * Constructor
     * 
     * @param ModuleInventory $inventory Module inventory to analyze
     * @param ASTParser|null $astParser Optional AST parser instance for dependency injection
     */
    public function __construct(ModuleInventory $inventory, ?ASTParser $astParser = null)
    {
        $this->inventory = $inventory;
        $this->astParser = $astParser ?? new ASTParser();
    }

    /**
     * Analyze all modules and build a complete dependency graph
     * 
     * This method orchestrates the entire dependency analysis process:
     * 1. Parse controller dependencies
     * 2. Parse model dependencies
     * 3. Build the dependency graph
     * 4. Detect circular dependencies
     * 5. Calculate impact scores
     * 
     * @return DependencyGraph Complete dependency graph with impact scores
     */
    public function analyze(): DependencyGraph
    {
        $graph = new DependencyGraph();

        // Add all modules as nodes
        foreach ($this->inventory->getModuleNames() as $moduleName) {
            $graph->addNode($moduleName);
        }

        // Parse controller dependencies for all modules
        foreach ($this->inventory->modules as $module) {
            $controllerDeps = $this->parseControllerDependencies($module->controllerPath);
            
            foreach ($controllerDeps as $dependency) {
                // Only add edges for dependencies that exist in our inventory
                if (in_array($dependency, $this->inventory->getModuleNames(), true)) {
                    $graph->addEdge($module->name, $dependency);
                }
            }
        }

        // Parse model dependencies for all modules
        foreach ($this->inventory->modules as $module) {
            foreach ($module->modelPaths as $modelPath) {
                $modelDeps = $this->parseModelDependencies($modelPath);
                
                foreach ($modelDeps as $dependency) {
                    // Only add edges for dependencies that exist in our inventory
                    if (in_array($dependency, $this->inventory->getModuleNames(), true)) {
                        $graph->addEdge($module->name, $dependency);
                    }
                }
            }
        }

        // Detect circular dependencies
        $graph->circular = $this->detectCircularDependencies($graph);

        // Calculate impact scores
        $impactScores = $this->calculateImpactScores($graph);
        foreach ($impactScores as $module => $score) {
            $graph->setImpactScore($module, $score);
        }

        return $graph;
    }

    /**
     * Parse controller dependencies from a controller file
     * 
     * Extracts dependencies by analyzing:
     * - Use statements (imported classes)
     * - Class instantiations (new ClassName())
     * - Constructor dependencies (dependency injection)
     * - Method calls to other classes
     * 
     * Returns an array of module names that this controller depends on.
     * 
     * @param string $filePath Path to controller file
     * @return array<string> Array of module names this controller depends on
     */
    public function parseControllerDependencies(string $filePath): array
    {
        if (!file_exists($filePath)) {
            return [];
        }

        $dependencies = [];

        // Extract all dependency information
        $allDeps = $this->astParser->extractAllDependencies($filePath);
        
        // Process use statements
        foreach ($allDeps['uses'] as $useStatement) {
            $moduleName = $this->extractModuleNameFromClass($useStatement);
            if ($moduleName && !in_array($moduleName, $dependencies, true)) {
                $dependencies[] = $moduleName;
            }
        }

        // Process class instantiations
        foreach ($allDeps['instantiations'] as $instantiation) {
            $moduleName = $this->extractModuleNameFromClass($instantiation['class']);
            if ($moduleName && !in_array($moduleName, $dependencies, true)) {
                $dependencies[] = $moduleName;
            }
        }

        // Process constructor dependencies
        $constructorDeps = $this->astParser->extractConstructorDependencies($filePath);
        foreach ($constructorDeps as $dep) {
            if ($dep['type']) {
                $moduleName = $this->extractModuleNameFromClass($dep['type']);
                if ($moduleName && !in_array($moduleName, $dependencies, true)) {
                    $dependencies[] = $moduleName;
                }
            }
        }

        return $dependencies;
    }

    /**
     * Parse model dependencies from a model file
     * 
     * Extracts dependencies by analyzing:
     * - Use statements (imported classes)
     * - Class instantiations (new ClassName())
     * - Method calls to other models
     * - Model relationships (belongsTo, hasMany, etc.)
     * 
     * Returns an array of module names that this model depends on.
     * 
     * @param string $filePath Path to model file
     * @return array<string> Array of module names this model depends on
     */
    public function parseModelDependencies(string $filePath): array
    {
        if (!file_exists($filePath)) {
            return [];
        }

        $dependencies = [];

        // Extract all dependency information
        $allDeps = $this->astParser->extractAllDependencies($filePath);
        
        // Process use statements
        foreach ($allDeps['uses'] as $useStatement) {
            $moduleName = $this->extractModuleNameFromClass($useStatement);
            if ($moduleName && !in_array($moduleName, $dependencies, true)) {
                $dependencies[] = $moduleName;
            }
        }

        // Process class instantiations
        foreach ($allDeps['instantiations'] as $instantiation) {
            $moduleName = $this->extractModuleNameFromClass($instantiation['class']);
            if ($moduleName && !in_array($moduleName, $dependencies, true)) {
                $dependencies[] = $moduleName;
            }
        }

        // Process method calls to identify model relationships
        foreach ($allDeps['methodCalls'] as $call) {
            if ($call['class']) {
                $moduleName = $this->extractModuleNameFromClass($call['class']);
                if ($moduleName && !in_array($moduleName, $dependencies, true)) {
                    $dependencies[] = $moduleName;
                }
            }
        }

        return $dependencies;
    }

    /**
     * Detect circular dependencies in the dependency graph
     * 
     * Uses depth-first search (DFS) to detect cycles in the dependency graph.
     * A circular dependency occurs when module A depends on B, B depends on C,
     * and C depends on A (forming a cycle).
     * 
     * Returns an array of circular dependency chains, where each chain is an
     * array of module names forming a cycle.
     * 
     * @param DependencyGraph $graph Dependency graph to analyze
     * @return array<int, array<string>> Array of circular dependency chains
     */
    public function detectCircularDependencies(DependencyGraph $graph): array
    {
        $circular = [];
        $visited = [];
        $recursionStack = [];

        foreach ($graph->nodes as $node) {
            if (!isset($visited[$node])) {
                $this->detectCyclesDFS($node, $graph, $visited, $recursionStack, [], $circular);
            }
        }

        return $circular;
    }

    /**
     * Depth-first search helper for cycle detection
     * 
     * @param string $node Current node being visited
     * @param DependencyGraph $graph Dependency graph
     * @param array<string, bool> &$visited Visited nodes
     * @param array<string, bool> &$recursionStack Current recursion stack
     * @param array<string> $path Current path being explored
     * @param array<int, array<string>> &$circular Array to store detected cycles
     * @return bool True if a cycle is detected
     */
    private function detectCyclesDFS(
        string $node,
        DependencyGraph $graph,
        array &$visited,
        array &$recursionStack,
        array $path,
        array &$circular
    ): bool {
        $visited[$node] = true;
        $recursionStack[$node] = true;
        $path[] = $node;

        $dependencies = $graph->getDependencies($node);
        
        foreach ($dependencies as $dependency) {
            if (!isset($visited[$dependency])) {
                if ($this->detectCyclesDFS($dependency, $graph, $visited, $recursionStack, $path, $circular)) {
                    return true;
                }
            } elseif (isset($recursionStack[$dependency]) && $recursionStack[$dependency]) {
                // Found a cycle - extract the cycle from the path
                $cycleStart = array_search($dependency, $path, true);
                if ($cycleStart !== false) {
                    $cycle = array_slice($path, $cycleStart);
                    $cycle[] = $dependency; // Close the cycle
                    
                    // Check if this cycle is already recorded (avoid duplicates)
                    $cycleKey = implode('->', $cycle);
                    $isDuplicate = false;
                    foreach ($circular as $existingCycle) {
                        if (implode('->', $existingCycle) === $cycleKey) {
                            $isDuplicate = true;
                            break;
                        }
                    }
                    
                    if (!$isDuplicate) {
                        $circular[] = $cycle;
                    }
                }
                return true;
            }
        }

        $recursionStack[$node] = false;
        return false;
    }

    /**
     * Calculate impact scores for all modules
     * 
     * Impact score = number of modules that depend on this module.
     * A higher impact score means more modules will be affected if this
     * module is refactored, making it a higher-risk refactoring target.
     * 
     * Modules with impact score of 0 are "leaf modules" - safe starting points
     * for refactoring since no other modules depend on them.
     * 
     * @param DependencyGraph $graph Dependency graph
     * @return array<string, int> Array mapping module names to impact scores
     */
    public function calculateImpactScores(DependencyGraph $graph): array
    {
        $impactScores = [];

        // Initialize all modules with score 0
        foreach ($graph->nodes as $node) {
            $impactScores[$node] = 0;
        }

        // Count how many modules depend on each module
        foreach ($graph->nodes as $node) {
            $dependents = $graph->getDependents($node);
            $impactScores[$node] = count($dependents);
        }

        return $impactScores;
    }

    /**
     * Extract module name from a fully qualified class name
     * 
     * Converts class names like:
     * - "App\Models\UserModel" -> "User"
     * - "App\Controllers\TransaksiController" -> "Transaksi"
     * - "UserModel" -> "User"
     * - "TransaksiController" -> "Transaksi"
     * 
     * @param string $className Fully qualified or simple class name
     * @return string|null Module name or null if not a recognized pattern
     */
    private function extractModuleNameFromClass(string $className): ?string
    {
        // Get the simple class name (last part after namespace)
        $parts = explode('\\', $className);
        $simpleClassName = end($parts);

        // Remove common suffixes
        $suffixes = ['Controller', 'Model', 'Service', 'Repository'];
        foreach ($suffixes as $suffix) {
            if (str_ends_with($simpleClassName, $suffix)) {
                $moduleName = substr($simpleClassName, 0, -strlen($suffix));
                return $moduleName ?: null;
            }
        }

        // If no suffix matched, check if this class exists in our inventory
        // by checking if any module has this name
        if (in_array($simpleClassName, $this->inventory->getModuleNames(), true)) {
            return $simpleClassName;
        }

        return null;
    }
}

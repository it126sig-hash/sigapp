<?php

namespace App\Libraries\Refactor\Analysis;

use App\Libraries\Refactor\Models\DependencyGraph;
use App\Libraries\Refactor\Models\ModuleInventory;
use App\Libraries\Refactor\Models\PriorityScore;
use App\Libraries\Refactor\Models\SecurityReport;

/**
 * Prioritization System
 * 
 * Ranks modules for refactoring based on multiple factors:
 * - Impact score (number of dependents)
 * - Dependency depth
 * - Security vulnerabilities
 * - Manual overrides
 * 
 * Identifies leaf modules (safe starting points) and core modules (high-risk targets).
 * Generates a recommended refactoring order.
 * 
 * @package App\Libraries\Refactor\Analysis
 */
class PrioritizationSystem
{
    /**
     * Threshold for core module classification (number of dependents)
     */
    private const CORE_MODULE_THRESHOLD = 3;

    /**
     * Module inventory containing all discovered modules
     */
    private ModuleInventory $inventory;

    /**
     * Dependency graph showing module relationships
     */
    private DependencyGraph $dependencyGraph;

    /**
     * Security reports indexed by module name
     * 
     * @var array<string, SecurityReport>
     */
    private array $securityReports = [];

    /**
     * Manual priority overrides indexed by module name
     * 
     * @var array<string, int>
     */
    private array $manualOverrides = [];

    /**
     * Calculated priority scores indexed by module name
     * 
     * @var array<string, PriorityScore>
     */
    private array $priorityScores = [];

    /**
     * Create a new PrioritizationSystem instance
     * 
     * @param ModuleInventory $inventory Module inventory
     * @param DependencyGraph $dependencyGraph Dependency graph
     */
    public function __construct(
        ModuleInventory $inventory,
        DependencyGraph $dependencyGraph
    ) {
        $this->inventory = $inventory;
        $this->dependencyGraph = $dependencyGraph;
    }

    /**
     * Add a security report for a module
     * 
     * @param SecurityReport $report
     * @return void
     */
    public function addSecurityReport(SecurityReport $report): void
    {
        $this->securityReports[$report->moduleName] = $report;
    }

    /**
     * Apply manual priority override for a module
     * Lower values = higher priority
     * 
     * @param string $module Module name
     * @param int $priority Priority value (1 = highest priority)
     * @return void
     */
    public function applyManualOverride(string $module, int $priority): void
    {
        if ($priority < 1) {
            throw new \InvalidArgumentException('Priority must be at least 1');
        }

        $this->manualOverrides[$module] = $priority;
    }

    /**
     * Identify leaf modules (modules with no dependents)
     * These are safe starting points for refactoring
     * 
     * @return string[]
     */
    public function identifyLeafModules(): array
    {
        $leafModules = [];

        foreach ($this->inventory->getModuleNames() as $moduleName) {
            $impactScore = $this->dependencyGraph->getImpactScore($moduleName);
            
            if ($impactScore === 0) {
                $leafModules[] = $moduleName;
            }
        }

        return $leafModules;
    }

    /**
     * Identify core modules (modules with many dependents)
     * These are high-risk refactoring targets
     * 
     * @return string[]
     */
    public function identifyCoreModules(): array
    {
        $coreModules = [];

        foreach ($this->inventory->getModuleNames() as $moduleName) {
            $impactScore = $this->dependencyGraph->getImpactScore($moduleName);
            
            if ($impactScore >= self::CORE_MODULE_THRESHOLD) {
                $coreModules[] = $moduleName;
            }
        }

        return $coreModules;
    }

    /**
     * Calculate priority score for a module
     * 
     * Lower score = higher priority (should be refactored sooner)
     * 
     * Score calculation:
     * - Base score = impact score (number of dependents)
     * - Subtract points for critical vulnerabilities (makes it higher priority)
     * - Add points for dependency depth (deeper = lower priority)
     * 
     * @param string $module Module name
     * @return float Priority score
     */
    public function calculatePriorityScore(string $module): float
    {
        // Base score: impact score (modules with fewer dependents get lower scores)
        $impactScore = $this->dependencyGraph->getImpactScore($module);
        $score = (float) $impactScore;

        // Factor in critical vulnerabilities (subtract to increase priority)
        $criticalVulns = $this->getCriticalVulnerabilityCount($module);
        $score -= ($criticalVulns * 2.0); // Each critical vuln reduces score by 2

        // Factor in dependency depth (add to decrease priority for deep dependencies)
        $depth = $this->calculateDependencyDepth($module);
        $score += ($depth * 0.5); // Each level of depth adds 0.5 to score

        return $score;
    }

    /**
     * Get the number of critical vulnerabilities for a module
     * 
     * @param string $module Module name
     * @return int
     */
    private function getCriticalVulnerabilityCount(string $module): int
    {
        if (!isset($this->securityReports[$module])) {
            return 0;
        }

        return $this->securityReports[$module]->getCriticalCount();
    }

    /**
     * Calculate dependency depth for a module
     * (How many levels deep in the dependency tree)
     * 
     * @param string $module Module name
     * @param array<string> $visited Visited modules (for cycle detection)
     * @return int Depth level
     */
    private function calculateDependencyDepth(string $module, array $visited = []): int
    {
        // Prevent infinite recursion in circular dependencies
        if (in_array($module, $visited, true)) {
            return 0;
        }

        $visited[] = $module;
        $dependencies = $this->dependencyGraph->getDependencies($module);

        if (empty($dependencies)) {
            return 0;
        }

        $maxDepth = 0;
        foreach ($dependencies as $dependency) {
            $depth = $this->calculateDependencyDepth($dependency, $visited);
            $maxDepth = max($maxDepth, $depth);
        }

        return $maxDepth + 1;
    }

    /**
     * Determine module category based on impact score
     * 
     * @param int $impactScore
     * @return string
     */
    private function determineCategory(int $impactScore): string
    {
        if ($impactScore === 0) {
            return PriorityScore::CATEGORY_LEAF;
        } elseif ($impactScore >= self::CORE_MODULE_THRESHOLD) {
            return PriorityScore::CATEGORY_CORE;
        } else {
            return PriorityScore::CATEGORY_INTERMEDIATE;
        }
    }

    /**
     * Generate priority scores for all modules
     * 
     * @return array<string, PriorityScore>
     */
    private function generatePriorityScores(): array
    {
        $scores = [];

        foreach ($this->inventory->getModuleNames() as $moduleName) {
            $score = $this->calculatePriorityScore($moduleName);
            $impactScore = $this->dependencyGraph->getImpactScore($moduleName);
            $depth = $this->calculateDependencyDepth($moduleName);
            $criticalVulns = $this->getCriticalVulnerabilityCount($moduleName);
            $category = $this->determineCategory($impactScore);

            $priorityScore = new PriorityScore(
                $moduleName,
                $score,
                $impactScore,
                $depth,
                $criticalVulns,
                $category
            );

            // Apply manual override if exists
            if (isset($this->manualOverrides[$moduleName])) {
                $priorityScore->manualPriority = $this->manualOverrides[$moduleName];
            }

            $scores[$moduleName] = $priorityScore;
        }

        $this->priorityScores = $scores;
        return $scores;
    }

    /**
     * Prioritize modules and return ordered list
     * 
     * Returns modules sorted by priority (highest priority first)
     * 
     * @return PriorityScore[]
     */
    public function prioritize(): array
    {
        // Generate priority scores for all modules
        $scores = $this->generatePriorityScores();

        // Sort by effective priority (manual override takes precedence)
        usort($scores, function (PriorityScore $a, PriorityScore $b) {
            // Check if either has manual override
            $hasManualA = $a->manualPriority !== null;
            $hasManualB = $b->manualPriority !== null;
            
            // If both have manual overrides, compare them
            if ($hasManualA && $hasManualB) {
                return $a->manualPriority <=> $b->manualPriority;
            }
            
            // If only A has manual override, A comes first
            if ($hasManualA) {
                return -1;
            }
            
            // If only B has manual override, B comes first
            if ($hasManualB) {
                return 1;
            }
            
            // Neither has manual override, use calculated scores
            $priorityA = $a->score;
            $priorityB = $b->score;

            // Lower score = higher priority
            if (abs($priorityA - $priorityB) < 0.001) { // Float comparison with epsilon
                // If scores are equal, prioritize by critical vulnerabilities
                return $b->criticalVulnerabilities <=> $a->criticalVulnerabilities;
            }

            return $priorityA <=> $priorityB;
        });

        return $scores;
    }

    /**
     * Get recommended refactoring order (module names only)
     * 
     * @return string[]
     */
    public function getRecommendedOrder(): array
    {
        $prioritizedScores = $this->prioritize();
        
        return array_map(
            fn(PriorityScore $score) => $score->module,
            $prioritizedScores
        );
    }

    /**
     * Get priority score for a specific module
     * 
     * @param string $module Module name
     * @return PriorityScore|null
     */
    public function getPriorityScore(string $module): ?PriorityScore
    {
        if (empty($this->priorityScores)) {
            $this->generatePriorityScores();
        }

        return $this->priorityScores[$module] ?? null;
    }

    /**
     * Get all priority scores indexed by module name
     * 
     * @return array<string, PriorityScore>
     */
    public function getAllPriorityScores(): array
    {
        if (empty($this->priorityScores)) {
            $this->generatePriorityScores();
        }

        return $this->priorityScores;
    }
}

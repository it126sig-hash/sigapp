<?php

namespace App\Libraries\Refactor\Analysis;

use App\Libraries\Refactor\Contracts\AnalyzerInterface;
use App\Libraries\Refactor\Models\DependencyGraph;
use App\Libraries\Refactor\Models\ImpactAnalysis;
use App\Libraries\Refactor\Models\Module;
use App\Libraries\Refactor\Models\ModuleInventory;
use App\Libraries\Refactor\Models\SecurityReport;

/**
 * Impact Analyzer
 * 
 * Analyzes the impact of refactoring a specific module by examining:
 * - Dependency relationships (which modules depend on this one)
 * - Affected routes and views
 * - Security vulnerabilities
 * - Estimated file changes
 * - Risk assessment
 * 
 * This helps developers make informed decisions about when and how to refactor.
 * 
 * @package App\Libraries\Refactor\Analysis
 */
class ImpactAnalyzer implements AnalyzerInterface
{
    /**
     * Module inventory containing all discovered modules
     */
    private ModuleInventory $inventory;

    /**
     * Dependency graph showing module relationships
     */
    private DependencyGraph $dependencyGraph;

    /**
     * Optional security report for the module
     */
    private ?SecurityReport $securityReport = null;

    /**
     * Create a new ImpactAnalyzer instance
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
     * Set the security report for enhanced analysis
     * 
     * @param SecurityReport $securityReport
     * @return void
     */
    public function setSecurityReport(SecurityReport $securityReport): void
    {
        $this->securityReport = $securityReport;
    }

    /**
     * Analyze the impact of refactoring a module
     * 
     * @param string $moduleName Module name to analyze
     * @return ImpactAnalysis Impact analysis results
     */
    public function analyze(mixed $moduleName): ImpactAnalysis
    {
        if (!is_string($moduleName)) {
            throw new \InvalidArgumentException('Module name must be a string');
        }

        $module = $this->inventory->getModule($moduleName);
        if ($module === null) {
            throw new \InvalidArgumentException("Module '{$moduleName}' not found in inventory");
        }

        $analysis = new ImpactAnalysis($moduleName);

        // Analyze dependencies
        $this->analyzeDependencies($module, $analysis);

        // Analyze routes
        $this->analyzeRoutes($module, $analysis);

        // Analyze views
        $this->analyzeViews($module, $analysis);

        // Estimate file changes
        $this->estimateFileChanges($module, $analysis);

        // Analyze security vulnerabilities
        $this->analyzeSecurityVulnerabilities($analysis);

        // Calculate risk assessment
        $this->calculateRiskAssessment($analysis);

        // Generate warnings
        $this->generateWarnings($analysis);

        return $analysis;
    }

    /**
     * Analyze module dependencies and identify dependents
     * 
     * @param Module $module
     * @param ImpactAnalysis $analysis
     * @return void
     */
    private function analyzeDependencies(Module $module, ImpactAnalysis $analysis): void
    {
        // Get modules that depend on this module
        $dependents = $this->dependencyGraph->getDependents($module->name);
        
        foreach ($dependents as $dependent) {
            $analysis->addDependentModule($dependent);
        }

        // Set impact score from dependency graph
        $analysis->impactScore = $this->dependencyGraph->getImpactScore($module->name);
    }

    /**
     * Analyze affected routes
     * 
     * @param Module $module
     * @param ImpactAnalysis $analysis
     * @return void
     */
    private function analyzeRoutes(Module $module, ImpactAnalysis $analysis): void
    {
        // Add all routes from the module
        foreach ($module->routes as $route) {
            $analysis->addAffectedRoute($route);
        }
    }

    /**
     * Analyze affected views
     * 
     * @param Module $module
     * @param ImpactAnalysis $analysis
     * @return void
     */
    private function analyzeViews(Module $module, ImpactAnalysis $analysis): void
    {
        // Scan for view files related to this module
        $viewsPath = APPPATH . 'Views';
        $moduleLower = strtolower($module->name);
        
        // Check if module-specific view directory exists
        $moduleViewPath = $viewsPath . DIRECTORY_SEPARATOR . $moduleLower;
        
        if (is_dir($moduleViewPath)) {
            $viewFiles = $this->scanViewDirectory($moduleViewPath);
            foreach ($viewFiles as $viewFile) {
                $analysis->addAffectedView($viewFile);
            }
        }

        // Also check for views that might be named after the module
        if (is_dir($viewsPath)) {
            $allViews = $this->scanViewDirectory($viewsPath);
            foreach ($allViews as $viewFile) {
                $basename = basename($viewFile, '.php');
                if (stripos($basename, $moduleLower) !== false) {
                    $analysis->addAffectedView($viewFile);
                }
            }
        }
    }

    /**
     * Recursively scan a directory for view files
     * 
     * @param string $directory
     * @return string[]
     */
    private function scanViewDirectory(string $directory): array
    {
        $views = [];
        
        if (!is_dir($directory)) {
            return $views;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $views[] = $file->getPathname();
            }
        }

        return $views;
    }

    /**
     * Estimate the number of files that will be created or modified
     * 
     * @param Module $module
     * @param ImpactAnalysis $analysis
     * @return void
     */
    private function estimateFileChanges(Module $module, ImpactAnalysis $analysis): void
    {
        // Files that will be created:
        // 1. Repository class (if not exists)
        // 2. Service class (if not exists)
        // 3. Validation class (if validation rules exist)
        // 4. API controller (if web/api split needed)
        
        $filesCreated = 0;
        $filesModified = 0;

        // Repository will be created if it doesn't exist
        if ($module->repositoryPath === null) {
            $filesCreated++;
        }

        // Service will be created if it doesn't exist
        if ($module->servicePath === null) {
            $filesCreated++;
        }

        // Controller will be modified
        $filesModified++;

        // If module has methods, likely needs validation class
        if (count($module->methods) > 0) {
            $filesCreated++;
        }

        // If controller handles both web and API, API controller will be created
        // This is a heuristic - we'd need to analyze the controller code to be sure
        if (count($module->routes) > 0) {
            // Assume if there are routes, there might be API endpoints
            $filesCreated++; // Potential API controller
        }

        // Models might need modification for repository pattern
        $filesModified += count($module->modelPaths);

        $analysis->filesWillBeCreated = $filesCreated;
        $analysis->filesWillBeModified = $filesModified;
    }

    /**
     * Analyze security vulnerabilities if security report is available
     * 
     * @param ImpactAnalysis $analysis
     * @return void
     */
    private function analyzeSecurityVulnerabilities(ImpactAnalysis $analysis): void
    {
        if ($this->securityReport === null) {
            return;
        }

        $analysis->vulnerabilityCount = $this->securityReport->getTotalCount();
        $analysis->criticalVulnerabilityCount = $this->securityReport->getCriticalCount();
    }

    /**
     * Calculate risk assessment based on multiple factors
     * 
     * Risk factors:
     * - Number of dependent modules (high impact = high risk)
     * - Number of affected routes (more routes = higher risk)
     * - Number of files to modify (more files = higher risk)
     * - Critical security vulnerabilities (increases risk)
     * 
     * @param ImpactAnalysis $analysis
     * @return void
     */
    private function calculateRiskAssessment(ImpactAnalysis $analysis): void
    {
        $riskScore = 0;

        // Factor 1: Dependent modules (0-3 points)
        $dependentCount = count($analysis->dependentModules);
        if ($dependentCount === 0) {
            $riskScore += 0; // No dependents = low risk
        } elseif ($dependentCount <= 2) {
            $riskScore += 1; // Few dependents = medium risk
        } elseif ($dependentCount <= 5) {
            $riskScore += 2; // Several dependents = higher risk
        } else {
            $riskScore += 3; // Many dependents = high risk
        }

        // Factor 2: Affected routes (0-2 points)
        $routeCount = count($analysis->affectedRoutes);
        if ($routeCount <= 3) {
            $riskScore += 0;
        } elseif ($routeCount <= 7) {
            $riskScore += 1;
        } else {
            $riskScore += 2;
        }

        // Factor 3: Files to modify (0-2 points)
        $fileCount = $analysis->filesWillBeModified;
        if ($fileCount <= 2) {
            $riskScore += 0;
        } elseif ($fileCount <= 5) {
            $riskScore += 1;
        } else {
            $riskScore += 2;
        }

        // Factor 4: Critical vulnerabilities (0-2 points)
        if ($analysis->criticalVulnerabilityCount > 0) {
            $riskScore += 2; // Critical vulnerabilities increase urgency but also risk
        }

        // Calculate final risk level (0-9 scale)
        if ($riskScore <= 2) {
            $analysis->riskLevel = ImpactAnalysis::RISK_LOW;
        } elseif ($riskScore <= 5) {
            $analysis->riskLevel = ImpactAnalysis::RISK_MEDIUM;
        } else {
            $analysis->riskLevel = ImpactAnalysis::RISK_HIGH;
        }
    }

    /**
     * Generate warning messages based on analysis results
     * 
     * @param ImpactAnalysis $analysis
     * @return void
     */
    private function generateWarnings(ImpactAnalysis $analysis): void
    {
        // Warning: Dependent modules
        if ($analysis->hasDependents()) {
            $count = count($analysis->dependentModules);
            $moduleList = implode(', ', array_slice($analysis->dependentModules, 0, 3));
            if ($count > 3) {
                $moduleList .= " and " . ($count - 3) . " more";
            }
            
            $analysis->addWarning(
                "This module has {$count} dependent module(s): {$moduleList}. " .
                "Refactoring may require changes to these modules."
            );
        }

        // Warning: Many routes affected
        if (count($analysis->affectedRoutes) > 5) {
            $analysis->addWarning(
                "This module has " . count($analysis->affectedRoutes) . " routes. " .
                "Ensure all endpoints maintain backward compatibility."
            );
        }

        // Warning: Many files to modify
        if ($analysis->filesWillBeModified > 5) {
            $analysis->addWarning(
                "Refactoring will modify {$analysis->filesWillBeModified} files. " .
                "Consider breaking this into smaller refactoring steps."
            );
        }

        // Warning: Critical vulnerabilities
        if ($analysis->criticalVulnerabilityCount > 0) {
            $analysis->addWarning(
                "This module has {$analysis->criticalVulnerabilityCount} critical security " .
                "vulnerability/vulnerabilities. Prioritize fixing these during refactoring."
            );
        }

        // Warning: High risk
        if ($analysis->isHighRisk()) {
            $analysis->addWarning(
                "This refactoring is HIGH RISK. Consider thorough testing and " .
                "having a rollback plan ready."
            );
        }

        // Warning: No dependents (leaf module)
        if (!$analysis->hasDependents() && $analysis->impactScore === 0) {
            $analysis->addWarning(
                "This is a leaf module with no dependents. It's a safe starting point for refactoring."
            );
        }
    }

    /**
     * Analyze multiple modules and return their impact analyses
     * 
     * @param string[] $moduleNames
     * @return ImpactAnalysis[]
     */
    public function analyzeMultiple(array $moduleNames): array
    {
        $analyses = [];

        foreach ($moduleNames as $moduleName) {
            try {
                $analyses[$moduleName] = $this->analyze($moduleName);
            } catch (\InvalidArgumentException $e) {
                // Skip modules that don't exist
                continue;
            }
        }

        return $analyses;
    }

    /**
     * Get all modules sorted by impact score (ascending)
     * Lower impact = safer to refactor first
     * 
     * @return string[]
     */
    public function getModulesByImpact(): array
    {
        $modules = [];

        foreach ($this->inventory->modules as $module) {
            $impactScore = $this->dependencyGraph->getImpactScore($module->name);
            $modules[$module->name] = $impactScore;
        }

        asort($modules);
        return array_keys($modules);
    }
}

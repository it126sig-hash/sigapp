<?php

namespace App\Libraries\Refactor\Analysis;

use App\Libraries\Refactor\Exceptions\AnalysisException;
use App\Libraries\Refactor\Models\AuditReport;
use App\Libraries\Refactor\Models\ControllerAnalysis;
use App\Libraries\Refactor\Models\ImpactAnalysis;
use App\Libraries\Refactor\Models\Module;
use App\Libraries\Refactor\Models\ModuleInventory;
use App\Libraries\Refactor\Models\SecurityReport;
use App\Libraries\Refactor\Security\SecurityScanner;

/**
 * Audit Generator
 * 
 * Orchestrates the generation of comprehensive audit reports for modules.
 * Combines structural analysis, business logic identification, database query
 * identification, security scanning, and impact analysis to provide a complete
 * picture of what needs to be refactored.
 * 
 * This class ensures NO code modification during the audit phase - it only
 * analyzes and reports findings.
 * 
 * @package App\Libraries\Refactor\Analysis
 */
class AuditGenerator
{
    /**
     * Module inventory containing all discovered modules
     */
    private ModuleInventory $inventory;

    /**
     * Security scanner for vulnerability detection
     */
    private SecurityScanner $scanner;

    /**
     * Impact analyzer for dependency and impact analysis
     */
    private ImpactAnalyzer $impactAnalyzer;

    /**
     * Code analyzer for structural and pattern analysis
     */
    private CodeAnalyzer $codeAnalyzer;

    /**
     * Constructor
     * 
     * @param ModuleInventory $inventory Module inventory
     * @param SecurityScanner $scanner Security scanner
     * @param ImpactAnalyzer $impactAnalyzer Impact analyzer
     * @param CodeAnalyzer|null $codeAnalyzer Optional code analyzer (will create if not provided)
     */
    public function __construct(
        ModuleInventory $inventory,
        SecurityScanner $scanner,
        ImpactAnalyzer $impactAnalyzer,
        ?CodeAnalyzer $codeAnalyzer = null
    ) {
        $this->inventory = $inventory;
        $this->scanner = $scanner;
        $this->impactAnalyzer = $impactAnalyzer;
        $this->codeAnalyzer = $codeAnalyzer ?? new CodeAnalyzer();
    }

    /**
     * Generate comprehensive audit report for a module
     * 
     * Orchestrates all analysis steps:
     * 1. Analyze controller structure
     * 2. Identify business logic patterns
     * 3. Identify database queries
     * 4. Scan for security vulnerabilities
     * 5. Analyze refactoring impact
     * 6. Estimate complexity
     * 7. Generate recommendations
     * 
     * @param string $moduleName Module name to audit
     * @return AuditReport Comprehensive audit report
     * @throws AnalysisException If module not found or analysis fails
     */
    public function generateAudit(string $moduleName): AuditReport
    {
        // Get module from inventory
        $module = $this->inventory->getModule($moduleName);
        if ($module === null) {
            throw new AnalysisException("Module '{$moduleName}' not found in inventory");
        }

        // Validate controller file exists
        if (empty($module->controllerPath) || !file_exists($module->controllerPath)) {
            throw new AnalysisException("Controller file not found for module '{$moduleName}'");
        }

        // Step 1: Analyze controller structure
        $structureArray = $this->codeAnalyzer->analyzeControllerStructure($module->controllerPath);
        
        // Convert array to ControllerAnalysis object
        $controllerAnalysis = new ControllerAnalysis();
        $controllerAnalysis->methodCount = $structureArray['methodCount'];
        $controllerAnalysis->linesOfCode = $structureArray['linesOfCode'];
        $controllerAnalysis->dependencies = $structureArray['dependencies'];
        
        // Add methods to analysis
        foreach ($structureArray['methods'] as $method) {
            $controllerAnalysis->addMethod(
                $method['name'],
                $method['lineCount']
            );
        }

        // Step 2: Identify business logic patterns
        $businessLogic = $this->identifyBusinessLogic($module->controllerPath);
        if (!empty($businessLogic)) {
            $controllerAnalysis->hasBusinessLogic = true;
        }

        // Step 3: Identify database queries
        $queries = $this->identifyDatabaseQueries($module->controllerPath);
        if (!empty($queries)) {
            $controllerAnalysis->hasDirectQueries = true;
        }

        // Step 4: Scan for security vulnerabilities
        $securityReport = $this->scanner->scanModule($module);

        // Step 5: Analyze refactoring impact
        $this->impactAnalyzer->setSecurityReport($securityReport);
        $impactAnalysis = $this->impactAnalyzer->analyze($moduleName);

        // Step 6: Create audit report (complexity is calculated by ControllerAnalysis)
        $auditReport = new AuditReport(
            $moduleName,
            $controllerAnalysis,
            $securityReport,
            $impactAnalysis
        );

        // Set controller path and model paths
        $auditReport->controllerPath = $module->controllerPath;
        $auditReport->modelPaths = $module->modelPaths;

        // Add business logic findings
        foreach ($businessLogic as $finding) {
            $auditReport->addBusinessLogic(
                $finding['method'],
                $finding['description'],
                (string)$finding['line']
            );
        }

        // Add database query findings
        foreach ($queries as $finding) {
            $auditReport->addQueryToMove(
                $finding['method'],
                $finding['snippet'],
                $finding['type'],
                (string)$finding['line']
            );
        }

        // Step 7: Generate recommendations
        $this->generateRecommendations($auditReport, $module);

        return $auditReport;
    }

    /**
     * Analyze controller structure
     * 
     * Delegates to CodeAnalyzer to get structural information about the controller
     * including method count, lines of code, dependencies, and method details.
     * 
     * @param string $filePath Path to controller file
     * @return array{
     *     methodCount: int,
     *     linesOfCode: int,
     *     dependencies: array<string>,
     *     methods: array<array{name: string, lineCount: int, startLine: int, endLine: int}>
     * }
     * @throws AnalysisException If analysis fails
     */
    public function analyzeControllerStructure(string $filePath): array
    {
        return $this->codeAnalyzer->analyzeControllerStructure($filePath);
    }

    /**
     * Identify business logic in controller
     * 
     * Delegates to CodeAnalyzer to identify business logic patterns that should
     * be extracted to service classes. Looks for calculations, complex conditionals,
     * data transformations, validation logic, and data processing loops.
     * 
     * @param string $filePath Path to controller file
     * @return array<array{
     *     type: string,
     *     description: string,
     *     line: int,
     *     method: string,
     *     snippet: string,
     *     severity: string
     * }>
     * @throws AnalysisException If analysis fails
     */
    public function identifyBusinessLogic(string $filePath): array
    {
        return $this->codeAnalyzer->identifyBusinessLogic($filePath);
    }

    /**
     * Identify database queries in controller
     * 
     * Delegates to CodeAnalyzer to identify database queries that should be
     * moved to repository classes. Looks for Query Builder usage, raw queries,
     * model method calls, and direct database connections.
     * 
     * @param string $filePath Path to controller file
     * @return array<array{
     *     type: string,
     *     description: string,
     *     line: int,
     *     method: string,
     *     snippet: string,
     *     severity: string
     * }>
     * @throws AnalysisException If analysis fails
     */
    public function identifyDatabaseQueries(string $filePath): array
    {
        return $this->codeAnalyzer->identifyDatabaseQueries($filePath);
    }

    /**
     * Generate comprehensive recommendations based on audit findings
     * 
     * Analyzes all findings and generates actionable recommendations for
     * refactoring the module. Recommendations are prioritized based on
     * severity and impact.
     * 
     * @param AuditReport $report Audit report to add recommendations to
     * @param Module $module Module being audited
     * @return void
     */
    private function generateRecommendations(AuditReport $report, Module $module): void
    {
        // Recommendation 1: Critical security vulnerabilities
        if ($report->securityReport->getCriticalCount() > 0) {
            $report->addRecommendation(
                "🔴 CRITICAL: Fix {$report->securityReport->getCriticalCount()} critical security " .
                "vulnerability/vulnerabilities immediately before refactoring."
            );
        }

        // Recommendation 2: High severity security issues
        if ($report->securityReport->getHighCount() > 0) {
            $report->addRecommendation(
                "🟠 HIGH PRIORITY: Address {$report->securityReport->getHighCount()} high severity " .
                "security issue(s) during refactoring."
            );
        }

        // Recommendation 3: Repository layer creation
        if (count($report->queriesToMove) > 0) {
            $report->addRecommendation(
                "Create a Repository class to handle all database operations. " .
                "Found " . count($report->queriesToMove) . " database query pattern(s) to move."
            );
        }

        // Recommendation 4: Service layer creation
        if (count($report->businessLogicToExtract) > 0) {
            $report->addRecommendation(
                "Create a Service class to handle business logic. " .
                "Found " . count($report->businessLogicToExtract) . " business logic pattern(s) to extract."
            );
        }

        // Recommendation 5: Controller refactoring
        if ($report->controllerAnalysis->methodCount > 5 || $report->controllerAnalysis->linesOfCode > 200) {
            $report->addRecommendation(
                "Refactor controller to be thin - delegate all business logic to services " .
                "and all database operations to repositories."
            );
        }

        // Recommendation 6: Web/API separation
        if ($report->controllerAnalysis->hasMixedResponses) {
            $report->addRecommendation(
                "Split controller into separate Web and API controllers for better separation of concerns."
            );
        }

        // Recommendation 7: Dependency management
        if (count($report->controllerAnalysis->dependencies) > 5) {
            $report->addRecommendation(
                "Controller has " . count($report->controllerAnalysis->dependencies) . " dependencies. " .
                "Consider using dependency injection and reducing coupling."
            );
        }

        // Recommendation 8: Impact-based recommendations
        if ($report->impactAnalysis->isHighRisk()) {
            $report->addRecommendation(
                "⚠️ This is a HIGH RISK refactoring. Create comprehensive tests before starting, " .
                "and consider refactoring dependent modules first."
            );
        } elseif ($report->impactAnalysis->isLowRisk() && !$report->impactAnalysis->hasDependents()) {
            $report->addRecommendation(
                "✅ This is a SAFE starting point - it's a leaf module with no dependents. " .
                "Good candidate for learning the refactoring process."
            );
        }

        // Recommendation 9: Complexity-based recommendations
        if ($report->complexity === 'Complex') {
            $report->addRecommendation(
                "This is a COMPLEX refactoring. Consider breaking it into smaller steps: " .
                "1) Create repository, 2) Create service, 3) Refactor controller, 4) Fix security issues."
            );
        } elseif ($report->complexity === 'Simple') {
            $report->addRecommendation(
                "This is a SIMPLE refactoring. Can be completed in a single session with low risk."
            );
        }

        // Recommendation 10: Testing recommendation
        $report->addRecommendation(
            "Write unit tests for the new Service and Repository classes before refactoring the controller."
        );

        // Recommendation 11: Validation extraction
        if ($report->controllerAnalysis->hasValidation) {
            $report->addRecommendation(
                "Extract validation rules into a dedicated Validation class for reusability."
            );
        }

        // Recommendation 12: View updates
        if (count($report->impactAnalysis->affectedViews) > 0) {
            $report->addRecommendation(
                "Review and update " . count($report->impactAnalysis->affectedViews) . " view file(s) " .
                "to ensure they work with the refactored controller."
            );
        }

        // Recommendation 13: Documentation
        $report->addRecommendation(
            "Document the refactored architecture in the module's README or inline comments."
        );

        // Recommendation 14: Backup reminder
        $report->addRecommendation(
            "Create a backup or git commit before starting the refactoring process."
        );
    }

    /**
     * Generate audit reports for multiple modules
     * 
     * @param string[] $moduleNames Array of module names to audit
     * @return array<string, AuditReport> Array of audit reports indexed by module name
     */
    public function generateMultipleAudits(array $moduleNames): array
    {
        $reports = [];

        foreach ($moduleNames as $moduleName) {
            try {
                $reports[$moduleName] = $this->generateAudit($moduleName);
            } catch (AnalysisException $e) {
                // Skip modules that fail analysis
                // In production, you might want to log this
                continue;
            }
        }

        return $reports;
    }

    /**
     * Generate audit report and save to file
     * 
     * @param string $moduleName Module name to audit
     * @param string $outputPath Path to save the audit report (markdown format)
     * @return AuditReport Generated audit report
     * @throws AnalysisException If audit generation or file writing fails
     */
    public function generateAndSaveAudit(string $moduleName, string $outputPath): AuditReport
    {
        $report = $this->generateAudit($moduleName);

        // Ensure output directory exists
        $outputDir = dirname($outputPath);
        if (!is_dir($outputDir)) {
            if (!mkdir($outputDir, 0755, true)) {
                throw new AnalysisException("Failed to create output directory: {$outputDir}");
            }
        }

        // Write markdown report to file
        $markdown = $report->toMarkdown();
        $result = file_put_contents($outputPath, $markdown);

        if ($result === false) {
            throw new AnalysisException("Failed to write audit report to: {$outputPath}");
        }

        return $report;
    }

    /**
     * Get the module inventory
     * 
     * @return ModuleInventory
     */
    public function getInventory(): ModuleInventory
    {
        return $this->inventory;
    }

    /**
     * Get the security scanner
     * 
     * @return SecurityScanner
     */
    public function getScanner(): SecurityScanner
    {
        return $this->scanner;
    }

    /**
     * Get the impact analyzer
     * 
     * @return ImpactAnalyzer
     */
    public function getImpactAnalyzer(): ImpactAnalyzer
    {
        return $this->impactAnalyzer;
    }

    /**
     * Get the code analyzer
     * 
     * @return CodeAnalyzer
     */
    public function getCodeAnalyzer(): CodeAnalyzer
    {
        return $this->codeAnalyzer;
    }
}

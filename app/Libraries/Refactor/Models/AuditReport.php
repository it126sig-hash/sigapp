<?php

namespace App\Libraries\Refactor\Models;

use DateTime;

/**
 * Audit Report Data Model
 * 
 * Contains comprehensive analysis results for a module before refactoring.
 * This report is generated during the audit phase and does NOT modify any code.
 * 
 * @package App\Libraries\Refactor\Models
 */
class AuditReport
{
    /**
     * Complexity level constants
     */
    public const COMPLEXITY_SIMPLE = 'Simple';
    public const COMPLEXITY_MEDIUM = 'Medium';
    public const COMPLEXITY_COMPLEX = 'Complex';

    /**
     * Name of the module being audited
     */
    public string $moduleName;

    /**
     * Controller structure analysis
     */
    public ControllerAnalysis $controllerAnalysis;

    /**
     * Array of business logic items that should be extracted to Service layer
     * Format: ['method' => string, 'description' => string, 'lines' => string]
     * 
     * @var array<int, array<string, string>>
     */
    public array $businessLogicToExtract = [];

    /**
     * Array of database queries that should be moved to Repository layer
     * Format: ['method' => string, 'query' => string, 'type' => string, 'lines' => string]
     * 
     * @var array<int, array<string, string>>
     */
    public array $queriesToMove = [];

    /**
     * Security report with vulnerability findings
     */
    public SecurityReport $securityReport;

    /**
     * Impact analysis for this module
     */
    public ImpactAnalysis $impactAnalysis;

    /**
     * Estimated refactoring complexity (Simple, Medium, Complex)
     */
    public string $complexity;

    /**
     * Array of recommendations for refactoring
     * 
     * @var string[]
     */
    public array $recommendations = [];

    /**
     * Timestamp when the audit was generated
     */
    public DateTime $generatedAt;

    /**
     * Path to the controller file
     */
    public string $controllerPath;

    /**
     * Array of model paths used by the controller
     * 
     * @var string[]
     */
    public array $modelPaths = [];

    /**
     * Whether the controller should be split into Web and API controllers
     */
    public bool $shouldSplitWebApi = false;

    /**
     * Create a new AuditReport instance
     * 
     * @param string $moduleName Module name
     * @param ControllerAnalysis $controllerAnalysis Controller analysis
     * @param SecurityReport $securityReport Security report
     * @param ImpactAnalysis $impactAnalysis Impact analysis
     */
    public function __construct(
        string $moduleName,
        ControllerAnalysis $controllerAnalysis,
        SecurityReport $securityReport,
        ImpactAnalysis $impactAnalysis
    ) {
        $this->moduleName = $moduleName;
        $this->controllerAnalysis = $controllerAnalysis;
        $this->securityReport = $securityReport;
        $this->impactAnalysis = $impactAnalysis;
        $this->generatedAt = new DateTime();
        $this->complexity = $controllerAnalysis->getRefactoringComplexity();
        $this->shouldSplitWebApi = $controllerAnalysis->hasMixedResponses;
    }

    /**
     * Add business logic item to extract
     * 
     * @param string $method Method name
     * @param string $description Description of the business logic
     * @param string $lines Line range (e.g., "45-67")
     * @return void
     */
    public function addBusinessLogic(string $method, string $description, string $lines): void
    {
        $this->businessLogicToExtract[] = [
            'method' => $method,
            'description' => $description,
            'lines' => $lines,
        ];
    }

    /**
     * Add database query to move
     * 
     * @param string $method Method name
     * @param string $query Query description or snippet
     * @param string $type Query type (SELECT, INSERT, UPDATE, DELETE, RAW)
     * @param string $lines Line range (e.g., "45-67")
     * @return void
     */
    public function addQueryToMove(string $method, string $query, string $type, string $lines): void
    {
        $this->queriesToMove[] = [
            'method' => $method,
            'query' => $query,
            'type' => $type,
            'lines' => $lines,
        ];
    }

    /**
     * Add a recommendation
     * 
     * @param string $recommendation Recommendation text
     * @return void
     */
    public function addRecommendation(string $recommendation): void
    {
        if (!in_array($recommendation, $this->recommendations, true)) {
            $this->recommendations[] = $recommendation;
        }
    }

    /**
     * Check if the module has security issues
     * 
     * @return bool
     */
    public function hasSecurityIssues(): bool
    {
        return $this->securityReport->getTotalCount() > 0;
    }

    /**
     * Check if the module has critical security issues
     * 
     * @return bool
     */
    public function hasCriticalSecurityIssues(): bool
    {
        return $this->securityReport->hasCriticalVulnerabilities();
    }

    /**
     * Get summary statistics
     * 
     * @return array<string, mixed>
     */
    public function getSummary(): array
    {
        return [
            'moduleName' => $this->moduleName,
            'complexity' => $this->complexity,
            'methodCount' => $this->controllerAnalysis->methodCount,
            'linesOfCode' => $this->controllerAnalysis->linesOfCode,
            'businessLogicItems' => count($this->businessLogicToExtract),
            'queriesToMove' => count($this->queriesToMove),
            'totalVulnerabilities' => $this->securityReport->getTotalCount(),
            'criticalVulnerabilities' => $this->securityReport->getCriticalCount(),
            'dependentModules' => count($this->impactAnalysis->dependentModules),
            'riskLevel' => $this->impactAnalysis->riskLevel,
            'shouldSplitWebApi' => $this->shouldSplitWebApi,
        ];
    }

    /**
     * Convert report to markdown format for human-readable output
     * 
     * @return string
     */
    public function toMarkdown(): string
    {
        $md = "# Audit Report: {$this->moduleName}\n\n";
        $md .= "**Generated:** {$this->generatedAt->format('Y-m-d H:i:s')}\n\n";
        $md .= "**Refactoring Complexity:** {$this->complexity}\n\n";
        $md .= "---\n\n";

        // Controller Analysis Section
        $md .= "## Controller Analysis\n\n";
        $md .= "**File:** `{$this->controllerPath}`\n\n";
        $md .= "### Metrics\n\n";
        $md .= "- **Methods:** {$this->controllerAnalysis->methodCount}\n";
        $md .= "- **Lines of Code:** {$this->controllerAnalysis->linesOfCode}\n";
        $md .= "- **Average Lines per Method:** {$this->controllerAnalysis->getAverageLinesPerMethod()}\n";
        $md .= "- **Routes:** {$this->controllerAnalysis->routeCount}\n";
        $md .= "- **Dependencies:** " . count($this->controllerAnalysis->dependencies) . "\n\n";

        $md .= "### Characteristics\n\n";
        $md .= "- **Has Validation:** " . ($this->controllerAnalysis->hasValidation ? 'Yes' : 'No') . "\n";
        $md .= "- **Has Business Logic:** " . ($this->controllerAnalysis->hasBusinessLogic ? 'Yes' : 'No') . "\n";
        $md .= "- **Has Direct Queries:** " . ($this->controllerAnalysis->hasDirectQueries ? 'Yes' : 'No') . "\n";
        $md .= "- **Mixed Web/API Responses:** " . ($this->controllerAnalysis->hasMixedResponses ? 'Yes' : 'No') . "\n";

        if ($this->controllerAnalysis->hasMixedResponses) {
            $md .= "  - Web Methods: {$this->controllerAnalysis->webMethodCount}\n";
            $md .= "  - API Methods: {$this->controllerAnalysis->apiMethodCount}\n";
        }

        $md .= "\n";

        // Dependencies
        if (!empty($this->controllerAnalysis->dependencies)) {
            $md .= "### Dependencies\n\n";
            foreach ($this->controllerAnalysis->dependencies as $dep) {
                $md .= "- `{$dep}`\n";
            }
            $md .= "\n";
        }

        // Methods
        if (!empty($this->controllerAnalysis->methods)) {
            $md .= "### Methods\n\n";
            $md .= "| Method | Lines | Complexity | Has Business Logic |\n";
            $md .= "|--------|-------|------------|--------------------|\n";
            foreach ($this->controllerAnalysis->methods as $name => $details) {
                $hasLogic = $details['hasBusinessLogic'] ? 'Yes' : 'No';
                $md .= "| `{$name}` | {$details['lines']} | {$details['complexity']} | {$hasLogic} |\n";
            }
            $md .= "\n";
        }

        // Business Logic to Extract
        if (!empty($this->businessLogicToExtract)) {
            $md .= "## Business Logic to Extract\n\n";
            $md .= "The following business logic should be moved to a Service layer:\n\n";
            foreach ($this->businessLogicToExtract as $idx => $item) {
                $md .= ($idx + 1) . ". **Method:** `{$item['method']}` (Lines {$item['lines']})\n";
                $md .= "   - **Description:** {$item['description']}\n\n";
            }
        }

        // Queries to Move
        if (!empty($this->queriesToMove)) {
            $md .= "## Database Queries to Move\n\n";
            $md .= "The following queries should be moved to a Repository layer:\n\n";
            foreach ($this->queriesToMove as $idx => $item) {
                $md .= ($idx + 1) . ". **Method:** `{$item['method']}` (Lines {$item['lines']})\n";
                $md .= "   - **Type:** {$item['type']}\n";
                $md .= "   - **Query:** `{$item['query']}`\n\n";
            }
        }

        // Security Report
        $md .= "## Security Analysis\n\n";
        $totalVulns = $this->securityReport->getTotalCount();
        if ($totalVulns > 0) {
            $md .= "**Total Vulnerabilities:** {$totalVulns}\n\n";
            $md .= "### Severity Breakdown\n\n";
            $md .= "- **Critical:** {$this->securityReport->getCriticalCount()}\n";
            $md .= "- **High:** {$this->securityReport->getHighCount()}\n";
            $md .= "- **Medium:** {$this->securityReport->getMediumCount()}\n";
            $md .= "- **Low:** {$this->securityReport->getLowCount()}\n\n";

            $md .= "### Vulnerabilities\n\n";
            foreach ($this->securityReport->vulnerabilities as $idx => $vuln) {
                $md .= ($idx + 1) . ". **{$vuln->type}** ({$vuln->severity})\n";
                $md .= "   - **File:** `{$vuln->filePath}` (Line {$vuln->lineNumber})\n";
                $md .= "   - **Description:** {$vuln->description}\n";
                $md .= "   - **Recommendation:** {$vuln->recommendation}\n";
                if ($vuln->codeSnippet) {
                    $md .= "   - **Code:**\n     ```php\n     {$vuln->codeSnippet}\n     ```\n";
                }
                $md .= "\n";
            }
        } else {
            $md .= "✅ **No security vulnerabilities detected.**\n\n";
        }

        // Impact Analysis
        $md .= "## Impact Analysis\n\n";
        $md .= "**Risk Level:** {$this->impactAnalysis->riskLevel}\n\n";
        $md .= "**Impact Score:** {$this->impactAnalysis->impactScore}\n\n";

        if (!empty($this->impactAnalysis->dependentModules)) {
            $md .= "### Dependent Modules\n\n";
            $md .= "The following modules depend on this module:\n\n";
            foreach ($this->impactAnalysis->dependentModules as $dep) {
                $md .= "- `{$dep}`\n";
            }
            $md .= "\n";
        } else {
            $md .= "✅ **No dependent modules** (safe to refactor)\n\n";
        }

        if (!empty($this->impactAnalysis->affectedRoutes)) {
            $md .= "### Affected Routes\n\n";
            foreach ($this->impactAnalysis->affectedRoutes as $route) {
                $md .= "- `{$route}`\n";
            }
            $md .= "\n";
        }

        if (!empty($this->impactAnalysis->warnings)) {
            $md .= "### Warnings\n\n";
            foreach ($this->impactAnalysis->warnings as $warning) {
                $md .= "⚠️ {$warning}\n\n";
            }
        }

        $md .= "### Estimated Changes\n\n";
        $md .= "- **Files to Create:** {$this->impactAnalysis->filesWillBeCreated}\n";
        $md .= "- **Files to Modify:** {$this->impactAnalysis->filesWillBeModified}\n\n";

        // Recommendations
        if (!empty($this->recommendations)) {
            $md .= "## Recommendations\n\n";
            foreach ($this->recommendations as $idx => $rec) {
                $md .= ($idx + 1) . ". {$rec}\n";
            }
            $md .= "\n";
        }

        // Summary
        $md .= "---\n\n";
        $md .= "## Summary\n\n";
        $summary = $this->getSummary();
        $md .= "- **Complexity:** {$summary['complexity']}\n";
        $md .= "- **Business Logic Items:** {$summary['businessLogicItems']}\n";
        $md .= "- **Queries to Move:** {$summary['queriesToMove']}\n";
        $md .= "- **Security Issues:** {$summary['totalVulnerabilities']} ({$summary['criticalVulnerabilities']} critical)\n";
        $md .= "- **Risk Level:** {$summary['riskLevel']}\n";
        $md .= "- **Split Web/API:** " . ($summary['shouldSplitWebApi'] ? 'Yes' : 'No') . "\n\n";

        $md .= "---\n\n";
        $md .= "**⚠️ IMPORTANT:** This is an audit report only. No code has been modified.\n";
        $md .= "Review this report and approve before executing the refactoring.\n";

        return $md;
    }

    /**
     * Convert report to array representation
     * 
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'moduleName' => $this->moduleName,
            'controllerPath' => $this->controllerPath,
            'modelPaths' => $this->modelPaths,
            'controllerAnalysis' => $this->controllerAnalysis->toArray(),
            'businessLogicToExtract' => $this->businessLogicToExtract,
            'queriesToMove' => $this->queriesToMove,
            'securityReport' => [
                'moduleName' => $this->securityReport->moduleName,
                'scannedAt' => $this->securityReport->scannedAt->format('c'),
                'vulnerabilities' => array_map(fn($v) => $v->toArray(), $this->securityReport->vulnerabilities),
            ],
            'impactAnalysis' => $this->impactAnalysis->toArray(),
            'complexity' => $this->complexity,
            'recommendations' => $this->recommendations,
            'generatedAt' => $this->generatedAt->format('c'),
            'shouldSplitWebApi' => $this->shouldSplitWebApi,
            'summary' => $this->getSummary(),
        ];
    }

    /**
     * Convert report to JSON string
     * 
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Create AuditReport instance from array data
     * 
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $controllerAnalysis = ControllerAnalysis::fromArray($data['controllerAnalysis']);
        
        // Reconstruct SecurityReport
        $securityReport = new SecurityReport($data['securityReport']['moduleName']);
        $securityReport->scannedAt = new DateTime($data['securityReport']['scannedAt']);
        foreach ($data['securityReport']['vulnerabilities'] ?? [] as $vulnData) {
            $securityReport->addVulnerability(Vulnerability::fromArray($vulnData));
        }
        
        $impactAnalysis = ImpactAnalysis::fromArray($data['impactAnalysis']);
        
        $report = new self(
            $data['moduleName'],
            $controllerAnalysis,
            $securityReport,
            $impactAnalysis
        );
        
        $report->controllerPath = $data['controllerPath'] ?? '';
        $report->modelPaths = $data['modelPaths'] ?? [];
        $report->businessLogicToExtract = $data['businessLogicToExtract'] ?? [];
        $report->queriesToMove = $data['queriesToMove'] ?? [];
        $report->complexity = $data['complexity'] ?? self::COMPLEXITY_SIMPLE;
        $report->recommendations = $data['recommendations'] ?? [];
        $report->generatedAt = new DateTime($data['generatedAt']);
        $report->shouldSplitWebApi = $data['shouldSplitWebApi'] ?? false;
        
        return $report;
    }

    /**
     * Create AuditReport instance from JSON string
     * 
     * @param string $json JSON string
     * @return self
     * @throws \JsonException
     */
    public static function fromJson(string $json): self
    {
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        return self::fromArray($data);
    }
}

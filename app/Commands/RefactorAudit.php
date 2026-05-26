<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Libraries\Refactor\Analysis\AuditGenerator;
use App\Libraries\Refactor\Analysis\CodeAnalyzer;
use App\Libraries\Refactor\Analysis\ImpactAnalyzer;
use App\Libraries\Refactor\Security\SecurityScanner;
use App\Libraries\Refactor\Models\ModuleInventory;
use App\Libraries\Refactor\Models\DependencyGraph;

/**
 * CLI Command: Audit Generation
 *
 * Runs AuditGenerator for a specified module to produce a detailed
 * audit report without modifying any code.
 *
 * Usage: php spark refactor:audit <module>
 */
class RefactorAudit extends BaseCommand
{
    /**
     * Command group
     */
    protected $group = 'Refactor';

    /**
     * Command name
     */
    protected $name = 'refactor:audit';

    /**
     * Command description
     */
    protected $description = 'Generate an audit report for a module (no code modification)';

    /**
     * Command usage
     */
    protected $usage = 'refactor:audit <module> [options]';

    /**
     * Command arguments
     */
    protected $arguments = [
        'module' => 'Module name to audit (required)',
    ];

    /**
     * Command options
     */
    protected $options = [
        '--inventory' => 'Path to module inventory JSON',
        '--graph'     => 'Path to dependency graph JSON',
    ];

    /**
     * Execute the command
     */
    public function run(array $params)
    {
        CLI::write('=== Audit Generator ===', 'cyan');
        CLI::newLine();

        try {
            $moduleName = array_shift($params);

            if (empty($moduleName)) {
                CLI::error('Module name is required. Usage: php spark refactor:audit <module>');
                return;
            }

            $refactorDir = WRITEPATH . 'refactor' . DIRECTORY_SEPARATOR;

            // Load module inventory
            $inventoryPath = $params['inventory'] ?? $refactorDir . 'module_inventory.json';
            if (!file_exists($inventoryPath)) {
                CLI::error('Module inventory not found. Run "php spark refactor:discover" first.');
                return;
            }

            $inventory = ModuleInventory::fromJson(file_get_contents($inventoryPath));

            // Verify module exists
            $module = $inventory->getModule($moduleName);
            if (!$module) {
                CLI::error("Module '{$moduleName}' not found in inventory.");
                CLI::write('Available modules:', 'yellow');
                foreach ($inventory->getModuleNames() as $name) {
                    CLI::write("  • {$name}", 'white');
                }
                return;
            }

            // Load dependency graph (required for ImpactAnalyzer)
            $graphPath = $params['graph'] ?? $refactorDir . 'dependency_graph.json';
            if (!file_exists($graphPath)) {
                CLI::error('Dependency graph not found. Run "php spark refactor:analyze" first.');
                return;
            }

            $graph = DependencyGraph::fromJson(file_get_contents($graphPath));

            CLI::write("Generating audit for module: {$moduleName}", 'white');
            CLI::write('(No code will be modified)', 'yellow');
            CLI::newLine();

            // Create dependencies for AuditGenerator
            $scanner = new SecurityScanner();
            $codeAnalyzer = new CodeAnalyzer();
            $impactAnalyzer = new ImpactAnalyzer($inventory, $graph);

            $auditGenerator = new AuditGenerator(
                $inventory,
                $scanner,
                $impactAnalyzer,
                $codeAnalyzer
            );

            $report = $auditGenerator->generateAudit($moduleName);

            // Display audit summary
            CLI::write('Audit Summary:', 'yellow');
            CLI::write("  Module: {$report->moduleName}", 'white');
            CLI::write("  Complexity: {$report->complexity}", 'white');
            CLI::newLine();

            if ($report->controllerAnalysis) {
                CLI::write('Controller Analysis:', 'yellow');
                CLI::write("  Methods: {$report->controllerAnalysis->methodCount}", 'white');
                CLI::write("  Lines of Code: {$report->controllerAnalysis->linesOfCode}", 'white');
                CLI::write("  Has Business Logic: " . ($report->controllerAnalysis->hasBusinessLogic ? 'Yes' : 'No'), 'white');
                CLI::write("  Has Direct Queries: " . ($report->controllerAnalysis->hasDirectQueries ? 'Yes' : 'No'), 'white');
                CLI::write("  Has Validation: " . ($report->controllerAnalysis->hasValidation ? 'Yes' : 'No'), 'white');
                CLI::newLine();
            }

            if (!empty($report->businessLogicToExtract)) {
                CLI::write('Business Logic to Extract:', 'yellow');
                foreach ($report->businessLogicToExtract as $logic) {
                    $desc = is_array($logic) ? ($logic['description'] ?? $logic['method'] ?? 'unknown') : $logic;
                    CLI::write("  • {$desc}", 'white');
                }
                CLI::newLine();
            }

            if (!empty($report->queriesToMove)) {
                CLI::write('Queries to Move to Repository:', 'yellow');
                foreach ($report->queriesToMove as $query) {
                    $desc = is_array($query) ? ($query['description'] ?? $query['query'] ?? 'unknown') : $query;
                    CLI::write("  • {$desc}", 'white');
                }
                CLI::newLine();
            }

            if ($report->securityReport && $report->securityReport->getTotalCount() > 0) {
                CLI::write('Security Issues:', 'red');
                CLI::write("  Critical: {$report->securityReport->getCriticalCount()}", 'white');
                CLI::write("  High: {$report->securityReport->getHighCount()}", 'white');
                CLI::write("  Medium: {$report->securityReport->getMediumCount()}", 'white');
                CLI::write("  Low: {$report->securityReport->getLowCount()}", 'white');
                CLI::newLine();
            }

            if (!empty($report->recommendations)) {
                CLI::write('Recommendations:', 'yellow');
                foreach ($report->recommendations as $rec) {
                    CLI::write("  • {$rec}", 'white');
                }
                CLI::newLine();
            }

            // Save audit report as markdown
            $outputDir = WRITEPATH . 'refactor' . DIRECTORY_SEPARATOR . 'audits';
            if (!is_dir($outputDir)) {
                mkdir($outputDir, 0755, true);
            }

            $outputFile = $outputDir . DIRECTORY_SEPARATOR . $moduleName . '_audit.md';
            file_put_contents($outputFile, $report->toMarkdown());

            CLI::write("Audit report saved to: {$outputFile}", 'green');

        } catch (\Exception $e) {
            CLI::error('Audit generation failed: ' . $e->getMessage());
            return;
        }
    }
}

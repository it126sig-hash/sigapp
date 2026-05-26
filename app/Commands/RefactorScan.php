<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Libraries\Refactor\Security\SecurityScanner;
use App\Libraries\Refactor\Models\ModuleInventory;
use App\Libraries\Refactor\Models\SecurityReport;

/**
 * CLI Command: Security Scanning
 *
 * Runs SecurityScanner on specified module or all modules to detect
 * security vulnerabilities.
 *
 * Usage: php spark refactor:scan [module]
 */
class RefactorScan extends BaseCommand
{
    /**
     * Command group
     */
    protected $group = 'Refactor';

    /**
     * Command name
     */
    protected $name = 'refactor:scan';

    /**
     * Command description
     */
    protected $description = 'Scan modules for security vulnerabilities';

    /**
     * Command usage
     */
    protected $usage = 'refactor:scan [module] [options]';

    /**
     * Command arguments
     */
    protected $arguments = [
        'module' => 'Module name to scan (optional, scans all if omitted)',
    ];

    /**
     * Command options
     */
    protected $options = [
        '--inventory' => 'Path to module inventory JSON (default: writable/refactor/module_inventory.json)',
    ];

    /**
     * Execute the command
     */
    public function run(array $params)
    {
        CLI::write('=== Security Scanner ===', 'cyan');
        CLI::newLine();

        try {
            // Load module inventory
            $inventoryPath = $params['inventory']
                ?? WRITEPATH . 'refactor' . DIRECTORY_SEPARATOR . 'module_inventory.json';

            if (!file_exists($inventoryPath)) {
                CLI::error('Module inventory not found. Run "php spark refactor:discover" first.');
                return;
            }

            $inventory = ModuleInventory::fromJson(file_get_contents($inventoryPath));
            $scanner = new SecurityScanner();

            // Determine which modules to scan
            $moduleName = array_shift($params);
            $modulesToScan = [];

            if ($moduleName) {
                $module = $inventory->getModule($moduleName);
                if (!$module) {
                    CLI::error("Module '{$moduleName}' not found in inventory.");
                    return;
                }
                $modulesToScan = [$module];
                CLI::write("Scanning module: {$moduleName}", 'white');
            } else {
                $modulesToScan = array_values($inventory->modules);
                CLI::write("Scanning all {$inventory->getModuleCount()} modules...", 'white');
            }

            CLI::newLine();

            // Ensure output directory exists
            $outputDir = WRITEPATH . 'refactor' . DIRECTORY_SEPARATOR . 'security_reports';
            if (!is_dir($outputDir)) {
                mkdir($outputDir, 0755, true);
            }

            $totalVulnerabilities = 0;
            $severityCounts = ['CRITICAL' => 0, 'HIGH' => 0, 'MEDIUM' => 0, 'LOW' => 0];

            foreach ($modulesToScan as $module) {
                $report = $scanner->scanModule($module);
                $totalVulnerabilities += $report->getTotalCount();

                $severityCounts['CRITICAL'] += $report->getCriticalCount();
                $severityCounts['HIGH'] += $report->getHighCount();
                $severityCounts['MEDIUM'] += $report->getMediumCount();
                $severityCounts['LOW'] += $report->getLowCount();

                // Save individual report
                $reportFile = $outputDir . DIRECTORY_SEPARATOR . $module->name . '_security_report.json';
                file_put_contents($reportFile, $report->toJson());

                // Display per-module summary
                if ($report->getTotalCount() > 0) {
                    CLI::write("  [{$module->name}] {$report->getTotalCount()} vulnerabilities found", 'yellow');
                } else {
                    CLI::write("  [{$module->name}] No vulnerabilities found", 'green');
                }
            }

            CLI::newLine();

            // Display overall summary
            CLI::write('Scan Complete!', 'green');
            CLI::newLine();
            CLI::write('Vulnerability Summary:', 'yellow');

            if ($severityCounts['CRITICAL'] > 0) {
                CLI::write("  CRITICAL: {$severityCounts['CRITICAL']}", 'red');
            } else {
                CLI::write("  CRITICAL: 0", 'white');
            }

            if ($severityCounts['HIGH'] > 0) {
                CLI::write("  HIGH:     {$severityCounts['HIGH']}", 'red');
            } else {
                CLI::write("  HIGH:     0", 'white');
            }

            if ($severityCounts['MEDIUM'] > 0) {
                CLI::write("  MEDIUM:   {$severityCounts['MEDIUM']}", 'yellow');
            } else {
                CLI::write("  MEDIUM:   0", 'white');
            }

            CLI::write("  LOW:      {$severityCounts['LOW']}", 'white');
            CLI::newLine();
            CLI::write("  TOTAL:    {$totalVulnerabilities}", 'white');
            CLI::newLine();

            CLI::write("Security reports saved to: {$outputDir}", 'green');

        } catch (\Exception $e) {
            CLI::error('Security scan failed: ' . $e->getMessage());
            return;
        }
    }
}

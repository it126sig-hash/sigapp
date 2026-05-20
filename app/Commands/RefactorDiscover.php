<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Libraries\Refactor\Discovery\ModuleDiscovery;
use App\Libraries\Refactor\Discovery\FileScanner;
use App\Libraries\Refactor\Discovery\CodeParser;

/**
 * CLI Command: Module Discovery
 *
 * Scans the application to discover all modules (controllers, models, services, repositories)
 * and saves the inventory to a JSON file.
 *
 * Usage: php spark refactor:discover
 */
class RefactorDiscover extends BaseCommand
{
    /**
     * Command group
     */
    protected $group = 'Refactor';

    /**
     * Command name
     */
    protected $name = 'refactor:discover';

    /**
     * Command description
     */
    protected $description = 'Discover all modules in the application (controllers, models, services, repositories)';

    /**
     * Command usage
     */
    protected $usage = 'refactor:discover [options]';

    /**
     * Command options
     */
    protected $options = [
        '--path' => 'Application path to scan (default: APPPATH)',
    ];

    /**
     * Execute the command
     */
    public function run(array $params)
    {
        CLI::write('=== Module Discovery ===', 'cyan');
        CLI::write('Scanning application for modules...', 'white');
        CLI::newLine();

        try {
            $appPath = $params['path'] ?? APPPATH;

            $fileScanner = new FileScanner();
            $codeParser = new CodeParser();
            $discovery = new ModuleDiscovery($appPath, $fileScanner, $codeParser);

            $inventory = $discovery->discover();

            // Display summary
            CLI::write('Discovery Complete!', 'green');
            CLI::newLine();
            CLI::write('Summary:', 'yellow');
            CLI::write("  Controllers:   " . count($inventory->controllers), 'white');
            CLI::write("  Models:        " . count($inventory->models), 'white');
            CLI::write("  Services:      " . count($inventory->services), 'white');
            CLI::write("  Repositories:  " . count($inventory->repositories), 'white');
            CLI::write("  Total Modules: " . $inventory->getModuleCount(), 'white');
            CLI::newLine();

            // Display module list
            if ($inventory->getModuleCount() > 0) {
                CLI::write('Discovered Modules:', 'yellow');
                foreach ($inventory->modules as $name => $module) {
                    $modelCount = count($module->modelPaths);
                    $methodCount = count($module->methods);
                    CLI::write("  [{$name}] - {$methodCount} methods, {$modelCount} models", 'white');
                }
                CLI::newLine();
            }

            // Save to JSON file
            $outputDir = WRITEPATH . 'refactor';
            if (!is_dir($outputDir)) {
                mkdir($outputDir, 0755, true);
            }

            $outputFile = $outputDir . DIRECTORY_SEPARATOR . 'module_inventory.json';
            file_put_contents($outputFile, $inventory->toJson());

            CLI::write("Module inventory saved to: {$outputFile}", 'green');

        } catch (\Exception $e) {
            CLI::error('Discovery failed: ' . $e->getMessage());
            return;
        }
    }
}

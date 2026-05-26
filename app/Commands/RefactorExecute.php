<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Libraries\Refactor\Execution\RefactorEngine;
use App\Libraries\Refactor\Execution\BackupManager;
use App\Libraries\Refactor\Execution\ControllerRefactorer;
use App\Libraries\Refactor\Execution\ControllerSplitter;
use App\Libraries\Refactor\Execution\SecurityFixer;
use App\Libraries\Refactor\Discovery\ModuleDiscovery;
use App\Libraries\Refactor\Discovery\FileScanner;
use App\Libraries\Refactor\Discovery\CodeParser;
use App\Libraries\Refactor\Generation\RepositoryGenerator;
use App\Libraries\Refactor\Generation\ServiceGenerator;
use App\Libraries\Refactor\Generation\CodeGenerator;
use App\Libraries\Refactor\Generation\QueryAnalyzer;
use App\Libraries\Refactor\Generation\ValidationExtractor;
use App\Libraries\Refactor\Models\ModuleInventory;
use App\Libraries\Refactor\Models\RefactorOptions;

/**
 * CLI Command: Refactor Execution
 *
 * Runs RefactorEngine for a specified module with configurable options.
 *
 * Usage: php spark refactor:execute <module> [options]
 */
class RefactorExecute extends BaseCommand
{
    /**
     * Command group
     */
    protected $group = 'Refactor';

    /**
     * Command name
     */
    protected $name = 'refactor:execute';

    /**
     * Command description
     */
    protected $description = 'Execute refactoring for a specified module';

    /**
     * Command usage
     */
    protected $usage = 'refactor:execute <module> [options]';

    /**
     * Command arguments
     */
    protected $arguments = [
        'module' => 'Module name to refactor (required)',
    ];

    /**
     * Command options
     */
    protected $options = [
        '--repository'  => 'Create repository class (default: yes)',
        '--service'     => 'Create service class (default: yes)',
        '--controller'  => 'Refactor controller (default: yes)',
        '--security'    => 'Fix security issues (default: yes)',
        '--split'       => 'Split web/API controllers (default: yes)',
        '--no-repository'  => 'Skip repository creation',
        '--no-service'     => 'Skip service creation',
        '--no-controller'  => 'Skip controller refactoring',
        '--no-security'    => 'Skip security fixes',
        '--no-split'       => 'Skip web/API splitting',
        '--run-tests'      => 'Run tests after refactoring',
        '--inventory'      => 'Path to module inventory JSON',
    ];

    /**
     * Execute the command
     */
    public function run(array $params)
    {
        CLI::write('=== Refactor Execution ===', 'cyan');
        CLI::newLine();

        try {
            $moduleName = array_shift($params);

            if (empty($moduleName)) {
                CLI::error('Module name is required. Usage: php spark refactor:execute <module>');
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
                return;
            }

            // Build refactor options from CLI flags
            $options = new RefactorOptions();
            $options->createRepository = !CLI::getOption('no-repository');
            $options->createService = !CLI::getOption('no-service');
            $options->refactorController = !CLI::getOption('no-controller');
            $options->fixSecurity = !CLI::getOption('no-security');
            $options->separateWebApi = !CLI::getOption('no-split');
            $options->runTests = (bool) CLI::getOption('run-tests');

            // Display configuration
            CLI::write("Module: {$moduleName}", 'white');
            CLI::write('Refactoring Options:', 'yellow');
            CLI::write("  Create Repository:    " . ($options->createRepository ? 'Yes' : 'No'), 'white');
            CLI::write("  Create Service:       " . ($options->createService ? 'Yes' : 'No'), 'white');
            CLI::write("  Refactor Controller:  " . ($options->refactorController ? 'Yes' : 'No'), 'white');
            CLI::write("  Fix Security:         " . ($options->fixSecurity ? 'Yes' : 'No'), 'white');
            CLI::write("  Split Web/API:        " . ($options->separateWebApi ? 'Yes' : 'No'), 'white');
            CLI::write("  Run Tests:            " . ($options->runTests ? 'Yes' : 'No'), 'white');
            CLI::newLine();

            // Confirm execution
            $confirm = CLI::prompt('Proceed with refactoring?', ['y', 'n']);
            if ($confirm !== 'y') {
                CLI::write('Refactoring cancelled.', 'yellow');
                return;
            }

            CLI::newLine();
            CLI::write('Starting refactoring...', 'white');
            CLI::newLine();

            // Create engine dependencies
            $codeGenerator = new CodeGenerator();
            $queryAnalyzer = new QueryAnalyzer();
            $validationExtractor = new ValidationExtractor();
            $codeParser = new CodeParser();
            $controllerSplitter = new ControllerSplitter($codeParser, $codeGenerator);

            $repoGen = new RepositoryGenerator($codeGenerator, $queryAnalyzer);
            $svcGen = new ServiceGenerator($codeGenerator, $validationExtractor);
            $ctrlRef = new ControllerRefactorer($codeParser, $codeGenerator, $controllerSplitter);
            $secFix = new SecurityFixer($codeGenerator, $queryAnalyzer);
            $backup = new BackupManager();

            $fileScanner = new FileScanner();
            $moduleDiscovery = new ModuleDiscovery(APPPATH, $fileScanner, $codeParser);

            $engine = new RefactorEngine(
                $backup,
                $repoGen,
                $svcGen,
                $ctrlRef,
                $secFix,
                $controllerSplitter,
                $moduleDiscovery
            );

            // Execute refactoring
            $result = $engine->refactor($moduleName, $options);

            CLI::newLine();

            // Display results
            if ($result->success) {
                CLI::write('Refactoring Complete!', 'green');
                CLI::newLine();

                if (!empty($result->filesCreated)) {
                    CLI::write('Files Created:', 'yellow');
                    foreach ($result->filesCreated as $file) {
                        CLI::write("  + {$file}", 'green');
                    }
                    CLI::newLine();
                }

                if (!empty($result->filesModified)) {
                    CLI::write('Files Modified:', 'yellow');
                    foreach ($result->filesModified as $file) {
                        CLI::write("  ~ {$file}", 'yellow');
                    }
                    CLI::newLine();
                }

                if (!empty($result->stepsCompleted)) {
                    CLI::write('Steps Completed:', 'yellow');
                    foreach ($result->stepsCompleted as $step) {
                        CLI::write("  ✓ {$step}", 'green');
                    }
                    CLI::newLine();
                }

                if ($result->backupId) {
                    CLI::write("Backup ID: {$result->backupId}", 'white');
                    CLI::write('Use "php spark refactor:backup --restore ' . $result->backupId . '" to rollback.', 'white');
                }
            } else {
                CLI::write('Refactoring Failed!', 'red');
                CLI::write("Error: {$result->errorMessage}", 'red');
                CLI::newLine();

                if ($result->backupId) {
                    $restore = CLI::prompt('Would you like to rollback?', ['y', 'n']);
                    if ($restore === 'y') {
                        $engine->rollback($result->backupId);
                        CLI::write('Rollback complete.', 'green');
                    } else {
                        CLI::write("Backup preserved: {$result->backupId}", 'yellow');
                    }
                }
            }

        } catch (\Exception $e) {
            CLI::error('Refactoring failed: ' . $e->getMessage());
            return;
        }
    }
}

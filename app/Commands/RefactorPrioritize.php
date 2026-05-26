<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Libraries\Refactor\Analysis\PrioritizationSystem;
use App\Libraries\Refactor\Models\DependencyGraph;
use App\Libraries\Refactor\Models\ModuleInventory;

/**
 * CLI Command: Module Prioritization
 *
 * Runs PrioritizationSystem to calculate priority scores and display
 * the recommended refactoring order.
 *
 * Usage: php spark refactor:prioritize
 */
class RefactorPrioritize extends BaseCommand
{
    /**
     * Command group
     */
    protected $group = 'Refactor';

    /**
     * Command name
     */
    protected $name = 'refactor:prioritize';

    /**
     * Command description
     */
    protected $description = 'Calculate module priority scores and recommend refactoring order';

    /**
     * Command usage
     */
    protected $usage = 'refactor:prioritize [options]';

    /**
     * Command options
     */
    protected $options = [
        '--graph'     => 'Path to dependency graph JSON (default: writable/refactor/dependency_graph.json)',
        '--inventory' => 'Path to module inventory JSON (default: writable/refactor/module_inventory.json)',
    ];

    /**
     * Execute the command
     */
    public function run(array $params)
    {
        CLI::write('=== Module Prioritization ===', 'cyan');
        CLI::newLine();

        try {
            $refactorDir = WRITEPATH . 'refactor' . DIRECTORY_SEPARATOR;

            // Load dependency graph
            $graphPath = $params['graph'] ?? $refactorDir . 'dependency_graph.json';
            if (!file_exists($graphPath)) {
                CLI::error('Dependency graph not found. Run "php spark refactor:analyze" first.');
                return;
            }

            // Load module inventory
            $inventoryPath = $params['inventory'] ?? $refactorDir . 'module_inventory.json';
            if (!file_exists($inventoryPath)) {
                CLI::error('Module inventory not found. Run "php spark refactor:discover" first.');
                return;
            }

            CLI::write('Loading dependency graph and inventory...', 'white');
            $graph = DependencyGraph::fromJson(file_get_contents($graphPath));
            $inventory = ModuleInventory::fromJson(file_get_contents($inventoryPath));

            // Run prioritization
            CLI::write('Calculating priority scores...', 'white');
            CLI::newLine();

            $prioritizer = new PrioritizationSystem($inventory, $graph);
            $priorityScores = $prioritizer->prioritize();

            // Display classification
            $leafModules = $prioritizer->identifyLeafModules();
            $coreModules = $prioritizer->identifyCoreModules();

            CLI::write('Module Classification:', 'yellow');
            CLI::newLine();

            if (!empty($leafModules)) {
                CLI::write('  LEAF Modules (safe starting points, no dependents):', 'green');
                foreach ($leafModules as $module) {
                    CLI::write("    • {$module}", 'white');
                }
                CLI::newLine();
            }

            if (!empty($coreModules)) {
                CLI::write('  CORE Modules (high-risk, many dependents):', 'red');
                foreach ($coreModules as $module) {
                    CLI::write("    • {$module}", 'white');
                }
                CLI::newLine();
            }

            // Display intermediate modules
            $intermediateModules = array_diff(
                $graph->nodes,
                $leafModules,
                $coreModules
            );
            if (!empty($intermediateModules)) {
                CLI::write('  INTERMEDIATE Modules:', 'yellow');
                foreach ($intermediateModules as $module) {
                    CLI::write("    • {$module}", 'white');
                }
                CLI::newLine();
            }

            // Display recommended order
            CLI::write('Recommended Refactoring Order:', 'yellow');
            CLI::newLine();

            $order = $prioritizer->getRecommendedOrder();
            $position = 1;
            foreach ($order as $moduleName) {
                $score = $prioritizer->getPriorityScore($moduleName);
                $category = $score ? $score->category : 'UNKNOWN';
                $scoreValue = $score ? number_format($score->score, 2) : '0.00';
                CLI::write("  {$position}. [{$category}] {$moduleName} (score: {$scoreValue})", 'white');
                $position++;
            }
            CLI::newLine();

            CLI::write('Prioritization complete!', 'green');

        } catch (\Exception $e) {
            CLI::error('Prioritization failed: ' . $e->getMessage());
            return;
        }
    }
}

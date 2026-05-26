<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Libraries\Refactor\Analysis\DependencyAnalyzer;
use App\Libraries\Refactor\Models\ModuleInventory;

/**
 * CLI Command: Dependency Analysis
 *
 * Runs DependencyAnalyzer on discovered modules to build a dependency graph
 * and calculate impact scores.
 *
 * Usage: php spark refactor:analyze
 */
class RefactorAnalyze extends BaseCommand
{
    /**
     * Command group
     */
    protected $group = 'Refactor';

    /**
     * Command name
     */
    protected $name = 'refactor:analyze';

    /**
     * Command description
     */
    protected $description = 'Analyze module dependencies and calculate impact scores';

    /**
     * Command usage
     */
    protected $usage = 'refactor:analyze [options]';

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
        CLI::write('=== Dependency Analysis ===', 'cyan');
        CLI::newLine();

        try {
            // Load module inventory
            $inventoryPath = $params['inventory']
                ?? WRITEPATH . 'refactor' . DIRECTORY_SEPARATOR . 'module_inventory.json';

            if (!file_exists($inventoryPath)) {
                CLI::error('Module inventory not found. Run "php spark refactor:discover" first.');
                return;
            }

            CLI::write('Loading module inventory...', 'white');
            $json = file_get_contents($inventoryPath);
            $inventory = ModuleInventory::fromJson($json);

            CLI::write("Loaded {$inventory->getModuleCount()} modules.", 'white');
            CLI::newLine();

            // Run dependency analysis
            CLI::write('Analyzing dependencies...', 'white');
            $analyzer = new DependencyAnalyzer($inventory);
            $graph = $analyzer->analyze();

            // Display summary
            CLI::write('Analysis Complete!', 'green');
            CLI::newLine();
            CLI::write('Dependency Graph Summary:', 'yellow');
            CLI::write("  Nodes (modules): " . count($graph->nodes), 'white');
            CLI::write("  Edges (dependencies): " . array_sum(array_map('count', $graph->edges)), 'white');
            CLI::write("  Circular dependencies: " . count($graph->circular), 'white');
            CLI::newLine();

            // Display impact scores
            if (!empty($graph->impactScores)) {
                CLI::write('Impact Scores (higher = more modules depend on it):', 'yellow');
                arsort($graph->impactScores);
                foreach ($graph->impactScores as $module => $score) {
                    $bar = str_repeat('█', min($score, 20));
                    CLI::write("  {$module}: {$score} {$bar}", 'white');
                }
                CLI::newLine();
            }

            // Display circular dependencies if any
            if (!empty($graph->circular)) {
                CLI::write('⚠ Circular Dependencies Detected:', 'red');
                foreach ($graph->circular as $cycle) {
                    CLI::write('  ' . implode(' → ', $cycle), 'yellow');
                }
                CLI::newLine();
            }

            // Save dependency graph
            $outputDir = WRITEPATH . 'refactor';
            if (!is_dir($outputDir)) {
                mkdir($outputDir, 0755, true);
            }

            $outputFile = $outputDir . DIRECTORY_SEPARATOR . 'dependency_graph.json';
            file_put_contents($outputFile, $graph->toJson());
            CLI::write("Dependency graph saved to: {$outputFile}", 'green');

            // Save Mermaid diagram
            $mermaidFile = $outputDir . DIRECTORY_SEPARATOR . 'dependency_graph.mmd';
            file_put_contents($mermaidFile, $graph->toMermaid());
            CLI::write("Mermaid diagram saved to: {$mermaidFile}", 'green');

        } catch (\Exception $e) {
            CLI::error('Analysis failed: ' . $e->getMessage());
            return;
        }
    }
}

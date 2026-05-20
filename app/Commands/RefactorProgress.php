<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Libraries\Refactor\Execution\ProgressTracker;

/**
 * CLI Command: Progress Tracking
 *
 * Shows overall refactoring progress and module statuses.
 *
 * Usage: php spark refactor:progress [options]
 */
class RefactorProgress extends BaseCommand
{
    /**
     * Command group
     */
    protected $group = 'Refactor';

    /**
     * Command name
     */
    protected $name = 'refactor:progress';

    /**
     * Command description
     */
    protected $description = 'Show refactoring progress and module statuses';

    /**
     * Command usage
     */
    protected $usage = 'refactor:progress [options]';

    /**
     * Command options
     */
    protected $options = [
        '--status' => 'Filter by status (NOT_STARTED, AUDITED, IN_PROGRESS, COMPLETED, FAILED)',
    ];

    /**
     * Execute the command
     */
    public function run(array $params)
    {
        CLI::write('=== Refactoring Progress ===', 'cyan');
        CLI::newLine();

        try {
            $tracker = new ProgressTracker();
            $statusFilter = CLI::getOption('status');

            // Calculate overall progress
            $overallProgress = $tracker->calculateOverallProgress();
            $progressBar = $this->renderProgressBar($overallProgress);

            CLI::write("Overall Progress: {$progressBar} " . number_format($overallProgress, 1) . '%', 'white');
            CLI::newLine();

            // Get tracked modules
            $trackedModules = $tracker->getTrackedModules();

            if (empty($trackedModules)) {
                CLI::write('No modules tracked yet.', 'yellow');
                CLI::write('Run "php spark refactor:discover" to start.', 'white');
                return;
            }

            // Apply status filter if provided
            if ($statusFilter) {
                $statusFilter = strtoupper($statusFilter);
                $filteredModules = $tracker->getModulesByStatus($statusFilter);

                CLI::write("Modules with status '{$statusFilter}':", 'yellow');
                CLI::newLine();

                if (empty($filteredModules)) {
                    CLI::write("  No modules with status '{$statusFilter}'.", 'white');
                } else {
                    foreach ($filteredModules as $moduleName) {
                        $data = $tracker->getModuleData($moduleName);
                        $this->displayModuleRow($moduleName, $data);
                    }
                }
            } else {
                // Display all modules grouped by status
                $report = $tracker->generateProgressReport();

                // Display status counts
                CLI::write('Status Summary:', 'yellow');
                $statusCounts = [
                    'COMPLETED' => 0,
                    'IN_PROGRESS' => 0,
                    'AUDITED' => 0,
                    'FAILED' => 0,
                    'NOT_STARTED' => 0,
                ];

                foreach ($trackedModules as $moduleName) {
                    $status = $tracker->getModuleStatus($moduleName);
                    if (isset($statusCounts[$status])) {
                        $statusCounts[$status]++;
                    }
                }

                CLI::write("  Completed:   {$statusCounts['COMPLETED']}", 'green');
                CLI::write("  In Progress: {$statusCounts['IN_PROGRESS']}", 'yellow');
                CLI::write("  Audited:     {$statusCounts['AUDITED']}", 'cyan');
                CLI::write("  Failed:      {$statusCounts['FAILED']}", 'red');
                CLI::write("  Not Started: {$statusCounts['NOT_STARTED']}", 'white');
                CLI::newLine();

                // Display module table
                CLI::write('Module Details:', 'yellow');
                CLI::newLine();

                foreach ($trackedModules as $moduleName) {
                    $data = $tracker->getModuleData($moduleName);
                    $this->displayModuleRow($moduleName, $data);
                }
            }

            CLI::newLine();

        } catch (\Exception $e) {
            CLI::error('Progress tracking failed: ' . $e->getMessage());
            return;
        }
    }

    /**
     * Render a text-based progress bar
     */
    private function renderProgressBar(float $percentage): string
    {
        $width = 30;
        $filled = (int) round($percentage / 100 * $width);
        $empty = $width - $filled;

        return '[' . str_repeat('█', $filled) . str_repeat('░', $empty) . ']';
    }

    /**
     * Display a single module row
     */
    private function displayModuleRow(string $moduleName, ?array $data): void
    {
        if (!$data) {
            CLI::write("  {$moduleName}: UNKNOWN", 'white');
            return;
        }

        $status = $data['status'] ?? 'NOT_STARTED';
        $color = match ($status) {
            'COMPLETED' => 'green',
            'IN_PROGRESS' => 'yellow',
            'AUDITED' => 'cyan',
            'FAILED' => 'red',
            default => 'white',
        };

        $statusPadded = str_pad($status, 12);
        $line = "  {$moduleName}";
        $line = str_pad($line, 30);
        $line .= "[{$statusPadded}]";

        if (!empty($data['auditedAt'])) {
            $line .= " audited: " . substr($data['auditedAt'], 0, 10);
        }

        if (!empty($data['refactoredAt'])) {
            $line .= " refactored: " . substr($data['refactoredAt'], 0, 10);
        }

        CLI::write($line, $color);
    }
}

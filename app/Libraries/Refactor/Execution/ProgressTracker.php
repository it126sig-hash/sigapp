<?php

namespace App\Libraries\Refactor\Execution;

use DateTime;

/**
 * Progress Tracker
 *
 * Tracks refactoring progress across all modules in the application.
 * Maintains status for each module, records audit and refactoring activities,
 * calculates overall progress, and generates filtered progress reports.
 *
 * Data is persisted in JSON format at writable/refactor/progress.json.
 *
 * @package App\Libraries\Refactor\Execution
 */
class ProgressTracker
{
    /**
     * Module status constants
     */
    public const STATUS_NOT_STARTED = 'NOT_STARTED';
    public const STATUS_AUDITED = 'AUDITED';
    public const STATUS_IN_PROGRESS = 'IN_PROGRESS';
    public const STATUS_COMPLETED = 'COMPLETED';
    public const STATUS_FAILED = 'FAILED';

    /**
     * Valid status transitions
     */
    private const VALID_STATUSES = [
        self::STATUS_NOT_STARTED,
        self::STATUS_AUDITED,
        self::STATUS_IN_PROGRESS,
        self::STATUS_COMPLETED,
        self::STATUS_FAILED,
    ];

    /**
     * Path to the progress JSON file
     */
    private string $progressFile;

    /**
     * In-memory progress data
     *
     * @var array<string, array<string, mixed>>
     */
    private array $data;

    /**
     * Create a new ProgressTracker instance
     *
     * @param string|null $progressFile Custom path to progress file (defaults to writable/refactor/progress.json)
     */
    public function __construct(?string $progressFile = null)
    {
        $this->progressFile = $progressFile ?? WRITEPATH . 'refactor/progress.json';
        $this->load();
    }

    /**
     * Record that a module has been audited
     *
     * @param string $moduleName The name of the module that was audited
     * @return void
     */
    public function recordAudit(string $moduleName): void
    {
        $this->ensureModuleExists($moduleName);

        $this->data['modules'][$moduleName]['status'] = self::STATUS_AUDITED;
        $this->data['modules'][$moduleName]['auditedAt'] = (new DateTime())->format('c');

        $this->save();
    }

    /**
     * Record that a module refactoring has been completed or failed
     *
     * @param string $moduleName The name of the module that was refactored
     * @param bool $success Whether the refactoring was successful
     * @return void
     */
    public function recordRefactor(string $moduleName, bool $success): void
    {
        $this->ensureModuleExists($moduleName);

        if ($success) {
            $this->data['modules'][$moduleName]['status'] = self::STATUS_COMPLETED;
            $this->data['modules'][$moduleName]['refactoredAt'] = (new DateTime())->format('c');
        } else {
            $this->data['modules'][$moduleName]['status'] = self::STATUS_FAILED;
            $this->data['modules'][$moduleName]['failedAt'] = (new DateTime())->format('c');
        }

        $this->save();
    }

    /**
     * Calculate overall refactoring progress as a percentage
     *
     * @return float Progress percentage (0.0 - 100.0)
     */
    public function calculateOverallProgress(): float
    {
        $modules = $this->data['modules'] ?? [];

        if (empty($modules)) {
            return 0.0;
        }

        $totalModules = count($modules);
        $completedModules = 0;

        foreach ($modules as $moduleData) {
            if ($moduleData['status'] === self::STATUS_COMPLETED) {
                $completedModules++;
            }
        }

        return round(($completedModules / $totalModules) * 100, 2);
    }

    /**
     * Generate a progress report with optional filtering
     *
     * @param array<string, mixed> $filters Optional filters (e.g., ['status' => 'COMPLETED'])
     * @return array<string, mixed> Progress report data
     */
    public function generateProgressReport(array $filters = []): array
    {
        $modules = $this->data['modules'] ?? [];
        $filteredModules = $modules;

        // Apply status filter
        if (isset($filters['status'])) {
            $filteredModules = array_filter($modules, function (array $moduleData) use ($filters): bool {
                return $moduleData['status'] === $filters['status'];
            });
        }

        $totalModules = count($modules);
        $completedModules = 0;
        $auditedModules = 0;
        $inProgressModules = 0;
        $failedModules = 0;
        $notStartedModules = 0;

        foreach ($modules as $moduleData) {
            switch ($moduleData['status']) {
                case self::STATUS_COMPLETED:
                    $completedModules++;
                    break;
                case self::STATUS_AUDITED:
                    $auditedModules++;
                    break;
                case self::STATUS_IN_PROGRESS:
                    $inProgressModules++;
                    break;
                case self::STATUS_FAILED:
                    $failedModules++;
                    break;
                case self::STATUS_NOT_STARTED:
                    $notStartedModules++;
                    break;
            }
        }

        return [
            'overallProgress' => $this->calculateOverallProgress(),
            'totalModules' => $totalModules,
            'completedModules' => $completedModules,
            'auditedModules' => $auditedModules,
            'inProgressModules' => $inProgressModules,
            'failedModules' => $failedModules,
            'notStartedModules' => $notStartedModules,
            'modules' => $filteredModules,
            'generatedAt' => (new DateTime())->format('c'),
        ];
    }

    /**
     * Get the current status of a module
     *
     * @param string $moduleName The name of the module
     * @return string The module's current status
     */
    public function getModuleStatus(string $moduleName): string
    {
        if (!isset($this->data['modules'][$moduleName])) {
            return self::STATUS_NOT_STARTED;
        }

        return $this->data['modules'][$moduleName]['status'];
    }

    /**
     * Get all modules with a given status
     *
     * @param string $status The status to filter by
     * @return array<int, string> Array of module names with the given status
     */
    public function getModulesByStatus(string $status): array
    {
        $result = [];

        foreach ($this->data['modules'] ?? [] as $moduleName => $moduleData) {
            if ($moduleData['status'] === $status) {
                $result[] = $moduleName;
            }
        }

        return $result;
    }

    /**
     * Reset a module's status back to NOT_STARTED
     *
     * @param string $moduleName The name of the module to reset
     * @return void
     */
    public function reset(string $moduleName): void
    {
        if (isset($this->data['modules'][$moduleName])) {
            $this->data['modules'][$moduleName] = [
                'status' => self::STATUS_NOT_STARTED,
                'auditedAt' => null,
                'refactoredAt' => null,
                'failedAt' => null,
                'vulnerabilitiesFixed' => 0,
                'backupId' => null,
            ];

            $this->save();
        }
    }

    /**
     * Mark a module as in progress
     *
     * @param string $moduleName The name of the module
     * @return void
     */
    public function markInProgress(string $moduleName): void
    {
        $this->ensureModuleExists($moduleName);

        $this->data['modules'][$moduleName]['status'] = self::STATUS_IN_PROGRESS;

        $this->save();
    }

    /**
     * Record the number of vulnerabilities fixed for a module
     *
     * @param string $moduleName The name of the module
     * @param int $count Number of vulnerabilities fixed
     * @return void
     */
    public function recordVulnerabilitiesFixed(string $moduleName, int $count): void
    {
        $this->ensureModuleExists($moduleName);

        $this->data['modules'][$moduleName]['vulnerabilitiesFixed'] = $count;

        $this->save();
    }

    /**
     * Record the backup ID associated with a module's refactoring
     *
     * @param string $moduleName The name of the module
     * @param string $backupId The backup identifier
     * @return void
     */
    public function recordBackupId(string $moduleName, string $backupId): void
    {
        $this->ensureModuleExists($moduleName);

        $this->data['modules'][$moduleName]['backupId'] = $backupId;

        $this->save();
    }

    /**
     * Get all tracked module names
     *
     * @return array<int, string> Array of module names
     */
    public function getTrackedModules(): array
    {
        return array_keys($this->data['modules'] ?? []);
    }

    /**
     * Check if a module is being tracked
     *
     * @param string $moduleName The name of the module
     * @return bool True if the module is tracked
     */
    public function isTracked(string $moduleName): bool
    {
        return isset($this->data['modules'][$moduleName]);
    }

    /**
     * Get the full data for a specific module
     *
     * @param string $moduleName The name of the module
     * @return array<string, mixed>|null Module data or null if not tracked
     */
    public function getModuleData(string $moduleName): ?array
    {
        return $this->data['modules'][$moduleName] ?? null;
    }

    /**
     * Load progress data from JSON file
     *
     * @return void
     */
    private function load(): void
    {
        if (file_exists($this->progressFile)) {
            $content = file_get_contents($this->progressFile);

            if ($content !== false) {
                $decoded = json_decode($content, true);

                if (is_array($decoded)) {
                    $this->data = $decoded;
                    return;
                }
            }
        }

        // Initialize with empty data structure
        $this->data = [
            'modules' => [],
        ];
    }

    /**
     * Save progress data to JSON file
     *
     * @return void
     */
    private function save(): void
    {
        $dir = dirname($this->progressFile);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $json = json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        file_put_contents($this->progressFile, $json);
    }

    /**
     * Ensure a module entry exists in the data structure
     *
     * @param string $moduleName The name of the module
     * @return void
     */
    private function ensureModuleExists(string $moduleName): void
    {
        if (!isset($this->data['modules'][$moduleName])) {
            $this->data['modules'][$moduleName] = [
                'status' => self::STATUS_NOT_STARTED,
                'auditedAt' => null,
                'refactoredAt' => null,
                'failedAt' => null,
                'vulnerabilitiesFixed' => 0,
                'backupId' => null,
            ];
        }
    }
}

<?php

namespace App\Libraries\Refactor\Execution;

use App\Libraries\Refactor\Discovery\ModuleDiscovery;
use App\Libraries\Refactor\Exceptions\RefactorException;
use App\Libraries\Refactor\Generation\RepositoryGenerator;
use App\Libraries\Refactor\Generation\ServiceGenerator;
use App\Libraries\Refactor\Models\Module;
use App\Libraries\Refactor\Models\RefactorOptions;
use App\Libraries\Refactor\Models\RefactorResult;
use Psr\Log\LoggerInterface;

/**
 * RefactorEngine
 *
 * Orchestrates the complete refactoring workflow for a CodeIgniter 4 module.
 * Coordinates backup creation, step-by-step execution of generators and fixers,
 * error handling with rollback, and optional test execution.
 *
 * Execution order:
 * 1. Repository generation
 * 2. Service generation
 * 3. Controller refactoring
 * 4. Security fixes
 * 5. Web/API splitting
 *
 * @package App\Libraries\Refactor\Execution
 */
class RefactorEngine
{
    /**
     * @var BackupManager Manages backup and rollback operations
     */
    private BackupManager $backupManager;

    /**
     * @var RepositoryGenerator Generates repository classes
     */
    private RepositoryGenerator $repositoryGenerator;

    /**
     * @var ServiceGenerator Generates service classes
     */
    private ServiceGenerator $serviceGenerator;

    /**
     * @var ControllerRefactorer Refactors controller classes
     */
    private ControllerRefactorer $controllerRefactorer;

    /**
     * @var SecurityFixer Applies security fixes
     */
    private SecurityFixer $securityFixer;

    /**
     * @var ControllerSplitter Splits controllers into Web/API
     */
    private ControllerSplitter $controllerSplitter;

    /**
     * @var ModuleDiscovery Discovers module information
     */
    private ModuleDiscovery $moduleDiscovery;

    /**
     * @var LoggerInterface|null Optional logger for step tracking
     */
    private ?LoggerInterface $logger;

    /**
     * @var RefactorResult Current result being built during execution
     */
    private RefactorResult $currentResult;

    /**
     * @var string|null Current backup ID for rollback
     */
    private ?string $currentBackupId = null;

    /**
     * Constructor
     *
     * @param BackupManager $backupManager Backup manager instance
     * @param RepositoryGenerator $repositoryGenerator Repository generator instance
     * @param ServiceGenerator $serviceGenerator Service generator instance
     * @param ControllerRefactorer $controllerRefactorer Controller refactorer instance
     * @param SecurityFixer $securityFixer Security fixer instance
     * @param ControllerSplitter $controllerSplitter Controller splitter instance
     * @param ModuleDiscovery $moduleDiscovery Module discovery instance
     * @param LoggerInterface|null $logger Optional PSR-3 logger
     */
    public function __construct(
        BackupManager $backupManager,
        RepositoryGenerator $repositoryGenerator,
        ServiceGenerator $serviceGenerator,
        ControllerRefactorer $controllerRefactorer,
        SecurityFixer $securityFixer,
        ControllerSplitter $controllerSplitter,
        ModuleDiscovery $moduleDiscovery,
        ?LoggerInterface $logger = null
    ) {
        $this->backupManager = $backupManager;
        $this->repositoryGenerator = $repositoryGenerator;
        $this->serviceGenerator = $serviceGenerator;
        $this->controllerRefactorer = $controllerRefactorer;
        $this->securityFixer = $securityFixer;
        $this->controllerSplitter = $controllerSplitter;
        $this->moduleDiscovery = $moduleDiscovery;
        $this->logger = $logger;
    }

    /**
     * Execute the complete refactoring workflow for a module
     *
     * Orchestrates all refactoring steps in order, with backup creation
     * before modifications and rollback on failure.
     *
     * @param string $moduleName Name of the module to refactor
     * @param RefactorOptions $options Refactoring options
     * @return RefactorResult Result of the refactoring operation
     */
    public function refactor(string $moduleName, RefactorOptions $options): RefactorResult
    {
        $this->currentResult = RefactorResult::success();
        $this->currentBackupId = null;

        $this->log('info', "Starting refactoring for module: {$moduleName}");

        try {
            // Discover module information
            $module = $this->discoverModule($moduleName);

            if ($module === null) {
                return RefactorResult::failure("Module not found: {$moduleName}");
            }

            // Collect files that will be affected
            $affectedFiles = $this->collectAffectedFiles($module);

            // Create backup before modifications
            $this->currentBackupId = $this->createBackup($affectedFiles);
            $this->currentResult->backupId = $this->currentBackupId;

            // Execute steps based on options
            if ($options->createRepository) {
                $this->executeStep('Repository Generation', function () use ($module) {
                    return $this->executeRepositoryGeneration($module);
                });
            }

            if ($options->createService) {
                $this->executeStep('Service Generation', function () use ($module) {
                    return $this->executeServiceGeneration($module);
                });
            }

            if ($options->refactorController) {
                $this->executeStep('Controller Refactoring', function () use ($module) {
                    return $this->executeControllerRefactoring($module);
                });
            }

            if ($options->fixSecurity) {
                $this->executeStep('Security Fixes', function () use ($module) {
                    return $this->executeSecurityFixes($module);
                });
            }

            if ($options->separateWebApi) {
                $this->executeStep('Web/API Splitting', function () use ($module) {
                    return $this->executeWebApiSplitting($module);
                });
            }

            // Run tests if requested
            if ($options->runTests) {
                $this->executeStep('Test Execution', function () {
                    $passed = $this->runTests();
                    if (!$passed) {
                        throw new RefactorException(
                            'Tests failed after refactoring',
                            0,
                            RefactorException::CATEGORY_REFACTOR,
                            RefactorException::SEVERITY_ERROR
                        );
                    }
                    return [];
                });
            }

            $this->log('info', "Refactoring completed successfully for module: {$moduleName}");

            return $this->currentResult;
        } catch (RefactorException $e) {
            return $this->handleFailure($e->getMessage());
        } catch (\Throwable $e) {
            return $this->handleFailure("Unexpected error: {$e->getMessage()}");
        }
    }

    /**
     * Create a backup of files before modification
     *
     * @param array<string> $files Array of file paths to backup
     * @return string Backup ID for rollback
     * @throws RefactorException If backup creation fails
     */
    public function createBackup(array $files): string
    {
        $this->log('info', 'Creating backup of ' . count($files) . ' file(s)');

        try {
            $backupId = $this->backupManager->createBackup(
                $files,
                'refactor_engine',
                'Pre-refactoring backup'
            );

            $this->log('info', "Backup created: {$backupId}");

            return $backupId;
        } catch (\Throwable $e) {
            throw new RefactorException(
                "Failed to create backup: {$e->getMessage()}",
                0,
                RefactorException::CATEGORY_REFACTOR,
                RefactorException::SEVERITY_CRITICAL,
                $e
            );
        }
    }

    /**
     * Execute a single refactoring step with error handling
     *
     * Logs the step, executes the action, and records the result.
     * On failure, throws a RefactorException to trigger rollback.
     *
     * @param string $stepName Human-readable step name
     * @param callable $action Callable that performs the step, returns array of created/modified files
     * @return void
     * @throws RefactorException If the step fails
     */
    public function executeStep(string $stepName, callable $action): void
    {
        $this->log('info', "Executing step: {$stepName}");

        try {
            /** @var array{created?: string[], modified?: string[]} $result */
            $result = $action();

            // Record created files
            if (isset($result['created']) && is_array($result['created'])) {
                foreach ($result['created'] as $file) {
                    $this->currentResult->addCreatedFile($file);
                }
            }

            // Record modified files
            if (isset($result['modified']) && is_array($result['modified'])) {
                foreach ($result['modified'] as $file) {
                    $this->currentResult->addModifiedFile($file);
                }
            }

            // Record completed step
            $this->currentResult->addCompletedStep($stepName);
            $this->log('info', "Step completed: {$stepName}");
        } catch (RefactorException $e) {
            $this->log('error', "Step failed: {$stepName} - {$e->getMessage()}");
            throw $e;
        } catch (\Throwable $e) {
            $this->log('error', "Step failed: {$stepName} - {$e->getMessage()}");
            throw new RefactorException(
                "Step '{$stepName}' failed: {$e->getMessage()}",
                0,
                RefactorException::CATEGORY_REFACTOR,
                RefactorException::SEVERITY_ERROR,
                $e
            );
        }
    }

    /**
     * Rollback changes using a backup
     *
     * Restores all files from the specified backup to their original state.
     *
     * @param string $backupId Backup identifier to restore from
     * @return void
     */
    public function rollback(string $backupId): void
    {
        $this->log('info', "Rolling back using backup: {$backupId}");

        try {
            $this->backupManager->restoreBackup($backupId);
            $this->log('info', 'Rollback completed successfully');
        } catch (\Throwable $e) {
            $this->log('error', "Rollback failed: {$e->getMessage()}");
            // Rollback failure is logged but not re-thrown to avoid masking the original error
        }
    }

    /**
     * Run tests to verify refactoring correctness
     *
     * Executes PHPUnit tests if they exist for the module.
     * Returns true if tests pass or no tests exist.
     *
     * @return bool True if tests pass or no tests exist
     */
    public function runTests(): bool
    {
        $this->log('info', 'Running tests...');

        $testCommand = 'php vendor/bin/phpunit --no-coverage 2>&1';

        $output = [];
        $returnCode = 0;

        exec($testCommand, $output, $returnCode);

        $passed = $returnCode === 0;

        if ($passed) {
            $this->log('info', 'Tests passed');
        } else {
            $this->log('warning', 'Tests failed: ' . implode("\n", array_slice($output, -5)));
        }

        return $passed;
    }

    /**
     * Discover module information by name
     *
     * @param string $moduleName Module name to discover
     * @return Module|null Module information or null if not found
     */
    private function discoverModule(string $moduleName): ?Module
    {
        $this->log('info', "Discovering module: {$moduleName}");

        try {
            $inventory = $this->moduleDiscovery->discover();
            return $inventory->getModule($moduleName);
        } catch (\Throwable $e) {
            $this->log('warning', "Module discovery failed: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Collect all files that may be affected by refactoring
     *
     * @param Module $module Module being refactored
     * @return array<string> Array of file paths
     */
    private function collectAffectedFiles(Module $module): array
    {
        $files = [];

        // Controller file
        if (file_exists($module->controllerPath)) {
            $files[] = $module->controllerPath;
        }

        // Model files
        foreach ($module->modelPaths as $modelPath) {
            if (file_exists($modelPath)) {
                $files[] = $modelPath;
            }
        }

        // Existing service file
        if ($module->servicePath !== null && file_exists($module->servicePath)) {
            $files[] = $module->servicePath;
        }

        // Existing repository file
        if ($module->repositoryPath !== null && file_exists($module->repositoryPath)) {
            $files[] = $module->repositoryPath;
        }

        return $files;
    }

    /**
     * Execute repository generation step
     *
     * @param Module $module Module being refactored
     * @return array{created: string[], modified: string[]} Files affected
     */
    private function executeRepositoryGeneration(Module $module): array
    {
        $created = [];

        // Generate repository for each model
        foreach ($module->modelPaths as $modelPath) {
            $modelName = basename($modelPath, '.php');
            // Remove "Model" suffix if present
            $baseName = preg_replace('/Model$/', '', $modelName);

            $repositoryCode = $this->repositoryGenerator->generate([
                'modelName' => $baseName,
                'tableName' => strtolower($baseName) . 's',
            ]);

            if (!empty($repositoryCode)) {
                $repositoryPath = APPPATH . 'Repositories' . DIRECTORY_SEPARATOR . $baseName . 'Repository.php';

                // Ensure directory exists
                $dir = dirname($repositoryPath);
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }

                file_put_contents($repositoryPath, $repositoryCode);
                $created[] = $repositoryPath;
            }
        }

        return ['created' => $created, 'modified' => []];
    }

    /**
     * Execute service generation step
     *
     * @param Module $module Module being refactored
     * @return array{created: string[], modified: string[]} Files affected
     */
    private function executeServiceGeneration(Module $module): array
    {
        $created = [];

        $controllerCode = file_get_contents($module->controllerPath);

        if ($controllerCode === false) {
            throw new RefactorException(
                "Cannot read controller file: {$module->controllerPath}",
                0,
                RefactorException::CATEGORY_REFACTOR,
                RefactorException::SEVERITY_ERROR
            );
        }

        $serviceCode = $this->serviceGenerator->generate($module->controllerPath);

        if (!empty($serviceCode)) {
            $servicePath = APPPATH . 'Services' . DIRECTORY_SEPARATOR . $module->name . 'Service.php';

            // Ensure directory exists
            $dir = dirname($servicePath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            file_put_contents($servicePath, $serviceCode);
            $created[] = $servicePath;
        }

        return ['created' => $created, 'modified' => []];
    }

    /**
     * Execute controller refactoring step
     *
     * @param Module $module Module being refactored
     * @return array{created: string[], modified: string[]} Files affected
     */
    private function executeControllerRefactoring(Module $module): array
    {
        $serviceName = $module->name . 'Service';

        $result = $this->controllerRefactorer->refactor(
            $module->controllerPath,
            $serviceName,
            ['splitWebApi' => false, 'addErrorHandling' => true]
        );

        return [
            'created' => $result->filesCreated,
            'modified' => $result->filesModified,
        ];
    }

    /**
     * Execute security fixes step
     *
     * @param Module $module Module being refactored
     * @return array{created: string[], modified: string[]} Files affected
     */
    private function executeSecurityFixes(Module $module): array
    {
        // SecurityFixer works with SecurityReport, but for the engine
        // we apply fixes directly to the controller file
        $controllerPath = $module->controllerPath;

        if (!file_exists($controllerPath)) {
            return ['created' => [], 'modified' => []];
        }

        $code = file_get_contents($controllerPath);
        if ($code === false) {
            return ['created' => [], 'modified' => []];
        }

        $modified = [];
        $originalCode = $code;

        // Apply security fixes
        $code = $this->securityFixer->addInputValidation($code);
        $code = $this->securityFixer->addOutputEscaping($code);
        $code = $this->securityFixer->addCSRFProtection($code);
        $code = $this->securityFixer->addAuthenticationCheck($code);
        $code = $this->securityFixer->replaceRawQueryWithQueryBuilder($code);

        if ($code !== $originalCode) {
            file_put_contents($controllerPath, $code);
            $modified[] = $controllerPath;
        }

        return ['created' => [], 'modified' => $modified];
    }

    /**
     * Execute Web/API controller splitting step
     *
     * @param Module $module Module being refactored
     * @return array{created: string[], modified: string[]} Files affected
     */
    private function executeWebApiSplitting(Module $module): array
    {
        $splitResult = $this->controllerSplitter->split($module->controllerPath);

        $created = [];

        if ($splitResult->wasSplit) {
            // Write web controller
            if (!empty($splitResult->webControllerCode)) {
                $webPath = $module->controllerPath;
                file_put_contents($webPath, $splitResult->webControllerCode);
                $created[] = $webPath;
            }

            // Write API controller
            if (!empty($splitResult->apiControllerCode)) {
                $apiDir = dirname($module->controllerPath) . DIRECTORY_SEPARATOR . 'Api';
                if (!is_dir($apiDir)) {
                    mkdir($apiDir, 0755, true);
                }
                $apiPath = $apiDir . DIRECTORY_SEPARATOR . $module->name . 'Controller.php';
                file_put_contents($apiPath, $splitResult->apiControllerCode);
                $created[] = $apiPath;
            }
        }

        return ['created' => $created, 'modified' => []];
    }

    /**
     * Handle a failure during refactoring
     *
     * Performs rollback if a backup exists and returns a failure result.
     *
     * @param string $errorMessage Error message describing the failure
     * @return RefactorResult Failed result with error details
     */
    private function handleFailure(string $errorMessage): RefactorResult
    {
        $this->log('error', "Refactoring failed: {$errorMessage}");

        // Attempt rollback if backup exists
        if ($this->currentBackupId !== null) {
            $this->rollback($this->currentBackupId);
        }

        $result = RefactorResult::failure($errorMessage);
        $result->backupId = $this->currentBackupId;
        $result->stepsCompleted = $this->currentResult->stepsCompleted;

        return $result;
    }

    /**
     * Log a message using the configured logger
     *
     * @param string $level Log level (info, warning, error)
     * @param string $message Log message
     * @return void
     */
    private function log(string $level, string $message): void
    {
        if ($this->logger !== null) {
            $this->logger->log($level, "[RefactorEngine] {$message}");
        }
    }
}

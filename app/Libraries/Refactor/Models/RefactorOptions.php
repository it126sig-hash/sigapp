<?php

namespace App\Libraries\Refactor\Models;

/**
 * Refactor Options Data Model
 * 
 * Configuration options for the refactoring process.
 * 
 * @package App\Libraries\Refactor\Models
 */
class RefactorOptions
{
    /**
     * Whether to create repository classes
     */
    public bool $createRepository = true;

    /**
     * Whether to create service classes
     */
    public bool $createService = true;

    /**
     * Whether to refactor the controller
     */
    public bool $refactorController = true;

    /**
     * Whether to fix security vulnerabilities
     */
    public bool $fixSecurity = true;

    /**
     * Whether to separate Web and API controllers
     */
    public bool $separateWebApi = true;

    /**
     * Whether to run tests after refactoring
     */
    public bool $runTests = false;

    /**
     * Whether to create git commits for each step
     */
    public bool $createGitCommits = true;

    /**
     * Create RefactorOptions with default values
     */
    public function __construct()
    {
        // Default values are set in property declarations
    }

    /**
     * Create options with all features enabled
     * 
     * @return self
     */
    public static function all(): self
    {
        return new self();
    }

    /**
     * Create options with minimal refactoring (no security fixes, no web/api split)
     * 
     * @return self
     */
    public static function minimal(): self
    {
        $options = new self();
        $options->fixSecurity = false;
        $options->separateWebApi = false;
        $options->createGitCommits = false;

        return $options;
    }

    /**
     * Create options for security fixes only
     * 
     * @return self
     */
    public static function securityOnly(): self
    {
        $options = new self();
        $options->createRepository = false;
        $options->createService = false;
        $options->refactorController = false;
        $options->separateWebApi = false;

        return $options;
    }

    /**
     * Convert to array representation
     * 
     * @return array<string, bool>
     */
    public function toArray(): array
    {
        return [
            'createRepository' => $this->createRepository,
            'createService' => $this->createService,
            'refactorController' => $this->refactorController,
            'fixSecurity' => $this->fixSecurity,
            'separateWebApi' => $this->separateWebApi,
            'runTests' => $this->runTests,
            'createGitCommits' => $this->createGitCommits,
        ];
    }

    /**
     * Create RefactorOptions instance from array data
     * 
     * @param array<string, bool> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $options = new self();
        $options->createRepository = $data['createRepository'] ?? true;
        $options->createService = $data['createService'] ?? true;
        $options->refactorController = $data['refactorController'] ?? true;
        $options->fixSecurity = $data['fixSecurity'] ?? true;
        $options->separateWebApi = $data['separateWebApi'] ?? true;
        $options->runTests = $data['runTests'] ?? false;
        $options->createGitCommits = $data['createGitCommits'] ?? true;

        return $options;
    }
}

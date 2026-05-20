<?php

namespace App\Libraries\Refactor\Models;

/**
 * Module Data Model
 * 
 * Represents a functional unit of the application consisting of a controller,
 * its related models, views, and business logic.
 * 
 * @package App\Libraries\Refactor\Models
 */
class Module
{
    /**
     * Module name (typically the controller name without suffix)
     */
    public string $name;

    /**
     * Absolute path to the controller file
     */
    public string $controllerPath;

    /**
     * Array of absolute paths to related model files
     * 
     * @var string[]
     */
    public array $modelPaths = [];

    /**
     * Absolute path to the service file (if exists)
     */
    public ?string $servicePath = null;

    /**
     * Absolute path to the repository file (if exists)
     */
    public ?string $repositoryPath = null;

    /**
     * Array of route definitions for this module
     * Format: ["GET /path", "POST /path/action"]
     * 
     * @var string[]
     */
    public array $routes = [];

    /**
     * Array of public method names in the controller
     * 
     * @var string[]
     */
    public array $methods = [];

    /**
     * Create a new Module instance
     * 
     * @param string $name Module name
     * @param string $controllerPath Path to controller file
     */
    public function __construct(string $name, string $controllerPath)
    {
        $this->name = $name;
        $this->controllerPath = $controllerPath;
    }

    /**
     * Convert module to array representation
     * 
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'controllerPath' => $this->controllerPath,
            'modelPaths' => $this->modelPaths,
            'servicePath' => $this->servicePath,
            'repositoryPath' => $this->repositoryPath,
            'routes' => $this->routes,
            'methods' => $this->methods,
        ];
    }

    /**
     * Create Module instance from array data
     * 
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $module = new self($data['name'], $data['controllerPath']);
        $module->modelPaths = $data['modelPaths'] ?? [];
        $module->servicePath = $data['servicePath'] ?? null;
        $module->repositoryPath = $data['repositoryPath'] ?? null;
        $module->routes = $data['routes'] ?? [];
        $module->methods = $data['methods'] ?? [];

        return $module;
    }
}

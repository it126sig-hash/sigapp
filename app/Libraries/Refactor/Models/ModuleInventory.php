<?php

namespace App\Libraries\Refactor\Models;

use DateTime;

/**
 * Module Inventory Data Model
 * 
 * Contains the complete inventory of all discovered modules and their components
 * in the CodeIgniter 4 application.
 * 
 * @package App\Libraries\Refactor\Models
 */
class ModuleInventory
{
    /**
     * Array of Module objects indexed by module name
     * 
     * @var array<string, Module>
     */
    public array $modules = [];

    /**
     * Array of all controller file paths
     * 
     * @var string[]
     */
    public array $controllers = [];

    /**
     * Array of all model file paths
     * 
     * @var string[]
     */
    public array $models = [];

    /**
     * Array of all service file paths
     * 
     * @var string[]
     */
    public array $services = [];

    /**
     * Array of all repository file paths
     * 
     * @var string[]
     */
    public array $repositories = [];

    /**
     * Timestamp when the inventory was discovered
     */
    public DateTime $discoveredAt;

    /**
     * Create a new ModuleInventory instance
     */
    public function __construct()
    {
        $this->discoveredAt = new DateTime();
    }

    /**
     * Add a module to the inventory
     * 
     * @param Module $module
     * @return void
     */
    public function addModule(Module $module): void
    {
        $this->modules[$module->name] = $module;
    }

    /**
     * Get a module by name
     * 
     * @param string $name Module name
     * @return Module|null
     */
    public function getModule(string $name): ?Module
    {
        return $this->modules[$name] ?? null;
    }

    /**
     * Get all module names
     * 
     * @return string[]
     */
    public function getModuleNames(): array
    {
        return array_keys($this->modules);
    }

    /**
     * Get total count of modules
     * 
     * @return int
     */
    public function getModuleCount(): int
    {
        return count($this->modules);
    }

    /**
     * Convert inventory to JSON string
     * 
     * @return string
     */
    public function toJson(): string
    {
        $data = [
            'discoveredAt' => $this->discoveredAt->format('c'),
            'modules' => array_map(fn($module) => $module->toArray(), $this->modules),
            'controllers' => $this->controllers,
            'models' => $this->models,
            'services' => $this->services,
            'repositories' => $this->repositories,
        ];

        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Create ModuleInventory instance from JSON string
     * 
     * @param string $json JSON string
     * @return self
     * @throws \JsonException
     */
    public static function fromJson(string $json): self
    {
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        $inventory = new self();
        $inventory->discoveredAt = new DateTime($data['discoveredAt']);
        $inventory->controllers = $data['controllers'] ?? [];
        $inventory->models = $data['models'] ?? [];
        $inventory->services = $data['services'] ?? [];
        $inventory->repositories = $data['repositories'] ?? [];

        foreach ($data['modules'] ?? [] as $moduleData) {
            $inventory->addModule(Module::fromArray($moduleData));
        }

        return $inventory;
    }
}

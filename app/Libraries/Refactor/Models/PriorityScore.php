<?php

namespace App\Libraries\Refactor\Models;

/**
 * Priority Score Data Model
 * 
 * Represents the priority score for a module in the refactoring order.
 * 
 * @package App\Libraries\Refactor\Models
 */
class PriorityScore
{
    // Module categories
    public const CATEGORY_LEAF = 'LEAF';           // No dependents
    public const CATEGORY_CORE = 'CORE';           // Many dependents
    public const CATEGORY_INTERMEDIATE = 'INTERMEDIATE'; // Some dependents

    /**
     * Module name
     */
    public string $module;

    /**
     * Calculated priority score (lower = higher priority)
     */
    public float $score;

    /**
     * Impact score (number of modules that depend on this module)
     */
    public int $impactScore;

    /**
     * Dependency depth (how deep in the dependency tree)
     */
    public int $dependencyDepth;

    /**
     * Number of critical vulnerabilities in the module
     */
    public int $criticalVulnerabilities;

    /**
     * Module category (LEAF, CORE, INTERMEDIATE)
     */
    public string $category;

    /**
     * Manual priority override (if set by user)
     */
    public ?int $manualPriority = null;

    /**
     * Create a new PriorityScore instance
     * 
     * @param string $module Module name
     * @param float $score Priority score
     * @param int $impactScore Impact score
     * @param int $dependencyDepth Dependency depth
     * @param int $criticalVulnerabilities Critical vulnerability count
     * @param string $category Module category
     */
    public function __construct(
        string $module,
        float $score,
        int $impactScore,
        int $dependencyDepth,
        int $criticalVulnerabilities,
        string $category
    ) {
        $this->module = $module;
        $this->score = $score;
        $this->impactScore = $impactScore;
        $this->dependencyDepth = $dependencyDepth;
        $this->criticalVulnerabilities = $criticalVulnerabilities;
        $this->category = $category;
    }

    /**
     * Check if this module is a leaf module
     * 
     * @return bool
     */
    public function isLeaf(): bool
    {
        return $this->category === self::CATEGORY_LEAF;
    }

    /**
     * Check if this module is a core module
     * 
     * @return bool
     */
    public function isCore(): bool
    {
        return $this->category === self::CATEGORY_CORE;
    }

    /**
     * Get the effective priority (manual override takes precedence)
     * 
     * @return float|int
     */
    public function getEffectivePriority(): float|int
    {
        return $this->manualPriority ?? $this->score;
    }

    /**
     * Set manual priority override
     * 
     * @param int|null $priority Manual priority value (null to clear override)
     * @return void
     */
    public function setManualPriority(?int $priority): void
    {
        $this->manualPriority = $priority;
    }

    /**
     * Check if manual priority override is set
     * 
     * @return bool
     */
    public function hasManualOverride(): bool
    {
        return $this->manualPriority !== null;
    }

    /**
     * Convert to array representation
     * 
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'module' => $this->module,
            'score' => $this->score,
            'impactScore' => $this->impactScore,
            'dependencyDepth' => $this->dependencyDepth,
            'criticalVulnerabilities' => $this->criticalVulnerabilities,
            'category' => $this->category,
            'manualPriority' => $this->manualPriority,
        ];
    }

    /**
     * Convert to JSON string
     * 
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Create PriorityScore instance from array data
     * 
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $score = new self(
            $data['module'],
            $data['score'],
            $data['impactScore'],
            $data['dependencyDepth'],
            $data['criticalVulnerabilities'],
            $data['category']
        );

        $score->manualPriority = $data['manualPriority'] ?? null;

        return $score;
    }

    /**
     * Create PriorityScore instance from JSON string
     * 
     * @param string $json JSON string
     * @return self
     * @throws \JsonException
     */
    public static function fromJson(string $json): self
    {
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        return self::fromArray($data);
    }
}

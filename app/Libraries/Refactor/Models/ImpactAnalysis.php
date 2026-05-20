<?php

namespace App\Libraries\Refactor\Models;

/**
 * Impact Analysis Data Model
 * 
 * Contains the results of analyzing the impact of refactoring a specific module.
 * Provides information about affected modules, routes, views, and risk assessment.
 * 
 * @package App\Libraries\Refactor\Models
 */
class ImpactAnalysis
{
    /**
     * Risk level constants
     */
    public const RISK_LOW = 'Low';
    public const RISK_MEDIUM = 'Medium';
    public const RISK_HIGH = 'High';

    /**
     * Name of the module being analyzed
     */
    public string $moduleName;

    /**
     * Array of module names that depend on this module
     * 
     * @var string[]
     */
    public array $dependentModules = [];

    /**
     * Array of route definitions that will be affected
     * Format: ["GET /path", "POST /path/action"]
     * 
     * @var string[]
     */
    public array $affectedRoutes = [];

    /**
     * Array of view file paths that may need updating
     * 
     * @var string[]
     */
    public array $affectedViews = [];

    /**
     * Estimated number of files that will be created
     */
    public int $filesWillBeCreated = 0;

    /**
     * Estimated number of files that will be modified
     */
    public int $filesWillBeModified = 0;

    /**
     * Risk assessment level (Low, Medium, High)
     */
    public string $riskLevel;

    /**
     * Array of warning messages about potential issues
     * 
     * @var string[]
     */
    public array $warnings = [];

    /**
     * Impact score from dependency graph
     */
    public int $impactScore = 0;

    /**
     * Number of security vulnerabilities in the module
     */
    public int $vulnerabilityCount = 0;

    /**
     * Number of critical security vulnerabilities
     */
    public int $criticalVulnerabilityCount = 0;

    /**
     * Create a new ImpactAnalysis instance
     * 
     * @param string $moduleName Module name
     */
    public function __construct(string $moduleName)
    {
        $this->moduleName = $moduleName;
        $this->riskLevel = self::RISK_LOW;
    }

    /**
     * Add a dependent module
     * 
     * @param string $moduleName
     * @return void
     */
    public function addDependentModule(string $moduleName): void
    {
        if (!in_array($moduleName, $this->dependentModules, true)) {
            $this->dependentModules[] = $moduleName;
        }
    }

    /**
     * Add an affected route
     * 
     * @param string $route
     * @return void
     */
    public function addAffectedRoute(string $route): void
    {
        if (!in_array($route, $this->affectedRoutes, true)) {
            $this->affectedRoutes[] = $route;
        }
    }

    /**
     * Add an affected view
     * 
     * @param string $viewPath
     * @return void
     */
    public function addAffectedView(string $viewPath): void
    {
        if (!in_array($viewPath, $this->affectedViews, true)) {
            $this->affectedViews[] = $viewPath;
        }
    }

    /**
     * Add a warning message
     * 
     * @param string $warning
     * @return void
     */
    public function addWarning(string $warning): void
    {
        if (!in_array($warning, $this->warnings, true)) {
            $this->warnings[] = $warning;
        }
    }

    /**
     * Check if the module has dependent modules
     * 
     * @return bool
     */
    public function hasDependents(): bool
    {
        return count($this->dependentModules) > 0;
    }

    /**
     * Get total number of files that will be affected
     * 
     * @return int
     */
    public function getTotalFilesAffected(): int
    {
        return $this->filesWillBeCreated + $this->filesWillBeModified;
    }

    /**
     * Check if the refactoring is high risk
     * 
     * @return bool
     */
    public function isHighRisk(): bool
    {
        return $this->riskLevel === self::RISK_HIGH;
    }

    /**
     * Check if the refactoring is medium risk
     * 
     * @return bool
     */
    public function isMediumRisk(): bool
    {
        return $this->riskLevel === self::RISK_MEDIUM;
    }

    /**
     * Check if the refactoring is low risk
     * 
     * @return bool
     */
    public function isLowRisk(): bool
    {
        return $this->riskLevel === self::RISK_LOW;
    }

    /**
     * Convert analysis to array representation
     * 
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'moduleName' => $this->moduleName,
            'dependentModules' => $this->dependentModules,
            'affectedRoutes' => $this->affectedRoutes,
            'affectedViews' => $this->affectedViews,
            'filesWillBeCreated' => $this->filesWillBeCreated,
            'filesWillBeModified' => $this->filesWillBeModified,
            'riskLevel' => $this->riskLevel,
            'warnings' => $this->warnings,
            'impactScore' => $this->impactScore,
            'vulnerabilityCount' => $this->vulnerabilityCount,
            'criticalVulnerabilityCount' => $this->criticalVulnerabilityCount,
        ];
    }

    /**
     * Convert analysis to JSON string
     * 
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Create ImpactAnalysis instance from array data
     * 
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $analysis = new self($data['moduleName']);
        $analysis->dependentModules = $data['dependentModules'] ?? [];
        $analysis->affectedRoutes = $data['affectedRoutes'] ?? [];
        $analysis->affectedViews = $data['affectedViews'] ?? [];
        $analysis->filesWillBeCreated = $data['filesWillBeCreated'] ?? 0;
        $analysis->filesWillBeModified = $data['filesWillBeModified'] ?? 0;
        $analysis->riskLevel = $data['riskLevel'] ?? self::RISK_LOW;
        $analysis->warnings = $data['warnings'] ?? [];
        $analysis->impactScore = $data['impactScore'] ?? 0;
        $analysis->vulnerabilityCount = $data['vulnerabilityCount'] ?? 0;
        $analysis->criticalVulnerabilityCount = $data['criticalVulnerabilityCount'] ?? 0;

        return $analysis;
    }

    /**
     * Create ImpactAnalysis instance from JSON string
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

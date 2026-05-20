<?php

namespace App\Libraries\Refactor\Models;

/**
 * Controller Analysis Data Model
 * 
 * Contains structural metrics and analysis results for a controller.
 * Used to understand the current state of a controller before refactoring.
 * 
 * @package App\Libraries\Refactor\Models
 */
class ControllerAnalysis
{
    /**
     * Number of methods in the controller
     */
    public int $methodCount = 0;

    /**
     * Total lines of code in the controller
     */
    public int $linesOfCode = 0;

    /**
     * Array of method details
     * Format: ['methodName' => ['lines' => int, 'complexity' => string, 'hasBusinessLogic' => bool]]
     * 
     * @var array<string, array<string, mixed>>
     */
    public array $methods = [];

    /**
     * Whether the controller has validation logic
     */
    public bool $hasValidation = false;

    /**
     * Whether the controller has business logic that should be extracted
     */
    public bool $hasBusinessLogic = false;

    /**
     * Whether the controller has direct database queries
     */
    public bool $hasDirectQueries = false;

    /**
     * Array of dependency class names used by the controller
     * 
     * @var string[]
     */
    public array $dependencies = [];

    /**
     * Number of routes handled by the controller
     */
    public int $routeCount = 0;

    /**
     * Whether the controller handles both web and API requests
     */
    public bool $hasMixedResponses = false;

    /**
     * Number of methods that return views (web responses)
     */
    public int $webMethodCount = 0;

    /**
     * Number of methods that return JSON (API responses)
     */
    public int $apiMethodCount = 0;

    /**
     * Add a method to the analysis
     * 
     * @param string $methodName Method name
     * @param int $lines Number of lines in the method
     * @param string $complexity Complexity level (Simple, Medium, Complex)
     * @param bool $hasBusinessLogic Whether the method contains business logic
     * @return void
     */
    public function addMethod(
        string $methodName,
        int $lines,
        string $complexity = 'Simple',
        bool $hasBusinessLogic = false
    ): void {
        $this->methods[$methodName] = [
            'lines' => $lines,
            'complexity' => $complexity,
            'hasBusinessLogic' => $hasBusinessLogic,
        ];
        $this->methodCount++;
    }

    /**
     * Add a dependency to the analysis
     * 
     * @param string $dependency Dependency class name
     * @return void
     */
    public function addDependency(string $dependency): void
    {
        if (!in_array($dependency, $this->dependencies, true)) {
            $this->dependencies[] = $dependency;
        }
    }

    /**
     * Get the average lines per method
     * 
     * @return float
     */
    public function getAverageLinesPerMethod(): float
    {
        if ($this->methodCount === 0) {
            return 0.0;
        }

        return round($this->linesOfCode / $this->methodCount, 2);
    }

    /**
     * Check if the controller needs refactoring
     * 
     * @return bool
     */
    public function needsRefactoring(): bool
    {
        return $this->hasBusinessLogic || $this->hasDirectQueries || $this->hasMixedResponses;
    }

    /**
     * Get refactoring complexity estimate
     * 
     * @return string Simple, Medium, or Complex
     */
    public function getRefactoringComplexity(): string
    {
        $score = 0;

        // Add points for various complexity factors
        if ($this->methodCount > 10) {
            $score += 2;
        } elseif ($this->methodCount > 5) {
            $score += 1;
        }

        if ($this->linesOfCode > 500) {
            $score += 2;
        } elseif ($this->linesOfCode > 200) {
            $score += 1;
        }

        if ($this->hasBusinessLogic) {
            $score += 1;
        }

        if ($this->hasDirectQueries) {
            $score += 1;
        }

        if ($this->hasMixedResponses) {
            $score += 1;
        }

        if (count($this->dependencies) > 5) {
            $score += 1;
        }

        // Determine complexity based on score
        if ($score >= 5) {
            return 'Complex';
        } elseif ($score >= 3) {
            return 'Medium';
        } else {
            return 'Simple';
        }
    }

    /**
     * Convert analysis to array representation
     * 
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'methodCount' => $this->methodCount,
            'linesOfCode' => $this->linesOfCode,
            'methods' => $this->methods,
            'hasValidation' => $this->hasValidation,
            'hasBusinessLogic' => $this->hasBusinessLogic,
            'hasDirectQueries' => $this->hasDirectQueries,
            'dependencies' => $this->dependencies,
            'routeCount' => $this->routeCount,
            'hasMixedResponses' => $this->hasMixedResponses,
            'webMethodCount' => $this->webMethodCount,
            'apiMethodCount' => $this->apiMethodCount,
            'averageLinesPerMethod' => $this->getAverageLinesPerMethod(),
            'needsRefactoring' => $this->needsRefactoring(),
            'refactoringComplexity' => $this->getRefactoringComplexity(),
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
     * Create ControllerAnalysis instance from array data
     * 
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $analysis = new self();
        $analysis->methodCount = $data['methodCount'] ?? 0;
        $analysis->linesOfCode = $data['linesOfCode'] ?? 0;
        $analysis->methods = $data['methods'] ?? [];
        $analysis->hasValidation = $data['hasValidation'] ?? false;
        $analysis->hasBusinessLogic = $data['hasBusinessLogic'] ?? false;
        $analysis->hasDirectQueries = $data['hasDirectQueries'] ?? false;
        $analysis->dependencies = $data['dependencies'] ?? [];
        $analysis->routeCount = $data['routeCount'] ?? 0;
        $analysis->hasMixedResponses = $data['hasMixedResponses'] ?? false;
        $analysis->webMethodCount = $data['webMethodCount'] ?? 0;
        $analysis->apiMethodCount = $data['apiMethodCount'] ?? 0;

        return $analysis;
    }

    /**
     * Create ControllerAnalysis instance from JSON string
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

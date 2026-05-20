<?php

namespace App\Libraries\Refactor\Models;

/**
 * SplitResult
 * 
 * Represents the result of splitting a controller into Web and API controllers.
 * Contains the generated code for both controllers and metadata about the split.
 * 
 * @package App\Libraries\Refactor\Models
 */
class SplitResult
{
    /**
     * @var string|null Generated web controller code
     */
    public ?string $webControllerCode = null;

    /**
     * @var string|null Generated API controller code
     */
    public ?string $apiControllerCode = null;

    /**
     * @var array<string> List of method names identified as web methods
     */
    public array $webMethods = [];

    /**
     * @var array<string> List of method names identified as API methods
     */
    public array $apiMethods = [];

    /**
     * @var bool Whether the controller was split (true) or only one type was found (false)
     */
    public bool $wasSplit = false;

    /**
     * @var string|null Original controller class name
     */
    public ?string $originalClassName = null;

    /**
     * @var string|null Original controller namespace
     */
    public ?string $originalNamespace = null;

    /**
     * @var array<string> Use statements from original controller
     */
    public array $useStatements = [];

    /**
     * Constructor
     * 
     * @param array<string, mixed> $data Initial data
     */
    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Check if web controller was generated
     * 
     * @return bool
     */
    public function hasWebController(): bool
    {
        return $this->webControllerCode !== null && !empty($this->webMethods);
    }

    /**
     * Check if API controller was generated
     * 
     * @return bool
     */
    public function hasApiController(): bool
    {
        return $this->apiControllerCode !== null && !empty($this->apiMethods);
    }

    /**
     * Get summary of the split operation
     * 
     * @return array<string, mixed>
     */
    public function getSummary(): array
    {
        return [
            'originalClass' => $this->originalClassName,
            'wasSplit' => $this->wasSplit,
            'webMethodCount' => count($this->webMethods),
            'apiMethodCount' => count($this->apiMethods),
            'webMethods' => $this->webMethods,
            'apiMethods' => $this->apiMethods,
            'hasWebController' => $this->hasWebController(),
            'hasApiController' => $this->hasApiController(),
        ];
    }

    /**
     * Convert to array
     * 
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'webControllerCode' => $this->webControllerCode,
            'apiControllerCode' => $this->apiControllerCode,
            'webMethods' => $this->webMethods,
            'apiMethods' => $this->apiMethods,
            'wasSplit' => $this->wasSplit,
            'originalClassName' => $this->originalClassName,
            'originalNamespace' => $this->originalNamespace,
            'useStatements' => $this->useStatements,
        ];
    }
}

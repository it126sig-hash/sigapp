<?php

namespace App\Libraries\Refactor\Models;

use DateTime;

/**
 * Security Report Data Model
 * 
 * Contains security vulnerability findings for a specific module.
 * 
 * @package App\Libraries\Refactor\Models
 */
class SecurityReport
{
    /**
     * Name of the module that was scanned
     */
    public string $moduleName;

    /**
     * Array of Vulnerability objects found in the module
     * 
     * @var Vulnerability[]
     */
    public array $vulnerabilities = [];

    /**
     * Timestamp when the scan was performed
     */
    public DateTime $scannedAt;

    /**
     * Create a new SecurityReport instance
     * 
     * @param string $moduleName Module name
     */
    public function __construct(string $moduleName)
    {
        $this->moduleName = $moduleName;
        $this->scannedAt = new DateTime();
    }

    /**
     * Add a vulnerability to the report
     * 
     * @param Vulnerability $vulnerability
     * @return void
     */
    public function addVulnerability(Vulnerability $vulnerability): void
    {
        $this->vulnerabilities[] = $vulnerability;
    }

    /**
     * Get vulnerabilities filtered by severity level
     * 
     * @param string $severity Severity level (CRITICAL, HIGH, MEDIUM, LOW)
     * @return Vulnerability[]
     */
    public function getBySeverity(string $severity): array
    {
        return array_filter(
            $this->vulnerabilities,
            fn($v) => $v->severity === $severity
        );
    }

    /**
     * Get count of critical vulnerabilities
     * 
     * @return int
     */
    public function getCriticalCount(): int
    {
        return count($this->getBySeverity(Vulnerability::SEVERITY_CRITICAL));
    }

    /**
     * Get count of high severity vulnerabilities
     * 
     * @return int
     */
    public function getHighCount(): int
    {
        return count($this->getBySeverity(Vulnerability::SEVERITY_HIGH));
    }

    /**
     * Get count of medium severity vulnerabilities
     * 
     * @return int
     */
    public function getMediumCount(): int
    {
        return count($this->getBySeverity(Vulnerability::SEVERITY_MEDIUM));
    }

    /**
     * Get count of low severity vulnerabilities
     * 
     * @return int
     */
    public function getLowCount(): int
    {
        return count($this->getBySeverity(Vulnerability::SEVERITY_LOW));
    }

    /**
     * Get total count of vulnerabilities
     * 
     * @return int
     */
    public function getTotalCount(): int
    {
        return count($this->vulnerabilities);
    }

    /**
     * Check if the module has any critical vulnerabilities
     * 
     * @return bool
     */
    public function hasCriticalVulnerabilities(): bool
    {
        return $this->getCriticalCount() > 0;
    }

    /**
     * Convert report to JSON string
     * 
     * @return string
     */
    public function toJson(): string
    {
        $data = [
            'moduleName' => $this->moduleName,
            'scannedAt' => $this->scannedAt->format('c'),
            'vulnerabilities' => array_map(fn($v) => $v->toArray(), $this->vulnerabilities),
        ];

        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Create SecurityReport instance from JSON string
     * 
     * @param string $json JSON string
     * @return self
     * @throws \JsonException
     */
    public static function fromJson(string $json): self
    {
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        $report = new self($data['moduleName']);
        $report->scannedAt = new DateTime($data['scannedAt']);

        foreach ($data['vulnerabilities'] ?? [] as $vulnData) {
            $report->addVulnerability(Vulnerability::fromArray($vulnData));
        }

        return $report;
    }
}

<?php

namespace App\Libraries\Refactor\Security;

use App\Libraries\Refactor\Models\Module;
use App\Libraries\Refactor\Models\SecurityReport;
use App\Libraries\Refactor\Models\Vulnerability;

/**
 * Security Scanner
 * 
 * Scans module files for security vulnerabilities using pattern matching
 * and static analysis. Generates SecurityReport objects with detected
 * vulnerabilities.
 * 
 * @package App\Libraries\Refactor\Security
 */
class SecurityScanner
{
    /**
     * Security rules for vulnerability detection
     * 
     * @var array<string, array<int, array<string, mixed>>>
     */
    private array $rules;

    /**
     * Constructor
     * 
     * @param array<string, array<int, array<string, mixed>>>|null $rules Optional custom rules
     */
    public function __construct(?array $rules = null)
    {
        $this->rules = $rules ?? SecurityRules::getAllRules();
    }

    /**
     * Scan a module for security vulnerabilities
     * 
     * @param Module $module Module to scan
     * @return SecurityReport Security report with findings
     */
    public function scanModule(Module $module): SecurityReport
    {
        $report = new SecurityReport($module->name);

        // Scan controller file
        if (!empty($module->controllerPath) && file_exists($module->controllerPath)) {
            $this->scanFile($module->controllerPath, $report);
        }

        // Scan model files
        foreach ($module->modelPaths as $modelPath) {
            if (file_exists($modelPath)) {
                $this->scanFile($modelPath, $report);
            }
        }

        // Scan service file if exists
        if (!empty($module->servicePath) && file_exists($module->servicePath)) {
            $this->scanFile($module->servicePath, $report);
        }

        // Scan repository file if exists
        if (!empty($module->repositoryPath) && file_exists($module->repositoryPath)) {
            $this->scanFile($module->repositoryPath, $report);
        }

        return $report;
    }

    /**
     * Scan a single file for all vulnerability types
     * 
     * @param string $filePath Path to file to scan
     * @param SecurityReport $report Report to add vulnerabilities to
     * @return void
     */
    private function scanFile(string $filePath, SecurityReport $report): void
    {
        $code = file_get_contents($filePath);
        if ($code === false) {
            return;
        }

        // Detect SQL injection vulnerabilities
        $sqlInjectionVulns = $this->detectSQLInjection($code, $filePath);
        foreach ($sqlInjectionVulns as $vuln) {
            $report->addVulnerability($vuln);
        }

        // Detect XSS vulnerabilities
        $xssVulns = $this->detectXSS($code, $filePath);
        foreach ($xssVulns as $vuln) {
            $report->addVulnerability($vuln);
        }

        // Detect missing CSRF protection
        $csrfVulns = $this->detectCSRFMissing($code, $filePath);
        foreach ($csrfVulns as $vuln) {
            $report->addVulnerability($vuln);
        }

        // Detect insecure authentication
        $authVulns = $this->detectInsecureAuth($code, $filePath);
        foreach ($authVulns as $vuln) {
            $report->addVulnerability($vuln);
        }

        // Detect hardcoded credentials
        $credVulns = $this->detectHardcodedCredentials($code, $filePath);
        foreach ($credVulns as $vuln) {
            $report->addVulnerability($vuln);
        }

        // Detect missing validation
        $validationVulns = $this->detectMissingValidation($code, $filePath);
        foreach ($validationVulns as $vuln) {
            $report->addVulnerability($vuln);
        }

        // Detect insecure file uploads
        $fileUploadVulns = $this->detectInsecureFileUpload($code, $filePath);
        foreach ($fileUploadVulns as $vuln) {
            $report->addVulnerability($vuln);
        }
    }

    /**
     * Detect SQL injection vulnerabilities
     * 
     * @param string $code Source code to scan
     * @param string $filePath File path for reporting
     * @return Vulnerability[]
     */
    public function detectSQLInjection(string $code, string $filePath): array
    {
        return $this->detectVulnerabilities(
            $code,
            $filePath,
            $this->rules['SQL_INJECTION'] ?? [],
            Vulnerability::TYPE_SQL_INJECTION
        );
    }

    /**
     * Detect XSS (Cross-Site Scripting) vulnerabilities
     * 
     * @param string $code Source code to scan
     * @param string $filePath File path for reporting
     * @return Vulnerability[]
     */
    public function detectXSS(string $code, string $filePath): array
    {
        return $this->detectVulnerabilities(
            $code,
            $filePath,
            $this->rules['XSS'] ?? [],
            Vulnerability::TYPE_XSS
        );
    }

    /**
     * Detect missing CSRF protection
     * 
     * @param string $code Source code to scan
     * @param string $filePath File path for reporting
     * @return Vulnerability[]
     */
    public function detectCSRFMissing(string $code, string $filePath): array
    {
        return $this->detectVulnerabilities(
            $code,
            $filePath,
            $this->rules['CSRF'] ?? [],
            Vulnerability::TYPE_CSRF
        );
    }

    /**
     * Detect insecure authentication patterns
     * 
     * @param string $code Source code to scan
     * @param string $filePath File path for reporting
     * @return Vulnerability[]
     */
    public function detectInsecureAuth(string $code, string $filePath): array
    {
        return $this->detectVulnerabilities(
            $code,
            $filePath,
            $this->rules['INSECURE_AUTH'] ?? [],
            Vulnerability::TYPE_INSECURE_AUTH
        );
    }

    /**
     * Detect hardcoded credentials
     * 
     * @param string $code Source code to scan
     * @param string $filePath File path for reporting
     * @return Vulnerability[]
     */
    public function detectHardcodedCredentials(string $code, string $filePath): array
    {
        return $this->detectVulnerabilities(
            $code,
            $filePath,
            $this->rules['HARDCODED_CREDENTIALS'] ?? [],
            Vulnerability::TYPE_HARDCODED_CREDENTIALS
        );
    }

    /**
     * Detect missing input validation
     * 
     * @param string $code Source code to scan
     * @param string $filePath File path for reporting
     * @return Vulnerability[]
     */
    public function detectMissingValidation(string $code, string $filePath): array
    {
        return $this->detectVulnerabilities(
            $code,
            $filePath,
            $this->rules['MISSING_VALIDATION'] ?? [],
            Vulnerability::TYPE_MISSING_VALIDATION
        );
    }

    /**
     * Detect insecure file upload handling
     * 
     * @param string $code Source code to scan
     * @param string $filePath File path for reporting
     * @return Vulnerability[]
     */
    public function detectInsecureFileUpload(string $code, string $filePath): array
    {
        return $this->detectVulnerabilities(
            $code,
            $filePath,
            $this->rules['INSECURE_FILE_UPLOAD'] ?? [],
            Vulnerability::TYPE_INSECURE_FILE_UPLOAD
        );
    }

    /**
     * Generic vulnerability detection using pattern matching
     * 
     * @param string $code Source code to scan
     * @param string $filePath File path for reporting
     * @param array<int, array<string, mixed>> $rules Rules to apply
     * @param string $type Vulnerability type
     * @return Vulnerability[]
     */
    private function detectVulnerabilities(
        string $code,
        string $filePath,
        array $rules,
        string $type
    ): array {
        $vulnerabilities = [];

        foreach ($rules as $rule) {
            $pattern = $rule['pattern'];
            $severity = $rule['severity'];
            $description = $rule['description'];
            $recommendation = $rule['recommendation'];

            // Suppress regex warnings and handle errors gracefully
            $matches = [];
            $result = @preg_match_all($pattern, $code, $matches, PREG_OFFSET_CAPTURE);

            // Skip if pattern is invalid or no matches found
            if ($result === false || $result === 0) {
                continue;
            }

            // Process each match
            foreach ($matches[0] as $match) {
                $matchedText = $match[0];
                $offset = $match[1];

                // Calculate line number from offset
                $lineNumber = $this->getLineNumberFromOffset($code, $offset);

                // Extract code snippet (the matched text, trimmed)
                $codeSnippet = trim($matchedText);
                if (strlen($codeSnippet) > 200) {
                    $codeSnippet = substr($codeSnippet, 0, 200) . '...';
                }

                // Create vulnerability object
                $vulnerability = new Vulnerability(
                    $type,
                    $severity,
                    $filePath,
                    $lineNumber,
                    $description,
                    $recommendation
                );
                $vulnerability->codeSnippet = $codeSnippet;

                $vulnerabilities[] = $vulnerability;
            }
        }

        return $vulnerabilities;
    }

    /**
     * Calculate line number from byte offset in code
     * 
     * @param string $code Source code
     * @param int $offset Byte offset
     * @return int Line number (1-indexed)
     */
    private function getLineNumberFromOffset(string $code, int $offset): int
    {
        $codeBeforeMatch = substr($code, 0, $offset);
        return substr_count($codeBeforeMatch, "\n") + 1;
    }

    /**
     * Get all security rules
     * 
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    /**
     * Set custom security rules
     * 
     * @param array<string, array<int, array<string, mixed>>> $rules
     * @return void
     */
    public function setRules(array $rules): void
    {
        $this->rules = $rules;
    }
}

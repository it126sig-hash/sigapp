<?php

namespace App\Libraries\Refactor\Security;

/**
 * Security Rule Definitions
 * 
 * This class defines regex patterns and detection rules for identifying
 * common security vulnerabilities in CodeIgniter 4 code.
 * 
 * Each rule includes:
 * - Pattern: Regex pattern to match vulnerable code
 * - Severity: CRITICAL, HIGH, MEDIUM, or LOW
 * - Description: Human-readable description of the vulnerability
 * - Recommendation: How to fix the vulnerability
 * 
 * @package App\Libraries\Refactor\Security
 */
class SecurityRules
{
    /**
     * Get all security rules organized by vulnerability type
     * 
     * @return array<string, array<int, array<string, mixed>>>
     */
    public static function getAllRules(): array
    {
        return [
            'SQL_INJECTION' => self::getSQLInjectionRules(),
            'XSS' => self::getXSSRules(),
            'CSRF' => self::getCSRFRules(),
            'INSECURE_AUTH' => self::getInsecureAuthRules(),
            'HARDCODED_CREDENTIALS' => self::getHardcodedCredentialsRules(),
            'MISSING_VALIDATION' => self::getMissingValidationRules(),
            'INSECURE_FILE_UPLOAD' => self::getInsecureFileUploadRules(),
        ];
    }

    /**
     * SQL Injection Detection Rules
     * 
     * Detects patterns where user input is directly concatenated into SQL queries
     * without proper parameter binding or escaping.
     * 
     * @return array<int, array<string, mixed>>
     */
    public static function getSQLInjectionRules(): array
    {
        return [
            [
                'pattern' => '/\$this->db->query\s*\(\s*["\'].*?\$.*?["\']\s*\)/s',
                'severity' => 'CRITICAL',
                'description' => 'Raw SQL query with variable concatenation detected',
                'recommendation' => 'Use Query Builder with parameter binding: $this->db->table()->where()->get()',
            ],
            [
                'pattern' => '/\$this->db->query\s*\(\s*["\'].*?\..*?\$.*?\..*?["\']\s*\)/s',
                'severity' => 'CRITICAL',
                'description' => 'Raw SQL query with string concatenation detected',
                'recommendation' => 'Use Query Builder with parameter binding instead of concatenating variables',
            ],
            [
                'pattern' => '/\$db->query\s*\(\s*["\'].*?\$.*?["\']\s*\)/s',
                'severity' => 'CRITICAL',
                'description' => 'Raw SQL query with variable interpolation detected',
                'recommendation' => 'Use Query Builder or prepared statements with parameter binding',
            ],
            [
                'pattern' => '/\$builder->where\s*\(\s*["\'].*?\$.*?["\']\s*\)/s',
                'severity' => 'HIGH',
                'description' => 'Query Builder where clause with variable interpolation',
                'recommendation' => 'Use parameter binding: $builder->where(\'field\', $value)',
            ],
            [
                'pattern' => '/\$builder->whereIn\s*\(\s*["\'][^"\']*["\']\s*,\s*\$.*?\)/s',
                'severity' => 'MEDIUM',
                'description' => 'Query Builder whereIn with unsanitized array',
                'recommendation' => 'Ensure array values are validated before use in whereIn()',
            ],
            [
                'pattern' => '/\$this->db->escape\s*\(\s*\$.*?\)/s',
                'severity' => 'MEDIUM',
                'description' => 'Manual escaping detected - may indicate raw query usage',
                'recommendation' => 'Prefer Query Builder which handles escaping automatically',
            ],
            [
                'pattern' => '/SELECT\s+.*?\s+FROM\s+.*?\s+WHERE\s+.*?\$.*?["\']?\s*;/is',
                'severity' => 'CRITICAL',
                'description' => 'Direct SQL SELECT statement with variable interpolation',
                'recommendation' => 'Use Query Builder: $this->db->table()->where()->get()',
            ],
            [
                'pattern' => '/INSERT\s+INTO\s+.*?\s+VALUES\s*\(.*?\$.*?\)/is',
                'severity' => 'CRITICAL',
                'description' => 'Direct SQL INSERT statement with variable interpolation',
                'recommendation' => 'Use Query Builder: $this->db->table()->insert($data)',
            ],
            [
                'pattern' => '/UPDATE\s+.*?\s+SET\s+.*?\$.*?\s+WHERE/is',
                'severity' => 'CRITICAL',
                'description' => 'Direct SQL UPDATE statement with variable interpolation',
                'recommendation' => 'Use Query Builder: $this->db->table()->where()->update($data)',
            ],
            [
                'pattern' => '/DELETE\s+FROM\s+.*?\s+WHERE\s+.*?\$.*?["\']?/is',
                'severity' => 'CRITICAL',
                'description' => 'Direct SQL DELETE statement with variable interpolation',
                'recommendation' => 'Use Query Builder: $this->db->table()->where()->delete()',
            ],
        ];
    }

    /**
     * XSS (Cross-Site Scripting) Detection Rules
     * 
     * Detects patterns where user input is rendered in views without proper escaping.
     * 
     * @return array<int, array<string, mixed>>
     */
    public static function getXSSRules(): array
    {
        return [
            [
                'pattern' => '/echo\s+\$(?!this->)(?!esc\().*?;/s',
                'severity' => 'HIGH',
                'description' => 'Unescaped echo statement detected',
                'recommendation' => 'Use esc() helper: echo esc($variable)',
            ],
            [
                'pattern' => '/\<\?=\s*\$(?!esc\().*?\?\>/s',
                'severity' => 'HIGH',
                'description' => 'Unescaped short echo tag detected',
                'recommendation' => 'Use esc() helper: <?= esc($variable) ?>',
            ],
            [
                'pattern' => '/print\s+\$(?!esc\().*?;/s',
                'severity' => 'HIGH',
                'description' => 'Unescaped print statement detected',
                'recommendation' => 'Use esc() helper: print esc($variable)',
            ],
            [
                'pattern' => '/\$this->response->setJSON\s*\(\s*\$(?!this->).*?\)/s',
                'severity' => 'MEDIUM',
                'description' => 'JSON response with potentially unvalidated data',
                'recommendation' => 'Ensure data is validated and sanitized before JSON encoding',
            ],
            [
                'pattern' => '/innerHTML\s*=\s*["\'].*?\$.*?["\']/s',
                'severity' => 'HIGH',
                'description' => 'JavaScript innerHTML with variable interpolation',
                'recommendation' => 'Use textContent or properly escape HTML entities',
            ],
            [
                'pattern' => '/document\.write\s*\(.*?\$.*?\)/s',
                'severity' => 'HIGH',
                'description' => 'JavaScript document.write with variable interpolation',
                'recommendation' => 'Avoid document.write and use safe DOM manipulation methods',
            ],
            [
                'pattern' => '/\$this->request->getVar\s*\([^)]+\)(?!.*?esc\()/s',
                'severity' => 'MEDIUM',
                'description' => 'Request variable used without escaping',
                'recommendation' => 'Always escape user input before output: esc($this->request->getVar())',
            ],
            [
                'pattern' => '/\$this->request->getPost\s*\([^)]+\)(?!.*?esc\()/s',
                'severity' => 'MEDIUM',
                'description' => 'POST data used without escaping',
                'recommendation' => 'Always escape user input before output: esc($this->request->getPost())',
            ],
        ];
    }

    /**
     * CSRF (Cross-Site Request Forgery) Detection Rules
     * 
     * Detects forms and POST endpoints without CSRF protection.
     * 
     * @return array<int, array<string, mixed>>
     */
    public static function getCSRFRules(): array
    {
        return [
            [
                'pattern' => '/\<form[^>]*method\s*=\s*["\']post["\'][^>]*\>(?!.*?csrf_field\(\))/is',
                'severity' => 'HIGH',
                'description' => 'POST form without CSRF token detected',
                'recommendation' => 'Add <?= csrf_field() ?> inside the form or use form_open() helper',
            ],
            [
                'pattern' => '/\<form[^>]*\>(?!.*?csrf_field\(\))(?!.*?form_open\()/is',
                'severity' => 'MEDIUM',
                'description' => 'Form without CSRF protection detected',
                'recommendation' => 'Use form_open() helper or add <?= csrf_field() ?> manually',
            ],
            [
                'pattern' => '/public\s+function\s+\w+\s*\([^)]*\)\s*:\s*ResponseInterface\s*\{(?!.*?csrf_hash\(\))/s',
                'severity' => 'MEDIUM',
                'description' => 'Controller method returning response without CSRF token refresh',
                'recommendation' => 'Include csrf_hash() in JSON responses: [\'token\' => csrf_hash()]',
            ],
            [
                'pattern' => '/\$this->request->getPost\s*\((?!.*?csrf_test_name)/s',
                'severity' => 'LOW',
                'description' => 'POST request handling without explicit CSRF validation',
                'recommendation' => 'Ensure CSRF filter is enabled in app/Config/Filters.php',
            ],
            [
                'pattern' => '/fetch\s*\([^)]*method\s*:\s*["\']POST["\'](?!.*?X-CSRF-TOKEN)/is',
                'severity' => 'HIGH',
                'description' => 'JavaScript fetch POST request without CSRF token',
                'recommendation' => 'Include CSRF token in headers: headers: {\'X-CSRF-TOKEN\': token}',
            ],
            [
                'pattern' => '/\$\.ajax\s*\(\s*\{[^}]*type\s*:\s*["\']POST["\'](?!.*?X-CSRF-TOKEN)/is',
                'severity' => 'HIGH',
                'description' => 'jQuery AJAX POST request without CSRF token',
                'recommendation' => 'Include CSRF token in headers or data',
            ],
        ];
    }

    /**
     * Insecure Authentication Detection Rules
     * 
     * Detects weak authentication patterns and missing authorization checks.
     * 
     * @return array<int, array<string, mixed>>
     */
    public static function getInsecureAuthRules(): array
    {
        return [
            [
                'pattern' => '/password\s*==\s*\$.*?password/i',
                'severity' => 'CRITICAL',
                'description' => 'Plain text password comparison detected',
                'recommendation' => 'Use password_verify() for password comparison',
            ],
            [
                'pattern' => '/md5\s*\(\s*\$.*?password.*?\)/i',
                'severity' => 'CRITICAL',
                'description' => 'MD5 password hashing detected (insecure)',
                'recommendation' => 'Use password_hash() with PASSWORD_DEFAULT or PASSWORD_ARGON2ID',
            ],
            [
                'pattern' => '/sha1\s*\(\s*\$.*?password.*?\)/i',
                'severity' => 'CRITICAL',
                'description' => 'SHA1 password hashing detected (insecure)',
                'recommendation' => 'Use password_hash() with PASSWORD_DEFAULT or PASSWORD_ARGON2ID',
            ],
            [
                'pattern' => '/\$_SESSION\s*\[\s*["\']user.*?["\']\s*\]\s*=\s*\$.*?(?!password_verify)/i',
                'severity' => 'HIGH',
                'description' => 'Session assignment without proper authentication',
                'recommendation' => 'Verify credentials with password_verify() before setting session',
            ],
            [
                'pattern' => '/public\s+function\s+(?!__construct|index|login|logout)\w+\s*\([^)]*\)(?!.*?logged_in\(\))/s',
                'severity' => 'MEDIUM',
                'description' => 'Controller method without authentication check',
                'recommendation' => 'Add authentication check or use auth filter in routes',
            ],
            [
                'pattern' => '/if\s*\(\s*\$_SESSION\s*\[\s*["\']logged_in["\']\s*\]\s*\)/i',
                'severity' => 'MEDIUM',
                'description' => 'Direct session check instead of using helper',
                'recommendation' => 'Use logged_in() helper or session()->get() method',
            ],
            [
                'pattern' => '/\$this->session->set\s*\(\s*["\']user_id["\']\s*,\s*\$.*?\)(?!.*?password_verify)/s',
                'severity' => 'HIGH',
                'description' => 'User ID stored in session without authentication',
                'recommendation' => 'Verify credentials before storing user information in session',
            ],
            [
                'pattern' => '/public\s+function\s+delete\w*\s*\([^)]*\)(?!.*?has_permission\(\))/s',
                'severity' => 'HIGH',
                'description' => 'Delete operation without authorization check',
                'recommendation' => 'Add authorization check before delete operations',
            ],
            [
                'pattern' => '/public\s+function\s+update\w*\s*\([^)]*\)(?!.*?has_permission\(\))/s',
                'severity' => 'MEDIUM',
                'description' => 'Update operation without authorization check',
                'recommendation' => 'Add authorization check before update operations',
            ],
        ];
    }

    /**
     * Hardcoded Credentials Detection Rules
     * 
     * Detects hardcoded passwords, API keys, and other sensitive data.
     * 
     * @return array<int, array<string, mixed>>
     */
    public static function getHardcodedCredentialsRules(): array
    {
        return [
            [
                'pattern' => '/password\s*=\s*["\'][^"\']{6,}["\']/i',
                'severity' => 'CRITICAL',
                'description' => 'Hardcoded password detected',
                'recommendation' => 'Move password to .env file and use env() helper',
            ],
            [
                'pattern' => '/api[_-]?key\s*=\s*["\'][^"\']{10,}["\']/i',
                'severity' => 'CRITICAL',
                'description' => 'Hardcoded API key detected',
                'recommendation' => 'Move API key to .env file and use env() helper',
            ],
            [
                'pattern' => '/secret[_-]?key\s*=\s*["\'][^"\']{10,}["\']/i',
                'severity' => 'CRITICAL',
                'description' => 'Hardcoded secret key detected',
                'recommendation' => 'Move secret key to .env file and use env() helper',
            ],
            [
                'pattern' => '/token\s*=\s*["\'][a-zA-Z0-9]{20,}["\']/i',
                'severity' => 'HIGH',
                'description' => 'Hardcoded token detected',
                'recommendation' => 'Move token to .env file and use env() helper',
            ],
            [
                'pattern' => '/private[_-]?key\s*=\s*["\'][^"\']{10,}["\']/i',
                'severity' => 'CRITICAL',
                'description' => 'Hardcoded private key detected',
                'recommendation' => 'Move private key to .env file and use env() helper',
            ],
            [
                'pattern' => '/database\s*=\s*["\'][^"\']+["\']\s*;\s*\$username\s*=\s*["\'][^"\']+["\']\s*;\s*\$password\s*=\s*["\'][^"\']+["\']/is',
                'severity' => 'CRITICAL',
                'description' => 'Hardcoded database credentials detected',
                'recommendation' => 'Use database configuration in app/Config/Database.php',
            ],
            [
                'pattern' => '/\$.*?password.*?\s*=\s*["\'][^"\']{6,}["\']/i',
                'severity' => 'HIGH',
                'description' => 'Variable with password value hardcoded',
                'recommendation' => 'Move password to .env file and use env() helper',
            ],
            [
                'pattern' => '/define\s*\(\s*["\'].*?(?:PASSWORD|API_KEY|SECRET).*?["\']\s*,\s*["\'][^"\']+["\']\s*\)/i',
                'severity' => 'CRITICAL',
                'description' => 'Hardcoded credential in constant definition',
                'recommendation' => 'Move credential to .env file and use env() helper',
            ],
            [
                'pattern' => '/aws[_-]?(?:access[_-]?key|secret)\s*=\s*["\'][^"\']{10,}["\']/i',
                'severity' => 'CRITICAL',
                'description' => 'Hardcoded AWS credentials detected',
                'recommendation' => 'Move AWS credentials to .env file and use env() helper',
            ],
        ];
    }

    /**
     * Missing Input Validation Detection Rules
     * 
     * Detects controller methods that process user input without validation.
     * 
     * @return array<int, array<string, mixed>>
     */
    public static function getMissingValidationRules(): array
    {
        return [
            [
                'pattern' => '/public\s+function\s+(?:save|create|store|update|edit)\w*\s*\([^)]*\)(?!.*?\$this->validate\()/s',
                'severity' => 'HIGH',
                'description' => 'Data modification method without validation',
                'recommendation' => 'Add validation using $this->validate() before processing data',
            ],
            [
                'pattern' => '/\$this->request->getPost\s*\([^)]+\)(?!.*?\$this->validate\()(?!.*?->setRules\()/s',
                'severity' => 'MEDIUM',
                'description' => 'POST data retrieved without validation',
                'recommendation' => 'Validate POST data using $this->validate() or validation service',
            ],
            [
                'pattern' => '/\$this->request->getVar\s*\([^)]+\)(?!.*?filter_var\()(?!.*?\$this->validate\()/s',
                'severity' => 'MEDIUM',
                'description' => 'Request variable retrieved without validation',
                'recommendation' => 'Validate input using $this->validate() or filter_var()',
            ],
            [
                'pattern' => '/\$this->request->getJSON\s*\([^)]*\)(?!.*?\$this->validate\()/s',
                'severity' => 'MEDIUM',
                'description' => 'JSON data retrieved without validation',
                'recommendation' => 'Validate JSON data using $this->validate() with rules',
            ],
            [
                'pattern' => '/->insert\s*\(\s*\$(?!validated).*?\)/s',
                'severity' => 'HIGH',
                'description' => 'Database insert without validated data',
                'recommendation' => 'Validate data before insert: $this->validate() then use validated data',
            ],
            [
                'pattern' => '/->update\s*\(\s*\$(?!validated).*?\)/s',
                'severity' => 'HIGH',
                'description' => 'Database update without validated data',
                'recommendation' => 'Validate data before update: $this->validate() then use validated data',
            ],
            [
                'pattern' => '/\$_GET\s*\[/i',
                'severity' => 'HIGH',
                'description' => 'Direct $_GET superglobal usage detected',
                'recommendation' => 'Use $this->request->getGet() with validation',
            ],
            [
                'pattern' => '/\$_POST\s*\[/i',
                'severity' => 'HIGH',
                'description' => 'Direct $_POST superglobal usage detected',
                'recommendation' => 'Use $this->request->getPost() with validation',
            ],
            [
                'pattern' => '/\$_REQUEST\s*\[/i',
                'severity' => 'HIGH',
                'description' => 'Direct $_REQUEST superglobal usage detected',
                'recommendation' => 'Use $this->request->getVar() with validation',
            ],
        ];
    }

    /**
     * Insecure File Upload Detection Rules
     * 
     * Detects file upload handling without proper security checks.
     * 
     * @return array<int, array<string, mixed>>
     */
    public static function getInsecureFileUploadRules(): array
    {
        return [
            [
                'pattern' => '/\$this->request->getFile\s*\([^)]+\)(?!.*?isValid\(\))(?!.*?getExtension\(\))/s',
                'severity' => 'HIGH',
                'description' => 'File upload without validation',
                'recommendation' => 'Validate file using isValid(), getExtension(), and getMimeType()',
            ],
            [
                'pattern' => '/move_uploaded_file\s*\(/i',
                'severity' => 'HIGH',
                'description' => 'Direct move_uploaded_file usage detected',
                'recommendation' => 'Use CodeIgniter file upload: $file->move() with validation',
            ],
            [
                'pattern' => '/\$file->move\s*\([^)]*\)(?!.*?getExtension\(\))/s',
                'severity' => 'HIGH',
                'description' => 'File moved without extension validation',
                'recommendation' => 'Validate file extension before moving: $file->getExtension()',
            ],
            [
                'pattern' => '/\$file->move\s*\([^)]*\)(?!.*?getMimeType\(\))/s',
                'severity' => 'MEDIUM',
                'description' => 'File moved without MIME type validation',
                'recommendation' => 'Validate MIME type before moving: $file->getMimeType()',
            ],
            [
                'pattern' => '/\$file->move\s*\([^)]*\)(?!.*?getSize\(\))/s',
                'severity' => 'MEDIUM',
                'description' => 'File moved without size validation',
                'recommendation' => 'Validate file size before moving: $file->getSize()',
            ],
            [
                'pattern' => '/\$_FILES\s*\[/i',
                'severity' => 'HIGH',
                'description' => 'Direct $_FILES superglobal usage detected',
                'recommendation' => 'Use $this->request->getFile() with proper validation',
            ],
            [
                'pattern' => '/\$file->getName\s*\(\)(?!.*?getRandomName\(\))/s',
                'severity' => 'MEDIUM',
                'description' => 'Original filename used without sanitization',
                'recommendation' => 'Use $file->getRandomName() to prevent directory traversal',
            ],
            [
                'pattern' => '/\$file->move\s*\([^)]*\)(?!.*?(?:jpg|jpeg|png|gif|pdf|doc|docx|xls|xlsx))/is',
                'severity' => 'LOW',
                'description' => 'File upload without explicit allowed extensions',
                'recommendation' => 'Define and validate allowed file extensions explicitly',
            ],
            [
                'pattern' => '/\$file->move\s*\([^)]*WRITEPATH.*?\)/s',
                'severity' => 'LOW',
                'description' => 'File uploaded to writable path',
                'recommendation' => 'Ensure uploaded files are stored outside web root or with proper access controls',
            ],
        ];
    }

    /**
     * Get severity level numeric value for sorting
     * 
     * @param string $severity
     * @return int
     */
    public static function getSeverityLevel(string $severity): int
    {
        return match (strtoupper($severity)) {
            'CRITICAL' => 4,
            'HIGH' => 3,
            'MEDIUM' => 2,
            'LOW' => 1,
            default => 0,
        };
    }

    /**
     * Get all vulnerability types
     * 
     * @return array<int, string>
     */
    public static function getVulnerabilityTypes(): array
    {
        return [
            'SQL_INJECTION',
            'XSS',
            'CSRF',
            'INSECURE_AUTH',
            'HARDCODED_CREDENTIALS',
            'MISSING_VALIDATION',
            'INSECURE_FILE_UPLOAD',
        ];
    }

    /**
     * Get all severity levels
     * 
     * @return array<int, string>
     */
    public static function getSeverityLevels(): array
    {
        return [
            'CRITICAL',
            'HIGH',
            'MEDIUM',
            'LOW',
        ];
    }
}

<?php

namespace App\Libraries\Refactor\Execution;

use App\Libraries\Refactor\Generation\CodeGenerator;
use App\Libraries\Refactor\Generation\QueryAnalyzer;
use App\Libraries\Refactor\Models\RefactorResult;
use App\Libraries\Refactor\Models\SecurityReport;
use App\Libraries\Refactor\Models\Vulnerability;

/**
 * SecurityFixer
 *
 * Implements security fixes for identified vulnerabilities in CodeIgniter 4 code.
 * Handles SQL injection prevention, XSS output escaping, CSRF protection,
 * authentication/authorization checks, input validation, and file upload security.
 *
 * Integrates with BackupManager for safe operations and uses QueryAnalyzer
 * for converting raw SQL queries to Query Builder syntax.
 *
 * @package App\Libraries\Refactor\Execution
 */
class SecurityFixer
{
    /**
     * @var CodeGenerator Generator for creating PHP code
     */
    private CodeGenerator $codeGenerator;

    /**
     * @var QueryAnalyzer Analyzer for SQL query conversion
     */
    private QueryAnalyzer $queryAnalyzer;

    /**
     * @var BackupManager Manager for backup/rollback operations
     */
    private BackupManager $backupManager;

    /**
     * Constructor
     *
     * @param CodeGenerator|null $codeGenerator Optional CodeGenerator instance
     * @param QueryAnalyzer|null $queryAnalyzer Optional QueryAnalyzer instance
     * @param BackupManager|null $backupManager Optional BackupManager instance
     */
    public function __construct(
        ?CodeGenerator $codeGenerator = null,
        ?QueryAnalyzer $queryAnalyzer = null,
        ?BackupManager $backupManager = null
    ) {
        $this->codeGenerator = $codeGenerator ?? new CodeGenerator();
        $this->queryAnalyzer = $queryAnalyzer ?? new QueryAnalyzer();
        $this->backupManager = $backupManager ?? new BackupManager();
    }

    /**
     * Apply security fixes based on a SecurityReport
     *
     * Creates a backup before applying fixes and returns a RefactorResult
     * with details of all changes made.
     *
     * @param SecurityReport $report Security report with vulnerabilities to fix
     * @param array<string, mixed> $options Fix options (createBackup, dryRun)
     * @return RefactorResult Result of the fix operation
     */
    public function fix(SecurityReport $report, array $options = []): RefactorResult
    {
        $options = array_merge([
            'createBackup' => true,
            'dryRun' => false,
        ], $options);

        if (empty($report->vulnerabilities)) {
            return RefactorResult::success();
        }

        try {
            // Collect unique file paths from vulnerabilities
            $filePaths = $this->collectFilePaths($report);

            // Create backup if requested
            $backupId = null;
            if ($options['createBackup'] && !$options['dryRun']) {
                $backupId = $this->backupManager->createBackup(
                    $filePaths,
                    $report->moduleName,
                    'Security fix backup'
                );
            }

            $result = RefactorResult::success();
            $result->backupId = $backupId;

            // Group vulnerabilities by file
            $vulnerabilitiesByFile = $this->groupVulnerabilitiesByFile($report);

            // Apply fixes for each file
            foreach ($vulnerabilitiesByFile as $filePath => $vulnerabilities) {
                if (!file_exists($filePath) && !$options['dryRun']) {
                    continue;
                }

                $code = $options['dryRun'] ? '' : file_get_contents($filePath);
                if ($code === false) {
                    continue;
                }

                $modified = false;

                foreach ($vulnerabilities as $vulnerability) {
                    $fixedCode = $this->applyFix($code, $vulnerability);
                    if ($fixedCode !== $code) {
                        $code = $fixedCode;
                        $modified = true;
                    }
                }

                if ($modified && !$options['dryRun']) {
                    file_put_contents($filePath, $code);
                    $result->addModifiedFile($filePath);
                }
            }

            // Record completed steps
            $fixedTypes = $this->getFixedVulnerabilityTypes($report);
            foreach ($fixedTypes as $type) {
                $result->addCompletedStep('Fixed ' . $type . ' vulnerabilities');
            }

            return $result;
        } catch (\Exception $e) {
            return RefactorResult::failure('Security fix failed: ' . $e->getMessage());
        }
    }

    /**
     * Apply a single fix based on vulnerability type
     *
     * @param string $code Source code to fix
     * @param Vulnerability $vulnerability Vulnerability to fix
     * @return string Fixed code
     */
    private function applyFix(string $code, Vulnerability $vulnerability): string
    {
        return match ($vulnerability->type) {
            Vulnerability::TYPE_SQL_INJECTION => $this->replaceRawQueryWithQueryBuilder($code, $vulnerability),
            Vulnerability::TYPE_XSS => $this->addOutputEscaping($code, $vulnerability),
            Vulnerability::TYPE_CSRF => $this->addCSRFProtection($code, $vulnerability),
            Vulnerability::TYPE_INSECURE_AUTH => $this->addAuthenticationCheck($code, $vulnerability),
            Vulnerability::TYPE_MISSING_VALIDATION => $this->addInputValidation($code, $vulnerability),
            Vulnerability::TYPE_INSECURE_FILE_UPLOAD => $this->addFileUploadValidation($code, $vulnerability),
            default => $code,
        };
    }

    /**
     * Add CSRF protection to form submissions
     *
     * Adds csrf_field() to forms and ensures CSRF filter is referenced.
     *
     * @param string $code Source code
     * @param Vulnerability|null $vulnerability Optional vulnerability context
     * @return string Code with CSRF protection added
     */
    public function addCSRFProtection(string $code, ?Vulnerability $vulnerability = null): string
    {
        // Pattern 1: Add csrf_field() after <form> tags that don't have it
        $csrfInsert = "\n    " . '<' . '?= csrf_field() ?' . '>';
        $code = preg_replace_callback(
            '/(<form[^>]*>)(?!\s*<\?=\s*csrf_field\(\))/',
            function ($matches) use ($csrfInsert) {
                return $matches[1] . $csrfInsert;
            },
            $code
        );

        // Pattern 2: For controller code, add CSRF filter comment if missing
        if (str_contains($code, 'namespace App\\Controllers')) {
            if (!str_contains($code, 'csrf') && !str_contains($code, 'CSRF')) {
                $code = preg_replace(
                    '/(class\s+\w+[^{]*\{)/',
                    "$1\n    // NOTE: Ensure CSRF filter is applied via app/Config/Filters.php or route-level filter",
                    $code,
                    1
                );
            }
        }

        return $code;
    }

    /**
     * Add input validation using CodeIgniter 4 validation rules
     *
     * Adds validation logic to controller methods that accept user input
     * without proper validation.
     *
     * @param string $code Source code
     * @param Vulnerability|null $vulnerability Optional vulnerability context
     * @param array<string, string> $rules Optional validation rules to apply
     * @return string Code with input validation added
     */
    public function addInputValidation(string $code, ?Vulnerability $vulnerability = null, array $rules = []): string
    {
        // Find methods that use $this->request->getPost() or getVar() without validation
        $pattern = '/(\$\w+\s*=\s*\$this->request->(?:getPost|getVar|getGet)\s*\([\'"](\w+)[\'"]\)\s*;)/';
        $code = preg_replace_callback(
            $pattern,
            function ($matches) use ($rules) {
                $originalLine = $matches[1];
                $fieldName = $matches[2];

                // Check if validation already exists nearby
                if (str_contains($originalLine, 'validate') || str_contains($originalLine, 'getValidated')) {
                    return $originalLine;
                }

                // Determine validation rule based on field name or provided rules
                $rule = $rules[$fieldName] ?? $this->inferValidationRule($fieldName);

                $validationCode = "\n        // Validate input\n";
                $validationCode .= "        if (!\$this->validate(['" . $fieldName . "' => '" . $rule . "'])) {\n";
                $validationCode .= "            return redirect()->back()->withInput()->with('errors', \$this->validator->getErrors());\n";
                $validationCode .= "        }\n        ";

                return $validationCode . $originalLine;
            },
            $code
        );

        return $code;
    }

    /**
     * Add output escaping in views to prevent XSS
     *
     * Wraps unescaped output variables with esc() function.
     *
     * @param string $code View or controller code
     * @param Vulnerability|null $vulnerability Optional vulnerability context
     * @return string Code with output escaping added
     */
    public function addOutputEscaping(string $code, ?Vulnerability $vulnerability = null): string
    {
        // Pattern 1: Replace <?= $variable with <?= esc($variable)
        $closeTag = '?' . '>';
        $openEchoTag = '<' . '?= ';
        $pattern1 = "/<\\?=\\s*(\\$\\w+(?:->\\w+|\\['\\w+'\\])*)\\s*\\?" . ">/";
        $code = preg_replace_callback(
            $pattern1,
            function ($matches) use ($closeTag, $openEchoTag) {
                $variable = $matches[1];

                // Skip if already escaped
                if (str_contains($matches[0], 'esc(')) {
                    return $matches[0];
                }

                return $openEchoTag . 'esc(' . $variable . ') ' . $closeTag;
            },
            $code
        );

        // Pattern 2: Replace echo $variable with echo esc($variable)
        $pattern2 = "/\\becho\\s+(\\$\\w+(?:->\\w+|\\['\\w+'\\])*)\\s*;/";
        $code = preg_replace_callback(
            $pattern2,
            function ($matches) {
                $variable = $matches[1];

                // Skip if already escaped
                if (str_contains($matches[0], 'esc(')) {
                    return $matches[0];
                }

                return 'echo esc(' . $variable . ');';
            },
            $code
        );

        // Pattern 3: Handle variables in double-quoted strings in view context
        // This is complex and risky to auto-fix, so we add a comment instead
        if (preg_match('/["\'][^"\']*\$\w+[^"\']*["\']/', $code) && str_contains($code, 'view(')) {
            if (!str_contains($code, '// WARNING: Ensure output escaping')) {
                $code = preg_replace(
                    '/(return\s+view\s*\()/',
                    "// WARNING: Ensure output escaping for all user-provided data passed to views\n        $1",
                    $code,
                    1
                );
            }
        }

        return $code;
    }

    /**
     * Replace raw SQL queries with Query Builder syntax
     *
     * Uses QueryAnalyzer to convert raw SQL to safe Query Builder calls.
     *
     * @param string $code Source code containing raw queries
     * @param Vulnerability|null $vulnerability Optional vulnerability context
     * @return string Code with Query Builder replacements
     */
    public function replaceRawQueryWithQueryBuilder(string $code, ?Vulnerability $vulnerability = null): string
    {
        // Pattern 1: Replace $this->db->query("SELECT ...") with Query Builder
        $pattern1 = '/\$this->db->query\s*\(\s*["\']([^"\']+)["\']\s*\)/';
        $code = preg_replace_callback(
            $pattern1,
            function ($matches) {
                $rawQuery = $matches[1];

                try {
                    $queryBuilderCode = $this->queryAnalyzer->convertToQueryBuilder($rawQuery, '$this->db');
                    return $queryBuilderCode . '->get()';
                } catch (\Exception $e) {
                    // If conversion fails, add parameter binding instead
                    return $matches[0] . ' /* TODO: Convert to Query Builder */';
                }
            },
            $code
        );

        // Pattern 2: Replace string concatenation in queries
        $pattern2 = '/\$this->db->query\s*\(\s*["\']([^"\']*)["\'](?:\s*\.\s*\$(\w+)(?:\s*\.\s*["\']([^"\']*)["\'])*)+\s*\)/';
        $code = preg_replace_callback(
            $pattern2,
            function ($matches) {
                $fullMatch = $matches[0];

                // Extract the query parts and variables
                if (preg_match_all('/\$(\w+)/', $fullMatch, $varMatches)) {
                    $variables = $varMatches[1];
                    // Filter out 'this' from variables
                    $variables = array_filter($variables, fn($v) => $v !== 'this');

                    if (!empty($variables)) {
                        // Convert to parameterized query
                        $bindingCode = $this->queryAnalyzer->generateParameterBinding(array_values($variables));
                        $comment = "// TODO: Refactor to Query Builder for full SQL injection protection\n        ";
                        $comment .= "// Use parameter binding: " . $bindingCode;

                        return $comment . "\n        " . $fullMatch;
                    }
                }

                return $fullMatch;
            },
            $code
        );

        // Pattern 3: Replace direct variable interpolation in query strings
        $pattern3 = '/(\$this->db->query\s*\(\s*")([^"]*\$\w+[^"]*)("\s*\))/';
        $code = preg_replace_callback(
            $pattern3,
            function ($matches) {
                $queryString = $matches[2];

                // Extract variables from the query
                if (preg_match_all('/\$(\w+)/', $queryString, $varMatches)) {
                    $variables = $varMatches[1];

                    // Replace variables with ? placeholders
                    $parameterizedQuery = preg_replace('/\$\w+/', '?', $queryString);
                    $bindingArray = implode(', ', array_map(fn($v) => '$' . $v, $variables));

                    return '$this->db->query("' . $parameterizedQuery . '", [' . $bindingArray . '])';
                }

                return $matches[0];
            },
            $code
        );

        return $code;
    }

    /**
     * Add authentication check to controller methods
     *
     * Ensures protected routes have proper authentication verification.
     *
     * @param string $code Controller code
     * @param Vulnerability|null $vulnerability Optional vulnerability context
     * @return string Code with authentication checks added
     */
    public function addAuthenticationCheck(string $code, ?Vulnerability $vulnerability = null): string
    {
        // Check if the controller already has auth checks
        if (str_contains($code, 'session()->get(') || str_contains($code, '$this->session->get(')) {
            // Already has some session checks, add guard if missing
            if (!str_contains($code, 'isLoggedIn') && !str_contains($code, 'logged_in')) {
                // Add auth check helper method
                $authCheck = $this->generateAuthCheckMethod();

                // Insert before the last closing brace of the class
                $lastBracePos = strrpos($code, '}');
                if ($lastBracePos !== false) {
                    $code = substr_replace($code, "\n" . $authCheck . "\n}", $lastBracePos);
                }
            }
            return $code;
        }

        // For controllers without any auth, add a filter reference
        if (str_contains($code, 'namespace App\\Controllers') && !str_contains($code, 'BaseController')) {
            $code = preg_replace(
                '/(class\s+\w+[^{]*\{)/',
                "$1\n    // NOTE: Apply auth filter via Routes or Filters config for authentication",
                $code,
                1
            );
        }

        // Add session-based auth check to public methods that modify data
        $methodPattern = '/(public\s+function\s+(simpan|save|store|update|delete|destroy|create)\s*\([^)]*\)\s*(?::\s*\w+\s*)?\{)/';
        $code = preg_replace_callback(
            $methodPattern,
            function ($matches) {
                $methodSignature = $matches[1];

                // Check if auth check already exists
                if (str_contains($methodSignature, 'session')) {
                    return $methodSignature;
                }

                $authGuard = "\n        // Authentication check\n";
                $authGuard .= "        if (!session()->get('logged_in')) {\n";
                $authGuard .= "            return redirect()->to('/login')->with('error', 'Please login first.');\n";
                $authGuard .= "        }\n";

                return $methodSignature . $authGuard;
            },
            $code
        );

        return $code;
    }

    /**
     * Add authorization check before sensitive operations
     *
     * Ensures proper role/permission checks before sensitive operations.
     *
     * @param string $code Controller code
     * @param Vulnerability|null $vulnerability Optional vulnerability context
     * @param string $requiredRole Required role for authorization
     * @return string Code with authorization checks added
     */
    public function addAuthorizationCheck(string $code, ?Vulnerability $vulnerability = null, string $requiredRole = 'admin'): string
    {
        // Add authorization check to delete/destroy methods
        $methodPattern = '/(public\s+function\s+(delete|destroy|remove)\s*\([^)]*\)\s*(?::\s*\w+\s*)?\{)/';
        $code = preg_replace_callback(
            $methodPattern,
            function ($matches) use ($requiredRole) {
                $methodSignature = $matches[1];

                // Skip if authorization check already exists
                if (str_contains($methodSignature, 'role') || str_contains($methodSignature, 'permission')) {
                    return $methodSignature;
                }

                $authzGuard = "\n        // Authorization check\n";
                $authzGuard .= "        \$userRole = session()->get('role');\n";
                $authzGuard .= "        if (\$userRole !== '" . $requiredRole . "') {\n";
                $authzGuard .= "            return redirect()->back()->with('error', 'Unauthorized action.');\n";
                $authzGuard .= "        }\n";

                return $methodSignature . $authzGuard;
            },
            $code
        );

        return $code;
    }

    /**
     * Add secure file upload validation
     *
     * Adds type, size, and extension checks to file upload handling.
     *
     * @param string $code Controller code with file upload handling
     * @param Vulnerability|null $vulnerability Optional vulnerability context
     * @return string Code with file upload validation added
     */
    public function addFileUploadValidation(string $code, ?Vulnerability $vulnerability = null): string
    {
        // Pattern: Find file upload handling without validation
        $pattern = '/(\$(\w+)\s*=\s*\$this->request->getFile\s*\([\'"](\w+)[\'"]\)\s*;)/';
        $code = preg_replace_callback(
            $pattern,
            function ($matches) {
                $originalLine = $matches[1];
                $varName = $matches[2];
                $fieldName = $matches[3];

                // Check if validation already exists after this line
                if (str_contains($originalLine, 'validate') || str_contains($originalLine, 'isValid')) {
                    return $originalLine;
                }

                $validationCode = $originalLine . "\n\n";
                $validationCode .= "        // File upload validation\n";
                $validationCode .= '        if (!$' . $varName . "->isValid()) {\n";
                $validationCode .= "            return redirect()->back()->with('error', 'Invalid file upload.');\n";
                $validationCode .= "        }\n\n";
                $validationCode .= "        // Validate file type, size, and extension\n";
                $validationCode .= "        \$validationRules = [\n";
                $validationCode .= "            '" . $fieldName . "' => [\n";
                $validationCode .= "                'uploaded[" . $fieldName . "]',\n";
                $validationCode .= "                'max_size[" . $fieldName . ",2048]',\n";
                $validationCode .= "                'ext_in[" . $fieldName . ",jpg,jpeg,png,pdf,doc,docx]',\n";
                $validationCode .= "                'mime_in[" . $fieldName . ",image/jpeg,image/png,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document]',\n";
                $validationCode .= "            ],\n";
                $validationCode .= "        ];\n\n";
                $validationCode .= "        if (!\$this->validate(\$validationRules)) {\n";
                $validationCode .= "            return redirect()->back()->withInput()->with('errors', \$this->validator->getErrors());\n";
                $validationCode .= "        }";

                return $validationCode;
            },
            $code
        );

        return $code;
    }

    /**
     * Collect unique file paths from a security report
     *
     * @param SecurityReport $report Security report
     * @return array<string> Unique file paths
     */
    private function collectFilePaths(SecurityReport $report): array
    {
        $paths = [];
        foreach ($report->vulnerabilities as $vulnerability) {
            if (!in_array($vulnerability->filePath, $paths, true)) {
                $paths[] = $vulnerability->filePath;
            }
        }
        return $paths;
    }

    /**
     * Group vulnerabilities by file path
     *
     * @param SecurityReport $report Security report
     * @return array<string, Vulnerability[]> Vulnerabilities grouped by file path
     */
    private function groupVulnerabilitiesByFile(SecurityReport $report): array
    {
        $grouped = [];
        foreach ($report->vulnerabilities as $vulnerability) {
            $grouped[$vulnerability->filePath][] = $vulnerability;
        }

        // Sort vulnerabilities within each file by line number (descending)
        // to avoid offset issues when applying fixes
        foreach ($grouped as &$vulnerabilities) {
            usort($vulnerabilities, fn($a, $b) => $b->lineNumber - $a->lineNumber);
        }

        return $grouped;
    }

    /**
     * Get unique vulnerability types that were fixed
     *
     * @param SecurityReport $report Security report
     * @return array<string> Unique vulnerability types
     */
    private function getFixedVulnerabilityTypes(SecurityReport $report): array
    {
        $types = [];
        foreach ($report->vulnerabilities as $vulnerability) {
            if (!in_array($vulnerability->type, $types, true)) {
                $types[] = $vulnerability->type;
            }
        }
        return $types;
    }

    /**
     * Infer validation rule based on field name
     *
     * @param string $fieldName Field name
     * @return string Inferred validation rule
     */
    private function inferValidationRule(string $fieldName): string
    {
        $fieldLower = strtolower($fieldName);

        if (str_contains($fieldLower, 'email')) {
            return 'required|valid_email';
        }
        if (str_contains($fieldLower, 'phone') || str_contains($fieldLower, 'telp')) {
            return 'required|numeric|min_length[10]';
        }
        if (str_contains($fieldLower, 'password')) {
            return 'required|min_length[8]';
        }
        if (str_contains($fieldLower, 'id')) {
            return 'required|integer';
        }
        if (str_contains($fieldLower, 'nama') || str_contains($fieldLower, 'name')) {
            return 'required|max_length[255]';
        }
        if (str_contains($fieldLower, 'url') || str_contains($fieldLower, 'link')) {
            return 'required|valid_url';
        }
        if (str_contains($fieldLower, 'tanggal') || str_contains($fieldLower, 'date')) {
            return 'required|valid_date';
        }
        if (str_contains($fieldLower, 'jumlah') || str_contains($fieldLower, 'amount') || str_contains($fieldLower, 'harga') || str_contains($fieldLower, 'price')) {
            return 'required|numeric';
        }

        return 'required|max_length[255]';
    }

    /**
     * Generate authentication check helper method
     *
     * @return string Generated auth check method code
     */
    private function generateAuthCheckMethod(): string
    {
        $code = "    /**\n";
        $code .= "     * Check if user is authenticated\n";
        $code .= "     *\n";
        $code .= "     * @return bool\n";
        $code .= "     */\n";
        $code .= "    protected function isAuthenticated(): bool\n";
        $code .= "    {\n";
        $code .= "        return (bool) session()->get('logged_in');\n";
        $code .= "    }\n";

        return $code;
    }
}

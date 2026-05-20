<?php

namespace App\Libraries\Refactor\Security;

use App\Libraries\Refactor\Generation\CodeGenerator;
use App\Libraries\Refactor\Models\SecurityReport;
use App\Libraries\Refactor\Models\Vulnerability;

/**
 * SecurityFixer
 * 
 * Automatically fixes common security vulnerabilities identified by SecurityScanner.
 * Applies security patterns including input validation, output escaping, CSRF protection,
 * SQL injection prevention, and secure authentication/authorization checks.
 * 
 * @package App\Libraries\Refactor\Security
 */
class SecurityFixer
{
    /**
     * @var CodeGenerator Code generator for creating secure code
     */
    private CodeGenerator $codeGenerator;

    /**
     * Constructor
     * 
     * @param CodeGenerator|null $codeGenerator Optional CodeGenerator instance
     */
    public function __construct(?CodeGenerator $codeGenerator = null)
    {
        $this->codeGenerator = $codeGenerator ?? new CodeGenerator();
    }

    /**
     * Fix security vulnerabilities based on SecurityReport
     * 
     * @param SecurityReport $report Security report with vulnerabilities
     * @return array<string, string> Array of fixed file contents [filePath => fixedCode]
     */
    public function fix(SecurityReport $report): array
    {
        $fixedFiles = [];

        // Group vulnerabilities by file path
        $vulnerabilitiesByFile = $this->groupVulnerabilitiesByFile($report->vulnerabilities);

        // Process each file
        foreach ($vulnerabilitiesByFile as $filePath => $vulnerabilities) {
            if (!file_exists($filePath)) {
                continue;
            }

            $code = file_get_contents($filePath);
            if ($code === false) {
                continue;
            }

            // Apply fixes for each vulnerability type
            $fixedCode = $this->applyFixes($code, $vulnerabilities, $filePath);

            $fixedFiles[$filePath] = $fixedCode;
        }

        return $fixedFiles;
    }

    /**
     * Apply fixes to code based on vulnerabilities
     * 
     * @param string $code Original code
     * @param Vulnerability[] $vulnerabilities Vulnerabilities to fix
     * @param string $filePath File path for context
     * @return string Fixed code
     */
    private function applyFixes(string $code, array $vulnerabilities, string $filePath): string
    {
        $fixedCode = $code;

        // Group vulnerabilities by type for batch processing
        $vulnerabilitiesByType = [];
        foreach ($vulnerabilities as $vulnerability) {
            $vulnerabilitiesByType[$vulnerability->type][] = $vulnerability;
        }

        // Apply fixes in order of priority (most critical first)
        $fixOrder = [
            Vulnerability::TYPE_SQL_INJECTION,
            Vulnerability::TYPE_INSECURE_AUTH,
            Vulnerability::TYPE_HARDCODED_CREDENTIALS,
            Vulnerability::TYPE_CSRF,
            Vulnerability::TYPE_MISSING_VALIDATION,
            Vulnerability::TYPE_XSS,
            Vulnerability::TYPE_INSECURE_FILE_UPLOAD,
        ];

        foreach ($fixOrder as $type) {
            if (!isset($vulnerabilitiesByType[$type])) {
                continue;
            }

            $fixedCode = match ($type) {
                Vulnerability::TYPE_SQL_INJECTION => $this->replaceRawQueryWithQueryBuilder($fixedCode),
                Vulnerability::TYPE_CSRF => $this->addCSRFProtection($fixedCode),
                Vulnerability::TYPE_XSS => $this->addOutputEscaping($fixedCode),
                Vulnerability::TYPE_INSECURE_AUTH => $this->fixInsecureAuth($fixedCode),
                Vulnerability::TYPE_HARDCODED_CREDENTIALS => $this->removeHardcodedCredentials($fixedCode),
                Vulnerability::TYPE_MISSING_VALIDATION => $this->addInputValidation($fixedCode, []),
                Vulnerability::TYPE_INSECURE_FILE_UPLOAD => $this->addFileUploadValidation($fixedCode),
                default => $fixedCode,
            };
        }

        return $fixedCode;
    }

    /**
     * Add CSRF protection to form submissions
     * 
     * @param string $code Code to fix
     * @return string Fixed code with CSRF protection
     */
    public function addCSRFProtection(string $code): string
    {
        // Pattern 1: Add csrf_field() to forms without CSRF protection
        $code = preg_replace_callback(
            '/<form\s+([^>]*method=["\']post["\'][^>]*)>/i',
            function ($matches) {
                $formTag = $matches[0];
                
                // Check if form already has CSRF protection
                if (str_contains($formTag, 'csrf_field') || str_contains($formTag, 'csrf_token')) {
                    return $formTag;
                }
                
                // Add csrf_field() after form opening tag
                return $formTag . "\n    <?= csrf_field() ?>";
            },
            $code
        );

        // Pattern 2: Add CSRF filter to routes in controllers
        if (preg_match('/class\s+\w+\s+extends\s+BaseController/i', $code)) {
            // Check if $filters property exists
            if (!preg_match('/protected\s+\$filters\s*=/', $code)) {
                // Add filters property after class declaration
                $code = preg_replace(
                    '/(class\s+\w+\s+extends\s+BaseController[^{]*\{)/',
                    "$1\n    /**\n     * @var array<string, array<string>> Filters to apply\n     */\n    protected \$filters = ['csrf'];",
                    $code,
                    1
                );
            } else {
                // Add csrf to existing filters array if not present
                if (!str_contains($code, "'csrf'") && !str_contains($code, '"csrf"')) {
                    $code = preg_replace(
                        '/(protected\s+\$filters\s*=\s*\[)([^\]]*)\]/',
                        "$1$2, 'csrf']",
                        $code
                    );
                }
            }
        }

        return $code;
    }

    /**
     * Add input validation using CodeIgniter 4 validation rules
     * 
     * @param string $code Code to fix
     * @param array<string, string> $rules Validation rules to apply
     * @return string Fixed code with input validation
     */
    public function addInputValidation(string $code, array $rules): string
    {
        // Find methods that handle POST data without validation
        $code = preg_replace_callback(
            '/(public\s+function\s+\w+\s*\([^)]*\)\s*(?::\s*\w+)?\s*\{)([^}]*\$this->request->getPost\(\)[^}]*?)(\})/s',
            function ($matches) use ($rules) {
                $methodSignature = $matches[1];
                $methodBody = $matches[2];
                $closingBrace = $matches[3];

                // Check if validation already exists
                if (str_contains($methodBody, '$this->validate(') || str_contains($methodBody, 'validation->')) {
                    return $matches[0];
                }

                // Add validation check
                $validationCode = "\n        // Validate input data\n";
                $validationCode .= "        if (!\$this->validate([\n";
                $validationCode .= "            // TODO: Add validation rules for your fields\n";
                $validationCode .= "            // 'field_name' => 'required|min_length[3]',\n";
                $validationCode .= "        ])) {\n";
                $validationCode .= "            return redirect()->back()->withInput()->with('errors', \$this->validator->getErrors());\n";
                $validationCode .= "        }\n\n";

                return $methodSignature . $validationCode . $methodBody . $closingBrace;
            },
            $code
        );

        return $code;
    }

    /**
     * Add output escaping in views to prevent XSS
     * 
     * @param string $viewCode View code to fix
     * @return string Fixed code with output escaping
     */
    public function addOutputEscaping(string $viewCode): string
    {
        // Pattern 1: Replace = $var  with <?= esc($var) 
        $viewCode = preg_replace_callback(
            '/<\?=\s*(\$[a-zA-Z_][a-zA-Z0-9_]*(?:->[a-zA-Z_][a-zA-Z0-9_]*|\[[^\]]+\])*)\s*\?>/i',
            function ($matches) {
                $variable = $matches[1];
                
                // Skip if already escaped
                if (str_contains($matches[0], 'esc(')) {
                    return $matches[0];
                }
                
                return "<?= esc({$variable}) ?>";
            },
            $viewCode
        );

        // Pattern 2: Replace echo $var with echo esc($var)
        $viewCode = preg_replace_callback(
            '/echo\s+(\$[a-zA-Z_][a-zA-Z0-9_]*(?:->[a-zA-Z_][a-zA-Z0-9_]*|\[[^\]]+\])*)\s*;/i',
            function ($matches) {
                $variable = $matches[1];
                
                // Skip if already escaped
                if (str_contains($matches[0], 'esc(')) {
                    return $matches[0];
                }
                
                return "echo esc({$variable});";
            },
            $viewCode
        );

        return $viewCode;
    }

    /**
     * Replace raw SQL queries with Query Builder to prevent SQL injection
     * 
     * @param string $code Code to fix
     * @return string Fixed code with Query Builder
     */
    public function replaceRawQueryWithQueryBuilder(string $code): string
    {
        // Pattern 1: Replace simple SELECT queries with Query Builder
        $code = preg_replace_callback(
            '/\$this->db->query\s*\(\s*["\']SELECT\s+\*\s+FROM\s+(\w+)\s+WHERE\s+(\w+)\s*=\s*["\']?\s*\$(\w+)\s*["\']?\s*["\']\s*\)/i',
            function ($matches) {
                $table = $matches[1];
                $column = $matches[2];
                $variable = $matches[3];
                
                return "\$this->db->table('{$table}')->where('{$column}', \${$variable})->get()";
            },
            $code
        );

        // Pattern 2: Replace simple INSERT queries
        $code = preg_replace_callback(
            '/\$this->db->query\s*\(\s*["\']INSERT\s+INTO\s+(\w+)\s*\([^)]+\)\s+VALUES\s*\([^)]+\)["\']\s*\)/i',
            function ($matches) {
                $table = $matches[1];
                
                return "// TODO: Replace with \$this->db->table('{$table}')->insert(\$data)";
            },
            $code
        );

        // Pattern 3: Replace simple UPDATE queries
        $code = preg_replace_callback(
            '/\$this->db->query\s*\(\s*["\']UPDATE\s+(\w+)\s+SET\s+[^"\']+WHERE\s+(\w+)\s*=\s*["\']?\s*\$(\w+)\s*["\']?["\']\s*\)/i',
            function ($matches) {
                $table = $matches[1];
                $column = $matches[2];
                $variable = $matches[3];
                
                return "// TODO: Replace with \$this->db->table('{$table}')->where('{$column}', \${$variable})->update(\$data)";
            },
            $code
        );

        // Pattern 4: Replace simple DELETE queries
        $code = preg_replace_callback(
            '/\$this->db->query\s*\(\s*["\']DELETE\s+FROM\s+(\w+)\s+WHERE\s+(\w+)\s*=\s*["\']?\s*\$(\w+)\s*["\']?["\']\s*\)/i',
            function ($matches) {
                $table = $matches[1];
                $column = $matches[2];
                $variable = $matches[3];
                
                return "\$this->db->table('{$table}')->where('{$column}', \${$variable})->delete()";
            },
            $code
        );

        // Add comment for complex queries that need manual refactoring
        $code = preg_replace_callback(
            '/(\$this->db->query\s*\([^)]+\))/s',
            function ($matches) {
                $query = $matches[1];
                
                // Skip if already has a TODO comment nearby
                if (str_contains($query, 'TODO')) {
                    return $query;
                }
                
                return "// TODO: Refactor this raw query to use Query Builder for SQL injection prevention\n        " . $query;
            },
            $code
        );

        return $code;
    }

    /**
     * Add authentication check to protected routes
     * 
     * @param string $code Code to fix
     * @return string Fixed code with authentication checks
     */
    public function addAuthenticationCheck(string $code): string
    {
        // Find public methods in controllers that don't have authentication checks
        $code = preg_replace_callback(
            '/(public\s+function\s+(?!__construct)(\w+)\s*\([^)]*\)\s*(?::\s*\w+)?\s*\{)([^}]*?)(\})/s',
            function ($matches) {
                $methodSignature = $matches[1];
                $methodName = $matches[2];
                $methodBody = $matches[3];
                $closingBrace = $matches[4];

                // Skip if already has authentication check
                if (str_contains($methodBody, 'logged_in') || 
                    str_contains($methodBody, 'isLoggedIn') ||
                    str_contains($methodBody, 'session()->get(') ||
                    str_contains($methodBody, 'auth->check')) {
                    return $matches[0];
                }

                // Skip common public methods
                $publicMethods = ['index', 'login', 'register', 'logout'];
                if (in_array($methodName, $publicMethods)) {
                    return $matches[0];
                }

                // Add authentication check
                $authCheck = "\n        // TODO: Add proper authentication check\n";
                $authCheck .= "        if (!session()->get('logged_in')) {\n";
                $authCheck .= "            return redirect()->to('/login');\n";
                $authCheck .= "        }\n";

                return $methodSignature . $authCheck . $methodBody . $closingBrace;
            },
            $code
        );

        return $code;
    }

    /**
     * Add authorization check before sensitive operations
     * 
     * @param string $code Code to fix
     * @return string Fixed code with authorization checks
     */
    public function addAuthorizationCheck(string $code): string
    {
        // Find methods that perform delete, update, or modify operations
        $code = preg_replace_callback(
            '/(public\s+function\s+(delete|update|modify|remove)\w*\s*\([^)]*\)\s*(?::\s*\w+)?\s*\{)([^}]*?)(\})/si',
            function ($matches) {
                $methodSignature = $matches[1];
                $methodBody = $matches[3];
                $closingBrace = $matches[4];

                // Skip if already has authorization check
                if (str_contains($methodBody, 'can(') || 
                    str_contains($methodBody, 'authorize') ||
                    str_contains($methodBody, 'hasPermission')) {
                    return $matches[0];
                }

                // Add authorization check
                $authzCheck = "\n        // TODO: Add proper authorization check\n";
                $authzCheck .= "        // if (!\$this->authorize('resource', 'action')) {\n";
                $authzCheck .= "        //     throw new \CodeIgniter\Exceptions\PageNotFoundException();\n";
                $authzCheck .= "        // }\n";

                return $methodSignature . $authzCheck . $methodBody . $closingBrace;
            },
            $code
        );

        return $code;
    }

    /**
     * Add secure file upload validation
     * 
     * @param string $code Code to fix
     * @return string Fixed code with file upload validation
     */
    public function addFileUploadValidation(string $code): string
    {
        // Find file upload operations
        $code = preg_replace_callback(
            '/(\$\w+\s*=\s*\$this->request->getFile\([^)]+\);)([^}]*?)(\$\w+->move\([^)]+\);)/s',
            function ($matches) {
                $getFile = $matches[1];
                $betweenCode = $matches[2];
                $moveFile = $matches[3];

                // Skip if already has validation
                if (str_contains($betweenCode, 'isValid()') || 
                    str_contains($betweenCode, 'getExtension()') ||
                    str_contains($betweenCode, 'getSize()')) {
                    return $matches[0];
                }

                // Add file validation
                $validation = "\n\n        // Validate file upload\n";
                $validation .= "        if (!\$file->isValid()) {\n";
                $validation .= "            throw new \RuntimeException(\$file->getErrorString() . '(' . \$file->getError() . ')');\n";
                $validation .= "        }\n\n";
                $validation .= "        // Validate file type\n";
                $validation .= "        \$allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];\n";
                $validation .= "        if (!in_array(\$file->getExtension(), \$allowedExtensions)) {\n";
                $validation .= "            throw new \RuntimeException('Invalid file type');\n";
                $validation .= "        }\n\n";
                $validation .= "        // Validate file size (max 2MB)\n";
                $validation .= "        if (\$file->getSize() > 2048000) {\n";
                $validation .= "            throw new \RuntimeException('File too large');\n";
                $validation .= "        }\n\n";

                return $getFile . $validation . $betweenCode . $moveFile;
            },
            $code
        );

        return $code;
    }

    /**
     * Fix insecure authentication patterns
     * 
     * @param string $code Code to fix
     * @return string Fixed code with secure authentication
     */
    private function fixInsecureAuth(string $code): string
    {
        // Replace MD5 password hashing with password_hash
        $code = preg_replace(
            '/md5\s*\(\s*\$(\w+)\s*\)/',
            'password_hash($\1, PASSWORD_DEFAULT)',
            $code
        );

        // Replace SHA1 password hashing with password_hash
        $code = preg_replace(
            '/sha1\s*\(\s*\$(\w+)\s*\)/',
            'password_hash($\1, PASSWORD_DEFAULT)',
            $code
        );

        // Replace plain text password comparison with password_verify
        $code = preg_replace_callback(
            '/if\s*\(\s*\$(\w+)\s*==\s*\$(\w+)\s*\)/',
            function ($matches) {
                $var1 = $matches[1];
                $var2 = $matches[2];
                
                // Check if this looks like password comparison
                if (str_contains($var1, 'password') || str_contains($var2, 'password')) {
                    return "if (password_verify(\${$var1}, \${$var2}))";
                }
                
                return $matches[0];
            },
            $code
        );

        return $code;
    }

    /**
     * Remove hardcoded credentials and replace with environment variables
     * 
     * @param string $code Code to fix
     * @return string Fixed code without hardcoded credentials
     */
    private function removeHardcodedCredentials(string $code): string
    {
        // Pattern 1: Replace hardcoded passwords
        $code = preg_replace_callback(
            '/\$(\w*[Pp]assword\w*)\s*=\s*["\']([^"\']+)["\'];/',
            function ($matches) {
                $varName = $matches[1];
                $value = $matches[2];
                
                // Skip if it's already using env()
                if (str_contains($value, 'env(')) {
                    return $matches[0];
                }
                
                return "\${$varName} = env('PASSWORD'); // TODO: Set PASSWORD in .env file (was: {$value})";
            },
            $code
        );

        // Pattern 2: Replace hardcoded API keys
        $code = preg_replace_callback(
            '/\$(\w*[Aa]pi[Kk]ey\w*)\s*=\s*["\']([^"\']+)["\'];/',
            function ($matches) {
                $varName = $matches[1];
                $value = $matches[2];
                
                // Skip if it's already using env()
                if (str_contains($value, 'env(')) {
                    return $matches[0];
                }
                
                return "\${$varName} = env('API_KEY'); // TODO: Set API_KEY in .env file (was: {$value})";
            },
            $code
        );

        // Pattern 3: Replace hardcoded secrets/tokens
        $code = preg_replace_callback(
            '/\$(\w*[Ss]ecret\w*|\w*[Tt]oken\w*)\s*=\s*["\']([^"\']+)["\'];/',
            function ($matches) {
                $varName = $matches[1];
                $value = $matches[2];
                
                // Skip if it's already using env()
                if (str_contains($value, 'env(')) {
                    return $matches[0];
                }
                
                $envKey = strtoupper(preg_replace('/([a-z])([A-Z])/', '$1_$2', $varName));
                return "\${$varName} = env('{$envKey}'); // TODO: Set {$envKey} in .env file (was: {$value})";
            },
            $code
        );

        return $code;
    }

    /**
     * Group vulnerabilities by file path
     * 
     * @param Vulnerability[] $vulnerabilities Array of vulnerabilities
     * @return array<string, Vulnerability[]> Vulnerabilities grouped by file path
     */
    private function groupVulnerabilitiesByFile(array $vulnerabilities): array
    {
        $grouped = [];

        foreach ($vulnerabilities as $vulnerability) {
            $filePath = $vulnerability->filePath;
            
            if (!isset($grouped[$filePath])) {
                $grouped[$filePath] = [];
            }
            
            $grouped[$filePath][] = $vulnerability;
        }

        return $grouped;
    }

    /**
     * Write fixed code to file
     * 
     * @param string $filePath File path to write
     * @param string $code Fixed code
     * @return bool Success status
     */
    public function writeFixedCode(string $filePath, string $code): bool
    {
        // Ensure directory exists
        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true) && !is_dir($dir)) {
                return false;
            }
        }

        // Write file
        return file_put_contents($filePath, $code) !== false;
    }

    /**
     * Validate fixed code syntax
     * 
     * @param string $code Code to validate
     * @return array{valid: bool, error: string|null} Validation result
     */
    public function validateFixedCode(string $code): array
    {
        return $this->codeGenerator->validateSyntax($code);
    }

    /**
     * Generate fix report
     * 
     * @param SecurityReport $report Original security report
     * @param array<string, string> $fixedFiles Fixed files
     * @return array<string, mixed> Fix report with statistics
     */
    public function generateFixReport(SecurityReport $report, array $fixedFiles): array
    {
        $fixReport = [
            'moduleName' => $report->moduleName,
            'totalVulnerabilities' => $report->getTotalCount(),
            'filesFixed' => count($fixedFiles),
            'fixedFiles' => array_keys($fixedFiles),
            'vulnerabilitiesByType' => [],
            'timestamp' => date('c'),
        ];

        // Count vulnerabilities by type
        foreach ($report->vulnerabilities as $vulnerability) {
            $type = $vulnerability->type;
            if (!isset($fixReport['vulnerabilitiesByType'][$type])) {
                $fixReport['vulnerabilitiesByType'][$type] = 0;
            }
            $fixReport['vulnerabilitiesByType'][$type]++;
        }

        return $fixReport;
    }
}

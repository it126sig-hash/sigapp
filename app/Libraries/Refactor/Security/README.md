# Security Rules Documentation

## Overview

The `SecurityRules` class provides comprehensive security rule definitions for detecting common vulnerabilities in CodeIgniter 4 applications. These rules are used by the `SecurityScanner` component to identify security issues during the refactoring process.

## Vulnerability Types

The security rules cover seven major vulnerability categories:

### 1. SQL Injection (SQL_INJECTION)

**Description**: Detects patterns where user input is directly concatenated into SQL queries without proper parameter binding or escaping.

**Severity Levels**: CRITICAL, HIGH, MEDIUM

**Common Patterns Detected**:
- Raw SQL queries with variable concatenation
- String concatenation in SQL statements
- Query Builder with variable interpolation in WHERE clauses
- Direct SQL statements (SELECT, INSERT, UPDATE, DELETE) with variables

**Example Vulnerable Code**:
```php
// CRITICAL: Raw query with variable
$this->db->query("SELECT * FROM users WHERE id = $id");

// CRITICAL: String concatenation
$query = "SELECT * FROM users WHERE name = '" . $name . "'";
$this->db->query($query);

// HIGH: Query Builder with interpolation
$builder->where("id = $id");
```

**Recommended Fix**:
```php
// Use Query Builder with parameter binding
$this->db->table('users')->where('id', $id)->get();

// Or use prepared statements
$this->db->query("SELECT * FROM users WHERE id = ?", [$id]);
```

### 2. Cross-Site Scripting (XSS)

**Description**: Detects patterns where user input is rendered in views without proper escaping.

**Severity Levels**: HIGH, MEDIUM

**Common Patterns Detected**:
- Unescaped echo statements
- Unescaped short echo tags (<?= ?>)
- Unescaped print statements
- JavaScript innerHTML with variable interpolation
- Request variables used without escaping

**Example Vulnerable Code**:
```php
// HIGH: Unescaped echo
echo $userInput;

// HIGH: Unescaped short tag
<?= $userInput ?>

// MEDIUM: Request variable without escaping
$name = $this->request->getVar('name');
echo $name;
```

**Recommended Fix**:
```php
// Use esc() helper
echo esc($userInput);

// Use esc() in views
<?= esc($userInput) ?>

// Escape request variables
$name = $this->request->getVar('name');
echo esc($name);
```

### 3. Cross-Site Request Forgery (CSRF)

**Description**: Detects forms and POST endpoints without CSRF protection.

**Severity Levels**: HIGH, MEDIUM, LOW

**Common Patterns Detected**:
- POST forms without CSRF token
- Forms without csrf_field() or form_open()
- Controller methods without CSRF token refresh
- JavaScript fetch/AJAX POST requests without CSRF token

**Example Vulnerable Code**:
```php
// HIGH: Form without CSRF token
<form method="post" action="/submit">
    <input name="data">
</form>

// HIGH: JavaScript fetch without CSRF token
fetch('/api/save', {
    method: 'POST',
    body: JSON.stringify(data)
});
```

**Recommended Fix**:
```php
// Use form_open() helper (includes CSRF automatically)
<?= form_open('/submit') ?>
    <input name="data">
<?= form_close() ?>

// Or add csrf_field() manually
<form method="post" action="/submit">
    <?= csrf_field() ?>
    <input name="data">
</form>

// Include CSRF token in JavaScript
fetch('/api/save', {
    method: 'POST',
    headers: {
        'X-CSRF-TOKEN': csrfToken
    },
    body: JSON.stringify(data)
});
```

### 4. Insecure Authentication (INSECURE_AUTH)

**Description**: Detects weak authentication patterns and missing authorization checks.

**Severity Levels**: CRITICAL, HIGH, MEDIUM

**Common Patterns Detected**:
- Plain text password comparison
- MD5 or SHA1 password hashing (insecure)
- Session assignment without proper authentication
- Controller methods without authentication checks
- Delete/update operations without authorization checks

**Example Vulnerable Code**:
```php
// CRITICAL: Plain text password comparison
if ($password == $storedPassword) {
    // Login user
}

// CRITICAL: MD5 hashing (insecure)
$hashedPassword = md5($password);

// HIGH: Delete without authorization
public function delete($id) {
    $this->model->delete($id);
}
```

**Recommended Fix**:
```php
// Use password_hash() and password_verify()
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

if (password_verify($password, $storedPassword)) {
    // Login user
}

// Add authorization check
public function delete($id) {
    if (!has_permission('delete_user')) {
        return $this->response->setStatusCode(403);
    }
    $this->model->delete($id);
}
```

### 5. Hardcoded Credentials (HARDCODED_CREDENTIALS)

**Description**: Detects hardcoded passwords, API keys, and other sensitive data.

**Severity Levels**: CRITICAL, HIGH

**Common Patterns Detected**:
- Hardcoded passwords
- Hardcoded API keys
- Hardcoded secret keys
- Hardcoded tokens
- Hardcoded private keys
- Hardcoded database credentials
- Hardcoded AWS credentials

**Example Vulnerable Code**:
```php
// CRITICAL: Hardcoded password
$password = "mySecretPassword123";

// CRITICAL: Hardcoded API key
$apiKey = "sk_live_1234567890abcdef";

// CRITICAL: Hardcoded database credentials
$username = "root";
$password = "admin123";
```

**Recommended Fix**:
```php
// Use .env file and env() helper
$password = env('APP_PASSWORD');
$apiKey = env('API_KEY');

// Use database configuration
$db = db_connect();
```

### 6. Missing Input Validation (MISSING_VALIDATION)

**Description**: Detects controller methods that process user input without validation.

**Severity Levels**: HIGH, MEDIUM

**Common Patterns Detected**:
- Data modification methods without validation
- POST data retrieved without validation
- Request variables without validation
- JSON data without validation
- Database insert/update without validated data
- Direct superglobal usage ($_GET, $_POST, $_REQUEST)

**Example Vulnerable Code**:
```php
// HIGH: Save method without validation
public function save() {
    $data = $this->request->getPost();
    $this->model->insert($data);
}

// HIGH: Direct superglobal usage
$id = $_GET['id'];
```

**Recommended Fix**:
```php
// Add validation
public function save() {
    $rules = [
        'name' => 'required|min_length[3]',
        'email' => 'required|valid_email',
    ];
    
    if (!$this->validate($rules)) {
        return $this->response->setJSON([
            'errors' => $this->validator->getErrors()
        ]);
    }
    
    $data = $this->validator->getValidated();
    $this->model->insert($data);
}

// Use request methods
$id = $this->request->getGet('id');
```

### 7. Insecure File Upload (INSECURE_FILE_UPLOAD)

**Description**: Detects file upload handling without proper security checks.

**Severity Levels**: HIGH, MEDIUM, LOW

**Common Patterns Detected**:
- File upload without validation
- Direct move_uploaded_file usage
- File moved without extension validation
- File moved without MIME type validation
- File moved without size validation
- Direct $_FILES superglobal usage
- Original filename used without sanitization

**Example Vulnerable Code**:
```php
// HIGH: File upload without validation
$file = $this->request->getFile('upload');
$file->move(WRITEPATH . 'uploads');

// HIGH: Direct $_FILES usage
move_uploaded_file($_FILES['upload']['tmp_name'], 'uploads/' . $_FILES['upload']['name']);
```

**Recommended Fix**:
```php
// Validate file before moving
$file = $this->request->getFile('upload');

if (!$file->isValid()) {
    return $this->response->setJSON(['error' => 'Invalid file']);
}

$allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
if (!in_array($file->getExtension(), $allowedExtensions)) {
    return $this->response->setJSON(['error' => 'Invalid file type']);
}

$allowedMimeTypes = ['image/jpeg', 'image/png', 'application/pdf'];
if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
    return $this->response->setJSON(['error' => 'Invalid MIME type']);
}

if ($file->getSize() > 5 * 1024 * 1024) { // 5MB
    return $this->response->setJSON(['error' => 'File too large']);
}

// Use random name to prevent directory traversal
$file->move(WRITEPATH . 'uploads', $file->getRandomName());
```

## Usage

### Getting All Rules

```php
use App\Libraries\Refactor\Security\SecurityRules;

$allRules = SecurityRules::getAllRules();
```

### Getting Rules by Type

```php
$sqlInjectionRules = SecurityRules::getSQLInjectionRules();
$xssRules = SecurityRules::getXSSRules();
$csrfRules = SecurityRules::getCSRFRules();
$authRules = SecurityRules::getInsecureAuthRules();
$credentialRules = SecurityRules::getHardcodedCredentialsRules();
$validationRules = SecurityRules::getMissingValidationRules();
$fileUploadRules = SecurityRules::getInsecureFileUploadRules();
```

### Checking Code Against Rules

```php
$code = file_get_contents('path/to/controller.php');
$vulnerabilities = [];

foreach ($sqlInjectionRules as $rule) {
    if (preg_match($rule['pattern'], $code, $matches)) {
        $vulnerabilities[] = [
            'type' => 'SQL_INJECTION',
            'severity' => $rule['severity'],
            'description' => $rule['description'],
            'recommendation' => $rule['recommendation'],
            'match' => $matches[0],
        ];
    }
}
```

## Rule Structure

Each rule is an associative array with the following keys:

- **pattern** (string): Regular expression pattern to match vulnerable code
- **severity** (string): Severity level (CRITICAL, HIGH, MEDIUM, LOW)
- **description** (string): Human-readable description of the vulnerability
- **recommendation** (string): How to fix the vulnerability

Example:
```php
[
    'pattern' => '/\$this->db->query\s*\(\s*["\'].*?\$.*?["\']\s*\)/s',
    'severity' => 'CRITICAL',
    'description' => 'Raw SQL query with variable concatenation detected',
    'recommendation' => 'Use Query Builder with parameter binding: $this->db->table()->where()->get()',
]
```

## Severity Levels

### CRITICAL (Level 4)
- Immediate security risk
- Can lead to data breach or system compromise
- Examples: SQL injection, hardcoded passwords, insecure password hashing

### HIGH (Level 3)
- Significant security risk
- Can lead to unauthorized access or data exposure
- Examples: XSS, missing CSRF protection, missing authentication

### MEDIUM (Level 2)
- Moderate security risk
- Can lead to security issues under certain conditions
- Examples: Missing validation, unescaped output in specific contexts

### LOW (Level 1)
- Minor security concern
- Best practice violation
- Examples: Missing file extension validation, suboptimal security patterns

## Helper Methods

### Get Severity Level

```php
$level = SecurityRules::getSeverityLevel('CRITICAL'); // Returns 4
$level = SecurityRules::getSeverityLevel('HIGH');     // Returns 3
$level = SecurityRules::getSeverityLevel('MEDIUM');   // Returns 2
$level = SecurityRules::getSeverityLevel('LOW');      // Returns 1
```

### Get Vulnerability Types

```php
$types = SecurityRules::getVulnerabilityTypes();
// Returns: ['SQL_INJECTION', 'XSS', 'CSRF', 'INSECURE_AUTH', 'HARDCODED_CREDENTIALS', 'MISSING_VALIDATION', 'INSECURE_FILE_UPLOAD']
```

### Get Severity Levels

```php
$levels = SecurityRules::getSeverityLevels();
// Returns: ['CRITICAL', 'HIGH', 'MEDIUM', 'LOW']
```

## False Positives

Some patterns may produce false positives. When implementing the SecurityScanner, consider:

1. **Context Analysis**: Check if the code is in a comment or string literal
2. **Framework Helpers**: Some CodeIgniter helpers already provide security (e.g., form_open() includes CSRF)
3. **Validated Data**: If data is already validated, some patterns may not apply
4. **Test Code**: Test files may intentionally contain vulnerable patterns

## Extending Rules

To add new rules, create a new method in the `SecurityRules` class:

```php
public static function getCustomRules(): array
{
    return [
        [
            'pattern' => '/your-regex-pattern/',
            'severity' => 'HIGH',
            'description' => 'Description of the vulnerability',
            'recommendation' => 'How to fix it',
        ],
    ];
}
```

Then add the new rule type to `getAllRules()`:

```php
public static function getAllRules(): array
{
    return [
        // ... existing rules
        'CUSTOM_VULNERABILITY' => self::getCustomRules(),
    ];
}
```

## Testing

Run the security rules tests:

```bash
vendor/bin/phpunit tests/Libraries/Refactor/Security/SecurityRulesTest.php
```

The test suite verifies:
- All rules are properly structured
- All required fields are present
- Severity levels are valid
- Regex patterns are valid
- Rules can detect known vulnerable patterns

## References

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [CodeIgniter 4 Security Guidelines](https://codeigniter.com/user_guide/concepts/security.html)
- [PHP Security Best Practices](https://www.php.net/manual/en/security.php)
- [CWE - Common Weakness Enumeration](https://cwe.mitre.org/)

## Requirements Mapping

This implementation satisfies the following requirements:

- **Requirement 4.1**: SQL injection detection patterns
- **Requirement 4.2**: CSRF protection detection patterns
- **Requirement 4.3**: XSS vulnerability detection patterns
- **Requirement 4.4**: Insecure authentication detection patterns
- **Requirement 4.5**: Hardcoded credentials detection patterns
- **Requirement 4.6**: Missing input validation detection patterns
- **Requirement 4.7**: Insecure file upload detection patterns


## SecurityScanner Component

### Overview

The `SecurityScanner` class uses the `SecurityRules` patterns to scan CodeIgniter 4 module files and detect security vulnerabilities. It generates comprehensive `SecurityReport` objects with detailed findings.

### Features

- **Module-Level Scanning**: Scans all files in a module (controller, models, service, repository)
- **Pattern-Based Detection**: Uses SecurityRules regex patterns for vulnerability detection
- **Line Number Tracking**: Calculates exact line numbers for each vulnerability
- **Code Snippet Extraction**: Extracts and truncates vulnerable code snippets
- **Comprehensive Reporting**: Generates SecurityReport with all findings

### Usage

```php
use App\Libraries\Refactor\Security\SecurityScanner;
use App\Libraries\Refactor\Models\Module;

// Create scanner
$scanner = new SecurityScanner();

// Create module to scan
$module = new Module('Transaksi', 'app/Controllers/Transaksi.php');
$module->modelPaths = ['app/Models/TransaksiModel.php'];

// Scan the module
$report = $scanner->scanModule($module);

// Access results
echo "Total vulnerabilities: {$report->getTotalCount()}\n";
echo "Critical: {$report->getCriticalCount()}\n";
echo "High: {$report->getHighCount()}\n";
echo "Medium: {$report->getMediumCount()}\n";
echo "Low: {$report->getLowCount()}\n";

// Iterate through vulnerabilities
foreach ($report->vulnerabilities as $vuln) {
    echo "\n{$vuln->severity}: {$vuln->type}\n";
    echo "  Description: {$vuln->description}\n";
    echo "  Location: {$vuln->filePath}:{$vuln->lineNumber}\n";
    echo "  Code: {$vuln->codeSnippet}\n";
    echo "  Fix: {$vuln->recommendation}\n";
}

// Get vulnerabilities by severity
$criticalVulns = $report->getBySeverity('CRITICAL');
foreach ($criticalVulns as $vuln) {
    // Handle critical vulnerabilities
}

// Save report to JSON
file_put_contents('security_report.json', $report->toJson());
```

### Detection Methods

The SecurityScanner provides individual detection methods for each vulnerability type:

```php
// Detect SQL injection
$vulnerabilities = $scanner->detectSQLInjection($code, $filePath);

// Detect XSS
$vulnerabilities = $scanner->detectXSS($code, $filePath);

// Detect missing CSRF protection
$vulnerabilities = $scanner->detectCSRFMissing($code, $filePath);

// Detect insecure authentication
$vulnerabilities = $scanner->detectInsecureAuth($code, $filePath);

// Detect hardcoded credentials
$vulnerabilities = $scanner->detectHardcodedCredentials($code, $filePath);

// Detect missing validation
$vulnerabilities = $scanner->detectMissingValidation($code, $filePath);

// Detect insecure file uploads
$vulnerabilities = $scanner->detectInsecureFileUpload($code, $filePath);
```

### Custom Rules

You can provide custom security rules to the scanner:

```php
$customRules = [
    'CUSTOM_VULN' => [
        [
            'pattern' => '/dangerous_function\s*\(/',
            'severity' => 'HIGH',
            'description' => 'Use of dangerous function detected',
            'recommendation' => 'Use safe alternative function',
        ],
    ],
];

$scanner = new SecurityScanner($customRules);
```

### Testing

Run the SecurityScanner tests:

```bash
vendor/bin/phpunit tests/Libraries/Refactor/Security/SecurityScannerTest.php
```

Test coverage includes:
- All 7 vulnerability detection methods
- Module scanning with multiple files
- Line number accuracy
- Code snippet extraction and truncation
- Custom rules support
- Error handling for invalid patterns

**Test Results**: 22 tests, 49 assertions, all passing ✅

## Implementation Status

- [x] Task 5.1: Security rule definitions (COMPLETED)
- [x] Task 5.2: SecurityScanner implementation (COMPLETED)
- [x] Task 5.3: SecurityReport and Vulnerability models (COMPLETED)
- [x] Task 5.4: Unit tests (COMPLETED)

## Files

- `SecurityRules.php` - Security rule definitions and patterns
- `SecurityScanner.php` - Main security scanner implementation
- `TASK_5.1_COMPLETION_SUMMARY.md` - Task 5.1 completion documentation
- `TASK_5.2_COMPLETION_SUMMARY.md` - Task 5.2 completion documentation
- `README.md` - This file

## Related Components

- `app/Libraries/Refactor/Models/SecurityReport.php` - Security report data model
- `app/Libraries/Refactor/Models/Vulnerability.php` - Vulnerability data model
- `tests/Libraries/Refactor/Security/SecurityRulesTest.php` - SecurityRules tests
- `tests/Libraries/Refactor/Security/SecurityScannerTest.php` - SecurityScanner tests

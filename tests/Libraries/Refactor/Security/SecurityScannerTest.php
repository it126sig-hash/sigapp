<?php

namespace Tests\Libraries\Refactor\Security;

use App\Libraries\Refactor\Models\Module;
use App\Libraries\Refactor\Models\SecurityReport;
use App\Libraries\Refactor\Models\Vulnerability;
use App\Libraries\Refactor\Security\SecurityRules;
use App\Libraries\Refactor\Security\SecurityScanner;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Security Scanner Test
 * 
 * Tests the SecurityScanner class to ensure it correctly detects
 * security vulnerabilities in module code.
 */
class SecurityScannerTest extends CIUnitTestCase
{
    private SecurityScanner $scanner;
    private string $testFilesDir;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->scanner = new SecurityScanner();
        $this->testFilesDir = WRITEPATH . 'tests/security_scanner/';
        
        // Create test directory if it doesn't exist
        if (!is_dir($this->testFilesDir)) {
            mkdir($this->testFilesDir, 0777, true);
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        
        // Clean up test files
        if (is_dir($this->testFilesDir)) {
            $this->deleteDirectory($this->testFilesDir);
        }
    }

    private function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }

    private function createTestFile(string $filename, string $content): string
    {
        $filePath = $this->testFilesDir . $filename;
        file_put_contents($filePath, $content);
        return $filePath;
    }

    public function testConstructorWithDefaultRules(): void
    {
        $scanner = new SecurityScanner();
        $rules = $scanner->getRules();
        
        $this->assertIsArray($rules);
        $this->assertNotEmpty($rules);
        $this->assertArrayHasKey('SQL_INJECTION', $rules);
    }

    public function testConstructorWithCustomRules(): void
    {
        $customRules = [
            'CUSTOM_VULN' => [
                [
                    'pattern' => '/test/',
                    'severity' => 'HIGH',
                    'description' => 'Test vulnerability',
                    'recommendation' => 'Fix it',
                ],
            ],
        ];
        
        $scanner = new SecurityScanner($customRules);
        $rules = $scanner->getRules();
        
        $this->assertArrayHasKey('CUSTOM_VULN', $rules);
    }

    public function testDetectSQLInjectionWithRawQuery(): void
    {
        $code = '<?php
        class TestController {
            public function index() {
                $id = $_GET["id"];
                $this->db->query("SELECT * FROM users WHERE id = $id");
            }
        }';
        
        $filePath = $this->createTestFile('sql_injection.php', $code);
        $vulnerabilities = $this->scanner->detectSQLInjection($code, $filePath);
        
        $this->assertNotEmpty($vulnerabilities);
        $this->assertInstanceOf(Vulnerability::class, $vulnerabilities[0]);
        $this->assertEquals(Vulnerability::TYPE_SQL_INJECTION, $vulnerabilities[0]->type);
        $this->assertEquals('CRITICAL', $vulnerabilities[0]->severity);
    }

    public function testDetectSQLInjectionWithQueryBuilder(): void
    {
        $code = '<?php
        class TestController {
            public function index() {
                $id = $_GET["id"];
                $this->db->table("users")->where("id", $id)->get();
            }
        }';
        
        $filePath = $this->createTestFile('safe_query.php', $code);
        $vulnerabilities = $this->scanner->detectSQLInjection($code, $filePath);
        
        // Should not detect vulnerability in properly parameterized query
        $this->assertEmpty($vulnerabilities);
    }

    public function testDetectXSSWithUnescapedEcho(): void
    {
        $code = '<?php
        $userInput = $_GET["name"];
        echo $userInput;
        ';
        
        $filePath = $this->createTestFile('xss_vuln.php', $code);
        $vulnerabilities = $this->scanner->detectXSS($code, $filePath);
        
        $this->assertNotEmpty($vulnerabilities);
        $this->assertEquals(Vulnerability::TYPE_XSS, $vulnerabilities[0]->type);
    }

    public function testDetectXSSWithEscapedOutput(): void
    {
        $code = '<?php
        $userInput = $_GET["name"];
        echo esc($userInput);
        ';
        
        $filePath = $this->createTestFile('safe_output.php', $code);
        $vulnerabilities = $this->scanner->detectXSS($code, $filePath);
        
        // Should not detect vulnerability in escaped output
        $this->assertEmpty($vulnerabilities);
    }

    public function testDetectCSRFMissingInForm(): void
    {
        $code = '<form method="post" action="/submit">
            <input type="text" name="username">
            <button type="submit">Submit</button>
        </form>';
        
        $filePath = $this->createTestFile('csrf_missing.php', $code);
        $vulnerabilities = $this->scanner->detectCSRFMissing($code, $filePath);
        
        $this->assertNotEmpty($vulnerabilities);
        $this->assertEquals(Vulnerability::TYPE_CSRF, $vulnerabilities[0]->type);
    }

    public function testDetectInsecureAuthWithPlainTextComparison(): void
    {
        $code = '<?php
        if ($password == $userPassword) {
            $_SESSION["logged_in"] = true;
        }
        ';
        
        $filePath = $this->createTestFile('insecure_auth.php', $code);
        $vulnerabilities = $this->scanner->detectInsecureAuth($code, $filePath);
        
        $this->assertNotEmpty($vulnerabilities);
        $this->assertEquals(Vulnerability::TYPE_INSECURE_AUTH, $vulnerabilities[0]->type);
        $this->assertEquals('CRITICAL', $vulnerabilities[0]->severity);
    }

    public function testDetectInsecureAuthWithMD5(): void
    {
        $code = '<?php
        $hashedPassword = md5($password);
        ';
        
        $filePath = $this->createTestFile('md5_hash.php', $code);
        $vulnerabilities = $this->scanner->detectInsecureAuth($code, $filePath);
        
        $this->assertNotEmpty($vulnerabilities);
        $this->assertEquals(Vulnerability::TYPE_INSECURE_AUTH, $vulnerabilities[0]->type);
    }

    public function testDetectHardcodedCredentials(): void
    {
        $code = '<?php
        $password = "mySecretPassword123";
        $apiKey = "sk_live_1234567890abcdef";
        ';
        
        $filePath = $this->createTestFile('hardcoded_creds.php', $code);
        $vulnerabilities = $this->scanner->detectHardcodedCredentials($code, $filePath);
        
        $this->assertNotEmpty($vulnerabilities);
        $this->assertEquals(Vulnerability::TYPE_HARDCODED_CREDENTIALS, $vulnerabilities[0]->type);
    }

    public function testDetectMissingValidation(): void
    {
        $code = '<?php
        class TestController {
            public function save() {
                $data = $this->request->getPost();
                $this->model->insert($data);
            }
        }';
        
        $filePath = $this->createTestFile('missing_validation.php', $code);
        $vulnerabilities = $this->scanner->detectMissingValidation($code, $filePath);
        
        $this->assertNotEmpty($vulnerabilities);
        $this->assertEquals(Vulnerability::TYPE_MISSING_VALIDATION, $vulnerabilities[0]->type);
    }

    public function testDetectInsecureFileUpload(): void
    {
        $code = '<?php
        $file = $this->request->getFile("upload");
        $file->move(WRITEPATH . "uploads");
        ';
        
        $filePath = $this->createTestFile('insecure_upload.php', $code);
        $vulnerabilities = $this->scanner->detectInsecureFileUpload($code, $filePath);
        
        $this->assertNotEmpty($vulnerabilities);
        $this->assertEquals(Vulnerability::TYPE_INSECURE_FILE_UPLOAD, $vulnerabilities[0]->type);
    }

    public function testScanModuleWithController(): void
    {
        $controllerCode = '<?php
        namespace App\Controllers;
        
        class TestController {
            public function index() {
                $id = $_GET["id"];
                $this->db->query("SELECT * FROM users WHERE id = $id");
                echo $id;
            }
        }';
        
        $controllerPath = $this->createTestFile('TestController.php', $controllerCode);
        
        $module = new Module('Test', $controllerPath);
        $report = $this->scanner->scanModule($module);
        
        $this->assertInstanceOf(SecurityReport::class, $report);
        $this->assertEquals('Test', $report->moduleName);
        $this->assertGreaterThan(0, $report->getTotalCount());
    }

    public function testScanModuleWithMultipleFiles(): void
    {
        $controllerCode = '<?php
        class TestController {
            public function save() {
                $data = $this->request->getPost();
                $this->model->insert($data);
            }
        }';
        
        $modelCode = '<?php
        class TestModel {
            public function getData($id) {
                return $this->db->query("SELECT * FROM test WHERE id = $id");
            }
        }';
        
        $controllerPath = $this->createTestFile('TestController.php', $controllerCode);
        $modelPath = $this->createTestFile('TestModel.php', $modelCode);
        
        $module = new Module('Test', $controllerPath);
        $module->modelPaths = [$modelPath];
        
        $report = $this->scanner->scanModule($module);
        
        $this->assertGreaterThan(0, $report->getTotalCount());
        
        // Should find vulnerabilities in both controller and model
        $sqlInjectionVulns = array_filter(
            $report->vulnerabilities,
            fn($v) => $v->type === Vulnerability::TYPE_SQL_INJECTION
        );
        $this->assertNotEmpty($sqlInjectionVulns);
    }

    public function testScanModuleWithNonExistentFile(): void
    {
        $module = new Module('Test', '/non/existent/file.php');
        $report = $this->scanner->scanModule($module);
        
        $this->assertInstanceOf(SecurityReport::class, $report);
        $this->assertEquals(0, $report->getTotalCount());
    }

    public function testVulnerabilityHasCorrectLineNumber(): void
    {
        $code = '<?php
        // Line 2
        // Line 3
        $id = $_GET["id"];
        $this->db->query("SELECT * FROM users WHERE id = $id"); // Line 5
        ';
        
        $filePath = $this->createTestFile('line_number_test.php', $code);
        $vulnerabilities = $this->scanner->detectSQLInjection($code, $filePath);
        
        $this->assertNotEmpty($vulnerabilities);
        $this->assertEquals(5, $vulnerabilities[0]->lineNumber);
    }

    public function testVulnerabilityHasCodeSnippet(): void
    {
        $code = '<?php
        $this->db->query("SELECT * FROM users WHERE id = $id");
        ';
        
        $filePath = $this->createTestFile('snippet_test.php', $code);
        $vulnerabilities = $this->scanner->detectSQLInjection($code, $filePath);
        
        $this->assertNotEmpty($vulnerabilities);
        $this->assertNotNull($vulnerabilities[0]->codeSnippet);
        $this->assertStringContainsString('SELECT', $vulnerabilities[0]->codeSnippet);
    }

    public function testCodeSnippetIsTruncatedIfTooLong(): void
    {
        $longQuery = str_repeat('a', 300);
        $code = "<?php \$id = \$_GET['id']; \$this->db->query(\"SELECT * FROM users WHERE id = \$id AND name = '{$longQuery}'\");";
        
        $filePath = $this->createTestFile('long_snippet.php', $code);
        $vulnerabilities = $this->scanner->detectSQLInjection($code, $filePath);
        
        $this->assertNotEmpty($vulnerabilities, 'Should detect SQL injection vulnerability');
        $this->assertLessThanOrEqual(203, strlen($vulnerabilities[0]->codeSnippet)); // 200 + '...'
    }

    public function testSetRulesUpdatesRules(): void
    {
        $newRules = [
            'TEST_VULN' => [
                [
                    'pattern' => '/test_pattern/',
                    'severity' => 'LOW',
                    'description' => 'Test',
                    'recommendation' => 'Fix',
                ],
            ],
        ];
        
        $this->scanner->setRules($newRules);
        $rules = $this->scanner->getRules();
        
        $this->assertArrayHasKey('TEST_VULN', $rules);
        $this->assertArrayNotHasKey('SQL_INJECTION', $rules);
    }

    public function testScanModuleWithServiceAndRepository(): void
    {
        $serviceCode = '<?php
        class TestService {
            public function process($data) {
                echo $data;
            }
        }';
        
        $repositoryCode = '<?php
        class TestRepository {
            public function find($id) {
                return $this->db->query("SELECT * FROM test WHERE id = $id");
            }
        }';
        
        $controllerPath = $this->createTestFile('TestController.php', '<?php class TestController {}');
        $servicePath = $this->createTestFile('TestService.php', $serviceCode);
        $repositoryPath = $this->createTestFile('TestRepository.php', $repositoryCode);
        
        $module = new Module('Test', $controllerPath);
        $module->servicePath = $servicePath;
        $module->repositoryPath = $repositoryPath;
        
        $report = $this->scanner->scanModule($module);
        
        $this->assertGreaterThan(0, $report->getTotalCount());
    }

    public function testMultipleVulnerabilitiesInSameFile(): void
    {
        $code = '<?php
        $id = $_GET["id"];
        $this->db->query("SELECT * FROM users WHERE id = $id");
        echo $id;
        $password = "hardcoded123";
        ';
        
        $filePath = $this->createTestFile('multiple_vulns.php', $code);
        
        $module = new Module('Test', $filePath);
        $report = $this->scanner->scanModule($module);
        
        $this->assertGreaterThanOrEqual(3, $report->getTotalCount());
        
        // Should have SQL injection, XSS, and hardcoded credentials
        $types = array_map(fn($v) => $v->type, $report->vulnerabilities);
        $this->assertContains(Vulnerability::TYPE_SQL_INJECTION, $types);
        $this->assertContains(Vulnerability::TYPE_XSS, $types);
        $this->assertContains(Vulnerability::TYPE_HARDCODED_CREDENTIALS, $types);
    }

    public function testInvalidRegexPatternIsHandledGracefully(): void
    {
        $invalidRules = [
            'TEST' => [
                [
                    'pattern' => '/[invalid(regex/',
                    'severity' => 'HIGH',
                    'description' => 'Test',
                    'recommendation' => 'Fix',
                ],
            ],
        ];
        
        $scanner = new SecurityScanner($invalidRules);
        $code = '<?php echo "test";';
        $filePath = $this->createTestFile('invalid_regex.php', $code);
        
        // Should not throw exception
        $vulnerabilities = $scanner->detectSQLInjection($code, $filePath);
        $this->assertIsArray($vulnerabilities);
    }
}

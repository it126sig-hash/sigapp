<?php

namespace Tests\Libraries\Refactor\Security;

use App\Libraries\Refactor\Security\SecurityRules;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Security Rules Test
 * 
 * Tests the security rule definitions to ensure they are properly structured
 * and contain all required vulnerability detection patterns.
 */
class SecurityRulesTest extends CIUnitTestCase
{
    public function testGetAllRulesReturnsArray(): void
    {
        $rules = SecurityRules::getAllRules();
        
        $this->assertIsArray($rules);
        $this->assertNotEmpty($rules);
    }

    public function testGetAllRulesContainsAllVulnerabilityTypes(): void
    {
        $rules = SecurityRules::getAllRules();
        
        $expectedTypes = [
            'SQL_INJECTION',
            'XSS',
            'CSRF',
            'INSECURE_AUTH',
            'HARDCODED_CREDENTIALS',
            'MISSING_VALIDATION',
            'INSECURE_FILE_UPLOAD',
        ];
        
        foreach ($expectedTypes as $type) {
            $this->assertArrayHasKey($type, $rules, "Missing vulnerability type: {$type}");
        }
    }

    public function testSQLInjectionRulesAreNotEmpty(): void
    {
        $rules = SecurityRules::getSQLInjectionRules();
        
        $this->assertIsArray($rules);
        $this->assertNotEmpty($rules);
        $this->assertGreaterThanOrEqual(5, count($rules), 'Should have at least 5 SQL injection rules');
    }

    public function testXSSRulesAreNotEmpty(): void
    {
        $rules = SecurityRules::getXSSRules();
        
        $this->assertIsArray($rules);
        $this->assertNotEmpty($rules);
        $this->assertGreaterThanOrEqual(5, count($rules), 'Should have at least 5 XSS rules');
    }

    public function testCSRFRulesAreNotEmpty(): void
    {
        $rules = SecurityRules::getCSRFRules();
        
        $this->assertIsArray($rules);
        $this->assertNotEmpty($rules);
        $this->assertGreaterThanOrEqual(3, count($rules), 'Should have at least 3 CSRF rules');
    }

    public function testInsecureAuthRulesAreNotEmpty(): void
    {
        $rules = SecurityRules::getInsecureAuthRules();
        
        $this->assertIsArray($rules);
        $this->assertNotEmpty($rules);
        $this->assertGreaterThanOrEqual(5, count($rules), 'Should have at least 5 insecure auth rules');
    }

    public function testHardcodedCredentialsRulesAreNotEmpty(): void
    {
        $rules = SecurityRules::getHardcodedCredentialsRules();
        
        $this->assertIsArray($rules);
        $this->assertNotEmpty($rules);
        $this->assertGreaterThanOrEqual(5, count($rules), 'Should have at least 5 hardcoded credentials rules');
    }

    public function testMissingValidationRulesAreNotEmpty(): void
    {
        $rules = SecurityRules::getMissingValidationRules();
        
        $this->assertIsArray($rules);
        $this->assertNotEmpty($rules);
        $this->assertGreaterThanOrEqual(5, count($rules), 'Should have at least 5 missing validation rules');
    }

    public function testInsecureFileUploadRulesAreNotEmpty(): void
    {
        $rules = SecurityRules::getInsecureFileUploadRules();
        
        $this->assertIsArray($rules);
        $this->assertNotEmpty($rules);
        $this->assertGreaterThanOrEqual(5, count($rules), 'Should have at least 5 insecure file upload rules');
    }

    public function testEachRuleHasRequiredFields(): void
    {
        $allRules = SecurityRules::getAllRules();
        
        foreach ($allRules as $type => $rules) {
            foreach ($rules as $index => $rule) {
                $this->assertArrayHasKey('pattern', $rule, "{$type}[{$index}] missing 'pattern'");
                $this->assertArrayHasKey('severity', $rule, "{$type}[{$index}] missing 'severity'");
                $this->assertArrayHasKey('description', $rule, "{$type}[{$index}] missing 'description'");
                $this->assertArrayHasKey('recommendation', $rule, "{$type}[{$index}] missing 'recommendation'");
                
                $this->assertNotEmpty($rule['pattern'], "{$type}[{$index}] pattern is empty");
                $this->assertNotEmpty($rule['severity'], "{$type}[{$index}] severity is empty");
                $this->assertNotEmpty($rule['description'], "{$type}[{$index}] description is empty");
                $this->assertNotEmpty($rule['recommendation'], "{$type}[{$index}] recommendation is empty");
            }
        }
    }

    public function testSeverityLevelsAreValid(): void
    {
        $allRules = SecurityRules::getAllRules();
        $validSeverities = ['CRITICAL', 'HIGH', 'MEDIUM', 'LOW'];
        
        foreach ($allRules as $type => $rules) {
            foreach ($rules as $index => $rule) {
                $this->assertContains(
                    $rule['severity'],
                    $validSeverities,
                    "{$type}[{$index}] has invalid severity: {$rule['severity']}"
                );
            }
        }
    }

    public function testRegexPatternsAreValid(): void
    {
        $allRules = SecurityRules::getAllRules();
        
        foreach ($allRules as $type => $rules) {
            foreach ($rules as $index => $rule) {
                $pattern = $rule['pattern'];
                
                // Suppress warnings for invalid regex
                $result = @preg_match($pattern, '');
                
                $this->assertNotFalse(
                    $result,
                    "{$type}[{$index}] has invalid regex pattern: {$pattern}"
                );
            }
        }
    }

    public function testGetSeverityLevelReturnsCorrectValues(): void
    {
        $this->assertEquals(4, SecurityRules::getSeverityLevel('CRITICAL'));
        $this->assertEquals(3, SecurityRules::getSeverityLevel('HIGH'));
        $this->assertEquals(2, SecurityRules::getSeverityLevel('MEDIUM'));
        $this->assertEquals(1, SecurityRules::getSeverityLevel('LOW'));
        $this->assertEquals(0, SecurityRules::getSeverityLevel('UNKNOWN'));
    }

    public function testGetVulnerabilityTypesReturnsArray(): void
    {
        $types = SecurityRules::getVulnerabilityTypes();
        
        $this->assertIsArray($types);
        $this->assertCount(7, $types);
        $this->assertContains('SQL_INJECTION', $types);
        $this->assertContains('XSS', $types);
        $this->assertContains('CSRF', $types);
    }

    public function testGetSeverityLevelsReturnsArray(): void
    {
        $levels = SecurityRules::getSeverityLevels();
        
        $this->assertIsArray($levels);
        $this->assertCount(4, $levels);
        $this->assertContains('CRITICAL', $levels);
        $this->assertContains('HIGH', $levels);
        $this->assertContains('MEDIUM', $levels);
        $this->assertContains('LOW', $levels);
    }

    public function testSQLInjectionPatternDetectsRawQuery(): void
    {
        $rules = SecurityRules::getSQLInjectionRules();
        $vulnerableCode = '$this->db->query("SELECT * FROM users WHERE id = $id");';
        
        $matched = false;
        foreach ($rules as $rule) {
            if (preg_match($rule['pattern'], $vulnerableCode)) {
                $matched = true;
                break;
            }
        }
        
        $this->assertTrue($matched, 'SQL injection pattern should detect raw query with variable');
    }

    public function testXSSPatternDetectsUnescapedEcho(): void
    {
        $rules = SecurityRules::getXSSRules();
        $vulnerableCode = 'echo $userInput;';
        
        $matched = false;
        foreach ($rules as $rule) {
            if (preg_match($rule['pattern'], $vulnerableCode)) {
                $matched = true;
                break;
            }
        }
        
        $this->assertTrue($matched, 'XSS pattern should detect unescaped echo');
    }

    public function testCSRFPatternDetectsFormWithoutToken(): void
    {
        $rules = SecurityRules::getCSRFRules();
        $vulnerableCode = '<form method="post" action="/submit"><input name="data"></form>';
        
        $matched = false;
        foreach ($rules as $rule) {
            if (preg_match($rule['pattern'], $vulnerableCode)) {
                $matched = true;
                break;
            }
        }
        
        $this->assertTrue($matched, 'CSRF pattern should detect form without CSRF token');
    }

    public function testHardcodedCredentialsPatternDetectsPassword(): void
    {
        $rules = SecurityRules::getHardcodedCredentialsRules();
        $vulnerableCode = '$password = "mySecretPassword123";';
        
        $matched = false;
        foreach ($rules as $rule) {
            if (preg_match($rule['pattern'], $vulnerableCode)) {
                $matched = true;
                break;
            }
        }
        
        $this->assertTrue($matched, 'Hardcoded credentials pattern should detect hardcoded password');
    }

    public function testMissingValidationPatternDetectsSaveWithoutValidation(): void
    {
        $rules = SecurityRules::getMissingValidationRules();
        $vulnerableCode = 'public function save() { $data = $this->request->getPost(); }';
        
        $matched = false;
        foreach ($rules as $rule) {
            if (preg_match($rule['pattern'], $vulnerableCode)) {
                $matched = true;
                break;
            }
        }
        
        $this->assertTrue($matched, 'Missing validation pattern should detect save method without validation');
    }

    public function testInsecureFileUploadPatternDetectsUnvalidatedUpload(): void
    {
        $rules = SecurityRules::getInsecureFileUploadRules();
        $vulnerableCode = '$file = $this->request->getFile("upload"); $file->move(WRITEPATH);';
        
        $matched = false;
        foreach ($rules as $rule) {
            if (preg_match($rule['pattern'], $vulnerableCode)) {
                $matched = true;
                break;
            }
        }
        
        $this->assertTrue($matched, 'Insecure file upload pattern should detect unvalidated file upload');
    }
}

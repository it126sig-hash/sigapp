<?php

namespace Tests\Unit\Refactor\Models;

use App\Libraries\Refactor\Models\SecurityReport;
use App\Libraries\Refactor\Models\Vulnerability;
use CodeIgniter\Test\CIUnitTestCase;
use DateTime;

/**
 * Unit tests for SecurityReport data model
 * 
 * @package Tests\Unit\Refactor\Models
 */
class SecurityReportTest extends CIUnitTestCase
{
    public function testConstructorSetsModuleNameAndTimestamp(): void
    {
        $before = new DateTime();
        $report = new SecurityReport('TestModule');
        $after = new DateTime();

        $this->assertSame('TestModule', $report->moduleName);
        $this->assertInstanceOf(DateTime::class, $report->scannedAt);
        $this->assertGreaterThanOrEqual($before->getTimestamp(), $report->scannedAt->getTimestamp());
        $this->assertLessThanOrEqual($after->getTimestamp(), $report->scannedAt->getTimestamp());
    }

    public function testConstructorInitializesEmptyVulnerabilitiesArray(): void
    {
        $report = new SecurityReport('TestModule');

        $this->assertIsArray($report->vulnerabilities);
        $this->assertEmpty($report->vulnerabilities);
    }

    public function testAddVulnerabilityAddsToArray(): void
    {
        $report = new SecurityReport('TestModule');
        $vulnerability = new Vulnerability(
            Vulnerability::TYPE_SQL_INJECTION,
            Vulnerability::SEVERITY_CRITICAL,
            '/app/Controllers/Test.php',
            42,
            'SQL injection detected',
            'Use Query Builder'
        );

        $report->addVulnerability($vulnerability);

        $this->assertCount(1, $report->vulnerabilities);
        $this->assertSame($vulnerability, $report->vulnerabilities[0]);
    }

    public function testAddMultipleVulnerabilities(): void
    {
        $report = new SecurityReport('TestModule');
        
        $vuln1 = new Vulnerability(
            Vulnerability::TYPE_SQL_INJECTION,
            Vulnerability::SEVERITY_CRITICAL,
            '/app/Controllers/Test.php',
            42,
            'SQL injection',
            'Use Query Builder'
        );
        
        $vuln2 = new Vulnerability(
            Vulnerability::TYPE_XSS,
            Vulnerability::SEVERITY_HIGH,
            '/app/Views/test.php',
            10,
            'XSS vulnerability',
            'Use esc() helper'
        );

        $report->addVulnerability($vuln1);
        $report->addVulnerability($vuln2);

        $this->assertCount(2, $report->vulnerabilities);
        $this->assertSame($vuln1, $report->vulnerabilities[0]);
        $this->assertSame($vuln2, $report->vulnerabilities[1]);
    }

    public function testGetBySeverityFiltersCritical(): void
    {
        $report = new SecurityReport('TestModule');
        
        $critical = new Vulnerability(
            Vulnerability::TYPE_SQL_INJECTION,
            Vulnerability::SEVERITY_CRITICAL,
            '/app/Controllers/Test.php',
            42,
            'Critical issue',
            'Fix it'
        );
        
        $high = new Vulnerability(
            Vulnerability::TYPE_XSS,
            Vulnerability::SEVERITY_HIGH,
            '/app/Views/test.php',
            10,
            'High issue',
            'Fix it'
        );

        $report->addVulnerability($critical);
        $report->addVulnerability($high);

        $criticalVulns = $report->getBySeverity(Vulnerability::SEVERITY_CRITICAL);

        $this->assertCount(1, $criticalVulns);
        $this->assertSame($critical, $criticalVulns[0]);
    }

    public function testGetBySeverityFiltersHigh(): void
    {
        $report = new SecurityReport('TestModule');
        
        $high1 = new Vulnerability(
            Vulnerability::TYPE_XSS,
            Vulnerability::SEVERITY_HIGH,
            '/app/Views/test.php',
            10,
            'High issue 1',
            'Fix it'
        );
        
        $high2 = new Vulnerability(
            Vulnerability::TYPE_CSRF,
            Vulnerability::SEVERITY_HIGH,
            '/app/Controllers/Test.php',
            20,
            'High issue 2',
            'Fix it'
        );
        
        $medium = new Vulnerability(
            Vulnerability::TYPE_MISSING_VALIDATION,
            Vulnerability::SEVERITY_MEDIUM,
            '/app/Controllers/Test.php',
            30,
            'Medium issue',
            'Fix it'
        );

        $report->addVulnerability($high1);
        $report->addVulnerability($high2);
        $report->addVulnerability($medium);

        $highVulns = $report->getBySeverity(Vulnerability::SEVERITY_HIGH);

        $this->assertCount(2, $highVulns);
    }

    public function testGetBySeverityReturnsEmptyArrayWhenNoMatches(): void
    {
        $report = new SecurityReport('TestModule');
        
        $high = new Vulnerability(
            Vulnerability::TYPE_XSS,
            Vulnerability::SEVERITY_HIGH,
            '/app/Views/test.php',
            10,
            'High issue',
            'Fix it'
        );

        $report->addVulnerability($high);

        $criticalVulns = $report->getBySeverity(Vulnerability::SEVERITY_CRITICAL);

        $this->assertIsArray($criticalVulns);
        $this->assertEmpty($criticalVulns);
    }

    public function testGetCriticalCountReturnsCorrectCount(): void
    {
        $report = new SecurityReport('TestModule');
        
        $report->addVulnerability(new Vulnerability(
            Vulnerability::TYPE_SQL_INJECTION,
            Vulnerability::SEVERITY_CRITICAL,
            '/app/Controllers/Test.php',
            42,
            'Critical 1',
            'Fix it'
        ));
        
        $report->addVulnerability(new Vulnerability(
            Vulnerability::TYPE_HARDCODED_CREDENTIALS,
            Vulnerability::SEVERITY_CRITICAL,
            '/app/Config/Database.php',
            15,
            'Critical 2',
            'Fix it'
        ));
        
        $report->addVulnerability(new Vulnerability(
            Vulnerability::TYPE_XSS,
            Vulnerability::SEVERITY_HIGH,
            '/app/Views/test.php',
            10,
            'High issue',
            'Fix it'
        ));

        $this->assertSame(2, $report->getCriticalCount());
    }

    public function testGetCriticalCountReturnsZeroWhenNoCritical(): void
    {
        $report = new SecurityReport('TestModule');
        
        $report->addVulnerability(new Vulnerability(
            Vulnerability::TYPE_XSS,
            Vulnerability::SEVERITY_HIGH,
            '/app/Views/test.php',
            10,
            'High issue',
            'Fix it'
        ));

        $this->assertSame(0, $report->getCriticalCount());
    }

    public function testGetHighCountReturnsCorrectCount(): void
    {
        $report = new SecurityReport('TestModule');
        
        $report->addVulnerability(new Vulnerability(
            Vulnerability::TYPE_XSS,
            Vulnerability::SEVERITY_HIGH,
            '/app/Views/test.php',
            10,
            'High 1',
            'Fix it'
        ));
        
        $report->addVulnerability(new Vulnerability(
            Vulnerability::TYPE_CSRF,
            Vulnerability::SEVERITY_HIGH,
            '/app/Controllers/Test.php',
            20,
            'High 2',
            'Fix it'
        ));

        $this->assertSame(2, $report->getHighCount());
    }

    public function testGetMediumCountReturnsCorrectCount(): void
    {
        $report = new SecurityReport('TestModule');
        
        $report->addVulnerability(new Vulnerability(
            Vulnerability::TYPE_MISSING_VALIDATION,
            Vulnerability::SEVERITY_MEDIUM,
            '/app/Controllers/Test.php',
            30,
            'Medium issue',
            'Fix it'
        ));

        $this->assertSame(1, $report->getMediumCount());
    }

    public function testGetLowCountReturnsCorrectCount(): void
    {
        $report = new SecurityReport('TestModule');
        
        $report->addVulnerability(new Vulnerability(
            Vulnerability::TYPE_MISSING_VALIDATION,
            Vulnerability::SEVERITY_LOW,
            '/app/Controllers/Test.php',
            30,
            'Low issue',
            'Fix it'
        ));

        $this->assertSame(1, $report->getLowCount());
    }

    public function testGetTotalCountReturnsCorrectCount(): void
    {
        $report = new SecurityReport('TestModule');
        
        $report->addVulnerability(new Vulnerability(
            Vulnerability::TYPE_SQL_INJECTION,
            Vulnerability::SEVERITY_CRITICAL,
            '/app/Controllers/Test.php',
            42,
            'Critical',
            'Fix it'
        ));
        
        $report->addVulnerability(new Vulnerability(
            Vulnerability::TYPE_XSS,
            Vulnerability::SEVERITY_HIGH,
            '/app/Views/test.php',
            10,
            'High',
            'Fix it'
        ));
        
        $report->addVulnerability(new Vulnerability(
            Vulnerability::TYPE_MISSING_VALIDATION,
            Vulnerability::SEVERITY_MEDIUM,
            '/app/Controllers/Test.php',
            30,
            'Medium',
            'Fix it'
        ));

        $this->assertSame(3, $report->getTotalCount());
    }

    public function testHasCriticalVulnerabilitiesReturnsTrueWhenCriticalExists(): void
    {
        $report = new SecurityReport('TestModule');
        
        $report->addVulnerability(new Vulnerability(
            Vulnerability::TYPE_SQL_INJECTION,
            Vulnerability::SEVERITY_CRITICAL,
            '/app/Controllers/Test.php',
            42,
            'Critical issue',
            'Fix it'
        ));

        $this->assertTrue($report->hasCriticalVulnerabilities());
    }

    public function testHasCriticalVulnerabilitiesReturnsFalseWhenNoCritical(): void
    {
        $report = new SecurityReport('TestModule');
        
        $report->addVulnerability(new Vulnerability(
            Vulnerability::TYPE_XSS,
            Vulnerability::SEVERITY_HIGH,
            '/app/Views/test.php',
            10,
            'High issue',
            'Fix it'
        ));

        $this->assertFalse($report->hasCriticalVulnerabilities());
    }

    public function testToJsonReturnsValidJson(): void
    {
        $report = new SecurityReport('TestModule');
        
        $vulnerability = new Vulnerability(
            Vulnerability::TYPE_SQL_INJECTION,
            Vulnerability::SEVERITY_CRITICAL,
            '/app/Controllers/Test.php',
            42,
            'SQL injection detected',
            'Use Query Builder'
        );
        $vulnerability->codeSnippet = 'SELECT * FROM users';

        $report->addVulnerability($vulnerability);

        $json = $report->toJson();

        $this->assertJson($json);
        
        $decoded = json_decode($json, true);
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('moduleName', $decoded);
        $this->assertArrayHasKey('scannedAt', $decoded);
        $this->assertArrayHasKey('vulnerabilities', $decoded);
        $this->assertSame('TestModule', $decoded['moduleName']);
        $this->assertIsArray($decoded['vulnerabilities']);
        $this->assertCount(1, $decoded['vulnerabilities']);
    }

    public function testToJsonIncludesVulnerabilityDetails(): void
    {
        $report = new SecurityReport('TestModule');
        
        $vulnerability = new Vulnerability(
            Vulnerability::TYPE_XSS,
            Vulnerability::SEVERITY_HIGH,
            '/app/Views/test.php',
            10,
            'XSS vulnerability',
            'Use esc() helper'
        );

        $report->addVulnerability($vulnerability);

        $json = $report->toJson();
        $decoded = json_decode($json, true);

        $vuln = $decoded['vulnerabilities'][0];
        $this->assertSame(Vulnerability::TYPE_XSS, $vuln['type']);
        $this->assertSame(Vulnerability::SEVERITY_HIGH, $vuln['severity']);
        $this->assertSame('/app/Views/test.php', $vuln['filePath']);
        $this->assertSame(10, $vuln['lineNumber']);
        $this->assertSame('XSS vulnerability', $vuln['description']);
        $this->assertSame('Use esc() helper', $vuln['recommendation']);
    }

    public function testFromJsonCreatesSecurityReportInstance(): void
    {
        $json = json_encode([
            'moduleName' => 'TestModule',
            'scannedAt' => '2024-01-15T10:30:00+00:00',
            'vulnerabilities' => [
                [
                    'type' => Vulnerability::TYPE_SQL_INJECTION,
                    'severity' => Vulnerability::SEVERITY_CRITICAL,
                    'filePath' => '/app/Controllers/Test.php',
                    'lineNumber' => 42,
                    'description' => 'SQL injection detected',
                    'recommendation' => 'Use Query Builder',
                    'codeSnippet' => 'SELECT * FROM users',
                ],
            ],
        ]);

        $report = SecurityReport::fromJson($json);

        $this->assertInstanceOf(SecurityReport::class, $report);
        $this->assertSame('TestModule', $report->moduleName);
        $this->assertInstanceOf(DateTime::class, $report->scannedAt);
        $this->assertCount(1, $report->vulnerabilities);
        $this->assertInstanceOf(Vulnerability::class, $report->vulnerabilities[0]);
    }

    public function testFromJsonHandlesEmptyVulnerabilities(): void
    {
        $json = json_encode([
            'moduleName' => 'TestModule',
            'scannedAt' => '2024-01-15T10:30:00+00:00',
            'vulnerabilities' => [],
        ]);

        $report = SecurityReport::fromJson($json);

        $this->assertSame('TestModule', $report->moduleName);
        $this->assertEmpty($report->vulnerabilities);
    }

    public function testFromJsonHandlesMissingVulnerabilities(): void
    {
        $json = json_encode([
            'moduleName' => 'TestModule',
            'scannedAt' => '2024-01-15T10:30:00+00:00',
        ]);

        $report = SecurityReport::fromJson($json);

        $this->assertSame('TestModule', $report->moduleName);
        $this->assertEmpty($report->vulnerabilities);
    }

    public function testRoundTripConversionToJsonAndBack(): void
    {
        $original = new SecurityReport('TestModule');
        
        $vuln1 = new Vulnerability(
            Vulnerability::TYPE_SQL_INJECTION,
            Vulnerability::SEVERITY_CRITICAL,
            '/app/Controllers/Test.php',
            42,
            'SQL injection',
            'Use Query Builder'
        );
        $vuln1->codeSnippet = 'SELECT * FROM users';
        
        $vuln2 = new Vulnerability(
            Vulnerability::TYPE_XSS,
            Vulnerability::SEVERITY_HIGH,
            '/app/Views/test.php',
            10,
            'XSS vulnerability',
            'Use esc() helper'
        );

        $original->addVulnerability($vuln1);
        $original->addVulnerability($vuln2);

        $json = $original->toJson();
        $restored = SecurityReport::fromJson($json);

        $this->assertSame($original->moduleName, $restored->moduleName);
        $this->assertCount(count($original->vulnerabilities), $restored->vulnerabilities);
        
        // Verify first vulnerability
        $this->assertSame($vuln1->type, $restored->vulnerabilities[0]->type);
        $this->assertSame($vuln1->severity, $restored->vulnerabilities[0]->severity);
        $this->assertSame($vuln1->filePath, $restored->vulnerabilities[0]->filePath);
        $this->assertSame($vuln1->lineNumber, $restored->vulnerabilities[0]->lineNumber);
        $this->assertSame($vuln1->description, $restored->vulnerabilities[0]->description);
        $this->assertSame($vuln1->recommendation, $restored->vulnerabilities[0]->recommendation);
        $this->assertSame($vuln1->codeSnippet, $restored->vulnerabilities[0]->codeSnippet);
        
        // Verify second vulnerability
        $this->assertSame($vuln2->type, $restored->vulnerabilities[1]->type);
        $this->assertSame($vuln2->severity, $restored->vulnerabilities[1]->severity);
    }

    public function testFromJsonThrowsExceptionOnInvalidJson(): void
    {
        $this->expectException(\JsonException::class);

        SecurityReport::fromJson('invalid json {');
    }

    public function testToJsonFormatsWithPrettyPrint(): void
    {
        $report = new SecurityReport('TestModule');
        
        $vulnerability = new Vulnerability(
            Vulnerability::TYPE_SQL_INJECTION,
            Vulnerability::SEVERITY_CRITICAL,
            '/app/Controllers/Test.php',
            42,
            'SQL injection',
            'Use Query Builder'
        );

        $report->addVulnerability($vulnerability);

        $json = $report->toJson();

        // Pretty printed JSON should contain newlines and indentation
        $this->assertStringContainsString("\n", $json);
        $this->assertStringContainsString('    ', $json);
    }
}

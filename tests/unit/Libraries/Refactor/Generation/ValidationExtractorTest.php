<?php

namespace Tests\Unit\Libraries\Refactor\Generation;

use App\Libraries\Refactor\Generation\CodeGenerator;
use App\Libraries\Refactor\Generation\ValidationExtractor;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * ValidationExtractor Unit Tests
 * 
 * Tests the ValidationExtractor utility for extracting validation rules from
 * controller code and converting them to validation rule classes.
 */
class ValidationExtractorTest extends CIUnitTestCase
{
    private ValidationExtractor $extractor;
    private CodeGenerator $codeGenerator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->codeGenerator = new CodeGenerator();
        $this->extractor = new ValidationExtractor($this->codeGenerator);
    }

    /**
     * Test extracting validation rules from simple controller code
     */
    public function testExtractFromControllerSimple(): void
    {
        $controllerCode = <<<'PHP'
<?php
namespace App\Controllers;

class TestController extends BaseController
{
    public function store()
    {
        $rules = [
            'name' => 'required|max_length[255]',
            'email' => 'required|valid_email',
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back();
        }
    }
}
PHP;

        $result = $this->extractor->extractFromController($controllerCode);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals('store', $result[0]['method']);
        $this->assertArrayHasKey('name', $result[0]['rules']);
        $this->assertArrayHasKey('email', $result[0]['rules']);
        $this->assertEquals('required|max_length[255]', $result[0]['rules']['name']);
        $this->assertEquals('required|valid_email', $result[0]['rules']['email']);
    }

    /**
     * Test extracting validation rules from controller with multiple methods
     */
    public function testExtractFromControllerMultipleMethods(): void
    {
        $controllerCode = <<<'PHP'
<?php
namespace App\Controllers;

class TestController extends BaseController
{
    public function store()
    {
        $rules = [
            'name' => 'required|max_length[255]',
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back();
        }
    }
    
    public function update($id)
    {
        $rules = [
            'name' => 'required|max_length[255]',
            'status' => 'required|in_list[active,inactive]',
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back();
        }
    }
}
PHP;

        $result = $this->extractor->extractFromController($controllerCode);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        
        // First method (store)
        $this->assertEquals('store', $result[0]['method']);
        $this->assertCount(1, $result[0]['rules']);
        
        // Second method (update)
        $this->assertEquals('update', $result[1]['method']);
        $this->assertCount(2, $result[1]['rules']);
        $this->assertArrayHasKey('status', $result[1]['rules']);
    }

    /**
     * Test extracting validation rules with various rule types
     */
    public function testExtractVariousRuleTypes(): void
    {
        $controllerCode = <<<'PHP'
<?php
namespace App\Controllers;

class TestController extends BaseController
{
    public function store()
    {
        $rules = [
            'bank' => 'required|max_length[255]',
            'keterangan' => 'permit_empty|max_length[255]',
            'exp_days' => 'permit_empty|integer',
            'id_kavling' => 'permit_empty|is_natural_no_zero',
            'email' => 'required|valid_email|is_unique[users.email]',
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back();
        }
    }
}
PHP;

        $result = $this->extractor->extractFromController($controllerCode);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertCount(5, $result[0]['rules']);
        $this->assertEquals('required|max_length[255]', $result[0]['rules']['bank']);
        $this->assertEquals('permit_empty|max_length[255]', $result[0]['rules']['keterangan']);
        $this->assertEquals('permit_empty|integer', $result[0]['rules']['exp_days']);
        $this->assertEquals('permit_empty|is_natural_no_zero', $result[0]['rules']['id_kavling']);
        $this->assertEquals('required|valid_email|is_unique[users.email]', $result[0]['rules']['email']);
    }

    /**
     * Test extracting from controller with no validation rules
     */
    public function testExtractFromControllerNoRules(): void
    {
        $controllerCode = <<<'PHP'
<?php
namespace App\Controllers;

class TestController extends BaseController
{
    public function index()
    {
        return view('test/index');
    }
}
PHP;

        $result = $this->extractor->extractFromController($controllerCode);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test extracting from invalid PHP code
     */
    public function testExtractFromInvalidCode(): void
    {
        $invalidCode = "<?php this is not valid PHP code";

        $result = $this->extractor->extractFromController($invalidCode);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test converting inline validation to rule class
     */
    public function testConvertToRuleClass(): void
    {
        $rules = [
            'bank' => 'required|max_length[255]',
            'keterangan' => 'permit_empty|max_length[255]',
            'exp_days' => 'permit_empty|integer',
        ];

        $result = $this->extractor->convertToRuleClass('BankValidation', $rules);

        $this->assertIsString($result);
        $this->assertStringContainsString('namespace App\Validation;', $result);
        $this->assertStringContainsString('class BankValidation', $result);
        $this->assertStringContainsString('public static function getRules(): array', $result);
        $this->assertStringContainsString("'bank' => 'required|max_length[255]'", $result);
        $this->assertStringContainsString("'keterangan' => 'permit_empty|max_length[255]'", $result);
        $this->assertStringContainsString("'exp_days' => 'permit_empty|integer'", $result);
        
        // Check for individual field methods
        $this->assertStringContainsString('public static function getBankRules(): string', $result);
        $this->assertStringContainsString('public static function getKeteranganRules(): string', $result);
        $this->assertStringContainsString('public static function getExpDaysRules(): string', $result);
    }

    /**
     * Test converting to rule class with custom namespace
     */
    public function testConvertToRuleClassCustomNamespace(): void
    {
        $rules = [
            'name' => 'required|max_length[255]',
        ];

        $result = $this->extractor->convertToRuleClass(
            'CustomValidation',
            $rules,
            'App\\Custom\\Validation'
        );

        $this->assertStringContainsString('namespace App\Custom\Validation;', $result);
        $this->assertStringContainsString('class CustomValidation', $result);
    }

    /**
     * Test generating error messages for validation rules
     */
    public function testGenerateErrorMessages(): void
    {
        $rules = [
            'bank' => 'required|max_length[255]',
            'email' => 'required|valid_email',
            'exp_days' => 'permit_empty|integer',
        ];

        $result = $this->extractor->generateErrorMessages($rules, 'Bank');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('bank', $result);
        $this->assertArrayHasKey('email', $result);
        $this->assertArrayHasKey('exp_days', $result);
        
        // Check bank field messages
        $this->assertArrayHasKey('required', $result['bank']);
        $this->assertArrayHasKey('max_length', $result['bank']);
        $this->assertStringContainsString('Bank', $result['bank']['required']);
        $this->assertStringContainsString('255', $result['bank']['max_length']);
        
        // Check email field messages
        $this->assertArrayHasKey('required', $result['email']);
        $this->assertArrayHasKey('valid_email', $result['email']);
        
        // Check exp_days field messages (permit_empty should not have message)
        $this->assertArrayNotHasKey('permit_empty', $result['exp_days']);
        $this->assertArrayHasKey('integer', $result['exp_days']);
    }

    /**
     * Test generating error messages without context
     */
    public function testGenerateErrorMessagesNoContext(): void
    {
        $rules = [
            'name' => 'required',
        ];

        $result = $this->extractor->generateErrorMessages($rules);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('required', $result['name']);
        $this->assertStringNotContainsString('Bank', $result['name']['required']);
    }

    /**
     * Test generating error messages for various rule types
     */
    public function testGenerateErrorMessagesVariousRules(): void
    {
        $rules = [
            'username' => 'required|min_length[5]|max_length[20]|alpha_numeric',
            'password' => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]',
            'age' => 'required|integer|greater_than[17]',
            'status' => 'required|in_list[active,inactive]',
        ];

        $result = $this->extractor->generateErrorMessages($rules);

        $this->assertIsArray($result);
        
        // Username messages
        $this->assertArrayHasKey('username', $result);
        $this->assertArrayHasKey('min_length', $result['username']);
        $this->assertStringContainsString('5', $result['username']['min_length']);
        $this->assertArrayHasKey('alpha_numeric', $result['username']);
        
        // Password messages
        $this->assertArrayHasKey('password', $result);
        $this->assertArrayHasKey('min_length', $result['password']);
        $this->assertStringContainsString('8', $result['password']['min_length']);
        
        // Password confirm messages
        $this->assertArrayHasKey('password_confirm', $result);
        $this->assertArrayHasKey('matches', $result['password_confirm']);
        
        // Age messages
        $this->assertArrayHasKey('age', $result);
        $this->assertArrayHasKey('greater_than', $result['age']);
        $this->assertStringContainsString('17', $result['age']['greater_than']);
        
        // Status messages
        $this->assertArrayHasKey('status', $result);
        $this->assertArrayHasKey('in_list', $result['status']);
    }

    /**
     * Test generating language file content
     */
    public function testGenerateLanguageFile(): void
    {
        $messages = [
            'bank' => [
                'required' => 'Bank harus diisi.',
                'max_length' => 'Bank maksimal 255 karakter.',
            ],
            'email' => [
                'required' => 'Email harus diisi.',
                'valid_email' => 'Email harus berupa email yang valid.',
            ],
        ];

        $result = $this->extractor->generateLanguageFile($messages, 'Bank');

        $this->assertIsString($result);
        $this->assertStringContainsString('<?php', $result);
        $this->assertStringContainsString('Bank Validation Language File', $result);
        $this->assertStringContainsString("'bank.required' => 'Bank harus diisi.'", $result);
        $this->assertStringContainsString("'bank.max_length' => 'Bank maksimal 255 karakter.'", $result);
        $this->assertStringContainsString("'email.required' => 'Email harus diisi.'", $result);
        $this->assertStringContainsString("'email.valid_email' => 'Email harus berupa email yang valid.'", $result);
        $this->assertStringContainsString('return [', $result);
    }

    /**
     * Test extracting from multiple controller files
     */
    public function testExtractFromMultipleControllers(): void
    {
        // Create temporary controller files
        $tempDir = sys_get_temp_dir() . '/validation_test_' . uniqid();
        mkdir($tempDir);

        $controller1 = $tempDir . '/Controller1.php';
        $controller2 = $tempDir . '/Controller2.php';

        file_put_contents($controller1, <<<'PHP'
<?php
namespace App\Controllers;

class Controller1 extends BaseController
{
    public function store()
    {
        $rules = ['name' => 'required'];
        if (!$this->validate($rules)) {
            return redirect()->back();
        }
    }
}
PHP
        );

        file_put_contents($controller2, <<<'PHP'
<?php
namespace App\Controllers;

class Controller2 extends BaseController
{
    public function update()
    {
        $rules = ['email' => 'required|valid_email'];
        if (!$this->validate($rules)) {
            return redirect()->back();
        }
    }
}
PHP
        );

        $result = $this->extractor->extractFromMultipleControllers([$controller1, $controller2]);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('Controller1', $result);
        $this->assertArrayHasKey('Controller2', $result);
        $this->assertCount(1, $result['Controller1']);
        $this->assertCount(1, $result['Controller2']);

        // Cleanup
        unlink($controller1);
        unlink($controller2);
        rmdir($tempDir);
    }

    /**
     * Test generating custom rule class
     */
    public function testGenerateCustomRuleClass(): void
    {
        $customRules = [
            'valid_phone' => <<<'PHP'
// Validate Indonesian phone number format
if (!preg_match('/^(\+62|62|0)[0-9]{9,12}$/', $str)) {
    $error = 'Nomor telepon tidak valid.';
    return false;
}
return true;
PHP
        ];

        $result = $this->extractor->generateCustomRuleClass('PhoneValidation', $customRules);

        $this->assertIsString($result);
        $this->assertStringContainsString('namespace App\Validation;', $result);
        $this->assertStringContainsString('class PhoneValidation', $result);
        $this->assertStringContainsString('public function valid_phone(', $result);
        $this->assertStringContainsString('string $str', $result);
        $this->assertStringContainsString('string $fields', $result);
        $this->assertStringContainsString('array $data', $result);
        $this->assertStringContainsString('string|null $error = null', $result);
        $this->assertStringContainsString('): bool', $result);
        $this->assertStringContainsString('preg_match', $result);
    }

    /**
     * Test that generated validation class code is valid PHP
     */
    public function testGeneratedCodeIsValidPHP(): void
    {
        $rules = [
            'bank' => 'required|max_length[255]',
            'keterangan' => 'permit_empty|max_length[255]',
        ];

        $code = $this->extractor->convertToRuleClass('BankValidation', $rules);

        // Validate syntax using CodeGenerator
        $validation = $this->codeGenerator->validateSyntax($code);

        $this->assertTrue($validation['valid'], 'Generated code should be valid PHP: ' . ($validation['error'] ?? ''));
    }

    /**
     * Test field name to human-readable conversion
     */
    public function testFieldNameToHumanReadable(): void
    {
        $rules = [
            'id_kavling' => 'required',
            'bank_name' => 'required',
            'user_email' => 'required',
        ];

        $messages = $this->extractor->generateErrorMessages($rules);

        // Check that field names are converted to human-readable format
        $this->assertStringContainsString('Id Kavling', $messages['id_kavling']['required']);
        $this->assertStringContainsString('Bank Name', $messages['bank_name']['required']);
        $this->assertStringContainsString('User Email', $messages['user_email']['required']);
    }

    /**
     * Test field name to camelCase conversion in method names
     */
    public function testFieldNameToCamelCase(): void
    {
        $rules = [
            'id_kavling' => 'required',
            'bank_name' => 'required',
        ];

        $code = $this->extractor->convertToRuleClass('TestValidation', $rules);

        // Check that method names use camelCase
        $this->assertStringContainsString('getIdKavlingRules', $code);
        $this->assertStringContainsString('getBankNameRules', $code);
    }
}


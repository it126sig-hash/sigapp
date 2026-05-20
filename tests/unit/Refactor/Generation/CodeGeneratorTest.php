<?php

namespace Tests\Unit\Refactor\Generation;

use App\Libraries\Refactor\Generation\CodeGenerator;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * CodeGenerator Unit Tests
 * 
 * Tests for the CodeGenerator utility that generates PHP code with proper formatting,
 * namespacing, and documentation following PSR-12 standards.
 * 
 * @package Tests\Unit\Refactor\Generation
 */
class CodeGeneratorTest extends CIUnitTestCase
{
    private CodeGenerator $generator;
    private string $testFilesDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = new CodeGenerator();
        $this->testFilesDir = APPPATH . '../tests/_support/Refactor/GeneratedFiles';
        
        // Create test files directory
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

    /**
     * Test generate with string template
     */
    public function testGenerateWithStringTemplate(): void
    {
        $template = '<?php echo "Hello World";';
        $result = $this->generator->generate($template);
        
        $this->assertEquals($template, $result);
    }

    /**
     * Test generate with template and variables
     */
    public function testGenerateWithTemplateAndVariables(): void
    {
        $data = [
            'template' => '<?php class {{className}} {}',
            'vars' => ['className' => 'TestClass'],
        ];
        
        $result = $this->generator->generate($data);
        
        $this->assertStringContainsString('class TestClass', $result);
    }

    /**
     * Test setNamespace sets namespace correctly
     */
    public function testSetNamespaceSetsNamespaceCorrectly(): void
    {
        $this->generator->setNamespace('App\Services');
        
        $code = $this->generator->generateClass('TestService');
        
        $this->assertStringContainsString('namespace App\Services;', $code);
    }

    /**
     * Test addUseStatement adds use statement
     */
    public function testAddUseStatementAddsUseStatement(): void
    {
        $this->generator
            ->setNamespace('App\Services')
            ->addUseStatement('App\Models\UserModel');
        
        $code = $this->generator->generateClass('TestService');
        
        $this->assertStringContainsString('use App\Models\UserModel;', $code);
    }

    /**
     * Test addUseStatement with alias
     */
    public function testAddUseStatementWithAlias(): void
    {
        $this->generator
            ->setNamespace('App\Services')
            ->addUseStatement('App\Models\UserModel', 'User');
        
        $code = $this->generator->generateClass('TestService');
        
        $this->assertStringContainsString('use App\Models\UserModel as User;', $code);
    }

    /**
     * Test addUseStatements adds multiple use statements
     */
    public function testAddUseStatementsAddsMultipleUseStatements(): void
    {
        $this->generator
            ->setNamespace('App\Services')
            ->addUseStatements([
                'App\Models\UserModel',
                'App\Repositories\UserRepository',
            ]);
        
        $code = $this->generator->generateClass('TestService');
        
        $this->assertStringContainsString('use App\Models\UserModel;', $code);
        $this->assertStringContainsString('use App\Repositories\UserRepository;', $code);
    }

    /**
     * Test generateClass generates basic class
     */
    public function testGenerateClassGeneratesBasicClass(): void
    {
        $this->generator->setNamespace('App\Services');
        
        $code = $this->generator->generateClass('TestService');
        
        $this->assertStringContainsString('<?php', $code);
        $this->assertStringContainsString('namespace App\Services;', $code);
        $this->assertStringContainsString('class TestService', $code);
    }

    /**
     * Test generateClass with extends
     */
    public function testGenerateClassWithExtends(): void
    {
        $this->generator->setNamespace('App\Services');
        
        $code = $this->generator->generateClass('TestService', [
            'extends' => 'BaseService',
        ]);
        
        $this->assertStringContainsString('class TestService extends BaseService', $code);
    }

    /**
     * Test generateClass with implements
     */
    public function testGenerateClassWithImplements(): void
    {
        $this->generator->setNamespace('App\Services');
        
        $code = $this->generator->generateClass('TestService', [
            'implements' => 'ServiceInterface',
        ]);
        
        $this->assertStringContainsString('class TestService implements ServiceInterface', $code);
    }

    /**
     * Test generateClass with multiple implements
     */
    public function testGenerateClassWithMultipleImplements(): void
    {
        $this->generator->setNamespace('App\Services');
        
        $code = $this->generator->generateClass('TestService', [
            'implements' => ['ServiceInterface', 'LoggerAwareInterface'],
        ]);
        
        $this->assertStringContainsString('class TestService implements ServiceInterface, LoggerAwareInterface', $code);
    }

    /**
     * Test generateClass with description
     */
    public function testGenerateClassWithDescription(): void
    {
        $this->generator->setNamespace('App\Services');
        
        $code = $this->generator->generateClass('TestService', [
            'description' => 'This is a test service for unit testing',
        ]);
        
        $this->assertStringContainsString('/**', $code);
        $this->assertStringContainsString('* TestService', $code);
        $this->assertStringContainsString('* This is a test service for unit testing', $code);
        $this->assertStringContainsString('* @package App\Services', $code);
    }

    /**
     * Test generateClass with properties
     */
    public function testGenerateClassWithProperties(): void
    {
        $this->generator->setNamespace('App\Services');
        
        $code = $this->generator->generateClass('TestService', [
            'properties' => [
                [
                    'name' => 'userModel',
                    'visibility' => 'private',
                    'type' => 'UserModel',
                ],
                [
                    'name' => 'config',
                    'visibility' => 'protected',
                    'type' => 'array',
                    'default' => '[]',
                ],
            ],
        ]);
        
        $this->assertStringContainsString('private UserModel $userModel;', $code);
        $this->assertStringContainsString('protected array $config = [];', $code);
    }

    /**
     * Test generateClass with constructor
     */
    public function testGenerateClassWithConstructor(): void
    {
        $this->generator->setNamespace('App\Services');
        
        $code = $this->generator->generateClass('TestService', [
            'constructor' => [
                'params' => [
                    ['type' => 'UserModel', 'name' => 'userModel'],
                ],
                'body' => '$this->userModel = $userModel;',
            ],
        ]);
        
        $this->assertStringContainsString('public function __construct(UserModel $userModel)', $code);
        $this->assertStringContainsString('$this->userModel = $userModel;', $code);
    }

    /**
     * Test generateClass with methods
     */
    public function testGenerateClassWithMethods(): void
    {
        $this->generator->setNamespace('App\Services');
        
        $code = $this->generator->generateClass('TestService', [
            'methods' => [
                [
                    'name' => 'getUser',
                    'visibility' => 'public',
                    'params' => [
                        ['type' => 'int', 'name' => 'id'],
                    ],
                    'return' => 'array',
                    'body' => 'return $this->userModel->find($id);',
                ],
            ],
        ]);
        
        $this->assertStringContainsString('public function getUser(int $id): array', $code);
        $this->assertStringContainsString('return $this->userModel->find($id);', $code);
    }

    /**
     * Test generateMethod generates method correctly
     */
    public function testGenerateMethodGeneratesMethodCorrectly(): void
    {
        $method = $this->generator->generateMethod([
            'name' => 'testMethod',
            'visibility' => 'public',
            'params' => [
                ['type' => 'string', 'name' => 'param1'],
                ['type' => 'int', 'name' => 'param2', 'default' => '0'],
            ],
            'return' => 'bool',
            'body' => 'return true;',
        ]);
        
        $this->assertStringContainsString('public function testMethod(string $param1, int $param2 = 0): bool', $method);
        $this->assertStringContainsString('return true;', $method);
    }

    /**
     * Test generateMethod with static modifier
     */
    public function testGenerateMethodWithStaticModifier(): void
    {
        $method = $this->generator->generateMethod([
            'name' => 'staticMethod',
            'visibility' => 'public',
            'static' => true,
            'return' => 'void',
            'body' => 'echo "Static method";',
        ]);
        
        $this->assertStringContainsString('public static function staticMethod(): void', $method);
    }

    /**
     * Test generateMethod with description
     */
    public function testGenerateMethodWithDescription(): void
    {
        $method = $this->generator->generateMethod([
            'name' => 'testMethod',
            'description' => 'This is a test method',
            'params' => [
                ['type' => 'string', 'name' => 'param1', 'description' => 'First parameter'],
            ],
            'return' => 'bool',
            'returnDescription' => 'Returns true on success',
            'body' => 'return true;',
        ]);
        
        $this->assertStringContainsString('/**', $method);
        $this->assertStringContainsString('* This is a test method', $method);
        $this->assertStringContainsString('* @param string $param1 First parameter', $method);
        $this->assertStringContainsString('* @return bool Returns true on success', $method);
    }

    /**
     * Test generateProperty generates property correctly
     */
    public function testGeneratePropertyGeneratesPropertyCorrectly(): void
    {
        $property = $this->generator->generateProperty([
            'name' => 'testProperty',
            'visibility' => 'private',
            'type' => 'string',
        ]);
        
        $this->assertStringContainsString('private string $testProperty;', $property);
    }

    /**
     * Test generateProperty with default value
     */
    public function testGeneratePropertyWithDefaultValue(): void
    {
        $property = $this->generator->generateProperty([
            'name' => 'count',
            'visibility' => 'private',
            'type' => 'int',
            'default' => '0',
        ]);
        
        $this->assertStringContainsString('private int $count = 0;', $property);
    }

    /**
     * Test generateProperty with description
     */
    public function testGeneratePropertyWithDescription(): void
    {
        $property = $this->generator->generateProperty([
            'name' => 'testProperty',
            'visibility' => 'private',
            'type' => 'string',
            'description' => 'This is a test property',
        ]);
        
        $this->assertStringContainsString('/**', $property);
        $this->assertStringContainsString('* This is a test property', $property);
        $this->assertStringContainsString('* @var string', $property);
    }

    /**
     * Test generateProperty with static modifier
     */
    public function testGeneratePropertyWithStaticModifier(): void
    {
        $property = $this->generator->generateProperty([
            'name' => 'staticProperty',
            'visibility' => 'public',
            'static' => true,
            'type' => 'int',
            'default' => '0',
        ]);
        
        $this->assertStringContainsString('public static int $staticProperty = 0;', $property);
    }

    /**
     * Test generateConstructor generates constructor correctly
     */
    public function testGenerateConstructorGeneratesConstructorCorrectly(): void
    {
        $constructor = $this->generator->generateConstructor([
            'params' => [
                ['type' => 'UserModel', 'name' => 'userModel'],
                ['type' => 'AuthService', 'name' => 'authService'],
            ],
            'body' => [
                '$this->userModel = $userModel;',
                '$this->authService = $authService;',
            ],
        ]);
        
        $this->assertStringContainsString('public function __construct(UserModel $userModel, AuthService $authService)', $constructor);
        $this->assertStringContainsString('$this->userModel = $userModel;', $constructor);
        $this->assertStringContainsString('$this->authService = $authService;', $constructor);
    }

    /**
     * Test formatCode removes trailing whitespace
     */
    public function testFormatCodeRemovesTrailingWhitespace(): void
    {
        $code = "<?php\nclass TestClass   \n{   \n}   \n";
        $formatted = $this->generator->formatCode($code);
        
        $this->assertStringNotContainsString('   ', $formatted);
    }

    /**
     * Test formatCode ensures single newline at end
     */
    public function testFormatCodeEnsuresSingleNewlineAtEnd(): void
    {
        $code = "<?php\nclass TestClass\n{\n}\n\n\n";
        $formatted = $this->generator->formatCode($code);
        
        $this->assertStringEndsWith("}\n", $formatted);
        $this->assertStringNotContainsString("\n\n\n", $formatted);
    }

    /**
     * Test validateSyntax validates correct PHP code
     */
    public function testValidateSyntaxValidatesCorrectPhpCode(): void
    {
        $code = "<?php\nclass TestClass\n{\n    public function test(): void\n    {\n        echo 'test';\n    }\n}\n";
        
        $result = $this->generator->validateSyntax($code);
        
        $this->assertTrue($result['valid']);
        $this->assertNull($result['error']);
    }

    /**
     * Test validateSyntax detects syntax errors
     */
    public function testValidateSyntaxDetectsSyntaxErrors(): void
    {
        $code = "<?php\nclass TestClass\n{\n    public function test()\n    {\n        echo 'test'\n    }\n}\n";  // Missing semicolon
        
        $result = $this->generator->validateSyntax($code);
        
        $this->assertFalse($result['valid']);
        $this->assertNotNull($result['error']);
    }

    /**
     * Test reset clears generator state
     */
    public function testResetClearsGeneratorState(): void
    {
        $this->generator
            ->setNamespace('App\Services')
            ->addUseStatement('App\Models\UserModel');
        
        $this->generator->reset();
        
        $code = $this->generator->generateClass('TestService');
        
        $this->assertStringNotContainsString('namespace App\Services;', $code);
        $this->assertStringNotContainsString('use App\Models\UserModel;', $code);
    }

    /**
     * Test setIndentSize changes indentation
     */
    public function testSetIndentSizeChangesIndentation(): void
    {
        $this->generator
            ->setNamespace('App\Services')
            ->setIndentSize(2);
        
        $code = $this->generator->generateClass('TestService', [
            'methods' => [
                [
                    'name' => 'test',
                    'body' => 'return true;',
                ],
            ],
        ]);
        
        // Check that indentation uses 2 spaces instead of 4
        $this->assertStringContainsString('  public function test()', $code);
    }

    /**
     * Test generateClass with complete example
     */
    public function testGenerateClassWithCompleteExample(): void
    {
        $this->generator
            ->setNamespace('App\Services')
            ->addUseStatements([
                'App\Models\UserModel',
                'App\Repositories\UserRepository',
            ]);
        
        $code = $this->generator->generateClass('UserService', [
            'description' => 'Service for managing user operations',
            'properties' => [
                [
                    'name' => 'userRepository',
                    'visibility' => 'private',
                    'type' => 'UserRepository',
                    'description' => 'User repository instance',
                ],
            ],
            'constructor' => [
                'params' => [
                    ['type' => 'UserRepository', 'name' => 'userRepository'],
                ],
                'body' => '$this->userRepository = $userRepository;',
            ],
            'methods' => [
                [
                    'name' => 'getUser',
                    'visibility' => 'public',
                    'description' => 'Get user by ID',
                    'params' => [
                        ['type' => 'int', 'name' => 'id', 'description' => 'User ID'],
                    ],
                    'return' => 'array',
                    'returnDescription' => 'User data array',
                    'body' => 'return $this->userRepository->find($id);',
                ],
                [
                    'name' => 'createUser',
                    'visibility' => 'public',
                    'description' => 'Create a new user',
                    'params' => [
                        ['type' => 'array', 'name' => 'data', 'description' => 'User data'],
                    ],
                    'return' => 'bool',
                    'returnDescription' => 'True on success',
                    'body' => 'return $this->userRepository->create($data);',
                ],
            ],
        ]);
        
        // Validate structure
        $this->assertStringContainsString('<?php', $code);
        $this->assertStringContainsString('namespace App\Services;', $code);
        $this->assertStringContainsString('use App\Models\UserModel;', $code);
        $this->assertStringContainsString('use App\Repositories\UserRepository;', $code);
        $this->assertStringContainsString('class UserService', $code);
        $this->assertStringContainsString('private UserRepository $userRepository;', $code);
        $this->assertStringContainsString('public function __construct(UserRepository $userRepository)', $code);
        $this->assertStringContainsString('public function getUser(int $id): array', $code);
        $this->assertStringContainsString('public function createUser(array $data): bool', $code);
        
        // Validate syntax
        $result = $this->generator->validateSyntax($code);
        $this->assertTrue($result['valid'], 'Generated code should have valid PHP syntax');
    }

    /**
     * Test use statements are sorted alphabetically (PSR-12)
     */
    public function testUseStatementsAreSortedAlphabetically(): void
    {
        $this->generator
            ->setNamespace('App\Services')
            ->addUseStatements([
                'Zebra\ZebraClass',
                'App\Models\UserModel',
                'Beta\BetaClass',
            ]);
        
        $code = $this->generator->generateClass('TestService');
        
        // Extract use statements section
        preg_match_all('/use ([^;]+);/', $code, $matches);
        $useStatements = $matches[1];
        
        // Check if sorted
        $sorted = $useStatements;
        sort($sorted);
        
        $this->assertEquals($sorted, $useStatements, 'Use statements should be sorted alphabetically');
    }

    /**
     * Test generated code follows PSR-12 standards
     */
    public function testGeneratedCodeFollowsPsr12Standards(): void
    {
        $this->generator
            ->setNamespace('App\Services')
            ->addUseStatement('App\Models\UserModel');
        
        $code = $this->generator->generateClass('TestService', [
            'extends' => 'BaseService',
            'implements' => ['ServiceInterface'],
            'properties' => [
                ['name' => 'userModel', 'visibility' => 'private', 'type' => 'UserModel'],
            ],
            'constructor' => [
                'params' => [['type' => 'UserModel', 'name' => 'userModel']],
                'body' => '$this->userModel = $userModel;',
            ],
            'methods' => [
                [
                    'name' => 'test',
                    'visibility' => 'public',
                    'return' => 'void',
                    'body' => 'echo "test";',
                ],
            ],
        ]);
        
        // PSR-12 checks
        $this->assertStringStartsWith('<?php', $code);
        $this->assertMatchesRegularExpression('/namespace [^;]+;\n\n/', $code);
        $this->assertMatchesRegularExpression('/use [^;]+;\n/', $code);
        $this->assertStringContainsString('class TestService extends BaseService implements ServiceInterface', $code);
        
        // Validate syntax
        $result = $this->generator->validateSyntax($code);
        $this->assertTrue($result['valid']);
    }

    /**
     * Recursively delete directory
     */
    private function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
}

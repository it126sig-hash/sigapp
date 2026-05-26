<?php

namespace Tests\Libraries\Refactor\Generation;

use App\Libraries\Refactor\Generation\CodeGenerator;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * CodeGeneratorTest
 *
 * Unit tests for the CodeGenerator base class that provides common code generation
 * utilities including PSR-12 formatting, namespace declarations, use statements,
 * class/interface generation, method signatures, PHPDoc blocks, and syntax validation.
 *
 * @package Tests\Libraries\Refactor\Generation
 */
class CodeGeneratorTest extends CIUnitTestCase
{
    private CodeGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = new CodeGenerator();
    }

    // =========================================================================
    // Namespace Declaration Tests
    // =========================================================================

    /**
     * Test generating namespace declaration
     */
    public function testGenerateNamespaceDeclaration(): void
    {
        $result = $this->generator->generateNamespaceDeclaration('App\Services');

        $this->assertEquals('namespace App\Services;', $result);
    }

    /**
     * Test setNamespace is used in class generation
     */
    public function testSetNamespaceAppliedInGenerateClass(): void
    {
        $this->generator->setNamespace('App\Repositories');

        $code = $this->generator->generateClass('UserRepository');

        $this->assertStringContainsString('namespace App\Repositories;', $code);
    }

    /**
     * Test getNamespace returns current namespace
     */
    public function testGetNamespaceReturnsCurrentNamespace(): void
    {
        $this->assertNull($this->generator->getNamespace());

        $this->generator->setNamespace('App\Services');

        $this->assertEquals('App\Services', $this->generator->getNamespace());
    }

    // =========================================================================
    // Use Statement Tests
    // =========================================================================

    /**
     * Test generating use block from array
     */
    public function testGenerateUseBlock(): void
    {
        $classes = [
            'App\Models\UserModel',
            'App\Repositories\UserRepository',
        ];

        $result = $this->generator->generateUseBlock($classes);

        $this->assertStringContainsString('use App\Models\UserModel;', $result);
        $this->assertStringContainsString('use App\Repositories\UserRepository;', $result);
    }

    /**
     * Test use block is sorted alphabetically (PSR-12)
     */
    public function testGenerateUseBlockSortedAlphabetically(): void
    {
        $classes = [
            'Zebra\ZebraClass',
            'App\Models\UserModel',
            'Beta\BetaClass',
        ];

        $result = $this->generator->generateUseBlock($classes);
        $lines = explode("\n", $result);

        $this->assertStringContainsString('App\Models\UserModel', $lines[0]);
        $this->assertStringContainsString('Beta\BetaClass', $lines[1]);
        $this->assertStringContainsString('Zebra\ZebraClass', $lines[2]);
    }

    /**
     * Test use block with aliases
     */
    public function testGenerateUseBlockWithAliases(): void
    {
        $classes = [
            'App\Models\UserModel' => 'User',
        ];

        $result = $this->generator->generateUseBlock($classes);

        $this->assertStringContainsString('use App\Models\UserModel as User;', $result);
    }

    /**
     * Test addUseStatement and getUseStatements
     */
    public function testAddAndGetUseStatements(): void
    {
        $this->generator->addUseStatement('App\Models\UserModel');
        $this->generator->addUseStatement('App\Services\AuthService');

        $statements = $this->generator->getUseStatements();

        $this->assertContains('App\Models\UserModel', $statements);
        $this->assertContains('App\Services\AuthService', $statements);
    }

    // =========================================================================
    // Class Declaration Tests
    // =========================================================================

    /**
     * Test generating simple class declaration
     */
    public function testGenerateClassDeclarationSimple(): void
    {
        $result = $this->generator->generateClassDeclaration('UserService');

        $this->assertEquals('class UserService', $result);
    }

    /**
     * Test generating class declaration with extends
     */
    public function testGenerateClassDeclarationWithExtends(): void
    {
        $result = $this->generator->generateClassDeclaration('UserService', 'BaseService');

        $this->assertEquals('class UserService extends BaseService', $result);
    }

    /**
     * Test generating class declaration with implements
     */
    public function testGenerateClassDeclarationWithImplements(): void
    {
        $result = $this->generator->generateClassDeclaration(
            'UserService',
            null,
            ['ServiceInterface', 'LoggableInterface']
        );

        $this->assertEquals(
            'class UserService implements ServiceInterface, LoggableInterface',
            $result
        );
    }

    /**
     * Test generating abstract class declaration
     */
    public function testGenerateClassDeclarationAbstract(): void
    {
        $result = $this->generator->generateClassDeclaration(
            'BaseService',
            null,
            null,
            true
        );

        $this->assertEquals('abstract class BaseService', $result);
    }

    /**
     * Test generating final class declaration
     */
    public function testGenerateClassDeclarationFinal(): void
    {
        $result = $this->generator->generateClassDeclaration(
            'UserService',
            null,
            null,
            false,
            true
        );

        $this->assertEquals('final class UserService', $result);
    }

    /**
     * Test generateClass with abstract option
     */
    public function testGenerateClassAbstract(): void
    {
        $this->generator->setNamespace('App\Services');

        $code = $this->generator->generateClass('BaseService', [
            'abstract' => true,
        ]);

        $this->assertStringContainsString('abstract class BaseService', $code);
    }

    /**
     * Test generateClass with final option
     */
    public function testGenerateClassFinal(): void
    {
        $this->generator->setNamespace('App\Services');

        $code = $this->generator->generateClass('UserService', [
            'final' => true,
        ]);

        $this->assertStringContainsString('final class UserService', $code);
    }

    /**
     * Test generateClass with extends and implements
     */
    public function testGenerateClassWithExtendsAndImplements(): void
    {
        $this->generator->setNamespace('App\Services');

        $code = $this->generator->generateClass('UserService', [
            'extends' => 'BaseService',
            'implements' => ['ServiceInterface', 'LoggableInterface'],
        ]);

        $this->assertStringContainsString(
            'class UserService extends BaseService implements ServiceInterface, LoggableInterface',
            $code
        );
    }

    // =========================================================================
    // Method Signature Tests
    // =========================================================================

    /**
     * Test generating simple method signature
     */
    public function testGenerateMethodSignatureSimple(): void
    {
        $result = $this->generator->generateMethodSignature('getUser');

        $this->assertEquals('public function getUser()', $result);
    }

    /**
     * Test generating method signature with parameters and return type
     */
    public function testGenerateMethodSignatureWithParamsAndReturn(): void
    {
        $result = $this->generator->generateMethodSignature(
            'findById',
            'public',
            [
                ['type' => 'int', 'name' => 'id'],
            ],
            'array|null'
        );

        $this->assertEquals('public function findById(int $id): array|null', $result);
    }

    /**
     * Test generating static method signature
     */
    public function testGenerateMethodSignatureStatic(): void
    {
        $result = $this->generator->generateMethodSignature(
            'getInstance',
            'public',
            [],
            'self',
            true
        );

        $this->assertEquals('public static function getInstance(): self', $result);
    }

    /**
     * Test generating method signature with default parameter values
     */
    public function testGenerateMethodSignatureWithDefaults(): void
    {
        $result = $this->generator->generateMethodSignature(
            'findAll',
            'public',
            [
                ['type' => 'int', 'name' => 'limit', 'default' => 'null'],
                ['type' => 'int', 'name' => 'offset', 'default' => '0'],
            ],
            'array'
        );

        $this->assertEquals(
            'public function findAll(int $limit = null, int $offset = 0): array',
            $result
        );
    }

    /**
     * Test generating protected method signature
     */
    public function testGenerateMethodSignatureProtected(): void
    {
        $result = $this->generator->generateMethodSignature(
            'processData',
            'protected',
            [['type' => 'array', 'name' => 'data']],
            'void'
        );

        $this->assertEquals('protected function processData(array $data): void', $result);
    }

    // =========================================================================
    // PHPDoc Block Tests
    // =========================================================================

    /**
     * Test generating PHPDoc block with description only
     */
    public function testGenerateDocBlockDescriptionOnly(): void
    {
        $result = $this->generator->generateDocBlock('This is a test method');

        $this->assertStringContainsString('/**', $result);
        $this->assertStringContainsString('* This is a test method', $result);
        $this->assertStringContainsString('*/', $result);
    }

    /**
     * Test generating PHPDoc block with params and return
     */
    public function testGenerateDocBlockWithParamsAndReturn(): void
    {
        $result = $this->generator->generateDocBlock(
            'Find a user by ID',
            [
                ['type' => 'int', 'name' => 'id', 'description' => 'User ID'],
            ],
            'array|null',
            'User data or null if not found'
        );

        $this->assertStringContainsString('* Find a user by ID', $result);
        $this->assertStringContainsString('* @param int $id User ID', $result);
        $this->assertStringContainsString('* @return array|null User data or null if not found', $result);
    }

    /**
     * Test generating PHPDoc block with throws
     */
    public function testGenerateDocBlockWithThrows(): void
    {
        $result = $this->generator->generateDocBlock(
            'Delete a record',
            [],
            'void',
            null,
            ['InvalidArgumentException', 'RuntimeException']
        );

        $this->assertStringContainsString('* @throws InvalidArgumentException', $result);
        $this->assertStringContainsString('* @throws RuntimeException', $result);
    }

    /**
     * Test generating PHPDoc block with indentation
     */
    public function testGenerateDocBlockWithIndentation(): void
    {
        $result = $this->generator->generateDocBlock(
            'Indented method',
            [],
            'void',
            null,
            [],
            1
        );

        $this->assertStringStartsWith('    /**', $result);
        $this->assertStringContainsString('     * Indented method', $result);
        $this->assertStringContainsString('     */', $result);
    }

    // =========================================================================
    // Interface Generation Tests
    // =========================================================================

    /**
     * Test generating interface
     */
    public function testGenerateInterface(): void
    {
        $this->generator->setNamespace('App\Contracts');

        $code = $this->generator->generateInterface('UserServiceInterface', [], [
            [
                'name' => 'getUser',
                'params' => [['type' => 'int', 'name' => 'id']],
                'return' => 'array|null',
                'description' => 'Get user by ID',
            ],
            [
                'name' => 'createUser',
                'params' => [['type' => 'array', 'name' => 'data']],
                'return' => 'bool',
                'description' => 'Create a new user',
            ],
        ]);

        $this->assertStringContainsString('namespace App\Contracts;', $code);
        $this->assertStringContainsString('interface UserServiceInterface', $code);
        $this->assertStringContainsString('public function getUser(int $id): array|null;', $code);
        $this->assertStringContainsString('public function createUser(array $data): bool;', $code);

        // Validate syntax
        $result = $this->generator->validateSyntax($code);
        $this->assertTrue($result['valid'], 'Generated interface should have valid PHP syntax: ' . ($result['error'] ?? ''));
    }

    /**
     * Test generating interface with extends
     */
    public function testGenerateInterfaceWithExtends(): void
    {
        $this->generator->setNamespace('App\Contracts');

        $code = $this->generator->generateInterface(
            'UserServiceInterface',
            ['BaseServiceInterface'],
            [
                [
                    'name' => 'getUser',
                    'params' => [['type' => 'int', 'name' => 'id']],
                    'return' => 'array|null',
                ],
            ]
        );

        $this->assertStringContainsString('interface UserServiceInterface extends BaseServiceInterface', $code);
    }

    // =========================================================================
    // Dependency Injection Constructor Tests
    // =========================================================================

    /**
     * Test generating DI constructor with promoted properties
     */
    public function testGenerateDIConstructor(): void
    {
        $dependencies = [
            ['type' => 'UserRepository', 'name' => 'userRepository', 'description' => 'User repository'],
            ['type' => 'AuthService', 'name' => 'authService', 'description' => 'Auth service'],
        ];

        $result = $this->generator->generateDIConstructor($dependencies);

        $this->assertStringContainsString('public function __construct(', $result);
        $this->assertStringContainsString('private UserRepository $userRepository', $result);
        $this->assertStringContainsString('private AuthService $authService', $result);
        $this->assertStringContainsString('@param UserRepository $userRepository User repository', $result);
        $this->assertStringContainsString('@param AuthService $authService Auth service', $result);
    }

    /**
     * Test generating DI constructor with empty dependencies returns empty string
     */
    public function testGenerateDIConstructorEmpty(): void
    {
        $result = $this->generator->generateDIConstructor([]);

        $this->assertEquals('', $result);
    }

    // =========================================================================
    // PHP File Generation Tests
    // =========================================================================

    /**
     * Test generating complete PHP file
     */
    public function testGeneratePhpFile(): void
    {
        $this->generator->setNamespace('App\Services');
        $this->generator->addUseStatement('App\Models\UserModel');

        $content = "class UserService\n{\n    // Service code\n}\n";
        $result = $this->generator->generatePhpFile($content);

        $this->assertStringStartsWith('<?php', $result);
        $this->assertStringContainsString('namespace App\Services;', $result);
        $this->assertStringContainsString('use App\Models\UserModel;', $result);
        $this->assertStringContainsString('class UserService', $result);
    }

    // =========================================================================
    // Naming Convention Validation Tests
    // =========================================================================

    /**
     * Test valid class names
     */
    public function testIsValidClassNameValid(): void
    {
        $this->assertTrue($this->generator->isValidClassName('UserService'));
        $this->assertTrue($this->generator->isValidClassName('BaseController'));
        $this->assertTrue($this->generator->isValidClassName('A'));
    }

    /**
     * Test invalid class names
     */
    public function testIsValidClassNameInvalid(): void
    {
        $this->assertFalse($this->generator->isValidClassName('userService'));
        $this->assertFalse($this->generator->isValidClassName('_UserService'));
        $this->assertFalse($this->generator->isValidClassName('123Class'));
        $this->assertFalse($this->generator->isValidClassName('User_Service'));
    }

    /**
     * Test valid method names (camelCase)
     */
    public function testIsValidMethodNameValid(): void
    {
        $this->assertTrue($this->generator->isValidMethodName('getUser'));
        $this->assertTrue($this->generator->isValidMethodName('findById'));
        $this->assertTrue($this->generator->isValidMethodName('a'));
    }

    /**
     * Test invalid method names
     */
    public function testIsValidMethodNameInvalid(): void
    {
        $this->assertFalse($this->generator->isValidMethodName('GetUser'));
        $this->assertFalse($this->generator->isValidMethodName('_getUser'));
        $this->assertFalse($this->generator->isValidMethodName('123method'));
        $this->assertFalse($this->generator->isValidMethodName('get_user'));
    }

    // =========================================================================
    // Indentation and Formatting Tests
    // =========================================================================

    /**
     * Test getIndent returns correct indentation
     */
    public function testGetIndentReturnsCorrectIndentation(): void
    {
        $this->assertEquals('    ', $this->generator->getIndent(1));
        $this->assertEquals('        ', $this->generator->getIndent(2));
        $this->assertEquals('            ', $this->generator->getIndent(3));
    }

    /**
     * Test indentCode indents multiple lines
     */
    public function testIndentCodeIndentsMultipleLines(): void
    {
        $code = "line1\nline2\nline3";
        $result = $this->generator->indentCode($code, 1);

        $lines = explode("\n", $result);
        $this->assertStringStartsWith('    ', $lines[0]);
        $this->assertStringStartsWith('    ', $lines[1]);
        $this->assertStringStartsWith('    ', $lines[2]);
    }

    /**
     * Test indentCode preserves empty lines
     */
    public function testIndentCodePreservesEmptyLines(): void
    {
        $code = "line1\n\nline3";
        $result = $this->generator->indentCode($code, 1);

        $lines = explode("\n", $result);
        $this->assertEquals('', $lines[1]);
    }

    /**
     * Test formatCode removes trailing whitespace
     */
    public function testFormatCodeRemovesTrailingWhitespace(): void
    {
        $code = "<?php\nclass Test   \n{   \n}   \n";
        $formatted = $this->generator->formatCode($code);

        $lines = explode("\n", $formatted);
        foreach ($lines as $line) {
            $this->assertEquals(rtrim($line), $line);
        }
    }

    /**
     * Test formatCode ensures single newline at end
     */
    public function testFormatCodeEnsuresSingleNewlineAtEnd(): void
    {
        $code = "<?php\nclass Test\n{\n}\n\n\n";
        $formatted = $this->generator->formatCode($code);

        $this->assertStringEndsWith("}\n", $formatted);
        $this->assertStringNotContainsString("\n\n\n", $formatted);
    }

    /**
     * Test setIndentSize changes indentation
     */
    public function testSetIndentSizeChangesIndentation(): void
    {
        $this->generator->setIndentSize(2);

        $this->assertEquals('  ', $this->generator->getIndent(1));
        $this->assertEquals('    ', $this->generator->getIndent(2));
    }

    // =========================================================================
    // Syntax Validation Tests
    // =========================================================================

    /**
     * Test validateSyntax with valid PHP code
     */
    public function testValidateSyntaxWithValidCode(): void
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
        $code = "<?php\nclass TestClass\n{\n    public function test()\n    {\n        echo 'test'\n    }\n}\n";

        $result = $this->generator->validateSyntax($code);

        $this->assertFalse($result['valid']);
        $this->assertNotNull($result['error']);
    }

    // =========================================================================
    // Template-Based Code Generation Tests
    // =========================================================================

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
            'template' => '<?php class {{className}} extends {{baseClass}} {}',
            'vars' => [
                'className' => 'UserService',
                'baseClass' => 'BaseService',
            ],
        ];

        $result = $this->generator->generate($data);

        $this->assertStringContainsString('class UserService extends BaseService', $result);
    }

    /**
     * Test generate with empty data returns empty string
     */
    public function testGenerateWithInvalidDataReturnsEmpty(): void
    {
        $result = $this->generator->generate(123);

        $this->assertEquals('', $result);
    }

    // =========================================================================
    // Reset and State Management Tests
    // =========================================================================

    /**
     * Test reset clears all state
     */
    public function testResetClearsAllState(): void
    {
        $this->generator
            ->setNamespace('App\Services')
            ->addUseStatement('App\Models\UserModel');

        $this->generator->reset();

        $this->assertNull($this->generator->getNamespace());
        $this->assertEmpty($this->generator->getUseStatements());

        $code = $this->generator->generateClass('TestService');
        $this->assertStringNotContainsString('namespace', $code);
        $this->assertStringNotContainsString('use App', $code);
    }

    // =========================================================================
    // Complete Integration Tests
    // =========================================================================

    /**
     * Test generating a complete service class with all features
     */
    public function testGenerateCompleteServiceClass(): void
    {
        $this->generator
            ->setNamespace('App\Services')
            ->addUseStatements([
                'App\Repositories\UserRepository',
                'App\Models\UserModel',
                'CodeIgniter\Database\Exceptions\DatabaseException',
            ]);

        $code = $this->generator->generateClass('UserService', [
            'description' => 'Service for managing user operations including CRUD and authentication',
            'extends' => 'BaseService',
            'implements' => ['UserServiceInterface'],
            'properties' => [
                [
                    'name' => 'userRepository',
                    'visibility' => 'private',
                    'type' => 'UserRepository',
                    'description' => 'User repository instance',
                ],
            ],
            'constructor' => [
                'description' => 'Initialize the UserService',
                'params' => [
                    ['type' => 'UserRepository', 'name' => 'userRepository', 'description' => 'User repository'],
                ],
                'body' => '$this->userRepository = $userRepository;',
            ],
            'methods' => [
                [
                    'name' => 'findById',
                    'visibility' => 'public',
                    'description' => 'Find a user by their ID',
                    'params' => [
                        ['type' => 'int', 'name' => 'id', 'description' => 'User ID'],
                    ],
                    'return' => 'array|null',
                    'returnDescription' => 'User data or null if not found',
                    'body' => 'return $this->userRepository->findById($id);',
                ],
                [
                    'name' => 'create',
                    'visibility' => 'public',
                    'description' => 'Create a new user',
                    'params' => [
                        ['type' => 'array', 'name' => 'data', 'description' => 'User data'],
                    ],
                    'return' => 'int|false',
                    'returnDescription' => 'New user ID or false on failure',
                    'body' => [
                        '// Validate data before creating',
                        'if (empty($data)) {',
                        '    return false;',
                        '}',
                        '',
                        'return $this->userRepository->create($data);',
                    ],
                ],
            ],
        ]);

        // Structural assertions
        $this->assertStringContainsString('<?php', $code);
        $this->assertStringContainsString('namespace App\Services;', $code);
        $this->assertStringContainsString('use App\Models\UserModel;', $code);
        $this->assertStringContainsString('use App\Repositories\UserRepository;', $code);
        $this->assertStringContainsString('use CodeIgniter\Database\Exceptions\DatabaseException;', $code);
        $this->assertStringContainsString('class UserService extends BaseService implements UserServiceInterface', $code);
        $this->assertStringContainsString('private UserRepository $userRepository;', $code);
        $this->assertStringContainsString('public function __construct(UserRepository $userRepository)', $code);
        $this->assertStringContainsString('public function findById(int $id): array|null', $code);
        $this->assertStringContainsString('public function create(array $data): int|false', $code);

        // PHPDoc assertions
        $this->assertStringContainsString('@package App\Services', $code);
        $this->assertStringContainsString('@param int $id User ID', $code);
        $this->assertStringContainsString('@return array|null User data or null if not found', $code);

        // Validate syntax
        $result = $this->generator->validateSyntax($code);
        $this->assertTrue($result['valid'], 'Generated service class should have valid PHP syntax: ' . ($result['error'] ?? ''));
    }

    /**
     * Test generating a complete repository class produces valid PHP
     */
    public function testGenerateCompleteRepositoryClassValidSyntax(): void
    {
        $this->generator
            ->setNamespace('App\Repositories')
            ->addUseStatements([
                'CodeIgniter\Database\ConnectionInterface',
                'CodeIgniter\Database\BaseBuilder',
            ]);

        $code = $this->generator->generateClass('TransaksiRepository', [
            'description' => 'Repository for Transaksi data access operations',
            'properties' => [
                [
                    'name' => 'db',
                    'type' => 'ConnectionInterface',
                    'visibility' => 'private',
                    'description' => 'Database connection',
                ],
                [
                    'name' => 'table',
                    'type' => 'string',
                    'visibility' => 'private',
                    'default' => 'transaksi',
                ],
            ],
            'constructor' => [
                'params' => [
                    ['type' => 'ConnectionInterface', 'name' => 'db'],
                ],
                'body' => [
                    '$this->db = $db;',
                    "\$this->table = 'transaksi';",
                ],
            ],
            'methods' => [
                [
                    'name' => 'findAll',
                    'visibility' => 'public',
                    'params' => [
                        ['type' => 'int', 'name' => 'limit', 'default' => 'null'],
                        ['type' => 'int', 'name' => 'offset', 'default' => '0'],
                    ],
                    'return' => 'array',
                    'description' => 'Retrieve all records',
                    'body' => [
                        '$builder = $this->db->table($this->table);',
                        'if ($limit !== null) {',
                        '    $builder->limit($limit, $offset);',
                        '}',
                        'return $builder->get()->getResultArray();',
                    ],
                ],
            ],
        ]);

        $result = $this->generator->validateSyntax($code);
        $this->assertTrue($result['valid'], 'Generated repository class should have valid PHP syntax: ' . ($result['error'] ?? ''));
    }

    /**
     * Test PSR-12 compliance: opening brace on same line for class
     */
    public function testPsr12OpeningBraceOnNewLineForClass(): void
    {
        $this->generator->setNamespace('App\Services');

        $code = $this->generator->generateClass('TestService');

        // PSR-12: Opening brace for class MUST be on its own line
        $this->assertMatchesRegularExpression('/class TestService\n\{/', $code);
    }

    /**
     * Test PSR-12 compliance: blank line after namespace
     */
    public function testPsr12BlankLineAfterNamespace(): void
    {
        $this->generator->setNamespace('App\Services');

        $code = $this->generator->generateClass('TestService');

        $this->assertMatchesRegularExpression('/namespace App\\\\Services;\n\n/', $code);
    }

    /**
     * Test PSR-12 compliance: use statements sorted
     */
    public function testPsr12UseStatementsSorted(): void
    {
        $this->generator
            ->setNamespace('App\Services')
            ->addUseStatements([
                'Zebra\ZebraClass',
                'App\Models\UserModel',
                'Beta\BetaClass',
            ]);

        $code = $this->generator->generateClass('TestService');

        preg_match_all('/use ([^;]+);/', $code, $matches);
        $useStatements = $matches[1];

        $sorted = $useStatements;
        sort($sorted);

        $this->assertEquals($sorted, $useStatements);
    }
}

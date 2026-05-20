<?php

namespace Tests\Unit\Refactor\Generation;

use App\Libraries\Refactor\Generation\CodeGenerator;
use App\Libraries\Refactor\Generation\QueryAnalyzer;
use App\Libraries\Refactor\Generation\RepositoryGenerator;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * RepositoryGeneratorTest
 * 
 * Tests for the RepositoryGenerator class that generates repository classes
 * following the Repository pattern for CodeIgniter 4.
 */
class RepositoryGeneratorTest extends CIUnitTestCase
{
    private RepositoryGenerator $generator;
    private CodeGenerator $codeGenerator;
    private QueryAnalyzer $queryAnalyzer;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->codeGenerator = new CodeGenerator();
        $this->queryAnalyzer = new QueryAnalyzer();
        $this->generator = new RepositoryGenerator($this->codeGenerator, $this->queryAnalyzer);
    }

    /**
     * Test basic repository generation with minimal data
     */
    public function testGenerateBasicRepository(): void
    {
        $data = [
            'modelName' => 'User',
            'tableName' => 'users',
            'primaryKey' => 'id',
        ];

        $code = $this->generator->generate($data);

        // Verify namespace
        $this->assertStringContainsString('namespace App\Repositories;', $code);

        // Verify class name
        $this->assertStringContainsString('class UserRepository', $code);

        // Verify use statements
        $this->assertStringContainsString('use CodeIgniter\Database\ConnectionInterface;', $code);
        $this->assertStringContainsString('use CodeIgniter\Database\BaseBuilder;', $code);

        // Verify properties
        $this->assertStringContainsString('private ConnectionInterface $db;', $code);
        $this->assertStringContainsString('private string $table;', $code);
        $this->assertStringContainsString('private string $primaryKey;', $code);

        // Verify constructor
        $this->assertStringContainsString('public function __construct(', $code);
        $this->assertStringContainsString('ConnectionInterface $db', $code);
        $this->assertStringContainsString('$this->db = $db;', $code);
        $this->assertStringContainsString("\$this->table = 'users';", $code);
        $this->assertStringContainsString("\$this->primaryKey = 'id';", $code);

        // Verify CRUD methods exist
        $this->assertStringContainsString('public function findAll(', $code);
        $this->assertStringContainsString('public function findById(', $code);
        $this->assertStringContainsString('public function findBy(', $code);
        $this->assertStringContainsString('public function findOneBy(', $code);
        $this->assertStringContainsString('public function create(', $code);
        $this->assertStringContainsString('public function update(', $code);
        $this->assertStringContainsString('public function updateBy(', $code);
        $this->assertStringContainsString('public function delete(', $code);
        $this->assertStringContainsString('public function deleteBy(', $code);
        $this->assertStringContainsString('public function count(', $code);
        $this->assertStringContainsString('public function exists(', $code);
    }

    /**
     * Test repository generation with custom namespace
     */
    public function testGenerateRepositoryWithCustomNamespace(): void
    {
        $data = [
            'modelName' => 'Product',
            'tableName' => 'products',
            'namespace' => 'App\Modules\Shop\Repositories',
        ];

        $code = $this->generator->generate($data);

        $this->assertStringContainsString('namespace App\Modules\Shop\Repositories;', $code);
        $this->assertStringContainsString('class ProductRepository', $code);
    }

    /**
     * Test repository generation with custom queries
     */
    public function testGenerateRepositoryWithCustomQueries(): void
    {
        $data = [
            'modelName' => 'User',
            'tableName' => 'users',
            'queries' => [
                [
                    'methodName' => 'findActiveUsers',
                    'query' => "SELECT * FROM users WHERE status = 'active'",
                    'description' => 'Find all active users',
                    'params' => [],
                    'returnType' => 'array',
                ],
                [
                    'methodName' => 'findByEmail',
                    'query' => "SELECT * FROM users WHERE email = ?",
                    'description' => 'Find user by email address',
                    'params' => [
                        [
                            'name' => 'email',
                            'type' => 'string',
                            'description' => 'Email address',
                        ],
                    ],
                    'returnType' => 'array|null',
                ],
            ],
        ];

        $code = $this->generator->generate($data);

        // Verify custom methods exist
        $this->assertStringContainsString('public function findActiveUsers(', $code);
        $this->assertStringContainsString('public function findByEmail(', $code);

        // Verify method descriptions
        $this->assertStringContainsString('Find all active users', $code);
        $this->assertStringContainsString('Find user by email address', $code);

        // Verify Query Builder usage
        $this->assertStringContainsString('$builder = $this->db->table($this->table);', $code);
    }

    /**
     * Test generateCrudMethods returns correct structure
     */
    public function testGenerateCrudMethods(): void
    {
        $methods = $this->generator->generateCrudMethods('User', 'id');

        // Verify we have all CRUD methods
        $this->assertIsArray($methods);
        $this->assertCount(11, $methods); // 11 standard CRUD methods

        $methodNames = array_column($methods, 'name');
        $expectedMethods = [
            'findAll',
            'findById',
            'findBy',
            'findOneBy',
            'create',
            'update',
            'updateBy',
            'delete',
            'deleteBy',
            'count',
            'exists',
        ];

        foreach ($expectedMethods as $expectedMethod) {
            $this->assertContains($expectedMethod, $methodNames);
        }

        // Verify method structure
        foreach ($methods as $method) {
            $this->assertArrayHasKey('name', $method);
            $this->assertArrayHasKey('visibility', $method);
            $this->assertArrayHasKey('return', $method);
            $this->assertArrayHasKey('description', $method);
            $this->assertArrayHasKey('body', $method);
        }
    }

    /**
     * Test findAll method generation
     */
    public function testFindAllMethodGeneration(): void
    {
        $methods = $this->generator->generateCrudMethods('User', 'id');
        $findAll = array_values(array_filter($methods, fn($m) => $m['name'] === 'findAll'))[0];

        $this->assertEquals('findAll', $findAll['name']);
        $this->assertEquals('public', $findAll['visibility']);
        $this->assertEquals('array', $findAll['return']);
        $this->assertStringContainsString('Retrieve all records', $findAll['description']);

        // Verify parameters
        $this->assertCount(2, $findAll['params']);
        $this->assertEquals('limit', $findAll['params'][0]['name']);
        $this->assertEquals('offset', $findAll['params'][1]['name']);

        // Verify body contains Query Builder code
        $body = implode("\n", $findAll['body']);
        $this->assertStringContainsString('$builder = $this->db->table($this->table);', $body);
        $this->assertStringContainsString('$builder->limit($limit, $offset);', $body);
        $this->assertStringContainsString('return $builder->get()->getResultArray();', $body);
    }

    /**
     * Test findById method generation
     */
    public function testFindByIdMethodGeneration(): void
    {
        $methods = $this->generator->generateCrudMethods('User', 'id');
        $findById = array_values(array_filter($methods, fn($m) => $m['name'] === 'findById'))[0];

        $this->assertEquals('findById', $findById['name']);
        $this->assertEquals('array|null', $findById['return']);

        // Verify body uses primary key
        $body = implode("\n", $findById['body']);
        $this->assertStringContainsString('$builder->where($this->primaryKey, $id);', $body);
        $this->assertStringContainsString('return $result ?: null;', $body);
    }

    /**
     * Test create method generation
     */
    public function testCreateMethodGeneration(): void
    {
        $methods = $this->generator->generateCrudMethods('User', 'id');
        $create = array_values(array_filter($methods, fn($m) => $m['name'] === 'create'))[0];

        $this->assertEquals('create', $create['name']);
        $this->assertEquals('int|string|false', $create['return']);

        // Verify body uses insert and returns insertID
        $body = implode("\n", $create['body']);
        $this->assertStringContainsString('$builder->insert($data)', $body);
        $this->assertStringContainsString('return $this->db->insertID();', $body);
    }

    /**
     * Test update method generation
     */
    public function testUpdateMethodGeneration(): void
    {
        $methods = $this->generator->generateCrudMethods('User', 'id');
        $update = array_values(array_filter($methods, fn($m) => $m['name'] === 'update'))[0];

        $this->assertEquals('update', $update['name']);
        $this->assertEquals('bool', $update['return']);

        // Verify body uses where and update
        $body = implode("\n", $update['body']);
        $this->assertStringContainsString('$builder->where($this->primaryKey, $id);', $body);
        $this->assertStringContainsString('return $builder->update($data);', $body);
    }

    /**
     * Test delete method generation
     */
    public function testDeleteMethodGeneration(): void
    {
        $methods = $this->generator->generateCrudMethods('User', 'id');
        $delete = array_values(array_filter($methods, fn($m) => $m['name'] === 'delete'))[0];

        $this->assertEquals('delete', $delete['name']);
        $this->assertEquals('bool', $delete['return']);

        // Verify body uses where and delete
        $body = implode("\n", $delete['body']);
        $this->assertStringContainsString('$builder->where($this->primaryKey, $id);', $body);
        $this->assertStringContainsString('return $builder->delete();', $body);
    }

    /**
     * Test convertToQueryBuilder delegates to QueryAnalyzer
     */
    public function testConvertToQueryBuilder(): void
    {
        $rawQuery = "SELECT * FROM users WHERE status = 'active'";
        $result = $this->generator->convertToQueryBuilder($rawQuery);

        // Verify it returns Query Builder code
        $this->assertIsString($result);
        $this->assertStringContainsString('$builder', $result);
    }

    /**
     * Test generateComplexQueryMethod creates proper method structure
     */
    public function testGenerateComplexQueryMethod(): void
    {
        $method = $this->generator->generateComplexQueryMethod(
            'findActiveUsers',
            "SELECT * FROM users WHERE status = 'active'",
            'Find all active users',
            [],
            'array'
        );

        $this->assertEquals('findActiveUsers', $method['name']);
        $this->assertEquals('public', $method['visibility']);
        $this->assertEquals('array', $method['return']);
        $this->assertEquals('Find all active users', $method['description']);

        // Verify body structure
        $body = implode("\n", $method['body']);
        $this->assertStringContainsString('$builder = $this->db->table($this->table);', $body);
        $this->assertStringContainsString('return $builder->get()->getResultArray();', $body);
    }

    /**
     * Test generateComplexQueryMethod with parameters
     */
    public function testGenerateComplexQueryMethodWithParameters(): void
    {
        $method = $this->generator->generateComplexQueryMethod(
            'findByStatus',
            "SELECT * FROM users WHERE status = \$status",
            'Find users by status',
            [
                [
                    'name' => 'status',
                    'type' => 'string',
                    'description' => 'User status',
                ],
            ],
            'array'
        );

        $this->assertCount(1, $method['params']);
        $this->assertEquals('status', $method['params'][0]['name']);

        // Verify parameter binding comment exists
        $body = implode("\n", $method['body']);
        $this->assertStringContainsString('Parameter binding for SQL injection prevention', $body);
    }

    /**
     * Test generateComplexQueryMethod with different return types
     */
    public function testGenerateComplexQueryMethodReturnTypes(): void
    {
        // Test array|null return type
        $method1 = $this->generator->generateComplexQueryMethod(
            'findOne',
            "SELECT * FROM users WHERE id = 1",
            'Find one user',
            [],
            'array|null'
        );
        $body1 = implode("\n", $method1['body']);
        $this->assertStringContainsString('$result = $builder->get()->getRowArray();', $body1);
        $this->assertStringContainsString('return $result ?: null;', $body1);

        // Test int return type
        $method2 = $this->generator->generateComplexQueryMethod(
            'countUsers',
            "SELECT COUNT(*) FROM users",
            'Count users',
            [],
            'int'
        );
        $body2 = implode("\n", $method2['body']);
        $this->assertStringContainsString('return $builder->countAllResults();', $body2);

        // Test bool return type
        $method3 = $this->generator->generateComplexQueryMethod(
            'hasUsers',
            "SELECT * FROM users",
            'Check if users exist',
            [],
            'bool'
        );
        $body3 = implode("\n", $method3['body']);
        $this->assertStringContainsString('return $builder->get()->getNumRows() > 0;', $body3);
    }

    /**
     * Test addParameterBinding with parameters
     */
    public function testAddParameterBinding(): void
    {
        $result = $this->generator->addParameterBinding(['userId', 'status']);

        $this->assertIsString($result);
        $this->assertStringContainsString('userId', $result);
        $this->assertStringContainsString('status', $result);
    }

    /**
     * Test addParameterBinding with empty parameters
     */
    public function testAddParameterBindingEmpty(): void
    {
        $result = $this->generator->addParameterBinding([]);

        $this->assertEquals('', $result);
    }

    /**
     * Test repository generation with default table name
     */
    public function testGenerateRepositoryWithDefaultTableName(): void
    {
        $data = [
            'modelName' => 'Product',
        ];

        $code = $this->generator->generate($data);

        // Should default to lowercase model name + 's'
        $this->assertStringContainsString("\$this->table = 'products';", $code);
    }

    /**
     * Test repository generation with default primary key
     */
    public function testGenerateRepositoryWithDefaultPrimaryKey(): void
    {
        $data = [
            'modelName' => 'User',
            'tableName' => 'users',
        ];

        $code = $this->generator->generate($data);

        // Should default to 'id'
        $this->assertStringContainsString("\$this->primaryKey = 'id';", $code);
    }

    /**
     * Test repository generation throws exception for invalid data
     */
    public function testGenerateThrowsExceptionForInvalidData(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Repository generation data must be an array');

        $this->generator->generate('invalid');
    }

    /**
     * Test repository generation throws exception for missing modelName
     */
    public function testGenerateThrowsExceptionForMissingModelName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('modelName is required');

        $this->generator->generate([]);
    }

    /**
     * Test generated code has valid PHP syntax
     */
    public function testGeneratedCodeHasValidSyntax(): void
    {
        $data = [
            'modelName' => 'User',
            'tableName' => 'users',
            'primaryKey' => 'id',
        ];

        $code = $this->generator->generate($data);

        // Validate syntax using CodeGenerator
        $validation = $this->codeGenerator->validateSyntax($code);

        $this->assertTrue($validation['valid'], 'Generated code should have valid PHP syntax');
        $this->assertNull($validation['error']);
    }

    /**
     * Test generated code follows PSR-12 standards
     */
    public function testGeneratedCodeFollowsPsr12(): void
    {
        $data = [
            'modelName' => 'User',
            'tableName' => 'users',
        ];

        $code = $this->generator->generate($data);

        // Check PSR-12 compliance indicators
        $this->assertStringStartsWith('<?php', $code);
        $this->assertStringContainsString("\n\nnamespace", $code); // Blank line after opening tag
        // Check for blank line between use statements and class (or class doc)
        $this->assertMatchesRegularExpression('/use [^;]+;\n\n/', $code); // Blank line after use statements
        $this->assertStringEndsWith("\n", $code); // File ends with newline
    }

    /**
     * Test generated repository has proper PHPDoc comments
     */
    public function testGeneratedRepositoryHasPhpDoc(): void
    {
        $data = [
            'modelName' => 'User',
            'tableName' => 'users',
        ];

        $code = $this->generator->generate($data);

        // Check for class PHPDoc
        $this->assertStringContainsString('/**', $code);
        $this->assertStringContainsString('* Repository for User data access operations', $code);
        $this->assertStringContainsString('* @package', $code);

        // Check for method PHPDoc
        $this->assertStringContainsString('* @param', $code);
        $this->assertStringContainsString('* @return', $code);
    }

    /**
     * Test generated repository has type hints
     */
    public function testGeneratedRepositoryHasTypeHints(): void
    {
        $data = [
            'modelName' => 'User',
            'tableName' => 'users',
        ];

        $code = $this->generator->generate($data);

        // Check for property type hints
        $this->assertStringContainsString('private ConnectionInterface $db;', $code);
        $this->assertStringContainsString('private string $table;', $code);
        $this->assertStringContainsString('private string $primaryKey;', $code);

        // Check for parameter type hints
        $this->assertStringContainsString('int $limit', $code);
        $this->assertStringContainsString('array $criteria', $code);
        $this->assertStringContainsString('array $data', $code);

        // Check for return type hints
        $this->assertStringContainsString('): array', $code);
        $this->assertStringContainsString('): bool', $code);
        $this->assertStringContainsString('): int', $code);
    }
}

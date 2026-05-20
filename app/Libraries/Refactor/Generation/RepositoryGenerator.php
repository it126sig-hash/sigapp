<?php

namespace App\Libraries\Refactor\Generation;

use App\Libraries\Refactor\Contracts\GeneratorInterface;

/**
 * RepositoryGenerator
 * 
 * Generates repository classes following the Repository pattern for CodeIgniter 4.
 * Creates repository classes with CRUD operations and custom query methods using
 * Query Builder for safe database operations. All generated code follows PSR-12
 * standards with proper type hints and PHPDoc comments.
 * 
 * @package App\Libraries\Refactor\Generation
 */
class RepositoryGenerator implements GeneratorInterface
{
    /**
     * @var CodeGenerator Code generator for creating PHP classes
     */
    private CodeGenerator $codeGenerator;

    /**
     * @var QueryAnalyzer Query analyzer for converting raw SQL to Query Builder
     */
    private QueryAnalyzer $queryAnalyzer;

    /**
     * Constructor
     * 
     * @param CodeGenerator $codeGenerator Code generator instance
     * @param QueryAnalyzer $queryAnalyzer Query analyzer instance
     */
    public function __construct(
        CodeGenerator $codeGenerator,
        QueryAnalyzer $queryAnalyzer
    ) {
        $this->codeGenerator = $codeGenerator;
        $this->queryAnalyzer = $queryAnalyzer;
    }

    /**
     * Generate repository class code
     * 
     * @param mixed $data Repository generation data containing:
     *                    - modelName: Name of the model (e.g., "User")
     *                    - tableName: Database table name (e.g., "users")
     *                    - primaryKey: Primary key field name (default: "id")
     *                    - queries: Array of custom queries to convert (optional)
     *                    - namespace: Namespace for the repository (default: "App\Repositories")
     * @return string Generated repository class code
     */
    public function generate(mixed $data): string
    {
        if (!is_array($data)) {
            throw new \InvalidArgumentException('Repository generation data must be an array');
        }

        $modelName = $data['modelName'] ?? throw new \InvalidArgumentException('modelName is required');
        $tableName = $data['tableName'] ?? strtolower($modelName) . 's';
        $primaryKey = $data['primaryKey'] ?? 'id';
        $queries = $data['queries'] ?? [];
        $namespace = $data['namespace'] ?? 'App\Repositories';

        $repositoryName = $modelName . 'Repository';

        // Reset and configure code generator
        $this->codeGenerator->reset();
        $this->codeGenerator->setNamespace($namespace);

        // Add use statements
        $this->codeGenerator->addUseStatements([
            'CodeIgniter\Database\ConnectionInterface',
            'CodeIgniter\Database\BaseBuilder',
        ]);

        // Generate class options
        $classOptions = [
            'description' => "Repository for {$modelName} data access operations. Provides CRUD operations and custom queries using CodeIgniter 4 Query Builder for safe database operations.",
            'properties' => [
                [
                    'name' => 'db',
                    'type' => 'ConnectionInterface',
                    'visibility' => 'private',
                    'description' => 'Database connection instance',
                ],
                [
                    'name' => 'table',
                    'type' => 'string',
                    'visibility' => 'private',
                    'description' => 'Table name',
                ],
                [
                    'name' => 'primaryKey',
                    'type' => 'string',
                    'visibility' => 'private',
                    'description' => 'Primary key field name',
                ],
            ],
            'constructor' => [
                'params' => [
                    [
                        'name' => 'db',
                        'type' => 'ConnectionInterface',
                        'description' => 'Database connection instance',
                    ],
                ],
                'body' => [
                    '$this->db = $db;',
                    "\$this->table = '{$tableName}';",
                    "\$this->primaryKey = '{$primaryKey}';",
                ],
            ],
            'methods' => array_merge(
                $this->generateCrudMethods($modelName, $primaryKey),
                $this->generateCustomQueryMethods($queries, $modelName)
            ),
        ];

        return $this->codeGenerator->generateClass($repositoryName, $classOptions);
    }

    /**
     * Generate standard CRUD methods for the repository
     * 
     * @param string $modelName Model name for documentation
     * @param string $primaryKey Primary key field name
     * @return array<array<string, mixed>> Array of method definitions
     */
    public function generateCrudMethods(string $modelName, string $primaryKey = 'id'): array
    {
        return [
            // findAll method
            [
                'name' => 'findAll',
                'visibility' => 'public',
                'params' => [
                    [
                        'name' => 'limit',
                        'type' => 'int',
                        'default' => 'null',
                        'description' => 'Maximum number of records to return',
                    ],
                    [
                        'name' => 'offset',
                        'type' => 'int',
                        'default' => '0',
                        'description' => 'Number of records to skip',
                    ],
                ],
                'return' => 'array',
                'returnDescription' => 'Array of records',
                'description' => 'Retrieve all records from the table',
                'body' => [
                    '$builder = $this->db->table($this->table);',
                    '',
                    'if ($limit !== null) {',
                    '    $builder->limit($limit, $offset);',
                    '}',
                    '',
                    'return $builder->get()->getResultArray();',
                ],
            ],

            // findById method
            [
                'name' => 'findById',
                'visibility' => 'public',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'int|string',
                        'description' => 'Primary key value',
                    ],
                ],
                'return' => 'array|null',
                'returnDescription' => 'Record array or null if not found',
                'description' => 'Find a single record by primary key',
                'body' => [
                    '$builder = $this->db->table($this->table);',
                    '$builder->where($this->primaryKey, $id);',
                    '',
                    '$result = $builder->get()->getRowArray();',
                    '',
                    'return $result ?: null;',
                ],
            ],

            // findBy method
            [
                'name' => 'findBy',
                'visibility' => 'public',
                'params' => [
                    [
                        'name' => 'criteria',
                        'type' => 'array',
                        'description' => 'Array of field => value pairs for WHERE conditions',
                    ],
                    [
                        'name' => 'limit',
                        'type' => 'int',
                        'default' => 'null',
                        'description' => 'Maximum number of records to return',
                    ],
                    [
                        'name' => 'offset',
                        'type' => 'int',
                        'default' => '0',
                        'description' => 'Number of records to skip',
                    ],
                ],
                'return' => 'array',
                'returnDescription' => 'Array of matching records',
                'description' => 'Find records matching the given criteria',
                'body' => [
                    '$builder = $this->db->table($this->table);',
                    '',
                    'foreach ($criteria as $field => $value) {',
                    '    $builder->where($field, $value);',
                    '}',
                    '',
                    'if ($limit !== null) {',
                    '    $builder->limit($limit, $offset);',
                    '}',
                    '',
                    'return $builder->get()->getResultArray();',
                ],
            ],

            // findOneBy method
            [
                'name' => 'findOneBy',
                'visibility' => 'public',
                'params' => [
                    [
                        'name' => 'criteria',
                        'type' => 'array',
                        'description' => 'Array of field => value pairs for WHERE conditions',
                    ],
                ],
                'return' => 'array|null',
                'returnDescription' => 'Record array or null if not found',
                'description' => 'Find a single record matching the given criteria',
                'body' => [
                    '$builder = $this->db->table($this->table);',
                    '',
                    'foreach ($criteria as $field => $value) {',
                    '    $builder->where($field, $value);',
                    '}',
                    '',
                    '$result = $builder->get()->getRowArray();',
                    '',
                    'return $result ?: null;',
                ],
            ],

            // create method
            [
                'name' => 'create',
                'visibility' => 'public',
                'params' => [
                    [
                        'name' => 'data',
                        'type' => 'array',
                        'description' => 'Data to insert',
                    ],
                ],
                'return' => 'int|string|false',
                'returnDescription' => 'Insert ID on success, false on failure',
                'description' => 'Insert a new record into the table',
                'body' => [
                    '$builder = $this->db->table($this->table);',
                    '',
                    'if ($builder->insert($data)) {',
                    '    return $this->db->insertID();',
                    '}',
                    '',
                    'return false;',
                ],
            ],

            // update method
            [
                'name' => 'update',
                'visibility' => 'public',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'int|string',
                        'description' => 'Primary key value',
                    ],
                    [
                        'name' => 'data',
                        'type' => 'array',
                        'description' => 'Data to update',
                    ],
                ],
                'return' => 'bool',
                'returnDescription' => 'True on success, false on failure',
                'description' => 'Update a record by primary key',
                'body' => [
                    '$builder = $this->db->table($this->table);',
                    '$builder->where($this->primaryKey, $id);',
                    '',
                    'return $builder->update($data);',
                ],
            ],

            // updateBy method
            [
                'name' => 'updateBy',
                'visibility' => 'public',
                'params' => [
                    [
                        'name' => 'criteria',
                        'type' => 'array',
                        'description' => 'Array of field => value pairs for WHERE conditions',
                    ],
                    [
                        'name' => 'data',
                        'type' => 'array',
                        'description' => 'Data to update',
                    ],
                ],
                'return' => 'bool',
                'returnDescription' => 'True on success, false on failure',
                'description' => 'Update records matching the given criteria',
                'body' => [
                    '$builder = $this->db->table($this->table);',
                    '',
                    'foreach ($criteria as $field => $value) {',
                    '    $builder->where($field, $value);',
                    '}',
                    '',
                    'return $builder->update($data);',
                ],
            ],

            // delete method
            [
                'name' => 'delete',
                'visibility' => 'public',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'int|string',
                        'description' => 'Primary key value',
                    ],
                ],
                'return' => 'bool',
                'returnDescription' => 'True on success, false on failure',
                'description' => 'Delete a record by primary key',
                'body' => [
                    '$builder = $this->db->table($this->table);',
                    '$builder->where($this->primaryKey, $id);',
                    '',
                    'return $builder->delete();',
                ],
            ],

            // deleteBy method
            [
                'name' => 'deleteBy',
                'visibility' => 'public',
                'params' => [
                    [
                        'name' => 'criteria',
                        'type' => 'array',
                        'description' => 'Array of field => value pairs for WHERE conditions',
                    ],
                ],
                'return' => 'bool',
                'returnDescription' => 'True on success, false on failure',
                'description' => 'Delete records matching the given criteria',
                'body' => [
                    '$builder = $this->db->table($this->table);',
                    '',
                    'foreach ($criteria as $field => $value) {',
                    '    $builder->where($field, $value);',
                    '}',
                    '',
                    'return $builder->delete();',
                ],
            ],

            // count method
            [
                'name' => 'count',
                'visibility' => 'public',
                'params' => [
                    [
                        'name' => 'criteria',
                        'type' => 'array',
                        'default' => '[]',
                        'description' => 'Optional array of field => value pairs for WHERE conditions',
                    ],
                ],
                'return' => 'int',
                'returnDescription' => 'Number of matching records',
                'description' => 'Count records matching the given criteria',
                'body' => [
                    '$builder = $this->db->table($this->table);',
                    '',
                    'foreach ($criteria as $field => $value) {',
                    '    $builder->where($field, $value);',
                    '}',
                    '',
                    'return $builder->countAllResults();',
                ],
            ],

            // exists method
            [
                'name' => 'exists',
                'visibility' => 'public',
                'params' => [
                    [
                        'name' => 'id',
                        'type' => 'int|string',
                        'description' => 'Primary key value',
                    ],
                ],
                'return' => 'bool',
                'returnDescription' => 'True if record exists, false otherwise',
                'description' => 'Check if a record exists by primary key',
                'body' => [
                    '$builder = $this->db->table($this->table);',
                    '$builder->where($this->primaryKey, $id);',
                    '',
                    'return $builder->countAllResults() > 0;',
                ],
            ],
        ];
    }

    /**
     * Convert raw SQL query to Query Builder syntax
     * 
     * @param string $rawQuery Raw SQL query
     * @return string Query Builder code
     */
    public function convertToQueryBuilder(string $rawQuery): string
    {
        return $this->queryAnalyzer->convertToQueryBuilder($rawQuery, '$builder');
    }

    /**
     * Generate custom query methods from raw SQL queries
     * 
     * @param array<array<string, mixed>> $queries Array of query definitions
     * @param string $modelName Model name for documentation
     * @return array<array<string, mixed>> Array of method definitions
     */
    public function generateCustomQueryMethods(array $queries, string $modelName): array
    {
        $methods = [];

        foreach ($queries as $query) {
            $methodName = $query['methodName'] ?? null;
            $rawQuery = $query['query'] ?? null;
            $description = $query['description'] ?? "Custom query method for {$modelName}";
            $params = $query['params'] ?? [];
            $returnType = $query['returnType'] ?? 'array';

            if (!$methodName || !$rawQuery) {
                continue;
            }

            $methods[] = $this->generateComplexQueryMethod(
                $methodName,
                $rawQuery,
                $description,
                $params,
                $returnType
            );
        }

        return $methods;
    }

    /**
     * Generate a complex query method from raw SQL
     * 
     * @param string $methodName Method name
     * @param string $rawQuery Raw SQL query
     * @param string $description Method description
     * @param array<array<string, mixed>> $params Method parameters
     * @param string $returnType Return type
     * @return array<string, mixed> Method definition
     */
    public function generateComplexQueryMethod(
        string $methodName,
        string $rawQuery,
        string $description,
        array $params = [],
        string $returnType = 'array'
    ): array {
        // Analyze the query to identify parameters
        $analysis = $this->queryAnalyzer->analyze($rawQuery);
        $queryParameters = $analysis['parameters'];

        // Convert raw query to Query Builder
        $builderCode = $this->convertToQueryBuilder($rawQuery);

        // Add parameter binding if needed
        $parameterBindingCode = $this->addParameterBinding($queryParameters);

        // Build method body
        $body = ['$builder = $this->db->table($this->table);'];

        if ($parameterBindingCode) {
            $body[] = '';
            $body[] = '// Parameter binding for SQL injection prevention';
            $body[] = $parameterBindingCode;
        }

        $body[] = '';
        $body[] = '// Query Builder implementation';

        // Split builder code into lines and add to body
        $builderLines = explode("\n", $builderCode);
        foreach ($builderLines as $line) {
            if (trim($line)) {
                $body[] = $line . ';';
            }
        }

        $body[] = '';

        // Add appropriate return statement based on return type
        if ($returnType === 'array') {
            $body[] = 'return $builder->get()->getResultArray();';
        } elseif ($returnType === 'array|null') {
            $body[] = '$result = $builder->get()->getRowArray();';
            $body[] = 'return $result ?: null;';
        } elseif ($returnType === 'int') {
            $body[] = 'return $builder->countAllResults();';
        } elseif ($returnType === 'bool') {
            $body[] = 'return $builder->get()->getNumRows() > 0;';
        } else {
            $body[] = 'return $builder->get()->getResultArray();';
        }

        return [
            'name' => $methodName,
            'visibility' => 'public',
            'params' => $params,
            'return' => $returnType,
            'description' => $description,
            'body' => $body,
        ];
    }

    /**
     * Add parameter binding code for SQL injection prevention
     * 
     * @param array<string> $parameters Array of parameter names
     * @return string Parameter binding code
     */
    public function addParameterBinding(array $parameters): string
    {
        if (empty($parameters)) {
            return '';
        }

        return $this->queryAnalyzer->generateParameterBinding($parameters);
    }

    /**
     * Get the builder instance for the repository
     * 
     * @return BaseBuilder Query builder instance
     */
    private function getBuilder(): BaseBuilder
    {
        return $this->db->table($this->table);
    }
}

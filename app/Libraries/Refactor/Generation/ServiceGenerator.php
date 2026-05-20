<?php

namespace App\Libraries\Refactor\Generation;

use App\Libraries\Refactor\Contracts\GeneratorInterface;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeFinder;
use PhpParser\ParserFactory;

/**
 * ServiceGenerator
 * 
 * Generates service layer classes by extracting business logic from controllers.
 * Creates services that follow the Thin Controller → Service → Repository pattern
 * with proper dependency injection, transaction management, and structured responses.
 * 
 * @package App\Libraries\Refactor\Generation
 */
class ServiceGenerator implements GeneratorInterface
{
    /**
     * @var CodeGenerator Code generator instance
     */
    private CodeGenerator $codeGenerator;

    /**
     * @var ValidationExtractor Validation extractor instance
     */
    private ValidationExtractor $validationExtractor;

    /**
     * @var \PhpParser\Parser PHP parser instance
     */
    private $parser;

    /**
     * @var NodeFinder Node finder for AST traversal
     */
    private NodeFinder $nodeFinder;

    /**
     * Constructor
     * 
     * @param CodeGenerator $codeGenerator Code generator instance
     * @param ValidationExtractor $validationExtractor Validation extractor instance
     */
    public function __construct(CodeGenerator $codeGenerator, ValidationExtractor $validationExtractor)
    {
        $this->codeGenerator = $codeGenerator;
        $this->validationExtractor = $validationExtractor;
        $this->parser = (new ParserFactory())->createForNewestSupportedVersion();
        $this->nodeFinder = new NodeFinder();
    }

    /**
     * Generate service class code
     * 
     * @param mixed $data Generation data (controller path or array with controller info)
     * @return string Generated service class code
     */
    public function generate(mixed $data): string
    {
        if (is_string($data)) {
            // Assume it's a controller file path
            $controllerPath = $data;
            $controllerCode = file_get_contents($controllerPath);
            $controllerName = basename($controllerPath, '.php');
            
            return $this->generateFromController($controllerName, $controllerCode);
        }

        if (is_array($data)) {
            $controllerName = $data['controllerName'] ?? 'Unknown';
            $controllerCode = $data['controllerCode'] ?? '';
            $businessLogic = $data['businessLogic'] ?? [];
            $repositories = $data['repositories'] ?? [];
            
            return $this->generateFromExtractedLogic($controllerName, $businessLogic, $repositories);
        }

        return '';
    }

    /**
     * Generate service class from controller code
     * 
     * @param string $controllerName Controller name (e.g., "Transaksi")
     * @param string $controllerCode Controller PHP code
     * @param array<string> $repositories Repository class names to inject
     * @return string Generated service class code
     */
    public function generateFromController(
        string $controllerName,
        string $controllerCode,
        array $repositories = []
    ): string {
        // Extract business logic from controller
        $businessLogic = $this->extractBusinessLogic($controllerCode);
        
        // Auto-detect repositories if not provided
        if (empty($repositories)) {
            $repositories = $this->detectRepositories($controllerCode);
        }

        return $this->generateFromExtractedLogic($controllerName, $businessLogic, $repositories);
    }

    /**
     * Generate service class from extracted business logic
     * 
     * @param string $controllerName Controller name
     * @param array $businessLogic Extracted business logic methods
     * @param array<string> $repositories Repository class names
     * @return string Generated service class code
     */
    public function generateFromExtractedLogic(
        string $controllerName,
        array $businessLogic,
        array $repositories = []
    ): string {
        $serviceName = $this->generateServiceName($controllerName);
        
        $this->codeGenerator->reset();
        $this->codeGenerator->setNamespace('App\\Services');

        // Add use statements
        $this->addUseStatements($repositories);

        // Generate properties for repositories
        $properties = $this->generateRepositoryProperties($repositories);

        // Generate constructor
        $constructor = $this->generateConstructor($repositories);

        // Generate service methods from business logic
        $methods = [];
        foreach ($businessLogic as $logic) {
            $methods[] = $this->generateServiceMethod($logic);
        }

        // Generate the service class
        $classCode = $this->codeGenerator->generateClass($serviceName, [
            'description' => "Service class for {$controllerName} business logic",
            'properties' => $properties,
            'constructor' => $constructor,
            'methods' => $methods,
        ]);

        return $classCode;
    }

    /**
     * Extract business logic from controller code
     * 
     * Identifies methods that contain business logic (database operations,
     * complex calculations, external service calls, etc.)
     * 
     * @param string $controllerCode Controller PHP code
     * @return array<array{name: string, code: string, hasTransaction: bool, hasValidation: bool}> Extracted business logic
     */
    public function extractBusinessLogic(string $controllerCode): array
    {
        try {
            $ast = $this->parser->parse($controllerCode);
        } catch (\Exception $e) {
            return [];
        }

        if (!$ast) {
            return [];
        }

        $businessLogic = [];
        $methods = $this->nodeFinder->findInstanceOf($ast, ClassMethod::class);

        foreach ($methods as $method) {
            $methodName = $method->name->toString();
            
            // Skip constructor and magic methods
            if ($methodName === '__construct' || str_starts_with($methodName, '__')) {
                continue;
            }

            // Check if method contains business logic
            $hasBusinessLogic = $this->methodHasBusinessLogic($method);
            
            if ($hasBusinessLogic) {
                $businessLogic[] = [
                    'name' => $methodName,
                    'method' => $method,
                    'hasTransaction' => $this->methodHasTransaction($method),
                    'hasValidation' => $this->methodHasValidation($method),
                ];
            }
        }

        return $businessLogic;
    }

    /**
     * Check if method contains business logic
     * 
     * @param ClassMethod $method Method AST node
     * @return bool True if method has business logic
     */
    private function methodHasBusinessLogic(ClassMethod $method): bool
    {
        // Look for indicators of business logic by traversing the AST
        // - Database operations (insert, update, delete, query)
        // - Model method calls
        // - Repository method calls
        // - Service method calls
        // - Transaction management
        
        // Look for method calls that indicate business logic
        $methodCalls = $this->nodeFinder->findInstanceOf($method, Node\Expr\MethodCall::class);
        
        foreach ($methodCalls as $call) {
            // Check if it's a database operation
            if ($call->name instanceof Node\Identifier) {
                $methodName = $call->name->toString();
                
                // Database operations
                $dbMethods = ['insert', 'update', 'delete', 'save', 'query', 'transStart', 'transComplete'];
                if (in_array($methodName, $dbMethods)) {
                    return true;
                }
            }
            
            // Check if calling on $this->db, $this->*Model, $this->*Repo, $this->*Service
            if ($call->var instanceof Node\Expr\PropertyFetch) {
                $propertyFetch = $call->var;
                if ($propertyFetch->var instanceof Node\Expr\Variable && 
                    $propertyFetch->var->name === 'this' &&
                    $propertyFetch->name instanceof Node\Identifier) {
                    
                    $propertyName = $propertyFetch->name->toString();
                    
                    // Check for common patterns
                    if ($propertyName === 'db' ||
                        str_ends_with($propertyName, 'Model') ||
                        str_ends_with($propertyName, 'Repo') ||
                        str_ends_with($propertyName, 'Repository') ||
                        str_ends_with($propertyName, 'Service')) {
                        return true;
                    }
                }
            }
        }
        
        // Look for table() calls which indicate Query Builder usage
        $staticCalls = $this->nodeFinder->findInstanceOf($method, Node\Expr\StaticCall::class);
        foreach ($staticCalls as $call) {
            if ($call->name instanceof Node\Identifier && $call->name->toString() === 'table') {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if method uses transactions
     * 
     * @param ClassMethod $method Method AST node
     * @return bool True if method uses transactions
     */
    private function methodHasTransaction(ClassMethod $method): bool
    {
        // Look for transStart() method calls
        $methodCalls = $this->nodeFinder->findInstanceOf($method, Node\Expr\MethodCall::class);
        
        foreach ($methodCalls as $call) {
            if ($call->name instanceof Node\Identifier && 
                $call->name->toString() === 'transStart') {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if method has validation
     * 
     * @param ClassMethod $method Method AST node
     * @return bool True if method has validation
     */
    private function methodHasValidation(ClassMethod $method): bool
    {
        // Look for $rules = [...] assignments
        $assignments = $this->nodeFinder->findInstanceOf($method, Node\Expr\Assign::class);
        
        foreach ($assignments as $assignment) {
            if ($assignment->var instanceof Node\Expr\Variable &&
                $assignment->var->name === 'rules' &&
                $assignment->expr instanceof Node\Expr\Array_) {
                return true;
            }
        }

        return false;
    }

    /**
     * Detect repositories used in controller code
     * 
     * @param string $controllerCode Controller PHP code
     * @return array<string> Repository class names
     */
    public function detectRepositories(string $controllerCode): array
    {
        $repositories = [];

        // Look for repository instantiations
        if (preg_match_all('/new\s+([A-Z]\w+Repository)\s*\(/', $controllerCode, $matches)) {
            $repositories = array_merge($repositories, $matches[1]);
        }

        // Look for repository property declarations
        if (preg_match_all('/@var\s+([A-Z]\w+Repository)/', $controllerCode, $matches)) {
            $repositories = array_merge($repositories, $matches[1]);
        }

        // Look for use statements
        if (preg_match_all('/use\s+App\\\\Repositories\\\\([A-Z]\w+Repository);/', $controllerCode, $matches)) {
            $repositories = array_merge($repositories, $matches[1]);
        }

        return array_unique($repositories);
    }

    /**
     * Generate service name from controller name
     * 
     * @param string $controllerName Controller name
     * @return string Service name
     */
    private function generateServiceName(string $controllerName): string
    {
        // Remove "Controller" suffix if present
        $name = preg_replace('/Controller$/', '', $controllerName);
        
        // Add "Service" suffix
        return $name . 'Service';
    }

    /**
     * Add use statements for repositories and other dependencies
     * 
     * @param array<string> $repositories Repository class names
     * @return void
     */
    private function addUseStatements(array $repositories): void
    {
        // Add repository use statements
        foreach ($repositories as $repository) {
            $this->codeGenerator->addUseStatement("App\\Repositories\\{$repository}");
        }

        // Add common use statements
        $this->codeGenerator->addUseStatement('CodeIgniter\\Database\\BaseConnection');
    }

    /**
     * Generate repository properties
     * 
     * @param array<string> $repositories Repository class names
     * @return array<array> Property definitions
     */
    private function generateRepositoryProperties(array $repositories): array
    {
        $properties = [];

        // Add database property
        $properties[] = [
            'name' => 'db',
            'visibility' => 'protected',
            'type' => 'BaseConnection',
            'description' => 'Database connection instance',
        ];

        // Add repository properties
        foreach ($repositories as $repository) {
            $propertyName = $this->repositoryToPropertyName($repository);
            
            $properties[] = [
                'name' => $propertyName,
                'visibility' => 'protected',
                'type' => $repository,
                'description' => "{$repository} instance",
            ];
        }

        return $properties;
    }

    /**
     * Convert repository class name to property name
     * 
     * @param string $repository Repository class name (e.g., "KavlingRepository")
     * @return string Property name (e.g., "kavlingRepo")
     */
    private function repositoryToPropertyName(string $repository): string
    {
        // Remove "Repository" suffix
        $name = preg_replace('/Repository$/', '', $repository);
        
        // Convert to camelCase
        $name = lcfirst($name);
        
        // Add "Repo" suffix
        return $name . 'Repo';
    }

    /**
     * Generate constructor with dependency injection
     * 
     * @param array<string> $repositories Repository class names
     * @return array Constructor definition
     */
    private function generateConstructor(array $repositories): array
    {
        $params = [];
        $body = [];

        // Add database parameter
        $params[] = [
            'type' => 'BaseConnection',
            'name' => 'db',
            'description' => 'Database connection instance',
        ];
        $body[] = '$this->db = $db;';

        // Add repository parameters
        foreach ($repositories as $repository) {
            $propertyName = $this->repositoryToPropertyName($repository);
            
            $params[] = [
                'type' => $repository,
                'name' => $propertyName,
                'description' => "{$repository} instance",
            ];
            
            $body[] = "\$this->{$propertyName} = \${$propertyName};";
        }

        return [
            'description' => 'Constructor with dependency injection',
            'params' => $params,
            'body' => implode("\n", $body),
        ];
    }

    /**
     * Generate service method from business logic
     * 
     * @param array $logic Business logic data
     * @return array Method definition
     */
    public function generateServiceMethod(array $logic): array
    {
        $methodName = $logic['name'];
        $hasTransaction = $logic['hasTransaction'] ?? false;
        $hasValidation = $logic['hasValidation'] ?? false;

        // Extract parameters from original method
        $params = $this->extractMethodParameters($logic['method'] ?? null);

        // Generate method body
        $body = $this->generateServiceMethodBody($logic, $hasTransaction, $hasValidation);

        return [
            'name' => $methodName,
            'visibility' => 'public',
            'params' => $params,
            'return' => 'array',
            'description' => "Business logic for {$methodName}",
            'returnDescription' => 'Result array with success status and data',
            'body' => $body,
        ];
    }

    /**
     * Extract method parameters from AST node
     * 
     * @param ClassMethod|null $method Method AST node
     * @return array<array> Parameter definitions
     */
    private function extractMethodParameters(?ClassMethod $method): array
    {
        if (!$method || !$method->params) {
            return [];
        }

        $params = [];
        foreach ($method->params as $param) {
            $paramDef = [
                'name' => $param->var->name,
            ];

            // Add type if available
            if ($param->type) {
                $paramDef['type'] = $param->type->toString();
            }

            // Add default value if available
            if ($param->default) {
                $paramDef['default'] = 'null'; // Simplified
            }

            $params[] = $paramDef;
        }

        return $params;
    }

    /**
     * Generate service method body
     * 
     * @param array $logic Business logic data
     * @param bool $hasTransaction Whether to add transaction management
     * @param bool $hasValidation Whether to add validation
     * @return string Method body code
     */
    private function generateServiceMethodBody(
        array $logic,
        bool $hasTransaction,
        bool $hasValidation
    ): string {
        $body = [];

        // Add validation if needed
        if ($hasValidation) {
            $body[] = '// TODO: Add validation logic here';
            $body[] = '// Use validation rules extracted from controller';
            $body[] = '';
        }

        // Add transaction management if needed
        if ($hasTransaction) {
            $body[] = 'try {';
            $body[] = '    $this->db->transStart();';
            $body[] = '';
            $body[] = '    // TODO: Add business logic here';
            $body[] = '    // Move database operations from controller';
            $body[] = '';
            $body[] = '    $this->db->transComplete();';
            $body[] = '';
            $body[] = '    if ($this->db->transStatus() === false) {';
            $body[] = '        return $this->generateResultObject(false, \'Transaction failed\');';
            $body[] = '    }';
            $body[] = '';
            $body[] = '    return $this->generateResultObject(true, \'Operation successful\');';
            $body[] = '} catch (\\Throwable $e) {';
            $body[] = '    if ($this->db->transStatus() !== false) {';
            $body[] = '        $this->db->transRollback();';
            $body[] = '    }';
            $body[] = '    return $this->generateResultObject(false, $e->getMessage());';
            $body[] = '}';
        } else {
            $body[] = 'try {';
            $body[] = '    // TODO: Add business logic here';
            $body[] = '    // Move business operations from controller';
            $body[] = '';
            $body[] = '    return $this->generateResultObject(true, \'Operation successful\');';
            $body[] = '} catch (\\Throwable $e) {';
            $body[] = '    return $this->generateResultObject(false, $e->getMessage());';
            $body[] = '}';
        }

        return implode("\n", $body);
    }

    /**
     * Add transaction management to method body
     * 
     * @param string $methodBody Original method body
     * @return string Method body with transaction management
     */
    public function addTransactionManagement(string $methodBody): string
    {
        // Check if already has transaction management
        if (preg_match('/transStart|transComplete/', $methodBody)) {
            return $methodBody;
        }

        $wrapped = [];
        $wrapped[] = 'try {';
        $wrapped[] = '    $this->db->transStart();';
        $wrapped[] = '';
        
        // Indent original body
        $lines = explode("\n", $methodBody);
        foreach ($lines as $line) {
            $wrapped[] = '    ' . $line;
        }
        
        $wrapped[] = '';
        $wrapped[] = '    $this->db->transComplete();';
        $wrapped[] = '';
        $wrapped[] = '    if ($this->db->transStatus() === false) {';
        $wrapped[] = '        return $this->generateResultObject(false, \'Transaction failed\');';
        $wrapped[] = '    }';
        $wrapped[] = '';
        $wrapped[] = '    return $this->generateResultObject(true, \'Operation successful\');';
        $wrapped[] = '} catch (\\Throwable $e) {';
        $wrapped[] = '    if ($this->db->transStatus() !== false) {';
        $wrapped[] = '        $this->db->transRollback();';
        $wrapped[] = '    }';
        $wrapped[] = '    return $this->generateResultObject(false, $e->getMessage());';
        $wrapped[] = '}';

        return implode("\n", $wrapped);
    }

    /**
     * Generate result object structure
     * 
     * Creates a helper method for generating structured service responses.
     * 
     * @return string Result object helper method code
     */
    public function generateResultObject(): string
    {
        $method = [
            'name' => 'generateResultObject',
            'visibility' => 'protected',
            'params' => [
                ['type' => 'bool', 'name' => 'success', 'description' => 'Operation success status'],
                ['type' => 'string', 'name' => 'message', 'description' => 'Result message'],
                ['type' => 'mixed', 'name' => 'data', 'default' => 'null', 'description' => 'Result data'],
            ],
            'return' => 'array',
            'description' => 'Generate structured result object',
            'returnDescription' => 'Structured result array',
            'body' => implode("\n", [
                'return [',
                '    \'success\' => $success,',
                '    \'message\' => $message,',
                '    \'data\' => $data,',
                '];',
            ]),
        ];

        return $this->codeGenerator->generateMethod($method);
    }
}

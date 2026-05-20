# Code Generation Components

This directory contains utilities for generating PHP code with proper formatting, documentation, and validation.

## Components

### CodeGenerator

**Purpose:** Generate PHP code with PSR-12 formatting, namespace management, PHPDoc comments, and syntax validation.

**Key Features:**
- Template-based code generation
- PSR-12 compliant formatting
- Namespace and use statement management
- PHPDoc comment generation
- PHP syntax validation
- Class, method, and property generation

**Usage Example:**

```php
use App\Libraries\Refactor\Generation\CodeGenerator;

$generator = new CodeGenerator();
$generator
    ->setNamespace('App\Services')
    ->addUseStatement('App\Repositories\UserRepository');

$code = $generator->generateClass('UserService', [
    'description' => 'Service for managing user operations',
    'properties' => [
        [
            'name' => 'userRepository',
            'visibility' => 'private',
            'type' => 'UserRepository',
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
            'params' => [
                ['type' => 'int', 'name' => 'id'],
            ],
            'return' => 'array',
            'body' => 'return $this->userRepository->find($id);',
        ],
    ],
]);

// Validate syntax
$result = $generator->validateSyntax($code);
if ($result['valid']) {
    file_put_contents('UserService.php', $code);
}
```

**API Methods:**

- `setNamespace(string $namespace): self` - Set namespace for generated code
- `addUseStatement(string $class, ?string $alias = null): self` - Add use statement
- `addUseStatements(array $classes): self` - Add multiple use statements
- `generateClass(string $className, array $options = []): string` - Generate complete class
- `generateMethod(array $methodData): string` - Generate method code
- `generateProperty(array $propertyData): string` - Generate property code
- `generateConstructor(array $constructorData): string` - Generate constructor
- `formatCode(string $code): string` - Format code according to PSR-12
- `validateSyntax(string $code): array` - Validate PHP syntax
- `reset(): self` - Reset generator state
- `setIndentSize(int $size): self` - Set indentation size

**Class Options:**

```php
[
    'description' => 'Class description',
    'extends' => 'BaseClass',
    'implements' => ['Interface1', 'Interface2'],
    'properties' => [
        [
            'name' => 'propertyName',
            'visibility' => 'private|protected|public',
            'type' => 'string',
            'static' => false,
            'default' => 'defaultValue',
            'description' => 'Property description',
        ],
    ],
    'constructor' => [
        'params' => [
            [
                'type' => 'string',
                'name' => 'paramName',
                'default' => 'defaultValue',
                'description' => 'Parameter description',
            ],
        ],
        'body' => 'Constructor body code',
    ],
    'methods' => [
        [
            'name' => 'methodName',
            'visibility' => 'private|protected|public',
            'static' => false,
            'description' => 'Method description',
            'params' => [
                [
                    'type' => 'string',
                    'name' => 'paramName',
                    'default' => 'defaultValue',
                    'description' => 'Parameter description',
                ],
            ],
            'return' => 'returnType',
            'returnDescription' => 'Return value description',
            'body' => 'Method body code',
        ],
    ],
]
```

## Testing

Run tests for this component:

```bash
vendor/bin/phpunit tests/unit/Refactor/Generation/CodeGeneratorTest.php
```

## Standards

All generated code follows:
- PSR-12 coding standards
- PSR-4 autoloading conventions
- CodeIgniter 4 naming conventions
- PHP 7.4+ type hints and syntax

## Integration

This component is used by:
- RepositoryGenerator - Generate repository classes
- ServiceGenerator - Generate service classes
- ControllerRefactorer - Generate refactored controllers
- SecurityFixer - Generate security-fixed code

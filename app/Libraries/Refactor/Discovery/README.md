# Discovery Utilities

This directory contains utilities for discovering and parsing PHP code in the CodeIgniter 4 application.

## Components

### FileScanner

The `FileScanner` class recursively scans directories for PHP files with filtering capabilities.

**Features:**
- Recursive directory scanning
- File type filtering (controllers, models, services, repositories)
- Exclude directory patterns
- Multiple directory scanning
- File counting and existence checking

**Usage:**

```php
use App\Libraries\Refactor\Discovery\FileScanner;

$scanner = new FileScanner();

// Scan all PHP files in a directory
$files = $scanner->scan(APPPATH . 'Controllers');

// Scan with filters
$scanner->setFilters([FileScanner::FILTER_CONTROLLERS]);
$controllers = $scanner->scan(APPPATH);

// Scan multiple directories
$files = $scanner->scanMultiple([
    APPPATH . 'Controllers',
    APPPATH . 'Models',
]);

// Count files
$count = $scanner->count(APPPATH . 'Controllers');

// Check if directory has PHP files
$hasFiles = $scanner->hasPhpFiles(APPPATH . 'Services');
```

**Available Filters:**
- `FileScanner::FILTER_CONTROLLERS` - Only controller files
- `FileScanner::FILTER_MODELS` - Only model files
- `FileScanner::FILTER_SERVICES` - Only service files
- `FileScanner::FILTER_REPOSITORIES` - Only repository files
- `FileScanner::FILTER_ALL` - All PHP files (default)

**Default Excluded Directories:**
- vendor
- tests
- writable
- public
- .git
- .idea
- node_modules

### CodeParser

The `CodeParser` class uses nikic/php-parser to parse PHP code and extract AST (Abstract Syntax Tree) information.

**Features:**
- Parse PHP code or files into AST
- Extract class information (namespace, extends, implements, methods, properties)
- Extract use statements (imports)
- Extract method calls (instance and static)
- Extract model instantiations
- Extract constructor dependencies
- Find raw SQL queries
- Error handling with metadata

**Usage:**

```php
use App\Libraries\Refactor\Discovery\CodeParser;

$parser = new CodeParser();

// Parse PHP code
$ast = $parser->parse('<?php class MyClass {}');

// Parse PHP file
$ast = $parser->parse('/path/to/file.php');

// Extract class information
$info = $parser->parseClassInfo('/path/to/Controller.php');
// Returns: [
//     'namespace' => 'App\Controllers',
//     'className' => 'UserController',
//     'extends' => 'BaseController',
//     'implements' => ['SomeInterface'],
//     'methods' => ['index', 'show', 'create'],
//     'properties' => ['userModel', 'authService'],
//     'uses' => ['App\Models\UserModel', 'App\Services\AuthService']
// ]

// Extract method calls
$calls = $parser->extractMethodCalls($filePath);
// Returns: [
//     ['class' => 'userModel', 'method' => 'find', 'line' => 45],
//     ['class' => 'SomeClass', 'method' => 'staticMethod', 'line' => 67]
// ]

// Extract model instantiations
$models = $parser->extractModelInstantiations($filePath);
// Returns: [
//     ['class' => 'UserModel', 'line' => 23],
//     ['class' => 'ProductModel', 'line' => 45]
// ]

// Extract use statements
$uses = $parser->extractUseStatements($filePath);
// Returns: ['App\Models\UserModel', 'App\Services\AuthService']

// Find raw SQL queries
$queries = $parser->findRawSqlQueries($filePath);
// Returns: [
//     ['query' => 'SELECT * FROM users WHERE id = $id', 'line' => 78]
// ]

// Extract constructor dependencies
$deps = $parser->extractConstructorDependencies($filePath);
// Returns: [
//     ['type' => 'UserModel', 'name' => 'userModel'],
//     ['type' => 'AuthService', 'name' => 'authService']
// ]

// Check parse success
if ($parser->wasLastParseSuccessful()) {
    $metadata = $parser->getLastParseMetadata();
}
```

## Integration Example

Here's how to use both utilities together:

```php
use App\Libraries\Refactor\Discovery\FileScanner;
use App\Libraries\Refactor\Discovery\CodeParser;

$scanner = new FileScanner();
$parser = new CodeParser();

// Step 1: Scan for controller files
$scanner->setFilters([FileScanner::FILTER_CONTROLLERS]);
$controllers = $scanner->scan(APPPATH . 'Controllers');

// Step 2: Parse each controller
foreach ($controllers as $controllerPath) {
    // Get class information
    $classInfo = $parser->parseClassInfo($controllerPath);
    
    echo "Controller: {$classInfo['className']}\n";
    echo "Namespace: {$classInfo['namespace']}\n";
    echo "Methods: " . implode(', ', $classInfo['methods']) . "\n";
    
    // Find dependencies
    $uses = $parser->extractUseStatements($controllerPath);
    echo "Dependencies: " . implode(', ', $uses) . "\n";
    
    // Find raw SQL queries (potential security issues)
    $queries = $parser->findRawSqlQueries($controllerPath);
    if (!empty($queries)) {
        echo "Warning: Found " . count($queries) . " raw SQL queries\n";
    }
    
    echo "\n";
}
```

## Requirements

- PHP 8.1 or higher
- nikic/php-parser ^5.7
- CodeIgniter 4

## Testing

Unit tests are located in `tests/unit/Refactor/Discovery/`:
- `FileScannerTest.php` - Tests for FileScanner
- `CodeParserTest.php` - Tests for CodeParser
- `IntegrationTest.php` - Integration tests

Run tests:
```bash
vendor/bin/phpunit tests/unit/Refactor/Discovery/
```

## Implementation Status

✅ **Task 2.1: Create FileScanner and CodeParser utilities**
- ✅ FileScanner implemented with recursive scanning
- ✅ CodeParser implemented with AST parsing
- ✅ File filtering logic (controllers, models, services, repositories)
- ✅ Comprehensive unit tests (40 tests, 613 assertions)
- ✅ Integration tests (7 tests, 24 assertions)

**Requirements Validated:**
- ✅ Requirement 1.1: Module Discovery System scans directories
- ✅ Requirement 1.2: Module Discovery System identifies relationships

## Next Steps

The next task (2.2) will implement the `ModuleDiscovery` class that uses these utilities to:
- Scan controllers and identify all controller files
- Scan models and identify all model files
- Scan services to detect existing service classes
- Scan repositories to detect existing repository classes
- Identify relationships between controllers and their models

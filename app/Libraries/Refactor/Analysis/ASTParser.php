<?php

namespace App\Libraries\Refactor\Analysis;

use App\Libraries\Refactor\Discovery\CodeParser;

/**
 * ASTParser
 * 
 * Wrapper class for PHP-Parser library specifically focused on dependency extraction.
 * Provides methods to extract use statements, class instantiations, and method calls
 * needed for building dependency graphs.
 * 
 * This class wraps CodeParser and provides a focused interface for dependency analysis,
 * separating concerns between general code parsing and dependency extraction.
 * 
 * @package App\Libraries\Refactor\Analysis
 */
class ASTParser
{
    /**
     * @var CodeParser Underlying code parser instance
     */
    private CodeParser $codeParser;

    /**
     * Constructor
     * 
     * @param CodeParser|null $codeParser Optional CodeParser instance for dependency injection
     */
    public function __construct(?CodeParser $codeParser = null)
    {
        $this->codeParser = $codeParser ?? new CodeParser();
    }

    /**
     * Extract use statements (imports) from a file
     * 
     * Use statements indicate dependencies on other classes/namespaces.
     * This is crucial for understanding module dependencies.
     * 
     * @param string $filePath Path to PHP file
     * @return array<string> Array of fully qualified class names
     * 
     * @example
     * ```php
     * $parser = new ASTParser();
     * $uses = $parser->extractUseStatements('app/Controllers/UserController.php');
     * // Returns: ['App\Models\UserModel', 'App\Services\AuthService', ...]
     * ```
     */
    public function extractUseStatements(string $filePath): array
    {
        return $this->codeParser->extractUseStatements($filePath);
    }

    /**
     * Extract class instantiations from a file
     * 
     * Identifies where classes are instantiated using 'new ClassName()'.
     * This helps identify runtime dependencies between modules.
     * 
     * @param string $filePath Path to PHP file
     * @return array<array{class: string, line: int}> Array of instantiation info
     * 
     * @example
     * ```php
     * $parser = new ASTParser();
     * $instantiations = $parser->extractClassInstantiations('app/Controllers/UserController.php');
     * // Returns: [
     * //   ['class' => 'UserModel', 'line' => 45],
     * //   ['class' => 'EmailService', 'line' => 67],
     * // ]
     * ```
     */
    public function extractClassInstantiations(string $filePath): array
    {
        return $this->codeParser->extractModelInstantiations($filePath);
    }

    /**
     * Extract method calls to other classes from a file
     * 
     * Identifies both instance method calls ($object->method()) and 
     * static method calls (Class::method()). This reveals runtime
     * dependencies and interactions between modules.
     * 
     * @param string $filePath Path to PHP file
     * @return array<array{class: string|null, method: string, line: int}> Array of method call info
     * 
     * @example
     * ```php
     * $parser = new ASTParser();
     * $calls = $parser->extractMethodCalls('app/Controllers/UserController.php');
     * // Returns: [
     * //   ['class' => 'userModel', 'method' => 'find', 'line' => 50],
     * //   ['class' => 'DB', 'method' => 'table', 'line' => 55],
     * // ]
     * ```
     */
    public function extractMethodCalls(string $filePath): array
    {
        return $this->codeParser->extractMethodCalls($filePath);
    }

    /**
     * Extract all dependencies from a file
     * 
     * Convenience method that extracts use statements, class instantiations,
     * and method calls in a single operation. Returns a comprehensive
     * dependency analysis for the file.
     * 
     * @param string $filePath Path to PHP file
     * @return array{
     *     uses: array<string>,
     *     instantiations: array<array{class: string, line: int}>,
     *     methodCalls: array<array{class: string|null, method: string, line: int}>
     * }
     * 
     * @example
     * ```php
     * $parser = new ASTParser();
     * $deps = $parser->extractAllDependencies('app/Controllers/UserController.php');
     * // Returns: [
     * //   'uses' => ['App\Models\UserModel', ...],
     * //   'instantiations' => [['class' => 'UserModel', 'line' => 45], ...],
     * //   'methodCalls' => [['class' => 'userModel', 'method' => 'find', 'line' => 50], ...]
     * // ]
     * ```
     */
    public function extractAllDependencies(string $filePath): array
    {
        return [
            'uses' => $this->extractUseStatements($filePath),
            'instantiations' => $this->extractClassInstantiations($filePath),
            'methodCalls' => $this->extractMethodCalls($filePath),
        ];
    }

    /**
     * Extract constructor dependencies from a file
     * 
     * Identifies dependencies injected through the constructor.
     * This is important for understanding dependency injection patterns
     * and compile-time dependencies.
     * 
     * @param string $filePath Path to PHP file
     * @return array<array{type: string|null, name: string}> Array of constructor parameters
     * 
     * @example
     * ```php
     * $parser = new ASTParser();
     * $deps = $parser->extractConstructorDependencies('app/Services/UserService.php');
     * // Returns: [
     * //   ['type' => 'UserRepository', 'name' => 'userRepository'],
     * //   ['type' => 'EmailService', 'name' => 'emailService'],
     * // ]
     * ```
     */
    public function extractConstructorDependencies(string $filePath): array
    {
        return $this->codeParser->extractConstructorDependencies($filePath);
    }

    /**
     * Parse class information from a file
     * 
     * Extracts comprehensive class information including namespace, class name,
     * inheritance, interfaces, methods, properties, and use statements.
     * Useful for understanding the structure and relationships of a class.
     * 
     * @param string $filePath Path to PHP file
     * @return array{
     *     namespace: string|null,
     *     className: string|null,
     *     extends: string|null,
     *     implements: array<string>,
     *     methods: array<string>,
     *     properties: array<string>,
     *     uses: array<string>
     * }|null Returns null if parsing fails
     * 
     * @example
     * ```php
     * $parser = new ASTParser();
     * $info = $parser->parseClassInfo('app/Controllers/UserController.php');
     * // Returns: [
     * //   'namespace' => 'App\Controllers',
     * //   'className' => 'UserController',
     * //   'extends' => 'BaseController',
     * //   'implements' => [],
     * //   'methods' => ['index', 'create', 'update', 'delete'],
     * //   'properties' => ['userService'],
     * //   'uses' => ['App\Services\UserService', ...]
     * // ]
     * ```
     */
    public function parseClassInfo(string $filePath): ?array
    {
        return $this->codeParser->parseClassInfo($filePath);
    }

    /**
     * Check if the last parse operation was successful
     * 
     * @return bool True if last parse succeeded, false otherwise
     */
    public function wasLastParseSuccessful(): bool
    {
        return $this->codeParser->wasLastParseSuccessful();
    }

    /**
     * Get metadata from the last parse operation
     * 
     * Provides detailed information about the last parse operation,
     * including success status, error messages, and node count.
     * 
     * @return array<string, mixed> Metadata array with keys: success, error, nodeCount/line
     */
    public function getLastParseMetadata(): array
    {
        return $this->codeParser->getLastParseMetadata();
    }

    /**
     * Get the underlying CodeParser instance
     * 
     * Provides access to the wrapped CodeParser for advanced use cases
     * that require direct access to parsing functionality.
     * 
     * @return CodeParser
     */
    public function getCodeParser(): CodeParser
    {
        return $this->codeParser;
    }
}

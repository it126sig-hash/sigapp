<?php

namespace App\Libraries\Refactor\Discovery;

use App\Libraries\Refactor\Contracts\ParserInterface;
use PhpParser\Error;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;

/**
 * CodeParser
 * 
 * Parses PHP code using nikic/php-parser to extract AST (Abstract Syntax Tree).
 * Provides methods to analyze code structure, dependencies, and patterns.
 * 
 * @package App\Libraries\Refactor\Discovery
 */
class CodeParser implements ParserInterface
{
    /**
     * @var \PhpParser\Parser PHP Parser instance
     */
    private $parser;

    /**
     * @var array<string, mixed> Last parse result metadata
     */
    private array $lastParseMetadata = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->parser = (new ParserFactory())->createForNewestSupportedVersion();
    }

    /**
     * Parse PHP code or file
     * 
     * @param string $content File path or PHP code string
     * @return array<Node>|null Array of AST nodes or null on error
     */
    public function parse(string $content): ?array
    {
        $code = $this->getCodeContent($content);

        if ($code === null) {
            return null;
        }

        try {
            $ast = $this->parser->parse($code);
            $this->lastParseMetadata = [
                'success' => true,
                'error' => null,
                'nodeCount' => count($ast ?? []),
            ];
            return $ast;
        } catch (Error $e) {
            $this->lastParseMetadata = [
                'success' => false,
                'error' => $e->getMessage(),
                'line' => $e->getStartLine(),
            ];
            return null;
        }
    }

    /**
     * Parse file and extract class information
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
     * }|null
     */
    public function parseClassInfo(string $filePath): ?array
    {
        $ast = $this->parse($filePath);

        if ($ast === null) {
            return null;
        }

        $info = [
            'namespace' => null,
            'className' => null,
            'extends' => null,
            'implements' => [],
            'methods' => [],
            'properties' => [],
            'uses' => [],
        ];

        $traverser = new NodeTraverser();
        $visitor = new class($info) extends NodeVisitorAbstract {
            private array $info;

            public function __construct(array &$info)
            {
                $this->info = &$info;
            }

            public function enterNode(Node $node)
            {
                // Extract namespace
                if ($node instanceof Node\Stmt\Namespace_) {
                    $this->info['namespace'] = $node->name ? $node->name->toString() : null;
                }

                // Extract use statements
                if ($node instanceof Node\Stmt\Use_) {
                    foreach ($node->uses as $use) {
                        $this->info['uses'][] = $use->name->toString();
                    }
                }

                // Extract class information
                if ($node instanceof Node\Stmt\Class_) {
                    $this->info['className'] = $node->name ? $node->name->toString() : null;
                    
                    // Extract extends
                    if ($node->extends) {
                        $this->info['extends'] = $node->extends->toString();
                    }

                    // Extract implements
                    if ($node->implements) {
                        foreach ($node->implements as $implement) {
                            $this->info['implements'][] = $implement->toString();
                        }
                    }

                    // Extract methods
                    foreach ($node->getMethods() as $method) {
                        $this->info['methods'][] = $method->name->toString();
                    }

                    // Extract properties
                    foreach ($node->getProperties() as $property) {
                        foreach ($property->props as $prop) {
                            $this->info['properties'][] = $prop->name->toString();
                        }
                    }
                }

                return null;
            }
        };

        $traverser->addVisitor($visitor);
        $traverser->traverse($ast);

        return $info;
    }

    /**
     * Extract method calls from code
     * 
     * @param string $content File path or PHP code
     * @return array<array{class: string|null, method: string, line: int}>
     */
    public function extractMethodCalls(string $content): array
    {
        $ast = $this->parse($content);

        if ($ast === null) {
            return [];
        }

        $methodCalls = [];

        $traverser = new NodeTraverser();
        $visitor = new class($methodCalls) extends NodeVisitorAbstract {
            private array $methodCalls;

            public function __construct(array &$methodCalls)
            {
                $this->methodCalls = &$methodCalls;
            }

            public function enterNode(Node $node)
            {
                // Method calls: $object->method()
                if ($node instanceof Node\Expr\MethodCall) {
                    $className = null;
                    if ($node->var instanceof Node\Expr\Variable) {
                        $className = $node->var->name;
                    }

                    if ($node->name instanceof Node\Identifier) {
                        $this->methodCalls[] = [
                            'class' => $className,
                            'method' => $node->name->toString(),
                            'line' => $node->getStartLine(),
                        ];
                    }
                }

                // Static method calls: Class::method()
                if ($node instanceof Node\Expr\StaticCall) {
                    $className = null;
                    if ($node->class instanceof Node\Name) {
                        $className = $node->class->toString();
                    }

                    if ($node->name instanceof Node\Identifier) {
                        $this->methodCalls[] = [
                            'class' => $className,
                            'method' => $node->name->toString(),
                            'line' => $node->getStartLine(),
                        ];
                    }
                }

                return null;
            }
        };

        $traverser->addVisitor($visitor);
        $traverser->traverse($ast);

        return $methodCalls;
    }

    /**
     * Extract model instantiations from code
     * 
     * @param string $content File path or PHP code
     * @return array<array{class: string, line: int}>
     */
    public function extractModelInstantiations(string $content): array
    {
        $ast = $this->parse($content);

        if ($ast === null) {
            return [];
        }

        $instantiations = [];

        $traverser = new NodeTraverser();
        $visitor = new class($instantiations) extends NodeVisitorAbstract {
            private array $instantiations;

            public function __construct(array &$instantiations)
            {
                $this->instantiations = &$instantiations;
            }

            public function enterNode(Node $node)
            {
                // new ClassName()
                if ($node instanceof Node\Expr\New_) {
                    if ($node->class instanceof Node\Name) {
                        $className = $node->class->toString();
                        
                        // Filter for Model classes
                        if (str_ends_with($className, 'Model')) {
                            $this->instantiations[] = [
                                'class' => $className,
                                'line' => $node->getStartLine(),
                            ];
                        }
                    }
                }

                return null;
            }
        };

        $traverser->addVisitor($visitor);
        $traverser->traverse($ast);

        return $instantiations;
    }

    /**
     * Extract use statements (imports) from code
     * 
     * @param string $content File path or PHP code
     * @return array<string> Array of fully qualified class names
     */
    public function extractUseStatements(string $content): array
    {
        $ast = $this->parse($content);

        if ($ast === null) {
            return [];
        }

        $uses = [];

        $traverser = new NodeTraverser();
        $visitor = new class($uses) extends NodeVisitorAbstract {
            private array $uses;

            public function __construct(array &$uses)
            {
                $this->uses = &$uses;
            }

            public function enterNode(Node $node)
            {
                if ($node instanceof Node\Stmt\Use_) {
                    foreach ($node->uses as $use) {
                        $this->uses[] = $use->name->toString();
                    }
                }

                return null;
            }
        };

        $traverser->addVisitor($visitor);
        $traverser->traverse($ast);

        return $uses;
    }

    /**
     * Check if code contains raw SQL queries
     * 
     * @param string $content File path or PHP code
     * @return array<array{query: string, line: int}>
     */
    public function findRawSqlQueries(string $content): array
    {
        $ast = $this->parse($content);

        if ($ast === null) {
            return [];
        }

        $queries = [];

        $traverser = new NodeTraverser();
        $visitor = new class($queries) extends NodeVisitorAbstract {
            private array $queries;

            public function __construct(array &$queries)
            {
                $this->queries = &$queries;
            }

            public function enterNode(Node $node)
            {
                // Look for string literals containing SQL keywords
                if ($node instanceof Node\Scalar\String_) {
                    $value = $node->value;
                    $upperValue = strtoupper($value);

                    // Check for SQL keywords
                    $sqlKeywords = ['SELECT', 'INSERT', 'UPDATE', 'DELETE', 'FROM', 'WHERE'];
                    foreach ($sqlKeywords as $keyword) {
                        if (str_contains($upperValue, $keyword)) {
                            $this->queries[] = [
                                'query' => $value,
                                'line' => $node->getStartLine(),
                            ];
                            break;
                        }
                    }
                }

                return null;
            }
        };

        $traverser->addVisitor($visitor);
        $traverser->traverse($ast);

        return $queries;
    }

    /**
     * Get metadata from last parse operation
     * 
     * @return array<string, mixed>
     */
    public function getLastParseMetadata(): array
    {
        return $this->lastParseMetadata;
    }

    /**
     * Check if last parse was successful
     * 
     * @return bool
     */
    public function wasLastParseSuccessful(): bool
    {
        return $this->lastParseMetadata['success'] ?? false;
    }

    /**
     * Get code content from file path or string
     * 
     * @param string $content File path or PHP code
     * @return string|null
     */
    private function getCodeContent(string $content): ?string
    {
        // Check if it's a file path that exists
        if (file_exists($content) && is_file($content)) {
            $code = file_get_contents($content);
            return $code !== false ? $code : null;
        }

        // If it looks like a file path but doesn't exist, return null
        if (str_contains($content, '/') || str_contains($content, '\\')) {
            if (strlen($content) < 1000 && !str_starts_with($content, '<?php')) {
                // Likely a file path that doesn't exist
                return null;
            }
        }

        // Assume it's PHP code string
        return $content;
    }

    /**
     * Extract constructor dependencies
     * 
     * @param string $content File path or PHP code
     * @return array<array{type: string|null, name: string}>
     */
    public function extractConstructorDependencies(string $content): array
    {
        $ast = $this->parse($content);

        if ($ast === null) {
            return [];
        }

        $dependencies = [];

        $traverser = new NodeTraverser();
        $visitor = new class($dependencies) extends NodeVisitorAbstract {
            private array $dependencies;

            public function __construct(array &$dependencies)
            {
                $this->dependencies = &$dependencies;
            }

            public function enterNode(Node $node)
            {
                // Find constructor method
                if ($node instanceof Node\Stmt\ClassMethod && $node->name->toString() === '__construct') {
                    foreach ($node->params as $param) {
                        $type = null;
                        if ($param->type instanceof Node\Name) {
                            $type = $param->type->toString();
                        } elseif ($param->type instanceof Node\Identifier) {
                            $type = $param->type->toString();
                        }

                        $this->dependencies[] = [
                            'type' => $type,
                            'name' => $param->var->name ?? 'unknown',
                        ];
                    }
                }

                return null;
            }
        };

        $traverser->addVisitor($visitor);
        $traverser->traverse($ast);

        return $dependencies;
    }
}

<?php

namespace App\Libraries\Refactor\Execution;

use App\Libraries\Refactor\Discovery\CodeParser;
use App\Libraries\Refactor\Generation\CodeGenerator;
use App\Libraries\Refactor\Models\SplitResult;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

/**
 * ControllerSplitter
 * 
 * Splits mixed controllers into separate Web and API controllers.
 * Analyzes controller methods to identify web methods (view rendering) and API methods (JSON responses).
 * Generates two separate controllers with appropriate base classes and response formats.
 * 
 * @package App\Libraries\Refactor\Execution
 */
class ControllerSplitter
{
    /**
     * @var CodeParser Parser for analyzing PHP code
     */
    private CodeParser $parser;

    /**
     * @var CodeGenerator Generator for creating PHP code
     */
    private CodeGenerator $generator;

    /**
     * Constructor
     * 
     * @param CodeParser|null $parser Optional CodeParser instance
     * @param CodeGenerator|null $generator Optional CodeGenerator instance
     */
    public function __construct(?CodeParser $parser = null, ?CodeGenerator $generator = null)
    {
        $this->parser = $parser ?? new CodeParser();
        $this->generator = $generator ?? new CodeGenerator();
    }

    /**
     * Split controller into Web and API controllers
     * 
     * @param string $controllerPath Path to controller file
     * @return SplitResult Result containing generated controllers
     */
    public function split(string $controllerPath): SplitResult
    {
        // Parse the controller file
        $ast = $this->parser->parse($controllerPath);
        
        if ($ast === null) {
            return new SplitResult([
                'wasSplit' => false,
            ]);
        }

        // Extract controller information
        $classInfo = $this->parser->parseClassInfo($controllerPath);
        
        if ($classInfo === null || $classInfo['className'] === null) {
            return new SplitResult([
                'wasSplit' => false,
            ]);
        }

        // Extract methods with their code
        $methods = $this->extractMethodsWithCode($controllerPath, $ast);

        // Identify web and API methods
        $webMethods = $this->identifyWebMethods($methods);
        $apiMethods = $this->identifyApiMethods($methods);

        // Create result
        $result = new SplitResult([
            'originalClassName' => $classInfo['className'],
            'originalNamespace' => $classInfo['namespace'],
            'useStatements' => $classInfo['uses'],
            'webMethods' => array_keys($webMethods),
            'apiMethods' => array_keys($apiMethods),
            'wasSplit' => !empty($webMethods) && !empty($apiMethods),
        ]);

        // Generate controllers
        if (!empty($webMethods)) {
            $result->webControllerCode = $this->generateWebController(
                $classInfo['className'],
                $classInfo['namespace'],
                $classInfo['uses'],
                $webMethods,
                $classInfo['extends']
            );
        }

        if (!empty($apiMethods)) {
            $result->apiControllerCode = $this->generateApiController(
                $classInfo['className'],
                $classInfo['namespace'],
                $classInfo['uses'],
                $apiMethods
            );
        }

        return $result;
    }

    /**
     * Identify web methods based on view rendering
     * 
     * @param array<string, array<string, mixed>> $methods Methods with their code
     * @return array<string, array<string, mixed>> Web methods
     */
    public function identifyWebMethods(array $methods): array
    {
        $webMethods = [];

        foreach ($methods as $methodName => $methodData) {
            $code = $methodData['code'] ?? '';

            // Check for view rendering patterns
            if ($this->isWebMethod($code)) {
                $webMethods[$methodName] = $methodData;
            }
        }

        return $webMethods;
    }

    /**
     * Identify API methods based on JSON responses
     * 
     * @param array<string, array<string, mixed>> $methods Methods with their code
     * @return array<string, array<string, mixed>> API methods
     */
    public function identifyApiMethods(array $methods): array
    {
        $apiMethods = [];

        foreach ($methods as $methodName => $methodData) {
            $code = $methodData['code'] ?? '';

            // Check for JSON response patterns
            if ($this->isApiMethod($code)) {
                $apiMethods[$methodName] = $methodData;
            }
        }

        return $apiMethods;
    }

    /**
     * Generate web controller for HTML responses
     * 
     * @param string $originalClassName Original controller class name
     * @param string|null $namespace Controller namespace
     * @param array<string> $uses Use statements
     * @param array<string, array<string, mixed>> $methods Web methods
     * @param string|null $extends Parent class to extend
     * @return string Generated web controller code
     */
    public function generateWebController(
        string $originalClassName,
        ?string $namespace,
        array $uses,
        array $methods,
        ?string $extends = null
    ): string {
        $this->generator->reset();

        // Set namespace
        if ($namespace) {
            $this->generator->setNamespace($namespace);
        }

        // Add use statements (filter out API-specific ones)
        $filteredUses = $this->filterUsesForWeb($uses);
        $this->generator->addUseStatements($filteredUses);

        // Prepare methods for generation
        $generatedMethods = [];
        foreach ($methods as $methodName => $methodData) {
            $generatedMethods[] = [
                'name' => $methodName,
                'visibility' => $methodData['visibility'] ?? 'public',
                'params' => $methodData['params'] ?? [],
                'return' => $methodData['return'] ?? null,
                'body' => $methodData['code'] ?? '',
                'description' => $methodData['description'] ?? "Handle {$methodName} request",
            ];
        }

        // Generate class
        $className = $originalClassName;
        $extendsClass = $extends ?? 'BaseController';

        return $this->generator->generateClass($className, [
            'extends' => $extendsClass,
            'description' => "Web controller for {$originalClassName}. Handles HTML view rendering.",
            'methods' => $generatedMethods,
        ]);
    }

    /**
     * Generate API controller extending BaseApiController
     * 
     * @param string $originalClassName Original controller class name
     * @param string|null $namespace Controller namespace
     * @param array<string> $uses Use statements
     * @param array<string, array<string, mixed>> $methods API methods
     * @return string Generated API controller code
     */
    public function generateApiController(
        string $originalClassName,
        ?string $namespace,
        array $uses,
        array $methods
    ): string {
        $this->generator->reset();

        // Set namespace for API controllers
        $apiNamespace = $namespace ? $namespace . '\\Api' : 'App\\Controllers\\Api';
        $this->generator->setNamespace($apiNamespace);

        // Add BaseApiController use statement
        $this->generator->addUseStatement('App\\Controllers\\Api\\BaseApiController');

        // Add use statements (filter out web-specific ones)
        $filteredUses = $this->filterUsesForApi($uses);
        $this->generator->addUseStatements($filteredUses);

        // Prepare methods for generation
        $generatedMethods = [];
        foreach ($methods as $methodName => $methodData) {
            $generatedMethods[] = [
                'name' => $methodName,
                'visibility' => $methodData['visibility'] ?? 'public',
                'params' => $methodData['params'] ?? [],
                'return' => $methodData['return'] ?? null,
                'body' => $methodData['code'] ?? '',
                'description' => $methodData['description'] ?? "Handle {$methodName} API request",
            ];
        }

        // Generate class
        $className = $originalClassName . 'Controller';

        return $this->generator->generateClass($className, [
            'extends' => 'BaseApiController',
            'description' => "API controller for {$originalClassName}. Handles JSON responses.",
            'methods' => $generatedMethods,
        ]);
    }

    /**
     * Check if method is a web method (renders views)
     * 
     * @param string $code Method code
     * @return bool
     */
    private function isWebMethod(string $code): bool
    {
        // Patterns that indicate view rendering
        $viewPatterns = [
            '/\breturn\s+view\s*\(/i',                    // return view(...)
            '/\becho\s+view\s*\(/i',                      // echo view(...)
            '/\$this->response->setBody\s*\(/i',          // setBody for HTML
            '/\breturn\s+\$this->response\s*;/i',         // return response (might be HTML)
        ];

        foreach ($viewPatterns as $pattern) {
            if (preg_match($pattern, $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if method is an API method (returns JSON)
     * 
     * @param string $code Method code
     * @return bool
     */
    private function isApiMethod(string $code): bool
    {
        // Patterns that indicate JSON responses
        $jsonPatterns = [
            '/\breturn\s+\$this->response->setJSON\s*\(/i',     // return $this->response->setJSON(...)
            '/\breturn\s+\$this->respond\s*\(/i',               // return $this->respond(...) (ResourceController)
            '/\breturn\s+\$this->respondCreated\s*\(/i',        // return $this->respondCreated(...)
            '/\breturn\s+\$this->fail\s*\(/i',                  // return $this->fail(...)
            '/\breturn\s+\$this->failNotFound\s*\(/i',          // return $this->failNotFound(...)
            '/\breturn\s+\$this->failValidationError\s*\(/i',   // return $this->failValidationError(...)
            '/\breturn\s+\$this->success\s*\(/i',               // return $this->success(...) (custom)
            '/\breturn\s+\$this->error\s*\(/i',                 // return $this->error(...) (custom)
            '/\bresponse\(\)->setJSON\s*\(/i',                  // response()->setJSON(...)
            '/\bjson_encode\s*\(/i',                            // json_encode(...)
        ];

        foreach ($jsonPatterns as $pattern) {
            if (preg_match($pattern, $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Extract methods with their code from AST
     * 
     * @param string $filePath Path to controller file
     * @param array<Node> $ast Abstract Syntax Tree
     * @return array<string, array<string, mixed>> Methods with metadata
     */
    private function extractMethodsWithCode(string $filePath, array $ast): array
    {
        $methods = [];
        $fileContent = file_get_contents($filePath);

        if ($fileContent === false) {
            return [];
        }

        $lines = explode("\n", $fileContent);

        $traverser = new NodeTraverser();
        $visitor = new class($methods, $lines) extends NodeVisitorAbstract {
            private array $methods;
            private array $lines;

            public function __construct(array &$methods, array $lines)
            {
                $this->methods = &$methods;
                $this->lines = $lines;
            }

            public function enterNode(Node $node)
            {
                if ($node instanceof Node\Stmt\ClassMethod) {
                    $methodName = $node->name->toString();
                    
                    // Skip magic methods except __construct
                    if (str_starts_with($methodName, '__') && $methodName !== '__construct') {
                        return null;
                    }

                    // Extract method code
                    $startLine = $node->getStartLine() - 1;
                    $endLine = $node->getEndLine() - 1;
                    $methodCode = implode("\n", array_slice($this->lines, $startLine, $endLine - $startLine + 1));

                    // Extract method body only (without signature)
                    $bodyStartLine = $node->stmts[0]->getStartLine() ?? $startLine + 1;
                    $bodyEndLine = $endLine;
                    $bodyCode = implode("\n", array_slice($this->lines, $bodyStartLine - 1, $bodyEndLine - $bodyStartLine + 2));
                    
                    // Clean up body code (remove leading/trailing braces and whitespace)
                    $bodyCode = trim($bodyCode);
                    if (str_starts_with($bodyCode, '{')) {
                        $bodyCode = substr($bodyCode, 1);
                    }
                    if (str_ends_with($bodyCode, '}')) {
                        $bodyCode = substr($bodyCode, 0, -1);
                    }
                    $bodyCode = trim($bodyCode);

                    // Extract parameters
                    $params = [];
                    foreach ($node->params as $param) {
                        $type = null;
                        if ($param->type instanceof Node\Name) {
                            $type = $param->type->toString();
                        } elseif ($param->type instanceof Node\Identifier) {
                            $type = $param->type->toString();
                        } elseif ($param->type instanceof Node\UnionType) {
                            $types = [];
                            foreach ($param->type->types as $t) {
                                if ($t instanceof Node\Name || $t instanceof Node\Identifier) {
                                    $types[] = $t->toString();
                                }
                            }
                            $type = implode('|', $types);
                        }

                        $params[] = [
                            'name' => $param->var->name ?? 'unknown',
                            'type' => $type,
                        ];
                    }

                    // Extract return type
                    $returnType = null;
                    if ($node->returnType instanceof Node\Name) {
                        $returnType = $node->returnType->toString();
                    } elseif ($node->returnType instanceof Node\Identifier) {
                        $returnType = $node->returnType->toString();
                    }

                    // Determine visibility
                    $visibility = 'public';
                    if ($node->isPrivate()) {
                        $visibility = 'private';
                    } elseif ($node->isProtected()) {
                        $visibility = 'protected';
                    }

                    $this->methods[$methodName] = [
                        'code' => $bodyCode,
                        'fullCode' => $methodCode,
                        'params' => $params,
                        'return' => $returnType,
                        'visibility' => $visibility,
                        'startLine' => $startLine + 1,
                        'endLine' => $endLine + 1,
                    ];
                }

                return null;
            }
        };

        $traverser->addVisitor($visitor);
        $traverser->traverse($ast);

        return $methods;
    }

    /**
     * Filter use statements for web controller
     * 
     * @param array<string> $uses Original use statements
     * @return array<string> Filtered use statements
     */
    private function filterUsesForWeb(array $uses): array
    {
        // Remove API-specific use statements
        return array_filter($uses, function ($use) {
            return !str_contains($use, 'ResourceController') &&
                   !str_contains($use, 'BaseApiController');
        });
    }

    /**
     * Filter use statements for API controller
     * 
     * @param array<string> $uses Original use statements
     * @return array<string> Filtered use statements
     */
    private function filterUsesForApi(array $uses): array
    {
        // Remove web-specific use statements (if any)
        return array_filter($uses, function ($use) {
            return !str_contains($use, 'BaseController') ||
                   str_contains($use, 'BaseApiController');
        });
    }
}

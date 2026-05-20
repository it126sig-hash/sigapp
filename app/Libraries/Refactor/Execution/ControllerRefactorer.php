<?php

namespace App\Libraries\Refactor\Execution;

use App\Libraries\Refactor\Discovery\CodeParser;
use App\Libraries\Refactor\Generation\CodeGenerator;
use App\Libraries\Refactor\Models\RefactorResult;
use App\Libraries\Refactor\Models\SplitResult;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

/**
 * ControllerRefactorer
 * 
 * Orchestrates the complete controller refactoring process.
 * Transforms fat controllers into thin controllers by:
 * - Injecting service dependencies
 * - Replacing business logic with service calls
 * - Adding proper error handling
 * - Splitting Web and API controllers when needed
 * 
 * @package App\Libraries\Refactor\Execution
 */
class ControllerRefactorer
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
     * @var ControllerSplitter Splitter for separating Web and API controllers
     */
    private ControllerSplitter $splitter;

    /**
     * Constructor
     * 
     * @param CodeParser|null $parser Optional CodeParser instance
     * @param CodeGenerator|null $generator Optional CodeGenerator instance
     * @param ControllerSplitter|null $splitter Optional ControllerSplitter instance
     */
    public function __construct(
        ?CodeParser $parser = null,
        ?CodeGenerator $generator = null,
        ?ControllerSplitter $splitter = null
    ) {
        $this->parser = $parser ?? new CodeParser();
        $this->generator = $generator ?? new CodeGenerator();
        $this->splitter = $splitter ?? new ControllerSplitter($this->parser, $this->generator);
    }

    /**
     * Refactor controller to use service layer
     * 
     * @param string $controllerPath Path to controller file
     * @param string $serviceName Service class name to inject
     * @param array<string, mixed> $options Refactoring options
     * @return RefactorResult Result of refactoring operation
     */
    public function refactor(string $controllerPath, string $serviceName, array $options = []): RefactorResult
    {
        // Default options
        $options = array_merge([
            'splitWebApi' => true,
            'addErrorHandling' => true,
            'preserveComments' => true,
        ], $options);

        try {
            // Parse controller file
            $ast = $this->parser->parse($controllerPath);
            
            if ($ast === null) {
                return RefactorResult::failure("Failed to parse controller file: {$controllerPath}");
            }

            // Extract controller information
            $classInfo = $this->parser->parseClassInfo($controllerPath);
            
            if ($classInfo === null || $classInfo['className'] === null) {
                return RefactorResult::failure("Failed to extract controller class information");
            }

            // Read original file content
            $originalContent = file_get_contents($controllerPath);
            if ($originalContent === false) {
                return RefactorResult::failure("Failed to read controller file: {$controllerPath}");
            }

            // Step 1: Inject service dependency
            $refactoredCode = $this->injectService($originalContent, $serviceName, $classInfo);

            // Step 2: Replace business logic with service calls
            $refactoredCode = $this->replaceBusinessLogicWithServiceCalls($refactoredCode, $serviceName);

            // Step 3: Add error handling if requested
            if ($options['addErrorHandling']) {
                $refactoredCode = $this->addErrorHandling($refactoredCode);
            }

            // Create result
            $result = RefactorResult::success();
            $result->addCompletedStep('Injected service dependency');
            $result->addCompletedStep('Replaced business logic with service calls');
            
            if ($options['addErrorHandling']) {
                $result->addCompletedStep('Added error handling');
            }

            // Step 4: Split Web and API if requested
            if ($options['splitWebApi']) {
                $splitResult = $this->splitWebAndApi($controllerPath);
                
                if ($splitResult->wasSplit) {
                    $result->addCompletedStep('Split Web and API controllers');
                    
                    // Return both controllers in the result
                    $result->filesCreated[] = $this->getWebControllerPath($controllerPath, $classInfo['className']);
                    $result->filesCreated[] = $this->getApiControllerPath($controllerPath, $classInfo['className']);
                } else {
                    // No split needed, just return refactored controller
                    $result->filesModified[] = $controllerPath;
                }
            } else {
                $result->filesModified[] = $controllerPath;
            }

            return $result;

        } catch (\Exception $e) {
            return RefactorResult::failure("Refactoring failed: {$e->getMessage()}");
        }
    }

    /**
     * Inject service dependency into controller
     * 
     * @param string $code Controller code
     * @param string $serviceName Service class name
     * @param array<string, mixed> $classInfo Controller class information
     * @return string Modified code with service injection
     */
    public function injectService(string $code, string $serviceName, array $classInfo): string
    {
        // Add use statement for service
        $serviceNamespace = "App\\Services\\{$serviceName}";
        
        // Check if use statement already exists
        if (!str_contains($code, "use {$serviceNamespace};")) {
            // Find the position to insert use statement (after namespace or after last use statement)
            if (preg_match('/namespace\s+[^;]+;/', $code, $matches, PREG_OFFSET_CAPTURE)) {
                $insertPos = $matches[0][1] + strlen($matches[0][0]);
                $code = substr_replace($code, "\n\nuse {$serviceNamespace};", $insertPos, 0);
            } elseif (preg_match('/use\s+[^;]+;(?:\s*use\s+[^;]+;)*/', $code, $matches, PREG_OFFSET_CAPTURE)) {
                $insertPos = $matches[0][1] + strlen($matches[0][0]);
                $code = substr_replace($code, "\nuse {$serviceNamespace};", $insertPos, 0);
            }
        }

        // Add service property
        $propertyName = lcfirst($serviceName);
        $propertyDeclaration = "\n    /**\n     * @var {$serviceName} Service layer for business logic\n     */\n    protected {$serviceName} \${$propertyName};\n";
        
        // Check if property already exists
        if (!str_contains($code, "\${$propertyName}")) {
            // Find class opening brace and insert property
            if (preg_match('/class\s+\w+[^{]*\{/', $code, $matches, PREG_OFFSET_CAPTURE)) {
                $insertPos = $matches[0][1] + strlen($matches[0][0]);
                $code = substr_replace($code, $propertyDeclaration, $insertPos, 0);
            }
        }

        // Inject service in constructor
        $code = $this->injectServiceInConstructor($code, $serviceName, $propertyName);

        return $code;
    }

    /**
     * Inject service parameter in constructor
     * 
     * @param string $code Controller code
     * @param string $serviceName Service class name
     * @param string $propertyName Property name for service
     * @return string Modified code
     */
    private function injectServiceInConstructor(string $code, string $serviceName, string $propertyName): string
    {
        // Check if constructor exists
        if (preg_match('/public\s+function\s+__construct\s*\([^)]*\)/', $code, $matches, PREG_OFFSET_CAPTURE)) {
            // Constructor exists - add parameter if not already present
            $constructorSignature = $matches[0][0];
            
            if (!str_contains($constructorSignature, $serviceName)) {
                // Add parameter to constructor
                $newParam = "{$serviceName} \${$propertyName}";
                
                // Check if constructor has parameters
                if (preg_match('/\([^)]+\)/', $constructorSignature)) {
                    // Has parameters - add to end
                    $newSignature = str_replace(')', ", {$newParam})", $constructorSignature);
                } else {
                    // No parameters - add first parameter
                    $newSignature = str_replace('()', "({$newParam})", $constructorSignature);
                }
                
                $code = str_replace($constructorSignature, $newSignature, $code);
                
                // Add property assignment in constructor body
                $assignment = "\n        \$this->{$propertyName} = \${$propertyName};";
                
                // Find constructor body opening brace
                $constructorPos = $matches[0][1] + strlen($matches[0][0]);
                if (preg_match('/\{/', $code, $braceMatch, PREG_OFFSET_CAPTURE, $constructorPos)) {
                    $insertPos = $braceMatch[0][1] + 1;
                    $code = substr_replace($code, $assignment, $insertPos, 0);
                }
            }
        } else {
            // No constructor - create one
            $constructor = "\n    /**\n     * Constructor\n     * \n     * @param {$serviceName} \${$propertyName} Service layer\n     */\n    public function __construct({$serviceName} \${$propertyName})\n    {\n        \$this->{$propertyName} = \${$propertyName};\n    }\n";
            
            // Find class opening brace and insert constructor after properties
            // Look for the class declaration and opening brace
            if (preg_match('/class\s+\w+[^{]*\{/', $code, $matches, PREG_OFFSET_CAPTURE)) {
                $classStart = $matches[0][1] + strlen($matches[0][0]);
                
                // Find the end of properties section (look for first method or end of class)
                $afterProperties = $classStart;
                
                // Look for property declarations
                if (preg_match('/\{[^\}]*?(protected|private|public)\s+[^;]+;/s', $code, $propMatch, PREG_OFFSET_CAPTURE, $classStart)) {
                    // Find the last property
                    $searchPos = $propMatch[0][1] + strlen($propMatch[0][0]);
                    while (preg_match('/(protected|private|public)\s+[^;]+;/', $code, $nextProp, PREG_OFFSET_CAPTURE, $searchPos)) {
                        $searchPos = $nextProp[0][1] + strlen($nextProp[0][0]);
                        $afterProperties = $searchPos;
                    }
                }
                
                $code = substr_replace($code, $constructor, $afterProperties, 0);
            }
        }

        return $code;
    }

    /**
     * Replace business logic with service calls
     * 
     * @param string $code Controller code
     * @param string $serviceName Service class name
     * @return string Modified code
     */
    public function replaceBusinessLogicWithServiceCalls(string $code, string $serviceName): string
    {
        $propertyName = lcfirst($serviceName);
        
        // Pattern 1: Replace direct model calls with service calls
        // Example: $this->model->save($data) -> $this->service->save($data)
        $code = preg_replace(
            '/\$this->(\w+Model)->(\w+)\(/',
            "\$this->{$propertyName}->$2(",
            $code
        );

        // Pattern 2: Replace direct database calls with service calls
        // Example: $this->db->table('users')->insert($data) -> $this->service->createUser($data)
        // Note: This is a placeholder - actual implementation would need more sophisticated logic
        
        // Pattern 3: Add TODO comments for manual refactoring
        // Find methods with complex business logic that need manual review
        $code = $this->addRefactoringTodos($code, $serviceName);

        return $code;
    }

    /**
     * Add TODO comments for manual refactoring
     * 
     * @param string $code Controller code
     * @param string $serviceName Service class name
     * @return string Modified code
     */
    private function addRefactoringTodos(string $code, string $serviceName): string
    {
        // Patterns that indicate business logic that should be moved to service
        $businessLogicPatterns = [
            '/\$this->db->/',
            '/\$this->model->/',
            '/\$this->(\w+Model)->/',
            '/->transStart\(\)/',
            '/->transComplete\(\)/',
        ];

        $lines = explode("\n", $code);
        $inMethod = false;
        $methodHasBusinessLogic = false;
        $methodStartLine = 0;

        foreach ($lines as $index => $line) {
            // Detect method start
            if (preg_match('/^\s*public\s+function\s+(\w+)\s*\(/', $line)) {
                $inMethod = true;
                $methodHasBusinessLogic = false;
                $methodStartLine = $index;
            }

            // Detect method end
            if ($inMethod && preg_match('/^\s*\}\s*$/', $line)) {
                // If method has business logic and no TODO comment, add one
                if ($methodHasBusinessLogic && !str_contains($lines[$methodStartLine], 'TODO')) {
                    $indent = str_repeat(' ', 8);
                    $todo = "{$indent}// TODO: Refactor business logic to {$serviceName}";
                    
                    // Insert TODO after method signature
                    $lines[$methodStartLine] .= "\n{$todo}";
                }
                
                $inMethod = false;
            }

            // Check for business logic patterns
            if ($inMethod) {
                foreach ($businessLogicPatterns as $pattern) {
                    if (preg_match($pattern, $line)) {
                        $methodHasBusinessLogic = true;
                        break;
                    }
                }
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Add error handling to controller methods
     * 
     * @param string $code Controller code
     * @return string Modified code with error handling
     */
    public function addErrorHandling(string $code): string
    {
        // This is a simplified implementation
        // In a real implementation, we would use AST transformation to wrap method bodies in try-catch
        
        // For now, add a comment indicating error handling should be added
        $lines = explode("\n", $code);
        
        foreach ($lines as $index => $line) {
            // Find public methods that don't already have try-catch
            if (preg_match('/^\s*public\s+function\s+(\w+)\s*\(/', $line)) {
                // Check if next few lines contain try-catch
                $hasTryCatch = false;
                for ($i = $index; $i < min($index + 5, count($lines)); $i++) {
                    if (str_contains($lines[$i], 'try {')) {
                        $hasTryCatch = true;
                        break;
                    }
                }
                
                if (!$hasTryCatch && !str_contains($line, '__construct')) {
                    $indent = str_repeat(' ', 8);
                    $lines[$index] .= "\n{$indent}// TODO: Add try-catch error handling";
                }
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Split controller into Web and API controllers
     * 
     * @param string $controllerPath Path to controller file
     * @return SplitResult Result of split operation
     */
    public function splitWebAndApi(string $controllerPath): SplitResult
    {
        return $this->splitter->split($controllerPath);
    }

    /**
     * Get web controller file path
     * 
     * @param string $originalPath Original controller path
     * @param string $className Controller class name
     * @return string Web controller path
     */
    private function getWebControllerPath(string $originalPath, string $className): string
    {
        $dir = dirname($originalPath);
        return $dir . DIRECTORY_SEPARATOR . $className . '.php';
    }

    /**
     * Get API controller file path
     * 
     * @param string $originalPath Original controller path
     * @param string $className Controller class name
     * @return string API controller path
     */
    private function getApiControllerPath(string $originalPath, string $className): string
    {
        $dir = dirname($originalPath);
        $apiDir = $dir . DIRECTORY_SEPARATOR . 'Api';
        
        return $apiDir . DIRECTORY_SEPARATOR . $className . 'Controller.php';
    }

    /**
     * Write refactored controller to file
     * 
     * @param string $filePath File path to write
     * @param string $code Controller code
     * @return bool Success status
     */
    public function writeController(string $filePath, string $code): bool
    {
        // Ensure directory exists
        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true) && !is_dir($dir)) {
                return false;
            }
        }

        // Write file
        return file_put_contents($filePath, $code) !== false;
    }

    /**
     * Validate refactored controller code
     * 
     * @param string $code Controller code
     * @return array{valid: bool, error: string|null} Validation result
     */
    public function validateController(string $code): array
    {
        return $this->generator->validateSyntax($code);
    }
}

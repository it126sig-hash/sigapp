<?php

namespace App\Libraries\Refactor\Analysis;

use App\Libraries\Refactor\Exceptions\AnalysisException;

/**
 * CodeAnalyzer
 * 
 * Analyzes controller code structure to identify refactoring needs.
 * Provides detailed analysis of method count, lines of code, dependencies,
 * business logic patterns, database queries, and complexity estimation.
 * 
 * This class is used by the AuditGenerator to understand what needs to be
 * refactored in a controller without modifying any code.
 * 
 * @package App\Libraries\Refactor\Analysis
 */
class CodeAnalyzer
{
    /**
     * @var ASTParser Parser for extracting code structure
     */
    private ASTParser $astParser;

    /**
     * Complexity thresholds for estimation
     */
    private const SIMPLE_METHOD_THRESHOLD = 5;
    private const SIMPLE_LOC_THRESHOLD = 200;
    private const MEDIUM_LOC_THRESHOLD = 500;

    /**
     * Constructor
     * 
     * @param ASTParser|null $astParser Optional ASTParser instance for dependency injection
     */
    public function __construct(?ASTParser $astParser = null)
    {
        $this->astParser = $astParser ?? new ASTParser();
    }

    /**
     * Analyze controller structure
     * 
     * Analyzes the overall structure of a controller including method count,
     * lines of code, and dependencies. This provides a high-level overview
     * of the controller's complexity.
     * 
     * @param string $filePath Path to controller file
     * @return array{
     *     methodCount: int,
     *     linesOfCode: int,
     *     dependencies: array<string>,
     *     methods: array<array{name: string, lineCount: int, startLine: int, endLine: int}>
     * }
     * @throws AnalysisException If file cannot be read or parsed
     */
    public function analyzeControllerStructure(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new AnalysisException("File not found: {$filePath}");
        }

        if (!is_readable($filePath)) {
            throw new AnalysisException("File not readable: {$filePath}");
        }

        // Get class information from AST
        $classInfo = $this->astParser->parseClassInfo($filePath);
        
        if ($classInfo === null) {
            throw new AnalysisException("Failed to parse class info from: {$filePath}");
        }

        // Count lines of code (excluding blank lines and comments)
        $linesOfCode = $this->countLinesOfCode($filePath);

        // Extract dependencies
        $dependencies = $this->extractDependencies($filePath);

        // Analyze methods
        $methods = $this->analyzeMethods($filePath);

        return [
            'methodCount' => count($classInfo['methods'] ?? []),
            'linesOfCode' => $linesOfCode,
            'dependencies' => $dependencies,
            'methods' => $methods,
        ];
    }

    /**
     * Identify business logic in controller
     * 
     * Scans controller code to identify business logic patterns that should
     * be moved to service classes. Looks for calculations, complex conditionals,
     * data transformations, and validation logic.
     * 
     * @param string $filePath Path to controller file
     * @return array<array{
     *     type: string,
     *     description: string,
     *     line: int,
     *     method: string,
     *     snippet: string,
     *     severity: string
     * }> Array of business logic findings
     * @throws AnalysisException If file cannot be read
     */
    public function identifyBusinessLogic(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new AnalysisException("File not found: {$filePath}");
        }

        $code = file_get_contents($filePath);
        if ($code === false) {
            throw new AnalysisException("Failed to read file: {$filePath}");
        }

        $findings = [];

        // Identify calculations (arithmetic operations)
        $findings = array_merge($findings, $this->findCalculations($code));

        // Identify complex conditionals
        $findings = array_merge($findings, $this->findComplexConditionals($code));

        // Identify data transformations
        $findings = array_merge($findings, $this->findDataTransformations($code));

        // Identify validation logic
        $findings = array_merge($findings, $this->findValidationLogic($code));

        // Identify loops processing data
        $findings = array_merge($findings, $this->findDataProcessingLoops($code));

        return $findings;
    }

    /**
     * Identify database queries in controller
     * 
     * Scans controller code to identify direct database queries that should
     * be moved to repository classes. Looks for Query Builder usage, raw queries,
     * and model method calls.
     * 
     * @param string $filePath Path to controller file
     * @return array<array{
     *     type: string,
     *     description: string,
     *     line: int,
     *     method: string,
     *     snippet: string,
     *     severity: string
     * }> Array of database query findings
     * @throws AnalysisException If file cannot be read
     */
    public function identifyDatabaseQueries(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new AnalysisException("File not found: {$filePath}");
        }

        $code = file_get_contents($filePath);
        if ($code === false) {
            throw new AnalysisException("Failed to read file: {$filePath}");
        }

        $findings = [];

        // Identify Query Builder usage
        $findings = array_merge($findings, $this->findQueryBuilderUsage($code));

        // Identify raw SQL queries
        $findings = array_merge($findings, $this->findRawQueries($code));

        // Identify model method calls (find, findAll, where, etc.)
        $findings = array_merge($findings, $this->findModelQueryMethods($code));

        // Identify database connection usage
        $findings = array_merge($findings, $this->findDatabaseConnections($code));

        return $findings;
    }

    /**
     * Estimate refactoring complexity
     * 
     * Estimates the complexity of refactoring a controller based on method count,
     * lines of code, number of dependencies, business logic patterns, and
     * database queries found.
     * 
     * @param string $filePath Path to controller file
     * @return string Complexity level: 'SIMPLE', 'MEDIUM', or 'COMPLEX'
     * @throws AnalysisException If analysis fails
     */
    public function estimateComplexity(string $filePath): string
    {
        $structure = $this->analyzeControllerStructure($filePath);
        $businessLogic = $this->identifyBusinessLogic($filePath);
        $queries = $this->identifyDatabaseQueries($filePath);

        $methodCount = $structure['methodCount'];
        $linesOfCode = $structure['linesOfCode'];
        $dependencyCount = count($structure['dependencies']);
        $businessLogicCount = count($businessLogic);
        $queryCount = count($queries);

        // Calculate complexity score
        $score = 0;

        // Method count factor
        if ($methodCount > self::SIMPLE_METHOD_THRESHOLD) {
            $score += ($methodCount - self::SIMPLE_METHOD_THRESHOLD) * 2;
        }

        // Lines of code factor
        if ($linesOfCode > self::SIMPLE_LOC_THRESHOLD) {
            $score += ($linesOfCode - self::SIMPLE_LOC_THRESHOLD) / 50;
        }

        // Dependency factor
        $score += $dependencyCount * 3;

        // Business logic factor
        $score += $businessLogicCount * 5;

        // Query factor
        $score += $queryCount * 4;

        // Determine complexity level
        if ($score < 20) {
            return 'SIMPLE';
        } elseif ($score < 50) {
            return 'MEDIUM';
        } else {
            return 'COMPLEX';
        }
    }

    /**
     * Count lines of code excluding blank lines and comments
     * 
     * @param string $filePath Path to file
     * @return int Number of lines of code
     */
    private function countLinesOfCode(string $filePath): int
    {
        $code = file_get_contents($filePath);
        if ($code === false) {
            return 0;
        }

        $lines = explode("\n", $code);
        $count = 0;

        $inBlockComment = false;

        foreach ($lines as $line) {
            $trimmed = trim($line);

            // Skip empty lines
            if (empty($trimmed)) {
                continue;
            }

            // Handle block comments
            if (str_contains($trimmed, '/*')) {
                $inBlockComment = true;
            }

            if ($inBlockComment) {
                if (str_contains($trimmed, '*/')) {
                    $inBlockComment = false;
                }
                continue;
            }

            // Skip single-line comments
            if (str_starts_with($trimmed, '//') || str_starts_with($trimmed, '#')) {
                continue;
            }

            $count++;
        }

        return $count;
    }

    /**
     * Extract dependencies from controller
     * 
     * @param string $filePath Path to controller file
     * @return array<string> Array of dependency class names
     */
    private function extractDependencies(string $filePath): array
    {
        $deps = $this->astParser->extractAllDependencies($filePath);
        
        // Combine use statements and instantiations
        $dependencies = $deps['uses'] ?? [];
        
        foreach ($deps['instantiations'] ?? [] as $inst) {
            if (!in_array($inst['class'], $dependencies)) {
                $dependencies[] = $inst['class'];
            }
        }

        return $dependencies;
    }

    /**
     * Analyze methods in controller
     * 
     * @param string $filePath Path to controller file
     * @return array<array{name: string, lineCount: int, startLine: int, endLine: int}>
     */
    private function analyzeMethods(string $filePath): array
    {
        $code = file_get_contents($filePath);
        if ($code === false) {
            return [];
        }

        $methods = [];
        $lines = explode("\n", $code);

        // Simple regex-based method detection
        // This is a simplified approach; for production, use AST parsing
        $currentMethod = null;
        $braceCount = 0;

        foreach ($lines as $lineNum => $line) {
            $trimmed = trim($line);

            // Detect method declaration
            if (preg_match('/^\s*(public|protected|private)\s+function\s+(\w+)\s*\(/', $line, $matches)) {
                if ($currentMethod !== null) {
                    // Close previous method
                    $currentMethod['endLine'] = $lineNum;
                    $currentMethod['lineCount'] = $currentMethod['endLine'] - $currentMethod['startLine'] + 1;
                    $methods[] = $currentMethod;
                }

                $currentMethod = [
                    'name' => $matches[2],
                    'startLine' => $lineNum + 1, // 1-indexed
                    'endLine' => $lineNum + 1,
                    'lineCount' => 0,
                ];
                $braceCount = 0;
            }

            // Track braces to find method end
            if ($currentMethod !== null) {
                $braceCount += substr_count($line, '{') - substr_count($line, '}');
                
                if ($braceCount === 0 && preg_match('/}/', $line)) {
                    $currentMethod['endLine'] = $lineNum + 1;
                    $currentMethod['lineCount'] = $currentMethod['endLine'] - $currentMethod['startLine'] + 1;
                    $methods[] = $currentMethod;
                    $currentMethod = null;
                }
            }
        }

        // Close last method if still open
        if ($currentMethod !== null) {
            $currentMethod['endLine'] = count($lines);
            $currentMethod['lineCount'] = $currentMethod['endLine'] - $currentMethod['startLine'] + 1;
            $methods[] = $currentMethod;
        }

        return $methods;
    }

    /**
     * Find calculations in code
     * 
     * @param string $code Source code
     * @return array<array{type: string, description: string, line: int, method: string, snippet: string, severity: string}>
     */
    private function findCalculations(string $code): array
    {
        $findings = [];
        $lines = explode("\n", $code);

        foreach ($lines as $lineNum => $line) {
            // Look for arithmetic operations (excluding simple assignments)
            if (preg_match('/\$\w+\s*[+\-*\/]\s*\$\w+/', $line) ||
                preg_match('/\$\w+\s*[+\-*\/]=/', $line)) {
                
                $findings[] = [
                    'type' => 'CALCULATION',
                    'description' => 'Arithmetic calculation found - should be in service layer',
                    'line' => $lineNum + 1,
                    'method' => $this->findMethodForLine($code, $lineNum),
                    'snippet' => trim($line),
                    'severity' => 'MEDIUM',
                ];
            }
        }

        return $findings;
    }

    /**
     * Find complex conditionals in code
     * 
     * @param string $code Source code
     * @return array<array{type: string, description: string, line: int, method: string, snippet: string, severity: string}>
     */
    private function findComplexConditionals(string $code): array
    {
        $findings = [];
        $lines = explode("\n", $code);

        foreach ($lines as $lineNum => $line) {
            // Look for complex if statements (multiple conditions)
            if (preg_match('/if\s*\([^)]*&&[^)]*&&/', $line) ||
                preg_match('/if\s*\([^)]*\|\|[^)]*\|\|/', $line)) {
                
                $findings[] = [
                    'type' => 'COMPLEX_CONDITIONAL',
                    'description' => 'Complex conditional logic - should be in service layer',
                    'line' => $lineNum + 1,
                    'method' => $this->findMethodForLine($code, $lineNum),
                    'snippet' => trim($line),
                    'severity' => 'HIGH',
                ];
            }
        }

        return $findings;
    }

    /**
     * Find data transformations in code
     * 
     * @param string $code Source code
     * @return array<array{type: string, description: string, line: int, method: string, snippet: string, severity: string}>
     */
    private function findDataTransformations(string $code): array
    {
        $findings = [];
        $lines = explode("\n", $code);

        foreach ($lines as $lineNum => $line) {
            // Look for array_map, array_filter, array_reduce
            if (preg_match('/array_(map|filter|reduce|walk)/', $line)) {
                $findings[] = [
                    'type' => 'DATA_TRANSFORMATION',
                    'description' => 'Data transformation logic - should be in service layer',
                    'line' => $lineNum + 1,
                    'method' => $this->findMethodForLine($code, $lineNum),
                    'snippet' => trim($line),
                    'severity' => 'MEDIUM',
                ];
            }
        }

        return $findings;
    }

    /**
     * Find validation logic in code
     * 
     * @param string $code Source code
     * @return array<array{type: string, description: string, line: int, method: string, snippet: string, severity: string}>
     */
    private function findValidationLogic(string $code): array
    {
        $findings = [];
        $lines = explode("\n", $code);

        foreach ($lines as $lineNum => $line) {
            // Look for validation patterns
            if (preg_match('/->validate\(/', $line) ||
                preg_match('/\$this->validation/', $line) ||
                preg_match('/empty\(\$\w+\)/', $line) ||
                preg_match('/isset\(\$\w+\)/', $line)) {
                
                $findings[] = [
                    'type' => 'VALIDATION',
                    'description' => 'Validation logic - should be in service layer or validation class',
                    'line' => $lineNum + 1,
                    'method' => $this->findMethodForLine($code, $lineNum),
                    'snippet' => trim($line),
                    'severity' => 'MEDIUM',
                ];
            }
        }

        return $findings;
    }

    /**
     * Find data processing loops in code
     * 
     * @param string $code Source code
     * @return array<array{type: string, description: string, line: int, method: string, snippet: string, severity: string}>
     */
    private function findDataProcessingLoops(string $code): array
    {
        $findings = [];
        $lines = explode("\n", $code);

        foreach ($lines as $lineNum => $line) {
            // Look for foreach loops
            if (preg_match('/foreach\s*\(/', $line)) {
                $findings[] = [
                    'type' => 'DATA_PROCESSING_LOOP',
                    'description' => 'Data processing loop - should be in service layer',
                    'line' => $lineNum + 1,
                    'method' => $this->findMethodForLine($code, $lineNum),
                    'snippet' => trim($line),
                    'severity' => 'MEDIUM',
                ];
            }
        }

        return $findings;
    }

    /**
     * Find Query Builder usage in code
     * 
     * @param string $code Source code
     * @return array<array{type: string, description: string, line: int, method: string, snippet: string, severity: string}>
     */
    private function findQueryBuilderUsage(string $code): array
    {
        $findings = [];
        $lines = explode("\n", $code);

        foreach ($lines as $lineNum => $line) {
            // Look for Query Builder methods
            if (preg_match('/->table\(/', $line) ||
                preg_match('/->select\(/', $line) ||
                preg_match('/->where\(/', $line) ||
                preg_match('/->join\(/', $line) ||
                preg_match('/->orderBy\(/', $line) ||
                preg_match('/->groupBy\(/', $line)) {
                
                $findings[] = [
                    'type' => 'QUERY_BUILDER',
                    'description' => 'Query Builder usage - should be in repository layer',
                    'line' => $lineNum + 1,
                    'method' => $this->findMethodForLine($code, $lineNum),
                    'snippet' => trim($line),
                    'severity' => 'HIGH',
                ];
            }
        }

        return $findings;
    }

    /**
     * Find raw SQL queries in code
     * 
     * @param string $code Source code
     * @return array<array{type: string, description: string, line: int, method: string, snippet: string, severity: string}>
     */
    private function findRawQueries(string $code): array
    {
        $findings = [];
        $lines = explode("\n", $code);

        foreach ($lines as $lineNum => $line) {
            // Look for raw SQL queries
            if (preg_match('/->query\(/', $line) ||
                preg_match('/(SELECT|INSERT|UPDATE|DELETE)\s+/i', $line)) {
                
                $findings[] = [
                    'type' => 'RAW_QUERY',
                    'description' => 'Raw SQL query - should be in repository layer with Query Builder',
                    'line' => $lineNum + 1,
                    'method' => $this->findMethodForLine($code, $lineNum),
                    'snippet' => trim($line),
                    'severity' => 'CRITICAL',
                ];
            }
        }

        return $findings;
    }

    /**
     * Find model query methods in code
     * 
     * @param string $code Source code
     * @return array<array{type: string, description: string, line: int, method: string, snippet: string, severity: string}>
     */
    private function findModelQueryMethods(string $code): array
    {
        $findings = [];
        $lines = explode("\n", $code);

        foreach ($lines as $lineNum => $line) {
            // Look for model query methods
            if (preg_match('/->find\(/', $line) ||
                preg_match('/->findAll\(/', $line) ||
                preg_match('/->where\(/', $line) ||
                preg_match('/->first\(/', $line) ||
                preg_match('/->insert\(/', $line) ||
                preg_match('/->update\(/', $line) ||
                preg_match('/->delete\(/', $line)) {
                
                $findings[] = [
                    'type' => 'MODEL_QUERY',
                    'description' => 'Model query method - should be in repository layer',
                    'line' => $lineNum + 1,
                    'method' => $this->findMethodForLine($code, $lineNum),
                    'snippet' => trim($line),
                    'severity' => 'HIGH',
                ];
            }
        }

        return $findings;
    }

    /**
     * Find database connection usage in code
     * 
     * @param string $code Source code
     * @return array<array{type: string, description: string, line: int, method: string, snippet: string, severity: string}>
     */
    private function findDatabaseConnections(string $code): array
    {
        $findings = [];
        $lines = explode("\n", $code);

        foreach ($lines as $lineNum => $line) {
            // Look for database connection usage
            if (preg_match('/\$this->db/', $line) ||
                preg_match('/database\(\)/', $line) ||
                preg_match('/\\\Config\\\Database::connect/', $line)) {
                
                $findings[] = [
                    'type' => 'DATABASE_CONNECTION',
                    'description' => 'Direct database connection usage - should be in repository layer',
                    'line' => $lineNum + 1,
                    'method' => $this->findMethodForLine($code, $lineNum),
                    'snippet' => trim($line),
                    'severity' => 'HIGH',
                ];
            }
        }

        return $findings;
    }

    /**
     * Find which method a line belongs to
     * 
     * @param string $code Source code
     * @param int $lineNum Line number (0-indexed)
     * @return string Method name or 'unknown'
     */
    private function findMethodForLine(string $code, int $lineNum): string
    {
        $lines = explode("\n", $code);
        $currentMethod = 'unknown';

        for ($i = $lineNum; $i >= 0; $i--) {
            if (preg_match('/function\s+(\w+)\s*\(/', $lines[$i], $matches)) {
                $currentMethod = $matches[1];
                break;
            }
        }

        return $currentMethod;
    }
}

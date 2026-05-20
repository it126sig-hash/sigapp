<?php

namespace App\Libraries\Refactor\Generation;

use App\Libraries\Refactor\Contracts\AnalyzerInterface;

/**
 * QueryAnalyzer
 * 
 * Utility for analyzing raw SQL queries and converting them to CodeIgniter 4 Query Builder syntax.
 * Identifies query structure, parameters for binding, and generates secure Query Builder code.
 * 
 * @package App\Libraries\Refactor\Generation
 */
class QueryAnalyzer implements AnalyzerInterface
{
    /**
     * @var array<string> Supported SQL operations
     */
    private const SUPPORTED_OPERATIONS = ['SELECT', 'INSERT', 'UPDATE', 'DELETE'];

    /**
     * @var array<string> SQL clauses in order of appearance
     */
    private const SQL_CLAUSES = [
        'SELECT', 'FROM', 'JOIN', 'LEFT JOIN', 'RIGHT JOIN', 'INNER JOIN',
        'WHERE', 'GROUP BY', 'HAVING', 'ORDER BY', 'LIMIT', 'OFFSET'
    ];

    /**
     * Analyze a raw SQL query
     * 
     * @param mixed $data Raw SQL query string
     * @return array{
     *     operation: string,
     *     table: string|null,
     *     select: array<string>,
     *     joins: array<array{type: string, table: string, condition: string}>,
     *     where: array<string>,
     *     groupBy: array<string>,
     *     having: array<string>,
     *     orderBy: array<string>,
     *     limit: int|null,
     *     offset: int|null,
     *     parameters: array<string>,
     *     hasSubquery: bool,
     *     rawQuery: string
     * } Parsed query structure
     */
    public function analyze(mixed $data): array
    {
        if (!is_string($data)) {
            throw new \InvalidArgumentException('Query must be a string');
        }

        $query = trim($data);
        
        return [
            'operation' => $this->extractOperation($query),
            'table' => $this->extractTable($query),
            'select' => $this->extractSelect($query),
            'joins' => $this->extractJoins($query),
            'where' => $this->extractWhere($query),
            'groupBy' => $this->extractGroupBy($query),
            'having' => $this->extractHaving($query),
            'orderBy' => $this->extractOrderBy($query),
            'limit' => $this->extractLimit($query),
            'offset' => $this->extractOffset($query),
            'parameters' => $this->identifyParameters($query),
            'hasSubquery' => $this->hasSubquery($query),
            'rawQuery' => $query,
        ];
    }

    /**
     * Convert raw SQL query to Query Builder syntax
     * 
     * @param string $rawQuery Raw SQL query
     * @param string $builderVar Variable name for the query builder (default: '$builder')
     * @return string Query Builder code
     */
    public function convertToQueryBuilder(string $rawQuery, string $builderVar = '$builder'): string
    {
        $analysis = $this->analyze($rawQuery);
        $code = [];

        // Handle different operations
        switch ($analysis['operation']) {
            case 'SELECT':
                $code = $this->generateSelectQueryBuilder($analysis, $builderVar);
                break;
            case 'INSERT':
                $code = $this->generateInsertQueryBuilder($analysis, $builderVar);
                break;
            case 'UPDATE':
                $code = $this->generateUpdateQueryBuilder($analysis, $builderVar);
                break;
            case 'DELETE':
                $code = $this->generateDeleteQueryBuilder($analysis, $builderVar);
                break;
            default:
                throw new \RuntimeException("Unsupported operation: {$analysis['operation']}");
        }

        return implode("\n", $code);
    }

    /**
     * Identify parameters that need binding in a query
     * 
     * @param string $query SQL query
     * @return array<string> List of parameters found in the query
     */
    public function identifyParameters(string $query): array
    {
        $parameters = [];

        // Find PHP variables ($variable)
        if (preg_match_all('/\$(\w+)/', $query, $matches)) {
            $parameters = array_merge($parameters, $matches[1]);
        }

        // Find concatenated values that should be parameters
        // Pattern: "... = " . $var or "... = '$var'"
        if (preg_match_all('/["\'].*?["\'].*?\.\s*\$(\w+)/', $query, $matches)) {
            $parameters = array_merge($parameters, $matches[1]);
        }

        // Find direct variable concatenation in strings
        if (preg_match_all('/["\'][^"\']*\$(\w+)[^"\']*["\']/', $query, $matches)) {
            $parameters = array_merge($parameters, $matches[1]);
        }

        return array_unique($parameters);
    }

    /**
     * Extract SQL operation (SELECT, INSERT, UPDATE, DELETE)
     * 
     * @param string $query SQL query
     * @return string Operation type
     */
    private function extractOperation(string $query): string
    {
        $query = strtoupper(trim($query));
        
        foreach (self::SUPPORTED_OPERATIONS as $operation) {
            if (str_starts_with($query, $operation)) {
                return $operation;
            }
        }

        return 'UNKNOWN';
    }

    /**
     * Extract table name from query
     * 
     * @param string $query SQL query
     * @return string|null Table name
     */
    private function extractTable(string $query): ?string
    {
        $query = $this->normalizeQuery($query);

        // For SELECT queries, we need to find the main FROM clause (not in subqueries)
        // Strategy: Find the first FROM that appears after SELECT but not inside parentheses
        if (preg_match('/^SELECT\s+/i', $query)) {
            // Remove subqueries in parentheses first to avoid matching their FROM clauses
            // Use a more robust method that handles nested parentheses
            $queryWithoutSubqueries = $this->removeParenthesesContent($query);
            
            if (preg_match('/\bFROM\s+([a-z0-9_]+)/i', $queryWithoutSubqueries, $matches)) {
                return $matches[1];
            }
        }

        // INSERT INTO table
        if (preg_match('/INSERT\s+INTO\s+([a-z0-9_]+)/i', $query, $matches)) {
            return $matches[1];
        }

        // UPDATE table
        if (preg_match('/UPDATE\s+([a-z0-9_]+)/i', $query, $matches)) {
            return $matches[1];
        }

        // DELETE FROM table
        if (preg_match('/DELETE\s+FROM\s+([a-z0-9_]+)/i', $query, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Remove content inside parentheses (handles nested parentheses)
     * 
     * @param string $string String to process
     * @return string String with parentheses content removed
     */
    private function removeParenthesesContent(string $string): string
    {
        $result = '';
        $depth = 0;

        for ($i = 0; $i < strlen($string); $i++) {
            $char = $string[$i];

            if ($char === '(') {
                $depth++;
            } elseif ($char === ')') {
                $depth--;
            } elseif ($depth === 0) {
                $result .= $char;
            }
        }

        return $result;
    }

    /**
     * Extract SELECT fields
     * 
     * @param string $query SQL query
     * @return array<string> List of selected fields
     */
    private function extractSelect(string $query): array
    {
        if (!preg_match('/SELECT\s+(.*?)\s+FROM/is', $query, $matches)) {
            return [];
        }

        $selectClause = trim($matches[1]);

        // Handle SELECT *
        if ($selectClause === '*') {
            return ['*'];
        }

        // Split by comma, but respect parentheses (for subqueries)
        $fields = $this->splitByComma($selectClause);

        return array_map('trim', $fields);
    }

    /**
     * Extract JOIN clauses
     * 
     * @param string $query SQL query
     * @return array<array{type: string, table: string, condition: string}> List of joins
     */
    private function extractJoins(string $query): array
    {
        $joins = [];
        $query = $this->normalizeQuery($query);

        // Match all types of joins - improved pattern to handle table aliases and conditions better
        // Pattern explanation:
        // - (LEFT\s+JOIN|RIGHT\s+JOIN|INNER\s+JOIN|JOIN) - Match join type
        // - \s+([a-z0-9_]+) - Match table name
        // - (?:\s+[a-z0-9_]+)? - Optional table alias
        // - \s+ON\s+ - Match ON keyword
        // - (.*?) - Capture condition (non-greedy)
        // - (?=\s+(?:LEFT\s+JOIN|RIGHT\s+JOIN|INNER\s+JOIN|JOIN|WHERE|GROUP\s+BY|ORDER\s+BY|LIMIT)|$) - Lookahead for next clause or end
        $pattern = '/(LEFT\s+JOIN|RIGHT\s+JOIN|INNER\s+JOIN|JOIN)\s+([a-z0-9_]+)(?:\s+[a-z0-9_]+)?\s+ON\s+(.*?)(?=\s+(?:LEFT\s+JOIN|RIGHT\s+JOIN|INNER\s+JOIN|JOIN|WHERE|GROUP\s+BY|ORDER\s+BY|LIMIT)|$)/i';
        
        if (preg_match_all($pattern, $query, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $joins[] = [
                    'type' => strtoupper(trim($match[1])),
                    'table' => trim($match[2]),
                    'condition' => trim($match[3]),
                ];
            }
        }

        return $joins;
    }

    /**
     * Extract WHERE conditions
     * 
     * @param string $query SQL query
     * @return array<string> List of WHERE conditions
     */
    private function extractWhere(string $query): array
    {
        if (!preg_match('/WHERE\s+(.*?)(?:GROUP\s+BY|ORDER\s+BY|LIMIT|$)/is', $query, $matches)) {
            return [];
        }

        $whereClause = trim($matches[1]);

        // Split by AND/OR while preserving the operators
        $conditions = preg_split('/\s+(AND|OR)\s+/i', $whereClause, -1, PREG_SPLIT_DELIM_CAPTURE);

        $result = [];
        for ($i = 0; $i < count($conditions); $i++) {
            $condition = trim($conditions[$i]);
            if ($condition && !in_array(strtoupper($condition), ['AND', 'OR'])) {
                $result[] = $condition;
            }
        }

        return $result;
    }

    /**
     * Extract GROUP BY clause
     * 
     * @param string $query SQL query
     * @return array<string> List of GROUP BY fields
     */
    private function extractGroupBy(string $query): array
    {
        if (!preg_match('/GROUP\s+BY\s+(.*?)(?:HAVING|ORDER\s+BY|LIMIT|$)/is', $query, $matches)) {
            return [];
        }

        $groupByClause = trim($matches[1]);
        $fields = explode(',', $groupByClause);

        return array_map('trim', $fields);
    }

    /**
     * Extract HAVING clause
     * 
     * @param string $query SQL query
     * @return array<string> List of HAVING conditions
     */
    private function extractHaving(string $query): array
    {
        if (!preg_match('/HAVING\s+(.*?)(?:ORDER\s+BY|LIMIT|$)/is', $query, $matches)) {
            return [];
        }

        $havingClause = trim($matches[1]);
        $conditions = explode(',', $havingClause);

        return array_map('trim', $conditions);
    }

    /**
     * Extract ORDER BY clause
     * 
     * @param string $query SQL query
     * @return array<string> List of ORDER BY fields with direction
     */
    private function extractOrderBy(string $query): array
    {
        if (!preg_match('/ORDER\s+BY\s+(.*?)(?:LIMIT|$)/is', $query, $matches)) {
            return [];
        }

        $orderByClause = trim($matches[1]);
        $fields = explode(',', $orderByClause);

        return array_map('trim', $fields);
    }

    /**
     * Extract LIMIT value
     * 
     * @param string $query SQL query
     * @return int|null Limit value
     */
    private function extractLimit(string $query): ?int
    {
        if (preg_match('/LIMIT\s+(\d+)/i', $query, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    /**
     * Extract OFFSET value
     * 
     * @param string $query SQL query
     * @return int|null Offset value
     */
    private function extractOffset(string $query): ?int
    {
        if (preg_match('/OFFSET\s+(\d+)/i', $query, $matches)) {
            return (int) $matches[1];
        }

        // Also check for LIMIT x, y syntax (LIMIT offset, limit)
        if (preg_match('/LIMIT\s+(\d+)\s*,\s*(\d+)/i', $query, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    /**
     * Check if query contains subqueries
     * 
     * @param string $query SQL query
     * @return bool True if query contains subqueries
     */
    private function hasSubquery(string $query): bool
    {
        // Look for SELECT within parentheses
        return (bool) preg_match('/\(\s*SELECT\s+/i', $query);
    }

    /**
     * Generate Query Builder code for SELECT query
     * 
     * @param array<string, mixed> $analysis Query analysis result
     * @param string $builderVar Builder variable name
     * @return array<string> Lines of Query Builder code
     */
    private function generateSelectQueryBuilder(array $analysis, string $builderVar): array
    {
        $code = [];

        // SELECT
        if (!empty($analysis['select'])) {
            if ($analysis['select'] === ['*']) {
                // No need to call select() for SELECT *
            } else {
                $fields = implode(', ', array_map(fn($f) => "'{$f}'", $analysis['select']));
                $code[] = "{$builderVar}->select({$fields})";
            }
        }

        // FROM
        if ($analysis['table']) {
            $code[] = "{$builderVar}->from('{$analysis['table']}')";
        }

        // JOINs
        foreach ($analysis['joins'] as $join) {
            $joinType = strtolower(str_replace(' ', '', $join['type']));
            $method = $joinType === 'join' ? 'join' : $joinType;
            $code[] = "{$builderVar}->{$method}('{$join['table']}', '{$join['condition']}')";
        }

        // WHERE
        foreach ($analysis['where'] as $condition) {
            $code[] = "{$builderVar}->where(\"{$condition}\")";
        }

        // GROUP BY
        if (!empty($analysis['groupBy'])) {
            $fields = implode(', ', array_map(fn($f) => "'{$f}'", $analysis['groupBy']));
            $code[] = "{$builderVar}->groupBy({$fields})";
        }

        // HAVING
        foreach ($analysis['having'] as $condition) {
            $code[] = "{$builderVar}->having('{$condition}')";
        }

        // ORDER BY
        foreach ($analysis['orderBy'] as $order) {
            $code[] = "{$builderVar}->orderBy('{$order}')";
        }

        // LIMIT
        if ($analysis['limit'] !== null) {
            if ($analysis['offset'] !== null) {
                $code[] = "{$builderVar}->limit({$analysis['limit']}, {$analysis['offset']})";
            } else {
                $code[] = "{$builderVar}->limit({$analysis['limit']})";
            }
        }

        return $code;
    }

    /**
     * Generate Query Builder code for INSERT query
     * 
     * @param array<string, mixed> $analysis Query analysis result
     * @param string $builderVar Builder variable name
     * @return array<string> Lines of Query Builder code
     */
    private function generateInsertQueryBuilder(array $analysis, string $builderVar): array
    {
        $code = [];

        if ($analysis['table']) {
            $code[] = "{$builderVar}->table('{$analysis['table']}')";
            $code[] = "{$builderVar}->insert(\$data)";
        }

        return $code;
    }

    /**
     * Generate Query Builder code for UPDATE query
     * 
     * @param array<string, mixed> $analysis Query analysis result
     * @param string $builderVar Builder variable name
     * @return array<string> Lines of Query Builder code
     */
    private function generateUpdateQueryBuilder(array $analysis, string $builderVar): array
    {
        $code = [];

        if ($analysis['table']) {
            $code[] = "{$builderVar}->table('{$analysis['table']}')";
        }

        // WHERE
        foreach ($analysis['where'] as $condition) {
            $code[] = "{$builderVar}->where(\"{$condition}\")";
        }

        $code[] = "{$builderVar}->update(\$data)";

        return $code;
    }

    /**
     * Generate Query Builder code for DELETE query
     * 
     * @param array<string, mixed> $analysis Query analysis result
     * @param string $builderVar Builder variable name
     * @return array<string> Lines of Query Builder code
     */
    private function generateDeleteQueryBuilder(array $analysis, string $builderVar): array
    {
        $code = [];

        if ($analysis['table']) {
            $code[] = "{$builderVar}->table('{$analysis['table']}')";
        }

        // WHERE
        foreach ($analysis['where'] as $condition) {
            $code[] = "{$builderVar}->where(\"{$condition}\")";
        }

        $code[] = "{$builderVar}->delete()";

        return $code;
    }

    /**
     * Normalize query by removing extra whitespace and newlines
     * 
     * @param string $query SQL query
     * @return string Normalized query
     */
    private function normalizeQuery(string $query): string
    {
        // Replace multiple whitespace/newlines with single space
        $query = preg_replace('/\s+/', ' ', $query);
        
        return trim($query);
    }

    /**
     * Split string by comma while respecting parentheses
     * 
     * @param string $string String to split
     * @return array<string> Split parts
     */
    private function splitByComma(string $string): array
    {
        $parts = [];
        $current = '';
        $depth = 0;

        for ($i = 0; $i < strlen($string); $i++) {
            $char = $string[$i];

            if ($char === '(') {
                $depth++;
                $current .= $char;
            } elseif ($char === ')') {
                $depth--;
                $current .= $char;
            } elseif ($char === ',' && $depth === 0) {
                $parts[] = trim($current);
                $current = '';
            } else {
                $current .= $char;
            }
        }

        if ($current !== '') {
            $parts[] = trim($current);
        }

        return $parts;
    }

    /**
     * Generate parameter binding code for a query
     * 
     * @param array<string> $parameters List of parameters
     * @return string Parameter binding code
     */
    public function generateParameterBinding(array $parameters): string
    {
        if (empty($parameters)) {
            return '';
        }

        $bindings = [];
        foreach ($parameters as $param) {
            $bindings[] = "'{$param}' => \${$param}";
        }

        return '$bindings = [' . implode(', ', $bindings) . '];';
    }

    /**
     * Check if a query is safe (uses Query Builder patterns)
     * 
     * @param string $query Query to check
     * @return bool True if query appears to use Query Builder
     */
    public function isSafeQuery(string $query): bool
    {
        // Check if query uses Query Builder methods
        $builderMethods = ['->select(', '->from(', '->where(', '->join(', '->get(', '->insert(', '->update(', '->delete('];
        
        foreach ($builderMethods as $method) {
            if (str_contains($query, $method)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Detect potential SQL injection vulnerabilities in a query
     * 
     * @param string $query Query to check
     * @return array{vulnerable: bool, reasons: array<string>} Vulnerability analysis
     */
    public function detectSqlInjection(string $query): array
    {
        $reasons = [];

        // Check for string concatenation with variables
        if (preg_match('/["\'].*?\.\s*\$/', $query)) {
            $reasons[] = 'String concatenation with variables detected';
        }

        // Check for variables directly in SQL strings
        if (preg_match('/["\'][^"\']*\$\w+[^"\']*["\']/', $query)) {
            $reasons[] = 'Variables embedded in SQL strings';
        }

        // Check for raw query execution
        if (preg_match('/->query\s*\(/', $query)) {
            $reasons[] = 'Raw query execution detected';
        }

        // Check for missing parameter binding
        $parameters = $this->identifyParameters($query);
        if (!empty($parameters) && !$this->isSafeQuery($query)) {
            $reasons[] = 'Parameters found but not using Query Builder';
        }

        return [
            'vulnerable' => !empty($reasons),
            'reasons' => $reasons,
        ];
    }
}

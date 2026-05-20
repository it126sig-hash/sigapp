<?php

namespace Tests\Unit\Refactor\Generation;

use App\Libraries\Refactor\Generation\QueryAnalyzer;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * QueryAnalyzer Unit Tests
 * 
 * Tests for the QueryAnalyzer utility that parses raw SQL queries
 * and converts them to CodeIgniter 4 Query Builder syntax.
 * 
 * @package Tests\Unit\Refactor\Generation
 */
class QueryAnalyzerTest extends CIUnitTestCase
{
    private QueryAnalyzer $analyzer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->analyzer = new QueryAnalyzer();
    }

    // ========================================================================
    // Query Analysis Tests
    // ========================================================================

    public function testAnalyzeSimpleSelectQuery(): void
    {
        $query = "SELECT * FROM users";
        $result = $this->analyzer->analyze($query);

        $this->assertEquals('SELECT', $result['operation']);
        $this->assertEquals('users', $result['table']);
        $this->assertEquals(['*'], $result['select']);
        $this->assertEmpty($result['where']);
        $this->assertFalse($result['hasSubquery']);
    }

    public function testAnalyzeSelectWithSpecificFields(): void
    {
        $query = "SELECT id, name, email FROM users";
        $result = $this->analyzer->analyze($query);

        $this->assertEquals('SELECT', $result['operation']);
        $this->assertEquals('users', $result['table']);
        $this->assertEquals(['id', 'name', 'email'], $result['select']);
    }

    public function testAnalyzeSelectWithWhere(): void
    {
        $query = "SELECT * FROM users WHERE status = 'active' AND role = 'admin'";
        $result = $this->analyzer->analyze($query);

        $this->assertEquals('SELECT', $result['operation']);
        $this->assertEquals('users', $result['table']);
        $this->assertCount(2, $result['where']);
        $this->assertContains("status = 'active'", $result['where']);
        $this->assertContains("role = 'admin'", $result['where']);
    }

    public function testAnalyzeSelectWithJoin(): void
    {
        $query = "SELECT u.*, p.name FROM users u JOIN profiles p ON u.id = p.user_id";
        $result = $this->analyzer->analyze($query);

        $this->assertEquals('SELECT', $result['operation']);
        $this->assertEquals('users', $result['table']);
        $this->assertCount(1, $result['joins']);
        $this->assertEquals('JOIN', $result['joins'][0]['type']);
        $this->assertEquals('profiles', $result['joins'][0]['table']);
        $this->assertStringContainsString('u.id = p.user_id', $result['joins'][0]['condition']);
    }

    public function testAnalyzeSelectWithLeftJoin(): void
    {
        $query = "SELECT * FROM users LEFT JOIN orders ON users.id = orders.user_id";
        $result = $this->analyzer->analyze($query);

        $this->assertCount(1, $result['joins']);
        $this->assertEquals('LEFT JOIN', $result['joins'][0]['type']);
        $this->assertEquals('orders', $result['joins'][0]['table']);
    }

    public function testAnalyzeSelectWithOrderBy(): void
    {
        $query = "SELECT * FROM users ORDER BY created_at DESC, name ASC";
        $result = $this->analyzer->analyze($query);

        $this->assertCount(2, $result['orderBy']);
        $this->assertEquals('created_at DESC', $result['orderBy'][0]);
        $this->assertEquals('name ASC', $result['orderBy'][1]);
    }

    public function testAnalyzeSelectWithLimit(): void
    {
        $query = "SELECT * FROM users LIMIT 10";
        $result = $this->analyzer->analyze($query);

        $this->assertEquals(10, $result['limit']);
        $this->assertNull($result['offset']);
    }

    public function testAnalyzeSelectWithLimitAndOffset(): void
    {
        $query = "SELECT * FROM users LIMIT 10 OFFSET 20";
        $result = $this->analyzer->analyze($query);

        $this->assertEquals(10, $result['limit']);
        $this->assertEquals(20, $result['offset']);
    }

    public function testAnalyzeSelectWithGroupBy(): void
    {
        $query = "SELECT status, COUNT(*) FROM users GROUP BY status";
        $result = $this->analyzer->analyze($query);

        $this->assertCount(1, $result['groupBy']);
        $this->assertEquals('status', $result['groupBy'][0]);
    }

    public function testAnalyzeSelectWithHaving(): void
    {
        $query = "SELECT status, COUNT(*) as total FROM users GROUP BY status HAVING total > 5";
        $result = $this->analyzer->analyze($query);

        $this->assertCount(1, $result['having']);
        $this->assertEquals('total > 5', $result['having'][0]);
    }

    public function testAnalyzeDetectsSubquery(): void
    {
        $query = "SELECT * FROM users WHERE id IN (SELECT user_id FROM orders)";
        $result = $this->analyzer->analyze($query);

        $this->assertTrue($result['hasSubquery']);
    }

    public function testAnalyzeComplexSelectQuery(): void
    {
        $query = "SELECT u.id, u.name, COUNT(o.id) as order_count 
                  FROM users u 
                  LEFT JOIN orders o ON u.id = o.user_id 
                  WHERE u.status = 'active' 
                  GROUP BY u.id 
                  HAVING order_count > 0 
                  ORDER BY order_count DESC 
                  LIMIT 10";
        
        $result = $this->analyzer->analyze($query);

        $this->assertEquals('SELECT', $result['operation']);
        $this->assertEquals('users', $result['table']);
        $this->assertCount(1, $result['joins']);
        $this->assertCount(1, $result['where']);
        $this->assertCount(1, $result['groupBy']);
        $this->assertCount(1, $result['having']);
        $this->assertCount(1, $result['orderBy']);
        $this->assertEquals(10, $result['limit']);
    }

    public function testAnalyzeInsertQuery(): void
    {
        $query = "INSERT INTO users (name, email) VALUES ('John', 'john@example.com')";
        $result = $this->analyzer->analyze($query);

        $this->assertEquals('INSERT', $result['operation']);
        $this->assertEquals('users', $result['table']);
    }

    public function testAnalyzeUpdateQuery(): void
    {
        $query = "UPDATE users SET status = 'inactive' WHERE id = 5";
        $result = $this->analyzer->analyze($query);

        $this->assertEquals('UPDATE', $result['operation']);
        $this->assertEquals('users', $result['table']);
        $this->assertCount(1, $result['where']);
    }

    public function testAnalyzeDeleteQuery(): void
    {
        $query = "DELETE FROM users WHERE status = 'deleted'";
        $result = $this->analyzer->analyze($query);

        $this->assertEquals('DELETE', $result['operation']);
        $this->assertEquals('users', $result['table']);
        $this->assertCount(1, $result['where']);
    }

    // ========================================================================
    // Parameter Identification Tests
    // ========================================================================

    public function testIdentifyParametersInSimpleQuery(): void
    {
        $query = "SELECT * FROM users WHERE id = \$userId";
        $parameters = $this->analyzer->identifyParameters($query);

        $this->assertContains('userId', $parameters);
    }

    public function testIdentifyMultipleParameters(): void
    {
        $query = "SELECT * FROM users WHERE status = \$status AND role = \$role";
        $parameters = $this->analyzer->identifyParameters($query);

        $this->assertCount(2, $parameters);
        $this->assertContains('status', $parameters);
        $this->assertContains('role', $parameters);
    }

    public function testIdentifyParametersInConcatenation(): void
    {
        $query = "SELECT * FROM users WHERE id = " . "\$userId";
        $parameters = $this->analyzer->identifyParameters($query);

        $this->assertContains('userId', $parameters);
    }

    public function testIdentifyParametersInStringInterpolation(): void
    {
        $query = "SELECT * FROM users WHERE name = '\$userName'";
        $parameters = $this->analyzer->identifyParameters($query);

        $this->assertContains('userName', $parameters);
    }

    public function testIdentifyParametersReturnsUniqueValues(): void
    {
        $query = "SELECT * FROM users WHERE id = \$id OR parent_id = \$id";
        $parameters = $this->analyzer->identifyParameters($query);

        $this->assertCount(1, $parameters);
        $this->assertContains('id', $parameters);
    }

    // ========================================================================
    // Query Builder Conversion Tests
    // ========================================================================

    public function testConvertSimpleSelectToQueryBuilder(): void
    {
        $query = "SELECT * FROM users";
        $result = $this->analyzer->convertToQueryBuilder($query);

        $this->assertStringContainsString("\$builder->from('users')", $result);
    }

    public function testConvertSelectWithFieldsToQueryBuilder(): void
    {
        $query = "SELECT id, name, email FROM users";
        $result = $this->analyzer->convertToQueryBuilder($query);

        $this->assertStringContainsString("\$builder->select('id', 'name', 'email')", $result);
        $this->assertStringContainsString("\$builder->from('users')", $result);
    }

    public function testConvertSelectWithWhereToQueryBuilder(): void
    {
        $query = "SELECT * FROM users WHERE status = 'active'";
        $result = $this->analyzer->convertToQueryBuilder($query);

        $this->assertStringContainsString("\$builder->from('users')", $result);
        $this->assertStringContainsString("\$builder->where(\"status = 'active'\")", $result);
    }

    public function testConvertSelectWithJoinToQueryBuilder(): void
    {
        $query = "SELECT * FROM users JOIN profiles ON users.id = profiles.user_id";
        $result = $this->analyzer->convertToQueryBuilder($query);

        $this->assertStringContainsString("\$builder->from('users')", $result);
        $this->assertStringContainsString("\$builder->join('profiles', 'users.id = profiles.user_id')", $result);
    }

    public function testConvertSelectWithLeftJoinToQueryBuilder(): void
    {
        $query = "SELECT * FROM users LEFT JOIN orders ON users.id = orders.user_id";
        $result = $this->analyzer->convertToQueryBuilder($query);

        $this->assertStringContainsString("\$builder->leftjoin('orders', 'users.id = orders.user_id')", $result);
    }

    public function testConvertSelectWithOrderByToQueryBuilder(): void
    {
        $query = "SELECT * FROM users ORDER BY created_at DESC";
        $result = $this->analyzer->convertToQueryBuilder($query);

        $this->assertStringContainsString("\$builder->orderBy('created_at DESC')", $result);
    }

    public function testConvertSelectWithLimitToQueryBuilder(): void
    {
        $query = "SELECT * FROM users LIMIT 10";
        $result = $this->analyzer->convertToQueryBuilder($query);

        $this->assertStringContainsString("\$builder->limit(10)", $result);
    }

    public function testConvertSelectWithLimitAndOffsetToQueryBuilder(): void
    {
        $query = "SELECT * FROM users LIMIT 10 OFFSET 20";
        $result = $this->analyzer->convertToQueryBuilder($query);

        $this->assertStringContainsString("\$builder->limit(10, 20)", $result);
    }

    public function testConvertSelectWithGroupByToQueryBuilder(): void
    {
        $query = "SELECT status, COUNT(*) FROM users GROUP BY status";
        $result = $this->analyzer->convertToQueryBuilder($query);

        $this->assertStringContainsString("\$builder->groupBy('status')", $result);
    }

    public function testConvertInsertToQueryBuilder(): void
    {
        $query = "INSERT INTO users (name, email) VALUES ('John', 'john@example.com')";
        $result = $this->analyzer->convertToQueryBuilder($query);

        $this->assertStringContainsString("\$builder->table('users')", $result);
        $this->assertStringContainsString("\$builder->insert(\$data)", $result);
    }

    public function testConvertUpdateToQueryBuilder(): void
    {
        $query = "UPDATE users SET status = 'inactive' WHERE id = 5";
        $result = $this->analyzer->convertToQueryBuilder($query);

        $this->assertStringContainsString("\$builder->table('users')", $result);
        $this->assertStringContainsString("\$builder->where(\"id = 5\")", $result);
        $this->assertStringContainsString("\$builder->update(\$data)", $result);
    }

    public function testConvertDeleteToQueryBuilder(): void
    {
        $query = "DELETE FROM users WHERE status = 'deleted'";
        $result = $this->analyzer->convertToQueryBuilder($query);

        $this->assertStringContainsString("\$builder->table('users')", $result);
        $this->assertStringContainsString("\$builder->where(\"status = 'deleted'\")", $result);
        $this->assertStringContainsString("\$builder->delete()", $result);
    }

    public function testConvertWithCustomBuilderVariable(): void
    {
        $query = "SELECT * FROM users";
        $result = $this->analyzer->convertToQueryBuilder($query, '$db');

        $this->assertStringContainsString("\$db->from('users')", $result);
    }

    // ========================================================================
    // Parameter Binding Tests
    // ========================================================================

    public function testGenerateParameterBinding(): void
    {
        $parameters = ['userId', 'status'];
        $result = $this->analyzer->generateParameterBinding($parameters);

        $this->assertStringContainsString("'userId' => \$userId", $result);
        $this->assertStringContainsString("'status' => \$status", $result);
    }

    public function testGenerateParameterBindingWithEmptyArray(): void
    {
        $parameters = [];
        $result = $this->analyzer->generateParameterBinding($parameters);

        $this->assertEquals('', $result);
    }

    // ========================================================================
    // Security Tests
    // ========================================================================

    public function testIsSafeQueryDetectsQueryBuilder(): void
    {
        $query = "\$builder->select('*')->from('users')->where('id', \$id)->get()";
        $result = $this->analyzer->isSafeQuery($query);

        $this->assertTrue($result);
    }

    public function testIsSafeQueryDetectsRawQuery(): void
    {
        $query = "SELECT * FROM users WHERE id = \$id";
        $result = $this->analyzer->isSafeQuery($query);

        $this->assertFalse($result);
    }

    public function testDetectSqlInjectionInConcatenation(): void
    {
        $query = "SELECT * FROM users WHERE id = " . "\$userId";
        $result = $this->analyzer->detectSqlInjection($query);

        $this->assertTrue($result['vulnerable']);
        $this->assertNotEmpty($result['reasons']);
    }

    public function testDetectSqlInjectionInStringInterpolation(): void
    {
        $query = "SELECT * FROM users WHERE name = '\$userName'";
        $result = $this->analyzer->detectSqlInjection($query);

        $this->assertTrue($result['vulnerable']);
        $this->assertNotEmpty($result['reasons']);
    }

    public function testDetectSqlInjectionInRawQuery(): void
    {
        $query = "\$db->query(\"SELECT * FROM users WHERE id = \$id\")";
        $result = $this->analyzer->detectSqlInjection($query);

        $this->assertTrue($result['vulnerable']);
        $this->assertContains('Raw query execution detected', $result['reasons']);
    }

    public function testDetectSqlInjectionInSafeQuery(): void
    {
        $query = "\$builder->select('*')->from('users')->where('id', \$id)->get()";
        $result = $this->analyzer->detectSqlInjection($query);

        $this->assertFalse($result['vulnerable']);
        $this->assertEmpty($result['reasons']);
    }

    // ========================================================================
    // Edge Cases and Error Handling
    // ========================================================================

    public function testAnalyzeThrowsExceptionForNonStringInput(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Query must be a string');

        $this->analyzer->analyze(123);
    }

    public function testAnalyzeHandlesEmptyQuery(): void
    {
        $query = "";
        $result = $this->analyzer->analyze($query);

        $this->assertEquals('UNKNOWN', $result['operation']);
        $this->assertNull($result['table']);
    }

    public function testAnalyzeHandlesQueryWithExtraWhitespace(): void
    {
        $query = "  SELECT   *   FROM   users  ";
        $result = $this->analyzer->analyze($query);

        $this->assertEquals('SELECT', $result['operation']);
        $this->assertEquals('users', $result['table']);
    }

    public function testAnalyzeHandlesMultilineQuery(): void
    {
        $query = "SELECT *
                  FROM users
                  WHERE status = 'active'
                  ORDER BY created_at DESC";
        
        $result = $this->analyzer->analyze($query);

        $this->assertEquals('SELECT', $result['operation']);
        $this->assertEquals('users', $result['table']);
        $this->assertCount(1, $result['where']);
        $this->assertCount(1, $result['orderBy']);
    }

    public function testConvertThrowsExceptionForUnsupportedOperation(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unsupported operation');

        $query = "TRUNCATE TABLE users";
        $this->analyzer->convertToQueryBuilder($query);
    }

    // ========================================================================
    // Real-world Query Tests (from actual codebase)
    // ========================================================================

    public function testAnalyzeRealWorldSubquery(): void
    {
        $query = "SELECT * FROM mkdt WHERE uniq_id = (SELECT uniq_id FROM mkdt WHERE id_mkdt = \$id_mkdt)";
        $result = $this->analyzer->analyze($query);

        $this->assertEquals('SELECT', $result['operation']);
        $this->assertEquals('mkdt', $result['table']);
        $this->assertTrue($result['hasSubquery']);
        $this->assertContains('id_mkdt', $result['parameters']);
    }

    public function testAnalyzeRealWorldComplexSelect(): void
    {
        $query = "SELECT konsumen.nama_konsumen, konsumen.hp_konsumen, 
                  (SELECT SUM(nominal) FROM log_pembayaran WHERE log_pembayaran.id_mkdt = mkdt.id_mkdt) as sudah_bayar
                  FROM mkdt
                  JOIN kavling ON kavling.id_mkdt = mkdt.id_mkdt
                  JOIN konsumen ON konsumen.id_konsumen = mkdt.id_konsumen
                  WHERE mkdt.status = 'active'";
        
        $result = $this->analyzer->analyze($query);

        $this->assertEquals('SELECT', $result['operation']);
        $this->assertEquals('mkdt', $result['table']);
        $this->assertTrue($result['hasSubquery']);
        $this->assertCount(2, $result['joins']);
    }
}

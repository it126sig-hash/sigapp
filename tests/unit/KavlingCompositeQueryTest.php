<?php

use App\Repositories\KavlingRepository;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

final class KavlingCompositeQueryTest extends CIUnitTestCase
{
    public function testCompositeQueryUsesAggregatedJoinsAndExplicitMkdtAliases(): void
    {
        $repository = new KavlingRepository(Database::connect());
        $reflection = new ReflectionClass($repository);

        $baseQuery = $reflection->getMethod('baseQuery');
        $builder = $baseQuery->invoke($repository);
        $this->assertInstanceOf(BaseBuilder::class, $builder);

        foreach (['addDivisiSelect', 'addCompositeVisualSelect', 'addCompositeFinanceSelect', 'addPencairanAkadSelect'] as $methodName) {
            $method = $reflection->getMethod($methodName);
            $methodName === 'addDivisiSelect'
                ? $method->invoke($repository, $builder, 0)
                : $method->invoke($repository, $builder);
        }

        $sql = $builder->getCompiledSelect(false);

        $this->assertStringContainsString('hargajual`.`is_subsidi', $sql);
        $this->assertStringContainsString('kavling`.`perintah_bangun` AS `is_turun_pembangunan', $sql);
        $this->assertStringNotContainsString('mkdt_perintah_bangun', $sql);
        $this->assertStringContainsString('MIN(jatuh_tempo_tgl) AS jatuh_tempo_tgl', $sql);
        $this->assertStringContainsString('GROUP BY id_mkdt', $sql);
        $this->assertStringContainsString('GROUP BY id_plan', $sql);
        $this->assertStringContainsString("status <> 'void'", $sql);
        $this->assertStringNotContainsString('SELECT jatuh_tempo_tgl FROM keuangan WHERE', $sql);
    }
}

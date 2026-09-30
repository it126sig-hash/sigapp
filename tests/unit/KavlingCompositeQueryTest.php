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
        $this->assertStringContainsString('mkdt`.`id_konsumen` AS `visual_id_konsumen', $sql);
        $this->assertStringContainsString('kavling`.`perintah_bangun` AS `is_turun_pembangunan', $sql);
        $this->assertStringNotContainsString('mkdt_perintah_bangun', $sql);
        $this->assertStringContainsString('COUNT(*) AS tagihan_aktif_count', $sql);
        $this->assertStringContainsString('MIN(CASE', $sql);
        $this->assertStringContainsString('WHEN sudah_dibayar = 0 AND jatuh_tempo_tgl IS NOT NULL', $sql);
        $this->assertStringContainsString('WHERE is_void = 0', $sql);
        $this->assertStringContainsString('GROUP BY id_mkdt', $sql);
        $this->assertStringContainsString('GROUP BY id_plan', $sql);
        $this->assertStringContainsString("status <> 'void'", $sql);
        $this->assertStringContainsString("status IN ('active', 'partial')", $sql);
        $this->assertStringContainsString('pa_pengajuan_outstanding_count', $sql);
        $this->assertStringNotContainsString('SELECT jatuh_tempo_tgl FROM keuangan WHERE', $sql);
    }
}

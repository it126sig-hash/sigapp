<?php

use App\Repositories\KeuanganRepository;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

final class KeuanganBookingQueryTest extends CIUnitTestCase
{
    public function testGroupedQueriesIncludeBookingAndKeepPaidStatusesExclusive(): void
    {
        $repository = new KeuanganRepository(Database::connect('tests', true));

        $belumLunasSql = $repository->getBelumLunasGroupedQuery()->getCompiledSelect();
        $lunasSql = $repository->getLunasGroupedQuery()->getCompiledSelect();

        $this->assertStringNotContainsString("payment_type != 'Booking'", $belumLunasSql);
        $this->assertStringNotContainsString("payment_type != 'Booking'", $lunasSql);
        $this->assertStringContainsString(
            'COALESCE(tagihan_agg.total_tagihan, 0) > COALESCE(NULLIF(paid_detail_agg.total_sudah_bayar_detail, 0), paid_log_agg.total_sudah_bayar_log, 0)',
            $belumLunasSql
        );
        $this->assertStringContainsString(
            'COALESCE(tagihan_agg.total_tagihan, 0) <= COALESCE(NULLIF(paid_detail_agg.total_sudah_bayar_detail, 0), paid_log_agg.total_sudah_bayar_log, 0)',
            $lunasSql
        );
    }
}

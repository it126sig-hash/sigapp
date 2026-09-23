<?php

use App\Repositories\KeuanganRepository;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

final class KeuanganBookingQueryTest extends CIUnitTestCase
{
    public function testGroupedQueriesExcludeSeparateBookingAndPreservePromoBookingAsInstallment(): void
    {
        $repository = new KeuanganRepository(Database::connect('tests', true));

        $belumLunasSql = $repository->getBelumLunasGroupedQuery()->getCompiledSelect();
        $lunasSql = $repository->getLunasGroupedQuery()->getCompiledSelect();

        $this->assertStringContainsString("kil.kategori = 'BO' AND COALESCE(lpd.booking_is_installment, 0) = 0", $belumLunasSql);
        $this->assertStringContainsString("kil.kategori = 'BO' AND COALESCE(lpd.booking_is_installment, 0) = 0", $lunasSql);
        $this->assertStringContainsString(
            'COALESCE(tagihan_agg.total_tagihan, 0) > (COALESCE(paid_detail_agg.total_sudah_bayar_detail, 0) + COALESCE(paid_log_agg.total_sudah_bayar_log, 0))',
            $belumLunasSql
        );
        $this->assertStringContainsString(
            'COALESCE(tagihan_agg.total_tagihan, 0) <= (COALESCE(paid_detail_agg.total_sudah_bayar_detail, 0) + COALESCE(paid_log_agg.total_sudah_bayar_log, 0))',
            $lunasSql
        );
        $this->assertStringContainsString('AS perlu_rekonsiliasi', $lunasSql);
    }
}

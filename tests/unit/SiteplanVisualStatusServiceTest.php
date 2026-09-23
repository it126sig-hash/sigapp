<?php

use App\Services\SiteplanVisualStatusService;
use CodeIgniter\Test\CIUnitTestCase;

final class SiteplanVisualStatusServiceTest extends CIUnitTestCase
{
    private SiteplanVisualStatusService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SiteplanVisualStatusService();
    }

    public function testMkdtUsesLatestMilestoneAndBatalOnlyChangesMkdtRow(): void
    {
        $rows = $this->service->buildRows($this->activeFinance([
            'status_mkdt' => 'Batal',
            'is_batal' => 1,
            'akad' => 1,
            'progres_bangunan' => 50,
            'is_lunas' => 1,
        ]));

        $this->assertSame('Batal', $rows[0]['segments'][0]['config_name']);
        $this->assertSame('Pembangunan', $rows[1]['segments'][0]['config_name']);
        $this->assertSame('Lunas', $rows[2]['segments'][0]['config_name']);
    }

    /**
     * @dataProvider mkdtMilestoneProvider
     */
    public function testMkdtMilestoneResolution(array $data, string $expected): void
    {
        $rows = $this->service->buildRows((object) $data);

        $this->assertSame($expected, $rows[0]['segments'][0]['config_name']);
    }

    public static function mkdtMilestoneProvider(): array
    {
        return [
            'default' => [[], 'Def'],
            'invalid date' => [['booking_tgl' => '2026-02-30'], 'Def'],
            'booking' => [['booking_tgl' => '2026-01-01'], 'Booking'],
            'wawancara' => [['booking_tgl' => '2026-01-01', 'wawancara' => 1], 'Wawancara'],
            'sp3k' => [['wawancara' => 1, 'sp3k_tgl' => '2026-02-01'], 'SP3K'],
            'akad indent' => [['sp3k' => 1, 'akad_indent' => 1], 'Akad Indent'],
            'akad' => [['akad_indent' => 1, 'status_mkdt' => 'Akad'], 'Akad'],
        ];
    }

    /**
     * @dataProvider mkdtMarketVariantProvider
     */
    public function testMkdtMilestonesUsePricelistMarketVariant(array $data, string $expected): void
    {
        $rows = $this->service->buildRows((object) $data);

        $this->assertSame($expected, $rows[0]['segments'][0]['config_name']);
    }

    public static function mkdtMarketVariantProvider(): array
    {
        return [
            'booking subsidi' => [['booking_tgl' => '2026-01-01', 'is_subsidi' => 1], 'Booking Subsidi'],
            'booking komersil' => [['booking_tgl' => '2026-01-01', 'is_subsidi' => 0], 'Booking Komersil'],
            'wawancara subsidi' => [['wawancara' => 1, 'is_subsidi' => 1], 'Wawancara Subsidi'],
            'wawancara komersil' => [['wawancara' => 1, 'is_subsidi' => 0], 'Wawancara Komersil'],
            'sp3k subsidi' => [['sp3k' => 1, 'is_subsidi' => 1], 'SP3K Subsidi'],
            'sp3k komersil' => [['sp3k' => 1, 'is_subsidi' => 0], 'SP3K Komersil'],
            'akad indent subsidi' => [['akad_indent' => 1, 'is_subsidi' => 1], 'Akad Indent Subsidi'],
            'akad indent komersil' => [['akad_indent' => 1, 'is_subsidi' => 0], 'Akad Indent Komersil'],
            'akad subsidi' => [['akad' => 1, 'is_subsidi' => 1], 'Akad Subsidi'],
            'akad komersil' => [['akad' => 1, 'is_subsidi' => 0], 'Akad Komersil'],
        ];
    }

    public function testDefBatalAndUnknownMarketTypeRemainUnsuffixed(): void
    {
        $defaultRows = $this->service->buildRows((object) ['is_subsidi' => 1]);
        $cancelledRows = $this->service->buildRows((object) ['status_mkdt' => 'Batal', 'is_subsidi' => 0]);
        $unknownRows = $this->service->buildRows((object) ['booking_tgl' => '2026-01-01', 'is_subsidi' => null]);

        $this->assertSame('Def', $defaultRows[0]['segments'][0]['config_name']);
        $this->assertSame('Batal', $cancelledRows[0]['segments'][0]['config_name']);
        $this->assertSame('Booking', $unknownRows[0]['segments'][0]['config_name']);
    }

    /**
     * @dataProvider productionProvider
     */
    public function testProductionResolution(float $progress, string $expected): void
    {
        $rows = $this->service->buildRows((object) ['progres_bangunan' => $progress]);

        $this->assertSame($expected, $rows[1]['segments'][0]['config_name']);
    }

    public static function productionProvider(): array
    {
        return [
            [0, 'Def'],
            [1, 'Pembangunan'],
            [99, 'Pembangunan'],
            [100, 'Bangunan 100%'],
        ];
    }

    public function testAppendVisualRowsSupportsArrayRepositoryRows(): void
    {
        $rows = $this->service->appendVisualRows([[
            'booking_tgl' => '2026-01-01',
            'progres_bangunan' => 1,
            'is_lunas' => 0,
        ]]);

        $this->assertCount(3, $rows[0]['visual_rows']);
        $this->assertSame('Booking', $rows[0]['visual_rows'][0]['segments'][0]['config_name']);
    }

    public function testProductionAndDueDateMarkersUseMiddlePosition(): void
    {
        $rows = $this->service->buildRows($this->activeFinance([
            'is_turun_pembangunan' => 1,
            'is_lunas' => 0,
            'jatuh_tempo_tgl' => '2026-09-17',
        ]), new DateTimeImmutable('2026-09-17'));

        $this->assertSame([['config_name' => 'Perintah Bangun', 'position' => 0.5]], $rows[1]['markers']);
        $this->assertSame([['config_name' => 'Jatuh Tempo', 'position' => 0.5]], $rows[2]['markers']);
    }

    public function testLegacyMkdtPerintahBangunDoesNotTriggerProductionMarker(): void
    {
        $rows = $this->service->buildRows((object) [
            'mkdt_perintah_bangun' => 1,
            'is_turun_pembangunan' => 0,
        ]);

        $this->assertSame([], $rows[1]['markers']);
    }

    /**
     * @dataProvider productionMarkerProvider
     */
    public function testProductionMarkerStopsWhenBuildingIsComplete(float $progress, bool $expectsMarker): void
    {
        $rows = $this->service->buildRows((object) [
            'progres_bangunan' => $progress,
            'is_turun_pembangunan' => 1,
        ]);

        $expected = $expectsMarker
            ? [['config_name' => 'Perintah Bangun', 'position' => 0.5]]
            : [];

        $this->assertSame($expected, $rows[1]['markers']);
    }

    public static function productionMarkerProvider(): array
    {
        return [
            'belum mulai' => [0, true],
            'baru mulai' => [1, true],
            'hampir selesai' => [99, true],
            'selesai' => [100, false],
            'lebih dari seratus' => [120, false],
        ];
    }

    /**
     * @dataProvider inactiveFinanceProvider
     */
    public function testFinanceStaysDefaultUntilConsumerAndActiveBillExist(array $data): void
    {
        $rows = $this->service->buildRows((object) array_merge([
            'is_lunas' => 0,
            'jatuh_tempo_tgl' => '2026-09-17',
        ], $data), new DateTimeImmutable('2026-09-17'));

        $this->assertSame([
            ['config_name' => 'Def', 'ratio' => 1.0],
        ], $rows[2]['segments']);
        $this->assertSame([], $rows[2]['markers']);
    }

    public static function inactiveFinanceProvider(): array
    {
        return [
            'belum booking' => [['visual_id_konsumen' => null, 'tagihan_aktif_count' => null]],
            'tagihan tanpa konsumen' => [['visual_id_konsumen' => null, 'tagihan_aktif_count' => 1]],
            'konsumen tanpa tagihan' => [['visual_id_konsumen' => 1, 'tagihan_aktif_count' => 0]],
        ];
    }

    public function testFinanceUsesBelumLunasWhenConsumerAndActiveBillExist(): void
    {
        $rows = $this->service->buildRows($this->activeFinance([
            'is_lunas' => 0,
        ]));

        $this->assertSame([
            ['config_name' => 'Belum Lunas', 'ratio' => 1.0],
        ], $rows[2]['segments']);
    }

    public function testFinanceWithoutSubmissionUsesLunasWithoutMarker(): void
    {
        $rows = $this->service->buildRows($this->activeFinance([
            'is_lunas' => 1,
            'pa_pengajuan_count' => 0,
        ]));

        $this->assertSame([
            ['config_name' => 'Lunas', 'ratio' => 1.0],
        ], $rows[2]['segments']);
        $this->assertSame([], $rows[2]['markers']);
        $this->assertSame([
            'disbursement_ratio' => null,
            'outstanding_submission_count' => 0,
        ], $rows[2]['meta']);
    }

    public function testFinancePartialDisbursementCreatesLeftToRightRatios(): void
    {
        $rows = $this->service->buildRows($this->activeFinance([
            'is_lunas' => 1,
            'pa_pengajuan_count' => 1,
            'pa_pengajuan_outstanding_count' => 1,
            'pa_total_hasil_akad' => 100,
            'pa_total_cair_sum' => 30,
        ]));

        $this->assertSame([
            ['config_name' => 'Pencairan Hasil Akad', 'ratio' => 0.3],
            ['config_name' => 'Lunas', 'ratio' => 0.7],
        ], $rows[2]['segments']);
        $this->assertSame([
            ['config_name' => 'Pengajuan Pencairan Hasil Akad', 'position' => 0.5],
        ], $rows[2]['markers']);
        $this->assertSame([
            'disbursement_ratio' => 0.3,
            'outstanding_submission_count' => 1,
        ], $rows[2]['meta']);
    }

    public function testFinanceSubmissionWithoutDisbursementUsesLunasBaseAndSubmissionMarker(): void
    {
        $rows = $this->service->buildRows($this->activeFinance([
            'is_lunas' => 1,
            'pa_pengajuan_count' => 1,
            'pa_pengajuan_outstanding_count' => 1,
            'pa_total_hasil_akad' => 100,
            'pa_total_cair_sum' => 0,
        ]));

        $this->assertSame([
            ['config_name' => 'Lunas', 'ratio' => 1.0],
        ], $rows[2]['segments']);
        $this->assertSame([
            ['config_name' => 'Pengajuan Pencairan Hasil Akad', 'position' => 0.5],
        ], $rows[2]['markers']);
        $this->assertSame([
            'disbursement_ratio' => 0.0,
            'outstanding_submission_count' => 1,
        ], $rows[2]['meta']);
    }

    public function testFinanceRatiosClampAtOneHundredPercent(): void
    {
        $rows = $this->service->buildRows($this->activeFinance([
            'is_lunas' => 1,
            'pa_pengajuan_count' => 1,
            'pa_pengajuan_outstanding_count' => 0,
            'pa_total_hasil_akad' => 100,
            'pa_total_cair_sum' => 120,
        ]));

        $this->assertSame([
            ['config_name' => 'Pencairan Hasil Akad', 'ratio' => 1.0],
        ], $rows[2]['segments']);
        $this->assertSame([], $rows[2]['markers']);
        $this->assertSame(1.0, $rows[2]['meta']['disbursement_ratio']);
    }

    /**
     * @dataProvider invalidHasilAkadProvider
     */
    public function testFinanceInvalidHasilAkadKeepsLunasBaseAndSubmissionMarker($totalResult): void
    {
        $rows = $this->service->buildRows($this->activeFinance([
            'is_lunas' => 1,
            'pa_pengajuan_count' => 1,
            'pa_pengajuan_outstanding_count' => 1,
            'pa_total_hasil_akad' => $totalResult,
            'pa_total_cair_sum' => 30,
        ]));

        $this->assertSame([
            ['config_name' => 'Lunas', 'ratio' => 1.0],
        ], $rows[2]['segments']);
        $this->assertSame([
            ['config_name' => 'Pengajuan Pencairan Hasil Akad', 'position' => 0.5],
        ], $rows[2]['markers']);
        $this->assertNull($rows[2]['meta']['disbursement_ratio']);
    }

    public static function invalidHasilAkadProvider(): array
    {
        return [
            'null' => [null],
            'nol' => [0],
            'negatif' => [-100],
            'bukan angka' => ['invalid'],
        ];
    }

    public function testFinancePaidSubmissionDoesNotCreateOutstandingMarker(): void
    {
        $rows = $this->service->buildRows($this->activeFinance([
            'is_lunas' => 1,
            'pa_pengajuan_count' => 1,
            'pa_pengajuan_outstanding_count' => 0,
            'pa_total_hasil_akad' => 100,
            'pa_total_cair_sum' => 30,
        ]));

        $this->assertSame([], $rows[2]['markers']);
        $this->assertSame(0, $rows[2]['meta']['outstanding_submission_count']);
        $this->assertSame(0.3, $rows[2]['meta']['disbursement_ratio']);
    }

    /**
     * @dataProvider outstandingMarkerProvider
     */
    public function testFinanceOutstandingMarkersAreDistributedAndCapped(
        int $outstandingCount,
        array $expectedPositions
    ): void {
        $rows = $this->service->buildRows($this->activeFinance([
            'is_lunas' => 1,
            'pa_pengajuan_count' => $outstandingCount,
            'pa_pengajuan_outstanding_count' => $outstandingCount,
            'pa_total_hasil_akad' => 100,
            'pa_total_cair_sum' => 0,
        ]));

        $this->assertSame(
            $expectedPositions,
            array_column($rows[2]['markers'], 'position')
        );
        $this->assertSame($outstandingCount, $rows[2]['meta']['outstanding_submission_count']);
    }

    public static function outstandingMarkerProvider(): array
    {
        return [
            'tidak ada' => [0, []],
            'satu' => [1, [0.5]],
            'dua' => [2, [0.3333, 0.6667]],
            'tiga' => [3, [0.25, 0.5, 0.75]],
            'empat dibatasi tiga garis' => [4, [0.25, 0.5, 0.75]],
        ];
    }

    private function activeFinance(array $data): object
    {
        return (object) array_merge([
            'visual_id_konsumen' => 1,
            'tagihan_aktif_count' => 1,
        ], $data);
    }
}

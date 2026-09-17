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
        $rows = $this->service->buildRows((object) [
            'status_mkdt' => 'Batal',
            'is_batal' => 1,
            'akad' => 1,
            'progres_bangunan' => 50,
            'is_lunas' => 1,
        ]);

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
        $rows = $this->service->buildRows((object) [
            'is_turun_pembangunan' => 1,
            'is_lunas' => 0,
            'jatuh_tempo_tgl' => '2026-09-17',
        ], new DateTimeImmutable('2026-09-17'));

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

    public function testFinancePartialDisbursementCreatesLeftToRightRatios(): void
    {
        $rows = $this->service->buildRows((object) [
            'is_lunas' => 1,
            'pa_pengajuan_count' => 1,
            'pa_total_hasil_akad' => 100,
            'pa_total_cair_sum' => 30,
        ]);

        $this->assertSame([
            ['config_name' => 'Pencairan Hasil Akad', 'ratio' => 0.3],
            ['config_name' => 'Pengajuan Pencairan Hasil Akad', 'ratio' => 0.7],
        ], $rows[2]['segments']);
    }

    public function testFinanceSubmissionWithoutDisbursementUsesSubmissionColor(): void
    {
        $rows = $this->service->buildRows((object) [
            'is_lunas' => 1,
            'pa_pengajuan_count' => 1,
            'pa_total_hasil_akad' => 100,
            'pa_total_cair_sum' => 0,
        ]);

        $this->assertSame([
            ['config_name' => 'Pengajuan Pencairan Hasil Akad', 'ratio' => 1.0],
        ], $rows[2]['segments']);
    }

    public function testFinanceRatiosClampAtOneHundredPercent(): void
    {
        $rows = $this->service->buildRows((object) [
            'is_lunas' => 1,
            'pa_pengajuan_count' => 1,
            'pa_total_hasil_akad' => 100,
            'pa_total_cair_sum' => 120,
        ]);

        $this->assertSame([
            ['config_name' => 'Pencairan Hasil Akad', 'ratio' => 1.0],
        ], $rows[2]['segments']);
    }
}

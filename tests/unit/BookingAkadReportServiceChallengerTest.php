<?php

namespace Tests\Unit;

use App\Repositories\BookingAkadReportRepository;
use App\Services\BookingAkadReportService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Class BookingAkadReportServiceChallengerTest
 *
 * EMPIRICAL ADVERSARIAL STRESS TEST SUITE for Milestone 1.
 * Tests edge cases, boundary conditions, arithmetic discrepancies,
 * division-by-zero guards, and numeric clamping.
 */
final class BookingAkadReportServiceChallengerTest extends CIUnitTestCase
{
    /**
     * 1. Extreme 0 bookings across all months.
     * Verifies division-by-zero prevention and strict 0.0% return.
     */
    public function testExtremeZeroBookingsAcrossAllMonths(): void
    {
        $repository = $this->createMock(BookingAkadReportRepository::class);
        $repository->method('getMonthlyStatusTotals')->willReturn([]);

        $service = new BookingAkadReportService($repository);
        $result = $service->getSummary(13, 2026);

        $this->assertCount(12, $result['months']);
        foreach ($result['months'] as $m) {
            $this->assertSame(0.0, $m['values'][2026]['konversi']);
            $this->assertSame(0, $m['values'][2026]['total_booking_cohort']);
            $this->assertSame(0, $m['values'][2026]['booking_converted_akad']);
        }
        $this->assertSame(0.0, $result['totals'][2026]['konversi']);
        $this->assertSame(0.0, $result['summary'][2026]['total']['konversi']);
    }

    /**
     * 2. Extreme 100% conversion with large volume (1,000,000 transactions).
     */
    public function testExtremeHundredPercentConversionLargeVolume(): void
    {
        $repository = $this->createMock(BookingAkadReportRepository::class);
        $repository->method('getMonthlyStatusTotals')->willReturn([
            [
                'tahun' => 2026,
                'bulan' => 6,
                'booking' => 0,
                'wawancara' => 0,
                'sp3k' => 0,
                'batal' => 0,
                'akad_indent' => 400000,
                'akad_ajb' => 600000,
                'total_booking_cohort' => 1000000,
                'booking_converted_akad' => 1000000,
            ],
        ]);

        $service = new BookingAkadReportService($repository);
        $result = $service->getSummary(13, 2026);

        $june = $result['months'][5]['values'][2026];
        $this->assertSame(100.0, $june['konversi']);
        $this->assertSame(1000000, $june['total_booking_cohort']);
        $this->assertSame(1000000, $june['booking_converted_akad']);
        $this->assertSame(100.0, $result['totals'][2026]['konversi']);
    }

    /**
     * 3. Extreme 0% conversion with large volume (1,000,000 bookings, 0 reached akad).
     */
    public function testExtremeZeroPercentConversionLargeVolume(): void
    {
        $repository = $this->createMock(BookingAkadReportRepository::class);
        $repository->method('getMonthlyStatusTotals')->willReturn([
            [
                'tahun' => 2026,
                'bulan' => 3,
                'booking' => 1000000,
                'wawancara' => 0,
                'sp3k' => 0,
                'batal' => 0,
                'akad_indent' => 0,
                'akad_ajb' => 0,
                'total_booking_cohort' => 1000000,
                'booking_converted_akad' => 0,
            ],
        ]);

        $service = new BookingAkadReportService($repository);
        $result = $service->getSummary(13, 2026);

        $march = $result['months'][2]['values'][2026];
        $this->assertSame(0.0, $march['konversi']);
        $this->assertSame(0.0, $result['totals'][2026]['konversi']);
    }

    /**
     * 4. Adversarial anomaly: Data corruption where converted akad exceeds cohort bookings.
     * Must be strictly clamped to <= 100.0%.
     */
    public function testAdversarialAnomalyClampingAboveHundredPercent(): void
    {
        $repository = $this->createMock(BookingAkadReportRepository::class);
        $repository->method('getMonthlyStatusTotals')->willReturn([
            [
                'tahun' => 2026,
                'bulan' => 1,
                'booking' => 1,
                'total_booking_cohort' => 5,
                'booking_converted_akad' => 10, // Corrupted: 200%
            ],
        ]);

        $service = new BookingAkadReportService($repository);
        $result = $service->getSummary(13, 2026);

        $this->assertSame(100.0, $result['months'][0]['values'][2026]['konversi']);
        $this->assertSame(100.0, $result['totals'][2026]['konversi']);
    }

    /**
     * 5. Adversarial anomaly: Negative numbers from database.
     * Must be strictly clamped to >= 0.0%.
     */
    public function testAdversarialAnomalyClampingBelowZeroPercent(): void
    {
        $repository = $this->createMock(BookingAkadReportRepository::class);
        $repository->method('getMonthlyStatusTotals')->willReturn([
            [
                'tahun' => 2026,
                'bulan' => 1,
                'booking' => 10,
                'total_booking_cohort' => 10,
                'booking_converted_akad' => -5, // Anomaly
            ],
        ]);

        $service = new BookingAkadReportService($repository);
        $result = $service->getSummary(13, 2026);

        $this->assertSame(0.0, $result['months'][0]['values'][2026]['konversi']);
        $this->assertSame(0.0, $result['totals'][2026]['konversi']);
    }

    /**
     * 6. Precision & Boundary Rounding Oracle:
     * Comprehensive sweep of fractional ratios.
     */
    public function testPrecisionAndRoundingSweep(): void
    {
        $testCases = [
            ['cohort' => 3, 'akad' => 1, 'expected' => 33.3], // 33.3333... -> 33.3
            ['cohort' => 3, 'akad' => 2, 'expected' => 66.7], // 66.6666... -> 66.7
            ['cohort' => 6, 'akad' => 1, 'expected' => 16.7], // 16.6666... -> 16.7
            ['cohort' => 6, 'akad' => 5, 'expected' => 83.3], // 83.3333... -> 83.3
            ['cohort' => 7, 'akad' => 1, 'expected' => 14.3], // 14.2857... -> 14.3
            ['cohort' => 7, 'akad' => 3, 'expected' => 42.9], // 42.8571... -> 42.9
            ['cohort' => 7, 'akad' => 5, 'expected' => 71.4], // 71.4285... -> 71.4
            ['cohort' => 8, 'akad' => 1, 'expected' => 12.5], // 12.5000... -> 12.5
            ['cohort' => 8, 'akad' => 3, 'expected' => 37.5], // 37.5000... -> 37.5
            ['cohort' => 9, 'akad' => 1, 'expected' => 11.1], // 11.1111... -> 11.1
            ['cohort' => 9, 'akad' => 5, 'expected' => 55.6], // 55.5555... -> 55.6
            ['cohort' => 11, 'akad' => 1, 'expected' => 9.1],  // 9.0909... -> 9.1
            ['cohort' => 1000, 'akad' => 1, 'expected' => 0.1], // 0.1000 -> 0.1
            ['cohort' => 2000, 'akad' => 1, 'expected' => 0.1], // 0.0500 -> 0.1 (round half up)
            ['cohort' => 4000, 'akad' => 1, 'expected' => 0.0], // 0.0250 -> 0.0
        ];

        $repoData = [];
        $monthIndex = 1;
        // Place up to 12 in year 2026
        foreach (array_slice($testCases, 0, 12) as $tc) {
            $repoData[] = [
                'tahun' => 2026,
                'bulan' => $monthIndex++,
                'booking' => $tc['cohort'],
                'total_booking_cohort' => $tc['cohort'],
                'booking_converted_akad' => $tc['akad'],
            ];
        }

        $repository = $this->createMock(BookingAkadReportRepository::class);
        $repository->method('getMonthlyStatusTotals')->willReturn($repoData);

        $service = new BookingAkadReportService($repository);
        $result = $service->getSummary(13, 2026);

        for ($i = 0; $i < 12; $i++) {
            $expected = $testCases[$i]['expected'];
            $actual = $result['months'][$i]['values'][2026]['konversi'];
            $this->assertSame(
                $expected,
                $actual,
                "Month " . ($i + 1) . " with cohort {$testCases[$i]['cohort']} and akad {$testCases[$i]['akad']} failed: expected {$expected}, got {$actual}"
            );
        }
    }

    /**
     * 7. Adversarial Challenge: Weighted Sum vs Arithmetic Mean Discrepancy.
     *
     * Scenario 1: Skewed low volume high conversion vs high volume zero conversion.
     * Month 1: 1 booking, 1 akad (100.0%)
     * Months 2-12: 100 bookings each (1100 total), 0 akad (0.0%)
     * Arithmetic mean = 100.0 / 12 = 8.3%
     * Weighted cohort sum = 1 / 1101 * 100 = 0.0908...% -> 0.1%
     */
    public function testAdversarialAnnualTotalWeightedVsArithmeticMeanScenarioOne(): void
    {
        $repoData = [
            [
                'tahun' => 2026,
                'bulan' => 1,
                'booking' => 1,
                'total_booking_cohort' => 1,
                'booking_converted_akad' => 1,
            ],
        ];

        for ($m = 2; $m <= 12; $m++) {
            $repoData[] = [
                'tahun' => 2026,
                'bulan' => $m,
                'booking' => 100,
                'total_booking_cohort' => 100,
                'booking_converted_akad' => 0,
            ];
        }

        $repository = $this->createMock(BookingAkadReportRepository::class);
        $repository->method('getMonthlyStatusTotals')->willReturn($repoData);

        $service = new BookingAkadReportService($repository);
        $result = $service->getSummary(13, 2026);

        // Arithmetic average would be ~8.3%
        $this->assertNotSame(8.3, $result['totals'][2026]['konversi']);
        // True weighted sum: 1 / 1101 = 0.09% -> 0.1%
        $this->assertSame(0.1, $result['totals'][2026]['konversi']);
        $this->assertSame(1101, $result['totals'][2026]['total_booking_cohort']);
        $this->assertSame(1, $result['totals'][2026]['booking_converted_akad']);
    }

    /**
     * 7B. Adversarial Challenge: Weighted Sum vs Arithmetic Mean Discrepancy.
     *
     * Scenario 2: High volume high conversion vs 11 months single booking zero conversion.
     * Month 1: 100 bookings, 90 akad (90.0%)
     * Months 2-12: 1 booking each (11 total), 0 akad (0.0%)
     * Arithmetic mean = 90.0 / 12 = 7.5%
     * Weighted cohort sum = 90 / 111 * 100 = 81.081...% -> 81.1%
     */
    public function testAdversarialAnnualTotalWeightedVsArithmeticMeanScenarioTwo(): void
    {
        $repoData = [
            [
                'tahun' => 2026,
                'bulan' => 1,
                'booking' => 100,
                'total_booking_cohort' => 100,
                'booking_converted_akad' => 90,
            ],
        ];

        for ($m = 2; $m <= 12; $m++) {
            $repoData[] = [
                'tahun' => 2026,
                'bulan' => $m,
                'booking' => 1,
                'total_booking_cohort' => 1,
                'booking_converted_akad' => 0,
            ];
        }

        $repository = $this->createMock(BookingAkadReportRepository::class);
        $repository->method('getMonthlyStatusTotals')->willReturn($repoData);

        $service = new BookingAkadReportService($repository);
        $result = $service->getSummary(13, 2026);

        // Arithmetic average would be 7.5%
        $this->assertNotSame(7.5, $result['totals'][2026]['konversi']);
        // True weighted sum: 90 / 111 = 81.08% -> 81.1%
        $this->assertSame(81.1, $result['totals'][2026]['konversi']);
        $this->assertSame(111, $result['totals'][2026]['total_booking_cohort']);
        $this->assertSame(90, $result['totals'][2026]['booking_converted_akad']);
    }

    /**
     * 8. Multi-Year Isolation:
     * Year 2025 (50% conversion) and Year 2026 (25% conversion) do not cross-contaminate.
     */
    public function testMultiYearCalculationIsolation(): void
    {
        $repoData = [
            ['tahun' => 2025, 'bulan' => 1, 'booking' => 10, 'total_booking_cohort' => 10, 'booking_converted_akad' => 5], // 50%
            ['tahun' => 2026, 'bulan' => 1, 'booking' => 20, 'total_booking_cohort' => 20, 'booking_converted_akad' => 5], // 25%
        ];

        $repository = $this->createMock(BookingAkadReportRepository::class);
        $repository->method('getMonthlyStatusTotals')->willReturn($repoData);

        $service = new BookingAkadReportService($repository);
        $result = $service->getSummary(13, 2026, 2025);

        $this->assertSame(25.0, $result['months'][0]['values'][2026]['konversi']);
        $this->assertSame(50.0, $result['months'][0]['values'][2025]['konversi']);

        $this->assertSame(25.0, $result['totals'][2026]['konversi']);
        $this->assertSame(50.0, $result['totals'][2025]['konversi']);
    }

    /**
     * 9. Date Formatting Oracle:
     * Verifies safe parsing of various valid, invalid, and edge-case date strings.
     */
    public function testDateFormattingOracle(): void
    {
        $service = new BookingAkadReportService($this->createMock(BookingAkadReportRepository::class));

        // Invalid or zero-dates must return '-'
        $this->assertSame('-', $service->formatDate(null));
        $this->assertSame('-', $service->formatDate(''));
        $this->assertSame('-', $service->formatDate('   '));
        $this->assertSame('-', $service->formatDate('0000-00-00'));
        $this->assertSame('-', $service->formatDate('0000-00-00 00:00:00'));
        $this->assertSame('-', $service->formatDate('1970-01-01'));
        $this->assertSame('-', $service->formatDate('1969-12-31'));
        $this->assertSame('-', $service->formatDate('invalid-date-string'));

        // Valid dates
        $this->assertSame('14-04-2025', $service->formatDate('2025-04-14'));
        $this->assertSame('29-02-2024', $service->formatDate('2024-02-29')); // Leap year
        $this->assertSame('31-12-2026', $service->formatDate('2026-12-31 23:59:59'));
    }

    /**
     * 10. Detail Row Resilience:
     * Extreme inputs with HTML entities, unicode, and null fields.
     */
    public function testDetailRowResilienceWithExtremeValues(): void
    {
        $repository = $this->createMock(BookingAkadReportRepository::class);
        $repository->method('getDetailRows')->willReturn([
            'recordsTotal' => 1,
            'recordsFiltered' => 1,
            'data' => [
                [
                    'id_mkdt' => '42', // String id coerced to int
                    'id_kavling' => '108',
                    'nama_jalan' => "Jalan <script>alert('xss')</script>",
                    'no_kavling' => "B-01/A & 'hook'",
                    'sales' => '  ', // Whitespace-only sales -> '-'
                    'nama_konsumen' => 'H. Muḥammad Ṣāliḥ 🏡',
                    'nama_tipe' => 'Type 45 "Deluxe"',
                    'is_subsidi' => '0',
                    'booking_tgl' => '2026-03-15',
                    'status_terakhir' => 'SP3K',
                    'status_terakhir_badge' => 'primary',
                    'catatan_mkdt' => '   ', // Whitespace-only notes -> '-'
                    'tanggal_status_terakhir' => '2026-03-20',
                ],
            ],
        ]);

        $service = new BookingAkadReportService($repository);
        $result = $service->getDetail(13, ['year' => 2026, 'month' => 3, 'category' => 'sp3k']);

        $row = $result['data'][0];
        $this->assertSame(42, $row['id_mkdt']);
        $this->assertSame(108, $row['id_kavling']);
        $this->assertSame('-', $row['kavling']['sales']);
        $this->assertSame('-', $row['catatan_mkdt']);
        $this->assertSame('15-03-2026', $row['tanggal_booking']);
        $this->assertSame('20-03-2026', $row['tanggal_status_terakhir']);
        $this->assertSame('SP3K', $row['status_terakhir']);
        $this->assertSame('primary', $row['status_terakhir_badge']);
        $this->assertFalse($row['konsumen']['is_subsidi']);
    }
}

<?php

namespace Tests\Database;

use App\Repositories\BookingAkadReportRepository;
use App\Services\BookingAkadReportService;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

final class BookingAkadReportProyek13MetricsTest extends CIUnitTestCase
{
    protected ?BaseConnection $liveDb = null;
    protected ?BookingAkadReportRepository $repository = null;
    protected ?BookingAkadReportService $service = null;

    protected function setUp(): void
    {
        parent::setUp();
        try {
            $this->liveDb = Database::connect('default', false);
            $this->liveDb->connect();
            $this->repository = new BookingAkadReportRepository($this->liveDb);
            $this->service = new BookingAkadReportService($this->repository);
        } catch (\Throwable $e) {
            $this->markTestSkipped('Local MySQL database is not accessible: ' . $e->getMessage());
        }
    }

    protected function tearDown(): void
    {
        if ($this->liveDb !== null) {
            $this->liveDb->close();
        }
        parent::tearDown();
    }

    public function testProyek13Year2026CohortConversionMetrics(): void
    {
        $idProyek = 13;
        $summary = $this->service->getSummary($idProyek, 2026);

        $total2026 = $summary['totals'][2026];
        $summaryMapTotal = $summary['summary'][2026]['total'];

        // Confirm 6 status partition for Year 2026 bookings:
        // 8 booking + 11 wawancara + 10 sp3k + 4 batal + 18 akad_indent + 18 akad_ajb = 69 total cohort
        $this->assertSame(8, $total2026['booking']);
        $this->assertSame(11, $total2026['wawancara']);
        $this->assertSame(10, $total2026['sp3k']);
        $this->assertSame(4, $total2026['batal']);
        $this->assertSame(18, $total2026['akad_indent']);
        $this->assertSame(18, $total2026['akad_ajb']);

        // Sum of all 6 statuses must equal the total cohort
        $sumOfSix = $total2026['booking'] + $total2026['wawancara'] + $total2026['sp3k'] + $total2026['batal'] + $total2026['akad_indent'] + $total2026['akad_ajb'];
        $this->assertSame(69, $sumOfSix);
        $this->assertSame(69, $total2026['total_booking_cohort']);

        // Converted to akad in 2026 cohort: 18 akad_indent + 18 akad_ajb = 36
        $this->assertSame(36, $total2026['booking_converted_akad']);

        // Check conversion: 36 / 69 * 100 = 52.1739...% -> 52.2%
        $this->assertSame(52.2, $total2026['konversi']);
        $this->assertSame(52.2, $summaryMapTotal['konversi']);

        // Check weighted sum vs naive arithmetic average:
        // Naive arithmetic average of monthly conversion percentages:
        $monthlyConversions = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthData = $summary['summary'][2026][$m];
            $monthlyConversions[] = $monthData['konversi'];
            $this->assertGreaterThanOrEqual(0.0, $monthData['konversi']);
            $this->assertLessThanOrEqual(100.0, $monthData['konversi']);
        }
        $arithmeticAverage = round(array_sum($monthlyConversions) / 12, 1);
        // Arithmetic average is (100 + 100 + 100 + 31.3 + 80 + 64.3 + 40 + 14.3 + 0 + 0 + 0 + 0) / 12 = 529.9 / 12 = 44.2%
        $this->assertSame(44.2, $arithmeticAverage);
        // True weighted cohort conversion is 52.2%, NOT 44.2%
        $this->assertNotSame($arithmeticAverage, $total2026['konversi']);
        $this->assertSame(52.2, $total2026['konversi']);
    }
}

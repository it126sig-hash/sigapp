<?php

namespace Tests\Database;

use App\Repositories\BookingAkadReportRepository;
use App\Services\BookingAkadReportService;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

/**
 * Class BookingAkadReportMatrixDetailCoherenceLiveTest
 *
 * Empirical verification of coherence between:
 * 1. getMonthlyStatusTotals (Matrix Summary)
 * 2. getDetailRows (Modal Detail Table)
 *
 * Checks that for every month and every status category in Proyek 13,
 * the count reported in the summary matrix equals EXACTLY recordsTotal
 * in the modal detail query.
 */
final class BookingAkadReportMatrixDetailCoherenceLiveTest extends CIUnitTestCase
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

    /**
     * Test Coherence across all years present in Proyek 13.
     */
    public function testSummaryCountsExactlyMatchDetailRecordsTotalForEveryMonthAndCategory(): void
    {
        $idProyek = 13;
        $bounds = $this->repository->getAvailableYearBounds($idProyek);
        $minYear = $bounds['min'] ?? 2024;
        $maxYear = $bounds['max'] ?? 2026;

        $categories = BookingAkadReportService::CATEGORIES; // ['booking', 'wawancara', 'sp3k', 'batal', 'akad_indent', 'akad_ajb']

        $totalChecked = 0;

        for ($year = $minYear; $year <= $maxYear; $year++) {
            $summaryResult = $this->service->getSummary($idProyek, $year);

            for ($month = 1; $month <= 12; $month++) {
                $monthValues = $summaryResult['summary'][$year][$month];

                foreach ($categories as $cat) {
                    $summaryCount = (int) ($monthValues[$cat] ?? 0);

                    // Call detail query
                    $detailResult = $this->service->getDetail($idProyek, [
                        'year' => $year,
                        'month' => $month,
                        'category' => $cat,
                        'draw' => 1,
                        'start' => 0,
                        'length' => 10,
                    ]);

                    $detailRecordsTotal = (int) $detailResult['recordsTotal'];

                    $this->assertSame(
                        $summaryCount,
                        $detailRecordsTotal,
                        "Mismatch in Year {$year}, Month {$month}, Category {$cat}: Summary says {$summaryCount}, Detail query says {$detailRecordsTotal}"
                    );
                    $totalChecked++;
                }
            }
        }

        $this->assertGreaterThan(0, $totalChecked);
    }
}

<?php

namespace Tests\Unit;

use App\Repositories\BookingAkadReportRepository;
use App\Services\BookingAkadReportService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * ChallengerEmpiricalVerificationTest
 *
 * Empirical verification harness constructed by Challenger M1_2.
 * Directly stress-tests edge cases, boundary conditions, zero-dates,
 * null sales, precedence hierarchy (including record 349), and catatan fallback.
 */
final class ChallengerEmpiricalVerificationTest extends CIUnitTestCase
{
    private BookingAkadReportService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new BookingAkadReportService();
    }

    /**
     * CHALLENGE TASK 1.1: Dirty zero-dates formatting.
     * All forms of zero-dates, empty dates, and epoch boundaries must format to '-'
     * with ZERO PHP warnings, notices, or deprecation notices.
     */
    public function testDirtyZeroDatesFormatToHyphenWithZeroWarnings(): void
    {
        $caughtErrors = [];
        set_error_handler(function ($errno, $errstr, $errfile, $errline) use (&$caughtErrors) {
            $caughtErrors[] = [
                'errno'   => $errno,
                'errstr'  => $errstr,
                'errfile' => $errfile,
                'errline' => $errline,
            ];
            return true; // Don't execute PHP internal error handler
        });

        $testDates = [
            '0000-00-00',
            '0000-00-00 00:00:00',
            '0000-00-00 12:34:56',
            '0000-00-00 23:59:59',
            '0000-01-01',
            '0001-01-01',
            '1970-01-01',
            '1970-01-01 00:00:00',
            '1969-12-31',
            null,
            '',
            '   ',
            'invalid-date-string',
            '0000/00/00',
        ];

        try {
            foreach ($testDates as $date) {
                $formatted = $this->service->formatDate($date);
                $this->assertSame('-', $formatted, "Date '{$date}' must format to '-'");
            }

            // Also test valid date formats correctly
            $this->assertSame('14-04-2025', $this->service->formatDate('2025-04-14'));
            $this->assertSame('09-10-2024', $this->service->formatDate('2024-10-09 10:15:00'));
        } finally {
            restore_error_handler();
        }

        $this->assertEmpty($caughtErrors, 'Zero PHP warnings/notices allowed during zero-date formatting. Caught: ' . json_encode($caughtErrors));
    }

    /**
     * CHALLENGE TASK 1.2: Null sales records formatting.
     * When sales is null, empty string, or whitespace, getDetail() must format
     * kavling.sales to '-' with ZERO PHP warnings or notices.
     */
    public function testNullSalesRecordsFormatToHyphenWithZeroWarnings(): void
    {
        $caughtErrors = [];
        set_error_handler(function ($errno, $errstr, $errfile, $errline) use (&$caughtErrors) {
            $caughtErrors[] = [
                'errno'   => $errno,
                'errstr'  => $errstr,
                'errfile' => $errfile,
                'errline' => $errline,
            ];
            return true;
        });

        $mockRepo = $this->createMock(BookingAkadReportRepository::class);
        $mockRepo->method('getDetailRows')->willReturn([
            'recordsTotal'    => 4,
            'recordsFiltered' => 4,
            'data'            => [
                ['id_mkdt' => 101, 'sales' => null, 'booking_tgl' => '2026-04-01'],
                ['id_mkdt' => 102, 'sales' => '', 'booking_tgl' => '2026-04-01'],
                ['id_mkdt' => 103, 'sales' => '   ', 'booking_tgl' => '2026-04-01'],
                ['id_mkdt' => 104, 'sales' => 'Budi Santoso', 'booking_tgl' => '2026-04-01'],
            ],
        ]);

        $serviceWithMock = new BookingAkadReportService($mockRepo);

        try {
            $res = $serviceWithMock->getDetail(13, ['year' => 2026, 'month' => 4, 'category' => 'booking']);
            $rows = $res['data'];

            $this->assertSame('-', $rows[0]['kavling']['sales']);
            $this->assertSame('-', $rows[0]['sales']);

            $this->assertSame('-', $rows[1]['kavling']['sales']);
            $this->assertSame('-', $rows[1]['sales']);

            $this->assertSame('-', $rows[2]['kavling']['sales']);
            $this->assertSame('-', $rows[2]['sales']);

            $this->assertSame('Budi Santoso', $rows[3]['kavling']['sales']);
            $this->assertSame('Budi Santoso', $rows[3]['sales']);
        } finally {
            restore_error_handler();
        }

        $this->assertEmpty($caughtErrors, 'Zero PHP warnings/notices allowed during null sales formatting. Caught: ' . json_encode($caughtErrors));
    }

    /**
     * CHALLENGE TASK 1.3: Status precedence determinism (including record 349).
     * Record 349 in live DB has both Indent (akad_indent=1, is_ajb='PPJB', akad_indent_tgl='2026-07-29')
     * and AJB (akad=1, akad_tgl='2026-11-02').
     * It must deterministically resolve to 'Akad AJB'.
     */
    public function testStatusPrecedenceResolvesDeterministicallyForRecord349(): void
    {
        $db = \Config\Database::connect('default', false);
        $raw349 = $db->table('mkdt')->where('id_mkdt', 349)->get()->getRowArray();

        $this->assertNotNull($raw349, 'Record id_mkdt 349 must exist in database.');
        $this->assertSame('1', (string) $raw349['akad']);
        $this->assertSame('1', (string) $raw349['akad_indent']);
        $this->assertSame('PPJB', $raw349['is_ajb']);

        // Test PHP Service resolution
        $resolvedStatus = $this->service->resolveStatusTerakhir($raw349);
        $this->assertSame('Akad AJB', $resolvedStatus, 'Record 349 with both Indent and AJB must resolve to Akad AJB.');

        $resolvedBadge = $this->service->resolveStatusBadge($resolvedStatus);
        $this->assertSame('success', $resolvedBadge);

        $resolvedDate = $this->service->resolveTanggalStatusTerakhir($raw349, $resolvedStatus);
        $this->assertSame('2026-11-02', $resolvedDate, 'Status date must be the Akad AJB date (2026-11-02), not Indent date.');

        // Test Full Repository Detail Query for Record 349's project
        // First find project for kavling 265
        $kavling = $db->table('kavling k')
            ->join('jalan j', 'j.id_jalan = k.id_jalan')
            ->join('cluster cl', 'cl.id_cluster = j.id_cluster')
            ->where('k.id_kavling', $raw349['id_kavling'])
            ->select('cl.id_proyek')
            ->get()
            ->getRowArray();
        $this->assertNotNull($kavling, 'Kavling for record 349 must exist.');
        $idProyek = (int) $kavling['id_proyek'];

        $repo = new BookingAkadReportRepository($db);
        $serviceLive = new BookingAkadReportService($repo);

        // Booking date of 349 is 2026-05-13 (Month 5, Year 2026)
        // Detail drilldown under 'akad_ajb' must include record 349
        $detailAjb = $serviceLive->getDetail($idProyek, [
            'year'     => 2026,
            'month'    => 5,
            'category' => 'akad_ajb',
        ]);
        $foundInAjb = false;
        foreach ($detailAjb['data'] as $row) {
            if ($row['id_mkdt'] === 349) {
                $foundInAjb = true;
                $this->assertSame('Akad AJB', $row['status_terakhir']);
                $this->assertSame('success', $row['status_terakhir_badge']);
                $this->assertSame('02-11-2026', $row['tanggal_status_terakhir']);
                $this->assertSame('AKAD', $row['catatan_mkdt']);
            }
        }
        $this->assertTrue($foundInAjb, 'Record 349 must be found in akad_ajb category drilldown.');

        // Detail drilldown under 'akad_indent' must NOT include record 349 because it has advanced to AJB
        $detailIndent = $serviceLive->getDetail($idProyek, [
            'year'     => 2026,
            'month'    => 5,
            'category' => 'akad_indent',
        ]);
        $foundInIndent = false;
        foreach ($detailIndent['data'] as $row) {
            if ($row['id_mkdt'] === 349) {
                $foundInIndent = true;
            }
        }
        $this->assertFalse($foundInIndent, 'Record 349 must NOT appear in akad_indent category since AJB supersedes Indent.');
    }

    /**
     * CHALLENGE TASK 1.3b: Complete 6-tier precedence hierarchy stress test.
     * Hierarchy: Batal > Akad AJB > Akad Indent > SP3K > Wawancara > Booking
     */
    public function testSixTierPrecedenceHierarchyStressTest(): void
    {
        // 1. Batal beats everything (even if all other flags are true)
        $rowBatal = [
            'is_batal'     => 1,
            'status_mkdt'  => 'Batal',
            'akad'         => 1,
            'akad_indent'  => 1,
            'is_ajb'       => 'PPJB',
            'sp3k'         => 1,
            'wawancara'    => 1,
            'booking_tgl'  => '2026-01-01',
        ];
        $this->assertSame('Batal', $this->service->resolveStatusTerakhir($rowBatal));

        // 2. Akad AJB beats Indent, SP3K, Wawancara, Booking
        $rowAjb = [
            'is_batal'     => 0,
            'status_mkdt'  => 'Akad',
            'akad'         => 1,
            'akad_indent'  => 1,
            'is_ajb'       => 'PPJB',
            'sp3k'         => 1,
            'wawancara'    => 1,
            'booking_tgl'  => '2026-01-01',
        ];
        $this->assertSame('Akad AJB', $this->service->resolveStatusTerakhir($rowAjb));

        // 3. Akad Indent beats SP3K, Wawancara, Booking
        $rowIndent = [
            'is_batal'     => 0,
            'status_mkdt'  => 'Indent',
            'akad'         => 0,
            'akad_indent'  => 1,
            'is_ajb'       => 'PPJB',
            'sp3k'         => 1,
            'wawancara'    => 1,
            'booking_tgl'  => '2026-01-01',
        ];
        $this->assertSame('Akad Indent', $this->service->resolveStatusTerakhir($rowIndent));

        // 4. SP3K beats Wawancara, Booking
        $rowSp3k = [
            'is_batal'     => 0,
            'status_mkdt'  => 'SP3K',
            'akad'         => 0,
            'akad_indent'  => 0,
            'is_ajb'       => '',
            'sp3k'         => 1,
            'sp3k_tgl'     => '2026-03-01',
            'wawancara'    => 1,
            'booking_tgl'  => '2026-01-01',
        ];
        $this->assertSame('SP3K', $this->service->resolveStatusTerakhir($rowSp3k));

        // 5. Wawancara beats Booking
        $rowWawancara = [
            'is_batal'     => 0,
            'status_mkdt'  => 'Wawancara',
            'akad'         => 0,
            'akad_indent'  => 0,
            'is_ajb'       => '',
            'sp3k'         => 0,
            'wawancara'    => 1,
            'wawancara_tgl'=> '2026-02-01',
            'booking_tgl'  => '2026-01-01',
        ];
        $this->assertSame('Wawancara', $this->service->resolveStatusTerakhir($rowWawancara));

        // 6. Booking fallback
        $rowBooking = [
            'is_batal'     => 0,
            'status_mkdt'  => 'Booking',
            'akad'         => 0,
            'akad_indent'  => 0,
            'is_ajb'       => '',
            'sp3k'         => 0,
            'wawancara'    => 0,
            'booking_tgl'  => '2026-01-01',
        ];
        $this->assertSame('Booking', $this->service->resolveStatusTerakhir($rowBooking));
    }

    /**
     * CHALLENGE TASK 1.4: Missing catatan MKDT fallback sequence.
     * Hierarchy: keterangan_batal -> keterangan -> keterangan_status -> '-'
     */
    public function testMissingCatatanMkdtFallbackSequence(): void
    {
        // 1. keterangan_batal takes precedence
        $r1 = [
            'keterangan_batal'  => 'Batal karena BI Checking',
            'keterangan'        => 'Keterangan umum',
            'keterangan_status' => 'Status berkas',
        ];
        $this->assertSame('Batal karena BI Checking', $this->service->resolveCatatanMkdt($r1));

        // 2. keterangan when keterangan_batal is empty / whitespace
        $r2 = [
            'keterangan_batal'  => '   ',
            'keterangan'        => 'Berkas sudah lengkap di bank',
            'keterangan_status' => 'Status berkas',
        ];
        $this->assertSame('Berkas sudah lengkap di bank', $this->service->resolveCatatanMkdt($r2));

        // 3. keterangan_status when keterangan_batal & keterangan are empty
        $r3 = [
            'keterangan_batal'  => null,
            'keterangan'        => '',
            'keterangan_status' => 'Wawancara lolos',
        ];
        $this->assertSame('Wawancara lolos', $this->service->resolveCatatanMkdt($r3));

        // 4. Fallback to '-' when all 3 are null or empty
        $r4 = [
            'keterangan_batal'  => null,
            'keterangan'        => null,
            'keterangan_status' => null,
        ];
        $this->assertSame('-', $this->service->resolveCatatanMkdt($r4));

        // 5. Fallback to '-' when all 3 are only whitespace
        $r5 = [
            'keterangan_batal'  => "  \t  ",
            'keterangan'        => "   \n ",
            'keterangan_status' => "     ",
        ];
        $this->assertSame('-', $this->service->resolveCatatanMkdt($r5));

        // 6. '0' is a valid non-empty string in PHP and must NOT be treated as empty
        $r6 = [
            'keterangan_batal'  => '',
            'keterangan'        => '',
            'keterangan_status' => '0',
        ];
        $this->assertSame('0', $this->service->resolveCatatanMkdt($r6));
    }

    /**
     * CHALLENGE TASK 1.5: Empty year and mathematical bounding [0.0, 100.0].
     */
    public function testEmptyYearAndConversionMathematicalBounding(): void
    {
        $db = \Config\Database::connect('default', false);
        $repo = new BookingAkadReportRepository($db);
        $service = new BookingAkadReportService($repo);

        // Year 2010 has 0 transactions in Proyek 13
        $summary2010 = $service->getSummary(13, 2010);
        $this->assertIsArray($summary2010);
        $this->assertCount(12, $summary2010['months']);
        $this->assertSame(0, $summary2010['totals'][2010]['booking']);
        $this->assertSame(0.0, $summary2010['totals'][2010]['konversi']);

        // Test bounding when mock repository returns anomalous data (e.g. akad > booking)
        $mockRepo = $this->createMock(BookingAkadReportRepository::class);
        $mockRepo->method('getMonthlyStatusTotals')->willReturn([
            [
                'tahun'                  => 2026,
                'bulan'                  => 1,
                'booking'                => 5,
                'wawancara'              => 0,
                'sp3k'                   => 0,
                'batal'                  => 0,
                'akad_indent'            => 10,
                'akad_ajb'               => 10,
                'total_booking_cohort'   => 5,
                'booking_converted_akad' => 20, // 20 / 5 = 400% anomaly
            ],
        ]);

        $serviceMock = new BookingAkadReportService($mockRepo);
        $res = $serviceMock->getSummary(13, 2026);
        $jan = $res['months'][0]['values'][2026];

        $this->assertSame(100.0, $jan['konversi'], 'Conversion must be capped at 100.0% even under severe data anomaly.');
        $this->assertSame(100.0, $res['totals'][2026]['konversi'], 'Annual total conversion must also be capped at 100.0%.');
    }

    /**
     * CHALLENGE TASK 1.6: DataTables search and filter query in Repository.
     * Verifies that getDetailRows handles search value trimming and column ordering safely.
     */
    public function testDataTablesSearchAndOrderQueryExecution(): void
    {
        $db = \Config\Database::connect('default', false);
        $repo = new BookingAkadReportRepository($db);

        // Test search filter on Proyek 13
        $resultSearch = $repo->getDetailRows(13, 2026, 5, 'akad_ajb', [
            'draw'      => 1,
            'start'     => 0,
            'length'    => 10,
            'search'    => ['value' => 'AKAD'],
            'order_by'  => 'booking_akad.booking_tgl',
            'order_dir' => 'DESC',
        ]);

        $this->assertIsArray($resultSearch);
        $this->assertArrayHasKey('recordsTotal', $resultSearch);
        $this->assertArrayHasKey('recordsFiltered', $resultSearch);
        $this->assertArrayHasKey('data', $resultSearch);
        $this->assertGreaterThan(0, $resultSearch['recordsFiltered']);
    }
}

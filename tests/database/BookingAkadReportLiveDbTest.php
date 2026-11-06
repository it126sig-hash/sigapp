<?php

namespace Tests\Database;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

/**
 * Class BookingAkadReportLiveDbTest
 *
 * Tier 4: Real-World Application Scenario Tests against live Project 13
 * ("Sanggar Indah Palastri") in the local MySQL database.
 * Authoritative source: ORIGINAL_REQUEST.md (§Acceptance Criteria & Verification Resources)
 * and analysis.md (§3 & §5.2).
 */
final class BookingAkadReportLiveDbTest extends CIUnitTestCase
{
    protected ?BaseConnection $liveDb = null;

    protected function setUp(): void
    {
        parent::setUp();

        try {
            $this->liveDb = Database::connect('default', false);
            // Test connection
            $this->liveDb->connect();
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
     * TC-R01: Proyek 13 exists, is named "SANGGAR INDAH PALASTRI", and contains 242 transactions.
     */
    public function testProyek13ExistsAndContains242Transactions(): void
    {
        $project = $this->liveDb->table('proyek')
            ->where('id_proyek', 13)
            ->get()
            ->getRowArray();

        $this->assertNotNull($project, 'Proyek 13 must exist in database.');
        $this->assertSame('SANGGAR INDAH PALASTRI', strtoupper((string) ($project['nama_proyek'] ?? '')));
        $this->assertSame('SIP', (string) ($project['kode_keuangan'] ?? ''));

        $totalMkdt = $this->liveDb->table('mkdt m')
            ->join('kavling k', 'k.id_kavling = m.id_kavling')
            ->join('jalan j', 'j.id_jalan = k.id_jalan')
            ->join('cluster cl', 'cl.id_cluster = j.id_cluster')
            ->where('cl.id_proyek', 13)
            ->countAllResults();

        $this->assertSame(242, $totalMkdt, 'Proyek 13 must contain exactly 242 MKDT transactions.');
    }

    /**
     * TC-R02: 100% Unique Transaction Code (uniq_id) Integrity.
     * All 242 records must possess unique, non-null, non-empty uniq_id.
     */
    public function testProyek13HasOneHundredPercentUniqIdIntegrity(): void
    {
        $rows = $this->liveDb->table('mkdt m')
            ->select('m.id_mkdt, m.uniq_id')
            ->join('kavling k', 'k.id_kavling = m.id_kavling')
            ->join('jalan j', 'j.id_jalan = k.id_jalan')
            ->join('cluster cl', 'cl.id_cluster = j.id_cluster')
            ->where('cl.id_proyek', 13)
            ->get()
            ->getResultArray();

        $this->assertCount(242, $rows);

        $uniqIds = [];
        foreach ($rows as $row) {
            $uniqId = trim((string) ($row['uniq_id'] ?? ''));
            $this->assertNotEmpty($uniqId, "Record id_mkdt {$row['id_mkdt']} has empty uniq_id.");
            $uniqIds[] = $uniqId;
        }

        $distinctCount = count(array_unique($uniqIds));
        $this->assertSame(242, $distinctCount, 'All 242 transactions in Proyek 13 must have distinct uniq_id.');
    }

    /**
     * TC-R03: Complete 6-Status Partition Verification.
     * The 242 records must partition deterministically:
     * 171 Akad AJB + 18 Akad Indent + 15 SP3K + 11 Wawancara + 18 Booking + 9 Batal = 242.
     */
    public function testProyek13TransactionsPartitionExactlyIntoSixStatuses(): void
    {
        $builder = $this->liveDb->table('mkdt m')
            ->select('m.id_mkdt, m.status_mkdt, m.is_batal, m.akad, m.akad_tgl, m.is_ajb, m.akad_indent, m.akad_indent_tgl, m.sp3k, m.sp3k_tgl, m.sp3k_no, m.wawancara, m.wawancara_tgl, m.booking_tgl')
            ->join('kavling k', 'k.id_kavling = m.id_kavling')
            ->join('jalan j', 'j.id_jalan = k.id_jalan')
            ->join('cluster cl', 'cl.id_cluster = j.id_cluster')
            ->where('cl.id_proyek', 13);

        $rows = $builder->get()->getResultArray();
        $this->assertCount(242, $rows);

        $counts = [
            'batal' => 0,
            'akad_ajb' => 0,
            'akad_indent' => 0,
            'sp3k' => 0,
            'wawancara' => 0,
            'booking' => 0,
        ];

        foreach ($rows as $r) {
            $sp3kNo = trim((string) ($r['sp3k_no'] ?? ''));
            $isBatal = ((int) ($r['is_batal'] ?? 0) === 1) || (($r['status_mkdt'] ?? '') === 'Batal');
            $isAkadAjb = ! $isBatal && (((int) ($r['akad'] ?? 0) === 1) || ($r['akad_tgl'] !== null && $r['akad_tgl'] > '1970-01-01'));
            $isAkadIndent = ! $isBatal && ! $isAkadAjb && (((int) ($r['akad_indent'] ?? 0) === 1) || ($r['akad_indent_tgl'] !== null && $r['akad_indent_tgl'] > '1970-01-01'));
            $hasSp3k = ! $isBatal && ! $isAkadAjb && ! $isAkadIndent && (((int) ($r['sp3k'] ?? 0) === 1) || ($r['sp3k_tgl'] !== null && $r['sp3k_tgl'] > '1970-01-01') || ($sp3kNo !== '' && $sp3kNo !== '-'));
            $hasWawancara = ! $isBatal && ! $isAkadAjb && ! $isAkadIndent && ! $hasSp3k && (((int) ($r['wawancara'] ?? 0) === 1) || ($r['wawancara_tgl'] !== null && $r['wawancara_tgl'] > '1970-01-01'));

            if ($isBatal) {
                $counts['batal']++;
            } elseif ($isAkadAjb) {
                $counts['akad_ajb']++;
            } elseif ($isAkadIndent) {
                $counts['akad_indent']++;
            } elseif ($hasSp3k) {
                $counts['sp3k']++;
            } elseif ($hasWawancara) {
                $counts['wawancara']++;
            } else {
                $counts['booking']++;
            }
        }

        $this->assertSame(9, $counts['batal'], 'Batal count must be 9.');
        $this->assertSame(171, $counts['akad_ajb'], 'Akad AJB count must be 171.');
        $this->assertSame(18, $counts['akad_indent'], 'Akad Indent count must be 18.');
        $this->assertSame(15, $counts['sp3k'], 'SP3K count must be 15.');
        $this->assertSame(11, $counts['wawancara'], 'Wawancara count must be 11.');
        $this->assertSame(18, $counts['booking'], 'Booking count must be 18.');

        $sum = array_sum($counts);
        $this->assertSame(242, $sum, 'The sum of all 6 status counts must equal exactly 242.');
    }

    /**
     * TC-R04: Real Lifecycle Transition Trace (id_mkdt = 147).
     * Proves concrete real-world transition: Booking -> Wawancara -> SP3K -> Akad Indent -> Akad AJB.
     */
    public function testRealLifecycleTransitionTraceForIdMkdt147(): void
    {
        $row = $this->liveDb->table('mkdt m')
            ->select('m.id_mkdt, m.uniq_id, m.booking_tgl, m.wawancara_tgl, m.sp3k_tgl, m.akad_indent_tgl, m.akad_tgl, m.is_ajb, m.akad, m.akad_indent')
            ->where('m.id_mkdt', 147)
            ->get()
            ->getRowArray();

        $this->assertNotNull($row);
        $this->assertSame('67d7dcbd8200f7.27535517', $row['uniq_id']);
        $this->assertSame('2024-10-09', $row['booking_tgl']);
        $this->assertSame('2024-10-09', $row['wawancara_tgl']);
        $this->assertSame('2025-01-31', $row['sp3k_tgl']);
        $this->assertSame('2025-02-14', $row['akad_indent_tgl']);
        $this->assertSame('2025-04-14', $row['akad_tgl']);
        $this->assertSame('AJB', $row['is_ajb']);
        $this->assertSame('1', (string) $row['akad']);
    }

    /**
     * TC-R05: Dirty Zero-Date Records Handled Safely Without MySQL Warnings.
     * Guards with > '1970-01-01' prevent error 1525 and year 0 in MySQL strict mode.
     */
    public function testZeroDateRecordsInProyek13AreFilteredSafely(): void
    {
        // Count records containing '0000-00-00'
        $zeroAkad = $this->liveDb->table('mkdt m')
            ->join('kavling k', 'k.id_kavling = m.id_kavling')
            ->join('jalan j', 'j.id_jalan = k.id_jalan')
            ->join('cluster cl', 'cl.id_cluster = j.id_cluster')
            ->where('cl.id_proyek', 13)
            ->where("CAST(m.akad_tgl AS CHAR) LIKE '0000%'")
            ->countAllResults();

        $this->assertGreaterThan(0, $zeroAkad, 'Proyek 13 has known zero-date akad records to test against.');

        // Verify that guarding with > '1970-01-01' eliminates all zero-dates cleanly
        $cleanAkadCount = $this->liveDb->table('mkdt m')
            ->join('kavling k', 'k.id_kavling = m.id_kavling')
            ->join('jalan j', 'j.id_jalan = k.id_jalan')
            ->join('cluster cl', 'cl.id_cluster = j.id_cluster')
            ->where('cl.id_proyek', 13)
            ->where('m.akad_tgl >', '1970-01-01')
            ->where("CAST(m.akad_tgl AS CHAR) LIKE '0000%'")
            ->countAllResults();

        $this->assertSame(0, $cleanAkadCount, 'Guarding with > 1970-01-01 must exclude all 0000-00-00 dates.');
    }

    /**
     * TC-R06: Modal Detail Query on Proyek 13 produces clean 7-column data structure.
     */
    public function testModalDetailQueryOnProyek13ProducesConsistentData(): void
    {
        $rows = $this->liveDb->table('mkdt m')
            ->select('m.id_mkdt, m.id_kavling, m.uniq_id, m.booking_tgl')
            ->select("TRIM(CONCAT(COALESCE(j.nama_jalan, ''), ' No. ', COALESCE(k.no_kavling, '-'))) AS kavling_label", false)
            ->select("COALESCE(c.sales, '-') AS sales_nama", false)
            ->select("COALESCE(c.nama_konsumen, '-') AS nama_konsumen", false)
            ->select("COALESCE(hj.id_tipe, t.tipe_rumah, '-') AS tipe_pricelist", false)
            ->select("CASE WHEN COALESCE(m.is_subsidi, 1) = 1 THEN 'Subsidi' ELSE 'Komersil' END AS badge_subsidi", false)
            ->join('kavling k', 'k.id_kavling = m.id_kavling')
            ->join('jalan j', 'j.id_jalan = k.id_jalan')
            ->join('cluster cl', 'cl.id_cluster = j.id_cluster')
            ->join('konsumen c', 'c.id_konsumen = m.id_konsumen', 'left')
            ->join('hargajual hj', 'hj.id = m.id_hargajual', 'left')
            ->join('tipe t', 't.id_tipe = k.id_tipe', 'left')
            ->where('cl.id_proyek', 13)
            ->limit(10)
            ->get()
            ->getResultArray();

        $this->assertNotEmpty($rows);
        foreach ($rows as $r) {
            $this->assertNotEmpty($r['id_mkdt']);
            $this->assertNotEmpty($r['id_kavling']);
            $this->assertNotEmpty($r['uniq_id']);
            $this->assertNotEmpty($r['kavling_label']);
            $this->assertNotEmpty($r['nama_konsumen']);
            $this->assertContains($r['badge_subsidi'], ['Subsidi', 'Komersil']);
        }
    }

    /**
     * TC-R07: Module documentation sync check via spark module:sync MKDT --check-only.
     */
    public function testDocumentationSyncCheckOnlyPasses(): void
    {
        $output = [];
        $exitCode = 0;
        exec('php spark module:sync MKDT --check-only 2>&1', $output, $exitCode);

        $outputText = implode("\n", $output);
        $this->assertSame(0, $exitCode, "php spark module:sync MKDT --check-only must exit with 0. Output: {$outputText}");
        $this->assertStringContainsString('MKDT: MODULE.md ada', $outputText);
    }

    /**
     * TC-R08: Repository getAvailableYears(13) and Matrix Summary Cell Count matches Modal Detail recordsTotal.
     */
    public function testMatrixSummaryCountMatchesModalDetailRecordsTotalInProyek13(): void
    {
        $repo = new \App\Repositories\BookingAkadReportRepository($this->liveDb);
        $years = $repo->getAvailableYears(13);

        $this->assertIsArray($years);
        $this->assertNotEmpty($years);

        $categories = ['booking', 'wawancara', 'sp3k', 'batal', 'akad_indent', 'akad_ajb'];

        foreach ($years as $year) {
            $summary = $repo->getMonthlyStatusTotals(13, [$year]);
            foreach ($summary as $row) {
                $month = (int) $row['bulan'];
                foreach ($categories as $cat) {
                    $expectedCount = (int) ($row[$cat] ?? 0);
                    if ($expectedCount > 0) {
                        $detail = $repo->getDetailRows(13, $year, $month, $cat);
                        $this->assertSame(
                            $expectedCount,
                            $detail['recordsTotal'],
                            "Mismatch in {$year}-{$month} for category {$cat}: matrix says {$expectedCount}, detail returned {$detail['recordsTotal']}"
                        );
                    }
                }
            }
        }
    }

    /**
     * TC-R09: All 5 column calculation rules across available years match exact DB condition totals without dropping zero-date flagged rows.
     */
    public function testFiveColumnRulesTotalInProyek13MatchesDatabaseConditionCounts(): void
    {
        $repo = new \App\Repositories\BookingAkadReportRepository($this->liveDb);
        $years = $repo->getAvailableYears(13);
        $summaryRows = $repo->getMonthlyStatusTotals(13, $years);

        $matrixTotals = [
            'booking' => 0,
            'wawancara' => 0,
            'sp3k' => 0,
            'akad_indent' => 0,
            'akad_ajb' => 0,
        ];

        foreach ($summaryRows as $row) {
            foreach (array_keys($matrixTotals) as $col) {
                $matrixTotals[$col] += (int) ($row[$col] ?? 0);
            }
        }

        $baseQuery = fn () => $this->liveDb->table('mkdt m')
            ->join('kavling k', 'k.id_kavling = m.id_kavling')
            ->join('jalan j', 'j.id_jalan = k.id_jalan')
            ->join('cluster cl', 'cl.id_cluster = j.id_cluster')
            ->where('cl.id_proyek', 13);

        $expectedBooking = $baseQuery()
            ->where("m.booking_tgl IS NOT NULL AND m.booking_tgl > '1970-01-01'", null, false)
            ->countAllResults();
        $expectedWawancara = $baseQuery()
            ->where("(COALESCE(m.wawancara, 0) = 1 OR (m.wawancara_tgl IS NOT NULL AND m.wawancara_tgl > '1970-01-01'))", null, false)
            ->countAllResults();
        $expectedSp3k = $baseQuery()
            ->where("(COALESCE(m.sp3k, 0) = 1 OR (m.sp3k_tgl IS NOT NULL AND m.sp3k_tgl > '1970-01-01') OR (m.sp3k_no IS NOT NULL AND TRIM(m.sp3k_no) != '' AND TRIM(m.sp3k_no) != '-'))", null, false)
            ->countAllResults();
        $expectedIndent = $baseQuery()
            ->where("(COALESCE(m.akad_indent, 0) = 1 OR (m.akad_indent_tgl IS NOT NULL AND m.akad_indent_tgl > '1970-01-01'))", null, false)
            ->countAllResults();
        $expectedAjb = $baseQuery()
            ->where("(COALESCE(m.akad, 0) = 1 OR (m.akad_tgl IS NOT NULL AND m.akad_tgl > '1970-01-01'))", null, false)
            ->countAllResults();

        $this->assertSame($expectedBooking, $matrixTotals['booking'], 'Total booking across years must match booking_tgl condition.');
        $this->assertSame($expectedWawancara, $matrixTotals['wawancara'], 'Total wawancara across years must match wawancara/wawancara_tgl condition.');
        $this->assertSame($expectedSp3k, $matrixTotals['sp3k'], 'Total sp3k across years must match sp3k/sp3k_tgl/sp3k_no condition.');
        $this->assertSame($expectedIndent, $matrixTotals['akad_indent'], 'Total akad_indent across years must match akad_indent/akad_indent_tgl condition.');
        $this->assertSame($expectedAjb, $matrixTotals['akad_ajb'], 'Total akad_ajb across years must match akad/akad_tgl condition.');
    }
}

<?php

require dirname(__DIR__, 3) . '/vendor/codeigniter4/framework/system/Test/bootstrap.php';

use App\Repositories\BookingAkadReportRepository;
use App\Services\BookingAkadReportService;

$db = \Config\Database::connect('default', false);
echo "=== INDEPENDENT LIVE DATABASE VERIFICATION ===\n";

// 1. Target project check
$proyek = $db->table('proyek')->where('id_proyek', 13)->get()->getRow();
echo "Target Proyek (ID 13): " . ($proyek ? $proyek->nama_proyek : 'NOT FOUND') . "\n";
if (!$proyek) {
    exit(1);
}

// 2. Count raw MKDT transactions in Proyek 13
$rawCount = $db->table('mkdt m')
    ->join('kavling k', 'k.id_kavling = m.id_kavling')
    ->join('jalan j', 'j.id_jalan = k.id_jalan')
    ->join('cluster cl', 'cl.id_cluster = j.id_cluster')
    ->where('cl.id_proyek', 13)
    ->countAllResults();
echo "Total raw MKDT transactions for Proyek 13: $rawCount\n";

// 3. Check distinct uniq_id
$distinctUniqId = $db->table('mkdt m')
    ->join('kavling k', 'k.id_kavling = m.id_kavling')
    ->join('jalan j', 'j.id_jalan = k.id_jalan')
    ->join('cluster cl', 'cl.id_cluster = j.id_cluster')
    ->where('cl.id_proyek', 13)
    ->select('COUNT(DISTINCT m.uniq_id) as total')
    ->get()
    ->getRow()
    ->total;
echo "Distinct uniq_id: $distinctUniqId\n";

// 4. Raw partition query
$partitionQuery = $db->table('mkdt m')
    ->join('kavling k', 'k.id_kavling = m.id_kavling')
    ->join('jalan j', 'j.id_jalan = k.id_jalan')
    ->join('cluster cl', 'cl.id_cluster = j.id_cluster')
    ->where('cl.id_proyek', 13)
    ->select("SUM(CASE WHEN (COALESCE(m.is_batal, 0) = 1 OR COALESCE(m.status_mkdt, '') = 'Batal') THEN 1 ELSE 0 END) AS batal", false)
    ->select("SUM(CASE WHEN (COALESCE(m.is_batal, 0) = 0 AND COALESCE(m.status_mkdt, '') != 'Batal') AND COALESCE(m.akad, 0) = 1 THEN 1 ELSE 0 END) AS akad_ajb", false)
    ->select("SUM(CASE WHEN (COALESCE(m.is_batal, 0) = 0 AND COALESCE(m.status_mkdt, '') != 'Batal') AND COALESCE(m.akad, 0) = 0 AND (COALESCE(m.akad_indent, 0) = 1 OR COALESCE(m.is_ajb, '') = 'PPJB') THEN 1 ELSE 0 END) AS akad_indent", false)
    ->select("SUM(CASE WHEN (COALESCE(m.is_batal, 0) = 0 AND COALESCE(m.status_mkdt, '') != 'Batal') AND COALESCE(m.akad, 0) = 0 AND (COALESCE(m.akad_indent, 0) = 0 AND COALESCE(m.is_ajb, '') != 'PPJB') AND (COALESCE(m.sp3k, 0) = 1 OR (m.sp3k_tgl IS NOT NULL AND m.sp3k_tgl > '1970-01-01')) THEN 1 ELSE 0 END) AS sp3k", false)
    ->select("SUM(CASE WHEN (COALESCE(m.is_batal, 0) = 0 AND COALESCE(m.status_mkdt, '') != 'Batal') AND COALESCE(m.akad, 0) = 0 AND (COALESCE(m.akad_indent, 0) = 0 AND COALESCE(m.is_ajb, '') != 'PPJB') AND (COALESCE(m.sp3k, 0) = 0 AND (m.sp3k_tgl IS NULL OR m.sp3k_tgl <= '1970-01-01')) AND (COALESCE(m.wawancara, 0) = 1 OR (m.wawancara_tgl IS NOT NULL AND m.wawancara_tgl > '1970-01-01')) THEN 1 ELSE 0 END) AS wawancara", false)
    ->select("SUM(CASE WHEN (COALESCE(m.is_batal, 0) = 0 AND COALESCE(m.status_mkdt, '') != 'Batal') AND COALESCE(m.akad, 0) = 0 AND (COALESCE(m.akad_indent, 0) = 0 AND COALESCE(m.is_ajb, '') != 'PPJB') AND (COALESCE(m.sp3k, 0) = 0 AND (m.sp3k_tgl IS NULL OR m.sp3k_tgl <= '1970-01-01')) AND (COALESCE(m.wawancara, 0) = 0 AND (m.wawancara_tgl IS NULL OR m.wawancara_tgl <= '1970-01-01')) THEN 1 ELSE 0 END) AS booking", false)
    ->get()
    ->getRowArray();

echo "Partition Results:\n";
print_r($partitionQuery);

$sumPartition = array_sum($partitionQuery);
echo "Sum of all 6 partitions: $sumPartition\n";
if ($sumPartition !== (int)$rawCount) {
    echo "ERROR: Partition sum $sumPartition does not equal raw count $rawCount!\n";
    exit(1);
}

// 5. Test BookingAkadReportRepository and BookingAkadReportService directly
$repo = new BookingAkadReportRepository($db);
$service = new BookingAkadReportService($repo);

$summary = $service->getSummary(13, 2026);
echo "Service Summary 2026 Total:\n";
print_r($summary['totals'][2026]);

// Check annual conversion math
$bookingCohort2026 = $summary['totals'][2026]['total_booking_cohort'];
$akadCohort2026 = $summary['totals'][2026]['booking_converted_akad'];
$expectedKonversi = $bookingCohort2026 > 0 ? round(($akadCohort2026 / $bookingCohort2026) * 100, 1) : 0.0;
echo "Cohort 2026: Converted = $akadCohort2026, Total = $bookingCohort2026, Expected Konversi = $expectedKonversi%, Actual = " . $summary['totals'][2026]['konversi'] . "%\n";

if ($expectedKonversi !== $summary['totals'][2026]['konversi']) {
    echo "ERROR: Konversi mismatch!\n";
    exit(1);
}

// 6. Test detail drilldown for a month in 2026 that has transactions
$foundMonth = null;
foreach ($summary['months'] as $m) {
    if ($m['values'][2026]['booking'] > 0 || $m['values'][2026]['akad_ajb'] > 0) {
        $foundMonth = $m['month'];
        break;
    }
}
echo "Testing detail for month: $foundMonth in 2026 (category 'akad_ajb')...\n";
$detail = $service->getDetail(13, 2026, $foundMonth, 'akad_ajb');
echo "Detail recordsTotal = " . $detail['recordsTotal'] . "\n";
if (!empty($detail['data'])) {
    echo "Sample detail row 1:\n";
    print_r($detail['data'][0]);
}

echo "=== INDEPENDENT VERIFICATION COMPLETE: ALL PASS ===\n";

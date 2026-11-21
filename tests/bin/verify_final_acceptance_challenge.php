<?php

declare(strict_types=1);

/**
 * ====================================================================
 * FINAL ACCEPTANCE CHALLENGE TEST HARNESS
 * Modul: MKDT / Laporan Booking & Akad Overhaul
 * Author: Challenger Final 1 (teamwork_challenger)
 * ====================================================================
 */

$root = dirname(__DIR__, 2);
require $root . '/vendor/codeigniter4/framework/system/Test/bootstrap.php';

use App\Repositories\BookingAkadReportRepository;
use App\Services\BookingAkadReportService;
use Config\Database;

$db = Database::connect('default', false);
$repo = new BookingAkadReportRepository($db);
$service = new BookingAkadReportService($repo);

$totalChecks = 0;
$passedChecks = 0;
$failures = [];

$assertEmpirical = function (string $id, string $title, bool $condition, string $evidence = '') use (&$totalChecks, &$passedChecks, &$failures): void {
    $totalChecks++;
    if ($condition) {
        $passedChecks++;
        echo sprintf("  [PASS] %-10s %-60s | %s\n", $id, $title, $evidence);
    } else {
        $failures[] = "{$id} {$title}: {$evidence}";
        echo sprintf("  [FAIL] %-10s %-60s | %s\n", $id, $title, $evidence);
    }
};

echo "====================================================================\n";
echo " FINAL MILESTONE ACCEPTANCE CHALLENGE: LAPORAN BOOKING & AKAD\n";
echo " Timestamp: " . gmdate('Y-m-d H:i:s') . " UTC\n";
echo " Proyek Target: ID 13 (SANGGAR INDAH PALASTRI)\n";
echo "====================================================================\n\n";

// --------------------------------------------------------------------
// SECTION 1: METRIK & KALKULASI (6 STATUS & REAL COHORT CONVERSION)
// --------------------------------------------------------------------
echo "--- SECTION 1: METRIK & KALKULASI ---\n";

// 1.1 Proyek 13 Dataset Invariants
$proyekRow = $db->table('proyek')->where('id_proyek', 13)->get()->getRowArray();
$assertEmpirical(
    'CRIT-1.01',
    'Target Proyek 13 exists in database',
    $proyekRow !== null && ($proyekRow['nama_proyek'] ?? '') !== '',
    'Nama: ' . ($proyekRow['nama_proyek'] ?? 'N/A')
);

$totalMkdtSip = $db->table('mkdt m')
    ->join('kavling k', 'k.id_kavling = m.id_kavling')
    ->join('jalan j', 'j.id_jalan = k.id_jalan')
    ->join('cluster cl', 'cl.id_cluster = j.id_cluster')
    ->where('cl.id_proyek', 13)
    ->countAllResults();

$assertEmpirical(
    'CRIT-1.02',
    'Total MKDT transactions in Proyek 13 matches ground truth 241',
    $totalMkdtSip === 241,
    "Count: {$totalMkdtSip} records"
);

$distinctUniq = $db->table('mkdt m')
    ->join('kavling k', 'k.id_kavling = m.id_kavling')
    ->join('jalan j', 'j.id_jalan = k.id_jalan')
    ->join('cluster cl', 'cl.id_cluster = j.id_cluster')
    ->select('COUNT(DISTINCT m.uniq_id) as cnt, COUNT(*) as total')
    ->where('cl.id_proyek', 13)
    ->get()->getRowArray();

$assertEmpirical(
    'CRIT-1.03',
    '100% Unique transaction code (uniq_id) presence & uniqueness in Proyek 13',
    ((int)$distinctUniq['cnt'] === 241) && ((int)$distinctUniq['total'] === 241),
    "Distinct uniq_id: {$distinctUniq['cnt']} / Total: {$distinctUniq['total']}"
);

// 1.2 6 Statuses Partition on Proyek 13
$statusPartition = $db->query("
    SELECT 
        SUM(CASE WHEN (COALESCE(m.is_batal, 0) = 1 OR COALESCE(m.status_mkdt, '') = 'Batal') THEN 1 ELSE 0 END) AS batal,
        SUM(CASE WHEN (COALESCE(m.is_batal, 0) = 0 AND COALESCE(m.status_mkdt, '') != 'Batal')
                      AND COALESCE(m.akad, 0) = 1 THEN 1 ELSE 0 END) AS akad_ajb,
        SUM(CASE WHEN (COALESCE(m.is_batal, 0) = 0 AND COALESCE(m.status_mkdt, '') != 'Batal')
                      AND COALESCE(m.akad, 0) = 0
                      AND (COALESCE(m.akad_indent, 0) = 1 OR COALESCE(m.is_ajb, '') = 'PPJB') THEN 1 ELSE 0 END) AS akad_indent,
        SUM(CASE WHEN (COALESCE(m.is_batal, 0) = 0 AND COALESCE(m.status_mkdt, '') != 'Batal')
                      AND COALESCE(m.akad, 0) = 0
                      AND (COALESCE(m.akad_indent, 0) = 0 AND COALESCE(m.is_ajb, '') != 'PPJB')
                      AND (COALESCE(m.sp3k, 0) = 1 OR (m.sp3k_tgl IS NOT NULL AND m.sp3k_tgl > '1970-01-01')) THEN 1 ELSE 0 END) AS sp3k,
        SUM(CASE WHEN (COALESCE(m.is_batal, 0) = 0 AND COALESCE(m.status_mkdt, '') != 'Batal')
                      AND COALESCE(m.akad, 0) = 0
                      AND (COALESCE(m.akad_indent, 0) = 0 AND COALESCE(m.is_ajb, '') != 'PPJB')
                      AND (COALESCE(m.sp3k, 0) = 0 AND (m.sp3k_tgl IS NULL OR m.sp3k_tgl <= '1970-01-01'))
                      AND (COALESCE(m.wawancara, 0) = 1 OR (m.wawancara_tgl IS NOT NULL AND m.wawancara_tgl > '1970-01-01')) THEN 1 ELSE 0 END) AS wawancara,
        SUM(CASE WHEN (COALESCE(m.is_batal, 0) = 0 AND COALESCE(m.status_mkdt, '') != 'Batal')
                      AND COALESCE(m.akad, 0) = 0
                      AND (COALESCE(m.akad_indent, 0) = 0 AND COALESCE(m.is_ajb, '') != 'PPJB')
                      AND (COALESCE(m.sp3k, 0) = 0 AND (m.sp3k_tgl IS NULL OR m.sp3k_tgl <= '1970-01-01'))
                      AND (COALESCE(m.wawancara, 0) = 0 AND (m.wawancara_tgl IS NULL OR m.wawancara_tgl <= '1970-01-01')) THEN 1 ELSE 0 END) AS booking
    FROM mkdt m
    JOIN kavling k ON k.id_kavling = m.id_kavling
    JOIN jalan j ON j.id_jalan = k.id_jalan
    JOIN cluster cl ON cl.id_cluster = j.id_cluster
    WHERE cl.id_proyek = 13
")->getRowArray();

$sumPartition = (int)$statusPartition['booking']
    + (int)$statusPartition['wawancara']
    + (int)$statusPartition['sp3k']
    + (int)$statusPartition['batal']
    + (int)$statusPartition['akad_indent']
    + (int)$statusPartition['akad_ajb'];

$assertEmpirical(
    'CRIT-1.04',
    'Database partition into 6 statuses sums exactly to 241',
    $sumPartition === 241 && (int)$statusPartition['akad_ajb'] === 170 && (int)$statusPartition['akad_indent'] === 19 && (int)$statusPartition['sp3k'] === 16 && (int)$statusPartition['wawancara'] === 9 && (int)$statusPartition['booking'] === 18 && (int)$statusPartition['batal'] === 9,
    "AJB:{$statusPartition['akad_ajb']}, Indent:{$statusPartition['akad_indent']}, SP3K:{$statusPartition['sp3k']}, Wawancara:{$statusPartition['wawancara']}, Booking:{$statusPartition['booking']}, Batal:{$statusPartition['batal']}"
);

// 1.3 Service Summary Calculation for 2026
$summary2026 = $service->getSummary(13, 2026);
$assertEmpirical(
    'CRIT-1.05',
    'Service summary returns all 6 status keys + konversi per month',
    isset($summary2026['summary'][2026][1]['booking'],
          $summary2026['summary'][2026][1]['wawancara'],
          $summary2026['summary'][2026][1]['sp3k'],
          $summary2026['summary'][2026][1]['batal'],
          $summary2026['summary'][2026][1]['akad_indent'],
          $summary2026['summary'][2026][1]['akad_ajb'],
          $summary2026['summary'][2026][1]['konversi']),
    'All 7 expected metrics present in monthly summary payload'
);

// Verify total annual row has 6 statuses and conversion
$annualTotals = $summary2026['totals'][2026];
$assertEmpirical(
    'CRIT-1.06',
    'Service summary total year contains all 6 statuses and annual conversion',
    isset($annualTotals['booking'], $annualTotals['wawancara'], $annualTotals['sp3k'], $annualTotals['batal'], $annualTotals['akad_indent'], $annualTotals['akad_ajb'], $annualTotals['konversi']),
    sprintf('Annual Konversi: %.1f%%, Total Booking: %d', $annualTotals['konversi'], $annualTotals['total_booking_cohort'])
);

// Verify cohort conversion mathematical soundness
// In 2026: Total bookings = 68, Converted to Akad = 36 (19 Indent + 17 AJB) -> 36/68 = 52.94% -> 52.9%
$expectedAnnualKonversi = round((36 / 68) * 100, 1); // 52.9
$assertEmpirical(
    'CRIT-1.07',
    'Annual conversion ratio is calculated from unique transaction cohort (52.9%)',
    abs($annualTotals['konversi'] - $expectedAnnualKonversi) < 0.01,
    "Computed: {$annualTotals['konversi']}% (Expected: {$expectedAnnualKonversi}%)"
);

// Adversarial test: Check that conversion is bounded [0.0, 100.0] across all 12 months
$allMonthsBounded = true;
for ($m = 1; $m <= 12; $m++) {
    $k = $summary2026['summary'][2026][$m]['konversi'];
    if ($k < 0.0 || $k > 100.0) {
        $allMonthsBounded = false;
        break;
    }
}
$assertEmpirical(
    'CRIT-1.08',
    'Conversion ratio strictly bounded within [0.0%, 100.0%] for all 12 months',
    $allMonthsBounded,
    'Eliminated legacy 1400% anomaly'
);

// --------------------------------------------------------------------
// SECTION 2: MODAL DETAIL & UI INTEGRATION
// --------------------------------------------------------------------
echo "\n--- SECTION 2: MODAL DETAIL & UI ---\n";

// 2.1 Service getDetail returns exact 7 columns
$detailResult = $service->getDetail(13, [
    'year' => 2026,
    'month' => 4,
    'category' => 'booking',
    'draw' => 1,
    'start' => 0,
    'length' => 10,
]);

$assertEmpirical(
    'CRIT-2.01',
    'Service getDetail returns valid DataTables payload',
    isset($detailResult['draw'], $detailResult['recordsTotal'], $detailResult['recordsFiltered'], $detailResult['data']),
    "recordsTotal: {$detailResult['recordsTotal']}, returned rows: " . count($detailResult['data'])
);

if (!empty($detailResult['data'])) {
    $firstRow = $detailResult['data'][0];
    $hasAll7Cols = isset(
        $firstRow['id_mkdt'],
        $firstRow['id_kavling'],
        $firstRow['kavling']['nama_jalan'],
        $firstRow['kavling']['no_kavling'],
        $firstRow['kavling']['sales'],
        $firstRow['konsumen']['nama_konsumen'],
        $firstRow['konsumen']['tipe_rumah'],
        $firstRow['konsumen']['is_subsidi'],
        $firstRow['tanggal_booking'],
        $firstRow['status_terakhir'],
        $firstRow['status_terakhir_badge'],
        $firstRow['catatan_mkdt'],
        $firstRow['tanggal_status_terakhir']
    );
    $assertEmpirical(
        'CRIT-2.02',
        'Detail row payload matches 7-column dictionary and relations',
        $hasAll7Cols,
        "Kavling: {$firstRow['kavling']['nama_jalan']} No {$firstRow['kavling']['no_kavling']}, Konsumen: {$firstRow['konsumen']['nama_konsumen']}, Status: {$firstRow['status_terakhir']}"
    );
}

// 2.2 Template Inspection for 7 columns & alignments
$viewPath = $root . '/app/Views/laporan/booking-akad.php';
$viewContent = file_get_contents($viewPath);

$assertEmpirical(
    'CRIT-2.03',
    'Detail table DOM header explicitly renders all 7 specified columns',
    str_contains($viewContent, '<th class="text-center" style="width: 40px;">No</th>') &&
    str_contains($viewContent, '<th class="text-left">Kavling</th>') &&
    str_contains($viewContent, '<th class="text-left">Konsumen</th>') &&
    str_contains($viewContent, '<th class="text-center">Tanggal Booking</th>') &&
    str_contains($viewContent, '<th class="text-left">Status Terakhir</th>') &&
    str_contains($viewContent, '<th class="text-left">Catatan / Keterangan</th>') &&
    str_contains($viewContent, '<th class="text-center" style="width: 70px;">Aksi</th>'),
    '7 th elements matched in booking_akad_detail_table'
);

// 2.3 Check siteplan modal dependencies in booking-akad.php
$hasModalStyles = str_contains($viewContent, "view('siteplan/partials/modal_detail_styles')");
$hasFlatpickr = str_contains($viewContent, 'flatpickr.min.js');
$hasModalDetailPartial = str_contains($viewContent, "view('siteplan/partials/modal_detail'");
$hasBaseUrl = preg_match("/window\.base_url\s*=\s*'<\?=\s*base_url\(\)\s*\?>\/'/", $viewContent) === 1;
$hasLoadedGuard = str_contains($viewContent, 'window.loaded = window.loaded || [];');
$hasDtProyekGuard = str_contains($viewContent, "if (typeof dt_proyek === 'undefined') var dt_proyek");
$hasSiteplanJs = str_contains($viewContent, 'assets/js/siteplan-detail-modal.js');

$assertEmpirical(
    'CRIT-2.04',
    'All siteplan modal dependencies, styles, scripts, and runtime globals guard exist',
    $hasModalStyles && $hasFlatpickr && $hasModalDetailPartial && $hasBaseUrl && $hasLoadedGuard && $hasDtProyekGuard && $hasSiteplanJs,
    'modal_detail_styles, flatpickr, partial, base_url, loaded, dt_proyek, siteplan-detail-modal.js verified'
);

// 2.4 JavaScript Event Handler and Button Binding
$jsPath = $root . '/public/assets/js/laporan-booking-akad.js';
$jsContent = file_get_contents($jsPath);

$hasViewKavlingClick = str_contains($jsContent, '$(document).on("click", ".booking-akad-view-kavling", function ()');
$hasDetailKavlingFallback = str_contains($jsContent, 'detail_kavling(sh, idKavling)');
$hasSiteplanModalCall = str_contains($jsContent, 'window.openSiteplanDetailModal(idKavling)');
$hasSafeStringSanitization = str_contains($jsContent, 'var namaJalan = String(button.data("nama-jalan") || "");') && str_contains($jsContent, 'var noKavling = String(button.data("no-kavling") || "");');

$assertEmpirical(
    'CRIT-2.05',
    'JavaScript action button triggers siteplan modal safely without string type errors',
    $hasViewKavlingClick && ($hasDetailKavlingFallback || $hasSiteplanModalCall) && $hasSafeStringSanitization,
    'Click handler, safe string parsing, and modal invocation verified'
);

// 2.5 Text Alignment Standards (R4)
$cssPath = $root . '/public/assets/css/laporan-booking-akad.css';
$cssContent = file_get_contents($cssPath);

$hasExplicitAlignClasses = str_contains($cssContent, '#booking_akad_matrix th.text-left') &&
                           str_contains($cssContent, '#booking_akad_matrix th.text-right') &&
                           str_contains($cssContent, '#booking_akad_matrix th.text-center') &&
                           str_contains($cssContent, '#booking_akad_detail_table th.text-left') &&
                           str_contains($cssContent, '#booking_akad_detail_table th.text-center');

$assertEmpirical(
    'CRIT-2.06',
    'CSS and DataTables enforce explicit text-left, text-right, and text-center classes',
    $hasExplicitAlignClasses,
    'Explicit text alignment classes configured for matrix and detail tables'
);

// --------------------------------------------------------------------
// SECTION 3: DOKUMENTASI MODUL
// --------------------------------------------------------------------
echo "\n--- SECTION 3: DOKUMENTASI MODUL ---\n";

$moduleDocPath = $root . '/docs/modules/MKDT/MODULE.md';
$laporanDocPath = $root . '/docs/modules/MKDT/laporan-booking-akad.md';

$moduleDocExists = file_exists($moduleDocPath);
$laporanDocExists = file_exists($laporanDocPath);

$assertEmpirical(
    'CRIT-3.01',
    'Both documentation files exist in docs/modules/MKDT/',
    $moduleDocExists && $laporanDocExists,
    "MODULE.md: " . ($moduleDocExists ? 'OK' : 'MISSING') . ", laporan-booking-akad.md: " . ($laporanDocExists ? 'OK' : 'MISSING')
);

$moduleDoc = $moduleDocExists ? file_get_contents($moduleDocPath) : '';
$laporanDoc = $laporanDocExists ? file_get_contents($laporanDocPath) : '';

$hasRoutes = str_contains($moduleDoc, '/laporan/booking-akad') &&
             str_contains($moduleDoc, '/api/laporan/booking-akad/summary') &&
             str_contains($moduleDoc, '/api/laporan/booking-akad/detail');

$hasServiceMethods = str_contains($moduleDoc, 'BookingAkadReportService.php') &&
                     str_contains($moduleDoc, 'getSummary') &&
                     str_contains($moduleDoc, 'getDetail');

$hasCohortDoc = str_contains($laporanDoc, 'mkdt.uniq_id') &&
                (str_contains($laporanDoc, 'Cohort Real Conversion Rate') || str_contains($laporanDoc, 'Cohort-Based Real Conversion Rate')) &&
                str_contains($laporanDoc, 'Akad Indent') &&
                str_contains($laporanDoc, 'Akad AJB');

$hasPipelineDoc = str_contains($laporanDoc, 'Hierarki Prevalensi Status') &&
                  str_contains($laporanDoc, 'Batal') &&
                  str_contains($laporanDoc, 'SP3K') &&
                  str_contains($laporanDoc, 'Wawancara');

$assertEmpirical(
    'CRIT-3.02',
    'Documentation comprehensively describes routes, services, cohort formula & 6-status pipeline',
    $hasRoutes && $hasServiceMethods && $hasCohortDoc && $hasPipelineDoc,
    'All core architectural artifacts documented in detail'
);

// --------------------------------------------------------------------
// SUMMARY & VERDICT
// --------------------------------------------------------------------
echo "\n====================================================================\n";
echo " CHALLENGE AUDIT SUMMARY\n";
echo " Total Empirical Checks : {$totalChecks}\n";
echo " Passed                 : {$passedChecks}\n";
echo " Failed                 : " . count($failures) . "\n";
echo " Pass Rate              : " . round(($passedChecks / $totalChecks) * 100, 1) . "%\n";
echo "====================================================================\n";

if (!empty($failures)) {
    echo "FAILURES ENCOUNTERED:\n";
    foreach ($failures as $f) {
        echo " - {$f}\n";
    }
    exit(1);
} else {
    echo "VERDICT: ALL ACCEPTANCE CRITERIA VERIFIED EMPIRICALLY WITH 100% SUCCESS.\n";
    exit(0);
}

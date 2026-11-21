<?php

/**
 * ==============================================================================
 * EMPIRICAL CHALLENGER VERIFICATION HARNESS: MILESTONE 2 (FRONTEND UI & R4)
 * ==============================================================================
 *
 * Usage:
 *   php tests/bin/verify_m2_frontend_empirical.php
 */

require dirname(__DIR__, 2) . '/vendor/codeigniter4/framework/system/Test/bootstrap.php';

use App\Repositories\BookingAkadReportRepository;
use App\Services\BookingAkadReportService;

echo "====================================================================\n";
echo " CHALLENGER M2_2: EMPIRICAL VERIFICATION HARNESS (R4 ALIGNMENTS & UI)\n";
echo "====================================================================\n";
echo "Timestamp : " . date('Y-m-d H:i:s T') . "\n";
echo "PHP Version: " . PHP_VERSION . "\n\n";

$passCount = 0;
$failCount = 0;

function check(string $code, string $desc, bool $condition, ?string $extra = null): void
{
    global $passCount, $failCount;
    if ($condition) {
        $passCount++;
        echo "  \033[32m[PASS]\033[0m {$code} - {$desc}" . ($extra ? " ({$extra})" : "") . "\n";
    } else {
        $failCount++;
        echo "  \033[31m[FAIL]\033[0m {$code} - {$desc}" . ($extra ? " ({$extra})" : "") . "\n";
    }
}

$viewPath = APPPATH . 'Views/laporan/booking-akad.php';
$jsPath = FCPATH . 'assets/js/laporan-booking-akad.js';
$cssPath = FCPATH . 'assets/css/laporan-booking-akad.css';

$viewContent = file_get_contents($viewPath);
$jsContent = file_get_contents($jsPath);
$cssContent = file_get_contents($cssPath);

// -----------------------------------------------------------------------------
// SECTION 1: MODAL DETAIL 7-COLUMN SYNCHRONIZATION & ALIGNMENT
// -----------------------------------------------------------------------------
echo "--------------------------------------------------------------------\n";
echo " 1. MODAL DETAIL TABLE 7-COLUMN SYNCHRONIZATION & ALIGNMENT (R3 & R4)\n";
echo "--------------------------------------------------------------------\n";

// Parse HTML thead
preg_match('/<thead\s+id="booking_akad_detail_table_head">([\s\S]*?)<\/thead>/i', $viewContent, $theadM);
preg_match_all('/<th([^>]*)>(.*?)<\/th>/is', $theadM[1] ?? '', $thList, PREG_SET_ORDER);

$expectedHtmlCols = [
    0 => ['No', 'text-center'],
    1 => ['Kavling', 'text-left'],
    2 => ['Konsumen', 'text-left'],
    3 => ['Tanggal Booking', 'text-center'],
    4 => ['Status Terakhir', 'text-left'],
    5 => ['Tanggal Status Terakhir', 'text-center'],
    6 => ['Aksi', 'text-center'],
];

check('M2-MODAL-01', 'HTML thead contains exact 7 columns', count($thList) === 7, 'Count: ' . count($thList));

foreach ($expectedHtmlCols as $i => [$label, $align]) {
    $foundText = trim(strip_tags($thList[$i][2] ?? ''));
    $attr = $thList[$i][1] ?? '';
    $hasAlign = strpos($attr, $align) !== false;
    check(
        "M2-MODAL-02-C{$i}",
        "HTML Col {$i} is '{$label}' with alignment '{$align}'",
        $foundText === $label && $hasAlign,
        "Found: '{$foundText}' [{$attr}]"
    );
}

// Parse JS columns
preg_match('/var\s+columns\s*=\s*\[([\s\S]*?)\]\s*;\s*\n\s*detailTable/i', $jsContent, $colBlockM);
preg_match_all('/className\s*:\s*["\']([^"\']+)["\']/i', $colBlockM[1] ?? '', $jsColClasses);
$jsClasses = $jsColClasses[1] ?? [];

check('M2-MODAL-03', 'JS DataTables columns configuration defines exact 7 columns', count($jsClasses) === 7, 'Count: ' . count($jsClasses));

foreach ($expectedHtmlCols as $i => [$label, $align]) {
    $jsAlign = $jsClasses[$i] ?? '';
    check(
        "M2-MODAL-04-C{$i}",
        "JS DataTables Col {$i} ({$label}) className matches '{$align}'",
        $jsAlign === $align,
        "JS className: '{$jsAlign}'"
    );
}

// -----------------------------------------------------------------------------
// SECTION 2: MATRIX TABLE LAYOUT & TEXT ALIGNMENT (R1, R2, R4)
// -----------------------------------------------------------------------------
echo "\n--------------------------------------------------------------------\n";
echo " 2. MATRIX TABLE LAYOUT & TEXT ALIGNMENT (R1, R2, R4)\n";
echo "--------------------------------------------------------------------\n";

// Year header colspan
$hasColspan7 = strpos($jsContent, 'colspan="7"') !== false;
check('M2-MATRIX-01', 'Matrix header sets colspan="7" for 6 statuses + 1 conversion %', $hasColspan7);

// Month header left alignment
$monthHeaderLeft = preg_match('/<th\s+rowspan="2"\s+class="[^"]*text-left[^"]*booking-akad-month-column[^"]*">Bulan<\/th>/i', $jsContent);
check('M2-MATRIX-02', 'Matrix header Month column has class text-left', (bool) $monthHeaderLeft);

// Monthly body row alignments
$bodyMonthLeft = preg_match('/cells\s*=\s*\[\s*["\']<td class="text-left">["\']/i', $jsContent);
check('M2-MATRIX-03', 'Matrix body Month label cell has class text-left', (bool) $bodyMonthLeft);

$metricCellsRight = preg_match('/cells\.push\(\s*["\']<td class="text-right">["\']\s*\+\s*detailButton/i', $jsContent);
check('M2-MATRIX-04', 'Matrix body Metric cells (isDrilldown=true) have class text-right', (bool) $metricCellsRight);

$conversionCellCenter = preg_match('/cells\.push\(["\']<td class="text-center">["\']\s*\+\s*rasioBadge/i', $jsContent);
check('M2-MATRIX-05', 'Matrix body Conversion % cell (isDrilldown=false) has class text-center', (bool) $conversionCellCenter);

// Annual footer row alignments
$footMonthLeft = preg_match('/footerCells\s*=\s*\[\s*["\']<td class="text-left font-weight-bold">Total Tahun<\/td>["\']/i', $jsContent);
check('M2-MATRIX-06', 'Matrix footer Month label cell has class text-left', (bool) $footMonthLeft);

$footMetricsRight = preg_match('/footerCells\.push\(\s*["\']<td class="text-right font-weight-bold/i', $jsContent);
check('M2-MATRIX-07', 'Matrix footer Metric total cells have class text-right', (bool) $footMetricsRight);

$footConversionCenter = preg_match('/footerCells\.push\(["\']<td class="text-center font-weight-bold">["\']\s*\+\s*rasioBadge/i', $jsContent);
check('M2-MATRIX-08', 'Matrix footer Conversion % cell has class text-center', (bool) $footConversionCenter);

// -----------------------------------------------------------------------------
// SECTION 3: CSS OVERRIDES & SPECIFICITY AGAINST DATATABLES STYLES
// -----------------------------------------------------------------------------
echo "\n--------------------------------------------------------------------\n";
echo " 3. CSS OVERRIDES & SPECIFICITY AGAINST DATATABLES (R4)\n";
echo "--------------------------------------------------------------------\n";

$hasImportantLeft = preg_match('/#booking_akad_matrix\s+th\.text-left,\s*#booking_akad_matrix\s+td\.text-left,\s*#booking_akad_detail_table\s+th\.text-left,\s*#booking_akad_detail_table\s+td\.text-left\s*\{\s*text-align:\s*left\s*!important;\s*\}/i', $cssContent);
check('M2-CSS-01', 'CSS overrides text-align: left !important on matrix & detail tables', (bool) $hasImportantLeft);

$hasImportantRight = preg_match('/#booking_akad_matrix\s+th\.text-right,\s*#booking_akad_matrix\s+td\.text-right,\s*#booking_akad_detail_table\s+th\.text-right,\s*#booking_akad_detail_table\s+td\.text-right\s*\{\s*text-align:\s*right\s*!important;\s*\}/i', $cssContent);
check('M2-CSS-02', 'CSS overrides text-align: right !important on matrix & detail tables', (bool) $hasImportantRight);

$hasImportantCenter = preg_match('/#booking_akad_matrix\s+th\.text-center,\s*#booking_akad_matrix\s+td\.text-center,\s*#booking_akad_detail_table\s+th\.text-center,\s*#booking_akad_detail_table\s+td\.text-center\s*\{\s*text-align:\s*center\s*!important;\s*\}/i', $cssContent);
check('M2-CSS-03', 'CSS overrides text-align: center !important on matrix & detail tables', (bool) $hasImportantCenter);

$hasMin1200 = preg_match('/#booking_akad_matrix\s*\{\s*min-width:\s*1200px;/i', $cssContent);
check('M2-CSS-04', 'CSS sets #booking_akad_matrix min-width: 1200px (single year)', (bool) $hasMin1200);

$hasMin2200 = preg_match('/#booking_akad_matrix\.is-comparison\s*\{\s*min-width:\s*2200px;/i', $cssContent);
check('M2-CSS-05', 'CSS sets #booking_akad_matrix.is-comparison min-width: 2200px (2 years)', (bool) $hasMin2200);

$btnRight = preg_match('/\.booking-akad-value-button\s*\{[\s\S]*?text-align:\s*right;/i', $cssContent);
check('M2-CSS-06', 'CSS sets .booking-akad-value-button text-align: right', (bool) $btnRight);

$badgeCenter = preg_match('/\.booking-akad-rasio-badge\s*\{[\s\S]*?text-align:\s*center;/i', $cssContent);
check('M2-CSS-07', 'CSS sets .booking-akad-rasio-badge text-align: center', (bool) $badgeCenter);

// -----------------------------------------------------------------------------
// SECTION 4: REAL-DATA END-TO-END PAYLOAD VERIFICATION (PROYEK 13)
// -----------------------------------------------------------------------------
echo "\n--------------------------------------------------------------------\n";
echo " 4. REAL-DATA END-TO-END PAYLOAD VERIFICATION (PROYEK 13)\n";
echo "--------------------------------------------------------------------\n";

$liveDb = \Config\Database::connect('default', false);
$repo = new BookingAkadReportRepository($liveDb);
$service = new BookingAkadReportService($repo);

// Proyek 13 has transactions across various months. Let's find a month with data or test with month 1
$summary = $service->getSummary(13, 2026);
$monthToTest = 1;
foreach ($summary['months'] as $m) {
    if (($m['values'][2026]['booking'] ?? 0) > 0) {
        $monthToTest = $m['month'];
        break;
    }
}

$detailResult = $service->getDetail(13, 2026, $monthToTest, 'booking');
$detailPayload = $detailResult['data'] ?? [];

check('M2-DATA-01', 'Service returns array of rows for Proyek 13 (Month: ' . $monthToTest . ')', is_array($detailPayload), 'Rows: ' . count($detailPayload));

if (!empty($detailPayload)) {
    $firstRow = $detailPayload[0];
    
    // Check required keys in payload matching JS openDetail columns
    $hasKavling = isset($firstRow['kavling']['nama_jalan'], $firstRow['kavling']['no_kavling'], $firstRow['kavling']['sales']);
    check('M2-DATA-02', 'Payload contains kavling object (nama_jalan, no_kavling, sales)', $hasKavling, ($firstRow['kavling']['nama_jalan'] ?? '') . ' No ' . ($firstRow['kavling']['no_kavling'] ?? ''));

    $hasKonsumen = isset($firstRow['konsumen']['nama_konsumen'], $firstRow['konsumen']['tipe_rumah'], $firstRow['konsumen']['is_subsidi']);
    check('M2-DATA-03', 'Payload contains konsumen object (nama_konsumen, tipe_rumah, is_subsidi)', $hasKonsumen, $firstRow['konsumen']['nama_konsumen'] ?? '');

    $hasTanggalBooking = isset($firstRow['tanggal_booking']);
    check('M2-DATA-04', 'Payload contains tanggal_booking', $hasTanggalBooking, $firstRow['tanggal_booking'] ?? '');

    $hasStatusTerakhir = isset($firstRow['status_terakhir'], $firstRow['status_terakhir_badge'], $firstRow['catatan_mkdt']);
    check('M2-DATA-05', 'Payload contains status_terakhir, badge, and catatan_mkdt', $hasStatusTerakhir, ($firstRow['status_terakhir'] ?? '') . ' [' . ($firstRow['status_terakhir_badge'] ?? '') . ']');

    $hasTanggalStatusTerakhir = isset($firstRow['tanggal_status_terakhir']);
    check('M2-DATA-06', 'Payload contains tanggal_status_terakhir', $hasTanggalStatusTerakhir, $firstRow['tanggal_status_terakhir'] ?? '');

    $hasIdKavling = !empty($firstRow['id_kavling']);
    check('M2-DATA-07', 'Payload contains id_kavling for action button modal trigger', $hasIdKavling, 'ID: ' . ($firstRow['id_kavling'] ?? 0));
} else {
    // If 2026 has no booking in chosen month, test on available years
    $years = $service->getAvailableYears(13);
    $testYear = !empty($years) ? $years[0] : 2024;
    $detailResult = $service->getDetail(13, $testYear, 1, 'booking');
    check('M2-DATA-01-ALT', "Service returns data for year {$testYear}", is_array($detailResult['data']));
}

echo "\n====================================================================\n";
echo " CHALLENGE SUMMARY: {$passCount} Passed, {$failCount} Failed\n";
echo "====================================================================\n";

exit($failCount === 0 ? 0 : 1);

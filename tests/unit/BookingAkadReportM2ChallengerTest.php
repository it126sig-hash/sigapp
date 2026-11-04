<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Class BookingAkadReportM2ChallengerTest
 *
 * EMPIRICAL ADVERSARIAL CHALLENGER TEST SUITE for Milestone 2:
 * Frontend UI & Modal Integration.
 *
 * Verifies Requirement R4 (Text Alignment & Table Layout),
 * Requirement R3 (7-column Modal Layout & Actions),
 * CSS Overrides against DataTables default styles, and
 * View/Script syntactical and logical integrity.
 */
final class BookingAkadReportM2ChallengerTest extends CIUnitTestCase
{
    private string $viewContent;
    private string $jsContent;
    private string $cssContent;

    protected function setUp(): void
    {
        parent::setUp();

        $viewPath = APPPATH . 'Views/laporan/booking-akad.php';
        $jsPath = FCPATH . 'assets/js/laporan-booking-akad.js';
        $cssPath = FCPATH . 'assets/css/laporan-booking-akad.css';

        $this->assertFileExists($viewPath, "View file must exist at {$viewPath}");
        $this->assertFileExists($jsPath, "JS file must exist at {$jsPath}");
        $this->assertFileExists($cssPath, "CSS file must exist at {$cssPath}");

        $this->viewContent = file_get_contents($viewPath);
        $this->jsContent = file_get_contents($jsPath);
        $this->cssContent = file_get_contents($cssPath);
    }

    /**
     * TEST 1: View Modal Detail Header must define exact 7 columns per R3 & R4.
     */
    public function testModalDetailTableHeadHasExactSevenColumnsWithCorrectAlignments(): void
    {
        // Extract the thead for #booking_akad_detail_table
        $this->assertMatchesRegularExpression(
            '/<thead\s+id="booking_akad_detail_table_head">([\s\S]*?)<\/thead>/i',
            $this->viewContent,
            'Detail table must contain thead with id booking_akad_detail_table_head'
        );

        preg_match(
            '/<thead\s+id="booking_akad_detail_table_head">([\s\S]*?)<\/thead>/i',
            $this->viewContent,
            $matches
        );
        $theadHtml = $matches[1];

        // Match all <th> elements
        preg_match_all('/<th([^>]*)>(.*?)<\/th>/is', $theadHtml, $thMatches, PREG_SET_ORDER);

        $this->assertCount(7, $thMatches, 'Modal detail header must have exactly 7 columns per Requirement R3');

        $expectedColumns = [
            0 => ['label' => 'No',                     'align' => 'text-center'],
            1 => ['label' => 'Kavling',                'align' => 'text-left'],
            2 => ['label' => 'Konsumen',               'align' => 'text-left'],
            3 => ['label' => 'Tanggal Booking',        'align' => 'text-center'],
            4 => ['label' => 'Status Terakhir',        'align' => 'text-left'],
            5 => ['label' => 'Tanggal Status Terakhir','align' => 'text-center'],
            6 => ['label' => 'Aksi',                   'align' => 'text-center'],
        ];

        foreach ($expectedColumns as $idx => $expected) {
            $thAttributes = $thMatches[$idx][1];
            $thText = trim(strip_tags($thMatches[$idx][2]));

            $this->assertSame($expected['label'], $thText, "Column {$idx} must have label '{$expected['label']}'");
            $this->assertStringContainsString(
                $expected['align'],
                $thAttributes,
                "Column {$idx} ({$expected['label']}) header must include class '{$expected['align']}'"
            );
        }

        // Verify legacy columns are removed
        $this->assertStringNotContainsString('Jenis Laporan', $theadHtml, 'Legacy column "Jenis Laporan" must not exist');
        $this->assertStringNotContainsString('Nominal', $theadHtml, 'Legacy column "Nominal" must not exist in modal header');
    }

    /**
     * TEST 2: JavaScript DataTables columns definition in openDetail must have exact 7 columns
     * with matching className and field mapping.
     */
    public function testJsDataTablesColumnsDefinitionMatchesSevenColumnsAndAlignments(): void
    {
        // Extract the columns array inside openDetail
        $this->assertMatchesRegularExpression(
            '/var\s+columns\s*=\s*\[([\s\S]*?)\]\s*;\s*\n\s*detailTable/i',
            $this->jsContent,
            'openDetail must define var columns = [...] before instantiating DataTable'
        );

        preg_match(
            '/var\s+columns\s*=\s*\[([\s\S]*?)\]\s*;\s*\n\s*detailTable/i',
            $this->jsContent,
            $matches
        );
        $columnsBlock = $matches[1];

        // Count column objects by matching className properties
        preg_match_all('/className\s*:\s*["\']([^"\']+)["\']/i', $columnsBlock, $classMatches);
        $classNames = $classMatches[1];

        $this->assertCount(7, $classNames, 'JavaScript DataTables columns must define exactly 7 columns with className');

        $expectedClasses = [
            0 => 'text-center', // No
            1 => 'text-left',   // Kavling
            2 => 'text-left',   // Konsumen
            3 => 'text-center', // Tanggal Booking
            4 => 'text-left',   // Status Terakhir
            5 => 'text-center', // Tanggal Status Terakhir
            6 => 'text-center', // Aksi
        ];

        foreach ($expectedClasses as $idx => $expectedClass) {
            $this->assertSame(
                $expectedClass,
                $classNames[$idx],
                "DataTables column {$idx} must have className: '{$expectedClass}'"
            );
        }

        // Verify default order is Tanggal Booking ASC: order: [[3, "asc"]]
        $this->assertMatchesRegularExpression(
            '/order\s*:\s*\[\s*\[\s*3\s*,\s*["\']asc["\']\s*\]\s*\]/i',
            $this->jsContent,
            'DataTables must have default sorting on Column 3 (Tanggal Booking) in ascending order'
        );
    }

    /**
     * TEST 3: Matrix table rendering logic enforces R4 alignments:
     * - Month column: text-left
     * - 6 Status Metric values: text-right
     * - Real Conversion %: text-center
     * Both in header, monthly body rows, and annual total footer row.
     */
    public function testMatrixTableLayoutAndTextAlignmentInJs(): void
    {
        // 1. Categories array has 7 items: 6 statuses + konversi
        $this->assertMatchesRegularExpression(
            '/var\s+categories\s*=\s*\[([\s\S]*?)\];/i',
            $this->jsContent,
            'Must define categories array'
        );

        preg_match('/var\s+categories\s*=\s*\[([\s\S]*?)\];/i', $this->jsContent, $catMatches);
        $catBlock = $catMatches[1];

        $expectedKeys = ['booking', 'wawancara', 'sp3k', 'batal', 'akad_indent', 'akad_ajb', 'konversi'];
        foreach ($expectedKeys as $key) {
            $this->assertStringContainsString("key: \"{$key}\"", $catBlock, "Categories must contain key '{$key}'");
        }

        // 2. Colspan for year header is 7
        $this->assertMatchesRegularExpression(
            '/colspan="7"/i',
            $this->jsContent,
            'renderHeader must set colspan="7" for each year group'
        );

        // 3. Month header has text-left
        $this->assertMatchesRegularExpression(
            '/<th\s+rowspan="2"\s+class="[^"]*text-left[^"]*booking-akad-month-column[^"]*">Bulan<\/th>/i',
            $this->jsContent,
            'Month header must have class text-left'
        );

        // 4. Body row cells:
        // Month cell: text-left
        $this->assertMatchesRegularExpression(
            '/cells\s*=\s*\[\s*["\']<td class="text-left">["\']/i',
            $this->jsContent,
            'Body month cell must have class="text-left"'
        );

        // Drilldown cells (the 6 metric numbers): text-right
        $this->assertMatchesRegularExpression(
            '/cells\.push\(\s*["\']<td class="text-right">["\']\s*\+\s*detailButton/i',
            $this->jsContent,
            'Metric value cells (isDrilldown=true) must be pushed with class="text-right"'
        );

        // Conversion cell (konversi %): text-center
        $this->assertMatchesRegularExpression(
            '/cells\.push\(["\']<td class="text-center">["\']\s*\+\s*rasioBadge/i',
            $this->jsContent,
            'Conversion cell (isDrilldown=false) must be pushed with class="text-center"'
        );

        // 5. Footer summary row:
        // Month label: text-left
        $this->assertMatchesRegularExpression(
            '/footerCells\s*=\s*\[\s*["\']<td class="text-left font-weight-bold">Total Tahun<\/td>["\']/i',
            $this->jsContent,
            'Footer month label must have class="text-left font-weight-bold"'
        );

        // Metric totals: text-right
        $this->assertMatchesRegularExpression(
            '/footerCells\.push\(\s*["\']<td class="text-right font-weight-bold/i',
            $this->jsContent,
            'Footer metric totals must have class="text-right font-weight-bold"'
        );

        // Conversion total: text-center
        $this->assertMatchesRegularExpression(
            '/footerCells\.push\(["\']<td class="text-center font-weight-bold">["\']\s*\+\s*rasioBadge/i',
            $this->jsContent,
            'Footer conversion total must have class="text-center font-weight-bold"'
        );
    }

    /**
     * TEST 4: CSS Overrides enforce R4 alignments with !important on matrix and detail tables.
     */
    public function testCssExplicitImportantAlignmentOverrides(): void
    {
        // Check text-left with !important
        $this->assertMatchesRegularExpression(
            '/#booking_akad_matrix\s+th\.text-left,\s*#booking_akad_matrix\s+td\.text-left,\s*#booking_akad_detail_table\s+th\.text-left,\s*#booking_akad_detail_table\s+td\.text-left\s*\{\s*text-align:\s*left\s*!important;\s*\}/i',
            $this->cssContent,
            'CSS must enforce text-align: left !important on matrix and detail table text-left cells'
        );

        // Check text-right with !important
        $this->assertMatchesRegularExpression(
            '/#booking_akad_matrix\s+th\.text-right,\s*#booking_akad_matrix\s+td\.text-right,\s*#booking_akad_detail_table\s+th\.text-right,\s*#booking_akad_detail_table\s+td\.text-right\s*\{\s*text-align:\s*right\s*!important;\s*\}/i',
            $this->cssContent,
            'CSS must enforce text-align: right !important on matrix and detail table text-right cells'
        );

        // Check text-center with !important
        $this->assertMatchesRegularExpression(
            '/#booking_akad_matrix\s+th\.text-center,\s*#booking_akad_matrix\s+td\.text-center,\s*#booking_akad_detail_table\s+th\.text-center,\s*#booking_akad_detail_table\s+td\.text-center\s*\{\s*text-align:\s*center\s*!important;\s*\}/i',
            $this->cssContent,
            'CSS must enforce text-align: center !important on matrix and detail table text-center cells'
        );
    }

    /**
     * TEST 5: CSS Table Layout & Min-Width Responsive Constraints.
     */
    public function testCssResponsiveTableLayoutConstraints(): void
    {
        // Single-year min-width >= 1200px
        $this->assertMatchesRegularExpression(
            '/#booking_akad_matrix\s*\{\s*min-width:\s*1200px;/i',
            $this->cssContent,
            'Matrix table must have min-width: 1200px for single year view'
        );

        // Comparison min-width >= 2200px
        $this->assertMatchesRegularExpression(
            '/#booking_akad_matrix\.is-comparison\s*\{\s*min-width:\s*2200px;/i',
            $this->cssContent,
            'Matrix table must have min-width: 2200px for 2-year comparison view'
        );

        // Value button inside metric cells has text-align: right
        $this->assertMatchesRegularExpression(
            '/\.booking-akad-value-button\s*\{[\s\S]*?text-align:\s*right;/i',
            $this->cssContent,
            'Value buttons inside metric cells must have text-align: right'
        );

        // Rasio badge inside conversion cells has text-align: center
        $this->assertMatchesRegularExpression(
            '/\.booking-akad-rasio-badge\s*\{[\s\S]*?text-align:\s*center;/i',
            $this->cssContent,
            'Rasio badges inside conversion cells must have text-align: center'
        );
    }

    /**
     * TEST 6: CSS Status Badge Definitions cover all 6 statuses per R1 & R3.
     */
    public function testCssStatusBadgesDefinedForSixStatuses(): void
    {
        $requiredStatusClasses = [
            'booking-akad-badge-booking',
            'booking-akad-badge-wawancara',
            'booking-akad-badge-sp3k',
            'booking-akad-badge-batal',
            'booking-akad-badge-akad-indent',
            'booking-akad-badge-akad-ajb',
        ];

        foreach ($requiredStatusClasses as $class) {
            $this->assertStringContainsString(
                ".{$class}",
                $this->cssContent,
                "CSS must define semantic badge styling for .{$class}"
            );
        }
    }

    /**
     * TEST 7: View Integration & JavaScript Global Guards.
     * Prevents runtime errors when clicking action buttons to open siteplan modal.
     */
    public function testViewGlobalVariablesAndModalDependencies(): void
    {
        // modal_detail_styles must be included in view
        $this->assertStringContainsString(
            "view('siteplan/partials/modal_detail_styles')",
            $this->viewContent,
            'View must include siteplan modal_detail_styles'
        );

        // flatpickr.min.js must be loaded
        $this->assertStringContainsString(
            'flatpickr.min.js',
            $this->viewContent,
            'View must include flatpickr.min.js vendor asset'
        );

        // modal_detail partial must be included
        $this->assertStringContainsString(
            "view('siteplan/partials/modal_detail'",
            $this->viewContent,
            'View must include siteplan/partials/modal_detail template'
        );

        // Global guards exist
        $this->assertStringContainsString('window.base_url =', $this->viewContent);
        $this->assertStringContainsString('window.loaded = window.loaded || [];', $this->viewContent);
        $this->assertStringContainsString('if (typeof loaded === \'undefined\') var loaded = [];', $this->viewContent);
        $this->assertStringContainsString('if (typeof editdtt === \'undefined\') var editdtt = [];', $this->viewContent);
    }
}

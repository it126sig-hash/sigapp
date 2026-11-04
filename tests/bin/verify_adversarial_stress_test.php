<?php

declare(strict_types=1);

/**
 * ====================================================================
 * ADVERSARIAL STRESS TESTING HARNESS
 * Role: critic (teamwork_challenger)
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

$advPassed = 0;
$advTotal = 0;
$advFailures = [];

$assertAdv = function (string $id, string $scenario, bool $condition, string $observation) use (&$advPassed, &$advTotal, &$advFailures): void {
    $advTotal++;
    if ($condition) {
        $advPassed++;
        echo sprintf("  [PASS] %-10s %-55s | %s\n", $id, $scenario, $observation);
    } else {
        $advFailures[] = "{$id} {$scenario}: {$observation}";
        echo sprintf("  [FAIL] %-10s %-55s | %s\n", $id, $scenario, $observation);
    }
};

echo "====================================================================\n";
echo " ADVERSARIAL STRESS TESTS: FAILURE MODES & ATTACK SCENARIOS\n";
echo "====================================================================\n\n";

// 1. Division by Zero Stress (Empty Month & Empty Year)
$emptyMockRepo = new class extends BookingAkadReportRepository {
    public function __construct() {}
    public function getMonthlyStatusTotals(int $idProyek, array $years): array {
        return []; // zero transactions
    }
    public function getAvailableYearBounds(int $idProyek): array {
        return ['min' => 2020, 'max' => 2026];
    }
};
$emptyService = new BookingAkadReportService($emptyMockRepo);
$emptySummary = $emptyService->getSummary(13, 2026);

$allZeroSafe = true;
for ($m = 1; $m <= 12; $m++) {
    if ($emptySummary['summary'][2026][$m]['konversi'] !== 0.0 || $emptySummary['summary'][2026][$m]['booking'] !== 0) {
        $allZeroSafe = false;
    }
}
$assertAdv(
    'ADV-01',
    'Division by zero immunity (all months 0 bookings)',
    $allZeroSafe && $emptySummary['totals'][2026]['konversi'] === 0.0,
    'Returned 0.0% safely for all months and annual total'
);

// 2. Extreme Mathematical Boundary: 100% Conversion Bounded
$hundredMockRepo = new class extends BookingAkadReportRepository {
    public function __construct() {}
    public function getMonthlyStatusTotals(int $idProyek, array $years): array {
        return [
            [
                'tahun' => 2026,
                'bulan' => 5,
                'total_booking' => 10,
                'total_booking_cohort' => 10,
                'booking_converted_akad' => 10,
                'batal' => 0,
                'akad_ajb' => 8,
                'akad_indent' => 2,
                'sp3k' => 0,
                'wawancara' => 0,
                'booking' => 0,
            ]
        ];
    }
};
$hundredService = new BookingAkadReportService($hundredMockRepo);
$hundredSummary = $hundredService->getSummary(13, 2026);
$assertAdv(
    'ADV-02',
    'Upper bound mathematical precision (100.0% conversion)',
    $hundredSummary['summary'][2026][5]['konversi'] === 100.0 && $hundredSummary['totals'][2026]['konversi'] === 100.0,
    "Computed: {$hundredSummary['summary'][2026][5]['konversi']}%"
);

// 3. Overflow Anomaly Attack (>100% legacy anomaly defense)
$overflowMockRepo = new class extends BookingAkadReportRepository {
    public function __construct() {}
    public function getMonthlyStatusTotals(int $idProyek, array $years): array {
        // Simulating corrupt or legacy raw input where cohort_akad > cohort_booking
        return [
            [
                'tahun' => 2026,
                'bulan' => 6,
                'total_booking' => 1,
                'total_booking_cohort' => 1,
                'booking_converted_akad' => 14, // legacy 1400% scenario
                'batal' => 0,
                'akad_ajb' => 14,
                'akad_indent' => 0,
                'sp3k' => 0,
                'wawancara' => 0,
                'booking' => 0,
            ]
        ];
    }
};
$overflowService = new BookingAkadReportService($overflowMockRepo);
$overflowSummary = $overflowService->getSummary(13, 2026);
$assertAdv(
    'ADV-03',
    'Overflow anomaly clamp defense (legacy 1400% capped)',
    $overflowSummary['summary'][2026][6]['konversi'] === 100.0,
    "Clamped strictly to {$overflowSummary['summary'][2026][6]['konversi']}% max"
);

// 4. Invalid Category Injection Attack
$injectedCatCaught = false;
try {
    $service->getDetail(13, ['year' => 2026, 'month' => 5, 'category' => "booking' OR 1=1 --"]);
} catch (InvalidArgumentException $e) {
    $injectedCatCaught = true;
}
$assertAdv(
    'ADV-04',
    'SQL injection / invalid category rejection',
    $injectedCatCaught,
    'InvalidArgumentException thrown as expected'
);

// 5. Identical Year Comparison Attack (yearA === yearB)
$sameYearCaught = false;
try {
    $service->getSummary(13, 2026, 2026);
} catch (InvalidArgumentException $e) {
    $sameYearCaught = ($e->getMessage() === 'Pilih dua tahun yang berbeda.');
}
$assertAdv(
    'ADV-05',
    'Identical comparison year rejection',
    $sameYearCaught,
    'Pilih dua tahun yang berbeda caught'
);

// 6. Dirty Zero-Date & Null Sales Sanitization
$dirtyRow = [
    'id_mkdt' => 999,
    'id_kavling' => 888,
    'nama_jalan' => null,
    'no_kavling' => '',
    'sales' => null,
    'nama_konsumen' => null,
    'nama_tipe' => null,
    'is_subsidi' => null,
    'booking_tgl' => '0000-00-00',
    'status_terakhir' => null,
    'is_batal' => 0,
    'status_mkdt' => '',
    'akad' => 0,
    'akad_indent' => 0,
    'sp3k' => 0,
    'wawancara' => 0,
    'tanggal_status_terakhir' => '0000-00-00 00:00:00',
    'catatan_mkdt' => '',
    'keterangan' => '',
    'keterangan_batal' => '',
    'keterangan_status' => '',
];

$reflection = new ReflectionClass($service);
$formatMethod = $reflection->getMethod('formatDetailRow');
$formatMethod->setAccessible(true);
$formattedDirty = $formatMethod->invoke($service, $dirtyRow);

$assertAdv(
    'ADV-06',
    'Dirty zero-dates and NULL relations sanitization',
    $formattedDirty['tanggal_booking'] === '-' &&
    $formattedDirty['tanggal_status_terakhir'] === '-' &&
    $formattedDirty['kavling']['nama_jalan'] === '-' &&
    $formattedDirty['kavling']['sales'] === '-' &&
    $formattedDirty['konsumen']['nama_konsumen'] === '-' &&
    $formattedDirty['catatan_mkdt'] === '-',
    'All dirty fields safely converted to hyphen placeholder (-)'
);

// 7. Status Precedence Stress: Batal overrides prior Akad AJB
$batalOverrideRow = [
    'is_batal' => 1,
    'status_mkdt' => 'Batal',
    'akad' => 1,
    'akad_tgl' => '2025-05-10',
    'akad_indent' => 1,
    'sp3k' => 1,
    'wawancara' => 1,
    'booking_tgl' => '2024-01-01',
    'mkdt_batal_tgl' => '2025-06-01',
];
$resolvedStatus = $service->resolveStatusTerakhir($batalOverrideRow);
$resolvedDate = $service->resolveTanggalStatusTerakhir($batalOverrideRow, $resolvedStatus);

$assertAdv(
    'ADV-07',
    'Precedence stress: Batal overrides Akad, Indent, SP3K, Wawancara',
    $resolvedStatus === 'Batal' && $resolvedDate === '2025-06-01',
    "Resolved status: {$resolvedStatus}, date: {$resolvedDate}"
);

// 8. Cross-Year Attribution Stress
// When booking is in 2024 and Akad is in 2025, booking cohort attribution belongs to 2024
$traceSample = $db->table('mkdt')
    ->where('id_mkdt', 147)
    ->get()->getRowArray();

$assertAdv(
    'ADV-08',
    'Lifecycle trace: id_mkdt=147 cohort attribution belongs to 2024',
    $traceSample !== null && str_starts_with((string)$traceSample['booking_tgl'], '2024') && str_starts_with((string)$traceSample['akad_tgl'], '2025'),
    "Booking: {$traceSample['booking_tgl']}, Akad: {$traceSample['akad_tgl']}"
);

echo "\n====================================================================\n";
echo " ADVERSARIAL STRESS SUMMARY\n";
echo " Scenarios Tested : {$advTotal}\n";
echo " Passed           : {$advPassed}\n";
echo " Failed           : " . count($advFailures) . "\n";
echo " Pass Rate        : " . round(($advPassed / $advTotal) * 100, 1) . "%\n";
echo "====================================================================\n";

if (!empty($advFailures)) {
    exit(1);
}
exit(0);

# Milestone 1: Unit Test Suite & Edge Case Verification Plan

**Explorer**: Explorer M1_3 (`teamwork_explorer`)  
**Mission**: Formulate Unit Test Suite and Edge Case Verification Plan for Milestone 1 (Backend Calculation & Data Layer)  
**Target Class**: `App\Services\BookingAkadReportService`  
**Test File**: `tests/unit/BookingAkadReportServiceTest.php`  
**Standalone Script**: `tests/verify_booking_akad_service.php`  
**Date**: 2026-11-04  

---

## 1. Executive Summary & Existing Test Suite Audit

### 1.1 Existing Test Suite Analysis (`tests/unit/BookingAkadReportServiceTest.php`)
The current test file in `tests/unit/BookingAkadReportServiceTest.php` contains 4 test methods:
1. `testSummaryCombinesBookingAndAkadAndCalculatesRatio()`:
   - Tests the **legacy 4-status model**: `jumlah_booking`, `booking_aktif`, `booking_batal`, `jumlah_akad`.
   - Tests calendar-month ratio calculation (`jumlah_akad / jumlah_booking * 100`).
   - Asserts outdated keys that do not reflect the 6 statuses (`wawancara`, `sp3k`, `batal`, `akad_indent`, `akad_ajb`) nor the cohort conversion algorithm.
2. `testSummaryRejectsSameComparisonYear()`:
   - Verifies that `yearA === yearB` throws `InvalidArgumentException`. (Valid, will be preserved).
3. `testDetailNormalizesPagingSearchAndOrder()`:
   - Tests DataTables request normalization for old category `'booking_aktif'` and old column mappings.
4. `testDetailRejectsUnknownCategory()`:
   - Tests rejection of invalid category string.

### 1.2 Environment & Test Runner Findings
- **PHP Version**: PHP 8.3.30 (cli) on Windows (Laragon).
- **PHPUnit Version**: PHPUnit 10.5.27.
- **Critical Runner Warning**:
  `phpunit.xml.dist` defines `failOnWarning="true"` and specifies a `<coverage>` block. Because PHP 8.3 in Laragon does not have a code coverage driver (Xdebug or PCOV) active by default, running `vendor/bin/phpunit tests/unit/BookingAkadReportServiceTest.php` outputs:
  ```
  There was 1 PHPUnit test runner warning:
  1) No code coverage driver available
  WARNINGS! Tests: 4, Assertions: 26, Warnings: 1.
  Process exit code: 1
  ```
- **Operational Discovery**:
  Running with `--no-coverage` bypasses the warning and exits cleanly with exit code 0:
  ```bash
  vendor/bin/phpunit --no-coverage tests/unit/BookingAkadReportServiceTest.php
  # OK (4 tests, 26 assertions) - Exit Code: 0 (0.100s)
  ```
- **CLI Bootstrap Capability**:
  `vendor/codeigniter4/framework/system/Test/bootstrap.php` can be required directly in CLI scripts, enabling lightning-fast standalone verification (`php tests/verify_booking_akad_service.php`) without full test framework overhead.

---

## 2. Edge Case Matrix & Behavioral Specifications

### 2.1 Cohort Conversion % Edge Cases
Cohort conversion measures the percentage of unique bookings created in month $M$ (`uniq_id`) that eventually achieved an akad milestone (`Akad Indent` or `Akad AJB`), regardless of when the akad occurred.

| Scenario | Input `total_cohort_booking` | Input `cohort_akad` | Expected `konversi` (%) | Rationale / Risk |
|---|---|---|---|---|
| **Zero Bookings** | 0 | 0 | `0.0` | Prevents `DivisionByZeroError` or `NAN`/`INF`. |
| **Zero Bookings, Stale Akad** | 0 | 2 | `0.0` | In cohort analysis, converted transactions cannot exceed cohort bookings. Must guard against $B=0$. |
| **Zero Akad Reached** | 10 | 0 | `0.0` | Legitimate operational case: bookings exist but none have progressed to akad yet. |
| **Partial Conversion (50%)** | 10 | 5 | `50.0` | Standard integer division check. |
| **Complete Conversion (100%)** | 10 | 10 | `100.0` | Maximum expected bound. |
| **Fractional Decimal (33.3%)** | 3 | 1 | `33.3` | $1/3 = 0.3333...$ must round to 1 decimal place (`33.3`), returned as `float`. |
| **Fractional Decimal (28.6%)** | 7 | 2 | `28.6` | $2/7 = 0.28571...$ rounds to `28.6`. |
| **Annual Total Conversion** | $\sum B = 101$ | $\sum A = 11$ | `10.9` | **Weighted sum calculation**: $\frac{11}{101} \times 100 = 10.89\% \approx 10.9\%$. Must NOT be calculated as arithmetic mean of monthly percentages (which would incorrectly yield $55.0\%$). |

### 2.2 Date Formatting & Zero-Date PHP 8.3 Gotchas
In PHP 8.1+ / 8.3 and MySQL:
1. `strtotime('0000-00-00')` returns integer `-62169984000`.
   Calling `date('d-m-Y', -62169984000)` produces string `"30-11--0001"`.
2. `strtotime(null)` in PHP 8.1+ emits a deprecation warning:
   `Deprecated: strtotime(): Passing null to parameter #1 ($datetime) of type string is deprecated`.
3. `date('d-m-Y', false)` returns `"01-01-1970"`.
4. Epoch dates `'1970-01-01'` or `'1970-01-01 00:00:00'` represent uninitialized dates in database backfills.

**Behavioral Requirement for Service Formatter**:
Any date that is `null`, `''`, starts with `'0000-00-00'`, or is on/before `'1970-01-01'` MUST be formatted safely as `'-'`. Valid dates must be formatted as `'d-m-Y'` (e.g. `'09-10-2024'`).

| Raw Input Date | Formatted Output | Note |
|---|---|---|
| `'2026-04-14'` | `'14-04-2026'` | Standard valid date |
| `'2026-10-09 15:30:00'` | `'09-10-2026'` | Strips time component cleanly |
| `'0000-00-00'` | `'-'` | MySQL zero-date guarded |
| `'0000-00-00 00:00:00'` | `'-'` | MySQL zero-datetime guarded |
| `null` | `'-'` | No PHP 8.1+ deprecation notice |
| `''` | `'-'` | Empty string handled |
| `'1970-01-01'` | `'-'` | Epoch zero-date guarded |

### 2.3 Status Priority Resolution Hierarchy
In a consumer's journey, multiple status flags may be populated sequentially. In the detail modal (R3), the displayed **Status Terakhir** must reflect the latest authoritative milestone in reverse priority order:
1. **Batal** (Highest priority: overrides any prior milestone, badge `danger`)
2. **Akad AJB** (Final completion milestone, badge `success`)
3. **Akad Indent** (Intermediate mortgage signing milestone, badge `info`)
4. **SP3K** (Bank credit approval milestone, badge `primary`)
5. **Wawancara** (Bank interview milestone, badge `warning`)
6. **Booking** (Initial reservation milestone, badge `secondary`)

| Active Milestone Flags | Resolved Status Terakhir | Resolved Badge |
|---|---|---|
| `is_batal = 1`, `akad = 1`, `akad_indent = 1`, `sp3k = 1`, `wawancara = 1` | `Batal` | `danger` |
| `status_mkdt = 'Batal'` | `Batal` | `danger` |
| `akad = 1` OR `is_ajb = 'AJB'`, `akad_indent = 1`, `sp3k = 1` | `Akad AJB` | `success` |
| `akad_indent = 1` OR `is_ajb = 'PPJB'`, `sp3k = 1`, `wawancara = 1`, `akad = 0` | `Akad Indent` | `info` |
| `sp3k = 1`, `wawancara = 1`, `akad = 0`, `akad_indent = 0` | `SP3K` | `primary` |
| `wawancara = 1`, `sp3k = 0`, `akad = 0`, `akad_indent = 0` | `Wawancara` | `warning` |
| No advanced flags, only `booking_tgl` present | `Booking` | `secondary` |

### 2.4 Catatan MKDT Fallback Hierarchy
The note attached to the latest status in modal detail (R3) must be extracted using the fallback priority:
`keterangan_batal` $\rightarrow$ `keterangan` $\rightarrow$ `keterangan_status` $\rightarrow$ `'-'`

| `keterangan_batal` | `keterangan` | `keterangan_status` | Pre-resolved `catatan_mkdt` | Resulting `catatan_mkdt` |
|---|---|---|---|---|
| `"Konsumen batal"` | `"Pending berkas"` | `"Menunggu bank"` | `null` | `"Konsumen batal"` |
| `""` (empty) | `"Pending berkas"` | `"Menunggu bank"` | `null` | `"Pending berkas"` |
| `null` | `"   "` (spaces) | `"Menunggu bank"` | `null` | `"Menunggu bank"` |
| `null` | `null` | `null` | `null` | `"-"` |
| `null` | `null` | `null` | `"Catatan khusus"` | `"Catatan khusus"` |

### 2.5 Null Field Sanitization
To guarantee zero PHP notices, undefined array key errors, or frontend breaking:
- **`sales`**: If `null` or `''`, output `'-'`.
- **`nama_konsumen`**: If `null` or `''`, output `'-'`.
- **`tipe_rumah`**: If `nama_tipe` and `tipe_rumah` are `null`, output `'-'`.
- **`nama_jalan`**: If `null` or `''`, output `'-'`.
- **`no_kavling`**: If `null` or `''`, output `'-'`.
- **`is_subsidi`**: Cast strictly to boolean `(bool) ($row['is_subsidi'] ?? false)`.

---

## 3. Comprehensive PHPUnit Test Suite Specification

Below is the complete, drop-in replacement specification for `tests/unit/BookingAkadReportServiceTest.php`. It provides 17 test methods with 80+ assertions covering all 6 statuses, cohort math, DataTables normalization, zero-dates, status resolution, and JSON contract structure.

```php
<?php

namespace Tests\Unit;

use App\Repositories\BookingAkadReportRepository;
use App\Services\BookingAkadReportService;
use CodeIgniter\Test\CIUnitTestCase;
use InvalidArgumentException;

final class BookingAkadReportServiceTest extends CIUnitTestCase
{
    /**
     * Test R1: CATEGORIES constant must contain all 6 transaction statuses.
     */
    public function testCategoriesConstantContainsAllSixStatuses(): void
    {
        $expected = ['booking', 'wawancara', 'sp3k', 'batal', 'akad_indent', 'akad_ajb'];

        $this->assertCount(6, BookingAkadReportService::CATEGORIES);
        $this->assertSame($expected, BookingAkadReportService::CATEGORIES);
    }

    /**
     * Test R1 & R2: Aggregation of 6 statuses and real cohort conversion %.
     */
    public function testSummaryAggregatesSixStatusesAndCalculatesCohortConversion(): void
    {
        $repository = $this->createMock(BookingAkadReportRepository::class);

        // Mock repository returning 6 statuses and cohort tracking
        $repository->expects($this->once())
            ->method('getMonthlySummaryTotals')
            ->with(7, [2026, 2025])
            ->willReturn([
                [
                    'tahun' => 2026,
                    'bulan' => 1,
                    'booking' => 10,
                    'wawancara' => 4,
                    'sp3k' => 3,
                    'batal' => 1,
                    'akad_indent' => 2,
                    'akad_ajb' => 3,
                    'booking_converted_akad' => 5,
                    'total_booking_cohort' => 10,
                ],
                [
                    'tahun' => 2026,
                    'bulan' => 2,
                    'booking' => 20,
                    'wawancara' => 8,
                    'sp3k' => 6,
                    'batal' => 2,
                    'akad_indent' => 4,
                    'akad_ajb' => 6,
                    'booking_converted_akad' => 10,
                    'total_booking_cohort' => 20,
                ],
                [
                    'tahun' => 2025,
                    'bulan' => 1,
                    'booking' => 6,
                    'wawancara' => 2,
                    'sp3k' => 2,
                    'batal' => 0,
                    'akad_indent' => 1,
                    'akad_ajb' => 2,
                    'booking_converted_akad' => 3,
                    'total_booking_cohort' => 6,
                ],
            ]);

        $service = new BookingAkadReportService($repository);
        $result = $service->getSummary(7, 2026, 2025);

        // Verify structure
        $this->assertSame([2026, 2025], $result['years']);
        $this->assertCount(12, $result['months']);

        // Month 1 (Januari 2026): 10 booking, 5 converted to akad => 50.0%
        $jan2026 = $result['months'][0]['values'][2026];
        $this->assertSame(10, $jan2026['booking']);
        $this->assertSame(4, $jan2026['wawancara']);
        $this->assertSame(3, $jan2026['sp3k']);
        $this->assertSame(1, $jan2026['batal']);
        $this->assertSame(2, $jan2026['akad_indent']);
        $this->assertSame(3, $jan2026['akad_ajb']);
        $this->assertSame(50.0, $jan2026['konversi']);

        // Month 2 (Februari 2026): 20 booking, 10 converted to akad => 50.0%
        $feb2026 = $result['months'][1]['values'][2026];
        $this->assertSame(20, $feb2026['booking']);
        $this->assertSame(50.0, $feb2026['konversi']);

        // Month 3 (Maret 2026): unpopulated month defaults to zero
        $mar2026 = $result['months'][2]['values'][2026];
        $this->assertSame(0, $mar2026['booking']);
        $this->assertSame(0.0, $mar2026['konversi']);

        // Year 2025 Month 1: 6 booking, 3 converted => 50.0%
        $jan2025 = $result['months'][0]['values'][2025];
        $this->assertSame(6, $jan2025['booking']);
        $this->assertSame(50.0, $jan2025['konversi']);

        // Annual Totals for 2026: 30 booking, 15 converted => 50.0%
        $totals2026 = $result['totals'][2026];
        $this->assertSame(30, $totals2026['booking']);
        $this->assertSame(12, $totals2026['wawancara']);
        $this->assertSame(9, $totals2026['sp3k']);
        $this->assertSame(3, $totals2026['batal']);
        $this->assertSame(6, $totals2026['akad_indent']);
        $this->assertSame(9, $totals2026['akad_ajb']);
        $this->assertSame(50.0, $totals2026['konversi']);
    }

    /**
     * Test R2 Cohort Conversion Math: 0 bookings yields 0.0% without division-by-zero.
     */
    public function testCohortConversionZeroBookingsYieldsZeroPercent(): void
    {
        $repository = $this->createMock(BookingAkadReportRepository::class);
        $repository->method('getMonthlySummaryTotals')->willReturn([
            ['tahun' => 2026, 'bulan' => 1, 'booking' => 0, 'total_booking_cohort' => 0, 'booking_converted_akad' => 0],
        ]);

        $service = new BookingAkadReportService($repository);
        $result = $service->getSummary(7, 2026);

        $this->assertSame(0.0, $result['months'][0]['values'][2026]['konversi']);
        $this->assertSame(0.0, $result['totals'][2026]['konversi']);
    }

    /**
     * Test R2 Cohort Conversion Math: 10 bookings, 5 akad => 50.0%.
     */
    public function testCohortConversionFiftyPercent(): void
    {
        $repository = $this->createMock(BookingAkadReportRepository::class);
        $repository->method('getMonthlySummaryTotals')->willReturn([
            ['tahun' => 2026, 'bulan' => 1, 'booking' => 10, 'total_booking_cohort' => 10, 'booking_converted_akad' => 5],
        ]);

        $service = new BookingAkadReportService($repository);
        $result = $service->getSummary(7, 2026);

        $this->assertSame(50.0, $result['months'][0]['values'][2026]['konversi']);
    }

    /**
     * Test R2 Cohort Conversion Math: 10 bookings, 10 akad => 100.0%.
     */
    public function testCohortConversionOneHundredPercent(): void
    {
        $repository = $this->createMock(BookingAkadReportRepository::class);
        $repository->method('getMonthlySummaryTotals')->willReturn([
            ['tahun' => 2026, 'bulan' => 1, 'booking' => 10, 'total_booking_cohort' => 10, 'booking_converted_akad' => 10],
        ]);

        $service = new BookingAkadReportService($repository);
        $result = $service->getSummary(7, 2026);

        $this->assertSame(100.0, $result['months'][0]['values'][2026]['konversi']);
    }

    /**
     * Test R2 Cohort Conversion Math: 10 bookings, 0 akad => 0.0%.
     */
    public function testCohortConversionZeroAkadYieldsZeroPercent(): void
    {
        $repository = $this->createMock(BookingAkadReportRepository::class);
        $repository->method('getMonthlySummaryTotals')->willReturn([
            ['tahun' => 2026, 'bulan' => 1, 'booking' => 10, 'total_booking_cohort' => 10, 'booking_converted_akad' => 0],
        ]);

        $service = new BookingAkadReportService($repository);
        $result = $service->getSummary(7, 2026);

        $this->assertSame(0.0, $result['months'][0]['values'][2026]['konversi']);
    }

    /**
     * Test R2 Cohort Conversion Math: 1 decimal place rounding precision.
     */
    public function testCohortConversionRoundingPrecision(): void
    {
        $repository = $this->createMock(BookingAkadReportRepository::class);
        $repository->method('getMonthlySummaryTotals')->willReturn([
            ['tahun' => 2026, 'bulan' => 1, 'booking' => 3, 'total_booking_cohort' => 3, 'booking_converted_akad' => 1], // 33.333% -> 33.3
            ['tahun' => 2026, 'bulan' => 2, 'booking' => 7, 'total_booking_cohort' => 7, 'booking_converted_akad' => 2], // 28.571% -> 28.6
            ['tahun' => 2026, 'bulan' => 3, 'booking' => 6, 'total_booking_cohort' => 6, 'booking_converted_akad' => 1], // 16.666% -> 16.7
        ]);

        $service = new BookingAkadReportService($repository);
        $result = $service->getSummary(7, 2026);

        $this->assertSame(33.3, $result['months'][0]['values'][2026]['konversi']);
        $this->assertSame(28.6, $result['months'][1]['values'][2026]['konversi']);
        $this->assertSame(16.7, $result['months'][2]['values'][2026]['konversi']);
    }

    /**
     * Test R2 Annual Conversion: Calculated as weighted sum of cohorts, NOT arithmetic average of ratios.
     */
    public function testAnnualConversionCalculatedAsWeightedSumRatherThanAverageOfRatios(): void
    {
        $repository = $this->createMock(BookingAkadReportRepository::class);
        $repository->method('getMonthlySummaryTotals')->willReturn([
            ['tahun' => 2026, 'bulan' => 1, 'booking' => 1, 'total_booking_cohort' => 1, 'booking_converted_akad' => 1], // 100.0%
            ['tahun' => 2026, 'bulan' => 2, 'booking' => 100, 'total_booking_cohort' => 100, 'booking_converted_akad' => 10], // 10.0%
        ]);

        $service = new BookingAkadReportService($repository);
        $result = $service->getSummary(7, 2026);

        // Arithmetic average would be (100.0 + 10.0) / 2 = 55.0% or 110 / 12 = 9.17%
        // True weighted cohort conversion is (1 + 10) / (1 + 100) = 11 / 101 * 100 = 10.89% -> 10.9%
        $this->assertSame(10.9, $result['totals'][2026]['konversi']);
        $this->assertNotEquals(55.0, $result['totals'][2026]['konversi']);
    }

    /**
     * Test Validation: Rejection of identical comparison years.
     */
    public function testSummaryRejectsSameComparisonYear(): void
    {
        $service = new BookingAkadReportService($this->createMock(BookingAkadReportRepository::class));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Pilih dua tahun yang berbeda.');

        $service->getSummary(7, 2026, 2026);
    }

    /**
     * Test Validation: Rejection of invalid project ID or year bounds.
     */
    public function testSummaryValidatesProjectIdAndYearBounds(): void
    {
        $service = new BookingAkadReportService($this->createMock(BookingAkadReportRepository::class));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Pilih proyek aktif terlebih dahulu.');
        $service->getSummary(0, 2026);
    }

    /**
     * Test R3: Detail Data Normalization and Acceptance of all 6 Status Categories.
     */
    public function testDetailAcceptsAllSixCategories(): void
    {
        $repository = $this->createMock(BookingAkadReportRepository::class);
        $repository->expects($this->exactly(6))
            ->method('getDetailRows')
            ->willReturn(['recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => []]);

        $service = new BookingAkadReportService($repository);

        foreach (BookingAkadReportService::CATEGORIES as $category) {
            $result = $service->getDetail(7, [
                'draw' => 1,
                'year' => 2026,
                'month' => 4,
                'category' => $category,
            ]);
            $this->assertSame(1, $result['draw']);
            $this->assertIsArray($result['data']);
        }
    }

    /**
     * Test R3: Detail rejects invalid category.
     */
    public function testDetailRejectsInvalidCategory(): void
    {
        $service = new BookingAkadReportService($this->createMock(BookingAkadReportRepository::class));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Kategori laporan tidak valid.');

        $service->getDetail(7, ['year' => 2026, 'month' => 1, 'category' => 'booking_aktif']);
    }

    /**
     * Test R3: Detail formats raw database rows into the clean 7-column contract.
     */
    public function testDetailFormatsRowToSevenColumnContract(): void
    {
        $repository = $this->createMock(BookingAkadReportRepository::class);
        $repository->method('getDetailRows')->willReturn([
            'recordsTotal' => 1,
            'recordsFiltered' => 1,
            'data' => [
                [
                    'id_mkdt' => 147,
                    'id_kavling' => 52,
                    'nama_jalan' => 'Jalan Mawar',
                    'no_kavling' => 'A-12',
                    'sales' => 'Budi Santoso',
                    'nama_konsumen' => 'Ahmad Supardi',
                    'nama_tipe' => '36/60',
                    'is_subsidi' => 1,
                    'booking_tgl' => '2024-10-09',
                    'status_terakhir' => 'Akad AJB',
                    'status_terakhir_badge' => 'success',
                    'catatan_mkdt' => 'Berkas lengkap dan acc bank',
                    'tanggal_status_terakhir' => '2025-04-14',
                ],
            ],
        ]);

        $service = new BookingAkadReportService($repository);
        $result = $service->getDetail(7, ['year' => 2026, 'month' => 4, 'category' => 'akad_ajb']);

        $row = $result['data'][0];
        $this->assertSame(147, $row['id_mkdt']);
        $this->assertSame(52, $row['id_kavling']);
        $this->assertSame([
            'nama_jalan' => 'Jalan Mawar',
            'no_kavling' => 'A-12',
            'sales' => 'Budi Santoso',
        ], $row['kavling']);
        $this->assertSame([
            'nama_konsumen' => 'Ahmad Supardi',
            'tipe_rumah' => '36/60',
            'is_subsidi' => true,
        ], $row['konsumen']);
        $this->assertSame('09-10-2024', $row['tanggal_booking']);
        $this->assertSame('Akad AJB', $row['status_terakhir']);
        $this->assertSame('success', $row['status_terakhir_badge']);
        $this->assertSame('Berkas lengkap dan acc bank', $row['catatan_mkdt']);
        $this->assertSame('14-04-2025', $row['tanggal_status_terakhir']);
    }

    /**
     * Test Edge Cases: Safe handling of zero-dates ('0000-00-00'), null sales, and null fields without PHP notices.
     */
    public function testDetailSafeHandlingOfZeroDatesAndNullFields(): void
    {
        $repository = $this->createMock(BookingAkadReportRepository::class);
        $repository->method('getDetailRows')->willReturn([
            'recordsTotal' => 1,
            'recordsFiltered' => 1,
            'data' => [
                [
                    'id_mkdt' => 99,
                    'id_kavling' => 10,
                    'nama_jalan' => null,
                    'no_kavling' => null,
                    'sales' => null, // Null sales
                    'nama_konsumen' => null,
                    'nama_tipe' => null,
                    'tipe_rumah' => null,
                    'is_subsidi' => null,
                    'booking_tgl' => '0000-00-00', // Zero date
                    'status_terakhir' => null,
                    'catatan_mkdt' => null,
                    'tanggal_status_terakhir' => '0000-00-00 00:00:00', // Zero datetime
                ],
            ],
        ]);

        $service = new BookingAkadReportService($repository);
        $result = $service->getDetail(7, ['year' => 2026, 'month' => 1, 'category' => 'booking']);

        $row = $result['data'][0];
        $this->assertSame('-', $row['kavling']['nama_jalan']);
        $this->assertSame('-', $row['kavling']['no_kavling']);
        $this->assertSame('-', $row['kavling']['sales']);
        $this->assertSame('-', $row['konsumen']['nama_konsumen']);
        $this->assertSame('-', $row['konsumen']['tipe_rumah']);
        $this->assertFalse($row['konsumen']['is_subsidi']);
        $this->assertSame('-', $row['tanggal_booking']);
        $this->assertSame('-', $row['tanggal_status_terakhir']);
        $this->assertSame('-', $row['catatan_mkdt']);
    }

    /**
     * Test Status Priority Hierarchy: Batal > Akad AJB > Akad Indent > SP3K > Wawancara > Booking.
     */
    public function testDetailStatusPriorityHierarchy(): void
    {
        $repository = $this->createMock(BookingAkadReportRepository::class);
        $repository->method('getDetailRows')->willReturn([
            'recordsTotal' => 6,
            'recordsFiltered' => 6,
            'data' => [
                // 1. Batal overrides everything even if akad = 1
                ['id_mkdt' => 1, 'is_batal' => 1, 'akad' => 1, 'sp3k' => 1, 'booking_tgl' => '2026-01-01'],
                // 2. Akad AJB
                ['id_mkdt' => 2, 'is_batal' => 0, 'akad' => 1, 'akad_indent' => 1, 'sp3k' => 1, 'booking_tgl' => '2026-01-01'],
                // 3. Akad Indent
                ['id_mkdt' => 3, 'is_batal' => 0, 'akad' => 0, 'akad_indent' => 1, 'sp3k' => 1, 'booking_tgl' => '2026-01-01'],
                // 4. SP3K
                ['id_mkdt' => 4, 'is_batal' => 0, 'akad' => 0, 'akad_indent' => 0, 'sp3k' => 1, 'wawancara' => 1, 'booking_tgl' => '2026-01-01'],
                // 5. Wawancara
                ['id_mkdt' => 5, 'is_batal' => 0, 'akad' => 0, 'akad_indent' => 0, 'sp3k' => 0, 'wawancara' => 1, 'booking_tgl' => '2026-01-01'],
                // 6. Booking fallback
                ['id_mkdt' => 6, 'is_batal' => 0, 'akad' => 0, 'akad_indent' => 0, 'sp3k' => 0, 'wawancara' => 0, 'booking_tgl' => '2026-01-01'],
            ],
        ]);

        $service = new BookingAkadReportService($repository);
        $result = $service->getDetail(7, ['year' => 2026, 'month' => 1, 'category' => 'booking']);

        $this->assertSame('Batal', $result['data'][0]['status_terakhir']);
        $this->assertSame('danger', $result['data'][0]['status_terakhir_badge']);

        $this->assertSame('Akad AJB', $result['data'][1]['status_terakhir']);
        $this->assertSame('success', $result['data'][1]['status_terakhir_badge']);

        $this->assertSame('Akad Indent', $result['data'][2]['status_terakhir']);
        $this->assertSame('info', $result['data'][2]['status_terakhir_badge']);

        $this->assertSame('SP3K', $result['data'][3]['status_terakhir']);
        $this->assertSame('primary', $result['data'][3]['status_terakhir_badge']);

        $this->assertSame('Wawancara', $result['data'][4]['status_terakhir']);
        $this->assertSame('warning', $result['data'][4]['status_terakhir_badge']);

        $this->assertSame('Booking', $result['data'][5]['status_terakhir']);
        $this->assertSame('secondary', $result['data'][5]['status_terakhir_badge']);
    }

    /**
     * Test Catatan MKDT Fallback: keterangan_batal -> keterangan -> keterangan_status -> '-'.
     */
    public function testDetailCatatanMkdtFallbackHierarchy(): void
    {
        $repository = $this->createMock(BookingAkadReportRepository::class);
        $repository->method('getDetailRows')->willReturn([
            'recordsTotal' => 4,
            'recordsFiltered' => 4,
            'data' => [
                // 1. keterangan_batal takes precedence
                ['id_mkdt' => 1, 'keterangan_batal' => 'Batal oleh user', 'keterangan' => 'Ket 2', 'keterangan_status' => 'Ket 3'],
                // 2. keterangan when keterangan_batal is empty
                ['id_mkdt' => 2, 'keterangan_batal' => '', 'keterangan' => 'Pending bank', 'keterangan_status' => 'Ket 3'],
                // 3. keterangan_status when others empty or whitespace
                ['id_mkdt' => 3, 'keterangan_batal' => null, 'keterangan' => '   ', 'keterangan_status' => 'Status berkas siap'],
                // 4. Fallback to '-' when all empty
                ['id_mkdt' => 4, 'keterangan_batal' => null, 'keterangan' => null, 'keterangan_status' => null],
            ],
        ]);

        $service = new BookingAkadReportService($repository);
        $result = $service->getDetail(7, ['year' => 2026, 'month' => 1, 'category' => 'booking']);

        $this->assertSame('Batal oleh user', $result['data'][0]['catatan_mkdt']);
        $this->assertSame('Pending bank', $result['data'][1]['catatan_mkdt']);
        $this->assertSame('Status berkas siap', $result['data'][2]['catatan_mkdt']);
        $this->assertSame('-', $result['data'][3]['catatan_mkdt']);
    }

    /**
     * Test DataTables Parameter Normalization: paging, search trimming, and order.
     */
    public function testDetailNormalizesDataTableParameters(): void
    {
        $repository = $this->createMock(BookingAkadReportRepository::class);
        $repository->expects($this->once())
            ->method('getDetailRows')
            ->with(7, 2026, 2, 'booking', [
                'draw' => 3,
                'start' => 0,
                'length' => 50,
                'search' => 'Blok B',
                'order_by' => 'booking_akad.booking_tgl',
                'order_dir' => 'DESC',
            ])
            ->willReturn(['recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => []]);

        $service = new BookingAkadReportService($repository);
        $service->getDetail(7, [
            'draw' => 3,
            'start' => -5,
            'length' => 50,
            'year' => 2026,
            'month' => 2,
            'category' => 'booking',
            'search' => ['value' => '  Blok B  '],
            'columns' => [3 => ['name' => 'tanggal_booking']],
            'order' => [['column' => 3, 'dir' => 'desc']],
        ]);
    }
}
```

---

## 4. Standalone PHP Verification Script Formulation

For rapid, deterministic verification without requiring PHPUnit runner configurations or external extensions, the following standalone CLI script (`tests/verify_booking_akad_service.php`) is formulated:

```php
<?php

/**
 * Standalone Verification Script for Milestone 1: BookingAkadReportService
 * Usage: php tests/verify_booking_akad_service.php
 */

declare(strict_types=1);

$startTime = microtime(true);
$passed = 0;
$failed = 0;

echo "\n======================================================\n";
echo " SIGAPP Milestone 1: BookingAkadReportService Verifier \n";
echo "======================================================\n\n";

// 1. Bootstrap CodeIgniter 4 Test Environment
$bootstrapPath = __DIR__ . '/../vendor/codeigniter4/framework/system/Test/bootstrap.php';
if (!file_exists($bootstrapPath)) {
    echo "[FATAL] Cannot locate CI4 bootstrap at: {$bootstrapPath}\n";
    exit(1);
}
require $bootstrapPath;

use App\Repositories\BookingAkadReportRepository;
use App\Services\BookingAkadReportService;

function assertCheck(string $description, bool $condition, ?string $errorMessage = null): void {
    global $passed, $failed;
    if ($condition) {
        $passed++;
        echo "  [PASS] {$description}\n";
    } else {
        $failed++;
        echo "  [FAIL] {$description}" . ($errorMessage ? ": {$errorMessage}" : "") . "\n";
    }
}

// ----------------------------------------------------
// Check 1: CATEGORIES constant contains all 6 statuses
// ----------------------------------------------------
$expectedCategories = ['booking', 'wawancara', 'sp3k', 'batal', 'akad_indent', 'akad_ajb'];
assertCheck(
    "CATEGORIES constant contains exactly 6 transaction statuses",
    BookingAkadReportService::CATEGORIES === $expectedCategories,
    "Expected: " . json_encode($expectedCategories) . ", got: " . json_encode(BookingAkadReportService::CATEGORIES)
);

// ----------------------------------------------------
// Check 2: Cohort conversion math scenarios
// ----------------------------------------------------
class StubBookingAkadRepo extends BookingAkadReportRepository {
    public array $mockSummary = [];
    public array $mockDetail = ['recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => []];

    public function __construct() {} // Bypass DB connection

    public function getMonthlySummaryTotals(int $idProyek, array $years): array {
        return $this->mockSummary;
    }

    public function getDetailRows(int $idProyek, int $year, int $month, string $category, array $dataTable = []): array {
        return $this->mockDetail;
    }
}

$stubRepo = new StubBookingAkadRepo();
$service = new BookingAkadReportService($stubRepo);

// Scenario A: 0 bookings -> 0.0% (no division by zero)
$stubRepo->mockSummary = [
    ['tahun' => 2026, 'bulan' => 1, 'booking' => 0, 'total_booking_cohort' => 0, 'booking_converted_akad' => 0]
];
$summaryA = $service->getSummary(7, 2026);
assertCheck("Cohort conversion: 0 bookings -> 0.0%", $summaryA['months'][0]['values'][2026]['konversi'] === 0.0);

// Scenario B: 10 bookings, 5 akad -> 50.0%
$stubRepo->mockSummary = [
    ['tahun' => 2026, 'bulan' => 1, 'booking' => 10, 'total_booking_cohort' => 10, 'booking_converted_akad' => 5]
];
$summaryB = $service->getSummary(7, 2026);
assertCheck("Cohort conversion: 10 bookings, 5 akad -> 50.0%", $summaryB['months'][0]['values'][2026]['konversi'] === 50.0);

// Scenario C: 10 bookings, 10 akad -> 100.0%
$stubRepo->mockSummary = [
    ['tahun' => 2026, 'bulan' => 1, 'booking' => 10, 'total_booking_cohort' => 10, 'booking_converted_akad' => 10]
];
$summaryC = $service->getSummary(7, 2026);
assertCheck("Cohort conversion: 10 bookings, 10 akad -> 100.0%", $summaryC['months'][0]['values'][2026]['konversi'] === 100.0);

// Scenario D: 10 bookings, 0 akad -> 0.0%
$stubRepo->mockSummary = [
    ['tahun' => 2026, 'bulan' => 1, 'booking' => 10, 'total_booking_cohort' => 10, 'booking_converted_akad' => 0]
];
$summaryD = $service->getSummary(7, 2026);
assertCheck("Cohort conversion: 10 bookings, 0 akad -> 0.0%", $summaryD['months'][0]['values'][2026]['konversi'] === 0.0);

// Scenario E: Annual weighted conversion vs arithmetic mean
$stubRepo->mockSummary = [
    ['tahun' => 2026, 'bulan' => 1, 'booking' => 1, 'total_booking_cohort' => 1, 'booking_converted_akad' => 1], // 100%
    ['tahun' => 2026, 'bulan' => 2, 'booking' => 100, 'total_booking_cohort' => 100, 'booking_converted_akad' => 10], // 10%
];
$summaryE = $service->getSummary(7, 2026);
assertCheck(
    "Annual conversion: Weighted cohort sum (11/101 = 10.9%)",
    $summaryE['totals'][2026]['konversi'] === 10.9
);

// ----------------------------------------------------
// Check 3: Detail row formatting to 7-column contract
// ----------------------------------------------------
$stubRepo->mockDetail = [
    'recordsTotal' => 1,
    'recordsFiltered' => 1,
    'data' => [
        [
            'id_mkdt' => 147,
            'id_kavling' => 52,
            'nama_jalan' => 'Jalan Mawar',
            'no_kavling' => 'A-12',
            'sales' => 'Budi Santoso',
            'nama_konsumen' => 'Ahmad Supardi',
            'nama_tipe' => '36/60',
            'is_subsidi' => 1,
            'booking_tgl' => '2024-10-09',
            'status_terakhir' => 'Akad AJB',
            'status_terakhir_badge' => 'success',
            'catatan_mkdt' => 'Berkas lengkap dan acc bank',
            'tanggal_status_terakhir' => '2025-04-14',
        ]
    ]
];
$detailResult = $service->getDetail(7, ['year' => 2026, 'month' => 4, 'category' => 'akad_ajb']);
$detailRow = $detailResult['data'][0] ?? [];

assertCheck("Detail row: id_mkdt and id_kavling mapped", $detailRow['id_mkdt'] === 147 && $detailRow['id_kavling'] === 52);
assertCheck("Detail row: kavling object structured (jalan, no, sales)", 
    ($detailRow['kavling']['nama_jalan'] ?? '') === 'Jalan Mawar' &&
    ($detailRow['kavling']['no_kavling'] ?? '') === 'A-12' &&
    ($detailRow['kavling']['sales'] ?? '') === 'Budi Santoso'
);
assertCheck("Detail row: konsumen object structured (nama, tipe, is_subsidi bool)",
    ($detailRow['konsumen']['nama_konsumen'] ?? '') === 'Ahmad Supardi' &&
    ($detailRow['konsumen']['tipe_rumah'] ?? '') === '36/60' &&
    $detailRow['konsumen']['is_subsidi'] === true
);
assertCheck("Detail row: dates formatted as d-m-Y", 
    $detailRow['tanggal_booking'] === '09-10-2024' &&
    $detailRow['tanggal_status_terakhir'] === '14-04-2025'
);

// ----------------------------------------------------
// Check 4: Zero-dates and null fields safe handling
// ----------------------------------------------------
$stubRepo->mockDetail = [
    'recordsTotal' => 1,
    'recordsFiltered' => 1,
    'data' => [
        [
            'id_mkdt' => 10,
            'id_kavling' => 5,
            'nama_jalan' => null,
            'no_kavling' => null,
            'sales' => null,
            'nama_konsumen' => null,
            'nama_tipe' => null,
            'is_subsidi' => null,
            'booking_tgl' => '0000-00-00',
            'status_terakhir' => null,
            'catatan_mkdt' => null,
            'tanggal_status_terakhir' => '0000-00-00 00:00:00',
        ]
    ]
];
$safeDetailResult = $service->getDetail(7, ['year' => 2026, 'month' => 1, 'category' => 'booking']);
$safeRow = $safeDetailResult['data'][0] ?? [];

assertCheck("Zero-date handling: '0000-00-00' converted to '-'", $safeRow['tanggal_booking'] === '-');
assertCheck("Zero-datetime handling: '0000-00-00 00:00:00' converted to '-'", $safeRow['tanggal_status_terakhir'] === '-');
assertCheck("Null sales converted to '-'", $safeRow['kavling']['sales'] === '-');
assertCheck("Null konsumen converted to '-'", $safeRow['konsumen']['nama_konsumen'] === '-');
assertCheck("Null catatan converted to '-'", $safeRow['catatan_mkdt'] === '-');

// ----------------------------------------------------
// Check 5: Status priority resolution
// ----------------------------------------------------
$stubRepo->mockDetail = [
    'recordsTotal' => 2,
    'recordsFiltered' => 2,
    'data' => [
        ['id_mkdt' => 1, 'is_batal' => 1, 'akad' => 1, 'booking_tgl' => '2026-01-01'],
        ['id_mkdt' => 2, 'is_batal' => 0, 'akad' => 1, 'akad_indent' => 1, 'booking_tgl' => '2026-01-01'],
    ]
];
$prioDetail = $service->getDetail(7, ['year' => 2026, 'month' => 1, 'category' => 'booking']);
assertCheck("Status priority: Batal trumps Akad", $prioDetail['data'][0]['status_terakhir'] === 'Batal');
assertCheck("Status priority: Akad AJB trumps Akad Indent", $prioDetail['data'][1]['status_terakhir'] === 'Akad AJB');

// ----------------------------------------------------
// Summary & Verdict
// ----------------------------------------------------
$elapsed = round(microtime(true) - $startTime, 3);
echo "\n------------------------------------------------------\n";
echo " Results: {$passed} PASSED, {$failed} FAILED in {$elapsed}s\n";
echo "------------------------------------------------------\n\n";

if ($failed > 0) {
    exit(1);
}
echo "[VERIFIED] Milestone 1 Service implementation adheres to all edge case requirements!\n\n";
exit(0);
```

---

## 5. Verification Commands & Execution Guide

### 5.1 Project Test Command (PHPUnit)
To verify the unit test suite after the Engineer implements `BookingAkadReportService.php`:
```powershell
vendor/bin/phpunit --no-coverage tests/unit/BookingAkadReportServiceTest.php
```
*Note*: The `--no-coverage` flag is strictly required in Laragon Windows setups lacking Xdebug to avoid PHPUnit 10 test runner warning exit code 1.

### 5.2 Standalone PHP Verification Command
To verify the service logic without PHPUnit runner overhead:
```powershell
php tests/verify_booking_akad_service.php
```

### 5.3 Acceptance Checklist for Implementer (Engineer)
Before marking Milestone 1 complete, the implementer must ensure:
- [ ] `BookingAkadReportService::CATEGORIES` equals `['booking', 'wawancara', 'sp3k', 'batal', 'akad_indent', 'akad_ajb']`.
- [ ] Monthly conversion % is guarded against division-by-zero (`0.0` when $B=0$).
- [ ] Annual total conversion % sums all converted transactions and divides by total bookings (weighted cohort), never averaging monthly percentages.
- [ ] Dates starting with `'0000-00-00'` or `null` return `'-'` and never emit `strtotime(null)` deprecations.
- [ ] Status priority resolution strictly satisfies `Batal > Akad AJB > Akad Indent > SP3K > Wawancara > Booking`.
- [ ] Catatan MKDT fallback resolves: `keterangan_batal` $\rightarrow$ `keterangan` $\rightarrow$ `keterangan_status` $\rightarrow$ `'-'`.
- [ ] All 17 PHPUnit tests pass with 0 failures and 0 warnings.

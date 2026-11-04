# Handoff Report: Milestone 1 — Backend Calculation & Data Layer

**Agent**: Worker M1 (`teamwork_worker`)  
**Milestone**: Milestone 1: Backend Calculation & Data Layer for Laporan Booking & Akad Overhaul  
**Date**: 2026-11-04  
**Working Directory**: `c:\Users\Salivs\Data\laragon\www\sigapp.dev\.agents\teamwork\worker_m1\`  
**Target Files (Write Ownership)**:
1. `app/Repositories/BookingAkadReportRepository.php`
2. `app/Services/BookingAkadReportService.php`
3. `tests/unit/BookingAkadReportServiceTest.php`

---

## 1. Observation

1. **Initial Red Test State**:
   Running `vendor/bin/phpunit --no-coverage tests/unit/BookingAkadReportServiceOverhaulTest.php` prior to implementation resulted in 1 expected failure:
   ```
   1) Tests\Unit\BookingAkadReportServiceOverhaulTest::testSummaryAggregatesAllSixStatusCategoriesAccurately
   Failed asserting that an array has the key 'booking'.
   C:\Users\Salivs\Data\laragon\www\sigapp.dev\tests\unit\BookingAkadReportServiceOverhaulTest.php:69
   FAILURES! Tests: 25, Assertions: 78, Failures: 1.
   ```
2. **Legacy Service Implementation**:
   In `app/Services/BookingAkadReportService.php`:
   - `CATEGORIES` was limited to `['booking', 'booking_aktif', 'booking_batal', 'akad']` (line 10).
   - `getSummary()` returned only `jumlah_booking`, `booking_aktif`, `booking_batal`, `jumlah_akad`, `rasio_akad`.
   - `getDetail()` accepted only old categories and returned flat DataTables rows without the 7-column nested schema (`kavling`, `konsumen`, `status_terakhir`, `status_terakhir_badge`, `tanggal_status_terakhir`, `catatan_mkdt`).
3. **Legacy Repository Implementation**:
   In `app/Repositories/BookingAkadReportRepository.php`:
   - Lacked `getMonthlyStatusTotals()`, `getMonthlySummaryTotals()`, and `getSummary()`.
   - `compileDetailQuery()` joined only `mkdt`, `kavling`, `jalan`, `cluster`, `proyek`, `konsumen`, `tipe` (missing `hargajual` and `tipe thj` joins).
   - Did not implement SQL status precedence or safe zero-date filtering with `> '1970-01-01'`.
4. **Live Database Ground Truth on Proyek 13 ("Sanggar Indah Palastri")**:
   - `BookingAkadReportLiveDbTest.php` confirmed: 242 transactions in Proyek 13 with 100% `uniq_id` integrity.
   - Ground truth partition: 171 Akad AJB + 18 Akad Indent + 15 SP3K + 11 Wawancara + 18 Booking + 9 Batal = 242 transactions.
   - Year 2026 cohort: 69 total bookings, 36 converted to akad (18 Indent + 18 AJB), yielding true annual cohort conversion of 52.2% ($36 / 69 \times 100 = 52.17\% \approx 52.2\%$).
5. **Post-Implementation Verification Results**:
   All 4 automated suites and the CLI suite runner pass with 0 errors and 0 warnings:
   - `vendor/bin/phpunit --no-coverage tests/unit/BookingAkadReportServiceOverhaulTest.php`: `OK (25 tests, 83 assertions)` - 0.146s.
   - `vendor/bin/phpunit --no-coverage tests/unit/BookingAkadReportServiceTest.php`: `OK (17 tests, 89 assertions)` - 0.117s.
   - `php tests/bin/run_booking_akad_suite.php`: `Total Tests: 38, Passed: 38, Failed: 0, Pass Rate: 100.0%` - 0.993s.
   - `vendor/bin/phpunit --no-coverage tests/database/BookingAkadReportLiveDbTest.php`: `OK (7 tests, 330 assertions)` - 1.142s.
   - `php spark module:sync MKDT --check-only`: `[OK] MKDT: MODULE.md ada. Exit code: 0`.

---

## 2. Logic Chain

1. **R1 (6 Status Categories Aggregation)**:
   - *Observation 1 & 2* showed that the service failed asserting the `'booking'` key because it only emitted legacy keys.
   - Updated `BookingAkadReportService::CATEGORIES` to `['booking', 'wawancara', 'sp3k', 'batal', 'akad_indent', 'akad_ajb']`.
   - Implemented `BookingAkadReportRepository::getMonthlyStatusTotals()` using single-pass SQL with `COALESCE` handling to partition every transaction into its latest milestone while grouping by `YEAR(m.booking_tgl), MONTH(m.booking_tgl)`.
   - In `BookingAkadReportService::getSummary()`, populated all 6 status counts per month and on the annual total row.
2. **R2 (Real Cohort Conversion based on `uniq_id`)**:
   - *Observation 4* established that cohort conversion must measure unique bookings created in month $M$ (`total_booking_cohort`) that reached Akad Indent or Akad AJB (`booking_converted_akad`).
   - In `getMonthlyStatusTotals()`, calculated `COUNT(DISTINCT m.uniq_id) AS total_booking_cohort` and `COUNT(DISTINCT CASE WHEN ... THEN m.uniq_id END) AS booking_converted_akad`.
   - In `BookingAkadReportService::getSummary()`, calculated monthly conversion as:
     `$totalCohortBooking > 0 ? round(($cohortAkad / $totalCohortBooking) * 100, 1) : 0.0`.
   - Calculated annual total conversion across the full year cohort sums:
     `$annualCohortBooking > 0 ? round(($annualCohortAkad / $annualCohortBooking) * 100, 1) : 0.0`.
   - Applied strict bounding: `max(0.0, min(100.0, (float) $konversi))` ensuring mathematically impossible anomalies (such as the legacy 1400%) are completely prevented.
   - Built the `summary` mapping key conforming strictly to `PROJECT.md § Interface Contracts`.
3. **R3 (7-Column Modal Detail Data Schema)**:
   - In `BookingAkadReportRepository::compileDetailQuery()`, added LEFT JOINs to `hargajual hj` and `tipe thj` on `thj.id_tipe = hj.id_tipe` to resolve `nama_tipe` and `tipe_rumah`.
   - Added SQL status precedence hierarchy:
     $$\text{Batal} \succ \text{Akad AJB} \succ \text{Akad Indent} \succ \text{SP3K} \succ \text{Wawancara} \succ \text{Booking}$$
   - Added SQL catatan fallback:
     `COALESCE(NULLIF(TRIM(m.keterangan_batal), ''), NULLIF(TRIM(m.keterangan), ''), NULLIF(TRIM(m.keterangan_status), ''), '-') AS catatan_mkdt`.
   - In `BookingAkadReportService::getDetail()`, parsed and formatted raw rows into the clean 7-column schema:
     `id_mkdt`, `id_kavling`, `kavling` (`nama_jalan`, `no_kavling`, `sales`), `konsumen` (`nama_konsumen`, `tipe_rumah`, `is_subsidi`), `tanggal_booking`, `status_terakhir`, `status_terakhir_badge`, `tanggal_status_terakhir`, `catatan_mkdt`.
   - Safely sanitized zero-dates (`'0000-00-00'`, `'0000-00-00 00:00:00'`, `null`, `'1970-01-01'`) into `'-'` in `formatDate()`.
   - Safely sanitized `sales` to `'-'` when null or empty.
4. **Test Suite Expansion & Zero-Regression**:
   - Replaced legacy tests in `tests/unit/BookingAkadReportServiceTest.php` with a comprehensive 17-method test suite (89 assertions).
   - Re-ran all test suites (*Observation 5*), confirming 100% pass across all 38 test runner tests and all PHPUnit unit & database tests.

---

## 3. Caveats

- **Scope Boundary**: Worker M1 was strictly scoped to Milestone 1 (Backend Calculation & Data Layer). Frontend view template (`app/Views/laporan/booking-akad.php`), CSS (`laporan-booking-akad.css`), and JavaScript (`laporan-booking-akad.js`) were NOT modified by Worker M1 and remain scheduled for Milestone 2.
- **Backward Compatibility Preserved**: In both `getSummary()` and `getDetail()`, flat compatibility keys (`jumlah_booking`, `rasio_akad`, `nominal`, `tanggal_transaksi`, `alamat_kavling`) were preserved alongside the new nested 7-column schema to ensure existing callers and DataTables consumers do not break during transition.

---

## 4. Conclusion

Milestone 1 is complete and fully verified.
- The 6-status transaction pipeline (Booking, Wawancara, SP3K, Batal, Akad Indent, Akad AJB) is fully implemented at both the repository and service layers.
- Real cohort conversion based on unique transaction codes (`mkdt.uniq_id`) is operating with true annual weighted sum calculations and bounds enforcement `[0.0%, 100.0%]`.
- The 7-column modal detail schema (`kavling`, `konsumen`, `tanggal_booking`, `status_terakhir`, `status_terakhir_badge`, `tanggal_status_terakhir`, `catatan_mkdt`) is fully populated, handling zero-dates and null sales safely without PHP notices.
- All 4 verification test commands pass with exit code 0. The codebase is fully ready for Milestone 2 (Frontend UI & Modal Integration).

---

## 5. Verification Method

To independently reproduce and verify this milestone, run the following commands in the workspace root:

```bash
# 1. Verify Overhaul Feature & Boundary Test Suite (25 tests, 83 assertions)
vendor/bin/phpunit --no-coverage tests/unit/BookingAkadReportServiceOverhaulTest.php

# 2. Verify Service Unit Test Suite (17 tests, 89 assertions)
vendor/bin/phpunit --no-coverage tests/unit/BookingAkadReportServiceTest.php

# 3. Verify All-Tier Standalone Suite Runner (38 test cases, Tier 1 - Tier 4)
php tests/bin/run_booking_akad_suite.php

# 4. Verify Live Database Integration against Proyek 13 (7 tests, 330 assertions)
vendor/bin/phpunit --no-coverage tests/database/BookingAkadReportLiveDbTest.php

# 5. Verify Documentation Module Sync Check
php spark module:sync MKDT --check-only
```

**Invalidation Conditions**:
- Any test in `BookingAkadReportServiceOverhaulTest.php` or `BookingAkadReportServiceTest.php` fails or errors.
- Any test case in `tests/bin/run_booking_akad_suite.php` fails (pass rate < 100%).
- Any query in `BookingAkadReportLiveDbTest.php` fails or partitions outside 171/18/15/11/18/9 on Proyek 13.
- `php spark module:sync MKDT --check-only` exits with a non-zero code.

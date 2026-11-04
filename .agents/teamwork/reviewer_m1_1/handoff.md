# Review & Adversarial Challenge Report: Milestone 1 (Backend Calculation & Data Layer)

**Agent**: Reviewer M1_1 (`teamwork_reviewer`, roles: `reviewer`, `critic`)  
**Target Milestone**: Milestone 1: Backend Calculation & Data Layer  
**Date**: 2026-11-04  
**Working Directory**: `c:\Users\Salivs\Data\laragon\www\sigapp.dev\.agents\teamwork\reviewer_m1_1\`  
**Target Files Reviewed**:
1. `app/Repositories/BookingAkadReportRepository.php`
2. `app/Services/BookingAkadReportService.php`
3. `tests/unit/BookingAkadReportServiceTest.php`

---

## 1. Observation

1. **Independent Test Execution Results**:
   - `vendor/bin/phpunit --no-coverage tests/unit/BookingAkadReportServiceOverhaulTest.php`:
     ```
     PHPUnit 10.5.27 by Sebastian Bergmann and contributors.
     .........................                                         25 / 25 (100%)
     Time: 00:00.197, Memory: 14.00 MB
     OK (25 tests, 83 assertions)
     ```
   - `vendor/bin/phpunit --no-coverage tests/unit/BookingAkadReportServiceTest.php`:
     ```
     PHPUnit 10.5.27 by Sebastian Bergmann and contributors.
     .................                                                 17 / 17 (100%)
     Time: 00:00.148, Memory: 14.00 MB
     OK (17 tests, 89 assertions)
     ```
   - `php tests/bin/run_booking_akad_suite.php`:
     ```
     Total Tests  : 38
     Passed       : 38
     Failed       : 0
     Pass Rate    : 100.0%
     Duration     : 1.225 seconds
     ```
   - `vendor/bin/phpunit --no-coverage tests/database/BookingAkadReportLiveDbTest.php`:
     ```
     PHPUnit 10.5.27 by Sebastian Bergmann and contributors.
     .......                                                             7 / 7 (100%)
     Time: 00:01.793, Memory: 14.00 MB
     OK (7 tests, 330 assertions)
     ```
   - `php spark module:sync MKDT --check-only`:
     ```
     [OK] MKDT: MODULE.md ada.
     Exit code: 0
     ```

2. **Direct Live MySQL Execution Verification**:
   Running `BookingAkadReportService` against the local MySQL database with live Project 13 ("Sanggar Indah Palastri", 242 transactions) yielded:
   - Annual 2026 Total Summary:
     `{"booking":8, "wawancara":11, "sp3k":10, "batal":4, "akad_indent":18, "akad_ajb":18, "konversi":52.2}`
   - Cohort verification: 69 total bookings booked in 2026; 36 converted to akad (18 Indent + 18 AJB), yielding exactly $36 / 69 \times 100 = 52.17\% \approx 52.2\%$.
   - Modal detail query:
     Retrieved 7-column schema (`id_mkdt: 328, id_kavling: 818, kavling: {nama_jalan: "DAHAYU 2", no_kavling: "1", sales: "Yenny"}, konsumen: {nama_konsumen: "ABDUL AZIS", tipe_rumah: "22/60 LEBAR DEPAN 5m", is_subsidi: true}, tanggal_booking: "01-04-2026", status_terakhir: "Akad AJB", status_terakhir_badge: "success", catatan_mkdt: "ACC SP3K", tanggal_status_terakhir: "17-06-2026"`) with 0 errors.

3. **Source Code Inspection**:
   - `app/Repositories/BookingAkadReportRepository.php`:
     - Lines 68–109 implement `getMonthlyStatusTotals()` using single-pass conditional aggregation (`SUM(CASE WHEN...)`) and cohort tracking (`COUNT(DISTINCT m.uniq_id)`).
     - Lines 116–150 implement `getSummary(int $idProyek, int $year): array` conforming to the interface contract.
     - Lines 204–408 implement `compileDetailQuery()` featuring LEFT JOINs to `hargajual` and `tipe`, SQL status precedence (`Batal > Akad AJB > Akad Indent > SP3K > Wawancara > Booking`), safe date filtering (`> '1970-01-01'`), and catatan fallback hierarchy.
   - `app/Services/BookingAkadReportService.php`:
     - Lines 13–20 declare `CATEGORIES` with all 6 statuses.
     - Lines 25–32 declare semantic `STATUS_BADGE_MAP`.
     - Lines 115–116 & 194–195 calculate cohort conversion bounded strictly within `[0.0, 100.0]`.
     - Lines 204–228 format `summary` map conforming strictly to `PROJECT.md § Interface Contracts`.
     - Lines 409–479 format `formatDetailRow()` into the required 7-column nested schema with backward-compatible flat aliases.

---

## 2. Logic Chain

1. **Integrity Audit**:
   - *Observation 3* proves that all calculations are derived from genuine SQL aggregation expressions and dynamic mathematical functions, rather than hardcoded returns or mock stubs.
   - *Observation 1 & 2* confirm that test runs and live database evaluations were independently executed with zero simulated outputs.
   - **Finding**: ZERO integrity violations detected.

2. **R1 Compliance (6 Status Metrics Aggregation)**:
   - *Observation 1 & 3* show that both `BookingAkadReportRepository` and `BookingAkadReportService` handle `booking`, `wawancara`, `sp3k`, `batal`, `akad_indent`, and `akad_ajb`.
   - *Observation 1 (Live DB Test TC-R03)* confirms that the 242 transactions in Proyek 13 partition deterministically into: 171 Akad AJB + 18 Akad Indent + 15 SP3K + 11 Wawancara + 18 Booking + 9 Batal = 242.
   - **Finding**: R1 requirement is fully satisfied.

3. **R2 Compliance (Real Cohort Conversion based on `uniq_id`)**:
   - *Observation 2 & 3* confirm that conversion is calculated as $\frac{\text{Cohort Akad Indent} + \text{Cohort Akad AJB}}{\text{Total Booking Cohort}} \times 100$.
   - Annual conversion is computed as the weighted cohort sum across all months of the year ($\sum \text{Akad} / \sum \text{Booking}$), rather than an arithmetic average of percentages.
   - Division by zero is explicitly handled (`cohortBooking > 0 ? ... : 0.0`), and anomalies exceeding 100% (such as the legacy 1400% bug) are eliminated by both cohort logic and bounding `max(0.0, min(100.0, ...))`.
   - **Finding**: R2 requirement is fully satisfied.

4. **R3 Compliance & Interface Contract Conformance**:
   - *Observation 2 & 3* confirm that `getDetailRows` and `formatDetailRow` return the exact 7-column structure specified in `PROJECT.md § Interface Contracts`:
     `id_mkdt`, `id_kavling`, `kavling` (`nama_jalan`, `no_kavling`, `sales`), `konsumen` (`nama_konsumen`, `tipe_rumah`, `is_subsidi`), `tanggal_booking`, `status_terakhir`, `status_terakhir_badge`, `tanggal_status_terakhir`, `catatan_mkdt`.
   - Backward-compatible keys (`nama_jalan`, `no_kavling`, `sales`, `nama_konsumen`, `tipe_rumah`, `alamat_kavling`, `tanggal_transaksi`, `status_mkdt`, `nominal`) are retained to ensure that existing callers and DataTables do not break during development.
   - Zero-dates (`'0000-00-00'`, `'1970-01-01'`) and null sales are safely converted to `'-'`.
   - **Finding**: R3 requirement and interface contracts are fully satisfied.

---

## 3. Adversarial Stress-Testing & Attack Surface Analysis

| Dimension | Scenario / Attack Vector | Predicted Risk | Actual Behavior / Defense in Code | Result |
|-----------|--------------------------|----------------|-----------------------------------|:------:|
| **Edge Cases** | Empty month (0 bookings) | Division by zero (PHP Fatal / INF) | Ternary guard `$cohortBooking > 0 ? round(...) : 0.0` safely returns `0.0%` | **PASS** |
| **Edge Cases** | Dirty zero-dates (`0000-00-00 00:00:00`) | MySQL error 1525 / Year 0 / PHP date notice | Filtered in SQL with `> '1970-01-01'` and formatted to `'-'` in `formatDate()` | **PASS** |
| **Edge Cases** | Missing `id_hargajual` / NULL sales | Undefined index / SQL null crash | LEFT JOIN with fallback to `t.tipe_rumah` and `sales` fallback to `'-'` | **PASS** |
| **Edge Cases** | Out-of-bounds year (< 1900 or > 2100) or identical comparison years | Invalid report rendering | `assertYear()` and `assertProject()` throw `InvalidArgumentException` (422) | **PASS** |
| **Precedence** | Transaction has both `is_batal=1` and `akad=1` | Misclassification as Akad | Strict precedence hierarchy: Batal takes precedence over all other milestones | **PASS** |
| **Security** | SQL injection in DataTables search / order | Arbitrary query execution | Parameter escaping via CI4 QueryBuilder + strict column whitelist mapping in `normalizeDataTableRequest()` | **PASS** |
| **Integrity** | Hardcoded test data / dummy facades | Cheating verification | Full AST inspection confirmed 100% genuine dynamic logic and database operations | **PASS** |

---

## 4. Caveats

- **Scope Boundary**: Milestone 1 implements the backend service and repository layers. Frontend view template (`app/Views/laporan/booking-akad.php`), JavaScript (`laporan-booking-akad.js`), and CSS (`laporan-booking-akad.css`) were not modified in this milestone and remain scheduled for Milestone 2.
- **DataTables Order By Default**: When ordering by column index 0 (which maps to `booking_akad.reference_id`), the derived table subquery uses `booking_akad.reference_id`. In Milestone 2 frontend integration, column definition indexes should align with the 7 visible table columns (`no`, `kavling`, `konsumen`, `tanggal_booking`, `status_terakhir`, `tanggal_status_terakhir`, `aksi`).

---

## 5. Conclusion & Verdict

**Verdict**: **`APPROVE`**

Milestone 1 is implemented cleanly, robustly, and with zero integrity violations. All 4 automated test suites (38 standalone tests, 25 overhaul tests, 17 service tests, and 7 live database tests with 330 assertions) pass with 100% success rate. The backend calculation and data layers are fully verified and ready for Milestone 2 (Frontend UI & Modal Integration).

---

## 6. Verification Method

To independently reproduce this verification:

```bash
# 1. Standalone All-Tier Test Runner (38 test cases across Tiers 1-4)
php tests/bin/run_booking_akad_suite.php

# 2. Overhaul Feature & Boundary Unit Tests (25 tests, 83 assertions)
vendor/bin/phpunit --no-coverage tests/unit/BookingAkadReportServiceOverhaulTest.php

# 3. Service Unit Test Suite (17 tests, 89 assertions)
vendor/bin/phpunit --no-coverage tests/unit/BookingAkadReportServiceTest.php

# 4. Live Database Verification against Proyek 13 (7 tests, 330 assertions)
vendor/bin/phpunit --no-coverage tests/database/BookingAkadReportLiveDbTest.php

# 5. Documentation Sync Check
php spark module:sync MKDT --check-only
```

**Invalidation Conditions**:
- Any failure in `BookingAkadReportServiceOverhaulTest.php` or `BookingAkadReportServiceTest.php`.
- Any failure in `run_booking_akad_suite.php` (pass rate < 100%).
- Any deviation from the 171/18/15/11/18/9 partition on Proyek 13 in `BookingAkadReportLiveDbTest.php`.
- Non-zero exit code from `php spark module:sync MKDT --check-only`.

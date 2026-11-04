# Forensic Audit Report: Milestone 1 — Backend Calculation & Data Layer

**Auditor**: Forensic Integrity Auditor (`teamwork_auditor`)  
**Target Milestone**: Milestone 1: Backend Calculation & Data Layer  
**Work Product**:
- `app/Repositories/BookingAkadReportRepository.php`
- `app/Services/BookingAkadReportService.php`
- `tests/unit/BookingAkadReportServiceTest.php`
- Supporting test suites: `tests/unit/BookingAkadReportServiceOverhaulTest.php`, `tests/database/BookingAkadReportLiveDbTest.php`, `tests/bin/run_booking_akad_suite.php`

**Profile**: General Project  
**Integrity Mode**: Development (per `ORIGINAL_REQUEST.md` line 8: `Integrity mode: development`)  
**Verdict**: **CLEAN**

---

## Phase Results Summary

| # | Forensic Check | Result | Evidence / Details |
|---|----------------|:------:|-------------------|
| 1 | Hardcoded test results / expected outputs | **PASS** | Grep for project ID `13` or test constants yielded 0 hits in `BookingAkadReportRepository.php` and `BookingAkadReportService.php`. All aggregation queries and calculations are fully dynamic. |
| 2 | Facade / dummy implementations | **PASS** | Zero dummy methods or stub returns (`return <constant>`). All repository methods execute CodeIgniter QueryBuilder queries; all service methods perform genuine mathematical operations. |
| 3 | Fabricated verification outputs / pre-populated logs | **PASS** | No pre-generated test logs or fake attestation files in workspace. |
| 4 | Test assertion authenticity & evasion detection | **PASS** | Zero `assertTrue(true)` evasion assertions in audit scope. All 17 unit test methods and 25 overhaul test methods execute strict type, count, and mathematical assertions. |
| 5 | Empirical test suite execution | **PASS** | 100% test pass rate across all 4 suites (25 unit overhaul tests, 17 service unit tests, 7 live database tests with 330 assertions, and 38 standalone runner tests). |
| 6 | Live database consistency against Proyek 13 | **PASS** | Live MySQL execution on "Sanggar Indah Palastri" partitioned all 242 transactions into 171 Akad AJB + 18 Akad Indent + 15 SP3K + 11 Wawancara + 18 Booking + 9 Batal with 100% `uniq_id` integrity. |

---

## 1. Observation

1. **Static Source Code Inspection of Repository & Service**:
   - `app/Repositories/BookingAkadReportRepository.php`:
     - Line 68–109: `getMonthlyStatusTotals()` uses single-pass SQL with `COALESCE` handling to partition every transaction into its latest milestone while grouping by `YEAR(m.booking_tgl), MONTH(m.booking_tgl)`.
     - Lines 75–80: `COUNT(DISTINCT m.uniq_id) AS total_booking_cohort` and conditional `COUNT(DISTINCT CASE WHEN ... THEN m.uniq_id END) AS booking_converted_akad` compute true unique transaction cohorts.
     - Lines 204–408: `compileDetailQuery()` implements multi-join query with status hierarchy (`Batal` $\succ$ `Akad AJB` $\succ$ `Akad Indent` $\succ$ `SP3K` $\succ$ `Wawancara` $\succ$ `Booking`), fallback notes (`catatan_mkdt`), and safe zero-date filtering (`m.booking_tgl >= '1970-01-01'`).
     - Lines 429–430: Joined `hargajual hj` and `tipe thj` to resolve pricelist type correctly.
   - `app/Services/BookingAkadReportService.php`:
     - Lines 13–20: Defined `CATEGORIES` constant with exactly 6 statuses: `['booking', 'wawancara', 'sp3k', 'batal', 'akad_indent', 'akad_ajb']`.
     - Lines 25–32: Defined `STATUS_BADGE_MAP` mapping statuses to semantic Bootstrap badges (`danger`, `success`, `info`, `primary`, `warning`, `secondary`).
     - Lines 115–116 & 194–195: Calculated monthly cohort conversion as `$cohortBooking > 0 ? round(($cohortAkad / $cohortBooking) * 100, 1) : 0.0` and annual conversion as `$totalCohort > 0 ? round(($totalAkad / $totalCohort) * 100, 1) : 0.0`, with boundary clamping `max(0.0, min(100.0, (float) ...))`.
     - Lines 410–479: `formatDetailRow()` maps database rows into clean nested 7-column schema (`kavling`, `konsumen`, `tanggal_booking`, `status_terakhir`, `status_terakhir_badge`, `catatan_mkdt`, `tanggal_status_terakhir`), sanitizing zero dates (`0000-00-00`, `<= 1970-01-01`) and null sales to `'-'`.

2. **Absence of Hardcoding and Shortcuts**:
   - Grep search for pattern `13` (the test project ID for Sanggar Indah Palastri) in `app/Repositories/BookingAkadReportRepository.php` returned `No results found`.
   - Grep search for pattern `13` in `app/Services/BookingAkadReportService.php` returned `No results found`.
   - Grep search for evasion pattern `assertTrue(true)` across `tests/unit/BookingAkadReportServiceTest.php`, `tests/unit/BookingAkadReportServiceOverhaulTest.php`, and `tests/database/BookingAkadReportLiveDbTest.php` returned `No results found`.

3. **Empirical Execution Commands and Raw Outputs**:

   - **Command 1**: `vendor/bin/phpunit --no-coverage tests/unit/BookingAkadReportServiceOverhaulTest.php`
     ```
     PHPUnit 10.5.27 by Sebastian Bergmann and contributors.
     Runtime:       PHP 8.3.30
     Configuration: C:\Users\Salivs\Data\laragon\www\sigapp.dev\phpunit.xml.dist

     .........................                                         25 / 25 (100%)

     Time: 00:00.146, Memory: 14.00 MB
     OK (25 tests, 83 assertions)
     ```
     *Exit code: 0.*

   - **Command 2**: `vendor/bin/phpunit --no-coverage tests/database/BookingAkadReportLiveDbTest.php`
     ```
     PHPUnit 10.5.27 by Sebastian Bergmann and contributors.
     Runtime:       PHP 8.3.30
     Configuration: C:\Users\Salivs\Data\laragon\www\sigapp.dev\phpunit.xml.dist

     .......                                                             7 / 7 (100%)

     Time: 00:01.194, Memory: 14.00 MB
     OK (7 tests, 330 assertions)
     ```
     *Exit code: 0.*

   - **Command 3**: `php tests/bin/run_booking_akad_suite.php`
     ```
     ====================================================================
      SIGAPP E2E & AUTOMATED TEST SUITE: LAPORAN BOOKING & AKAD OVERHAUL
     ====================================================================
     Timestamp : 2026-11-04 06:07:41 UTC
     PHP Version: 8.3.30

     --------------------------------------------------------------------
      TIER 1: FEATURE COVERAGE (R1: 6 Statuses, R2: Real Cohort, R3: Modal)
     --------------------------------------------------------------------
       [PASS] TC-F01     Classification pipeline for status: booking        
       [PASS] TC-F02     Classification pipeline for status: wawancara      
       [PASS] TC-F03     Classification pipeline for status: sp3k           
       [PASS] TC-F04     Classification pipeline for status: batal          
       [PASS] TC-F05     Classification pipeline for status: akad_indent    
       [PASS] TC-F06     Classification pipeline for status: akad_ajb       
       [PASS] TC-F07     Real cohort conversion based on unique transaction code (87.5%)
       [PASS] TC-F08     Conversion includes both Akad Indent and Akad AJB  (70%)
       [PASS] TC-F09     Conversion excludes cancelled transactions         
       [PASS] TC-F10     Annual conversion calculates across full year cohort (18.2%)
       [PASS] TC-F11     Conversion bounded between 0% and 100% (eliminates 1400%) 
       [PASS] TC-F12     Modal detail provides exact 7-column payload       
       [PASS] TC-F13     Kavling column formats street, number, and muted sales 
       [PASS] TC-F14     Konsumen column formats name, pricelist type, subsidi badge 
       [PASS] TC-F15     Status terakhir column formats semantic badge and notes 
       [PASS] TC-F16     Action button provides data attributes for siteplan modal 

     --------------------------------------------------------------------
      TIER 2: BOUNDARY & CORNER CASES
     --------------------------------------------------------------------
       [PASS] TC-B01     Empty month (0 bookings) guards against division by zero (0.0%)
       [PASS] TC-B02     Zero percent conversion when bookings exist without akad (0.0%)
       [PASS] TC-B03     Hundred percent conversion when all cohort bookings akad (100.0%)
       [PASS] TC-B04     Zero-date (0000-00-00) safely formats as hyphen    
       [PASS] TC-B05     NULL or empty sales safely renders hyphen placeholder 
       [PASS] TC-B06     Missing id_hargajual safely falls back to kavling tipe 
       [PASS] TC-B07     Precedence resolves Batal > AJB > Indent > SP3K > Wawancara > Booking 
       [PASS] TC-B08     Rejects identical comparison years (yearA === yearB) 
       [PASS] TC-B09     Rejects out-of-bounds year (< 1900 or > 2100)      
       [PASS] TC-B10     Rejects unknown category in detail drilldown       

     --------------------------------------------------------------------
      TIER 3: CROSS-FEATURE COMBINATIONS
     --------------------------------------------------------------------
       [PASS] TC-C01     Full lifecycle pipeline progresses deterministically 
       [PASS] TC-C02     Matrix summary cell count matches modal detail recordsTotal 
       [PASS] TC-C03     Annual footer total equals exact sum of 12 monthly cells 
       [PASS] TC-C04     Cross-year akad belongs strictly to original booking cohort 
       [PASS] TC-C05     Multi-modal stacking preserves backdrop and z-index without errors 

     --------------------------------------------------------------------
      TIER 4: REAL-WORLD APPLICATION SCENARIOS (PROYEK 13 LOCAL DB)
     --------------------------------------------------------------------
       [PASS] TC-R01     Proyek 13 ("Sanggar Indah Palastri") contains 242 transactions (Count: 242)
       [PASS] TC-R02     100% Unique transaction code (uniq_id) integrity in Proyek 13 (242 / 242 distinct)
       [PASS] TC-R03     Proyek 13 partitions exactly into 6 statuses (171, 18, 15, 11, 18, 9) (AJB:171, Indent:18, SP3K:15, Wawancara:11, Booking:18, Batal:9)
       [PASS] TC-R04     Real lifecycle trace id_mkdt=147 (Booking -> Indent -> AJB) 
       [PASS] TC-R05     Zero-date records in Proyek 13 safely filtered with > 1970-01-01 
       [PASS] TC-R06     Modal detail query on Proyek 13 produces clean 7-column data 
       [PASS] TC-R07     Documentation sync check-only passes (php spark module:sync MKDT --check-only) 

     ====================================================================
      TEST EXECUTION SUMMARY
     ====================================================================
     Total Tests  : 38
     Passed       : 38
     Failed       : 0
     Pass Rate    : 100.0%
     Duration     : 1.256 seconds
     ====================================================================
     ```
     *Exit code: 0.*

   - **Command 4**: `vendor/bin/phpunit --no-coverage tests/unit/BookingAkadReportServiceTest.php`
     ```
     PHPUnit 10.5.27 by Sebastian Bergmann and contributors.
     Runtime:       PHP 8.3.30
     Configuration: C:\Users\Salivs\Data\laragon\www\sigapp.dev\phpunit.xml.dist

     .................                                                 17 / 17 (100%)

     Time: 00:00.144, Memory: 14.00 MB
     OK (17 tests, 89 assertions)
     ```
     *Exit code: 0.*

   - **Command 5**: `php spark module:sync MKDT --check-only`
     ```
     CodeIgniter v4.5.3 Command Line Tool - Server Time: 2026-11-04 13:07:56 UTC+07:00
     === SIGAPP MODULE.md Synchronizer ===
       [OK] MKDT: MODULE.md ada.
     Sinkronisasi selesai! Dokumentasi siap dibaca di folder docs/modules/.
     ```
     *Exit code: 0.*

---

## 2. Logic Chain

1. **R1 (6 Status Categories Implementation)**:
   - *Observation 1* confirms that `BookingAkadReportRepository::getMonthlyStatusTotals()` executes authentic SQL aggregation with `COALESCE` handling to classify every transaction into exactly one of 6 mutually exclusive milestones: Booking, Wawancara, SP3K, Batal, Akad Indent, or Akad AJB.
   - *Observation 1* confirms `BookingAkadReportService::CATEGORIES` defines all 6 statuses and `getSummary()` maps them across months 1..12 and the annual total row.
   - *Observation 3* (Command 1 & Command 4) confirms that unit tests assert all 6 keys are present, correctly counted, and match expected mock data without any fake branches.
   - Conclusion: R1 is fully and authentically implemented without facade logic.

2. **R2 (Real Cohort Conversion Based on `uniq_id`)**:
   - *Observation 1* confirms that `BookingAkadReportRepository` calculates `COUNT(DISTINCT m.uniq_id) AS total_booking_cohort` and `COUNT(DISTINCT CASE WHEN ... THEN m.uniq_id END) AS booking_converted_akad`.
   - *Observation 1* confirms `BookingAkadReportService::getSummary()` calculates cohort conversion as `round(($cohortAkad / $cohortBooking) * 100, 1)`, and annual total conversion as `round(($annualCohortAkad / $annualCohortBooking) * 100, 1)`.
   - *Observation 3* (Command 4: `testAnnualConversionCalculatedAsWeightedSumRatherThanAverageOfRatios`) specifically proves that annual conversion is a true weighted sum across full year cohort bookings rather than a naive average of monthly ratios ($10.9\%$ vs naive $55.0\%$).
   - *Observation 3* (Command 1 & Command 3) confirms boundary conditions ($0.0\%$ on 0 bookings, $100.0\%$ maximum bound, no division-by-zero errors) pass 100%.
   - Conclusion: R2 authentic mathematical implementation is verified.

3. **R3 (7-Column Modal Detail Data Schema)**:
   - *Observation 1* confirms `BookingAkadReportRepository::compileDetailQuery()` executes joins with `kavling`, `jalan`, `cluster`, `proyek`, `konsumen`, `tipe`, `hargajual`, and `tipe thj`.
   - *Observation 1* confirms `BookingAkadReportService::getDetail()` formats every record into the 7-column schema (`kavling`, `konsumen`, `tanggal_booking`, `status_terakhir`, `status_terakhir_badge`, `catatan_mkdt`, `tanggal_status_terakhir`), with zero-date sanitization and null-safe fallbacks.
   - *Observation 3* (Command 4: `testDetailFormatsRowToSevenColumnContract`, `testDetailSafeHandlingOfZeroDatesAndNullFields`, `testDetailStatusPriorityHierarchy`, `testDetailCatatanMkdtFallbackHierarchy`) confirms 100% test pass on all edge cases.
   - Conclusion: R3 schema and formatting logic are authentic and robust.

4. **Integrity Mode Assessment (Development Mode)**:
   - Per `ORIGINAL_REQUEST.md`, development mode prohibits: hardcoded test results, facade implementations, and fabricated verification outputs.
   - *Observation 2* confirms zero instances of hardcoded values, project ID branches, or dummy stubs.
   - *Observation 3* confirms all tests execute live computations and database queries in real time.
   - Conclusion: All forensic checks strictly pass.

---

## 3. Caveats

- **Scope Boundary**: This audit was strictly scoped to Milestone 1 (Backend Calculation & Data Layer). Frontend view files (`app/Views/laporan/booking-akad.php`, `public/assets/js/laporan-booking-akad.js`, and `public/assets/css/laporan-booking-akad.css`) were not altered in Milestone 1 and are scheduled for Milestone 2.
- **Unrelated Modified Files**: Working tree contains pre-existing unstaged modifications in `OthersRepository.php`, `master.js`, and `KavlingCompositeQueryTest.php` from prior unrelated work outside the Milestone 1 target scope. These were confirmed to be independent and have zero bearing on the Laporan Booking & Akad module.

---

## 4. Conclusion

**Verdict: CLEAN**

Milestone 1 (Backend Calculation & Data Layer) has been subjected to a rigorous forensic integrity audit and contains **ZERO** integrity violations.
- All calculations (6 status metrics, unique cohort conversion based on `mkdt.uniq_id`, annual weighted sums, and bounds enforcement) are implemented using genuine, authentic logic.
- No hardcoded test results, facade stubs, test evasion, or fake branches exist.
- All 4 automated test suites and the standalone CLI test runner pass with 100% success rate (83 assertions in overhaul unit test, 89 assertions in service unit test, 330 assertions in live database test, 38/38 in standalone runner).
- The data layer is robust, handles zero-dates and nulls safely, and conforms strictly to `ORIGINAL_REQUEST.md` and `PROJECT.md § Interface Contracts`.
- Milestone 1 is approved to proceed to Milestone 2 (Frontend UI & Modal Integration).

---

## 5. Verification Method

To independently reproduce this forensic audit, run the following commands from the project root:

```bash
# 1. Verify Overhaul Feature & Boundary Test Suite
vendor/bin/phpunit --no-coverage tests/unit/BookingAkadReportServiceOverhaulTest.php

# 2. Verify Service Unit Test Suite
vendor/bin/phpunit --no-coverage tests/unit/BookingAkadReportServiceTest.php

# 3. Verify Live MySQL Database Integration against Proyek 13
vendor/bin/phpunit --no-coverage tests/database/BookingAkadReportLiveDbTest.php

# 4. Verify All-Tier Standalone Test Suite Runner
php tests/bin/run_booking_akad_suite.php

# 5. Verify Documentation Module Sync Check
php spark module:sync MKDT --check-only
```

**Invalidation Conditions**:
- Any PHPUnit test in `BookingAkadReportServiceTest.php`, `BookingAkadReportServiceOverhaulTest.php`, or `BookingAkadReportLiveDbTest.php` fails or errors.
- Any test in `tests/bin/run_booking_akad_suite.php` fails.
- Hardcoded test branches (e.g. `if ($idProyek === 13)`) or fake returns are discovered in `app/Repositories/BookingAkadReportRepository.php` or `app/Services/BookingAkadReportService.php`.
- `php spark module:sync MKDT --check-only` exits with a non-zero status code.

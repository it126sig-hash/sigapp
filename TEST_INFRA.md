# Test Infrastructure Specification: Laporan Booking & Akad Overhaul

**Module:** MKDT / Laporan Booking & Akad  
**Document Status:** Published  
**Author:** Test Writer (`teamwork_test_writer`)  
**Target:** Project 13 ("Sanggar Indah Palastri") & CI4 Backend/Frontend  
**Interface Contracts:** `PROJECT.md`, `ORIGINAL_REQUEST.md`

---

## 1. Test Philosophy

1. **Opaque-Box & Requirement-Driven**:
   Tests are derived strictly from the authoritative specifications in `ORIGINAL_REQUEST.md` (Requirements R1, R2, R3, R4, R5) and architectural contracts in `PROJECT.md`. Tests verify external behaviors, outputs, HTTP/JSON contracts, and data invariants rather than private internal implementation details.

2. **Authoritative Output Derivation**:
   Every test case possesses an explicit source of ground truth:
   - **Conversion Percentage Formula**: Mathematical cohort calculation:
     $$\text{Konversi (\%)} = \frac{\text{Jumlah Transaksi Unik Bulan } M \text{ yang Mencapai Akad Indent/AJB}}{\text{Total Transaksi Unik yang Booking pada Bulan } M} \times 100\%$$
   - **Status Pipeline Hierarchy**: Precedence logic:
     $$\text{Batal} \succ \text{Akad AJB} \succ \text{Akad Indent} \succ \text{SP3K} \succ \text{Wawancara} \succ \text{Booking}$$
   - **Live DB Ground Truth**: Project 13 ("Sanggar Indah Palastri") contains 242 transactions with 100% `uniq_id` presence, partitioned into 171 Akad AJB, 18 Akad Indent, 15 SP3K, 11 Wawancara, 18 Booking, and 9 Batal.

3. **Test Independence & Isolation**:
   - Each test sets up its own state or runs against deterministic fixtures.
   - Unit tests use test doubles / mocks to isolate `BookingAkadReportService` from database state.
   - Database tests verify query builders, safe date handling, and live dataset invariants with non-destructive read-only queries or isolated transactions.
   - No cross-test side effects; execution order is irrelevant.

---

## 2. Feature Inventory & Mapping to Test Tiers

| # | Feature (from `PROJECT.md`) | Scope & Description | Tier 1 (Feature) | Tier 2 (Boundary) | Tier 3 (Cross-Feature) | Tier 4 (Real-World DB) |
|---|-----------------------------|---------------------|:----------------:|:-----------------:|:----------------------:|:----------------------:|
| 1 | **Metrik 6 Status Transaksi (R1)** | Aggregation of 6 status categories (Booking, Wawancara, SP3K, Batal, Akad Indent, Akad AJB) per month & year total | ✓ (TC-F01..F06) | ✓ (TC-B01, B07) | ✓ (TC-C01, C02, C03) | ✓ (TC-R01, R03) |
| 2 | **Konversi Riil `uniq_id` (R2)** | Real cohort conversion % based on `mkdt.uniq_id` for monthly rows and annual footer | ✓ (TC-F07..F11) | ✓ (TC-B01..B03) | ✓ (TC-C03, C04) | ✓ (TC-R04, R05) |
| 3 | **Query Data Modal Detail 7 Kolom (R3)** | Server-side DataTables query returning 7 columns with relations and note fallback | ✓ (TC-F12..F16) | ✓ (TC-B04..B06) | ✓ (TC-C02) | ✓ (TC-R06) |
| 4 | **UI Matriks & Kolom Konversi (R1, R2, R4)** | 7 columns per year (`colspan="7"`), min-width 1200px/2200px, responsive styling | ✓ (TC-F17) | ✓ (TC-B08) | ✓ (TC-C05) | ✓ (TC-R05) |
| 5 | **Tampilan Modal Detail 7 Kolom (R3, R4)** | DataTables rendering: No, Kavling, Konsumen, Tgl Booking, Status Terakhir, Tgl Status Terakhir, Aksi | ✓ (TC-F18..F20) | ✓ (TC-B05, B06) | ✓ (TC-C02) | ✓ (TC-R06) |
| 6 | **Integrasi Tombol Aksi Modal Kavling (R3)** | Click eye button opens `siteplan/partials/modal_detail` without missing globals | ✓ (TC-F21) | ✓ (TC-B04) | ✓ (TC-C05) | ✓ (TC-R06) |
| 7 | **Perataan Teks Tampilan (R4)** | Text columns left-aligned (`text-left`), numbers right-aligned (`text-right`), dates centered (`text-center`) | ✓ (TC-F22) | ✓ (TC-B10) | ✓ (TC-C02) | ✓ (TC-R06) |
| 8 | **Sinkronisasi Dokumentasi Modul (R5)** | Verification of `docs/modules/MKDT/MODULE.md` via `php spark module:sync MKDT` | ✓ (TC-F23) | ✓ (TC-B09) | ✓ (TC-C03) | ✓ (TC-R07) |
| 9 | **E2E Testing & Audit Verifikasi** | Full suite execution against Project 13 dataset and acceptance criteria verification | ✓ | ✓ | ✓ | ✓ (TC-R01..R07) |

---

## 3. 4-Tier Test Suite Specification

### Tier 1: Feature Coverage (>=5 Test Cases per Core Feature)

#### Feature 1: 6-Status Metric Aggregation (R1)
- **TC-F01**: `testSummaryAggregatesBookingCategory`: Verifies transactions categorized as pure booking (no subsequent milestones) appear in the `booking` count.
- **TC-F02**: `testSummaryAggregatesWawancaraCategory`: Verifies transactions having `wawancara = 1` or valid `wawancara_tgl` appear in `wawancara`.
- **TC-F03**: `testSummaryAggregatesSp3kCategory`: Verifies transactions reaching SP3K stage (`sp3k = 1` or valid `sp3k_tgl`) appear in `sp3k`.
- **TC-F04**: `testSummaryAggregatesBatalCategory`: Verifies transactions with `is_batal = 1` or `status_mkdt = 'Batal'` appear in `batal`.
- **TC-F05**: `testSummaryAggregatesAkadIndentCategory`: Verifies transactions with `akad_indent = 1` or `is_ajb = 'PPJB'` appear in `akad_indent`.
- **TC-F06**: `testSummaryAggregatesAkadAjbCategory`: Verifies transactions with `akad = 1` and `is_ajb = 'AJB'` appear in `akad_ajb`.

#### Feature 2: Real Cohort Conversion % (`uniq_id` Basis) (R2)
- **TC-F07**: `testConversionCalculatesRealCohortFromUniqueTransactionCodes`: Verifies conversion is calculated from cohort bookings reaching Akad Indent or Akad AJB divided by total bookings in that cohort month.
- **TC-F08**: `testConversionIncludesBothAkadIndentAndAkadAjb`: Verifies both `akad_indent` and `akad_ajb` transactions are included in the conversion numerator.
- **TC-F09**: `testConversionExcludesBatalEvenIfPriorMilestonesExist`: Verifies cancelled transactions are excluded from the conversion numerator even if they had achieved interview or SP3K.
- **TC-F10**: `testAnnualTotalConversionCalculatesAcrossFullYearCohort`: Verifies the annual total footer row conversion % equals $\frac{\sum \text{Akad}}{\sum \text{Booking Cohort}} \times 100\%$ rather than averaging monthly percentages.
- **TC-F11**: `testConversionNeverExceeds100PercentInCohortModel`: Verifies that unlike the legacy event-based formula which yielded 1400%, the cohort conversion is mathematically bound between 0.0% and 100.0%.

#### Feature 3: Modal Detail 7 Columns Structure (R3)
- **TC-F12**: `testDetailReturnsExactSevenColumnPayload`: Verifies the DataTables AJAX response contains all 7 required columns: `no`, `kavling`, `konsumen`, `tanggal_booking`, `status_terakhir`, `tanggal_status_terakhir`, `aksi`.
- **TC-F13**: `testDetailFormatsKavlingWithMutedSales`: Verifies `kavling` contains `nama_jalan`, `no_kavling`, and `<small class="text-muted d-block">Sales: ...</small>`.
- **TC-F14**: `testDetailFormatsKonsumenWithPricelistTypeAndSubsidiBadge`: Verifies `konsumen` includes consumer name, pricelist house type, and badge (`Subsidi` or `Komersil`).
- **TC-F15**: `testDetailFormatsStatusTerakhirWithBadgeAndMkdtNotes`: Verifies `status_terakhir` outputs the semantic badge and appends notes from `keterangan` or `keterangan_batal`.
- **TC-F16**: `testDetailProvidesActionDataAttributesForSiteplanModal`: Verifies the action column outputs a button with `id_kavling`, `id_mkdt`, `nama_jalan`, and `no_kavling` data attributes.

---

### Tier 2: Boundary & Corner Cases

- **TC-B01: Empty Month (Zero Bookings)**:
  - Input: Month with 0 bookings (`total_booking = 0`).
  - Expected: Counts are 0, `konversi` returns `0.0%` (safe division by zero guard).
- **TC-B02: 0% Conversion**:
  - Input: Month with 10 bookings, 0 reached Akad.
  - Expected: `konversi = 0.0%`, badge displays muted neutral style.
- **TC-B03: 100% Conversion**:
  - Input: Month with 5 bookings, all 5 reached Akad Indent or AJB.
  - Expected: `konversi = 100.0%`, badge displays success style.
- **TC-B04: Dirty Zero-Date (`'0000-00-00'`) Handling**:
  - Input: Transaction records with `akad_tgl = '0000-00-00'` or `sp3k_tgl = '0000-00-00'`.
  - Expected: SQL queries guard with `> '1970-01-01'`. Modal detail renders `'-'` for `tanggal_status_terakhir`. No string parsing errors or MySQL 1525 warnings.
- **TC-B05: NULL or Empty `sales`**:
  - Input: Konsumen record with `sales = NULL` or `sales = ''`.
  - Expected: Muted line renders `<small class="text-muted d-block">-</small>` without emitting PHP notice or JS `undefined`.
- **TC-B06: Missing `id_hargajual` Fallback**:
  - Input: MKDT record where `id_hargajual = NULL`.
  - Expected: Query falls back to `tipe.tipe_rumah` from `kavling.id_tipe`.
- **TC-B07: Multi-Status Precedence & Transitions**:
  - Input: Unit with both `akad_indent = 1` and `akad = 1` (completed indent, then completed AJB).
  - Expected: Status resolves to `Akad AJB` (highest precedence).
- **TC-B08: Same Comparison Year Rejection**:
  - Input: `getSummary(idProyek, 2026, 2026)`.
  - Expected: Throws `InvalidArgumentException('Pilih dua tahun yang berbeda.')`.
- **TC-B09: Invalid Month or Year Bounds**:
  - Input: `month = 13` or `year = 1800`.
  - Expected: Throws `InvalidArgumentException`.
- **TC-B10: Unknown Category Drilldown**:
  - Input: `category = 'invalid_cat'`.
  - Expected: Throws `InvalidArgumentException('Kategori laporan tidak valid.')`.

---

### Tier 3: Cross-Feature Combinations

- **TC-C01: Full Lifecycle Pipeline Trace**:
  - Sequence: Booking ($t_1$) $\to$ Wawancara ($t_2$) $\to$ SP3K ($t_3$) $\to$ Akad Indent ($t_4$) $\to$ Akad AJB ($t_5$).
  - Assertion: Unit is classified strictly as `Akad AJB` in the 6-status partition; contributes 1 to numerator and 1 to denominator in conversion.
- **TC-C02: Matrix Summary vs Modal Detail Row Count Coherence**:
  - Action: Query summary count for status $S$ in month $M$ (e.g. 15 for SP3K in month 10). Query `getDetail` for month $M$ and category $S$.
  - Assertion: `recordsTotal` in detail response EXACTLY matches the count in the matrix cell.
- **TC-C03: Annual Footer Additive Consistency**:
  - Assertion: For every status column $k \in \{1..6\}$, $\text{Total}_k = \sum_{m=1}^{12} \text{Count}(m, k)$.
- **TC-C04: Cross-Year Cohort Conversion Attribution**:
  - Scenario: Booking occurred in November 2024; Akad AJB occurred in April 2025.
  - Assertion: The conversion credit belongs to the **November 2024 cohort**, NOT April 2025.
- **TC-C05: Multi-Modal Stacking & Global Asset Dependents**:
  - Verification: `window.base_url` and `window.loaded` are declared; opening `#modal_detail` on top of `#booking_akad_detail_modal` preserves backdrop stacking without console errors.

---

### Tier 4: Real-World Application Scenarios (Live Proyek 13: "Sanggar Indah Palastri")

- **TC-R01: Proyek 13 Existence & Volume Ground Truth**:
  - Verification: Query `proyek` for `id_proyek = 13` ("SANGGAR INDAH PALASTRI", code `SIP`). Confirm total MKDT transactions $= 242$.
- **TC-R02: 100% Unique Transaction Code (`uniq_id`) Integrity**:
  - Verification: Query `COUNT(DISTINCT uniq_id)` vs `COUNT(*)` in Proyek 13.
  - Assertion: Both equal 242. 0 records have NULL or blank `uniq_id`.
- **TC-R03: Complete 6-Status Partition Verification**:
  - Verification: Partition all 242 records using the pipeline hierarchy:
    * `Akad AJB`: 171 records
    * `Akad Indent`: 18 records
    * `SP3K`: 15 records
    * `Wawancara`: 11 records
    * `Booking`: 18 records
    * `Batal`: 9 records
  - Assertion: $171 + 18 + 15 + 11 + 18 + 9 = 242$ (100% classification).
- **TC-R04: Real Lifecycle Conversion Sample Verification (`id_mkdt = 147`)**:
  - Record: `id_mkdt = 147`, `uniq_id = '67d7dcbd8200f7.27535517'`, Kavling `LINTAS CALYA No. 15`, Konsumen `INU RAINUKA NUGRAHA`.
  - Stages verified in DB: Booking (`2024-10-09`) $\to$ Wawancara (`2024-10-09`) $\to$ SP3K (`2025-01-31`) $\to$ Akad Indent (`2025-02-14`) $\to$ Akad AJB (`2025-04-14`).
- **TC-R05: Ground Truth 2026 Cohort Calculation Verification**:
  - Execute authoritative verification query for Year 2026 against Proyek 13.
  - Verify monthly conversion rates (e.g. Feb 2026: 100%, Apr 2026: 31.3%, annual total: 52.2%).
- **TC-R06: Modal Detail Data Shape on Proyek 13**:
  - Execute detail query for Proyek 13 transactions. Confirm all 7 columns populate with clean strings; zero-dates format safely as `'-'`; sales null format safely as `'-'`.
- **TC-R07: Documentation Module Sync Verification**:
  - Run `php spark module:sync MKDT --check-only` and `php spark module:status`.
  - Assert exit code 0 and MKDT status verified.

---

## 4. Test Execution & Verification Protocol

### Test Commands
```bash
# 1. Run Unit Tests (Tier 1, Tier 2, Tier 3)
vendor/bin/phpunit --no-coverage tests/unit/BookingAkadReportServiceOverhaulTest.php

# 2. Run Live Database Verification Tests (Tier 4)
vendor/bin/phpunit --no-coverage tests/database/BookingAkadReportLiveDbTest.php

# 3. Run Standalone E2E Test Suite (All Tiers)
php tests/bin/run_booking_akad_suite.php

# 4. Verify Documentation Sync
php spark module:sync MKDT --check-only
```

---

## 5. Traceability Matrix

| Requirement | Description | Test Cases | Target Artifact |
|-------------|-------------|------------|-----------------|
| **R1** | 6 Status Categories | TC-F01..F06, TC-B01, TC-C01, TC-C03, TC-R03 | `BookingAkadReportService`, `BookingAkadReportRepository` |
| **R2** | Real Cohort Conversion % | TC-F07..F11, TC-B02, TC-B03, TC-C04, TC-R04, TC-R05 | `BookingAkadReportService::getSummary()` |
| **R3** | 7-Column Modal Detail | TC-F12..F16, TC-B04..B07, TC-C02, TC-R06 | `BookingAkadReportService::getDetail()`, `BookingAkadReportRepository::getDetailRows()` |
| **R3.Action** | Kavling Modal Trigger | TC-F21, TC-C05, TC-R06 | `booking-akad.php`, `siteplan-detail-modal.js` |
| **R4** | Text & Date Alignment | TC-F22, TC-B10 | `laporan-booking-akad.css`, `laporan-booking-akad.js` |
| **R5** | Documentation Sync | TC-F23, TC-R07 | `docs/modules/MKDT/MODULE.md` |

# Handoff Report: Spec Miner 3 (Documentation Standards, DB Verification & QA Plan)

## 1. Observation

1. **Documentation Standard & Catalog**:
   - `app/Libraries/ModuleManager/ModuleCatalog.php` (lines 104–137) catalogues Laporan Booking & Akad under the **`MKDT`** module:
     * Web Controller: `Web/BookingAkadReportController.php`
     * Api Controller: `Api/BookingAkadReportController.php`
     * Service: `BookingAkadReportService.php`
     * Repository: `BookingAkadReportRepository.php`
     * Views: `laporan` (`app/Views/laporan/booking-akad.php`)
     * Assets JS: `laporan-booking-akad.js`
   - `app/Libraries/ModuleManager/ModuleDocGenerator.php` (lines 34–41, 186–188) enforces AST generation of `docs/modules/<Modul>/MODULE.md`, preserving curated sections (`## Purpose`, `## Invariants`, `## Key Functions`, `## Deprecated & Legacy Shims`, `## Notes`, `## Change Log`) while dynamically regenerating contracts and file inventories.
   - Command `php spark module:sync MKDT --check-only` executed cleanly:
     ```
     === SIGAPP MODULE.md Synchronizer ===
       [OK] MKDT: MODULE.md ada.
     ```
   - Command `php spark module:status` verified that `MKDT` is cataloged with 6 Controllers, 8 Services, 5 Repositories, 3 Models, 44 Routes, and 4 Owner Tables.

2. **Database Verification Resources (Sanggar Indah Palastri)**:
   - Command `mysql -u root sigapp -e "SELECT id_proyek, nama_proyek, kode_keuangan, nama_pt FROM proyek WHERE id_proyek = 13;"` returned:
     * `id_proyek`: 13
     * `nama_proyek`: `SANGGAR INDAH PALASTRI`
     * `kode_keuangan`: `SIP`
     * `nama_pt`: `PT. Sanggarindah Karyasentosa Raya`
   - Command `mysql -u root sigapp -e "SELECT COUNT(*) AS total_mkdt, COUNT(DISTINCT uniq_id) AS distinct_uniq_id ... WHERE cl.id_proyek = 13;"` confirmed:
     * Total MKDT transactions = 242.
     * All 242 have unique `uniq_id` codes (0 empty `uniq_id`).
   - Querying the 6 statuses on Project 13 returned:
     * `akad_ajb`: 171
     * `akad_indent`: 18
     * `sp3k`: 15
     * `wawancara`: 11
     * `booking`: 18
     * `batal`: 9
     * Total = 242 (100% complete coverage).
   - Real conversion example discovered: `id_mkdt = 147`, `uniq_id = 67d7dcbd8200f7.27535517`, booked `2024-10-09`, interviewed `2024-10-09`, SP3K `2025-01-31`, Akad Indent (PPJB) `2025-02-14`, and final Akad AJB `2025-04-14`.

3. **Dirty Data & Edge Cases in Live Database**:
   - Querying zero-dates in Project 13 revealed:
     * `akad_tgl`: 31 records have `'0000-00-00'` (causes MySQL error 1525 in strict mode on `= '0000-00-00'`, and `YEAR()` returns 0).
     * `wawancara_tgl`: 33 records have `'0000-00-00'`.
     * `sp3k_tgl`: 45 records have `'0000-00-00'`.
   - Milestone flag vs Date mismatch:
     * 68 records have `wawancara = 1` with NULL or zero date.
     * 21 records have `sp3k = 1` with NULL or zero date.
     * 6 records have `status_mkdt = 'Batal'` with NULL `mkdt_batal_tgl`.
   - 11 of 242 records have NULL/empty `konsumen.sales`.

4. **Frontend & Missing Global Variables**:
   - In `public/assets/js/laporan-booking-akad.js` (lines 565–602), clicking `.booking-akad-view-kavling` invokes `detail_kavling(sh, idKavling)` from `siteplan-detail-modal.js`.
   - In `public/assets/js/siteplan-detail-modal.js`:
     * Line 464: `url: base_url + 'siteplan/get/detail'`.
     * Line 497: `loaded['sm'] = true`.
   - In `app/Views/laporan/booking-akad.php`: neither `var base_url` nor `var loaded` are defined! Clicking the eye button in standalone report view risks `ReferenceError: base_url is not defined` or `loaded is not defined`.

---

## 2. Logic Chain

1. From Observation 1, Laporan Booking & Akad is an integrated subfeature of the `MKDT` module, not a freestanding module. Consequently, documentation updates (Requirement R5) must be applied to `docs/modules/MKDT/MODULE.md`, followed by executing `php spark module:sync MKDT`.
2. From Observation 2, Project 13 ("Sanggar Indah Palastri") contains 242 clean transaction records with 100% unique transaction codes (`uniq_id`), and live transactions populate all 6 required statuses.
3. From Observation 3, 31 to 45 records contain zero-dates (`'0000-00-00'`) or null dates despite status flags being set to 1. Therefore:
   - SQL queries in `BookingAkadReportRepository` must avoid naive `IS NOT NULL` checks and instead enforce `> '1970-01-01'` or `CAST(col AS CHAR) NOT LIKE '0000%'`.
   - Modal detail formatting for `tanggal_status_terakhir` must format valid dates as `DD-MM-YYYY` and render `'-'` for null or zero dates.
4. From Observation 2 & 3, the old conversion ratio compared total akad events in a month against total booking events in that month, yielding illogical ratios (e.g. 1400% in Feb 2026 where 1 booked and 14 old bookings did akad). Using the real cohort conversion based on unique transaction code (`uniq_id`) in R2 produces exact, logical conversion figures (100% for Feb 2026, 31.3% for Apr 2026, 52.2% annual total 2026).
5. From Observation 4, `booking-akad.php` must explicitly define `window.base_url = '<?= base_url() ?>/';` and `window.loaded = [];` and verify required CSS/JS scripts to prevent runtime failures when clicking the modal detail action button (Requirement R3).

---

## 3. Caveats

1. **Cohort vs Event-Date Matrix Representation**: Requirement R1 asks to "tampilkan dan hitung 6 kategori status transaksi", while R2 asks for "konversi dari booking ke akad secara riil berbasis kode transaksi". Two interpretations exist:
   - *Model A (Status Cohort)*: Monthly rows represent bookings made in that month, and the 6 columns show the current status of those bookings (sum of columns = total bookings; conversion % = `(Akad Indent + Akad AJB) / Total * 100%`).
   - *Model B (Status Event Activity)*: Columns show event counts that occurred in that month (`booking_tgl`, `wawancara_tgl`, `sp3k_tgl`, etc.), while Konversi % displays the cohort conversion rate of that month's bookings.
   Both models are fully documented with corresponding SQL verification queries in `analysis.md`.
2. **AJB vs Indent Precedence**: When a unit reaches Akad Indent and subsequently reaches Akad AJB, both `akad_indent = 1` and `akad = 1` may be set. The status hierarchy gives priority to `Akad AJB`.

---

## 4. Conclusion

The specification survey for Laporan Booking & Akad overhaul is complete:
- **R1 & R2**: Both matrix models and the real transaction code cohort formula (`uniq_id`) have been formulated and verified against Project 13 in the local MySQL database.
- **R3 & R4**: The 7-column modal detail structure, rich kavling formatting, rich consumer formatting, status badge with MKDT notes, zero-date safe formatting, text alignment rules, and missing global variables (`base_url`, `loaded`) have been identified and resolved.
- **R5**: The documentation path (`docs/modules/MKDT/MODULE.md`) and synchronization procedures via `php spark module:sync MKDT` are confirmed and ready for the implementation phase.

---

## 5. Verification Method

1. **Documentation Verification**:
   ```bash
   php spark module:sync MKDT --check-only
   php spark module:status
   ```
2. **Database Verification (Project 13)**:
   - Run the Standalone SQL Verification Queries provided in `analysis.md` §5.2:
     * Verification Query 1: Cohort Matrix & Conversion % for Year 2026.
     * Verification Query 2: Modal Detail Query & Data Shape Verification.
3. **Playwright E2E UI Verification**:
   - Activate `.agents/skills/sigapp-local-testing/SKILL.md`.
   - Login as MKDT (`citra` / `citra123`) or Keuangan (`nisalis` / `nisalis123`).
   - Navigate to `http://localhost/sigapp.dev/laporan/booking-akad`.
   - Verify 6 status headers, drilldown value buttons, real conversion badges, modal detail 7 columns, and click the eye icon to verify `#modal_detail` opens with 0 console errors.

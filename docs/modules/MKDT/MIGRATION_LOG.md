# Migration Log: Modul MKDT

Log bertahap migrasi modular dan debugging untuk modul **MKDT** (Mode `REFACTOR` — 6 Fase, Zero-Downtime Architecture).

---

## Ringkasan Progres Fase

| Fase | Nama Fase | Status | Tanggal | Commit Hash |
|---|---|---|---|---|
| **Fase 0** | Baseline & Safety Verification | `COMPLETED` | 2026-11-13 | `c377b23` |
| **Fase 1** | Cleanup Dead Code + Move Modern Layer + Bugfix `/list-stock` | `COMPLETED` | 2026-11-13 | `6aebbbb` |
| **Fase 2** | Dekomposisi & Ekstraksi Legacy Layer — Subdomain Promo (`PromoController`, `PromoService`, `PromoRepository`, Shim, & Bugfix UI) | `COMPLETED` | 2026-11-13 | `8ffb065` |
| **Fase 3** | API & Web Controller Separation + Explicit Routes | `PENDING` | - | - |
| **Fase 4** | Cross-Module Alignment & Unit/Integration Tests | `PENDING` | - | - |
| **Fase 5** | Deprecation Warnings (`Mkdt::save`, `set_harga`, `get_data_by_id`, `export_xlsx`) & Cleanup | `PENDING` | - | - |
| **Fase 6** | Final QA Audit & Global Migration Ledger Sync (`docs/MIGRATION_PROGRESS.md`) | `PENDING` | - | - |

---

## Fase 0 — Baseline & Safety

- **Tanggal:** `2026-11-13`
- **Commit Baseline Bersih:** `c377b23` (`refactor(siteplan): commit pending Siteplan migration before MKDT`)
- **Hasil Verifikasi Baseline:**
  - `php spark module:status MKDT`: Status `MAPPED`, Readiness Score `60%`.
  - `tests/bin/run_booking_akad_suite.php`: `39/39` pass.
  - `vendor/bin/phpunit`: `82 tests / 882 assertions` pass.
- **Keputusan Arsitektur yang Disetujui User:**
  1. **Opsi A (Legacy Deprecation In-Place):** `Mkdt::save`, `Mkdt::set_harga`, `Mkdt::get_data_by_id`, dan `Mkdt::export_xlsx` tetap dipertahankan sebagai rute/method legacy dan akan diberi `@deprecated` + log warning pada Fase 5.
  2. **Cakupan Modul Promo:** Domain `Promo` (`app/Controllers/Promo.php` dan tabel `promo`) resmi masuk ke dalam cakupan modul MKDT.
  3. **Strategi Endpoint `/api/mkdt/*`:** Endpoint baru `/api/mkdt/*` dirancang dual-mode (`?format=raw`) dengan flag penanda untuk *follow-up* migrasi ke Full JSON.
  4. **Strategi Frontend JS/View:** Aset frontend eksisting (`public/assets/js/siteplan/mkdt.js`, view `siteplan/mkdt.php`, `kavling/*.php`, `promo/index.php`) **tidak dialihkan** ke `/api/mkdt/*` dalam migrasi ini, melainkan ditandai sebagai pekerjaan *follow-up*.
  5. **Konsolidasi Operasi Kecil:** Operasi kecil MKDT akan digabungkan pada `App\Controllers\Api\Mkdt\MkdtOperationController`.

---

## Fase 1 — Cleanup Dead Code + Move Modern Layer ke Subfolder `Mkdt/` + Bugfix `/list-stock`

- **Tanggal:** `2026-11-13`
- **Status QA:** `PASS` (Diverifikasi oleh `sigapp-qa` pada `2026-11-13 11:40`)
- **Scope:**
  1. Pembersihan dead commented code pada `app/Controllers/Mkdt.php` (baris 801–1515).
  2. Pemindahan 3 modern controllers, 8 services, dan 5 repositories MKDT ke namespace & direktori kanonik `*\Mkdt\*` disertai pembuatan 16 file *Zero-Downtime Inheritance Shims* pada namespace asal.
  3. Pembaruan routing eksplisit di `app/Config/Routes.php` untuk `laporan/booking-akad`, `api/laporan/booking-akad/*`, dan `api/nup/*` agar langsung menunjuk controller di `*\Mkdt\*`.
  4. Pembaruan inventori `MKDT` pada `app/Libraries/ModuleManager/ModuleCatalog.php` (termasuk daftar 16 shims).
  5. Remediasi bug Fase 3B pada alur `/list-stock` (`Mkdt::list_stock()` + guard null-safe pada `app/Views/siteplan/mkdt.php`).

### Daftar File Diubah & Dibuat

- **Dihapus Dead Code / Dimodifikasi (Core & Routing & Catalog):**
  - `app/Controllers/Mkdt.php` — Hapus dead commented code L801–1515; bugfix `list_stock()` mengisi `$data['data']['proyek']` via `(new \App\Services\Proyek\ActiveProyekService())->resolveAndGet()` dengan fallback aman.
  - `app/Views/siteplan/mkdt.php` — L1693, 1709, 1778, 1797: guard null-safe `esc($data['proyek']->nama_proyek ?? ...)` pada modal Turun Pembangunan dan Set Harga.
  - `app/Config/Routes.php` — Update handler ke `\App\Controllers\Web\Mkdt\BookingAkadReportController::index` dan namespace grup `App\Controllers\Api\Mkdt` untuk `api/laporan/booking-akad` & `api/nup`.
  - `app/Libraries/ModuleManager/ModuleCatalog.php` — Registrasi path baru `Api/Mkdt/*`, `Web/Mkdt/*`, `Services/Mkdt/*`, `Repositories/Mkdt/*`, serta 16 file `'shims'`.
- **File Kanonik Baru di Subfolder `Mkdt/` (16 File):**
  - Controllers (3):
    - `app/Controllers/Api/Mkdt/NupController.php`
    - `app/Controllers/Api/Mkdt/BookingAkadReportController.php`
    - `app/Controllers/Web/Mkdt/BookingAkadReportController.php`
  - Services (8):
    - `app/Services/Mkdt/MkdtHistoryService.php`
    - `app/Services/Mkdt/MkdtSettlementService.php`
    - `app/Services/Mkdt/MkdtFinancialBreakdownService.php`
    - `app/Services/Mkdt/MkdtFinancialReconciliationService.php`
    - `app/Services/Mkdt/PosisiKonsumenService.php`
    - `app/Services/Mkdt/KonsumenService.php`
    - `app/Services/Mkdt/NupService.php`
    - `app/Services/Mkdt/BookingAkadReportService.php`
  - Repositories (5):
    - `app/Repositories/Mkdt/MkdtHistoryRepository.php`
    - `app/Repositories/Mkdt/PosisiKonsumenRepository.php`
    - `app/Repositories/Mkdt/KonsumenRepository.php`
    - `app/Repositories/Mkdt/NupRepository.php`
    - `app/Repositories/Mkdt/BookingAkadReportRepository.php`
- **Zero-Downtime Shims (16 File Dipertahankan di Lokasi Asal):**
  - `app/Controllers/Api/NupController.php`
  - `app/Controllers/Web/BookingAkadReportController.php`
  - `app/Controllers/Api/BookingAkadReportController.php`
  - `app/Services/{MkdtHistoryService,MkdtSettlementService,MkdtFinancialBreakdownService,MkdtFinancialReconciliationService,PosisiKonsumenService,KonsumenService,NupService,BookingAkadReportService}.php`
  - `app/Repositories/{MkdtHistoryRepository,PosisiKonsumenRepository,KonsumenRepository,NupRepository,BookingAkadReportRepository}.php`
- **Dokumentasi:**
  - `docs/modules/MKDT/MODULE.md` (disinkronkan via `php spark module:sync MKDT` + kurasi manual).
  - `docs/modules/MKDT/MIGRATION_LOG.md` (dibuat pada Fase 4).

### Hasil Verifikasi & Command

- **PHP Syntax Lint (`php -l`):** `35 + 2` file bersih tanpa syntax error (termasuk 16 file kanonik, 16 shims, `Mkdt.php`, `Routes.php`, `ModuleCatalog.php`, dan file terkait fase 3B).
- **`php spark module:status MKDT`:** Skor MKDT naik dari `60%` (`MAPPED`) menjadi `80%` (`MAPPED`, 6 Ctrl, 8 Serv, 5 Repo, 3 Model, 44 Route, 4 Tables).
- **`php spark module:deadcode MKDT`:** Memeriksa 30 metode; ditemukan 1 kandidat dead code (`Service MkdtFinancialReconciliationService.php::apply()` dengan `0 Callers`).
- **`tests/bin/run_booking_akad_suite.php`:** `39/39` pass.
- **PHPUnit (`vendor/bin/phpunit`):** `82 tests / 882 assertions` pass.

### Bug Ditemukan, Root Cause Analysis (RCA), & Remediasi (Playwright E2E)

- **Temuan QA Awal:** Saat pengujian Playwright E2E pada rute `GET /list-stock`, server mengembalikan **HTTP 500** dengan error `Undefined array key "proyek"`.
- **Root Cause Analysis (RCA):**
  1. `App\Controllers\Mkdt::list_stock()` merender view `kavling/list-stock`, yang pada bagian modal MKDT mengikutsertakan `app/Views/siteplan/mkdt.php`.
  2. Controller `Mkdt::list_stock()` tidak pernah menginisialisasi maupun mengirim kunci `$data['data']['proyek']` ke view.
  3. Di dalam `app/Views/siteplan/mkdt.php` (L1693, 1709, 1778, 1797 pada modal `#modals-turun_pembangunan` dan `#modals-set_harga`), template langsung mengakses `$data['proyek']->nama_proyek` tanpa guard null-safe.
- **Perbaikan (Fase 3B):**
  - `app/Controllers/Mkdt.php` (`list_stock()`): Memanggil `(new \App\Services\Proyek\ActiveProyekService())->resolveAndGet()` dan menetapkan `$data['data']['proyek'] = $proyek ?? (object) ['id_proyek' => null, 'nama_proyek' => '']`.
  - `app/Views/siteplan/mkdt.php` (L1693, 1709, 1778, 1797): Menambahkan guard null-safe dan escaping `esc($data['proyek']->nama_proyek ?? ...)`.
- **Re-Verify QA:** Playwright E2E ulang pada `/list-stock` dan alur MKDT terkait mengembalikan **HTTP 200 OK**, tanpa error PHP maupun console error (`PASS`).

### Commit & Rollback

- **Commit Fase 1:** `6aebbbb` (`refactor(mkdt): phase 1 - cleanup dead comments, move modern layer, and fix list-stock proyek`)
- **Prosedur Rollback Aman:**
  ```bash
  git revert 6aebbbb
  ```
  *(Karena 16 Zero-Downtime Shims menjaga kompatibilitas penuh pemanggil eksternal, rollback cukup dengan `git revert` tanpa migrasi skema database.)*

---

## Fase 2 — Dekomposisi & Ekstraksi Legacy Layer (Subdomain Promo)

- **Tanggal:** `2026-11-13`
- **Status QA:** `PASS` (Diverifikasi oleh `sigapp-qa` pada `2026-11-13 12:01`)
- **Scope:**
  1. Ekstraksi query database tabel `promo` ke `App\Repositories\Mkdt\PromoRepository` (termasuk penghapusan anomali legacy `ClusterModel` pada `getAll()`).
  2. Ekstraksi business logic, validasi CI4, sanitasi numerik `cleanNumeric()`, format tanggal safe zero-date `formatDate()`, `statusBadge()`, dan dukungan dual-mode (`?format=raw` vs legacy DataTables/array) ke `App\Services\Mkdt\PromoService`.
  3. Pemisahan controller Promo menjadi Thin Web Controller (`App\Controllers\Web\Mkdt\PromoController`) dan Thin API Controller (`App\Controllers\Api\Mkdt\PromoController extends BaseApiController`) dengan respon JSON 404/422 terstruktur serta penanda `// TODO(full-json)`.
  4. Konversi `App\Controllers\Promo` menjadi Zero-Downtime legacy delegator shim (`initController()` meneruskan request/response/logger, 0 layer violations, metode helper diberi `@deprecated`).
  5. Registrasi rute eksplisit Web (`/promo`), API Dual-Mode (`/api/mkdt/promo/*`), dan Legacy Zero-Downtime (`/promo/*`) di `app/Config/Routes.php`, serta pembaruan `app/Libraries/ModuleManager/ModuleCatalog.php`.
  6. Penandaan `// TODO(follow-up)` pada `app/Views/promo/index.php:9` serta perbaikan Fase 3B untuk 3 bug JS eksisting hasil temuan QA Playwright E2E.

### Daftar File Diubah & Dibuat

- **File Kanonik Baru (4 File):**
  - `app/Repositories/Mkdt/PromoRepository.php` (200 baris, 7 public methods: `getProyekList()`, `getDataTables()`, `getAll()`, `getById()`, `insert()`, `updateById()`, `deactivate()`)
  - `app/Services/Mkdt/PromoService.php` (491 baris, 10 public methods: `getIndexData()`, `getDataTables()`, `getAll()`, `getOne()`, `create()`, `update()`, `deactivate()`, `formatDate()`, `statusBadge()`, `cleanNumeric()`)
  - `app/Controllers/Web/Mkdt/PromoController.php` (32 baris, 1 public method: `index()`)
  - `app/Controllers/Api/Mkdt/PromoController.php` (205 baris, 7 public methods: `meta()`, `getDataTable()`, `getAll()`, `getOne()`, `add()`, `edit()`, `remove()`)
- **File Dimodifikasi & Zero-Downtime Shim:**
  - `app/Controllers/Promo.php` (107 baris) — Diubah menjadi Zero-Downtime legacy delegator shim (`@deprecated`, 0 layer violations; mempertahankan `if_where()`, `is_active()`, `format_tgl()` sebagai `@deprecated`).
  - `app/Config/Routes.php` — Rute eksplisit Web (`GET /promo`), API Dual-Mode (`GET|POST /api/mkdt/promo/{meta,datatable,getdatatable,getDataTable,all,getall,getAll,getone,getOne,getone/(:num),getOne/(:num)}` & `POST /api/mkdt/promo/{add,edit,edit/(:num),remove,remove/(:num)}`), dan Legacy Zero-Downtime (`/promo/*`).
  - `app/Libraries/ModuleManager/ModuleCatalog.php` — Registrasi controller/service/repository Promo dan penambahan `app/Controllers/Promo.php` pada `'shims'`.
  - `app/Views/promo/index.php` — Penambahan penanda `// TODO(follow-up)` pada L9 serta perbaikan 3 bug JS Fase 3B.
- **Dokumentasi:**
  - `docs/modules/MKDT/MODULE.md` (disinkronkan via `php spark module:sync MKDT` + kurasi manual).
  - `docs/modules/MKDT/MIGRATION_LOG.md` (diperbarui pada Fase 4).

### Hasil Verifikasi & Command

- **PHP Syntax Lint (`php -l`):** Seluruh file yang dibuat dan dimodifikasi bersih tanpa syntax error.
- **`php spark module:status MKDT`:** Skor MKDT `80%` (`MAPPED`, 7 Ctrl, 9 Serv, 6 Repo, 3 Model, 40 Route, 4 Tables); `Layer Violations Detected` untuk `app/Controllers/Promo.php` kini **0** (bersih).
- **`php spark module:deadcode MKDT`:** Memeriksa 30 metode; ditemukan 2 kandidat:
  1. `Controller PromoController.php::meta()` (`0 Callers` — endpoint API metadata baru untuk konsumsi frontend *follow-up* setelah migrasi full-JSON).
  2. `Service MkdtFinancialReconciliationService.php::apply()` (`0 Callers` — kandidat eksisting dari Fase 1).
- **PHPUnit:** `10/10` pass (verifikasi QA Fase 2).

### Bug Ditemukan, Root Cause Analysis (RCA), & Remediasi (Playwright E2E)

- **Temuan QA Awal (Playwright E2E pada `/promo`):**
  1. **SyntaxError Re-deklarasi `const base_url`:** Console browser memunculkan error karena `app/Views/promo/index.php` mendeklarasikan ulang `const base_url` yang sudah dideklarasikan di `template/menu.php`.
  2. **Alur `Add -> Edit` Mengirim Duplikat ke `POST /promo/add`:** Setelah membuka/submit modal Tambah lalu menekan Edit pada baris data, form tetap mengirim ke `/promo/add` (membuat data duplikat) alih-alih `/promo/edit`.
  3. **Dropdown Proyek (`#idProyek`) Tidak Terisi & Field Tanggal Rentan Null Error saat Edit:** Pada fungsi `edit()`, nilai `r.id_proyek` tidak di-set dengan `.trigger('change')` pada Select2, serta iterasi field tanggal belum memiliki guard null-safe terhadap instance `_flatpickr`.
- **Root Cause Analysis (RCA):**
  1. Duplikasi deklarasi variabel global `const base_url` antara `promo/index.php` dan layout `template/menu.php`.
  2. Inisialisasi `$('#add-form').validate(...)` sebelumnya dipasang ulang dengan URL statis di dalam masing-masing fungsi `add()` dan `edit()`, sehingga handler `submitHandler` pertama yang terikat ke `/promo/add` tetap menang saat user berpindah dari `Add` ke `Edit`.
  3. Fungsi `edit()` tidak memicu pembaruan Select2 `#idProyek` dan tidak mengecek keberadaan elemen maupun instance `_flatpickr` sebelum memanggil `clear()`/`setDate()`.
- **Perbaikan (Fase 3B pada `app/Views/promo/index.php`):**
  1. Menghapus re-deklarasi `const base_url` dan menggunakan `window.base_url` / `base_url()` PHP helper secara konsisten.
  2. Mengubah `$('#add-form').validate(...)` menjadi inisialisasi tunggal di `$(document).ready(...)` dengan penentuan URL dinamis (`const isEdit = Boolean($('#id').val())` -> `edit` vs `add`), serta menambahkan helper `resetPromoForm()`.
  3. Mengisi `$("#idProyek").val(r.id_proyek).trigger('change')` di dalam `edit()` dan menambahkan guard null-safe (`if (input && input._flatpickr)`) saat iterasi `promoDateFields`.
- **Re-Verify QA:** Pengujian ulang Playwright E2E pada `/promo` (daftar DataTables, Add, Edit, dan Soft-Deactivate/Remove) berjalan **PASS** dengan **0 console error** dan **0 network error**.

### Commit & Rollback

- **Commit Implementasi Fase 2:** `8ffb065` (`refactor(mkdt): phase 2 - modularize Promo controller/service/repo and fix promo UI bugs`) *(catatan: commit sinkronisasi dokumen menyusul atau digabung pada commit Fase 2)*.
- **Prosedur Rollback Aman:**
  ```bash
  git revert 8ffb065
  ```
  *(Karena `App\Controllers\Promo` bertindak sebagai Zero-Downtime delegator shim dan rute `/promo/*` tetap dipertahankan, rollback dapat dilakukan aman via `git revert` tanpa perubahan skema database.)*

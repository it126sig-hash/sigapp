# Migration Log: Modul MKDT

Log bertahap migrasi modular dan debugging untuk modul **MKDT** (Mode `REFACTOR` — 6 Fase, Zero-Downtime Architecture).

---

## Ringkasan Progres Fase

| Fase | Nama Fase | Status | Tanggal | Commit Hash |
|---|---|---|---|---|
| **Fase 0** | Baseline & Safety Verification | `COMPLETED` | 2026-11-13 | `c377b23` |
| **Fase 1** | Cleanup Dead Code + Move Modern Layer + Bugfix `/list-stock` | `COMPLETED` | 2026-11-13 | `6aebbbb` |
| **Fase 2** | Dekomposisi & Ekstraksi Legacy Layer — Subdomain Promo (`PromoController`, `PromoService`, `PromoRepository`, Shim, & Bugfix UI) | `COMPLETED` | 2026-11-13 | `8ffb065` |
| **Fase 3** | API & Web Controller Separation + Explicit Routes (Subdomain Posisi Konsumen) | `COMPLETED` | 2026-11-13 | `4975daa` |
| **Fase 4** | Stock, Standing Instruction, & Batal Booking + Unit Tests | `COMPLETED` | 2026-11-13 | `873f308` |
| **Fase 5** | Deprecate Legacy `Mkdt` Methods (Opsi A), Shim Migration, & Redirect `/list-batal` | `COMPLETED` | 2026-11-13 | `(akan diisi setelah commit Fase 5)` |
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

---

## Fase 3 — Posisi Konsumen (Web & API Controller Separation + Explicit Routes)

- **Tanggal:** `2026-11-13`
- **Status QA:** `PASS` (Diverifikasi oleh `sigapp-qa` pada `2026-11-13 12:22`)
- **Scope:**
  1. Ekstraksi query metadata halaman (`getFirstProyek`, `getPphList`, `getPpnList`, `getLiKeu`, `getConfigShapeMap`, `getChecklistSubItemsWithGroup`, `getHakAksesByUser`) dan query server-side DataTables mode raw (`getRawDataTables`, `getRawDataTablesBatal`) ke `App\Repositories\Mkdt\PosisiKonsumenRepository`.
  2. Ekstraksi orkestrasi data halaman web & metadata (`getIndexData`, `userHasProjectAccess` memakai `ActiveProyekService::resolveAndGet()` dengan fallback `getFirstProyek()`), payload JSON raw DataTables (`getRawDataTables`, `getRawDataTablesBatal`), serta ekspor PDF/XLSX + pencatatan riwayat (`exportPoskon`) ke `App\Services\Mkdt\PosisiKonsumenService`.
  3. Pemisahan controller Posisi Konsumen menjadi Thin Web Controller (`App\Controllers\Web\Mkdt\PosisiKonsumenController::index`) dan Thin API Controller (`App\Controllers\Api\Mkdt\PosisiKonsumenController extends BaseApiController`) dengan dukungan dual-mode (`?format=raw` = `RAW_JSON_READY` dengan penanda `// TODO(full-json)` pada `isRawRequest()`).
  4. Konversi `App\Controllers\PosisiKonsumen` menjadi Zero-Downtime legacy delegator shim (`initController()` meneruskan request/response/logger, 0 layer violations; memperbaiki `exportPoskonPdf()` dan `exportPoskonXlsx()` legacy yang sebelumnya memanggil method `PrintService` yang tidak ada agar mendelegasikan ke `Api\Mkdt\PosisiKonsumenController::exportPoskon()`).
  5. Pembaruan routing eksplisit di `app/Config/Routes.php` untuk rute Web (`/list-kavling`, `/list-kavling/(:segment)`), rute Legacy Zero-Downtime AJAX (`/list-kavling/ambil`, `/list-kavling/akad/ambil`, `/list-kavling/batal/ambil`, `/export/poskon/(:segment)/(:segment)`, `/riwayat/poskon/(:segment)`), dan grup API dual-mode `/api/mkdt/posisi-konsumen/*` (`meta`, `datatable`, `datatable/(:segment)`, `datatable-akad`, `datatable-batal`, `export/(:segment)/(:segment)`, `riwayat/(:segment)`), serta pembaruan `app/Libraries/ModuleManager/ModuleCatalog.php` (penambahan tabel `riwayat_export_poskon` ke `owner_tables`, pembaruan `controllers`, dan pendaftaran `app/Controllers/PosisiKonsumen.php` pada `'shims'`).
  6. Penambahan penanda `<!-- TODO(follow-up) -->` pada `app/Views/kavling/list-kavling.php`, `list-kavling-akad.php`, `list-kavling-batal.php`, serta perbaikan Fase 3B untuk bug double-nesting `$data['data'][...]` pada `app/Views/kavling/list-kavling.php` (L716–721 & L1634).

### Daftar File Diubah & Dibuat

- **File Kanonik Baru (2 File):**
  - `app/Controllers/Web/Mkdt/PosisiKonsumenController.php` (37 baris, 1 public method: `index($status = null)`)
  - `app/Controllers/Api/Mkdt/PosisiKonsumenController.php` (144 baris, 6 public methods: `meta()`, `getDataTables($status = null)`, `getDataTablesAkad()`, `getDataTablesBatal()`, `exportPoskon($tipe = 'xlsx', $status = 'aktif')`, `getRiwayatExport($status = 'Aktif')`)
- **File Dimodifikasi & Zero-Downtime Shim:**
  - `app/Repositories/Mkdt/PosisiKonsumenRepository.php` (575 baris, 13 public methods: tambah `getFirstProyek`, `getPphList`, `getPpnList`, `getLiKeu`, `getConfigShapeMap`, `getChecklistSubItemsWithGroup`, `getHakAksesByUser`, `getRawDataTables`, `getRawDataTablesBatal`)
  - `app/Services/Mkdt/PosisiKonsumenService.php` (821 baris, 11 public methods: tambah `getIndexData`, `userHasProjectAccess`, `getRawDataTables`, `getRawDataTablesBatal`, `exportPoskon`)
  - `app/Controllers/PosisiKonsumen.php` (96 baris) — Diubah menjadi Zero-Downtime legacy delegator shim (`@deprecated`, 0 layer violations; memperbaiki delegasi `exportPoskonPdf`/`exportPoskonXlsx`; mempertahankan `format_tgl` dan `is_active` sebagai `@deprecated`).
  - `app/Config/Routes.php` — Memperbarui rute Web & Legacy AJAX `/list-kavling*`, `/export/poskon/*`, `/riwayat/poskon/*` ke controller kanonik dan mendaftarkan grup `/api/mkdt/posisi-konsumen/*`.
  - `app/Libraries/ModuleManager/ModuleCatalog.php` — Pembaruan daftar `controllers`, penambahan `app/Controllers/PosisiKonsumen.php` di `shims`, dan penambahan `riwayat_export_poskon` di `owner_tables`.
  - `app/Views/kavling/list-kavling.php` — L1 penanda `TODO(follow-up)`; L716–721 & L1634 perbaikan Fase 3B pembacaan variabel view langsung (non-double-nested) dan passing data ke partial `siteplan/partials/modal_detail`.
  - `app/Views/kavling/list-kavling-akad.php` — L1 penanda `TODO(follow-up)`.
  - `app/Views/kavling/list-kavling-batal.php` — L1 penanda `TODO(follow-up)`.
- **Dokumentasi:**
  - `docs/modules/MKDT/MODULE.md` (disinkronkan via `php spark module:sync MKDT` + kurasi manual).
  - `docs/modules/MKDT/MIGRATION_LOG.md` (diperbarui pada Fase 4).

### Hasil Verifikasi & Command

- **PHP Syntax Lint (`php -l`):** Seluruh file PHP yang dibuat dan dimodifikasi (`Web/Mkdt/PosisiKonsumenController.php`, `Api/Mkdt/PosisiKonsumenController.php`, `PosisiKonsumen.php`, `PosisiKonsumenService.php`, `PosisiKonsumenRepository.php`, `Routes.php`, `ModuleCatalog.php`) bersih (`No syntax errors detected`).
- **`php spark module:status MKDT`:** Skor MKDT `80%` (`MAPPED`, `8 Ctrl`, `9 Serv`, `6 Repo`, `3 Model`, `37 Route`, `5 Tables`); `Layer Violations Detected` untuk `app/Controllers/PosisiKonsumen.php` kini **0** (hanya menyisakan `app/Controllers/Mkdt.php`).
- **`php spark module:deadcode MKDT`:** Memeriksa 30 metode; ditemukan 4 kandidat `0 Callers`:
  1. `Controller PosisiKonsumenController.php::meta()` (endpoint API metadata baru untuk konsumsi frontend *follow-up*).
  2. `Controller PromoController.php::meta()` (kandidat dari Fase 2 untuk konsumsi *follow-up*).
  3. `Service MkdtFinancialReconciliationService.php::apply()` (kandidat eksisting dari Fase 1).
  4. `Service PosisiKonsumenService.php::exportPoskon()` (dipanggil oleh `Api\Mkdt\PosisiKonsumenController::exportPoskon()`).
- **PHPUnit:** `10/10` pass (verifikasi QA Fase 3); verifikasi tambahan unit test MKDT (`tests/unit/MkdtFinancialBreakdownServiceTest.php` + `tests/unit/MkdtConsumerReplacementHistoryTest.php`): `13/13` pass (`31 assertions`).
- **Playwright E2E & Dual-Mode `?format=raw`:** Seluruh rute web (`/list-kavling`, `/list-kavling/akad`, `/list-kavling/batal`) serta endpoint `/api/mkdt/posisi-konsumen/{meta,datatable,datatable-akad,datatable-batal,export,riwayat}` (termasuk mode `?format=raw`) terverifikasi **PASS** dengan **0 console error**.

### Bug Ditemukan, Root Cause Analysis (RCA), & Remediasi (Playwright E2E)

- **Temuan QA Awal (Playwright E2E pada `/list-kavling`):**
  - Halaman `/list-kavling` memicu error PHP/view & kerusakan inisialisasi variabel JS (`has_akses`, `pph`, `ppn`, `li_keu`, `conf`, `proyek`) serta payload partial `siteplan/partials/modal_detail`.
- **Root Cause Analysis (RCA):**
  1. View `app/Views/kavling/list-kavling.php` (baris 716–721) sebelumnya membaca `$data['data']['has_akses']`, `$data['data']['pph']`, `$data['data']['ppn']`, `$data['data']['li_keu']`, `$data['data']['conf']`, dan `$data['data']['proyek']` (double-nesting), sementara `Web\Mkdt\PosisiKonsumenController::index()` mengirim `view('template', ['content' => $indexData['content'], 'data' => $indexData['data']])` sehingga di dalam view konten, CodeIgniter sudah mengekstrak `$data` menjadi variabel langsung (`$has_akses`, `$pph`, `$ppn`, `$li_keu`, `$conf`, `$proyek`) dan tidak lagi memiliki `$data['data']`.
  2. Baris 1634 `list-kavling.php` meneruskan parameter view ke `siteplan/partials/modal_detail` tanpa menyesuaikan fallback struktur `$data['data']` vs `$data`.
- **Perbaikan (Fase 3B pada `app/Views/kavling/list-kavling.php` L716–721 & L1634):**
  1. Mengubah L716–721 agar membaca variabel langsung dengan fallback aman: `$has_akses ?? $data['data']['has_akses'] ?? ['proyek' => false, 'legal' => false]`, `$pph ?? $data['data']['pph'] ?? []`, `$ppn ?? $data['data']['ppn'] ?? []`, `$li_keu ?? $data['data']['li_keu'] ?? '[]'`, `$conf ?? $data['data']['conf'] ?? '{}'`, dan `$proyek ?? $data['data']['proyek'] ?? null`.
  2. Memperbaiki L1634 pemanggilan `view('siteplan/partials/modal_detail', isset($data['data']) ? ['data' => $data['data']] : (isset($data) ? $data : []))`.
- **Re-Verify QA:** Pengujian ulang Playwright E2E pada `/list-kavling`, `/list-kavling/akad`, `/list-kavling/batal`, dan endpoint dual-mode `?format=raw` mengembalikan **PASS** dengan **0 console error** dan **0 network error**.

### Commit & Rollback

- **Commit Implementasi Fase 3:** `4975daa` (`refactor(mkdt): phase 3 - modularize PosisiKonsumen and fix list-kavling view variables`)
- **Prosedur Rollback Aman:**
  ```bash
  git revert 4975daa
  ```
  *(Karena `App\Controllers\PosisiKonsumen` tetap dipertahankan sebagai Zero-Downtime delegator shim dan seluruh rute `/list-kavling*`, `/export/poskon/*`, `/riwayat/poskon/*` tetap kompatibel dalam mode default, rollback aman dilakukan via `git revert 4975daa` tanpa perubahan skema database.)*

---

## Fase 4 — Stock, Standing Instruction, & Batal Booking

- **Tanggal:** `2026-11-13`
- **Status QA:** `PASS` (Diverifikasi oleh `sigapp-qa`)
- **Scope:**
  1. Dekomposisi & ekstraksi query database subdomain Stock (`kavling`, `produksi`, `mkdt`, `konsumen`), Standing Instruction (`list_si`, `si`), dan Batal Booking (`mkdt`, `konsumen`, `log_pembayaran`) ke `App\Repositories\Mkdt\{StockRepository,StandingInstructionRepository,BatalBookingRepository}`.
  2. Ekstraksi business logic, sanitasi numerik `cleanNumeric()`, kalkulasi ulang biaya/pembayaran dari DB, upload & akses berkas via `FileAccessService`, transaksi database atomic (`transStart`/`transComplete`), audit trail `MkdtHistoryService::log()`, dan notifikasi `NotificationEvent::BATAL_BOOKING` ke `App\Services\Mkdt\{StockService,StandingInstructionService,BatalBookingService}`.
  3. Pemisahan Web Controller (`App\Controllers\Web\Mkdt\StockController::index`) dan konsolidasi operasi kecil MKDT pada API Controller (`App\Controllers\Api\Mkdt\MkdtOperationController extends BaseApiController`) dengan dukungan dual-mode (`?format=raw` + penanda `// TODO(full-json)`).
  4. Konversi 6 metode pada `App\Controllers\Mkdt` (`list_stock`, `getListStock`, `getsi`, `saveSI`, `batal_mkdt`, `simpan_batal`) menjadi `@deprecated` Zero-Downtime delegator shims yang meneruskan eksekusi ke `StockController` dan `MkdtOperationController` via `initController()`.
  5. Registrasi rute eksplisit Web (`/list-stock`), API Dual-Mode (`/api/mkdt/operations/meta`, `/api/mkdt/stock/list`, `/api/mkdt/si`, `/api/mkdt/si/save`, `/api/mkdt/batal/detail`, `/api/mkdt/batal/simpan`), dan Legacy Zero-Downtime (`/mkdt/{getListStock,getliststock,getsi,saveSI,savesi,batal_mkdt,simpan_batal}`) di `app/Config/Routes.php`, serta pembaruan `app/Libraries/ModuleManager/ModuleCatalog.php` (penambahan owner tables `si`, `list_si` dan registrasi file Fase 4).
  6. Penambahan penanda `<!-- TODO(follow-up): migrate /mkdt/getListStock AJAX call to /api/mkdt/stock/list?format=raw -->` di baris 1 `app/Views/kavling/list-stock.php` serta pembuatan unit test `tests/unit/MkdtOperationServicesTest.php`.

### Daftar File Diubah & Dibuat

- **Repositories Baru (3 File):**
  - `app/Repositories/Mkdt/StockRepository.php` (161 baris, 1 public method: `getStockListDataTables()`)
  - `app/Repositories/Mkdt/StandingInstructionRepository.php` (99 baris, 6 public methods: `getByKavling()`, `getSiListIdBySiId()`, `getListSiNameById()`, `getKavlingMkdtId()`, `insertSi()`, `updateSi()`)
  - `app/Repositories/Mkdt/BatalBookingRepository.php` (87 baris, 5 public methods: `getMkdtDetailForBatal()`, `getMkdtRawById()`, `getPembayaranLogsByMkdt()`, `getProyekIdByKavling()`, `updateMkdtBatal()`)
- **Services Baru (3 File):**
  - `app/Services/Mkdt/StockService.php` (173 baris, 4 public methods: `getIndexData()`, `getListStock()`, `formatTanggal()`, `formatDateTime()`)
  - `app/Services/Mkdt/StandingInstructionService.php` (189 baris, 2 public methods: `getByKavling()`, `save()`)
  - `app/Services/Mkdt/BatalBookingService.php` (269 baris, 3 public methods: `cleanNumeric()`, `getDetail()`, `cancelBooking()`)
- **Controllers Baru (2 File):**
  - `app/Controllers/Web/Mkdt/StockController.php` (26 baris, 1 public method: `index()`)
  - `app/Controllers/Api/Mkdt/MkdtOperationController.php` (218 baris, 6 public methods: `meta()`, `getListStock()`, `getSi()`, `saveSi()`, `getBatal()`, `simpanBatal()`)
- **File Dimodifikasi & Shims:**
  - `app/Controllers/Mkdt.php` (798 baris) — Tambah `initController()` forwarding ke `StockController` & `MkdtOperationController`, serta ubah `list_stock()`, `getListStock()`, `getsi()`, `saveSI()`, `batal_mkdt()`, `simpan_batal()` menjadi `@deprecated` Zero-Downtime shims.
  - `app/Config/Routes.php` — Rute Web `GET /list-stock` ke `Web\Mkdt\StockController::index`, grup API dual-mode `/api/mkdt/{operations/meta,stock/list,si,si/save,batal/detail,batal/simpan}`, dan rute legacy Zero-Downtime `/mkdt/{getListStock,getliststock,getsi,saveSI,savesi,batal_mkdt,simpan_batal}` langsung ke `Api\Mkdt\MkdtOperationController`.
  - `app/Libraries/ModuleManager/ModuleCatalog.php` — Daftarkan 2 controller, 3 service, dan 3 repository Fase 4 serta tambahkan `si` dan `list_si` pada `owner_tables` MKDT.
  - `app/Views/kavling/list-stock.php` — Baris 1 penanda `<!-- TODO(follow-up): migrate /mkdt/getListStock AJAX call to /api/mkdt/stock/list?format=raw -->`.
- **Tests:**
  - `tests/unit/MkdtOperationServicesTest.php` (6 test cases: `testBatalBookingCleanNumericSanitizesVariousFormats`, `testBatalBookingGetDetailHandlesInvalidAndMissingId`, `testBatalBookingGetDetailComputesTotalsPaidAndAccessUrl`, `testCancelBookingValidatesIdsAndStoresSuratBatalBeforeUpdate`, `testStockServiceFormatsLegacyAndRawPayloadsWithSafeDates`, `testStandingInstructionServiceSaveValidatesAndCommits`).
- **Dokumentasi:**
  - `docs/modules/MKDT/MODULE.md` (disinkronkan via `php spark module:sync MKDT` + kurasi manual).
  - `docs/modules/MKDT/MIGRATION_LOG.md` (entri Fase 4 & pembaruan commit hash Fase 3 `4975daa`).

### Kontrak Dual-Mode (`/api/mkdt/*?format=raw`)

- Seluruh endpoint operasi di `App\Controllers\Api\Mkdt\MkdtOperationController` (`getListStock()`, `getSi()`, `saveSi()`, `getBatal()`, `simpanBatal()`) menyertakan penanda `// TODO(full-json): switch default to raw JSON once legacy DataTables views migrate`.
- **Mode Default (Legacy Compatible):** Mengembalikan payload JSON legacy (DataTables `draw`/`recordsTotal`/`recordsFiltered`/`data` + `token`, atau `{token, success, messages}` / `{data, token, sudah_bayar, total_biaya}`) agar view eksisting (`kavling/list-stock.php`, `siteplan/mkdt.php`, `siteplan/mkdt.js`) tetap berjalan tanpa perubahan.
- **Mode `?format=raw`:** Mengembalikan envelope `BaseApiController` (`success` / `error` HTTP 404/422) dengan payload JSON bersih bebas HTML:
  - `GET|POST /api/mkdt/operations/meta`
  - `GET|POST /api/mkdt/stock/list?format=raw`
  - `GET|POST /api/mkdt/si?format=raw`
  - `POST /api/mkdt/si/save?format=raw`
  - `GET|POST /api/mkdt/batal/detail?format=raw`
  - `POST /api/mkdt/batal/simpan?format=raw`

### Bug Ditemukan, Root Cause Analysis (RCA), & Remediasi (`sigapp-explorer` / `sigapp-engineer`)

1. **Bug Urutan `simpan_batal` (`surat_batal` Gagal Tersimpan di Database):**
   - **RCA:** Pada implementasi legacy `Mkdt::simpan_batal()`, query update tabel `mkdt` dieksekusi lebih dulu sebelum pemindahan/penyimpanan file `surat_batal`, sehingga path file unggahan `surat_batal` tidak ikut tersimpan pada baris `mkdt` dalam satu alur persistensi.
   - **Perbaikan (`BatalBookingService::cancelBooking()`):** File `file_surat_batal` kini divalidasi dan disimpan terlebih dahulu ke `uploads/batal/{Ymd}/` melalui `FileAccessService::storeAs()` **sebelum** transaksi database `updateMkdtBatal($idMkdt, $updateData)` dijalankan (`FILE_STORED_BEFORE_UPDATE: YES`).
2. **Bug `batal_mkdt` Tidak Menyertakan `surat_batal_access_url`:**
   - **RCA:** Endpoint detail pembatalan `batal_mkdt` sebelumnya tidak menghasilkan URL akses aman untuk file `surat_batal` via `FileAccessService`.
   - **Perbaikan (`BatalBookingService::getDetail()` & `cancelBooking()`):** Menyisipkan field `surat_batal_access_url` melalui `$this->fileAccessService->accessUrl('mkdt_surat_batal', $idMkdt)` ketika `surat_batal` terisi.

### Hasil Verifikasi & Command (QA `PASS`)

- **Playwright E2E:** Pengujian halaman `/list-stock`, modal Standing Instruction (`#modals-si`), modal Batal Booking (`#modal-batal`), serta endpoint dual-mode `/api/mkdt/*?format=raw` seluruhnya **PASS**.
- **PHPUnit:**
  - `tests/unit/MkdtOperationServicesTest.php`: **6 tests / 55 assertions** PASS.
  - Gabungan unit test modul MKDT: **19 tests / 86 assertions** PASS.
- **Suite Laporan Booking & Akad (`tests/bin/run_booking_akad_suite.php`):** **39/39 PASS**.
- **DB Transaction Rollback Check:** Terverifikasi `FILE_STORED_BEFORE_UPDATE: YES` (file tersimpan sebelum pembaruan DB dan rollback aman saat transaksi gagal).
- **`php spark module:status`:** MKDT berada pada skor **80%** (`MAPPED`: `10 Ctrl`, `12 Serv`, `9 Repo`, `3 Model`, `44 Route`, `7 Tables`).

### Commit & Rollback

- **Commit Implementasi Fase 4:** `873f308`
- **Prosedur Rollback Aman:**
  ```bash
  git revert 873f308
  ```
  *(Karena 6 metode pada `App\Controllers\Mkdt` tetap dipertahankan sebagai `@deprecated` Zero-Downtime shims dan mode default endpoint `/api/mkdt/*` tetap kompatibel dengan frontend legacy, rollback dapat dilakukan aman via `git revert` tanpa perubahan skema database.)*

---

## Fase 5 — Deprecate Legacy `Mkdt` Methods (Opsi A) & Shim Migration

- **Tanggal:** `2026-11-13`
- **Status QA:** `PASS` (Diverifikasi oleh `sigapp-qa`)
- **Scope:**
  1. Menandai `App\Controllers\Mkdt` sebagai `@deprecated Zero-Downtime legacy shim` serta memindahkannya dari daftar `'controllers'` ke `'shims'` pada `ModuleCatalog::MODULES['MKDT']`.
  2. Menghapus dead code yatim di `app/Controllers/Mkdt.php` (`$lpModel`, `$username`, `$mkdtHistoryService`, `get_kavling()`, `get_kavling_konsumen()`).
  3. Menerapkan keputusan Opsi A (Legacy Deprecation In-Place) dengan anotasi `@deprecated` + logging observasi `log_message('warning', 'DEPRECATED_MKDT_HIT: ...')` pada 4 method legacy utama (`get_data_by_id()`, `save()`, `set_harga()`, `export_xlsx()`) dan 3 orphan public helper (`if_where()`, `format_tgl()`, `is_active()`), serta `@deprecated` pada helper internal `num()`.
  4. Memperbaiki rute broken `GET /list-batal` (yang sebelumnya menunjuk `Mkdt::list_batal`) menjadi `$routes->addRedirect('/list-batal', '/list-kavling/batal')` pada `app/Config/Routes.php` L52.

### Daftar File Diubah

- `app/Controllers/Mkdt.php` (775 baris) — Tambah class-level `@deprecated Zero-Downtime legacy shim`; hapus properti/method yatim (`$lpModel`, `$username`, `$mkdtHistoryService`, `get_kavling()`, `get_kavling_konsumen()`); tambah `@deprecated` + `log_message('warning', 'DEPRECATED_MKDT_HIT: ...')` pada 7 method publik (`get_data_by_id`, `save`, `set_harga`, `export_xlsx`, `if_where`, `format_tgl`, `is_active`); tambah `@deprecated` pada `protected function num($d)`.
- `app/Config/Routes.php` (L52) — Mengganti rute broken `/list-batal` -> `$routes->addRedirect('/list-batal', '/list-kavling/batal')`.
- `app/Libraries/ModuleManager/ModuleCatalog.php` — Memindahkan `app/Controllers/Mkdt.php` dari `'controllers'` ke `'shims'` pada definisi modul `MKDT`.
- **Dokumentasi:**
  - `docs/modules/MKDT/MODULE.md` (disinkronkan via `php spark module:sync MKDT` -> status `REFACTORED | 100/100`, plus kurasi manual `## Key Functions`, `## Deprecated & Legacy Shims`, dan `## Change Log`).
  - `docs/modules/MKDT/MIGRATION_LOG.md` (pengisian commit hash Fase 4 `873f308` dan penambahan entri Fase 5).

### Daftar Method `@deprecated` & Hook Observasi `DEPRECATED_MKDT_HIT:`

Seluruh method di bawah ini tetap dipertahankan sementara untuk masa observasi log sebelum penghapusan permanen pada tahap *follow-up*:

- **4 Method Opsi A (`log_message('warning', 'DEPRECATED_MKDT_HIT: ...')`):**
  1. `get_data_by_id($st = null)` -> gunakan `App\Controllers\Api\Transaksi\TransaksiController`.
  2. `save()` -> gunakan `App\Controllers\Api\Transaksi\TransaksiController`.
  3. `set_harga()` -> gunakan `App\Controllers\Api\Transaksi\HargajualController::set_harga()`.
  4. `export_xlsx()` -> dipertahankan sementara (belum ada pengganti modular).
- **3 Orphan Public Helpers (`log_message('warning', 'DEPRECATED_MKDT_HIT: ...')`):**
  5. `if_where($var, $column, $condition, $query)`
  6. `format_tgl($tgl)`
  7. `is_active($id, $texts, $textf)`
- **1 Internal Helper (`@deprecated`):**
  8. `num($d)` (`protected`, hanya digunakan oleh `save()` & `set_harga()`).
- **6 Method Delegator Shim (dari Fase 4, `@deprecated`):**
  9. `list_stock()`, `getListStock()`, `getsi()`, `saveSI()`, `batal_mkdt()`, `simpan_batal()`.

### Hasil Verifikasi & Command (QA `PASS`)

- **Playwright E2E:**
  - Navigasi `GET /list-batal` -> HTTP **302** redirect ke `/list-kavling/batal` (`PASS`).
  - Navigasi `GET /list-stock` -> HTTP **200 OK**, tanpa console error maupun error PHP (`PASS`).
- **Verifikasi Log Observasi `DEPRECATED_MKDT_HIT:`:**
  - Terkonfirmasi tercatat di log aplikasi: `DEPRECATED_MKDT_HIT: App\Controllers\Mkdt::format_tgl called by user 17` (`PASS`).
- **`php spark module:status`:**
  - `MKDT`: `REFACTORED | 100% | Ctrl: 9 | Serv: 12 | Repo: 9 | Model: 3 | Route: 29 | Tables: 7`.
- **`php spark module:deadcode MKDT`:**
  - Memeriksa 30 metode; ditemukan 6 kandidat (`0 Callers`: `PosisiKonsumenController::meta()`, `PromoController::meta()`, `MkdtOperationController::meta()`, `MkdtOperationController::getListStock()`, `MkdtOperationController::getSi()`, `MkdtFinancialReconciliationService::apply()`). Tidak ada lagi dead code yatim di `Mkdt.php` dalam inventori controller aktif.
- **PHPUnit (`tests/unit/MkdtOperationServicesTest.php`):**
  - **6/6 tests pass (55 assertions)**.
- **Suite Laporan Booking & Akad (`tests/bin/run_booking_akad_suite.php`):**
  - **39/39 PASS**.

### Commit & Rollback

- **Commit Implementasi Fase 5:** `(akan diisi setelah commit Fase 5)`
- **Prosedur Rollback Aman:**
  ```bash
  git revert <hash_fase_5>
  ```
  *(Karena 4 method Opsi A dan 3 orphan public helpers pada `App\Controllers\Mkdt` hanya diberi anotasi `@deprecated` + log observasi tanpa menghapus endpoint aktif, dan `/list-batal` dialihkan via redirect HTTP 302, rollback aman dilakukan via `git revert` tanpa perubahan skema database.)*

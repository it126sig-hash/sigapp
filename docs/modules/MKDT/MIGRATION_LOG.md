# Migration Log: Modul MKDT

Log bertahap migrasi modular dan debugging untuk modul **MKDT** (Mode `REFACTOR` — 6 Fase, Zero-Downtime Architecture).

---

## Ringkasan Progres Fase

| Fase | Nama Fase | Status | Tanggal | Commit Hash |
|---|---|---|---|---|
| **Fase 0** | Baseline & Safety Verification | `COMPLETED` | 2026-11-13 | `c377b23` |
| **Fase 1** | Cleanup Dead Code + Move Modern Layer + Bugfix `/list-stock` | `PASS` (QA Verified) | 2026-11-13 | `<hash_fase_1>` (pending commit) |
| **Fase 2** | Dekomposisi & Ekstraksi Legacy Layer (PosisiKonsumen / Promo / MKDT Core) | `PENDING` | - | - |
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

- **Commit Fase 1:** `<hash_fase_1>` *(diisi setelah Master Orchestrator melakukan commit)*
- **Prosedur Rollback Aman:**
  ```bash
  git revert <hash_fase_1>
  ```
  *(Karena 16 Zero-Downtime Shims menjaga kompatibilitas penuh pemanggil eksternal, rollback cukup dengan `git revert` tanpa migrasi skema database.)*

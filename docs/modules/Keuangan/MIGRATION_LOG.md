# Migration Log: Modul Keuangan

Log bertahap migrasi modular dan debugging untuk modul **Keuangan** (Mode `REFACTOR` — 6 Fase, Zero-Downtime Architecture).

---

## Ringkasan Progres Fase

| Fase | Nama Fase | Status | Tanggal | Commit Hash |
|---|---|---|---|---|
| **Fase 0** | Baseline & Safety Verification | `COMPLETED` | 2026-11-13 | `8204898` *(baseline)* |
| **Fase 1** | Migrasi Layer Service & Repository ke `*\Keuangan\*` + Subdomain Laporan, Bank & BookingPayment | `COMPLETED` | 2026-11-13 | *(working tree — pra-commit)* |
| **Fase 2** | Dekomposisi & Ekstraksi Legacy Layer — Gelombang 1 | `PENDING` | — | — |
| **Fase 3** | Dekomposisi & Ekstraksi Legacy Layer — Gelombang 2 | `PENDING` | — | — |
| **Fase 4** | Dekomposisi & Ekstraksi Legacy Layer — Gelombang 3 & Konsolidasi API/Web | `PENDING` | — | — |
| **Fase 5** | Deprecate Legacy Methods (Opsi A) & Shim Migration | `PENDING` | — | — |
| **Fase 6** | Final Cross-Module QA Audit & Global Migration Ledger Sync (`docs/MIGRATION_PROGRESS.md`) | `PENDING` | — | — |

---

## Fase 0 — Baseline & Safety Verification

- **Tanggal:** `2026-11-13`
- **Commit Baseline Terakhir di Repo:** `8204898` (`docs(mkdt): phase 6 - finalize MKDT modular refactor and update MIGRATION_PROGRESS`)
- **Catatan Pre-Existing Uncommitted Files (Di Luar Modul Keuangan):**
  - Terdapat perubahan pre-existing yang belum di-commit untuk subdomain `PengaturanWarna*` (Modul **Settings**):
    - `app/Models/PengaturanWarnaModel.php`
    - `app/Repositories/Settings/PengaturanWarnaRepository.php`
    - `app/Services/Settings/PengaturanWarnaService.php`
    - `docs/modules/Settings/MODULE.md`
    - `writable/modules/module_map.json`
  - File-file tersebut berada di luar cakupan modul Keuangan dan **tidak tersentuh** oleh migrasi modul Keuangan.
- **Hasil Verifikasi Baseline (`sigapp-qa`):**
  - **`php spark module:map`:** `0 unmapped files` (`17` modul terpetakan).
  - **`php spark module:status Keuangan`:** `MAPPED | 60% | Ctrl: 19 | Serv: 16 | Repo: 13 | Model: 16 | Route: 134 | Tables: 25` *(Rata-rata global: `91.2%`, `14 dari 17` modul berstatus `REFACTORED`)*.
  - **`php spark module:deadcode Keuangan`:** Memeriksa `30` metode, ditemukan `4` kandidat dead code awal:
    1. `KeuanganService::simpanDanaAkad()`
    2. `KeuanganService::simpanInvoice()`
    3. `KeuanganService::getListTagihanJatuhTempoGrouped()`
    4. `KeuanganService::getListTagihanJatuhTempoDetail()`
  - **PHPUnit (`--no-coverage`):**
    - **7 Unit Test files:** `27 tests, 98 assertions` — **PASS**
    - **4 Database Test files:** `19 tests, 71 assertions` — **PASS**
    - **Total:** `46 tests, 169 assertions` — **PASS**
- **Keputusan Arsitektur yang Disetujui User:**
  1. **Opsi A (Deprecation In-Place):** `Keuangan.php`, `Direksi.php`, dan `DanaAkad.php` dipertahankan di lokasi legacy-nya dengan pendekatan deprecation in-place (anotasi `@deprecated` + log observasi pada fase terkait) demi menjaga Zero-Downtime terhadap pemanggil legacy.
  2. **Konsolidasi `DanaJaminanController` + Shim `PencairanJaminan`:** Fungsionalitas terkait dikonsolidasikan ke `DanaJaminanController` dengan mempertahankan `PencairanJaminan` sebagai Zero-Downtime legacy shim selama masa transisi.
  3. **Dual-Mode API (`?format=raw`):** Endpoint API baru `/api/keuangan/*` (serta endpoint terstruktur terkait) mendukung dual-mode `?format=raw` untuk kompatibilitas bertahap sebelum migrasi penuh ke format JSON standar.
  4. **Minimum Diff (Tanpa Rombak 9 File JS Eksisting):** 9 file JavaScript eksisting milik modul Keuangan dipertahankan tanpa perombakan besar pada refaktor ini (minimum diff) untuk menekan risiko regresi UI.
  5. **Continuous Debugging Log di `MIGRATION_LOG.md`:** Seluruh hasil verifikasi, temuan bug QA/E2E, Root Cause Analysis (RCA), remediasi, commit hash, dan prosedur rollback dicatat secara berkelanjutan di `docs/modules/Keuangan/MIGRATION_LOG.md` pada setiap fase.

---

## Fase 1 — Migrasi Layer Service & Repository ke `*\Keuangan\*` + Subdomain Laporan, Bank & BookingPayment

- **Tanggal:** `2026-11-13`
- **Status QA:** `PASS` *(Diverifikasi independen oleh `sigapp-qa` pada 2026-11-13 19:46 WIB)*
- **Commit Hash:** *(pra-commit di working tree; akan diisi setelah commit Fase 1)*

### 1. Scope Fase 1
1. Memindahkan seluruh **14 Services** dan **11 Repositories** modul Keuangan dari root namespace (`App\Services\*`, `App\Repositories\*`) ke subfolder terstruktur `App\Services\Keuangan\*` dan `App\Repositories\Keuangan\*`.
2. Membuat **`BankRepository.php`** di `app/Repositories/Keuangan/BankRepository.php` dan memindahkan akses `ListBankModel` dari `BankService` ke layer Repository (`getAllBanks()`, `getActiveBanks()`, `getBankById()`, `insertBank()`, `updateBank()`, `deleteBank()`), disertai penambahan `getAllActive()`, `getList()`, dan `getOne()` pada `App\Services\Keuangan\BankService`.
3. Membuat **7 Controller modular baru** di `App\Controllers\Web\Keuangan\*` & `App\Controllers\Api\Keuangan\*` untuk subdomain **Bank**, **CashInReport**, **HasilAkadReport**, dan **BookingPayment**, serta menambahkan `findProjectIdByMkdt()` pada `BookingPaymentService` dan `BookingPaymentRepository` untuk otorisasi proyek di `Api\Keuangan\BookingPaymentController::verify()`.
4. Menjaga kompatibilitas penuh (*Zero-Downtime*) melalui **32 file shim** `@deprecated` pada namespace/lokasi asal (7 Controller shims, 14 Service shims, 11 Repository shims).
5. Menambahkan endpoint metadata (`/meta`) dan kontrak **Dual-Mode API (`?format=raw`)** tanpa memecah format respons DataTables HTML/legacy eksisting (minimum diff, 0 perubahan JS/View).

### 2. Daftar File Diubah & Dibuat
- **Controllers Modular Baru (7 file):**
  - `app/Controllers/Web/Keuangan/BankController.php` (`index()`)
  - `app/Controllers/Api/Keuangan/BankController.php` (`meta()`, `list()`, `ambil()`, `ambilSatu()`, `simpan()`, `hapus()`)
  - `app/Controllers/Web/Keuangan/CashInReportController.php` (`index()`)
  - `app/Controllers/Api/Keuangan/CashInReportController.php` (`meta()`, `summary()`, `detail()`)
  - `app/Controllers/Web/Keuangan/HasilAkadReportController.php` (`index()`)
  - `app/Controllers/Api/Keuangan/HasilAkadReportController.php` (`meta()`, `summary()`, `detail()`)
  - `app/Controllers/Api/Keuangan/BookingPaymentController.php` (`verify()`)
- **Services Modular (`app/Services/Keuangan/`, 14 file dipindahkan):**
  - `KeuanganService.php`, `PembayaranService.php`, `PajakService.php`, `FinanceLedgerService.php`, `BankKprDisbursementService.php`, `BankService.php` *(+ `getAllActive()`, `getList()`, `getOne()`)*, `BookingPaymentService.php` *(+ `findProjectIdByMkdt()`)*, `BookingFeeMigrationService.php`, `PencairanAkadService.php`, `CashOutService.php`, `CashInReportService.php`, `HasilAkadReportService.php`, `PrintService.php`, `DanaJaminanService.php`.
- **Repositories Modular (`app/Repositories/Keuangan/`, 1 baru + 11 dipindahkan):**
  - Baru: `BankRepository.php`.
  - Dipindahkan: `KeuanganRepository.php`, `LogPembayaranRepository.php`, `PaymentSummaryRepository.php`, `FinanceLedgerRepository.php`, `PajakRepository.php`, `BookingPaymentRepository.php` *(+ `findProjectIdByMkdt()`)*, `PencairanAkadListRepository.php`, `CashOutRepository.php`, `CashInReportRepository.php`, `HasilAkadReportRepository.php`, `SpptbRepository.php`.
- **Zero-Downtime Shims (32 file di lokasi asal, bertanda `@deprecated`):**
  - **Controller Shims (7):** `app/Controllers/ListBank.php`, `app/Controllers/Api/BankController.php`, `app/Controllers/Api/BookingPaymentController.php`, `app/Controllers/Web/CashInReportController.php`, `app/Controllers/Api/CashInReportController.php`, `app/Controllers/Web/HasilAkadReportController.php`, `app/Controllers/Api/HasilAkadReportController.php`.
  - **Service Shims (14):** `app/Services/{KeuanganService,PembayaranService,PajakService,FinanceLedgerService,BankKprDisbursementService,BankService,BookingPaymentService,BookingFeeMigrationService,PencairanAkadService,CashOutService,CashInReportService,HasilAkadReportService,PrintService,DanaJaminanService}.php`.
  - **Repository Shims (11):** `app/Repositories/{KeuanganRepository,LogPembayaranRepository,PaymentSummaryRepository,FinanceLedgerRepository,PajakRepository,BookingPaymentRepository,PencairanAkadListRepository,CashOutRepository,CashInReportRepository,HasilAkadReportRepository,SpptbRepository}.php`.
- **Konfigurasi, Katalog & Dokumentasi:**
  - `app/Config/Routes.php` — Registrasi rute `/list_bank*`, `/listbank`, `/api/bank/*` (termasuk `/api/bank/meta`), `/laporan/cash-in`, `/api/laporan/cash-in/*` (termasuk `/meta`), `/laporan/hasil-akad`, `/api/laporan/hasil-akad/*` (termasuk `/meta`), dan `/api/tagihan/booking/verifikasi` ke namespace modular `*\Keuangan\*`.
  - `app/Libraries/ModuleManager/ModuleCatalog.php` — Pembaruan inventori controllers, services, repositories, dan registrasi `shims` untuk modul Keuangan.
  - `docs/modules/Keuangan/MODULE.md` & `writable/modules/module_map.json` — Disinkronkan via `php spark module:sync Keuangan`.

### 3. Kontrak Dual-Mode & Full JSON
- **Endpoint Metadata Baru (`GET`):**
  - `GET /api/bank/meta` -> Mengembalikan `banks`, `active_banks`, dan `permissions`.
  - `GET /api/laporan/cash-in/meta` -> Mengembalikan `active_project`, `available_years`, `default_year_a`, `default_year_b`, `categories`, dan `permissions`.
  - `GET /api/laporan/hasil-akad/meta` -> Mengembalikan `active_project`, `available_years`, `default_year_a`, `default_year_b`, `categories`, dan `permissions`.
- **Dual-Mode (`?format=raw` vs Legacy/DataTables):**
  - `GET /api/bank/list?format=raw` -> JSON standar `{ success, message, data: { items, meta: { total } } }`; tanpa `?format=raw` tetap mengembalikan `{ token, data }` (DataTables HTML) untuk kompatibilitas `app/Views/master/listbank.php`.
  - `POST /api/bank/ambilsatu?format=raw` -> JSON standar `{ success, message, data }`; tanpa `?format=raw` mempertahankan format flat legacy + `data` + `token`.
  - `POST /api/laporan/cash-in/detail?format=raw` & `POST /api/laporan/hasil-akad/detail?format=raw` -> JSON standar `{ success, message, data: { items, meta: { total, recordsTotal, recordsFiltered } } }`; tanpa `?format=raw` mengembalikan struktur DataTables server-side eksisting + `token`.
- **Full JSON Endpoints:**
  - `GET /api/bank/ambil`, `POST /api/bank/simpan`, `POST /api/bank/hapus`, `POST /api/laporan/cash-in/summary`, `POST /api/laporan/hasil-akad/summary`, dan `POST /api/tagihan/booking/verifikasi` menggunakan envelope JSON standar `BaseApiController` (`success()` / `error()`).

### 4. Hasil Verifikasi QA (`PASS`)
- **`php spark module:map`:** `0 unmapped files` (seluruh file baru & shims terpetakan bersih).
- **`php spark module:status Keuangan`:** Readiness Score meningkat **`60% -> 80%`** (Service Layer ✅, Repository Layer ✅, MODULE.md ✅).
- **`php spark module:deadcode Keuangan`:** `4` kandidat dead code pre-existing (sama dengan baseline Fase 0, tidak ada regresi dead code baru).
- **PHPUnit (`vendor/bin/phpunit --no-coverage`):** **`46/46` tests, `169 assertions` — PASS** (`0` error, `0` failure, `0` skipped).
- **Playwright E2E Verification:** Seluruh alur terverifikasi dengan **`0 console error`**.
- **Runtime Log (`writable/logs/`):** **`0 ERROR`** selama eksekusi pengujian Fase 1.
- **Temuan Bug & Remediasi:** Tidak ditemukan bug pada fase ini (`0` temuan QA).

### 5. Prosedur Rollback
Jika diperlukan rollback terhadap perubahan Fase 1 setelah di-commit:
```bash
git revert <commit-hash-fase-1>
php spark module:sync Keuangan
```
Atau sebelum commit (pada working tree):
1. Kembalikan perubahan pada file tracked: `git checkout -- app/Config/Routes.php app/Libraries/ModuleManager/ModuleCatalog.php app/Controllers/ListBank.php app/Controllers/Api/BankController.php app/Controllers/Api/BookingPaymentController.php app/Controllers/Web/CashInReportController.php app/Controllers/Api/CashInReportController.php app/Controllers/Web/HasilAkadReportController.php app/Controllers/Api/HasilAkadReportController.php app/Services/* app/Repositories/* docs/modules/Keuangan/MODULE.md writable/modules/module_map.json` *(khusus file milik Keuangan)*.
2. Hapus file baru untracked di `app/Controllers/Web/Keuangan/`, `app/Controllers/Api/Keuangan/`, `app/Services/Keuangan/` *(kecuali `Cashout/` yang sudah ada)*, dan `app/Repositories/Keuangan/` *(kecuali `Cashout/` yang sudah ada)*.
3. Jalankan `php spark module:sync Keuangan` untuk memulihkan status baseline.

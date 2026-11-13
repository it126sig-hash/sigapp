# Migration Log: Modul Keuangan

Log bertahap migrasi modular dan debugging untuk modul **Keuangan** (Mode `REFACTOR` — 6 Fase, Zero-Downtime Architecture).

---

## Ringkasan Progres Fase

| Fase | Nama Fase | Status | Tanggal | Commit Hash |
|---|---|---|---|---|
| **Fase 0** | Baseline & Safety Verification | `COMPLETED` | 2026-11-13 | `8204898` *(baseline)* |
| **Fase 1** | Migrasi Layer Service & Repository ke `*\Keuangan\*` + Subdomain Laporan, Bank & BookingPayment | `COMPLETED` | 2026-11-13 | `d5ce0c1` |
| **Fase 2** | Dekomposisi & Ekstraksi Legacy Layer — Gelombang 1 (Subdomain Tagihan, Pembayaran, Print & Bank KPR Disbursement) | `COMPLETED` | 2026-11-13 | `280ba27` |
| **Fase 3** | Dekomposisi & Ekstraksi Legacy Layer — Gelombang 2 (Subdomain Pencairan Akad, Dana Akad & Konsolidasi `PencairanJaminan` -> `DanaJaminanController`) | `COMPLETED` | 2026-11-13 | `2e2ade2` |
| **Fase 4** | Dekomposisi & Ekstraksi Legacy Layer — Gelombang 3 (Subdomain CashOut Umum, Cashout Kavling & Cashout Subkon + Fase 4B Remediation) | `COMPLETED` | 2026-11-13 | *(working tree — pra-commit)* |
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
- **Commit Hash:** `d5ce0c1`

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

---

## Fase 2 — Subdomain Tagihan, Pembayaran, Print & Bank KPR Disbursement

- **Tanggal:** `2026-11-13`
- **Status QA:** `PASS` *(QA Awal `FAIL` -> Remediasi Fase 2B -> Re-QA Independen `PASS` oleh `sigapp-qa` pada 2026-11-13 20:19 WIB)*
- **Commit Hash:** `280ba27`

### 1. Scope & Daftar File Fase 2
1. Memisahkan controller legacy **Tagihan**, **Pembayaran**, **Print**, dan **Bank KPR Disbursement** (dari `Tagihan.php`, `Pembayaran.php`, `PrintController.php`, dan sebagian `Keuangan.php`) ke **5 Controller modular baru** di `App\Controllers\Web\Keuangan\*` dan `App\Controllers\Api\Keuangan\*`.
2. Mempertahankan Zero-Downtime compatibility melalui **3 Controller Shims baru** bertanda `@deprecated` pada `app/Controllers/{Tagihan,Pembayaran,PrintController}.php` (total 35 shims di modul Keuangan).
3. Memperkuat **7 Service & Repository** (`KeuanganService`, `PembayaranService`, `PrintService`, `BankKprDisbursementService`, `KeuanganRepository`, `LogPembayaranRepository`, `SpptbRepository`) serta memperbaiki kompatibilitas DataTables & CSRF pada view `app/Views/keuangan/riwayat-bayar.php`.
4. Memperbarui rute eksplisit di `app/Config/Routes.php`, inventori katalog di `app/Libraries/ModuleManager/ModuleCatalog.php`, dan menyinkronkan `docs/modules/Keuangan/MODULE.md` serta `writable/modules/module_map.json`.

#### Daftar File Diubah & Dibuat
- **Controllers Modular Baru (5 file):**
  - `app/Controllers/Web/Keuangan/TagihanController.php` (`listTagihan()`, `riwayatBayarIndex()`)
  - `app/Controllers/Api/Keuangan/TagihanController.php` (`meta()`, `getByID()`, `getRiwayatByID()`, `hapusTurunKPR()`, `tambahTurunKPR()`, `hapus()`, `voidTagihan()`, `unvoidTagihan()`, `save()`, `getListTagihan()`, `getListTagihanGrouped()`, `exportExcelTagihan()`, `getListTagihanDetail()`, `getRiwayatBayar()`, `getListRiwayatSurat()`, `getAllJatuhTempo()`)
  - `app/Controllers/Api/Keuangan/PembayaranController.php` (`meta()`, `save()`, `removeLP()`, `recalculateSummary()`)
  - `app/Controllers/Web/Keuangan/PrintController.php` (`printSpptb()`, `printKuitansi()`, `printPembayaran()`, `printPembayaranUm()`, `printTagihan()`, serta `handleKuitansiPrint()`)
  - `app/Controllers/Api/Keuangan/BankKprDisbursementController.php` (`meta()`, `getJatuhTempo()`, `getData()`, `save()`, `void()`)
- **Zero-Downtime Controller Shims Baru (3 file):**
  - `app/Controllers/Tagihan.php` -> delegasi ke `Api\Keuangan\TagihanController` & `Web\Keuangan\TagihanController`
  - `app/Controllers/Pembayaran.php` -> delegasi ke `Api\Keuangan\PembayaranController` & `Web\Keuangan\PrintController`
  - `app/Controllers/PrintController.php` -> delegasi ke `Web\Keuangan\PrintController`
- **Services & Repositories Diperkuat (7 file):**
  - `app/Services/Keuangan/KeuanganService.php` (`getListItemKeuangan()`, `getTagihanBundle()`, `formatBiayaMkdt()`, `saveTagihan()`, `getJatuhTempoByProyek()`, `getListTagihanRaw()`, `getListTagihanGroupedRaw()`, `getRiwayatBayarRaw()`, `getListRiwayatSuratRaw()`, `ensureDataTableRequestDefaults()`)
  - `app/Services/Keuangan/PembayaranService.php` (`recalculateAllSummaries()`, penguatan validasi alokasi di `simpan()`)
  - `app/Services/Keuangan/PrintService.php` (`printTagihan()`, pembersihan trailing whitespace)
  - `app/Services/Keuangan/BankKprDisbursementService.php` (`getBanksList()`, `canUserManage()`)
  - `app/Repositories/Keuangan/KeuanganRepository.php` (`getJatuhTempoByProyek()`, `getRiwayatSuratQuery()`)
  - `app/Repositories/Keuangan/LogPembayaranRepository.php` (`getDistinctActiveMkdtIds()`)
  - `app/Repositories/Keuangan/SpptbRepository.php` (`insertFile()`)
- **View, Konfigurasi & Dokumentasi:**
  - `app/Views/keuangan/riwayat-bayar.php` (perbaikan definisi `columns` DataTables, escaping HTML, dan rotasi `csrfHash`)
  - `app/Config/Routes.php` (rute eksplisit subdomain Tagihan, Pembayaran, Print, Bank KPR Disbursement + 3 endpoint `/meta`)
  - `app/Libraries/ModuleManager/ModuleCatalog.php`, `docs/modules/Keuangan/MODULE.md`, `writable/modules/module_map.json`

### 2. Kontrak Public HTTP `/meta` & Dual-Mode `?format=raw`
- **Endpoint Metadata Baru (`GET`):**
  - `GET /api/keuangan/tagihan/meta` -> Mengembalikan `active_project`, `list_item_keuangan`, dan `permissions` (`can_view`, `can_create_tagihan`, `can_pay`, `can_void`, `can_delete`).
  - `GET /api/keuangan/pembayaran/meta` -> Mengembalikan `active_project`, `list_item_keuangan`, dan `permissions` (`can_view`, `can_pay`, `can_delete`, `can_recalculate`).
  - `GET /api/keuangan/pencairan-bank/meta` -> Mengembalikan `active_project`, `banks`, `statuses` (`draft`, `cair`, `void`), dan `permissions.can_manage`.
- **Dual-Mode (`?format=raw` vs Legacy/DataTables):**
  - Seluruh endpoint mutasi & query pada `Api\Keuangan\TagihanController` (`/tagihan/*`, `/danaakad/list/ambilsatu`), `Api\Keuangan\PembayaranController` (`/pembayaran/*`), dan `Api\Keuangan\BankKprDisbursementController` (`/keuangan/jatuhtempo/get`, `/keuangan/pencairan-bank/*`) mengembalikan envelope standar `BaseApiController` (`{ success, message, data }`) ketika dipanggil dengan `?format=raw`, dan tetap mempertahankan payload DataTables/legacy + `token` CSRF ketika dipanggil tanpa `?format=raw`.

### 3. Catatan QA Awal (`FAIL` — Fase 2) & RCA + Remediasi (Fase 2B)
- **Temuan QA Awal (`FAIL`):**
  1. **Blocking Bug di `/riwayat-bayar` (DataTables warning + CSRF stale):**
     - **Gejala:** Halaman `/riwayat-bayar` memunculkan dialog `DataTables warning: table id=data_table - Requested unknown parameter '0' for row 0, column 0` saat pemuatan tabel.
     - **Root Cause Analysis (RCA):** `KeuanganService::getRiwayatBayar()` menggunakan `DataTable::of($builder)->toJson(true)` yang mengembalikan baris berupa JSON *object* (`{ nominal, tanggal_bayar, keterangan, ... }`), sedangkan `app/Views/keuangan/riwayat-bayar.php` belum mendefinisikan konfigurasi `columns` (sehingga DataTables mencoba membaca indeks array numerik `0..5`). Selain itu, `ajax.dataSrc` dan Select2 `processResults` belum konsisten menyinkronkan `csrfHash = r.token` dari respons server (mencegah token stale pada request berikutnya).
     - **Remediasi (`sigapp-frontend` pada `app/Views/keuangan/riwayat-bayar.php`):** Menambahkan konfigurasi `columns` eksplisit untuk seluruh 6 kolom (`No`, `nominal`, `tanggal_bayar`, `keterangan`, `username`, `created_at`), sanitasi output via `escapeHtml()`, serta pembaruan `if (r && r.token) csrfHash = r.token;` pada `dataSrc` DataTables dan `processResults` Select2 (`#id_cluster`, `#id_jalan`).
  2. **Hardening Non-Blocking (`sigapp-engineer`):**
     - `app/Controllers/Web/Keuangan/PrintController.php`: Menambahkan `handleKuitansiPrint()` serta try/catch `\DomainException` pada `printTagihan()` untuk memetakan error domain menjadi HTTP `403`/`404` JSON yang terstruktur (menggantikan uncaught exception `500`).
     - `app/Services/Keuangan/PrintService.php`: Pembersihan trailing whitespace pada akhir file class.
     - `app/Services/Keuangan/KeuanganService.php`: Menambahkan guard `ensureDataTableRequestDefaults($request)` agar key `columns` dan `search` tetap berupa array aman ketika endpoint DataTables dipanggil tanpa payload DataTables standar.

### 4. Hasil Verifikasi Re-QA Independen (`PASS` — Fase 2B)
Diverifikasi secara independen oleh `sigapp-qa` pada **`2026-11-13 20:19 WIB`**:
- **Playwright E2E (`/riwayat-bayar` & `/tagihan/list`):** **`0` DataTables warning dialog** dan **`0` console error**; tabel, filter cluster/blok, dan interaksi halaman berjalan normal.
- **PHPUnit:** **`45 tests / 196 assertions` PASS** + **`39 tests / 732 assertions` kombinasi live DB PASS** *(catatan: `PencairanAkadBatchServiceTest` memiliki 2 pre-existing CLI guard failure di luar cakupan Fase 2)*.
- **`php spark module:map`:** **`0` unmapped files**.
- **`php spark module:sync Keuangan --check-only`:** Exit code `0` (`MODULE.md` tersinkronisasi).
- **Runtime Log (`writable/logs/log-2026-11-13.log`):** **`0` ERROR/CRITICAL baru** selama eksekusi verifikasi.

### 5. Prosedur Rollback Fase 2
Jika diperlukan rollback terhadap perubahan Fase 2 setelah di-commit:
```bash
git revert <commit-hash-fase-2>
php spark module:sync Keuangan
```
Atau sebelum commit (pada working tree):
1. Kembalikan file tracked milik Fase 2:
   ```bash
   git checkout -- app/Config/Routes.php app/Libraries/ModuleManager/ModuleCatalog.php app/Controllers/Tagihan.php app/Controllers/Pembayaran.php app/Controllers/PrintController.php app/Services/Keuangan/KeuanganService.php app/Services/Keuangan/PembayaranService.php app/Services/Keuangan/PrintService.php app/Services/Keuangan/BankKprDisbursementService.php app/Repositories/Keuangan/KeuanganRepository.php app/Repositories/Keuangan/LogPembayaranRepository.php app/Repositories/Keuangan/SpptbRepository.php app/Views/keuangan/riwayat-bayar.php docs/modules/Keuangan/MODULE.md docs/modules/Keuangan/MIGRATION_LOG.md writable/modules/module_map.json
   ```
2. Hapus 5 file controller baru di working tree:
   - `app/Controllers/Web/Keuangan/TagihanController.php`
   - `app/Controllers/Api/Keuangan/TagihanController.php`
   - `app/Controllers/Api/Keuangan/PembayaranController.php`
   - `app/Controllers/Web/Keuangan/PrintController.php`
   - `app/Controllers/Api/Keuangan/BankKprDisbursementController.php`
3. Jalankan `php spark module:sync Keuangan` untuk memulihkan status Fase 1.

---

## Fase 3 — Subdomain Pencairan Akad, Dana Akad & Konsolidasi `PencairanJaminan` -> `DanaJaminanController`

- **Tanggal:** `2026-11-13`
- **Status QA:** `PASS` *(Diverifikasi independen oleh `sigapp-qa`)*
- **Commit Hash:** `2e2ade2`

### 1. Ringkasan Implementasi (`sigapp-engineer`)
- **3 Controller Modular Baru (0 direct DB/Model access; `meta()` + `?format=raw`):**
  - `app/Controllers/Web/Keuangan/PencairanAkadController.php` (`listHasilAkad()`, `importForm()`, `exportTemplate()`)
  - `app/Controllers/Api/Keuangan/PencairanAkadController.php` (`meta()`, `get()`, `saveRetensi()`, `saveTenor()`, `storePengajuan()`, `cairkan()`, `listPayment()`, `voidPayment()`, `void()`, `history()`, `getListGrouped()`, `getListDetail()`, `import()`, `postBatchList()`, `postEligibleKavling()`, `postStoreBatchPengajuan()`, `postBatchDetail()`, `postCairkanBatch()`, `postVoidBatch()`, `getBatchHistory()`)
  - `app/Controllers/Api/Keuangan/DanaJaminanController.php` (`meta()`, `index()`, `list()`, `store()`, `cairkan()`, `history()`, `toggleStatus()`, `download()`, `getByID()`, `getDanaAkad()`, `saveDanaAkad()`)
- **Penguatan Service Layer:**
  - `app/Services/Keuangan/PencairanAkadService.php`: Penguatan `void()` dengan DB transaction (`transStart`/`transComplete`), ekspos `getListDajam()` (public), serta penambahan `getMetaPayload()`, `getListGroupedRaw()`, `buildBatchListQuery()`, dan `getBatchListRaw()`.
  - `app/Services/Keuangan/DanaJaminanService.php`: Penambahan `getListDajam()`, `getMetaPayload()`, `getDownloadFile()`, dan `getDanaAkadDetail()`.
- **3 Zero-Downtime Shims `@deprecated`:**
  - `app/Controllers/PencairanAkad.php` -> delegasi ke `Api\Keuangan\PencairanAkadController` & `Web\Keuangan\PencairanAkadController`
  - `app/Controllers/PencairanJaminan.php` -> delegasi ke `Api\Keuangan\DanaJaminanController` + warning log `DEPRECATED_KEUANGAN_HIT` pada `initController()`
  - `app/Controllers/DanaAkad.php` -> delegasi ke `Api\Keuangan\DanaJaminanController` + warning log `DEPRECATED_KEUANGAN_HIT` pada `initController()`
- **Rute, Katalog & Dokumentasi:**
  - `app/Config/Routes.php` (`151` rute eksplisit Keuangan, termasuk `GET /api/keuangan/pencairan-akad/meta`, `GET /api/keuangan/dana-jaminan/meta`, grup `/keuangan/pencairan-akad/*` & `/keuangan/hasil-akad/*`, `/danaakad/getbyid`, `/keuangan/{getDanaAkad,saveDanaAkad}`, serta konsolidasi grup `/pencairan/*` ke `Api\Keuangan\DanaJaminanController`).
  - `app/Libraries/ModuleManager/ModuleCatalog.php`, `docs/modules/Keuangan/MODULE.md`, `writable/modules/module_map.json`.

### 2. Hasil QA Independen Fase 3 (`sigapp-qa` — `PASS`)
- **Static & Modularity Checks:** `0 broken callers`, `0` violations baru pada controller modular, `php spark module:map` `100%` (`0 unmapped files`), `php spark module:status Keuangan` = `MAPPED 80%`, `php spark module:deadcode Keuangan` `0` regresi baru.
- **PHPUnit:**
  - Suite `PencairanAkad`: `17 tests / 33 assertions` — **PASS**
  - `FeeSalesWorkflowTest`: `31 tests / 275 assertions` — **PASS**
  - `BookingPaymentAggregationTest`: `2 tests / 8 assertions` — **PASS**
- **Playwright E2E & Endpoint Verification:**
  - `/keuangan/hasil-akad/list`, `/keuangan/hasil-akad/import`, `/keuangan/hasil-akad/export-template`, `/api/keuangan/pencairan-akad/meta`, `/api/keuangan/dana-jaminan/meta`, dual-mode `?format=raw`, dan negative test `404` terverifikasi normal (`0` console error & `0 ERROR` log dari alur UI).
- **Catatan Minor Pra-Eksisting (Non-Blocking):**
  - Polusi shared `Services::renderer()` saat menjalankan seluruh suite PHPUnit sekaligus, serta perilaku `respondDual()` ketika payload array kosong `[]` (dikonversi menjadi `null`).

### 3. Prosedur Rollback Fase 3
Jika diperlukan rollback terhadap perubahan Fase 3 sebelum commit (mengacu pada baseline commit Fase 2 **`280ba27`**):
1. Kembalikan seluruh file tracked milik Fase 3 ke `HEAD` (`280ba27`):
   ```bash
   git checkout HEAD -- app/Config/Routes.php app/Libraries/ModuleManager/ModuleCatalog.php app/Controllers/PencairanAkad.php app/Controllers/PencairanJaminan.php app/Controllers/DanaAkad.php app/Services/Keuangan/PencairanAkadService.php app/Services/Keuangan/DanaJaminanService.php docs/modules/Keuangan/MODULE.md docs/modules/Keuangan/MIGRATION_LOG.md writable/modules/module_map.json
   ```
2. Hapus 3 file controller modular baru untracked:
   - `app/Controllers/Web/Keuangan/PencairanAkadController.php`
   - `app/Controllers/Api/Keuangan/PencairanAkadController.php`
   - `app/Controllers/Api/Keuangan/DanaJaminanController.php`
3. Jalankan `php spark module:sync Keuangan` untuk memulihkan status Fase 2.

---

## Fase 4 — Subdomain CashOut Umum, Cashout Kavling & Cashout Subkon (+ Fase 4B Remediation)

- **Tanggal:** `2026-11-13`
- **Status QA:** `PASS` *(QA Awal `FAIL` (`ae592b78`) -> Remediasi Fase 4B (`sigapp-frontend`) -> Re-QA Independen `PASS` oleh `sigapp-qa` (`800fa998`))*
- **Commit Hash:** *(working tree — pra-commit)*

### 1. Ringkasan Implementasi (`sigapp-engineer`)
- **5 Controller Modular Baru (`0` direct DB/Model access; `meta()` + Dual-Mode `?format=raw`):**
  - `app/Controllers/Api/Keuangan/CashOutController.php` (`meta()`, `getListItem()`, `getByIDKavling()`, `insert()`, `delete()`)
  - `app/Controllers/Web/Keuangan/Cashout/CashoutKavlingController.php` (`index()`)
  - `app/Controllers/Api/Keuangan/Cashout/CashoutKavlingController.php` (`meta()`, `getDataTables()`, `getDetailList()`)
  - `app/Controllers/Web/Keuangan/Cashout/CashoutSubkonController.php` (`index()`)
  - `app/Controllers/Api/Keuangan/Cashout/CashoutSubkonController.php` (`meta()`, `getDataTables()`, `getDetailList()`, `get()`, `save()`, `turunJatuhTempo()`, `ajukanSPP()`, `ajukanPencairan()`, `pembayaran()`, `getHistory()`)
- **Ekstraksi Query/Validasi ke Service & Repository:**
  - `app/Repositories/Keuangan/CashOutRepository.php`: Penambahan `getListItemSelect2()` dan `getKavlingDetail()`.
  - `app/Services/Keuangan/CashOutService.php`: Penambahan `getListItemSelect2()`, `getByIDKavling()` (bundel data konsumen, biaya MKDT, riwayat bayar), `getMetaPayload()`, `konsumenExistsForKavling()`, `cleanNumeric()`, `formatBiayaMkdt()`, `saveCashOut()`, dan `deleteCashOut()`.
  - `app/Services/Keuangan/Cashout/CashoutKavlingService.php`: Penambahan `getIndexData()`, `getMetaPayload()`, dan `getDataTablesRaw()`.
  - `app/Services/Keuangan/Cashout/CashoutSubkonService.php`: Penambahan `getIndexData()`, `getMetaPayload()`, dan `getDataTablesRaw()` (delegasi langsung ke `CashoutSubkonRepo::getDataTables()`).
- **3 Zero-Downtime Shims `@deprecated`:**
  - `app/Controllers/CashOut.php` -> delegasi ke `Api\Keuangan\CashOutController` (`DEPRECATED_KEUANGAN_HIT` warning log pada `initController()`).
  - `app/Controllers/Keuangan/Cashout/CashoutKavling.php` -> delegasi ke `Web\Keuangan\Cashout\CashoutKavlingController` & `Api\Keuangan\Cashout\CashoutKavlingController` (`DEPRECATED_KEUANGAN_HIT` warning log pada `initController()`).
  - `app/Controllers/Keuangan/Cashout/CashoutSubkon.php` -> delegasi ke `Web\Keuangan\Cashout\CashoutSubkonController` & `Api\Keuangan\Cashout\CashoutSubkonController` (`DEPRECATED_KEUANGAN_HIT` warning log pada `initController()`).
- **Rute, Katalog & Dokumentasi:**
  - `app/Config/Routes.php` (`154` rute eksplisit Keuangan, termasuk `GET /api/keuangan/cashout/meta`, `GET /api/keuangan/cashout-kavling/meta`, `GET /api/keuangan/cashout-subkon/meta`, serta rute eksplisit `/keuangan/cashout/*`, `/cashout/kavling*`, dan `/cashout/subkon*` ke controller modular).
  - `app/Libraries/ModuleManager/ModuleCatalog.php` (`Ctrl: 23`, total `41` shims), `docs/modules/Keuangan/MODULE.md`, `writable/modules/module_map.json`.

### 2. QA Awal & Fase 4B Remediation (`FAIL` -> `PASS`)
- **Audit Awal `sigapp-qa` (`ae592b78` — `FAIL`):**
  - Menandai `FAIL` akibat `TypeError: $(...).richText is not a function` pada `public/assets/js/siteplan/keuangan.js:72` saat memuat `GET /cashout/kavling` (memutus eksekusi skrip sebelum binding modal sehingga klik `.ck-open-cashout` tidak membuka modal).
  - *(Sempat pula tercatat catatan transien pada `POST /cashout/subkon/list?format=raw`, namun terkonfirmasi `CashoutSubkonService::getDataTablesRaw()` sudah mendelegasikan langsung ke `CashoutSubkonRepo::getDataTables()`).*
- **Fase 4B Remediation (`sigapp-frontend`):**
  - Membungkus inisialisasi `.richText()` untuk `#idk-rincian`, `#snk`, dan `#status_keterangan` dengan guard `if ($.fn && typeof $.fn.richText === "function")` di `public/assets/js/siteplan/keuangan.js:72-116` (`JS_SYNTAX_OK`, `git diff --check` exit `0`).
- **Re-QA Independen (`sigapp-qa` `800fa998` — `PASS`):**
  - **Playwright E2E:** `/cashout/kavling` & klik `.ck-open-cashout` membuka `#modal-cashout-keu` dengan **`0 console error`**; `/cashout/subkon` termuat normal dengan **`0 console error`**.
  - **HTTP Endpoints & Dual-Mode:** Seluruh 3 endpoint `/meta` (`/api/keuangan/cashout/meta`, `/api/keuangan/cashout-kavling/meta`, `/api/keuangan/cashout-subkon/meta`) & dual-mode `?format=raw` mengembalikan `200 OK`; negative tests menghasilkan `404`/`422` sesuai kontrak.
  - **PHPUnit:** **`33 tests / 114 assertions` — PASS**.
  - **Runtime Log:** **`0 ERROR / 0 CRITICAL`**.

### 3. Prosedur Rollback Fase 4
Jika diperlukan rollback terhadap perubahan Fase 4 sebelum commit (mengacu pada baseline commit Fase 3 **`2e2ade2`**):
1. Kembalikan seluruh file tracked milik Fase 4 ke `HEAD` (`2e2ade2`):
   ```bash
   git checkout HEAD -- app/Config/Routes.php app/Libraries/ModuleManager/ModuleCatalog.php app/Controllers/CashOut.php app/Controllers/Keuangan/Cashout/CashoutKavling.php app/Controllers/Keuangan/Cashout/CashoutSubkon.php app/Repositories/Keuangan/CashOutRepository.php app/Services/Keuangan/CashOutService.php app/Services/Keuangan/Cashout/CashoutKavlingService.php app/Services/Keuangan/Cashout/CashoutSubkonService.php public/assets/js/siteplan/keuangan.js docs/modules/Keuangan/MODULE.md docs/modules/Keuangan/MIGRATION_LOG.md writable/modules/module_map.json
   ```
2. Hapus 5 file controller modular baru untracked:
   - `app/Controllers/Api/Keuangan/CashOutController.php`
   - `app/Controllers/Web/Keuangan/Cashout/CashoutKavlingController.php`
   - `app/Controllers/Api/Keuangan/Cashout/CashoutKavlingController.php`
   - `app/Controllers/Web/Keuangan/Cashout/CashoutSubkonController.php`
   - `app/Controllers/Api/Keuangan/Cashout/CashoutSubkonController.php`
3. Jalankan `php spark module:sync Keuangan` untuk memulihkan status Fase 3.
4. Jika rollback dilakukan setelah di-commit, gunakan `git revert <commit-hash-fase-4>` diikuti `php spark module:sync Keuangan`.

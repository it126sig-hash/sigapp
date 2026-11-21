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
| **Fase 4** | Dekomposisi & Ekstraksi Legacy Layer — Gelombang 3 (Subdomain CashOut Umum, Cashout Kavling & Cashout Subkon + Fase 4B Remediation) | `COMPLETED` | 2026-11-13 | `64209ba` |
| **Fase 5** | Dekomposisi Subdomain Pajak, Direksi, Penagihan/KopSurat/`doPrint` & Opsi A Shim `Keuangan.php` (+ Fase 5B Putaran 1 & 2) | `COMPLETED` | 2026-11-13 | `f21fc2b` |
| **Fase 6** | Final Cross-Module QA Audit & Global Migration Ledger Sync (`docs/MIGRATION_PROGRESS.md`) | `COMPLETED` | 2026-11-14 | `PENDING` *(akan di-commit pada penutup Fase 6)* |

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
- **Commit Hash:** `64209ba`

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

---

## Fase 5 — Pajak, Direksi, Penagihan/KopSurat/`doPrint`, & Opsi A Shim `Keuangan.php` (+ Fase 5B Putaran 1 & 2)

- **Tanggal:** `2026-11-13`
- **Status QA:** `PASS` *(QA Awal `FAIL` -> Remediasi Fase 5B Putaran 1 & 2 -> Re-QA Independen `PASS` oleh `sigapp-qa`)*
- **Commit Hash:** `f21fc2b`

### 1. Scope & Daftar File Baru & Diubah (17 File Kode + Docs/Map)
1. Mengekstrak subdomain **Pajak**, **Direksi**, serta **Penagihan / KopSurat / `doPrint`** ke **5 Controller modular baru**, **2 Service baru** (+1 diperkuat), dan **2 Repository baru** (+1 diperkuat) di namespace `*\Keuangan\*`.
2. Mengubah `app/Controllers/Keuangan.php`, `app/Controllers/Pajak.php`, dan `app/Controllers/Direksi.php` menjadi **Opsi A Zero-Downtime Shims (`@deprecated`)** dengan logging `DEPRECATED_KEUANGAN_HIT`.
3. Mendaftarkan rute eksplisit `/api/keuangan/{pajak,direksi,penagihan}/meta`, `/pajak/*`, `/direksi/*`, dan `/keuangan/*` di `app/Config/Routes.php` beserta kontrak Dual-Mode `?format=raw`, preservasi singular `'message'` vs `'messages'`, dan `HTTP 404` pada `download_penagihan`.
4. Memperbarui `app/Libraries/ModuleManager/ModuleCatalog.php` sehingga status modul Keuangan mencapai **`REFACTORED | 100%`** (`Ctrl 25 | Serv 18 | Repo 16 | Model 16 | Route 141 | Tables 25`, total `44` shims).

#### Daftar File Kode (17 File)
- **Controllers Modular Baru (5 file):**
  - `app/Controllers/Api/Keuangan/PajakController.php` (`meta()`, `getOne()`, `save()`)
  - `app/Controllers/Web/Keuangan/DireksiController.php` (`tagihan()`, `export_xlsx()`)
  - `app/Controllers/Api/Keuangan/DireksiController.php` (`meta()`, `get_data_by_id()`, `save()`, `get_pending_tagihan()`, `get_my_signature()`, `sign_tagihan()`, `get_history_tagihan()`, `reject_tagihan()`)
  - `app/Controllers/Web/Keuangan/PenagihanController.php` (`download_penagihan()`, `doPrint()`)
  - `app/Controllers/Api/Keuangan/PenagihanController.php` (`meta()`, `isi_tagihan()`, `save_sb()`, `save()`, `get_tagihan()`, `get_riwayat_tagihan()`, `simpan_penagihan()`, `update_status_penagihan()`, `get_kopsurat_list()`, `get_kopsurat_detail()`, `simpan_kopsurat()`, `hapus_kopsurat()`)
- **Services Baru & Diperkuat (3 file):**
  - `app/Services/Keuangan/DireksiService.php` *(baru — 9 public methods)*
  - `app/Services/Keuangan/PenagihanService.php` *(baru — 14 public methods, termasuk `streamDownloadPenagihan()` & `streamDoPrintPdf()`)*
  - `app/Services/Keuangan/PajakService.php` *(diperkuat — `getMetaPayload()`, `getOneBundle()`, `savePajakData()`, `insertPajak()`, `updatePajak()`)*
- **Repositories Baru & Diperkuat (3 file):**
  - `app/Repositories/Keuangan/DireksiRepository.php` *(baru — 8 public methods)*
  - `app/Repositories/Keuangan/PenagihanRepository.php` *(baru — 32 public methods)*
  - `app/Repositories/Keuangan/PajakRepository.php` *(diperkuat — `getPajakBundleByKavling()`, `getLatestFilesByCategories()`, `insertFileUpload()`, `getTarifPph()`, `getTarifPpn()`)*
- **Opsi A Zero-Downtime Shims (3 file diubah):**
  - `app/Controllers/Keuangan.php` (`@deprecated` + `DEPRECATED_KEUANGAN_HIT` + `initDelegate(\CodeIgniter\Controller $controller): \CodeIgniter\Controller` + 4 orphan legacy methods)
  - `app/Controllers/Pajak.php` (`@deprecated` + `DEPRECATED_KEUANGAN_HIT`)
  - `app/Controllers/Direksi.php` (`@deprecated` + `DEPRECATED_KEUANGAN_HIT` + delegasi Web `tagihan()` & `export_xlsx()`)
- **View, Konfigurasi & Katalog (3 file diubah) + Docs/Map:**
  - `app/Views/pdf/tagihan_keuangan.php` (perbaikan helper `format_date()` menggantikan typo `foramt_date()` + render tanggal aman untuk Dompdf)
  - `app/Config/Routes.php` (rute eksplisit Pajak, Direksi, Penagihan/KopSurat/`doPrint`, 3 endpoint `/meta`, termasuk `GET /keuangan/get_riwayat_tagihan`)
  - `app/Libraries/ModuleManager/ModuleCatalog.php` (inventori Fase 5 + 3 shims baru)
  - Dokumentasi & map: `docs/modules/Keuangan/MODULE.md`, `docs/modules/Keuangan/MIGRATION_LOG.md`, `writable/modules/module_map.json`, `writable/modules/deadcode_Keuangan.json`.

### 2. Hasil QA Awal (`FAIL`) & Root Cause Analysis (RCA)
1. **Error pada `doPrint` akibat typo `foramt_date()` vs `format_date()`:**
   - **Gejala:** Render view `app/Views/pdf/tagihan_keuangan.php` gagal saat memanggil helper tanggal.
   - **RCA:** Terdapat typo nama fungsi `foramt_date()` pada template PDF `tagihan_keuangan.php` serta ketidaksesuaian pemanggilan `format_date()`.
2. **Dompdf `PREG_JIT_STACKLIMIT_ERROR` / kegagalan render `doPrint`:**
   - **Gejala:** Streaming PDF `POST /keuangan/doPrint` gagal di Dompdf ketika tanggal/kop surat tidak dinormalisasi atau MIME gambar kop tidak terdeteksi tepat.
   - **RCA:** Data URI gambar kop di-hardcode/tidak mendeteksi MIME secara dinamis dan input `tanggal_surat_tagihan` belum dinormalisasi ke `Y-m-d` / `'-'` sebelum dirender oleh Dompdf.
3. **`download_penagihan` mengembalikan HTTP `200` saat `id` kosong:**
   - **Gejala:** Request `GET /keuangan/download_penagihan` tanpa `id` atau dengan `id` kosong tidak mengembalikan status `404`.
   - **RCA:** Guard pengecekan `$id === ''` pada `Web\Keuangan\PenagihanController::download_penagihan()` belum mengembalikan status `404` secara eksplisit sebelum memanggil service.
4. **`Keuangan::initDelegate()` melempar `TypeError`:**
   - **Gejala:** Pemanggilan endpoint melalui shim `App\Controllers\Keuangan` ke controller `Api\Keuangan\*` gagal dengan `TypeError`.
   - **RCA:** Signature `initDelegate()` sebelumnya menuntut `App\Controllers\BaseController`, sedangkan controller `Api\Keuangan\*` mewarisi `BaseApiController` (`CodeIgniter\RESTful\ResourceController` / `CodeIgniter\Controller`), bukan `App\Controllers\BaseController`.
5. **Rute `GET /keuangan/get_riwayat_tagihan` belum terdaftar:**
   - **Gejala:** Akses HTTP `GET` ke `/keuangan/get_riwayat_tagihan` tidak cocok pada rute eksplisit yang awalnya hanya mendaftarkan `POST`.

### 3. Perbaikan Fase 5B (Putaran 1 & Putaran 2)
- **`app/Services/Keuangan/PenagihanService.php` (`streamDoPrintPdf`):** Menambahkan deteksi MIME kop surat dinamis (`mime_content_type()` -> `\finfo(FILEINFO_MIME_TYPE)` -> peta ekstensi gambar) serta normalisasi `tanggal_surat_tagihan` ke format `Y-m-d` atau `'-'`.
- **`app/Controllers/Web/Keuangan/PenagihanController.php` (`download_penagihan`):** Memastikan `trim((string) ($this->request->getVar('id') ?? '')) === ''` dan `! $streamed` mengembalikan `HTTP 404` (`Surat tagihan tidak ditemukan`).
- **`app/Views/pdf/tagihan_keuangan.php` (`format_date`):** Mengganti typo `foramt_date()` menjadi `format_date($d)` dengan guard kosong/non-ISO yang aman untuk Dompdf.
- **`app/Controllers/Keuangan.php` (`initDelegate`):** Mengubah type hint menjadi `initDelegate(\CodeIgniter\Controller $controller): \CodeIgniter\Controller` (diimpor sebagai `BaseCiController`) agar kompatibel dengan seluruh delegasi `Web\Keuangan\*` maupun `Api\Keuangan\*`.
- **`app/Config/Routes.php`:** Menambahkan rute eksplisit `GET /keuangan/get_riwayat_tagihan` (mendampingi `POST /keuangan/get_riwayat_tagihan`).

### 4. Hasil Re-QA Final (`PASS` — Fase 5B Putaran 2)
Diverifikasi secara independen oleh `sigapp-qa`:
- **`doPrint` PDF Rendering:** Menghasilkan output PDF valid sebesar **`274,951` bytes** dengan **`0` console error**.
- **Negative Test `download_penagihan`:** Request dengan `id` kosong maupun ID tidak ditemukan mengembalikan **`HTTP 404`**.
- **Shim & Endpoint Verification:** Seluruh shim Opsi A (`Keuangan`, `Pajak`, `Direksi`), 3 endpoint `/meta` (`/api/keuangan/{pajak,direksi,penagihan}/meta`), dual-mode `?format=raw`, dan preservasi singular `'message'` vs `'messages'` terverifikasi lulus.
- **Modular Sync & Status:**
  - `php spark module:sync Keuangan --check-only`: Exit `0` (`MODULE.md` sinkron).
  - `php spark module:status Keuangan`: **`REFACTORED | 100%`** (`Ctrl 25 | Serv 18 | Repo 16 | Model 16 | Route 141 | Tables 25`).
- **Runtime Log Audit (`writable/logs/`):** **`0 ERROR / 0 CRITICAL`**.

### 5. Prosedur Rollback Fase 5
Catatan: Commit hash Fase 5 adalah **`f21fc2b`** (`refactor(keuangan): phase 5 - modularize pajak, direksi, penagihan, and deprecate legacy keuangan`); baseline sebelum Fase 5 adalah commit Fase 4 **`64209ba`**.
Jika diperlukan rollback terhadap perubahan Fase 5 setelah di-commit:
```bash
git revert f21fc2b
php spark module:sync Keuangan
```

---

## Fase 6 — Final Cross-Module Regression Audit & Global Ledger Sync

- **Tanggal:** `2026-11-14`
- **Status QA:** `PASS` *(Sertifikat `PASS` independen oleh `sigapp-qa` pada commit Fase 5 **`f21fc2b`**)*
- **Commit Hash:** `PENDING` *(commit penutup dokumen Fase 6)*

### 1. Scope Fase 6
1. Verifikasi sinkronisasi akhir modul `Keuangan` via `php spark module:status` dan `php spark module:sync Keuangan --check-only` (serta pemutakhiran timestamp/hash sinkronisasi `MODULE.md` ke `@ f21fc2b`).
2. Validasi regresi lintas-modul menyeluruh pasca-Fase 5 (Playwright E2E multi-role `Keuangan`, `MKDT`, dan `Planning`, seluruh endpoint `/meta`, `?format=raw`, dan legacy Opsi A shims).
3. Eksekusi penuh unit & database test PHPUnit terkait Keuangan serta modul terhubung (`Cashout`, `Direksi`, `PencairanAkad`, `FeeSales`, `Siteplan`, `MKDT-Financial`).
4. Audit log aplikasi harian (`writable/logs/log-2026-11-14.log`) untuk memastikan `0` error runtime dan mengonfirmasi hook observasi `DEPRECATED_KEUANGAN_HIT` berjalan sesuai probe.
5. Pembaruan Global Migration Ledger `docs/MIGRATION_PROGRESS.md` (status Modul **#16 `Keuangan`** -> **`COMPLETED`**, progres global **`16 Selesai | 0 Sedang Dikerjakan | 0 Antre (Pending)`**).

### 2. Hasil Verifikasi QA Akhir (`PASS` pada Commit `f21fc2b`)
- **CLI Modularity & Sync:**
  - `php spark module:status Keuangan`: **`REFACTORED | 100%`** (`25 Ctrl | 18 Serv | 16 Repo | 16 Model | 141 Route | 25 Tables`), `controller_violations: []`.
  - Statistik Global: `17` modul (`15 REFACTORED`, `2 MAPPED`, rata-rata skor kesiapan `93.5%`), `0 unmapped files`.
  - `php spark module:sync Keuangan --check-only`: Exit `0` (`[OK] Keuangan: MODULE.md ada.`, tersinkronisasi pada `@ f21fc2b`).
- **Playwright E2E Multi-Role:**
  - **Keuangan (`nisalis`):** 7 halaman inti Keuangan (`200 OK`, `0` console errors, termasuk `/cashout/kavling` dan pembukaan modal `#modal-cashout-keu`), seluruh **11+2** endpoint `/meta` `200 OK`, `6` endpoint `?format=raw` `200 OK`, dan `5` legacy Opsi A shims terverifikasi memicu `DEPRECATED_KEUANGAN_HIT`.
  - **MKDT (`citra`):** `/list-kavling` `200 OK` (`1.489` baris, `0` console error, `0` HTTP 404/500).
  - **Planning (`purnama`):** `/siteplan/view` `200 OK` (`311` Konva shapes, `#modal_detail` tab Keuangan & Legal/Pajak termuat normal, `0` JS error, `0` HTTP 500; catatan 1 asset foto profil lokal tidak terkait modul Keuangan).
- **PHPUnit (`--no-coverage`):**
  - **27/27** file test relevan `Keuangan` / `Cashout` / `Direksi` / `PencairanAkad` / `FeeSales` / `Siteplan` / `MKDT-Financial` — **`PASS`**.
  - *(Catatan transparan non-blocking: terdapat 2 test pre-existing milik modul MKDT di luar Keuangan, yaitu `BookingAkadReportProyek13MetricsTest` & `BookingAkadReportM2ChallengerTest`, yang berasal dari sebelum refaktor Keuangan).*
- **Runtime Log Audit (`writable/logs/log-2026-11-14.log`, rentang `00:43:24`–`00:55:51`):**
  - **`0` `ERROR` / `0` `CRITICAL`**.
  - **`8` `WARNING DEPRECATED_KEUANGAN_HIT`** tercatat sesuai probe pengujian terhadap legacy Opsi A shims.

### 3. Daftar File Diperbarui pada Fase 6
- `docs/modules/Keuangan/MODULE.md` — Disinkronkan via `php spark module:sync Keuangan` (`@ f21fc2b`) dan penambahan entri `REFACTOR COMPLETE` (Fase 6) pada `## Change Log`.
- `docs/modules/Keuangan/MIGRATION_LOG.md` — Pengisian commit hash Fase 5 (`f21fc2b`) pada tabel ringkasan, header Fase 5, dan prosedur rollback Fase 5, serta penambahan entri lengkap **Fase 6**.
- `docs/MIGRATION_PROGRESS.md` — Pembaruan baris **#16 `Keuangan`** menjadi `COMPLETED` (`REFACTORED 100%`, `f21fc2b`, `2026-11-14`) dan pembaruan rangkuman progres global (`16 Selesai | 0 Sedang Dikerjakan | 0 Antre (Pending)`).

### 4. Prosedur Rollback Fase 6
```bash
git revert <commit-hash-fase-6>
```
*(Fase 6 hanya memutakhirkan file dokumentasi tanpa perubahan kode aplikasi PHP/JS/View maupun skema database.)*

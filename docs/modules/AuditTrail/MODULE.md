# Module: AuditTrail

**Status:** `REFACTORED`  
**Readiness Score:** `100/100`  
**Last Synced:** `2026-11-12 15:35:45` @ `a68829a`  

## Purpose
Pencatatan riwayat perubahan data krusial, log transaksi, dan jejak aktivitas user untuk kepatuhan tata kelola, rekonsiliasi data, serta penelusuran debugging audit menyeluruh di seluruh divisi SIGAPP.

## Out of scope
- Mutasi langsung data operasional bisnis spesifik (misal pengubahan status kavling di MKDT, input progres di Produksi, atau pencairan di Keuangan; hal itu dilakukan oleh Service masing-masing domain).
- Autentikasi dan otorisasi login (dikelola oleh Myth/Auth dan modul `Auth`).

## Invariants
- Modul lain **DILARANG KERAS** memanipulasi atau menulis langsung ke tabel `history_log`. Seluruh pencatatan riwayat mutasi wajib melalui `App\Services\AuditTrail\HistoryService::log()`.
- Log audit bersifat **Append-Only** (hanya boleh `INSERT`, tidak boleh ada operasi `UPDATE` atau `DELETE` pada baris log riwayat untuk menjaga integritas data audit).
- Operasi logging pada `HistoryService::log()` bersifat **Fail-Safe** dan **Non-Blocking** (dibungkus blok `try-catch (\Throwable $e)` yang mencatat error ke `log_message('error')`), sehingga kegagalan audit trail tidak pernah membatalkan atau merusak transaksi bisnis utama.
- Metadata audit secara otomatis diperkaya (*auto-enriched*) dengan konteks debugging request: Client IP (`ip_address`), User Agent browser (`user_agent`), dan Request URI (`uri`).
- Identifikasi proyek (`id_proyek`) wajib terisi otomatis melalui resolusi hierarki (`resolveProyekId`) agar filtering berbasis proyek aktif dan otorisasi role konsisten.

## Database Tables
### Owner Table(s)
- `history_log` *(akses tulis eksklusif hanya untuk modul AuditTrail via `HistoryRepository::insert()`)*

### Shared Tables (Dibaca dari modul lain)
- `users` *(membaca username akun pelaku perubahan/audit)*
- `kavling`, `jalan`, `cluster` *(membaca relasi ID proyek dari unit kavling)*
- `target_siteplan` *(membaca ID proyek dari target siteplan)*
- `cashout_subkon_kavling` *(membaca daftar proyek dan keterkaitan kavling dengan transaksi cashout)*

## Contracts
### A. Public HTTP Endpoints & Routes Inventory
| Method | URI / Pattern | Tipe | Handler | Status Migrasi | Target API / Catatan |
|---|---|---|---|---|---|
| `GET` | `/riwayat-perubahan` | `Explicit` | `\App\Controllers\Web\AuditTrail\RiwayatPerubahanController::index` | 🟢 MIGRATED | Endpoint halaman web antarmuka riwayat perubahan |
| `GET` | `/api/riwayat-perubahan/meta` | `Explicit` | `HistoryController::meta` | 🟢 MIGRATED | Metadata SPA/Web: list modul, hak akses, proyek aktif & dapat diakses |
| `POST` | `/api/riwayat-perubahan/list` | `Explicit` | `HistoryController::list` | 🟢 MIGRATED | List riwayat perubahan terfilter per modul, tanggal, & proyek |
| `POST` | `/api/riwayat-perubahan/kavling` | `Explicit` | `HistoryController::kavling` | 🟢 MIGRATED | Riwayat mutasi unit kavling spesifik (mendukung modal detail kavling) |

### B. Public PHP Services (Backend Contract Antar-Modul)
#### `App\Services\AuditTrail\HistoryService`
- `hasTable(): bool` — Memeriksa ketersediaan tabel `history_log`.
- `log(string $module, array $fields): bool` — Kontrak utama pencatatan log audit dari seluruh modul aplikasi dengan auto-enrichment metadata request context & fail-safe error handling.
- `getList(array $filters, int $limit, int $offset): array` — Mengambil data riwayat terformat untuk halaman utama riwayat perubahan.
- `getByKavling(int $idKavling, array $modules, int $limit, int $offset): array` — Mengambil riwayat mutasi unit kavling untuk modal detail spasial/siteplan.
- `getByReferenceAction(string $module, int $referenceId, string $action): array` — Mengambil jejak mutasi spesifik entitas bisnis berdasarkan action tertentu.
- `moduleLabels(): array` — Kamus label human-readable untuk modul-modul bisnis di SIGAPP.
- `formatActionLabel(string $action): string` — Kamus label human-readable untuk nama-nama action transaksi.
- `getProyekIdByKavling(int $idKavling): ?int` — Resolusi aman `id_proyek` dari unit kavling tanpa melanggar layer boundary.

## File Inventory
### Controllers (2)
- `app/Controllers/Web/AuditTrail/RiwayatPerubahanController.php` (29 baris, 1 public methods: `index()`)
- `app/Controllers/Api/AuditTrail/HistoryController.php` (168 baris, 3 public methods: `meta()`, `list()`, `kavling()`)

### Services (1)
- `app/Services/AuditTrail/HistoryService.php` (244 baris, 8 public methods)

### Repositories (1)
- `app/Repositories/AuditTrail/HistoryRepository.php` (206 baris, 10 public methods: `hasTable()`, `insert(array $fields)`, `getList(array $filters, int $limit, int $offset)`, `count(array $filters)`, `getByKavling(int $idKavling, array $modules, int $limit, int $offset)`, `countByKavling(int $idKavling, array $modules)`, `getByReferenceAction(string $module, int $referenceId, string $action)`, `getProyekIdByKavling(int $idKavling)`, `getTargetSiteplanProyekId(int $referenceId)`, `getCashoutProyekIds(int $idCashoutSubkon)`)

### Models (1)
- `app/Models/HistoryLogModel.php`

### Views (1 folder)
- `app/Views/riwayat_perubahan` (1 file: `index.php`)

### CSS & JavaScript (2 files)
- `public/assets/css/riwayat_perubahan.css` (Style terisolasi timeline riwayat, filter form, badge modul/action, dan debug context box)
- `public/assets/js/riwayat_perubahan.js` (256 baris - Logika interaktif filter AJAX, rendering timeline, pagination, modal JSON inspector, dan debug information panel)

### Zero-Downtime Shims (4 file)
- `app/Controllers/RiwayatPerubahan.php` — Shim fallback controller web di root namespace
- `app/Controllers/Api/HistoryController.php` — Shim fallback controller API di root namespace
- `app/Services/HistoryService.php` — Shim fallback service di root namespace
- `app/Repositories/HistoryRepository.php` — Shim fallback repository di root namespace

## Key Functions & Core Business Logic
- **Controller Web (`App\Controllers\Web\AuditTrail\RiwayatPerubahanController`):**
  - `index()`: Merender antarmuka view utama riwayat perubahan (`riwayat_perubahan/index`), memuat kamus label modul untuk filter dropdown, dan menyertakan asset CSS/JS eksternal dengan filemtime cache-busting.
- **Controller API (`App\Controllers\Api\AuditTrail\HistoryController`):**
  - `meta()`: Endpoint metadata (SPA Ready) yang menyajikan daftar modul, kamus label modul, informasi proyek aktif, daftar proyek yang dapat diakses user, serta matriks perizinan (`is_admin`, `can_view_all`, `can_filter_proyek`).
  - `list()`: Endpoint server-side data log riwayat perubahan dengan dukungan filter modul, rentang tanggal (`date_from`, `date_to`), proteksi otorisasi proyek non-admin via `ActiveProyekService`, paginasi fleksibel (`limit`, `offset`), serta CSRF token hash.
  - `kavling()`: Endpoint riwayat mutasi khusus per unit kavling dengan validasi input `id_kavling`, filter modul opsional, paginasi (`history_offset`, `history_limit`), dan verifikasi hak akses proyek kavling untuk non-admin.
- **Service (`App\Services\AuditTrail\HistoryService`):**
  - `log(string $module, array $fields)`: Core logging engine dengan fail-safe try-catch `\Throwable`. Melakukan auto-enrichment konteks debugging request (`_debug`: Client IP, User Agent, URI via `resolveDebugInfo()`), otomatisasi resolusi `id_proyek` dari unit kavling/target/cashout, serialisasi data JSON (`metadata`, `old_data`, `new_data`), dan fallback pencatatan ke `log_message('error')` jika database write mengalami kendala agar tidak ada jejak audit yang hilang tanpa dokumentasi teknis.
  - `getList(array $filters, int $limit, int $offset)`: Mengambil daftar riwayat dengan decoding otomatis seluruh kolom JSON, penyusunan label human-readable untuk modul dan jenis action (`formatActionLabel()`), serta kalkulasi total baris.
  - `getByKavling(int $idKavling, array $modules, int $limit, int $offset)`: Menyediakan data riwayat khusus unit kavling (termasuk riwayat yang berkaitan dengan subkontraktor `cashout_subkon_kavling`) dalam bentuk list objek terformat untuk modal detail dan infinite scroll.
  - `getByReferenceAction(string $module, int $referenceId, string $action)`: Penelusuran jejak mutasi spesifik entitas bisnis berdasarkan action tertentu secara kronologis.
  - `resolveDebugInfo()`: Menangkap Client IP Address, User Agent browser, dan Request URI secara aman dari request context aktif CodeIgniter 4.
  - `resolveProyekId()`: Resolusi hierarkis `id_proyek` dari `id_kavling`, `target_siteplan`, atau `cashout_subkon`.
- **Repository (`App\Repositories\AuditTrail\HistoryRepository`):**
  - `hasTable()`: Pemeriksaan eksistensi tabel `history_log` di database secara aman.
  - `insert(array $fields)`: Eksekusi SQL insert baris audit baru ke tabel `history_log`.
  - `getList(array $filters, int $limit, int $offset)` & `count(array $filters)`: Query pembacaan dan penghitungan data audit dengan JOIN ke tabel `users` untuk menampilkan username akun pembuat riwayat.
  - `getByKavling()` & `countByKavling()`: Query gabungan berbasis `id_kavling` langsung ATAU subquery ke tabel `cashout_subkon_kavling`.
  - `getProyekIdByKavling(int $idKavling)`, `getTargetSiteplanProyekId(int $referenceId)`, `getCashoutProyekIds(int $idCashoutSubkon)`: Enkapsulasi kueri database untuk resolusi proyek yang mengeliminasi layer violations dari controller/service.

## Deprecated & Legacy Shims
- **Deprecated Routes / Endpoints:**
  - `POST /api/history/list` -> **DEPRECATED**, dialihkan ke controller modular via shim `App\Controllers\Api\HistoryController::list`. Gunakan endpoint resmi `POST /api/riwayat-perubahan/list`.
  - `POST /api/history/kavling` -> **DEPRECATED**, dialihkan ke controller modular via shim `App\Controllers\Api\HistoryController::kavling`. Gunakan endpoint resmi `POST /api/riwayat-perubahan/kavling`.
- **Deprecated Classes / Shims (Zero-Downtime Shims):**
  - `app/Controllers/RiwayatPerubahan.php` — **DEPRECATED (Zero-Downtime Shim)**: Dipertahankan untuk kompatibilitas mundur; gunakan `App\Controllers\Web\AuditTrail\RiwayatPerubahanController`.
  - `app/Controllers/Api/HistoryController.php` — **DEPRECATED (Zero-Downtime Shim)**: Dipertahankan untuk kompatibilitas mundur; gunakan `App\Controllers\Api\AuditTrail\HistoryController`.
  - `app/Services/HistoryService.php` — **DEPRECATED (Zero-Downtime Shim)**: Dipertahankan untuk kompatibilitas mundur; gunakan `App\Services\AuditTrail\HistoryService`.
  - `app/Repositories/HistoryRepository.php` — **DEPRECATED (Zero-Downtime Shim)**: Dipertahankan untuk kompatibilitas mundur; gunakan `App\Repositories\AuditTrail\HistoryRepository`.

## Debugging & End-to-End Tracing Matrix
Matriks ini mendokumentasikan alur penelusuran menyeluruh (*End-to-End Tracing*) setiap mutasi data dari event pemicu di modul aplikasi, pemanggilan `HistoryService::log()`, penyimpanan ke tabel `history_log`, hingga visualisasi di antarmuka riwayat serta pencatatan fail-safe di log sistem CodeIgniter 4 ("Selalu Record untuk Debugging"):

| Modul Sumber | Trigger Event / Aksi Pengguna | Calling Service & Method | Parameter `HistoryService::log()` | Enriched `_debug` Context | Visualisasi UI (`/riwayat-perubahan` / Modal) | Fail-Safe & Logging Sysadmin (`writable/logs/`) |
|---|---|---|---|---|---|---|
| **MKDT / Kavling** | Simpan harga jual unit kavling baru/revisi | `Hargajual::simpan()` / `MkdtHistoryService::log()` | `module: 'mkdt'`, `action: 'set_harga_jual'`, `id_kavling`, `old_data: [harga_akhir]`, `new_data: [harga_akhir]` | Client IP, User Agent, URI `/hargajual/simpan` | Badge `MKDT / Kavling`, Action `Set Harga Jual`, JSON modal menampilkan selisih harga lama vs baru | Jika query DB gagal, tertangkap try-catch `HistoryService::log` -> `log_message('error', ...)` |
| **MKDT / Konsumen** | Input / update identitas konsumen kavling | `TransaksiService::save()` / `MkdtHistoryService::log()` | `module: 'mkdt'`, `action: 'isi_data_konsumen'`, `id_kavling`, `summary: 'Nama Konsumen ...'`, `new_data` | Client IP, User Agent, URI `/transaksi/save` | Badge `MKDT / Kavling`, Action `Isi Data Konsumen`, rincian biodata konsumen di JSON modal | Dicatat ke CI4 error log tanpa menggagalkan transaksi penjualan |
| **MKDT / Status** | Perubahan tahapan unit (Booking -> Akad -> SP3K) | `TransaksiService::saveStatus()` / `MkdtHistoryService::log()` | `module: 'mkdt'`, `action: 'ubah_status_kavling'`, `id_kavling`, `summary`, `old_data`, `new_data` | Client IP, User Agent, URI `/api/transaksi/status` | Badge `MKDT / Kavling`, Action `Ubah Status Kavling`, ringkasan perubahan status transaksi | Terisolasi dalam fail-safe logger |
| **MKDT / Kavling** | Pemindahan unit kavling konsumen | `TransaksiService::transferKavling()` | `module: 'mkdt'`, `action: 'pindah_kavling'`, `id_kavling`, `old_data`, `new_data` | Client IP, User Agent, URI `/transaksi/transfer` | Badge `MKDT / Kavling`, Action `Pindah Kavling`, jejak kavling asal dan kavling tujuan | Terisolasi dalam fail-safe logger |
| **MKDT / Batal** | Pembatalan transaksi kavling oleh konsumen | `Mkdt::batal_mkdt()` / `MkdtHistoryService::log()` | `module: 'mkdt'`, `action: 'batal_booking'`, `id_kavling`, `summary: Batal Booking`, `new_data: [alasan, tgl_batal]` | Client IP, User Agent, URI `/mkdt/simpan_batal` | Badge `MKDT / Kavling`, Action `Batal Booking`, alasan pembatalan tertera di timeline | Terisolasi dalam fail-safe logger |
| **Produksi** | Update progres fisik serah terima unit (ST 0% - 100%) | `ProduksiService::save()` / `logHistory()` | `module: 'produksi'`, `action: 'update_progres'`, `id_kavling`, `summary: 'Progres ST ...'`, `metadata: [foto, subkon]` | Client IP, User Agent, URI `/api/produksi/save` | Badge `Produksi`, Action `Update Progres Bangunan`, timeline menampilkan riwayat progres & subkon | Jika DB error, foto tetap tersimpan & error dicatat ke error log CI4 |
| **Dana Jaminan** | Pengajuan pencairan dana jaminan bank | `DanaJaminanService::simpanPengajuan()` | `module: 'dana_jaminan'`, `action: 'pengajuan'`, `id_kavling`, `reference_type: 'dana_jaminan'`, `new_data` | Client IP, User Agent, URI `/api/keuangan/dana-jaminan/ajukan` | Badge `Dana Jaminan`, Action `Pengajuan Pencairan`, rincian nominal dan bank penjamin | Error tidak memutus alur pengajuan jaminan di DB |
| **Pencairan Akad** | Konfirmasi pencairan dana akad KPR | `PencairanAkadService::simpanPencairan()` | `module: 'pencairan_akad'`, `action: 'pencairan'`, `id_kavling`, `reference_type: 'pencairan_akad'`, `new_data` | Client IP, User Agent, URI `/api/keuangan/pencairan-akad/cair` | Badge `Pencairan Akad`, Action `Pencairan Dana Akad`, nominal pencairan dan bank penyalur | Validasi finansial aman di Service, log kegagalan masuk file log |
| **Cashout Subkon** | Verifikasi & realisasi pembayaran subkontraktor | `CashOutService::saveSubkonPayment()` | `module: 'cashout_subkon'`, `action: 'pencairan'`, `reference_type: 'cashout_subkon'`, `metadata: [id_proyek_list]` | Client IP, User Agent, URI `/api/cashout/subkon/bayar` | Badge `Cashout Subkon`, Action `Pencairan`, unit-unit kavling subkon terhubung otomatis | Auto-resolusi daftar proyek via `HistoryRepository::getCashoutProyekIds` |
| **Fee Sales** | Pengajuan dan persetujuan komisi broker eksternal | `FeeSalesBatchService::ajukanBatch()` / `setujuiBatch()` | `module: 'fee_sales'`, `action: 'pengajuan'` / `'approval'`, `reference_type: 'batch_fee_sales'`, `metadata` | Client IP, User Agent, URI `/api/fee-sales/batch` | Badge `Fee Sales External`, Action `Pencairan`, nomor batch dan jumlah komisi | Terisolasi dalam fail-safe logger |
| **Target Siteplan** | Perencanaan alokasi target penjualan bulanan | `TargetSiteplanService::saveTarget()` | `module: 'target_siteplan'`, `action: 'update'`, `reference_type: 'target_siteplan'`, `reference_id` | Client IP, User Agent, URI `/api/target-siteplan/save` | Badge `Target Siteplan`, Action `Pembaruan Data`, auto-resolusi proyek via target | Terisolasi dalam fail-safe logger |
| **Siteplan Spasial** | Perubahan atribut visual / polygon kavling | `Siteplan::save_kavling()` | `module: 'siteplan'`, `action: 'update'`, `reference_type: 'kavling'`, `reference_id` | Client IP, User Agent, URI `/siteplan/save_kavling` | Badge `Siteplan (Planning)`, Action `Pembaruan Data`, jejak koordinat & tipe kavling | Terisolasi dalam fail-safe logger |
| **Master Proyek / Cluster / Jalan / Tipe** | Mutasi master data perencanaan perumahan | `ProyekService`, `ClusterService`, `JalanService`, `TipeService` | `module: 'master_*'`, `action: 'update'`, `reference_type`, `reference_id`, `old_data`, `new_data` | Client IP, User Agent, URI `/api/proyek/*` | Badge `Master Proyek/Cluster/Jalan/Tipe`, rincian properti yang diubah | Terisolasi dalam fail-safe logger |
| **Simulasi Fail-Safe** | Gangguan koneksi database / tabel terkunci saat mutasi | `HistoryService::log()` saat `insert()` melempar Exception | Menangkap `\Throwable`, mengembalikan boolean `false` | Ditangkap sebelum melempar exception ke modul pemanggil | Mutasi bisnis utama tetap sukses tanpa crash di layar user | Sysadmin melihat jejak error lengkap di `writable/logs/log-YYYY-MM-DD.log` dengan file & line number |

## Readiness Checklist
- [x] Has Service Layer
- [x] Has Repository Layer
- [x] Web & API Controllers Separated
- [x] Explicit Routes Defined
- [x] Zero Controller Layer Violations
- [x] MODULE.md Initialized

## Dead Code Candidates
_Jalankan `php spark module:deadcode AuditTrail` untuk mengaudit dead code kandidat._

## Notes
- **Cross-Module Callers & Consumers:**
  - **Modul `MKDT` & `Transaksi`:** Mengonsumsi `HistoryService` secara ekstensif (melalui `MkdtHistoryService` dan pemanggilan langsung) untuk pencatatan harga jual, biodata konsumen, mutasi kavling, pembatalan booking, standing instruction, dan rekonsiliasi keuangan.
  - **Modul `Produksi`:** Memanggil `HistoryService::log('produksi', ...)` untuk mencatat setiap tahapan progres serah terima (ST 0% - 100%) dan catatan komplain lapangan.
  - **Modul `Keuangan` (Dana Jaminan & Pencairan Akad):** Memanggil `HistoryService::log('dana_jaminan', ...)` dan `HistoryService::log('pencairan_akad', ...)` untuk mencatat pengajuan dan realisasi pencairan dana perbankan.
  - **Modul `CashOut`:** Memanggil `HistoryService::log('cashout_subkon', ...)` untuk mencatat alokasi dan realisasi pembayaran subkontraktor yang terhubung ke unit-unit kavling.
  - **Modul `SalesExternal`:** Memanggil `HistoryService::log('fee_sales', ...)` untuk mencatat pengajuan dan approval batch komisi penjualan pihak ketiga.
  - **Modul `Siteplan`:** Memanggil `HistoryService::log('siteplan', ...)` dan mengonsumsi endpoint `/api/riwayat-perubahan/kavling` melalui `siteplan-detail-modal.js` untuk menampilkan riwayat unit kavling pada visualisasi peta spasial.
  - **Modul `TargetSiteplan`:** Memanggil `HistoryService::log('target_siteplan', ...)` untuk merekam perubahan target proyek perumahan.
  - **Modul Master Data (`Proyek`, `Cluster`, `Jalan`, `Tipe`):** Mencatat setiap mutasi data master fisik ke audit trail.

## Change Log
- **`2026-11-12` [`REFACTOR`] — Refaktorisasi Penuh Modul AuditTrail (Arsitektur Thin Controller, Standardisasi Debugging Context, Fail-Safe Logging, & Assets Terpisah):**
  - **Routes & Public Contracts:**
    - Mendaftarkan rute web eksplisit: `GET /riwayat-perubahan` -> `App\Controllers\Web\AuditTrail\RiwayatPerubahanController::index`.
    - Mendaftarkan rute API eksplisit: `GET /api/riwayat-perubahan/meta` -> `App\Controllers\Api\AuditTrail\HistoryController::meta`.
    - Mendaftarkan rute API eksplisit: `POST /api/riwayat-perubahan/list` -> `App\Controllers\Api\AuditTrail\HistoryController::list`.
    - Mendaftarkan rute API eksplisit: `POST /api/riwayat-perubahan/kavling` -> `App\Controllers\Api\AuditTrail\HistoryController::kavling`.
    - Mengamankan endpoint lama `/api/history/*` via Zero-Downtime Shim.
  - **Controller / Service / Repo / Model:**
    - `App\Controllers\Web\AuditTrail\RiwayatPerubahanController`: Thin Controller Web yang bersih, memuat kamus label modul dan merender view `riwayat_perubahan/index`.
    - `App\Controllers\Api\AuditTrail\HistoryController`: Thin Controller API yang mewarisi `BaseApiController`, menyediakan endpoint `meta()`, `list()`, dan `kavling()` dengan envelope respons JSON standar, sanitasi input, validasi otorisasi proyek non-admin, dan token CSRF.
    - `App\Services\AuditTrail\HistoryService`: Service layer terpusat dengan fail-safe try-catch `\Throwable`, standardisasi auto-enrichment konteks debugging request (`_debug`: Client IP, User Agent, URI via `resolveDebugInfo()`), resolusi otomatis `id_proyek` (`resolveProyekId()`), serialisasi/deserialisasi JSON aman, kamus label modul & action manusiawi, serta pelaporan error fatal ke `log_message('error')`.
    - `App\Repositories\AuditTrail\HistoryRepository`: Repository layer independen yang mengenkapsulasi seluruh kueri database ke tabel `history_log` dengan JOIN `users`, filter pencarian modul/tanggal/proyek, filter relasi kavling (termasuk subquery `cashout_subkon_kavling`), dan kueri relasi proyek (`getProyekIdByKavling`, `getTargetSiteplanProyekId`, `getCashoutProyekIds`) guna mengeliminasi seluruh layer violations.
    - `App\Models\HistoryLogModel`: Model resmi CI4 untuk tabel `history_log`.
    - Zero-Downtime Shims: Menyediakan 4 class shim fallback di root namespace (`app/Controllers/RiwayatPerubahan.php`, `app/Controllers/Api/HistoryController.php`, `app/Services/HistoryService.php`, `app/Repositories/HistoryRepository.php`).
  - **View / CSS / JS:**
    - `app/Views/riwayat_perubahan/index.php`: Restrukturisasi view dengan pemisahan 3-tier assets (`riwayat_perubahan.css` & `riwayat_perubahan.js`) disertai parameter cache-busting `filemtime()`, standarisasi divider Bootstrap Vuexy (`divider divider-left`), dan panel informasi konteks debugging `#history-debug-box`.
    - `public/assets/css/riwayat_perubahan.css`: Asset stylesheet baru yang terisolasi untuk styling timeline riwayat, filter controls, badge warna modul/action, serta panel debug context box.
    - `public/assets/js/riwayat_perubahan.js`: Asset javascript baru yang modular untuk manajemen AJAX request, rendering timeline dinamis, kontrol paginasi, modal inspect JSON payload, integrasi CSRF token update, dan sinkronisasi live debug box.
  - **Deprecations:** Rute dan controller lama di root namespace berstatus deprecated dan dipertahankan melalui mekanisme Zero-Downtime Shim.
- `2026-11-12` [`SYNC`] — Sinkronisasi status & inventori modul melalui `module:sync`.

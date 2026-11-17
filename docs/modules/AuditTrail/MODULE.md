# Module: AuditTrail

**Status:** `REFACTORED`  
**Readiness Score:** `100/100`  
**Last Synced:** `2026-11-16 14:59:16` @ `af95026`  

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
- `history_log` *(akses tulis eksklusif hanya untuk modul AuditTrail)*

### Shared Tables (Dibaca dari modul lain)
- `users`

## Contracts
### A. Public HTTP Endpoints & Routes Inventory
| Method | URI / Pattern | Tipe | Handler | Status Migrasi | Target API / Catatan |
|---|---|---|---|---|---|
| `GET` | `/riwayat-perubahan` | `Explicit` | `\App\Controllers\Web\AuditTrail\RiwayatPerubahanController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/riwayat-perubahan/meta` | `Explicit` | `HistoryController::meta` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/riwayat-perubahan/list` | `Explicit` | `HistoryController::list` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/riwayat-perubahan/kavling` | `Explicit` | `HistoryController::kavling` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |

### B. Public PHP Services (Backend Contract Antar-Modul)
#### `HistoryService.php`
- `hasTable()`
- `log(string $module, array $fields)`
- `getList(array $filters, int $limit, int $offset)`
- `getByKavling(int $idKavling, array $modules, int $limit, int $offset)`
- `getByReferenceAction(string $module, int $referenceId, string $action)`
- `moduleLabels()`
- `formatActionLabel(string $action)`
- `getProyekIdByKavling(int $idKavling)`

## File Inventory
### Controllers (2)
- `app/Controllers/Web/AuditTrail/RiwayatPerubahanController.php` (29 baris, 1 public methods: `index()`)
- `app/Controllers/Api/AuditTrail/HistoryController.php` (168 baris, 3 public methods: `meta()`, `list()`, `kavling()`)

### Services (1)
- `app/Services/AuditTrail/HistoryService.php` (248 baris, 8 public methods)

### Repositories (1)
- `app/Repositories/AuditTrail/HistoryRepository.php` (206 baris, 10 public methods: `hasTable()`, `insert(array $fields)`, `getList(array $filters, int $limit, int $offset)`, `count(array $filters)`, `getByKavling(int $idKavling, array $modules, int $limit, int $offset)`, `countByKavling(int $idKavling, array $modules)`, `getByReferenceAction(string $module, int $referenceId, string $action)`, `getProyekIdByKavling(int $idKavling)`, `getTargetSiteplanProyekId(int $referenceId)`, `getCashoutProyekIds(int $idCashoutSubkon)`)

### Models (1)
- `app/Models/HistoryLogModel.php`

### Views (1 folder)
- `app/Views/riwayat_perubahan` (1 file: `index.php`)

### JavaScript (1 file)
- `public/assets/js/riwayat_perubahan.js` (256 baris)

### Zero-Downtime Shims (4 file)
- `app/Controllers/RiwayatPerubahan.php`
- `app/Controllers/Api/HistoryController.php`
- `app/Services/HistoryService.php`
- `app/Repositories/HistoryRepository.php`

## Key Functions
- **Service (`app/Services/AuditTrail/HistoryService.php`):** `hasTable()`, `log(string $module, array $fields)`, `getList(array $filters, int $limit, int $offset)`, `getByKavling(int $idKavling, array $modules, int $limit, int $offset)`, `getByReferenceAction(string $module, int $referenceId, string $action)`
- **Repository (`app/Repositories/AuditTrail/HistoryRepository.php`):** `hasTable()`, `insert(array $fields)`, `getList(array $filters, int $limit, int $offset)`, `count(array $filters)`, `getByKavling(int $idKavling, array $modules, int $limit, int $offset)`

## Deprecated & Legacy Shims
- **Deprecated Routes / Endpoints:**
  - `POST /api/history/list` -> **DEPRECATED**, dialihkan ke controller modular via shim `App\Controllers\Api\HistoryController::list`. Gunakan endpoint resmi `POST /api/riwayat-perubahan/list`.
  - `POST /api/history/kavling` -> **DEPRECATED**, dialihkan ke controller modular via shim `App\Controllers\Api\HistoryController::kavling`. Gunakan endpoint resmi `POST /api/riwayat-perubahan/kavling`.
- **Deprecated Classes / Shims (Zero-Downtime Shims):**
  - `app/Controllers/RiwayatPerubahan.php` — **DEPRECATED (Zero-Downtime Shim)**: Dipertahankan untuk kompatibilitas mundur; gunakan `App\Controllers\Web\AuditTrail\RiwayatPerubahanController`.
  - `app/Controllers/Api/HistoryController.php` — **DEPRECATED (Zero-Downtime Shim)**: Dipertahankan untuk kompatibilitas mundur; gunakan `App\Controllers\Api\AuditTrail\HistoryController`.
  - `app/Services/HistoryService.php` — **DEPRECATED (Zero-Downtime Shim)**: Dipertahankan untuk kompatibilitas mundur; gunakan `App\Services\AuditTrail\HistoryService`.
  - `app/Repositories/HistoryRepository.php` — **DEPRECATED (Zero-Downtime Shim)**: Dipertahankan untuk kompatibilitas mundur; gunakan `App\Repositories\AuditTrail\HistoryRepository`.

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
- **`2026-11-16` [`FEATURE`] — Penambahan Label Aksi Approval Request Target Siteplan pada `HistoryService::formatActionLabel()` (Target Siteplan Fase 4):**
  - **Routes & Public Contracts:** Tidak ada perubahan rute atau tanda tangan kontrak `HistoryService`.
  - **Controller / Service / Repo / Model:**
    - `app/Services/AuditTrail/HistoryService.php`: Menambahkan pemetaan label aksi manusiawi pada `formatActionLabel()` untuk `target_approve_tipe` (`ACC Ubah Tipe Target`) guna mendukung pencatatan riwayat approval pengajuan perubahan tipe target pada `TargetSiteplanService::reviewRequest()`.
  - **View / CSS / JS:** Tidak ada perubahan view/asset modul `AuditTrail`.
  - **Deprecations:** Tidak ada.
- **`2026-11-16` [`FEATURE`] — Penambahan Label Aksi Versioning Target Siteplan pada `HistoryService::formatActionLabel()` (Target Siteplan Fase 3):**
  - **Routes & Public Contracts:** Tidak ada perubahan rute atau tanda tangan kontrak `HistoryService`.
  - **Controller / Service / Repo / Model:**
    - `app/Services/AuditTrail/HistoryService.php`: Menambahkan pemetaan label aksi manusiawi pada `formatActionLabel()` untuk `target_add_kavling` (`Tambah Kavling Target`), `target_remove_kavling` (`Hapus Kavling Target`), dan `target_update_tipe` (`Ubah Tipe Target Kavling`) guna mendukung timeline versioning `TargetSiteplanService`.
  - **View / CSS / JS:** Tidak ada perubahan view/asset modul `AuditTrail`.
  - **Deprecations:** Tidak ada.
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

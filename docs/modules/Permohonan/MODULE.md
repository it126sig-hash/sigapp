# Module: Permohonan

**Status:** `REFACTORED`  
**Readiness Score:** `100/100`  
**Last Synced:** `2026-11-24 20:25:29` @ `e156bb6`  

## Purpose
Pusat Permohonan & Approval terpadu lintas 7 modul (BPB, Fee Sales, Request Kavling, Tagihan Direksi, Cashout Subkon, MGM, dan Tiket Masalah) beserta 7 modal respon langsung.

## Out of scope
- Modul `Permohonan` bertindak sebagai *aggregation & response hub* (read-only index + modal launcher). Modul ini tidak menyimpan tabel transaksi sendiri dan tidak melakukan mutasi langsung ke tabel modul sumber (`bpb_requests`, `fee_sales_pengajuan`, `kavling_request`, `invoice_log`, `cashout_subkon_detail`, `referral_bonuses`, `tiket_masalah`); seluruh aksi approval/penolakan/pencairan didelegasikan langsung ke endpoint API masing-masing modul asal.

## Invariants
- `PermohonanRepository` hanya melakukan operasi `SELECT` read-only lintas 7 tabel modul sumber dan menggunakan pola *lightweight UNION ALL index + separate batch hydration per module* agar aman terhadap perbedaan collation antar-tabel (`utf8mb4_general_ci`, `utf8mb4_0900_ai_ci`, `latin1_swedish_ci`, `utf8mb3_general_ci`).
- Otorisasi `id_proyek` dan `scope` (`assigned_to_me`, `my_requests`, `department_monitoring`) wajib diverifikasi oleh `PermohonanService::resolveUserContext()` berdasarkan keanggotaan grup (`auth_groups_users`), akses proyek (`proyek.id_users`), dan hak akses `FeeSalesSettingService::getUserPermissions()`.
- Controller Web dan API (`Web\Permohonan\PermohonanController`, `Api\Permohonan\PermohonanController`) bersih 100% dari query database langsung (`Zero Controller Layer Violations`).

## Database Tables
### Owner Table(s)
- *(Tidak memiliki tabel database independen)*

### Shared Tables (Dibaca dari modul lain)
- `bpb_requests`
- `fee_sales_pengajuan`
- `kavling_request`
- `invoice_log`
- `cashout_subkon_detail`
- `referral_bonuses`
- `tiket_masalah`
- `proyek`
- `kavling`
- `mkdt`
- `konsumen`
- `users`

## Contracts
### A. Public HTTP Endpoints & Routes Inventory
| Method | URI / Pattern | Tipe | Handler | Status Migrasi | Target API / Catatan |
|---|---|---|---|---|---|
| `GET` | `/permohonan` | `Explicit` | `\App\Controllers\Web\Permohonan\PermohonanController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/permohonan/meta` | `Explicit` | `PermohonanController::meta` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/permohonan/summary` | `Explicit` | `PermohonanController::summary` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/permohonan/list` | `Explicit` | `PermohonanController::list` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |

### B. Public PHP Services (Backend Contract Antar-Modul)
#### `PermohonanService.php`
- `resolveUserContext(int $userId)`
- `getMeta(int $userId)`
- `getSummary(int $userId, ?int $idProyek = null, string $scope = 'assigned_to_me')`
- `getList(int $userId, array $filters = [])`

## File Inventory
### Controllers (2)
- `app/Controllers/Web/Permohonan/PermohonanController.php` (67 baris, 1 public methods: `index()`)
- `app/Controllers/Api/Permohonan/PermohonanController.php` (114 baris, 3 public methods: `meta()`, `summary()`, `list()`)

### Services (1)
- `app/Services/Permohonan/PermohonanService.php` (869 baris, 4 public methods)

### Repositories (1)
- `app/Repositories/Permohonan/PermohonanRepository.php` (1213 baris, 6 public methods: `getUserGroupIds(int $userId)`, `getAccessibleProjects(int $userId, bool $isAdmin)`, `getUserSnapshots(array $userIds)`, `getSummaryCounts(array $context, ?int $idProyek = null)`, `getPaginatedIndex(array $context, array $filters)`, `hydrateRows(array $indexRows, int $userId)`)

### Models (0)

### Views (1 folder)
- `app/Views/permohonan` (1 file: `index.php`)

### JavaScript (1 file)
- `public/assets/js/permohonan.js` (2234 baris)

## Key Functions
- **Web Controller (`App\Controllers\Web\Permohonan\PermohonanController`):**
  - `index()`: Merender halaman `permohonan/index` dengan initial payload SSR (`meta`, `summary`, `projects`, `roleId`, `roleName`, `deepLink` untuk `scope`, `module`, `id`, `id_proyek`).
- **API Controller (`App\Controllers\Api\Permohonan\PermohonanController`):**
  - `meta()`: Mengembalikan metadata opsi filter (`modules`, `scopes`, `statuses`, `projects`, dan `permissions` Vue-ready).
  - `summary()`: Mengembalikan ringkasan badge counter untuk 3 scope tab dan breakdown per modul (`by_module`, `by_scope_and_module`) dengan validasi `id_proyek` & `scope`.
  - `list()`: Mengembalikan daftar permohonan terpaginasi lintas 7 modul (`items` DTO seragam + `pagination` + `summary`) dengan filter `scope`, `module`, `status`, `id_proyek`, `search`, `page`, dan `per_page`.
- **Service (`App\Services\Permohonan\PermohonanService`):**
  - `resolveUserContext(int $userId)`: Resolusi role grup, hak akses proyek (`proyek.id_users`), dan permission Fee Sales (`FeeSalesSettingService::getUserPermissions()`).
  - `getMeta(int $userId)`, `getSummary(int $userId, ?int $idProyek = null, string $scope = 'assigned_to_me')`, `getList(int $userId, array $filters = [])`: Orkestrasi query indeks + batch hydration + pemetaan DTO seragam per modul (`mapBpbDto`, `mapFeeSalesDto`, `mapKavlingRequestDto`, `mapTagihanDireksiDto`, `mapCashoutSubkonDto`, `mapMgmDto`, `mapTiketMasalahDto`) lengkap dengan `can_respond`, `primary_action_label`, `modal_payload`, dan `spa_route`.
- **Repository (`App\Repositories\Permohonan\PermohonanRepository`):**
  - `getUserGroupIds(int $userId)`, `getAccessibleProjects(int $userId, bool $isAdmin)`, `getUserSnapshots(array $userIds)`: Resolusi user/grup/proyek/divisi secara batch.
  - `getSummaryCounts(array $context, ?int $idProyek = null)` & `getPaginatedIndex(array $context, array $filters)`: Query indeks `UNION ALL` ringan lintas 7 modul (`bpb`, `fee_sales`, `kavling_request`, `tagihan_direksi`, `cashout_subkon`, `mgm`, `tiket_masalah`) dengan filter scope/status/proyek/pencarian.
  - `hydrateRows(array $indexRows, int $userId)`: Batch hydration terpisah per modul untuk mengambil detail relasi tanpa masalah collation lintas tabel.
- **Frontend (`app/Views/permohonan/index.php`, `public/assets/css/permohonan.css`, `public/assets/js/permohonan.js`):**
  - Halaman Pola A `.permohonan-page` (3 KPI scope cards, 8 module filter pills, filter proyek/status/search, tabel desktop + `#permohonan-mobile-list`, pagination).
  - `sanitizeUrl()` (`permohonan.js`): Normalisasi URL subpath aplikasi (termasuk `spa_route` relatif `/...` via `apiUrl()`) dan pencegahan scheme berbahaya.
  - 7 Modal Respon Langsung yang memanggil endpoint API modul terkait serta menyinkronkan ulang badge notifikasi (`window.loadNotificationBadge()`): `#permohonan-bpb-modal`, `#permohonan-fee-sales-modal`, `#permohonan-kavling-modal`, `#permohonan-tagihan-direksi-modal`, `#modal-cashout-subkon`, `#permohonan-mgm-modal`, dan `#modal_tiket_masalah`.

## Deprecated & Legacy Shims
- *(Tidak ada fungsi, route, atau class shim yang berstatus deprecated saat ini).*

## Readiness Checklist
- [x] Has Service Layer
- [x] Has Repository Layer
- [x] Web & API Controllers Separated
- [x] Explicit Routes Defined
- [x] Zero Controller Layer Violations
- [x] MODULE.md Initialized

## Dead Code Candidates
_Jalankan `php spark module:deadcode Permohonan` untuk mengaudit dead code kandidat._

## Notes
- **Asset CSS Modul:** `public/assets/css/permohonan.css` (1038 baris, dimuat dengan cache-busting `filemtime()`, seluruh rule di-scope di bawah `.permohonan-page` dengan dukungan `.dark-layout` dan responsif mobile).
- **Integrasi Menu & Notifikasi:**
  - Menu sidebar `Permohonan & Approval` (`url: 'permohonan'`, `slug: 'permohonan'`, `icon: 'check-square'`, `id=52`) didaftarkan melalui migration `2026-11-25-000001_UpgradePushSubscriptionsAndNotificationTaxonomy.php` untuk role `1..10`.
  - Notification Center `#list-notif` menyediakan tombol pintasan `#notif-permohonan-link` menuju `/permohonan`, dan `NotificationTaxonomy::resolve()` mengarahkan `spa_route` untuk notifikasi ber-intent `action_required` pada 7 modul ke `/permohonan?module=<modul>&id=<ref_id>`.
- **Cross-Module API Consumers (7 Modal Respon Langsung di `permohonan.js` & `index.php`):**
  - `BPB`: `/api/bpb/*` (`detail`, `sign`, `reject`, `process`, `verify-purchase`, `pdf`).
  - `SalesExternal (Fee Sales)`: `/api/sales-external/fee/pengajuan/*` (`detail`, `sign`, `reject`, `cairkan`, `pdf`).
  - `Transaksi (Request Kavling)`: `/api/kavling-request/*` (`detail`, `status`, `apply-ubah-tipe`) + `/api/tipe/list`.
  - `Keuangan (Tagihan Direksi)`: `/api/direksi/tagihan/*` (`detail`, `sign`, `reject`).
  - `Keuangan (Cashout Subkon)`: `app/Views/cashout_subkon/modal_cashout_subkon.php` & `/api/keuangan/cashout-subkon/*` (`window.openCOSubkon()`).
  - `MGM`: `/api/mgm/*` (`list`, `subrows`, `konfirmasi-bonus`, `ajukan-keuangan`, `bayar-promosi`, `cairkan-keuangan`, `ajukan-reimburse`, `bayar-member`).
  - `TiketMasalah`: `app/Views/tiket_masalah/modal_form.php` & `/api/tiket-masalah/*` (`window.tm_open_detail()` / `window.openTiketMasalah()`).

## Change Log
- `2026-11-24` [`FEATURE`] — **Inisiasi Modul Pusat Permohonan & Approval (`/permohonan` & `/api/permohonan/*`) (Score 100/100, PASS QA)**:
  - **Routes & Public Contracts:** Menambahkan `GET /permohonan` (`Web\Permohonan\PermohonanController::index`) serta `GET /api/permohonan/meta`, `GET /api/permohonan/summary`, dan `GET /api/permohonan/list` (`Api\Permohonan\PermohonanController`, `RAW_JSON_READY`, filter `login`).
  - **Controller / Service / Repo / Model:** Menambahkan `Web\Permohonan\PermohonanController`, `Api\Permohonan\PermohonanController`, `PermohonanService` (`resolveUserContext`, `getMeta`, `getSummary`, `getList`), dan `PermohonanRepository` (agregasi `UNION ALL` + batch hydration aman collation lintas 7 modul).
  - **View / CSS / JS:** Menambahkan `app/Views/permohonan/index.php`, `public/assets/css/permohonan.css`, dan `public/assets/js/permohonan.js` (Pola A + 7 modal respon langsung + `sanitizeUrl()` subpath-aware + sinkronisasi `loadNotificationBadge()`).
  - **Deprecations:** Tidak ada.
- `2026-11-24` [`SYNC`] — Sinkronisasi status & inventori modul melalui `module:sync`.

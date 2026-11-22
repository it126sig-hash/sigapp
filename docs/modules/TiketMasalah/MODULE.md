# Module: TiketMasalah

**Status:** `REFACTORED`  
**Readiness Score:** `100/100`  
**Last Synced:** `2026-11-04 13:26:29` @ `2d5d609`  

## Purpose
Pelaporan kendala lintas divisi, delegasi PIC perbaikan lapangan, foto bukti, dan tracking progres penyelesaian.

## Out of scope
- Hal-hal di luar tanggung jawab domain TiketMasalah.

## Invariants
- Modul lain tidak boleh memanipulasi tabel pemilik secara langsung.
- Perubahan finansial/status harus diverifikasi di level Service.

## Database Tables
### Owner Table(s)
- `tiket_masalah` *(akses tulis eksklusif hanya untuk modul TiketMasalah)*
- `tiket_masalah_progress` *(akses tulis eksklusif hanya untuk modul TiketMasalah)*
- `tiket_masalah_foto` *(akses tulis eksklusif hanya untuk modul TiketMasalah)*
- `tiket_masalah_user` *(akses tulis eksklusif hanya untuk modul TiketMasalah)*

### Shared Tables (Dibaca dari modul lain)
- `kavling`
- `proyek`
- `users`

## Contracts
### A. Public HTTP Endpoints & Routes Inventory
| Method | URI / Pattern | Tipe | Handler | Status Migrasi | Target API / Catatan |
|---|---|---|---|---|---|
| `GET` | `/tiket-masalah` | `Explicit` | `\App\Controllers\Web\TiketMasalah\TiketMasalahController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/tiket-masalah/list` | `Explicit` | `TiketMasalahController::list` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/tiket-masalah/detail` | `Explicit` | `TiketMasalahController::detail` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/tiket-masalah/progress` | `Explicit` | `TiketMasalahController::progress` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/tiket-masalah/store` | `Explicit` | `TiketMasalahController::store` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/tiket-masalah/update` | `Explicit` | `TiketMasalahController::update` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/tiket-masalah/add-progress` | `Explicit` | `TiketMasalahController::addProgress` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/tiket-masalah/ref-info` | `Explicit` | `TiketMasalahController::refInfo` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/tiket-masalah/users` | `Explicit` | `TiketMasalahController::users` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/tiket-masalah/divisions` | `Explicit` | `TiketMasalahController::divisions` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/tiket-masalah/creators` | `Explicit` | `TiketMasalahController::creators` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/tiket-masalah/create-others-area` | `Explicit` | `TiketMasalahController::createOthersArea` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/tiket-masalah/datatable` | `Explicit` | `TiketMasalahController::datatable` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/tiket-masalah/toggle-pin` | `Explicit` | `TiketMasalahController::togglePin` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |

### B. Public PHP Services (Backend Contract Antar-Modul)
#### `TiketMasalahService.php`
- `getListByRef(string $refType, int $refId)`
- `getDetail(int $idTiket)`
- `getProgress(int $idTiket, int $limit, int $offset)`
- `getDatatableData(array $params)`
- `createTiket(array $data, array $files)`
- `updateTiket(int $idTiket, array $data, array $files)`
- `addProgress(int $idTiket, array $data, array $files)`
- `togglePinProgress(int $idProgress)`
- `getRefInfo(string $refType, int $refId)`
- `getUserList()`
- `getCreatorList()`
- `getDivisionList()`
- `createOthersArea(array $params, int $userId)`

## File Inventory
### Controllers (2)
- `app/Controllers/Web/TiketMasalah/TiketMasalahController.php` (19 baris, 1 public methods: `index()`)
- `app/Controllers/Api/TiketMasalah/TiketMasalahController.php` (218 baris, 13 public methods: `list()`, `detail()`, `progress()`, `store()`, `update()`, `addProgress()`, `togglePin()`, `refInfo()`, `users()`, `creators()`, `divisions()`, `datatable()`, `createOthersArea()`)

### Services (1)
- `app/Services/TiketMasalah/TiketMasalahService.php` (741 baris, 13 public methods)

### Repositories (1)
- `app/Repositories/TiketMasalah/TiketMasalahRepository.php` (179 baris, 7 public methods: `getListWithSummary(string $refType, int $refId)`, `getDetailFull(int $idTiket)`, `getProgressPaginated(int $idTiket, int $limit, int $offset)`, `getDatatables(array $params)`, `countDatatables(array $params)`, `countAll()`, `insertOthersArea(array $fields)`)

### Models (4)
- `app/Models/TiketMasalahModel.php`
- `app/Models/TiketMasalahProgressModel.php`
- `app/Models/TiketMasalahFotoModel.php`
- `app/Models/TiketMasalahUserModel.php`

### Views (1 folder)
- `app/Views/tiket_masalah` (1 file: `index.php`)

### JavaScript (2 file)
- `public/assets/js/tiket_masalah/index.js` (313 baris)
- `public/assets/js/siteplan/tiket-masalah.js` (1943 baris)

### Zero-Downtime Shims (4 file)
- `app/Controllers/Web/TiketMasalahController.php`
- `app/Controllers/Api/TiketMasalahController.php`
- `app/Services/TiketMasalahService.php`
- `app/Repositories/TiketMasalahRepository.php`

## Key Functions
- **Service (`app/Services/TiketMasalah/TiketMasalahService.php`):** `getListByRef(string $refType, int $refId)`, `getDetail(int $idTiket)`, `getProgress(int $idTiket, int $limit, int $offset)`, `getDatatableData(array $params)`, `createTiket(array $data, array $files)`
- **Repository (`app/Repositories/TiketMasalah/TiketMasalahRepository.php`):** `getListWithSummary(string $refType, int $refId)`, `getDetailFull(int $idTiket)`, `getProgressPaginated(int $idTiket, int $limit, int $offset)`, `getDatatables(array $params)`, `countDatatables(array $params)`

## Deprecated & Legacy Shims
- `app/Controllers/Web/TiketMasalahController.php` — **DEPRECATED (Zero-Downtime Shim)**: Dipertahankan untuk kompatibilitas mundur; gunakan class modular di namespace `TiketMasalah`.
- `app/Controllers/Api/TiketMasalahController.php` — **DEPRECATED (Zero-Downtime Shim)**: Dipertahankan untuk kompatibilitas mundur; gunakan class modular di namespace `TiketMasalah`.
- `app/Services/TiketMasalahService.php` — **DEPRECATED (Zero-Downtime Shim)**: Dipertahankan untuk kompatibilitas mundur; gunakan class modular di namespace `TiketMasalah`.
- `app/Repositories/TiketMasalahRepository.php` — **DEPRECATED (Zero-Downtime Shim)**: Dipertahankan untuk kompatibilitas mundur; gunakan class modular di namespace `TiketMasalah`.

## Readiness Checklist
- [x] Has Service Layer
- [x] Has Repository Layer
- [x] Web & API Controllers Separated
- [x] Explicit Routes Defined
- [x] Zero Controller Layer Violations
- [x] MODULE.md Initialized

## Dead Code Candidates
_Jalankan `php spark module:deadcode TiketMasalah` untuk mengaudit dead code kandidat._

## Notes
- Modul diinisialisasi melalui tooling otomatis.
- **Cross-Module Callers & Consumers:**
  - `Siteplan` & `Transaksi` (`KavlingRepository::addActiveTiketSelect` dan `OthersRepository::addActiveTiketSelect`): Membaca data agregasi tabel `tiket_masalah` (`ref_type = 'kavling'` dan `ref_type = 'others'`, `status NOT IN ('selesai', 'batal', 'hold')`) untuk menampilkan indikator denyut spasial masalah per kavling maupun per aset fasum/penunjang pada antarmuka peta Siteplan interaktif. Representasi spasial di modul `Siteplan` (`public/assets/js/siteplan/tiket-overlay.js` & `master.js`) didukung dengan frame rate capping 30 FPS, pulsing high-contrast stroke, centroid warning badge `!`, safe centroid calculation untuk poligon fasum, dan eliminasi race condition via agregasi atomik.

### Audit Verifikasi Migrasi (2026-11-22)
- **Status Ledger:** `COMPLETED (Catatan Minor)` (Status modul `REFACTORED`, Readiness Score `100/100`).
- **Terverifikasi Bersih:**
  - Keempat zero-downtime shims (`app/Controllers/Web/TiketMasalahController.php`, `app/Controllers/Api/TiketMasalahController.php`, `app/Services/TiketMasalahService.php`, `app/Repositories/TiketMasalahRepository.php`) adalah `thin_shim` murni (7 baris).
  - Seluruh 14 rute di `app/Config/Routes.php` (baris 1005–1018 dan 1022) eksplisit mengarah ke controller modular (`0` legacy route, `0` autoroute).
  - Web controller (`app/Controllers/Web/TiketMasalah/TiketMasalahController.php`, 18 baris) bersih dari response JSON dan akses repository langsung.
- **Temuan Minor (Perlu Perhatian):**
  1. `app/Controllers/Api/TiketMasalah/TiketMasalahController.php` (baris 5, 9) masih mewarisi `App\Controllers\BaseController` + `use ResponseTrait`, belum mewarisi `App\Controllers\Api\BaseApiController` (meski bersih dari `view()`, `redirect()`, dan string HTML).
  2. `app/Services/TiketMasalah/TiketMasalahService.php` selain penggunaan transaksi (`transStart`/`transComplete`/`transStatus`), masih memanggil `$this->db->table()` langsung di Service pada:
     - Baris 214–217 (`createTiket`) dan baris 415 (`updateTiket`) untuk query `auth_groups_users`.
     - Baris 594–635 (`getRefInfo`) untuk query `kavling k` dan `others o` beserta join terkait.
     - Baris 640–668 (`getUserList`, `getCreatorList`, `getDivisionList`) untuk query `users` dan `auth_groups`.

## Change Log
- `2026-11-22` [`AUDIT`] — Audit verifikasi migrasi modular: status ledger `COMPLETED (Catatan Minor)` — Api `TiketMasalahController` masih extends `BaseController` (belum `BaseApiController`) dan `TiketMasalahService` masih memanggil `$this->db->table()` langsung pada beberapa query.
- **2026-11-04 [`FEATURE`] — Perluasan Konsumsi Tiket Masalah Spasial untuk Entitas Others/Fasum di Siteplan:**
  - **Routes & Public Contracts:** Tidak berubah. Rute API `/api/tiket-masalah` tetap stabil.
  - **Controller / Service / Repo / Model:** `OthersRepository::addActiveTiketSelect` pada modul Siteplan kini mengonsumsi tabel `tiket_masalah` dengan filter `ref_type = 'others'` dan `status NOT IN ('selesai', 'batal', 'hold')` untuk memetakan tiket aktif berprioritas tertinggi pada aset fasum/penunjang non-kavling. Pengujian unit diverifikasi pada `tests/unit/KavlingCompositeQueryTest.php`.
  - **View / CSS / JS:** Visualisasi spasial tiket masalah pada peta Siteplan (`public/assets/js/siteplan/tiket-overlay.js` dan `master.js`) kini mendukung multi-tipe kavling dan others secara atomik dengan safe centroid calculation dan evaluasi visibilitas node `#others{id}` (25 unit tests Jest passing).
  - **Deprecations:** Tidak ada.
- **2026-11-04 [`FEATURE`] — Penyempurnaan Visualisasi Spasial Tiket Masalah pada Siteplan (30 FPS Cap, High-Contrast Stroke, Centroid Badge):**
  - **Routes & Public Contracts:** Tidak berubah.
  - **Controller / Service / Repo / Model:** Tidak berubah.
  - **View / CSS / JS:** Visualisasi spasial tiket masalah aktif pada peta Siteplan (`public/assets/js/siteplan/tiket-overlay.js`) dioptimalkan dengan frame rate cap 30 FPS (`TARGET_FPS = 30`, interval 33.33ms), kontur stroke putih berdenyut (1.0px s.d. 3.5px), serta penambahan warning badge tanda seru `!` di centroid poligon kavling dengan animasi skala dinamis (0.85x s.d. 1.15x). Suite pengujian diperluas menjadi 21 unit tests Jest (`tests/js/siteplan-tiket-overlay.test.js`).
  - **Deprecations:** Tidak ada.
- **2026-11-04 [`FEATURE`] — Integrasi Spasial Tiket Masalah Aktif ke Siteplan Overlay:**
  - **Routes & Public Contracts:** Tidak ada perubahan rute API `/api/tiket-masalah`. Data tiket aktif kavling diintegrasikan ke payload query composite kavling (`POST /siteplan/get/all`).
  - **Controller / Service / Repo / Model:** Konsumsi tabel `tiket_masalah` melalui query builder agregat `KavlingRepository::addActiveTiketSelect` memetakan kavling ke tiket aktif berprioritas tertinggi (`urgent`, `medium`, `normal`, `low`).
  - **View / CSS / JS:** Data tiket masalah aktif divisualisasikan oleh layer `SiteplanTiketOverlay` (`public/assets/js/siteplan/tiket-overlay.js`) pada modul Siteplan.
  - **Deprecations:** Tidak ada.
- `2026-11-04` [`SYNC`] — Sinkronisasi status & inventori modul melalui `module:sync`.

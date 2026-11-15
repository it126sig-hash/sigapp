# Module: Proyek

**Status:** `REFACTORED`  
**Readiness Score:** `100/100`  
**Last Synced:** `2026-11-14 07:17:24` @ `7b1acf6`  

## Purpose
Master data perencanaan proyek perumahan, cluster, tipe bangunan, dan jalan akses.

## Out of scope
- Hal-hal di luar tanggung jawab domain Proyek.

## Invariants
- Modul lain tidak boleh memanipulasi tabel pemilik secara langsung.
- Perubahan finansial/status harus diverifikasi di level Service.

## Database Tables
### Owner Table(s)
- `proyek` *(akses tulis eksklusif hanya untuk modul Proyek)*
- `cluster` *(akses tulis eksklusif hanya untuk modul Proyek)*
- `tipe` *(akses tulis eksklusif hanya untuk modul Proyek)*
- `jalan` *(akses tulis eksklusif hanya untuk modul Proyek)*

### Shared Tables (Dibaca dari modul lain)
- *(Tidak membaca tabel modul lain)*

## Contracts
### A. Public HTTP Endpoints & Routes Inventory
| Method | URI / Pattern | Tipe | Handler | Status Migrasi | Target API / Catatan |
|---|---|---|---|---|---|
| `GET` | `/proyek` | `Explicit` | `ProyekController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/proyek/add` | `Explicit` | `ProyekController::add` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/proyek/edit` | `Explicit` | `ProyekController::edit` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/proyek/remove` | `Explicit` | `ProyekController::remove` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/proyek/set-active` | `Explicit` | `ProyekController::setActive` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/cluster` | `Explicit` | `ClusterController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/cluster/add` | `Explicit` | `ClusterController::add` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/cluster/edit` | `Explicit` | `ClusterController::edit` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/cluster/remove` | `Explicit` | `ClusterController::remove` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/tipe` | `Explicit` | `TipeController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/tipe/add` | `Explicit` | `TipeController::add` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/tipe/edit` | `Explicit` | `TipeController::edit` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/tipe/remove` | `Explicit` | `TipeController::remove` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/jalan` | `Explicit` | `JalanController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/jalan/add` | `Explicit` | `JalanController::add` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/jalan/edit` | `Explicit` | `JalanController::edit` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/jalan/remove` | `Explicit` | `JalanController::remove` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/aksesproyek` | `Explicit` | `\App\Controllers\Web\Proyek\AksesProyekController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/aksesproyek` | `Explicit` | `AksesProyekController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/aksesproyek/meta` | `Explicit` | `AksesProyekController::meta` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/aksesproyek/edit` | `Explicit` | `AksesProyekController::edit` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |

### B. Public PHP Services (Backend Contract Antar-Modul)
#### `ProyekService.php`
- `getDropdownList()`
- `getAll(array $params = [])`
- `getOne(int $idProyek)`
- `add(IncomingRequest $request)`
- `edit(IncomingRequest $request)`
- `getSiteplanList(int $idProyek)`
- `remove(int $idProyek)`
- `getAksesMeta()`
- `getAksesList(array $params = [])`
- `getAksesOne(int $idProyek, bool $raw = false)`
- `searchAksesUsers(string $keyword, bool $raw = false)`
- `updateAkses(array $postData)`
#### `ClusterService.php`
- `getDataTables(array $params)`
- `getAll(array $params)`
- `getOne(int $idCluster)`
- `add(array $postData)`
- `edit(array $postData)`
- `remove(int $idCluster)`
- `isActiveBadge($id, string $texts, string $textf)`
#### `TipeService.php`
- `getAll(array $params)`
- `getDataTables(array $params)`
- `getListKavling(int $idTipe)`
- `getOne(int $idTipe)`
- `add(IncomingRequest $request)`
- `edit(IncomingRequest $request)`
- `remove(int $idTipe)`
#### `JalanService.php`
- `getIndexData()`
- `getAll(array $params)`
- `getDataTables(array $params)`
- `getOne(int $idJalan)`
- `add($input)`
- `edit($input)`
- `remove(int $idJalan)`
#### `ActiveProyekService.php`
- `getActiveId()`
- `getActive()`
- `resolveOnRequest()`
- `bootstrapForRequest()`
- `needsSelection()`
- `dismissSelectionPicker()`
- `resolveAndGet()`
- `getAccessibleList(int $userId)`
- `setActive(int $idProyek)`
- `userCanAccess(int $idProyek, int $userId)`

## File Inventory
### Controllers (10)
- `app/Controllers/Web/Proyek/ProyekController.php` (33 baris, 1 public methods: `index()`)
- `app/Controllers/Api/Proyek/ProyekController.php` (119 baris, 8 public methods: `getAll()`, `getOne($id = null)`, `add()`, `edit($id = null)`, `getSiteplanList($id = null)`, `remove()`, `getAccessible()`, `setActive()`)
- `app/Controllers/Web/Proyek/ClusterController.php` (42 baris, 1 public methods: `index()`)
- `app/Controllers/Api/Proyek/ClusterController.php` (104 baris, 6 public methods: `getDataTable()`, `getAll()`, `getOne($id = null)`, `add()`, `edit($id = null)`, `remove()`)
- `app/Controllers/Web/Proyek/TipeController.php` (42 baris, 1 public methods: `index()`)
- `app/Controllers/Api/Proyek/TipeController.php` (119 baris, 7 public methods: `getAll()`, `getDataTables()`, `getOne($id = null)`, `add()`, `edit($id = null)`, `remove($id = null)`, `getListKavling($id = null)`)
- `app/Controllers/Web/Proyek/JalanController.php` (41 baris, 1 public methods: `index()`)
- `app/Controllers/Api/Proyek/JalanController.php` (110 baris, 6 public methods: `getAll()`, `getDatatable()`, `getOne($id = null)`, `add()`, `edit($id = null)`, `remove()`)
- `app/Controllers/Web/Proyek/AksesProyekController.php` (35 baris, 1 public methods: `index()`)
- `app/Controllers/Api/Proyek/AksesProyekController.php` (121 baris, 5 public methods: `meta()`, `getAll()`, `getOne($id = null)`, `getAkses()`, `edit($id = null)`)

### Services (5)
- `app/Services/Proyek/ProyekService.php` (700 baris, 12 public methods)
- `app/Services/Proyek/ClusterService.php` (274 baris, 7 public methods)
- `app/Services/Proyek/TipeService.php` (497 baris, 7 public methods)
- `app/Services/Proyek/JalanService.php` (289 baris, 7 public methods)
- `app/Services/Proyek/ActiveProyekService.php` (207 baris, 10 public methods)

### Repositories (4)
- `app/Repositories/Proyek/ProyekRepository.php` (261 baris, 13 public methods: `getAll(array $params = [])`, `getById(int $idProyek)`, `getAccessibleForUser(int $userId, bool $isAdmin)`, `getSiteplanUploads(int $idProyek)`, `getAksesProyekList()`, `getAksesProyekById(int $idProyek)`, `searchUsers(string $keyword)`, `updateUserAccess(int $idProyek, string $userIdsCsv)`, `getAksesProyekPaginated(string $search, int $page, int $perPage)`, `getActiveUsersWithGroups(string $search = '')`, `getAuthGroups()`, `existsProyek(int $idProyek)`, `findActiveUserIds(array $userIds)`)
- `app/Repositories/Proyek/ClusterRepository.php` (137 baris, 5 public methods: `getDataTables(array $params)`, `getAll(array $params)`, `getById(int $idCluster)`, `countJalan(int $idCluster)`, `countKavling(int $idCluster)`)
- `app/Repositories/Proyek/TipeRepository.php` (123 baris, 5 public methods: `getAll(array $params)`, `getDataTables(array $params)`, `getGambarKerjaHistory(int $idTipe)`, `ensureConfigShape(string $tipeRumah, string $fill)`, `getListKavling(int $idTipe)`)
- `app/Repositories/Proyek/JalanRepository.php` (132 baris, 3 public methods: `getClusters()`, `getAll(array $params)`, `getDataTables(array $params)`)

### Models (4)
- `app/Models/ProyekModel.php`
- `app/Models/ClusterModel.php`
- `app/Models/TipeModel.php`
- `app/Models/JalanModel.php`

### Views (2 folder)
- `app/Views/proyek` (1 file: `proyek_.php`)
- `app/Views/master` (9 file: `aksesproyek.php`, `cluster.php`, `configcolor.php`, `jalan.php`, `karyawan.php`, `listbank.php`, `pengaturanWarna.php`, `proyek.php`, `tipe.php`)

### JavaScript (2 file)
- `public/assets/js/siteplan/planning.js` (1251 baris)
- `public/assets/js/active-proyek.js` (218 baris)

### Zero-Downtime Shims (14 file)
- `app/Controllers/Proyek.php`
- `app/Controllers/Cluster.php`
- `app/Controllers/Tipe.php`
- `app/Controllers/Jalan.php`
- `app/Controllers/AksesProyek.php`
- `app/Services/ProyekService.php`
- `app/Services/ClusterService.php`
- `app/Services/TipeService.php`
- `app/Services/JalanService.php`
- `app/Services/ActiveProyekService.php`
- `app/Repositories/ProyekRepository.php`
- `app/Repositories/ClusterRepository.php`
- `app/Repositories/TipeRepository.php`
- `app/Repositories/JalanRepository.php`

## Key Functions
- **Controller (`app/Controllers/Api/Proyek/JalanController.php`):** `getRequestData(): array` *(memeriksa header `Content-Type` `application/json` sebelum memanggil `$this->request->getJSON(true)` dan fallback ke `$this->request->getPost()`)*
- **Service (`app/Services/Proyek/ProyekService.php`):** `getDropdownList()`, `getAll(array $params = [])`, `getOne(int $idProyek)`, `add(IncomingRequest $request)`, `edit(IncomingRequest $request)`
- **Service (`app/Services/Proyek/ClusterService.php`):** `getDataTables(array $params)`, `getAll(array $params)`, `getOne(int $idCluster)`, `add(array $postData)`, `edit(array $postData)`
- **Service (`app/Services/Proyek/TipeService.php`):** `getAll(array $params)`, `getDataTables(array $params)`, `getListKavling(int $idTipe)`, `getOne(int $idTipe)`, `add(IncomingRequest $request)`
- **Service (`app/Services/Proyek/JalanService.php`):** `getIndexData()`, `getAll(array $params)`, `getDataTables(array $params)`, `getOne(int $idJalan)`, `add($input)`, `edit($input)`, `requestFields($input): array` *(memeriksa header `Content-Type` `application/json` sebelum memanggil `$input->getJSON(true)` agar kompatibel dengan payload JSON maupun `form-urlencoded`)*
- **Service (`app/Services/Proyek/ActiveProyekService.php`):** `getActiveId()`, `getActive()`, `resolveOnRequest()`, `bootstrapForRequest()`, `needsSelection()`
- **Repository (`app/Repositories/Proyek/ProyekRepository.php`):** `getAll(array $params = [])`, `getById(int $idProyek)`, `getAccessibleForUser(int $userId, bool $isAdmin)`, `getSiteplanUploads(int $idProyek)`, `getAksesProyekList()`
- **Repository (`app/Repositories/Proyek/ClusterRepository.php`):** `getDataTables(array $params)`, `getAll(array $params)`, `getById(int $idCluster)`, `countJalan(int $idCluster)`, `countKavling(int $idCluster)`
- **Repository (`app/Repositories/Proyek/TipeRepository.php`):** `getAll(array $params)`, `getDataTables(array $params)`, `getGambarKerjaHistory(int $idTipe)`, `ensureConfigShape(string $tipeRumah, string $fill)`, `getListKavling(int $idTipe)`
- **Repository (`app/Repositories/Proyek/JalanRepository.php`):** `getClusters()`, `getAll(array $params)`, `getDataTables(array $params)`

## Deprecated & Legacy Shims
- `app/Controllers/Proyek.php` — **DEPRECATED (Zero-Downtime Shim)**: Dipertahankan untuk kompatibilitas mundur; gunakan class modular di namespace `Proyek`.
- `app/Controllers/Cluster.php` — **DEPRECATED (Zero-Downtime Shim)**: Dipertahankan untuk kompatibilitas mundur; gunakan class modular di namespace `Proyek`.
- `app/Controllers/Tipe.php` — **DEPRECATED (Zero-Downtime Shim)**: Dipertahankan untuk kompatibilitas mundur; gunakan class modular di namespace `Proyek`.
- `app/Controllers/Jalan.php` — **DEPRECATED (Zero-Downtime Shim)**: Dipertahankan untuk kompatibilitas mundur; gunakan class modular di namespace `Proyek`.
- `app/Controllers/AksesProyek.php` — **DEPRECATED (Zero-Downtime Shim)**: Dipertahankan untuk kompatibilitas mundur; gunakan class modular di namespace `Proyek`.
- `app/Services/ProyekService.php` — **DEPRECATED (Zero-Downtime Shim)**: Dipertahankan untuk kompatibilitas mundur; gunakan class modular di namespace `Proyek`.
- `app/Services/ClusterService.php` — **DEPRECATED (Zero-Downtime Shim)**: Dipertahankan untuk kompatibilitas mundur; gunakan class modular di namespace `Proyek`.
- `app/Services/TipeService.php` — **DEPRECATED (Zero-Downtime Shim)**: Dipertahankan untuk kompatibilitas mundur; gunakan class modular di namespace `Proyek`.
- `app/Services/JalanService.php` — **DEPRECATED (Zero-Downtime Shim)**: Dipertahankan untuk kompatibilitas mundur; gunakan class modular di namespace `Proyek`.
- `app/Services/ActiveProyekService.php` — **DEPRECATED (Zero-Downtime Shim)**: Dipertahankan untuk kompatibilitas mundur; gunakan class modular di namespace `Proyek`.
- `app/Repositories/ProyekRepository.php` — **DEPRECATED (Zero-Downtime Shim)**: Dipertahankan untuk kompatibilitas mundur; gunakan class modular di namespace `Proyek`.
- `app/Repositories/ClusterRepository.php` — **DEPRECATED (Zero-Downtime Shim)**: Dipertahankan untuk kompatibilitas mundur; gunakan class modular di namespace `Proyek`.
- `app/Repositories/TipeRepository.php` — **DEPRECATED (Zero-Downtime Shim)**: Dipertahankan untuk kompatibilitas mundur; gunakan class modular di namespace `Proyek`.
- `app/Repositories/JalanRepository.php` — **DEPRECATED (Zero-Downtime Shim)**: Dipertahankan untuk kompatibilitas mundur; gunakan class modular di namespace `Proyek`.

## Readiness Checklist
- [x] Has Service Layer
- [x] Has Repository Layer
- [x] Web & API Controllers Separated
- [x] Explicit Routes Defined
- [x] Zero Controller Layer Violations
- [x] MODULE.md Initialized

## Dead Code Candidates
_Jalankan `php spark module:deadcode Proyek` untuk mengaudit dead code kandidat._

## Notes
- Modul diinisialisasi melalui tooling otomatis.

## Change Log
- `2026-11-14` [`BUGFIX`] — **Perbaikan Parsing Payload Form-Urlencoded pada Master Jalan (`/api/jalan/*`)**:
  - **Routes & Public Contracts:** Tidak ada perubahan rute; kontrak `JalanService::add($input)` & `JalanService::edit($input)` serta `/api/jalan/*` tetap kompatibel untuk payload JSON maupun `application/x-www-form-urlencoded`.
  - **Controller / Service / Repo / Model:**
    - `App\Services\Proyek\JalanService::requestFields($input): array` kini memeriksa header `Content-Type` (`application/json`) sebelum memanggil `$input->getJSON(true)` agar request `form-urlencoded` dari `app/Views/master/jalan.php` tidak melempar `HTTPException` (`Failed to parse JSON string`).
    - `App\Controllers\Api\Proyek\JalanController::getRequestData(): array` kini memeriksa header `Content-Type` (`application/json`) sebelum memanggil `$this->request->getJSON(true)` dan fallback ke `$this->request->getPost()`.
  - **View / CSS / JS:** Tidak ada perubahan.
  - **Deprecations:** Tidak ada.
- `2026-11-11` [`REFACTOR`] — **Migrasi Full JSON (RAW_JSON_READY) & Dual-Mode Modul Proyek**:
  - Menghapus 100% HTML debt (tag `<button>`, `<span class="badge">`, `<img src="...">`) dari `ProyekService`, `ClusterService`, `TipeService`, dan `JalanService`.
  - Menstandarisasi response `Api\Proyek\*Controller` menggunakan envelope `BaseApiController` (`$this->success()`, `$this->error()`, `$this->successCreated()`).
  - Mengganti seluruh `throw new PageNotFoundException()` (yang melempar HTML 404) dengan JSON response 404 pada API controller dan shims.
  - Memperbaiki seluruh layer violations (`new ProyekModel()` dan `new KavlingModel()`) sehingga Readiness Score mencapai **100/100**.
  - Mengimplementasikan dukungan header `X-Project-Id` pada `ActiveProyekService` (Wave 0 prerequisite `W0-4`).
  - Mengaktifkan Dual-Mode pada seluruh Web Controller (`Accept: application/json` vs `Accept: text/html`) dan memindahkan rendering tombol/badge legacy ke client-side DataTables `columns.render` (Zero-Downtime terverifikasi).
- `2026-11-11` [`FEATURE`] — **Dukungan Token Query Param pada Auth Asset (`/files/*`)**:
  - `DeviceLoginFilter` diperbarui untuk menerima token autentikasi Bearer via query parameter `?token=` dan `?access_token=`.
  - Mengembalikan `401 JSON` untuk route `/files/*` ketika unauthenticated (tanpa me-redirect ke login page HTML).
  - Mengizinkan tag native `<img>` dan tautan dokumen pada Web SPA (`sigapp-vue`) memuat asset privat secara aman tanpa memerlukan session cookie backend legacy.
- `2026-11-12` [`FEATURE`] — **Dukungan Payload JSON Murni & Penguatan Validasi Cluster (`/api/cluster/*`)**:
  - `Api\Proyek\ClusterController` diperbarui dengan helper `$this->getRequestData()` yang secara mulus mengekstrak `$this->request->getJSON(true) ?: (array) $this->request->getPost()` pada method `add()`, `edit()`, dan `remove()`, memungkinkan konsumsi payload JSON murni langsung dari Axios (`sigapp-vue`) maupun form URL encoded legacy.
  - `ClusterService` diperbarui dengan normalisasi input dual-case (`camelCase` dan `snake_case`), penguatan validasi `nama_cluster` menjadi `required|min_length[2]|max_length[255]` guna mencegah row dummy NULL, serta fallback otomatis `id_proyek` ke active proyek pengguna (`resolve_active_proyek_id(null)`).
  - Lulus verifikasi Playwright E2E Master Cluster di Vue SPA dan uji Zero-Downtime web legacy di `http://localhost/sigapp.dev/cluster`.
- `2026-11-12` [`FEATURE`] — **Dukungan Payload JSON Murni, Validasi Ketat & Rich Unit Stats Jalan (`/api/jalan/*`)**:
  - `Api\Proyek\JalanController` diperbarui dengan helper `$this->getRequestData()` pada method `add()`, `edit()`, dan `remove()`, memungkinkan konsumsi payload JSON murni langsung dari Axios (`sigapp-vue`) maupun form URL encoded legacy.
  - `JalanService` diperbarui dengan normalisasi input dual-case (`camelCase` dan `snake_case`), validasi ketat `id_cluster` (`required|is_natural_no_zero`) dan `nama_jalan` (`required|min_length[2]|max_length[255]`), penyertaan data analitik kavling lengkap (`total_kavling`, `total_akad`, `total_booking`, `total_belum_terjual`, dan `nama_proyek`) pada format raw `getAll()`, serta detail relasi cluster & proyek pada `getOne()`.
  - Lulus verifikasi Playwright E2E Master Jalan di Vue SPA dan uji Zero-Downtime web legacy di `http://localhost/sigapp.dev/jalan`.
- `2026-11-12` [`FEATURE`] — **Dukungan Payload JSON Murni, Normalisasi Input & Access URLs Tipe Rumah (`/api/tipe/*`)**:
  - `Api\Proyek\TipeController` diperbarui dengan helper `$this->getRequestData()` pada method `add()`, `edit()`, dan `remove()`, serta mendukung parameter ID via URL route maupun payload body.
  - `TipeService` diperbarui dengan normalisasi input dual-case (`camelCase` dan `snake_case`), upload berkas dibuat opsional saat pendaftaran awal/edit dengan validasi ketat jika ada berkas diunggah (MIME type image/pdf maks 12MB), fallback otomatis `id_proyek` ke active proyek pengguna (`resolve_active_proyek_id(null)`), serta penyertaan `gambar_tipe_access_url`, `gambar_denah_access_url`, dan `gambar_kerja_access_url` via `FileAccessService::accessUrl()` pada format raw.
  - Format legacy non-raw `getDataTables()` tetap menghasilkan tepat 9 kolom numerik terstruktur tanpa gangguan.
  - Lulus verifikasi Playwright E2E siklus CRUD lengkap Master Tipe Rumah, paginasi, lazy loading gambar, inspeksi detail kavling di Vue SPA, serta uji Zero-Downtime web legacy di `http://localhost/sigapp.dev/tipe`.

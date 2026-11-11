# Module: Proyek

**Status:** `REFACTORED`  
**Readiness Score:** `100/100`  
**Last Synced:** `2026-11-11 19:33:10` @ `e658eba`  

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
- `getAksesList(array $params = [])`
- `getAksesOne(int $idProyek)`
- `searchAksesUsers(string $keyword)`
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
- `add(IncomingRequest $request)`
- `edit(IncomingRequest $request)`
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
- `app/Controllers/Api/Proyek/ProyekController.php` (117 baris, 8 public methods: `getAll()`, `getOne($id = null)`, `add()`, `edit($id = null)`, `getSiteplanList($id = null)`, `remove()`, `getAccessible()`, `setActive()`)
- `app/Controllers/Web/Proyek/ClusterController.php` (42 baris, 1 public methods: `index()`)
- `app/Controllers/Api/Proyek/ClusterController.php` (93 baris, 6 public methods: `getDataTable()`, `getAll()`, `getOne($id = null)`, `add()`, `edit($id = null)`, `remove()`)
- `app/Controllers/Web/Proyek/TipeController.php` (42 baris, 1 public methods: `index()`)
- `app/Controllers/Api/Proyek/TipeController.php` (103 baris, 7 public methods: `getAll()`, `getDataTables()`, `getOne($id = null)`, `add()`, `edit($id = null)`, `remove()`, `getListKavling($id = null)`)
- `app/Controllers/Web/Proyek/JalanController.php` (41 baris, 1 public methods: `index()`)
- `app/Controllers/Api/Proyek/JalanController.php` (92 baris, 6 public methods: `getAll()`, `getDatatable()`, `getOne($id = null)`, `add()`, `edit($id = null)`, `remove()`)
- `app/Controllers/Web/Proyek/AksesProyekController.php` (35 baris, 1 public methods: `index()`)
- `app/Controllers/Api/Proyek/AksesProyekController.php` (63 baris, 4 public methods: `getAll()`, `getOne($id = null)`, `getAkses()`, `edit($id = null)`)

### Services (5)
- `app/Services/Proyek/ProyekService.php` (423 baris, 11 public methods)
- `app/Services/Proyek/ClusterService.php` (264 baris, 7 public methods)
- `app/Services/Proyek/TipeService.php` (460 baris, 7 public methods)
- `app/Services/Proyek/JalanService.php` (234 baris, 7 public methods)
- `app/Services/Proyek/ActiveProyekService.php` (207 baris, 10 public methods)

### Repositories (4)
- `app/Repositories/Proyek/ProyekRepository.php` (125 baris, 8 public methods: `getAll(array $params = [])`, `getById(int $idProyek)`, `getAccessibleForUser(int $userId, bool $isAdmin)`, `getSiteplanUploads(int $idProyek)`, `getAksesProyekList()`, `getAksesProyekById(int $idProyek)`, `searchUsers(string $keyword)`, `updateUserAccess(int $idProyek, string $userIdsCsv)`)
- `app/Repositories/Proyek/ClusterRepository.php` (137 baris, 5 public methods: `getDataTables(array $params)`, `getAll(array $params)`, `getById(int $idCluster)`, `countJalan(int $idCluster)`, `countKavling(int $idCluster)`)
- `app/Repositories/Proyek/TipeRepository.php` (117 baris, 5 public methods: `getAll(array $params)`, `getDataTables(array $params)`, `getGambarKerjaHistory(int $idTipe)`, `ensureConfigShape(string $tipeRumah, string $fill)`, `getListKavling(int $idTipe)`)
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
- **Service (`app/Services/Proyek/ProyekService.php`):** `getDropdownList()`, `getAll(array $params = [])`, `getOne(int $idProyek)`, `add(IncomingRequest $request)`, `edit(IncomingRequest $request)`
- **Service (`app/Services/Proyek/ClusterService.php`):** `getDataTables(array $params)`, `getAll(array $params)`, `getOne(int $idCluster)`, `add(array $postData)`, `edit(array $postData)`
- **Service (`app/Services/Proyek/TipeService.php`):** `getAll(array $params)`, `getDataTables(array $params)`, `getListKavling(int $idTipe)`, `getOne(int $idTipe)`, `add(IncomingRequest $request)`
- **Service (`app/Services/Proyek/JalanService.php`):** `getIndexData()`, `getAll(array $params)`, `getDataTables(array $params)`, `getOne(int $idJalan)`, `add(IncomingRequest $request)`
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

## Arsitektur Dual-Mode & Panduan Pembersihan Post-SPA
Untuk mendukung masa transisi migrasi ke Vue SPA (`docs/VUE_MIGRATION_PROGRESS.md`) tanpa merusak view legacy yang sedang aktif (Zero-Downtime), modul Proyek menerapkan arsitektur **Dual-Mode**:
1. **Web Controllers (`App\Controllers\Web\Proyek\*`)**:
   - Mengecek method `$this->wantsJson()`.
   - Jika request datang dengan header `Accept: application/json` atau `X-Client-Platform: vue-spa`, controller mengembalikan respons JSON murni (`$this->response->setJSON(...)`).
   - Jika request dibuka langsung oleh browser legacy (`Accept: text/html`), controller merender view legacy `view('template', $data)`.
2. **Client-side Rendering pada View Legacy (`app/Views/master/*`)**:
   - Seluruh tag HTML tombol aksi (`<button>`), badge status (`<span>`), dan gambar/thumbnail telah dihapus dari Service backend.
   - View legacy menggunakan callback client-side DataTables (`columns.render`) untuk merender elemen UI tersebut secara mandiri.
3. **Panduan Pembersihan Backend Setelah Cutover SPA Selesai**:
   Ketika Vue SPA sudah 100% dideploy dan legacy web browser routes tidak lagi diakses langsung:
   - **Hapus Web Controllers**: `app/Controllers/Web/Proyek/{ProyekController,ClusterController,TipeController,JalanController,AksesProyekController}.php`.
   - **Hapus Legacy Views**: `app/Views/master/{proyek,cluster,tipe,jalan,aksesproyek}.php`.
   - **Hapus Shims**: `app/Controllers/{Proyek,Cluster,Tipe,Jalan,AksesProyek}.php`.
   - **Rampingkan Routes**: Hapus rute web browser legacy di `app/Config/Routes.php` (sisakan grup `api/proyek`, `api/cluster`, `api/tipe`, `api/jalan`, `api/aksesproyek`).
   - **Rampingkan Services**: Hapus fallback parameter indexed array pada method `getDataTables()` di `ClusterService`, `TipeService`, `JalanService`, sehingga murni mengembalikan structured object list.

## Change Log
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

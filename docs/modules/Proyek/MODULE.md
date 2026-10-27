# Module: Proyek

**Status:** `REFACTORED`  
**Readiness Score:** `90/100`  
**Last Synced:** `2026-10-23 13:52:12` @ `ad0c900`  

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
| `POST` | `/api/proyek/all` | `Explicit` | `ProyekController::getAll` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/proyek/getall` | `Explicit` | `ProyekController::getAll` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/proyek/getone` | `Explicit` | `ProyekController::getOne` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/proyek/add` | `Explicit` | `ProyekController::add` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/proyek/edit` | `Explicit` | `ProyekController::edit` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/proyek/remove` | `Explicit` | `ProyekController::remove` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/proyek/siteplan-list` | `Explicit` | `ProyekController::getSiteplanList` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/proyek/siteplan-list` | `Explicit` | `ProyekController::getSiteplanList` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/proyek/accessible` | `Explicit` | `ProyekController::getAccessible` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/proyek/accessible` | `Explicit` | `ProyekController::getAccessible` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/proyek/set-active` | `Explicit` | `ProyekController::setActive` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/cluster` | `Explicit` | `ClusterController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/cluster/datatable` | `Explicit` | `ClusterController::getDataTable` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/cluster/getdatatable` | `Explicit` | `ClusterController::getDataTable` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/cluster/all` | `Explicit` | `ClusterController::getAll` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/cluster/getall` | `Explicit` | `ClusterController::getAll` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/cluster/getone` | `Explicit` | `ClusterController::getOne` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/cluster/add` | `Explicit` | `ClusterController::add` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/cluster/edit` | `Explicit` | `ClusterController::edit` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/cluster/remove` | `Explicit` | `ClusterController::remove` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/tipe` | `Explicit` | `TipeController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/tipe/datatables` | `Explicit` | `TipeController::getDataTables` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/tipe/getdatatables` | `Explicit` | `TipeController::getDataTables` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/tipe/all` | `Explicit` | `TipeController::getAll` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/tipe/getall` | `Explicit` | `TipeController::getAll` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/tipe/getone` | `Explicit` | `TipeController::getOne` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/tipe/add` | `Explicit` | `TipeController::add` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/tipe/edit` | `Explicit` | `TipeController::edit` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/tipe/remove` | `Explicit` | `TipeController::remove` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/tipe/list-kavling` | `Explicit` | `TipeController::getListKavling` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/tipe/getlistkavling` | `Explicit` | `TipeController::getListKavling` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/jalan` | `Explicit` | `JalanController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/jalan/datatable` | `Explicit` | `JalanController::getDatatable` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/jalan/getdatatable` | `Explicit` | `JalanController::getDatatable` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/jalan/all` | `Explicit` | `JalanController::getAll` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/jalan/getall` | `Explicit` | `JalanController::getAll` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/jalan/getone` | `Explicit` | `JalanController::getOne` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/jalan/add` | `Explicit` | `JalanController::add` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/jalan/edit` | `Explicit` | `JalanController::edit` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/jalan/remove` | `Explicit` | `JalanController::remove` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/aksesproyek` | `Explicit` | `\App\Controllers\Web\Proyek\AksesProyekController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/aksesproyek` | `Explicit` | `AksesProyekController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/aksesproyek/all` | `Explicit` | `AksesProyekController::getAll` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/aksesproyek/getone` | `Explicit` | `AksesProyekController::getOne` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/aksesproyek/search-users` | `Explicit` | `AksesProyekController::getAkses` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/aksesproyek/edit` | `Explicit` | `AksesProyekController::edit` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |

### B. Public PHP Services (Backend Contract Antar-Modul)
#### `ProyekService.php`
- `getAll(array $params = [])`
- `getOne(int $idProyek)`
- `add(IncomingRequest $request)`
- `edit(IncomingRequest $request)`
- `getSiteplanList(int $idProyek)`
- `remove(int $idProyek)`
- `getAksesList()`
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
- `app/Controllers/Web/Proyek/ProyekController.php` (18 baris, 1 public methods)
- `app/Controllers/Api/Proyek/ProyekController.php` (107 baris, 8 public methods)
- `app/Controllers/Web/Proyek/ClusterController.php` (27 baris, 1 public methods)
- `app/Controllers/Api/Proyek/ClusterController.php` (87 baris, 6 public methods)
- `app/Controllers/Web/Proyek/TipeController.php` (30 baris, 1 public methods)
- `app/Controllers/Api/Proyek/TipeController.php` (100 baris, 7 public methods)
- `app/Controllers/Web/Proyek/JalanController.php` (26 baris, 1 public methods)
- `app/Controllers/Api/Proyek/JalanController.php` (88 baris, 6 public methods)
- `app/Controllers/Web/Proyek/AksesProyekController.php` (20 baris, 1 public methods)
- `app/Controllers/Api/Proyek/AksesProyekController.php` (69 baris, 4 public methods)

### Services (5)
- `app/Services/Proyek/ProyekService.php` (418 baris)
- `app/Services/Proyek/ClusterService.php` (248 baris)
- `app/Services/Proyek/TipeService.php` (428 baris)
- `app/Services/Proyek/JalanService.php` (210 baris)
- `app/Services/Proyek/ActiveProyekService.php` (190 baris)

### Repositories (4)
- `app/Repositories/Proyek/ProyekRepository.php` (125 baris)
- `app/Repositories/Proyek/ClusterRepository.php` (137 baris)
- `app/Repositories/Proyek/TipeRepository.php` (107 baris)
- `app/Repositories/Proyek/JalanRepository.php` (132 baris)

### Models (4)
- `app/Models/ProyekModel.php`
- `app/Models/ClusterModel.php`
- `app/Models/TipeModel.php`
- `app/Models/JalanModel.php`

### Views (2 folder)
- `app/Views/proyek` (1 file)
- `app/Views/master` (9 file)

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

## Readiness Checklist
- [x] Has Service Layer
- [x] Has Repository Layer
- [x] Web & API Controllers Separated
- [x] Explicit Routes Defined
- [ ] Zero Controller Layer Violations
- [x] MODULE.md Initialized

> **Layer Violations Detected:**
> - `app/Controllers/Web/Proyek/ClusterController.php`: new ProyekModel()
> - `app/Controllers/Web/Proyek/TipeController.php`: new ProyekModel()
> - `app/Controllers/Api/Proyek/TipeController.php`: new KavlingModel()

## Dead Code Candidates
_Jalankan `php spark module:deadcode Proyek` untuk mengaudit dead code kandidat._

## Notes
- Modul diinisialisasi melalui tooling otomatis.

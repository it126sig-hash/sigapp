# Module: Proyek

**Status:** `REFACTORED`  
**Readiness Score:** `100/100`  
**Last Synced:** `2026-10-12 15:33:40` @ `e8e5ef5`  

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
| `GET` | `/aksesproyek` | `Explicit` | `\App\Controllers\Web\AksesProyekController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `proyek` | `Explicit` | `ProyekController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `all` | `Explicit` | `ProyekController::getAll` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `getall` | `Explicit` | `ProyekController::getAll` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `getone` | `Explicit` | `ProyekController::getOne` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `add` | `Explicit` | `ProyekController::add` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `edit` | `Explicit` | `ProyekController::edit` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `remove` | `Explicit` | `ProyekController::remove` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `siteplan-list` | `Explicit` | `ProyekController::getSiteplanList` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `siteplan-list` | `Explicit` | `ProyekController::getSiteplanList` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `accessible` | `Explicit` | `ProyekController::getAccessible` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `accessible` | `Explicit` | `ProyekController::getAccessible` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `set-active` | `Explicit` | `ProyekController::setActive` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `all` | `Explicit` | `AksesProyekController::getAll` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `getone` | `Explicit` | `AksesProyekController::getOne` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `search-users` | `Explicit` | `AksesProyekController::getAkses` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `edit` | `Explicit` | `AksesProyekController::edit` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/aksesproyek` | `Explicit` | `\App\Controllers\Web\AksesProyekController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `proyek` | `Explicit` | `ProyekController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `all` | `Explicit` | `ProyekController::getAll` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `getall` | `Explicit` | `ProyekController::getAll` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `getone` | `Explicit` | `ProyekController::getOne` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `add` | `Explicit` | `ProyekController::add` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `edit` | `Explicit` | `ProyekController::edit` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `remove` | `Explicit` | `ProyekController::remove` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `siteplan-list` | `Explicit` | `ProyekController::getSiteplanList` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `siteplan-list` | `Explicit` | `ProyekController::getSiteplanList` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `accessible` | `Explicit` | `ProyekController::getAccessible` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `accessible` | `Explicit` | `ProyekController::getAccessible` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `set-active` | `Explicit` | `ProyekController::setActive` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `all` | `Explicit` | `AksesProyekController::getAll` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `getone` | `Explicit` | `AksesProyekController::getOne` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `search-users` | `Explicit` | `AksesProyekController::getAkses` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `edit` | `Explicit` | `AksesProyekController::edit` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `cluster` | `Explicit` | `ClusterController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `datatable` | `Explicit` | `ClusterController::getDataTable` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `getdatatable` | `Explicit` | `ClusterController::getDataTable` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `all` | `Explicit` | `ClusterController::getAll` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `getall` | `Explicit` | `ClusterController::getAll` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `getone` | `Explicit` | `ClusterController::getOne` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `add` | `Explicit` | `ClusterController::add` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `edit` | `Explicit` | `ClusterController::edit` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `remove` | `Explicit` | `ClusterController::remove` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `cluster` | `Explicit` | `ClusterController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `datatable` | `Explicit` | `ClusterController::getDataTable` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `getdatatable` | `Explicit` | `ClusterController::getDataTable` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `all` | `Explicit` | `ClusterController::getAll` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `getall` | `Explicit` | `ClusterController::getAll` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `getone` | `Explicit` | `ClusterController::getOne` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `add` | `Explicit` | `ClusterController::add` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `edit` | `Explicit` | `ClusterController::edit` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `remove` | `Explicit` | `ClusterController::remove` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `tipe` | `Explicit` | `TipeController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `datatables` | `Explicit` | `TipeController::getDataTables` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `getdatatables` | `Explicit` | `TipeController::getDataTables` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `all` | `Explicit` | `TipeController::getAll` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `getall` | `Explicit` | `TipeController::getAll` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `getone` | `Explicit` | `TipeController::getOne` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `add` | `Explicit` | `TipeController::add` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `edit` | `Explicit` | `TipeController::edit` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `remove` | `Explicit` | `TipeController::remove` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `list-kavling` | `Explicit` | `TipeController::getListKavling` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `getlistkavling` | `Explicit` | `TipeController::getListKavling` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `tipe` | `Explicit` | `TipeController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `datatables` | `Explicit` | `TipeController::getDataTables` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `getdatatables` | `Explicit` | `TipeController::getDataTables` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `all` | `Explicit` | `TipeController::getAll` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `getall` | `Explicit` | `TipeController::getAll` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `getone` | `Explicit` | `TipeController::getOne` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `add` | `Explicit` | `TipeController::add` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `edit` | `Explicit` | `TipeController::edit` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `remove` | `Explicit` | `TipeController::remove` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `list-kavling` | `Explicit` | `TipeController::getListKavling` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `getlistkavling` | `Explicit` | `TipeController::getListKavling` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `jalan` | `Explicit` | `JalanController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `datatable` | `Explicit` | `JalanController::getDatatable` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `getdatatable` | `Explicit` | `JalanController::getDatatable` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `all` | `Explicit` | `JalanController::getAll` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `getall` | `Explicit` | `JalanController::getAll` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `getone` | `Explicit` | `JalanController::getOne` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `add` | `Explicit` | `JalanController::add` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `edit` | `Explicit` | `JalanController::edit` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `remove` | `Explicit` | `JalanController::remove` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `jalan` | `Explicit` | `JalanController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `datatable` | `Explicit` | `JalanController::getDatatable` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `getdatatable` | `Explicit` | `JalanController::getDatatable` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `all` | `Explicit` | `JalanController::getAll` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `getall` | `Explicit` | `JalanController::getAll` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `getone` | `Explicit` | `JalanController::getOne` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `add` | `Explicit` | `JalanController::add` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `edit` | `Explicit` | `JalanController::edit` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `remove` | `Explicit` | `JalanController::remove` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/aksesproyek` | `Explicit` | `\App\Controllers\Web\AksesProyekController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `all` | `Explicit` | `AksesProyekController::getAll` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `getone` | `Explicit` | `AksesProyekController::getOne` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `search-users` | `Explicit` | `AksesProyekController::getAkses` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `edit` | `Explicit` | `AksesProyekController::edit` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/aksesproyek` | `Explicit` | `\App\Controllers\Web\AksesProyekController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `all` | `Explicit` | `AksesProyekController::getAll` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `getone` | `Explicit` | `AksesProyekController::getOne` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `search-users` | `Explicit` | `AksesProyekController::getAkses` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `edit` | `Explicit` | `AksesProyekController::edit` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |

### B. Public PHP Services (Backend Contract Antar-Modul)
#### `ProyekService.php`
- `getAll(array $params = [])`
- `getOne(int $idProyek)`
- `add(IncomingRequest $request)`
- `edit(IncomingRequest $request)`
- `getSiteplanList(int $idProyek)`
- `remove(int $idProyek)`
- `requestFields(IncomingRequest $request)`
- `validate(array $fields, array $fileRules)`
- `siteplanRules(string $fieldName, int $maxDimension)`
- `logoRules(string $fieldName)`
- `storeSiteplanUpload(IncomingRequest $request, string $fieldName)`
- `storeImage(IncomingRequest $request, string $fieldName, string $logicalDir)`
- `shouldUpload(IncomingRequest $request, string $fieldName, string $flagName)`
- `hasUploadedFile(IncomingRequest $request, string $fieldName)`
- `getAksesList()`
- _dan 3 method lainnya..._
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
- `requestFields(IncomingRequest $request)`
- `validate(array $fields, bool $requireFiles, IncomingRequest $request)`
- `storeUploadedFile(IncomingRequest $request, string $fieldName, array $fields, ?object $proyek, ?int $idTipe = null)`
- `hasUploadedFile(IncomingRequest $request, string $fieldName)`
- `ensureConfigShape(array $fields)`
- `generateSimilarColor(string $baseColor, int $variation = 20)`
- `tipeFieldForUpload(string $fieldName)`
- `appendFileUrls(object $row, string $prefix, $id)`
- `statusBadge($id, string $texts, string $textf)`
#### `JalanService.php`
- `getIndexData()`
- `getAll(array $params)`
- `getDataTables(array $params)`
- `getOne(int $idJalan)`
- `add(IncomingRequest $request)`
- `edit(IncomingRequest $request)`
- `remove(int $idJalan)`
- `requestFields(IncomingRequest $request)`
- `validate(array $fields)`
- `actionButtons(int $idJalan)`
#### `ActiveProyekService.php`
- `getActiveId()`
- `getActive()`
- `resolveOnRequest()`
- `bootstrapForRequest()`
- `needsSelection()`
- `dismissSelectionPicker()`
- `resolveAndGet()`
- `ensureDefaultActive(int $userId)`
- `getAccessibleList(int $userId)`
- `setActive(int $idProyek)`
- `userCanAccess(int $idProyek, int $userId)`
- `userCanAccessProyek(?object $proyek, int $userId)`
- `enrichProyek(?object $proyek)`

## File Inventory
### Controllers (10)
- `app/Controllers/Web/ProyekController.php` (18 baris, 1 public methods)
- `app/Controllers/Api/ProyekController.php` (106 baris, 8 public methods)
- `app/Controllers/Web/ClusterController.php` (27 baris, 1 public methods)
- `app/Controllers/Api/ClusterController.php` (86 baris, 6 public methods)
- `app/Controllers/Web/TipeController.php` (30 baris, 1 public methods)
- `app/Controllers/Api/TipeController.php` (99 baris, 7 public methods)
- `app/Controllers/Web/JalanController.php` (26 baris, 1 public methods)
- `app/Controllers/Api/JalanController.php` (87 baris, 6 public methods)
- `app/Controllers/Web/AksesProyekController.php` (20 baris, 1 public methods)
- `app/Controllers/Api/AksesProyekController.php` (68 baris, 4 public methods)

### Services (5)
- `app/Services/ProyekService.php` (419 baris)
- `app/Services/ClusterService.php` (247 baris)
- `app/Services/TipeService.php` (426 baris)
- `app/Services/JalanService.php` (210 baris)
- `app/Services/ActiveProyekService.php` (189 baris)

### Repositories (4)
- `app/Repositories/ProyekRepository.php` (126 baris)
- `app/Repositories/ClusterRepository.php` (137 baris)
- `app/Repositories/TipeRepository.php` (107 baris)
- `app/Repositories/JalanRepository.php` (132 baris)

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

## Readiness Checklist
- [x] Has Service Layer
- [x] Has Repository Layer
- [x] Web & API Controllers Separated
- [x] Explicit Routes Defined
- [x] MODULE.md Initialized

## Dead Code Candidates
_Jalankan `php spark module:deadcode Proyek` untuk mengaudit dead code kandidat._

## Notes
- Modul diinisialisasi melalui tooling otomatis.

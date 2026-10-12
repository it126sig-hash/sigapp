# Module: Proyek

**Status:** `REFACTORED`  
**Readiness Score:** `80/100`  
**Last Synced:** `2026-10-12 14:38:40` @ `be98758`  

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
| `POST` | `/getAkases` | `Explicit` | `AksesProyek::getAkses` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `/aksesproyek` | `Explicit` | `AksesProyek::index` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `proyek/get/all` | `Explicit` | `Proyek::getAll` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `tipe/get/all` | `Explicit` | `Tipe::getAll` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `add_jalan` | `Explicit` | `ProduksiController::add_jalan` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `ANY` | `/proyek/getone` | `AutoRoute` | `Proyek::getOne` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/proyek/add` | `AutoRoute` | `Proyek::add` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/proyek/edit` | `AutoRoute` | `Proyek::edit` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/proyek/getsiteplanlist` | `AutoRoute` | `Proyek::getSiteplanList` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/proyek/remove` | `AutoRoute` | `Proyek::remove` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/proyek/getaccessible` | `AutoRoute` | `Proyek::getAccessible` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/proyek/setactive` | `AutoRoute` | `Proyek::setActive` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/proyek/json` | `AutoRoute` | `Proyek::json` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/cluster/index` | `AutoRoute` | `Cluster::index` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/cluster/getdatatable` | `AutoRoute` | `Cluster::getDataTable` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/cluster/getall` | `AutoRoute` | `Cluster::getAll` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/cluster/getone` | `AutoRoute` | `Cluster::getOne` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/cluster/add` | `AutoRoute` | `Cluster::add` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/cluster/edit` | `AutoRoute` | `Cluster::edit` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/cluster/remove` | `AutoRoute` | `Cluster::remove` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/cluster/if_where` | `AutoRoute` | `Cluster::if_where` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/cluster/is_active` | `AutoRoute` | `Cluster::is_active` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/tipe/index` | `AutoRoute` | `Tipe::index` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/tipe/getdatatables` | `AutoRoute` | `Tipe::getDataTables` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/tipe/getone` | `AutoRoute` | `Tipe::getOne` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/tipe/add` | `AutoRoute` | `Tipe::add` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/tipe/edit` | `AutoRoute` | `Tipe::edit` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/tipe/remove` | `AutoRoute` | `Tipe::remove` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/tipe/getlistkavling` | `AutoRoute` | `Tipe::getListKavling` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/tipe/json` | `AutoRoute` | `Tipe::json` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/jalan/index` | `AutoRoute` | `Jalan::index` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/jalan/getall` | `AutoRoute` | `Jalan::getAll` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/jalan/getdatatable` | `AutoRoute` | `Jalan::getDatatable` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/jalan/getone` | `AutoRoute` | `Jalan::getOne` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/jalan/add` | `AutoRoute` | `Jalan::add` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/jalan/edit` | `AutoRoute` | `Jalan::edit` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/jalan/remove` | `AutoRoute` | `Jalan::remove` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/jalan/json` | `AutoRoute` | `Jalan::json` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |

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
### Controllers (4)
- `app/Controllers/Proyek.php` (118 baris, 10 public methods)
- `app/Controllers/Cluster.php` (341 baris, 9 public methods)
- `app/Controllers/Tipe.php` (103 baris, 9 public methods)
- `app/Controllers/Jalan.php` (89 baris, 8 public methods)

### Services (4)
- `app/Services/ProyekService.php` (354 baris)
- `app/Services/TipeService.php` (426 baris)
- `app/Services/JalanService.php` (210 baris)
- `app/Services/ActiveProyekService.php` (189 baris)

### Repositories (3)
- `app/Repositories/ProyekRepository.php` (72 baris)
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
- [ ] Web & API Controllers Separated
- [x] Explicit Routes Defined
- [x] MODULE.md Initialized

## Dead Code Candidates
_Jalankan `php spark module:deadcode Proyek` untuk mengaudit dead code kandidat._

## Notes
- Modul diinisialisasi melalui tooling otomatis.

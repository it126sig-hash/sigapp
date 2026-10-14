# Module: Siteplan

**Status:** `REFACTORED`  
**Readiness Score:** `100/100`  
**Last Synced:** `2026-10-14 10:07:46` @ `bb751f9`  

## Purpose
Visualisasi peta interaktif kavling, filter multi-departemen, dan status spasial visual.

## Out of scope
- Hal-hal di luar tanggung jawab domain Siteplan.

## Invariants
- Modul lain tidak boleh memanipulasi tabel pemilik secara langsung.
- Perubahan finansial/status harus diverifikasi di level Service.

## Database Tables
### Owner Table(s)
- `siteplan_upload` *(akses tulis eksklusif hanya untuk modul Siteplan)*
- `target_siteplan` *(akses tulis eksklusif hanya untuk modul Siteplan)*
- `target_siteplan_kavling` *(akses tulis eksklusif hanya untuk modul Siteplan)*
- `target_siteplan_history` *(akses tulis eksklusif hanya untuk modul Siteplan)*

### Shared Tables (Dibaca dari modul lain)
- `kavling`
- `mkdt`
- `keuangan`
- `produksi`
- `legal`

## Contracts
### A. Public HTTP Endpoints & Routes Inventory
| Method | URI / Pattern | Tipe | Handler | Status Migrasi | Target API / Catatan |
|---|---|---|---|---|---|
| `GET` | `siteplan-list` | `Explicit` | `ProyekController::getSiteplanList` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `siteplan-list` | `Explicit` | `ProyekController::getSiteplanList` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `siteplan-menu/list` | `Explicit` | `MenuSettingController::siteplanMenuList` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `siteplan-menu/get` | `Explicit` | `MenuSettingController::siteplanMenuGet` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `siteplan-menu/save` | `Explicit` | `MenuSettingController::siteplanMenuSave` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `siteplan-menu/toggle` | `Explicit` | `MenuSettingController::siteplanMenuToggle` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `siteplan-menu/list` | `Explicit` | `\App\Controllers\Api\Settings\MenuSettingController::siteplanMenuList` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `siteplan-menu/get` | `Explicit` | `\App\Controllers\Api\Settings\MenuSettingController::siteplanMenuGet` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `siteplan-menu/save` | `Explicit` | `\App\Controllers\Api\Settings\MenuSettingController::siteplanMenuSave` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `siteplan-menu/toggle` | `Explicit` | `\App\Controllers\Api\Settings\MenuSettingController::siteplanMenuToggle` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `siteplan/get/all` | `Explicit` | `Siteplan::getAllKavling` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `siteplan/get/detail` | `Explicit` | `Siteplan::get_detail` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `siteplan/getKategoriOptions` | `Explicit` | `Siteplan::getKategoriOptions` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `siteplan/urgent/summary` | `Explicit` | `Siteplan::urgentSummary` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `siteplan/view` | `Explicit` | `Siteplan::view_siteplan` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `siteplan/produksi-mobile` | `Explicit` | `Siteplan::produksi_mobile` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `getmodal` | `Explicit` | `Modal::index` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `list` | `Explicit` | `TargetController::list` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `detail` | `Explicit` | `TargetController::detail` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `save` | `Explicit` | `TargetController::save` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `history` | `Explicit` | `TargetController::history` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `ANY` | `/siteplan/index` | `AutoRoute` | `Siteplan::index` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/siteplan/normalizeidproyek` | `AutoRoute` | `Siteplan::normalizeIdProyek` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/siteplan/getprofileperusahaan` | `AutoRoute` | `Siteplan::getProfilePerusahaan` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/siteplan/getproyekor404` | `AutoRoute` | `Siteplan::getProyekOr404` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/siteplan/userhasprojectaccess` | `AutoRoute` | `Siteplan::userHasProjectAccess` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/siteplan/getconfigshapemap` | `AutoRoute` | `Siteplan::getConfigShapeMap` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/siteplan/add_kavling` | `AutoRoute` | `Siteplan::add_kavling` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/siteplan/add_kavling2` | `AutoRoute` | `Siteplan::add_kavling2` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/siteplan/add_others` | `AutoRoute` | `Siteplan::add_others` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/siteplan/edit_kavling` | `AutoRoute` | `Siteplan::edit_kavling` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/siteplan/edit_others` | `AutoRoute` | `Siteplan::edit_others` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/siteplan/get_config` | `AutoRoute` | `Siteplan::get_config` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/siteplan/get_others` | `AutoRoute` | `Siteplan::get_others` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/siteplan/get_kavling_by_multiple_id` | `AutoRoute` | `Siteplan::get_kavling_by_multiple_id` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/siteplan/get_harga_kavling` | `AutoRoute` | `Siteplan::get_harga_kavling` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/siteplan/get_turun_pembangunan` | `AutoRoute` | `Siteplan::get_turun_pembangunan` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/siteplan/set_turun_pembangunan` | `AutoRoute` | `Siteplan::set_turun_pembangunan` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/siteplan/get_kavling_by_id` | `AutoRoute` | `Siteplan::get_kavling_by_id` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/siteplan/getfinancialsummary` | `AutoRoute` | `Siteplan::getFinancialSummary` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/siteplan/getlatestfilesbycategories` | `AutoRoute` | `Siteplan::getLatestFilesByCategories` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |

### B. Public PHP Services (Backend Contract Antar-Modul)
#### `SiteplanDataService.php`
- `getKavlings(array $params)`
- `getOthers(array $params)`
- `normalizeIdList($value)`
- `normalizeOptionalId($value)`
- `categoryFilters(array $params)`
#### `SiteplanMenuService.php`
- _Tidak ada public method terdeteksi._
#### `SiteplanUrgentService.php`
- `getSummary(int $idProyek, int $groupId, ?int $userId = null)`
- `getUrgentSummary(int $idProyek, int $groupId, ?int $userId = null)`
- `getDashboardAlertCounts(int $idProyek)`
- `emptySummary()`
- `snoozeUrgentItem(int $userId, int $idProyek, string $itemKey, int $minutes)`
- `buildUrgentSections(int $idProyek, int $groupId, ?int $userId)`
- `visibleUrgentSectionKeys(int $groupId)`
- `canViewActivity(int $groupId)`
- `newSection(string $key)`
- `finalizeSections(array $sections)`
- `filterSnoozedSections(array $sections, array $snoozedKeys)`
- `getActiveSnoozeKeys(int $userId, int $idProyek)`
- `hasSnoozeTable()`
- `extractItemType(string $itemKey)`
- `tagihanBaseBuilder(int $idProyek)`
- _dan 16 method lainnya..._
#### `SiteplanVisualStatusService.php`
- `appendVisualRows(array $rows, ?DateTimeInterface $today = null)`
- `buildRows($row, ?DateTimeInterface $today = null)`
- `buildFill($row, ?array $visualRows = null)`
- `mkdtRow($row)`
- `productionRow($row)`
- `financeRow($row, DateTimeInterface $today)`
- `row(string $key, string $label, array $segments, array $markers = [], array $meta = [])`
- `segment(string $configName, float $ratio)`
- `marker(string $configName, float $position = 0.5)`
- `isNoProgress(array $visualRows)`
- `hasPrice($row)`
- `fill(string $configName, string $tipe = 'Status')`
- `rowWithFinalMarketColors(array $row, ?string $marketFinalStatus)`
- `marketFinalStatus($row)`
- `submissionMarkers(int $outstandingCount)`
- _dan 6 method lainnya..._
#### `TargetSiteplanService.php`
- `listByProject(int $idProyek)`
- `get(int $idTarget)`
- `getKavlingTargetMap(int $idProyek)`
- `save(array $data)`
- `history(int $idTarget)`
- `normalizeKavlingIds($raw)`
- `assertKavlingsBelongToProject(int $idProyek, array $idKavlings)`
- `makeSnapshot(int $idTarget)`
- `currentUserId()`

## File Inventory
### Controllers (3)
- `app/Controllers/Siteplan.php` (1859 baris, 27 public methods)
- `app/Controllers/Modal.php` (18 baris, 1 public methods)
- `app/Controllers/Api/TargetController.php` (81 baris, 4 public methods)

### Services (5)
- `app/Services/SiteplanDataService.php` (124 baris)
- `app/Services/SiteplanMenuService.php` (12 baris)
- `app/Services/SiteplanUrgentService.php` (743 baris)
- `app/Services/SiteplanVisualStatusService.php` (412 baris)
- `app/Services/TargetSiteplanService.php` (202 baris)

### Repositories (2)
- `app/Repositories/TargetSiteplanRepository.php` (142 baris)
- `app/Repositories/OthersRepository.php` (117 baris)

### Models (4)
- `app/Models/SiteplanuploadModel.php`
- `app/Models/TargetSiteplanModel.php`
- `app/Models/TargetSiteplanKavlingModel.php`
- `app/Models/TargetSiteplanHistoryModel.php`

### Views (1 folder)
- `app/Views/siteplan` (16 file)

### JavaScript (7 file)
- `public/assets/js/siteplan/master.js` (3824 baris)
- `public/assets/js/siteplan/composite-shape.js` (420 baris)
- `public/assets/js/siteplan/interaction-highlight.js` (143 baris)
- `public/assets/js/siteplan/shape-filter.js` (65 baris)
- `public/assets/js/siteplan/polygon-clip.js` (312 baris)
- `public/assets/js/siteplan-detail-modal.js` (1889 baris)
- `public/assets/js/siteplan/target.js` (244 baris)

## Readiness Checklist
- [x] Has Service Layer
- [x] Has Repository Layer
- [x] Web & API Controllers Separated
- [x] Explicit Routes Defined
- [x] MODULE.md Initialized

## Dead Code Candidates
_Jalankan `php spark module:deadcode Siteplan` untuk mengaudit dead code kandidat._

## Notes
- Modul diinisialisasi melalui tooling otomatis.

# Module: Siteplan

**Status:** `MAPPED`  
**Readiness Score:** `60/100`  
**Last Synced:** `2026-10-23 13:52:12` @ `ad0c900`  

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
| `POST` | `/siteplan/get/all` | `Explicit` | `Siteplan::getAllKavling` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/siteplan/get/detail` | `Explicit` | `Siteplan::get_detail` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/siteplan/getKategoriOptions` | `Explicit` | `Siteplan::getKategoriOptions` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/siteplan/urgent/summary` | `Explicit` | `Siteplan::urgentSummary` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `/siteplan/view` | `Explicit` | `Siteplan::view_siteplan` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `/siteplan/produksi-mobile` | `Explicit` | `Siteplan::produksi_mobile` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `/getmodal` | `Explicit` | `Modal::index` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/api/target/list` | `Explicit` | `TargetController::list` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/target/detail` | `Explicit` | `TargetController::detail` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/target/save` | `Explicit` | `TargetController::save` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/target/history` | `Explicit` | `TargetController::history` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `ANY` | `/siteplan/index` | `AutoRoute` | `Siteplan::index` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
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
| `ANY` | `/kalendar/index` | `AutoRoute` | `Kalendar::index` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/kalendar/getevents` | `AutoRoute` | `Kalendar::getEvents` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |

### B. Public PHP Services (Backend Contract Antar-Modul)
#### `SiteplanDataService.php`
- `getKavlings(array $params)`
- `getOthers(array $params)`
- `normalizeIdList($value)`
#### `SiteplanMenuService.php`
- _Tidak ada public method terdeteksi._
#### `SiteplanUrgentService.php`
- `getSummary(int $idProyek, int $groupId, ?int $userId = null)`
- `getUrgentSummary(int $idProyek, int $groupId, ?int $userId = null)`
- `getDashboardAlertCounts(int $idProyek)`
- `emptySummary()`
- `snoozeUrgentItem(int $userId, int $idProyek, string $itemKey, int $minutes)`
#### `SiteplanVisualStatusService.php`
- `appendVisualRows(array $rows, ?DateTimeInterface $today = null)`
- `buildRows($row, ?DateTimeInterface $today = null)`
- `buildFill($row, ?array $visualRows = null)`
#### `TargetSiteplanService.php`
- `listByProject(int $idProyek)`
- `get(int $idTarget)`
- `getKavlingTargetMap(int $idProyek)`
- `save(array $data)`
- `history(int $idTarget)`
#### `KalendarService.php`
- `divisiKeyFromName(string $name)`
- `getDivisiForView(array $groups)`
- `getEventKeysForDivisiKeys(array $divisiKeys)`
- `getEventsForCalendar(int $idProyek, string $start, string $end, array $types = [])`

## File Inventory
### Controllers (4)
- `app/Controllers/Siteplan.php` (1859 baris, 19 public methods)
- `app/Controllers/Modal.php` (18 baris, 1 public methods)
- `app/Controllers/Api/TargetController.php` (81 baris, 4 public methods)
- `app/Controllers/Kalendar.php` (58 baris, 2 public methods)

### Services (6)
- `app/Services/SiteplanDataService.php` (124 baris)
- `app/Services/SiteplanMenuService.php` (12 baris)
- `app/Services/SiteplanUrgentService.php` (743 baris)
- `app/Services/SiteplanVisualStatusService.php` (412 baris)
- `app/Services/TargetSiteplanService.php` (202 baris)
- `app/Services/KalendarService.php` (189 baris)

### Repositories (3)
- `app/Repositories/TargetSiteplanRepository.php` (142 baris)
- `app/Repositories/OthersRepository.php` (117 baris)
- `app/Repositories/KalendarRepository.php` (87 baris)

### Models (4)
- `app/Models/SiteplanuploadModel.php`
- `app/Models/TargetSiteplanModel.php`
- `app/Models/TargetSiteplanKavlingModel.php`
- `app/Models/TargetSiteplanHistoryModel.php`

### Views (2 folder)
- `app/Views/siteplan` (16 file)
- `app/Views/misc` (1 file)

### JavaScript (7 file)
- `public/assets/js/siteplan/master.js` (3824 baris)
- `public/assets/js/siteplan/composite-shape.js` (420 baris)
- `public/assets/js/siteplan/interaction-highlight.js` (143 baris)
- `public/assets/js/siteplan/shape-filter.js` (65 baris)
- `public/assets/js/siteplan/polygon-clip.js` (312 baris)
- `public/assets/js/siteplan-detail-modal.js` (2070 baris)
- `public/assets/js/siteplan/target.js` (244 baris)

## Readiness Checklist
- [ ] Has Service Layer
- [ ] Has Repository Layer
- [ ] Web & API Controllers Separated
- [ ] Explicit Routes Defined
- [ ] Zero Controller Layer Violations
- [x] MODULE.md Initialized

> **Layer Violations Detected:**
> - `app/Controllers/Siteplan.php`: Database::connect(), $this->db->table(), new ProyekModel(), new ClusterModel(), new JalanModel(), new TipeModel(), new KavlingModel(), new ConfigModel(), new ProduksiModel(), new LegalModel(), new MkdtModel(), new KeuanganModel(), new LogPembayaranModel(), new ChecklistSubItemModel()
> - `app/Controllers/Kalendar.php`: new GroupModel()

## Dead Code Candidates
_Jalankan `php spark module:deadcode Siteplan` untuk mengaudit dead code kandidat._

## Notes
- Modul diinisialisasi melalui tooling otomatis.

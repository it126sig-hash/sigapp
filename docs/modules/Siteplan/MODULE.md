# Module: Siteplan

**Status:** `REFACTORED`  
**Readiness Score:** `100/100`  
**Last Synced:** `2026-11-24 13:40:22` @ `84f8fcb`  

## Purpose
Visualisasi peta interaktif kavling, filter multi-departemen, dan status spasial visual.

## Out of scope
- Hal-hal di luar tanggung jawab domain Siteplan.

## Invariants
- Modul lain tidak boleh memanipulasi tabel pemilik secara langsung.
- Perubahan finansial/status harus diverifikasi di level Service.

## Database Tables
### Owner Table(s)
- `others` *(akses tulis eksklusif hanya untuk modul Siteplan)*
- `siteplan_upload` *(akses tulis eksklusif hanya untuk modul Siteplan)*
- `target_siteplan` *(akses tulis eksklusif hanya untuk modul Siteplan)*
- `target_siteplan_kavling` *(akses tulis eksklusif hanya untuk modul Siteplan)*
- `target_siteplan_history` *(akses tulis eksklusif hanya untuk modul Siteplan)*
- `target_siteplan_request` *(akses tulis eksklusif hanya untuk modul Siteplan)*

### Shared Tables (Dibaca dari modul lain)
- `kavling`
- `mkdt`
- `keuangan`
- `produksi`
- `legal`
- `tiket_masalah`
- `kavling_request`
- `kavling_request_kavling`

## Contracts
### A. Public HTTP Endpoints & Routes Inventory
| Method | URI / Pattern | Tipe | Handler | Status Migrasi | Target API / Catatan |
|---|---|---|---|---|---|
| `GET` | `/siteplan` | `Explicit` | `SiteplanController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/siteplan/index` | `Explicit` | `SiteplanController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/siteplan/view` | `Explicit` | `SiteplanController::view_siteplan` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/siteplan/view_siteplan` | `Explicit` | `SiteplanController::view_siteplan` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/siteplan/view_siteplan/(:num)` | `Explicit` | `SiteplanController::view_siteplan/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/siteplan/produksi-mobile` | `Explicit` | `SiteplanController::produksi_mobile` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/siteplan/produksi_mobile` | `Explicit` | `SiteplanController::produksi_mobile` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/getmodal` | `Explicit` | `\App\Controllers\Web\Siteplan\ModalController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/kalendar` | `Explicit` | `\App\Controllers\Web\Siteplan\KalendarController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/kalendar/index` | `Explicit` | `\App\Controllers\Web\Siteplan\KalendarController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/siteplan/meta` | `Explicit` | `SiteplanController::meta` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/siteplan/kavling` | `Explicit` | `SiteplanController::kavling` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/siteplan/kavling` | `Explicit` | `SiteplanController::kavling` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/siteplan/proyek/(:num)` | `Explicit` | `SiteplanController::proyek/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/siteplan/proyek` | `Explicit` | `SiteplanController::proyek` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/siteplan/config` | `Explicit` | `SiteplanController::config` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/siteplan/others` | `Explicit` | `SiteplanController::others` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/siteplan/others` | `Explicit` | `SiteplanController::others` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/siteplan/detail/(:num)` | `Explicit` | `SiteplanController::detail/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/siteplan/detail` | `Explicit` | `SiteplanController::detail` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/siteplan/add_kavling` | `Explicit` | `SiteplanController::add_kavling` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/siteplan/edit_kavling` | `Explicit` | `SiteplanController::edit_kavling` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/siteplan/edit_others` | `Explicit` | `SiteplanController::edit_others` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/siteplan/get_kavling_by_id` | `Explicit` | `SiteplanController::get_kavling_by_id` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/siteplan/get_kavling_by_multiple_id` | `Explicit` | `SiteplanController::get_kavling_by_multiple_id` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/siteplan/get_harga_kavling` | `Explicit` | `SiteplanController::get_harga_kavling` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/siteplan/get_turun_pembangunan` | `Explicit` | `SiteplanController::get_turun_pembangunan` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/siteplan/set_turun_pembangunan` | `Explicit` | `SiteplanController::set_turun_pembangunan` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/siteplan/get_others` | `Explicit` | `SiteplanController::get_others` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/siteplan/get/all` | `Explicit` | `SiteplanController::getAllKavling` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/siteplan/getAllKavling` | `Explicit` | `SiteplanController::getAllKavling` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/siteplan/get/detail` | `Explicit` | `SiteplanController::get_detail` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/siteplan/get_detail` | `Explicit` | `SiteplanController::get_detail` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/siteplan/getKategoriOptions` | `Explicit` | `SiteplanController::getKategoriOptions` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/siteplan/urgent/summary` | `Explicit` | `SiteplanController::urgentSummary` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/siteplan/get_others` | `Explicit` | `SiteplanController::get_others` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/siteplan/add_kavling` | `Explicit` | `SiteplanController::add_kavling` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/siteplan/edit_kavling` | `Explicit` | `SiteplanController::edit_kavling` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/siteplan/edit_others` | `Explicit` | `SiteplanController::edit_others` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/siteplan/get_kavling_by_multiple_id` | `Explicit` | `SiteplanController::get_kavling_by_multiple_id` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/siteplan/get_harga_kavling` | `Explicit` | `SiteplanController::get_harga_kavling` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/siteplan/get_turun_pembangunan` | `Explicit` | `SiteplanController::get_turun_pembangunan` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/siteplan/set_turun_pembangunan` | `Explicit` | `SiteplanController::set_turun_pembangunan` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/siteplan/get_kavling_by_id` | `Explicit` | `SiteplanController::get_kavling_by_id` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/target/list` | `Explicit` | `TargetController::list` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/target/detail` | `Explicit` | `TargetController::detail` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/target/save` | `Explicit` | `TargetController::save` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/target/add-kavling` | `Explicit` | `TargetController::addKavling` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/target/update-kavling-tipe` | `Explicit` | `TargetController::updateKavlingTipe` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/target/remove-kavling` | `Explicit` | `TargetController::removeKavling` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/target/history` | `Explicit` | `TargetController::history` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/target/request-change` | `Explicit` | `TargetController::requestChange` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/target/requests` | `Explicit` | `TargetController::requests` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/target/review-request` | `Explicit` | `TargetController::reviewRequest` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/siteplan/kalendar/events` | `Explicit` | `KalendarController::getEvents` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/siteplan/kalendar/meta` | `Explicit` | `KalendarController::meta` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/kalendar/events` | `Explicit` | `KalendarController::getEvents` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/kalendar/meta` | `Explicit` | `KalendarController::meta` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |

### B. Public PHP Services (Backend Contract Antar-Modul)
#### `SiteplanService.php`
- `getIndexState(int $userId)`
- `getViewSiteplanData(?int $requestedProyekId, int $userId)`
- `getProduksiMobileData(?int $requestedProyekId, int $userId, int $initialIdKavling = 0)`
- `getMeta(?int $idProyek, int $userId)`
- `getConfigShapeList()`
- `getConfigShapeMap()`
- `getConfigByRole(int $roleId)`
- `getKategoriOptions(int $idProyek)`
- `addKavlingOrOthers(array $post, int $userId)`
- `editKavling(array $post, int $userId)`
- `editOthers(array $post, int $userId)`
- `getKavlingByMultipleId(array $ids)`
- `getHargaKavling(array $ids)`
- `getTurunPembangunan(array $ids)`
- `setTurunPembangunan(array $post, mixed $file, int $userId)`
- `getKavlingById(int $id)`
#### `SiteplanDetailService.php`
- `getDetail(int $idKavling, ?int $idMkdt = null, ?int $idHargaJual = null, ?int $idLegal = null, ?int $userId = null)`
#### `SiteplanDataService.php`
- `getKavlings(array $params)`
- `getOthers(array $params)`
- `normalizeIdList($value)`
#### `SiteplanUrgentService.php`
- `resolveGroupId(?int $sessionGroupId, int $userId)`
- `getSummary(int $idProyek, int $groupId, ?int $userId = null)`
- `getUrgentSummary(int $idProyek, int $groupId, ?int $userId = null)`
- `getDashboardAlertCounts(?int $idProyek = null)`
- `countMkdtDateAlerts(string $field, ?int $idProyek = null, ?string $fromDate = null, ?string $toDate = null)`
- `emptySummary()`
- `snoozeUrgentItem(int $userId, int $idProyek, string $itemKey, int $minutes)`
#### `SiteplanVisualStatusService.php`
- `appendVisualRows(array $rows, ?DateTimeInterface $today = null)`
- `buildRows($row, ?DateTimeInterface $today = null)`
- `buildFill($row, ?array $visualRows = null)`
#### `TargetSiteplanService.php`
- `listByProject(int $idProyek)`
- `get(int $idTarget)`
- `getByProjectAndYear(int $idProyek, int $tahunTarget)`
- `resolveProjectIdForTargetKavling(?int $idTargetKavling, ?int $idTarget)`
- `resolveProjectIdsForTargetKavlings(array $idTargetKavlings, ?int $idTarget = null)`
- `resolveProjectIdForTargetRequest(int $idRequest)`
- `getKavlingTargetMap(int $idProyek, ?int $tahunTarget = null)`
- `getAvailableYears(int $idProyek)`
- `addKavlingToYear(array $data, int $actorId)`
- `updateKavlingTipe(array $data, int $actorId)`
- `removeKavling(array $data, int $actorId)`
- `save(array $data)`
- `history(int $idTarget)`
- `requestTipeChange(array $data, int $actorId)`
- `listRequests(int $idProyek, ?int $idTarget = null, ?int $tahunTarget = null, ?string $status = null)`
- `reviewRequest(int $idRequest, string $decision, ?string $catatanApproval, int $approverId)`
#### `KalendarService.php`
- `divisiKeyFromName(string $name)`
- `getCalendarViewData(int $userId = 0)`
- `getDivisiForView(array $groups)`
- `getEventKeysForDivisiKeys(array $divisiKeys)`
- `getEventsForCalendar(int $idProyek, string $start, string $end, array $types = [])`

## File Inventory
### Controllers (6)
- `app/Controllers/Web/Siteplan/SiteplanController.php` (96 baris, 3 public methods: `index()`, `view_siteplan(?int $idProyek = null)`, `produksi_mobile()`)
- `app/Controllers/Web/Siteplan/ModalController.php` (21 baris, 1 public methods: `index()`)
- `app/Controllers/Web/Siteplan/KalendarController.php` (27 baris, 1 public methods: `index()`)
- `app/Controllers/Api/Siteplan/SiteplanController.php` (550 baris, 19 public methods: `meta()`, `kavling()`, `proyek($id = null)`, `config()`, `others()`, `detail(?int $idKavling = null)`, `urgentSummary()`, `getAllKavling()`, `getKategoriOptions()`, `get_others()`, `add_kavling()`, `edit_kavling()`, `edit_others()`, `get_kavling_by_multiple_id()`, `get_harga_kavling()`, `get_turun_pembangunan()`, `set_turun_pembangunan()`, `get_kavling_by_id()`, `get_detail()`)
- `app/Controllers/Api/Siteplan/TargetController.php` (645 baris, 10 public methods: `list()`, `detail()`, `save()`, `addKavling()`, `updateKavlingTipe()`, `removeKavling()`, `history()`, `requestChange()`, `requests()`, `reviewRequest()`)
- `app/Controllers/Api/Siteplan/KalendarController.php` (132 baris, 2 public methods: `meta()`, `getEvents()`)

### Services (7)
- `app/Services/Siteplan/SiteplanService.php` (954 baris, 16 public methods)
- `app/Services/Siteplan/SiteplanDetailService.php` (540 baris, 1 public methods)
- `app/Services/Siteplan/SiteplanDataService.php` (127 baris, 3 public methods)
- `app/Services/Siteplan/SiteplanUrgentService.php` (396 baris, 7 public methods)
- `app/Services/Siteplan/SiteplanVisualStatusService.php` (415 baris, 3 public methods)
- `app/Services/Siteplan/TargetSiteplanService.php` (1316 baris, 16 public methods)
- `app/Services/Siteplan/KalendarService.php` (210 baris, 5 public methods)

### Repositories (5)
- `app/Repositories/Siteplan/SiteplanRepository.php` (946 baris, 49 public methods: `getProfilePerusahaan()`, `getPphList()`, `getPpnList()`, `getConfigShapeList()`, `getConfigShapeMap()`, `getConfigByRole(int $roleId)`, `getClusterOptionsByProyek(int $idProyek)`, `getClustersByProyek(int $idProyek)`, `getJalansByProyek(int $idProyek)`, `getTipesByProyek(int $idProyek)`, `getChecklistSubItemsWithGroup(?int $idProyek = null)`, `getChecklistItemsMap()`, `getHakAksesByUser(int $userId, ?string $namaHak = null)`, `getUserGroupId(int $userId)`, `findProyekById(int $idProyek)`, `getProyekByUserId(int $userId, bool $isAdmin = false)`, `userHasProjectAccess(?object $proyek, int $userId)`, `findDuplicateKavlingByJalan(string $noKavling, int $idJalan, ?int $excludeId = null)`, `isNoKavlingDuplicate(string $noKavling, int $idJalan, ?int $excludeId = null)`, `insertKavling(array $fields)`, `updateKavling(int $id, array $fields)`, `findKavlingsByIdsRaw(array $ids)`, `findKavlingPerintahBangunSnapshot(int $id)`, `findKavlingWithRelationsById(int $id)`, `findKavlingsWithRelationsByIds(array $ids)`, `findHargaKavlingById(int $id)`, `findHargaKavlingsByIds(array $ids)`, `findTurunPembangunanByIds(array $ids)`, `getDetailMkdt(int $idMkdt)`, `getDetailKavling(int $idKavling)`, `getDetailTipe(int $idTipe)`, `getDetailPricelist(int $idHargaJual)`, `getDetailKeuanganByMkdt(int $idMkdt)`, `getLastQueryString()`, `getDetailLogPembayaranByMkdt(int $idMkdt)`, `getDetailLogPembayaranDetailByMkdt(int $idMkdt)`, `getDetailPaymentSummaryByMkdt(int $idMkdt)`, `getDetailLastKeuanganUserByMkdt(int $idMkdt)`, `getDetailLegal(int $idLegal)`, `getDetailProduksi(int $idKavling)`, `getDetailFileProduksi(int $idKavling)`, `getDetailFilePph(int $idKavling)`, `getDetailFilePpn(int $idKavling)`, `getDetailHutangSubkon(int $idKavling)`, `getDetailFinanceLedgerIncome(?int $idMkdt, ?int $idKavling)`, `getDetailBankRetensiPending(int $idKavling)`, `getDetailDanaAkadSummary(int $idKavling)`, `getDetailBayarProduksi(int $idKavling)`, `getDetailSi(int $idKavling)`)
- `app/Repositories/Siteplan/SiteplanUrgentRepository.php` (453 baris, 14 public methods: `getUserGroupId(int $userId)`, `countTagihanOverdue(int $idProyek, string $today)`, `countTagihanDue(int $idProyek, string $today, string $limitDate)`, `countCashoutSubkonUrgent(int $idProyek, string $limitDate)`, `countPembangunanTelat(int $idProyek, string $today)`, `countMkdtDateAlerts(string $field, ?int $idProyek = null, ?string $fromDate = null, ?string $toDate = null)`, `hasSnoozeTable()`, `upsertSnooze(int $userId, int $idProyek, string $itemKey, string $itemType, string $snoozedUntil, string $now)`, `getActiveSnoozeKeys(int $userId, int $idProyek, string $now)`, `getTagihanUrgent(int $idProyek, string $limitDate)`, `getCashoutSubkonUrgent(int $idProyek, string $limitDate, int $groupId)`, `getMkdtDateUrgent(int $idProyek, string $field, string $limitDate)`, `getPembangunanTelat(int $idProyek, string $today)`, `getUnreadNotifications(int $idProyek, int $groupId)`)
- `app/Repositories/Siteplan/TargetSiteplanRepository.php` (935 baris, 27 public methods: `getByProject(int $idProyek)`, `getById(int $idTarget)`, `findActiveByProjectAndYear(int $idProyek, int $tahunTarget)`, `createTargetHeader(array $fields)`, `updateTargetHeader(int $idTarget, array $fields)`, `getKavlings(int $idTarget)`, `findTargetKavlingRow(int $idTarget, int $idKavling)`, `findTargetKavlingById(int $idTargetKavling)`, `findTargetKavlingsByIds(array $idTargetKavlings)`, `findTargetKavlingsByTargetAndKavlingIds(int $idTarget, array $idKavlings)`, `insertTargetKavling(array $fields)`, `updateTargetKavling(int $idTargetKavling, array $fields)`, `deleteTargetKavling(int $idTargetKavling)`, `deleteTargetKavlings(array $idTargetKavlings)`, `findKavlingsWithActiveConsumer(array $idKavlings)`, `findTipeForProject(int $idTipe, int $idProyek)`, `countKavlingsInProject(int $idProyek, array $idKavlings)`, `countTargetHistory(int $idTarget)`, `getHistory(int $idTarget)`, `getAvailableYears(int $idProyek)`, `getKavlingTargetMap(int $idProyek, ?int $tahunTarget = null)`, `findPendingRequest(int $idTarget, int $idKavling)`, `findRequestById(int $idRequest)`, `insertRequest(array $data)`, `updateRequest(int $idRequest, array $data)`, `getRequests(int $idProyek, ?int $idTarget = null, ?int $tahunTarget = null, ?string $status = null)`, `getRequestCounts(int $idProyek, ?int $idTarget = null, ?int $tahunTarget = null)`)
- `app/Repositories/Siteplan/OthersRepository.php` (187 baris, 8 public methods: `getAll(?int $id, int $idProyek, array $clusterIds, array $filters = [])`, `insertOthers(array $fields)`, `updateOthers(int $id, array $fields)`, `findById(int $id)`, `findProyekIdByJalanId(int $idJalan)`, `hasProgressHistory()`, `countProgressHistory(int $idOthers)`, `getProgressHistory(int $idOthers, int $limit, int $offset)`)
- `app/Repositories/Siteplan/KalendarRepository.php` (108 baris, 5 public methods: `getGroupsForUser(int $userId)`, `getAllGroups()`, `getPembayaranEvents(int $idProyek, string $start, string $end)`, `getJatuhTempoEvents(int $idProyek, string $start, string $end)`, `getMkdtEvents(int $idProyek, string $start, string $end)`)

### Models (5)
- `app/Models/SiteplanuploadModel.php`
- `app/Models/TargetSiteplanModel.php`
- `app/Models/TargetSiteplanKavlingModel.php`
- `app/Models/TargetSiteplanHistoryModel.php`
- `app/Models/TargetSiteplanRequestModel.php`

### Views (2 folder)
- `app/Views/siteplan` (16 file: `cashout_subkon.php`, `direksi.php`, `keuangan.php`, `legal.php`, `master.php`, `mkdt.php`, `modal_list_request_kavling.php`, `pajak.php`, `pilih_proyek.php`, `planning.php`, `produksi.php`, `produksi_mobile.php`, `request_kavling_modal.php`, `sales.php`, `target.php`, `test.php`)
- `app/Views/misc` (1 file: `kalender.php`)

### JavaScript (9 file)
- `public/assets/js/siteplan/master.js` (5180 baris)
- `public/assets/js/siteplan/composite-shape.js` (440 baris)
- `public/assets/js/siteplan/interaction-highlight.js` (143 baris)
- `public/assets/js/siteplan/shape-filter.js` (65 baris)
- `public/assets/js/siteplan/polygon-clip.js` (312 baris)
- `public/assets/js/siteplan-detail-modal.js` (2431 baris)
- `public/assets/js/siteplan/target.js` (3545 baris)
- `public/assets/js/siteplan/tiket-overlay.js` (462 baris)
- `public/assets/js/siteplan/target-overlay.js` (363 baris)

### Zero-Downtime Shims (13 file)
- `app/Controllers/Siteplan.php`
- `app/Controllers/Modal.php`
- `app/Controllers/Kalendar.php`
- `app/Controllers/Api/TargetController.php`
- `app/Services/SiteplanDataService.php`
- `app/Services/SiteplanMenuService.php`
- `app/Services/SiteplanUrgentService.php`
- `app/Services/SiteplanVisualStatusService.php`
- `app/Services/TargetSiteplanService.php`
- `app/Services/KalendarService.php`
- `app/Repositories/TargetSiteplanRepository.php`
- `app/Repositories/OthersRepository.php`
- `app/Repositories/KalendarRepository.php`

## Key Functions
- **Web Controllers (`app/Controllers/Web/Siteplan/`):**
  - `SiteplanController::index()`, `view_siteplan(?int $idProyek = null)`, `produksi_mobile()`: Thin controller yang mendelegasikan state proyek, izin role, dan data master ke `SiteplanService` sebelum merender view `siteplan/*`.
  - `ModalController::index()`: Render partial modal terstruktur (`/getmodal`).
  - `KalendarController::index()`: Render halaman kalender proyek (`/kalendar`, `/kalendar/index`) menggunakan metadata divisi/event dari `KalendarService::getCalendarViewData()`.
- **API Controllers (`app/Controllers/Api/Siteplan/`):**
  - `SiteplanController`: Menyediakan endpoint REST JSON murni (`/api/siteplan/{meta,kavling,proyek,config,others,detail}`) serta handler Zero-Downtime AJAX (`/siteplan/get/all`, `/siteplan/get/detail`, `/siteplan/add_kavling`, `/siteplan/edit_kavling`, `/siteplan/edit_others`, `/siteplan/get_turun_pembangunan`, `/siteplan/set_turun_pembangunan`, dll.) dengan `getRequestData()` (aman untuk form-urlencoded, multipart, dan JSON) serta `wantsEnvelope()` (memprioritaskan query `?format=raw` / `RAW_JSON_READY`).
  - `TargetController`: Endpoint `/api/target/{list,detail,save,add-kavling,update-kavling-tipe,remove-kavling,history,request-change,requests,review-request}` dengan RBAC mutasi/approval eksklusif group `1` (Admin) dan `4` (MKDT) via `authorizeMutation()`, RBAC pengajuan perubahan tipe eksklusif group `1` dan `6` (Planning) via `authorizePlanningRequest()`, akses daftar request untuk group `1`/`4`/`6`/`11` via `authorizeRequestList()` (`403` untuk role lain), verifikasi akses proyek (`authorizeProjectAccess()`), dukungan lookup `detail()` via `id_target` atau pasangan `id_proyek` + `tahun_target`, dukungan input batch (`id_kavlings`, `id_target_kavlings`) pada `addKavling()`, `updateKavlingTipe()`, dan `removeKavling()` (validasi satu proyek via `resolveProjectIdsForTargetKavlings()` dan pesan respons dinamis tunggal vs batch), serta delegasi penuh ke `TargetSiteplanService`.
  - `KalendarController`: Endpoint `/api/kalendar/{events,meta}`, `/api/siteplan/kalendar/{events,meta}`, dan fallback `/kalendar/getEvents` dengan dukungan `?format=raw` dan filter divisi/event.
- **Service (`app/Services/Siteplan/SiteplanService.php`):**
  - `getIndexState(int $userId)`, `getViewSiteplanData(?int $requestedProyekId, int $userId)`, `getProduksiMobileData(...)`, `getMeta(?int $idProyek, int $userId)`: Agregasi state halaman web dan metadata SPA siteplan (daftar proyek berizin, cluster, jalan, tipe, pph, ppn, checklist, `config_shape`, `permissions.{role_id, is_admin, has_project_access, can_manage_target, can_request_target_change}` via `resolvePrimaryRoleId()`, `has_akses.target` untuk group `1`/`4`/`11`, `has_akses.update_tanggal_pembangunan` bernilai `true` untuk group `1`/`7`/`8` pada alur desktop/Siteplan di `getViewSiteplanData()` & `getMeta()` tanpa mengubah `getProduksiMobileData()`, serta `siteplan_menus` dalam format JSON murni via `SiteplanMenuService::getMenusForSpa()`).
  - `addKavlingOrOthers(array $post, int $userId)`, `editKavling(array $post, int $userId)`, `editOthers(array $post, int $userId)`: Mutasi kavling dan aset penunjang `others` dalam transaksi DB (`transBegin`/`transCommit`/`transRollback`), validasi duplikasi nomor kavling per jalan, serta integrasi pencatatan riwayat (`HistoryService`, `MkdtHistoryService`) dan notifikasi (`NotifikasiService`).
  - `getKavlingByMultipleId(array $ids)`, `getHargaKavling(array $ids)`, `getTurunPembangunan(array $ids)`, `setTurunPembangunan(array $post, mixed $file, int $userId)`: Pengambilan harga/turun bangun dan eksekusi perintah turun pembangunan (upload file perintah bangun via `FileAccessService`, update batch dalam DB transaction, log `MkdtHistoryService`, dan trigger `NotifikasiService` `NotificationEvent::TURUN_PEMBANGUNAN`).
- **Service (`app/Services/Siteplan/SiteplanDetailService.php`):**
  - `getDetail(int $idKavling, ?int $idMkdt = null, ?int $idHargaJual = null, ?int $idLegal = null, ?int $userId = null)`: Ekstraksi agregator detail kavling lintas departemen (~540 baris: MKDT, kavling, tipe, pricelist, keuangan, log pembayaran, legal, produksi, PPh/PPN, hutang subkon, finance ledger, retensi bank, dana akad, SI) dengan lampiran akses dan Signed URL dari `FileAccessService`.
- **Service (`app/Services/Siteplan/SiteplanVisualStatusService.php`):**
  - `appendVisualRows(array $rows, ?DateTimeInterface $today = null)` / `buildRows($row, ?DateTimeInterface $today = null)`: Membangun array status visual multi-departemen (MKDT, Keuangan, Produksi, Legal, Pajak) untuk setiap unit kavling di peta siteplan.
  - `mkdtRow($row)`: Menghitung status dan marker visual segmen MKDT. Mengevaluasi boolean `$hasAkadAjb` (`akad == 1` atau tanggal akad terisi) dan `$hasAkadIndent` (`akad_indent == 1` atau tanggal akad indent terisi). Jika salah satu terpenuhi, status MKDT adalah `'Akad'` (`Akad Subsidi` atau `Akad Komersil`). Jika kavling sudah mencapai `Akad Indent` namun belum `Akad AJB` (`$hasAkadIndent && !$hasAkadAjb`), marker visual `[['config_name' => 'Akad Indent', 'position' => 0.5]]` ditambahkan (garis aksen biru). Saat `Akad AJB` terpenuhi (`$hasAkadAjb`), marker visual dilepas sehingga warna solid Akad tampil penuh.
  - `buildFill($row, ?array $visualRows = null)`: Menentukan warna isian (fill color) poligon berdasarkan konfigurasi status visual kavling.
- **Service (`app/Services/Siteplan/SiteplanDataService.php`):**
  - `getKavlings(array $params)` & `getOthers(array $params)`: Pengambilan data kavling dan fasum/others dengan filtering proyek, cluster, dan status visual/overlay. `getKavlings()` kini selalu menyertakan `target_kavling` (dengan filter opsional `tahun_target`) dan `target_years` di semua `id_role` tanpa pembatasan `id_role === 11`.
- **Service (`app/Services/Siteplan/SiteplanUrgentService.php`):**
  - `getSummary()` / `getUrgentSummary()`: Menghitung kavling-kavling yang memerlukan perhatian mendesak berdasarkan filter waktu/status per divisi (menggunakan `SiteplanUrgentRepository`).
  - `getDashboardAlertCounts(?int $idProyek)`: Agregasi tanpa batas kuota untuk kartu peringatan dashboard utama (tagihan overdue, tagihan due 7 hari, cashout subkon, bangunan telat, SP3K expire, rencana akad).
  - `countMkdtDateAlerts(string $field, ?int $idProyek, ?string $fromDate, ?string $toDate)`: Menghitung kavling aktif dengan batas tanggal MKDT (`sp3k_tgl_exp`, `rencana_akad_tgl`) pada rentang tertentu, memvalidasi `akad_tgl` belum terlaksana dan unit tidak batal.
  - `snoozeUrgentItem(int $userId, int $idProyek, string $itemKey, int $minutes)`: Menyimpan snooze alert per user.
- **Service (`app/Services/Siteplan/TargetSiteplanService.php`):**
  - `getByProjectAndYear(int $idProyek, int $tahunTarget)`, `resolveProjectIdForTargetKavling(?int $idTargetKavling, ?int $idTarget)`, `resolveProjectIdsForTargetKavlings(array $idTargetKavlings, ?int $idTarget = null)`, & `resolveProjectIdForTargetRequest(int $idRequest)`: Resolusi detail target aktif per tahun proyek (mengembalikan struktur kosong jika tahun belum dibuat) dan lookup proyek tunggal maupun batch untuk otorisasi endpoint mutasi/approval.
  - `getAvailableYears(int $idProyek)` & `getKavlingTargetMap(int $idProyek, ?int $tahunTarget = null)`: Mengambil daftar tahun target aktif proyek dan meneruskan filter `$tahunTarget` ke `TargetSiteplanRepository::getKavlingTargetMap()`.
  - `addKavlingToYear(array $data, int $actorId)`, `updateKavlingTipe(array $data, int $actorId)`, & `removeKavling(array $data, int $actorId)`: Mutasi atomik target tunggal maupun batch (`id_kavlings` / `id_target_kavlings`) dalam satu transaksi DB (`transBegin`/`transCommit`/`transRollback`), auto-create header `target_siteplan` jika tahun baru, validasi kepemilikan kavling pada proyek via `assertKavlingsBelongToProject()`, penolakan kavling berkonsumen aktif kecuali status kosong/`Batal`/`NUP` via `assertNoActiveConsumers()` pada `addKavlingToYear()`/`updateKavlingTipe()` (sementara `removeKavling()` mendukung penghapusan batch/single tanpa `assertNoActiveConsumers()`, mengelompokkan baris per target melalui `locateTargetKavlingRows()`, menghapus via `deleteTargetKavlings()`, dan mengembalikan `batch_summary` `{ removed_count, id_target_kavlings, removed_kavling_ids }`), validasi tipe master `is_active = 1` vs `tipe_manual` via `resolveAndValidateTipeInput()`, serta pencatatan **Versioning History** `v1`, `v2`, ... lengkap dengan snapshot `before`/`after` dan `diff_summary` dalam 1 entri riwayat per transaksi/target via `recordTargetHistory()` & `HistoryService::log('target_siteplan', ...)`.
  - `requestTipeChange(array $data, int $actorId)`: Pengajuan perubahan tipe target oleh Planning ke tabel `target_siteplan_request` (`status = 'pending'`) dengan validasi target aktif, penolakan kavling berkonsumen aktif, validasi tipe baru (`is_active = 1` vs `tipe_manual`, wajib berbeda dari tipe target saat ini), serta penjagaan duplikat pengajuan `pending` pada kavling/target yang sama.
  - `listRequests(int $idProyek, ?int $idTarget = null, ?int $tahunTarget = null, ?string $status = null)`: Pengambilan daftar pengajuan perubahan tipe target beserta statistik counter (`all`, `pending`, `approved`, `rejected`).
  - `reviewRequest(int $idRequest, string $decision, ?string $catatanApproval, int $approverId)`: Pemrosesan ACC/Tolak oleh MKDT dalam transaksi DB. Saat `reject`, memperbarui status menjadi `rejected` beserta catatan approval. Saat `approve`, memvalidasi ulang konsumen & tipe aktif, **hanya** memperbarui `target_siteplan_kavling` (`id_tipe`/`tipe_manual`/`updated_by`/`updated_at`) tanpa menyentuh master `kavling.id_tipe`, memperbarui `target_siteplan_request` (`status = 'approved'`), serta mencatat versi baru pada history dengan aksi `target_approve_tipe` dan metadata `id_request`, `catatan_request`, `catatan_approval`, `requested_by_username`, dan `approved_by_username` (didukung resolusi username dari tabel `users` via `resolveUsername()`).
  - `save(array $data)`: Logika transaksi penyimpanan/pembaruan catatan target tahunan dengan mempertahankan `id_tipe` & `tipe_manual` eksisting saat sinkronisasi batch dan mencatat versi riwayat target.
- **Service (`app/Services/Siteplan/KalendarService.php`):**
  - `getCalendarViewData(int $userId = 0)` & `getEventsForCalendar(int $idProyek, string $start, string $end, array $types = [])`: Metadata divisi/filter serta pengambilan event kalender untuk jatuh tempo, pembayaran, dan MKDT.
- **Repository (`app/Repositories/Siteplan/SiteplanRepository.php`):**
  - Query konfigurasi (`getConfigShapeList`, `getConfigShapeMap`, `getConfigByRole`), hak akses user (`getHakAksesByUser`), parameterized `UNION ALL` file pajak (`getDetailFilePph`, `getDetailFilePpn`), mutasi kavling (`insertKavling`, `updateKavling`, `findDuplicateKavlingByJalan`), dan seluruh query agregasi detail kavling lintas tabel.
- **Repository (`app/Repositories/Siteplan/SiteplanUrgentRepository.php`):**
  - Ekstraksi penuh seluruh query database dari `SiteplanUrgentService` (tagihan overdue/due, cashout subkon, bangunan telat, alert tanggal MKDT, snooze, dan notifikasi belum dibaca).
- **Repository (`app/Repositories/Siteplan/TargetSiteplanRepository.php`):**
  - `getByProject(int $idProyek)`, `getById(int $idTarget)`, `findActiveByProjectAndYear(int $idProyek, int $tahunTarget)`, `createTargetHeader()`, `updateTargetHeader()`, `countKavlingsInProject(...)`: Query header target siteplan per proyek/tahun.
  - `getKavlings(int $idTarget)`, `findTargetKavlingRow()`, `findTargetKavlingById()`, `findTargetKavlingsByIds()`, `findTargetKavlingsByTargetAndKavlingIds()`, `getLatestPlanningChangeRequests()` (private), `insertTargetKavling()`, `updateTargetKavling()`, `deleteTargetKavling()`, `deleteTargetKavlings(array $idTargetKavlings)`, `findKavlingsWithActiveConsumer()`, `findTipeForProject()`: Operasi data `target_siteplan_kavling` tunggal/batch (termasuk hapus batch atomik `deleteTargetKavlings()` sambil mempertahankan `deleteTargetKavling()`), proyeksi field `pending_planning_request_id`, `planning_request_status`, `planning_request_created_at` dari tabel `kavling_request_kavling` JOIN `kavling_request` (`jenis_request = 'ubah_tipe'`, digabung dengan fallback kolom legacy `kavling_request.id_kavling` sambil memprioritaskan status `pending`), validasi status konsumen aktif (`id_mkdt > 0`, `is_batal = 0`, `status_mkdt NOT IN ('', 'batal', 'nup')`), serta pengecekan master tipe proyek (`tipe.is_active`).
  - `countTargetHistory(int $idTarget)` & `getHistory(int $idTarget)`: Pembacaan riwayat versi target melalui `HistoryRepository`/`HistoryService` lengkap dengan ekstraksi `version`, `version_label`, `before_snapshot`, `after_snapshot`, `diff_summary`, `requested_by_username`, `approved_by_username`, `id_request`, `catatan_request`, dan `catatan_approval`.
  - `findPendingRequest(int $idTarget, int $idKavling)`, `findRequestById(int $idRequest)`, `insertRequest(array $data)`, `updateRequest(int $idRequest, array $data)`, `getRequests(int $idProyek, ?int $idTarget = null, ?int $tahunTarget = null, ?string $status = null)`, & `getRequestCounts(int $idProyek, ?int $idTarget = null, ?int $tahunTarget = null)`: Operasi CRUD dan query join `target_siteplan_request` dengan `target_siteplan`, `kavling`, `jalan`, `cluster`, `tipe` (`old_t`, `new_t`, `master_t`), dan `users` (`req_u`, `app_u`) beserta agregasi counter status.
  - `getAvailableYears(int $idProyek)`: Mengambil daftar `tahun_target` unik yang aktif (`status = 1`, `deleted_at IS NULL`) untuk proyek terkait.
  - `getKavlingTargetMap(int $idProyek, ?int $tahunTarget = null)` & `resolveTipeLabel(...)` (private): Pemetaan kavling target dengan filter tahun opsional, `LEFT JOIN tipe target_tipe`, serta atribut `id_tipe`, `tipe_manual`, `tipe_label`, `target_tipe_rumah`, `target_no_tipe_rumah`, dan `target_is_subsidi`.
- **Repository (`app/Repositories/Siteplan/OthersRepository.php`):**
  - `getAll(?int $id, int $idProyek, array $clusterIds, array $filters = [])`: Mengelola data aset penunjang non-kavling pada siteplan. Menerapkan pengelompokan filter scope (`siteplan`, `produksi`, serta `masalah` jika kategori 'Masalah' dicentang) dan secara eksplisit menyertakan `orWhere('act_tm.active_tiket_id IS NOT NULL', null, false)` agar entitas others berscope `masalah` dengan tiket aktif otomatis tampil di kanvas default.
  - `insertOthers(array $fields)`, `updateOthers(int $id, array $fields)`, `findById(int $id)`, `findProyekIdByJalanId(int $idJalan)`: Operasi CRUD & lookup relasi proyek untuk tabel pemilik `others`.
  - `addActiveTiketSelect(BaseBuilder $builder)`: Query JOIN ke derived table `tiket_masalah` (`ref_type = 'others'`, `status NOT IN ('selesai', 'batal', 'hold')`) untuk menyertakan `active_tiket_id`, `active_tiket_prioritas`, dan `active_tiket_status`.
- **Repository (`app/Repositories/Siteplan/KalendarRepository.php`):**
  - `getGroupsForUser()`, `getAllGroups()`, `getPembayaranEvents()`, `getJatuhTempoEvents()`, `getMkdtEvents()`: Query grup user, jadwal pembayaran, jatuh tempo, dan agenda MKDT.
- **Frontend JavaScript (`public/assets/js/siteplan/composite-shape.js`):**
  - `SiteplanCompositeShape.createKonvaShape(Konva, options)`: Merender poligon komposit multi-segmen (3-segmen) dan menggambar border poligon di luar `clip()` (`nativeContext.stroke()` saat `Number(options.strokeWidth) > 0`) agar border hitam pada kavling target tetap tampil utuh.
- **Frontend JavaScript (`public/assets/js/siteplan/tiket-overlay.js`):**
  - `SiteplanTiketOverlay`: Modul UMD layer animasi overlay tiket masalah di KonvaJS (`getPriorityColor`, `calculateOpacity`, `calculateCentroid`, `createOverlayShape`, `createBadgeShape`, `createAnimation`, `buildOverlayShapes`, `stopAnimation`, `clearOverlay`). Mendukung entitas `kavling` & `others`, safe centroid calculation, frame rate capping 30 FPS, pulsing high-contrast stroke, dan centroid warning badge `!`.
- **Frontend JavaScript (`public/assets/js/siteplan/target-overlay.js`):**
  - `SiteplanTargetOverlay`: Modul UMD layer overlay border luar blok kavling target pada KonvaJS (`DEFAULT_STROKE_COLOR` `'#000000'`, `DEFAULT_BORDER_WIDTH` `2.5`, `DEFAULT_GAP_BRIDGE` `2`, `MIN_GAP_BRIDGE` `0`, `MAX_GAP_BRIDGE` `12`; fungsi publik `normalizeGapBridge(value)`, `parsePoints(points)`, `computeBounds(polygons, padding)`, `tracePolygonPath(nativeContext, flatPoints)`, `createOverlayShape(Konva, polygons, options)`, `clearOverlay(layer)`, `buildOverlayShapes(Konva, layer, items, isBaseVisibleCallback, options)`). Menggunakan 2-pass Canvas composite (`source-over` dilasi `2 * (borderWidth + gapBridge)` lalu `destination-out` erosi `fill()` + `stroke(2 * gapBridge)` ketika `gapBridge > 0`) agar border internal antar kavling target berjejer menghilang dan hanya perimeter luar blok target yang tampil, lengkap dengan `getSelfRect()` terhitung.
- **Frontend JavaScript (`public/assets/js/siteplan/master.js`, `public/assets/js/siteplan/target.js`, `public/assets/js/siteplan/planning.js`, & `public/assets/js/siteplan/request-kavling.js`):**
  - `master.js`: `siteplanProblemItems` & `syncSiteplanTiketOverlay()` (overlay tiket masalah kavling/others), layer `targetOverlayLayer` (`stage.add(siteplan, targetOverlayLayer, tiketOverlayLayer, masked)`), state `siteplanTargetItems`, helper toleransi celah & persistensi `localStorage` (`targetGapBridgeStorageKey()`, `getSavedTargetGapBridge()`, `getTargetGapBridge()`, `applyTargetGapBridgeUI(value, persist)` dengan key `sigapp_siteplan_target_gap_bridge_<id_proyek>`), sinkronisasi overlay border luar target (`syncSiteplanTargetOverlay()` & `clearSiteplanTargetOverlay()` pada `load_kavling()`, `clearSiteplanDataShapes()`, `applyShapeVisibilityFilter()`, `reset_server_filter()`, dan `$(document).ready`), penyertaan metadata MKDT `akad`, `akad_tgl`, `akad_indent`, `akad_indent_tgl`, dan `nama_konsumen` pada data shape Konva, `window.isTargetSetModeActive()`, `window.scheduleTargetCanvasRefresh()`, pencegahan seleksi/context menu standar saat mode interaktif Set Target maupun mode Request Kavling Canvas Pick aktif (`window.isRequestKavlingCanvasPickActive()` meneruskan klik kanvas ke `window.handleRequestKavlingCanvasClick()`, menahan klik stage kosong, tombol `Escape` memanggil `window.cancelRequestKavlingCanvasPick()`, dan memanggil `window.renderRequestKavlingCanvasHighlights()` setelah `load_kavling()`), pemanggilan `window.renderTargetStagedCanvasHighlights()` setelah `load_kavling()`, plus kontrol tampilan target (`isTargetFilterActive()`, `selectedTargetYear()`, `selectedTargetOtherMode()`, `selectedTargetKavlingMode()`, `resolveTargetTipeColor()`, `hasValidTargetDate(val)`, `syncTargetMenuVisibility()`, `syncTargetYearDropdown(targetYears, preferredYear)` menjaga tahun aktif dalam rentang `-1..+3`), pewarnaan matriks 4 × 3 `resolveTargetCanvasHit(row, rawHargaAkhir, targetInfo, defaultHit)` (mengevaluasi `isBatal` sebagai prioritas tertinggi, mengklasifikasikan `isAkad` dari `row.akad == 1 || hasValidTargetDate(row.akad_tgl) || row.akad_indent == 1 || hasValidTargetDate(row.akad_indent_tgl)` agar status akad indent dihitung/diwarnai sebagai `Akad Subsidi`/`Akad Komersil` pada `split_4` & `Sudah Akad` pada `merge_2` selaras `SiteplanVisualStatusService::mkdtRow()`, menjaga fallback anomali `row.status_mkdt === 'Akad'` di `isBooking` saat flag/tanggal akad & akad indent kosong, menyertakan flag `isTargetKavling` serta `targetStroke: undefined` & `targetStrokeWidth: 0` karena border luar hitam `#000000` `2.5px` kini dirender di `targetOverlayLayer`; konsumen target & non-target mengikuti filter Selain Target `default`/`split_4`/`merge_2`/`single_1`; non-konsumen target mengikuti filter Kavling Target `default`/`single_color`/`by_tipe`; non-konsumen non-target mengikuti mode Selain Target atau `Bukan Target` `#e2e8f0`) & `get_kategori_color()` (`Sudah Akad`, `Booking`, `Sudah Memiliki Konsumen`, `Kavling Target`, `Akad Subsidi`, `Akad Komersil`, `Booking Subsidi`, `Booking Komersil`, `Tipe Target:`), serta sinkronisasi filter, legend (`Target`, `Tipe Target`), reset filter (`removeFilterTag('target')`, `reset_server_filter()`), dan tooltip (`setSiteplanTooltip()`).
  - `target.js`: Pengelola UI Mode Set Target MKDT, Request Perubahan Planning, & Modal Kelola Target: `open_target_siteplan()` (auto-toggle `#toggle-tampilkan-target`), `open_target_history()`, `open_target_request(preferredKavlingId)`, `enterTargetSetMode()`, `exitTargetSetMode()`, `resetTargetInteractiveCardScroll()` (menjaga `scrollTop = 0` pada `#target-interactive-card` saat dibuka atau mode/tipe berubah), dropdown tahun `-1..+3` sinkron ke `#filter-tahun-target`, `initTargetBarTipeSelect2()` & `initTargetEditTipeSelect2()` (Select2 AJAX `<strong>Tipe</strong> (No Tipe)`), `renderTargetBarTipePreview()` & `openTargetTipeSpecModal()` (fetch fallback `api/tipe/getone` untuk 3D/denah/spek), staging kavling kanvas (`handleTargetCanvasClick()`, `renderTargetStagedList()`, `renderTargetStagedCanvasHighlights()`, `submitStagedKavlings()`), `loadActiveTipes()` (`/api/tipe/all?only_active=1&format=raw`), deteksi *mismatch* `isTargetRowTipeMismatch()`, `renderCurrentKavlingTipeCell()`, `renderTipeTargetCell()` (badge `Manual` di ujung kanan tanpa badge `Master`), `renderTargetRowActions()`, guard checkbox Opsi A (`canEditTargetRow()` agar hanya kavling tanpa konsumen aktif yang dapat dicentang untuk aksi batch), modal edit tipe single & batch (`openTargetEditTipeModal()`, `submitEditTipeModal()` via `POST /api/target/update-kavling-tipe`), `confirmBatchRemoveTargetKavlings()` + state `targetState.isSubmittingBatchRemove` + handler `#target-btn-batch-remove` (`POST /api/target/remove-kavling` payload `id_target_kavlings`), `submitPlanningKavlingChangeRequest()` ke `POST /api/kavling-request/submit` (preview grup Swal terkelompok per tipe tujuan `master:{id_tipe}` / `manual:{lowercase}` & penyegaran badge via `window.refreshRequestKavlingCounts()`) & badge `Req Planning: Pending`, `confirmRemoveTargetKavling()` (`POST /api/target/remove-kavling`), `save_target_siteplan()` (`POST /api/target/save`), `submitInlineRequestChange()` (`POST /api/target/request-change`), `loadTargetRequests()` (`POST /api/target/requests`), `renderTargetRequests()` & `updateTargetPendingBadge()`, `reviewTargetRequest()` (`POST /api/target/review-request`), serta `render_target_history()` (kartu versi `v1..vN`, pill diff, komparasi Before vs After, dan `.target-history-notes`).
  - `planning.js`: Pengelola modal Planning `#modals-slide-in` (`app/Views/siteplan/planning.php`): Select2 `#id_tipe` via `POST /api/tipe/all?only_active=1&format=raw` (`is_active = 1`) dengan cache `planningTipeMap`, preview Gambar 3D + Denah + Spesifikasi Teknis (`renderPlanningTipePreview()`, `buildPlanningTipePreviewHtml()`, `hasCompletePlanningTipeSpec()`, fetch fallback `POST /api/tipe/getone` untuk mode edit, `planningApiUrl()`, `planningSanitizeAccessUrl()`), lightbox modal zoom (`openPlanningTipeLightbox()`), reset preview pada `tambah_kavling()` / `edit_kavling_batch()` / `$planningJenis.change`, pembacaan `#id_tipe option:selected` pada `add_kavling()`, serta penjagaan handler `stage.on('click.planningDeselect tap.planningDeselect')` agar mengabaikan klik kanvas saat `window.isTargetSetModeActive()` maupun `window.isRequestKavlingCanvasPickActive()` bernilai `true`.
  - `request-kavling.js` *(terdaftar pada `ModuleCatalog['Transaksi']['assets_js']`)*: Pengelola modal terpadu Request Kavling (`window.openModalRequestKavling`, `window.handleContextRequestKavling`, `window.refreshRequestKavlingCounts` = `loadCountsSummary`, tampilan `.rk-kavling-count-badge` pada `renderKavlingsCell()` & `openApplyUbahKavlingModal()` bila `kavlings.length > 1`, Konva canvas picker `window.isRequestKavlingCanvasPickActive`, `window.handleRequestKavlingCanvasClick`, `window.renderRequestKavlingCanvasHighlights`, `window.cancelRequestKavlingCanvasPick`, serta shim alias `window.openModalListRequestKavling`).
- **Frontend JavaScript (`public/assets/js/siteplan-detail-modal.js`):**
  - `resolveMkdtStatusInfo(m)`, `renderKavlingTimeline(mkdt, currentStatus)`, `updateRingkasanHasilAkad(pa)`: Resolusi hierarki status MKDT (Batal -> Akad AJB -> Akad Indent -> SP3K -> Wawancara -> Booking), stepper milestone horizontal Opsi B, dan bar/legend Total Hasil Akad.
- **Frontend View & CSS (`app/Views/siteplan/master.php`, `app/Views/siteplan/planning.php`, `app/Views/siteplan/target.php`, `app/Views/siteplan/request_kavling_modal.php`, `public/assets/css/siteplan-planning.css`, `public/assets/css/siteplan-target.css`, `public/assets/css/siteplan-request-kavling.css`, `public/assets/css/style.css`, & `app/Views/misc/kalender.php`):**
  - `app/Views/siteplan/master.php`: Memuat partial `siteplan/target` untuk role `[1, 4, 6, 11]`, menyertakan switch `#toggle-tampilkan-target` dan tombol icon-only `#btn-toggle-target-border-settings` di dalam `.siteplan-target-header-row`, panel collapsible `#target-border-settings-panel` (default tersembunyi) berisi kontrol toleransi celah (`#input-target-gap-bridge-range`, `#input-target-gap-bridge`, `#target-gap-bridge-value`), `#filter-target-tahun-container` (`#filter-tahun-target`, `#filter-target-other-mode`, `#filter-target-kavling-mode`), memuat `siteplan-target.css` serta `siteplan/target-overlay.js` sebelum `master.js` dengan cache-busting `filemtime`, menyelaraskan `@media screen and (max-width: 1366px)` `html { font-size: calc(70% * var(--sigapp-font-scale, 1)); }` dengan Pengaturan Scale Layar Global, serta menghapus opsi `Target` (`value="11"`) dari `#pilih-divisi`. Untuk Request Kavling: guard RBAC `[1, 4, 6]` (`$canAccessRequestKavling`, `$canCreateRequestKavling` `[1, 4]`, `$canProcessRequestKavling` `[1, 6]`), konsolidasi 1 tombol `#btn-open-request-kavling` + `#badge-request-kavling-pending`, pemuatan `siteplan-request-kavling.css` & `siteplan/request-kavling.js` dengan `filemtime`, pemuatan `siteplan/request_kavling_modal` khusus `[1, 4, 6]`, dan penghapusan partial `modal_list_request_kavling.php`.
  - `app/Views/siteplan/request_kavling_modal.php` & `public/assets/css/siteplan-request-kavling.css` (`1295` baris): Modal terpadu 3-tab (`Request Tambah Kavling`, `Request Ubah Kavling`, `Riwayat`), preview 3D/Denah/Spek, floating canvas picker bar `#rk-canvas-picker-bar`, sub-modal `#modal-apply-ubah-kavling` (beserta link `Tambah Tipe` ke `/tipe` `target="_blank"` & tombol `Refresh Tipe`), batas scroll `.rk-chip-stack` (`max-height: 180px` tabel / `220px` modal apply) & `.rk-kavling-count-badge`, modal preview `#modal-rk-tipe-preview`, dan modal lightbox `#modal-rk-lightbox` beserta dukungan `.dark-layout` dan responsif mobile.
  - `public/assets/css/style.css` *(global stylesheet)*: Memperbaiki kontras global `.badge.badge-light`, `.btn-primary .badge.badge-light.badge-nup-count` (`background-color: #ffffff`, `color: #2057a3`), dan varian `.dark-layout` agar badge Daftar NUP terbaca jelas.
  - `app/Views/siteplan/planning.php` & `public/assets/css/siteplan-planning.css`: Memuat `siteplan-planning.css?v=filemtime(...)` (ter-scope `#modals-slide-in`, `#modal-planning-tipe-image`, `.planning-modal-actions`, `.planning-tipe-preview*`, `.dark-layout`, dan responsif `< 575.98px` / `< 340px`), menambahkan kontainer `#planning-tipe-preview` di dalam `#div_kavling`, modal lightbox `#modal-planning-tipe-image`, dan cache-busting `filemtime` pada tag script `planning.js`.
  - `app/Views/siteplan/target.php`: Merender Floating Interactive Card `#target-interactive-card` (layout portrait di dalam `#target-interactive-bar`, `<select id="target-bar-year">`, `#target-bar-tipe-preview`, `#target-bar-staged-list`), Modal `#modal-target-siteplan` dengan 3 tab (**Daftar Kavling Target** dilengkapi `#target-check-all`, kolom `Tipe Rumah Saat Ini`, `#target-batch-action-bar` + `#target-btn-batch-edit-tipe` + `#target-btn-batch-req-planning` + `#target-btn-batch-remove`; **History Versioning**; dan **Request Perubahan** `#tab-target-requests`), serta 2 modal pendukung: `#modal-target-tipe-spec` (3D, denah, spek compact) dan `#modal-target-edit-tipe` (Select2 AJAX single & batch).
  - `public/assets/css/siteplan-target.css` (`2812` baris): Stylesheet ter-scope `#filter-side` (`.siteplan-target-header-row`, `.siteplan-target-settings-btn`, `.siteplan-target-settings-panel`, `.siteplan-target-settings-row`, `.siteplan-target-settings-value`, `.siteplan-target-settings-controls`, `.siteplan-target-gap-range` beserta override penuh pseudo-element `.custom-range` `::-webkit-slider-runnable-track`/`::-webkit-slider-thumb`/`::-moz-range-track`/`::-moz-range-thumb` `:hover`/`:active`/`:focus` memakai token `#2057a3`/`#163f78` dan `.dark-layout` `#4c86d4`/`#6ba0e6`/`#3b4253`, `.siteplan-target-gap-input`, `.siteplan-target-year-box`, `.siteplan-target-subfilter`, `.siteplan-target-subfilter-label`, `.siteplan-target-subfilter-select`), `#target-interactive-bar` (`max-height` & `is-fixed-fallback` di atas `.float`), `#target-interactive-card` (`flex-shrink: 0; box-sizing: border-box;` pada child sections + preview/staged list padat), `#modal-target-siteplan` (termasuk `#target-btn-batch-remove`), `#modal-target-tipe-spec`, `#modal-target-edit-tipe`, `.target-row-tipe-mismatch`, `.target-mismatch-note`, `.target-batch-action-bar`, `.target-planning-req-badge`, `.target-inline-req-box`, `#target-requests-table`, `.target-history-notes`, `.swal2-popup` (`.target-planning-swal-summary`, `.target-planning-swal-group*`, `.target-remove-swal-*`), dan `.siteplan-target-*` dengan aksen biru `#2057a3`, responsivitas mobile `< 768px`, dan dukungan `.dark-layout`.
  - `app/Views/misc/kalender.php`: Menggunakan `const kalendarBaseUrl` (menggantikan `var base_url`), callback error `failureCallback(new Error(...))`, dan fallback warna event `#2057a3`.
- **Models (`app/Models/TargetSiteplanKavlingModel.php` & `app/Models/TargetSiteplanRequestModel.php`):**
  - `TargetSiteplanKavlingModel`: `$allowedFields` mencakup `id_target`, `id_kavling`, `id_tipe`, `tipe_manual`, `created_at`, `updated_by`, `updated_at` untuk pemetaan tipe target per kavling dan audit perubahan.
  - `TargetSiteplanRequestModel`: Model baru untuk tabel `target_siteplan_request` (`id_request`, `id_target`, `id_kavling`, `old_id_tipe`, `old_tipe_manual`, `new_id_tipe`, `new_tipe_manual`, `catatan_request`, `status`, `request_by`, `request_at`, `approve_by`, `approve_at`, `catatan_approval`) yang disiapkan untuk alur request ubah tipe target Planning & approval MKDT.

## Deprecated & Legacy Shims
- **Deprecated Classes / Zero-Downtime Shims (13 File):**
  - Controllers Shim -> **DEPRECATED** (dipertahankan hanya untuk kompatibilitas backward; gunakan `App\Controllers\Web\Siteplan\*` atau `App\Controllers\Api\Siteplan\*`):
    - `app/Controllers/Siteplan.php`
    - `app/Controllers/Modal.php`
    - `app/Controllers/Kalendar.php`
    - `app/Controllers/Api/TargetController.php`
  - Services Shim -> **DEPRECATED** (gunakan namespace kanonik `App\Services\Siteplan\*`):
    - `app/Services/SiteplanDataService.php`
    - `app/Services/SiteplanMenuService.php`
    - `app/Services/SiteplanUrgentService.php`
    - `app/Services/SiteplanVisualStatusService.php`
    - `app/Services/TargetSiteplanService.php`
    - `app/Services/KalendarService.php`
  - Repositories Shim -> **DEPRECATED** (gunakan namespace kanonik `App\Repositories\Siteplan\*`):
    - `app/Repositories/TargetSiteplanRepository.php`
    - `app/Repositories/OthersRepository.php`
    - `app/Repositories/KalendarRepository.php`
- **Legacy Zero-Downtime AJAX Routes (Dialihkan ke Modular Controller & Mendukung `?format=raw`):**
  - `POST /siteplan/get/all`, `/siteplan/getAllKavling` -> dialihkan ke `Api\Siteplan\SiteplanController::getAllKavling`; untuk klien REST modern gunakan `GET|POST /api/siteplan/kavling`.
  - `POST /siteplan/get/detail`, `/siteplan/get_detail` -> dialihkan ke `Api\Siteplan\SiteplanController::get_detail`; gunakan `GET /api/siteplan/detail/(:num)` atau `POST /api/siteplan/detail`.
  - `POST /siteplan/getKategoriOptions`, `/siteplan/urgent/summary`, `/siteplan/get_others`, `/siteplan/add_kavling`, `/siteplan/edit_kavling`, `/siteplan/edit_others`, `/siteplan/get_kavling_by_multiple_id`, `/siteplan/get_harga_kavling`, `/siteplan/get_turun_pembangunan`, `/siteplan/set_turun_pembangunan`, `/siteplan/get_kavling_by_id` -> rute eksplisit legacy AJAX ke `Api\Siteplan\SiteplanController` (menggantikan AutoRoute lama).
  - `GET|POST /kalendar/getEvents`, `/kalendar/getevents` -> rute eksplisit legacy AJAX ke `Api\Siteplan\KalendarController::getEvents`; gunakan `GET /api/kalendar/events` atau `/api/siteplan/kalendar/events`.
- **Legacy Internal Helper Methods yang Dihapus dari Public Controller:**
  - `Siteplan::add_kavling2()`, `Siteplan::add_others()`, `Siteplan::get_config()`, `Siteplan::normalizeIdProyek()` -> **REMOVED / ENCAPSULATED**, logika telah dienkapsulasi ke `App\Services\Siteplan\SiteplanService` dan `App\Repositories\Siteplan\SiteplanRepository`.
- **Deprecated UI Controls / Legacy Frontend Target Branch:**
  - `<option value="11">Target</option>` pada `#pilih-divisi` (`app/Views/siteplan/master.php`) dan cabang `if (va == 11)` pada `public/assets/js/siteplan/master.js` -> **DEPRECATED / REMOVED**, digantikan oleh Switch `#toggle-tampilkan-target`, Dropdown `#filter-tahun-target`, serta fungsi `isTargetFilterActive()` dan `resolveTargetCanvasHit()`.
  - `<input type="number" list="target-bar-year-list">` + `<datalist id="target-bar-year-list">` (`app/Views/siteplan/target.php`) -> **DEPRECATED / REMOVED**, digantikan oleh `<select id="target-bar-year">` dengan rentang tahun `-1..+3`.
  - `populateActiveTipeSelect()` untuk `#target-bar-tipe-select` (`public/assets/js/siteplan/target.js`) -> **DEPRECATED / REMOVED**, digantikan oleh `initTargetBarTipeSelect2()` (Select2 AJAX via `/api/tipe/all?only_active=1&format=raw`).
  - Inline MKDT tipe editor (`submitInlineTipeUpdate`) (`public/assets/js/siteplan/target.js`) -> **DEPRECATED / REMOVED**, digantikan oleh modal `#modal-target-edit-tipe` (`openTargetEditTipeModal()` / `submitEditTipeModal()`) yang mendukung ubah tipe tunggal maupun batch via Select2 AJAX.
  - Pemanggilan legacy `POST /tipe/getAll` pada Select2 `#id_tipe` di `public/assets/js/siteplan/planning.js` -> **DEPRECATED / REPLACED**, digantikan oleh `POST /api/tipe/all?only_active=1&format=raw` (`Api\Proyek\TipeController::getAll`, hanya tipe aktif `is_active = 1`). Rute legacy `/tipe/getAll` tetap dipertahankan untuk pemanggil lain jika masih digunakan di modul lain.
  - `app/Views/siteplan/modal_list_request_kavling.php` -> **DEPRECATED / UNMOUNTED**: Tidak lagi dipanggil dari `app/Views/siteplan/master.php`; antarmuka daftar dan tindak lanjut Planning telah dikonsolidasikan ke `app/Views/siteplan/request_kavling_modal.php` (modal terpadu 3-tab).
  - Tombol ganda sidebar (`openModalRequestKavling()` & `openModalListRequestKavling()`) di `app/Views/siteplan/master.php` -> **DEPRECATED / CONSOLIDATED**, digantikan 1 tombol terpadu `#btn-open-request-kavling` (`openModalRequestKavling()`), sementara `window.openModalListRequestKavling()` di `public/assets/js/siteplan/request-kavling.js` dipertahankan sebagai shim alias ke `window.openModalRequestKavling({ tab: 'tambah' })`.
- **Retained Backward-Compatibility Helpers (Tidak Dideprecate pada Fitur `2026-11-21` Batch Remove):**
  - `TargetSiteplanRepository::deleteTargetKavling(int $idTargetKavling): bool` tetap dipertahankan di samping `deleteTargetKavlings(array $idTargetKavlings): bool`.
  - Helper privat `TargetSiteplanService::locateTargetKavlingRow(array $data): object` tetap dipertahankan di samping `locateTargetKavlingRows(array $data): array`.
- **Penggantian Mekanisme Stroke Target Individual (`2026-11-21` Border Luar Blok Target):**
  - Tidak ada fungsi, route, atau class shim yang di-deprecate. Perilaku rendering stroke target individual per-shape (`targetStroke: '#000000'`, `targetStrokeWidth: 2.5`) pada `resolveTargetCanvasHit()` di `public/assets/js/siteplan/master.js` digantikan oleh layer terisolasi `targetOverlayLayer` melalui `SiteplanTargetOverlay` (`public/assets/js/siteplan/target-overlay.js`), dengan `resolveTargetCanvasHit()` kini mengembalikan flag `isTargetKavling` serta `targetStroke: undefined` & `targetStrokeWidth: 0`. Opsi `strokeWidth` pada `SiteplanCompositeShape.createKonvaShape` (`public/assets/js/siteplan/composite-shape.js`) tetap dipertahankan backward-compatible.

## Readiness Checklist
- [x] Has Service Layer
- [x] Has Repository Layer
- [x] Web & API Controllers Separated
- [x] Explicit Routes Defined
- [x] Zero Controller Layer Violations
- [x] MODULE.md Initialized

## Dead Code Candidates
_Jalankan `php spark module:deadcode Siteplan` untuk mengaudit dead code kandidat._

## Notes
- Modul telah direfaktor penuh menjadi arsitektur modular (`REFACTORED`, `100/100`) dengan zero-downtime shims dan endpoint REST API Full JSON (`RAW_JSON_READY`).
- **Cross-Module Callers & Integrations:**
  - `App\Services\Settings\SiteplanMenuService`: di-inject opsional ke `SiteplanService` untuk menyediakan `siteplan_menus` (`getMenusForSpa($roleId)`) pada payload `GET /api/siteplan/meta`.
  - `App\Controllers\Home`: mengonsumsi `SiteplanUrgentService::getDashboardAlertCounts()` untuk kartu peringatan dashboard utama.
  - `App\Controllers\Api\Notifikasi\NotifikasiController`: mengonsumsi `SiteplanUrgentService::{getUrgentSummary,emptySummary,snoozeUrgentItem}` untuk pusat notifikasi mendesak.
  - Terintegrasi dengan modul `Proyek` (`TipeService` / `POST /api/tipe/all?only_active=1&format=raw`, `POST /api/tipe/getone`, & `POST /api/jalan/all?format=raw`) serta `FileAccessService` (`/files/gambar_kerja/{id}`) untuk Select2 AJAX tipe aktif (`is_active = 1`), preview gambar 3D, denah, gambar kerja, dan 6 spesifikasi teknis pada `#target-interactive-card`, `#modal-target-tipe-spec`, modal Planning `#modals-slide-in` (`#planning-tipe-preview` & `#modal-planning-tipe-image`), serta modal terpadu Request Kavling (`#modal-request-kavling`, `#modal-apply-ubah-kavling`, `#modal-rk-tipe-preview`, `#modal-rk-lightbox`). Catatan katalog aset: `public/assets/css/siteplan-planning.css`, `public/assets/css/siteplan-target.css`, dan `public/assets/css/siteplan-request-kavling.css` terdaftar pada `ModuleCatalog['Siteplan']['assets_css']`, sedangkan `public/assets/js/siteplan/planning.js` tercatat pada `ModuleCatalog['Proyek']['assets_js']` dan `public/assets/js/siteplan/request-kavling.js` tercatat pada `ModuleCatalog['Transaksi']['assets_js']`.
  - Terintegrasi dengan modul `TiketMasalah` dan `Transaksi`:
    - `TargetSiteplanRepository::getLatestPlanningChangeRequests()` membaca tabel relasi `kavling_request_kavling` JOIN `kavling_request` (`jenis_request = 'ubah_tipe'`, plus fallback kolom legacy `kavling_request.id_kavling`) untuk menampilkan status `Req Planning: Pending` pada daftar kavling target, sementara `public/assets/js/siteplan/target.js` (`submitPlanningKavlingChangeRequest()`) mengirimkan pengajuan ubah tipe kavling (dikelompokkan per tipe tujuan di preview Swal maupun backend) ke `POST /api/kavling-request/submit` (`Transaksi\KavlingRequestController::submit()`) dan menyegarkan badge sidebar via `window.refreshRequestKavlingCounts()`.
    - Modal terpadu Request Kavling (`app/Views/siteplan/request_kavling_modal.php`, `public/assets/css/siteplan-request-kavling.css`, `public/assets/js/siteplan/request-kavling.js`) mengonsumsi endpoint modul `Transaksi` (`/api/kavling-request/{submit,kavling-list,list,update-status,apply-ubah-kavling}`) dengan guard RBAC `[1, 4, 6]` dan integrasi Konva canvas picker di `master.js` & `planning.js`.
    - `KavlingRepository::baseQuery()` (`app/Repositories/Transaksi/KavlingRepository.php`): mengonsolidasikan field MKDT (`status_mkdt`, `is_batal`, `booking_tgl`, `wawancara`, `wawancara_tgl`, `sp3k`, `sp3k_tgl`, `akad_indent`, `akad_indent_tgl`, `akad`, `akad_tgl`, `is_kpr`, `mkdt_is_subsidi`) agar tersedia di semua mode divisi (termasuk saat mode Tampilkan Target aktif) tanpa duplikasi alias SQL.
    - `KavlingRepository::addActiveTiketSelect`: data kavling dari `POST /siteplan/get/all` dan `/api/siteplan/kavling` menyertakan atribut `active_tiket_id`, `active_tiket_prioritas`, dan `active_tiket_status` untuk kavling bermasalah aktif.
    - `OthersRepository::addActiveTiketSelect`: data fasum/others dari `POST /siteplan/get_others` dan `/api/siteplan/others` menyertakan atribut `active_tiket_id`, `active_tiket_prioritas`, dan `active_tiket_status` untuk fasilitas non-kavling bermasalah aktif.
    - `OthersRepository::getAll()`: menyertakan kondisi `orWhere('act_tm.active_tiket_id IS NOT NULL', null, false)` sehingga item others berscope `masalah` otomatis termuat dan teranimasi pulse stroke serta badge peringatan `!` pada default canvas siteplan tanpa mengharuskan pengguna mencentang filter kategori 'Masalah'.
    - `SiteplanTiketOverlay` (`public/assets/js/siteplan/tiket-overlay.js`) dan `master.js` (`syncSiteplanTiketOverlay`): mengagregasikan masalah kavling dan others secara atomik dan merender `tiketOverlayLayer` pada peta KonvaJS.
    - `SiteplanTargetOverlay` (`public/assets/js/siteplan/target-overlay.js`) dan `master.js` (`targetOverlayLayer`, `syncSiteplanTargetOverlay`, `clearSiteplanTargetOverlay`, `resolveTargetCanvasHit`): merender border luar blok kavling target pada `targetOverlayLayer` (`siteplan -> targetOverlayLayer -> tiketOverlayLayer -> masked`), tersinkronisasi dengan filter legend (`applyShapeVisibilityFilter`), persistensi toleransi celah `localStorage` per proyek (`sigapp_siteplan_target_gap_bridge_<id_proyek>`), dan kompatibel dengan Export Siteplan (`renderSiteplanExportCanvas`). Kontrak JS publik yang diekspor: `SiteplanTargetOverlay.{DEFAULT_STROKE_COLOR, DEFAULT_BORDER_WIDTH, DEFAULT_GAP_BRIDGE, MIN_GAP_BRIDGE, MAX_GAP_BRIDGE, normalizeGapBridge, parsePoints, computeBounds, tracePolygonPath, createOverlayShape, clearOverlay, buildOverlayShapes}` (consumer aktif: `public/assets/js/siteplan/master.js` dan `tests/js/siteplan-target-overlay.test.js`).

### Audit Verifikasi Migrasi (2026-11-22)
- **Status Ledger:** `COMPLETED (Catatan Minor)` (`REFACTORED`, Readiness Score `100/100`).
- **Verifikasi Controller, Shim, & Routes:** Seluruh 6 controller modular (3 Web, 3 Api) dan 58 rute eksplisit bersih tanpa pelanggaran layer (`0` rute legacy, `0` autoroute). Shim `app/Controllers/Siteplan.php` (111 baris, terdeteksi `fat_legacy` pada pre-scan) terverifikasi sebagai thin delegator murni ke `Web\Siteplan\SiteplanController` dan `Api\Siteplan\SiteplanController` tanpa logika bisnis maupun akses DB.
- **Temuan Layering pada Service:**
  - `app/Services/Siteplan/SiteplanService.php` (`resolvePrimaryRoleId()`, L932): query langsung `$this->db->table('auth_groups_users')` alih-alih melalui `SiteplanRepository`.
  - `app/Services/Siteplan/TargetSiteplanService.php`: memanggil `$this->db->table('target_siteplan_kavling')` langsung di `addKavlingToYear()` (L303) dan `save()` (L634-636, L649), serta membaca tabel Auth `users` langsung di `resolveUsername()` (L1295).
  - `app/Services/Siteplan/TargetSiteplanService.php` (`__construct()`, L31-33): masih menginstansiasi `TargetSiteplanModel`, `TargetSiteplanKavlingModel`, dan `TargetSiteplanHistoryModel` ke properti yang tidak terpakai.

## Change Log
- **`2026-11-24` [`UI_REPORT`] — Integrasi Variabel Global Display Scale (`--sigapp-font-scale`) pada Siteplan Layar Kecil (`<= 1366px`):**
  - **Routes & Public Contracts:** Tidak ada perubahan rute HTTP maupun kontrak API/Service backend.
  - **Controller / Service / Repo / Model:** Tidak ada perubahan backend.
  - **View / CSS / JS:**
    - `app/Views/siteplan/master.php`: Memperbarui blok `@media screen and (max-width: 1366px)` dari `html { font-size: 70%; }` menjadi `html { font-size: calc(70% * var(--sigapp-font-scale, 1)); }` agar pengaturan *Font Scale* dari modal global (`#sigapp-display-scale-modal`) tetap berfungsi proporsional di layar `<= 1366px` tanpa menimpa skala font pengguna.
  - **Deprecations:** Tidak ada.
- **`2026-11-24` [`BUGFIX`] — Klasifikasi `akad_indent = 1` sebagai Akad pada Filter Target (`#filter-target-other-mode`):**
  - **Routes & Public Contracts:** Tidak ada perubahan rute maupun kontrak API backend.
  - **Controller / Service / Repo / Model:** Tidak ada perubahan (`SiteplanVisualStatusService::mkdtRow()` dan `KavlingRepository::baseQuery()` sudah selaras).
  - **View / CSS / JS:** `public/assets/js/siteplan/master.js` (`5180` baris, L741–756, `+2/-2` baris) — pada `resolveTargetCanvasHit(row, rawHargaAkhir, targetInfo, defaultHit)`, memindahkan `row.akad_indent == 1` dan `hasValidTargetDate(row.akad_indent_tgl)` dari `isBooking` ke `isAkad` (dengan guard `!isBatal`), sambil mempertahankan `row.status_mkdt === 'Akad'` di `isBooking` sebagai fallback anomali apabila flag/tanggal akad maupun akad indent kosong. Saat `#toggle-tampilkan-target` aktif, kavling akad indent kini dihitung dan diwarnai sebagai Kavling Akad (`Akad Subsidi`/`Akad Komersil` di mode `split_4` dan `Sudah Akad` di mode `merge_2`) untuk kavling target maupun non-target.
  - **Deprecations:** Tidak ada.
- **`2026-11-23` [`API-BRIDGE`] — Pengayaan Payload `/api/siteplan/kavling` (`pa_tanggal_cair_last`) via `KavlingRepository`:**
  - **Routes & Public Contracts:** Tidak ada rute baru; payload item kavling pada `GET|POST /api/siteplan/kavling` (`SiteplanDataService::getKavlings()` $\rightarrow$ `KavlingRepository::getAll()`) kini menyertakan field *backward-compatible* `pa_tanggal_cair_last` (tanggal pencairan hasil akad terakhir `MAX(pay.tanggal_cair)`) untuk kartu inspector `SiteplanInspectorCard.vue` di `sigapp-vue`.
  - **Controller / Service / Repo / Model:** Perubahan query berada pada modul pemilik `Transaksi` (`app/Repositories/Transaksi/KavlingRepository.php`::`addPencairanAkadSelect()`, `LEFT JOIN` subquery `papay`), 100% *Zero-Downtime* untuk web legacy `sigapp.dev`.
  - **Deprecations:** Tidak ada.
- **`2026-11-23` [`FEATURE`] — Penyelarasan Flag `update_tanggal_pembangunan` untuk Modal Produksi Siteplan (`#modal_divisi7`):**
  - **Routes & Public Contracts:** `GET /api/siteplan/meta` dan state `getViewSiteplanData()` mengembalikan `has_akses.update_tanggal_pembangunan = true` untuk role `1` (Admin), `7` (Produksi), dan `8` (Direksi).
  - **Controller / Service / Repo / Model:** `app/Services/Siteplan/SiteplanService.php` menyelaraskan `getViewSiteplanData()` dan `getMeta()` untuk `update_tanggal_pembangunan` tanpa mengubah `getProduksiMobileData()` (`produksi_mobile.php` tetap utuh).
  - **View / CSS / JS:** Perubahan frontend terkait modal Produksi dicatat pada modul `Produksi` (`app/Views/siteplan/produksi.php`, `public/assets/js/siteplan/produksi.js`, `public/assets/js/scripts.js`, `public/assets/css/produksi.css`).
  - **Deprecations:** Tidak ada.
- `2026-11-22` [`AUDIT`] — Audit verifikasi migrasi modular (`COMPLETED (Catatan Minor)`, Score `100%`): 6 controller modular, 58 explicit routes, dan 13 shims terverifikasi bersih (`Siteplan.php` murni delegator); dicatat temuan minor query `$this->db->table()` langsung pada `SiteplanService` (L932) dan `TargetSiteplanService` (L303, L634, L649, L1295) serta 3 instansiasi Model tak terpakai pada `TargetSiteplanService::__construct()` (L31-33).
- **`2026-11-21` [`UI_REPORT`] — Border Luar Blok Kavling Target di Kanvas Siteplan (Opsi 1: `targetOverlayLayer` & Toleransi Celah):**
  - **Routes & Public Contracts:** Tidak ada perubahan Backend, Database, maupun Routes (`RAW_JSON_READY`). Kontrak JS publik baru `SiteplanTargetOverlay` (`public/assets/js/siteplan/target-overlay.js`), serta `resolveTargetCanvasHit()` di `public/assets/js/siteplan/master.js` kini mengembalikan flag `isTargetKavling` dengan `targetStroke: undefined` & `targetStrokeWidth: 0`.
  - **Controller / Service / Repo / Model / Catalog:** Tidak ada perubahan backend; mendaftarkan `'siteplan/target-overlay.js'` pada `ModuleCatalog['Siteplan']['assets_js']` di `app/Libraries/ModuleManager/ModuleCatalog.php` dan sinkronisasi via `php spark module:sync Siteplan`.
  - **View / CSS / JS / Test:**
    - `public/assets/js/siteplan/target-overlay.js` [BARU] (`363` baris): Modul UMD `SiteplanTargetOverlay` untuk merender border luar blok kavling target pada `targetOverlayLayer` terisolasi menggunakan 2-pass Canvas composite (`source-over` dilasi `2 * (borderWidth + gapBridge)` lalu `destination-out` erosi `fill()` + `stroke(2 * gapBridge)` ketika `gapBridge > 0`).
    - `tests/js/siteplan-target-overlay.test.js` [BARU] (`423` baris): 19 unit test Jest untuk `SiteplanTargetOverlay` (`normalizeGapBridge`, `parsePoints`, `computeBounds`, `tracePolygonPath`, `createOverlayShape`, `clearOverlay`, `buildOverlayShapes`).
    - `app/Views/siteplan/master.php`: Menambahkan tombol icon-only `#btn-toggle-target-border-settings` di `.siteplan-target-header-row` dan panel collapsible `#target-border-settings-panel` (default tersembunyi) berisi slider & input angka toleransi celah (`#input-target-gap-bridge-range`, `#input-target-gap-bridge`, `#target-gap-bridge-value`), serta memuat `assets/js/siteplan/target-overlay.js?v=<?= filemtime(...) ?>` sebelum `master.js`.
    - `public/assets/css/siteplan-target.css` (`2812` baris): Menambahkan styling scoped di bawah `#filter-side` dan `.dark-layout #filter-side` untuk header row target, tombol settings icon-only, panel toleransi celah, input angka, dan override penuh pseudo-element `.custom-range` (`::-webkit-slider-runnable-track`, `::-webkit-slider-thumb`, `::-moz-range-track`, `::-moz-range-thumb`, `:hover`, `:active`, `:focus`) dengan token warna `#2057a3` / `#163f78` (dark `#4c86d4` / `#6ba0e6`).
    - `public/assets/js/siteplan/master.js` (`5184` baris): Menambahkan `targetOverlayLayer` (`stage.add(siteplan, targetOverlayLayer, tiketOverlayLayer, masked)`), state `siteplanTargetItems`, `syncSiteplanTargetOverlay()`, `clearSiteplanTargetOverlay()`, persistensi `localStorage` (`sigapp_siteplan_target_gap_bridge_<id_proyek>`), sinkronisasi di `load_kavling()`, `clearSiteplanDataShapes()`, dan `applyShapeVisibilityFilter()`, serta pemulihan nilai di `reset_server_filter()` dan `$(document).ready`.
  - **Deprecations & Verification:** Tidak ada fungsi/route yang di-deprecate (perilaku stroke target individual per-shape pada `master.js` digantikan oleh `targetOverlayLayer` sementara opsi `strokeWidth` di `SiteplanCompositeShape.createKonvaShape` tetap dipertahankan backward-compatible). Terverifikasi QA Putaran 2 `PASS` (`0` console error, `0` broken callers, `php spark module:deadcode Siteplan` `0` kandidat, & `8/8` Jest suite Siteplan / `104/104` tests lulus).
- **`2026-11-21` [`FEATURE`] — Hapus Kavling Target Batch (Opsi A) & Grouping Request Perubahan Tipe ke Planning per Tipe Tujuan:**
  - **Routes & Public Contracts (`RAW_JSON_READY`):**
    - Memperluas kontrak `POST /api/target/remove-kavling` (`App\Controllers\Api\Siteplan\TargetController::removeKavling()`): mendukung input batch (`id_target_kavlings`, atau pasangan `id_target` + `id_kavlings`) di samping single (`id_target_kavling`, `id_target` + `id_kavling`), validasi satu proyek (`422` bila lintas proyek), pesan dinamis (`N kavling berhasil dihapus dari target siteplan`), serta mengembalikan `{ target, kavlings, history, batch_summary: { removed_count, id_target_kavlings, removed_kavling_ids } }`.
    - Terintegrasi dengan pembaruan kontrak `POST /api/kavling-request/submit` (modul `Transaksi`): pengajuan batch `items[]` dari Target Siteplan kini mengelompokkan item per tipe tujuan (`master:{id_tipe}` / `manual:{lowercase}`) dan mengembalikan `request_count`, `kavling_count`, serta `groups`.
  - **Controller / Service / Repo / Model / Test:**
    - `app/Repositories/Siteplan/TargetSiteplanRepository.php` (`935` baris): Menambahkan `deleteTargetKavlings(array $idTargetKavlings): bool`; tetap mempertahankan `deleteTargetKavling(int $idTargetKavling): bool`.
    - `app/Services/Siteplan/TargetSiteplanService.php` (`1316` baris): Memperluas `removeKavling(array $data, int $actorId): array` mendukung single & batch atomik dalam 1 transaksi DB melalui `locateTargetKavlingRows()`, tanpa `assertNoActiveConsumers()`, mencatat tepat 1 entri Versioning History (`target_remove_kavling`) per target via `recordTargetHistory()`, dan mengembalikan `batch_summary`; tetap mempertahankan `locateTargetKavlingRow()`.
    - `app/Controllers/Api/Siteplan/TargetController.php` (`645` baris): Memperluas `removeKavling()` untuk normalisasi list ID (`normalizeIdList()`), resolusi proyek batch melalui `resolveProjectIdsForTargetKavlings()`, dan pesan respons dinamis.
    - `tests/unit/TargetSiteplanServiceTest.php`: Menambahkan tabel `db_kavling_request_kavling` pada schema test serta menambah test `testKavlingRequestBatchGroupedByTargetTipe()` dan `testRemoveKavlingBatchRecordsSingleHistoryAndBatchSummary()` (`15 tests, 267 assertions` `PASS`).
  - **View / CSS / JS:**
    - `app/Views/siteplan/target.php`: Menambahkan tombol `#target-btn-batch-remove` (`Hapus dari Target`) pada `#target-batch-action-bar .target-batch-action-buttons`.
    - `public/assets/js/siteplan/target.js` (`3545` baris): Mempertahankan guard checkbox Opsi A (`canEditTargetRow()` — hanya kavling tanpa konsumen aktif yang dapat dicentang untuk aksi batch), menambahkan `targetState.isSubmittingBatchRemove`, `confirmBatchRemoveTargetKavlings()`, handler klik `#target-btn-batch-remove`, serta pembaruan `submitPlanningKavlingChangeRequest()` (preview grup Swal terkelompok per tipe dan penyegaran badge via `window.refreshRequestKavlingCounts()`).
    - `public/assets/css/siteplan-target.css` (`2587` baris): Menambahkan styling scoped `#modal-target-siteplan #target-btn-batch-remove`, `.target-planning-swal-summary`, `.target-planning-swal-group*`, dan `.target-remove-swal-*` (light, `.dark-layout`, dan responsivitas mobile `< 768px`).
    - `public/assets/css/siteplan-request-kavling.css` (`1295` baris): Menambahkan batas tinggi & scroll (`max-height` + `overflow-y: auto`) pada `.rk-chip-stack` (tabel & `#modal-apply-ubah-kavling`) serta styling `.rk-kavling-count-badge` (light & `.dark-layout`).
    - `public/assets/js/siteplan/request-kavling.js` (`2051` baris — terdaftar di katalog `Transaksi`): Mengekspos `window.refreshRequestKavlingCounts = loadCountsSummary` serta menampilkan `.rk-kavling-count-badge` (`N kavling`) pada `renderKavlingsCell()` dan `openApplyUbahKavlingModal()` bila `kavlings.length > 1`.
  - **Deprecations & Verification:** Tidak ada fungsi/route baru yang di-deprecate (`deleteTargetKavling()` dan `locateTargetKavlingRow()` tetap dipertahankan). Terverifikasi QA `PASS` (`php spark module:sync Siteplan`, `php spark routes`, `TargetSiteplanServiceTest` `15 tests, 267 assertions`, `php spark module:deadcode Siteplan` `0` kandidat, & Playwright E2E role MKDT & Planning).
- **`2026-11-21` [`FEATURE` + `BUGFIX`] — Perbaikan Kontras Badge Daftar NUP & Revamp UI/Integrasi Request Kavling Terpadu (Modal 3-Tab, Canvas Picker, Preview Tipe, & Terapkan Ubah Tipe):**
  - **Routes & Public Contracts:** Tidak ada rute baru pada controller Siteplan; modul `Siteplan` mengintegrasikan endpoint baru/terbarui modul `Transaksi` (`POST /api/kavling-request/{submit,kavling-list,list,update-status,apply-ubah-kavling}`) serta kontrak `Proyek` (`/api/jalan/all?format=raw`, `/api/tipe/all?only_active=1&format=raw`).
  - **Controller / Service / Repo / Model:**
    - `app/Repositories/Siteplan/TargetSiteplanRepository.php` (`919` baris): Memperbarui `getLatestPlanningChangeRequests()` agar membaca tabel relasi `kavling_request_kavling` JOIN `kavling_request` (`jenis_request = 'ubah_tipe'`) digabung fallback kolom legacy `kavling_request.id_kavling` dan memprioritaskan status `pending`.
    - `app/Libraries/ModuleManager/ModuleCatalog.php`: Mendaftarkan `siteplan-request-kavling.css` pada `Siteplan['assets_css']` serta `kavling_request` & `kavling_request_kavling` pada `Siteplan['shared_tables']`.
  - **View / CSS / JS:**
    - `public/assets/css/style.css` (`1973` baris): Menambahkan override kontras global `.badge.badge-light`, `.btn-primary .badge.badge-light.badge-nup-count` (`background-color: #ffffff`, `color: #2057a3`), serta varian `.dark-layout` untuk memperbaiki teks putih-di-atas-putih pada badge Daftar NUP.
    - `public/assets/css/siteplan-request-kavling.css` [BARU] (`1261` baris): Stylesheet ter-scope untuk modal 3-tab Request Kavling, floating canvas picker bar `#rk-canvas-picker-bar`, badge tab/counter, sub-modal `#modal-apply-ubah-kavling`, preview/lightbox, responsivitas mobile (`< 768px` / `< 576px`), dan `.dark-layout`.
    - `app/Views/siteplan/master.php`: Menambahkan guard RBAC `[1, 4, 6]` (`$canAccessRequestKavling`, `$canCreateRequestKavling` `[1, 4]`, `$canProcessRequestKavling` `[1, 6]`), menyatukan sidebar menjadi 1 tombol `#btn-open-request-kavling` + `#badge-request-kavling-pending`, menghapus pemanggilan `modal_list_request_kavling.php`, dan memuat `siteplan-request-kavling.css` & `siteplan/request-kavling.js` dengan `filemtime`.
    - `app/Views/siteplan/request_kavling_modal.php` (`518` baris): Revamp menjadi modal terpadu 3-tab (`Request Tambah Kavling`, `Request Ubah Kavling`, `Riwayat`), Select2 multi-jalan & multi-kavling, preview 3D/Denah/Spek, floating canvas picker bar, sub-modal `#modal-apply-ubah-kavling` (+ link `Tambah Tipe` ke `/tipe` `target="_blank"` & `Refresh Tipe`), serta `#modal-rk-tipe-preview` dan `#modal-rk-lightbox`.
    - `public/assets/js/siteplan/request-kavling.js` (`2038` baris — terdaftar di katalog `Transaksi`): Mengimplementasikan `openModalRequestKavling`, `handleContextRequestKavling`, Konva canvas picker (`isRequestKavlingCanvasPickActive`, `handleRequestKavlingCanvasClick`, `renderRequestKavlingCanvasHighlights`, `cancelRequestKavlingCanvasPick`), preview tipe & lightbox, tindak lanjut Planning (`update-status`, `apply-ubah-kavling` + `load_kavling()`), tab Riwayat, serta shim `openModalListRequestKavling`.
    - `public/assets/js/siteplan/master.js` (`5038` baris) & `public/assets/js/siteplan/planning.js` (`1598` baris): Menambahkan metadata shape (`akad`, `akad_tgl`, `akad_indent`, `akad_indent_tgl`, `nama_konsumen`) dan hook minimal Konva canvas picker (`isRequestKavlingCanvasPickActive`, `handleRequestKavlingCanvasClick`, `renderRequestKavlingCanvasHighlights`, `cancelRequestKavlingCanvasPick`).
  - **Deprecations & Verification:** `app/Views/siteplan/modal_list_request_kavling.php` tidak lagi dimuat dari `master.php`; tombol ganda sidebar dikonsolidasikan menjadi `#btn-open-request-kavling` dengan shim `window.openModalListRequestKavling()`. Terverifikasi QA `PASS` (`php spark module:sync Siteplan`, `TargetSiteplanServiceTest` `13 tests, 186 assertions`, `php spark module:deadcode Siteplan` `0` kandidat, & Playwright E2E role MKDT, Planning, Keuangan).
- **`2026-11-18` [`API-BRIDGE`] — Pengayaan Payload `/api/siteplan/kavling` (`tagihan_total_nominal` & `tagihan_sudah_bayar`) via `KavlingRepository`:**
  - **Routes & Public Contracts:** Payload item kavling pada `GET|POST /api/siteplan/kavling` (`SiteplanDataService::getKavlings()` $\rightarrow$ `KavlingRepository::getAll()`) kini menyertakan agregasi `tagihan_total_nominal` dan `tagihan_sudah_bayar` untuk kartu inspector Keuangan di `sigapp-vue`.
  - **Controller / Service / Repo / Model:** Perubahan query repository berada pada modul pemilik `Transaksi` (`app/Repositories/Transaksi/KavlingRepository.php`: `addCompositeFinanceSelect`, `addKeuanganFinanceSelect`, `addTagihanPaymentDerivedJoins`, `selectTagihanAggregates`, & `getAll` cabang `$idDivisi === 3`) beserta unit test `tests/unit/KavlingCompositeQueryTest.php` (*Zero-Downtime* untuk web legacy `sigapp.dev`).
  - **View / CSS / JS:** Tidak ada perubahan aset legacy di `sigapp.dev` untuk fitur inspector SPA ini.
  - **Deprecations:** Tidak ada.
- **`2026-11-17` [`FEATURE`] — Fase 9: Filter Visualisasi Selain Target (4 Mode) & Kavling Target (3 Mode + Border Hitam):**
  - **Routes & Public Contracts:** Tidak ada rute baru; memperkaya payload `target_kavling` pada `SiteplanDataService::getKavlings()` / `TargetSiteplanRepository::getKavlingTargetMap()` dengan field `target_tipe_rumah` dan `target_no_tipe_rumah`.
  - **Controller / Service / Repo / Model:**
    - `app/Repositories/Siteplan/TargetSiteplanRepository.php` (`getKavlingTargetMap(int $idProyek, ?int $tahunTarget = null)`): Menambahkan `'target_tipe_rumah'` dan `'target_no_tipe_rumah'` pada array output per `$idKavling` untuk pengelompokan tipe target eksak di kanvas.
  - **View / CSS / JS:**
    - `app/Views/siteplan/master.php`: Menambahkan 2 dropdown subfilter di `#filter-target-tahun-container`: `#filter-target-other-mode` (`default`, `split_4`, `merge_2`, `single_1`) dan `#filter-target-kavling-mode` (`default`, `single_color`, `by_tipe`).
    - `public/assets/css/siteplan-target.css`: Menambahkan styling `.siteplan-target-subfilter`, `.siteplan-target-subfilter-label`, dan `.siteplan-target-subfilter-select` di dalam `#filter-side .siteplan-target-year-box` (`#2057a3`, `.dark-layout`).
    - `public/assets/js/siteplan/composite-shape.js` (`SiteplanCompositeShape.createKonvaShape`): Menambahkan rendering border poligon di luar `clip()` ketika `Number(options.strokeWidth) > 0` agar border hitam pada kavling target tampil utuh pada bentuk 3-segmen.
    - `public/assets/js/siteplan/master.js`: Menambahkan `selectedTargetOtherMode()`, `selectedTargetKavlingMode()`, `resolveTargetTipeColor()`, kategori warna (`Sudah Akad`, `Booking`, `Sudah Memiliki Konsumen`, `Kavling Target`, `Akad Subsidi`, `Akad Komersil`, `Booking Subsidi`, `Booking Komersil`, `Tipe Target:`), pembaruan `resolveTargetCanvasHit(row, rawHargaAkhir, targetInfo, defaultHit)` & `load_kavling()` sesuai matriks 4 × 3 (border hitam `2.5px` di semua kavling target; konsumen target & non-target mengikuti mode Selain Target; non-konsumen target mengikuti mode Kavling Target; reset filter, badge tag aktif, tooltip target, serta legend `Tipe Target` & `Target`).
  - **Deprecations:** Tidak ada.
- **`2026-11-17` [`BUGFIX`] — Prioritas Pewarnaan Non-Target Akad & Booking Saat Toggle Filter Target Aktif:**
  - **Routes & Public Contracts:** Tidak ada perubahan rute maupun kontrak backend.
  - **Controller / Service / Repo / Model:** Tidak ada perubahan.
  - **View / CSS / JS:**
    - `public/assets/js/siteplan/master.js` (`resolveTargetCanvasHit(row, rawHargaAkhir, targetInfo)`): Memindahkan evaluasi Prioritas 1 (`Akad`: `!isBatal && (row.akad == 1 || hasValidTargetDate(row.akad_tgl))` -> `Akad Komersil`/`Akad Subsidi`) dan Prioritas 2 (`Booking`: `!isBatal &&` non-akad aktif seperti `status_mkdt === 'Booking'|'Akad'`, `sp3k == 1`, `wawancara == 1`, `booking_tgl`, `akad_indent == 1` -> `Booking Komersil`/`Booking Subsidi`) sebelum pengecekan `if (!matchesSelectedYear)`. Pengecekan `!matchesSelectedYear` (`Bukan Target` `#e2e8f0`) kini menjadi Prioritas 3 sehingga hanya mewarnai abu-abu kavling non-target yang belum Akad & belum Booking (atau Batal), sedangkan kavling non-target yang sudah Akad/Booking tetap mempertahankan warna statusnya dan Prioritas 4–5 khusus kavling target tetap memakai warna tipe rumah / `Def` (`#fcffd9`).
  - **Deprecations:** Tidak ada.
- **`2026-11-17` [`FEATURE`] — Preview Gambar & Spesifikasi Tipe Aktif di Modal Planning Siteplan:**
  - **Routes & Public Contracts:** Tidak ada perubahan rute backend baru; frontend Planning mengonsumsi kontrak kanonik modul `Proyek` `POST /api/tipe/all?only_active=1&format=raw` (`Api\Proyek\TipeController::getAll`) dan `POST /api/tipe/getone` (`Api\Proyek\TipeController::getOne`), serta URL aman `FileAccessService` (`/files/gambar_kerja/{id}`).
  - **Controller / Service / Repo / Model:** Tidak ada perubahan file backend; mendaftarkan `siteplan-planning.css` pada `ModuleCatalog['Siteplan']['assets_css']` dan sinkronisasi `php spark module:sync Siteplan`.
  - **View / CSS / JS:**
    - `public/assets/css/siteplan-planning.css` [NEW]: Ekstraksi CSS ter-scope `#modals-slide-in` dari `planning.php`, styling panel preview tipe (`.planning-tipe-preview*`), dan modal lightbox (`#modal-planning-tipe-image`) lengkap dengan dukungan `.dark-layout` dan responsivitas mobile.
    - `app/Views/siteplan/planning.php`: Memuat `siteplan-planning.css?v=<?= filemtime(...) ?>`, menambahkan `#planning-tipe-preview` di dalam `#div_kavling`, menambahkan modal lightbox `#modal-planning-tipe-image`, dan menerapkan cache-busting `filemtime` pada script `planning.js`.
    - `public/assets/js/siteplan/planning.js`: Migrasi Select2 `#id_tipe` ke `api/tipe/all?only_active=1&format=raw` (`is_active = 1`), cache `planningTipeMap`, rendering preview Gambar 3D + Denah + Spesifikasi Teknis + link Gambar Kerja (`renderPlanningTipePreview`, `buildPlanningTipePreviewHtml`, `hasCompletePlanningTipeSpec`, fetch fallback `api/tipe/getone` saat data belum lengkap di `edit_kavling_batch()`, `planningApiUrl`, `planningSanitizeAccessUrl`), lightbox zoom (`openPlanningTipeLightbox`), reset state di `tambah_kavling()` / `$planningJenis.change`, serta pembacaan `#id_tipe option:selected` pada `add_kavling()`.
  - **Deprecations:** Pemanggilan legacy `/tipe/getAll` pada Select2 `#id_tipe` di `public/assets/js/siteplan/planning.js` digantikan oleh `/api/tipe/all?only_active=1&format=raw` (`/tipe/getAll` tetap dipertahankan untuk pemanggil lain seperti `request-kavling.js`).
- **`2026-11-16` [`BUGFIX`] — Perbaikan Pewarnaan Target (`akad = 1` vs Booking) & Layout `#target-interactive-card`:**
  - **Routes & Public Contracts:** Tidak ada perubahan rute maupun kontrak backend.
  - **Controller / Service / Repo / Model:** Tidak ada perubahan.
  - **View / CSS / JS:**
    - `public/assets/js/siteplan/master.js`: Memperketat Prioritas 1 `resolveTargetCanvasHit()` hanya untuk `!isBatal && (row.akad == 1 || hasValidTargetDate(row.akad_tgl))` (`Akad Komersil` / `Akad Subsidi`), memindahkan `row.status_mkdt === 'Akad'` & `akad_indent == 1` saat `akad != 1` ke Prioritas 2 (`Booking Komersil` / `Booking Subsidi`) bersama `status_mkdt === 'Booking'`, `sp3k == 1`, `wawancara == 1`, dan `booking_tgl`, serta memperluas fallback `targetSubsidi` (`row.is_subsidi` -> `row.mkdt_is_subsidi` -> `targetInfo.target_is_subsidi`).
    - `public/assets/css/siteplan-target.css`: Menambahkan batas tinggi `#target-interactive-bar` (`max-height: min(calc(100% - 96px), calc(100vh - 216px))`, `is-fixed-fallback` `calc(100vh - 160px)`) agar tidak tertutup `.float` (`bottom: 24px`, `z-index: 1040`), menambahkan `flex-shrink: 0; box-sizing: border-box;` pada child sections `#target-interactive-card`, dan memadatkan preview/staged list pada desktop & mobile (`< 768px`).
    - `public/assets/js/siteplan/target.js`: Menambahkan `resetTargetInteractiveCardScroll()` dan memanggilnya pada `enterTargetSetMode()` dan `renderTargetBarTipePreview()` untuk menjaga `scrollTop = 0`.
  - **Deprecations:** Tidak ada.
- **`2026-11-16` [`FEATURE`] — Enhancement Fitur Target Siteplan (Fase 5–8: Spek Teknis Tipe, Redesign Card Portrait + Staging Kanvas, Mismatch + Batch Edit, & Integrasi Request Planning):**
  - **Routes & Public Contracts:**
    - Tidak ada rute baru; memperluas kontrak `POST /api/target/add-kavling` (`TargetController::addKavling` / `TargetSiteplanService::addKavlingToYear`) dan `POST /api/target/update-kavling-tipe` (`TargetController::updateKavlingTipe` / `TargetSiteplanService::updateKavlingTipe`) agar menerima input batch (`id_kavlings`, `id_target_kavlings`) dalam 1 transaksi DB & 1 entri Versioning History beserta `batch_summary`.
    - Memperluas kontrak `TargetSiteplanService`: menambah `resolveProjectIdsForTargetKavlings(array $idTargetKavlings, ?int $idTarget = null)`.
    - Memperkaya respons `TargetSiteplanRepository::getKavlings()` dengan proyeksi status pengajuan Planning terbaru (`pending_planning_request_id`, `planning_request_status`, `planning_request_created_at`) dari `kavling_request` (`ubah_tipe`).
  - **Controller / Service / Repo / Model:**
    - `app/Repositories/Siteplan/TargetSiteplanRepository.php`: Menambahkan `getLatestPlanningChangeRequests()`, memperbarui `getKavlings()` dengan status `kavling_request` Planning, serta menambah `findTargetKavlingsByIds()` & `findTargetKavlingsByTargetAndKavlingIds()`.
    - `app/Services/Siteplan/TargetSiteplanService.php` & `app/Controllers/Api/Siteplan/TargetController.php`: Mendukung batch add/update target kavling (`id_kavlings`, `id_target_kavlings`) dan `resolveProjectIdsForTargetKavlings()` dalam 1 transaksi DB & 1 versi history.
    - `tests/unit/TargetSiteplanServiceTest.php`: Menambahkan 4 unit test baru (batch add/update target, status planning request di `getKavlings()`, batch & deduplikasi `KavlingRequest`); total `19 tests, 234 assertions` `PASS`.
  - **View / CSS / JS:**
    - `app/Views/siteplan/target.php`: Redesign `#target-interactive-card` (portrait, `<select id="target-bar-year">`, `#target-bar-tipe-preview`, `#target-bar-staged-list`), pembaruan `#target-kavling-table` (checkbox `#target-check-all`, kolom `Tipe Rumah Saat Ini`, `#target-batch-action-bar` + `#target-btn-batch-edit-tipe` + `#target-btn-batch-req-planning`), modal baru `#modal-target-tipe-spec` (3D, denah, spek compact), dan modal baru `#modal-target-edit-tipe` (Select2 AJAX single & batch).
    - `public/assets/js/siteplan/target.js`: Auto-toggle `#toggle-tampilkan-target` saat `open_target_siteplan()`, dropdown tahun `-1..+3` sinkron ke `#filter-tahun-target`, Select2 AJAX (`<strong>Tipe</strong> (No Tipe)`), preview spek kompak & modal `#modal-target-tipe-spec` (fetch fallback `api/tipe/getone`), staging kavling kanvas + `renderTargetStagedCanvasHighlights()`, deteksi `isTargetRowTipeMismatch()`, badge `Manual` di ujung kanan tanpa badge `Master`, tombol `Ubah Tipe` & batch modal `#modal-target-edit-tipe`, serta `submitPlanningKavlingChangeRequest()` ke `POST /api/kavling-request/submit` & badge `Req Planning: Pending`.
    - `public/assets/js/siteplan/master.js`: `syncTargetYearDropdown()` menjaga tahun aktif dalam rentang `-1..+3` dan memanggil `renderTargetStagedCanvasHighlights()` setelah `load_kavling()`.
    - `public/assets/css/siteplan-target.css`: Penambahan styling portrait `#target-interactive-card`, preview & staging list, `.target-row-tipe-mismatch`, `.target-mismatch-note`, `.target-batch-action-bar`, `.target-planning-req-badge`, `#modal-target-tipe-spec`, `#modal-target-edit-tipe`, responsivitas mobile `< 768px`, dan `.dark-layout`.
  - **Deprecations:** `<input type="number" list="target-bar-year-list">` + `<datalist id="target-bar-year-list">` digantikan `<select id="target-bar-year">`; `populateActiveTipeSelect()` digantikan `initTargetBarTipeSelect2()`; inline MKDT tipe editor (`submitInlineTipeUpdate`) digantikan modal `#modal-target-edit-tipe`.
- **`2026-11-16` [`FEATURE`] — Request Ubah Tipe Target (Planning) & Approval (MKDT) (Target Siteplan Fase 4):**
  - **Routes & Public Contracts:**
    - Menambahkan 3 rute eksplisit pada grup `api/target` (`app/Config/Routes.php` -> `App\Controllers\Api\Siteplan\TargetController`):
      - `POST /api/target/request-change` -> `TargetController::requestChange`
      - `POST /api/target/requests` -> `TargetController::requests`
      - `POST /api/target/review-request` -> `TargetController::reviewRequest`
    - Memperkaya respons `GET /api/siteplan/meta` (`SiteplanService::getMeta()`) dengan flag `permissions.can_request_target_change` (`$this->userInGroups(['1', '6'])`).
    - Memperluas kontrak `TargetSiteplanService`: `resolveProjectIdForTargetRequest(int $idRequest)`, `requestTipeChange(array $data, int $actorId)`, `listRequests(int $idProyek, ?int $idTarget = null, ?int $tahunTarget = null, ?string $status = null)`, dan `reviewRequest(int $idRequest, string $decision, ?string $catatanApproval, int $approverId)`.
  - **Controller / Service / Repo / Model:**
    - `app/Database/Migrations/2026-11-16-000003_AddPlanningTargetMenuItem.php` [NEW]: Menambahkan item menu `planning_target_request` ("Target Siteplan", `onclick="open_target_request()"`, `extra_id="planning-target-request-btn"`, `sort_order=85`) untuk group `6` (Planning) & `1` (Admin) di `siteplan_menu_items` dan `siteplan_menu_roles`.
    - `app/Controllers/Api/Siteplan/TargetController.php`: Menambahkan `requestChange()`, `requests()`, `reviewRequest()`, serta RBAC `authorizePlanningRequest()` (group `1`/`6`) dan `authorizeRequestList()` (group `1`/`4`/`6`/`11`).
    - `app/Services/Siteplan/SiteplanService.php`: Menambahkan `permissions.can_request_target_change` di `getMeta()`.
    - `app/Services/Siteplan/TargetSiteplanService.php`: Implementasi pengajuan request ubah tipe target (`requestTipeChange()`), daftar & counter request (`listRequests()`), approval/reject MKDT (`reviewRequest()` — saat ACC hanya memperbarui `target_siteplan_kavling` dan tidak menyentuh `kavling.id_tipe`, mencatat history `target_approve_tipe` beserta `id_request`, `catatan_request`, `catatan_approval`, `requested_by_username`, dan `approved_by_username`), serta perbaikan resolusi username dari tabel `users` pada `resolveUsername()`.
    - `app/Repositories/Siteplan/TargetSiteplanRepository.php`: Menambahkan `findPendingRequest()`, `findRequestById()`, `insertRequest()`, `updateRequest()`, `getRequests()`, `getRequestCounts()`, serta proyeksi `id_request`, `catatan_request`, dan `catatan_approval` di `getHistory()`.
    - `app/Services/AuditTrail/HistoryService.php`: Menambahkan label aksi `target_approve_tipe` (`ACC Ubah Tipe Target`) pada `formatActionLabel()`.
    - `tests/unit/TargetSiteplanServiceTest.php`: Menambahkan unit test alur pengajuan request, penolakan duplikat pending/tipe sama/konsumen aktif, reject, approve (verifikasi invariant `kavling.id_tipe`), serta eksposur `id_request`, `catatan_request`, dan `catatan_approval` pada `getHistory()`.
  - **View / CSS / JS:**
    - `app/Views/siteplan/master.php`: Memperluas pemuatan partial `siteplan/target` untuk role `[1, 4, 6, 11]`.
    - `app/Views/siteplan/target.php`: Menambahkan Tab 3 **Request Perubahan** (`#tab-target-requests`, badge pending `#target-request-pending-badge`, `#panel-target-requests`, filter status/tahun, `#target-requests-table`).
    - `public/assets/js/siteplan/target.js`: Menambahkan `open_target_request()`, inline request box Planning (`.target-inline-req-box`, `submitInlineRequestChange()`), daftar & filter pengajuan (`loadTargetRequests()`, `renderTargetRequests()`, `updateTargetPendingBadge()`), aksi ACC/Tolak MKDT (`reviewTargetRequest()`), dan rendering `.target-history-notes` pada Tab 2 History Versioning.
    - `public/assets/css/siteplan-target.css`: Penambahan styling ter-scope Tab Request Perubahan, `#target-request-pending-badge`, `.target-inline-req-box`, `#target-requests-table`, `.target-history-notes`, responsivitas mobile `< 768px`, dan `.dark-layout`.
  - **Deprecations:** Tidak ada.
- **`2026-11-16` [`FEATURE`] — Menu MKDT "Set Target" (Mode Interaktif Kanvas, Modal Kelola Target, & History Versioning) (Target Siteplan Fase 3):**
  - **Routes & Public Contracts:**
    - Menambahkan 3 rute eksplisit pada grup `api/target` (`app/Config/Routes.php` -> `App\Controllers\Api\Siteplan\TargetController`):
      - `POST /api/target/add-kavling` -> `TargetController::addKavling`
      - `POST /api/target/update-kavling-tipe` -> `TargetController::updateKavlingTipe`
      - `POST /api/target/remove-kavling` -> `TargetController::removeKavling`
    - Memperluas kontrak `TargetSiteplanService`: `getByProjectAndYear(int $idProyek, int $tahunTarget)`, `resolveProjectIdForTargetKavling(?int $idTargetKavling, ?int $idTarget)`, `addKavlingToYear(array $data, int $actorId)`, `updateKavlingTipe(array $data, int $actorId)`, dan `removeKavling(array $data, int $actorId)`.
  - **Controller / Service / Repo / Model:**
    - `app/Database/Migrations/2026-11-16-000002_AddMkdtTargetMenuItem.php` [NEW]: Menambahkan item menu floating `mkdt_set_target` ("Set Target", `onclick="open_target_siteplan()"`, `extra_id="mkdt-set-target-btn"`) untuk group `4` (MKDT) & `1` (Admin) di `siteplan_menu_items` dan `siteplan_menu_roles`.
    - `app/Controllers/Api/Siteplan/TargetController.php`: Menambahkan `addKavling()`, `updateKavlingTipe()`, `removeKavling()`, dukungan lookup `detail()` via `(id_proyek, tahun_target)`, serta proteksi RBAC mutasi eksklusif group `1` & `4` (`authorizeMutation()` / `403`) dan akses proyek (`authorizeProjectAccess()`).
    - `app/Services/Siteplan/SiteplanService.php`: Memperbarui `getViewSiteplanData()` (`has_akses['target']`) dan `getMeta()` (`can_manage_target`) agar mengizinkan group `4` (MKDT) selain `1` & `11`.
    - `app/Services/Siteplan/TargetSiteplanService.php`: Implementasi mutasi target per kavling (auto-create header tahun baru, validasi tipe aktif `is_active = 1` vs `tipe_manual`, tolak konsumen aktif kecuali kosong/`Batal`/`NUP`, preservasi tipe saat `save()`), serta pencatatan **Versioning History** (`v1`, `v2`, ...) beserta `before`/`after` snapshot & `diff_summary` via `HistoryService::log('target_siteplan', ...)`.
    - `app/Repositories/Siteplan/TargetSiteplanRepository.php`: Menambahkan `findActiveByProjectAndYear()`, `createTargetHeader()`, `updateTargetHeader()`, `findTargetKavlingRow()`, `findTargetKavlingById()`, `insertTargetKavling()`, `updateTargetKavling()`, `deleteTargetKavling()`, `findKavlingsWithActiveConsumer()`, `findTipeForProject()`, `countTargetHistory()`, dan memperkaya `getKavlings()` serta `getHistory()` (`version`, `version_label`, `before_snapshot`, `after_snapshot`, `diff_summary`, `requested_by_username`, `approved_by_username`).
    - `app/Services/AuditTrail/HistoryService.php`: Menambahkan label aksi `target_add_kavling`, `target_remove_kavling`, dan `target_update_tipe` pada `formatActionLabel()`.
    - `tests/unit/TargetSiteplanServiceTest.php` [NEW]: Unit test CRUD target, validasi konsumen aktif & tipe nonaktif, preservasi tipe saat `save()`, dan versioning history.
  - **View / CSS / JS:**
    - `app/Views/siteplan/master.php`: Memperluas kondisi pemuatan `siteplan/target` untuk role `[1, 4, 11]`.
    - `app/Views/siteplan/target.php`: Menambahkan Floating Interactive Bar `#target-interactive-bar` dan merombak Modal Kelola Target `#modal-target-siteplan` (tab Daftar Kavling Target + History Versioning).
    - `public/assets/js/siteplan/target.js`: Implementasi Mode Interaktif Kanvas (`open_target_siteplan`, `handleTargetCanvasClick`, `flashTargetShapeHighlight`), inline edit tipe target, hapus kavling, simpan catatan target, dan timeline History Versioning Before vs After.
    - `public/assets/js/siteplan/master.js` & `public/assets/js/siteplan/planning.js`: Integrasi `isTargetSetModeActive()` dan `scheduleTargetCanvasRefresh()` pada handler klik/contextmenu/deselect kanvas Konva.
    - `public/assets/css/siteplan-target.css`: Penambahan styling ter-scope `#target-interactive-bar`, `#modal-target-siteplan`, diff Before vs After, responsivitas mobile `< 768px`, dan `.dark-layout`.
  - **Deprecations:** Tidak ada.
- **`2026-11-16` [`FEATURE`] — Toggle "Tampilkan Target", Dropdown Tahun Target, & Pewarnaan Kanvas (Target Siteplan Fase 2):**
  - **Routes & Public Contracts:**
    - Payload `SiteplanDataService::getKavlings(array $params)` (konsumsi `POST /siteplan/get/all` & `GET|POST /api/siteplan/kavling`) kini selalu menyertakan `target_kavling` (mendukung filter opsional `tahun_target`) dan `target_years` di semua `id_role`.
    - `TargetSiteplanService`: Menambahkan `getAvailableYears(int $idProyek): array` dan memperbarui `getKavlingTargetMap(int $idProyek, ?int $tahunTarget = null): array`.
  - **Controller / Service / Repo / Model:**
    - `app/Repositories/Siteplan/TargetSiteplanRepository.php`: Menambahkan `getAvailableYears(int $idProyek)` serta memperbarui `getKavlingTargetMap(int $idProyek, ?int $tahunTarget = null)` dengan filter tahun opsional, `LEFT JOIN tipe target_tipe`, dan field `id_tipe`, `tipe_manual`, `tipe_label`, `target_is_subsidi` (didukung helper privat `resolveTipeLabel()`).
    - `app/Services/Siteplan/TargetSiteplanService.php`: Menambahkan `getAvailableYears(int $idProyek)` dan meneruskan `$tahunTarget` di `getKavlingTargetMap()`.
    - `app/Services/Siteplan/SiteplanDataService.php`: Menghapus pembatasan `if ($idRole === 11)` pada `getKavlings()` sehingga `target_kavling` dan `target_years` selalu dikembalikan untuk seluruh divisi.
    - `app/Repositories/Transaksi/KavlingRepository.php`: Konsolidasi field MKDT (`status_mkdt`, `is_batal`, `booking_tgl`, `wawancara`, `wawancara_tgl`, `sp3k`, `sp3k_tgl`, `akad_indent`, `akad_indent_tgl`, `akad`, `akad_tgl`, `is_kpr`, `mkdt_is_subsidi`) ke `baseQuery()`.
    - Unit test `tests/unit/SiteplanDataServiceTest.php` & `tests/unit/KavlingCompositeQueryTest.php` diperbarui (`90` tests, `765` assertions `PASS`).
  - **View / CSS / JS:**
    - `public/assets/css/siteplan-target.css` [NEW]: Stylesheet ter-scope `#filter-side` / `.siteplan-target-*` (`#2057a3`, `.dark-layout`).
    - `app/Views/siteplan/master.php`: Memuat `siteplan-target.css` dengan `filemtime`, menghapus `<option value="11">Target</option>` dari `#pilih-divisi`, serta menambahkan switch `#toggle-tampilkan-target` dan container `#filter-target-tahun-container` (`#filter-tahun-target`).
    - `public/assets/js/siteplan/master.js`: Menambahkan `isTargetFilterActive()`, `selectedTargetYear()`, `hasValidTargetDate()`, `syncTargetMenuVisibility()`, `syncTargetYearDropdown()`, `resolveTargetCanvasHit()` (4 prioritas warna target + `#e2e8f0` untuk `Bukan Target`), serta memperbarui `get_kategori_color()`, `getServerFilterData()`, `renderActiveFilterTags()`, `removeFilterTag('target')`, `reset_server_filter()`, dan `setSiteplanTooltip()`.
  - **Deprecations:** `<option value="11">Target</option>` di `#pilih-divisi` (`master.php`) dan cabang `if (va == 11)` di `master.js` digantikan oleh Switch Tampilkan Target + Dropdown Tahun Target.
- **`2026-11-16` [`FEATURE`] — Upgrade Skema Database Target Siteplan & Model `target_siteplan_request` (Target Siteplan Fase 1):**
  - **Routes & Public Contracts:** Tidak ada perubahan rute atau service contract pada Fase 1.
  - **Controller / Service / Repo / Model:**
    - Migrasi `app/Database/Migrations/2026-11-16-000001_UpgradeTargetSiteplanAndTipeActive.php`: Menambahkan kolom `id_tipe`, `tipe_manual`, `updated_by`, `updated_at` beserta index `id_tipe` pada tabel `target_siteplan_kavling`, serta membuat tabel baru `target_siteplan_request`.
    - `app/Models/TargetSiteplanKavlingModel.php`: Menambahkan `'id_tipe'`, `'tipe_manual'`, `'updated_by'`, dan `'updated_at'` ke `$allowedFields`.
    - `app/Models/TargetSiteplanRequestModel.php`: Model baru untuk tabel pemilik `target_siteplan_request` (didaftarkan pada `ModuleCatalog.php` dan disinkronkan via `php spark module:sync Siteplan`).
  - **View / CSS / JS:** Tidak ada perubahan pada aset view/JS Siteplan pada Fase 1.
  - **Deprecations:** Tidak ada.
- **`2026-11-15` [`API-BRIDGE`] — Penambahan 3 Rute REST MKDT `POST /api/siteplan/{get_harga_kavling,get_turun_pembangunan,set_turun_pembangunan}` untuk SPA Siteplan Fase 3.3 (Sub-Fase 3.3.1):**
  - **Routes & Public Contracts:**
    - Mendaftarkan 3 rute eksplisit baru pada grup `api/siteplan` (`app/Config/Routes.php` -> `Api\Siteplan\SiteplanController`): `POST /api/siteplan/get_harga_kavling`, `POST /api/siteplan/get_turun_pembangunan`, dan `POST /api/siteplan/set_turun_pembangunan` (`RAW_JSON_READY` JSON envelope via `getRequestData()` + `wantsEnvelope()`, mendukung upload `perintah_bangun_file`).
    - Rute legacy `/siteplan/{get_harga_kavling,get_turun_pembangunan,set_turun_pembangunan}` tetap dipertahankan utuh (*Zero-Downtime*).
  - **Controller / Service / Repo / Model & View:** Tidak ada perubahan logika controller/service/view legacy `sigapp.dev`; diverifikasi QA `PASS` bersama `php spark module:sync Siteplan`.
- **`2026-11-15` [`API-BRIDGE`] — Penambahan 6 Endpoint REST Planning `POST /api/siteplan/*` & Pengayaan `KavlingRequest` untuk SPA Siteplan Fase 3.2 (Divisi Planning):**
  - **Routes & Public Contracts:**
    - Mendaftarkan 6 rute eksplisit baru pada grup `api/siteplan` (`app/Config/Routes.php` -> `Api\Siteplan\SiteplanController`): `POST /api/siteplan/add_kavling`, `POST /api/siteplan/edit_kavling`, `POST /api/siteplan/edit_others`, `POST /api/siteplan/get_kavling_by_id`, `POST /api/siteplan/get_kavling_by_multiple_id`, dan `POST /api/siteplan/get_others` (`RAW_JSON_READY` JSON envelope via `wantsEnvelope()`, `add_kavling` mengembalikan HTTP `201`/`422`, `edit_kavling`/`edit_others` HTTP `200`/`422`, `get_kavling_by_id` HTTP `200`/`404`, `get_kavling_by_multiple_id`/`get_others` HTTP `200`).
    - Rute legacy `/siteplan/{add_kavling,edit_kavling,edit_others,get_kavling_by_id,get_kavling_by_multiple_id,get_others}` tetap dipertahankan utuh (*Zero-Downtime*).
  - **Controller / Service / Repo / Model:**
    - `app/Controllers/Api/Transaksi/KavlingRequestController.php` & `app/Services/Transaksi/KavlingRequestService.php`: Penguatan `getRequestData()` (query + POST + JSON + fallback `X-Project-Id`) pada `list`, `submit`, `updateStatus`, `kavlingList`, serta penambahan `getStatusCounts()` (`counts.{all, pending, approved, rejected}`) untuk badge & tab filter modal List Request Kavling tanpa merusak `request-kavling.js` legacy.
  - **View / CSS / JS & Verifikasi:** Tidak ada perubahan view legacy `sigapp.dev`; `php spark module:sync Siteplan` (`REFACTORED 100/100`), `php spark routes`, & `http://localhost/sigapp.dev/public/siteplan/view` (`200 OK`) terverifikasi tanpa regresi.
- **`2026-11-14` [`API-BRIDGE`] — Pengayaan `GET /api/siteplan/meta` untuk SPA Siteplan Fase 3.1 (`siteplan_menus` & `permissions`):**
  - **Routes & Public Contracts:** Memperkaya respons JSON `GET /api/siteplan/meta` (`Api\Siteplan\SiteplanController::meta()`) dengan key baru `siteplan_menus` (`role_id`, `default_group_id`, `groups`, `context_items`) serta atribut `permissions.{role_id, is_admin, has_project_access}`.
  - **Controller / Service / Repo / Model:**
    - `app/Services/Siteplan/SiteplanService.php`: Inject opsional `App\Services\Settings\SiteplanMenuService` di konstruktor, menambahkan helper privat `resolvePrimaryRoleId(int $userId)`, serta mengembalikan `permissions.{role_id, is_admin, has_project_access}` dan `siteplan_menus` pada `getMeta(?int $idProyek, int $userId)`.
    - `app/Services/Settings/SiteplanMenuService.php`: Menambahkan `getMenusForSpa(int $roleId)` dan `formatSpaMenuItem()` untuk menyusun menu floating & context actions Siteplan dalam JSON murni bebas HTML.
  - **View / CSS / JS:** Tidak ada perubahan view legacy `sigapp.dev` (Zero-Downtime terjaga).
  - **Deprecations:** Tidak ada.
- **`2026-11-13` [`REFACTOR`] — Refaktor Penuh Arsitektur Modular Siteplan, Pemisahan Web/Api Controller, Service & Repository Layer, serta Full JSON API (`RAW_JSON_READY`):**
  - **Routes & Public Contracts:**
    - Mendaftarkan rute eksplisit Web (`Web\Siteplan\SiteplanController`, `ModalController`, `KalendarController`): `/siteplan`, `/siteplan/index`, `/siteplan/view`, `/siteplan/view_siteplan`, `/siteplan/view_siteplan/(:num)`, `/siteplan/produksi-mobile`, `/siteplan/produksi_mobile`, `/getmodal`, `/kalendar`, `/kalendar/index`.
    - Mendaftarkan rute eksplisit REST API (`Api\Siteplan\SiteplanController`, `TargetController`, `KalendarController` — Full JSON `RAW_JSON_READY`): `/api/siteplan/meta`, `/api/siteplan/kavling`, `/api/siteplan/proyek`, `/api/siteplan/proyek/(:num)`, `/api/siteplan/config`, `/api/siteplan/others`, `/api/siteplan/detail`, `/api/siteplan/detail/(:num)`, `/api/target/{list,detail,save,history}`, `/api/kalendar/{events,meta}`, `/api/siteplan/kalendar/{events,meta}`.
    - Mendaftarkan rute eksplisit Legacy Zero-Downtime AJAX (mendukung `?format=raw` Full JSON envelope): `POST /siteplan/get/all`, `/siteplan/getAllKavling`, `/siteplan/get/detail`, `/siteplan/get_detail`, `/siteplan/getKategoriOptions`, `/siteplan/urgent/summary`, `/siteplan/get_others`, `/siteplan/add_kavling`, `/siteplan/edit_kavling`, `/siteplan/edit_others`, `/siteplan/get_kavling_by_multiple_id`, `/siteplan/get_harga_kavling`, `/siteplan/get_turun_pembangunan`, `/siteplan/set_turun_pembangunan`, `/siteplan/get_kavling_by_id`, dan `GET|POST /kalendar/getEvents`, `/kalendar/getevents`.
  - **Controller / Service / Repo / Model:**
    - Memisahkan 6 controller modular baru (`Web/Siteplan/{SiteplanController,ModalController,KalendarController}.php`, `Api/Siteplan/{SiteplanController,TargetController,KalendarController}.php`).
    - Memindahkan dan memecah logika bisnis ke 7 service di `app/Services/Siteplan/`: `SiteplanService.php`, `SiteplanDetailService.php` (ekstraksi agregator detail kavling lintas departemen dengan Signed URL `FileAccessService`), `SiteplanDataService.php`, `SiteplanUrgentService.php`, `SiteplanVisualStatusService.php`, `TargetSiteplanService.php`, `KalendarService.php`.
    - Mengonsolidasikan seluruh query DB ke 5 repository di `app/Repositories/Siteplan/`: `SiteplanRepository.php` (termasuk parameterized `UNION ALL` PPh/PPN & `getHakAksesByUser`), `SiteplanUrgentRepository.php`, `TargetSiteplanRepository.php`, `OthersRepository.php` (penambahan `insertOthers`, `updateOthers`, `findById`, `findProyekIdByJalanId`), `KalendarRepository.php`.
    - Perbaikan QA Fase 3B pada `Api\Siteplan\{SiteplanController,KalendarController,TargetController}` (`getRequestData()` aman untuk form-urlencoded/multipart/JSON dan `wantsEnvelope()` memprioritaskan query `?format=raw`).
    - Memperbarui `app/Libraries/ModuleManager/ModuleCatalog.php` (`owner_tables` termasuk `others`, 6 controllers, 7 services, 5 repositories, 13 shims).
  - **View / CSS / JS:**
    - Surgical fix QA Fase 3B pada `app/Views/misc/kalender.php`: `const kalendarBaseUrl` menggantikan `var base_url`, `failureCallback(new Error(...))`, dan fallback warna `#2057a3`.
  - **Deprecations & Verifikasi:**
    - Mengubah 4 controller lama, 6 service root, dan 3 repository root menjadi Zero-Downtime Shims (total 13 shim); menghapus helper internal controller lama (`Siteplan::add_kavling2()`, `add_others()`, `get_config()`, `normalizeIdProyek()`) yang kini terenkapsulasi di Service/Repository.
    - Verifikasi QA Putaran 2: **PASS** (Playwright E2E, Full JSON API `?format=raw` & `/api/siteplan/*`, PHPUnit 77/77, `php spark module:status Siteplan` = `REFACTORED 100%`).
- **2026-11-09 [`UI_REPORT`] — Penyesuaian Bar Belum Cair (`bg-transparent` / Track Progress Biasa) dan Indikator Legend Secondary pada Blok Total Hasil Akad Modal Detail Kavling:**
  - **Routes & Public Contracts:** Tidak ada perubahan.
  - **Controller / Service / Repo / Model:** Tidak ada perubahan.
  - **View / CSS / JS:**
    - `app/Views/siteplan/partials/modal_detail.php`: Mengubah `#s-pa_sisa_bar` menggunakan `progress-bar bg-transparent` (transparan/kosong sehingga menampilkan track `.progress` biasa), serta mengubah dot indikator legend (`.detail-dot.bg-secondary`) dan label teks persentase belum cair (`#s-pa_sisa_pct.text-secondary`) pada blok Total Hasil Akad (Tab Ringkasan -> Card Ringkasan Keuangan) menjadi warna secondary.
    - `app/Views/siteplan/partials/modal_detail_styles.php`: Menambahkan aturan CSS ter-scope untuk memastikan `#modal_detail #s-pa_sisa_bar.bg-transparent` menampilkan track `.progress` kosong (`background-color: transparent !important`, `background-image: none !important`) serta styling `.detail-dot.bg-secondary` dan dukungan `.dark-layout`.
  - **Deprecations:** Tidak ada.
- **2026-11-05 [`BUGFIX`] / [`UI_REPORT`] — Penyempurnaan Hierarki Status MKDT (Prioritas Akad AJB atas Indent) dan Opsi B Stepper Timeline Selesai:**
  - **Routes & Public Contracts:** Tidak ada perubahan endpoint atau kontrak backend (`siteplan/get/detail`).
  - **Controller / Service / Repo / Model:** Tidak berubah.
  - **View / CSS / JS:**
    - `public/assets/js/siteplan-detail-modal.js`:
      - Memperbaiki `resolveMkdtStatusInfo(m)` dengan hierarki prioritas status transaksi yang presisi: Batal (`is_batal == 1 || status_mkdt === 'Batal'`) -> Akad AJB (`akad == 1 || isValidDate(akad_tgl) || (status_mkdt === 'Akad' && akad_indent != 1)`) -> Akad Indent (`(akad_indent == 1 || isValidDate(akad_indent_tgl) || is_ajb === 'PPJB') && akad != 1`) -> SP3K -> Wawancara -> Booking. Menjamin jika `m.akad == 1` selalu diselesaikan sebagai status `'Akad AJB'` (`badge-success`) meskipun `m.akad_indent == 1` (solusi tuntas edge case kavling 265 / MKDT 349).
      - Mengimplementasikan Opsi B pada `renderKavlingTimeline(mkdt, currentStatus)`: saat status transaksi mencapai 'Akad AJB' selesai (`mkdt.akad == 1 || isValidDate(mkdt.akad_tgl)`), seluruh 5 tahapan stepper (Booking, Wawancara, SP3K, Akad Indent, Akad AJB) otomatis berstatus `is-completed` dengan icon centang `fas fa-check` lingkaran solid biru `#2056a4`.
    - `tests/js/siteplan-status-timeline.test.js`:
      - Menambahkan suite pengujian unit Jest dengan 16 skenario komprehensif (Kasus A s/d G dan edge cases) untuk memvalidasi resolusi status MKDT dan stepper timeline (100% passing).
  - **Deprecations:** Tidak ada.
- **2026-11-04 [`FEATURE`] — Penambahan Agregasi Peringatan SP3K Expire & Rencana Akad pada SiteplanUrgentService:**
  - **Routes & Public Contracts:** Tidak berubah.
  - **Controller / Service / Repo / Model:** `app/Services/SiteplanUrgentService.php` menambahkan method publik `countMkdtDateAlerts(string $field, ?int $idProyek, ?string $fromDate, ?string $toDate)` dan memperkaya `getDashboardAlertCounts(?int $idProyek)` dengan metrik `sp3k_expire` dan `rencana_akad` untuk integrasi peringatan dini dashboard lintas departemen.
  - **View / CSS / JS:** Tidak berubah.
  - **Deprecations:** Tidak ada.
- **2026-11-04 [`FEATURE`] — Inklusi Otomatis Item Others Bermasalah Aktif pada Default Canvas Siteplan:**
  - **Routes & Public Contracts:** Tidak berubah (`POST /siteplan/get_others` / `SiteplanDataService::getOthers`).
  - **Controller / Service / Repo / Model:** `app/Repositories/OthersRepository.php` memperbarui method `getAll()` dengan menambahkan klausul `orWhere('act_tm.active_tiket_id IS NOT NULL', null, false)` di dalam grup filter `others.scope`. Entitas non-kavling/others (jalan, fasos, rth, ruko) yang dibuat dengan `scope = 'masalah'` kini otomatis terpilih dan tampil di kanvas default siteplan selama tiket masalahnya aktif (`act_tm.active_tiket_id IS NOT NULL`), memicu animasi pulsing stroke dan warning badge tanda seru (`!`) tanpa memerlukan filter manual kategori 'Masalah'. Unit test ditambahkan pada `tests/unit/KavlingCompositeQueryTest.php` (`testOthersScopeFilteringIncludesActiveTiket`).
  - **View / CSS / JS:** Tidak berubah; canvas KonvaJS dan `SiteplanTiketOverlay` langsung merender animasi pulse dan badge `!` untuk aset others tersebut.
  - **Deprecations:** Tidak ada.
- **2026-11-04 [`FEATURE`] — Dukungan Overlay Tiket Masalah pada Entitas Fasum/Others Siteplan:**
  - **Routes & Public Contracts:** Payload respons aset non-kavling / others (`POST /siteplan/get_others` / `SiteplanDataService::getOthers`) diperkaya dengan atribut tiket masalah aktif (`active_tiket_id`, `active_tiket_prioritas`, `active_tiket_status`).
  - **Controller / Service / Repo / Model:** `app/Repositories/OthersRepository.php` menambahkan method `addActiveTiketSelect(BaseBuilder $builder)` dengan LEFT JOIN derived table agregasi `tiket_masalah` (`ref_type = 'others'`, `status NOT IN ('selesai', 'batal', 'hold')`). Unit test ditambahkan pada `tests/unit/KavlingCompositeQueryTest.php` (`testOthersActiveTiketSelectAddsDerivedTableAndColumns`).
  - **View / CSS / JS:**
    - `public/assets/js/siteplan/tiket-overlay.js`: Penambahan dukungan multi-tipe `refType = 'others'` dan `refType = 'kavling'`, ID prefixing terisolasi (`#tiket-overlay-others{id}`, `#tiket-badge-others{id}`), safe centroid calculation untuk poligon fasum, dan evaluasi visibilitas layer terhadap node target `#others{id}`.
    - `public/assets/js/siteplan/master.js`: Penambahan state tersentralisasi `siteplanProblemItems = { kavling: [], others: [] }` dan fungsi agregasi atomik `syncSiteplanTiketOverlay()` guna mengeliminasi race condition antara pemuatan data kavling dan aset fasum.
    - `app/Views/siteplan/master.php`: Penerapan cache busting dinamis `filemtime` pada pemuatan script `master.js`.
    - Suite unit test Jest di `tests/js/siteplan-tiket-overlay.test.js` diperluas dari 21 menjadi 25 unit tests (100% passing).
  - **Deprecations:** Tidak ada.
- **2026-11-04 [`FEATURE`] — Optimalisasi Tiket Masalah Overlay Siteplan (Cap 30 FPS, High-Contrast Stroke, dan Centroid Warning Badge):**
  - **Routes & Public Contracts:** Tidak berubah.
  - **Controller / Service / Repo / Model:** Tidak berubah.
  - **View / CSS / JS:** Pembaruan pada `public/assets/js/siteplan/tiket-overlay.js`:
    - Penambahan Frame Rate Capping ke 30 FPS (`TARGET_FPS = 30`, `FRAME_INTERVAL_MS = 33.33ms`) pada loop animasi KonvaJS guna menekan utilisasi GPU/CPU canvas.
    - Opsi 1 (Pulsing High-Contrast Stroke): Garis tepi putih `#ffffff` berdenyut dinamis (`strokeWidth` 1.0px s.d. 3.5px via `1.0 + 2.5 * opacity`) pada shape poligon kavling overlay.
    - Opsi 2 (Centroid Warning Badge `!`): Penambahan fungsi `calculateCentroid(points)` dan `createBadgeShape(Konva, options)` yang menggambar floating warning badge tanda seru (`!`) di tengah kavling dengan animasi skala halus (`scale` 0.85x s.d. 1.15x via `0.85 + 0.3 * opacity`).
    - Suite unit test Jest di `tests/js/siteplan-tiket-overlay.test.js` diperluas dari 14 menjadi 21 unit tests (100% passing).
  - **Deprecations:** Tidak ada.
- **2026-11-04 [`FEATURE`] — Penambahan Layer Animasi Tiket Masalah Overlay pada Siteplan KonvaJS:**
  - **Routes & Public Contracts:** Tidak ada penambahan endpoint baru. Payload data kavling pada `POST /siteplan/get/all` (`Siteplan::getAllKavling`) diperkaya dengan kolom tiket masalah aktif (`active_tiket_id`, `active_tiket_prioritas`, `active_tiket_status`).
  - **Controller / Service / Repo / Model:** `app/Repositories/KavlingRepository.php` menambahkan method `addActiveTiketSelect(BaseBuilder $builder)` dengan LEFT JOIN derived table agregasi `tiket_masalah` (`ref_type = 'kavling'`, `status NOT IN ('selesai', 'batal', 'hold')`). Unit test ditambahkan pada `tests/unit/KavlingCompositeQueryTest.php` (`testActiveTiketSelectAddsDerivedTableAndColumns()`).
  - **View / CSS / JS:** Asset baru `public/assets/js/siteplan/tiket-overlay.js` (modul UMD `SiteplanTiketOverlay`). View `app/Views/siteplan/master.php` mendaftarkan tag script `tiket-overlay.js`. Script `public/assets/js/siteplan/master.js` menginisialisasi `tiketOverlayLayer` pada Konva Stage di antara `siteplanLayer` dan `maskedLayer`, memanggil `SiteplanTiketOverlay.buildOverlayShapes()` pada saat kavling dimuat, dan membersihkan overlay via `clearSiteplanDataShapes()`. Unit test Jest `tests/js/siteplan-tiket-overlay.test.js` (14 test cases pass).
  - **Deprecations:** Tidak ada.
- **2026-11-03 [`UI_REPORT`] — Sinkronisasi Milestone Visual Siteplan untuk Status Akad Indent & Akad AJB:**
  - **Routes & Public Contracts:** Penyesuaian output kontrak `visual_rows[0]` (segmen MKDT) pada method `SiteplanVisualStatusService::buildRows` dan `appendVisualRows`. Segmen MKDT kini mengembalikan `config_name` `'Akad Subsidi'` atau `'Akad Komersil'` saat `akad_indent = 1` maupun `akad = 1`. Jika `akad_indent = 1` dan belum akad AJB (`akad = 0`), menyertakan marker `[['config_name' => 'Akad Indent', 'position' => 0.5]]` (garis aksen biru). Setelah `akad = 1` (Akad AJB), marker dihapus sehingga warna solid Akad tampil penuh.
  - **Controller / Service / Repo / Model:** Diperbarui `app/Services/SiteplanVisualStatusService.php` (`mkdtRow()`) untuk evaluasi boolean `$hasAkadAjb` dan `$hasAkadIndent`. Test suite diperbarui pada `tests/unit/SiteplanVisualStatusServiceTest.php` (72 tests passing).
  - **View / CSS / JS:** Tidak ada perubahan file (visual rendering poligon siteplan digerakkan oleh output backend).
  - **Deprecations:** Tidak ada.
- **2026-11-04 [`BUGFIX`] — Resolusi Nama Bank dari id_bank Tabel MKDT pada Modal Detail Kavling:**
  - **Routes & Public Contracts:** Payload endpoint `POST /siteplan/get/detail` (`Siteplan::get_detail`) diperkaya dengan kolom `nama_bank` yang bersumber dari tabel master `list_bank` via `LEFT JOIN list_bank ON list_bank.id = mkdt.id_bank`.
  - **Controller / Service / Repo / Model:** `app/Controllers/Siteplan.php` (`get_detail()`) menambahkan `list_bank.bank as nama_bank` pada select query `$d['mkdt']` dan menambahkan join `list_bank` berdasarkan `mkdt.id_bank`.
  - **View / CSS / JS:** `public/assets/js/siteplan-detail-modal.js`:
    - Mengubah populasi `#s-st_bank` pada bar status ringkasan modal detail agar memprioritaskan `mkdt.nama_bank || mkdt.bank || (mkdt.id_bank && mkdt.id_bank != '0' ? mkdt.id_bank : '-')`.
    - Mengubah input `#dt-st_bank` pada form Tab Status & Akad agar menampilkan nama bank yang sama.
  - **Deprecations:** Tidak ada.
- **2026-11-04 [`BUGFIX`] — Perbaikan Render Ikon Checkmark FontAwesome pada Card Progres Pembangunan:**
  - **Routes & Public Contracts:** Tidak berubah.
  - **Controller / Service / Repo / Model:** Tidak berubah.
  - **View / CSS / JS:**
    - `public/assets/js/siteplan-detail-modal.js`: Menghapus kelas `font-weight-bold` dari output `<i class="fas fa-check text-success"></i>` pada `setAmenityIcon()`. Penggunaan `font-weight-bold` di Vuexy sebelumnya memicu `font-weight: 500 !important`, yang tidak cocok dengan `@font-face` Font Awesome Solid (`font-weight: 900`), sehingga menyebabkan glyph checkmark hilang/tidak muncul.
    - `app/Views/siteplan/partials/modal_detail_styles.php`: Menambahkan rule eksplisit `#modal_detail .amenity-item i.fa-check` dengan `font-family: 'Font Awesome 5 Free' !important`, `font-weight: 900 !important`, dan `color: #28c76f !important` agar render checkmark selalu konsisten dan tampak jelas.
    - `app/Views/siteplan/partials/modal_detail.php`: Menyesuaikan placeholder default amenity menjadi `-` konsisten dengan desain.
  - **Deprecations:** Tidak ada.
- **2026-11-04 [`BUGFIX`] / [`UI_REPORT`] — Penyempurnaan Validitas Status MKDT, Elongated Badge, dan Tanggal Muted Vertikal:**
  - **Routes & Public Contracts:** Tidak ada perubahan endpoint atau kontrak backend (`siteplan/get/detail`).
  - **Controller / Service / Repo / Model:** Tidak berubah.
  - **View / CSS / JS:**
    - `app/Views/siteplan/partials/modal_detail.php`: Mengubah layout kolom Status Konsumen agar badge `#s-status_konsumen_badge` berada di atas dan tanggal `#s-status_konsumen_tgl` berada vertikal di bawahnya dengan style muted (`text-muted small mt-25 font-weight-normal`).
    - `app/Views/siteplan/partials/modal_detail_styles.php`: Menambahkan styling pill memanjang (`min-width: 110px`, `padding: 0.35rem 1.35rem`, `border-radius: 20px`) untuk `#s-status_konsumen_badge` beserta warna background/teks kontras per status (`badge-primary`, `badge-success`, `badge-warning`, `badge-danger`, `badge-info`, `badge-secondary`).
    - `public/assets/js/siteplan-detail-modal.js`:
      - Penambahan helper `isValidDate(val)` untuk menangani field MySQL `'0000-00-00'` agar tidak dievaluasi truthy di JS.
      - Penambahan fungsi `resolveMkdtStatusInfo(m)` untuk menentukan status aktif MKDT (Batal, Akad AJB, Akad Indent, SP3K, Wawancara, Booking) dan tanggal status yang valid.
      - Perbaikan fungsi `renderKavlingTimeline(mkdt, currentStatus)` agar status step aktif (`is-active`), completed (`is-completed`), dan connecting line horizontal sinkron dengan status aktif MKDT.
      - Perbaikan kondisi warning SP3K kedaluwarsa dengan `isValidDate(sp3k_tgl_exp)`.
  - **Deprecations:** Tidak ada.
- **2026-11-04 [`UI_REPORT`] — Redesign Layout Tab Ringkasan Modal Detail Kavling:**
  - **Routes & Public Contracts:** Tidak ada perubahan endpoint atau kontrak backend (`siteplan/get/detail`).
  - **Controller / Service / Repo / Model:** Tidak berubah.
  - **View / CSS / JS:**
    - `app/Views/siteplan/partials/modal_detail.php`: Redesign total tab `#dtt-summary` menjadi 3 baris utama:
      1. Banner warning "PERLU PERHATIAN" (SP3K H-21 & Tagihan jatuh tempo) yang otomatis tampil/sembunyi sesuai kondisi data.
      2. Card horizontal "Status Konsumen" dengan badge status aktif saat ini (Booking, Wawancara, SP3K, Batal, Akad Indent, Akad AJB) + tanggal status, Bank, Notaris, SP3K Terbit & Kadaluarsa.
      3. Row 2 kolom: Ringkasan Keuangan (Total Tagihan vs Sudah Bayar, Total Hasil Akad, Cair vs Belum Cair dual bar) dan Progres Pembangunan (Progres fisik bar %, Turun/Mulai/Selesai, dan checklist Site Amenities).
      4. Card Timeline Aktivitas Kavling dengan stepper horizontal bergaris penghubung (*connecting line*).
      5. Menjaga elemen legacy shims tersembunyi demi backward compatibility.
    - `app/Views/siteplan/partials/modal_detail_styles.php`: Penambahan styling responsif untuk `.detail-warning-banner`, `.detail-warning-card`, `.detail-summary-status-bar`, `.kavling-timeline`, `.kavling-timeline-item`, connecting lines `::after`, dan adaptasi Dark Mode `.dark-layout`.
    - `public/assets/js/siteplan-detail-modal.js`: Penambahan helper `format_date_clean()`, `setAmenityIcon()`, `updateRingkasanHasilAkad()`, dan `renderKavlingTimeline()`. Memperbarui `loadSummary(r)` untuk mengevaluasi warning H-21 SP3K & tagihan jatuh tempo, menentukan status aktif dan tanggalnya, mengupdate progress bar keuangan dan pembangunan, serta merender stepper timeline bergaris.
  - **Deprecations:** Tidak ada deprecations; legacy DOM selector dipertahankan melalui hidden shims.
- `2026-11-03` [`SYNC`] — Sinkronisasi status & inventori modul melalui `module:sync`.

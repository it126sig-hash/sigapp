# Module: Siteplan

**Status:** `REFACTORED`  
**Readiness Score:** `100/100`  
**Last Synced:** `2026-11-15 01:36:27` @ `7b1acf6`  

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

### Shared Tables (Dibaca dari modul lain)
- `kavling`
- `mkdt`
- `keuangan`
- `produksi`
- `legal`
- `tiket_masalah`

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
| `GET` | `/api/siteplan/meta` | `Explicit` | `SiteplanController::meta` | 🟢 MIGRATED | Endpoint REST JSON `RAW_JSON_READY`; menyertakan `siteplan_menus` & `permissions.{role_id, is_admin, has_project_access}` |
| `GET` | `/api/siteplan/kavling` | `Explicit` | `SiteplanController::kavling` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/siteplan/kavling` | `Explicit` | `SiteplanController::kavling` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/siteplan/proyek/(:num)` | `Explicit` | `SiteplanController::proyek/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/siteplan/proyek` | `Explicit` | `SiteplanController::proyek` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/siteplan/config` | `Explicit` | `SiteplanController::config` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/siteplan/others` | `Explicit` | `SiteplanController::others` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/siteplan/others` | `Explicit` | `SiteplanController::others` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/siteplan/detail/(:num)` | `Explicit` | `SiteplanController::detail/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/siteplan/detail` | `Explicit` | `SiteplanController::detail` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/siteplan/add_kavling` | `Explicit` | `SiteplanController::add_kavling` | 🟢 MIGRATED | Endpoint REST JSON `RAW_JSON_READY` Planning tambah kavling/others (`201`/`422` envelope via `wantsEnvelope()`) |
| `POST` | `/api/siteplan/edit_kavling` | `Explicit` | `SiteplanController::edit_kavling` | 🟢 MIGRATED | Endpoint REST JSON `RAW_JSON_READY` Planning ubah/pindah kavling single & batch (`200`/`422` envelope) |
| `POST` | `/api/siteplan/edit_others` | `Explicit` | `SiteplanController::edit_others` | 🟢 MIGRATED | Endpoint REST JSON `RAW_JSON_READY` Planning ubah/pindah item `others` (`200`/`422` envelope) |
| `POST` | `/api/siteplan/get_kavling_by_id` | `Explicit` | `SiteplanController::get_kavling_by_id` | 🟢 MIGRATED | Endpoint REST JSON `RAW_JSON_READY` fetch kavling tunggal untuk form Planning (`200`/`404` envelope) |
| `POST` | `/api/siteplan/get_kavling_by_multiple_id` | `Explicit` | `SiteplanController::get_kavling_by_multiple_id` | 🟢 MIGRATED | Endpoint REST JSON `RAW_JSON_READY` fetch kavling batch untuk form edit Planning (`200` envelope) |
| `POST` | `/api/siteplan/get_others` | `Explicit` | `SiteplanController::get_others` | 🟢 MIGRATED | Endpoint REST JSON `RAW_JSON_READY` fetch item `others` per ID/filter (`200` envelope + fallback header `X-Project-Id`) |
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
| `POST` | `/api/target/history` | `Explicit` | `TargetController::history` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
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
- `getKavlingTargetMap(int $idProyek)`
- `save(array $data)`
- `history(int $idTarget)`
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
- `app/Controllers/Api/Siteplan/TargetController.php` (122 baris, 4 public methods: `list()`, `detail()`, `save()`, `history()`)
- `app/Controllers/Api/Siteplan/KalendarController.php` (132 baris, 2 public methods: `meta()`, `getEvents()`)

### Services (7)
- `app/Services/Siteplan/SiteplanService.php` (953 baris, 16 public methods)
- `app/Services/Siteplan/SiteplanDetailService.php` (540 baris, 1 public methods)
- `app/Services/Siteplan/SiteplanDataService.php` (125 baris, 3 public methods)
- `app/Services/Siteplan/SiteplanUrgentService.php` (396 baris, 7 public methods)
- `app/Services/Siteplan/SiteplanVisualStatusService.php` (415 baris, 3 public methods)
- `app/Services/Siteplan/TargetSiteplanService.php` (205 baris, 5 public methods)
- `app/Services/Siteplan/KalendarService.php` (210 baris, 5 public methods)

### Repositories (5)
- `app/Repositories/Siteplan/SiteplanRepository.php` (946 baris, 49 public methods: `getProfilePerusahaan()`, `getPphList()`, `getPpnList()`, `getConfigShapeList()`, `getConfigShapeMap()`, `getConfigByRole(int $roleId)`, `getClusterOptionsByProyek(int $idProyek)`, `getClustersByProyek(int $idProyek)`, `getJalansByProyek(int $idProyek)`, `getTipesByProyek(int $idProyek)`, `getChecklistSubItemsWithGroup(?int $idProyek = null)`, `getChecklistItemsMap()`, `getHakAksesByUser(int $userId, ?string $namaHak = null)`, `getUserGroupId(int $userId)`, `findProyekById(int $idProyek)`, `getProyekByUserId(int $userId, bool $isAdmin = false)`, `userHasProjectAccess(?object $proyek, int $userId)`, `findDuplicateKavlingByJalan(string $noKavling, int $idJalan, ?int $excludeId = null)`, `isNoKavlingDuplicate(string $noKavling, int $idJalan, ?int $excludeId = null)`, `insertKavling(array $fields)`, `updateKavling(int $id, array $fields)`, `findKavlingsByIdsRaw(array $ids)`, `findKavlingPerintahBangunSnapshot(int $id)`, `findKavlingWithRelationsById(int $id)`, `findKavlingsWithRelationsByIds(array $ids)`, `findHargaKavlingById(int $id)`, `findHargaKavlingsByIds(array $ids)`, `findTurunPembangunanByIds(array $ids)`, `getDetailMkdt(int $idMkdt)`, `getDetailKavling(int $idKavling)`, `getDetailTipe(int $idTipe)`, `getDetailPricelist(int $idHargaJual)`, `getDetailKeuanganByMkdt(int $idMkdt)`, `getLastQueryString()`, `getDetailLogPembayaranByMkdt(int $idMkdt)`, `getDetailLogPembayaranDetailByMkdt(int $idMkdt)`, `getDetailPaymentSummaryByMkdt(int $idMkdt)`, `getDetailLastKeuanganUserByMkdt(int $idMkdt)`, `getDetailLegal(int $idLegal)`, `getDetailProduksi(int $idKavling)`, `getDetailFileProduksi(int $idKavling)`, `getDetailFilePph(int $idKavling)`, `getDetailFilePpn(int $idKavling)`, `getDetailHutangSubkon(int $idKavling)`, `getDetailFinanceLedgerIncome(?int $idMkdt, ?int $idKavling)`, `getDetailBankRetensiPending(int $idKavling)`, `getDetailDanaAkadSummary(int $idKavling)`, `getDetailBayarProduksi(int $idKavling)`, `getDetailSi(int $idKavling)`)
- `app/Repositories/Siteplan/SiteplanUrgentRepository.php` (453 baris, 14 public methods: `getUserGroupId(int $userId)`, `countTagihanOverdue(int $idProyek, string $today)`, `countTagihanDue(int $idProyek, string $today, string $limitDate)`, `countCashoutSubkonUrgent(int $idProyek, string $limitDate)`, `countPembangunanTelat(int $idProyek, string $today)`, `countMkdtDateAlerts(string $field, ?int $idProyek = null, ?string $fromDate = null, ?string $toDate = null)`, `hasSnoozeTable()`, `upsertSnooze(int $userId, int $idProyek, string $itemKey, string $itemType, string $snoozedUntil, string $now)`, `getActiveSnoozeKeys(int $userId, int $idProyek, string $now)`, `getTagihanUrgent(int $idProyek, string $limitDate)`, `getCashoutSubkonUrgent(int $idProyek, string $limitDate, int $groupId)`, `getMkdtDateUrgent(int $idProyek, string $field, string $limitDate)`, `getPembangunanTelat(int $idProyek, string $today)`, `getUnreadNotifications(int $idProyek, int $groupId)`)
- `app/Repositories/Siteplan/TargetSiteplanRepository.php` (157 baris, 6 public methods: `getByProject(int $idProyek)`, `getById(int $idTarget)`, `getKavlings(int $idTarget)`, `countKavlingsInProject(int $idProyek, array $idKavlings)`, `getHistory(int $idTarget)`, `getKavlingTargetMap(int $idProyek)`)
- `app/Repositories/Siteplan/OthersRepository.php` (187 baris, 8 public methods: `getAll(?int $id, int $idProyek, array $clusterIds, array $filters = [])`, `insertOthers(array $fields)`, `updateOthers(int $id, array $fields)`, `findById(int $id)`, `findProyekIdByJalanId(int $idJalan)`, `hasProgressHistory()`, `countProgressHistory(int $idOthers)`, `getProgressHistory(int $idOthers, int $limit, int $offset)`)
- `app/Repositories/Siteplan/KalendarRepository.php` (108 baris, 5 public methods: `getGroupsForUser(int $userId)`, `getAllGroups()`, `getPembayaranEvents(int $idProyek, string $start, string $end)`, `getJatuhTempoEvents(int $idProyek, string $start, string $end)`, `getMkdtEvents(int $idProyek, string $start, string $end)`)

### Models (4)
- `app/Models/SiteplanuploadModel.php`
- `app/Models/TargetSiteplanModel.php`
- `app/Models/TargetSiteplanKavlingModel.php`
- `app/Models/TargetSiteplanHistoryModel.php`

### Views (2 folder)
- `app/Views/siteplan` (16 file: `cashout_subkon.php`, `direksi.php`, `keuangan.php`, `legal.php`, `master.php`, `mkdt.php`, `modal_list_request_kavling.php`, `pajak.php`, `pilih_proyek.php`, `planning.php`, `produksi.php`, `produksi_mobile.php`, `request_kavling_modal.php`, `sales.php`, `target.php`, `test.php`)
- `app/Views/misc` (1 file: `kalender.php`)

### JavaScript (8 file)
- `public/assets/js/siteplan/master.js` (3931 baris)
- `public/assets/js/siteplan/composite-shape.js` (420 baris)
- `public/assets/js/siteplan/interaction-highlight.js` (143 baris)
- `public/assets/js/siteplan/shape-filter.js` (65 baris)
- `public/assets/js/siteplan/polygon-clip.js` (312 baris)
- `public/assets/js/siteplan-detail-modal.js` (2431 baris)
- `public/assets/js/siteplan/target.js` (244 baris)
- `public/assets/js/siteplan/tiket-overlay.js` (462 baris)

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
  - `TargetController`: Endpoint `/api/target/{list,detail,save,history}` untuk validasi dan delegasi target penjualan kavling bulanan ke `TargetSiteplanService`.
  - `KalendarController`: Endpoint `/api/kalendar/{events,meta}`, `/api/siteplan/kalendar/{events,meta}`, dan fallback `/kalendar/getEvents` dengan dukungan `?format=raw` dan filter divisi/event.
- **Service (`app/Services/Siteplan/SiteplanService.php`):**
  - `getIndexState(int $userId)`, `getViewSiteplanData(?int $requestedProyekId, int $userId)`, `getProduksiMobileData(...)`, `getMeta(?int $idProyek, int $userId)`: Agregasi state halaman web dan metadata SPA siteplan (daftar proyek berizin, cluster, jalan, tipe, pph, ppn, checklist, `config_shape`, `permissions.{role_id, is_admin, has_project_access}` via `resolvePrimaryRoleId()`, serta `siteplan_menus` dalam format JSON murni via `SiteplanMenuService::getMenusForSpa()`).
  - `addKavlingOrOthers(array $post, int $userId)`, `editKavling(array $post, int $userId)`, `editOthers(array $post, int $userId)`: Mutasi kavling dan aset penunjang `others` dalam transaksi DB (`transBegin`/`transCommit`/`transRollback`), validasi duplikasi nomor kavling per jalan, serta integrasi pencatatan riwayat (`HistoryService`, `MkdtHistoryService`) dan notifikasi (`NotifikasiService`).
  - `getKavlingByMultipleId(array $ids)`, `getHargaKavling(array $ids)`, `getTurunPembangunan(array $ids)`, `setTurunPembangunan(array $post, mixed $file, int $userId)`: Pengambilan harga/turun bangun dan eksekusi perintah turun pembangunan (upload file perintah bangun via `FileAccessService`, update batch dalam DB transaction, log `MkdtHistoryService`, dan trigger `NotifikasiService` `NotificationEvent::TURUN_PEMBANGUNAN`).
- **Service (`app/Services/Siteplan/SiteplanDetailService.php`):**
  - `getDetail(int $idKavling, ?int $idMkdt = null, ?int $idHargaJual = null, ?int $idLegal = null, ?int $userId = null)`: Ekstraksi agregator detail kavling lintas departemen (~540 baris: MKDT, kavling, tipe, pricelist, keuangan, log pembayaran, legal, produksi, PPh/PPN, hutang subkon, finance ledger, retensi bank, dana akad, SI) dengan lampiran akses dan Signed URL dari `FileAccessService`.
- **Service (`app/Services/Siteplan/SiteplanVisualStatusService.php`):**
  - `appendVisualRows(array $rows, ?DateTimeInterface $today = null)` / `buildRows($row, ?DateTimeInterface $today = null)`: Membangun array status visual multi-departemen (MKDT, Keuangan, Produksi, Legal, Pajak) untuk setiap unit kavling di peta siteplan.
  - `mkdtRow($row)`: Menghitung status dan marker visual segmen MKDT. Mengevaluasi boolean `$hasAkadAjb` (`akad == 1` atau tanggal akad terisi) dan `$hasAkadIndent` (`akad_indent == 1` atau tanggal akad indent terisi). Jika salah satu terpenuhi, status MKDT adalah `'Akad'` (`Akad Subsidi` atau `Akad Komersil`). Jika kavling sudah mencapai `Akad Indent` namun belum `Akad AJB` (`$hasAkadIndent && !$hasAkadAjb`), marker visual `[['config_name' => 'Akad Indent', 'position' => 0.5]]` ditambahkan (garis aksen biru). Saat `Akad AJB` terpenuhi (`$hasAkadAjb`), marker visual dilepas sehingga warna solid Akad tampil penuh.
  - `buildFill($row, ?array $visualRows = null)`: Menentukan warna isian (fill color) poligon berdasarkan konfigurasi status visual kavling.
- **Service (`app/Services/Siteplan/SiteplanDataService.php`):**
  - `getKavlings(array $params)` & `getOthers(array $params)`: Pengambilan data kavling dan fasum/others dengan filtering proyek, cluster, dan status visual/overlay.
- **Service (`app/Services/Siteplan/SiteplanUrgentService.php`):**
  - `getSummary()` / `getUrgentSummary()`: Menghitung kavling-kavling yang memerlukan perhatian mendesak berdasarkan filter waktu/status per divisi (menggunakan `SiteplanUrgentRepository`).
  - `getDashboardAlertCounts(?int $idProyek)`: Agregasi tanpa batas kuota untuk kartu peringatan dashboard utama (tagihan overdue, tagihan due 7 hari, cashout subkon, bangunan telat, SP3K expire, rencana akad).
  - `countMkdtDateAlerts(string $field, ?int $idProyek, ?string $fromDate, ?string $toDate)`: Menghitung kavling aktif dengan batas tanggal MKDT (`sp3k_tgl_exp`, `rencana_akad_tgl`) pada rentang tertentu, memvalidasi `akad_tgl` belum terlaksana dan unit tidak batal.
  - `snoozeUrgentItem(int $userId, int $idProyek, string $itemKey, int $minutes)`: Menyimpan snooze alert per user.
- **Service (`app/Services/Siteplan/TargetSiteplanService.php`):**
  - `save(array $data)`: Logika transaksi penyimpanan target kavling bulanan dan pencatatan riwayat target.
- **Service (`app/Services/Siteplan/KalendarService.php`):**
  - `getCalendarViewData(int $userId = 0)` & `getEventsForCalendar(int $idProyek, string $start, string $end, array $types = [])`: Metadata divisi/filter serta pengambilan event kalender untuk jatuh tempo, pembayaran, dan MKDT.
- **Repository (`app/Repositories/Siteplan/SiteplanRepository.php`):**
  - Query konfigurasi (`getConfigShapeList`, `getConfigShapeMap`, `getConfigByRole`), hak akses user (`getHakAksesByUser`), parameterized `UNION ALL` file pajak (`getDetailFilePph`, `getDetailFilePpn`), mutasi kavling (`insertKavling`, `updateKavling`, `findDuplicateKavlingByJalan`), dan seluruh query agregasi detail kavling lintas tabel.
- **Repository (`app/Repositories/Siteplan/SiteplanUrgentRepository.php`):**
  - Ekstraksi penuh seluruh query database dari `SiteplanUrgentService` (tagihan overdue/due, cashout subkon, bangunan telat, alert tanggal MKDT, snooze, dan notifikasi belum dibaca).
- **Repository (`app/Repositories/Siteplan/TargetSiteplanRepository.php`):**
  - `getByProject(int $idProyek)`, `getKavlingTargetMap(int $idProyek)`, `countKavlingsInProject(...)`: Data access target siteplan dan pemetaan kavling target per proyek.
- **Repository (`app/Repositories/Siteplan/OthersRepository.php`):**
  - `getAll(?int $id, int $idProyek, array $clusterIds, array $filters = [])`: Mengelola data aset penunjang non-kavling pada siteplan. Menerapkan pengelompokan filter scope (`siteplan`, `produksi`, serta `masalah` jika kategori 'Masalah' dicentang) dan secara eksplisit menyertakan `orWhere('act_tm.active_tiket_id IS NOT NULL', null, false)` agar entitas others berscope `masalah` dengan tiket aktif otomatis tampil di kanvas default.
  - `insertOthers(array $fields)`, `updateOthers(int $id, array $fields)`, `findById(int $id)`, `findProyekIdByJalanId(int $idJalan)`: Operasi CRUD & lookup relasi proyek untuk tabel pemilik `others`.
  - `addActiveTiketSelect(BaseBuilder $builder)`: Query JOIN ke derived table `tiket_masalah` (`ref_type = 'others'`, `status NOT IN ('selesai', 'batal', 'hold')`) untuk menyertakan `active_tiket_id`, `active_tiket_prioritas`, dan `active_tiket_status`.
- **Repository (`app/Repositories/Siteplan/KalendarRepository.php`):**
  - `getGroupsForUser()`, `getAllGroups()`, `getPembayaranEvents()`, `getJatuhTempoEvents()`, `getMkdtEvents()`: Query grup user, jadwal pembayaran, jatuh tempo, dan agenda MKDT.
- **Frontend JavaScript (`public/assets/js/siteplan/tiket-overlay.js`):**
  - `SiteplanTiketOverlay`: Modul UMD layer animasi overlay tiket masalah di KonvaJS (`getPriorityColor`, `calculateOpacity`, `calculateCentroid`, `createOverlayShape`, `createBadgeShape`, `createAnimation`, `buildOverlayShapes`, `stopAnimation`, `clearOverlay`). Mendukung entitas `kavling` & `others`, safe centroid calculation, frame rate capping 30 FPS, pulsing high-contrast stroke, dan centroid warning badge `!`.
- **Frontend JavaScript (`public/assets/js/siteplan/master.js`):**
  - `siteplanProblemItems` & `syncSiteplanTiketOverlay()`: Menggabungkan item masalah kavling dan others secara atomik sebelum meneruskan data gabungan ke `SiteplanTiketOverlay.buildOverlayShapes()`.
- **Frontend JavaScript (`public/assets/js/siteplan-detail-modal.js`):**
  - `resolveMkdtStatusInfo(m)`, `renderKavlingTimeline(mkdt, currentStatus)`, `updateRingkasanHasilAkad(pa)`: Resolusi hierarki status MKDT (Batal -> Akad AJB -> Akad Indent -> SP3K -> Wawancara -> Booking), stepper milestone horizontal Opsi B, dan bar/legend Total Hasil Akad.
- **Frontend View (`app/Views/misc/kalender.php`):**
  - Menggunakan `const kalendarBaseUrl` (menggantikan `var base_url`), callback error `failureCallback(new Error(...))`, dan fallback warna event `#2057a3`.

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
  - Terintegrasi dengan modul `TiketMasalah` dan `Transaksi`:
    - `KavlingRepository::addActiveTiketSelect`: data kavling dari `POST /siteplan/get/all` dan `/api/siteplan/kavling` menyertakan atribut `active_tiket_id`, `active_tiket_prioritas`, dan `active_tiket_status` untuk kavling bermasalah aktif.
    - `OthersRepository::addActiveTiketSelect`: data fasum/others dari `POST /siteplan/get_others` dan `/api/siteplan/others` menyertakan atribut `active_tiket_id`, `active_tiket_prioritas`, dan `active_tiket_status` untuk fasilitas non-kavling bermasalah aktif.
    - `OthersRepository::getAll()`: menyertakan kondisi `orWhere('act_tm.active_tiket_id IS NOT NULL', null, false)` sehingga item others berscope `masalah` otomatis termuat dan teranimasi pulse stroke serta badge peringatan `!` pada default canvas siteplan tanpa mengharuskan pengguna mencentang filter kategori 'Masalah'.
    - `SiteplanTiketOverlay` (`public/assets/js/siteplan/tiket-overlay.js`) dan `master.js` (`syncSiteplanTiketOverlay`): mengagregasikan masalah kavling dan others secara atomik dan merender `tiketOverlayLayer` pada peta KonvaJS.

## Change Log
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

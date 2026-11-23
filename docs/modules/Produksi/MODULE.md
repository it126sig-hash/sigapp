# Module: Produksi

**Status:** `REFACTORED`  
**Readiness Score:** `100/100`  
**Last Synced:** `2026-11-23 10:21:14` @ `2b19370`  

## Purpose
Manajemen konstruksi lapangan perumahan, monitoring tahapan serah terima fisik (ST 0%, 25%, 50%, 75%, 100%), pelaporan komplain paska serah terima, pengawasan sub-kontraktor (subkon), pengelolaan gambar kerja & SLF, serta sistem checklist inspeksi lapangan berbasis hierarki grup, item, dan sub-item.

## Out of scope
- Pencatatan transaksi pemesanan unit kavling oleh konsumen (Domain `MKDT`).
- Pembayaran kasbon, pengajuan SPP, dan pencairan termin subkon secara akuntansi (Domain `Keuangan` / `Cashout`).
- Pengurusan perizinan legalitas tanah, HGB, dan PBG (Domain `Legal`).
- Master data perumahan, cluster kavling, dan tipe bangunan (Domain `Proyek`).

## Invariants
- Modul lain tidak boleh memanipulasi tabel pemilik (`owner_tables`) secara langsung.
- Perubahan progres fisik (ST 0% s/d 100%) dan pengajuan pembayaran subkon wajib divalidasi di level Service.
- Seluruh input nominal rupiah pada pembayaran produksi subkon wajib dibersihkan menggunakan `cleanNumeric()` sebelum masuk operasi database untuk mencegah kegagalan kalkulasi finansial.
- Semua mutasi data multi-tabel (seperti unggah foto lapangan, pembayaran produksi, pembaruan status kavling) wajib dibungkus dalam DB Transaction (`transStart()` / `transComplete()`).

## Database Tables
### Owner Table(s)
- `produksi` *(akses tulis eksklusif hanya untuk modul Produksi)*
- `komplain` *(akses tulis eksklusif hanya untuk modul Produksi)*
- `subkon` *(akses tulis eksklusif hanya untuk modul Produksi)*
- `gambar_kerja` *(akses tulis eksklusif hanya untuk modul Produksi)*
- `serah_terima` *(akses tulis eksklusif hanya untuk modul Produksi)*
- `checklist_group` *(akses tulis eksklusif hanya untuk modul Produksi)*
- `checklist_item` *(akses tulis eksklusif hanya untuk modul Produksi)*
- `checklist_subitem` *(akses tulis eksklusif hanya untuk modul Produksi)*
- `checklist_work` *(akses tulis eksklusif hanya untuk modul Produksi)*

### Shared Tables (Dibaca dari modul lain)
- `kavling`
- `jalan`

## Contracts
### A. Public HTTP Endpoints & Routes Inventory
| Method | URI / Pattern | Tipe | Handler | Status Migrasi | Target API / Catatan |
|---|---|---|---|---|---|
| `GET` | `/produksi/mobile` | `Explicit` | `ProduksiController::produksiMobile` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/checklist-group` | `Explicit` | `ChecklistGroupController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/checklistGroup` | `Explicit` | `ChecklistGroupController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/checklist-item` | `Explicit` | `ChecklistItemController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/checklistItem` | `Explicit` | `ChecklistItemController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/checklist-sub-item` | `Explicit` | `ChecklistSubItemController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/checklistSubItem` | `Explicit` | `ChecklistSubItemController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/produksi/get_data_by_id` | `Explicit` | `ProduksiController::get_data_by_id` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/produksi/getBayarProduksi` | `Explicit` | `ProduksiController::getBayarProduksi` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/produksi/getBayarProduksiListItem` | `Explicit` | `ProduksiController::getBayarProduksiListItem` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/produksi/saveBayarProduksi` | `Explicit` | `ProduksiController::saveBayarProduksi` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/produksi/deleteBayarProduksi` | `Explicit` | `ProduksiController::deleteBayarProduksi` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/produksi/get_data_komplain_by_id` | `Explicit` | `ProduksiController::get_data_komplain_by_id` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/produksi/save_komplain_produksi` | `Explicit` | `ProduksiController::save_komplain_produksi` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/produksi/save` | `Explicit` | `ProduksiController::save` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/produksi/upload-mobile` | `Explicit` | `ProduksiController::uploadMobile` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/produksi/history` | `Explicit` | `ProduksiController::history` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/produksi/saveSLF` | `Explicit` | `ProduksiController::saveSLf` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/produksi/getSlf` | `Explicit` | `ProduksiController::getSlf` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/produksi/hapusSLF` | `Explicit` | `ProduksiController::hapusSLF` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/produksi/getSLFPDF/(:num)` | `Explicit` | `ProduksiController::getSLFPDF/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/produksi/getKavling` | `Explicit` | `ProduksiController::getKavling` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/produksi/hapus_foto` | `Explicit` | `ProduksiController::hapus_foto` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/produksi/get_gambarkerja` | `Explicit` | `ProduksiController::get_gambarkerja` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/produksi/add_jalan` | `Explicit` | `ProduksiController::add_jalan` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/produksi/edit_others` | `Explicit` | `ProduksiController::edit_others` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/produksi/sales/get_data_by_id` | `Explicit` | `SalesController::get_data_by_id` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/produksi/sales/save_checklist` | `Explicit` | `SalesController::save_checklist` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/produksi/sales/get_data_komplain_by_id` | `Explicit` | `SalesController::get_data_komplain_by_id` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/produksi/sales/batalkan_komplain` | `Explicit` | `SalesController::batalkan_komplain` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/produksi/sales/save_komplain_sales` | `Explicit` | `SalesController::save_komplain_sales` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/produksi/sales/get_data_serah_terima_by_id` | `Explicit` | `SalesController::get_data_serah_terima_by_id` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/produksi/sales/save_serah_terima` | `Explicit` | `SalesController::save_serah_terima` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/sales/get_data_by_id` | `Explicit` | `SalesController::get_data_by_id` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/sales/save_checklist` | `Explicit` | `SalesController::save_checklist` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/sales/get_data_komplain_by_id` | `Explicit` | `SalesController::get_data_komplain_by_id` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/sales/batalkan_komplain` | `Explicit` | `SalesController::batalkan_komplain` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/sales/save_komplain_sales` | `Explicit` | `SalesController::save_komplain_sales` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/sales/get_data_serah_terima_by_id` | `Explicit` | `SalesController::get_data_serah_terima_by_id` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/sales/save_serah_terima` | `Explicit` | `SalesController::save_serah_terima` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/checklistGroup/getOne` | `Explicit` | `ChecklistGroupController::getOne` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/checklistGroup/add` | `Explicit` | `ChecklistGroupController::add` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/checklistGroup/edit` | `Explicit` | `ChecklistGroupController::edit` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/checklistGroup/remove` | `Explicit` | `ChecklistGroupController::remove` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/checklist-group/getOne` | `Explicit` | `ChecklistGroupController::getOne` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/checklist-group/add` | `Explicit` | `ChecklistGroupController::add` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/checklist-group/edit` | `Explicit` | `ChecklistGroupController::edit` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/checklist-group/remove` | `Explicit` | `ChecklistGroupController::remove` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/checklistItem/getOne` | `Explicit` | `ChecklistItemController::getOne` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/checklistItem/add` | `Explicit` | `ChecklistItemController::add` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/checklistItem/edit` | `Explicit` | `ChecklistItemController::edit` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/checklistItem/remove` | `Explicit` | `ChecklistItemController::remove` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/checklist-item/getOne` | `Explicit` | `ChecklistItemController::getOne` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/checklist-item/add` | `Explicit` | `ChecklistItemController::add` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/checklist-item/edit` | `Explicit` | `ChecklistItemController::edit` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/checklist-item/remove` | `Explicit` | `ChecklistItemController::remove` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/checklistSubItem/getOne` | `Explicit` | `ChecklistSubItemController::getOne` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/checklistSubItem/add` | `Explicit` | `ChecklistSubItemController::add` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/checklistSubItem/edit` | `Explicit` | `ChecklistSubItemController::edit` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/checklistSubItem/remove` | `Explicit` | `ChecklistSubItemController::remove` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/checklist-sub-item/getOne` | `Explicit` | `ChecklistSubItemController::getOne` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/checklist-sub-item/add` | `Explicit` | `ChecklistSubItemController::add` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/checklist-sub-item/edit` | `Explicit` | `ChecklistSubItemController::edit` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/checklist-sub-item/remove` | `Explicit` | `ChecklistSubItemController::remove` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |

### B. Public PHP Services (Backend Contract Antar-Modul)
#### `ProduksiService.php`
- `cleanNumeric(mixed $value)`
- `save(array $var, array $uploadedFiles, int $userId)`
- `saveMobileUpload(int $idKavling, ?int $idProduksi, array $files, array $vars, int $userId)`
- `saveBayarProduksi(int $idKavling, int $idItemProduksi, mixed $nominal, string $tanggalBayar, string $keterangan, int $userId)`
- `deleteBayarProduksi(int $id, int $userId)`
- `getBayarProduksiData(int $idKavling)`
- `getDetailByKavling(int $idKavling, ?int $idProduksi)`
- `getHistory(int $idKavling, int $limit = 10, int $offset = 0)`
- `getKomplainDetail(int $idKavling)`
- `saveKomplainProduksi(array $data, array $uploadedFiles, int $userId)`
- `saveKomplainSales(array $data, array $uploadedFiles, int $userId)`
- `batalkanKomplain(int $idKavling, ?int $idKomplain)`
- `getSerahTerimaDetail(int $idKavling)`
- `saveSerahTerima(array $data, int $userId)`
- `getChecklistSalesDetail(int $idKavling)`
- `saveChecklistSales(int $idKavling, mixed $isChecked, array $postVars, int $userId)`
- `deleteFoto(int $id, int $userId)`
- `addJalan(array $postVars, int $userId)`
- `editOthers(int $id, array $postVars, array $uploadedFiles, int $userId)`
- `saveSlf(array $postVars, int $userId)`
- `deleteSlf(int $id)`
#### `ProduksiFileService.php`
- `uploadFotoGroups(array $requestFiles, array $requestVars, int $idKavling)`
- `moveFotoToTrash(object $file)`
#### `PhotoGpsMetadataService.php`
- `prepareDownload(array $file)`
- `cleanup(?string $temporaryPath)`
#### `ChecklistService.php`
- `getAllGroups(bool $asDatatable = false)`
- `getGroup(int $id)`
- `createGroup(array $data, int $userId)`
- `updateGroup(int $id, array $data, int $userId)`
- `deleteGroup(int $id)`
- `getAllItems(bool $asDatatable = false)`
- `getItem(int $id)`
- `createItem(array $data, int $userId)`
- `updateItem(int $id, array $data, int $userId)`
- `deleteItem(int $id)`
- `getAllSubItems(bool $asDatatable = false)`
- `getSubItem(int $id)`
- `createSubItem(array $data, int $userId)`
- `updateSubItem(int $id, array $data, int $userId)`
- `deleteSubItem(int $id)`
- `getActiveGroups()`
- `getActiveGroupsWithItems()`

## File Inventory
### Controllers (10)
- `app/Controllers/Web/Produksi/ProduksiController.php` (101 baris, 4 public methods: `index()`, `produksiMobile()`, `getSLFPDF($id)`, `direksi()`)
- `app/Controllers/Web/Produksi/ChecklistGroupController.php` (18 baris, 1 public methods: `index()`)
- `app/Controllers/Web/Produksi/ChecklistItemController.php` (27 baris, 1 public methods: `index()`)
- `app/Controllers/Web/Produksi/ChecklistSubItemController.php` (27 baris, 1 public methods: `index()`)
- `app/Controllers/Api/Produksi/ProduksiController.php` (435 baris, 20 public methods: `meta()`, `get_data_by_id()`, `history()`, `getBayarProduksiListItem()`, `getBayarProduksi()`, `saveBayarProduksi()`, `deleteBayarProduksi()`, `get_data_komplain_by_id()`, `save_komplain_produksi()`, `save()`, `uploadMobile()`, `saveSLf()`, `getSlf()`, `hapusSLF()`, `getSLFPDF($id)`, `getKavling()`, `hapus_foto()`, `get_gambarkerja()`, `add_jalan()`, `edit_others()`)
- `app/Controllers/Api/Produksi/SubkonController.php` (37 baris, 1 public methods: `getList()`)
- `app/Controllers/Api/Produksi/SalesController.php` (92 baris, 7 public methods: `get_data_by_id()`, `save_checklist()`, `get_data_komplain_by_id()`, `batalkan_komplain()`, `save_komplain_sales()`, `get_data_serah_terima_by_id()`, `save_serah_terima()`)
- `app/Controllers/Api/Produksi/ChecklistGroupController.php` (98 baris, 5 public methods: `getAll()`, `getOne()`, `add()`, `edit($id = null)`, `remove()`)
- `app/Controllers/Api/Produksi/ChecklistItemController.php` (98 baris, 5 public methods: `getAll()`, `getOne()`, `add()`, `edit($id = null)`, `remove()`)
- `app/Controllers/Api/Produksi/ChecklistSubItemController.php` (98 baris, 5 public methods: `getAll()`, `getOne()`, `add()`, `edit($id = null)`, `remove()`)

### Services (4)
- `app/Services/Produksi/ProduksiService.php` (1094 baris, 21 public methods)
- `app/Services/Produksi/ProduksiFileService.php` (238 baris, 2 public methods)
- `app/Services/Produksi/PhotoGpsMetadataService.php` (191 baris, 2 public methods)
- `app/Services/Produksi/ChecklistService.php` (334 baris, 17 public methods)

### Repositories (2)
- `app/Repositories/Produksi/ProduksiRepository.php` (681 baris, 50 public methods: `getFilesByKavling(int $idKavling)`, `insertFiles(array $files)`, `hasProduksiChangeHistoryTable()`, `insertProduksiChangeHistory(array $fields)`, `countProduksiChangeHistory(int $idKavling)`, `getProduksiChangeHistory(int $idKavling, int $limit, int $offset)`, `getFileById(int $id)`, `deleteFileById(int $id)`, `getBayarList(int $idKavling)`, `getBayarItemList(string $search)`, `getRiwayatBayarByKavling(int $idKavling)`, `insertBayarSingle(array $data)`, `deleteBayar(int $id)`, `getByIdWithUsers(int $idProduksi)`, `getSlfList(?int $idProyek = null)`, `getSlfById(int $id)`, `getSlfKavlingData(array $idKavlingList)`, `insertSlf(array $data)`, `deleteSlfById(int $id)`, `getKavlingByProyek(int $idProyek, string $search, ?int $idKavling = null)`, `getKavlingProduksiById(int $idKavling)`, `createOther(array $fields)`, `getOtherById(int $id)`, `updateOthers(int $id, array $fields)`, `hasJalanProgressHistoryTable()`, `insertJalanProgressHistory(array $fields)`, `getSubkonList(string $search)`, `getGambarKerjaById(int $id)`, `getKavlingById(int $idKavling)`, `getKavlingCheckStatus(int $idKavling)`, `getKavlingOwnerKomplain(int $idKavling, int $idKomplain)`, `updateKavling(int $idKavling, array $data)`, `getProduksiRaw(int $idProduksi)`, `insertProduksi(array $data)`, `updateProduksi(int $idProduksi, array $data)`, `getKomplainWithUsers(int $idKavling)`, `insertKomplain(array $data)`, `updateKomplain(int $idKomplain, array $data)`, `deleteKomplain(int $idKomplain)`, `getSerahTerimaByKavling(int $idKavling)`, `insertSerahTerima(array $data)`, `updateSerahTerima(int $idSerahTerima, array $data)`, `getChecklistWorkByKavling(int $idKavling)`, `getChecklistWorkWithUsers(int $idKavling)`, `getChecklistWorkForProduksi(int $idKavling)`, `getAllSubitemIds()`, `upsertChecklistWork(int $idKavling, int $idSubitem, array $data)`, `getChecklistSubItemsWithGroup()`, `getProyekDetailForSlf(int $idProyek)`, `hasUpdateTanggalPembangunanAccess(int $userId)`)
- `app/Repositories/Produksi/ChecklistRepository.php` (199 baris, 18 public methods: `getAllGroups()`, `getActiveGroups()`, `getGroupById(int $id)`, `insertGroup(array $data)`, `updateGroup(int $id, array $data)`, `toggleGroupStatus(int $id)`, `getAllItems()`, `getItemById(int $id)`, `getItemsByGroup(int $idGroup)`, `insertItem(array $data)`, `updateItem(int $id, array $data)`, `toggleItemStatus(int $id)`, `getAllSubItems()`, `getSubItemById(int $id)`, `insertSubItem(array $data)`, `updateSubItem(int $id, array $data)`, `toggleSubItemStatus(int $id)`, `getActiveGroupsWithItems()`)

### Models (9)
- `app/Models/ProduksiModel.php`
- `app/Models/KomplainModel.php`
- `app/Models/SubkonModel.php`
- `app/Models/GambarkerjaModel.php`
- `app/Models/SerahTerimaModel.php`
- `app/Models/ChecklistGroupModel.php`
- `app/Models/ChecklistItemModel.php`
- `app/Models/ChecklistSubItemModel.php`
- `app/Models/ChecklistWorkModel.php`

### Views (2 folder)
- `app/Views/checklist` (3 file: `checklistGroup.php`, `checklistItem.php`, `checklistSubItem.php`)
- `app/Views/direksi` (1 file: `list-tagihan-pending.php`)

### JavaScript (3 file)
- `public/assets/js/siteplan/produksi.js` (1900 baris)
- `public/assets/js/siteplan/produksi_mobile.js` (354 baris)
- `public/assets/js/siteplan/direksi.js` (131 baris)

### Zero-Downtime Shims (11 file)
- `app/Controllers/Produksi.php`
- `app/Controllers/Api/ProduksiController.php`
- `app/Controllers/Subkon.php`
- `app/Controllers/ChecklistGroup.php`
- `app/Controllers/ChecklistItem.php`
- `app/Controllers/ChecklistSubItem.php`
- `app/Controllers/Sales.php`
- `app/Services/ProduksiService.php`
- `app/Services/ProduksiFileService.php`
- `app/Services/PhotoGpsMetadataService.php`
- `app/Repositories/ProduksiRepository.php`

## Key Functions
- **Controller (`App\Controllers\Api\Produksi\ProduksiController`):**
  - `meta()`: Menyediakan metadata JSON murni (RAW_JSON_READY) untuk opsi dropdown filter subkon, cluster, dan tahapan ST tanpa coupling HTML, serta `permissions.update_tanggal_pembangunan` (`true` untuk role `1`, `7`, `8`).
  - `save()`: Validasi request input update progres ST kavling, delegasi mutasi multi-tabel ke `ProduksiService::save()`.
  - `hapus_foto()`: Endpoint `POST /api/produksi/hapus_foto` dengan guard login (`401`), guard role Admin (`1`) / Produksi (`7`) (`403`), validasi integer positif `id` (`400`), delegasi ke `ProduksiService::deleteFoto()`, dan respons JSON standar `{ success, messages, data: { id, id_kavling }, token }`.
  - `resolveUserRoleIds(int $userId)` *(private)*: Resolusi array integer role user aktif dengan fallback ke `FileAccessService::getUserRoles($userId)`.
  - `saveBayarProduksi()` & `deleteBayarProduksi()`: Endpoint mutasi finansial pembayaran subkon, membersihkan input nominal dan mendelegasikan transaksi ke Service.
  - `uploadMobile()`: Endpoint penerima multi-upload foto lapangan dengan stempel GPS.
  - `save_komplain_produksi()` & `get_data_komplain_by_id()`: Manajemen siklus perbaikan komplain unit.
- **Controller (`App\Controllers\Api\Produksi\SalesController`):**
  - `save_serah_terima()`: Pencatatan serah terima fisik bangunan kepada konsumen.
  - `save_checklist()`: Penyimpanan hasil audit checklist kelayakan fisik bangunan sebelum serah terima.
  - `save_komplain_sales()` & `batalkan_komplain()`: Pengajuan dan pembatalan komplain konsumen dari sisi Sales.
- **Service (`App\Services\Produksi\ProduksiService`):**
  - `cleanNumeric(mixed $value)`: Pembersihan format input nominal mata uang (membuang `.` atau format ribuan rupiah) menjadi float/integer murni untuk mencegah kegagalan kalkulasi DB.
  - `saveBayarProduksi()`: Transaksi DB (`transStart`/`transComplete`) penyimpanan pembayaran termin subkon dengan validasi sanitasi nominal uang via `cleanNumeric()`.
  - `save(array $var, array $uploadedFiles, int $userId)`: Pengendali transaksi utama progres ST kavling; mengoordinasikan update status `kavling`, `produksi`, upload foto via `ProduksiFileService`, dan pencatatan audit log `insertProduksiChangeHistory()`.
  - `deleteFoto(int $id, int $userId)`: Validasi `$id > 0` (`400`), lookup record foto (`404`), pemindahan file & thumbnail ke trash via `ProduksiFileService::moveFotoToTrash()` (`500` bila gagal IO), serta transaksi DB (`transStart()`/`transComplete()`) untuk `deleteFileById()` + `insertProduksiChangeHistory('delete_file')`, mengembalikan `data: { id, id_kavling }`.
  - `saveKomplainProduksi()` & `saveKomplainSales()`: Penanganan tiket komplain unit dengan enkapsulasi DB transaction.
  - `saveSerahTerima()`: Penyimpanan dokumen BAST fisik unit ke tabel `serah_terima`.
- **Service (`App\Services\Produksi\ProduksiFileService`):**
  - `uploadFotoGroups()`: Validasi mime type, enkripsi nama file, resize/kompresi foto lapangan, dan penyimpanan ke storage folder per kavling.
  - `moveFotoToTrash(object $file)`: Soft delete foto fisik dan thumbnail (`thumbnails/`) ke `uploads/produksi/trash/YYYYMMDD/`; tetap mengembalikan `true` jika file fisik sudah hilang di disk agar pembersihan record yatim di DB tetap berjalan.
- **Service (`App\Services\Produksi\PhotoGpsMetadataService`):**
  - `prepareDownload()`: Membaca tag EXIF dari file foto, mengekstrak koordinat lintang/bujur (Latitude/Longitude), dan merender preview watermarked GPS untuk kebutuhan verifikasi lapangan.
  - `cleanup(?string $temporaryPath)`: Pembersihan berkas sementara pasca-unduh metadata foto.
- **Service (`App\Services\Produksi\ChecklistService`):**
  - Manajemen pohon hierarki checklist (`Group -> Item -> SubItem`) serta penyajian DataTables atau data murni untuk inspeksi lapangan.
- **Repository (`App\Repositories\Produksi\ProduksiRepository`):**
  - Mengelola seluruh query pembacaan dan penulisan ke tabel `produksi`, `komplain`, `subkon`, `gambar_kerja`, `serah_terima`, `checklist_work`, serta riwayat progres jalan.
  - Menghapus method mati/dead-code `upsertBayarItems()` yang memiliki 0 pemanggil lintas codebase.
- **Repository (`App\Repositories\Produksi\ChecklistRepository`):**
  - Query struktur checklist hierarkis dengan optimasi JOIN antar `checklist_group`, `checklist_item`, dan `checklist_subitem`.
- **Frontend JavaScript (`public/assets/js/siteplan/produksi.js` & `public/assets/js/scripts.js`):**
  - `open_fproduksi(sh, role, id_kavling)` (`produksi.js`): Membuka modal `#modal_divisi7`, menghapus restriction disable `.tgl_bangun` berbasis `has_akses.update_tanggal_pembangunan` maupun kondisi `!r.tanggal_pembangunan` (memastikan `.tgl_bangun` + Flatpickr `altInput` selalu aktif sementara validasi wajib-isi tetap berlaku), serta menambahkan `'listrik_disediakan_foto'` pada daftar reset kategori.
  - `showFoto(data, imbuhan, del, options)` (`scripts.js`): Merender tile file/foto tersimpan dengan guard ketat `allowDelete = del === true || del === "true"` sehingga modal read-only (`#modal_detail` dengan `del = "false"`) tidak merender tombol hapus, sedangkan `#modal_divisi7` merender tombol `.btn-hapus-foto` (`type="button"`) di `.detail-file-action` sejajar dengan tombol Lihat & Download.
  - `hapusFoto(id)` (`scripts.js`): Konfirmasi SweetAlert2, request `POST /api/produksi/hapus_foto`, refresh `csrfHash` pada `success`/`error`, menghapus elemen `#foto_produksi_<id>`, dan me-refresh `loadProduksiHistory()` saat `#modal_divisi7` sedang terbuka.

## Deprecated & Legacy Shims
- **Deprecated Routes / Endpoints:**
  - Route flat legacy `/sales/*` -> **DEPRECATED**, dialihkan ke `App\Controllers\Api\Produksi\SalesController`. Direkomendasikan menggunakan prefix `/api/produksi/sales/*`.
  - Route flat legacy `/subkon/list/ambil` -> **DEPRECATED**, dialihkan ke `App\Controllers\Api\Produksi\SubkonController::getList`. Direkomendasikan menggunakan `/api/subkon/list`.
  - Route AutoRoute implisit `/produksi/*`, `/checklist_group/*`, `/checklist_item/*`, `/checklist_sub_item/*` -> **DEPRECATED**, seluruhnya telah digantikan oleh rute eksplisit modern di `/api/produksi/*` dan `/api/checklist-*/*`.
- **Deprecated Classes / Shims (Zero-Downtime Shims):**
  - `app/Controllers/Produksi.php` — **DEPRECATED (Zero-Downtime Shim)**: Gunakan `App\Controllers\Web\Produksi\ProduksiController` / `App\Controllers\Api\Produksi\ProduksiController`.
  - `app/Controllers/Api/ProduksiController.php` — **DEPRECATED (Zero-Downtime Shim)**: Gunakan `App\Controllers\Api\Produksi\ProduksiController`.
  - `app/Controllers/Subkon.php` — **DEPRECATED (Zero-Downtime Shim)**: Gunakan `App\Controllers\Api\Produksi\SubkonController`.
  - `app/Controllers/ChecklistGroup.php` — **DEPRECATED (Zero-Downtime Shim)**: Gunakan `App\Controllers\Web\Produksi\ChecklistGroupController` / `App\Controllers\Api\Produksi\ChecklistGroupController`.
  - `app/Controllers/ChecklistItem.php` — **DEPRECATED (Zero-Downtime Shim)**: Gunakan `App\Controllers\Web\Produksi\ChecklistItemController` / `App\Controllers\Api\Produksi\ChecklistItemController`.
  - `app/Controllers/ChecklistSubItem.php` — **DEPRECATED (Zero-Downtime Shim)**: Gunakan `App\Controllers\Web\Produksi\ChecklistSubItemController` / `App\Controllers\Api\Produksi\ChecklistSubItemController`.
  - `app/Controllers/Sales.php` — **DEPRECATED (Zero-Downtime Shim)**: Gunakan `App\Controllers\Api\Produksi\SalesController`.
  - `app/Services/ProduksiService.php` — **DEPRECATED (Zero-Downtime Shim)**: Gunakan `App\Services\Produksi\ProduksiService`.
  - `app/Services/ProduksiFileService.php` — **DEPRECATED (Zero-Downtime Shim)**: Gunakan `App\Services\Produksi\ProduksiFileService`.
  - `app/Services/PhotoGpsMetadataService.php` — **DEPRECATED (Zero-Downtime Shim)**: Gunakan `App\Services\Produksi\PhotoGpsMetadataService`.
  - `app/Repositories/ProduksiRepository.php` — **DEPRECATED (Zero-Downtime Shim)**: Gunakan `App\Repositories\Produksi\ProduksiRepository`.
- **Deprecated Dead Methods:**
  - `ProduksiRepository::upsertBayarItems()` — **DEPRECATED & PRUNED**: Method batch-upsert bayar yang tidak pernah dipanggil di mana pun (0 pemanggil di codebase) telah dibersihkan dari repository modular baru.

## Readiness Checklist
- [x] Has Service Layer
- [x] Has Repository Layer
- [x] Web & API Controllers Separated
- [x] Explicit Routes Defined
- [x] Zero Controller Layer Violations
- [x] MODULE.md Initialized

## Dead Code Candidates
_Jalankan `php spark module:deadcode Produksi` untuk mengaudit dead code kandidat._

## Notes
- **Cross-Module Callers:**
  - Modul `Siteplan`: Memanggil modal `app/Views/siteplan/produksi.php` (`#modal_divisi7`) dan skrip `public/assets/js/siteplan/produksi.js`, `public/assets/js/siteplan/produksi_mobile.js` yang berinteraksi intensif dengan API `/api/produksi/*` dan `ProduksiService`, serta `SiteplanService::getViewSiteplanData()` / `SiteplanService::getMeta()` untuk `has_akses.update_tanggal_pembangunan`.
  - Shared UI Helper (`public/assets/js/scripts.js`): `showFoto()` & `hapusFoto()` dipanggil oleh `produksi.js` (`#modal_divisi7`, mode editable `del = true`) maupun `siteplan-detail-modal.js` (`#modal_detail`, mode read-only `del = "false"`).
  - Modul `Keuangan` / `Cashout`: Mengakses data master subkon dan riwayat pembayaran fisik kavling untuk verifikasi SPP serta pencairan kasbon termin subkon.
  - Modul `MKDT` / `Sales`: Mengakses endpoint `/api/produksi/sales/*` untuk berita acara serah terima fisik unit (BAST), pengisian checklist kelayakan bangunan, dan tiket komplain paska-huni.
  - Modul `Direksi`: Mengakses view `app/Views/direksi/list-tagihan-pending.php` dan endpoint produksi untuk pengawasan tagihan konstruksi berjalan.

### Audit Verifikasi Migrasi (2026-11-22)
- **Status Audit:** `COMPLETED (Catatan Minor)` (Actual: `REFACTORED`, Score `100/100`).
- **Struktur & Rute:** Rute eksplisit sudah modular (`App\Controllers\Api\Produksi\*` & `App\Controllers\Web\Produksi\*`) dengan 0 rute legacy/AutoRoute; 8 `thin_shim` + 3 `medium_shim` (`ChecklistGroup.php`, `ChecklistItem.php`, `ChecklistSubItem.php`) terverifikasi murni delegator Web/API tanpa logic/DB; `ProduksiService` hanya memakai `\Config\Database::connect()` untuk transaksi DB (`transStart`/`transBegin`/`transCommit`/`transRollback`/`transComplete`); dan API checklist/subkon sudah mendukung `?format=raw` / `X-Client-Platform`.
- **Temuan Minor:**
  1. `app/Controllers/Web/Produksi/ProduksiController.php:48` menginstansiasi `new \App\Models\HakaksesModel()` langsung di `produksiMobile()` serta memanggil `ProduksiRepository` langsung di `getSLFPDF()` (`L72,L81`).
  2. `app/Controllers/Web/Produksi/ChecklistItemController.php:19` dan `app/Controllers/Web/Produksi/ChecklistSubItemController.php:19` memanggil `ChecklistRepository` langsung alih-alih melalui `ChecklistService`.
  3. `app/Controllers/Api/Produksi/ProduksiController.php` memanggil `ProduksiRepository` langsung pada beberapa endpoint (`L51,L112,L222,L259,L271,L290,L308`) dan `getSLFPDF()` (`L276-279`) merender `view('pdf/slf', ...)` / `view('pdf/slf_page3', ...)` + `exit()`.
  4. `app/Services/Produksi/ProduksiFileService.php:36` memanggil `\Config\Database::connect()->getFieldNames('file_produksi')` langsung di `__construct()`.

## Change Log
- `2026-11-23` [`BUGFIX-CORS-SLF-PDF`] — Pembaruan `Api\Produksi\ProduksiController::getSLFPDF()` untuk mengembalikan PDF melalui `$this->mpdf->renderBinary()` + `ResponseInterface` tanpa `exit()`, sehingga filter `Cors::after` tetap berjalan untuk request cross-origin blob XHR (`https://app.sigapp.site` $\leftrightarrow$ `https://sigapp.site`) dengan tetap menjaga Zero-Downtime terhadap `Web\Produksi\ProduksiController::getSLFPDF()`, disinkronkan via `php spark module:sync Produksi`.
- **`2026-11-23` [`FEATURE`] — Hapus Restriction Tanggal Pembangunan & Perbaikan Tombol Hapus Foto Produksi (`#modal_divisi7`):**
  - **Routes & Public Contracts:**
    - `POST /api/produksi/hapus_foto` (`App\Controllers\Api\Produksi\ProduksiController::hapus_foto`): Diperketat dengan guard login (`401`), guard role Admin (`1`) / Produksi (`7`) (`403`), validasi integer positif `id` (`400`), dan respons JSON standar `{ success, messages, data: { id, id_kavling }, token }`.
    - `GET|POST /api/produksi/meta` (`ProduksiController::meta`): Menyelaraskan `permissions.update_tanggal_pembangunan = true` untuk role `1`, `7`, `8` pada alur desktop/Siteplan (halaman `produksi_mobile.php` tidak diubah).
  - **Controller / Service / Repo / Model:**
    - `app/Controllers/Api/Produksi/ProduksiController.php`: Menambahkan helper `resolveUserRoleIds(int $userId)` (private), memperketat `hapus_foto()`, dan menyelaraskan `meta()`.
    - `app/Services/Produksi/ProduksiService.php`: Memperbarui `deleteFoto(int $id, int $userId)` (validasi `$id > 0`, transaksi DB `transStart()`/`transComplete()` untuk `deleteFileById()` + `insertProduksiChangeHistory('delete_file')`, return `data: { id, id_kavling }`).
    - `app/Services/Produksi/ProduksiFileService.php`: Memperbarui `moveFotoToTrash(object $file)` untuk memindahkan file fisik dan thumbnail (`thumbnails/`) ke `uploads/produksi/trash/YYYYMMDD/` serta tetap mengembalikan `true` jika file fisik sudah hilang di disk agar pembersihan record DB tetap berjalan.
  - **View / CSS / JS:**
    - `app/Views/siteplan/produksi.php`: Memperbaiki typo ID `labe_listrik_disediakan_foto` -> `label_listrik_disediakan_foto`.
    - `public/assets/js/siteplan/produksi.js`: `open_fproduksi()` menghapus restriction disable `.tgl_bangun` berbasis `has_akses.update_tanggal_pembangunan` maupun kondisi `!r.tanggal_pembangunan` (termasuk Flatpickr `altInput`), serta menambahkan `'listrik_disediakan_foto'` ke reset `categories`.
    - `public/assets/js/scripts.js`: `showFoto()` menambahkan guard ketat `allowDelete = del === true || del === "true"` dan merender tombol `.btn-hapus-foto` (`type="button"`) di `.detail-file-action` sejajar Lihat & Download; `hapusFoto(id)` mengarah ke `/api/produksi/hapus_foto`, memperbarui `csrfHash`, menghapus `#foto_produksi_<id>`, dan me-refresh `loadProduksiHistory()`.
    - `public/assets/css/produksi.css`: Menambahkan styling ter-scope `#modal_divisi7` untuk `.detail-file-body`, `.detail-file-title`, `.detail-file-meta`, `.detail-file-action`, `.btn-hapus-foto`, dan `.dark-layout`.
  - **Deprecations:** Tidak ada.
- `2026-11-22` [`AUDIT`] — Audit verifikasi migrasi modular: status `COMPLETED (Catatan Minor)` (Score 100/100); tercatat temuan minor layer di `Web\Produksi\ProduksiController` (instansiasi `HakaksesModel` & akses langsung `ProduksiRepository`), `Web\Produksi\ChecklistItemController` & `ChecklistSubItemController` (akses langsung `ChecklistRepository`), `Api\Produksi\ProduksiController` (akses langsung repo & render view PDF), serta `ProduksiFileService` (`getFieldNames` via `Database::connect()`).
- **`2026-11-12` [`REFACTOR`] — Refactoring Lengkap Modul Produksi (Thin Controller, Modular Web & API Architecture, Encapsulated Transactions, Clean Numeric, Scoped CSS & External Asset Separation):**
  - **Routes & Public Contracts:**
    - Mengarahkan seluruh rute grup `/api/produksi/*` ke namespace controller modular `App\Controllers\Api\Produksi`.
    - Mendaftarkan endpoint eksplisit baru `GET|POST /api/produksi/meta` dengan keluaran full JSON murni (`RAW_JSON_READY`).
    - Menetapkan rute eksplisit untuk grup subkon (`/subkon/list/ambil`, `/subkon/list`, `/api/subkon/list`), grup sales serah terima (`/api/produksi/sales/*` dan fallback legacy `/sales/*`), serta rute master checklist (`/checklist-group`, `/checklist-item`, `/checklist-sub-item` dan API `/api/checklist-*/*`).
    - Memetakan rute antarmuka mobile `GET /produksi/mobile` ke `Web\Produksi\ProduksiController::produksiMobile`.
    - Menyediakan kontrak service publik pada `ProduksiService`, `ProduksiFileService`, `PhotoGpsMetadataService`, dan `ChecklistService`.
  - **Controller / Service / Repo / Model:**
    - Memisahkan controller menjadi 4 Web Controllers (`ProduksiController`, `ChecklistGroupController`, `ChecklistItemController`, `ChecklistSubItemController`) di `app/Controllers/Web/Produksi/` dan 6 API Controllers (`ProduksiController`, `SubkonController`, `SalesController`, `ChecklistGroupController`, `ChecklistItemController`, `ChecklistSubItemController`) di `app/Controllers/Api/Produksi/` dengan 0 Layer Violations.
    - Mengenkapsulasi seluruh logika bisnis, kalkulasi finansial (`cleanNumeric()`), sanitasi input, dan transaksi multi-tabel (`transStart()`/`transComplete()`) ke dalam `App\Services\Produksi\ProduksiService`.
    - Mengorganisir service pendukung ke namespace modular: `ProduksiFileService`, `PhotoGpsMetadataService`, dan `ChecklistService`.
    - Mengabstraksi seluruh akses database ke `App\Repositories\Produksi\ProduksiRepository` dan `App\Repositories\Produksi\ChecklistRepository`.
    - Mempertahankan 11 file lama sebagai Zero-Downtime Shims untuk menjaga kompatibilitas mundur.
    - Membersihkan method mati `ProduksiRepository::upsertBayarItems()` (0 pemanggil lintas codebase).
  - **View / CSS / JS:**
    - Membuat stylesheet eksternal `public/assets/css/produksi.css` (25.1 KB, 3-Tier CSS ber-scope `#modal-produksi` dan modal terkait, warna brand `#2057a3`, tabs 1-baris scrollable horizontal untuk mobile, badge status, support dark layout).
    - Memperbarui modal `app/Views/siteplan/produksi.php` dengan mengekstrak 827 baris CSS inline ke file eksternal `produksi.css` menggunakan cache-busting otomatis `filemtime`.
    - Memverifikasi fungsionalitas JavaScript pada `public/assets/js/siteplan/produksi.js`, `public/assets/js/siteplan/produksi_mobile.js`, dan `public/assets/js/siteplan/direksi.js`.
  - **Deprecations:**
    - Mendepresiasi 11 class di root namespace dan menjadikannya Zero-Downtime Shim.
    - Mendepresiasi rute flat tanpa namespace `/sales/*` dan `/subkon/list/ambil` ke endpoint modular.

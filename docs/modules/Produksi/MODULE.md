# Module: Produksi

**Status:** `MAPPED`  
**Readiness Score:** `60/100`  
**Last Synced:** `2026-10-23 13:52:12` @ `ad0c900`  

## Purpose
Manajemen konstruksi lapangan, tahapan serah terima fisik (ST 0-100%), komplain, dan pengawasan subkon.

## Out of scope
- Hal-hal di luar tanggung jawab domain Produksi.

## Invariants
- Modul lain tidak boleh memanipulasi tabel pemilik secara langsung.
- Perubahan finansial/status harus diverifikasi di level Service.

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
| `POST` | `/subkon/list/ambil` | `Explicit` | `Subkon::getList` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `ANY` | `/produksi/get_data_by_id` | `AutoRoute` | `Produksi::get_data_by_id` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/produksi/getbayarproduksilistitem` | `AutoRoute` | `Produksi::getBayarProduksiListItem` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/produksi/getbayarproduksi` | `AutoRoute` | `Produksi::getBayarProduksi` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/produksi/savebayarproduksi` | `AutoRoute` | `Produksi::saveBayarProduksi` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/produksi/deletebayarproduksi` | `AutoRoute` | `Produksi::deleteBayarProduksi` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/produksi/get_data_komplain_by_id` | `AutoRoute` | `Produksi::get_data_komplain_by_id` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/produksi/save_komplain_produksi` | `AutoRoute` | `Produksi::save_komplain_produksi` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/produksi/save` | `AutoRoute` | `Produksi::save` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/produksi/saveslf` | `AutoRoute` | `Produksi::saveSLf` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/produksi/getslf` | `AutoRoute` | `Produksi::getSlf` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/produksi/hapusslf` | `AutoRoute` | `Produksi::hapusSLF` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/produksi/getslfpdf` | `AutoRoute` | `Produksi::getSLFPDF` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/produksi/getkavling` | `AutoRoute` | `Produksi::getKavling` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/produksi/hapus_foto` | `AutoRoute` | `Produksi::hapus_foto` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/produksi/get_gambarkerja` | `AutoRoute` | `Produksi::get_gambarkerja` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/produksi/edit_others` | `AutoRoute` | `Produksi::edit_others` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/checklist_group/index` | `AutoRoute` | `ChecklistGroup::index` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/checklist_group/getall` | `AutoRoute` | `ChecklistGroup::getAll` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/checklist_group/getone` | `AutoRoute` | `ChecklistGroup::getOne` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/checklist_group/add` | `AutoRoute` | `ChecklistGroup::add` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/checklist_group/edit` | `AutoRoute` | `ChecklistGroup::edit` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/checklist_group/remove` | `AutoRoute` | `ChecklistGroup::remove` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/checklist_item/index` | `AutoRoute` | `ChecklistItem::index` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/checklist_item/getall` | `AutoRoute` | `ChecklistItem::getAll` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/checklist_item/getone` | `AutoRoute` | `ChecklistItem::getOne` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/checklist_item/add` | `AutoRoute` | `ChecklistItem::add` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/checklist_item/edit` | `AutoRoute` | `ChecklistItem::edit` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/checklist_item/remove` | `AutoRoute` | `ChecklistItem::remove` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/checklist_sub_item/index` | `AutoRoute` | `ChecklistSubItem::index` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/checklist_sub_item/getall` | `AutoRoute` | `ChecklistSubItem::getAll` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/checklist_sub_item/getone` | `AutoRoute` | `ChecklistSubItem::getOne` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/checklist_sub_item/add` | `AutoRoute` | `ChecklistSubItem::add` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/checklist_sub_item/edit` | `AutoRoute` | `ChecklistSubItem::edit` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/checklist_sub_item/remove` | `AutoRoute` | `ChecklistSubItem::remove` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/sales/get_data_by_id` | `AutoRoute` | `Sales::get_data_by_id` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/sales/save_checklist` | `AutoRoute` | `Sales::save_checklist` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/sales/get_data_komplain_by_id` | `AutoRoute` | `Sales::get_data_komplain_by_id` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/sales/batalkan_komplain` | `AutoRoute` | `Sales::batalkan_komplain` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/sales/save_komplain_sales` | `AutoRoute` | `Sales::save_komplain_sales` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/sales/get_data_serah_terima_by_id` | `AutoRoute` | `Sales::get_data_serah_terima_by_id` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/sales/save_serah_terima` | `AutoRoute` | `Sales::save_serah_terima` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |

### B. Public PHP Services (Backend Contract Antar-Modul)
#### `ProduksiService.php`
- `save(array $var, array $uploadedFiles, int $userId)`
#### `ProduksiFileService.php`
- `uploadFotoGroups(array $requestFiles, array $requestVars, int $idKavling)`
- `moveFotoToTrash(object $file)`
#### `PhotoGpsMetadataService.php`
- `prepareDownload(array $file)`
- `cleanup(?string $temporaryPath)`

## File Inventory
### Controllers (7)
- `app/Controllers/Produksi.php` (493 baris, 16 public methods)
- `app/Controllers/Api/ProduksiController.php` (789 baris, 19 public methods)
- `app/Controllers/Subkon.php` (31 baris, 1 public methods)
- `app/Controllers/ChecklistGroup.php` (193 baris, 6 public methods)
- `app/Controllers/ChecklistItem.php` (220 baris, 6 public methods)
- `app/Controllers/ChecklistSubItem.php` (259 baris, 6 public methods)
- `app/Controllers/Sales.php` (388 baris, 7 public methods)

### Services (3)
- `app/Services/ProduksiService.php` (264 baris)
- `app/Services/ProduksiFileService.php` (186 baris)
- `app/Services/PhotoGpsMetadataService.php` (191 baris)

### Repositories (1)
- `app/Repositories/ProduksiRepository.php` (379 baris)

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
- `app/Views/checklist` (3 file)
- `app/Views/direksi` (1 file)

### JavaScript (3 file)
- `public/assets/js/siteplan/produksi.js` (1900 baris)
- `public/assets/js/siteplan/produksi_mobile.js` (354 baris)
- `public/assets/js/siteplan/direksi.js` (131 baris)

## Readiness Checklist
- [ ] Has Service Layer
- [ ] Has Repository Layer
- [ ] Web & API Controllers Separated
- [ ] Explicit Routes Defined
- [ ] Zero Controller Layer Violations
- [x] MODULE.md Initialized

> **Layer Violations Detected:**
> - `app/Controllers/Produksi.php`: Database::connect(), new GambarkerjaModel(), new KavlingModel(), new ChecklistWorkModel(), new KomplainModel(), new ProduksiModel()
> - `app/Controllers/Api/ProduksiController.php`: Database::connect(), new GambarkerjaModel(), new KavlingModel(), new ChecklistWorkModel(), new KomplainModel()
> - `app/Controllers/Subkon.php`: db_connect(), new SubkonModel()
> - `app/Controllers/ChecklistGroup.php`: new ChecklistGroupModel()
> - `app/Controllers/ChecklistItem.php`: new ChecklistItemModel(), new ChecklistGroupModel()
> - `app/Controllers/ChecklistSubItem.php`: new ChecklistSubItemModel(), new ChecklistItemModel(), new ChecklistGroupModel()
> - `app/Controllers/Sales.php`: new ProduksiModel(), new GambarkerjaModel(), new KavlingModel(), new ChecklistSubItemModel(), new ChecklistWorkModel(), new SerahTerimaModel(), new KomplainModel()

## Dead Code Candidates
_Jalankan `php spark module:deadcode Produksi` untuk mengaudit dead code kandidat._

## Notes
- Modul diinisialisasi melalui tooling otomatis.

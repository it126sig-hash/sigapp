# Module: Produksi

**Status:** `REFACTORED`  
**Readiness Score:** `100/100`  
**Last Synced:** `2026-10-12 14:38:40` @ `be98758`  

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
| `GET` | `siteplan/produksi-mobile` | `Explicit` | `Siteplan::produksi_mobile` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `get_data_by_id` | `Explicit` | `ProduksiController::get_data_by_id` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `getBayarProduksi` | `Explicit` | `ProduksiController::getBayarProduksi` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `getBayarProduksiListItem` | `Explicit` | `ProduksiController::getBayarProduksiListItem` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `saveBayarProduksi` | `Explicit` | `ProduksiController::saveBayarProduksi` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `deleteBayarProduksi` | `Explicit` | `ProduksiController::deleteBayarProduksi` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `get_data_komplain_by_id` | `Explicit` | `ProduksiController::get_data_komplain_by_id` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `save_komplain_produksi` | `Explicit` | `ProduksiController::save_komplain_produksi` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `save` | `Explicit` | `ProduksiController::save` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `upload-mobile` | `Explicit` | `ProduksiController::uploadMobile` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `history` | `Explicit` | `ProduksiController::history` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `saveSLF` | `Explicit` | `ProduksiController::saveSLf` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `getSlf` | `Explicit` | `ProduksiController::getSlf` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `hapusSLF` | `Explicit` | `ProduksiController::hapusSLF` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `getSLFPDF/(:num)` | `Explicit` | `ProduksiController::getSLFPDF/$1` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `getKavling` | `Explicit` | `ProduksiController::getKavling` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `hapus_foto` | `Explicit` | `ProduksiController::hapus_foto` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `get_gambarkerja` | `Explicit` | `ProduksiController::get_gambarkerja` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `add_jalan` | `Explicit` | `ProduksiController::add_jalan` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `edit_others` | `Explicit` | `ProduksiController::edit_others` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `get_data_by_id` | `Explicit` | `ProduksiController::get_data_by_id` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `getBayarProduksi` | `Explicit` | `ProduksiController::getBayarProduksi` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `getBayarProduksiListItem` | `Explicit` | `ProduksiController::getBayarProduksiListItem` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `saveBayarProduksi` | `Explicit` | `ProduksiController::saveBayarProduksi` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `deleteBayarProduksi` | `Explicit` | `ProduksiController::deleteBayarProduksi` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `get_data_komplain_by_id` | `Explicit` | `ProduksiController::get_data_komplain_by_id` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `save_komplain_produksi` | `Explicit` | `ProduksiController::save_komplain_produksi` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `save` | `Explicit` | `ProduksiController::save` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `upload-mobile` | `Explicit` | `ProduksiController::uploadMobile` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `history` | `Explicit` | `ProduksiController::history` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `saveSLF` | `Explicit` | `ProduksiController::saveSLf` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `getSlf` | `Explicit` | `ProduksiController::getSlf` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `hapusSLF` | `Explicit` | `ProduksiController::hapusSLF` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `getSLFPDF/(:num)` | `Explicit` | `ProduksiController::getSLFPDF/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `getKavling` | `Explicit` | `ProduksiController::getKavling` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `hapus_foto` | `Explicit` | `ProduksiController::hapus_foto` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `get_gambarkerja` | `Explicit` | `ProduksiController::get_gambarkerja` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `add_jalan` | `Explicit` | `ProduksiController::add_jalan` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `edit_others` | `Explicit` | `ProduksiController::edit_others` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `list/ambil` | `Explicit` | `Subkon::getList` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `subkon` | `Explicit` | `CashoutSubkon::index` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `subkon/list` | `Explicit` | `CashoutSubkon::getDataTables` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `subkon/detail-list` | `Explicit` | `CashoutSubkon::getDetailList` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `subkon/ambil` | `Explicit` | `CashoutSubkon::get` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `subkon/save` | `Explicit` | `CashoutSubkon::save` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `subkon/turun-jatuh-tempo` | `Explicit` | `CashoutSubkon::turunJatuhTempo` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `subkon/ajukan-spp` | `Explicit` | `CashoutSubkon::ajukanSPP` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `subkon/history` | `Explicit` | `CashoutSubkon::getHistory` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `subkon/ajukan-pencairan` | `Explicit` | `CashoutSubkon::ajukanPencairan` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `subkon/pembayaran` | `Explicit` | `CashoutSubkon::pembayaran` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `toggle/(:num)` | `Explicit` | `CashoutSubkon::toggleStatus/$1` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `download/(:num)` | `Explicit` | `CashoutSubkon::download/$1` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `ANY` | `/produksi_controller/storejalanprogressphotos` | `AutoRoute` | `ProduksiController::storeJalanProgressPhotos` | 🟢 MIGRATED | Rute method aktif |
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

### B. Public PHP Services (Backend Contract Antar-Modul)
#### `ProduksiService.php`
- `save(array $var, array $uploadedFiles, int $userId)`
- `buildProduksiData(array $var, int $userId)`
- `processChecklists(int $idKavling, array $var, int $userId)`
- `buildHistoryPayload(array $oldProduksi, array $newProduksi, array $var, array $fotoRows)`
- `shouldWriteHistory(array $payload)`
#### `ProduksiFileService.php`
- `uploadFotoGroups(array $requestFiles, array $requestVars, int $idKavling)`
- `compressImageIfPossible(string $relativePath)`
- `hasFileProduksiField(string $field)`
- `nullableDecimal($value)`
- `moveFotoToTrash(object $file)`
#### `PhotoGpsMetadataService.php`
- `prepareDownload(array $file)`
- `cleanup(?string $temporaryPath)`
- `coordinates(mixed $record)`
- `convertToJpeg(string $sourcePath, string $destinationPath)`
- `embedGps(string $jpegPath, float $latitude, float $longitude)`
- `degreesMinutesSeconds(float $coordinate)`
- `jpegFileName(string $fileName)`

## File Inventory
### Controllers (6)
- `app/Controllers/Produksi.php` (493 baris, 16 public methods)
- `app/Controllers/Api/ProduksiController.php` (789 baris, 20 public methods)
- `app/Controllers/Subkon.php` (31 baris, 1 public methods)
- `app/Controllers/ChecklistGroup.php` (193 baris, 6 public methods)
- `app/Controllers/ChecklistItem.php` (220 baris, 6 public methods)
- `app/Controllers/ChecklistSubItem.php` (259 baris, 6 public methods)

### Services (3)
- `app/Services/ProduksiService.php` (264 baris)
- `app/Services/ProduksiFileService.php` (186 baris)
- `app/Services/PhotoGpsMetadataService.php` (178 baris)

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
- [x] Has Service Layer
- [x] Has Repository Layer
- [x] Web & API Controllers Separated
- [x] Explicit Routes Defined
- [x] MODULE.md Initialized

## Dead Code Candidates
_Jalankan `php spark module:deadcode Produksi` untuk mengaudit dead code kandidat._

## Notes
- Modul diinisialisasi melalui tooling otomatis.

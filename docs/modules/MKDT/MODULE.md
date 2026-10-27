# Module: MKDT

**Status:** `MAPPED`  
**Readiness Score:** `60/100`  
**Last Synced:** `2026-10-23 13:52:12` @ `ad0c900`  

## Purpose
Manajemen Konsumen dan Transaksi: siklus pemesanan, verifikasi data konsumen, dan posisi kavling.

## Out of scope
- Hal-hal di luar tanggung jawab domain MKDT.

## Invariants
- Modul lain tidak boleh memanipulasi tabel pemilik secara langsung.
- Perubahan finansial/status harus diverifikasi di level Service.

## Database Tables
### Owner Table(s)
- `mkdt` *(akses tulis eksklusif hanya untuk modul MKDT)*
- `konsumen` *(akses tulis eksklusif hanya untuk modul MKDT)*
- `mkdt_kavling_history` *(akses tulis eksklusif hanya untuk modul MKDT)*
- `promo` *(akses tulis eksklusif hanya untuk modul MKDT)*

### Shared Tables (Dibaca dari modul lain)
- `kavling`
- `keuangan`

## Contracts
### A. Public HTTP Endpoints & Routes Inventory
| Method | URI / Pattern | Tipe | Handler | Status Migrasi | Target API / Catatan |
|---|---|---|---|---|---|
| `GET` | `/list-batal` | `Explicit` | `Mkdt::list_batal` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `/list-stock` | `Explicit` | `Mkdt::list_stock` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `/list-kavling` | `Explicit` | `PosisiKonsumen::index` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/list-kavling/ambil` | `Explicit` | `PosisiKonsumen::getDataTables` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `/list-kavling/(:segment)` | `Explicit` | `PosisiKonsumen::index/$1` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/list-kavling/akad/ambil` | `Explicit` | `PosisiKonsumen::getDataTablesAkad` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/list-kavling/batal/ambil` | `Explicit` | `PosisiKonsumen::getDataTablesBatal` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/export/poskon/(:segment)/(:segment)` | `Explicit` | `PosisiKonsumen::exportPoskon/$1/$2` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/riwayat/poskon/(:segment)` | `Explicit` | `PosisiKonsumen::getRiwayatExport/$1` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/api/nup/list` | `Explicit` | `NupController::list` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/nup/detail` | `Explicit` | `NupController::detail` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/nup/save` | `Explicit` | `NupController::save` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/nup/pull` | `Explicit` | `NupController::pull` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/nup/kavling-options` | `Explicit` | `NupController::kavlingOptions` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/nup/kavling-history` | `Explicit` | `NupController::kavlingHistory` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `ANY` | `/mkdt/get_data_by_id` | `AutoRoute` | `Mkdt::get_data_by_id` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/mkdt/simpan_batal` | `AutoRoute` | `Mkdt::simpan_batal` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/mkdt/batal_mkdt` | `AutoRoute` | `Mkdt::batal_mkdt` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/mkdt/save` | `AutoRoute` | `Mkdt::save` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/mkdt/set_harga` | `AutoRoute` | `Mkdt::set_harga` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/mkdt/getliststock` | `AutoRoute` | `Mkdt::getListStock` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/mkdt/if_where` | `AutoRoute` | `Mkdt::if_where` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/mkdt/format_tgl` | `AutoRoute` | `Mkdt::format_tgl` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/mkdt/is_active` | `AutoRoute` | `Mkdt::is_active` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/mkdt/getsi` | `AutoRoute` | `Mkdt::getsi` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/mkdt/savesi` | `AutoRoute` | `Mkdt::saveSI` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/mkdt/export_xlsx` | `AutoRoute` | `Mkdt::export_xlsx` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/posisi_konsumen/format_tgl` | `AutoRoute` | `PosisiKonsumen::format_tgl` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/posisi_konsumen/is_active` | `AutoRoute` | `PosisiKonsumen::is_active` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/posisi_konsumen/exportposkonpdf` | `AutoRoute` | `PosisiKonsumen::exportPoskonPdf` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/posisi_konsumen/exportposkonxlsx` | `AutoRoute` | `PosisiKonsumen::exportPoskonXlsx` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/promo/index` | `AutoRoute` | `Promo::index` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/promo/getdatatable` | `AutoRoute` | `Promo::getDataTable` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/promo/getall` | `AutoRoute` | `Promo::getAll` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/promo/getone` | `AutoRoute` | `Promo::getOne` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/promo/add` | `AutoRoute` | `Promo::add` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/promo/edit` | `AutoRoute` | `Promo::edit` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/promo/remove` | `AutoRoute` | `Promo::remove` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/promo/if_where` | `AutoRoute` | `Promo::if_where` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/promo/is_active` | `AutoRoute` | `Promo::is_active` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/promo/format_tgl` | `AutoRoute` | `Promo::format_tgl` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |

### B. Public PHP Services (Backend Contract Antar-Modul)
#### `MkdtHistoryService.php`
- `actionLabel(string $action)`
- `log(int $idKavling, ?int $idMkdt, string $action, string $summary, ?array $oldData = null, ?array $newData = null, ?int $actorId = null, array $metadata = [])`
- `getConsumerReplacementSnapshots(int $idMkdt)`
- `getConsumerReplacementHistory(int $idMkdt, int $idKavling)`
- `getHistory(int $idKavling, int $limit = 10, int $offset = 0)`
- `buildSetHargaSummary(?array $old, array $new, ?string $pricelistLabel = null)`
- `buildKonsumenSummary(?object $oldMkdt, array $kons, array $mk, bool $isNew)`
- `buildStatusSummary(?object $old, array $new, array $perintahBangun = [])`
- `buildTurunPembangunanSummary(?array $old, array $new)`
- `buildStandingInstructionSummary(array $items)`
- `buildBatalSummary(array $data)`
#### `MkdtSettlementService.php`
- `synchronize(int $idMkdt)`
#### `MkdtFinancialBreakdownService.php`
- `targets(array|object $mkdt)`
- `validTurunKpr(array|object $mkdt)`
- `progress(float $target, float $paid)`
- `hasFinancialChanges(?object $old, array $new)`
- `validateContractAndSchedule(array $mkdt, array $schedules)`
- `validatePaymentAllocation(int $idMkdt, array $allocations, float $headerNominal)`
#### `MkdtFinancialReconciliationService.php`
- `audit(?int $idMkdt = null)`
- `apply(array $manifest, ?int $actorId = null)`
#### `PosisiKonsumenService.php`
- `getDataTable($request, $status = null)`
- `getDataTablesBatal($request)`
- `getRiwayatExport($id_proyek, $status)`
- `insertRiwayatExport($data)`
- `format_tgl($tgl)`
- `is_active($id, $texts, $textf)`
#### `KonsumenService.php`
- `upsert(?int $idKonsumen, array $data)`
- `getKonsumenTransaksi($id_mkdt, $select = [])`
- `getByIDKavling($id_kavling)`
#### `NupService.php`
- `getListByProyek(int $idProyek, ?string $search = null)`
- `getDetail(int $idMkdt)`
- `saveNup(array $post, array $files, int $actorId)`
- `pullToNup(array $post, ?UploadedFile $fileLampiran, int $actorId)`
- `getKavlingOptions(int $idProyek, ?string $search = null, ?int $currentIdKavling = null, ?int $lastIdKavling = null, ?string $filterHargaJual = null)`
- `getFormattedKavlingHistory(int $idMkdt = 0, int $idKavling = 0)`

## File Inventory
### Controllers (4)
- `app/Controllers/Mkdt.php` (1969 baris, 13 public methods)
- `app/Controllers/PosisiKonsumen.php` (304 baris, 10 public methods)
- `app/Controllers/Api/NupController.php` (194 baris, 6 public methods)
- `app/Controllers/Promo.php` (351 baris, 10 public methods)

### Services (7)
- `app/Services/MkdtHistoryService.php` (313 baris)
- `app/Services/MkdtSettlementService.php` (48 baris)
- `app/Services/MkdtFinancialBreakdownService.php` (254 baris)
- `app/Services/MkdtFinancialReconciliationService.php` (275 baris)
- `app/Services/PosisiKonsumenService.php` (426 baris)
- `app/Services/KonsumenService.php` (49 baris)
- `app/Services/NupService.php` (532 baris)

### Repositories (4)
- `app/Repositories/MkdtHistoryRepository.php` (55 baris)
- `app/Repositories/PosisiKonsumenRepository.php` (197 baris)
- `app/Repositories/KonsumenRepository.php` (60 baris)
- `app/Repositories/NupRepository.php` (412 baris)

### Models (3)
- `app/Models/MkdtModel.php`
- `app/Models/KonsumenModel.php`
- `app/Models/MkdtKavlingHistoryModel.php`

### Views (2 folder)
- `app/Views/kavling` (4 file)
- `app/Views/promo` (1 file)

### JavaScript (1 file)
- `public/assets/js/siteplan/mkdt.js` (3267 baris)

## Readiness Checklist
- [ ] Has Service Layer
- [ ] Has Repository Layer
- [ ] Web & API Controllers Separated
- [ ] Explicit Routes Defined
- [ ] Zero Controller Layer Violations
- [x] MODULE.md Initialized

> **Layer Violations Detected:**
> - `app/Controllers/Mkdt.php`: db_connect(), $this->db->table(), new KeuanganModel(), new MkdtModel(), new KavlingModel(), new KonsumenModel(), new LogPembayaranModel()
> - `app/Controllers/PosisiKonsumen.php`: db_connect(), $this->db->table(), new KeuanganModel(), new MkdtModel(), new KavlingModel(), new KonsumenModel(), new LogPembayaranModel(), new ProyekModel(), new ChecklistSubItemModel()
> - `app/Controllers/Promo.php`: db_connect(), $this->db->table(), new ClusterModel(), new ProyekModel()

## Dead Code Candidates
_Jalankan `php spark module:deadcode MKDT` untuk mengaudit dead code kandidat._

## Notes
- Modul diinisialisasi melalui tooling otomatis.

# Module: MKDT

**Status:** `REFACTORED`  
**Readiness Score:** `80/100`  
**Last Synced:** `2026-10-13 15:48:18` @ `3a8b1f5`  

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

### Shared Tables (Dibaca dari modul lain)
- `kavling`
- `keuangan`

## Contracts
### A. Public HTTP Endpoints & Routes Inventory
| Method | URI / Pattern | Tipe | Handler | Status Migrasi | Target API / Catatan |
|---|---|---|---|---|---|
| `GET` | `/list-batal` | `Explicit` | `Mkdt::list_batal` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `/list-stock` | `Explicit` | `Mkdt::list_stock` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `history` | `Explicit` | `MkdtController::history` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `consumer-replacement-history` | `Explicit` | `MkdtController::consumerReplacementHistory` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `/list-kavling` | `Explicit` | `PosisiKonsumen::index` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/list-kavling/ambil` | `Explicit` | `PosisiKonsumen::getDataTables` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `/list-kavling/(:segment)` | `Explicit` | `PosisiKonsumen::index/$1` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/list-kavling/akad/ambil` | `Explicit` | `PosisiKonsumen::getDataTablesAkad` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/list-kavling/batal/ambil` | `Explicit` | `PosisiKonsumen::getDataTablesBatal` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `export/poskon/(:segment)/(:segment)` | `Explicit` | `PosisiKonsumen::exportPoskon/$1/$2` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `riwayat/poskon/(:segment)` | `Explicit` | `PosisiKonsumen::getRiwayatExport/$1` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `ANY` | `/mkdt/get_data_by_id` | `AutoRoute` | `Mkdt::get_data_by_id` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/mkdt/simpan_batal` | `AutoRoute` | `Mkdt::simpan_batal` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/mkdt/batal_mkdt` | `AutoRoute` | `Mkdt::batal_mkdt` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/mkdt/inst` | `AutoRoute` | `Mkdt::inst` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/mkdt/save` | `AutoRoute` | `Mkdt::save` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/mkdt/set_harga` | `AutoRoute` | `Mkdt::set_harga` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/mkdt/get_kavling` | `AutoRoute` | `Mkdt::get_kavling` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/mkdt/get_kavling_konsumen` | `AutoRoute` | `Mkdt::get_kavling_konsumen` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/mkdt/list_kavling_akad` | `AutoRoute` | `Mkdt::list_kavling_akad` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/mkdt/getlistkavlingakad` | `AutoRoute` | `Mkdt::getListKavlingAkad` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/mkdt/list_kavling` | `AutoRoute` | `Mkdt::list_kavling` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/mkdt/getlistkavling` | `AutoRoute` | `Mkdt::getListKavling` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/mkdt/getlistbatal` | `AutoRoute` | `Mkdt::getListBatal` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/mkdt/getliststock` | `AutoRoute` | `Mkdt::getListStock` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/mkdt/if_where` | `AutoRoute` | `Mkdt::if_where` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/mkdt/num` | `AutoRoute` | `Mkdt::num` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/mkdt/format_tgl` | `AutoRoute` | `Mkdt::format_tgl` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/mkdt/is_active` | `AutoRoute` | `Mkdt::is_active` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/mkdt/getsi` | `AutoRoute` | `Mkdt::getsi` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/mkdt/savesi` | `AutoRoute` | `Mkdt::saveSI` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/mkdt/export_xlsx` | `AutoRoute` | `Mkdt::export_xlsx` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/posisi_konsumen/getconfigshapemap` | `AutoRoute` | `PosisiKonsumen::getConfigShapeMap` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/posisi_konsumen/userhasprojectaccess` | `AutoRoute` | `PosisiKonsumen::userHasProjectAccess` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/posisi_konsumen/num` | `AutoRoute` | `PosisiKonsumen::num` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/posisi_konsumen/format_tgl` | `AutoRoute` | `PosisiKonsumen::format_tgl` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/posisi_konsumen/is_active` | `AutoRoute` | `PosisiKonsumen::is_active` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/posisi_konsumen/exportposkonpdf` | `AutoRoute` | `PosisiKonsumen::exportPoskonPdf` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/posisi_konsumen/exportposkonxlsx` | `AutoRoute` | `PosisiKonsumen::exportPoskonXlsx` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |

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
- `normalized(mixed $value)`
- `displayValue(mixed $value)`
#### `MkdtSettlementService.php`
- `synchronize(int $idMkdt)`
#### `MkdtFinancialBreakdownService.php`
- `targets(array|object $mkdt)`
- `validTurunKpr(array|object $mkdt)`
- `progress(float $target, float $paid)`
- `hasFinancialChanges(?object $old, array $new)`
- `validateContractAndSchedule(array $mkdt, array $schedules)`
- `validatePaymentAllocation(int $idMkdt, array $allocations, float $headerNominal)`
- `paidByEffectiveItem(int $idMkdt, array $masters)`
- `effectiveTargetKey(array $master)`
- `number(array $row, string $field)`
- `requireDb()`
#### `MkdtFinancialReconciliationService.php`
- `audit(?int $idMkdt = null)`
- `apply(array $manifest, ?int $actorId = null)`
- `paymentIntegrity(int $idMkdt)`
- `paidComponents(int $idMkdt)`
- `canonicalItem(string $item)`
- `alreadyApplied(array $current, array $changes)`
#### `PosisiKonsumenService.php`
- `resolveCurrentRoleId()`
- `getActionMenuItems()`
- `buildPoskonRowPayload(object $row)`
- `renderPoskonActionHtml(object $row)`
- `renderReferralQrHtml(object $row)`
- `renderPercentageLabel(string $label, ?int $percentage, bool $colorize = false)`
- `renderProgress(float $target, float $paid, bool $colorize = false)`
- `getDataTable($request, $status = null)`
- `getDataTablesBatal($request)`
- `getRiwayatExport($id_proyek, $status)`
- `insertRiwayatExport($data)`
- `num($d)`
- `format_tgl($tgl)`
- `is_active($id, $texts, $textf)`
#### `KonsumenService.php`
- `upsert(?int $idKonsumen, array $data)`
- `getKonsumenTransaksi($id_mkdt, $select = [])`
- `getByIDKavling($id_kavling)`

## File Inventory
### Controllers (2)
- `app/Controllers/Mkdt.php` (1969 baris, 23 public methods)
- `app/Controllers/PosisiKonsumen.php` (304 baris, 13 public methods)

### Services (6)
- `app/Services/MkdtHistoryService.php` (313 baris)
- `app/Services/MkdtSettlementService.php` (48 baris)
- `app/Services/MkdtFinancialBreakdownService.php` (254 baris)
- `app/Services/MkdtFinancialReconciliationService.php` (275 baris)
- `app/Services/PosisiKonsumenService.php` (426 baris)
- `app/Services/KonsumenService.php` (49 baris)

### Repositories (3)
- `app/Repositories/MkdtHistoryRepository.php` (55 baris)
- `app/Repositories/PosisiKonsumenRepository.php` (197 baris)
- `app/Repositories/KonsumenRepository.php` (60 baris)

### Models (2)
- `app/Models/MkdtModel.php`
- `app/Models/KonsumenModel.php`

### Views (1 folder)
- `app/Views/kavling` (4 file)

### JavaScript (1 file)
- `public/assets/js/siteplan/mkdt.js` (3216 baris)

## Readiness Checklist
- [x] Has Service Layer
- [x] Has Repository Layer
- [ ] Web & API Controllers Separated
- [x] Explicit Routes Defined
- [x] MODULE.md Initialized

## Dead Code Candidates
_Jalankan `php spark module:deadcode MKDT` untuk mengaudit dead code kandidat._

## Notes
- Modul diinisialisasi melalui tooling otomatis.

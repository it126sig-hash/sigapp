# Module: PencairanJaminan

**Status:** `REFACTORED`  
**Readiness Score:** `80/100`  
**Last Synced:** `2026-10-12 14:38:40` @ `be98758`  

## Purpose
Pengelolaan dan pelacakan riwayat pencairan dana jaminan / retensi perbankan.

## Out of scope
- Hal-hal di luar tanggung jawab domain PencairanJaminan.

## Invariants
- Modul lain tidak boleh memanipulasi tabel pemilik secara langsung.
- Perubahan finansial/status harus diverifikasi di level Service.

## Database Tables
### Owner Table(s)
- `riwayat_pencairan_jaminan` *(akses tulis eksklusif hanya untuk modul PencairanJaminan)*
- `riwayat_pencairan_jaminan_detail` *(akses tulis eksklusif hanya untuk modul PencairanJaminan)*
- `dana_jaminan_history` *(akses tulis eksklusif hanya untuk modul PencairanJaminan)*

### Shared Tables (Dibaca dari modul lain)
- `keuangan`
- `proyek`

## Contracts
### A. Public HTTP Endpoints & Routes Inventory
| Method | URI / Pattern | Tipe | Handler | Status Migrasi | Target API / Catatan |
|---|---|---|---|---|---|
| `GET` | `/` | `Explicit` | `PencairanJaminan::index` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `list/(:num)` | `Explicit` | `PencairanJaminan::list/$1` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `store` | `Explicit` | `PencairanJaminan::store` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `cairkan/(:num)` | `Explicit` | `PencairanJaminan::cairkan/$1` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `history/(:num)` | `Explicit` | `PencairanJaminan::history/$1` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `toggle/(:num)` | `Explicit` | `PencairanJaminan::toggleStatus/$1` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `download/(:num)` | `Explicit` | `PencairanJaminan::download/$1` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |

### B. Public PHP Services (Backend Contract Antar-Modul)
#### `DanaJaminanService.php`
- `getData(int $idMkdt, int $idKavling)`
- `saveDanaAkad($request, int $actorId)`
- `listPengajuan(int $idKavling)`
- `storePengajuan($request, int $actorId)`
- `cairkanPengajuan(int $idPengajuan, $request, int $actorId)`
- `getHistory(int $idKavling)`
- `rejectLegacyToggle()`
- `getDanaJaminanRows(int $idKavling)`
- `getPengajuanDetailMap(array $ids)`
- `getPengajuanDetail(int $idDetail, int $idPengajuan)`
- `findDanaAkad(string $key, int $idKavling, int $idListDajam)`
- `getDanaAkadById(int $idDanaAkad, int $idKavling)`
- `buildCairPayload(array $row, float $nominal, int $actorId)`
- `getAkadContext(int $idMkdt, int $idKavling, bool $akadOnly = true)`
- `getContextByKavling(int $idKavling)`
- _dan 3 method lainnya..._

## File Inventory
### Controllers (1)
- `app/Controllers/PencairanJaminan.php` (71 baris, 7 public methods)

### Services (1)
- `app/Services/DanaJaminanService.php` (633 baris)

### Repositories (1)
- `app/Repositories/PartialRepository.php` (1 baris)

### Models (3)
- `app/Models/RiwayatPencairanJaminanModel.php`
- `app/Models/RiwayatPencairanJaminanDetailModel.php`
- `app/Models/DanaJaminanHistoryModel.php`

### Views (0 folder)

### JavaScript (0 file)

## Readiness Checklist
- [x] Has Service Layer
- [x] Has Repository Layer
- [ ] Web & API Controllers Separated
- [x] Explicit Routes Defined
- [x] MODULE.md Initialized

## Dead Code Candidates
_Jalankan `php spark module:deadcode PencairanJaminan` untuk mengaudit dead code kandidat._

## Notes
- Modul diinisialisasi melalui tooling otomatis.

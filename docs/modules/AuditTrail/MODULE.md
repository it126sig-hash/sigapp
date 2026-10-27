# Module: AuditTrail

**Status:** `MAPPED`  
**Readiness Score:** `70/100`  
**Last Synced:** `2026-10-23 13:52:13` @ `ad0c900`  

## Purpose
Pencatatan riwayat perubahan data krusial, log transaksi, dan jejak aktivitas user.

## Out of scope
- Hal-hal di luar tanggung jawab domain AuditTrail.

## Invariants
- Modul lain tidak boleh memanipulasi tabel pemilik secara langsung.
- Perubahan finansial/status harus diverifikasi di level Service.

## Database Tables
### Owner Table(s)
- `history_log` *(akses tulis eksklusif hanya untuk modul AuditTrail)*

### Shared Tables (Dibaca dari modul lain)
- `users`

## Contracts
### A. Public HTTP Endpoints & Routes Inventory
| Method | URI / Pattern | Tipe | Handler | Status Migrasi | Target API / Catatan |
|---|---|---|---|---|---|
| `GET` | `/riwayat-perubahan` | `Explicit` | `RiwayatPerubahan::index` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/api/riwayat-perubahan/list` | `Explicit` | `HistoryController::list` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/riwayat-perubahan/kavling` | `Explicit` | `HistoryController::kavling` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |

### B. Public PHP Services (Backend Contract Antar-Modul)
#### `HistoryService.php`
- `hasTable()`
- `log(string $module, array $fields)`
- `getList(array $filters, int $limit, int $offset)`
- `getByKavling(int $idKavling, array $modules, int $limit, int $offset)`
- `getByReferenceAction(string $module, int $referenceId, string $action)`
- `moduleLabels()`
- `formatActionLabel(string $action)`
- `getProyekIdByKavling(int $idKavling)`

## File Inventory
### Controllers (2)
- `app/Controllers/RiwayatPerubahan.php` (28 baris, 1 public methods)
- `app/Controllers/Api/HistoryController.php` (120 baris, 2 public methods)

### Services (1)
- `app/Services/HistoryService.php` (242 baris)

### Repositories (1)
- `app/Repositories/HistoryRepository.php` (164 baris)

### Models (1)
- `app/Models/HistoryLogModel.php`

### Views (1 folder)
- `app/Views/riwayat_perubahan` (1 file)

### JavaScript (0 file)

## Readiness Checklist
- [ ] Has Service Layer
- [ ] Has Repository Layer
- [ ] Web & API Controllers Separated
- [x] Explicit Routes Defined
- [x] Zero Controller Layer Violations
- [x] MODULE.md Initialized

## Dead Code Candidates
_Jalankan `php spark module:deadcode AuditTrail` untuk mengaudit dead code kandidat._

## Notes
- Modul diinisialisasi melalui tooling otomatis.

# Module: TiketMasalah

**Status:** `REFACTORED`  
**Readiness Score:** `100/100`  
**Last Synced:** `2026-10-23 13:52:13` @ `ad0c900`  

## Purpose
Pelaporan kendala lintas divisi, delegasi PIC perbaikan lapangan, foto bukti, dan tracking progres penyelesaian.

## Out of scope
- Hal-hal di luar tanggung jawab domain TiketMasalah.

## Invariants
- Modul lain tidak boleh memanipulasi tabel pemilik secara langsung.
- Perubahan finansial/status harus diverifikasi di level Service.

## Database Tables
### Owner Table(s)
- `tiket_masalah` *(akses tulis eksklusif hanya untuk modul TiketMasalah)*
- `tiket_masalah_progress` *(akses tulis eksklusif hanya untuk modul TiketMasalah)*
- `tiket_masalah_foto` *(akses tulis eksklusif hanya untuk modul TiketMasalah)*
- `tiket_masalah_user` *(akses tulis eksklusif hanya untuk modul TiketMasalah)*

### Shared Tables (Dibaca dari modul lain)
- `kavling`
- `proyek`
- `users`

## Contracts
### A. Public HTTP Endpoints & Routes Inventory
| Method | URI / Pattern | Tipe | Handler | Status Migrasi | Target API / Catatan |
|---|---|---|---|---|---|
| `GET` | `/tiket-masalah` | `Explicit` | `\App\Controllers\Web\TiketMasalah\TiketMasalahController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/tiket-masalah/list` | `Explicit` | `TiketMasalahController::list` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/tiket-masalah/detail` | `Explicit` | `TiketMasalahController::detail` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/tiket-masalah/progress` | `Explicit` | `TiketMasalahController::progress` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/tiket-masalah/store` | `Explicit` | `TiketMasalahController::store` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/tiket-masalah/update` | `Explicit` | `TiketMasalahController::update` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/tiket-masalah/add-progress` | `Explicit` | `TiketMasalahController::addProgress` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/tiket-masalah/ref-info` | `Explicit` | `TiketMasalahController::refInfo` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/tiket-masalah/users` | `Explicit` | `TiketMasalahController::users` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/tiket-masalah/divisions` | `Explicit` | `TiketMasalahController::divisions` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/tiket-masalah/creators` | `Explicit` | `TiketMasalahController::creators` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/tiket-masalah/create-others-area` | `Explicit` | `TiketMasalahController::createOthersArea` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/tiket-masalah/datatable` | `Explicit` | `TiketMasalahController::datatable` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/tiket-masalah/toggle-pin` | `Explicit` | `TiketMasalahController::togglePin` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |

### B. Public PHP Services (Backend Contract Antar-Modul)
#### `TiketMasalahService.php`
- `getListByRef(string $refType, int $refId)`
- `getDetail(int $idTiket)`
- `getProgress(int $idTiket, int $limit, int $offset)`
- `getDatatableData(array $params)`
- `createTiket(array $data, array $files)`
- `updateTiket(int $idTiket, array $data, array $files)`
- `addProgress(int $idTiket, array $data, array $files)`
- `togglePinProgress(int $idProgress)`
- `getRefInfo(string $refType, int $refId)`
- `getUserList()`
- `getCreatorList()`
- `getDivisionList()`
- `createOthersArea(array $params, int $userId)`

## File Inventory
### Controllers (2)
- `app/Controllers/Web/TiketMasalah/TiketMasalahController.php` (19 baris, 1 public methods)
- `app/Controllers/Api/TiketMasalah/TiketMasalahController.php` (218 baris, 13 public methods)

### Services (1)
- `app/Services/TiketMasalah/TiketMasalahService.php` (741 baris)

### Repositories (1)
- `app/Repositories/TiketMasalah/TiketMasalahRepository.php` (179 baris)

### Models (4)
- `app/Models/TiketMasalahModel.php`
- `app/Models/TiketMasalahProgressModel.php`
- `app/Models/TiketMasalahFotoModel.php`
- `app/Models/TiketMasalahUserModel.php`

### Views (1 folder)
- `app/Views/tiket_masalah` (1 file)

### JavaScript (2 file)
- `public/assets/js/tiket_masalah/index.js` (313 baris)
- `public/assets/js/siteplan/tiket-masalah.js` (1943 baris)

### Zero-Downtime Shims (4 file)
- `app/Controllers/Web/TiketMasalahController.php`
- `app/Controllers/Api/TiketMasalahController.php`
- `app/Services/TiketMasalahService.php`
- `app/Repositories/TiketMasalahRepository.php`

## Readiness Checklist
- [x] Has Service Layer
- [x] Has Repository Layer
- [x] Web & API Controllers Separated
- [x] Explicit Routes Defined
- [x] Zero Controller Layer Violations
- [x] MODULE.md Initialized

## Dead Code Candidates
_Jalankan `php spark module:deadcode TiketMasalah` untuk mengaudit dead code kandidat._

## Notes
- Modul diinisialisasi melalui tooling otomatis.

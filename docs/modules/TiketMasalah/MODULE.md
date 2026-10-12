# Module: TiketMasalah

**Status:** `REFACTORED`  
**Readiness Score:** `100/100`  
**Last Synced:** `2026-10-12 14:38:40` @ `be98758`  

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
| `POST` | `list` | `Explicit` | `TiketMasalahController::list` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `detail` | `Explicit` | `TiketMasalahController::detail` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `progress` | `Explicit` | `TiketMasalahController::progress` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `store` | `Explicit` | `TiketMasalahController::store` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `update` | `Explicit` | `TiketMasalahController::update` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `add-progress` | `Explicit` | `TiketMasalahController::addProgress` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `ref-info` | `Explicit` | `TiketMasalahController::refInfo` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `users` | `Explicit` | `TiketMasalahController::users` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `divisions` | `Explicit` | `TiketMasalahController::divisions` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `creators` | `Explicit` | `TiketMasalahController::creators` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `create-others-area` | `Explicit` | `TiketMasalahController::createOthersArea` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `datatable` | `Explicit` | `TiketMasalahController::datatable` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `toggle-pin` | `Explicit` | `TiketMasalahController::togglePin` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `tiket-masalah` | `Explicit` | `\App\Controllers\Web\TiketMasalahController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `list` | `Explicit` | `TiketMasalahController::list` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `detail` | `Explicit` | `TiketMasalahController::detail` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `progress` | `Explicit` | `TiketMasalahController::progress` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `store` | `Explicit` | `TiketMasalahController::store` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `update` | `Explicit` | `TiketMasalahController::update` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `add-progress` | `Explicit` | `TiketMasalahController::addProgress` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `ref-info` | `Explicit` | `TiketMasalahController::refInfo` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `users` | `Explicit` | `TiketMasalahController::users` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `divisions` | `Explicit` | `TiketMasalahController::divisions` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `creators` | `Explicit` | `TiketMasalahController::creators` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `create-others-area` | `Explicit` | `TiketMasalahController::createOthersArea` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `datatable` | `Explicit` | `TiketMasalahController::datatable` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `toggle-pin` | `Explicit` | `TiketMasalahController::togglePin` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `tiket-masalah` | `Explicit` | `\App\Controllers\Web\TiketMasalahController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |

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
- `notificationType(string $refType, int $refId, int $idTiket)`
- `getRefInfo(string $refType, int $refId)`
- `getUserList()`
- `getCreatorList()`
- `getDivisionList()`
- `compressImageIfPossible(string $absolutePath)`

## File Inventory
### Controllers (2)
- `app/Controllers/Web/TiketMasalahController.php` (19 baris, 1 public methods)
- `app/Controllers/Api/TiketMasalahController.php` (253 baris, 13 public methods)

### Services (1)
- `app/Services/TiketMasalahService.php` (693 baris)

### Repositories (1)
- `app/Repositories/TiketMasalahRepository.php` (174 baris)

### Models (4)
- `app/Models/TiketMasalahModel.php`
- `app/Models/TiketMasalahProgressModel.php`
- `app/Models/TiketMasalahFotoModel.php`
- `app/Models/TiketMasalahUserModel.php`

### Views (1 folder)
- `app/Views/tiket_masalah` (1 file)

### JavaScript (2 file)
- `public/assets/js/tiket_masalah/index.js` (293 baris)
- `public/assets/js/siteplan/tiket-masalah.js` (1607 baris)

## Readiness Checklist
- [x] Has Service Layer
- [x] Has Repository Layer
- [x] Web & API Controllers Separated
- [x] Explicit Routes Defined
- [x] MODULE.md Initialized

## Dead Code Candidates
_Jalankan `php spark module:deadcode TiketMasalah` untuk mengaudit dead code kandidat._

## Notes
- Modul diinisialisasi melalui tooling otomatis.

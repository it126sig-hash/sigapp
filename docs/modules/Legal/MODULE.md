# Module: Legal

**Status:** `REFACTORED`  
**Readiness Score:** `100/100`  
**Last Synced:** `2026-10-12 14:38:40` @ `be98758`  

## Purpose
Administrasi dan tracking perizinan lahan, sertifikat HGB/PBB/PBG/AJB, dan dokumen legal.

## Out of scope
- Hal-hal di luar tanggung jawab domain Legal.

## Invariants
- Modul lain tidak boleh memanipulasi tabel pemilik secara langsung.
- Perubahan finansial/status harus diverifikasi di level Service.

## Database Tables
### Owner Table(s)
- `legal` *(akses tulis eksklusif hanya untuk modul Legal)*

### Shared Tables (Dibaca dari modul lain)
- `kavling`
- `mkdt`

## Contracts
### A. Public HTTP Endpoints & Routes Inventory
| Method | URI / Pattern | Tipe | Handler | Status Migrasi | Target API / Catatan |
|---|---|---|---|---|---|
| `GET` | `privacy-policy` | `Explicit` | `\App\Controllers\Web\LegalPageController::privacyPolicy` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `toc` | `Explicit` | `\App\Controllers\Web\LegalPageController::termsOfService` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `/legalitas` | `Explicit` | `Legal::list_legalitas` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `save` | `Explicit` | `LegalController::save` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `get` | `Explicit` | `LegalController::getDataByID` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `remove` | `Explicit` | `LegalController::removeDoc` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `getDoc` | `Explicit` | `LegalController::getDoc` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `upload` | `Explicit` | `LegalController::upload` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `getListLegalitas` | `Explicit` | `LegalController::getListLegalitas` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `edit_others` | `Explicit` | `LegalController::edit_others` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `save` | `Explicit` | `LegalController::save` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `get` | `Explicit` | `LegalController::getDataByID` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `remove` | `Explicit` | `LegalController::removeDoc` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `getDoc` | `Explicit` | `LegalController::getDoc` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `upload` | `Explicit` | `LegalController::upload` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `getListLegalitas` | `Explicit` | `LegalController::getListLegalitas` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `edit_others` | `Explicit` | `LegalController::edit_others` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `privacy-policy` | `Explicit` | `\App\Controllers\Web\LegalPageController::privacyPolicy` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `toc` | `Explicit` | `\App\Controllers\Web\LegalPageController::termsOfService` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `ANY` | `/legal_controller/format_tgl` | `AutoRoute` | `LegalController::format_tgl` | 🟢 MIGRATED | Rute method aktif |

### B. Public PHP Services (Backend Contract Antar-Modul)
#### `LegalService.php`
- `simpan(array $data)`
- `num($d)`
- `deleteDocument($id)`
- `uploadDocument($file, array $data)`
- `editOthers(array $data, int $id_kavling)`

## File Inventory
### Controllers (3)
- `app/Controllers/Legal.php` (16 baris, 1 public methods)
- `app/Controllers/Api/LegalController.php` (206 baris, 8 public methods)
- `app/Controllers/Web/LegalPageController.php` (27 baris, 2 public methods)

### Services (1)
- `app/Services/LegalService.php` (150 baris)

### Repositories (1)
- `app/Repositories/LegalRepository.php` (190 baris)

### Models (1)
- `app/Models/LegalModel.php`

### Views (2 folder)
- `app/Views/legal` (1 file)
- `app/Views/public` (4 file)

### JavaScript (1 file)
- `public/assets/js/siteplan/legal.js` (470 baris)

## Readiness Checklist
- [x] Has Service Layer
- [x] Has Repository Layer
- [x] Web & API Controllers Separated
- [x] Explicit Routes Defined
- [x] MODULE.md Initialized

## Dead Code Candidates
_Jalankan `php spark module:deadcode Legal` untuk mengaudit dead code kandidat._

## Notes
- Modul diinisialisasi melalui tooling otomatis.

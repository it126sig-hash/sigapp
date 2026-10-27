# Module: Legal

**Status:** `MAPPED`  
**Readiness Score:** `70/100`  
**Last Synced:** `2026-10-23 13:52:12` @ `ad0c900`  

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
| `GET` | `/legalitas` | `Explicit` | `Legal::list_legalitas` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/api/legal/save` | `Explicit` | `LegalController::save` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/legal/get` | `Explicit` | `LegalController::getDataByID` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/legal/remove` | `Explicit` | `LegalController::removeDoc` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/legal/getDoc` | `Explicit` | `LegalController::getDoc` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/legal/upload` | `Explicit` | `LegalController::upload` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/legal/getListLegalitas` | `Explicit` | `LegalController::getListLegalitas` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/legal/edit_others` | `Explicit` | `LegalController::edit_others` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/privacy-policy` | `Explicit` | `\App\Controllers\Web\LegalPageController::privacyPolicy` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/toc` | `Explicit` | `\App\Controllers\Web\LegalPageController::termsOfService` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |

### B. Public PHP Services (Backend Contract Antar-Modul)
#### `LegalService.php`
- `simpan(array $data)`
- `deleteDocument($id)`
- `uploadDocument($file, array $data)`
- `editOthers(array $data, int $id_kavling)`

## File Inventory
### Controllers (3)
- `app/Controllers/Legal.php` (16 baris, 1 public methods)
- `app/Controllers/Api/LegalController.php` (206 baris, 7 public methods)
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
- [ ] Has Service Layer
- [ ] Has Repository Layer
- [ ] Web & API Controllers Separated
- [x] Explicit Routes Defined
- [x] Zero Controller Layer Violations
- [x] MODULE.md Initialized

## Dead Code Candidates
_Jalankan `php spark module:deadcode Legal` untuk mengaudit dead code kandidat._

## Notes
- Modul diinisialisasi melalui tooling otomatis.

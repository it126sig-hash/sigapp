# Module: BPB

**Status:** `REFACTORED`  
**Readiness Score:** `100/100`  
**Last Synced:** `2026-10-23 13:52:12` @ `ad0c900`  

## Purpose
Bon Permintaan Barang: alur pengajuan logistik lapangan, verifikasi bertingkat, dan tanda tangan digital.

## Out of scope
- Hal-hal di luar tanggung jawab domain BPB.

## Invariants
- Modul lain tidak boleh memanipulasi tabel pemilik secara langsung.
- Perubahan finansial/status harus diverifikasi di level Service.

## Database Tables
### Owner Table(s)
- `bpb_requests` *(akses tulis eksklusif hanya untuk modul BPB)*
- `bpb_items` *(akses tulis eksklusif hanya untuk modul BPB)*
- `bpb_files` *(akses tulis eksklusif hanya untuk modul BPB)*
- `bpb_history` *(akses tulis eksklusif hanya untuk modul BPB)*
- `bpb_signatures` *(akses tulis eksklusif hanya untuk modul BPB)*
- `user_signature_profiles` *(akses tulis eksklusif hanya untuk modul BPB)*

### Shared Tables (Dibaca dari modul lain)
- `proyek`
- `kavling`
- `users`

## Contracts
### A. Public HTTP Endpoints & Routes Inventory
| Method | URI / Pattern | Tipe | Handler | Status Migrasi | Target API / Catatan |
|---|---|---|---|---|---|
| `GET` | `/bpb` | `Explicit` | `BpbController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/bpb/(:num)/pdf` | `Explicit` | `BpbController::pdf/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/bpb/signature/(:num)` | `Explicit` | `BpbController::signature/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/bpb/verify/(:segment)` | `Explicit` | `BpbController::verify/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/bpb/list` | `Explicit` | `BpbController::list` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/bpb/options` | `Explicit` | `BpbController::options` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/bpb/detail/(:num)` | `Explicit` | `BpbController::detail/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/bpb/draft` | `Explicit` | `BpbController::draft` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/bpb/submit` | `Explicit` | `BpbController::submit` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/bpb/(:num)/update` | `Explicit` | `BpbController::update/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/bpb/(:num)/sign` | `Explicit` | `BpbController::sign/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/bpb/(:num)/reject` | `Explicit` | `BpbController::reject/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/bpb/(:num)/cancel` | `Explicit` | `BpbController::cancel/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/bpb/(:num)/status` | `Explicit` | `BpbController::status/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/profile/signature` | `Explicit` | `ProfileSignatureController::show` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/profile/signature/image` | `Explicit` | `ProfileSignatureController::image` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `PUT` | `/api/profile/signature` | `Explicit` | `ProfileSignatureController::save` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `DELETE` | `/api/profile/signature` | `Explicit` | `ProfileSignatureController::delete` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |

### B. Public PHP Services (Backend Contract Antar-Modul)
#### `BpbService.php`
- `list(array $input, int $userId)`
- `options(int $userId)`
- `detail(int $id, int $userId)`
- `saveDraft(array $data, array $uploads, int $userId)`
- `submit(array $data, array $uploads, int $userId, string $ip, string $agent)`
- `update(int $id, array $data, array $uploads, int $userId, string $ip, string $agent)`
- `sign(int $id, array $data, int $userId, string $ip, string $agent)`
- `reject(int $id, string $reason, int $userId)`
- `cancel(int $id, string $reason, int $userId)`
- `changeStatus(int $id, array $data, array $uploads, int $userId, string $ip, string $agent)`
- `signaturePath(int $signatureId, int $userId)`
#### `BpbWorkflow.php`
- `labels()`
- `label(string $status)`
- `approvalStatus(bool $hasCc)`
- `afterSignature(string $status)`
- `canOperationalTransition(string $from, string $to)`
- `isTerminal(string $status)`
#### `BpbPdfService.php`
- `render(array $detail, int $userId)`
- `verify(string $token)`
#### `BpbFileService.php`
- `storeUploads(array $uploads, int $bpbId, string $category, int $userId, int $existingCount = 0)`
- `storeCanvas(string $dataUrl, string $logicalDir)`
- `copyPrivate(string $sourceLogicalPath, string $logicalDir)`
- `cleanup(array $logicalPaths)`
#### `BpbNumberFormatter.php`
- `format(int $sequence, int $month, int $year)`
#### `BpbDocumentHasher.php`
- `hash(array $request, array $items, array $files)`
#### `ProfileSignatureService.php`
- `get(int $userId)`
- `save(int $userId, string $password, string $dataUrl)`
- `delete(int $userId, string $password)`
- `absolutePath(int $userId)`

## File Inventory
### Controllers (3)
- `app/Controllers/Web/Bpb/BpbController.php` (77 baris, 4 public methods)
- `app/Controllers/Api/Bpb/BpbController.php` (123 baris, 10 public methods)
- `app/Controllers/Api/Bpb/ProfileSignatureController.php` (54 baris, 4 public methods)

### Services (7)
- `app/Services/Bpb/BpbService.php` (664 baris)
- `app/Services/Bpb/BpbWorkflow.php` (63 baris)
- `app/Services/Bpb/BpbPdfService.php` (323 baris)
- `app/Services/Bpb/BpbFileService.php` (119 baris)
- `app/Services/Bpb/BpbNumberFormatter.php` (17 baris)
- `app/Services/Bpb/BpbDocumentHasher.php` (44 baris)
- `app/Services/Bpb/ProfileSignatureService.php` (89 baris)

### Repositories (1)
- `app/Repositories/Bpb/BpbRepository.php` (206 baris)

### Models (6)
- `app/Models/BpbRequestModel.php`
- `app/Models/BpbItemModel.php`
- `app/Models/BpbFileModel.php`
- `app/Models/BpbHistoryModel.php`
- `app/Models/BpbSignatureModel.php`
- `app/Models/UserSignatureProfileModel.php`

### Views (1 folder)
- `app/Views/bpb` (3 file)

### JavaScript (2 file)
- `public/assets/js/bpb.js` (628 baris)
- `public/assets/js/profile-signature.js` (23 baris)

### Zero-Downtime Shims (4 file)
- `app/Controllers/Web/BpbController.php`
- `app/Controllers/Api/BpbController.php`
- `app/Controllers/Api/ProfileSignatureController.php`
- `app/Repositories/BpbRepository.php`

## Readiness Checklist
- [x] Has Service Layer
- [x] Has Repository Layer
- [x] Web & API Controllers Separated
- [x] Explicit Routes Defined
- [x] Zero Controller Layer Violations
- [x] MODULE.md Initialized

## Dead Code Candidates
_Jalankan `php spark module:deadcode BPB` untuk mengaudit dead code kandidat._

## Notes
- Modul diinisialisasi melalui tooling otomatis.

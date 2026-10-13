# Module: BPB

**Status:** `REFACTORED`  
**Readiness Score:** `100/100`  
**Last Synced:** `2026-10-13 09:19:59` @ `3b94cd9`  

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
| `GET` | `(:num)/pdf` | `Explicit` | `BpbController::pdf/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `signature/(:num)` | `Explicit` | `BpbController::signature/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `verify/(:segment)` | `Explicit` | `BpbController::verify/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `list` | `Explicit` | `BpbController::list` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `options` | `Explicit` | `BpbController::options` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `detail/(:num)` | `Explicit` | `BpbController::detail/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `draft` | `Explicit` | `BpbController::draft` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `submit` | `Explicit` | `BpbController::submit` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `(:num)/update` | `Explicit` | `BpbController::update/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `(:num)/sign` | `Explicit` | `BpbController::sign/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `(:num)/reject` | `Explicit` | `BpbController::reject/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `(:num)/cancel` | `Explicit` | `BpbController::cancel/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `(:num)/status` | `Explicit` | `BpbController::status/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `(:num)/pdf` | `Explicit` | `BpbController::pdf/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `signature/(:num)` | `Explicit` | `BpbController::signature/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `verify/(:segment)` | `Explicit` | `BpbController::verify/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `list` | `Explicit` | `BpbController::list` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `options` | `Explicit` | `BpbController::options` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `detail/(:num)` | `Explicit` | `BpbController::detail/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `draft` | `Explicit` | `BpbController::draft` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `submit` | `Explicit` | `BpbController::submit` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `(:num)/update` | `Explicit` | `BpbController::update/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `(:num)/sign` | `Explicit` | `BpbController::sign/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `(:num)/reject` | `Explicit` | `BpbController::reject/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `(:num)/cancel` | `Explicit` | `BpbController::cancel/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `(:num)/status` | `Explicit` | `BpbController::status/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `image` | `Explicit` | `ProfileSignatureController::image` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `ANY` | `/bpb_controller/index` | `AutoRoute` | `BpbController::index` | 🟢 MIGRATED | Rute method aktif |
| `ANY` | `/bpb_controller/run` | `AutoRoute` | `BpbController::run` | 🟢 MIGRATED | Rute method aktif |
| `ANY` | `/bpb_controller/payload` | `AutoRoute` | `BpbController::payload` | 🟢 MIGRATED | Rute method aktif |
| `ANY` | `/bpb_controller/uploads` | `AutoRoute` | `BpbController::uploads` | 🟢 MIGRATED | Rute method aktif |
| `ANY` | `/profile_signature_controller/show` | `AutoRoute` | `ProfileSignatureController::show` | 🟢 MIGRATED | Rute method aktif |
| `ANY` | `/profile_signature_controller/save` | `AutoRoute` | `ProfileSignatureController::save` | 🟢 MIGRATED | Rute method aktif |
| `ANY` | `/profile_signature_controller/delete` | `AutoRoute` | `ProfileSignatureController::delete` | 🟢 MIGRATED | Rute method aktif |

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
- `saveSubmitted(int $id, array $data, array $uploads, int $userId, string $ip, string $agent, bool $editing)`
- `isSubmissionKeyConflict(\Throwable $error)`
- `createSignature(array $request, string $role, array $user, array $data, string $hash, string $ip, string $agent)`
- `normalizeItems(mixed $items, bool $required)`
- _dan 14 method lainnya..._
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
- `prepareAttachments(array $files, array &$temporaryFiles)`
- `appendAttachments(Mpdf $mpdf, array $attachments)`
- `addImportedPage(Mpdf $mpdf, string $template, float $sourceWidth, float $sourceHeight)`
- `formatIndonesianDate(string $date)`
- `formatSignatureDate(string $date)`
#### `BpbFileService.php`
- `storeUploads(array $uploads, int $bpbId, string $category, int $userId, int $existingCount = 0)`
- `storeCanvas(string $dataUrl, string $logicalDir)`
- `copyPrivate(string $sourceLogicalPath, string $logicalDir)`
- `cleanup(array $logicalPaths)`
- `compressImage(string $logicalPath)`
#### `BpbNumberFormatter.php`
- `format(int $sequence, int $month, int $year)`
#### `BpbDocumentHasher.php`
- `hash(array $request, array $items, array $files)`
#### `ProfileSignatureService.php`
- `get(int $userId)`
- `save(int $userId, string $password, string $dataUrl)`
- `delete(int $userId, string $password)`
- `absolutePath(int $userId)`
- `verifyPassword(int $userId, string $password)`

## File Inventory
### Controllers (3)
- `app/Controllers/Web/Bpb/BpbController.php` (77 baris, 4 public methods)
- `app/Controllers/Api/Bpb/BpbController.php` (123 baris, 13 public methods)
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

## Readiness Checklist
- [x] Has Service Layer
- [x] Has Repository Layer
- [x] Web & API Controllers Separated
- [x] Explicit Routes Defined
- [x] MODULE.md Initialized

## Dead Code Candidates
_Jalankan `php spark module:deadcode BPB` untuk mengaudit dead code kandidat._

## Notes
- Modul diinisialisasi melalui tooling otomatis.

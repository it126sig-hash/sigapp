# Module: Auth

**Status:** `MAPPED`  
**Readiness Score:** `70/100`  
**Last Synced:** `2026-10-23 13:52:13` @ `ad0c900`  

## Purpose
Autentikasi sesi multi-device, revocations, proteksi login, dan audit sesi pengguna.

## Out of scope
- Hal-hal di luar tanggung jawab domain Auth.

## Invariants
- Modul lain tidak boleh memanipulasi tabel pemilik secara langsung.
- Perubahan finansial/status harus diverifikasi di level Service.

## Database Tables
### Owner Table(s)
- `auth_device_sessions` *(akses tulis eksklusif hanya untuk modul Auth)*

### Shared Tables (Dibaca dari modul lain)
- `users`

## Contracts
### A. Public HTTP Endpoints & Routes Inventory
| Method | URI / Pattern | Tipe | Handler | Status Migrasi | Target API / Catatan |
|---|---|---|---|---|---|
| `GET` | `/login` | `Explicit` | `\App\Controllers\Web\DeviceAuthController::login` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/login` | `Explicit` | `\App\Controllers\Web\DeviceAuthController::attemptLogin` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/logout` | `Explicit` | `\App\Controllers\Web\DeviceAuthController::logout` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/auth/devices` | `Explicit` | `AuthDeviceController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/auth/devices/(:num)/revoke` | `Explicit` | `AuthDeviceController::revoke/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/auth/devices/revoke-others` | `Explicit` | `AuthDeviceController::revokeOthers` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/admin/users/(:num)/devices` | `Explicit` | `AdminAuthDeviceController::index/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/admin/users/(:num)/devices/(:num)/revoke` | `Explicit` | `AdminAuthDeviceController::revoke/$1/$2` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/admin/users/(:num)/devices/revoke-all` | `Explicit` | `AdminAuthDeviceController::revokeAll/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/landing` | `Explicit` | `\App\Controllers\Web\LandingController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |

### B. Public PHP Services (Backend Contract Antar-Modul)
#### `DeviceSessionService.php`
- `registerOrRotate(int $userId, string $rawToken, ?string $rememberSelector, ?string $userAgent, ?string $ipAddress, ?string $previousRawToken = null,)`
- `resolveActive(int $userId, string $rawToken)`
- `resolveActiveForRemember(string $rawToken, string $rememberSelector)`
- `listActive(int $userId, string $currentRawToken)`
- `listActiveForUser(int $userId)`
- `touchLastSeen(int $userId, int $deviceId)`
- `revokeOne(int $userId, int $deviceId, int $revokedBy, ?string $reason = null)`
- `revokeOthers(int $userId, string $currentRawToken, int $revokedBy, ?string $reason = null,)`
- `revokeAll(int $userId, int $revokedBy, ?string $reason = null)`
- `deleteRememberSelector(string $selector, ?int $userId = null)`
#### `DeviceManagementService.php`
- `listOwn(int $userId, string $currentRawToken)`
- `revokeOwn(int $userId, int $deviceId, string $password, string $currentRawToken)`
- `revokeOthers(int $userId, string $currentRawToken, string $password)`
- `listForAdmin(int $adminUserId, int $targetUserId)`
- `revokeForAdmin(int $adminUserId, int $targetUserId, int $deviceId, string $reason,)`
- `revokeAllForAdmin(int $adminUserId, int $targetUserId, string $reason)`
#### `UserAgentDeviceParser.php`
- `parse(?string $userAgent)`
#### `DeviceManagementException.php`
- `statusCode()`

## File Inventory
### Controllers (4)
- `app/Controllers/Web/DeviceAuthController.php` (167 baris, 2 public methods)
- `app/Controllers/Api/AuthDeviceController.php` (89 baris, 3 public methods)
- `app/Controllers/Api/AdminAuthDeviceController.php` (77 baris, 3 public methods)
- `app/Controllers/Web/LandingController.php` (16 baris, 1 public methods)

### Services (4)
- `app/Services/DeviceSessionService.php` (259 baris)
- `app/Services/DeviceManagementService.php` (149 baris)
- `app/Services/UserAgentDeviceParser.php` (64 baris)
- `app/Services/DeviceManagementException.php` (19 baris)

### Repositories (1)
- `app/Repositories/DeviceSessionRepository.php` (427 baris)

### Models (1)
- `app/Models/DeviceSessionModel.php`

### Views (0 folder)

### JavaScript (2 file)
- `public/assets/js/auth-devices.js` (122 baris)
- `public/assets/js/admin-auth-devices.js` (95 baris)

## Readiness Checklist
- [ ] Has Service Layer
- [ ] Has Repository Layer
- [ ] Web & API Controllers Separated
- [x] Explicit Routes Defined
- [x] Zero Controller Layer Violations
- [x] MODULE.md Initialized

## Dead Code Candidates
_Jalankan `php spark module:deadcode Auth` untuk mengaudit dead code kandidat._

## Notes
- Modul diinisialisasi melalui tooling otomatis.

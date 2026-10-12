# Module: Auth

**Status:** `REFACTORED`  
**Readiness Score:** `100/100`  
**Last Synced:** `2026-10-12 14:38:40` @ `be98758`  

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
| `GET` | `login` | `Explicit` | `\\App\\Controllers\\Web\\DeviceAuthController::login` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `login` | `Explicit` | `\\App\\Controllers\\Web\\DeviceAuthController::attemptLogin` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `logout` | `Explicit` | `\\App\\Controllers\\Web\\DeviceAuthController::logout` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `(:num)/revoke` | `Explicit` | `AuthDeviceController::revoke/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `revoke-others` | `Explicit` | `AuthDeviceController::revokeOthers` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `(:num)/revoke` | `Explicit` | `AdminAuthDeviceController::revoke/$1/$2` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `revoke-all` | `Explicit` | `AdminAuthDeviceController::revokeAll/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `(:num)/revoke` | `Explicit` | `AdminAuthDeviceController::revoke/$1/$2` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `revoke-all` | `Explicit` | `AdminAuthDeviceController::revokeAll/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `ANY` | `/device_auth_controller/setdevicecookie` | `AutoRoute` | `DeviceAuthController::setDeviceCookie` | 🟢 MIGRATED | Rute method aktif |
| `ANY` | `/device_auth_controller/expireauthcookies` | `AutoRoute` | `DeviceAuthController::expireAuthCookies` | 🟢 MIGRATED | Rute method aktif |
| `ANY` | `/device_auth_controller/clearsession` | `AutoRoute` | `DeviceAuthController::clearSession` | 🟢 MIGRATED | Rute method aktif |
| `ANY` | `/device_auth_controller/rememberselector` | `AutoRoute` | `DeviceAuthController::rememberSelector` | 🟢 MIGRATED | Rute method aktif |
| `ANY` | `/auth_device_controller/index` | `AutoRoute` | `AuthDeviceController::index` | 🟢 MIGRATED | Rute method aktif |
| `ANY` | `/auth_device_controller/run` | `AutoRoute` | `AuthDeviceController::run` | 🟢 MIGRATED | Rute method aktif |
| `ANY` | `/auth_device_controller/jsonresponse` | `AutoRoute` | `AuthDeviceController::jsonResponse` | 🟢 MIGRATED | Rute method aktif |
| `ANY` | `/admin_auth_device_controller/index` | `AutoRoute` | `AdminAuthDeviceController::index` | 🟢 MIGRATED | Rute method aktif |
| `ANY` | `/admin_auth_device_controller/run` | `AutoRoute` | `AdminAuthDeviceController::run` | 🟢 MIGRATED | Rute method aktif |
| `ANY` | `/admin_auth_device_controller/jsonresponse` | `AutoRoute` | `AdminAuthDeviceController::jsonResponse` | 🟢 MIGRATED | Rute method aktif |

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
- `hashToken(string $rawToken)`
- `publicDevice(array $row)`
- `expiresAt(DateTimeImmutable $now)`
- `now()`
- `nullableLimited(?string $value, int $limit)`
- _dan 2 method lainnya..._
#### `DeviceManagementService.php`
- `listOwn(int $userId, string $currentRawToken)`
- `revokeOwn(int $userId, int $deviceId, string $password, string $currentRawToken)`
- `revokeOthers(int $userId, string $currentRawToken, string $password)`
- `listForAdmin(int $adminUserId, int $targetUserId)`
- `revokeForAdmin(int $adminUserId, int $targetUserId, int $deviceId, string $reason,)`
- `revokeAllForAdmin(int $adminUserId, int $targetUserId, string $reason)`
- `requireActiveCurrent(int $userId, string $rawToken)`
- `verifyPassword(int $userId, string $password)`
- `requireAdmin(int $adminUserId)`
- `requireUser(int $userId)`
- `requireReason(string $reason)`
#### `UserAgentDeviceParser.php`
- `parse(?string $userAgent)`
- `browser(string $userAgent)`
- `platform(string $userAgent)`

## File Inventory
### Controllers (3)
- `app/Controllers/Web/DeviceAuthController.php` (167 baris, 6 public methods)
- `app/Controllers/Api/AuthDeviceController.php` (89 baris, 5 public methods)
- `app/Controllers/Api/AdminAuthDeviceController.php` (77 baris, 5 public methods)

### Services (3)
- `app/Services/DeviceSessionService.php` (259 baris)
- `app/Services/DeviceManagementService.php` (149 baris)
- `app/Services/UserAgentDeviceParser.php` (64 baris)

### Repositories (1)
- `app/Repositories/DeviceSessionRepository.php` (427 baris)

### Models (1)
- `app/Models/DeviceSessionModel.php`

### Views (0 folder)

### JavaScript (2 file)
- `public/assets/js/auth-devices.js` (122 baris)
- `public/assets/js/admin-auth-devices.js` (95 baris)

## Readiness Checklist
- [x] Has Service Layer
- [x] Has Repository Layer
- [x] Web & API Controllers Separated
- [x] Explicit Routes Defined
- [x] MODULE.md Initialized

## Dead Code Candidates
_Jalankan `php spark module:deadcode Auth` untuk mengaudit dead code kandidat._

## Notes
- Modul diinisialisasi melalui tooling otomatis.

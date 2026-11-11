# Module: Auth

**Status:** `MAPPED`  
**Readiness Score:** `60/100`  
**Last Synced:** `2026-11-12 01:53:35` @ `d86bec6`  

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
| `POST` | `/api/auth/login` | `Explicit` | `Api\Auth\AuthController::login` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/auth/me` | `Explicit` | `AuthController::me` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/auth/logout` | `Explicit` | `AuthController::logout` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
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
- `resolveActiveByToken(string $rawToken)`
- `verifyDeviceBinding(array $device, ?string $currentUserAgent)`
- `revokeByToken(string $rawToken, ?string $reason = 'user_logout')`
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
### Controllers (5)
- `app/Controllers/Web/DeviceAuthController.php` (167 baris, 2 public methods: `attemptLogin()`, `logout()`)
- `app/Controllers/Api/Auth/AuthController.php` (292 baris, 3 public methods: `login()`, `logout()`, `me()`)
- `app/Controllers/Api/AuthDeviceController.php` (125 baris, 3 public methods: `index()`, `revoke(int $deviceId)`, `revokeOthers()`)
- `app/Controllers/Api/AdminAuthDeviceController.php` (77 baris, 3 public methods: `index(int $userId)`, `revoke(int $userId, int $deviceId)`, `revokeAll(int $userId)`)
- `app/Controllers/Web/LandingController.php` (16 baris, 1 public methods: `index()`)

### Services (4)
- `app/Services/DeviceSessionService.php` (322 baris, 13 public methods)
- `app/Services/DeviceManagementService.php` (149 baris, 6 public methods)
- `app/Services/UserAgentDeviceParser.php` (64 baris, 1 public methods)
- `app/Services/DeviceManagementException.php` (19 baris, 1 public methods)

### Repositories (1)
- `app/Repositories/DeviceSessionRepository.php` (439 baris, 10 public methods: `registerOrRotate(array $data, ?string $previousTokenHash)`, `findActiveByTokenHash(int $userId, string $tokenHash, string $now)`, `findActiveByTokenHashOnly(string $tokenHash, string $now)`, `findActiveByTokenHashAndSelector(string $tokenHash, string $selector, string $now)`, `listActive(int $userId, string $now)`, `touchIfStale(int $userId, int $deviceId, string $threshold, string $now, string $expiresAt)`, `revokeOne(int $userId, int $deviceId, int $revokedBy, ?string $reason, string $now)`, `revokeOthers(int $userId, int $currentDeviceId, int $revokedBy, ?string $reason, string $now)`, `revokeAll(int $userId, int $revokedBy, ?string $reason, string $now)`, `deleteRememberSelector(string $selector, ?int $userId = null)`)

### Models (1)
- `app/Models/DeviceSessionModel.php`

### Views (0 folder)

### JavaScript (2 file)
- `public/assets/js/auth-devices.js` (122 baris)
- `public/assets/js/admin-auth-devices.js` (95 baris)

## Key Functions
- **Service (`app/Services/DeviceSessionService.php`):** `registerOrRotate(int $userId, string $rawToken, ?string $rememberSelector, ?string $userAgent, ?string $ipAddress, ?string $previousRawToken = null,)`, `resolveActive(int $userId, string $rawToken)`, `resolveActiveByToken(string $rawToken)`, `revokeByToken(string $rawToken, ?string $reason = 'user_logout')`, `resolveActiveForRemember(string $rawToken, string $rememberSelector)`
- **Service (`app/Services/DeviceManagementService.php`):** `listOwn(int $userId, string $currentRawToken)`, `revokeOwn(int $userId, int $deviceId, string $password, string $currentRawToken)`, `revokeOthers(int $userId, string $currentRawToken, string $password)`, `listForAdmin(int $adminUserId, int $targetUserId)`, `revokeForAdmin(int $adminUserId, int $targetUserId, int $deviceId, string $reason,)`
- **Service (`app/Services/UserAgentDeviceParser.php`):** `parse(?string $userAgent)`
- **Service (`app/Services/DeviceManagementException.php`):** `statusCode()`
- **Repository (`app/Repositories/DeviceSessionRepository.php`):** `registerOrRotate(array $data, ?string $previousTokenHash)`, `findActiveByTokenHash(int $userId, string $tokenHash, string $now)`, `findActiveByTokenHashOnly(string $tokenHash, string $now)`, `findActiveByTokenHashAndSelector(string $tokenHash, string $selector, string $now)`, `listActive(int $userId, string $now)`

## Deprecated & Legacy Shims
- *(Tidak ada fungsi, route, atau class shim yang berstatus deprecated saat ini).*

## Readiness Checklist
- [ ] Has Service Layer
- [ ] Has Repository Layer
- [ ] Web & API Controllers Separated
- [x] Explicit Routes Defined
- [ ] Zero Controller Layer Violations
- [x] MODULE.md Initialized

> **Layer Violations Detected:**
> - `app/Controllers/Api/Auth/AuthController.php`: db_connect(), $this->db->table()

## Dead Code Candidates
_Jalankan `php spark module:deadcode Auth` untuk mengaudit dead code kandidat._

## Notes
- Modul diinisialisasi melalui tooling otomatis.

## Change Log
- `2026-11-10` [`WAVE0-AUTH`] — Penambahan endpoint REST API JSON `POST /api/auth/login`, `GET /api/auth/me`, dan `POST /api/auth/logout` untuk autentikasi Bearer Token SPA Vue 3 / Capacitor, integrasi Dual-Mode Bearer Token di `DeviceLoginFilter`, serta rotasi sesi multi-device `DeviceSessionService`.
- `2026-11-10` [`SYNC`] — Sinkronisasi status & inventori modul melalui `module:sync`.

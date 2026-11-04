# Module: UserManagement

**Status:** `REFACTORED`  
**Readiness Score:** `100/100`  
**Last Synced:** `2026-10-23 13:52:13` @ `ad0c900`  

## Purpose
Manajemen kepegawaian, struktur divisi, hak akses menu, akun pengguna, dan profil perusahaan.

## Out of scope
- Hal-hal di luar tanggung jawab domain UserManagement.

## Invariants
- Modul lain tidak boleh memanipulasi tabel pemilik secara langsung.
- Perubahan finansial/status harus diverifikasi di level Service.

## Database Tables
### Owner Table(s)
- `karyawan` *(akses tulis eksklusif hanya untuk modul UserManagement)*
- `divisi` *(akses tulis eksklusif hanya untuk modul UserManagement)*
- `level` *(akses tulis eksklusif hanya untuk modul UserManagement)*
- `users` *(akses tulis eksklusif hanya untuk modul UserManagement)*
- `profile_perusahaan` *(akses tulis eksklusif hanya untuk modul UserManagement)*

### Shared Tables (Dibaca dari modul lain)
- `proyek`

## Contracts
### A. Public HTTP Endpoints & Routes Inventory
| Method | URI / Pattern | Tipe | Handler | Status Migrasi | Target API / Catatan |
|---|---|---|---|---|---|
| `GET` | `/pengguna/setuser` | `Explicit` | `PenggunaController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/pengguna/setuser` | `Explicit` | `\App\Controllers\Web\UserManagement\PenggunaController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/pengguna/getall` | `Explicit` | `PenggunaController::getAll` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/pengguna/getAll` | `Explicit` | `PenggunaController::getAll` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/pengguna/getone` | `Explicit` | `PenggunaController::getOne` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/pengguna/getOne` | `Explicit` | `PenggunaController::getOne` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/pengguna/add` | `Explicit` | `PenggunaController::add` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/pengguna/edit` | `Explicit` | `PenggunaController::edit` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/pengguna/edit_user` | `Explicit` | `PenggunaController::edit_user` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/pengguna/remove` | `Explicit` | `PenggunaController::remove` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/pengguna/getAll` | `Explicit` | `\App\Controllers\Api\UserManagement\PenggunaController::getAll` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/pengguna/getall` | `Explicit` | `\App\Controllers\Api\UserManagement\PenggunaController::getAll` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/pengguna/getOne` | `Explicit` | `\App\Controllers\Api\UserManagement\PenggunaController::getOne` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/pengguna/getone` | `Explicit` | `\App\Controllers\Api\UserManagement\PenggunaController::getOne` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/pengguna/add` | `Explicit` | `\App\Controllers\Api\UserManagement\PenggunaController::add` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/pengguna/edit` | `Explicit` | `\App\Controllers\Api\UserManagement\PenggunaController::edit` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/pengguna/edit_user` | `Explicit` | `\App\Controllers\Api\UserManagement\PenggunaController::edit_user` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/pengguna/remove` | `Explicit` | `\App\Controllers\Api\UserManagement\PenggunaController::remove` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/karyawan` | `Explicit` | `KaryawanController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/karyawan` | `Explicit` | `\App\Controllers\Web\UserManagement\KaryawanController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/karyawan/getall` | `Explicit` | `KaryawanController::getAll` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/karyawan/getAll` | `Explicit` | `KaryawanController::getAll` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/karyawan/getone` | `Explicit` | `KaryawanController::getOne` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/karyawan/getOne` | `Explicit` | `KaryawanController::getOne` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/karyawan/add` | `Explicit` | `KaryawanController::add` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/karyawan/edit` | `Explicit` | `KaryawanController::edit` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/karyawan/remove` | `Explicit` | `KaryawanController::remove` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/karyawan/getAll` | `Explicit` | `\App\Controllers\Api\UserManagement\KaryawanController::getAll` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/karyawan/getall` | `Explicit` | `\App\Controllers\Api\UserManagement\KaryawanController::getAll` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/karyawan/getOne` | `Explicit` | `\App\Controllers\Api\UserManagement\KaryawanController::getOne` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/karyawan/getone` | `Explicit` | `\App\Controllers\Api\UserManagement\KaryawanController::getOne` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/karyawan/add` | `Explicit` | `\App\Controllers\Api\UserManagement\KaryawanController::add` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/karyawan/edit` | `Explicit` | `\App\Controllers\Api\UserManagement\KaryawanController::edit` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/karyawan/remove` | `Explicit` | `\App\Controllers\Api\UserManagement\KaryawanController::remove` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/profil` | `Explicit` | `\App\Controllers\Web\UserManagement\ProfilController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/profil/update` | `Explicit` | `\App\Controllers\Web\UserManagement\ProfilController::update` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/profil/update` | `Explicit` | `ProfilController::update` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |

### B. Public PHP Services (Backend Contract Antar-Modul)
#### `PenggunaService.php`
- `getSetuserData()`
- `getDatatable()`
- `getOne(int $id)`
- `addUser(array $postData)`
- `editUser(array $postData)`
- `toggleStatus(int $id, int $currentStatus)`
#### `KaryawanService.php`
- `getIndexData()`
- `getDatatable()`
- `getOne(string $nik)`
- `addKaryawan(array $postData)`
- `editKaryawan(array $postData)`
- `toggleStatus(string $nik, int $currentStatus)`
#### `ProfilService.php`
- `getIndexData(int $userId)`
- `updateProfile(int $userId, array $postData, ?UploadedFile $photo = null)`
- `profilePhotoUrl(?string $path)`

## File Inventory
### Controllers (6)
- `app/Controllers/Web/UserManagement/PenggunaController.php` (25 baris, 1 public methods)
- `app/Controllers/Api/UserManagement/PenggunaController.php` (71 baris, 6 public methods)
- `app/Controllers/Web/UserManagement/KaryawanController.php` (25 baris, 1 public methods)
- `app/Controllers/Api/UserManagement/KaryawanController.php` (66 baris, 5 public methods)
- `app/Controllers/Web/UserManagement/ProfilController.php` (41 baris, 2 public methods)
- `app/Controllers/Api/UserManagement/ProfilController.php` (31 baris, 1 public methods)

### Services (3)
- `app/Services/UserManagement/PenggunaService.php` (346 baris)
- `app/Services/UserManagement/KaryawanService.php` (242 baris)
- `app/Services/UserManagement/ProfilService.php` (230 baris)

### Repositories (4)
- `app/Repositories/UserManagement/PenggunaRepository.php` (107 baris)
- `app/Repositories/UserManagement/KaryawanRepository.php` (110 baris)
- `app/Repositories/UserManagement/ProfilRepository.php` (58 baris)
- `app/Repositories/DivisiRepository.php` (42 baris)

### Models (5)
- `app/Models/KaryawanModel.php`
- `app/Models/DivisiModel.php`
- `app/Models/LevelModel.php`
- `app/Models/UserModel.php`
- `app/Models/ProfilePerusahaanModel.php`

### Views (2 folder)
- `app/Views/user` (4 file)
- `app/Views/master` (9 file)

### JavaScript (0 file)

### Zero-Downtime Shims (5 file)
- `app/Controllers/Pengguna.php`
- `app/Controllers/Karyawan.php`
- `app/Controllers/Profil.php`
- `app/Services/PenggunaService.php`
- `app/Repositories/KaryawanRepository.php`

## Readiness Checklist
- [x] Has Service Layer
- [x] Has Repository Layer
- [x] Web & API Controllers Separated
- [x] Explicit Routes Defined
- [x] Zero Controller Layer Violations
- [x] MODULE.md Initialized

## Dead Code Candidates
_Jalankan `php spark module:deadcode UserManagement` untuk mengaudit dead code kandidat._

## Notes
- Modul diinisialisasi melalui tooling otomatis.
- 2026-11-04: Bugfix upload foto profil pada ProfilService (`TypeError: FileRules::uploaded()`). Objek UploadedFile dipisahkan dari array validasi data teks dan umpan balik error profil_photo ditambahkan pada view profil.

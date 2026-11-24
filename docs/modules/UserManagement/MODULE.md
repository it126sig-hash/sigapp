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

## Key Functions
- **Web Controller (`app/Controllers/Web/UserManagement/PenggunaController.php`):** `index()`
- **Web Controller (`app/Controllers/Web/UserManagement/KaryawanController.php`):** `index()`
- **Web Controller (`app/Controllers/Web/UserManagement/ProfilController.php`):** `index()`, `update()`
- **API Controller (`app/Controllers/Api/UserManagement/PenggunaController.php`):** `getAll()`, `getOne()`, `add()`, `edit($id = null)`, `edit_user()`, `remove()`
- **API Controller (`app/Controllers/Api/UserManagement/KaryawanController.php`):** `getAll()`, `getOne()`, `add()`, `edit($id = null)`, `remove()`
- **API Controller (`app/Controllers/Api/UserManagement/ProfilController.php`):** `update($id = null)`
- **Service (`app/Services/UserManagement/PenggunaService.php`):** `getSetuserData()`, `getDatatable()`, `getOne(int $id)`, `addUser(array $postData)`, `editUser(array $postData)`, `toggleStatus(int $id, int $currentStatus)`
- **Service (`app/Services/UserManagement/KaryawanService.php`):** `getIndexData()`, `getDatatable()`, `getOne(string $nik)`, `addKaryawan(array $postData)`, `editKaryawan(array $postData)`, `toggleStatus(string $nik, int $currentStatus)`
- **Service (`app/Services/UserManagement/ProfilService.php`):** `getIndexData(int $userId)`, `updateProfile(int $userId, array $postData, ?UploadedFile $photo = null)`, `profilePhotoUrl(?string $path)`
- **Repository (`app/Repositories/UserManagement/PenggunaRepository.php`):** `getAll()`, `findById(int $id)`, `findRawUser(int $id)`, `insertUser(array $data)`, `updateUser(int $id, array $data)`, `syncGroup(int $userId, int $groupId)`, `syncPermission(int $userId, int $permissionId)`, `toggleActive(int $id, int $newActiveStatus)`
- **Repository (`app/Repositories/UserManagement/KaryawanRepository.php`):** `getAll()`, `getUnlinkedKaryawan()`, `findByNik(string $nik)`, `findByUserId(int $userId)`, `insert(array $data)`, `update(string $nik, array $data)`, `linkUser(string $nik, int $userId)`, `toggleStatus(string $nik, int $newStatus)`, `getDivisiList()`, `getLevelList()`
- **Repository (`app/Repositories/UserManagement/ProfilRepository.php`):** `getUserRow(int $userId)`, `getDepartmentName(int $userId)`, `updateUser(int $userId, array $fields)`, `syncKaryawanName(int $userId, string $name)`
- **Repository (`app/Repositories/DivisiRepository.php`):** `findAll(int $limit = null, int $offset = 0)`, `findById($id)`

## Deprecated & Legacy Shims
- `app/Controllers/Pengguna.php` — **DEPRECATED (`fat_legacy` Shim):** Menduplikasi method endpoint `PenggunaService` dan masih menginstansiasi `new \App\Repositories\UserManagement\PenggunaRepository()` langsung pada `changeGroup()` & `chnagePermission()` (L77–87); gunakan `App\Controllers\Web\UserManagement\PenggunaController` / `App\Controllers\Api\UserManagement\PenggunaController`.
- `app/Controllers/Karyawan.php` — **DEPRECATED (`fat_legacy` Shim):** Menduplikasi method endpoint `KaryawanService` dan masih menginstansiasi `new \App\Repositories\UserManagement\PenggunaRepository()` langsung pada `changeGroup()` & `chnagePermission()` (L72–82); gunakan `App\Controllers\Web\UserManagement\KaryawanController` / `App\Controllers\Api\UserManagement\KaryawanController`.
- `app/Controllers/Profil.php` — **DEPRECATED (Legacy Shim):** Menduplikasi logika `Web\UserManagement\ProfilController` via `ProfilService`; gunakan `App\Controllers\Web\UserManagement\ProfilController` / `App\Controllers\Api\UserManagement\ProfilController`.
- `app/Services/PenggunaService.php` — **DEPRECATED (Thin Zero-Downtime Shim):** Mewarisi `App\Services\UserManagement\PenggunaService`; gunakan class modular `App\Services\UserManagement\PenggunaService`.
- `app/Repositories/KaryawanRepository.php` — **DEPRECATED (Thin Zero-Downtime Shim):** Mewarisi `App\Repositories\UserManagement\KaryawanRepository`; gunakan class modular `App\Repositories\UserManagement\KaryawanRepository`.

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

### Audit Verifikasi Migrasi (2026-11-22)
- **Status Audit Ledger:** `COMPLETED (Catatan Minor)` (`REFACTORED`, Score `100/100`).
- **Layer & Routes Terverifikasi Bersih:**
  - Ketiga Web controller (`Web\UserManagement\PenggunaController`, `KaryawanController`, `ProfilController`) tipis (24–40 baris), bebas `setJSON`/`respond`, dan tidak memanggil Repository langsung.
  - Ketiga API controller (`Api\UserManagement\PenggunaController`, `KaryawanController`, `ProfilController`) seluruhnya mewarisi `BaseApiController`, bebas `view()`/`redirect()`, dan tidak menyusun HTML langsung di controller.
  - Seluruh 37 rute di `app/Config/Routes.php` (L81–82, L360–412) adalah rute modular eksplisit (`legacy_route_count: 0`, `autoroutes_count: 0`).
  - Ketiga Service (`PenggunaService`, `KaryawanService`, `ProfilService`) menggunakan `$this->db = \Config\Database::connect()` khusus untuk transaksi atomik (`transStart`/`transComplete`/`transStatus`/`transRollback`), tanpa `->table()`, `->query()`, raw SQL, maupun mutasi tabel modul lain.
- **Temuan Minor:**
  - **File non-modular:** `app/Repositories/DivisiRepository.php` (41 baris, `namespace App\Repositories`) masih berada di root `app/Repositories/` di luar subfolder `UserManagement/` dan saat ini tidak direferensikan di `app/` maupun `tests/`.
  - **Shim `fat_legacy`:** `app/Controllers/Pengguna.php` (88 baris) dan `app/Controllers/Karyawan.php` (83 baris) berklasifikasi `fat_legacy` karena menduplikasi isi method alih-alih mendelegasikan secara tipis, serta memanggil `new \App\Repositories\UserManagement\PenggunaRepository()` langsung pada `changeGroup()`/`chnagePermission()` (L77–87 & L72–82). `app/Controllers/Profil.php` (46 baris) juga menduplikasi `Web\UserManagement\ProfilController`, sedangkan `app/Services/PenggunaService.php` & `app/Repositories/KaryawanRepository.php` sudah menjadi thin shim murni.
  - **HTML DataTables di Service tanpa `?format=raw`:** `PenggunaService::getDatatable()` (L48–59) dan `KaryawanService::getDatatable()` (L48–58) menyematkan string HTML (`<div class="btn-group">`, `<button>`, `<span class="badge">`) ke payload JSON untuk `/api/pengguna/getall` & `/api/karyawan/getall` tanpa dukungan parameter `?format=raw`.

## Change Log
- `2026-11-22` [`AUDIT`] — Audit verifikasi migrasi modular (`COMPLETED (Catatan Minor)`, Score `100/100`): melengkapi seksi `Key Functions`, `Deprecated & Legacy Shims`, dan `Change Log`; mencatat `app/Repositories/DivisiRepository.php` non-modular/unused, 2 shim `fat_legacy` (`Pengguna.php` L77–87 & `Karyawan.php` L72–82 memanggil `PenggunaRepository` langsung), serta `PenggunaService::getDatatable()` & `KaryawanService::getDatatable()` yang masih menyematkan HTML DataTables tanpa `?format=raw`.

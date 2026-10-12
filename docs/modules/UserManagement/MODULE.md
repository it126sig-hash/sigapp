# Module: UserManagement

**Status:** `REFACTORED`  
**Readiness Score:** `80/100`  
**Last Synced:** `2026-10-12 14:38:40` @ `be98758`  

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
| `POST` | `/getAkases` | `Explicit` | `AksesProyek::getAkses` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `/aksesproyek` | `Explicit` | `AksesProyek::index` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `/profil` | `Explicit` | `Profil::index` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/profil/update` | `Explicit` | `Profil::update` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `image` | `Explicit` | `ProfileSignatureController::image` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `ANY` | `/pengguna/index` | `AutoRoute` | `Pengguna::index` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/pengguna/setuser` | `AutoRoute` | `Pengguna::setuser` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/pengguna/getall` | `AutoRoute` | `Pengguna::getAll` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/pengguna/changegroup` | `AutoRoute` | `Pengguna::changeGroup` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/pengguna/chnagepermission` | `AutoRoute` | `Pengguna::chnagePermission` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/pengguna/getone` | `AutoRoute` | `Pengguna::getOne` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/pengguna/edit_user` | `AutoRoute` | `Pengguna::edit_user` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/pengguna/add` | `AutoRoute` | `Pengguna::add` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/pengguna/remove` | `AutoRoute` | `Pengguna::remove` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/karyawan/index` | `AutoRoute` | `Karyawan::index` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/karyawan/getall` | `AutoRoute` | `Karyawan::getAll` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/karyawan/getone` | `AutoRoute` | `Karyawan::getOne` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/karyawan/add` | `AutoRoute` | `Karyawan::add` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/karyawan/changegroup` | `AutoRoute` | `Karyawan::changeGroup` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/karyawan/chnagepermission` | `AutoRoute` | `Karyawan::chnagePermission` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/karyawan/edit` | `AutoRoute` | `Karyawan::edit` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/karyawan/remove` | `AutoRoute` | `Karyawan::remove` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/akses_proyek/getall` | `AutoRoute` | `AksesProyek::getAll` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/akses_proyek/getone` | `AutoRoute` | `AksesProyek::getOne` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/akses_proyek/edit` | `AutoRoute` | `AksesProyek::edit` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/profil/currentuserrow` | `AutoRoute` | `Profil::currentUserRow` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/profil/profilephotourl` | `AutoRoute` | `Profil::profilePhotoUrl` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |

### B. Public PHP Services (Backend Contract Antar-Modul)
#### `PenggunaService.php`
- `setuser()`
- `getAll()`
- `changeGroup($userId, $groupId)`
- `chnagePermission($userId, $permissionId)`
- `getOne()`
- `edit_user()`
- `add()`
- `remove()`

## File Inventory
### Controllers (4)
- `app/Controllers/Pengguna.php` (371 baris, 9 public methods)
- `app/Controllers/Karyawan.php` (229 baris, 8 public methods)
- `app/Controllers/AksesProyek.php` (153 baris, 5 public methods)
- `app/Controllers/Profil.php` (197 baris, 4 public methods)

### Services (1)
- `app/Services/PenggunaService.php` (174 baris)

### Repositories (2)
- `app/Repositories/KaryawanRepository.php` (236 baris)
- `app/Repositories/DivisiRepository.php` (236 baris)

### Models (5)
- `app/Models/KaryawanModel.php`
- `app/Models/DivisiModel.php`
- `app/Models/LevelModel.php`
- `app/Models/UserModel.php`
- `app/Models/ProfilePerusahaanModel.php`

### Views (1 folder)
- `app/Views/user` (4 file)

### JavaScript (0 file)

## Readiness Checklist
- [x] Has Service Layer
- [x] Has Repository Layer
- [ ] Web & API Controllers Separated
- [x] Explicit Routes Defined
- [x] MODULE.md Initialized

## Dead Code Candidates
_Jalankan `php spark module:deadcode UserManagement` untuk mengaudit dead code kandidat._

## Notes
- Modul diinisialisasi melalui tooling otomatis.

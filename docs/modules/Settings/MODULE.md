# Module: Settings

**Status:** `REFACTORED`  
**Readiness Score:** `100/100`  
**Last Synced:** `2026-11-24 05:31:50` @ `7609063`  

## Purpose
Konfigurasi global sistem, manajemen menu dinamis, akses role menu, dan pewarnaan siteplan.

## Out of scope
- Hal-hal di luar tanggung jawab domain Settings.

## Invariants
- Modul lain tidak boleh memanipulasi tabel pemilik secara langsung.
- Perubahan finansial/status harus diverifikasi di level Service.

## Database Tables
### Owner Table(s)
- `menus` *(akses tulis eksklusif hanya untuk modul Settings)*
- `menu_roles` *(akses tulis eksklusif hanya untuk modul Settings)*
- `menu_user_access` *(akses tulis eksklusif hanya untuk modul Settings)*
- `config_shape` *(akses tulis eksklusif hanya untuk modul Settings)*

### Shared Tables (Dibaca dari modul lain)
- `users`

## Contracts
### A. Public HTTP Endpoints & Routes Inventory
| Method | URI / Pattern | Tipe | Handler | Status Migrasi | Target API / Catatan |
|---|---|---|---|---|---|
| `GET` | `/menu-setting` | `Explicit` | `MenuSettingController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/menu-setting` | `Explicit` | `\App\Controllers\Web\Settings\MenuSettingController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/menu-setting/meta` | `Explicit` | `MenuSettingController::meta` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/menu-setting/menu/save` | `Explicit` | `MenuSettingController::menuSave` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/menu-setting/menu/toggle` | `Explicit` | `MenuSettingController::menuToggle` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/menu-setting/access/group/save` | `Explicit` | `MenuSettingController::groupSave` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/menu-setting/access/user/save` | `Explicit` | `MenuSettingController::userSave` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/menu-setting/siteplan-menu/save` | `Explicit` | `MenuSettingController::siteplanMenuSave` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/menu-setting/siteplan-menu/toggle` | `Explicit` | `MenuSettingController::siteplanMenuToggle` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/menu-setting/menu/list` | `Explicit` | `\App\Controllers\Api\Settings\MenuSettingController::menuList` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/menu-setting/menu/get` | `Explicit` | `\App\Controllers\Api\Settings\MenuSettingController::menuGet` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/menu-setting/menu/save` | `Explicit` | `\App\Controllers\Api\Settings\MenuSettingController::menuSave` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/menu-setting/menu/toggle` | `Explicit` | `\App\Controllers\Api\Settings\MenuSettingController::menuToggle` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/menu-setting/access/group` | `Explicit` | `\App\Controllers\Api\Settings\MenuSettingController::groupAccess` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/menu-setting/access/group/save` | `Explicit` | `\App\Controllers\Api\Settings\MenuSettingController::groupSave` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/menu-setting/access/user` | `Explicit` | `\App\Controllers\Api\Settings\MenuSettingController::userAccess` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/menu-setting/access/user/save` | `Explicit` | `\App\Controllers\Api\Settings\MenuSettingController::userSave` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/menu-setting/siteplan-menu/list` | `Explicit` | `\App\Controllers\Api\Settings\MenuSettingController::siteplanMenuList` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/menu-setting/siteplan-menu/get` | `Explicit` | `\App\Controllers\Api\Settings\MenuSettingController::siteplanMenuGet` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/menu-setting/siteplan-menu/save` | `Explicit` | `\App\Controllers\Api\Settings\MenuSettingController::siteplanMenuSave` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/menu-setting/siteplan-menu/toggle` | `Explicit` | `\App\Controllers\Api\Settings\MenuSettingController::siteplanMenuToggle` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/setting-data` | `Explicit` | `SettingDataController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/setting-data` | `Explicit` | `\App\Controllers\Web\Settings\SettingDataController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/setting-data/meta` | `Explicit` | `SettingDataController::meta` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/setting-data/save/(:segment)` | `Explicit` | `SettingDataController::save/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/setting-data/list/(:segment)` | `Explicit` | `\App\Controllers\Api\Settings\SettingDataController::list/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/setting-data/get/(:segment)` | `Explicit` | `\App\Controllers\Api\Settings\SettingDataController::get/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/setting-data/save/(:segment)` | `Explicit` | `\App\Controllers\Api\Settings\SettingDataController::save/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/setting-data/delete/(:segment)` | `Explicit` | `\App\Controllers\Api\Settings\SettingDataController::delete/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/configcolor` | `Explicit` | `\App\Controllers\Web\Settings\PengaturanWarnaController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/pengaturan-warna/meta` | `Explicit` | `PengaturanWarnaController::meta` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/pengaturan-warna/add` | `Explicit` | `PengaturanWarnaController::add` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/pengaturan-warna/edit` | `Explicit` | `PengaturanWarnaController::edit` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/pengaturanWarna/getAll` | `Explicit` | `\App\Controllers\Api\Settings\PengaturanWarnaController::getAll` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/pengaturanWarna/getOne` | `Explicit` | `\App\Controllers\Api\Settings\PengaturanWarnaController::getOne` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/pengaturanWarna/add` | `Explicit` | `\App\Controllers\Api\Settings\PengaturanWarnaController::add` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/pengaturanWarna/edit` | `Explicit` | `\App\Controllers\Api\Settings\PengaturanWarnaController::edit` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/pengaturanWarna/remove` | `Explicit` | `\App\Controllers\Api\Settings\PengaturanWarnaController::remove` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/pengaturan_warna/getall` | `Explicit` | `\App\Controllers\Api\Settings\PengaturanWarnaController::getAll` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/pengaturan_warna/getone` | `Explicit` | `\App\Controllers\Api\Settings\PengaturanWarnaController::getOne` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/pengaturan_warna/add` | `Explicit` | `\App\Controllers\Api\Settings\PengaturanWarnaController::add` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/pengaturan_warna/edit` | `Explicit` | `\App\Controllers\Api\Settings\PengaturanWarnaController::edit` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/pengaturan_warna/remove` | `Explicit` | `\App\Controllers\Api\Settings\PengaturanWarnaController::remove` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |

### B. Public PHP Services (Backend Contract Antar-Modul)
#### `MenuAccessService.php`
- `getEffectiveMenuIds(int $userId)`
- `getMenuTreeForUser(int $userId)`
- `canAccessUrl(int $userId, string $url)`
- `syncGroupMenus(int $groupId, array $menuIds)`
- `syncUserOverrides(int $userId, array $allowIds, array $denyIds)`
- `getGroupMenuIds(array $groupIds)`
- `getUserOverrides(int $userId)`
- `getActiveMenus()`
- `getUserGroupIds(int $userId)`
#### `SiteplanMenuService.php`
- `renderForRole(int $roleId)`
- `getFlatItems(bool $activeOnly = false)`
- `findItem(int $id)`
- `saveItem(array $fields, int $id = 0)`
- `toggleItem(int $id)`
- `syncRoles(int $itemId, array $groupIds)`
- `getItemRoleIds(int $itemId)`
- `groupOptions()`
- `groupLabel(int $groupId)`
- `getMenusForSpa(int $roleId)`
- `getActionItemsForList(int $roleId)`
#### `SettingDataService.php`
- `getAllConfigs()`
- `getMetaTables()`
- `getConfig(string $key)`
- `list(string $key, array $params = [])`
- `get(string $key, int $id, bool $raw = false)`
- `save(string $key, array $postData, int $userId, bool $raw = false)`
- `delete(string $key, int $id, int $userId, bool $raw = false)`
#### `PengaturanWarnaService.php`
- `getAll(array $params = [])`
- `getOne(string $configName, bool $raw = false)`
- `add(array $postData, ?string $userName = null, bool $raw = false)`
- `edit(array $postData, ?string $userName = null, bool $raw = false)`
- `remove(string $configName, bool $raw = false)`
- `normalizeStrokeWidth($value)`
#### `MenuSettingService.php`
- `getIndexData()`
- `getMenuList(array $params = [])`
- `getMenu(int $id, bool $raw = false)`
- `saveMenu(array $postData, bool $raw = false)`
- `toggleMenu(int $id, bool $raw = false)`
- `getSiteplanMenuList(array $params = [])`
- `getSiteplanMenu(int $id, bool $raw = false)`
- `saveSiteplanMenu(array $postData, bool $raw = false)`
- `toggleSiteplanMenu(int $id, bool $raw = false)`
- `makeSlug(string $name)`

## File Inventory
### Controllers (6)
- `app/Controllers/Web/Settings/MenuSettingController.php` (28 baris, 1 public methods: `index()`)
- `app/Controllers/Api/Settings/MenuSettingController.php` (367 baris, 13 public methods: `meta()`, `menuList()`, `menuGet()`, `menuSave()`, `menuToggle()`, `groupAccess()`, `groupSave()`, `userAccess()`, `userSave()`, `siteplanMenuList()`, `siteplanMenuGet()`, `siteplanMenuSave()`, `siteplanMenuToggle()`)
- `app/Controllers/Web/Settings/SettingDataController.php` (29 baris, 1 public methods: `index()`)
- `app/Controllers/Api/Settings/SettingDataController.php` (150 baris, 5 public methods: `meta()`, `list(string $key)`, `get(string $key)`, `save(string $key)`, `delete($key = null)`)
- `app/Controllers/Web/Settings/PengaturanWarnaController.php` (20 baris, 1 public methods: `index()`)
- `app/Controllers/Api/Settings/PengaturanWarnaController.php` (146 baris, 6 public methods: `meta()`, `getAll()`, `getOne()`, `add()`, `edit($id = null)`, `remove()`)

### Services (5)
- `app/Services/Settings/MenuAccessService.php` (266 baris, 9 public methods)
- `app/Services/Settings/SiteplanMenuService.php` (455 baris, 11 public methods)
- `app/Services/Settings/SettingDataService.php` (349 baris, 7 public methods)
- `app/Services/Settings/PengaturanWarnaService.php` (374 baris, 6 public methods)
- `app/Services/Settings/MenuSettingService.php` (533 baris, 10 public methods)

### Repositories (3)
- `app/Repositories/Settings/MenuRepository.php` (150 baris, 11 public methods: `getGroups()`, `getUsers()`, `getParentOptions()`, `getFlatMenus(bool $activeOnly = false)`, `findMenuById(int $id)`, `insertMenu(array $data)`, `updateMenu(int $id, array $data)`, `groupExists(int $groupId)`, `userExists(int $userId)`, `isDescendant(int $menuId, int $parentId)`, `checkDuplicateSiteplanItemKey(string $itemKey, int $excludeId = 0)`)
- `app/Repositories/Settings/SettingDataRepository.php` (191 baris, 9 public methods: `getAllConfigs()`, `getConfig(string $key)`, `listRows(array $config)`, `findActiveRow(array $config, int $id)`, `nextManualId(array $config)`, `getFieldNames(string $table)`, `insertRow(string $table, array $fields)`, `updateRow(string $table, string $primaryKey, int $id, array $fields)`, `softDeleteRow(array $config, int $id, int $userId)`)
- `app/Repositories/Settings/PengaturanWarnaRepository.php` (43 baris, 5 public methods: `getAll()`, `getByConfigName(string $configName)`, `insert(array $fields)`, `update(string $configName, array $fields)`, `delete(string $configName)`)

### Models (5)
- `app/Models/MenuModel.php`
- `app/Models/MenuRoleModel.php`
- `app/Models/MenuUserAccessModel.php`
- `app/Models/ConfigModel.php`
- `app/Models/PengaturanWarnaModel.php`

### Views (3 folder)
- `app/Views/setting` (2 file: `data.php`, `fee_sales_access.php`)
- `app/Views/menu` (1 file: `setting.php`)
- `app/Views/master` (9 file: `aksesproyek.php`, `cluster.php`, `configcolor.php`, `jalan.php`, `karyawan.php`, `listbank.php`, `pengaturanWarna.php`, `proyek.php`, `tipe.php`)

### JavaScript (0 file)

### Zero-Downtime Shims (6 file)
- `app/Controllers/MenuSetting.php`
- `app/Controllers/SettingData.php`
- `app/Controllers/PengaturanWarna.php`
- `app/Controllers/Config.php`
- `app/Controllers/ConfigColor.php`
- `app/Services/MenuAccessService.php`

## Key Functions
- **Service (`app/Services/Settings/MenuAccessService.php`):** `getEffectiveMenuIds(int $userId)`, `getMenuTreeForUser(int $userId)`, `canAccessUrl(int $userId, string $url)`, `syncGroupMenus(int $groupId, array $menuIds)`, `syncUserOverrides(int $userId, array $allowIds, array $denyIds)`
- **Service (`app/Services/Settings/SiteplanMenuService.php`):** `renderForRole(int $roleId)`, `getFlatItems(bool $activeOnly = false)`, `findItem(int $id)`, `saveItem(array $fields, int $id = 0)`, `toggleItem(int $id)`, `getMenusForSpa(int $roleId)` *(JSON murni untuk SPA via `formatSpaMenuItem()`)*, `getActionItemsForList(int $roleId)`
- **Service (`app/Services/Settings/SettingDataService.php`):** `getAllConfigs()`, `getMetaTables()`, `getConfig(string $key)`, `list(string $key, array $params = [])`, `get(string $key, int $id, bool $raw = false)`, `save(string $key, array $postData, int $userId, bool $raw = false)`, `delete(string $key, int $id, int $userId, bool $raw = false)`
- **Service (`app/Services/Settings/PengaturanWarnaService.php`):** `getAll(array $params = [])`, `getOne(string $configName, bool $raw = false)`, `add(array $postData, ?string $userName = null, bool $raw = false)`, `edit(array $postData, ?string $userName = null, bool $raw = false)` *(mengecualikan `config_name` dari `$updateFields` agar PK tidak ikut di-update)*, `remove(string $configName, bool $raw = false)`
- **Service (`app/Services/Settings/MenuSettingService.php`):** `getIndexData()`, `getMenuList(array $params = [])`, `getMenu(int $id, bool $raw = false)`, `saveMenu(array $postData, bool $raw = false)`, `toggleMenu(int $id, bool $raw = false)`, `getSiteplanMenuList(array $params = [])`, `getSiteplanMenu(int $id, bool $raw = false)`, `saveSiteplanMenu(array $postData, bool $raw = false)`, `toggleSiteplanMenu(int $id, bool $raw = false)`
- **API Controllers (`app/Controllers/Api/Settings/*` via `BaseApiController`):** `readRequestData()`, `isRawFormat()` (`?format=raw`), `ensureMenuAccess()` (`403` JSON guard per menu URL)
- **Repository (`app/Repositories/Settings/MenuRepository.php`):** `getGroups()`, `getUsers()`, `getParentOptions()`, `getFlatMenus(bool $activeOnly = false)`, `findMenuById(int $id)`
- **Repository (`app/Repositories/Settings/SettingDataRepository.php`):** `getAllConfigs()`, `getConfig(string $key)`, `listRows(array $config)`, `findActiveRow(array $config, int $id)`, `nextManualId(array $config)`
- **Repository (`app/Repositories/Settings/PengaturanWarnaRepository.php`):** `getAll()`, `getByConfigName(string $configName)`, `insert(array $fields)` *(memakai `$this->model->insert($fields, false)` untuk PK non-auto-increment)*, `update(string $configName, array $fields)`, `delete(string $configName)`
- **Model (`app/Models/PengaturanWarnaModel.php`):** `$table = 'config_shape'`, `$primaryKey = 'config_name'`, `$useAutoIncrement = false`, `$allowedFields` menyertakan `'config_name'`

## Deprecated & Legacy Shims
- `app/Controllers/MenuSetting.php` — **DEPRECATED (Zero-Downtime Shim)**: Dipertahankan untuk kompatibilitas mundur; gunakan class modular di namespace `Settings`.
- `app/Controllers/SettingData.php` — **DEPRECATED (Zero-Downtime Shim)**: Dipertahankan untuk kompatibilitas mundur; gunakan class modular di namespace `Settings`.
- `app/Controllers/PengaturanWarna.php` — **DEPRECATED (Zero-Downtime Shim)**: Dipertahankan untuk kompatibilitas mundur; gunakan class modular di namespace `Settings`.
- `app/Controllers/Config.php` — **DEPRECATED (Zero-Downtime Shim)**: Dipertahankan untuk kompatibilitas mundur; gunakan class modular di namespace `Settings`.
- `app/Controllers/ConfigColor.php` — **DEPRECATED (Zero-Downtime Shim)**: Dipertahankan untuk kompatibilitas mundur; gunakan class modular di namespace `Settings`.
- `app/Services/MenuAccessService.php` — **DEPRECATED (Zero-Downtime Shim)**: Dipertahankan untuk kompatibilitas mundur; gunakan class modular di namespace `Settings`.

## Readiness Checklist
- [x] Has Service Layer
- [x] Has Repository Layer
- [x] Web & API Controllers Separated
- [x] Explicit Routes Defined
- [x] Zero Controller Layer Violations
- [x] MODULE.md Initialized

## Dead Code Candidates
_Jalankan `php spark module:deadcode Settings` untuk mengaudit dead code kandidat._

## Notes
- Modul diinisialisasi melalui tooling otomatis.
- Seluruh endpoint `/api/menu-setting/*`, `/api/setting-data/*`, dan `/api/pengaturan-warna/*` mendukung mode `?format=raw` untuk mengembalikan envelope JSON SPA murni (`{ success, messages, data }`) beserta pagination (`items` + `meta`), HTTP status REST (`201`/`403`/`404`/`422` dengan map `errors`), dan guard akses `ensureMenuAccess()` tanpa memutus rute `POST` legacy DataTables.

### Audit Verifikasi Migrasi (2026-11-22)
- **Status Ledger:** `COMPLETED (Catatan Minor)` (Status scanner: `REFACTORED`, Readiness Score: `100/100`, 43 rute modular eksplisit, 0 file non-modular, seluruh API controller mewarisi `BaseApiController` dan mendukung `?format=raw`, seluruh Web controller thin).
- **Verifikasi False Positive Shim:** `app/Controllers/MenuSetting.php` (88 baris, ditandai `fat_legacy` oleh heuristik jumlah baris pre-scan) terverifikasi murni thin delegator shim yang meneruskan 13 method ke `Web\Settings\MenuSettingController` dan `Api\Settings\MenuSettingController` tanpa logic maupun query DB.
- **Temuan Layering & Dokumentasi:**
  1. `app/Services/Settings/MenuAccessService.php` masih memanggil `$this->db->table()` langsung pada tabel `menus`, `menu_roles`, `menu_user_access`, dan `auth_groups_users` (`L81-86`, `L99-109`, `L119-127`, `L137-141`, `L148-152`, `L166-172`, `L177-181`, `L188-194`, `L204-208`, `L220`) di luar manajemen transaksi.
  2. `app/Services/Settings/SiteplanMenuService.php` masih memanggil `$this->db->table()` / `tableExists()` langsung pada tabel `siteplan_menu_items`, `siteplan_menu_roles`, dan `auth_groups` (`L94-104`, `L113`, `L121`, `L126`, `L138-143`, `L151-161`, `L170-174`, `L189-193`, `L368-376`, `L450`).
  3. `app/Controllers/Api/Settings/MenuSettingController.php` menginjeksi dan memanggil `MenuRepository` secara langsung (`L16`, `L22`, `L118`, `L132`, `L156`, `L197`, `L212`, `L238`) melewati layer Service.
  4. Seksi `## Database Tables` belum mencantumkan `siteplan_menu_items` dan `siteplan_menu_roles` pada `Owner Table(s)` (sehingga pre-scan menandai `owners: ["UNKNOWN"]`) maupun `auth_groups` dan `auth_groups_users` pada `Shared Tables`.

## Change Log
- `2026-11-24` [`API-BRIDGE`] — Penambahan group `5 => 'Legal'` pada `GROUP_LABELS` dan `5 => 'legal_menu'` pada `GROUP_CONTAINER_IDS` di `app/Services/Settings/SiteplanMenuService.php` agar `GET /api/siteplan/meta` mengembalikan menu aksi Legal (`open_legal()`) untuk user Legal & Admin secara Zero-Downtime, disertai sinkronisasi `php spark module:sync Settings`.
- `2026-11-22` [`AUDIT`] — Audit verifikasi migrasi modular: status ledger `COMPLETED (Catatan Minor)` (Score 100%); temuan query `$this->db->table()` di `MenuAccessService` & `SiteplanMenuService`, akses langsung `MenuRepository` di `Api\Settings\MenuSettingController`, serta tabel `siteplan_menu_items`/`siteplan_menu_roles` belum tercatat di `MODULE.md` (shim `MenuSetting.php` terverifikasi thin delegator).
- `2026-11-14` [`API-BRIDGE`] — Penambahan `SiteplanMenuService::getMenusForSpa(int $roleId)` dan helper privat `formatSpaMenuItem()` untuk menyusun daftar menu aksi departemen `siteplan_menus` (`role_id`, `default_group_id`, `groups`, `context_items`) dalam format JSON murni yang dikonsumsi oleh `SiteplanService::getMeta()` (`GET /api/siteplan/meta`) tanpa memengaruhi `renderForRole()` pada view legacy.
- `2026-11-13` [`BUGFIX`] — Perbaikan CRUD primary key non-auto-increment `config_name` pada tabel `config_shape`: `PengaturanWarnaModel` menambahkan `protected $useAutoIncrement = false;` dan `'config_name'` ke `$allowedFields`; `PengaturanWarnaRepository::insert(array $fields)` memakai `$this->model->insert($fields, false)`; `PengaturanWarnaService::edit()` mengecualikan `config_name` dari `$updateFields` agar primary key tidak ikut di-update. Verifikasi SPA & non-regresi web legacy `/configcolor` PASS.
- `2026-11-13` [`API-BRIDGE`] — Penyesuaian kontrak API SPA `RAW_JSON_READY` Fase 1: Penambahan helper `readRequestData()`, `isRawFormat()`, dan `ensureMenuAccess()` di `BaseApiController`; endpoint baru `GET /api/menu-setting/meta`, `GET /api/setting-data/meta` (7 tabel), dan `GET /api/pengaturan-warna/meta`; dukungan verb `GET`/`DELETE` dan mode `?format=raw` (pagination `items`+`meta`, payload JSON body, `201`/`403`/`404`/`422` errors map, unknown `$key` `404` JSON, serta alias `config_name`/`configName`) dengan mempertahankan 100% rute `POST` legacy.
- `2026-11-12` [`SECURITY-AUTH`] — Peningkatan keamanan autentikasi SPA: Integrasi arsitektur Hybrid HttpOnly Cookie `sigapp_spa_token` untuk Web SPA (0 token di `localStorage`, `access_token: null`), Device Binding (`DeviceSessionService::verifyDeviceBinding()`) berdasarkan `User-Agent` (`browser` & `platform`), dan verifikasi sesi aman untuk navigasi menu/pengaturan.
- `2026-11-12` [`SYNC`] — Sinkronisasi status & inventori modul melalui `module:sync`.

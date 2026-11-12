# Module: Settings

**Status:** `REFACTORED`  
**Readiness Score:** `100/100`  
**Last Synced:** `2026-11-12 01:53:09` @ `d86bec6`  

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
| `POST` | `/api/menu-setting/menu/list` | `Explicit` | `MenuSettingController::menuList` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/menu-setting/menu/get` | `Explicit` | `MenuSettingController::menuGet` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/menu-setting/menu/save` | `Explicit` | `MenuSettingController::menuSave` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/menu-setting/menu/toggle` | `Explicit` | `MenuSettingController::menuToggle` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/menu-setting/access/group` | `Explicit` | `MenuSettingController::groupAccess` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/menu-setting/access/group/save` | `Explicit` | `MenuSettingController::groupSave` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/menu-setting/access/user` | `Explicit` | `MenuSettingController::userAccess` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/menu-setting/access/user/save` | `Explicit` | `MenuSettingController::userSave` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/menu-setting/siteplan-menu/list` | `Explicit` | `MenuSettingController::siteplanMenuList` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/menu-setting/siteplan-menu/get` | `Explicit` | `MenuSettingController::siteplanMenuGet` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
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
| `POST` | `/api/setting-data/list/(:segment)` | `Explicit` | `SettingDataController::list/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/setting-data/get/(:segment)` | `Explicit` | `SettingDataController::get/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/setting-data/save/(:segment)` | `Explicit` | `SettingDataController::save/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/setting-data/delete/(:segment)` | `Explicit` | `SettingDataController::delete/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/setting-data/list/(:segment)` | `Explicit` | `\App\Controllers\Api\Settings\SettingDataController::list/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/setting-data/get/(:segment)` | `Explicit` | `\App\Controllers\Api\Settings\SettingDataController::get/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/setting-data/save/(:segment)` | `Explicit` | `\App\Controllers\Api\Settings\SettingDataController::save/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/setting-data/delete/(:segment)` | `Explicit` | `\App\Controllers\Api\Settings\SettingDataController::delete/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/configcolor` | `Explicit` | `\App\Controllers\Web\Settings\PengaturanWarnaController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/pengaturan-warna/all` | `Explicit` | `PengaturanWarnaController::getAll` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/pengaturan-warna/getall` | `Explicit` | `PengaturanWarnaController::getAll` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/pengaturan-warna/getone` | `Explicit` | `PengaturanWarnaController::getOne` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/pengaturan-warna/add` | `Explicit` | `PengaturanWarnaController::add` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/pengaturan-warna/edit` | `Explicit` | `PengaturanWarnaController::edit` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/pengaturan-warna/remove` | `Explicit` | `PengaturanWarnaController::remove` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
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
- `getActionItemsForList(int $roleId)`
#### `SettingDataService.php`
- `getAllConfigs()`
- `getConfig(string $key)`
- `list(string $key)`
- `get(string $key, int $id)`
- `save(string $key, array $postData, int $userId)`
- `delete(string $key, int $id, int $userId)`
#### `PengaturanWarnaService.php`
- `getAll()`
- `getOne(string $configName)`
- `add(array $postData, ?string $userName = null)`
- `edit(array $postData, ?string $userName = null)`
- `remove(string $configName)`
- `normalizeStrokeWidth($value)`
#### `MenuSettingService.php`
- `getIndexData()`
- `getMenuList()`
- `getMenu(int $id)`
- `saveMenu(array $postData)`
- `toggleMenu(int $id)`
- `getSiteplanMenuList()`
- `getSiteplanMenu(int $id)`
- `saveSiteplanMenu(array $postData)`
- `toggleSiteplanMenu(int $id)`
- `makeSlug(string $name)`

## File Inventory
### Controllers (6)
- `app/Controllers/Web/Settings/MenuSettingController.php` (28 baris, 1 public methods: `index()`)
- `app/Controllers/Api/Settings/MenuSettingController.php` (180 baris, 12 public methods: `menuList()`, `menuGet()`, `menuSave()`, `menuToggle()`, `groupAccess()`, `groupSave()`, `userAccess()`, `userSave()`, `siteplanMenuList()`, `siteplanMenuGet()`, `siteplanMenuSave()`, `siteplanMenuToggle()`)
- `app/Controllers/Web/Settings/SettingDataController.php` (29 baris, 1 public methods: `index()`)
- `app/Controllers/Api/Settings/SettingDataController.php` (56 baris, 4 public methods: `list(string $key)`, `get(string $key)`, `save(string $key)`, `delete($key = null)`)
- `app/Controllers/Web/Settings/PengaturanWarnaController.php` (20 baris, 1 public methods: `index()`)
- `app/Controllers/Api/Settings/PengaturanWarnaController.php` (62 baris, 5 public methods: `getAll()`, `getOne()`, `add()`, `edit($id = null)`, `remove()`)

### Services (5)
- `app/Services/Settings/MenuAccessService.php` (266 baris, 9 public methods)
- `app/Services/Settings/SiteplanMenuService.php` (373 baris, 10 public methods)
- `app/Services/Settings/SettingDataService.php` (195 baris, 6 public methods)
- `app/Services/Settings/PengaturanWarnaService.php` (205 baris, 6 public methods)
- `app/Services/Settings/MenuSettingService.php` (324 baris, 10 public methods)

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
- **Service (`app/Services/Settings/SiteplanMenuService.php`):** `renderForRole(int $roleId)`, `getFlatItems(bool $activeOnly = false)`, `findItem(int $id)`, `saveItem(array $fields, int $id = 0)`, `toggleItem(int $id)`
- **Service (`app/Services/Settings/SettingDataService.php`):** `getAllConfigs()`, `getConfig(string $key)`, `list(string $key)`, `get(string $key, int $id)`, `save(string $key, array $postData, int $userId)`
- **Service (`app/Services/Settings/PengaturanWarnaService.php`):** `getAll()`, `getOne(string $configName)`, `add(array $postData, ?string $userName = null)`, `edit(array $postData, ?string $userName = null)`, `remove(string $configName)`
- **Service (`app/Services/Settings/MenuSettingService.php`):** `getIndexData()`, `getMenuList()`, `getMenu(int $id)`, `saveMenu(array $postData)`, `toggleMenu(int $id)`
- **Repository (`app/Repositories/Settings/MenuRepository.php`):** `getGroups()`, `getUsers()`, `getParentOptions()`, `getFlatMenus(bool $activeOnly = false)`, `findMenuById(int $id)`
- **Repository (`app/Repositories/Settings/SettingDataRepository.php`):** `getAllConfigs()`, `getConfig(string $key)`, `listRows(array $config)`, `findActiveRow(array $config, int $id)`, `nextManualId(array $config)`
- **Repository (`app/Repositories/Settings/PengaturanWarnaRepository.php`):** `getAll()`, `getByConfigName(string $configName)`, `insert(array $fields)`, `update(string $configName, array $fields)`, `delete(string $configName)`

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

## Change Log
- `2026-11-12` [`SECURITY-AUTH`] — Peningkatan keamanan autentikasi SPA: Integrasi arsitektur Hybrid HttpOnly Cookie `sigapp_spa_token` untuk Web SPA (0 token di `localStorage`, `access_token: null`), Device Binding (`DeviceSessionService::verifyDeviceBinding()`) berdasarkan `User-Agent` (`browser` & `platform`), dan verifikasi sesi aman untuk navigasi menu/pengaturan.
- `2026-11-12` [`SYNC`] — Sinkronisasi status & inventori modul melalui `module:sync`.

# Module: Settings

**Status:** `REFACTORED`  
**Readiness Score:** `100/100`  
**Last Synced:** `2026-10-12 20:21:12` @ `6e70679`  

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
| `GET` | `menu-setting` | `Explicit` | `MenuSettingController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `menu/list` | `Explicit` | `MenuSettingController::menuList` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `menu/get` | `Explicit` | `MenuSettingController::menuGet` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `menu/save` | `Explicit` | `MenuSettingController::menuSave` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `menu/toggle` | `Explicit` | `MenuSettingController::menuToggle` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `access/group` | `Explicit` | `MenuSettingController::groupAccess` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `access/group/save` | `Explicit` | `MenuSettingController::groupSave` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `access/user` | `Explicit` | `MenuSettingController::userAccess` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `access/user/save` | `Explicit` | `MenuSettingController::userSave` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `siteplan-menu/list` | `Explicit` | `MenuSettingController::siteplanMenuList` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `siteplan-menu/get` | `Explicit` | `MenuSettingController::siteplanMenuGet` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `siteplan-menu/save` | `Explicit` | `MenuSettingController::siteplanMenuSave` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `siteplan-menu/toggle` | `Explicit` | `MenuSettingController::siteplanMenuToggle` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/` | `Explicit` | `\App\Controllers\Web\Settings\MenuSettingController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `menu/list` | `Explicit` | `\App\Controllers\Api\Settings\MenuSettingController::menuList` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `menu/get` | `Explicit` | `\App\Controllers\Api\Settings\MenuSettingController::menuGet` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `menu/save` | `Explicit` | `\App\Controllers\Api\Settings\MenuSettingController::menuSave` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `menu/toggle` | `Explicit` | `\App\Controllers\Api\Settings\MenuSettingController::menuToggle` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `access/group` | `Explicit` | `\App\Controllers\Api\Settings\MenuSettingController::groupAccess` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `access/group/save` | `Explicit` | `\App\Controllers\Api\Settings\MenuSettingController::groupSave` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `access/user` | `Explicit` | `\App\Controllers\Api\Settings\MenuSettingController::userAccess` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `access/user/save` | `Explicit` | `\App\Controllers\Api\Settings\MenuSettingController::userSave` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `siteplan-menu/list` | `Explicit` | `\App\Controllers\Api\Settings\MenuSettingController::siteplanMenuList` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `siteplan-menu/get` | `Explicit` | `\App\Controllers\Api\Settings\MenuSettingController::siteplanMenuGet` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `siteplan-menu/save` | `Explicit` | `\App\Controllers\Api\Settings\MenuSettingController::siteplanMenuSave` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `siteplan-menu/toggle` | `Explicit` | `\App\Controllers\Api\Settings\MenuSettingController::siteplanMenuToggle` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `menu-setting` | `Explicit` | `MenuSettingController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `menu/list` | `Explicit` | `MenuSettingController::menuList` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `menu/get` | `Explicit` | `MenuSettingController::menuGet` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `menu/save` | `Explicit` | `MenuSettingController::menuSave` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `menu/toggle` | `Explicit` | `MenuSettingController::menuToggle` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `access/group` | `Explicit` | `MenuSettingController::groupAccess` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `access/group/save` | `Explicit` | `MenuSettingController::groupSave` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `access/user` | `Explicit` | `MenuSettingController::userAccess` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `access/user/save` | `Explicit` | `MenuSettingController::userSave` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `siteplan-menu/list` | `Explicit` | `MenuSettingController::siteplanMenuList` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `siteplan-menu/get` | `Explicit` | `MenuSettingController::siteplanMenuGet` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `siteplan-menu/save` | `Explicit` | `MenuSettingController::siteplanMenuSave` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `siteplan-menu/toggle` | `Explicit` | `MenuSettingController::siteplanMenuToggle` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/` | `Explicit` | `\App\Controllers\Web\Settings\MenuSettingController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `menu/list` | `Explicit` | `\App\Controllers\Api\Settings\MenuSettingController::menuList` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `menu/get` | `Explicit` | `\App\Controllers\Api\Settings\MenuSettingController::menuGet` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `menu/save` | `Explicit` | `\App\Controllers\Api\Settings\MenuSettingController::menuSave` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `menu/toggle` | `Explicit` | `\App\Controllers\Api\Settings\MenuSettingController::menuToggle` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `access/group` | `Explicit` | `\App\Controllers\Api\Settings\MenuSettingController::groupAccess` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `access/group/save` | `Explicit` | `\App\Controllers\Api\Settings\MenuSettingController::groupSave` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `access/user` | `Explicit` | `\App\Controllers\Api\Settings\MenuSettingController::userAccess` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `access/user/save` | `Explicit` | `\App\Controllers\Api\Settings\MenuSettingController::userSave` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `siteplan-menu/list` | `Explicit` | `\App\Controllers\Api\Settings\MenuSettingController::siteplanMenuList` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `siteplan-menu/get` | `Explicit` | `\App\Controllers\Api\Settings\MenuSettingController::siteplanMenuGet` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `siteplan-menu/save` | `Explicit` | `\App\Controllers\Api\Settings\MenuSettingController::siteplanMenuSave` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `siteplan-menu/toggle` | `Explicit` | `\App\Controllers\Api\Settings\MenuSettingController::siteplanMenuToggle` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `setting-data` | `Explicit` | `SettingDataController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `list/(:segment)` | `Explicit` | `SettingDataController::list/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `get/(:segment)` | `Explicit` | `SettingDataController::get/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `save/(:segment)` | `Explicit` | `SettingDataController::save/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `delete/(:segment)` | `Explicit` | `SettingDataController::delete/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/` | `Explicit` | `\App\Controllers\Web\Settings\SettingDataController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `list/(:segment)` | `Explicit` | `\App\Controllers\Api\Settings\SettingDataController::list/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `get/(:segment)` | `Explicit` | `\App\Controllers\Api\Settings\SettingDataController::get/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `save/(:segment)` | `Explicit` | `\App\Controllers\Api\Settings\SettingDataController::save/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `delete/(:segment)` | `Explicit` | `\App\Controllers\Api\Settings\SettingDataController::delete/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `setting-data` | `Explicit` | `SettingDataController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `list/(:segment)` | `Explicit` | `SettingDataController::list/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `get/(:segment)` | `Explicit` | `SettingDataController::get/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `save/(:segment)` | `Explicit` | `SettingDataController::save/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `delete/(:segment)` | `Explicit` | `SettingDataController::delete/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/` | `Explicit` | `\App\Controllers\Web\Settings\SettingDataController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `list/(:segment)` | `Explicit` | `\App\Controllers\Api\Settings\SettingDataController::list/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `get/(:segment)` | `Explicit` | `\App\Controllers\Api\Settings\SettingDataController::get/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `save/(:segment)` | `Explicit` | `\App\Controllers\Api\Settings\SettingDataController::save/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `delete/(:segment)` | `Explicit` | `\App\Controllers\Api\Settings\SettingDataController::delete/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/configcolor` | `Explicit` | `\App\Controllers\Web\Settings\PengaturanWarnaController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `all` | `Explicit` | `PengaturanWarnaController::getAll` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `getall` | `Explicit` | `PengaturanWarnaController::getAll` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `getone` | `Explicit` | `PengaturanWarnaController::getOne` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `add` | `Explicit` | `PengaturanWarnaController::add` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `edit` | `Explicit` | `PengaturanWarnaController::edit` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `remove` | `Explicit` | `PengaturanWarnaController::remove` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `getAll` | `Explicit` | `\App\Controllers\Api\Settings\PengaturanWarnaController::getAll` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `getOne` | `Explicit` | `\App\Controllers\Api\Settings\PengaturanWarnaController::getOne` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `add` | `Explicit` | `\App\Controllers\Api\Settings\PengaturanWarnaController::add` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `edit` | `Explicit` | `\App\Controllers\Api\Settings\PengaturanWarnaController::edit` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `remove` | `Explicit` | `\App\Controllers\Api\Settings\PengaturanWarnaController::remove` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `getall` | `Explicit` | `\App\Controllers\Api\Settings\PengaturanWarnaController::getAll` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `getone` | `Explicit` | `\App\Controllers\Api\Settings\PengaturanWarnaController::getOne` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `add` | `Explicit` | `\App\Controllers\Api\Settings\PengaturanWarnaController::add` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `edit` | `Explicit` | `\App\Controllers\Api\Settings\PengaturanWarnaController::edit` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `remove` | `Explicit` | `\App\Controllers\Api\Settings\PengaturanWarnaController::remove` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/configcolor` | `Explicit` | `\App\Controllers\Web\Settings\PengaturanWarnaController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `all` | `Explicit` | `PengaturanWarnaController::getAll` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `getall` | `Explicit` | `PengaturanWarnaController::getAll` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `getone` | `Explicit` | `PengaturanWarnaController::getOne` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `add` | `Explicit` | `PengaturanWarnaController::add` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `edit` | `Explicit` | `PengaturanWarnaController::edit` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `remove` | `Explicit` | `PengaturanWarnaController::remove` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `getAll` | `Explicit` | `\App\Controllers\Api\Settings\PengaturanWarnaController::getAll` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `getOne` | `Explicit` | `\App\Controllers\Api\Settings\PengaturanWarnaController::getOne` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `add` | `Explicit` | `\App\Controllers\Api\Settings\PengaturanWarnaController::add` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `edit` | `Explicit` | `\App\Controllers\Api\Settings\PengaturanWarnaController::edit` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `remove` | `Explicit` | `\App\Controllers\Api\Settings\PengaturanWarnaController::remove` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `getall` | `Explicit` | `\App\Controllers\Api\Settings\PengaturanWarnaController::getAll` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `getone` | `Explicit` | `\App\Controllers\Api\Settings\PengaturanWarnaController::getOne` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `add` | `Explicit` | `\App\Controllers\Api\Settings\PengaturanWarnaController::add` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `edit` | `Explicit` | `\App\Controllers\Api\Settings\PengaturanWarnaController::edit` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `remove` | `Explicit` | `\App\Controllers\Api\Settings\PengaturanWarnaController::remove` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |

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
- `insertUserOverride(int $userId, int $menuId, string $accessType)`
- `filterValidMenuIds(array $menuIds)`
- `expandWithDescendants(array $menuIds)`
- `includeAncestors(array $menuIds, array $menus, array $denyIds)`
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
- `renderAllGroupMenus()`
- `getItemsForAccessGroup(int $groupId)`
- `renderGroupMenu(int $groupId, array $items, bool $hidden)`
- `renderItem($item)`
- `tablesReady()`
#### `SettingDataService.php`
- `getAllConfigs()`
- `getConfig(string $key)`
- `list(string $key)`
- `get(string $key, int $id)`
- `save(string $key, array $postData, int $userId)`
- `delete(string $key, int $id, int $userId)`
- `collectFields(array $config, array $postData)`
- `validationRules(array $config)`
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
- `app/Controllers/Web/Settings/MenuSettingController.php` (28 baris, 1 public methods)
- `app/Controllers/Api/Settings/MenuSettingController.php` (180 baris, 12 public methods)
- `app/Controllers/Web/Settings/SettingDataController.php` (29 baris, 1 public methods)
- `app/Controllers/Api/Settings/SettingDataController.php` (55 baris, 4 public methods)
- `app/Controllers/Web/Settings/PengaturanWarnaController.php` (20 baris, 1 public methods)
- `app/Controllers/Api/Settings/PengaturanWarnaController.php` (62 baris, 5 public methods)

### Services (5)
- `app/Services/Settings/MenuAccessService.php` (266 baris)
- `app/Services/Settings/SiteplanMenuService.php` (373 baris)
- `app/Services/Settings/SettingDataService.php` (195 baris)
- `app/Services/Settings/PengaturanWarnaService.php` (205 baris)
- `app/Services/Settings/MenuSettingService.php` (324 baris)

### Repositories (3)
- `app/Repositories/Settings/MenuRepository.php` (150 baris)
- `app/Repositories/Settings/SettingDataRepository.php` (191 baris)
- `app/Repositories/Settings/PengaturanWarnaRepository.php` (43 baris)

### Models (5)
- `app/Models/MenuModel.php`
- `app/Models/MenuRoleModel.php`
- `app/Models/MenuUserAccessModel.php`
- `app/Models/ConfigModel.php`
- `app/Models/PengaturanWarnaModel.php`

### Views (3 folder)
- `app/Views/setting` (1 file)
- `app/Views/menu` (1 file)
- `app/Views/master` (9 file)

### JavaScript (0 file)

## Readiness Checklist
- [x] Has Service Layer
- [x] Has Repository Layer
- [x] Web & API Controllers Separated
- [x] Explicit Routes Defined
- [x] MODULE.md Initialized

## Dead Code Candidates
_Jalankan `php spark module:deadcode Settings` untuk mengaudit dead code kandidat._

## Notes
- Modul diinisialisasi melalui tooling otomatis.

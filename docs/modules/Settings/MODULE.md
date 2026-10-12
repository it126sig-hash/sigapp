# Module: Settings

**Status:** `MAPPED`  
**Readiness Score:** `60/100`  
**Last Synced:** `2026-10-12 14:38:40` @ `be98758`  

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
| `GET` | `/` | `Explicit` | `MenuSetting::index` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `menu/list` | `Explicit` | `MenuSetting::menuList` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `menu/get` | `Explicit` | `MenuSetting::menuGet` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `menu/save` | `Explicit` | `MenuSetting::menuSave` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `menu/toggle` | `Explicit` | `MenuSetting::menuToggle` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `access/group` | `Explicit` | `MenuSetting::groupAccess` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `access/group/save` | `Explicit` | `MenuSetting::groupSave` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `access/user` | `Explicit` | `MenuSetting::userAccess` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `access/user/save` | `Explicit` | `MenuSetting::userSave` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `siteplan-menu/list` | `Explicit` | `MenuSetting::siteplanMenuList` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `siteplan-menu/get` | `Explicit` | `MenuSetting::siteplanMenuGet` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `siteplan-menu/save` | `Explicit` | `MenuSetting::siteplanMenuSave` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `siteplan-menu/toggle` | `Explicit` | `MenuSetting::siteplanMenuToggle` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `/` | `Explicit` | `SettingData::index` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `list/(:segment)` | `Explicit` | `SettingData::list/$1` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `get/(:segment)` | `Explicit` | `SettingData::get/$1` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `save/(:segment)` | `Explicit` | `SettingData::save/$1` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `delete/(:segment)` | `Explicit` | `SettingData::delete/$1` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `/configcolor` | `Explicit` | `PengaturanWarna::index` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `ANY` | `/menu_setting/guardadmin` | `AutoRoute` | `MenuSetting::guardAdmin` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/menu_setting/jsonerror` | `AutoRoute` | `MenuSetting::jsonError` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/menu_setting/getgroups` | `AutoRoute` | `MenuSetting::getGroups` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/menu_setting/getusers` | `AutoRoute` | `MenuSetting::getUsers` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/menu_setting/getparentoptions` | `AutoRoute` | `MenuSetting::getParentOptions` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/menu_setting/getflatmenus` | `AutoRoute` | `MenuSetting::getFlatMenus` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/menu_setting/groupexists` | `AutoRoute` | `MenuSetting::groupExists` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/menu_setting/userexists` | `AutoRoute` | `MenuSetting::userExists` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/menu_setting/isdescendant` | `AutoRoute` | `MenuSetting::isDescendant` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/menu_setting/makeslug` | `AutoRoute` | `MenuSetting::makeSlug` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/setting_data/collectfields` | `AutoRoute` | `SettingData::collectFields` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/setting_data/validationrules` | `AutoRoute` | `SettingData::validationRules` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/setting_data/findactiverow` | `AutoRoute` | `SettingData::findActiveRow` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/setting_data/nextmanualid` | `AutoRoute` | `SettingData::nextManualId` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/setting_data/getconfig` | `AutoRoute` | `SettingData::getConfig` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/setting_data/guardadmin` | `AutoRoute` | `SettingData::guardAdmin` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/setting_data/jsonerror` | `AutoRoute` | `SettingData::jsonError` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/setting_data/jsonsuccess` | `AutoRoute` | `SettingData::jsonSuccess` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/setting_data/tableconfigs` | `AutoRoute` | `SettingData::tableConfigs` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/pengaturan_warna/getall` | `AutoRoute` | `PengaturanWarna::getAll` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/pengaturan_warna/getone` | `AutoRoute` | `PengaturanWarna::getOne` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/pengaturan_warna/add` | `AutoRoute` | `PengaturanWarna::add` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/pengaturan_warna/edit` | `AutoRoute` | `PengaturanWarna::edit` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/pengaturan_warna/remove` | `AutoRoute` | `PengaturanWarna::remove` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/pengaturan_warna/normalizestrokewidth` | `AutoRoute` | `PengaturanWarna::normalizeStrokeWidth` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/config_color/index` | `AutoRoute` | `ConfigColor::index` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/config/index` | `AutoRoute` | `Config::index` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/config/get_shape` | `AutoRoute` | `Config::get_shape` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |

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

## File Inventory
### Controllers (5)
- `app/Controllers/MenuSetting.php` (545 baris, 23 public methods)
- `app/Controllers/SettingData.php` (363 baris, 14 public methods)
- `app/Controllers/PengaturanWarna.php` (237 baris, 7 public methods)
- `app/Controllers/ConfigColor.php` (28 baris, 1 public methods)
- `app/Controllers/Config.php` (15 baris, 2 public methods)

### Services (2)
- `app/Services/MenuAccessService.php` (266 baris)
- `app/Services/SiteplanMenuService.php` (373 baris)

### Repositories (0)

### Models (5)
- `app/Models/MenuModel.php`
- `app/Models/MenuRoleModel.php`
- `app/Models/MenuUserAccessModel.php`
- `app/Models/ConfigModel.php`
- `app/Models/PengaturanWarnaModel.php`

### Views (2 folder)
- `app/Views/setting` (1 file)
- `app/Views/menu` (1 file)

### JavaScript (0 file)

## Readiness Checklist
- [x] Has Service Layer
- [ ] Has Repository Layer
- [ ] Web & API Controllers Separated
- [x] Explicit Routes Defined
- [x] MODULE.md Initialized

## Dead Code Candidates
_Jalankan `php spark module:deadcode Settings` untuk mengaudit dead code kandidat._

## Notes
- Modul diinisialisasi melalui tooling otomatis.

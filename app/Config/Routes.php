<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->setAutoRoute(true);

// Override Myth/Auth routes so successful login records a device session.
$routes->get('login', '\\App\\Controllers\\Web\\DeviceAuthController::login', ['as' => 'login']);
$routes->post('login', '\\App\\Controllers\\Web\\DeviceAuthController::attemptLogin');
$routes->get('logout', '\\App\\Controllers\\Web\\DeviceAuthController::logout', ['filter' => []]);

$routes->post('api/auth/login', 'Api\Auth\AuthController::login');
$routes->group('api/auth', ['namespace' => 'App\Controllers\Api\Auth', 'filter' => 'login'], static function ($routes) {
    $routes->get('me', 'AuthController::me');
    $routes->post('logout', 'AuthController::logout');
});

$routes->group('api/auth/devices', ['namespace' => 'App\\Controllers\\Api', 'filter' => 'login'], static function ($routes): void {
    $routes->get('', 'AuthDeviceController::index');
    $routes->post('(:num)/revoke', 'AuthDeviceController::revoke/$1', ['filter' => 'csrf']);
    $routes->post('revoke-others', 'AuthDeviceController::revokeOthers', ['filter' => 'csrf']);
});

$routes->group('api/admin/users/(:num)/devices', ['namespace' => 'App\\Controllers\\Api', 'filter' => 'login'], static function ($routes): void {
    $routes->get('', 'AdminAuthDeviceController::index/$1');
    $routes->post('(:num)/revoke', 'AdminAuthDeviceController::revoke/$1/$2', ['filter' => 'csrf']);
    $routes->post('revoke-all', 'AdminAuthDeviceController::revokeAll/$1', ['filter' => 'csrf']);
});

// ── Modul Legal (Public Web Routes) ──
$routes->group('', ['namespace' => 'App\Controllers\Web\Legal'], function ($routes) {
    $routes->get('privacy-policy', 'LegalPageController::privacyPolicy');
    $routes->get('toc', 'LegalPageController::termsOfService');
});

$routes->get('dashboard', 'Home::dashboard');
$routes->get('/', 'Home::dashboard');
$routes->get('landing', '\App\Controllers\Web\LandingController::index');

//poskon
$routes->get('/list-kavling', '\App\Controllers\Web\Mkdt\PosisiKonsumenController::index');
$routes->post('/list-kavling/ambil', '\App\Controllers\Api\Mkdt\PosisiKonsumenController::getDataTables');
$routes->post('/list-kavling/akad/ambil', '\App\Controllers\Api\Mkdt\PosisiKonsumenController::getDataTablesAkad');
$routes->post('/list-kavling/batal/ambil', '\App\Controllers\Api\Mkdt\PosisiKonsumenController::getDataTablesBatal');
$routes->get('/list-kavling/(:segment)', '\App\Controllers\Web\Mkdt\PosisiKonsumenController::index/$1');


$routes->addRedirect('/list-batal', '/list-kavling/batal');
$routes->get('/list-stock', '\App\Controllers\Web\Mkdt\StockController::index', ['filter' => 'login']);
// $routes->get('/list-tagihan', 'Keuangan::list_tagihan');

// ── Modul Legal (Web Routes) ──
$routes->group('', ['namespace' => 'App\Controllers\Web\Legal', 'filter' => 'login'], function ($routes) {
    $routes->get('legalitas', 'LegalController::list_legalitas');
});

// ── Modul Notifikasi (Web Routes) ──
$routes->group('notif', ['namespace' => 'App\Controllers\Web\Notifikasi'], function ($routes) {
    $routes->get('open/(:num)', 'NotifikasiController::open/$1', ['filter' => 'login']);
    $routes->get('icon/(:num)', 'NotifikasiController::icon/$1');
});

// ── Modul Notifikasi: Legacy & AutoRoute Shims (Zero-Downtime) ──
$routes->group('', ['filter' => 'login'], function ($routes) {
    $routes->get('/getnotif', '\App\Controllers\Api\Notifikasi\NotifikasiController::getNotif');
    $routes->get('/loadnotif', '\App\Controllers\Api\Notifikasi\NotifikasiController::loadNotif');
    $routes->get('/notif/summary', '\App\Controllers\Api\Notifikasi\NotifikasiController::getSummary');
    $routes->get('/notif/center', '\App\Controllers\Api\Notifikasi\NotifikasiController::getCenter');
    $routes->post('/notif/snooze', '\App\Controllers\Api\Notifikasi\NotifikasiController::snooze');
    $routes->post('/notif/mark-as-read/(:num)', '\App\Controllers\Api\Notifikasi\NotifikasiController::markAsRead/$1');
    $routes->post('/notif/mark-all-as-read', '\App\Controllers\Api\Notifikasi\NotifikasiController::markAllAsRead');
    $routes->match(['get', 'post'], '/notif/tambah_notif', '\App\Controllers\Api\Notifikasi\NotifikasiController::tambah_notif');
    $routes->match(['get', 'post'], '/notif/getactivity', '\App\Controllers\Api\Notifikasi\NotifikasiController::getActivityRoute');
});
$routes->get('/loadaktivitas', 'Home::loadAktivitas');

$routes->get('/profil', '\App\Controllers\Web\UserManagement\ProfilController::index');
$routes->post('/profil/update', '\App\Controllers\Web\UserManagement\ProfilController::update');

// ── Modul BPB (Web Routes) ──
$routes->group('bpb', ['namespace' => 'App\Controllers\Web\Bpb'], function ($routes) {
    $routes->get('', 'BpbController::index', ['filter' => 'login']);
    $routes->get('shared/(:segment)', 'BpbController::shared/$1', ['filter' => 'login']);
    $routes->get('(:num)/pdf', 'BpbController::pdf/$1', ['filter' => 'login']);
    $routes->get('signature/(:num)', 'BpbController::signature/$1', ['filter' => 'login']);
    $routes->get('verify/(:segment)', 'BpbController::verify/$1', ['filter' => 'throttle:20,60']);
});

// ── Modul BPB (API Routes) ──
$routes->group('api/bpb', ['namespace' => 'App\Controllers\Api\Bpb', 'filter' => 'login'], function ($routes) {
    $routes->match(['get', 'post'], 'list', 'BpbController::list');
    $routes->get('options', 'BpbController::options');
    $routes->get('detail/(:num)', 'BpbController::detail/$1');
    $routes->get('shared/(:segment)', 'BpbController::shared/$1');
    $routes->post('draft', 'BpbController::draft');
    $routes->post('submit', 'BpbController::submit', ['filter' => 'throttle:10,60']);
    $routes->post('(:num)/update', 'BpbController::update/$1', ['filter' => 'throttle:10,60']);
    $routes->post('(:num)/sign', 'BpbController::sign/$1', ['filter' => 'throttle:5,60']);
    $routes->post('(:num)/reject', 'BpbController::reject/$1', ['filter' => 'throttle:10,60']);
    $routes->post('(:num)/cancel', 'BpbController::cancel/$1');
    $routes->post('(:num)/status', 'BpbController::status/$1', ['filter' => 'throttle:10,60']);
});

$routes->group('api/profile/signature', ['namespace' => 'App\Controllers\Api\Bpb', 'filter' => 'login'], function ($routes) {
    $routes->get('', 'ProfileSignatureController::show');
    $routes->get('image', 'ProfileSignatureController::image');
    $routes->put('', 'ProfileSignatureController::save', ['filter' => 'throttle:5,60']);
    $routes->delete('', 'ProfileSignatureController::delete', ['filter' => 'throttle:5,60']);
});
$routes->group('google-calendar', ['namespace' => 'App\Controllers\Web\Notifikasi', 'filter' => 'login'], function ($routes) {
    $routes->get('connect', 'GoogleCalendarController::connect');
    $routes->get('callback', 'GoogleCalendarController::callback');
    $routes->post('disconnect', 'GoogleCalendarController::disconnect');
});

$routes->get('/configcolor', '\App\Controllers\Web\Settings\PengaturanWarnaController::index');

$routes->post("/get-dashboard", 'Home::getDashboard');
$routes->post("/home/getMenuItemsJson", 'Home::getMenuItemsJson');

$routes->post("/keuangan/jatuhtempo/get", '\App\Controllers\Api\Keuangan\BankKprDisbursementController::getJatuhTempo', ['filter' => 'login']);


$routes->post("/getAkases", 'AksesProyek::getAkses');
$routes->get("/aksesproyek", '\App\Controllers\Web\Proyek\AksesProyekController::index');

// ── Modul Proyek (Web Routes) ──
$routes->group('', ['namespace' => 'App\Controllers\Web\Proyek', 'filter' => 'login'], function ($routes) {
    $routes->get('proyek', 'ProyekController::index');
    $routes->get('cluster', 'ClusterController::index');
    $routes->get('tipe', 'TipeController::index');
    $routes->get('jalan', 'JalanController::index');
    $routes->get('aksesproyek', 'AksesProyekController::index');
});

// ── Modul Proyek (API Routes) ──
$routes->group('api/proyek', ['namespace' => 'App\Controllers\Api\Proyek', 'filter' => 'login'], function ($routes) {
    $routes->match(['get', 'post'], 'all', 'ProyekController::getAll');
    $routes->match(['get', 'post'], 'getall', 'ProyekController::getAll');
    $routes->match(['get', 'post'], 'getone', 'ProyekController::getOne');
    $routes->match(['get', 'post'], 'getone/(:num)', 'ProyekController::getOne/$1');
    $routes->post('add', 'ProyekController::add');
    $routes->post('edit', 'ProyekController::edit');
    $routes->post('remove', 'ProyekController::remove');
    $routes->match(['get', 'post'], 'siteplan-list', 'ProyekController::getSiteplanList');
    $routes->match(['get', 'post'], 'siteplan-list/(:num)', 'ProyekController::getSiteplanList/$1');
    $routes->match(['get', 'post'], 'accessible', 'ProyekController::getAccessible');
    $routes->post('set-active', 'ProyekController::setActive');
});

$routes->group('api/cluster', ['namespace' => 'App\Controllers\Api\Proyek', 'filter' => 'login'], function ($routes) {
    $routes->match(['get', 'post'], 'datatable', 'ClusterController::getDataTable');
    $routes->match(['get', 'post'], 'getdatatable', 'ClusterController::getDataTable');
    $routes->match(['get', 'post'], 'all', 'ClusterController::getAll');
    $routes->match(['get', 'post'], 'getall', 'ClusterController::getAll');
    $routes->match(['get', 'post'], 'getone', 'ClusterController::getOne');
    $routes->match(['get', 'post'], 'getone/(:num)', 'ClusterController::getOne/$1');
    $routes->post('add', 'ClusterController::add');
    $routes->post('edit', 'ClusterController::edit');
    $routes->post('remove', 'ClusterController::remove');
});

$routes->group('api/tipe', ['namespace' => 'App\Controllers\Api\Proyek', 'filter' => 'login'], function ($routes) {
    $routes->match(['get', 'post'], 'datatables', 'TipeController::getDataTables');
    $routes->match(['get', 'post'], 'getdatatables', 'TipeController::getDataTables');
    $routes->match(['get', 'post'], 'all', 'TipeController::getAll');
    $routes->match(['get', 'post'], 'getall', 'TipeController::getAll');
    $routes->match(['get', 'post'], 'getone', 'TipeController::getOne');
    $routes->match(['get', 'post'], 'getone/(:num)', 'TipeController::getOne/$1');
    $routes->post('add', 'TipeController::add');
    $routes->post('edit', 'TipeController::edit');
    $routes->post('remove', 'TipeController::remove');
    $routes->match(['get', 'post'], 'list-kavling', 'TipeController::getListKavling');
    $routes->match(['get', 'post'], 'list-kavling/(:num)', 'TipeController::getListKavling/$1');
    $routes->match(['get', 'post'], 'getlistkavling', 'TipeController::getListKavling');
});

$routes->group('api/jalan', ['namespace' => 'App\Controllers\Api\Proyek', 'filter' => 'login'], function ($routes) {
    $routes->match(['get', 'post'], 'datatable', 'JalanController::getDatatable');
    $routes->match(['get', 'post'], 'getdatatable', 'JalanController::getDatatable');
    $routes->match(['get', 'post'], 'all', 'JalanController::getAll');
    $routes->match(['get', 'post'], 'getall', 'JalanController::getAll');
    $routes->match(['get', 'post'], 'getone', 'JalanController::getOne');
    $routes->match(['get', 'post'], 'getone/(:num)', 'JalanController::getOne/$1');
    $routes->post('add', 'JalanController::add');
    $routes->post('edit', 'JalanController::edit');
    $routes->post('remove', 'JalanController::remove');
});

$routes->group('api/aksesproyek', ['namespace' => 'App\Controllers\Api\Proyek', 'filter' => 'login'], function ($routes) {
    $routes->get('meta', 'AksesProyekController::meta');
    $routes->match(['get', 'post'], 'all', 'AksesProyekController::getAll');
    $routes->match(['get', 'post'], 'getone', 'AksesProyekController::getOne');
    $routes->match(['get', 'post'], 'getone/(:num)', 'AksesProyekController::getOne/$1');
    $routes->match(['get', 'post'], 'search-users', 'AksesProyekController::getAkses');
    $routes->post('edit', 'AksesProyekController::edit');
});

// ── Modul Siteplan (API Routes) ──
$routes->group('api/siteplan', ['namespace' => 'App\Controllers\Api\Siteplan', 'filter' => 'login'], function ($routes) {
    $routes->get('meta', 'SiteplanController::meta');
    $routes->get('kavling', 'SiteplanController::kavling');
    $routes->post('kavling', 'SiteplanController::kavling');
    $routes->get('proyek/(:num)', 'SiteplanController::proyek/$1');
    $routes->get('proyek', 'SiteplanController::proyek');
    $routes->get('config', 'SiteplanController::config');
    $routes->get('others', 'SiteplanController::others');
    $routes->post('others', 'SiteplanController::others');
    $routes->get('detail/(:num)', 'SiteplanController::detail/$1');
    $routes->post('detail', 'SiteplanController::detail');
    $routes->get('kalendar/events', 'KalendarController::getEvents');
    $routes->get('kalendar/meta', 'KalendarController::meta');
});

$routes->group('api/kalendar', ['namespace' => 'App\Controllers\Api\Siteplan', 'filter' => 'login'], function ($routes) {
    $routes->get('events', 'KalendarController::getEvents');
    $routes->get('meta', 'KalendarController::meta');
});



// $routes->get("bank", 'ListBank::index');
$routes->get('list_bank/index', '\App\Controllers\Web\Keuangan\BankController::index', ['filter' => 'login']);
$routes->get('list_bank', '\App\Controllers\Web\Keuangan\BankController::index', ['filter' => 'login']);
$routes->get('listbank', '\App\Controllers\Web\Keuangan\BankController::index', ['filter' => 'login']);

$routes->group('api/bank', ['namespace' => 'App\Controllers\Api\Keuangan', 'filter' => 'login'], function($routes) {
    $routes->get('meta', 'BankController::meta');
    $routes->get('ambil', 'BankController::ambil');
    $routes->get('list', 'BankController::list');
    $routes->post('ambilsatu', 'BankController::ambilSatu');
    $routes->post('simpan', 'BankController::simpan');
    $routes->post('hapus', 'BankController::hapus');
});

// ── Modul Notifikasi (API Routes Modern Full JSON) ──
$routes->group('api/notif', ['namespace' => 'App\Controllers\Api\Notifikasi', 'filter' => 'login'], function ($routes) {
    $routes->get('summary', 'NotifikasiController::summary');
    $routes->get('center', 'NotifikasiController::center');
    $routes->post('snooze', 'NotifikasiController::snooze');
    $routes->get('load', 'NotifikasiController::load');
    $routes->post('mark-as-read/(:num)', 'NotifikasiController::markAsRead/$1');
    $routes->post('mark-all-as-read', 'NotifikasiController::markAllAsRead');
    $routes->get('list', 'NotifikasiController::list');

    $routes->get('push/status', 'NotifPushController::status');
    $routes->post('push/subscribe', 'NotifPushController::subscribe');
    $routes->post('push/unsubscribe', 'NotifPushController::unsubscribe');
    $routes->post('push/test', 'NotifPushController::test', ['filter' => 'throttle:5,60']);

    $routes->get('preferences', 'NotificationPreferenceController::index');
    $routes->post('preferences', 'NotificationPreferenceController::save');
    $routes->post('preferences/reset', 'NotificationPreferenceController::reset');
});


// ── Modul Settings (Web Routes) ──
$routes->group('', ['namespace' => 'App\Controllers\Web\Settings', 'filter' => 'login'], function ($routes) {
    $routes->get('menu-setting', 'MenuSettingController::index');
    $routes->get('setting-data', 'SettingDataController::index');
    $routes->get('setting-fee-sales', '\App\Controllers\Web\SalesExternal\FeeSalesSettingController::index');
});

// Setting Akses Fee Sales API
$routes->group('api/setting-fee-sales', ['namespace' => 'App\Controllers\Api\SalesExternal', 'filter' => 'login'], function ($routes) {
    $routes->post('list', 'FeeSalesSettingController::listPermissions');
    $routes->post('save', 'FeeSalesSettingController::savePermissions');
});

// ── Modul Settings (API Routes Modern) ──
$routes->group('api/menu-setting', ['namespace' => 'App\Controllers\Api\Settings', 'filter' => 'login'], function ($routes) {
    $routes->get('meta', 'MenuSettingController::meta');
    $routes->match(['get', 'post'], 'menu/list', 'MenuSettingController::menuList');
    $routes->match(['get', 'post'], 'menu/get', 'MenuSettingController::menuGet');
    $routes->post('menu/save', 'MenuSettingController::menuSave');
    $routes->post('menu/toggle', 'MenuSettingController::menuToggle');
    $routes->match(['get', 'post'], 'access/group', 'MenuSettingController::groupAccess');
    $routes->post('access/group/save', 'MenuSettingController::groupSave');
    $routes->match(['get', 'post'], 'access/user', 'MenuSettingController::userAccess');
    $routes->post('access/user/save', 'MenuSettingController::userSave');
    $routes->match(['get', 'post'], 'siteplan-menu/list', 'MenuSettingController::siteplanMenuList');
    $routes->match(['get', 'post'], 'siteplan-menu/get', 'MenuSettingController::siteplanMenuGet');
    $routes->post('siteplan-menu/save', 'MenuSettingController::siteplanMenuSave');
    $routes->post('siteplan-menu/toggle', 'MenuSettingController::siteplanMenuToggle');
});

$routes->group('api/setting-data', ['namespace' => 'App\Controllers\Api\Settings', 'filter' => 'login'], function ($routes) {
    $routes->get('meta', 'SettingDataController::meta');
    $routes->match(['get', 'post'], 'list/(:segment)', 'SettingDataController::list/$1');
    $routes->match(['get', 'post'], 'get/(:segment)', 'SettingDataController::get/$1');
    $routes->post('save/(:segment)', 'SettingDataController::save/$1');
    $routes->match(['delete', 'post'], 'delete/(:segment)', 'SettingDataController::delete/$1');
});

$routes->group('api/pengaturan-warna', ['namespace' => 'App\Controllers\Api\Settings', 'filter' => 'login'], function ($routes) {
    $routes->get('meta', 'PengaturanWarnaController::meta');
    $routes->match(['get', 'post'], 'all', 'PengaturanWarnaController::getAll');
    $routes->match(['get', 'post'], 'getall', 'PengaturanWarnaController::getAll');
    $routes->match(['get', 'post'], 'getone', 'PengaturanWarnaController::getOne');
    $routes->post('add', 'PengaturanWarnaController::add');
    $routes->post('edit', 'PengaturanWarnaController::edit');
    $routes->match(['delete', 'post'], 'remove', 'PengaturanWarnaController::remove');
});

// ── Modul Settings (Legacy Routes Zero-Downtime Aliases) ──
$routes->group('menu-setting', ['filter' => 'login'], function ($routes) {
    $routes->get('/', '\App\Controllers\Web\Settings\MenuSettingController::index');
    $routes->post('menu/list', '\App\Controllers\Api\Settings\MenuSettingController::menuList');
    $routes->post('menu/get', '\App\Controllers\Api\Settings\MenuSettingController::menuGet');
    $routes->post('menu/save', '\App\Controllers\Api\Settings\MenuSettingController::menuSave');
    $routes->post('menu/toggle', '\App\Controllers\Api\Settings\MenuSettingController::menuToggle');
    $routes->post('access/group', '\App\Controllers\Api\Settings\MenuSettingController::groupAccess');
    $routes->post('access/group/save', '\App\Controllers\Api\Settings\MenuSettingController::groupSave');
    $routes->post('access/user', '\App\Controllers\Api\Settings\MenuSettingController::userAccess');
    $routes->post('access/user/save', '\App\Controllers\Api\Settings\MenuSettingController::userSave');
    $routes->post('siteplan-menu/list', '\App\Controllers\Api\Settings\MenuSettingController::siteplanMenuList');
    $routes->post('siteplan-menu/get', '\App\Controllers\Api\Settings\MenuSettingController::siteplanMenuGet');
    $routes->post('siteplan-menu/save', '\App\Controllers\Api\Settings\MenuSettingController::siteplanMenuSave');
    $routes->post('siteplan-menu/toggle', '\App\Controllers\Api\Settings\MenuSettingController::siteplanMenuToggle');
});

$routes->group('setting-data', ['filter' => 'login'], function ($routes) {
    $routes->get('/', '\App\Controllers\Web\Settings\SettingDataController::index');
    $routes->post('list/(:segment)', '\App\Controllers\Api\Settings\SettingDataController::list/$1');
    $routes->post('get/(:segment)', '\App\Controllers\Api\Settings\SettingDataController::get/$1');
    $routes->post('save/(:segment)', '\App\Controllers\Api\Settings\SettingDataController::save/$1');
    $routes->post('delete/(:segment)', '\App\Controllers\Api\Settings\SettingDataController::delete/$1');
});

$routes->group('pengaturanWarna', ['filter' => 'login'], function ($routes) {
    $routes->post('getAll', '\App\Controllers\Api\Settings\PengaturanWarnaController::getAll');
    $routes->post('getOne', '\App\Controllers\Api\Settings\PengaturanWarnaController::getOne');
    $routes->post('add', '\App\Controllers\Api\Settings\PengaturanWarnaController::add');
    $routes->post('edit', '\App\Controllers\Api\Settings\PengaturanWarnaController::edit');
    $routes->post('remove', '\App\Controllers\Api\Settings\PengaturanWarnaController::remove');
});

$routes->group('pengaturan_warna', ['filter' => 'login'], function ($routes) {
    $routes->post('getall', '\App\Controllers\Api\Settings\PengaturanWarnaController::getAll');
    $routes->post('getone', '\App\Controllers\Api\Settings\PengaturanWarnaController::getOne');
    $routes->post('add', '\App\Controllers\Api\Settings\PengaturanWarnaController::add');
    $routes->post('edit', '\App\Controllers\Api\Settings\PengaturanWarnaController::edit');
    $routes->post('remove', '\App\Controllers\Api\Settings\PengaturanWarnaController::remove');
});

// ── Modul UserManagement (Web Routes) ──
$routes->group('', ['namespace' => 'App\Controllers\Web\UserManagement', 'filter' => 'login'], function ($routes) {
    $routes->get('pengguna/setuser', 'PenggunaController::index');
    $routes->get('karyawan', 'KaryawanController::index');
});

// ── Modul UserManagement (API Routes Modern) ──
$routes->group('api/pengguna', ['namespace' => 'App\Controllers\Api\UserManagement', 'filter' => 'login'], function ($routes) {
    $routes->post('getall', 'PenggunaController::getAll');
    $routes->post('getAll', 'PenggunaController::getAll');
    $routes->post('getone', 'PenggunaController::getOne');
    $routes->post('getOne', 'PenggunaController::getOne');
    $routes->post('add', 'PenggunaController::add');
    $routes->post('edit', 'PenggunaController::edit');
    $routes->post('edit_user', 'PenggunaController::edit_user');
    $routes->post('remove', 'PenggunaController::remove');
});

$routes->group('api/karyawan', ['namespace' => 'App\Controllers\Api\UserManagement', 'filter' => 'login'], function ($routes) {
    $routes->post('getall', 'KaryawanController::getAll');
    $routes->post('getAll', 'KaryawanController::getAll');
    $routes->post('getone', 'KaryawanController::getOne');
    $routes->post('getOne', 'KaryawanController::getOne');
    $routes->post('add', 'KaryawanController::add');
    $routes->post('edit', 'KaryawanController::edit');
    $routes->post('remove', 'KaryawanController::remove');
});

$routes->group('api/profil', ['namespace' => 'App\Controllers\Api\UserManagement', 'filter' => 'login'], function ($routes) {
    $routes->post('update', 'ProfilController::update');
});

// ── Modul UserManagement (Legacy Routes Zero-Downtime Aliases) ──
$routes->group('pengguna', ['filter' => 'login'], function ($routes) {
    $routes->get('setuser', '\App\Controllers\Web\UserManagement\PenggunaController::index');
    $routes->post('getAll', '\App\Controllers\Api\UserManagement\PenggunaController::getAll');
    $routes->post('getall', '\App\Controllers\Api\UserManagement\PenggunaController::getAll');
    $routes->post('getOne', '\App\Controllers\Api\UserManagement\PenggunaController::getOne');
    $routes->post('getone', '\App\Controllers\Api\UserManagement\PenggunaController::getOne');
    $routes->post('add', '\App\Controllers\Api\UserManagement\PenggunaController::add');
    $routes->post('edit', '\App\Controllers\Api\UserManagement\PenggunaController::edit');
    $routes->post('edit_user', '\App\Controllers\Api\UserManagement\PenggunaController::edit_user');
    $routes->post('remove', '\App\Controllers\Api\UserManagement\PenggunaController::remove');
});

$routes->group('karyawan', ['filter' => 'login'], function ($routes) {
    $routes->get('/', '\App\Controllers\Web\UserManagement\KaryawanController::index');
    $routes->post('getAll', '\App\Controllers\Api\UserManagement\KaryawanController::getAll');
    $routes->post('getall', '\App\Controllers\Api\UserManagement\KaryawanController::getAll');
    $routes->post('getOne', '\App\Controllers\Api\UserManagement\KaryawanController::getOne');
    $routes->post('getone', '\App\Controllers\Api\UserManagement\KaryawanController::getOne');
    $routes->post('add', '\App\Controllers\Api\UserManagement\KaryawanController::add');
    $routes->post('edit', '\App\Controllers\Api\UserManagement\KaryawanController::edit');
    $routes->post('remove', '\App\Controllers\Api\UserManagement\KaryawanController::remove');
});

$routes->get('getmodal', '\App\Controllers\Web\Siteplan\ModalController::index');

$routes->group('kalendar', ['filter' => 'login'], function ($routes) {
    $routes->get('/', '\App\Controllers\Web\Siteplan\KalendarController::index');
    $routes->get('index', '\App\Controllers\Web\Siteplan\KalendarController::index');
    $routes->match(['get', 'post'], 'getEvents', '\App\Controllers\Api\Siteplan\KalendarController::getEvents');
    $routes->match(['get', 'post'], 'getevents', '\App\Controllers\Api\Siteplan\KalendarController::getEvents');
});

$routes->group('files', ['filter' => 'login'], function ($routes) {
    $routes->get('(:segment)/path/thumbnail', 'FileController::pathThumbnail/$1');
    $routes->get('(:segment)/path', 'FileController::path/$1');
    $routes->get('(:segment)/(:num)/thumbnail', 'FileController::thumbnail/$1/$2');
    $routes->get('(:segment)/(:num)', 'FileController::show/$1/$2');
});



$routes->group('print', ['namespace' => 'App\Controllers\Web\Keuangan', 'filter' => 'login'], function ($routes) {
    $routes->get('spptb', 'PrintController::printSpptb');
    $routes->get('kuitansi', 'PrintController::printKuitansi');
});
$routes->get("pembayaran/kuitansi/cetak", '\App\Controllers\Web\Keuangan\PrintController::printPembayaran', ['filter' => 'login']);
$routes->get("pembayaran/kuitansi-um/cetak", '\App\Controllers\Web\Keuangan\PrintController::printPembayaranUm', ['filter' => 'login']);
$routes->get("keuangan/print_tagihan", '\App\Controllers\Web\Keuangan\PrintController::printTagihan', ['filter' => 'login']);

// ── Modul Siteplan (Web Routes) ──
$routes->group('siteplan', ['namespace' => 'App\Controllers\Web\Siteplan', 'filter' => 'login'], function ($routes) {
    $routes->get('/', 'SiteplanController::index');
    $routes->get('index', 'SiteplanController::index');
    $routes->get('view', 'SiteplanController::view_siteplan');
    $routes->get('view_siteplan', 'SiteplanController::view_siteplan');
    $routes->get('view_siteplan/(:num)', 'SiteplanController::view_siteplan/$1');
    $routes->get('produksi-mobile', 'SiteplanController::produksi_mobile');
    $routes->get('produksi_mobile', 'SiteplanController::produksi_mobile');
});

// ── Modul Siteplan (Legacy Zero-Downtime AJAX Routes) ──
$routes->group('siteplan', ['namespace' => 'App\Controllers\Api\Siteplan', 'filter' => 'login'], function ($routes) {
    $routes->post('get/all', 'SiteplanController::getAllKavling');
    $routes->post('getAllKavling', 'SiteplanController::getAllKavling');
    $routes->post('get/detail', 'SiteplanController::get_detail');
    $routes->post('get_detail', 'SiteplanController::get_detail');
    $routes->post('getKategoriOptions', 'SiteplanController::getKategoriOptions');
    $routes->post('urgent/summary', 'SiteplanController::urgentSummary');
    $routes->post('get_others', 'SiteplanController::get_others');
    $routes->post('add_kavling', 'SiteplanController::add_kavling');
    $routes->post('edit_kavling', 'SiteplanController::edit_kavling');
    $routes->post('edit_others', 'SiteplanController::edit_others');
    $routes->post('get_kavling_by_multiple_id', 'SiteplanController::get_kavling_by_multiple_id');
    $routes->post('get_harga_kavling', 'SiteplanController::get_harga_kavling');
    $routes->post('get_turun_pembangunan', 'SiteplanController::get_turun_pembangunan');
    $routes->post('set_turun_pembangunan', 'SiteplanController::set_turun_pembangunan');
    $routes->post('get_kavling_by_id', 'SiteplanController::get_kavling_by_id');
});

// ── Modul Transaksi (Web Routes) ──
$routes->group('', ['namespace' => 'App\Controllers\Web\Transaksi', 'filter' => 'login'], function ($routes) {
    $routes->get('hargajual', 'HargajualController::index');
    $routes->get('hargajual/index', 'HargajualController::index');
});

// ── Modul MKDT Promo (Web & Legacy Zero-Downtime Routes) ──
$routes->get('promo', '\App\Controllers\Web\Mkdt\PromoController::index', ['filter' => 'login']);
$routes->group('promo', ['filter' => 'login'], function ($routes) {
    $routes->get('index', 'Promo::index');
    $routes->match(['get', 'post'], 'getDataTable', 'Promo::getDataTable');
    $routes->match(['get', 'post'], 'getdatatable', 'Promo::getDataTable');
    $routes->match(['get', 'post'], 'getAll', 'Promo::getAll');
    $routes->match(['get', 'post'], 'getall', 'Promo::getAll');
    $routes->match(['get', 'post'], 'getOne', 'Promo::getOne');
    $routes->match(['get', 'post'], 'getone', 'Promo::getOne');
    $routes->match(['get', 'post'], 'getOne/(:num)', 'Promo::getOne/$1');
    $routes->match(['get', 'post'], 'getone/(:num)', 'Promo::getOne/$1');
    $routes->post('add', 'Promo::add');
    $routes->post('edit', 'Promo::edit');
    $routes->post('edit/(:num)', 'Promo::edit/$1');
    $routes->post('remove', 'Promo::remove');
});

// ── Modul Transaksi (Legacy Zero-Downtime Aliases) ──
$routes->group('hargajual', ['namespace' => 'App\Controllers\Api\Transaksi', 'filter' => 'login'], function ($routes) {
    $routes->post('get/all', 'HargajualController::getDataTable');
    $routes->post('getDataTable', 'HargajualController::getDataTable');
    $routes->post('getdatatable', 'HargajualController::getDataTable');
    $routes->post('get', 'HargajualController::getAll');
    $routes->post('getAll', 'HargajualController::getAll');
    $routes->post('getall', 'HargajualController::getAll');
    $routes->post('getOne', 'HargajualController::getOne');
    $routes->post('getone', 'HargajualController::getOne');
    $routes->post('add', 'HargajualController::add');
    $routes->post('edit', 'HargajualController::edit');
    $routes->post('remove', 'HargajualController::remove');
    $routes->post('set_harga', 'HargajualController::set_harga');
});

$routes->group('transaksi', ['namespace' => 'App\Controllers\Api\Transaksi', 'filter' => 'login'], function ($routes) {
    $routes->post('simpan', 'TransaksiController::saveTransaksi');
    $routes->post('saveTransaksi', 'TransaksiController::saveTransaksi');
    $routes->post('savetransaksi', 'TransaksiController::saveTransaksi');
    $routes->post('ambilsatu', 'TransaksiController::getByID');
    $routes->post('getByID', 'TransaksiController::getByID');
    $routes->post('getbyid', 'TransaksiController::getByID');
    $routes->post('status/ambilsatu', 'TransaksiController::getStatusByID');
    $routes->post('getStatusByID', 'TransaksiController::getStatusByID');
    $routes->post('getstatusbyid', 'TransaksiController::getStatusByID');
    $routes->post('status/simpan', 'TransaksiController::saveStatus');
    $routes->post('saveStatus', 'TransaksiController::saveStatus');
    $routes->post('savestatus', 'TransaksiController::saveStatus');
});


//tipe
$routes->post("tipe/get/all", 'Tipe::getAll');

//proyek
$routes->post("proyek/get/all", 'Proyek::getAll');

//export
$routes->post("export/poskon/(:segment)/(:segment)", '\App\Controllers\Api\Mkdt\PosisiKonsumenController::exportPoskon/$1/$2');

//log export
$routes->post("riwayat/poskon/(:segment)", '\App\Controllers\Api\Mkdt\PosisiKonsumenController::getRiwayatExport/$1');


// routes awal
// $routes->post("/transaksi/simpan", 'Transaksi::saveTransaksi');
// $routes->post("/transaksi/ambilsatu", 'Transaksi::getByID');
// $routes->post("/transaksi/status/ambilsatu", 'Transaksi::getStatusById');
// $routes->post("/transaksi/status/simpan", 'Transaksi::saveStatus');

$routes->group('tagihan', ['namespace' => 'App\Controllers\Api\Keuangan', 'filter' => 'login'], function ($routes) {
    $routes->post('ambilsatu', 'TagihanController::getByID');
    $routes->post('riwayat/ambilsatu', 'TagihanController::getRiwayatByID');
    $routes->post('turunkpr', 'TagihanController::tambahTurunKPR');
    $routes->post('hapusturunkpr', 'TagihanController::hapusTurunKPR');
    $routes->post('hapus', 'TagihanController::hapus');
    $routes->post('void', 'TagihanController::voidTagihan');
    $routes->post('unvoid', 'TagihanController::unvoidTagihan');
    $routes->post('save', 'TagihanController::save');
    $routes->post('jatuhtempo', 'TagihanController::getAllJatuhTempo');
    $routes->post('riwayat-bayar/ambil', 'TagihanController::getRiwayatBayar');
    $routes->post('list/ambil', 'TagihanController::getListTagihan');
    $routes->post('list/ambil-grouped', 'TagihanController::getListTagihanGrouped');
    $routes->post('list/detail', 'TagihanController::getListTagihanDetail');
    $routes->post('list/export-excel', 'TagihanController::exportExcelTagihan');
    $routes->post('list/ambil-riwayat-surat', 'TagihanController::getListRiwayatSurat');
});

$routes->get('/riwayat-bayar', '\App\Controllers\Web\Keuangan\TagihanController::riwayatBayarIndex', ['filter' => 'login']);
$routes->get('/tagihan/list', '\App\Controllers\Web\Keuangan\TagihanController::listTagihan', ['filter' => 'login']);

$routes->group('', ['filter' => 'login'], function ($routes) {
    $routes->get('riwayat-perubahan', '\App\Controllers\Web\AuditTrail\RiwayatPerubahanController::index');
});

//dana akad
$routes->post("/danaakad/list/ambilsatu", '\App\Controllers\Api\Keuangan\TagihanController::getListTagihan', ['filter' => 'login']);
$routes->post("/danaakad/getbyid", '\App\Controllers\Api\Keuangan\DanaJaminanController::getByID', ['filter' => 'login']);
$routes->post("/keuangan/getDanaAkad", '\App\Controllers\Api\Keuangan\DanaJaminanController::getDanaAkad', ['filter' => 'login']);
$routes->post("/keuangan/saveDanaAkad", '\App\Controllers\Api\Keuangan\DanaJaminanController::saveDanaAkad', ['filter' => 'login']);

//pembayaran
$routes->post("/pembayaran/simpan", '\App\Controllers\Api\Keuangan\PembayaranController::save', ['filter' => 'login']);
$routes->post("/pembayaran/hapus", '\App\Controllers\Api\Keuangan\PembayaranController::removeLP', ['filter' => 'login']);

//cashout
$routes->post("/keuangan/cashout/ambil", 'CashOut::getByIDKavling');
$routes->post("/keuangan/cashout/listitem/ambil", 'CashOut::getListItem');
$routes->post("/keuangan/cashout/delete", 'CashOut::delete');
$routes->post("/keuangan/cashout/save", 'CashOut::insert');
$routes->post("/keuangan/pencairan-bank/get", '\App\Controllers\Api\Keuangan\BankKprDisbursementController::getData', ['filter' => 'login']);
$routes->post("/keuangan/pencairan-bank/save", '\App\Controllers\Api\Keuangan\BankKprDisbursementController::save', ['filter' => 'login']);
$routes->post("/keuangan/pencairan-bank/void/(:num)", '\App\Controllers\Api\Keuangan\BankKprDisbursementController::void/$1', ['filter' => 'login']);

$routes->group('api/keuangan', ['namespace' => 'App\Controllers\Api\Keuangan', 'filter' => 'login'], function ($routes) {
    $routes->get('tagihan/meta', 'TagihanController::meta');
    $routes->get('pembayaran/meta', 'PembayaranController::meta');
    $routes->get('pencairan-bank/meta', 'BankKprDisbursementController::meta');
    $routes->get('pencairan-akad/meta', 'PencairanAkadController::meta');
    $routes->get('dana-jaminan/meta', 'DanaJaminanController::meta');
});

//pencairan akad (gabungan retensi + hasil akad)
$routes->group('keuangan', ['filter' => 'login'], function ($routes) {
    $routes->post('pencairan-akad/get', '\App\Controllers\Api\Keuangan\PencairanAkadController::get');
    $routes->post('pencairan-akad/plan/save-retensi', '\App\Controllers\Api\Keuangan\PencairanAkadController::saveRetensi');
    $routes->post('pencairan-akad/plan/save-tenor', '\App\Controllers\Api\Keuangan\PencairanAkadController::saveTenor');
    $routes->post('pencairan-akad/pengajuan/store', '\App\Controllers\Api\Keuangan\PencairanAkadController::storePengajuan');
    $routes->post('pencairan-akad/pencairan/store', '\App\Controllers\Api\Keuangan\PencairanAkadController::cairkan');
    $routes->post('pencairan-akad/payment/list', '\App\Controllers\Api\Keuangan\PencairanAkadController::listPayment');
    $routes->post('pencairan-akad/payment/void', '\App\Controllers\Api\Keuangan\PencairanAkadController::voidPayment');
    $routes->post('pencairan-akad/void', '\App\Controllers\Api\Keuangan\PencairanAkadController::void');
    $routes->get('pencairan-akad/history/(:num)', '\App\Controllers\Api\Keuangan\PencairanAkadController::history/$1');

    $routes->post('pencairan-akad/batch/list', '\App\Controllers\Api\Keuangan\PencairanAkadController::postBatchList');
    $routes->post('pencairan-akad/batch/eligible-kavling', '\App\Controllers\Api\Keuangan\PencairanAkadController::postEligibleKavling');
    $routes->post('pencairan-akad/batch/pengajuan/store', '\App\Controllers\Api\Keuangan\PencairanAkadController::postStoreBatchPengajuan');
    $routes->post('pencairan-akad/batch/detail', '\App\Controllers\Api\Keuangan\PencairanAkadController::postBatchDetail');
    $routes->post('pencairan-akad/batch/pencairan/store', '\App\Controllers\Api\Keuangan\PencairanAkadController::postCairkanBatch');
    $routes->post('pencairan-akad/batch/void', '\App\Controllers\Api\Keuangan\PencairanAkadController::postVoidBatch');
    $routes->get('pencairan-akad/batch/history/(:num)', '\App\Controllers\Api\Keuangan\PencairanAkadController::getBatchHistory/$1');

    $routes->get('hasil-akad/list', '\App\Controllers\Web\Keuangan\PencairanAkadController::listHasilAkad');
    $routes->post('hasil-akad/list/ambil-grouped', '\App\Controllers\Api\Keuangan\PencairanAkadController::getListGrouped');
    $routes->post('hasil-akad/list/detail', '\App\Controllers\Api\Keuangan\PencairanAkadController::getListDetail');
    $routes->get('hasil-akad/export-template', '\App\Controllers\Web\Keuangan\PencairanAkadController::exportTemplate');
    $routes->get('hasil-akad/import', '\App\Controllers\Web\Keuangan\PencairanAkadController::importForm');
    $routes->post('hasil-akad/import', '\App\Controllers\Api\Keuangan\PencairanAkadController::import');
});

$routes->group('laporan', ['namespace' => 'App\Controllers\Web', 'filter' => 'login'], function ($routes) {
    $routes->get('cash-in', '\App\Controllers\Web\Keuangan\CashInReportController::index');
    $routes->get('hasil-akad', '\App\Controllers\Web\Keuangan\HasilAkadReportController::index');
    $routes->get('booking-akad', '\App\Controllers\Web\Mkdt\BookingAkadReportController::index');
});

$routes->group('api/laporan/cash-in', ['namespace' => 'App\Controllers\Api\Keuangan', 'filter' => 'login'], function ($routes) {
    $routes->get('meta', 'CashInReportController::meta');
    $routes->post('summary', 'CashInReportController::summary');
    $routes->post('detail', 'CashInReportController::detail');
});

$routes->group('api/laporan/hasil-akad', ['namespace' => 'App\Controllers\Api\Keuangan', 'filter' => 'login'], function ($routes) {
    $routes->get('meta', 'HasilAkadReportController::meta');
    $routes->post('summary', 'HasilAkadReportController::summary');
    $routes->post('detail', 'HasilAkadReportController::detail');
});

$routes->group('api/laporan/booking-akad', ['namespace' => 'App\Controllers\Api\Mkdt', 'filter' => 'login'], function ($routes) {
    $routes->post('summary', 'BookingAkadReportController::summary');
    $routes->post('detail', 'BookingAkadReportController::detail');
});


$routes->get("/pembayaran/hitungulang", '\App\Controllers\Api\Keuangan\PembayaranController::recalculateSummary', ['filter' => 'login']);

$routes->group('kavling', ['namespace' => 'App\Controllers\Api\Transaksi', 'filter' => 'login'], function ($routes) {
    $routes->post('list/ambil', 'KavlingController::getList');
    $routes->post('getList', 'KavlingController::getList');
});

$routes->group('api/kavling', ['namespace' => 'App\Controllers\Api\Transaksi', 'filter' => 'login'], function ($routes) {
    $routes->get('list', 'KavlingController::getList');
    $routes->post('list', 'KavlingController::getList');
    $routes->post('list/ambil', 'KavlingController::getList');
});

$routes->group('subkon', ['namespace' => 'App\Controllers\Api\Produksi', 'filter' => 'login'], function ($routes) {
    $routes->match(['get', 'post'], 'list/ambil', 'SubkonController::getList');
    $routes->match(['get', 'post'], 'list',       'SubkonController::getList');
});

$routes->group('api/subkon', ['namespace' => 'App\Controllers\Api\Produksi', 'filter' => 'login'], function ($routes) {
    $routes->match(['get', 'post'], 'list', 'SubkonController::getList');
});

//subkon
$routes->group('cashout', ['filter' => 'login', 'namespace' => 'App\Controllers\Keuangan\Cashout'], function ($routes) {
    $routes->get('kavling', 'CashoutKavling::index');
    $routes->post('kavling/list', 'CashoutKavling::getDataTables');
    $routes->post('kavling/detail-list', 'CashoutKavling::getDetailList');
    $routes->get('subkon', 'CashoutSubkon::index');
    $routes->post('subkon/list', 'CashoutSubkon::getDataTables');
    $routes->post('subkon/detail-list', 'CashoutSubkon::getDetailList');
    $routes->post('subkon/ambil', 'CashoutSubkon::get');
    $routes->post('subkon/save', 'CashoutSubkon::save');
    $routes->post('subkon/turun-jatuh-tempo', 'CashoutSubkon::turunJatuhTempo');
    $routes->post('subkon/ajukan-spp', 'CashoutSubkon::ajukanSPP');
    $routes->post('subkon/history', 'CashoutSubkon::getHistory');
    $routes->post('subkon/ajukan-pencairan', 'CashoutSubkon::ajukanPencairan');
    $routes->post('subkon/pembayaran', 'CashoutSubkon::pembayaran');
    // $routes->post('toggle/(:num)', 'CashoutSubkon::toggleStatus/$1');
    // $routes->get('download/(:num)', 'CashoutSubkon::download/$1');
});



// DEPRECATED: Rute legacy untuk PencairanJaminan, dijadwalkan untuk dihapus saat refaktor Keuangan.
// Seluruh fitur pencairan telah dialihkan ke rute /keuangan/pencairan-akad/*
$routes->group('pencairan', ['namespace' => 'App\Controllers\Api\Keuangan', 'filter' => 'login'], function ($routes) {
    $routes->get('/', 'DanaJaminanController::index');
    $routes->get('list/(:num)', 'DanaJaminanController::list/$1');
    $routes->post('store', 'DanaJaminanController::store');
    $routes->post('cairkan/(:num)', 'DanaJaminanController::cairkan/$1');
    $routes->get('history/(:num)', 'DanaJaminanController::history/$1');
    $routes->post('toggle/(:num)', 'DanaJaminanController::toggleStatus/$1');
    $routes->get('download/(:num)', 'DanaJaminanController::download/$1');
});



$routes->group('api/legal', ['namespace' => 'App\Controllers\Api\Legal', 'filter' => 'login'], function ($routes) {
    $routes->get('meta', 'LegalController::meta');
    $routes->post('meta', 'LegalController::meta');
    $routes->post('save', 'LegalController::save');
    $routes->get('get', 'LegalController::getDataByID');
    $routes->post('get', 'LegalController::getDataByID');
    $routes->post('remove', 'LegalController::removeDoc');
    $routes->post('removeDoc', 'LegalController::removeDoc');
    $routes->get('getDoc', 'LegalController::getDoc');
    $routes->post('getDoc', 'LegalController::getDoc');
    $routes->post('upload', 'LegalController::upload');
    $routes->get('getListLegalitas', 'LegalController::getListLegalitas');
    $routes->post('getListLegalitas', 'LegalController::getListLegalitas');
    $routes->post('edit_others', 'LegalController::edit_others');
});

$routes->group('api/transaksi', ['namespace' => 'App\Controllers\Api\Transaksi', 'filter' => 'login'], function($routes) {
    $routes->get('meta', 'TransaksiController::meta');
    $routes->post('meta', 'TransaksiController::meta');
    $routes->post('simpan', 'TransaksiController::saveTransaksi');
    $routes->post('ambilsatu', 'TransaksiController::getByID');
    $routes->post('status/ambilsatu', 'TransaksiController::getStatusByID');
    $routes->post('status/simpan', 'TransaksiController::saveStatus');
    $routes->post('konsumen/ganti', 'TransaksiController::replaceConsumer');
});

$routes->group('api/hargajual', ['namespace' => 'App\Controllers\Api\Transaksi', 'filter' => 'login'], function ($routes) {
    $routes->get('meta', 'HargajualController::meta');
    $routes->post('meta', 'HargajualController::meta');
    $routes->post('get/all', 'HargajualController::getDataTable');
    $routes->post('datatable', 'HargajualController::getDataTable');
    $routes->post('getDataTable', 'HargajualController::getDataTable');
    $routes->post('get', 'HargajualController::getAll');
    $routes->post('all', 'HargajualController::getAll');
    $routes->post('getAll', 'HargajualController::getAll');
    $routes->post('getOne', 'HargajualController::getOne');
    $routes->post('getone', 'HargajualController::getOne');
    $routes->post('add', 'HargajualController::add');
    $routes->post('edit', 'HargajualController::edit');
    $routes->post('remove', 'HargajualController::remove');
    $routes->post('set_harga', 'HargajualController::set_harga');
    $routes->post('set-harga', 'HargajualController::set_harga');
});

$routes->group('api/mkdt', ['namespace' => 'App\Controllers\Api\Transaksi', 'filter' => 'login'], function ($routes) {
    $routes->post('history', 'MkdtController::history');
    $routes->post('consumer-replacement-history', 'MkdtController::consumerReplacementHistory');
});

$routes->group('api/mkdt', ['namespace' => 'App\Controllers\Api\Mkdt', 'filter' => 'login'], function ($routes) {
    $routes->match(['get', 'post'], 'operations/meta', 'MkdtOperationController::meta');
    $routes->match(['get', 'post'], 'stock/list', 'MkdtOperationController::getListStock');
    $routes->match(['get', 'post'], 'si', 'MkdtOperationController::getSi');
    $routes->post('si/save', 'MkdtOperationController::saveSi');
    $routes->match(['get', 'post'], 'batal/detail', 'MkdtOperationController::getBatal');
    $routes->post('batal/simpan', 'MkdtOperationController::simpanBatal');
});

// ── Modul MKDT Operasi (Legacy Zero-Downtime Routes) ──
$routes->group('mkdt', ['namespace' => 'App\Controllers\Api\Mkdt', 'filter' => 'login'], function ($routes) {
    $routes->match(['get', 'post'], 'getListStock', 'MkdtOperationController::getListStock');
    $routes->match(['get', 'post'], 'getliststock', 'MkdtOperationController::getListStock');
    $routes->match(['get', 'post'], 'getsi', 'MkdtOperationController::getSi');
    $routes->post('saveSI', 'MkdtOperationController::saveSi');
    $routes->post('savesi', 'MkdtOperationController::saveSi');
    $routes->match(['get', 'post'], 'batal_mkdt', 'MkdtOperationController::getBatal');
    $routes->post('simpan_batal', 'MkdtOperationController::simpanBatal');
});

$routes->group('api/mkdt/promo', ['namespace' => 'App\Controllers\Api\Mkdt', 'filter' => 'login'], function ($routes) {
    $routes->match(['get', 'post'], 'meta', 'PromoController::meta');
    $routes->match(['get', 'post'], 'datatable', 'PromoController::getDataTable');
    $routes->match(['get', 'post'], 'getdatatable', 'PromoController::getDataTable');
    $routes->match(['get', 'post'], 'getDataTable', 'PromoController::getDataTable');
    $routes->match(['get', 'post'], 'all', 'PromoController::getAll');
    $routes->match(['get', 'post'], 'getall', 'PromoController::getAll');
    $routes->match(['get', 'post'], 'getAll', 'PromoController::getAll');
    $routes->match(['get', 'post'], 'getone', 'PromoController::getOne');
    $routes->match(['get', 'post'], 'getOne', 'PromoController::getOne');
    $routes->match(['get', 'post'], 'getone/(:num)', 'PromoController::getOne/$1');
    $routes->match(['get', 'post'], 'getOne/(:num)', 'PromoController::getOne/$1');
    $routes->post('add', 'PromoController::add');
    $routes->post('edit', 'PromoController::edit');
    $routes->post('edit/(:num)', 'PromoController::edit/$1');
    $routes->post('remove', 'PromoController::remove');
    $routes->post('remove/(:num)', 'PromoController::remove/$1');
});

$routes->group('api/mkdt/posisi-konsumen', ['namespace' => 'App\Controllers\Api\Mkdt', 'filter' => 'login'], function ($routes) {
    $routes->match(['get', 'post'], 'meta', 'PosisiKonsumenController::meta');
    $routes->match(['get', 'post'], 'datatable', 'PosisiKonsumenController::getDataTables');
    $routes->match(['get', 'post'], 'datatable-akad', 'PosisiKonsumenController::getDataTablesAkad');
    $routes->match(['get', 'post'], 'datatable-batal', 'PosisiKonsumenController::getDataTablesBatal');
    $routes->match(['get', 'post'], 'datatable/(:segment)', 'PosisiKonsumenController::getDataTables/$1');
    $routes->post('export/(:segment)/(:segment)', 'PosisiKonsumenController::exportPoskon/$1/$2');
    $routes->match(['get', 'post'], 'riwayat/(:segment)', 'PosisiKonsumenController::getRiwayatExport/$1');
});

$routes->group('api/nup', ['namespace' => 'App\Controllers\Api\Mkdt', 'filter' => 'login'], function ($routes) {
    $routes->post('list', 'NupController::list');
    $routes->post('detail', 'NupController::detail');
    $routes->post('save', 'NupController::save');
    $routes->post('pull', 'NupController::pull');
    $routes->post('kavling-options', 'NupController::kavlingOptions');
    $routes->post('kavling-history', 'NupController::kavlingHistory');
});

$routes->group('api/tagihan/booking', ['namespace' => 'App\Controllers\Api\Keuangan', 'filter' => ['login', 'csrf']], function ($routes) {
    $routes->post('verifikasi', 'BookingPaymentController::verify');
});

$routes->group('api/referral-qr', ['namespace' => 'App\Controllers\Api\MGM', 'filter' => 'login'], function ($routes) {
    $routes->post('preview', 'ReferralQrController::preview');
    $routes->get('download', 'ReferralQrController::download');
});

$routes->group('api/produksi', ['namespace' => 'App\Controllers\Api\Produksi', 'filter' => 'login'], function ($routes) {
    $routes->match(['get', 'post'], 'meta',   'ProduksiController::meta');
    $routes->post('get_data_by_id',          'ProduksiController::get_data_by_id');
    $routes->post('getBayarProduksi',         'ProduksiController::getBayarProduksi');
    $routes->post('getBayarProduksiListItem', 'ProduksiController::getBayarProduksiListItem');
    $routes->post('saveBayarProduksi',        'ProduksiController::saveBayarProduksi');
    $routes->post('deleteBayarProduksi',      'ProduksiController::deleteBayarProduksi');
    $routes->post('get_data_komplain_by_id',  'ProduksiController::get_data_komplain_by_id');
    $routes->post('save_komplain_produksi',   'ProduksiController::save_komplain_produksi');
    $routes->post('save',                     'ProduksiController::save');
    $routes->post('upload-mobile',            'ProduksiController::uploadMobile');
    $routes->post('history',                  'ProduksiController::history');
    $routes->post('saveSLF',                  'ProduksiController::saveSLf');
    $routes->get('getSlf',                    'ProduksiController::getSlf');
    $routes->post('hapusSLF',                 'ProduksiController::hapusSLF');
    $routes->get('getSLFPDF/(:num)',           'ProduksiController::getSLFPDF/$1');
    $routes->post('getKavling',               'ProduksiController::getKavling');
    $routes->post('hapus_foto',               'ProduksiController::hapus_foto');
    $routes->post('get_gambarkerja',          'ProduksiController::get_gambarkerja');
    $routes->post('add_jalan',                'ProduksiController::add_jalan');
    $routes->post('edit_others',              'ProduksiController::edit_others');

    // Sales API sub-group
    $routes->group('sales', function ($routes) {
        $routes->post('get_data_by_id',              'SalesController::get_data_by_id');
        $routes->post('save_checklist',              'SalesController::save_checklist');
        $routes->post('get_data_komplain_by_id',    'SalesController::get_data_komplain_by_id');
        $routes->post('batalkan_komplain',           'SalesController::batalkan_komplain');
        $routes->post('save_komplain_sales',         'SalesController::save_komplain_sales');
        $routes->post('get_data_serah_terima_by_id', 'SalesController::get_data_serah_terima_by_id');
        $routes->post('save_serah_terima',           'SalesController::save_serah_terima');
    });
});

$routes->group('sales', ['namespace' => 'App\Controllers\Api\Produksi', 'filter' => 'login'], function ($routes) {
    $routes->post('get_data_by_id',              'SalesController::get_data_by_id');
    $routes->post('save_checklist',              'SalesController::save_checklist');
    $routes->post('get_data_komplain_by_id',    'SalesController::get_data_komplain_by_id');
    $routes->post('batalkan_komplain',           'SalesController::batalkan_komplain');
    $routes->post('save_komplain_sales',         'SalesController::save_komplain_sales');
    $routes->post('get_data_serah_terima_by_id', 'SalesController::get_data_serah_terima_by_id');
    $routes->post('save_serah_terima',           'SalesController::save_serah_terima');
});

$routes->group('', ['namespace' => 'App\Controllers\Web\Produksi', 'filter' => 'login'], function ($routes) {
    $routes->get('checklist-group',    'ChecklistGroupController::index');
    $routes->get('checklistGroup',      'ChecklistGroupController::index');
    $routes->get('checklist-item',     'ChecklistItemController::index');
    $routes->get('checklistItem',       'ChecklistItemController::index');
    $routes->get('checklist-sub-item', 'ChecklistSubItemController::index');
    $routes->get('checklistSubItem',    'ChecklistSubItemController::index');
    $routes->get('produksi/mobile',     'ProduksiController::produksiMobile');
});

$routes->group('checklistGroup', ['namespace' => 'App\Controllers\Api\Produksi', 'filter' => 'login'], function ($routes) {
    $routes->match(['get', 'post'], 'getAll', 'ChecklistGroupController::getAll');
    $routes->post('getOne',                   'ChecklistGroupController::getOne');
    $routes->post('add',                      'ChecklistGroupController::add');
    $routes->post('edit',                     'ChecklistGroupController::edit');
    $routes->post('remove',                   'ChecklistGroupController::remove');
});

$routes->group('checklistItem', ['namespace' => 'App\Controllers\Api\Produksi', 'filter' => 'login'], function ($routes) {
    $routes->match(['get', 'post'], 'getAll', 'ChecklistItemController::getAll');
    $routes->post('getOne',                   'ChecklistItemController::getOne');
    $routes->post('add',                      'ChecklistItemController::add');
    $routes->post('edit',                     'ChecklistItemController::edit');
    $routes->post('remove',                   'ChecklistItemController::remove');
});

$routes->group('checklistSubItem', ['namespace' => 'App\Controllers\Api\Produksi', 'filter' => 'login'], function ($routes) {
    $routes->match(['get', 'post'], 'getAll', 'ChecklistSubItemController::getAll');
    $routes->post('getOne',                   'ChecklistSubItemController::getOne');
    $routes->post('add',                      'ChecklistSubItemController::add');
    $routes->post('edit',                     'ChecklistSubItemController::edit');
    $routes->post('remove',                   'ChecklistSubItemController::remove');
});

$routes->group('api/checklist-group', ['namespace' => 'App\Controllers\Api\Produksi', 'filter' => 'login'], function ($routes) {
    $routes->match(['get', 'post'], 'getAll', 'ChecklistGroupController::getAll');
    $routes->post('getOne',                   'ChecklistGroupController::getOne');
    $routes->post('add',                      'ChecklistGroupController::add');
    $routes->post('edit',                     'ChecklistGroupController::edit');
    $routes->post('remove',                   'ChecklistGroupController::remove');
});

$routes->group('api/checklist-item', ['namespace' => 'App\Controllers\Api\Produksi', 'filter' => 'login'], function ($routes) {
    $routes->match(['get', 'post'], 'getAll', 'ChecklistItemController::getAll');
    $routes->post('getOne',                   'ChecklistItemController::getOne');
    $routes->post('add',                      'ChecklistItemController::add');
    $routes->post('edit',                     'ChecklistItemController::edit');
    $routes->post('remove',                   'ChecklistItemController::remove');
});

$routes->group('api/checklist-sub-item', ['namespace' => 'App\Controllers\Api\Produksi', 'filter' => 'login'], function ($routes) {
    $routes->match(['get', 'post'], 'getAll', 'ChecklistSubItemController::getAll');
    $routes->post('getOne',                   'ChecklistSubItemController::getOne');
    $routes->post('add',                      'ChecklistSubItemController::add');
    $routes->post('edit',                     'ChecklistSubItemController::edit');
    $routes->post('remove',                   'ChecklistSubItemController::remove');
});

$routes->group('api/target', ['namespace' => 'App\Controllers\Api\Siteplan', 'filter' => 'login'], function ($routes) {
    $routes->post('list',    'TargetController::list');
    $routes->post('detail',  'TargetController::detail');
    $routes->post('save',    'TargetController::save');
    $routes->post('history', 'TargetController::history');
});

$routes->group('api/riwayat-perubahan', ['namespace' => 'App\Controllers\Api\AuditTrail', 'filter' => 'login'], function ($routes) {
    $routes->get('meta', 'HistoryController::meta');
    $routes->post('list', 'HistoryController::list');
    $routes->post('kavling', 'HistoryController::kavling');
});

$routes->group('api/tiket-masalah', ['namespace' => 'App\Controllers\Api\TiketMasalah', 'filter' => 'login'], function ($routes) {
    $routes->post('list',               'TiketMasalahController::list');
    $routes->post('detail',             'TiketMasalahController::detail');
    $routes->post('progress',           'TiketMasalahController::progress');
    $routes->post('store',              'TiketMasalahController::store');
    $routes->post('update',             'TiketMasalahController::update');
    $routes->post('add-progress',       'TiketMasalahController::addProgress');
    $routes->post('ref-info',           'TiketMasalahController::refInfo');
    $routes->get('users',               'TiketMasalahController::users');
    $routes->get('divisions',           'TiketMasalahController::divisions');
    $routes->get('creators',            'TiketMasalahController::creators');
    $routes->post('create-others-area', 'TiketMasalahController::createOthersArea');
    $routes->post('datatable',          'TiketMasalahController::datatable');
    $routes->post('toggle-pin',         'TiketMasalahController::togglePin');
});

$routes->group('', ['filter' => 'login'], function ($routes) {
    $routes->get('tiket-masalah', '\App\Controllers\Web\TiketMasalah\TiketMasalahController::index');
    $routes->get('member-get-member', '\App\Controllers\Web\MGM\MemberGetMemberController::index');
});

$routes->group('api/mgm', ['namespace' => 'App\Controllers\Api\MGM', 'filter' => 'login'], function ($routes) {
    $routes->post('list', 'ReferralController::list');
    $routes->post('subrows', 'ReferralController::subRows');
    $routes->post('confirm-bonus', 'ReferralController::confirmBonus');
    $routes->post('update-nominal', 'ReferralController::updateNominal');
    $routes->post('pay-promosi', 'ReferralController::payByPromosi');
    $routes->post('submit-keuangan', 'ReferralController::submitKeuangan');
    $routes->post('mark-cair-keuangan', 'ReferralController::markCairKeuangan');
    $routes->post('cancel-bonus', 'ReferralController::cancelBonus');
    $routes->post('update-keterangan', 'ReferralController::updateKeterangan');
    $routes->post('search-options', 'ReferralController::searchOptions');

    $routes->post('stages/list', 'ReferralSettingController::listStages');
    $routes->post('stages/save', 'ReferralSettingController::saveStage');
    $routes->post('stages/delete', 'ReferralSettingController::deleteStage');
});

$routes->group('api/kavling-request', ['namespace' => 'App\Controllers\Api\Transaksi', 'filter' => 'login'], function ($routes) {
    $routes->post('submit', 'KavlingRequestController::submit');
    $routes->post('kavling-list', 'KavlingRequestController::kavlingList');
    $routes->post('list', 'KavlingRequestController::list');
    $routes->post('update-status', 'KavlingRequestController::updateStatus');
});

// ─────────────────────────────────────────────────────────────────────────────
// Public API — No authentication required
// Throttle: 20 requests / 60 seconds per IP
// ─────────────────────────────────────────────────────────────────────────────
$routes->group('api/public', [
    'namespace' => 'App\Controllers\Api\MGM',
    'filter'    => ['throttle:20,60', 'cors'],
], function ($routes) {
    // Handle OPTIONS request untuk CORS preflight
    $routes->options('check-referral', function() {
        return response()->setStatusCode(204);
    });

    // Validasi kode referral — POST only
    $routes->post('check-referral', 'PublicReferralController::checkReferral');

    // Blok akses GET agar tidak bisa dibuka langsung di browser
    $routes->get('check-referral', function () {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    });
});

// ── Master Sales External & Fee Sales ──────────────────────────────
$routes->get('master-sales', '\App\Controllers\Web\SalesExternal\SalesExternalController::index', ['filter' => ['login', 'role:Admin,MKDT']]);
$routes->get('fee-sales', '\App\Controllers\Web\SalesExternal\SalesExternalController::feeSales', ['filter' => 'login']);
$routes->get('fee-sales/pengajuan-pdf/(:num)', '\App\Controllers\Web\SalesExternal\SalesExternalController::pengajuanPdf/$1', ['filter' => 'login']);
$routes->get('fee-sales/export-pdf-pengajuan/(:num)', '\App\Controllers\Web\SalesExternal\SalesExternalController::pengajuanPdf/$1', ['filter' => 'login']);
$routes->get('fee-sales/pengajuan-signature/(:num)/(:segment)', '\App\Controllers\Web\SalesExternal\SalesExternalController::pengajuanSignature/$1/$2', ['filter' => 'login']);
$routes->get('fee-sales/pengajuan/(:segment)', '\App\Controllers\Web\SalesExternal\SalesExternalController::pengajuanShared/$1', ['filter' => 'login']);

$routes->group('api/sales-external', ['namespace' => 'App\Controllers\Api\SalesExternal', 'filter' => 'login'], function ($routes) {
    $routes->post('list-master', 'SalesExternalController::listMasterSales');
    $routes->get('get/(:num)', 'SalesExternalController::getSales/$1');
    $routes->post('save', 'SalesExternalController::saveMasterSales');
    $routes->get('history/(:num)', 'SalesExternalController::getSalesHistory/$1');
    $routes->get('list-active', 'SalesExternalController::getActiveSalesList');

    $routes->post('list-fee', 'SalesExternalController::listFeeSales');
    $routes->get('fee-detail/(:num)', 'SalesExternalController::getFeeDetail/$1');
    $routes->post('save-fee-header', 'SalesExternalController::saveFeeHeader');
    $routes->post('submit-spp', 'SalesExternalController::submitPengajuanSpp');
    $routes->post('konfirmasi-cair', 'SalesExternalController::konfirmasiCair');
    $routes->post('edit-pengajuan', 'SalesExternalController::editPengajuan');
    $routes->post('edit-cair', 'SalesExternalController::editCair');
    $routes->delete('fee-entry/(:num)', 'SalesExternalController::deleteFeeEntry/$1');
    $routes->post('delete-fee-entry/(:num)', 'SalesExternalController::deleteFeeEntry/$1');

    // Batch Pengajuan Pencairan
    $routes->post('prepare-group', 'SalesExternalController::preparePengajuanGroup');
    $routes->post('create-group', 'SalesExternalController::createPengajuanGroup');
    $routes->post('list-group', 'SalesExternalController::listPengajuanGroup');
    $routes->get('group-detail/(:num)', 'SalesExternalController::getPengajuanGroupDetail/$1');
    $routes->post('konfirmasi-cair-group', 'SalesExternalController::konfirmasiCairGroup');
    $routes->post('void-group', 'SalesExternalController::voidPengajuanGroup');
    $routes->post('sign-group', 'SalesExternalController::signPengajuanGroup');
    $routes->post('reject-group', 'SalesExternalController::rejectPengajuanGroup');
    $routes->post('recalculate-termin', 'SalesExternalController::recalculateTermin');
});




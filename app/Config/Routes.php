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

$routes->get('privacy-policy', '\App\Controllers\Web\LegalPageController::privacyPolicy');
$routes->get('toc', '\App\Controllers\Web\LegalPageController::termsOfService');

$routes->get('dashboard', 'Home::dashboard');
$routes->get('/', 'Home::dashboard');
$routes->get('landing', '\App\Controllers\Web\LandingController::index');

//poskon
$routes->get('/list-kavling', 'PosisiKonsumen::index');
$routes->post('/list-kavling/ambil', 'PosisiKonsumen::getDataTables');


$routes->get('/list-kavling/(:segment)', 'PosisiKonsumen::index/$1');
$routes->post('/list-kavling/akad/ambil', 'PosisiKonsumen::getDataTablesAkad');
$routes->post('/list-kavling/batal/ambil', 'PosisiKonsumen::getDataTablesBatal');


$routes->get('/list-batal', 'Mkdt::list_batal');
$routes->get('/list-stock', 'Mkdt::list_stock');
// $routes->get('/list-tagihan', 'Keuangan::list_tagihan');

$routes->get('/legalitas', 'Legal::list_legalitas');

$routes->get('/getnotif', 'Notif::getNotif');
$routes->get('/loadnotif', 'Notif::loadNotif');
$routes->get('/notif/summary', 'Notif::getSummary');
$routes->get('/notif/center', 'Notif::getCenter');
$routes->post('/notif/snooze', 'Notif::snooze');
$routes->post('/notif/mark-as-read/(:num)', 'Notif::markAsRead/$1');
$routes->post('/notif/mark-all-as-read', 'Notif::markAllAsRead');
$routes->get('/notif/icon/(:num)', 'Notif::icon/$1');
$routes->get('/notif/open/(:num)', 'Notif::open/$1', ['filter' => 'login']);
$routes->get('/loadaktivitas', 'Home::loadAktivitas');

$routes->get('/profil', '\App\Controllers\Web\UserManagement\ProfilController::index');
$routes->post('/profil/update', '\App\Controllers\Web\UserManagement\ProfilController::update');

// ── Modul BPB (Web Routes) ──
$routes->group('bpb', ['namespace' => 'App\Controllers\Web\Bpb'], function ($routes) {
    $routes->get('', 'BpbController::index', ['filter' => 'login']);
    $routes->get('(:num)/pdf', 'BpbController::pdf/$1', ['filter' => 'login']);
    $routes->get('signature/(:num)', 'BpbController::signature/$1', ['filter' => 'login']);
    $routes->get('verify/(:segment)', 'BpbController::verify/$1', ['filter' => 'throttle:20,60']);
});

// ── Modul BPB (API Routes) ──
$routes->group('api/bpb', ['namespace' => 'App\Controllers\Api\Bpb', 'filter' => 'csrf'], function ($routes) {
    $routes->post('list', 'BpbController::list');
    $routes->get('options', 'BpbController::options');
    $routes->get('detail/(:num)', 'BpbController::detail/$1');
    $routes->post('draft', 'BpbController::draft');
    $routes->post('submit', 'BpbController::submit', ['filter' => 'throttle:10,60']);
    $routes->post('(:num)/update', 'BpbController::update/$1', ['filter' => 'throttle:10,60']);
    $routes->post('(:num)/sign', 'BpbController::sign/$1', ['filter' => 'throttle:5,60']);
    $routes->post('(:num)/reject', 'BpbController::reject/$1', ['filter' => 'throttle:10,60']);
    $routes->post('(:num)/cancel', 'BpbController::cancel/$1');
    $routes->post('(:num)/status', 'BpbController::status/$1', ['filter' => 'throttle:10,60']);
});

$routes->group('api/profile/signature', ['namespace' => 'App\Controllers\Api\Bpb', 'filter' => 'csrf'], function ($routes) {
    $routes->get('', 'ProfileSignatureController::show');
    $routes->get('image', 'ProfileSignatureController::image');
    $routes->put('', 'ProfileSignatureController::save', ['filter' => 'throttle:5,60']);
    $routes->delete('', 'ProfileSignatureController::delete', ['filter' => 'throttle:5,60']);
});
$routes->group('google-calendar', ['filter' => 'login'], function ($routes) {
    $routes->get('connect', 'GoogleCalendar::connect');
    $routes->get('callback', 'GoogleCalendar::callback');
    $routes->post('disconnect', 'GoogleCalendar::disconnect');
});

$routes->get('/configcolor', '\App\Controllers\Web\Settings\PengaturanWarnaController::index');

$routes->post("/get-dashboard", 'Home::getDashboard');
$routes->post("/home/getMenuItemsJson", 'Home::getMenuItemsJson');

$routes->post("/keuangan/jatuhtempo/get", 'Keuangan::getJatuhTempo');


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
    $routes->post('all', 'ProyekController::getAll');
    $routes->post('getall', 'ProyekController::getAll');
    $routes->post('getone', 'ProyekController::getOne');
    $routes->post('add', 'ProyekController::add');
    $routes->post('edit', 'ProyekController::edit');
    $routes->post('remove', 'ProyekController::remove');
    $routes->get('siteplan-list', 'ProyekController::getSiteplanList');
    $routes->post('siteplan-list', 'ProyekController::getSiteplanList');
    $routes->get('accessible', 'ProyekController::getAccessible');
    $routes->post('accessible', 'ProyekController::getAccessible');
    $routes->post('set-active', 'ProyekController::setActive');
});

$routes->group('api/cluster', ['namespace' => 'App\Controllers\Api\Proyek', 'filter' => 'login'], function ($routes) {
    $routes->post('datatable', 'ClusterController::getDataTable');
    $routes->post('getdatatable', 'ClusterController::getDataTable');
    $routes->post('all', 'ClusterController::getAll');
    $routes->post('getall', 'ClusterController::getAll');
    $routes->post('getone', 'ClusterController::getOne');
    $routes->post('add', 'ClusterController::add');
    $routes->post('edit', 'ClusterController::edit');
    $routes->post('remove', 'ClusterController::remove');
});

$routes->group('api/tipe', ['namespace' => 'App\Controllers\Api\Proyek', 'filter' => 'login'], function ($routes) {
    $routes->post('datatables', 'TipeController::getDataTables');
    $routes->post('getdatatables', 'TipeController::getDataTables');
    $routes->post('all', 'TipeController::getAll');
    $routes->post('getall', 'TipeController::getAll');
    $routes->post('getone', 'TipeController::getOne');
    $routes->post('add', 'TipeController::add');
    $routes->post('edit', 'TipeController::edit');
    $routes->post('remove', 'TipeController::remove');
    $routes->post('list-kavling', 'TipeController::getListKavling');
    $routes->post('getlistkavling', 'TipeController::getListKavling');
});

$routes->group('api/jalan', ['namespace' => 'App\Controllers\Api\Proyek', 'filter' => 'login'], function ($routes) {
    $routes->post('datatable', 'JalanController::getDatatable');
    $routes->post('getdatatable', 'JalanController::getDatatable');
    $routes->post('all', 'JalanController::getAll');
    $routes->post('getall', 'JalanController::getAll');
    $routes->post('getone', 'JalanController::getOne');
    $routes->post('add', 'JalanController::add');
    $routes->post('edit', 'JalanController::edit');
    $routes->post('remove', 'JalanController::remove');
});

$routes->group('api/aksesproyek', ['namespace' => 'App\Controllers\Api\Proyek', 'filter' => 'login'], function ($routes) {
    $routes->post('all', 'AksesProyekController::getAll');
    $routes->post('getone', 'AksesProyekController::getOne');
    $routes->post('search-users', 'AksesProyekController::getAkses');
    $routes->post('edit', 'AksesProyekController::edit');
});



// $routes->get("bank", 'ListBank::index');

$routes->group('api/bank', ['namespace' => 'App\Controllers\Api', 'filter' => 'login'], function($routes) {
    $routes->get('ambil', 'BankController::ambil');
    $routes->get('list', 'BankController::list');
    $routes->post('ambilsatu', 'BankController::ambilSatu');
    $routes->post('simpan', 'BankController::simpan');
    $routes->post('hapus', 'BankController::hapus');
});

// Notifikasi push. Foreground tetap memakai polling /notif/summary.
$routes->group('api/notif', ['namespace' => 'App\Controllers\Api', 'filter' => 'login'], function($routes) {
    $routes->get('push/status', 'NotifPushController::status');
    $routes->post('push/subscribe', 'NotifPushController::subscribe');
    $routes->post('push/unsubscribe', 'NotifPushController::unsubscribe');
    $routes->post('push/test', 'NotifPushController::test', ['filter' => 'throttle:5,60']);

    // Preferensi Notifikasi
    $routes->get('preferences', 'NotificationPreferenceController::index');
    $routes->post('preferences', 'NotificationPreferenceController::save');
    $routes->post('preferences/reset', 'NotificationPreferenceController::reset');
});


// ── Modul Settings (Web Routes) ──
$routes->group('', ['namespace' => 'App\Controllers\Web\Settings', 'filter' => 'login'], function ($routes) {
    $routes->get('menu-setting', 'MenuSettingController::index');
    $routes->get('setting-data', 'SettingDataController::index');
});

// ── Modul Settings (API Routes Modern) ──
$routes->group('api/menu-setting', ['namespace' => 'App\Controllers\Api\Settings', 'filter' => 'login'], function ($routes) {
    $routes->post('menu/list', 'MenuSettingController::menuList');
    $routes->post('menu/get', 'MenuSettingController::menuGet');
    $routes->post('menu/save', 'MenuSettingController::menuSave');
    $routes->post('menu/toggle', 'MenuSettingController::menuToggle');
    $routes->post('access/group', 'MenuSettingController::groupAccess');
    $routes->post('access/group/save', 'MenuSettingController::groupSave');
    $routes->post('access/user', 'MenuSettingController::userAccess');
    $routes->post('access/user/save', 'MenuSettingController::userSave');
    $routes->post('siteplan-menu/list', 'MenuSettingController::siteplanMenuList');
    $routes->post('siteplan-menu/get', 'MenuSettingController::siteplanMenuGet');
    $routes->post('siteplan-menu/save', 'MenuSettingController::siteplanMenuSave');
    $routes->post('siteplan-menu/toggle', 'MenuSettingController::siteplanMenuToggle');
});

$routes->group('api/setting-data', ['namespace' => 'App\Controllers\Api\Settings', 'filter' => 'login'], function ($routes) {
    $routes->post('list/(:segment)', 'SettingDataController::list/$1');
    $routes->post('get/(:segment)', 'SettingDataController::get/$1');
    $routes->post('save/(:segment)', 'SettingDataController::save/$1');
    $routes->post('delete/(:segment)', 'SettingDataController::delete/$1');
});

$routes->group('api/pengaturan-warna', ['namespace' => 'App\Controllers\Api\Settings', 'filter' => 'login'], function ($routes) {
    $routes->post('all', 'PengaturanWarnaController::getAll');
    $routes->post('getall', 'PengaturanWarnaController::getAll');
    $routes->post('getone', 'PengaturanWarnaController::getOne');
    $routes->post('add', 'PengaturanWarnaController::add');
    $routes->post('edit', 'PengaturanWarnaController::edit');
    $routes->post('remove', 'PengaturanWarnaController::remove');
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

$routes->get("getmodal", 'Modal::index');

$routes->group('files', ['filter' => 'login'], function ($routes) {
    $routes->get('(:segment)/path/thumbnail', 'FileController::pathThumbnail/$1');
    $routes->get('(:segment)/path', 'FileController::path/$1');
    $routes->get('(:segment)/(:num)/thumbnail', 'FileController::thumbnail/$1/$2');
    $routes->get('(:segment)/(:num)', 'FileController::show/$1/$2');
});



$routes->group('print', ['filter' => 'login'], function ($routes) {
    $routes->get('spptb', 'PrintController::printSpptb');
    // $routes->get('kuitansi', 'PrintController::printKuitansi');
});
$routes->get("pembayaran/kuitansi/cetak", 'Pembayaran::print');
$routes->get("pembayaran/kuitansi-um/cetak", 'Pembayaran::printUm');

//siteplan
$routes->post("siteplan/get/all", 'Siteplan::getAllKavling');
$routes->post("siteplan/get/detail", 'Siteplan::get_detail');
$routes->post("siteplan/getKategoriOptions", 'Siteplan::getKategoriOptions');
$routes->post("siteplan/urgent/summary", 'Siteplan::urgentSummary');
$routes->get("siteplan/view", 'Siteplan::view_siteplan');
$routes->get("siteplan/produksi-mobile", 'Siteplan::produksi_mobile');

//hargajual
$routes->post("hargajual/get/all", 'Hargajual::getDataTable');
$routes->post("hargajual/get", 'Hargajual::getAll');


//tipe
$routes->post("tipe/get/all", 'Tipe::getAll');

//proyek
$routes->post("proyek/get/all", 'Proyek::getAll');

//export
$routes->post("export/poskon/(:segment)/(:segment)", 'PosisiKonsumen::exportPoskon/$1/$2');

//log export
$routes->post("riwayat/poskon/(:segment)", 'PosisiKonsumen::getRiwayatExport/$1');


// routes awal
// $routes->post("/transaksi/simpan", 'Transaksi::saveTransaksi');
// $routes->post("/transaksi/ambilsatu", 'Transaksi::getByID');
// $routes->post("/transaksi/status/ambilsatu", 'Transaksi::getStatusById');
// $routes->post("/transaksi/status/simpan", 'Transaksi::saveStatus');

$routes->post("/tagihan/ambilsatu", 'Tagihan::getByID');
$routes->post("/tagihan/riwayat/ambilsatu", 'Tagihan::getRiwayatByID');
$routes->post("/tagihan/turunkpr", 'Tagihan::tambahTurunKPR');
$routes->post("/tagihan/hapusturunkpr", 'Tagihan::hapusTurunKPR');
$routes->post("/tagihan/hapus", 'Tagihan::hapus');
$routes->post("/tagihan/void", 'Tagihan::voidTagihan');
$routes->post("/tagihan/unvoid", 'Tagihan::unvoidTagihan');
$routes->post("/tagihan/jatuhtempo", 'Tagihan::getAllJatuhTempo');

$routes->get('/riwayat-bayar', 'Tagihan::riwayatBayarIndex');

// $routes->get('/riwayat-bayar', 'Tagihan::riwayatBayarIndex');
$routes->post('/tagihan/riwayat-bayar/ambil', 'Tagihan::getRiwayatBayar');

$routes->group('', ['filter' => 'login'], function ($routes) {
    $routes->get('riwayat-perubahan', 'RiwayatPerubahan::index');
});


$routes->get("/tagihan/list", 'Tagihan::listTagihan');
$routes->post("/tagihan/list/ambil", 'Tagihan::getListTagihan');
$routes->post("/tagihan/list/ambil-grouped", 'Tagihan::getListTagihanGrouped');
$routes->post("/tagihan/list/detail", 'Tagihan::getListTagihanDetail');
$routes->post("/tagihan/list/export-excel", 'Tagihan::exportExcelTagihan');
$routes->post("/tagihan/list/ambil-riwayat-surat", 'Tagihan::getListRiwayatSurat');

//dana akad
$routes->post("/danaakad/list/ambilsatu", 'Tagihan::getListTagihan');

//pembayaran
$routes->post("/pembayaran/simpan", 'Pembayaran::save');
$routes->post("/pembayaran/hapus", 'Pembayaran::removeLP');

//cashout
$routes->post("/keuangan/cashout/ambil", 'CashOut::getByIDKavling');
$routes->post("/keuangan/cashout/listitem/ambil", 'CashOut::getListItem');
$routes->post("/keuangan/cashout/delete", 'CashOut::delete');
$routes->post("/keuangan/cashout/save", 'CashOut::insert');
$routes->post("/keuangan/pencairan-bank/get", 'Keuangan::getBankKprDisbursement');
$routes->post("/keuangan/pencairan-bank/save", 'Keuangan::saveBankKprDisbursement');
$routes->post("/keuangan/pencairan-bank/void/(:num)", 'Keuangan::voidBankKprDisbursement/$1');

//pencairan akad (gabungan retensi + hasil akad)
$routes->post("/keuangan/pencairan-akad/get", 'PencairanAkad::get');
$routes->post("/keuangan/pencairan-akad/plan/save-retensi", 'PencairanAkad::saveRetensi');
$routes->post("/keuangan/pencairan-akad/plan/save-tenor", 'PencairanAkad::saveTenor');
$routes->post("/keuangan/pencairan-akad/pengajuan/store", 'PencairanAkad::storePengajuan');
$routes->post("/keuangan/pencairan-akad/pencairan/store", 'PencairanAkad::cairkan');
$routes->post("/keuangan/pencairan-akad/payment/list", 'PencairanAkad::listPayment');
$routes->post("/keuangan/pencairan-akad/payment/void", 'PencairanAkad::voidPayment');
$routes->post("/keuangan/pencairan-akad/void", 'PencairanAkad::void');
$routes->get("/keuangan/pencairan-akad/history/(:num)", 'PencairanAkad::history/$1');

$routes->get("/keuangan/hasil-akad/list", 'PencairanAkad::listHasilAkad');
$routes->post("/keuangan/hasil-akad/list/ambil-grouped", 'PencairanAkad::getListGrouped');
$routes->post("/keuangan/hasil-akad/list/detail", 'PencairanAkad::getListDetail');
$routes->get("/keuangan/hasil-akad/export-template", 'PencairanAkad::exportTemplate');
$routes->get("/keuangan/hasil-akad/import", 'PencairanAkad::importForm');
$routes->post("/keuangan/hasil-akad/import", 'PencairanAkad::import');

$routes->group('laporan', ['namespace' => 'App\Controllers\Web', 'filter' => 'login'], function ($routes) {
    $routes->get('cash-in', 'CashInReportController::index');
    $routes->get('hasil-akad', 'HasilAkadReportController::index');
});

$routes->group('api/laporan/cash-in', ['namespace' => 'App\Controllers\Api', 'filter' => 'login'], function ($routes) {
    $routes->post('summary', 'CashInReportController::summary');
    $routes->post('detail', 'CashInReportController::detail');
});

$routes->group('api/laporan/hasil-akad', ['namespace' => 'App\Controllers\Api', 'filter' => 'login'], function ($routes) {
    $routes->post('summary', 'HasilAkadReportController::summary');
    $routes->post('detail', 'HasilAkadReportController::detail');
});


$routes->get("/pembayaran/hitungulang", 'Pembayaran::recalculateSummary');

$routes->group('kavling', ['filter' => 'login'], function ($routes) {
    $routes->post('list/ambil', 'Kavling::getList');
});

$routes->group('subkon', ['filter' => 'login'], function ($routes) {
    $routes->post('list/ambil', 'Subkon::getList');
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
$routes->group('pencairan', ['filter' => 'login'], function ($routes) {
    $routes->get('/', 'PencairanJaminan::index');
    $routes->get('list/(:num)', 'PencairanJaminan::list/$1');
    $routes->post('store', 'PencairanJaminan::store');
    $routes->post('cairkan/(:num)', 'PencairanJaminan::cairkan/$1');
    $routes->get('history/(:num)', 'PencairanJaminan::history/$1');
    $routes->post('toggle/(:num)', 'PencairanJaminan::toggleStatus/$1');
    $routes->get('download/(:num)', 'PencairanJaminan::download/$1');
});



$routes->group('api/legal', ['namespace' => 'App\Controllers\Api', 'filter' => 'login'], function($routes) {
    $routes->post('save', 'LegalController::save');
    $routes->post('get', 'LegalController::getDataByID');
    $routes->post('remove', 'LegalController::removeDoc');
    $routes->post('getDoc', 'LegalController::getDoc');
    $routes->post('upload', 'LegalController::upload');
    $routes->post('getListLegalitas', 'LegalController::getListLegalitas');
    $routes->post('edit_others', 'LegalController::edit_others');
});

$routes->group('api/transaksi', ['namespace' => 'App\Controllers\Api', 'filter' => 'login'], function($routes) {
    $routes->post('simpan', 'TransaksiController::saveTransaksi');
    $routes->post('ambilsatu', 'TransaksiController::getByID');
    $routes->post('status/ambilsatu', 'TransaksiController::getStatusById');
    $routes->post('status/simpan', 'TransaksiController::saveStatus');
    $routes->post('konsumen/ganti', 'TransaksiController::replaceConsumer');
});

$routes->group('api/mkdt', ['namespace' => 'App\Controllers\Api', 'filter' => 'login'], function ($routes) {
    $routes->post('history', 'MkdtController::history');
    $routes->post('consumer-replacement-history', 'MkdtController::consumerReplacementHistory');
});

$routes->group('api/tagihan/booking', ['namespace' => 'App\Controllers\Api', 'filter' => ['login', 'csrf']], function ($routes) {
    $routes->post('verifikasi', 'BookingPaymentController::verify');
});

$routes->group('api/referral-qr', ['namespace' => 'App\Controllers\Api', 'filter' => 'login'], function ($routes) {
    $routes->post('preview', 'ReferralQrController::preview');
    $routes->get('download', 'ReferralQrController::download');
});

$routes->group('api/produksi', ['namespace' => 'App\Controllers\Api', 'filter' => 'login'], function ($routes) {
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
});

$routes->group('api/target', ['namespace' => 'App\Controllers\Api', 'filter' => 'login'], function ($routes) {
    $routes->post('list',    'TargetController::list');
    $routes->post('detail',  'TargetController::detail');
    $routes->post('save',    'TargetController::save');
    $routes->post('history', 'TargetController::history');
});

$routes->group('api/riwayat-perubahan', ['namespace' => 'App\Controllers\Api', 'filter' => 'login'], function ($routes) {
    $routes->post('list', 'HistoryController::list');
});

$routes->group('api/tiket-masalah', ['namespace' => 'App\Controllers\Api', 'filter' => 'login'], function ($routes) {
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
    $routes->get('tiket-masalah', '\App\Controllers\Web\TiketMasalahController::index');
    $routes->get('member-get-member', '\App\Controllers\Web\MemberGetMemberController::index');
});

$routes->group('api/mgm', ['namespace' => 'App\Controllers\Api', 'filter' => 'login'], function ($routes) {
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

$routes->group('api/kavling-request', ['namespace' => 'App\Controllers\Api', 'filter' => 'login'], function ($routes) {
    $routes->post('submit', 'KavlingRequestController::submit');
    $routes->post('kavling-list', 'KavlingRequestController::kavlingList');
    $routes->post('list', 'KavlingRequestController::list');
    $routes->post('update-status', 'KavlingRequestController::updateStatus');
});

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// Public API â€” No authentication required
// Throttle: 20 requests / 60 seconds per IP
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
$routes->group('api/public', [
    'namespace' => 'App\Controllers\Api',
    'filter'    => ['throttle:20,60', 'cors'],
], function ($routes) {
    // Handle OPTIONS request untuk CORS preflight
    $routes->options('check-referral', function() {
        return response()->setStatusCode(204);
    });

    // Validasi kode referral â€” POST only
    $routes->post('check-referral', 'PublicController::checkReferral');

    // Blok akses GET agar tidak bisa dibuka langsung di browser
    $routes->get('check-referral', function () {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    });
});




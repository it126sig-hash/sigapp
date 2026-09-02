<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->setAutoRoute(true);

$routes->get('privacy-policy', '\App\Controllers\Web\LegalPageController::privacyPolicy');
$routes->get('toc', '\App\Controllers\Web\LegalPageController::termsOfService');

$routes->get('dashboard', 'Home::dashboard');
$routes->get('/', 'Home::dashboard');

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
$routes->get('/loadaktivitas', 'Home::loadAktivitas');

$routes->get('/profil', 'Profil::index');
$routes->post('/profil/update', 'Profil::update');
$routes->group('google-calendar', ['filter' => 'login'], function ($routes) {
    $routes->get('connect', 'GoogleCalendar::connect');
    $routes->get('callback', 'GoogleCalendar::callback');
    $routes->post('disconnect', 'GoogleCalendar::disconnect');
});

$routes->get('/configcolor', 'PengaturanWarna::index');

$routes->post("/get-dashboard", 'Home::getDashboard');
$routes->post("/home/getMenuItemsJson", 'Home::getMenuItemsJson');

$routes->post("/keuangan/jatuhtempo/get", 'Keuangan::getJatuhTempo');


$routes->post("/getAkases", 'AksesProyek::getAkses');
$routes->get("/aksesproyek", 'AksesProyek::index');


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
});


$routes->group('menu-setting', ['filter' => 'login'], function ($routes) {
    $routes->get('/', 'MenuSetting::index');
    $routes->post('menu/list', 'MenuSetting::menuList');
    $routes->post('menu/get', 'MenuSetting::menuGet');
    $routes->post('menu/save', 'MenuSetting::menuSave');
    $routes->post('menu/toggle', 'MenuSetting::menuToggle');
    $routes->post('access/group', 'MenuSetting::groupAccess');
    $routes->post('access/group/save', 'MenuSetting::groupSave');
    $routes->post('access/user', 'MenuSetting::userAccess');
    $routes->post('access/user/save', 'MenuSetting::userSave');
    $routes->post('siteplan-menu/list', 'MenuSetting::siteplanMenuList');
    $routes->post('siteplan-menu/get', 'MenuSetting::siteplanMenuGet');
    $routes->post('siteplan-menu/save', 'MenuSetting::siteplanMenuSave');
    $routes->post('siteplan-menu/toggle', 'MenuSetting::siteplanMenuToggle');
});

$routes->group('setting-data', ['filter' => 'login'], function ($routes) {
    $routes->get('/', 'SettingData::index');
    $routes->post('list/(:segment)', 'SettingData::list/$1');
    $routes->post('get/(:segment)', 'SettingData::get/$1');
    $routes->post('save/(:segment)', 'SettingData::save/$1');
    $routes->post('delete/(:segment)', 'SettingData::delete/$1');
});

$routes->get("getmodal", 'Modal::index');

$routes->group('files', ['filter' => 'login'], function ($routes) {
    $routes->get('(:segment)/path', 'FileController::path/$1');
    $routes->get('(:segment)/(:num)', 'FileController::show/$1/$2');
    $routes->get('(:segment)/(:num)/thumbnail', 'FileController::thumbnail/$1/$2');
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
$routes->post("/keuangan/pencairan-akad/void", 'PencairanAkad::void');
$routes->get("/keuangan/pencairan-akad/history/(:num)", 'PencairanAkad::history/$1');

$routes->get("/keuangan/hasil-akad/list", 'PencairanAkad::listHasilAkad');
$routes->post("/keuangan/hasil-akad/list/ambil-grouped", 'PencairanAkad::getListGrouped');
$routes->post("/keuangan/hasil-akad/list/detail", 'PencairanAkad::getListDetail');
$routes->get("/keuangan/hasil-akad/export-template", 'PencairanAkad::exportTemplate');
$routes->get("/keuangan/hasil-akad/import", 'PencairanAkad::importForm');
$routes->post("/keuangan/hasil-akad/import", 'PencairanAkad::import');


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
});

$routes->group('api/mkdt', ['namespace' => 'App\Controllers\Api', 'filter' => 'login'], function ($routes) {
    $routes->post('history', 'MkdtController::history');
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

// ─────────────────────────────────────────────────────────────────────────────
// Public API — No authentication required
// Throttle: 20 requests / 60 seconds per IP
// ─────────────────────────────────────────────────────────────────────────────
$routes->group('api/public', [
    'namespace' => 'App\Controllers\Api',
    'filter'    => ['throttle:20,60', 'cors'],
], function ($routes) {
    // Handle OPTIONS request untuk CORS preflight
    $routes->options('check-referral', function() {
        return response()->setStatusCode(204);
    });

    // Validasi kode referral — POST only
    $routes->post('check-referral', 'PublicController::checkReferral');

    // Blok akses GET agar tidak bisa dibuka langsung di browser
    $routes->get('check-referral', function () {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    });
});

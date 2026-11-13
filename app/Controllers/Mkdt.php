<?php

namespace App\Controllers;

use App\Models\MkdtModel;
use App\Models\KavlingModel;
use App\Models\KonsumenModel;
use App\Models\KeuanganModel;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use App\Controllers\Api\Mkdt\MkdtOperationController;
use App\Controllers\Notif;
use App\Controllers\Web\Mkdt\StockController;
use App\Services\FileAccessService;
use App\Services\BookingPaymentService;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * @deprecated Zero-Downtime legacy shim. Use App\Controllers\Web\Mkdt\* or App\Controllers\Api\Mkdt\* (and App\Controllers\Api\Transaksi\* for transaction endpoints).
 */
class Mkdt extends BaseController
{
    protected $db;
    protected $keuModel;
    protected $mkdtModel;
    protected $kavlingModel;
    protected $konsumenModel;
    protected $validation;
    protected $notif;
    protected $fileAccessService;
    protected BookingPaymentService $bookingPaymentService;
    protected StockController $stockController;
    protected MkdtOperationController $operationController;

    public function __construct()
    {
        $this->notif = new Notif();
        $this->keuModel = new KeuanganModel();
        $this->mkdtModel = new MkdtModel();
        $this->kavlingModel = new KavlingModel();
        $this->konsumenModel = new KonsumenModel();
        $this->validation = \Config\Services::validation();
        $this->db = db_connect();
        $this->fileAccessService = new FileAccessService();
        $this->bookingPaymentService = new BookingPaymentService($this->db);
        $this->stockController = new StockController();
        $this->operationController = new MkdtOperationController();
    }

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->stockController->initController($request, $response, $logger);
        $this->operationController->initController($request, $response, $logger);
    }

    /**
     * @deprecated Legacy endpoint. Use App\Controllers\Api\Transaksi\TransaksiController.
     */
    function get_data_by_id($st = null)
    {
        log_message('warning', 'DEPRECATED_MKDT_HIT: ' . __METHOD__ . ' called by user ' . (function_exists('user_id') ? (user_id() ?? 'guest') : 'cli') . '. Use App\Controllers\Api\Transaksi\TransaksiController.');
        $hj = [];
        $id_hargajual = $this->request->getVar('id_hargajual');
        if ($id_hargajual) {
            $q = $this->db->table('hargajual as b')
                ->select('
                    b.id,
                    b.id as harga_akhir,
                    b.tgl_harga,
                    b.row,
                    b.hargajual,
                    b.hargajual_net,
                    b.kpr,
                    b.uang_muka,
                    b.bphtb,
                    b.ppn,
                    b.biaya_proses,
                    b.biaya_adm,
                    a.tipe_rumah as tipe,
                    b.lb,
                    b.lt
                ')
                ->join('tipe as a', 'a.id_tipe = b.id_tipe')
                ->where('id', $id_hargajual)
                ->get()->getResult()[0];
            $hj = $q;
        }


        $condition = ['kavling.id_kavling' => $this->request->getVar('id_kavling')];
        $y = $this->kavlingModel
            ->select('
                    kavling.id_kavling,
                    perintah_bangun,
                    perintah_bangun_tgl,
                    perintah_bangun_file,
                    username,
                ')
            ->join('users', 'users.id = kavling.perintah_bangun_oleh', 'left')
            ->where($condition)
            ->first();
        if ($y && !empty($y->perintah_bangun_file)) {
            $y->perintah_bangun_access_url = $this->fileAccessService->accessUrl('kavling_perintah_bangun', (int) $y->id_kavling);
        }

        $r['perintah_bangun'] = $y;

        $condition = ['mkdt.id_mkdt' => $this->request->getVar('id_mkdt')];
        $x = $this->mkdtModel
            ->select('
                    mkdt.*,
                    konsumen.id_konsumen,
                    konsumen.nama_konsumen,
                    konsumen.no_spptb,
                    konsumen.nik as nik_konsumen,
                    konsumen.npwp as npwp_konsumen,
                    konsumen.email_konsumen,
                    konsumen.file_ktp as ktp_lok,
                    konsumen.file_npwp as npwp_lok,
                    konsumen.file_data_diri as data_diri_lok,
                    konsumen.hp_konsumen,
                    konsumen.alamat_konsumen,
                    konsumen.status_konsumen,
                    konsumen.refund,
                    konsumen.refund_tgl,
                    konsumen.status_pernikahan,
                    konsumen.nama_pasangan,
                    konsumen.nik_pasangan,
                    konsumen.nama_instansi,
                    konsumen.alamat_instansi,
                    konsumen.tel_instansi,
                    konsumen.sales,
                    konsumen.keterangan as keterangan_batal,
                    username,
                    list_bank.bank as nama_bank,
                ')
            ->join('list_bank', 'list_bank.id = mkdt.id_bank', 'left')
            ->join('konsumen', 'konsumen.id_konsumen = mkdt.id_konsumen', 'left')
            ->join('users', 'users.id = mkdt.edit_by', 'left')
            ->where($condition)
            ->first();

        if ($x) {
            if (!empty($x->ktp_lok)) {
                $x->ktp_access_url = $this->fileAccessService->accessUrl('konsumen_ktp', (int) $x->id_konsumen);
            }
            if (!empty($x->npwp_lok)) {
                $x->npwp_access_url = $this->fileAccessService->accessUrl('konsumen_npwp', (int) $x->id_konsumen);
            }
            if (!empty($x->data_diri_lok)) {
                $x->data_diri_access_url = $this->fileAccessService->accessUrl('konsumen_data', (int) $x->id_konsumen);
            }
            if (!empty($x->sp3k_file)) {
                $x->sp3k_access_url = $this->fileAccessService->accessUrl('mkdt_sp3k', (int) $x->id_mkdt);
            }
            if (!empty($x->bast_file)) {
                $x->bast_access_url = $this->fileAccessService->accessUrl('mkdt_bast', (int) $x->id_mkdt);
            }
            $r['data'] = $x;
            $r['hj'] = $hj;
            $r['token'] = csrf_hash();
            $r['tagihan'] = $this->keuModel
                ->select("*")
                ->where("id_mkdt", $x->id_mkdt)
                ->find();
        } else {
            $r['data'] = null;
            $r['token'] = csrf_hash();
            $r['hj'] = $hj;
        }

        return $this->response->setJSON($r);
    }

    /**
     * @deprecated Legacy shim. Use App\Controllers\Api\Mkdt\MkdtOperationController::simpanBatal().
     */
    function simpan_batal()
    {
        return $this->operationController->simpanBatal();
    }

    /**
     * @deprecated Legacy shim. Use App\Controllers\Api\Mkdt\MkdtOperationController::getBatal().
     */
    function batal_mkdt()
    {
        return $this->operationController->getBatal();
    }
    // function inst(){
    //     $ft['berita_acara'] = "ewean kuda";
    //     $ft['jatuh_tempo_tgl'] = date("Y-m-d hi:i:s");
    //     $ft['nominal'] = 666;
    //     $ft['id_mkdt'] = 99;
    //     $ft['add_by'] = 1;
    //     $ft['edit_by'] = 1;

    //     $this->keuModel->insert($ft);

    //     // $this->db->table("keuangan")->insert($ft);
    // }
    /**
     * @deprecated Legacy endpoint. Use App\Controllers\Api\Transaksi\TransaksiController.
     */
    function save()
    {
        log_message('warning', 'DEPRECATED_MKDT_HIT: ' . __METHOD__ . ' called by user ' . (function_exists('user_id') ? (user_id() ?? 'guest') : 'cli') . '. Use App\Controllers\Api\Transaksi\TransaksiController.');
        $response['token'] = csrf_hash();
        $id_kavling = $this->request->getVar('id_kavling');

        $databaru = $this->request->getVar('mkdt_data_baru');
        // var_dump($this->request->getVar());die();

        $f['id_kavling'] = $id_kavling; //id_kavling untuk table konsumen
        $id_konsumen = $this->request->getPost('id_konsumen');

        $f['id_mkdt'] = $this->request->getPost('id_mkdt');

        if (! empty($f['id_mkdt'])) {
            try {
                $this->bookingPaymentService->assertEditable(
                    (int) $f['id_mkdt'],
                    (float) $this->num($this->request->getPost('booking_fee')),
                    $this->request->getPost('booking_tgl')
                );
            } catch (\DomainException $e) {
                return $this->response->setJSON(['token'=>csrf_hash(),'success'=>false,'messages'=>$e->getMessage()]);
            }
        }

        $uniqid = uniqid('', true);


        if ($databaru == 1)
            $f['id_mkdt'] = null;

        $before_upadte = $this->db->table("mkdt")->where("id_mkdt", $f['id_mkdt'])->get()->getRow();



        $f['nama_konsumen'] = $this->request->getPost('nama_konsumen');
        $f['email_konsumen'] = $this->request->getPost('email_konsumen');
        $f['no_spptb'] = $this->request->getPost('no_spptb');
        $f['alamat_konsumen'] = $this->request->getPost('alamat_konsumen');
        $f['nik'] = $this->request->getPost('nik_konsumen');
        $f['npwp'] = $this->request->getPost('npwp_konsumen');
        $f['hp_konsumen'] = $this->request->getPost('hp_konsumen');
        $f['status_konsumen'] = $this->request->getPost('status_konsumen');

        $f['status_pernikahan'] = $this->request->getPost('status_pernikahan');
        $f['nama_pasangan'] = $this->request->getPost('nama_pasangan');
        $f['nik_pasangan'] = $this->request->getPost('nik_pasangan');

        $f['nama_instansi'] = $this->request->getPost('nama_instansi');
        $f['alamat_instansi'] = $this->request->getPost('alamat_instansi');
        $f['tel_instansi'] = $this->request->getPost('tel_instansi');

        $f['sales'] = $this->request->getPost('sales');

        //jika status batal
        if ($this->request->getVar("status_mkdt") == "Batal") {
            $f['keterangan'] = $this->request->getVar('keterangan_batal');
            // $f['refund'] = $this->num($this->request->getVar('refund'));
            // $f['refund_tgl'] = $this->request->getVar('refund_tgl');

            //jika batal input uang refund ke log pembayaran
            // if ($id_konsumen) {
            //     $kav = $this->get_kavling_konsumen($id_kavling);
            //     $data = array(
            //         "id_kavling" => $id_kavling,
            //         "nominal" => $f['refund'],
            //         "tanggal_bayar" => $f['refund_tgl'],
            //         "keterangan" => "Refund: " . $kav->nama_konsumen . " - " . $kav->nama_jalan . " No. " . $kav->no_kavling . "",
            //         "add_by" => user_id(),
            //         "edit_by" => user_id()
            //     );
            //     $this->lpModel->insert($data);
            // }
        }

        /************************ upload KTP *****************************/
        if ($this->request->getFile('file_ktp')->getSize() > 0) {
            $img = $this->request->getFile('file_ktp');

            $name = $img->getRandomName();

            $lok = 'uploads/konsumen/k/' . date('Ymd') . '/';

            $this->fileAccessService->storeAs($img, $lok, $name);

            $f['file_ktp'] = $lok . $name;
        }

        /************************ upload NPWP *****************************/
        if ($this->request->getFile('file_npwp')->getSize() > 0) {
            $img2 = $this->request->getFile('file_npwp');

            $name = $img2->getRandomName();

            $lok = 'uploads/konsumen/n/' . date('Ymd') . '/';

            $this->fileAccessService->storeAs($img2, $lok, $name);

            $f['file_npwp'] = $lok . $name;
        }

        /************************ upload data diri *****************************/
        if ($this->request->getFile('file_data_diri')->getSize() > 0) {
            $img3 = $this->request->getFile('file_data_diri');

            $name = $img3->getRandomName();

            $lok = 'uploads/konsumen/d/' . date('Ymd') . '/';

            $this->fileAccessService->storeAs($img3, $lok, $name);

            $f['file_data_diri'] = $lok . $name;
        }

        //cek jika sudah ada konsumen atau tidak pada kavling
        if ($id_konsumen == null || $id_konsumen == '') {

            $f['add_by'] = user_id();
            $f['created_at'] = date('Y-m-d H:i:s');

            if ($this->konsumenModel->insert($f)) {
                $id_konsumen = $this->konsumenModel->getInsertID();

                //insert ke log
                $notif = 'Menambahkan konsumen baru';
                $this->notif->tambah_notif("4;9", $notif, user_id(), $id_kavling, $id_konsumen, \App\Enums\NotificationEvent::KONSUMEN_BARU, null, "siteplan/view?id_kavling=" . $id_kavling . "&tab=konsumen"); //4 mkdt 9 direksi
            } else {
                $response['success'] = false;
                $response['messages'] = 'Terjadi kesaahan saat melakukan penambahan konsumen';
                return $this->response->setJSON($response);
            }
        } else {
            $f['edit_by'] = user_id();
            $f['updated_at'] = date('Y-m-d H:i:s');
            if (!$this->konsumenModel->update($id_konsumen, $f)) {
                $response['success'] = false;
                $response['messages'] = 'Terjadi kesaahan saat melakukan perubahan data konsumen';
                return $this->response->setJSON($response);
            }
            // else{
            //     //insert ke log
            //     $notif = 'Melakukan perubahan pada data konsumen';
            //     $this->notif->tambah_notif("4;9", $notif, user_id(), $id_kavling, $id_konsumen, null, null, "siteplan/view?id_kavling=" . $id_kavling . "&tab=konsumen"); //4 mkdt 9 direksi
            // }
        }

        //jika perintah bangun checked


        $before_upadte_pb = $this->db->table("kavling")->where("id_kavling", $id_kavling)->get()->getRow();
        if ($this->request->getVar('perintah_bangun') == 1) {

            if ($before_upadte_pb->perintah_bangun == 0) {
                //insert log
                $notif = 'Terbit perintah bangun pada : ' . date_format(date_create($this->request->getVar('perintah_bangun_tgl')), "d-M-Y");
                $this->notif->tambah_notif("7;4;9", $notif, user_id(), $id_kavling, $id_konsumen, \App\Enums\NotificationEvent::PERINTAH_BANGUN, null, "siteplan/view?id_kavling=" . $id_kavling . "&tab=konsumen"); //4 mkdt 9 direksi 7 produksi
            }

            /************************ upload perintah bangun *****************************/
            if ($this->request->getFile('perintah_bangun_file')->getSize() > 0) {
                $img = $this->request->getFile('perintah_bangun_file');

                $name = $img->getRandomName();

                $lok = 'uploads/perintah_bangun/' . date('Ymd') . '/';

                $this->fileAccessService->storeAs($img, $lok, $name);

                $f2['perintah_bangun_file'] = $lok . $name;
            }


            $f2['perintah_bangun'] = 1;
            $f2['perintah_bangun_tgl'] = $this->request->getVar('perintah_bangun_tgl');
            $f2['perintah_bangun_oleh'] = user_id();

            $this->kavlingModel->update($id_kavling, $f2);
        }
        $f2 = [];

        //jika wawancara checked
        $f2['wawancara'] = 0;
        $f2['wawancara_tgl'] = null;

        if ($this->request->getVar('wawancara') == 1) {

            if ($before_upadte && $before_upadte->wawancara == 0) {
                $notif = 'Telah melakukan wawancara pada : ' . date_format(date_create($this->request->getVar('wawancara_tgl')), "d-M-Y");
                $this->notif->tambah_notif("4;9", $notif, user_id(), $id_kavling, $id_konsumen, \App\Enums\NotificationEvent::WAWANCARA, null, "siteplan/view?id_kavling=" . $id_kavling . "&tab=konsumen");
            }

            $f2['wawancara'] = 1;
            $f2['wawancara_tgl'] = $this->request->getPost('wawancara_tgl');
        }

        //status akad
        $f2['status_mkdt'] = $this->num($this->request->getPost('status_mkdt'));

        //jika akad checked
        $f2['akad'] = 0;
        $f2['akad_tgl'] = null;
        if ($this->request->getVar('akad') == 1) {

            if ($before_upadte && $before_upadte->akad == 0) {
                //insert log
                $notif = 'Telah melakukan akad pada : ' . date_format(date_create($this->request->getVar('wawancara_tgl')), "d-M-Y");
                $this->notif->tambah_notif("3;5;8;4;9", $notif, user_id(), $id_kavling, $id_konsumen, \App\Enums\NotificationEvent::AKAD, null, "siteplan/view?id_kavling=" . $id_kavling . "&tab=konsumen"); //4 mkdt 9 direksi 7 produksi 3 keuangan 8 sales
            }
            /************************ upload bast *****************************/
            if ($this->request->getFile('bast_file')->getSize() > 0) {
                $img = $this->request->getFile('bast_file');

                $name = $img->getRandomName();

                $lok = 'uploads/bast_file/' . date('Ymd') . '/';

                $this->fileAccessService->storeAs($img, $lok, $name);

                $f2['bast_file'] = $lok . $name;
            }

            $f2['akad'] = 1;
            $f2['is_ajb'] = $this->request->getPost('is_ajb');
            $f2['notaris'] = $this->request->getPost('notaris');
            $f2['debitur_no'] = $this->request->getPost('debitur_no');
            $f2['bast_no'] = $this->request->getPost('bast_no');
            $f2['akad_tgl'] = $this->request->getPost('akad_tgl');
            $f2['status_mkdt'] = "Akad"; //jika akad di ceklis
        }

        //jika sp3k checked
        $f2['sp3k'] = 0;
        $f2['sp3k_no'] = $this->request->getPost('sp3k_no');
        $f2['sp3k_tgl'] = $this->request->getPost('sp3k_tgl');
        $f2['sp3k_tgl_exp'] = $this->request->getPost('sp3k_tgl_exp');
        if ($this->request->getPost('sp3k_tgl') != '' && $this->request->getPost('sp3k_tgl_exp') != '') {
            $f2['sp3k'] = 1;
        }
        /************************ upload sp3k *****************************/
        if ($this->request->getFile('sp3k_file')->getSize() > 0) {
            $img = $this->request->getFile('sp3k_file');

            $name = $img->getRandomName();

            $lok = 'uploads/sp3k_file/' . date('Ymd') . '/';

            $this->fileAccessService->storeAs($img, $lok, $name);

            $f2['sp3k_file'] = $lok . $name;
        }

        $is_kpr = ($this->num($this->request->getPost('is_kpr')) > 0) ? 1 : 0;

        //detail data mkdt
        $f2['id_mkdt'] = $f['id_mkdt'];
        $f2['id_konsumen'] = $id_konsumen;
        // $f2['booking_paid'] = $this->num($this->request->getPost('booking_paid'));

        $f2['booking_fee'] = $this->num($this->request->getPost('booking_fee'));
        $f2['booking_tgl'] = $this->num($this->request->getPost('booking_tgl'));

        $f2['id_hargajual'] = $this->request->getPost('mkdt-harga_akhir');


        // $f2['harga_uang_muka'] = $this->num($this->request->getPost('harga_uang_muka'));
        $f2['harga_kpr'] = $this->num($this->request->getPost('harga_kpr'));
        $f2['harga_kpr_acc'] = $this->num($this->request->getPost('acc_harga_kpr')); // harga acc kpr
        $f2['harga_penambahan_um'] = $this->num($this->request->getPost('harga_turun_kpr')); //turun kpr
        $f2['is_kpr'] = $is_kpr;
        $f2['is_subsidi'] = $this->request->getPost('is_subsidi');
        $f2['jenis_subsidi'] = $this->request->getPost('jenis_subsidi');
        $f2['bank'] = $this->request->getPost('bank');
        $f2['id_bank'] = $this->request->getPost('id_bank');
        $f2['keterangan'] = $this->request->getPost('mkdt_keterangan');
        $f2['harga_total'] = $this->num($this->request->getPost('total_biaya'));

        $f2['id_kavling'] = $id_kavling;

        $f2['rencana_akad_tgl'] = $this->request->getPost('rencana_akad_tgl');


        //jika booking
        // if ($f2['status_mkdt'] == "Booking" && $f['id_mkdt'] == null || $f2['status_mkdt'] == "Booking" && $f['id_mkdt'] == "") {
        //     $kav = $this->get_kavling($id_kavling);
        //     $data = array(
        //         "id_kavling" => $id_kavling,
        //         "nominal" => $this->num($f2['booking_fee']),
        //         "tanggal_bayar" => $f2['booking_tgl'],
        //         "keterangan" => "Booking: " . $f['nama_konsumen'] . " - " . $kav->nama_jalan . " No. " . $kav->no_kavling . "",
        //         "add_by" => user_id(),
        //         "edit_by" => user_id()
        //     );

        //     $this->lpModel->insert($data);
        // }

        $id_mkdt = $f2['id_mkdt'];

        $this->db->transException(true)->transBegin();
        try {
        if ($f2['id_mkdt'] == null) {
            $f2['add_by'] = user_id();
            $f2['edit_by'] = user_id();

            $f2['uniq_id'] = $uniqid;


            // $f2['harga_jual'] = $this->num($this->request->getPost('harga_jual'));
            // $f2['harga_diskon'] = $this->num($this->request->getPost('harga_diskon'));
            // $f2['harga_penambahan'] = $this->num($this->request->getPost('harga_penambahan'));
            // $f2['harga_administrasi'] = $this->num($this->request->getPost('harga_administrasi'));
            // $f2['harga_ppn'] = $this->num($this->request->getPost('harga_ppn'));
            // $f2['harga_bphtb'] = $this->num($this->request->getPost('harga_bphtb'));
            // $f2['harga_biaya_proses'] = $this->num($this->request->getPost('harga_biaya_proses'));

            if ($this->mkdtModel->insert($f2)) {
                $id_mkdt = $this->mkdtModel->getInsertID();


                if ($f2['sp3k'] == 1) {
                    $notif = 'Melakukan perubahan pada tanggal terbit SP3K (' . date_format(date_create($f2['sp3k_tgl']), "d-M-Y") . ') dan exp (' . date_format(date_create($f2['sp3k_tgl_exp']), "d-M-Y") . ') ';
                    $this->notif->tambah_notif("4;9", $notif, user_id(), $id_kavling, $id_konsumen, \App\Enums\NotificationEvent::DATA_KONSUMEN_UPDATE, null, "siteplan/view?id_kavling=" . $id_kavling . "&tab=konsumen"); //4 mkdt 9 direksi
                }

                //update id_mkdt di tbl kav
                $this->kavlingModel->update($this->request->getVar('id_kavling'), array('id_mkdt' => $id_mkdt));

                $response['success'] = true;
                $response['messages'] = 'Data berhasil ditambah';
            } else {
                $response['success'] = false;
                $response['messages'] = 'Terjadi Kesalahan';
            }
        } else {
            $f2['edit_by'] = user_id();

            $old_data = $this->mkdtModel->find($f['id_mkdt']);

            if ($this->mkdtModel->update($f['id_mkdt'], $f2)) {

                if ($f2['sp3k'] == 1) {
                    if ($old_data->sp3k_tgl != $f2['sp3k_tgl']) {
                        $notif = 'Melakukan perubahan pada tanggal terbit SP3K (' . date_format(date_create($f2['sp3k_tgl']), "d-M-Y") . ') dan exp (' . date_format(date_create($f2['sp3k_tgl_exp']), "d-M-Y") . ') ';
                        $this->notif->tambah_notif("4;9", $notif, user_id(), $id_kavling, $id_konsumen, \App\Enums\NotificationEvent::DATA_KONSUMEN_UPDATE, null, "siteplan/view?id_kavling=" . $id_kavling . "&tab=konsumen");
                    }
                }

                $response['success'] = true;
                $response['messages'] = 'Data berhasil diperbaharui';
            } else {
                $response['success'] = false;
                $response['messages'] = 'Terjadi Kesalahan';
            }
        }

        if (! empty($id_mkdt)) $this->bookingPaymentService->synchronize((int) $id_mkdt, (int) user_id());
        if ($this->db->transStatus() === false) throw new \RuntimeException('Transaksi database gagal.');
        $this->db->transCommit();
        } catch (\Throwable $e) {
            $this->db->transRollback();
            $response['success'] = false;
            $response['messages'] = 'Gagal menyimpan booking: ' . $e->getMessage();
        }

        return $this->response->setJSON($response);
    }
    /**
     * @deprecated Legacy endpoint. Use App\Controllers\Api\Transaksi\HargajualController::set_harga().
     */
    function set_harga()
    {
        log_message('warning', 'DEPRECATED_MKDT_HIT: ' . __METHOD__ . ' called by user ' . (function_exists('user_id') ? (user_id() ?? 'guest') : 'cli') . '. Use App\Controllers\Api\Transaksi\HargajualController::set_harga().');
        $response = array();
        $response['token'] = csrf_hash();

        $fields['id_kavling'] = $this->request->getVar('id_kavling');
        $fields['harga_akhir'] = $this->num($this->request->getVar('harga'));
        $fields['harga_akhir_tgl'] = date('Y-m-d');
        $fields['harga_akhir_oleh'] = user_id();
        $fields['edit_by'] = user_id();

        $id = explode(";", $this->request->getPost('id_kavling'));
        $id_last = $id[count($id) - 1];
        $id_len = ($id_last == "") ? count($id) - 1 : count($id);

        $this->validation->setRules([
            'harga' => ['label' => 'Harga', 'rules' => 'permit_empty|max_length[255]']
        ]);


        if ($id_len > 0) {
            for ($x = 0; $x < $id_len; $x++) {

                $fields['id_kavling'] = $id[$x];


                if ($this->validation->run($fields) == FALSE) {
                    $response['success'] = false;
                    $response['messages'] = $this->validation->listErrors();
                } else {
                    if ($this->kavlingModel->update($fields['id_kavling'], $fields)) {
                        $response['success'] = true;
                        $response['messages'] = 'Successfully updated';
                    } else {
                        $response['success'] = false;
                        $response['messages'] = 'Update error!';
                    }
                }
            }
        } else {
            if ($this->validation->run($fields) == FALSE) {
                $response['success'] = false;
                $response['messages'] = $this->validation->listErrors();
            } else {

                if ($this->kavlingModel->update($fields['id_kavling'], $fields)) {

                    $response['success'] = true;
                    $response['messages'] = 'Successfully updated';
                } else {

                    $response['success'] = false;
                    $response['messages'] = 'Update error!';
                }
            }
        }
        return $this->response->setJSON($response);
    }

    /**
     * @deprecated Legacy shim. Use App\Controllers\Web\Mkdt\StockController::index().
     */
    function list_stock()
    {
        return $this->stockController->index();
    }

    /**
     * @deprecated Legacy shim. Use App\Controllers\Api\Mkdt\MkdtOperationController::getListStock().
     */
    function getListStock()
    {
        return $this->operationController->getListStock();
    }

    /**
     * @deprecated Legacy orphan public helper. No active callers; kept for Zero-Downtime observation.
     */
    function if_where($var, $column, $condition, $query)
    {
        log_message('warning', 'DEPRECATED_MKDT_HIT: ' . __METHOD__ . ' called by user ' . (function_exists('user_id') ? (user_id() ?? 'guest') : 'cli') . '.');
        $x = 0;
        foreach ($column as $i) {
            if ($x === 0) {
                $query->like($i, $var['search']['value']);
            } else {
                $query->orLike($i, $var['search']['value']);
            }
            $query->where($condition);
            $x++;
        }
        return $query;
    }

    /**
     * @deprecated Internal helper used only by deprecated save()/set_harga().
     */
    protected function num($d)
    {
        $d = str_replace('.', "", $d);
        $d = str_replace(',', "", $d);

        return $d;
    }

    /**
     * @deprecated Legacy orphan public helper. No active callers; kept for Zero-Downtime observation.
     */
    function format_tgl($tgl)
    {
        log_message('warning', 'DEPRECATED_MKDT_HIT: ' . __METHOD__ . ' called by user ' . (function_exists('user_id') ? (user_id() ?? 'guest') : 'cli') . '.');
        if ($tgl == "" || $tgl == "0000-00-00" || $tgl == null)
            return "-";
        return date_format(date_create($tgl), "d-M-Y");
    }

    /**
     * @deprecated Legacy orphan public helper. No active callers; kept for Zero-Downtime observation.
     */
    function is_active($id, $texts, $textf)
    {
        log_message('warning', 'DEPRECATED_MKDT_HIT: ' . __METHOD__ . ' called by user ' . (function_exists('user_id') ? (user_id() ?? 'guest') : 'cli') . '.');
        $r = '<span class="btn btn-primary btn-sm" text-capitalized="">' . $textf . '</span>';
        if ($id == "1")
            $r = '<span class="btn btn-success btn-sm" text-capitalized="">' . $texts . '</span>';
        return $r;
    }

    /**
     * @deprecated Legacy shim. Use App\Controllers\Api\Mkdt\MkdtOperationController::getSi().
     */
    function getsi()
    {
        return $this->operationController->getSi();
    }

    /**
     * @deprecated Legacy shim. Use App\Controllers\Api\Mkdt\MkdtOperationController::saveSi().
     */
    function saveSI()
    {
        return $this->operationController->saveSi();
    }

    /******************************** export *******************************/
    /**
     * @deprecated Legacy endpoint. No modular target exists yet; kept for Zero-Downtime observation.
     */
    public function export_xlsx()
    {
        log_message('warning', 'DEPRECATED_MKDT_HIT: ' . __METHOD__ . ' called by user ' . (function_exists('user_id') ? (user_id() ?? 'guest') : 'cli') . '. No modular replacement yet.');
        $table = $this->request->getVar('table');

        // $spreadsheet = new Spreadsheet();

        $firstHtmlString = '<table>' . $table . '</table>';

        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Html();
        $spreadsheet = $reader->loadFromString($firstHtmlString);
        $reader->setSheetIndex(1);
        // $spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xls');
        // $writer->save('write.xls');

        // $sheet = $spreadsheet->getActiveSheet();
        // $sheet->setCellValue('A1', 'Employee Name');
        // $sheet->setCellValue('B1', 'Email Address');
        // $sheet->setCellValue('C1', 'Mobile No.');
        // $sheet->setCellValue('D1', 'Department');

        // $count = 2;

        // foreach ($data as $row) {
        //     $sheet->setCellValue('A' . $count, $row['employee_name']);

        //     $sheet->setCellValue('B' . $count, $row['employee_email']);

        //     $sheet->setCellValue('C' . $count, $row['employee_mobile']);

        //     $sheet->setCellValue('D' . $count, $row['employee_department']);

        //     $count++;
        // }
        ob_start();
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        $xlsData = ob_get_contents();
        ob_end_clean();
        $response = array(
            'status' => TRUE,
            'file' => "data:application/vnd.ms-excel;base64," . base64_encode($xlsData)
        );

        return $this->response->setJSON($response);
    }
}

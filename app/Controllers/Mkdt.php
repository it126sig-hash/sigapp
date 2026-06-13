<?php

namespace App\Controllers;

use App\Models\MkdtModel;
use App\Models\KavlingModel;
use App\Models\KonsumenModel;
use App\Models\LogPembayaranModel;
use CodeIgniter\HTTP\Response;
use App\Models\KeuanganModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use App\Controllers\Notif;
use App\Services\FileAccessService;
use App\Services\MkdtHistoryService;

class Mkdt extends BaseController
{
    protected $db;
    protected $keuModel;
    protected $mkdtModel;
    protected $kavlingModel;
    protected $konsumenModel;
    protected $validation;
    protected $lpModel;
    protected $notif;
    protected $username;
    protected $fileAccessService;
    protected $mkdtHistoryService;

    public function __construct()
    {
        $this->notif = new Notif();
        $this->keuModel = new KeuanganModel();
        $this->mkdtModel = new MkdtModel();
        $this->kavlingModel = new KavlingModel();
        $this->konsumenModel = new KonsumenModel();
        $this->validation = \Config\Services::validation();
        $this->lpModel = new LogPembayaranModel();
        $this->db = db_connect();
        $this->username = $this->db->table('users')->select('username')->get()->getRow();
        $this->fileAccessService = new FileAccessService();
        $this->mkdtHistoryService = new MkdtHistoryService();
    }
    function get_data_by_id($st = null)
    {
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

    function simpan_batal()
    {
        $response['token'] = csrf_hash();
        $id_mkdt = $this->request->getVar('batal-id_mkdt');
        $id_kavling = $this->request->getVar('batal-id_kavling');
        $id_konsumen = $this->request->getVar('batal-id_konsumen');


        foreach (user()->getRoles() as $key => $val) {
            $roleid = $key;
        }

        if ($roleid == 3) {
            $f['keuangan_batal_oleh'] = user_id();
            $f['keuangan_batal_tgl'] = date('Y-m-d H:i:s');
        } else {
            $f['mkdt_batal_oleh'] = user_id();
            $f['mkdt_batal_tgl'] = date('Y-m-d H:i:s');
        }

        $f['keterangan_batal'] = $this->request->getPost('batal-keterangan_batal'); //keterangan batal dari mkdt
        $f['perlu_refund'] = (int) (($this->request->getPost('batal-perlu_refund') ?? 0) == 1);

        $f['is_batal'] = 1; //status batal dari mkdt
        $f['is_batal'] = 1; //status batal dari mkdt

        $f['status_mkdt'] = "Batal"; //status batal dari mkdt

        $q = $this->db->table('mkdt')->update($f, ['id_mkdt' => $id_mkdt]);
        /************************ upload KTP *****************************/
        if ($this->request->getFile('file_surat_batal')->getSize() > 0) {
            $img = $this->request->getFile('file_surat_batal');

            $name = $img->getRandomName();

            $lok = 'uploads/batal/' . date('Ymd') . '/';

            $this->fileAccessService->storeAs($img, $lok, $name);

            $f['surat_batal'] = $lok . $name;
        }

        if ($q) {
            //insert ke log
            $notif = 'Membatalkan booking';
            $this->notif->tambah_notif("3;4;9", $notif, user_id(), $id_kavling, $id_konsumen); //4 mkdt 9 direksi

            $this->mkdtHistoryService->log(
                (int) $id_kavling,
                (int) $id_mkdt,
                MkdtHistoryService::ACTION_BATAL_BOOKING,
                $this->mkdtHistoryService->buildBatalSummary($f),
                null,
                $f,
                user_id()
            );

            $response['success'] = true;
            $response['messages'] = '';
        } else {
            $response['success'] = false;
            $response['messages'] = 'Terjadi kesalahan saat melakukan penambahan konsumen';
        }
        return $this->response->setJSON($response);
    }

    function batal_mkdt()
    {
        $hj = [];
        $id_hargajual = $this->request->getVar('id_hargajual');
        $id_kavling = $this->request->getVar('id_kavling');
        $id_mkdt = $this->request->getVar('id_mkdt');



        if ($id_mkdt) {
            $r = (object) [];
            $x = $this->mkdtModel
                ->select('
                    mkdt.*,

                    konsumen.no_spptb,
                    konsumen.nama_konsumen, 
                    konsumen.nik as nik_konsumen,
                    konsumen.npwp as npwp_konsumen,
                    konsumen.email_konsumen,
                    konsumen.hp_konsumen,
                    konsumen.alamat_konsumen,
                    konsumen.status_konsumen,
                    konsumen.sales,
                    username as mkdt_batal_oleh_u,
                ')
                ->join('konsumen', 'konsumen.id_konsumen = mkdt.id_konsumen')
                ->join('users', 'users.id = mkdt.mkdt_batal_oleh', 'left')
                ->where('id_mkdt', $id_mkdt)
                ->first();

            if ($x) {
                $total_um = $x->harga_uang_muka + $x->harga_administrasi - $x->harga_penambahan_um - $x->harga_diskon_uang_muka;
                $total_bb = $x->harga_ppn + $x->harga_bphtb + $x->harga_biaya_proses;

                $r->data = $x;
                $r->token = csrf_hash();

                $sudah_bayar = $this->db->table('log_pembayaran')
                    ->select("*")
                    ->where("id_mkdt", $x->id_mkdt)
                    ->get()->getResult();
                $sb_um = 0;
                $sb_bb = 0;
                foreach ($sudah_bayar as $v) {
                    if ($v->payment_type == 'UangMuka')
                        $sb_um += $v->nominal;
                    elseif ($v->payment_type == 'BiayaBiaya')
                        $sb_bb += $v->nominal;
                }

                $r->sudah_bayar = [
                    'uang_muka' => $sb_um,
                    'biaya_biaya' => $sb_bb,
                ];
                $r->total_biaya = [
                    'uang_muka' => $total_um,
                    'biaya_biaya' => $total_bb,
                ];
            } else {
                $r['data'] = null;
                $r['token'] = csrf_hash();
                $r['sudah_bayar'] = [
                    'uang_muka' => 0,
                    'biaya_biaya' => 0,
                ];
                $r['total_biaya'] = [
                    'uang_muka' => 0,
                    'biaya_biaya' => 0,
                ];
            }
        } else {
            $r['data'] = null;
            $r['token'] = csrf_hash();
            $r['sudah_bayar'] = [
                'uang_muka' => 0,
                'biaya_biaya' => 0,
            ];
            $r['total_biaya'] = [
                'uang_muka' => 0,
                'biaya_biaya' => 0,
            ];
        }
        return $this->response->setJSON($r);
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
    function save()
    {
        $response['token'] = csrf_hash();
        $id_kavling = $this->request->getVar('id_kavling');

        $databaru = $this->request->getVar('mkdt_data_baru');
        // var_dump($this->request->getVar());die();

        $f['id_kavling'] = $id_kavling; //id_kavling untuk table konsumen
        $id_konsumen = $this->request->getPost('id_konsumen');

        $f['id_mkdt'] = $this->request->getPost('id_mkdt');

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
                $this->notif->tambah_notif("4;9", $notif, user_id(), $id_kavling, $id_konsumen); //4 mkdt 9 direksi
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
            //     $this->notif->tambah_notif("4;9", $notif, user_id(), $id_kavling, $id_konsumen); //4 mkdt 9 direksi
            // }
        }

        //jika perintah bangun checked


        $before_upadte_pb = $this->db->table("kavling")->where("id_kavling", $id_kavling)->get()->getRow();
        if ($this->request->getVar('perintah_bangun') == 1) {

            if ($before_upadte_pb->perintah_bangun == 0) {
                //insert log
                $notif = 'Terbit perintah bangun pada : ' . date_format(date_create($this->request->getVar('perintah_bangun_tgl')), "d-M-Y");
                $this->notif->tambah_notif("7;4;9", $notif, user_id(), $id_kavling, $id_konsumen); //4 mkdt 9 direksi 7 produksi
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
                $this->notif->tambah_notif("4;9", $notif, user_id(), $id_kavling, $id_konsumen);
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
                $this->notif->tambah_notif("3;5;8;4;9", $notif, user_id(), $id_kavling, $id_konsumen); //4 mkdt 9 direksi 7 produksi 3 keuangan 8 sales
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
                    $this->notif->tambah_notif("4;9", $notif, user_id(), $id_kavling, $id_konsumen); //4 mkdt 9 direksi
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
                        $this->notif->tambah_notif("4;9", $notif, user_id(), $id_kavling, $id_konsumen);
                    }
                }

                $response['success'] = true;
                $response['messages'] = 'Data berhasil diperbaharui';
            } else {
                $response['success'] = false;
                $response['messages'] = 'Terjadi Kesalahan';
            }
        }

        return $this->response->setJSON($response);
    }
    function set_harga()
    {
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
    protected function get_kavling($id_kavling)
    {
        return $q = $this->kavlingModel
            ->select('
                        kavling.*, 
                        jalan.nama_jalan, 
                        cluster.id_cluster, 
                        cluster.nama_cluster,
                        tipe.id_tipe,
                        tipe.tipe_rumah,
                        tipe.no_tipe_rumah,
                        tipe.id_gambar_kerja,
                        config_shape.config_name, 
                        config_shape.fill, 
                        config_shape.stroke, 
                        config_shape.strokeWidth, 
                        config_shape.dashed, 
                        config_shape.keterangan as conf_ket')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster')
            ->join('proyek', 'proyek.id_proyek = cluster.id_proyek')
            ->join('tipe', 'kavling.id_tipe = tipe.id_tipe')
            // ->join('config_shape', 'config_shape.id_config = kavling.'.$conf)
            ->join('config_shape', 'config_shape.id_config = kavling.id_config')
            ->where('kavling.id_kavling', $id_kavling)
            ->first();
    }
    protected function get_kavling_konsumen($id_kavling)
    {
        return $q = $this->kavlingModel
            ->select('
                        kavling.*, 
                        konsumen.nama_konsumen,
                        jalan.nama_jalan, 
                        cluster.id_cluster, 
                        cluster.nama_cluster,
                        tipe.id_tipe,
                        tipe.tipe_rumah,
                        tipe.no_tipe_rumah,
                        tipe.id_gambar_kerja,
                       ')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster')
            ->join('proyek', 'proyek.id_proyek = cluster.id_proyek')
            ->join('tipe', 'kavling.id_tipe = tipe.id_tipe')
            ->join('mkdt', 'kavling.id_mkdt = mkdt.id_mkdt')
            ->join('konsumen', 'konsumen.id_konsumen = mkdt.id_konsumen')
            ->where('kavling.id_kavling', $id_kavling)
            ->first();
    }
    // function list_kavling_akad()
    // {
    //     $data['content'] = 'kavling/list-kavling-akad';
    //     $data['data']['controller'] = 'Mkdt';
    //     $data['data']['title'] = 'List Kavling';

    //     return view('template', $data);
    // }
    // function getListKavlingAkad()
    // {
    //     $data['token'] = csrf_hash();
    //     $data['data'] = array();

    //     $var = $this->request->getVar();

    //     $colum = ['nama_konsumen', 'nama_jalan', 'no_kavling'];
    //     $condition = [
    //         'mkdt.status_mkdt ' => "Akad"
    //     ];

    //     if ($var['sp3k'] != "")
    //         $condition = array_merge($condition, ["sp3k" => $var['sp3k']]);
    //     if ($var['wawancara'] != "")
    //         $condition = array_merge($condition, ["wawancara" => $var['wawancara']]);
    //     if ($var['akad'] != "")
    //         $condition = array_merge($condition, ["akad" => $var['akad']]);

    //     //get mkdt query 
    //     $query = $this->db->table('mkdt')
    //         ->select('
    //             mkdt.*,
    //             kavling.no_kavling,
    //             kavling.luas_tanah,

    //             jalan.id_jalan,
    //             jalan.nama_jalan,
    //             cluster.id_cluster,
    //             cluster.nama_cluster,
    //             proyek.id_proyek,
    //             proyek.nama_proyek,
    //             tipe.tipe_rumah,
    //             produksi.progres_bangunan,
    //             produksi.st_air,
    //             produksi.st_jalan,
    //             produksi.lpa,

    //             konsumen.nama_konsumen,
    //             konsumen.hp_konsumen,
    //             konsumen.sales,
    //             pbb_pecah_nop,
    //             pbg_no,
    //             sertifikat_split_no_hgb,
    //             (select sum(nominal) from log_pembayaran where log_pembayaran.id_mkdt = mkdt.id_mkdt and payment_type like "%Uang Muka%") as sudah_bayar_um,
    //             (select sum(nominal) from log_pembayaran where log_pembayaran.id_mkdt = mkdt.id_mkdt and (payment_type LIKE "%Biaya Proses%" OR payment_type LIKE "%BPHTB%" OR payment_type LIKE "%PPN%")) sudah_bayar_bb,

    //             a.username as uadd_by,
    //             b.username as uedit_by,
    //         ')
    //         ->join('kavling', "kavling.id_mkdt = mkdt.id_mkdt")
    //         ->join('produksi', "kavling.id_produksi = produksi.id_produksi", 'left')
    //         ->join('tipe', "tipe.id_tipe = kavling.id_tipe")
    //         ->join('konsumen', "konsumen.id_konsumen = mkdt.id_konsumen", 'left')
    //         ->join('legal', "kavling.id_legal = legal.id_legal", 'left')
    //         ->join('jalan', "jalan.id_jalan = kavling.id_jalan")
    //         ->join('cluster', "jalan.id_cluster = cluster.id_cluster")
    //         ->join('proyek', "proyek.id_proyek = cluster.id_proyek")
    //         ->join('users a', "a.id = mkdt.add_by")
    //         ->join('users b', "b.id = mkdt.edit_by");

    //     if ($var['id_jalan'])
    //         $condition = array_merge($condition, ["jalan.id_jalan" => $var['id_jalan']]);
    //     elseif ($var['id_cluster'])
    //         $condition = array_merge($condition, ["cluster.id_cluster" => $var['id_cluster']]);
    //     else
    //         $condition = array_merge($condition, ["proyek.id_proyek" => $var['id_proyek']]);

    //     $result = $this->if_where($var, $colum, $condition, $query);

    //     $result
    //         ->offset($var['start'])
    //         ->limit($var['length']);

    //     $x = $result->get();

    //     $data['draw'] = $var['draw'];

    //     //count filtered
    //     $countfiltered = $this->db->table("mkdt")
    //         ->select('
    //             mkdt.*,

    //             kavling.no_kavling,

    //             jalan.id_jalan,
    //             jalan.nama_jalan,
    //             cluster.id_cluster,
    //             cluster.nama_cluster,
    //             proyek.id_proyek,
    //             proyek.nama_proyek,
    //             tipe.tipe_rumah,
    //             produksi.progres_bangunan,
    //             pbb_pecah_nop,
    //             pbg_no,
    //             sertifikat_split_no_hgb,

    //             konsumen.nama_konsumen,
    //             konsumen.hp_konsumen,
    //             konsumen.sales,
    //         ')
    //         ->join('kavling', "kavling.id_mkdt = mkdt.id_mkdt")
    //         ->join('produksi', "kavling.id_produksi = produksi.id_produksi", 'left')
    //         ->join('tipe', "tipe.id_tipe = kavling.id_tipe")
    //         ->join('konsumen', "konsumen.id_konsumen = mkdt.id_konsumen", 'left')
    //         ->join('legal', "kavling.id_legal = legal.id_legal", 'left')
    //         ->join('jalan', "jalan.id_jalan = kavling.id_jalan")
    //         ->join('cluster', "jalan.id_cluster = cluster.id_cluster")
    //         ->join('proyek', "proyek.id_proyek = cluster.id_proyek");

    //     // $countTotal = $countfiltered;

    //     $countfiltered = $this->if_where($var, $colum, $condition, $countfiltered);
    //     $data['recordsFiltered'] = count($countfiltered->get()->getResult());

    //     //count total
    //     $condition = [
    //         'mkdt.status_mkdt ' => "Booking"
    //     ];
    //     $countTotal = $this->db->table("mkdt")
    //         ->select("count(mkdt.id_mkdt) as count")
    //         ->join('kavling', "kavling.id_mkdt = mkdt.id_mkdt")
    //         ->join('produksi', "kavling.id_produksi = produksi.id_produksi", 'left')
    //         ->join('tipe', "tipe.id_tipe = kavling.id_tipe")
    //         ->join('konsumen', "konsumen.id_konsumen = mkdt.id_konsumen", 'left')
    //         ->join('legal', "kavling.id_legal = legal.id_legal", 'left')
    //         ->join('jalan', "jalan.id_jalan = kavling.id_jalan")
    //         ->join('cluster', "jalan.id_cluster = cluster.id_cluster")
    //         ->join('proyek', "proyek.id_proyek = cluster.id_proyek")
    //         ->where($condition);

    //     $data['recordsTotal'] = $countTotal->get()->getResult()[0]->count;

    //     //looping data untuk datatable
    //     $no = $var['start'];
    //     foreach ($x->getResult() as $key => $v) {
    //         $no++;

    //         $ops = '<div class="btn-group">';
    //         $ops .= '	<button type="button" class="btn btn-sm btn-info" onclick="edit(' . $v->id_mkdt . ')"><i class="fa fa-edit"></i></button>';
    //         $ops .= '	<button type="button" class="btn btn-sm btn-danger" onclick="remove(' . $v->id_mkdt . ')"><i class="fa ' . $no . '"></i></button>';
    //         $ops .= '</div>';

    //         $um = $v->harga_uang_muka ? $v->harga_uang_muka : 0;
    //         $bb = $v->harga_bphtb + $v->harga_biaya_proses + $v->harga_ppn;

    //         $tunai = $v->sudah_bayar_um > 0 ? $v->sudah_bayar_um / $um * 100 : 0;



    //         $sudah_bayar_bb = $v->sudah_bayar_bb > 0 ? $v->sudah_bayar_bb : 0;
    //         $sudah_bayar_um = $v->sudah_bayar_um > $um ? $um : $v->sudah_bayar_um;

    //         $um_pers = "";
    //         $bb_pers = "";

    //         $um_pers = 0;
    //         $bb_pers = 0;

    //         if ($sudah_bayar_bb > 0 && $bb > 0) {
    //             $bb_pers = $sudah_bayar_bb / $bb * 100;
    //         }
    //         // $bb_pers = $v->sudah_bayar_bb > 0 ? $v->sudah_bayar_bb / $bb * 100 : 0;
    //         if ($v->is_kpr == 1) {
    //             $tunai = "";
    //             $um_pers = $sudah_bayar_um > 0 ? $sudah_bayar_um / $um * 100 : 0;
    //         }

    //         // echo $v->is_kpr;    
    //         // echo $v->sudah_bayar_um;
    //         // echo "<br>";
    //         // echo $um;
    //         // die();

    //         $data['data'][$key] = array(

    //             $no,
    //             $v->nama_jalan,
    //             $v->no_kavling,
    //             $v->tipe_rumah . "(" . $v->luas_tanah . ")",

    //             $v->nama_konsumen,
    //             $v->sales,


    //             $this->format_tgl($v->booking_tgl),
    //             $this->format_tgl($v->wawancara_tgl),

    //             $this->is_active($v->is_kpr, "KPR", "TUNAI"),
    //             $v->bank,
    //             $v->keterangan,

    //             $this->format_tgl($v->sp3k_tgl),
    //             $this->format_tgl($v->sp3k_tgl_exp),
    //             "", //sikasep

    //             $tunai ? number_format((float) $tunai, 2, '.', '') . "%" : "-",
    //             $um_pers ? number_format((float) $um_pers, 2, '.', '') . "%" : "-",
    //             $bb_pers ? number_format((float) $bb_pers, 2, '.', '') . "%" : "-",


    //             number_format($v->harga_jual),
    //             number_format($v->harga_kpr),
    //             $this->format_tgl($v->tgl_harga),


    //             $v->progres_bangunan ? $v->progres_bangunan . "%" : '-',

    //             $v->lpa ? '&#10004;' : '-', //lpa
    //             $v->st_jalan ? '&#10004;' : '-', //listrik
    //             '', //jalan

    //             $v->sertifikat_split_no_hgb,
    //             $v->pbg_no,
    //             $v->pbb_pecah_nop,

    //             '', //sikumbang

    //             $v->uadd_by,
    //             date_format(date_create($v->created_at), "d-M-Y H:i"),
    //             $v->uedit_by,
    //             date_format(date_create($v->updated_at), "d-M-Y H:i"),
    //             $ops
    //         );
    //     }
    //     return $this->response->setJSON($data);
    // }
    // function list_kavling()
    // {
    //     $data['content'] = 'kavling/list-kavling';
    //     $data['data']['controller'] = 'Mkdt';
    //     $data['data']['title'] = 'List Kavling';

    //     return view('template', $data);
    // }

    //sudah dipindah ke service
    // function getListKavling()
    // {
    //     $data['token'] = csrf_hash();
    //     $data['data'] = array();

    //     $var = $this->request->getVar();

    //     $colum = ['nama_konsumen', 'nama_jalan', 'no_kavling'];
    //     $condition = [
    //         'mkdt.status_mkdt ' => "Booking"
    //     ];

    //     if ($var['sp3k'] != "")
    //         $condition = array_merge($condition, ["sp3k" => $var['sp3k']]);
    //     if ($var['wawancara'] != "")
    //         $condition = array_merge($condition, ["wawancara" => $var['wawancara']]);
    //     // if ($var['akad'] != "")
    //     //     $condition = array_merge($condition, ["akad" => $var['akad']]);

    //     //get mkdt query 
    //     $query = $this->db->table('mkdt')
    //         ->select('
    //             mkdt.*,
    //             kavling.no_kavling,
    //             kavling.luas_tanah,

    //             jalan.id_jalan,
    //             jalan.nama_jalan,
    //             cluster.id_cluster,
    //             cluster.nama_cluster,
    //             proyek.id_proyek,
    //             proyek.nama_proyek,
    //             tipe.tipe_rumah,
    //             produksi.progres_bangunan,
    //             produksi.st_air,
    //             produksi.st_jalan,
    //             produksi.lpa,

    //             konsumen.nama_konsumen,
    //             konsumen.hp_konsumen,
    //             konsumen.sales,
    //             pbb_pecah_nop,
    //             pbg_no,
    //             sertifikat_split_no_hgb,
    //             (select sum(nominal) from log_pembayaran where log_pembayaran.id_mkdt = mkdt.id_mkdt and payment_type like "%Uang Muka%") as sudah_bayar_um,
    //             (select sum(nominal) from log_pembayaran where log_pembayaran.id_mkdt = mkdt.id_mkdt and payment_type not like "%Booking%") as total_sudah_bayar,
    //             (select sum(nominal) from log_pembayaran where log_pembayaran.id_mkdt = mkdt.id_mkdt and (payment_type LIKE "%Biaya Proses%" OR payment_type LIKE "%BPHTB%" OR payment_type LIKE "%PPN%")) sudah_bayar_bb,

    //             a.username as uadd_by,
    //             b.username as uedit_by,
    //         ')
    //         ->join('kavling', "kavling.id_mkdt = mkdt.id_mkdt")
    //         ->join('produksi', "kavling.id_produksi = produksi.id_produksi", 'left')
    //         ->join('tipe', "tipe.id_tipe = kavling.id_tipe")
    //         ->join('konsumen', "konsumen.id_konsumen = mkdt.id_konsumen", 'left')
    //         ->join('legal', "kavling.id_legal = legal.id_legal", 'left')
    //         ->join('jalan', "jalan.id_jalan = kavling.id_jalan")
    //         ->join('cluster', "jalan.id_cluster = cluster.id_cluster")
    //         ->join('proyek', "proyek.id_proyek = cluster.id_proyek")
    //         ->join('users a', "a.id = mkdt.add_by")
    //         ->join('users b', "b.id = mkdt.edit_by");

    //     if ($var['id_jalan'])
    //         $condition = array_merge($condition, ["jalan.id_jalan" => $var['id_jalan']]);
    //     elseif ($var['id_cluster'])
    //         $condition = array_merge($condition, ["cluster.id_cluster" => $var['id_cluster']]);
    //     else
    //         $condition = array_merge($condition, ["proyek.id_proyek" => $var['id_proyek']]);

    //     $result = $this->if_where($var, $colum, $condition, $query);

    //     $result
    //         ->offset($var['start'])
    //         ->limit($var['length']);

    //     $x = $result->get();

    //     $data['draw'] = $var['draw'];

    //     //count filtered
    //     $countfiltered = $this->db->table("mkdt")
    //         ->select('
    //             mkdt.*,

    //             kavling.no_kavling,

    //             jalan.id_jalan,
    //             jalan.nama_jalan,
    //             cluster.id_cluster,
    //             cluster.nama_cluster,
    //             proyek.id_proyek,
    //             proyek.nama_proyek,
    //             tipe.tipe_rumah,
    //             produksi.progres_bangunan,
    //             pbb_pecah_nop,
    //             pbg_no,
    //             sertifikat_split_no_hgb,

    //             konsumen.nama_konsumen,
    //             konsumen.hp_konsumen,
    //             konsumen.sales,
    //         ')
    //         ->join('kavling', "kavling.id_mkdt = mkdt.id_mkdt")
    //         ->join('produksi', "kavling.id_produksi = produksi.id_produksi", 'left')
    //         ->join('tipe', "tipe.id_tipe = kavling.id_tipe")
    //         ->join('konsumen', "konsumen.id_konsumen = mkdt.id_konsumen", 'left')
    //         ->join('legal', "kavling.id_legal = legal.id_legal", 'left')
    //         ->join('jalan', "jalan.id_jalan = kavling.id_jalan")
    //         ->join('cluster', "jalan.id_cluster = cluster.id_cluster")
    //         ->join('proyek', "proyek.id_proyek = cluster.id_proyek");

    //     // $countTotal = $countfiltered;

    //     $countfiltered = $this->if_where($var, $colum, $condition, $countfiltered);
    //     $data['recordsFiltered'] = count($countfiltered->get()->getResult());

    //     //count total
    //     $condition = [
    //         'mkdt.status_mkdt ' => "Booking"
    //     ];
    //     $countTotal = $this->db->table("mkdt")
    //         ->select("count(mkdt.id_mkdt) as count")
    //         ->join('kavling', "kavling.id_mkdt = mkdt.id_mkdt")
    //         ->join('produksi', "kavling.id_produksi = produksi.id_produksi", 'left')
    //         ->join('tipe', "tipe.id_tipe = kavling.id_tipe")
    //         ->join('konsumen', "konsumen.id_konsumen = mkdt.id_konsumen", 'left')
    //         ->join('legal', "kavling.id_legal = legal.id_legal", 'left')
    //         ->join('jalan', "jalan.id_jalan = kavling.id_jalan")
    //         ->join('cluster', "jalan.id_cluster = cluster.id_cluster")
    //         ->join('proyek', "proyek.id_proyek = cluster.id_proyek")
    //         ->where($condition);

    //     $data['recordsTotal'] = $countTotal->get()->getResult()[0]->count;

    //     //looping data untuk datatable
    //     $no = $var['start'];
    //     foreach ($x->getResult() as $key => $v) {
    //         $no++;

    //         $ops = '<div class="btn-group">';
    //         $ops .= '	<button type="button" class="btn btn-sm btn-info" onclick="edit(' . $v->id_mkdt . ')"><i class="fa fa-edit"></i></button>';
    //         $ops .= '	<button type="button" class="btn btn-sm btn-danger" onclick="remove(' . $v->id_mkdt . ')"><i class="fa ' . $no . '"></i></button>';
    //         $ops .= '</div>';

    //         $um = $v->harga_uang_muka - $v->harga_diskon_uang_muka ?: 0;
    //         $badm = $v->harga_administrasi + $v->harga_penambahan_um + $v->harga_penambahan + $v->harga_penambahan_tanah ?: 0; //biaya adm + hook + kelebihan tanah + turun kpr
    //         $bb = $v->harga_bphtb + $v->harga_biaya_proses + $v->harga_ppn;

    //         $tunai = $v->sudah_bayar_um > 0 ? $v->sudah_bayar_um / $um * 100 : 0;



    //         $sudah_bayar_bb = $v->sudah_bayar_bb > 0 ? $v->sudah_bayar_bb : 0;
    //         $sudah_bayar_um = $v->sudah_bayar_um > $um ? $um : $v->sudah_bayar_um;
    //         $sudah_bayar_adm = $v->total_sudah_bayar - $sudah_bayar_bb - $sudah_bayar_um ?: 0;

    //         $um_pers = 0;
    //         $bb_pers = 0;
    //         $badm_pers = 0;

    //         if ($sudah_bayar_bb > 0 && $bb > 0) {
    //             $bb_pers = $sudah_bayar_bb / $bb * 100;
    //         }
    //         // $bb_pers = $v->sudah_bayar_bb > 0 ? $v->sudah_bayar_bb / $bb * 100 : 0;
    //         if ($v->is_kpr == 1) {
    //             $tunai = "";
    //             $um_pers = $sudah_bayar_um > 0 ? $sudah_bayar_um / $um * 100 : 0;
    //         }

    //         if ($sudah_bayar_um > 0 && $badm > 0) {
    //             $badm_pers  = $sudah_bayar_um / $badm * 100;
    //         }
    //         // echo $v->is_kpr;    
    //         // echo $v->sudah_bayar_um;
    //         // echo "<br>";
    //         // echo $um;
    //         // die();

    //         $is_subsidi = $v->is_subsidi ? '<span >(Subsidi)</span>' : '<span >(Non-Subsidi)</span>';

    //         $data['data'][$key] = array(

    //             $no,
    //             $v->nama_jalan,
    //             $v->no_kavling,
    //             $v->tipe_rumah . "(" . $v->luas_tanah . ")",

    //             $v->nama_konsumen,
    //             $v->sales,


    //             $this->format_tgl($v->booking_tgl),
    //             $this->format_tgl($v->wawancara_tgl),

    //             $this->is_active($v->is_kpr, "KPR " . $is_subsidi, "TUNAI " . $is_subsidi),
    //             $v->bank,
    //             $v->keterangan,

    //             $this->format_tgl($v->sp3k_tgl),
    //             $this->format_tgl($v->sp3k_tgl_exp),
    //             "", //sikasep

    //             $tunai ? number_format((float) $tunai, 2, '.', '') . "%" : "-",
    //             $um_pers ? number_format((float) $um_pers, 2, '.', '') . "%" : "-",
    //             $badm_pers ? number_format((float) $badm_pers, 2, '.', '') . "%" : "-",
    //             $bb_pers ? number_format((float) $bb_pers, 2, '.', '') . "%" : "-",


    //             number_format($v->harga_jual),
    //             number_format($v->harga_kpr),
    //             $this->format_tgl($v->tgl_harga),


    //             $v->progres_bangunan ? $v->progres_bangunan . "%" : '-',

    //             $v->lpa ? '&#10004;' : '-', //lpa
    //             $v->st_jalan ? '&#10004;' : '-', //listrik
    //             '', //jalan

    //             $v->sertifikat_split_no_hgb,
    //             $v->pbg_no,
    //             $v->pbb_pecah_nop,

    //             '', //sikumbang

    //             $v->uadd_by,
    //             date_format(date_create($v->created_at), "d-M-Y H:i"),
    //             $v->uedit_by,
    //             date_format(date_create($v->updated_at), "d-M-Y H:i"),
    //             $ops
    //         );
    //     }
    //     return $this->response->setJSON($data);
    // }

    // sudah dipindah ke service
    // function list_batal()
    // {
    //     $data['content'] = 'kavling/list-batal';
    //     $data['data']['controller'] = 'Mkdt';
    //     $data['data']['title'] = 'List Konsumen Batal';

    //     return view('template', $data);
    // }
    // function getListBatal()
    // {
    //     $data['token'] = csrf_hash();
    //     $data['data'] = array();

    //     $var = $this->request->getVar();

    //     $colum = ['nama_konsumen', 'nama_jalan', 'no_kavling'];
    //     $condition = [
    //         'mkdt.status_mkdt ' => "Batal"
    //     ];

    //     if ($var['sp3k'] != "")
    //         $condition = array_merge($condition, ["sp3k" => $var['sp3k']]);
    //     if ($var['wawancara'] != "")
    //         $condition = array_merge($condition, ["wawancara" => $var['wawancara']]);
    //     if ($var['akad'] != "")
    //         $condition = array_merge($condition, ["akad" => $var['akad']]);

    //     //get mkdt query 
    //     $query = $this->db->table('mkdt')
    //         ->select('
    //             mkdt.*,
    //             kavling.no_kavling,
    //             kavling.luas_tanah,

    //             jalan.id_jalan,
    //             jalan.nama_jalan,
    //             cluster.id_cluster,
    //             cluster.nama_cluster,
    //             proyek.id_proyek,
    //             proyek.nama_proyek,
    //             tipe.tipe_rumah,
    //             produksi.progres_bangunan,

    //             konsumen.nama_konsumen,
    //             konsumen.hp_konsumen,
    //             pbb_pecah_nop,
    //             pbg_no,
    //             sertifikat_split_no_hgb,
    //             (select sum(nominal) from log_pembayaran where log_pembayaran.id_mkdt = mkdt.id_mkdt and payment_type = "UangMuka") as sudah_bayar_um,
    //             (select sum(nominal) from log_pembayaran where log_pembayaran.id_mkdt = mkdt.id_mkdt and payment_type = "BiayaBiaya") sudah_bayar_bb,

    //             a.username as uadd_by,
    //             b.username as uedit_by,
    //         ')
    //         ->join('kavling', "kavling.id_mkdt = mkdt.id_mkdt")
    //         ->join('produksi', "kavling.id_produksi = produksi.id_produksi", 'left')
    //         ->join('tipe', "tipe.id_tipe = kavling.id_tipe")
    //         ->join('konsumen', "konsumen.id_konsumen = mkdt.id_konsumen", 'left')
    //         ->join('legal', "kavling.id_legal = legal.id_legal", 'left')
    //         ->join('jalan', "jalan.id_jalan = kavling.id_jalan")
    //         ->join('cluster', "jalan.id_cluster = cluster.id_cluster")
    //         ->join('proyek', "proyek.id_proyek = cluster.id_proyek")
    //         ->join('users a', "a.id = mkdt.add_by")
    //         ->join('users b', "b.id = mkdt.edit_by");

    //     if ($var['id_jalan'])
    //         $condition = array_merge($condition, ["jalan.id_jalan" => $var['id_jalan']]);
    //     elseif ($var['id_cluster'])
    //         $condition = array_merge($condition, ["cluster.id_cluster" => $var['id_cluster']]);
    //     else
    //         $condition = array_merge($condition, ["proyek.id_proyek" => $var['id_proyek']]);

    //     $result = $this->if_where($var, $colum, $condition, $query);

    //     $result
    //         ->offset($var['start'])
    //         ->limit($var['length']);

    //     $x = $result->get();

    //     $data['draw'] = $var['draw'];


    //     //count filtered
    //     $countfiltered = $this->db->table("mkdt")
    //         ->select('
    //             mkdt.*,

    //             kavling.no_kavling,

    //             jalan.id_jalan,
    //             jalan.nama_jalan,
    //             cluster.id_cluster,
    //             cluster.nama_cluster,
    //             proyek.id_proyek,
    //             proyek.nama_proyek,
    //             tipe.tipe_rumah,
    //             produksi.progres_bangunan,
    //             pbb_pecah_nop,
    //             pbg_no,
    //             sertifikat_split_no_hgb,

    //             konsumen.nama_konsumen,
    //             konsumen.hp_konsumen
    //         ')
    //         ->join('kavling', "kavling.id_mkdt = mkdt.id_mkdt")
    //         ->join('produksi', "kavling.id_produksi = produksi.id_produksi", 'left')
    //         ->join('tipe', "tipe.id_tipe = kavling.id_tipe")
    //         ->join('konsumen', "konsumen.id_konsumen = mkdt.id_konsumen", 'left')
    //         ->join('legal', "kavling.id_legal = legal.id_legal", 'left')
    //         ->join('jalan', "jalan.id_jalan = kavling.id_jalan")
    //         ->join('cluster', "jalan.id_cluster = cluster.id_cluster")
    //         ->join('proyek', "proyek.id_proyek = cluster.id_proyek");

    //     // $countTotal = $countfiltered;



    //     $countfiltered = $this->if_where($var, $colum, $condition, $countfiltered);
    //     $data['recordsFiltered'] = count($countfiltered->get()->getResult());

    //     //count total
    //     $condition = [
    //         'mkdt.status_mkdt ' => "Booking"
    //     ];
    //     $countTotal = $this->db->table("mkdt")
    //         ->select("count(mkdt.id_mkdt) as count")
    //         ->join('kavling', "kavling.id_mkdt = mkdt.id_mkdt")
    //         ->join('produksi', "kavling.id_produksi = produksi.id_produksi", 'left')
    //         ->join('tipe', "tipe.id_tipe = kavling.id_tipe")
    //         ->join('konsumen', "konsumen.id_konsumen = mkdt.id_konsumen", 'left')
    //         ->join('legal', "kavling.id_legal = legal.id_legal", 'left')
    //         ->join('jalan', "jalan.id_jalan = kavling.id_jalan")
    //         ->join('cluster', "jalan.id_cluster = cluster.id_cluster")
    //         ->join('proyek', "proyek.id_proyek = cluster.id_proyek")
    //         ->where($condition);

    //     $data['recordsTotal'] = $countTotal->get()->getResult()[0]->count;


    //     //looping data untuk datatable
    //     $no = $var['start'];
    //     foreach ($x->getResult() as $key => $v) {
    //         $no++;

    //         $ops = '<div class="btn-group">';
    //         $ops .= '	<button type="button" class="btn btn-sm btn-info" onclick="edit(' . $v->id_mkdt . ')"><i class="fa fa-edit"></i></button>';
    //         $ops .= '	<button type="button" class="btn btn-sm btn-danger" onclick="remove(' . $v->id_mkdt . ')"><i class="fa ' . $no . '"></i></button>';
    //         $ops .= '</div>';





    //         if ($v->harga_jual > 0)
    //             $um = $v->harga_jual - $v->harga_kpr + $v->harga_penambahan_um;
    //         else {
    //             $um = 0;
    //         }



    //         $bb = $v->harga_bphtb + $v->harga_administrasi + $v->harga_biaya_proses + $v->harga_ppn;

    //         $turun_kpr = ($v->harga_kpr_acc > 0) ? $v->harga_jual - $v->harga_kpr_acc : 0;

    //         $tot = $um + $bb;
    //         $pers = ($v->sudah_bayar_um + $v->sudah_bayar_bb > 0 && $tot > 0) ? ($v->sudah_bayar_um + $v->sudah_bayar_bb) / $tot * 100 : 0;

    //         if ($tot < 0) {
    //             $tot = 0;
    //             $pers = 0;
    //         }



    //         $surat_batal = $v->surat_batal ? "<br><a target=_blank href='" . base_url($v->surat_batal) . "'>Klik untuk melihat surat batal</a>" : "";

    //         $data['data'][$key] = array(
    //             $no,
    //             $v->keterangan_batal . ' ' . $surat_batal, //keterangan batal
    //             number_format($v->refund), //Nominal Refund
    //             $v->nama_jalan,
    //             $v->no_kavling,
    //             $v->tipe_rumah,
    //             $v->luas_tanah,

    //             $v->nama_konsumen,
    //             $this->format_tgl($v->booking_tgl),

    //             $this->is_active($v->is_kpr, "KPR", "TUNAI"),
    //             $v->bank,
    //             $v->keterangan,

    //             $this->format_tgl($v->wawancara_tgl),
    //             number_format($v->harga_kpr),
    //             number_format($v->harga_kpr_acc),
    //             number_format($v->harga_penambahan_um),
    //             $this->format_tgl($v->sp3k_tgl),
    //             $this->format_tgl($v->sp3k_tgl_exp),

    //             number_format($v->harga_jual),
    //             number_format($v->harga_diskon_hargajual),
    //             number_format($v->harga_jual_net),
    //             number_format($um), //uang muka
    //             number_format($bb), //biaya-biaya
    //             number_format($tot), //total harus bayar

    //             number_format($v->sudah_bayar_um), //um
    //             number_format($v->sudah_bayar_bb), //bb

    //             number_format($um + $bb - $v->sudah_bayar_um - $v->sudah_bayar_bb), //sisa

    //             number_format($pers) . "%",
    //             number_format($turun_kpr),

    //             $this->format_tgl($v->perintah_bangun_tgl),
    //             $v->progres_bangunan . "%",

    //             $v->sertifikat_split_no_hgb,
    //             $v->pbg_no,
    //             $v->pbb_pecah_nop,

    //             $v->uadd_by,
    //             date_format(date_create($v->created_at), "d-M-Y H:i"),
    //             $v->uedit_by,
    //             date_format(date_create($v->updated_at), "d-M-Y H:i"),
    //             $ops,

    //         );
    //         // var_dump($data );die();
    //     }
    //     return $this->response->setJSON($data);
    // }

    function list_stock()
    {
        $data['content'] = 'kavling/list-stock';
        $data['data']['controller'] = 'Mkdt';
        $data['data']['title'] = 'List Kavling Stock';

        return view('template', $data);
    }
    function getListStock()
    {
        $response = array();
        $data['token'] = csrf_hash();
        $data['data'] = array();

        $var = $this->request->getVar();

        // $arr = ['mkdt.status_mkdt' => "Batal", 'mkdt.status_mkdt' => "", 'mkdt.status_mkdt' => null];
        $colum = ['nama_konsumen', 'nama_jalan', 'no_kavling'];
        $condition = [
            "mkdt.status_mkdt" => "Batal",
            "mkdt.status_mkdt" => null,
            // "mkdt.status_mkdt" => null,
            "produksi.progres_bangunan" => 100,
            // "produksi.progres_bangunan" => 100,
        ];

        $query = $this->db->table('kavling')
            ->select('
                kavling.*,

                mkdt.status_mkdt,

                produksi.progres_bangunan,
                
                jalan.id_jalan,
                jalan.nama_jalan,
                cluster.id_cluster,
                cluster.nama_cluster,
                proyek.id_proyek,
                proyek.nama_proyek,
                tipe.tipe_rumah,                

                konsumen.nama_konsumen,
                konsumen.hp_konsumen,
                konsumen.keterangan as keterangan_batal,

                a.username as uadd_by,
                b.username as uedit_by,
            ')
            ->join('tipe', "tipe.id_tipe = kavling.id_tipe")
            ->join('jalan', "jalan.id_jalan = kavling.id_jalan")
            ->join('cluster', "jalan.id_cluster = cluster.id_cluster")
            ->join('proyek', "proyek.id_proyek = cluster.id_proyek")

            ->join('produksi', "kavling.id_produksi = produksi.id_produksi")
            ->join('mkdt', "kavling.id_mkdt = mkdt.id_mkdt", "left")

            ->join('users a', "produksi.add_by = a.id")
            ->join('users b', "produksi.edit_by = b.id")

            ->join('konsumen', "konsumen.id_konsumen = mkdt.id_konsumen", 'left');

        if ($var['id_jalan'])
            $query->where(["jalan.id_jalan" => $var['id_jalan']]);
        elseif ($var['id_cluster'])
            $query->where(["cluster.id_cluster" => $var['id_cluster']]);
        else
            $query->where(["proyek.id_proyek" => $var['id_proyek']]);

        $query->where('produksi.progres_bangunan', 100);
        $query->groupStart()
            ->where('mkdt.status_mkdt', 'Batal')
            ->orWhere('mkdt.status_mkdt IS NULL', null, false)
            ->groupEnd();

        // Jika ada parameter pencarian
        $search = ''; // Ganti dengan input pencarian jika ada
        if (!empty($search)) {
            $query->groupStart()
                ->like('nama_konsumen', $search)
                ->orLike('nama_jalan', $search)
                ->orLike('no_kavling', $search)
                ->groupEnd();
        }

        // $result = $this->if_where($var, $colum, $condition, $query);
        $result = $query;

        $result
            ->offset($var['start'])
            ->limit($var['length']);

        // echo $result->getCompiledSelect();
        // die();

        $x = $result->get();



        //count filtered
        $countfiltered = $this->db->table("kavling")
            ->select("kavling.*")
            ->join('tipe', "tipe.id_tipe = kavling.id_tipe")
            ->join('jalan', "jalan.id_jalan = kavling.id_jalan")
            ->join('cluster', "jalan.id_cluster = cluster.id_cluster")
            ->join('proyek', "proyek.id_proyek = cluster.id_proyek")

            ->join('produksi', "kavling.id_produksi = produksi.id_produksi")
            ->join('mkdt', "kavling.id_mkdt = mkdt.id_mkdt", "left")

            ->join('users a', "produksi.add_by = a.id")
            ->join('users b', "produksi.edit_by = b.id")

            ->join('konsumen', "konsumen.id_konsumen = mkdt.id_konsumen", 'left');

        if ($var['id_jalan'])
            $countfiltered->where(["jalan.id_jalan" => $var['id_jalan']]);
        elseif ($var['id_cluster'])
            $countfiltered->where(["cluster.id_cluster" => $var['id_cluster']]);
        else
            $countfiltered->where(["proyek.id_proyek" => $var['id_proyek']]);

        $countfiltered->where('produksi.progres_bangunan', 100);
        $countfiltered->groupStart()
            ->where('mkdt.status_mkdt', 'Batal')
            ->orWhere('mkdt.status_mkdt IS NULL', null, false)
            ->groupEnd();

        // Jika ada parameter pencarian
        // $search = ''; // Ganti dengan input pencarian jika ada
        if (!empty($search)) {
            $countfiltered->groupStart()
                ->like('nama_konsumen', $search)
                ->orLike('nama_jalan', $search)
                ->orLike('no_kavling', $search)
                ->groupEnd();
        }

        // $countTotal = $countfiltered;

        // $countfiltered = $this->if_where($var, $colum, $condition, $countfiltered);
        $data['recordsFiltered'] = count($countfiltered->get()->getResult());

        //count total
        $condition = [
            "mkdt.status_mkdt " => "Batal",
            "mkdt.status_mkdt " => null,
            "produksi.progres_bangunan" => 100,
            // "produksi.progres_bangunan" => 100,
        ];
        $countTotal = $this->db->table("kavling")
            ->select("count(kavling.id_kavling) as count")
            ->join('tipe', "tipe.id_tipe = kavling.id_tipe")
            ->join('jalan', "jalan.id_jalan = kavling.id_jalan")
            ->join('cluster', "jalan.id_cluster = cluster.id_cluster")
            ->join('proyek', "proyek.id_proyek = cluster.id_proyek")

            ->join('produksi', "kavling.id_produksi = produksi.id_produksi")
            ->join('mkdt', "kavling.id_mkdt = mkdt.id_mkdt", "left")

            ->join('users a', "produksi.add_by = a.id")
            ->join('users b', "produksi.edit_by = b.id")

            ->join('konsumen', "konsumen.id_konsumen = mkdt.id_konsumen", 'left');
        // ->where($condition);
        if ($var['id_jalan'])
            $countTotal->where(["jalan.id_jalan" => $var['id_jalan']]);
        elseif ($var['id_cluster'])
            $countTotal->where(["cluster.id_cluster" => $var['id_cluster']]);
        else
            $countTotal->where(["proyek.id_proyek" => $var['id_proyek']]);

        $countTotal->where('produksi.progres_bangunan', 100);
        $countTotal->groupStart()
            ->where('mkdt.status_mkdt', 'Batal')
            ->orWhere('mkdt.status_mkdt IS NULL', null, false)
            ->groupEnd();

        // Jika ada parameter pencarian
        // $search = ''; // Ganti dengan input pencarian jika ada
        if (!empty($search)) {
            $countTotal->groupStart()
                ->like('nama_konsumen', $search)
                ->orLike('nama_jalan', $search)
                ->orLike('no_kavling', $search)
                ->groupEnd();
        }

        // echo $countTotal->getCompiledSelect();die();

        $data['recordsTotal'] = $countTotal->get()->getResult()[0]->count;



        $data['draw'] = $var['draw'];

        //looping data untuk datatable
        $no = $var['start'];
        foreach ($x->getResult() as $key => $v) {
            $ops = '<div class="btn-group">';
            $ops .= '	<button type="button" class="btn btn-sm btn-info" onclick="edit(' . $v->id_mkdt . ')"><i class="fa fa-edit"></i></button>';
            $ops .= '	<button type="button" class="btn btn-sm btn-danger" onclick="remove(' . $v->id_mkdt . ')"><i class="fa ' . $no . '"></i></button>';
            $ops .= '</div>';
            $no++;
            $nama_konsumen = ($v->nama_konsumen != null) ? $v->nama_konsumen . "(" . $v->status_mkdt . ")" : "";
            $data['data'][] = array(

                $no,

                $v->nama_jalan,
                $v->no_kavling,
                $v->tipe_rumah,
                $v->progres_bangunan . "%",
                $nama_konsumen,
                $v->hp_konsumen,
                $v->keterangan_batal,

                $v->uadd_by,
                date_format(date_create($v->created_at), "d-M-Y H:i"),
                $v->uedit_by,
                date_format(date_create($v->updated_at), "d-M-Y H:i"),
                $ops
            );
        }

        return $this->response->setJSON($data);
    }

    function if_where($var, $column, $condition, $query)
    {
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
    protected function num($d)
    {
        $d = str_replace('.', "", $d);
        $d = str_replace(',', "", $d);

        return $d;
    }
    function format_tgl($tgl)
    {
        if ($tgl == "" || $tgl == "0000-00-00" || $tgl == null)
            return "-";
        return date_format(date_create($tgl), "d-M-Y");
    }

    function is_active($id, $texts, $textf)
    {
        $r = '<span class="btn btn-primary btn-sm" text-capitalized="">' . $textf . '</span>';
        if ($id == "1")
            $r = '<span class="btn btn-success btn-sm" text-capitalized="">' . $texts . '</span>';
        return $r;
    }

    function getsi()
    {
        $id_kavling = $this->request->getVar('id_kavling');


        $r['token'] = csrf_hash();
        $r['data'] = $this->db->table('list_si')
            ->select('list_si.nama, list_si.id as id_list_si_ori, si.*, a.username as uadd_by, b.username as uedit_by')
            ->join('si', 'si.id_list_si = list_si.id and id_kavling = ' . $this->db->escape($id_kavling), 'left')
            ->join('users a', 'a.id = si.add_by', 'left')
            ->join('users b', 'b.id = si.edit_by', 'left')
            ->get()->getResult();
        $r['data'] = $this->fileAccessService->addAccessUrlsToRows($r['data'], 'si');

        return $this->response->setJSON($r);
    }

    function saveSI()
    {
        $response['token'] = csrf_hash();
        $id_si = $this->request->getVar('id-si');
        $id_kavling = $this->request->getVar('id_kavling');
        $savedItems = [];

        foreach ($id_si as $i => $v) {
            $data = [
                'id_kavling' => $id_kavling,
                'tanggal_si' => $v['tanggal_si'],
                'keterangan' => $v['keterangan'],
            ];

            if ($v['tanggal_si'] != "") {
                $file = $this->request->getFile('id-si-file-' . $i);
                if ($file && $file->isValid() && !$file->hasMoved() && $file->getSize() > 0) {
                    $name = $file->getRandomName();
                    $lok = 'uploads/si/' . date('Ymd') . '/';

                    $this->fileAccessService->storeAs($file, $lok, $name);

                    $data['file'] = $lok . $name;
                }

                if (strpos($i, 'n') === false) {
                    $data['edit_by'] = user_id();
                    $data['updated_at'] = date("Y-m-d H:i:s");

                    $q = $this->db->table('si')
                        ->where(['id' => $i])
                        ->update($data);
                } else {
                    $data['id_list_si'] = substr($i, 1);

                    $data['add_by'] = user_id();
                    $data['created_at'] = date("Y-m-d H:i:s");

                    $q = $this->db->table('si')
                        ->insert($data);
                }

                $listSiId = strpos($i, 'n') === false
                    ? ($this->db->table('si')->select('id_list_si')->where('id', $i)->get()->getRow('id_list_si') ?? null)
                    : substr($i, 1);
                $listSi = $listSiId
                    ? $this->db->table('list_si')->select('nama')->where('id', $listSiId)->get()->getRow()
                    : null;

                $savedItems[] = [
                    'nama'       => $listSi->nama ?? 'SI',
                    'tanggal_si' => $v['tanggal_si'],
                    'keterangan' => $v['keterangan'] ?? '',
                ];
            }
        }

        if (!empty($savedItems)) {
            $kavling = $this->kavlingModel->select('id_mkdt')->find($id_kavling);
            $this->mkdtHistoryService->log(
                (int) $id_kavling,
                $kavling ? (int) ($kavling->id_mkdt ?? 0) ?: null : null,
                MkdtHistoryService::ACTION_STANDING_INSTRUCTION,
                $this->mkdtHistoryService->buildStandingInstructionSummary($savedItems),
                null,
                $savedItems,
                user_id()
            );
        }

        $response['success'] = true;
        $response['messages'] = 'Data berhasil diperbaharui';

        return $this->response->setJSON($response);
    }

    /******************************** export *******************************/
    public function export_xlsx()
    {
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

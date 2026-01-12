<?php

namespace App\Controllers;

use App\Models\KeuanganModel;
use App\Models\KavlingModel;
use App\Models\KonsumenModel;
use App\Models\MkdtModel;
use App\Models\LogPembayaranModel;
use CodeIgniter\HTTP\Response;
use App\Models\ProfilePerusahaanModel;
use Exception;

// use App\Libraries\Pdf;
use App\Libraries\Mpdf_lib;

class Keuangan extends BaseController
{
    protected $db;
    protected $keuanganModel;
    protected $mkdtModel;
    protected $kavlingModel;
    protected $konsumenModel;
    protected $comproModel;
    protected $lpModel;
    protected $notif;
    protected $mpdf;

    public function __construct()
    {
        $this->notif = new Notif();
        $this->keuanganModel = new KeuanganModel();
        $this->kavlingModel = new KavlingModel();
        $this->mkdtModel = new MkdtModel();
        $this->konsumenModel = new KonsumenModel();
        $this->lpModel = new LogPembayaranModel();
        $this->comproModel = new ProfilePerusahaanModel();
        $this->db = db_connect();
        // $this->pdf = new Pdf();
        $this->mpdf = new Mpdf_lib();
    }
    function getDanaAkad()
    {
        $r['token'] = csrf_hash();
        $id_mkdt = $this->request->getVar('id_mkdt');
        $id_kavling = $this->request->getVar('id_kavling');

        $r['id_mkdt'] = $id_mkdt;
        $r['id_kavling'] = $id_kavling;

        $r['mkdt'] = $this->db->table('mkdt')
            ->select('harga_kpr_acc, dajam_selesai')
            ->where(['id_mkdt' => $id_mkdt])
            ->get()->getResult()[0];


        $r['list_dajam'] = $this->db->table('list_dajam')
            ->select('list_dajam.nama_jaminan, list_dajam.id as id_list_dajam_ori, dana_akad.*')
            ->join('dana_akad', 'dana_akad.id_list_dajam = list_dajam.id and id_kavling = ' . $this->db->escape($id_kavling), 'left')
            // ->where('id_kavling', $id_kavling)
            ->get()->getResult();

        return $this->response->setJSON($r);
    }
    function getCashOut()
    {
        $r['token'] = csrf_hash();

        $id_kavling = $this->request->getVar('id_kavling');

        $r['id_kavling'] = $id_kavling;


        $r['list_cashout'] = $this->db->table('list_cashout lc')
            ->select('lc.id as id_cashout, lc.item, lc.sort, c.*, u.username as add_by_u, e.username as edit_by_u')
            ->join('cashout c', 'c.id_item_cashout = lc.id and id_kavling = ' . $this->db->escape($id_kavling), 'left')
            ->join('users u', 'u.id = c.add_by', 'left')
            ->join('users e', 'e.id = c.edit_by', 'left')
            ->get()->getResult();

        return $this->response->setJSON($r);
    }
    function saveCashOut()
    {
        $response['token'] = csrf_hash();
        $id_cashout = $this->request->getVar('id-cashout');
        $id_kavling = $this->request->getVar('id_kavling');

        foreach ($id_cashout as $i => $v) {
            $data = [];
            
            $data['id_kavling'] = $id_kavling;


            $s = false;

          
              
            if ($v['nominal'] != "" && $v['tanggal_bayar'] != "") {
                if (strpos($i, 'n') === false) {
                    // $data['id_item_cashout'] = $v['id_item_cashout'];
                    $data['nominal'] = $this->num($v['nominal']);
                    $data['keterangan'] = $v['keterangan'];
                    $data['tanggal_bayar'] = $v['tanggal_bayar'];

                    $data['edit_by'] = user_id();
                    $data['updated_at'] = date("Y-m-d H:i:s");

                    // $data['id'] = $i;
                    $q = $this->db->table('cashout')
                        ->where(['id' => $i])
                        ->update($data);
                    
                        $s = $q ;
                } else {
                    $data['id_item_cashout'] = substr($v['id_item_cashout'], 1);
                    $data['nominal'] = $this->num($v['nominal']);
                    $data['keterangan'] = $v['keterangan'];
                    $data['tanggal_bayar'] = $v['tanggal_bayar'];

                    $data['id'] = null;
                    $data['add_by'] = user_id();
                    $data['created_at'] = date("Y-m-d H:i:s");
                    
                    $q = $this->db->table('cashout')
                        ->insert($data);
                    
                    $s = $q;
                }
            }
            
        }
     
        // if ($s) {
            $response['success'] = true;
            $response['messages'] = 'Data berhasil diperbaharui';
        // } else {
        //     $response['success'] = false;
        //     $response['messages'] = 'Terjadi kesalahan saat melakukan perubahan data';
        // }
        return $this->response->setJSON($response);
    }
    function saveDanaAkad()
    {
        $response['token'] = csrf_hash();

        // $id = $this->request->getVar('id_dana_cair');
        $id_mkdt = $this->request->getVar('id_mkdt');

        // $sudah_cair = ($this->request->getVar('dana_akad_cair') == 1) ? 1 : 0;

        // $id_list_dajam = $this->request->getVar('id_list_dajam');
        // $nominal = $this->request->getVar('nominal');
        $id_dajam = $this->request->getVar('id_dajam');


        $data['id_kavling'] = $this->request->getVar('id_kavling');
        $dajam_selesai = $this->request->getVar('dajam_selesai') ?? 0;

        $this->mkdtModel->update( $id_mkdt, ['dajam_selesai' => $dajam_selesai]);
      
        // $s = false;
        // echo "<pre>";
        // echo $dajam_selesai;
        // echo "</pre>";

        // die();

        // var_dump($this->request->getVar());die();
        foreach ($id_dajam as $i => $v) {
            $data['id_list_dajam'] = $v['id_list_dajam'];
            $data['nominal'] = $this->num($v['nominal']);
            

            $s = false;

            // jika sudah cair
            if (isset($v['sudah_cair']) && $v['sudah_cair']) {
                $data['sudah_cair'] = $v['sudah_cair'];
                $data['tgl_cair'] = $v['tgl_cair'];
                $data['keterangan'] = $v['keterangan'];
                $data['cair_oleh'] = user_id();
                $data['cair_created_at'] = date("Y-m-d H:i:s");
                $data['nominal_cair'] = $this->num($v['nominal_cair']);
            } else {
                $data['sudah_cair'] = 0;
                $data['tgl_cair'] = null;
                $data['keterangan'] = null;
                $data['cair_oleh'] = null;
                $data['cair_created_at'] = null;
                $data['nominal_cair'] = null;
            }

            if (strpos($i, 'n') === false) {


                $data['edit_by'] = user_id();
                $data['updated_at'] = date("Y-m-d H:i:s");

                $data['id'] = $i;
                $q = $this->db->table('dana_akad')
                    ->where(['id' => $i])
                    ->update($data);
                $s = $q;
            } else {
                $data['id'] = null;
                $data['add_by'] = user_id();
                $data['created_at'] = date("Y-m-d H:i:s");

                $q = $this->db->table('dana_akad')
                    ->insert($data);
                $s = $q;
            }


            // echo "<pre>";
            // print_r($data);
            // echo "</pre>";
            if (!$s) {
                break;
            }
        }

        if ($s) {
            $response['success'] = true;
            $response['messages'] = 'Data berhasil diperbaharui';
        } else {
            $response['success'] = false;
            $response['messages'] = 'Terjadi kesalahan saat melakukan perubahan data';
        }
        return $this->response->setJSON($response);
    }
    function get_riwayat_gantinama()
    {
        $id_mkdt = $this->request->getVar('id_mkdt');
        $r['token'] = csrf_hash();
        if ($id_mkdt) {
            $x = $this->db->table('mkdt')
                ->select('file_spptb')
                ->where("is_ganti_nama = 'Ganti Nama'")
                ->where("uniq_id = (select uniq_id from mkdt where id_mkdt = $id_mkdt and is_ganti_nama = 'Normal')")
                ->get()->getResult();
            $r['riwayat'] = $x;
        }
        $response['success'] = true;
        return $this->response->setJSON($r);
    }
    function get_data_by_id()
    {
        $hj = [];
        $id_hargajual = $this->request->getVar('id_hargajual');
        $id_kavling = $this->request->getVar('id_kavling');
        $diskresi = [];

        if ($id_kavling) {
            $q = $this->db->table('kavling')
                ->select('
                harga_akhir_tgl,
                a.username as username_harga_akhir,
                diskresi_harga,
                diskresi_memo,
                diskresi_at,
                b.username as username_diskresi
                ')
                ->join('users as a', 'a.id = harga_akhir_oleh', 'left')
                ->join('users as b', 'b.id = diskresi_oleh', 'left')
                ->where('id_kavling', $id_kavling)
                ->get()->getResult()[0];
            $diskresi = $q;
        }

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
        if ($this->request->getVar('id_mkdt')) {
            $r = (object) [];
            $x = $this->mkdtModel
                ->select('
                    mkdt.*,
                    konsumen.nama_konsumen,
                    konsumen.no_spptb,
                    konsumen.nik as nik_konsumen,
                    konsumen.npwp as npwp_konsumen,
                    konsumen.file_ktp as ktp_lok,
                    konsumen.file_npwp as npwp_lok,
                    konsumen.file_data_diri as data_diri_lok,
                    konsumen.email_konsumen,
                    konsumen.hp_konsumen,
                    konsumen.alamat_konsumen,
                    konsumen.status_konsumen,
                    konsumen.status_pernikahan,
                    konsumen.nama_pasangan,
                    konsumen.nik_pasangan,
                    konsumen.nama_instansi,
                    konsumen.alamat_instansi,
                    konsumen.tel_instansi,
                    konsumen.sales,
                    username as perintah_bangun_user,
                    
                ')
                ->join('konsumen', 'konsumen.id_konsumen = mkdt.id_konsumen')
                ->join('users', 'users.id = mkdt.edit_by', 'left')
                ->where('id_mkdt', $this->request->getVar('id_mkdt'))
                ->first();
            if ($x) {
                $r->data = $x;
                $r->hj = $hj;
                $r->diskresi = $diskresi;
                $r->token = csrf_hash();
                $r->tagihan = $this->keuanganModel
                    ->select('keuangan.*, users.username')
                    ->join('users', 'users.id = keuangan.add_by')
                    ->where("id_mkdt", $x->id_mkdt)
                    ->find();
                $r->log_pembayaran = $this->lpModel
                    ->select('
                        log_pembayaran.*,
                        users.username,
                        keuangan.status
                    ')
                    ->join('users', 'users.id = log_pembayaran.add_by')
                    ->join('keuangan', 'keuangan.id_keuangan = log_pembayaran.id_keuangan', 'left')
                    ->where('log_pembayaran.id_mkdt', $this->request->getVar('id_mkdt'))
                    ->orderBy('tanggal_bayar', 'asc')
                    // ->notLike('log_pembayaran.keterangan', 'Booking')
                    ->find();
                $r->list_spptb = $this->db->table('file_spptb')
                    ->select('lokasi, file_spptb.created_at, username')
                    ->join('users', 'users.id = file_spptb.add_by')
                    ->where('id_mkdt', $x->id_mkdt)
                    ->limit(3)
                    ->get()->getResult();
            } else {
                $r['data'] = null;
                $r['token'] = csrf_hash();
                $r['hj'] = $hj;
                $r['diskresi'] = $diskresi;
            }
        } else {
            $r['data'] = null;
            $r['hj'] = $hj;
            $r['diskresi'] = $diskresi;
            $r['token'] = csrf_hash();
        }
        return $this->response->setJSON($r);
    }

    function getTagihan()
    {
        // $r = $this->keuanganModel
        //     ->where('id_mkdt', $this->request->getVar('id_mkdt'))
        //     ->first();

        // if ($r == null)
        $r = (object) array();

        $r->token = csrf_hash();

        $hj = [];


        //get mkdt detail data
        if ($this->request->getVar('id_mkdt')) {
            $r->mkdt = $this->mkdtModel
                ->select('
                    mkdt.*,
                    konsumen.nama_konsumen,
                    konsumen.no_spptb,
                    konsumen.nik as nik_konsumen,
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
                    b.row,
                    b.id_tipe,
                    b.lb,
                    b.lt,
                    c.tipe_rumah

            ')
                ->join('konsumen', 'konsumen.id_konsumen = mkdt.id_konsumen')
                ->join('hargajual as b', 'mkdt.id_hargajual = b.id', 'left')
                ->join('tipe as c', 'c.id_tipe = b.id_tipe', 'left')
                ->where('id_mkdt', $this->request->getVar('id_mkdt'))
                ->first();

            //get rincian tagihan
            $r->tagihan = $this->keuanganModel
                ->select('keuangan.*, users.username')
                ->where('id_mkdt', $this->request->getVar('id_mkdt'))
                ->join('users', 'users.id = keuangan.add_by')
                ->orderBy('jatuh_tempo_tgl', 'asc')
                ->find();

            // get sudah bayar
            $r->log_pembayaran = $this->lpModel
                ->select('
                    log_pembayaran.*,
                    users.username,
                    keuangan.status
                ')
                ->join('users', 'users.id = log_pembayaran.add_by')
                ->join('keuangan', 'keuangan.id_keuangan = log_pembayaran.id_keuangan', 'left')
                ->where('log_pembayaran.id_mkdt', $this->request->getVar('id_mkdt'))
                ->orderBy('tanggal_bayar', 'asc')
                // ->notLike('log_pembayaran.keterangan', 'Booking')
                ->find();
        }
        return $this->response->setJSON($r);
    }
    function isSudahBayar($id)
    {
        $response['token'] = csrf_hash();

        $r = $this->lpModel
            ->select('count(id_keuangan) as c')
            ->where('id_mkdt', $id)
            ->where('payment_type !=', 'Booking')
            ->first();

        if ($r->c > 0) {
            $response['success'] = false;
            $response['messages'] = 'Tidak dapat menghapus tagihan karena sudah melakukan pembayaran';
        }
        return $this->response->setJSON($response);
    }
    function save_kons()
    {
        $response['token'] = csrf_hash();
        $id_kavling = $this->request->getVar('id_kavling');

        $is_ganti_nama = $this->request->getVar('is_ganti_nama');
        $id_mkdt_old = $this->request->getVar('id_mkdt_old');
        $id_konsumen_old = $this->request->getVar('id_konsumen_old');

        $uniqid = uniqid('', true);

        $databaru = $this->request->getVar('mkdt_data_baru');
        // var_dump($this->request->getVar());die();

        $f['id_kavling'] = $id_kavling; //id_kavling untuk table konsumen
        $id_konsumen = $this->request->getPost('id_konsumen');

        $f['id_mkdt'] = $this->request->getPost('id_mkdt');


        // var_dump($this->request->getPost());
        // die();

        //jika data konsumen baru
        if ($databaru == 1)
            $f['id_mkdt'] = null;

        // form data konsumen
        $f['nama_konsumen'] = $this->request->getPost('nama_konsumen');
        $f['no_spptb'] = $this->request->getPost('no_spptb');
        $f['alamat_konsumen'] = $this->request->getPost('alamat_konsumen');
        $f['nik'] = $this->request->getPost('nik_konsumen');
        $f['npwp'] = $this->request->getPost('npwp_konsumen');
        $f['hp_konsumen'] = $this->request->getPost('hp_konsumen');
        $f['status_konsumen'] = $this->request->getPost('status_konsumen');
        $f['email_konsumen'] = $this->request->getPost('email_konsumen');

        $f['status_pernikahan'] = $this->request->getPost('status_pernikahan');
        $f['nama_pasangan'] = $this->request->getPost('nama_pasangan');
        $f['nik_pasangan'] = $this->request->getPost('nik_pasangan');

        $f['nama_instansi'] = $this->request->getPost('nama_instansi');
        $f['alamat_instansi'] = $this->request->getPost('alamat_instansi');
        $f['tel_instansi'] = $this->request->getPost('tel_instansi');

        $f['sales'] = $this->request->getPost('sales');
        $f['add_by'] = user_id();
        $f['edit_by'] = user_id();

        //jika status batal
        $st = $this->request->getPost('dt-status_mkdt');
        if ($st == "Batal") {
            $f['keterangan'] = $this->request->getVar('dt-keterangan_batal');
        }

        //cek jika sudah ada konsumen atau tidak pada kavling
        if ($id_konsumen == null || $id_konsumen == '') {
            if ($this->konsumenModel->insert($f))
                $id_konsumen = $this->konsumenModel->getInsertID();
            else {
                $response['success'] = false;
                $response['messages'] = 'Terjadi kesaahan saat melakukan penambahan konsumen';
                return $this->response->setJSON($response);
            }
        } else {
            if (!$this->konsumenModel->update($id_konsumen, $f)) {
                $response['success'] = false;
                $response['messages'] = 'Terjadi kesaahan saat melakukan perubahan data konsumen';
                return $this->response->setJSON($response);
            }
        }

        /************************ upload file SPPTB *****************************/
        if ($this->request->getFile('file_spptb')->getSize() > 0) {
            $img = $this->request->getFile('file_spptb');

            $name = $img->getRandomName();

            $lok = 'uploads/spptb/' . date('Ymd') . '/';

            $img->move($lok, $name);

            $f2['file_spptb'] = $lok . $name;


            $this->db->table('file_spptb')
                ->insert([
                    'id_mkdt' => $f['id_mkdt'],
                    'lokasi' => $lok . $name,
                    'created_at' => date('Y-m-d H:i:s'),
                    'add_by' => user_id()
                ]);
        }
        /************************ upload file surat kuasa *****************************/
        if ($this->request->getFile('file_surat_kuasa')->getSize() > 0) {
            $img = $this->request->getFile('file_surat_kuasa');

            $name = $img->getRandomName();

            $lok = 'uploads/spptb/lampiran/' . date('Ymd') . '/';

            $img->move($lok, $name);

            $f2['file_surat_kuasa'] = $lok . $name;
        }

        //detail data mkdt
        $f2['id_mkdt'] = $f['id_mkdt'];
        $f2['id_konsumen'] = $id_konsumen;
        $f2['status_mkdt'] = $st;
        // $f2['booking_paid'] = $this->num($this->request->getPost('booking_paid'));

        $f2['id_hargajual'] = $this->request->getPost('idk-harga_akhir');
        $f2['tgl_harga'] = $this->num($this->request->getPost('mk-tgl_harga'));
        $f2['harga_uang_muka'] = $this->num($this->request->getPost('mk-uang_muka'));
        $f2['harga_jual'] = $this->num($this->request->getPost('mk-hargajual'));
        $f2['harga_jual_net'] = $this->num($this->request->getPost('mk-hargajual_net'));
        $f2['harga_administrasi'] = $this->num($this->request->getPost('mk-biaya_adm'));
        $f2['harga_bphtb'] = $this->num($this->request->getPost('mk-bphtb'));
        $f2['harga_biaya_proses'] = $this->num($this->request->getPost('mk-biaya_proses'));
        $f2['harga_kpr'] = $this->num($this->request->getPost('mk-kpr'));
        $f2['harga_ppn'] = $this->num($this->request->getPost('mk-ppn'));
        $f2['harga_penambahan'] = $this->num($this->request->getPost('mk-harga_penambahan'));
        $f2['harga_penambahan_tanah'] = $this->num($this->request->getPost('mk-harga_penambahan_tanah'));
        // $f2['keterangan_penambahan_biaya'] = $this->num($this->request->getPost('mk-keterangan_harga_penambahan'));

        $f2['promo'] = $this->request->getPost('promo');

        $f2['rincian'] = $this->request->getPost('rincian');
        $f2['jenis_subsidi'] = $this->request->getPost('jenis_subsidi');

        $f2['is_kpr'] = $this->request->getPost('is_kpr');
        $f2['is_subsidi'] = $this->request->getPost('is_subsidi');

        $f2['booking_fee'] = $this->num($this->request->getPost('dt-booking_fee'));
        $f2['booking_tgl'] = $this->request->getPost('dt-booking_tgl');

        $f2['keuangan_saved_by'] = user_id();

        $f2['id_kavling'] = $id_kavling;
        $id_mkdt = $f2['id_mkdt'];

        if ($f2['id_mkdt'] == null) {
            $f2['add_by'] = user_id();
            $f2['edit_by'] = user_id();


            if ($is_ganti_nama == "Ganti Nama") {
                $uniqid = $this->db->table('mkdt')->select('uniq_id')->where('id_mkdt', $id_mkdt_old)->get()->getRow()->uniq_id;
                $this->mkdtModel->update($id_mkdt_old, ['is_ganti_nama' => $is_ganti_nama]);

                $this->konsumenModel->update($id_konsumen_old, ['status' => $is_ganti_nama, 'uniq_id' => $uniqid]);
            } else if ($is_ganti_nama == "Ganti Kavling") {
                $uniqid = $this->db->table('mkdt')->select('uniq_id')->where('id_mkdt', $id_mkdt_old)->get()->getRow()->uniq_id;
                $this->mkdtModel->update($id_mkdt_old, ['is_ganti_kavling' => $is_ganti_nama]);

                $this->konsumenModel->update($id_konsumen_old, ['status' => $is_ganti_nama, 'uniq_id' => $uniqid]);
            }

            $f2['uniq_id'] = $uniqid;

            if ($this->mkdtModel->insert((object) $f2)) {
                $id_mkdt = $this->mkdtModel->getInsertID();

                //update id_mkdt di tbl kav
                $this->kavlingModel->update($this->request->getVar('id_kavling'), array('id_mkdt' => $id_mkdt));

                $notif = 'Booking kavling atas nama : ' . $f['nama_konsumen'];
                $this->notif->tambah_notif("3;4;9", $notif, user_id(), $id_kavling, $id_konsumen); //4 mkdt 9 direksi 3 keuangan

                $response['success'] = true;
                $response['messages'] = 'Data berhasil ditambah';
            } else {
                $response['success'] = false;
                $response['messages'] = 'Gagal menginput data booking';
            }
        } else {
            $f2['edit_by'] = user_id();
            if ($this->mkdtModel->update($f['id_mkdt'], $f2)) {

                $notif = 'Melakukan perubahan data konsumen : ' . $f['nama_konsumen'];
                $this->notif->tambah_notif("3;4;9", $notif, user_id(), $id_kavling, $id_konsumen); //4 mkdt 9 direksi 3 keuangan

                $response['success'] = true;
                $response['messages'] = 'Data berhasil diperbaharui';
            } else {
                $response['success'] = false;
                $response['messages'] = 'Gagal memperbaharui data booking';
            }
        }

        // $id_mkdt_new = $this->db->table('mkdt')->select('id_mkdt_new')->where('id_mkdt', $id_mkdt_old)->get()->getRow();


        //ubah status mkdt jadi ganti nama



        ################################## insert ke tagihan ##########################
        // cek jika sudah ada pembayaran
        $cek = $this->db->table("keuangan")->select('id_keuangan')->where("id_mkdt", $id_mkdt)->get()->getResultArray();
        $list_tg = array_map(function ($item) {
            return $item['id_keuangan'];
        }, $cek);

        $id_keu_merge = [];
        $len = 0;
        if (is_array($this->request->getVar('berita_acara[]'))) {
            //input detail tagihan ke table keuangan
            //um
            $len = count($this->request->getVar('berita_acara[]'));
            $idkeu = $this->request->getVar('id_keuangan[]');
            $ba = $this->request->getVar('berita_acara[]');
            $jt = $this->request->getVar('jatuh_tempo_tgl[]');
            $nm = $this->request->getVar('nominal[]');
            $id_keu_merge = array_merge($id_keu_merge, $idkeu);
        }

        $len_bb = 0;
        if (is_array($this->request->getVar('berita_acara_bb[]'))) {
            //bb
            $len_bb = count($this->request->getVar('berita_acara_bb[]'));
            $idkeu_bb = $this->request->getVar('id_keuangan_bb[]');
            $ba_bb = $this->request->getVar('berita_acara_bb[]');
            $jt_bb = $this->request->getVar('jatuh_tempo_tgl_bb[]');
            $nm_bb = $this->request->getVar('nominal_bb[]');

            $id_keu_merge = array_merge($id_keu_merge, $idkeu_bb);
        }

        $dif = array_diff($list_tg, $id_keu_merge);

        //hapus tagihan di table keuangan
        if (count($id_keu_merge) > 0) {
            foreach ($dif as $v) {
                $this->keuanganModel->where('id_keuangan', $v)->delete();
            }
        } else {
            $this->keuanganModel->where('id_mkdt', $id_mkdt)->delete();
        }


        //input detail tagihan ke table keuangan
        $ft = [];
        for ($x = 0; $x < $len; $x++) {
            if ($idkeu[$x] != '' || $idkeu[$x] != null) {
                $ft['upt']['berita_acara'] = $ba[$x];
                $ft['upt']['jatuh_tempo_tgl'] = $jt[$x];
                $ft['upt']['nominal'] = $this->num($nm[$x]);
                $ft['upt']['status'] = "UM";
                $ft['upt']['id_mkdt'] = $id_mkdt;
                $ft['upt']['edit_by'] = user_id();

                $res['upt'][$x] = $this->keuanganModel->update($idkeu[$x], $ft['upt']);
            } else {
                $ft['ins'][$x]['berita_acara'] = $ba[$x];
                $ft['ins'][$x]['jatuh_tempo_tgl'] = $jt[$x];
                $ft['ins'][$x]['nominal'] = $this->num($nm[$x]);
                $ft['ins'][$x]['id_mkdt'] = $id_mkdt;
                $ft['ins'][$x]['status'] = "UM";
                $ft['ins'][$x]['add_by'] = user_id();
                $ft['ins'][$x]['edit_by'] = user_id();

                $res['ins'][$x] = $this->keuanganModel->insert($ft['ins'][$x]);
            }
        }

        $ft = [];
        for ($y = 0; $y < $len_bb; $y++) {
            if ($idkeu_bb[$y] != '' || $idkeu_bb[$y] != null) {
                $ft['upt']['berita_acara'] = $ba_bb[$y];
                $ft['upt']['jatuh_tempo_tgl'] = $jt_bb[$y];
                $ft['upt']['nominal'] = $this->num($nm_bb[$y]);
                $ft['upt']['status'] = "BB";
                $ft['upt']['id_mkdt'] = $id_mkdt;
                $ft['upt']['edit_by'] = user_id();

                $res['upt'][$y] = $this->keuanganModel->update($idkeu_bb[$y], $ft['upt']);
            } else {
                $ft['ins'][$y]['berita_acara'] = $ba_bb[$y];
                $ft['ins'][$y]['jatuh_tempo_tgl'] = $jt_bb[$y];
                $ft['ins'][$y]['nominal'] = $this->num($nm_bb[$y]);
                $ft['ins'][$y]['id_mkdt'] = $id_mkdt;
                $ft['ins'][$y]['status'] = "BB";
                $ft['ins'][$y]['add_by'] = user_id();
                $ft['ins'][$y]['edit_by'] = user_id();

                $res['ins'][$y] = $this->keuanganModel->insert($ft['ins'][$y]);
            }
        }
        $ft = [];




        return $this->response->setJSON($response);
    }
    function isi_tagihan()
    {
        $response['token'] = csrf_hash();
        $id_mkdt = $this->request->getPost('mk-id_mkdt');

        //cek jika sudah ada pembayaran
        $cek = $this->db->table("keuangan")->where("id_mkdt", $id_mkdt)->get()->getResult();
        $cek_lp = $this->db->table("log_pembayaran")->where("id_mkdt", $id_mkdt)->get()->getResult();

        foreach ($cek_lp as $cl) {
            foreach ($cek as $c) {
                $q = array_search($cl->id_keuangan, (array) $c);
                if ($q == 'id_keuangan') {
                    $response['success'] = false;
                    $response['messages'] = 'Sudah ada pembayaran. Tidak bisa merubah detail biaya/tagihan';

                    return $this->response->setJSON($response);
                }
            }
        }

        ################################## update detail biaya #######################
        $time = strtotime($this->request->getVar('mk-tgl_harga'));
        $newformat = date('Y-m-d', $time);

        $data = [
            'id_hargajual' => $this->request->getVar('mk-id'),
            'tgl_harga' => $newformat,
            'harga_jual' => $this->num($this->request->getVar('mk-hargajual')),
            'harga_kpr' => $this->num($this->request->getVar('mk-kpr')),
            'harga_diskon_harga_jual' => $this->num($this->request->getVar('mk-diskon_harga_jual')),
            'harga_diskon_uang_muka' => $this->num($this->request->getVar('mk-diskon_uang_muka')),
            'harga_administrasi' => $this->num($this->request->getVar('mk-biaya_adm')),
            'harga_ppn' => $this->num($this->request->getVar('mk-harga_ppn')),
            'harga_bphtb' => $this->num($this->request->getVar('mk-bphtb')),
            'harga_biaya_proses' => $this->num($this->request->getVar('mk-biaya_proses')),
            'harga_penambahan' => $this->num($this->request->getVar('mk-harga_penambahan')),
            'harga_penambahan_tanah' => $this->num($this->request->getVar('mk-harga_penambahan_tanah')),
            'keterangan_penambahan_biaya' => $this->num($this->request->getVar('mk-keterangan_harga_penambahan')),
        ];

        $this->mkdtModel->update($id_mkdt, $data);

        ################################## insert ke tagihan ##########################
        if ($this->request->getVar('berita_acara[]')) {

            //input detail tagihan ke table keuangan
            //um
            $len = count($this->request->getVar('berita_acara[]'));
            $idkeu = $this->request->getVar('id_keuangan[]');
            $ba = $this->request->getVar('berita_acara[]');
            $jt = $this->request->getVar('jatuh_tempo_tgl[]');
            $nm = $this->request->getVar('nominal[]');

            //bb
            $len_bb = count($this->request->getVar('berita_acara_bb[]'));
            $idkeu_bb = $this->request->getVar('id_keuangan_bb[]');
            $ba_bb = $this->request->getVar('berita_acara_bb[]');
            $jt_bb = $this->request->getVar('jatuh_tempo_tgl_bb[]');
            $nm_bb = $this->request->getVar('nominal_bb[]');

            //input detail tagihan ke table keuangan
            $ft['ins'] = null;

            //hapus tagihan di table keuangan
            if (count($cek) > $len) {
                foreach ($cek as $a) {
                    if ($a->status == "UM") {
                        if (array_search($a->id_keuangan, $idkeu) === false) {
                            $this->keuanganModel->where('id_keuangan', $a->id_keuangan)->delete();
                        }
                    }
                }
            }
            if (count($cek) > $len_bb) {
                foreach ($cek as $a) {
                    if ($a->status == "BB") {
                        if (array_search($a->id_keuangan, $idkeu_bb) === false) {
                            $this->keuanganModel->where('id_keuangan', $a->id_keuangan)->delete();
                        }
                    }
                }
            }

            for ($x = 0; $x < $len; $x++) {
                if ($idkeu[$x] != '' || $idkeu[$x] != null) {
                    $ft['upt']['berita_acara'] = $ba[$x];
                    $ft['upt']['jatuh_tempo_tgl'] = $jt[$x];
                    $ft['upt']['nominal'] = $this->num($nm[$x]);
                    $ft['upt']['status'] = "UM";
                    $ft['upt']['id_mkdt'] = $id_mkdt;
                    $ft['upt']['edit_by'] = user_id();

                    $res['upt'][$x] = $this->keuanganModel->update($idkeu[$x], $ft['upt']);
                } else {
                    $ft['ins'][$x]['berita_acara'] = $ba[$x];
                    $ft['ins'][$x]['jatuh_tempo_tgl'] = $jt[$x];
                    $ft['ins'][$x]['nominal'] = $this->num($nm[$x]);
                    $ft['ins'][$x]['id_mkdt'] = $id_mkdt;
                    $ft['ins'][$x]['status'] = "UM";
                    $ft['ins'][$x]['add_by'] = user_id();
                    $ft['ins'][$x]['edit_by'] = user_id();

                    $res['ins'][$x] = $this->keuanganModel->insert($ft['ins'][$x]);
                }
            }

            $ft = [];
            for ($y = 0; $y < $len_bb; $y++) {
                if ($idkeu_bb[$y] != '' || $idkeu_bb[$y] != null) {
                    $ft['upt']['berita_acara'] = $ba_bb[$y];
                    $ft['upt']['jatuh_tempo_tgl'] = $jt_bb[$y];
                    $ft['upt']['nominal'] = $this->num($nm_bb[$y]);
                    $ft['upt']['status'] = "BB";
                    $ft['upt']['id_mkdt'] = $id_mkdt;
                    $ft['upt']['edit_by'] = user_id();

                    $res['upt'][$y] = $this->keuanganModel->update($idkeu_bb[$y], $ft['upt']);
                } else {
                    $ft['ins'][$y]['berita_acara'] = $ba_bb[$y];
                    $ft['ins'][$y]['jatuh_tempo_tgl'] = $jt_bb[$y];
                    $ft['ins'][$y]['nominal'] = $this->num($nm_bb[$y]);
                    $ft['ins'][$y]['id_mkdt'] = $id_mkdt;
                    $ft['ins'][$y]['status'] = "BB";
                    $ft['ins'][$y]['add_by'] = user_id();
                    $ft['ins'][$y]['edit_by'] = user_id();

                    $res['ins'][$y] = $this->keuanganModel->insert($ft['ins'][$y]);
                }
            }
            $ft = [];
        }
        $response['success'] = true;
        $response['messages'] = 'Data berhasil diperbaharui';

        return $this->response->setJSON($response);
        //insert ke table keuangan

        // if ($ft['ins'])
        //     $res['ins'] = $this->keuModel->insertBatch($ft['ins']);
    }
    function save_inv()
    {

        $response['token'] = csrf_hash();
        $data = [
            'no_inv' => $this->request->getVar('no_inv'),
            'id_mkdt' => $this->request->getVar('id_mkdt'),
            'id_konsumen' => $this->request->getVar('id_konsumen'),
            'id_kavling' => $this->request->getVar('id_kavling'),
            'id_kopsurat' => $this->request->getVar('id_kopsurat'),
            'tanggal_invoice' => $this->request->getVar('tanggal_invoice'),
            'tanggal_jatuh_tempo' => $this->request->getVar('tanggal_jatuh_tempo'),
            'tagihan' => $this->request->getVar('tagihan'),
            'terms' => $this->request->getVar('terms'),

            'add_by' => user_id(),
            'date_add' => date("Y-m-d  H:i:s")
        ];

        $r = $this->db->table('invoice_log')
            ->insert($data);

        if ($r) {
            $response['success'] = true;
            $response['messages'] = 'Data berhasil ditambahkan';
        } else {
            $response['success'] = false;
            $response['messages'] = 'Data berhasil diperbaharui';
        }
        return $this->response->setJSON($response);
    }
    function save_sb()
    {

        $response['token'] = csrf_hash();
        $r = $this->keuanganModel->update(
            $this->request->getVar('id_keuangan'),
            array(
                'sudah_dibayar' => $this->request->getVar('sb')
            )
        );

        if ($r) {
            $response['success'] = true;
            $response['messages'] = 'Data berhasil diperbaharui';
        } else {
            $response['success'] = false;
            $response['messages'] = 'Data berhasil diperbaharui';
        }
        return $this->response->setJSON($response);
    }
    function save()
    {
        $response['token'] = csrf_hash();
        // print_r($this->request->getVar());
        // die();

        $id_kavling = $this->request->getVar('id_kavling');
        $id_mkdt = $this->request->getVar('id_mkdt');

        $berita_acara = $this->request->getPost('bt-berita_acara_um');
        $bayar_tagihan = $this->num($this->request->getVar('bt-bayar_tagihan_um'));
        $tanggal_bayar = $this->request->getVar('bt-tanggal_bayar_um');
        $id_keus = $this->request->getVar('bt-for');
        $text_keu = $this->request->getVar('text_um');

        $berita_acara_bb = $this->request->getPost('bt-berita_acara_bb');
        $bayar_tagihan_bb = $this->num($this->request->getVar('bt-bayar_tagihan_bb'));
        $tanggal_bayar_bb = $this->request->getVar('bt-tanggal_bayar_bb');
        $id_keu_bbs = $this->request->getVar('bt-for_bb');
        $text_keu_bb = $this->request->getVar('text_bb');

        $id_keu = '';
        if ($id_keus) {
            foreach ($id_keus as $id) {
                $id_keu .= $id . ";";
            }
        }
        $id_keu_bb = '';
        if ($id_keu_bbs) {
            foreach ($id_keu_bbs as $id) {
                $id_keu_bb .= $id . ";";
            }
        }

        // $response['id_keu'] = $text_keu;
        // $response['id_keu_bb'] = $text_keu_bb;

        // return $this->response->setJSON($response);        
        // die();


        $e = $this->request->getVar('e');

        $is_lunas = $this->request->getVar('is_lunas') ? 1 : 0;

        // $refund_paid = $this->request->getVar('refund_paid');
        $refund_paid = 1;

        $kav = $this->get_kavling($id_kavling);

        if ($this->request->getVar('status_mkdt') == "Batal") {
            $nominal_refund = $this->num($this->request->getVar("nominal_refund"));
            $tgl_refund = $this->request->getVar("tanggal_refund");
            //jika refund sudah dibayar
            $d['refund_paid'] = ($refund_paid == 1) ? 1 : 0;
            $d['refund'] = $nominal_refund;
            $d['refund_tgl'] = $tgl_refund;


            try {
                $this->mkdtModel->update($id_mkdt, $d);
                // insert into log_pembayaran
                $data = array(
                    "id_mkdt" => $id_mkdt,
                    "nominal" => $nominal_refund,
                    "payment_type" => "Refund",
                    "tanggal_bayar" => $tgl_refund,
                    "keterangan" => "Refund: " . $this->request->getVar("keterangan_refund") . " - " . $kav->nama_konsumen . " - " . $kav->nama_jalan . " No. " . $kav->no_kavling . "",
                    "add_by" => user_id(),
                    "edit_by" => user_id()
                );

                if ($this->lpModel->insert($data)) {
                    $response['success'] = true;
                    $response['messages'] = 'Data berhasil ditambah';
                } else {
                    $response['success'] = false;
                    $response['messages'] = 'Terjadi kesalahan';
                }
            } catch (\Exception $e) {
                $response['success'] = false;
                $response['messages'] = 'Terjadi kesalahan: ' . $e->getMessage();
            }
        } else {
            $f['id_keuangan'] = $this->request->getPost('b-for');

            //jika is_lunas di cek
            $this->mkdtModel->update($id_mkdt, ['is_lunas' => $is_lunas]);


            $f['booking_fee_paid'] = '';
            $f['id_mkdt'] = $id_mkdt;

            //input booking disabled jika booking_fee_paid == 1 
            if ($this->request->getPost('booking_fee_paid')) {
                $f['booking_fee_paid'] = $this->request->getPost('booking_fee_paid');
                $f['booking_fee'] = $this->num($this->request->getPost('keu_booking_fee'));
                $f['booking_fee_tgl'] = $this->request->getPost('keu_booking_tgl');
            }

            // //jika input bayar tagihan diisi lebih dari nol
            // if ($bayar_tagihan != "0") {
            //     $f['terakhir_bayar_berita_acara'] = $berita_acara;
            //     $f['terakhir_bayar_nominal'] = $bayar_tagihan;
            //     $f['terakhir_bayar_tgl'] = $tanggal_bayar;
            // }

            $f['keterangan'] = $this->request->getPost('keuangan_keterangan');

            // bayar booking
            //cek apakah booking fee sudah di bayar
            $is_paid = $this->mkdtModel->select('booking_paid')->where('id_mkdt', $id_mkdt)->first();
            $is_paid = ($is_paid) ? $is_paid->booking_paid : 0;

            //jika booking fee belum di bayar, maka insert ke table log pembayaran
            if (!$is_paid && $f['booking_fee_paid']) {
                $data = array(
                    "id_mkdt" => $id_mkdt,
                    "nominal" => $f['booking_fee'],
                    "tanggal_bayar" => $f['booking_fee_tgl'],
                    "payment_type" => "Booking",
                    "keterangan" => "Booking: " . $kav->nama_konsumen . " - " . $kav->nama_jalan . " No. " . $kav->no_kavling . "",
                    "add_by" => user_id(),
                    "edit_by" => user_id()
                );

                $this->lpModel->insert($data);

                //update table mkdt set booking fee sudah dibayar
                $this->mkdtModel->update(
                    $id_mkdt,
                    array(
                        'booking_paid' => $f['booking_fee_paid']
                    )
                );
            }
            //end of bayar booking

            //insert into log_pembayaran
            if ($e == '') {
                if ($bayar_tagihan > 0) {
                    $data = array(
                        "id_mkdt" => $id_mkdt,
                        "id_keuangan" => $id_keu,
                        "nominal" => $bayar_tagihan,
                        "payment_type" => $text_keu,
                        "tanggal_bayar" => $tanggal_bayar,
                        "keterangan" => "(" . $berita_acara . ") Pembayaran: " . $text_keu . " - " . $kav->nama_konsumen . " - " . $kav->nama_jalan . " No. " . $kav->no_kavling . "",
                        "add_by" => user_id(),
                        "edit_by" => user_id()
                    );
                    $this->lpModel->insert($data);
                }
            } elseif ($e == 'bb') {
                if ($bayar_tagihan_bb > 0) {
                    $data = array(
                        "id_mkdt" => $id_mkdt,
                        "id_keuangan" => $id_keu_bb,
                        "nominal" => $bayar_tagihan_bb,
                        "payment_type" => $text_keu_bb,
                        "tanggal_bayar" => $tanggal_bayar_bb,
                        "keterangan" => "(" . $berita_acara_bb . ")Pembayaran: " . $text_keu_bb . " - " . $kav->nama_konsumen . " - " . $kav->nama_jalan . " No. " . $kav->no_kavling . "",
                        "add_by" => user_id(),
                        "edit_by" => user_id()
                    );
                    $this->lpModel->insert($data);
                }
            }

            $response['success'] = true;
            $response['messages'] = "Data berhasil diinput";
        }

        return $this->response->setJSON($response);
    }
    protected function get_kavling($id_kavling)
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
    function list_tagihan()
    {
        $data['content'] = 'keuangan/list-tagihan';
        $data['data']['controller'] = 'Keuangan';
        $data['data']['title'] = 'List Tagihan';

        return view('template', $data);
    }
    function getListTagihan()
    {
        $data['token'] = csrf_hash();
        $data['data'] = array();

        $var = $this->request->getVar();

        $colum = ['nama_konsumen', 'nama_jalan', 'no_kavling'];
        $condition = [
            // 'mkdt.status_mkdt !=' => "Batal",
            'mkdt.is_lunas' => 0
        ];
        //get mkdt query 
        $query = $this->db->table('mkdt')
            ->select('
             mkdt.*,

             kavling.no_kavling,

             jalan.id_jalan,
             jalan.nama_jalan,
             cluster.id_cluster,
             cluster.nama_cluster,
             proyek.id_proyek,
             proyek.nama_proyek,
             tipe.tipe_rumah,
             produksi.progres_bangunan,

             konsumen.nama_konsumen,
             konsumen.hp_konsumen,
             (select sum(nominal) from log_pembayaran where log_pembayaran.id_mkdt = mkdt.id_mkdt) as sudah_bayar,
             (SELECT CONCAT(keuangan.jatuh_tempo_tgl, ":", keuangan.berita_acara) FROM keuangan WHERE keuangan.id_mkdt = mkdt.id_mkdt AND sudah_dibayar = 0 ORDER BY jatuh_tempo_tgl ASC LIMIT 1 ) AS keu
         ')
            ->join('kavling', "kavling.id_mkdt = mkdt.id_mkdt")
            ->join('produksi', "kavling.id_produksi = produksi.id_produksi", 'left')
            ->join('tipe', "tipe.id_tipe = kavling.id_tipe")
            ->join('konsumen', "konsumen.id_konsumen = mkdt.id_konsumen", 'left')
            ->join('jalan', "jalan.id_jalan = kavling.id_jalan")
            ->join('cluster', "jalan.id_cluster = cluster.id_cluster")
            ->join('proyek', "proyek.id_proyek = cluster.id_proyek");

        if ($var['id_jalan'])
            $condition = array_merge($condition, ["jalan.id_jalan" => $var['id_jalan']]);
        elseif ($var['id_cluster'])
            $condition = array_merge($condition, ["cluster.id_cluster" => $var['id_cluster']]);
        else
            $condition = array_merge($condition, ["proyek.id_proyek" => $var['id_proyek']]);
        $result = $this->if_where($var, $colum, $condition, $query);

        $result
            ->offset($var['start'])
            ->limit($var['length']);

        $x = $result->get();

        $data['draw'] = $var['draw'];

        //count filtered
        $countfiltered = $this->db->table("mkdt")
            ->select('
                    mkdt.*,
    
                    kavling.no_kavling,
    
                    jalan.id_jalan,
                    jalan.nama_jalan,
                    cluster.id_cluster,
                    cluster.nama_cluster,
                    proyek.id_proyek,
                    proyek.nama_proyek,
                    tipe.tipe_rumah,
                    produksi.progres_bangunan,
    
                    konsumen.nama_konsumen,
                    konsumen.hp_konsumen,
                    (select sum(nominal) from log_pembayaran where log_pembayaran.id_mkdt = mkdt.id_mkdt) as sudah_bayar
                ')
            ->join('kavling', "kavling.id_mkdt = mkdt.id_mkdt")
            ->join('produksi', "kavling.id_produksi = produksi.id_produksi", 'left')
            ->join('tipe', "tipe.id_tipe = kavling.id_tipe")
            ->join('konsumen', "konsumen.id_konsumen = mkdt.id_konsumen", 'left')
            ->join('jalan', "jalan.id_jalan = kavling.id_jalan")
            ->join('cluster', "jalan.id_cluster = cluster.id_cluster")
            ->join('proyek', "proyek.id_proyek = cluster.id_proyek");

        // $countTotal = $countfiltered;

        $countfiltered = $this->if_where($var, $colum, $condition, $countfiltered);
        $data['recordsFiltered'] = count($countfiltered->get()->getResult());
        //count total
        $condition = [
            // 'mkdt.status_mkdt !=' => "Batal",
            'mkdt.is_lunas' => 0
        ];
        $countTotal = $this->db->table("mkdt")
            ->select("count(mkdt.id_mkdt) as count")
            ->join('kavling', "kavling.id_mkdt = mkdt.id_mkdt")
            ->join('produksi', "kavling.id_produksi = produksi.id_produksi", 'left')
            ->join('tipe', "tipe.id_tipe = kavling.id_tipe")
            ->join('konsumen', "konsumen.id_konsumen = mkdt.id_konsumen", 'left')
            ->join('jalan', "jalan.id_jalan = kavling.id_jalan")
            ->join('cluster', "jalan.id_cluster = cluster.id_cluster")
            ->join('proyek', "proyek.id_proyek = cluster.id_proyek")
            ->where($condition);

        $data['recordsTotal'] = $countTotal->get()->getResult()[0]->count;

        //looping data untuk datatable
        $no = $var['start'];
        foreach ($x->getResult() as $key => $v) {
            $no++;

            $ops = '<div class="btn-group">';
            $ops .= '	<button type="button" class="btn btn-sm btn-info" onclick="edit(' . $v->id_mkdt . ')"><i class="fa fa-edit"></i></button>';
            $ops .= '	<button type="button" class="btn btn-sm btn-danger" onclick="remove(' . $v->id_mkdt . ')"><i class="fa ' . $no . '"></i></button>';
            $ops .= '</div>';

            $jt = "";
            if ($v->keu) {
                $jatuh_tempo = explode(":", $v->keu);
                $jt = $this->format_tgl($jatuh_tempo[0]) . "<br/>(" . $jatuh_tempo[1] . ")";
            }


            $tot =
                $v->harga_uang_muka
                + $v->harga_administrasi
                + $v->harga_penambahan
                + $v->harga_penambahan_tanah
                + $v->harga_penambahan_um
                + $v->harga_ppn
                + $v->harga_bphtb
                + $v->harga_biaya_proses
                - $v->harga_diskon_uang_muka
                - $v->harga_diskon_hargajual;

            $persentase = ($v->sudah_bayar == 0) ? 0 : $v->sudah_bayar / $tot * 100;

            $data['data'][$key] = array(

                $no,
                number_format($tot),
                number_format($v->sudah_bayar),
                number_format($tot - $v->sudah_bayar),
                number_format($persentase) . "%",

                $jt,

                $v->nama_jalan,
                $v->no_kavling,
                $v->tipe_rumah,
                $v->nama_konsumen,
                $v->hp_konsumen,
                $v->progres_bangunan . "%",
                $this->format_tgl($v->booking_tgl),

                number_format($v->booking_fee),
                number_format($v->harga_jual),
                number_format($v->harga_kpr),
                number_format($v->harga_penambahan),
                number_format($v->harga_administrasi),
                number_format($v->harga_ppn),
                number_format($v->harga_bphtb),
                number_format($v->harga_biaya_proses),
                number_format($v->harga_diskon_uang_muka),


                $this->is_active($v->wawancara, "Sudah", "Belum"),
                $this->format_tgl($v->wawancara_tgl),
                $this->is_active($v->sp3k, "Sudah", "Belum"),
                $this->format_tgl($v->sp3k_tgl),
                $this->format_tgl($v->rencana_akad_tgl),
                $this->is_active($v->akad, "Sudah", "Belum"),
                $this->format_tgl($v->akad_tgl),
                $v->add_by,
                $this->format_tgl($v->created_at),
                $v->edit_by,
                $this->format_tgl($v->updated_at),
                $ops
            );
        }
        return $this->response->setJSON($data);
    }
    function riwayat_bayar()
    {
        $data['content'] = 'keuangan/riwayat-bayar';
        $data['data']['controller'] = 'Keuangan';
        $data['data']['title'] = 'Riwayat Pembayaran';

        return view('template', $data);
    }
    function getRiwayatBayar()
    {
        $data['token'] = csrf_hash();
        $data['data'] = array();

        $var = $this->request->getVar();

        $colum = ['log_pembayaran.keterangan', 'nominal', 'no_kavling', 'users.username'];
        $condition = [
            // 'mkdt.status_mkdt !=' => "Batal",
            'log_pembayaran.is_deleted' => 0
        ];
        //get mkdt query 
        $query = $this->db->table('log_pembayaran')
            ->select('
                log_pembayaran.*,
                users.username as ucreated_by
            ')
            ->join('mkdt', "log_pembayaran.id_mkdt = mkdt.id_mkdt")
            ->join('kavling', "kavling.id_kavling = mkdt.id_kavling")
            ->join('jalan', "jalan.id_jalan = kavling.id_jalan")
            ->join('cluster', "jalan.id_cluster = cluster.id_cluster")
            ->join('proyek', "proyek.id_proyek = cluster.id_proyek")
            ->join("users", "users.id = log_pembayaran.add_by");

        if ($var['id_jalan'])
            $condition = array_merge($condition, ["jalan.id_jalan" => $var['id_jalan']]);
        elseif ($var['id_cluster'])
            $condition = array_merge($condition, ["cluster.id_cluster" => $var['id_cluster']]);
        else
            $condition = array_merge($condition, ["proyek.id_proyek" => $var['id_proyek']]);

        $result = $this->if_where($var, $colum, $condition, $query);

        $result
            ->offset($var['start'])
            ->limit($var['length']);

        $x = $result->get();

        $data['draw'] = $var['draw'];

        //count filtered
        $countfiltered = $this->db->table('log_pembayaran')
            ->select('
            log_pembayaran.*,
            users.username as ucreated_by
        ')
            ->join('mkdt', "log_pembayaran.id_mkdt = mkdt.id_mkdt")
            ->join('kavling', "kavling.id_kavling = mkdt.id_kavling")
            ->join('jalan', "jalan.id_jalan = kavling.id_jalan")
            ->join('cluster', "jalan.id_cluster = cluster.id_cluster")
            ->join('proyek', "proyek.id_proyek = cluster.id_proyek")
            ->join("users", "users.id = log_pembayaran.add_by");

        // $countTotal = $countfiltered;

        $countfiltered = $this->if_where($var, $colum, $condition, $countfiltered);

        $data['recordsFiltered'] = count($countfiltered->get()->getResult());
        //count total
        $condition = [
            // 'mkdt.status_mkdt !=' => "Batal",
            'log_pembayaran.is_deleted' => 0
        ];
        $countTotal = $this->db->table('log_pembayaran')
            ->select('
                count(id_pembayaran) as count
            ')
            ->join('mkdt', "log_pembayaran.id_mkdt = mkdt.id_mkdt")
            ->join('kavling', "kavling.id_kavling = mkdt.id_kavling")
            ->join('jalan', "jalan.id_jalan = kavling.id_jalan")
            ->join('cluster', "jalan.id_cluster = cluster.id_cluster")
            ->join('proyek', "proyek.id_proyek = cluster.id_proyek")
            ->join("users", "users.id = log_pembayaran.add_by")
            ->where($condition);

        $data['recordsTotal'] = $countTotal->get()->getResult()[0]->count;

        //looping data untuk datatable
        $no = $var['start'];
        foreach ($x->getResult() as $key => $v) {
            $no++;

            $ops = '<div class="btn-group">';
            $ops .= '	<button type="button" class="btn btn-sm btn-info" onclick="edit(' . $v->id_mkdt . ')"><i class="fa fa-edit"></i></button>';
            $ops .= '	<button type="button" class="btn btn-sm btn-danger" onclick="remove(' . $v->id_mkdt . ')"><i class="fa ' . $no . '"></i></button>';
            $ops .= '</div>';

            $jt = "";

            $data['data'][$key] = array(

                $no,
                number_format($v->nominal),
                $this->format_tgl($v->tanggal_bayar),
                $v->keterangan,

                $v->ucreated_by,
                $this->format_tgl($v->created_at)
            );
        }
        return $this->response->setJSON($data);
    }
    protected function num($d)
    {
        // $d = str_replace('.', "", $d);
        $d = str_replace(',', "", $d);

        return $d;
    }
    function get_tagihan($id = null)
    {
        if (!$this->request->getVar('id_mkdt')) {
            $r['token'] = csrf_hash();
            return $this->response->setJSON($r);
        }
        $r['token'] = csrf_hash();
        //get profile perusahaan
        $r['compro'] = $this->comproModel->first();

        //get detail konsumen
        $r['detail'] = $this->mkdtModel
            ->select('
            mkdt.*,
            konsumen.nama_konsumen,
            konsumen.nik as nik_konsumen,
            konsumen.hp_konsumen,
            konsumen.alamat_konsumen,
            konsumen.status_konsumen
        ')
            ->join('konsumen', 'konsumen.id_konsumen = mkdt.id_konsumen')
            ->where('id_mkdt', $this->request->getVar('id_mkdt'))
            ->first();

        //get get list tagihan
        $r['list_tagihan'] = $this->keuanganModel
            ->where('id_mkdt', $this->request->getVar('id_mkdt'))
            ->find();

        //get log pembayaran
        $r['log_pembayaran'] = $this->lpModel
            ->select('
            log_pembayaran.*,
            users.username
        ')
            ->join('users', 'users.id = log_pembayaran.add_by')
            ->where('id_mkdt', $this->request->getVar('id_mkdt'))
            ->find();
        $r['sudah_bayar'] = $this->db->table('log_pembayaran')
            ->select("sum(nominal) as sudah_bayar")
            ->where('id_mkdt', $this->request->getVar('id_mkdt'))
            ->get()->getResult()[0]->sudah_bayar;


        if ($id == 'inv') {
            $r['invoice'] = $this->db->table('invoice_log')
                ->select("invoice_log.*,  users.username as uadd_by")
                ->join('users', 'users.id = invoice_log.add_by')
                ->where('id_mkdt', $this->request->getVar('id_mkdt'))
                ->get()->getResult();
        }


        return $this->response->setJSON($r);
    }
    function removeLP()
    {
        $r['token'] = csrf_hash();
        $id = $this->request->getVar('id_pembayaran');
        if ($id == '') {
            $r['success'] = false;
            $r['messages'] = "Tidak dapat melanjutkan perintah";
            return $this->response->setJSON($r);
        }
        // var_dump($this->lpModel->where('id_pembayaran', $id)->delete());
        if ($this->lpModel->where('id_pembayaran', $id)->delete()) {
            $r['success'] = true;
            $r['messages'] = "Berhasil menghapus data";
            return $this->response->setJSON($r);
        }
    }

    //deprecated pindah ke mpdf
    function doPrint()
    {
        $filename = date('y-m-d-H-i-s') . '-surat tagihan';

        // instantiate and use the dompdf class
        $dompdf = new Dompdf();

        $konsumen = $this->request->getVar('konsumen');
        $alamat = $this->request->getVar('alamat');
        $no_sruat = $this->request->getVar('no_sruat');
        $table = $this->request->getVar('table');
        $tanggal_surat_tagihan = $this->request->getVar('tanggal_surat_tagihan');






        $koplok = $this->db->table('kopsurat')->select("lokasi")->get()->getResult()[0]->lokasi;
        $kop = file_get_contents($koplok);
        $base64 = 'data:image/jpg;base64,' . base64_encode($kop);

        $data['table'] = $table;
        $data['konsumen'] = $konsumen;
        $data['alamat'] = $alamat;
        $data['no_sruat'] = $no_sruat;
        $data['tanggal_surat_tagihan'] = $this->format_tgl($tanggal_surat_tagihan);

        $data['kop'] = $base64;
        $data['nama'] = $this->db->table("karyawan")->select("nama_karyawan")->where("id_user", user_id())->get()->getResult()[0]->nama_karyawan;


        // return view('pdf/tagihan_keuangan', $data);

        // load HTML content
        $dompdf->loadHtml(view('pdf/tagihan_keuangan', $data));

        $dompdf->setPaper('A4', 'portrait');

        // render html as PDF
        $dompdf->render();

        // output the generated pdf
        $dompdf->stream($filename);
    }

    function printSPPTB()
    {

        $id_kavling = $this->request->getVar('id_kavling');
        $id_mkdt = $this->request->getVar('id_mkdt');
        $id_proyek = $this->request->getVar('id_proyek');

        if ($id_mkdt == 'null') {
            echo "Data konsumen belum tersimpan";
            return;
        }
        if (!$id_kavling || !$id_mkdt || !$id_proyek) {
            echo "woops";
            return;
        }

        // $data = [] ;
        $data['proyek'] = $this->db->table('proyek')->where('id_proyek', $id_proyek)->get()->getRow();
        $data['data'] = $this->db->table('kavling')
            ->select('
                `proyek`.`nama_proyek`,
                `proyek`.`alamat_proyek`,
                `proyek`.`kelurahan`,
                `proyek`.`kecamatan`,
                `proyek`.`kota`,
                `proyek`.`provinsi`,
                `proyek`.`nama_pt`,
                `cluster`.`nama_cluster`,
                `jalan`.`nama_jalan`,
                `tipe`.`no_tipe_rumah`,
                `tipe`.`tipe_rumah`,
                tipe.lb,
                `kavling`.`no_kavling`,
                kavling.luas_tanah,
                mkdt.*,
                `konsumen`.`no_spptb`,
                `konsumen`.`nama_konsumen`,
                `konsumen`.`nik`,
                `konsumen`.`npwp`,
                `konsumen`.`file_npwp`,
                `konsumen`.`file_ktp`,
                `konsumen`.`hp_konsumen`,
                `konsumen`.`alamat_konsumen`,
                `konsumen`.`tel_instansi`,
                `konsumen`.`email_konsumen`,
                `konsumen`.`sales`,
                `konsumen`.`nama_instansi`,
                `konsumen`.`alamat_instansi`,
                `konsumen`.`tel_instansi`
            ')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster')
            ->join('proyek', 'cluster.id_proyek = proyek.id_proyek')
            ->join('tipe', 'tipe.id_tipe = kavling.id_tipe')
            ->join('mkdt', 'mkdt.id_mkdt = kavling.id_mkdt')
            ->join('konsumen', 'konsumen.id_konsumen = mkdt.id_konsumen', 'left')
            ->where('kavling.id_kavling', $id_kavling)
            ->get()->getRow();

        $data['list_tagihan'] = $this->keuanganModel
            ->where('id_mkdt', $this->request->getVar('id_mkdt'))
            ->orderBy('jatuh_tempo_tgl')
            ->find();
        // //get log pembayaran
        // $r['log_pembayaran'] = $this->lpModel
        //     ->select('
        //     log_pembayaran.*,
        //     users.username
        // ')
        //     ->join('users', 'users.id = log_pembayaran.add_by')
        //     ->where('id_mkdt', $this->request->getVar('id_mkdt'))
        //     ->find();
        // $r['sudah_bayar'] = $this->db->table('log_pembayaran')
        //     ->select("sum(nominal) as sudah_bayar")
        //     ->where('id_mkdt', $this->request->getVar('id_mkdt'))
        //     ->get()->getResult()[0]->sudah_bayar;


        $html[0] = view('pdf/spptb-page1', $data);
        $filename = 'SPPTB - ' . $data['data']->nama_konsumen . ' - ' . date('Ymd') . '.pdf';

        $html[1] = view('pdf/spptb-page-next', $data);
        $header = '';
        $mg = [15, 15, 10, 2];

        // $mg = [$kop->pml, $kop->pmr, $kop->pmt, $kop->pmb];

        $this->mpdf->generate($html, $filename, $header, $mg);
        exit();
    }
    function print_tagihan($id = null)
    {
        $id = $this->request->getVar('id');
        if (!$id)
            return false;

        $data['inv'] = $this->db->table("invoice_log")
            ->where('no_inv', $id)
            ->get()->getResult()[0];


        $data['konsumen'] = $this->db->table('konsumen')
            ->select('konsumen.nama_konsumen, konsumen.alamat_konsumen, konsumen.hp_konsumen')
            ->join('mkdt', 'mkdt.id_konsumen = konsumen.id_konsumen')
            ->where('mkdt.id_mkdt', $data['inv']->id_mkdt)
            ->get()->getResult()[0];

        $kop = $this->db->table('kopsurat')
            ->where('id', $data['inv']->id_kopsurat)
            ->get()->getRow();

        $data['nama'] = $this->db->table('karyawan b')
            ->select('nama_karyawan')
            // ->join('users a', 'a.id = b.id_user')
            ->where('b.id_user', user_id())
            ->get()->getRow();

        $data['kavling'] = $this->db->table('kavling')
            ->select('proyek.nama_proyek,jalan.nama_jalan, no_kavling')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster')
            ->join('proyek', 'cluster.id_proyek = proyek.id_proyek')
            ->where('id_kavling', $data['inv']->id_kavling)
            ->get()->getRow();


        $koplok = $kop->lokasi;

        $data['kop'] = $koplok;
        $data['kop2'] = $kop;

        $html = view('pdf/invoice', $data);
        $filename = date('y-m-d-H-i-s') . '- Tagihan';

        $header = '
        <div style="
            background-image: url(' . base_url($koplok) . '); 
            background-size: cover; 
            width: ' . $kop->w . '; 
            height: ' . $kop->h . '; 
            position: fixed; 
            top: ' . $kop->mt . '; 
            bottom: ' . $kop->mb . '; 
            left: ' . $kop->ml . ';
            z-index:0;
            ">
        </div>';

        $mg = [$kop->pml, $kop->pmr, $kop->pmt, $kop->pmb];

        $this->mpdf->generate($html, $filename, $header, $mg);
        // die();
        // $this->pdf->generate($html, $filename, $kop->ukuran, 'portrait');
        exit;
    }
    function print_kuitansi($id = null, $id_mkdt = null, $id_poryek = null)
    {
        // if(!$id || !$id_mkdt)
        //     return false;

        $data['pembayaran'] = $this->db->table("log_pembayaran")
            ->select('*')
            ->where('id_pembayaran', $id)
            ->get()->getResult()[0];

        $data['konsumen'] = $this->db->table('konsumen')
            ->select('konsumen.nama_konsumen')
            ->join('mkdt', 'mkdt.id_konsumen = konsumen.id_konsumen')
            ->where('mkdt.id_mkdt', $id_mkdt)
            ->get()->getResult()[0];

        $data['proyek'] = $this->db->table('proyek')
            ->select('* ')
            ->where('id_proyek', $id_poryek)
            ->get()->getResult()[0];

        $data['kavling'] = $this->db->table('kavling')
            ->select('
                        kavling.no_kavling, 
                        jalan.nama_jalan, 
                        tipe.id_tipe,
                        tipe.tipe_rumah,
                        tipe.lb,
                        tipe.lt
                    ')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster')
            ->join('proyek', 'proyek.id_proyek = cluster.id_proyek')
            ->join('tipe', 'kavling.id_tipe = tipe.id_tipe')
            ->where('kavling.id_mkdt', $id_mkdt)
            ->get()->getResult();

        $filename = $data['konsumen']->nama_konsumen . '-Kuitansi Pembayaran';

        // var_dump($data['proyek']);
        // die();

        $html[0] = view('pdf/kuitansi_pembayaran', $data);

        $mg = [15, 15, 10, 2];

        // $mg = [$kop->pml, $kop->pmr, $kop->pmt, $kop->pmb];

        $this->mpdf->generate($html, $filename, $header = '', $mg, 'A5-L');

        exit();
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
    function format_tgl($tgl)
    {
        if ($tgl == "" || $tgl == "0000-00-00" || $tgl == null)
            return "-";
        return date_format(date_create($tgl), "d-M-Y");
    }
    function is_active($id, $texts, $textf)
    {
        $r = '<span class="badge badge-pill badge-light-danger" text-capitalized="">' . $textf . '</span>';
        if ($id == "1")
            $r = '<span class="badge badge-pill badge-light-success" text-capitalized="">' . $texts . '</span>';
        return $r;
    }
}

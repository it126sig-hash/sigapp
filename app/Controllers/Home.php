<?php

namespace App\Controllers;

use App\Models\ProyekModel;


class Home extends BaseController
{
    protected $db;
    protected $proyekModel;
    protected $notif;
    public function __construct()
    {
        $this->proyekModel = new ProyekModel();
        $this->notif = new Notif();
        $this->db = db_connect();
    }
    public function index()
    {
       echo "hello world";
    }
    public function dashboard()
    {
        // var_dump(session('token'));die();
        $data['content'] = 'home/dashboard';

        //ambil data proyek
        $data['data']['proyek'] = $this->proyekModel
            ->select("id_proyek, alamat_proyek, nama_proyek, siteplan, logo")
            ->findAll();

        return view('template', $data);
    }
    function getData($id_proyek, $sdate, $edate)
    {
        return  $this->db->table('mkdt')
            ->select('mkdt.id_mkdt, status_mkdt, booking_tgl, sp3k_tgl, sp3k_tgl_exp')

            ->join('kavling', 'kavling.id_kavling = mkdt.id_kavling')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster')
            ->join('proyek', 'proyek.id_proyek = cluster.id_proyek')
            ->where("proyek.id_proyek", $id_proyek)
            ->Where("booking_tgl between '$sdate'  and '$edate' ")
            ->orWhere("sp3k_tgl between '$sdate' and '$edate'")
            ->where("proyek.id_proyek", $id_proyek)
            ->get()->getResult();
    }

    function getDashboard()
    {
        $sdate = $this->request->getVar('sdate');
        $edate = $this->request->getVar('edate');
        $tahun = $this->request->getVar('tahun');

        $id_proyek = $this->request->getVar('id_proyek');

        //get statistik dashboard
        if ($this->request->getVar('statistik')) {
            $q = $this->getData($id_proyek, $sdate, $edate);

            $booking = 0;
            $akad = 0;
            $batal = 0;
            $sp3k = 0;

            foreach ($q as $a) {
                if ($a->status_mkdt == "Akad")
                    $akad++;
                else if ($a->status_mkdt == "Batal")
                    $batal++;

                if ($a->booking_tgl >= $sdate && $a->booking_tgl <= $edate){
                    $booking++;
                }

                if ($a->sp3k_tgl >= $sdate && $a->sp3k_tgl <= $edate)
                    $sp3k++;
            }
            $r = [
                'booking' => $booking,
                'akad' => $akad,
                'batal' => $batal,
                'sp3k' => $sp3k,
            ];
        }

        // get aktivitas dashboard
        if ($this->request->getVar('aktivitas')) {
            $r['aktivitas'] = $this->notif->getActivity(true, 0, $id_proyek) ;
        }

        if ($this->request->getVar('aktivitas')) {
            $r['cbooking'] = $this->getBookingAkad('booking_tgl',  $tahun, $id_proyek);
            $r['cakad'] = $this->getBookingAkad('akad_tgl',  $tahun, $id_proyek);
        }

        //get chart dashboard

        return $this->response->setJSON($r);
    }
    function getBookingAkad($field = 'booking_tgl',  $thn = null, $id_proyek)
    {
        if (!$thn)
            return;
        return $this->db->table('mkdt')
            ->select("
                YEAR($field) AS tahun, 
                MONTH($field) AS bulan,
                COUNT($field) AS jumlah
            ")
            ->join('kavling', 'kavling.id_kavling = mkdt.id_kavling')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster')
            ->join('proyek', 'proyek.id_proyek = cluster.id_proyek')

            ->where("proyek.id_proyek", $id_proyek)

            ->where("YEAR($field)", $thn)
            ->groupBy("YEAR($field), MONTH($field)")
            ->get()->getResult();
    }
    function loadAktivitas()
    {
        $r['token'] = csrf_hash();
        $offset = $this->request->getVar('offset');
        $id_proyek = $this->request->getVar('id_proyek');

        $r['aktivitas'] = $this->notif->getActivity(true, $offset, $id_proyek);

        return $this->response->setJSON($r);
    }
    function generateMenu($menu){
        $div = '<div id="'.$menu['id'].'" class="float div_menu">';
    }
    function getMenuBtn()
    {

        $k = '';
        $v = '';
        foreach (user()->getRoles() as $key => $val) {
            $k = $key;
            $v = $val;
        }

        //planing
        $menu[6] = '
        <div id="planning_menu" class="float div_menu">
            <div class="custom-control custom-switch custom-control-inline">
                <input onchange="hapus_seleksi()" type="checkbox" value="1" class="custom-control-input" id="tambah_jalan" name="tambah_jalan" />
                <label class="custom-control-label" for="tambah_jalan">Manual Seleksi</label>
            </div>
            <button id="selesai_pindah_btn" type="button" onclick="selesai_selection(1)" class="my-float btn-icon btn btn-primary btn-round btn-sm" style="display: none;">
                Selesai
            </button>
            <button id="batal_pindah_btn" type="button" onclick="selesai_selection(0)" class="my-float btn-icon btn btn-primary btn-round btn-sm" style="display: none;">
                Batal
            </button>

            <button id="add_kavling" type="button" onclick="tambah_kavling()" class="my-float btn-icon btn btn-primary btn-round btn-sm" data-toggle="modal">
                Tambah Data
            </button>

            <div class="btn-icon btn btn-primary btn-round btn-sm my-float" onclick="hapus_seleksi()">Hapus Seleksi</div>
            <div id="edit_kavling_batch" class="btn-icon btn btn-primary btn-round btn-sm my-float" onclick="edit_kavling_batch()">Ubah Kavling</div>
            <button id="planning_toggle_btn" class="btn-icon btn btn-primary btn-round btn-sm my-float" data-toggle="collapse" data-target="#planningCollapse" aria-expanded="false" aria-controls="planningCollapse">Cek Legenda</button>
            <div class="collapse" id="planningCollapse">
                <div class="card card-body">
                    Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident.
                </div>
            </div>
        </div>';
        //keunagan
        $menu[3]='
        <div id="keuangan_menu" class="float div_menu">
            <button type="button" class="my-float btn-icon btn btn-primary btn-round btn-sm" onclick="lihat_detail()">
                Lihat Detail
            </button>

            <div class="btn-icon btn btn-primary btn-round btn-sm my-float" onclick="hapus_seleksi()">Hapus Seleksi</div>
            <div id="isi_tagihan-btn" class="btn-icon btn btn-primary btn-round btn-sm my-float" onclick="isi_data_konsumen()">Isi Data Konsumen</div>
            <div id="bayar_tagihan-btn" class="btn-icon btn btn-primary btn-round btn-sm my-float" onclick="isi_data()">Bayar Tagihan</div>
            <div id="print_tagihan-btn" class="btn-icon btn btn-primary btn-round btn-sm my-float" onclick="print_tagihan()">Print Tagihan</div>
            <div id="dana_akad-btn" class="btn-icon btn btn-primary btn-round btn-sm my-float" onclick="dana_akad()">Dana Akad</div>
            <div class="btn-icon btn btn-primary btn-round btn-sm my-float" onclick="terima_batal()">Batal Booking</div>
        </div>';
        //mkdt
        $menu[4] = '
        <div id="mkdt_menu" class="float div_menu">
            <button type="button" class="my-float btn-icon btn btn-primary btn-round btn-sm" onclick="lihat_detail()">
                Lihat Detail
            </button>

            
            <div class="btn-icon btn btn-primary btn-round btn-sm my-float" onclick="hapus_seleksi()">Hapus Seleksi</div>
            <div class="btn-icon btn btn-primary btn-round btn-sm my-float" onclick="open_set_harga()">Set Harga</div>
            <div class="btn-icon btn btn-primary btn-round btn-sm my-float" onclick="open_set_turun_pembangunan()">Turun Pembangunan</div>
            <div id="edit_kavling_batch" class="btn-icon btn btn-primary btn-round btn-sm my-float" onclick="isi_data()">Isi/ubah Data</div>
            <div class="btn-icon btn btn-primary btn-round btn-sm my-float" onclick="ajukan_batal()">Batal Booking</div>
        </div>';
        //produksi
        $menu[7] = '
        <div id="produksi_menu" class="float div_menu">
            <button type="button" class="my-float btn-icon btn btn-primary btn-round btn-sm" onclick="lihat_detail()">
                Lihat Detail
            </button>

            
            <div class="btn-icon btn btn-primary btn-round btn-sm my-float" onclick="hapus_seleksi()">Hapus Seleksi</div>
            <div class="btn-icon btn btn-primary btn-round btn-sm my-float" onclick="isi_data()">Isi/ubah Data</div>
            <div class="btn-icon btn btn-primary btn-round btn-sm my-float" onclick="buat_slf()">SLF</div>
            <div class="btn-icon btn btn-primary btn-round btn-sm my-float" onclick="cek_tanggal_pembangunan(true)">Bangunan Belum Selesai</div>
            <div class="btn-icon btn btn-primary btn-round btn-sm my-float" onclick="open_komplain_produksi()">Komplain</div>
        </div>';

        //sales
        $menu[8] = '
        <div id="sales_menu" class="float div_menu">
            <button type="button" class="my-float btn-icon btn btn-primary btn-round btn-sm" onclick="lihat_detail()">
                Lihat Detail
            </button>

            
            <div class="btn-icon btn btn-primary btn-round btn-sm my-float" onclick="hapus_seleksi()">Hapus Seleksi</div>
            <div class="btn-icon btn btn-primary btn-round btn-sm my-float" onclick="open_checklist_sales()">Checklist</div>
            <div class="btn-icon btn btn-primary btn-round btn-sm my-float" onclick="open_serah_terima()">Serah Terima</div>
            <div class="btn-icon btn btn-primary btn-round btn-sm my-float" onclick="open_komplain_sales()">Komplain</div>

        </div>';

        // Direksi
        $menu[9] = '
        <div id="direksi_menu" class="float div_menu">
            <button type="button" class="my-float btn-icon btn btn-primary btn-round btn-sm" onclick="lihat_detail()">
                Lihat Detail
            </button>
            
            <div class="btn-icon btn btn-primary btn-round btn-sm my-float" onclick="hapus_seleksi()">Hapus Seleksi</div>
            <div class="btn-icon btn btn-primary btn-round btn-sm my-float" onclick="open_diskresi()">Diskresi HargaJual</div>
        </div>';

        //other
        $menu[0] = '
        <div id="others_menu" class="float div_menu">
            <button type="button" class="my-float btn-icon btn btn-primary btn-round btn-sm" onclick="lihat_detail()">
                Lihat Detail
            </button>

            <div class="btn-icon btn btn-primary btn-round btn-sm my-float" onclick="hapus_seleksi()">Hapus Seleksi</div>
            <div id="edit_kavling_batch" class="btn-icon btn btn-primary btn-round btn-sm my-float" onclick="isi_data()">Isi/ubah Data</div>

        </div>';
        $arr = [3,4,6,7,8,9];
        if ($k != 1) {
            $menu = in_array($k, $arr) ? $menu[$k] : $menu[0];
        }else{
            $menu = implode('', $menu);
        }
        $r['token'] = csrf_hash();
        $r['menu'] = $menu;
        return $this->response->setJSON($r);
    }
    function getKop()
    {
        $data['token'] = csrf_hash();
        // $id_proyek = $this->request->getPost('id_proyek');

        $search = "";
        if ($this->request->getPost('search'))
            $search = $this->request->getPost('search');

        $data['data'] = $this->db->table('kopsurat')
            ->like('nama', $search)
            ->get()->getResult();
        // $data['token'] = csrf_hash();
        return $this->response->setJSON($data);
    }
}
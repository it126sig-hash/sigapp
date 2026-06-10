<?php

namespace App\Controllers;

use App\Models\ProyekModel;
use App\Services\FileAccessService;


class Home extends BaseController
{
    protected $db;
    protected $proyekModel;
    protected $notif;
    protected $fileAccessService;
    public function __construct()
    {
        $this->proyekModel = new ProyekModel();
        $this->notif = new Notif();
        $this->db = db_connect();
        $this->fileAccessService = new FileAccessService();
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
        foreach ($data['data']['proyek'] as $proyek) {
            $proyek->siteplan_access_url = $this->fileAccessService->accessUrl('proyek_siteplan', (int) $proyek->id_proyek);
            $proyek->logo_access_url = $this->fileAccessService->accessUrl('proyek_logo', (int) $proyek->id_proyek);
        }

        return view('template', $data);
    }
    function getData($id_proyek, $sdate, $edate)
    {
        $builder = $this->db->table("mkdt");

        // Apply query conditions
        $builder->select([
            "COUNT(CASE WHEN booking_tgl BETWEEN '$sdate' AND '$edate' THEN 1 END) AS jumlah_booking",
            "COUNT(CASE WHEN akad_tgl BETWEEN '$sdate' AND '$edate' THEN 1 END) AS jumlah_akad",
            "COUNT(CASE WHEN sp3k_tgl BETWEEN '$sdate' AND '$edate' THEN 1 END) AS jumlah_sp3k",
            // "COUNT(CASE WHEN kavling.perintah_bangun_tgl BETWEEN '$sdate' AND '$edate' THEN 1 END) AS jumlah_perintah_bangun",
            "COUNT(CASE WHEN produksi.tanggal_pembangunan BETWEEN '$sdate' AND '$edate' THEN 1 END) AS jumlah_pembangunan",
            "COUNT(CASE WHEN produksi.tanggal_pembangunan BETWEEN '$sdate' AND '$edate' and progres_bangunan = 100 THEN 1 END) AS jumlah_bangunan_selesai",
            "COUNT(CASE WHEN produksi.tanggal_selesai_pembangunan = null and progres_bangunan < 100 THEN 1 END) AS jumlah_bangunan_telat",

        ]);

        $builder->join('kavling', 'kavling.id_kavling = mkdt.id_kavling')
            ->join('produksi', 'produksi.id_produksi = kavling.id_produksi', 'left')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster')
            ->join('proyek', 'proyek.id_proyek = cluster.id_proyek')
            ->where("proyek.id_proyek", $id_proyek);



        // Execute and return the query result
        return $builder->get()->getRow();
    }

    function getDashboard()
    {
        $sdate = $this->request->getVar('sdate');
        $edate = $this->request->getVar('edate');
        $tahun = $this->request->getVar('tahun');

        $id_proyek = $this->request->getVar('id_proyek');

        //get statistik dashboard
        if ($this->request->getVar('statistik')) {

            // $booking = 0;
            // $akad = 0;
            // $batal = 0;
            // $sp3k = 0;

            // var_dump($q);die();

            // foreach ($q as $a) {
            //     if ($a->status_mkdt == "Akad")
            //         $akad++;
            //     else if ($a->status_mkdt == "Batal")
            //         $batal++;

            //     if ($a->booking_tgl >= $sdate && $a->booking_tgl <= $edate) {
            //         $booking++;
            //     }

            //     if ($a->sp3k_tgl >= $sdate && $a->sp3k_tgl <= $edate)
            //         $sp3k++;
            // }
            //get statistik pembangunan dashboard
            $q = $this->getData($id_proyek, $sdate, $edate);

            //get statistik dashboard
            $q = $this->getData($id_proyek, $sdate, $edate);
            $r = [
                'booking' => $q->jumlah_booking,
                'akad' => $q->jumlah_akad,
                'batal' => 0,
                'sp3k' => $q->jumlah_sp3k,
                'pembangunan' => $q->jumlah_pembangunan,
                'pembangunan_selesai' => $q->jumlah_bangunan_selesai,
                'pembangunan_telat' => $q->jumlah_bangunan_telat,
                // 'perintah_bangun' => $q->jumlah_perintah_bangun,
            ];
        }

        // get aktivitas dashboard
        if ($this->request->getVar('aktivitas')) {
            $r['aktivitas'] = $this->notif->getActivity(true, 0, $id_proyek);
        }

        if ($this->request->getVar('aktivitas')) {
            $r['cbooking'] = $this->getBookingAkad('booking_tgl',  $id_proyek, $tahun);
            $r['cakad'] = $this->getBookingAkad('akad_tgl', $id_proyek,  $tahun);
        }

        //get chart dashboard

        return $this->response->setJSON($r);
    }
    function getBookingAkad($field = 'booking_tgl',  $id_proyek = null, $thn = null, $bln = null)
    {
        if (!$thn)
            return;
        if ($bln) {
            return $this->db->table('mkdt')
                ->select("
                YEAR($field) AS tahun, 
                MONTH($field) AS bulan,
                day(booking_tgl) as hari,
                COUNT($field) AS jumlah
            ")
                ->join('kavling', 'kavling.id_kavling = mkdt.id_kavling')
                ->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
                ->join('cluster', 'cluster.id_cluster = jalan.id_cluster')
                ->join('proyek', 'proyek.id_proyek = cluster.id_proyek')

                ->where("proyek.id_proyek", $id_proyek)

                ->where("YEAR($field)", $thn)
                ->where("MONTH($field)", $bln)
                ->groupBy("YEAR($field), MONTH($field), day(booking_tgl)")
                ->get()->getResult();
        }
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
    function generateMenu($menu)
    {
        $div = '<div id="' . $menu['id'] . '" class="float div_menu">';
    }
    function getMenuBtn()
    {

        $k = '';
        $v = '';
        foreach (user()->getRoles() as $key => $val) {
            $k = $key;
            $v = $val;
        }

        $generik = '
            <button type="button" class="my-float btn-icon btn btn-success btn-round btn-sm" onclick="lihat_detail()">
                Lihat Detail
            </button>
            <div class="btn-icon btn btn-outline-warning btn-round btn-sm my-float" onclick="hapus_seleksi()">Hapus Seleksi</div>';

        //planing
        $menu[6] = '
        <div id="planning_menu" class="float div_menu">
            <div class="custom- trol custom-switch custom-control-inline">
                <input onchange="hapus_seleksi()" type="checkbox" value="1" class="custom-control-input" id="tambah_jalan" name="tambah_jalan" />
                <label class="custom-control-label" for="tambah_jalan">Manual Seleksi</label>
            </div>
            <button id="selesai_pindah_btn" type="button" onclick="selesai_selection(1)" class="my-float btn-icon btn btn-primary btn-round btn-sm" style="display: none;">
                Selesai
            </button>
            <button id="batal_pindah_btn" type="button" onclick="selesai_selection(0)" class="my-float btn-icon btn btn-danger btn-round btn-sm" style="display: none;">
                Batal
            </button>

            <button id="add_kavling" type="button" onclick="tambah_kavling()" class="my-float btn-icon btn btn-primary btn-round btn-sm" data-toggle="modal">
                Tambah Data
            </button>

            <button id="planning_undo_manual_selection" type="button" onclick="undo_manual_selection()" class="my-float btn-icon btn btn-outline-warning btn-round btn-sm">
                Undo Titik
            </button>
            <div class="btn-icon btn btn-secondary btn-round btn-sm my-float" onclick="hapus_seleksi()">Hapus Seleksi</div>
            <div id="edit_kavling_batch" class="btn-icon btn btn-primary btn-round btn-sm my-float" onclick="edit_kavling_batch()">Ubah Kavling</div>
            <button id="planning_toggle_btn" class="btn-icon btn btn-primary btn-round btn-sm my-float" data-toggle="collapse" data-target="#planningCollapse" aria-expanded="false" aria-controls="planningCollapse">Cek Legenda</button>
            <div class="collapse" id="planningCollapse">
                <div/ class="card card-body">
                </div>
            </div>
        </div>';
        //keunagan
        $menu[3] = '
        <div id="keuangan_menu" class="float div_menu">
            ' . $generik . '

            <div id="bayar_sumurbor-btn" class="btn-icon btn btn-primary btn-round btn-sm my-float" onclick="openCOSubkon(1)">Cash Out Subkon</div>

            <div id="bayar_sumurbor-btn" class="btn-icon btn btn-primary btn-round btn-sm my-float" onclick="isi_cashout()">Cash Out</div>
            <div id="bayar_tagihan-btn" class="btn-icon btn btn-primary btn-round btn-sm my-float" onclick="isi_data()">Bayar Tagihan</div>
            <div id="print_tagihan-btn" class="btn-icon btn btn-primary btn-round btn-sm my-float" onclick="print_tagihan()">Print Tagihan</div>
            <div id="dana_akad-btn" class="btn-icon btn btn-primary btn-round btn-sm my-float" onclick="dana_akad()">Dana Jaminan</div>
            <div class="btn-icon btn btn-primary btn-round btn-sm my-float" onclick="cek_jatuh_tempo(true)">List Jatuh Tempo</div>
            <div class="btn-icon btn btn-primary btn-round btn-sm my-float" onclick="terima_batal()">Batal Booking</div>
        </div>';
        //mkdt
        $menu[4] = '
        <div id="mkdt_menu" class="float div_menu">
            ' . $generik . '
            <div class="btn-icon btn btn-primary btn-round btn-sm my-float" onclick="open_set_harga()">Set Harga</div>
            <div class="btn-icon btn btn-primary btn-round btn-sm my-float" onclick="isi_data_konsumen()">Isi Data Konsumen</div>
            <div id="edit_kavling_batch" class="btn-icon btn btn-primary btn-round btn-sm my-float" onclick="isi_data()">Ubah Status Kavling</div>
            <div class="btn-icon btn btn-primary btn-round btn-sm my-float" onclick="open_set_turun_pembangunan()">Turun Pembangunan</div>
            <div class="btn-icon btn btn-primary btn-round btn-sm my-float" onclick="isi_si()">Standing Instruction</div>
            <div class="btn-icon btn btn-primary btn-round btn-sm my-float" onclick="buat_nominatif()">Buat Nominatif</div>
            <div class="btn-icon btn btn-primary btn-round btn-sm my-float" onclick="ajukan_batal()">Batal Booking</div>
        </div>';
        //produksi
        $menu[7] = '
        <div id="produksi_menu" class="float div_menu">
            ' . $generik . '
            <div id="btn-cashout_subkon-pr" class="btn-icon btn btn-primary btn-round btn-sm my-float" onclick="openCOSubkon()">Cash Out Subkon</div>

            <input type="checkbox" value="1" class="d-none" id="produksi_tambah_jalan" name="produksi_tambah_jalan" />
            <button id="produksi_add_jalan" type="button" onclick="start_tambah_jalan_produksi()" class="my-float btn-icon btn btn-primary btn-round btn-sm btn-prod">
                Tambah Jalan
            </button>
            <button id="produksi_add_jalan_ok" type="button" onclick="tambah_jalan_produksi()" class="my-float btn-icon btn btn-success btn-round btn-sm btn-prod d-none">OK</button>
            <button id="produksi_add_jalan_undo" type="button" onclick="undo_manual_selection()" class="my-float btn-icon btn btn-outline-warning btn-round btn-sm btn-prod d-none">Undo Titik</button>
            <button id="produksi_add_jalan_batal" type="button" onclick="cancel_tambah_jalan_produksi()" class="my-float btn-icon btn btn-outline-secondary btn-round btn-sm btn-prod d-none">Batal</button>
            <div id="produksi_add_jalan_hint" class="my-float text-warning font-weight-bold d-none">Tandai jalan yang akan dibuat</div>
            <div class="btn-icon btn btn-primary btn-round btn-sm my-float btn-prod" onclick="isi_data()">Isi/ubah Data</div>
            <div class="btn-icon btn btn-primary btn-round btn-sm my-float btn-prod" onclick="isi_pembayaran()">Pembayaran</div>
            <div class="btn-icon btn btn-primary btn-round btn-sm my-float btn-prod" onclick="buat_slf()">SLF</div>
            <div class="btn-icon btn btn-primary btn-round btn-sm my-float" onclick="cek_tanggal_pembangunan(true)">Bangunan Belum Selesai</div>
            <div class="btn-icon btn btn-primary btn-round btn-sm my-float btn-prod" onclick="open_komplain_produksi()">Komplain</div>
        </div>';

        //sales
        $menu[8] = '
        <div id="sales_menu" class="float div_menu">
           ' . $generik . '
            <div class="btn-icon btn btn-primary btn-round btn-sm my-float" onclick="open_checklist_sales()">Checklist</div>
            <div class="btn-icon btn btn-primary btn-round btn-sm my-float" onclick="open_serah_terima()">Serah Terima</div>
            <div class="btn-icon btn btn-primary btn-round btn-sm my-float" onclick="open_komplain_sales()">Komplain</div>

        </div>';

        // Direksi
        $menu[9] = '
        <div id="direksi_menu" class="float div_menu">
            ' . $generik . '
            <div class="btn-icon btn btn-primary btn-round btn-sm my-float" onclick="open_diskresi()">Diskresi HargaJual</div>
        </div>';

        // Target
        $menu[11] = '
        <div id="target_menu" class="float div_menu">
            ' . $generik . '
            <div class="btn-icon btn btn-primary btn-round btn-sm my-float" onclick="open_target_siteplan()">Set Target</div>
            <div class="btn-icon btn btn-primary btn-round btn-sm my-float" onclick="open_target_history()">Histori Target</div>
        </div>';

        //other
        $menu[0] = '
        <div id="others_menu" class="float div_menu">
            ' . $generik . '
            <div id="edit_kavling_batch" class="btn-icon btn btn-primary btn-round btn-sm my-float" onclick="isi_data()">Isi/ubah Data</div>

        </div>';
        $arr = [3, 4, 6, 7, 8, 9, 11];
        if ($k != 1) {
            $menu = in_array($k, $arr) ? $menu[$k] : $menu[0];
        } else {
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
    function getHak($id_user, $nama_hak = null)
    {
        $q = $this->db->table('hak_akses')
            ->like('id_users', $id_user);
        if ($nama_hak)
            $q->like('nama_hak', ";" . $nama_hak . ";");
        return $q->get()->getResult();
    }
}

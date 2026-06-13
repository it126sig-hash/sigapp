<?php

namespace App\Controllers;

use App\Models\ProyekModel;
use App\Services\FileAccessService;
use App\Services\SiteplanMenuService;


class Home extends BaseController
{
    protected $db;
    protected $proyekModel;
    protected $notif;
    protected $fileAccessService;
    protected $siteplanMenuService;
    public function __construct()
    {
        $this->proyekModel = new ProyekModel();
        $this->notif = new Notif();
        $this->db = db_connect();
        $this->fileAccessService = new FileAccessService();
        $this->siteplanMenuService = new SiteplanMenuService();
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
        $sdate = $this->db->escape($sdate);
        $edate = $this->db->escape($edate);

        // Apply query conditions
        $builder->select("
            COUNT(CASE WHEN mkdt.booking_tgl BETWEEN $sdate AND $edate THEN 1 END) AS jumlah_booking,
            COUNT(CASE WHEN mkdt.akad_tgl BETWEEN $sdate AND $edate THEN 1 END) AS jumlah_akad,
            COUNT(CASE WHEN mkdt.sp3k_tgl BETWEEN $sdate AND $edate THEN 1 END) AS jumlah_sp3k,
            COUNT(CASE WHEN mkdt.mkdt_batal_tgl BETWEEN $sdate AND $edate THEN 1 END) AS jumlah_batal,
            COUNT(CASE WHEN produksi.tanggal_pembangunan BETWEEN $sdate AND $edate THEN 1 END) AS jumlah_pembangunan,
            COUNT(CASE WHEN produksi.tanggal_selesai_pembangunan BETWEEN $sdate AND $edate AND produksi.progres_bangunan = 100 THEN 1 END) AS jumlah_bangunan_selesai,
            COUNT(CASE WHEN produksi.tanggal_rencana_selesai_pembangunan < CURDATE() AND produksi.tanggal_selesai_pembangunan IS NULL AND COALESCE(produksi.progres_bangunan, 0) < 100 THEN 1 END) AS jumlah_bangunan_telat
        ", false);

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
        $sdate = $this->request->getVar('sdate') ?: date('Y-m-01');
        $edate = $this->request->getVar('edate') ?: date('Y-m-t');
        $tahun = $this->request->getVar('tahun') ?: date('Y');

        $id_proyek = (int) $this->request->getVar('id_proyek');
        $withStatistik = $this->requestBool('statistik');
        $withAktivitas = $this->requestBool('aktivitas');
        $withChart = $this->requestBool('chart');
        $r = [
            'booking' => 0,
            'akad' => 0,
            'batal' => 0,
            'sp3k' => 0,
            'pembangunan' => 0,
            'pembangunan_selesai' => 0,
            'pembangunan_telat' => 0,
            'summary' => [],
            'finance' => [],
            'production' => [],
            'target' => [],
            'alerts' => [],
            'aktivitas' => [],
            'cbooking' => [],
            'cakad' => [],
        ];

        //get statistik dashboard
        if ($withStatistik) {

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
            $r['booking'] = (int) ($q->jumlah_booking ?? 0);
            $r['akad'] = (int) ($q->jumlah_akad ?? 0);
            $r['batal'] = (int) ($q->jumlah_batal ?? 0);
            $r['sp3k'] = (int) ($q->jumlah_sp3k ?? 0);
            $r['pembangunan'] = (int) ($q->jumlah_pembangunan ?? 0);
            $r['pembangunan_selesai'] = (int) ($q->jumlah_bangunan_selesai ?? 0);
            $r['pembangunan_telat'] = (int) ($q->jumlah_bangunan_telat ?? 0);
            $r['summary'] = $this->getDashboardProjectSummary($id_proyek, $sdate, $edate);
            $r['finance'] = $this->getDashboardFinanceSummary($id_proyek, $sdate, $edate);
            $r['production'] = $this->getDashboardProductionSummary($id_proyek, $sdate, $edate);
            $r['target'] = $this->getDashboardTargetSummary($id_proyek, (int) $tahun);
            $r['alerts'] = $this->buildDashboardAlerts($r['finance'], $r['production']);
        }

        // get aktivitas dashboard
        if ($withAktivitas) {
            $r['aktivitas'] = $this->notif->getActivity(true, 0, $id_proyek);
        }

        if ($withChart) {
            $r['cbooking'] = $this->getBookingAkad('booking_tgl',  $id_proyek, $tahun);
            $r['cakad'] = $this->getBookingAkad('akad_tgl', $id_proyek,  $tahun);
        }

        //get chart dashboard

        return $this->response->setJSON($r);
    }

    private function requestBool(string $key): bool
    {
        return filter_var($this->request->getVar($key), FILTER_VALIDATE_BOOLEAN);
    }

    private function getDashboardProjectSummary(int $id_proyek, string $sdate, string $edate): array
    {
        $row = $this->db->table('kavling')
            ->select("
                COUNT(DISTINCT kavling.id_kavling) AS total_kavling,
                COUNT(DISTINCT CASE WHEN COALESCE(kavling.id_mkdt, 0) = 0 THEN kavling.id_kavling END) AS kavling_tersedia,
                COUNT(DISTINCT CASE WHEN mkdt.status_mkdt = 'Booking' THEN kavling.id_kavling END) AS kavling_booking,
                COUNT(DISTINCT CASE WHEN mkdt.status_mkdt = 'Akad' THEN kavling.id_kavling END) AS kavling_akad,
                COUNT(DISTINCT CASE WHEN mkdt.status_mkdt = 'Batal' OR mkdt.is_batal = 1 THEN kavling.id_kavling END) AS kavling_batal,
                COUNT(DISTINCT CASE WHEN mkdt.is_lunas = 1 THEN kavling.id_kavling END) AS kavling_lunas
            ", false)
            ->join('mkdt', 'mkdt.id_kavling = kavling.id_kavling', 'left')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster')
            ->where('cluster.id_proyek', $id_proyek)
            ->get()
            ->getRow();

        $akad = (int) ($row->kavling_akad ?? 0);
        $booking = (int) ($row->kavling_booking ?? 0);

        return [
            'total_kavling' => (int) ($row->total_kavling ?? 0),
            'kavling_tersedia' => (int) ($row->kavling_tersedia ?? 0),
            'kavling_booking' => $booking,
            'kavling_akad' => $akad,
            'kavling_batal' => (int) ($row->kavling_batal ?? 0),
            'kavling_lunas' => (int) ($row->kavling_lunas ?? 0),
            'booking_to_akad_rate' => $booking > 0 ? round(($akad / $booking) * 100, 1) : 0,
        ];
    }

    private function getDashboardFinanceSummary(int $id_proyek, string $sdate, string $edate): array
    {
        $bill = $this->db->table('keuangan')
            ->select("
                COUNT(keuangan.id_keuangan) AS tagihan_belum_bayar,
                COALESCE(SUM(keuangan.nominal), 0) AS nominal_belum_bayar,
                COUNT(CASE WHEN keuangan.jatuh_tempo_tgl BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 1 END) AS tagihan_jatuh_tempo,
                COUNT(CASE WHEN keuangan.jatuh_tempo_tgl < CURDATE() THEN 1 END) AS tagihan_lewat_tempo
            ", false)
            ->join('mkdt', 'mkdt.id_mkdt = keuangan.id_mkdt')
            ->join('kavling', 'kavling.id_kavling = mkdt.id_kavling')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster')
            ->where('cluster.id_proyek', $id_proyek)
            ->where('keuangan.sudah_dibayar', 0)
            ->get()
            ->getRow();

        $payment = $this->db->table('log_pembayaran lp')
            ->select("
                COUNT(lp.id_pembayaran) AS pembayaran_count,
                COALESCE(SUM(lp.nominal), 0) AS pembayaran_masuk
            ", false)
            ->join('mkdt', 'mkdt.id_mkdt = lp.id_mkdt')
            ->join('kavling', 'kavling.id_kavling = mkdt.id_kavling')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster')
            ->where('cluster.id_proyek', $id_proyek)
            ->where('lp.tanggal_bayar >=', $sdate)
            ->where('lp.tanggal_bayar <=', $edate)
            ->get()
            ->getRow();

        $cashout = $this->db->table('cashout')
            ->select("
                COUNT(cashout.id) AS cashout_count,
                COALESCE(SUM(cashout.nominal), 0) AS cashout_total
            ", false)
            ->join('kavling', 'kavling.id_kavling = cashout.id_kavling')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster')
            ->where('cluster.id_proyek', $id_proyek)
            ->where('cashout.is_deleted', 0)
            ->where('cashout.tanggal_bayar >=', $sdate)
            ->where('cashout.tanggal_bayar <=', $edate)
            ->get()
            ->getRow();

        $cashoutSubkon = $this->db->table('cashout_subkon_detail csd')
            ->select("
                COUNT(DISTINCT csd.id_cashout_subkon_detail) AS cashout_subkon_jatuh_tempo,
                COALESCE(SUM(csd.nominal), 0) AS cashout_subkon_nominal
            ", false)
            ->join('cashout_subkon cs', 'cs.id_cashout_subkon = csd.id_cashout_subkon')
            ->join('cashout_subkon_kavling csk', 'csk.id_cashout_subkon = cs.id_cashout_subkon')
            ->join('kavling', 'kavling.id_kavling = csk.id_kavling')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster')
            ->where('cluster.id_proyek', $id_proyek)
            ->where('csd.is_paid', 0)
            ->where('csd.tanggal_jatuh_tempo <=', date('Y-m-d', strtotime('+7 days')))
            ->get()
            ->getRow();

        return [
            'tagihan_belum_bayar' => (int) ($bill->tagihan_belum_bayar ?? 0),
            'nominal_belum_bayar' => (float) ($bill->nominal_belum_bayar ?? 0),
            'tagihan_jatuh_tempo' => (int) ($bill->tagihan_jatuh_tempo ?? 0),
            'tagihan_lewat_tempo' => (int) ($bill->tagihan_lewat_tempo ?? 0),
            'pembayaran_count' => (int) ($payment->pembayaran_count ?? 0),
            'pembayaran_masuk' => (float) ($payment->pembayaran_masuk ?? 0),
            'cashout_count' => (int) ($cashout->cashout_count ?? 0),
            'cashout_total' => (float) ($cashout->cashout_total ?? 0),
            'cashout_subkon_jatuh_tempo' => (int) ($cashoutSubkon->cashout_subkon_jatuh_tempo ?? 0),
            'cashout_subkon_nominal' => (float) ($cashoutSubkon->cashout_subkon_nominal ?? 0),
        ];
    }

    private function getDashboardProductionSummary(int $id_proyek, string $sdate, string $edate): array
    {
        $row = $this->db->table('produksi')
            ->select("
                COUNT(DISTINCT produksi.id_produksi) AS total_produksi,
                COUNT(DISTINCT CASE WHEN produksi.tanggal_pembangunan IS NOT NULL AND COALESCE(produksi.progres_bangunan, 0) < 100 THEN produksi.id_produksi END) AS pembangunan_berjalan,
                COUNT(DISTINCT CASE WHEN produksi.progres_bangunan = 100 THEN produksi.id_produksi END) AS bangunan_selesai,
                COUNT(DISTINCT CASE WHEN produksi.tanggal_rencana_selesai_pembangunan < CURDATE() AND produksi.tanggal_selesai_pembangunan IS NULL AND COALESCE(produksi.progres_bangunan, 0) < 100 THEN produksi.id_produksi END) AS bangunan_telat,
                COALESCE(AVG(produksi.progres_bangunan), 0) AS progres_rata_rata,
                COUNT(DISTINCT CASE WHEN kavling.status_komplain IS NOT NULL AND kavling.status_komplain != 0 THEN kavling.id_kavling END) AS komplain_aktif
            ", false)
            ->join('kavling', 'kavling.id_produksi = produksi.id_produksi')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster')
            ->where('cluster.id_proyek', $id_proyek)
            ->get()
            ->getRow();

        return [
            'total_produksi' => (int) ($row->total_produksi ?? 0),
            'pembangunan_berjalan' => (int) ($row->pembangunan_berjalan ?? 0),
            'bangunan_selesai' => (int) ($row->bangunan_selesai ?? 0),
            'bangunan_telat' => (int) ($row->bangunan_telat ?? 0),
            'progres_rata_rata' => round((float) ($row->progres_rata_rata ?? 0), 1),
            'komplain_aktif' => (int) ($row->komplain_aktif ?? 0),
        ];
    }

    private function getDashboardTargetSummary(int $id_proyek, int $tahun): array
    {
        $summary = [
            'tahun' => $tahun,
            'target_kavling' => 0,
            'realisasi_booking' => 0,
            'realisasi_akad' => 0,
            'persen_booking' => 0,
            'persen_akad' => 0,
        ];

        if (!$this->db->tableExists('target_siteplan') || !$this->db->tableExists('target_siteplan_kavling')) {
            return $summary;
        }

        $target = $this->db->table('target_siteplan t')
            ->select('COUNT(DISTINCT tk.id_kavling) AS target_kavling')
            ->join('target_siteplan_kavling tk', 'tk.id_target = t.id_target')
            ->where('t.id_proyek', $id_proyek)
            ->where('t.tahun_target', $tahun)
            ->where('t.status', 1)
            ->where('t.deleted_at', null)
            ->get()
            ->getRow();

        $realisasi = $this->db->table('mkdt')
            ->select("
                COUNT(CASE WHEN YEAR(mkdt.booking_tgl) = " . (int) $tahun . " THEN 1 END) AS realisasi_booking,
                COUNT(CASE WHEN YEAR(mkdt.akad_tgl) = " . (int) $tahun . " THEN 1 END) AS realisasi_akad
            ", false)
            ->join('kavling', 'kavling.id_kavling = mkdt.id_kavling')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster')
            ->where('cluster.id_proyek', $id_proyek)
            ->get()
            ->getRow();

        $targetKavling = (int) ($target->target_kavling ?? 0);
        $booking = (int) ($realisasi->realisasi_booking ?? 0);
        $akad = (int) ($realisasi->realisasi_akad ?? 0);

        return [
            'tahun' => $tahun,
            'target_kavling' => $targetKavling,
            'realisasi_booking' => $booking,
            'realisasi_akad' => $akad,
            'persen_booking' => $targetKavling > 0 ? round(($booking / $targetKavling) * 100, 1) : 0,
            'persen_akad' => $targetKavling > 0 ? round(($akad / $targetKavling) * 100, 1) : 0,
        ];
    }

    private function buildDashboardAlerts(array $finance, array $production): array
    {
        return [
            [
                'label' => 'Tagihan lewat jatuh tempo',
                'value' => (int) ($finance['tagihan_lewat_tempo'] ?? 0),
                'type' => 'danger',
                'description' => 'Perlu ditagih atau diverifikasi pembayarannya',
            ],
            [
                'label' => 'Tagihan jatuh tempo 7 hari',
                'value' => (int) ($finance['tagihan_jatuh_tempo'] ?? 0),
                'type' => 'warning',
                'description' => 'Perlu follow up sebelum lewat tempo',
            ],
            [
                'label' => 'Cashout subkon jatuh tempo',
                'value' => (int) ($finance['cashout_subkon_jatuh_tempo'] ?? 0),
                'type' => 'warning',
                'description' => 'Termin subkon belum dibayar',
            ],
            [
                'label' => 'Bangunan telat',
                'value' => (int) ($production['bangunan_telat'] ?? 0),
                'type' => 'danger',
                'description' => 'Progres belum selesai melewati rencana selesai',
            ],
            [
                'label' => 'Komplain aktif',
                'value' => (int) ($production['komplain_aktif'] ?? 0),
                'type' => 'info',
                'description' => 'Komplain sales/produksi yang masih perlu dipantau',
            ],
        ];
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
        foreach (user()->getRoles() as $key => $val) {
            $k = $key;
        }

        $r['token'] = csrf_hash();
        $r['menu'] = $this->siteplanMenuService->renderForRole((int) $k);
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

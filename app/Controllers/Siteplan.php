<?php

namespace App\Controllers;

use App\Models\ProyekModel;
use App\Models\ClusterModel;
use App\Models\JalanModel;
use App\Models\KavlingModel;
use App\Models\TipeModel;
use App\Models\ConfigModel;
use App\Models\ProduksiModel;
use App\Models\LegalModel;
use App\Models\KeuanganModel;
use App\Models\MkdtModel;
use App\Models\LogPembayaranModel;
use App\Models\ChecklistSubItemModel;
use CodeIgniter\HTTP\Response;
use App\Controllers\Notif;

class Siteplan extends BaseController
{
    protected $proyekModel;
    protected $clusterModel;
    protected $jalanModel;
    protected $tipeModel;
    protected $kavlingModel;
    protected $configModel;
    protected $produksiModel;
    protected $legalModel;
    protected $mkdtModel;
    protected $keuanganModel;
    protected $lpModel;
    protected $siModel;
    protected $validation;
    protected $db;
    protected $notif;

    public function __construct()
    {
        $this->notif = new Notif();
        $this->proyekModel = new ProyekModel();
        $this->clusterModel = new ClusterModel();
        $this->jalanModel = new JalanModel();
        $this->tipeModel = new TipeModel();
        $this->kavlingModel = new KavlingModel();
        $this->configModel = new ConfigModel();
        $this->produksiModel = new ProduksiModel();
        $this->legalModel = new LegalModel();
        $this->mkdtModel = new MkdtModel();
        $this->keuanganModel = new KeuanganModel();
        $this->lpModel = new LogPembayaranModel();
        $this->siModel = new ChecklistSubItemModel();
        $this->validation = \Config\Services::validation();
        $this->db = \Config\Database::connect();
    }
    public function index()
    {
        // var_dump(session('token'));die();
        $data['content'] = 'siteplan/pilih_proyek';


        //ambil data proyek
        $data['data']['proyek'] = $this->proyekModel
            ->select("id_proyek, alamat_proyek, nama_proyek, siteplan, logo")
            ->findAll();

        return view('template', $data);
    }
    function view_siteplan($a = null)
    {
        $data['content'] = 'siteplan/master';
        $b = $this->db->table('profile_perusahaan');

        $data['data']['profile'] = $b->get()->getRow();

        session()->set([
            'id_proyek' => $a
        ]);

        //ambil data proyek 
        $data['data']['proyek'] = $this->proyekModel
            ->select("*")
            ->where("id_proyek", session('id_proyek'))
            ->first();

        if (in_groups(['9', '10', '4', '1'])) {
            // get data cluster
            $data['data']['cluster'] = $this->clusterModel
                ->select('id_cluster, nama_cluster')
                ->where('cluster.id_proyek', $data['data']['proyek']->id_proyek)
                ->findAll();

            //get data jalan
            $data['data']['jalan'] = $this->jalanModel
                ->select('id_jalan, nama_jalan, nama_cluster')
                ->join('cluster', 'cluster.id_cluster = jalan.id_cluster')
                ->where('cluster.id_proyek', $data['data']['proyek']->id_proyek)
                ->findAll();

            //get data tipe
            $data['data']['tipe'] = $this->tipeModel
                ->select('id_tipe, tipe_rumah, harga, no_tipe_rumah')
                ->where('id_proyek', $data['data']['proyek']->id_proyek)
                ->findAll();
        }


        if (in_groups(['1', '7', '8'])) {
            //get data ceklist
            $data['data']['list'] = $this->siModel
                ->select('
                checklist_group.nama_group,
                checklist_item.nama_item,
                checklist_subitem.id_subitem, 
                checklist_subitem.nama_subitem,
                ')
                ->join('checklist_item', 'checklist_item.id_item = checklist_subitem.id_item')
                ->join('checklist_group', 'checklist_item.id_group = checklist_group.id_group')
                ->where('checklist_item.is_active = 1')
                ->where('checklist_group.is_active = 1')
                ->where('checklist_subitem.is_active = 1')
                ->orderBy('checklist_item.id_group', 'asc')
                ->orderBy('checklist_item.id_item', 'asc')
                ->find();
        }


        //get config color shape
        $conf = $this->db->table('config_shape')->get()->getResult();
        foreach ($conf as $conf) {
            $c[$conf->config_name] = [
                'fill' => $conf->fill,
                'stroke' => $conf->stroke,
                'strokeWidth' => $conf->strokeWidth,
                'dashed' => $conf->dashed,
                'keterangan' => $conf->keterangan
            ];
        }
        $data['data']['conf'] = json_encode($c);

        // var_dump($data['data']['conf']);die();
        return view('template', $data);
    }
    function add_kavling()
    {
        $response = array();

        if ($this->request->getPost('id_jenis') == "kavling") {
            $id_jalan = $this->request->getPost('id_jalan');
            $no_kavling = $this->request->getPost('no_kavling');
            $pecah = explode(";", $no_kavling);

            for ($x = 0; $x < count($pecah); $x++) {
                $a = $this->db->table("kavling")
                    ->where('no_kavling', $pecah[$x])
                    ->where('id_jalan', $id_jalan)
                    ->get()
                    ->getRow();

                if ($a) {
                    $response['token'] = csrf_hash();
                    $response['success'] = false;
                    $response['messages'] = "No $a->no_kavling Sudah digunakan";
                    return $this->response->setJSON($response);
                }
            }
            $response = $this->add_kavling2();
            // } elseif ($this->request->getPost('id_jenis') == "jalan") {
        } else {
            $response = $this->add_others();
        }


        return $this->response->setJSON($response);
    }
    function add_kavling2()
    {
        $response = array();
        $response['token'] = csrf_hash();

        $fields['id_jalan'] = $this->request->getPost('id_jalan');
        $fields['id_tipe'] = $this->request->getPost('id_tipe');
        $fields['no_kavling'] = $this->request->getPost('no_kavling');
        $fields['points'] = $this->request->getPost('points');
        $fields['luas_tanah'] = $this->request->getPost('f_luas');
        $fields['status_tanah'] = $this->request->getPost('status_tanah');

        //multiple selection var
        $bpoints = $this->request->getPost('bpoints[]');
        $count_bpoints = count($bpoints);

        $pecah = explode(";", $fields['no_kavling']);
        // $pecah_last = $pecah[count($pecah) -1];
        // $count_pecah = ($pecah_last == "")?count($pecah)-1: count($pecah);


        $this->validation->setRules([
            'no_kavling' => ['label' => 'No Rumah', 'rules' => 'permit_empty|max_length[255]']
        ]);

        if ($this->validation->run($fields) == FALSE) {
            $response['success'] = false;
            $response['messages'] = $this->validation->listErrors();
        } else {
            if ($count_bpoints > 1) {
                for ($x = 0; $x < $count_bpoints; $x++) {
                    $fields['no_kavling'] = $pecah[$x];
                    $fields['points'] = $bpoints[$x];

                    if (!$this->kavlingModel->insert($fields)) {
                        $response['success'] = false;
                        $response['messages'] = 'Insertion error!';
                        //force return if error insert
                        return $this->response->setJSON($response);
                    } else {
                        $response['id'][$x] = $this->kavlingModel->getInsertID();
                        $response['no_kavling'][$x] = $pecah[$x];
                        $response['points'][$x] = $bpoints[$x];
                    }
                }
                $response['success'] = true;
                $response['messages'] = 'Data berhasil diinput';
            } else {
                if ($this->kavlingModel->insert($fields)) {
                    $response['success'] = true;
                    $response['messages'] = 'Data berhasil diinput';
                    $response['id'] = $this->kavlingModel->getInsertID();
                } else {
                    $response['success'] = false;
                    $response['messages'] = 'Insertion error!';
                }
            }
        }
        return $response;
    }
    function add_others()
    {
        $response = array();
        $response['token'] = csrf_hash();

        $builder = $this->db->table("others");

        $fields['id_jalan'] = $this->request->getPost('id_jalan');
        $fields['tipe'] = $this->request->getPost('id_jenis');
        $fields['points'] = $this->request->getPost('points');
        $fields['planning_luas'] = $this->request->getPost('f_luas');
        $fields['nama'] = $this->request->getPost('f_nama');
        $fields['planning_keterangan'] = $this->request->getPost('f_planning_keterangan');
        $fields['planning_add_by'] = user_id();
        $fields['planning_created_at'] = date('Y-m-d H:i:s');
        $fields['planning_edit_by'] = user_id();
        $fields['planning_updated_at'] = date('Y-m-d H:i:s');

        //multiple selection var
        // $bpoints = $this->request->getPost('bpoints[]');
        // $count_bpoints = count($bpoints);

        // $pecah = explode(";", $fields['no_kavling']);

        $this->validation->setRules([
            'id_cluster' => [
                'label' => 'Cluster',
                'rules' => 'permit_empty|max_length[255]'
            ]
        ]);

        if ($this->validation->run($fields) == FALSE) {
            $response['success'] = false;
            $response['messages'] = $this->validation->listErrors();
        } else {

            if ($builder->insert($fields)) {
                $response['success'] = true;
                $response['messages'] = 'Data berhasil diinput';
                // $response['id'] = $builder->insertID();
            } else {
                $response['success'] = false;
                $response['messages'] = 'Insertion error!';
            }
        }
        return $response;
    }
    function edit_kavling()
    {
        $response = array();
        $response['token'] = csrf_hash();

        $fields['id_kavling'] = $this->request->getPost('id_kavling');
        $fields['id_jalan'] = $this->request->getPost('id_jalan');
        $fields['id_tipe'] = $this->request->getPost('id_tipe');
        $fields['no_kavling'] = $this->request->getPost('no_kavling');
        $fields['status_tanah'] = $this->request->getPost('status_tanah');
        $fields['luas_tanah'] = $this->request->getPost('f_luas');

        $id = explode(";", $this->request->getPost('id_kavling'));
        $id_last = $id[count($id) - 1];
        $id_len = ($id_last == "") ? count($id) - 1 : count($id);

        $no = explode(";", $this->request->getPost('no_kavling'));
        $no_last = $no[count($no) - 1];
        $no_len = ($no_last == "") ? count($no) - 1 : count($no);

        $points = explode(";", $this->request->getPost('points'));
        $points_last = $points[count($points) - 1];
        $points_len = ($points_last == "") ? count($points) - 1 : count($points);

        if ($no_len != $id_len) {
            $response['success'] = false;
            $response['messages'] = 'Update error!';
            return $this->response->setJSON($response);
        }

        $this->validation->setRules([
            'no_kavling' => ['label' => 'No Rumah', 'rules' => 'permit_empty|max_length[255]']
        ]);

        if ($no_len > 0 || $id_len > 0) {
            for ($x = 0; $x < $no_len; $x++) {

                $fields['id_kavling'] = $id[$x];
                $fields['no_kavling'] = $no[$x];
                $fields['points'] = $points[$x];

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
    function edit_others()
    {
        $response = array();
        $response['token'] = csrf_hash();

        $builder = $this->db->table("others");

        $fields['id_jalan'] = $this->request->getPost('id_jalan');
        $fields['points'] = $this->request->getPost('points');
        $fields['tipe'] = $this->request->getPost('id_jenis');
        // $fields['points'] = $this->request->getPost('points');
        $fields['planning_luas'] = $this->request->getPost('f_luas');
        $fields['nama'] = $this->request->getPost('f_nama');
        $fields['planning_keterangan'] = $this->request->getPost('f_planning_keterangan');

        $fields['planning_edit_by'] = user_id();
        $fields['planning_updated_at'] = date('Y-m-d H:i:s');

        $id = $this->request->getPost('id_kavling');

        $this->validation->setRules([
            'no_kavling' => [
                'label' => 'No Rumah',
                'rules' => 'permit_empty|max_length[255]'
            ]
        ]);

        if ($this->validation->run($fields) == FALSE) {
            $response['success'] = false;
            $response['messages'] = $this->validation->listErrors();
        } else {
            $builder->where('id', $id);
            if ($builder->update($fields)) {
                $response['success'] = true;
                $response['messages'] = 'Data berhasil diperbaharui';
            } else {
                $response['success'] = false;
                $response['messages'] = 'Data gagal diperbaharui!';
            }
        }

        return $this->response->setJSON($response);
    }
    function get_config($id)
    {
        $result = $this->configModel->select("*")
            ->where('id_role', $id)
            ->first();
        return $result;
    }
    function get_kavling_all()
    {
        $id_divisi = $this->request->getVar('id_role');
        $id_cluster = $this->request->getVar('id_cluster');
        $id_jalan = $this->request->getVar('id_jalan');

        $result['token'] = csrf_hash();
        $result['config'] = [];

        $q = "kavling.*, 
        hargajual.hargajual,
        hargajual.tgl_harga,
        hargajual.is_subsidi,
        jalan.nama_jalan, 
        cluster.id_cluster, 
        cluster.nama_cluster,
        b.tipe_rumah as hj_tipe_rumah,
        b.no_tipe_rumah as hj_no_tipe_rumah,
        tipe.id_tipe,
        tipe.tipe_rumah,
        tipe.no_tipe_rumah,
        tipe.id_gambar_kerja,
        produksi.progres_bangunan,
        produksi.tanggal_pembangunan,
        produksi.tanggal_rencana_selesai_pembangunan,
        produksi.tanggal_selesai_pembangunan,
        produksi.keterangan as keterangan_produksi,

        users.username as harga_akhir_oleh_username,
        u.username as perintah_bangun_username";

        $divisi_queries = [
            3 => "mkdt.status_mkdt,
                  mkdt.is_lunas,
                  mkdt.is_subsidi as mkdt_is_subsidi,
                  mkdt.is_kpr,
                  mkdt.is_batal,
                  (SELECT jatuh_tempo_tgl FROM keuangan WHERE keuangan.id_mkdt = mkdt.id_mkdt and sudah_dibayar = 0 ORDER BY jatuh_tempo_tgl asc LIMIT 1) AS jatuh_tempo_tgl",
            4 => "mkdt.status_mkdt,
                  mkdt.booking_tgl,
                  mkdt.wawancara_tgl,
                  mkdt.sp3k_tgl,
                  mkdt.akad_tgl,
                  mkdt.is_subsidi as mkdt_is_subsidi,
                  mkdt.is_kpr,
                  mkdt.is_batal",
            5 => "pbb_pecah_nop,
                  pbb_pecah_luas_bumi,
                  pbb_pecah_njop_bumi,
                  pbb_pecah_luas_bangunan,
                  pbb_pecah_njop_bangunan,
                  pbb_pecah_tanggal_bayar,
                  pbb_pecah_jumlah_tagihan,
                  sertifikat_split_no_hgb,
                  sertifikat_split_tanggal_terbit,
                  sertifikat_split_tanggal_berakhir,
                  sertifikat_split_nib,
                  sertifikat_split_tanggal_surat_ukur,
                  sertifikat_split_no_surat_ukur,
                  sertifikat_split_luas_tanah,
                  sertifikat_balik_nama,
                  sertifikat_balik_nama_tgl_pengiriman,
                  sertifikat_balik_nama_ke,
                  pbg_no,
                  pbg_tanggal_terbit,
                  pbg_tanggal_pengajuan,
                  pbg_tipe,
                  pbg_status,
                  pbg_dikirim_ke,
                  pbg_tanggal_kirim,
                  bphtb_tanggal_verifikasi,
                  bphtb_jatuh_tempo,
                  bphtb_perpanjang_jatuh_tempo,
                  bphtb_tanggal_pembayaran,
                  bphtb_nominal_disetujui,
                  bphtb_tanggal_validasi,
                  bphtb_nominal_tervalidasi,
                  pph_tgl_permohonan,
                  pph_nominal_validasi,
                  pph_nominal_bayar,
                  pph_nominal_disetujui,
                  pph_tanggal_validasi,
                  pph_no_sket,
                  pph_kode_verifikasi,
                  pph_ntpn,
                  pph_tgl_bayar,
                  pph_jenis_validasi,
                  ajb_no,
                  ajb_tanggal,
                  ajb_notaris,
                  ajb_dikirim_ke,
                  ajb_tanggal_dikirim,
                  ppjb_no,
                  ppjb_tanggal,
                  ppjb_notaris",
            7 => "produksi.st_0,
                  produksi.st_25,
                  produksi.st_50,
                  produksi.st_75,
                  produksi.st_100,
                  produksi.slo,
                  produksi.bp,
                  produksi.lpa,
                  produksi.st_jalan,
                  produksi.st_saluran,
                  produksi.st_air,
                  mkdt.status_mkdt",
            8 => "mkdt.status_mkdt,
                  produksi.progres_bangunan"
        ];

        if (isset($divisi_queries[$id_divisi])) {
            $q .= ", " . $divisi_queries[$id_divisi];
            $a = $this->kavlingModel->select($q)->join('mkdt', 'mkdt.id_mkdt = kavling.id_mkdt', 'left');
        } else {
            $q .= ", mkdt.status_mkdt, mkdt.is_batal";
            $a = $this->kavlingModel->select($q)->join('mkdt', 'mkdt.id_mkdt = kavling.id_mkdt', 'left');
        }

        $a->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster')
            ->join('proyek', 'proyek.id_proyek = cluster.id_proyek')
            ->join('tipe', 'kavling.id_tipe = tipe.id_tipe')
            ->join('hargajual', 'hargajual.id = kavling.harga_akhir', "left")
            ->join('legal', 'legal.id_legal = kavling.id_legal', 'left')
            ->join('produksi', 'produksi.id_produksi = kavling.id_produksi', 'left')
            ->join("users as u", "u.id = kavling.perintah_bangun_oleh", "left")
            ->join('tipe b', 'hargajual.id_tipe = b.id_tipe', 'left')
            ->join("users", "users.id = kavling.harga_akhir_oleh", "left")
            ->where('cluster.id_proyek', $this->request->getVar('id_proyek'));

        if ($id_cluster) {
            $a->where('cluster.id_cluster', $id_cluster);
        }
        if ($id_jalan) {
            $a->where('kavling.id_jalan', $id_jalan);
        }

        $result['data'] = $a->find();
        return $this->response->setJSON($result);
    }
    function get_others()
    {
        $result['token'] = csrf_hash();
        $id = $this->request->getVar('id_kavling');

        $where = ['cluster.id_proyek' => $this->request->getVar('id_proyek')];
        if ($id != null || $id != "") {
            $where = ["others.id" => $id];
        }

        $q = $this->db->table('others')
            ->select('
                others.*,
                a.username as planning_add,
                b.username as produksi_add,
                c.username as legal_add,

                d.username as planning_edit,
                e.username as produksi_edit,
                f.username as legal_edit,

                jalan.nama_jalan, 
                cluster.id_cluster, 
                cluster.nama_cluster,
                ')
            ->join('jalan', 'jalan.id_jalan = others.id_jalan')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster')
            ->join('proyek', 'proyek.id_proyek = cluster.id_proyek')
            ->join("users as a", "a.id = others.planning_add_by", "left")
            ->join("users as b", "b.id = others.produksi_add_by", "left")
            ->join("users as c", "c.id = others.legal_add_by", "left")
            ->join("users as d", "d.id = others.planning_edit_by", "left")
            ->join("users as e", "e.id = others.produksi_edit_by", "left")
            ->join("users as f", "f.id = others.legal_edit_by", "left")
            ->where($where);

        $result['data'] = $q->get()->getResult();
        return $this->response->setJSON($result);
    }
    function get_kavling_by_multiple_id()
    {
        $result['token'] = csrf_hash();
        $len = count($this->request->getVar('id_kavling'));
        $id = $this->request->getVar('id_kavling');
        for ($x = 0; $x < $len; $x++) {
            $result['data'][$x] = $this->kavlingModel
                ->select('
                    kavling.*, 
                    jalan.nama_jalan, 
                    cluster.id_cluster, 
                    cluster.nama_cluster,
                    tipe.id_tipe,
                    tipe.tipe_rumah,
                    tipe.no_tipe_rumah,
                    tipe.id_gambar_kerja')
                ->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
                ->join('cluster', 'cluster.id_cluster = jalan.id_cluster')
                ->join('proyek', 'proyek.id_proyek = cluster.id_proyek')
                ->join('tipe', 'kavling.id_tipe = tipe.id_tipe')
                // ->join('config_shape', 'config_shape.id_config = kavling.'.$conf)
                // ->join('config_shape', 'config_shape.id_config = kavling.id_config')
                ->where('kavling.id_kavling', $id[$x])
                ->first();
        }


        return $this->response->setJSON($result);
    }
    function get_harga_kavling()
    {
        $result['token'] = csrf_hash();
        $len = count($this->request->getVar('id_kavling'));
        $id = $this->request->getVar('id_kavling');
        for ($x = 0; $x < $len; $x++) {
            $result['data'][$x] = $this->kavlingModel
                ->select('
                    kavling.id_kavling, 
                    kavling.harga_akhir,
                    kavling.no_kavling,
                    jalan.nama_jalan, 
                    tipe.id_tipe,
                    tipe.tipe_rumah,
                    tgl_harga,
                    row,
                    hargajual.lb as hj_lb,
                    hargajual.lt as hj_lt,
                    hargajual,
                    hargajual_net,
                    ppn,
                    kpr,
                    uang_muka,
                    bphtb,
                    biaya_adm,
                    biaya_proses,
                    hargajual.keterangan as ket_hj,
                    fhj.lokasi,
                    fhj.file_name
                    ')
                ->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
                ->join('tipe', 'kavling.id_tipe = tipe.id_tipe')
                ->join('hargajual', 'hargajual.id = kavling.harga_akhir', "left")
                ->join('file_hargajual fhj', 'hargajual.id_filehj = fhj.id_filehj', "left")
                // ->join('config_shape', 'config_shape.id_config = kavling.'.$conf)
                // ->join('config_shape', 'config_shape.id_config = kavling.id_config')
                ->where('kavling.id_kavling', $id[$x])
                ->first();
        }


        return $this->response->setJSON($result);
    }
    function get_turun_pembangunan()
    {
        $result['token'] = csrf_hash();

        $id = $this->request->getVar('id_kavling');

        $q = $this->kavlingModel
            ->select('
                    kavling.id_kavling, 
                    kavling.id_jalan, 
                    kavling.no_kavling, 
                    proyek.id_proyek, 
                    cluster.id_cluster,
                    kavling.perintah_bangun,
                    kavling.perintah_bangun_tgl,
                    kavling.perintah_bangun_oleh,
                    kavling.perintah_bangun_file,

                    jalan.nama_jalan, 
                    cluster.id_cluster, 
                    cluster.nama_cluster,
                    tipe.id_tipe,
                    tipe.tipe_rumah,
                    tipe.no_tipe_rumah,
                    username
                    

                    ')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster')
            ->join('proyek', 'proyek.id_proyek = cluster.id_proyek')
            ->join('tipe', 'kavling.id_tipe = tipe.id_tipe')
            ->join('users', 'users.id = kavling.perintah_bangun_oleh', 'left')
            ->whereIn('kavling.id_kavling', $id)
            ->findAll();
        if ($q) {
            $result['data'] = $q;
            $result['success'] = true;
        } else {
            $result['data'] = [];
            $result['success'] = false;
        }

        return $this->response->setJSON($result);
    }
    function set_turun_pembangunan()
    {
        $r['token'] = csrf_hash();

        $id_kavling = $this->request->getVar('id_kavling');
        $id_kavling = explode(';', $id_kavling);



        if ($this->request->getFile('perintah_bangun_file')->getSize() > 0) {
            $img = $this->request->getFile('perintah_bangun_file');
            $name = $img->getRandomName();
            $lok = 'uploads/perintah_bangun_file/' . date('Ymd') . '/';
            $img->move($lok, $name);

            $f['perintah_bangun_file'] = $lok . $name;
        }
        $f['perintah_bangun'] = 1;
        $f['perintah_bangun_oleh'] = user_id();
        $f['perintah_bangun_tgl'] = $this->request->getVar('perintah_bangun_tgl');

        foreach ($id_kavling as $id) {
            if ($this->kavlingModel->update($id, $f)) {
                $r['success'] = true;
                $r['messages'] = 'Berhasil melakukan perubahan data';

                $notif = 'Turun pembanguanan untuk kavling: ' . $this->request->getVar('tp-kavling') . ' pada tanggal: ' . date_format(date_create($f['perintah_bangun_tgl']), "d-M-Y") . '';
                $this->notif->tambah_notif("4;9", $notif, user_id(), $id, null); //4 mkdt 9 direksi
            } else {
                $r['success'] = false;
                $r['messages'] = 'Gagal melakukan perubahan data';

            }
        }

        return $this->response->setJSON($r);

    }
    function get_kavling_by_id()
    {
        $result['token'] = csrf_hash();
        $result['data'] = $this->kavlingModel
            ->select('
                    kavling.*, 
                    jalan.nama_jalan, 
                    cluster.id_cluster, 
                    cluster.nama_cluster,
                    tipe.id_tipe,
                    tipe.tipe_rumah,
                    tipe.no_tipe_rumah,
                    tipe.id_gambar_kerja')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster')
            ->join('proyek', 'proyek.id_proyek = cluster.id_proyek')
            ->join('tipe', 'kavling.id_tipe = tipe.id_tipe')
            // ->join('config_shape', 'config_shape.id_config = kavling.'.$conf)
            // ->join('config_shape', 'config_shape.id_config = kavling.id_config')
            ->where('kavling.id_kavling', $this->request->getVar('id_kavling'))
            ->first();

        return $this->response->setJSON($result);
    }
    function get_detail()
    {
        $d['token'] = csrf_hash();
        $id_mkdt = $this->request->getVar('id_mkdt');

        $d['mkdt'] = $this->mkdtModel
            ->select('
                mkdt.*,
                konsumen.nama_konsumen,
                konsumen.nik as nik_konsumen,
                konsumen.hp_konsumen,
                konsumen.alamat_konsumen,
                konsumen.status_konsumen,
                konsumen.no_spptb,
                konsumen.npwp,
                konsumen.sales,
                username
            ')
            ->join('konsumen', 'konsumen.id_konsumen = mkdt.id_konsumen')
            ->join('users', 'users.id = mkdt.edit_by')
            ->where('id_mkdt', $id_mkdt)
            ->first();

        $id_hargajual = $this->request->getVar('id_hargajual');
        $d['pricelist'] = null;
        if ($id_hargajual) {
            $d['pricelist'] = $this->db->table('hargajual')
                ->where('id', $id_hargajual)->get()->getResult()[0];
        }


        //get tagihan
        $tg = $this->db->table('keuangan')
            ->select('*')
            ->where('id_mkdt', $id_mkdt)
            ->get()->getResult();
        $tg_um = 0;
        $tg_um_ll = 0;
        $tg_bb = 0;
        foreach ($tg as $v) {
            switch ($v->status) {
                case 'UM':
                    if ($v->berita_acara == 'Uang Muka')
                        $tg_um += $v->nominal;
                    else
                        $tg_um_ll += $v->nominal;
                    break;
                case 'BB':
                    $tg_bb += $v->nominal;
                    break;
            }
        }
        $d['total_um'] = $tg_um;
        $d['total_um_ll'] = $tg_um_ll;
        $d['total_bb'] = $tg_bb;

        //get sudah bayar
        $sb = $this->db->table('log_pembayaran')
            ->select('log_pembayaran.nominal,  log_pembayaran.payment_type')
            ->where('log_pembayaran.id_mkdt', $id_mkdt)
            ->get()->getResult();


        $sb_um = 0;
        $sb_um_ll = 0;
        $sb_bb = 0;
        foreach ($sb as $v) {
            if ($v->payment_type != 'Booking') {
                $pt = explode(';', $v->payment_type);
                if (in_array('Uang Muka', $pt))
                    $sb_um += $v->nominal;
                elseif (in_array('BPHTB', $pt) || in_array('PPN', $pt) || in_array('BPHTB', $pt) || in_array('Biaya Proses', $pt))
                    $sb_bb += $v->nominal;
                else
                    $sb_um_ll += $v->nominal;
            }
        }
        $sisa = $sb_um > $tg_um ? $sb_um - $tg_um : 0;
        $sb_um_ll = $sisa > 0 ? $sb_um_ll + $sisa : $sb_um_ll;
        $sb_um = $sisa > 0 ? $tg_um : $sb_um;

        $d['sb_um'] = $sb_um;
        $d['sb_um_ll'] = $sb_um_ll;
        $d['sb_bb'] = $sb_bb;

        $ku = $this->db->table('log_pembayaran')
            ->select('users.username, log_pembayaran.created_at')
            ->join('users', 'users.id = log_pembayaran.add_by')
            ->where('id_mkdt', $id_mkdt)
            ->orderBy('log_pembayaran.created_at', 'desc')
            ->get()->getResult();

        $d['ku'] = (count($ku) > 0) ? $ku[0] : null;

        $d['legal'] = $this->legalModel
            ->select("legal.*, username")
            ->where('id_legal', $this->request->getVar('id_legal'))
            ->join('users', 'users.id = legal.edit_by')
            ->first();

        $d['produksi'] = $this->produksiModel
            ->select("produksi.*, username")
            ->where('id_produksi', $this->request->getVar('id_produksi'))
            ->join('users', 'users.id = produksi.edit_by')
            ->first();

        $id_kavling = $this->request->getVar('id_kavling');
        $files = [];
        if ($id_kavling) {
            $files = $this->db->table('file_produksi')
                ->select('file_produksi.*, username')
                ->join('users', 'file_produksi.upload_by = users.id')
                ->where('id_kavling', $id_kavling)
                ->get()->getResult();
        }
        $d['files'] = $files;

        $d['status'] = true;

        return $this->response->setJSON($d);
    }
}

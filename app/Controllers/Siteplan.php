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
use App\Repositories\KavlingRepository;
use CodeIgniter\HTTP\Response;
use App\Controllers\Notif;
use App\Controllers\Home;
use App\Repositories\KeuanganRepository;
use App\Services\FileAccessService;

use App\Repositories\CashOutRepository;

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
    protected $hak_akses;
    protected $kavlingRepo;
    protected $keuRepo;

    protected $cashoutRepo;
    protected $fileAccessService;

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
        $this->keuRepo = new KeuanganRepository();
        $this->cashoutRepo = new CashOutRepository();
        $this->fileAccessService = new FileAccessService();

        $this->kavlingRepo = new KavlingRepository();

        $this->hak_akses = new Home();
    }
    public function index()
    {
        // var_dump(session('token'));die();
        $data['content'] = 'siteplan/pilih_proyek';


        //ambil data proyek
        $data['data']['proyek'] = $this->proyekModel
            ->select("id_proyek, alamat_proyek, nama_proyek, siteplan, logo")
            ->orderBy('order_by', 'asc')
            ->findAll();
        foreach ($data['data']['proyek'] as $proyek) {
            $proyek->siteplan_access_url = $this->fileAccessService->accessUrl('proyek_siteplan', (int) $proyek->id_proyek);
            $proyek->logo_access_url = $this->fileAccessService->accessUrl('proyek_logo', (int) $proyek->id_proyek);
        }

        return view('template', $data);
    }
    public function view_siteplan($a = null)
    {
        $idProyek = $this->normalizeIdProyek($a);
        if ($idProyek === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        session()->set(['id_proyek' => $idProyek]);

        $data = [
            'content' => 'siteplan/master',
            'data' => [
                'profile' => $this->getProfilePerusahaan(),
                'pph'     => $this->db->table('pph')->get()->getResult(),
                'ppn'     => $this->db->table('ppn')->get()->getResult(),
            ],
        ];

        // ambil data proyek
        $data['data']['proyek'] = $this->getProyekOr404($idProyek);
        $data['data']['proyek']->siteplan_access_url = $this->fileAccessService->accessUrl('proyek_siteplan', (int) $data['data']['proyek']->id_proyek);
        $data['data']['proyek']->logo_access_url = $this->fileAccessService->accessUrl('proyek_logo', (int) $data['data']['proyek']->id_proyek);

        // var_dump($data);die();

        if (in_groups(['9', '10', '4', '1'])) {
            $idProyek = (int) $data['data']['proyek']->id_proyek;
            // get data cluster
            $data['data']['cluster'] = $this->clusterModel
                ->select('id_cluster, nama_cluster')
                ->where('cluster.id_proyek', $idProyek)
                ->findAll();

            //get data jalan
            $data['data']['jalan'] = $this->jalanModel
                ->select('id_jalan, nama_jalan, nama_cluster')
                ->join('cluster', 'cluster.id_cluster = jalan.id_cluster')
                ->where('cluster.id_proyek', $idProyek)
                ->findAll();

            //get data tipe
            $data['data']['tipe'] = $this->tipeModel
                ->select('id_tipe, tipe_rumah, harga, no_tipe_rumah')
                ->where('id_proyek', $idProyek)
                ->findAll();
        }

        $data['data']['li_keu'] = json_encode($this->keuRepo->getLIKeu());

        $has_akses = [
            'proyek' => false,
            'legal'  => false,
        ];

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
                ->findAll();

            //mendapatkan hak akses pada proyek
            $user_id = user_id();
            $has_akses['proyek'] = $this->userHasProjectAccess($data['data']['proyek'], (int) $user_id);

            //cek update tanggal pembangunan
            $tanggal_pembangunan_akses = $this->hak_akses->getHak((int) $user_id);
            $has_update_access = array_values(array_filter($tanggal_pembangunan_akses, function ($item) {
                return $item->nama_akses == 'update_tanggal_pembangunan';
            }));

            $has_akses['update_tanggal_pembangunan'] = count($has_update_access) > 0;
        } else if (in_groups(['5'])) {
            //mendapatkan hak akses pada proyek
            $user_id = user_id();
            $has_akses['legal'] = $this->userHasProjectAccess($data['data']['proyek'], (int) $user_id);
        }

        $data['data']['has_akses'] = $has_akses;

        // var_dump($has_akses);die();


        // get config color shape
        $data['data']['conf'] = json_encode($this->getConfigShapeMap());

        // var_dump($data['data']['conf']);die();
        return view('template', $data);
    }

    private function normalizeIdProyek($raw): ?int
    {
        if ($raw === null || $raw === '') {
            return null;
        }

        if (is_int($raw)) {
            return $raw > 0 ? $raw : null;
        }

        if (!is_string($raw) && !is_numeric($raw)) {
            return null;
        }

        $id = (int) $raw;
        return $id > 0 ? $id : null;
    }

    private function getProfilePerusahaan()
    {
        return $this->db->table('profile_perusahaan')->get()->getRow();
    }

    private function getProyekOr404(int $idProyek)
    {
        $proyek = $this->proyekModel
            ->select('*')
            ->where('id_proyek', $idProyek)
            ->first();

        if (!$proyek) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return $proyek;
    }

    private function userHasProjectAccess($proyek, int $userId): bool
    {
        if (!$proyek || !isset($proyek->id_users) || $proyek->id_users === null || $proyek->id_users === '') {
            return false;
        }

        $allowedUserIds = array_filter(array_map('trim', explode(',', (string) $proyek->id_users)));
        return in_array((string) $userId, $allowedUserIds, true);
    }

    private function getConfigShapeMap(): array
    {
        $rows = $this->db->table('config_shape')->get()->getResult();
        $map = [];

        foreach ($rows as $row) {
            if (!isset($row->config_name)) {
                continue;
            }

            $map[$row->config_name] = [
                'fill'        => $row->fill ?? null,
                'stroke'      => $row->stroke ?? null,
                'strokeWidth' => $row->strokeWidth ?? null,
                'dashed'      => $row->dashed ?? null,
                'keterangan'  => $row->keterangan ?? null,
            ];
        }

        return $map;
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
        $notif = 'Menambahkan data kavling ke siteplan: 
                Jalan ' . $this->request->getVar('nama_jalan') . ' 
                No. 
                dengan tipe rumah ' . $this->request->getVar('tp-kavling') . ' 
                pada tanggal: ' . date_format(date_create(date('Y-m-d')), "d-M-Y") . '';

        $this->notif->tambah_notif("0", $notif, user_id(), null, null); //4 mkdt 9 direksi

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
    function getAllKavling()
    {
        $result['token'] = csrf_hash();
        $result['config'] = [];

        $data = $this->kavlingRepo->getAll(
            $this->request->getVar('id_proyek'),
            $this->request->getVar('id_cluster'),
            $this->request->getVar('id_jalan'),
            $this->request->getVar('id_role')
        );

        $result['data'] = $data;
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
                    hargajual.id_filehj,
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
            if ($result['data'][$x] && !empty($result['data'][$x]->id_filehj)) {
                $result['data'][$x]->access_url = $this->fileAccessService->accessUrl('file_hargajual', (int) $result['data'][$x]->id_filehj);
            }
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
            $this->fileAccessService->storeAs($img, $lok, $name);

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
        $id_kavling = $this->request->getVar('id_kavling');

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
                konsumen.file_npwp,
                konsumen.file_ktp,
                konsumen.file_data_diri,
                konsumen.email_konsumen,
                username
            ')
            ->join('konsumen', 'konsumen.id_konsumen = mkdt.id_konsumen')
            ->join('users', 'users.id = mkdt.edit_by')
            ->where('id_mkdt', $id_mkdt)
            ->first();

        $d['kavling'] = $this->kavlingModel
            ->select('
                perintah_bangun,
                perintah_bangun_tgl,
                perintah_bangun_file,
                username,
                kavling.id_tipe,
                pajak.pph42_id_billing,
                pajak.pph42_ntpn,
                pajak.pph42_nilai,
                pajak.pph42_tgl_bayar
            ')
            ->join('users', 'users.id = kavling.perintah_bangun_oleh', 'left')
            ->join('pajak', 'pajak.id_mkdt = kavling.id_mkdt', 'left')
            ->where('id_kavling', $id_kavling)
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


        $d['query'] = (string) $this->db->getLastQuery();
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
            ->select("legal.*, a.username as uadd_by, ,b.username as uedit_by")
            ->where('id_legal', $this->request->getVar('id_legal'))
            ->join('users a', 'a.id = legal.add_by', 'left')
            ->join('users b', 'b.id = legal.edit_by', 'left')
            ->first();

        $d['produksi'] = $this->produksiModel
            ->select('
                produksi.*, 
                a.username as tanggal_pembangunan_oleh_u,
                b.username as tanggal_pembangunan_diubah_oleh_u,
                c.username as tanggal_selesai_pembangunan_diubah_oleh_u,
                sumurbor,
                sumurbor_tanggal,
                sumurbor_keterangan,
                d.username as sumurbor_oleh_u
                ')
            ->where('id_kavling', $id_kavling)
            ->join('users as a', 'a.id = produksi.tanggal_pembangunan_oleh', 'left')
            ->join('users as b', 'b.id = produksi.tanggal_pembangunan_diubah_oleh', 'left')
            ->join('users as c', 'c.id = produksi.tanggal_selesai_pembangunan_diubah_oleh', 'left')
            ->join('kavling', 'kavling.id_produksi = produksi.id_produksi', 'left')
            ->join('users as d', 'd.id = kavling.sumurbor_oleh', 'left')
            ->first();


        $files = [];
        if ($id_kavling) {
            $files = $this->db->table('file_produksi')
                ->select('file_produksi.*, username')
                ->join('users', 'file_produksi.upload_by = users.id')
                ->where('id_kavling', $id_kavling)
                ->get()->getResult();
            $files = $this->fileAccessService->addAccessUrlsToRows($files, 'file_produksi');
        }
        $d['files'] = $files;

        $gambar = $this->db->table('gambar_kerja')
            ->select('id_gambar_tipe, id_gambar_denah')
            ->where('id_tipe', $d['kavling']->id_tipe);



        //get pph ppn bukti bayar
        $q = '
    		(SELECT 
    			file_upload.*,
    			c.username as uupload_by
    			FROM `file_upload`
    			left join users  as c on file_upload.upload_by = c.id
    			WHERE `id_kavling` = ' . $id_kavling . '
    			AND `id_group` = 10
    			AND `kategori` = 9
    			ORDER BY `upload_at` DESC
    			LIMIT 1 )
    		UNION ALL
    		( SELECT 
    			file_upload.*,
    			c.username as uupload_by
    			FROM `file_upload`
    			left join users  as c on file_upload.upload_by = c.id
    			WHERE `id_kavling` = ' . $id_kavling . '
    			AND `id_group` = 10
    			AND `kategori` = 10
    			ORDER BY `upload_at` DESC
    			LIMIT 1)
    		';

        // Query pertama untuk kategori 9

        // Menggabungkan kedua query dengan UNION ALL
        $query = $this->db->query($q);

        // Menjalankan query dan mendapatkan hasil
        $d['file_pph'] = $this->fileAccessService->addAccessUrlsToRows($query->getResult(), 'file_upload');


        //load file ppn
        $q = '
    		(SELECT 
    			file_upload.*,
    			c.username as uupload_by
    			FROM `file_upload`
    			left join users  as c on file_upload.upload_by = c.id

    			WHERE `id_kavling` = ' . $id_kavling . '
    			AND `id_group` = 10
    			AND `kategori` = 11
    			ORDER BY `upload_at` DESC
    			LIMIT 1 )
    		UNION ALL
    		( SELECT 
    			file_upload.*,
    			c.username as uupload_by
    			FROM `file_upload`
    			left join users  as c on file_upload.upload_by = c.id
    			WHERE `id_kavling` = ' . $id_kavling . '
    			AND `id_group` = 10
    			AND `kategori` = 12
    			ORDER BY `upload_at` DESC
    			LIMIT 1)
    			UNION ALL
    		( SELECT 
    			file_upload.*,
    			c.username as uupload_by
    			FROM `file_upload`
    			left join users  as c on file_upload.upload_by = c.id
    			WHERE `id_kavling` = ' . $id_kavling . '
    			AND `id_group` = 10
    			AND `kategori` = 13
    			ORDER BY `upload_at` DESC
    			LIMIT 1)
    		';

        // Query pertama untuk kategori 9

        // Menggabungkan kedua query dengan UNION ALL
        $query = $this->db->query($q);

        // Menjalankan query dan mendapatkan hasil
        $d['file_ppn'] = $this->fileAccessService->addAccessUrlsToRows($query->getResult(), 'file_upload');


        //get cashout riwayat bayar
        $d['cashout'] = $this->cashoutRepo->getRiwayatBayarCashOutByIDKavling($id_kavling);

        $d['bayar_produksi'] = $this->db->table('list_bayar_produksi lc')
            ->select('lc.id as id_bayar_produksi, lc.item, lc.sort, c.*, u.username as add_by_u, e.username as edit_by_u')
            ->join('bayar_produksi c', 'c.id_item_produksi = lc.id and id_kavling = ' . $this->db->escape($id_kavling), 'left')
            ->join('users u', 'u.id = c.add_by', 'left')
            ->join('users e', 'e.id = c.edit_by', 'left')
            ->get()->getResult();

        $d['si'] = $this->db->table('list_si')
            ->select('si.*, list_si.nama')
            ->join('si', 'list_si.id = si.id_list_si and si.id_kavling = ' . $id_kavling, 'left')
            ->get()->getResult();

        $d['status'] = true;

        return $this->response->setJSON($d);
    }


    // ini adalah fungsi untuk refaktor fungsi get_detail() karna terlalu berat
    // function get_detail()
    // {
    //     $d['token'] = csrf_hash();
    //     $id_mkdt = $this->request->getVar('id_mkdt');
    //     $id_kavling = $this->request->getVar('id_kavling');
    //     $id_hargajual = $this->request->getVar('id_hargajual');
    //     $id_legal = $this->request->getVar('id_legal');

    //     // Single comprehensive query to get main data
    //     $mainQuery = $this->db->table('mkdt')
    //         ->select('
    //         mkdt.*,
    //         konsumen.nama_konsumen,
    //         konsumen.nik as nik_konsumen,
    //         konsumen.hp_konsumen,
    //         konsumen.alamat_konsumen,
    //         konsumen.status_konsumen,
    //         konsumen.no_spptb,
    //         konsumen.npwp,
    //         konsumen.sales,
    //         konsumen.file_npwp,
    //         konsumen.file_ktp,
    //         konsumen.file_data_diri,
    //         users.username,

    //         kavling.perintah_bangun,
    //         kavling.perintah_bangun_tgl,
    //         kavling.perintah_bangun_file,
    //         kavling.id_tipe,
    //         kavling_users.username as perintah_bangun_username,

    //         pajak.pph42_id_billing,
    //         pajak.pph42_ntpn,
    //         pajak.pph42_nilai,
    //         pajak.pph42_tgl_bayar,

    //         produksi.id_produksi,
    //         produksi.tanggal_pembangunan_oleh,
    //         produksi.tanggal_pembangunan_diubah_oleh,
    //         produksi.tanggal_selesai_pembangunan_diubah_oleh,
    //         produksi_user_a.username as tanggal_pembangunan_oleh_u,
    //         produksi_user_b.username as tanggal_pembangunan_diubah_oleh_u,
    //         produksi_user_c.username as tanggal_selesai_pembangunan_diubah_oleh_u,

    //         kavling.sumurbor,
    //         kavling.sumurbor_tanggal,
    //         kavling.sumurbor_keterangan,
    //         sumurbor_user.username as sumurbor_oleh_u
    //     ')
    //         ->join('konsumen', 'konsumen.id_konsumen = mkdt.id_konsumen')
    //         ->join('users', 'users.id = mkdt.edit_by')
    //         ->join('kavling', 'kavling.id_mkdt = mkdt.id_mkdt', 'left')
    //         ->join('users as kavling_users', 'kavling_users.id = kavling.perintah_bangun_oleh', 'left')
    //         ->join('pajak', 'pajak.id_mkdt = mkdt.id_mkdt', 'left')
    //         ->join('produksi', 'produksi.id_produksi = kavling.id_produksi', 'left')
    //         ->join('users as produksi_user_a', 'produksi_user_a.id = produksi.tanggal_pembangunan_oleh', 'left')
    //         ->join('users as produksi_user_b', 'produksi_user_b.id = produksi.tanggal_pembangunan_diubah_oleh', 'left')
    //         ->join('users as produksi_user_c', 'produksi_user_c.id = produksi.tanggal_selesai_pembangunan_diubah_oleh', 'left')
    //         ->join('users as sumurbor_user', 'sumurbor_user.id = kavling.sumurbor_oleh', 'left')
    //         ->where('mkdt.id_mkdt', $id_mkdt);

    //     if ($id_kavling) {
    //         $mainQuery->where('kavling.id_kavling', $id_kavling);
    //     }

    //     $mainData = $mainQuery->get()->getRow();

    //     if (!$mainData) {
    //         return $this->response->setJSON(['status' => false, 'message' => 'Data not found']);
    //     }

    //     // Separate mkdt and kavling data
    //     $d['mkdt'] = (object)[
    //         'id_mkdt' => $mainData->id_mkdt,
    //         'id_konsumen' => $mainData->id_konsumen,
    //         'nama_konsumen' => $mainData->nama_konsumen,
    //         'nik_konsumen' => $mainData->nik_konsumen,
    //         'hp_konsumen' => $mainData->hp_konsumen,
    //         'alamat_konsumen' => $mainData->alamat_konsumen,
    //         'status_konsumen' => $mainData->status_konsumen,
    //         'no_spptb' => $mainData->no_spptb,
    //         'npwp' => $mainData->npwp,
    //         'sales' => $mainData->sales,
    //         'file_npwp' => $mainData->file_npwp,
    //         'file_ktp' => $mainData->file_ktp,
    //         'file_data_diri' => $mainData->file_data_diri,
    //         'username' => $mainData->username
    //     ];

    //     $d['kavling'] = (object)[
    //         'perintah_bangun' => $mainData->perintah_bangun,
    //         'perintah_bangun_tgl' => $mainData->perintah_bangun_tgl,
    //         'perintah_bangun_file' => $mainData->perintah_bangun_file,
    //         'username' => $mainData->perintah_bangun_username,
    //         'id_tipe' => $mainData->id_tipe,
    //         'pph42_id_billing' => $mainData->pph42_id_billing,
    //         'pph42_ntpn' => $mainData->pph42_ntpn,
    //         'pph42_nilai' => $mainData->pph42_nilai,
    //         'pph42_tgl_bayar' => $mainData->pph42_tgl_bayar
    //     ];

    //     $d['produksi'] = (object)[
    //         'id_produksi' => $mainData->id_produksi,
    //         'tanggal_pembangunan_oleh_u' => $mainData->tanggal_pembangunan_oleh_u,
    //         'tanggal_pembangunan_diubah_oleh_u' => $mainData->tanggal_pembangunan_diubah_oleh_u,
    //         'tanggal_selesai_pembangunan_diubah_oleh_u' => $mainData->tanggal_selesai_pembangunan_diubah_oleh_u,
    //         'sumurbor' => $mainData->sumurbor,
    //         'sumurbor_tanggal' => $mainData->sumurbor_tanggal,
    //         'sumurbor_keterangan' => $mainData->sumurbor_keterangan,
    //         'sumurbor_oleh_u' => $mainData->sumurbor_oleh_u
    //     ];

    //     // Get pricelist if needed
    //     $d['pricelist'] = null;
    //     if ($id_hargajual) {
    //         $d['pricelist'] = $this->db->table('hargajual')
    //             ->where('id', $id_hargajual)
    //             ->get()
    //             ->getRow();
    //     }

    //     // Get legal data if needed
    //     $d['legal'] = null;
    //     if ($id_legal) {
    //         $d['legal'] = $this->db->table('legal')
    //             ->select("legal.*, a.username as uadd_by, b.username as uedit_by")
    //             ->join('users a', 'a.id = legal.add_by', 'left')
    //             ->join('users b', 'b.id = legal.edit_by', 'left')
    //             ->where('id_legal', $id_legal)
    //             ->get()
    //             ->getRow();
    //     }

    //     // Batch financial calculations
    //     $financialData = $this->getFinancialSummary($id_mkdt);
    //     $d = array_merge($d, $financialData);

    //     // Get last payment user info
    //     $d['ku'] = $this->db->table('log_pembayaran')
    //         ->select('users.username, log_pembayaran.created_at')
    //         ->join('users', 'users.id = log_pembayaran.add_by')
    //         ->where('id_mkdt', $id_mkdt)
    //         ->orderBy('log_pembayaran.created_at', 'desc')
    //         ->limit(1)
    //         ->get()
    //         ->getRow();

    //     // Get files if kavling exists
    //     $d['files'] = [];
    //     if ($id_kavling) {
    //         $d['files'] = $this->db->table('file_produksi')
    //             ->select('file_produksi.*, username')
    //             ->join('users', 'file_produksi.upload_by = users.id')
    //             ->where('id_kavling', $id_kavling)
    //             ->get()
    //             ->getResult();

    //         // Get file uploads with optimized query
    //         $d['file_pph'] = $this->getLatestFilesByCategories($id_kavling, [9, 10]);
    //         $d['file_ppn'] = $this->getLatestFilesByCategories($id_kavling, [11, 12, 13]);

    //         // Get cashout and bayar_produksi data
    //         $d['cashout'] = $this->db->table('list_cashout lc')
    //             ->select('lc.id as id_cashout, lc.item, lc.sort, c.*, u.username as add_by_u, e.username as edit_by_u')
    //             ->join('cashout c', 'c.id_item_cashout = lc.id AND c.id_kavling = ' . $this->db->escape($id_kavling), 'left')
    //             ->join('users u', 'u.id = c.add_by', 'left')
    //             ->join('users e', 'e.id = c.edit_by', 'left')
    //             ->get()
    //             ->getResult();

    //         $d['bayar_produksi'] = $this->db->table('list_bayar_produksi lc')
    //             ->select('lc.id as id_bayar_produksi, lc.item, lc.sort, c.*, u.username as add_by_u, e.username as edit_by_u')
    //             ->join('bayar_produksi c', 'c.id_item_produksi = lc.id AND c.id_kavling = ' . $this->db->escape($id_kavling), 'left')
    //             ->join('users u', 'u.id = c.add_by', 'left')
    //             ->join('users e', 'e.id = c.edit_by', 'left')
    //             ->get()
    //             ->getResult();
    //     }

    //     $d['status'] = true;
    //     return $this->response->setJSON($d);
    // }

    // /**
    //  * Get financial summary with optimized calculations
    //  */
    // private function getFinancialSummary($id_mkdt)
    // {
    //     // Get tagihan (bills) in single query
    //     $tagihan = $this->db->table('keuangan')
    //         ->select('status, berita_acara, nominal')
    //         ->where('id_mkdt', $id_mkdt)
    //         ->get()
    //         ->getResult();

    //     $tg_um = 0;
    //     $tg_um_ll = 0;
    //     $tg_bb = 0;

    //     foreach ($tagihan as $v) {
    //         switch ($v->status) {
    //             case 'UM':
    //                 if ($v->berita_acara == 'Uang Muka') {
    //                     $tg_um += $v->nominal;
    //                 } else {
    //                     $tg_um_ll += $v->nominal;
    //                 }
    //                 break;
    //             case 'BB':
    //                 $tg_bb += $v->nominal;
    //                 break;
    //         }
    //     }

    //     // Get payments (sudah bayar) in single query
    //     $payments = $this->db->table('log_pembayaran')
    //         ->select('nominal, payment_type')
    //         ->where('id_mkdt', $id_mkdt)
    //         ->get()
    //         ->getResult();

    //     $sb_um = 0;
    //     $sb_um_ll = 0;
    //     $sb_bb = 0;

    //     foreach ($payments as $v) {
    //         if ($v->payment_type != 'Booking') {
    //             $pt = explode(';', $v->payment_type);
    //             if (in_array('Uang Muka', $pt)) {
    //                 $sb_um += $v->nominal;
    //             } elseif (in_array('BPHTB', $pt) || in_array('PPN', $pt) || in_array('Biaya Proses', $pt)) {
    //                 $sb_bb += $v->nominal;
    //             } else {
    //                 $sb_um_ll += $v->nominal;
    //             }
    //         }
    //     }

    //     // Calculate sisa and adjust sb_um_ll
    //     $sisa = $sb_um > $tg_um ? $sb_um - $tg_um : 0;
    //     $sb_um_ll = $sisa > 0 ? $sb_um_ll + $sisa : $sb_um_ll;
    //     $sb_um = $sisa > 0 ? $tg_um : $sb_um;

    //     return [
    //         'total_um' => $tg_um,
    //         'total_um_ll' => $tg_um_ll,
    //         'total_bb' => $tg_bb,
    //         'sb_um' => $sb_um,
    //         'sb_um_ll' => $sb_um_ll,
    //         'sb_bb' => $sb_bb
    //     ];
    // }

    // /**
    //  * Get latest files by categories with single optimized query
    //  */
    // private function getLatestFilesByCategories($id_kavling, $categories)
    // {
    //     $categoryConditions = [];
    //     foreach ($categories as $category) {
    //         $categoryConditions[] = "
    //         (SELECT 
    //             file_upload.*,
    //             users.username as uupload_by
    //         FROM file_upload
    //         LEFT JOIN users ON file_upload.upload_by = users.id
    //         WHERE file_upload.id_kavling = {$id_kavling}
    //             AND file_upload.id_group = 10
    //             AND file_upload.kategori = {$category}
    //         ORDER BY file_upload.upload_at DESC
    //         LIMIT 1)";
    //     }

    //     $query = implode(' UNION ALL ', $categoryConditions);
    //     return $this->db->query($query)->getResult();
    // }
}

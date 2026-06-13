<?php

namespace App\Controllers;

use App\Models\MkdtModel;
use App\Models\KavlingModel;
use App\Models\KonsumenModel;
use App\Models\LogPembayaranModel;
use CodeIgniter\HTTP\Response;
use App\Models\KeuanganModel;
use App\Services\PrintService;
use App\Libraries\Mpdf_lib;
use App\Models\ChecklistSubItemModel;
use App\Models\ProyekModel;

use App\Controllers\Notif;
use App\Services\PosisiKonsumenService;

class PosisiKonsumen extends BaseController
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
    protected $posisiKonsumenService;
    protected $printService;
    protected $proyekModel;


    protected $siModel;

    public function __construct()
    {
        $this->notif = new Notif();
        $this->keuModel = new KeuanganModel();
        $this->mkdtModel = new MkdtModel();
        $this->posisiKonsumenService = new PosisiKonsumenService();
        $this->printService = new PrintService();
        $this->kavlingModel = new KavlingModel();
        $this->konsumenModel = new KonsumenModel();
        $this->validation = \Config\Services::validation();
        $this->lpModel = new LogPembayaranModel();
        $this->proyekModel = new ProyekModel();
        $this->siModel = new ChecklistSubItemModel();

        $this->db = db_connect();
        $this->username = $this->db->table('users')->select('username')->get()->getRow();
    }
    function index($status = null)
    {

        $data = [
            'data' => [
                'pph'     => $this->db->table('pph')->where('deleted_at', null)->get()->getResult(),
                'ppn'     => $this->db->table('ppn')->where('deleted_at', null)->get()->getResult(),
            ],
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
        } else if (in_groups(['5'])) {
            //mendapatkan hak akses pada proyek
            $user_id = user_id();
        }



        $data['content'] = 'kavling/list-kavling';
        if ($status == "akad") {
            $data['content'] = 'kavling/list-kavling-akad';
        } else if ($status == "batal") {
            $data['content'] = 'kavling/list-kavling-batal';
        }
        $data['data']['controller'] = 'PosisiKonsumen';
        $data['data']['title'] = 'Posisi Konsumen ' . $status;
        //ambil data proyek 
        $data['data']['proyek'] = $this->proyekModel->first();
        return view('template', $data);
    }
    function getDataTables($status = null)
    {
        $request = $this->request;
        $datatbel = $this->posisiKonsumenService->getDataTable($request, $status);

        return $datatbel;
    }
    function getDataTablesAkad()
    {
        $request = $this->request;
        $datatbel = $this->getDataTables("Akad");

        return $datatbel;
    }
    function getDataTablesBatal()
    {
        $request = $this->request;
        $datatbel = $this->posisiKonsumenService->getDataTablesBatal($request);

        return $datatbel;
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



    /******************************** export *******************************/
    function exportPoskon($tipe, $status)
    {
        $request = $this->request;
        $id_proyek = $request->getVar('id_proyek');
        $id_cluster = $request->getVar('id_cluster');
        $id_jalan = $request->getVar('id_jalan');

        if ($id_proyek == null) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Tidak ada proyek yang dipilih'
            ]);
        }
        if ($tipe == "pdf") {
            $response  = $this->printService->exportPoskonPdf($id_proyek, $id_cluster, $id_jalan, $status);
        } else {
            $response  = $this->printService->exportPoskonXlsx($id_proyek, $id_cluster, $id_jalan, $status);
        }
        if ($response['status'] == TRUE) {
            $data = [
                'id_proyek' => $id_proyek,
                'status' => $status,
                'path' => $response['path'],
                'filename' => $response['filename'],
                'randomName' => $response['randomName'],
                'tipe_file' => $tipe,
                'export_tgl' => date('Y-m-d H:i:s'),
                'export_by' => user_id(),
            ];
            $this->posisiKonsumenService->insertRiwayatExport($data);
        }
        return $this->response->setJSON($response);
    }
    public function exportPoskonPdf($status)
    {

        $request = $this->request;
        $id_proyek = $request->getVar('id_proyek');
        $id_cluster = $request->getVar('id_cluster');
        $id_jalan = $request->getVar('id_jalan');

        if ($id_proyek == null) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Tidak ada proyek yang dipilih'
            ]);
        }
        $response  = $this->printService->exportPoskonAktifPdf($id_proyek, $id_cluster, $id_jalan);
        if ($response['status'] == TRUE) {
            $data = [
                'id_proyek' => $id_proyek,
                'status' => $status,
                'path' => $response['path'],
                'filename' => $response['filename'],
                'randomName' => $response['randomName'],
                'tipe_file' => 'xlsx',
                'export_tgl' => date('Y-m-d H:i:s'),
                'export_by' => user_id(),
            ];
            $this->posisiKonsumenService->insertRiwayatExport($data);
        }
        return $this->response->setJSON($response);
    }
    public function exportPoskonXlsx($status)
    {

        $request = $this->request;
        $id_proyek = $request->getVar('id_proyek');
        $id_cluster = $request->getVar('id_cluster');
        $id_jalan = $request->getVar('id_jalan');

        if ($id_proyek == null) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Tidak ada proyek yang dipilih'
            ]);
        }
        $response  = $this->printService->exportPoskonAktifXlsx($id_proyek, $id_cluster, $id_jalan);
        if ($response['status'] == TRUE) {
            $data = [
                'id_proyek' => $id_proyek,
                'status' => $status,
                'path' => $response['path'],
                'filename' => $response['filename'],
                'randomName' => $response['randomName'],
                'tipe_file' => 'pdf',
                'export_tgl' => date('Y-m-d H:i:s'),
                'export_by' => user_id(),
            ];
            $this->posisiKonsumenService->insertRiwayatExport($data);
        }
        return $this->response->setJSON($response);
    }
    public function getRiwayatExport($status = "Aktif")
    {
        $request = $this->request;
        $id_proyek = $request->getVar('id_proyek');
        $response = $this->posisiKonsumenService->getRiwayatExport($id_proyek, $status);
        return $this->response->setJSON($response);
    }
}

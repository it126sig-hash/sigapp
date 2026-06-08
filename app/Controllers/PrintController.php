<?php

namespace App\Controllers;

use CodeIgniter\HTTP\Response;
use App\Models\ProfilePerusahaanModel;
use App\Repositories\TransaksiRepository;
use App\Models\ProyekModel;
use App\Models\KeuanganModel;
use App\Repositories\PosisiKonsumenRepository;
use App\Services\PrintService;

use CodeIgniter\HTTP\ResponseInterface;


// use App\Libraries\Pdf;
use App\Libraries\Mpdf_lib;

class PrintController extends BaseController
{
    protected $db;
    protected $notif;
    protected $comproModel;
    protected $mpdf;
    protected $transaksi;
    protected $proyek;
    protected $keuanganModel;
    protected $posisiKonsumen;
    protected PrintService $printService;


    public function __construct()
    {
        $this->notif = new Notif();
        $this->comproModel = new ProfilePerusahaanModel();
        $this->db = db_connect();
        // $this->pdf = new Pdf();
        $this->mpdf = new Mpdf_lib();
        $this->proyek = new ProyekModel();
        $this->transaksi = new TransaksiRepository();
        $this->keuanganModel = new KeuanganModel();
        $this->printService = new PrintService();
    }
    public function printSpptb()
    {
        $idKavling = trim((string) $this->request->getVar('id_kavling'));
        $idMkdt    = trim((string) $this->request->getVar('id_mkdt'));
        $idProyek  = trim((string) $this->request->getVar('id_proyek'));

        if ($idMkdt === 'null' || $idMkdt === '') {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'Data konsumen belum tersimpan (id_mkdt kosong).',
            ]);
        }

        $validation = \Config\Services::validation();
        $rules = [
            'id_kavling' => 'required|is_natural_no_zero',
            'id_mkdt'    => 'required|is_natural_no_zero',
            'id_proyek'  => 'required|is_natural_no_zero',
        ];
        if (!$validation->setRules($rules)->run(['id_kavling' => $idKavling, 'id_mkdt' => $idMkdt, 'id_proyek' => $idProyek])) {
            return $this->response->setStatusCode(422)->setJSON([
                'status'  => 'error',
                'message' => 'Validasi gagal.',
                'errors'  => $validation->getErrors(),
            ]);
        }

        try {
            $this->printService->printSpptb((int) $idKavling, (int) $idMkdt, (int) $idProyek);
            exit();
        } catch (\Throwable $e) {
            log_message('error', 'SPPTB print error: {msg}', ['msg' => $e->getMessage()]);
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }
    public function printKuitansi()
    {
        $id = trim((string) $this->request->getVar('e'));
        $id_mkdt = trim((string) $this->request->getVar('e2'));
        $id_poryek = trim((string) $this->request->getVar('e3'));

        $data['pembayaran'] = $this->db->table("log_pembayaran")
            ->select('*')
            ->where('id_pembayaran', $id)
            ->get()->getResult()[0];

        $data['konsumen'] = $this->db->table('konsumen')
            ->select('konsumen.nama_konsumen')
            ->join('mkdt', 'mkdt.id_konsumen = konsumen.id_konsumen')
            ->where('mkdt.id_mkdt', $id_mkdt)
            ->get()->getResult()[0];

        $data['proyek'] = $this->proyek->find($id_poryek);

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
}

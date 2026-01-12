<?php

namespace App\Controllers;

use CodeIgniter\HTTP\Response;
use App\Models\ProfilePerusahaanModel;
use App\Repositories\TransaksiRepository;
use App\Models\ProyekModel;
use App\Models\KeuanganModel;
use App\Repositories\PosisiKonsumenRepository;

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
    }
    public function printSpptb()
    {
        // 1) Normalisasi & validasi input
        $idKavling = trim((string) $this->request->getVar('id_kavling'));
        $idMkdt = trim((string) $this->request->getVar('id_mkdt'));
        $idProyek = trim((string) $this->request->getVar('id_proyek'));

        // Frontend kadang kirim string "null" → normalisasi jadi null
        if ($idMkdt === 'null' || $idMkdt === '') {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => 'Data konsumen belum tersimpan (id_mkdt kosong).'
            ]);
        }

        $validation = \Config\Services::validation();
        $rules = [
            'id_kavling' => 'required|is_natural_no_zero',
            'id_mkdt' => 'required|is_natural_no_zero',
            'id_proyek' => 'required|is_natural_no_zero',
        ];
        $payload = [
            'id_kavling' => $idKavling,
            'id_mkdt' => $idMkdt,
            'id_proyek' => $idProyek,
        ];
        if (!$validation->setRules($rules)->run($payload)) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => 'error',
                'message' => 'Validasi gagal.',
                'errors' => $validation->getErrors(),
            ]);
        }

        $data['proyek'] = $this->proyek->find($idProyek);
        $data['data'] = $this->transaksi->getKonsumenByIdKavling($idKavling);
        $data['list_tagihan'] = $this->keuanganModel
            ->where('id_mkdt', $this->request->getVar('id_mkdt'))
            ->orderBy('jatuh_tempo_tgl')
            ->find();


        try {

            $html[] = view('pdf/spptb-new', $data);
            $html[] = view('pdf/spptb-new-page2', $data);
            $html[] = view('pdf/spptb-new-page3', $data);

            if ($data['data']->is_allin) {
                $html[] = view('pdf/spptb-memo', $data);
            }

            $filename = 'SPPTB - ' . $data['data']->nama_konsumen . ' - ' . date('Ymd') . '.pdf';
            $header = '';
            $mg = [15, 15, 10, 25];

            $ktp = $data['data']->file_ktp ? base_url($data['data']->file_ktp) : "";
            $npwp = $data['data']->file_npwp ? base_url($data['data']->file_npwp) : "";

            // $mg = [$kop->pml, $kop->pmr, $kop->pmt, $kop->pmb];
            if ($ktp != "" || $npwp != "") {
                $footer = "
                <div style='text-align:center;'>
                    <img src='$ktp' width='85mm' height='54mm'>
                    <img src='$npwp' width='85mm' height='54mm'>
                </div>
                ";
            }

            $this->mpdf->generate($html, $filename, $header, $mg, 'F4', true, $footer);
            exit();
        } catch (\Throwable $e) {
            log_message('error', 'SPPTB print error: {msg}', ['msg' => $e->getMessage()]);
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menghasilkan PDF.',
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

<?php
// ADEL CODEIGNITER 4 CRUD GENERATOR

namespace App\Controllers\Keuangan\Cashout;

use App\Controllers\BaseController;
// use App\Repositories\Keuangan\Cashout\CashoutRepo;
use App\Repositories\Keuangan\Cashout\CashoutSubkonRepo;
use App\Services\Keuangan\Cashout\CashoutSubkonService;
use CodeIgniter\API\ResponseTrait;

class CashoutSubkon extends BaseController
{
    protected $cashoutRepo;
    protected $cashoutSubkonRepo;
    protected $cashoutSubkonService;

    public function __construct()
    {
        // $this->cashoutRepo = new CashoutRepo();
        $this->cashoutSubkonRepo = new CashoutSubkonRepo();
        $this->cashoutSubkonService = new CashoutSubkonService();
    }
    public function index()
    {
        return view('cashout_subkon/index');
    }
    public function get()
    {
        $id_kavlings = $this->request->getVar('id_kavlings'); //ini adalah array id kavling
        if (empty($id_kavlings)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data tidak ditemukan'
            ]);
        }
        $data = $this->cashoutSubkonService->getCashoutSubkon($id_kavlings);
        return $this->response->setJSON($data);
    }
    public function save()
    {
        $data = $this->request->getPost();

        // 1. Validasi Subkon
        if (
            empty($data['nama_subkon']) &&
            empty($data['hp1_subkon']) &&
            empty($data['alamat_subkon'])
        ) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Subkon Harus diisi']);
        }

        // 2. Validasi Kavling
        if (empty($data['id_kavling'])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Kavling Harus diisi']);
        }

        // 3. Validasi Total Nominal
        $total_nominal = str_replace(',', '', $data['total_nominal']);
        if (empty($total_nominal) || $total_nominal == 0) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Total Nominal harus diisi']);
        }

        // 4. Validasi Persentase
        $persentase = $data['persentase'] ?? [];
        $total_percentage = 0;
        foreach ($persentase as $p) {
            $total_percentage += floatval(str_replace(',', '', $p));
        }
        if ($total_percentage != 100) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Total Persentase harus 100%']);
        }

        // 5. Validasi Total Nominal vs Detail
        $nominals = $data['nominal'] ?? [];
        $total_nominal_detail = 0;
        foreach ($nominals as $n) {
            $total_nominal_detail += floatval(str_replace(',', '', $n));
        }

        // Gunakan bccomp atau round untuk perbandingan float jika perlu, tapi == cukup untuk mirroring JS sederhana
        if ($total_nominal_detail != floatval($total_nominal)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Total Nominal harus sesuai dengan total persentase']);
        }

        $data = $this->request;

        $result = $this->cashoutSubkonService->saveCashoutSubkon($data);
        return $this->response->setJSON($result);
    }

    public function turunJatuhTempo()
    {
        $id = $this->request->getPost('id_cashout_subkon_detail');
        $tanggal = $this->request->getPost('tanggal_jatuh_tempo');
        $berita_acara = $this->request->getPost('berita_acara');

        if (empty($id)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'ID Detail tidak ditemukan']);
        }
        if (empty($tanggal)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Tanggal Jatuh Tempo harus diisi']);
        }
        if (empty($berita_acara)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Berita Acara harus diisi']);
        }

        $result = $this->cashoutSubkonService->turunJatuhTempo($id, $tanggal, $berita_acara);
        return $this->response->setJSON($result);
    }

    public function ajukanSPP()
    {
        $id = $this->request->getPost('id_cashout_subkon_detail');
        $spp_no = $this->request->getPost('spp_no');
        $spp_tgl = $this->request->getPost('spp_tgl');

        if (empty($id)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'ID Detail tidak ditemukan']);
        }
        if (empty($spp_no)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No SPP harus diisi']);
        }
        if (empty($spp_tgl)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Tanggal SPP harus diisi']);
        }

        $result = $this->cashoutSubkonService->ajukanSPP($id, $spp_no, $spp_tgl);
        return $this->response->setJSON($result);
    }

    public function ajukanPencairan()
    {
        $id = $this->request->getPost('id_cashout_subkon_detail');
        $pencairan_tgl = $this->request->getPost('pencairan_tgl');

        if (empty($id)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'ID Detail tidak ditemukan']);
        }
        if (empty($pencairan_tgl)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Tanggal Pengajuan Cair harus diisi']);
        }

        $result = $this->cashoutSubkonService->ajukanPencairan($id, $pencairan_tgl);
        return $this->response->setJSON($result);
    }

    public function pembayaran()
    {
        $id = $this->request->getPost('id_cashout_subkon_detail');
        $cek_no = $this->request->getPost('cek_no');
        $cek_tgl = $this->request->getPost('cek_tgl');

        if (empty($id)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'ID Detail tidak ditemukan']);
        }
        if (empty($cek_no)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No Cek harus diisi']);
        }
        if (empty($cek_tgl)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Tanggal Cek harus diisi']);
        }

        $result = $this->cashoutSubkonService->pembayaran($id, $cek_no, $cek_tgl);
        return $this->response->setJSON($result);
    }

    public function getHistory()
    {
        $id_cashout_subkon = $this->request->getPost('id_cashout_subkon');
        if (empty($id_cashout_subkon)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'ID tidak ditemukan']);
        }
        $data = $this->cashoutSubkonRepo->getHistoryByIDCashoutSubkon($id_cashout_subkon);
        return $this->response->setJSON(['status' => 'success', 'data' => $data]);
    }
}

<?php
// ADEL CODEIGNITER 4 CRUD GENERATOR

namespace App\Controllers\Keuangan\Cashout;

use App\Controllers\BaseController;
use App\Services\Keuangan\Cashout\CashoutSubkonService;

class CashoutSubkon extends BaseController
{
    protected $cashoutSubkonService;

    public function __construct()
    {
        $this->cashoutSubkonService = new CashoutSubkonService();
    }

    public function index()
    {
        $data['content'] = 'cashout_subkon/index';
        $data['data']['title'] = 'Cashout Subkon';

        return view('template', $data);
    }

    public function getDataTables()
    {
        return $this->json($this->cashoutSubkonService->getDataTables($this->request->getVar()));
    }

    public function get()
    {
        return $this->json(
            $this->cashoutSubkonService->getCashoutSubkon(
                (array) $this->request->getVar('id_kavlings'),
                (int) $this->request->getVar('id_cashout_subkon') ?: null
            )
        );
    }

    public function save()
    {
        return $this->json($this->cashoutSubkonService->saveCashoutSubkon($this->request));
    }

    public function turunJatuhTempo()
    {
        return $this->json(
            $this->cashoutSubkonService->turunJatuhTempo(
                $this->request->getPost('id_cashout_subkon_detail'),
                $this->request->getPost('tanggal_jatuh_tempo'),
                $this->request->getPost('berita_acara')
            )
        );
    }

    public function ajukanSPP()
    {
        return $this->json(
            $this->cashoutSubkonService->ajukanSPP(
                $this->request->getPost('id_cashout_subkon_detail'),
                $this->request->getPost('spp_no'),
                $this->request->getPost('spp_tgl')
            )
        );
    }

    public function ajukanPencairan()
    {
        return $this->json(
            $this->cashoutSubkonService->ajukanPencairan(
                $this->request->getPost('id_cashout_subkon_detail'),
                $this->request->getPost('pencairan_tgl')
            )
        );
    }

    public function pembayaran()
    {
        return $this->json(
            $this->cashoutSubkonService->pembayaran(
                $this->request->getPost('id_cashout_subkon_detail'),
                $this->request->getPost('cek_no'),
                $this->request->getPost('cek_tgl')
            )
        );
    }

    public function getHistory()
    {
        return $this->json($this->cashoutSubkonService->getHistory($this->request->getPost('id_cashout_subkon')));
    }

    private function json(array $payload)
    {
        $payload['token'] = $payload['token'] ?? csrf_hash();
        return $this->response->setJSON($payload);
    }
}

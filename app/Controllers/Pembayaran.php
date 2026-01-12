<?php

namespace App\Controllers;

use App\Services\PembayaranService;
use App\Repositories\KeuanganRepository;
use App\Services\PrintService;

use Exception;

class Pembayaran extends BaseController
{
    protected $pembayaranService;
    protected $keuRepo;
    protected $print;
    public function __construct()
    {
        $this->pembayaranService = new PembayaranService();
        $this->keuRepo = new KeuanganRepository();
        $this->print = new PrintService();
    }

    function save()
    {
        $li_keu = $this->keuRepo->getLIKeu();

        $total = 0;
        foreach ($li_keu as $key => $value) {
            // $this->request->getPost("nominal-" . $value->id_keuangan_item_list)
            $nominal = $this->request->getPost("nominal-" . $value->id_keuangan_item_list);
            if ($nominal) {
                $total += $this->num($nominal);
            }
        }
        if ($this->num($this->request->getPost("bt-bayar_tagihan_um")) == 0) {
            $response = [
                'status' => false,
                'message' => 'Nominal pembayaran tidak boleh 0!',
            ];
            return $this->response->setJSON($response);
        }
        if ($total != $this->num($this->request->getPost("bt-bayar_tagihan_um"))) {
            $response = [
                'status' => false,
                'message' => 'Alokasi dana harus sama dengan nominal pembayaran!',
            ];
            return $this->response->setJSON($response);
        }
        $data = $this->request;

        $response = $this->pembayaranService->simpan($data);

        return $this->response->setJSON($response);
    }
    function print()
    {
        $data = $this->request;
        $this->print->printKuitansi($data);
    }
    function removeLP()
    {
        $data = $this->request;
        $r = $this->pembayaranService->removeLP($data);
        return $this->response->setJSON($r);
    }
    function printUm()
    {
        $data = $this->request;
        $this->print->printKuitansiUm($data);
    }

    protected function num($d)
    {
        $d = str_replace(',', "", $d);
        return $d;
    }

    function recalculateSummary()
    {
        $db = \Config\Database::connect();
        $id_mkdt = $db->table('log_pembayaran')->select('id_mkdt')->where('is_deleted', 0)->get()->getResultArray();
        foreach ($id_mkdt as $key => $value) {
            $this->pembayaranService->recalculateSummary($value['id_mkdt']);
        }
    }
}

<?php

namespace App\Controllers;

use App\Controllers\Notif;
use App\Models\ProfilePerusahaanModel;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\MkdtModel;
use App\Models\KavlingModel;
use App\Models\KonsumenModel;
use App\Models\KeuanganModel;
use App\Services\TransaksiService;
use App\Services\KonsumenService;
use App\Services\KeuanganService;
use App\Services\StorageService;
use Throwable;

// use App\Libraries\Pdf;
use App\Libraries\Mpdf_lib;

class Transaksi extends BaseController
{
    protected $db;
    protected $comproModel;
    protected $mpdf;

    /** @var \App\Services\KonsumenService */
    protected $konsumenService;

    protected $mkdtService;

    /** @var \App\Services\KeuanganService */
    protected $keuanganService;

    /** @var \App\Services\StorageService */
    protected $storageService;
    protected $notif;

    protected $keuModel;
    protected $mkdtModel;
    protected $kavlingModel;
    protected $konsumenModel;
    public function __construct()
    {
        $this->keuModel = new KeuanganModel();
        $this->mkdtModel = new MkdtModel();
        $this->kavlingModel = new KavlingModel();
        $this->konsumenModel = new KonsumenModel();

        $this->konsumenService = new KonsumenService();
        $this->mkdtService = new TransaksiService();
        $this->keuanganService = new KeuanganService();
        $this->storageService = new StorageService();
        $this->notif = new Notif();
        $this->comproModel = new ProfilePerusahaanModel();
        $this->db = db_connect();
        // $this->pdf = new Pdf();
        $this->mpdf = new Mpdf_lib();
    }

    function getByID()
    {
        // Validasi sederhana (jangan takut “strict”, daripada SQL error/notice)
        $rules = [
            'id_mkdt' => 'permit_empty|is_natural_no_zero',
            'id_hargajual' => 'permit_empty|is_natural_no_zero',
            'id_kavling' => 'permit_empty|is_natural_no_zero',
        ];
        if (!$this->validate($rules)) {
            return $this->response->setStatusCode(400)
                ->setJSON([
                    'error' => 'Invalid parameters',
                    'messages' => $this->validator->getErrors(),
                    'token' => csrf_hash(),
                ]);
        }

        $idMkdt = $this->request->getVar('id_mkdt') ?: null;
        $idHargajual = $this->request->getVar('id_hargajual') ?: null;
        $idKavling = $this->request->getVar('id_kavling') ?: null;



        $resp = $this->mkdtService->getBundle(
            $idMkdt ? (int) $idMkdt : null,
            $idHargajual ? (int) $idHargajual : null,
            $idKavling ? (int) $idKavling : null
        );

        return $this->response->setJSON($resp);
    }

    function getStatusByID()
    {
        $id_hargajual = $this->request->getVar('id_hargajual');
        $id_kavling = $this->request->getVar('id_kavling');
        $id_mkdt = $this->request->getVar('id_mkdt');
        $resp = $this->mkdtService->getStatusById($id_hargajual, $id_kavling, $id_mkdt);

        return $this->response->setJSON($resp);
    }
    public function saveTransaksi(): ResponseInterface
    {
        $resp = ['token' => csrf_hash()];
        $input = $this->request;

        echo "<pre>";
        print_r($input->getVar('id_konsumen'));
        echo "</pre>";
        die;

        $resp = $this->mkdtService->saveTransaksi($input);
        return $this->response->setJSON($resp);
    }
    public function saveStatus(): ResponseInterface
    {
        $resp = ['token' => csrf_hash()];
        $input = $this->request;

        $resp = $this->mkdtService->saveStatus($input);
        return $this->response->setJSON($resp);
    }
    protected function num($d)
    {
        $d = str_replace(',', "", $d);
        return $d;
    }
}

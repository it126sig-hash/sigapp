<?php

namespace App\Controllers;

use App\Controllers\Notif;
use App\Models\ProfilePerusahaanModel;
use App\Services\KeuanganService;
use App\Services\StorageService;
use App\Services\TransaksiService;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use App\Services\KonsumenService;
use App\Repositories\LogPembayaranRepository;
use App\Repositories\KeuanganRepository;
use Throwable;

// use App\Libraries\Pdf;
use App\Libraries\Mpdf_lib;

class DanaAkad extends BaseController
{
    protected $db;
    protected $comproModel;
    protected $mpdf;
    protected $logRepo;
    protected $keuRepo;

    /** @var \App\Services\KonsumenService */
    protected $konsumenService;

    /** @var \App\Services\TransaksiService */
    protected $mkdtService;

    /** @var \App\Services\KeuanganService */
    protected $keuanganService;

    /** @var \App\Services\StorageService */
    protected $storageService;
    protected $notif;
    use ResponseTrait;

    public function __construct()
    {
        $this->konsumenService = new KonsumenService();
        $this->mkdtService      = new TransaksiService();
        $this->keuanganService  = new KeuanganService();
        $this->storageService   = new StorageService();
        $this->notif            = new Notif();
        $this->comproModel = new ProfilePerusahaanModel();
        $this->db = db_connect();
        // $this->pdf = new Pdf();
        $this->mpdf = new Mpdf_lib();

        $this->logRepo = new LogPembayaranRepository();
        $this->keuRepo = new KeuanganRepository();
    }

    function getByID()
    {
        $r['token'] = csrf_hash();
        $id_mkdt = $this->request->getVar('id_mkdt');
        $id_kavling = $this->request->getVar('id_kavling');

        $r['id_mkdt'] = $id_mkdt;
        $r['id_kavling'] = $id_kavling;

        $r['mkdt'] = $this->db->table('mkdt')
            ->select('harga_kpr_acc, dajam_selesai')
            ->where(['id_mkdt' => $id_mkdt])
            ->get()->getResult()[0];

        $r['list_dajam'] = $this->db->table('list_dajam')
            ->select('list_dajam.nama_jaminan, list_dajam.id as id_list_dajam_ori, dana_akad.*')
            ->join('dana_akad', 'dana_akad.id_list_dajam = list_dajam.id and id_kavling = ' . $this->db->escape($id_kavling), 'left')
            ->where('list_dajam.deleted_at', null)
            // ->where('id_kavling', $id_kavling)
            ->get()->getResult();


        $r['list_pengajuan'] = $this->rdajam->where('id_kavling', $id_kavling)->get()->getResult();
        return $this->response->setJSON($r);
    }

    protected function num($d)
    {
        // $d = str_replace('.', "", $d);
        $d = str_replace(',', "", $d);

        return $d;
    }
}

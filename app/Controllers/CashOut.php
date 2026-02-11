<?php

namespace App\Controllers;

// use App\Controllers\Notif;
// use App\Models\ProfilePerusahaanModel;
// use App\Services\KeuanganService;
// use App\Services\StorageService;
// use App\Services\TransaksiService;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use App\Services\KonsumenService;
// use App\Repositories\LogPembayaranRepository;
// use App\Repositories\KeuanganRepository;

use Throwable;
use App\Services\CashOutService;
use App\Repositories\CashOutRepository;


class CashOut extends BaseController
{
    protected $db;
    // protected $comproModel;
    // protected $mpdf;
    // protected $logRepo;
    // protected $keuRepo;

    // /** @var \App\Services\KonsumenService */
    protected $konsumenService;

    // /** @var \App\Services\TransaksiService */
    // protected $mkdtService;

    // /** @var \App\Services\KeuanganService */
    // protected $keuanganService;

    // /** @var \App\Services\StorageService */
    // protected $storageService;
    protected $notif;
    protected $cashoutService;
    protected $cashoutRepo;
    use ResponseTrait;

    public function __construct()
    {
        $this->konsumenService = new KonsumenService();
        // $this->mkdtService      = new TransaksiService();
        // $this->keuanganService  = new KeuanganService();
        // $this->storageService   = new StorageService();
        // $this->notif            = new Notif();
        // $this->comproModel = new ProfilePerusahaanModel();
        $this->db = db_connect();
        // $this->mpdf = new Mpdf_lib();

        // $this->logRepo = new LogPembayaranRepository();
        // $this->keuRepo = new KeuanganRepository();


        $this->cashoutService = new CashOutService();
        $this->cashoutRepo = new CashOutRepository();
    }

    function getListItem()
    {
        $search = trim((string) $this->request->getVar('search'));

        $data['token'] = csrf_hash();

        $data['list_item'] = $this->cashoutRepo->getListItem($search);
        return $this->response->setJSON($data);
    }
    function getByIDKavling()
    {
        $id_kavling = trim((string) $this->request->getVar('id_kavling'));

        $data['token'] = csrf_hash();

        if (empty($id_kavling)) {
            return $this->response->setJSON([
                'token'    => csrf_hash(),
                'success'  => false,
                'messages' => 'data tidak ditemukan',
            ]);
        }

        $data['konsumen'] = $this->konsumenService->getByIDKavling($id_kavling);
        $data['riwayat_bayar'] = $this->cashoutRepo->getRiwayatBayarCashOutByIDKavling($id_kavling);
        return $this->response->setJSON($data);
    }
    function insert()
    {
        $data['token'] = csrf_hash();
        $data['id_kavling'] = $this->request->getVar('id_kavling');
        $data['id_item_cashout'] = $this->request->getVar('co-untuk_pembayaran');
        $data['tanggal_bayar'] = $this->request->getVar('co-tanggal_bayar');
        $data['nominal'] = $this->num($this->request->getVar('co-nominal'));
        $data['keterangan'] = $this->request->getVar('co-keterangan');

        if (empty($data['id_kavling']) || empty($data['id_item_cashout']) || empty($data['tanggal_bayar']) || empty($data['nominal'])) {
            return $this->response->setJSON([
                'token'    => csrf_hash(),
                'success'  => false,
                'messages' => 'data tidak lengkap',
            ]);
        }


        $result = $this->cashoutService->insert($data);

        if ($result) {
            return $this->response->setJSON([
                'token'    => csrf_hash(),
                'id_kavling' => $data['id_kavling'],
                'success'  => true,
                'messages' => 'data berhasil disimpan',
            ]);
        }

        return $this->response->setJSON([
            'token'    => csrf_hash(),
            'success'  => false,
            'messages' => 'data gagal disimpan',
        ]);
    }
    function delete()
    {
        $id = $this->request->getVar('id');

        if (empty($id)) {
            return $this->response->setJSON([
                'token'    => csrf_hash(),
                'success'  => false,
                'messages' => 'data tidak ditemukan',
            ]);
        }

        $result = $this->cashoutService->delete($id);


        if ($result) {
            return $this->response->setJSON([
                'token'    => csrf_hash(),
                'id_kavling' => $result,
                'success'  => true,
                'messages' => 'data berhasil dihapus',
            ]);
        }

        return $this->response->setJSON([
            'token'    => csrf_hash(),
            'success'  => false,
            'messages' => 'data gagal dihapus',
        ]);
    }

    protected function num($d)
    {
        // $d = str_replace('.', "", $d);
        $d = str_replace(',', "", $d);

        return $d;
    }
}

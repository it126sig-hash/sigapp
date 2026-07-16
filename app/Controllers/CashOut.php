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

        $kavling = $this->db->table('kavling')
            ->select('id_mkdt')
            ->where('id_kavling', $id_kavling)
            ->get()
            ->getRow();
        $mkdt = $kavling && !empty($kavling->id_mkdt)
            ? $this->konsumenService->getKonsumenTransaksi((int) $kavling->id_mkdt)
            : null;

        $data['konsumen'] = $mkdt ?: $this->konsumenService->getByIDKavling($id_kavling);
        $data['biaya_mkdt'] = $this->formatBiayaMkdt($mkdt);
        $data['riwayat_bayar'] = $this->cashoutRepo->getRiwayatBayarCashOutByIDKavling($id_kavling);
        return $this->response->setJSON($data);
    }

    protected function konsumenExistsForKavling(string $id_kavling): bool
    {
        $kavling = $this->db->table('kavling')
            ->select('id_mkdt')
            ->where('id_kavling', $id_kavling)
            ->get()
            ->getRow();

        if ($kavling && !empty($kavling->id_mkdt) && $this->konsumenService->getKonsumenTransaksi((int) $kavling->id_mkdt)) {
            return true;
        }

        return (bool) $this->konsumenService->getByIDKavling($id_kavling);
    }

    protected function formatBiayaMkdt($mkdt): array
    {
        $num = static function ($value): float {
            return (float) ($value ?? 0);
        };

        if (!$mkdt) {
            return [];
        }

        $hargaUangMuka = $num($mkdt->harga_uang_muka ?? 0);
        $diskonUangMuka = $num($mkdt->harga_diskon_uang_muka ?? 0);
        $sbum = $num($mkdt->harga_sbum ?? 0);
        $administrasi = $num($mkdt->harga_administrasi ?? 0);
        $bphtb = $num($mkdt->harga_bphtb ?? 0);
        $biayaProses = $num($mkdt->harga_biaya_proses ?? 0);
        $ppn = $num($mkdt->harga_ppn ?? 0);
        $penambahanUm = $num($mkdt->harga_penambahan_um ?? 0);
        $penambahan = $num($mkdt->harga_penambahan ?? 0);
        $penambahanTanah = $num($mkdt->harga_penambahan_tanah ?? 0);

        $totalUm = $hargaUangMuka - $diskonUangMuka - $sbum;
        $totalBiayaLain = $administrasi + $bphtb + $biayaProses + $ppn + $penambahanUm + $penambahan + $penambahanTanah;

        return [
            'harga_jual' => $num($mkdt->harga_jual ?? 0),
            'harga_jual_net' => $num($mkdt->harga_jual_net ?? 0),
            'harga_kpr' => $num($mkdt->harga_kpr ?? 0),
            'harga_kpr_acc' => $num($mkdt->harga_kpr_acc ?? 0),
            'harga_uang_muka' => $hargaUangMuka,
            'harga_diskon_uang_muka' => $diskonUangMuka,
            'harga_sbum' => $sbum,
            'harga_administrasi' => $administrasi,
            'harga_bphtb' => $bphtb,
            'harga_biaya_proses' => $biayaProses,
            'harga_ppn' => $ppn,
            'harga_penambahan_um' => $penambahanUm,
            'harga_penambahan' => $penambahan,
            'harga_penambahan_tanah' => $penambahanTanah,
            'total_um' => $totalUm,
            'total_biaya_lain' => $totalBiayaLain,
            'total_tercatat' => $totalUm + $totalBiayaLain,
        ];
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

        if (!$this->konsumenExistsForKavling($data['id_kavling'])) {
            return $this->response->setJSON([
                'token'    => csrf_hash(),
                'success'  => false,
                'messages' => 'Kavling belum memiliki data konsumen',
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

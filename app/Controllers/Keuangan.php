<?php

namespace App\Controllers;

use App\Controllers\Api\Keuangan\BankKprDisbursementController;
use App\Controllers\Api\Keuangan\DanaJaminanController;
use App\Controllers\Api\Keuangan\PembayaranController;
use App\Controllers\Api\Keuangan\PenagihanController as ApiPenagihanController;
use App\Controllers\Api\Keuangan\TagihanController as ApiTagihanController;
use App\Controllers\Web\Keuangan\PenagihanController as WebPenagihanController;
use App\Controllers\Web\Keuangan\PrintController as WebPrintController;
use App\Controllers\Web\Keuangan\TagihanController as WebTagihanController;
use App\Services\FileAccessService;
use CodeIgniter\Controller as BaseCiController;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * @deprecated Gunakan controller modular di App\Controllers\Web\Keuangan\* dan App\Controllers\Api\Keuangan\*.
 *             Dipertahankan sebagai Zero-Downtime Deprecation Shim (Opsi A).
 */
class Keuangan extends BaseController
{
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $uri = method_exists($request, 'getUri') ? (string) $request->getUri()->getPath() : '';
        log_message('warning', 'DEPRECATED_KEUANGAN_HIT: ' . $uri . ' via legacy ' . static::class);
    }

    protected function initDelegate(BaseCiController $controller): BaseCiController
    {
        $controller->initController($this->request, $this->response, $this->logger);
        return $controller;
    }

    protected function apiPenagihan(): ApiPenagihanController
    {
        /** @var ApiPenagihanController $ctrl */
        $ctrl = $this->initDelegate(new ApiPenagihanController());
        return $ctrl;
    }

    protected function webPenagihan(): WebPenagihanController
    {
        /** @var WebPenagihanController $ctrl */
        $ctrl = $this->initDelegate(new WebPenagihanController());
        return $ctrl;
    }

    protected function apiDanaJaminan(): DanaJaminanController
    {
        /** @var DanaJaminanController $ctrl */
        $ctrl = $this->initDelegate(new DanaJaminanController());
        return $ctrl;
    }

    protected function apiBankKpr(): BankKprDisbursementController
    {
        /** @var BankKprDisbursementController $ctrl */
        $ctrl = $this->initDelegate(new BankKprDisbursementController());
        return $ctrl;
    }

    protected function apiTagihan(): ApiTagihanController
    {
        /** @var ApiTagihanController $ctrl */
        $ctrl = $this->initDelegate(new ApiTagihanController());
        return $ctrl;
    }

    protected function webTagihan(): WebTagihanController
    {
        /** @var WebTagihanController $ctrl */
        $ctrl = $this->initDelegate(new WebTagihanController());
        return $ctrl;
    }

    protected function apiPembayaran(): PembayaranController
    {
        /** @var PembayaranController $ctrl */
        $ctrl = $this->initDelegate(new PembayaranController());
        return $ctrl;
    }

    protected function webPrint(): WebPrintController
    {
        /** @var WebPrintController $ctrl */
        $ctrl = $this->initDelegate(new WebPrintController());
        return $ctrl;
    }

    /** @deprecated Gunakan Api\Keuangan\DanaJaminanController::getDanaAkad() */
    public function getDanaAkad()
    {
        return $this->apiDanaJaminan()->getDanaAkad();
    }

    /** @deprecated Gunakan Api\Keuangan\DanaJaminanController::saveDanaAkad() */
    public function saveDanaAkad()
    {
        return $this->apiDanaJaminan()->saveDanaAkad();
    }

    /** @deprecated Gunakan Api\Keuangan\BankKprDisbursementController::getData() */
    public function getBankKprDisbursement()
    {
        return $this->apiBankKpr()->getData();
    }

    /** @deprecated Gunakan Api\Keuangan\BankKprDisbursementController::save() */
    public function saveBankKprDisbursement()
    {
        return $this->apiBankKpr()->save();
    }

    /** @deprecated Gunakan Api\Keuangan\BankKprDisbursementController::void() */
    public function voidBankKprDisbursement($id = null)
    {
        return $this->apiBankKpr()->void($id);
    }

    /** @deprecated Gunakan Api\Keuangan\BankKprDisbursementController::getJatuhTempo() */
    public function getJatuhTempo()
    {
        return $this->apiBankKpr()->getJatuhTempo();
    }

    /** @deprecated Gunakan Api\Keuangan\PenagihanController::isi_tagihan() */
    public function isi_tagihan()
    {
        return $this->apiPenagihan()->isi_tagihan();
    }

    /** @deprecated Gunakan Api\Keuangan\PenagihanController::save_sb() */
    public function save_sb()
    {
        return $this->apiPenagihan()->save_sb();
    }

    /** @deprecated Gunakan Api\Keuangan\PenagihanController::save() */
    public function save()
    {
        return $this->apiPenagihan()->save();
    }

    /** @deprecated Gunakan Web\Keuangan\TagihanController::listTagihan() */
    public function list_tagihan()
    {
        return $this->webTagihan()->listTagihan();
    }

    /** @deprecated Gunakan Api\Keuangan\TagihanController::getListTagihan() */
    public function getListTagihan()
    {
        return $this->apiTagihan()->getListTagihan();
    }

    /** @deprecated Gunakan Web\Keuangan\TagihanController::riwayatBayarIndex() */
    public function riwayat_bayar()
    {
        return $this->webTagihan()->riwayatBayarIndex();
    }

    /** @deprecated Gunakan Api\Keuangan\TagihanController::getRiwayatBayar() */
    public function getRiwayatBayar()
    {
        return $this->apiTagihan()->getRiwayatBayar();
    }

    /** @deprecated Gunakan Api\Keuangan\PenagihanController::get_tagihan() */
    public function get_tagihan($id = null)
    {
        return $this->apiPenagihan()->get_tagihan($id);
    }

    /** @deprecated Gunakan Api\Keuangan\PembayaranController::removeLP() */
    public function removeLP()
    {
        return $this->apiPembayaran()->removeLP();
    }

    /** @deprecated Gunakan Web\Keuangan\PenagihanController::doPrint() */
    public function doPrint()
    {
        return $this->webPenagihan()->doPrint();
    }

    /** @deprecated Gunakan Web\Keuangan\PrintController::printTagihan() */
    public function print_tagihan($id = null)
    {
        return $this->webPrint()->printTagihan();
    }

    /** @deprecated Gunakan Api\Keuangan\PenagihanController::get_riwayat_tagihan() */
    public function get_riwayat_tagihan()
    {
        return $this->apiPenagihan()->get_riwayat_tagihan();
    }

    /** @deprecated Gunakan Api\Keuangan\PenagihanController::simpan_penagihan() */
    public function simpan_penagihan()
    {
        return $this->apiPenagihan()->simpan_penagihan();
    }

    /** @deprecated Gunakan Api\Keuangan\PenagihanController::update_status_penagihan() */
    public function update_status_penagihan()
    {
        return $this->apiPenagihan()->update_status_penagihan();
    }

    /** @deprecated Gunakan Web\Keuangan\PenagihanController::download_penagihan() */
    public function download_penagihan()
    {
        return $this->webPenagihan()->download_penagihan();
    }

    /** @deprecated Gunakan Api\Keuangan\PenagihanController::get_kopsurat_list() */
    public function get_kopsurat_list()
    {
        return $this->apiPenagihan()->get_kopsurat_list();
    }

    /** @deprecated Gunakan Api\Keuangan\PenagihanController::get_kopsurat_detail() */
    public function get_kopsurat_detail()
    {
        return $this->apiPenagihan()->get_kopsurat_detail();
    }

    /** @deprecated Gunakan Api\Keuangan\PenagihanController::simpan_kopsurat() */
    public function simpan_kopsurat()
    {
        return $this->apiPenagihan()->simpan_kopsurat();
    }

    /** @deprecated Gunakan Api\Keuangan\PenagihanController::hapus_kopsurat() */
    public function hapus_kopsurat()
    {
        return $this->apiPenagihan()->hapus_kopsurat();
    }

    /**
     * @deprecated Orphan legacy method — gunakan Api\Keuangan\CashOutController.
     */
    public function getCashOut()
    {
        log_message('warning', 'DEPRECATED_KEUANGAN_HIT: Keuangan::getCashOut() dipanggil');
        $db = db_connect();
        $r['token'] = csrf_hash();
        $id_kavling = $this->request->getVar('id_kavling');
        $r['id_kavling'] = $id_kavling;

        $r['list_cashout'] = $db->table('list_cashout lc')
            ->select('lc.id as id_cashout, lc.item, lc.sort, c.*, u.username as add_by_u, e.username as edit_by_u')
            ->join('cashout c', 'c.id_item_cashout = lc.id and id_kavling = ' . $db->escape($id_kavling), 'left')
            ->join('users u', 'u.id = c.add_by', 'left')
            ->join('users e', 'e.id = c.edit_by', 'left')
            ->where('lc.deleted_at', null)
            ->orderBy('lc.sort', 'ASC')
            ->orderBy('lc.id', 'ASC')
            ->get()
            ->getResult();

        return $this->response->setJSON($r);
    }

    /**
     * @deprecated Orphan legacy method — gunakan Api\Keuangan\CashOutController.
     */
    public function saveCashOut()
    {
        log_message('warning', 'DEPRECATED_KEUANGAN_HIT: Keuangan::saveCashOut() dipanggil');
        $db = db_connect();
        $response['token'] = csrf_hash();
        $id_cashout = $this->request->getVar('id-cashout');
        $id_kavling = $this->request->getVar('id_kavling');

        if (is_array($id_cashout)) {
            foreach ($id_cashout as $i => $v) {
                $data = ['id_kavling' => $id_kavling];

                if (($v['nominal'] ?? '') !== '' && ($v['tanggal_bayar'] ?? '') !== '') {
                    if (strpos((string) $i, 'n') === false) {
                        $data['nominal'] = str_replace(',', '', (string) $v['nominal']);
                        $data['keterangan'] = $v['keterangan'] ?? '';
                        $data['tanggal_bayar'] = $v['tanggal_bayar'];
                        $data['edit_by'] = user_id();
                        $data['updated_at'] = date('Y-m-d H:i:s');

                        $db->table('cashout')->where(['id' => $i])->update($data);
                    } else {
                        $data['id_item_cashout'] = substr((string) ($v['id_item_cashout'] ?? ''), 1);
                        $data['nominal'] = str_replace(',', '', (string) $v['nominal']);
                        $data['keterangan'] = $v['keterangan'] ?? '';
                        $data['tanggal_bayar'] = $v['tanggal_bayar'];
                        $data['id'] = null;
                        $data['add_by'] = user_id();
                        $data['created_at'] = date('Y-m-d H:i:s');

                        $db->table('cashout')->insert($data);
                    }
                }
            }
        }

        $response['success'] = true;
        $response['messages'] = 'Data berhasil diperbaharui';

        return $this->response->setJSON($response);
    }

    /**
     * @deprecated Orphan legacy method — riwayat ganti nama MKDT.
     */
    public function get_riwayat_gantinama()
    {
        log_message('warning', 'DEPRECATED_KEUANGAN_HIT: Keuangan::get_riwayat_gantinama() dipanggil');
        $db = db_connect();
        $fileAccessService = new FileAccessService();
        $id_mkdt = $this->request->getVar('id_mkdt');
        $r['token'] = csrf_hash();

        if ($id_mkdt) {
            $idMkdtInt = (int) $id_mkdt;
            $x = $db->table('mkdt')
                ->select('id_mkdt, file_spptb')
                ->where('is_ganti_nama', 'Ganti Nama')
                ->where("uniq_id = (select uniq_id from mkdt where id_mkdt = {$idMkdtInt} and is_ganti_nama = 'Normal')")
                ->get()
                ->getResult();

            foreach ($x as $row) {
                if (! empty($row->file_spptb)) {
                    $row->file_spptb_access_url = $fileAccessService->accessUrl('mkdt_file_spptb', (int) $row->id_mkdt);
                }
            }
            $r['riwayat'] = $x;
        }

        return $this->response->setJSON($r);
    }

    /**
     * @deprecated Orphan legacy method — pengecekan status pembayaran MKDT.
     */
    public function isSudahBayar($id)
    {
        log_message('warning', 'DEPRECATED_KEUANGAN_HIT: Keuangan::isSudahBayar() dipanggil');
        $db = db_connect();
        $response['token'] = csrf_hash();

        $r = $db->table('log_pembayaran')
            ->select('count(id_keuangan) as c')
            ->where('id_mkdt', $id)
            ->where('payment_type !=', 'Booking')
            ->get()
            ->getRow();

        if ($r && (int) $r->c > 0) {
            $response['success'] = false;
            $response['messages'] = 'Tidak dapat menghapus tagihan karena sudah melakukan pembayaran';
        }

        return $this->response->setJSON($response);
    }
}

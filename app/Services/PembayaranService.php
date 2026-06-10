<?php

namespace App\Services;

use App\Repositories\NotifRepository;
use App\Repositories\KeuanganRepository;
use App\Repositories\KavlingRepository;
use App\Repositories\LogPembayaranRepository;
use App\Repositories\PaymentSummaryRepository;
use App\Models\MkdtModel;
use App\Exceptions\DataNotFoundException;
use App\Services\FinanceLedgerService;

class PembayaranService
{
    protected $notif;
    protected $lpModel;
    protected $db;
    protected $keuRepo;
    protected $kavRepo;
    protected $mkdtModel;
    protected $summaryRepo;
    protected $ledgerService;

    public function __construct()
    {
        $this->notif = new NotifRepository();
        $this->keuRepo = new KeuanganRepository();
        $this->kavRepo = new KavlingRepository();
        $this->mkdtModel = new MkdtModel();
        $this->lpModel = new LogPembayaranRepository();
        $this->summaryRepo = new PaymentSummaryRepository();
        $this->ledgerService = new FinanceLedgerService();
        $this->db = \Config\Database::connect();
    }


    function simpan($data)
    {
        $response['roken'] = csrf_hash();
        $li_keu = $this->keuRepo->getLIKeu();

        $pembayaran = [];
        foreach ($li_keu as $key => $value) {
            // $this->request->getPost("nominal-" . $value->id_keuangan_item_list)
            $nominal = $data->getPost("nominal-" . $value->id_keuangan_item_list);
            if ($nominal) {
                $pembayaran[] = [
                    'id' => $value->id_keuangan_item_list,
                    'nominal' => $this->num($nominal)
                ];
            }
        }
        $allKategori = $this->keuRepo->getLIKeu();
        $kategoriMap = [];
        foreach ($allKategori as $v) {
            $kategoriMap[$v->id_keuangan_item_list] = $v->kategori;
        }
        // var_dump($kategoriMap[1]);
        // die();
        //form untuk table log_pembayaran
        $id_keus = $data->getVar('bt-for');
        $id_keu = '';
        if ($id_keus) {
            foreach ($id_keus as $id) {
                $id_keu .= $id . ";";
            }
        }

        $form['id_keuangan'] = $id_keu;
        $form['id_mkdt'] = $data->getVar('id_mkdt');
        $form['nominal'] = $this->num($data->getPost('bt-bayar_tagihan_um'));
        $form['payment_type'] = $data->getVar('text_um');
        $form['keterangan'] = $data->getVar('bt-berita_acara_um');
        $form['tanggal_bayar'] = $data->getVar('bt-tanggal_bayar_um');
        $form['created_at'] = date('Y   -m-d H:i:s');
        $form['updated_at'] = date('Y-m-d H:i:s');
        $form['add_by'] = user_id();
        $form['edit_by'] = user_id();

        // $e = $data->getVar('e');

        $is_lunas = $data->getVar('is_lunas') ? 1 : 0;

        #############################
        $db = $this->db;
        try {
            $db->transStart();
            //bayar booking fee
            $this->bayarBookingFee($form['id_mkdt']);

            //insert log pembayaran
            $id_pembayaran = $this->lpModel->insert($form);

            foreach ($pembayaran as $k => $v) {
                $form_pembayaran = [
                    "id_pembayaran" => $id_pembayaran,
                    "id_keuangan_item_list" => $v['id'],
                    "nominal" => $v['nominal'],
                    "created_at" => date('Y-m-d H:i:s'),
                    "updated_at" => date('Y-m-d H:i:s'),
                    "add_by" => user_id(),
                    "edit_by" => user_id()
                ];
                $r = $this->lpModel->insertDetail($form_pembayaran);


                // ambil kategori item
                $kategori = $kategoriMap[$v['id']]; // UM / ADM / BB

                // var_dump($kategori);
                // die();
                // update summary
                $this->summaryRepo->updateCategory(
                    $form['id_mkdt'],
                    $kategori,
                    $this->num($v['nominal'])
                );

                if (!$r) {
                    $db->transRollback();
                    $response = [
                        'status' => false,
                        'message' => 'Gagal menambahkan detail pembayaran'
                    ];
                    return $response;
                }
            }

            $this->ledgerService->recordIncomeFromLogPembayaran((int) $id_pembayaran, user_id());

            $db->transCommit();
            $response = [
                'status' => true,
                'message' => 'Pembayaran berhasil',
                'data' => $pembayaran
            ];
            return $response;
        } catch (\Throwable $e) {
            $db->transRollback();
            // echo $e->getMessage();
            // echo '<pre>' . $e->getTraceAsString() . '</pre>';
            $response = [
                'status' => false,
                'message' => 'Gagal menambahkan pembayaran',
                'error' => $e->getTraceAsString()
            ];
            return $response;
        }
    }
    function bayarBookingFee($id_mkdt)
    {
        $booking_fee = $this->mkdtModel->select('booking_paid,booking_fee,booking_tgl')->where('id_mkdt', $id_mkdt)->first();
        $is_paid = $this->lpModel->isBookingPaid($id_mkdt);

        if (!$is_paid) {
            try {
                $db = $this->db;
                $db->transStart();
                $data = array(
                    "id_mkdt" => $id_mkdt,
                    "nominal" => $booking_fee->booking_fee,
                    "tanggal_bayar" => $booking_fee->booking_tgl,
                    "payment_type" => "Booking",
                    "keterangan" => "",
                    "add_by" => user_id(),
                    "created_at" => date('Y-m-d H:i:s'),
                    "updated_at" => date('Y-m-d H:i:s'),
                    "edit_by" => user_id()
                );

                $this->mkdtModel->update($id_mkdt, ['booking_paid' => 1]);
                $id_pembyaaran = $this->lpModel->insert($data);

                $form_pembayaran = [
                    "id_pembayaran" => $id_pembyaaran,
                    "id_keuangan_item_list" => 1,
                    "nominal" => $booking_fee->booking_fee,
                    "created_at" => date('Y-m-d H:i:s'),
                    "updated_at" => date('Y-m-d H:i:s'),
                    "add_by" => user_id(),
                    "edit_by" => user_id()
                ];
                $this->lpModel->insertDetail($form_pembayaran);
                $this->ledgerService->recordIncomeFromLogPembayaran((int) $id_pembyaaran, user_id());

                $db->transCommit();
            } catch (\Throwable $th) {
                $db->transRollback();
                throw $th;
            }
        }
    }

    function recalculateSummary($id_mkdt)
    {
        $this->summaryRepo->setToZero($id_mkdt);

        $detail = $this->lpModel->getDetailRiwayatBayarByIdMkdt($id_mkdt);

        $grouped = [];
        foreach ($detail as $row) {
            $grouped[$row['kategori']] =
                ($grouped[$row['kategori']] ?? 0) + $this->num($row['nominal']);
        }

        foreach ($grouped as $kategori => $nominal) {
            $this->summaryRepo->updateCategory($id_mkdt, $kategori, $nominal);
        }
    }
    function removeLP($request)
    {
        $response = [
            'token'   => csrf_hash(),
            'success' => false,
            'messages' => 'Gagal menghapus data'
        ];

        $idPembayaran = $request->getVar('id_pembayaran');
        if (!$idPembayaran) {
            $response['messages'] = 'ID pembayaran tidak valid';
            return $response;
        }

        $db = $this->db;

        try {
            $db->transBegin();
            //soft delete log pembayaran
            $idMkdt = $this->lpModel->softDeleteAndReturnIdMkdt($idPembayaran);
            $this->ledgerService->voidByLogPembayaran((int) $idPembayaran, user_id());
            //recalculate summary
            $this->recalculateSummary($idMkdt);

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaksi gagal');
            }

            $db->transCommit();

            $response['success']  = true;
            $response['messages'] = 'Berhasil menghapus data';
            return $response;
        } catch (DataNotFoundException $e) {
            $db->transRollback();
            $response['messages'] = $e->getMessage();
            return $response;
        } catch (\Throwable $e) {
            $db->transRollback();
            $response['messages'] = $e->getMessage();
            log_message('error', $e->getMessage());
            return $response;
        }
    }

    protected function num($d)
    {
        $d = str_replace(',', "", $d);
        return $d;
    }
}

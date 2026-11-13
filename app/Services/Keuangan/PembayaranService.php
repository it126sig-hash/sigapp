<?php

namespace App\Services\Keuangan;

use App\Exceptions\DataNotFoundException;
use App\Models\MkdtModel;
use App\Repositories\Keuangan\BookingPaymentRepository;
use App\Repositories\Keuangan\KeuanganRepository;
use App\Repositories\Keuangan\LogPembayaranRepository;
use App\Repositories\Keuangan\PaymentSummaryRepository;
use App\Repositories\Transaksi\KavlingRepository;
use App\Services\Mkdt\MkdtFinancialBreakdownService;
use App\Services\Mkdt\MkdtSettlementService;
use App\Services\Notifikasi\NotifikasiService;

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
    protected BookingPaymentService $bookingService;
    protected MkdtSettlementService $settlementService;
    protected MkdtFinancialBreakdownService $financialBreakdownService;

    public function __construct()
    {
        $this->notif = new NotifikasiService();
        $this->keuRepo = new KeuanganRepository();
        $this->kavRepo = new KavlingRepository();
        $this->mkdtModel = new MkdtModel();
        $this->lpModel = new LogPembayaranRepository();
        $this->summaryRepo = new PaymentSummaryRepository();
        $this->ledgerService = new FinanceLedgerService();
        $this->db = \Config\Database::connect();
        $this->bookingService = new BookingPaymentService($this->db);
        $this->settlementService = new MkdtSettlementService($this->db);
        $this->financialBreakdownService = new MkdtFinancialBreakdownService($this->db);
    }

    public function simpan($data): array
    {
        $get = static function (string $key, mixed $default = null) use ($data): mixed {
            if (is_array($data)) {
                return $data[$key] ?? $default;
            }
            return $data->getVar($key) ?? $default;
        };
        $getPost = static function (string $key, mixed $default = null) use ($data): mixed {
            if (is_array($data)) {
                return $data[$key] ?? $default;
            }
            return $data->getPost($key) ?? $default;
        };

        $allKategori = $this->keuRepo->getLIKeu();
        $kategoriMap = [];
        $total = 0.0;
        $pembayaran = [];

        foreach ($allKategori as $value) {
            $kategoriMap[$value->id_keuangan_item_list] = $value->kategori;
            $nominal = $getPost('nominal-' . $value->id_keuangan_item_list);
            if ($nominal !== null && $nominal !== '') {
                $cleanNominal = (float) $this->num($nominal);
                if ($cleanNominal != 0.0) {
                    $total += $cleanNominal;
                    $pembayaran[] = [
                        'id' => $value->id_keuangan_item_list,
                        'nominal' => $cleanNominal,
                    ];
                }
            }
        }

        $nominalBayar = (float) $this->num($getPost('bt-bayar_tagihan_um'));
        if ($nominalBayar <= 0) {
            return [
                'token' => csrf_hash(),
                'status' => false,
                'message' => 'Nominal pembayaran tidak boleh 0!',
            ];
        }
        if (abs($total - $nominalBayar) > 0.001) {
            return [
                'token' => csrf_hash(),
                'status' => false,
                'message' => 'Alokasi dana harus sama dengan nominal pembayaran!',
            ];
        }

        // Form untuk table log_pembayaran
        $id_keus = $get('bt-for');
        $id_keu = '';
        if (is_array($id_keus)) {
            foreach ($id_keus as $id) {
                $id_keu .= $id . ';';
            }
        } elseif (! empty($id_keus)) {
            $id_keu = (string) $id_keus;
        }

        $form['id_keuangan'] = $id_keu;
        $form['id_mkdt'] = $get('id_mkdt');
        $form['nominal'] = $nominalBayar;
        $form['payment_type'] = $get('text_um');
        $form['keterangan'] = $get('bt-berita_acara_um');
        $form['tanggal_bayar'] = $get('bt-tanggal_bayar_um');
        $form['created_at'] = date('Y-m-d H:i:s');
        $form['updated_at'] = date('Y-m-d H:i:s');
        $form['add_by'] = user_id();
        $form['edit_by'] = user_id();

        if ($this->lpModel->hasRecentDuplicate((int) $form['id_mkdt'], (string) $form['id_keuangan'], $form['nominal'], (string) $form['tanggal_bayar'], (string) $form['payment_type'])) {
            return [
                'token' => csrf_hash(),
                'status' => false,
                'message' => 'Pembayaran dengan nominal dan tanggal yang sama baru saja disimpan. Silakan cek Riwayat Pembayaran sebelum mengulang.',
            ];
        }

        $db = $this->db;
        try {
            $db->transException(true)->transBegin();

            $this->bookingService->assertManualAllocationAllowed((int) $form['id_mkdt'], $pembayaran);
            $discrepancies = $this->financialBreakdownService->validatePaymentAllocation(
                (int) $form['id_mkdt'],
                $pembayaran,
                (float) $form['nominal']
            );

            $id_pembayaran = $this->lpModel->insert($form);
            if (! $id_pembayaran) {
                throw new \RuntimeException('Gagal menambahkan pembayaran');
            }

            foreach ($pembayaran as $v) {
                $form_pembayaran = [
                    'id_pembayaran' => $id_pembayaran,
                    'id_keuangan_item_list' => $v['id'],
                    'nominal' => $v['nominal'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                    'add_by' => user_id(),
                    'edit_by' => user_id(),
                    'booking_is_installment' => ($kategoriMap[$v['id']] === 'BO' ? 1 : 0),
                ];
                $r = $this->lpModel->insertDetail($form_pembayaran);
                if (! $r) {
                    throw new \RuntimeException('Gagal menambahkan detail pembayaran');
                }
            }

            $this->ledgerService->recordIncomeFromLogPembayaran((int) $id_pembayaran, user_id());
            (new BookingPaymentRepository($db))->recalculate((int) $form['id_mkdt']);
            $this->settlementService->synchronize((int) $form['id_mkdt']);

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaksi gagal');
            }
            $db->transCommit();

            if (! empty($discrepancies)) {
                $kavlingData = $this->kavRepo->getKavlingByIdMkdt((int) $form['id_mkdt']);
                if ($kavlingData) {
                    $itemNames = array_map(static function ($v) use ($allKategori) {
                        foreach ($allKategori as $kat) {
                            if ($kat->id_keuangan_item_list == $v['id']) {
                                return $kat->item;
                            }
                        }
                        return 'Item Tidak Diketahui';
                    }, $pembayaran);

                    $pesan = sprintf(
                        'Pembayaran Kavling %s No. %s (%s): Alokasi %s tidak sesuai list tagihan MKDT. Perlu rekonsiliasi. Detail: %s',
                        $kavlingData['nama_jalan'] ?? '-',
                        $kavlingData['no_kavling'] ?? '-',
                        $kavlingData['nama_konsumen'] ?? '-',
                        implode(', ', $itemNames),
                        implode('; ', $discrepancies)
                    );

                    $actionUrl = 'list-kavling?search=' . urlencode($kavlingData['nama_konsumen'] ?? '');
                    $this->notif->tambah_notif(
                        '4',
                        $pesan,
                        user_id(),
                        $kavlingData['id_kavling'] ?? null,
                        $kavlingData['id_konsumen'] ?? null,
                        \App\Enums\NotificationEvent::REKONSILIASI_KEUANGAN,
                        $kavlingData['id_proyek'] ?? null,
                        $actionUrl
                    );
                }
            }

            return [
                'token' => csrf_hash(),
                'status' => true,
                'message' => 'Pembayaran berhasil',
                'data' => $pembayaran,
            ];
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', '[PembayaranService::simpan] {message}', ['message' => $e->getMessage()]);
            return [
                'token' => csrf_hash(),
                'status' => false,
                'message' => $e instanceof \DomainException ? $e->getMessage() : 'Gagal menambahkan pembayaran',
            ];
        }
    }

    public function recalculateSummary($id_mkdt): void
    {
        (new BookingPaymentRepository($this->db))->recalculate((int) $id_mkdt);
    }

    public function recalculateAllSummaries(): int
    {
        $mkdtIds = $this->lpModel->getDistinctActiveMkdtIds();
        foreach ($mkdtIds as $idMkdt) {
            $this->recalculateSummary($idMkdt);
        }

        return count($mkdtIds);
    }

    public function removeLP($request): array
    {
        $response = [
            'token' => csrf_hash(),
            'success' => false,
            'messages' => 'Gagal menghapus data',
        ];

        $idPembayaran = is_array($request)
            ? ($request['id_pembayaran'] ?? null)
            : $request->getVar('id_pembayaran');

        if (! $idPembayaran) {
            $response['messages'] = 'ID pembayaran tidak valid';
            return $response;
        }

        $db = $this->db;

        try {
            $db->transException(true)->transBegin();
            $this->bookingService->assertMayDelete((int) $idPembayaran);
            // Soft delete log pembayaran
            $idMkdt = $this->lpModel->softDeleteAndReturnIdMkdt($idPembayaran);
            $this->ledgerService->voidByLogPembayaran((int) $idPembayaran, user_id());
            // Recalculate summary
            $this->recalculateSummary($idMkdt);
            $this->settlementService->synchronize((int) $idMkdt);

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaksi gagal');
            }

            $db->transCommit();

            $response['success'] = true;
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

    protected function num($d): float
    {
        if ($d === null || $d === '') {
            return 0.0;
        }
        $cleaned = str_replace([',', ' '], '', (string) $d);
        return is_numeric($cleaned) ? (float) $cleaned : 0.0;
    }
}

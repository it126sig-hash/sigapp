<?php

namespace App\Services\Keuangan;

use App\Enums\NotificationEvent;
use App\Libraries\Mpdf_lib;
use App\Repositories\BookingPaymentRepository;
use App\Repositories\Keuangan\PenagihanRepository;
use App\Services\FileAccessService;
use App\Services\KeuanganService as LegacyKeuanganService;
use App\Services\Notifikasi\NotifikasiService;
use Dompdf\Dompdf;

class PenagihanService
{
    protected PenagihanRepository $penagihanRepo;
    protected BookingPaymentRepository $bookingPaymentRepo;
    protected LegacyKeuanganService $keuanganService;
    protected FileAccessService $fileAccessService;
    protected Mpdf_lib $mpdf;
    protected NotifikasiService $notifikasiService;

    public const KOPSURAT_ALLOWED_GROUPS = [
        'Admin',
        'Umum',
        'Keuangan',
        'MKDT',
        'Legal',
        'Planning',
        'Produksi',
        'Sales',
        'Direksi',
        'Pajak',
    ];

    public function __construct(
        ?PenagihanRepository $penagihanRepo = null,
        ?BookingPaymentRepository $bookingPaymentRepo = null,
        ?LegacyKeuanganService $keuanganService = null,
        ?FileAccessService $fileAccessService = null,
        ?Mpdf_lib $mpdf = null,
        ?NotifikasiService $notifikasiService = null
    ) {
        $this->penagihanRepo = $penagihanRepo ?? new PenagihanRepository();
        $this->bookingPaymentRepo = $bookingPaymentRepo ?? new BookingPaymentRepository($this->penagihanRepo->getDb());
        $this->keuanganService = $keuanganService ?? new LegacyKeuanganService();
        $this->fileAccessService = $fileAccessService ?? new FileAccessService();
        $this->mpdf = $mpdf ?? new Mpdf_lib();
        $this->notifikasiService = $notifikasiService ?? new NotifikasiService();
    }

    public function getMetaPayload(?int $activeProjectId = null): array
    {
        $projectId = $activeProjectId;
        if ($projectId === null && function_exists('resolve_active_proyek_id')) {
            $projectId = resolve_active_proyek_id(null);
        }

        $kopsuratList = $this->getKopsuratList()['data'] ?? [];
        $canManagePenagihan = function_exists('in_groups') ? in_groups(['1', '3']) : false;
        $canManageKopsurat = function_exists('in_groups') ? in_groups(self::KOPSURAT_ALLOWED_GROUPS) : false;

        return [
            'active_project_id' => $projectId,
            'status_tagihan_options' => [
                ['value' => 'draft', 'label' => 'Draft'],
                ['value' => 'publish', 'label' => 'Publish'],
                ['value' => 'lunas', 'label' => 'Lunas'],
                ['value' => 'batal', 'label' => 'Batal'],
            ],
            'ukuran_kertas_options' => ['A4', 'F4'],
            'kopsurat_options' => $kopsuratList,
            'permissions' => [
                'can_manage_penagihan' => $canManagePenagihan,
                'can_manage_kopsurat'  => $canManageKopsurat,
                'can_input_pembayaran' => $canManagePenagihan,
            ],
        ];
    }

    public function isiTagihan($request, ?int $actorId): array
    {
        return $this->keuanganService->simpanIsiTagihan($request, $actorId);
    }

    public function saveSudahDibayar($request): array
    {
        return $this->keuanganService->updateSudahDibayar($request);
    }

    public function saveLegacyPayment(array $input, ?int $actorId): array
    {
        $response = [
            'token'    => csrf_hash(),
            'success'  => false,
            'messages' => 'Gagal menyimpan pembayaran',
        ];

        $idKavling = (int) ($input['id_kavling'] ?? 0);
        $idMkdt = (int) ($input['id_mkdt'] ?? 0);

        $beritaAcara = $input['bt-berita_acara_um'] ?? '';
        $bayarTagihan = $this->cleanNumeric($input['bt-bayar_tagihan_um'] ?? 0);
        $tanggalBayar = $input['bt-tanggal_bayar_um'] ?? null;
        $idKeus = $input['bt-for'] ?? [];
        $textKeu = (string) ($input['text_um'] ?? '');

        $beritaAcaraBb = $input['bt-berita_acara_bb'] ?? '';
        $bayarTagihanBb = $this->cleanNumeric($input['bt-bayar_tagihan_bb'] ?? 0);
        $tanggalBayarBb = $input['bt-tanggal_bayar_bb'] ?? null;
        $idKeuBbs = $input['bt-for_bb'] ?? [];
        $textKeuBb = (string) ($input['text_bb'] ?? '');

        $idKeu = '';
        if (is_array($idKeus)) {
            foreach ($idKeus as $id) {
                $idKeu .= $id . ';';
            }
        }

        $idKeuBb = '';
        if (is_array($idKeuBbs)) {
            foreach ($idKeuBbs as $id) {
                $idKeuBb .= $id . ';';
            }
        }

        $mode = (string) ($input['e'] ?? '');
        $refundPaid = 1;
        $kav = $idKavling > 0 ? $this->penagihanRepo->getKavlingForPayment($idKavling) : null;
        $namaKonsumen = $kav->nama_konsumen ?? '';
        $namaJalan = $kav->nama_jalan ?? '';
        $noKavling = $kav->no_kavling ?? '';

        $db = $this->penagihanRepo->getDb();
        $db->transException(true)->transBegin();

        try {
            if (($input['status_mkdt'] ?? '') === 'Batal') {
                $nominalRefund = $this->cleanNumeric($input['nominal_refund'] ?? 0);
                $tglRefund = $input['tanggal_refund'] ?? null;

                $refundData = [
                    'refund_paid' => ($refundPaid == 1) ? 1 : 0,
                    'refund'      => $nominalRefund,
                    'refund_tgl'  => $tglRefund,
                ];

                if (! $this->penagihanRepo->updateMkdt($idMkdt, $refundData)) {
                    throw new \RuntimeException('Gagal memperbarui data refund');
                }

                $logData = [
                    'id_mkdt'       => $idMkdt,
                    'nominal'       => $nominalRefund,
                    'payment_type'  => 'Refund',
                    'tanggal_bayar' => $tglRefund,
                    'keterangan'    => 'Refund: ' . ($input['keterangan_refund'] ?? '') . ' - ' . $namaKonsumen . ' - ' . $namaJalan . ' No. ' . $noKavling,
                    'add_by'        => $actorId,
                    'edit_by'       => $actorId,
                ];

                if (! $this->penagihanRepo->insertLogPembayaran($logData)) {
                    throw new \RuntimeException('Gagal menyimpan pembayaran refund');
                }

                $response['success'] = true;
                $response['messages'] = 'Data berhasil ditambah';
            } else {
                $bookingFeePaid = $input['booking_fee_paid'] ?? '';
                $bookingFee = 0;
                $bookingFeeTgl = null;
                if (! empty($bookingFeePaid)) {
                    $bookingFee = $this->cleanNumeric($input['keu_booking_fee'] ?? 0);
                    $bookingFeeTgl = $input['keu_booking_tgl'] ?? null;
                }

                $isPaid = $this->penagihanRepo->isMkdtBookingPaid($idMkdt);

                if (! $isPaid && ! empty($bookingFeePaid)) {
                    $bookingData = [
                        'id_mkdt'       => $idMkdt,
                        'nominal'       => $bookingFee,
                        'tanggal_bayar' => $bookingFeeTgl,
                        'payment_type'  => 'Booking',
                        'keterangan'    => 'Booking: ' . $namaKonsumen . ' - ' . $namaJalan . ' No. ' . $noKavling,
                        'add_by'        => $actorId,
                        'edit_by'       => $actorId,
                    ];

                    if (! $this->penagihanRepo->insertLogPembayaran($bookingData)) {
                        throw new \RuntimeException('Gagal menyimpan pembayaran booking');
                    }

                    if (! $this->penagihanRepo->updateMkdt($idMkdt, ['booking_paid' => $bookingFeePaid])) {
                        throw new \RuntimeException('Gagal memperbarui status pembayaran booking');
                    }
                }

                if ($mode === '') {
                    if ($bayarTagihan > 0) {
                        $umData = [
                            'id_mkdt'       => $idMkdt,
                            'id_keuangan'   => $idKeu,
                            'nominal'       => $bayarTagihan,
                            'payment_type'  => $textKeu,
                            'tanggal_bayar' => $tanggalBayar,
                            'keterangan'    => '(' . $beritaAcara . ') Pembayaran: ' . $textKeu . ' - ' . $namaKonsumen . ' - ' . $namaJalan . ' No. ' . $noKavling,
                            'add_by'        => $actorId,
                            'edit_by'       => $actorId,
                        ];
                        if (! $this->penagihanRepo->insertLogPembayaran($umData)) {
                            throw new \RuntimeException('Gagal menyimpan pembayaran UM');
                        }
                    }
                } elseif ($mode === 'bb') {
                    if ($bayarTagihanBb > 0) {
                        $bbData = [
                            'id_mkdt'       => $idMkdt,
                            'id_keuangan'   => $idKeuBb,
                            'nominal'       => $bayarTagihanBb,
                            'payment_type'  => $textKeuBb,
                            'tanggal_bayar' => $tanggalBayarBb,
                            'keterangan'    => '(' . $beritaAcaraBb . ')Pembayaran: ' . $textKeuBb . ' - ' . $namaKonsumen . ' - ' . $namaJalan . ' No. ' . $noKavling,
                            'add_by'        => $actorId,
                            'edit_by'       => $actorId,
                        ];
                        if (! $this->penagihanRepo->insertLogPembayaran($bbData)) {
                            throw new \RuntimeException('Gagal menyimpan pembayaran BB');
                        }
                    }
                }

                $response['success'] = true;
                $response['messages'] = 'Data berhasil diinput';
            }

            $this->keuanganService->synchronizeLunasStatus($idMkdt);
            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaksi gagal');
            }
            $db->transCommit();
        } catch (\Throwable $e) {
            try {
                $db->transRollback();
            } catch (\Throwable $rollbackError) {
                // Ignore rollback failure
            }
            log_message('error', '[PenagihanService::saveLegacyPayment] {message}', ['message' => $e->getMessage()]);
            $response['success'] = false;
            $response['messages'] = 'Gagal menyimpan pembayaran: ' . $e->getMessage();
        }

        return $response;
    }

    public function getTagihanBundle(int $idMkdt, ?string $mode = null): array
    {
        if ($idMkdt <= 0) {
            return ['token' => csrf_hash()];
        }

        $summary = $this->bookingPaymentRepo->installmentSummary($idMkdt);

        $result = [
            'token'          => csrf_hash(),
            'compro'         => $this->penagihanRepo->getProfilePerusahaan(),
            'detail'         => $this->penagihanRepo->getMkdtWithKonsumen($idMkdt),
            'list_tagihan'   => $this->penagihanRepo->getKeuanganByMkdt($idMkdt),
            'log_pembayaran' => $this->penagihanRepo->getLogPembayaranWithUserByMkdt($idMkdt),
            'sudah_bayar'    => (float) ($summary['sudah_bayar'] ?? 0),
            'total_tagihan'  => (float) ($summary['total_tagihan'] ?? 0),
            'sisa_tagihan'   => (float) ($summary['sisa_tagihan'] ?? 0),
        ];

        if ($mode === 'inv') {
            $kopsurat = $this->penagihanRepo->getKopsuratListObjects();
            foreach ($kopsurat as $item) {
                $item->preview_url = $this->fileAccessService->accessUrl('kop_surat', (int) $item->id);
            }

            $result['invoice'] = $this->penagihanRepo->getInvoicesByMkdt($idMkdt);
            $result['kop_surat'] = $kopsurat;
            $result['kode_keuangan'] = $this->penagihanRepo->getKodeKeuanganByMkdt($idMkdt);
        }

        return $result;
    }

    public function getRiwayatTagihan(?int $idMkdt = null, ?string $noInv = null): array
    {
        $riwayat = $this->penagihanRepo->getRiwayatTagihan($idMkdt, $noInv);

        return [
            'token'   => csrf_hash(),
            'success' => true,
            'data'    => $riwayat,
        ];
    }

    public function simpanPenagihan(array $input, int $actorId): array
    {
        $response = [
            'token'    => csrf_hash(),
            'success'  => false,
            'messages' => 'Terjadi kesalahan saat menyimpan invoice',
        ];

        if (function_exists('in_groups') && ! in_groups(['1', '3'])) {
            $response['messages'] = 'Akses ditolak. Hanya user Keuangan yang berhak membuat surat tagihan.';
            $response['_http_status'] = 403;
            return $response;
        }

        $idMkdt = (int) ($input['id_mkdt'] ?? 0);
        $idKonsumen = $input['id_konsumen'] ?? null;
        $idKavling = $input['id_kavling'] ?? null;
        $idKopsurat = (int) ($input['id_kopsurat'] ?? 0);
        $tanggalInvoice = $input['tanggal_invoice'] ?? null;
        $tanggalJatuhTempo = $input['tanggal_jatuh_tempo'] ?? null;
        $terms = $input['terms'] ?? null;
        $tagihan = $input['tagihan'] ?? null;

        if ($idMkdt > 0 && (empty($idKonsumen) || empty($idKavling))) {
            $mkdtRow = $this->penagihanRepo->getMkdtRow($idMkdt);
            if ($mkdtRow) {
                if (empty($idKonsumen)) {
                    $idKonsumen = $mkdtRow->id_konsumen;
                }
                if (empty($idKavling)) {
                    $idKavling = $mkdtRow->id_kavling;
                }
            }
        }

        $nomorSurat = $input['nomor_surat'] ?? null;
        $statusTagihan = (string) ($input['status_tagihan'] ?? 'draft');
        $noInv = (string) ($input['no_inv'] ?? '');

        if ($idMkdt <= 0) {
            $response['messages'] = 'Data transaksi/konsumen (MKDT) tidak ditemukan.';
            $response['_http_status'] = 422;
            return $response;
        }

        if ($idKopsurat <= 0) {
            $response['messages'] = 'Silakan pilih Kop Surat terlebih dahulu.';
            $response['_http_status'] = 422;
            return $response;
        }

        $kop = $this->penagihanRepo->findKopsuratObject($idKopsurat);
        if (! $kop) {
            $response['messages'] = 'Kop Surat yang dipilih tidak valid atau sudah dihapus.';
            $response['_http_status'] = 422;
            return $response;
        }

        if (empty($tagihan) || $tagihan === '[]') {
            $response['messages'] = 'Tidak ada item tagihan untuk dibuatkan invoice.';
            $response['_http_status'] = 422;
            return $response;
        }

        if (empty($nomorSurat) || trim((string) $nomorSurat) === '') {
            $response['messages'] = 'Nomor Surat wajib diisi.';
            $response['_http_status'] = 422;
            return $response;
        }

        if ($statusTagihan === 'publish') {
            $dup = $this->penagihanRepo->findDuplicateNomorSurat((string) $nomorSurat, $noInv !== '' ? $noInv : null);
            if ($dup) {
                $response['messages'] = $this->buildDuplicateNomorSuratMessage($dup);
                $response['_http_status'] = 422;
                return $response;
            }
        }

        $isEdit = false;
        if ($noInv !== '') {
            $oldInv = $this->penagihanRepo->findInvoiceByNoInv($noInv);
            if ($oldInv && $oldInv->status_tagihan === 'draft') {
                $isEdit = true;
            } elseif ($oldInv) {
                $response['messages'] = 'Hanya tagihan berstatus DRAFT yang dapat diedit.';
                $response['_http_status'] = 422;
                return $response;
            }
        }

        if (! $isEdit) {
            $prefix = 'INV/' . date('Y') . '/' . date('m') . '/';
            $lastInv = $this->penagihanRepo->findLastInvoiceByPrefix($prefix);
            if ($lastInv) {
                $lastNum = (int) substr((string) $lastInv->no_inv, -4);
                $newNum = str_pad((string) ($lastNum + 1), 4, '0', STR_PAD_LEFT);
            } else {
                $newNum = '0001';
            }
            $noInv = $prefix . $newNum;
        }

        $db = $this->penagihanRepo->getDb();
        $db->transException(true);

        try {
            $db->transStart();

            $saveData = [
                'nomor_surat'         => trim((string) $nomorSurat),
                'id_mkdt'             => $idMkdt,
                'id_konsumen'         => $idKonsumen,
                'id_kavling'          => $idKavling,
                'id_kopsurat'         => $idKopsurat,
                'tanggal_invoice'     => $tanggalInvoice,
                'tanggal_jatuh_tempo' => $tanggalJatuhTempo,
                'tagihan'             => is_string($tagihan) ? $tagihan : json_encode($tagihan),
                'terms'               => $terms,
                'status_tagihan'      => $statusTagihan,
                'edit_by'             => $actorId,
                'date_edit'           => date('Y-m-d H:i:s'),
            ];

            if ($isEdit) {
                $saved = $this->penagihanRepo->updateInvoice($noInv, $saveData);
            } else {
                $saveData['no_inv'] = $noInv;
                $saveData['add_by'] = $actorId;
                $saveData['date_add'] = date('Y-m-d H:i:s');
                $saved = $this->penagihanRepo->insertInvoice($saveData);
            }

            if (! $saved) {
                throw new \RuntimeException('Gagal menyimpan invoice');
            }

            $this->penagihanRepo->insertInvoiceStatusLog([
                'no_inv'     => $noInv,
                'status'     => $statusTagihan,
                'tanggal'    => date('Y-m-d'),
                'keterangan' => 'Surat penagihan ' . ($isEdit ? 'diedit' : 'dibuat') . ' sebagai ' . strtoupper($statusTagihan) . '.',
                'add_by'     => $actorId,
                'date_add'   => date('Y-m-d H:i:s'),
            ]);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaksi gagal');
            }

            if ($statusTagihan === 'publish') {
                $this->notifyTagihanSignatureRequested(
                    trim((string) $nomorSurat),
                    $idKavling ? (int) $idKavling : null,
                    $idKonsumen ? (int) $idKonsumen : null,
                    $idMkdt,
                    $tagihan,
                    $actorId
                );
            }

            return [
                'token'    => csrf_hash(),
                'success'  => true,
                'messages' => 'Tagihan berhasil ' . ($isEdit ? 'diperbarui' : 'dibuat'),
                'no_inv'   => $noInv,
            ];
        } catch (\Throwable $e) {
            try {
                $db->transRollback();
            } catch (\Throwable $rollbackError) {
                // Ignore rollback failure
            }
            $response['messages'] = $e->getMessage();
            $response['_http_status'] = 500;
            return $response;
        }
    }

    public function updateStatusPenagihan(array $input, int $actorId): array
    {
        $response = [
            'token'    => csrf_hash(),
            'success'  => false,
            'messages' => 'Terjadi kesalahan',
        ];

        if (function_exists('in_groups') && ! in_groups(['1', '3'])) {
            $response['messages'] = 'Akses ditolak. Hanya user Keuangan yang berhak mengubah status surat tagihan.';
            $response['_http_status'] = 403;
            return $response;
        }

        $noInv = trim((string) ($input['no_inv'] ?? ''));
        $statusTagihan = trim((string) ($input['status_tagihan'] ?? ''));
        $tanggalUbahStatus = $input['tanggal_ubah_status'] ?? null;
        $keteranganStatus = $input['keterangan_status'] ?? null;
        $nomorSurat = $input['nomor_surat'] ?? null;

        if ($noInv === '' || $statusTagihan === '') {
            $response['messages'] = 'Data tidak lengkap';
            $response['_http_status'] = 422;
            return $response;
        }

        $updateData = [
            'status_tagihan'      => $statusTagihan,
            'tanggal_ubah_status' => $tanggalUbahStatus,
            'keterangan_status'   => $keteranganStatus,
            'date_edit'           => date('Y-m-d H:i:s'),
            'edit_by'             => $actorId,
        ];

        if ($statusTagihan === 'publish') {
            if (empty($nomorSurat) || trim((string) $nomorSurat) === '') {
                $response['messages'] = 'Nomor Surat wajib diisi saat Publish.';
                $response['_http_status'] = 422;
                return $response;
            }

            $dup = $this->penagihanRepo->findDuplicateNomorSurat((string) $nomorSurat, $noInv);
            if ($dup) {
                $response['messages'] = $this->buildDuplicateNomorSuratMessage($dup);
                $response['_http_status'] = 422;
                return $response;
            }
            $updateData['nomor_surat'] = trim((string) $nomorSurat);
        }

        $this->penagihanRepo->updateInvoice($noInv, $updateData);
        $this->penagihanRepo->insertInvoiceStatusLog([
            'no_inv'     => $noInv,
            'status'     => $statusTagihan,
            'tanggal'    => $tanggalUbahStatus,
            'keterangan' => $keteranganStatus,
            'add_by'     => $actorId,
            'date_add'   => date('Y-m-d H:i:s'),
        ]);

        if ($statusTagihan === 'publish') {
            $invoice = $this->penagihanRepo->findInvoiceByNoInv($noInv);
            $this->notifyTagihanSignatureRequested(
                trim((string) ($nomorSurat ?: ($invoice->nomor_surat ?? $noInv))),
                $invoice && ! empty($invoice->id_kavling) ? (int) $invoice->id_kavling : null,
                $invoice && ! empty($invoice->id_konsumen) ? (int) $invoice->id_konsumen : null,
                $invoice && ! empty($invoice->id_mkdt) ? (int) $invoice->id_mkdt : 0,
                $invoice->tagihan ?? null,
                $actorId
            );
        }

        return [
            'token'    => csrf_hash(),
            'success'  => true,
            'messages' => 'Status berhasil diubah',
        ];
    }

    public function getKopsuratList(): array
    {
        $list = $this->penagihanRepo->getKopsuratListArrays();
        foreach ($list as &$item) {
            $item['preview_url'] = $this->fileAccessService->accessUrl('kop_surat', (int) $item['id']);
        }
        unset($item);

        return [
            'token'   => csrf_hash(),
            'success' => true,
            'data'    => $list,
        ];
    }

    public function getKopsuratDetail(int $id): array
    {
        $kop = $id > 0 ? $this->penagihanRepo->findKopsuratArray($id) : null;
        if ($kop) {
            $kop['preview_url'] = $this->fileAccessService->accessUrl('kop_surat', (int) $kop['id']);
        }

        return [
            'token'   => csrf_hash(),
            'success' => (bool) $kop,
            'data'    => $kop,
        ];
    }

    public function simpanKopsurat(array $postData, $fileKop = null): array
    {
        if (function_exists('logged_in') && ! logged_in()) {
            return [
                'token'        => csrf_hash(),
                'success'      => false,
                'message'      => 'Silakan login terlebih dahulu.',
                '_http_status' => 401,
            ];
        }

        if (function_exists('in_groups') && ! in_groups(self::KOPSURAT_ALLOWED_GROUPS)) {
            return [
                'token'        => csrf_hash(),
                'success'      => false,
                'message'      => 'Anda tidak memiliki hak akses.',
                '_http_status' => 403,
            ];
        }

        $id = ! empty($postData['id']) ? (int) $postData['id'] : null;
        $nama = trim((string) ($postData['nama'] ?? ''));
        $ukuran = strtoupper(trim((string) ($postData['ukuran'] ?? '')));
        if ($ukuran !== 'F4') {
            $ukuran = 'A4';
        }

        if ($nama === '') {
            return [
                'token'        => csrf_hash(),
                'success'      => false,
                'message'      => 'Nama kop surat wajib diisi.',
                '_http_status' => 422,
            ];
        }

        if ($ukuran === 'F4') {
            $w = '21.5cm';
            $h = '33cm';
        } else {
            $w = '21cm';
            $h = '29.7cm';
        }

        $numMargin = static function ($val, float $default): float {
            return ($val !== null && $val !== '') ? (float) $val : $default;
        };

        $saveData = [
            'nama'   => $nama,
            'ukuran' => $ukuran,
            'w'      => $w,
            'h'      => $h,
            'mt'     => $numMargin($postData['mt'] ?? null, 0.0),
            'mb'     => $numMargin($postData['mb'] ?? null, 0.0),
            'ml'     => $numMargin($postData['ml'] ?? null, 0.0),
            'mr'     => $numMargin($postData['mr'] ?? null, 0.0),
            'pmt'    => $numMargin($postData['pmt'] ?? null, 30.0),
            'pmb'    => $numMargin($postData['pmb'] ?? null, 25.0),
            'pml'    => $numMargin($postData['pml'] ?? null, 15.0),
            'pmr'    => $numMargin($postData['pmr'] ?? null, 15.0),
        ];

        if ($fileKop && $fileKop->isValid() && ! $fileKop->hasMoved()) {
            $allowedTypes = ['image/png', 'image/jpeg', 'image/jpg'];
            if (! in_array($fileKop->getMimeType(), $allowedTypes, true)) {
                return [
                    'token'        => csrf_hash(),
                    'success'      => false,
                    'message'      => 'Format file background harus JPG atau PNG.',
                    '_http_status' => 422,
                ];
            }

            $saveData['lokasi'] = $this->fileAccessService->store($fileKop, 'uploads/kop');

            if (! empty($id)) {
                $oldKop = $this->penagihanRepo->findKopsuratObject($id);
                if ($oldKop && ! empty($oldKop->lokasi)) {
                    $oldPath = $this->fileAccessService->existingPath((string) $oldKop->lokasi);
                    if ($oldPath && is_file($oldPath)) {
                        @unlink($oldPath);
                    }
                }
            }
        } elseif (empty($id)) {
            return [
                'token'        => csrf_hash(),
                'success'      => false,
                'message'      => 'Background kop surat wajib diunggah.',
                '_http_status' => 422,
            ];
        }

        if (! empty($id)) {
            $this->penagihanRepo->updateKopsurat($id, $saveData);
            $msg = 'Kop surat berhasil diperbarui.';
        } else {
            $this->penagihanRepo->insertKopsurat($saveData);
            $msg = 'Kop surat berhasil ditambahkan.';
        }

        return [
            'token'   => csrf_hash(),
            'success' => true,
            'message' => $msg,
        ];
    }

    public function hapusKopsurat(int $id): array
    {
        if (function_exists('logged_in') && ! logged_in()) {
            return [
                'token'        => csrf_hash(),
                'success'      => false,
                'message'      => 'Silakan login terlebih dahulu.',
                '_http_status' => 401,
            ];
        }

        if (function_exists('in_groups') && ! in_groups(self::KOPSURAT_ALLOWED_GROUPS)) {
            return [
                'token'        => csrf_hash(),
                'success'      => false,
                'message'      => 'Anda tidak memiliki hak akses.',
                '_http_status' => 403,
            ];
        }

        if ($id <= 0) {
            return [
                'token'        => csrf_hash(),
                'success'      => false,
                'message'      => 'ID kop surat tidak valid.',
                '_http_status' => 422,
            ];
        }

        $usedCount = $this->penagihanRepo->countInvoicesUsingKopsurat($id);
        if ($usedCount > 0) {
            return [
                'token'        => csrf_hash(),
                'success'      => false,
                'message'      => "Kop surat tidak dapat dihapus karena telah digunakan pada {$usedCount} data tagihan.",
                '_http_status' => 422,
            ];
        }

        $kop = $this->penagihanRepo->findKopsuratObject($id);
        if ($kop) {
            if (! empty($kop->lokasi)) {
                $filePath = $this->fileAccessService->existingPath((string) $kop->lokasi);
                if ($filePath && is_file($filePath)) {
                    @unlink($filePath);
                }
            }
            $this->penagihanRepo->deleteKopsurat($id);
        }

        return [
            'token'   => csrf_hash(),
            'success' => true,
            'message' => 'Kop surat berhasil dihapus.',
        ];
    }

    public function streamDownloadPenagihan(string $noInv): bool
    {
        @ini_set('pcre.backtrack_limit', '5000000');

        if ($noInv === '') {
            return false;
        }

        $this->keuanganService->setTanggalKirim($noInv);

        $inv = $this->penagihanRepo->findInvoiceByNoInv($noInv);
        if (! $inv) {
            return false;
        }

        $idKavling = (int) ($inv->id_kavling ?? 0);
        $idKonsumen = (int) ($inv->id_konsumen ?? 0);

        if ($idKavling <= 0 || $idKonsumen <= 0) {
            $mkdt = $this->penagihanRepo->getMkdtRow((int) ($inv->id_mkdt ?? 0));
            if ($mkdt) {
                if ($idKavling <= 0 && ! empty($mkdt->id_kavling)) {
                    $idKavling = (int) $mkdt->id_kavling;
                    $this->penagihanRepo->updateInvoice($noInv, ['id_kavling' => $idKavling]);
                }
                if ($idKonsumen <= 0 && ! empty($mkdt->id_konsumen)) {
                    $idKonsumen = (int) $mkdt->id_konsumen;
                    $this->penagihanRepo->updateInvoice($noInv, ['id_konsumen' => $idKonsumen]);
                }
            }
        }

        $proyekInfo = $this->penagihanRepo->getProyekInfoForInvoice($idKavling);
        $data['proyek_info'] = $proyekInfo;

        $inv->ttd_img = null;
        if (! empty($inv->is_signed_direktur) && $proyekInfo && ! empty($proyekInfo->signature_path)) {
            $absolutePath = $this->fileAccessService->existingPath((string) $proyekInfo->signature_path);
            if ($absolutePath && file_exists($absolutePath)) {
                $ext = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));
                $mime = ($ext === 'png') ? 'image/png' : 'image/jpeg';
                $inv->ttd_img = 'data:' . $mime . ';base64,' . base64_encode((string) file_get_contents($absolutePath));
            }
        }

        $data['inv'] = $inv;

        $konsumen = $this->penagihanRepo->getKonsumenSummary($idKonsumen);
        $data['konsumen'] = $konsumen ?: (object) [
            'nama_konsumen'   => '-',
            'alamat_konsumen' => '-',
            'hp_konsumen'     => '-',
        ];

        $kavling = $this->penagihanRepo->getKavlingLocationSummary($idKavling);
        $data['kavling'] = $kavling ?: (object) [
            'nama_proyek' => '-',
            'nama_jalan'  => '-',
            'no_kavling'  => '-',
        ];

        $kop = $this->penagihanRepo->findKopsuratObject((int) ($inv->id_kopsurat ?? 0));
        $data['nama'] = $this->penagihanRepo->getKaryawanNameByUserId((int) ($inv->add_by ?? 0));

        $koplok = $kop ? (string) ($kop->lokasi ?? '') : '';
        $data['kop'] = $koplok;
        $data['kop2'] = $kop;

        $html = view('pdf/penagihan', $data);
        $filename = 'Tagihan_' . str_replace('/', '_', $noInv) . '.pdf';

        $bgUrl = base_url($koplok);
        if ($koplok !== '') {
            $localKop = $this->fileAccessService->existingPath($koplok);
            if ($localKop && file_exists($localKop)) {
                $ext = strtolower(pathinfo($localKop, PATHINFO_EXTENSION));
                $mime = ($ext === 'png') ? 'image/png' : 'image/jpeg';
                $bgUrl = 'data:' . $mime . ';base64,' . base64_encode((string) file_get_contents($localKop));
            }
        }

        $kopW = $kop->w ?? '21cm';
        $kopH = $kop->h ?? '29.7cm';
        $kopMt = $kop->mt ?? 0;
        $kopMb = $kop->mb ?? 0;
        $kopMl = $kop->ml ?? 0;
        $kopPml = $kop->pml ?? 15;
        $kopPmr = $kop->pmr ?? 15;
        $kopPmt = $kop->pmt ?? 30;
        $kopPmb = $kop->pmb ?? 25;
        $kopUkuran = $kop->ukuran ?? 'A4';

        $header = '
        <div style="
            background-image: url(' . $bgUrl . ');
            background-size: cover;
            width: ' . $kopW . ';
            height: ' . $kopH . ';
            position: fixed;
            top: ' . $kopMt . ';
            bottom: ' . $kopMb . ';
            left: ' . $kopMl . ';
            z-index:0;
            ">
        </div>';

        $mg = [$kopPml, $kopPmr, $kopPmt, $kopPmb];
        $paperFormat = ($kopUkuran === 'F4' || $kopUkuran === 'Folio') ? 'F4' : 'A4';

        $this->mpdf->generate($html, $filename, $header, $mg, $paperFormat);
        return true;
    }

    public function streamDoPrintPdf(array $input, int $actorId): void
    {
        $filename = date('y-m-d-H-i-s') . '-surat tagihan';

        $koplok = $this->penagihanRepo->getFirstKopsuratLokasi() ?? '';
        $base64 = '';
        if ($koplok !== '') {
            $resolved = $this->fileAccessService->existingPath($koplok);
            $pathToRead = ($resolved && is_file($resolved)) ? $resolved : (is_file($koplok) ? $koplok : null);
            if ($pathToRead !== null) {
                $kopContents = file_get_contents($pathToRead);
                if ($kopContents !== false) {
                    $mime = function_exists('mime_content_type') ? (string) (mime_content_type($pathToRead) ?: '') : '';
                    if ($mime === '' && class_exists(\finfo::class)) {
                        $finfo = new \finfo(FILEINFO_MIME_TYPE);
                        $mime = (string) ($finfo->file($pathToRead) ?: '');
                    }
                    if ($mime === '' || strpos($mime, 'image/') !== 0) {
                        $ext = strtolower(pathinfo($pathToRead, PATHINFO_EXTENSION));
                        $extMimeMap = [
                            'png'  => 'image/png',
                            'jpg'  => 'image/jpeg',
                            'jpeg' => 'image/jpeg',
                            'gif'  => 'image/gif',
                            'webp' => 'image/webp',
                        ];
                        $mime = $extMimeMap[$ext] ?? 'image/png';
                    }
                    $base64 = 'data:' . $mime . ';base64,' . base64_encode($kopContents);
                }
            }
        }

        $karyawan = $actorId > 0 ? $this->penagihanRepo->getKaryawanNameByUserId($actorId) : null;

        $rawTanggal = trim((string) ($input['tanggal_surat_tagihan'] ?? ''));
        $tanggalSuratTagihan = '-';
        if ($rawTanggal !== '' && $rawTanggal !== '-' && strpos($rawTanggal, '0000-00-00') !== 0) {
            $parsedDate = date_create($rawTanggal);
            if ($parsedDate !== false) {
                $tanggalSuratTagihan = date_format($parsedDate, 'Y-m-d');
            }
        }

        $data = [
            'table'                 => $input['table'] ?? null,
            'konsumen'              => $input['konsumen'] ?? null,
            'alamat'                => $input['alamat'] ?? null,
            'no_sruat'              => $input['no_sruat'] ?? null,
            'tanggal_surat_tagihan' => $tanggalSuratTagihan,
            'kop'                   => $base64,
            'nama'                  => $karyawan->nama_karyawan ?? '-',
        ];

        $dompdf = new Dompdf();
        $dompdf->loadHtml(view('pdf/tagihan_keuangan', $data));
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream($filename);
    }

    protected function notifyTagihanSignatureRequested(
        string $nomorSurat,
        ?int $idKavling,
        ?int $idKonsumen,
        int $idMkdt,
        mixed $tagihan,
        int $actorId
    ): void {
        try {
            $totalNominal = 0.0;
            $items = $tagihan;
            if (is_string($items)) {
                $items = json_decode(str_replace('&quot;', '"', $items), true);
            }
            if (is_array($items)) {
                foreach ($items as $item) {
                    $totalNominal += $this->cleanNumeric($item['nominal'] ?? 0);
                }
            }

            $namaKonsumen = '';
            if ($idMkdt > 0) {
                $mkdt = $this->penagihanRepo->getMkdtWithKonsumen($idMkdt);
                $namaKonsumen = trim((string) ($mkdt->nama_konsumen ?? ''));
            }

            $message = 'Surat tagihan ' . $nomorSurat;
            if ($namaKonsumen !== '') {
                $message .= ' a.n. ' . $namaKonsumen;
            }
            if ($totalNominal > 0) {
                $message .= ' (Rp ' . number_format($totalNominal, 0, ',', '.') . ')';
            }
            $message .= ' membutuhkan tanda tangan Direksi.';

            $this->notifikasiService->tambah_notif(
                '9',
                $message,
                $actorId,
                $idKavling,
                $idKonsumen,
                NotificationEvent::TAGIHAN_SIGNATURE_REQUESTED,
                null,
                'direksi/tagihan',
                NotificationEvent::TAGIHAN_SIGNATURE_REQUESTED
            );
        } catch (\Throwable $e) {
            log_message('error', '[PenagihanService::notifyTagihanSignatureRequested] {message}', ['message' => $e->getMessage()]);
        }
    }

    protected function buildDuplicateNomorSuratMessage(object $dup): string
    {
        $konsumenInfo = ! empty($dup->nama_konsumen) ? $dup->nama_konsumen : 'Konsumen lain';
        $kavlingParts = [];
        if (! empty($dup->nama_proyek)) {
            $kavlingParts[] = $dup->nama_proyek;
        }
        if (! empty($dup->nama_jalan)) {
            $kavlingParts[] = $dup->nama_jalan;
        }
        if (! empty($dup->no_kavling)) {
            $kavlingParts[] = 'No. ' . $dup->no_kavling;
        }
        $kavlingInfo = ! empty($kavlingParts) ? implode(' - ', $kavlingParts) : 'Kavling lain';

        return "Nomor Surat ini sudah digunakan untuk tagihan konsumen {$konsumenInfo} ({$kavlingInfo}). Silakan gunakan nomor surat yang lain.";
    }

    protected function cleanNumeric($value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        $cleaned = str_replace(',', '', trim((string) $value));
        return is_numeric($cleaned) ? (float) $cleaned : 0.0;
    }

    protected function formatTanggal(?string $tgl): string
    {
        if ($tgl === null || $tgl === '' || $tgl === '0000-00-00') {
            return '-';
        }

        $date = date_create($tgl);
        return $date ? date_format($date, 'd-M-Y') : '-';
    }
}

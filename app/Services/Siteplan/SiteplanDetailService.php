<?php

namespace App\Services\Siteplan;

use App\Repositories\CashOutRepository;
use App\Repositories\Keuangan\Cashout\CashoutKavlingRepo;
use App\Repositories\Siteplan\SiteplanRepository;
use App\Services\FileAccessService;
use App\Services\PencairanAkadService;

/**
 * Service khusus agregasi detail kavling untuk modal/halaman detail Siteplan.
 */
class SiteplanDetailService
{
    protected SiteplanRepository $siteplanRepository;
    protected CashOutRepository $cashOutRepository;
    protected CashoutKavlingRepo $cashoutKavlingRepo;
    protected PencairanAkadService $pencairanAkadService;
    protected FileAccessService $fileAccessService;

    public function __construct(
        ?SiteplanRepository $siteplanRepository = null,
        ?CashOutRepository $cashOutRepository = null,
        ?CashoutKavlingRepo $cashoutKavlingRepo = null,
        ?PencairanAkadService $pencairanAkadService = null,
        ?FileAccessService $fileAccessService = null
    ) {
        $this->siteplanRepository   = $siteplanRepository ?? new SiteplanRepository();
        $this->cashOutRepository    = $cashOutRepository ?? new CashOutRepository();
        $this->cashoutKavlingRepo   = $cashoutKavlingRepo ?? new CashoutKavlingRepo();
        $this->pencairanAkadService = $pencairanAkadService ?? new PencairanAkadService();
        $this->fileAccessService    = $fileAccessService ?? new FileAccessService();
    }

    /**
     * Mengambil seluruh data detail kavling (100% kompatibel dengan kontrak Siteplan::get_detail).
     */
    public function getDetail(
        int $idKavling,
        ?int $idMkdt = null,
        ?int $idHargaJual = null,
        ?int $idLegal = null,
        ?int $userId = null
    ): array {
        $idMkdtInt = (int) ($idMkdt ?? 0);
        $idLegalInt = (int) ($idLegal ?? 0);

        $d = [];
        $d['token'] = csrf_hash();

        $mkdt = $this->siteplanRepository->getDetailMkdt($idMkdtInt);
        if ($mkdt) {
            $this->enrichMkdtFiles($mkdt, $userId);
        }
        $d['mkdt'] = $mkdt;

        $kavling = $this->siteplanRepository->getDetailKavling($idKavling);
        if ($kavling && ! empty($kavling->perintah_bangun_file)) {
            $this->attachAccessAndSignedUrl(
                $kavling,
                'kavling_perintah_bangun',
                (int) $kavling->id_kavling,
                'perintah_bangun',
                false,
                $userId
            );
        }
        $d['kavling'] = $kavling;

        $tipeDetail = null;
        if ($kavling && ! empty($kavling->id_tipe)) {
            $tipeDetail = $this->siteplanRepository->getDetailTipe((int) $kavling->id_tipe);
            if ($tipeDetail) {
                $this->enrichTipeGambar($tipeDetail, $userId);
            }
        }
        $d['tipe_detail'] = $tipeDetail;

        $effectiveHargaJualId = (int) ($idHargaJual ?? 0);
        if ($effectiveHargaJualId <= 0 && $kavling && ! empty($kavling->harga_akhir)) {
            $effectiveHargaJualId = (int) $kavling->harga_akhir;
        }
        $d['pricelist'] = $effectiveHargaJualId > 0
            ? $this->siteplanRepository->getDetailPricelist($effectiveHargaJualId)
            : null;

        $tg         = $this->siteplanRepository->getDetailKeuanganByMkdt($idMkdtInt);
        $d['query'] = $this->siteplanRepository->getLastQueryString();

        $d['tagihan_jatuh_tempo'] = $this->buildTagihanJatuhTempo($tg);
        $tagihanTotals            = $this->calculateTagihanTotals($tg, $mkdt);

        $d['total_um']            = $tagihanTotals['total_um'];
        $d['total_um_ll']         = $tagihanTotals['total_um_ll'];
        $d['total_bb']            = $tagihanTotals['total_bb'];
        $d['total_tagihan_semua'] = $tagihanTotals['total_tagihan_semua'];

        $sb       = $this->siteplanRepository->getDetailLogPembayaranByMkdt($idMkdtInt);
        $sbDetail = $this->siteplanRepository->getDetailLogPembayaranDetailByMkdt($idMkdtInt);

        $paymentAggregates    = $this->calculatePembayaranTotals($idMkdtInt, $sb, $sbDetail, $tagihanTotals['total_um']);
        $breakdownByPaymentId = $paymentAggregates['breakdown_by_payment_id'];

        $d['sb_um']             = $paymentAggregates['sb_um'];
        $d['sb_um_ll']          = $paymentAggregates['sb_um_ll'];
        $d['sb_bb']             = $paymentAggregates['sb_bb'];
        $d['sudah_bayar_semua'] = $paymentAggregates['sudah_bayar_semua'];

        $d['ku']       = $this->siteplanRepository->getDetailLastKeuanganUserByMkdt($idMkdtInt);
        $d['legal']    = $this->siteplanRepository->getDetailLegal($idLegalInt);
        $d['produksi'] = $this->siteplanRepository->getDetailProduksi($idKavling);

        $d['files'] = $idKavling > 0
            ? $this->enrichFileRows(
                $this->siteplanRepository->getDetailFileProduksi($idKavling),
                'file_produksi',
                $userId
            )
            : [];

        $d['file_pph'] = $this->enrichFileRows(
            $this->siteplanRepository->getDetailFilePph($idKavling),
            'file_upload',
            $userId
        );

        $d['file_ppn'] = $this->enrichFileRows(
            $this->siteplanRepository->getDetailFilePpn($idKavling),
            'file_upload',
            $userId
        );

        $d['cashout']       = $this->cashOutRepository->getRiwayatBayarCashOutByIDKavling($idKavling);
        $d['hutang_subkon'] = $this->siteplanRepository->getDetailHutangSubkon($idKavling);

        $d['pencairan_akad'] = $this->pencairanAkadService->getData($idMkdtInt, $idKavling);
        $d['finance_flow']   = $this->buildFinanceFlow($idKavling, $idMkdtInt, $mkdt, $breakdownByPaymentId);

        $d['bayar_produksi'] = $this->siteplanRepository->getDetailBayarProduksi($idKavling);
        $d['si']             = $this->enrichFileRows(
            $this->siteplanRepository->getDetailSi($idKavling),
            'si',
            $userId
        );

        $d['status'] = true;

        return $d;
    }

    private function enrichMkdtFiles(object $mkdt, ?int $userId): void
    {
        $mkdtFiles = [
            ['field' => 'surat_batal', 'source' => 'mkdt_surat_batal', 'id' => (int) ($mkdt->id_mkdt ?? 0), 'prefix' => 'surat_batal'],
            ['field' => 'sp3k_file', 'source' => 'mkdt_sp3k', 'id' => (int) ($mkdt->id_mkdt ?? 0), 'prefix' => 'sp3k'],
            ['field' => 'bast_file', 'source' => 'mkdt_bast', 'id' => (int) ($mkdt->id_mkdt ?? 0), 'prefix' => 'bast'],
            ['field' => 'file_ktp', 'source' => 'konsumen_ktp', 'id' => (int) ($mkdt->id_konsumen ?? 0), 'prefix' => 'ktp'],
            ['field' => 'file_npwp', 'source' => 'konsumen_npwp', 'id' => (int) ($mkdt->id_konsumen ?? 0), 'prefix' => 'npwp'],
            ['field' => 'file_data_diri', 'source' => 'konsumen_data', 'id' => (int) ($mkdt->id_konsumen ?? 0), 'prefix' => 'data_diri'],
        ];

        foreach ($mkdtFiles as $cfg) {
            $field = $cfg['field'];
            if (! empty($mkdt->$field) && $cfg['id'] > 0) {
                $this->attachAccessAndSignedUrl($mkdt, $cfg['source'], $cfg['id'], $cfg['prefix'], false, $userId);
            }
        }
    }

    private function enrichTipeGambar(object $tipeDetail, ?int $userId): void
    {
        $tipeDetail->gambar_tipe_access_url         = null;
        $tipeDetail->gambar_tipe_download_url       = null;
        $tipeDetail->gambar_tipe_signed_url         = null;
        $tipeDetail->gambar_tipe_signed_download_url = null;
        $tipeDetail->gambar_denah_access_url         = null;
        $tipeDetail->gambar_denah_download_url       = null;
        $tipeDetail->gambar_denah_signed_url         = null;
        $tipeDetail->gambar_denah_signed_download_url = null;

        if (! empty($tipeDetail->id_gambar_tipe)) {
            $this->attachAccessAndSignedUrl(
                $tipeDetail,
                'gambar_kerja',
                (int) $tipeDetail->id_gambar_tipe,
                'gambar_tipe',
                true,
                $userId
            );
        }

        if (! empty($tipeDetail->id_gambar_denah)) {
            $this->attachAccessAndSignedUrl(
                $tipeDetail,
                'gambar_kerja',
                (int) $tipeDetail->id_gambar_denah,
                'gambar_denah',
                true,
                $userId
            );
        }
    }

    private function attachAccessAndSignedUrl(
        object $target,
        string $source,
        int $id,
        string $prefix,
        bool $includeDownload,
        ?int $userId
    ): void {
        if ($id <= 0) {
            return;
        }

        $accessKey = $prefix . '_access_url';
        $signedKey = $prefix . '_signed_url';

        $target->$accessKey = $this->fileAccessService->accessUrl($source, $id);
        $target->$signedKey = $this->fileAccessService->signedUrl($source, $id, 3600, null, false, $userId);

        if ($includeDownload) {
            $downloadKey       = $prefix . '_download_url';
            $signedDownloadKey = $prefix . '_signed_download_url';

            $target->$downloadKey       = $this->fileAccessService->accessUrl($source, $id, true);
            $target->$signedDownloadKey = $this->fileAccessService->signedUrl($source, $id, 3600, null, true, $userId);
        }
    }

    /**
     * @param array<int, object> $rows
     * @return array<int, object>
     */
    private function enrichFileRows(array $rows, string $source, ?int $userId): array
    {
        $rows = $this->fileAccessService->addAccessUrlsToRows($rows, $source);

        foreach ($rows as $row) {
            if (empty($row->id)) {
                continue;
            }

            $fileId = (int) $row->id;
            $row->signed_url          = $this->fileAccessService->signedUrl($source, $fileId, 3600, null, false, $userId);
            $row->signed_download_url = $this->fileAccessService->signedUrl($source, $fileId, 3600, null, true, $userId);

            if ($source === 'file_produksi') {
                $row->signed_thumbnail_url = $this->fileAccessService->signedThumbnailUrl($source, $fileId, 3600, $userId);
            }
        }

        return $rows;
    }

    /**
     * @param array<int, object> $tg
     * @return array<int, array{berita_acara: string, jatuh_tempo_tgl: string, nominal: float}>
     */
    private function buildTagihanJatuhTempo(array $tg): array
    {
        $today = date('Y-m-d');

        $dueRows = array_values(array_filter($tg, static function (object $row) use ($today): bool {
            if ((int) ($row->sudah_dibayar ?? 0) !== 0 || (int) ($row->is_void ?? 0) !== 0) {
                return false;
            }

            $dueDate = trim((string) ($row->jatuh_tempo_tgl ?? ''));

            return $dueDate !== '' && $dueDate !== '0000-00-00' && $dueDate <= $today;
        }));

        usort($dueRows, static function (object $a, object $b): int {
            return strcmp((string) ($a->jatuh_tempo_tgl ?? ''), (string) ($b->jatuh_tempo_tgl ?? ''));
        });

        return array_map(static function (object $row): array {
            return [
                'berita_acara'    => (string) ($row->berita_acara ?? '-'),
                'jatuh_tempo_tgl' => (string) $row->jatuh_tempo_tgl,
                'nominal'         => (float) ($row->nominal ?? 0),
            ];
        }, $dueRows);
    }

    /**
     * @param array<int, object> $tg
     * @return array{total_um: float, total_um_ll: float, total_bb: float, total_tagihan_semua: float}
     */
    private function calculateTagihanTotals(array $tg, ?object $mkdt): array
    {
        $tgUm       = 0.0;
        $tgUmLl     = 0.0;
        $tgBb       = 0.0;
        $totalSemua = 0.0;

        foreach ($tg as $row) {
            $nominal     = (float) ($row->nominal ?? 0);
            $totalSemua += $nominal;

            if (($row->kategori ?? '') === 'UM') {
                if (($row->berita_acara ?? '') === 'Uang Muka') {
                    $tgUm += $nominal;
                } else {
                    $tgUmLl += $nominal;
                }
            } elseif (($row->kategori ?? '') === 'BB') {
                if (($row->berita_acara ?? '') !== 'Turun KPR') {
                    $tgBb += $nominal;
                }
            }
        }

        if ($mkdt) {
            $hargaKpr    = (float) ($mkdt->harga_kpr ?? 0);
            $hargaKprAcc = (float) ($mkdt->harga_kpr_acc ?? 0);
            $turunKpr    = (float) ($mkdt->harga_penambahan_um ?? 0);

            if ($turunKpr <= 0 && $hargaKprAcc > 0 && $hargaKprAcc < $hargaKpr) {
                $turunKpr = $hargaKpr - $hargaKprAcc;
            }

            $tgUmLl = (float) ($mkdt->harga_administrasi ?? 0) + $turunKpr;
        }

        return [
            'total_um'            => $tgUm,
            'total_um_ll'         => $tgUmLl,
            'total_bb'            => $tgBb,
            'total_tagihan_semua' => $totalSemua,
        ];
    }

    /**
     * @param array<int, object> $sb
     * @param array<int, object> $sbDetail
     * @return array{
     *     sb_um: float,
     *     sb_um_ll: float,
     *     sb_bb: float,
     *     sudah_bayar_semua: float,
     *     breakdown_by_payment_id: array<int, array<int, array{label: string, nominal: float}>>
     * }
     */
    private function calculatePembayaranTotals(int $idMkdt, array $sb, array $sbDetail, float $tgUm): array
    {
        $sbUm   = 0.0;
        $sbUmLl = 0.0;
        $sbBb   = 0.0;

        $detailPaymentIds     = [];
        $breakdownByPaymentId = [];

        foreach ($sbDetail as $v) {
            $idPembayaran = (int) ($v->id_pembayaran ?? 0);
            $detailPaymentIds[$idPembayaran] = true;

            $itemId    = (int) ($v->id_keuangan_item_list ?? 0);
            $item      = strtolower(trim((string) ($v->item ?? '')));
            $itemLabel = trim((string) ($v->item ?? ''));
            $kategori  = strtoupper(trim((string) ($v->kategori ?? '')));
            $nominal   = (float) ($v->nominal ?? 0);

            if ($kategori === 'BO' && (int) ($v->booking_is_installment ?? 0) === 1) {
                $itemLabel = 'Uang Muka';
            }

            if ($itemLabel === '') {
                $itemLabel = $kategori !== '' ? $kategori : 'Pembayaran';
            }

            $breakdownByPaymentId[$idPembayaran][] = [
                'label'   => $itemLabel,
                'nominal' => $nominal,
            ];

            if ($kategori === 'BO' || $itemId === 1 || str_contains($item, 'booking')) {
                continue;
            }

            if ($itemId === 2 || $kategori === 'UM' || str_contains($item, 'uang muka')) {
                $sbUm += $nominal;
                continue;
            }

            if (
                $itemId === 3
                || $kategori === 'ADM'
                || str_contains($item, 'administrasi')
                || $itemId === 9
                || str_contains($item, 'turun kpr')
            ) {
                $sbUmLl += $nominal;
                continue;
            }

            if ($kategori === 'BB') {
                $sbBb += $nominal;
            }
        }

        foreach ($sb as $v) {
            if (isset($detailPaymentIds[(int) ($v->id_pembayaran ?? 0)]) || ($v->payment_type ?? '') == 'Booking') {
                continue;
            }

            $pt      = array_map('trim', explode(';', (string) ($v->payment_type ?? '')));
            $nominal = (float) ($v->nominal ?? 0);

            if (in_array('Uang Muka', $pt, true)) {
                $sbUm += $nominal;
            } elseif (in_array('Biaya Administrasi', $pt, true) || in_array('Turun KPR', $pt, true)) {
                $sbUmLl += $nominal;
            } elseif (
                in_array('BPHTB', $pt, true)
                || in_array('PPN', $pt, true)
                || in_array('Biaya Proses', $pt, true)
                || in_array('Biaya Kavling Strategis', $pt, true)
                || in_array('Biaya Kelebihan Tanah', $pt, true)
            ) {
                $sbBb += $nominal;
            } else {
                $sbUmLl += $nominal;
            }
        }

        if (($sbUm + $sbUmLl + $sbBb) <= 0) {
            $summary = $this->siteplanRepository->getDetailPaymentSummaryByMkdt($idMkdt);

            if ($summary && (((float) ($summary->total_um ?? 0) + (float) ($summary->total_adm ?? 0) + (float) ($summary->total_bb ?? 0)) > 0)) {
                $sbUm   = (float) $summary->total_um;
                $sbUmLl = (float) $summary->total_adm;
                $sbBb   = (float) $summary->total_bb;
            }
        }

        if (count($sbDetail) === 0) {
            $sisa   = $sbUm > $tgUm ? $sbUm - $tgUm : 0.0;
            $sbUmLl = $sisa > 0 ? $sbUmLl + $sisa : $sbUmLl;
            $sbUm   = $sisa > 0 ? $tgUm : $sbUm;
        }

        $sudahBayarSemua = 0.0;
        foreach ($sb as $v) {
            if (($v->payment_type ?? '') !== 'Booking') {
                $sudahBayarSemua += (float) ($v->nominal ?? 0);
            }
        }

        return [
            'sb_um'                   => $sbUm,
            'sb_um_ll'                => $sbUmLl,
            'sb_bb'                   => $sbBb,
            'sudah_bayar_semua'       => $sudahBayarSemua,
            'breakdown_by_payment_id' => $breakdownByPaymentId,
        ];
    }

    /**
     * @param array<int, array<int, array{label: string, nominal: float}>> $breakdownByPaymentId
     * @return array<string, mixed>
     */
    private function buildFinanceFlow(
        int $idKavling,
        int $idMkdt,
        ?object $mkdt,
        array $breakdownByPaymentId
    ): array {
        $incomeRows = ($idMkdt > 0 || $idKavling > 0)
            ? $this->siteplanRepository->getDetailFinanceLedgerIncome(
                $idMkdt > 0 ? $idMkdt : null,
                $idKavling > 0 ? $idKavling : null
            )
            : [];

        $incomeTotal = 0.0;
        foreach ($incomeRows as $row) {
            if (($row->source_type ?? '') === 'log_pembayaran') {
                $breakdown = $breakdownByPaymentId[(int) ($row->source_id ?? 0)] ?? [];
                if ($breakdown !== []) {
                    $row->breakdown = $breakdown;
                }
            }

            unset($row->source_id);
            $incomeTotal += (float) ($row->nominal ?? 0);
        }

        $expenseRows  = $this->cashoutKavlingRepo->getCashoutRowsByKavling($idKavling);
        $expenseTotal = 0.0;
        foreach ($expenseRows as $row) {
            $expenseTotal += (float) ($row->nominal ?? 0);
        }

        $bankRetensiPending    = $this->siteplanRepository->getDetailBankRetensiPending($idKavling);
        $danaJaminanSummary    = $this->siteplanRepository->getDetailDanaAkadSummary($idKavling);
        $danaJaminanConfigured = (float) ($danaJaminanSummary['total_nominal'] ?? 0);
        $danaJaminanPending    = (float) ($danaJaminanSummary['total_pending'] ?? 0);

        $bankRetensiCounted  = $danaJaminanConfigured > 0 ? 0.0 : $bankRetensiPending;
        $retensiPendingTotal = $danaJaminanPending + $bankRetensiCounted;
        $nilaiAkadEstimasi   = $this->resolveNilaiAkadEstimasi($mkdt);

        return [
            'income_total'                 => $incomeTotal,
            'income_count'                 => count($incomeRows),
            'income_rows'                  => $incomeRows,
            'expense_total'                => $expenseTotal,
            'expense_count'                => count($expenseRows),
            'expense_rows'                 => $expenseRows,
            'balance'                      => $incomeTotal - $expenseTotal,
            'margin_cash'                  => $incomeTotal - $expenseTotal,
            'margin_estimasi'              => $nilaiAkadEstimasi - $expenseTotal,
            'nilai_akad_estimasi'          => $nilaiAkadEstimasi,
            'retensi_pending_total'        => $retensiPendingTotal,
            'retensi_dana_jaminan_pending' => $danaJaminanPending,
            'retensi_bank_pending'         => $bankRetensiPending,
            'retensi_bank_counted'         => $bankRetensiCounted,
        ];
    }

    private function resolveNilaiAkadEstimasi(?object $mkdt): float
    {
        if (! $mkdt) {
            return 0.0;
        }

        foreach (['harga_jual_net', 'harga_jual', 'harga_kpr_acc', 'harga_kpr'] as $field) {
            $value = (float) ($mkdt->$field ?? 0);
            if ($value > 0) {
                return $value;
            }
        }

        return 0.0;
    }
}

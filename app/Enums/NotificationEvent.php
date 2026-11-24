<?php

namespace App\Enums;

final class NotificationEvent
{
    // ── Siteplan & Kavling ──
    const KAVLING_ADDED        = 'kavling_added';
    const KAVLING_UPDATED      = 'kavling_updated';
    const OTHERS_ADDED         = 'others_added';
    const OTHERS_UPDATED       = 'others_updated';
    const TURUN_PEMBANGUNAN    = 'turun_pembangunan';
    const REQUEST_KAVLING      = 'request_kavling';

    // ── Konsumen & Transaksi (MKDT) ──
    const BOOKING_BARU         = 'booking_baru';
    const DATA_KONSUMEN_UPDATE = 'data_konsumen_update';
    const BATAL_BOOKING        = 'batal_booking';
    const KONSUMEN_BARU        = 'konsumen_baru';
    const WAWANCARA            = 'wawancara';
    const PERINTAH_BANGUN      = 'perintah_bangun';
    const AKAD                 = 'akad';

    // ── Keuangan ──
    const TAGIHAN_KPR          = 'tagihan_kpr';
    const CASHOUT_SUBKON       = 'cashout_subkon';
    const PENCAIRAN_HASIL_AKAD = 'pencairan_hasil_akad';
    const REKONSILIASI_KEUANGAN = 'rekonsiliasi_keuangan';

    // ── Pajak ──
    const PAJAK_PPH            = 'pajak_pph';
    const PAJAK_PPN            = 'pajak_ppn';

    // ── Produksi ──
    const PROGRESS_PRODUKSI    = 'progress_produksi';

    // ── Direksi ──
    const DISKRESI_HARGA       = 'diskresi_harga';

    // ── Master Data ──
    const MASTER_PROYEK        = 'master_proyek';
    const MASTER_CLUSTER       = 'master_cluster';
    const MASTER_JALAN         = 'master_jalan';
    const MASTER_TIPE          = 'master_tipe';

    // ── Tiket Masalah ──
    const TIKET_MASALAH_BARU   = 'tiket_masalah_baru';
    const TIKET_MASALAH_ASSIGN = 'tiket_masalah_assign';
    const TIKET_MASALAH_UPDATE = 'tiket_masalah_update';

    // ── Referral / MGM ──
    const MGM_REFERRAL_CREATED = 'mgm_referral_created';
    const MGM_SPP_SUBMITTED    = 'mgm_spp_submitted';
    const MGM_SPP_CAIR         = 'mgm_spp_cair';

    // ── Bon Permintaan Barang ──
    const BPB_SIGNATURE_REQUESTED = 'bpb_signature_requested';
    const BPB_SIGNATURE_COMPLETED = 'bpb_signature_completed';
    const BPB_REJECTED            = 'bpb_rejected';
    const BPB_APPROVED            = 'bpb_approved';
    const BPB_STATUS_CHANGED      = 'bpb_status_changed';
    public const BPB_EXECUTION_REQUESTED = 'bpb_execution_requested';

    // ── Legal ──
    public const LEGAL_DOKUMEN_UPDATED = 'legal_dokumen_updated';

    // ── Direksi / Tagihan ──
    public const TAGIHAN_SIGNATURE_REQUESTED = 'tagihan_signature_requested';
    public const TAGIHAN_SIGNATURE_RESPONDED = 'tagihan_signature_responded';

    // ── Dana Jaminan (Dajam) ──
    public const DAJAM_PENGAJUAN_CREATED   = 'dajam_pengajuan_created';
    public const DAJAM_PENCAIRAN_COMPLETED = 'dajam_pencairan_completed';

    // ── Fee Sales ──
    public const FEE_SALES_PENGAJUAN_CREATED  = 'fee_sales_pengajuan_created';
    public const FEE_SALES_PENGAJUAN_REJECTED = 'fee_sales_pengajuan_rejected';
    public const FEE_SALES_TERMIN_GENERATED   = 'fee_sales_termin_generated';
    public const FEE_SALES_HARGA_NET_UPDATED  = 'fee_sales_harga_net_updated';
    public const FEE_SALES_TTD_SIGNED         = 'fee_sales_ttd_signed';
    public const FEE_SALES_PENCAIRAN          = 'fee_sales_pencairan';

    public static function all(): array
    {
        return array_values((new \ReflectionClass(self::class))->getConstants());
    }

    public static function legacyTypeMap(): array
    {
        return [
            'fee_sales_pengajuan'          => self::FEE_SALES_PENGAJUAN_CREATED,
            'fee_sales_pengajuan_rejected' => self::FEE_SALES_PENGAJUAN_REJECTED,
            'fee_sales_reject'             => self::FEE_SALES_PENGAJUAN_REJECTED,
            'fee_sales_rejection'          => self::FEE_SALES_PENGAJUAN_REJECTED,
            'fee_sales_termin'             => self::FEE_SALES_TERMIN_GENERATED,
            'fee_sales_harga_net'          => self::FEE_SALES_HARGA_NET_UPDATED,
            'fee_sales_ttd'                => self::FEE_SALES_TTD_SIGNED,
            'fee_sales_pencairan'          => self::FEE_SALES_PENCAIRAN,
            'fee_sales_cair'               => self::FEE_SALES_PENCAIRAN,
        ];
    }

    public static function fromLegacyType(?string $type): ?string
    {
        if ($type === null) {
            return null;
        }

        $type = strtolower(trim($type));
        if ($type === '') {
            return null;
        }

        $map = self::legacyTypeMap();

        return $map[$type] ?? $type;
    }
}


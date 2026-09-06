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
}

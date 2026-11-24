<?php

namespace App\Database\Seeds;

use App\Enums\NotificationEvent;
use CodeIgniter\Database\Seeder;

class NotificationEventTypeSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        $events = [
            // Kategori: Siteplan
            ['kavling_added', 'siteplan', 'Penambahan Kavling', 'Saat kavling baru ditambahkan ke siteplan', 1, 1, 1, 0, '4;5;6;7;9', 1],
            ['kavling_updated', 'siteplan', 'Perubahan Data Kavling', 'Saat ada update luas, harga, atau atribut teknis kavling', 1, 1, 1, 0, '4;5;6;7;9', 2],
            ['others_added', 'siteplan', 'Penambahan Fasum/Lainnya', 'Saat fasilitas umum atau lainnya ditambahkan', 1, 1, 1, 0, '4;5;6;7;9', 3],
            ['others_updated', 'siteplan', 'Perubahan Fasum/Lainnya', 'Saat fasilitas umum atau lainnya diubah', 1, 1, 1, 0, '4;5;6;7;9', 4],
            ['turun_pembangunan', 'siteplan', 'Turun Pembangunan', 'Saat kavling dipindah status turun pembangunan', 1, 1, 1, 0, '4;9', 5],
            ['request_kavling', 'siteplan', 'Request Kavling Baru', 'Saat ada pengajuan kavling baru ke tim Planning', 1, 1, 1, 0, '6', 6],

            // Kategori: Konsumen
            ['booking_baru', 'konsumen', 'Booking Kavling Baru', 'Saat ada booking kavling baru', 1, 1, 1, 0, '3;4;9', 10],
            ['data_konsumen_update', 'konsumen', 'Perubahan Data Konsumen', 'Saat informasi data pembeli diperbarui', 1, 1, 1, 0, '3;4;9', 11],
            ['batal_booking', 'konsumen', 'Pembatalan Booking', 'Saat konsumen membatalkan pembelian', 1, 1, 1, 0, '3;4;9', 12],
            ['konsumen_baru', 'konsumen', 'Konsumen Baru', 'Saat konsumen baru diinput (tanpa kavling)', 1, 1, 1, 0, '4;9', 13],
            ['wawancara', 'konsumen', 'Wawancara Konsumen', 'Saat ada jadwal wawancara untuk KPR', 1, 1, 1, 0, '4;9', 14],
            ['perintah_bangun', 'konsumen', 'Terbit Perintah Bangun', 'Surat SPK terbit untuk mulai dibangun', 1, 1, 1, 0, '7;4;9', 15],
            ['akad', 'konsumen', 'Akad Kredit', 'Pemberitahuan telah/akan akad kredit', 1, 1, 1, 0, '3;5;8;4;9', 16],

            // Kategori: Keuangan
            ['tagihan_kpr', 'keuangan', 'Tagihan Turun KPR', 'KPR telah cair/acc dari bank', 1, 1, 1, 0, '3;4;9', 20],
            ['cashout_subkon', 'keuangan', 'Cashout Subkontraktor', 'Pemberitahuan / jatuh tempo pencairan ke subkon', 1, 1, 1, 0, '7;3', 21],
            ['pencairan_hasil_akad', 'keuangan', 'Pencairan Hasil Akad', 'Pemberitahuan pencairan hasil akad ke bank', 1, 1, 1, 0, '3;4;9', 22],
            ['rekonsiliasi_keuangan', 'keuangan', 'Pembayaran Perlu Rekonsiliasi', 'Saat ada pembayaran dengan alokasi yang tidak sesuai list tagihan MKDT', 1, 1, 1, 0, '4', 23],
            [NotificationEvent::DAJAM_PENGAJUAN_CREATED, 'keuangan', 'Pengajuan Dana Jaminan', 'Saat ada pengajuan pencairan dana jaminan', 1, 1, 1, 0, '3;4', 24],
            [NotificationEvent::DAJAM_PENCAIRAN_COMPLETED, 'keuangan', 'Pencairan Dana Jaminan Selesai', 'Saat pencairan dana jaminan telah disetujui/dicairkan', 1, 1, 1, 0, '3;4;9', 25],

            // Kategori: Pajak
            ['pajak_pph', 'pajak', 'Pembayaran PPH', 'Pemberitahuan pelunasan atau proses PPH', 1, 1, 1, 0, '3;5;10', 30],
            ['pajak_ppn', 'pajak', 'Pembayaran PPN', 'Pemberitahuan pelunasan atau proses PPN', 1, 1, 1, 0, '3;5;10', 31],

            // Kategori: Legal
            [NotificationEvent::LEGAL_DOKUMEN_UPDATED, 'legal', 'Update Dokumen Legal', 'Saat dokumen atau status legal kavling diperbarui', 1, 1, 1, 0, '3;4;5;10', 35],

            // Kategori: Produksi
            ['progress_produksi', 'produksi', 'Update Progress Produksi', 'Saat status ST bangunan (misal: 25%, 50%) naik', 1, 1, 1, 0, '3;4;7;9', 40],

            // Kategori: Direksi
            ['diskresi_harga', 'direksi', 'Diskresi Harga Jual', 'Approval potong harga / diskresi oleh direksi', 1, 1, 1, 0, '3;4;9', 50],
            [NotificationEvent::TAGIHAN_SIGNATURE_REQUESTED, 'direksi', 'Permintaan Tanda Tangan Tagihan', 'Saat surat tagihan membutuhkan tanda tangan Direksi', 1, 1, 1, 1, '9', 51],
            [NotificationEvent::TAGIHAN_SIGNATURE_RESPONDED, 'direksi', 'Respon Tanda Tangan Tagihan', 'Saat Direksi menyetujui atau menolak tanda tangan surat tagihan', 1, 1, 1, 0, '3;9', 52],

            // Kategori: Master Data
            ['master_proyek', 'master_data', 'Master Proyek', 'Info jika master data proyek diubah', 1, 0, 0, 0, '6', 60],
            ['master_cluster', 'master_data', 'Master Cluster', 'Info jika master data cluster diubah', 1, 0, 0, 0, '6', 61],
            ['master_jalan', 'master_data', 'Master Jalan', 'Info jika master data jalan diubah', 1, 0, 0, 0, '6', 62],
            ['master_tipe', 'master_data', 'Master Tipe', 'Info jika master data tipe bangunan diubah', 1, 0, 0, 0, '6', 63],

            // Kategori: Tiket Masalah
            ['tiket_masalah_baru', 'tiket_masalah', 'Tiket Masalah Baru', 'Saat ada komplain atau masalah baru tercatat', 1, 1, 1, 1, null, 70],
            ['tiket_masalah_assign', 'tiket_masalah', 'Ditugaskan ke Tiket', 'Pemberitahuan Anda di-assign ke masalah tertentu', 1, 1, 1, 1, null, 71],
            ['tiket_masalah_update', 'tiket_masalah', 'Update Progress Tiket', 'Setiap kali ada tanggapan atau status tiket diubah', 1, 1, 1, 0, null, 72],

            // Kategori: Referral
            ['mgm_referral_created', 'referral', 'Referral Baru (MGM)', 'Pendaftaran agen / referral baru', 1, 1, 1, 0, '8;3', 80],
            ['mgm_spp_submitted', 'referral', 'Pengajuan SPP Referral', 'Request pembayaran bonus referral diajukan ke finance', 1, 1, 1, 0, '3', 81],
            ['mgm_spp_cair', 'referral', 'Pencairan Bonus Referral', 'Bonus referral berhasil dibayarkan', 1, 1, 1, 0, '8', 82],

            // Kategori: Bon Permintaan Barang
            ['bpb_signature_requested', 'bpb', 'Permintaan Tanda Tangan BPB', 'Saat pengguna perlu menandatangani BPB', 1, 1, 1, 1, null, 90],
            ['bpb_signature_completed', 'bpb', 'Tanda Tangan BPB Selesai', 'Saat salah satu tahap tanda tangan BPB selesai', 1, 0, 0, 0, null, 91],
            ['bpb_rejected', 'bpb', 'BPB Ditolak', 'Saat BPB ditolak oleh pemberi paraf atau Mengetahui', 1, 1, 1, 0, null, 92],
            ['bpb_approved', 'bpb', 'BPB Disetujui', 'Saat seluruh approval BPB selesai', 1, 1, 1, 0, null, 93],
            ['bpb_status_changed', 'bpb', 'Perubahan Status BPB', 'Saat status operasional BPB berubah', 1, 0, 0, 0, null, 94],
            [NotificationEvent::BPB_EXECUTION_REQUESTED, 'bpb', 'Permintaan Eksekusi BPB', 'Saat BPB yang disetujui menunggu eksekusi Keuangan/Umum', 1, 1, 1, 0, '2;3', 95],

            // Kategori: Fee Sales
            [NotificationEvent::FEE_SALES_PENGAJUAN_CREATED, 'Fee Sales', 'Pengajuan Fee Sales Baru', 'Notifikasi saat ada pengajuan pencairan fee sales baru.', 1, 1, 1, 0, '3;4', 100],
            [NotificationEvent::FEE_SALES_TERMIN_GENERATED, 'Fee Sales', 'Generate Termin Fee Sales', 'Notifikasi saat termin pembayaran fee sales di-generate pada kavling.', 1, 1, 1, 0, '3;4', 101],
            [NotificationEvent::FEE_SALES_HARGA_NET_UPDATED, 'Fee Sales', 'Perubahan Harga Jual Net / AJB (Fee Sales)', 'Notifikasi saat nominal Harga Jual Net atau Harga Net Sesuai AJB mengalami perubahan.', 1, 1, 1, 0, '3;4', 102],
            [NotificationEvent::FEE_SALES_TTD_SIGNED, 'Fee Sales', 'Tanda Tangan Pengajuan Fee Sales (MKDT / Keuangan)', 'Notifikasi saat pengajuan fee sales ditandatangani oleh pihak MKDT atau Keuangan.', 1, 1, 1, 0, '3;4', 103],
            [NotificationEvent::FEE_SALES_PENCAIRAN, 'Fee Sales', 'Pencairan Pengajuan Fee Sales', 'Notifikasi saat pengajuan fee sales telah dikonfirmasi cair.', 1, 1, 1, 0, '3;4', 104],
            [NotificationEvent::FEE_SALES_PENGAJUAN_REJECTED, 'Fee Sales', 'Penolakan Pengajuan Fee Sales', 'Notifikasi saat pengajuan fee sales ditolak oleh MKDT atau Keuangan.', 1, 0, 0, 1, '3;4', 105],
        ];

        $table = $this->db->table('notification_event_types');
        $existingRows = $table->select('event_type')->get()->getResultArray();
        $existingMap = array_fill_keys(array_column($existingRows, 'event_type'), true);

        $insertRows = [];
        foreach ($events as $e) {
            $row = [
                'event_type'       => $e[0],
                'category'         => $e[1],
                'label'            => $e[2],
                'description'      => $e[3],
                'default_in_app'   => $e[4],
                'default_email'    => $e[5],
                'default_web_push' => $e[6],
                'is_mandatory'     => $e[7],
                'relevant_groups'  => $e[8],
                'sort_order'       => $e[9],
                'updated_at'       => $now,
            ];

            if (isset($existingMap[$e[0]])) {
                $this->db->table('notification_event_types')
                    ->where('event_type', $e[0])
                    ->update($row);
            } else {
                $row['created_at'] = $now;
                $insertRows[] = $row;
            }
        }

        if ($insertRows !== []) {
            $this->db->table('notification_event_types')->ignore(true)->insertBatch($insertRows);
        }
    }
}

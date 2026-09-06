<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class NotificationEventTypeSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        $events = [
            // Kategori: Siteplan
            ['kavling_added', 'siteplan', 'Penambahan Kavling', 'Saat kavling baru ditambahkan ke siteplan', 1, 1, 1, 0, null, 1],
            ['kavling_updated', 'siteplan', 'Perubahan Data Kavling', 'Saat ada update luas, harga, atau atribut teknis kavling', 1, 1, 1, 0, null, 2],
            ['others_added', 'siteplan', 'Penambahan Fasum/Lainnya', 'Saat fasilitas umum atau lainnya ditambahkan', 1, 1, 1, 0, null, 3],
            ['others_updated', 'siteplan', 'Perubahan Fasum/Lainnya', 'Saat fasilitas umum atau lainnya diubah', 1, 1, 1, 0, null, 4],
            ['turun_pembangunan', 'siteplan', 'Turun Pembangunan', 'Saat kavling dipindah status turun pembangunan', 1, 1, 1, 0, '4;9', 5],

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

            // Kategori: Pajak
            ['pajak_pph', 'pajak', 'Pembayaran PPH', 'Pemberitahuan pelunasan atau proses PPH', 1, 1, 1, 0, '3;5', 30],
            ['pajak_ppn', 'pajak', 'Pembayaran PPN', 'Pemberitahuan pelunasan atau proses PPN', 1, 1, 1, 0, '3;5', 31],

            // Kategori: Produksi
            ['progress_produksi', 'produksi', 'Update Progress Produksi', 'Saat status ST bangunan (misal: 25%, 50%) naik', 1, 1, 1, 0, '7;4;9', 40],

            // Kategori: Direksi
            ['diskresi_harga', 'direksi', 'Diskresi Harga Jual', 'Approval potong harga / diskresi oleh direksi', 1, 1, 1, 0, '3;4;9', 50],

            // Kategori: Master Data
            ['master_proyek', 'master_data', 'Master Proyek', 'Info jika master data proyek diubah', 1, 1, 1, 0, '6', 60],
            ['master_cluster', 'master_data', 'Master Cluster', 'Info jika master data cluster diubah', 1, 1, 1, 0, '6', 61],
            ['master_jalan', 'master_data', 'Master Jalan', 'Info jika master data jalan diubah', 1, 1, 1, 0, '6', 62],
            ['master_tipe', 'master_data', 'Master Tipe', 'Info jika master data tipe bangunan diubah', 1, 1, 1, 0, '6', 63],

            // Kategori: Tiket Masalah
            ['tiket_masalah_baru', 'tiket_masalah', 'Tiket Masalah Baru', 'Saat ada komplain atau masalah baru tercatat', 1, 1, 1, 1, null, 70],
            ['tiket_masalah_assign', 'tiket_masalah', 'Ditugaskan ke Tiket', 'Pemberitahuan Anda di-assign ke masalah tertentu', 1, 1, 1, 1, null, 71],
            ['tiket_masalah_update', 'tiket_masalah', 'Update Progress Tiket', 'Setiap kali ada tanggapan atau status tiket diubah', 1, 1, 1, 0, null, 72],

            // Kategori: Referral
            ['mgm_referral_created', 'referral', 'Referral Baru (MGM)', 'Pendaftaran agen / referral baru', 1, 1, 1, 0, '8;3', 80],
            ['mgm_spp_submitted', 'referral', 'Pengajuan SPP Referral', 'Request pembayaran bonus referral diajukan ke finance', 1, 1, 1, 0, '3', 81],
            ['mgm_spp_cair', 'referral', 'Pencairan Bonus Referral', 'Bonus referral berhasil dibayarkan', 1, 1, 1, 0, '8', 82],
        ];

        $data = [];
        foreach ($events as $e) {
            $data[] = [
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
                'created_at'       => $now,
                'updated_at'       => $now,
            ];
        }

        $this->db->table('notification_event_types')->ignore(true)->insertBatch($data);
    }
}

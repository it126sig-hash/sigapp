<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;

final class SpptbTemplateContentTest extends CIUnitTestCase
{
    public function testSpptbViewsRenderAllUpdatedClausesAndHeaders(): void
    {
        $proyek = (object) [
            'id_proyek' => 13,
            'nama_proyek' => 'Sanggar Indah Palastri',
            'nama_pt' => 'PT. Sanggarindah Karyasentosa Raya',
            'logo' => 'uploads/logo/dummy.png',
            'logo_pt' => 'uploads/logo/dummy_pt.png',
            'bank' => 'BCA',
            'no_rek' => '233 7887 887',
            'atas_nama' => 'PT. Sanggarindah Karyasentosa Raya',
        ];

        $data = (object) [
            'id_proyek' => 13,
            'id_mkdt' => 10,
            'id_konsumen' => 1,
            'nama_konsumen' => 'Budi Santoso',
            'no_spptb' => 'SPPTB/2026/001',
            'kode_referal' => 'REF001',
            'nik' => '3201010101010001',
            'npwp' => '01.234.567.8-901.000',
            'alamat_konsumen' => 'Jl. Mawar No. 1',
            'email_konsumen' => 'budi@example.com',
            'hp_konsumen' => '08123456789',
            'nama_instansi' => 'PT Maju Jaya',
            'alamat_instansi' => 'Jl. Melati No. 2',
            'email_instansi' => 'office@majujaya.com',
            'tel_instansi' => '022123456',
            'alamat_surat' => 'Jl. Mawar No. 1',
            'pekerjaan' => 'Karyawan',
            'bidang_pekerjaan' => 'IT',
            'lama_bekerja' => '5 Tahun',
            'status_pernikahan' => 'Kawin',
            'nama_pasangan' => 'Siti',
            'nik_pasangan' => '3201010101010002',
            'hp_pasangan' => '08123456780',
            'status_pekerjaan_pasangan' => 'Ibu Rumah Tangga',
            'instansi_pasangan' => '-',
            'tipe_pricelist' => 'Type 36',
            'lb' => 36,
            'nama_jalan' => 'Palastri 1',
            'no_kavling' => 'A1',
            'luas_tanah' => 72,
            'sales' => 'Marketing A',
            'is_kpr' => 1,
            'is_allin' => 0,
            'harga_jual' => 300000000,
            'harga_kpr' => 250000000,
            'harga_uang_muka' => 50000000,
            'harga_administrasi' => 1000000,
            'harga_ppn' => 0,
            'harga_bphtb' => 5000000,
            'harga_biaya_proses' => 2000000,
            'harga_penambahan_tanah' => 0,
            'harga_penambahan' => 0,
            'harga_sbum' => 0,
            'harga_diskon_uang_muka' => 2000000,
            'harga_allin' => 0,
            'booking_fee' => 1000000,
            'booking_tgl' => '2026-11-01',
        ];

        $list_tagihan = [
            (object) [
                'status' => 'UM',
                'jatuh_tempo_tgl' => '2026-12-01',
                'nominal' => 24000000,
            ],
        ];

        $viewData = [
            'proyek' => $proyek,
            'data' => $data,
            'list_tagihan' => $list_tagihan,
        ];

        // 1. Page 1
        $htmlPage1 = preg_replace('/\s+/', ' ', view('pdf/spptb-new', $viewData));
        $this->assertStringContainsString('Proyek : Sanggar Indah Palastri', $htmlPage1);
        $this->assertStringContainsString('No. SPPTB : SPPTB/2026/001', $htmlPage1);

        // 2. Page 2
        $htmlPage2 = preg_replace('/\s+/', ' ', view('pdf/spptb-new-page2', $viewData));
        $this->assertStringContainsString('<li>Diskon</li>', $htmlPage2);
        $this->assertStringContainsString('Uraian Pembayaran', $htmlPage2);
        $this->assertStringContainsString('Apabila keterlambatan pembayaran melewati waktu 30 hari kerja dari tanggal jatuh tempo. Saya bersedia pembelian tanah dan rumah ini dibatalkan dengan sendirinya.', $htmlPage2);
        $this->assertStringContainsString('secara lengkap kepada PT. SANGGARINDAH KARYASENTOSA RAYA paling lambat 2 (dua) minggu setelah tanggal SPPTB ini ditandatangani.', $htmlPage2);
        $this->assertStringContainsString('Apabila terjadi penurunan KPR/nilai KPR yang disetujui dari pihak Bank lebih rendah, maka saya bersedia membayar kekurangan / penurunan KPR tersebut paling lambat 2 minggu hari kerja setelah tanggal persetujuan kredit dari Bank.', $htmlPage2);
        $this->assertStringContainsString('Saya bersedia untuk mematuhi dan menaati seluruh peraturan, tata tertib, serta ketentuan yang berlaku di lingkungan perumahan PT. SANGGARINDAH KARYASENTOSA RAYA, baik yang telah ditetapkan maupun yang akan ditetapkan di kemudian hari, selama tidak bertentangan dengan peraturan perundang-undangan yang berlaku.', $htmlPage2);

        // 3. Page 3
        $htmlPage3 = preg_replace('/\s+/', ' ', view('pdf/spptb-new-page3', $viewData));
        $this->assertStringContainsString('Meterai Rp. 10.000', $htmlPage3);
        $this->assertStringContainsString('(BUDI SANTOSO) <br> Pembeli', $htmlPage3);
    }
}

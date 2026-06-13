<?php

namespace App\Controllers\Api;

use CodeIgniter\HTTP\ResponseInterface;
use App\Services\TransaksiService;

class TransaksiController extends BaseApiController
{
    protected $mkdtService;

    public function __construct()
    {
        $this->mkdtService = new TransaksiService();
    }

    public function getByID(): ResponseInterface
    {
        $rules = [
            'id_mkdt'      => 'permit_empty|is_natural_no_zero',
            'id_hargajual' => 'permit_empty|is_natural_no_zero',
            'id_kavling'   => 'permit_empty|is_natural_no_zero',
        ];
        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $idMkdt      = $this->request->getVar('id_mkdt') ?: null;
        $idHargajual = $this->request->getVar('id_hargajual') ?: null;
        $idKavling   = $this->request->getVar('id_kavling') ?: null;

        $resp = $this->mkdtService->getBundle(
            $idMkdt ? (int) $idMkdt : null,
            $idHargajual ? (int) $idHargajual : null,
            $idKavling ? (int) $idKavling : null
        );

        $resp['token'] = csrf_hash();
        return $this->response->setJSON($resp);
    }

    public function getStatusByID(): ResponseInterface
    {
        $id_hargajual = $this->request->getVar('id_hargajual');
        $id_kavling   = $this->request->getVar('id_kavling');
        $id_mkdt      = $this->request->getVar('id_mkdt');

        $resp = $this->mkdtService->getStatusById($id_hargajual, $id_kavling, $id_mkdt);
        return $this->response->setJSON($resp);
    }

    public function saveTransaksi(): ResponseInterface
    {
        // PENTING: PHP otomatis mengubah tanda '-' pada nama field menjadi '_'
        // sehingga field 'mk-hargajual' menjadi 'mk_hargajual' di $_POST.
        // Gunakan request->getPost() untuk akses lebih aman.
        $req = $this->request;

        // --- Data konsumen ---
        $kons = [
            'id_kavling'               => trim((string) ($req->getPost('id_kavling') ?? '')),
            'id_mkdt'                  => $req->getPost('id_mkdt') ?? null,
            'no_spptb'                 => trim((string) ($req->getPost('no_spptb') ?? '')),
            'nama_konsumen'            => trim((string) ($req->getPost('nama_konsumen') ?? '')),
            'nik'                      => trim((string) ($req->getPost('nik_konsumen') ?? '')),
            'alamat_konsumen'          => trim((string) ($req->getPost('alamat_konsumen') ?? '')),
            'npwp'                     => trim((string) ($req->getPost('npwp_konsumen') ?? '')),
            'hp_konsumen'              => trim((string) ($req->getPost('hp_konsumen') ?? '')),
            'status_konsumen'          => trim((string) ($req->getPost('status_konsumen') ?? '')),
            'email_konsumen'           => trim((string) ($req->getPost('email_konsumen') ?? '')),
            'nama_instansi'            => trim((string) ($req->getPost('nama_instansi') ?? '')),
            'alamat_instansi'          => trim((string) ($req->getPost('alamat_instansi') ?? '')),
            'tel_instansi'             => trim((string) ($req->getPost('tel_instansi') ?? '')),
            'email_instansi'           => trim((string) ($req->getPost('email_instansi') ?? '')),
            'alamat_surat'             => trim((string) ($req->getPost('alamat_surat') ?? '')),
            'pekerjaan'                => trim((string) ($req->getPost('pekerjaan') ?? '')),
            'lama_bekerja'             => trim((string) ($req->getPost('lama_bekerja') ?? '')),
            'bidang_pekerjaan'         => trim((string) ($req->getPost('bidang_pekerjaan') ?? '')),
            'status_pernikahan'        => trim((string) ($req->getPost('status_pernikahan') ?? '')),
            'nama_pasangan'            => trim((string) ($req->getPost('nama_pasangan') ?? '')),
            'nik_pasangan'             => trim((string) ($req->getPost('nik_pasangan') ?? '')),
            'hp_pasangan'              => trim((string) ($req->getPost('hp_pasangan') ?? '')),
            'status_pekerjaan_pasangan'=> trim((string) ($req->getPost('status_pekerjaan_pasangan') ?? '')),
            'instansi_pasangan'        => trim((string) ($req->getPost('instansi_pasangan') ?? '')),
            'sales'                    => trim((string) ($req->getPost('sales') ?? '')),
            'add_by'                   => user_id(),
            'edit_by'                  => user_id(),
        ];

        // Field dt-status_mkdt → diterima PHP sebagai dt_status_mkdt
        $statusMkdt = trim((string) ($req->getPost('dt_status_mkdt') ?? ''));
        // Fallback: coba nama asli jika CI4 tidak konversi
        if ($statusMkdt === '') {
            $statusMkdt = trim((string) ($req->getPost('dt-status_mkdt') ?? ''));
        }
        if ($statusMkdt === 'Batal') {
            $kons['keterangan'] = trim((string) ($req->getPost('dt_keterangan_batal') ?? $req->getPost('dt-keterangan_batal') ?? ''));
        }
        $perluRefund = $statusMkdt === 'Batal'
            ? (int) (($req->getPost('dt_perlu_refund') ?? $req->getPost('dt-perlu_refund') ?? 0) == 1)
            : 0;

        // --- Data mkdt (keuangan/harga) ---
        // Nama field dengan '-' dikonversi PHP ke '_' di $_POST.
        // Misal: 'mk-hargajual' → bisa dibaca sebagai 'mk_hargajual' atau pakai getPost('mk-hargajual')
        // CI4's getPost() langsung membaca dari stream, tdk konversi. Gunakan keduanya sebagai fallback.
        $mk = [
            'id_mkdt'               => $kons['id_mkdt'],
            'id_konsumen'           => null,
            'status_mkdt'           => $statusMkdt,
            'is_allin'              => $req->getPost('idk-is_allin') ?? $req->getPost('idk_is_allin') ?? 0,
            'harga_allin'           => $this->cleanNum($req->getPost('mk-harga_allin') ?? $req->getPost('mk_harga_allin') ?? 0),
            'id_hargajual'          => $req->getPost('idk-harga_akhir') ?? $req->getPost('idk_harga_akhir') ?? null,
            'tgl_harga'             => $req->getPost('mk-tgl_harga') ?? $req->getPost('mk_tgl_harga') ?? null,
            'harga_uang_muka'       => $this->cleanNum($req->getPost('mk-uang_muka') ?? $req->getPost('mk_uang_muka') ?? 0),
            'harga_jual'            => $this->cleanNum($req->getPost('mk-hargajual') ?? $req->getPost('mk_hargajual') ?? 0),
            'harga_jual_net'        => $this->cleanNum($req->getPost('mk-hargajual_net') ?? $req->getPost('mk_hargajual_net') ?? 0),
            'harga_administrasi'    => $this->cleanNum($req->getPost('mk-biaya_adm') ?? $req->getPost('mk_biaya_adm') ?? 0),
            'harga_bphtb'           => $this->cleanNum($req->getPost('mk-bphtb') ?? $req->getPost('mk_bphtb') ?? 0),
            'harga_biaya_proses'    => $this->cleanNum($req->getPost('mk-biaya_proses') ?? $req->getPost('mk_biaya_proses') ?? 0),
            'harga_kpr'             => $this->cleanNum($req->getPost('mk-kpr') ?? $req->getPost('mk_kpr') ?? 0),
            'harga_ppn'             => $this->cleanNum($req->getPost('mk-ppn') ?? $req->getPost('mk_ppn') ?? 0),
            'harga_penambahan'      => $this->cleanNum($req->getPost('mk-harga_penambahan') ?? $req->getPost('mk_harga_penambahan') ?? 0),
            'harga_penambahan_tanah'=> $this->cleanNum($req->getPost('mk-harga_penambahan_tanah') ?? $req->getPost('mk_harga_penambahan_tanah') ?? 0),
            'harga_sbum'            => $this->cleanNum($req->getPost('mk-harga_sbum') ?? $req->getPost('mk_harga_sbum') ?? 0),
            'harga_diskon_hargajual'=> $this->cleanNum($req->getPost('mk-diskon_harga_jual') ?? $req->getPost('mk_diskon_harga_jual') ?? 0),
            'harga_diskon_uang_muka'=> $this->cleanNum($req->getPost('mk-diskon_uang_muka') ?? $req->getPost('mk_diskon_uang_muka') ?? 0),
            'promo'                 => trim((string) ($req->getPost('promo') ?? '')),
            'rincian'               => $req->getPost('rincian') ?? null,
            'jenis_subsidi'         => $req->getPost('jenis_subsidi') ?? null,
            'is_kpr'                => $req->getPost('is_kpr') ?? null,
            'is_subsidi'            => $req->getPost('is_subsidi') ?? null,
            'booking_fee'           => $this->cleanNum($req->getPost('dt-booking_fee') ?? $req->getPost('dt_booking_fee') ?? 0),
            'booking_tgl'           => $req->getPost('dt-booking_tgl') ?? $req->getPost('dt_booking_tgl') ?? null,
            'perlu_refund'          => $perluRefund,
            'id_kavling'            => $kons['id_kavling'],
            'is_sudah_isi_tagihan'  => 1,
        ];


        // --- Data tagihan ---
        $um = [
            'id_keuangan'     => (array) ($req->getPost('id_keuangan') ?? []),
            'berita_acara'    => (array) ($req->getPost('berita_acara') ?? []),
            'jatuh_tempo_tgl' => (array) ($req->getPost('jatuh_tempo_tgl') ?? []),
            'nominal'         => (array) ($req->getPost('nominal') ?? []),
        ];

        // --- Files ---
        $files = [];
        foreach (['file_ktp', 'file_npwp', 'file_data_diri', 'file_spptb', 'file_surat_kuasa'] as $f) {
            $files[$f] = $this->request->getFile($f);
        }

        // --- Opsi ganti nama / kavling ---
        $opt = [
            'is_ganti_nama'   => trim((string) ($req->getPost('is_ganti_nama') ?? '')),
            'id_mkdt_old'     => $req->getPost('id_mkdt_old') ?? null,
            'id_konsumen_old' => $req->getPost('id_konsumen_old') ?? null,
            'id_konsumen'     => $req->getPost('id_konsumen') ?: null,
            'is_data_baru'    => (int) ($req->getPost('mkdt_data_baru') ?? 0) === 1,
            'allow_duplicate_nik' => (int) ($req->getPost('allow_duplicate_nik') ?? 0) === 1,
        ];

        $resp = $this->mkdtService->saveTransaksi($kons, $mk, $um, $files, $opt);
        $resp['token'] = csrf_hash();
        return $this->response->setJSON($resp);
    }

    public function saveStatus(): ResponseInterface
    {
        $p = $this->request->getPost();

        $perintah_bangun = ($p['perintah_bangun'] ?? null) !== null ? 1 : 0;
        $sp3k = ($p['sp3k'] ?? null) !== null ? 1 : 0;
        $akad = ($p['akad'] ?? null) !== null ? 1 : 0;

        $data = [
            'id_mkdt'           => (int) ($p['id_mkdt'] ?? 0),
            'id_kavling'        => (int) ($p['id_kavling'] ?? 0),
            'status_mkdt'       => $p['status_mkdt'] ?? '',
            'sp3k'              => $sp3k,
            'sp3k_no'           => $p['sp3k_no'] ?? null,
            'sp3k_tgl'          => $p['sp3k_tgl'] ?? null,
            'sp3k_tgl_exp'      => $p['sp3k_tgl_exp'] ?? null,
            'harga_kpr_acc'     => $this->cleanNum($p['acc_harga_kpr'] ?? 0),
            'rencana_akad_tgl'  => $p['rencana_akad_tgl'] ?? null,
            'notaris'           => $p['notaris'] ?? null,
            'is_ajb'            => $p['is_ajb'] ?? null,
            'akad'              => $akad,
            'akad_tgl'          => $p['akad_tgl'] ?? null,
            'debitur_no'        => $p['debitur_no'] ?? null,
            'keterangan'        => $p['mkdt_keterangan'] ?? null,
            'wawancara_tgl'     => $p['wawancara_tgl'] ?? null,
            'wawancara'         => $p['wawancara'] ?? null,
            'id_bank'           => $p['id_bank'] ?? null,
            'bank'              => $p['bank'] ?? null,
        ];

        $perintahBangun = [
            'perintah_bangun'     => $perintah_bangun,
            'perintah_bangun_oleh'=> $perintah_bangun ? user_id() : null,
            'perintah_bangun_tgl' => $p['perintah_bangun_tgl'] ?? null,
        ];

        $files = [];
        foreach (['perintah_bangun_file', 'sp3k_file', 'bast_file'] as $f) {
            $files[$f] = $this->request->getFile($f);
        }

        $resp = $this->mkdtService->saveStatus($data, $perintahBangun, $files);
        $resp['token'] = csrf_hash();
        return $this->response->setJSON($resp);
    }

    /** Bersihkan format angka ribuan, misal "1,500,000" → "1500000" */
    private function cleanNum($value): string
    {
        return str_replace(',', '', (string) $value);
    }
}

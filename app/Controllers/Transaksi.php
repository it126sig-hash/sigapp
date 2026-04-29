<?php

namespace App\Controllers;

use App\Controllers\Notif;
use App\Models\ProfilePerusahaanModel;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\MkdtModel;
use App\Models\KavlingModel;
use App\Models\KonsumenModel;
use App\Models\KeuanganModel;
use App\Services\TransaksiService;
use App\Services\KonsumenService;
use App\Services\KeuanganService;
use App\Services\StorageService;
use Throwable;

// use App\Libraries\Pdf;
use App\Libraries\Mpdf_lib;

class Transaksi extends BaseController
{
    protected $db;
    protected $comproModel;
    protected $mpdf;

    /** @var \App\Services\KonsumenService */
    protected $konsumenService;

    protected $mkdtService;

    /** @var \App\Services\KeuanganService */
    protected $keuanganService;

    /** @var \App\Services\StorageService */
    protected $storageService;
    protected $notif;

    protected $keuModel;
    protected $mkdtModel;
    protected $kavlingModel;
    protected $konsumenModel;
    public function __construct()
    {
        $this->keuModel = new KeuanganModel();
        $this->mkdtModel = new MkdtModel();
        $this->kavlingModel = new KavlingModel();
        $this->konsumenModel = new KonsumenModel();

        $this->konsumenService = new KonsumenService();
        $this->mkdtService = new TransaksiService();
        $this->keuanganService = new KeuanganService();
        $this->storageService = new StorageService();
        $this->notif = new Notif();
        $this->comproModel = new ProfilePerusahaanModel();
        $this->db = db_connect();
        // $this->pdf = new Pdf();
        $this->mpdf = new Mpdf_lib();
    }

    function getByID()
    {
        // Validasi sederhana (jangan takut “strict”, daripada SQL error/notice)
        $rules = [
            'id_mkdt' => 'permit_empty|is_natural_no_zero',
            'id_hargajual' => 'permit_empty|is_natural_no_zero',
            'id_kavling' => 'permit_empty|is_natural_no_zero',
        ];
        if (!$this->validate($rules)) {
            return $this->response->setStatusCode(400)
                ->setJSON([
                    'error' => 'Invalid parameters',
                    'messages' => $this->validator->getErrors(),
                    'token' => csrf_hash(),
                ]);
        }

        $idMkdt = $this->request->getVar('id_mkdt') ?: null;
        $idHargajual = $this->request->getVar('id_hargajual') ?: null;
        $idKavling = $this->request->getVar('id_kavling') ?: null;



        $resp = $this->mkdtService->getBundle(
            $idMkdt ? (int) $idMkdt : null,
            $idHargajual ? (int) $idHargajual : null,
            $idKavling ? (int) $idKavling : null
        );

        return $this->response->setJSON($resp);
    }

    function getStatusByID()
    {
        $id_hargajual = $this->request->getVar('id_hargajual');
        $id_kavling = $this->request->getVar('id_kavling');
        $id_mkdt = $this->request->getVar('id_mkdt');
        $resp = $this->mkdtService->getStatusById($id_hargajual, $id_kavling, $id_mkdt);

        return $this->response->setJSON($resp);
    }
    public function saveTransaksi(): ResponseInterface
    {
        $p = $this->request->getPost();

        $kons = [
            'id_kavling'               => trim((string) ($p['id_kavling'] ?? '')),
            'id_mkdt'                  => $p['id_mkdt'] ?? null,
            'no_spptb'                 => trim((string) ($p['no_spptb'] ?? '')),
            'nama_konsumen'            => trim((string) ($p['nama_konsumen'] ?? '')),
            'nik'                      => trim((string) ($p['nik_konsumen'] ?? '')),
            'alamat_konsumen'          => trim((string) ($p['alamat_konsumen'] ?? '')),
            'npwp'                     => trim((string) ($p['npwp_konsumen'] ?? '')),
            'hp_konsumen'              => trim((string) ($p['hp_konsumen'] ?? '')),
            'status_konsumen'          => trim((string) ($p['status_konsumen'] ?? '')),
            'email_konsumen'           => trim((string) ($p['email_konsumen'] ?? '')),
            'nama_instansi'            => trim((string) ($p['nama_instansi'] ?? '')),
            'alamat_instansi'          => trim((string) ($p['alamat_instansi'] ?? '')),
            'tel_instansi'             => trim((string) ($p['tel_instansi'] ?? '')),
            'email_instansi'           => trim((string) ($p['email_instansi'] ?? '')),
            'alamat_surat'             => trim((string) ($p['alamat_surat'] ?? '')),
            'pekerjaan'                => trim((string) ($p['pekerjaan'] ?? '')),
            'lama_bekerja'             => trim((string) ($p['lama_bekerja'] ?? '')),
            'bidang_pekerjaan'         => trim((string) ($p['bidang_pekerjaan'] ?? '')),
            'status_pernikahan'        => trim((string) ($p['status_pernikahan'] ?? '')),
            'nama_pasangan'            => trim((string) ($p['nama_pasangan'] ?? '')),
            'nik_pasangan'             => trim((string) ($p['nik_pasangan'] ?? '')),
            'hp_pasangan'              => trim((string) ($p['hp_pasangan'] ?? '')),
            'status_pekerjaan_pasangan'=> trim((string) ($p['status_pekerjaan_pasangan'] ?? '')),
            'instansi_pasangan'        => trim((string) ($p['instansi_pasangan'] ?? '')),
            'sales'                    => trim((string) ($p['sales'] ?? '')),
            'add_by'                   => user_id(),
            'edit_by'                  => user_id(),
        ];

        $statusMkdt = trim((string) ($p['dt-status_mkdt'] ?? ''));
        if ($statusMkdt === 'Batal') {
            $kons['keterangan'] = trim((string) ($p['dt-keterangan_batal'] ?? ''));
        }

        $mk = [
            'id_mkdt'               => $kons['id_mkdt'],
            'id_konsumen'           => null,
            'status_mkdt'           => $statusMkdt,
            'is_allin'              => $p['idk-is_allin'] ?? 0,
            'harga_allin'           => $this->num($p['mk-harga_allin'] ?? 0),
            'id_hargajual'          => $p['idk-harga_akhir'] ?? null,
            'tgl_harga'             => $p['mk-tgl_harga'] ?? null,
            'harga_uang_muka'       => $this->num($p['mk-uang_muka'] ?? 0),
            'harga_jual'            => $this->num($p['mk-hargajual'] ?? 0),
            'harga_jual_net'        => $this->num($p['mk-hargajual_net'] ?? 0),
            'harga_administrasi'    => $this->num($p['mk-biaya_adm'] ?? 0),
            'harga_bphtb'           => $this->num($p['mk-bphtb'] ?? 0),
            'harga_biaya_proses'    => $this->num($p['mk-biaya_proses'] ?? 0),
            'harga_kpr'             => $this->num($p['mk-kpr'] ?? 0),
            'harga_ppn'             => $this->num($p['mk-ppn'] ?? 0),
            'harga_penambahan'      => $this->num($p['mk-harga_penambahan'] ?? 0),
            'harga_penambahan_tanah'=> $this->num($p['mk-harga_penambahan_tanah'] ?? 0),
            'harga_sbum'            => $this->num($p['mk-harga_sbum'] ?? 0),
            'promo'                 => trim((string) ($p['promo'] ?? '')),
            'rincian'               => $p['rincian'] ?? null,
            'jenis_subsidi'         => $p['jenis_subsidi'] ?? null,
            'is_kpr'                => $p['is_kpr'] ?? null,
            'is_subsidi'            => $p['is_subsidi'] ?? null,
            'booking_fee'           => $this->num($p['dt-booking_fee'] ?? 0),
            'booking_tgl'           => $p['dt-booking_tgl'] ?? null,
            'id_kavling'            => $kons['id_kavling'],
            'is_sudah_isi_tagihan'  => 1,
        ];

        $um = [
            'id_keuangan'     => (array) ($p['id_keuangan'] ?? []),
            'berita_acara'    => (array) ($p['berita_acara'] ?? []),
            'jatuh_tempo_tgl' => (array) ($p['jatuh_tempo_tgl'] ?? []),
            'nominal'         => (array) ($p['nominal'] ?? []),
        ];

        $files = [];
        foreach (['file_ktp', 'file_npwp', 'file_data_diri', 'file_spptb', 'file_surat_kuasa'] as $f) {
            $files[$f] = $this->request->getFile($f);
        }

        $opt = [
            'is_ganti_nama'   => trim((string) ($p['is_ganti_nama'] ?? '')),
            'id_mkdt_old'     => $p['id_mkdt_old'] ?? null,
            'id_konsumen_old' => $p['id_konsumen_old'] ?? null,
            'id_konsumen'     => $p['id_konsumen'] ?: null,
            'is_data_baru'    => (int) ($p['mkdt_data_baru'] ?? 0) === 1,
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
            'harga_kpr_acc'     => $this->num($p['acc_harga_kpr'] ?? 0),
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
            'perintah_bangun'      => $perintah_bangun,
            'perintah_bangun_oleh' => $perintah_bangun ? user_id() : null,
            'perintah_bangun_tgl'  => $p['perintah_bangun_tgl'] ?? null,
        ];

        $files = [];
        foreach (['perintah_bangun_file', 'sp3k_file', 'bast_file'] as $f) {
            $files[$f] = $this->request->getFile($f);
        }

        $resp = $this->mkdtService->saveStatus($data, $perintahBangun, $files);
        $resp['token'] = csrf_hash();
        return $this->response->setJSON($resp);
    }

    protected function num($d): string
    {
        $d = str_replace(',', '', (string) $d);
        return $d;
    }
}

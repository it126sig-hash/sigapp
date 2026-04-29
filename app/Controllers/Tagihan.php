<?php

namespace App\Controllers;

use App\Controllers\Notif;
use App\Models\ProfilePerusahaanModel;
use App\Services\KeuanganService;
use App\Services\StorageService;
use App\Services\TransaksiService;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use App\Services\KonsumenService;
use App\Repositories\LogPembayaranRepository;
use App\Repositories\KeuanganRepository;
use Throwable;

// use App\Libraries\Pdf;
use App\Libraries\Mpdf_lib;

class Tagihan extends BaseController
{
    protected $db;
    protected $comproModel;
    protected $mpdf;
    protected $logRepo;
    protected $keuRepo;

    /** @var \App\Services\KonsumenService */
    protected $konsumenService;

    /** @var \App\Services\TransaksiService */
    protected $mkdtService;

    /** @var \App\Services\KeuanganService */
    protected $keuanganService;

    /** @var \App\Services\StorageService */
    protected $storageService;
    protected $notif;
    use ResponseTrait;

    public function __construct()
    {
        $this->konsumenService = new KonsumenService();
        $this->mkdtService      = new TransaksiService();
        $this->keuanganService  = new KeuanganService();
        $this->storageService   = new StorageService();
        $this->notif            = new Notif();
        $this->comproModel = new ProfilePerusahaanModel();
        $this->db = db_connect();
        // $this->pdf = new Pdf();
        $this->mpdf = new Mpdf_lib();

        $this->logRepo = new LogPembayaranRepository();
        $this->keuRepo = new KeuanganRepository();
    }

    function getByID()
    {
        $id_mkdt = trim((string) $this->request->getVar('id_mkdt'));

        $data['token'] = csrf_hash();

        if (empty($id_mkdt)) {
            return $this->response->setJSON([
                'token'    => csrf_hash(),
                'success'  => false,
                'messages' => 'data tidak ditemukan',
            ]);
        }
        $data['mkdt'] = $this->konsumenService->getKonsumenTransaksi($id_mkdt);
        $data['tagihan'] =  $this->keuanganService->getTagihanById($id_mkdt);
        $data['log_pembayaran'] = $this->keuanganService->getRiwayatBayarWithDetailById($id_mkdt);

        return $this->response->setJSON($data);
    }
    public function hapusTurunKPR(): ResponseInterface
    {
        // Hanya izinkan AJAX & JSON
        // if (!$this->request->isAJAX()) {
        //     return $this->fail('Invalid request', 400);
        // }

        // Izinkan POST atau DELETE (tergantung frontend Anda)
        $method = strtoupper($this->request->getMethod());
        if (!in_array($method, ['POST', 'DELETE'], true)) {
            return $this->fail('Method not allowed', 405);
        }

        // Ambil payload (prioritaskan JSON)
        $payload = $this->request->getJSON(true) ?? $this->request->getPost();
        $id      = isset($payload['id_keuangan']) ? (int) $payload['id_keuangan'] : null;

        // Validasi sederhana (bisa juga pakai $this->validate())
        if (empty($id) || $id <= 0) {
            return $this->failValidationErrors(['id_keuangan' => 'ID Keuangan tidak ditemukan']);
        }
        $q = $this->keuanganService->hapusTurunKPR($id);
        return $this->response->setJSON($q);
    }
    function tambahTurunKPR()
    {
        $data = $this->request->getVar();
        $q =  $this->keuanganService->tambahTurunKPR($data);
        return $this->response->setJSON($q, true);
    }
    public function save(): ResponseInterface
    {
        $resp = ['token' => csrf_hash()];

        // --- 0) Ambil & normalisasi input
        $idKavling      = trim((string) $this->request->getVar('id_kavling'));
        $isGantiNama    = trim((string) $this->request->getVar('is_ganti_nama')); // "", "Ganti Nama", "Ganti Kavling"
        $idMkdtOld      = $this->request->getVar('id_mkdt_old');
        $idKonsumenOld  = $this->request->getVar('id_konsumen_old');
        $isDataBaru     = (int) ($this->request->getVar('mkdt_data_baru') ?? 0) === 1;

        // form konsumen
        $kons = [
            'id_kavling'        => $idKavling,
            'id_mkdt'           => $this->request->getPost('id_mkdt') ?: null,
            'no_spptb'          => trim((string) $this->request->getPost('no_spptb')),
            'nama_konsumen'     => trim((string) $this->request->getPost('nama_konsumen')),
            'nik'               => trim((string) $this->request->getPost('nik_konsumen')),
            'alamat_konsumen'   => trim((string) $this->request->getPost('alamat_konsumen')),
            'npwp'              => trim((string) $this->request->getPost('npwp_konsumen')),
            'hp_konsumen'       => trim((string) $this->request->getPost('hp_konsumen')),
            'status_konsumen'   => trim((string) $this->request->getPost('status_konsumen')),
            'email_konsumen'    => trim((string) $this->request->getPost('email_konsumen')),

            'nama_instansi'     => trim((string) $this->request->getPost('nama_instansi')),
            'alamat_instansi'   => trim((string) $this->request->getPost('alamat_instansi')),
            'tel_instansi'      => trim((string) $this->request->getPost('tel_instansi')),
            'email_instansi'      => trim((string) $this->request->getPost('email_instansi')),
            'alamat_surat'      => trim((string) $this->request->getPost('alamat_surat')),
            'jenis_pekerjaan'      => trim((string) $this->request->getPost('jenis_pekerjaan')),
            'lama_bekerja'      => trim((string) $this->request->getPost('lama_bekerja')),
            'bidang_pekerjaan'      => trim((string) $this->request->getPost('bidang_pekerjaan')),

            'status_pernikahan' => trim((string) $this->request->getPost('status_pernikahan')),
            'nama_pasangan'     => trim((string) $this->request->getPost('nama_pasangan')),
            'nik_pasangan'      => trim((string) $this->request->getPost('nik_pasangan')),
            'hp_pasangan'      => trim((string) $this->request->getPost('hp_pasangan')),
            'status_pekerjaan_pasangan'      => trim((string) $this->request->getPost('status_pekerjaan_pasangan')),
            'instansi_pasangan'      => trim((string) $this->request->getPost('instansi_pasangan')),

            'sales'             => trim((string) $this->request->getPost('sales')),
            'add_by'            => user_id(),
            'edit_by'           => user_id(),
        ];

        if ($isDataBaru) {
            $kons['id_mkdt'] = null;
        }

        $statusMkdt = trim((string) $this->request->getPost('dt-status_mkdt'));
        if ($statusMkdt === 'Batal') {
            $kons['keterangan'] = trim((string) $this->request->getVar('dt-keterangan_batal'));
        }

        $idKonsumen = $this->request->getPost('id_konsumen') ?: null;

        // mkdt detail (keuangan/harga)
        $mk = [
            'id_mkdt'                   => $kons['id_mkdt'],
            'id_konsumen'               => null, // set setelah upsert konsumen
            'status_mkdt'               => $statusMkdt,
            'id_hargajual'              => $this->request->getPost('idk-harga_akhir'),
            'tgl_harga'                 => $this->num($this->request->getPost('mk-tgl_harga')),
            'harga_uang_muka'           => $this->num($this->request->getPost('mk-uang_muka')),
            'harga_jual'                => $this->num($this->request->getPost('mk-hargajual')),
            'harga_jual_net'            => $this->num($this->request->getPost('mk-hargajual_net')),
            'harga_administrasi'        => $this->num($this->request->getPost('mk-biaya_adm')),
            'harga_bphtb'               => $this->num($this->request->getPost('mk-bphtb')),
            'harga_biaya_proses'        => $this->num($this->request->getPost('mk-biaya_proses')),
            'harga_kpr'                 => $this->num($this->request->getPost('mk-kpr')),
            'harga_ppn'                 => $this->num($this->request->getPost('mk-ppn')),
            'harga_penambahan'          => $this->num($this->request->getPost('mk-harga_penambahan')),
            'harga_penambahan_tanah'    => $this->num($this->request->getPost('mk-harga_penambahan_tanah')),
            'harga_sbum'    => $this->num($this->request->getPost('mk-harga_sbum')),
            'promo'                     => trim((string) $this->request->getPost('promo')),
            'rincian'                   => $this->request->getPost('rincian'),
            'jenis_subsidi'             => $this->request->getPost('jenis_subsidi'),
            'is_kpr'                    => $this->request->getPost('is_kpr'),
            'is_subsidi'                => $this->request->getPost('is_subsidi'),
            'booking_fee'               => $this->num($this->request->getPost('dt-booking_fee')),
            'booking_tgl'               => $this->request->getPost('dt-booking_tgl'),
            'keuangan_saved_by'         => user_id(),
            'id_kavling'                => $idKavling,
        ];

        // tagihan UM
        $um = [
            'id_keuangan'    => (array) $this->request->getVar('id_keuangan'),
            'berita_acara'   => (array) $this->request->getVar('berita_acara'),
            'jatuh_tempo_tgl' => (array) $this->request->getVar('jatuh_tempo_tgl'),
            'nominal'        => (array) $this->request->getVar('nominal'),
        ];
        // tagihan BB
        // $bb = [
        //     'id_keuangan'    => (array) $this->request->getVar('id_keuangan_bb'),
        //     'berita_acara'   => (array) $this->request->getVar('berita_acara_bb'),
        //     'jatuh_tempo_tgl' => (array) $this->request->getVar('jatuh_tempo_tgl_bb'),
        //     'nominal'        => (array) $this->request->getVar('nominal_bb'),
        // ];

        // --- 1) Validasi minimal (silakan tambah ruleset CI4 Validator kamu)
        if (!$idKavling) {
            return $this->response->setJSON([
                'token'    => csrf_hash(),
                'success'  => false,
                'messages' => 'id_kavling wajib diisi',
            ]);
        }
        if (!$kons['nama_konsumen']) {
            return $this->response->setJSON([
                'token'    => csrf_hash(),
                'success'  => false,
                'messages' => 'Nama konsumen wajib diisi',
            ]);
        }

        // --- 2) Transactional flow
        $db = \Config\Database::connect();
        $db->transException(true);



        try {
            $db->transStart();

            // 2a. Upsert Konsumen
            $idKonsumen = $this->konsumenService->upsert($idKonsumen, $kons);
            if (!$idKonsumen) {
                throw new \RuntimeException('Gagal menyimpan data konsumen.');
            }

            // 2b. Create/Update MKDT (+handle ganti nama/kavling, update kavling.id_mkdt)
            $mk['id_konsumen'] = $idKonsumen;
            $mkResult = $this->mkdtService->createOrUpdate($kons['id_mkdt'], $mk, [
                'is_ganti_nama'   => $isGantiNama === 'Ganti Nama',
                'is_ganti_kavling' => $isGantiNama === 'Ganti Kavling',
                'id_mkdt_old'     => $idMkdtOld,
                'id_konsumen_old' => $idKonsumenOld,
                'nama_konsumen'   => $kons['nama_konsumen'],
                'id_kavling'      => $idKavling,
                'actor_id'        => user_id(),
            ]);

            $idMkdt = $mkResult['id_mkdt'];
            $uniqId = $mkResult['uniq_id']; // dipertahankan kalau ganti nama/kavling

            // 2c. Sync Tagihan (UM & BB)
            $this->keuanganService->syncTagihan($idMkdt, $um, user_id());

            // 2d. Upload lampiran (setelah id_mkdt ada)
            $spptbFile   = $this->request->getFile('file_spptb');
            $suratKuasa  = $this->request->getFile('file_surat_kuasa');

            if ($spptbFile && $spptbFile->isValid() && !$spptbFile->hasMoved()) {
                $pathSpptb = $this->storageService->store($spptbFile, 'uploads/spptb/' . date('Ymd'));
                // catat ke table file_spptb
                $db->table('file_spptb')->insert([
                    'id_mkdt'    => $idMkdt,
                    'lokasi'     => $pathSpptb,
                    'created_at' => date('Y-m-d H:i:s'),
                    'add_by'     => user_id(),
                ]);
            }

            if ($suratKuasa && $suratKuasa->isValid() && !$suratKuasa->hasMoved()) {
                $pathKuasa = $this->storageService->store($suratKuasa, 'uploads/spptb/lampiran/' . date('Ymd'));
                // kalau mau disimpan ke kolom lain mkdt/file table, tinggal insert di sini
                $db->table('file_spptb')->insert([
                    'id_mkdt'    => $idMkdt,
                    'lokasi'     => $pathKuasa,
                    'created_at' => date('Y-m-d H:i:s'),
                    'add_by'     => user_id(),
                ]);
            }

            // 2e. Notifikasi
            $pesanNotif = $kons['id_mkdt'] ?
                ('Melakukan perubahan data konsumen : ' . $kons['nama_konsumen']) : ('Booking kavling atas nama : ' . $kons['nama_konsumen']);

            $this->notif->tambah_notif("3;4;9", $pesanNotif, user_id(), $idKavling, $idKonsumen);

            $db->transComplete();

            $resp['success']  = true;
            $resp['messages'] = $kons['id_mkdt'] ? 'Data berhasil diperbaharui' : 'Data berhasil ditambah';
            $resp['id_mkdt']  = $idMkdt;
            $resp['id_konsumen'] = $idKonsumen;
            $resp['uniq_id']  = $uniqId ?? null;
            return $this->response->setJSON($resp);
        } catch (Throwable $e) {
            if ($db->transStatus() !== false) {
                $db->transRollback();
            }
            return $this->response->setJSON([
                'token'    => csrf_hash(),
                'success'  => false,
                'messages' => 'Gagal menyimpan data: ' . $e->getMessage(),
            ]);
        }
    }

    ################################## untuk list tagihan ##########################

    function listTagihan()
    {
        $data['content'] = 'keuangan/list-tagihan';
        $data['data']['controller'] = 'Keuangan';
        $data['data']['title'] = 'List Tagihan';
        $data['data']['li_keu'] = json_encode($this->keuRepo->getLIKeu());

        return view('template', $data);
    }

    function getListTagihan($status = null)
    {
        $request = $this->request;
        $datatbel = $this->keuanganService->getListTagihan($request, $status);

        return $datatbel;
    }
    function getListTagihanGrouped()
    {
        $request   = $this->request;
        $datatbel = $this->keuanganService->getListTagihanGrouped($request);

        return $datatbel;
    }
    ################################## end of untuk list tagihan ##########################

    ################################## untuk list riwayat bayar ##########################
    function riwayatBayarIndex()
    {
        $data['content'] = 'keuangan/riwayat-bayar';
        $data['data']['controller'] = 'Tagihan';
        $data['data']['title'] = 'Riwayat Pembayaran';

        return view('template', $data);
    }
    function getRiwayatBayar()
    {
        $request = $this->request;
        $datatbel = $this->keuanganService->getRiwayatBayar($request);;

        return $datatbel;
    }
    ################################## end of untuk list riwayat bayar ##########################
    ################################## untuk jatuh tempo ##########################
    function getAllJatuhTempo()
    {
        $id_proyek = $this->request->getVar('id_proyek');
        $datatbel = $this->keuanganService->getAllJatuhTempo($id_proyek);
        return $this->response->setJSON($datatbel);;
    }
    protected function num($d)
    {
        // $d = str_replace('.', "", $d);
        $d = str_replace(',', "", $d);

        return $d;
    }
}

<?php

namespace App\Services;

use App\Repositories\KavlingRepository;
use App\Repositories\HargaJualRepository;
use App\Repositories\TransaksiRepository;
use App\Repositories\KeuanganRepository;
use App\Repositories\LogPembayaranRepository;
use App\Repositories\SpptbRepository;
use App\Services\StorageService;

use App\Services\KonsumenService;
use App\Services\KeuanganService;
use App\Repositories\NotifRepository;

use App\Models\MkdtModel;
use App\Models\KavlingModel;


class TransaksiService
{
    protected $mkdt;
    protected $kavling;
    protected $db;
    protected $konsumenService;
    protected $keuanganService;
    protected $notif;
    protected $kavlingRepo;
    protected $hargaJualRepo;
    protected $transaksiRepo;
    protected $keuRepo;
    protected $logRepo;
    protected $spptbRepo;
    protected $storageService;

    // $kavlingRepo,
    //         $hargaRepo,
    //         $mkdtRepo,
    //         $keuRepo,
    //         $logRepo,
    //         $spptbRepo,
    //         $security
    public function __construct()
    {
        $this->db = \Config\Database::connect();

        $this->konsumenService = new KonsumenService();
        $this->keuanganService = new KeuanganService();
        $this->notif = new NotifRepository();

        $this->kavlingRepo = new KavlingRepository();
        $this->hargaJualRepo = new HargaJualRepository($this->db);
        $this->transaksiRepo = new TransaksiRepository(); //mkdt repo
        $this->keuRepo = new KeuanganRepository();
        $this->logRepo = new LogPembayaranRepository();
        $this->spptbRepo = new SpptbRepository($this->db);
        $this->storageService = new StorageService();

        $this->mkdt = new MkdtModel();
        $this->kavling = new KavlingModel();
    }

    /**
     * @return array{
     *   data: ?object,
     *   hj: ?object,
     *   diskresi: ?object,
     *   tagihan: array,
     *   log_pembayaran: array,
     *   list_spptb: array,
     *   token: string
     * }
     */
    public function getBundle(?int $idMkdt, ?int $idHargajual, ?int $idKavling): array
    {
        $resp = [
            'data' => null,
            'hj' => null,
            'diskresi' => null,
            'tagihan' => [],
            'log_pembayaran' => [],
            'list_spptb' => [],
            'token' => csrf_hash(), // optional
        ];

        if (!empty($idKavling)) {
            $resp['diskresi'] = $this->kavlingRepo->getDiskresiByKavlingId($idKavling);
        }

        if (!empty($idHargajual)) {
            $resp['hj'] = $this->hargaJualRepo->getHargaJualById($idHargajual);
        }

        if (!empty($idMkdt)) {
            $data = $this->transaksiRepo->getKonsumenTransaksi($idMkdt);
            if ($data) {
                $resp['data'] = $data;
                $resp['tagihan'] = $this->keuRepo->getTagihanOnlyByID($idMkdt);
                $resp['log_pembayaran'] = $this->logRepo->getRiwayatBayarById($idMkdt);
                $resp['list_spptb'] = $this->spptbRepo->getLatestByMkdtId($idMkdt, 3);
            }
        }

        return $resp;
    }
    public function getStatusById($id_hargajual, $id_kavling, $id_mkdt)
    {

        //ambil hargajual
        // if (!empty($id_hargajual)) {
        //     $resp['hj'] = $this->hargaJualRepo->getHargaJualById($id_hargajual);
        // }

        //ambil status perintah bangun
        $resp['perintah_bangun'] = $this->kavlingRepo->getPerintahBangun($id_kavling);

        //ambil data transaksi
        $resp['data'] = $this->transaksiRepo->getKonsumenTransaksi($id_mkdt);

        if ($resp['data']) {
            $resp['tagihan'] = $this->keuRepo->getTagihanTurunKPRById($id_mkdt);
        }
        $resp['token'] = csrf_hash();

        return $resp;
    }

    public function saveTransaksi($input)
    {
        $idKavling = trim((string) $input->getPost('id_kavling'));
        $isGantiNama = trim((string) $input->getPost('is_ganti_nama')); // "", "Ganti Nama", "Ganti Kavling"
        $idMkdtOld = $input->getPost('id_mkdt_old');
        $idKonsumenOld = $input->getPost('id_konsumen_old');
        $isDataBaru = (int) ($input->getPost('mkdt_data_baru') ?? 0) === 1;

        // return $idKavling;
        // die();

        $kons = [
            'id_kavling' => $idKavling,
            'id_mkdt' => $input->getPost('id_mkdt') ?? null,
            'no_spptb' => trim((string) ($input->getPost('no_spptb') ?? '')),
            'nama_konsumen' => trim((string) ($input->getPost('nama_konsumen') ?? '')),
            'nik' => trim((string) ($input->getPost('nik_konsumen') ?? '')),
            'alamat_konsumen' => trim((string) ($input->getPost('alamat_konsumen') ?? '')),
            'npwp' => trim((string) ($input->getPost('npwp_konsumen') ?? '')),
            'hp_konsumen' => trim((string) ($input->getPost('hp_konsumen') ?? '')),
            'status_konsumen' => trim((string) ($input->getPost('status_konsumen') ?? '')),
            'email_konsumen' => trim((string) ($input->getPost('email_konsumen') ?? '')),

            'nama_instansi' => trim((string) ($input->getPost('nama_instansi') ?? '')),
            'alamat_instansi' => trim((string) ($input->getPost('alamat_instansi') ?? '')),
            'tel_instansi' => trim((string) ($input->getPost('tel_instansi') ?? '')),
            'email_instansi' => trim((string) ($input->getPost('email_instansi') ?? '')),
            'alamat_surat' => trim((string) ($input->getPost('alamat_surat') ?? '')),
            'pekerjaan' => trim((string) ($input->getPost('pekerjaan') ?? '')),
            'lama_bekerja' => trim((string) ($input->getPost('lama_bekerja') ?? '')),
            'bidang_pekerjaan' => trim((string) ($input->getPost('bidang_pekerjaan') ?? '')),

            'status_pernikahan' => trim((string) ($input->getPost('status_pernikahan') ?? '')),
            'nama_pasangan' => trim((string) ($input->getPost('nama_pasangan') ?? '')),
            'nik_pasangan' => trim((string) ($input->getPost('nik_pasangan') ?? '')),
            'hp_pasangan' => trim((string) ($input->getPost('hp_pasangan') ?? '')),
            'status_pekerjaan_pasangan' => trim((string) ($input->getPost('status_pekerjaan_pasangan') ?? '')),
            'instansi_pasangan' => trim((string) ($input->getPost('instansi_pasangan') ?? '')),

            'sales' => trim((string) ($input->getPost('sales') ?? '')),
            'add_by' => user_id(),
            'edit_by' => user_id(),
        ];

        if ($isDataBaru) {
            $kons['id_mkdt'] = null;
        }

        $statusMkdt = trim((string) $input->getPost('dt-status_mkdt'));
        if ($statusMkdt === 'Batal') {
            $kons['keterangan'] = trim((string) $input->getPost('dt-keterangan_batal'));
        }

        $idKonsumen = $input->getPost('id_konsumen') ?: null;

        // mkdt detail (keuangan/harga)
        $mk = [
            'id_mkdt' => $kons['id_mkdt'],
            'id_konsumen' => null, // set setelah upsert konsumen
            'status_mkdt' => $statusMkdt,
            'is_allin' => $input->getPost('idk-is_allin') ?? 0,
            'harga_allin' => $this->num($input->getPost('mk-harga_allin') ?? 0),
            'id_hargajual' => $input->getPost('idk-harga_akhir') ?? null,
            'tgl_harga' => $this->num($input->getPost('mk-tgl_harga') ?? ''),
            'harga_uang_muka' => $this->num($input->getPost('mk-uang_muka') ?? ''),
            'harga_jual' => $this->num($input->getPost('mk-hargajual') ?? ''),
            'harga_jual_net' => $this->num($input->getPost('mk-hargajual_net') ?? ''),
            'harga_administrasi' => $this->num($input->getPost('mk-biaya_adm') ?? ''),
            'harga_bphtb' => $this->num($input->getPost('mk-bphtb') ?? ''),
            'harga_biaya_proses' => $this->num($input->getPost('mk-biaya_proses') ?? ''),
            'harga_kpr' => $this->num($input->getPost('mk-kpr') ?? ''),
            'harga_ppn' => $this->num($input->getPost('mk-ppn') ?? ''),
            'harga_penambahan' => $this->num($input->getPost('mk-harga_penambahan') ?? ''),
            'harga_penambahan_tanah' => $this->num($input->getPost('mk-harga_penambahan_tanah') ?? ''),
            'harga_sbum' => $this->num($input->getPost('mk-harga_sbum') ?? ''),
            'promo' => trim((string) ($input->getPost('promo') ?? '')),
            'rincian' => $input->getPost('rincian') ?? null,
            'jenis_subsidi' => $input->getPost('jenis_subsidi') ?? null,
            'is_kpr' => $input->getPost('is_kpr') ?? null,
            'is_subsidi' => $input->getPost('is_subsidi') ?? null,
            'booking_fee' => $this->num($input->getPost('dt-booking_fee') ?? ''),
            'booking_tgl' => $input->getPost('dt-booking_tgl') ?? null,
            'keuangan_saved_by' => user_id(),
            'id_kavling' => $idKavling,
            'is_sudah_isi_tagihan' => 1,
        ];

        // tagihan UM
        $um = [
            'id_keuangan' => (array) $input->getPost('id_keuangan'),
            'berita_acara' => (array) $input->getPost('berita_acara'),
            'jatuh_tempo_tgl' => (array) $input->getPost('jatuh_tempo_tgl'),
            'nominal' => (array) $input->getPost('nominal'),
        ];

        // --- 1) Validasi minimal (silakan tambah ruleset CI4 Validator kamu)
        if (!$idKavling) {
            return [
                'token' => csrf_hash(),
                'success' => false,
                'messages' => 'Tidak ada kavling terpilih',
            ];
        }

        if (!$kons['nama_konsumen']) {
            return [
                'token' => csrf_hash(),
                'success' => false,
                'messages' => 'Nama konsumen wajib diisi',
            ];
        }

        // --- 2) Transactional flow
        $db = \Config\Database::connect();
        $db->transException(true);
        try {
            $db->transStart();

            //upload berkas
            $berkas = [
                ['nama' => 'file_ktp', "path" => 'uploads/konsumen/k/'],
                ['nama' => 'file_npwp', "path" => 'uploads/konsumen/n/'],
                ['nama' => 'file_data_diri', "path" => 'uploads/konsumen/d/'],
            ];
            foreach ($berkas as $b) {
                $file = $input->getFile($b['nama']);
                if ($file && $file->isValid() && !$file->hasMoved()) {
                    $pathFile = $this->storageService->store($file, $b['path'] . date('Ymd'));
                    // catat ke table file_spptb
                    $kons[$b['nama']] = $pathFile;
                }
            }

            // 2a. Upsert Konsumen
            $idKonsumen = $this->konsumenService->upsert($idKonsumen, $kons);
            if (!$idKonsumen) {
                throw new \RuntimeException('Gagal menyimpan data konsumen.');
            }



            // 2b. Create/Update MKDT (+handle ganti nama/kavling, update kavling.id_mkdt)
            $mk['id_konsumen'] = $idKonsumen;
            $mkResult = $this->createOrUpdate($kons['id_mkdt'], $mk, [
                'is_ganti_nama' => $isGantiNama === 'Ganti Nama',
                'is_ganti_kavling' => $isGantiNama === 'Ganti Kavling',
                'id_mkdt_old' => $idMkdtOld,
                'id_konsumen_old' => $idKonsumenOld,
                'nama_konsumen' => $kons['nama_konsumen'],
                'id_kavling' => $idKavling,
                'actor_id' => user_id(),
            ]);

            $idMkdt = $mkResult['id_mkdt'];
            $uniqId = $mkResult['uniq_id']; // dipertahankan kalau ganti nama/kavling



            // 2c. Sync Tagihan (UM & BB)
            $this->keuanganService->syncTagihan($idMkdt, $um, user_id());



            // 2d. Upload lampiran (setelah id_mkdt ada)
            $spptbFile = $input->getFile('file_spptb');
            $suratKuasa = $input->getFile('file_surat_kuasa');

            if ($spptbFile && $spptbFile->isValid() && !$spptbFile->hasMoved()) {
                $pathSpptb = $this->storageService->store($spptbFile, 'uploads/spptb/' . date('Ymd'));
                // catat ke table file_spptb
                $db->table('file_spptb')->insert([
                    'id_mkdt' => $idMkdt,
                    'lokasi' => $pathSpptb,
                    'created_at' => date('Y-m-d H:i:s'),
                    'add_by' => user_id(),
                ]);
            }

            if ($suratKuasa && $suratKuasa->isValid() && !$suratKuasa->hasMoved()) {
                $pathKuasa = $this->storageService->store($suratKuasa, 'uploads/spptb/lampiran/' . date('Ymd'));
                // kalau mau disimpan ke kolom lain mkdt/file table, tinggal insert di sini
                $db->table('file_spptb')->insert([
                    'id_mkdt' => $idMkdt,
                    'lokasi' => $pathKuasa,
                    'created_at' => date('Y-m-d H:i:s'),
                    'add_by' => user_id(),
                ]);
            }


            // 2e. Notifikasi
            $pesanNotif = $kons['id_mkdt'] ?
                ('Melakukan perubahan data konsumen : ' . $kons['nama_konsumen']) : ('Booking kavling atas nama : ' . $kons['nama_konsumen']);

            $this->notif->tambah_notif("3;4;9", $pesanNotif, user_id(), $idKavling, $idKonsumen);

            $db->transComplete();

            $resp['success'] = true;
            $resp['messages'] = $kons['id_mkdt'] ? 'Data berhasil diperbaharui' : 'Data berhasil ditambah';
            $resp['id_mkdt'] = $idMkdt;
            $resp['id_konsumen'] = $idKonsumen;
            $resp['uniq_id'] = $uniqId ?? null;
            return $resp;
        } catch (\Throwable $e) {
            if ($db->transStatus() !== false) {
                $db->transRollback();
            }
            return [
                'token' => csrf_hash(),
                'success' => false,
                'messages' => 'Gagal menyimpan data: ' . $e->getMessage(),
            ];
        }
    }

    public function saveStatus($input)
    {
        $idKavling = trim((int) $input->getPost('id_kavling'));
        $idMkdt   = trim((int) $input->getPost('id_mkdt'));
        $statusMkdt = $input->getPost('status_mkdt') ?? '';
        $perintah_bangun = ($input->getPost('perintah_bangun') !== null) ? 1 : 0;
        $sp3k = ($input->getPost('sp3k') !== null) ? 1 : 0;
        $akad = ($input->getPost('akad') !== null) ? 1 : 0;

        if ($idMkdt == '') {
            return [
                'token' => csrf_hash(),
                'success' => false,
                'messages' => 'ID MKDT tidak bisa kosong',
            ];
        }


        if ($statusMkdt == '') {
            return [
                'token' => csrf_hash(),
                'success' => false,
                'messages' => 'Status kavling tidak bisa kosong',
            ];
        }

        $data = [
            'status_mkdt' => $statusMkdt,

            'sp3k' => $sp3k,
            // 'sp3k_oleh' => $sp3k ? user_id() : null,
            'sp3k_no' => $input->getPost('sp3k_no') ?? null,
            'sp3k_tgl' => $input->getPost('sp3k_tgl') ?? null,
            'sp3k_tgl_exp' => $input->getPost('sp3k_tgl_exp') ?? null,

            'harga_kpr' => $this->num($input->getPost('harga_kpr')) ?? 0,
            'harga_kpr_acc' => $this->num($input->getPost('acc_harga_kpr')) ?? 0,
            'harga_penambahan_um' => $this->num($input->getPost('harga_turun_kpr')) ?? 0,

            'rencana_akad_tgl' => $input->getPost('rencana_akad_tgl') ?? null,
            'notaris' => $input->getPost('notaris') ?? null,
            'is_ajb' => $input->getPost('is_ajb') ?? null,
            'akad' => $akad,
            'akad_tgl' => $input->getPost('akad_tgl') ?? null,
            'debitur_no' => $input->getPost('debitur_no') ?? null,
            // 'debitur_tgl' => $input->getPost('debitur_tgl') ?? null,

            'keterangan' => $input->getPost('mkdt_keterangan') ?? null,
            'wawancara_tgl' => $input->getPost('wawancara_tgl') ?? null,
            'wawancara' => $input->getPost('wawancara') ?? null,
            'id_bank' => $input->getPost('id_bank') ?? null,
            'bank' => $input->getPost('bank') ?? null,
        ];

        $perintahBangun = [
            'perintah_bangun' => $perintah_bangun,
            'perintah_bangun_oleh' => $perintah_bangun ? user_id() : null,
            'perintah_bangun_tgl' => $input->getPost('perintah_bangun_tgl') ?? null,
        ];

        // return [
        //     'token' => csrf_hash(),
        //     'success' => false,
        //     'messages' => $data,
        // ];

        // --- 1) Validasi minimal (silakan tambah ruleset CI4 Validator kamu)
        if (!$idKavling) {
            return [
                'token' => csrf_hash(),
                'success' => false,
                'messages' => 'Tidak ada kavling terpilih',
            ];
        }

        // --- 2) Transactional flow
        $db = $this->db;
        $db->transException(true);
        try {
            $db->transStart();

            //upload berkas
            $berkas = [
                ['nama' => 'perintah_bangun_file', "path" => 'uploads/perintah_bangun/'],
                ['nama' => 'sp3k_file', "path" => 'uploads/sp3k/'],
                ['nama' => 'bast_file', "path" => 'uploads/bast/'],
            ];
            foreach ($berkas as $b) {
                $file = $input->getFile($b['nama']);
                if ($file && $file->isValid() && !$file->hasMoved()) {
                    $pathFile = $this->storageService->store($file, $b['path'] . date('Ymd'));
                    // catat ke table file_spptb
                    $data[$b['nama']] = $pathFile;
                }
            }

            $this->mkdt->update($idMkdt, $data);

            $this->kavlingRepo->setPerintahBangun($idKavling, $perintahBangun);
            // $pesanNotif = $kons['id_mkdt'] ?
            //     ('Melakukan perubahan data konsumen : ' . $kons['nama_konsumen']) : ('Booking kavling atas nama : ' . $kons['nama_konsumen']);

            // $this->notif->tambah_notif("3;4;9", $pesanNotif, user_id(), $idKavling, $idKonsumen);

            $db->transComplete();

            $resp['success'] = true;
            $resp['messages'] = 'Data berhasil diperbaharui';
            return $resp;
        } catch (\Throwable $e) {
            if ($db->transStatus() !== false) {
                $db->transRollback();
            }
            return [
                'token' => csrf_hash(),
                'success' => false,
                'messages' => 'Gagal menyimpan data: ' . $e->getMessage(),
            ];
        }
    }

    public function createOrUpdate(?int $idMkdtExisting, array $data, array $opt = []): array
    {
        $uniqId = $data['uniq_id'] ?? uniqid('', true);

        if (empty($idMkdtExisting)) {
            // handle ganti nama/kavling → pakai uniq_id lama
            if (!empty($opt['id_mkdt_old']) && ($opt['is_ganti_nama'] || $opt['is_ganti_kavling'])) {
                $row = $this->db->table('mkdt')->select('uniq_id')->where('id_mkdt', $opt['id_mkdt_old'])->get()->getRow();
                if ($row && $row->uniq_id)
                    $uniqId = $row->uniq_id;

                // set flag pada mkdt lama
                if ($opt['is_ganti_nama']) {
                    $this->mkdt->update($opt['id_mkdt_old'], ['is_ganti_nama' => 'Ganti Nama']);
                }
                if ($opt['is_ganti_kavling']) {
                    $this->mkdt->update($opt['id_mkdt_old'], ['is_ganti_kavling' => 'Ganti Kavling']);
                }

                // status pada konsumen lama + uniq_id
                if (!empty($opt['id_konsumen_old'])) {
                    $this->db->table('konsumen')->where('id_konsumen', $opt['id_konsumen_old'])
                        ->update(['status' => ($opt['is_ganti_nama'] ? 'Ganti Nama' : 'Ganti Kavling'), 'uniq_id' => $uniqId]);
                }
            }

            $data['uniq_id'] = $uniqId;
            $data['add_by'] = $data['add_by'] ?? ($opt['actor_id'] ?? null);
            $data['edit_by'] = $data['edit_by'] ?? ($opt['actor_id'] ?? null);

            if (!$this->mkdt->insert($data)) {
                throw new \RuntimeException('Gagal menginput data booking (mkdt).');
            }
            $idMkdt = (int) $this->mkdt->getInsertID();

            // update kavling.id_mkdt
            $this->kavling->update($opt['id_kavling'], ['id_mkdt' => $idMkdt]);

            return ['id_mkdt' => $idMkdt, 'uniq_id' => $uniqId];
        }

        // update
        $data['edit_by'] = $opt['actor_id'] ?? $data['edit_by'] ?? null;
        if (!$this->mkdt->update($idMkdtExisting, $data)) {
            throw new \RuntimeException('Gagal memperbaharui data booking (mkdt).');
        }

        // Ambil uniq_id eksisting
        $row = $this->db->table('mkdt')->select('uniq_id')->where('id_mkdt', $idMkdtExisting)->get()->getRow();
        $uniqId = $row->uniq_id ?? $uniqId;

        return ['id_mkdt' => (int) $idMkdtExisting, 'uniq_id' => $uniqId];
    }

    function update($id, $data)
    {
        return $this->mkdt->update($id, $data);
    }

    protected function num($d)
    {
        // $d = str_replace('.', "", $d);
        $d = str_replace(',', "", $d);

        return $d;
    }
}

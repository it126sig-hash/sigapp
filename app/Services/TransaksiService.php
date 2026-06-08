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
    protected $fileAccessService;

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
        $this->fileAccessService = new FileAccessService();

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
                if (!empty($data->id_konsumen)) {
                    if (!empty($data->ktp_lok)) {
                        $data->ktp_access_url = $this->fileAccessService->accessUrl('konsumen_ktp', $data->id_konsumen);
                    }
                    if (!empty($data->npwp_lok)) {
                        $data->npwp_access_url = $this->fileAccessService->accessUrl('konsumen_npwp', $data->id_konsumen);
                    }
                    if (!empty($data->data_diri_lok)) {
                        $data->data_diri_access_url = $this->fileAccessService->accessUrl('konsumen_data', $data->id_konsumen);
                    }
                }

                if (!empty($data->id_mkdt)) {
                    $mkdtFiles = [
                        'perintah_bangun_file' => 'mkdt_perintah',
                        'file_spptb'           => 'mkdt_file_spptb',
                        'sp3k_file'            => 'mkdt_sp3k',
                        'bast_file'            => 'mkdt_bast',
                        'surat_batal'          => 'mkdt_surat_batal'
                    ];

                    foreach ($mkdtFiles as $field => $source) {
                        if (!empty($data->$field)) {
                            $data->$field = $this->fileAccessService->accessUrl($source, $data->id_mkdt);
                        }
                    }
                }

                $resp['data'] = $data;
                $resp['tagihan'] = $this->keuRepo->getTagihanOnlyByID($idMkdt);
                $resp['log_pembayaran'] = $this->logRepo->getRiwayatBayarById($idMkdt);
                $resp['list_spptb'] = $this->fileAccessService->addAccessUrlsToRows($this->spptbRepo->getLatestByMkdtId($idMkdt, 3), 'file_spptb');
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

    /**
     * @param array $kons      Data konsumen dari request
     * @param array $mk        Data mkdt (keuangan/harga) dari request
     * @param array $um        Data tagihan dari request
     * @param array $files     File uploads (UploadedFile objects) dari request
     * @param array $opt       Opsional: is_ganti_nama, id_mkdt_old, id_konsumen_old, is_data_baru
     */
    public function saveTransaksi(array $kons, array $mk, array $um, array $files = [], array $opt = [])
    {
        $idKavling      = $kons['id_kavling'] ?? '';
        $isGantiNama    = $opt['is_ganti_nama'] ?? '';
        $idMkdtOld      = $opt['id_mkdt_old'] ?? null;
        $idKonsumenOld  = $opt['id_konsumen_old'] ?? null;
        $idKonsumen     = $opt['id_konsumen'] ?? null;
        $isDataBaru     = !empty($opt['is_data_baru']);

        if ($isDataBaru) {
            $kons['id_mkdt'] = null;
        }

        // id_hargajual disimpan sebagai referensi pricelist, tapi nilai harga
        // sepenuhnya diisi manual oleh user dan tidak di-override dari DB.


        // --- 1) Validasi minimal
        if (!$idKavling) {
            return [
                'token' => csrf_hash(),
                'success' => false,
                'messages' => 'Tidak ada kavling terpilih',
            ];
        }

        if (empty($kons['nama_konsumen'])) {
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

            // Upload berkas konsumen
            foreach (['file_ktp', 'file_npwp', 'file_data_diri'] as $fieldName) {
                $file = $files[$fieldName] ?? null;
                if ($file && $file->isValid() && !$file->hasMoved()) {
                    $pathMap = [
                        'file_ktp' => 'uploads/konsumen/k/',
                        'file_npwp' => 'uploads/konsumen/n/',
                        'file_data_diri' => 'uploads/konsumen/d/',
                    ];
                    $kons[$fieldName] = $this->storageService->store($file, $pathMap[$fieldName] . date('Ymd'));
                }
            }

            // 2a. Upsert Konsumen
            $idKonsumen = $this->konsumenService->upsert($idKonsumen, $kons);
            if (!$idKonsumen) {
                throw new \RuntimeException('Gagal menyimpan data konsumen.');
            }

            // 2b. Create/Update MKDT
            $mk['id_konsumen'] = $idKonsumen;
            $mk['keuangan_saved_by'] = user_id();
            $mkResult = $this->createOrUpdate($kons['id_mkdt'], $mk, [
                'is_ganti_nama'    => $isGantiNama === 'Ganti Nama',
                'is_ganti_kavling' => $isGantiNama === 'Ganti Kavling',
                'id_mkdt_old'      => $idMkdtOld,
                'id_konsumen_old'  => $idKonsumenOld,
                'nama_konsumen'    => $kons['nama_konsumen'],
                'id_kavling'       => $idKavling,
                'actor_id'         => user_id(),
            ]);

            $idMkdt = $mkResult['id_mkdt'];
            $uniqId = $mkResult['uniq_id'];

            // 2c. Sync Tagihan
            $this->keuanganService->syncTagihan($idMkdt, $um, user_id());

            // 2d. Upload lampiran SPPTB
            foreach (['file_spptb' => 'uploads/spptb/', 'file_surat_kuasa' => 'uploads/spptb/lampiran/'] as $field => $path) {
                $file = $files[$field] ?? null;
                if ($file && $file->isValid() && !$file->hasMoved()) {
                    $stored = $this->storageService->store($file, $path . date('Ymd'));
                    $db->table('file_spptb')->insert([
                        'id_mkdt'    => $idMkdt,
                        'lokasi'     => $stored,
                        'created_at' => date('Y-m-d H:i:s'),
                        'add_by'     => user_id(),
                    ]);
                }
            }

            // 2e. Notifikasi
            $pesanNotif = $kons['id_mkdt']
                ? ('Melakukan perubahan data konsumen : ' . $kons['nama_konsumen'])
                : ('Booking kavling atas nama : ' . $kons['nama_konsumen']);
            $this->notif->tambah_notif('3;4;9', $pesanNotif, user_id(), $idKavling, $idKonsumen);

            $db->transComplete();

            return [
                'success'     => true,
                'messages'    => $kons['id_mkdt'] ? 'Data berhasil diperbaharui' : 'Data berhasil ditambah',
                'id_mkdt'     => $idMkdt,
                'id_konsumen' => $idKonsumen,
                'uniq_id'     => $uniqId ?? null,
            ];
        } catch (\Throwable $e) {
            if ($db->transStatus() !== false) {
                $db->transRollback();
            }
            return [
                'token'    => csrf_hash(),
                'success'  => false,
                'messages' => 'Gagal menyimpan data: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * @param array $data         Data status mkdt (status_mkdt, sp3k, akad, kpr, dll)
     * @param array $perintahBangun Data perintah bangun
     * @param array $files        File uploads (UploadedFile objects): perintah_bangun_file, sp3k_file, bast_file
     */
    public function saveStatus(array $data, array $perintahBangun, array $files = [])
    {
        $idKavling  = $data['id_kavling'] ?? '';
        $idMkdt     = $data['id_mkdt'] ?? '';
        $statusMkdt = $data['status_mkdt'] ?? '';

        unset($data['id_kavling'], $data['id_mkdt']);

        if (empty($idMkdt)) {
            return [
                'token'    => csrf_hash(),
                'success'  => false,
                'messages' => 'ID MKDT tidak bisa kosong',
            ];
        }

        if (empty($statusMkdt)) {
            return [
                'token'    => csrf_hash(),
                'success'  => false,
                'messages' => 'Status kavling tidak bisa kosong',
            ];
        }

        if (empty($idKavling)) {
            return [
                'token'    => csrf_hash(),
                'success'  => false,
                'messages' => 'Tidak ada kavling terpilih',
            ];
        }

        // Kalkulasi Turun KPR dari DB — abaikan nilai dari frontend
        $oldData = $this->transaksiRepo->getKonsumenTransaksi((int) $idMkdt);
        $hargaKprDb = $oldData ? (float) $oldData->harga_kpr : 0;
        $accKpr = (float) ($data['harga_kpr_acc'] ?? 0);
        $data['harga_kpr']           = $hargaKprDb;
        $data['harga_kpr_acc']       = $accKpr;
        $data['harga_penambahan_um'] = max(0, $hargaKprDb - $accKpr);

        // --- Transactional flow
        $db = $this->db;
        $db->transException(true);
        try {
            $db->transStart();

            // Upload berkas status
            foreach ($files as $fieldName => $file) {
                if ($file && $file->isValid() && !$file->hasMoved()) {
                    $pathMap = [
                        'perintah_bangun_file' => 'uploads/perintah_bangun/',
                        'sp3k_file'            => 'uploads/sp3k/',
                        'bast_file'            => 'uploads/bast/',
                    ];
                    $path = $pathMap[$fieldName] ?? 'uploads/misc/';
                    $data[$fieldName] = $this->storageService->store($file, $path . date('Ymd'));
                }
            }

            $this->mkdt->update($idMkdt, $data);
            $this->kavlingRepo->setPerintahBangun($idKavling, $perintahBangun);

            $db->transComplete();

            return [
                'success'  => true,
                'messages' => 'Data berhasil diperbaharui',
            ];
        } catch (\Throwable $e) {
            if ($db->transStatus() !== false) {
                $db->transRollback();
            }
            return [
                'token'    => csrf_hash(),
                'success'  => false,
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

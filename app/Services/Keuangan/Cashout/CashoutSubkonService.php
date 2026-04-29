<?php

namespace App\Services\Keuangan\Cashout;

use App\Repositories\Keuangan\Cashout\CashoutSubkonRepo;
use App\Repositories\KavlingRepository;
use App\Services\StorageService;

class CashoutSubkonService
{
    protected $repo;
    protected $kavlingRepo;
    protected $db;
    protected $storageService;

    public function __construct()
    {
        $this->repo = new CashoutSubkonRepo();
        $this->kavlingRepo = new KavlingRepository();
        $this->db = \Config\Database::connect();
        $this->storageService = new StorageService();
    }
    public function getCashoutSubkon(array $id_kavlings)
    {
        $id_cashout_subkon = $this->repo->getListCashoutKavling($id_kavlings);
        if (count($id_cashout_subkon) > 1) {
            return [
                'status' => 'error',
                'message' => 'Tidak bisa memanggil SPK Subkon lebih dari 1 SPK'
            ];
        }
        if (count($id_cashout_subkon) == 0) {
            return [
                'status' => 'success',
                'kavling_subkon' => [],
                'cashout_subkon_detail' => [],
                'detail_kavling' => [],
                'cashout_subkon' => [],
                'subkon' => [],
            ];
        }
        $kavling_subkon = $this->repo->getKavlingSubkonByID($id_cashout_subkon[0]->id_cashout_subkon);
        $detail_kavling = $this->kavlingRepo->getKavlingByIds($kavling_subkon);
        $cashout_subkon_detail = $this->repo->getListItemDetailByIDCashoutsubkon($id_cashout_subkon[0]->id_cashout_subkon);
        $cashout_subkon = $this->repo->getCashoutSubkonByID($id_cashout_subkon[0]->id_cashout_subkon);
        $subkon = $this->repo->getSubkonByID($cashout_subkon['id_subkon']);
        // var_dump($cashout_subkon['id_subkon']);
        // die;

        return [
            'status' => 'success',
            'kavling_subkon' => $kavling_subkon,
            'cashout_subkon_detail' => $cashout_subkon_detail,
            'detail_kavling' => $detail_kavling,
            'cashout_subkon' => $cashout_subkon,
            'subkon' => $subkon,
        ];
    }
    public function saveCashoutSubkon($post)
    {

        $data = $post->getPost();
        $id_subkon = !empty($data['id_subkon']) ? $data['id_subkon'] : null;

        $data_subkon = [
            'nama_subkon' => ucwords($data['nama_subkon']),
            'hp1_subkon' => $data['hp1_subkon'],
            'alamat_subkon' => $data['alamat_subkon'],
        ];

        //define data cashout subkon
        $id_cashout_subkon = !empty($data['id_cashout_subkon']) ? $data['id_cashout_subkon'] : null;

        $data_cashout_subkon = [
            'id_cashout_subkon' => $id_cashout_subkon,
            'id_subkon' => "", //diisi setelah berhasil insert ke table subkon
            'nomor_surat' => $data['nomor_surat'],
            'tanggal_surat' => $data['tanggal_surat'],
            'keterangan' => $data['keterangan_cashout_subkon'],
            'total_nominal' => $this->num($data['total_nominal']),
            'status' => 0,

        ];

        if (empty($id_cashout_subkon)) {
            $data_cashout_subkon['created_at'] = date('Y-m-d H:i:s');
            $data_cashout_subkon['add_by'] = user_id();
        }

        try {
            $this->db->transStart();

            $file = $post->getFile('file_surat');
            if ($file && $file->isValid() && !$file->hasMoved()) {
                $pathFile = $this->storageService->store($file, 'uploads/cashout/subkon/' . date('Ymd'));
                $data_cashout_subkon['file_surat'] = $pathFile;
            }

            //insert subkon
            $id_subkon = $this->repo->upsertSubkon($id_subkon, $data_subkon);
            $data_cashout_subkon['id_subkon'] = $id_subkon;

            //insert cashout 
            $id_cashout_subkon = $this->repo->saveCashoutSubkon($data_cashout_subkon);

            //insert cashout subkon detail
            $data_cashout_subkon_detail = [];
            foreach ($data['id_cashout_subkon_detail'] as $key => $value) {
                $data_cashout_subkon_detail[] = [
                    'id_cashout_subkon' => $id_cashout_subkon,
                    'id_cashout_subkon_detail' => $value,
                    'berita_acara' => $data['berita_acara'][$key],
                    'persentase' => $data['persentase'][$key],
                    'nominal' => $this->num($data['nominal'][$key]),
                ];
            }
            $this->repo->saveCashoutSubkonDetail($data_cashout_subkon_detail);

            //data cashout subkon kavling
            $data_cashout_subkon_kavling = [];
            foreach ($data['id_kavling'] as $key => $value) {
                $data_cashout_subkon_kavling[] = [
                    'id_kavling' => $value,
                    'id_cashout_subkon' => $id_cashout_subkon,
                ];
            }
            $this->repo->saveCashoutSubkonKavling($data_cashout_subkon_kavling);

            $this->db->transComplete();
            return [
                'status' => 'success',
                'message' => 'Data berhasil disimpan',
                'id_kavling' => $data['id_kavling'],
            ];
        } catch (\Throwable $th) {
            $this->db->transRollback();
            return [
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menyimpan data',
                'error' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine(),
                'trace' => $th->getTraceAsString(),
            ];
        }
    }
    public function turunJatuhTempo($id_detail, $tanggal_jatuh_tempo, $berita_acara)
    {
        try {
            $this->db->transStart();

            $detail = $this->repo->updateJatuhTempo($id_detail, $tanggal_jatuh_tempo, $berita_acara);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                return [
                    'status' => 'error',
                    'message' => 'Gagal menyimpan data',
                ];
            }

            // Kirim notifikasi ke divisi Produksi (group_id=7)
            // setelah Jatuh Tempo berhasil diturunkan oleh Keuangan
            if ($detail && !empty($detail['id_cashout_subkon'])) {
                $id_kavlings = $this->repo->getKavlingBySubkonId((int) $detail['id_cashout_subkon']);
                if (!empty($id_kavlings)) {
                    $notif = new \App\Controllers\Notif();
                    foreach ($id_kavlings as $id_kavling) {
                        $notif->tambah_notif(
                            [7],   // Target: Divisi Produksi
                            'Jatuh Tempo Cashout Subkon telah diturunkan untuk: ' . $berita_acara,
                            user_id(),
                            $id_kavling,
                            null,
                            'cashout_subkon'
                        );
                    }
                }
            }

            return [
                'status' => 'success',
                'message' => 'Tanggal Jatuh Tempo berhasil disimpan',
            ];
        } catch (\Throwable $th) {
            $this->db->transRollback();
            return [
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menyimpan data',
                'error' => $th->getMessage(),
            ];
        }
    }

    public function ajukanSPP($id_detail, $spp_no, $spp_tgl)
    {
        try {
            $this->db->transStart();

            $this->repo->updateSPP($id_detail, $spp_no, $spp_tgl);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                return [
                    'status' => 'error',
                    'message' => 'Gagal menyimpan data',
                ];
            }

            return [
                'status' => 'success',
                'message' => 'Pengajuan SPP berhasil disimpan',
            ];
        } catch (\Throwable $th) {
            $this->db->transRollback();
            return [
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menyimpan data',
                'error' => $th->getMessage(),
            ];
        }
    }

    public function ajukanPencairan($id_detail, $pencairan_tgl)
    {
        try {
            $this->db->transStart();

            $this->repo->updatePencairan($id_detail, $pencairan_tgl);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                return [
                    'status' => 'error',
                    'message' => 'Gagal menyimpan data',
                ];
            }

            return [
                'status' => 'success',
                'message' => 'Pengajuan Pencairan berhasil disimpan',
            ];
        } catch (\Throwable $th) {
            $this->db->transRollback();
            return [
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menyimpan data',
                'error' => $th->getMessage(),
            ];
        }
    }

    public function pembayaran($id_detail, $cek_no, $cek_tgl)
    {
        try {
            $this->db->transStart();

            $this->repo->updatePembayaran($id_detail, $cek_no, $cek_tgl);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                return [
                    'status' => 'error',
                    'message' => 'Gagal menyimpan data',
                ];
            }

            return [
                'status' => 'success',
                'message' => 'Pembayaran berhasil disimpan',
            ];
        } catch (\Throwable $th) {
            $this->db->transRollback();
            return [
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menyimpan data',
                'error' => $th->getMessage(),
            ];
        }
    }

    function num($number)
    {
        $v = str_replace(',', "", $number);
        return $v;
    }
}

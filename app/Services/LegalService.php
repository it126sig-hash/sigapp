<?php

namespace App\Services;

use App\Models\LegalModel;
use App\Models\KavlingModel;
use App\Repositories\LegalRepository;

class LegalService
{
    protected $legalModel;
    protected $kavlingModel;
    protected $legalRepository;
    protected $fileAccessService;
    protected $db;

    public function __construct()
    {
        $this->legalModel = new LegalModel();
        $this->kavlingModel = new KavlingModel();
        $this->legalRepository = new LegalRepository();
        $this->fileAccessService = new FileAccessService();
        $this->db = \Config\Database::connect();
    }

    public function simpan(array $data)
    {
        // Validasi dan format angka kembali (pbb_pecah_*)
        if (isset($data['pbb_pecah_jumlah_tagihan'])) {
            $data['pbb_pecah_jumlah_tagihan'] = $this->num($data['pbb_pecah_jumlah_tagihan']);
        }
        if (isset($data['pbb_pecah_njop_bumi'])) {
            $data['pbb_pecah_njop_bumi'] = $this->num($data['pbb_pecah_njop_bumi']);
        }
        if (isset($data['pbb_pecah_njop_bangunan'])) {
            $data['pbb_pecah_njop_bangunan'] = $this->num($data['pbb_pecah_njop_bangunan']);
        }

        $this->db->transStart();

        try {
            if (empty($data['id_legal'])) {
                // Insert data baru
                $data['add_by'] = user_id();
                $data['created_at'] = date('Y-m-d H:i:s');

                if ($this->legalModel->insert($data)) {
                    $insertID = $this->legalModel->getInsertID();
                    $this->kavlingModel->update($data['id_kavling'], ['id_legal' => $insertID]);
                } else {
                    throw new \Exception('Gagal menambahkan data legal.');
                }
            } else {
                // Update data yang sudah ada
                $data['edit_by'] = user_id();
                $data['updated_at'] = date('Y-m-d H:i:s');

                if (!$this->legalModel->update($data['id_legal'], $data)) {
                    throw new \Exception('Gagal memperbaharui data legal.');
                }
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception('Transaksi database gagal.');
            }

            return true;
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    protected function num($d)
    {
        if (!$d) return $d;
        $d = str_replace('.', "", $d);
        $d = str_replace(',', "", $d);
        return $d;
    }

    public function deleteDocument($id)
    {
        $fileDoc = $this->legalRepository->getFileDocById($id);
        if (!$fileDoc) {
            throw new \Exception('Dokumen tidak ditemukan.');
        }

        $this->db->transStart();

        try {
            if (!$this->legalRepository->deleteFileDoc($id)) {
                throw new \Exception('Gagal menghapus data dari database.');
            }

            if (!empty($fileDoc->lokasi)) {
                $str = $this->fileAccessService->existingPath($fileDoc->lokasi);
                if ($str && file_exists($str)) {
                    unlink($str);
                }
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception('Transaksi penghapusan gagal.');
            }

            return true;
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    public function uploadDocument($file, array $data)
    {
        $originalname = $file->getClientName();
        $name = $file->getRandomName();

        $lok = 'uploads/file/' . date('Ymd') . '/';
        $this->fileAccessService->storeAs($file, $lok, $name);

        $data['lokasi'] = $lok . $name;
        $data['default_filename'] = $originalname;
        $data['upload_at'] = date('Y-m-d H:i:s');
        $data['upload_by'] = user_id();

        if (!$this->legalRepository->insertFileDoc($data)) {
            throw new \Exception('Gagal menyimpan referensi file ke database.');
        }

        return true;
    }

    public function editOthers(array $data, int $id_kavling)
    {
        $data['legal_edit_by'] = user_id();
        $data['legal_updated_at'] = date('Y-m-d H:i:s');

        if (!$this->legalRepository->updateOthers($id_kavling, $data)) {
            throw new \Exception('Gagal memperbarui data.');
        }

        return true;
    }
}

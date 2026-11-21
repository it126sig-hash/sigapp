<?php

namespace App\Services\Legal;

use App\Repositories\Legal\LegalRepository;
use App\Services\FileAccessService;

class LegalService
{
    protected LegalRepository $legalRepository;
    protected FileAccessService $fileAccessService;
    protected $db;

    public function __construct(
        ?LegalRepository $legalRepository = null,
        ?FileAccessService $fileAccessService = null
    ) {
        $this->legalRepository   = $legalRepository ?? new LegalRepository();
        $this->fileAccessService = $fileAccessService ?? new FileAccessService();
        $this->db                = \Config\Database::connect();
    }

    public function simpan(array $data): bool
    {
        // Validasi dan format angka kembali (pbb_pecah_*)
        if (isset($data['pbb_pecah_jumlah_tagihan'])) {
            $data['pbb_pecah_jumlah_tagihan'] = $this->cleanNumeric($data['pbb_pecah_jumlah_tagihan']);
        }
        if (isset($data['pbb_pecah_njop_bumi'])) {
            $data['pbb_pecah_njop_bumi'] = $this->cleanNumeric($data['pbb_pecah_njop_bumi']);
        }
        if (isset($data['pbb_pecah_njop_bangunan'])) {
            $data['pbb_pecah_njop_bangunan'] = $this->cleanNumeric($data['pbb_pecah_njop_bangunan']);
        }

        $this->db->transStart();

        try {
            if (empty($data['id_legal'])) {
                // Insert data baru
                $data['add_by'] = user_id();
                $data['created_at'] = date('Y-m-d H:i:s');

                $insertID = $this->legalRepository->insertLegal($data);
                if ($insertID) {
                    $this->legalRepository->updateKavlingLegal((int) $data['id_kavling'], (int) $insertID);
                } else {
                    throw new \Exception('Gagal menambahkan data legal.');
                }
            } else {
                // Update data yang sudah ada
                $data['edit_by'] = user_id();
                $data['updated_at'] = date('Y-m-d H:i:s');

                if (!$this->legalRepository->updateLegal((int) $data['id_legal'], $data)) {
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

    public function cleanNumeric(mixed $value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $clean = trim((string) $value);

        if (strpos($clean, '.') !== false && strpos($clean, ',') !== false) {
            $clean = str_replace('.', '', $clean);
            $clean = str_replace(',', '.', $clean);
        } elseif (strpos($clean, '.') !== false) {
            $clean = str_replace('.', '', $clean);
        } elseif (strpos($clean, ',') !== false) {
            $parts = explode(',', $clean);
            if (count($parts) === 2 && strlen($parts[1]) <= 2) {
                $clean = $parts[0] . '.' . $parts[1];
            } else {
                $clean = str_replace(',', '', $clean);
            }
        }

        $numeric = preg_replace('/[^\d.\-]/', '', $clean);

        return (float) ($numeric !== '' ? $numeric : 0.0);
    }

    protected function num($d)
    {
        return $this->cleanNumeric($d);
    }

    public function deleteDocument($id): bool
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

    public function uploadDocument($file, array $data): bool
    {
        $originalname = $file->getClientName();
        $name         = $file->getRandomName();

        $lok = 'uploads/file/' . date('Ymd') . '/';
        $this->fileAccessService->storeAs($file, $lok, $name);

        $data['lokasi']           = $lok . $name;
        $data['default_filename'] = $originalname;
        $data['upload_at']        = date('Y-m-d H:i:s');
        $data['upload_by']        = user_id();

        if (!$this->legalRepository->insertFileDoc($data)) {
            throw new \Exception('Gagal menyimpan referensi file ke database.');
        }

        return true;
    }

    public function editOthers(array $data, int $id_kavling): bool
    {
        $data['legal_edit_by']    = user_id();
        $data['legal_updated_at'] = date('Y-m-d H:i:s');

        if (!$this->legalRepository->updateOthers($id_kavling, $data)) {
            throw new \Exception('Gagal memperbarui data.');
        }

        return true;
    }
}

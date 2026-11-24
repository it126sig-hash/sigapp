<?php

namespace App\Services\Legal;

use App\Enums\NotificationEvent;
use App\Repositories\Legal\LegalRepository;
use App\Services\FileAccessService;
use App\Services\Notifikasi\NotifikasiService;

class LegalService
{
    public const DOCUMENT_CATEGORIES = [
        ['value' => 2,  'label' => 'AJB'],
        ['value' => 3,  'label' => 'PPJB'],
        ['value' => 5,  'label' => 'PBB'],
        ['value' => 6,  'label' => 'BAST'],
        ['value' => 7,  'label' => 'BPHTB'],
        ['value' => 14, 'label' => 'IMB / PBG'],
        ['value' => 0,  'label' => 'Lainnya'],
    ];

    protected LegalRepository $legalRepository;
    protected FileAccessService $fileAccessService;
    protected NotifikasiService $notifikasiService;
    protected $db;

    public function __construct(
        ?LegalRepository $legalRepository = null,
        ?FileAccessService $fileAccessService = null,
        ?NotifikasiService $notifikasiService = null
    ) {
        $this->legalRepository   = $legalRepository ?? new LegalRepository();
        $this->fileAccessService = $fileAccessService ?? new FileAccessService();
        $this->notifikasiService = $notifikasiService ?? new NotifikasiService();
        $this->db                = \Config\Database::connect();
    }

    public function getDocumentCategories(): array
    {
        return self::DOCUMENT_CATEGORIES;
    }

    public function getDocumentCategoryLabel(int|string|null $kategori): string
    {
        $code = is_numeric($kategori) ? (int) $kategori : 0;
        foreach (self::DOCUMENT_CATEGORIES as $cat) {
            if ($cat['value'] === $code) {
                return $cat['label'];
            }
        }

        return 'Lainnya';
    }

    public function simpan(array $data): int
    {
        $numericFields = [
            'pbb_pecah_jumlah_tagihan',
            'pbb_pecah_njop_bumi',
            'pbb_pecah_njop_bangunan',
            'pbb_pecah_luas_bangunan',
            'sertifikat_split_luas_tanah',
            'bphtb_nominal_disetujui',
            'pph_nominal_bayar',
            'pph_nominal_validasi',
            'pph_nominal_disetujui',
        ];
        foreach ($numericFields as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = $data[$field] === null ? null : $this->cleanNumeric($data[$field]);
            }
        }

        $dateFields = [
            'pbb_pecah_tanggal_bayar',
            'pbb_tgl_pembetulan',
            'pbb_balik_nama_tgl_pengiriman',
            'pbg_tanggal_terbit',
            'pbg_tanggal_pengajuan',
            'pbg_tanggal_kirim',
            'pbg_tanggal_terbit_revisi',
            'bphtb_tanggal_verifikasi',
            'bphtb_jatuh_tempo',
            'bphtb_perpanjang_jatuh_tempo',
            'bphtb_tanggal_pembayaran',
            'bphtb_tanggal_validasi',
            'sertifikat_split_tanggal_terbit',
            'sertifikat_split_tanggal_berakhir',
            'sertifikat_split_tanggal_surat_ukur',
            'sertifikat_balik_nama_tgl_pengiriman',
            'pph_tgl_bayar',
            'pph_tanggal_validasi',
            'pph_tgl_permohonan',
            'pph_tgl_selesai',
            'ajb_tanggal',
            'ajb_tanggal_dikirim',
            'ppjb_tanggal',
        ];
        foreach ($dateFields as $field) {
            if (array_key_exists($field, $data)) {
                $val = is_string($data[$field]) ? trim($data[$field]) : $data[$field];
                if ($val === '' || $val === '0000-00-00' || $val === null) {
                    $data[$field] = null;
                }
            }
        }

        $idKavling = (int) ($data['id_kavling'] ?? 0);
        $idLegal   = (int) ($data['id_legal'] ?? 0);
        if ($idLegal <= 0 && $idKavling > 0) {
            $existingIdLegal = $this->legalRepository->getKavlingLegalId($idKavling);
            if ($existingIdLegal !== null && $existingIdLegal > 0) {
                $idLegal = $existingIdLegal;
            }
        }
        unset($data['id_legal']);

        $this->db->transStart();

        try {
            if ($idLegal <= 0) {
                // Insert data baru
                $data['add_by']     = user_id();
                $data['created_at'] = date('Y-m-d H:i:s');

                $insertID = (int) $this->legalRepository->insertLegal($data);
                if ($insertID > 0) {
                    if ($idKavling > 0) {
                        $this->legalRepository->updateKavlingLegal($idKavling, $insertID);
                    }
                    $idLegal = $insertID;
                } else {
                    throw new \Exception('Gagal menambahkan data legal.');
                }
            } else {
                // Update data yang sudah ada
                $data['edit_by']    = user_id();
                $data['updated_at'] = date('Y-m-d H:i:s');

                if (!$this->legalRepository->updateLegal($idLegal, $data)) {
                    throw new \Exception('Gagal memperbaharui data legal.');
                }
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception('Transaksi database gagal.');
            }

            if ($idKavling > 0) {
                $this->notifyLegalUpdated(
                    $idKavling,
                    'Data legalitas (' . $this->resolveLegalUpdateLabel($data) . ') kavling telah diperbarui.'
                );
            }

            return $idLegal;
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

    public function uploadDocument($file, array $data): int
    {
        $originalname = $file->getClientName();
        $name         = $file->getRandomName();

        $lok = 'uploads/file/' . date('Ymd') . '/';
        $this->fileAccessService->storeAs($file, $lok, $name);

        $data['lokasi']           = $lok . $name;
        $data['default_filename'] = $originalname;
        $data['upload_at']        = date('Y-m-d H:i:s');
        $data['upload_by']        = user_id();

        $insertId = (int) $this->legalRepository->insertFileDoc($data);
        if ($insertId <= 0) {
            throw new \Exception('Gagal menyimpan referensi file ke database.');
        }

        $idKavling = (int) ($data['id_kavling'] ?? 0);
        if ($idKavling > 0) {
            $kategoriLabel = $this->getDocumentCategoryLabel($data['kategori'] ?? 0);
            $fileName      = trim((string) ($data['file_name'] ?? $originalname));
            $docDesc       = $fileName !== '' ? "{$kategoriLabel} ({$fileName})" : $kategoriLabel;
            $this->notifyLegalUpdated(
                $idKavling,
                "Dokumen legal {$docDesc} telah diunggah."
            );
        }

        return $insertId;
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

    protected function resolveLegalUpdateLabel(array $data): string
    {
        $prefixLabels = [
            'ajb_'        => 'AJB',
            'ppjb_'       => 'PPJB',
            'pbb_'        => 'PBB',
            'bphtb_'      => 'BPHTB',
            'pbg_'        => 'IMB / PBG',
            'sertifikat_' => 'Sertifikat',
            'pph_'        => 'PPh',
        ];

        $found = [];
        foreach ($data as $key => $value) {
            if (!is_string($key)) {
                continue;
            }
            foreach ($prefixLabels as $prefix => $label) {
                if (str_starts_with($key, $prefix) && $value !== null && $value !== '') {
                    $found[$label] = true;
                }
            }
        }

        return $found !== [] ? implode(', ', array_keys($found)) : 'Data Legalitas';
    }

    protected function notifyLegalUpdated(int $idKavling, string $message): void
    {
        try {
            $actorId = function_exists('user_id') && user_id() ? (int) user_id() : 0;
            $this->notifikasiService->tambah_notif(
                '3;4;5;10',
                $message,
                $actorId,
                $idKavling,
                null,
                NotificationEvent::LEGAL_DOKUMEN_UPDATED,
                null,
                'siteplan/view?id_kavling=' . $idKavling . '&tab=legal',
                NotificationEvent::LEGAL_DOKUMEN_UPDATED
            );
        } catch (\Throwable $e) {
            log_message('error', '[LegalService::notifyLegalUpdated] {message}', ['message' => $e->getMessage()]);
        }
    }
}

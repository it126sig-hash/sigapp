<?php

namespace App\Services;

use CodeIgniter\HTTP\Files\UploadedFile;

class ProduksiFileService
{
    private const FOTO_CATEGORIES = [
        'prod_foto_konstruksi',
        'prod_foto_exterior',
        'prod_foto_interior',
        'jalan_foto',
        'jalan_foto_update',
        'listrik_pln_foto',
        'listrik_disediakan_dokumen',
        'air_komunal',
        'air_tanah',
        'air_pdam',
    ];

    protected FileAccessService $fileAccessService;

    public function __construct()
    {
        $this->fileAccessService = new FileAccessService();
    }

    /**
     * Upload all foto categories from request files.
     * Returns array of file_produksi rows ready for batch insert.
     */
    public function uploadFotoGroups(array $requestFiles, array $requestVars, int $idKavling): array
    {
        $rows       = [];
        $lok        = 'uploads/produksi/' . date('Ymd') . '/';
        $thumbLok   = 'uploads/produksi/' . date('Ymd') . '/thumbnails/';

        $thumbAbsDir = $this->fileAccessService->privatePath($thumbLok);
        if (!is_dir($thumbAbsDir)) {
            mkdir($thumbAbsDir, 0777, true);
        }

        foreach (self::FOTO_CATEGORIES as $category) {
            if (!isset($requestFiles[$category])) {
                continue;
            }

            $tgl = $requestVars['tgl_' . $category] ?? [];
            $ket = $requestVars['ket_' . $category] ?? [];

            foreach ($requestFiles[$category] as $i => $img) {
                if (!($img instanceof UploadedFile) || $img->getSize() === 0) {
                    continue;
                }

                $name = $img->getRandomName();
                $this->fileAccessService->storeAs($img, $lok, $name);

                \Config\Services::image()
                    ->withFile($this->fileAccessService->privatePath($lok . $name))
                    ->resize(150, 150, true, 'height')
                    ->save($this->fileAccessService->privatePath($thumbLok . $name));

                $rows[] = [
                    'id_kavling'      => $idKavling,
                    'lokasi'          => $lok,
                    'file_name'       => $name,
                    'tgl_capture'     => $tgl[$i] ?? null,
                    'file_keterangan' => $ket[$i] ?? null,
                    'kategori'        => $category,
                    'upload_at'       => date('Y-m-d H:i:s'),
                    'upload_by'       => user_id(),
                ];
            }
        }

        return $rows;
    }

    /**
     * Move a foto to trash folder (soft delete).
     * Returns true on success, false on failure.
     */
    public function moveFotoToTrash(object $file): bool
    {
        $filePath        = $file->lokasi . $file->file_name;
        $trashDir        = 'uploads/produksi/trash/' . date('Ymd') . '/';
        $fileAbsPath     = $this->fileAccessService->existingPath($filePath);
        $trashAbsDir     = $this->fileAccessService->privatePath($trashDir);

        if (!is_dir($trashAbsDir)) {
            mkdir($trashAbsDir, 0777, true);
        }

        $trashAbsPath = $trashAbsDir . DIRECTORY_SEPARATOR . basename($file->file_name);

        return $fileAbsPath && is_file($fileAbsPath) && rename($fileAbsPath, $trashAbsPath);
    }
}

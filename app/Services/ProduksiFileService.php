<?php

namespace App\Services;

use CodeIgniter\HTTP\Files\UploadedFile;

class ProduksiFileService
{
    private const IMAGE_MIME_TYPES = [
        'image/jpg',
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

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
    protected array $fileProduksiFields = [];

    public function __construct()
    {
        $this->fileAccessService = new FileAccessService();
        $this->fileProduksiFields = \Config\Database::connect()->getFieldNames('file_produksi');
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

            $tgl               = $requestVars['tgl_' . $category] ?? [];
            $ket               = $requestVars['ket_' . $category] ?? [];
            $kategoriPekerjaan = $requestVars['kategoriPekerjaan_' . $category] ?? [];
            $tugasPekerjaan    = $requestVars['tugasPekerjaan_' . $category] ?? [];
            $fotoLat           = $requestVars['foto_lat_' . $category] ?? [];
            $fotoLng           = $requestVars['foto_lng_' . $category] ?? [];
            $fotoAccuracy      = $requestVars['foto_accuracy_' . $category] ?? [];
            $coordinateSource  = $requestVars['foto_coordinate_source_' . $category] ?? [];

            foreach ($requestFiles[$category] as $i => $img) {
                if (!($img instanceof UploadedFile) || $img->getSize() === 0) {
                    continue;
                }

                $name = $img->getRandomName();
                $this->fileAccessService->storeAs($img, $lok, $name);
                $this->compressImageIfPossible($lok . $name);

                \Config\Services::image()
                    ->withFile($this->fileAccessService->privatePath($lok . $name))
                    ->resize(150, 150, true, 'height')
                    ->save($this->fileAccessService->privatePath($thumbLok . $name));

                $fileKeterangan = $ket[$i] ?? null;
                if ($category === 'prod_foto_konstruksi') {
                    $parts = array_filter([
                        $kategoriPekerjaan[$i] ?? null,
                        $tugasPekerjaan[$i] ?? null,
                    ], static fn ($value) => $value !== null && $value !== '');
                    $fileKeterangan = !empty($parts) ? implode(' - ', $parts) : $fileKeterangan;
                }

                $row = [
                    'id_kavling'      => $idKavling,
                    'lokasi'          => $lok,
                    'file_name'       => $name,
                    'tgl_capture'     => $tgl[$i] ?? null,
                    'file_keterangan' => $fileKeterangan,
                    'kategori'        => $category,
                    'upload_at'       => date('Y-m-d H:i:s'),
                    'upload_by'       => user_id(),
                ];

                if ($this->hasFileProduksiField('foto_lat')) {
                    $row['foto_lat'] = $this->nullableDecimal($fotoLat[$i] ?? null);
                }
                if ($this->hasFileProduksiField('foto_lng')) {
                    $row['foto_lng'] = $this->nullableDecimal($fotoLng[$i] ?? null);
                }
                if ($this->hasFileProduksiField('foto_accuracy')) {
                    $row['foto_accuracy'] = $this->nullableDecimal($fotoAccuracy[$i] ?? null);
                }
                if ($this->hasFileProduksiField('foto_coordinate_source')) {
                    $row['foto_coordinate_source'] = $coordinateSource[$i] ?? null;
                }

                $rows[] = $row;
            }
        }

        return $rows;
    }

    private function compressImageIfPossible(string $relativePath): void
    {
        $absolutePath = $this->fileAccessService->privatePath($relativePath);
        if (!is_file($absolutePath)) {
            return;
        }

        $mime = mime_content_type($absolutePath);
        if (!in_array($mime, self::IMAGE_MIME_TYPES, true)) {
            return;
        }

        $size = @getimagesize($absolutePath);
        if (!$size) {
            return;
        }

        [$width, $height] = $size;
        $maxDimension = 1920;

        try {
            $image = \Config\Services::image()->withFile($absolutePath);
            if ($width > $maxDimension || $height > $maxDimension) {
                $image->resize($maxDimension, $maxDimension, true, 'auto');
            }
            $image->save($absolutePath, 78);
        } catch (\Throwable $e) {
            log_message('warning', 'Gagal kompres foto produksi: ' . $e->getMessage());
        }
    }

    private function hasFileProduksiField(string $field): bool
    {
        return in_array($field, $this->fileProduksiFields, true);
    }

    private function nullableDecimal($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? (float) $value : null;
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

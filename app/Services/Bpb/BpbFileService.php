<?php

namespace App\Services\Bpb;

use App\Services\FileAccessService;
use CodeIgniter\HTTP\Files\UploadedFile;
use RuntimeException;

class BpbFileService
{
    private const MAX_FILES = 5;
    private const MAX_BYTES = 5242880;
    private const ALLOWED = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];

    public function __construct(private ?FileAccessService $files = null)
    {
        $this->files ??= new FileAccessService();
    }

    /** @return array<int, array<string,mixed>> */
    public function storeUploads(array $uploads, int $bpbId, string $category, int $userId, int $existingCount = 0): array
    {
        $uploads = array_values(array_filter($uploads, static fn($f): bool => $f instanceof UploadedFile && $f->getError() !== UPLOAD_ERR_NO_FILE));
        if ($existingCount + count($uploads) > self::MAX_FILES) {
            throw new RuntimeException('Maksimal 5 file untuk setiap tahap.');
        }
        $stored = [];
        try {
            foreach ($uploads as $file) {
                if (! $file->isValid() || $file->getSize() > self::MAX_BYTES) {
                    throw new RuntimeException('File tidak valid atau melebihi 5 MB.');
                }
                $mime = strtolower((string) $file->getMimeType());
                if (! in_array($mime, self::ALLOWED, true)) {
                    throw new RuntimeException('Format file harus JPG, PNG, WEBP, atau PDF.');
                }
                $path = $this->files->store($file, "bpb/{$bpbId}/{$category}");
                if ($mime !== 'application/pdf') {
                    $this->compressImage($path);
                    $mime = mime_content_type($this->files->privatePath($path)) ?: $mime;
                }
                $stored[] = [
                    'bpb_id' => $bpbId, 'category' => $category, 'logical_path' => $path,
                    'original_name' => basename((string) $file->getClientName()), 'mime_type' => $mime,
                    'file_size' => filesize($this->files->privatePath($path)) ?: 0,
                    'file_sha256' => hash_file('sha256', $this->files->privatePath($path)),
                    'uploaded_by' => $userId, 'created_at' => date('Y-m-d H:i:s'),
                ];
            }
        } catch (\Throwable $e) {
            $this->cleanup(array_column($stored, 'logical_path'));
            throw $e;
        }
        return $stored;
    }

    public function storeCanvas(string $dataUrl, string $logicalDir): string
    {
        if (! preg_match('#^data:image/png;base64,([A-Za-z0-9+/=]+)$#', trim($dataUrl), $matches)) {
            throw new RuntimeException('Gambar tanda tangan tidak valid.');
        }
        $binary = base64_decode($matches[1], true);
        if ($binary === false || strlen($binary) < 100 || strlen($binary) > 2097152 || ! str_starts_with($binary, "\x89PNG")) {
            throw new RuntimeException('Gambar tanda tangan tidak valid atau terlalu besar.');
        }
        $name = bin2hex(random_bytes(16)) . '.png';
        $path = rtrim($logicalDir, '/') . '/' . $name;
        $absolute = $this->files->privatePath($path);
        if (! is_dir(dirname($absolute))) {
            mkdir(dirname($absolute), 0775, true);
        }
        if (file_put_contents($absolute, $binary, LOCK_EX) === false) {
            throw new RuntimeException('Gagal menyimpan tanda tangan.');
        }
        return $path;
    }

    public function copyPrivate(string $sourceLogicalPath, string $logicalDir): string
    {
        $source = $this->files->privatePath($sourceLogicalPath);
        if (! is_file($source)) {
            throw new RuntimeException('Tanda tangan profil tidak tersedia.');
        }
        $path = rtrim($logicalDir, '/') . '/' . bin2hex(random_bytes(16)) . '.png';
        $target = $this->files->privatePath($path);
        if (! is_dir(dirname($target))) {
            mkdir(dirname($target), 0775, true);
        }
        if (! copy($source, $target)) {
            throw new RuntimeException('Gagal menyalin tanda tangan profil.');
        }
        return $path;
    }

    public function cleanup(array $logicalPaths): void
    {
        foreach ($logicalPaths as $path) {
            try {
                $absolute = $this->files->privatePath((string) $path);
                if (is_file($absolute)) {
                    unlink($absolute);
                }
            } catch (\Throwable) {
            }
        }
    }

    private function compressImage(string $logicalPath): void
    {
        $path = $this->files->privatePath($logicalPath);
        $image = \Config\Services::image()->withFile($path);
        $properties = $image->getProperties(true);
        if (($properties['width'] ?? 0) > 1920 || ($properties['height'] ?? 0) > 1920) {
            $image->resize(1920, 1920, true, 'auto');
        }
        $image->save($path, 78);
    }
}

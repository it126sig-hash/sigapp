<?php
namespace App\Services;

use CodeIgniter\Files\File;
use CodeIgniter\HTTP\Files\UploadedFile;

class StorageService
{
    /**
     * Simpan file ke subdir, buat folder jika belum ada, kembalikan path relatif yg disimpan.
     */
    public function store(UploadedFile $file, string $subdir): string
    {
        $subdir = rtrim($subdir, '/').'/';
        if (!is_dir($subdir)) {
            @mkdir($subdir, 0775, true);
        }
        $newName = $file->getRandomName();
        $file->move($subdir, $newName);
        return $subdir . $newName;
    }
}

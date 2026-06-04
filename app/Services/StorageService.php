<?php
namespace App\Services;

use CodeIgniter\HTTP\Files\UploadedFile;

class StorageService
{
    protected FileAccessService $fileAccessService;

    public function __construct()
    {
        $this->fileAccessService = new FileAccessService();
    }

    /**
     * Simpan file ke storage privat, kembalikan logical path yang tetap kompatibel dengan data lama.
     */
    public function store(UploadedFile $file, string $subdir): string
    {
        return $this->fileAccessService->store($file, $subdir);
    }
}

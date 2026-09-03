<?php

namespace App\Services;

use App\Models\ProyekModel;

class NotificationIconService
{
    private string $cachePath;

    public function __construct()
    {
        $this->cachePath = WRITEPATH . 'cache' . DIRECTORY_SEPARATOR . 'notif-icons';
        if (! is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
    }

    public function getIconPath(int $idProyek): string
    {
        $defaultIcon = ROOTPATH . 'public/assets/images/pwa/icon-192.png';
        if ($idProyek <= 0) {
            return $defaultIcon;
        }

        $cacheFile = $this->cachePath . DIRECTORY_SEPARATOR . $idProyek . '.png';
        if (file_exists($cacheFile)) {
            return $cacheFile;
        }

        $proyekModel = new ProyekModel();
        $proyek = $proyekModel->find($idProyek);
        if (! $proyek || empty($proyek->logo)) {
            return $defaultIcon;
        }

        $fileAccessService = new FileAccessService();
        $originalPath = $fileAccessService->existingPath($proyek->logo);

        if (! $originalPath || ! file_exists($originalPath)) {
            return $defaultIcon;
        }

        try {
            // Generate 192x192 PNG
            $image = \Config\Services::image()
                ->withFile($originalPath)
                ->fit(192, 192, 'center')
                ->save($cacheFile, 90);

            if (file_exists($cacheFile)) {
                return $cacheFile;
            }
        } catch (\Throwable $th) {
            log_message('error', 'Failed to generate notification icon for project ' . $idProyek . ': ' . $th->getMessage());
        }

        return $defaultIcon;
    }
}

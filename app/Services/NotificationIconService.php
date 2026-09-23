<?php

namespace App\Services;

use App\Models\ProyekModel;

class NotificationIconService
{
    private ImageThumbnailService $thumbnailService;

    public function __construct()
    {
        $this->thumbnailService = new ImageThumbnailService();
    }

    public function getIconPath(int $idProyek): string
    {
        $defaultIcon = ROOTPATH . 'public/assets/images/pwa/icon-192.png';
        if ($idProyek <= 0) {
            return $defaultIcon;
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

        $thumb = $this->thumbnailService->getThumbnail($originalPath, 192, 192, 'center', 90);

        return $thumb ?: $defaultIcon;
    }
}

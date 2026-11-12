<?php

namespace App\Controllers\Web\Notifikasi;

use App\Controllers\BaseController;
use App\Services\Notifikasi\NotificationIconService;
use App\Services\Notifikasi\NotificationNavigationService;

class NotifikasiController extends BaseController
{
    protected NotificationNavigationService $navigationService;
    protected NotificationIconService $iconService;

    public function __construct(
        ?NotificationNavigationService $navigationService = null,
        ?NotificationIconService $iconService = null
    ) {
        $this->navigationService = $navigationService ?? new NotificationNavigationService();
        $this->iconService = $iconService ?? new NotificationIconService();
    }

    public function open(int $notificationId)
    {
        $userId = function_exists('user_id') ? (int) user_id() : 0;
        $url = $this->navigationService->processOpen($notificationId, $userId);

        return redirect()->to($url);
    }

    public function icon(int $idProyek)
    {
        $path = $this->iconService->getIconPath($idProyek);
        $mime = is_file($path) ? mime_content_type($path) : 'image/png';

        $this->response->setContentType($mime ?: 'image/png');
        $this->response->setHeader('Cache-Control', 'public, max-age=86400');
        if (is_file($path)) {
            $this->response->setHeader('ETag', md5_file($path));
            return $this->response->setBody(file_get_contents($path));
        }

        return $this->response->setStatusCode(404);
    }
}

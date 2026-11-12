<?php

namespace App\Controllers;

/**
 * @deprecated Gunakan \App\Controllers\Api\Notifikasi\NotifikasiController atau \App\Controllers\Web\Notifikasi\NotifikasiController
 */
class Notif extends \App\Controllers\Api\Notifikasi\NotifikasiController
{
    public function icon(int $idProyek)
    {
        $webController = new \App\Controllers\Web\Notifikasi\NotifikasiController();
        $webController->initController($this->request, $this->response, $this->logger);

        return $webController->icon($idProyek);
    }

    public function open(int $notificationId)
    {
        $webController = new \App\Controllers\Web\Notifikasi\NotifikasiController();
        $webController->initController($this->request, $this->response, $this->logger);

        return $webController->open($notificationId);
    }
}

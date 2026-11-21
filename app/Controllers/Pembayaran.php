<?php

namespace App\Controllers;

use App\Controllers\Web\Keuangan\PrintController as WebPrintController;

/** @deprecated Zero-Downtime Shim — gunakan \App\Controllers\Api\Keuangan\PembayaranController atau \App\Controllers\Web\Keuangan\PrintController */
class Pembayaran extends \App\Controllers\Api\Keuangan\PembayaranController
{
    public function print()
    {
        $web = new WebPrintController();
        $web->initController($this->request, $this->response, $this->logger);

        return $web->printPembayaran();
    }

    public function printUm()
    {
        $web = new WebPrintController();
        $web->initController($this->request, $this->response, $this->logger);

        return $web->printPembayaranUm();
    }
}

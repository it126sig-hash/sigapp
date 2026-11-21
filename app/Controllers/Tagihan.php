<?php

namespace App\Controllers;

use App\Controllers\Web\Keuangan\TagihanController as WebTagihanController;

/** @deprecated Zero-Downtime Shim — gunakan \App\Controllers\Api\Keuangan\TagihanController atau \App\Controllers\Web\Keuangan\TagihanController */
class Tagihan extends \App\Controllers\Api\Keuangan\TagihanController
{
    public function listTagihan()
    {
        $web = new WebTagihanController();
        $web->initController($this->request, $this->response, $this->logger);

        return $web->listTagihan();
    }

    public function riwayatBayarIndex()
    {
        $web = new WebTagihanController();
        $web->initController($this->request, $this->response, $this->logger);

        return $web->riwayatBayarIndex();
    }
}

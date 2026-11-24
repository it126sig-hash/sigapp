<?php

namespace App\Controllers;

use App\Controllers\Web\Keuangan\PencairanAkadController as WebPencairanAkadController;

/** @deprecated Zero-Downtime Shim — gunakan \App\Controllers\Api\Keuangan\PencairanAkadController atau \App\Controllers\Web\Keuangan\PencairanAkadController */
class PencairanAkad extends \App\Controllers\Api\Keuangan\PencairanAkadController
{
    public function listHasilAkad()
    {
        $web = new WebPencairanAkadController();
        $web->initController($this->request, $this->response, $this->logger);

        return $web->listHasilAkad();
    }

    public function importForm()
    {
        $web = new WebPencairanAkadController();
        $web->initController($this->request, $this->response, $this->logger);

        return $web->importForm();
    }

    public function exportTemplate()
    {
        $web = new WebPencairanAkadController();
        $web->initController($this->request, $this->response, $this->logger);

        return $web->exportTemplate();
    }
}

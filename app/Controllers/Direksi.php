<?php

namespace App\Controllers;

use App\Controllers\Web\Keuangan\DireksiController as WebDireksiController;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/** @deprecated Zero-Downtime Shim — gunakan \App\Controllers\Api\Keuangan\DireksiController atau \App\Controllers\Web\Keuangan\DireksiController */
class Direksi extends \App\Controllers\Api\Keuangan\DireksiController
{
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        log_message('warning', 'DEPRECATED_KEUANGAN_HIT: ' . static::class . ' via ' . $request->getUri()->getPath());
    }

    public function tagihan()
    {
        $web = new WebDireksiController();
        $web->initController($this->request, $this->response, $this->logger);

        return $web->tagihan();
    }

    public function export_xlsx()
    {
        $web = new WebDireksiController();
        $web->initController($this->request, $this->response, $this->logger);

        return $web->export_xlsx();
    }
}

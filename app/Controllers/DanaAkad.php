<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/** @deprecated Zero-Downtime Shim — gunakan \App\Controllers\Api\Keuangan\DanaJaminanController */
class DanaAkad extends \App\Controllers\Api\Keuangan\DanaJaminanController
{
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        log_message('warning', 'DEPRECATED_KEUANGAN_HIT: ' . static::class . ' via ' . $request->getUri()->getPath());
    }
}

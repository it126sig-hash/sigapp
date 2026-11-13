<?php

namespace App\Controllers\Keuangan\Cashout;

use App\Controllers\Api\Keuangan\Cashout\CashoutKavlingController as ApiCashoutKavlingController;
use App\Controllers\BaseController;
use App\Controllers\Web\Keuangan\Cashout\CashoutKavlingController as WebCashoutKavlingController;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/** @deprecated Zero-Downtime Shim — gunakan \App\Controllers\Web\Keuangan\Cashout\CashoutKavlingController atau \App\Controllers\Api\Keuangan\Cashout\CashoutKavlingController */
class CashoutKavling extends BaseController
{
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        log_message('warning', 'DEPRECATED_KEUANGAN_HIT: ' . static::class . ' via ' . $request->getUri()->getPath());
    }

    public function index()
    {
        $web = new WebCashoutKavlingController();
        $web->initController($this->request, $this->response, $this->logger);

        return $web->index();
    }

    public function getDataTables()
    {
        $api = new ApiCashoutKavlingController();
        $api->initController($this->request, $this->response, $this->logger);

        return $api->getDataTables();
    }

    public function getDetailList()
    {
        $api = new ApiCashoutKavlingController();
        $api->initController($this->request, $this->response, $this->logger);

        return $api->getDetailList();
    }
}

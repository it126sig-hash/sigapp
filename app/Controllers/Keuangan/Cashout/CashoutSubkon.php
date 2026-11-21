<?php

namespace App\Controllers\Keuangan\Cashout;

use App\Controllers\Api\Keuangan\Cashout\CashoutSubkonController as ApiCashoutSubkonController;
use App\Controllers\BaseController;
use App\Controllers\Web\Keuangan\Cashout\CashoutSubkonController as WebCashoutSubkonController;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/** @deprecated Zero-Downtime Shim — gunakan \App\Controllers\Web\Keuangan\Cashout\CashoutSubkonController atau \App\Controllers\Api\Keuangan\Cashout\CashoutSubkonController */
class CashoutSubkon extends BaseController
{
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        log_message('warning', 'DEPRECATED_KEUANGAN_HIT: ' . static::class . ' via ' . $request->getUri()->getPath());
    }

    public function index()
    {
        $web = new WebCashoutSubkonController();
        $web->initController($this->request, $this->response, $this->logger);

        return $web->index();
    }

    public function getDataTables()
    {
        return $this->apiController()->getDataTables();
    }

    public function getDetailList()
    {
        return $this->apiController()->getDetailList();
    }

    public function get()
    {
        return $this->apiController()->get();
    }

    public function save()
    {
        return $this->apiController()->save();
    }

    public function turunJatuhTempo()
    {
        return $this->apiController()->turunJatuhTempo();
    }

    public function ajukanSPP()
    {
        return $this->apiController()->ajukanSPP();
    }

    public function ajukanPencairan()
    {
        return $this->apiController()->ajukanPencairan();
    }

    public function pembayaran()
    {
        return $this->apiController()->pembayaran();
    }

    public function getHistory()
    {
        return $this->apiController()->getHistory();
    }

    private function apiController(): ApiCashoutSubkonController
    {
        $api = new ApiCashoutSubkonController();
        $api->initController($this->request, $this->response, $this->logger);

        return $api;
    }
}

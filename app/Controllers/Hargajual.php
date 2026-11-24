<?php

namespace App\Controllers;

use App\Controllers\Api\Transaksi\HargajualController as ApiHargajualController;
use App\Controllers\Web\Transaksi\HargajualController as WebHargajualController;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * @deprecated Legacy shim. Use App\Controllers\Web\Transaksi\HargajualController or App\Controllers\Api\Transaksi\HargajualController.
 */
class Hargajual extends BaseController
{
    protected WebHargajualController $webController;
    protected ApiHargajualController $apiController;

    public function __construct()
    {
        $this->webController = new WebHargajualController();
        $this->apiController = new ApiHargajualController();
    }

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->webController->initController($request, $response, $logger);
        $this->apiController->initController($request, $response, $logger);
    }

    public function index()
    {
        return $this->webController->index();
    }

    public function getDataTable()
    {
        return $this->apiController->getDataTable();
    }

    public function getAll()
    {
        return $this->apiController->getAll();
    }

    public function getOne()
    {
        return $this->apiController->getOne();
    }

    public function add()
    {
        return $this->apiController->add();
    }

    public function edit($id = null)
    {
        return $this->apiController->edit($id);
    }

    public function remove()
    {
        return $this->apiController->remove();
    }

    public function set_harga()
    {
        return $this->apiController->set_harga();
    }
}

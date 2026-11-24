<?php

namespace App\Controllers;

use App\Controllers\Api\Produksi\ChecklistItemController as ApiChecklistItemController;
use App\Controllers\Web\Produksi\ChecklistItemController as WebChecklistItemController;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * @deprecated Legacy shim. Use App\Controllers\Web\Produksi\ChecklistItemController or App\Controllers\Api\Produksi\ChecklistItemController.
 */
class ChecklistItem extends BaseController
{
    protected WebChecklistItemController $webController;
    protected ApiChecklistItemController $apiController;

    public function __construct()
    {
        $this->webController = new WebChecklistItemController();
        $this->apiController = new ApiChecklistItemController();
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
}

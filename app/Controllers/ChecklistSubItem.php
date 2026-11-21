<?php

namespace App\Controllers;

use App\Controllers\Api\Produksi\ChecklistSubItemController as ApiChecklistSubItemController;
use App\Controllers\Web\Produksi\ChecklistSubItemController as WebChecklistSubItemController;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * @deprecated Legacy shim. Use App\Controllers\Web\Produksi\ChecklistSubItemController or App\Controllers\Api\Produksi\ChecklistSubItemController.
 */
class ChecklistSubItem extends BaseController
{
    protected WebChecklistSubItemController $webController;
    protected ApiChecklistSubItemController $apiController;

    public function __construct()
    {
        $this->webController = new WebChecklistSubItemController();
        $this->apiController = new ApiChecklistSubItemController();
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

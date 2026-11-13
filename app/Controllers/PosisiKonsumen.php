<?php

namespace App\Controllers;

use App\Controllers\Api\Mkdt\PosisiKonsumenController as ApiPosisiKonsumenController;
use App\Controllers\Web\Mkdt\PosisiKonsumenController as WebPosisiKonsumenController;
use App\Services\Mkdt\PosisiKonsumenService;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * @deprecated Legacy shim. Use App\Controllers\Web\Mkdt\PosisiKonsumenController or App\Controllers\Api\Mkdt\PosisiKonsumenController.
 */
class PosisiKonsumen extends BaseController
{
    protected WebPosisiKonsumenController $webController;
    protected ApiPosisiKonsumenController $apiController;
    protected PosisiKonsumenService $posisiKonsumenService;

    public function __construct()
    {
        $this->posisiKonsumenService = new PosisiKonsumenService();
        $this->webController         = new WebPosisiKonsumenController($this->posisiKonsumenService);
        $this->apiController         = new ApiPosisiKonsumenController($this->posisiKonsumenService);
    }

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->webController->initController($request, $response, $logger);
        $this->apiController->initController($request, $response, $logger);
    }

    public function index($status = null)
    {
        return $this->webController->index($status);
    }

    public function getDataTables($status = null)
    {
        return $this->apiController->getDataTables($status);
    }

    public function getDataTablesAkad()
    {
        return $this->apiController->getDataTablesAkad();
    }

    public function getDataTablesBatal()
    {
        return $this->apiController->getDataTablesBatal();
    }

    public function exportPoskon($tipe = 'xlsx', $status = 'aktif')
    {
        return $this->apiController->exportPoskon($tipe, $status);
    }

    public function getRiwayatExport($status = 'Aktif')
    {
        return $this->apiController->getRiwayatExport($status);
    }

    /**
     * @deprecated Use App\Controllers\Api\Mkdt\PosisiKonsumenController::exportPoskon('pdf', $status).
     */
    public function exportPoskonPdf($status = 'aktif')
    {
        return $this->apiController->exportPoskon('pdf', $status);
    }

    /**
     * @deprecated Use App\Controllers\Api\Mkdt\PosisiKonsumenController::exportPoskon('xlsx', $status).
     */
    public function exportPoskonXlsx($status = 'aktif')
    {
        return $this->apiController->exportPoskon('xlsx', $status);
    }

    /**
     * @deprecated Use App\Services\Mkdt\PosisiKonsumenService::format_tgl().
     */
    public function format_tgl($tgl)
    {
        return $this->posisiKonsumenService->format_tgl($tgl);
    }

    /**
     * @deprecated Use App\Services\Mkdt\PosisiKonsumenService::is_active().
     */
    public function is_active($id, $texts, $textf)
    {
        return $this->posisiKonsumenService->is_active($id, $texts, $textf);
    }
}

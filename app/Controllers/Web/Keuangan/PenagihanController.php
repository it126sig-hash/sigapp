<?php

namespace App\Controllers\Web\Keuangan;

use App\Controllers\BaseController;
use App\Services\Keuangan\PenagihanService;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class PenagihanController extends BaseController
{
    protected PenagihanService $penagihanService;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->penagihanService = new PenagihanService();
    }

    public function download_penagihan()
    {
        $id = trim((string) ($this->request->getVar('id') ?? ''));
        if ($id === '') {
            return $this->response->setStatusCode(404)->setBody('Surat tagihan tidak ditemukan');
        }

        $streamed = $this->penagihanService->streamDownloadPenagihan($id);
        if (! $streamed) {
            return $this->response->setStatusCode(404)->setBody('Surat tagihan tidak ditemukan');
        }

        exit;
    }

    public function doPrint()
    {
        $input = [];
        $contentType = strtolower((string) $this->request->getHeaderLine('Content-Type'));
        if (str_contains($contentType, 'application/json')) {
            $json = $this->request->getJSON(true);
            if (is_array($json)) {
                $input = $json;
            }
        }

        if ($input === []) {
            $vars = $this->request->getVar();
            $input = is_array($vars) ? $vars : [];
        }

        $actorId = function_exists('user_id') ? (int) (user_id() ?? 0) : 0;
        $this->penagihanService->streamDoPrintPdf($input, $actorId);
    }
}

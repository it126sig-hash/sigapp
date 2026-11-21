<?php

namespace App\Controllers\Web\Keuangan\Cashout;

use App\Controllers\BaseController;
use App\Services\Keuangan\Cashout\CashoutKavlingService;
use App\Services\Proyek\ActiveProyekService;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class CashoutKavlingController extends BaseController
{
    protected CashoutKavlingService $cashoutKavlingService;
    protected ActiveProyekService $activeProyekService;

    public function __construct(
        ?CashoutKavlingService $cashoutKavlingService = null,
        ?ActiveProyekService $activeProyekService = null
    ) {
        if ($cashoutKavlingService !== null) {
            $this->cashoutKavlingService = $cashoutKavlingService;
        }
        if ($activeProyekService !== null) {
            $this->activeProyekService = $activeProyekService;
        }
    }

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->cashoutKavlingService ??= new CashoutKavlingService();
        $this->activeProyekService ??= new ActiveProyekService();
    }

    public function index()
    {
        $idProyek = $this->activeProyekService->getActiveId();
        if ($idProyek === null) {
            return redirect()->to(base_url('dashboard'))->with('error', 'Silahkan pilih proyek terlebih dahulu');
        }

        return view('template', [
            'content' => 'cashout_kavling/index',
            'data' => $this->cashoutKavlingService->getIndexData($idProyek),
        ]);
    }
}

<?php

namespace App\Controllers\Web\Keuangan\Cashout;

use App\Controllers\BaseController;
use App\Services\Keuangan\Cashout\CashoutSubkonService;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class CashoutSubkonController extends BaseController
{
    protected CashoutSubkonService $cashoutSubkonService;

    public function __construct(?CashoutSubkonService $cashoutSubkonService = null)
    {
        if ($cashoutSubkonService !== null) {
            $this->cashoutSubkonService = $cashoutSubkonService;
        }
    }

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->cashoutSubkonService ??= new CashoutSubkonService();
    }

    public function index(): string
    {
        return view('template', [
            'content' => 'cashout_subkon/index',
            'data' => $this->cashoutSubkonService->getIndexData(),
        ]);
    }
}

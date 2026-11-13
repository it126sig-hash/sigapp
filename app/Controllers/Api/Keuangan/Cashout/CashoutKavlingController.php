<?php

namespace App\Controllers\Api\Keuangan\Cashout;

use App\Controllers\Api\BaseApiController;
use App\Services\Keuangan\Cashout\CashoutKavlingService;
use App\Services\Proyek\ActiveProyekService;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class CashoutKavlingController extends BaseApiController
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
        helper(['auth', 'active_proyek']);
        $this->cashoutKavlingService ??= new CashoutKavlingService();
        $this->activeProyekService ??= new ActiveProyekService();
    }

    public function meta(): ResponseInterface
    {
        $activeProyek = $this->activeProyekService->resolveAndGet();
        $idProyek = $activeProyek ? (int) $activeProyek->id_proyek : null;

        return $this->success(
            $this->cashoutKavlingService->getMetaPayload($idProyek),
            'Metadata cashout kavling berhasil dimuat.'
        );
    }

    public function getDataTables(): ResponseInterface
    {
        $input = $this->readRequestData();
        if (empty($input['id_proyek'])) {
            $input['id_proyek'] = $this->activeProyekService->getActiveId();
        }

        if ($this->isRawFormat()) {
            return $this->success(
                $this->cashoutKavlingService->getDataTablesRaw($input),
                'Daftar rekap cashout kavling berhasil dimuat.'
            );
        }

        $payload = $this->cashoutKavlingService->getDataTables($input);
        $payload['token'] = $payload['token'] ?? csrf_hash();

        return $this->respond($payload);
    }

    public function getDetailList(): ResponseInterface
    {
        $input = $this->readRequestData();
        $idKavling = (int) ($input['id_kavling'] ?? 0);
        $result = $this->cashoutKavlingService->getDetailList($idKavling);

        if ($this->isRawFormat()) {
            if (($result['status'] ?? 'error') !== 'success') {
                return $this->error((string) ($result['message'] ?? 'ID kavling tidak valid'), 422);
            }

            $items = $result['data'] ?? [];

            return $this->success([
                'items' => $items,
                'meta' => [
                    'total' => count($items),
                ],
            ], 'Detail cashout kavling berhasil dimuat.');
        }

        $result['token'] = $result['token'] ?? csrf_hash();

        return $this->respond($result);
    }
}

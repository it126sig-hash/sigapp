<?php

namespace App\Controllers\Api\Keuangan;

use App\Controllers\Api\BaseApiController;
use App\Services\Keuangan\CashOutService;
use App\Services\Proyek\ActiveProyekService;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class CashOutController extends BaseApiController
{
    protected CashOutService $cashoutService;
    protected ActiveProyekService $activeProyekService;

    public function __construct(
        ?CashOutService $cashoutService = null,
        ?ActiveProyekService $activeProyekService = null
    ) {
        if ($cashoutService !== null) {
            $this->cashoutService = $cashoutService;
        }
        if ($activeProyekService !== null) {
            $this->activeProyekService = $activeProyekService;
        }
    }

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        helper(['auth', 'active_proyek']);
        $this->cashoutService ??= new CashOutService();
        $this->activeProyekService ??= new ActiveProyekService();
    }

    public function meta(): ResponseInterface
    {
        $canManage = function_exists('in_groups') && in_groups(['1', '3']);
        $activeProyek = $this->activeProyekService->resolveAndGet();
        $meta = $this->cashoutService->getMetaPayload();

        return $this->success([
            'active_project' => $activeProyek ? [
                'id' => (int) $activeProyek->id_proyek,
                'name' => (string) ($activeProyek->nama_proyek ?? ''),
            ] : null,
            'list_item' => $meta['list_item'] ?? [],
            'items' => $meta['items'] ?? [],
            'permissions' => [
                'can_view' => true,
                'can_manage' => $canManage,
                'can_create' => $canManage,
                'can_delete' => $canManage,
            ],
        ], 'Metadata cashout berhasil dimuat.');
    }

    public function getListItem(): ResponseInterface
    {
        $input = $this->readRequestData();
        $search = trim((string) ($input['search'] ?? ''));

        if ($this->isRawFormat()) {
            $items = $this->cashoutService->getListItemSelect2($search);

            return $this->success([
                'items' => $items,
                'list_item' => $items,
            ], 'Daftar item cashout berhasil dimuat.');
        }

        return $this->respond([
            'token' => csrf_hash(),
            'list_item' => $this->cashoutService->getListItem($search),
        ]);
    }

    public function getByIDKavling(): ResponseInterface
    {
        $input = $this->readRequestData();
        $idKavling = (int) ($input['id_kavling'] ?? 0);
        $result = $this->cashoutService->getByIDKavling($idKavling);

        if ($this->isRawFormat()) {
            if (! ($result['success'] ?? false)) {
                return $this->error((string) ($result['messages'] ?? 'data tidak ditemukan'), 404);
            }

            return $this->success([
                'konsumen' => $result['konsumen'],
                'biaya_mkdt' => $result['biaya_mkdt'],
                'riwayat_bayar' => $result['riwayat_bayar'],
            ], 'Data cashout kavling berhasil dimuat.');
        }

        if (! ($result['success'] ?? false)) {
            return $this->respond([
                'token' => csrf_hash(),
                'success' => false,
                'messages' => 'data tidak ditemukan',
            ]);
        }

        return $this->respond([
            'token' => csrf_hash(),
            'konsumen' => $result['konsumen'],
            'biaya_mkdt' => $result['biaya_mkdt'],
            'riwayat_bayar' => $result['riwayat_bayar'],
        ]);
    }

    public function insert(): ResponseInterface
    {
        $input = $this->readRequestData();
        $userId = function_exists('user_id') ? (int) (user_id() ?? 0) : 0;
        $result = $this->cashoutService->saveCashOut($input, $userId > 0 ? $userId : null);
        $statusCode = (int) ($result['status_code'] ?? 422);
        unset($result['status_code']);

        if ($this->isRawFormat()) {
            if (! ($result['success'] ?? false)) {
                return $this->error((string) ($result['messages'] ?? 'data gagal disimpan'), $statusCode);
            }

            return $this->successCreated([
                'id_kavling' => $result['id_kavling'] ?? null,
            ], (string) ($result['messages'] ?? 'data berhasil disimpan'));
        }

        return $this->respond(array_merge([
            'token' => csrf_hash(),
        ], $result));
    }

    public function delete($id = null): ResponseInterface
    {
        $input = $this->readRequestData();
        $id = (int) ($id ?? $input['id'] ?? 0);
        $result = $this->cashoutService->deleteCashOut($id);
        $statusCode = (int) ($result['status_code'] ?? 422);
        unset($result['status_code']);

        if ($this->isRawFormat()) {
            if (! ($result['success'] ?? false)) {
                return $this->error((string) ($result['messages'] ?? 'data gagal dihapus'), $statusCode);
            }

            return $this->success([
                'id_kavling' => $result['id_kavling'] ?? null,
            ], (string) ($result['messages'] ?? 'data berhasil dihapus'));
        }

        return $this->respond(array_merge([
            'token' => csrf_hash(),
        ], $result));
    }
}

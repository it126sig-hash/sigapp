<?php

namespace App\Controllers\Api\Mkdt;

use App\Controllers\Api\BaseApiController;
use App\Services\Mkdt\BatalBookingService;
use App\Services\Mkdt\StandingInstructionService;
use App\Services\Mkdt\StockService;
use App\Services\Proyek\ActiveProyekService;
use CodeIgniter\HTTP\ResponseInterface;

class MkdtOperationController extends BaseApiController
{
    private StockService $stockService;
    private StandingInstructionService $siService;
    private BatalBookingService $batalService;

    public function __construct(
        ?StockService $stockService = null,
        ?StandingInstructionService $siService = null,
        ?BatalBookingService $batalService = null
    ) {
        $this->stockService = $stockService ?? new StockService();
        $this->siService    = $siService ?? new StandingInstructionService();
        $this->batalService = $batalService ?? new BatalBookingService();
    }

    public function meta(): ResponseInterface
    {
        $indexData  = $this->stockService->getIndexData();
        $proyek     = $indexData['data']['proyek'] ?? (object) ['id_proyek' => null, 'nama_proyek' => ''];
        $userGroups = $this->getUserGroupIds();

        return $this->success([
            'proyek'           => $proyek,
            'active_proyek_id' => $proyek->id_proyek ?? null,
            'title'            => $indexData['data']['title'] ?? 'List Kavling Stock',
            'user_group_ids'   => $userGroups,
            'is_keuangan'      => in_array(3, $userGroups, true),
        ], 'Metadata operasi MKDT berhasil diambil');
    }

    public function getListStock(): ResponseInterface
    {
        // TODO(full-json): switch default to raw JSON once legacy DataTables views migrate
        $isRaw  = $this->isRawFormat();
        $params = $this->readRequestData();

        if (!isset($params['search'])) {
            $params['search'] = ['value' => '', 'regex' => false];
        }

        if (
            empty($params['id_jalan'])
            && empty($params['id_cluster'])
            && empty($params['id_proyek'])
        ) {
            $params['id_proyek'] = (new ActiveProyekService())->getActiveId();
        }

        $result = $this->stockService->getListStock($params, $isRaw);

        if ($isRaw) {
            return $this->success($result, 'Data kavling stock berhasil diambil');
        }

        return $this->response->setJSON([
            'draw'            => $result['draw'],
            'recordsTotal'    => $result['recordsTotal'],
            'recordsFiltered' => $result['recordsFiltered'],
            'data'            => $result['data'],
            'token'           => csrf_hash(),
        ]);
    }

    public function getSi(): ResponseInterface
    {
        // TODO(full-json): switch default to raw JSON once legacy DataTables views migrate
        $isRaw     = $this->isRawFormat();
        $payload   = $this->readRequestData();
        $idKavling = (int) ($payload['id_kavling'] ?? 0);

        if ($isRaw && $idKavling <= 0) {
            return $this->error('ID kavling tidak valid', 422, ['id_kavling' => 'ID kavling wajib diisi']);
        }

        $rows = $this->siService->getByKavling($idKavling);

        if ($isRaw) {
            return $this->success($rows, 'Data Standing Instruction berhasil diambil');
        }

        return $this->response->setJSON([
            'token' => csrf_hash(),
            'data'  => $rows,
        ]);
    }

    public function saveSi(): ResponseInterface
    {
        // TODO(full-json): switch default to raw JSON once legacy DataTables views migrate
        $isRaw     = $this->isRawFormat();
        $payload   = $this->readRequestData();
        $idKavling = (int) ($payload['id_kavling'] ?? 0);
        $items     = $payload['id-si'] ?? $payload['items'] ?? [];
        $items     = is_array($items) ? $items : [];

        $uploadedFiles = $this->request->getFiles();
        $uploadedFiles = is_array($uploadedFiles) ? $uploadedFiles : [];

        $result = $this->siService->save($idKavling, $items, $uploadedFiles, $this->getActorId());

        if ($isRaw) {
            if (!$result['success']) {
                return $this->error($result['messages'], 422, $result['errors'] ?? null);
            }

            return $this->success(['saved_items' => $result['saved_items']], $result['messages']);
        }

        return $this->response->setJSON([
            'token'    => csrf_hash(),
            'success'  => $result['success'],
            'messages' => $result['messages'],
        ]);
    }

    public function getBatal(): ResponseInterface
    {
        // TODO(full-json): switch default to raw JSON once legacy DataTables views migrate
        $isRaw   = $this->isRawFormat();
        $payload = $this->readRequestData();
        $idMkdt  = (int) ($payload['id_mkdt'] ?? 0);

        if ($isRaw && $idMkdt <= 0) {
            return $this->error('ID MKDT tidak valid', 422, ['id_mkdt' => 'ID MKDT wajib diisi']);
        }

        $result = $this->batalService->getDetail($idMkdt);

        if ($isRaw) {
            if (!$result['found']) {
                return $this->error('Data booking MKDT tidak ditemukan', 404);
            }

            return $this->success([
                'data'        => $result['data'],
                'sudah_bayar' => $result['sudah_bayar'],
                'total_biaya' => $result['total_biaya'],
            ], 'Detail pembatalan booking berhasil diambil');
        }

        return $this->response->setJSON([
            'data'        => $result['found'] ? $result['data'] : (object) [],
            'token'       => csrf_hash(),
            'sudah_bayar' => $result['sudah_bayar'],
            'total_biaya' => $result['total_biaya'],
        ]);
    }

    public function simpanBatal(): ResponseInterface
    {
        // TODO(full-json): switch default to raw JSON once legacy DataTables views migrate
        $isRaw   = $this->isRawFormat();
        $payload = $this->readRequestData();
        $file    = $this->request->getFile('file_surat_batal');

        $result = $this->batalService->cancelBooking(
            $payload,
            $file,
            $this->getActorId(),
            $this->getUserGroupIds()
        );

        if ($isRaw) {
            if (!$result['success']) {
                if (!empty($result['not_found'])) {
                    return $this->error($result['messages'], 404);
                }

                return $this->error($result['messages'], 422, $result['errors'] ?? null);
            }

            return $this->success($result['data'] ?? null, $result['messages']);
        }

        return $this->response->setJSON([
            'token'    => csrf_hash(),
            'success'  => $result['success'],
            'messages' => $result['messages'],
        ]);
    }

    private function getActorId(): int
    {
        if (!function_exists('user_id')) {
            helper('auth');
        }

        return (int) (function_exists('user_id') ? (user_id() ?? 0) : 0);
    }

    /**
     * @return array<int, int>
     */
    private function getUserGroupIds(): array
    {
        if (!function_exists('user')) {
            helper('auth');
        }

        if (function_exists('user') && user()) {
            return array_map('intval', array_keys(user()->getRoles()));
        }

        return [];
    }
}

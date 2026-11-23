<?php

namespace App\Controllers\Api\Produksi;

use App\Controllers\Api\BaseApiController;
use App\Services\Produksi\ChecklistService;
use CodeIgniter\HTTP\ResponseInterface;

class ChecklistItemController extends BaseApiController
{
    protected ChecklistService $checklistService;

    public function __construct(?ChecklistService $checklistService = null)
    {
        $this->checklistService = $checklistService ?? new ChecklistService();
    }

    public function getAll(): ResponseInterface
    {
        $isRaw = $this->request->getGet('format') === 'raw' || $this->request->hasHeader('X-Client-Platform');

        if ($isRaw) {
            $data = $this->checklistService->getAllItems(false);
            return $this->success($data);
        }

        $data = $this->checklistService->getAllItems(true);
        return $this->response->setJSON([
            'token' => csrf_hash(),
            'data'  => $data,
        ]);
    }

    public function getOne(): ResponseInterface
    {
        $data = $this->readRequestData();
        $id   = (int) ($data['id_item'] ?? $data['idItem'] ?? $this->request->getVar('id_item') ?? 0);
        if ($id <= 0) {
            return $this->error('ID item tidak valid', 400);
        }

        $item = $this->checklistService->getItem($id);
        if (!$item) {
            return $this->error('Data item tidak ditemukan', 404);
        }

        $item->token = csrf_hash();
        return $this->response->setJSON($item);
    }

    public function add(): ResponseInterface
    {
        $data   = $this->readRequestData();
        $userId = (int) user_id();
        $result = $this->checklistService->createItem($data, $userId);
        $result['token'] = csrf_hash();

        return $this->response->setJSON($result);
    }

    public function edit($id = null)
    {
        $data = $this->readRequestData();
        $id   = (int) ($id ?? $data['idItem'] ?? $data['id_item'] ?? $this->request->getVar('idItem') ?? $this->request->getVar('id_item') ?? 0);
        if ($id <= 0) {
            return $this->response->setJSON([
                'token'    => csrf_hash(),
                'success'  => false,
                'messages' => 'ID item tidak valid',
            ]);
        }

        $userId = (int) user_id();
        $result = $this->checklistService->updateItem($id, $data, $userId);
        $result['token'] = csrf_hash();

        return $this->response->setJSON($result);
    }

    public function remove(): ResponseInterface
    {
        $data = $this->readRequestData();
        $id   = (int) ($data['id_item'] ?? $data['idItem'] ?? $this->request->getVar('id_item') ?? 0);
        if ($id <= 0) {
            return $this->response->setJSON([
                'token'    => csrf_hash(),
                'success'  => false,
                'messages' => 'ID item tidak valid',
            ]);
        }

        $result = $this->checklistService->deleteItem($id);
        $result['token'] = csrf_hash();

        return $this->response->setJSON($result);
    }
}

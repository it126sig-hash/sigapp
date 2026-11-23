<?php

namespace App\Controllers\Api\Produksi;

use App\Controllers\Api\BaseApiController;
use App\Services\Produksi\ChecklistService;
use CodeIgniter\HTTP\ResponseInterface;

class ChecklistSubItemController extends BaseApiController
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
            $data = $this->checklistService->getAllSubItems(false);
            return $this->success($data);
        }

        $data = $this->checklistService->getAllSubItems(true);
        return $this->response->setJSON([
            'token' => csrf_hash(),
            'data'  => $data,
        ]);
    }

    public function getOne(): ResponseInterface
    {
        $data = $this->readRequestData();
        $id   = (int) ($data['id_subitem'] ?? $data['idSubitem'] ?? $this->request->getVar('id_subitem') ?? 0);
        if ($id <= 0) {
            return $this->error('ID subitem tidak valid', 400);
        }

        $subItem = $this->checklistService->getSubItem($id);
        if (!$subItem) {
            return $this->error('Data subitem tidak ditemukan', 404);
        }

        $subItem->token = csrf_hash();
        return $this->response->setJSON($subItem);
    }

    public function add(): ResponseInterface
    {
        $data   = $this->readRequestData();
        $userId = (int) user_id();
        $result = $this->checklistService->createSubItem($data, $userId);
        $result['token'] = csrf_hash();

        return $this->response->setJSON($result);
    }

    public function edit($id = null)
    {
        $data = $this->readRequestData();
        $id   = (int) ($id ?? $data['idSubitem'] ?? $data['id_subitem'] ?? $this->request->getVar('idSubitem') ?? $this->request->getVar('id_subitem') ?? 0);
        if ($id <= 0) {
            return $this->response->setJSON([
                'token'    => csrf_hash(),
                'success'  => false,
                'messages' => 'ID subitem tidak valid',
            ]);
        }

        $userId = (int) user_id();
        $result = $this->checklistService->updateSubItem($id, $data, $userId);
        $result['token'] = csrf_hash();

        return $this->response->setJSON($result);
    }

    public function remove(): ResponseInterface
    {
        $data = $this->readRequestData();
        $id   = (int) ($data['id_subitem'] ?? $data['idSubitem'] ?? $this->request->getVar('id_subitem') ?? 0);
        if ($id <= 0) {
            return $this->response->setJSON([
                'token'    => csrf_hash(),
                'success'  => false,
                'messages' => 'ID subitem tidak valid',
            ]);
        }

        $result = $this->checklistService->deleteSubItem($id);
        $result['token'] = csrf_hash();

        return $this->response->setJSON($result);
    }
}

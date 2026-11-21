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
        $id = (int) ($this->request->getPost('id_subitem') ?? 0);
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
        $userId = (int) user_id();
        $result = $this->checklistService->createSubItem((array) $this->request->getPost(), $userId);
        $result['token'] = csrf_hash();

        return $this->response->setJSON($result);
    }

    public function edit($id = null)
    {
        $id = (int) ($id ?? $this->request->getPost('idSubitem') ?? $this->request->getPost('id_subitem') ?? 0);
        if ($id <= 0) {
            return $this->response->setJSON([
                'token'    => csrf_hash(),
                'success'  => false,
                'messages' => 'ID subitem tidak valid',
            ]);
        }

        $userId = (int) user_id();
        $result = $this->checklistService->updateSubItem($id, (array) $this->request->getPost(), $userId);
        $result['token'] = csrf_hash();

        return $this->response->setJSON($result);
    }

    public function remove(): ResponseInterface
    {
        $id = (int) ($this->request->getPost('id_subitem') ?? 0);
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

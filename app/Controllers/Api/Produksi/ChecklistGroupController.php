<?php

namespace App\Controllers\Api\Produksi;

use App\Controllers\Api\BaseApiController;
use App\Services\Produksi\ChecklistService;
use CodeIgniter\HTTP\ResponseInterface;

class ChecklistGroupController extends BaseApiController
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
            $data = $this->checklistService->getAllGroups(false);
            return $this->success($data);
        }

        $data = $this->checklistService->getAllGroups(true);
        return $this->response->setJSON([
            'token' => csrf_hash(),
            'data'  => $data,
        ]);
    }

    public function getOne(): ResponseInterface
    {
        $data = $this->readRequestData();
        $id   = (int) ($data['id_group'] ?? $data['idGroup'] ?? $this->request->getVar('id_group') ?? 0);
        if ($id <= 0) {
            return $this->error('ID group tidak valid', 400);
        }

        $group = $this->checklistService->getGroup($id);
        if (!$group) {
            return $this->error('Data group tidak ditemukan', 404);
        }

        $group->token = csrf_hash();
        return $this->response->setJSON($group);
    }

    public function add(): ResponseInterface
    {
        $data   = $this->readRequestData();
        $userId = (int) user_id();
        $result = $this->checklistService->createGroup($data, $userId);
        $result['token'] = csrf_hash();

        return $this->response->setJSON($result);
    }

    public function edit($id = null)
    {
        $data = $this->readRequestData();
        $id   = (int) ($id ?? $data['idGroup'] ?? $data['id_group'] ?? $this->request->getVar('idGroup') ?? $this->request->getVar('id_group') ?? 0);
        if ($id <= 0) {
            return $this->response->setJSON([
                'token'    => csrf_hash(),
                'success'  => false,
                'messages' => 'ID group tidak valid',
            ]);
        }

        $userId = (int) user_id();
        $result = $this->checklistService->updateGroup($id, $data, $userId);
        $result['token'] = csrf_hash();

        return $this->response->setJSON($result);
    }

    public function remove(): ResponseInterface
    {
        $data = $this->readRequestData();
        $id   = (int) ($data['id_group'] ?? $data['idGroup'] ?? $this->request->getVar('id_group') ?? 0);
        if ($id <= 0) {
            return $this->response->setJSON([
                'token'    => csrf_hash(),
                'success'  => false,
                'messages' => 'ID group tidak valid',
            ]);
        }

        $result = $this->checklistService->deleteGroup($id);
        $result['token'] = csrf_hash();

        return $this->response->setJSON($result);
    }
}

<?php

namespace App\Controllers\Api;

use App\Services\ActiveProyekService;
use App\Services\HistoryService;
use CodeIgniter\HTTP\ResponseInterface;

class HistoryController extends BaseApiController
{
    protected HistoryService $historyService;
    protected ActiveProyekService $activeProyekService;

    public function __construct()
    {
        helper(['auth', 'active_proyek']);
        $this->historyService = new HistoryService();
        $this->activeProyekService = new ActiveProyekService();
    }

    public function list(): ResponseInterface
    {
        $filters = [];
        $module = $this->request->getPost('module');
        if (is_array($module)) {
            $filters['module'] = array_values(array_filter(array_map('strval', $module)));
        } elseif ((string) $module !== '') {
            $filters['module'] = (string) $module;
        }

        foreach (['date_from', 'date_to'] as $key) {
            $value = trim((string) ($this->request->getPost($key) ?? ''));
            if ($value !== '') {
                $filters[$key] = $value;
            }
        }

        $requestedProject = (int) ($this->request->getPost('id_proyek') ?: 0);
        if (! in_groups(['1'])) {
            $activeProject = $this->activeProyekService->getActiveId();
            if (! $activeProject || ! $this->activeProyekService->userCanAccess($activeProject, (int) user_id())) {
                return $this->respond([
                    'success' => false,
                    'messages' => 'Proyek aktif tidak valid',
                    'token' => csrf_hash(),
                    'data' => [],
                    'total' => 0,
                    'limit' => 0,
                    'offset' => 0,
                ], 403);
            }
            $filters['id_proyek'] = $activeProject;
        } elseif ($requestedProject > 0) {
            $filters['id_proyek'] = $requestedProject;
        }

        $limit = (int) ($this->request->getPost('limit') ?? 20);
        $offset = (int) ($this->request->getPost('offset') ?? 0);
        $result = $this->historyService->getList($filters, $limit, $offset);

        return $this->respond([
            'success' => true,
            'token' => csrf_hash(),
            'data' => $result['data'],
            'total' => $result['total'],
            'limit' => $result['limit'],
            'offset' => $result['offset'],
        ]);
    }

    public function kavling(): ResponseInterface
    {
        $rules = [
            'id_kavling' => 'required|is_natural_no_zero',
        ];

        if (! $this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $idKavling = (int) $this->request->getPost('id_kavling');
        $module = $this->request->getPost('module');
        $modules = [];
        if (is_array($module)) {
            $modules = array_values(array_filter(array_map('strval', $module)));
        } elseif ((string) $module !== '') {
            $modules = [(string) $module];
        }

        $limit = (int) ($this->request->getPost('limit') ?? $this->request->getPost('history_limit') ?? 15);
        $offset = max(0, (int) ($this->request->getPost('offset') ?? $this->request->getPost('history_offset') ?? 0));

        if (! in_groups(['1'])) {
            $proyekId = $this->historyService->getProyekIdByKavling($idKavling);
            if ($proyekId && ! $this->activeProyekService->userCanAccess($proyekId, (int) user_id())) {
                return $this->respond([
                    'success'  => false,
                    'messages' => 'Anda tidak memiliki akses ke proyek kavling ini.',
                    'token'    => csrf_hash(),
                    'history'  => [],
                    'total'    => 0,
                ], 403);
            }
        }

        $result = $this->historyService->getByKavling($idKavling, $modules, $limit, $offset);

        return $this->respond([
            'success'             => true,
            'token'               => csrf_hash(),
            'history'             => $result['history'],
            'history_total'       => $result['history_total'],
            'history_limit'       => $result['history_limit'],
            'history_offset'      => $result['history_offset'],
            'history_next_offset' => $result['history_next_offset'],
            'history_has_more'    => $result['history_has_more'],
        ]);
    }
}

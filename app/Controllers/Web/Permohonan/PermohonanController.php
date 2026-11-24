<?php

namespace App\Controllers\Web\Permohonan;

use App\Controllers\BaseController;
use App\Services\Permohonan\PermohonanService;

class PermohonanController extends BaseController
{
    protected string $activeMenu = 'permohonan';
    protected PermohonanService $service;

    public function __construct(?PermohonanService $service = null)
    {
        $this->service = $service ?? new PermohonanService();
    }

    public function index(): string
    {
        $userId = (int) (user_id() ?? 0);

        $initialScope = trim((string) ($this->request->getGet('scope') ?? 'assigned_to_me'));
        if (! in_array($initialScope, ['assigned_to_me', 'my_requests', 'department_monitoring'], true)) {
            $initialScope = 'assigned_to_me';
        }

        $initialModule = trim((string) ($this->request->getGet('module') ?? 'all'));
        $initialId     = trim((string) ($this->request->getGet('id') ?? ''));
        $rawProyek     = $this->request->getGet('id_proyek');
        $initialProyek = ($rawProyek !== null && $rawProyek !== '' && (int) $rawProyek > 0)
            ? (int) $rawProyek
            : null;

        $meta    = $this->service->getMeta($userId);
        $summary = $this->service->getSummary($userId, $initialProyek, $initialScope);

        $roleId   = 0;
        $roleName = '';
        if (function_exists('user') && user()) {
            foreach (user()->getRoles() as $key => $val) {
                $roleId   = (int) $key;
                $roleName = (string) $val;
                break;
            }
        }

        return view('template', [
            'content' => 'permohonan/index',
            'data'    => [
                'title'      => 'Pusat Permohonan & Approval',
                'activeMenu' => $this->activeMenu,
                'meta'       => $meta,
                'summary'    => $summary,
                'projects'   => $meta['projects'] ?? [],
                'roleId'     => $roleId,
                'roleName'   => $roleName,
                'deepLink'   => [
                    'scope'     => $initialScope,
                    'module'    => $initialModule !== '' ? $initialModule : 'all',
                    'id'        => $initialId !== '' ? $initialId : null,
                    'id_proyek' => $initialProyek,
                ],
            ],
        ]);
    }
}

<?php

namespace App\Controllers\Web\AuditTrail;

use App\Controllers\BaseController;
use App\Services\AuditTrail\HistoryService;

class RiwayatPerubahanController extends BaseController
{
    protected HistoryService $historyService;

    public function __construct()
    {
        $this->historyService = new HistoryService();
    }

    public function index()
    {
        return view('template', [
            'content' => 'riwayat_perubahan/index',
            'data' => [
                'controller' => 'riwayat-perubahan',
                'title'      => 'Riwayat Perubahan',
                'modules'    => $this->historyService->moduleLabels(),
            ],
        ]);
    }
}

<?php

namespace App\Controllers;

use App\Services\HistoryService;

class RiwayatPerubahan extends BaseController
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
                'title' => 'Riwayat Perubahan',
                'modules' => $this->historyService->moduleLabels(),
            ],
        ]);
    }
}

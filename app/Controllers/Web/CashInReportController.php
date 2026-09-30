<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Services\ActiveProyekService;
use App\Services\CashInReportService;
use App\Services\MenuAccessService;

class CashInReportController extends BaseController
{
    private const MENU_URL = 'laporan/cash-in';

    private CashInReportService $reportService;
    private ActiveProyekService $activeProyekService;
    private MenuAccessService $menuAccessService;

    public function __construct()
    {
        $this->reportService = new CashInReportService();
        $this->activeProyekService = new ActiveProyekService();
        $this->menuAccessService = new MenuAccessService();
    }

    public function index()
    {
        if (! $this->menuAccessService->canAccessUrl((int) user_id(), self::MENU_URL)) {
            return $this->response
                ->setStatusCode(403)
                ->setBody(view('errors/html/unauthorized'));
        }

        $activeProyek = $this->activeProyekService->resolveAndGet();
        $currentYear = (int) date('Y');
        $availableYears = $activeProyek
            ? $this->reportService->getAvailableYears((int) $activeProyek->id_proyek)
            : [$currentYear, $currentYear - 1];

        return view('template', [
            'content' => 'laporan/cash-in',
            'data' => [
                'title' => 'Laporan Cash In',
                'activeProyek' => $activeProyek,
                'availableYears' => $availableYears,
                'defaultYearA' => $currentYear,
                'defaultYearB' => $currentYear - 1,
            ],
        ]);
    }
}

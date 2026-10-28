<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Services\ActiveProyekService;
use App\Services\BookingAkadReportService;
use App\Services\MenuAccessService;

class BookingAkadReportController extends BaseController
{
    private const MENU_URL = 'laporan/booking-akad';

    private BookingAkadReportService $reportService;
    private ActiveProyekService $activeProyekService;
    private MenuAccessService $menuAccessService;

    public function __construct()
    {
        $this->reportService = new BookingAkadReportService();
        $this->activeProyekService = new ActiveProyekService();
        $this->menuAccessService = new MenuAccessService();
    }

    public function index()
    {
        if (! $this->menuAccessService->canAccessUrl((int) user_id(), self::MENU_URL)) {
            return $this->response->setStatusCode(403)->setBody(view('errors/html/unauthorized'));
        }

        $activeProyek = $this->activeProyekService->resolveAndGet();
        $currentYear = (int) date('Y');
        $availableYears = $activeProyek
            ? $this->reportService->getAvailableYears((int) $activeProyek->id_proyek)
            : [$currentYear, $currentYear - 1];

        return view('template', [
            'content' => 'laporan/booking-akad',
            'data' => [
                'title' => 'Laporan Booking & Akad',
                'activeProyek' => $activeProyek,
                'availableYears' => $availableYears,
                'defaultYearA' => $currentYear,
                'defaultYearB' => $currentYear - 1,
            ],
        ]);
    }
}

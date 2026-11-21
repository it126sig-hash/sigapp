<?php

namespace App\Controllers\Api\Mkdt;

use App\Controllers\Api\BaseApiController;
use App\Services\ActiveProyekService;
use App\Services\Mkdt\BookingAkadReportService;
use App\Services\MenuAccessService;
use InvalidArgumentException;

class BookingAkadReportController extends BaseApiController
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

    public function summary()
    {
        if (! $this->canAccessReport()) {
            return $this->error('Anda tidak memiliki akses ke laporan Booking & Akad.', 403);
        }
        $activeProyek = $this->activeProyekService->resolveAndGet();
        if (! $activeProyek) {
            return $this->error('Pilih proyek aktif terlebih dahulu.', 422);
        }

        try {
            $yearBRaw = $this->request->getPost('year_b');
            $summary = $this->reportService->getSummary(
                (int) $activeProyek->id_proyek,
                (int) $this->request->getPost('year_a'),
                $yearBRaw === null || $yearBRaw === '' ? null : (int) $yearBRaw
            );
            $summary['project'] = [
                'id' => (int) $activeProyek->id_proyek,
                'name' => (string) ($activeProyek->nama_proyek ?? ''),
            ];

            return $this->success($summary, 'Laporan Booking & Akad berhasil dimuat.');
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    public function detail()
    {
        if (! $this->canAccessReport()) {
            return $this->error('Anda tidak memiliki akses ke laporan Booking & Akad.', 403);
        }
        $activeProyek = $this->activeProyekService->resolveAndGet();
        if (! $activeProyek) {
            return $this->error('Pilih proyek aktif terlebih dahulu.', 422);
        }

        try {
            $result = $this->reportService->getDetail((int) $activeProyek->id_proyek, $this->request->getPost());
            $result['success'] = true;
            $result['messages'] = 'Detail Booking & Akad berhasil dimuat.';
            $result['token'] = csrf_hash();

            return $this->respond($result);
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    private function canAccessReport(): bool
    {
        return $this->menuAccessService->canAccessUrl((int) user_id(), self::MENU_URL);
    }
}

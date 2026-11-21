<?php

namespace App\Controllers\Api\Keuangan;

use App\Controllers\Api\BaseApiController;
use App\Services\Keuangan\HasilAkadReportService;
use App\Services\Proyek\ActiveProyekService;
use App\Services\Settings\MenuAccessService;
use CodeIgniter\HTTP\ResponseInterface;
use InvalidArgumentException;

class HasilAkadReportController extends BaseApiController
{
    private const MENU_URL = 'laporan/hasil-akad';

    private HasilAkadReportService $reportService;
    private ActiveProyekService $activeProyekService;
    private MenuAccessService $menuAccessService;

    public function __construct()
    {
        $this->reportService = new HasilAkadReportService();
        $this->activeProyekService = new ActiveProyekService();
        $this->menuAccessService = new MenuAccessService();
    }

    public function meta(): ResponseInterface
    {
        if (! $this->canAccessReport()) {
            return $this->error('Anda tidak memiliki akses ke laporan Hasil Akad.', 403);
        }

        $activeProyek = $this->activeProyekService->resolveAndGet();
        $currentYear = (int) date('Y');
        $availableYears = $activeProyek
            ? $this->reportService->getAvailableYears((int) $activeProyek->id_proyek)
            : [$currentYear, $currentYear - 1];

        return $this->success([
            'active_project' => $activeProyek ? [
                'id' => (int) $activeProyek->id_proyek,
                'name' => (string) ($activeProyek->nama_proyek ?? ''),
            ] : null,
            'available_years' => $availableYears,
            'default_year_a' => $currentYear,
            'default_year_b' => $currentYear - 1,
            'categories' => HasilAkadReportService::CATEGORIES,
            'permissions' => [
                'can_view' => true,
            ],
        ], 'Metadata laporan Hasil Akad berhasil dimuat.');
    }

    public function summary(): ResponseInterface
    {
        if (! $this->canAccessReport()) {
            return $this->error('Anda tidak memiliki akses ke laporan Hasil Akad.', 403);
        }
        $activeProyek = $this->activeProyekService->resolveAndGet();
        if (! $activeProyek) {
            return $this->error('Pilih proyek aktif terlebih dahulu.', 422);
        }

        $input = $this->readRequestData();

        try {
            $yearBRaw = $input['year_b'] ?? null;
            $summary = $this->reportService->getSummary(
                (int) $activeProyek->id_proyek,
                (int) ($input['year_a'] ?? 0),
                $yearBRaw === null || $yearBRaw === '' ? null : (int) $yearBRaw
            );
            $summary['project'] = [
                'id' => (int) $activeProyek->id_proyek,
                'name' => (string) ($activeProyek->nama_proyek ?? ''),
            ];

            return $this->success($summary, 'Laporan Hasil Akad berhasil dimuat.');
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    public function detail(): ResponseInterface
    {
        if (! $this->canAccessReport()) {
            return $this->error('Anda tidak memiliki akses ke laporan Hasil Akad.', 403);
        }
        $activeProyek = $this->activeProyekService->resolveAndGet();
        if (! $activeProyek) {
            return $this->error('Pilih proyek aktif terlebih dahulu.', 422);
        }

        $input = $this->readRequestData();

        try {
            $result = $this->reportService->getDetail((int) $activeProyek->id_proyek, $input);

            if ($this->isRawFormat()) {
                return $this->success([
                    'items' => $result['data'],
                    'meta' => [
                        'total' => $result['recordsFiltered'],
                        'recordsTotal' => $result['recordsTotal'],
                        'recordsFiltered' => $result['recordsFiltered'],
                    ],
                ], 'Detail Hasil Akad berhasil dimuat.');
            }

            $result['success'] = true;
            $result['messages'] = 'Detail Hasil Akad berhasil dimuat.';
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

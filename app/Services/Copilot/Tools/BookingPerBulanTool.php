<?php

namespace App\Services\Copilot\Tools;

use App\Services\Copilot\CopilotAuthorizer;
use App\Services\Copilot\PiiGuard;
use App\Services\Mkdt\BookingAkadReportService;
use InvalidArgumentException;

class BookingPerBulanTool implements CopilotToolInterface
{
    private CopilotAuthorizer $authorizer;
    private BookingAkadReportService $reportService;
    private PiiGuard $piiGuard;

    public function __construct(
        ?CopilotAuthorizer $authorizer = null,
        ?BookingAkadReportService $reportService = null,
        ?PiiGuard $piiGuard = null,
    ) {
        $this->authorizer = $authorizer ?? new CopilotAuthorizer();
        $this->reportService = $reportService ?? new BookingAkadReportService();
        $this->piiGuard = $piiGuard ?? new PiiGuard();
    }

    public function getName(): string
    {
        return 'get_booking_per_bulan';
    }

    public function getDescription(): string
    {
        return 'Mengambil data agregat jumlah booking per bulan dalam satu tahun untuk proyek yang dipilih (read-only, tanpa data personal).';
    }

    public function getDeclaration(): array
    {
        return [
            'type'     => 'function',
            'function' => [
                'name'        => $this->getName(),
                'description' => $this->getDescription(),
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'tahun' => [
                            'type'        => 'integer',
                            'description' => 'Tahun laporan (default tahun berjalan).',
                        ],
                        'proyek' => [
                            'type'        => 'string',
                            'description' => 'Nama atau ID proyek target.',
                        ],
                    ],
                ],
            ],
        ];
    }

    public function execute(int $userId, array $arguments): array
    {
        $projectArg = isset($arguments['proyek']) ? (string) $arguments['proyek'] : null;
        $projectIdArg = isset($arguments['id_proyek']) && is_numeric($arguments['id_proyek'])
            ? (int) $arguments['id_proyek']
            : null;

        $resolution = $this->authorizer->resolveTargetProject($userId, $projectArg, $projectIdArg, false);
        if ($resolution['status'] !== 'ok' || $resolution['selected_project'] === null) {
            return $this->piiGuard->assertSafePayload([
                'status'             => $resolution['status'],
                'message'            => $this->buildResolutionMessage($resolution['status']),
                'available_projects' => $resolution['available_projects'],
            ]);
        }

        $tahun = isset($arguments['tahun']) && is_numeric($arguments['tahun']) && (int) $arguments['tahun'] > 0
            ? (int) $arguments['tahun']
            : (int) date('Y');

        $selectedProject = $resolution['selected_project'];

        try {
            $summary = $this->reportService->getSummary((int) $selectedProject['id_proyek'], $tahun);
        } catch (InvalidArgumentException $e) {
            return $this->piiGuard->assertSafePayload([
                'status'  => 'invalid_argument',
                'message' => $e->getMessage(),
            ]);
        }

        $months = [];
        foreach ($summary['months'] ?? [] as $item) {
            $values = $item['values'][$tahun] ?? [];
            $months[] = [
                'month'       => (int) ($item['month'] ?? 0),
                'month_label' => (string) ($item['month_label'] ?? ''),
                'booking'     => (int) ($values['booking'] ?? 0),
            ];
        }

        $totalBooking = (int) ($summary['totals'][$tahun]['booking'] ?? 0);

        return $this->piiGuard->assertSafePayload([
            'status'        => 'ok',
            'proyek'        => $selectedProject,
            'tahun'         => $tahun,
            'months'        => $months,
            'total_booking' => $totalBooking,
        ]);
    }

    private function buildResolutionMessage(string $status): string
    {
        if ($status === 'needs_project_selection') {
            return 'User memiliki akses ke lebih dari satu proyek. Tanyakan proyek spesifik mana yang ingin dilihat laporannya.';
        }

        return 'Proyek tidak ditemukan atau Anda tidak memiliki akses ke proyek tersebut.';
    }
}

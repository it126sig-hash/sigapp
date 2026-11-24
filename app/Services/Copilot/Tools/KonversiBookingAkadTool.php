<?php

namespace App\Services\Copilot\Tools;

use App\Services\Copilot\CopilotAuthorizer;
use App\Services\Copilot\PiiGuard;
use App\Services\Mkdt\BookingAkadReportService;
use InvalidArgumentException;

class KonversiBookingAkadTool implements CopilotToolInterface
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
        return 'get_konversi_booking_akad';
    }

    public function getDescription(): string
    {
        return 'Mengambil data agregat konversi booking menjadi akad (cohort conversion, akad indent, akad AJB, dan batal) per bulan dalam satu tahun untuk proyek yang dipilih.';
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
                'month'                  => (int) ($item['month'] ?? 0),
                'month_label'            => (string) ($item['month_label'] ?? ''),
                'total_booking_cohort'   => (int) ($values['total_booking_cohort'] ?? 0),
                'booking_converted_akad' => (int) ($values['booking_converted_akad'] ?? 0),
                'konversi'               => (float) ($values['konversi'] ?? 0.0),
                'akad_indent'            => (int) ($values['akad_indent'] ?? 0),
                'akad_ajb'               => (int) ($values['akad_ajb'] ?? 0),
                'batal'                  => (int) ($values['batal'] ?? 0),
            ];
        }

        $yearTotals = $summary['totals'][$tahun] ?? [];

        return $this->piiGuard->assertSafePayload([
            'status' => 'ok',
            'proyek' => $selectedProject,
            'tahun'  => $tahun,
            'months' => $months,
            'totals' => [
                'total_booking_cohort'   => (int) ($yearTotals['total_booking_cohort'] ?? 0),
                'booking_converted_akad' => (int) ($yearTotals['booking_converted_akad'] ?? 0),
                'konversi'               => (float) ($yearTotals['konversi'] ?? 0.0),
                'akad_indent'            => (int) ($yearTotals['akad_indent'] ?? 0),
                'akad_ajb'               => (int) ($yearTotals['akad_ajb'] ?? 0),
                'batal'                  => (int) ($yearTotals['batal'] ?? 0),
            ],
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

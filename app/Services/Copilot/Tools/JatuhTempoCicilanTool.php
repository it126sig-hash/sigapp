<?php

namespace App\Services\Copilot\Tools;

use App\Repositories\Copilot\JatuhTempoRepository;
use App\Services\Copilot\CopilotAuthorizer;
use App\Services\Copilot\PiiGuard;
use Config\Copilot as CopilotConfig;

class JatuhTempoCicilanTool implements CopilotToolInterface
{
    private CopilotAuthorizer $authorizer;
    private JatuhTempoRepository $repository;
    private PiiGuard $piiGuard;
    private CopilotConfig $config;

    public function __construct(
        ?CopilotAuthorizer $authorizer = null,
        ?JatuhTempoRepository $repository = null,
        ?PiiGuard $piiGuard = null,
        ?CopilotConfig $config = null,
    ) {
        $this->authorizer = $authorizer ?? new CopilotAuthorizer();
        $this->repository = $repository ?? new JatuhTempoRepository();
        $this->piiGuard = $piiGuard ?? new PiiGuard();
        $this->config = $config ?? config('Copilot');
    }

    public function getName(): string
    {
        return 'get_jatuh_tempo_cicilan';
    }

    public function getDescription(): string
    {
        return 'Mengambil ringkasan dan daftar tagihan cicilan konsumen yang sudah terlambat atau akan jatuh tempo dalam N hari ke depan (read-only, bebas PII).';
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
                        'proyek' => [
                            'type'        => 'string',
                            'description' => 'Nama proyek atau ID proyek, atau "semua" untuk seluruh proyek yang boleh diakses user.',
                        ],
                        'hari' => [
                            'type'        => 'integer',
                            'description' => 'Rentang hari ke depan dari hari ini (default 7, rentang 0-90).',
                        ],
                        'kategori' => [
                            'type'        => 'string',
                            'enum'        => ['semua', 'terlambat', 'akan_jatuh_tempo'],
                            'description' => 'Filter kategori hasil: semua, terlambat, atau akan_jatuh_tempo.',
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

        $resolution = $this->authorizer->resolveTargetProject($userId, $projectArg, $projectIdArg, true);
        if ($resolution['status'] !== 'ok') {
            return $this->piiGuard->assertSafePayload([
                'status'             => $resolution['status'],
                'message'            => $this->buildResolutionMessage($resolution['status']),
                'available_projects' => $resolution['available_projects'],
            ]);
        }

        $days = isset($arguments['hari']) && is_numeric($arguments['hari'])
            ? (int) $arguments['hari']
            : $this->config->jatuhTempoDefaultDays;
        $days = max(0, min(JatuhTempoRepository::MAX_DAYS, $days));

        $kategori = strtolower(trim((string) ($arguments['kategori'] ?? 'semua')));
        if (! in_array($kategori, ['semua', 'terlambat', 'akan_jatuh_tempo'], true)) {
            $kategori = 'semua';
        }

        $result = $this->repository->getJatuhTempo(
            $resolution['project_ids'],
            $days,
            $this->config->jatuhTempoMaxRows,
            null,
            $kategori
        );

        return $this->piiGuard->assertSafePayload(array_merge([
            'status'   => 'ok',
            'projects' => $resolution['projects'],
        ], $result));
    }

    private function buildResolutionMessage(string $status): string
    {
        if ($status === 'needs_project_selection') {
            return 'User memiliki akses ke lebih dari satu proyek. Tanyakan proyek mana yang ingin dicek atau gunakan proyek="semua".';
        }

        return 'Proyek tidak ditemukan atau Anda tidak memiliki akses ke proyek tersebut.';
    }
}

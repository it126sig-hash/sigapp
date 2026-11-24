<?php

namespace App\Services\Copilot;

use App\Repositories\Proyek\ProyekRepository;
use App\Services\Settings\MenuAccessService;
use Config\Copilot as CopilotConfig;

class CopilotAuthorizer
{
    private MenuAccessService $menuAccessService;
    private ProyekRepository $proyekRepository;
    private CopilotConfig $config;

    public function __construct(
        ?MenuAccessService $menuAccessService = null,
        ?ProyekRepository $proyekRepository = null,
        ?CopilotConfig $config = null,
    ) {
        $this->menuAccessService = $menuAccessService ?? new MenuAccessService();
        $this->proyekRepository = $proyekRepository ?? new ProyekRepository();
        $this->config = $config ?? config('Copilot');
    }

    /**
     * @return int[]
     */
    public function getUserGroupIds(int $userId): array
    {
        if ($userId <= 0) {
            return [];
        }

        return $this->menuAccessService->getUserGroupIds($userId);
    }

    public function canAccessCopilot(int $userId): bool
    {
        if ($userId <= 0) {
            return false;
        }

        return $this->menuAccessService->canAccessUrl($userId, 'copilot');
    }

    public function canUseTool(int $userId, string $toolName): bool
    {
        if (! $this->canAccessCopilot($userId)) {
            return false;
        }

        if (! array_key_exists($toolName, $this->config->toolAllowedGroupIds)) {
            return false;
        }

        $allowedGroups = array_map('intval', $this->config->toolAllowedGroupIds[$toolName]);
        if ($allowedGroups === []) {
            return true;
        }

        $userGroups = $this->getUserGroupIds($userId);

        return array_intersect($userGroups, $allowedGroups) !== [];
    }

    /**
     * Mengembalikan daftar proyek yang dapat diakses user (hanya id_proyek & nama_proyek).
     *
     * @return array<int, array{id_proyek: int, nama_proyek: string}>
     */
    public function getAccessibleProjects(int $userId): array
    {
        if ($userId <= 0) {
            return [];
        }

        $groupIds = $this->getUserGroupIds($userId);
        $isAdmin = in_array(1, $groupIds, true);
        $rows = $this->proyekRepository->getAccessibleForUser($userId, $isAdmin);

        $projects = [];
        foreach ($rows as $row) {
            $id = (int) (is_object($row) ? ($row->id_proyek ?? 0) : ($row['id_proyek'] ?? 0));
            $name = trim((string) (is_object($row) ? ($row->nama_proyek ?? '') : ($row['nama_proyek'] ?? '')));
            if ($id > 0) {
                $projects[] = [
                    'id_proyek'   => $id,
                    'nama_proyek' => $name,
                ];
            }
        }

        return $projects;
    }

    /**
     * Menyelesaikan target proyek berdasarkan hak akses user dan argumen tool.
     *
     * @return array{
     *     status: string,
     *     projects: array<int, array{id_proyek: int, nama_proyek: string}>,
     *     project_ids: int[],
     *     selected_project: ?array{id_proyek: int, nama_proyek: string},
     *     available_projects: array<int, array{id_proyek: int, nama_proyek: string}>
     * }
     */
    public function resolveTargetProject(
        int $userId,
        ?string $projectName = null,
        ?int $projectId = null,
        bool $allowMultiDefault = false
    ): array {
        $accessible = $this->getAccessibleProjects($userId);
        if ($accessible === []) {
            return [
                'status'             => 'forbidden_or_not_found',
                'projects'           => [],
                'project_ids'        => [],
                'selected_project'   => null,
                'available_projects' => [],
            ];
        }

        $trimmedName = $projectName !== null ? trim($projectName) : null;
        if ($trimmedName === '') {
            $trimmedName = null;
        }

        // Handle numeric string passed in $projectName
        if ($projectId === null && $trimmedName !== null && ctype_digit($trimmedName)) {
            $projectId = (int) $trimmedName;
            $trimmedName = null;
        }

        // Explicit "semua" / "all"
        if ($trimmedName !== null && in_array(strtolower($trimmedName), ['semua', 'all', 'semua proyek'], true)) {
            if ($allowMultiDefault) {
                return [
                    'status'             => 'ok',
                    'projects'           => $accessible,
                    'project_ids'        => array_column($accessible, 'id_proyek'),
                    'selected_project'   => count($accessible) === 1 ? $accessible[0] : null,
                    'available_projects' => $accessible,
                ];
            }

            if (count($accessible) === 1) {
                return $this->singleProjectOk($accessible[0], $accessible);
            }

            return [
                'status'             => 'needs_project_selection',
                'projects'           => [],
                'project_ids'        => [],
                'selected_project'   => null,
                'available_projects' => $accessible,
            ];
        }

        // Explicit projectId
        if ($projectId !== null && $projectId > 0) {
            foreach ($accessible as $project) {
                if ($project['id_proyek'] === $projectId) {
                    return $this->singleProjectOk($project, $accessible);
                }
            }

            return [
                'status'             => 'forbidden_or_not_found',
                'projects'           => [],
                'project_ids'        => [],
                'selected_project'   => null,
                'available_projects' => $accessible,
            ];
        }

        // Explicit projectName
        if ($trimmedName !== null) {
            $needle = strtolower($trimmedName);

            foreach ($accessible as $project) {
                if (strtolower($project['nama_proyek']) === $needle) {
                    return $this->singleProjectOk($project, $accessible);
                }
            }

            $partialMatches = array_values(array_filter(
                $accessible,
                static fn (array $project): bool => str_contains(strtolower($project['nama_proyek']), $needle)
            ));

            if (count($partialMatches) === 1) {
                return $this->singleProjectOk($partialMatches[0], $accessible);
            }

            if (count($partialMatches) > 1) {
                return [
                    'status'             => 'needs_project_selection',
                    'projects'           => [],
                    'project_ids'        => [],
                    'selected_project'   => null,
                    'available_projects' => $partialMatches,
                ];
            }

            return [
                'status'             => 'forbidden_or_not_found',
                'projects'           => [],
                'project_ids'        => [],
                'selected_project'   => null,
                'available_projects' => $accessible,
            ];
        }

        // No project specified
        if (count($accessible) === 1) {
            return $this->singleProjectOk($accessible[0], $accessible);
        }

        return [
            'status'             => 'needs_project_selection',
            'projects'           => [],
            'project_ids'        => [],
            'selected_project'   => null,
            'available_projects' => $accessible,
        ];
    }

    /**
     * @param array{id_proyek: int, nama_proyek: string} $project
     * @param array<int, array{id_proyek: int, nama_proyek: string}> $accessible
     * @return array<string, mixed>
     */
    private function singleProjectOk(array $project, array $accessible): array
    {
        return [
            'status'             => 'ok',
            'projects'           => [$project],
            'project_ids'        => [$project['id_proyek']],
            'selected_project'   => $project,
            'available_projects' => $accessible,
        ];
    }
}

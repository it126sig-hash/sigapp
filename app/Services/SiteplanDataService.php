<?php

namespace App\Services;

use App\Repositories\KavlingRepository;
use App\Repositories\OthersRepository;

class SiteplanDataService
{
    private KavlingRepository $kavlingRepository;
    private OthersRepository $othersRepository;
    private SiteplanVisualStatusService $visualStatusService;
    private TargetSiteplanService $targetSiteplanService;
    private FileAccessService $fileAccessService;

    public function __construct(
        ?KavlingRepository $kavlingRepository = null,
        ?OthersRepository $othersRepository = null,
        ?SiteplanVisualStatusService $visualStatusService = null,
        ?TargetSiteplanService $targetSiteplanService = null,
        ?FileAccessService $fileAccessService = null
    ) {
        $this->kavlingRepository = $kavlingRepository ?? new KavlingRepository();
        $this->othersRepository = $othersRepository ?? new OthersRepository();
        $this->visualStatusService = $visualStatusService ?? new SiteplanVisualStatusService();
        $this->targetSiteplanService = $targetSiteplanService ?? new TargetSiteplanService();
        $this->fileAccessService = $fileAccessService ?? new FileAccessService();
    }

    public function getKavlings(array $params): array
    {
        $idProyek = (int) ($params['id_proyek'] ?? 0);
        $idRole = (int) ($params['id_role'] ?? 0);
        $clusterIds = self::normalizeIdList($params['id_cluster'] ?? null);
        $idJalan = self::normalizeOptionalId($params['id_jalan'] ?? null);
        $filters = $this->categoryFilters($params);

        $data = $this->kavlingRepository->getAll($idProyek, $clusterIds ?: null, $idJalan, $idRole, $filters);
        if ($idRole === 0) {
            $data = $this->visualStatusService->appendVisualRows($data);
        }

        $result = ['data' => $data];
        if ($idRole === 11) {
            $result['target_kavling'] = $this->targetSiteplanService->getKavlingTargetMap($idProyek);
        }

        return $result;
    }

    public function getOthers(array $params): array
    {
        $id = self::normalizeOptionalId($params['id_kavling'] ?? null);
        $idProyek = (int) ($params['id_proyek'] ?? 0);
        $clusterIds = self::normalizeIdList($params['id_cluster'] ?? null);
        $result = [
            'data' => $this->othersRepository->getAll($id, $idProyek, $clusterIds, $this->categoryFilters($params)),
            'history' => [],
            'history_total' => 0,
            'history_limit' => 0,
            'history_offset' => 0,
            'history_next_offset' => 0,
            'history_has_more' => false,
        ];

        if ($id === null || !$this->othersRepository->hasProgressHistory()) {
            return $result;
        }

        $limit = max(1, min(50, (int) ($params['history_limit'] ?? 10)));
        $offset = max(0, (int) ($params['history_offset'] ?? 0));
        $total = $this->othersRepository->countProgressHistory($id);
        $history = $this->othersRepository->getProgressHistory($id, $limit, $offset);

        foreach ($history as $item) {
            $item->foto_urls = $this->fileAccessService->pathUrlsFromDelimitedString(
                $item->foto ?? '',
                'produksi_jalan_progress'
            );
        }

        $result['history'] = $history;
        $result['history_total'] = $total;
        $result['history_limit'] = $limit;
        $result['history_offset'] = $offset;
        $result['history_next_offset'] = $offset + count($history);
        $result['history_has_more'] = $result['history_next_offset'] < $total;

        return $result;
    }

    public static function normalizeIdList($value): array
    {
        $values = is_array($value) ? $value : (($value === null || $value === '') ? [] : [$value]);
        $ids = [];

        foreach ($values as $item) {
            $id = filter_var($item, FILTER_VALIDATE_INT);
            if ($id !== false && $id > 0) {
                $ids[(int) $id] = (int) $id;
            }
        }

        return array_values($ids);
    }

    private static function normalizeOptionalId($value): ?int
    {
        $id = filter_var($value, FILTER_VALIDATE_INT);
        return ($id !== false && $id > 0) ? (int) $id : null;
    }

    private function categoryFilters(array $params): array
    {
        return [
            'kategori' => is_array($params['kategori'] ?? null) ? $params['kategori'] : [],
            'periode_mulai' => $params['periode_mulai'] ?? null,
            'periode_selesai' => $params['periode_selesai'] ?? null,
            'status_masalah' => $params['status_masalah'] ?? null,
            'periode_masalah_jenis' => $params['periode_masalah_jenis'] ?? null,
        ];
    }
}

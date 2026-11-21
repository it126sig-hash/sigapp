<?php

namespace App\Services\Siteplan;

use App\Models\TargetSiteplanHistoryModel;
use App\Models\TargetSiteplanKavlingModel;
use App\Models\TargetSiteplanModel;
use App\Repositories\Siteplan\TargetSiteplanRepository;
use App\Services\HistoryService;
use CodeIgniter\Database\BaseConnection;
use RuntimeException;

class TargetSiteplanService
{
    protected BaseConnection $db;
    protected TargetSiteplanModel $targetModel;
    protected TargetSiteplanKavlingModel $targetKavlingModel;
    protected TargetSiteplanHistoryModel $historyModel;
    protected TargetSiteplanRepository $repository;
    protected HistoryService $historyService;

    public function __construct(
        ?TargetSiteplanRepository $repository = null,
        ?TargetSiteplanModel $targetModel = null,
        ?TargetSiteplanKavlingModel $targetKavlingModel = null,
        ?TargetSiteplanHistoryModel $historyModel = null,
        ?HistoryService $historyService = null,
        ?BaseConnection $db = null
    ) {
        $this->db = $db ?? \Config\Database::connect();
        $this->targetModel = $targetModel ?? new TargetSiteplanModel();
        $this->targetKavlingModel = $targetKavlingModel ?? new TargetSiteplanKavlingModel();
        $this->historyModel = $historyModel ?? new TargetSiteplanHistoryModel();
        $this->historyService = $historyService ?? new HistoryService();
        $this->repository = $repository ?? new TargetSiteplanRepository($this->db, $this->historyService);
    }

    public function listByProject(int $idProyek): array
    {
        return $this->repository->getByProject($idProyek);
    }

    public function get(int $idTarget): array
    {
        $target = $this->repository->getById($idTarget);
        if (! $target) {
            throw new RuntimeException('Target tidak ditemukan');
        }

        return [
            'target'   => $target,
            'kavlings' => $this->repository->getKavlings($idTarget),
            'history'  => $this->repository->getHistory($idTarget),
        ];
    }

    public function getByProjectAndYear(int $idProyek, int $tahunTarget): array
    {
        if ($idProyek <= 0) {
            throw new RuntimeException('Proyek harus diisi');
        }

        if ($tahunTarget < 2000 || $tahunTarget > 2100) {
            throw new RuntimeException('Tahun target tidak valid');
        }

        $target = $this->repository->findActiveByProjectAndYear($idProyek, $tahunTarget);
        if (! $target) {
            return [
                'target'   => null,
                'kavlings' => [],
                'history'  => [],
            ];
        }

        $idTarget = (int) $target->id_target;

        return [
            'target'   => $target,
            'kavlings' => $this->repository->getKavlings($idTarget),
            'history'  => $this->repository->getHistory($idTarget),
        ];
    }

    public function resolveProjectIdForTargetKavling(?int $idTargetKavling, ?int $idTarget): ?int
    {
        if ($idTargetKavling !== null && $idTargetKavling > 0) {
            $row = $this->repository->findTargetKavlingById($idTargetKavling);
            if ($row && isset($row->id_target)) {
                $target = $this->repository->getById((int) $row->id_target);

                return $target && isset($target->id_proyek) ? (int) $target->id_proyek : null;
            }
        }

        if ($idTarget !== null && $idTarget > 0) {
            $target = $this->repository->getById($idTarget);

            return $target && isset($target->id_proyek) ? (int) $target->id_proyek : null;
        }

        return null;
    }

    /**
     * Resolve semua id_proyek unik yang terkait dengan daftar id_target_kavling atau id_target.
     *
     * @return int[]
     */
    public function resolveProjectIdsForTargetKavlings(array $idTargetKavlings, ?int $idTarget = null): array
    {
        $projectIds = [];
        $ids = $this->normalizeKavlingIds($idTargetKavlings);

        if ($ids !== []) {
            $rows = $this->repository->findTargetKavlingsByIds($ids);
            foreach ($rows as $row) {
                $pId = (int) ($row->id_proyek ?? 0);
                if ($pId > 0) {
                    $projectIds[$pId] = $pId;
                }
            }

            return array_values($projectIds);
        }

        if ($idTarget !== null && $idTarget > 0) {
            $target = $this->repository->getById($idTarget);
            if ($target && isset($target->id_proyek) && (int) $target->id_proyek > 0) {
                return [(int) $target->id_proyek];
            }
        }

        return [];
    }

    public function resolveProjectIdForTargetRequest(int $idRequest): ?int
    {
        if ($idRequest <= 0) {
            return null;
        }

        $request = $this->repository->findRequestById($idRequest);
        if (! $request || ! isset($request->id_target)) {
            return null;
        }

        $target = $this->repository->getById((int) $request->id_target);

        return $target && isset($target->id_proyek) ? (int) $target->id_proyek : null;
    }

    public function getKavlingTargetMap(int $idProyek, ?int $tahunTarget = null): array
    {
        return $this->repository->getKavlingTargetMap($idProyek, $tahunTarget);
    }

    public function getAvailableYears(int $idProyek): array
    {
        return $this->repository->getAvailableYears($idProyek);
    }

    public function addKavlingToYear(array $data, int $actorId): array
    {
        $idProyek = isset($data['id_proyek']) ? (int) $data['id_proyek'] : 0;
        $tahunTarget = isset($data['tahun_target']) ? (int) $data['tahun_target'] : 0;
        $rawKavlingInput = ! empty($data['id_kavlings'])
            ? $data['id_kavlings']
            : ($data['id_kavling'] ?? []);
        $idKavlings = $this->normalizeKavlingIds($rawKavlingInput);
        $isSingleInput = empty($data['id_kavlings'])
            && ! is_array($data['id_kavling'] ?? null)
            && count($idKavlings) === 1;

        if ($idProyek <= 0) {
            throw new RuntimeException('Proyek harus diisi');
        }

        if ($tahunTarget < 2000 || $tahunTarget > 2100) {
            throw new RuntimeException('Tahun target tidak valid');
        }

        if ($idKavlings === []) {
            throw new RuntimeException('Kavling harus diisi');
        }

        $this->assertKavlingsBelongToProject($idProyek, $idKavlings);
        $this->assertNoActiveConsumers($idKavlings);
        $resolvedTipe = $this->resolveAndValidateTipeInput($idProyek, $data);

        $now = date('Y-m-d H:i:s');
        $effectiveActorId = $actorId > 0 ? $actorId : ($this->currentUserId() ?? 0);
        $toInsertIds = $idKavlings;
        $skippedIds = [];

        $this->db->transBegin();

        try {
            $target = $this->repository->findActiveByProjectAndYear($idProyek, $tahunTarget);
            $before = null;

            if (! $target) {
                $deskripsi = trim((string) ($data['deskripsi'] ?? ''));
                $idTarget = $this->repository->createTargetHeader([
                    'id_proyek'    => $idProyek,
                    'tahun_target' => $tahunTarget,
                    'deskripsi'    => $deskripsi !== '' ? $deskripsi : ('Target Tahun ' . $tahunTarget),
                    'status'       => 1,
                    'add_by'       => $effectiveActorId > 0 ? $effectiveActorId : null,
                    'edit_by'      => $effectiveActorId > 0 ? $effectiveActorId : null,
                    'created_at'   => $now,
                    'updated_at'   => $now,
                ]);
            } else {
                $idTarget = (int) $target->id_target;
                $before = $this->makeSnapshot($idTarget);
                $existingByKavling = $this->indexKavlingsById($before['kavlings'] ?? []);

                if ($isSingleInput && isset($existingByKavling[$idKavlings[0]])) {
                    $existingRow = $existingByKavling[$idKavlings[0]];
                    if (! empty($data['update_if_exists'])) {
                        $this->repository->updateTargetKavling((int) $existingRow->id_target_kavling, [
                            'id_tipe'     => $resolvedTipe['id_tipe'],
                            'tipe_manual' => $resolvedTipe['tipe_manual'],
                            'updated_by'  => $effectiveActorId > 0 ? $effectiveActorId : null,
                            'updated_at'  => $now,
                        ]);
                        $this->repository->updateTargetHeader($idTarget, [
                            'edit_by'    => $effectiveActorId > 0 ? $effectiveActorId : null,
                            'updated_at' => $now,
                        ]);

                        $after = $this->makeSnapshot($idTarget);
                        $this->recordTargetHistory(
                            $idTarget,
                            $idProyek,
                            'target_update_tipe',
                            'Tipe target kavling diperbarui menjadi ' . ($resolvedTipe['tipe_label'] ?? '-'),
                            $before,
                            $after,
                            $effectiveActorId,
                            $now,
                            $idKavlings[0]
                        );

                        $this->db->transCommit();

                        return array_merge($this->get($idTarget), [
                            'batch_summary' => [
                                'added_count'         => 0,
                                'updated_count'       => 1,
                                'skipped_count'       => 0,
                                'added_kavling_ids'   => [],
                                'skipped_kavling_ids' => [],
                            ],
                        ]);
                    }

                    throw new RuntimeException(
                        'Kavling sudah terdaftar pada target tahun ' . $tahunTarget . '. Silakan ubah tipe target melalui menu edit.'
                    );
                }

                $toInsertIds = [];
                foreach ($idKavlings as $kId) {
                    if (isset($existingByKavling[$kId])) {
                        $skippedIds[] = $kId;
                    } else {
                        $toInsertIds[] = $kId;
                    }
                }

                if ($toInsertIds === []) {
                    throw new RuntimeException(
                        count($idKavlings) === 1
                            ? ('Kavling sudah terdaftar pada target tahun ' . $tahunTarget . '. Silakan ubah tipe target melalui menu edit.')
                            : ('Semua kavling yang dipilih sudah terdaftar pada target tahun ' . $tahunTarget . '.')
                    );
                }

                $this->repository->updateTargetHeader($idTarget, [
                    'edit_by'    => $effectiveActorId > 0 ? $effectiveActorId : null,
                    'updated_at' => $now,
                ]);
            }

            $insertRows = [];
            foreach ($toInsertIds as $kId) {
                $insertRows[] = [
                    'id_target'   => $idTarget,
                    'id_kavling'  => $kId,
                    'id_tipe'     => $resolvedTipe['id_tipe'],
                    'tipe_manual' => $resolvedTipe['tipe_manual'],
                    'created_at'  => $now,
                    'updated_by'  => $effectiveActorId > 0 ? $effectiveActorId : null,
                    'updated_at'  => $now,
                ];
            }

            if (count($insertRows) === 1) {
                $this->repository->insertTargetKavling($insertRows[0]);
            } else {
                $this->db->table('target_siteplan_kavling')->insertBatch($insertRows);
            }

            $after = $this->makeSnapshot($idTarget);
            $addedCount = count($toInsertIds);
            $summary = $addedCount === 1
                ? ('Kavling ditambahkan ke target tahun ' . $tahunTarget . ' dengan tipe ' . ($resolvedTipe['tipe_label'] ?? '-'))
                : ($addedCount . ' kavling ditambahkan ke target tahun ' . $tahunTarget . ' dengan tipe ' . ($resolvedTipe['tipe_label'] ?? '-'));

            $this->recordTargetHistory(
                $idTarget,
                $idProyek,
                'target_add_kavling',
                $summary,
                $before,
                $after,
                $effectiveActorId,
                $now,
                $addedCount === 1 ? $toInsertIds[0] : null,
                null,
                null,
                [
                    'added_count'         => $addedCount,
                    'added_kavling_ids'   => $toInsertIds,
                    'skipped_count'       => count($skippedIds),
                    'skipped_kavling_ids' => $skippedIds,
                ]
            );

            $this->db->transCommit();
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }

        return array_merge($this->get($idTarget), [
            'batch_summary' => [
                'added_count'         => count($toInsertIds),
                'skipped_count'       => count($skippedIds),
                'added_kavling_ids'   => $toInsertIds,
                'skipped_kavling_ids' => $skippedIds,
            ],
        ]);
    }

    public function updateKavlingTipe(array $data, int $actorId): array
    {
        $rows = $this->locateTargetKavlingRows($data);
        $projectIds = [];
        $allKavlingIds = [];
        $groupedByTarget = [];

        foreach ($rows as $row) {
            $rowProyekId = (int) ($row->id_proyek ?? 0);
            if ($rowProyekId <= 0) {
                throw new RuntimeException('Target tidak ditemukan');
            }
            $projectIds[$rowProyekId] = $rowProyekId;
            $allKavlingIds[] = (int) $row->id_kavling;
            $groupedByTarget[(int) $row->id_target][] = $row;
        }

        if (count($projectIds) !== 1) {
            throw new RuntimeException('Semua kavling harus berasal dari proyek yang sama');
        }

        $idProyek = (int) reset($projectIds);
        if (! empty($data['id_proyek']) && (int) $data['id_proyek'] !== $idProyek) {
            throw new RuntimeException('Target tidak sesuai proyek');
        }

        $allKavlingIds = array_values(array_unique($allKavlingIds));
        $this->assertNoActiveConsumers($allKavlingIds);
        $resolvedTipe = $this->resolveAndValidateTipeInput($idProyek, $data);

        $now = date('Y-m-d H:i:s');
        $effectiveActorId = $actorId > 0 ? $actorId : ($this->currentUserId() ?? 0);
        $updatedIds = [];

        $this->db->transBegin();

        try {
            foreach ($groupedByTarget as $idTarget => $targetRows) {
                $idTarget = (int) $idTarget;
                $before = $this->makeSnapshot($idTarget);
                $targetKavlingIds = [];
                $targetKavlingRefIds = [];

                foreach ($targetRows as $targetRow) {
                    $idTargetKavling = (int) $targetRow->id_target_kavling;
                    $this->repository->updateTargetKavling($idTargetKavling, [
                        'id_tipe'     => $resolvedTipe['id_tipe'],
                        'tipe_manual' => $resolvedTipe['tipe_manual'],
                        'updated_by'  => $effectiveActorId > 0 ? $effectiveActorId : null,
                        'updated_at'  => $now,
                    ]);
                    $targetKavlingIds[] = $idTargetKavling;
                    $targetKavlingRefIds[] = (int) $targetRow->id_kavling;
                    $updatedIds[] = $idTargetKavling;
                }

                $this->repository->updateTargetHeader($idTarget, [
                    'edit_by'    => $effectiveActorId > 0 ? $effectiveActorId : null,
                    'updated_at' => $now,
                ]);

                $after = $this->makeSnapshot($idTarget);
                $updatedCount = count($targetRows);
                $summary = $updatedCount === 1
                    ? ('Tipe target kavling diperbarui menjadi ' . ($resolvedTipe['tipe_label'] ?? '-'))
                    : ($updatedCount . ' tipe target kavling diperbarui menjadi ' . ($resolvedTipe['tipe_label'] ?? '-'));

                $this->recordTargetHistory(
                    $idTarget,
                    $idProyek,
                    'target_update_tipe',
                    $summary,
                    $before,
                    $after,
                    $effectiveActorId,
                    $now,
                    $updatedCount === 1 ? $targetKavlingRefIds[0] : null,
                    null,
                    null,
                    [
                        'updated_count'         => $updatedCount,
                        'id_target_kavlings'    => $targetKavlingIds,
                        'updated_kavling_ids'   => $targetKavlingRefIds,
                    ]
                );
            }

            $this->db->transCommit();
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }

        $primaryIdTarget = (int) array_key_first($groupedByTarget);

        return array_merge($this->get($primaryIdTarget), [
            'batch_summary' => [
                'updated_count'      => count($updatedIds),
                'id_target_kavlings' => $updatedIds,
            ],
        ]);
    }

    public function removeKavling(array $data, int $actorId): array
    {
        $rows = $this->locateTargetKavlingRows($data);
        $projectIds = [];
        $groupedByTarget = [];
        $targetsById = [];

        foreach ($rows as $row) {
            $rowTargetId = (int) ($row->id_target ?? 0);
            if (! isset($targetsById[$rowTargetId])) {
                $target = $this->repository->getById($rowTargetId);
                if (! $target) {
                    throw new RuntimeException('Target tidak ditemukan');
                }
                $targetsById[$rowTargetId] = $target;
            }

            $rowProyekId = (int) ($targetsById[$rowTargetId]->id_proyek ?? 0);
            if ($rowProyekId <= 0) {
                throw new RuntimeException('Target tidak ditemukan');
            }

            $projectIds[$rowProyekId] = $rowProyekId;
            $groupedByTarget[$rowTargetId][] = $row;
        }

        if (count($projectIds) !== 1) {
            throw new RuntimeException('Semua kavling harus berasal dari proyek yang sama');
        }

        $idProyek = (int) reset($projectIds);
        if (! empty($data['id_proyek']) && (int) $data['id_proyek'] !== $idProyek) {
            throw new RuntimeException('Target tidak sesuai proyek');
        }

        $now = date('Y-m-d H:i:s');
        $effectiveActorId = $actorId > 0 ? $actorId : ($this->currentUserId() ?? 0);
        $allRemovedTargetKavlingIds = [];
        $allRemovedKavlingIds = [];

        $this->db->transBegin();

        try {
            foreach ($groupedByTarget as $idTarget => $targetRows) {
                $idTarget = (int) $idTarget;
                $before = $this->makeSnapshot($idTarget);
                $targetKavlingIds = [];
                $removedKavlingIds = [];

                foreach ($targetRows as $targetRow) {
                    $idTargetKavling = (int) $targetRow->id_target_kavling;
                    $idKavling = (int) $targetRow->id_kavling;
                    $targetKavlingIds[] = $idTargetKavling;
                    $removedKavlingIds[] = $idKavling;
                    $allRemovedTargetKavlingIds[] = $idTargetKavling;
                    $allRemovedKavlingIds[] = $idKavling;
                }

                $this->repository->deleteTargetKavlings($targetKavlingIds);

                $this->repository->updateTargetHeader($idTarget, [
                    'edit_by'    => $effectiveActorId > 0 ? $effectiveActorId : null,
                    'updated_at' => $now,
                ]);

                $after = $this->makeSnapshot($idTarget);
                $removedCount = count($targetRows);
                $tahun = (int) $targetsById[$idTarget]->tahun_target;
                $summary = $removedCount === 1
                    ? ('Kavling dihapus dari target tahun ' . $tahun)
                    : ($removedCount . ' kavling dihapus dari target tahun ' . $tahun);

                $this->recordTargetHistory(
                    $idTarget,
                    $idProyek,
                    'target_remove_kavling',
                    $summary,
                    $before,
                    $after,
                    $effectiveActorId,
                    $now,
                    $removedCount === 1 ? $removedKavlingIds[0] : null,
                    null,
                    null,
                    [
                        'removed_count'       => $removedCount,
                        'id_target_kavlings'  => $targetKavlingIds,
                        'removed_kavling_ids' => $removedKavlingIds,
                    ]
                );
            }

            $this->db->transCommit();
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }

        $primaryIdTarget = (int) array_key_first($groupedByTarget);

        return array_merge($this->get($primaryIdTarget), [
            'batch_summary' => [
                'removed_count'       => count($allRemovedTargetKavlingIds),
                'id_target_kavlings'  => $allRemovedTargetKavlingIds,
                'removed_kavling_ids' => $allRemovedKavlingIds,
            ],
        ]);
    }

    public function save(array $data): array
    {
        $idTarget = isset($data['id_target']) && $data['id_target'] !== '' ? (int) $data['id_target'] : null;
        $idProyek = isset($data['id_proyek']) ? (int) $data['id_proyek'] : 0;
        $tahunTarget = isset($data['tahun_target']) ? (int) $data['tahun_target'] : 0;
        $deskripsi = trim((string) ($data['deskripsi'] ?? ''));
        $idKavlings = $this->normalizeKavlingIds($data['id_kavling'] ?? []);

        if ($idProyek <= 0) {
            throw new RuntimeException('Proyek harus diisi');
        }

        if ($tahunTarget < 2000 || $tahunTarget > 2100) {
            throw new RuntimeException('Tahun target tidak valid');
        }

        if (count($idKavlings) === 0) {
            throw new RuntimeException('Pilih minimal 1 kavling');
        }

        $this->assertKavlingsBelongToProject($idProyek, $idKavlings);
        $this->assertNoActiveConsumers($idKavlings);

        $userId = $this->currentUserId();
        $now = date('Y-m-d H:i:s');
        $this->db->transBegin();

        try {
            $before = null;
            $existingTipeMap = [];

            if ($idTarget) {
                $target = $this->repository->getById($idTarget);
                if (! $target) {
                    throw new RuntimeException('Target tidak ditemukan');
                }
                if ((int) $target->id_proyek !== $idProyek) {
                    throw new RuntimeException('Target tidak sesuai proyek');
                }

                $before = $this->makeSnapshot($idTarget);
                foreach ($before['kavlings'] ?? [] as $existingRow) {
                    $existingTipeMap[(int) $existingRow->id_kavling] = [
                        'id_tipe'     => isset($existingRow->id_tipe) && (int) $existingRow->id_tipe > 0
                            ? (int) $existingRow->id_tipe
                            : null,
                        'tipe_manual' => isset($existingRow->tipe_manual) && trim((string) $existingRow->tipe_manual) !== ''
                            ? trim((string) $existingRow->tipe_manual)
                            : null,
                    ];
                }

                $this->repository->updateTargetHeader($idTarget, [
                    'tahun_target' => $tahunTarget,
                    'deskripsi'    => $deskripsi,
                    'status'       => 1,
                    'edit_by'      => $userId,
                    'updated_at'   => $now,
                ]);
                $action = 'update';
            } else {
                $idTarget = $this->repository->createTargetHeader([
                    'id_proyek'    => $idProyek,
                    'tahun_target' => $tahunTarget,
                    'deskripsi'    => $deskripsi,
                    'status'       => 1,
                    'add_by'       => $userId,
                    'edit_by'      => $userId,
                    'created_at'   => $now,
                    'updated_at'   => $now,
                ]);
                $action = 'create';
            }

            $this->db->table('target_siteplan_kavling')
                ->where('id_target', $idTarget)
                ->delete();

            $rows = [];
            foreach ($idKavlings as $idKavling) {
                $preserved = $existingTipeMap[$idKavling] ?? ['id_tipe' => null, 'tipe_manual' => null];
                $rows[] = [
                    'id_target'   => $idTarget,
                    'id_kavling'  => $idKavling,
                    'id_tipe'     => $preserved['id_tipe'],
                    'tipe_manual' => $preserved['tipe_manual'],
                    'created_at'  => $now,
                ];
            }
            $this->db->table('target_siteplan_kavling')->insertBatch($rows);

            $after = $this->makeSnapshot($idTarget);
            $this->recordTargetHistory(
                $idTarget,
                $idProyek,
                $action,
                $action === 'create' ? 'Target dibuat' : 'Target diperbaharui',
                $before,
                $after,
                $userId ?? 0,
                $now
            );

            $this->db->transCommit();
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }

        return $this->get($idTarget);
    }

    public function history(int $idTarget): array
    {
        return $this->repository->getHistory($idTarget);
    }

    public function requestTipeChange(array $data, int $actorId): array
    {
        $row = $this->locateTargetKavlingRow($data);
        $idTarget = (int) $row->id_target;
        $idKavling = (int) $row->id_kavling;

        $target = $this->repository->getById($idTarget);
        if (! $target) {
            throw new RuntimeException('Target tidak ditemukan');
        }

        if ((int) ($target->status ?? 0) !== 1) {
            throw new RuntimeException('Target siteplan tidak aktif');
        }

        $idProyek = (int) $target->id_proyek;
        if (! empty($data['id_proyek']) && (int) $data['id_proyek'] !== $idProyek) {
            throw new RuntimeException('Target tidak sesuai proyek');
        }

        $this->assertNoActiveConsumers([$idKavling]);
        $resolvedTipe = $this->resolveAndValidateTipeInput($idProyek, $this->normalizeNewTipeInput($data));

        $oldIdTipe = isset($row->id_tipe) && (int) $row->id_tipe > 0 ? (int) $row->id_tipe : null;
        $oldTipeManual = isset($row->tipe_manual) && trim((string) $row->tipe_manual) !== ''
            ? trim((string) $row->tipe_manual)
            : null;

        $newIdTipe = $resolvedTipe['id_tipe'];
        $newTipeManual = $resolvedTipe['tipe_manual'];

        if ($oldIdTipe === $newIdTipe && $oldTipeManual === $newTipeManual) {
            throw new RuntimeException('Tipe target baru harus berbeda dari tipe target saat ini');
        }

        $rawCatatan = isset($data['catatan_request']) ? trim((string) $data['catatan_request']) : '';
        $catatanRequest = $rawCatatan !== '' ? $rawCatatan : null;
        $effectiveActorId = $actorId > 0 ? $actorId : ($this->currentUserId() ?? 0);
        $now = date('Y-m-d H:i:s');

        $this->db->transBegin();

        try {
            $pending = $this->repository->findPendingRequest($idTarget, $idKavling);
            if ($pending) {
                throw new RuntimeException('Masih ada pengajuan ubah tipe yang menunggu persetujuan MKDT untuk kavling ini');
            }

            $idRequest = $this->repository->insertRequest([
                'id_target'       => $idTarget,
                'id_kavling'      => $idKavling,
                'old_id_tipe'     => $oldIdTipe,
                'old_tipe_manual' => $oldTipeManual,
                'new_id_tipe'     => $newIdTipe,
                'new_tipe_manual' => $newTipeManual,
                'catatan_request' => $catatanRequest,
                'status'          => 'pending',
                'request_by'      => $effectiveActorId > 0 ? $effectiveActorId : null,
                'request_at'      => $now,
            ]);

            $this->db->transCommit();
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }

        $requests = $this->repository->getRequests($idProyek, $idTarget);

        return [
            'request'  => $this->findRequestInList($requests, $idRequest) ?? $this->repository->findRequestById($idRequest),
            'requests' => $requests,
            'counts'   => $this->repository->getRequestCounts($idProyek, $idTarget),
        ];
    }

    public function listRequests(int $idProyek, ?int $idTarget = null, ?int $tahunTarget = null, ?string $status = null): array
    {
        if ($idProyek <= 0) {
            throw new RuntimeException('Proyek harus diisi');
        }

        $normalizedStatus = null;
        if ($status !== null) {
            $candidate = strtolower(trim($status));
            if ($candidate !== '' && $candidate !== 'all') {
                if (! in_array($candidate, ['pending', 'approved', 'rejected'], true)) {
                    throw new RuntimeException('Status filter request tidak valid');
                }
                $normalizedStatus = $candidate;
            }
        }

        return [
            'requests' => $this->repository->getRequests($idProyek, $idTarget, $tahunTarget, $normalizedStatus),
            'counts'   => $this->repository->getRequestCounts($idProyek, $idTarget, $tahunTarget),
        ];
    }

    public function reviewRequest(int $idRequest, string $decision, ?string $catatanApproval, int $approverId): array
    {
        if ($idRequest <= 0) {
            throw new RuntimeException('ID request tidak valid');
        }

        $normalizedDecision = match (strtolower(trim($decision))) {
            'approve', 'approved' => 'approved',
            'reject', 'rejected'   => 'rejected',
            default                => throw new RuntimeException('Keputusan review harus approve atau reject'),
        };

        $cleanCatatanApproval = $catatanApproval !== null && trim($catatanApproval) !== ''
            ? trim($catatanApproval)
            : null;

        $effectiveApproverId = $approverId > 0 ? $approverId : ($this->currentUserId() ?? 0);
        $now = date('Y-m-d H:i:s');

        $this->db->transBegin();

        try {
            $req = $this->repository->findRequestById($idRequest);
            if (! $req) {
                throw new RuntimeException('Request ubah tipe target tidak ditemukan');
            }

            if (strtolower((string) ($req->status ?? '')) !== 'pending') {
                throw new RuntimeException('Request ini sudah diproses sebelumnya');
            }

            $idTarget = (int) $req->id_target;
            $idKavling = (int) $req->id_kavling;

            $target = $this->repository->getById($idTarget);
            if (! $target) {
                throw new RuntimeException('Target tidak ditemukan');
            }

            $idProyek = (int) $target->id_proyek;

            if ($normalizedDecision === 'rejected') {
                $this->repository->updateRequest($idRequest, [
                    'status'           => 'rejected',
                    'approve_by'       => $effectiveApproverId > 0 ? $effectiveApproverId : null,
                    'approve_at'       => $now,
                    'catatan_approval' => $cleanCatatanApproval,
                ]);

                $this->db->transCommit();

                $requests = $this->repository->getRequests($idProyek, $idTarget);

                return [
                    'request'  => $this->findRequestInList($requests, $idRequest) ?? $this->repository->findRequestById($idRequest),
                    'target'   => $target,
                    'kavlings' => $this->repository->getKavlings($idTarget),
                    'history'  => $this->repository->getHistory($idTarget),
                    'requests' => $requests,
                    'counts'   => $this->repository->getRequestCounts($idProyek, $idTarget),
                ];
            }

            if ((int) ($target->status ?? 0) !== 1) {
                throw new RuntimeException('Target siteplan tidak aktif');
            }

            $targetKavlingRow = $this->repository->findTargetKavlingRow($idTarget, $idKavling);
            if (! $targetKavlingRow) {
                throw new RuntimeException('Kavling pada target tidak ditemukan');
            }

            $this->assertNoActiveConsumers([$idKavling]);
            $resolvedTipe = $this->resolveAndValidateTipeInput($idProyek, [
                'id_tipe'     => $req->new_id_tipe ?? null,
                'tipe_manual' => $req->new_tipe_manual ?? null,
            ]);

            $before = $this->makeSnapshot($idTarget);

            // Hanya memperbarui tipe target pada target_siteplan_kavling; tabel kavling master tidak boleh disentuh.
            $this->repository->updateTargetKavling((int) $targetKavlingRow->id_target_kavling, [
                'id_tipe'     => $resolvedTipe['id_tipe'],
                'tipe_manual' => $resolvedTipe['tipe_manual'],
                'updated_by'  => $effectiveApproverId > 0 ? $effectiveApproverId : null,
                'updated_at'  => $now,
            ]);

            $this->repository->updateTargetHeader($idTarget, [
                'edit_by'    => $effectiveApproverId > 0 ? $effectiveApproverId : null,
                'updated_at' => $now,
            ]);

            $this->repository->updateRequest($idRequest, [
                'status'           => 'approved',
                'approve_by'       => $effectiveApproverId > 0 ? $effectiveApproverId : null,
                'approve_at'       => $now,
                'catatan_approval' => $cleanCatatanApproval,
            ]);

            $after = $this->makeSnapshot($idTarget);
            $requestedByUserId = isset($req->request_by) && (int) $req->request_by > 0
                ? (int) $req->request_by
                : null;
            $approvedByUserId = $effectiveApproverId > 0 ? $effectiveApproverId : null;

            $this->recordTargetHistory(
                $idTarget,
                $idProyek,
                'target_approve_tipe',
                'ACC pengajuan ubah tipe target menjadi ' . ($resolvedTipe['tipe_label'] ?? '-'),
                $before,
                $after,
                $effectiveApproverId,
                $now,
                $idKavling,
                $requestedByUserId,
                $approvedByUserId,
                [
                    'id_request'       => $idRequest,
                    'catatan_request'  => $req->catatan_request ?? null,
                    'catatan_approval' => $cleanCatatanApproval,
                ]
            );

            $this->db->transCommit();
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }

        $requests = $this->repository->getRequests($idProyek, $idTarget);

        return [
            'request'  => $this->findRequestInList($requests, $idRequest) ?? $this->repository->findRequestById($idRequest),
            'target'   => $this->repository->getById($idTarget),
            'kavlings' => $this->repository->getKavlings($idTarget),
            'history'  => $this->repository->getHistory($idTarget),
            'requests' => $requests,
            'counts'   => $this->repository->getRequestCounts($idProyek, $idTarget),
        ];
    }

    private function locateTargetKavlingRows(array $data): array
    {
        $rawTargetKavlings = ! empty($data['id_target_kavlings'])
            ? $data['id_target_kavlings']
            : ($data['id_target_kavling'] ?? null);

        $idTargetKavlings = $this->normalizeKavlingIds($rawTargetKavlings);
        if ($idTargetKavlings !== []) {
            $rows = $this->repository->findTargetKavlingsByIds($idTargetKavlings);
            if (count($rows) !== count($idTargetKavlings)) {
                throw new RuntimeException('Kavling pada target tidak ditemukan');
            }

            return $rows;
        }

        $idTarget = isset($data['id_target']) ? (int) $data['id_target'] : 0;
        $rawKavlings = ! empty($data['id_kavlings'])
            ? $data['id_kavlings']
            : ($data['id_kavling'] ?? null);
        $idKavlings = $this->normalizeKavlingIds($rawKavlings);

        if ($idTarget > 0 && $idKavlings !== []) {
            $rows = $this->repository->findTargetKavlingsByTargetAndKavlingIds($idTarget, $idKavlings);
            if (count($rows) !== count($idKavlings)) {
                throw new RuntimeException('Kavling pada target tidak ditemukan');
            }

            return $rows;
        }

        throw new RuntimeException('Parameter id_target_kavling atau pasangan id_target dan id_kavling wajib diisi');
    }

    private function locateTargetKavlingRow(array $data): object
    {
        $idTargetKavling = isset($data['id_target_kavling']) ? (int) $data['id_target_kavling'] : 0;
        if ($idTargetKavling > 0) {
            $row = $this->repository->findTargetKavlingById($idTargetKavling);
            if (! $row) {
                throw new RuntimeException('Kavling pada target tidak ditemukan');
            }

            return $row;
        }

        $idTarget = isset($data['id_target']) ? (int) $data['id_target'] : 0;
        $idKavling = isset($data['id_kavling']) ? (int) $data['id_kavling'] : 0;
        if ($idTarget > 0 && $idKavling > 0) {
            $row = $this->repository->findTargetKavlingRow($idTarget, $idKavling);
            if (! $row) {
                throw new RuntimeException('Kavling pada target tidak ditemukan');
            }

            return $row;
        }

        throw new RuntimeException('Parameter id_target_kavling atau pasangan id_target dan id_kavling wajib diisi');
    }

    private function resolveAndValidateTipeInput(int $idProyek, array $data): array
    {
        $rawIdTipe = $data['id_tipe'] ?? null;
        $idTipe = ($rawIdTipe !== null && $rawIdTipe !== '' && (int) $rawIdTipe > 0)
            ? (int) $rawIdTipe
            : null;

        $rawTipeManual = isset($data['tipe_manual']) ? trim((string) $data['tipe_manual']) : '';
        $tipeManual = $rawTipeManual !== '' ? $rawTipeManual : null;

        if ($idTipe === null && $tipeManual === null) {
            throw new RuntimeException('Tipe rumah wajib diisi (pilih tipe master atau isi tipe manual)');
        }

        if ($idTipe !== null && $tipeManual !== null) {
            throw new RuntimeException('Pilih salah satu antara tipe master atau tipe manual');
        }

        if ($idTipe !== null) {
            $tipe = $this->repository->findTipeForProject($idTipe, $idProyek);
            if (! $tipe) {
                throw new RuntimeException('Tipe rumah tidak ditemukan pada proyek ini');
            }

            if ((int) ($tipe->is_active ?? 1) !== 1) {
                throw new RuntimeException('Tipe rumah sudah tidak aktif dan tidak dapat dipilih');
            }

            return [
                'id_tipe'     => $idTipe,
                'tipe_manual' => null,
                'tipe_label'  => $this->formatTipeLabel($tipe),
            ];
        }

        $length = function_exists('mb_strlen') ? mb_strlen($tipeManual) : strlen($tipeManual);
        if ($length > 255) {
            throw new RuntimeException('Tipe manual maksimal 255 karakter');
        }

        return [
            'id_tipe'     => null,
            'tipe_manual' => $tipeManual,
            'tipe_label'  => $tipeManual,
        ];
    }

    private function formatTipeLabel(object $tipe): string
    {
        $noTipe = trim((string) ($tipe->no_tipe_rumah ?? ''));
        $namaTipe = trim((string) ($tipe->tipe_rumah ?? ''));

        if ($noTipe !== '' && $namaTipe !== '') {
            return $noTipe . ' / ' . $namaTipe;
        }

        return $namaTipe !== '' ? $namaTipe : $noTipe;
    }

    private function assertNoActiveConsumers(array $idKavlings): void
    {
        $activeConsumers = $this->repository->findKavlingsWithActiveConsumer($idKavlings);
        if ($activeConsumers === []) {
            return;
        }

        $labels = array_map(static function (object $row): string {
            $no = trim((string) ($row->no_kavling ?? ''));

            return $no !== '' ? $no : ('#' . (int) ($row->id_kavling ?? 0));
        }, $activeConsumers);

        throw new RuntimeException(
            'Kavling sudah memiliki konsumen aktif dan tidak dapat dimasukkan ke target: ' . implode(', ', $labels)
        );
    }

    private function recordTargetHistory(
        int $idTarget,
        int $idProyek,
        string $action,
        string $summary,
        ?array $before,
        array $after,
        int $actorId,
        string $now,
        ?int $idKavling = null,
        ?int $requestedByUserId = null,
        ?int $approvedByUserId = null,
        array $extraMetadata = []
    ): void {
        $version = $this->repository->countTargetHistory($idTarget) + 1;
        $versionLabel = 'v' . $version;
        $diffSummary = $this->buildDiffSummary($before, $after, $action);

        $metadata = array_merge($extraMetadata, [
            'version'       => $version,
            'version_label' => $versionLabel,
            'diff_summary'  => $diffSummary,
        ]);

        if ($requestedByUserId !== null || $approvedByUserId !== null) {
            if ($requestedByUserId !== null && $requestedByUserId > 0) {
                $metadata['requested_by_user_id'] = $requestedByUserId;
                $reqUsername = $this->resolveUsername($requestedByUserId);
                if ($reqUsername !== null) {
                    $metadata['requested_by_username'] = $reqUsername;
                }
            }

            if ($approvedByUserId !== null && $approvedByUserId > 0) {
                $metadata['approved_by_user_id'] = $approvedByUserId;
                $appUsername = $this->resolveUsername($approvedByUserId);
                if ($appUsername !== null) {
                    $metadata['approved_by_username'] = $appUsername;
                }
            }
        } else {
            $actorUsername = $this->resolveUsername($actorId);
            if ($actorUsername !== null) {
                $metadata['requested_by_username'] = $actorUsername;
            }
        }

        $payload = [
            'reference_type' => 'target_siteplan',
            'reference_id'   => $idTarget,
            'id_proyek'      => $idProyek,
            'action'         => $action,
            'summary'        => $summary,
            'old_data'       => $before,
            'new_data'       => [
                'before' => $before,
                'after'  => $after,
            ],
            'metadata'       => $metadata,
            'add_by'         => $actorId > 0 ? $actorId : null,
            'created_at'     => $now,
        ];

        if ($idKavling !== null && $idKavling > 0) {
            $payload['id_kavling'] = $idKavling;
        }

        $this->historyService->log('target_siteplan', $payload);
    }

    private function buildDiffSummary(?array $before, array $after, string $action): array
    {
        $beforeKavlings = $this->indexKavlingsById($before['kavlings'] ?? []);
        $afterKavlings = $this->indexKavlingsById($after['kavlings'] ?? []);

        $added = [];
        $updated = [];
        $removed = [];

        foreach ($afterKavlings as $idKav => $item) {
            if (! isset($beforeKavlings[$idKav])) {
                $added[] = [
                    'id_kavling'  => $idKav,
                    'no_kavling'  => $item->no_kavling ?? null,
                    'id_tipe'     => $item->id_tipe ?? null,
                    'tipe_manual' => $item->tipe_manual ?? null,
                    'tipe_label'  => $item->tipe_label ?? null,
                ];
                continue;
            }

            $prev = $beforeKavlings[$idKav];
            if (($prev->id_tipe ?? null) !== ($item->id_tipe ?? null)
                || ($prev->tipe_manual ?? null) !== ($item->tipe_manual ?? null)) {
                $updated[] = [
                    'id_kavling'     => $idKav,
                    'no_kavling'     => $item->no_kavling ?? null,
                    'before_id_tipe' => $prev->id_tipe ?? null,
                    'before_manual'  => $prev->tipe_manual ?? null,
                    'before_label'   => $prev->tipe_label ?? null,
                    'after_id_tipe'  => $item->id_tipe ?? null,
                    'after_manual'   => $item->tipe_manual ?? null,
                    'after_label'    => $item->tipe_label ?? null,
                ];
            }
        }

        foreach ($beforeKavlings as $idKav => $prev) {
            if (! isset($afterKavlings[$idKav])) {
                $removed[] = [
                    'id_kavling'  => $idKav,
                    'no_kavling'  => $prev->no_kavling ?? null,
                    'id_tipe'     => $prev->id_tipe ?? null,
                    'tipe_manual' => $prev->tipe_manual ?? null,
                    'tipe_label'  => $prev->tipe_label ?? null,
                ];
            }
        }

        return [
            'action'        => $action,
            'added_count'   => count($added),
            'updated_count' => count($updated),
            'removed_count' => count($removed),
            'total_before'  => count($beforeKavlings),
            'total_after'   => count($afterKavlings),
            'added'         => $added,
            'updated'       => $updated,
            'removed'       => $removed,
        ];
    }

    private function indexKavlingsById(array $kavlings): array
    {
        $indexed = [];
        foreach ($kavlings as $row) {
            $obj = is_array($row) ? (object) $row : $row;
            $id = isset($obj->id_kavling) ? (int) $obj->id_kavling : 0;
            if ($id > 0) {
                $indexed[$id] = $obj;
            }
        }

        return $indexed;
    }

    private function normalizeKavlingIds($raw): array
    {
        if (is_int($raw) || is_float($raw)) {
            $raw = [(int) $raw];
        } elseif (is_string($raw)) {
            $raw = preg_split('/[;,]+/', $raw) ?: [];
        }

        if (! is_array($raw)) {
            return [];
        }

        $ids = [];
        foreach ($raw as $id) {
            if (is_array($id) || is_object($id)) {
                continue;
            }
            $id = (int) trim((string) $id);
            if ($id > 0) {
                $ids[] = $id;
            }
        }

        return array_values(array_unique($ids));
    }

    private function assertKavlingsBelongToProject(int $idProyek, array $idKavlings): void
    {
        $count = $this->repository->countKavlingsInProject($idProyek, $idKavlings);

        if ($count !== count($idKavlings)) {
            throw new RuntimeException('Ada kavling yang tidak sesuai proyek');
        }
    }

    private function makeSnapshot(int $idTarget): array
    {
        $target = $this->repository->getById($idTarget);
        $kavlings = $this->repository->getKavlings($idTarget);

        return [
            'target'   => $target,
            'kavlings' => $kavlings,
        ];
    }

    private function currentUserId(): ?int
    {
        return function_exists('user_id') && user_id() ? (int) user_id() : null;
    }

    private function normalizeNewTipeInput(array $data): array
    {
        $idTipe = null;
        foreach (['new_id_tipe', 'id_tipe'] as $key) {
            if (isset($data[$key]) && $data[$key] !== '' && (int) $data[$key] > 0) {
                $idTipe = (int) $data[$key];
                break;
            }
        }

        $tipeManual = null;
        foreach (['new_tipe_manual', 'tipe_manual'] as $key) {
            if (isset($data[$key]) && trim((string) $data[$key]) !== '') {
                $tipeManual = trim((string) $data[$key]);
                break;
            }
        }

        return [
            'id_tipe'     => $idTipe,
            'tipe_manual' => $tipeManual,
        ];
    }

    private function findRequestInList(array $requests, int $idRequest): ?object
    {
        foreach ($requests as $item) {
            if ((int) ($item->id_request ?? 0) === $idRequest) {
                return $item;
            }
        }

        return null;
    }

    private function resolveUsername(int $actorId): ?string
    {
        if ($actorId <= 0) {
            return null;
        }

        if ($this->db->tableExists('users')) {
            $row = $this->db->table('users')
                ->select('username')
                ->where('id', $actorId)
                ->get()
                ->getRow();

            if ($row && isset($row->username) && trim((string) $row->username) !== '') {
                return trim((string) $row->username);
            }
        }

        if (function_exists('user') && function_exists('user_id') && (int) user_id() === $actorId && user() && isset(user()->username)) {
            $username = trim((string) user()->username);
            if ($username !== '') {
                return $username;
            }
        }

        return 'User #' . $actorId;
    }
}

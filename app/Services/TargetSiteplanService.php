<?php

namespace App\Services;

use App\Models\TargetSiteplanHistoryModel;
use App\Models\TargetSiteplanKavlingModel;
use App\Models\TargetSiteplanModel;
use App\Repositories\TargetSiteplanRepository;
use RuntimeException;

class TargetSiteplanService
{
    protected $db;
    protected $targetModel;
    protected $targetKavlingModel;
    protected $historyModel;
    protected $repository;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->targetModel = new TargetSiteplanModel();
        $this->targetKavlingModel = new TargetSiteplanKavlingModel();
        $this->historyModel = new TargetSiteplanHistoryModel();
        $this->repository = new TargetSiteplanRepository();
    }

    public function listByProject(int $idProyek): array
    {
        return $this->repository->getByProject($idProyek);
    }

    public function get(int $idTarget): array
    {
        $target = $this->repository->getById($idTarget);
        if (!$target) {
            throw new RuntimeException('Target tidak ditemukan');
        }

        return [
            'target' => $target,
            'kavlings' => $this->repository->getKavlings($idTarget),
            'history' => $this->repository->getHistory($idTarget),
        ];
    }

    public function getKavlingTargetMap(int $idProyek): array
    {
        return $this->repository->getKavlingTargetMap($idProyek);
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

        $userId = $this->currentUserId();
        $this->db->transBegin();

        try {
            $before = null;
            if ($idTarget) {
                $target = $this->repository->getById($idTarget);
                if (!$target) {
                    throw new RuntimeException('Target tidak ditemukan');
                }
                if ((int) $target->id_proyek !== $idProyek) {
                    throw new RuntimeException('Target tidak sesuai proyek');
                }

                $before = $this->makeSnapshot($idTarget);
                $this->targetModel->update($idTarget, [
                    'tahun_target' => $tahunTarget,
                    'deskripsi' => $deskripsi,
                    'status' => 1,
                    'edit_by' => $userId,
                ]);
                $action = 'update';
            } else {
                $idTarget = (int) $this->targetModel->insert([
                    'id_proyek' => $idProyek,
                    'tahun_target' => $tahunTarget,
                    'deskripsi' => $deskripsi,
                    'status' => 1,
                    'add_by' => $userId,
                    'edit_by' => $userId,
                ], true);
                $action = 'create';
            }

            $this->targetKavlingModel->where('id_target', $idTarget)->delete();
            $rows = [];
            $now = date('Y-m-d H:i:s');
            foreach ($idKavlings as $idKavling) {
                $rows[] = [
                    'id_target' => $idTarget,
                    'id_kavling' => $idKavling,
                    'created_at' => $now,
                ];
            }
            $this->targetKavlingModel->insertBatch($rows);

            $after = $this->makeSnapshot($idTarget);
            $this->historyModel->insert([
                'id_target' => $idTarget,
                'aksi' => $action,
                'deskripsi' => $action === 'create' ? 'Target dibuat' : 'Target diperbaharui',
                'snapshot' => json_encode([
                    'before' => $before,
                    'after' => $after,
                ]),
                'add_by' => $userId,
                'created_at' => $now,
            ]);

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

    private function normalizeKavlingIds($raw): array
    {
        if (is_string($raw)) {
            $raw = explode(';', $raw);
        }

        if (!is_array($raw)) {
            return [];
        }

        $ids = [];
        foreach ($raw as $id) {
            $id = (int) $id;
            if ($id > 0) {
                $ids[] = $id;
            }
        }

        return array_values(array_unique($ids));
    }

    private function assertKavlingsBelongToProject(int $idProyek, array $idKavlings): void
    {
        $count = $this->db->table('kavling')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster')
            ->where('cluster.id_proyek', $idProyek)
            ->whereIn('kavling.id_kavling', $idKavlings)
            ->countAllResults();

        if ($count !== count($idKavlings)) {
            throw new RuntimeException('Ada kavling yang tidak sesuai proyek');
        }
    }

    private function makeSnapshot(int $idTarget): array
    {
        $target = $this->repository->getById($idTarget);
        $kavlings = $this->repository->getKavlings($idTarget);

        return [
            'target' => $target,
            'kavlings' => $kavlings,
        ];
    }

    private function currentUserId(): ?int
    {
        return function_exists('user_id') && user_id() ? (int) user_id() : null;
    }
}

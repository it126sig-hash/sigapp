<?php

namespace App\Services;

use App\Models\TiketMasalahModel;
use App\Models\TiketMasalahUserModel;
use App\Models\TiketMasalahFotoModel;
use App\Models\TiketMasalahProgressModel;
use App\Repositories\TiketMasalahRepository;

class TiketMasalahService
{
    protected $tiketModel;
    protected $tiketUserModel;
    protected $tiketFotoModel;
    protected $tiketProgressModel;
    protected $repository;
    protected $db;
    protected $fileAccessService;
    protected $notifikasiService;

    public function __construct()
    {
        $this->tiketModel = new TiketMasalahModel();
        $this->tiketUserModel = new TiketMasalahUserModel();
        $this->tiketFotoModel = new TiketMasalahFotoModel();
        $this->tiketProgressModel = new TiketMasalahProgressModel();
        $this->repository = new TiketMasalahRepository();
        $this->db = \Config\Database::connect();
        $this->fileAccessService = new FileAccessService();
        $this->notifikasiService = new NotifikasiService();
    }

    public function getListByRef(string $refType, int $refId): array
    {
        $list = $this->repository->getListWithSummary($refType, $refId);
        foreach ($list as $item) {
            $item->foto_url_1 = !empty($item->foto_path_1) ? $this->fileAccessService->pathUrl('tiket_masalah', $item->foto_path_1) : null;
        }
        return $list;
    }

    public function getDetail(int $idTiket): ?object
    {
        $tiket = $this->repository->getDetailFull($idTiket);
        if ($tiket) {
            foreach ($tiket->foto as $f) {
                $f->url = $this->fileAccessService->pathUrl('tiket_masalah', $f->file_path);
            }
        }
        return $tiket;
    }

    public function getProgress(int $idTiket, int $limit, int $offset): array
    {
        $progress = $this->repository->getProgressPaginated($idTiket, $limit, $offset);
        foreach ($progress as $p) {
            $p->foto_urls = [];
            if (!empty($p->foto_paths)) {
                $paths = array_filter(explode(';', $p->foto_paths));
                foreach ($paths as $path) {
                    $p->foto_urls[] = $this->fileAccessService->pathUrl('tiket_masalah', $path);
                }
            }
        }
        return $progress;
    }

    public function createTiket(array $data, array $files): array
    {
        $this->db->transStart();

        $userId = user_id();
        $data['pic_user_id'] = $userId;
        $data['status'] = 'dibuat';

        $idTiket = $this->tiketModel->insert($data);

        // Upload photos
        $lok = 'uploads/tiket_masalah/' . date('Ymd') . '/';
        foreach ($files as $img) {
            if ($img->isValid() && !$img->hasMoved()) {
                $name = $img->getRandomName();
                $path = $this->fileAccessService->storeAs($img, $lok, $name);

                // compress image (server-side safety net)
                $absPath = $this->fileAccessService->privatePath($path);
                $this->compressImageIfPossible($absPath);

                $this->tiketFotoModel->insert([
                    'id_tiket_masalah' => $idTiket,
                    'file_path' => $path,
                    'file_name' => $img->getClientName(),
                    'uploaded_by' => $userId
                ]);
            }
        }

        // Assign users
        $assignedUsers = $data['assigned_users'] ?? [];
        if (!is_array($assignedUsers)) {
            $assignedUsers = explode(',', $assignedUsers);
        }

        // Notifikasi ke departemen user pembuat
        $userGroupId = session()->get('group_id');
        if ($userGroupId) {
            $this->notifikasiService->tambah_notif(
                $userGroupId,                   // group_target = departemen user
                "Tiket masalah baru: " . substr($data['keterangan'], 0, 80),
                $userId,                        // add_by
                $data['ref_type'] == 'kavling' ? $data['ref_id'] : null,
                null,                           // id_konsumen
                'tiket_masalah|' . $data['ref_type'] . '|' . $data['ref_id'], // type
                $data['id_proyek'] ?? null      // id_proyek
            );
        }

        foreach ($assignedUsers as $uid) {
            $uid = (int) $uid;
            if ($uid > 0 && $uid != $userId) {
                $this->tiketUserModel->insert([
                    'id_tiket_masalah' => $idTiket,
                    'user_id' => $uid
                ]);

                // Cek departemen user yang di-assign
                $assigneeGroupRow = $this->db->table('auth_groups_users')
                    ->select('group_id')
                    ->where('user_id', $uid)
                    ->get()->getRow();
                $assigneeGroupId = $assigneeGroupRow ? $assigneeGroupRow->group_id : null;

                // Deduplikasi: jika dia ada di departemen pembuat yang sudah dinotif, skip individual notif
                if ($userGroupId && $assigneeGroupId == $userGroupId) {
                    continue;
                }

                // Notify user
                $this->notifikasiService->tambah_notif_user(
                    $uid,
                    "Tiket masalah baru ditugaskan ke Anda: " . substr($data['keterangan'], 0, 50),
                    $userId,
                    $data['ref_type'] == 'kavling' ? $data['ref_id'] : null,
                    null,
                    'tiket_masalah|' . $data['ref_type'] . '|' . $data['ref_id'],
                    $data['id_proyek'] ?? null
                );
            }
        }

        $this->db->transComplete();

        return [
            'success' => $this->db->transStatus(),
            'id' => $idTiket
        ];
    }

    public function addProgress(int $idTiket, array $data, array $files): array
    {
        $tiket = $this->tiketModel->find($idTiket);
        if (!$tiket) {
            return ['success' => false, 'message' => 'Tiket tidak ditemukan'];
        }

        if (in_array($tiket->status, ['selesai', 'batal'])) {
            return ['success' => false, 'message' => 'Tiket sudah selesai atau dibatalkan, tidak dapat diubah'];
        }

        $userId = user_id();
        $groupId = session()->get('group_id');
        $isPic = ($tiket->pic_user_id == $userId) || ($groupId == 1);

        if (!$isPic) {
            // Cek apakah user assigned
            $isAssigned = $this->tiketUserModel->where('id_tiket_masalah', $idTiket)
                ->where('user_id', $userId)->countAllResults() > 0;

            if (!$isAssigned) {
                return ['success' => false, 'message' => 'Anda tidak memiliki akses ke tiket ini'];
            }
        }

        $this->db->transStart();

        $statusSebelum = $tiket->status;
        $statusSesudah = $data['status'] ?? null;

        if ($statusSesudah && $statusSesudah != $statusSebelum) {
            if (!$isPic) {
                return ['success' => false, 'message' => 'Hanya PIC yang dapat mengubah status tiket'];
            }
            $this->tiketModel->update($idTiket, ['status' => $statusSesudah]);
        } else {
            $statusSesudah = null;
            $statusSebelum = null;
        }

        // Upload photos
        $lok = 'uploads/tiket_masalah/' . date('Ymd') . '/';
        $fotoPaths = [];
        foreach ($files as $img) {
            if ($img->isValid() && !$img->hasMoved()) {
                $name = $img->getRandomName();
                $path = $this->fileAccessService->storeAs($img, $lok, $name);

                // compress image
                $absPath = $this->fileAccessService->privatePath($path);
                $this->compressImageIfPossible($absPath);

                $fotoPaths[] = $path;
            }
        }

        $this->tiketProgressModel->insert([
            'id_tiket_masalah' => $idTiket,
            'user_id' => $userId,
            'keterangan' => $data['keterangan'] ?? '',
            'status_sebelum' => $statusSebelum,
            'status_sesudah' => $statusSesudah,
            'foto_paths' => !empty($fotoPaths) ? implode(';', $fotoPaths) : null
        ]);

        // Notifications
        $assignedUsers = $this->tiketUserModel->where('id_tiket_masalah', $idTiket)->findAll();
        $notifyUids = array_map(function($u) { return $u->user_id; }, $assignedUsers);
        if (!in_array($tiket->pic_user_id, $notifyUids)) {
            $notifyUids[] = $tiket->pic_user_id;
        }

        foreach ($notifyUids as $uid) {
            if ($uid != $userId) {
                $this->notifikasiService->tambah_notif_user(
                    $uid,
                    "Update progress pada tiket masalah: " . substr($data['keterangan'] ?? '', 0, 50),
                    $userId,
                    $tiket->ref_type == 'kavling' ? $tiket->ref_id : null,
                    null,
                    'tiket_masalah|' . $tiket->ref_type . '|' . $tiket->ref_id,
                    $tiket->id_proyek ?? null
                );
            }
        }

        $this->db->transComplete();

        return [
            'success' => $this->db->transStatus()
        ];
    }

    public function getRefInfo(string $refType, int $refId): ?object
    {
        if ($refType === 'kavling') {
            return $this->db->table('kavling k')
                ->select("
                    k.id_kavling as id,
                    k.no_kavling,
                    'kavling' as tipe,
                    j.nama_jalan,
                    cl.nama_cluster,
                    p.nama_proyek,
                    p.id_proyek,
                    COALESCE(pr.progres_bangunan, 0) as progres_bangunan,
                    t.tipe_rumah,
                    t.lb,
                    t.lt,
                    t.keterangan as tipe_keterangan
                ")
                ->join('jalan j', 'j.id_jalan = k.id_jalan')
                ->join('cluster cl', 'cl.id_cluster = j.id_cluster')
                ->join('proyek p', 'p.id_proyek = cl.id_proyek')
                ->join('produksi pr', 'pr.id_produksi = k.id_produksi', 'left')
                ->join('tipe t', 't.id_tipe = k.id_tipe', 'left')
                ->where('k.id_kavling', $refId)
                ->get()
                ->getRow();
        } else {
            return $this->db->table('others o')
                ->select("
                    o.id,
                    o.nama as no_kavling,
                    o.tipe,
                    j.nama_jalan,
                    cl.nama_cluster,
                    p.nama_proyek,
                    p.id_proyek,
                    COALESCE(o.progres, 0) as progres_bangunan
                ")
                ->join('jalan j', 'j.id_jalan = o.id_jalan')
                ->join('cluster cl', 'cl.id_cluster = j.id_cluster')
                ->join('proyek p', 'p.id_proyek = cl.id_proyek')
                ->where('o.id', $refId)
                ->get()
                ->getRow();
        }
    }

    public function getUserList(): array
    {
        return $this->db->table('users')
            ->select('id, username, name')
            ->where('active', 1)
            ->where('deleted_at IS NULL', null, false)
            ->orderBy('username', 'ASC')
            ->get()
            ->getResult();
    }

    private function compressImageIfPossible(string $absolutePath): void
    {
        if (!is_file($absolutePath)) {
            return;
        }

        $size = @getimagesize($absolutePath);
        if (!$size) {
            return;
        }

        [$width, $height] = $size;
        $maxDimension = 1920;

        try {
            $image = \Config\Services::image()->withFile($absolutePath);
            if ($width > $maxDimension || $height > $maxDimension) {
                $image->resize($maxDimension, $maxDimension, true, 'auto');
            }
            $image->save($absolutePath, 78);
        } catch (\Throwable $e) {
            log_message('warning', 'Gagal kompres foto tiket masalah: ' . $e->getMessage());
        }
    }
}

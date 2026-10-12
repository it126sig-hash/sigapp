<?php

namespace App\Services;

use App\Controllers\Notif;
use App\Models\ClusterModel;
use App\Repositories\ClusterRepository;

class ClusterService
{
    protected ClusterModel $clusterModel;
    protected ClusterRepository $clusterRepository;
    protected HistoryService $historyService;
    protected Notif $notif;
    protected $validation;

    public function __construct()
    {
        $this->clusterModel      = new ClusterModel();
        $this->clusterRepository = new ClusterRepository();
        $this->historyService    = new HistoryService();
        $this->notif             = new Notif();
        $this->validation        = \Config\Services::validation();
    }

    public function getDataTables(array $params): array
    {
        $params['id_proyek'] = resolve_active_proyek_id($params['id_proyek'] ?? null);

        $result = $this->clusterRepository->getDataTables($params);
        $data   = [];

        foreach ($result['rows'] as $key => $value) {
            $ops = '<div class="btn-group">';
            $ops .= '	<button type="button" class="btn btn-outline-primary waves-effect btn-sm" onclick="edit(' . $value->id_cluster . ')"><i class="fa fa-edit"></i></button>';
            $ops .= '	<button type="button" class="btn btn-outline-danger waves-effect btn-sm" onclick="remove(' . $value->id_cluster . ')"><i class="fa fa-trash"></i></button>';
            $ops .= '</div>';

            $data[$key] = [
                $value->id_cluster,
                $value->id_proyek,
                $value->nama_proyek,
                $value->nama_cluster,
                $value->jumlah_bangun,
                '<span class="badge badge-light-success badge-pill">' . $value->total_akad . '</span>',
                '<span class="badge badge-light-warning badge-pill">' . $value->total_booking . '</span>',
                '<span class="badge badge-light-secondary badge-pill">' . $value->total_belum_terjual . '</span>',
                $this->isActiveBadge($value->is_active, "Aktif", "Tidak"),
                $ops,
            ];
        }

        return [
            'draw'            => $result['draw'],
            'recordsTotal'    => $result['recordsTotal'],
            'recordsFiltered' => $result['recordsFiltered'],
            'data'            => $data,
        ];
    }

    public function getAll(array $params): array
    {
        $params['id_proyek'] = resolve_active_proyek_id($params['id_proyek'] ?? null);
        $clusters            = $this->clusterRepository->getAll($params);
        $data                = [];

        foreach ($clusters as $key => $value) {
            $ops = '<div class="btn-group">';
            $ops .= '	<button type="button" class="btn btn-outline-primary waves-effect btn-sm" onclick="edit(' . $value->id_cluster . ')"><i class="fa fa-edit"></i></button>';
            $ops .= '	<button type="button" class="btn btn-outline-danger waves-effect btn-sm" onclick="remove(' . $value->id_cluster . ')"><i class="fa fa-trash"></i></button>';
            $ops .= '</div>';

            $data[$key] = [
                $value->id_cluster,
                $value->id_proyek,
                $value->nama_proyek,
                $value->nama_cluster,
                $value->is_active,
                $ops,
            ];
        }

        return $data;
    }

    public function getOne(int $idCluster): ?object
    {
        if ($idCluster <= 0) {
            return null;
        }

        return $this->clusterModel->where('id_cluster', $idCluster)->first();
    }

    public function add(array $postData): array
    {
        $fields = [
            'id_cluster'   => $postData['idCluster'] ?? null,
            'id_proyek'    => $postData['idProyek'] ?? null,
            'nama_cluster' => $postData['namaCluster'] ?? null,
            'is_active'    => $postData['isActive'] ?? null,
        ];

        $this->validation->setRules([
            'id_proyek'    => ['label' => 'Id proyek', 'rules' => 'permit_empty|max_length[255]'],
            'nama_cluster' => ['label' => 'Nama cluster', 'rules' => 'permit_empty|max_length[255]'],
            'is_active'    => ['label' => 'Is active', 'rules' => 'permit_empty|max_length[1]'],
        ]);

        if (! $this->validation->run($fields)) {
            return [
                'success'  => false,
                'messages' => $this->validation->listErrors(),
            ];
        }

        if ($this->clusterModel->insert($fields)) {
            $insertId = (int) $this->clusterModel->getInsertID();

            $this->historyService->log('master_cluster', [
                'reference_type' => 'cluster',
                'reference_id'   => $insertId,
                'action'         => 'insert',
                'new_data'       => $fields,
            ]);

            $this->notif->tambah_notif(
                "6",
                "Menambahkan Master Cluster: " . ($fields['nama_cluster'] ?? ''),
                user_id(),
                null,
                null,
                \App\Enums\NotificationEvent::MASTER_CLUSTER,
                $fields['id_proyek']
            );

            return [
                'success'  => true,
                'messages' => 'Data has been inserted successfully',
            ];
        }

        return [
            'success'  => false,
            'messages' => 'Insertion error!',
        ];
    }

    public function edit(array $postData): array
    {
        $fields = [
            'id_cluster'   => $postData['idCluster'] ?? null,
            'id_proyek'    => $postData['idProyek'] ?? null,
            'nama_cluster' => $postData['namaCluster'] ?? null,
            'is_active'    => $postData['isActive'] ?? null,
        ];

        $this->validation->setRules([
            'id_proyek'    => ['label' => 'Id proyek', 'rules' => 'permit_empty|max_length[255]'],
            'nama_cluster' => ['label' => 'Nama cluster', 'rules' => 'permit_empty|max_length[255]'],
            'is_active'    => ['label' => 'Is active', 'rules' => 'permit_empty|max_length[1]'],
        ]);

        if (! $this->validation->run($fields)) {
            return [
                'success'  => false,
                'messages' => $this->validation->listErrors(),
            ];
        }

        $idCluster = (int) $fields['id_cluster'];
        $oldData   = $this->clusterModel->find($idCluster);

        if ($this->clusterModel->update($idCluster, $fields)) {
            $this->historyService->log('master_cluster', [
                'reference_type' => 'cluster',
                'reference_id'   => $idCluster,
                'action'         => 'update',
                'old_data'       => $oldData,
                'new_data'       => $fields,
            ]);

            $this->notif->tambah_notif(
                "6",
                "Mengubah Master Cluster: " . ($fields['nama_cluster'] ?? ''),
                user_id(),
                null,
                null,
                \App\Enums\NotificationEvent::MASTER_CLUSTER,
                $fields['id_proyek']
            );

            return [
                'success'  => true,
                'messages' => 'Successfully updated',
            ];
        }

        return [
            'success'  => false,
            'messages' => 'Update error!',
        ];
    }

    public function remove(int $idCluster): array
    {
        if ($idCluster <= 0) {
            return [
                'success'  => false,
                'messages' => 'ID cluster tidak valid',
            ];
        }

        $jalanCount = $this->clusterRepository->countJalan($idCluster);
        if ($jalanCount > 0) {
            $kavlingCount = $this->clusterRepository->countKavling($idCluster);

            return [
                'success'  => false,
                'messages' => "Tidak dapat menghapus cluster karena sudah memiliki {$jalanCount} jalan dan {$kavlingCount} kavling terdaftar.",
            ];
        }

        if ($this->clusterModel->where('id_cluster', $idCluster)->delete()) {
            return [
                'success'  => true,
                'messages' => 'Deletion succeeded',
            ];
        }

        return [
            'success'  => false,
            'messages' => 'Deletion error!',
        ];
    }

    public function isActiveBadge($id, string $texts, string $textf): string
    {
        $r = '<span class="badge badge-pill badge-light-danger" text-capitalized="">' . $textf . '</span>';
        if ($id == "1") {
            $r = '<span class="badge badge-pill badge-light-success" text-capitalized="">' . $texts . '</span>';
        }

        return $r;
    }
}

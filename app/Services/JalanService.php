<?php

namespace App\Services;

use App\Models\JalanModel;
use App\Models\ProyekModel;
use App\Repositories\JalanRepository;
use CodeIgniter\HTTP\IncomingRequest;
use App\Services\HistoryService;
use App\Controllers\Notif;

class JalanService
{
    private JalanModel $jalanModel;
    private ProyekModel $proyekModel;
    private JalanRepository $jalanRepository;
    private $validation;
    private HistoryService $historyService;
    private Notif $notif;

    public function __construct()
    {
        $this->jalanModel = new JalanModel();
        $this->proyekModel = new ProyekModel();
        $this->jalanRepository = new JalanRepository();
        $this->validation = \Config\Services::validation();
        $this->historyService = new HistoryService();
        $this->notif = new Notif();
    }

    public function getIndexData(): array
    {
        return [
            'proyek' => $this->proyekModel->select('id_proyek, nama_proyek')->findAll(),
            'cluster' => [],
            'controller' => 'jalan',
            'title' => 'Jalan',
        ];
    }

    public function getAll(array $params): array
    {
        $rows = [];

        foreach ($this->jalanRepository->getAll($params) as $key => $value) {
            $rows[$key] = [
                $value->id_jalan,
                $value->id_cluster,
                $value->nama_cluster,
                $value->nama_jalan,
                $this->actionButtons((int) $value->id_jalan),
            ];
        }

        return $rows;
    }

    public function getDataTables(array $params): array
    {
        $result = $this->jalanRepository->getDataTables($params);
        $rows = [];
        $no = $result['start'];

        foreach ($result['rows'] as $key => $value) {
            $no++;

            $rows[$key] = [
                $no,
                $value->id_jalan,
                $value->nama_cluster,
                $value->nama_jalan,
                $this->actionButtons((int) $value->id_jalan),
            ];
        }

        return [
            'draw' => $result['draw'],
            'recordsTotal' => $result['recordsTotal'],
            'recordsFiltered' => $result['recordsFiltered'],
            'data' => $rows,
        ];
    }

    public function getOne(int $idJalan): ?object
    {
        return $this->jalanModel
            ->select('jalan.*, cluster.nama_cluster')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster', 'left')
            ->where('id_jalan', $idJalan)
            ->first();
    }

    public function add(IncomingRequest $request): array
    {
        $fields = $this->requestFields($request);

        $validation = $this->validate($fields);
        if ($validation !== true) {
            return ['success' => false, 'messages' => $validation];
        }

        if ($this->jalanModel->insert($fields)) {
            $insertId = (int) $this->jalanModel->getInsertID();
            $this->historyService->log('master_jalan', [
                'reference_type' => 'jalan',
                'reference_id' => $insertId,
                'action' => 'insert',
                'new_data' => $fields
            ]);
            $db = db_connect();
            $idProyek = $db->table('cluster')->select('id_proyek')->where('id_cluster', $fields['id_cluster'])->get()->getRow()->id_proyek ?? null;
            $this->notif->tambah_notif("6", "Menambahkan Master Jalan: " . ($fields['nama_jalan'] ?? ''), user_id(), null, null, \App\Enums\NotificationEvent::MASTER_JALAN, $idProyek);

            return ['success' => true, 'messages' => 'Data has been inserted successfully'];
        }

        return ['success' => false, 'messages' => 'Insertion error!'];
    }

    public function edit(IncomingRequest $request): array
    {
        $fields = $this->requestFields($request);
        $idJalan = (int) ($fields['id_jalan'] ?? 0);

        if ($idJalan <= 0) {
            return ['success' => false, 'messages' => 'Id jalan tidak valid'];
        }

        $validation = $this->validate($fields);
        if ($validation !== true) {
            return ['success' => false, 'messages' => $validation];
        }

        $oldData = $this->jalanModel->find($idJalan);
        if ($this->jalanModel->update($idJalan, $fields)) {
            $this->historyService->log('master_jalan', [
                'reference_type' => 'jalan',
                'reference_id' => $idJalan,
                'action' => 'update',
                'old_data' => $oldData,
                'new_data' => $fields
            ]);
            $db = db_connect();
            $idProyek = $db->table('cluster')->select('id_proyek')->where('id_cluster', $fields['id_cluster'])->get()->getRow()->id_proyek ?? null;
            $this->notif->tambah_notif("6", "Mengubah Master Jalan: " . ($fields['nama_jalan'] ?? ''), user_id(), null, null, \App\Enums\NotificationEvent::MASTER_JALAN, $idProyek);

            return ['success' => true, 'messages' => 'Successfully updated'];
        }

        return ['success' => false, 'messages' => 'Update error!'];
    }

    public function remove(int $idJalan): array
    {
        if ($idJalan <= 0) {
            return ['success' => false, 'messages' => 'Id jalan tidak valid'];
        }

        if ($this->jalanModel->where('id_jalan', $idJalan)->delete()) {
            return ['success' => true, 'messages' => 'Deletion succeeded'];
        }

        return ['success' => false, 'messages' => 'Deletion error!'];
    }

    private function requestFields(IncomingRequest $request): array
    {
        return [
            'id_jalan' => $request->getPost('idJalan'),
            'id_cluster' => $request->getPost('idCluster'),
            'nama_jalan' => $request->getPost('namaJalan'),
        ];
    }

    private function validate(array $fields)
    {
        $this->validation->reset();
        $this->validation->setRules([
            'id_cluster' => ['label' => 'Id cluster', 'rules' => 'permit_empty|max_length[255]'],
            'nama_jalan' => ['label' => 'Nama jalan', 'rules' => 'permit_empty|max_length[255]'],
        ]);

        if (!$this->validation->run($fields)) {
            return $this->validation->listErrors();
        }

        return true;
    }

    private function actionButtons(int $idJalan): string
    {
        $ops = '<div class="btn-group">';
        $ops .= '	<button type="button" class="btn btn-sm btn-info" onclick="edit(' . $idJalan . ')"><i class="fa fa-edit"></i></button>';
        $ops .= '	<button type="button" class="btn btn-sm btn-danger" onclick="remove(' . $idJalan . ')"><i class="fa fa-trash"></i></button>';
        $ops .= '</div>';

        return $ops;
    }
}

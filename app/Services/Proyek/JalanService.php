<?php

namespace App\Services\Proyek;

use App\Models\JalanModel;
use App\Models\ProyekModel;
use App\Repositories\Proyek\JalanRepository;
use App\Services\HistoryService;
use App\Services\NotifikasiService;
use CodeIgniter\HTTP\IncomingRequest;

class JalanService
{
    private JalanModel $jalanModel;
    private ProyekModel $proyekModel;
    private JalanRepository $jalanRepository;
    private $validation;
    private HistoryService $historyService;
    private NotifikasiService $notif;

    public function __construct()
    {
        $this->jalanModel = new JalanModel();
        $this->proyekModel = new ProyekModel();
        $this->jalanRepository = new JalanRepository();
        $this->validation = \Config\Services::validation();
        $this->historyService = new HistoryService();
        $this->notif = new NotifikasiService();
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
        $isRaw = ($params['format'] ?? '') === 'raw';

        foreach ($this->jalanRepository->getAll($params) as $key => $value) {
            if ($isRaw) {
                $rows[$key] = [
                    'id_jalan'            => (int) $value->id_jalan,
                    'id_cluster'          => (int) $value->id_cluster,
                    'nama_cluster'        => $value->nama_cluster,
                    'nama_proyek'         => $value->nama_proyek ?? '',
                    'nama_jalan'          => $value->nama_jalan,
                    'total_kavling'       => (int) ($value->total_kavling ?? 0),
                    'total_akad'          => (int) ($value->total_akad ?? 0),
                    'total_booking'       => (int) ($value->total_booking ?? 0),
                    'total_belum_terjual' => (int) ($value->total_belum_terjual ?? 0),
                ];
            } else {
                $rows[$key] = [
                    (int) $value->id_jalan,
                    (int) $value->id_cluster,
                    $value->nama_cluster,
                    $value->nama_jalan,
                    (int) $value->id_jalan,
                ];
            }
        }

        return $rows;
    }

    public function getDataTables(array $params): array
    {
        $result = $this->jalanRepository->getDataTables($params);
        $rows = [];
        $no = ($result['start'] ?? 0);
        $isRaw = ($params['format'] ?? '') === 'raw';

        foreach ($result['rows'] as $key => $value) {
            $no++;

            if ($isRaw) {
                $rows[$key] = [
                    'id_jalan'            => (int) $value->id_jalan,
                    'id_cluster'          => (int) $value->id_cluster,
                    'nama_cluster'        => $value->nama_cluster,
                    'nama_jalan'          => $value->nama_jalan,
                    'total_kavling'       => (int) $value->total_kavling,
                    'total_akad'          => (int) $value->total_akad,
                    'total_booking'       => (int) $value->total_booking,
                    'total_belum_terjual' => (int) $value->total_belum_terjual,
                ];
            } else {
                $rows[$key] = [
                    $no,
                    (int) $value->id_jalan,
                    $value->nama_cluster,
                    $value->nama_jalan,
                    (int) $value->total_kavling,
                    (int) $value->total_akad,
                    (int) $value->total_booking,
                    (int) $value->total_belum_terjual,
                    (int) $value->id_jalan,
                ];
            }
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
        $db = db_connect();
        $row = $db->table('jalan')
            ->select("id_jalan, jalan.id_cluster, cluster.nama_cluster, cluster.id_proyek, proyek.nama_proyek, nama_jalan,
                (SELECT COUNT(kavling.id_kavling) FROM kavling WHERE kavling.id_jalan = jalan.id_jalan) as total_kavling,
                (SELECT COUNT(kavling.id_kavling) FROM kavling JOIN mkdt ON mkdt.id_mkdt = kavling.id_mkdt WHERE kavling.id_jalan = jalan.id_jalan AND mkdt.status_mkdt = 'Akad' AND mkdt.is_batal = 0) as total_akad,
                (SELECT COUNT(kavling.id_kavling) FROM kavling JOIN mkdt ON mkdt.id_mkdt = kavling.id_mkdt WHERE kavling.id_jalan = jalan.id_jalan AND mkdt.status_mkdt = 'Booking' AND mkdt.is_batal = 0) as total_booking,
                (SELECT COUNT(kavling.id_kavling) FROM kavling LEFT JOIN mkdt ON mkdt.id_mkdt = kavling.id_mkdt WHERE kavling.id_jalan = jalan.id_jalan AND (kavling.id_mkdt IS NULL OR mkdt.status_mkdt = 'Batal' OR mkdt.is_batal = 1)) as total_belum_terjual
            ")
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster')
            ->join('proyek', 'proyek.id_proyek = cluster.id_proyek')
            ->where('jalan.id_jalan', $idJalan)
            ->get()
            ->getRow();

        if ($row) {
            return $row;
        }

        return $this->jalanModel
            ->select('jalan.*, cluster.nama_cluster')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster', 'left')
            ->where('id_jalan', $idJalan)
            ->first();
    }

    public function add($input): array
    {
        $fields = $this->requestFields($input);
        unset($fields['id_jalan']);

        $validation = $this->validate($fields);
        if ($validation !== true) {
            return ['success' => false, 'messages' => $validation];
        }

        if ($this->jalanModel->insert($fields)) {
            $insertId = (int) $this->jalanModel->getInsertID();
            $this->historyService->log('master_jalan', [
                'reference_type' => 'jalan',
                'reference_id'   => $insertId,
                'action'         => 'insert',
                'new_data'       => $fields,
            ]);
            $db = db_connect();
            $idProyek = $db->table('cluster')->select('id_proyek')->where('id_cluster', $fields['id_cluster'])->get()->getRow()->id_proyek ?? null;
            $this->notif->tambah_notif("6", "Menambahkan Master Jalan: " . ($fields['nama_jalan'] ?? ''), user_id(), null, null, \App\Enums\NotificationEvent::MASTER_JALAN, $idProyek);

            return [
                'success'  => true,
                'messages' => 'Data has been inserted successfully',
                'id_jalan' => $insertId,
            ];
        }

        return ['success' => false, 'messages' => 'Insertion error!'];
    }

    public function edit($input): array
    {
        $fields = $this->requestFields($input);
        $idJalan = (int) ($fields['id_jalan'] ?? 0);

        if ($idJalan <= 0) {
            return ['success' => false, 'messages' => 'Id jalan tidak valid'];
        }

        $validation = $this->validate($fields);
        if ($validation !== true) {
            return ['success' => false, 'messages' => $validation];
        }

        $oldData = $this->jalanModel->find($idJalan);
        $updateData = [
            'id_cluster' => $fields['id_cluster'],
            'nama_jalan' => $fields['nama_jalan'],
        ];

        if ($this->jalanModel->update($idJalan, $updateData)) {
            $this->historyService->log('master_jalan', [
                'reference_type' => 'jalan',
                'reference_id'   => $idJalan,
                'action'         => 'update',
                'old_data'       => $oldData,
                'new_data'       => $updateData,
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

        $db = db_connect();
        $kavlingCount = $db->table('kavling')->where('id_jalan', $idJalan)->countAllResults();
        if ($kavlingCount > 0) {
            return ['success' => false, 'messages' => "Tidak dapat menghapus jalan karena sudah memiliki $kavlingCount kavling terdaftar."];
        }

        if ($this->jalanModel->where('id_jalan', $idJalan)->delete()) {
            return ['success' => true, 'messages' => 'Deletion succeeded'];
        }

        return ['success' => false, 'messages' => 'Deletion error!'];
    }

    private function requestFields($input): array
    {
        if ($input instanceof IncomingRequest) {
            $post = $input->getJSON(true) ?: (array) $input->getPost();
        } else {
            $post = (array) $input;
        }

        return [
            'id_jalan'   => (int) ($post['id_jalan'] ?? $post['idJalan'] ?? 0),
            'id_cluster' => (int) ($post['id_cluster'] ?? $post['idCluster'] ?? 0),
            'nama_jalan' => trim((string) ($post['nama_jalan'] ?? $post['namaJalan'] ?? '')),
        ];
    }

    private function validate(array $fields)
    {
        $this->validation->reset();
        $this->validation->setRules([
            'id_cluster' => [
                'label'  => 'Cluster',
                'rules'  => 'required|is_natural_no_zero',
                'errors' => [
                    'required'            => 'Cluster wajib dipilih.',
                    'is_natural_no_zero' => 'Cluster yang dipilih tidak valid.',
                ],
            ],
            'nama_jalan' => [
                'label'  => 'Nama jalan',
                'rules'  => 'required|min_length[2]|max_length[255]',
                'errors' => [
                    'required'   => 'Nama jalan wajib diisi.',
                    'min_length' => 'Nama jalan minimal 2 karakter.',
                    'max_length' => 'Nama jalan maksimal 255 karakter.',
                ],
            ],
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

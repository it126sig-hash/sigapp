<?php

namespace App\Services\Proyek;

use App\Models\GambarkerjaModel;
use App\Models\KavlingModel;
use App\Models\ProyekModel;
use App\Models\TipeModel;
use App\Repositories\Proyek\TipeRepository;
use App\Services\FileAccessService;
use App\Services\HistoryService;
use App\Services\NotifikasiService;
use CodeIgniter\HTTP\IncomingRequest;
use RuntimeException;

class TipeService
{
    private const FILE_FIELDS = [
        'gambar_kerja' => [
            'label' => 'Gambar Kerja',
            'dir' => 'uploads/gambarkerja/',
            'tipe' => 'gambarkerja',
            'mime' => 'application/pdf',
            'message' => 'Gambar Kerja harus berupa file pdf',
        ],
        'gambar_tipe' => [
            'label' => 'Gambar Ilustrasi',
            'dir' => 'uploads/gambartipe/',
            'tipe' => 'gambar ilustrasi',
            'mime' => 'image/jpeg,image/png,image/webp',
            'message' => 'Gambar Ilustrasi harus berupa file gambar jpg/png/webp',
        ],
        'gambar_denah' => [
            'label' => 'Denah Arsitektural',
            'dir' => 'uploads/gambardenah/',
            'tipe' => 'gambar denah',
            'mime' => 'image/jpeg,image/png,image/webp',
            'message' => 'Denah Arsitektural harus berupa file gambar jpg/png/webp',
        ],
    ];

    private TipeModel $tipeModel;
    private ProyekModel $proyekModel;
    private GambarkerjaModel $gambarkerjaModel;
    private TipeRepository $tipeRepository;
    private FileAccessService $fileAccessService;
    private $validation;
    private $db;
    private HistoryService $historyService;
    private NotifikasiService $notif;

    public function __construct()
    {
        $this->tipeModel = new TipeModel();
        $this->proyekModel = new ProyekModel();
        $this->gambarkerjaModel = new GambarkerjaModel();
        $this->tipeRepository = new TipeRepository();
        $this->fileAccessService = new FileAccessService();
        $this->validation = \Config\Services::validation();
        $this->db = db_connect();
        $this->historyService = new HistoryService();
        $this->notif = new NotifikasiService();
    }

    public function getAll(array $params): array
    {
        $data = [];
        $isRaw = ($params['format'] ?? '') === 'raw';

        foreach ($this->tipeRepository->getAll($params) as $key => $value) {
            if ($isRaw) {
                $data[$key] = [
                    'id_tipe'                    => (int) $value->id_tipe,
                    'id_proyek'                  => (int) $value->id_proyek,
                    'nama_proyek'                => $value->nama_proyek,
                    'no_tipe_rumah'              => $value->no_tipe_rumah,
                    'tipe_rumah'                 => $value->tipe_rumah,
                    'lb'                         => (float) $value->lb,
                    'lt'                         => (float) $value->lt,
                    'jumlah_kamar_tidur'         => (int) ($value->jumlah_kamar_tidur ?? 0),
                    'jumlah_kamar_mandi'         => (int) ($value->jumlah_kamar_mandi ?? 0),
                    'spesifikasi_teknis_atap'    => $value->spesifikasi_teknis_atap ?? null,
                    'spesifikasi_teknis_dinding' => $value->spesifikasi_teknis_dinding ?? null,
                    'spesifikasi_teknis_lantai'  => $value->spesifikasi_teknis_lantai ?? null,
                    'spesifikasi_teknis_pondasi' => $value->spesifikasi_teknis_pondasi ?? null,
                    'harga'                      => (float) ($value->harga ?? 0),
                    'is_subsidi'                 => (int) ($value->is_subsidi ?? 0),
                    'is_active'                  => (int) ($value->is_active ?? 1),
                    'jumlah_kavling'             => (int) ($value->jumlah_kavling ?? 0),
                    'id_gambar_tipe'             => !empty($value->id_gambar_tipe) ? (int) $value->id_gambar_tipe : null,
                    'id_gambar_denah'            => !empty($value->id_gambar_denah) ? (int) $value->id_gambar_denah : null,
                    'id_gambar_kerja'            => !empty($value->id_gambar_kerja) ? (int) $value->id_gambar_kerja : null,
                    'gambar_tipe_access_url'     => !empty($value->id_gambar_tipe) ? $this->fileAccessService->accessUrl('gambar_kerja', (int) $value->id_gambar_tipe) : null,
                    'gambar_denah_access_url'    => !empty($value->id_gambar_denah) ? $this->fileAccessService->accessUrl('gambar_kerja', (int) $value->id_gambar_denah) : null,
                    'gambar_kerja_access_url'    => !empty($value->id_gambar_kerja) ? $this->fileAccessService->accessUrl('gambar_kerja', (int) $value->id_gambar_kerja) : null,
                    'keterangan'                 => $value->keterangan,
                ];
            } else {
                $data[$key] = [
                    $value->id_tipe,
                    $value->nama_proyek,
                    $value->no_tipe_rumah,
                    $value->tipe_rumah,
                    $value->lb,
                    $value->lt,
                    $value->keterangan,
                    (int) $value->id_tipe,
                ];
            }
        }

        return $data;
    }

    public function getDataTables(array $params): array
    {
        $result = $this->tipeRepository->getDataTables($params);
        $rows = [];
        $no = ($params['start'] ?? 0) + 1;
        $isRaw = ($params['format'] ?? '') === 'raw';

        foreach ($result['rows'] as $key => $value) {
            $tipeRumahMerge = $value->tipe_rumah . ' (' . $value->lb . '/' . $value->lt . ')';

            if ($isRaw) {
                $rows[$key] = [
                    'id_tipe'                 => (int) $value->id_tipe,
                    'id_proyek'               => (int) $value->id_proyek,
                    'nama_proyek'             => $value->nama_proyek,
                    'no_tipe_rumah'           => $value->no_tipe_rumah,
                    'tipe_rumah'              => $value->tipe_rumah,
                    'lb'                      => (float) $value->lb,
                    'lt'                      => (float) $value->lt,
                    'is_subsidi'              => (int) $value->is_subsidi,
                    'is_active'               => (int) ($value->is_active ?? 1),
                    'jumlah_kamar_tidur'      => (int) $value->jumlah_kamar_tidur,
                    'jumlah_kamar_mandi'      => (int) $value->jumlah_kamar_mandi,
                    'jumlah_kavling'          => (int) $value->jumlah_kavling,
                    'harga'                   => (float) ($value->harga ?? 0),
                    'id_gambar_tipe'          => !empty($value->id_gambar_tipe) ? (int) $value->id_gambar_tipe : null,
                    'id_gambar_denah'         => !empty($value->id_gambar_denah) ? (int) $value->id_gambar_denah : null,
                    'id_gambar_kerja'         => !empty($value->id_gambar_kerja) ? (int) $value->id_gambar_kerja : null,
                    'gambar_tipe_access_url'  => !empty($value->id_gambar_tipe) ? $this->fileAccessService->accessUrl('gambar_kerja', (int) $value->id_gambar_tipe) : null,
                    'gambar_denah_access_url' => !empty($value->id_gambar_denah) ? $this->fileAccessService->accessUrl('gambar_kerja', (int) $value->id_gambar_denah) : null,
                    'gambar_kerja_access_url' => !empty($value->id_gambar_kerja) ? $this->fileAccessService->accessUrl('gambar_kerja', (int) $value->id_gambar_kerja) : null,
                    'keterangan'              => $value->keterangan ?? '',
                ];
            } else {
                $rows[$key] = [
                    $no++,
                    $value->no_tipe_rumah,
                    $tipeRumahMerge,
                    (int) $value->is_subsidi,
                    (int) $value->jumlah_kamar_tidur,
                    (int) $value->jumlah_kamar_mandi,
                    (int) $value->jumlah_kavling,
                    $value->keterangan ?? '',
                    (int) ($value->is_active ?? 1),
                    (int) $value->id_tipe,
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

    public function getListKavling(int $idTipe): array
    {
        if ($idTipe <= 0) {
            return [];
        }

        return $this->tipeRepository->getListKavling($idTipe);
    }

    public function getOne(int $idTipe): ?object
    {
        $data = $this->tipeModel->where('id_tipe', $idTipe)->first();

        if (!$data) {
            return null;
        }

        $data->is_active = (int) ($data->is_active ?? 1);

        $this->appendFileUrls($data, 'gambar_kerja', $data->id_gambar_kerja ?? null);
        $this->appendFileUrls($data, 'gambar_tipe', $data->id_gambar_tipe ?? null);
        $this->appendFileUrls($data, 'gambar_denah', $data->id_gambar_denah ?? null);

        $data->gambarkerja = $this->fileAccessService->addAccessUrlsToRows(
            $this->tipeRepository->getGambarKerjaHistory($idTipe),
            'gambar_kerja',
            'id_gambar_kerja'
        );

        return $data;
    }

    public function add(IncomingRequest $request): array
    {
        $fields = $this->requestFields($request);
        $fields['id_gambar_kerja'] = null;
        $fields['id_gambar_tipe'] = null;
        $fields['id_gambar_denah'] = null;

        $validation = $this->validate($fields, false, $request);
        if ($validation !== true) {
            return ['success' => false, 'messages' => $validation];
        }

        if ($fields['is_active'] === null || $fields['is_active'] === '') {
            $fields['is_active'] = 1;
        } else {
            $fields['is_active'] = (int) $fields['is_active'];
        }

        $this->db->transStart();

        try {
            $this->ensureConfigShape($fields);

            $proyek = $this->proyekModel->where('id_proyek', $fields['id_proyek'])->first();
            foreach (array_keys(self::FILE_FIELDS) as $fieldName) {
                if (!$this->hasUploadedFile($request, $fieldName)) {
                    continue;
                }
                $fileId = $this->storeUploadedFile($request, $fieldName, $fields, $proyek);
                $fields[$this->tipeFieldForUpload($fieldName)] = $fileId;
            }

            if (!$this->tipeModel->insert($fields)) {
                throw new RuntimeException('Insertion error!');
            }

            $idTipe = (int) $this->tipeModel->getInsertID();
            foreach (['id_gambar_kerja', 'id_gambar_tipe', 'id_gambar_denah'] as $fieldName) {
                if (!empty($fields[$fieldName])) {
                    $this->gambarkerjaModel->update($fields[$fieldName], ['id_tipe' => $idTipe]);
                }
            }

            $this->historyService->log('master_tipe', [
                'reference_type' => 'tipe',
                'reference_id' => $idTipe,
                'action' => 'insert',
                'new_data' => $fields
            ]);
            $this->notif->tambah_notif("6", "Menambahkan Master Tipe: " . ($fields['tipe_rumah'] ?? ''), user_id(), null, null, \App\Enums\NotificationEvent::MASTER_TIPE, $fields['id_proyek']);

            $this->db->transComplete();
        } catch (\Throwable $e) {
            $this->db->transRollback();
            return ['success' => false, 'messages' => $e->getMessage()];
        }

        if ($this->db->transStatus() === false) {
            return ['success' => false, 'messages' => 'Insertion error!'];
        }

        return ['success' => true, 'messages' => 'Data has been inserted successfully'];
    }

    public function edit(IncomingRequest $request): array
    {
        $fields = $this->requestFields($request);
        $idTipe = (int) ($fields['id_tipe'] ?? 0);

        if ($idTipe <= 0) {
            return ['success' => false, 'messages' => 'Id tipe tidak valid'];
        }

        $oldData = $this->tipeModel->find($idTipe);
        if (!$oldData) {
            return ['success' => false, 'messages' => 'Data tipe tidak ditemukan'];
        }

        if (empty($fields['id_proyek']) && !empty($oldData->id_proyek)) {
            $fields['id_proyek'] = $oldData->id_proyek;
        }

        $validation = $this->validate($fields, false, $request);
        if ($validation !== true) {
            return ['success' => false, 'messages' => $validation];
        }

        if ($fields['is_active'] === null || $fields['is_active'] === '') {
            $fields['is_active'] = (int) ($oldData->is_active ?? 1);
        } else {
            $fields['is_active'] = (int) $fields['is_active'];
        }

        $this->db->transStart();

        try {
            $this->ensureConfigShape($fields);

            $proyek = $this->proyekModel->where('id_proyek', $fields['id_proyek'])->first();
            foreach (array_keys(self::FILE_FIELDS) as $fieldName) {
                if (!$this->hasUploadedFile($request, $fieldName)) {
                    continue;
                }

                $fileId = $this->storeUploadedFile($request, $fieldName, $fields, $proyek, $idTipe);
                $fields[$this->tipeFieldForUpload($fieldName)] = $fileId;
            }

            if (!$this->tipeModel->update($idTipe, $fields)) {
                throw new RuntimeException('Update error!');
            }

            $this->historyService->log('master_tipe', [
                'reference_type' => 'tipe',
                'reference_id' => $idTipe,
                'action' => 'update',
                'old_data' => $oldData,
                'new_data' => $fields
            ]);
            $this->notif->tambah_notif("6", "Mengubah Master Tipe: " . ($fields['tipe_rumah'] ?? ''), user_id(), null, null, \App\Enums\NotificationEvent::MASTER_TIPE, $fields['id_proyek']);

            $this->db->transComplete();
        } catch (\Throwable $e) {
            $this->db->transRollback();
            return ['success' => false, 'messages' => $e->getMessage()];
        }

        if ($this->db->transStatus() === false) {
            return ['success' => false, 'messages' => 'Update error!'];
        }

        return ['success' => true, 'messages' => 'Successfully updated'];
    }

    public function remove(int $idTipe): array
    {
        if ($idTipe <= 0) {
            return ['success' => false, 'messages' => 'Id tipe tidak valid'];
        }

        $kavlingModel = new KavlingModel();
        $count = $kavlingModel->where('id_tipe', $idTipe)->countAllResults();
        
        if ($count > 0) {
            return ['success' => false, 'messages' => 'Data tipe tidak bisa dihapus karena sedang digunakan oleh ' . $count . ' kavling.'];
        }

        if ($this->tipeModel->where('id_tipe', $idTipe)->delete()) {
            return ['success' => true, 'messages' => 'Deletion succeeded'];
        }

        return ['success' => false, 'messages' => 'Deletion error!'];
    }

    private function requestFields(IncomingRequest $request): array
    {
        $payload = [];
        if (str_contains((string) $request->getHeaderLine('Content-Type'), 'application/json')) {
            $payload = (array) ($request->getJSON(true) ?? []);
        }
        $post = (array) $request->getPost();
        $data = array_merge($payload, $post);

        $idProyek = $data['id_proyek'] ?? $data['idProyek'] ?? null;
        if (function_exists('resolve_active_proyek_id')) {
            $idProyek = resolve_active_proyek_id($idProyek);
        }

        $isActive = $data['is_active'] ?? ($data['isActive'] ?? null);
        if (is_bool($isActive)) {
            $isActive = $isActive ? 1 : 0;
        }

        return [
            'id_tipe'                    => $data['id_tipe'] ?? $data['idTipe'] ?? null,
            'id_proyek'                  => $idProyek,
            'no_tipe_rumah'              => $data['no_tipe_rumah'] ?? $data['noTipeRumah'] ?? null,
            'tipe_rumah'                 => $data['tipe_rumah'] ?? $data['tipeRumah'] ?? null,
            'is_subsidi'                 => isset($data['is_subsidi']) ? $data['is_subsidi'] : ($data['isSubsidi'] ?? 0),
            'is_active'                  => $isActive,
            'lb'                         => $data['lb'] ?? null,
            'lt'                         => $data['lt'] ?? null,
            'jumlah_kamar_tidur'         => $data['jumlah_kamar_tidur'] ?? $data['jumlahKamarTidur'] ?? 0,
            'jumlah_kamar_mandi'         => $data['jumlah_kamar_mandi'] ?? $data['jumlahKamarMandi'] ?? 0,
            'spesifikasi_teknis_atap'    => $data['spesifikasi_teknis_atap'] ?? $data['spesifikasiTeknisAtap'] ?? null,
            'spesifikasi_teknis_dinding' => $data['spesifikasi_teknis_dinding'] ?? $data['spesifikasiTeknisDinding'] ?? null,
            'spesifikasi_teknis_lantai'  => $data['spesifikasi_teknis_lantai'] ?? $data['spesifikasiTeknisLantai'] ?? null,
            'spesifikasi_teknis_pondasi' => $data['spesifikasi_teknis_pondasi'] ?? $data['spesifikasiTeknisPondasi'] ?? null,
            'harga'                      => $data['harga'] ?? 0,
            'keterangan'                 => $data['keterangan'] ?? null,
        ];
    }

    private function validate(array $fields, bool $requireFiles, IncomingRequest $request)
    {
        $rules = [
            'id_proyek' => ['label' => 'Id proyek', 'rules' => 'permit_empty|max_length[255]'],
            'no_tipe_rumah' => ['label' => 'Nomor', 'rules' => 'permit_empty|max_length[255]'],
            'tipe_rumah' => ['label' => 'Tipe rumah', 'rules' => 'permit_empty|max_length[255]'],
            'is_active' => ['label' => 'Status Aktif', 'rules' => 'permit_empty|in_list[0,1]'],
            'lb' => ['label' => 'Lb', 'rules' => 'permit_empty'],
            'lt' => ['label' => 'Lt', 'rules' => 'permit_empty'],
            'jumlah_kamar_tidur' => ['label' => 'Jumlah Kamar Tidur', 'rules' => 'permit_empty|integer'],
            'jumlah_kamar_mandi' => ['label' => 'Jumlah Kamar Mandi', 'rules' => 'permit_empty|integer'],
            'spesifikasi_teknis_atap' => ['label' => 'Spesifikasi Teknis Atap', 'rules' => 'permit_empty'],
            'spesifikasi_teknis_dinding' => ['label' => 'Spesifikasi Teknis Dinding', 'rules' => 'permit_empty'],
            'spesifikasi_teknis_lantai' => ['label' => 'Spesifikasi Teknis Lantai', 'rules' => 'permit_empty'],
            'spesifikasi_teknis_pondasi' => ['label' => 'Spesifikasi Teknis Pondasi', 'rules' => 'permit_empty'],
            'harga' => ['label' => 'Harga', 'rules' => 'permit_empty'],
            'keterangan' => ['label' => 'Keterangan', 'rules' => 'permit_empty'],
        ];

        foreach (self::FILE_FIELDS as $fieldName => $config) {
            if (!$requireFiles && !$this->hasUploadedFile($request, $fieldName)) {
                continue;
            }

            $rules[$fieldName] = [
                'label' => 'File',
                'rules' => 'uploaded[' . $fieldName . ']'
                    . '|mime_in[' . $fieldName . ',' . $config['mime'] . ']'
                    . '|max_size[' . $fieldName . ',12000]',
                'errors' => [
                    'uploaded' => 'Harus mengunggah ' . $config['label'],
                    'mime_in' => $config['message'],
                    'max_size' => $config['label'] . ' tidak lebih dari 12MB',
                ],
            ];
        }

        $this->validation->reset();
        $this->validation->setRules($rules);

        if (!$this->validation->run($fields)) {
            return $this->validation->listErrors();
        }

        return true;
    }

    private function storeUploadedFile(IncomingRequest $request, string $fieldName, array $fields, ?object $proyek, ?int $idTipe = null): int
    {
        $file = $request->getFile($fieldName);
        if (!$file || !$file->isValid()) {
            throw new RuntimeException(self::FILE_FIELDS[$fieldName]['label'] . ' tidak valid');
        }

        $originalName = $file->getClientName();
        $name = $file->getRandomName();
        $logicalDir = self::FILE_FIELDS[$fieldName]['dir'] . date('Ymd') . '/';
        $logicalPath = $this->fileAccessService->storeAs($file, $logicalDir, $name);

        $record = [
            'id_tipe' => $idTipe,
            'lokasi' => $logicalPath,
            'default_filename' => $originalName,
            'keterangan' => 'tipe: ' . $fields['tipe_rumah']
                . ', Proyek: ' . ($proyek->nama_proyek ?? '-')
                . ', Nomor: ' . $fields['no_tipe_rumah'],
            'tipe' => self::FILE_FIELDS[$fieldName]['tipe'],
            'upload_at' => date('Y-m-d H:i:s'),
            'upload_by' => user_id(),
        ];

        $this->gambarkerjaModel->insert($record);

        return (int) $this->gambarkerjaModel->getInsertID();
    }

    private function hasUploadedFile(IncomingRequest $request, string $fieldName): bool
    {
        $file = $request->getFile($fieldName);
        return $file && $file->getError() !== UPLOAD_ERR_NO_FILE;
    }

    private function ensureConfigShape(array $fields): void
    {
        $isSubsidi = (string) ($fields['is_subsidi'] ?? '') === '1';
        $baseColor = $isSubsidi ? '#fbff00' : '#718096';
        $this->tipeRepository->ensureConfigShape((string) $fields['tipe_rumah'], $this->generateSimilarColor($baseColor));
    }

    private function generateSimilarColor(string $baseColor, int $variation = 20): string
    {
        [$r, $g, $b] = sscanf($baseColor, '#%02x%02x%02x');

        $newR = max(0, min(255, $r + rand(-$variation, $variation)));
        $newG = max(0, min(255, $g + rand(-$variation, $variation)));
        $newB = max(0, min(255, $b + rand(-$variation, $variation)));

        return sprintf('#%02x%02x%02x', $newR, $newG, $newB);
    }

    private function tipeFieldForUpload(string $fieldName): string
    {
        return [
            'gambar_kerja' => 'id_gambar_kerja',
            'gambar_tipe' => 'id_gambar_tipe',
            'gambar_denah' => 'id_gambar_denah',
        ][$fieldName];
    }

    private function appendFileUrls(object $row, string $prefix, $id): void
    {
        $accessField = $prefix . '_access_url';
        $downloadField = $prefix . '_download_url';
        $row->{$accessField} = null;
        $row->{$downloadField} = null;

        if (!empty($id)) {
            $row->{$accessField} = $this->fileAccessService->accessUrl('gambar_kerja', (int) $id);
            $row->{$downloadField} = $this->fileAccessService->accessUrl('gambar_kerja', (int) $id, true);
        }
    }

    private function statusBadge($id, string $texts, string $textf): string
    {
        if ($id == '1') {
            return '<span class="badge badge-pill badge-light-warning" style="font-weight: 600; font-size: 11px; padding: 0.4rem 0.8rem; border: 1px solid #f8d75e;">' . $texts . '</span>';
        }
        return '<span class="badge badge-pill badge-light-secondary" style="font-weight: 600; font-size: 11px; padding: 0.4rem 0.8rem; border: 1px solid #dcdcdc; color: #6e6b7b;">' . $textf . '</span>';
    }
}

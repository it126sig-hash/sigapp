<?php

namespace App\Services;

use App\Models\GambarkerjaModel;
use App\Models\ProyekModel;
use App\Models\TipeModel;
use App\Repositories\TipeRepository;
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

    public function __construct()
    {
        $this->tipeModel = new TipeModel();
        $this->proyekModel = new ProyekModel();
        $this->gambarkerjaModel = new GambarkerjaModel();
        $this->tipeRepository = new TipeRepository();
        $this->fileAccessService = new FileAccessService();
        $this->validation = \Config\Services::validation();
        $this->db = db_connect();
    }

    public function getAll(array $params): array
    {
        $data = [];

        foreach ($this->tipeRepository->getAll($params) as $key => $value) {
            $ops = '<div class="btn-group">';
            $ops .= '	<button type="button" class="btn btn-outline-primary waves-effect btn-sm" onclick="edit(' . $value->id_tipe . ')"><i class="fa fa-edit"></i></button>';
            $ops .= '</div>';

            $data[$key] = [
                $value->id_tipe,
                $value->nama_proyek,
                $value->no_tipe_rumah,
                $value->tipe_rumah,
                $value->lb,
                $value->lt,
                $value->keterangan,
                $ops,
            ];
        }

        return $data;
    }

    public function getDataTables(array $params): array
    {
        $result = $this->tipeRepository->getDataTables($params);
        $rows = [];

        foreach ($result['rows'] as $key => $value) {
            $ops = '<div class="btn-group">';
            $ops .= '	<button type="button" class="btn btn-outline-primary waves-effect btn-sm" onclick="edit(' . $value->id_tipe . ')"><i class="fa fa-edit"></i></button>';
            $ops .= '	<button type="button" class="btn btn-outline-danger waves-effect btn-sm" onclick="remove(' . $value->id_tipe . ')"><i class="fa fa-trash"></i></button>';
            $ops .= '</div>';

            $rows[$key] = [
                $value->id_tipe,
                $value->nama_proyek,
                $value->no_tipe_rumah,
                $value->tipe_rumah,
                $this->statusBadge($value->is_subsidi, 'Subsidi', 'Non-Subsidi'),
                $value->lb,
                $value->lt,
                $value->keterangan,
                $ops,
            ];
        }

        return [
            'draw' => $result['draw'],
            'recordsTotal' => $result['recordsTotal'],
            'recordsFiltered' => $result['recordsFiltered'],
            'data' => $rows,
        ];
    }

    public function getOne(int $idTipe): ?object
    {
        $data = $this->tipeModel->where('id_tipe', $idTipe)->first();

        if (!$data) {
            return null;
        }

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

        $validation = $this->validate($fields, true, $request);
        if ($validation !== true) {
            return ['success' => false, 'messages' => $validation];
        }

        $this->db->transStart();

        try {
            $this->ensureConfigShape($fields);

            $proyek = $this->proyekModel->where('id_proyek', $fields['id_proyek'])->first();
            foreach (array_keys(self::FILE_FIELDS) as $fieldName) {
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

        $validation = $this->validate($fields, false, $request);
        if ($validation !== true) {
            return ['success' => false, 'messages' => $validation];
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

        if ($this->tipeModel->where('id_tipe', $idTipe)->delete()) {
            return ['success' => true, 'messages' => 'Deletion succeeded'];
        }

        return ['success' => false, 'messages' => 'Deletion error!'];
    }

    private function requestFields(IncomingRequest $request): array
    {
        return [
            'id_tipe' => $request->getPost('idTipe'),
            'id_proyek' => $request->getPost('idProyek'),
            'no_tipe_rumah' => $request->getPost('no_tipe_rumah'),
            'tipe_rumah' => $request->getPost('tipeRumah'),
            'is_subsidi' => $request->getPost('isSubsidi'),
            'lb' => $request->getPost('lb'),
            'lt' => $request->getPost('lt'),
            'harga' => $request->getPost('harga'),
            'keterangan' => $request->getPost('keterangan'),
        ];
    }

    private function validate(array $fields, bool $requireFiles, IncomingRequest $request)
    {
        $rules = [
            'id_proyek' => ['label' => 'Id proyek', 'rules' => 'permit_empty|max_length[255]'],
            'no_tipe_rumah' => ['label' => 'Nomor', 'rules' => 'permit_empty|max_length[255]'],
            'tipe_rumah' => ['label' => 'Tipe rumah', 'rules' => 'permit_empty|max_length[255]'],
            'lb' => ['label' => 'Lb', 'rules' => 'permit_empty'],
            'lt' => ['label' => 'Lt', 'rules' => 'permit_empty'],
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
        $r = '<span class="btn btn-outline-secondary" text-capitalized="">' . $textf . '</span>';
        if ($id == '1') {
            $r = '<span class="btn  btn-outline-warning" text-capitalized="">' . $texts . '</span>';
        }

        return $r;
    }
}

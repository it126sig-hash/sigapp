<?php

namespace App\Services;

use App\Models\ProyekModel;
use App\Models\SiteplanuploadModel;
use App\Repositories\ProyekRepository;
use CodeIgniter\HTTP\IncomingRequest;
use RuntimeException;
use App\Services\HistoryService;
use App\Controllers\Notif;

class ProyekService
{
    private ProyekModel $proyekModel;
    private SiteplanuploadModel $siteplanUploadModel;
    private ProyekRepository $proyekRepository;
    private FileAccessService $fileAccessService;
    private $validation;
    private $db;
    private HistoryService $historyService;
    private Notif $notif;

    public function __construct()
    {
        $this->proyekModel = new ProyekModel();
        $this->siteplanUploadModel = new SiteplanuploadModel();
        $this->proyekRepository = new ProyekRepository();
        $this->fileAccessService = new FileAccessService();
        $this->validation = \Config\Services::validation();
        $this->db = db_connect();
        $this->historyService = new HistoryService();
        $this->notif = new Notif();
    }

    public function getAll(array $params = []): array
    {
        $rows = [];

        foreach ($this->proyekRepository->getAll($params) as $key => $value) {
            $ops = '<div class="btn-group">';
            $ops .= '	<button type="button" class="btn btn-outline-primary waves-effect btn-sm" onclick="edit(' . $value->id_proyek . ')"><i class="fas fa-edit"></i></button>';
            $ops .= '</div>';

            $rows[$key] = [
                $value->id_proyek,
                $value->nama_proyek,
                $value->alamat_proyek,
                "<img width='50px' src='" . $this->fileAccessService->accessUrl('proyek_logo', (int) $value->id_proyek) . "'>",
                $ops,
            ];
        }

        return $rows;
    }

    public function getOne(int $idProyek): ?object
    {
        $data = $this->proyekModel->where('id_proyek', $idProyek)->first();

        if (!$data) {
            return null;
        }

        $data->siteplan_access_url = $this->fileAccessService->accessUrl('proyek_siteplan', $idProyek);
        $data->logo_access_url = $this->fileAccessService->accessUrl('proyek_logo', $idProyek);
        $data->logo_pt_access_url = $this->fileAccessService->accessUrl('proyek_logo_pt', $idProyek);
        $data->list_siteplan = $this->getSiteplanList($idProyek);

        return $data;
    }

    public function add(IncomingRequest $request): array
    {
        $fields = $this->requestFields($request);
        $fields['siteplan'] = '';
        $fields['logo'] = '';
        $fields['logo_pt'] = '';

        $rules = [
            'file' => $this->siteplanRules('file', 15000),
            'logo' => $this->logoRules('logo'),
        ];

        if ($this->hasUploadedFile($request, 'logo_pt')) {
            $rules['logo_pt'] = $this->logoRules('logo_pt');
        }

        $validation = $this->validate($fields, $rules);

        if ($validation !== true) {
            return ['success' => false, 'messages' => $validation];
        }

        $this->db->transStart();

        try {
            $siteplan = $this->storeSiteplanUpload($request, 'file');
            $fields['siteplan'] = $siteplan['location'];

            $logo = $this->storeImage($request, 'logo', 'uploads/logo/');
            $fields['logo'] = $logo['location'];

            if (isset($rules['logo_pt'])) {
                $logoPt = $this->storeImage($request, 'logo_pt', 'uploads/logo_pt/');
                $fields['logo_pt'] = $logoPt['location'];
            }

            if (!$this->proyekModel->insert($fields)) {
                throw new RuntimeException('Insertion error!');
            }

            $siteplan['id_proyek'] = (int) $this->proyekModel->getInsertID();
            $this->siteplanUploadModel->insert($siteplan);

            $this->historyService->log('master_proyek', [
                'reference_type' => 'proyek',
                'reference_id' => $siteplan['id_proyek'],
                'action' => 'insert',
                'new_data' => $fields
            ]);
            $this->notif->tambah_notif("6", "Menambahkan Master Proyek: " . ($fields['nama_proyek'] ?? ''), user_id(), null, null, null, $siteplan['id_proyek']);

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
        $idProyek = (int) ($fields['id_proyek'] ?? 0);

        if ($idProyek <= 0) {
            return ['success' => false, 'messages' => 'Id proyek tidak valid'];
        }

        $rules = [];
        if ($this->shouldUpload($request, 'file', 'no_up')) {
            $rules['file'] = $this->siteplanRules('file', 10000);
        }

        if ($this->shouldUpload($request, 'logon', 'no_up_logo')) {
            $rules['logon'] = $this->logoRules('logon');
        }

        if ($this->shouldUpload($request, 'logo_pt', 'no_up_logo_pt')) {
            $rules['logo_pt'] = $this->logoRules('logo_pt');
        }

        $validation = $this->validate($fields, $rules);
        if ($validation !== true) {
            return ['success' => false, 'messages' => $validation];
        }

        $oldData = $this->proyekModel->find($idProyek);

        $this->db->transStart();

        try {
            if (isset($rules['file'])) {
                $siteplan = $this->storeSiteplanUpload($request, 'file');
                $siteplan['id_proyek'] = $idProyek;
                $this->siteplanUploadModel->insert($siteplan);
                $fields['siteplan'] = $siteplan['location'];
            }

            if (isset($rules['logon'])) {
                $logo = $this->storeImage($request, 'logon', 'uploads/logo/');
                $fields['logo'] = $logo['location'];
            }

            if (isset($rules['logo_pt'])) {
                $logoPt = $this->storeImage($request, 'logo_pt', 'uploads/logo_pt/');
                $fields['logo_pt'] = $logoPt['location'];
            }

            if (!$this->proyekModel->update($idProyek, $fields)) {
                throw new RuntimeException('Update error!');
            }

            $this->historyService->log('master_proyek', [
                'reference_type' => 'proyek',
                'reference_id' => $idProyek,
                'action' => 'update',
                'old_data' => $oldData,
                'new_data' => $fields
            ]);
            $this->notif->tambah_notif("6", "Mengubah Master Proyek: " . ($fields['nama_proyek'] ?? ''), user_id(), null, null, null, $idProyek);

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

    public function getSiteplanList(int $idProyek): array
    {
        return $this->fileAccessService->addAccessUrlsToRows(
            $this->proyekRepository->getSiteplanUploads($idProyek),
            'siteplan_upload'
        );
    }

    public function remove(int $idProyek): array
    {
        if ($idProyek <= 0) {
            return ['success' => false, 'messages' => 'Id proyek tidak valid'];
        }

        if ($this->proyekModel->where('id_proyek', $idProyek)->delete()) {
            return ['success' => true, 'messages' => 'Deletion succeeded'];
        }

        return ['success' => false, 'messages' => 'Deletion error!'];
    }

    private function requestFields(IncomingRequest $request): array
    {
        return [
            'id_proyek' => $request->getPost('idProyek'),
            'nama_proyek' => $request->getPost('namaProyek'),
            'alamat_proyek' => $request->getPost('alamatProyek'),
            'kelurahan' => $request->getPost('kelurahanProyek'),
            'kecamatan' => $request->getPost('kecamatanProyek'),
            'kota' => $request->getPost('kotaProyek'),
            'provinsi' => $request->getPost('provinsiProyek'),
            'landing_page_url' => trim((string) ($request->getPost('landingPageUrl') ?: 'https://sigapp.site')),
            'nama_pt' => $request->getPost('namaPt'),
            'no_rek' => $request->getPost('noRek'),
            'bank' => $request->getPost('bank'),
            'atas_nama' => $request->getPost('atasNama'),
        ];
    }

    private function validate(array $fields, array $fileRules)
    {
        $rules = [
            'nama_proyek' => ['label' => 'Nama proyek', 'rules' => 'permit_empty|max_length[255]'],
            'alamat_proyek' => ['label' => 'Alamat proyek', 'rules' => 'permit_empty|max_length[255]'],
            'landing_page_url' => ['label' => 'URL landing page', 'rules' => 'permit_empty|valid_url|max_length[255]'],
            'nama_pt' => ['label' => 'Nama PT', 'rules' => 'permit_empty|max_length[255]'],
            'no_rek' => ['label' => 'No rekening', 'rules' => 'permit_empty|max_length[255]'],
            'bank' => ['label' => 'Bank', 'rules' => 'permit_empty|max_length[255]'],
            'atas_nama' => ['label' => 'Atas nama', 'rules' => 'permit_empty|max_length[255]'],
        ];

        $this->validation->reset();
        $this->validation->setRules(array_merge($rules, $fileRules));

        if (!$this->validation->run($fields)) {
            return $this->validation->listErrors();
        }

        return true;
    }

    private function siteplanRules(string $fieldName, int $maxDimension): array
    {
        return [
            'label' => 'Image File',
            'rules' => 'uploaded[' . $fieldName . ']'
                . '|is_image[' . $fieldName . ']'
                . '|mime_in[' . $fieldName . ',image/jpg,image/jpeg,image/gif,image/png,image/webp]'
                . '|max_size[' . $fieldName . ',12000]'
                . '|max_dims[' . $fieldName . ',' . $maxDimension . ',' . $maxDimension . ']',
        ];
    }

    private function logoRules(string $fieldName): array
    {
        return [
            'label' => 'Image File',
            'rules' => 'uploaded[' . $fieldName . ']'
                . '|is_image[' . $fieldName . ']'
                . '|mime_in[' . $fieldName . ',image/jpg,image/jpeg,image/gif,image/png,image/webp]'
                . '|max_size[' . $fieldName . ',12000]'
                . '|max_dims[' . $fieldName . ',6000,6000]',
        ];
    }

    private function storeSiteplanUpload(IncomingRequest $request, string $fieldName): array
    {
        $stored = $this->storeImage($request, $fieldName, 'uploads/siteplan/');
        $info = \Config\Services::image()
            ->withFile($this->fileAccessService->privatePath($stored['location']))
            ->getFile()
            ->getProperties(true);

        return [
            'file_name' => $stored['original_name'],
            'location' => $stored['location'],
            'width' => $info['width'],
            'height' => $info['height'],
            'file_type' => $stored['mime_type'],
            'upload_at' => date('Y-m-d H:i:s'),
            'upload_by' => user_id(),
        ];
    }

    private function storeImage(IncomingRequest $request, string $fieldName, string $logicalDir): array
    {
        $file = $request->getFile($fieldName);
        if (!$file || !$file->isValid()) {
            throw new RuntimeException('File upload tidak valid');
        }

        $originalName = $file->getClientName();
        $mimeType = $file->getMimeType();

        $name = $file->getRandomName();
        $logicalPath = $this->fileAccessService->storeAs($file, $logicalDir, $name);

        return [
            'location' => $logicalPath,
            'original_name' => $originalName,
            'mime_type' => $mimeType,
        ];
    }

    private function shouldUpload(IncomingRequest $request, string $fieldName, string $flagName): bool
    {
        return (string) $request->getPost($flagName) === '0' || $this->hasUploadedFile($request, $fieldName);
    }

    private function hasUploadedFile(IncomingRequest $request, string $fieldName): bool
    {
        $file = $request->getFile($fieldName);
        return $file && $file->getError() !== UPLOAD_ERR_NO_FILE;
    }
}

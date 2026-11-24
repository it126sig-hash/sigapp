<?php

namespace App\Services\Proyek;

use App\Models\ProyekModel;
use App\Models\SiteplanuploadModel;
use App\Repositories\Proyek\ProyekRepository;
use App\Services\FileAccessService;
use App\Services\HistoryService;
use App\Services\NotifikasiService;
use CodeIgniter\HTTP\IncomingRequest;
use RuntimeException;

class ProyekService
{
    private ProyekModel $proyekModel;
    private SiteplanuploadModel $siteplanUploadModel;
    private ProyekRepository $proyekRepository;
    private FileAccessService $fileAccessService;
    private $validation;
    private $db;
    private HistoryService $historyService;
    private NotifikasiService $notif;

    public function __construct()
    {
        $this->proyekModel         = new ProyekModel();
        $this->siteplanUploadModel = new SiteplanuploadModel();
        $this->proyekRepository    = new ProyekRepository();
        $this->fileAccessService   = new FileAccessService();
        $this->validation          = \Config\Services::validation();
        $this->db                  = db_connect();
        $this->historyService      = new HistoryService();
        $this->notif               = new NotifikasiService();
    }

    public function getDropdownList(): array
    {
        return $this->proyekModel->select('id_proyek, nama_proyek')->orderBy('order_by', 'asc')->findAll();
    }

    public function getAll(array $params = []): array
    {
        $rows = [];
        $items = $this->proyekRepository->getAll($params);

        foreach ($items as $key => $value) {
            $id = (int) $value->id_proyek;
            $rows[$key] = [
                'id_proyek'          => $id,
                'no'                 => $key + 1,
                'nama_proyek'        => $value->nama_proyek,
                'alamat_proyek'      => $value->alamat_proyek,
                'kelurahan'          => $value->kelurahan ?? null,
                'kecamatan'          => $value->kecamatan ?? null,
                'kota'               => $value->kota ?? null,
                'provinsi'           => $value->provinsi ?? null,
                'landing_page_url'   => $value->landing_page_url ?? null,
                'logo_thumbnail_url' => $this->fileAccessService->thumbnailUrl('proyek_logo', $id),
                'logo_access_url'    => $this->fileAccessService->accessUrl('proyek_logo', $id),
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

        $data->siteplan_access_url = $this->fileAccessService->versionedAccessUrl(
            'proyek_siteplan',
            $idProyek,
            $data->siteplan ?? null
        );
        $data->logo_access_url = $this->fileAccessService->accessUrl('proyek_logo', $idProyek);
        $data->logo_thumbnail_url = $this->fileAccessService->thumbnailUrl('proyek_logo', $idProyek);
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
            $this->notif->tambah_notif("6", "Menambahkan Master Proyek: " . ($fields['nama_proyek'] ?? ''), user_id(), null, null, \App\Enums\NotificationEvent::MASTER_PROYEK, $siteplan['id_proyek']);

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

        $logoField = $this->hasUploadedFile($request, 'logo') ? 'logo' : 'logon';
        if ($this->shouldUpload($request, $logoField, 'no_up_logo')) {
            $rules[$logoField] = $this->logoRules($logoField);
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

            if (isset($rules[$logoField])) {
                $logo = $this->storeImage($request, $logoField, 'uploads/logo/');
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
            $this->notif->tambah_notif("6", "Mengubah Master Proyek: " . ($fields['nama_proyek'] ?? ''), user_id(), null, null, \App\Enums\NotificationEvent::MASTER_PROYEK, $idProyek);

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
            'order_by' => (int) $request->getPost('orderBy'),
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

    public function getAksesMeta(): array
    {
        $userRows  = $this->proyekRepository->getActiveUsersWithGroups();
        $groupRows = $this->proyekRepository->getAuthGroups();

        $users = array_map(
            fn (object $row): array => $this->formatActiveUser($row),
            $userRows
        );

        $groups = array_map(static fn (object $row): array => [
            'id'          => (int) $row->id,
            'name'        => (string) $row->name,
            'description' => (string) ($row->description ?? ''),
        ], $groupRows);

        return [
            'users'           => $users,
            'groups'          => $groups,
            'admin_group_ids' => [1],
            'permissions'     => [
                'can_manage' => true,
                'can_edit'   => true,
            ],
        ];
    }

    public function getAksesList(array $params = []): array
    {
        if (($params['format'] ?? null) === 'raw') {
            $search  = trim((string) ($params['search'] ?? ''));
            $page    = max(1, (int) ($params['page'] ?? 1));
            $perPage = max(1, min(100, (int) ($params['per_page'] ?? 25)));

            $paginated      = $this->proyekRepository->getAksesProyekPaginated($search, $page, $perPage);
            $activeUsersMap = $this->getActiveUsersMap();
            $items          = [];

            foreach ($paginated['rows'] as $row) {
                $idProyek = (int) $row->id_proyek;
                $hasLogo  = ! empty($row->logo);
                $resolved = $this->resolveActiveUsersFromCsv($row->id_users ?? '', $activeUsersMap);

                $items[] = [
                    'id_proyek'          => $idProyek,
                    'nama_proyek'        => (string) $row->nama_proyek,
                    'logo_thumbnail_url' => $hasLogo ? $this->fileAccessService->thumbnailUrl('proyek_logo', $idProyek) : null,
                    'logo_access_url'    => $hasLogo ? $this->fileAccessService->accessUrl('proyek_logo', $idProyek) : null,
                    'id_users'           => (string) ($row->id_users ?? ''),
                    'user_ids'           => $resolved['user_ids'],
                    'users_count'        => count($resolved['users']),
                    'users_preview'      => array_slice($resolved['users'], 0, 5),
                    'kumpulan_username'  => $resolved['kumpulan_username'],
                ];
            }

            return [
                'items' => $items,
                'meta'  => [
                    'page'     => $page,
                    'per_page' => $perPage,
                    'total'    => (int) $paginated['total'],
                ],
            ];
        }

        $rows = $this->proyekRepository->getAksesProyekList();
        $data = [];

        foreach ($rows as $key => $value) {
            $data[$key] = [
                'id_proyek'         => (int) $value->id_proyek,
                'nama_proyek'       => $value->nama_proyek,
                'id_users'          => $value->id_users,
                'kumpulan_username' => $value->kumpulan_username ?? '',
            ];
        }

        return $data;
    }

    public function getAksesOne(int $idProyek, bool $raw = false): array|object|null
    {
        if ($idProyek <= 0) {
            return null;
        }

        if (! $raw) {
            return $this->proyekRepository->getAksesProyekById($idProyek);
        }

        $proyek = $this->proyekRepository->getById($idProyek);
        if (! $proyek) {
            return null;
        }

        $resolved = $this->resolveActiveUsersFromCsv($proyek->id_users ?? '', $this->getActiveUsersMap());

        return [
            'id_proyek'         => (int) $proyek->id_proyek,
            'nama_proyek'       => (string) $proyek->nama_proyek,
            'id_users'          => (string) ($proyek->id_users ?? ''),
            'user_ids'          => $resolved['user_ids'],
            'users'             => $resolved['users'],
            'kumpulan_username' => $resolved['kumpulan_username'],
        ];
    }

    public function searchAksesUsers(string $keyword, bool $raw = false): array
    {
        if ($raw) {
            return array_map(
                fn (object $row): array => $this->formatActiveUser($row),
                $this->proyekRepository->getActiveUsersWithGroups($keyword)
            );
        }

        return $this->proyekRepository->searchUsers($keyword);
    }

    public function updateAkses(array $postData): array
    {
        $rawIdProyek = $postData['id_proyek'] ?? $postData['idProyek'] ?? 0;
        $idProyek    = is_numeric($rawIdProyek) ? (int) $rawIdProyek : 0;

        if ($idProyek <= 0) {
            return [
                'success'     => false,
                'status_code' => 422,
                'messages'    => 'Validasi gagal.',
                'errors'      => [
                    'id_proyek' => 'Proyek wajib dipilih.',
                ],
            ];
        }

        if (! $this->proyekRepository->existsProyek($idProyek)) {
            return [
                'success'     => false,
                'status_code' => 404,
                'messages'    => 'Proyek tidak ditemukan.',
            ];
        }

        $proyek     = $this->proyekRepository->getById($idProyek);
        $rawUserIds = array_key_exists('id_users', $postData)
            ? $postData['id_users']
            : ($postData['idUsers'] ?? []);

        $parsed = $this->normalizeSubmittedUserIds($rawUserIds);
        if (! $parsed['valid']) {
            return [
                'success'     => false,
                'status_code' => 422,
                'messages'    => 'Validasi gagal.',
                'errors'      => [
                    'id_users' => 'Terdapat ID pengguna yang tidak valid.',
                ],
            ];
        }

        $userIds = $parsed['ids'];

        if ($userIds !== []) {
            $activeIds  = $this->proyekRepository->findActiveUserIds($userIds);
            $missingIds = array_values(array_diff($userIds, $activeIds));

            if ($missingIds !== []) {
                return [
                    'success'     => false,
                    'status_code' => 422,
                    'messages'    => 'Validasi gagal.',
                    'errors'      => [
                        'id_users' => 'Sebagian pengguna tidak ditemukan atau tidak aktif (ID: ' . implode(', ', $missingIds) . ').',
                    ],
                ];
            }
        }

        $csv = $userIds !== [] ? implode(',', $userIds) . ',' : '';

        if (! $this->proyekRepository->updateUserAccess($idProyek, $csv)) {
            return [
                'success'     => false,
                'status_code' => 500,
                'messages'    => 'Terjadi kesalahan saat memperbarui akses proyek.',
            ];
        }

        $oldCsv = (string) ($proyek->id_users ?? '');
        $this->historyService->log('master_proyek', [
            'reference_type' => 'proyek_akses',
            'reference_id'   => $idProyek,
            'id_proyek'      => $idProyek,
            'action'         => 'update_akses_proyek',
            'summary'        => 'Memperbarui akses pengguna proyek ' . (string) ($proyek->nama_proyek ?? ''),
            'old_data'       => [
                'id_users' => $oldCsv,
                'user_ids' => $this->parseUserIdsCsv($oldCsv),
            ],
            'new_data'       => [
                'id_users' => $csv,
                'user_ids' => $userIds,
            ],
        ]);

        $resolved = $this->resolveActiveUsersFromIds($userIds, $this->getActiveUsersMap());

        return [
            'success'     => true,
            'status_code' => 200,
            'messages'    => 'Akses proyek berhasil diperbarui.',
            'data'        => [
                'id_proyek'         => $idProyek,
                'nama_proyek'       => (string) ($proyek->nama_proyek ?? ''),
                'id_users'          => $csv,
                'user_ids'          => $resolved['user_ids'],
                'users_count'       => count($resolved['users']),
                'users'             => $resolved['users'],
                'kumpulan_username' => $resolved['kumpulan_username'],
            ],
        ];
    }

    private function formatActiveUser(object $row): array
    {
        return [
            'id'            => (int) $row->id,
            'username'      => (string) $row->username,
            'nama_karyawan' => (string) ($row->nama_karyawan ?? $row->username),
            'id_group'      => isset($row->id_group) && $row->id_group !== null ? (int) $row->id_group : null,
            'divisi'        => isset($row->divisi) && $row->divisi !== null ? (string) $row->divisi : null,
        ];
    }

    private function getActiveUsersMap(): array
    {
        $map = [];
        foreach ($this->proyekRepository->getActiveUsersWithGroups() as $row) {
            $formatted               = $this->formatActiveUser($row);
            $map[$formatted['id']]   = $formatted;
        }

        return $map;
    }

    private function parseUserIdsCsv(?string $csv): array
    {
        if ($csv === null || trim($csv) === '') {
            return [];
        }

        $ids = [];
        foreach (explode(',', $csv) as $part) {
            $id = (int) trim($part);
            if ($id > 0 && ! in_array($id, $ids, true)) {
                $ids[] = $id;
            }
        }

        return $ids;
    }

    private function resolveActiveUsersFromCsv(?string $csv, array $activeUsersMap): array
    {
        return $this->resolveActiveUsersFromIds($this->parseUserIdsCsv($csv), $activeUsersMap);
    }

    private function resolveActiveUsersFromIds(array $ids, array $activeUsersMap): array
    {
        $activeIds   = [];
        $users       = [];
        $usernames   = [];

        foreach ($ids as $uid) {
            if (! isset($activeUsersMap[$uid])) {
                continue;
            }

            $user        = $activeUsersMap[$uid];
            $activeIds[] = $uid;
            $usernames[] = $user['username'];
            $users[]     = [
                'id'            => $user['id'],
                'username'      => $user['username'],
                'nama_karyawan' => $user['nama_karyawan'],
                'divisi'        => $user['divisi'],
            ];
        }

        return [
            'user_ids'          => $activeIds,
            'users'             => $users,
            'kumpulan_username' => implode(',', $usernames),
        ];
    }

    private function normalizeSubmittedUserIds(mixed $rawUserIds): array
    {
        if ($rawUserIds === null || $rawUserIds === '' || $rawUserIds === []) {
            return ['valid' => true, 'ids' => []];
        }

        if (is_string($rawUserIds)) {
            $items = explode(',', $rawUserIds);
        } elseif (is_array($rawUserIds)) {
            $items = $rawUserIds;
        } else {
            return ['valid' => false, 'ids' => []];
        }

        $ids = [];
        foreach ($items as $item) {
            if (is_string($item) && trim($item) === '') {
                continue;
            }

            if (is_int($item)) {
                $id = $item;
            } elseif (is_string($item) && ctype_digit(trim($item))) {
                $id = (int) trim($item);
            } else {
                return ['valid' => false, 'ids' => []];
            }

            if ($id <= 0) {
                return ['valid' => false, 'ids' => []];
            }

            if (! in_array($id, $ids, true)) {
                $ids[] = $id;
            }
        }

        return ['valid' => true, 'ids' => $ids];
    }
}

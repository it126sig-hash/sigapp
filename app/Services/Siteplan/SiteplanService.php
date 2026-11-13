<?php

namespace App\Services\Siteplan;

use App\Enums\NotificationEvent;
use App\Exceptions\ServiceException;
use App\Repositories\KeuanganRepository;
use App\Repositories\Siteplan\OthersRepository;
use App\Repositories\Siteplan\SiteplanRepository;
use App\Repositories\Transaksi\KavlingRepository;
use App\Services\AuditTrail\HistoryService;
use App\Services\FileAccessService;
use App\Services\MkdtHistoryService;
use App\Services\Notifikasi\NotifikasiService;
use App\Services\Proyek\ActiveProyekService;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\Validation\ValidationInterface;

/**
 * Service utama modul Siteplan (halaman siteplan, meta API, CRUD kavling/others, & turun pembangunan).
 */
class SiteplanService
{
    protected SiteplanRepository $siteplanRepository;
    protected OthersRepository $othersRepository;
    protected KavlingRepository $kavlingRepository;
    protected KeuanganRepository $keuanganRepository;
    protected ActiveProyekService $activeProyekService;
    protected FileAccessService $fileAccessService;
    protected MkdtHistoryService $mkdtHistoryService;
    protected HistoryService $historyService;
    protected NotifikasiService $notifikasiService;
    protected BaseConnection $db;
    protected ValidationInterface $validation;

    public function __construct(
        ?SiteplanRepository $siteplanRepository = null,
        ?OthersRepository $othersRepository = null,
        ?KavlingRepository $kavlingRepository = null,
        ?KeuanganRepository $keuanganRepository = null,
        ?ActiveProyekService $activeProyekService = null,
        ?FileAccessService $fileAccessService = null,
        ?MkdtHistoryService $mkdtHistoryService = null,
        ?HistoryService $historyService = null,
        ?NotifikasiService $notifikasiService = null,
        ?BaseConnection $db = null,
        ?ValidationInterface $validation = null
    ) {
        $this->db                  = $db ?? db_connect();
        $this->validation          = $validation ?? \Config\Services::validation();
        $this->siteplanRepository  = $siteplanRepository ?? new SiteplanRepository($this->db);
        $this->othersRepository    = $othersRepository ?? new OthersRepository($this->db);
        $this->kavlingRepository   = $kavlingRepository ?? new KavlingRepository($this->db);
        $this->keuanganRepository  = $keuanganRepository ?? new KeuanganRepository();
        $this->activeProyekService = $activeProyekService ?? new ActiveProyekService();
        $this->fileAccessService   = $fileAccessService ?? new FileAccessService();
        $this->mkdtHistoryService  = $mkdtHistoryService ?? new MkdtHistoryService();
        $this->historyService      = $historyService ?? new HistoryService();
        $this->notifikasiService   = $notifikasiService ?? new NotifikasiService();
    }

    // ─── PAGE STATE & META ───

    public function getIndexState(int $userId): array
    {
        $activeId = $this->activeProyekService->getActiveId();
        if ($activeId && $this->activeProyekService->userCanAccess($activeId, $userId)) {
            return [
                'redirect_to_view' => true,
                'active_id'        => $activeId,
                'proyek'           => [],
            ];
        }

        return [
            'redirect_to_view' => false,
            'active_id'        => $activeId,
            'proyek'           => $this->activeProyekService->getAccessibleList($userId),
        ];
    }

    /**
     * Menyiapkan seluruh data untuk halaman `siteplan/master`.
     *
     * @throws ServiceException 400 jika belum ada proyek aktif, 404 jika proyek tidak ada, 403 jika tidak ada akses.
     */
    public function getViewSiteplanData(?int $requestedProyekId, int $userId): array
    {
        $proyek   = $this->resolveAndActivateProyek($requestedProyekId);
        $idProyek = (int) $proyek->id_proyek;

        $proyek->siteplan_access_url = $this->fileAccessService->versionedAccessUrl(
            'proyek_siteplan',
            $idProyek,
            $proyek->siteplan ?? null
        );
        $proyek->logo_access_url = $this->fileAccessService->accessUrl('proyek_logo', $idProyek);

        $configShapeMap = $this->siteplanRepository->getConfigShapeMap();

        $viewData = [
            'profile'                  => $this->siteplanRepository->getProfilePerusahaan(),
            'pph'                      => $this->siteplanRepository->getPphList(),
            'ppn'                      => $this->siteplanRepository->getPpnList(),
            'proyek'                   => $proyek,
            'siteplan_cluster_options' => $this->siteplanRepository->getClusterOptionsByProyek($idProyek),
            'li_keu'                   => json_encode($this->keuanganRepository->getLIKeu()),
            'conf'                     => json_encode($configShapeMap),
            'config_shape'             => $configShapeMap,
        ];

        if ($this->userInGroups(['9', '10', '4', '1'])) {
            $viewData['cluster'] = $this->siteplanRepository->getClustersByProyek($idProyek);
            $viewData['jalan']   = $this->siteplanRepository->getJalansByProyek($idProyek);
            $viewData['tipe']    = $this->siteplanRepository->getTipesByProyek($idProyek);
        }

        $hasAkses = [
            'proyek' => false,
            'legal'  => false,
        ];

        if ($this->userInGroups(['1', '7', '8'])) {
            $viewData['list']                     = $this->siteplanRepository->getChecklistSubItemsWithGroup($idProyek);
            $hasAkses['proyek']                   = $this->siteplanRepository->userHasProjectAccess($proyek, $userId);
            $hasAkses['update_tanggal_pembangunan'] = $this->userHasUpdateTanggalPembangunanAccess($userId);
        } elseif ($this->userInGroups(['5'])) {
            $hasAkses['legal'] = $this->siteplanRepository->userHasProjectAccess($proyek, $userId);
        }

        $viewData['has_akses'] = $hasAkses;

        return $viewData;
    }

    /**
     * Menyiapkan seluruh data untuk halaman `siteplan/produksi_mobile`.
     *
     * @throws ServiceException
     */
    public function getProduksiMobileData(?int $requestedProyekId, int $userId, int $initialIdKavling = 0): array
    {
        $proyek = $this->resolveAndActivateProyek($requestedProyekId);

        $hasAkses = [
            'proyek'                     => $this->siteplanRepository->userHasProjectAccess($proyek, $userId),
            'update_tanggal_pembangunan' => false,
        ];

        if ($this->userInGroups(['1', '7', '8'])) {
            $hasAkses['update_tanggal_pembangunan'] = $this->userHasUpdateTanggalPembangunanAccess($userId);
        }

        return [
            'proyek'             => $proyek,
            'has_akses'          => $hasAkses,
            'initial_id_kavling' => $initialIdKavling,
        ];
    }

    /**
     * Payload JSON bersih untuk `GET /api/siteplan/meta`.
     *
     * @throws ServiceException
     */
    public function getMeta(?int $idProyek, int $userId): array
    {
        $resolvedProyekId = ($idProyek !== null && $idProyek > 0)
            ? $idProyek
            : $this->activeProyekService->getActiveId();

        if ($resolvedProyekId === null || $resolvedProyekId <= 0) {
            throw new ServiceException('Parameter id_proyek wajib diisi atau proyek aktif belum dipilih.', 400);
        }

        $proyek = $this->siteplanRepository->findProyekById($resolvedProyekId);
        if (! $proyek) {
            throw new ServiceException('Data proyek tidak ditemukan.', 404);
        }

        if (! $this->activeProyekService->userCanAccess($resolvedProyekId, $userId)) {
            throw new ServiceException('Anda tidak memiliki akses ke proyek ini.', 403);
        }

        $proyek->siteplan_access_url = $this->fileAccessService->versionedAccessUrl(
            'proyek_siteplan',
            $resolvedProyekId,
            $proyek->siteplan ?? null
        );
        $proyek->siteplan_signed_url = $this->fileAccessService->signedUrl(
            'proyek_siteplan',
            $resolvedProyekId,
            3600,
            null,
            false,
            $userId
        );
        $proyek->logo_access_url = $this->fileAccessService->accessUrl('proyek_logo', $resolvedProyekId);
        $proyek->logo_signed_url = $this->fileAccessService->signedUrl(
            'proyek_logo',
            $resolvedProyekId,
            3600,
            null,
            false,
            $userId
        );

        $hasProjectAccess = $this->siteplanRepository->userHasProjectAccess($proyek, $userId);
        $isAdmin          = $this->userInGroups(['1']);

        return [
            'proyek'           => $proyek,
            'clusters'         => $this->siteplanRepository->getClustersByProyek($resolvedProyekId),
            'jalans'           => $this->siteplanRepository->getJalansByProyek($resolvedProyekId),
            'tipes'            => $this->siteplanRepository->getTipesByProyek($resolvedProyekId),
            'config_shape'     => $this->siteplanRepository->getConfigShapeMap(),
            'kategori_options' => $this->kavlingRepository->getKategoriOptions($resolvedProyekId),
            'profile'          => $this->siteplanRepository->getProfilePerusahaan(),
            'pph'              => $this->siteplanRepository->getPphList(),
            'ppn'              => $this->siteplanRepository->getPpnList(),
            'li_keu'           => $this->keuanganRepository->getLIKeu(),
            'checklist'        => $this->siteplanRepository->getChecklistSubItemsWithGroup($resolvedProyekId),
            'permissions'      => [
                'proyek'                     => $isAdmin || $hasProjectAccess,
                'legal'                      => $this->userInGroups(['5']) && $hasProjectAccess,
                'update_tanggal_pembangunan' => $this->userInGroups(['1', '7', '8'])
                    ? $this->userHasUpdateTanggalPembangunanAccess($userId)
                    : false,
                'can_manage_planning'        => $this->userInGroups(['1', '6']),
                'can_edit_legal'             => $this->userInGroups(['1', '5']),
                'can_edit_produksi'          => $this->userInGroups(['1', '7']),
                'can_edit_mkdt'              => $this->userInGroups(['1', '4']),
                'can_edit_keuangan'          => $this->userInGroups(['1', '3']),
                'can_edit_pajak'             => $this->userInGroups(['1', '10']),
                'can_edit_sales'             => $this->userInGroups(['1', '8']),
                'can_edit_direksi'           => $this->userInGroups(['1', '9']),
                'can_manage_target'          => $this->userInGroups(['1', '11']),
            ],
        ];
    }

    public function getConfigShapeList(): array
    {
        return $this->siteplanRepository->getConfigShapeList();
    }

    public function getConfigShapeMap(): array
    {
        return $this->siteplanRepository->getConfigShapeMap();
    }

    public function getConfigByRole(int $roleId): ?object
    {
        return $this->siteplanRepository->getConfigByRole($roleId);
    }

    public function getKategoriOptions(int $idProyek): array
    {
        if ($idProyek <= 0) {
            return [];
        }

        return $this->kavlingRepository->getKategoriOptions($idProyek);
    }

    // ─── MUTASI KAVLING & OTHERS ───

    public function addKavlingOrOthers(array $post, int $userId): array
    {
        $idJenis = (string) ($post['id_jenis'] ?? '');

        if (in_array($idJenis, ['kavling', 'ruko'], true)) {
            $idJalan    = (int) ($post['id_jalan'] ?? 0);
            $noKavling  = (string) ($post['no_kavling'] ?? '');
            $pecah      = explode(';', $noKavling);

            foreach ($pecah as $noItem) {
                if (trim($noItem) === '') {
                    continue;
                }

                $duplicate = $this->siteplanRepository->findDuplicateKavlingByJalan($noItem, $idJalan);
                if ($duplicate) {
                    return [
                        'token'    => csrf_hash(),
                        'success'  => false,
                        'messages' => "No {$duplicate->no_kavling} Sudah digunakan",
                    ];
                }
            }

            return $this->insertKavlingData($post, $userId);
        }

        return $this->insertOthersData($post, $userId);
    }

    public function editKavling(array $post, int $userId): array
    {
        $response = [
            'token' => csrf_hash(),
        ];

        $splitList = static function ($value): array {
            return array_values(array_filter(array_map('trim', explode(';', (string) $value)), static function ($item) {
                return $item !== '';
            }));
        };

        $fields = [
            'id_jalan'     => $post['id_jalan'] ?? null,
            'id_tipe'      => $post['id_tipe'] ?? null,
            'status_tanah' => $post['status_tanah'] ?? null,
            'luas_tanah'   => $post['f_luas'] ?? null,
            'rotation'     => ($post['rotation'] ?? '') !== '' ? $post['rotation'] : null,
        ];

        $id     = $splitList($post['id_kavling'] ?? '');
        $no     = $splitList($post['no_kavling'] ?? '');
        $points = $splitList($post['points'] ?? '');

        $idLen     = count($id);
        $noLen     = count($no);
        $pointsLen = count($points);

        if ($idLen === 0 || $noLen !== $idLen || $pointsLen !== $idLen) {
            $response['success']  = false;
            $response['messages'] = 'Jumlah kavling, nomor, dan lokasi tidak sesuai';
            return $response;
        }

        $seenNo = [];
        foreach ($no as $noKavling) {
            $key = strtolower($noKavling);
            if (isset($seenNo[$key])) {
                $response['success']  = false;
                $response['messages'] = 'No ' . $noKavling . ' duplikat di input';
                return $response;
            }
            $seenNo[$key] = true;
        }

        $oldDataMap = [];
        foreach ($this->siteplanRepository->findKavlingsByIdsRaw($id) as $row) {
            $oldDataMap[$row['id_kavling']] = $row;
        }

        $this->db->transBegin();
        $logEntries = [];

        for ($x = 0; $x < $idLen; $x++) {
            $fields['id_kavling'] = $id[$x];
            $fields['no_kavling'] = $no[$x];
            $fields['points']     = $points[$x];

            $this->validation->reset()->setRules([
                'no_kavling' => ['label' => 'No Rumah', 'rules' => 'permit_empty|max_length[255]'],
            ]);

            if (! $this->validation->run($fields)) {
                $this->db->transRollback();
                $response['success']  = false;
                $response['messages'] = $this->validation->listErrors();
                return $response;
            }

            if (! empty($fields['id_jalan']) && $fields['no_kavling'] !== '') {
                $duplicate = $this->siteplanRepository->findDuplicateKavlingByJalan(
                    (string) $fields['no_kavling'],
                    (int) $fields['id_jalan'],
                    (int) $fields['id_kavling']
                );

                if ($duplicate) {
                    $this->db->transRollback();
                    $response['success']  = false;
                    $response['messages'] = 'No ' . $fields['no_kavling'] . ' Sudah digunakan';
                    return $response;
                }
            }

            if (! $this->siteplanRepository->updateKavling((int) $fields['id_kavling'], $fields)) {
                $this->db->transRollback();
                $response['success']  = false;
                $response['messages'] = 'Update error!';
                return $response;
            }

            $logEntries[] = [
                'id_kavling' => $fields['id_kavling'],
                'old_data'   => $oldDataMap[$fields['id_kavling']] ?? null,
                'new_data'   => $fields,
            ];
        }

        if ($this->db->transStatus() === false) {
            $this->db->transRollback();
            $response['success']  = false;
            $response['messages'] = 'Update error!';
            return $response;
        }

        $this->db->transCommit();

        $this->historyService->log('siteplan', [
            'reference_type' => 'kavling',
            'reference_id'   => $id[0] ?? 0,
            'action'         => 'batch_update',
            'metadata'       => ['batch_ids' => $id],
            'old_data'       => array_column($logEntries, 'old_data'),
            'new_data'       => array_column($logEntries, 'new_data'),
        ]);

        $notif = 'Mengupdate data ' . $idLen . ' kavling pada siteplan pada tanggal: ' . date('d-M-Y');
        $this->notifikasiService->tambah_notif(
            '0',
            $notif,
            $userId,
            $id[0] ?? null,
            null,
            NotificationEvent::KAVLING_UPDATED
        );

        $response['success']  = true;
        $response['messages'] = 'Successfully updated';

        return $response;
    }

    public function editOthers(array $post, int $userId): array
    {
        $response = [
            'token' => csrf_hash(),
        ];

        $fields = [
            'id_jalan'            => $post['id_jalan'] ?? null,
            'tipe'                => $post['id_jenis'] ?? null,
            'planning_luas'       => $post['f_luas'] ?? null,
            'nama'                => $post['f_nama'] ?? null,
            'planning_keterangan' => $post['f_planning_keterangan'] ?? null,
            'planning_edit_by'    => $userId,
            'planning_updated_at' => date('Y-m-d H:i:s'),
        ];

        $points = $post['points'] ?? null;
        if ($points) {
            $fields['points'] = rtrim((string) $points, ';');
        }

        $id = (int) ($post['id_kavling'] ?? 0);

        $this->validation->reset()->setRules([
            'tipe' => [
                'label' => 'Jenis',
                'rules' => 'required|in_list[jalan,fasos,rth,fasum,ruko]',
            ],
            'no_kavling' => [
                'label' => 'No Rumah',
                'rules' => 'permit_empty|max_length[255]',
            ],
        ]);

        if (! $this->validation->run($fields)) {
            $response['success']  = false;
            $response['messages'] = $this->validation->listErrors();
            return $response;
        }

        $oldData = $this->othersRepository->findById($id);

        $this->db->transStart();
        $updated = $this->othersRepository->updateOthers($id, $fields);
        $this->db->transComplete();

        if ($updated && $this->db->transStatus() !== false) {
            $response['success']  = true;
            $response['messages'] = 'Data berhasil diperbaharui';

            $this->historyService->log('siteplan', [
                'reference_type' => 'others',
                'reference_id'   => $id,
                'action'         => 'update',
                'old_data'       => $oldData,
                'new_data'       => $fields,
            ]);

            $notif    = 'Mengupdate data fasum/lainnya ke siteplan: ' . ($fields['nama'] ?? '') . ' pada tanggal: ' . date('d-M-Y');
            $idProyek = $this->othersRepository->findProyekIdByJalanId((int) ($fields['id_jalan'] ?? 0));
            $this->notifikasiService->tambah_notif(
                '0',
                $notif,
                $userId,
                null,
                null,
                NotificationEvent::OTHERS_UPDATED,
                $idProyek > 0 ? $idProyek : null
            );
        } else {
            $response['success']  = false;
            $response['messages'] = 'Data gagal diperbaharui!';
        }

        return $response;
    }

    // ─── LOOKUP KAVLING, HARGA, & TURUN PEMBANGUNAN ───

    public function getKavlingByMultipleId(array $ids): array
    {
        return [
            'token' => csrf_hash(),
            'data'  => $this->siteplanRepository->findKavlingsWithRelationsByIds($ids),
        ];
    }

    public function getHargaKavling(array $ids): array
    {
        $rows = $this->siteplanRepository->findHargaKavlingsByIds($ids);

        foreach ($rows as $row) {
            if ($row && ! empty($row->id_filehj)) {
                $fileId            = (int) $row->id_filehj;
                $row->access_url   = $this->fileAccessService->accessUrl('file_hargajual', $fileId);
                $row->signed_url   = $this->fileAccessService->signedUrl('file_hargajual', $fileId);
                $row->download_url = $this->fileAccessService->signedUrl('file_hargajual', $fileId, 3600, null, true);
            }
        }

        return [
            'token' => csrf_hash(),
            'data'  => $rows,
        ];
    }

    public function getTurunPembangunan(array $ids): array
    {
        $rows = $this->siteplanRepository->findTurunPembangunanByIds($ids);

        foreach ($rows as $row) {
            if (! empty($row->perintah_bangun_file)) {
                $kavlingId                          = (int) $row->id_kavling;
                $row->perintah_bangun_access_url    = $this->fileAccessService->accessUrl('kavling_perintah_bangun', $kavlingId);
                $row->perintah_bangun_signed_url    = $this->fileAccessService->signedUrl('kavling_perintah_bangun', $kavlingId);
                $row->perintah_bangun_download_url  = $this->fileAccessService->signedUrl('kavling_perintah_bangun', $kavlingId, 3600, null, true);
            }
        }

        return [
            'token'   => csrf_hash(),
            'data'    => $rows,
            'success' => count($rows) > 0,
        ];
    }

    public function setTurunPembangunan(array $post, mixed $file, int $userId): array
    {
        $response = [
            'token' => csrf_hash(),
        ];

        $rawIds     = $post['id_kavling'] ?? '';
        $idList     = is_array($rawIds) ? $rawIds : explode(';', (string) $rawIds);
        $idKavlings = array_values(array_filter(array_map('intval', $idList), static fn (int $id): bool => $id > 0));

        if ($idKavlings === []) {
            $response['success']  = false;
            $response['messages'] = 'Gagal melakukan perubahan data';
            return $response;
        }

        $fields = [];
        if ($file instanceof UploadedFile && $file->getSize() > 0 && ! $file->hasMoved()) {
            $name = $file->getRandomName();
            $lok  = 'uploads/perintah_bangun_file/' . date('Ymd') . '/';
            $this->fileAccessService->storeAs($file, $lok, $name);
            $fields['perintah_bangun_file'] = $lok . $name;
        }

        $fields['perintah_bangun']      = 1;
        $fields['perintah_bangun_oleh'] = $userId;
        $fields['perintah_bangun_tgl']  = $post['perintah_bangun_tgl'] ?? null;

        $tpKavling    = (string) ($post['tp-kavling'] ?? '');
        $tglFormatted = ! empty($fields['perintah_bangun_tgl'])
            ? date_format(date_create((string) $fields['perintah_bangun_tgl']), 'd-M-Y')
            : '-';

        $this->db->transBegin();
        $updatedLogs = [];

        foreach ($idKavlings as $id) {
            $oldKavling = $this->siteplanRepository->findKavlingPerintahBangunSnapshot($id);

            if (! $this->siteplanRepository->updateKavling($id, $fields)) {
                $this->db->transRollback();
                $response['success']  = false;
                $response['messages'] = 'Gagal melakukan perubahan data';
                return $response;
            }

            $updatedLogs[] = [
                'id_kavling'  => $id,
                'old_kavling' => $oldKavling,
            ];
        }

        if ($this->db->transStatus() === false) {
            $this->db->transRollback();
            $response['success']  = false;
            $response['messages'] = 'Gagal melakukan perubahan data';
            return $response;
        }

        $this->db->transCommit();

        foreach ($updatedLogs as $entry) {
            $id         = $entry['id_kavling'];
            $oldKavling = $entry['old_kavling'];

            $this->mkdtHistoryService->log(
                $id,
                $oldKavling ? (int) ($oldKavling->id_mkdt ?? 0) ?: null : null,
                MkdtHistoryService::ACTION_TURUN_PEMBANGUNAN,
                $this->mkdtHistoryService->buildTurunPembangunanSummary(
                    $oldKavling ? (array) $oldKavling : null,
                    $fields
                ),
                $oldKavling ? (array) $oldKavling : null,
                $fields,
                $userId
            );

            $notif = 'Turun pembanguanan untuk kavling: ' . $tpKavling . ' pada tanggal: ' . $tglFormatted . '';
            $this->notifikasiService->tambah_notif(
                '4;9',
                $notif,
                $userId,
                $id,
                null,
                NotificationEvent::TURUN_PEMBANGUNAN
            );
        }

        $response['success']  = true;
        $response['messages'] = 'Berhasil melakukan perubahan data';

        return $response;
    }

    public function getKavlingById(int $id): ?array
    {
        $row = $this->siteplanRepository->findKavlingWithRelationsById($id);
        if (! $row) {
            return null;
        }

        return [
            'token' => csrf_hash(),
            'data'  => $row,
        ];
    }

    // ─── INTERNAL HELPERS ───

    private function insertKavlingData(array $post, int $userId): array
    {
        $response = [
            'token' => csrf_hash(),
        ];

        $fields = [
            'id_jalan'     => $post['id_jalan'] ?? null,
            'id_tipe'      => $post['id_tipe'] ?? null,
            'no_kavling'   => (string) ($post['no_kavling'] ?? ''),
            'points'       => $post['points'] ?? null,
            'luas_tanah'   => $post['f_luas'] ?? null,
            'status_tanah' => $post['status_tanah'] ?? null,
            'rotation'     => ($post['rotation'] ?? '') !== '' ? $post['rotation'] : null,
        ];

        $bpoints = $post['bpoints'] ?? $post['bpoints[]'] ?? [];
        if (! is_array($bpoints)) {
            $bpoints = ($bpoints !== null && $bpoints !== '') ? [$bpoints] : [];
        }
        $countBpoints = count($bpoints);
        $pecah        = explode(';', $fields['no_kavling']);

        $this->validation->reset()->setRules([
            'no_kavling' => ['label' => 'No Rumah', 'rules' => 'permit_empty|max_length[255]'],
        ]);

        if (! $this->validation->run($fields)) {
            $response['success']  = false;
            $response['messages'] = $this->validation->listErrors();
            return $response;
        }

        $this->db->transBegin();
        $logPayloads = [];

        if ($countBpoints > 1) {
            for ($x = 0; $x < $countBpoints; $x++) {
                $fields['no_kavling'] = $pecah[$x] ?? '';
                $fields['points']     = $bpoints[$x];

                $insertId = $this->siteplanRepository->insertKavling($fields);
                if ($insertId <= 0) {
                    $this->db->transRollback();
                    $response['success']  = false;
                    $response['messages'] = 'Insertion error!';
                    return $response;
                }

                $response['id'][$x]         = $insertId;
                $response['no_kavling'][$x] = $fields['no_kavling'];
                $response['points'][$x]     = $fields['points'];
                $logPayloads[]              = [
                    'id'     => $insertId,
                    'fields' => $fields,
                ];
            }
        } else {
            $insertId = $this->siteplanRepository->insertKavling($fields);
            if ($insertId <= 0) {
                $this->db->transRollback();
                $response['success']  = false;
                $response['messages'] = 'Insertion error!';
                return $response;
            }

            $response['id'] = $insertId;
            $logPayloads[]  = [
                'id'     => $insertId,
                'fields' => $fields,
            ];
        }

        if ($this->db->transStatus() === false) {
            $this->db->transRollback();
            $response['success']  = false;
            $response['messages'] = 'Insertion error!';
            return $response;
        }

        $this->db->transCommit();

        foreach ($logPayloads as $entry) {
            $this->historyService->log('siteplan', [
                'reference_type' => 'kavling',
                'reference_id'   => $entry['id'],
                'action'         => 'insert',
                'new_data'       => $entry['fields'],
            ]);
        }

        $namaJalan = (string) ($post['nama_jalan'] ?? '');
        $tpKavling = (string) ($post['tp-kavling'] ?? '');
        $notif     = 'Menambahkan data kavling ke siteplan: ' . "\n"
            . '                Jalan ' . $namaJalan . ' ' . "\n"
            . '                No. ' . "\n"
            . '                dengan tipe rumah ' . $tpKavling . ' ' . "\n"
            . '                pada tanggal: ' . date('d-M-Y');

        $this->notifikasiService->tambah_notif(
            '0',
            $notif,
            $userId,
            $response['id'] ?? null,
            null,
            NotificationEvent::KAVLING_ADDED
        );

        $response['success']  = true;
        $response['messages'] = 'Data berhasil diinput';

        return $response;
    }

    private function insertOthersData(array $post, int $userId): array
    {
        $response = [
            'token' => csrf_hash(),
        ];

        $fields = [
            'id_jalan'            => $post['id_jalan'] ?? null,
            'tipe'                => $post['id_jenis'] ?? null,
            'planning_luas'       => $post['f_luas'] ?? null,
            'nama'                => $post['f_nama'] ?? null,
            'planning_keterangan' => $post['f_planning_keterangan'] ?? null,
            'planning_add_by'     => $userId,
            'planning_created_at' => date('Y-m-d H:i:s'),
            'planning_edit_by'    => $userId,
            'planning_updated_at' => date('Y-m-d H:i:s'),
        ];

        $points = $post['points'] ?? null;
        if ($points) {
            $fields['points'] = rtrim((string) $points, ';');
        }

        $this->validation->reset()->setRules([
            'tipe' => [
                'label' => 'Jenis',
                'rules' => 'required|in_list[jalan,fasos,rth,fasum,ruko]',
            ],
            'id_cluster' => [
                'label' => 'Cluster',
                'rules' => 'permit_empty|max_length[255]',
            ],
        ]);

        if (! $this->validation->run($fields)) {
            $response['success']  = false;
            $response['messages'] = $this->validation->listErrors();
            return $response;
        }

        $this->db->transStart();
        $insertId = $this->othersRepository->insertOthers($fields);
        $this->db->transComplete();

        if ($insertId > 0 && $this->db->transStatus() !== false) {
            $response['success']  = true;
            $response['messages'] = 'Data berhasil diinput';
            $response['id']       = $insertId;

            $this->historyService->log('siteplan', [
                'reference_type' => 'others',
                'reference_id'   => $insertId,
                'action'         => 'insert',
                'new_data'       => $fields,
            ]);

            $notif    = 'Menambahkan data fasum/lainnya ke siteplan: ' . ($fields['nama'] ?? '') . ' pada tanggal: ' . date('d-M-Y');
            $idProyek = $this->othersRepository->findProyekIdByJalanId((int) ($fields['id_jalan'] ?? 0));
            $this->notifikasiService->tambah_notif(
                '0',
                $notif,
                $userId,
                null,
                null,
                NotificationEvent::OTHERS_ADDED,
                $idProyek > 0 ? $idProyek : null
            );
        } else {
            $response['success']  = false;
            $response['messages'] = 'Insertion error!';
        }

        return $response;
    }

    /**
     * @throws ServiceException
     */
    private function resolveAndActivateProyek(?int $requestedProyekId): object
    {
        $idProyek = ($requestedProyekId !== null && $requestedProyekId > 0)
            ? $requestedProyekId
            : $this->activeProyekService->getActiveId();

        if ($idProyek === null || $idProyek <= 0) {
            throw new ServiceException(
                'Silahkan pilih proyek terlebih dahulu',
                400,
                ['redirect' => 'dashboard']
            );
        }

        $proyek = $this->siteplanRepository->findProyekById($idProyek);
        if (! $proyek) {
            throw new ServiceException(
                'Proyek tidak ditemukan.',
                404,
                ['redirect' => 'siteplan']
            );
        }

        $setResult = $this->activeProyekService->setActive($idProyek);
        if (! ($setResult['success'] ?? false)) {
            throw new ServiceException(
                $setResult['message'] ?? 'Anda tidak memiliki akses ke proyek ini.',
                403,
                ['redirect' => 'siteplan']
            );
        }

        return $proyek;
    }

    private function userHasUpdateTanggalPembangunanAccess(int $userId): bool
    {
        $hakList = $this->siteplanRepository->getHakAksesByUser($userId);

        foreach ($hakList as $item) {
            if (($item->nama_akses ?? '') === 'update_tanggal_pembangunan') {
                return true;
            }
        }

        return false;
    }

    private function userInGroups(array $groups): bool
    {
        if (! function_exists('in_groups')) {
            return false;
        }

        return (bool) in_groups($groups);
    }
}

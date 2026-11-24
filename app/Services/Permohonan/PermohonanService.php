<?php

namespace App\Services\Permohonan;

use App\Repositories\Permohonan\PermohonanRepository;
use App\Services\Bpb\BpbWorkflow;
use App\Services\SalesExternal\FeeSalesSettingService;

class PermohonanService
{
    private const ALLOWED_SCOPES = ['assigned_to_me', 'my_requests', 'department_monitoring'];

    private const MODULE_LABELS = [
        'bpb'             => 'BPB',
        'fee_sales'       => 'Fee Sales',
        'kavling_request' => 'Request Kavling',
        'tagihan_direksi' => 'Tagihan Direksi',
        'cashout_subkon'  => 'Cashout Subkon',
        'mgm'             => 'Member Get Member',
        'tiket_masalah'   => 'Tiket Masalah',
    ];

    protected PermohonanRepository $repo;
    protected FeeSalesSettingService $feeSalesSettingService;

    public function __construct(
        ?PermohonanRepository $repo = null,
        ?FeeSalesSettingService $feeSalesSettingService = null
    ) {
        $this->repo                   = $repo ?? new PermohonanRepository();
        $this->feeSalesSettingService = $feeSalesSettingService ?? new FeeSalesSettingService();
    }

    /**
     * Resolusi konteks role, permission lintas modul, dan proyek yang dapat diakses user.
     */
    public function resolveUserContext(int $userId): array
    {
        $groupIds = $this->repo->getUserGroupIds($userId);
        $isAdmin  = in_array(1, $groupIds, true);
        $feePerms = $this->feeSalesSettingService->getUserPermissions($userId);
        $projects = $this->repo->getAccessibleProjects($userId, $isAdmin);

        $accessibleProjectIds = array_values(array_map(
            static fn (array $p): int => (int) $p['id_proyek'],
            $projects
        ));

        return [
            'user_id'                 => $userId,
            'group_ids'               => $groupIds,
            'is_admin'                => $isAdmin,
            'is_umum'                 => in_array(2, $groupIds, true),
            'is_keuangan'             => in_array(3, $groupIds, true),
            'is_mkdt'                 => in_array(4, $groupIds, true),
            'is_legal'                => in_array(5, $groupIds, true),
            'is_planning'             => in_array(6, $groupIds, true),
            'is_produksi'             => in_array(7, $groupIds, true),
            'is_promosi'              => in_array(8, $groupIds, true),
            'is_direksi'              => in_array(9, $groupIds, true),
            'is_pajak'                => in_array(10, $groupIds, true),
            'can_view_fee_sales'      => ! empty($feePerms['can_view']),
            'can_sign_mkdt'           => ! empty($feePerms['can_sign_mkdt']),
            'can_sign_keuangan'       => ! empty($feePerms['can_sign_keuangan']),
            'can_cairkan_fee_sales'   => ! empty($feePerms['can_cairkan']),
            'accessible_projects'     => $projects,
            'accessible_project_ids'  => $accessibleProjectIds,
        ];
    }

    /**
     * Metadata initial page props, opsi filter, proyek, dan permission flags (Vue-ready).
     */
    public function getMeta(int $userId): array
    {
        $context = $this->resolveUserContext($userId);

        $modules = [
            ['key' => 'all', 'label' => 'Semua Modul'],
        ];
        foreach (self::MODULE_LABELS as $key => $label) {
            $modules[] = ['key' => $key, 'label' => $label];
        }

        return [
            'modules' => $modules,
            'scopes'  => [
                ['key' => 'assigned_to_me', 'label' => 'Menunggu Respon Saya'],
                ['key' => 'my_requests', 'label' => 'Pengajuan Saya'],
                ['key' => 'department_monitoring', 'label' => 'Monitoring Departemen'],
            ],
            'statuses' => [
                ['key' => 'all', 'label' => 'Semua Status'],
                ['key' => 'active', 'label' => 'Aktif / Perlu Proses'],
                ['key' => 'pending', 'label' => 'Menunggu'],
                ['key' => 'in_progress', 'label' => 'Sedang Diproses'],
                ['key' => 'completed', 'label' => 'Selesai / Disetujui'],
                ['key' => 'rejected', 'label' => 'Ditolak / Dibatalkan'],
            ],
            'projects'    => $context['accessible_projects'],
            'permissions' => [
                'user_id'               => $context['user_id'],
                'group_ids'             => $context['group_ids'],
                'is_admin'              => $context['is_admin'],
                'is_umum'               => $context['is_umum'],
                'is_keuangan'           => $context['is_keuangan'],
                'is_mkdt'               => $context['is_mkdt'],
                'is_legal'              => $context['is_legal'],
                'is_planning'           => $context['is_planning'],
                'is_produksi'           => $context['is_produksi'],
                'is_promosi'            => $context['is_promosi'],
                'is_direksi'            => $context['is_direksi'],
                'can_view_fee_sales'    => $context['can_view_fee_sales'],
                'can_sign_mkdt'         => $context['can_sign_mkdt'],
                'can_sign_keuangan'     => $context['can_sign_keuangan'],
                'can_cairkan_fee_sales' => $context['can_cairkan_fee_sales'],
            ],
        ];
    }

    /**
     * Ringkasan badge counter untuk 3 scope tab dan pill modul.
     */
    public function getSummary(int $userId, ?int $idProyek = null, string $scope = 'assigned_to_me'): array
    {
        $context       = $this->resolveUserContext($userId);
        $safeScope     = $this->normalizeScope($scope);
        $safeIdProyek  = $this->sanitizeAccessibleProjectId($idProyek, $context);
        $countsByScope = $this->repo->getSummaryCounts($context, $safeIdProyek);

        return [
            'assigned_to_me'        => $countsByScope['assigned_to_me']['all'] ?? 0,
            'my_requests'           => $countsByScope['my_requests']['all'] ?? 0,
            'department_monitoring' => $countsByScope['department_monitoring']['all'] ?? 0,
            'by_module'             => $countsByScope[$safeScope] ?? [],
            'by_scope_and_module'   => $countsByScope,
        ];
    }

    /**
     * Daftar permohonan terpadu lintas 7 modul dengan format DTO seragam.
     */
    public function getList(int $userId, array $filters = []): array
    {
        $context  = $this->resolveUserContext($userId);
        $scope    = $this->normalizeScope((string) ($filters['scope'] ?? 'assigned_to_me'));
        $module   = $this->normalizeModule((string) ($filters['module'] ?? 'all'));
        $status   = trim((string) ($filters['status'] ?? 'all'));
        $search   = trim((string) ($filters['search'] ?? ''));
        $page     = max(1, (int) ($filters['page'] ?? 1));
        $perPage  = min(100, max(1, (int) ($filters['per_page'] ?? 20)));
        $rawProyek = isset($filters['id_proyek']) && $filters['id_proyek'] !== '' ? (int) $filters['id_proyek'] : null;
        $idProyek  = $this->sanitizeAccessibleProjectId($rawProyek, $context);

        $normalizedFilters = [
            'scope'     => $scope,
            'module'    => $module,
            'status'    => $status !== '' ? $status : 'all',
            'id_proyek' => $idProyek,
            'search'    => $search,
            'page'      => $page,
            'per_page'  => $perPage,
        ];

        // Jika user memilih proyek yang tidak dapat diaksesnya, kembalikan kosong aman
        if ($rawProyek !== null && $rawProyek > 0 && $idProyek === null) {
            $summary = $this->formatSummaryPayload($this->repo->getSummaryCounts($context, null), $scope);
            return [
                'items' => [],
                'pagination' => [
                    'page'        => $page,
                    'per_page'    => $perPage,
                    'total'       => 0,
                    'total_pages' => 0,
                ],
                'summary' => $summary,
            ];
        }

        $indexResult = $this->repo->getPaginatedIndex($context, $normalizedFilters);
        $indexRows   = $indexResult['rows'];
        $total       = (int) $indexResult['total'];
        $totalPages  = $total > 0 ? (int) ceil($total / $perPage) : 0;

        $hydratedMap   = $this->repo->hydrateRows($indexRows, $userId);
        $userSnapshots = $this->resolveSnapshotsForHydrated($hydratedMap);

        $items = [];
        foreach ($indexRows as $idx) {
            $key = $idx['module'] . ':' . $idx['item_id'];
            if (! isset($hydratedMap[$key])) {
                continue;
            }
            $items[] = $this->mapToDto((string) $idx['module'], $hydratedMap[$key], $context, $userSnapshots);
        }

        $countsByScope = $this->repo->getSummaryCounts($context, $idProyek);
        $summary       = $this->formatSummaryPayload($countsByScope, $scope);

        return [
            'items'      => $items,
            'pagination' => [
                'page'        => $page,
                'per_page'    => $perPage,
                'total'       => $total,
                'total_pages' => $totalPages,
            ],
            'summary'    => $summary,
        ];
    }

    private function formatSummaryPayload(array $countsByScope, string $scope): array
    {
        return [
            'assigned_to_me'        => $countsByScope['assigned_to_me']['all'] ?? 0,
            'my_requests'           => $countsByScope['my_requests']['all'] ?? 0,
            'department_monitoring' => $countsByScope['department_monitoring']['all'] ?? 0,
            'by_module'             => $countsByScope[$scope] ?? [],
            'by_scope_and_module'   => $countsByScope,
        ];
    }

    private function normalizeScope(string $scope): string
    {
        $scope = trim($scope);
        return in_array($scope, self::ALLOWED_SCOPES, true) ? $scope : 'assigned_to_me';
    }

    private function normalizeModule(string $module): string
    {
        $module = trim($module);
        return ($module === 'all' || in_array($module, PermohonanRepository::MODULES, true))
            ? $module
            : 'all';
    }

    private function sanitizeAccessibleProjectId(?int $idProyek, array $context): ?int
    {
        if ($idProyek === null || $idProyek <= 0) {
            return null;
        }

        if (! empty($context['is_admin'])) {
            return $idProyek;
        }

        return in_array($idProyek, $context['accessible_project_ids'] ?? [], true)
            ? $idProyek
            : null;
    }

    /**
     * @param array<string, array<string, mixed>> $hydratedMap
     * @return array<int, array{nama: string, divisi: string}>
     */
    private function resolveSnapshotsForHydrated(array $hydratedMap): array
    {
        $userIds = [];
        foreach ($hydratedMap as $key => $row) {
            if (str_starts_with($key, 'fee_sales:')) {
                $userIds[] = (int) ($row['created_by'] ?? 0);
            } elseif (str_starts_with($key, 'kavling_request:')) {
                $userIds[] = (int) ($row['created_by'] ?? 0);
            } elseif (str_starts_with($key, 'tagihan_direksi:')) {
                $userIds[] = (int) ($row['add_by'] ?? 0);
            } elseif (str_starts_with($key, 'cashout_subkon:')) {
                $userIds[] = (int) ($row['spp_add_by'] ?? 0);
                $userIds[] = (int) ($row['add_by'] ?? 0);
            } elseif (str_starts_with($key, 'mgm:')) {
                $userIds[] = (int) ($row['submitted_keuangan_by'] ?? 0);
                $userIds[] = (int) ($row['confirmed_by'] ?? 0);
                $userIds[] = (int) ($row['add_by'] ?? 0);
            } elseif (str_starts_with($key, 'tiket_masalah:')) {
                $userIds[] = (int) ($row['pic_user_id'] ?? 0);
            }
        }

        return $this->repo->getUserSnapshots($userIds);
    }

    private function mapToDto(string $module, array $row, array $context, array $userSnapshots): array
    {
        return match ($module) {
            'bpb'             => $this->mapBpbDto($row, $context),
            'fee_sales'       => $this->mapFeeSalesDto($row, $context, $userSnapshots),
            'kavling_request' => $this->mapKavlingRequestDto($row, $context, $userSnapshots),
            'tagihan_direksi' => $this->mapTagihanDireksiDto($row, $context, $userSnapshots),
            'cashout_subkon'  => $this->mapCashoutSubkonDto($row, $context, $userSnapshots),
            'mgm'             => $this->mapMgmDto($row, $context, $userSnapshots),
            'tiket_masalah'   => $this->mapTiketMasalahDto($row, $context, $userSnapshots),
            default           => [],
        };
    }

    private function mapBpbDto(array $row, array $context): array
    {
        $id          = (int) $row['id'];
        $userId      = (int) $context['user_id'];
        $status      = (string) ($row['status'] ?? 'draft');
        $firstItem   = trim((string) ($row['first_item'] ?? ''));
        $itemCount   = (int) ($row['item_count'] ?? 0);
        $extraCount  = max(0, $itemCount - 1);
        $judul       = $firstItem !== '' ? ($firstItem . ($extraCount > 0 ? " (+{$extraCount} item)" : '')) : ('Permintaan Barang ' . ($row['nomor'] ?: ('#' . $id)));
        $statusLabel = BpbWorkflow::label($status);

        $statusVariant = match ($status) {
            BpbWorkflow::WAITING_CC, BpbWorkflow::WAITING_APPROVER, BpbWorkflow::PENDING => 'warning',
            BpbWorkflow::APPROVED, BpbWorkflow::PROCESSED, BpbWorkflow::DISBURSED        => 'primary',
            BpbWorkflow::PURCHASED                                                       => 'success',
            BpbWorkflow::REJECTED                                                        => 'danger',
            default                                                                      => 'secondary',
        };

        $canRespond  = false;
        $actionLabel = 'Lihat Detail';

        if ($status === BpbWorkflow::WAITING_CC && (int) ($row['cc_user_id'] ?? 0) === $userId) {
            $canRespond  = true;
            $actionLabel = 'Paraf / Tolak';
        } elseif ($status === BpbWorkflow::WAITING_APPROVER && (int) ($row['approver_user_id'] ?? 0) === $userId) {
            $canRespond  = true;
            $actionLabel = 'ACC / Tolak';
        } elseif (! empty($context['is_umum']) && in_array($status, [BpbWorkflow::APPROVED, BpbWorkflow::PROCESSED], true)) {
            $canRespond  = true;
            $actionLabel = 'Proses Pencairan';
        } elseif (! empty($context['is_umum']) && in_array($status, [BpbWorkflow::DISBURSED, BpbWorkflow::PENDING], true)) {
            $canRespond  = true;
            $actionLabel = 'Verifikasi Beli';
        }

        $nominal = $row['actual_amount'] !== null && $row['actual_amount'] !== ''
            ? (float) $row['actual_amount']
            : null;

        return [
            'uid'                  => 'bpb:' . $id,
            'id'                   => $id,
            'module'               => 'bpb',
            'module_label'         => self::MODULE_LABELS['bpb'],
            'nomor_dokumen'        => (string) ($row['nomor'] ?: ('BPB-' . $id)),
            'judul'                => $judul,
            'subjudul'             => $itemCount > 0 ? "{$itemCount} item barang diminta" : 'Bon Permintaan Barang',
            'pemohon_nama'         => (string) ($row['applicant_name'] ?: '-'),
            'pemohon_divisi'       => (string) ($row['applicant_department'] ?: '-'),
            'id_proyek'            => null,
            'nama_proyek'          => 'Lintas Proyek',
            'id_kavling'           => null,
            'lokasi_kavling'       => null,
            'nominal'              => $nominal,
            'status'               => $status,
            'status_label'         => $statusLabel,
            'status_variant'       => $statusVariant,
            'created_at'           => (string) ($row['submitted_at'] ?: $row['created_at'] ?: ''),
            'can_respond'          => $canRespond,
            'primary_action_label' => $actionLabel,
            'modal_payload'        => [
                'id_bpb'      => $id,
                'share_token' => ! empty($row['share_token']) ? (string) $row['share_token'] : null,
            ],
            'spa_route'            => '/bpb?open=' . $id,
        ];
    }

    private function mapFeeSalesDto(array $row, array $context, array $userSnapshots): array
    {
        $id          = (int) $row['id'];
        $status      = (string) ($row['status'] ?? 'diajukan');
        $hasMkdt     = ! empty($row['ttd_mkdt_by']);
        $hasKeuangan = ! empty($row['ttd_keuangan_by']);
        $totalUnit   = (int) ($row['total_unit'] ?? 0);
        $totalItem   = (int) ($row['total_item'] ?? 0);
        $salesNames  = trim((string) ($row['sales_names'] ?? ''));

        $creatorId   = (int) ($row['created_by'] ?? 0);
        $snapshot    = $userSnapshots[$creatorId] ?? null;
        $pemohonNama = trim((string) ($row['ttd_pembuat_nama'] ?? ''));
        if ($pemohonNama === '') {
            $pemohonNama = $snapshot['nama'] ?? '-';
        }
        $pemohonDivisi = $snapshot['divisi'] ?? 'MKDT';

        $canRespond  = false;
        $actionLabel = 'Lihat Pengajuan';

        if ($status === 'diajukan') {
            if (! empty($context['can_sign_mkdt']) && ! $hasMkdt) {
                $canRespond  = true;
                $actionLabel = 'TTD MKDT';
            } elseif (! empty($context['can_sign_keuangan']) && ! $hasKeuangan) {
                $canRespond  = true;
                $actionLabel = 'TTD Keuangan';
            } elseif (! empty($context['can_cairkan_fee_sales']) && $hasMkdt && $hasKeuangan) {
                $canRespond  = true;
                $actionLabel = 'Proses Cairkan';
            }
        }

        [$statusLabel, $statusVariant] = match ($status) {
            'diajukan' => (! $hasMkdt && ! $hasKeuangan)
                ? ['Menunggu TTD MKDT & Keuangan', 'warning']
                : ((! $hasMkdt)
                    ? ['Menunggu TTD MKDT', 'warning']
                    : ((! $hasKeuangan)
                        ? ['Menunggu TTD Keuangan', 'warning']
                        : ['Siap Dicairkan', 'primary'])),
            'selesai'  => ['Selesai / Cair', 'success'],
            'ditolak'  => ['Ditolak', 'danger'],
            'void'     => ['Void / Dibatalkan', 'secondary'],
            default    => [ucfirst($status), 'secondary'],
        };

        $judul    = 'Pengajuan Fee Sales' . ($salesNames !== '' ? ' - ' . $salesNames : '');
        $subjudul = "{$totalUnit} unit / {$totalItem} termin" . (! empty($row['catatan']) ? ' • ' . $this->cleanText($row['catatan'], 90) : '');

        $shareToken = ! empty($row['share_token']) ? (string) $row['share_token'] : null;

        return [
            'uid'                  => 'fee_sales:' . $id,
            'id'                   => $id,
            'module'               => 'fee_sales',
            'module_label'         => self::MODULE_LABELS['fee_sales'],
            'nomor_dokumen'        => (string) ($row['nomor_pengajuan'] ?: ('FS-' . $id)),
            'judul'                => $judul,
            'subjudul'             => $subjudul,
            'pemohon_nama'         => $pemohonNama,
            'pemohon_divisi'       => $pemohonDivisi,
            'id_proyek'            => ! empty($row['id_proyek']) ? (int) $row['id_proyek'] : null,
            'nama_proyek'          => (string) ($row['nama_proyek'] ?: '-'),
            'id_kavling'           => ! empty($row['first_id_kavling']) ? (int) $row['first_id_kavling'] : null,
            'lokasi_kavling'       => null,
            'nominal'              => (float) ($row['total_nominal'] ?? 0),
            'status'               => $status,
            'status_label'         => $statusLabel,
            'status_variant'       => $statusVariant,
            'created_at'           => (string) ($row['created_at'] ?: ($row['tanggal_pengajuan'] ? $row['tanggal_pengajuan'] . ' 00:00:00' : '')),
            'can_respond'          => $canRespond,
            'primary_action_label' => $actionLabel,
            'modal_payload'        => [
                'id_pengajuan' => $id,
                'share_token'  => $shareToken,
                'id_proyek'    => ! empty($row['id_proyek']) ? (int) $row['id_proyek'] : null,
            ],
            'spa_route'            => '/fee-sales/pengajuan/' . ($shareToken ?: $id),
        ];
    }

    private function mapKavlingRequestDto(array $row, array $context, array $userSnapshots): array
    {
        $id           = (int) $row['id_request'];
        $status       = (string) ($row['status'] ?? 'pending');
        $jenis        = (string) ($row['jenis_request'] ?? 'tambah_baru');
        $idProyek     = ! empty($row['id_proyek']) ? (int) $row['id_proyek'] : null;
        $creatorId    = (int) ($row['created_by'] ?? 0);
        $snapshot     = $userSnapshots[$creatorId] ?? ['nama' => '-', 'divisi' => 'MKDT'];

        $lokasi = trim((string) ($row['multi_kavling_labels'] ?? ''));
        if ($lokasi === '' && ! empty($row['no_kavling'])) {
            $lokasi = trim((string) ($row['kavling_nama_jalan'] ?? '') . ' No. ' . (string) $row['no_kavling']);
        }
        if ($lokasi === '') {
            $lokasi = trim((string) ($row['multi_jalan_labels'] ?? ''));
        }
        if ($lokasi === '' && ! empty($row['nama_jalan'])) {
            $lokasi = 'Jalan ' . $row['nama_jalan'];
        }
        if ($lokasi === '' && ! empty($row['nama_cluster'])) {
            $lokasi = 'Cluster ' . $row['nama_cluster'];
        }

        $tipeParts = [];
        if (! empty($row['no_tipe_rumah']) || ! empty($row['tipe_rumah'])) {
            $tipeParts[] = trim(($row['no_tipe_rumah'] ?? '') . ' ' . ($row['tipe_rumah'] ?? ''));
        }
        if (! empty($row['tipe_manual'])) {
            $tipeParts[] = (string) $row['tipe_manual'];
        }
        $tipeText   = $tipeParts !== [] ? implode(' / ', $tipeParts) : null;
        $jenisLabel = $jenis === 'ubah_tipe' ? 'Ubah Tipe Kavling' : 'Tambah Kavling Baru';

        $judul    = $jenisLabel . ($lokasi !== '' ? ' - ' . $lokasi : '');
        $subjudul = ($tipeText ? "Tipe: {$tipeText} • " : '') . $this->cleanText($row['keterangan'] ?? '', 100);

        [$statusLabel, $statusVariant] = match ($status) {
            'pending'   => ['Menunggu Planning', 'warning'],
            'approved'  => ['Disetujui', 'success'],
            'rejected'  => ['Ditolak', 'danger'],
            'cancelled' => ['Dibatalkan', 'secondary'],
            default     => [ucfirst($status), 'secondary'],
        };

        $canRespond  = $status === 'pending' && (! empty($context['is_planning']) || ! empty($context['is_admin']));
        $actionLabel = $canRespond ? 'Proses Request' : 'Lihat Request';

        $idKavling = ! empty($row['id_kavling'])
            ? (int) $row['id_kavling']
            : (! empty($row['first_multi_id_kavling']) ? (int) $row['first_multi_id_kavling'] : null);

        $spaRoute = '/siteplan/view?id_proyek=' . ($idProyek ?? '');
        if ($idKavling !== null) {
            $spaRoute .= '&id_kavling=' . $idKavling;
        }

        return [
            'uid'                  => 'kavling_request:' . $id,
            'id'                   => $id,
            'module'               => 'kavling_request',
            'module_label'         => self::MODULE_LABELS['kavling_request'],
            'nomor_dokumen'        => 'REQ-KVL-' . str_pad((string) $id, 4, '0', STR_PAD_LEFT),
            'judul'                => $judul,
            'subjudul'             => $subjudul !== '' ? $subjudul : $jenisLabel,
            'pemohon_nama'         => $snapshot['nama'],
            'pemohon_divisi'       => $snapshot['divisi'],
            'id_proyek'            => $idProyek,
            'nama_proyek'          => (string) ($row['nama_proyek'] ?: '-'),
            'id_kavling'           => $idKavling,
            'lokasi_kavling'       => $lokasi !== '' ? $lokasi : null,
            'nominal'              => null,
            'status'               => $status,
            'status_label'         => $statusLabel,
            'status_variant'       => $statusVariant,
            'created_at'           => (string) ($row['created_at'] ?: ''),
            'can_respond'          => $canRespond,
            'primary_action_label' => $actionLabel,
            'modal_payload'        => [
                'id_request'    => $id,
                'id_proyek'     => $idProyek,
                'id_kavling'    => $idKavling,
                'jenis_request' => $jenis,
            ],
            'spa_route'            => $spaRoute,
        ];
    }

    private function mapTagihanDireksiDto(array $row, array $context, array $userSnapshots): array
    {
        $noInv        = (string) $row['no_inv'];
        $nomorSurat   = trim((string) ($row['nomor_surat'] ?? ''));
        $signedStatus = (int) ($row['is_signed_direktur'] ?? 0);
        $creatorId    = (int) ($row['add_by'] ?? 0);
        $snapshot     = $userSnapshots[$creatorId] ?? ['nama' => '-', 'divisi' => 'Keuangan'];

        $totalNominal = 0.0;
        if (! empty($row['tagihan'])) {
            $decoded = json_decode(str_replace('&quot;', '"', (string) $row['tagihan']), true);
            if (is_array($decoded)) {
                foreach ($decoded as $item) {
                    $totalNominal += (float) ($item['nominal'] ?? 0);
                }
            }
        }

        [$statusKey, $statusLabel, $statusVariant] = match ($signedStatus) {
            1       => ['approved', 'Disetujui Direksi', 'success'],
            2       => ['rejected', 'Ditolak Direksi', 'danger'],
            default => ['pending', 'Menunggu TTD Direksi', 'warning'],
        };

        $canRespond  = $signedStatus === 0 && (! empty($context['is_direksi']) || ! empty($context['is_admin']));
        $actionLabel = $canRespond ? 'TTD / Tolak' : 'Lihat Tagihan';

        $lokasi = '';
        if (! empty($row['nama_jalan']) || ! empty($row['no_kavling'])) {
            $lokasi = trim(($row['nama_jalan'] ?? '') . ' No. ' . ($row['no_kavling'] ?? ''));
        }

        $konsumen = trim((string) ($row['nama_konsumen'] ?? ''));
        $judul    = 'Surat Tagihan ' . ($konsumen !== '' ? $konsumen : ($nomorSurat !== '' ? $nomorSurat : $noInv));
        $subjudul = 'Invoice ' . $noInv . ($lokasi !== '' ? ' • ' . $lokasi : '');

        return [
            'uid'                  => 'tagihan_direksi:' . $noInv,
            'id'                   => $noInv,
            'module'               => 'tagihan_direksi',
            'module_label'         => self::MODULE_LABELS['tagihan_direksi'],
            'nomor_dokumen'        => $nomorSurat !== '' ? $nomorSurat : $noInv,
            'judul'                => $judul,
            'subjudul'             => $subjudul,
            'pemohon_nama'         => $snapshot['nama'],
            'pemohon_divisi'       => $snapshot['divisi'],
            'id_proyek'            => ! empty($row['id_proyek']) ? (int) $row['id_proyek'] : null,
            'nama_proyek'          => (string) ($row['nama_proyek'] ?: '-'),
            'id_kavling'           => ! empty($row['id_kavling']) ? (int) $row['id_kavling'] : null,
            'lokasi_kavling'       => $lokasi !== '' ? $lokasi : null,
            'nominal'              => $totalNominal,
            'status'               => $statusKey,
            'status_label'         => $statusLabel,
            'status_variant'       => $statusVariant,
            'created_at'           => (string) ($row['date_add'] ?: ''),
            'can_respond'          => $canRespond,
            'primary_action_label' => $actionLabel,
            'modal_payload'        => [
                'no_inv'      => $noInv,
                'nomor_surat' => $nomorSurat,
                'id_proyek'   => ! empty($row['id_proyek']) ? (int) $row['id_proyek'] : null,
                'id_kavling'  => ! empty($row['id_kavling']) ? (int) $row['id_kavling'] : null,
            ],
            'spa_route'            => '/direksi/tagihan?no_inv=' . urlencode($noInv),
        ];
    }

    private function mapCashoutSubkonDto(array $row, array $context, array $userSnapshots): array
    {
        $idDetail        = (int) $row['id_cashout_subkon_detail'];
        $idCashoutSubkon = (int) $row['id_cashout_subkon'];
        $statusInt       = ((int) ($row['is_paid'] ?? 0) === 1) ? 4 : (int) ($row['status'] ?? 0);

        $creatorId = (int) (($row['spp_add_by'] ?? 0) ?: ($row['add_by'] ?? 0));
        $snapshot  = $userSnapshots[$creatorId] ?? ['nama' => '-', 'divisi' => ($statusInt >= 2 ? 'Produksi' : 'Produksi')];

        [$statusLabel, $statusVariant] = match ($statusInt) {
            0       => ['Terbit SPK (Menunggu Jatuh Tempo)', 'warning'],
            1       => ['Turun Jatuh Tempo (Menunggu SPP)', 'warning'],
            2       => ['Pengajuan SPP (Menunggu Pencairan)', 'primary'],
            3       => ['Pengajuan Pencairan (Menunggu Bayar)', 'primary'],
            4       => ['Dibayar Oleh Keuangan', 'success'],
            default => ['Status ' . $statusInt, 'secondary'],
        };

        $isAdmin    = ! empty($context['is_admin']);
        $isKeuangan = ! empty($context['is_keuangan']);
        $isProduksi = ! empty($context['is_produksi']);

        $canRespond  = false;
        $actionLabel = 'Lihat SPK';

        if ($statusInt === 0 && ($isAdmin || $isKeuangan)) {
            $canRespond  = true;
            $actionLabel = 'Turun Jatuh Tempo';
        } elseif ($statusInt === 1 && ($isAdmin || $isProduksi)) {
            $canRespond  = true;
            $actionLabel = 'Ajukan SPP';
        } elseif ($statusInt === 2 && ($isAdmin || $isKeuangan)) {
            $canRespond  = true;
            $actionLabel = 'Ajukan Pencairan';
        } elseif ($statusInt === 3 && ($isAdmin || $isKeuangan)) {
            $canRespond  = true;
            $actionLabel = 'Bayar Termin';
        }

        $idKavlings = [];
        if (! empty($row['id_kavlings'])) {
            $idKavlings = array_values(array_filter(array_map('trim', explode(',', (string) $row['id_kavlings']))));
        }

        $selectedKavlings = [];
        if (! empty($row['kavling_options'])) {
            foreach (explode(',', (string) $row['kavling_options']) as $entry) {
                $parts = explode('|', $entry);
                if (count($parts) >= 3) {
                    $selectedKavlings[] = [
                        'id'   => (string) $parts[0],
                        'text' => trim($parts[1] . ' No ' . $parts[2]),
                    ];
                }
            }
        }

        $namaSubkon = trim((string) ($row['nama_subkon'] ?? ''));
        $ba         = trim((string) ($row['berita_acara'] ?? ''));
        $persen     = isset($row['persentase']) ? rtrim(rtrim((string) $row['persentase'], '0'), '.') . '%' : '';
        $judul      = ($ba !== '' ? $ba : 'Termin') . ($persen !== '' ? " ({$persen})" : '') . ($namaSubkon !== '' ? " - {$namaSubkon}" : '');
        $subjudul   = 'SPK: ' . ($row['nomor_surat'] ?: '-') . (! empty($row['spp_no']) ? ' • SPP: ' . $row['spp_no'] : '');

        return [
            'uid'                  => 'cashout_subkon:' . $idDetail,
            'id'                   => $idDetail,
            'module'               => 'cashout_subkon',
            'module_label'         => self::MODULE_LABELS['cashout_subkon'],
            'nomor_dokumen'        => (string) ($row['nomor_surat'] ?: ('SPK-' . $idCashoutSubkon)),
            'judul'                => $judul,
            'subjudul'             => $subjudul,
            'pemohon_nama'         => $snapshot['nama'],
            'pemohon_divisi'       => $snapshot['divisi'],
            'id_proyek'            => ! empty($row['id_proyek']) ? (int) $row['id_proyek'] : null,
            'nama_proyek'          => (string) ($row['nama_proyek'] ?: '-'),
            'id_kavling'           => ! empty($row['first_id_kavling']) ? (int) $row['first_id_kavling'] : null,
            'lokasi_kavling'       => ! empty($row['lokasi_kavling']) ? (string) $row['lokasi_kavling'] : null,
            'nominal'              => (float) ($row['nominal'] ?? 0),
            'status'               => (string) $statusInt,
            'status_label'         => $statusLabel,
            'status_variant'       => $statusVariant,
            'created_at'           => (string) ($row['pengajuan_cair_created_at'] ?: ($row['spp_created_at'] ?: ($row['spk_created_at'] ?: ''))),
            'can_respond'          => $canRespond,
            'primary_action_label' => $actionLabel,
            'modal_payload'        => [
                'id_cashout_subkon'        => $idCashoutSubkon,
                'id_cashout_subkon_detail' => $idDetail,
                'id_proyek'                => ! empty($row['id_proyek']) ? (int) $row['id_proyek'] : null,
                'id_kavlings'              => $idKavlings,
                'selected_kavlings'        => $selectedKavlings,
            ],
            'spa_route'            => '/cashout/subkon?open_id=' . $idCashoutSubkon,
        ];
    }

    private function mapMgmDto(array $row, array $context, array $userSnapshots): array
    {
        $id         = (int) $row['id'];
        $idReferral = (int) $row['id_referral'];
        $status     = (string) ($row['status'] ?? 'eligible');

        $creatorId = (int) (($row['submitted_keuangan_by'] ?? 0) ?: ($row['confirmed_by'] ?? 0) ?: ($row['add_by'] ?? 0));
        $snapshot  = $userSnapshots[$creatorId] ?? ['nama' => '-', 'divisi' => 'Promosi'];

        [$statusLabel, $statusVariant] = match ($status) {
            'eligible'          => ['Eligible (Menunggu Konfirmasi)', 'warning'],
            'dikonfirmasi'      => ['Dikonfirmasi (Belum Diajukan)', 'warning'],
            'diajukan_keuangan' => ['Diajukan ke Keuangan', 'primary'],
            'dibayar_promosi'   => ['Dibayar Promosi (Menunggu Reimburse)', 'primary'],
            'cair'              => ['Cair Keuangan (Siap Bayar Member)', 'primary'],
            'selesai'           => ['Selesai', 'success'],
            'batal'             => ['Dibatalkan', 'danger'],
            default             => [ucfirst($status), 'secondary'],
        };

        $isAdmin    = ! empty($context['is_admin']);
        $isPromosi  = ! empty($context['is_promosi']);
        $isKeuangan = ! empty($context['is_keuangan']);

        $canRespond  = false;
        $actionLabel = 'Lihat MGM';

        if ($status === 'eligible' && ($isAdmin || $isPromosi)) {
            $canRespond  = true;
            $actionLabel = 'Konfirmasi Bonus';
        } elseif ($status === 'dikonfirmasi' && ($isAdmin || $isPromosi)) {
            $canRespond  = true;
            $actionLabel = 'Ajukan / Bayar';
        } elseif ($status === 'diajukan_keuangan' && ($isAdmin || $isKeuangan)) {
            $canRespond  = true;
            $actionLabel = 'Cairkan Keuangan';
        } elseif ($status === 'dibayar_promosi' && empty($row['cair_keuangan_at']) && ($isAdmin || $isPromosi)) {
            $canRespond  = true;
            $actionLabel = 'Ajukan Reimburse';
        } elseif ($status === 'cair' && empty($row['paid_by_promosi']) && ($isAdmin || $isPromosi)) {
            $canRespond  = true;
            $actionLabel = 'Bayar Member';
        }

        $lokasi = '';
        if (! empty($row['nama_jalan']) || ! empty($row['no_kavling'])) {
            $lokasi = trim(($row['nama_jalan'] ?? '') . ' No. ' . ($row['no_kavling'] ?? ''));
        }

        $nominal = match ($status) {
            'diajukan_keuangan' => (float) ($row['nominal_pengajuan_keuangan'] ?: $row['nominal_bonus']),
            'cair', 'selesai'   => (float) ($row['nominal_cair_keuangan'] ?: $row['nominal_bonus']),
            default             => (float) ($row['nominal_bonus'] ?? 0),
        };

        $tahapan  = trim((string) ($row['nama_tahapan'] ?? 'Bonus MGM'));
        $referrer = trim((string) ($row['referrer_nama'] ?? '-'));
        $referred = trim((string) ($row['referred_nama'] ?? '-'));

        return [
            'uid'                  => 'mgm:' . $id,
            'id'                   => $id,
            'module'               => 'mgm',
            'module_label'         => self::MODULE_LABELS['mgm'],
            'nomor_dokumen'        => 'MGM-' . str_pad((string) $id, 4, '0', STR_PAD_LEFT),
            'judul'                => "{$tahapan} - {$referrer}",
            'subjudul'             => "Referal: {$referred}" . ($lokasi !== '' ? " • {$lokasi}" : ''),
            'pemohon_nama'         => $snapshot['nama'],
            'pemohon_divisi'       => $snapshot['divisi'],
            'id_proyek'            => ! empty($row['id_proyek']) ? (int) $row['id_proyek'] : null,
            'nama_proyek'          => (string) ($row['nama_proyek'] ?: '-'),
            'id_kavling'           => ! empty($row['id_kavling']) ? (int) $row['id_kavling'] : null,
            'lokasi_kavling'       => $lokasi !== '' ? $lokasi : null,
            'nominal'              => $nominal,
            'status'               => $status,
            'status_label'         => $statusLabel,
            'status_variant'       => $statusVariant,
            'created_at'           => (string) ($row['submitted_keuangan_at'] ?: ($row['paid_promosi_at'] ?: ($row['confirmed_at'] ?: ($row['eligible_at'] ?: ($row['created_at'] ?: ''))))),
            'can_respond'          => $canRespond,
            'primary_action_label' => $actionLabel,
            'modal_payload'        => [
                'id_bonus'    => $id,
                'id_referral' => $idReferral,
                'id_proyek'   => ! empty($row['id_proyek']) ? (int) $row['id_proyek'] : null,
            ],
            'spa_route'            => '/member-get-member?id_referral=' . $idReferral,
        ];
    }

    private function mapTiketMasalahDto(array $row, array $context, array $userSnapshots): array
    {
        $id        = (int) $row['id'];
        $userId    = (int) $context['user_id'];
        $status    = (string) ($row['status'] ?? 'dibuat');
        $refType   = (string) ($row['ref_type'] ?? 'kavling');
        $refId     = (int) ($row['ref_id'] ?? 0);
        $picUserId = (int) ($row['pic_user_id'] ?? 0);
        $snapshot  = $userSnapshots[$picUserId] ?? ['nama' => '-', 'divisi' => '-'];

        $lokasi = '';
        if ($refType === 'kavling') {
            $lokasi = trim(($row['kavling_jalan'] ?? '') . ' No. ' . ($row['no_kavling'] ?? ''));
        } else {
            $lokasi = trim(($row['others_jalan'] ?? '') . ' ' . ($row['others_nama'] ?? ''));
        }

        [$statusLabel, $statusVariant] = match ($status) {
            'draft'        => ['Draft', 'secondary'],
            'dibuat'       => ['Baru / Menunggu Respon', 'warning'],
            'dalam_proses' => ['Dalam Proses', 'primary'],
            'selesai'      => ['Selesai', 'success'],
            'batal'        => ['Dibatalkan', 'danger'],
            default        => [ucfirst(str_replace('_', ' ', $status)), 'secondary'],
        };

        $isAssigned  = ! empty($row['is_assigned_to_me']);
        $isPic       = $picUserId === $userId;
        $isActive    = ! in_array($status, ['selesai', 'batal'], true);
        $canRespond  = $isActive && ($isAssigned || $isPic || ! empty($context['is_admin']));
        $actionLabel = 'Lihat Tiket';
        if ($canRespond) {
            $actionLabel = $status === 'draft' ? 'Terbitkan Tiket' : 'Update Progres';
        }

        $prioritas   = strtoupper((string) ($row['prioritas'] ?? 'NORMAL'));
        $cleanDesc   = $this->cleanText($row['keterangan'] ?? '', 120);
        $assignedStr = trim((string) ($row['assigned_users_list'] ?? ''));

        return [
            'uid'                  => 'tiket_masalah:' . $id,
            'id'                   => $id,
            'module'               => 'tiket_masalah',
            'module_label'         => self::MODULE_LABELS['tiket_masalah'],
            'nomor_dokumen'        => 'TKT-' . str_pad((string) $id, 4, '0', STR_PAD_LEFT),
            'judul'                => "[{$prioritas}] " . ($cleanDesc !== '' ? $cleanDesc : 'Tiket Masalah #' . $id),
            'subjudul'             => ($lokasi !== '' ? $lokasi : 'Area Proyek') . ($assignedStr !== '' ? " • Assigned: {$assignedStr}" : ''),
            'pemohon_nama'         => $snapshot['nama'],
            'pemohon_divisi'       => $snapshot['divisi'],
            'id_proyek'            => ! empty($row['id_proyek']) ? (int) $row['id_proyek'] : null,
            'nama_proyek'          => (string) ($row['nama_proyek'] ?: '-'),
            'id_kavling'           => $refType === 'kavling' && $refId > 0 ? $refId : null,
            'lokasi_kavling'       => $lokasi !== '' ? $lokasi : null,
            'nominal'              => null,
            'status'               => $status,
            'status_label'         => $statusLabel,
            'status_variant'       => $statusVariant,
            'created_at'           => (string) ($row['created_at'] ?: ''),
            'can_respond'          => $canRespond,
            'primary_action_label' => $actionLabel,
            'modal_payload'        => [
                'id_tiket'  => $id,
                'ref_type'  => $refType,
                'ref_id'    => $refId,
                'id_proyek' => ! empty($row['id_proyek']) ? (int) $row['id_proyek'] : null,
            ],
            'spa_route'            => '/tiket-masalah?open=' . $id,
        ];
    }

    private function cleanText(?string $raw, int $maxLength = 120): string
    {
        if ($raw === null || $raw === '') {
            return '';
        }

        $plain = trim(preg_replace('/\s+/', ' ', strip_tags(html_entity_decode($raw, ENT_QUOTES, 'UTF-8'))) ?? '');
        if (mb_strlen($plain) <= $maxLength) {
            return $plain;
        }

        return mb_substr($plain, 0, $maxLength - 3) . '...';
    }
}

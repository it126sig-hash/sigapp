<?php

namespace App\Services\Mkdt;

use App\Repositories\Mkdt\PosisiKonsumenRepository;
use App\Services\FileAccessService;
use App\Services\PrintService;
use App\Services\Proyek\ActiveProyekService;
use App\Services\Settings\SiteplanMenuService;
use Hermawan\DataTables\DataTable;

class PosisiKonsumenService
{
    protected $db;
    protected $posisiKonsumenRepo;
    protected FileAccessService $fileAccessService;
    protected SiteplanMenuService $siteplanMenuService;
    protected MkdtFinancialBreakdownService $financialBreakdownService;
    protected ?ActiveProyekService $activeProyekService;
    protected ?PrintService $printService;
    protected ?array $actionMenuItems = null;
    protected int $actionMenuRoleId = 0;

    public function __construct(
        ?PosisiKonsumenRepository $posisiKonsumenRepo = null,
        ?ActiveProyekService $activeProyekService = null,
        ?PrintService $printService = null
    ) {
        $this->db = \Config\Database::connect();
        $this->posisiKonsumenRepo = $posisiKonsumenRepo ?? new PosisiKonsumenRepository();
        $this->fileAccessService = new FileAccessService();
        $this->siteplanMenuService = new SiteplanMenuService();
        $this->financialBreakdownService = new MkdtFinancialBreakdownService();
        $this->activeProyekService = $activeProyekService;
        $this->printService = $printService;
    }

    protected function getActiveProyekService(): ActiveProyekService
    {
        if ($this->activeProyekService === null) {
            $this->activeProyekService = new ActiveProyekService();
        }

        return $this->activeProyekService;
    }

    protected function getPrintService(): PrintService
    {
        if ($this->printService === null) {
            $this->printService = new PrintService();
        }

        return $this->printService;
    }

    /**
     * Susun data halaman Web index() dan metadata /api/mkdt/posisi-konsumen/meta.
     *
     * @param array<int|string, mixed> $userGroups
     * @return array<string, mixed>
     */
    public function getIndexData(?string $status, int $userId, array $userGroups): array
    {
        $normalizedStatus = ($status !== null && trim($status) !== '') ? trim($status) : null;

        $viewContent = 'kavling/list-kavling';
        if ($normalizedStatus === 'akad') {
            $viewContent = 'kavling/list-kavling-akad';
        } elseif ($normalizedStatus === 'batal') {
            $viewContent = 'kavling/list-kavling-batal';
        }

        $proyek = $this->getActiveProyekService()->resolveAndGet();
        if (!$proyek) {
            $proyek = $this->posisiKonsumenRepo->getFirstProyek();
        }

        $pph   = $this->posisiKonsumenRepo->getPphList();
        $ppn   = $this->posisiKonsumenRepo->getPpnList();
        $liKeu = $this->posisiKonsumenRepo->getLiKeu();
        $conf  = $this->posisiKonsumenRepo->getConfigShapeMap();

        $isProjectGroup = $this->hasAnyGroup($userGroups, ['1', '7', '8']);
        $isLegalGroup   = $this->hasAnyGroup($userGroups, ['5']);

        $viewData = [
            'pph'        => $pph,
            'ppn'        => $ppn,
            'controller' => 'PosisiKonsumen',
            'title'      => 'Posisi Konsumen ' . ($normalizedStatus ?? ''),
            'proyek'     => $proyek,
            'li_keu'     => json_encode($liKeu),
            'conf'       => json_encode($conf),
        ];

        if ($isProjectGroup) {
            $viewData['list'] = $this->posisiKonsumenRepo->getChecklistSubItemsWithGroup();
        }

        $hasAkses = [
            'proyek' => false,
            'legal'  => false,
        ];

        if ($proyek && $isProjectGroup) {
            $hasAkses['proyek'] = $this->userHasProjectAccess($proyek, $userId);
            $tanggalPembangunanAkses = $this->posisiKonsumenRepo->getHakAksesByUser($userId);
            $hasUpdateAccess = array_values(array_filter($tanggalPembangunanAkses, static function ($item) {
                return ($item->nama_akses ?? '') === 'update_tanggal_pembangunan';
            }));
            $hasAkses['update_tanggal_pembangunan'] = count($hasUpdateAccess) > 0;
        } elseif ($proyek && $isLegalGroup) {
            $hasAkses['legal'] = $this->userHasProjectAccess($proyek, $userId);
        }

        $viewData['has_akses'] = $hasAkses;

        return [
            'content' => $viewContent,
            'data'    => $viewData,
            'meta'    => [
                'proyek'              => $proyek,
                'active_proyek_id'    => $proyek ? (int) ($proyek->id_proyek ?? 0) : null,
                'pph'                 => $pph,
                'ppn'                 => $ppn,
                'li_keu'              => $liKeu,
                'conf'                => $conf,
                'has_akses'           => $hasAkses,
                'list'                => $viewData['list'] ?? [],
                'status_options'      => [
                    ['value' => 'aktif', 'label' => 'Aktif / Booking'],
                    ['value' => 'akad', 'label' => 'Akad'],
                    ['value' => 'batal', 'label' => 'Batal'],
                ],
                'status_kavling_options' => [
                    ['value' => 'semua', 'label' => 'Semua Status'],
                    ['value' => 'aktif', 'label' => 'Konsumen Aktif'],
                    ['value' => 'booking', 'label' => 'Konsumen Booking'],
                    ['value' => 'indent', 'label' => 'Konsumen Akad Indent'],
                    ['value' => 'akad', 'label' => 'Konsumen Akad'],
                ],
                'bulan_field_options' => [
                    ['value' => 'booking', 'label' => 'Tanggal Booking'],
                    ['value' => 'indent', 'label' => 'Tanggal Akad Indent'],
                    ['value' => 'akad', 'label' => 'Tanggal Akad'],
                ],
            ],
        ];
    }

    public function userHasProjectAccess(?object $proyek, int $userId): bool
    {
        if (!$proyek || !isset($proyek->id_users) || $proyek->id_users === null || $proyek->id_users === '') {
            return false;
        }

        $allowedUserIds = array_filter(array_map('trim', explode(',', (string) $proyek->id_users)));

        return in_array((string) $userId, $allowedUserIds, true);
    }

    /**
     * @param array<int|string, mixed> $userGroups
     * @param array<int, string>       $targets
     */
    private function hasAnyGroup(array $userGroups, array $targets): bool
    {
        $isList = array_is_list($userGroups);

        foreach ($userGroups as $key => $value) {
            if (in_array((string) $value, $targets, true)) {
                return true;
            }
            if (!$isList && is_int($key) && in_array((string) $key, $targets, true)) {
                return true;
            }
        }

        return false;
    }

    private function resolveCurrentRoleId(): int
    {
        if (!function_exists('user') || !user()) {
            return 0;
        }

        foreach (user()->getRoles() as $roleId => $roleName) {
            return (int) $roleId;
        }

        return 0;
    }

    private function getActionMenuItems(): array
    {
        if ($this->actionMenuItems === null) {
            $this->actionMenuRoleId = $this->resolveCurrentRoleId();
            $this->actionMenuItems = $this->siteplanMenuService->getActionItemsForList($this->actionMenuRoleId);
        }

        return $this->actionMenuItems;
    }

    private function buildPoskonRowPayload(object $row): array
    {
        return [
            'id_kavling'   => $row->id_kavling ?? null,
            'id_mkdt'      => $row->id_mkdt ?? null,
            'id_keuangan'  => $row->id_keuangan ?? null,
            'id_legal'     => $row->id_legal ?? null,
            'id_produksi'  => $row->id_produksi ?? null,
            'id_hargajual' => $row->id_hargajual ?? null,
            'id_komplain'  => $row->id_komplain ?? null,
            'nama_jalan'   => $row->nama_jalan ?? '',
            'no_kavling'   => $row->no_kavling ?? '',
            'no_tipe_rumah'=> $row->no_tipe_rumah ?? '',
            'tipe_rumah'   => $row->tipe_rumah ?? '',
            'harga_akhir'  => $row->harga_akhir ?? null,
            'kode_referal' => $row->kode_referal ?? '',
            'id_proyek'    => $row->id_proyek ?? null,
            'nama_proyek'  => $row->nama_proyek ?? '',
            'uadd_by'      => $row->uadd_by ?? '',
        ];
    }

    private function renderPoskonActionHtml(object $row): string
    {
        $idKavling = (int) ($row->id_kavling ?? 0);
        if ($idKavling <= 0) {
            return '';
        }

        $rowEncoded = rawurlencode(json_encode($this->buildPoskonRowPayload($row), JSON_UNESCAPED_UNICODE));
        $menuItems = $this->getActionMenuItems();
        $menuHtml = '';
        $lastGroup = null;

        foreach ($menuItems as $item) {
            if ($this->actionMenuRoleId === 1 && !empty($item['group_label']) && $item['group_label'] !== $lastGroup) {
                $lastGroup = $item['group_label'];
                $menuHtml .= '<div class="dropdown-header">' . esc($lastGroup) . '</div>';
            }

            $icon = !empty($item['icon'])
                ? '<i class="' . esc($item['icon']) . '"></i> '
                : '';
            $onclick = rawurlencode((string) ($item['onclick'] ?? ''));
            $menuHtml .= '<button type="button" class="dropdown-item poskon-menu-action" data-onclick="' . esc($onclick) . '" data-row="' . esc($rowEncoded) . '" data-group="' . esc((string) ($item['id_group'] ?? '')) . '">'
                . $icon . esc($item['label'] ?? '')
                . '</button>';
        }

        $dropdown = $menuHtml !== ''
            ? '<div class="btn-group ml-50">'
                . '<button type="button" class="btn btn-outline-primary btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Aksi</button>'
                . '<div class="dropdown-menu dropdown-menu-right">' . $menuHtml . '</div>'
                . '</div>'
            : '';

        return '<div class="btn-group poskon-action-cell" style="white-space:nowrap">'
            . '<button type="button" class="btn btn-info btn-sm poskon-detail-btn" data-row="' . esc($rowEncoded) . '" title="Lihat Detail">'
            . '<i class="fa fa-eye"></i></button>'
            . $dropdown
            . '</div>';
    }

    private function renderReferralQrHtml(object $row): string
    {
        $kodeReferal = trim((string) ($row->kode_referal ?? ''));
        if ($kodeReferal === '') {
            return '-';
        }

        return '<a href="javascript:void(0)" class="text-primary font-weight-bold js-referral-qr"'
            . ' data-kode-referal="' . esc($kodeReferal) . '"'
            . ' data-id-mkdt="' . esc((string) ($row->id_mkdt ?? '')) . '"'
            . ' data-id-proyek="' . esc((string) ($row->id_proyek ?? '')) . '"'
            . ' title="Generate QR Referal">'
            . '<i class="fas fa-qrcode mr-50"></i>' . esc($kodeReferal)
            . '</a>';
    }

    private function renderPercentageLabel(string $label, ?int $percentage, bool $colorize = false): string
    {
        $escapedLabel = esc($label);
        if (!$colorize || $percentage === null) {
            return $escapedLabel;
        }
        if ($percentage === 100) {
            return '<span class="text-success font-weight-bold">' . $escapedLabel . '</span>';
        }
        if ($percentage > 0 && $percentage < 100) {
            return '<span class="text-warning poskon-progress-partial font-weight-bold">' . $escapedLabel . '</span>';
        }

        return $escapedLabel;
    }

    private function renderProgress(float $target, float $paid, bool $colorize = false): string
    {
        $progress = $this->financialBreakdownService->progress($target, $paid);
        if ($progress['status'] === 'reconcile') {
            return '<span class="badge badge-warning text-wrap">Perlu Rekonsiliasi</span>';
        }
        if ($progress['status'] === 'overpaid') {
            return '<span class="badge badge-warning" title="Pembayaran melebihi target MKDT">'
                . esc($progress['label']) . '</span>';
        }
        return $this->renderPercentageLabel($progress['label'], $progress['percentage'], $colorize);
    }

    /**
     * Raw DataTables payload bebas HTML untuk ?format=raw (aktif/akad).
     *
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    public function getRawDataTables(array $params, ?string $status = null): array
    {
        $draw       = (int) ($params['draw'] ?? 1);
        $rowNumber  = max(0, (int) ($params['start'] ?? 0));
        $repoResult = $this->posisiKonsumenRepo->getRawDataTables($params, $status);

        $items = [];
        foreach ($repoResult['rows'] as $row) {
            $hasAjb    = !empty($row->akad_tgl) && $row->akad_tgl !== '0000-00-00';
            $hasIndent = !empty($row->akad_indent_tgl) && $row->akad_indent_tgl !== '0000-00-00';
            $akadLabel = '-';
            $akadType  = null;
            if ($hasAjb) {
                $akadLabel = 'AJB: ' . $this->format_tgl($row->akad_tgl);
                $akadType  = 'ajb';
            } elseif ($hasIndent) {
                $akadLabel = 'Indent: ' . $this->format_tgl($row->akad_indent_tgl);
                $akadType  = 'indent';
            }

            $isKpr = (int) ($row->is_kpr ?? 0);
            $um    = (float) ($row->um ?? 0);
            $adm   = (float) ($row->adm ?? 0);
            $bb    = (float) ($row->bb ?? 0);
            $totUm = (float) ($row->total_um ?? 0);
            $totAdm = (float) ($row->total_adm ?? 0);
            $totBb = (float) ($row->total_bb ?? 0);

            $items[] = [
                'no'                       => ++$rowNumber,
                'id_kavling'               => (int) ($row->id_kavling ?? 0),
                'id_mkdt'                  => (int) ($row->id_mkdt ?? 0),
                'id_keuangan'              => $row->id_keuangan !== null ? (int) $row->id_keuangan : null,
                'id_legal'                 => $row->id_legal !== null ? (int) $row->id_legal : null,
                'id_produksi'              => $row->id_produksi !== null ? (int) $row->id_produksi : null,
                'id_hargajual'             => $row->id_hargajual !== null ? (int) $row->id_hargajual : null,
                'id_komplain'              => $row->id_komplain !== null ? (int) $row->id_komplain : null,
                'id_proyek'                => $row->id_proyek !== null ? (int) $row->id_proyek : null,
                'nama_proyek'              => (string) ($row->nama_proyek ?? ''),
                'id_cluster'               => $row->id_cluster !== null ? (int) $row->id_cluster : null,
                'id_jalan'                 => $row->id_jalan !== null ? (int) $row->id_jalan : null,
                'nama_jalan'               => (string) ($row->nama_jalan ?? ''),
                'no_kavling'               => (string) ($row->no_kavling ?? ''),
                'id_tipe'                  => $row->id_tipe ?? null,
                'tipe_rumah'               => (string) ($row->tipe_rumah ?? ''),
                'no_tipe_rumah'            => (string) ($row->no_tipe_rumah ?? ''),
                'lb'                       => $row->lb !== null ? (float) $row->lb : null,
                'lt'                       => $row->lt !== null ? (float) $row->lt : null,
                'harga_akhir'              => $row->harga_akhir !== null ? (float) $row->harga_akhir : null,
                'nama_konsumen'            => (string) ($row->nama_konsumen ?? ''),
                'kode_referal'             => (string) ($row->kode_referal ?? ''),
                'sales'                    => (string) ($row->sales ?? ''),
                'booking_tgl'              => $row->booking_tgl ?? null,
                'booking_tgl_formatted'    => $this->format_tgl($row->booking_tgl ?? null),
                'wawancara_tgl'            => $row->wawancara_tgl ?? null,
                'wawancara_tgl_formatted'  => $this->format_tgl($row->wawancara_tgl ?? null),
                'akad'                     => (int) ($row->akad ?? 0),
                'akad_indent'              => (int) ($row->akad_indent ?? 0),
                'akad_tgl'                 => $row->akad_tgl ?? null,
                'akad_indent_tgl'          => $row->akad_indent_tgl ?? null,
                'akad_type'                => $akadType,
                'akad_label'               => $akadLabel,
                'is_kpr'                   => $isKpr,
                'jenis_pembayaran'         => $isKpr === 1 ? 'KPR' : 'TUNAI',
                'bank'                     => (string) ($row->bank ?? ''),
                'keterangan'               => (string) ($row->keterangan ?? ''),
                'sp3k_tgl'                 => $row->sp3k_tgl ?? null,
                'sp3k_tgl_formatted'       => $this->format_tgl($row->sp3k_tgl ?? null),
                'sp3k_tgl_exp'             => $row->sp3k_tgl_exp ?? null,
                'sp3k_tgl_exp_formatted'   => $this->format_tgl($row->sp3k_tgl_exp ?? null),
                'sikasep'                  => (string) ($row->sikasep ?? ''),
                'tunai_progress'           => $isKpr === 1 ? null : $this->financialBreakdownService->progress($um + $adm + $bb, $totUm + $totAdm + $totBb),
                'um_progress'              => $isKpr === 0 ? null : $this->financialBreakdownService->progress($um, $totUm),
                'adm_progress'             => $isKpr === 0 ? null : $this->financialBreakdownService->progress($adm, $totAdm),
                'bb_progress'              => $isKpr === 0 ? null : $this->financialBreakdownService->progress($bb, $totBb),
                'um'                       => $um,
                'adm'                      => $adm,
                'bb'                       => $bb,
                'total_um'                 => $totUm,
                'total_adm'                => $totAdm,
                'total_bb'                 => $totBb,
                'progres_bangunan'         => (int) ($row->progres_bangunan ?? 0),
                'lpa'                      => (int) ($row->lpa ?? 0),
                'lpa_tanggal'              => $row->lpa_tanggal ?? null,
                'st_listrik'               => (int) ($row->st_listrik ?? 0),
                'sertifikat_split_no_hgb'  => (string) ($row->sertifikat_split_no_hgb ?? ''),
                'sertifikat_split_nib'     => (string) ($row->sertifikat_split_nib ?? ''),
                'pbg_no'                   => (string) ($row->pbg_no ?? ''),
                'pbb_pecah_nop'            => (string) ($row->pbb_pecah_nop ?? ''),
                'sikumbang'                => (string) ($row->sikumbang ?? ''),
                'keterangan_status'        => (string) ($row->keterangan_status ?: '-'),
                'uadd_by'                  => (string) ($row->uadd_by ?? ''),
                'uedit_by'                 => (string) ($row->uedit_by ?? ''),
                'row_payload'              => $this->buildPoskonRowPayload($row),
            ];
        }

        return [
            'draw'            => $draw,
            'recordsTotal'    => $repoResult['recordsTotal'],
            'recordsFiltered' => $repoResult['recordsFiltered'],
            'items'           => $items,
            'data'            => $items,
        ];
    }

    /**
     * Raw DataTables payload bebas HTML untuk ?format=raw (batal).
     *
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    public function getRawDataTablesBatal(array $params): array
    {
        $draw       = (int) ($params['draw'] ?? 1);
        $rowNumber  = max(0, (int) ($params['start'] ?? 0));
        $repoResult = $this->posisiKonsumenRepo->getRawDataTablesBatal($params);

        $items = [];
        foreach ($repoResult['rows'] as $row) {
            $isKpr        = (int) ($row->is_kpr ?? 0);
            $perluRefund  = (int) ($row->perlu_refund ?? 0);
            $totalTagihan = (float) ($row->um ?? 0) + (float) ($row->adm ?? 0) + (float) ($row->bb ?? 0);
            $sudahBayar   = (float) ($row->total_um ?? 0) + (float) ($row->total_adm ?? 0) + (float) ($row->total_bb ?? 0);

            $items[] = [
                'no'                      => ++$rowNumber,
                'id_kavling'              => (int) ($row->id_kavling ?? 0),
                'id_mkdt'                 => (int) ($row->id_mkdt ?? 0),
                'nama_proyek'             => (string) ($row->nama_proyek ?? ''),
                'nama_jalan'              => (string) ($row->nama_jalan ?? ''),
                'no_kavling'              => (string) ($row->no_kavling ?? ''),
                'tipe_rumah'              => (string) ($row->tipe_rumah ?? ''),
                'no_tipe_rumah'           => (string) ($row->no_tipe_rumah ?? ''),
                'nama_konsumen'           => (string) ($row->nama_konsumen ?? ''),
                'booking_tgl'             => $row->booking_tgl ?? null,
                'booking_tgl_formatted'   => $this->format_tgl($row->booking_tgl ?? null),
                'is_kpr'                  => $isKpr,
                'jenis_pembayaran'        => $isKpr === 1 ? 'KPR' : 'TUNAI',
                'keterangan_batal'        => (string) ($row->keterangan_batal ?? ''),
                'mkdt_batal_tgl'          => $row->mkdt_batal_tgl ?? null,
                'tanggal_batal_formatted' => $this->format_tgl($row->mkdt_batal_tgl ?? null),
                'nama_pembatal'           => (string) ($row->nama_pembatal ?? '-'),
                'perlu_refund'            => $perluRefund,
                'perlu_refund_label'      => $perluRefund === 1 ? 'Perlu Refund' : 'Tidak Perlu Refund',
                'total_tagihan'           => $totalTagihan,
                'sudah_bayar'             => $sudahBayar,
                'sisa_tagihan'            => $totalTagihan - $sudahBayar,
                'uadd_by'                 => (string) ($row->uadd_by ?? ''),
                'uedit_by'                => (string) ($row->uedit_by ?? ''),
                'row_payload'             => $this->buildPoskonRowPayload($row),
            ];
        }

        return [
            'draw'            => $draw,
            'recordsTotal'    => $repoResult['recordsTotal'],
            'recordsFiltered' => $repoResult['recordsFiltered'],
            'items'           => $items,
            'data'            => $items,
        ];
    }

    /**
     * Ekspor data Posisi Konsumen ke PDF/XLSX dan catat riwayat export.
     *
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function exportPoskon(string $tipe, string $status, array $filters, int $userId): array
    {
        $idProyek  = $filters['id_proyek'] ?? null;
        $idCluster = $filters['id_cluster'] ?? null;
        $idJalan   = $filters['id_jalan'] ?? null;

        if ($idProyek === null || $idProyek === '' || (int) $idProyek <= 0) {
            return [
                'status'  => 'error',
                'message' => 'Tidak ada proyek yang dipilih',
            ];
        }

        $idCluster = ($idCluster !== null && $idCluster !== '') ? $idCluster : null;
        $idJalan   = ($idJalan !== null && $idJalan !== '') ? $idJalan : null;

        $normalizedTipe = strtolower(trim($tipe)) === 'pdf' ? 'pdf' : 'xlsx';
        $printService   = $this->getPrintService();

        if ($normalizedTipe === 'pdf') {
            $response = $printService->exportPoskonPdf($idProyek, $idCluster, $idJalan, $status);
        } else {
            $response = $printService->exportPoskonXlsx($idProyek, $idCluster, $idJalan, $status);
        }

        if (!empty($response['status']) && $response['status'] == true) {
            $data = [
                'id_proyek'  => $idProyek,
                'status'     => $status,
                'path'       => $response['path'],
                'filename'   => $response['filename'],
                'randomName' => $response['randomName'],
                'tipe_file'  => $normalizedTipe,
                'export_tgl' => date('Y-m-d H:i:s'),
                'export_by'  => $userId,
            ];
            $this->insertRiwayatExport($data);
        }

        return $response;
    }

    public function getDataTable($request, $status = null)
    {
        $rawStatusKavling = $request->getVar('status_kavling');
        $statusKavling    = $this->posisiKonsumenRepo->normalizeStatusKavling(
            ($rawStatusKavling !== null && is_scalar($rawStatusKavling)) ? (string) $rawStatusKavling : null
        );

        if ($statusKavling !== null) {
            $builder = $this->posisiKonsumenRepo->getBaseQuery(null);
            $this->posisiKonsumenRepo->applyStatusKavlingFilter($builder, $statusKavling);
        } else {
            $status = $status ?? "Booking";
            if (strtolower((string) $status) === 'aktif' || $status === '') {
                $status = 'Booking';
            } elseif (strtolower((string) $status) === 'akad') {
                $status = 'Akad';
            }

            $builder = $this->posisiKonsumenRepo->getBaseQuery($status);
        }

        $rowNumber = (int) $request->getVar('start');
        if ($request->getVar('id_proyek'))
            $builder->where('proyek.id_proyek', $request->getVar('id_proyek'));
        if ($request->getVar('id_cluster'))
            $builder->where('cluster.id_cluster', $request->getVar('id_cluster'));
        if ($request->getVar('id_jalan'))
            $builder->where('jalan.id_jalan', $request->getVar('id_jalan'));

        if ($request->getVar('sp3k') != "")
            $builder->where('mkdt.sp3k', $request->getVar('sp3k'));
        if ($request->getVar('wawancara') != "")
            $builder->where('mkdt.wawancara', $request->getVar('wawancara'));

        if ($statusKavling === null) {
            if ($request->getVar('akad') !== null && $request->getVar('akad') !== '')
                $builder->where('mkdt.akad', $request->getVar('akad'));
            if ($request->getVar('akad_indent') !== null && $request->getVar('akad_indent') !== '') {
                $builder->where('mkdt.akad_indent', $request->getVar('akad_indent'));
                if ((int) $request->getVar('akad_indent') === 1 && ($request->getVar('akad') === null || $request->getVar('akad') === '')) {
                    $builder->where('mkdt.akad', 0);
                }
            }
        }

        $bulan = $request->getVar('bulan');
        $tahun = $request->getVar('tahun');
        if ($bulan != "" || $tahun != "") {
            $bulanField = $request->getVar('bulan_field');
            if ($bulanField === 'akad') {
                $col = 'mkdt.akad_tgl';
            } elseif ($bulanField === 'indent') {
                $col = 'mkdt.akad_indent_tgl';
            } else {
                $col = 'mkdt.booking_tgl';
            }
            if ($bulan != "")
                $builder->where("MONTH($col)", $bulan);
            if ($tahun != "")
                $builder->where("YEAR($col)", $tahun);
        }

        return DataTable::of($builder)
            ->setSearchableColumns([
                'kavling.id_kavling',
                'jalan.nama_jalan',
                'kavling.no_kavling',
                'hargajual.id_tipe',
                'konsumen.nama_konsumen',
                'konsumen.kode_referal',
                'konsumen.sales',
                'mkdt.booking_tgl',
                'mkdt.wawancara_tgl',
                'mkdt.akad_tgl',
                'mkdt.akad_indent_tgl',
                'mkdt.is_kpr',
                'list_bank.bank',
                'mkdt.keterangan',
                'mkdt.sp3k_tgl',
                'mkdt.sp3k_tgl_exp',
                'produksi.progres_bangunan',
                'produksi.lpa',
                'produksi.st_jalan',
                'legal.sertifikat_split_no_hgb',
                'legal.sertifikat_split_nib',
                'legal.pbg_no',
                'legal.pbb_pecah_nop',
                'mkdt.keterangan_status',
            ])
            ->edit('id_kavling', function ($v) use (&$rowNumber) {
                return ++$rowNumber;
            })
            ->edit('nama_jalan', function ($v) {
                $html = esc($v->nama_jalan);
                $isAkadIndent = (int) ($v->akad_indent ?? 0) === 1;
                $isAkadAjb    = (int) ($v->akad ?? 0) === 1;
                if ($isAkadIndent && !$isAkadAjb) {
                    $html .= '<div class="mt-25"><span class="badge badge-primary poskon-akad-indent">Akad Indent</span></div>';
                }
                return $html;
            })
            ->edit('nama_konsumen', function ($v) {
                return '<div>' . esc($v->nama_konsumen ?: '-') . '</div>'
                    . '<div class="mt-25 poskon-referral">' . $this->renderReferralQrHtml($v) . '</div>';
            })
            ->edit('booking_tgl', function ($value) {
                return $this->format_tgl($value->booking_tgl);
            })
            ->edit('wawancara_tgl', function ($value) {
                return $this->format_tgl($value->wawancara_tgl);
            })
            ->edit('akad_tgl', function ($value) {
                $hasAjb = !empty($value->akad_tgl) && $value->akad_tgl !== '0000-00-00';
                if ($hasAjb) {
                    $tglAjb = $this->format_tgl($value->akad_tgl);
                    return '<span class="badge badge-light-success text-nowrap">AJB: ' . esc($tglAjb) . '</span>';
                }

                $hasIndent = !empty($value->akad_indent_tgl) && $value->akad_indent_tgl !== '0000-00-00';
                if ($hasIndent) {
                    $tglIndent = $this->format_tgl($value->akad_indent_tgl);
                    return '<span class="badge badge-light-primary text-nowrap">Indent: ' . esc($tglIndent) . '</span>';
                }

                return '-';
            })
            ->edit('sp3k_tgl', function ($value) {
                return $this->format_tgl($value->sp3k_tgl);
            })
            ->edit('sp3k_tgl_exp', function ($value) {
                return $this->format_tgl($value->sp3k_tgl_exp);
            })
            ->edit('progres_bangunan', function ($v) {
                $percentage = (int) ($v->progres_bangunan ?? 0);
                return $this->renderPercentageLabel($percentage . '%', $percentage, true);
            })
            ->edit('is_kpr', function ($value) {
                return $this->is_active($value->is_kpr, 'KPR', 'TUNAI');
            })
            ->edit('lpa', function ($v) {
                if ($v->lpa == 1) {
                    return '<i class="fa fa-solid fa-check"></i><br>';
                }
                return '';
            })
            ->edit('st_listrik', function ($v) {
                if ($v->st_listrik == 1) {
                    return '<i class="fa fa-solid fa-check"></i><br>';
                }
                return '';
            })
            ->edit('tunai', function ($v) {
                if ($v->is_kpr == 1) {
                    return '-';
                }

                return $this->renderProgress(
                    (float) $v->um + (float) $v->adm + (float) $v->bb,
                    (float) $v->total_um + (float) $v->total_adm + (float) $v->total_bb
                );
            })
            ->edit('um', function ($v) {
                if ($v->is_kpr == 0) {
                    return '-';
                }
                return $this->renderProgress((float) $v->um, (float) $v->total_um, true);
            })
            ->edit('adm', function ($v) {
                if ($v->is_kpr == 0) {
                    return '-';
                }
                return $this->renderProgress((float) $v->adm, (float) $v->total_adm, true);
            })
            ->edit('bb', function ($v) {
                if ($v->is_kpr == 0) {
                    return '-';
                }
                return $this->renderProgress((float) $v->bb, (float) $v->total_bb, true);
            })
            ->edit('action', function ($value) {
                return $this->renderPoskonActionHtml($value);
            })
            ->hide('kode_referal')
            ->edit('keterangan_status', function ($v) {
                return $v->keterangan_status ?: '-';
            })
            ->toJson();
    }
    function getDataTablesBatal($request)
    {
        $status = "Batal";
        $builder = $this->posisiKonsumenRepo->getQueryBatal();
        if ($request->getVar('id_proyek'))
            $builder->where('proyek.id_proyek', $request->getVar('id_proyek'));
        if ($request->getVar('id_cluster'))
            $builder->where('cluster.id_cluster', $request->getVar('id_cluster'));
        if ($request->getVar('id_jalan'))
            $builder->where('jalan.id_jalan', $request->getVar('id_jalan'));

        if ($request->getVar('periode_booking')) {
            $dates = explode(' to ', $request->getVar('periode_booking'));
            if (count($dates) == 2) {
                $builder->where('mkdt.booking_tgl >=', $dates[0]);
                $builder->where('mkdt.booking_tgl <=', $dates[1]);
            } else if (count($dates) == 1 && $dates[0] != '') {
                $builder->where('mkdt.booking_tgl', $dates[0]);
            }
        }

        if ($request->getVar('periode_batal')) {
            $dates = explode(' to ', $request->getVar('periode_batal'));
            if (count($dates) == 2) {
                $builder->where('mkdt.mkdt_batal_tgl >=', $dates[0]);
                $builder->where('mkdt.mkdt_batal_tgl <=', $dates[1]);
            } else if (count($dates) == 1 && $dates[0] != '') {
                $builder->where('mkdt.mkdt_batal_tgl', $dates[0]);
            }
        }

        return DataTable::of($builder)
            ->setSearchableColumns([
                'jalan.nama_jalan',
                'kavling.no_kavling',
                'tipe.tipe_rumah',
                'mkdt.keterangan_batal',
                'konsumen.nama_konsumen',
                'mkdt.booking_tgl',
                'mkdt.is_kpr',
            ])
            ->addNumbering('no')
            ->edit('booking_tgl', function ($value) {
                return $this->format_tgl($value->booking_tgl);
            })
            ->edit('is_kpr', function ($value) {
                return $this->is_active($value->is_kpr, 'KPR', 'TUNAI');
            })
            ->edit('keterangan_batal', function ($value) {
                return $value->keterangan_batal;
            })
            ->edit('tanggal_batal', function ($value) {
                $tanggal_batal = $this->format_tgl($value->mkdt_batal_tgl);
                $oleh = $value->nama_pembatal ?? '-';
                return $tanggal_batal . "<br><span class='text-muted'>Oleh: " . $oleh . "</span>";
            })
            ->edit('perlu_refund', function ($value) {
                if ((int) ($value->perlu_refund ?? 0) === 1) {
                    return '<span class="badge badge-warning">Perlu Refund</span>';
                }
                return '<span class="badge badge-secondary">Tidak Perlu Refund</span>';
            })
            ->edit('total_tagihan', function ($v) {
                return number_format($v->um + $v->adm + $v->bb);
            })
            ->edit('sudah_bayar', function ($v) {
                return number_format($v->total_um + $v->total_adm + $v->total_bb);
            })
            ->edit('sisa_tagihan', function ($v) {
                $tot = $v->um + $v->adm + $v->bb;
                $sb = $v->total_um + $v->total_adm + $v->total_bb;
                return number_format($tot - $sb);
            })
            ->edit('action', function ($value) {
                return $this->renderPoskonActionHtml($value);
            })
            ->toJson();
    }

    function getRiwayatExport($id_proyek, $status)
    {
        $rows = $this->posisiKonsumenRepo->getRiwayatExport($id_proyek, $status);

        foreach ($rows as $row) {
            $randomName = $row->randomName ?? $row->randomname ?? '';
            if ($randomName === '' || empty($row->path)) {
                continue;
            }

            $logicalPath = rtrim((string) $row->path, '/') . '/' . $randomName;

            try {
                $row->download_url = $this->fileAccessService->pathUrl('poskon_export', $logicalPath, true);
                $row->access_url = $this->fileAccessService->pathUrl('poskon_export', $logicalPath, false);
            } catch (\Throwable $e) {
                $row->download_url = '';
                $row->access_url = '';
            }
        }

        return $rows;
    }
    function insertRiwayatExport($data)
    {
        return $this->posisiKonsumenRepo->insertRiwayatExport($data);
    }
    protected function num($d)
    {
        $d = str_replace(',', "", $d);
        return $d;
    }
    function format_tgl($tgl)
    {
        if ($tgl == "" || $tgl == "0000-00-00" || $tgl == null)
            return "-";
        return date_format(date_create($tgl), "d-M-Y");
    }
    function is_active($id, $texts, $textf)
    {
        $r = '<span class="btn btn-primary btn-sm" text-capitalized="">' . $textf . '</span>';
        if ($id == "1")
            $r = '<span class="btn btn-success btn-sm" text-capitalized="">' . $texts . '</span>';
        return $r;
    }
}

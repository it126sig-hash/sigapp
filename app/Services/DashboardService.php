<?php

namespace App\Services;

use Config\Database;

class DashboardService
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    /**
     * Resolves user role context for dashboard viewing and role-based guards.
     */
    public function getRoleContext(): array
    {
        $userId = (int) (function_exists('user_id') ? (user_id() ?? 0) : 0);
        $user = function_exists('user') ? user() : null;
        $roles = ($user && method_exists($user, 'getRoles')) ? $user->getRoles() : [];

        $roleKeys = is_array($roles) ? array_keys($roles) : [];
        $groupIds = array_values(array_map('intval', $roleKeys));

        // can_view_finance: Admin (1), Keuangan (3), MKDT (4), Sales (8), Direksi (9)
        $canViewFinance = !empty(array_intersect([1, 3, 4, 8, 9], $groupIds));

        // can_switch_department: Admin (1), Direksi (9)
        $canSwitchDept = !empty(array_intersect([1, 9], $groupIds));

        // Determine default department tab
        if (!empty(array_intersect([1, 9], $groupIds))) {
            $defaultTab = 'mkdt_sales';
        } elseif (!empty(array_intersect([3, 10], $groupIds))) {
            $defaultTab = 'keuangan';
        } elseif (!empty(array_intersect([4, 8], $groupIds))) {
            $defaultTab = 'mkdt_sales';
        } elseif (!empty(array_intersect([7], $groupIds))) {
            $defaultTab = 'produksi';
        } elseif (!empty(array_intersect([5], $groupIds))) {
            $defaultTab = 'legal';
        } elseif (!empty(array_intersect([6], $groupIds))) {
            $defaultTab = 'planning';
        } else {
            $defaultTab = 'operasional';
        }

        // Determine visible tabs
        if ($canSwitchDept) {
            $visibleTabs = ['mkdt_sales', 'keuangan', 'produksi', 'legal', 'planning', 'operasional'];
        } else {
            $visibleTabs = [];
            if (!empty(array_intersect([3, 10], $groupIds))) {
                $visibleTabs[] = 'keuangan';
            }
            if (!empty(array_intersect([4, 8], $groupIds))) {
                $visibleTabs[] = 'mkdt_sales';
            }
            if (!empty(array_intersect([7], $groupIds))) {
                $visibleTabs[] = 'produksi';
            }
            if (!empty(array_intersect([5], $groupIds))) {
                $visibleTabs[] = 'legal';
            }
            if (!empty(array_intersect([6], $groupIds))) {
                $visibleTabs[] = 'planning';
            }

            if (empty($visibleTabs) || !in_array($defaultTab, $visibleTabs, true)) {
                $visibleTabs[] = $defaultTab;
            }
            $visibleTabs = array_values(array_unique($visibleTabs));
        }

        return [
            'user_id' => $userId,
            'group_ids' => $groupIds,
            'can_view_finance' => $canViewFinance,
            'can_switch_department' => $canSwitchDept,
            'default_tab' => $defaultTab,
            'visible_tabs' => $visibleTabs,
        ];
    }

    /**
     * Gathers cross-department operational metrics for the selected project and date range.
     */
    public function getDepartmentMetrics(int $idProyek, string $sdate, string $edate): array
    {
        return [
            'mkdt_sales' => $this->getMkdtSalesMetrics($idProyek, $sdate, $edate),
            'produksi'   => $this->getProduksiMetrics($idProyek, $sdate, $edate),
            'legal'      => $this->getLegalMetrics($idProyek, $sdate, $edate),
            'planning'   => $this->getPlanningMetrics($idProyek, $sdate, $edate),
        ];
    }

    /**
     * Gathers actionable queue items tailored to user role and project.
     */
    public function getActionQueue(int $idProyek, array $roleContext): array
    {
        $userId = (int) ($roleContext['user_id'] ?? 0);
        $groupIds = $roleContext['group_ids'] ?? [];
        $canViewFinance = !empty($roleContext['can_view_finance']);
        $queue = [];

        // 1. BPB Menunggu TTD user
        if ($this->db->tableExists('bpb_requests') && $userId > 0) {
            $bpbTtdCount = $this->db->table('bpb_requests')
                ->groupStart()
                    ->groupStart()
                        ->where('status', 'waiting_cc')
                        ->where('cc_user_id', $userId)
                    ->groupEnd()
                    ->orGroupStart()
                        ->where('status', 'waiting_approver')
                        ->where('approver_user_id', $userId)
                    ->groupEnd()
                ->groupEnd()
                ->countAllResults();

            if ($bpbTtdCount > 0) {
                $queue[] = [
                    'id'          => 'bpb_ttd',
                    'title'       => 'BPB Menunggu Tanda Tangan',
                    'count'       => $bpbTtdCount,
                    'severity'    => 'danger',
                    'description' => 'Permohonan BPB memerlukan tanda tangan elektronik Anda',
                    'url'         => $this->buildUrl('bpb'),
                ];
            }
        }

        // 2. BPB umum (Admin / Bagian Umum)
        if ($this->db->tableExists('bpb_requests') && !empty(array_intersect([1, 2], $groupIds))) {
            $bpbUmumCount = $this->db->table('bpb_requests')
                ->whereIn('status', ['approved', 'processed', 'pending'])
                ->countAllResults();

            if ($bpbUmumCount > 0) {
                $queue[] = [
                    'id'          => 'bpb_umum',
                    'title'       => 'BPB Siap Diproses Bagian Umum',
                    'count'       => $bpbUmumCount,
                    'severity'    => 'warning',
                    'description' => 'BPB disetujui yang menunggu pencairan atau verifikasi belanja',
                    'url'         => $this->buildUrl('bpb'),
                ];
            }
        }

        // 3. MGM (Member Get Member)
        if ($this->db->tableExists('referral_bonuses') && $this->db->tableExists('referrals')) {
            if ($canViewFinance) {
                $mgmKeuBuilder = $this->db->table('referral_bonuses rb')
                    ->join('referrals r', 'r.id = rb.id_referral')
                    ->where('rb.status', 'diajukan_keuangan');
                if ($idProyek > 0) {
                    $mgmKeuBuilder->where('r.id_proyek', $idProyek);
                }
                $mgmKeuCount = $mgmKeuBuilder->countAllResults();

                if ($mgmKeuCount > 0) {
                    $queue[] = [
                        'id'          => 'mgm_keuangan',
                        'title'       => 'Bonus MGM Menunggu Pencairan',
                        'count'       => $mgmKeuCount,
                        'severity'    => 'warning',
                        'description' => 'Pengajuan bonus referral MGM menunggu verifikasi & pencairan keuangan',
                        'url'         => $this->buildUrl('member-get-member'),
                    ];
                }
            }

            if (!empty(array_intersect([1, 4, 8, 9], $groupIds))) {
                $mgmSalesBuilder = $this->db->table('referral_bonuses rb')
                    ->join('referrals r', 'r.id = rb.id_referral')
                    ->whereIn('rb.status', ['eligible', 'dikonfirmasi']);
                if ($idProyek > 0) {
                    $mgmSalesBuilder->where('r.id_proyek', $idProyek);
                }
                $mgmSalesCount = $mgmSalesBuilder->countAllResults();

                if ($mgmSalesCount > 0) {
                    $queue[] = [
                        'id'          => 'mgm_sales',
                        'title'       => 'Bonus MGM Perlu Diajukan',
                        'count'       => $mgmSalesCount,
                        'severity'    => 'info',
                        'description' => 'Bonus referral MGM memenuhi syarat/dikonfirmasi siap diajukan ke keuangan',
                        'url'         => $this->buildUrl('member-get-member'),
                    ];
                }
            }
        }

        // 4. Fee Sales
        if ($this->db->tableExists('fee_sales_pengajuan')) {
            $feeSalesBuilder = $this->db->table('fee_sales_pengajuan')
                ->whereIn('status', ['diajukan', 'ditolak']);
            if ($idProyek > 0) {
                $feeSalesBuilder->where('id_proyek', $idProyek);
            }
            $feeSalesCount = $feeSalesBuilder->countAllResults();

            if ($feeSalesCount > 0) {
                $queue[] = [
                    'id'          => 'fee_sales',
                    'title'       => 'Pengajuan Fee Sales',
                    'count'       => $feeSalesCount,
                    'severity'    => 'warning',
                    'description' => 'Pengajuan fee sales berstatus diajukan atau perlu revisi (ditolak)',
                    'url'         => $this->buildUrl('fee-sales'),
                ];
            }
        }

        // 5. Request Kavling (Admin, Planning, Direksi)
        if ($this->db->tableExists('kavling_request') && !empty(array_intersect([1, 6, 9], $groupIds))) {
            $kavReqBuilder = $this->db->table('kavling_request')
                ->where('status', 'pending');
            if ($idProyek > 0) {
                $kavReqBuilder->where('id_proyek', $idProyek);
            }
            $kavReqCount = $kavReqBuilder->countAllResults();

            if ($kavReqCount > 0) {
                $queue[] = [
                    'id'          => 'request_kavling',
                    'title'       => 'Permohonan Kavling Menunggu Review',
                    'count'       => $kavReqCount,
                    'severity'    => 'warning',
                    'description' => 'Permohonan perubahan/tambah kavling baru berstatus pending',
                    'url'         => $this->buildUrl('siteplan/view'),
                ];
            }
        }

        return $queue;
    }

    /**
     * Builds role-aware alert notifications with clickable URLs and financial guards.
     */
    public function buildRoleAwareAlerts(
        array $alertCounts,
        array $production,
        array $deptMetrics,
        array $finance,
        array $roleContext
    ): array {
        $canViewFinance = !empty($roleContext['can_view_finance']);
        $count = static fn(string $k): int => (int) ($alertCounts[$k] ?? 0);
        $alerts = [];

        // Financial alerts (hidden if user cannot view finance)
        if ($canViewFinance) {
            $alerts[] = [
                'label'       => 'Tagihan lewat jatuh tempo',
                'value'       => $count('tagihan_overdue'),
                'type'        => 'danger',
                'description' => 'Perlu ditagih atau diverifikasi pembayarannya',
                'url'         => $this->buildUrl('tagihan/list'),
            ];

            $alerts[] = [
                'label'       => 'Tagihan jatuh tempo 7 hari',
                'value'       => $count('tagihan_due'),
                'type'        => 'warning',
                'description' => 'Perlu follow up sebelum lewat tempo',
                'url'         => $this->buildUrl('tagihan/list'),
            ];

            $alerts[] = [
                'label'       => 'Cashout subkon jatuh tempo',
                'value'       => $count('cashout_subkon'),
                'type'        => 'warning',
                'description' => 'Termin subkon belum dibayar',
                'url'         => $this->buildUrl('cashout-subkon'),
            ];
        }

        // Operational & Production alerts
        $alerts[] = [
            'label'       => 'Bangunan telat',
            'value'       => $count('pembangunan_telat'),
            'type'        => 'danger',
            'description' => 'Progres belum selesai melewati rencana selesai',
            'url'         => $this->buildUrl('produksi'),
        ];

        $alerts[] = [
            'label'       => 'Komplain aktif',
            'value'       => (int) ($production['komplain_aktif'] ?? 0),
            'type'        => 'info',
            'description' => 'Komplain sales/produksi yang masih perlu dipantau',
            'url'         => $this->buildUrl('komplain'),
        ];

        // MKDT & Sales alerts
        $alerts[] = [
            'label'       => 'SP3K expire 7 hari',
            'value'       => $count('sp3k_expire'),
            'type'        => 'warning',
            'description' => 'Masa berlaku SP3K segera habis dalam 7 hari',
            'url'         => $this->buildUrl('transaksi'),
        ];

        $alerts[] = [
            'label'       => 'Rencana akad 7 hari',
            'value'       => $count('rencana_akad'),
            'type'        => 'info',
            'description' => 'Konsumen siap akad dalam 7 hari ke depan',
            'url'         => $this->buildUrl('transaksi'),
        ];

        // Legal alerts
        if (isset($deptMetrics['legal']['bphtb_jatuh_tempo'])) {
            $alerts[] = [
                'label'       => 'BPHTB jatuh tempo',
                'value'       => (int) $deptMetrics['legal']['bphtb_jatuh_tempo'],
                'type'        => 'warning',
                'description' => 'Validasi/pembayaran BPHTB mendekati batas waktu',
                'url'         => $this->buildUrl('legalitas'),
            ];
        }

        // Planning alerts
        if (isset($deptMetrics['planning']['kavling_request']['pending_count'])) {
            $alerts[] = [
                'label'       => 'Request kavling pending',
                'value'       => (int) $deptMetrics['planning']['kavling_request']['pending_count'],
                'type'        => 'warning',
                'description' => 'Permohonan perubahan kavling menunggu persetujuan',
                'url'         => $this->buildUrl('siteplan/view'),
            ];
        }

        return $alerts;
    }

    /**
     * Gathers MKDT and Sales department metrics.
     */
    protected function getMkdtSalesMetrics(int $idProyek, string $sdate, string $edate): array
    {
        // NUP Aktif query
        $nupBuilder = $this->db->table('mkdt')
            ->where('status_mkdt', 'NUP')
            ->where('COALESCE(is_batal, 0)', 0, false);

        if ($idProyek > 0) {
            $nupBuilder->groupStart()
                ->where('mkdt.id_proyek', $idProyek)
                ->orWhere("mkdt.id_kavling IN (
                    SELECT k.id_kavling FROM kavling k
                    JOIN jalan j ON j.id_jalan = k.id_jalan
                    JOIN cluster c ON c.id_cluster = j.id_cluster
                    WHERE c.id_proyek = {$idProyek}
                )", null, false)
                ->groupEnd();
        }
        $nupAktif = (int) $nupBuilder->countAllResults();

        // MKDT cluster join query
        $builder = $this->db->table('mkdt')
            ->select("
                COUNT(CASE WHEN mkdt.status_mkdt = 'Booking' AND COALESCE(mkdt.is_batal, 0) = 0 THEN 1 END) AS booking_aktif,
                COUNT(CASE WHEN mkdt.status_mkdt = 'Booking' AND COALESCE(mkdt.is_batal, 0) = 0 AND mkdt.wawancara = 1 THEN 1 END) AS sudah_wawancara,
                COUNT(CASE WHEN mkdt.status_mkdt = 'Booking' AND COALESCE(mkdt.is_batal, 0) = 0 AND mkdt.sp3k = 1 THEN 1 END) AS sudah_sp3k,
                COUNT(CASE WHEN kavling.perintah_bangun = 1 AND COALESCE(mkdt.is_batal, 0) = 0 THEN 1 END) AS sudah_perintah_bangun,
                COUNT(CASE WHEN mkdt.status_mkdt = 'Akad' THEN 1 END) AS sudah_akad,
                COUNT(CASE WHEN COALESCE(mkdt.is_subsidi, 0) = 1 AND mkdt.status_mkdt IN ('Booking', 'Akad') AND COALESCE(mkdt.is_batal, 0) = 0 THEN 1 END) AS subsidi_count,
                COUNT(CASE WHEN COALESCE(mkdt.is_subsidi, 0) = 0 AND mkdt.status_mkdt IN ('Booking', 'Akad') AND COALESCE(mkdt.is_batal, 0) = 0 THEN 1 END) AS komersil_count,
                COUNT(CASE WHEN COALESCE(mkdt.is_kpr, 0) = 1 AND mkdt.status_mkdt IN ('Booking', 'Akad') AND COALESCE(mkdt.is_batal, 0) = 0 THEN 1 END) AS kpr_count,
                COUNT(CASE WHEN COALESCE(mkdt.is_kpr, 0) = 0 AND mkdt.status_mkdt IN ('Booking', 'Akad') AND COALESCE(mkdt.is_batal, 0) = 0 THEN 1 END) AS tunai_count
            ", false)
            ->join('kavling', 'kavling.id_kavling = mkdt.id_kavling')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster');

        if ($idProyek > 0) {
            $builder->where('cluster.id_proyek', $idProyek);
        }
        $row = $builder->get()->getRow();

        return [
            'nup_aktif'             => $nupAktif,
            'booking_aktif'         => (int) ($row->booking_aktif ?? 0),
            'sudah_wawancara'       => (int) ($row->sudah_wawancara ?? 0),
            'sudah_sp3k'            => (int) ($row->sudah_sp3k ?? 0),
            'sudah_perintah_bangun' => (int) ($row->sudah_perintah_bangun ?? 0),
            'sudah_akad'            => (int) ($row->sudah_akad ?? 0),
            'subsidi_count'         => (int) ($row->subsidi_count ?? 0),
            'komersil_count'        => (int) ($row->komersil_count ?? 0),
            'kpr_count'             => (int) ($row->kpr_count ?? 0),
            'tunai_count'           => (int) ($row->tunai_count ?? 0),
        ];
    }

    /**
     * Gathers Produksi department metrics.
     */
    protected function getProduksiMetrics(int $idProyek, string $sdate, string $edate): array
    {
        $hasSlfField = $this->db->fieldExists('slf_no', 'produksi');
        $slfCondition = $hasSlfField
            ? "TRIM(COALESCE(produksi.slf_no, '')) != ''"
            : "(SELECT COUNT(1) FROM list_slf ls WHERE FIND_IN_SET(kavling.id_kavling, ls.id_kavling) > 0 OR kavling.id_kavling = ls.id_kavling) > 0";

        $builder = $this->db->table('kavling')
            ->select("
                COUNT(CASE WHEN kavling.perintah_bangun = 1 AND (kavling.id_produksi IS NULL OR produksi.tanggal_pembangunan IS NULL OR produksi.tanggal_pembangunan <= '1000-01-01') AND COALESCE(produksi.progres_bangunan, 0) = 0 THEN 1 END) AS menunggu_bangun,
                COUNT(CASE WHEN produksi.id_produksi IS NOT NULL AND COALESCE(produksi.progres_bangunan, 0) = 0 THEN 1 END) AS progres_0,
                COUNT(CASE WHEN produksi.id_produksi IS NOT NULL AND produksi.progres_bangunan = 25 THEN 1 END) AS progres_25,
                COUNT(CASE WHEN produksi.id_produksi IS NOT NULL AND produksi.progres_bangunan = 50 THEN 1 END) AS progres_50,
                COUNT(CASE WHEN produksi.id_produksi IS NOT NULL AND produksi.progres_bangunan = 75 THEN 1 END) AS progres_75,
                COUNT(CASE WHEN produksi.id_produksi IS NOT NULL AND produksi.progres_bangunan = 100 THEN 1 END) AS progres_100,
                COUNT(CASE WHEN {$slfCondition} THEN 1 END) AS sudah_slf,
                COUNT(CASE WHEN (TRIM(COALESCE(produksi.listrik_pln, '')) NOT IN ('', '0')) OR (TRIM(COALESCE(produksi.listrik_disediakan_no, '')) != '') THEN 1 END) AS sudah_listrik,
                COUNT(CASE WHEN (TRIM(COALESCE(produksi.air_pdam_no, '')) NOT IN ('', '0')) OR COALESCE(produksi.st_air, 0) != 0 THEN 1 END) AS sudah_air
            ", false)
            ->join('produksi', 'produksi.id_produksi = kavling.id_produksi', 'left')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster');

        if ($idProyek > 0) {
            $builder->where('cluster.id_proyek', $idProyek);
        }
        $row = $builder->get()->getRow();

        return [
            'menunggu_bangun' => (int) ($row->menunggu_bangun ?? 0),
            'progres_0'       => (int) ($row->progres_0 ?? 0),
            'progres_25'      => (int) ($row->progres_25 ?? 0),
            'progres_50'      => (int) ($row->progres_50 ?? 0),
            'progres_75'      => (int) ($row->progres_75 ?? 0),
            'progres_100'     => (int) ($row->progres_100 ?? 0),
            'sudah_slf'       => (int) ($row->sudah_slf ?? 0),
            'sudah_listrik'   => (int) ($row->sudah_listrik ?? 0),
            'sudah_air'       => (int) ($row->sudah_air ?? 0),
        ];
    }

    /**
     * Gathers Legal department metrics.
     */
    protected function getLegalMetrics(int $idProyek, string $sdate, string $edate): array
    {
        $builder = $this->db->table('kavling')
            ->select("
                COUNT(kavling.id_kavling) AS total_unit_terjual,
                COUNT(CASE WHEN kavling.id_legal IS NULL OR legal.id_legal IS NULL THEN 1 END) AS belum_ada_legal,
                COUNT(CASE WHEN legal.sertifikat_is_split = 1 OR TRIM(COALESCE(legal.sertifikat_split_no_hgb, '')) != '' THEN 1 END) AS sudah_split_hgb,
                COUNT(CASE WHEN legal.sertifikat_is_balik_nama = 1 OR TRIM(COALESCE(legal.sertifikat_balik_nama, '')) != '' THEN 1 END) AS sudah_balik_nama,
                COUNT(CASE WHEN TRIM(COALESCE(legal.pbg_no, '')) != '' THEN 1 END) AS sudah_pbg,
                COUNT(CASE WHEN TRIM(COALESCE(legal.pbb_pecah_nop, '')) != '' THEN 1 END) AS sudah_pbb_pecah,
                COUNT(CASE WHEN TRIM(COALESCE(legal.ajb_no, '')) != '' OR COALESCE(mkdt.is_ajb, 0) = 1 OR UPPER(TRIM(COALESCE(mkdt.is_ajb, ''))) = 'AJB' THEN 1 END) AS sudah_ajb,
                COUNT(CASE WHEN legal.bphtb_tanggal_validasi > '1000-01-01' THEN 1 END) AS bphtb_valid,
                COUNT(CASE WHEN (legal.bphtb_tanggal_pembayaran IS NULL OR legal.bphtb_tanggal_pembayaran <= '1000-01-01')
                    AND (
                        (legal.bphtb_perpanjang_jatuh_tempo > '1000-01-01' AND legal.bphtb_perpanjang_jatuh_tempo <= DATE_ADD(CURDATE(), INTERVAL 7 DAY))
                        OR
                        ((legal.bphtb_perpanjang_jatuh_tempo IS NULL OR legal.bphtb_perpanjang_jatuh_tempo <= '1000-01-01') AND legal.bphtb_jatuh_tempo > '1000-01-01' AND legal.bphtb_jatuh_tempo <= DATE_ADD(CURDATE(), INTERVAL 7 DAY))
                    ) THEN 1 END) AS bphtb_jatuh_tempo
            ", false)
            ->join('mkdt', 'mkdt.id_kavling = kavling.id_kavling')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster')
            ->join('legal', 'legal.id_legal = kavling.id_legal', 'left')
            ->whereIn('mkdt.status_mkdt', ['Booking', 'Akad'])
            ->groupStart()
                ->where('mkdt.is_batal', 0)
                ->orWhere('mkdt.is_batal IS NULL', null, false)
            ->groupEnd();

        if ($idProyek > 0) {
            $builder->where('cluster.id_proyek', $idProyek);
        }
        $row = $builder->get()->getRow();

        return [
            'total_unit_terjual' => (int) ($row->total_unit_terjual ?? 0),
            'belum_ada_legal'    => (int) ($row->belum_ada_legal ?? 0),
            'sudah_split_hgb'    => (int) ($row->sudah_split_hgb ?? 0),
            'sudah_balik_nama'   => (int) ($row->sudah_balik_nama ?? 0),
            'sudah_pbg'          => (int) ($row->sudah_pbg ?? 0),
            'sudah_pbb_pecah'    => (int) ($row->sudah_pbb_pecah ?? 0),
            'sudah_ajb'          => (int) ($row->sudah_ajb ?? 0),
            'bphtb_valid'        => (int) ($row->bphtb_valid ?? 0),
            'bphtb_jatuh_tempo'  => (int) ($row->bphtb_jatuh_tempo ?? 0),
        ];
    }

    /**
     * Gathers Planning & Master Data metrics.
     */
    protected function getPlanningMetrics(int $idProyek, string $sdate, string $edate): array
    {
        // Kavling requests
        $reqBuilder = $this->db->table('kavling_request')
            ->select("
                COUNT(CASE WHEN status = 'pending' THEN 1 END) AS pending_count,
                COUNT(CASE WHEN status = 'approved' THEN 1 END) AS approved_count,
                COUNT(CASE WHEN status = 'rejected' THEN 1 END) AS rejected_count,
                COUNT(*) AS total_request
            ", false);

        if ($idProyek > 0) {
            $reqBuilder->where('id_proyek', $idProyek);
        }
        $reqRow = $reqBuilder->get()->getRow();

        // Master infrastruktur
        $clusterCount = $this->db->table('cluster')
            ->where('COALESCE(is_active, 1)', 1, false);
        if ($idProyek > 0) {
            $clusterCount->where('id_proyek', $idProyek);
        }
        $totalCluster = $clusterCount->countAllResults();

        $jalanCount = $this->db->table('jalan')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster');
        if ($idProyek > 0) {
            $jalanCount->where('cluster.id_proyek', $idProyek);
        }
        $totalJalan = $jalanCount->countAllResults();

        $tipeCount = $this->db->table('tipe');
        if ($idProyek > 0) {
            $tipeCount->where('id_proyek', $idProyek);
        }
        $totalTipe = $tipeCount->countAllResults();

        $kavlingMapBuilder = $this->db->table('kavling')
            ->select("
                COUNT(CASE WHEN TRIM(COALESCE(kavling.points, '')) != '' THEN 1 END) AS kavling_terpetakan,
                COUNT(CASE WHEN TRIM(COALESCE(kavling.points, '')) = '' THEN 1 END) AS kavling_belum_terpetakan
            ", false)
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster');

        if ($idProyek > 0) {
            $kavlingMapBuilder->where('cluster.id_proyek', $idProyek);
        }
        $kavlingMapRow = $kavlingMapBuilder->get()->getRow();

        return [
            'kavling_request' => [
                'pending_count'  => (int) ($reqRow->pending_count ?? 0),
                'approved_count' => (int) ($reqRow->approved_count ?? 0),
                'rejected_count' => (int) ($reqRow->rejected_count ?? 0),
                'total_request'  => (int) ($reqRow->total_request ?? 0),
            ],
            'infrastruktur'   => [
                'total_cluster'           => $totalCluster,
                'total_jalan'             => $totalJalan,
                'total_tipe'              => $totalTipe,
                'kavling_terpetakan'      => (int) ($kavlingMapRow->kavling_terpetakan ?? 0),
                'kavling_belum_terpetakan'=> (int) ($kavlingMapRow->kavling_belum_terpetakan ?? 0),
            ],
        ];
    }

    /**
     * Builds safe, clickable URLs.
     */
    protected function buildUrl(string $path): string
    {
        return function_exists('base_url') ? base_url($path) : '/' . ltrim($path, '/');
    }
}

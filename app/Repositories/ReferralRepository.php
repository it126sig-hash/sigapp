<?php

namespace App\Repositories;
use CodeIgniter\Model;

class ReferralRepository extends Model
{
    protected $table = 'referrals';
    protected $primaryKey = 'id';
    protected $returnType = 'object';

    public function getListMGM(int $idProyek, array $filters = []): array
    {
        $params = [$idProyek];
        $clusterSql = '';
        if (!empty($filters['id_cluster'])) {
            $clusterSql = " AND EXISTS (
                SELECT 1
                FROM mkdt mkf
                JOIN kavling kvf ON kvf.id_mkdt = mkf.id_mkdt
                JOIN jalan jlf ON jlf.id_jalan = kvf.id_jalan
                WHERE mkf.id_konsumen = r.id_konsumen_referrer
                  AND jlf.id_cluster = ?
            )";
            $params[] = (int) $filters['id_cluster'];
        }
        $filterSql = $this->mgmFilterSql($filters, $params, 'k');

        $sql = "SELECT
                r.id_konsumen_referrer,
                k.nama_konsumen AS referrer_nama,
                k.kode_referal,
                COUNT(DISTINCT r.id) AS jumlah_referal,
                COALESCE(kav.kavling_dimiliki, '-') AS kavling_dimiliki,
                COALESCE(SUM(CASE WHEN rb.status IS NULL OR rb.status <> 'batal' THEN rb.nominal_bonus ELSE 0 END), 0) AS total_penghasilan,
                COALESCE(SUM(CASE WHEN rb.status IN ('cair', 'selesai') OR rb.cair_keuangan_at IS NOT NULL THEN COALESCE(rb.nominal_cair_keuangan, rb.nominal_bonus) ELSE 0 END), 0) AS total_sudah_cair_keuangan,
                COALESCE(SUM(CASE WHEN rb.status = 'diajukan_keuangan' THEN COALESCE(rb.nominal_pengajuan_keuangan, rb.nominal_bonus) ELSE 0 END), 0) AS total_sedang_diajukan_keuangan,
                COALESCE(SUM(CASE WHEN rb.paid_by_promosi = 1 OR rb.status IN ('dibayar_promosi', 'selesai') THEN rb.nominal_bonus ELSE 0 END), 0) AS total_sudah_cair_promosi,
                GREATEST(
                    COALESCE(SUM(CASE WHEN rb.status IS NULL OR rb.status <> 'batal' THEN rb.nominal_bonus ELSE 0 END), 0)
                    - COALESCE(SUM(CASE WHEN rb.status IN ('cair', 'selesai') OR rb.cair_keuangan_at IS NOT NULL THEN COALESCE(rb.nominal_cair_keuangan, rb.nominal_bonus) ELSE 0 END), 0),
                    0
                ) AS sisa_belum_cair
            FROM referrals r
            JOIN konsumen k ON k.id_konsumen = r.id_konsumen_referrer
            JOIN mkdt mk ON mk.id_mkdt = r.id_mkdt_referred
            JOIN referral_bonus_stages st ON st.id_proyek = r.id_proyek AND st.is_active = 1
            LEFT JOIN referral_bonuses rb ON rb.id_referral = r.id AND rb.id_stage = st.id
            LEFT JOIN (
                SELECT
                    mk_ref.id_konsumen,
                    GROUP_CONCAT(DISTINCT CONCAT(jl_ref.nama_jalan, ', No. ', kv_ref.no_kavling) ORDER BY jl_ref.nama_jalan, kv_ref.no_kavling SEPARATOR ' | ') AS kavling_dimiliki
                FROM mkdt mk_ref
                JOIN kavling kv_ref ON kv_ref.id_mkdt = mk_ref.id_mkdt
                JOIN jalan jl_ref ON jl_ref.id_jalan = kv_ref.id_jalan
                WHERE mk_ref.status_mkdt NOT IN ('Batal')
                GROUP BY mk_ref.id_konsumen
            ) kav ON kav.id_konsumen = r.id_konsumen_referrer
            WHERE r.id_proyek = ?
              AND COALESCE(r.status, 'active') = 'active'
              {$clusterSql}
              {$filterSql}
            GROUP BY r.id_konsumen_referrer, k.nama_konsumen, k.kode_referal, kav.kavling_dimiliki
            ORDER BY jumlah_referal DESC";

        return $this->db->query($sql, $params)->getResultArray();
    }

    public function getSubRowsByReferrer(int $idKonsumenReferrer, int $idProyek, array $filters = []): array
    {
        $params = [$idKonsumenReferrer, $idProyek];
        $filterSql = $this->mgmFilterSql($filters, $params, 'kr');

        $sql = "SELECT 
                r.id as id_referral,
                r.id_mkdt_referred,
                r.status as referral_status,
                k.nama_konsumen as referred_nama,
                CONCAT(jl.nama_jalan, ', No. ', kv.no_kavling) as referred_kavling,
                mk.status_mkdt,
                mk.id_mkdt,
                mk.booking_tgl,
                mk.akad_tgl,
                
                rb.id as id_bonus,
                st.nama_tahapan,
                st.id as id_stage,
                COALESCE(rb.nominal_bonus, st.nominal_default) as nominal_bonus,
                rb.status as bonus_status,
                CASE 
                    WHEN rb.status IN ('diajukan_keuangan') THEN 'Menunggu Pencairan'
                    WHEN rb.status IN ('dibayar_promosi') THEN 'Cair Dari Promosi'
                    WHEN rb.status IN ('dikonfirmasi') THEN 'Belum diajukan'
                    WHEN rb.status IN ('cair', 'selesai') THEN 'Cair Dari Keuangan'
                    WHEN rb.status = 'batal' THEN 'Batal'
                    ELSE 'Belum diajukan'
                END as bonus_status_badge,
                rb.keterangan as bonus_keterangan,
                rb.paid_by_promosi,
                rb.cair_keuangan_at,
                rb.nominal_pengajuan_keuangan,
                rb.tanggal_spp,
                rb.bukti_pengajuan_keuangan,
                rb.submitted_keuangan_at,
                rb.submitted_keuangan_by,
                submitted_user.username as submitted_keuangan_username,
                rb.nominal_cair_keuangan,
                rb.tanggal_cair_keuangan,
                rb.bukti_transfer_ke_promosi,
                rb.cair_keuangan_by,
                cair_user.username as cair_keuangan_username,
                rb.bukti_bayar_promosi,
                rb.paid_promosi_tanggal,
                rb.paid_promosi_by,
                paid_user.username as paid_promosi_username,
                rb.paid_promosi_penerima_nama,
                rb.paid_promosi_no_rekening,
                rb.paid_promosi_bank,
                rb.cair_keuangan_penerima_nama,
                rb.cair_keuangan_no_rekening,
                rb.cair_keuangan_bank,
                rb.created_at,
                rb.eligible_at,
                rb.confirmed_at,
                rb.paid_promosi_at
            FROM referrals r
            JOIN mkdt mk ON mk.id_mkdt = r.id_mkdt_referred
            JOIN konsumen kr ON kr.id_konsumen = r.id_konsumen_referrer
            JOIN konsumen k ON k.id_konsumen = mk.id_konsumen
            LEFT JOIN kavling kv ON kv.id_mkdt = mk.id_mkdt
            LEFT JOIN jalan jl ON jl.id_jalan = kv.id_jalan
            JOIN referral_bonus_stages st ON st.id_proyek = r.id_proyek AND st.is_active = 1
            LEFT JOIN referral_bonuses rb ON rb.id_referral = r.id AND rb.id_stage = st.id
            LEFT JOIN users submitted_user ON submitted_user.id = rb.submitted_keuangan_by
            LEFT JOIN users cair_user ON cair_user.id = rb.cair_keuangan_by
            LEFT JOIN users paid_user ON paid_user.id = rb.paid_promosi_by
            WHERE r.id_konsumen_referrer = ? AND r.id_proyek = ?
              AND COALESCE(r.status, 'active') = 'active'
              AND (
                  LOWER(mk.status_mkdt) = 'akad'
                  OR (
                      LOWER(st.nama_tahapan) NOT LIKE '%akad%'
                      AND LOWER(st.trigger_status_mkdt) <> 'akad'
                  )
              )
              {$filterSql}
            ORDER BY r.id DESC, st.urutan ASC";

        $rows = $this->db->query($sql, $params)->getResultArray();
        $bonusIds = array_values(array_filter(array_map(static fn ($row) => (int) ($row['id_bonus'] ?? 0), $rows)));
        $referralIds = array_values(array_unique(array_filter(array_map(static fn ($row) => (int) ($row['id_referral'] ?? 0), $rows))));
        if ((empty($bonusIds) && empty($referralIds)) || !$this->db->tableExists('referral_bonus_histories')) {
            return $rows;
        }

        $historyBuilder = $this->db->table('referral_bonus_histories h')
            ->select('h.*, u.username')
            ->join('users u', 'u.id = h.add_by', 'left');

        $historyBuilder->groupStart();
        if (!empty($bonusIds)) {
            $historyBuilder->whereIn('h.id_bonus', $bonusIds);
        }
        if (!empty($referralIds)) {
            if (!empty($bonusIds)) {
                $historyBuilder->orGroupStart();
            } else {
                $historyBuilder->groupStart();
            }
            $historyBuilder->whereIn('h.id_referral', $referralIds)
                ->where('h.id_bonus IS NULL', null, false)
                ->groupEnd();
        }
        $historyBuilder->groupEnd();

        $histories = $historyBuilder
            ->orderBy('h.created_at', 'desc')
            ->orderBy('h.id', 'desc')
            ->get()
            ->getResultArray();

        $historyMap = [];
        $referralHistoryMap = [];
        foreach ($histories as $history) {
            if (!empty($history['id_bonus'])) {
                $historyMap[(int) $history['id_bonus']][] = $history;
                continue;
            }

            $referralHistoryMap[(int) $history['id_referral']][] = $history;
        }

        foreach ($rows as &$row) {
            $row['histories'] = array_merge(
                $historyMap[(int) ($row['id_bonus'] ?? 0)] ?? [],
                $referralHistoryMap[(int) ($row['id_referral'] ?? 0)] ?? []
            );

            usort($row['histories'], static function ($left, $right) {
                $leftTime = strtotime((string) ($left['created_at'] ?? '')) ?: 0;
                $rightTime = strtotime((string) ($right['created_at'] ?? '')) ?: 0;
                if ($leftTime === $rightTime) {
                    return (int) ($right['id'] ?? 0) <=> (int) ($left['id'] ?? 0);
                }

                return $rightTime <=> $leftTime;
            });
        }

        return $rows;
    }

    public function findByKodeReferal(string $kode, int $idProyek): ?object
    {
        // Cari referrer yang punya kode ini DAN punya kavling di proyek ini
        $sql = "SELECT k.* 
                FROM konsumen k
                JOIN mkdt mk ON mk.id_konsumen = k.id_konsumen
                JOIN kavling kv ON kv.id_mkdt = mk.id_mkdt
                JOIN jalan jl ON jl.id_jalan = kv.id_jalan
                JOIN cluster cl ON cl.id_cluster = jl.id_cluster
                WHERE k.kode_referal = ? 
                  AND cl.id_proyek = ?
                  AND mk.status_mkdt NOT IN ('Batal')
                LIMIT 1";
        
        return $this->db->query($sql, [$kode, $idProyek])->getRow();
    }

    public function getReferralByMkdt(int $idMkdt): ?object
    {
        return $this->where('id_mkdt_referred', $idMkdt)->first();
    }

    public function getMkdtReferralContext(int $idMkdt): ?object
    {
        return $this->db->table('mkdt')
            ->select('id_mkdt, id_konsumen, status_mkdt')
            ->where('id_mkdt', $idMkdt)
            ->get()
            ->getRow();
    }

    public function getBonusStagesByProyek(int $idProyek): array
    {
        return $this->db->table('referral_bonus_stages')
            ->where('id_proyek', $idProyek)
            ->where('is_active', 1)
            ->orderBy('urutan', 'asc')
            ->get()->getResultArray();
    }

    public function getMgmNotificationContextByBonus(int $idBonus): ?object
    {
        return $this->db->table('referral_bonuses rb')
            ->select([
                'rb.id AS id_bonus',
                'rb.status AS bonus_status',
                'rb.nominal_bonus',
                'rb.nominal_pengajuan_keuangan',
                'rb.nominal_cair_keuangan',
                'rb.submitted_keuangan_by',
                'rb.cair_keuangan_by',
                'rb.cair_keuangan_penerima_nama',
                'rb.cair_keuangan_no_rekening',
                'rb.cair_keuangan_bank',
                'r.id AS id_referral',
                'r.id_proyek',
                'mk.id_mkdt',
                'mk.status_mkdt',
                'mk.id_konsumen',
                'kv.id_kavling',
                'k_referred.nama_konsumen AS referred_nama',
                'k_referrer.nama_konsumen AS referrer_nama',
                'k_referrer.kode_referal',
                'st.nama_tahapan',
                'st.trigger_status_mkdt',
                'submitted_user.username AS submitted_keuangan_username',
                'cair_user.username AS cair_keuangan_username',
            ])
            ->select("CONCAT(COALESCE(jl.nama_jalan, '-'), ', No. ', COALESCE(kv.no_kavling, '-')) AS referred_kavling", false)
            ->join('referrals r', 'r.id = rb.id_referral')
            ->join('mkdt mk', 'mk.id_mkdt = r.id_mkdt_referred')
            ->join('konsumen k_referred', 'k_referred.id_konsumen = mk.id_konsumen', 'left')
            ->join('konsumen k_referrer', 'k_referrer.id_konsumen = r.id_konsumen_referrer', 'left')
            ->join('referral_bonus_stages st', 'st.id = rb.id_stage', 'left')
            ->join('kavling kv', 'kv.id_mkdt = mk.id_mkdt', 'left')
            ->join('jalan jl', 'jl.id_jalan = kv.id_jalan', 'left')
            ->join('users submitted_user', 'submitted_user.id = rb.submitted_keuangan_by', 'left')
            ->join('users cair_user', 'cair_user.id = rb.cair_keuangan_by', 'left')
            ->where('rb.id', $idBonus)
            ->get()
            ->getRow();
    }

    /**
     * Validasi kode referral secara publik (lintas proyek).
     * Mengembalikan data konsumen + semua kavling yang dimiliki beserta nama proyek.
     */
    public function findPublicByKode(string $kode): ?object
    {
        $sql = "SELECT
                    k.nama_konsumen,
                    k.kode_referal,
                    GROUP_CONCAT(
                        DISTINCT CONCAT(p.nama_proyek, ' — ', jl.nama_jalan, ' No. ', kv.no_kavling)
                        ORDER BY p.nama_proyek, jl.nama_jalan, kv.no_kavling
                        SEPARATOR ' | '
                    ) as kavling_dimiliki,
                    GROUP_CONCAT(
                        DISTINCT p.nama_proyek
                        ORDER BY p.nama_proyek
                        SEPARATOR ', '
                    ) as nama_proyek
                FROM konsumen k
                JOIN mkdt mk  ON mk.id_konsumen = k.id_konsumen
                JOIN kavling kv ON kv.id_mkdt = mk.id_mkdt
                JOIN jalan jl   ON jl.id_jalan = kv.id_jalan
                JOIN cluster cl ON cl.id_cluster = jl.id_cluster
                JOIN proyek p   ON p.id_proyek = cl.id_proyek
                WHERE k.kode_referal = ?
                  AND mk.status_mkdt NOT IN ('Batal')
                GROUP BY k.id_konsumen
                LIMIT 1";

        return $this->db->query($sql, [$kode])->getRow();
    }

    public function searchReferrerOptions(string $search, int $idProyek): array
    {
        $builder = $this->db->table('konsumen k')
            ->select('k.kode_referal, k.nama_konsumen, COUNT(DISTINCT r.id) as jumlah_referal')
            ->join('mkdt mk', 'mk.id_konsumen = k.id_konsumen')
            ->join('kavling kv', 'kv.id_mkdt = mk.id_mkdt')
            ->join('jalan jl', 'jl.id_jalan = kv.id_jalan')
            ->join('cluster cl', 'cl.id_cluster = jl.id_cluster')
            ->join('referrals r', 'r.id_konsumen_referrer = k.id_konsumen AND r.id_proyek = cl.id_proyek')
            ->where('cl.id_proyek', $idProyek)
            ->where('r.id_proyek', $idProyek)
            ->where("COALESCE(r.status, 'active') = 'active'", null, false)
            ->where('mk.status_mkdt !=', 'Batal')
            ->where('k.kode_referal IS NOT NULL')
            ->where('k.kode_referal !=', '');

        if (!empty($search)) {
            $builder->groupStart()
                ->like('k.kode_referal', $search)
                ->orLike('k.nama_konsumen', $search)
                ->groupEnd();
        }

        return $builder
            ->groupBy('k.id_konsumen, k.kode_referal, k.nama_konsumen')
            ->orderBy('jumlah_referal', 'DESC')
            ->limit(20)
            ->get()
            ->getResult();
    }

    private function mgmFilterSql(array $filters, array &$params, string $referrerAlias): string
    {
        $conditions = [];

        if (!empty($filters['kode_referal'])) {
            $conditions[] = "{$referrerAlias}.kode_referal = ?";
            $params[] = strtoupper(trim((string) $filters['kode_referal']));
        }

        $status = (string) ($filters['filter_status'] ?? '');
        if ($status === 'booking') {
            $conditions[] = $this->nonAkadStageSql();
            $conditions[] = $this->dateRangeSql('mk.booking_tgl', $filters, $params);
        } elseif ($status === 'akad') {
            $conditions[] = $this->akadStageSql();
            $conditions[] = "LOWER(COALESCE(mk.status_mkdt, '')) = 'akad'";
            $conditions[] = $this->dateRangeSql('mk.akad_tgl', $filters, $params);
        } elseif ($status === 'cair_bonus_booking') {
            $conditions[] = $this->nonAkadStageSql();
            $conditions[] = $this->anyDateRangeSql(['rb.tanggal_cair_keuangan', 'rb.paid_promosi_tanggal'], $filters, $params);
        } elseif ($status === 'cair_bonus_akad') {
            $conditions[] = $this->akadStageSql();
            $conditions[] = $this->anyDateRangeSql(['rb.tanggal_cair_keuangan', 'rb.paid_promosi_tanggal'], $filters, $params);
        } elseif (!empty($filters['kode_referal']) && (!empty($filters['tanggal_mulai']) || !empty($filters['tanggal_selesai']))) {
            $conditions[] = $this->anyDateRangeSql([
                'mk.booking_tgl',
                'mk.akad_tgl',
                'rb.tanggal_cair_keuangan',
                'rb.paid_promosi_tanggal',
            ], $filters, $params);
        }

        if (empty($conditions)) {
            return '';
        }

        return ' AND ' . implode(' AND ', array_map(static fn ($condition) => "({$condition})", $conditions));
    }

    private function nonAkadStageSql(): string
    {
        return "(LOWER(COALESCE(st.nama_tahapan, '')) NOT LIKE '%akad%' AND LOWER(COALESCE(st.trigger_status_mkdt, '')) <> 'akad')";
    }

    private function akadStageSql(): string
    {
        return "(LOWER(COALESCE(st.nama_tahapan, '')) LIKE '%akad%' OR LOWER(COALESCE(st.trigger_status_mkdt, '')) = 'akad')";
    }

    private function dateRangeSql(string $column, array $filters, array &$params): string
    {
        $conditions = [$this->validDateSql($column)];

        if (!empty($filters['tanggal_mulai'])) {
            $conditions[] = "{$column} >= ?";
            $params[] = $filters['tanggal_mulai'];
        }

        if (!empty($filters['tanggal_selesai'])) {
            $conditions[] = "{$column} <= ?";
            $params[] = $filters['tanggal_selesai'];
        }

        return implode(' AND ', $conditions);
    }

    private function anyDateRangeSql(array $columns, array $filters, array &$params): string
    {
        $conditions = [];
        foreach ($columns as $column) {
            $conditions[] = $this->dateRangeSql($column, $filters, $params);
        }

        return '(' . implode(' OR ', array_map(static fn ($condition) => "({$condition})", $conditions)) . ')';
    }

    private function validDateSql(string $column): string
    {
        return "{$column} IS NOT NULL AND YEAR({$column}) > 0";
    }
}

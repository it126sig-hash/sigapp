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
            LEFT JOIN referral_bonuses rb ON rb.id_referral = r.id
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
            GROUP BY r.id_konsumen_referrer, k.nama_konsumen, k.kode_referal, kav.kavling_dimiliki
            ORDER BY jumlah_referal DESC";

        return $this->db->query($sql, $params)->getResultArray();
    }

    public function getSubRowsByReferrer(int $idKonsumenReferrer, int $idProyek): array
    {
        $sql = "SELECT 
                r.id as id_referral,
                r.id_mkdt_referred,
                r.status as referral_status,
                k.nama_konsumen as referred_nama,
                CONCAT(jl.nama_jalan, ', No. ', kv.no_kavling) as referred_kavling,
                mk.status_mkdt,
                mk.id_mkdt,
                
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
                rb.nominal_cair_keuangan,
                rb.tanggal_cair_keuangan,
                rb.bukti_transfer_ke_promosi,
                rb.cair_keuangan_by,
                rb.bukti_bayar_promosi,
                rb.created_at,
                rb.eligible_at,
                rb.confirmed_at,
                rb.paid_promosi_at
            FROM referrals r
            JOIN mkdt mk ON mk.id_mkdt = r.id_mkdt_referred
            JOIN konsumen k ON k.id_konsumen = mk.id_konsumen
            LEFT JOIN kavling kv ON kv.id_mkdt = mk.id_mkdt
            LEFT JOIN jalan jl ON jl.id_jalan = kv.id_jalan
            JOIN referral_bonus_stages st ON st.id_proyek = r.id_proyek AND st.is_active = 1
            LEFT JOIN referral_bonuses rb ON rb.id_referral = r.id AND rb.id_stage = st.id
            WHERE r.id_konsumen_referrer = ? AND r.id_proyek = ?
              AND COALESCE(r.status, 'active') = 'active'
              AND (
                  LOWER(mk.status_mkdt) = 'akad'
                  OR (
                      LOWER(st.nama_tahapan) NOT LIKE '%akad%'
                      AND LOWER(st.trigger_status_mkdt) <> 'akad'
                  )
              )
            ORDER BY r.id DESC, st.urutan ASC";

        $rows = $this->db->query($sql, [$idKonsumenReferrer, $idProyek])->getResultArray();
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
            ->select('k.kode_referal, k.nama_konsumen')
            ->join('mkdt mk', 'mk.id_konsumen = k.id_konsumen')
            ->join('kavling kv', 'kv.id_mkdt = mk.id_mkdt')
            ->join('jalan jl', 'jl.id_jalan = kv.id_jalan')
            ->join('cluster cl', 'cl.id_cluster = jl.id_cluster')
            ->where('cl.id_proyek', $idProyek)
            ->where('mk.status_mkdt !=', 'Batal')
            ->where('k.kode_referal IS NOT NULL')
            ->where('k.kode_referal !=', '');

        if (!empty($search)) {
            $builder->groupStart()
                ->like('k.kode_referal', $search)
                ->orLike('k.nama_konsumen', $search)
                ->groupEnd();
        }

        return $builder->groupBy('k.id_konsumen')->limit(20)->get()->getResult();
    }
}

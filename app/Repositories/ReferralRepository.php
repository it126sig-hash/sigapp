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
        $builder = $this->db->table('referrals r')
            ->select('
                r.id_konsumen_referrer,
                k.nama_konsumen as referrer_nama,
                k.kode_referal,
                COUNT(DISTINCT r.id) as jumlah_referal,
                GROUP_CONCAT(DISTINCT CONCAT(jl_ref.nama_jalan, ", No. ", kv_ref.no_kavling) SEPARATOR " | ") as kavling_dimiliki,
                
                -- Summary bonus dari sub-query
                COALESCE(SUM(rb.nominal_bonus), 0) as total_penghasilan,
                COALESCE(SUM(CASE WHEN rb.status IN ("cair", "selesai") THEN rb.nominal_bonus ELSE 0 END), 0) as total_sudah_cair_keuangan,
                COALESCE(SUM(CASE WHEN rb.status IN ("dibayar_promosi", "selesai") THEN rb.nominal_bonus ELSE 0 END), 0) as total_sudah_cair_promosi,
                COALESCE(SUM(CASE WHEN rb.status NOT IN ("cair", "selesai", "dibayar_promosi", "batal") THEN rb.nominal_bonus ELSE 0 END), 0) as sisa_belum_cair
            ')
            ->join('konsumen k', 'k.id_konsumen = r.id_konsumen_referrer')
            ->join('mkdt mk_ref', 'mk_ref.id_konsumen = k.id_konsumen', 'left') // untuk dapatkan kavling milik referrer
            ->join('kavling kv_ref', 'kv_ref.id_mkdt = mk_ref.id_mkdt', 'left')
            ->join('jalan jl_ref', 'jl_ref.id_jalan = kv_ref.id_jalan', 'left')
            ->join('referral_bonuses rb', 'rb.id_referral = r.id', 'left')
            ->where('r.id_proyek', $idProyek);

        if (!empty($filters['id_cluster'])) {
            $builder->where('jl_ref.id_cluster', $filters['id_cluster']);
        }

        return $builder->groupBy('r.id_konsumen_referrer')
            ->orderBy('jumlah_referal', 'desc')
            ->get()->getResultArray();
    }

    public function getSubRowsByReferrer(int $idKonsumenReferrer, int $idProyek): array
    {
        $builder = $this->db->table('referrals r')
            ->select('
                r.id as id_referral,
                r.id_mkdt_referred,
                k.nama_konsumen as referred_nama,
                kv.no_kavling as referred_kavling,
                mk.status_mkdt,
                mk.id_mkdt,
                
                rb.id as id_bonus,
                st.nama_tahapan,
                st.id as id_stage,
                COALESCE(rb.nominal_bonus, st.nominal_default) as nominal_bonus,
                rb.status as bonus_status,
                rb.keterangan as bonus_keterangan,
                rb.paid_by_promosi,
                rb.cair_keuangan_at
            ')
            ->join('konsumen k', 'k.id_konsumen = mk_ref.id_konsumen') // referred
            ->join('mkdt mk', 'mk.id_mkdt = r.id_mkdt_referred')
            ->join('kavling kv', 'kv.id_mkdt = mk.id_mkdt', 'left')
            ->join('referral_bonus_stages st', 'st.id_proyek = r.id_proyek AND st.is_active = 1', 'left') // all active stages
            ->join('referral_bonuses rb', 'rb.id_referral = r.id AND rb.id_stage = st.id', 'left')
            ->where('r.id_konsumen_referrer', $idKonsumenReferrer)
            ->where('r.id_proyek', $idProyek)
            ->orderBy('r.id', 'desc')
            ->orderBy('st.urutan', 'asc');
            
        // Because of the join above on mkdt mk and k, let me fix the join condition for k
        // Wait, the join for k is wrong. It should be:
        // ->join('mkdt mk', 'mk.id_mkdt = r.id_mkdt_referred')
        // ->join('konsumen k', 'k.id_konsumen = mk.id_konsumen')
        
        $sql = "SELECT 
                r.id as id_referral,
                r.id_mkdt_referred,
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
                    WHEN rb.status IN ('dikonfirmasi', 'dibayar_promosi') THEN 'Diajukan Promosi'
                    WHEN rb.status IN ('cair', 'selesai') THEN 'Cair Dari Keuangan'
                    WHEN rb.status = 'batal' THEN 'Batal'
                    ELSE 'Belum diajukan'
                END as bonus_status_badge,
                rb.keterangan as bonus_keterangan,
                rb.paid_by_promosi,
                rb.cair_keuangan_at,
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
            ORDER BY r.id DESC, st.urutan ASC";
            
        return $this->db->query($sql, [$idKonsumenReferrer, $idProyek])->getResultArray();
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

    public function getBonusStagesByProyek(int $idProyek): array
    {
        return $this->db->table('referral_bonus_stages')
            ->where('id_proyek', $idProyek)
            ->where('is_active', 1)
            ->orderBy('urutan', 'asc')
            ->get()->getResultArray();
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

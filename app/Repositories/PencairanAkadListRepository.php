<?php

namespace App\Repositories;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\BaseConnection;
use Config\Database;

class PencairanAkadListRepository
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? Database::connect();
    }

    public function buildListQuery(array $filters): BaseBuilder
    {
        $pengajuanAgg = $this->db->table('pencairan_akad_pengajuan')
            ->select("id_plan,
                      SUM(CASE WHEN status IN ('active','partial') THEN total_pengajuan - total_cair ELSE 0 END) AS outstanding,
                      SUM(CASE WHEN status != 'void' THEN total_cair ELSE 0 END) AS total_cair")
            ->groupBy('id_plan')
            ->getCompiledSelect();

        $builder = $this->db->table('mkdt m')
            ->select('
                m.id_mkdt, m.id_kavling, m.akad_tgl, m.harga_kpr_acc, m.is_kpr,
                c.nama_konsumen, j.nama_jalan, k.no_kavling, tipe.tipe_rumah,
                hj.hargajual, p.nama_proyek,
                pap.id AS id_plan,
                COALESCE(pap.total_hasil_akad, m.harga_kpr_acc) AS total_hasil_akad,
                COALESCE(pg.outstanding, 0) AS pengajuan_outstanding,
                COALESCE(pg.total_cair, 0) AS sudah_cair
            ')
            ->join('kavling k', 'k.id_mkdt = m.id_mkdt')
            ->join('jalan j', 'j.id_jalan = k.id_jalan')
            ->join('cluster cl', 'cl.id_cluster = j.id_cluster')
            ->join('proyek p', 'p.id_proyek = cl.id_proyek')
            ->join('konsumen c', 'c.id_konsumen = m.id_konsumen')
            ->join('hargajual hj', 'hj.id = k.harga_akhir', 'left')
            ->join('tipe', 'tipe.id_tipe = k.id_tipe', 'left')
            ->join('pencairan_akad_plan pap', 'pap.id_mkdt = m.id_mkdt', 'left')
            ->join("({$pengajuanAgg}) pg", 'pg.id_plan = pap.id', 'left')
            ->where('m.status_mkdt', 'Akad')
            ->where('m.is_kpr', 1);

        $this->applyFilters($builder, $filters);

        return $builder;
    }

    public function getSummary(array $filters, string $search = ''): array
    {
        $builder = $this->buildListQuery($filters);
        $this->applySearch($builder, $search);

        $filteredSql = $builder->getCompiledSelect();
        $row = $this->db->query("SELECT
                COUNT(DISTINCT hasil_akad.id_mkdt) AS jumlah_kavling,
                COALESCE(SUM(hasil_akad.hargajual), 0) AS total_harga_jual,
                COALESCE(SUM(hasil_akad.harga_kpr_acc), 0) AS total_acc_kpr,
                COALESCE(SUM(hasil_akad.pengajuan_outstanding), 0) AS total_pengajuan_outstanding,
                COALESCE(SUM(hasil_akad.sudah_cair), 0) AS total_sudah_cair,
                COALESCE(SUM(hasil_akad.harga_kpr_acc - hasil_akad.sudah_cair), 0) AS total_sisa
            FROM ({$filteredSql}) hasil_akad")->getRowArray() ?? [];

        return [
            'jumlah_kavling' => (int) ($row['jumlah_kavling'] ?? 0),
            'total_harga_jual' => (float) ($row['total_harga_jual'] ?? 0),
            'total_acc_kpr' => (float) ($row['total_acc_kpr'] ?? 0),
            'total_pengajuan_outstanding' => (float) ($row['total_pengajuan_outstanding'] ?? 0),
            'total_sudah_cair' => (float) ($row['total_sudah_cair'] ?? 0),
            'total_sisa' => (float) ($row['total_sisa'] ?? 0),
        ];
    }

    private function applyFilters(BaseBuilder $builder, array $filters): void
    {
        if (!empty($filters['id_proyek'])) {
            $builder->where('p.id_proyek', (int) $filters['id_proyek']);
        }
        if (!empty($filters['id_cluster'])) {
            $builder->where('cl.id_cluster', (int) $filters['id_cluster']);
        }
        if (!empty($filters['id_jalan'])) {
            $builder->where('j.id_jalan', (int) $filters['id_jalan']);
        }

        if (($filters['status_cair'] ?? '') === 'belum_cair') {
            $builder->where('m.harga_kpr_acc - COALESCE(pg.total_cair, 0) > 0.01', null, false);
        } elseif (($filters['status_cair'] ?? '') === 'sudah_cair') {
            $builder->where('m.harga_kpr_acc - COALESCE(pg.total_cair, 0) <= 0.01', null, false);
        }

        $this->applyDateFilter($builder, $filters);
    }

    private function applyDateFilter(BaseBuilder $builder, array $filters): void
    {
        $start = $filters['tanggal_mulai'] ?? null;
        $end = $filters['tanggal_selesai'] ?? null;
        if (!$start || !$end) {
            return;
        }

        $jenisTanggal = $filters['jenis_tanggal'] ?? 'tanggal_akad';
        if ($jenisTanggal === 'tanggal_akad') {
            $builder->where('m.akad_tgl >=', $start);
            $builder->where('m.akad_tgl <=', $end);
            return;
        }

        $startSql = $this->db->escape($start);
        $endSql = $this->db->escape($end);

        if ($jenisTanggal === 'tanggal_pengajuan') {
            $builder->where("EXISTS (
                SELECT 1
                FROM pencairan_akad_pengajuan papg_filter
                WHERE papg_filter.id_plan = pap.id
                  AND papg_filter.status <> 'void'
                  AND papg_filter.tanggal_pengajuan >= {$startSql}
                  AND papg_filter.tanggal_pengajuan <= {$endSql}
            )", null, false);
            return;
        }

        if ($jenisTanggal === 'tanggal_pencairan') {
            $builder->where("EXISTS (
                SELECT 1
                FROM pencairan_akad_pengajuan papg_filter
                JOIN pencairan_akad_payment papy_filter ON papy_filter.id_pengajuan = papg_filter.id
                WHERE papg_filter.id_plan = pap.id
                  AND papg_filter.status <> 'void'
                  AND papy_filter.total_cair > 0
                  AND papy_filter.tanggal_cair >= {$startSql}
                  AND papy_filter.tanggal_cair <= {$endSql}
            )", null, false);
        }
    }

    private function applySearch(BaseBuilder $builder, string $search): void
    {
        if ($search === '') {
            return;
        }

        $builder->groupStart()
            ->like('c.nama_konsumen', $search)
            ->orLike('k.no_kavling', $search)
            ->orLike('j.nama_jalan', $search)
            ->groupEnd();
    }
}

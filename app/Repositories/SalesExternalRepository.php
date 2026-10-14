<?php

namespace App\Repositories;

use Config\Database;

class SalesExternalRepository
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    /**
     * Mengambil daftar master sales dengan pagination dan search DataTables
     */
    public function getMasterSalesList(array $params = []): array
    {
        $builder = $this->db->table('sales s')
            ->select('s.*, u_add.username AS created_by_name')
            ->join('users u_add', 'u_add.id = s.created_by', 'left')
            ->where('s.deleted_at IS NULL');

        if (! empty($params['status']) && $params['status'] !== 'all') {
            $builder->where('s.status', $params['status']);
        }

        if (! empty($params['search'])) {
            $search = $params['search'];
            $builder->groupStart()
                ->like('s.nama', $search)
                ->orLike('s.kontak', $search)
                ->orLike('s.bank', $search)
                ->orLike('s.no_rek', $search)
                ->groupEnd();
        }

        $totalFiltered = (clone $builder)->countAllResults();

        $start  = (int) ($params['start'] ?? 0);
        $length = (int) ($params['length'] ?? 10);
        if ($length > 0) {
            $builder->limit($length, $start);
        }

        $builder->orderBy('s.nama', 'ASC');
        $data = $builder->get()->getResult();

        return [
            'total' => $totalFiltered,
            'data'  => $data,
        ];
    }

    public function getSalesById(int $id): ?object
    {
        return $this->db->table('sales')
            ->where('id', $id)
            ->where('deleted_at IS NULL')
            ->get()
            ->getRow();
    }

    public function getSalesHistory(int $idSales): array
    {
        return $this->db->table('sales_history sh')
            ->select('sh.*, u.username AS created_by_name')
            ->join('users u', 'u.id = sh.created_by', 'left')
            ->where('sh.id_sales', $idSales)
            ->orderBy('sh.created_at', 'DESC')
            ->get()
            ->getResult();
    }

    public function getActiveSales(): array
    {
        return $this->db->table('sales')
            ->select('id, nama, kontak, bank, no_rek, fee_subsidi, fee_komersil')
            ->where('deleted_at IS NULL')
            ->whereIn('status', ['active', 'aktif'])
            ->orderBy('nama', 'ASC')
            ->get()
            ->getResult();
    }

    /**
     * Query data tracking Fee Sales untuk halaman /fee-sales
     */
    public function getFeeSalesList(array $params = []): array
    {
        $builder = $this->db->table('mkdt m')
            ->select([
                'm.id_mkdt',
                'm.id_kavling',
                'm.id_konsumen',
                'm.status_mkdt',
                'm.is_ajb',
                'm.is_subsidi',
                'm.akad_tgl',
                'm.harga_jual',
                'm.harga_jual_net',
                'm.harga_net_ajb',
                
                'k.nama_konsumen',
                'k.hp_konsumen',
                'k.sales AS sales_konsumen',
                'k.keterangan_sales',
                'k.id_sales',

                'kv.no_kavling',
                'j.nama_jalan',
                'c.nama_cluster',
                'p.id_proyek',
                'p.nama_proyek',
                't.tipe_rumah',

                's.nama AS master_sales_nama',
                's.bank AS sales_bank',
                's.no_rek AS sales_no_rek',
                's.fee_subsidi AS sales_fee_subsidi',
                's.fee_komersil AS sales_fee_komersil',

                'fs.id AS id_fee_sales',
                'fs.sales_nama AS fee_sales_nama',
                'fs.sales_keterangan AS fee_sales_keterangan',
                'fs.harga_net_ajb AS fee_harga_net_ajb',
                'fs.persentase_fee AS fee_persentase',
                'fs.total_fee AS fee_total',
                'fs.skema_termin AS fee_skema_termin',
                'fs.status AS fee_status',
                'fs.catatan AS fee_catatan',

                'keu.total_hasil_akad',
                'keu.total_cair_keuangan',

                't_stat.total_termin',
                't_stat.termin_cair',
                't_stat.termin_spp',
                't_stat.nominal_cair',
            ])
            ->join('konsumen k', 'k.id_konsumen = m.id_konsumen')
            ->join('kavling kv', 'kv.id_kavling = m.id_kavling')
            ->join('jalan j', 'j.id_jalan = kv.id_jalan', 'left')
            ->join('cluster c', 'c.id_cluster = j.id_cluster', 'left')
            ->join('proyek p', 'p.id_proyek = c.id_proyek', 'left')
            ->join('tipe t', 't.id_tipe = kv.id_tipe', 'left')
            ->join('sales s', 's.id = k.id_sales', 'left')
            ->join('fee_sales fs', 'fs.id_mkdt = m.id_mkdt', 'left')
            ->join('(
                SELECT pap.id_mkdt, pap.total_hasil_akad, COALESCE(SUM(paq.total_cair), 0) AS total_cair_keuangan
                FROM pencairan_akad_plan pap
                LEFT JOIN pencairan_akad_pengajuan paq ON paq.id_plan = pap.id AND paq.status = "paid"
                GROUP BY pap.id_mkdt, pap.total_hasil_akad
            ) keu', 'keu.id_mkdt = m.id_mkdt', 'left')
            ->join('(
                SELECT fst.id_fee_sales,
                       COUNT(*) AS total_termin,
                       SUM(CASE WHEN fst.status = "sudah_cair" THEN 1 ELSE 0 END) AS termin_cair,
                       SUM(CASE WHEN fst.status = "pengajuan_spp" THEN 1 ELSE 0 END) AS termin_spp,
                       SUM(CASE WHEN fst.status = "sudah_cair" THEN fst.nominal ELSE 0 END) AS nominal_cair
                FROM fee_sales_termin fst
                GROUP BY fst.id_fee_sales
            ) t_stat', 't_stat.id_fee_sales = fs.id', 'left')
            ->where('m.is_batal', 0)
            ->where('(m.status_mkdt = "Akad" OR m.is_ajb = 1 OR m.akad_tgl IS NOT NULL)');

        // Filter Proyek
        if (! empty($params['id_proyek'])) {
            $builder->where('p.id_proyek', (int) $params['id_proyek']);
        }

        // Filter Status Fee
        if (! empty($params['status_fee']) && $params['status_fee'] !== 'all') {
            if ($params['status_fee'] === 'belum_proses') {
                $builder->where('(fs.id IS NULL OR fs.status = "draft")');
            } elseif ($params['status_fee'] === 'proses_spp') {
                $builder->where('t_stat.termin_spp >', 0);
            } elseif ($params['status_fee'] === 'cair_sebagian') {
                $builder->where('t_stat.termin_cair >', 0)
                        ->where('fs.status !=', 'selesai');
            } elseif ($params['status_fee'] === 'selesai') {
                $builder->where('fs.status', 'selesai');
            }
        }

        // Filter Status Akad (PPJB / AJB)
        if (isset($params['is_ajb']) && $params['is_ajb'] !== '') {
            $builder->where('m.is_ajb', (int) $params['is_ajb']);
        }

        // Search text
        if (! empty($params['search'])) {
            $s = $params['search'];
            $builder->groupStart()
                ->like('k.nama_konsumen', $s)
                ->orLike('kv.no_kavling', $s)
                ->orLike('k.sales', $s)
                ->orLike('k.keterangan_sales', $s)
                ->orLike('s.nama', $s)
                ->groupEnd();
        }

        $totalFiltered = (clone $builder)->countAllResults();

        $start  = (int) ($params['start'] ?? 0);
        $length = (int) ($params['length'] ?? 10);
        if ($length > 0) {
            $builder->limit($length, $start);
        }

        $builder->orderBy('m.akad_tgl', 'DESC');
        $builder->orderBy('m.id_mkdt', 'DESC');
        $data = $builder->get()->getResult();

        return [
            'total' => $totalFiltered,
            'data'  => $data,
        ];
    }

    public function getFeeDetailByMkdt(int $idMkdt): ?object
    {
        $row = $this->db->table('mkdt m')
            ->select([
                'm.id_mkdt',
                'm.id_kavling',
                'm.id_konsumen',
                'm.status_mkdt',
                'm.is_ajb',
                'm.is_subsidi',
                'm.akad_tgl',
                'm.harga_jual',
                'm.harga_jual_net',
                'm.harga_net_ajb',

                'k.nama_konsumen',
                'k.hp_konsumen',
                'k.sales AS sales_konsumen',
                'k.keterangan_sales',
                'k.id_sales',

                'kv.no_kavling',
                'j.nama_jalan',
                'c.nama_cluster',
                'p.id_proyek',
                'p.nama_proyek',
                't.tipe_rumah',

                's.nama AS master_sales_nama',
                's.bank AS sales_bank',
                's.no_rek AS sales_no_rek',
                's.fee_subsidi AS sales_fee_subsidi',
                's.fee_komersil AS sales_fee_komersil',

                'fs.id AS id_fee_sales',
                'fs.sales_nama AS fee_sales_nama',
                'fs.sales_keterangan AS fee_sales_keterangan',
                'fs.harga_net_ajb AS fee_harga_net_ajb',
                'fs.persentase_fee AS fee_persentase',
                'fs.total_fee AS fee_total',
                'fs.skema_termin AS fee_skema_termin',
                'fs.status AS fee_status',
                'fs.catatan AS fee_catatan',

                'keu.total_hasil_akad',
                'keu.total_cair_keuangan',
            ])
            ->join('konsumen k', 'k.id_konsumen = m.id_konsumen')
            ->join('kavling kv', 'kv.id_kavling = m.id_kavling')
            ->join('jalan j', 'j.id_jalan = kv.id_jalan', 'left')
            ->join('cluster c', 'c.id_cluster = j.id_cluster', 'left')
            ->join('proyek p', 'p.id_proyek = c.id_proyek', 'left')
            ->join('tipe t', 't.id_tipe = kv.id_tipe', 'left')
            ->join('sales s', 's.id = k.id_sales', 'left')
            ->join('fee_sales fs', 'fs.id_mkdt = m.id_mkdt', 'left')
            ->join('(
                SELECT pap.id_mkdt, pap.total_hasil_akad, COALESCE(SUM(paq.total_cair), 0) AS total_cair_keuangan
                FROM pencairan_akad_plan pap
                LEFT JOIN pencairan_akad_pengajuan paq ON paq.id_plan = pap.id AND paq.status = "paid"
                GROUP BY pap.id_mkdt, pap.total_hasil_akad
            ) keu', 'keu.id_mkdt = m.id_mkdt', 'left')
            ->where('m.id_mkdt', $idMkdt)
            ->get()
            ->getRow();

        if (! $row) {
            return null;
        }

        // Ambil detail termin jika fee_sales sudah ada
        $termin = [];
        if (! empty($row->id_fee_sales)) {
            $termin = $this->db->table('fee_sales_termin')
                ->where('id_fee_sales', $row->id_fee_sales)
                ->orderBy('termin_ke', 'ASC')
                ->get()
                ->getResult();
        }

        $row->termin = $termin;
        return $row;
    }
}

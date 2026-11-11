<?php

namespace App\Repositories\SalesExternal;

use Config\Database;

class SalesExternalRepository
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function getMkdtIdByFeeSalesId(int $idFeeSales): ?int
    {
        $row = $this->db->table('fee_sales')
            ->select('id_mkdt')
            ->where('id', $idFeeSales)
            ->get()
            ->getRow();

        return $row ? (int) $row->id_mkdt : null;
    }

    public function hasGeneratedTerminByMkdt(int $idMkdt): bool
    {
        return $this->db->table('fee_sales_termin fst')
            ->join('fee_sales fs', 'fs.id = fst.id_fee_sales')
            ->where('fs.id_mkdt', $idMkdt)
            ->countAllResults() > 0;
    }

    public function hasCairTerminByMkdt(int $idMkdt): bool
    {
        return $this->db->table('fee_sales_termin fst')
            ->join('fee_sales fs', 'fs.id = fst.id_fee_sales')
            ->where('fs.id_mkdt', $idMkdt)
            ->where('fst.status', 'sudah_cair')
            ->countAllResults() > 0;
    }

    public function userSnapshot(int $userId): array
    {
        $row = $this->db->table('users u')
            ->select('u.id, u.username, u.email, u.password_hash, COALESCE(NULLIF(k.nama_karyawan, ""), NULLIF(u.name, ""), u.username) AS name', false)
            ->join('karyawan k', 'k.id_user = u.id', 'left')
            ->where('u.id', $userId)
            ->get()
            ->getRowArray();

        return $row ?: [
            'id'            => $userId,
            'username'      => 'user-' . $userId,
            'email'         => '',
            'password_hash' => '',
            'name'          => 'User #' . $userId,
        ];
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
            ->select('id, nama, kontak, bank, no_rek, fee_subsidi, fee_komersil, COALESCE(has_pks, 0) AS has_pks')
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
                'm.is_kpr',
                'm.is_ajb',
                'm.is_subsidi',
                'm.akad',
                'm.akad_indent',
                'm.akad_indent_tgl',
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
                'COALESCE(NULLIF(hj.id_tipe, ""), t.tipe_rumah) AS tipe_rumah',

                's.nama AS master_sales_nama',
                's.bank AS sales_bank',
                's.no_rek AS sales_no_rek',
                's.fee_subsidi AS sales_fee_subsidi',
                's.fee_komersil AS sales_fee_komersil',

                'fs_agg.id_fee_sales',
                'COALESCE(fs_agg.fee_sales_nama, s.nama, k.sales) AS fee_sales_nama',
                'COALESCE(fs_agg.sales_keterangan, k.keterangan_sales) AS fee_sales_keterangan',
                'COALESCE(fs_agg.fee_sales_nama, s.nama, k.sales) AS master_sales_nama',
                'm.harga_net_ajb AS fee_harga_net_ajb',
                'fs_agg.fee_persentase',
                'fs_agg.fee_total',
                'fs_agg.fee_skema_termin',
                'COALESCE(fs_agg.fee_status, CASE WHEN m.is_fee_sales_selesai = 1 THEN "selesai" ELSE NULL END) AS fee_status',
                'fs_agg.total_sales_count',

                'keu.total_hasil_akad',
                'keu.total_cair_keuangan',
                'keu.tgl_cair_keuangan',
                'keu.last_update_keuangan',

                't_stat.total_termin',
                't_stat.termin_cair',
                't_stat.termin_spp',
                't_stat.nominal_cair',
                't_stat.tgl_pengajuan_spp',
                't_stat.tgl_cair_terakhir',
            ])
            ->join('konsumen k', 'k.id_konsumen = m.id_konsumen')
            ->join('kavling kv', 'kv.id_kavling = m.id_kavling')
            ->join('jalan j', 'j.id_jalan = kv.id_jalan', 'left')
            ->join('cluster c', 'c.id_cluster = j.id_cluster', 'left')
            ->join('proyek p', 'p.id_proyek = c.id_proyek', 'left')
            ->join('tipe t', 't.id_tipe = kv.id_tipe', 'left')
            ->join('hargajual hj', 'hj.id = COALESCE(NULLIF(m.id_hargajual, 0), kv.harga_akhir)', 'left')
            ->join('sales s', 's.id = k.id_sales', 'left')
            ->join('(
                SELECT 
                    fs.id_mkdt,
                    MIN(fs.id) AS id_fee_sales,
                    GROUP_CONCAT(DISTINCT fs.sales_nama ORDER BY fs.id SEPARATOR ", ") AS fee_sales_nama,
                    GROUP_CONCAT(DISTINCT fs.sales_keterangan SEPARATOR ", ") AS sales_keterangan,
                    COUNT(DISTINCT fs.id) AS total_sales_count,
                    SUM(fs.total_fee) AS fee_total,
                    SUM(fs.persentase_fee) AS fee_persentase,
                    MIN(fs.skema_termin) AS fee_skema_termin,
                    CASE 
                        WHEN m.is_fee_sales_selesai = 1 THEN "selesai"
                        WHEN SUM(CASE WHEN fs.status != "selesai" THEN 1 ELSE 0 END) = 0 AND COUNT(fs.id) > 0 THEN "selesai"
                        WHEN SUM(CASE WHEN fs.status = "proses_termin" THEN 1 ELSE 0 END) > 0 THEN "proses_termin"
                        WHEN COUNT(fs.id) > 0 THEN "draft"
                        ELSE NULL
                    END AS fee_status
                FROM fee_sales fs
                LEFT JOIN mkdt m ON m.id_mkdt = fs.id_mkdt
                GROUP BY fs.id_mkdt, m.is_fee_sales_selesai
            ) fs_agg', 'fs_agg.id_mkdt = m.id_mkdt', 'left')
            ->join('(
                SELECT pap.id_mkdt, pap.total_hasil_akad, COALESCE(SUM(paq.total_cair), 0) AS total_cair_keuangan,
                       MAX(COALESCE(pay.tanggal_cair, paq.tanggal_rencana_cair, paq.tanggal_pengajuan, DATE(paq.updated_at))) AS tgl_cair_keuangan,
                       MAX(paq.updated_at) AS last_update_keuangan
                FROM pencairan_akad_plan pap
                LEFT JOIN pencairan_akad_pengajuan paq ON paq.id_plan = pap.id AND paq.status = "paid"
                LEFT JOIN pencairan_akad_payment pay ON pay.id_pengajuan = paq.id
                GROUP BY pap.id_mkdt, pap.total_hasil_akad
            ) keu', 'keu.id_mkdt = m.id_mkdt', 'left')
            ->join('(
                SELECT fs.id_mkdt,
                       COUNT(*) AS total_termin,
                       SUM(CASE WHEN fst.status = "sudah_cair" THEN 1 ELSE 0 END) AS termin_cair,
                       SUM(CASE WHEN fst.status = "pengajuan_spp" THEN 1 ELSE 0 END) AS termin_spp,
                       SUM(CASE WHEN fst.status = "sudah_cair" THEN fst.nominal ELSE 0 END) AS nominal_cair,
                       MAX(CASE WHEN fst.status = "pengajuan_spp" THEN fst.tanggal_pengajuan_spp ELSE NULL END) AS tgl_pengajuan_spp,
                       MAX(CASE WHEN fst.status = "sudah_cair" THEN fst.tanggal_cair ELSE NULL END) AS tgl_cair_terakhir
                FROM fee_sales_termin fst
                JOIN fee_sales fs ON fs.id = fst.id_fee_sales
                GROUP BY fs.id_mkdt
            ) t_stat', 't_stat.id_mkdt = m.id_mkdt', 'left')
            ->where('m.is_batal', 0)
            ->where('(m.status_mkdt = "Akad" OR m.is_ajb IN ("AJB", "PPJB") OR m.is_ajb = "1" OR m.akad_tgl IS NOT NULL)');

        // Filter Proyek
        if (! empty($params['id_proyek'])) {
            $builder->where('p.id_proyek', (int) $params['id_proyek']);
        }

        // Filter Sales External
        if (! empty($params['id_sales'])) {
            $idSales = (int) $params['id_sales'];
            $salesRow = $this->db->table('sales')->select('nama')->where('id', $idSales)->get()->getRow();
            $salesNama = $salesRow ? $salesRow->nama : '';
            $builder->where('(
                k.id_sales = ' . $idSales . '
                OR EXISTS (SELECT 1 FROM fee_sales fs_chk WHERE fs_chk.id_mkdt = m.id_mkdt AND (fs_chk.id_sales = ' . $idSales . ($salesNama ? ' OR fs_chk.sales_nama = ' . $this->db->escape($salesNama) : '') . '))
                ' . ($salesNama ? 'OR k.sales = ' . $this->db->escape($salesNama) : '') . '
            )');
        }

        // Filter Status Fee
        if (! empty($params['status_fee']) && $params['status_fee'] !== 'all') {
            if ($params['status_fee'] === 'belum_lunas') {
                $builder->where('(m.is_fee_sales_selesai = 0 OR m.is_fee_sales_selesai IS NULL)')
                        ->where('(fs_agg.fee_status IS NULL OR fs_agg.fee_status != "selesai")')
                        ->where('(t_stat.total_termin IS NULL OR t_stat.total_termin = 0 OR t_stat.termin_cair < t_stat.total_termin)');
            } elseif ($params['status_fee'] === 'belum_proses') {
                $builder->where('(m.is_fee_sales_selesai = 0 OR m.is_fee_sales_selesai IS NULL)')
                        ->where('(fs_agg.id_fee_sales IS NULL OR fs_agg.fee_status = "draft")');
            } elseif ($params['status_fee'] === 'proses_spp') {
                $builder->where('t_stat.termin_spp >', 0);
            } elseif ($params['status_fee'] === 'cair_sebagian') {
                $builder->where('t_stat.termin_cair >', 0)
                        ->where('fs_agg.fee_status !=', 'selesai');
            } elseif ($params['status_fee'] === 'selesai') {
                $builder->where('(fs_agg.fee_status = "selesai" OR m.is_fee_sales_selesai = 1)');
            }
        }

        // Filter Status Akad (PPJB / AJB)
        if (isset($params['is_ajb']) && $params['is_ajb'] !== '') {
            $val = strtoupper(trim($params['is_ajb']));
            if ($val === '1' || $val === 'AJB') {
                $builder->groupStart()
                    ->where('UPPER(TRIM(m.is_ajb))', 'AJB')
                    ->orWhere('m.is_ajb', '1')
                    ->groupEnd();
            } elseif ($val === '0' || $val === 'PPJB') {
                $builder->groupStart()
                    ->where('UPPER(TRIM(m.is_ajb))', 'PPJB')
                    ->orWhere('m.is_ajb', '0')
                    ->groupEnd();
            }
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
                ->orLike('fs_agg.fee_sales_nama', $s)
                ->groupEnd();
        }

        $totalFiltered = (clone $builder)->countAllResults();

        $start  = (int) ($params['start'] ?? 0);
        $length = (int) ($params['length'] ?? 10);
        if ($length > 0) {
            $builder->limit($length, $start);
        }

        $columnMap = [
            2  => 'k.nama_konsumen',
            3  => 'm.harga_jual',
            4  => 'm.harga_jual_net',
            5  => 'COALESCE(NULLIF(m.harga_net_ajb, 0), NULLIF(m.harga_jual_net, 0), m.harga_jual)',
            6  => 'COALESCE(fs_agg.fee_sales_nama, s.nama, k.sales)',
            7  => 'COALESCE(NULLIF(CAST(m.akad_tgl AS CHAR), "0000-00-00"), NULLIF(CAST(m.akad_indent_tgl AS CHAR), "0000-00-00"))',
            8  => 'COALESCE(keu.tgl_cair_keuangan, "1970-01-01")',
            9  => 'fs_agg.fee_total',
            10 => 'fs_agg.fee_status',
        ];

        $hasCustomOrder = false;
        if (! empty($params['order']) && is_array($params['order'])) {
            $orderColIdx = (int) ($params['order'][0]['column'] ?? -1);
            $orderDir    = strtolower((string) ($params['order'][0]['dir'] ?? 'desc')) === 'asc' ? 'ASC' : 'DESC';

            if (isset($columnMap[$orderColIdx])) {
                $builder->orderBy($columnMap[$orderColIdx], $orderDir, false);
                $hasCustomOrder = true;
            }
        }

        if (! $hasCustomOrder) {
            $builder->orderBy('COALESCE(keu.tgl_cair_keuangan, "1970-01-01")', 'DESC');
            $builder->orderBy('m.akad_tgl', 'DESC');
        }

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
                'm.is_kpr',
                'm.is_ajb',
                'm.is_subsidi',
                'm.akad',
                'm.akad_indent',
                'm.akad_indent_tgl',
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
                'COALESCE(NULLIF(hj.id_tipe, ""), t.tipe_rumah) AS tipe_rumah',

                's.nama AS master_sales_nama',
                's.bank AS sales_bank',
                's.no_rek AS sales_no_rek',
                's.fee_subsidi AS sales_fee_subsidi',
                's.fee_komersil AS sales_fee_komersil',
                'COALESCE(s.has_pks, 0) AS sales_has_pks',

                'keu.total_hasil_akad',
                'keu.total_cair_keuangan',
            ])
            ->join('konsumen k', 'k.id_konsumen = m.id_konsumen')
            ->join('kavling kv', 'kv.id_kavling = m.id_kavling')
            ->join('jalan j', 'j.id_jalan = kv.id_jalan', 'left')
            ->join('cluster c', 'c.id_cluster = j.id_cluster', 'left')
            ->join('proyek p', 'p.id_proyek = c.id_proyek', 'left')
            ->join('tipe t', 't.id_tipe = kv.id_tipe', 'left')
            ->join('hargajual hj', 'hj.id = COALESCE(NULLIF(m.id_hargajual, 0), kv.harga_akhir)', 'left')
            ->join('sales s', 's.id = k.id_sales', 'left')
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

        // Ambil semua entri fee_sales yang terkait dengan mkdt ini
        $feeList = $this->db->table('fee_sales fs')
            ->select('fs.*, s.nama AS master_sales_nama, s.bank AS sales_bank, s.no_rek AS sales_no_rek, s.fee_subsidi AS sales_fee_subsidi, s.fee_komersil AS sales_fee_komersil, COALESCE(s.has_pks, 0) AS has_pks')
            ->join('sales s', 's.id = fs.id_sales', 'left')
            ->where('fs.id_mkdt', $idMkdt)
            ->orderBy('fs.id', 'ASC')
            ->get()
            ->getResult();

        $feeSalesIds = array_filter(array_column($feeList, 'id'));
        $allTermins = [];
        if (! empty($feeSalesIds)) {
            $termins = $this->db->table('fee_sales_termin')
                ->whereIn('id_fee_sales', $feeSalesIds)
                ->orderBy('termin_ke', 'ASC')
                ->get()
                ->getResult();
            foreach ($termins as $t) {
                $allTermins[$t->id_fee_sales][] = $t;
            }
        }

        foreach ($feeList as &$fee) {
            $fee->termin = $allTermins[$fee->id] ?? [];
        }

        $row->fee_list = $feeList;
        $row->has_cair_termin = $this->hasCairTerminByMkdt($idMkdt);

        // Ambil active rejection jika unit kavling ini terdapat dalam pengajuan yang berstatus 'ditolak'
        $activeRejection = $this->db->table('fee_sales_pengajuan_item pi')
            ->select([
                'fsp.id AS id_pengajuan',
                'fsp.nomor_pengajuan',
                'fsp.rejected_by',
                'fsp.rejected_role',
                'fsp.rejected_at',
                'fsp.reject_reason',
                'COALESCE(pi.suggested_harga_net_ajb, fsp.suggested_harga_net_ajb) AS suggested_harga_net_ajb',
                'COALESCE(NULLIF(kr.nama_karyawan, ""), NULLIF(ur.name, ""), ur.username) AS rejected_by_name',
            ], false)
            ->join('fee_sales fs', 'fs.id = pi.id_fee_sales')
            ->join('fee_sales_pengajuan fsp', 'fsp.id = pi.id_pengajuan')
            ->join('users ur', 'ur.id = fsp.rejected_by', 'left')
            ->join('karyawan kr', 'kr.id_user = ur.id', 'left')
            ->where('fs.id_mkdt', $idMkdt)
            ->where('fsp.status', 'ditolak')
            ->orderBy('fsp.id', 'DESC')
            ->get()
            ->getRow();

        $row->active_rejection = $activeRejection ?: null;

        // Backward compatibility untuk pemanggil lama yang membaca $row->id_fee_sales atau $row->termin
        if (! empty($feeList)) {
            $firstFee = $feeList[0];
            $row->id_fee_sales = $firstFee->id;
            $row->fee_sales_nama = $firstFee->sales_nama;
            $row->fee_sales_keterangan = $firstFee->sales_keterangan;
            $row->fee_harga_net_ajb = $firstFee->harga_net_ajb;
            $row->fee_persentase = $firstFee->persentase_fee;
            $row->fee_total = $firstFee->total_fee;
            $row->fee_skema_termin = $firstFee->skema_termin;
            $row->fee_status = $firstFee->status;
            $row->fee_catatan = $firstFee->catatan;
            $row->termin = $firstFee->termin;
        } else {
            $row->id_fee_sales = null;
            $row->fee_sales_nama = null;
            $row->fee_sales_keterangan = null;
            $row->fee_harga_net_ajb = null;
            $row->fee_persentase = null;
            $row->fee_total = null;
            $row->fee_skema_termin = null;
            $row->fee_status = 'draft';
            $row->fee_catatan = null;
            $row->termin = [];
        }

        return $row;
    }

    /**
     * Ambil data beberapa MKDT sekaligus beserta termin untuk persiapan modal pengajuan kelompok
     */
    public function getFeeDetailsByMkdtIds(array $mkdtIds): array
    {
        if (empty($mkdtIds)) {
            return [];
        }

        $cleanIds = array_values(array_filter(array_map('intval', $mkdtIds)));
        if (empty($cleanIds)) {
            return [];
        }

        $builder = $this->db->table('mkdt m')
            ->select([
                'm.id_mkdt',
                'm.id_kavling',
                'm.id_konsumen',
                'm.status_mkdt',
                'm.is_kpr',
                'm.is_ajb',
                'm.is_subsidi',
                'm.akad',
                'm.akad_indent',
                'm.akad_indent_tgl',
                'm.booking_tgl',
                'm.akad_tgl',
                'm.harga_jual',
                'm.harga_jual_net',
                'm.harga_net_ajb',
                'm.harga_diskon_hargajual',
                'm.harga_penambahan_tanah',

                'k.nama_konsumen',
                'k.hp_konsumen',
                'k.nama_instansi',
                'k.sales AS sales_konsumen',
                'k.keterangan_sales',
                'k.id_sales',

                'kv.no_kavling',
                'j.nama_jalan',
                'c.nama_cluster',
                'p.id_proyek',
                'p.nama_proyek',
                'p.kode_keuangan',
                'COALESCE(NULLIF(hj.id_tipe, ""), t.tipe_rumah) AS tipe_rumah',

                's.nama AS master_sales_nama',
                's.bank AS sales_bank',
                's.no_rek AS sales_no_rek',
                's.fee_subsidi AS sales_fee_subsidi',
                's.fee_komersil AS sales_fee_komersil',
                'COALESCE(s.has_pks, 0) AS sales_has_pks',

                'fs.id AS id_fee_sales',
                'COALESCE(fs.sales_nama, s.nama, k.sales) AS fee_sales_nama',
                'COALESCE(fs.sales_keterangan, k.keterangan_sales) AS fee_sales_keterangan',
                'fs.harga_net_ajb AS fee_harga_net_ajb',
                'fs.persentase_fee AS fee_persentase',
                'fs.total_fee AS fee_total',
                'fs.skema_termin AS fee_skema_termin',
                'fs.status AS fee_status',
                'fs.catatan AS fee_catatan',
            ])
            ->join('konsumen k', 'k.id_konsumen = m.id_konsumen')
            ->join('kavling kv', 'kv.id_kavling = m.id_kavling')
            ->join('jalan j', 'j.id_jalan = kv.id_jalan', 'left')
            ->join('cluster c', 'c.id_cluster = j.id_cluster', 'left')
            ->join('proyek p', 'p.id_proyek = c.id_proyek', 'left')
            ->join('tipe t', 't.id_tipe = kv.id_tipe', 'left')
            ->join('hargajual hj', 'hj.id = COALESCE(NULLIF(m.id_hargajual, 0), kv.harga_akhir)', 'left')
            ->join('fee_sales fs', 'fs.id_mkdt = m.id_mkdt', 'left')
            ->join('sales s', 's.id = COALESCE(fs.id_sales, k.id_sales)', 'left')
            ->whereIn('m.id_mkdt', $cleanIds)
            ->where('m.is_batal', 0)
            ->orderBy('m.id_mkdt', 'ASC')
            ->orderBy('fs.id', 'ASC');

        $rows = $builder->get()->getResult();

        // Ambil semua termin untuk fee_sales yang ditemukan
        $feeSalesIds = [];
        foreach ($rows as $r) {
            if (! empty($r->id_fee_sales)) {
                $feeSalesIds[] = (int) $r->id_fee_sales;
            }
        }

        $allTermin = [];
        if (! empty($feeSalesIds)) {
            $tRows = $this->db->table('fee_sales_termin')
                ->whereIn('id_fee_sales', array_unique($feeSalesIds))
                ->orderBy('termin_ke', 'ASC')
                ->get()
                ->getResult();
            foreach ($tRows as $tr) {
                $allTermin[$tr->id_fee_sales][] = $tr;
            }
        }

        foreach ($rows as &$r) {
            $r->termin = $allTermin[$r->id_fee_sales] ?? [];
        }

        return $rows;
    }

    /**
     * Server-side DataTables list grup pengajuan pencairan
     */
    public function getPengajuanGroupList(array $params = []): array
    {
        $builder = $this->db->table('fee_sales_pengajuan fsp')
            ->select([
                'fsp.id',
                'fsp.id_proyek',
                'fsp.share_token',
                'fsp.nomor_pengajuan',
                'fsp.tanggal_pengajuan',
                'fsp.tanggal_pengajuan_sales',
                'fsp.catatan',
                'fsp.lampiran_spp',
                'fsp.status',
                'fsp.ttd_pembuat_by',
                'fsp.ttd_pembuat_nama',
                'fsp.ttd_pembuat_at',
                'fsp.ttd_mkdt_by',
                'fsp.ttd_mkdt_by AS ttd_mkdt_id',
                'fsp.ttd_mkdt_nama',
                'fsp.ttd_mkdt_at',
                'fsp.ttd_keuangan_by',
                'fsp.ttd_keuangan_by AS ttd_keuangan_id',
                'fsp.ttd_keuangan_nama',
                'fsp.ttd_keuangan_at',
                'fsp.voided_by',
                'fsp.voided_at',
                'fsp.void_reason',
                'fsp.created_by',
                'fsp.rejected_by',
                'fsp.rejected_role',
                'fsp.rejected_at',
                'fsp.reject_reason',
                'fsp.suggested_harga_net_ajb',
                'COALESCE(NULLIF(kr.nama_karyawan, ""), NULLIF(ur.name, ""), ur.username) AS rejected_by_name',
                'fsp.created_at',
                'p.nama_proyek',
                'p.kode_keuangan',
                'u.username AS creator_name',
                'COALESCE(stat.total_unit, 0) AS total_unit',
                'COALESCE(stat.total_nominal, 0) AS total_nominal',
                'COALESCE(stat.total_item, 0) AS total_item',
                'COALESCE(stat.max_suggested_harga, 0) AS max_suggested_harga',
                'CASE WHEN fsp.status = "void" THEN 0 ELSE COALESCE(stat.total_cair, 0) END AS total_cair',
                'stat.sales_names',
            ], false)
            ->join('users u', 'u.id = fsp.created_by', 'left')
            ->join('users ur', 'ur.id = fsp.rejected_by', 'left')
            ->join('karyawan kr', 'kr.id_user = ur.id', 'left')
            ->join('proyek p', 'p.id_proyek = fsp.id_proyek', 'left')
            ->join('(
                SELECT pi.id_pengajuan,
                       COUNT(DISTINCT fs.id_mkdt) AS total_unit,
                       COUNT(pi.id) AS total_item,
                       SUM(pi.nominal) AS total_nominal,
                       MAX(pi.suggested_harga_net_ajb) AS max_suggested_harga,
                       SUM(CASE WHEN fst.status = "sudah_cair" THEN 1 ELSE 0 END) AS total_cair,
                       GROUP_CONCAT(DISTINCT COALESCE(fs.sales_nama, "Sales") SEPARATOR ", ") AS sales_names
                FROM fee_sales_pengajuan_item pi
                JOIN fee_sales fs ON fs.id = pi.id_fee_sales
                LEFT JOIN fee_sales_termin fst ON fst.id = pi.id_termin
                GROUP BY pi.id_pengajuan
            ) stat', 'stat.id_pengajuan = fsp.id', 'left');

        if (! empty($params['id_proyek'])) {
            $builder->where('fsp.id_proyek', (int) $params['id_proyek']);
        }

        if (! empty($params['status']) && $params['status'] !== 'all') {
            $builder->where('fsp.status', $params['status']);
        }

        if (! empty($params['search'])) {
            $s = $params['search'];
            $builder->groupStart()
                ->like('fsp.nomor_pengajuan', $s)
                ->orLike('fsp.catatan', $s)
                ->orLike('fsp.reject_reason', $s)
                ->orLike('u.username', $s)
                ->orLike('ur.username', $s)
                ->orLike('fsp.ttd_pembuat_nama', $s)
                ->orLike('fsp.tanggal_pengajuan_sales', $s)
                ->orLike('fsp.tanggal_pengajuan', $s)
                ->orLike('stat.sales_names', $s)
                ->groupEnd();
        }

        $totalFiltered = (clone $builder)->countAllResults();

        $start  = (int) ($params['start'] ?? 0);
        $length = (int) ($params['length'] ?? 10);
        if ($length > 0) {
            $builder->limit($length, $start);
        }

        $builder->orderBy('fsp.tanggal_pengajuan', 'DESC');
        $builder->orderBy('fsp.id', 'DESC');
        $data = $builder->get()->getResult();

        return [
            'total' => $totalFiltered,
            'data'  => $data,
        ];
    }

    /**
     * Ambil detail lengkap grup pengajuan (header + items + detail kavling + termin)
     */
    public function getPengajuanGroupDetail(int $idPengajuan): ?object
    {
        $group = $this->db->table('fee_sales_pengajuan fsp')
            ->select('fsp.*, u.username AS creator_username, p.nama_proyek, p.kode_keuangan, uv.username AS voided_by_name, COALESCE(NULLIF(kr.nama_karyawan, ""), NULLIF(ur.name, ""), ur.username) AS rejected_by_name', false)
            ->join('users u', 'u.id = fsp.created_by', 'left')
            ->join('users uv', 'uv.id = fsp.voided_by', 'left')
            ->join('users ur', 'ur.id = fsp.rejected_by', 'left')
            ->join('karyawan kr', 'kr.id_user = ur.id', 'left')
            ->join('proyek p', 'p.id_proyek = fsp.id_proyek', 'left')
            ->where('fsp.id', $idPengajuan)
            ->get()
            ->getRow();

        if (! $group) {
            return null;
        }

        if (empty($group->share_token)) {
            $token = bin2hex(random_bytes(16));
            $this->db->table('fee_sales_pengajuan')
                ->where('id', $idPengajuan)
                ->update(['share_token' => $token]);
            $group->share_token = $token;
        }

        $items = $this->db->table('fee_sales_pengajuan_item pi')
            ->select([
                'pi.id AS id_pengajuan_item',
                'pi.id_pengajuan',
                'pi.id_fee_sales',
                'pi.id_termin',
                'pi.termin_ke AS termin_yang_diajukan',
                'pi.nominal AS nominal_diajukan',
                'pi.suggested_harga_net_ajb',

                'fst.persen AS termin_persen',
                'fst.nominal AS termin_nominal',
                'fst.status AS termin_status',
                'fst.nomor_spp AS termin_nomor_spp',
                'fst.tanggal_pengajuan_spp AS termin_tgl_spp',
                'fst.tanggal_cair AS termin_tgl_cair',
                'fst.no_cek AS termin_no_cek',
                'fst.keterangan AS termin_keterangan',
                'fst.lampiran_spp AS termin_lampiran_spp',
                'fst.lampiran_bukti_cair AS termin_lampiran_bukti_cair',

                'fs.id_mkdt',
                'fs.id_sales',
                'fs.sales_nama',
                'fs.sales_keterangan',
                'fs.harga_net_ajb',
                'fs.harga_net_ajb AS fee_sales_harga_net_ajb',
                'fs.harga_net_ajb AS fee_harga_net_ajb',
                'fs.persentase_fee',
                'fs.total_fee',
                'fs.skema_termin',
                'fs.status AS fee_status',

                'm.id_kavling',
                'm.id_konsumen',
                'm.is_kpr',
                'm.is_ajb',
                'm.is_subsidi',
                'm.akad',
                'm.akad_indent',
                'm.akad_indent_tgl',
                'm.booking_tgl',
                'm.akad_tgl',
                'm.harga_jual',
                'm.harga_jual_net',
                'm.harga_net_ajb',
                'm.harga_diskon_hargajual',
                'm.harga_penambahan_tanah',

                'k.nama_konsumen',
                'k.hp_konsumen',
                'k.nama_instansi',
                'kv.no_kavling',
                'j.nama_jalan',
                'c.id_proyek AS item_id_proyek',
                'COALESCE(NULLIF(hj.id_tipe, ""), t.tipe_rumah) AS tipe_rumah',
                's.bank AS sales_bank',
                's.no_rek AS sales_no_rek',
                'COALESCE(s.has_pks, 0) AS sales_has_pks',
            ])
            ->join('fee_sales fs', 'fs.id = pi.id_fee_sales')
            ->join('fee_sales_termin fst', 'fst.id = pi.id_termin', 'left')
            ->join('mkdt m', 'm.id_mkdt = fs.id_mkdt')
            ->join('konsumen k', 'k.id_konsumen = m.id_konsumen')
            ->join('kavling kv', 'kv.id_kavling = m.id_kavling')
            ->join('jalan j', 'j.id_jalan = kv.id_jalan', 'left')
            ->join('cluster c', 'c.id_cluster = j.id_cluster', 'left')
            ->join('tipe t', 't.id_tipe = kv.id_tipe', 'left')
            ->join('hargajual hj', 'hj.id = COALESCE(NULLIF(m.id_hargajual, 0), kv.harga_akhir)', 'left')
            ->join('sales s', 's.id = fs.id_sales', 'left')
            ->where('pi.id_pengajuan', $idPengajuan)
            ->orderBy('fs.sales_nama', 'ASC')
            ->orderBy('m.id_mkdt', 'ASC')
            ->get()
            ->getResult();

        // Ambil semua termin dari setiap fee_sales untuk render kolom termin 30/60/10/100 di PDF/UI
        $feeSalesIds = array_unique(array_column($items, 'id_fee_sales'));
        $allTermins = [];
        if (! empty($feeSalesIds)) {
            $tList = $this->db->table('fee_sales_termin fst')
                ->select([
                    'fst.*',
                    'pi.id_pengajuan',
                    'fsp.nomor_pengajuan AS pengajuan_nomor',
                    'fsp.status AS pengajuan_status',
                    'fsp.tanggal_pengajuan AS pengajuan_tanggal',
                ])
                ->join('fee_sales_pengajuan_item pi', 'pi.id_termin = fst.id', 'left')
                ->join('fee_sales_pengajuan fsp', 'fsp.id = pi.id_pengajuan AND fsp.status != "void"', 'left')
                ->whereIn('fst.id_fee_sales', $feeSalesIds)
                ->orderBy('fst.termin_ke', 'ASC')
                ->get()
                ->getResult();
            foreach ($tList as $t) {
                $allTermins[$t->id_fee_sales][$t->termin_ke] = $t;
            }
        }

        $mkdtIds = array_unique(array_filter(array_map(static fn($it) => (int) ($it->id_mkdt ?? 0), $items)));
        $cairMap = [];
        foreach ($mkdtIds as $mId) {
            $cairMap[$mId] = $this->hasCairTerminByMkdt($mId);
        }

        foreach ($items as &$item) {
            $item->all_termin = $allTermins[$item->id_fee_sales] ?? [];
            $item->has_cair_termin = $cairMap[(int) $item->id_mkdt] ?? false;
        }

        $group->items = $items;
        $group->total_nominal = (float) array_sum(array_map(static fn($it) => (float) ($it->nominal_diajukan ?? 0), $items));
        $group->total_unit    = count($mkdtIds);
        $group->has_cair_termin = in_array(true, $cairMap, true);

        // Backward compatibility aliases
        $group->creator_name    = $group->creator_name ?? $group->creator_username ?? null;
        $group->ttd_mkdt_id     = $group->ttd_mkdt_by ?? null;
        $group->ttd_keuangan_id = $group->ttd_keuangan_by ?? null;

        return $group;
    }

    /**
     * Ambil detail pengajuan berdasarkan share_token (atau fallback numeric ID)
     */
    public function getPengajuanGroupByToken(string $tokenOrId): ?object
    {
        $tokenOrId = trim($tokenOrId);
        if ($tokenOrId === '') {
            return null;
        }

        $builder = $this->db->table('fee_sales_pengajuan')
            ->select('id');

        if (ctype_digit($tokenOrId)) {
            $builder->groupStart()
                ->where('share_token', $tokenOrId)
                ->orWhere('id', (int) $tokenOrId)
                ->groupEnd();
        } else {
            $builder->where('share_token', $tokenOrId);
        }

        $row = $builder->get()->getRow();
        if (! $row) {
            return null;
        }

        return $this->getPengajuanGroupDetail((int) $row->id);
    }
}

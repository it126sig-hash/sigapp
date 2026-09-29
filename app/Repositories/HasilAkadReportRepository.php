<?php

namespace App\Repositories;

use CodeIgniter\Database\BaseConnection;

class HasilAkadReportRepository
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
    }

    public function getAvailableYearBounds(int $idProyek): array
    {
        $bounds = [
            $this->yearBounds($this->baseMkdtQuery($idProyek), 'm.akad_tgl'),
            $this->yearBounds($this->basePengajuanQuery($idProyek), 'pg.tanggal_pengajuan'),
            $this->yearBounds($this->basePaymentQuery($idProyek), 'pay.tanggal_cair'),
        ];

        $minimums = array_filter(array_column($bounds, 'min'));
        $maximums = array_filter(array_column($bounds, 'max'));

        return [
            'min' => $minimums ? min($minimums) : null,
            'max' => $maximums ? max($maximums) : null,
        ];
    }

    public function getMonthlyAkadTotals(int $idProyek, array $years): array
    {
        [$startDate, $endDate] = $this->yearDateRange($years);

        return $this->baseMkdtQuery($idProyek)
            ->select('YEAR(m.akad_tgl) AS tahun, MONTH(m.akad_tgl) AS bulan', false)
            ->selectSum('m.harga_jual_net', 'total_penjualan')
            ->select('COUNT(DISTINCT m.id_kavling) AS jumlah_kavling', false)
            ->selectSum('m.harga_kpr_acc', 'total_acc_kpr')
            ->where('m.akad_tgl >=', $startDate)
            ->where('m.akad_tgl <', $endDate)
            ->groupBy('YEAR(m.akad_tgl), MONTH(m.akad_tgl)', false)
            ->get()
            ->getResultArray();
    }

    public function getMonthlyPengajuanTotals(int $idProyek, array $years): array
    {
        [$startDate, $endDate] = $this->yearDateRange($years);

        return $this->basePengajuanQuery($idProyek)
            ->select('YEAR(pg.tanggal_pengajuan) AS tahun, MONTH(pg.tanggal_pengajuan) AS bulan', false)
            ->selectSum('pg.total_pengajuan', 'total_pengajuan')
            ->where('pg.tanggal_pengajuan >=', $startDate)
            ->where('pg.tanggal_pengajuan <', $endDate)
            ->groupBy('YEAR(pg.tanggal_pengajuan), MONTH(pg.tanggal_pengajuan)', false)
            ->get()
            ->getResultArray();
    }

    public function getMonthlyPaymentTotals(int $idProyek, array $years): array
    {
        [$startDate, $endDate] = $this->yearDateRange($years);

        return $this->basePaymentQuery($idProyek)
            ->select('YEAR(pay.tanggal_cair) AS tahun, MONTH(pay.tanggal_cair) AS bulan', false)
            ->selectSum('pay.total_cair', 'total_cair')
            ->where('pay.tanggal_cair >=', $startDate)
            ->where('pay.tanggal_cair <', $endDate)
            ->groupBy('YEAR(pay.tanggal_cair), MONTH(pay.tanggal_cair)', false)
            ->get()
            ->getResultArray();
    }

    public function getDetailRows(int $idProyek, int $year, int $month, string $category, array $dataTable): array
    {
        $compiled = $this->compileDetailQuery($idProyek, $year, $month, $category);
        $baseSql = "({$compiled}) hasil_akad";

        $recordsTotal = $this->db->table($baseSql)->countAllResults();
        $builder = $this->db->table($baseSql);

        if ($dataTable['search'] !== '') {
            $builder->groupStart()
                ->like('hasil_akad.jenis_laporan', $dataTable['search'])
                ->orLike('hasil_akad.alamat_kavling', $dataTable['search'])
                ->orLike('hasil_akad.nama_konsumen', $dataTable['search'])
                ->orLike('hasil_akad.tanggal_transaksi', $dataTable['search'])
                ->groupEnd();
        }

        $countBuilder = clone $builder;
        $recordsFiltered = $countBuilder->countAllResults();

        $builder->orderBy($dataTable['order_by'], $dataTable['order_dir']);
        $builder->orderBy('hasil_akad.reference_id', 'ASC');
        $builder->limit($dataTable['length'], $dataTable['start']);

        return [
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $builder->get()->getResultArray(),
        ];
    }

    private function compileDetailQuery(int $idProyek, int $year, int $month, string $category): string
    {
        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = date('Y-m-d', strtotime($startDate . ' +1 month'));

        if ($category === 'penjualan') {
            return $this->baseMkdtQuery($idProyek)
                ->select('m.id_mkdt AS reference_id')
                ->select("'penjualan' AS kategori", false)
                ->select("'Total Penjualan' AS jenis_laporan", false)
                ->select($this->addressSql())
                ->select("COALESCE(c.nama_konsumen, '-') AS nama_konsumen", false)
                ->select('m.akad_tgl AS tanggal_transaksi')
                ->select('m.harga_jual_net AS nominal')
                ->select('m.id_kavling, m.id_mkdt')
                ->select("COALESCE(j.nama_jalan, '') AS nama_jalan", false)
                ->select("COALESCE(k.no_kavling, '-') AS no_kavling", false)
                ->where('m.akad_tgl >=', $startDate)
                ->where('m.akad_tgl <', $endDate)
                ->getCompiledSelect();
        }

        if ($category === 'acc_kpr') {
            return $this->baseMkdtQuery($idProyek)
                ->select('m.id_mkdt AS reference_id')
                ->select("'acc_kpr' AS kategori", false)
                ->select("'Total ACC KPR' AS jenis_laporan", false)
                ->select($this->addressSql())
                ->select("COALESCE(c.nama_konsumen, '-') AS nama_konsumen", false)
                ->select('m.akad_tgl AS tanggal_transaksi')
                ->select('m.harga_kpr_acc AS nominal')
                ->select('m.id_kavling, m.id_mkdt')
                ->select("COALESCE(j.nama_jalan, '') AS nama_jalan", false)
                ->select("COALESCE(k.no_kavling, '-') AS no_kavling", false)
                ->where('m.akad_tgl >=', $startDate)
                ->where('m.akad_tgl <', $endDate)
                ->getCompiledSelect();
        }

        if ($category === 'pengajuan') {
            return $this->basePengajuanQuery($idProyek)
                ->select('pg.id AS reference_id')
                ->select("'pengajuan' AS kategori", false)
                ->select("'Total Pengajuan Pencairan' AS jenis_laporan", false)
                ->select($this->addressSql())
                ->select("COALESCE(c.nama_konsumen, '-') AS nama_konsumen", false)
                ->select('pg.tanggal_pengajuan AS tanggal_transaksi')
                ->select('pg.total_pengajuan AS nominal')
                ->select('m.id_kavling, m.id_mkdt')
                ->select("COALESCE(j.nama_jalan, '') AS nama_jalan", false)
                ->select("COALESCE(k.no_kavling, '-') AS no_kavling", false)
                ->where('pg.tanggal_pengajuan >=', $startDate)
                ->where('pg.tanggal_pengajuan <', $endDate)
                ->getCompiledSelect();
        }

        return $this->basePaymentQuery($idProyek)
            ->select('pay.id AS reference_id')
            ->select("'cair' AS kategori", false)
            ->select("'Total Cair Hasil Akad' AS jenis_laporan", false)
            ->select($this->addressSql())
            ->select("COALESCE(c.nama_konsumen, '-') AS nama_konsumen", false)
            ->select('pay.tanggal_cair AS tanggal_transaksi')
            ->select('pay.total_cair AS nominal')
            ->select('m.id_kavling, m.id_mkdt')
            ->select("COALESCE(j.nama_jalan, '') AS nama_jalan", false)
            ->select("COALESCE(k.no_kavling, '-') AS no_kavling", false)
            ->where('pay.tanggal_cair >=', $startDate)
            ->where('pay.tanggal_cair <', $endDate)
            ->getCompiledSelect();
    }

    private function baseMkdtQuery(int $idProyek)
    {
        return $this->db->table('mkdt m')
            ->join('kavling k', 'k.id_kavling = m.id_kavling')
            ->join('jalan j', 'j.id_jalan = k.id_jalan')
            ->join('cluster cl', 'cl.id_cluster = j.id_cluster')
            ->join('konsumen c', 'c.id_konsumen = m.id_konsumen', 'left')
            ->where('cl.id_proyek', $idProyek)
            ->where('m.status_mkdt', 'Akad')
            ->where('m.is_kpr', 1);
    }

    private function basePengajuanQuery(int $idProyek)
    {
        return $this->db->table('pencairan_akad_pengajuan pg')
            ->join('pencairan_akad_plan plan', 'plan.id = pg.id_plan')
            ->join('mkdt m', 'm.id_mkdt = plan.id_mkdt')
            ->join('kavling k', 'k.id_kavling = plan.id_kavling')
            ->join('jalan j', 'j.id_jalan = k.id_jalan')
            ->join('cluster cl', 'cl.id_cluster = j.id_cluster')
            ->join('konsumen c', 'c.id_konsumen = m.id_konsumen', 'left')
            ->where('cl.id_proyek', $idProyek)
            ->where('m.status_mkdt', 'Akad')
            ->where('m.is_kpr', 1)
            ->where('pg.status !=', 'void');
    }

    private function basePaymentQuery(int $idProyek)
    {
        return $this->db->table('pencairan_akad_payment pay')
            ->join('pencairan_akad_pengajuan pg', 'pg.id = pay.id_pengajuan')
            ->join('pencairan_akad_plan plan', 'plan.id = pg.id_plan')
            ->join('mkdt m', 'm.id_mkdt = plan.id_mkdt')
            ->join('kavling k', 'k.id_kavling = plan.id_kavling')
            ->join('jalan j', 'j.id_jalan = k.id_jalan')
            ->join('cluster cl', 'cl.id_cluster = j.id_cluster')
            ->join('konsumen c', 'c.id_konsumen = m.id_konsumen', 'left')
            ->where('cl.id_proyek', $idProyek)
            ->where('m.status_mkdt', 'Akad')
            ->where('m.is_kpr', 1)
            ->where('pg.status !=', 'void')
            ->where('pay.total_cair >', 0);
    }

    private function yearBounds($builder, string $dateColumn): array
    {
        $row = $builder
            ->select("MIN(YEAR({$dateColumn})) AS min_year, MAX(YEAR({$dateColumn})) AS max_year", false)
            ->where("{$dateColumn} >=", '1900-01-01')
            ->get()
            ->getRowArray() ?? [];

        return [
            'min' => isset($row['min_year']) ? (int) $row['min_year'] : null,
            'max' => isset($row['max_year']) ? (int) $row['max_year'] : null,
        ];
    }

    private function addressSql(): string
    {
        return "TRIM(CONCAT(COALESCE(j.nama_jalan, ''), ' No. ', COALESCE(k.no_kavling, '-'))) AS alamat_kavling";
    }

    private function yearDateRange(array $years): array
    {
        return [
            sprintf('%04d-01-01', min($years)),
            sprintf('%04d-01-01', max($years) + 1),
        ];
    }
}

<?php

namespace App\Repositories;

use CodeIgniter\Database\BaseConnection;

class BookingAkadReportRepository
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
    }

    public function getAvailableYearBounds(int $idProyek): array
    {
        $bounds = [
            $this->yearBounds($this->baseMkdtQuery($idProyek), 'm.booking_tgl'),
            $this->yearBounds($this->baseMkdtQuery($idProyek), 'm.akad_tgl'),
        ];

        $minimums = array_filter(array_column($bounds, 'min'));
        $maximums = array_filter(array_column($bounds, 'max'));

        return [
            'min' => $minimums ? min($minimums) : null,
            'max' => $maximums ? max($maximums) : null,
        ];
    }

    public function getMonthlyBookingTotals(int $idProyek, array $years): array
    {
        [$startDate, $endDate] = $this->yearDateRange($years);

        return $this->baseMkdtQuery($idProyek)
            ->select('YEAR(m.booking_tgl) AS tahun, MONTH(m.booking_tgl) AS bulan', false)
            ->select('COUNT(m.id_mkdt) AS jumlah_booking', false)
            ->select("SUM(CASE WHEN (m.is_batal = 0 OR m.is_batal IS NULL) AND (m.status_mkdt != 'Batal' OR m.status_mkdt IS NULL) THEN 1 ELSE 0 END) AS booking_aktif", false)
            ->select("SUM(CASE WHEN m.is_batal = 1 OR m.status_mkdt = 'Batal' THEN 1 ELSE 0 END) AS booking_batal", false)
            ->where('m.booking_tgl IS NOT NULL')
            ->where('m.booking_tgl >=', $startDate)
            ->where('m.booking_tgl <', $endDate)
            ->groupBy('YEAR(m.booking_tgl), MONTH(m.booking_tgl)', false)
            ->get()
            ->getResultArray();
    }

    public function getMonthlyAkadTotals(int $idProyek, array $years): array
    {
        [$startDate, $endDate] = $this->yearDateRange($years);

        return $this->baseMkdtQuery($idProyek)
            ->select('YEAR(m.akad_tgl) AS tahun, MONTH(m.akad_tgl) AS bulan', false)
            ->select('COUNT(m.id_mkdt) AS jumlah_akad', false)
            ->where('m.akad_tgl IS NOT NULL')
            ->groupStart()
                ->where('m.status_mkdt', 'Akad')
                ->orWhere('m.akad', 1)
            ->groupEnd()
            ->where('m.akad_tgl >=', $startDate)
            ->where('m.akad_tgl <', $endDate)
            ->groupBy('YEAR(m.akad_tgl), MONTH(m.akad_tgl)', false)
            ->get()
            ->getResultArray();
    }

    public function getDetailRows(int $idProyek, int $year, int $month, string $category, array $dataTable): array
    {
        $compiled = $this->compileDetailQuery($idProyek, $year, $month, $category);
        $baseSql = "({$compiled}) booking_akad";

        $recordsTotal = $this->db->table($baseSql)->countAllResults();
        $builder = $this->db->table($baseSql);

        if ($dataTable['search'] !== '') {
            $builder->groupStart()
                ->like('booking_akad.alamat_kavling', $dataTable['search'])
                ->orLike('booking_akad.nama_konsumen', $dataTable['search'])
                ->orLike('booking_akad.tipe_rumah', $dataTable['search'])
                ->orLike('booking_akad.jenis_laporan', $dataTable['search'])
                ->orLike('booking_akad.status_mkdt', $dataTable['search'])
                ->orLike('booking_akad.tanggal_transaksi', $dataTable['search'])
                ->groupEnd();
        }

        $countBuilder = clone $builder;
        $recordsFiltered = $countBuilder->countAllResults();

        $builder->orderBy($dataTable['order_by'], $dataTable['order_dir']);
        $builder->orderBy('booking_akad.reference_id', 'ASC');
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

        $builder = $this->baseMkdtDetailQuery($idProyek)
            ->select('m.id_mkdt AS reference_id')
            ->select('m.id_mkdt, m.id_kavling')
            ->select($this->addressSql())
            ->select("COALESCE(k.no_kavling, '-') AS no_kavling", false)
            ->select("COALESCE(j.nama_jalan, '') AS nama_jalan", false)
            ->select("COALESCE(cl.nama_cluster, '') AS nama_cluster", false)
            ->select("COALESCE(p.nama_proyek, '') AS nama_proyek", false)
            ->select("COALESCE(c.nama_konsumen, '-') AS nama_konsumen", false)
            ->select("COALESCE(t.tipe_rumah, '-') AS tipe_rumah", false)
            ->select("COALESCE(m.status_mkdt, '-') AS status_mkdt", false)
            ->select("COALESCE(m.is_batal, 0) AS is_batal", false)
            ->select("COALESCE(m.keterangan_batal, '') AS keterangan_batal", false)
            ->select('m.booking_tgl')
            ->select('m.akad_tgl')
            ->select("COALESCE(m.booking_fee, 0) AS booking_fee", false)
            ->select("COALESCE(m.harga_jual_net, 0) AS harga_jual_net", false)
            ->select("COALESCE(m.harga_kpr_acc, 0) AS harga_kpr_acc", false);

        if ($category === 'booking_aktif') {
            return $builder
                ->select("'booking_aktif' AS kategori", false)
                ->select("'Booking Aktif' AS jenis_laporan", false)
                ->select('m.booking_tgl AS tanggal_transaksi')
                ->select("COALESCE(m.booking_fee, 0) AS nominal", false)
                ->where('m.booking_tgl IS NOT NULL')
                ->where('m.booking_tgl >=', $startDate)
                ->where('m.booking_tgl <', $endDate)
                ->groupStart()
                    ->where('m.is_batal', 0)
                    ->orWhere('m.is_batal IS NULL', null, false)
                ->groupEnd()
                ->groupStart()
                    ->where('m.status_mkdt !=', 'Batal')
                    ->orWhere('m.status_mkdt IS NULL', null, false)
                ->groupEnd()
                ->getCompiledSelect();
        }

        if ($category === 'booking_batal') {
            return $builder
                ->select("'booking_batal' AS kategori", false)
                ->select("'Booking Batal' AS jenis_laporan", false)
                ->select('m.booking_tgl AS tanggal_transaksi')
                ->select("COALESCE(m.booking_fee, 0) AS nominal", false)
                ->where('m.booking_tgl IS NOT NULL')
                ->where('m.booking_tgl >=', $startDate)
                ->where('m.booking_tgl <', $endDate)
                ->groupStart()
                    ->where('m.is_batal', 1)
                    ->orWhere('m.status_mkdt', 'Batal')
                ->groupEnd()
                ->getCompiledSelect();
        }

        if ($category === 'akad') {
            return $builder
                ->select("'akad' AS kategori", false)
                ->select("'Akad' AS jenis_laporan", false)
                ->select('m.akad_tgl AS tanggal_transaksi')
                ->select("COALESCE(m.harga_jual_net, 0) AS nominal", false)
                ->where('m.akad_tgl IS NOT NULL')
                ->where('m.akad_tgl >=', $startDate)
                ->where('m.akad_tgl <', $endDate)
                ->groupStart()
                    ->where('m.status_mkdt', 'Akad')
                    ->orWhere('m.akad', 1)
                ->groupEnd()
                ->getCompiledSelect();
        }

        // Default: category === 'booking' (semua booking)
        return $builder
            ->select("'booking' AS kategori", false)
            ->select("'Booking' AS jenis_laporan", false)
            ->select('m.booking_tgl AS tanggal_transaksi')
            ->select("COALESCE(m.booking_fee, 0) AS nominal", false)
            ->where('m.booking_tgl IS NOT NULL')
            ->where('m.booking_tgl >=', $startDate)
            ->where('m.booking_tgl <', $endDate)
            ->getCompiledSelect();
    }

    private function baseMkdtQuery(int $idProyek)
    {
        return $this->db->table('mkdt m')
            ->join('kavling k', 'k.id_kavling = m.id_kavling')
            ->join('jalan j', 'j.id_jalan = k.id_jalan')
            ->join('cluster cl', 'cl.id_cluster = j.id_cluster')
            ->join('proyek p', 'p.id_proyek = cl.id_proyek')
            ->where('p.id_proyek', $idProyek);
    }

    private function baseMkdtDetailQuery(int $idProyek)
    {
        return $this->db->table('mkdt m')
            ->join('kavling k', 'k.id_kavling = m.id_kavling')
            ->join('jalan j', 'j.id_jalan = k.id_jalan')
            ->join('cluster cl', 'cl.id_cluster = j.id_cluster')
            ->join('proyek p', 'p.id_proyek = cl.id_proyek')
            ->join('konsumen c', 'c.id_konsumen = m.id_konsumen', 'left')
            ->join('tipe t', 't.id_tipe = k.id_tipe', 'left')
            ->where('p.id_proyek', $idProyek);
    }

    private function yearBounds($builder, string $dateColumn): array
    {
        $row = $builder
            ->select("MIN(YEAR({$dateColumn})) AS min_year, MAX(YEAR({$dateColumn})) AS max_year", false)
            ->where("{$dateColumn} IS NOT NULL")
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

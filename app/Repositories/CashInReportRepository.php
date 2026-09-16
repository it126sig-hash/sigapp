<?php

namespace App\Repositories;

use CodeIgniter\Database\BaseConnection;

class CashInReportRepository
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
    }

    public function getAvailableYearBounds(int $idProyek): array
    {
        $payment = $this->baseLogPaymentDetailQuery($idProyek)
            ->select('MIN(YEAR(lp.tanggal_bayar)) AS min_year, MAX(YEAR(lp.tanggal_bayar)) AS max_year', false)
            ->where('lp.tanggal_bayar >=', '1900-01-01')
            ->get()
            ->getRowArray();

        $akad = $this->baseAkadPaymentQuery($idProyek)
            ->select('MIN(YEAR(pay.tanggal_cair)) AS min_year, MAX(YEAR(pay.tanggal_cair)) AS max_year', false)
            ->where('pay.tanggal_cair >=', '1900-01-01')
            ->get()
            ->getRowArray();

        $minimums = array_filter([
            isset($payment['min_year']) ? (int) $payment['min_year'] : null,
            isset($akad['min_year']) ? (int) $akad['min_year'] : null,
        ]);
        $maximums = array_filter([
            isset($payment['max_year']) ? (int) $payment['max_year'] : null,
            isset($akad['max_year']) ? (int) $akad['max_year'] : null,
        ]);

        return [
            'min' => $minimums ? min($minimums) : null,
            'max' => $maximums ? max($maximums) : null,
        ];
    }

    public function getMonthlyPaymentTotals(int $idProyek, array $years): array
    {
        [$startDate, $endDate] = $this->yearDateRange($years);

        return $this->baseLogPaymentDetailQuery($idProyek)
            ->select("YEAR(lp.tanggal_bayar) AS tahun, MONTH(lp.tanggal_bayar) AS bulan")
            ->select("SUM(CASE WHEN kl.kategori = 'BO' AND COALESCE(lpd.booking_is_installment,0) = 0 THEN lpd.nominal ELSE 0 END) AS booking_fee", false)
            ->select("SUM(CASE WHEN kl.kategori != 'BO' OR COALESCE(lpd.booking_is_installment,0) = 1 THEN lpd.nominal ELSE 0 END) AS uang_muka", false)
            ->where('lp.tanggal_bayar >=', $startDate)
            ->where('lp.tanggal_bayar <', $endDate)
            ->groupBy('YEAR(lp.tanggal_bayar), MONTH(lp.tanggal_bayar)', false)
            ->get()
            ->getResultArray();
    }

    public function getMonthlyAkadTotals(int $idProyek, array $years): array
    {
        [$startDate, $endDate] = $this->yearDateRange($years);

        return $this->baseAkadPaymentQuery($idProyek)
            ->select('YEAR(pay.tanggal_cair) AS tahun, MONTH(pay.tanggal_cair) AS bulan', false)
            ->selectSum('pay.total_cair', 'hasil_akad')
            ->where('pay.tanggal_cair >=', $startDate)
            ->where('pay.tanggal_cair <', $endDate)
            ->groupBy('YEAR(pay.tanggal_cair), MONTH(pay.tanggal_cair)', false)
            ->get()
            ->getResultArray();
    }

    public function getDetailRows(
        int $idProyek,
        int $year,
        int $month,
        string $category,
        array $dataTable
    ): array {
        $compiled = $this->compileDetailQuery($idProyek, $year, $month, $category);
        $baseSql = "({$compiled}) cash_in";

        $recordsTotal = $this->db->table($baseSql)->countAllResults();
        $builder = $this->db->table($baseSql);

        if ($dataTable['search'] !== '') {
            $builder->groupStart()
                ->like('cash_in.jenis_pendapatan', $dataTable['search'])
                ->orLike('cash_in.alamat_kavling', $dataTable['search'])
                ->orLike('cash_in.nama_konsumen', $dataTable['search'])
                ->orLike('cash_in.keterangan', $dataTable['search'])
                ->orLike('cash_in.tanggal_transaksi', $dataTable['search'])
                ->groupEnd();
        }

        $countBuilder = clone $builder;
        $recordsFiltered = $countBuilder->countAllResults();

        $builder->orderBy($dataTable['order_by'], $dataTable['order_dir']);
        $builder->orderBy('cash_in.reference_id', 'ASC');
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
        $queries = [];

        if ($category === 'booking_fee' || $category === 'all') {
            $queries[] = $this->compileLogPaymentDetailQuery(
                $idProyek,
                $startDate,
                $endDate,
                'booking_fee',
                'Booking Fee'
            );
        }

        if ($category === 'uang_muka' || $category === 'all') {
            $queries[] = $this->compileLogPaymentDetailQuery(
                $idProyek,
                $startDate,
                $endDate,
                'uang_muka',
                'Uang Muka'
            );
        }

        if ($category === 'hasil_akad' || $category === 'all') {
            $queries[] = $this->compileAkadDetailQuery($idProyek, $startDate, $endDate);
        }

        return implode(' UNION ALL ', $queries);
    }

    private function compileLogPaymentDetailQuery(
        int $idProyek,
        string $startDate,
        string $endDate,
        string $category,
        string $label
    ): string {
        $builder = $this->baseLogPaymentDetailQuery($idProyek)
            ->select('lpd.id_pembayaran_detail AS reference_id')
            ->select($this->db->escape($category) . ' AS kategori', false)
            ->select($this->db->escape($label) . ' AS jenis_pendapatan', false)
            ->select("TRIM(CONCAT(COALESCE(j.nama_jalan, ''), ' No. ', COALESCE(k.no_kavling, '-'))) AS alamat_kavling", false)
            ->select("COALESCE(c.nama_konsumen, '-') AS nama_konsumen", false)
            ->select("COALESCE(kl.item, '-') AS keterangan", false)
            ->select('lp.tanggal_bayar AS tanggal_transaksi')
            ->select('lpd.nominal')
            ->where('lp.tanggal_bayar >=', $startDate)
            ->where('lp.tanggal_bayar <', $endDate);

        if ($category === 'booking_fee') {
            $builder->where('kl.kategori', 'BO')->where('lpd.booking_is_installment', 0);
        } else {
            $builder->groupStart()->where('kl.kategori !=', 'BO')->orWhere('lpd.booking_is_installment', 1)->groupEnd();
        }

        return $builder->getCompiledSelect();
    }

    private function compileAkadDetailQuery(int $idProyek, string $startDate, string $endDate): string
    {
        return $this->baseAkadPaymentQuery($idProyek)
            ->select('pay.id AS reference_id')
            ->select("'hasil_akad' AS kategori", false)
            ->select("'Hasil Akad' AS jenis_pendapatan", false)
            ->select("TRIM(CONCAT(COALESCE(j.nama_jalan, ''), ' No. ', COALESCE(k.no_kavling, '-'))) AS alamat_kavling", false)
            ->select("COALESCE(c.nama_konsumen, '-') AS nama_konsumen", false)
            ->select("COALESCE(NULLIF(pay.catatan, ''), 'Hasil Akad') AS keterangan", false)
            ->select('pay.tanggal_cair AS tanggal_transaksi')
            ->select('pay.total_cair AS nominal')
            ->where('pay.tanggal_cair >=', $startDate)
            ->where('pay.tanggal_cair <', $endDate)
            ->getCompiledSelect();
    }

    private function baseLogPaymentDetailQuery(int $idProyek)
    {
        return $this->db->table('log_pembayaran_detail lpd')
            ->join('log_pembayaran lp', 'lp.id_pembayaran = lpd.id_pembayaran')
            ->join('keuangan_item_list kl', 'kl.id_keuangan_item_list = lpd.id_keuangan_item_list')
            ->join('mkdt m', 'm.id_mkdt = lp.id_mkdt')
            ->join('kavling k', 'k.id_kavling = m.id_kavling')
            ->join('jalan j', 'j.id_jalan = k.id_jalan')
            ->join('cluster cl', 'cl.id_cluster = j.id_cluster')
            ->join('konsumen c', 'c.id_konsumen = m.id_konsumen', 'left')
            ->where('cl.id_proyek', $idProyek)
            ->where('lp.is_deleted', 0)
            ->where("COALESCE(lp.payment_type, '') != 'Refund'", null, false)
            ->where('lpd.nominal >', 0);
    }

    private function baseAkadPaymentQuery(int $idProyek)
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
            ->where('pay.total_cair >', 0);
    }

    private function yearDateRange(array $years): array
    {
        $minimum = min($years);
        $maximum = max($years);

        return [sprintf('%04d-01-01', $minimum), sprintf('%04d-01-01', $maximum + 1)];
    }
}

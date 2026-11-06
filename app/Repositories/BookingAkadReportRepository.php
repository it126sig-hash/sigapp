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

    public function getAvailableYears(int $idProyek): array
    {
        $bounds = $this->getAvailableYearBounds($idProyek);
        $currentYear = (int) date('Y');
        $minimum = min((int) ($bounds['min'] ?? $currentYear - 1), $currentYear - 1);
        $maximum = max((int) ($bounds['max'] ?? $currentYear), $currentYear);

        return range($maximum, $minimum);
    }

    public function getAvailableYearBounds(int $idProyek): array
    {
        $batalDateExpr = $this->batalDateSql();
        $wawancaraDateExpr = $this->stageDateSql('m.wawancara_tgl');
        $sp3kDateExpr = $this->stageDateSql('m.sp3k_tgl');
        $indentDateExpr = $this->stageDateSql('m.akad_indent_tgl');
        $ajbDateExpr = $this->stageDateSql('m.akad_tgl');

        $bounds = [
            $this->yearBounds($this->baseMkdtQuery($idProyek), 'm.booking_tgl'),
            $this->yearBounds(
                $this->baseMkdtQuery($idProyek)->where($this->wawancaraConditionSql(), null, false),
                $wawancaraDateExpr
            ),
            $this->yearBounds(
                $this->baseMkdtQuery($idProyek)->where($this->sp3kConditionSql(), null, false),
                $sp3kDateExpr
            ),
            $this->yearBounds(
                $this->baseMkdtQuery($idProyek)->where($this->akadIndentConditionSql(), null, false),
                $indentDateExpr
            ),
            $this->yearBounds(
                $this->baseMkdtQuery($idProyek)->where($this->akadAjbConditionSql(), null, false),
                $ajbDateExpr
            ),
            $this->yearBounds(
                $this->baseMkdtQuery($idProyek)->groupStart()
                    ->where('COALESCE(m.is_batal, 0)', 1)
                    ->orWhere("COALESCE(m.status_mkdt, '')", 'Batal')
                ->groupEnd(),
                $batalDateExpr
            ),
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

    public function getMonthlyStatusTotals(int $idProyek, array $years): array
    {
        [$startDate, $endDate] = $this->yearDateRange($years);

        $monthlyMap = [];
        foreach ($years as $year) {
            for ($month = 1; $month <= 12; $month++) {
                $monthlyMap[$year][$month] = [
                    'tahun'                  => $year,
                    'bulan'                  => $month,
                    'booking'                => 0,
                    'wawancara'              => 0,
                    'sp3k'                   => 0,
                    'batal'                  => 0,
                    'akad_indent'            => 0,
                    'akad_ajb'               => 0,
                    'total_booking'          => 0,
                    'total_booking_cohort'   => 0,
                    'booking_converted_akad' => 0,
                ];
            }
        }

        // 1. Booking & Cohort Conversion Metrics
        $bookingRows = $this->baseMkdtQuery($idProyek)
            ->select('YEAR(m.booking_tgl) AS tahun, MONTH(m.booking_tgl) AS bulan', false)
            ->select('COUNT(m.id_mkdt) AS total_booking', false)
            ->select('COUNT(m.id_mkdt) AS booking', false)
            ->select('COUNT(DISTINCT m.uniq_id) AS total_booking_cohort', false)
            ->select("COUNT(DISTINCT CASE 
                WHEN (COALESCE(m.is_batal, 0) = 0 AND COALESCE(m.status_mkdt, '') != 'Batal')
                     AND ({$this->akadAjbConditionSql()} OR {$this->akadIndentConditionSql()})
                THEN m.uniq_id 
            END) AS booking_converted_akad", false)
            ->where('m.booking_tgl IS NOT NULL')
            ->where('m.booking_tgl >', '1970-01-01')
            ->where('m.booking_tgl >=', $startDate)
            ->where('m.booking_tgl <', $endDate)
            ->groupBy('YEAR(m.booking_tgl), MONTH(m.booking_tgl)', false)
            ->get()
            ->getResultArray();

        foreach ($bookingRows as $row) {
            $y = (int) ($row['tahun'] ?? 0);
            $m = (int) ($row['bulan'] ?? 0);
            if (isset($monthlyMap[$y][$m])) {
                $monthlyMap[$y][$m]['booking']                = (int) ($row['booking'] ?? 0);
                $monthlyMap[$y][$m]['total_booking']          = (int) ($row['total_booking'] ?? 0);
                $monthlyMap[$y][$m]['total_booking_cohort']   = (int) ($row['total_booking_cohort'] ?? 0);
                $monthlyMap[$y][$m]['booking_converted_akad'] = (int) ($row['booking_converted_akad'] ?? 0);
            }
        }

        // 2. Wawancara Activity Metric
        $wawancaraDateExpr = $this->stageDateSql('m.wawancara_tgl');
        $wawancaraRows = $this->baseMkdtQuery($idProyek)
            ->select("YEAR({$wawancaraDateExpr}) AS tahun, MONTH({$wawancaraDateExpr}) AS bulan, COUNT(m.id_mkdt) AS wawancara", false)
            ->where($this->wawancaraConditionSql(), null, false)
            ->where("{$wawancaraDateExpr} IS NOT NULL", null, false)
            ->where("{$wawancaraDateExpr} > '1970-01-01'", null, false)
            ->where("{$wawancaraDateExpr} >= '{$startDate}'", null, false)
            ->where("{$wawancaraDateExpr} < '{$endDate}'", null, false)
            ->groupBy("YEAR({$wawancaraDateExpr}), MONTH({$wawancaraDateExpr})", false)
            ->get()
            ->getResultArray();

        foreach ($wawancaraRows as $row) {
            $y = (int) ($row['tahun'] ?? 0);
            $m = (int) ($row['bulan'] ?? 0);
            if (isset($monthlyMap[$y][$m])) {
                $monthlyMap[$y][$m]['wawancara'] = (int) ($row['wawancara'] ?? 0);
            }
        }

        // 3. SP3K Activity Metric
        $sp3kDateExpr = $this->stageDateSql('m.sp3k_tgl');
        $sp3kRows = $this->baseMkdtQuery($idProyek)
            ->select("YEAR({$sp3kDateExpr}) AS tahun, MONTH({$sp3kDateExpr}) AS bulan, COUNT(m.id_mkdt) AS sp3k", false)
            ->where($this->sp3kConditionSql(), null, false)
            ->where("{$sp3kDateExpr} IS NOT NULL", null, false)
            ->where("{$sp3kDateExpr} > '1970-01-01'", null, false)
            ->where("{$sp3kDateExpr} >= '{$startDate}'", null, false)
            ->where("{$sp3kDateExpr} < '{$endDate}'", null, false)
            ->groupBy("YEAR({$sp3kDateExpr}), MONTH({$sp3kDateExpr})", false)
            ->get()
            ->getResultArray();

        foreach ($sp3kRows as $row) {
            $y = (int) ($row['tahun'] ?? 0);
            $m = (int) ($row['bulan'] ?? 0);
            if (isset($monthlyMap[$y][$m])) {
                $monthlyMap[$y][$m]['sp3k'] = (int) ($row['sp3k'] ?? 0);
            }
        }

        // 4. Batal Activity Metric
        $batalDateExpr = $this->batalDateSql();
        $batalRows = $this->baseMkdtQuery($idProyek)
            ->select("YEAR({$batalDateExpr}) AS tahun, MONTH({$batalDateExpr}) AS bulan, COUNT(m.id_mkdt) AS batal", false)
            ->groupStart()
                ->where('COALESCE(m.is_batal, 0)', 1)
                ->orWhere("COALESCE(m.status_mkdt, '')", 'Batal')
            ->groupEnd()
            ->where("{$batalDateExpr} IS NOT NULL", null, false)
            ->where("{$batalDateExpr} > '1970-01-01'", null, false)
            ->where("{$batalDateExpr} >= '{$startDate}'", null, false)
            ->where("{$batalDateExpr} < '{$endDate}'", null, false)
            ->groupBy("YEAR({$batalDateExpr}), MONTH({$batalDateExpr})", false)
            ->get()
            ->getResultArray();

        foreach ($batalRows as $row) {
            $y = (int) ($row['tahun'] ?? 0);
            $m = (int) ($row['bulan'] ?? 0);
            if (isset($monthlyMap[$y][$m])) {
                $monthlyMap[$y][$m]['batal'] = (int) ($row['batal'] ?? 0);
            }
        }

        // 5. Akad Indent Activity Metric
        $indentDateExpr = $this->stageDateSql('m.akad_indent_tgl');
        $indentRows = $this->baseMkdtQuery($idProyek)
            ->select("YEAR({$indentDateExpr}) AS tahun, MONTH({$indentDateExpr}) AS bulan, COUNT(m.id_mkdt) AS akad_indent", false)
            ->where($this->akadIndentConditionSql(), null, false)
            ->where("{$indentDateExpr} IS NOT NULL", null, false)
            ->where("{$indentDateExpr} > '1970-01-01'", null, false)
            ->where("{$indentDateExpr} >= '{$startDate}'", null, false)
            ->where("{$indentDateExpr} < '{$endDate}'", null, false)
            ->groupBy("YEAR({$indentDateExpr}), MONTH({$indentDateExpr})", false)
            ->get()
            ->getResultArray();

        foreach ($indentRows as $row) {
            $y = (int) ($row['tahun'] ?? 0);
            $m = (int) ($row['bulan'] ?? 0);
            if (isset($monthlyMap[$y][$m])) {
                $monthlyMap[$y][$m]['akad_indent'] = (int) ($row['akad_indent'] ?? 0);
            }
        }

        // 6. Akad AJB Activity Metric
        $ajbDateExpr = $this->stageDateSql('m.akad_tgl');
        $ajbRows = $this->baseMkdtQuery($idProyek)
            ->select("YEAR({$ajbDateExpr}) AS tahun, MONTH({$ajbDateExpr}) AS bulan, COUNT(m.id_mkdt) AS akad_ajb", false)
            ->where($this->akadAjbConditionSql(), null, false)
            ->where("{$ajbDateExpr} IS NOT NULL", null, false)
            ->where("{$ajbDateExpr} > '1970-01-01'", null, false)
            ->where("{$ajbDateExpr} >= '{$startDate}'", null, false)
            ->where("{$ajbDateExpr} < '{$endDate}'", null, false)
            ->groupBy("YEAR({$ajbDateExpr}), MONTH({$ajbDateExpr})", false)
            ->get()
            ->getResultArray();

        foreach ($ajbRows as $row) {
            $y = (int) ($row['tahun'] ?? 0);
            $m = (int) ($row['bulan'] ?? 0);
            if (isset($monthlyMap[$y][$m])) {
                $monthlyMap[$y][$m]['akad_ajb'] = (int) ($row['akad_ajb'] ?? 0);
            }
        }

        $results = [];
        foreach ($monthlyMap as $y => $months) {
            foreach ($months as $m => $data) {
                $results[] = $data;
            }
        }

        return $results;
    }

    public function getMonthlySummaryTotals(int $idProyek, array $years): array
    {
        return $this->getMonthlyStatusTotals($idProyek, $years);
    }

    public function getSummary(int $idProyek, int $year): array
    {
        $rows = $this->getMonthlyStatusTotals($idProyek, [$year]);
        $result = [];
        for ($m = 1; $m <= 12; $m++) {
            $result[$m] = [
                'booking'                => 0,
                'wawancara'              => 0,
                'sp3k'                   => 0,
                'batal'                  => 0,
                'akad_indent'            => 0,
                'akad_ajb'               => 0,
                'booking_converted_akad' => 0,
                'total_booking_cohort'   => 0,
            ];
        }

        foreach ($rows as $row) {
            $m = (int) ($row['bulan'] ?? 0);
            if ($m >= 1 && $m <= 12) {
                $result[$m] = [
                    'booking'                => (int) ($row['booking'] ?? 0),
                    'wawancara'              => (int) ($row['wawancara'] ?? 0),
                    'sp3k'                   => (int) ($row['sp3k'] ?? 0),
                    'batal'                  => (int) ($row['batal'] ?? 0),
                    'akad_indent'            => (int) ($row['akad_indent'] ?? 0),
                    'akad_ajb'               => (int) ($row['akad_ajb'] ?? 0),
                    'booking_converted_akad' => (int) ($row['booking_converted_akad'] ?? 0),
                    'total_booking_cohort'   => (int) ($row['total_booking_cohort'] ?? 0),
                ];
            }
        }

        return $result;
    }

    public function getDetailRows(int $idProyek, int $year, int $month, string $category, array $dataTable = []): array
    {
        $compiled = $this->compileDetailQuery($idProyek, $year, $month, $category);
        $baseSql = "({$compiled}) booking_akad";

        $recordsTotal = $this->db->table($baseSql)->countAllResults();
        $builder = $this->db->table($baseSql);

        $searchVal = '';
        if (isset($dataTable['search'])) {
            if (is_array($dataTable['search'])) {
                $searchVal = trim((string) ($dataTable['search']['value'] ?? ''));
            } else {
                $searchVal = trim((string) $dataTable['search']);
            }
        }

        if ($searchVal !== '') {
            $builder->groupStart()
                ->like('booking_akad.nama_jalan', $searchVal)
                ->orLike('booking_akad.no_kavling', $searchVal)
                ->orLike('booking_akad.sales', $searchVal)
                ->orLike('booking_akad.nama_konsumen', $searchVal)
                ->orLike('booking_akad.tipe_rumah', $searchVal)
                ->orLike('booking_akad.status_terakhir', $searchVal)
                ->orLike('booking_akad.catatan_mkdt', $searchVal)
                ->orLike('booking_akad.alamat_kavling', $searchVal)
                ->orLike('booking_akad.tanggal_transaksi', $searchVal)
                ->orLike('booking_akad.jenis_laporan', $searchVal)
                ->orLike('booking_akad.status_mkdt', $searchVal)
                ->groupEnd();
        }

        $countBuilder = clone $builder;
        $recordsFiltered = $countBuilder->countAllResults();

        $orderBy = $dataTable['order_by'] ?? 'booking_tgl';
        $orderDir = $dataTable['order_dir'] ?? 'ASC';
        $builder->orderBy($orderBy, $orderDir);
        $builder->orderBy('booking_akad.reference_id', 'ASC');

        if (isset($dataTable['length']) && (int) $dataTable['length'] > 0) {
            $builder->limit((int) $dataTable['length'], (int) ($dataTable['start'] ?? 0));
        }

        return [
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $builder->get()->getResultArray(),
        ];
    }

    private function compileDetailQuery(int $idProyek, int $year, int $month, string $category): string
    {
        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = date('Y-m-d', strtotime($startDate . ' +1 month'));
        $batalDateExpr = $this->batalDateSql();
        $wawancaraDateExpr = $this->stageDateSql('m.wawancara_tgl');
        $sp3kDateExpr = $this->stageDateSql('m.sp3k_tgl');
        $indentDateExpr = $this->stageDateSql('m.akad_indent_tgl');
        $ajbDateExpr = $this->stageDateSql('m.akad_tgl');
        $wawancaraCond = $this->wawancaraConditionSql();
        $sp3kCond = $this->sp3kConditionSql();
        $indentCond = $this->akadIndentConditionSql();
        $ajbCond = $this->akadAjbConditionSql();

        $builder = $this->baseMkdtDetailQuery($idProyek)
            ->select('m.id_mkdt AS reference_id')
            ->select('m.id_mkdt, m.id_kavling, m.uniq_id')
            ->select('m.wawancara, m.sp3k, m.sp3k_no, m.akad_indent, m.akad')
            ->select($this->addressSql())
            ->select("COALESCE(k.no_kavling, '-') AS no_kavling", false)
            ->select("COALESCE(j.nama_jalan, '') AS nama_jalan", false)
            ->select("COALESCE(cl.nama_cluster, '') AS nama_cluster", false)
            ->select("COALESCE(p.nama_proyek, '') AS nama_proyek", false)
            ->select("COALESCE(c.sales, '-') AS sales", false)
            ->select("COALESCE(c.nama_konsumen, '-') AS nama_konsumen", false)
            ->select("COALESCE(thj.tipe_rumah, t.tipe_rumah, '-') AS nama_tipe", false)
            ->select("COALESCE(thj.tipe_rumah, t.tipe_rumah, '-') AS tipe_rumah", false)
            ->select("CASE WHEN COALESCE(m.is_subsidi, hj.is_subsidi, thj.is_subsidi, t.is_subsidi, 0) = 1 THEN 1 ELSE 0 END AS is_subsidi", false)
            ->select("COALESCE(m.status_mkdt, '-') AS status_mkdt", false)
            ->select("COALESCE(m.is_batal, 0) AS is_batal", false)
            ->select("COALESCE(m.keterangan_batal, '') AS keterangan_batal", false)
            ->select("COALESCE(m.keterangan, '') AS keterangan", false)
            ->select("COALESCE(m.keterangan_status, '') AS keterangan_status", false)
            ->select('m.booking_tgl')
            ->select('m.booking_tgl AS tanggal_transaksi')
            ->select('m.akad_tgl')
            ->select('m.akad_indent_tgl')
            ->select('m.sp3k_tgl')
            ->select('m.wawancara_tgl')
            ->select('m.mkdt_batal_tgl')
            ->select('m.keuangan_batal_tgl')
            ->select('m.updated_at')
            ->select("COALESCE(m.booking_fee, 0) AS booking_fee", false)
            ->select("COALESCE(m.booking_fee, 0) AS nominal", false)
            ->select("COALESCE(m.harga_jual_net, 0) AS harga_jual_net", false)
            ->select("COALESCE(m.harga_kpr_acc, 0) AS harga_kpr_acc", false)
            ->select("
CASE
    WHEN (COALESCE(m.is_batal, 0) = 1 OR COALESCE(m.status_mkdt, '') = 'Batal') THEN 'Batal'
    WHEN {$ajbCond} THEN 'Akad AJB'
    WHEN {$indentCond} THEN 'Akad Indent'
    WHEN {$sp3kCond} THEN 'SP3K'
    WHEN {$wawancaraCond} THEN 'Wawancara'
    ELSE 'Booking'
END AS status_terakhir", false)
            ->select("
CASE
    WHEN (COALESCE(m.is_batal, 0) = 1 OR COALESCE(m.status_mkdt, '') = 'Batal') THEN 'danger'
    WHEN {$ajbCond} THEN 'success'
    WHEN {$indentCond} THEN 'info'
    WHEN {$sp3kCond} THEN 'primary'
    WHEN {$wawancaraCond} THEN 'warning'
    ELSE 'secondary'
END AS status_terakhir_badge", false)
            ->select("
CASE
    WHEN (COALESCE(m.is_batal, 0) = 1 OR COALESCE(m.status_mkdt, '') = 'Batal') 
         THEN {$batalDateExpr}
    WHEN {$ajbCond} 
         THEN {$ajbDateExpr}
    WHEN {$indentCond} 
         THEN {$indentDateExpr}
    WHEN {$sp3kCond} 
         THEN {$sp3kDateExpr}
    WHEN {$wawancaraCond} 
         THEN {$wawancaraDateExpr}
    ELSE m.booking_tgl
END AS tanggal_status_terakhir", false)
            ->select("COALESCE(NULLIF(TRIM(m.keterangan_batal), ''), NULLIF(TRIM(m.keterangan), ''), NULLIF(TRIM(m.keterangan_status), ''), '-') AS catatan_mkdt", false);

        if ($category === 'booking_aktif') {
            return $builder
                ->select("'booking_aktif' AS kategori", false)
                ->select("'Booking Aktif' AS jenis_laporan", false)
                ->where('m.booking_tgl IS NOT NULL')
                ->where('m.booking_tgl >', '1970-01-01')
                ->where('m.booking_tgl >=', $startDate)
                ->where('m.booking_tgl <', $endDate)
                ->where('COALESCE(m.is_batal, 0)', 0)
                ->where("COALESCE(m.status_mkdt, '') !=", 'Batal')
                ->getCompiledSelect();
        }

        if ($category === 'booking_batal') {
            return $builder
                ->select("'booking_batal' AS kategori", false)
                ->select("'Booking Batal' AS jenis_laporan", false)
                ->groupStart()
                    ->where('COALESCE(m.is_batal, 0)', 1)
                    ->orWhere("COALESCE(m.status_mkdt, '')", 'Batal')
                ->groupEnd()
                ->where("{$batalDateExpr} IS NOT NULL", null, false)
                ->where("{$batalDateExpr} > '1970-01-01'", null, false)
                ->where("{$batalDateExpr} >= '{$startDate}'", null, false)
                ->where("{$batalDateExpr} < '{$endDate}'", null, false)
                ->getCompiledSelect();
        }

        if ($category === 'akad') {
            return $builder
                ->select("'akad' AS kategori", false)
                ->select("'Akad' AS jenis_laporan", false)
                ->where('m.akad_tgl IS NOT NULL')
                ->where('m.akad_tgl >', '1970-01-01')
                ->where('m.akad_tgl >=', $startDate)
                ->where('m.akad_tgl <', $endDate)
                ->groupStart()
                    ->where("COALESCE(m.status_mkdt, '')", 'Akad')
                    ->orWhere('COALESCE(m.akad, 0)', 1)
                ->groupEnd()
                ->getCompiledSelect();
        }

        if ($category === 'batal') {
            return $builder
                ->select("'batal' AS kategori", false)
                ->select("'Batal' AS jenis_laporan", false)
                ->groupStart()
                    ->where('COALESCE(m.is_batal, 0)', 1)
                    ->orWhere("COALESCE(m.status_mkdt, '')", 'Batal')
                ->groupEnd()
                ->where("{$batalDateExpr} IS NOT NULL", null, false)
                ->where("{$batalDateExpr} > '1970-01-01'", null, false)
                ->where("{$batalDateExpr} >= '{$startDate}'", null, false)
                ->where("{$batalDateExpr} < '{$endDate}'", null, false)
                ->getCompiledSelect();
        }

        if ($category === 'akad_ajb') {
            return $builder
                ->select("'akad_ajb' AS kategori", false)
                ->select("'Akad AJB' AS jenis_laporan", false)
                ->where($ajbCond, null, false)
                ->where("{$ajbDateExpr} IS NOT NULL", null, false)
                ->where("{$ajbDateExpr} > '1970-01-01'", null, false)
                ->where("{$ajbDateExpr} >= '{$startDate}'", null, false)
                ->where("{$ajbDateExpr} < '{$endDate}'", null, false)
                ->getCompiledSelect();
        }

        if ($category === 'akad_indent') {
            return $builder
                ->select("'akad_indent' AS kategori", false)
                ->select("'Akad Indent' AS jenis_laporan", false)
                ->where($indentCond, null, false)
                ->where("{$indentDateExpr} IS NOT NULL", null, false)
                ->where("{$indentDateExpr} > '1970-01-01'", null, false)
                ->where("{$indentDateExpr} >= '{$startDate}'", null, false)
                ->where("{$indentDateExpr} < '{$endDate}'", null, false)
                ->getCompiledSelect();
        }

        if ($category === 'sp3k') {
            return $builder
                ->select("'sp3k' AS kategori", false)
                ->select("'SP3K' AS jenis_laporan", false)
                ->where($sp3kCond, null, false)
                ->where("{$sp3kDateExpr} IS NOT NULL", null, false)
                ->where("{$sp3kDateExpr} > '1970-01-01'", null, false)
                ->where("{$sp3kDateExpr} >= '{$startDate}'", null, false)
                ->where("{$sp3kDateExpr} < '{$endDate}'", null, false)
                ->getCompiledSelect();
        }

        if ($category === 'wawancara') {
            return $builder
                ->select("'wawancara' AS kategori", false)
                ->select("'Wawancara' AS jenis_laporan", false)
                ->where($wawancaraCond, null, false)
                ->where("{$wawancaraDateExpr} IS NOT NULL", null, false)
                ->where("{$wawancaraDateExpr} > '1970-01-01'", null, false)
                ->where("{$wawancaraDateExpr} >= '{$startDate}'", null, false)
                ->where("{$wawancaraDateExpr} < '{$endDate}'", null, false)
                ->getCompiledSelect();
        }

        // Default: category === 'booking'
        return $builder
            ->select("'booking' AS kategori", false)
            ->select("'Booking' AS jenis_laporan", false)
            ->where('m.booking_tgl IS NOT NULL')
            ->where('m.booking_tgl >', '1970-01-01')
            ->where('m.booking_tgl >=', $startDate)
            ->where('m.booking_tgl <', $endDate)
            ->getCompiledSelect();
    }

    private function wawancaraConditionSql(): string
    {
        return "(COALESCE(m.wawancara, 0) = 1 OR (m.wawancara_tgl IS NOT NULL AND m.wawancara_tgl > '1970-01-01'))";
    }

    private function sp3kConditionSql(): string
    {
        return "(COALESCE(m.sp3k, 0) = 1 OR (m.sp3k_tgl IS NOT NULL AND m.sp3k_tgl > '1970-01-01') OR (m.sp3k_no IS NOT NULL AND TRIM(m.sp3k_no) != '' AND TRIM(m.sp3k_no) != '-'))";
    }

    private function akadIndentConditionSql(): string
    {
        return "(COALESCE(m.akad_indent, 0) = 1 OR (m.akad_indent_tgl IS NOT NULL AND m.akad_indent_tgl > '1970-01-01'))";
    }

    private function akadAjbConditionSql(): string
    {
        return "(COALESCE(m.akad, 0) = 1 OR (m.akad_tgl IS NOT NULL AND m.akad_tgl > '1970-01-01'))";
    }

    private function stageDateSql(string $column): string
    {
        return "COALESCE(
            CASE WHEN {$column} IS NOT NULL AND {$column} > '1970-01-01' THEN DATE({$column}) END,
            CASE WHEN m.booking_tgl IS NOT NULL AND m.booking_tgl > '1970-01-01' THEN DATE(m.booking_tgl) END,
            CASE WHEN m.updated_at IS NOT NULL AND m.updated_at > '1970-01-01' THEN DATE(m.updated_at) END,
            CASE WHEN m.created_at IS NOT NULL AND m.created_at > '1970-01-01' THEN DATE(m.created_at) END
        )";
    }

    private function batalDateSql(): string
    {
        return "COALESCE(
            CASE WHEN m.mkdt_batal_tgl IS NOT NULL AND m.mkdt_batal_tgl > '1970-01-01' THEN DATE(m.mkdt_batal_tgl) END,
            CASE WHEN m.keuangan_batal_tgl IS NOT NULL AND m.keuangan_batal_tgl > '1970-01-01' THEN DATE(m.keuangan_batal_tgl) END,
            CASE WHEN m.updated_at IS NOT NULL AND m.updated_at > '1970-01-01' THEN DATE(m.updated_at) END,
            CASE WHEN m.booking_tgl IS NOT NULL AND m.booking_tgl > '1970-01-01' THEN DATE(m.booking_tgl) END
        )";
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
            ->join('hargajual hj', 'hj.id = m.id_hargajual', 'left')
            ->join('tipe thj', 'thj.id_tipe = hj.id_tipe', 'left')
            ->where('p.id_proyek', $idProyek);
    }

    private function yearBounds($builder, string $dateColumn): array
    {
        $row = $builder
            ->select("MIN(YEAR({$dateColumn})) AS min_year, MAX(YEAR({$dateColumn})) AS max_year", false)
            ->where("{$dateColumn} IS NOT NULL", null, false)
            ->where("{$dateColumn} > '1970-01-01'", null, false)
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

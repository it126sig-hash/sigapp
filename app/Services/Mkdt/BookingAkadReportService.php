<?php

namespace App\Services\Mkdt;

use App\Repositories\Mkdt\BookingAkadReportRepository;
use InvalidArgumentException;

class BookingAkadReportService
{
    /**
     * 6 Kategori Status Transaksi Resmi (R1)
     */
    public const CATEGORIES = [
        'booking',
        'wawancara',
        'sp3k',
        'batal',
        'akad_indent',
        'akad_ajb',
    ];

    /**
     * Pemetaan Badge Status Semantik (R3)
     */
    public const STATUS_BADGE_MAP = [
        'Batal'       => 'danger',
        'Akad AJB'    => 'success',
        'Akad Indent' => 'info',
        'SP3K'        => 'primary',
        'Wawancara'   => 'warning',
        'Booking'     => 'secondary',
    ];

    private const MONTH_LABELS = [
        1  => 'Januari',
        2  => 'Februari',
        3  => 'Maret',
        4  => 'April',
        5  => 'Mei',
        6  => 'Juni',
        7  => 'Juli',
        8  => 'Agustus',
        9  => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    private BookingAkadReportRepository $repository;

    public function __construct(?BookingAkadReportRepository $repository = null)
    {
        $this->repository = $repository ?? new BookingAkadReportRepository();
    }

    /**
     * Mengambil range tahun yang tersedia untuk filter proyek
     */
    public function getAvailableYears(int $idProyek): array
    {
        if (method_exists($this->repository, 'getAvailableYears')) {
            $years = $this->repository->getAvailableYears($idProyek);
            if (! empty($years)) {
                return $years;
            }
        }

        $currentYear = (int) date('Y');
        $bounds = $this->repository->getAvailableYearBounds($idProyek);
        $minimum = min((int) ($bounds['min'] ?? $currentYear - 1), $currentYear - 1);
        $maximum = max((int) ($bounds['max'] ?? $currentYear), $currentYear);

        return range($maximum, $minimum);
    }

    /**
     * Mengambil ringkasan matriks 12 bulan untuk 1 atau 2 tahun pembanding
     * dengan perhitungan konversi riil berbasis kode transaksi unik (cohort conversion).
     *
     * @param int $idProyek
     * @param int $yearA
     * @param int|null $yearB
     * @return array
     */
    public function getSummary(int $idProyek, int $yearA, ?int $yearB = null): array
    {
        $this->assertProject($idProyek);
        $this->assertYear($yearA);

        $years = [$yearA];
        if ($yearB !== null) {
            $this->assertYear($yearB);
            if ($yearA === $yearB) {
                throw new InvalidArgumentException('Pilih dua tahun yang berbeda.');
            }
            $years[] = $yearB;
        }

        $summary = $this->emptySummary($years);
        $repoData = $this->fetchYearsSummaryFromRepository($idProyek, $years);

        // Ambil data bulanan dari repositori untuk setiap tahun
        foreach ($years as $year) {
            $yearData = $repoData[$year] ?? [];

            for ($month = 1; $month <= 12; $month++) {
                $monthData = $yearData[$month] ?? [];

                $booking    = (int) ($monthData['booking'] ?? 0);
                $wawancara  = (int) ($monthData['wawancara'] ?? ($monthData['jumlah_wawancara'] ?? 0));
                $sp3k       = (int) ($monthData['sp3k'] ?? ($monthData['jumlah_sp3k'] ?? 0));
                $batal      = (int) ($monthData['batal'] ?? ($monthData['booking_batal'] ?? ($monthData['jumlah_batal'] ?? 0)));
                $akadIndent = (int) ($monthData['akad_indent'] ?? ($monthData['jumlah_akad_indent'] ?? 0));
                $akadAjb    = (int) ($monthData['akad_ajb'] ?? ($monthData['jumlah_akad_ajb'] ?? ($monthData['jumlah_akad'] ?? 0)));

                // Metrik konversi cohort berbasis kode transaksi unik (mkdt.uniq_id)
                $defaultCohort = (int) ($monthData['total_booking'] ?? ($monthData['jumlah_booking'] ?? ($booking + $wawancara + $sp3k + $batal + $akadIndent + $akadAjb)));
                $cohortBooking = (int) ($monthData['total_booking_cohort'] ?? ($monthData['total_cohort_booking'] ?? $defaultCohort));
                $cohortAkad    = (int) ($monthData['booking_converted_akad'] ?? ($monthData['cohort_akad'] ?? ($akadIndent + $akadAjb)));

                // Formula konversi riil: total_cohort_booking > 0 ? round((cohort_akad / total_cohort_booking) * 100, 1) : 0.0
                $konversi = $cohortBooking > 0 ? round(($cohortAkad / $cohortBooking) * 100, 1) : 0.0;
                $konversi = max(0.0, min(100.0, (float) $konversi));

                $summary[$year][$month] = [
                    'booking'                => $booking,
                    'wawancara'              => $wawancara,
                    'sp3k'                   => $sp3k,
                    'batal'                  => $batal,
                    'akad_indent'            => $akadIndent,
                    'akad_ajb'               => $akadAjb,
                    'konversi'               => $konversi,
                    'total_booking_cohort'   => $cohortBooking,
                    'booking_converted_akad' => $cohortAkad,

                    // Compatibility aliases
                    'jumlah_booking'         => $cohortBooking > 0 ? $cohortBooking : $booking,
                    'booking_aktif'          => (int) ($monthData['booking_aktif'] ?? $booking),
                    'booking_batal'          => $batal,
                    'jumlah_wawancara'       => $wawancara,
                    'jumlah_sp3k'            => $sp3k,
                    'jumlah_batal'           => $batal,
                    'jumlah_akad_indent'     => $akadIndent,
                    'jumlah_akad_ajb'        => $akadAjb,
                    'jumlah_akad'            => $cohortAkad > 0 ? $cohortAkad : ($akadIndent + $akadAjb),
                    'rasio_konversi'         => $konversi,
                    'rasio_akad'             => $konversi,
                ];
            }
        }

        $totals = [];
        $annualCohortBooking = [];
        $annualCohortAkad = [];

        foreach ($years as $year) {
            $totals[$year] = $this->emptyAmounts();
            $annualCohortBooking[$year] = 0;
            $annualCohortAkad[$year] = 0;
        }

        $months = [];
        foreach (self::MONTH_LABELS as $month => $label) {
            $values = [];
            foreach ($years as $year) {
                $item = $summary[$year][$month];
                $values[$year] = $item;

                $totals[$year]['booking']            += $item['booking'];
                $totals[$year]['wawancara']          += $item['wawancara'];
                $totals[$year]['sp3k']               += $item['sp3k'];
                $totals[$year]['batal']              += $item['batal'];
                $totals[$year]['akad_indent']        += $item['akad_indent'];
                $totals[$year]['akad_ajb']           += $item['akad_ajb'];

                $totals[$year]['jumlah_booking']     += $item['jumlah_booking'];
                $totals[$year]['booking_aktif']      += $item['booking_aktif'];
                $totals[$year]['booking_batal']      += $item['booking_batal'];
                $totals[$year]['jumlah_wawancara']   += $item['wawancara'];
                $totals[$year]['jumlah_sp3k']        += $item['sp3k'];
                $totals[$year]['jumlah_batal']       += $item['batal'];
                $totals[$year]['jumlah_akad_indent'] += $item['akad_indent'];
                $totals[$year]['jumlah_akad_ajb']    += $item['akad_ajb'];
                $totals[$year]['jumlah_akad']        += $item['jumlah_akad'];

                $annualCohortBooking[$year]          += $item['total_booking_cohort'];
                $annualCohortAkad[$year]             += $item['booking_converted_akad'];
            }

            $months[] = [
                'month'       => $month,
                'month_label' => $label,
                'values'      => $values,
            ];
        }

        // True annual cohort conversion: annual_cohort_booking > 0 ? round((annual_cohort_akad / annual_cohort_booking) * 100, 1) : 0.0
        foreach ($years as $year) {
            $totalCohort    = $annualCohortBooking[$year];
            $totalAkad      = $annualCohortAkad[$year];
            $annualKonversi = $totalCohort > 0 ? round(($totalAkad / $totalCohort) * 100, 1) : 0.0;
            $annualKonversi = max(0.0, min(100.0, (float) $annualKonversi));

            $totals[$year]['konversi']               = $annualKonversi;
            $totals[$year]['rasio_konversi']         = $annualKonversi;
            $totals[$year]['rasio_akad']             = $annualKonversi;
            $totals[$year]['total_booking_cohort']   = $totalCohort;
            $totals[$year]['booking_converted_akad'] = $totalAkad;
        }

        // Build summary map matching PROJECT.md § Interface Contracts
        $summaryMap = [];
        foreach ($years as $year) {
            $summaryMap[$year] = [];
            foreach (self::MONTH_LABELS as $month => $label) {
                $summaryMap[$year][$month] = [
                    'booking'     => $summary[$year][$month]['booking'],
                    'wawancara'   => $summary[$year][$month]['wawancara'],
                    'sp3k'        => $summary[$year][$month]['sp3k'],
                    'batal'       => $summary[$year][$month]['batal'],
                    'akad_indent' => $summary[$year][$month]['akad_indent'],
                    'akad_ajb'    => $summary[$year][$month]['akad_ajb'],
                    'konversi'    => $summary[$year][$month]['konversi'],
                ];
            }
            $summaryMap[$year]['total'] = [
                'booking'     => $totals[$year]['booking'],
                'wawancara'   => $totals[$year]['wawancara'],
                'sp3k'        => $totals[$year]['sp3k'],
                'batal'       => $totals[$year]['batal'],
                'akad_indent' => $totals[$year]['akad_indent'],
                'akad_ajb'    => $totals[$year]['akad_ajb'],
                'konversi'    => $totals[$year]['konversi'],
            ];
        }

        return [
            'years'   => $years,
            'months'  => $months,
            'totals'  => $totals,
            'summary' => $summaryMap,
        ];
    }

    /**
     * Mengambil detail transaksi dengan format JSON 7 kolom (R3 & Interface Contract)
     *
     * @param int $idProyek
     * @param array|int $requestOrYear
     * @param int|null $month
     * @param string|null $category
     * @param array $dataTable
     * @return array
     */
    public function getDetail(
        int $idProyek,
        array|int $requestOrYear,
        ?int $month = null,
        ?string $category = null,
        array $dataTable = []
    ): array {
        $this->assertProject($idProyek);

        if (is_array($requestOrYear)) {
            $year = (int) ($requestOrYear['year'] ?? 0);
            $month = (int) ($requestOrYear['month'] ?? 0);
            $category = trim((string) ($requestOrYear['category'] ?? ''));
            $dataTableRequest = $requestOrYear;
        } else {
            $year = (int) $requestOrYear;
            $month = (int) $month;
            $category = trim((string) $category);
            $dataTableRequest = $dataTable;
        }

        $this->assertYear($year);
        if ($month < 1 || $month > 12) {
            throw new InvalidArgumentException('Bulan tidak valid.');
        }
        if (! in_array($category, self::CATEGORIES, true)) {
            throw new InvalidArgumentException('Kategori laporan tidak valid.');
        }

        $normalizedDataTable = $this->normalizeDataTableRequest($dataTableRequest);
        $result = $this->repository->getDetailRows($idProyek, $year, $month, $category, $normalizedDataTable);

        if (isset($result['data']) && is_array($result['data'])) {
            $rawRows = $result['data'];
            $recordsTotal = (int) ($result['recordsTotal'] ?? count($rawRows));
            $recordsFiltered = (int) ($result['recordsFiltered'] ?? count($rawRows));
        } else {
            $rawRows = is_array($result) ? $result : [];
            $recordsTotal = count($rawRows);
            $recordsFiltered = count($rawRows);
        }

        $formattedData = [];
        foreach ($rawRows as $row) {
            $formattedData[] = $this->formatDetailRow($row);
        }

        return [
            'draw'            => $normalizedDataTable['draw'],
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $formattedData,
        ];
    }

    public function monthLabel(int $month): string
    {
        return self::MONTH_LABELS[$month] ?? '';
    }

    public function resolveStatusBadge(string $status): string
    {
        $normalized = ucwords(str_replace('_', ' ', strtolower(trim($status))));
        if ($normalized === 'Sp3k') {
            $normalized = 'SP3K';
        } elseif ($normalized === 'Akad Ajb') {
            $normalized = 'Akad AJB';
        }

        return self::STATUS_BADGE_MAP[$normalized] ?? (self::STATUS_BADGE_MAP[$status] ?? 'secondary');
    }

    public function resolveStatusTerakhir(array $row): string
    {
        if (! empty($row['status_terakhir'])) {
            return (string) $row['status_terakhir'];
        }

        // Hierarki: Batal > Akad AJB > Akad Indent > SP3K > Wawancara > Booking
        if (! empty($row['is_batal']) || strtolower((string) ($row['status_mkdt'] ?? '')) === 'batal') {
            return 'Batal';
        }

        $hasAkad = ! empty($row['akad']) || $this->isValidDate($row['akad_tgl'] ?? ($row['tgl_akad'] ?? null));
        if ($hasAkad) {
            return 'Akad AJB';
        }

        $hasAkadIndent = ! empty($row['akad_indent']) || $this->isValidDate($row['akad_indent_tgl'] ?? null);
        if ($hasAkadIndent) {
            return 'Akad Indent';
        }

        $hasSp3k = ! empty($row['sp3k'])
            || $this->isValidDate($row['sp3k_tgl'] ?? null)
            || $this->hasValidSp3kNo($row['sp3k_no'] ?? null);
        if ($hasSp3k) {
            return 'SP3K';
        }

        $hasWawancara = ! empty($row['wawancara']) || $this->isValidDate($row['wawancara_tgl'] ?? null);
        if ($hasWawancara) {
            return 'Wawancara';
        }

        return 'Booking';
    }

    public function resolveTanggalStatusTerakhir(array $row, string $status): ?string
    {
        if ($this->isValidDate($row['tanggal_status_terakhir'] ?? null)) {
            return (string) $row['tanggal_status_terakhir'];
        }

        return match ($status) {
            'Batal'       => $this->firstValidDate(
                $row['mkdt_batal_tgl'] ?? null,
                $row['keuangan_batal_tgl'] ?? null,
                $row['updated_at'] ?? null,
                $row['booking_tgl'] ?? null
            ),
            'Akad AJB'    => $this->firstValidDate(
                $row['akad_tgl'] ?? ($row['tgl_akad'] ?? null),
                $row['booking_tgl'] ?? null,
                $row['updated_at'] ?? null
            ),
            'Akad Indent' => $this->firstValidDate(
                $row['akad_indent_tgl'] ?? null,
                $row['booking_tgl'] ?? null,
                $row['updated_at'] ?? null
            ),
            'SP3K'        => $this->firstValidDate(
                $row['sp3k_tgl'] ?? null,
                $row['booking_tgl'] ?? null,
                $row['updated_at'] ?? null
            ),
            'Wawancara'   => $this->firstValidDate(
                $row['wawancara_tgl'] ?? null,
                $row['booking_tgl'] ?? null,
                $row['updated_at'] ?? null
            ),
            default       => $this->firstValidDate(
                $row['booking_tgl'] ?? null,
                $row['updated_at'] ?? null
            ),
        };
    }

    private function isValidDate(?string $date): bool
    {
        if ($date === null || trim($date) === '') {
            return false;
        }

        $trimmed = trim($date);
        if (str_starts_with($trimmed, '0000') || $trimmed <= '1970-01-01') {
            return false;
        }

        $timestamp = strtotime($trimmed);

        return $timestamp !== false && $timestamp > 0;
    }

    private function hasValidSp3kNo(?string $sp3kNo): bool
    {
        if ($sp3kNo === null) {
            return false;
        }

        $trimmed = trim($sp3kNo);

        return $trimmed !== '' && $trimmed !== '-';
    }

    private function firstValidDate(?string ...$candidates): ?string
    {
        foreach ($candidates as $candidate) {
            if ($this->isValidDate($candidate)) {
                return (string) $candidate;
            }
        }

        return null;
    }

    public function resolveCatatanMkdt(array $row): string
    {
        if (isset($row['catatan_mkdt']) && trim((string) $row['catatan_mkdt']) !== '') {
            return trim((string) $row['catatan_mkdt']);
        }

        $catatan = trim((string) ($row['keterangan_batal'] ?? ''));
        if ($catatan === '') {
            $catatan = trim((string) ($row['keterangan'] ?? ''));
        }
        if ($catatan === '') {
            $catatan = trim((string) ($row['keterangan_status'] ?? ''));
        }

        return $catatan !== '' ? $catatan : '-';
    }

    public function formatDate(?string $date): string
    {
        if ($date === null || trim($date) === '') {
            return '-';
        }

        $trimmed = trim($date);
        if (str_starts_with($trimmed, '0000') || $trimmed <= '1970-01-01') {
            return '-';
        }

        $timestamp = strtotime($trimmed);
        if ($timestamp === false || $timestamp <= 0) {
            return '-';
        }

        return date('d-m-Y', $timestamp);
    }

    private function formatDetailRow(array $row): array
    {
        $idMkdt = (int) ($row['id_mkdt'] ?? 0);
        $idKavling = (int) ($row['id_kavling'] ?? 0);

        $namaJalan = trim((string) ($row['nama_jalan'] ?? ''));
        $namaJalan = $namaJalan !== '' ? $namaJalan : '-';

        $noKavling = trim((string) ($row['no_kavling'] ?? '-'));
        if ($noKavling === '') {
            $noKavling = '-';
        }

        $sales = trim((string) ($row['sales'] ?? ''));
        $sales = $sales !== '' ? $sales : '-';

        $namaKonsumen = trim((string) ($row['nama_konsumen'] ?? ''));
        $namaKonsumen = $namaKonsumen !== '' ? $namaKonsumen : '-';

        $tipeRumah = trim((string) ($row['tipe_rumah'] ?? ($row['nama_tipe'] ?? '')));
        $tipeRumah = $tipeRumah !== '' ? $tipeRumah : '-';

        $isSubsidi = ! empty($row['is_subsidi']) && (int) $row['is_subsidi'] === 1;

        $bookingDateRaw = $row['booking_tgl'] ?? ($row['tanggal_booking'] ?? null);
        $tanggalBookingFormatted = $this->formatDate($bookingDateRaw);

        $statusTerakhir = $this->resolveStatusTerakhir($row);
        $statusTerakhirBadge = ! empty($row['status_terakhir_badge'])
            ? (string) $row['status_terakhir_badge']
            : $this->resolveStatusBadge($statusTerakhir);

        $catatanMkdt = $this->resolveCatatanMkdt($row);

        $statusDateRaw = $this->resolveTanggalStatusTerakhir($row, $statusTerakhir);
        $tanggalStatusTerakhirFormatted = $this->formatDate($statusDateRaw);

        return [
            'id_mkdt'                 => $idMkdt,
            'id_kavling'              => $idKavling,
            'kavling'                 => [
                'nama_jalan' => $namaJalan,
                'no_kavling' => $noKavling,
                'sales'      => $sales,
            ],
            'konsumen'                => [
                'nama_konsumen' => $namaKonsumen,
                'tipe_rumah'    => $tipeRumah,
                'is_subsidi'    => $isSubsidi,
            ],
            'tanggal_booking'         => $tanggalBookingFormatted,
            'status_terakhir'         => $statusTerakhir,
            'status_terakhir_badge'   => $statusTerakhirBadge,
            'catatan_mkdt'            => $catatanMkdt,
            'tanggal_status_terakhir' => $tanggalStatusTerakhirFormatted,

            // Flat aliases for DataTables & compatibility
            'nama_jalan'              => $namaJalan,
            'no_kavling'              => $noKavling,
            'sales'                   => $sales,
            'nama_konsumen'           => $namaKonsumen,
            'tipe_rumah'              => $tipeRumah,
            'nama_tipe'               => $tipeRumah,
            'is_subsidi'              => $isSubsidi,
            'alamat_kavling'          => trim(($namaJalan !== '-' ? $namaJalan : '') . ' No. ' . $noKavling),
            'booking_tgl'             => $bookingDateRaw,
            'tanggal_transaksi'       => $tanggalBookingFormatted,
            'status_mkdt'             => $statusTerakhir,
            'nominal'                 => (float) ($row['booking_fee'] ?? ($row['nominal'] ?? 0)),
        ];
    }

    private function fetchYearsSummaryFromRepository(int $idProyek, array $years): array
    {
        if (method_exists($this->repository, 'getMonthlyStatusTotals')) {
            $result = $this->repository->getMonthlyStatusTotals($idProyek, $years);
            if (is_array($result) && ! empty($result)) {
                return $this->normalizeMultiYearDataMap($result, $years);
            }
        }

        if (method_exists($this->repository, 'getMonthlySummaryTotals')) {
            $result = $this->repository->getMonthlySummaryTotals($idProyek, $years);
            if (is_array($result) && ! empty($result)) {
                return $this->normalizeMultiYearDataMap($result, $years);
            }
        }

        if (method_exists($this->repository, 'getSummary')) {
            $mapped = [];
            foreach ($years as $year) {
                $res = $this->repository->getSummary($idProyek, $year);
                if (is_array($res) && ! empty($res)) {
                    $mapped[$year] = $res;
                }
            }
            if (! empty($mapped)) {
                return $mapped;
            }
        }

        // Legacy fallback
        if (method_exists($this->repository, 'getMonthlyBookingTotals') && method_exists($this->repository, 'getMonthlyAkadTotals')) {
            $mapped = [];
            foreach ($years as $y) {
                $mapped[$y] = [];
            }
            foreach ($this->repository->getMonthlyBookingTotals($idProyek, $years) as $bRow) {
                $y = (int) ($bRow['tahun'] ?? ($years[0] ?? 0));
                $m = (int) ($bRow['bulan'] ?? 0);
                if (in_array($y, $years, true) && $m >= 1 && $m <= 12) {
                    $mapped[$y][$m] = $bRow;
                }
            }
            foreach ($this->repository->getMonthlyAkadTotals($idProyek, $years) as $aRow) {
                $y = (int) ($aRow['tahun'] ?? ($years[0] ?? 0));
                $m = (int) ($aRow['bulan'] ?? 0);
                if (in_array($y, $years, true) && $m >= 1 && $m <= 12) {
                    $mapped[$y][$m] = array_merge($mapped[$y][$m] ?? [], $aRow);
                }
            }
            if (! empty($mapped)) {
                return $mapped;
            }
        }

        return [];
    }

    private function normalizeMultiYearDataMap(array $data, array $years): array
    {
        $mapped = [];
        foreach ($years as $y) {
            $mapped[$y] = [];
        }

        foreach ($data as $key => $val) {
            if (is_numeric($key) && in_array((int) $key, $years, true) && is_array($val)) {
                $mapped[(int) $key] = $val;
            }
        }

        foreach ($data as $row) {
            if (is_array($row)) {
                $y = (int) ($row['tahun'] ?? ($row['year'] ?? 0));
                $m = (int) ($row['bulan'] ?? ($row['month'] ?? 0));
                if (in_array($y, $years, true) && $m >= 1 && $m <= 12) {
                    $mapped[$y][$m] = $row;
                } elseif ($y === 0 && count($years) === 1 && $m >= 1 && $m <= 12) {
                    $singleYear = $years[0];
                    $mapped[$singleYear][$m] = $row;
                }
            }
        }

        return $mapped;
    }

    private function emptySummary(array $years): array
    {
        $summary = [];
        foreach ($years as $year) {
            for ($month = 1; $month <= 12; $month++) {
                $summary[$year][$month] = $this->emptyAmounts();
            }
        }

        return $summary;
    }

    private function emptyAmounts(): array
    {
        return [
            'booking'                => 0,
            'wawancara'              => 0,
            'sp3k'                   => 0,
            'batal'                  => 0,
            'akad_indent'            => 0,
            'akad_ajb'               => 0,
            'konversi'               => 0.0,
            'total_booking_cohort'   => 0,
            'booking_converted_akad' => 0,

            // Compatibility aliases
            'jumlah_booking'         => 0,
            'booking_aktif'          => 0,
            'booking_batal'          => 0,
            'jumlah_wawancara'       => 0,
            'jumlah_sp3k'            => 0,
            'jumlah_batal'           => 0,
            'jumlah_akad_indent'     => 0,
            'jumlah_akad_ajb'        => 0,
            'jumlah_akad'            => 0,
            'rasio_konversi'         => 0.0,
            'rasio_akad'             => 0.0,
        ];
    }

    private function normalizeDataTableRequest(array $request): array
    {
        $columnIndex = (int) ($request['order'][0]['column'] ?? 3);
        $requestedName = $request['columns'][$columnIndex]['name'] ?? ($request['columns'][$columnIndex]['data'] ?? 'tanggal_booking');

        $orderColumns = [
            // New 7 columns (R3)
            'kavling'                 => 'booking_akad.nama_jalan',
            'nama_jalan'              => 'booking_akad.nama_jalan',
            'no_kavling'              => 'booking_akad.no_kavling',
            'sales'                   => 'booking_akad.sales',
            'konsumen'                => 'booking_akad.nama_konsumen',
            'nama_konsumen'           => 'booking_akad.nama_konsumen',
            'tipe_rumah'              => 'booking_akad.tipe_rumah',
            'nama_tipe'               => 'booking_akad.tipe_rumah',
            'tanggal_booking'         => 'booking_akad.booking_tgl',
            'booking_tgl'             => 'booking_akad.booking_tgl',
            'status_terakhir'         => 'booking_akad.status_terakhir',
            'tanggal_status_terakhir' => 'booking_akad.tanggal_status_terakhir',
            'catatan_mkdt'            => 'booking_akad.catatan_mkdt',
            'keterangan'              => 'booking_akad.catatan_mkdt',

            // Legacy compatibility
            'jenis_laporan'           => 'booking_akad.jenis_laporan',
            'alamat_kavling'          => 'booking_akad.alamat_kavling',
            'tanggal_transaksi'       => 'booking_akad.tanggal_transaksi',
            'status_mkdt'             => 'booking_akad.status_mkdt',
            'nominal'                 => 'booking_akad.nominal',
        ];

        $indexMapping = [
            0 => 'booking_akad.reference_id',
            1 => 'booking_akad.nama_jalan',
            2 => 'booking_akad.nama_konsumen',
            3 => 'booking_akad.booking_tgl',
            4 => 'booking_akad.status_terakhir',
            5 => 'booking_akad.catatan_mkdt',
            6 => 'booking_akad.reference_id',
        ];

        $orderBy = $orderColumns[$requestedName] ?? ($indexMapping[$columnIndex] ?? 'booking_akad.booking_tgl');

        $length = (int) ($request['length'] ?? 10);
        if ($length < 1) {
            $length = 10;
        }

        $searchVal = '';
        if (isset($request['search'])) {
            if (is_array($request['search'])) {
                $searchVal = trim((string) ($request['search']['value'] ?? ''));
            } else {
                $searchVal = trim((string) $request['search']);
            }
        }

        return [
            'draw'      => max(0, (int) ($request['draw'] ?? 0)),
            'start'     => max(0, (int) ($request['start'] ?? 0)),
            'length'    => min($length, 100),
            'search'    => $searchVal,
            'order_by'  => $orderBy,
            'order_dir' => strtolower((string) ($request['order'][0]['dir'] ?? 'asc')) === 'desc' ? 'DESC' : 'ASC',
        ];
    }

    private function assertYear(int $year): void
    {
        if ($year < 1900 || $year > 2100) {
            throw new InvalidArgumentException('Tahun harus berada antara 1900 dan 2100.');
        }
    }

    private function assertProject(int $idProyek): void
    {
        if ($idProyek <= 0) {
            throw new InvalidArgumentException('Pilih proyek aktif terlebih dahulu.');
        }
    }
}

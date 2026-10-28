<?php

namespace App\Services;

use App\Repositories\BookingAkadReportRepository;
use InvalidArgumentException;

class BookingAkadReportService
{
    public const CATEGORIES = ['booking', 'booking_aktif', 'booking_batal', 'akad'];

    private const MONTH_LABELS = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    private BookingAkadReportRepository $repository;

    public function __construct(?BookingAkadReportRepository $repository = null)
    {
        $this->repository = $repository ?? new BookingAkadReportRepository();
    }

    public function getAvailableYears(int $idProyek): array
    {
        $currentYear = (int) date('Y');
        $bounds = $this->repository->getAvailableYearBounds($idProyek);
        $minimum = min((int) ($bounds['min'] ?? $currentYear - 1), $currentYear - 1);
        $maximum = max((int) ($bounds['max'] ?? $currentYear), $currentYear);

        return range($maximum, $minimum);
    }

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

        foreach ($this->repository->getMonthlyBookingTotals($idProyek, $years) as $row) {
            $year = (int) ($row['tahun'] ?? 0);
            $month = (int) ($row['bulan'] ?? 0);
            if (! isset($summary[$year][$month])) {
                continue;
            }

            $summary[$year][$month]['jumlah_booking'] = (int) ($row['jumlah_booking'] ?? 0);
            $summary[$year][$month]['booking_aktif'] = (int) ($row['booking_aktif'] ?? 0);
            $summary[$year][$month]['booking_batal'] = (int) ($row['booking_batal'] ?? 0);
        }

        foreach ($this->repository->getMonthlyAkadTotals($idProyek, $years) as $row) {
            $year = (int) ($row['tahun'] ?? 0);
            $month = (int) ($row['bulan'] ?? 0);
            if (! isset($summary[$year][$month])) {
                continue;
            }

            $summary[$year][$month]['jumlah_akad'] = (int) ($row['jumlah_akad'] ?? 0);
        }

        $totals = [];
        foreach ($years as $year) {
            $totals[$year] = $this->emptyAmounts();
        }

        $months = [];
        foreach (self::MONTH_LABELS as $month => $label) {
            $values = [];
            foreach ($years as $year) {
                $item = $summary[$year][$month];
                $booking = (int) $item['jumlah_booking'];
                $akad = (int) $item['jumlah_akad'];
                $item['rasio_akad'] = $booking > 0 ? round(($akad / $booking) * 100, 1) : 0.0;

                $values[$year] = $item;

                $totals[$year]['jumlah_booking'] += $item['jumlah_booking'];
                $totals[$year]['booking_aktif'] += $item['booking_aktif'];
                $totals[$year]['booking_batal'] += $item['booking_batal'];
                $totals[$year]['jumlah_akad'] += $item['jumlah_akad'];
            }

            $months[] = [
                'month' => $month,
                'month_label' => $label,
                'values' => $values,
            ];
        }

        foreach ($years as $year) {
            $totalBooking = $totals[$year]['jumlah_booking'];
            $totalAkad = $totals[$year]['jumlah_akad'];
            $totals[$year]['rasio_akad'] = $totalBooking > 0 ? round(($totalAkad / $totalBooking) * 100, 1) : 0.0;
        }

        return [
            'years' => $years,
            'months' => $months,
            'totals' => $totals,
        ];
    }

    public function getDetail(int $idProyek, array $request): array
    {
        $this->assertProject($idProyek);

        $year = (int) ($request['year'] ?? 0);
        $month = (int) ($request['month'] ?? 0);
        $category = trim((string) ($request['category'] ?? ''));

        $this->assertYear($year);
        if ($month < 1 || $month > 12) {
            throw new InvalidArgumentException('Bulan tidak valid.');
        }
        if (! in_array($category, self::CATEGORIES, true)) {
            throw new InvalidArgumentException('Kategori laporan tidak valid.');
        }

        $dataTable = $this->normalizeDataTableRequest($request);
        $result = $this->repository->getDetailRows($idProyek, $year, $month, $category, $dataTable);

        return [
            'draw' => $dataTable['draw'],
            'recordsTotal' => $result['recordsTotal'],
            'recordsFiltered' => $result['recordsFiltered'],
            'data' => $result['data'],
        ];
    }

    public function monthLabel(int $month): string
    {
        return self::MONTH_LABELS[$month] ?? '';
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
            'jumlah_booking' => 0,
            'booking_aktif' => 0,
            'booking_batal' => 0,
            'jumlah_akad' => 0,
            'rasio_akad' => 0.0,
        ];
    }

    private function normalizeDataTableRequest(array $request): array
    {
        $columnIndex = (int) ($request['order'][0]['column'] ?? 3);
        $requestedName = $request['columns'][$columnIndex]['name'] ?? 'tanggal_transaksi';
        $orderColumns = [
            'jenis_laporan' => 'booking_akad.jenis_laporan',
            'alamat_kavling' => 'booking_akad.alamat_kavling',
            'nama_konsumen' => 'booking_akad.nama_konsumen',
            'tipe_rumah' => 'booking_akad.tipe_rumah',
            'tanggal_transaksi' => 'booking_akad.tanggal_transaksi',
            'status_mkdt' => 'booking_akad.status_mkdt',
            'nominal' => 'booking_akad.nominal',
        ];

        $length = (int) ($request['length'] ?? 10);
        if ($length < 1) {
            $length = 10;
        }

        return [
            'draw' => max(0, (int) ($request['draw'] ?? 0)),
            'start' => max(0, (int) ($request['start'] ?? 0)),
            'length' => min($length, 100),
            'search' => trim((string) ($request['search']['value'] ?? '')),
            'order_by' => $orderColumns[$requestedName] ?? 'booking_akad.tanggal_transaksi',
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

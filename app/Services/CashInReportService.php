<?php

namespace App\Services;

use App\Repositories\CashInReportRepository;
use InvalidArgumentException;

class CashInReportService
{
    public const CATEGORIES = ['booking_fee', 'uang_muka', 'hasil_akad', 'all'];

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

    private CashInReportRepository $repository;

    public function __construct(?CashInReportRepository $repository = null)
    {
        $this->repository = $repository ?? new CashInReportRepository();
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

        foreach ($this->repository->getMonthlyPaymentTotals($idProyek, $years) as $row) {
            $year = (int) $row['tahun'];
            $month = (int) $row['bulan'];
            if (! isset($summary[$year][$month])) {
                continue;
            }

            $summary[$year][$month]['booking_fee'] = (float) ($row['booking_fee'] ?? 0);
            $summary[$year][$month]['uang_muka'] = (float) ($row['uang_muka'] ?? 0);
        }

        foreach ($this->repository->getMonthlyAkadTotals($idProyek, $years) as $row) {
            $year = (int) $row['tahun'];
            $month = (int) $row['bulan'];
            if (! isset($summary[$year][$month])) {
                continue;
            }

            $summary[$year][$month]['hasil_akad'] = (float) ($row['hasil_akad'] ?? 0);
        }

        $months = [];
        $totals = [];
        foreach ($years as $year) {
            $totals[$year] = $this->emptyAmounts();
        }

        foreach (self::MONTH_LABELS as $month => $label) {
            $values = [];
            foreach ($years as $year) {
                $amounts = $summary[$year][$month];
                $amounts['total'] = $amounts['booking_fee'] + $amounts['uang_muka'] + $amounts['hasil_akad'];
                $values[$year] = $amounts;

                foreach (array_keys($totals[$year]) as $key) {
                    $totals[$year][$key] += $amounts[$key];
                }
            }

            $months[] = [
                'month' => $month,
                'month_label' => $label,
                'values' => $values,
            ];
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
            throw new InvalidArgumentException('Kategori pendapatan tidak valid.');
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

    private function normalizeDataTableRequest(array $request): array
    {
        $columnIndex = (int) ($request['order'][0]['column'] ?? 3);
        $requestedName = $request['columns'][$columnIndex]['name'] ?? 'tanggal_transaksi';
        $orderColumns = [
            'jenis_pendapatan' => 'cash_in.jenis_pendapatan',
            'alamat_kavling' => 'cash_in.alamat_kavling',
            'nama_konsumen' => 'cash_in.nama_konsumen',
            'tanggal_transaksi' => 'cash_in.tanggal_transaksi',
            'nominal' => 'cash_in.nominal',
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
            'order_by' => $orderColumns[$requestedName] ?? 'cash_in.tanggal_transaksi',
            'order_dir' => strtolower((string) ($request['order'][0]['dir'] ?? 'asc')) === 'desc' ? 'DESC' : 'ASC',
        ];
    }

    private function emptySummary(array $years): array
    {
        $summary = [];
        foreach ($years as $year) {
            for ($month = 1; $month <= 12; $month++) {
                $summary[$year][$month] = $this->emptyAmounts(false);
            }
        }

        return $summary;
    }

    private function emptyAmounts(bool $includeTotal = true): array
    {
        $amounts = [
            'booking_fee' => 0.0,
            'uang_muka' => 0.0,
            'hasil_akad' => 0.0,
        ];

        if ($includeTotal) {
            $amounts['total'] = 0.0;
        }

        return $amounts;
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

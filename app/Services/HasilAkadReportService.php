<?php

namespace App\Services;

use App\Repositories\HasilAkadReportRepository;
use InvalidArgumentException;

class HasilAkadReportService
{
    public const CATEGORIES = ['penjualan', 'acc_kpr', 'pengajuan', 'cair'];

    private const MONTH_LABELS = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    private HasilAkadReportRepository $repository;

    public function __construct(?HasilAkadReportRepository $repository = null)
    {
        $this->repository = $repository ?? new HasilAkadReportRepository();
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
        foreach ($this->repository->getMonthlyAkadTotals($idProyek, $years) as $row) {
            $this->assignAkadRow($summary, $row);
        }
        foreach ($this->repository->getMonthlyPengajuanTotals($idProyek, $years) as $row) {
            $this->assignAmount($summary, $row, 'total_pengajuan');
        }
        foreach ($this->repository->getMonthlyPaymentTotals($idProyek, $years) as $row) {
            $this->assignAmount($summary, $row, 'total_cair');
        }

        $totals = [];
        foreach ($years as $year) {
            $totals[$year] = $this->emptyAmounts();
        }

        $months = [];
        foreach (self::MONTH_LABELS as $month => $label) {
            $values = [];
            foreach ($years as $year) {
                $values[$year] = $summary[$year][$month];
                foreach ($totals[$year] as $key => $value) {
                    $totals[$year][$key] += $summary[$year][$month][$key];
                }
            }
            $months[] = ['month' => $month, 'month_label' => $label, 'values' => $values];
        }

        return ['years' => $years, 'months' => $months, 'totals' => $totals];
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

    private function assignAkadRow(array &$summary, array $row): void
    {
        $year = (int) ($row['tahun'] ?? 0);
        $month = (int) ($row['bulan'] ?? 0);
        if (! isset($summary[$year][$month])) {
            return;
        }

        $summary[$year][$month]['total_penjualan'] = (float) ($row['total_penjualan'] ?? 0);
        $summary[$year][$month]['jumlah_kavling'] = (int) ($row['jumlah_kavling'] ?? 0);
        $summary[$year][$month]['total_acc_kpr'] = (float) ($row['total_acc_kpr'] ?? 0);
    }

    private function assignAmount(array &$summary, array $row, string $key): void
    {
        $year = (int) ($row['tahun'] ?? 0);
        $month = (int) ($row['bulan'] ?? 0);
        if (isset($summary[$year][$month])) {
            $summary[$year][$month][$key] = (float) ($row[$key] ?? 0);
        }
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
            'total_penjualan' => 0.0,
            'jumlah_kavling' => 0,
            'total_acc_kpr' => 0.0,
            'total_pengajuan' => 0.0,
            'total_cair' => 0.0,
        ];
    }

    private function normalizeDataTableRequest(array $request): array
    {
        $columnIndex = (int) ($request['order'][0]['column'] ?? 3);
        $requestedName = $request['columns'][$columnIndex]['name'] ?? 'tanggal_transaksi';
        $orderColumns = [
            'jenis_laporan' => 'hasil_akad.jenis_laporan',
            'alamat_kavling' => 'hasil_akad.alamat_kavling',
            'nama_konsumen' => 'hasil_akad.nama_konsumen',
            'tanggal_transaksi' => 'hasil_akad.tanggal_transaksi',
            'nominal' => 'hasil_akad.nominal',
        ];
        $length = max(1, (int) ($request['length'] ?? 10));

        return [
            'draw' => max(0, (int) ($request['draw'] ?? 0)),
            'start' => max(0, (int) ($request['start'] ?? 0)),
            'length' => min($length, 100),
            'search' => trim((string) ($request['search']['value'] ?? '')),
            'order_by' => $orderColumns[$requestedName] ?? 'hasil_akad.tanggal_transaksi',
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

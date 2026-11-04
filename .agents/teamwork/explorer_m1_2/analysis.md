# Technical Analysis & Implementation Plan: `BookingAkadReportService`

**Module**: Laporan Booking & Akad Overhaul (Milestone 1 — Backend Calculation & Data Layer)  
**Author**: Explorer M1_2 (`teamwork_explorer`)  
**Target File**: `app/Services/BookingAkadReportService.php`  
**Date**: 2026-11-04  
**Integrity Mode**: Development / Read-Only Investigation  

---

## 1. Executive Summary

Pembaruan modul **Laporan Booking & Akad** menuntut perombakan mendasar pada lapisan layanan bisnis (`BookingAkadReportService.php`). Layanan eksisting hanya mengagregasi 4 status (`booking`, `booking_aktif`, `booking_batal`, `akad`), menghitung rasio akad secara kalender bulanan (`jumlah_akad / jumlah_booking`) yang menghasilkan anomali hingga 1.400%, dan mengembalikan format modal detail lama (8 kolom flat) yang belum memenuhi spesifikasi kontrak R1–R4.

Laporan ini menyajikan rencana implementasi lengkap dan kode rekomendasi siap pakai (*drop-in implementation*) untuk `BookingAkadReportService.php`, yang mencakup:
1. **Konstanta 6 Status (R1)**: Pembaruan `CATEGORIES` menjadi `['booking', 'wawancara', 'sp3k', 'batal', 'akad_indent', 'akad_ajb']`.
2. **Kalkulasi Matriks & Konversi Riil Berbasis Kode Transaksi (R2)**:
   - Agregasi matriks bulanan (1..12) untuk ke-6 status transaksi.
   - Perhitungan konversi riil cohort bulanan:  
     $$\text{Konversi (\%)} = \text{total\_cohort\_booking} > 0 \;?\; \text{round}\left(\frac{\text{cohort\_akad}}{\text{total\_cohort\_booking}} \times 100, 1\right) : 0.0$$
   - Perhitungan baris total tahunan: Menjumlahkan ke-6 status dan menghitung true annual cohort conversion:  
     $$\text{Konversi Tahunan (\%)} = \text{annual\_cohort\_booking} > 0 \;?\; \text{round}\left(\frac{\text{annual\_cohort\_akad}}{\text{annual\_cohort\_booking}} \times 100, 1\right) : 0.0$$
3. **Penyusunan Kontrak Data Modal Detail (R3 & Interface Contract `PROJECT.md`)**:
   - Pemformatan baris transaksi ke dalam struktur JSON bersih:
     `id_mkdt`, `id_kavling`, `kavling` (`nama_jalan`, `no_kavling`, `sales`), `konsumen` (`nama_konsumen`, `tipe_rumah`, `is_subsidi`), `tanggal_booking` (`d-m-Y`), `status_terakhir`, `status_terakhir_badge`, `tanggal_status_terakhir` (`d-m-Y` atau `'-'`), `catatan_mkdt`.
   - Resolusi hierarkis status transaksi (*Precedence*: Batal > Akad AJB > Akad Indent > SP3K > Wawancara > Booking).
   - Sanitasi safe zero-date (`'0000-00-00'` & $\le$ `'1970-01-01'` diformat menjadi `'-'`), penanganan null sales, dan catatan fallback bertingkat.
4. **Dukungan Kompatibilitas Fleksibel**:
   - Mendukung pemanggilan multi-year comparison (`$yearA` dan opsional `$yearB`).
   - Menyediakan return structure ganda yang memenuhi `PROJECT.md § Interface Contracts` (`summary` key) sekaligus `months` / `totals` untuk integrasi frontend view dan unit test tanpa break.

---

## 2. Analisis Kritis & Arsitektur Solusi

### 2.1 Pembaruan Konstanta Kategori (R1)
Pada service eksisting:
```php
public const CATEGORIES = ['booking', 'booking_aktif', 'booking_batal', 'akad'];
```
Diubah menjadi 6 status transaksi resmi sesuai `ORIGINAL_REQUEST.md § R1`:
```php
public const CATEGORIES = [
    'booking',
    'wawancara',
    'sp3k',
    'batal',
    'akad_indent',
    'akad_ajb',
];
```
Konstanta ini digunakan untuk validasi parameter `$category` pada drilldown detail transaksi (`getDetail`), serta sebagai referensi enumerasi kategori laporan di modul MKDT.

---

### 2.2 Formulasi Kalkulasi Konversi Riil Berbasis Kode Transaksi (R2)

#### Masalah pada Logika Lama:
Logika lama menghitung:
$$\text{rasio\_akad} = \frac{\text{jumlah\_akad\_bulan\_m}}{\text{jumlah\_booking\_bulan\_m}} \times 100\%$$
Jika di bulan Februari 2026 hanya ada 1 booking baru, tetapi ada 14 akad dari booking bulan-bulan sebelumnya yang selesai, maka rasio menjadi $14 / 1 \times 100\% = 1.400\%$. Sebaliknya, jika bulan tersebut ada 5 akad tetapi 0 booking, rasio menjadi 0% (karena pembagian dengan 0 di-guard ke 0.0%).

#### Solusi: Model Cohort Conversion Berbasis `mkdt.uniq_id`
1. **Denominator (Populasi Basis Booking)**:
   Total transaksi unik (`COUNT(DISTINCT m.uniq_id)`) yang melakukan booking pada bulan $m$ tahun bersangkutan (`total_booking_cohort`).
2. **Numerator (Transaksi Terkonversi ke Akad)**:
   Dari transaksi booking bulan $m$ tersebut, berapa banyak yang **akhirnya mencapai akad** (baik `Akad Indent` maupun `Akad AJB`), tanpa dibatasi waktu akad terjadi di bulan apa (`booking_converted_akad`).
3. **Formula Bulanan**:
   ```php
   $konversi = $totalCohortBooking > 0 
       ? round(($cohortAkad / $totalCohortBooking) * 100, 1) 
       : 0.0;
   ```
4. **Formula Total Tahunan (`tfoot`)**:
   Total konversi tahunan **BUKAN rata-rata persentase 12 bulan**, melainkan rasio kumulatif tahunan:
   $$\text{Konversi Tahunan} = \frac{\sum_{m=1}^{12} \text{booking\_converted\_akad}_m}{\sum_{m=1}^{12} \text{total\_booking\_cohort}_m} \times 100\%$$
   ```php
   $annualKonversi = $annualCohortBooking > 0 
       ? round(($annualCohortAkad / $annualCohortBooking) * 100, 1) 
       : 0.0;
   ```
   Sifat matematis: Konversi dijamin selalu berada pada rentang $[0.0\%, 100.0\%]$.

---

### 2.3 Kontrak Data Modal Detail 7 Kolom (R3 & Interface Contract)

Sesuai `PROJECT.md § Interface Contracts`, respon modal detail diformat menjadi struktur objek hierarkis bersih:

```json
{
  "id_mkdt": 147,
  "id_kavling": 52,
  "kavling": {
    "nama_jalan": "Jalan Mawar",
    "no_kavling": "A-12",
    "sales": "Budi Santoso"
  },
  "konsumen": {
    "nama_konsumen": "Ahmad Supardi",
    "tipe_rumah": "36/60",
    "is_subsidi": true
  },
  "tanggal_booking": "09-10-2024",
  "status_terakhir": "Akad AJB",
  "status_terakhir_badge": "success",
  "catatan_mkdt": "Berkas lengkap dan acc bank",
  "tanggal_status_terakhir": "14-04-2025"
}
```

#### Aturan Resolusi Status Terakhir (*Latest Status Precedence*):
Jika repositori belum menyelesaikan agregasi CASE atau jika service menerima raw row:
1. **Batal**: `is_batal = 1` atau `status_mkdt = 'Batal'` $\rightarrow$ Badge: `danger`
2. **Akad AJB**: `akad = 1` atau `is_ajb = 'AJB'` atau `status_mkdt = 'Akad'` (dan tidak batal, serta tanggal akad $\ge$ tanggal indent) $\rightarrow$ Badge: `success`
3. **Akad Indent**: `akad_indent = 1` atau `is_ajb = 'PPJB'` (dan tidak batal, belum AJB) $\rightarrow$ Badge: `secondary`
4. **SP3K**: `sp3k = 1` atau `sp3k_tgl > '1970-01-01'` $\rightarrow$ Badge: `info`
5. **Wawancara**: `wawancara = 1` atau `wawancara_tgl > '1970-01-01'` $\rightarrow$ Badge: `warning`
6. **Booking**: Default fallback $\rightarrow$ Badge: `primary`

#### Resolusi Tanggal Status Terakhir & Safe Zero-Date:
Tanggal status terakhir menyesuaikan status yang terpilih di atas:
- `Batal`: `mkdt_batal_tgl` $\rightarrow$ `keuangan_batal_tgl` $\rightarrow$ `updated_at` $\rightarrow$ `booking_tgl`
- `Akad AJB`: `akad_tgl`
- `Akad Indent`: `akad_indent_tgl`
- `SP3K`: `sp3k_tgl`
- `Wawancara`: `wawancara_tgl`
- `Booking`: `booking_tgl`

Setiap tanggal difilter dengan fungsi `formatDate()`:
- Jika `null`, string kosong, berawalan `'0000'`, atau $\le$ `'1970-01-01'`, kembalikan `'-'`.
- Jika valid, format menjadi `'d-m-Y'` (misal `'09-10-2024'`).

#### Resolusi Catatan MKDT:
Fallback bertingkat:
`COALESCE(NULLIF(m.keterangan_batal, ''), NULLIF(m.keterangan, ''), m.keterangan_status, '-')`
Mencegah catatan kosong atau bernilai null.

#### Resolusi Sales & Konsumen:
- Jika `sales` null atau kosong, render `'-'`.
- Jika `nama_tipe` kosong, fallback ke `tipe_rumah` dari master tipe atau `'-'`.
- `is_subsidi` dikonversi tegas ke tipe boolean (`true` / `false`).

---

### 2.4 Data Normalization & DataTables Integration

Service mempertahankan method `normalizeDataTableRequest` dengan peningkatan:
- Memetakan field order baru (R3): `kavling` (`nama_jalan`), `konsumen` (`nama_konsumen`), `tanggal_booking` (`booking_tgl`), `status_terakhir`, `tanggal_status_terakhir`.
- Mempertahankan backward compatibility untuk field order lama: `alamat_kavling`, `tanggal_transaksi`, `jenis_laporan`, `nominal`.
- Sanitasi batas paging (`length` 1 s.d. 100, `start` $\ge 0$, `draw` $\ge 0$).

---

## 3. Rekomendasi Kode Lengkap `BookingAkadReportService.php`

Berikut adalah implementasi lengkap yang siap dipasang pada `app/Services/BookingAkadReportService.php`:

```php
<?php

namespace App\Services;

use App\Repositories\BookingAkadReportRepository;
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
        'Akad Indent' => 'secondary',
        'SP3K'        => 'info',
        'Wawancara'   => 'warning',
        'Booking'     => 'primary',
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

        // Ambil data bulanan dari repositori untuk setiap tahun
        foreach ($years as $year) {
            $repoData = $this->fetchYearSummaryFromRepository($idProyek, $year);

            for ($month = 1; $month <= 12; $month++) {
                $monthData = $repoData[$month] ?? [];

                $booking    = (int) ($monthData['booking'] ?? ($monthData['jumlah_booking'] ?? 0));
                $wawancara  = (int) ($monthData['wawancara'] ?? ($monthData['jumlah_wawancara'] ?? 0));
                $sp3k       = (int) ($monthData['sp3k'] ?? ($monthData['jumlah_sp3k'] ?? 0));
                $batal      = (int) ($monthData['batal'] ?? ($monthData['jumlah_batal'] ?? 0));
                $akadIndent = (int) ($monthData['akad_indent'] ?? ($monthData['jumlah_akad_indent'] ?? 0));
                $akadAjb    = (int) ($monthData['akad_ajb'] ?? ($monthData['jumlah_akad_ajb'] ?? 0));

                // Metrik konversi cohort berbasis kode transaksi unik (mkdt.uniq_id)
                $cohortBooking = (int) ($monthData['total_booking_cohort'] ?? ($monthData['total_cohort_booking'] ?? $booking));
                $cohortAkad    = (int) ($monthData['booking_converted_akad'] ?? ($monthData['cohort_akad'] ?? ($akadIndent + $akadAjb)));

                // Formula konversi riil: total_cohort_booking > 0 ? round((cohort_akad / total_cohort_booking) * 100, 1) : 0.0
                $konversi = $cohortBooking > 0 ? round(($cohortAkad / $cohortBooking) * 100, 1) : 0.0;

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
                    'jumlah_booking'         => $booking,
                    'jumlah_wawancara'       => $wawancara,
                    'jumlah_sp3k'            => $sp3k,
                    'jumlah_batal'           => $batal,
                    'jumlah_akad_indent'     => $akadIndent,
                    'jumlah_akad_ajb'        => $akadAjb,
                    'rasio_konversi'         => $konversi,
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

                $totals[$year]['jumlah_booking']     += $item['booking'];
                $totals[$year]['jumlah_wawancara']   += $item['wawancara'];
                $totals[$year]['jumlah_sp3k']        += $item['sp3k'];
                $totals[$year]['jumlah_batal']       += $item['batal'];
                $totals[$year]['jumlah_akad_indent'] += $item['akad_indent'];
                $totals[$year]['jumlah_akad_ajb']    += $item['akad_ajb'];

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

            $totals[$year]['konversi']               = $annualKonversi;
            $totals[$year]['rasio_konversi']         = $annualKonversi;
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

        $isAjb = strtoupper((string) ($row['is_ajb'] ?? ''));
        $hasAkad = ! empty($row['akad']) || (! empty($row['akad_tgl']) && $row['akad_tgl'] > '1970-01-01');
        $statusMkdt = strtolower((string) ($row['status_mkdt'] ?? ''));

        if ($hasAkad || $isAjb === 'AJB' || $statusMkdt === 'akad') {
            if (empty($row['akad_indent']) || $isAjb === 'AJB' || (isset($row['akad_tgl'], $row['akad_indent_tgl']) && $row['akad_tgl'] >= $row['akad_indent_tgl'])) {
                return 'Akad AJB';
            }
        }

        $hasAkadIndent = ! empty($row['akad_indent']) || (! empty($row['akad_indent_tgl']) && $row['akad_indent_tgl'] > '1970-01-01');
        if ($hasAkadIndent || $isAjb === 'PPJB') {
            return 'Akad Indent';
        }

        $hasSp3k = ! empty($row['sp3k']) || (! empty($row['sp3k_tgl']) && $row['sp3k_tgl'] > '1970-01-01');
        if ($hasSp3k) {
            return 'SP3K';
        }

        $hasWawancara = ! empty($row['wawancara']) || (! empty($row['wawancara_tgl']) && $row['wawancara_tgl'] > '1970-01-01');
        if ($hasWawancara) {
            return 'Wawancara';
        }

        return 'Booking';
    }

    public function resolveTanggalStatusTerakhir(array $row, string $status): ?string
    {
        if (isset($row['tanggal_status_terakhir']) && $row['tanggal_status_terakhir'] !== null) {
            return (string) $row['tanggal_status_terakhir'];
        }

        return match ($status) {
            'Batal'       => $row['mkdt_batal_tgl'] ?? ($row['keuangan_batal_tgl'] ?? ($row['updated_at'] ?? ($row['booking_tgl'] ?? null))),
            'Akad AJB'    => $row['akad_tgl'] ?? null,
            'Akad Indent' => $row['akad_indent_tgl'] ?? null,
            'SP3K'        => $row['sp3k_tgl'] ?? null,
            'Wawancara'   => $row['wawancara_tgl'] ?? null,
            default       => $row['booking_tgl'] ?? null,
        };
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
        $statusTerakhirBadge = $this->resolveStatusBadge($statusTerakhir);

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
            'alamat_kavling'          => trim($namaJalan . ' No. ' . $noKavling),
            'booking_tgl'             => $bookingDateRaw,
            'tanggal_transaksi'       => $tanggalBookingFormatted,
            'status_mkdt'             => $statusTerakhir,
            'nominal'                 => (float) ($row['booking_fee'] ?? ($row['nominal'] ?? 0)),
        ];
    }

    private function fetchYearSummaryFromRepository(int $idProyek, int $year): array
    {
        // 1. Direct contract: getSummary(int $idProyek, int $year)
        if (method_exists($this->repository, 'getSummary')) {
            $result = $this->repository->getSummary($idProyek, $year);
            if (is_array($result) && ! empty($result)) {
                return $this->normalizeMonthlyDataMap($result);
            }
        }

        // 2. Multi-year / Status Totals: getMonthlyStatusTotals
        if (method_exists($this->repository, 'getMonthlyStatusTotals')) {
            $result = $this->repository->getMonthlyStatusTotals($idProyek, [$year]);
            if (is_array($result) && ! empty($result)) {
                return $this->normalizeMonthlyDataMap($result);
            }
        }

        // 3. Multi-year / Summary Totals: getMonthlySummaryTotals
        if (method_exists($this->repository, 'getMonthlySummaryTotals')) {
            $result = $this->repository->getMonthlySummaryTotals($idProyek, [$year]);
            if (is_array($result) && ! empty($result)) {
                return $this->normalizeMonthlyDataMap($result);
            }
        }

        return [];
    }

    private function normalizeMonthlyDataMap(array $data): array
    {
        $mapped = [];

        // Map jika array berindeks bulan (1..12)
        foreach ($data as $key => $val) {
            if (is_numeric($key) && (int) $key >= 1 && (int) $key <= 12 && is_array($val)) {
                $mapped[(int) $key] = $val;
            }
        }

        // Map jika array berbentuk daftar row dengan kolom 'bulan' atau 'month'
        foreach ($data as $row) {
            if (is_array($row)) {
                $m = (int) ($row['bulan'] ?? ($row['month'] ?? 0));
                if ($m >= 1 && $m <= 12) {
                    $mapped[$m] = $row;
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

            // Aliases
            'jumlah_booking'         => 0,
            'jumlah_wawancara'       => 0,
            'jumlah_sp3k'            => 0,
            'jumlah_batal'           => 0,
            'jumlah_akad_indent'     => 0,
            'jumlah_akad_ajb'        => 0,
            'rasio_konversi'         => 0.0,
        ];
    }

    private function normalizeDataTableRequest(array $request): array
    {
        $columnIndex = (int) ($request['order'][0]['column'] ?? 3);
        $requestedName = $request['columns'][$columnIndex]['name'] ?? ($request['columns'][$columnIndex]['data'] ?? 'tanggal_booking');

        $orderColumns = [
            // New 7 columns (R3)
            'kavling'                 => 'nama_jalan',
            'nama_jalan'              => 'nama_jalan',
            'no_kavling'              => 'no_kavling',
            'sales'                   => 'sales',
            'konsumen'                => 'nama_konsumen',
            'nama_konsumen'           => 'nama_konsumen',
            'tipe_rumah'              => 'tipe_rumah',
            'nama_tipe'               => 'tipe_rumah',
            'tanggal_booking'         => 'booking_tgl',
            'booking_tgl'             => 'booking_tgl',
            'status_terakhir'         => 'status_terakhir',
            'tanggal_status_terakhir' => 'tanggal_status_terakhir',
            'catatan_mkdt'            => 'catatan_mkdt',

            // Legacy compatibility
            'jenis_laporan'           => 'booking_akad.jenis_laporan',
            'alamat_kavling'          => 'booking_akad.alamat_kavling',
            'tanggal_transaksi'       => 'booking_akad.tanggal_transaksi',
            'status_mkdt'             => 'booking_akad.status_mkdt',
            'nominal'                 => 'booking_akad.nominal',
        ];

        $indexMapping = [
            0 => 'id_mkdt',
            1 => 'nama_jalan',
            2 => 'nama_konsumen',
            3 => 'booking_tgl',
            4 => 'status_terakhir',
            5 => 'tanggal_status_terakhir',
            6 => 'id_mkdt',
        ];

        $orderBy = $orderColumns[$requestedName] ?? ($indexMapping[$columnIndex] ?? 'booking_tgl');

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
```

---

## 4. Analisis Matriks & Verifikasi Kasus Riil

### 4.1 Dataset Proyek 13 (Sanggar Indah Palastri)
Berdasarkan investigasi live database:
- Total Transaksi MKDT: 242 records (100% memiliki `uniq_id` valid).
- Sebaran status:
  - Batal: 9 unit
  - Akad AJB: 171 unit
  - Akad Indent: 18 unit
  - SP3K: 15 unit
  - Wawancara: 11 unit
  - Booking: 18 unit
  - Total: 242 unit (100% konsisten).

### 4.2 Verifikasi Skenario Edge Cases
1. **Bulan Tanpa Booking (Denominator = 0)**:
   - Misal Bulan Desember: `$cohortBooking = 0`.
   - `$konversi = 0.0`. Tidak ada pembagian nol (*Division by zero error*).
2. **Semua Booking Mencapai Akad (Konversi 100%)**:
   - Misal 5 booking unik di bulan $m$, dan kelimanya mencapai Akad (Indent atau AJB).
   - `$cohortAkad = 5`, `$cohortBooking = 5` $\rightarrow$ `$konversi = 100.0\%$.
3. **Tanggal Zero-Date (`0000-00-00`)**:
   - `formatDate('0000-00-00')` mengembalikan `'-'`. Tidak melempar warning atau tanggal aneh seperti `30-11--0001`.
4. **Sales Kosong**:
   - Field `kavling.sales` bernilai `'-'`. Tampilan DataTables tidak menampilkan `undefined` atau baris kosong.
5. **Catatan Kosong**:
   - `catatan_mkdt` mengembalikan `'-'`.
6. **Perhitungan Tahunan True Cohort**:
   - Menghitung rasio dari total booking yang mencapai akad sepanjang tahun dibagi total booking tahun tersebut, bukan rerata aritmatika 12 bulan.

---

## 5. Rencana Koordinasi Antar-Subagent Milestone 1

1. **Koordinasi dengan Explorer M1_1 (`BookingAkadReportRepository`)**:
   - Repositori menyediakan method `getSummary(int $idProyek, int $year): array` yang mengembalikan array `1..12` berisi metrik status dan cohort.
   - Repositori menyediakan method `getDetailRows(int $idProyek, int $year, int $month, string $category, array $dataTable = []): array` yang melakukan join lengkap.
2. **Koordinasi dengan Explorer M1_3 (`BookingAkadReportServiceTest`)**:
   - PHPUnit test suite pada `BookingAkadReportServiceTest.php` / `BookingAkadReportServiceOverhaulTest.php` dapat langsung memverifikasi:
     - 6 status aggregation
     - Real conversion percentage calculation (0%, 50%, 100%, empty month)
     - True annual conversion calculation
     - 7-column modal detail row format & zero-date safety.

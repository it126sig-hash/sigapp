<?php

namespace App\Services;

use DateTimeImmutable;
use DateTimeInterface;

class SiteplanVisualStatusService
{
    private const MAX_PENGAJUAN_MARKERS = 3;

    public function appendVisualRows(array $rows, ?DateTimeInterface $today = null): array
    {
        foreach ($rows as $index => $row) {
            $visualRows = $this->buildRows($row, $today);

            if (is_object($row)) {
                $row->visual_rows = $visualRows;
            } else {
                $row['visual_rows'] = $visualRows;
                $rows[$index] = $row;
            }
        }

        return $rows;
    }

    public function buildRows($row, ?DateTimeInterface $today = null): array
    {
        $today = $today ?? new DateTimeImmutable('today');

        return [
            $this->mkdtRow($row),
            $this->productionRow($row),
            $this->financeRow($row, $today),
        ];
    }

    private function mkdtRow($row): array
    {
        $status = 'Def';

        if ($this->truthy($this->value($row, 'is_batal')) || $this->value($row, 'status_mkdt') === 'Batal') {
            $status = 'Batal';
        } elseif ($this->truthy($this->value($row, 'akad')) || $this->value($row, 'status_mkdt') === 'Akad' || $this->hasDate($this->value($row, 'akad_tgl'))) {
            $status = 'Akad';
        } elseif ($this->truthy($this->value($row, 'akad_indent'))) {
            $status = 'Akad Indent';
        } elseif ($this->truthy($this->value($row, 'sp3k')) || $this->hasDate($this->value($row, 'sp3k_tgl'))) {
            $status = 'SP3K';
        } elseif ($this->truthy($this->value($row, 'wawancara')) || $this->hasDate($this->value($row, 'wawancara_tgl'))) {
            $status = 'Wawancara';
        } elseif ($this->value($row, 'status_mkdt') === 'Booking' || $this->hasDate($this->value($row, 'booking_tgl'))) {
            $status = 'Booking';
        }

        if ($status !== 'Def' && $status !== 'Batal') {
            $status = $this->withMarketSuffix($status, $this->value($row, 'is_subsidi'));
        }

        return $this->row('mkdt', 'MKDT', [$this->segment($status, 1)]);
    }

    private function productionRow($row): array
    {
        $progress = max(0, (float) ($this->value($row, 'progres_bangunan') ?? 0));
        $status = 'Def';

        if ($progress >= 100) {
            $status = 'Bangunan 100%';
        } elseif ($progress > 0) {
            $status = 'Pembangunan';
        }

        $markers = [];
        if ($progress < 100 && $this->truthy($this->value($row, 'is_turun_pembangunan'))) {
            $markers[] = $this->marker('Perintah Bangun');
        }

        return $this->row('produksi', 'Produksi', [$this->segment($status, 1)], $markers);
    }

    private function financeRow($row, DateTimeInterface $today): array
    {
        $hasConsumer = (int) ($this->value($row, 'visual_id_konsumen') ?? 0) > 0;
        $hasActiveBill = (int) ($this->value($row, 'tagihan_aktif_count') ?? 0) > 0;

        if (!$hasConsumer || !$hasActiveBill) {
            return $this->row('keuangan', 'Keuangan', [$this->segment('Def', 1)]);
        }

        $isLunas = $this->truthy($this->value($row, 'is_lunas'));
        $markers = [];

        if (!$isLunas) {
            if ($this->isDue($this->value($row, 'jatuh_tempo_tgl'), $today)) {
                $markers[] = $this->marker('Jatuh Tempo');
            }

            return $this->row('keuangan', 'Keuangan', [$this->segment('Belum Lunas', 1)], $markers);
        }

        $outstandingCount = max(0, (int) ($this->value($row, 'pa_pengajuan_outstanding_count') ?? 0));
        $submissionCount = max(
            $outstandingCount,
            (int) ($this->value($row, 'pa_pengajuan_count') ?? 0)
        );
        $submissionMarkers = $this->submissionMarkers($outstandingCount);

        if ($submissionCount === 0) {
            return $this->row(
                'keuangan',
                'Keuangan',
                [$this->segment('Lunas', 1)],
                [],
                $this->financeMeta(null, 0)
            );
        }

        $totalResult = max(0, (float) ($this->value($row, 'pa_total_hasil_akad') ?? 0));
        $totalDisbursed = max(0, (float) ($this->value($row, 'pa_total_cair_sum') ?? 0));
        if ($totalResult <= 0) {
            return $this->row(
                'keuangan',
                'Keuangan',
                [$this->segment('Lunas', 1)],
                $submissionMarkers,
                $this->financeMeta(null, $outstandingCount)
            );
        }

        $disbursedRatio = min(1, $totalDisbursed / $totalResult);
        if ($disbursedRatio <= 0) {
            return $this->row(
                'keuangan',
                'Keuangan',
                [$this->segment('Lunas', 1)],
                $submissionMarkers,
                $this->financeMeta(0, $outstandingCount)
            );
        }

        $segments = [$this->segment('Pencairan Hasil Akad', $disbursedRatio)];

        if ($disbursedRatio < 1) {
            $segments[] = $this->segment('Lunas', 1 - $disbursedRatio);
        }

        return $this->row(
            'keuangan',
            'Keuangan',
            $segments,
            $submissionMarkers,
            $this->financeMeta($disbursedRatio, $outstandingCount)
        );
    }

    private function row(string $key, string $label, array $segments, array $markers = [], array $meta = []): array
    {
        $row = [
            'key' => $key,
            'label' => $label,
            'segments' => $segments,
            'markers' => $markers,
        ];

        if ($meta !== []) {
            $row['meta'] = $meta;
        }

        return $row;
    }

    private function segment(string $configName, float $ratio): array
    {
        return [
            'config_name' => $configName,
            'ratio' => round(max(0, min(1, $ratio)), 4),
        ];
    }

    private function marker(string $configName, float $position = 0.5): array
    {
        return [
            'config_name' => $configName,
            'position' => round(max(0, min(1, $position)), 4),
        ];
    }

    private function submissionMarkers(int $outstandingCount): array
    {
        $visibleCount = min(self::MAX_PENGAJUAN_MARKERS, max(0, $outstandingCount));
        $markers = [];

        for ($index = 1; $index <= $visibleCount; $index++) {
            $markers[] = $this->marker(
                'Pengajuan Pencairan Hasil Akad',
                $index / ($visibleCount + 1)
            );
        }

        return $markers;
    }

    private function financeMeta(?float $disbursementRatio, int $outstandingCount): array
    {
        return [
            'disbursement_ratio' => $disbursementRatio === null
                ? null
                : round(max(0, min(1, $disbursementRatio)), 4),
            'outstanding_submission_count' => max(0, $outstandingCount),
        ];
    }

    private function isDue($value, DateTimeInterface $today): bool
    {
        if (!$this->hasDate($value)) {
            return false;
        }

        $due = DateTimeImmutable::createFromFormat('!Y-m-d', substr((string) $value, 0, 10));

        return $due !== false && $due <= DateTimeImmutable::createFromInterface($today)->setTime(0, 0);
    }

    private function hasDate($value): bool
    {
        if (!is_string($value) || $value === '' || str_starts_with($value, '0000-00-00')) {
            return false;
        }

        $dateValue = substr($value, 0, 10);
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $dateValue);

        return $date !== false && $date->format('Y-m-d') === $dateValue;
    }

    private function truthy($value): bool
    {
        return $value === true || $value === 1 || $value === '1';
    }

    private function withMarketSuffix(string $status, $isSubsidi): string
    {
        if ($isSubsidi === true || $isSubsidi === 1 || $isSubsidi === '1') {
            return $status . ' Subsidi';
        }

        if ($isSubsidi === false || $isSubsidi === 0 || $isSubsidi === '0') {
            return $status . ' Komersil';
        }

        return $status;
    }

    private function value($row, string $key)
    {
        if (is_array($row)) {
            return $row[$key] ?? null;
        }

        return $row->{$key} ?? null;
    }
}

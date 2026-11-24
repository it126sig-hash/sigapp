<?php

namespace App\Repositories\Copilot;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\BaseConnection;
use DateTimeImmutable;
use InvalidArgumentException;

class JatuhTempoRepository
{
    public const DEFAULT_DAYS = 7;
    public const MAX_DAYS = 90;
    public const DEFAULT_LIMIT = 50;

    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
    }

    /**
     * Mengambil ringkasan & daftar tagihan cicilan jatuh tempo secara read-only dengan SELECT whitelist ketat.
     *
     * @param int[] $allowedProjectIds
     * @return array<string, mixed>
     */
    public function getJatuhTempo(
        array $allowedProjectIds,
        int $days = self::DEFAULT_DAYS,
        int $limit = self::DEFAULT_LIMIT,
        ?DateTimeImmutable $today = null,
        string $kategori = 'semua'
    ): array {
        $projectIds = array_values(array_unique(array_filter(
            array_map('intval', $allowedProjectIds),
            static fn (int $id): bool => $id > 0
        )));

        if ($projectIds === []) {
            throw new InvalidArgumentException('allowedProjectIds wajib berisi minimal satu id_proyek yang valid.');
        }

        $days = max(0, min(self::MAX_DAYS, $days));
        $limit = max(1, $limit);
        $kategori = in_array($kategori, ['semua', 'terlambat', 'akan_jatuh_tempo'], true) ? $kategori : 'semua';

        $todayDate = ($today ?? new DateTimeImmutable('today'))->setTime(0, 0, 0);
        $cutoffDate = $todayDate->modify('+' . $days . ' days');

        $todayStr = $todayDate->format('Y-m-d');
        $cutoffStr = $cutoffDate->format('Y-m-d');
        $escapedToday = $this->db->escape($todayStr);

        $summaryRow = $this->baseBuilder($projectIds, $cutoffStr)
            ->select("COALESCE(SUM(CASE WHEN k.jatuh_tempo_tgl < {$escapedToday} THEN 1 ELSE 0 END), 0) AS terlambat_count", false)
            ->select("COALESCE(SUM(CASE WHEN k.jatuh_tempo_tgl < {$escapedToday} THEN k.nominal ELSE 0 END), 0) AS terlambat_nominal", false)
            ->select("COALESCE(SUM(CASE WHEN k.jatuh_tempo_tgl >= {$escapedToday} THEN 1 ELSE 0 END), 0) AS akan_jatuh_tempo_count", false)
            ->select("COALESCE(SUM(CASE WHEN k.jatuh_tempo_tgl >= {$escapedToday} THEN k.nominal ELSE 0 END), 0) AS akan_jatuh_tempo_nominal", false)
            ->get()
            ->getRowArray() ?? [];

        $terlambatCount = (int) ($summaryRow['terlambat_count'] ?? 0);
        $terlambatNominal = (float) ($summaryRow['terlambat_nominal'] ?? 0.0);
        $akanCount = (int) ($summaryRow['akan_jatuh_tempo_count'] ?? 0);
        $akanNominal = (float) ($summaryRow['akan_jatuh_tempo_nominal'] ?? 0.0);

        $terlambatRows = [];
        if ($kategori === 'semua' || $kategori === 'terlambat') {
            $rawOverdue = $this->selectWhitelistBuilder($projectIds, $cutoffStr)
                ->where('k.jatuh_tempo_tgl <', $todayStr)
                ->orderBy('k.jatuh_tempo_tgl', 'ASC')
                ->orderBy('k.id_keuangan', 'ASC')
                ->limit($limit)
                ->get()
                ->getResultArray();

            foreach ($rawOverdue as $row) {
                $terlambatRows[] = $this->mapRow($row, $todayDate, true);
            }
        }

        $akanRows = [];
        if ($kategori === 'semua' || $kategori === 'akan_jatuh_tempo') {
            $rawUpcoming = $this->selectWhitelistBuilder($projectIds, $cutoffStr)
                ->where('k.jatuh_tempo_tgl >=', $todayStr)
                ->orderBy('k.jatuh_tempo_tgl', 'ASC')
                ->orderBy('k.id_keuangan', 'ASC')
                ->limit($limit)
                ->get()
                ->getResultArray();

            foreach ($rawUpcoming as $row) {
                $akanRows[] = $this->mapRow($row, $todayDate, false);
            }
        }

        return [
            'as_of_date'  => $todayStr,
            'cutoff_date' => $cutoffStr,
            'days'        => $days,
            'limit'       => $limit,
            'kategori'    => $kategori,
            'summary'     => [
                'terlambat' => [
                    'count'         => $terlambatCount,
                    'total_nominal' => $terlambatNominal,
                    'truncated'     => ($kategori === 'semua' || $kategori === 'terlambat') && $terlambatCount > count($terlambatRows),
                ],
                'akan_jatuh_tempo' => [
                    'count'         => $akanCount,
                    'total_nominal' => $akanNominal,
                    'truncated'     => ($kategori === 'semua' || $kategori === 'akan_jatuh_tempo') && $akanCount > count($akanRows),
                ],
                'total' => [
                    'count'         => $terlambatCount + $akanCount,
                    'total_nominal' => $terlambatNominal + $akanNominal,
                ],
            ],
            'terlambat'        => $terlambatRows,
            'akan_jatuh_tempo' => $akanRows,
        ];
    }

    /**
     * @param int[] $projectIds
     */
    private function selectWhitelistBuilder(array $projectIds, string $cutoffDate): BaseBuilder
    {
        return $this->baseBuilder($projectIds, $cutoffDate)->select(
            'k.id_keuangan, k.berita_acara, k.jatuh_tempo_tgl, k.nominal, '
            . 'c.nama_konsumen, kv.no_kavling, jn.nama_jalan, cl.nama_cluster, '
            . 'p.id_proyek, p.nama_proyek'
        );
    }

    /**
     * @param int[] $projectIds
     */
    private function baseBuilder(array $projectIds, string $cutoffDate): BaseBuilder
    {
        return $this->db->table('keuangan k')
            ->join('mkdt m', 'm.id_mkdt = k.id_mkdt')
            ->join('kavling kv', 'm.id_mkdt = kv.id_mkdt')
            ->join('jalan jn', 'kv.id_jalan = jn.id_jalan')
            ->join('cluster cl', 'cl.id_cluster = jn.id_cluster')
            ->join('proyek p', 'p.id_proyek = cl.id_proyek')
            ->join('konsumen c', 'c.id_konsumen = m.id_konsumen')
            ->where('k.sudah_dibayar', 0)
            ->where('k.is_void', 0)
            ->where('m.is_batal', 0)
            ->where("(m.status_mkdt IS NULL OR LOWER(m.status_mkdt) != 'batal')", null, false)
            ->where('(m.is_lunas IS NULL OR m.is_lunas = 0)', null, false)
            ->where('k.jatuh_tempo_tgl IS NOT NULL', null, false)
            ->where('k.jatuh_tempo_tgl >', '1970-01-01')
            ->where('k.jatuh_tempo_tgl <=', $cutoffDate)
            ->whereIn('p.id_proyek', $projectIds);
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function mapRow(array $row, DateTimeImmutable $todayDate, bool $isOverdue): array
    {
        $dueDateStr = (string) ($row['jatuh_tempo_tgl'] ?? '');
        $dueDate = DateTimeImmutable::createFromFormat('Y-m-d', $dueDateStr) ?: $todayDate;
        $dueDate = $dueDate->setTime(0, 0, 0);
        $diffDays = (int) $todayDate->diff($dueDate)->days;

        $mapped = [
            'id_keuangan'     => (int) ($row['id_keuangan'] ?? 0),
            'id_proyek'       => (int) ($row['id_proyek'] ?? 0),
            'nama_proyek'     => (string) ($row['nama_proyek'] ?? ''),
            'nama_cluster'    => (string) ($row['nama_cluster'] ?? ''),
            'nama_jalan'      => (string) ($row['nama_jalan'] ?? ''),
            'no_kavling'      => (string) ($row['no_kavling'] ?? ''),
            'nama_konsumen'   => (string) ($row['nama_konsumen'] ?? ''),
            'berita_acara'    => (string) ($row['berita_acara'] ?? ''),
            'jatuh_tempo_tgl' => $dueDateStr,
            'nominal'         => (float) ($row['nominal'] ?? 0.0),
        ];

        if ($isOverdue) {
            $mapped['hari_terlambat'] = $diffDays;
        } else {
            $mapped['hari_menuju_jatuh_tempo'] = $diffDays;
        }

        return $mapped;
    }
}

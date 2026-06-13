<?php

namespace App\Services;

use App\Repositories\MkdtHistoryRepository;

class MkdtHistoryService
{
    public const ACTION_SET_HARGA_JUAL        = 'set_harga_jual';
    public const ACTION_ISI_DATA_KONSUMEN     = 'isi_data_konsumen';
    public const ACTION_UBAH_STATUS_KAVLING   = 'ubah_status_kavling';
    public const ACTION_TURUN_PEMBANGUNAN     = 'turun_pembangunan';
    public const ACTION_STANDING_INSTRUCTION  = 'standing_instruction';
    public const ACTION_BATAL_BOOKING         = 'batal_booking';

    private const ACTION_LABELS = [
        self::ACTION_SET_HARGA_JUAL       => 'Set Harga Jual',
        self::ACTION_ISI_DATA_KONSUMEN    => 'Isi Data Konsumen',
        self::ACTION_UBAH_STATUS_KAVLING  => 'Ubah Status Kavling',
        self::ACTION_TURUN_PEMBANGUNAN    => 'Turun Pembangunan',
        self::ACTION_STANDING_INSTRUCTION => 'Standing Instruction',
        self::ACTION_BATAL_BOOKING        => 'Batal Booking',
    ];

    public function __construct(
        private readonly MkdtHistoryRepository $repo = new MkdtHistoryRepository()
    ) {}

    public function actionLabel(string $action): string
    {
        return self::ACTION_LABELS[$action] ?? $action;
    }

    public function log(
        int $idKavling,
        ?int $idMkdt,
        string $action,
        string $summary,
        ?array $oldData = null,
        ?array $newData = null,
        ?int $actorId = null
    ): bool {
        if (!$this->repo->hasTable() || $summary === '') {
            return false;
        }

        return $this->repo->insert([
            'id_kavling' => $idKavling,
            'id_mkdt'    => $idMkdt,
            'action'     => $action,
            'summary'    => $summary,
            'old_data'   => $oldData !== null ? json_encode($oldData, JSON_UNESCAPED_UNICODE) : null,
            'new_data'   => $newData !== null ? json_encode($newData, JSON_UNESCAPED_UNICODE) : null,
            'add_by'     => $actorId ?? (function_exists('user_id') ? user_id() : null),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function getHistory(int $idKavling, int $limit = 10, int $offset = 0): array
    {
        $limit = max(1, min(50, $limit));
        $offset = max(0, $offset);

        $total = $this->repo->countByKavling($idKavling);
        $rows = $this->repo->getByKavling($idKavling, $limit, $offset);

        foreach ($rows as $row) {
            $row->action_label = $this->actionLabel((string) $row->action);
            $row->old_data = json_decode($row->old_data ?? '{}', true) ?: [];
            $row->new_data = json_decode($row->new_data ?? '{}', true) ?: [];
        }

        return [
            'history'             => $rows,
            'history_total'       => $total,
            'history_limit'       => $limit,
            'history_offset'      => $offset,
            'history_next_offset' => $offset + count($rows),
            'history_has_more'    => ($offset + count($rows)) < $total,
        ];
    }

    public function buildSetHargaSummary(?array $old, array $new, ?string $pricelistLabel = null): string
    {
        $oldId = $old['harga_akhir'] ?? '-';
        $newId = $new['harga_akhir'] ?? '-';
        $label = $pricelistLabel ? " ({$pricelistLabel})" : '';

        return "Harga jual diubah dari ID pricelist {$oldId} ke {$newId}{$label}";
    }

    public function buildKonsumenSummary(?object $oldMkdt, array $kons, array $mk, bool $isNew): string
    {
        if ($isNew) {
            return 'Booking baru atas nama ' . ($kons['nama_konsumen'] ?? '-') .
                ' dengan status ' . ($mk['status_mkdt'] ?? '-');
        }

        $parts = ['Perubahan data konsumen: ' . ($kons['nama_konsumen'] ?? '-')];
        $fields = [
            'status_mkdt'    => 'Status',
            'harga_jual'     => 'Harga jual',
            'harga_kpr'      => 'KPR',
            'booking_fee'    => 'Booking fee',
            'booking_tgl'    => 'Tgl booking',
            'nama_konsumen'  => 'Nama',
            'nik'            => 'NIK',
            'hp_konsumen'    => 'HP',
            'sales'          => 'Sales',
        ];

        foreach ($fields as $key => $label) {
            $source = array_key_exists($key, $mk) ? $mk : $kons;
            $oldVal = $oldMkdt ? ($oldMkdt->$key ?? null) : null;
            $newVal = $source[$key] ?? null;
            if ($this->normalized($oldVal) !== $this->normalized($newVal)) {
                $parts[] = "{$label}: {$this->displayValue($oldVal)} → {$this->displayValue($newVal)}";
            }
        }

        return count($parts) > 1 ? implode('; ', $parts) : ($parts[0] . ' (tanpa perubahan field utama)');
    }

    public function buildStatusSummary(?object $old, array $new, array $perintahBangun = []): string
    {
        $parts = ['Perubahan status kavling'];
        $fields = [
            'status_mkdt'   => 'Status booking',
            'wawancara'     => 'Wawancara',
            'wawancara_tgl' => 'Tgl wawancara',
            'sp3k'          => 'SP3K',
            'sp3k_no'       => 'No SP3K',
            'sp3k_tgl'      => 'Tgl SP3K',
            'sp3k_tgl_exp'  => 'Exp SP3K',
            'akad'          => 'Akad',
            'akad_tgl'      => 'Tgl akad',
            'harga_kpr_acc' => 'KPR disetujui',
            'harga_penambahan_um' => 'Turun KPR',
            'keterangan'    => 'Keterangan',
            'bank'          => 'Bank',
        ];

        foreach ($fields as $key => $label) {
            $oldVal = $old ? ($old->$key ?? null) : null;
            $newVal = $new[$key] ?? null;
            if ($this->normalized($oldVal) !== $this->normalized($newVal)) {
                $parts[] = "{$label}: {$this->displayValue($oldVal)} → {$this->displayValue($newVal)}";
            }
        }

        if (!empty($perintahBangun['perintah_bangun'])) {
            $oldPb = $old ? (int) ($old->perintah_bangun ?? 0) : 0;
            if ($oldPb !== 1) {
                $parts[] = 'Perintah bangun: tidak → ya';
            }
            if (!empty($perintahBangun['perintah_bangun_tgl'])) {
                $parts[] = 'Tgl perintah bangun: ' . $perintahBangun['perintah_bangun_tgl'];
            }
        }

        return count($parts) > 1 ? implode('; ', $parts) : 'Perubahan status kavling (tanpa perubahan field utama)';
    }

    public function buildTurunPembangunanSummary(?array $old, array $new): string
    {
        $tgl = $new['perintah_bangun_tgl'] ?? '-';
        $oleh = $new['perintah_bangun_oleh'] ?? null;

        if (!empty($old['perintah_bangun'])) {
            return "Turun pembangunan diperbarui; tanggal {$tgl}";
        }

        return "Turun pembangunan diterbitkan pada tanggal {$tgl}";
    }

    public function buildStandingInstructionSummary(array $items): string
    {
        $labels = array_map(static fn ($item) => ($item['nama'] ?? 'SI') . ' (' . ($item['tanggal_si'] ?? '-') . ')', $items);

        return 'Standing instruction disimpan: ' . implode(', ', $labels);
    }

    public function buildBatalSummary(array $data): string
    {
        $ket = trim((string) ($data['keterangan_batal'] ?? ''));
        $refund = !empty($data['perlu_refund']) ? 'Perlu refund' : 'Tidak perlu refund';

        return 'Booking dibatalkan. ' . $refund . ($ket !== '' ? '; Keterangan: ' . $ket : '');
    }

    private function normalized(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        return trim((string) $value);
    }

    private function displayValue(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        if (is_bool($value) || $value === '0' || $value === '1' || $value === 0 || $value === 1) {
            return ((int) $value) === 1 ? 'Ya' : 'Tidak';
        }

        return (string) $value;
    }
}

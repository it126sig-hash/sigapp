<?php

namespace App\Services;

use App\Repositories\BookingPaymentRepository;
use CodeIgniter\Database\BaseConnection;

/**
 * Single source of truth for MKDT contract targets and Finance allocations.
 */
class MkdtFinancialBreakdownService
{
    public const TARGET_FIELDS = [
        'harga_uang_muka',
        'harga_diskon_uang_muka',
        'harga_sbum',
        'harga_administrasi',
        'harga_bphtb',
        'harga_biaya_proses',
        'harga_ppn',
        'harga_penambahan',
        'harga_penambahan_tanah',
        'harga_penambahan_um',
        'harga_kpr',
        'harga_kpr_acc',
        'harga_allin',
        'is_allin',
    ];

    public function __construct(private readonly ?BaseConnection $db = null)
    {
    }

    public function targets(array|object $mkdt): array
    {
        $row = (array) $mkdt;
        $um = max(0.0, $this->number($row, 'harga_uang_muka')
            - $this->number($row, 'harga_diskon_uang_muka')
            - $this->number($row, 'harga_sbum'));
        $adm = max(0.0, $this->number($row, 'harga_administrasi'));
        $turunKpr = $this->validTurunKpr($row);
        $components = [
            'Uang Muka' => $um,
            'Biaya Administrasi' => $adm,
            'BPHTB' => max(0.0, $this->number($row, 'harga_bphtb')),
            'Biaya Proses' => max(0.0, $this->number($row, 'harga_biaya_proses')),
            'PPN' => max(0.0, $this->number($row, 'harga_ppn')),
            'Kavling Strategis' => max(0.0, $this->number($row, 'harga_penambahan')),
            'Kelebihan Tanah' => max(0.0, $this->number($row, 'harga_penambahan_tanah')),
            'Turun KPR' => $turunKpr,
        ];

        return [
            'um' => $um,
            'adm' => $adm,
            'bb' => array_sum(array_slice($components, 2)),
            'total' => array_sum($components),
            'components' => $components,
        ];
    }

    public function validTurunKpr(array|object $mkdt): float
    {
        $row = (array) $mkdt;
        $acc = $this->number($row, 'harga_kpr_acc');
        return $acc > 0 ? max($this->number($row, 'harga_kpr') - $acc, 0.0) : 0.0;
    }

    /** @return array{label:string,status:string,percentage:?int} */
    public function progress(float $target, float $paid): array
    {
        if ($target <= 0 && $paid > 0) {
            return ['label' => 'Perlu Rekonsiliasi', 'status' => 'reconcile', 'percentage' => null];
        }
        if ($target <= 0) {
            return ['label' => '0%', 'status' => 'normal', 'percentage' => 0];
        }

        $percentage = (int) round(($paid / $target) * 100);
        return [
            'label' => $percentage . '%',
            'status' => $percentage > 100 ? 'overpaid' : 'normal',
            'percentage' => $percentage,
        ];
    }

    public function hasFinancialChanges(?object $old, array $new): bool
    {
        if ($old === null) {
            return true;
        }
        foreach (self::TARGET_FIELDS as $field) {
            if (!array_key_exists($field, $new)) {
                continue;
            }
            if (round((float) ($old->{$field} ?? 0), 2) !== round((float) $new[$field], 2)) {
                return true;
            }
        }
        return false;
    }

    public function validateContractAndSchedule(array $mkdt, array $schedules): void
    {
        $targets = $this->targets($mkdt);
        $isAllIn = (int) ($mkdt['is_allin'] ?? 0) === 1;
        $expected = $isAllIn
            ? max(0, (float) ($mkdt['harga_allin'] ?? 0))
            : $targets['total'];

        $scheduleTotal = 0.0;
        $nominals = isset($schedules['nominal']) && is_array($schedules['nominal'])
            ? $schedules['nominal']
            : array_map(static fn (array $row) => $row['nominal'] ?? 0, $schedules);
        foreach ($nominals as $nominal) {
            $scheduleTotal += max(0, (float) str_replace(',', '', (string) $nominal));
        }
        if (abs($scheduleTotal - $expected) > 0.01) {
            throw new \DomainException($isAllIn
                ? 'Total tagihan harus sama dengan harga all in.'
                : 'Total tagihan harus sama dengan total komponen efektif MKDT.');
        }
    }

    public function validatePaymentAllocation(int $idMkdt, array $allocations, float $headerNominal): void
    {
        $db = $this->requireDb();
        if ($headerNominal <= 0 || $allocations === []) {
            throw new \DomainException('Nominal pembayaran dan breakdown wajib lebih dari 0.');
        }

        $sum = array_sum(array_map(static fn (array $row): float => (float) ($row['nominal'] ?? 0), $allocations));
        if (abs($sum - $headerNominal) > 0.01) {
            throw new \DomainException('Jumlah breakdown harus sama dengan nominal pembayaran.');
        }

        $repo = new BookingPaymentRepository($db);
        $mkdt = $repo->mkdt($idMkdt, true);
        $targets = $this->targets($mkdt);
        $installment = $repo->installmentSummary($idMkdt);
        if ($headerNominal - (float) $installment['sisa_tagihan'] > 0.01) {
            throw new \DomainException('Nominal pembayaran melebihi sisa tagihan.');
        }

        $masterRows = $db->table('keuangan_item_list')
            ->where('deleted_at', null)->get()->getResultArray();
        $masters = [];
        foreach ($masterRows as $row) {
            $masters[(int) $row['id_keuangan_item_list']] = $row;
        }

        $paid = $this->paidByEffectiveItem($idMkdt, $masters);
        $incoming = [];
        foreach ($allocations as $allocation) {
            $id = (int) ($allocation['id'] ?? 0);
            $nominal = (float) ($allocation['nominal'] ?? 0);
            if ($nominal <= 0 || !isset($masters[$id])) {
                throw new \DomainException('Breakdown pembayaran memuat item yang tidak dikenal atau nominal tidak valid.');
            }
            $key = $this->effectiveTargetKey($masters[$id]);
            if ($key === null) {
                throw new \DomainException('Target MKDT untuk item pembayaran tidak dikenal. Perlu rekonsiliasi.');
            }
            $incoming[$key] = ($incoming[$key] ?? 0) + $nominal;
        }

        foreach ($incoming as $key => $nominal) {
            $target = (float) ($targets['components'][$key] ?? 0);
            $remaining = $target - (float) ($paid[$key] ?? 0);
            if ($target <= 0) {
                throw new \DomainException("Target MKDT untuk {$key} belum tersedia. Lakukan rekonsiliasi terlebih dahulu.");
            }
            if ($nominal - $remaining > 0.01) {
                throw new \DomainException("Pembayaran {$key} melebihi sisa target MKDT.");
            }
        }
    }

    private function paidByEffectiveItem(int $idMkdt, array $masters): array
    {
        $db = $this->requireDb();
        $rows = $db->table('log_pembayaran_detail lpd')
            ->select('lpd.id_keuangan_item_list, lpd.booking_is_installment, SUM(lpd.nominal) AS nominal', false)
            ->join('log_pembayaran lp', 'lp.id_pembayaran = lpd.id_pembayaran')
            ->where('lp.id_mkdt', $idMkdt)->where('lp.is_deleted', 0)
            ->where("LOWER(REPLACE(TRIM(COALESCE(lp.payment_type,'')), ';', '')) != 'refund'", null, false)
            ->groupBy('lpd.id_keuangan_item_list, lpd.booking_is_installment')
            ->get()->getResultArray();

        $paid = [];
        foreach ($rows as $row) {
            $id = (int) $row['id_keuangan_item_list'];
            if (!isset($masters[$id])) {
                continue;
            }
            if (($masters[$id]['kategori'] ?? '') === 'BO' && (int) $row['booking_is_installment'] === 0) {
                continue;
            }
            $key = (($masters[$id]['kategori'] ?? '') === 'BO')
                ? 'Uang Muka'
                : $this->effectiveTargetKey($masters[$id]);
            if ($key !== null) {
                $paid[$key] = ($paid[$key] ?? 0) + (float) $row['nominal'];
            }
        }
        return $paid;
    }

    private function effectiveTargetKey(array $master): ?string
    {
        $category = strtoupper(trim((string) ($master['kategori'] ?? '')));
        if ($category === 'BO' || $category === 'UM') {
            return 'Uang Muka';
        }
        if ($category === 'ADM') {
            return 'Biaya Administrasi';
        }
        if ($category !== 'BB') {
            return null;
        }

        $normalized = strtolower(preg_replace('/[^a-z0-9]+/i', ' ', (string) ($master['item'] ?? '')));
        foreach ([
            'bphtb' => 'BPHTB',
            'proses' => 'Biaya Proses',
            'ppn' => 'PPN',
            'strategis' => 'Kavling Strategis',
            'kelebihan' => 'Kelebihan Tanah',
            'turun kpr' => 'Turun KPR',
        ] as $needle => $key) {
            if (str_contains($normalized, $needle)) {
                return $key;
            }
        }
        return null;
    }

    private function number(array $row, string $field): float
    {
        return (float) ($row[$field] ?? 0);
    }

    private function requireDb(): BaseConnection
    {
        if ($this->db === null) {
            throw new \LogicException('Database connection diperlukan untuk validasi pembayaran.');
        }
        return $this->db;
    }
}

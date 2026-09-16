<?php

namespace App\Services;

use App\Repositories\BookingPaymentRepository;
use CodeIgniter\Database\BaseConnection;

/** One-time, rerunnable conversion of historical booking receipts. */
class BookingFeeMigrationService
{
    private BookingPaymentRepository $repo;
    private BookingPaymentService $booking;
    private FinanceLedgerService $ledger;

    private const REPLACED = [145=>160,216=>658,253=>614,266=>623,268=>665,269=>675,284=>629,285=>670,294=>251,300=>377,303=>343];
    private const SHARED = [[71,72],[74,75],[318,319]];

    public function __construct(private BaseConnection $db)
    {
        $this->repo = new BookingPaymentRepository($db);
        $this->booking = new BookingPaymentService($db);
        $this->ledger = new FinanceLedgerService($db);
    }

    public function run(): array
    {
        $before = $this->snapshot();
        $this->db->transException(true)->transBegin();
        try {
            $this->correctAllocation(987, 'BB', 'Biaya Proses', 202);
            $this->correctAllocation(863, 'UM', null, 279);
            $this->splitPayment1001();
            $this->seedExistingLinks();
            $this->linkApprovedReplacements();

            $rows = $this->sourceMkdtQuery()->select('id_mkdt, add_by')->orderBy('id_mkdt')->get()->getResultArray();
            foreach ($rows as $row) {
                $id = (int) $row['id_mkdt'];
                $oldPayment = $this->repo->link($id)['id_pembayaran'] ?? null;
                $result = $this->booking->synchronize($id, isset($row['add_by']) ? (int) $row['add_by'] : null);
                $newPayment = $result['id_pembayaran'] ?? null;
                if (! $oldPayment && $newPayment) {
                    $extra = [];
                    if (isset(self::REPLACED[$id])) $extra['replaces_payment_ids'] = (string) self::REPLACED[$id];
                    if ($extra) $this->repo->saveLink($id, (int) $newPayment, $extra);
                    $this->audit('booking-created-' . $id, $id, (int) $newPayment,
                        isset(self::REPLACED[$id]) ? 'create_deleted_replacement' : 'create_missing_booking',
                        ['replaces_payment_id' => self::REPLACED[$id] ?? null], $result);
                }
            }

            // Summaries derive only from active allocations after all moves/splits.
            foreach ($rows as $row) $this->repo->recalculate((int) $row['id_mkdt']);
            if ($this->db->transStatus() === false) throw new \RuntimeException('Migrasi booking gagal.');
            $this->db->transCommit();
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }

        return ['before' => $before, 'after' => $this->snapshot(), 'anomalies' => $this->anomalies()];
    }

    public function snapshot(): array
    {
        $rows = $this->db->table('mkdt m')
            ->select("m.id_mkdt, COALESCE(c.nama_konsumen,'-') nama_konsumen, m.booking_fee, m.booking_tgl, m.status_mkdt, m.is_batal, m.is_lunas, mbp.id_pembayaran canonical_payment, mbp.verified_at", false)
            ->join('konsumen c', 'c.id_konsumen=m.id_konsumen', 'left')
            ->join('mkdt_booking_payment mbp', 'mbp.id_mkdt=m.id_mkdt', 'left')
            ->groupStart()->where('m.booking_fee >', 0)->orWhereIn('m.id_mkdt', $this->promoIds())->groupEnd()
            ->orderBy('m.id_mkdt')->get()->getResultArray();
        foreach ($rows as &$row) {
            $id = (int) $row['id_mkdt'];
            $payments = $this->db->table('log_pembayaran lp')
                ->select('lp.id_pembayaran,lp.nominal,lp.tanggal_bayar,lp.payment_type,lp.is_deleted,fl.id ledger_id,fl.nominal ledger_nominal,fl.tanggal_transaksi ledger_date,fl.status ledger_status')
                ->join('finance_ledger fl', "fl.source_type='log_pembayaran' AND fl.source_id=lp.id_pembayaran", 'left')
                ->where('lp.id_mkdt', $id)->orderBy('lp.id_pembayaran')->get()->getResultArray();
            foreach ($payments as &$payment) {
                $payment['allocations'] = $this->repo->details((int) $payment['id_pembayaran']);
            }
            $row['payments'] = $payments;
            $row['installment'] = $this->repo->installmentSummary($id);
        }
        unset($row, $payment);
        return $rows;
    }

    private function seedExistingLinks(): void
    {
        $known = [163=>88,170=>116,199=>297,202=>154,279=>576,356=>1002];
        $rows = $this->db->table('mkdt')->select('id_mkdt,booking_fee,booking_tgl')->where('booking_fee >', 0)->orderBy('id_mkdt')->get()->getResultArray();
        foreach ($rows as $mkdt) {
            $id = (int) $mkdt['id_mkdt'];
            if ($this->repo->link($id)) continue;
            $candidate = isset($known[$id]) ? $this->repo->payment($known[$id]) : null;
            if (! $candidate || (int) ($candidate['is_deleted'] ?? 1) === 1) {
                $candidates = $this->repo->bookingRows($id);
                usort($candidates, function ($a, $b) use ($mkdt) {
                    $sa = ((float)$a['nominal'] === (float)$mkdt['booking_fee'] ? 2 : 0) + (substr((string)$a['tanggal_bayar'],0,10) === substr((string)$mkdt['booking_tgl'],0,10) ? 1 : 0);
                    $sb = ((float)$b['nominal'] === (float)$mkdt['booking_fee'] ? 2 : 0) + (substr((string)$b['tanggal_bayar'],0,10) === substr((string)$mkdt['booking_tgl'],0,10) ? 1 : 0);
                    return $sb <=> $sa;
                });
                $candidate = $candidates[0] ?? null;
            }
            if ($candidate) {
                $paymentId = (int) $candidate['id_pembayaran'];
                if (! $this->repo->owners($paymentId)) $this->repo->saveLink($id, $paymentId);
            }
        }
    }

    private function linkApprovedReplacements(): void
    {
        foreach (self::SHARED as [$oldId, $currentId]) {
            $old = $this->repo->link($oldId);
            $current = $this->repo->link($currentId);
            $paymentId = (int) ($current['id_pembayaran'] ?? $old['id_pembayaran'] ?? 0);
            if (! $paymentId) {
                $paymentId = (int) $this->booking->synchronize($currentId)['id_pembayaran'];
            }
            $this->repo->saveLink($currentId, $paymentId);
            $this->repo->saveLink($oldId, $paymentId);
            $this->audit("shared-$oldId-$currentId", $currentId, $paymentId, 'link_replacement_history', null, ['owners'=>[$oldId,$currentId]]);
        }
    }

    private function splitPayment1001(): void
    {
        $payment = $this->repo->payment(1001);
        if (! $payment) throw new \RuntimeException('Pembayaran 1001 tidak ditemukan.');
        $bo = array_values(array_filter($this->repo->details(1001), static fn($d) => ($d['kategori'] ?? '') === 'BO'));
        if (! $bo) return; // Already split on an earlier run.
        $before = ['payment'=>$payment,'details'=>$this->repo->details(1001)];
        $this->repo->write('log_pembayaran', [
            'nominal'=>1000000,'tanggal_bayar'=>'2026-09-01','payment_type'=>'Uang Muka',
            'keterangan'=>'Uang Muka - hasil pemisahan pembayaran 1001','updated_at'=>date('Y-m-d H:i:s')
        ], ['id_pembayaran'=>1001]);
        $newId = $this->repo->write('log_pembayaran', [
            'id_mkdt'=>404,'id_keuangan'=>'','nominal'=>1500000,'tanggal_bayar'=>'2025-07-09',
            'payment_type'=>'Booking','keterangan'=>'Booking fee - dipisahkan dari pembayaran 1001',
            'is_deleted'=>0,'st'=>1,'add_by'=>$payment['add_by'],'edit_by'=>$payment['edit_by'],
            'created_at'=>$payment['created_at'] ?: date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')
        ]);
        foreach ($bo as $detail) {
            $this->repo->write('log_pembayaran_detail', ['id_pembayaran'=>$newId,'nominal'=>1500000,'booking_is_installment'=>0,'updated_at'=>date('Y-m-d H:i:s')],
                ['id_pembayaran_detail'=>(int)$detail['id_pembayaran_detail']]);
        }
        $this->repo->saveLink(404, $newId, ['replaces_payment_ids'=>'1001']);
        $this->ledger->recordIncomeFromLogPembayaran(1001);
        $this->ledger->recordIncomeFromLogPembayaran($newId);
        $this->audit('split-1001', 404, $newId, 'split_booking_from_installment', $before,
            ['original'=>['id'=>1001,'nominal'=>1000000,'date'=>'2026-09-01'],'booking'=>['id'=>$newId,'nominal'=>1500000,'date'=>'2025-07-09']]);
    }

    private function correctAllocation(int $paymentId, string $category, ?string $name, int $idMkdt): void
    {
        $details = array_values(array_filter($this->repo->details($paymentId), static fn($d) => ($d['kategori'] ?? '') === 'BO'));
        if (! $details) return;
        $item = $this->repo->item($category, $name);
        foreach ($details as $detail) {
            $before = $detail;
            $this->repo->write('log_pembayaran_detail', [
                'id_keuangan_item_list'=>(int)$item['id_keuangan_item_list'], 'booking_is_installment'=>0,
                'updated_at'=>date('Y-m-d H:i:s')
            ], ['id_pembayaran_detail'=>(int)$detail['id_pembayaran_detail']]);
            $this->audit("allocation-$paymentId-{$detail['id_pembayaran_detail']}", $idMkdt, $paymentId,
                'move_allocation_without_cash_change', $before, ['category'=>$category,'item'=>$item['item']]);
        }
    }

    private function audit(string $key, ?int $idMkdt, ?int $paymentId, string $action, mixed $before, mixed $after): void
    {
        if ($this->db->table('booking_fee_migration_log')->where('action_key',$key)->countAllResults()) return;
        $this->repo->write('booking_fee_migration_log', [
            'action_key'=>$key,'id_mkdt'=>$idMkdt,'id_pembayaran'=>$paymentId,'action'=>$action,
            'before_json'=>json_encode($before, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
            'after_json'=>json_encode($after, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),'created_at'=>date('Y-m-d H:i:s')
        ]);
    }

    private function anomalies(): array
    {
        $result = [
            ['id_mkdt'=>63,'type'=>'replacement_unclear','note'=>'63→304 berbeda nominal Rp1.000.000 vs Rp1; tidak ditautkan.'],
            ['id_mkdt'=>142,'type'=>'identity_unlinked','note'=>'Konsumen dan kavling tidak terhubung; booking tetap mengikuti MKDT.'],
            ['id_mkdt'=>293,'type'=>'replacement_unclear','note'=>'Pasangan pengganti belum cukup jelas; tidak ditautkan otomatis.'],
        ];
        $cancelled = [64,80,87,100,160,165,220,246,306,329,351,363,374,418];
        foreach ($cancelled as $id) $result[] = ['id_mkdt'=>$id,'type'=>'cancelled','note'=>'Booking dicatat tanpa mengubah status batal/refund.'];
        foreach ($this->db->table('mkdt m')->select('m.id_mkdt')->join('keuangan k','k.id_mkdt=m.id_mkdt AND k.is_void=0','left')
            ->where('m.is_lunas',1)->groupBy('m.id_mkdt')->get()->getResultArray() as $row) {
            $summary = $this->repo->installmentSummary((int)$row['id_mkdt']);
            if ($summary['perlu_rekonsiliasi']) $result[] = ['id_mkdt'=>(int)$row['id_mkdt'],'type'=>'paid_flag_mismatch','note'=>'Flag lunas dipertahankan; sisa angsuran perlu rekonsiliasi.'];
        }
        return $result;
    }

    private function sourceMkdtQuery()
    {
        return $this->db->table('mkdt')->groupStart()->where('booking_fee >', 0)->orWhereIn('id_mkdt', $this->promoIds())->groupEnd();
    }

    private function promoIds(): array
    {
        return [247,248,250,252,262,263,275,276,277,278,280,281,282,287];
    }
}

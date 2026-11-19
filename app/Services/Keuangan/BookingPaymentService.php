<?php

namespace App\Services\Keuangan;

use App\Repositories\AuditTrail\HistoryRepository;
use App\Repositories\Keuangan\BookingPaymentRepository;
use App\Services\AuditTrail\HistoryService;
use App\Services\Mkdt\MkdtSettlementService;
use CodeIgniter\Database\BaseConnection;

/**
 * Owns the one cash receipt that represents the booking declared by MKDT.
 * Verification may correct MKDT and synchronize its canonical receipt, but it
 * never represents a second cash receipt.
 */
class BookingPaymentService
{
    private BookingPaymentRepository $repo;
    private FinanceLedgerService $ledger;
    private MkdtSettlementService $settlement;
    private HistoryService $history;

    public function __construct(private ?BaseConnection $db = null)
    {
        $this->db ??= \Config\Database::connect();
        $this->repo = new BookingPaymentRepository($this->db);
        $this->ledger = new FinanceLedgerService($this->db);
        $this->settlement = new MkdtSettlementService($this->db);
        $this->history = new HistoryService(new HistoryRepository($this->db));
    }

    public function findProjectIdByMkdt(int $idMkdt): ?int
    {
        return $this->repo->findProjectIdByMkdt($idMkdt);
    }

    public function assertEditable(int $idMkdt, float $nominal, ?string $tanggal): void
    {
        $mkdt = $this->repo->mkdt($idMkdt, true);
        $link = $this->repo->link($idMkdt, true);
        if (empty($link['verified_at'])) return;

        $oldNominal = $this->money($mkdt['booking_fee'] ?? 0);
        $oldTanggal = $this->date($mkdt['booking_tgl'] ?? null);
        if ($oldNominal !== $this->money($nominal) || $oldTanggal !== $this->date($tanggal)) {
            throw new \DomainException('Booking fee sudah diverifikasi Keuangan. Nominal dan tanggal booking tidak dapat diubah.');
        }
    }

    /** Synchronize the cash record after MKDT has been saved, inside the caller transaction. */
    public function synchronize(int $idMkdt, ?int $actorId = null): array
    {
        $mkdt = $this->repo->mkdt($idMkdt, true);
        $nominal = $this->money($mkdt['booking_fee'] ?? 0);
        $tanggal = $this->date($mkdt['booking_tgl'] ?? null);
        $link = $this->repo->link($idMkdt, true);

        if (! empty($link['verified_at'])) {
            $this->assertCanonicalMatches($mkdt, $link);
            return $this->getBooking($idMkdt);
        }

        if ($nominal <= 0) {
            // BO recorded by Finance for an MKDT promo remains an installment/UM allocation.
            foreach ($this->repo->bookingRows($idMkdt) as $row) {
                foreach ($this->repo->details((int) $row['id_pembayaran']) as $detail) {
                    if (($detail['kategori'] ?? '') === 'BO' && (int) ($detail['booking_is_installment'] ?? 0) !== 1) {
                        $this->repo->write('log_pembayaran_detail', [
                            'booking_is_installment' => 1,
                            'updated_at' => date('Y-m-d H:i:s'),
                            'edit_by' => $actorId,
                        ], ['id_pembayaran_detail' => (int) $detail['id_pembayaran_detail']]);
                    }
                }
            }
            $this->repo->saveLink($idMkdt, null);
            $this->repo->recalculate($idMkdt);
            $this->settlement->synchronize($idMkdt);
            return $this->getBooking($idMkdt);
        }

        if (! $tanggal) throw new \DomainException('Tanggal booking wajib diisi untuk booking fee lebih dari Rp0.');
        $paymentId = (int) ($link['id_pembayaran'] ?? 0);
        $payment = $paymentId ? $this->repo->payment($paymentId) : null;
        if ($payment && (int) ($payment['is_deleted'] ?? 0) === 1) $payment = null;
        $changed = false;

        if (! $payment) {
            $paymentId = $this->repo->write('log_pembayaran', [
                'id_mkdt' => $idMkdt,
                'id_keuangan' => '',
                'nominal' => $nominal,
                'tanggal_bayar' => $tanggal,
                'payment_type' => 'Booking',
                'keterangan' => 'Booking fee otomatis dari MKDT',
                'is_deleted' => 0,
                'st' => 1,
                'add_by' => $actorId,
                'edit_by' => $actorId,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            $item = $this->repo->item('BO');
            $this->repo->write('log_pembayaran_detail', [
                'id_pembayaran' => $paymentId,
                'id_keuangan_item_list' => (int) $item['id_keuangan_item_list'],
                'nominal' => $nominal,
                'booking_is_installment' => 0,
                'add_by' => $actorId,
                'edit_by' => $actorId,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            $this->repo->saveLink($idMkdt, $paymentId);
            $changed = true;
        } else {
            $owners = $this->repo->owners($paymentId);
            foreach ($owners as $owner) {
                if ((int) $owner['id_mkdt'] === $idMkdt) continue;
                $other = $this->repo->mkdt((int) $owner['id_mkdt'], true);
                if ($this->money($other['booking_fee'] ?? 0) !== $nominal || $this->date($other['booking_tgl'] ?? null) !== $tanggal) {
                    throw new \DomainException('Pembayaran booking dipakai riwayat MKDT lain dengan nominal atau tanggal berbeda.');
                }
                if (! empty($owner['verified_at'])) {
                    throw new \DomainException('Pembayaran booking bersama sudah diverifikasi dan tidak dapat diubah.');
                }
            }
            if ($this->money($payment['nominal']) !== $nominal || $this->date($payment['tanggal_bayar']) !== $tanggal
                || strtolower(trim((string)$payment['payment_type'], "; \t\n\r\0\x0B")) !== 'booking'
                || (string)($payment['id_keuangan'] ?? '') !== '') {
                $this->repo->write('log_pembayaran', [
                    'id_keuangan' => '', 'nominal' => $nominal, 'tanggal_bayar' => $tanggal, 'payment_type' => 'Booking',
                    'keterangan' => 'Booking fee otomatis dari MKDT', 'edit_by' => $actorId,
                    'updated_at' => date('Y-m-d H:i:s'),
                ], ['id_pembayaran' => $paymentId]);
                $changed = true;
            }
            $details = $this->repo->details($paymentId);
            $bookingDetails = array_values(array_filter($details, static fn ($d) => ($d['kategori'] ?? '') === 'BO'));
            if (count($bookingDetails) !== 1 || count($details) !== 1) {
                throw new \DomainException(
                    "Pembayaran booking MKDT {$idMkdt} (ID pembayaran {$paymentId}) memiliki "
                    . count($details) . ' detail dan ' . count($bookingDetails)
                    . ' detail BO; pembayaran harus direkonsiliasi.'
                );
            }
            if ($this->money($bookingDetails[0]['nominal']) !== $nominal || (int)($bookingDetails[0]['booking_is_installment'] ?? 0) !== 0) {
                $this->repo->write('log_pembayaran_detail', [
                    'nominal' => $nominal, 'booking_is_installment' => 0,
                    'edit_by' => $actorId, 'updated_at' => date('Y-m-d H:i:s'),
                ], ['id_pembayaran_detail' => (int) $bookingDetails[0]['id_pembayaran_detail']]);
                $changed = true;
            }
        }

        $ledger = $this->db->table('finance_ledger')->where('source_type', FinanceLedgerService::SOURCE_LOG_PEMBAYARAN)
            ->where('source_id', $paymentId)->get()->getRowArray();
        if ($changed || ! $ledger || (int)($ledger['is_deleted'] ?? 0) === 1
            || $this->money($ledger['nominal'] ?? 0) !== $nominal || $this->date($ledger['tanggal_transaksi'] ?? null) !== $tanggal) {
            $this->ledger->recordIncomeFromLogPembayaran($paymentId, $actorId);
        }
        $this->repo->recalculate($idMkdt);
        $this->settlement->synchronize($idMkdt);
        return $this->getBooking($idMkdt);
    }

    public function verify(int $idMkdt, int $actorId, ?float $nominal = null, ?string $tanggal = null): array
    {
        $this->db->transException(true)->transBegin();
        try {
            $mkdt = $this->repo->mkdt($idMkdt, true);
            $link = $this->repo->link($idMkdt, true);
            if (! $link) throw new \DomainException('Status booking belum dibentuk. Simpan ulang data MKDT terlebih dahulu.');
            if (! empty($link['verified_at'])) {
                if (($nominal !== null && $this->money($mkdt['booking_fee'] ?? 0) !== $this->money($nominal))
                    || ($tanggal !== null && $this->date($mkdt['booking_tgl'] ?? null) !== $this->validDate($tanggal))) {
                    throw new \DomainException('Booking fee sudah diverifikasi. Nominal dan tanggal tidak dapat diubah.');
                }
                $this->db->transCommit();
                return $this->getBooking($idMkdt);
            }

            if ($nominal !== null || $tanggal !== null) {
                if ($nominal === null || $nominal < 0) throw new \DomainException('Nominal booking tidak valid.');
                $tanggal = $this->validDate($tanggal);
                if (! $tanggal) throw new \DomainException('Tanggal booking tidak valid.');

                $nominal = $this->money($nominal);
                if ($this->money($mkdt['booking_fee'] ?? 0) !== $nominal
                    || $this->date($mkdt['booking_tgl'] ?? null) !== $tanggal) {
                    $this->repo->write('mkdt', [
                        'booking_fee' => $nominal,
                        'booking_tgl' => $tanggal,
                    ], ['id_mkdt' => $idMkdt]);
                }

                // Synchronize the canonical receipt, allocation, summary, and ledger
                // before verification locks the corrected source values.
                $this->synchronize($idMkdt, $actorId);
                $mkdt = $this->repo->mkdt($idMkdt, true);
                $link = $this->repo->link($idMkdt, true);
                if (! $link) throw new \DomainException('Status booking gagal disinkronkan.');
            }

            $this->assertCanonicalMatches($mkdt, $link);
            $now = date('Y-m-d H:i:s');
            $this->repo->saveLink($idMkdt, $link['id_pembayaran'] ? (int) $link['id_pembayaran'] : null, [
                'verified_by' => $actorId, 'verified_at' => $now,
            ]);
            $this->db->transCommit();
            return $this->getBooking($idMkdt);
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    public function unverify(int $idMkdt, int $actorId, string $reason): array
    {
        $reason = trim($reason);
        $reasonLength = mb_strlen($reason);
        if ($reasonLength < 3) {
            throw new \DomainException('Alasan pembatalan verifikasi wajib diisi minimal 3 karakter.');
        }
        if ($reasonLength > 500) {
            throw new \DomainException('Alasan pembatalan verifikasi maksimal 500 karakter.');
        }

        $this->db->transException(true)->transBegin();
        try {
            $mkdt = $this->repo->mkdt($idMkdt, true);
            $link = $this->repo->link($idMkdt, true);

            if (! $link || empty($link['verified_at'])) {
                throw new \DomainException('Booking fee belum diverifikasi atau verifikasinya sudah dibatalkan.');
            }
            if ($this->isUnverifyBlocked($mkdt)) {
                throw new \DomainException('Verifikasi booking fee tidak dapat dibatalkan karena transaksi sudah dibatalkan atau refund sudah dibayarkan.');
            }

            $this->repo->clearVerification($idMkdt);

            if ($this->history->hasTable()) {
                $idProyek = $this->repo->findProjectIdByMkdt($idMkdt);
                $idKavling = ! empty($mkdt['id_kavling']) ? (int) $mkdt['id_kavling'] : null;

                $this->history->log('keuangan', [
                    'id_proyek'      => $idProyek,
                    'id_kavling'     => $idKavling,
                    'reference_type' => 'mkdt_booking_payment',
                    'reference_id'   => $idMkdt,
                    'action'         => 'batal_verifikasi_booking',
                    'summary'        => 'Membatalkan verifikasi booking fee Rp ' . number_format($this->money($mkdt['booking_fee'] ?? 0), 0, ',', '.') . ' — Alasan: ' . $reason,
                    'old_data'       => [
                        'verified_by'   => isset($link['verified_by']) ? (int) $link['verified_by'] : null,
                        'verified_at'   => $link['verified_at'],
                        'booking_fee'   => $this->money($mkdt['booking_fee'] ?? 0),
                        'booking_tgl'   => $this->date($mkdt['booking_tgl'] ?? null),
                        'id_pembayaran' => ! empty($link['id_pembayaran']) ? (int) $link['id_pembayaran'] : null,
                    ],
                    'new_data'   => ['verified_by' => null, 'verified_at' => null],
                    'metadata'   => ['id_mkdt' => $idMkdt, 'reason' => $reason],
                    'add_by'     => $actorId,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }

            $this->db->transCommit();
            return $this->getBooking($idMkdt);
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    public function getVerifiedList(int $idProyek, array $filters, bool $canManage): array
    {
        $length = (int) ($filters['length'] ?? $filters['per_page'] ?? 25);
        $length = max(1, min(100, $length));

        if (isset($filters['start'])) {
            $start = max(0, (int) $filters['start']);
        } else {
            $pageInput = max(1, (int) ($filters['page'] ?? 1));
            $start = ($pageInput - 1) * $length;
        }

        $page = (int) floor($start / $length) + 1;

        $repoFilters = $filters;
        $repoFilters['length'] = $length;
        $repoFilters['start'] = $start;

        $result = $this->repo->getVerifiedList($idProyek, $repoFilters);
        $items = [];

        foreach ($result['rows'] as $row) {
            $bookingFee = $this->money($row['booking_fee'] ?? 0);
            $isBlocked = $this->isUnverifyBlocked($row);

            $items[] = [
                'id_mkdt'                 => (int) $row['id_mkdt'],
                'id_kavling'              => ! empty($row['id_kavling']) ? (int) $row['id_kavling'] : null,
                'id_proyek'               => ! empty($row['id_proyek']) ? (int) $row['id_proyek'] : null,
                'nama_proyek'             => (string) ($row['nama_proyek'] ?? ''),
                'nama_konsumen'           => (string) ($row['nama_konsumen'] ?? ''),
                'nama_jalan'              => (string) ($row['nama_jalan'] ?? ''),
                'no_kavling'              => (string) ($row['no_kavling'] ?? ''),
                'no_tipe_rumah'           => (string) ($row['no_tipe_rumah'] ?? ''),
                'tipe_rumah'              => (string) ($row['tipe_rumah'] ?? ''),
                'is_nup'                  => (int) ($row['is_nup'] ?? 0) === 1,
                'no_nup'                  => ($row['no_nup'] ?? '') !== '' ? (string) $row['no_nup'] : null,
                'status_mkdt'             => (string) ($row['status_mkdt'] ?? ''),
                'is_batal'                => (int) ($row['is_batal'] ?? 0) === 1,
                'refund_paid'             => (int) ($row['refund_paid'] ?? 0) === 1,
                'is_lunas'                => (int) ($row['is_lunas'] ?? 0) === 1,
                'booking_fee'             => $bookingFee,
                'booking_tgl'             => $this->date($row['booking_tgl'] ?? null),
                'is_promo'                => $bookingFee <= 0,
                'id_pembayaran'           => ! empty($row['id_pembayaran']) ? (int) $row['id_pembayaran'] : null,
                'verified_by'             => ! empty($row['verified_by']) ? (int) $row['verified_by'] : null,
                'verified_by_name'        => ($row['verified_by_name'] ?? null) !== null && $row['verified_by_name'] !== '' ? (string) $row['verified_by_name'] : null,
                'verified_at'             => $row['verified_at'] ?? null,
                'shared_owners_count'     => max(1, (int) ($row['shared_owners_count'] ?? 1)),
                'can_unverify'            => ! $isBlocked && $canManage,
                'unverify_blocked_reason' => $isBlocked
                    ? 'Transaksi sudah dibatalkan atau refund sudah dibayarkan.'
                    : null,
            ];
        }

        $recordsTotal = (int) ($result['recordsTotal'] ?? 0);
        $recordsFiltered = (int) ($result['recordsFiltered'] ?? 0);

        return [
            'items'           => $items,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'meta'            => [
                'page'     => $page,
                'per_page' => $length,
                'total'    => $recordsFiltered,
            ],
        ];
    }

    public function assertMayDelete(int $paymentId): void
    {
        if ($this->repo->owners($paymentId)) {
            throw new \DomainException('Pembayaran booking otomatis tidak dapat dihapus dari riwayat pembayaran umum.');
        }
        $verifiedPromo = $this->db->table('log_pembayaran lp')
            ->join('mkdt_booking_payment mbp', 'mbp.id_mkdt = lp.id_mkdt')
            ->join('log_pembayaran_detail lpd', 'lpd.id_pembayaran = lp.id_pembayaran')
            ->join('keuangan_item_list kil', 'kil.id_keuangan_item_list = lpd.id_keuangan_item_list')
            ->where('lp.id_pembayaran', $paymentId)->where('mbp.verified_at IS NOT NULL', null, false)
            ->where('kil.kategori', 'BO')->where('lpd.booking_is_installment', 1)->countAllResults();
        if ($verifiedPromo) throw new \DomainException('Booking promo sudah diverifikasi dan sumber pembayarannya tidak dapat dihapus.');
    }

    public function assertManualAllocationAllowed(int $idMkdt, array $allocations): void
    {
        $mkdt = $this->repo->mkdt($idMkdt, true);
        $link = $this->repo->link($idMkdt, true);
        $bo = $this->repo->item('BO');
        foreach ($allocations as $allocation) {
            if ((int) $allocation['id'] !== (int) $bo['id_keuangan_item_list'] || $this->money($allocation['nominal']) <= 0) continue;
            if (! empty($link['verified_at'])) throw new \DomainException('Booking fee sudah diverifikasi dan tidak dapat menerima alokasi booking baru.');
            if ($this->money($mkdt['booking_fee'] ?? 0) > 0) throw new \DomainException('Booking fee positif sudah dicatat otomatis dari MKDT dan tidak dapat dibayar lagi.');
        }
    }

    public function getBooking(int $idMkdt): array
    {
        $mkdt = $this->repo->mkdt($idMkdt);
        $link = $this->repo->link($idMkdt);
        $declared = $this->money($mkdt['booking_fee'] ?? 0);
        $history = [];
        $nominal = $declared;
        $tanggal = $this->date($mkdt['booking_tgl'] ?? null);

        if ($declared <= 0) {
            foreach ($this->repo->bookingRows($idMkdt) as $row) {
                $allocated = 0.0;
                foreach ($this->repo->details((int) $row['id_pembayaran']) as $detail) {
                    if (($detail['kategori'] ?? '') === 'BO' && (int) ($detail['booking_is_installment'] ?? 0) === 1) {
                        $allocated += $this->money($detail['nominal']);
                    }
                }
                if ($allocated <= 0) continue;
                $nominal += $allocated;
                $tanggal ??= $this->date($row['tanggal_bayar'] ?? null);
                $history[] = $this->historyRow($row, $allocated, true);
            }
        } elseif (! empty($link['id_pembayaran'])) {
            $payment = $this->repo->payment((int) $link['id_pembayaran']);
            if ($payment) $history[] = $this->historyRow($payment, $this->money($payment['nominal']), false);
        }

        $isVerified = ! empty($link['verified_at']);
        $isBlocked = $this->isUnverifyBlocked($mkdt);
        $canManage = function_exists('in_groups') && in_groups(['1', '3']);

        return [
            'id_mkdt' => $idMkdt,
            'nominal_mkdt' => $declared,
            'tanggal_mkdt' => $this->date($mkdt['booking_tgl'] ?? null),
            'nominal' => $nominal,
            'tanggal' => $tanggal,
            'is_promo' => $declared <= 0,
            'is_paid' => true,
            'is_verified' => $isVerified,
            'verified_by' => $link['verified_by'] ?? null,
            'verified_by_name' => $isVerified
                ? $this->repo->findUsernameById(isset($link['verified_by']) ? (int) $link['verified_by'] : null)
                : null,
            'verified_at' => $link['verified_at'] ?? null,
            'locked' => $isVerified,
            'can_unverify' => $isVerified && ! $isBlocked && $canManage,
            'unverify_blocked_reason' => ($isVerified && $isBlocked)
                ? 'Transaksi sudah dibatalkan atau refund sudah dibayarkan.'
                : null,
            'id_pembayaran' => ! empty($link['id_pembayaran']) ? (int) $link['id_pembayaran'] : null,
            'history' => $history,
        ];
    }

    public function getInstallment(int $idMkdt): array
    {
        return $this->repo->installmentSummary($idMkdt);
    }

    private function isUnverifyBlocked(array $mkdt): bool
    {
        return strcasecmp(trim((string) ($mkdt['status_mkdt'] ?? '')), 'Batal') === 0
            || (int) ($mkdt['is_batal'] ?? 0) === 1
            || (int) ($mkdt['refund_paid'] ?? 0) === 1;
    }

    private function assertCanonicalMatches(array $mkdt, array $link): void
    {
        $nominal = $this->money($mkdt['booking_fee'] ?? 0);
        if ($nominal <= 0) return; // Promo without a receipt is valid and paid by business rule.
        if (empty($link['id_pembayaran'])) throw new \DomainException('Pembayaran booking otomatis belum tersedia.');
        $payment = $this->repo->payment((int) $link['id_pembayaran']);
        if (! $payment || (int) ($payment['is_deleted'] ?? 0) === 1) throw new \DomainException('Pembayaran booking tidak aktif.');
        $details = array_values(array_filter($this->repo->details((int) $payment['id_pembayaran']),
            static fn ($d) => ($d['kategori'] ?? '') === 'BO' && (int) ($d['booking_is_installment'] ?? 0) === 0));
        $allocated = array_sum(array_map(static fn ($d) => (float) $d['nominal'], $details));
        if ($this->money($payment['nominal']) !== $nominal || $this->money($allocated) !== $nominal
            || $this->date($payment['tanggal_bayar'] ?? null) !== $this->date($mkdt['booking_tgl'] ?? null)) {
            throw new \DomainException('Nominal atau tanggal pembayaran booking belum sesuai dengan data MKDT.');
        }
    }

    private function historyRow(array $row, float $nominal, bool $installment): array
    {
        return [
            'id_pembayaran' => (int) $row['id_pembayaran'], 'nominal' => $nominal,
            'tanggal_bayar' => $this->date($row['tanggal_bayar'] ?? null),
            'keterangan' => $row['keterangan'] ?? null, 'is_installment' => $installment,
        ];
    }

    private function money(mixed $value): float
    {
        return round((float) str_replace(',', '', (string) $value), 2);
    }

    private function date(mixed $value): ?string
    {
        if (! $value || $value === '0000-00-00') return null;
        return substr((string) $value, 0, 10);
    }

    private function validDate(?string $value): ?string
    {
        if (! $value || ! preg_match('/^\d{4}-\d{2}-\d{2}$/D', $value)) return null;
        [$year, $month, $day] = array_map('intval', explode('-', $value));
        return checkdate($month, $day, $year) ? $value : null;
    }
}

<?php

namespace App\Services;

use App\Repositories\FinanceLedgerRepository;

class FinanceLedgerService
{
    public const DIRECTION_INCOME = 'income';
    public const SOURCE_LOG_PEMBAYARAN = 'log_pembayaran';

    protected FinanceLedgerRepository $ledgerRepo;
    protected $db;

    public function __construct()
    {
        $this->ledgerRepo = new FinanceLedgerRepository();
        $this->db = \Config\Database::connect();
    }

    public function recordIncomeFromLogPembayaran(int $idPembayaran, ?int $actorId = null): int
    {
        $payment = $this->getLogPembayaran($idPembayaran);
        if (!$payment) {
            throw new \RuntimeException('Log pembayaran tidak ditemukan');
        }

        if ((int) ($payment->is_deleted ?? 0) === 1) {
            $this->voidByLogPembayaran($idPembayaran, $actorId);
            return 0;
        }

        $now = date('Y-m-d H:i:s');
        $createdAt = $payment->created_at ?: $now;
        $addBy = $payment->add_by ?: $actorId;
        $editBy = $actorId ?: ($payment->edit_by ?: $addBy);

        return $this->ledgerRepo->upsertBySource([
            'direction' => self::DIRECTION_INCOME,
            'source_type' => self::SOURCE_LOG_PEMBAYARAN,
            'source_id' => (int) $payment->id_pembayaran,
            'source_detail_id' => null,
            'id_mkdt' => $payment->id_mkdt ? (int) $payment->id_mkdt : null,
            'id_kavling' => $payment->id_kavling ? (int) $payment->id_kavling : null,
            'nominal' => $this->num($payment->nominal ?? 0),
            'tanggal_transaksi' => $payment->tanggal_bayar ?: null,
            'label' => $payment->payment_type ?: 'Pembayaran',
            'keterangan' => $payment->keterangan,
            'status' => 'active',
            'is_deleted' => 0,
            'deleted_at' => null,
            'deleted_by' => null,
            'add_by' => $addBy,
            'created_at' => $createdAt,
            'edit_by' => $editBy,
            'updated_at' => $payment->updated_at ?: $now,
        ]);
    }

    public function voidByLogPembayaran(int $idPembayaran, ?int $actorId = null): bool
    {
        return $this->ledgerRepo->voidBySource(self::SOURCE_LOG_PEMBAYARAN, $idPembayaran, $actorId);
    }

    public function getTotalIncome(array $filters = []): float
    {
        return $this->ledgerRepo->sumIncome($filters);
    }

    public function getTotalIncomeByMkdt(int $idMkdt): float
    {
        return $this->getTotalIncome(['id_mkdt' => $idMkdt]);
    }

    public function getTotalIncomeByKavling(int $idKavling): float
    {
        return $this->getTotalIncome(['id_kavling' => $idKavling]);
    }

    public function getTotalIncomeByDateRange(string $dateFrom, string $dateTo): float
    {
        return $this->getTotalIncome([
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ]);
    }

    protected function getLogPembayaran(int $idPembayaran): ?object
    {
        return $this->db->table('log_pembayaran lp')
            ->select('lp.*, mkdt.id_kavling')
            ->join('mkdt', 'mkdt.id_mkdt = lp.id_mkdt', 'left')
            ->where('lp.id_pembayaran', $idPembayaran)
            ->get()
            ->getRow();
    }

    protected function num($value): float
    {
        if ($value === null || $value === '') {
            return 0;
        }

        return (float) str_replace(',', '', (string) $value);
    }
}

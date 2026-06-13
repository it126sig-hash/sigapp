<?php

namespace App\Services;

use App\Repositories\FinanceLedgerRepository;

class FinanceLedgerService
{
    public const DIRECTION_INCOME = 'income';
    public const DIRECTION_EXPENSE = 'expense';
    public const SOURCE_LOG_PEMBAYARAN = 'log_pembayaran';
    public const SOURCE_DANA_JAMINAN = 'dana_jaminan';
    public const SOURCE_CASHOUT_SUBKON_ALLOCATION = 'cashout_subkon_allocation';
    public const SOURCE_BAYAR_PRODUKSI = 'bayar_produksi';

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

    public function recordIncomeFromDanaJaminan(int $idDanaAkad, int $idMkdt, ?int $actorId = null): int
    {
        $row = $this->getDanaJaminan($idDanaAkad);
        if (! $row) {
            throw new \RuntimeException('Data dana jaminan tidak ditemukan');
        }

        if ((int) ($row->sudah_cair ?? 0) !== 1) {
            $this->voidByDanaJaminan($idDanaAkad, $actorId);
            return 0;
        }

        $now = date('Y-m-d H:i:s');
        $createdAt = $row->cair_created_at ?: ($row->created_at ?: $now);
        $addBy = $row->cair_oleh ?: ($row->add_by ?: $actorId);
        $editBy = $actorId ?: ($row->edit_by ?: $addBy);
        $label = 'Dana Jaminan';
        if (! empty($row->nama_jaminan)) {
            $label .= ' - ' . $row->nama_jaminan;
        }

        return $this->ledgerRepo->upsertBySource([
            'direction' => self::DIRECTION_INCOME,
            'source_type' => self::SOURCE_DANA_JAMINAN,
            'source_id' => (int) $row->id,
            'source_detail_id' => null,
            'id_mkdt' => $idMkdt,
            'id_kavling' => $row->id_kavling ? (int) $row->id_kavling : null,
            'nominal' => $this->num($row->nominal_cair ?? 0),
            'tanggal_transaksi' => $row->tgl_cair ?: null,
            'label' => $label,
            'keterangan' => $row->keterangan,
            'status' => 'active',
            'is_deleted' => 0,
            'deleted_at' => null,
            'deleted_by' => null,
            'add_by' => $addBy,
            'created_at' => $createdAt,
            'edit_by' => $editBy,
            'updated_at' => $row->updated_at ?: $now,
        ]);
    }

    public function voidByDanaJaminan(int $idDanaAkad, ?int $actorId = null): bool
    {
        return $this->ledgerRepo->voidBySource(self::SOURCE_DANA_JAMINAN, $idDanaAkad, $actorId);
    }

    public function recordExpenseFromCashoutSubkonDetail(int $idCashoutSubkonDetail, ?int $actorId = null): array
    {
        $detail = $this->getCashoutSubkonDetail($idCashoutSubkonDetail);
        if (!$detail) {
            throw new \RuntimeException('Detail cashout subkon tidak ditemukan');
        }

        $allocations = $this->getCashoutSubkonAllocations($idCashoutSubkonDetail);
        if (empty($allocations)) {
            throw new \RuntimeException('Allocation cashout subkon belum tersedia');
        }

        if ((int) ($detail->status ?? 0) !== 4 || (int) ($detail->is_paid ?? 0) !== 1) {
            foreach ($allocations as $allocation) {
                $this->ledgerRepo->voidBySource(
                    self::SOURCE_CASHOUT_SUBKON_ALLOCATION,
                    (int) $allocation->id_cashout_subkon_detail_allocation,
                    $actorId
                );
            }

            return [];
        }

        $now = date('Y-m-d H:i:s');
        $createdAt = $detail->cek_created_at ?: $now;
        $addBy = $detail->cek_add_by ?: $actorId;
        $editBy = $actorId ?: $addBy;
        $label = 'Cashout Subkon';
        if (!empty($detail->berita_acara)) {
            $label .= ' - ' . $detail->berita_acara;
        }

        $ids = [];
        foreach ($allocations as $allocation) {
            $ids[] = $this->ledgerRepo->upsertBySource([
                'direction' => self::DIRECTION_EXPENSE,
                'source_type' => self::SOURCE_CASHOUT_SUBKON_ALLOCATION,
                'source_id' => (int) $allocation->id_cashout_subkon_detail_allocation,
                'source_detail_id' => (int) $detail->id_cashout_subkon_detail,
                'id_mkdt' => null,
                'id_kavling' => $allocation->id_kavling ? (int) $allocation->id_kavling : null,
                'nominal' => $this->num($allocation->nominal ?? 0),
                'tanggal_transaksi' => $detail->cek_tgl ?: null,
                'label' => $label,
                'keterangan' => $this->cashoutSubkonExpenseDescription($detail),
                'status' => 'active',
                'is_deleted' => 0,
                'deleted_at' => null,
                'deleted_by' => null,
                'add_by' => $addBy,
                'created_at' => $createdAt,
                'edit_by' => $editBy,
                'updated_at' => $now,
            ]);
        }

        return $ids;
    }

    public function recordExpenseFromBayarProduksi(int $idBayarProduksi, ?int $actorId = null): int
    {
        $row = $this->getBayarProduksi($idBayarProduksi);
        if (!$row) {
            throw new \RuntimeException('Pembayaran produksi tidak ditemukan');
        }

        $now = date('Y-m-d H:i:s');
        $createdAt = $row->created_at ?: $now;
        $addBy = $row->add_by ?: $actorId;
        $editBy = $actorId ?: ($row->edit_by ?: $addBy);
        $label = 'Pembayaran Produksi';
        if (!empty($row->item)) {
            $label .= ' - ' . $row->item;
        }

        return $this->ledgerRepo->upsertBySource([
            'direction' => self::DIRECTION_EXPENSE,
            'source_type' => self::SOURCE_BAYAR_PRODUKSI,
            'source_id' => (int) $row->id,
            'source_detail_id' => $row->id_item_produksi ? (int) $row->id_item_produksi : null,
            'id_mkdt' => $row->id_mkdt ? (int) $row->id_mkdt : null,
            'id_kavling' => $row->id_kavling ? (int) $row->id_kavling : null,
            'nominal' => $this->num($row->nominal ?? 0),
            'tanggal_transaksi' => $row->tanggal_bayar ?: null,
            'label' => $label,
            'keterangan' => $row->keterangan,
            'status' => 'active',
            'is_deleted' => 0,
            'deleted_at' => null,
            'deleted_by' => null,
            'add_by' => $addBy,
            'created_at' => $createdAt,
            'edit_by' => $editBy,
            'updated_at' => $row->updated_at ?: $now,
        ]);
    }

    public function voidByBayarProduksi(int $idBayarProduksi, ?int $actorId = null): bool
    {
        return $this->ledgerRepo->voidBySource(self::SOURCE_BAYAR_PRODUKSI, $idBayarProduksi, $actorId);
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

    protected function getDanaJaminan(int $idDanaAkad): ?object
    {
        return $this->db->table('dana_akad da')
            ->select('da.*, ld.nama_jaminan')
            ->join('list_dajam ld', 'ld.id = da.id_list_dajam', 'left')
            ->where('da.id', $idDanaAkad)
            ->get()
            ->getRow();
    }

    protected function getCashoutSubkonDetail(int $idCashoutSubkonDetail): ?object
    {
        return $this->db->table('cashout_subkon_detail csd')
            ->select('
                csd.*,
                cs.nomor_surat,
                cs.tanggal_surat,
                s.nama_subkon
            ')
            ->join('cashout_subkon cs', 'cs.id_cashout_subkon = csd.id_cashout_subkon', 'left')
            ->join('subkon s', 's.id = cs.id_subkon', 'left')
            ->where('csd.id_cashout_subkon_detail', $idCashoutSubkonDetail)
            ->get()
            ->getRow();
    }

    protected function getCashoutSubkonAllocations(int $idCashoutSubkonDetail): array
    {
        if (!$this->db->tableExists('cashout_subkon_detail_allocation')) {
            return [];
        }

        return $this->db->table('cashout_subkon_detail_allocation')
            ->where('id_cashout_subkon_detail', $idCashoutSubkonDetail)
            ->orderBy('id_kavling', 'ASC')
            ->get()
            ->getResult();
    }

    protected function getBayarProduksi(int $idBayarProduksi): ?object
    {
        return $this->db->table('bayar_produksi bp')
            ->select('bp.*, lbp.item, kavling.id_mkdt')
            ->join('list_bayar_produksi lbp', 'lbp.id = bp.id_item_produksi', 'left')
            ->join('kavling', 'kavling.id_kavling = bp.id_kavling', 'left')
            ->where('bp.id', $idBayarProduksi)
            ->get()
            ->getRow();
    }

    protected function cashoutSubkonExpenseDescription(object $detail): string
    {
        $parts = [];
        if (!empty($detail->nama_subkon)) {
            $parts[] = 'Subkon: ' . $detail->nama_subkon;
        }
        if (!empty($detail->nomor_surat)) {
            $parts[] = 'No SPK: ' . $detail->nomor_surat;
        }
        if (!empty($detail->cek_no)) {
            $parts[] = 'No Cek: ' . $detail->cek_no;
        }

        return implode(' | ', $parts);
    }

    protected function num($value): float
    {
        if ($value === null || $value === '') {
            return 0;
        }

        return (float) str_replace(',', '', (string) $value);
    }
}

<?php

namespace App\Services;

use App\Repositories\BookingPaymentRepository;
use CodeIgniter\Database\BaseConnection;

class MkdtSettlementService
{
    private BookingPaymentRepository $repository;

    public function __construct(private ?BaseConnection $db = null)
    {
        $this->db ??= \Config\Database::connect();
        $this->repository = new BookingPaymentRepository($this->db);
    }

    /**
     * Recalculate and persist the canonical settlement flag for one MKDT.
     *
     * @return array{is_lunas:int, changed:bool, total_tagihan:float, sudah_bayar:float}
     */
    public function synchronize(int $idMkdt): array
    {
        if ($idMkdt <= 0) {
            throw new \InvalidArgumentException('ID MKDT tidak valid');
        }

        $summary = $this->repository->installmentSummary($idMkdt);
        $mkdt = $this->repository->mkdt($idMkdt);
        $totalTagihan = round((float) ($summary['total_tagihan'] ?? 0), 2);
        $sudahBayar = round((float) ($summary['sudah_bayar'] ?? 0), 2);
        $isLunas = $totalTagihan > 0 && $sudahBayar >= $totalTagihan ? 1 : 0;
        $changed = (int) ($mkdt['is_lunas'] ?? 0) !== $isLunas;

        if ($changed) {
            $this->repository->write('mkdt', ['is_lunas' => $isLunas], ['id_mkdt' => $idMkdt]);
        }

        return [
            'is_lunas' => $isLunas,
            'changed' => $changed,
            'total_tagihan' => $totalTagihan,
            'sudah_bayar' => $sudahBayar,
        ];
    }
}

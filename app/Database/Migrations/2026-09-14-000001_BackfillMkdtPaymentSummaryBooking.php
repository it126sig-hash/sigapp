<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class BackfillMkdtPaymentSummaryBooking extends Migration
{
    public function up()
    {
        if (
            ! $this->db->tableExists('mkdt_payment_summary')
            || ! $this->db->fieldExists('total_booking', 'mkdt_payment_summary')
            || ! $this->db->tableExists('log_pembayaran')
            || ! $this->db->tableExists('log_pembayaran_detail')
            || ! $this->db->tableExists('keuangan_item_list')
        ) {
            return;
        }

        $bookingTotals = [];
        $detailTotals = $this->db->table('log_pembayaran_detail lpd')
            ->select('lp.id_mkdt, COALESCE(SUM(lpd.nominal), 0) AS total_booking')
            ->join('log_pembayaran lp', 'lp.id_pembayaran = lpd.id_pembayaran')
            ->join('keuangan_item_list kil', 'kil.id_keuangan_item_list = lpd.id_keuangan_item_list')
            ->where('lp.is_deleted', 0)
            ->where('kil.kategori', 'BO')
            ->groupBy('lp.id_mkdt')
            ->get()
            ->getResultArray();

        foreach ($detailTotals as $row) {
            $bookingTotals[(int) $row['id_mkdt']] = (float) $row['total_booking'];
        }

        // Data lama dapat tidak memiliki detail alokasi. Dalam kondisi itu,
        // nominal log Booking menjadi fallback selama pembayarannya masih aktif.
        $legacyBookingRows = $this->db->table('log_pembayaran lp')
            ->select('lp.id_mkdt, lp.nominal, lp.payment_type')
            ->join('log_pembayaran_detail lpd', 'lpd.id_pembayaran = lp.id_pembayaran', 'left')
            ->where('lp.is_deleted', 0)
            ->where('lpd.id_pembayaran', null)
            ->get()
            ->getResultArray();

        foreach ($legacyBookingRows as $row) {
            $paymentType = strtolower(trim(trim((string) $row['payment_type']), ';'));
            if ($paymentType !== 'booking') {
                continue;
            }

            $idMkdt = (int) $row['id_mkdt'];
            $bookingTotals[$idMkdt] = ($bookingTotals[$idMkdt] ?? 0) + (float) $row['nominal'];
        }

        $this->db->transBegin();

        try {
            $this->db->table('mkdt_payment_summary')->set('total_booking', 0)->update();

            foreach ($bookingTotals as $idMkdt => $totalBooking) {
                $exists = $this->db->table('mkdt_payment_summary')
                    ->select('id_mkdt')
                    ->where('id_mkdt', $idMkdt)
                    ->get()
                    ->getRow();

                if ($exists) {
                    $this->db->table('mkdt_payment_summary')
                        ->where('id_mkdt', $idMkdt)
                        ->update(['total_booking' => $totalBooking]);
                    continue;
                }

                $this->db->table('mkdt_payment_summary')->insert([
                    'id_mkdt'       => $idMkdt,
                    'total_booking' => $totalBooking,
                ]);
            }

            if ($this->db->transStatus() === false) {
                throw new \RuntimeException('Gagal menghitung ulang total pembayaran Booking');
            }

            $this->db->transCommit();
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    public function down()
    {
        // No-op: total_booking adalah derived data dan nilai sebelum backfill
        // tidak dapat dipulihkan secara aman.
    }
}

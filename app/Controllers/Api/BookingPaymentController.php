<?php

namespace App\Controllers\Api;

use App\Services\ActiveProyekService;
use App\Services\BookingPaymentService;
use CodeIgniter\HTTP\ResponseInterface;

class BookingPaymentController extends BaseApiController
{
    public function verify(): ResponseInterface
    {
        if (! in_groups(['1', '3'])) return $this->error('Akses hanya untuk Keuangan atau administrator.', 403);
        $idMkdt = (int) $this->request->getPost('id_mkdt');
        if ($idMkdt <= 0) return $this->error('ID MKDT tidak valid.', 422);

        $nominalRaw = trim((string) $this->request->getPost('booking_fee'));
        $tanggal = trim((string) $this->request->getPost('booking_tgl'));
        if (! preg_match('/^\d+(?:\.\d{1,2})?$/D', $nominalRaw)) {
            return $this->error('Nominal booking tidak valid.', 422);
        }
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $tanggal);
        if (! $date || $date->format('Y-m-d') !== $tanggal) {
            return $this->error('Tanggal booking tidak valid.', 422);
        }

        $db = \Config\Database::connect();
        $project = $db->table('mkdt m')->select('c.id_proyek')
            ->join('kavling k', 'k.id_kavling = m.id_kavling')
            ->join('jalan j', 'j.id_jalan = k.id_jalan')
            ->join('cluster c', 'c.id_cluster = j.id_cluster')
            ->where('m.id_mkdt', $idMkdt)->get()->getRowArray();
        if (! $project) return $this->error('Proyek transaksi tidak ditemukan. Rekonsiliasi relasi kavling terlebih dahulu.', 409);
        if (! (new ActiveProyekService())->userCanAccess((int) $project['id_proyek'], (int) user_id())) {
            return $this->error('Anda tidak memiliki akses ke proyek transaksi ini.', 403);
        }

        try {
            $booking = (new BookingPaymentService($db))->verify(
                $idMkdt,
                (int) user_id(),
                (float) $nominalRaw,
                $tanggal
            );
            return $this->success($booking, 'Booking fee berhasil diverifikasi.');
        } catch (\DomainException $e) {
            return $this->error($e->getMessage(), 409);
        } catch (\Throwable $e) {
            log_message('error', 'Verifikasi booking MKDT {id}: {error}', ['id' => $idMkdt, 'error' => $e->getMessage()]);
            return $this->error('Verifikasi booking fee gagal.', 500);
        }
    }
}

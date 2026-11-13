<?php

namespace App\Controllers\Api\Keuangan;

use App\Controllers\Api\BaseApiController;
use App\Services\Keuangan\BookingPaymentService;
use App\Services\Proyek\ActiveProyekService;
use CodeIgniter\HTTP\ResponseInterface;

class BookingPaymentController extends BaseApiController
{
    private BookingPaymentService $bookingPaymentService;
    private ActiveProyekService $activeProyekService;

    public function __construct()
    {
        $this->bookingPaymentService = new BookingPaymentService();
        $this->activeProyekService = new ActiveProyekService();
    }

    public function verify(): ResponseInterface
    {
        if (! in_groups(['1', '3'])) {
            return $this->error('Akses hanya untuk Keuangan atau administrator.', 403);
        }

        $input = $this->readRequestData();
        $idMkdt = (int) ($input['id_mkdt'] ?? 0);
        if ($idMkdt <= 0) {
            return $this->error('ID MKDT tidak valid.', 422);
        }

        $nominalRaw = trim((string) ($input['booking_fee'] ?? ''));
        $tanggal = trim((string) ($input['booking_tgl'] ?? ''));
        if (! preg_match('/^\d+(?:\.\d{1,2})?$/D', $nominalRaw)) {
            return $this->error('Nominal booking tidak valid.', 422);
        }
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $tanggal);
        if (! $date || $date->format('Y-m-d') !== $tanggal) {
            return $this->error('Tanggal booking tidak valid.', 422);
        }

        $projectId = $this->bookingPaymentService->findProjectIdByMkdt($idMkdt);
        if (! $projectId) {
            return $this->error('Proyek transaksi tidak ditemukan. Rekonsiliasi relasi kavling terlebih dahulu.', 409);
        }
        if (! $this->activeProyekService->userCanAccess($projectId, (int) user_id())) {
            return $this->error('Anda tidak memiliki akses ke proyek transaksi ini.', 403);
        }

        try {
            $booking = $this->bookingPaymentService->verify(
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

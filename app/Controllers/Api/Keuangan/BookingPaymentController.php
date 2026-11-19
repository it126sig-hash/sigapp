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

    public function unverify(): ResponseInterface
    {
        if (! function_exists('in_groups') || ! in_groups(['1', '3'])) {
            return $this->error('Akses hanya untuk Keuangan atau administrator.', 403);
        }

        $input = $this->readRequestData();
        $idMkdt = (int) ($input['id_mkdt'] ?? 0);
        if ($idMkdt <= 0) {
            return $this->error('ID MKDT tidak valid.', 422);
        }

        $reason = trim((string) ($input['reason'] ?? ''));
        $reasonLength = mb_strlen($reason);
        if ($reasonLength < 3 || $reasonLength > 500) {
            return $this->error('Alasan pembatalan verifikasi wajib diisi (3–500 karakter).', 422, [
                'reason' => $reasonLength < 3
                    ? 'Alasan pembatalan verifikasi wajib diisi minimal 3 karakter.'
                    : 'Alasan pembatalan verifikasi maksimal 500 karakter.',
            ]);
        }

        $projectId = $this->bookingPaymentService->findProjectIdByMkdt($idMkdt);
        if (! $projectId) {
            return $this->error('Proyek transaksi tidak ditemukan. Rekonsiliasi relasi kavling terlebih dahulu.', 409);
        }
        if (! $this->activeProyekService->userCanAccess($projectId, (int) user_id())) {
            return $this->error('Anda tidak memiliki akses ke proyek transaksi ini.', 403);
        }

        try {
            $booking = $this->bookingPaymentService->unverify($idMkdt, (int) user_id(), $reason);
            return $this->success($booking, 'Verifikasi booking fee berhasil dibatalkan.');
        } catch (\DomainException $e) {
            return $this->error($e->getMessage(), 409);
        } catch (\Throwable $e) {
            log_message('error', 'Batal verifikasi booking MKDT {id}: {error}', ['id' => $idMkdt, 'error' => $e->getMessage()]);
            return $this->error('Pembatalan verifikasi booking fee gagal.', 500);
        }
    }

    public function verifiedList(): ResponseInterface
    {
        if (! function_exists('resolve_active_proyek_id')) {
            helper('active_proyek');
        }

        $input = $this->readRequestData();
        $idProyek = resolve_active_proyek_id($input['id_proyek'] ?? null);

        $draw = (int) ($input['draw'] ?? 1);
        $length = (int) ($input['length'] ?? $input['per_page'] ?? 25);
        $length = max(1, min(100, $length));

        if (isset($input['start'])) {
            $start = max(0, (int) $input['start']);
        } else {
            $page = max(1, (int) ($input['page'] ?? 1));
            $start = ($page - 1) * $length;
        }

        $searchRaw = is_array($input['search'] ?? null)
            ? ($input['search']['value'] ?? '')
            : ($input['search'] ?? '');
        $search = trim((string) $searchRaw);

        $idCluster = (int) ($input['id_cluster'] ?? 0);
        $idJalan = (int) ($input['id_jalan'] ?? 0);

        if ($idProyek === null) {
            if ($this->isRawFormat()) {
                return $this->success([
                    'items' => [],
                    'meta' => [
                        'page' => 1,
                        'per_page' => $length,
                        'total' => 0,
                    ],
                ], 'Proyek aktif belum dipilih.');
            }

            return $this->respond([
                'success' => true,
                'messages' => 'Proyek aktif belum dipilih.',
                'token' => csrf_hash(),
                'draw' => $draw,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'meta' => [
                    'page' => 1,
                    'per_page' => $length,
                    'total' => 0,
                ],
            ], 200);
        }

        if (! $this->activeProyekService->userCanAccess((int) $idProyek, (int) user_id())) {
            return $this->error('Anda tidak memiliki akses ke proyek ini.', 403);
        }

        $canManage = function_exists('in_groups') && in_groups(['1', '3']);

        try {
            $result = $this->bookingPaymentService->getVerifiedList((int) $idProyek, [
                'start' => $start,
                'length' => $length,
                'search' => $search,
                'id_cluster' => $idCluster,
                'id_jalan' => $idJalan,
            ], $canManage);

            if ($this->isRawFormat()) {
                return $this->success([
                    'items' => $result['items'],
                    'meta' => $result['meta'],
                ], 'Daftar booking fee terverifikasi berhasil dimuat.');
            }

            return $this->respond([
                'success' => true,
                'messages' => 'Daftar booking fee terverifikasi berhasil dimuat.',
                'token' => csrf_hash(),
                'draw' => $draw,
                'recordsTotal' => $result['recordsTotal'],
                'recordsFiltered' => $result['recordsFiltered'],
                'data' => $result['items'],
                'meta' => $result['meta'],
            ], 200);
        } catch (\Throwable $e) {
            log_message('error', 'Daftar booking fee terverifikasi proyek {id}: {error}', ['id' => $idProyek, 'error' => $e->getMessage()]);
            return $this->error('Gagal memuat daftar booking fee terverifikasi.', 500);
        }
    }
}

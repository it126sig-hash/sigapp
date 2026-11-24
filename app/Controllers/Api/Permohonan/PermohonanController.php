<?php

namespace App\Controllers\Api\Permohonan;

use App\Controllers\Api\BaseApiController;
use App\Services\Permohonan\PermohonanService;
use CodeIgniter\HTTP\ResponseInterface;

class PermohonanController extends BaseApiController
{
    protected PermohonanService $service;

    public function __construct(?PermohonanService $service = null)
    {
        if (! function_exists('user_id')) {
            helper('auth');
        }

        $this->service = $service ?? new PermohonanService();
    }

    /**
     * GET /api/permohonan/meta
     */
    public function meta(): ResponseInterface
    {
        $userId = $this->resolveUserId();
        if ($userId <= 0) {
            return $this->error('Sesi telah berakhir, silakan login kembali.', 401);
        }

        try {
            $data = $this->service->getMeta($userId);
            return $this->success($data, 'Metadata pusat permohonan berhasil dimuat.');
        } catch (\Throwable $e) {
            log_message('error', '[Api\Permohonan\PermohonanController::meta] {message}', ['message' => $e->getMessage()]);
            return $this->error('Gagal memuat metadata pusat permohonan.', 500);
        }
    }

    /**
     * GET /api/permohonan/summary
     */
    public function summary(): ResponseInterface
    {
        $userId = $this->resolveUserId();
        if ($userId <= 0) {
            return $this->error('Sesi telah berakhir, silakan login kembali.', 401);
        }

        $input = $this->readRequestData();
        $rules = [
            'id_proyek' => 'permit_empty|integer',
            'scope'     => 'permit_empty|in_list[assigned_to_me,my_requests,department_monitoring]',
        ];

        if (! $this->validateData($input, $rules)) {
            return $this->error('Validasi parameter summary gagal.', 422, $this->validator->getErrors());
        }

        $idProyek = isset($input['id_proyek']) && $input['id_proyek'] !== '' && (int) $input['id_proyek'] > 0
            ? (int) $input['id_proyek']
            : null;
        $scope = ! empty($input['scope']) ? (string) $input['scope'] : 'assigned_to_me';

        try {
            $data = $this->service->getSummary($userId, $idProyek, $scope);
            return $this->success($data, 'Ringkasan pusat permohonan berhasil dimuat.');
        } catch (\Throwable $e) {
            log_message('error', '[Api\Permohonan\PermohonanController::summary] {message}', ['message' => $e->getMessage()]);
            return $this->error('Gagal memuat ringkasan pusat permohonan.', 500);
        }
    }

    /**
     * GET /api/permohonan/list
     */
    public function list(): ResponseInterface
    {
        $userId = $this->resolveUserId();
        if ($userId <= 0) {
            return $this->error('Sesi telah berakhir, silakan login kembali.', 401);
        }

        $input = $this->readRequestData();
        $rules = [
            'scope'     => 'permit_empty|in_list[assigned_to_me,my_requests,department_monitoring]',
            'module'    => 'permit_empty|in_list[all,bpb,fee_sales,kavling_request,tagihan_direksi,cashout_subkon,mgm,tiket_masalah]',
            'status'    => 'permit_empty|max_length[50]',
            'id_proyek' => 'permit_empty|integer',
            'search'    => 'permit_empty|max_length[120]',
            'page'      => 'permit_empty|is_natural_no_zero',
            'per_page'  => 'permit_empty|is_natural_no_zero',
        ];

        if (! $this->validateData($input, $rules)) {
            return $this->error('Validasi parameter daftar permohonan gagal.', 422, $this->validator->getErrors());
        }

        try {
            $data = $this->service->getList($userId, $input);
            return $this->success($data, 'Daftar permohonan berhasil dimuat.');
        } catch (\Throwable $e) {
            log_message('error', '[Api\Permohonan\PermohonanController::list] {message}', ['message' => $e->getMessage()]);
            return $this->error('Gagal memuat daftar permohonan.', 500);
        }
    }

    private function resolveUserId(): int
    {
        return (int) (user_id() ?? 0);
    }
}

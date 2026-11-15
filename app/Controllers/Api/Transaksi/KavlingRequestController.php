<?php

namespace App\Controllers\Api\Transaksi;

use App\Controllers\Api\BaseApiController;
use App\Repositories\Transaksi\KavlingRepository;
use App\Services\Transaksi\KavlingRequestService;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class KavlingRequestController extends BaseApiController
{
    protected KavlingRequestService $service;
    protected KavlingRepository $kavlingRepo;

    public function __construct(
        ?KavlingRequestService $service = null,
        ?KavlingRepository $kavlingRepo = null
    ) {
        helper('auth');

        $this->service     = $service ?? new KavlingRequestService();
        $this->kavlingRepo = $kavlingRepo ?? new KavlingRepository();
    }

    /**
     * Submit pengajuan request penambahan atau ubah tipe kavling
     */
    public function submit(): ResponseInterface
    {
        $userId = (int) (function_exists('user_id') ? (user_id() ?? 0) : 0);
        if ($userId <= 0) {
            return $this->error('Sesi telah berakhir, silakan login kembali.', 401);
        }

        $input = $this->getRequestData();

        $rules = [
            'id_proyek'     => 'required|is_natural_no_zero',
            'jenis_request' => 'required|in_list[tambah_baru,ubah_tipe]',
        ];

        if (! $this->validateData($input, $rules)) {
            return $this->error('Validasi gagal', 422, $this->validator->getErrors());
        }

        try {
            $result = $this->service->submitRequest($input, $userId);

            return $this->success($result, $result['message']);
        } catch (Throwable $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * List kavling untuk select2 dropdown pada proyek tertentu
     */
    public function kavlingList(): ResponseInterface
    {
        $input    = $this->getRequestData();
        $idProyek = (int) ($input['id_proyek'] ?? 0);
        $search   = (string) ($input['search'] ?? '');
        $limit    = isset($input['limit']) && (int) $input['limit'] > 0
            ? min(100, (int) $input['limit'])
            : 30;

        if ($idProyek <= 0) {
            return $this->respond([
                'success'  => true,
                'messages' => 'Daftar kavling berhasil diambil',
                'data'     => [],
                'results'  => [],
                'token'    => csrf_hash(),
            ]);
        }

        $list = $this->kavlingRepo->getKavlingList($idProyek, $search, $limit);

        $results = [];
        foreach ($list as $item) {
            $results[] = [
                'id'   => (int) $item->id_kavling,
                'text' => $item->nama_jalan . ' No. ' . $item->no_kavling,
            ];
        }

        return $this->respond([
            'success'  => true,
            'messages' => 'Daftar kavling berhasil diambil',
            'data'     => $results,
            'results'  => $results,
            'token'    => csrf_hash(),
        ]);
    }

    /**
     * Menampilkan daftar request kavling untuk tim Planning / Admin
     */
    public function list(): ResponseInterface
    {
        if (! in_groups(['1', '6'])) {
            return $this->error('Akses ditolak. Hanya tim Planning yang dapat mengakses data ini.', 403);
        }

        $input    = $this->getRequestData();
        $idProyek = (int) ($input['id_proyek'] ?? 0);
        if ($idProyek <= 0) {
            return $this->error('ID Proyek diperlukan', 400);
        }

        $rawStatus    = isset($input['status']) ? trim((string) $input['status']) : '';
        $statusFilter = in_array($rawStatus, ['pending', 'approved', 'rejected'], true) ? $rawStatus : null;

        if ($rawStatus !== '' && $rawStatus !== 'all' && $statusFilter === null) {
            return $this->error('Filter status tidak valid.', 422, [
                'status' => 'Status harus bernilai pending, approved, atau rejected.',
            ]);
        }

        $hasPagination = array_key_exists('page', $input) || array_key_exists('per_page', $input);
        $page          = max(1, (int) ($input['page'] ?? 1));
        $perPage       = $hasPagination
            ? min(100, max(1, (int) ($input['per_page'] ?? 25)))
            : 500;

        try {
            $data   = $this->service->getList($idProyek, $statusFilter, $page, $perPage);
            $counts = $this->service->getStatusCounts($idProyek);
            $total  = match ($statusFilter) {
                'pending'  => $counts['pending'],
                'approved' => $counts['approved'],
                'rejected' => $counts['rejected'],
                default    => $counts['all'],
            };

            return $this->respond([
                'success'  => true,
                'messages' => 'Daftar request kavling berhasil diambil',
                'data'     => $data,
                'counts'   => $counts,
                'meta'     => [
                    'page'     => $page,
                    'per_page' => $perPage,
                    'total'    => $total,
                ],
                'token'    => csrf_hash(),
            ]);
        } catch (Throwable $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * Mengubah status request kavling (contoh: approved/selesai, pending, atau rejected)
     */
    public function updateStatus(): ResponseInterface
    {
        if (! in_groups(['1', '6'])) {
            return $this->error('Akses ditolak. Hanya tim Planning yang dapat mengubah status request.', 403);
        }

        $input     = $this->getRequestData();
        $idRequest = (int) ($input['id_request'] ?? 0);
        $status    = trim((string) ($input['status'] ?? ''));

        if ($idRequest <= 0 || $status === '') {
            return $this->error('Parameter id_request dan status wajib diisi.', 400);
        }

        if (! in_array($status, ['pending', 'approved', 'rejected'], true)) {
            return $this->error('Status tidak valid.', 422, [
                'status' => 'Status harus bernilai pending, approved, atau rejected.',
            ]);
        }

        try {
            $userId = (int) (function_exists('user_id') ? (user_id() ?? 0) : 0);
            $result = $this->service->updateStatus($idRequest, $status, $userId);

            return $this->success($result, $result['message']);
        } catch (Throwable $e) {
            $code = str_contains(strtolower($e->getMessage()), 'tidak ditemukan') ? 404 : 400;

            return $this->error($e->getMessage(), $code);
        }
    }

    /**
     * Gabungkan query GET, POST form, dan JSON body serta fallback header X-Project-Id.
     */
    private function getRequestData(): array
    {
        $data = $this->readRequestData();

        if (empty($data['id_proyek'])) {
            $headerProjectId = $this->request->header('X-Project-Id')?->getValue();
            if ($headerProjectId !== null && $headerProjectId !== '' && is_numeric($headerProjectId) && (int) $headerProjectId > 0) {
                $data['id_proyek'] = (int) $headerProjectId;
            }
        }

        return $data;
    }
}

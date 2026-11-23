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

        $input         = $this->getRequestData();
        $allowedGroups = array_key_exists('items', $input) ? ['1', '4', '6'] : ['1', '4'];
        if (! in_groups($allowedGroups)) {
            return $this->error('Akses ditolak. Anda tidak memiliki hak akses untuk mengajukan request kavling.', 403);
        }

        $rules = [
            'id_proyek'     => 'required|is_natural_no_zero',
            'jenis_request' => 'required|in_list[tambah_baru,ubah_tipe]',
            'id_tipe'       => 'permit_empty|is_natural_no_zero',
            'tipe_manual'   => 'permit_empty|max_length[100]',
        ];

        if (! array_key_exists('items', $input)) {
            if (array_key_exists('keterangan', $input)) {
                $input['keterangan'] = trim((string) $input['keterangan']);
            }
            $rules['keterangan'] = 'required|min_length[1]';
        }

        if (! $this->validateData($input, $rules)) {
            return $this->error('Validasi gagal', 422, $this->validator->getErrors());
        }

        if (array_key_exists('items', $input) && (! is_array($input['items']) || $input['items'] === [])) {
            return $this->error('Validasi gagal', 422, [
                'items' => 'Daftar items pengajuan wajib berupa array yang tidak kosong.',
            ]);
        }

        if ($input['jenis_request'] === 'tambah_baru' && array_key_exists('id_jalans', $input)) {
            if ($input['id_jalans'] !== null && $input['id_jalans'] !== '' && ! is_array($input['id_jalans'])) {
                return $this->error('Validasi gagal', 422, [
                    'id_jalans' => 'Daftar jalan pengajuan wajib berupa array.',
                ]);
            }
        }

        if ($input['jenis_request'] === 'ubah_tipe' && ! array_key_exists('items', $input)) {
            $rawKavlings   = $input['id_kavlings'] ?? null;
            $validKavlings = is_array($rawKavlings)
                ? array_values(array_filter(array_map('intval', $rawKavlings), static fn (int $id): bool => $id > 0))
                : [];

            if ($validKavlings === []) {
                return $this->error('Validasi gagal', 422, [
                    'id_kavlings' => 'Minimal 1 kavling harus dipilih untuk pengajuan ubah tipe.',
                ]);
            }
        }

        try {
            $result = $this->service->submitRequest($input, $userId);

            return $this->success($result, $result['message']);
        } catch (Throwable $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * List kavling untuk select2 dropdown pada proyek tertentu (memblokir Akad & Akad Indent)
     */
    public function kavlingList(): ResponseInterface
    {
        if (! in_groups(['1', '4', '6'])) {
            return $this->error('Akses ditolak. Anda tidak memiliki hak akses untuk melihat daftar kavling.', 403);
        }

        $input    = $this->getRequestData();
        $idProyek = (int) ($input['id_proyek'] ?? 0);
        $search   = (string) ($input['search'] ?? '');
        $limit    = isset($input['limit']) && (int) $input['limit'] > 0
            ? min(100, (int) $input['limit'])
            : 50;

        if ($idProyek <= 0) {
            return $this->respond([
                'success'  => true,
                'messages' => 'Daftar kavling berhasil diambil',
                'data'     => [],
                'results'  => [],
                'token'    => csrf_hash(),
            ]);
        }

        $list    = $this->kavlingRepo->getKavlingListForRequest($idProyek, $search, $limit);
        $results = [];

        foreach ($list as $item) {
            $hasActive = ! empty($item->id_mkdt)
                && (int) ($item->is_batal ?? 0) === 0
                && strcasecmp(trim((string) ($item->status_mkdt ?? '')), 'Batal') !== 0;

            $noTipe           = trim((string) ($item->no_tipe_rumah ?? ''));
            $namaTipe         = trim((string) ($item->tipe_rumah ?? ''));
            $currentTipeLabel = null;
            if ($noTipe !== '' && $namaTipe !== '') {
                $currentTipeLabel = "{$noTipe} ({$namaTipe})";
            } elseif ($noTipe !== '' || $namaTipe !== '') {
                $currentTipeLabel = $noTipe !== '' ? $noTipe : $namaTipe;
            }

            $namaKonsumen = $hasActive ? ($item->nama_konsumen ?: null) : null;
            $statusMkdt   = $hasActive ? ($item->status_mkdt ?: null) : null;

            $text = trim((string) $item->nama_jalan) . ' No. ' . trim((string) $item->no_kavling);
            if ($currentTipeLabel !== null) {
                $text .= ' - ' . $currentTipeLabel;
            }
            if ($hasActive && $namaKonsumen !== null) {
                $text .= ' [Konsumen: ' . $namaKonsumen . ($statusMkdt !== null ? ' - ' . $statusMkdt : '') . ']';
            }

            $results[] = [
                'id'                  => (int) $item->id_kavling,
                'no_kavling'          => (string) $item->no_kavling,
                'nama_jalan'          => (string) $item->nama_jalan,
                'current_id_tipe'     => ! empty($item->id_tipe) ? (int) $item->id_tipe : null,
                'current_tipe_label'  => $currentTipeLabel,
                'current_tipe_rumah'  => $namaTipe !== '' ? $namaTipe : null,
                'has_active_konsumen' => $hasActive,
                'nama_konsumen'       => $namaKonsumen,
                'status_mkdt'         => $statusMkdt,
                'text'                => $text,
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
     * Menampilkan daftar request kavling untuk Admin / MKDT / Planning
     */
    public function list(): ResponseInterface
    {
        if (! in_groups(['1', '4', '6'])) {
            return $this->error('Akses ditolak. Anda tidak memiliki hak akses untuk melihat daftar request kavling.', 403);
        }

        $input    = $this->getRequestData();
        $idProyek = (int) ($input['id_proyek'] ?? 0);
        if ($idProyek <= 0) {
            return $this->error('ID Proyek diperlukan', 400);
        }

        $rawJenis    = isset($input['jenis_request']) ? trim((string) $input['jenis_request']) : '';
        $jenisFilter = in_array($rawJenis, ['tambah_baru', 'ubah_tipe'], true) ? $rawJenis : null;
        if ($rawJenis !== '' && $rawJenis !== 'all' && $jenisFilter === null) {
            return $this->error('Filter jenis request tidak valid.', 422, [
                'jenis_request' => 'Jenis request harus bernilai tambah_baru, ubah_tipe, atau all.',
            ]);
        }

        $rawStatus    = isset($input['status']) ? trim((string) $input['status']) : '';
        $statusFilter = in_array($rawStatus, ['pending', 'approved', 'rejected', 'cancelled', 'history'], true) ? $rawStatus : null;
        if ($rawStatus !== '' && $rawStatus !== 'all' && $statusFilter === null) {
            return $this->error('Filter status tidak valid.', 422, [
                'status' => 'Status harus bernilai pending, approved, rejected, cancelled, history, atau all.',
            ]);
        }

        $hasPagination = array_key_exists('page', $input) || array_key_exists('per_page', $input);
        $page          = max(1, (int) ($input['page'] ?? 1));
        $perPage       = $hasPagination
            ? min(100, max(1, (int) ($input['per_page'] ?? 25)))
            : 500;

        $currentUserId = (int) (function_exists('user_id') ? (user_id() ?? 0) : 0);
        $isAdmin       = in_groups(['1']);
        $isMkdt        = in_groups(['4']);

        try {
            $data   = $this->service->getList($idProyek, $statusFilter, $page, $perPage, $jenisFilter);
            $data   = array_map(static function (array $item) use ($currentUserId, $isAdmin, $isMkdt): array {
                $item['can_cancel'] = ($item['status'] ?? '') === 'pending'
                    && ($isAdmin || ($isMkdt && (int) ($item['created_by'] ?? 0) === $currentUserId));

                return $item;
            }, $data);
            $counts = $this->service->getStatusCounts($idProyek);
            $total  = $this->service->countFiltered($idProyek, $jenisFilter, $statusFilter);

            return $this->respond([
                'success'  => true,
                'messages' => 'Daftar request kavling berhasil diambil',
                'data'     => $data,
                'counts'   => $counts,
                'meta'     => [
                    'page'        => $page,
                    'per_page'    => $perPage,
                    'total'       => $total,
                    'permissions' => [
                        'can_submit'             => in_groups(['1', '4']),
                        'can_process'            => in_groups(['1', '6']),
                        'can_apply_ubah_kavling' => in_groups(['1', '6']),
                        'can_cancel'             => ($isAdmin || $isMkdt),
                    ],
                ],
                'token'    => csrf_hash(),
            ]);
        } catch (Throwable $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * Mengubah status request kavling (pending, approved, atau rejected)
     */
    public function updateStatus(): ResponseInterface
    {
        if (! in_groups(['1', '6'])) {
            return $this->error('Akses ditolak. Hanya tim Planning yang dapat mengubah status request.', 403);
        }

        $input           = $this->getRequestData();
        $idRequest       = (int) ($input['id_request'] ?? 0);
        $status          = trim((string) ($input['status'] ?? ''));
        $catatanPlanning = trim((string) ($input['catatan_planning'] ?? $input['alasan_tolak'] ?? $input['notes'] ?? ''));

        if ($idRequest <= 0 || $status === '') {
            return $this->error('Parameter id_request dan status wajib diisi.', 400);
        }

        if (! in_array($status, ['pending', 'approved', 'rejected'], true)) {
            return $this->error('Status tidak valid.', 422, [
                'status' => 'Status harus bernilai pending, approved, atau rejected.',
            ]);
        }

        if ($status === 'rejected' && $catatanPlanning === '') {
            return $this->error('Catatan/alasan penolakan wajib diisi saat menolak request.', 422, [
                'catatan_planning' => 'Catatan/alasan penolakan wajib diisi.',
            ]);
        }

        try {
            $userId = (int) (function_exists('user_id') ? (user_id() ?? 0) : 0);
            $result = $this->service->updateStatus($idRequest, $status, $userId, $catatanPlanning !== '' ? $catatanPlanning : null);

            return $this->success($result, $result['message']);
        } catch (Throwable $e) {
            $code = str_contains(strtolower($e->getMessage()), 'tidak ditemukan') ? 404 : 400;

            return $this->error($e->getMessage(), $code);
        }
    }

    /**
     * Terapkan perubahan tipe kavling secara langsung dari request ubah_tipe
     */
    public function applyUbahKavling(): ResponseInterface
    {
        $userId = (int) (function_exists('user_id') ? (user_id() ?? 0) : 0);
        if ($userId <= 0) {
            return $this->error('Sesi telah berakhir, silakan login kembali.', 401);
        }

        if (! in_groups(['1', '6'])) {
            return $this->error('Akses ditolak. Hanya tim Planning yang dapat menerapkan perubahan tipe kavling.', 403);
        }

        $input = $this->getRequestData();
        $rules = [
            'id_request'       => 'required|is_natural_no_zero',
            'id_tipe'          => 'required|is_natural_no_zero',
            'catatan_planning' => 'permit_empty|string',
        ];

        if (! $this->validateData($input, $rules)) {
            return $this->error('Validasi gagal', 422, $this->validator->getErrors());
        }

        $catatan = isset($input['catatan_planning']) ? trim((string) $input['catatan_planning']) : null;

        try {
            $result = $this->service->applyUbahTipeKavling(
                (int) $input['id_request'],
                (int) $input['id_tipe'],
                $userId,
                ($catatan !== null && $catatan !== '') ? $catatan : null
            );

            return $this->success($result, $result['message']);
        } catch (Throwable $e) {
            $code = str_contains(strtolower($e->getMessage()), 'tidak ditemukan') ? 404 : 400;

            return $this->error($e->getMessage(), $code);
        }
    }

    /**
     * Batalkan satu atau beberapa request kavling berstatus pending (Admin / MKDT pemilik pengajuan)
     */
    public function cancel(): ResponseInterface
    {
        $userId = (int) (function_exists('user_id') ? (user_id() ?? 0) : 0);
        if ($userId <= 0) {
            return $this->error('Sesi telah berakhir, silakan login kembali.', 401);
        }

        if (! in_groups(['1', '4'])) {
            return $this->error('Akses ditolak. Hanya Admin atau MKDT yang dapat membatalkan request kavling.', 403);
        }

        $input = $this->getRequestData();
        $rules = [
            'id_proyek'    => 'required|is_natural_no_zero',
            'alasan_batal' => 'permit_empty|string|max_length[1000]',
        ];

        $idRequests = [];
        if (array_key_exists('id_requests', $input) && $input['id_requests'] !== null && $input['id_requests'] !== '') {
            if (is_array($input['id_requests'])) {
                foreach ($input['id_requests'] as $rawId) {
                    if (is_scalar($rawId) && (int) $rawId > 0) {
                        $idRequests[] = (int) $rawId;
                    }
                }
            } elseif (is_scalar($input['id_requests'])) {
                foreach (preg_split('/[;,]+/', (string) $input['id_requests']) ?: [] as $rawId) {
                    $parsed = (int) trim($rawId);
                    if ($parsed > 0) {
                        $idRequests[] = $parsed;
                    }
                }
            }
        }
        if (! empty($input['id_request']) && is_scalar($input['id_request']) && (int) $input['id_request'] > 0) {
            $idRequests[] = (int) $input['id_request'];
        }
        $idRequests = array_values(array_unique($idRequests));

        $errors = [];
        if (! $this->validateData($input, $rules)) {
            $errors = $this->validator->getErrors();
        }
        if ($idRequests === []) {
            $errors['id_requests'] = 'Minimal 1 request harus dipilih untuk dibatalkan.';
        }
        if ($errors !== []) {
            return $this->error('Validasi gagal', 422, $errors);
        }

        $alasanBatal = array_key_exists('alasan_batal', $input) && $input['alasan_batal'] !== null
            ? trim((string) $input['alasan_batal'])
            : null;
        if ($alasanBatal === '') {
            $alasanBatal = null;
        }

        try {
            $result = $this->service->cancelRequests(
                (int) $input['id_proyek'],
                $idRequests,
                $userId,
                in_groups(['1']),
                $alasanBatal
            );

            return $this->success($result, $result['message']);
        } catch (Throwable $e) {
            return $this->error($e->getMessage(), 400);
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

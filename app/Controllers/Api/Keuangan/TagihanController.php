<?php

namespace App\Controllers\Api\Keuangan;

use App\Controllers\Api\BaseApiController;
use App\Services\Keuangan\KeuanganService;
use App\Services\Proyek\ActiveProyekService;
use CodeIgniter\HTTP\ResponseInterface;

class TagihanController extends BaseApiController
{
    protected KeuanganService $keuanganService;
    protected ActiveProyekService $activeProyekService;

    public function __construct(
        ?KeuanganService $keuanganService = null,
        ?ActiveProyekService $activeProyekService = null
    ) {
        $this->keuanganService = $keuanganService ?? new KeuanganService();
        $this->activeProyekService = $activeProyekService ?? new ActiveProyekService();
    }

    public function meta(): ResponseInterface
    {
        $canManage = function_exists('in_groups') && in_groups(['1', '3']);
        $activeProyek = $this->activeProyekService->resolveAndGet();

        return $this->success([
            'active_project' => $activeProyek ? [
                'id' => (int) $activeProyek->id_proyek,
                'name' => (string) ($activeProyek->nama_proyek ?? ''),
            ] : null,
            'list_item_keuangan' => $this->keuanganService->getListItemKeuangan(),
            'permissions' => [
                'can_view' => true,
                'can_create_tagihan' => $canManage,
                'can_pay' => $canManage,
                'can_void' => $canManage,
                'can_delete' => $canManage,
            ],
        ], 'Metadata tagihan berhasil dimuat.');
    }

    public function getByID(): ResponseInterface
    {
        $input = $this->readRequestData();
        $idMkdt = trim((string) ($input['id_mkdt'] ?? ''));
        $includeLog = (string) ($input['include_log'] ?? '1') !== '0';

        if ($idMkdt === '') {
            if ($this->isRawFormat()) {
                return $this->error('Data tidak ditemukan', 404);
            }

            return $this->respond([
                'token' => csrf_hash(),
                'success' => false,
                'messages' => 'data tidak ditemukan',
            ]);
        }

        $bundle = $this->keuanganService->getTagihanBundle((int) $idMkdt, $includeLog);

        if ($this->isRawFormat()) {
            return $this->success($bundle, 'Data tagihan berhasil dimuat.');
        }

        return $this->respond(array_merge([
            'token' => csrf_hash(),
            'success' => true,
            'messages' => 'Data tagihan berhasil dimuat.',
        ], $bundle));
    }

    public function getRiwayatByID(): ResponseInterface
    {
        $input = $this->readRequestData();
        $idMkdt = trim((string) ($input['id_mkdt'] ?? ''));

        if ($idMkdt === '') {
            if ($this->isRawFormat()) {
                return $this->error('Data tidak ditemukan', 404);
            }

            return $this->respond([
                'token' => csrf_hash(),
                'success' => false,
                'messages' => 'data tidak ditemukan',
                'log_pembayaran' => [],
            ]);
        }

        $logs = $this->keuanganService->getRiwayatBayarWithDetailById((int) $idMkdt);

        if ($this->isRawFormat()) {
            return $this->success(['log_pembayaran' => $logs], 'Riwayat pembayaran berhasil dimuat.');
        }

        return $this->respond([
            'token' => csrf_hash(),
            'success' => true,
            'log_pembayaran' => $logs,
        ]);
    }

    public function hapusTurunKPR(): ResponseInterface
    {
        $method = strtoupper($this->request->getMethod());
        if (! in_array($method, ['POST', 'DELETE'], true)) {
            return $this->error('Method not allowed', 405);
        }

        $payload = $this->readRequestData();
        $id = isset($payload['id_keuangan']) ? (int) $payload['id_keuangan'] : 0;

        if ($id <= 0) {
            return $this->error('Validasi gagal', 422, ['id_keuangan' => 'ID Keuangan tidak ditemukan']);
        }

        $result = $this->keuanganService->hapusTurunKPR($id);

        if ($this->isRawFormat()) {
            if (! ($result['success'] ?? false)) {
                return $this->error($result['message'] ?? 'Gagal menghapus tagihan Turun KPR.', 422, $result);
            }

            return $this->success($result, $result['message'] ?? 'Berhasil menghapus tagihan Turun KPR.');
        }

        return $this->respond(array_merge(['token' => csrf_hash()], $result));
    }

    public function tambahTurunKPR(): ResponseInterface
    {
        $data = $this->readRequestData();
        $result = $this->keuanganService->tambahTurunKPR($data);

        if ($this->isRawFormat()) {
            if (! ($result['success'] ?? false)) {
                return $this->error($result['messages'] ?? 'Gagal menambahkan tagihan Turun KPR', 422);
            }

            return $this->successCreated($result['data'] ?? null, $result['messages'] ?? 'Tagihan KPR berhasil ditambahkan');
        }

        return $this->respond($result);
    }

    public function hapus(): ResponseInterface
    {
        $input = $this->readRequestData();
        $id = (int) ($input['id_keuangan'] ?? 0);

        if ($id <= 0) {
            if ($this->isRawFormat()) {
                return $this->error('ID Keuangan tidak ditemukan', 422);
            }

            return $this->respond([
                'token' => csrf_hash(),
                'success' => false,
                'message' => 'ID Keuangan tidak ditemukan',
            ]);
        }

        $result = $this->keuanganService->deleteTagihan($id, (int) user_id());

        if ($this->isRawFormat()) {
            return ($result['success'] ?? false)
                ? $this->success(null, $result['message'] ?? 'Tagihan berhasil dihapus')
                : $this->error($result['message'] ?? 'Gagal menghapus tagihan', 422);
        }

        return $this->respond(array_merge(['token' => csrf_hash()], $result));
    }

    public function voidTagihan(): ResponseInterface
    {
        $input = $this->readRequestData();
        $id = (int) ($input['id_keuangan'] ?? 0);
        $reason = (string) ($input['reason'] ?? '');

        if ($id <= 0) {
            if ($this->isRawFormat()) {
                return $this->error('ID Keuangan tidak ditemukan', 422);
            }

            return $this->respond([
                'token' => csrf_hash(),
                'success' => false,
                'message' => 'ID Keuangan tidak ditemukan',
            ]);
        }

        $result = $this->keuanganService->voidTagihan($id, $reason, (int) user_id());

        if ($this->isRawFormat()) {
            return ($result['success'] ?? false)
                ? $this->success(null, $result['message'] ?? 'Tagihan berhasil di-void')
                : $this->error($result['message'] ?? 'Gagal melakukan void tagihan', 422);
        }

        return $this->respond(array_merge(['token' => csrf_hash()], $result));
    }

    public function unvoidTagihan(): ResponseInterface
    {
        $input = $this->readRequestData();
        $id = (int) ($input['id_keuangan'] ?? 0);

        if ($id <= 0) {
            if ($this->isRawFormat()) {
                return $this->error('ID Keuangan tidak ditemukan', 422);
            }

            return $this->respond([
                'token' => csrf_hash(),
                'success' => false,
                'message' => 'ID Keuangan tidak ditemukan',
            ]);
        }

        $result = $this->keuanganService->unvoidTagihan($id, (int) user_id());

        if ($this->isRawFormat()) {
            return ($result['success'] ?? false)
                ? $this->success(null, $result['message'] ?? 'Void tagihan berhasil dibatalkan')
                : $this->error($result['message'] ?? 'Gagal membatalkan void tagihan', 422);
        }

        return $this->respond(array_merge(['token' => csrf_hash()], $result));
    }

    public function save(): ResponseInterface
    {
        $result = $this->keuanganService->saveTagihan($this->request, (int) user_id());

        if ($this->isRawFormat()) {
            if (! ($result['success'] ?? false)) {
                return $this->error($result['messages'] ?? 'Gagal menyimpan data', 422);
            }

            return $this->success([
                'id_mkdt' => $result['id_mkdt'] ?? null,
                'id_konsumen' => $result['id_konsumen'] ?? null,
                'uniq_id' => $result['uniq_id'] ?? null,
            ], $result['messages'] ?? 'Data berhasil disimpan');
        }

        return $this->respond($result);
    }

    public function getListTagihan($status = null): ResponseInterface
    {
        if ($this->isRawFormat()) {
            $result = $this->keuanganService->getListTagihanRaw($this->request, $status);
            return $this->success($result, 'Daftar tagihan berhasil dimuat.');
        }

        // TODO(full-json): Hapus format DataTables HTML ini setelah view CI4 legacy dipensiunkan.
        $output = $this->keuanganService->getListTagihan($this->request, $status);
        return $this->asJsonResponse($output);
    }

    public function getListTagihanGrouped(): ResponseInterface
    {
        if ($this->isRawFormat()) {
            $result = $this->keuanganService->getListTagihanGroupedRaw($this->request);
            return $this->success($result, 'Daftar tagihan terkelompok berhasil dimuat.');
        }

        // TODO(full-json): Hapus format DataTables HTML ini setelah app/Views/keuangan/list-tagihan.php dipensiunkan.
        $output = $this->keuanganService->getListTagihanGrouped($this->request);
        return $this->asJsonResponse($output);
    }

    public function exportExcelTagihan(): ResponseInterface
    {
        $response = $this->keuanganService->exportExcelTagihan($this->request);

        if ($this->isRawFormat()) {
            return $this->success($response, 'Export excel tagihan berhasil.');
        }

        return $this->respond($response);
    }

    public function getListTagihanDetail(): ResponseInterface
    {
        $input = $this->readRequestData();
        $idMkdt = (int) ($input['id_mkdt'] ?? 0);
        $statusLunas = $input['status_lunas'] ?? null;

        if ($idMkdt <= 0) {
            if ($this->isRawFormat()) {
                return $this->error('Data MKDT tidak valid', 422);
            }

            return $this->respond([
                'token' => csrf_hash(),
                'success' => false,
                'message' => 'Data MKDT tidak valid',
                'data' => [],
            ]);
        }

        $items = $this->keuanganService->getListTagihanDetail($idMkdt, $statusLunas);

        if ($this->isRawFormat()) {
            return $this->success([
                'items' => $items,
                'meta' => [
                    'total' => count($items),
                ],
            ], 'Detail tagihan berhasil dimuat.');
        }

        // TODO(full-json): Hapus wrapper legacy non-envelope ini setelah app/Views/keuangan/list-tagihan.php dipensiunkan.
        return $this->respond([
            'token' => csrf_hash(),
            'success' => true,
            'data' => $items,
        ]);
    }

    public function getRiwayatBayar(): ResponseInterface
    {
        if ($this->isRawFormat()) {
            $result = $this->keuanganService->getRiwayatBayarRaw($this->request);
            return $this->success($result, 'Riwayat pembayaran berhasil dimuat.');
        }

        // TODO(full-json): Hapus format DataTables legacy ini setelah app/Views/keuangan/riwayat-bayar.php dipensiunkan.
        $output = $this->keuanganService->getRiwayatBayar($this->request);
        return $this->asJsonResponse($output);
    }

    public function getListRiwayatSurat(): ResponseInterface
    {
        if ($this->isRawFormat()) {
            $result = $this->keuanganService->getListRiwayatSuratRaw($this->request);
            return $this->success($result, 'Riwayat surat tagihan berhasil dimuat.');
        }

        // TODO(full-json): Hapus format DataTables legacy ini setelah app/Views/keuangan/list-tagihan.php dipensiunkan.
        $output = $this->keuanganService->getListRiwayatSurat($this->request);
        return $this->asJsonResponse($output);
    }

    public function getAllJatuhTempo(): ResponseInterface
    {
        $idProyek = resolve_active_proyek_id($this->request->getVar('id_proyek'));
        $rows = $this->keuanganService->getAllJatuhTempo($idProyek);

        if ($this->isRawFormat()) {
            return $this->success([
                'items' => $rows,
                'meta' => [
                    'total' => is_array($rows) ? count($rows) : 0,
                ],
            ], 'Data jatuh tempo berhasil dimuat.');
        }

        return $this->respond($rows);
    }

    protected function asJsonResponse(mixed $output): ResponseInterface
    {
        if ($output instanceof ResponseInterface) {
            return $output;
        }

        if (is_string($output)) {
            return $this->response->setContentType('application/json')->setBody($output);
        }

        return $this->respond($output);
    }
}

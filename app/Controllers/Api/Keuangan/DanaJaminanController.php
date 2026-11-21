<?php

namespace App\Controllers\Api\Keuangan;

use App\Controllers\Api\BaseApiController;
use App\Services\Keuangan\DanaJaminanService;
use App\Services\Proyek\ActiveProyekService;
use CodeIgniter\HTTP\ResponseInterface;

class DanaJaminanController extends BaseApiController
{
    protected DanaJaminanService $danaJaminanService;
    protected ActiveProyekService $activeProyekService;

    public function __construct(
        ?DanaJaminanService $danaJaminanService = null,
        ?ActiveProyekService $activeProyekService = null
    ) {
        $this->danaJaminanService = $danaJaminanService ?? new DanaJaminanService();
        $this->activeProyekService = $activeProyekService ?? new ActiveProyekService();
    }

    public function meta(): ResponseInterface
    {
        $canManage = ! function_exists('in_groups') || in_groups(['1', '3']);
        $activeProyek = $this->activeProyekService->resolveAndGet();
        $meta = $this->danaJaminanService->getMetaPayload();

        return $this->success([
            'active_project' => $activeProyek ? [
                'id' => (int) $activeProyek->id_proyek,
                'name' => (string) ($activeProyek->nama_proyek ?? ''),
            ] : null,
            'list_dajam' => $meta['list_dajam'] ?? [],
            'statuses' => $meta['statuses'] ?? [],
            'permissions' => [
                'can_view' => true,
                'can_manage' => $canManage,
                'can_cairkan' => $canManage,
            ],
        ], 'Metadata dana jaminan berhasil dimuat.');
    }

    public function index(): ResponseInterface
    {
        $meta = $this->danaJaminanService->getMetaPayload();

        if ($this->isRawFormat()) {
            return $this->success($meta, 'Endpoint dana jaminan aktif.');
        }

        // TODO(full-json): Hapus wrapper legacy non-envelope ini setelah modul PencairanJaminan dipensiunkan.
        return $this->respond([
            'token' => csrf_hash(),
            'success' => true,
            'message' => 'Endpoint dana jaminan aktif.',
            'data' => $meta,
        ]);
    }

    public function list($id_kavling = null): ResponseInterface
    {
        $result = $this->danaJaminanService->listPengajuan((int) $id_kavling);

        return $this->respondDual($result, 'Daftar pengajuan dana jaminan berhasil dimuat.', 422);
    }

    public function store(): ResponseInterface
    {
        $result = $this->danaJaminanService->storePengajuan($this->request, (int) user_id());

        return $this->respondDual($result, 'Pengajuan dana jaminan berhasil disimpan.', 422);
    }

    public function cairkan($id_pengajuan = null): ResponseInterface
    {
        $result = $this->danaJaminanService->cairkanPengajuan((int) $id_pengajuan, $this->request, (int) user_id());

        return $this->respondDual($result, 'Pencairan dana jaminan berhasil disimpan.', 422);
    }

    public function history($id_kavling = null): ResponseInterface
    {
        $result = $this->danaJaminanService->getHistory((int) $id_kavling);

        return $this->respondDual($result, 'Riwayat dana jaminan berhasil dimuat.', 422);
    }

    public function toggleStatus($id_pengajuan = null): ResponseInterface
    {
        $result = $this->danaJaminanService->rejectLegacyToggle();

        return $this->respondDual($result, 'Status diperbarui.', 422);
    }

    public function download($id_pengajuan = null): ResponseInterface
    {
        $result = $this->danaJaminanService->getDownloadFile((int) $id_pengajuan);

        if ($this->isRawFormat()) {
            if (! ($result['success'] ?? false)) {
                return $this->error($result['message'] ?? 'Lampiran tidak ditemukan', 404);
            }

            return $this->success([
                'id' => (int) ($result['id'] ?? $id_pengajuan),
                'file_path' => $result['file_path'] ?? null,
                'access_url' => $result['access_url'] ?? null,
                'download_url' => $result['signed_download_url'] ?? ($result['download_url'] ?? null),
            ], 'Lampiran ditemukan.');
        }

        if (! ($result['success'] ?? false)) {
            return $this->response->setBody('Lampiran tidak ditemukan')->setStatusCode(404);
        }

        // TODO(full-json): Hapus redirect browser legacy ini setelah rute /pencairan/download/* dipensiunkan.
        return redirect()->to((string) $result['download_url']);
    }

    public function getByID(): ResponseInterface
    {
        $input = $this->readRequestData();
        $result = $this->danaJaminanService->getDanaAkadDetail(
            (int) ($input['id_mkdt'] ?? 0),
            (int) ($input['id_kavling'] ?? 0)
        );

        return $this->respondDual($result, 'Data dana akad berhasil dimuat.', 404);
    }

    public function getDanaAkad(): ResponseInterface
    {
        $input = $this->readRequestData();
        $result = $this->danaJaminanService->getData(
            (int) ($input['id_mkdt'] ?? 0),
            (int) ($input['id_kavling'] ?? 0)
        );

        return $this->respondDual($result, 'Data dana jaminan berhasil dimuat.', 404);
    }

    public function saveDanaAkad(): ResponseInterface
    {
        $result = $this->danaJaminanService->saveDanaAkad($this->request, (int) user_id());

        return $this->respondDual($result, 'Data dana jaminan berhasil disimpan.', 422);
    }

    protected function respondDual(array $result, string $defaultSuccessMessage, int $errorCode = 422): ResponseInterface
    {
        if ($this->isRawFormat()) {
            $message = (string) ($result['message'] ?? $result['messages'] ?? $defaultSuccessMessage);
            if (! ($result['success'] ?? false)) {
                return $this->error($message, $errorCode, $result['errors'] ?? null);
            }

            $payload = $result;
            unset($payload['token'], $payload['success'], $payload['message'], $payload['messages']);

            if (count($payload) === 1 && array_key_exists('data', $payload)) {
                $payload = $payload['data'];
            }

            return $this->success($payload === [] ? null : $payload, $message);
        }

        // TODO(full-json): Hapus wrapper legacy non-envelope ini setelah siteplan/keuangan.js dipensiunkan.
        return $this->respond($result);
    }
}

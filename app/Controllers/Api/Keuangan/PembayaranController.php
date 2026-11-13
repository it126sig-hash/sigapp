<?php

namespace App\Controllers\Api\Keuangan;

use App\Controllers\Api\BaseApiController;
use App\Services\Keuangan\KeuanganService;
use App\Services\Keuangan\PembayaranService;
use App\Services\Proyek\ActiveProyekService;
use CodeIgniter\HTTP\ResponseInterface;

class PembayaranController extends BaseApiController
{
    protected PembayaranService $pembayaranService;
    protected KeuanganService $keuanganService;
    protected ActiveProyekService $activeProyekService;

    public function __construct(
        ?PembayaranService $pembayaranService = null,
        ?KeuanganService $keuanganService = null,
        ?ActiveProyekService $activeProyekService = null
    ) {
        $this->pembayaranService = $pembayaranService ?? new PembayaranService();
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
                'can_pay' => $canManage,
                'can_delete' => $canManage,
                'can_recalculate' => $canManage,
            ],
        ], 'Metadata pembayaran berhasil dimuat.');
    }

    public function save(): ResponseInterface
    {
        $input = $this->readRequestData();
        $result = $this->pembayaranService->simpan($input);

        if ($this->isRawFormat()) {
            if (! ($result['status'] ?? false)) {
                return $this->error($result['message'] ?? 'Gagal menambahkan pembayaran', 422);
            }

            return $this->success($result['data'] ?? null, $result['message'] ?? 'Pembayaran berhasil');
        }

        return $this->respond($result);
    }

    public function removeLP(): ResponseInterface
    {
        $input = $this->readRequestData();
        $result = $this->pembayaranService->removeLP($input);

        if ($this->isRawFormat()) {
            if (! ($result['success'] ?? false)) {
                return $this->error($result['messages'] ?? 'Gagal menghapus data', 422);
            }

            return $this->success(null, $result['messages'] ?? 'Berhasil menghapus data');
        }

        return $this->respond($result);
    }

    public function recalculateSummary(): ResponseInterface
    {
        $processedCount = $this->pembayaranService->recalculateAllSummaries();

        if ($this->isRawFormat()) {
            return $this->success([
                'processed_count' => $processedCount,
            ], 'Recalculate summary pembayaran berhasil.');
        }

        return $this->respond([
            'success' => true,
            'messages' => 'Recalculate summary pembayaran berhasil.',
            'processed_count' => $processedCount,
            'token' => csrf_hash(),
        ]);
    }
}

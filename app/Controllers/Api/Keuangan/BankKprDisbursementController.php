<?php

namespace App\Controllers\Api\Keuangan;

use App\Controllers\Api\BaseApiController;
use App\Services\Keuangan\BankKprDisbursementService;
use App\Services\Keuangan\KeuanganService;
use App\Services\Proyek\ActiveProyekService;
use CodeIgniter\HTTP\ResponseInterface;

class BankKprDisbursementController extends BaseApiController
{
    protected BankKprDisbursementService $bankKprDisbursementService;
    protected KeuanganService $keuanganService;
    protected ActiveProyekService $activeProyekService;

    public function __construct(
        ?BankKprDisbursementService $bankKprDisbursementService = null,
        ?KeuanganService $keuanganService = null,
        ?ActiveProyekService $activeProyekService = null
    ) {
        $this->bankKprDisbursementService = $bankKprDisbursementService ?? new BankKprDisbursementService();
        $this->keuanganService = $keuanganService ?? new KeuanganService();
        $this->activeProyekService = $activeProyekService ?? new ActiveProyekService();
    }

    public function meta(): ResponseInterface
    {
        $activeProyek = $this->activeProyekService->resolveAndGet();

        return $this->success([
            'active_project' => $activeProyek ? [
                'id' => (int) $activeProyek->id_proyek,
                'name' => (string) ($activeProyek->nama_proyek ?? ''),
            ] : null,
            'banks' => $this->bankKprDisbursementService->getBanksList(),
            'statuses' => ['draft', 'cair', 'void'],
            'permissions' => [
                'can_view' => true,
                'can_manage' => $this->bankKprDisbursementService->canUserManage(),
            ],
        ], 'Metadata pencairan bank berhasil dimuat.');
    }

    public function getJatuhTempo(): ResponseInterface
    {
        $input = $this->readRequestData();

        if ($this->isRawFormat()) {
            $idProyek = resolve_active_proyek_id($input['id_proyek'] ?? null);
            $rows = $this->keuanganService->getJatuhTempoByProyek($idProyek);

            return $this->success([
                'items' => $rows,
                'meta' => [
                    'total' => count($rows),
                ],
            ], 'Data jatuh tempo berhasil dimuat.');
        }

        $idKavling = $input['list_jatuhtempo'] ?? null;
        if (! $idKavling) {
            return $this->respond([]);
        }

        $idProyek = isset($input['id_proyek']) && $input['id_proyek'] !== '' ? (int) $input['id_proyek'] : null;
        $rows = $this->keuanganService->getJatuhTempoByProyek($idProyek);

        return $this->respond($rows);
    }

    public function getData(): ResponseInterface
    {
        $input = $this->readRequestData();
        $result = $this->bankKprDisbursementService->getData(
            (int) ($input['id_mkdt'] ?? 0),
            (int) ($input['id_kavling'] ?? 0)
        );

        if ($this->isRawFormat()) {
            if (! ($result['success'] ?? false)) {
                return $this->error($result['messages'] ?? 'Data akad kavling tidak ditemukan', 404, $result);
            }

            return $this->success($result, $result['messages'] ?? 'Data pencairan bank berhasil dimuat.');
        }

        return $this->respond($result);
    }

    public function save(): ResponseInterface
    {
        $result = $this->bankKprDisbursementService->save($this->request, (int) user_id());

        if ($this->isRawFormat()) {
            if (! ($result['success'] ?? false)) {
                return $this->error($result['messages'] ?? 'Gagal menyimpan pencairan bank', 422);
            }

            return $this->success($result, $result['messages'] ?? 'Pencairan bank berhasil disimpan.');
        }

        return $this->respond($result);
    }

    public function void($id = null): ResponseInterface
    {
        $result = $this->bankKprDisbursementService->void((int) $id, $this->request, (int) user_id());

        if ($this->isRawFormat()) {
            if (! ($result['success'] ?? false)) {
                return $this->error($result['messages'] ?? 'Gagal void pencairan bank', 422);
            }

            return $this->success($result, $result['messages'] ?? 'Pencairan bank berhasil di-void.');
        }

        return $this->respond($result);
    }
}

<?php

namespace App\Controllers\Api\Keuangan;

use App\Controllers\Api\BaseApiController;
use App\Services\Keuangan\PencairanAkadService;
use App\Services\Proyek\ActiveProyekService;
use CodeIgniter\HTTP\ResponseInterface;

class PencairanAkadController extends BaseApiController
{
    protected PencairanAkadService $pencairanAkadService;
    protected ActiveProyekService $activeProyekService;

    public function __construct(
        ?PencairanAkadService $pencairanAkadService = null,
        ?ActiveProyekService $activeProyekService = null
    ) {
        $this->pencairanAkadService = $pencairanAkadService ?? new PencairanAkadService();
        $this->activeProyekService = $activeProyekService ?? new ActiveProyekService();
    }

    public function meta(): ResponseInterface
    {
        $canManage = ! function_exists('in_groups') || in_groups(['1', '3']);
        $activeProyek = $this->activeProyekService->resolveAndGet();
        $meta = $this->pencairanAkadService->getMetaPayload();

        return $this->success([
            'active_project' => $activeProyek ? [
                'id' => (int) $activeProyek->id_proyek,
                'name' => (string) ($activeProyek->nama_proyek ?? ''),
            ] : null,
            'list_dajam' => $meta['list_dajam'] ?? [],
            'template_columns' => $meta['template_columns'] ?? [],
            'statuses' => $meta['statuses'] ?? [],
            'permissions' => [
                'can_view' => true,
                'can_manage' => $canManage,
                'can_import' => $canManage,
                'can_export' => true,
                'can_cairkan' => $canManage,
                'can_void' => $canManage,
            ],
        ], 'Metadata pencairan akad berhasil dimuat.');
    }

    public function get(): ResponseInterface
    {
        $input = $this->readRequestData();
        $result = $this->pencairanAkadService->getData(
            (int) ($input['id_mkdt'] ?? 0),
            (int) ($input['id_kavling'] ?? 0)
        );

        return $this->respondDual($result, 'Data pencairan akad berhasil dimuat.', 404);
    }

    public function saveRetensi(): ResponseInterface
    {
        if ($denied = $this->checkKeuanganAccess()) {
            return $denied;
        }

        $result = $this->pencairanAkadService->saveRetensi($this->readRequestData(), (int) user_id());

        return $this->respondDual($result, 'Retensi berhasil disimpan.', 422);
    }

    public function saveTenor(): ResponseInterface
    {
        if ($denied = $this->checkKeuanganAccess()) {
            return $denied;
        }

        $result = $this->pencairanAkadService->saveTenor($this->readRequestData(), (int) user_id());

        return $this->respondDual($result, 'Tenor berhasil disimpan.', 422);
    }

    public function storePengajuan(): ResponseInterface
    {
        if ($denied = $this->checkKeuanganAccess()) {
            return $denied;
        }

        $result = $this->pencairanAkadService->storePengajuan($this->request, (int) user_id());

        return $this->respondDual($result, 'Pengajuan berhasil disimpan.', 422);
    }

    public function cairkan(): ResponseInterface
    {
        if ($denied = $this->checkKeuanganAccess()) {
            return $denied;
        }

        $result = $this->pencairanAkadService->cairkan($this->request, (int) user_id());

        return $this->respondDual($result, 'Pencairan berhasil disimpan.', 422);
    }

    public function listPayment(): ResponseInterface
    {
        $input = $this->readRequestData();
        $result = $this->pencairanAkadService->listPayment((int) ($input['id_pengajuan'] ?? 0));

        return $this->respondDual($result, 'Daftar pembayaran pencairan akad berhasil dimuat.', 422);
    }

    public function voidPayment(): ResponseInterface
    {
        if ($denied = $this->checkKeuanganAccess()) {
            return $denied;
        }

        $input = $this->readRequestData();
        $result = $this->pencairanAkadService->voidPayment(
            (int) ($input['id_payment'] ?? 0),
            (string) ($input['reason'] ?? ''),
            (int) user_id()
        );

        return $this->respondDual($result, 'Pencairan berhasil dibatalkan.', 422);
    }

    public function void(): ResponseInterface
    {
        if ($denied = $this->checkKeuanganAccess()) {
            return $denied;
        }

        $input = $this->readRequestData();
        $result = $this->pencairanAkadService->void(
            (int) ($input['id_pengajuan'] ?? 0),
            (string) ($input['reason'] ?? ''),
            (int) user_id()
        );

        return $this->respondDual($result, 'Pengajuan berhasil dibatalkan.', 422);
    }

    public function history($id_kavling = null): ResponseInterface
    {
        $result = $this->pencairanAkadService->getHistory((int) $id_kavling);

        return $this->respondDual($result, 'Riwayat pencairan akad berhasil dimuat.', 422);
    }

    public function getListGrouped(): ResponseInterface
    {
        if ($this->isRawFormat()) {
            $result = $this->pencairanAkadService->getListGroupedRaw($this->request);

            return $this->success($result, 'Daftar hasil akad berhasil dimuat.');
        }

        // TODO(full-json): Hapus format DataTables HTML ini setelah app/Views/keuangan/list-hasil-akad.php dipensiunkan.
        $output = $this->pencairanAkadService->getListGrouped($this->request);

        return $this->asJsonResponse($output);
    }

    public function getListDetail(): ResponseInterface
    {
        $input = $this->readRequestData();
        $result = $this->pencairanAkadService->getListDetail((int) ($input['id_mkdt'] ?? 0));

        return $this->respondDual($result, 'Detail hasil akad berhasil dimuat.', 422);
    }

    public function import(): ResponseInterface
    {
        if ($denied = $this->checkKeuanganAccess()) {
            return $denied;
        }

        $file = $this->request->getFile('csv_file');
        if (! $file || ! $file->isValid()) {
            if ($this->isRawFormat()) {
                return $this->error('File CSV tidak valid', 422);
            }

            // TODO(full-json): Hapus wrapper legacy non-envelope ini setelah app/Views/keuangan/pencairan-akad-import.php dipensiunkan.
            return $this->respond(['token' => csrf_hash(), 'success' => false, 'message' => 'File CSV tidak valid']);
        }

        $result = $this->pencairanAkadService->importTanggalCair(
            $file->getTempName(),
            $this->request->getFile('lampiran_surat'),
            (int) user_id()
        );

        return $this->respondDual($result, 'Import tanggal cair berhasil diproses.', 422);
    }

    public function postBatchList(): ResponseInterface
    {
        if ($this->isRawFormat()) {
            $result = $this->pencairanAkadService->getBatchListRaw($this->request);

            return $this->success($result, 'Daftar batch pencairan akad berhasil dimuat.');
        }

        // TODO(full-json): Hapus format DataTables HTML ini setelah app/Views/keuangan/list-hasil-akad.php dipensiunkan.
        $output = $this->pencairanAkadService->getBatchList($this->request);

        return $this->asJsonResponse($output);
    }

    public function postEligibleKavling(): ResponseInterface
    {
        $input = $this->readRequestData();
        $result = $this->pencairanAkadService->getEligibleBatchKavling(
            (array) ($input['id_kavlings'] ?? []),
            (int) ($input['id_proyek'] ?? 0)
        );

        return $this->respondDual($result, 'Daftar kavling eligible batch berhasil dimuat.', 422);
    }

    public function postStoreBatchPengajuan(): ResponseInterface
    {
        if ($denied = $this->checkKeuanganAccess()) {
            return $denied;
        }

        $result = $this->pencairanAkadService->storeBatchPengajuan(
            $this->readRequestData(),
            $this->request->getFile('lampiran_surat'),
            (int) user_id()
        );

        return $this->respondDual($result, 'Pengajuan batch berhasil disimpan.', 422);
    }

    public function postBatchDetail(): ResponseInterface
    {
        $input = $this->readRequestData();
        $result = $this->pencairanAkadService->getBatchDetail((int) ($input['id_batch'] ?? 0));

        return $this->respondDual($result, 'Detail batch pencairan akad berhasil dimuat.', 404);
    }

    public function postCairkanBatch(): ResponseInterface
    {
        if ($denied = $this->checkKeuanganAccess()) {
            return $denied;
        }

        $result = $this->pencairanAkadService->cairkanBatch($this->readRequestData(), (int) user_id());

        return $this->respondDual($result, 'Pencairan batch berhasil disimpan.', 422);
    }

    public function postVoidBatch(): ResponseInterface
    {
        if ($denied = $this->checkKeuanganAccess()) {
            return $denied;
        }

        $input = $this->readRequestData();
        $result = $this->pencairanAkadService->voidBatch(
            (int) ($input['id_batch'] ?? 0),
            (string) ($input['reason'] ?? ''),
            (int) user_id()
        );

        return $this->respondDual($result, 'Batch pengajuan berhasil dibatalkan.', 422);
    }

    public function getBatchHistory($id = null): ResponseInterface
    {
        $result = $this->pencairanAkadService->getBatchHistory((int) $id);

        return $this->respondDual($result, 'Riwayat batch pencairan akad berhasil dimuat.', 422);
    }

    protected function checkKeuanganAccess(): ?ResponseInterface
    {
        if (function_exists('in_groups') && ! in_groups(['1', '3'])) {
            $message = 'Akses ditolak: Hanya user Keuangan atau Administrator yang dapat memproses atau memperbarui pencairan dana.';
            if ($this->isRawFormat()) {
                return $this->error($message, 403);
            }

            return $this->response->setStatusCode(403)->setJSON([
                'token' => csrf_hash(),
                'success' => false,
                'message' => $message,
            ]);
        }

        return null;
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

        // TODO(full-json): Hapus wrapper legacy non-envelope ini setelah view/JS pencairan-akad dipensiunkan.
        return $this->respond($result);
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

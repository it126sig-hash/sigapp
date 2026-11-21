<?php

namespace App\Controllers\Api\Keuangan\Cashout;

use App\Controllers\Api\BaseApiController;
use App\Services\Keuangan\Cashout\CashoutSubkonService;
use App\Services\Proyek\ActiveProyekService;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class CashoutSubkonController extends BaseApiController
{
    protected CashoutSubkonService $cashoutSubkonService;
    protected ActiveProyekService $activeProyekService;

    public function __construct(
        ?CashoutSubkonService $cashoutSubkonService = null,
        ?ActiveProyekService $activeProyekService = null
    ) {
        if ($cashoutSubkonService !== null) {
            $this->cashoutSubkonService = $cashoutSubkonService;
        }
        if ($activeProyekService !== null) {
            $this->activeProyekService = $activeProyekService;
        }
    }

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        helper(['auth', 'active_proyek']);
        $this->cashoutSubkonService ??= new CashoutSubkonService();
        $this->activeProyekService ??= new ActiveProyekService();
    }

    public function meta(): ResponseInterface
    {
        $activeProyek = $this->activeProyekService->resolveAndGet();
        $meta = $this->cashoutSubkonService->getMetaPayload();

        return $this->success(array_merge([
            'active_project' => $activeProyek ? [
                'id' => (int) $activeProyek->id_proyek,
                'name' => (string) ($activeProyek->nama_proyek ?? ''),
            ] : null,
        ], $meta), 'Metadata cashout subkon berhasil dimuat.');
    }

    public function getDataTables(): ResponseInterface
    {
        $input = $this->readRequestData();
        if (empty($input['id_proyek'])) {
            $input['id_proyek'] = $this->activeProyekService->getActiveId();
        }

        if ($this->isRawFormat()) {
            return $this->success(
                $this->cashoutSubkonService->getDataTablesRaw($input),
                'Daftar cashout subkon berhasil dimuat.'
            );
        }

        $payload = $this->cashoutSubkonService->getDataTables($input);
        $payload['token'] = $payload['token'] ?? csrf_hash();

        return $this->respond($payload);
    }

    public function getDetailList(): ResponseInterface
    {
        $input = $this->readRequestData();
        $result = $this->cashoutSubkonService->getDetailList((int) ($input['id_cashout_subkon'] ?? 0));

        return $this->respondSubkonDual($result, 'Detail termin cashout subkon berhasil dimuat.');
    }

    public function get(): ResponseInterface
    {
        $input = $this->readRequestData();
        $idKavlings = isset($input['id_kavlings']) ? (array) $input['id_kavlings'] : [];
        $idCashoutSubkon = ! empty($input['id_cashout_subkon']) ? (int) $input['id_cashout_subkon'] : null;

        $result = $this->cashoutSubkonService->getCashoutSubkon($idKavlings, $idCashoutSubkon);

        return $this->respondSubkonDual($result, 'Data cashout subkon berhasil dimuat.');
    }

    public function save(): ResponseInterface
    {
        $result = $this->cashoutSubkonService->saveCashoutSubkon($this->request);

        return $this->respondSubkonDual($result, 'Data berhasil disimpan.');
    }

    public function turunJatuhTempo(): ResponseInterface
    {
        $input = $this->readRequestData();
        $result = $this->cashoutSubkonService->turunJatuhTempo(
            $input['id_cashout_subkon_detail'] ?? null,
            $input['tanggal_jatuh_tempo'] ?? null,
            $input['berita_acara'] ?? null
        );

        return $this->respondSubkonDual($result, 'Tanggal Jatuh Tempo berhasil disimpan.');
    }

    public function ajukanSPP(): ResponseInterface
    {
        $input = $this->readRequestData();
        $result = $this->cashoutSubkonService->ajukanSPP(
            $input['id_cashout_subkon_detail'] ?? null,
            $input['spp_no'] ?? null,
            $input['spp_tgl'] ?? null
        );

        return $this->respondSubkonDual($result, 'Pengajuan SPP berhasil disimpan.');
    }

    public function ajukanPencairan(): ResponseInterface
    {
        $input = $this->readRequestData();
        $result = $this->cashoutSubkonService->ajukanPencairan(
            $input['id_cashout_subkon_detail'] ?? null,
            $input['spp_no'] ?? null,
            $input['spp_tgl'] ?? null,
            $input['pencairan_tgl'] ?? null
        );

        return $this->respondSubkonDual($result, 'Pengajuan Pencairan berhasil disimpan.');
    }

    public function pembayaran(): ResponseInterface
    {
        $input = $this->readRequestData();
        $result = $this->cashoutSubkonService->pembayaran(
            $input['id_cashout_subkon_detail'] ?? null,
            $input['cek_no'] ?? null,
            $input['cek_tgl'] ?? null
        );

        return $this->respondSubkonDual($result, 'Pembayaran berhasil disimpan.');
    }

    public function getHistory(): ResponseInterface
    {
        $input = $this->readRequestData();
        $result = $this->cashoutSubkonService->getHistory($input['id_cashout_subkon'] ?? null);

        return $this->respondSubkonDual($result, 'Riwayat cashout subkon berhasil dimuat.');
    }

    protected function respondSubkonDual(array $result, string $defaultSuccessMessage, int $errorCode = 422): ResponseInterface
    {
        if ($this->isRawFormat()) {
            $status = (string) ($result['status'] ?? 'error');
            $message = (string) ($result['message'] ?? $defaultSuccessMessage);
            if ($status !== 'success') {
                return $this->error($message, $errorCode, $result['errors'] ?? null);
            }

            $payload = $result;
            unset($payload['status'], $payload['message'], $payload['token']);

            if (count($payload) === 1 && array_key_exists('data', $payload)) {
                $payload = $payload['data'];
            }

            return $this->success($payload === [] ? null : $payload, $message);
        }

        $result['token'] = $result['token'] ?? csrf_hash();

        return $this->respond($result);
    }
}

<?php

namespace App\Controllers\Api\Keuangan;

use App\Controllers\Api\BaseApiController;
use App\Services\Keuangan\DireksiService;
use CodeIgniter\HTTP\ResponseInterface;

class DireksiController extends BaseApiController
{
    protected DireksiService $direksiService;

    public function __construct(?DireksiService $direksiService = null)
    {
        $this->direksiService = $direksiService ?? new DireksiService();
    }

    public function meta(): ResponseInterface
    {
        $activeProjectId = $this->request->getGet('id_proyek');
        $projectId = ($activeProjectId !== null && $activeProjectId !== '') ? (int) $activeProjectId : null;

        return $this->success(
            $this->direksiService->getMetaPayload($projectId),
            'Metadata direksi berhasil dimuat.'
        );
    }

    public function get_data_by_id($st = null): ResponseInterface
    {
        $input = $this->readRequestData();
        $idKavling = (int) ($input['id_kavling'] ?? 0);
        $actorId = function_exists('user_id') && user_id() ? (int) user_id() : null;

        $result = $this->direksiService->getDiskresiData($idKavling, $actorId);

        if ($this->isRawFormat()) {
            if (empty($result['data'])) {
                return $this->error('Data diskresi kavling tidak ditemukan.', 404);
            }

            return $this->success($result, 'Data diskresi kavling berhasil dimuat.');
        }

        return $this->respond([
            'token'       => csrf_hash(),
            'data'        => $result['data'],
            'harga_akhir' => $result['harga_akhir'],
        ]);
    }

    public function save(): ResponseInterface
    {
        $input = $this->readRequestData();
        $idKavling = (int) ($input['id_kavling'] ?? 0);
        $actorId = (int) (function_exists('user_id') ? (user_id() ?? 0) : 0);

        $result = $this->direksiService->saveDiskresi($idKavling, $input, $actorId);

        if ($this->isRawFormat()) {
            if (! ($result['success'] ?? false)) {
                return $this->error($result['messages'] ?? 'Gagal menyimpan diskresi.', 422);
            }

            return $this->success($result['data'] ?? null, $result['messages'] ?? 'Berhasil melakukan perubahan data');
        }

        return $this->respond([
            'token'    => csrf_hash(),
            'success'  => (bool) ($result['success'] ?? false),
            'messages' => $result['messages'] ?? '',
        ]);
    }

    public function get_pending_tagihan(): ResponseInterface
    {
        $list = $this->direksiService->getPendingTagihan();

        if ($this->isRawFormat()) {
            return $this->success([
                'items' => $list,
                'meta'  => ['total' => count($list)],
            ], 'Daftar surat tagihan menunggu persetujuan berhasil dimuat.');
        }

        return $this->respond([
            'data' => $list,
        ]);
    }

    public function get_my_signature(): ResponseInterface
    {
        $userId = (int) (function_exists('user_id') ? (user_id() ?? 0) : 0);
        $profile = $this->direksiService->getMySignature($userId);

        if ($this->isRawFormat()) {
            return $this->success($profile, 'Profil tanda tangan berhasil dimuat.');
        }

        return $this->respond([
            'success' => true,
            'data'    => $profile,
            'token'   => csrf_hash(),
        ]);
    }

    public function sign_tagihan(): ResponseInterface
    {
        $input = $this->readRequestData();
        $noInv = (string) ($input['no_inv'] ?? '');
        $password = (string) ($input['password'] ?? '');
        $signatureMethod = (string) ($input['signature_method'] ?? 'profile');
        $signatureData = (string) ($input['signature_data'] ?? '');
        $userId = (int) (function_exists('user_id') ? (user_id() ?? 0) : 0);

        $result = $this->direksiService->signTagihan($noInv, $password, $signatureMethod, $signatureData, $userId);

        if ($this->isRawFormat()) {
            if (! ($result['success'] ?? false)) {
                return $this->error($result['message'] ?? 'Gagal menandatangani surat.', 422);
            }

            return $this->success($result['data'] ?? null, $result['message'] ?? 'Surat tagihan berhasil ditandatangani.');
        }

        return $this->respond([
            'success' => (bool) ($result['success'] ?? false),
            'message' => $result['message'] ?? '',
            'token'   => csrf_hash(),
        ]);
    }

    public function get_history_tagihan(): ResponseInterface
    {
        $list = $this->direksiService->getHistoryTagihan();

        if ($this->isRawFormat()) {
            return $this->success([
                'items' => $list,
                'meta'  => ['total' => count($list)],
            ], 'Riwayat persetujuan surat tagihan berhasil dimuat.');
        }

        return $this->respond([
            'data' => $list,
        ]);
    }

    public function reject_tagihan(): ResponseInterface
    {
        $input = $this->readRequestData();
        $noInv = (string) ($input['no_inv'] ?? '');
        $alasan = (string) ($input['alasan'] ?? '');
        $password = (string) ($input['password'] ?? '');
        $userId = (int) (function_exists('user_id') ? (user_id() ?? 0) : 0);

        $result = $this->direksiService->rejectTagihan($noInv, $alasan, $password, $userId);

        if ($this->isRawFormat()) {
            if (! ($result['success'] ?? false)) {
                return $this->error($result['message'] ?? 'Gagal menolak surat tagihan.', 422);
            }

            return $this->success($result['data'] ?? null, $result['message'] ?? 'Surat tagihan berhasil ditolak.');
        }

        return $this->respond([
            'success' => (bool) ($result['success'] ?? false),
            'message' => $result['message'] ?? '',
            'token'   => csrf_hash(),
        ]);
    }
}

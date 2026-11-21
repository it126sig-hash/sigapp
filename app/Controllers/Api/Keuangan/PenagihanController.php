<?php

namespace App\Controllers\Api\Keuangan;

use App\Controllers\Api\BaseApiController;
use App\Services\Keuangan\PenagihanService;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class PenagihanController extends BaseApiController
{
    protected PenagihanService $penagihanService;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->penagihanService = new PenagihanService();
    }

    public function meta()
    {
        $activeProjectId = $this->request->getGet('id_proyek');
        $payload = $this->penagihanService->getMetaPayload(
            $activeProjectId !== null && $activeProjectId !== '' ? (int) $activeProjectId : null
        );

        return $this->success($payload, 'Metadata penagihan berhasil dimuat.');
    }

    public function isi_tagihan()
    {
        $actorId = function_exists('user_id') ? user_id() : null;
        $result = $this->penagihanService->isiTagihan($this->request, $actorId !== null ? (int) $actorId : null);

        if ($this->isRawFormat()) {
            if (! empty($result['success'])) {
                return $this->success($result, (string) ($result['messages'] ?? 'Berhasil menyimpan isi tagihan.'));
            }
            return $this->error((string) ($result['messages'] ?? 'Gagal menyimpan isi tagihan.'), 422, [], $result);
        }

        return $this->response->setJSON($result);
    }

    public function save_sb()
    {
        $result = $this->penagihanService->saveSudahDibayar($this->request);

        if ($this->isRawFormat()) {
            if (! empty($result['success'])) {
                return $this->success($result, (string) ($result['messages'] ?? 'Berhasil memperbarui status pembayaran.'));
            }
            return $this->error((string) ($result['messages'] ?? 'Gagal memperbarui status pembayaran.'), 422, [], $result);
        }

        return $this->response->setJSON($result);
    }

    public function save()
    {
        $input = $this->readRequestData();
        $actorId = function_exists('user_id') ? user_id() : null;
        $result = $this->penagihanService->saveLegacyPayment($input, $actorId !== null ? (int) $actorId : null);

        if ($this->isRawFormat()) {
            if (! empty($result['success'])) {
                return $this->success($result, (string) ($result['messages'] ?? 'Pembayaran berhasil disimpan.'));
            }
            return $this->error((string) ($result['messages'] ?? 'Gagal menyimpan pembayaran.'), 422, [], $result);
        }

        return $this->response->setJSON($result);
    }

    public function get_tagihan($id = null)
    {
        $input = $this->readRequestData();
        $idMkdt = (int) ($input['id_mkdt'] ?? $this->request->getVar('id_mkdt') ?? 0);
        $bundle = $this->penagihanService->getTagihanBundle($idMkdt, $id !== null ? (string) $id : null);

        if ($this->isRawFormat()) {
            if ($idMkdt <= 0) {
                return $this->error('Parameter id_mkdt wajib diisi.', 422);
            }
            return $this->success($bundle, 'Data tagihan berhasil dimuat.');
        }

        return $this->response->setJSON($bundle);
    }

    public function get_riwayat_tagihan()
    {
        $input = $this->readRequestData();
        $idMkdtRaw = $input['id_mkdt'] ?? $this->request->getVar('id_mkdt');
        $noInvRaw = $input['no_inv'] ?? $this->request->getVar('no_inv');

        $idMkdt = ! empty($idMkdtRaw) ? (int) $idMkdtRaw : null;
        $noInv = ! empty($noInvRaw) ? trim((string) $noInvRaw) : null;

        $result = $this->penagihanService->getRiwayatTagihan($idMkdt, $noInv);

        if ($this->isRawFormat()) {
            $items = $result['data'] ?? [];
            return $this->success([
                'items' => $items,
                'meta'  => ['total' => count($items)],
            ], 'Riwayat surat penagihan berhasil dimuat.');
        }

        return $this->response->setJSON($result);
    }

    public function simpan_penagihan()
    {
        $input = $this->readRequestData();
        $actorId = function_exists('user_id') ? (int) (user_id() ?? 0) : 0;
        $result = $this->penagihanService->simpanPenagihan($input, $actorId);

        $statusCode = (int) ($result['_http_status'] ?? 422);
        unset($result['_http_status']);

        if ($this->isRawFormat()) {
            if (! empty($result['success'])) {
                return $this->success($result, (string) ($result['messages'] ?? 'Tagihan berhasil disimpan.'));
            }
            return $this->error((string) ($result['messages'] ?? 'Gagal menyimpan surat penagihan.'), $statusCode, [], $result);
        }

        return $this->response->setJSON($result);
    }

    public function update_status_penagihan()
    {
        $input = $this->readRequestData();
        $actorId = function_exists('user_id') ? (int) (user_id() ?? 0) : 0;
        $result = $this->penagihanService->updateStatusPenagihan($input, $actorId);

        $statusCode = (int) ($result['_http_status'] ?? 422);
        unset($result['_http_status']);

        if ($this->isRawFormat()) {
            if (! empty($result['success'])) {
                return $this->success($result, (string) ($result['messages'] ?? 'Status berhasil diubah.'));
            }
            return $this->error((string) ($result['messages'] ?? 'Gagal mengubah status surat penagihan.'), $statusCode, [], $result);
        }

        return $this->response->setJSON($result);
    }

    public function get_kopsurat_list()
    {
        $result = $this->penagihanService->getKopsuratList();

        if ($this->isRawFormat()) {
            $items = $result['data'] ?? [];
            return $this->success([
                'items' => $items,
                'meta'  => ['total' => count($items)],
            ], 'Daftar kop surat berhasil dimuat.');
        }

        return $this->response->setJSON($result);
    }

    public function get_kopsurat_detail()
    {
        $input = $this->readRequestData();
        $id = (int) ($input['id'] ?? $this->request->getVar('id') ?? 0);
        $result = $this->penagihanService->getKopsuratDetail($id);

        if ($this->isRawFormat()) {
            if (! empty($result['success'])) {
                return $this->success($result['data'], 'Detail kop surat berhasil dimuat.');
            }
            return $this->error('Data kop surat tidak ditemukan.', 404);
        }

        return $this->response->setJSON($result);
    }

    public function simpan_kopsurat()
    {
        $postData = $this->request->getPost();
        if (! is_array($postData) || $postData === []) {
            $postData = $this->readRequestData();
        }
        $fileKop = $this->request->getFile('file_kop');

        $result = $this->penagihanService->simpanKopsurat($postData, $fileKop);

        $statusCode = (int) ($result['_http_status'] ?? 422);
        unset($result['_http_status']);

        if ($this->isRawFormat()) {
            if (! empty($result['success'])) {
                return $this->success($result, (string) ($result['message'] ?? 'Kop surat berhasil disimpan.'));
            }
            return $this->error((string) ($result['message'] ?? 'Gagal menyimpan kop surat.'), $statusCode, [], $result);
        }

        return $this->response->setJSON($result);
    }

    public function hapus_kopsurat()
    {
        $postData = $this->request->getPost();
        if (! is_array($postData) || $postData === []) {
            $postData = $this->readRequestData();
        }
        $id = (int) ($postData['id'] ?? 0);

        $result = $this->penagihanService->hapusKopsurat($id);

        $statusCode = (int) ($result['_http_status'] ?? 422);
        unset($result['_http_status']);

        if ($this->isRawFormat()) {
            if (! empty($result['success'])) {
                return $this->success($result, (string) ($result['message'] ?? 'Kop surat berhasil dihapus.'));
            }
            return $this->error((string) ($result['message'] ?? 'Gagal menghapus kop surat.'), $statusCode, [], $result);
        }

        return $this->response->setJSON($result);
    }
}

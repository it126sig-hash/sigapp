<?php

namespace App\Controllers\Api;

use App\Exceptions\ServiceException;
use App\Services\ActiveProyekService;
use App\Services\NupService;
use CodeIgniter\HTTP\ResponseInterface;

class NupController extends BaseApiController
{
    public function __construct(
        private readonly NupService $nupService = new NupService(),
        private readonly ActiveProyekService $activeProyekService = new ActiveProyekService()
    ) {}

    public function list(): ResponseInterface
    {
        $idProyek = (int) ($this->request->getVar('id_proyek') ?: $this->activeProyekService->getActiveId() ?: 0);
        $search   = trim((string) ($this->request->getVar('search') ?? ''));

        $rows = $this->nupService->getListByProyek($idProyek, $search);

        return $this->response->setJSON([
            'status'  => true,
            'success' => true,
            'data'    => $rows,
            'count'   => count($rows),
            'token'   => csrf_hash(),
        ]);
    }

    public function detail(): ResponseInterface
    {
        if (! $this->validate(['id_mkdt' => 'required|is_natural_no_zero'])) {
            return $this->response->setStatusCode(422)->setJSON([
                'status'   => false,
                'success'  => false,
                'messages' => 'ID transaksi NUP tidak valid.',
                'token'    => csrf_hash(),
            ]);
        }

        try {
            $idMkdt = (int) $this->request->getVar('id_mkdt');
            $detail = $this->nupService->getDetail($idMkdt);

            return $this->response->setJSON([
                'status'  => true,
                'success' => true,
                'data'    => $detail,
                'token'   => csrf_hash(),
            ]);
        } catch (ServiceException $e) {
            return $this->response->setStatusCode($e->getStatusCode())->setJSON([
                'status'   => false,
                'success'  => false,
                'messages' => $e->getMessage(),
                'token'    => csrf_hash(),
            ]);
        }
    }

    public function save(): ResponseInterface
    {
        if (! in_groups(['1', '3', '4', '8'])) {
            return $this->response->setStatusCode(403)->setJSON([
                'status'   => false,
                'success'  => false,
                'messages' => 'Akses input NUP hanya untuk MKDT, Keuangan, Sales, atau Administrator.',
                'token'    => csrf_hash(),
            ]);
        }

        try {
            $post = $this->request->getPost();
            if (empty($post['id_proyek'])) {
                $post['id_proyek'] = (int) ($this->activeProyekService->getActiveId() ?: 0);
            }

            $files = [
                'file_ktp'       => $this->request->getFile('file_ktp'),
                'file_npwp'      => $this->request->getFile('file_npwp'),
                'file_data_diri' => $this->request->getFile('file_data_diri'),
                'file_nup'       => $this->request->getFile('file_nup'),
            ];

            $result = $this->nupService->saveNup($post, $files, (int) user_id());

            return $this->response->setJSON([
                'status'   => true,
                'success'  => true,
                'messages' => ! empty($post['id_mkdt']) ? 'Data NUP berhasil diperbarui.' : 'Data NUP berhasil ditambahkan.',
                'data'     => $result,
                'token'    => csrf_hash(),
            ]);
        } catch (ServiceException $e) {
            return $this->response->setStatusCode($e->getStatusCode())->setJSON([
                'status'   => false,
                'success'  => false,
                'messages' => $e->getMessage(),
                'token'    => csrf_hash(),
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status'   => false,
                'success'  => false,
                'messages' => 'Gagal menyimpan NUP: ' . $e->getMessage(),
                'token'    => csrf_hash(),
            ]);
        }
    }

    public function pull(): ResponseInterface
    {
        if (! in_groups(['1', '4'])) {
            return $this->response->setStatusCode(403)->setJSON([
                'status'   => false,
                'success'  => false,
                'messages' => 'Akses penarikan konsumen ke NUP hanya untuk MKDT atau Administrator.',
                'token'    => csrf_hash(),
            ]);
        }

        try {
            $post   = $this->request->getPost();
            $file   = $this->request->getFile('file_nup');
            $result = $this->nupService->pullToNup($post, $file, (int) user_id());

            return $this->response->setJSON([
                'status'   => true,
                'success'  => true,
                'messages' => 'Konsumen berhasil ditarik ke daftar NUP dan status kavling telah dikosongkan.',
                'data'     => $result,
                'token'    => csrf_hash(),
            ]);
        } catch (ServiceException $e) {
            return $this->response->setStatusCode($e->getStatusCode())->setJSON([
                'status'   => false,
                'success'  => false,
                'messages' => $e->getMessage(),
                'token'    => csrf_hash(),
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status'   => false,
                'success'  => false,
                'messages' => 'Gagal menarik konsumen ke NUP: ' . $e->getMessage(),
                'token'    => csrf_hash(),
            ]);
        }
    }

    public function kavlingOptions(): ResponseInterface
    {
        $idProyek         = (int) ($this->request->getVar('id_proyek') ?: $this->activeProyekService->getActiveId() ?: 0);
        $search           = trim((string) ($this->request->getVar('search') ?? ''));
        $currentIdKavling = (int) ($this->request->getVar('current_id_kavling') ?: 0);
        $lastIdKavling    = (int) ($this->request->getVar('last_id_kavling') ?: 0);
        $filterHargaJual  = trim((string) ($this->request->getVar('filter_hargajual') ?? ''));

        $options = $this->nupService->getKavlingOptions(
            $idProyek,
            $search,
            $currentIdKavling > 0 ? $currentIdKavling : null,
            $lastIdKavling > 0 ? $lastIdKavling : null,
            $filterHargaJual !== '' ? $filterHargaJual : null
        );

        return $this->response->setJSON([
            'status'  => true,
            'success' => true,
            'data'    => $options,
            'token'   => csrf_hash(),
        ]);
    }

    public function kavlingHistory(): ResponseInterface
    {
        $idMkdt    = (int) ($this->request->getVar('id_mkdt') ?: 0);
        $idKavling = (int) ($this->request->getVar('id_kavling') ?: 0);

        $history = $this->nupService->getFormattedKavlingHistory($idMkdt, $idKavling);

        return $this->response->setJSON([
            'status'      => true,
            'success'     => true,
            'history'     => $history,
            'has_history' => count($history) > 0,
            'token'       => csrf_hash(),
        ]);
    }
}

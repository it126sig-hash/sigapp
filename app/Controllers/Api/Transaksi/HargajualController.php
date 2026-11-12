<?php

namespace App\Controllers\Api\Transaksi;

use App\Controllers\Api\BaseApiController;
use App\Services\Transaksi\HargaJualService;
use CodeIgniter\HTTP\ResponseInterface;

class HargajualController extends BaseApiController
{
    protected HargaJualService $hargaJualService;

    public function __construct(?HargaJualService $hargaJualService = null)
    {
        $this->hargaJualService = $hargaJualService ?? new HargaJualService();
    }

    public function meta(): ResponseInterface
    {
        $idProyek = resolve_active_proyek_id($this->request->getVar('id_proyek'));
        $meta     = $this->hargaJualService->getMeta(
            $idProyek ? (int) $idProyek : null,
            (int) user_id()
        );

        return $this->success($meta, 'Metadata harga jual berhasil diambil');
    }

    public function getDataTable(): ResponseInterface
    {
        $var = (array) ($this->request->getVar() ?? []);
        if (!isset($var['search'])) {
            $var['search'] = ['value' => '', 'regex' => false];
        }
        $var['is_active'] = $this->request->getPost('is_active') ?? ($var['is_active'] ?? null);

        $idProyek  = resolve_active_proyek_id($var['id_proyek'] ?? null);
        $rawFormat = ($this->request->getVar('format') === 'raw');

        $result             = $this->hargaJualService->getDataTable($var, $idProyek ? (int) $idProyek : null, $rawFormat);
        $result['success']  = true;
        $result['messages'] = 'Data pricelist berhasil diambil';

        return $this->response->setJSON($result);
    }

    public function getAll(): ResponseInterface
    {
        $idProyek = resolve_active_proyek_id($this->request->getVar('id_proyek'));
        $search   = (string) ($this->request->getVar('search') ?? '');

        $rows = $this->hargaJualService->getAll(
            $idProyek ? (int) $idProyek : null,
            $search
        );

        return $this->response->setJSON([
            'success'  => true,
            'messages' => 'Daftar pricelist berhasil diambil',
            'data'     => $rows,
            'token'    => csrf_hash(),
        ]);
    }

    public function getOne(): ResponseInterface
    {
        $id = (int) ($this->request->getVar('id') ?: $this->request->getVar('id_cluster') ?: 0);
        if ($id <= 0) {
            return $this->error('ID pricelist wajib diisi', 422);
        }

        $row = $this->hargaJualService->getOne($id);
        if (!$row) {
            return $this->error('Data pricelist tidak ditemukan', 404);
        }

        return $this->response->setJSON(array_merge($row, [
            'success'  => true,
            'messages' => 'Data pricelist berhasil diambil',
            'data'     => $row,
            'token'    => csrf_hash(),
        ]));
    }

    public function add(): ResponseInterface
    {
        $id = $this->request->getPost('id');
        if ($id) {
            return $this->edit();
        }

        $fields = (array) ($this->request->getPost() ?? []);
        $file   = $this->request->getFile('id_filehj');

        $result          = $this->hargaJualService->create($fields, $file, (int) user_id());
        $result['token'] = csrf_hash();

        return $this->response->setJSON($result);
    }

    public function edit($id = null): ResponseInterface
    {
        $id = (int) ($this->request->getPost('id') ?: $id ?: 0);
        if ($id <= 0) {
            return $this->error('ID pricelist tidak valid', 422);
        }

        $fields = (array) ($this->request->getPost() ?? []);
        $file   = $this->request->getFile('id_filehj');

        $result          = $this->hargaJualService->update($id, $fields, $file, (int) user_id());
        $result['token'] = csrf_hash();

        return $this->response->setJSON($result);
    }

    public function remove(): ResponseInterface
    {
        $id = (int) ($this->request->getPost('id') ?? 0);
        if ($id <= 0) {
            return $this->error('Tidak ada Pricelist terpilih', 422);
        }

        $currentIsActive = (int) ($this->request->getPost('is_active') ?? 0);
        $result          = $this->hargaJualService->toggleActive($id, $currentIsActive, (int) user_id());
        $result['token'] = csrf_hash();

        return $this->response->setJSON($result);
    }

    public function set_harga(): ResponseInterface
    {
        $rawIdKavling = (string) ($this->request->getVar('id_kavling') ?? '');
        $idHargaAkhir = $this->request->getVar('sh-id') ?? $this->request->getVar('harga_akhir');

        $result          = $this->hargaJualService->setHargaKavling($rawIdKavling, $idHargaAkhir, (int) user_id());
        $result['token'] = csrf_hash();

        return $this->response->setJSON($result);
    }
}

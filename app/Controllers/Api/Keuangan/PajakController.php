<?php

namespace App\Controllers\Api\Keuangan;

use App\Controllers\Api\BaseApiController;
use App\Services\Keuangan\PajakService;
use CodeIgniter\HTTP\ResponseInterface;

class PajakController extends BaseApiController
{
    protected PajakService $pajakService;

    public function __construct(?PajakService $pajakService = null)
    {
        $this->pajakService = $pajakService ?? new PajakService();
    }

    public function meta(): ResponseInterface
    {
        $activeProjectId = $this->request->getGet('id_proyek');
        $projectId = ($activeProjectId !== null && $activeProjectId !== '') ? (int) $activeProjectId : null;

        return $this->success(
            $this->pajakService->getMetaPayload($projectId),
            'Metadata pajak berhasil dimuat.'
        );
    }

    public function getOne(): ResponseInterface
    {
        $input = $this->readRequestData();
        $idKavling = (int) ($input['id_kavling'] ?? 0);

        if ($idKavling <= 0) {
            if ($this->isRawFormat()) {
                return $this->error('ID kavling tidak valid.', 422);
            }

            return $this->respond(['token' => csrf_hash()]);
        }

        $actorId = function_exists('user_id') && user_id() ? (int) user_id() : null;
        $data = $this->pajakService->getOneBundle($idKavling, $actorId);

        if (! $data) {
            if ($this->isRawFormat()) {
                return $this->error('Data pajak kavling tidak ditemukan.', 404);
            }

            return $this->respond(['token' => csrf_hash()]);
        }

        if ($this->isRawFormat()) {
            return $this->success($data, 'Data pajak kavling berhasil dimuat.');
        }

        $data->token = csrf_hash();

        return $this->respond($data);
    }

    public function save(): ResponseInterface
    {
        $postData = $this->readRequestData();

        $rules = [
            'id_mkdt' => ['label' => 'Tidak ada data konsumen', 'rules' => 'permit_empty|max_length[255]'],
        ];

        $validation = \Config\Services::validation();
        if (! $validation->setRules($rules)->run($postData)) {
            if ($this->isRawFormat()) {
                return $this->error('Validasi gagal.', 422, $validation->getErrors());
            }

            return $this->respond([
                'token'    => csrf_hash(),
                'success'  => false,
                'messages' => $validation->listErrors(),
            ]);
        }

        $uploadedFiles = [
            'pph42_file-bpn'      => $this->request->getFile('pph42_file-bpn'),
            'pph42_file-ebilling' => $this->request->getFile('pph42_file-ebilling'),
            'ppn_file-bpn'        => $this->request->getFile('ppn_file-bpn'),
            'ppn_file-faktur'     => $this->request->getFile('ppn_file-faktur'),
            'ppn_file-ebilling'   => $this->request->getFile('ppn_file-ebilling'),
        ];

        $actorId = (int) (function_exists('user_id') ? (user_id() ?? 0) : 0);
        $result = $this->pajakService->savePajakData($postData, $uploadedFiles, $actorId);

        if ($this->isRawFormat()) {
            if (! ($result['success'] ?? false)) {
                return $this->error($result['messages'] ?? 'Gagal menyimpan pajak.', 422);
            }

            return $this->success($result['data'] ?? null, $result['messages'] ?? 'Data berhasil disimpan.');
        }

        return $this->respond([
            'token'    => csrf_hash(),
            'success'  => (bool) ($result['success'] ?? false),
            'messages' => $result['messages'] ?? '',
        ]);
    }
}

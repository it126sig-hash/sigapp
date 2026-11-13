<?php

namespace App\Controllers\Api\Mkdt;

use App\Controllers\Api\BaseApiController;
use App\Services\Mkdt\PromoService;
use CodeIgniter\HTTP\ResponseInterface;

class PromoController extends BaseApiController
{
    protected PromoService $promoService;

    public function __construct(?PromoService $promoService = null)
    {
        $this->promoService = $promoService ?? new PromoService();
    }

    public function meta(): ResponseInterface
    {
        $indexData = $this->promoService->getIndexData();

        return $this->success([
            'proyek'           => $indexData['proyek'],
            'active_proyek_id' => $indexData['active_proyek_id'],
            'status_options'   => $indexData['meta']['status_options'] ?? [],
        ], 'Metadata promo berhasil diambil');
    }

    public function getDataTable(): ResponseInterface
    {
        $isRaw = $this->isRawRequest();
        $var   = (array) ($this->request->getVar() ?? []);
        if (!isset($var['search'])) {
            $var['search'] = ['value' => '', 'regex' => false];
        }

        $result = $this->promoService->getDataTables($var, $isRaw);

        if ($isRaw) {
            return $this->success($result, 'Data promo berhasil diambil');
        }

        return $this->response->setJSON($result);
    }

    public function getAll(): ResponseInterface
    {
        $isRaw    = $this->isRawRequest();
        $rawProyek = $this->request->getVar('id_proyek');
        $idProyek  = function_exists('resolve_active_proyek_id')
            ? resolve_active_proyek_id($rawProyek)
            : ($rawProyek !== null && (int) $rawProyek > 0 ? (int) $rawProyek : null);
        $search    = (string) ($this->request->getVar('search') ?? '');

        $rows = $this->promoService->getAll($idProyek, $search, $isRaw);

        if ($isRaw) {
            return $this->success($rows, 'Daftar promo berhasil diambil');
        }

        return $this->response->setJSON([
            'token' => csrf_hash(),
            'data'  => $rows,
        ]);
    }

    public function getOne($id = null): ResponseInterface
    {
        $isRaw   = $this->isRawRequest();
        $promoId = (int) (
            $id
            ?: $this->request->getVar('id')
            ?: $this->request->getVar('id_promo')
            ?: $this->request->getVar('id_cluster')
            ?: 0
        );

        if ($promoId <= 0) {
            return $this->error('ID promo tidak valid', 422, ['id' => 'ID promo tidak valid']);
        }

        $row = $this->promoService->getOne($promoId);
        if (!$row) {
            return $this->error('Data promo tidak ditemukan', 404);
        }

        if ($isRaw) {
            return $this->success($row, 'Data promo berhasil diambil');
        }

        $payload          = (array) $row;
        $payload['token'] = csrf_hash();

        return $this->response->setJSON($payload);
    }

    public function add(): ResponseInterface
    {
        $isRaw  = $this->isRawRequest();
        $input  = $this->getInputPayload();
        $result = $this->promoService->create($input, (int) user_id());

        if ($isRaw) {
            if (!$result['success']) {
                return $this->error($result['messages'], 422, $result['errors'] ?? null);
            }

            return $this->successCreated($result['data'], $result['messages']);
        }

        return $this->response->setJSON([
            'token'    => csrf_hash(),
            'success'  => $result['success'],
            'messages' => $result['messages'],
            'data'     => $result['data'],
        ]);
    }

    public function edit($id = null): ResponseInterface
    {
        $isRaw   = $this->isRawRequest();
        $input   = $this->getInputPayload();
        $promoId = (int) ($id ?: ($input['id'] ?? ($input['id_promo'] ?? 0)));
        $result  = $this->promoService->update($promoId, $input, (int) user_id());

        if ($isRaw) {
            if (!$result['success']) {
                if (!empty($result['not_found'])) {
                    return $this->error($result['messages'], 404);
                }

                return $this->error($result['messages'], 422, $result['errors'] ?? null);
            }

            return $this->success($result['data'], $result['messages']);
        }

        return $this->response->setJSON([
            'token'    => csrf_hash(),
            'success'  => $result['success'],
            'messages' => $result['messages'],
            'data'     => $result['data'],
        ]);
    }

    public function remove($id = null): ResponseInterface
    {
        $isRaw   = $this->isRawRequest();
        $input   = $this->getInputPayload();
        $promoId = (int) (
            $id
            ?: ($input['id'] ?? null)
            ?: ($input['id_promo'] ?? null)
            ?: $this->request->getVar('id')
            ?: $this->request->getVar('id_promo')
            ?: 0
        );
        $result  = $this->promoService->deactivate($promoId, (int) user_id());

        if ($isRaw) {
            if (!$result['success']) {
                if (!empty($result['not_found'])) {
                    return $this->error($result['messages'], 404);
                }

                return $this->error($result['messages'], 422, $result['errors'] ?? null);
            }

            return $this->success($result['data'], $result['messages']);
        }

        return $this->response->setJSON([
            'token'    => csrf_hash(),
            'success'  => $result['success'],
            'messages' => $result['messages'],
            'data'     => $result['data'],
        ]);
    }

    protected function isRawRequest(): bool
    {
        // TODO(full-json): switch default to raw JSON once legacy DataTables views migrate
        return $this->request->getVar('format') === 'raw';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getInputPayload(): array
    {
        $post = (array) ($this->request->getPost() ?? []);
        if ($post !== []) {
            return $post;
        }

        $json = $this->request->getJSON(true);
        if (is_array($json)) {
            return $json;
        }

        $raw = $this->request->getRawInput();

        return is_array($raw) ? $raw : [];
    }
}

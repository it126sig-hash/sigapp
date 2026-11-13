<?php

namespace App\Controllers\Api\Siteplan;

use App\Controllers\Api\BaseApiController;
use App\Services\Siteplan\TargetSiteplanService;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Validation\ValidationInterface;

class TargetController extends BaseApiController
{
    protected TargetSiteplanService $targetService;
    protected ValidationInterface $validation;

    public function __construct(?TargetSiteplanService $targetService = null)
    {
        helper(['auth', 'active_proyek']);
        $this->targetService = $targetService ?? new TargetSiteplanService();
        $this->validation    = \Config\Services::validation();
    }

    public function list(): ResponseInterface
    {
        $input    = $this->getRequestData();
        $idProyek = (int) ($input['id_proyek'] ?? $this->request->header('X-Project-Id')?->getValue() ?? 0);

        if ($idProyek <= 0) {
            return $this->error('Proyek harus diisi', 422, ['id_proyek' => 'Proyek harus diisi']);
        }

        return $this->success($this->targetService->listByProject($idProyek));
    }

    public function detail(): ResponseInterface
    {
        $input    = $this->getRequestData();
        $idTarget = (int) ($input['id_target'] ?? 0);

        if ($idTarget <= 0) {
            return $this->error('Target harus diisi', 422, ['id_target' => 'Target harus diisi']);
        }

        try {
            return $this->success($this->targetService->get($idTarget));
        } catch (\Throwable $e) {
            $statusCode = str_contains(strtolower($e->getMessage()), 'tidak ditemukan') ? 404 : 400;

            return $this->error($e->getMessage(), $statusCode);
        }
    }

    public function save(): ResponseInterface
    {
        $input = $this->getRequestData();

        $this->validation->setRules([
            'id_proyek' => [
                'label' => 'Proyek',
                'rules' => 'required|integer',
            ],
            'tahun_target' => [
                'label' => 'Tahun Target',
                'rules' => 'required|integer|greater_than_equal_to[2000]|less_than_equal_to[2100]',
            ],
            'deskripsi' => [
                'label' => 'Deskripsi',
                'rules' => 'permit_empty',
            ],
        ]);

        if (! $this->validation->run($input)) {
            $errors  = $this->validation->getErrors();
            $message = implode(' ', array_values($errors)) ?: 'Validasi gagal.';

            return $this->error($message, 422, $errors);
        }

        try {
            $result = $this->targetService->save($input);

            return $this->success($result, 'Target berhasil disimpan');
        } catch (\Throwable $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    public function history(): ResponseInterface
    {
        $input    = $this->getRequestData();
        $idTarget = (int) ($input['id_target'] ?? 0);

        if ($idTarget <= 0) {
            return $this->error('Target harus diisi', 422, ['id_target' => 'Target harus diisi']);
        }

        return $this->success($this->targetService->history($idTarget));
    }

    private function getRequestData(): array
    {
        $query = $this->request->getGet();
        $query = is_array($query) ? $query : [];

        $post = $this->request->getPost();
        $post = is_array($post) ? $post : [];

        $json = [];
        if (str_contains(strtolower($this->request->getHeaderLine('Content-Type')), 'application/json')) {
            try {
                $decoded = $this->request->getJSON(true);
                if (is_array($decoded)) {
                    $json = $decoded;
                }
            } catch (\Throwable $e) {
                $json = [];
            }
        }

        return array_merge($query, $post, $json);
    }
}

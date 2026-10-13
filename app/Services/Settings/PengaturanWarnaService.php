<?php

namespace App\Services\Settings;

use App\Repositories\Settings\PengaturanWarnaRepository;

class PengaturanWarnaService
{
    protected PengaturanWarnaRepository $repository;
    protected $validation;

    public function __construct()
    {
        $this->repository = new PengaturanWarnaRepository();
        $this->validation = \Config\Services::validation();
    }

    public function getAll(): array
    {
        $result = $this->repository->getAll();
        $data = [];

        foreach ($result as $key => $value) {
            $ops = '<div class="btn-group">';
            $ops .= '<button type="button" class="btn btn-sm btn-info" onclick="edit(\''. esc($value->config_name) .'\')"><i class="fa fa-edit"></i></button>';
            $ops .= '<button type="button" class="btn btn-sm btn-danger" onclick="remove(\''. esc($value->config_name) .'\')"><i class="fa fa-trash"></i></button>';
            $ops .= '</div>';

            $data[$key] = [
                esc($value->config_name),
                "<span class='btn' style='background-color: ". esc($value->fill) ."'>". esc($value->fill) ."</span>",
                $value->strokeWidth ?? 0,
                esc((string) $value->dashed),
                esc((string) $value->keterangan),
                esc((string) $value->add_by),
                esc((string) $value->date_add),
                esc((string) $value->edit_by),
                esc((string) $value->date_edit),
                $ops,
            ];
        }

        return [
            'token' => csrf_hash(),
            'data'  => $data,
        ];
    }

    public function getOne(string $configName): ?object
    {
        if (trim($configName) === '') {
            return null;
        }

        $data = $this->repository->getByConfigName($configName);
        if (!$data) {
            return null;
        }

        $data->token = csrf_hash();
        return $data;
    }

    public function add(array $postData, ?string $userName = null): array
    {
        $fields = [
            'config_name' => trim((string) ($postData['configName'] ?? '')),
            'fill'        => trim((string) ($postData['fill'] ?? '')),
            'strokeWidth' => $postData['strokeWidth'] ?? null,
            'dashed'      => trim((string) ($postData['dashed'] ?? '')),
            'keterangan'  => trim((string) ($postData['keterangan'] ?? '')),
            'add_by'      => $userName ?: (string) user_id(),
            'date_add'    => date('Y-m-d H:i:s'),
        ];

        $this->validation->setRules([
            'config_name' => ['label' => 'Item', 'rules' => 'required|max_length[255]'],
            'fill'        => ['label' => 'Fill', 'rules' => 'required|max_length[20]'],
            'strokeWidth' => ['label' => 'Ketebalan garis', 'rules' => 'permit_empty|integer|greater_than_equal_to[0]|less_than_equal_to[100]'],
            'dashed'      => ['label' => 'Dashed', 'rules' => 'permit_empty|max_length[255]'],
            'keterangan'  => ['label' => 'Keterangan', 'rules' => 'permit_empty|max_length[255]'],
        ]);

        if (!$this->validation->run($fields)) {
            return [
                'success'  => false,
                'token'    => csrf_hash(),
                'messages' => $this->validation->listErrors(),
            ];
        }

        $fields['strokeWidth'] = $this->normalizeStrokeWidth($fields['strokeWidth']);

        if ($this->repository->getByConfigName($fields['config_name'])) {
            return [
                'success'  => false,
                'token'    => csrf_hash(),
                'messages' => 'Config name sudah digunakan',
            ];
        }

        if ($this->repository->insert($fields)) {
            return [
                'success'  => true,
                'token'    => csrf_hash(),
                'messages' => 'Data has been inserted successfully',
            ];
        }

        return [
            'success'  => false,
            'token'    => csrf_hash(),
            'messages' => 'Insertion error!',
        ];
    }

    public function edit(array $postData, ?string $userName = null): array
    {
        $configName = trim((string) ($postData['configName'] ?? ''));
        $fields = [
            'config_name' => $configName,
            'fill'        => trim((string) ($postData['fill'] ?? '')),
            'strokeWidth' => $postData['strokeWidth'] ?? null,
            'dashed'      => trim((string) ($postData['dashed'] ?? '')),
            'keterangan'  => trim((string) ($postData['keterangan'] ?? '')),
            'edit_by'     => $userName ?: (string) user_id(),
            'date_edit'   => date('Y-m-d H:i:s'),
        ];

        $this->validation->setRules([
            'config_name' => ['label' => 'Item', 'rules' => 'required|max_length[255]'],
            'fill'        => ['label' => 'Fill', 'rules' => 'required|max_length[20]'],
            'strokeWidth' => ['label' => 'Ketebalan garis', 'rules' => 'permit_empty|integer|greater_than_equal_to[0]|less_than_equal_to[100]'],
            'dashed'      => ['label' => 'Dashed', 'rules' => 'permit_empty|max_length[255]'],
            'keterangan'  => ['label' => 'Keterangan', 'rules' => 'permit_empty|max_length[255]'],
        ]);

        if (!$this->validation->run($fields)) {
            return [
                'success'  => false,
                'token'    => csrf_hash(),
                'messages' => $this->validation->listErrors(),
            ];
        }

        $fields['strokeWidth'] = $this->normalizeStrokeWidth($fields['strokeWidth']);

        if (!$this->repository->getByConfigName($configName)) {
            return [
                'success'  => false,
                'token'    => csrf_hash(),
                'messages' => 'Data tidak ditemukan',
            ];
        }

        if ($this->repository->update($configName, $fields)) {
            return [
                'success'  => true,
                'token'    => csrf_hash(),
                'messages' => 'Successfully updated',
            ];
        }

        return [
            'success'  => false,
            'token'    => csrf_hash(),
            'messages' => 'Update error!',
        ];
    }

    public function remove(string $configName): array
    {
        if (trim($configName) === '') {
            return [
                'success'  => false,
                'token'    => csrf_hash(),
                'messages' => 'Parameter tidak valid',
            ];
        }

        if ($this->repository->delete($configName)) {
            return [
                'success'  => true,
                'token'    => csrf_hash(),
                'messages' => 'Deletion succeeded',
            ];
        }

        return [
            'success'  => false,
            'token'    => csrf_hash(),
            'messages' => 'Deletion error!',
        ];
    }

    public function normalizeStrokeWidth($value): int
    {
        if ($value === null || $value === '') {
            return 0;
        }

        return max(0, min(100, (int) $value));
    }
}

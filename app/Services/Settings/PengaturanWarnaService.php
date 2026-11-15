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

    public function getAll(array $params = []): array
    {
        $result = $this->repository->getAll();

        if (($params['format'] ?? '') === 'raw') {
            $search = trim((string) ($params['search'] ?? ''));
            if ($search !== '') {
                $result = array_values(array_filter($result, static function (object $row) use ($search): bool {
                    $candidates = [
                        (string) ($row->config_name ?? ''),
                        (string) ($row->fill ?? ''),
                        (string) ($row->dashed ?? ''),
                        (string) ($row->keterangan ?? ''),
                        (string) ($row->add_by ?? ''),
                        (string) ($row->edit_by ?? ''),
                    ];
                    foreach ($candidates as $candidate) {
                        if ($candidate !== '' && mb_stripos($candidate, $search) !== false) {
                            return true;
                        }
                    }

                    return false;
                }));
            }

            $page = max(1, (int) ($params['page'] ?? 1));
            $perPage = max(1, min(100, (int) ($params['per_page'] ?? 25)));
            $total = count($result);
            $sliced = array_slice($result, ($page - 1) * $perPage, $perPage);
            $items = array_map(fn (object $row): array => $this->formatRawItem($row), $sliced);

            return [
                'items' => $items,
                'meta'  => [
                    'page'     => $page,
                    'per_page' => $perPage,
                    'total'    => $total,
                ],
            ];
        }

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

    public function getOne(string $configName, bool $raw = false): ?object
    {
        if (trim($configName) === '') {
            return null;
        }

        $data = $this->repository->getByConfigName($configName);
        if (!$data) {
            return null;
        }

        if ($raw) {
            return (object) $this->formatRawItem($data);
        }

        $data->token = csrf_hash();
        return $data;
    }

    public function add(array $postData, ?string $userName = null, bool $raw = false): array
    {
        $fields = [
            'config_name' => trim((string) ($postData['configName'] ?? $postData['config_name'] ?? '')),
            'fill'        => trim((string) ($postData['fill'] ?? '')),
            'strokeWidth' => $postData['strokeWidth'] ?? null,
            'dashed'      => trim((string) ($postData['dashed'] ?? '')),
            'keterangan'  => trim((string) ($postData['keterangan'] ?? '')),
            'add_by'      => $userName ?: (string) user_id(),
            'date_add'    => date('Y-m-d H:i:s'),
        ];

        $this->validation->reset();
        $this->validation->setRules([
            'config_name' => ['label' => 'Item', 'rules' => 'required|max_length[255]'],
            'fill'        => ['label' => 'Fill', 'rules' => 'required|max_length[20]'],
            'strokeWidth' => ['label' => 'Ketebalan garis', 'rules' => 'permit_empty|integer|greater_than_equal_to[0]|less_than_equal_to[100]'],
            'dashed'      => ['label' => 'Dashed', 'rules' => 'permit_empty|max_length[255]'],
            'keterangan'  => ['label' => 'Keterangan', 'rules' => 'permit_empty|max_length[255]'],
        ]);

        if (!$this->validation->run($fields)) {
            if ($raw) {
                return [
                    'success'  => false,
                    'status'   => 422,
                    'messages' => 'Validasi gagal.',
                    'errors'   => $this->validation->getErrors(),
                ];
            }

            return [
                'success'  => false,
                'token'    => csrf_hash(),
                'messages' => $this->validation->listErrors(),
            ];
        }

        $fields['strokeWidth'] = $this->normalizeStrokeWidth($fields['strokeWidth']);

        if ($this->repository->getByConfigName($fields['config_name'])) {
            if ($raw) {
                return [
                    'success'  => false,
                    'status'   => 422,
                    'messages' => 'Config name sudah digunakan',
                    'errors'   => ['config_name' => 'Config name sudah digunakan'],
                ];
            }

            return [
                'success'  => false,
                'token'    => csrf_hash(),
                'messages' => 'Config name sudah digunakan',
            ];
        }

        if ($this->repository->insert($fields)) {
            if ($raw) {
                $created = $this->repository->getByConfigName($fields['config_name']);
                return [
                    'success'  => true,
                    'created'  => true,
                    'status'   => 201,
                    'messages' => 'Data has been inserted successfully',
                    'data'     => $this->formatRawItem($created ?? (object) $fields),
                ];
            }

            return [
                'success'  => true,
                'token'    => csrf_hash(),
                'messages' => 'Data has been inserted successfully',
            ];
        }

        if ($raw) {
            return [
                'success'  => false,
                'status'   => 500,
                'messages' => 'Insertion error!',
            ];
        }

        return [
            'success'  => false,
            'token'    => csrf_hash(),
            'messages' => 'Insertion error!',
        ];
    }

    public function edit(array $postData, ?string $userName = null, bool $raw = false): array
    {
        $configName = trim((string) ($postData['configName'] ?? $postData['config_name'] ?? ''));
        $fields = [
            'config_name' => $configName,
            'fill'        => trim((string) ($postData['fill'] ?? '')),
            'strokeWidth' => $postData['strokeWidth'] ?? null,
            'dashed'      => trim((string) ($postData['dashed'] ?? '')),
            'keterangan'  => trim((string) ($postData['keterangan'] ?? '')),
            'edit_by'     => $userName ?: (string) user_id(),
            'date_edit'   => date('Y-m-d H:i:s'),
        ];

        $this->validation->reset();
        $this->validation->setRules([
            'config_name' => ['label' => 'Item', 'rules' => 'required|max_length[255]'],
            'fill'        => ['label' => 'Fill', 'rules' => 'required|max_length[20]'],
            'strokeWidth' => ['label' => 'Ketebalan garis', 'rules' => 'permit_empty|integer|greater_than_equal_to[0]|less_than_equal_to[100]'],
            'dashed'      => ['label' => 'Dashed', 'rules' => 'permit_empty|max_length[255]'],
            'keterangan'  => ['label' => 'Keterangan', 'rules' => 'permit_empty|max_length[255]'],
        ]);

        if (!$this->validation->run($fields)) {
            if ($raw) {
                return [
                    'success'  => false,
                    'status'   => 422,
                    'messages' => 'Validasi gagal.',
                    'errors'   => $this->validation->getErrors(),
                ];
            }

            return [
                'success'  => false,
                'token'    => csrf_hash(),
                'messages' => $this->validation->listErrors(),
            ];
        }

        $fields['strokeWidth'] = $this->normalizeStrokeWidth($fields['strokeWidth']);

        if (!$this->repository->getByConfigName($configName)) {
            if ($raw) {
                return [
                    'success'  => false,
                    'status'   => 404,
                    'messages' => 'Data tidak ditemukan',
                ];
            }

            return [
                'success'  => false,
                'token'    => csrf_hash(),
                'messages' => 'Data tidak ditemukan',
            ];
        }

        $updateFields = array_diff_key($fields, ['config_name' => true]);

        if ($this->repository->update($configName, $updateFields)) {
            if ($raw) {
                $updated = $this->repository->getByConfigName($configName);
                return [
                    'success'  => true,
                    'status'   => 200,
                    'messages' => 'Successfully updated',
                    'data'     => $this->formatRawItem($updated ?? (object) $fields),
                ];
            }

            return [
                'success'  => true,
                'token'    => csrf_hash(),
                'messages' => 'Successfully updated',
            ];
        }

        if ($raw) {
            return [
                'success'  => false,
                'status'   => 500,
                'messages' => 'Update error!',
            ];
        }

        return [
            'success'  => false,
            'token'    => csrf_hash(),
            'messages' => 'Update error!',
        ];
    }

    public function remove(string $configName, bool $raw = false): array
    {
        $configName = trim($configName);
        if ($configName === '') {
            if ($raw) {
                return [
                    'success'  => false,
                    'status'   => 422,
                    'messages' => 'Parameter tidak valid',
                    'errors'   => ['config_name' => 'Parameter tidak valid'],
                ];
            }

            return [
                'success'  => false,
                'token'    => csrf_hash(),
                'messages' => 'Parameter tidak valid',
            ];
        }

        if ($raw && !$this->repository->getByConfigName($configName)) {
            return [
                'success'  => false,
                'status'   => 404,
                'messages' => 'Data tidak ditemukan',
            ];
        }

        if ($this->repository->delete($configName)) {
            if ($raw) {
                return [
                    'success'  => true,
                    'status'   => 200,
                    'messages' => 'Deletion succeeded',
                    'data'     => ['config_name' => $configName],
                ];
            }

            return [
                'success'  => true,
                'token'    => csrf_hash(),
                'messages' => 'Deletion succeeded',
            ];
        }

        if ($raw) {
            return [
                'success'  => false,
                'status'   => 500,
                'messages' => 'Deletion error!',
            ];
        }

        return [
            'success'  => false,
            'token'    => csrf_hash(),
            'messages' => 'Deletion error!',
        ];
    }

    private function formatRawItem(object $row): array
    {
        return [
            'config_name' => (string) ($row->config_name ?? ''),
            'fill'        => (string) ($row->fill ?? ''),
            'strokeWidth' => (int) ($row->strokeWidth ?? 0),
            'dashed'      => (string) ($row->dashed ?? ''),
            'keterangan'  => (string) ($row->keterangan ?? ''),
            'add_by'      => $row->add_by ?? null,
            'date_add'    => $row->date_add ?? null,
            'edit_by'     => $row->edit_by ?? null,
            'date_edit'   => $row->date_edit ?? null,
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

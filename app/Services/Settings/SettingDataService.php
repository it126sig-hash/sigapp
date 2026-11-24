<?php

namespace App\Services\Settings;

use App\Repositories\Settings\SettingDataRepository;
use CodeIgniter\Validation\Exceptions\ValidationException;

class SettingDataService
{
    protected SettingDataRepository $repository;
    protected $validation;

    public function __construct()
    {
        $this->repository = new SettingDataRepository();
        $this->validation = \Config\Services::validation();
    }

    public function getAllConfigs(): array
    {
        return $this->repository->getAllConfigs();
    }

    public function getMetaTables(): array
    {
        $tables = [];
        foreach ($this->repository->getAllConfigs() as $key => $config) {
            $tables[] = [
                'key'         => $key,
                'label'       => $config['label'],
                'table_label' => $config['table_label'],
                'primary_key' => $config['primary_key'],
                'manual_id'   => !empty($config['manual_id']),
                'fields'      => $config['fields'],
            ];
        }

        return $tables;
    }

    public function getConfig(string $key): array
    {
        return $this->repository->getConfig($key);
    }

    public function list(string $key, array $params = []): array
    {
        $config = $this->repository->getConfig($key);
        $rows = $this->repository->listRows($config);

        if (($params['format'] ?? '') === 'raw') {
            $search = trim((string) ($params['search'] ?? ''));
            if ($search !== '') {
                $fieldKeys = array_keys($config['fields']);
                $rows = array_values(array_filter($rows, static function (array $row) use ($search, $config, $fieldKeys): bool {
                    $candidates = [(string) ($row[$config['primary_key']] ?? '')];
                    foreach ($fieldKeys as $fieldKey) {
                        $candidates[] = (string) ($row[$fieldKey] ?? '');
                    }
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
            $total = count($rows);
            $sliced = array_slice($rows, ($page - 1) * $perPage, $perPage);

            $items = array_map(fn (array $row): array => $this->formatRawRow($config, $row), $sliced);

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
        foreach ($rows as $index => $row) {
            $line = [$index + 1];
            foreach ($config['fields'] as $field => $meta) {
                $value = $row[$field] ?? '';
                $line[] = esc((string) $value);
            }
            $id = (int) ($row[$config['primary_key']] ?? 0);
            $line[] = '<div class="btn-group">'
                . '<button type="button" class="btn btn-sm btn-info" onclick="editSettingData(\'' . esc($key) . '\',' . $id . ')"><i class="fa fa-edit"></i></button>'
                . '<button type="button" class="btn btn-sm btn-danger" onclick="deleteSettingData(\'' . esc($key) . '\',' . $id . ')"><i class="fa fa-trash"></i></button>'
                . '</div>';
            $data[] = $line;
        }

        return [
            'token' => csrf_hash(),
            'data'  => $data,
        ];
    }

    public function get(string $key, int $id, bool $raw = false): ?array
    {
        $config = $this->repository->getConfig($key);
        $row = $this->repository->findActiveRow($config, $id);

        if (!$row) {
            return null;
        }

        if ($raw) {
            return $this->formatRawRow($config, $row);
        }

        $row['success'] = true;
        $row['token'] = csrf_hash();

        return $row;
    }

    public function save(string $key, array $postData, int $userId, bool $raw = false): array
    {
        $config = $this->repository->getConfig($key);
        $id = (int) ($postData[$config['primary_key']] ?? $postData['id'] ?? 0);
        $fields = $this->collectFields($config, $postData);

        $rules = $this->validationRules($config);
        $this->validation->reset();
        $this->validation->setRules($rules);

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

        $now = date('Y-m-d H:i:s');
        $dbFields = $this->repository->getFieldNames($config['table']);

        if ($id > 0) {
            if (!$this->repository->findActiveRow($config, $id)) {
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

            if (in_array('updated_at', $dbFields, true)) {
                $fields['updated_at'] = $now;
            }
            if (in_array('edit_by', $dbFields, true)) {
                $fields['edit_by'] = $userId;
            }

            $this->repository->updateRow($config['table'], $config['primary_key'], $id, $fields);

            if ($raw) {
                $updated = $this->repository->findActiveRow($config, $id);
                return [
                    'success'  => true,
                    'status'   => 200,
                    'messages' => 'Data berhasil diperbaharui',
                    'data'     => $updated ? $this->formatRawRow($config, $updated) : null,
                ];
            }

            return [
                'success'  => true,
                'token'    => csrf_hash(),
                'messages' => 'Data berhasil diperbaharui',
            ];
        }

        if (in_array('created_at', $dbFields, true)) {
            $fields['created_at'] = $now;
        }
        if (in_array('add_by', $dbFields, true)) {
            $fields['add_by'] = $userId;
        }

        if (!empty($config['manual_id'])) {
            $newId = $this->repository->nextManualId($config);
            $fields[$config['primary_key']] = $newId;
            $this->repository->insertRow($config['table'], $fields);
        } else {
            $newId = $this->repository->insertRow($config['table'], $fields);
        }

        if ($raw) {
            $created = $this->repository->findActiveRow($config, $newId);
            return [
                'success'  => true,
                'created'  => true,
                'status'   => 201,
                'messages' => 'Data berhasil ditambahkan',
                'data'     => $created ? $this->formatRawRow($config, $created) : [$config['primary_key'] => $newId],
            ];
        }

        return [
            'success'  => true,
            'token'    => csrf_hash(),
            'messages' => 'Data berhasil ditambahkan',
        ];
    }

    public function delete(string $key, int $id, int $userId, bool $raw = false): array
    {
        $config = $this->repository->getConfig($key);
        if ($raw && $id <= 0) {
            return [
                'success'  => false,
                'status'   => 422,
                'messages' => 'ID tidak valid',
                'errors'   => ['id' => 'ID wajib diisi'],
            ];
        }

        if (!$this->repository->findActiveRow($config, $id)) {
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

        $this->repository->softDeleteRow($config, $id, $userId);

        if ($raw) {
            return [
                'success'  => true,
                'status'   => 200,
                'messages' => 'Data berhasil dihapus',
                'data'     => [
                    'id'                    => $id,
                    $config['primary_key']  => $id,
                ],
            ];
        }

        return [
            'success'  => true,
            'token'    => csrf_hash(),
            'messages' => 'Data berhasil dihapus',
        ];
    }

    protected function formatRawRow(array $config, array $row): array
    {
        $pk = $config['primary_key'];
        $item = [
            $pk => (int) ($row[$pk] ?? 0),
        ];

        foreach ($config['fields'] as $field => $meta) {
            $type = $meta['type'] ?? 'text';
            if ($type === 'number') {
                $item[$field] = (int) ($row[$field] ?? 0);
            } elseif ($type === 'decimal') {
                $item[$field] = (float) ($row[$field] ?? 0);
            } else {
                $item[$field] = (string) ($row[$field] ?? '');
            }
        }

        foreach (['created_at', 'updated_at', 'add_by', 'edit_by'] as $auditField) {
            if (array_key_exists($auditField, $row)) {
                $item[$auditField] = $row[$auditField];
            }
        }

        return $item;
    }

    protected function collectFields(array $config, array $postData): array
    {
        $fields = [];
        foreach ($config['fields'] as $field => $meta) {
            $value = trim((string) ($postData[$field] ?? ''));
            if (($meta['type'] ?? 'text') === 'number') {
                $fields[$field] = $value === '' ? 0 : (int) $value;
            } elseif (($meta['type'] ?? 'text') === 'decimal') {
                $fields[$field] = $value === '' ? 0 : (float) str_replace(',', '.', $value);
            } else {
                $fields[$field] = $value;
            }
        }

        return $fields;
    }

    protected function validationRules(array $config): array
    {
        $rules = [];
        foreach ($config['fields'] as $field => $meta) {
            $fieldRules = [];
            $fieldRules[] = !empty($meta['required']) ? 'required' : 'permit_empty';
            if (($meta['type'] ?? 'text') === 'number') {
                $fieldRules[] = 'integer';
            } elseif (($meta['type'] ?? 'text') === 'decimal') {
                $fieldRules[] = 'regex_match[/^-?[0-9]+([.,][0-9]+)?$/]';
            }
            if (!empty($meta['max_length'])) {
                $fieldRules[] = 'max_length[' . (int) $meta['max_length'] . ']';
            }
            $rules[$field] = [
                'label' => $meta['label'],
                'rules' => implode('|', $fieldRules),
            ];
        }

        return $rules;
    }
}

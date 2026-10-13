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

    public function getConfig(string $key): array
    {
        return $this->repository->getConfig($key);
    }

    public function list(string $key): array
    {
        $config = $this->repository->getConfig($key);
        $rows = $this->repository->listRows($config);

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

    public function get(string $key, int $id): ?array
    {
        $config = $this->repository->getConfig($key);
        $row = $this->repository->findActiveRow($config, $id);

        if (!$row) {
            return null;
        }

        $row['success'] = true;
        $row['token'] = csrf_hash();

        return $row;
    }

    public function save(string $key, array $postData, int $userId): array
    {
        $config = $this->repository->getConfig($key);
        $id = (int) ($postData[$config['primary_key']] ?? 0);
        $fields = $this->collectFields($config, $postData);

        $rules = $this->validationRules($config);
        $this->validation->setRules($rules);

        if (!$this->validation->run($fields)) {
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
            $fields[$config['primary_key']] = $this->repository->nextManualId($config);
        }

        $this->repository->insertRow($config['table'], $fields);

        return [
            'success'  => true,
            'token'    => csrf_hash(),
            'messages' => 'Data berhasil ditambahkan',
        ];
    }

    public function delete(string $key, int $id, int $userId): array
    {
        $config = $this->repository->getConfig($key);
        if (!$this->repository->findActiveRow($config, $id)) {
            return [
                'success'  => false,
                'token'    => csrf_hash(),
                'messages' => 'Data tidak ditemukan',
            ];
        }

        $this->repository->softDeleteRow($config, $id, $userId);

        return [
            'success'  => true,
            'token'    => csrf_hash(),
            'messages' => 'Data berhasil dihapus',
        ];
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

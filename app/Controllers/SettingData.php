<?php

namespace App\Controllers;

use CodeIgniter\Exceptions\PageNotFoundException;

class SettingData extends BaseController
{
    protected $db;
    protected $validation;
    protected array $tables;

    public function __construct()
    {
        $this->db = db_connect();
        $this->validation = \Config\Services::validation();
        $this->tables = $this->tableConfigs();
    }

    public function index()
    {
        $this->guardAdmin();

        $data['content'] = 'setting/data';
        $data['data'] = [
            'controller' => 'setting-data',
            'title' => 'Setting Data',
            'tables' => $this->tables,
        ];

        return view('template', $data);
    }

    public function list(string $key)
    {
        $this->guardAdmin(true);
        $config = $this->getConfig($key);

        $builder = $this->db->table($config['table']);
        $builder->where($config['deleted_field'], null);
        foreach ($config['order'] as $field => $direction) {
            $builder->orderBy($field, $direction);
        }

        $rows = $builder->get()->getResultArray();
        $data = [
            'token' => csrf_hash(),
            'data' => [],
        ];

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
            $data['data'][] = $line;
        }

        return $this->response->setJSON($data);
    }

    public function get(string $key)
    {
        $this->guardAdmin(true);
        $config = $this->getConfig($key);
        $id = (int) $this->request->getPost('id');
        $row = $this->findActiveRow($config, $id);

        if (!$row) {
            return $this->jsonError('Data tidak ditemukan');
        }

        $row['success'] = true;
        $row['token'] = csrf_hash();

        return $this->response->setJSON($row);
    }

    public function save(string $key)
    {
        $this->guardAdmin(true);
        $config = $this->getConfig($key);
        $id = (int) $this->request->getPost($config['primary_key']);
        $fields = $this->collectFields($config);

        $this->validation->setRules($this->validationRules($config));
        if (!$this->validation->run($fields)) {
            return $this->jsonError($this->validation->listErrors());
        }

        $now = date('Y-m-d H:i:s');
        $dbFields = $this->db->getFieldNames($config['table']);
        if ($id > 0 && in_array('updated_at', $dbFields, true)) {
            $fields['updated_at'] = $now;
        }
        if ($id > 0 && in_array('edit_by', $dbFields, true)) {
            $fields['edit_by'] = user_id();
        }
        if ($id <= 0 && in_array('created_at', $dbFields, true)) {
            $fields['created_at'] = $now;
        }
        if ($id <= 0 && in_array('add_by', $dbFields, true)) {
            $fields['add_by'] = user_id();
        }

        if ($id > 0) {
            if (!$this->findActiveRow($config, $id)) {
                return $this->jsonError('Data tidak ditemukan');
            }

            $this->db->table($config['table'])
                ->where($config['primary_key'], $id)
                ->update($fields);

            return $this->jsonSuccess('Data berhasil diperbaharui');
        }

        if (!empty($config['manual_id'])) {
            $fields[$config['primary_key']] = $this->nextManualId($config);
        }

        $this->db->table($config['table'])->insert($fields);

        return $this->jsonSuccess('Data berhasil ditambahkan');
    }

    public function delete(string $key)
    {
        $this->guardAdmin(true);
        $config = $this->getConfig($key);
        $id = (int) $this->request->getPost('id');

        if (!$this->findActiveRow($config, $id)) {
            return $this->jsonError('Data tidak ditemukan');
        }

        $fields = [
            $config['deleted_field'] => date('Y-m-d H:i:s'),
        ];
        $dbFields = $this->db->getFieldNames($config['table']);
        if (in_array('updated_at', $dbFields, true)) {
            $fields['updated_at'] = date('Y-m-d H:i:s');
        }
        if (in_array('edit_by', $dbFields, true)) {
            $fields['edit_by'] = user_id();
        }

        $this->db->table($config['table'])
            ->where($config['primary_key'], $id)
            ->update($fields);

        return $this->jsonSuccess('Data berhasil dihapus');
    }

    private function collectFields(array $config): array
    {
        $fields = [];
        foreach ($config['fields'] as $field => $meta) {
            $value = trim((string) $this->request->getPost($field));
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

    private function validationRules(array $config): array
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

    private function findActiveRow(array $config, int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        return $this->db->table($config['table'])
            ->where($config['primary_key'], $id)
            ->where($config['deleted_field'], null)
            ->get()
            ->getRowArray() ?: null;
    }

    private function nextManualId(array $config): int
    {
        $row = $this->db->table($config['table'])
            ->selectMax($config['primary_key'], 'max_id')
            ->get()
            ->getRow();

        return ((int) ($row->max_id ?? 0)) + 1;
    }

    private function getConfig(string $key): array
    {
        if (!isset($this->tables[$key])) {
            throw new PageNotFoundException('Setting tidak ditemukan');
        }

        return $this->tables[$key];
    }

    private function guardAdmin(bool $json = false): void
    {
        if (in_groups('1')) {
            return;
        }

        if ($json) {
            $this->response->setStatusCode(403)
                ->setJSON([
                    'success' => false,
                    'token' => csrf_hash(),
                    'messages' => 'Akses hanya untuk admin',
                ])
                ->send();
            exit;
        }

        throw new PageNotFoundException('Halaman tidak ditemukan');
    }

    private function jsonError($messages)
    {
        return $this->response->setJSON([
            'success' => false,
            'token' => csrf_hash(),
            'messages' => $messages,
        ]);
    }

    private function jsonSuccess(string $message)
    {
        return $this->response->setJSON([
            'success' => true,
            'token' => csrf_hash(),
            'messages' => $message,
        ]);
    }

    private function tableConfigs(): array
    {
        return [
            'list_bank' => [
                'label' => 'List Bank',
                'table_label' => 'list_bank',
                'table' => 'list_bank',
                'primary_key' => 'id',
                'deleted_field' => 'deleted_at',
                'manual_id' => true,
                'order' => ['bank' => 'ASC', 'id' => 'ASC'],
                'fields' => [
                    'bank' => ['label' => 'Bank', 'required' => true, 'max_length' => 255],
                    'keterangan' => ['label' => 'Keterangan', 'required' => false, 'max_length' => 255],
                    'exp_days' => ['label' => 'Exp Days', 'type' => 'number', 'required' => false],
                ],
            ],
            'list_bayar_produksi' => [
                'label' => 'List Bayar Produksi',
                'table_label' => 'list_bayar_produksi',
                'table' => 'list_bayar_produksi',
                'primary_key' => 'id',
                'deleted_field' => 'deleted_at',
                'order' => ['sort' => 'ASC', 'id' => 'ASC'],
                'fields' => [
                    'item' => ['label' => 'Item', 'required' => true, 'max_length' => 255],
                    'sort' => ['label' => 'Sort', 'type' => 'number', 'required' => false],
                ],
            ],
            'list_cashout' => [
                'label' => 'List Cashout',
                'table_label' => 'list_cashout',
                'table' => 'list_cashout',
                'primary_key' => 'id',
                'deleted_field' => 'deleted_at',
                'order' => ['sort' => 'ASC', 'id' => 'ASC'],
                'fields' => [
                    'item' => ['label' => 'Item', 'required' => true, 'max_length' => 255],
                    'sort' => ['label' => 'Sort', 'type' => 'number', 'required' => false],
                ],
            ],
            'list_dajam' => [
                'label' => 'List Dajam',
                'table_label' => 'list_dajam',
                'table' => 'list_dajam',
                'primary_key' => 'id',
                'deleted_field' => 'deleted_at',
                'order' => ['sort' => 'ASC', 'id' => 'ASC'],
                'fields' => [
                    'nama_jaminan' => ['label' => 'Nama Jaminan', 'required' => true, 'max_length' => 255],
                    'sort' => ['label' => 'Sort', 'type' => 'number', 'required' => false],
                ],
            ],
            'keuangan_list_item' => [
                'label' => 'Keuangan List Item',
                'table_label' => 'keuangan_list_item',
                'table' => 'keuangan_item_list',
                'primary_key' => 'id_keuangan_item_list',
                'deleted_field' => 'deleted_at',
                'order' => ['id_keuangan_item_list' => 'ASC'],
                'fields' => [
                    'item' => ['label' => 'Item', 'required' => true, 'max_length' => 255],
                    'kategori' => ['label' => 'Kategori', 'required' => true, 'max_length' => 10],
                ],
            ],
            'ppn' => [
                'label' => 'Setting PPN',
                'table_label' => 'ppn',
                'table' => 'ppn',
                'primary_key' => 'id',
                'deleted_field' => 'deleted_at',
                'order' => ['id' => 'ASC'],
                'fields' => [
                    'besar' => ['label' => 'Besar PPN (%)', 'type' => 'decimal', 'required' => true],
                ],
            ],
            'pph' => [
                'label' => 'Setting PPH',
                'table_label' => 'pph',
                'table' => 'pph',
                'primary_key' => 'id',
                'deleted_field' => 'deleted_at',
                'order' => ['id' => 'ASC'],
                'fields' => [
                    'besar' => ['label' => 'Besar PPH (%)', 'type' => 'decimal', 'required' => true],
                    'ket' => ['label' => 'Keterangan', 'required' => true, 'max_length' => 10],
                ],
            ],
        ];
    }
}

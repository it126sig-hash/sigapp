<?php

namespace App\Repositories\Settings;

use CodeIgniter\Exceptions\PageNotFoundException;

class SettingDataRepository
{
    protected $db;
    protected array $tables;

    public function __construct()
    {
        $this->db = db_connect();
        $this->tables = $this->tableConfigs();
    }

    public function getAllConfigs(): array
    {
        return $this->tables;
    }

    public function getConfig(string $key): array
    {
        if (!isset($this->tables[$key])) {
            throw new PageNotFoundException('Setting tidak ditemukan: ' . esc($key));
        }

        return $this->tables[$key];
    }

    public function listRows(array $config): array
    {
        $builder = $this->db->table($config['table']);
        $builder->where($config['deleted_field'], null);
        foreach ($config['order'] as $field => $direction) {
            $builder->orderBy($field, $direction);
        }

        return $builder->get()->getResultArray();
    }

    public function findActiveRow(array $config, int $id): ?array
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

    public function nextManualId(array $config): int
    {
        $row = $this->db->table($config['table'])
            ->selectMax($config['primary_key'], 'max_id')
            ->get()
            ->getRow();

        return ((int) ($row->max_id ?? 0)) + 1;
    }

    public function getFieldNames(string $table): array
    {
        return $this->db->getFieldNames($table);
    }

    public function insertRow(string $table, array $fields): int
    {
        $this->db->table($table)->insert($fields);
        return (int) $this->db->insertID();
    }

    public function updateRow(string $table, string $primaryKey, int $id, array $fields): bool
    {
        return $this->db->table($table)
            ->where($primaryKey, $id)
            ->update($fields);
    }

    public function softDeleteRow(array $config, int $id, int $userId): bool
    {
        $fields = [
            $config['deleted_field'] => date('Y-m-d H:i:s'),
        ];
        $dbFields = $this->getFieldNames($config['table']);
        if (in_array('updated_at', $dbFields, true)) {
            $fields['updated_at'] = date('Y-m-d H:i:s');
        }
        if (in_array('edit_by', $dbFields, true)) {
            $fields['edit_by'] = $userId;
        }

        return $this->updateRow($config['table'], $config['primary_key'], $id, $fields);
    }

    protected function tableConfigs(): array
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

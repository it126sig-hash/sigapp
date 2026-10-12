<?php

namespace App\Repositories\Settings;

use App\Models\PengaturanWarnaModel;

class PengaturanWarnaRepository
{
    protected PengaturanWarnaModel $model;

    public function __construct()
    {
        $this->model = new PengaturanWarnaModel();
    }

    public function getAll(): array
    {
        return $this->model
            ->select('config_name, fill, strokeWidth, dashed, keterangan, add_by, date_add, edit_by, date_edit')
            ->findAll();
    }

    public function getByConfigName(string $configName): ?object
    {
        return $this->model->where('config_name', $configName)->first();
    }

    public function insert(array $fields): bool
    {
        return (bool) $this->model->insert($fields);
    }

    public function update(string $configName, array $fields): bool
    {
        return (bool) $this->model->update($configName, $fields);
    }

    public function delete(string $configName): bool
    {
        return (bool) $this->model->where('config_name', $configName)->delete();
    }
}

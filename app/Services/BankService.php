<?php

namespace App\Services;

use App\Models\ListBankModel;

class BankService
{
    protected $listBankModel;

    public function __construct()
    {
        $this->listBankModel = new ListBankModel();
    }

    public function simpan(array $data, int $id = null): int|bool
    {
        $saveData = [
            'bank' => $data['bank'],
            'keterangan' => $data['keterangan'] ?? null,
            'exp_days' => isset($data['exp_days']) ? (int) $data['exp_days'] : 0,
        ];

        if ($id) {
            $this->listBankModel->updateBank($id, $saveData);
            return $id;
        } else {
            return $this->listBankModel->insertBank($saveData);
        }
    }

    public function hapus(int $id): bool
    {
        return $this->listBankModel->deleteBank($id);
    }
}

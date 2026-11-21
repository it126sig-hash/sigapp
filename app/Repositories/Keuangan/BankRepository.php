<?php

namespace App\Repositories\Keuangan;

use App\Models\ListBankModel;

class BankRepository
{
    private ListBankModel $listBankModel;

    public function __construct(?ListBankModel $listBankModel = null)
    {
        $this->listBankModel = $listBankModel ?? new ListBankModel();
    }

    public function getAllBanks(?string $search = null): array
    {
        return $this->listBankModel->getAllBanks($search);
    }

    public function getActiveBanks(): array
    {
        return $this->listBankModel->getActiveBanks();
    }

    public function getBankById(int $id): ?array
    {
        $row = $this->listBankModel->getBankById($id);
        return is_array($row) ? $row : null;
    }

    public function insertBank(array $data): int|bool
    {
        return $this->listBankModel->insertBank($data);
    }

    public function updateBank(int $id, array $data): bool
    {
        return (bool) $this->listBankModel->updateBank($id, $data);
    }

    public function deleteBank(int $id): bool
    {
        return (bool) $this->listBankModel->deleteBank($id);
    }
}

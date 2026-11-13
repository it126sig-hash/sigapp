<?php

namespace App\Services\Keuangan;

use App\Repositories\Keuangan\BankRepository;

class BankService
{
    protected BankRepository $bankRepository;

    public function __construct(?BankRepository $bankRepository = null)
    {
        $this->bankRepository = $bankRepository ?? new BankRepository();
    }

    public function getAllActive(): array
    {
        return $this->bankRepository->getActiveBanks();
    }

    public function getList(?string $search = null): array
    {
        return $this->bankRepository->getAllBanks($search);
    }

    public function getOne(int $id): ?array
    {
        return $this->bankRepository->getBankById($id);
    }

    public function simpan(array $data, ?int $id = null): int|bool
    {
        $saveData = [
            'bank' => $data['bank'],
            'keterangan' => $data['keterangan'] ?? null,
            'exp_days' => isset($data['exp_days']) ? (int) $data['exp_days'] : 0,
        ];

        if ($id) {
            $this->bankRepository->updateBank($id, $saveData);
            return $id;
        }

        return $this->bankRepository->insertBank($saveData);
    }

    public function hapus(int $id): bool
    {
        return $this->bankRepository->deleteBank($id);
    }
}

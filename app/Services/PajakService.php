<?php

namespace App\Services;

use App\Repositories\PajakRepository;

class PajakService
{
    protected $repo;

    public function __construct()
    {
        $this->repo = new PajakRepository();
    }

    public function insertPajak(array $fields, int $idKavling, int $actorId): int
    {
        $fields['add_by'] = $actorId;
        $fields['created_at'] = date('Y-m-d H:i:s');

        $idPajak = $this->repo->insert($fields);
        if (!$idPajak) {
            throw new \RuntimeException('Kesalahan saat mengisi data!');
        }

        if (!$this->repo->linkKavling($idKavling, (int) $idPajak)) {
            throw new \RuntimeException('Kesalahan saat menghubungkan data pajak ke kavling!');
        }

        return (int) $idPajak;
    }

    public function updatePajak(int $idPajak, array $fields, int $actorId): bool
    {
        $fields['edit_by'] = $actorId;
        $fields['updated_at'] = date('Y-m-d H:i:s');

        if (!$this->repo->update($idPajak, $fields)) {
            throw new \RuntimeException('Kesalahan saat merubah data!');
        }

        return true;
    }
}

<?php

namespace App\Services\Mkdt;

use App\Repositories\Mkdt\PosisiKonsumenRepository;
use CodeIgniter\Database\BaseConnection;
use Config\Database;

class MkdtPricingService
{
    private BaseConnection $db;
    private PosisiKonsumenRepository $repo;

    public function __construct(?BaseConnection $db = null, ?PosisiKonsumenRepository $repo = null)
    {
        $this->db   = $db ?? Database::connect();
        $this->repo = $repo ?? new PosisiKonsumenRepository($this->db);
    }

    /**
     * Memperbarui harga_net_ajb pada entitas MKDT secara langsung tanpa membuka transaksi baru
     * agar tetap mengikuti konteks transaksi pemanggil lintas modul.
     */
    public function updateHargaNetAjb(int $idMkdt, float $hargaNetAjb): bool
    {
        if ($idMkdt <= 0) {
            return false;
        }

        return $this->repo->updateHargaNetAjb($idMkdt, $hargaNetAjb);
    }
}

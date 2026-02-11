<?php

namespace App\Services;

use App\Models\KonsumenModel;
use App\Repositories\KonsumenRepository;
use App\Repositories\KavlingRepository;

class KonsumenService
{
    protected $model;
    protected $konsRepo;
    protected $kavlingRepo;

    public function __construct()
    {
        $this->model = model(KonsumenModel::class);
        $this->konsRepo = new KonsumenRepository();
        $this->kavlingRepo = new KavlingRepository();
    }

    /**
     * @return int id_konsumen
     */
    public function upsert(?int $idKonsumen, array $data): ?int
    {
        if (empty($idKonsumen)) {
            if (!$this->model->insert($data)) return null;
            return (int) $this->model->getInsertID();
        }
        if (!$this->model->update($idKonsumen, $data)) return null;
        return (int) $idKonsumen;
    }

    public function getKonsumenTransaksi($id_mkdt, $select = [])
    {
        return $this->konsRepo->getKonsumenTransaksi($id_mkdt, $select);
    }
    public function getByIDKavling($id_kavling)
    {
        $kaVling = $this->kavlingRepo->getKavlingById($id_kavling);
        if ($kaVling) {
            $konsumen = $this->getKonsumenTransaksi($kaVling->id_mkdt, ['konsumen.nama_konsumen', 'mkdt.booking_tgl', 'harga_jual']);
            return $konsumen;
        }
        return null;
    }
}

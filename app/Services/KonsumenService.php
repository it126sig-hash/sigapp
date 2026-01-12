<?php
namespace App\Services;

use App\Models\KonsumenModel;
use App\Repositories\KonsumenRepository;

class KonsumenService
{
    protected $model;
    protected $konsRepo;

    public function __construct()
    {
        $this->model = model(KonsumenModel::class);
        $this->konsRepo = new KonsumenRepository();
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

    public function getKonsumenTransaksi($id_mkdt){
        return $this->konsRepo->getKonsumenTransaksi($id_mkdt);
    }
    
}

<?php

namespace App\Repositories;

use CodeIgniter\Model;

class PaymentSummaryRepository extends Model
{
    protected $table = 'mkdt_payment_summary';
    protected $primaryKey = 'id_mkdt';
    protected $allowedFields = ['id_mkdt', 'total_um', 'total_bb', 'total_adm', 'updated_at'];
    protected $returnType = 'array';

    /**
     * Tambah atau kurangi total kategori.
     */
    public function updateCategory(int $idMkdt, string $kategori, float $amount)
    {
        // pastikan kategori valid
        $field = match ($kategori) {
            'UM' => 'total_um',
            'BB' => 'total_bb',
            'ADM' => 'total_adm',
            default => throw new \Exception("Kategori tidak valid: $kategori"),
        };

        // jika belum ada record summary → insert baru
        $existing = $this->find($idMkdt);
        if (!$existing) {
            return $this->insert([
                'id_mkdt' => $idMkdt,
                $field => $amount
            ]);
        }

        // update nilai (bisa + atau -)
        return $this->set($field, "$field + {$amount}", false)
            ->where('id_mkdt', $idMkdt)
            ->update();
    }
    public function setToZero(int $id_mkdt)
    {
        // jika belum ada record summary → insert baru
        $existing = $this->find($id_mkdt);
        if (!$existing) {
            return $this->insert([
                'id_mkdt' => $id_mkdt,
                'total_um' => 0,
                'total_bb' => 0,
                'total_adm' => 0
            ]);
        }
        return $this->update($id_mkdt, ['total_um' => 0, 'total_bb' => 0, 'total_adm' => 0]);
    }

    /**
     * Set ulang nilai total (opsional kalau kamu butuh sync ulang)
     */
    public function setCategory(int $idMkdt, string $kategori, float $value)
    {
        $field = match ($kategori) {
            'UM' => 'total_um',
            'BB' => 'total_bb',
            'ADM' => 'total_adm',
            default => throw new \Exception("Kategori tidak valid!"),
        };

        return $this->update($idMkdt, [$field => $value]);
    }
}

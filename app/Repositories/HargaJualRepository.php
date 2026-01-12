<?php

namespace App\Repositories;

use CodeIgniter\Database\BaseConnection;

class HargaJualRepository
{
    public function __construct(private BaseConnection $db) {}

    public function getHargaJualById(int $id): ?object
    {
        return $this->db->table('hargajual b')
            ->select([
                'b.id',
                'b.id AS harga_akhir',
                'b.tgl_harga',
                'b.row',
                'b.hargajual',
                'b.hargajual_net',
                'b.kpr',
                'b.uang_muka',
                'b.bphtb',
                'b.ppn',
                'b.biaya_proses',
                'b.biaya_adm',
                'b.lb',
                'b.lt',
            ])
            // ->join('tipe a', 'a.id_tipe = b.id_tipe')
            ->where('b.id', $id)
            ->limit(1)
            ->get()
            ->getRow();
    }
}

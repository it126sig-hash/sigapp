<?php

namespace App\Repositories;

use CodeIgniter\Model;

class PajakRepository extends Model
{
    protected $table = 'pajak';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'id_mkdt',
        'pph42_tarif',
        'pph42_nilai',
        'pph42_id_billing',
        'pph42_ntpn',
        'pph42_tgl_bayar',
        'pph42_keterangan',
        'ppn_tarif',
        'ppn_nilai',
        'ppn_id_billing',
        'ppn_ntpn',
        'ppn_tgl_bayar',
        'ppn_keterangan',
        'ppn_no_faktur',
        'add_by',
        'edit_by',
    ];
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function linkKavling(int $idKavling, int $idPajak): bool
    {
        return $this->db->table('kavling')
            ->where('id_kavling', $idKavling)
            ->update(['id_pajak' => $idPajak]);
    }
}

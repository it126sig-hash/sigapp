<?php

namespace App\Models;

use CodeIgniter\Model;

class CashoutSubkonModel extends Model
{
    protected $table            = 'cashout_subkon';
    protected $primaryKey       = 'id_cashout_subkon';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'id_subkon',
        'total_nominal',
        'nomor_surat',
        'file_surat',
        'tanggal_surat',
        'keterangan',
        'status',
        'keuangan_diperiksa_by',
        'keuangan_diperiksa_at',
        'created_at',
        'add_by',
        'updated_at',
        'edit_by'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}

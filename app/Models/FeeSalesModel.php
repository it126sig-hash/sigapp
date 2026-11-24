<?php

namespace App\Models;

use CodeIgniter\Model;

class FeeSalesModel extends Model
{
    protected $table            = 'fee_sales';
    protected $primaryKey       = 'id';
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    protected $allowedFields = [
        'id_mkdt',
        'id_sales',
        'sales_nama',
        'sales_keterangan',
        'harga_net_ajb',
        'persentase_fee',
        'total_fee',
        'skema_termin',
        'status',
        'catatan',
        'created_by',
        'updated_by',
    ];
}

<?php

namespace App\Models;

use CodeIgniter\Model;

class FeeSalesPengajuanItemModel extends Model
{
    protected $table            = 'fee_sales_pengajuan_item';
    protected $primaryKey       = 'id';
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = false;

    protected $allowedFields = [
        'id_pengajuan',
        'id_fee_sales',
        'id_termin',
        'termin_ke',
        'nominal',
        'suggested_harga_net_ajb',
        'created_at',
    ];
}

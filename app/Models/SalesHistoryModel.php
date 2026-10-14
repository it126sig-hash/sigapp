<?php

namespace App\Models;

use CodeIgniter\Model;

class SalesHistoryModel extends Model
{
    protected $table            = 'sales_history';
    protected $primaryKey       = 'id';
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = false;

    protected $allowedFields = [
        'id_sales',
        'fee_subsidi_lama',
        'fee_subsidi_baru',
        'fee_komersil_lama',
        'fee_komersil_baru',
        'tanggal_pks_lama',
        'tanggal_pks_baru',
        'tanggal_exp_pks_lama',
        'tanggal_exp_pks_baru',
        'lampiran_pks_lama',
        'lampiran_pks_baru',
        'catatan',
        'created_by',
        'created_at',
    ];
}

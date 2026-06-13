<?php

namespace App\Models;

use CodeIgniter\Model;

class FinanceLedgerModel extends Model
{
    protected $table = 'finance_ledger';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'direction',
        'source_type',
        'source_id',
        'source_detail_id',
        'id_mkdt',
        'id_kavling',
        'nominal',
        'tanggal_transaksi',
        'label',
        'keterangan',
        'status',
        'is_deleted',
        'deleted_at',
        'deleted_by',
        'add_by',
        'created_at',
        'edit_by',
        'updated_at',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}

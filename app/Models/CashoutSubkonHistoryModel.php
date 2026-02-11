<?php

namespace App\Models;

use CodeIgniter\Model;

class CashoutSubkonHistoryModel extends Model
{
    protected $table            = 'cashout_subkon_history';
    protected $primaryKey       = 'id_cashout_subkon_history';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'id_cashout_subkon',
        'status',
        'keterangan',
        'created_at',
        'add_by',
        'updated_at',
        'edit_by',
        'deleted_at',
        'deleted_by'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}

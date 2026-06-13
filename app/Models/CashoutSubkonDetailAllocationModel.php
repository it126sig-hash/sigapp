<?php

namespace App\Models;

use CodeIgniter\Model;

class CashoutSubkonDetailAllocationModel extends Model
{
    protected $table            = 'cashout_subkon_detail_allocation';
    protected $primaryKey       = 'id_cashout_subkon_detail_allocation';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'id_cashout_subkon_detail',
        'id_cashout_subkon',
        'id_kavling',
        'nominal',
        'allocation_type',
        'created_at',
        'add_by',
        'updated_at',
        'edit_by',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}

<?php

namespace App\Models;

use CodeIgniter\Model;

class ListCashoutModel extends Model
{
    protected $table            = 'list_cashout';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $allowedFields    = ['item', 'sort', 'deleted_at'];
    protected $deletedField     = 'deleted_at';
}

<?php

namespace App\Models;

use CodeIgniter\Model;

class ListCashoutModel extends Model
{
    protected $table            = 'list_cashout';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['item', 'sort'];
}

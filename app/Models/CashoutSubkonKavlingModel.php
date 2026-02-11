<?php

namespace App\Models;

use CodeIgniter\Model;

class CashoutSubkonKavlingModel extends Model
{
    protected $table            = 'cashout_subkon_kavling';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['id_cashout_subkon', 'id_kavling', 'keterangan'];
}

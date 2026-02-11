<?php

namespace App\Models;

use CodeIgniter\Model;

class SubkonModel extends Model
{
    protected $table            = 'subkon';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $allowedFields    = [
        'nama_subkon',
        'hp1_subkon',
        'hp2_subkon',
        'alamat_subkon',
        'is_deleted',
        'add_by',
        'edit_by',
        'deleted_by'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
}

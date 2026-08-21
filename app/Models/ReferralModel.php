<?php

namespace App\Models;
use CodeIgniter\Model;

class ReferralModel extends Model {
    protected $table = 'referrals';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    
    protected $allowedFields = [
        'id_konsumen_referrer',
        'id_mkdt_referred',
        'id_proyek',
        'add_by'
    ];
    
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $skipValidation     = true;
}

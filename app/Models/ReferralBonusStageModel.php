<?php

namespace App\Models;
use CodeIgniter\Model;

class ReferralBonusStageModel extends Model {
    protected $table = 'referral_bonus_stages';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    
    protected $allowedFields = [
        'id_proyek',
        'nama_tahapan',
        'trigger_status_mkdt',
        'nominal_default',
        'urutan',
        'is_active'
    ];
    
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $skipValidation     = true;
}

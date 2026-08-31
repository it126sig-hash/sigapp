<?php

namespace App\Models;
use CodeIgniter\Model;

class ReferralBonusModel extends Model {
    protected $table = 'referral_bonuses';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    
    protected $allowedFields = [
        'id_referral',
        'id_stage',
        'nominal_bonus',
        'status',
        'eligible_at',
        'confirmed_by',
        'confirmed_at',
        'paid_by_promosi',
        'paid_promosi_at',
        'paid_promosi_by',
        'bukti_bayar_promosi',
        'id_pengajuan_pencairan',
        'nominal_pengajuan_keuangan',
        'tanggal_spp',
        'bukti_pengajuan_keuangan',
        'submitted_keuangan_at',
        'submitted_keuangan_by',
        'nominal_cair_keuangan',
        'tanggal_cair_keuangan',
        'bukti_transfer_ke_promosi',
        'cair_keuangan_by',
        'cair_keuangan_at',
        'keterangan',
        'add_by',
        'edit_by'
    ];
    
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $skipValidation     = true;
}

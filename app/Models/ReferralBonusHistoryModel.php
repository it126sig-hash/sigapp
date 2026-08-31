<?php

namespace App\Models;

use CodeIgniter\Model;

class ReferralBonusHistoryModel extends Model
{
    protected $table = 'referral_bonus_histories';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'id_bonus',
        'id_referral',
        'action',
        'old_status',
        'new_status',
        'old_nominal_bonus',
        'new_nominal_bonus',
        'nominal_pengajuan_keuangan',
        'nominal_cair_keuangan',
        'payload_json',
        'note',
        'add_by',
        'created_at',
    ];

    protected $useTimestamps = false;
    protected $skipValidation = true;
}

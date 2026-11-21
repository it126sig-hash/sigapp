<?php

namespace App\Models;

use CodeIgniter\Model;

class TargetSiteplanRequestModel extends Model
{
    protected $table = 'target_siteplan_request';
    protected $primaryKey = 'id_request';
    protected $returnType = 'object';
    protected $allowedFields = [
        'id_target',
        'id_kavling',
        'old_id_tipe',
        'old_tipe_manual',
        'new_id_tipe',
        'new_tipe_manual',
        'catatan_request',
        'status',
        'request_by',
        'request_at',
        'approve_by',
        'approve_at',
        'catatan_approval',
    ];
    protected $useTimestamps = false;
    protected $skipValidation = true;
}

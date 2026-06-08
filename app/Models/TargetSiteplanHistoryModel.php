<?php

namespace App\Models;

use CodeIgniter\Model;

class TargetSiteplanHistoryModel extends Model
{
    protected $table = 'target_siteplan_history';
    protected $primaryKey = 'id_target_history';
    protected $returnType = 'object';
    protected $allowedFields = [
        'id_target',
        'aksi',
        'deskripsi',
        'snapshot',
        'add_by',
        'created_at',
    ];
    protected $useTimestamps = false;
    protected $skipValidation = true;
}

<?php

namespace App\Models;

use CodeIgniter\Model;

class TargetSiteplanKavlingModel extends Model
{
    protected $table = 'target_siteplan_kavling';
    protected $primaryKey = 'id_target_kavling';
    protected $returnType = 'object';
    protected $allowedFields = [
        'id_target',
        'id_kavling',
        'created_at',
    ];
    protected $useTimestamps = false;
    protected $skipValidation = true;
}

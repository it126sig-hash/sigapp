<?php

namespace App\Models;

use CodeIgniter\Model;

class TargetSiteplanModel extends Model
{
    protected $table = 'target_siteplan';
    protected $primaryKey = 'id_target';
    protected $returnType = 'object';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'id_proyek',
        'tahun_target',
        'deskripsi',
        'status',
        'add_by',
        'edit_by',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
    protected $skipValidation = true;
}

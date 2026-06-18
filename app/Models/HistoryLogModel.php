<?php

namespace App\Models;

use CodeIgniter\Model;

class HistoryLogModel extends Model
{
    protected $table = 'history_log';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = false;
    protected $skipValidation = true;
    protected $allowedFields = [
        'module',
        'reference_type',
        'reference_id',
        'id_kavling',
        'id_proyek',
        'action',
        'summary',
        'old_data',
        'new_data',
        'metadata',
        'legacy_table',
        'legacy_id',
        'add_by',
        'created_at',
    ];
}

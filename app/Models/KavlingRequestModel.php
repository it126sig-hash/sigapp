<?php

namespace App\Models;

use CodeIgniter\Model;

class KavlingRequestModel extends Model
{
    protected $table            = 'kavling_request';
    protected $primaryKey       = 'id_request';
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = [
        'id_proyek',
        'jenis_request',
        'id_kavling',
        'id_cluster',
        'id_jalan',
        'id_tipe',
        'keterangan',
        'status',
        'created_by',
    ];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
}

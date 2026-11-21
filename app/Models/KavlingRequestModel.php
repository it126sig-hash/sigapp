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
        'applied_id_tipe',
        'tipe_manual',
        'keterangan',
        'status',
        'alasan_tolak',
        'catatan_planning',
        'created_by',
        'processed_by',
        'processed_at',
    ];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
}

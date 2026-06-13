<?php

namespace App\Models;

use CodeIgniter\Model;

class DanaJaminanHistoryModel extends Model
{
    protected $table         = 'dana_jaminan_history';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'id_kavling',
        'id_mkdt',
        'id_dana_akad',
        'id_pengajuan',
        'aksi',
        'deskripsi',
        'snapshot',
        'add_by',
        'created_at',
    ];
}

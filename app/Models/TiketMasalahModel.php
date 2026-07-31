<?php

namespace App\Models;

use CodeIgniter\Model;

class TiketMasalahModel extends Model
{
    protected $table            = 'tiket_masalah';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'ref_type', 'ref_id', 'id_proyek', 'tanggal_masalah',
        'keterangan', 'prioritas', 'status', 'pic_user_id'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
